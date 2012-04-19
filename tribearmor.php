<?php
/**
 *   File functions:
 *   Tribe armor - weapons and armors
 *
 *   @name                 : tribearmor.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
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
if (isset($_GET['reserve']))
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
                            "Armortype" => $arrType,
                            "Armorlink" => array(A_SHOW_W, A_SHOW_A, A_SHOW_H, A_SHOW_L, A_SHOW_D, A_SHOW_B, A_SHOW_S, A_SHOW_C, A_SHOW_R, A_SHOW_I, 'Zobaczyć listę łupów w zbrojowni klanu', 'Zobaczyć listę narzędzi w zbrojowni klanu', 'Zobacz listę planów w zbrojowni klanu'),
                            "Aadd" => A_ADD));
  }

/**
* List of items in tribe armor
*/
if (isset($_GET['step']) && $_GET['step'] == 'zobacz') 
{
    $arrList = array('id', 'name', 'power', 'wt', 'zr', 'szyb', 'minlev');
    if (isset($_GET['type']) && !in_array($_GET['type'], $arrType)) 
    {
        error (ERROR);
    }
    if (isset($_GET['lista']) && !in_array($_GET['lista'], $arrList)) 
    {
        error (ERROR);
    }
    if (!isset($_GET['type'])) 
    {
        error(WHAT_YOU);
    }
    $arrItem = array(T_WEAPONS, T_ARMORS, T_HELMETS, T_LEGS, T_SHIELDS, T_BOWS, T_STAFFS, T_CAPES, T_ARROWS, T_RINGS, "łupów", 'narzędzi', 'planów');
    $intKey = array_search($_GET['type'], $arrType);
    $item = $arrItem[$intKey];
    $amount = $db -> Execute("SELECT SUM(`amount`) FROM `tribe_zbroj` WHERE `klan`=".$player -> tribe." AND `type`='".$_GET['type']."'");
    if (!$amount -> fields['SUM(`amount`)']) 
      {
	message('error', NO_ITEMS.$item.IN_ARMOR);
	$_GET['step'] = '';
      }
    else
      {
	$przed = 0;
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
	if (isset($_GET['levels']) && $_GET['levels'] == 'yes')
	  {
	    checkvalue($_POST['min']);
	    checkvalue($_POST['max']);
	    if ($_POST['max'] < $_POST['min'])
	      {
		error(ERROR);
	      }
	    $arritem = $db -> Execute("SELECT * FROM `tribe_zbroj` WHERE `klan`=".$player -> tribe." AND `type`='".$_GET['type']."' AND `minlev`>=".$_POST['min']." AND `minlev`<=".$_POST['max']." ORDER BY ".$_GET['lista'].$strOrder);
	  }
        else
	  {
	    $arritem = $db -> Execute("SELECT * FROM tribe_zbroj WHERE klan=".$player -> tribe." AND type='".$_GET['type']."' ORDER BY ".$_GET['lista'].$strOrder);
	  }
	$arragi = array();
	$arrpower = array();
	$arrname = array();
	$arrdur = array();
	$arrmaxdur = array();
	$arrspeed = array();
	$arramount = array();
	$arraction = array();
	$arrLevel = array();
	$arrReserved = array();
	while (!$arritem -> EOF) 
	  {
	    if ($arritem -> fields['zr'] != 0) 
	      {
		$arragi[] = $arritem->fields['zr'] * -1;
	      }
	    else
	      {
		$arragi[] = 0;
	      }
	    $arrpower[] = $arritem -> fields['power'];
	    if ($arritem -> fields['poison'] > 0) 
	      {
		$arrpower[] = $arritem -> fields['power'] + $arritem -> fields['poison'];
	      }
	    switch ($arritem->fields['ptype'])
	      {
	      case 'D':
		$arritem->fields['name'] .= ' (Dynallca +'.$arritem->fields['poison'].')';
		break;
	      case 'N':
		$arritem->fields['name'] .= ' (Nutari +'.$arritem->fields['poison'].')';
		break;
	      case 'I':
		$arritem->fields['name'] .= ' (Illani +'.$arritem->fields['poison'].')';
		break;
	      default:
		break;
	      }
	    $arrname[] = $arritem -> fields['name'];
	    if ($arritem->fields['type'] != 'R')
	      {
		$arrdur[] = $arritem -> fields['wt'];
		$arrmaxdur[] = $arritem -> fields['maxwt'];
		$arramount[] = $arritem -> fields['amount'];
	      }
	    else
	      {
		$arrdur[] = 1;
		$arrmaxdur[] = 1;
		$arramount[] = $arritem -> fields['wt'];
	      }
	    $arrReserved[] = end($arramount) - $arritem->fields['reserved'];
	    $arrspeed[] = $arritem -> fields['szyb'];
	    $arrLevel[] = $arritem -> fields['minlev'];
	    if ($player -> id == $owner -> fields['owner'] || $perm -> fields['armory']) 
	      {
		$arraction[] = "<td>- <a href=tribearmor.php?daj=".$arritem -> fields['id'].">".A_GIVE."</a></td>";
	      } 
            else 
	      {
		$arraction[] = "<td>- <a href=tribearmor.php?reserve=".$arritem->fields['id'].">poproś</a></td>";
	      }
	    $arritem -> MoveNext();
	  }
	$arritem -> Close();
	if (in_array($_GET['type'], array('I', 'P', 'O')))
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
	$smarty -> assign(array("Name" => $arrname, 
				"Agility" => $arragi, 
				"Power" => $arrpower, 
				"Durability" => $arrdur, 
				"Maxdurability" => $arrmaxdur, 
				"Speed" => $arrspeed, 
				"Amount" => $arramount,
				"Reserved" => $arrReserved,
				"Action" => $arraction,
				"Ilevel" => $arrLevel,
				"Type" => $_GET['type'],
				"Tinfos" => $arrInfos,
				"Ttypes" => $arrList,
				"Inarmor2" => IN_ARMOR2,
				"Tamount2" => 'Razem/Dostępne',
				"Toptions" => T_OPTIONS,
				"Tor" => T_OR,
				"Tseek" => T_SEEK,
				"Titems" => T_ITEMS,
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
		$db -> Execute("UPDATE tribe_zbroj SET amount=amount-".$_POST['amount'].", `reserved`=`reserved`-".$intReserved." WHERE id=".$zbroj -> fields['id']);
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
		$db -> Execute("UPDATE tribe_zbroj SET `wt`=`wt-".$_POST['amount'].", `reserved`=`reserved`-".$intReserved." WHERE `id`=".$zbroj -> fields['id']);
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
        integercheck($_POST['amount']);
	checkvalue($_POST['przedmiot']);
	checkvalue($_POST['amount']);
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
	    if ($przed -> fields['amount'] < $_POST['amount']) 
	      {
		error (NO_AMOUNT);
	      }
	    $test = $db -> Execute("SELECT id FROM tribe_zbroj WHERE name='".$przed -> fields['name']."' AND klan=".$player -> tribe." AND wt=".$przed -> fields['wt']." AND type='".$przed -> fields['type']."' AND power=".$przed -> fields['power']." AND szyb=".$przed -> fields['szyb']." AND zr=".$przed -> fields['zr']." AND maxwt=".$przed -> fields['maxwt']." AND poison=".$przed -> fields['poison']." AND ptype='".$przed -> fields['ptype']."'");
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
    $arritem = $db -> Execute("SELECT * FROM equipment WHERE status='U' AND owner=".$player -> id);
    $arrname = array();
    $arrid = array();
    $arramount = array();
    $arrPower = array();
    $arrSpeed = array();
    $arrDur = array();
    $arrMaxdur = array();
    $arrAgi = array();
    while (!$arritem -> EOF) 
    {
        switch ($arritem->fields['ptype'])
	  {
	  case 'D':
	    $arritem->fields['name'] .= ' (Dynallca +'.$arritem->fields['poison'].')';
	    break;
	  case 'N':
	    $arritem->fields['name'] .= ' (Nutari +'.$arritem->fields['poison'].')';
	    break;
	  case 'I':
	    $arritem->fields['name'] .= ' (Illani +'.$arritem->fields['poison'].')';
	    break;
	  default:
	    break;
	  }
        $arrname[] = $arritem -> fields['name'];
        $arrid[] = $arritem -> fields['id'];
	if ($arritem->fields['type'] != 'R')
	  {
	    $arrDur[] =  $arritem->fields['wt'];
	    $arrMaxdur[] = $arritem->fields['maxwt'];
	    $arramount[] = $arritem -> fields['amount'];
	  }
	else
	  {
	    $arrDur[] = 1;
	    $arrMaxdur[] = 1;
	    $arramount[] = $arritem -> fields['wt'];
	  }
	$arrPower[] = $arritem->fields['power'];
	$arrSpeed[] = $arritem->fields['szyb'];
	$arrAgi[] = $arritem->fields['zr'] * -1;
        $arritem -> MoveNext();
    }
    $arritem -> Close();
    $smarty -> assign(array("Name" => $arrname, 
                            "Itemid" => $arrid, 
                            "Amount" => $arramount,
			    "Ipower" => $arrPower,
			    "Ispeed" => $arrSpeed,
			    "Idur" => $arrDur,
			    "Imaxdur" => $arrMaxdur,
			    "Iagi" => $arrAgi,
			    "Iag" => "zr",
			    "Ispd" => "szyb",
                            "Additem" => ADD_ITEM,
                            "Item" => ITEM,
                            "Amount2" => AMOUNT2,
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
