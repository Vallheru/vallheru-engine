<?php
/**
 *   File functions:
 *   Tribe armor - weapons and armors
 *
 *   @name                 : tribearmor.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 20.02.2013
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

$title = "Zbrojownia klanu";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/tribearmor.php");

/**
* Check if player is in clan
*/
if (!$player -> tribe) 
{
    error (NO_CLAN);
}

/**
* Check if player is in town
*/
if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Check permission who have ability to give items
*/
$perm = $db -> Execute("SELECT `armory` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `player`=".$player -> id);
$owner = $db -> Execute("SELECT `owner`, `level` FROM `tribes` WHERE `id`=".$player -> tribe);
if ($owner->fields['level'] == 1)
  {
    error('Najpierw musisz rozbudować klan, aby mieć dostęp do tego miejsca. (<a href="tribes.php?view=my">Wróć</a>)');
  }

$arrType = array('W', 'A', 'H', 'L', 'S', 'B', 'T', 'C', 'R', 'I', 'O', 'E', 'P');

/**
 * Reserve items from tribe
 */
if (isset($_GET['reserve']) && $_GET['reserve'] != '')
  {
    checkvalue($_GET['reserve']);
    if (!isset ($_GET['step3'])) 
      {
        $name = $db -> Execute("SELECT `id`, `name`, `type`, `reserved`, `amount`, `wt` FROM `tribe_zbroj` WHERE `id`=".$_GET['reserve']." AND `klan`=".$player->tribe);
	if (!$name->fields['id'])
	  {
	    error('Nie ma takiego przedmiotu.');
	  }
	if ($name->fields['type'] != 'R')
	  {
	    $intAmount = $name->fields['amount'] - $name->fields['reserved'];
	  }
	else
	  {
	    $intAmount = $name->fields['wt'] - $name->fields['reserved'];
	  }
        $smarty -> assign(array("Amount" => $intAmount, 
                                "Name" => $name -> fields['name'],
                                "Aask" => 'Poproś o',
                                "Tamount" => 'sztuk'));
        $name -> Close();
      }
    else
      {
	checkvalue($_POST['amount']);
        $zbroj = $db -> Execute("SELECT `id`, `name`, `type`, `amount`, `wt`, `reserved` FROM `tribe_zbroj` WHERE `id`=".$_GET['reserve']." AND `klan`=".$player->tribe);
	if (!$zbroj->fields['id']) 
	  {
	    error('Nie ma takiego przedmiotu.');  
	  }
	if ($zbroj->fields['type'] != 'R')
	  {
	    $intAmount = $zbroj->fields['amount'] - $zbroj->fields['reserved'];
	  }
	else
	  {
	    $intAmount = $zbroj->fields['wt'] - $zbroj->fields['reserved'];
	  }
	if ($intAmount < $_POST['amount']) 
	  {
	    message('error', "Klan nie ma dostępnej takiej ilości tego przemiotu!");
	    $smarty -> assign(array("Amount" => $intAmount, 
				    "Name" => $zbroj->fields['name'],
				    "Aask" => 'Poproś o',
				    "Tamount" => 'sztuk'));
	    $_GET['step3'] = '';
	  }
	else
	  {
	    $db->Execute("INSERT INTO `tribe_reserv` (`iid`, `pid`, `amount`, `tribe`, `type`) VALUES(".$_GET['reserve'].", ".$player->id.", ".$_POST['amount'].", ".$player->tribe.", 'A')");
	    $db->Execute("UPDATE `tribe_zbroj` SET `reserved`=`reserved`+".$_POST['amount']." WHERE `id`=".$_GET['reserve']);
	    $strMessage = 'Gracz <b><a href="view.php?view='.$player->id.'">'.$player->user.'</a></b> ID: <b>'.$player->id.'</b> prosi o <b>'.$_POST['amount'].'</b> sztuk <b>'.$zbroj->fields['name'].'</b> z klanu.';
	    $strSQL = "INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$owner -> fields['owner'].", '".$strMessage."','".$newdate."', 'C')";
	    $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player->tribe." AND `armory`=1");
	    while (!$objPerm->EOF)
	      {
		$strSQL .= ",(".$objPerm -> fields['player'].", '".$strMessage."','".$newdate."', 'C')";
		$objPerm -> MoveNext();
	      }
	    $objPerm -> Close();
	    $db -> Execute($strSQL);
	    $zbroj->Close();
	    if ($player->gender == 'M')
	      {
		$strSuffix = 'eś';
	      }
	    else
	      {
		$strSuffix = 'aś';
	      }
	    message('success', 'Poprosił'.$strSuffix.' o '.$_POST['amount'].' sztuk '.$zbroj->fields['name'].' ze zbrojowni klanu.');
	    $_GET['reserve'] = '';
	  }
      }
  }
else
  {
    $_GET['reserve'] = '';
  }

/**
* Main menu
*/
if (!isset($_GET['daj']) && !isset($_GET['step2']) && $_GET['reserve'] == '') 
  {
    $smarty -> assign(array("Armorinfo" => ARMOR_INFO,
                            "Armorlink" => 'Zobacz listę przedmiotów w zbrojowni klanu',
                            "Aadd" => A_ADD));
  }

/**
* List of items in tribe armor
*/
if (isset($_GET['step']) && $_GET['step'] == 'zobacz') 
{
    $arrList = array('id', 'name', 'power', 'wt', 'zr', 'szyb', 'minlev');
    if (isset($_GET['type']))
      {
	$_POST['type'] = $_GET['type'];
      }
    if (isset($_POST['type']) && !in_array($_POST['type'], $arrType)) 
    {
        error (ERROR);
    }
    if (isset($_GET['lista']) && !in_array($_GET['lista'], $arrList)) 
    {
        error (ERROR);
    }
    if (isset($_POST['min']))
      {
	checkvalue($_POST['min']);
      }
    if (isset($_POST['max']))
      {
	checkvalue($_POST['max']);
      }
    if (isset($_GET['min']))
      {
	checkvalue($_GET['min']);
	$_POST['min'] = $_GET['min'];
      }
    if (isset($_GET['max']))
      {
	checkvalue($_GET['max']);
	$_POST['max'] = $_GET['max'];
      }
    if (isset($_POST['type']))
      {
	$arrItem = array(T_WEAPONS, T_ARMORS, T_HELMETS, T_LEGS, T_SHIELDS, T_BOWS, T_STAFFS, T_CAPES, T_ARROWS, T_RINGS, "łupów", 'narzędzi', 'planów');
	$amount = $db -> Execute("SELECT SUM(`amount`) FROM `tribe_zbroj` WHERE `klan`=".$player -> tribe." AND `type`='".$_POST['type']."' AND `minlev`>=".$_POST['min']." AND `minlev`<=".$_POST['max']);
	$intKey = array_search($_POST['type'], $arrType);
	$item = $arrItem[$intKey];
      }
    else
      {
	$amount = $db -> Execute("SELECT SUM(`amount`) FROM `tribe_zbroj` WHERE `klan`=".$player -> tribe);
	$item = "przedmiotów";
      }
    if (!$amount -> fields['SUM(`amount`)']) 
      {
	message('error', NO_ITEMS.$item.IN_ARMOR);
	$_GET['step'] = '';
      }
    else
      {
	$smarty -> assign(array("Amount1" => $amount->fields['SUM(`amount`)'], 
				"Name1" => $item));
	if ($_GET['lista'] == 'zr')
	  {
	    $strOrder = ' ASC';
	  }
        else
	  {
	    $strOrder = ' DESC';
	  }
	if (isset($_POST['min']) && isset($_POST['max']))
	  {
	    checkvalue($_POST['min']);
	    checkvalue($_POST['max']);
	    if ($_POST['max'] < $_POST['min'])
	      {
		error(ERROR);
	      }
	    $objAmount = $db->Execute("SELECT count(`id`) FROM `tribe_zbroj` WHERE `klan`=".$player -> tribe." AND `type`='".$_POST['type']."' AND `minlev`>=".$_POST['min']." AND `minlev`<=".$_POST['max']);
	    $pages = ceil($objAmount->fields['count(`id`)'] / 30);
	    $objAmount->Close();
	    if (isset($_GET['page']))
	      {
		checkvalue($_GET['page']);
		$page = $_GET['page'];
	      }
	    else
	      {
		$page = 1;
	      }
	    if ($page > $pages)
	      {
		$page = $pages;
	      }
	    $arritem = $db->GetAll("SELECT * FROM `tribe_zbroj` WHERE `klan`=".$player -> tribe." AND `type`='".$_POST['type']."' AND `minlev`>=".$_POST['min']." AND `minlev`<=".$_POST['max']." ORDER BY ".$_GET['lista'].$strOrder." LIMIT ".(30 * ($page - 1)).", 30");
	  }
        else
	  {
	    $objAmount = $db->Execute("SELECT count(`id`) FROM tribe_zbroj WHERE klan=".$player -> tribe);
	    $pages = ceil($objAmount->fields['count(`id`)'] / 30);
	    $objAmount->Close();
	    if (isset($_GET['page']))
	      {
		checkvalue($_GET['page']);
		$page = $_GET['page'];
	      }
	    else
	      {
		$page = 1;
	      }
	    if ($page > $pages)
	      {
		$page = $pages;
	      }
	    $arritem = $db->GetAll("SELECT * FROM tribe_zbroj WHERE klan=".$player -> tribe." ORDER BY ".$_GET['lista'].$strOrder." LIMIT ".(30 * ($page - 1)).", 30");
	  }
	foreach ($arritem as &$arrItem)
	  {
	    if ($arrItem['zr'] != 0)
	      {
		$arrItem['zr'] = $arrItem['zr'] * -1;
	      }
	    if ($arrItem['poison'] > 0)
	      {
		$arrItem['power'] += $arrItem['poison'];
	      }
	    switch ($arrItem['ptype'])
	      {
	      case 'D':
		$arrItem['name'] .= ' (Dynallca +'.$arrItem['poison'].')';
		break;
	      case 'N':
		$arrItem['name'] .= ' (Nutari +'.$arrItem['poison'].')';
		break;
	      case 'I':
		$arrItem['name'] .= ' (Illani +'.$arrItem['poison'].')';
		break;
	      default:
		break;
	      }
	    switch ($arrItem['type'])
	      {
	      case 'R':
		$arrItem['amount'] = $arrItem['wt'];
		$arrItem['wt'] = 1;
		$arrItem['maxwt'] = 1;
		break;
	      case 'P':
		$arrItem['name'] = $arrItem['name'].' (plan)';
	      }
	    $arrItem['reserved'] = $arrItem['amount'] - $arrItem['reserved'];
	     if ($player -> id == $owner -> fields['owner'] || $perm -> fields['armory']) 
	      {
		$arrItem['action'] = "<a href=tribearmor.php?daj=".$arrItem['id'].">".A_GIVE."</a>";
	      } 
            else 
	      {
		$arrItem['action'] = "<a href=tribearmor.php?reserve=".$arrItem['id'].">Poproś</a>";
	      }
	  }
	if (isset($_POST['type']) && in_array($_POST['type'], array('I', 'P', 'O')))
	  {
	    $arrInfos = array(T_NAME, T_POWER, T_LEVEL);
	    $arrList = array('id', 'name', 'power', 'minlev');
	  }
        else
	  {
	    $arrInfos = array(T_NAME, T_POWER, T_DUR, T_AGI, T_SPEED, T_LEVEL);
	    $arrList2 = array('id', 'name', 'power', 'wt', 'zr', 'szyb', 'minlev');
	  }
	$strId = array_shift($arrList);
	if (!isset($_POST['type']))
	  {
	    $strType = '';
	    $_POST['type'] = '';
	  }
	else
	  {
	    $strType = '&amp;type='.$_POST['type'];
	  }
	if (!isset($_POST['min']))
	  {
	    $strMin = '';
	  }
	else
	  {
	    $strMin = '&amp;min='.$_POST['min'];
	  }
	if (!isset($_POST['max']))
	  {
	    $strMax = '';
	  }
	else
	  {
	    $strMax = '&amp;max='.$_POST['max'];
	  }
	$smarty -> assign(array("Items" => $arritem, 
				"Type" => $strType,
				"Mmin" => $strMin,
				"Mmax" => $strMax,
				"Mlist" => $_GET['lista'],
				"Tinfos" => $arrInfos,
				"Ttypes" => $arrList,
				"Inarmor2" => IN_ARMOR2,
				"Tamount2" => 'Razem/Dostępne',
				"Toptions" => T_OPTIONS,
				"Tor" => T_OR,
				"Tseek" => "Szukaj",
				"Titems" => "z poziomów",
				"Tpages" => $pages,
				"Tpage" => $page,
				"Fpage" => "Idź do strony:",
				"Type2" => $_POST['type'],
				"Otypes" => array('W' => 'broni białej',
						  'A' => 'zbrój',
						  'H' => 'hełmów',
						  'L' => 'nagolenników',
						  'S' => 'tarcz',
						  'B' => 'łuków',
						  'T' => 'różdżek',
						  'C' => 'szat',
						  'R' => 'strzał',
						  'I' => 'pierścieni',
						  'O' => 'łupów',
						  'E' => 'narzędzi',
						  'P' => 'planów'),
				"Tto" => T_TO));
      }
    $amount->Close();
}

/**
* Give players items from armor
*/
if (isset ($_GET['daj'])) 
{
    checkvalue($_GET['daj']);
    if (!isset ($_GET['step3'])) 
    {
        $name = $db -> Execute("SELECT * FROM tribe_zbroj WHERE id=".$_GET['daj']);
	if ($name -> fields['klan'] != $player -> tribe) 
	  {
	    error(ERROR);
	  }
	if ($name->fields['type'] != 'R')
	  {
	    $intAmount = $name->fields['amount'];
	  }
	else
	  {
	    $intAmount = $name->fields['wt'];
	  }
	$objMembers = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$player->tribe);
	$arrMembers = array();
	while (!$objMembers->EOF)
	  {
	    $arrMembers[$objMembers->fields['id']] = $objMembers->fields['user'].' ID:'.$objMembers->fields['id'];
	    $objMembers->MoveNext();
	  }
	$objMembers->Close();
        $smarty -> assign(array("Amount" => $intAmount, 
                                "Name" => $name -> fields['name'],
				"Members" => $arrMembers,
                                "Agive" => A_GIVE,
                                "Tamount" => T_AMOUNT,
                                "Playerid" => "graczowi"));
        $name -> Close();
    }
    if (isset ($_GET['step3']) && $_GET['step3'] == 'add') 
    {
	checkvalue($_POST['did']);
	checkvalue($_POST['amount']);
        $zbroj = $db -> Execute("SELECT * FROM tribe_zbroj WHERE id=".$_GET['daj']);
	if ($zbroj -> fields['klan'] != $player -> tribe) 
	  {
	    error(ERROR);  
	  }
        $dtrib = $db -> Execute("SELECT tribe FROM players WHERE id=".$_POST['did']);
        if ($dtrib -> fields['tribe'] != $player -> tribe) 
        {
            error (NOT_IN_CLAN);
        }
        $dtrib -> Close();
	if (!isset($intReserved))
	  {
	    $intReserved = 0;
	  }
	if ($zbroj->fields['type'] != 'R')
	  {
	    if ($zbroj -> fields['amount'] < $_POST['amount']) 
	      {
		error (NO_ITEMS);
	      }
	    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$zbroj -> fields['name']."' AND `owner`=".$_POST['did']." AND `wt`=".$zbroj -> fields['wt']." AND `type`='".$zbroj -> fields['type']."' AND `power`=".$zbroj -> fields['power']." AND `szyb`=".$zbroj -> fields['szyb']." AND `zr`=".$zbroj -> fields['zr']." AND `maxwt`=".$zbroj -> fields['maxwt']." AND `poison`=".$zbroj -> fields['poison']." AND `status`='U' AND `ptype`='".$zbroj -> fields['ptype']."' AND `cost`=1");
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$_POST['did'].",'".$zbroj -> fields['name']."',".$zbroj -> fields['power'].",'".$zbroj -> fields['type']."',1,".$zbroj -> fields['zr'].",".$zbroj -> fields['wt'].",".$zbroj -> fields['minlev'].",".$zbroj -> fields['maxwt'].",".$_POST['amount'].",'".$zbroj -> fields['magic']."',".$zbroj -> fields['poison'].",".$zbroj -> fields['szyb'].",'".$zbroj -> fields['twohand']."','".$zbroj -> fields['ptype']."', ".$zbroj -> fields['repair'].")");
	      } 
            else 
	      {
		if ($zbroj -> fields['type'] != 'R')
		  {
		    $db -> Execute("UPDATE equipment SET amount=amount+".$_POST['amount']." WHERE id=".$test -> fields['id']);
		  }
                else
		  {
		    $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$zbroj -> fields['wt']." WHERE `id`=".$test -> fields['id']);
		  }
	      }
	    if ($_POST['amount'] < $zbroj -> fields['amount']) 
	      {
		$db -> Execute("UPDATE tribe_zbroj SET amount=amount-".$_POST['amount']." WHERE id=".$zbroj -> fields['id']);
	      } 
            else 
	      {
		$db -> Execute("DELETE FROM tribe_zbroj WHERE id=".$zbroj -> fields['id']);
	      }
	  }
	else
	  {
	    if ($zbroj -> fields['wt'] < $_POST['amount']) 
	      {
		error (NO_ITEMS);
	      }
	    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$zbroj -> fields['name']."' AND `owner`=".$_POST['did']." AND `type`='R' AND `power`=".$zbroj -> fields['power']." AND `szyb`=".$zbroj -> fields['szyb']." AND `zr`=".$zbroj -> fields['zr']." AND `poison`=".$zbroj -> fields['poison']." AND `status`='U' AND `ptype`='".$zbroj -> fields['ptype']."' AND `cost`=1");
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$_POST['did'].",'".$zbroj -> fields['name']."',".$zbroj -> fields['power'].",'R',1,".$zbroj -> fields['zr'].",".$_POST['amount'].",".$zbroj -> fields['minlev'].",".$_POST['amount'].",1,'".$zbroj -> fields['magic']."',".$zbroj -> fields['poison'].",".$zbroj -> fields['szyb'].",'".$zbroj -> fields['twohand']."','".$zbroj -> fields['ptype']."', ".$zbroj -> fields['repair'].")");
	      } 
            else 
	      {
		$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
	      }
	    if ($_POST['amount'] < $zbroj -> fields['wt']) 
	      {
		$db -> Execute("UPDATE tribe_zbroj SET `wt`=`wt-".$_POST['amount']." WHERE `id`=".$zbroj -> fields['id']);
	      } 
            else 
	      {
		$db -> Execute("DELETE FROM `tribe_zbroj` WHERE `id`=".$zbroj -> fields['id']);
	      }
	  }
        $test -> Close();
        // Get name of the person that receives armour.
        $objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['did'].';');
        $strReceiversName = $objGetName -> fields['user'];
        $objGetName -> Close();
        unset( $objGetName );

        message("success", YOU_GIVE1.'<b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>'.YOU_GIVE2.'<b>'.$_POST['did']."</b> ".$_POST['amount'].T_AMOUNT.$zbroj -> fields['name'].'.');
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['did'].", '".YOU_GET.$_POST['amount'].T_AMOUNT.$zbroj -> fields['name'].".','".$newdate."', 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$_POST['did'].", '".YOU_GET.$_POST['amount'].T_AMOUNT.$zbroj -> fields['name'].".','".$newdate."')");
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$owner->fields['owner'].", '".YOU_GIVE1.'<b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>'.YOU_GIVE2.'<b>'.$_POST['did']."</b> ".$_POST['amount'].T_AMOUNT.$zbroj -> fields['name'].".','".$newdate."', 'C')");
        $objPerm = $db -> Execute("SELECT player FROM tribe_perm WHERE tribe=".$player -> tribe." AND armory=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".YOU_GIVE1.'<b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>'.YOU_GIVE2.'<b>'.$_POST['did']."</b> ".$_POST['amount'].T_AMOUNT.$zbroj -> fields['name'].".','".$newdate."', 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $zbroj -> Close();
    }
}

/**
* Add items to armor
*/
if (isset ($_GET['step']) && $_GET['step'] == 'daj') 
{
    if (isset ($_GET['step2']) && $_GET['step2'] == 'add') 
    {
        if (!isset($_POST['przedmiot'])) 
        {
            error(SELECT_ITEM);
        }
	checkvalue($_POST['przedmiot']);
	if (!isset($_POST['all']))
	  {
	    integercheck($_POST['amount']);
	    checkvalue($_POST['amount']);
	  }
        $przed = $db -> Execute("SELECT * FROM equipment WHERE id=".$_POST['przedmiot']);
        if (!$przed -> fields['name']) 
        {
            error (ERROR);
        }
	if ($przed -> fields['status'] != 'U') 
        {
            error (ERROR);
        }
        if ($przed -> fields['owner'] != $player -> id) 
        {
            error (ERROR);
        }
	if ($przed->fields['type'] == 'Q')
	  {
	    error("Nie możesz przekazać tego przemiotu.");
	  }
	if ($przed->fields['type'] != 'R')
	  {
	    if (isset($_POST['all']))
	      {
		$_POST['amount'] = $przed->fields['amount'];
	      }
	    if ($przed -> fields['amount'] < $_POST['amount']) 
	      {
		error (NO_AMOUNT);
	      }
	    $test = $db -> Execute("SELECT `id` FROM `tribe_zbroj` WHERE `name`='".$przed -> fields['name']."' AND `klan`=".$player -> tribe." AND `wt`=".$przed -> fields['wt']." AND `type`='".$przed -> fields['type']."' AND `power`=".$przed -> fields['power']." AND `szyb`=".$przed -> fields['szyb']." AND `zr`=".$przed -> fields['zr']." AND `maxwt`=".$przed -> fields['maxwt']." AND `poison`=".$przed -> fields['poison']." AND `ptype`='".$przed -> fields['ptype']."' AND `minlev`=".$przed->fields['minlev']);
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO tribe_zbroj (klan, name, power, type, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> tribe.",'".$przed -> fields['name']."',".$przed -> fields['power'].",'".$przed -> fields['type']."',".$przed -> fields['zr'].",".$przed -> fields['wt'].",".$przed -> fields['minlev'].",".$przed -> fields['maxwt'].",".$_POST['amount'].",'".$przed -> fields['magic']."',".$przed -> fields['poison'].",".$przed -> fields['szyb'].",'".$przed -> fields['twohand']."','".$przed -> fields['ptype']."', ".$przed -> fields['repair'].")");
	      } 
            else 
	      {
		$db -> Execute("UPDATE tribe_zbroj SET amount=amount+".$_POST['amount']." WHERE id=".$test -> fields['id']);
	      }
	    if ($_POST['amount'] < $przed -> fields['amount']) 
	      {
		$db -> Execute("UPDATE equipment SET amount=amount-".$_POST['amount']." WHERE id=".$przed -> fields['id']);
	      } 
            else 
	      {
		$db -> Execute("DELETE FROM equipment WHERE id=".$przed -> fields['id']);
	      }
	  }
	else
	  {
	    if (isset($_POST['all']))
	      {
		$_POST['amount'] = $przed->fields['wt'];
	      }
	    if ($przed -> fields['wt'] < $_POST['amount']) 
	      {
		error (NO_AMOUNT);
	      }
	    $test = $db -> Execute("SELECT `id` FROM `tribe_zbroj` WHERE `name`='".$przed -> fields['name']."' AND `klan`=".$player -> tribe." AND type='R' AND power=".$przed -> fields['power']." AND szyb=".$przed -> fields['szyb']." AND zr=".$przed -> fields['zr']." AND poison=".$przed -> fields['poison']." AND ptype='".$przed -> fields['ptype']."'");
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO tribe_zbroj (klan, name, power, type, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> tribe.",'".$przed -> fields['name']."',".$przed -> fields['power'].",'R',".$przed -> fields['zr'].",".$_POST['amount'].",".$przed -> fields['minlev'].",".$_POST['amount'].",1,'".$przed -> fields['magic']."',".$przed -> fields['poison'].",".$przed -> fields['szyb'].",'".$przed -> fields['twohand']."','".$przed -> fields['ptype']."', ".$przed -> fields['repair'].")");
	      } 
            else 
	      {
		$db -> Execute("UPDATE `tribe_zbroj` SET `wt`=`wt`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
	      }
	    if ($_POST['amount'] < $przed -> fields['wt']) 
	      {
		$db -> Execute("UPDATE `equipment` SET `wt`=`wt`-".$_POST['amount']." WHERE `id`=".$przed -> fields['id']);
	      } 
            else 
	      {
		$db -> Execute("DELETE FROM `equipment` WHERE `id`=".$przed -> fields['id']);
	      }
	  }
        $test -> Close();
        message("success", YOU_ADD.$_POST['amount'].T_AMOUNT.$przed -> fields['name']."</b> ".TO_ARMOR);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$owner -> fields['owner'].", '".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.ADD_TO.$_POST['amount'].T_AMOUNT.$przed -> fields['name'].".','".$newdate."', 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$owner -> fields['owner'].", '".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.ADD_TO.$_POST['amount'].T_AMOUNT.$przed -> fields['name'].".','".$newdate."')");
        $objPerm = $db -> Execute("SELECT player FROM tribe_perm WHERE tribe=".$player -> tribe." AND armory=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$objPerm -> fields['player'].", '".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.ADD_TO.$_POST['amount'].T_AMOUNT.$przed -> fields['name'].".','".$newdate."', 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $przed -> Close();
    }
    $arrItems = $db->GetAll("SELECT * FROM equipment WHERE status='U' AND owner=".$player -> id." AND `type`!='Q'");
    foreach ($arrItems as &$arrItem)
      {
	switch ($arrItem['ptype'])
	  {
	  case 'D':
	    $arrItem['name'] .= ' (Dynallca +'.$arrItem['poison'].')';
	    break;
	  case 'N':
	    $arrItem['name'] .= ' (Nutari +'.$arrItem['poison'].')';
	    break;
	  case 'I':
	    $arrItem['name'] .= ' (Illani +'.$arrItem['poison'].')';
	    break;
	  default:
	    break;
	  }
	switch ($arrItem['type'])
	  {
	  case 'R':
	    $arrItem['amount'] = $arrItem['wt'];
	    $arrItem['wt'] = 1;
	    $arrItem['maxwt'] = 1;
	    break;
	  case 'P':
	    $arrItem['name'] = 'Plan: '.$arrItem['name'].' (poziom: '.$arrItem['minlev'].')';
	    break;
	  default:
	    break;
	  }
	$arrItem['zr'] = $arrItem['zr'] * -1;
      }
    $smarty -> assign(array("Items" => $arrItems,
			    "Iag" => "zr",
			    "Ispd" => "szyb",
                            "Additem" => ADD_ITEM,
                            "Item" => ITEM,
                            "Amount2" => AMOUNT2,
			    "Tall" => "wszystkie posiadane)",
                            "Aadd" => A_ADD2));
}

/**
* Initialization of variables
*/
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (!isset($_GET['step2'])) 
{
    $_GET['step2'] = '';
}
if (!isset($_GET['step3'])) 
{
    $_GET['step3'] = '';
}
if (!isset($_GET['daj'])) 
{
    $_GET['daj'] = '';
}
if (!isset($intReserved))
  {
    $intReserved = 0;
  }

if ($intReserved == 0)
  {
    /**
     * Assign variables to template and display page
     */
    require_once('includes/tribemenu.php');
    $smarty -> assign(array("Step" => $_GET['step'], 
			    "Step2" => $_GET['step2'], 
			    "Give" => $_GET['daj'], 
			    "Step3" => $_GET['step3'],
			    "Reserve" => $_GET['reserve']));
    $smarty -> display ('tribearmor.tpl');
    
    require_once("includes/foot.php");
  }
?>
