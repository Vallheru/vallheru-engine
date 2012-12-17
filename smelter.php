<?php
/**
 *   File functions:
 *   Smelter - smelt minerals
 *
 *   @name                 : smelter.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 17.12.2012
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

$title = "Huta";
require_once("includes/head.php");

if ($player -> location != 'Altara') 
{
    error ("Nie znajdujesz się w mieście.");
}

$objSmelter = $db -> Execute("SELECT `level` FROM `smelter` WHERE `owner`=".$player -> id);

/**
 * Upgrade smelter
 */
if (isset($_GET['step']))
  {

    /**
     * Upgrade smelter
     */
    if ($_GET['step'] == 'upgrade')
      {
	if (!isset($objSmelter -> fields['level']))
	  {
	    $intSmelterlevel = 0;
	  }
        else
	  {
	    $intSmelterlevel = $objSmelter -> fields['level'];
	  }

	/**
	 * Start upgrade
	 */
	if (isset($_GET['upgrade']))
	  {
	    if ($objSmelter -> fields['level'] == 5)
	      {
		error("Nie możesz więcej rozbudowywać huty!");
	      }
	    $arrCost = array(1000, 5000, 20000, 60000, 120000);
	    if ($player -> credits < $arrCost[$intSmelterlevel])
	      {
		message('error', "Nie masz tyle sztuk złota!");
	      }
	    else
	      {
		if ($intSmelterlevel == 0)
		  {
		    $db -> Execute("INSERT INTO `smelter` (`owner`, `level`) VALUES(".$player -> id.", 1)");
		  }
		else
		  {
		    $db -> Execute("UPDATE `smelter` SET `level`=`level`+1 WHERE `owner`=".$player -> id);
		  }
		$db -> Execute("UPDATE `players` SET `credits`=`credits`-".$arrCost[$intSmelterlevel]." WHERE `id`=".$player -> id);
		$objSmelter = $db -> Execute("SELECT `level` FROM `smelter` WHERE `owner`=".$player -> id);
		$intSmelterlevel ++;
		message("success", "Rozbudowałeś swoją hutę.");
	      }
	  }

	switch ($intSmelterlevel)
	  {
	  case 0:
	    $strLevel = "Poziom 1 - wytapianie miedzi (1000 sztuk złota)";
	    break;
	  case 1:
	    $strLevel = "Poziom 2 - wytapianie miedzi, brązu (5000 sztuk złota)";
	    break;
	  case 2:
	    $strLevel = "Poziom 3 - wytapianie miedzi, brązu, mosiądzu (20000 sztuk złota)";
	    break;
	  case 3:
	    $strLevel = "Poziom 4 - wytapianie miedzi, brązu, mosiadzu, żelaza (60000 sztuk złota)";
	    break;
	  case 4:
	    $strLevel = "Poziom 5 - wytapianie miedzi, brązu, mosiądzu, żelaza, stali (120000 sztuk złota)";
	    break;
	  case 5:
	    $strLevel = '';
	    unset($_GET['step']);
	    break;
	  default:
	    break;
	  }
	/**
	 * Upgrade menu
	 */
	$smarty -> assign(array("Upgradeinfo" => "Tutaj możesz ulepszyć swoją hutę. Każdy nowy poziom huty pozwala ci wytapiać kolejne surowce",
				"Levelinfo" => $strLevel,
				"Aupgrade" => "Ulepsz"));
      }

    /**
     * Melting items
     */
    elseif ($_GET['step'] == 'smelt2')
      {
	if (!$objSmelter -> fields['level'])
	  {
	    error("Nie możesz przetapiać przedmiotów, ponieważ nie masz rozbudowanej huty.");
	  }
	$arrMaterials = array('miedzi', 'brązu', 'mosiądzu', 'żelaza', 'stali');
	//Melt item
	if (isset($_GET['smelt']))
	  {
	    if (!isset($_POST['item']))
	      {
		error('Podaj jaki przedmiot chcesz przetopić.');
	      }
	    checkvalue($_POST['item']);
	    $blnValid = TRUE;
	    $objItem = $db->Execute("SELECT `id`, `name`, `wt`, `maxwt`, `amount`, `minlev` FROM `equipment` WHERE `id`=".$_POST['item']." AND `owner`=".$player->id." AND `status`='U' AND `type` IN ('W', 'A', 'H', 'L', 'S', 'E')");
	    if (!$objItem->fields['id'])
	      {
		message('error', 'Nie ma takiego przedmiotu.');
		$blnValid = FALSE;
	      }
	    if ($objItem->fields['wt'] < $objItem->fields['maxwt'])
	      {
		message('error', 'Nie możesz przetapiać uszkodzonych przemiotów.');
		$blnValid = FALSE;
	      }
	    if (!isset($_POST['all']))
	      {
		checkvalue($_POST['amount']);
		if ($_POST['amount'] > $objItem->fields['amount'])
		  {
		    message('error', 'Nie możesz przetopić więcej przedmiotów niż posiadasz.');
		    $blnValid = FALSE;
		  }
	      }
	    else
	      {
		$_POST['amount'] = $objItem->fields['amount'];
	      }
	    $arrTmp = explode(' ', $objItem->fields['name']);
	    $intKey = array_search(end($arrTmp), $arrMaterials);
	    if (($intKey + 1) > $objSmelter->fields['level'])
	      {
		message('error', 'Musisz rozbudować hutę aby móc przetapiać ten przedmiot.');
		$blnValid = FALSE;
	      }
	    if ($blnValid)
	      {
		$objPlans = $db->Execute("SELECT `name`, `amount` FROM `smith` WHERE `owner`=0") or die($db->ErrorMsg());
		$intMaxamount = 0;
		while (!$objPlans->EOF)
		  {
		    if (strpos($objItem->fields['name'], $objPlans->fields['name']) !== FALSE)
		      {
			$intMaxamount = ceil($objPlans->fields['amount'] * 0.75);
			break;
		      }
		    $objPlans->MoveNext();
		  }
		$objPlans->Close();
		if ($intMaxamount == 0)
		  {
		    $objPlans = $db->Execute("SELECT `name`, `amount`, `level` FROM `plans`");
		    while (!$objPlans->EOF)
		      {
			if (strpos($objItem->fields['name'], $objPlans->fields['name']) !== FALSE && $objPlans->fields['level'] == $objItem->fields['minlev'])
			  {
			    $intMaxamount = ceil($objPlans->fields['amount'] * 0.75);
			    break;
			  }
			$objPlans->MoveNext();
		      }
		    $objPlans->Close();
		  }
		if ($intMaxamount == 0)
		  {
		    message('error', 'Nie można przetapiać tego przedmiotu.');
		    $blnValid = FALSE;
		  }
		$objCoal = $db->Execute("SELECT `coal` FROM `minerals` WHERE `owner`=".$player->id);
		$intCoalneed = $intMaxamount * $_POST['amount'];
		if ($objCoal->fields['coal'] < $intCoalneed)
		  {
		    message('error', 'Potrzebujesz '.$intCoalneed.' sztuk węgla aby przetopić ten przedmiot.');
		    $blnValid = FALSE;
		  }
		$objCoal->Close();
		$arrBillets = array(1, 2, 3, 5, 8);
		$intEnergy = (($arrBillets[$intKey] / 10) * $intMaxamount) * $_POST['amount'];
		$intEnergy = round($intEnergy, 2);
		if ($player->energy < $intEnergy)
		  {
		    message('error', 'Potrzebujesz '.$intEnergy.' energii aby przetopić wszystkie wybrane przedmioty.');
		    $blnValid = FALSE;
		  }
		//Start melting
		if ($blnValid)
		  {
		    $player->curskills(array('smelting'), TRUE, TRUE);
		    $player->skills['smelting'][1] += $player->checkbonus('smelting');
		    $intDiff = 100 * $arrBillets[$intKey];
		    $intAmount = 0;
		    for ($i = 0; $i < $_POST['amount']; $i++)
		      {
			$intRoll = rand(1, ($player->skills['smelting'][1] + $player->stats['condition'][2]) * 100);
			$intRoll2 = rand(1, $intDiff);
			if ($intRoll > $intRoll2)
			  {
			    $intAmount += $intMaxamount;
			  }
			else
			  {
			    $fltRand = rand(0, 50) / 100;
			    $intAmount += floor($intMaxamount * $fltRand);
			  }
		      }
		    $intExp = $intAmount * ($arrBillets[$intKey] / 2);
		    if ($player->clas == 'Rzemieślnik')
		      {
			$intExp = $intExp * 2;
		      }
		    $player->clearbless(array('condition'));
		    $player->checkexp(array('condition' => ($intExp / 2)), $player->id, "stats");
		    $player->checkexp(array('smelting' => ($intExp / 2)), $player->id, "skills");
		    $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
		    $player->energy -= $intEnergy;
		    if ($_POST['amount'] == $objItem->fields['amount'])
		      {
			$db->Execute("DELETE FROM `equipment` WHERE `id`=".$objItem->fields['id']);
		      }
		    else
		      {
			$db->Execute("UPDATE `equipment` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$objItem->fields['id']);
		      }
		    $arrAction = array('copper', 'bronze', 'brass', 'iron', 'steel');
		    $db->Execute("UPDATE `minerals` SET `coal`=`coal`-".$intCoalneed.", `".$arrAction[$intKey]."`=`".$arrAction[$intKey]."`+".$intAmount." WHERE `owner`=".$player->id);
		    message('success', 'Przetopiłeś '.$_POST['amount'].' sztuk '.$objItem->fields['name'].' i uzyskałeś '.$intAmount.' sztabek '.$arrMaterials[$intKey].' oraz '.$intExp.' punktów doświadczenia. Zużyłeś na to '.$intEnergy.' energii oraz '.$intCoalneed.' sztuk węgla.');
		  }
	      }
	  }
	//Main menu
	$objItems = $db->Execute("SELECT `id`, `name`, `power`, `zr`, `szyb`, `amount` FROM `equipment` WHERE `owner`=".$player->id." AND `status`='U' AND `type` IN ('W', 'A', 'H', 'L', 'S', 'E') AND `wt`=`maxwt`");
	$arrItems = array();
	while (!$objItems->EOF)
	  {
	    $arrTmp = explode(' ', $objItems->fields['name']);
	    if ((array_search(end($arrTmp), $arrMaterials) + 1) > $objSmelter->fields['level'])
	      {
		$objItems->MoveNext();
		continue;
	      }
	    $arrItems[$objItems->fields['id']] = $objItems->fields['name'].' (+'.$objItems->fields['power'].')';
	    if ($objItems->fields['zr'] != 0)
	      {
		$arrItems[$objItems->fields['id']] .= ' ('.($objItems->fields['zr'] * -1).' zr)';
	      }
	    if ($objItems->fields['szyb'] != 0)
	      {
		$arrItems[$objItems->fields['id']] .= ' ('.$objItems->fields['szyb'].' szyb)';
	      }
	    $arrItems[$objItems->fields['id']] .= ' (ilość: '.$objItems->fields['amount'].')';
	    $objItems->MoveNext();
	  }
	$objItems->Close();
	$smarty->assign(array('Smeltinfo' => 'Tutaj możesz przetapiać posiadane przedmioty na sztabki. Aby móc przetapiać przedmioty wykonane z lepszych materiałów, musisz mieć odpowiednio rozbudowaną hutę. Maksymalnie możesz odzyskać 3/4 sztabek, użytych do wykonania przedmiotu. Koszt energii przetopienia przedmiotu zależy od jego poziomu oraz od materiałów z których został wykonany. Ilość odzyskanych sztabek zależy od poziomu twojej umiejętności Hutnictwo. Do każdego przetapiania potrzebujesz również nieco węgla.',
			      'Asmelt3' => 'Przetop',
			      'Tsmelt' => 'na sztabki.',
			      'Tamount' => 'sztuk',
			      'Tall' => 'wszystkie posiadane',
			      'Ioptions' => $arrItems));
      }

    /**
     * Smelt ore
     */
    elseif ($_GET['step'] == 'smelt')
      {
	if (!$objSmelter -> fields['level'])
	  {
	    error("Nie możesz wytapiać rud surowców ponieważ nie masz odpowiednio rozbudowanej huty!");
	  }
	$arrAction = array('copper', 'bronze', 'brass', 'iron', 'steel');
	$arrBillets = array(1, 2, 3, 5, 8);
	$arrSmelt = array("Wytapiaj miedź (2 rudy miedzi + 1 bryła węgla = 1 sztabka miedzi)", "Wytapiaj brąz (1 ruda miedzi + 1 ruda cyny + 2 bryły węgla = 1 sztabka brązu)", "Wytapiaj mosiądz (2 rudy miedzi + 1 ruda cynku + 2 bryły węgla = 1 sztabka mosiądzu)", "Wytapiaj żelazo (2 rudy żelaza + 3 bryły węgla = 1 sztabka żelaza)", "Wytapiaj stal (3 rudy żelaza + 7 brył węgla = 1 sztabka stali)");
	$arrSmelt = array_slice($arrSmelt, 0, $objSmelter -> fields['level']);
	$arrAction = array_slice($arrAction, 0, $objSmelter -> fields['level']);
	if (isset($_GET['smelt']))
	  {
	    if (!in_array($_GET['smelt'], $arrAction))
	      {
		error("Zapomnij o tym.");
	      }
	    $blnValid = TRUE;
	    if ($player -> hp < 1)
	      {
		message('error', "Nie możesz wytapiać sztabek ponieważ jesteś martwy");
		$blnValid = FALSE;
	      }
	    else
	      {
		$intKey = array_search($_GET['smelt'], $arrAction);
		$arrSql = array('copperore, coal', 'copperore, tinore, coal', 'copperore, zincore, coal', 'ironore, coal', 'ironore, coal');
		$strSql = $arrSql[$intKey];
		$arrOres = explode(", ", $strSql);
		$objTestore = $db -> Execute("SELECT ".$strSql." FROM minerals WHERE owner=".$player -> id) or die($db -> ErrorMsg());
		foreach ($arrOres as $strOres)
		  {
		    if (!$objTestore -> fields[$strOres])
		      {
			message('error', "Nie masz minerałów potrzebnych do wytapiania tego surowca!");
			$blnValid = FALSE;
			break;
		      }
		  }
		if (!$player -> energy)
		  {
		    message('error', "Nie masz energii aby wytapiać surowce");
		    $blnValid = FALSE;
		  }
	      }
	    if ($blnValid)
	      {
		$arrSmeltamount = array(array(2, 1),
					array(1, 1, 2),
					array(2, 1, 2),
					array(2, 3),
					array(3, 7));
		$arrOresamount = $arrSmeltamount[$intKey];
		$arrOresname = array('copperore' => "</b> rud miedzi, <b>",
				     'tinore' => "</b> rud cyny, <b>",
				     'zincore' => "</b> rud cynku, <b>",
				     'ironore' => "</b> rud żelaza, <b>",
				     'coal' => "</b> brył węgla, <b>");
		$arrAmount2 = array();
		$arrOresname2 = array();
		$arrSmeltmineral = array("sztabek miedzi.", "sztabek brązu.", "sztabek mosiądzu.", "sztabek żelaza.", "sztabek stali.");
		/**
		 * Start smelting
		 */
		if (isset($_POST['amount']))
		  {
		    $blnValid = TRUE;
		    checkvalue($_POST['amount']);
		    $intEnergy = ($arrBillets[$intKey] / 10) * $_POST['amount'];
		    $intEnergy = round($intEnergy, 2);
		    if ($intEnergy > $player -> energy)
		      {
			message('error', "Nie masz tyle energii!");
			$blnValid = FALSE;
		      }
		    $i = 0;
		    $arrAmount = array();
		    $player->curskills(array('smelting'), TRUE, TRUE);
		    $player->skills['smelting'][1] += $player->checkbonus('smelting');
		    foreach ($arrOres as $strOres)
		      {
			$arrAmount[$i] = $_POST['amount'] * $arrOresamount[$i];
			if ($objTestore -> fields[$strOres] < $arrAmount[$i])
			  {
			    message('error', "Nie masz tylu rud aby wytopić taką ilość sztabek");
			    $blnValid = FALSE;
			  }
			$i++;
		      }
		    if ($blnValid)
		      {
			$intAmount = 0;
			$intDiff = 100 * $arrBillets[$intKey]; 
			for ($i = 0; $i < $_POST['amount']; $i++)
			  {
			    $intRoll = rand(1, ($player->skills['smelting'][1] + $player->stats['condition'][2]) * 100);
			    $intRoll2 = rand(1, $intDiff);
			    if ($intRoll > $intRoll2)
			      {
				$intAmount++;
				if ($intRoll * 2 > $intDiff)
				  {
				    $intAmount++;
				  }
			      }
			  }
			$intMinerals = count($arrAmount);
			$strSql = '';
			for ($i = 0; $i < $intMinerals; $i++)
			  {
			    $strSql = $strSql.", ".$arrOres[$i]."=".$arrOres[$i]."-".$arrAmount[$i];
			    $objTestore->fields[$arrOres[$i]] -= $arrAmount[$i];
			  }
			$intExp = $intAmount * ($arrBillets[$intKey] / 2);
			if ($player->clas == 'Rzemieślnik')
			  {
			    $intExp = $intExp * 2;
			  }
			$db -> Execute("UPDATE minerals SET ".$_GET['smelt']."=".$_GET['smelt']."+".$intAmount.$strSql." WHERE owner=".$player -> id);
			$player->clearbless(array('condition'));
			$player->checkexp(array('condition' => ($intExp / 2)), $player->id, "stats");
			$player->checkexp(array('smelting' => ($intExp / 2)), $player->id, "skills");
			$db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
			$player->energy -= $intEnergy;
			message("success", "Uzyskałeś ".$intAmount." ".$arrSmeltmineral[$intKey]." Zdobywasz ".$intExp." punktów doświadczenia.");
		      }
		  }
		$strOreshave = "Posiadasz <b>";
		foreach ($arrOres as $strKey)
		  {
		    $strOreshave = $strOreshave.$objTestore -> fields[$strKey].$arrOresname[$strKey];
		  }
		$i = 0;
		$intMaxamount = 0;
		$intEnergy = $player->energy / ($arrBillets[$intKey] / 10);
		foreach ($arrOresamount as $intAmount)
		  {
		    $strKey = $arrOres[$i];
		    $intTestamount = floor($objTestore -> fields[$strKey] / $intAmount);
		    if ($intTestamount < $intMaxamount || !$i)
		      {
			$intMaxamount = $intTestamount;
		      }
		    $i ++;
		  }
		if ($intEnergy < $intMaxamount)
		  {
		    $intMaxamount = $intEnergy;
		  }
		$smarty -> assign(array("Asmelt2" => "Wytop",
					"Smeltm" => $arrSmeltmineral[$intKey],
					"Youhave" => $strOreshave,
					"Maxamount" => floor($intMaxamount)));
	      }
	    else
	      {
		$_GET['smelt'] = '';
	      }
	  }
	else
	  {
	    $_GET['smelt'] = '';
	  }
	/**
	 * Smelt menu
	 */
	$smarty -> assign(array("Smeltinfo" => "Tutaj możesz wytapiać rudy minerałów w celu zdobycia surowców potrzebnych do wytwarzania przedmiotów. Koszt wytopu zależy od rodzaju metalu, który chcesz wytworzyć.",
				"Asmelt" => $arrSmelt,
				"Smeltaction" => $arrAction,
				"Smelt" => $_GET['smelt']));
      }
  }

/**
 * Main menu
 */
if (!isset($_GET['step']))
  {
    if (!$objSmelter -> fields['level'])
      {
        $smarty -> assign(array("Nosmelter" => "Najpierw musisz nieco ",
                                "Nosmelter2" => " hutę nim zaczniesz wytapiać w niej surowce.",
                                "Aupgrade" => "rozbudować",
                                "Smelterlevel" => 0));
      }
    else
      {
        $smarty -> assign(array("Smelterinfo" => "Witaj w hucie. Tutaj możesz wytapiać różne minerały oraz przetapiać przedmioty.",
                                "Smelterlevel" => $objSmelter -> fields['level'],
                                "Aupgrade" => "Ulepsz hutę",
                                "Asmelt" => "Wytapiaj surowce",
				"Asmelt2" => 'Przetop przedmiot'));
      }
    $_GET['step'] = '';
  }

/**
 * Free memory
 */
$objSmelter -> Close();

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Step" => $_GET['step'],
			"Aback" => "Wróć"));
$smarty -> display ('smelter.tpl');

require_once("includes/foot.php");
?>
