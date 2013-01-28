<?php
/**
 *   File functions:
 *   Tribe herbs
 *
 *   @name                 : tribeherbs.php                            
 *   @copyright            : (C) 2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 28.01.2013
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

$arrName = array("Illani", "Illanias", "Nutari", "Dynallca", "Nasiona Illani", "Nasiona Illanias", "Nasiona Nutari", "Nasiona Dynallca");
$arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
$objHerbs = $db->Execute("SELECT * FROM `tribe_herbs` WHERE `id`=".$player->tribe);
if ($objHerbs->fields['id'])
  {
    $arrAmount = array($objHerbs->fields['illani'],
		       $objHerbs->fields['illanias'],
		       $objHerbs->fields['nutari'],
		       $objHerbs->fields['dynallca'],
		       $objHerbs->fields['ilani_seeds'],
		       $objHerbs->fields['illanias_seeds'],
		       $objHerbs->fields['nutari_seeds'],
		       $objHerbs->fields['dynallca_seeds']);
    $arrReserved = array($objHerbs->fields['rillani'],
			 $objHerbs->fields['rillanias'],
			 $objHerbs->fields['rnutari'],
			 $objHerbs->fields['rdynallca'],
			 $objHerbs->fields['rilani_seeds'],
			 $objHerbs->fields['rillanias_seeds'],
			 $objHerbs->fields['rnutari_seeds'],
			 $objHerbs->fields['rdynallca_seeds']);
  }
else
  {
    $arrAmount = array(0, 0, 0, 0, 0, 0, 0, 0);
    $arrReserved = array(0, 0, 0, 0, 0, 0, 0, 0);
  }
$objHerbs->Close();

/**
 * Reserve items from tribe
 */
if (isset($_GET['reserve']) && $_GET['reserve'] != '')
  {
    if (!in_array($_GET['reserve'], $arrSqlname))
      {
	error("Zapomnij o tym.");
      }
    $intKey = array_search($_GET['reserve'], $arrSqlname);
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
	    $db->Execute("INSERT INTO `tribe_reserv` (`iid`, `pid`, `amount`, `tribe`, `type`) VALUES(".$intKey.", ".$player->id.", ".$_POST['amount'].", ".$player->tribe.", 'H')");
	    $db->Execute("UPDATE `tribe_herbs` SET `r".$arrSqlname[$intKey]."`=`r".$arrSqlname[$intKey]."`+".$_POST['amount']." WHERE `id`=".$player->tribe);
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
	    message('success', 'Poprosił'.$strSuffix.' o '.$_POST['amount'].' sztuk '.$arrName[$intKey].' z zielnika klanu.');
	    $_GET['reserve'] = '';
	  }
      }
  }
else
  {
    $_GET['reserve'] = '';
  }

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
    $objMembers = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$player->tribe);
    $arrMembers = array();
    while (!$objMembers->EOF)
      {
	$arrMembers[$objMembers->fields['id']] = $objMembers->fields['user'].' ID:'.$objMembers->fields['id'];
	$objMembers->MoveNext();
      }
    $objMembers->Close();
    $smarty -> assign(array("Giveplayer" => "Daj graczowi:",
			    "Members" => $arrMembers,
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
	$dtrib->Close();
	if (($arrAmount[$intKey] - $arrReserved[$intKey]) < $_POST['ilosc'] && !isset($intReserved)) 
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
	if (!isset($intReserved))
	  {
	    $intReserved = 0;
	  }
	$db -> Execute("UPDATE `tribe_herbs` SET `".$_GET['daj']."`=`".$_GET['daj']."`-".$_POST['ilosc']." WHERE `id`=".$mytribe->fields['id']);
        
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
			    "Tall" => "wszystkie posiadane)",
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
	if (isset($_POST['all']))
	  {
	    $_POST['ilosc'] = $gr->fields[$min];
	  }
	if ($_POST['ilosc'] > $gr -> fields[$min])
	  {
	    error("Nie masz takiej ilości ".$nazwa.".");
	  }
	checkvalue($_POST['ilosc']);
	if ($_POST['ilosc'] <= 0) 
	  {
	    error ("Zapomnij o tym.");
	  }
	$objTest = $db->Execute("SELECT `id` FROM `tribe_herbs` WHERE `id`=".$player->tribe);
	if ($objTest->fields['id'])
	  {
	    $db -> Execute("UPDATE `tribe_herbs` SET `".$min."`=`".$min."`+".$_POST['ilosc']." WHERE `id`=".$mytribe -> fields['id']);
	  }
	else
	  {
	    $db->Execute("INSERT INTO `tribe_herbs` (`id`, `".$min."`) VALUES(".$player->tribe.", ".$_POST['ilosc'].")");
	  }
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

if ($_GET['step2'] == '' && $_GET['step3'] == '' && $_GET['daj'] == '' && $_GET['step4'] == '' && $_GET['reserve'] == '')     
  {
    $arrTable = array();
    $arrAmount2 = array();
    $i = 0;
    foreach ($arrName as $strName)
      {
	if ($player -> id == $mytribe -> fields['owner'] || $perm -> fields['herbs']) 
	  {
	    $arrTable[] = '<th width="100"><a href="tribeherbs.php?daj='.$arrSqlname[$i].'">'.$strName.'</a></th>';
	  }
	else 
	  {
	    $arrTable[] = '<th width="100"><a href="tribeherbs.php?reserve='.$arrSqlname[$i].'">'.$strName.'</a></th>';
	  }
	$arrAmount2[] = $arrAmount[$i].'/'.($arrAmount[$i] - $arrReserved[$i]);
	$i++;
      }
    $smarty -> assign(array("Tamount" => $arrAmount2,
			    "Ttable" => $arrTable,
			    "Herbsinfo" => "Witaj w zielniku klanu. Tutaj są składowane zioła należące do klanu. Każdy członek klanu może ofiarować klanowi jakieś zioła, ale tylko przywódca lub osoba upoważniona przez niego może darować dane zioła członkom swojego klanu. Aby dać jakieś zioła członkom klanu, kliknij na nazwę owego zioła.",
			    "Whatyou" => "Co chcesz zrobić?",
			    "Agiveto" => "Dać zioła do klanu"));
  }

if (!isset($intReserved))
  {
    $intReserved = 0;
  }


if ($intReserved == 0)
  {
    $smarty->assign(array("Step2" => $_GET['step2'],
			  "Step3" => $_GET['step3'],
			  "Step4" => $_GET['step4'],
			  "Give" => $_GET['daj'],
			  "Reserve" => $_GET['reserve']));
    
    $smarty -> display('tribeherbs.tpl');

    require_once("includes/foot.php");
  }
?>