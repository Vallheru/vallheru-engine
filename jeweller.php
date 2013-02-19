<?php
/**
 *   File functions:
 *   Jeweller - make rings
 *
 *   @name                 : jeweller.php                            
 *   @copyright            : (C) 2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 19.02.2013
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
// 

$title = "Warsztat jubilerski";
require_once("includes/head.php");

if ($player -> location != 'Ardulith') 
{
    error('Nie znajdujesz się w mieście.');
}

/**
 * Main menu
 */
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
    $smarty -> assign(array("Jewellerinfo" => "Witaj w warsztacie jubilerskim. Możesz tutaj wykonywać różne pierścienie. Tylko rzemieślnicy mogą wykonywać pierścienie z premią do cech.",
                            "Aplans" => "Zakup plan pierścienia",
                            "Aring" => "Wykonuj pierścień",
                            "Amakering" => "Wykonaj artefakt",
                            "Amakering2" => "Wykonaj relikt",
                            "Playerclass" => $player -> clas));
} 
    else
{
    $smarty -> assign(array("Aback" => "Wróć",
                            "Message2" => '',
			    "Tmaked" => '',
			    "Tmaked2" => 0));
}

/**
 * Buy plans
 */
if (isset($_GET['step']) && $_GET['step'] == 'plans')
{
    $arrOwned = array();
    $objOwned = $db->Execute("SELECT `name` FROM `jeweller` WHERE `owner`=".$player->id);
    while (!$objOwned->EOF)
      {
	$arrOwned[] = $objOwned->fields['name'];
	$objOwned->MoveNext();
      }
    $objOwned->Close();
    if ($player -> clas != 'Rzemieślnik')
    {
        $objPlans = $db -> Execute("SELECT `id`, `name`, `cost`, `level` FROM `jeweller` WHERE `id`=1");
    }
        else
    {
        $objPlans = $db -> Execute("SELECT `id`, `name`, `cost`, `level` FROM `jeweller` WHERE `owner`=0 AND `level`<=".$player->skills['jewellry'][1]);
    }
    $arrId = array();
    $arrName = array();
    $arrCost = array();
    $arrLevel = array();
    while (!$objPlans -> EOF)
      {
	if (!in_array($objPlans->fields['name'], $arrOwned))
	  {
	    $arrId[] = $objPlans -> fields['id'];
	    $arrName[] = $objPlans -> fields['name'];
	    $arrCost[] = $objPlans -> fields['cost'];
	    $arrLevel[] = $objPlans -> fields['level'];
	  }
        $objPlans -> MoveNext();
    }
    $objPlans -> Close();
    $smarty -> assign(array("Planid" => $arrId,
                            "Planname" => $arrName,
                            "Plancost" => $arrCost,
                            "Planlevel" => $arrLevel,
                            "Tname" => "Nazwa",
                            "Tcost" => "Cena",
                            "Tlevel" => "Poziom",
                            "Taction" => "Akcja",
                            "Abuy" => "Kup",
                            "Plansinfo" => "Tutaj możesz kupić plany różnych pierścieni."));
    
    if (isset($_GET['buy']))
    {
	checkvalue($_GET['buy']);
        if ($_GET['buy'] > 1 && $player -> clas != 'Rzemieślnik')
        {
            error("Tylko rzemieślnik może kupić ten plan!");
        }
        $objBuy = $db -> Execute("SELECT * FROM `jeweller` WHERE `id`=".$_GET['buy']);
        if ($objBuy -> fields['owner'])
        {
            error('Zapomnij o tym.');
        }
        if ($player -> credits < $objBuy -> fields['cost'])
        {
            error("Nie masz tylu sztuk złota.");
        }
        if (in_array($objBuy->fields['name'], $arrOwned))
        {
            error("Masz już taki plan.");
        }
	if ($objBuy->fields['level'] > $player->skills['jewellry'][1])
	  {
	    error('Nie możesz jeszcze kupić tego planu.');
	  }
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$objBuy -> fields['cost']." WHERE `id`=".$player -> id);
        $db -> Execute("INSERT INTO `jeweller` (`owner`, `name`, `type`, `cost`, `level`, `bonus`) VALUES(".$player -> id.", '".$objBuy -> fields['name']."', '".$objBuy -> fields['type']."', ".$objBuy -> fields['cost'].", ".$objBuy -> fields['level'].", ".$objBuy -> fields['bonus'].")");
        $smarty -> assign("Message2", "Wydałeś <b>".$objBuy -> fields['cost']."</b> sztuk złota i kupiłeś za to plan przedmiotu: <b>".$objBuy -> fields['name']."</b>.");
        $objBuy -> Close();
    }
}

/**
 * Make simple rings
 */
if (isset($_GET['step']) && $_GET['step'] == 'make')
{
    $objPlan = $db -> Execute("SELECT `id` FROM `jeweller` WHERE `name`='pierścień' AND `owner`=".$player -> id);
    if (!$objPlan -> fields['id'])
    {
        error("Nie masz takiego planu!");
    }
    $objPlan -> Close();
    $smarty -> assign(array("Ringinfo" => "Tutaj możesz wykonywać zwykłe pieścienie. Wykonanie jednego pierścienia kosztuje 1 bryłę adamantium oraz 1 punkt energii.",
                            "Amake" => "Wykonaj",
                            "Ramount" => "pierścieni."));
    if (isset($_GET['make']) && $_GET['make'] == 'Y')
    {
	checkvalue($_POST['amount']);
        if ($player -> hp == 0) 
        {
            error("Nie możesz wykonywać pierścieni ponieważ jesteś martwy!");
        }
        $objAdamantium = $db -> Execute("SELECT `adamantium` FROM `minerals` WHERE `owner`=".$player -> id);
        if ($objAdamantium -> fields['adamantium'] < $_POST['amount'])
        {
            error("Nie masz tyle brył adamantium.");
        }
        $objAdamantium -> Close();
        if ($player -> energy < $_POST['amount'])
        {
            error("Nie masz tyle energii");
        }

        /**
         * Add bonuses to ability
         */
	$player->curskills(array('jewellry'), TRUE, TRUE);
	$player->skills['jewellry'][1] += $player->checkbonus('jewellry');
        
        $intChance = $player->skills['jewellry'][1] * 100;
        if ($intChance > 95)
        {
            $intChance = 95;
        }
        $intAmount = 0;
        for ($i = 0; $i < $_POST['amount']; $i++)
        {
            $intRoll = rand(1, 100);
            if ($intRoll < $intChance)
            {
                $intAmount ++;
            }
        }
        $intGainexp = $intAmount;
        if ($player -> clas == 'Rzemieślnik')
        {
            $intGainexp = $intGainexp * 2;
        }
	$player->checkexp(array('jewellry' => $intGainexp), $player->id, 'skills');
        if ($intAmount)
        {
            $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND `name`='pierścień' AND `status`='U'");
            if (!$objTest -> fields['id'])
            {
                $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", 'pierścień', 0, 'I', 3, 1, ".$intAmount.")");
            }
                else
            {
                $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$intAmount." WHERE `id`=".$objTest -> fields['id']);
            }
            $objTest -> Close();
        }
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$_POST['amount']." WHERE `id`=".$player -> id);
        $db -> Execute("UPDATE `minerals` SET `adamantium`=`adamantium`-".$_POST['amount']." WHERE `owner`=".$player -> id);
        $smarty -> assign("Message2", "Wykonałeś <b>".$intAmount."</b> pierścieni. Zdobywasz <b>".$intGainexp."</b> punktów doświadczenia.");
    }
}

/**
 * Make rings with bonus to stats
 */
if (isset($_GET['step']) && $_GET['step'] == 'make2')
{
    if ($player -> clas != 'Rzemieślnik')
    {
        error("Tylko rzemieślnik może wykonywać takie pierścienie!");
    }   

    /**
     * Make rings menu
     */
    if (!isset($_GET['action']))
    {
        $objMaked = $db -> Execute("SELECT `id`, `name`, `n_energy`, `u_energy` FROM `jeweller_work` WHERE `owner`=".$player -> id." AND `type`='N'");
        if (!$objMaked -> fields['id'])
        {
            $objPlans = $db -> Execute("SELECT `id`, `name`, `level`, `bonus` FROM `jeweller` WHERE `owner`=".$player -> id." AND `name`!='pierścień' ORDER BY `level`");
            if (!$objPlans -> fields['name'])
            {
                error("Nie masz jeszcze jakichkolwiek planów pierścieni.");
            }
            $arrId = array();
            $arrName = array();
            $arrLevel = array();
            $arrAdam = array();
            $arrCryst = array();
            $arrMeteor = array();
            $arrEnergy = array();
            $arrChange = array();
            $arrBonus = array();
            while (!$objPlans -> EOF)
            {
                $arrId[] = $objPlans -> fields['id'];
                $arrName[] = $objPlans -> fields['name'];
                $arrLevel[] = $objPlans -> fields['level'];
                $arrAdam[] = $objPlans -> fields['level'];
                $arrCryst[] = ceil($objPlans -> fields['level'] / 2);
                $arrMeteor[] = ceil($objPlans -> fields['level'] / 4);
                $arrBonus[] = $objPlans -> fields['bonus'];
                $arrEnergy[] = $objPlans -> fields['level'];
                $intChange = $objPlans -> fields['level'] * 4;
                if ($player->skills['jewellry'][1] >= $intChange)
                {
                    $arrChange[] = "Tak";
                }
                    else
                {
                    $arrChange[] = "Nie";
                }
                $objPlans -> MoveNext();
            }
            $objPlans -> Close();
            $smarty -> assign(array("Ringinfo" => "Tutaj możesz wykonywać pierścienie z premią do cech. Wykonanie jednego pierścienia kosztuje punkty energii oraz podaną przy każdym planie liczbę brył adamantium, kryształów bądź brył meteorytu. Jeżeli posiadasz odpowiednio wysoką wartość umiejętności jubilerstwo możesz samodzielnie wybrać do jakiej cechy ma mieć premię pierścień.",
                                    "Tname" => "Nazwa",
                                    "Tlevel" => "Poziom",
                                    "Tadam" => "Adamantium",
                                    "Tcryst" => "Kryształ",
                                    "Tmeteor" => "Meteoryt",
                                    "Tenergy" => "Energii",
                                    "Tbonus" => "Maks. premia",
                                    "Tchange" => "Wybór cechy",
                                    "Rname" => $arrName,
                                    "Rlevel" => $arrLevel,
                                    "Radam" => $arrAdam,
                                    "Rcryst" => $arrCryst,
                                    "Rmeteor" => $arrMeteor,
                                    "Renergy" => $arrEnergy,
                                    "Rbonus" => $arrBonus,
                                    "Rid" => $arrId,
                                    "Rchange" => $arrChange,
                                    "Action" => '',
                                    "Make" => '',
                                    "Maked" => ''));

            /**
             * Select make ring
             */
            if (isset($_GET['make']))
            {
		checkvalue($_GET['make']);
                if ($player -> hp == 0) 
                {
                  error("Nie możesz wykonywać pierścieni ponieważ jesteś martwy!");
                }
                if ($objMaked -> fields['id'])
                {
                    error('Zapomnij o tym.');
                }
                $intKey = array_search($_GET['make'], $arrId);
		if ($intKey === FALSE)
		  {
		    error('Zapomnij o tym.');
		  }
		$objMinerals = $db -> Execute("SELECT `owner`, `adamantium`, `crystal`, `meteor` FROM `minerals` WHERE `owner`=".$player -> id);
		if (!$objMinerals->fields['owner'])
		  {
		    error('Nie posiadasz minerałów potrzebnych do tworzenia pierścieni.');
		  }
		$objRings = $db -> Execute("SELECT `id`, `amount` FROM `equipment` WHERE `owner`=".$player -> id." AND `name`='pierścień' AND `status`='U'");
		if (!$objRings -> fields['id'])
		  {
		    error("Nie posiadasz zwykłych pierścieni.");
		  }
		$strYouhave = 'Posiadasz <b>'.$objMinerals->fields['adamantium'].'</b> sztuk <b>adamantium</b>, <b>'.$objMinerals->fields['crystal'].' kryształów</b>, <b>'.$objMinerals->fields['meteor'].'</b> sztuk <b>meteorytu</b> oraz <b>'.$objRings->fields['amount'].' pierścieni</b>.';
		$objRings->Close();
		$objMinerals->Close();
                $arrBonus = array('zręczności', 'siły', 'inteligencji', 'siły woli', 'szybkości', 'kondycji');
                if ($arrChange[$intKey] == 'Tak')
                {
                    $strChange = 'Y';
                }
                    else
                {
                    $strChange = 'N';
                }
                $smarty -> assign(array("Make" => $_GET['make'],
                                        "Rname2" => $arrName[$intKey],
                                        "Rbonus2" => $arrBonus,
                                        "Pjeweller" => $player->skills['jewellry'][1],
                                        "Change" => $strChange,
                                        "Youmake" => "Wykonaj",
                                        "Ramount" => "przeznaczając na to",
                                        "Withbon" => "z premią do",
                                        "Tenergy3" => "energii.",
					"Youhave" => $strYouhave));
            }
        }
            else
        {
            $smarty -> assign(array("Ringinfo" => "Obecnie pracujesz już nad pierścieniem. Aby kontynuować pracę, po prostu podaj ile energii chcesz użyć do wykonania tego przedmiotu. Pamiętaj że nie możesz przeznaczyć więcej energii niż potrzeba.",
                                    "Tname" => 'Nazwa',
                                    "Tenergy" => 'Energii',
                                    "Tenergy2" => 'Użytej energii',
                                    "Youcontinue" => "Pracuj nad",
                                    "Tenergy3" => "energii.",
                                    "Ramount" => "przeznaczając na to",
                                    "Rname" => $objMaked -> fields['name'],
                                    "Renergy" => $objMaked -> fields['n_energy'],
                                    "Renergy2" => $objMaked -> fields['u_energy'],
                                    "Action" => '',
                                    "Make" => '',
                                    "Maked" => $objMaked -> fields['id']));
        }
        $objMaked -> Close();
    }

    /**
     * Continue create rings
     */
    if (isset($_GET['action']) && $_GET['action'] == 'continue')
    {
	checkvalue($_POST['make']);
	checkvalue($_POST['amount']);
        if ($player -> hp == 0) 
        {
            error("Nie możesz wykonywać pierścieni ponieważ jesteś martwy!");
        }
        $objRing = $db -> Execute("SELECT `owner`, `name`, `n_energy`, `u_energy`, `bonus` FROM `jeweller_work` WHERE `id`=".$_POST['make']);
        if (!$objRing -> fields['owner'] || $objRing -> fields['owner'] != $player -> id)
        {
            error("Nie pracujesz nad takim pierścieniem!");
        }
        $intEnergy = $_POST['amount'] + $objRing -> fields['u_energy'];
        if ($intEnergy > $objRing -> fields['n_energy'])
        {
            error("Nie możesz przeznaczyć aż tyle energii.");
        }

        /**
         * When player assign need energy
         */
        if ($intEnergy == $objRing -> fields['n_energy'])
        {
            /**
             * Add bonuses to ability
             */
	    $player->curskills(array('jewellry'), TRUE, TRUE);
	    $player->skills['jewellry'][1] += $player->checkbonus('jewellry');

            $objRing2 = $db -> Execute("SELECT `level`, `bonus`, `cost`, `type` FROM `jeweller` WHERE `owner`=".$player -> id." AND `name`='".$objRing -> fields['name']."'");
           
            $intGainexp = 0;

            $arrStats = array('zręczności', 'siły', 'inteligencji', 'siły woli', 'szybkości', 'kondycji');
            $arrStats2 = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition');
            $intKey = array_search($objRing -> fields['bonus'], $arrStats);
	    $strStat = $arrStats2[$intKey];
            $intStat = $player->stats[$strStat][2];
	    $player->clearbless(array($strStat));

	    $intChance = (($player->skills['jewellry'][1] + $intStat) / $objRing2 -> fields['level']) * 50;
            if ($intChance > 95)
            {
                $intChance = 95;
            }
            $intRoll = rand(1, 100);

            /**
             * Success
             */
            if ($intRoll <= $intChance)
            {
                $intRoll2 = rand(0, $player->skills['jewellry'][1]);
                $intBonus = floor($intRoll2 + ($intStat / 50));
                if ($intBonus < 1)
                {
                    $intBonus = 1;
                }
                if ($intBonus > $objRing2 -> fields['bonus'])
                {
                    $intBonus = $objRing2 -> fields['bonus'];
                }
                $strName = $objRing -> fields['name']." ".$objRing -> fields['bonus'];
                $intCost = ceil($objRing2 -> fields['cost'] / 20);
                $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND status='U' AND `name`='".$strName."' AND `power`=".$intBonus." AND `cost`=".$intCost);
                if (!$objTest -> fields['id'])
                {
                    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", '".$strName."', '".$intBonus."', 'U', '".$objRing2 -> fields['type']."', '".$intCost."', '".$objRing2 -> fields['level']."', '1')");
                }
                    else
                {
                    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest -> fields['id']);
                }
                $objTest -> Close();
                $intGainexp = $objRing2 -> fields['level'] * 10;
                $smarty -> assign("Message2", "Wykonałeś <b>".$strName." (+".$intBonus.")"."</b>. Zdobywasz <b>".$intGainexp."</b> punktów doświadczenia.<br />");
            }
                else
            {
                $intGainexp = 2;
                $smarty -> assign("Message2", "Próbowałeś wykonać <b>".$objRing -> fields['name']."</b> niestety nie udało się.<br />");
            }
            $objRing2 -> Close();
            $db -> Execute("DELETE FROM `jeweller_work` WHERE `id`=".$_POST['make']);
	    $player->checkexp(array('jewellry' => ($intGainexp / 2)), $player->id, 'skills');
	    $player->checkexp(array($strStat => ($intGainexp / 2)), $player->id, 'stats');
        }
            else
        {
            $db -> Execute("UPDATE `jeweller_work` SET `u_energy`=".$intEnergy." WHERE `id`=".$_POST['make']);
            $smarty -> assign("Message2", "Próbowałeś wykonać <b>".$objRing -> fields['name']."</b> jednak wykonałeś go tylko częściowo.");
        }
        $objRing -> Close();
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$_POST['amount']." WHERE `id`=".$player -> id);
        $smarty -> assign("Action", $_GET['action']);
    }

    /**
     * Start create rings
     */
    if (isset($_GET['action']) && $_GET['action'] == 'create')
    {
	checkvalue($_POST['make']);
	checkvalue($_POST['amount']);
        $objRing = $db -> Execute("SELECT `owner`, `name`, `level`, `bonus`, `cost`, `type` FROM `jeweller` WHERE `id`=".$_POST['make']);
        if (!$objRing -> fields['owner'] || $objRing -> fields['owner'] != $player -> id)
        {
            error("Nie masz takiego planu!");
        }
        $intMake = floor($_POST['amount'] / $objRing -> fields['level']);
        if ($intMake < 1)
        {
            $intNeed = 1;
        }
            else
        {
            $intNeed = $intMake;
        }
        $intAdam = $objRing -> fields['level'] * $intNeed;
        $intCryst = ceil($objRing -> fields['level'] / 2) * $intNeed;
        $intMeteor = ceil($objRing -> fields['level'] / 4) * $intNeed;
        $objMinerals = $db -> Execute("SELECT `adamantium`, `crystal`, `meteor` FROM `minerals` WHERE `owner`=".$player -> id);
        if ($objMinerals -> fields['adamantium'] < $intAdam || $objMinerals -> fields['crystal'] < $intCryst || $objMinerals -> fields['meteor'] < $intMeteor)
        {
            error("Nie masz tyle minerałów.");
        }
        $objMinerals -> Close();
        if ($player -> energy < $_POST['amount'])
        {
            error("Nie masz tyle energii.");
        }
        $objRings = $db -> Execute("SELECT `id`, `amount` FROM `equipment` WHERE `owner`=".$player -> id." AND `name`='pierścień' AND `status`='U'");
        if ($objRings -> fields['amount'] < $intMake)
        {
            error("Nie masz tylu pierścieni.");
        }

        /**
         * Add bonuses to ability
         */
	$player->curskills(array('jewellry'), TRUE, TRUE);
	$player->skills['jewellry'][1] += $player->checkbonus('jewellry');

        /**
         * Which stats have bonus
         */
        $intChange = $objRing -> fields['level'] * 4;
	$arrStats = array('zręczności', 'siły', 'inteligencji', 'siły woli', 'szybkości', 'kondycji');
        $arrStats2 = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition');
        if (isset($_POST['bonus']) && $player->skills['jewellry'][1] >= $intChange)
	  {
	    $strStat = $_POST['bonus'];
            $intKey = array_search($_POST['bonus'], $arrStats);
	    $strStat2 = $arrStats2[$intKey];
            $intStat = $player->stats[$strStat2][2];
	  }
	else
	  {
            $intRoll = rand(0, 5);
            $strStat = $arrStats[$intRoll];
	    $strStat2 = $arrStats2[$intRoll];
            $intStat = $player->stats[$strStat2][2];
	  }
	$player->clearbless(array($strStat2));

	$intChance = (($player->skills['jewellry'][1] + $intStat) / $objRing -> fields['level']) * 50;
        if ($intChance > 95)
        {
            $intChance = 95;
        }
        
        /**
         * Start create items
         */
        $arrBonus = array();
        $arrAmount = array();
        $intAmount2 = 0;
        for ($i = 0; $i < $intMake; $i++)
        {
            $intRoll = rand(1, 100);
            if ($intRoll <= $intChance)
            {
                $intAmount2++;
                $intRoll2 = rand(0, $player->skills['jewellry'][1]);
                $intBonus = floor($intRoll2 + ($intStat / 5));
                if ($intBonus < 1)
                {
                    $intBonus = 1;
                }
                if ($intBonus > $objRing -> fields['bonus'])
                {
                    $intBonus = $objRing -> fields['bonus'];
                }
                $intKey = array_search($intBonus, $arrBonus);
                if (!empty($intKey) || $intKey === 0)
                {
                    $arrAmount[$intKey]++;
                }
                    else
                {
                    $arrBonus[] = $intBonus;
                    $arrAmount[] = 1;
                }
            }
        }

        /**
         * Write to database and show info
         */
        if ($intMake > 0)
        {
            $i = 0;
            $strName = $objRing -> fields['name']." ".$strStat;
            $intCost = ceil($objRing -> fields['cost'] / 20);
            foreach ($arrAmount as $intAmount)
            {
                $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND status='U' AND `name`='".$strName."' AND `power`=".$arrBonus[$i]." AND `cost`=".$intCost);
                if (!$objTest -> fields['id'])
                {
                    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", '".$strName."', '".$arrBonus[$i]."', 'U', '".$objRing -> fields['type']."', '".$intCost."', '".$objRing -> fields['level']."', '".$intAmount."')");
                }
                    else
                {
                    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$intAmount." WHERE `id`=".$objTest -> fields['id']);
                }
                $objTest -> Close();
                $i ++;
            }
            $intGainexp = $objRing -> fields['level'] * 10;
            $intGainexp = floor($intGainexp * $intAmount2);
            $smarty -> assign(array("Message2" => "Wykonałeś <b>".$intAmount2."</b> </b> sztuk <b>".$strName."</b>. Zdobywasz <b>".$intGainexp."</b> punktów doświadczenia.<br />",
				    "Tmaked" => "Wykonane pierścienie",
				    "Tamount" => "ilość",
				    "Iamount" => $arrAmount,
				    "Ibonus" => $arrBonus,
				    "Iname" => $strName));
        }
            else
        {
            $intAdam = $objRing -> fields['level'];
            $intCryst = ceil($objRing -> fields['level'] / 2);
            $intMeteor = ceil($objRing -> fields['level'] / 4);
            $intGainexp = 0;
            $intEnergy2 = $objRing -> fields['level'];
            $intEnergy = $_POST['amount'];
            $intMake = 1;
            $db -> Execute("INSERT INTO `jeweller_work` (`owner`, `name`, `n_energy`, `u_energy`, `bonus`, `type`) VALUES(".$player -> id.", '".$objRing -> fields['name']."', ".$intEnergy2.", ".$_POST['amount'].", '".$strStat."', 'N')");
            $smarty -> assign("Message2", "Próbowałeś wykonać <b>".$objRing -> fields['name']."</b> jednak wykonałeś go tylko częściowo.");
        }
	$player->checkexp(array('jewellry' => ($intGainexp / 2)), $player->id, 'skills');
	$player->checkexp(array($strStat2 => ($intGainexp / 2)), $player->id, 'stats');
        $db -> Execute("UPDATE `minerals` SET `adamantium`=`adamantium`-".$intAdam.", `crystal`=`crystal`-".$intCryst.", `meteor`=`meteor`-".$intMeteor." WHERE `owner`=".$player -> id);
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$_POST['amount']." WHERE `id`=".$player -> id);
        if ($objRings -> fields['amount'] == $intMake)
        {
            $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objRings -> fields['id']);
        }
            else
        {
            $db -> Execute("UPDATE `equipment` SET `amount`=`amount`-".$intMake." WHERE `id`=".$objRings -> fields['id']);
        }
        $objRings -> Close();
        $smarty -> assign("Action", $_GET['action']);
    }
}

/**
 * Make special rings with bonus to stats
 */
if (isset($_GET['step']) && $_GET['step'] == 'make3')
{
    if ($player -> clas != 'Rzemieślnik')
    {
        error("Tylko rzemieślnik może wykonywać takie pierścienie!");
    }

    /**
     * Main menu
     */
    $objMaked = $db -> Execute("SELECT `id`, `name`, `n_energy`, `u_energy` FROM `jeweller_work` WHERE `owner`=".$player -> id." AND `type`='S'");
    if (!$objMaked -> fields['id'])
    {
        $objPlans = $db -> Execute("SELECT `name`, `level` FROM `jeweller` WHERE `owner`=".$player -> id." AND `name`!='pierścień' ORDER BY `level`");
        if (!$objPlans -> fields['name'])
        {
            error("Nie masz jeszcze jakichkolwiek planów pierścieni.");
        }
        $arrName = array();
        $arrLevel = array();
        $arrMeteor = array();
        $arrEnergy = array();
        while (!$objPlans -> EOF)
        {
            $arrName[] = $objPlans -> fields['name'];
            $arrLevel[] = $objPlans -> fields['level'];
            $arrMeteor[] = ceil($objPlans -> fields['level'] / 2);
            $arrEnergy[] = $objPlans -> fields['level'] * 1.5;
            $objPlans -> MoveNext();
        }
        $objPlans -> Close();
        $objRings = $db -> Execute("SELECT `id`, `name`, `amount`, `minlev`, `power`, `cost` FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='U' AND `type`='I' AND `name`!='pierścień' AND `name` NOT LIKE 'Gnomi %' AND `name` NOT LIKE 'Elfi %' AND `name` NOT LIKE 'Krasnoludzki %' AND `name` NOT LIKE 'Boski %'");
        $arrRid = array();
        $arrRname = array();
        $arrRamount = array();
        $arrRpower = array();
        $arrRlevel = array();
        $arrRcost = array();
        while (!$objRings -> EOF)
        {
            $arrRid[] = $objRings -> fields['id'];
            $arrRname[] = $objRings -> fields['name'];
            $arrRamount[] = $objRings -> fields['amount'];
            $arrRpower[] = $objRings -> fields['power'];
            $arrRlevel[] = $objRings -> fields['minlev'];
            $arrRcost[] = $objRings -> fields['cost'];
            $objRings -> MoveNext();
        }
        $objRings -> Close();
	$objMinerals = $db -> Execute("SELECT `meteor` FROM `minerals` WHERE `owner`=".$player->id);
	if (!$objMinerals->fields['meteor'])
	  {
	    $strMeteor = 'Nie posiadasz meteoru.';
	  }
	else
	  {
	    $strMeteor = 'Posiadasz <b>'.$objMinerals->fields['meteor'].'</b> sztuk <b>meteorytu</b>.';
	  }

        $smarty -> assign(array("Ringinfo" => "Tutaj możesz wykonywać specjalne pierścienie (elfie, krasoludzkie, boskie itp). Aby móc je wykonać potrzebujesz odpowiedniej ilości brył meteorytu, energii, pierścieni z premią do jakiejś cechy oraz planu danego pierścienia. Poniżej znajduje się lista planów pierścieni jakie możesz ulepszać",
                                "Tname" => "Nazwa",
                                "Tlevel" => "Poziom",
                                "Tmeteor" => "Meteoryt",
                                "Tenergy" => "Energii",
                                "Amake" => "Przekuj",
                                "Ramount" => "sztuk",
                                "Onspecial" => "na relikty,",
                                "Ramount3" => "ilość:",
                                "Ramount4" => "przeznaczając na to",
                                "Renergy2" => "energii.",
                                "Maked" => '',
                                "Rname" => $arrName,
                                "Rlevel" => $arrLevel,
                                "Rmeteor" => $arrMeteor,
                                "Renergy" => $arrEnergy,
                                "Rid2" => $arrRid,
                                "Rname2" => $arrRname,
                                "Ramount2" => $arrRamount,
                                "Rpower" => $arrRpower,
				"Ameteor" => $strMeteor));
    }
        else
    {
        $smarty -> assign(array("Ringinfo" => "Obecnie pracujesz już nad pierścieniem. Aby kontynuować pracę, po prostu podaj ile energii chcesz użyć do wykonania tego przedmiotu. Pamiętaj że nie możesz przeznaczyć więcej energii niż potrzeba.",
                                "Tname" => "Nazwa",
                                "Tenergy" => "Energii",
                                "Tenergy2" => "Użytej energii",
                                "Youcontinue" => "Pracuj nad",
                                "Tenergy3" => "energii.",
                                "Ramount" => "ilość:",
                                "Rname" => $objMaked -> fields['name'],
                                "Renergy" => $objMaked -> fields['n_energy'],
                                "Renergy2" => $objMaked -> fields['u_energy'],
                                "Action" => '',
                                "Make" => '',
                                "Maked" => $objMaked -> fields['id']));
    }

    /**
     * Continue create special rings
     */
    if (isset($_GET['action']) && $_GET['action'] == 'continue')
    {
        if (floatval($_POST['amount']) <= 0) 
        {
            error('Zapomnij o tym.');
        }
	checkvalue($_POST['make']);
        if ($player -> hp == 0) 
        {
            error("Nie możesz wykonywać pierścieni ponieważ jesteś martwy!");
        }
        $objRing = $db -> Execute("SELECT `owner`, `name`, `n_energy`, `u_energy`, `bonus` FROM `jeweller_work` WHERE `id`=".$_POST['make']);
        if (!$objRing -> fields['owner'] || $objRing -> fields['owner'] != $player -> id)
        {
            error("Nie pracujesz nad takim pierścieniem!");
        }
        $intEnergy = $_POST['amount'] + $objRing -> fields['u_energy'];
        if ($intEnergy > $objRing -> fields['n_energy'])
        {
            error("Nie możesz przeznaczyć aż tyle energii.");
        }

        /**
         * When player assign need energy
         */
        if ($intEnergy == $objRing -> fields['n_energy'])
        {
            /**
             * Add bonuses to ability
             */
	    $player->curskills(array('jewellry'), TRUE, TRUE);
	    $player->skills['jewellry'][1] += $player->checkbonus('jewellry');

            /**
             * Select ring name
             */
            $arrRings = array('inteligencji', 'siły woli', 'zręczności', 'szybkości', 'siły', 'kondycji');
	    $arrStats = array('inteli', 'wisdom', 'agility', 'speed', 'strength', 'condition');
            $arrPrefix = array('Gnomi ', 'Elfi ', 'Krasnoludzki ');
            $strName = $objRing -> fields['name'];
	    $intKey2 = 0;  
            $arrRingtype = explode(" ", $strName);
            $intAmount2 = count($arrRingtype) - 1;
            $intKey2 = array_search($arrRingtype[$intAmount2], $arrRings);
            if ($intKey2 < 5)
            {
                $intName = $intAmount2 - 1;
            }
                else
            {
                $intName = $intAmount2 - 2;
            }
            $strName2 = '';
            for ($i = 0; $i <= $intName; $i++)
            {
                if (!empty($strName2))
                {
                  $strName2 = $strName2." ";
                }
                $strName2 = $strName2.$arrRingtype[$i];
	    }
	    $strStat = $arrStats[$intKey2];
            $intKey2 = floor($intKey2 / 2);
            $strPrefix = $arrPrefix[$intKey2];
            
            $objRing2 = $db -> Execute("SELECT `level`, `cost`, `type` FROM `jeweller` WHERE `owner`=".$player -> id." AND `name`='".$strName2."'");

            $intChance = floor((($player->skills['jewellry'][1] + $player->stats[$strStat]) / 50) * 0.5) + 5;
	    $player->clearbless(array($strStat));
            if ($intChance > 15)
            {
                $intChance = 15;
            }
            
            $intGainexp = 2;

            $intRoll = rand(1, 100);

            /**
             * Success
             */
            if ($intRoll <= $intChance)
            {
                $blnGod = false;
                $intRoll = rand(1, 100);
                if ($intRoll == 1)
                {
                    $intRoll2 = rand(1, 2);
                    if ($intRoll2 == 1)
                    {
                        $intGainexp =  $objRing -> fields['n_energy'] * 50;
                        $blnGod = true;
                    }
                }
                if ($intRoll <= $intChance && !$blnGod)
                {
                    $intGainexp = $objRing -> fields['n_energy'] * 25;
                }
                
                $intPower = (int)$objRing -> fields['bonus'];
                $intCost = ceil($objRing2 -> fields['cost'] / 10);
                if ($blnGod)
                {
                    $strName2 = 'Boski '.$strName;
                    $intPower = $intPower * 4;
                    $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND `name`='".$strName2."' AND `status`='U' AND `cost`=".$intCost." AND `power`=".$intPower);
                    if (!$objTest -> fields['id'])
                    {
                        $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", '".$strName2."', '".$intPower."', 'U', 'I', '".$intCost."', '".$intLevel."', '".$intGod."')");
                    }
                        else
                    {
                        $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$intGod." WHERE `id`=".$player -> id);
                    }
                    $objTest -> Close();
                }
                    else
                {
                    $strName2 = $strPrefix.$strName;
                    $intPower = $intPower * 2;
                    $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND `name`='".$strName2."' AND `status`='U' AND `cost`=".$intCost." AND `power`=".$intPower);
                    if (!$objTest -> fields['id'])
                    {
                        $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", '".$strName2."', '".$intPower."', 'U', 'I', '".$intCost."', '".$intLevel."', 1)");
                    }
                    else
                    {
                        $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$intSpecial." WHERE `id`=".$player -> id);
                    }
                    $objTest -> Close();
                }
                $smarty -> assign("Message", "Przekułeś <b>".$strName2." (+ ".$intPower.")</b>. Zdobywasz <b>".$intGainexp."</b> PD oraz <b>".$intAbility."</b> poziomów umiejętności jubilerstwo.<br />");
            }
                else
            {
                $smarty -> assign("Message2", "<br />Próbowałeś wykonać <b>".$strName."</b> niestety nie udało się.<br />");
            }
	    $player->checkexp(array('jewellry' => ($intGainexp / 2)), $player->id, 'skills');
	    $player->checkexp(array($strStat => ($intGainexp / 2)), $player->id, 'stats');
            $objRing2 -> Close();
            $db -> Execute("DELETE FROM `jeweller_work` WHERE `id`=".$_POST['make']);
        }
            else
        {
            $db -> Execute("UPDATE `jeweller_work` SET `u_energy`=".$intEnergy." WHERE `id`=".$_POST['make']);
            $smarty -> assign("Message2", "<br />Próbowałeś wykonać <b>".$objRing -> fields['name']."</b> jednak wykonałeś go tylko częściowo.");
        }
        $objRing -> Close();
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$_POST['amount']." WHERE `id`=".$player -> id);
    }

    /**
     * Start create special rings
     */
    if (isset($_GET['action']) && $_GET['action'] == 'create')
    {
        if (!isset($_POST['rings']) || floatval($_POST['amount']) <= 0) 
        {
            error('Zapomnij o tym.');
        }
	checkvalue($_POST['rings']);
        if ($objMaked -> fields['id'])
        {
            error('Zapomnij o tym.');
        }
        if ($player -> hp == 0) 
        {
            error("Nie możesz wykonywać pierścieni ponieważ jesteś martwy!");
        }
        $intKey = array_search($_POST['rings'], $arrRid);
        $intLevel = $arrRlevel[$intKey];
        $intMake = floor($_POST['amount'] / ($intLevel * 1.5));
        if (!$intMake)
        {
            $intMake = 1;
        }
        $intMeteor = ceil($intLevel / 2) * $intMake;
        $objMeteor = $db -> Execute("SELECT `meteor` FROM `minerals` WHERE `owner`=".$player -> id);
        if ($objMeteor -> fields['meteor'] < $intMeteor)
        {
            error("Nie masz takiej ilości brył meteorytu.");
        }
        $objMeteor -> Close();
        $intEnergys = $intLevel * 1.5;
        $intEnergy = $intEnergys * $intMake;
        if ($player -> energy < $intEnergy)
        {
            error("Nie masz tyle energii.");
        }
        $intRamount = $arrRamount[$intKey];
        if ($intRamount < $intMake)
        {
            error("Nie masz tylu pierścieni.");
        }
    
        /**
         * Select ring name
         */
	$arrRings = array('zręczności', 'szybkości', 'siły', 'kondycji', 'inteligencji', 'siły woli');
	$arrStats = array('agility', 'speed', 'strength', 'condition', 'inteli', 'wisdom');
	$arrPrefix = array('Elfi ', 'Krasnoludzki ', 'Gnomi ');
        $strName = $arrRname[$intKey];
        $arrRingtype = explode(" ", $strName);
        $intAmount2 = count($arrRingtype) - 1;
        $intKey2 = array_search($arrRingtype[$intAmount2], $arrRings);
	$strStat = $arrStats[$intKey2];
        $intKey2 = floor($intKey2 / 2);
        $strPrefix = $arrPrefix[$intKey2];
        
        /**
         * Add bonuses to ability
         */
	$player->curskills(array('jewellry'), TRUE, TRUE);
	$player->skills['jewellry'][1] += $player->checkbonus('jewellry');

        $intChance = floor((($player->skills['jewellry'][1] + $player->stats[$strStat][2]) / 50) * 0.5) + 5;
	$player->clearbless(array($strStat));
        if ($intChance > 15)
        {
            $intChance = 15;
        }

        /**
         * Start create item
         */
        $intGod = 0;
        $intSpecial = 0;
        $intGainexp = 2;
        $intAbility = 0;
        for ($i = 0; $i < $intMake; $i++)
        {
            $intRoll = rand(1, 100);
            if ($intRoll == 1)
            {
                $intRoll2 = rand(1, 2);
                if ($intRoll2 == 1)
                {
                    $intGod ++;
                    $intGainexp += ($intEnergys * 50);
                    continue;
                }
            }
            if ($intRoll <= $intChance)
            {
                $intSpecial ++;
                $intGainexp += ($intEnergys * 25);
            }
                else
            {
		$intGainexp += 2;
            }
        }

        /**
         * Write to database and show info
         */
	$arrBonus = array(0, 0);
        if ($intMake)
        {
            $intCost = ceil($arrRcost[$intKey] / 10);
            $intPower = $arrRpower[$intKey];
            if ($intGod)
            {
                $strName2 = 'Boski '.$strName;
                $intPower = $intPower * 4;
		$arrBonus[0] = $intPower;
                $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND `name`='".$strName2."' AND `status`='U' AND `cost`=".$intCost." AND `power`=".$intPower);
                if (!$objTest -> fields['id'])
                {
                    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", '".$strName2."', '".$intPower."', 'U', 'I', '".$intCost."', '".$intLevel."', '".$intGod."')");
                }
                    else
                {
                    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$intGod." WHERE `id`=".$player -> id);
                }
                $objTest -> Close();
            }
            $intPower = $arrRpower[$intKey];
            if ($intSpecial)
            {
                $strName2 = $strPrefix.$strName;
                $intPower = $intPower * 2;
		$arrBonus[1] = $intPower;
                $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND `name`='".$strName2."' AND `status`='U' AND `cost`=".$intCost." AND `power`=".$intPower);
                if (!$objTest -> fields['id'])
                {
                    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", '".$strName2."', '".$intPower."', 'U', 'I', '".$intCost."', '".$intLevel."', '".$intSpecial."')");
                }
                    else
                {
                  $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$intSpecial." WHERE `id`=".$objTest->fields['id']);
                }
                $objTest -> Close();
            }
            $intRings = $intGod + $intSpecial;
            $smarty -> assign(array("Message2" => "<br /><br />Przekułeś <b>".$intRings."</b>  sztuk <b>".$strName."</b>. Zdobywasz <b>".$intGainexp."</b> punktów doświadczenia.<br />",
				    "Tmaked2" => 1,
				    "Tamount" => "ilość",
				    "Iamount" => array($intGod, $intSpecial),
				    "Ibonus" => $arrBonus,
				    "Iname" => array('Boski'.$strName, $strPrefix.$strName)));
        }
           else
        {
            $intMeteor = ceil($intLevel / 2);
            $intGainexp = 0;
            $intEnergy2 = ($intLevel * 1.5);
            $intAbility = 0;
            $intEnergy = $_POST['amount'];
            $intMake = 1;
            $intPower = $arrRpower[$intKey];
            $db -> Execute("INSERT INTO `jeweller_work` (`owner`, `name`, `n_energy`, `u_energy`, `bonus`, `type`) VALUES(".$player -> id.", '".$strName."', ".$intEnergy2.", ".$_POST['amount'].", '".$intPower."', 'S')");
            $smarty -> assign("Message", "<br />Próbowałeś wykonać <b>".$strName."</b> jednak wykonałeś go tylko częściowo.");
        }
        $db -> Execute("UPDATE `minerals` SET `meteor`=`meteor`-".$intMeteor." WHERE `owner`=".$player -> id);
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
	$player->checkexp(array('jewellry' => ($intGainexp / 2)), $player->id, 'skills');
	$player->checkexp(array($strStat => ($intGainexp / 2)), $player->id, 'stats');
        if ($intRamount == $intMake)
        {
            $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$_POST['rings']);
        }
            else
        {
            $db -> Execute("UPDATE `equipment` SET `amount`=`amount`-".$intMake." WHERE `id`=".$_POST['rings']);
        }
    }
}

/**
* Assign variables to template and display page
*/
$smarty -> assign("Step", $_GET['step']);
$smarty -> display('jeweller.tpl');

require_once("includes/foot.php");
?>
