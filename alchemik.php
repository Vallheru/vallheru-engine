<?php
/**
 *   File functions:
 *   Alchemy mill - making potions
 *
 *   @name                 : alchemik.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 10.12.2012
 *
 */

//
//
//       This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; either version 2 of the License, or
//   (at your option) any later version.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

$title = "Pracownia alchemiczna";
require_once("includes/head.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
  {
    error ('Nie znajdujesz się w mieście.');
  }

/**
* Get amount of herbs from database
*/
$herb = $db -> Execute("SELECT `illani`, `illanias`, `nutari`, `dynallca` FROM `herbs` WHERE `gracz`=".$player -> id);

/**
* Assign variables to template
*/
if (!isset($_GET['alchemik']))
  {
    $smarty -> assign(array("Awelcome" => "Witaj w pracowni alchemika. Tutaj możesz wyrabiać różne mikstury. Aby móc je wykonywać musisz najpierw posiadać przepis na odpowiednią miksturę oraz odpowiednią ilość ziół.",
                            "Arecipes" => "Kup przepis na miksturę",
                            "Amake" => "Idź do pracowni",
                            "Aastral" => "Wykonaj astralną miksturę"));
    $objAstral = $db -> SelectLimit("SELECT `amount` FROM `astral_plans` WHERE `owner`=".$player -> id." AND `name` LIKE 'R%' AND `location`='V'", 1);
    if ($objAstral -> fields['amount'])
      {
        $smarty -> assign("Astral", 'Y');
      }
    else
      {
        $smarty -> assign("Astral", '');
      }
    $objAstral -> Close();
  }
else
  {
    /**
     * Buy receptures
     */
    if ($_GET['alchemik'] == 'przepisy') 
      {

	$objOwned = $db->Execute("SELECT `name` FROM `alchemy_mill` WHERE `owner`=".$player->id);
	$arrOwned = array();
	while (!$objOwned->EOF)
	  {
	    $arrOwned[] = $objOwned->fields['name'];
	    $objOwned->MoveNext();
	  }
	$objOwned->Close();

	if (isset($_GET['buy'])) 
	  {
	    checkvalue($_GET['buy']);
	    $plany = $db -> Execute("SELECT * FROM `alchemy_mill` WHERE `id`=".$_GET['buy']." AND `owner`=0");
	    if (in_array($plany->fields['name'], $arrOwned)) 
	      {
		message('error', "Masz już taki przepis!");
	      }
	    elseif ($plany -> fields['id'] == 0) 
	      {
		message('error', "Nie ma takiego przepisu.");
	      }
	    elseif ($plany -> fields['status'] != 'S') 
	      {
		message('error', "Tutaj tego nie sprzedasz");
	      }
	    elseif ($plany -> fields['cost'] > $player -> credits) 
	      {
		message('error', "Nie stać cię!");
	      }
	    elseif ($player->skills['alchemy'][1] < $plany->fields['level'])
	      {
		message('error', 'Nie możesz jeszcze kupić tego planu.');
	      }
	    else
	      {
		$db -> Execute("INSERT INTO `alchemy_mill` (`owner`, `name`, `cost`, `status`, `level`, `illani`, `illanias`, `nutari`, `dynallca`) VALUES(".$player -> id.",'".$plany -> fields['name']."',".$plany -> fields['cost'].",'N',".$plany -> fields['level'].",".$plany -> fields['illani'].",".$plany -> fields['illanias'].",".$plany -> fields['nutari'].",".$plany -> fields['dynallca'].")") or error("Nie mogę dodać do bazy danych!");
		$db -> Execute("UPDATE `players` SET `credits`=`credits`-".$plany -> fields['cost']." WHERE `id`=".$player -> id);
		$arrOwned[] = $plany->fields['name'];
		message('success', 'Zapłaciłeś <b>'.$plany->fields['cost'].'</b> sztuk złota, i kupiłeś za to nowy przepis na: <b>'.$plany->fields['name'].'</b>.');
		$plany -> Close();
	      }
	  }

	$plany = $db->Execute("SELECT `id`, `name`, `level`, `cost` FROM `alchemy_mill` WHERE `status`='S' AND `owner`=0 AND `level`<=".$player->skills['alchemy'][1]." ORDER BY `cost` ASC");
	$arrname = array();
	$arrcost = array();
	$arrlevel = array();
	$arrid = array();
	while (!$plany -> EOF) 
	  {
	    if (!in_array($plany->fields['name'], $arrOwned))
	      {
		$arrname[] = $plany -> fields['name'];
		$arrcost[] = $plany -> fields['cost'];
		$arrlevel[] = $plany -> fields['level'];
		$arrid[] = $plany -> fields['id'];
	      }
	    $plany -> MoveNext();
	  }
	$plany -> Close();
	$smarty -> assign (array("Name" => $arrname,
				 "Recipesinfo" => "Witaj w sklepie dla alchemików. Tutaj możesz kupić przepisy mikstur, które chcesz wykonywać. Aby kupić dany przepis, musisz mieć przy sobie odpowiednią ilość sztuk złota oraz odpowiedni poziom w umiejętności Alchemia. Oto lista dostępnych przepisów:",
				 "Rname" => "Nazwa",
				 "Rcost" => "Cena",
				 "Rlevel" => "Poziom",
				 "Roption" => "Opcje",
				 "Abuy" => "Kup",
				 "Cost" => $arrcost, 
				 "Level" => $arrlevel, 
				 "Id" => $arrid));
      }

    /**
     * Making potions
     */
    elseif ($_GET['alchemik'] == 'pracownia') 
      {
	if (!isset($_GET['rob'])) 
	  {
	    $arrname = array();
	    $arrlevel = array();
	    $arrid = array();
	    $arrillani = array();
	    $arrillanias = array();
	    $arrnutari = array();
	    $arrdynallca = array();
	    $kuznia = $db->GetAll("SELECT * FROM `alchemy_mill` WHERE `status`='N' AND `owner`=".$player -> id." ORDER BY `level` ASC");
	    $smarty -> assign (array("Plans" => $kuznia,
				     "Alchemistinfo" => "Tutaj możesz wykonywać mikstury co do których masz przepisy. Aby wykonać miksturę, musisz posiadać również odpowiednią ilość ziół. Każda próba kosztuje Cię odpowiednią do poziomu trudności wykonania mikstury ilość energii.<br /> Oto lista mikstur, które możesz wykonywać:",
				     "Rname" => "Nazwa",
				     "Rlevel" => "Poziom",
				     "Rillani" => "Illani",
				     "Rillanias" => "Illanias",
				     "Rnutari" => "Nutari",
				     "Rdynallca" => "Dynallca"));
	  }
	if (isset($_GET['dalej'])) 
	  {
	    if ($player -> hp == 0) 
	      {
		error ("Nie możesz wykonywać mikstur ponieważ jesteś martwy!");
	      }
	    checkvalue($_GET['dalej']);
	    $kuznia = $db -> Execute("SELECT `name`, `illani`, `illanias`, `nutari`, `dynallca`, `level` FROM `alchemy_mill` WHERE `id`=".$_GET['dalej']);
	    $arrHerbs = array('illani', 'illanias', 'nutari', 'dynallca');
	    $strHerb = 'Posiadasz ';
	    $intAmount = 0;
	    foreach ($arrHerbs as $strHerbname)
	      {
		if ($kuznia->fields[$strHerbname] > 0)
		  {
		    if ($strHerb != 'Posiadasz ')
		      {
			$strHerb = $strHerb.', ';
		      }
		    $strHerb = $strHerb.'<b>'.$herb->fields[$strHerbname]."</b> sztuk ".ucfirst($strHerbname);
		    $intAmount2 = floor($herb->fields[$strHerbname] / $kuznia->fields[$strHerbname]);
		    if ($intAmount2 < $intAmount || $intAmount == 0)
		      {
			$intAmount = $intAmount2;
		      }
		  }
	      }
	    if ($intAmount > 0)
	      {
		if ($kuznia->fields['level'] > 1)
		  {
		    $intEnergy = floor($player->energy / ($kuznia -> fields['level'] * 0.2));
		  }
		else
		  {
		    $intEnergy = floor($player->energy);
		  }
		if ($intEnergy < $intAmount)
		  {
		    $intAmount = $intEnergy;
		  }
	      }
	    $strHerb = $strHerb.'.';
	    $smarty -> assign (array ("Name1" => $kuznia -> fields['name'], 
				      "Id1" => $_GET['dalej'],
				      "Pstart" => "Spróbuj",
				      "Pamount" => "razy",
				      "Amake" => "wykonać",
				      "Tamount" => $intAmount,
				      "Therb" => $strHerb));
	    $kuznia -> Close();
	  }
	if (isset($_GET['rob'])) 
	  {
	    if (!isset($_POST['razy'])) 
	      {
		error ('Zapomnij o tym');
	      }
	    checkvalue($_GET['rob']);
	    checkvalue($_POST['razy']);
	    $kuznia = $db -> Execute("SELECT * FROM `alchemy_mill` WHERE `id`=".$_GET['rob']);
	    $rillani = ($_POST['razy'] * $kuznia -> fields['illani']);
	    $rillanias = ($_POST['razy'] * $kuznia -> fields['illanias']);
	    $rnutari = ($_POST['razy'] * $kuznia -> fields['nutari']);
	    $rdynallca = ($_POST['razy'] * $kuznia -> fields['dynallca']);
	    if ($herb -> fields['illani'] < $rillani || $herb -> fields['illanias'] < $rillanias || $herb -> fields['nutari'] < $rnutari || $herb -> fields['dynallca'] < $rdynallca) 
	      {
		error ("Nie masz tylu ziół!");
	      }
	    if ($kuznia -> fields['level'] > 1)
	      {
		$fltEnergy = $_POST['razy'] * ($kuznia -> fields['level'] * 0.2);
	      }
	    else
	      {
		$fltEnergy = $_POST['razy'];
	      }
	    if ($player -> energy < $fltEnergy) 
	      {
		error ("Nie masz tyle energii!");
	      }
	    if ($kuznia -> fields['owner'] != $player -> id) 
	      {
		error ("Nie masz takiego przepisu");
	      }

	    /**
	     * Add bonuses to ability
	     */
	    $player->curskills(array('alchemy'), TRUE, TRUE);
	    $player->skills['alchemy'][1] += $player->checkbonus('alchemy');
	    
	    $rprzedmiot = 0;
	    $rpd = 0;
	    $rum = 0;
	    $objItem = $db -> Execute("SELECT `efect`, `type` FROM `potions` WHERE `name`='".$kuznia -> fields['name']."' AND `owner`=0");
	    $arrMaked = array();

	    switch ($objItem->fields['type'])
	      {
	      case 'M':
		$fltStat = $player->stats['wisdom'][2];
		$arrStats = array('wisdom');
		$player->skills['alchemy'][1] += $player->checkbonus('amana');
		break;
	      case 'H':
		$fltStat = $player->stats['inteli'][2];
		$arrStats = array('inteli');
		$player->skills['alchemy'][1] += $player->checkbonus('ahealth');
		break;
	      case 'P':
		$fltStat = (min($player->stats['wisdom'][2], $player->stats['inteli'][2]) + $player->stats['agility'][2]) / 2;
		$arrStats = array('wisdom', 'inteli', 'agility');
		$player->skills['alchemy'][1] += $player->checkbonus('apoison');
		break;
	      case 'A':
		$fltStat = (min($player->stats['wisdom'][2], $player->stats['inteli'][2]) + $player->stats['speed'][2]) / 2;
		$arrStats = array('wisdom', 'inteli', 'speed');
		$player->skills['alchemy'][1] += $player->checkbonus('aantidote');
		break;
	      default:
		break;
	      }
	    $player->clearbless($arrStats);

	    /**
	     * Start making potions
	     */
	    for ($i = 1; $i <= $_POST['razy']; $i++)
	      {
		$intChance = $player->skills['alchemy'][1] + $fltStat;
		$intRoll = rand(1, 100);
		$intTmpamount = 0;
		while ($intRoll < $intChance)
		  {
		    $rprzedmiot ++;
		    $intTmpamount ++;
		    $intChance = $intChance - 50;
		    if ($intTmpamount == 20)
		      {
			break;
		      }
		  }
		if ($intTmpamount)
		  {
		    $intRoll2 = rand(1,100);
		    if ($player->clas == 'Rzemieślnik')
		      {
			$intBonus = floor($player->skills['alchemy'][1] / 10) - $kuznia->fields['level'];
			if ($intBonus > 50)
			  {
			    $intBonus = 50;
			  }
			if ($intBonus < 0)
			  {
			    $intBonus = 0;
			  }
			$intRoll2 += $intBonus;
		      }
		    $strName = $kuznia -> fields['name'];
		    $intPower = $kuznia->fields['level'];
		    $intMaxpower = $intPower;
		    if ($player -> clas == 'Rzemieślnik' && $intRoll2 > 89 && $objItem -> fields['type'] != 'A')
		      {
			if ($objItem -> fields['type'] != 'P')
			  {
			    $intMaxpower = $kuznia->fields['level'] * 2;
			    $intPower = ceil($kuznia->fields['level'] + $player->skills['alchemy'][1]);
			  }
                        else
			  {
			    $intMaxpower = $kuznia -> fields['level'] * 4;
			    $intPower = ceil($player->skills['alchemy'][1] / 2);
			  }
			$strName = $kuznia -> fields['name']." (S)";
			$rpd += ($kuznia -> fields['level'] * 10);
			if ($intTmpamount > 1)
			  {
			    $rpd += ($kuznia -> fields['level'] * (5 * ($intTmpamount - 1)));
			  }
		      }
                    else
		      {
			$rpd += ($kuznia -> fields['level'] * 5);
			if ($intTmpamount > 1)
			  {
			    $rpd += ($kuznia -> fields['level'] * ($intTmpamount - 1));
			  }
			$intPower = ceil($player->skills['alchemy'][1] / 2);
			if ($objItem -> fields['type'] == 'P' || $objItem->fields['type'] == 'A')
			  {
			    $intMaxpower = $kuznia -> fields['level'] * 2;
			  }
		      }
		  }
                else
		  {
		    $rpd += $kuznia->fields['level'] * 2;
		    if ($objItem -> fields['type'] != 'P')
		      {
			$intMaxpower = $kuznia->fields['level'];
			$intPower = $player->skills['alchemy'][1];
		      }
                    else
		      {
			$intMaxpower = $kuznia -> fields['level'];
			$intPower = ceil($player->skills['alchemy'][1] / 2);
		      }
		    $strName = $kuznia -> fields['name']." (K)";
		    $intTmpamount = 1;
		    $rprzedmiot ++;
		  }
		if ($intPower > $intMaxpower)
		  {
		    $intPower = $intMaxpower;
		  }
		if (!array_key_exists($strName, $arrMaked))
		  {
		    $arrMaked[$strName] = array($intPower, $intTmpamount);
		  }
		else
		  {
		    $arrMaked[$strName][1] += $intTmpamount;
		  }
		$intTmpamount = 0;
	      }
	    foreach ($arrMaked as $key => $value)
	      {
		$test = $db -> Execute("SELECT `id` FROM `potions` WHERE `name`='".$key."' AND `owner`=".$player -> id." AND `status`='K' AND `power`=".$value[0]);
		if (!$test -> fields['id']) 
		  {
		    if ($objItem -> fields['type'] == 'M')
		      {
			$intCost = ceil(($value[0] * 3) / 20);
		      }
		    else
		      {
			$intCost = ceil(((2 * $value[0]) * 3) / 20);
		      }
		    $db -> Execute("INSERT INTO potions (`owner`, `name`, `efect`, `power`, `amount`, `status`, `type`, `cost`) VALUES(".$player -> id.", '".$key."', '".$objItem -> fields['efect']."', ".$value[0].", ".$value[1].", 'K', '".$objItem -> fields['type']."', ".$intCost.")");
		  } 
		else 
		  {
		    $db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$value[1]." WHERE `id`=".$test -> fields['id']);
		  }
		$test -> Close();
	      }
	    if ($player -> clas == 'Rzemieślnik') 
	      {
		$rpd = $rpd * 2;
	      }
	    $smarty -> assign(array ("Name" => $kuznia -> fields['name'], 
				     "Amount" => $rprzedmiot, 
				     "Exp" => $rpd, 
				     "Youmake" => "Wykonałeś",
				     "Pgain" => "razy. Zdobywasz",
				     "Exp_and" => "PD.",
				     "Imaked" => $arrMaked,
				     "Youmade" => "Wykonane mikstury:",
				     "Ipower" => "moc",
				     "Iamount" => "ilość"));
	    $kuznia -> Close();
	    $arrStats2 = array();
	    $intEamount = count($arrStats) + 1;
	    foreach ($arrStats as $strStat)
	      {
		$arrStats2[$strStat] = $rpd / $intEamount;
	      }
	    $player->checkexp(array('alchemy' => ceil($rpd / $intEamount)), $player->id, 'skills');
	    $player->checkexp($arrStats2, $player->id, 'stats');
	    $db -> Execute("UPDATE `herbs` SET `illani`=`illani`-".$rillani.", `illanias`=`illanias`-".$rillanias.", `nutari`=`nutari`-".$rnutari.", `dynallca`=`dynallca`-".$rdynallca." WHERE `gracz`=".$player -> id);
	    $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$fltEnergy." WHERE `id`=".$player -> id);
	  }
      }

    /**
     * Make astral potions
     */
    elseif ($_GET['alchemik'] == 'astral')
      {
	$objAstral = $db -> Execute("SELECT `name` FROM `astral_plans` WHERE `owner`=".$player -> id." AND `name` LIKE 'R%' AND `location`='V'") or die($db -> ErrorMsg());
	if (!$objAstral -> fields['name'])
	  {
	    error('Nie masz takiego przepisu.');
	  }
	$arrHerbs = array("Illani", "Nutari", "Illanias", "Dynalca", 'Energii');
	$arrAmount = array(array(3000, 1000, 2000, 1000, 50),
			   array(5000, 2500, 3500, 1500, 75),
			   array(7000, 3500, 5000, 2000, 100),
			   array(9000, 4500, 6500, 2500, 125),
			   array(12000, 6000, 8000, 3000, 150));
	$arrNames = array("Magiczna esensja", "Gwiezdna maść", "Eliksir Illuminati", "Astralne medium", "Magiczny absynt");
	$arrAviable = array();
	$arrAmount2 = array();
	$arrNumber = array();
	while (!$objAstral -> EOF)
	  {
	    $intKey = str_replace("R", "", $objAstral -> fields['name']);
	    $arrNumber[] = $intKey;
	    $intKey = $intKey - 1;
	    $arrAviable[] = $arrNames[$intKey];
	    $arrAmount2[] = $arrAmount[$intKey];
	    $objAstral -> MoveNext();
	  }
	$objAstral -> Close();

	$smarty -> assign(array("Awelcome" => "Tutaj możesz wykonywać różne astralne mikstury. W danym momencie możesz wykonywać tylko te, których przepisy posiadasz.",
				"Aviablecom" => $arrAviable,
				"Mineralsname" => $arrHerbs,
				"Minamount" => $arrAmount2,
				"Compnumber" => $arrNumber,
				"Abuild" => "Wykonuj",
				"Tname" => "Nazwa",
				"Message" => ''));
	
	/**
	 * Start make potions
	 */
	if (isset($_GET['potion']))
	  {
	    $_GET['potion'] = intval($_GET['potion']);
	    if ($_GET['potion'] < 1 || $_GET['potion'] > 5)
	      {
		error('Zapomij o tym.');
	      }
	    $strName = "R".$_GET['potion'];
	    $objAstral = $db -> Execute("SELECT `amount` FROM `astral_plans` WHERE `owner`=".$player -> id." AND `name`='".$strName."' AND `location`='V'");
	    if (!$objAstral -> fields['amount'])
	      {
		error("Nie masz takiego przepisu");
	      }
	    $objAstral -> Close();
	    $intKey = $_GET['potion'] - 1;
	    $arrSqlherbs = array('illani', 'nutari', 'illanias', 'dynallca');
	    for ($i = 0; $i < 4; $i++)
	      {
		$strSqlname = $arrSqlherbs[$i];
		if ($herb -> fields[$strSqlname] < $arrAmount[$intKey][$i])
		  {
		    error("Nie masz takiej ilości ".$arrHerbs[$i]);
		  }
	      }
	    if ($player -> energy < $arrAmount[$intKey][4])
	      {
		error("Nie masz takiej ilości energii");
	      }
	    /**
	     * Add bonuses to ability
	     */
	    $player->curskills(array('alchemy'), TRUE, TRUE);
	    $arrChance = array(0.3, 0.25, 0.2, 0.15, 0.1);
	    $intChance = floor($player->skills['alchemy'][1] * $arrChance[$intKey]);
	    if ($intChance > 95)
	      {
		$intChance = 95;
	      }
	    $intRoll = rand(1, 100);
	    if ($intRoll <= $intChance)
	      {
		$strCompname = "T".$intKey;
		$objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strCompname."' AND `number`=0 AND `location`='V'");
		if (!$objTest -> fields['amount'])
		  {
		    $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$player -> id.", '".$strCompname."', 0, 1, 'V')");
		  }
                else
		  {
		    $db -> Execute("UPDATE `astral` SET `amount`=`amount`+1 WHERE `owner`=".$player -> id." AND `type`='".$strCompname."' AND `number`=0 AND `location`='V'");
		  }
		$objTest -> Close();
		$arrExp1 = array(500, 1000, 1500, 2000, 2500);
		$arrExp2 = array(1000, 1500, 2000, 2500, 3000);
		$intGainexp = rand($arrExp1[$intKey], $arrExp2[$intKey]);
		$player->checkexp(array('alchemy' => ($intGainexp / 5)), $player->id, 'skills');
		$player->checkexp(array('wisdom' => ($intGainexp / 5),
					'agility' => ($intGainexp / 5),
					'inteli' => ($intGainexp / 5),
					'speed' => ($intGainexp / 5)), $player->id, 'stats');
		$strMessage = "Wykonałeś ".$arrNames[$intKey]."! Zdobywasz ".$intGainexp." punktów doświadczenia Zużyłeś na to:<br />";
	      }
            else
	      {
		$intRoll2 = rand(1, 100);
		if ($player -> clas == 'Rzemieślnik')
		  {
		    if ($intRoll2 < 6)
		      {
			$fltBonus = 0;
		      }
		    elseif ($intRoll2 > 5 && $intRoll2 < 21)
		      {
			$fltBonus = 0.2;
		      }
		    elseif ($intRoll2 > 20 && $intRoll2 < 51)
		      {
			$fltBonus = 0.25;
		      }
		    elseif ($intRoll2 > 50)
		      {
			$fltBonus = 0.33;
		      }
		  }
                else
		  {
		    if ($intRoll2 < 6)
		      {
			$fltBonus = 0;
		      }
		    elseif ($intRoll2 > 5 && $intRoll2 < 21)
		      {
			$fltBonus = 0.4;
		      }
		    elseif ($intRoll2 > 20 && $intRoll2 < 51)
		      {
			$fltBonus = 0.5;
		      }
		    elseif ($intRoll2 > 50)
		      {
			$fltBonus = 0.66;
		      }
		  }
		for ($i = 0; $i < 4; $i ++)
		  {
		    $arrAmount[$intKey][$i] = ceil($arrAmount[$intKey][$i] * $fltBonus);
		  }
		$strMessage = "Próbowałeś wykonać ".$arrNames[$intKey].", niestety nie udało się. Zużyłeś na to:<br />";
	      }
	    for ($i = 0; $i < 4; $i++)
	      {
		$strMessage = $strMessage.$arrHerbs[$i].": ".$arrAmount[$intKey][$i]."<br />";
	      }
	    $smarty -> assign("Message", $strMessage);
	    $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$arrAmount[$intKey][4]." WHERE `id`=".$player -> id);
	    $db -> Execute("UPDATE `herbs` SET `illani`=`illani`-".$arrAmount[$intKey][0].", `illanias`=`illanias`-".$arrAmount[$intKey][2].", `nutari`=`nutari`-".$arrAmount[$intKey][1].", `dynallca`=`dynallca`-".$arrAmount[$intKey][3]." WHERE `gracz`=".$player -> id);
	  }
      }
  }

$herb -> Close();

/**
* Initialization of variables
*/
if (!isset($_GET['alchemik'])) 
{
    $_GET['alchemik'] = '';
}
if (!isset($_GET['rob'])) 
{
    $_GET['rob'] = '';
}
if (!isset($_GET['dalej'])) 
{
    $_GET['dalej'] = '';
}

/**
* Assing variables and display page
*/
$smarty -> assign (array ("Alchemist" => $_GET['alchemik'], 
			  "Make" => $_GET['rob'],
			  "Back" => "Wróć",
			  "Next" => $_GET['dalej']));
$smarty -> display ('alchemist.tpl');

require_once("includes/foot.php");
?>
