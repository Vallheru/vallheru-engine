<?php
/**
 *   File functions:
 *   Clans minerals
 *
 *   @name                 : tribeminerals.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 19.04.2012
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

$title = "Klany";
require_once("includes/head.php");

if ($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Zapomnij o tym.");
}

/**
* Check if player is in clan
*/
if (!$player->tribe) 
{
    error ("Nie należysz do jakiegokolwiek klanu.");
}

$mytribe = $db -> Execute("SELECT `id`, `owner`, `level` FROM `tribes` WHERE `id`=".$player->tribe);
if ($mytribe->fields['level'] < 3)
  {
    error('Najpierw musisz rozbudować klan, aby mieć dostęp do tego miejsca. (<a href="tribes.php?view=my">Wróć</a>)');
  }
$perm = $db -> Execute("SELECT * FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']." AND player=".$player->id);
require_once('includes/tribemenu.php');

$arrSqlname2 = array('copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
$arrName = array("rudy miedzi", "rudy cynku", "rudy cyny", "rudy żelaza", "sztabek miedzi", "sztabek brązu", "sztabek mosiądzu", "sztabek żelaza", "sztabek stali", "brył węgla", "brył adamantium", "kawałków meteorytu", "kryształów", "drewna sosnowego", "drewna z leszczyny", "drewna cisowego", "drewna z wiązu");
$objAmount = $db->Execute("SELECT * FROM `tribe_minerals` WHERE `id`=".$player->tribe);
if ($objAmount->fields['id'])
  {
    $arrAmount = array($objAmount -> fields['copperore'], 
		       $objAmount -> fields['zincore'], 
		       $objAmount -> fields['tinore'], 
		       $objAmount -> fields['ironore'], 
		       $objAmount -> fields['copper'], 
		       $objAmount -> fields['bronze'], 
		       $objAmount -> fields['brass'], 
		       $objAmount -> fields['iron'], 
		       $objAmount -> fields['steel'], 
		       $objAmount -> fields['coal'], 
		       $objAmount -> fields['adamantium'], 
		       $objAmount -> fields['meteor'], 
		       $objAmount -> fields['crystal'], 
		       $objAmount -> fields['pine'], 
		       $objAmount -> fields['hazel'], 
		       $objAmount -> fields['yew'], 
		       $objAmount -> fields['elm']);
    $arrReserved = array($objAmount -> fields['rcopperore'], 
			 $objAmount -> fields['rzincore'], 
			 $objAmount -> fields['rtinore'], 
			 $objAmount -> fields['rironore'], 
			 $objAmount -> fields['rcopper'], 
			 $objAmount -> fields['rbronze'], 
			 $objAmount -> fields['rbrass'], 
			 $objAmount -> fields['riron'], 
			 $objAmount -> fields['rsteel'], 
			 $objAmount -> fields['rcoal'], 
			 $objAmount -> fields['radamantium'], 
			 $objAmount -> fields['rmeteor'], 
			 $objAmount -> fields['rcrystal'], 
			 $objAmount -> fields['rpine'], 
			 $objAmount -> fields['rhazel'], 
			 $objAmount -> fields['ryew'], 
			 $objAmount -> fields['relm']);
  }
else
  {
    $arrAmount = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    $arrReserved = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
  }
$objAmount->Close();

/**
 * Reserve items from tribe
 */
if (isset($_GET['reserve']))
  {
    if (!in_array($_GET['reserve'], $arrSqlname2))
      {
	error("Zapomnij o tym.");
      }
    $intKey = array_search($_GET['reserve'], $arrSqlname2);
    $intAmount = $arrAmount[$intKey] - $arrReserved[$intKey];
    if (!isset ($_GET['step3'])) 
      {
        $smarty -> assign(array("Amount" => $intAmount, 
                                "Name" => $arrName[$intKey],
                                "Aask" => 'Poproś o',
                                "Tamount2" => 'sztuk'));
      }
    else
      {
	checkvalue($_POST['amount']);
	$_GET['step3'] = '';
	if ($intAmount < $_POST['amount']) 
	  {
	    message('error', "Klan nie ma dostępnej takiej ilości tego przemiotu!");
	    $smarty -> assign(array("Amount" => $intAmount, 
				    "Name" => $arrName[$intKey],
				    "Aask" => 'Poproś o',
				    "Tamount2" => 'sztuk'));
	  }
	else
	  {
	    $db->Execute("INSERT INTO `tribe_reserv` (`iid`, `pid`, `amount`, `tribe`, `type`) VALUES(".$intKey.", ".$player->id.", ".$_POST['amount'].", ".$player->tribe.", 'M')");
	    $db->Execute("UPDATE `tribe_minerals` SET `r".$arrSqlname2[$intKey]."`=`r".$arrSqlname2[$intKey]."`+".$_POST['amount']." WHERE `id`=".$player->tribe);
	    $strMessage = 'Gracz <b><a href="view.php?view='.$player->id.'">'.$player->user.'</a></b> ID: <b>'.$player->id.'</b> prosi o <b>'.$_POST['amount'].'</b> sztuk <b>'.$arrName[$intKey].'</b> z klanu.';
	    $strSQL = "INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$mytribe->fields['owner'].", '".$strMessage."','".$newdate."', 'C')";
	    $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player->tribe." AND `armory`=1");
	    while (!$objPerm->EOF)
	      {
		$strSQL .= ",(".$objPerm -> fields['player'].", '".$strMessage."','".$newdate."', 'C')";
		$objPerm -> MoveNext();
	      }
	    $objPerm -> Close();
	    $db -> Execute($strSQL);
	    if ($player->gender == 'M')
	      {
		$strSuffix = 'eś';
	      }
	    else
	      {
		$strSuffix = 'aś';
	      }
	    message('success', 'Poprosił'.$strSuffix.' o '.$_POST['amount'].' sztuk '.$arrName[$intKey].' ze skarbca klanu.');
	    $_GET['reserve'] = '';
	  }
      }
  }
else
  {
    $_GET['reserve'] = '';
  }

/**
 * Give minerals to players
 */
if (isset ($_GET['daj']) && $_GET['daj']) 
  {
    if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['bank'])
      {
	error("Zapomnij o tym.");
      }
    if (!in_array($_GET['daj'], $arrSqlname2))
      {
	error("Zapomnij o tym.");
      }
    $intKey = array_search($_GET['daj'], $arrSqlname2);
    $objMembers = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$player->tribe);
    $arrMembers = array();
    while (!$objMembers->EOF)
      {
	$arrMembers[$objMembers->fields['id']] = $objMembers->fields['user'].' ID:'.$objMembers->fields['id'];
	$objMembers->MoveNext();
      }
    $objMembers->Close();
    $smarty -> assign(array("Itemid" => $_GET['daj'],
			    "Giveplayer" => "Daj graczowi:",
			    "Members" => $arrMembers,
			    "Agive" => "Daj",
			    "Tamount3" => "z posiadanych",
			    "Mamount2" => "sztuk",
			    "Namemin" => $arrName[$intKey],
			    "Tamount2" => $arrAmount[$intKey]));
    if (isset ($_GET['step4']) && $_GET['step4'] == 'add') 
      {
	checkvalue($_POST['ilosc']);
	$daj = $_GET['daj'];
	checkvalue($_POST['did']);
	$dtrib = $db -> Execute("SELECT `tribe` FROM `players` WHERE `id`=".$_POST['did']);
	if ($dtrib -> fields['tribe'] != $mytribe -> fields['id']) 
	  {
	    error ("Ten gracz nie należy do twojego klanu.");
	  }
	$dtrib -> Close();
	$give = $_GET['daj'];
	if ($arrAmount[$intKey] < $_POST['ilosc']) 
	  {
	    error ("Klan nie ma takiej ilości ".$arrName[$intKey]."!");
	  }
	$kop = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$_POST['did']);
	if (!$kop -> fields['owner']) 
	  {
	    $db -> Execute("INSERT INTO `minerals` (`owner`, `".$daj."`) VALUES(".$_POST['did'].",".$_POST['ilosc'].")");
	  } 
	else 
	  {
	    $db -> Execute("UPDATE `minerals` SET `".$daj."`=`".$daj."`+".$_POST['ilosc']." WHERE `owner`=".$_POST['did']);
	  }
	$kop -> Close();
	if (!isset($intReserved))
	  {
	    $intReserved = 0;
	  }
	$db -> Execute("UPDATE `tribe_minerals` SET `".$daj."`=`".$daj."`-".$_POST['ilosc'].", `r".$_GET['daj']."`=`r".$_GET['daj']."`-".$intReserved." WHERE `id`=".$mytribe -> fields['id']);
        
	// Get name of the person which receives minerals.
	$objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['did'].';');
	$strReceiversName = $objGetName -> fields['user'];
	$objGetName -> Close();
	unset( $objGetName );
        
	message("success",  'Klan przekazał graczowi <b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>, ID '.$_POST['did']." ".$_POST['ilosc']." ".$arrName[$intKey]);
	$_GET['step4'] = '';
	$_GET['daj'] = '';
	$arrAmount[$intKey] -= $_POST['ilosc'];
	/**
	 * Send information about give minerals to player
	 */
	$strDate = $db -> DBDate($newdate);
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '".'Klan przekazał graczowi <b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>, ID <b>'.$_POST['did']."</b> ".$_POST['ilosc']." ".$arrName[$intKey].".', ".$strDate.", 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$mytribe -> fields['owner'].", '".'Klan przekazał graczowi <b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>, ID <b>'.$_POST['did']."</b> ".$_POST['ilosc']." ".$arrName[$intKey].".', ".$strDate.")");
	$objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `bank`=1");
	while (!$objPerm -> EOF)
	  {
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".'Klan przekazał graczowi <b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>, ID <b>'.$_POST['did']."</b> ".$_POST['ilosc']." ".$arrName[$intKey].".', ".$strDate.", 'C')");
	    $objPerm -> MoveNext();
	  }
	$objPerm -> Close();
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['did'].", 'Dostałeś od klanu ".$_POST['ilosc']." ".$arrName[$intKey].".', ".$strDate.", 'C')");
      }
  }

/**
 * Give minerals to tribe
 */
if (isset ($_GET['step2']) && $_GET['step2'] == 'daj') 
  {
    $objMinerals = $db->Execute("SELECT * FROM `minerals` WHERE `owner`=".$player->id);
    $arrOptions = array();
    foreach ($arrSqlname2 as $key => $strSqlname)
      {
	if ($objMinerals->fields[$strSqlname])
	  {
	    $arrOptions[$strSqlname] = $arrName[$key].' ('.$objMinerals->fields[$strSqlname].' sztuk)';
	  }
      }
    $objMinerals->Close();
    $smarty -> assign(array("Addmin" => "Dodaj minerały do skarbca",
			    "Aadd" => "Dodaj",
			    "Mineral" => "Minerał",
			    "Mamount" => "Ilość minerału",
			    "Minname" => $arrOptions));
    if (isset ($_GET['step3']) && $_GET['step3'] == 'add') 
      {
	if (!in_array($_POST['mineral'], $arrSqlname2))
	  {
	    error("Zapomnij o tym.");
	  }
	$gr = $db -> Execute("SELECT ".$_POST['mineral']." FROM `minerals` WHERE `owner`=".$player -> id);
	$intKey = array_search($_POST['mineral'], $arrSqlname2);
	if ($_POST['ilosc'] > $gr -> fields[$arrSqlname2[$intKey]])
	  {
	    error("Nie masz takiej ilości ".$arrName[$intKey].".");
	  }  
	checkvalue($_POST['ilosc']);
	if ($_POST['ilosc'] <= 0) 
	  {
	    error ("Zapomnij o tym.");
	  }
	$objTest = $db->Execute("SELECT `id` FROM `tribe_minerals` WHERE `id`=".$player->tribe);
	if (!$objTest->fields['id'])
	  {
	    $db->Execute("INSERT INTO `tribe_minerals` (`id`, `".$arrSqlname2[$intKey]."`) VALUES(".$player->tribe.", ".$_POST['ilosc'].")");
	  }
	else
	  {
	    $db -> Execute("UPDATE `tribe_minerals` SET `".$arrSqlname2[$intKey]."`=`".$arrSqlname2[$intKey]."`+".$_POST['ilosc']." WHERE `id`=".$mytribe -> fields['id']);
	  }
	$db -> Execute("UPDATE `minerals` SET `".$arrSqlname2[$intKey]."`=`".$arrSqlname2[$intKey]."`-".$_POST['ilosc']." WHERE `owner`=".$player -> id);
	message("success", "Dodałeś <b>".$_POST['ilosc']." sztuk(i) ".$arrName[$intKey]."</b> do skarbca klanu.");
	$arrAmount[$intKey] += $_POST['ilosc'];
	$_GET['step3'] = '';
	$_GET['step2'] = '';
	/**
	 * Send information about give minerals to tribe
	 */
	$strDate = $db -> DBDate($newdate);
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '"."Gracz <b><a href=\"view.php?view=".$player -> id."\">".$player -> user."</a></b> ID: ".'<b>'.$player -> id.'</b> dodał do skarbca klanu '.$_POST['ilosc']." sztuk(i) ".$arrName[$intKey].".', ".$strDate.", 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$mytribe -> fields['owner'].", '"."Gracz  <b><a href=\"view.php?view=".$player -> id."\">".$player -> user."</a></b> ID: ".'<b>'.$player -> id.'</b> dodał do skarbca klanu '.$_POST['ilosc']." sztuk(i) ".$arrName[$intKey].".', ".$strDate.")");
	$objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `bank`=1");
	while (!$objPerm -> EOF)
	  {
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '"."Gracz <b><a href=\"view.php?view=".$player -> id."\">".$player -> user."</a></b> ID: ".'<b>'.$player -> id.'</b> dodał do skarbca klanu '.$_POST['ilosc']." sztuk(i) ".$arrName[$intKey].".', ".$strDate.", 'C')");
	    $objPerm -> MoveNext();
	  }
	$objPerm -> Close();
      }
  }

if (!isset($intReserved))
  {
    $intReserved = 0;
  }

if (!isset($_GET['step2']))
  {
    $_GET['step2'] = '';
  }
if (!isset($_GET['step3']))
  {
    $_GET['step3'] = '';
  }
if (!isset($_GET['step4']))
  {
    $_GET['step4'] = '';
  }
if (!isset($_GET['daj']))
  {
    $_GET['daj'] = '';
  }

if ($_GET['step2'] == ''  && $_GET['step3'] == '' && $_GET['daj'] == '' && $_GET['step4'] == '' && $_GET['reserve'] == '') 
  {
    $arrTable = array('<tr><th>Nazwa</th><th align="center">Ilość/Dostępne</th></tr>');
    $i = 0;
    foreach ($arrName as $strName)
      {
	if ($player -> id == $mytribe -> fields['owner'] || $perm -> fields['bank']) 
	  {
	    $arrTable[] = "<tr><td><a href=\"tribeminerals.php?daj=".$arrSqlname2[$i]."\">".ucfirst($strName)."</a></td><td width=\"75%\" align=\"center\"><b>".$arrAmount[$i].'/'.($arrAmount[$i] - $arrReserved[$i])."</b></td></tr>";
	  }
	else
	  {
	    $arrTable[] = "<tr><td><a href=\"tribeminerals.php?reserve=".$arrSqlname2[$i]."\">".ucfirst($strName)."</a></td><td width=\"75%\" align=\"center\"><b>".$arrAmount[$i].'/'.($arrAmount[$i] - $arrReserved[$i])."</b></td></tr>";
	  }
	$i++;
      }
    $smarty -> assign(array("Mininfo" => "Witaj w skarbcu klanu. Tutaj są składowane minerały należące do klanu. Każdy członek klanu może ofiarować klanowi jakiś minerał, ale tylko przywódca lub osoba upoważniona przez niego może darować dany minerał członkom swojego klanu. Aby dać jakiś minerał członkom klanu, kliknij na nazwę owego minerału.",
			    "Whatyou" => "Co chcesz zrobić?",
			    "Agiveto" => "Dać minerały do klanu",
			    "Ttable" => $arrTable));
  }

if ($intReserved == 0)
  {
    $smarty->assign(array("Step2" => $_GET['step2'],
			  "Step3" => $_GET['step3'],
			  "Step4" => $_GET['step4'],
			  "Give" => $_GET['daj'],
			  "Reserve" => $_GET['reserve']));
    
    $smarty -> display('tribeminerals.tpl');

    require_once("includes/foot.php");
  }

?>