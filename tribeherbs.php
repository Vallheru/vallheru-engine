<?php
/**
 *   File functions:
 *   Tribe herbs
 *
 *   @name                 : tribeherbs.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 26.01.2012
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
// $Id$

$title = "Klany";
require_once("includes/head.php");

if ($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Zapomnij o tym.");
}

$mytribe = $db -> Execute("SELECT * FROM tribes WHERE id=".$player -> tribe);
$perm = $db -> Execute("SELECT * FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']." AND player=".$player -> id);
$smarty -> assign (array("Amain" => "Główna",
			 "Adonate" => "Dotuj",
			 "Amembers" => "Członkowie",
			 "Aarmor" => "Zbrojownia",
			 "Apotions" => "Magazyn",
			 "Aminerals" => "Skarbiec",
			 "Aherbs" => "Zielnik",
			 "Aleft" => "Opuść klan",
			 "Aleader" => "Opcje przywódcy",
			 "Aforums" => "Forum klanu",
			 "Aastral" => "Astralny skarbiec"));

$arrName = array("Illani", "Illanias", "Nutari", "Dynallca", "Nasiona Illani", "Nasiona Illanias", "Nasiona Nutari", "Nasiona Dynallca");
$arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
$arrAmount = array($mytribe -> fields['illani'],
		   $mytribe -> fields['illanias'],
		   $mytribe -> fields['nutari'],
		   $mytribe -> fields['dynallca'],
		   $mytribe -> fields['ilani_seeds'],
		   $mytribe -> fields['illanias_seeds'],
		   $mytribe -> fields['nutari_seeds'],
		   $mytribe -> fields['dynallca_seeds']);
/**
 * Give herbs to player
 */
if (isset ($_GET['daj']) && $_GET['daj']) 
  {
    if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['herbs'])
      {
	error("Zapomnij o tym.");
      }
    if (!in_array($_GET['daj'], $arrSqlname))
      {
	error("Zapomnij o tym.");
      }
    $intKey = array_search($_GET['daj'], $arrSqlname);
    if ($arrAmount[$intKey] == 0)
      {
	error("Klan nie posiada takich ziół.");
      }
    $min1 = $arrName[$intKey];
    $smarty -> assign(array("Giveplayer" => "Daj graczowi ID:",
			    "Agive" => "Daj",
			    "Tamount" => "z posiadanych",
			    "Hamount2" => "sztuk",
			    "Tamount2" => $arrAmount[$intKey],
			    "Nameherb" => $min1, 
			    "Itemid" => $_GET['daj']));
    if (isset ($_GET['step4']) && $_GET['step4'] == 'add') 
      {
	checkvalue($_POST['ilosc']);
	checkvalue($_POST['did']);
	$dtrib = $db -> Execute("SELECT `tribe` FROM `players` WHERE `id`=".$_POST['did']);
	if ($dtrib -> fields['tribe'] != $mytribe -> fields['id']) 
	  {
	    error ('Ten gracz nie należy do klanu.');
	  }
	$give = $_GET['daj'];
	if ($mytribe -> fields[$give] < $_POST['ilosc']) 
	  {
	    error ("Klan nie ma takiej ilości ".$min1."!");
	  }
	$kop = $db -> Execute("SELECT * FROM `herbs` WHERE `gracz`=".$_POST['did']);
	if (!$kop -> fields['id']) 
	  {
	    $db -> Execute("INSERT INTO `herbs` (`gracz`, `".$_GET['daj']."`) VALUES(".$_POST['did'].",".$_POST['ilosc'].")");
	  } 
	else 
	  {
	    $db -> Execute("UPDATE `herbs` SET `".$_GET['daj']."`=`".$_GET['daj']."`+".$_POST['ilosc']." WHERE `gracz`=".$_POST['did']);
	  }
	$db -> Execute("UPDATE `tribes` SET `".$_GET['daj']."`=`".$_GET['daj']."`-".$_POST['ilosc']." WHERE `id`=".$mytribe -> fields['id']);
        
	// Get name of the person which receives herbs.
	$objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['did'].';');
	$strReceiversName = $objGetName -> fields['user'];
	$objGetName -> Close();
	unset( $objGetName );
        
	message("success",  'Przekazałeś graczowi <b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>, ID '.$_POST['did']." ".$_POST['ilosc']." ".$min1.'.');
	$_GET['step4'] = '';
	$_GET['daj'] = '';
	$arrAmount[$intKey] -= $_POST['ilosc'];
	
	/**
	 * Send information about give herbs to player
	 */
	$strDate = $db -> DBDate($newdate);
        
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '".'Klan przekazał graczowi <b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>, ID <b>'.$_POST['did']."</b> ".$_POST['ilosc']." ".$min1.".', ".$strDate.", 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$mytribe -> fields['owner'].", '".'Klan przekazał graczowi <b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>, ID <b>'.$_POST['did']."</b> ".$_POST['ilosc']." ".$min1.".', ".$strDate.")");
	$objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `herbs`=1");
	while (!$objPerm -> EOF)
	  {
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".'Klan przekazał graczowi <b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>, ID <b>'.$_POST['did']."</b> ".$_POST['ilosc']." ".$min1.".', ".$strDate.", 'C')");
	    $objPerm -> MoveNext();
	  }
	$objPerm -> Close();
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['did'].", '"."Dostałeś od klanu ".$_POST['ilosc']." ".$min1.".', ".$strDate.", 'C')");
      }
  }

/**
 * Give herbs to tribe
 */
if (isset ($_GET['step2']) && $_GET['step2'] == 'daj') 
  {
    $objHerbs = $db->Execute("SELECT * FROM `herbs` WHERE `gracz`=".$player->id);
    $arrOptions = array();
    foreach ($arrSqlname as $key => $strSqlname)
      {
	if ($objHerbs->fields[$strSqlname])
	  {
	    $arrOptions[$strSqlname] = $arrName[$key].' ('.$objHerbs->fields[$strSqlname].' sztuk)';
	  }
      }
    $objHerbs->Close();
    if (count($arrOptions) == 0)
      {
	error('Nie posiadasz jakichkolwiek ziół.');
      }
    $smarty -> assign(array("Addherb" => "Dodaj zioła do zielnika",
			    "Aadd" => "Dodaj",
			    "Herb" => "Zioło",
			    "Hamount" => "Sztuk(i)",
			    "Hoptions" => $arrOptions));
    if (isset ($_GET['step3']) && $_GET['step3'] == 'add') 
      {
	$gr = $db -> Execute("SELECT * FROM `herbs` WHERE `gracz`=".$player -> id);
	if (!in_array($_POST['mineral'], $arrSqlname))
	  {
	    error("Zapomnij o tym.");
	  }
	$intKey = array_search($_POST['mineral'], $arrSqlname);
	$nazwa = $arrName[$intKey];
	$min = $arrSqlname[$intKey];
	if ($_POST['ilosc'] > $gr -> fields[$min])
	  {
	    error("Nie masz takiej ilości ".$nazwa.".");
	  }
	checkvalue($_POST['ilosc']);
	if ($_POST['ilosc'] <= 0) 
	  {
	    error ("Zapomnij o tym.");
	  }
	$db -> Execute("UPDATE `tribes` SET `".$min."`=`".$min."`+".$_POST['ilosc']." WHERE `id`=".$mytribe -> fields['id']);
	$db -> Execute("UPDATE `herbs` SET `".$min."`=`".$min."`-".$_POST['ilosc']." WHERE `gracz`=".$player -> id);
	message("success", "Dodałeś <b>".$_POST['ilosc']." ".$nazwa."</b> do zielnika klanu.");
	$_GET['step2'] = '';
	$_GET['step3'] = '';
	$arrAmount[$intKey] += $_POST['ilosc'];
	/**
	 * Send information about give herbs to tribe
	 */
	$strDate = $db -> DBDate($newdate);
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '"."Gracz <b><a href=view.php?view=".$player -> id.">".$player -> user.'</a></b> ID: <b>'.$player -> id.'</b> dodał do zielnika klanu '.$_POST['ilosc']." ".$nazwa.".', ".$strDate.", 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$mytribe -> fields['owner'].", '"."Gracz <b><a href=view.php?view=".$player -> id.">".$player -> user.'</a></b> ID: <b>'.$player -> id.'</b> dodał do zielnika klanu '.$_POST['ilosc']." ".$nazwa.".', ".$strDate.")");
	$objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `herbs`=1");
	while (!$objPerm -> EOF)
	  {
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '"."Gracz <b><a href=view.php?view=".$player -> id.">".$player -> user.'</a></b> ID: <b>'.$player -> id.'</b> dodał do zielnika klanu '.$_POST['ilosc']." ".$nazwa.".', ".$strDate.", 'C')");
	    $objPerm -> MoveNext();
	  }
	$objPerm -> Close();
      }
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

if ($_GET['step2'] == '' && $_GET['step3'] == '' && $_GET['daj'] == '' && $_GET['step4'] == '')     
  {
    $arrTable = array();
    $i = 0;
    foreach ($arrName as $strName)
      {
	if ($player -> id == $mytribe -> fields['owner'] || $perm -> fields['herbs']) 
	  {
	    $arrTable[$i] = "<th width=\"100\"><a href=\"tribeherbs.php?daj=".$arrSqlname[$i]."\">".$strName."</a></th>";
	  }
	else 
	  {
	    $arrTable[$i] = "<th width=\"100\"><b><u>".$strName."</u></b></th>";
	  }
	$i++;
      }
    $smarty -> assign(array("Tamount" => $arrAmount,
			    "Ttable" => $arrTable,
			    "Herbsinfo" => "Witaj w zielniku klanu. Tutaj są składowane zioła należące do klanu. Każdy członek klanu może ofiarować klanowi jakieś zioła ale tylko przywódca lub osoba upoważniona przez niego może darować dane zioła członkom swojego klanu. Aby dać jakieś zioła członkom klanu, kliknij na nazwę owego zioła.",
			    "Whatyou" => "Co chcesz zrobić?",
			    "Agiveto" => "Dać zioła do klanu"));
  }

$smarty->assign(array("Step2" => $_GET['step2'],
		      "Step3" => $_GET['step3'],
		      "Step4" => $_GET['step4'],
		      "Give" => $_GET['daj']));

$smarty -> display('tribeherbs.tpl');

require_once("includes/foot.php");
?>