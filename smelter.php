<?php
/**
 *   File functions:
 *   Smelter - smelt minerals
 *
 *   @name                 : smelter.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 07.09.2012
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

/**
* Get the localization for game
*/
require_once("languages/".$lang."/smelter.php");

if ($player -> location != 'Altara') 
{
    error (ERROR);
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
		error(NO_UPGRADE);
	      }
	    $arrCost = array(1000, 5000, 20000, 60000, 120000);
	    if ($player -> credits < $arrCost[$intSmelterlevel])
	      {
		message('error', NO_MONEY);
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
		message("success", YOU_UPGRADE);
	      }
	  }

	switch ($intSmelterlevel)
	  {
	  case 0:
	    $strLevel = LEVEL1;
	    break;
	  case 1:
	    $strLevel = LEVEL2;
	    break;
	  case 2:
	    $strLevel = LEVEL3;
	    break;
	  case 3:
	    $strLevel = LEVEL4;
	    break;
	  case 4:
	    $strLevel = LEVEL5;
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
	$smarty -> assign(array("Upgradeinfo" => UPGRADE_INFO,
				"Levelinfo" => $strLevel,
				"Aupgrade" => A_UPGRADE3));
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
		    $player->curskills(array('metallurgy'), TRUE, TRUE);
		    $intDiff = 100 * $arrBillets[$intKey];
		    $intAmount = 0;
		    for ($i = 0; $i < $_POST['amount']; $i++)
		      {
			$intRoll = rand(1, $player->metallurgy * 100);
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
		    $fltAbility = round(($intAmount / 100)  + ($_POST['amount'] * 0.01), 2);
		    $intExp = $intAmount * ($arrBillets[$intKey] / 2);
		    if ($player->clas == 'Rzemieślnik')
		      {
			$fltAbility = $fltAbility * 2;
			$intExp = $intExp * 2;
		      }
		    require_once('includes/checkexp.php');
		    checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'metallurgy', $fltAbility);
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
		    message('success', 'Przetopiłeś '.$_POST['amount'].' sztuk '.$objItem->fields['name'].' i uzyskałeś '.$intAmount.' sztabek '.$arrMaterials[$intKey].', '.$fltAbility.' do umiejętności Hutnictwo oraz '.$intExp.' punktów doświadczenia. Zużyłeś na to '.$intEnergy.' energii oraz '.$intCoalneed.' sztuk węgla.');
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
	    error(NO_SMELT);
	  }
	$arrAction = array('copper', 'bronze', 'brass', 'iron', 'steel');
	$arrBillets = array(1, 2, 3, 5, 8);
	$arrSmelt = array(SMELT1, SMELT2, SMELT3, SMELT4, SMELT5);
	$arrSmelt = array_slice($arrSmelt, 0, $objSmelter -> fields['level']);
	$arrAction = array_slice($arrAction, 0, $objSmelter -> fields['level']);
	if (isset($_GET['smelt']))
	  {
	    if (!in_array($_GET['smelt'], $arrAction))
	      {
		error(ERROR);
	      }
	    $blnValid = TRUE;
	    if ($player -> hp < 1)
	      {
		message('error', YOU_DEAD);
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
			message('error', NO_MINERALS);
			$blnValid = FALSE;
			break;
		      }
		  }
		if (!$player -> energy)
		  {
		    message('error', NO_ENERGY);
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
		$arrOresname = array('copperore' => MIN1,
				     'tinore' => MIN2,
				     'zincore' => MIN3,
				     'ironore' => MIN4,
				     'coal' => MIN5);
		$arrAmount2 = array();
		$arrOresname2 = array();
		$arrSmeltmineral = array(SMELTM1, SMELTM2, SMELTM3, SMELTM4, SMELTM5);
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
			message('error', NO_ENERGY2);
			$blnValid = FALSE;
		      }
		    $i = 0;
		    $arrAmount = array();
		    $player->curskills(array('metallurgy'), TRUE, TRUE);
		    foreach ($arrOres as $strOres)
		      {
			$arrAmount[$i] = $_POST['amount'] * $arrOresamount[$i];
			if ($objTestore -> fields[$strOres] < $arrAmount[$i])
			  {
			    message('error', NO_MINERALS2);
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
			    $intRoll = rand(1, $player->metallurgy * 100);
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
			$fltAbility = round(($intAmount / 100)  + (($_POST['amount'] - $intAmount) * 0.01), 2);
			$intExp = $intAmount * ($arrBillets[$intKey] / 2);
			if ($player->clas == 'Rzemieślnik')
			  {
			    $fltAbility = $fltAbility * 2;
			    $intExp = $intExp * 2;
			  }
			$db -> Execute("UPDATE minerals SET ".$_GET['smelt']."=".$_GET['smelt']."+".$intAmount.$strSql." WHERE owner=".$player -> id);
			require_once('includes/checkexp.php');
			checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'metallurgy', $fltAbility);
			$db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
			$player->energy -= $intEnergy;
			message("success", YOU_SMELT." ".$intAmount." ".$arrSmeltmineral[$intKey]." Zdobywasz ".$fltAbility." w umiejętności Hutnictwo oraz ".$intExp." PD.");
		      }
		  }
		$strOreshave = YOU_HAVE;
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
		$smarty -> assign(array("Asmelt2" => A_SMELT2,
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
	$smarty -> assign(array("Smeltinfo" => SMELT_INFO,
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
        $smarty -> assign(array("Nosmelter" => NO_SMELTER,
                                "Nosmelter2" => NO_SMELTER2,
                                "Aupgrade" => A_UPGRADE,
                                "Smelterlevel" => 0));
      }
    else
      {
        $smarty -> assign(array("Smelterinfo" => SMELTER_INFO,
                                "Smelterlevel" => $objSmelter -> fields['level'],
                                "Aupgrade" => A_UPGRADE2,
                                "Asmelt" => A_SMELT,
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
			"Aback" => A_BACK));
$smarty -> display ('smelter.tpl');

require_once("includes/foot.php");
?>
