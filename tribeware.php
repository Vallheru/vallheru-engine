<?php
/**
 *   File functions:
 *   Tribe magazine - add potions to clan, give potions to clan members
 *
 *   @name                 : tribeware.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 16.07.2012
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

$title = "Magazyn klanu";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/tribeware.php");

/**
* Check if player is in clan
*/
if (!$player -> tribe) 
{
    error (NO_CLAN);
}

/**
* Check if player is in city
*/
if ($player -> location != 'Altara' && $player -> location != 'Ardulith')  
{
    error (ERROR);
}

/**
* Check who have permission to give potions
*/
$perm = $db -> Execute("SELECT warehouse FROM tribe_perm WHERE tribe=".$player -> tribe." AND player=".$player -> id);
$owner = $db -> Execute("SELECT `owner`, `level` FROM `tribes` WHERE `id`=".$player -> tribe);
if ($owner->fields['level'] == 1)
  {
    error('Najpierw musisz rozbudować klan, aby mieć dostęp do tego miejsca. (<a href="tribes.php?view=my">Wróć</a>)');
  }

/**
 * Reserve items from tribe
 */
if (isset($_GET['reserve']) && $_GET['reserve'] != '')
  {
    checkvalue($_GET['reserve']);
    if (!isset ($_GET['step3'])) 
      {
        $name = $db -> Execute("SELECT * FROM `tribe_mag` WHERE `id`=".$_GET['reserve']." AND `owner`=".$player->tribe);
	if (!$name->fields['id'])
	  {
	    error('Nie ma takiego przedmiotu.');
	  }
	$intAmount = $name->fields['amount'] - $name->fields['reserved'];
	if ($name -> fields['type'] == 'A' && stripos($name->fields['name'], 'oszukanie') === FALSE)
        {
            $strName = $name -> fields['name'];
        }
	else
	  {
	    $strName = $name->fields['name']." (moc:".$name -> fields['power'].")";
	  }
        $smarty -> assign(array("Amount" => $intAmount, 
                                "Name" => $strName,
                                "Aask" => 'Poproś o',
                                "Tamount" => 'sztuk'));
        $name -> Close();
      }
    else
      {
	checkvalue($_POST['amount']);
        $zbroj = $db -> Execute("SELECT `id`, `name`, `amount`, `reserved` FROM `tribe_mag` WHERE `id`=".$_GET['reserve']." AND `owner`=".$player->tribe);
	if (!$zbroj->fields['id']) 
	  {
	    error('Nie ma takiego przedmiotu.');  
	  }
	$intAmount = $zbroj->fields['amount'] - $zbroj->fields['reserved'];
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
	    $db->Execute("INSERT INTO `tribe_reserv` (`iid`, `pid`, `amount`, `tribe`, `type`) VALUES(".$_GET['reserve'].", ".$player->id.", ".$_POST['amount'].", ".$player->tribe.", 'P')");
	    $db->Execute("UPDATE `tribe_mag` SET `reserved`=`reserved`+".$_POST['amount']." WHERE `id`=".$_GET['reserve']);
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
	    message('success', 'Poprosił'.$strSuffix.' o '.$_POST['amount'].' sztuk '.$zbroj->fields['name'].' z magazynu klanu.');
	    $_GET['reserve'] = '';
	    $_GET['step3'] = '';
	  }
      }
  }
else
  {
    $_GET['reserve'] = '';
  }

/**
* Potions list in warehouse
*/
if (isset ($_GET['step']) && $_GET['step'] == 'zobacz') 
{
    $arrlist = array('id', 'name', 'efect');
    if (isset($_GET['lista']) && !in_array($_GET['lista'], $arrlist)) 
    {
        error (ERROR);
    }
    $amount = $db -> Execute("SELECT SUM(`amount`) FROM `tribe_mag` WHERE `owner`=".$player-> tribe);
    $przed = $amount->fields['SUM(`amount`)'];
    if ($przed == 0) 
    {
        error(NO_ITEMS);
    }
    $amount -> Close();
    $arrPotions = $db->GetAll("SELECT * FROM tribe_mag WHERE owner=".$player -> tribe." ORDER BY ".$_GET['lista']." DESC");
    foreach ($arrPotions as &$arrPotion)
      {
	if ($arrPotion['type'] != 'A' || stripos($arrPotion['name'], 'oszukanie') !== FALSE)
	  {
	    $arrPotion['name'] .= ' (moc:'.$arrPotion['power'].')';
	  }
	$arrPotion['amount1'] = $arrPotion['amount'].' / '.($arrPotion['amount'] - $arrPotion['reserved']);
	if ($player -> id == $owner -> fields['owner'] || $perm -> fields['warehouse']) 
	  {
	    $arrPotion['link'] = "- <a href=tribeware.php?daj=".$arrPotion['id'].">".A_GIVE."</a>";
	  }
	else
	  {
	    $arrPotion['link'] = "- <a href=tribeware.php?reserve=".$arrPotion['id'].">poproś</a>";
	  }
      }
    $smarty -> assign(array("Amount1" => $przed, 
			    "Ipotions" => $arrPotions,
			    "Inware" => IN_WARE,
			    "Potions" => POTIONS,
			    "Tname" => T_NAME,
			    "Tefect" => T_EFECT,
			    "Tamount2" => "Razem/Dostępne",
			    "Toptions" => T_OPTIONS));
}

/**
* Give player potions from tribe warehouse
*/
if (isset ($_GET['daj'])) 
  {
    $_GET['daj'] = intval($_GET['daj']);
    if($_GET['daj'] <= 0) 
      {
	error(ERROR);
      }
    if (!isset ($_GET['step3'])) 
    {
        $miks = $db -> Execute("SELECT * FROM tribe_mag WHERE id=".$_GET['daj']);
	if ($miks -> fields['type'] == 'A' && stripos($miks->fields['name'], 'oszukanie') === FALSE)
        {
            $strName = $miks -> fields['name'];
        }
	else
	  {
	    $strName = $miks->fields['name']." (moc:".$miks -> fields['power'].")";
	  }
	$objMembers = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$player->tribe);
	$arrMembers = array();
	while (!$objMembers->EOF)
	  {
	    $arrMembers[$objMembers->fields['id']] = $objMembers->fields['user'].' ID:'.$objMembers->fields['id'];
	    $objMembers->MoveNext();
	  }
	$objMembers->Close();
        $smarty -> assign(array("Id" => $_GET['daj'], 
				"Members" => $arrMembers,
				"Name" => $strName, 
				"Amount" => $miks -> fields['amount'],
				"Agive" => A_GIVE,
				"Playerid" => PLAYER_ID,
				"Tamount" => T_AMOUNT));
        $miks -> Close();
    }
    if (isset ($_GET['step3']) && $_GET['step3'] == 'add') 
    {
        integercheck($_POST['amount']);
	checkvalue($_POST['did']);
	checkvalue($_POST['amount']);
        $dtrib = $db -> Execute("SELECT tribe FROM players WHERE id=".$_POST['did']);
        if ($dtrib -> fields['tribe'] != $player -> tribe) 
        {
                error (NOT_IN_CLAN);
        }
        $dtrib -> Close();
        $zbroj = $db -> Execute("SELECT * FROM tribe_mag WHERE id=".$_GET['daj']);
        if ($zbroj -> fields['amount'] < $_POST['amount']) 
        {
            error (NO_ITEMS);
        }
        $test = $db -> Execute("SELECT id FROM potions WHERE name='".$zbroj -> fields['name']."' AND owner=".$_POST['did']." AND status='K' AND power=".$zbroj -> fields['power']);
        if (!$test -> fields['id']) 
        {
            $db -> Execute("INSERT INTO potions (name, owner, efect, type, power, status, amount) VALUES('".$zbroj -> fields['name']."',".$_POST['did'].",'".$zbroj -> fields['efect']."','".$zbroj -> fields['type']."',".$zbroj -> fields['power'].",'K',".$_POST['amount'].")");
        } 
            else 
        {
            $db -> Execute("UPDATE potions SET amount=amount+".$_POST['amount']." WHERE id=".$test -> fields['id']);
        }
        $test -> Close();
	if (!isset($intReserved))
	  {
	    $intReserved = 0;
	  }
        if ($_POST['amount'] < $zbroj -> fields['amount']) 
        {
            $db -> Execute("UPDATE `tribe_mag` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$zbroj -> fields['id']);
        } 
            else 
        {
            $db -> Execute("DELETE FROM tribe_mag WHERE id=".$zbroj -> fields['id']);
        }
        
        // Get name of the person that receives potions.
        $objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['did'].';');
        $strReceiversName = $objGetName -> fields['user'];
        $objGetName -> Close();
        unset( $objGetName );

        message("success", YOU_GIVE1.'<b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>'.YOU_GIVE2.'<b>'.$_POST['did']."</b> ".$_POST['amount'].T_AMOUNT.$zbroj -> fields['name']);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['did'].", '".YOU_GET.$_POST['amount'].T_AMOUNT.$zbroj -> fields['name'].".', ".$strDate.", 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$_POST['did'].", '".YOU_GET.$_POST['amount'].T_AMOUNT.$zbroj -> fields['name'].".', ".$strDate.")");
        $objPerm = $db -> Execute("SELECT player FROM tribe_perm WHERE tribe=".$player -> tribe." AND warehouse=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".YOU_GIVE1.'<b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>'.YOU_GIVE2.'<b>'.$_POST['did']."</b> ".$_POST['amount'].T_AMOUNT.$zbroj -> fields['name'].".', ".$strDate.", 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $zbroj -> Close();
	$_GET['daj'] = '';
	$_GET['step3'] = '';
    }
}

/**
* Add potions to tribe warehouse
*/
if (isset ($_GET['step']) && $_GET['step'] == 'daj') 
{
    $miks = $db -> Execute("SELECT * FROM potions WHERE status='K' AND owner=".$player -> id);
    $arrPotions = array();
    while (!$miks -> EOF) 
      {
	$arrPotions[$miks->fields['id']] = '(ilość: '.$miks->fields['amount'].') '.$miks->fields['name'].' (moc: '.$miks->fields['power'].')';
        $miks -> MoveNext();
      }
    $miks -> Close();
    $smarty -> assign( array("Additem" => ADD_ITEM,
			     "Potions" => $arrPotions,
			     "Potion" => POTION,
			     "Amount2" => AMOUNT2,
			     "Aadd" => "Dodaj",
			     "Tall" => "wszystkie posiadane)",
			     "Tamount2" => T_AMOUNT2));
    if (isset ($_GET['step2']) && $_GET['step2'] == 'add') 
    {
	checkvalue($_POST['przedmiot']);
	if (!isset($_POST['all']))
	  {
	    integercheck($_POST['amount']);
	    checkvalue($_POST['amount']);
	  }
        $przed = $db -> Execute("SELECT * FROM potions WHERE id=".$_POST['przedmiot']);
        if (!$przed -> fields['name']) 
        {
            error (ERROR);
        }
	if ($przed -> fields['owner'] != $player-> id) 
	  {
	    error(ERROR);
	  }
        if ($przed -> fields['status'] != 'K') 
	  {
	    error(ERROR);  
	  }
	if (isset($_POST['all']))
	  {
	    $_POST['amount'] = $przed->fields['amount'];
	  }
        if ($_POST['amount'] > $przed -> fields['amount']) 
        {
            error (NO_AMOUNT);
        }
        $test = $db -> Execute("SELECT id FROM tribe_mag WHERE name='".$przed -> fields['name']."' AND owner=".$player -> tribe." AND power=".$przed -> fields['power']);
        if (!$test -> fields['id']) 
        {
            $db -> Execute("INSERT INTO tribe_mag (name, owner, efect, type, power, amount) VALUES('".$przed -> fields['name']."',".$player -> tribe.",'".$przed -> fields['efect']."','".$przed -> fields['type']."',".$przed -> fields['power'].",".$_POST['amount'].")");
        } 
            else 
        {
            $db -> Execute("UPDATE tribe_mag SET amount=amount+".$_POST['amount']." WHERE id=".$test -> fields['id']);
        }
        if ($_POST['amount'] < $przed -> fields['amount']) 
        {
            $db -> Execute("UPDATE potions SET amount=amount-".$_POST['amount']." WHERE id=".$przed -> fields['id']);
        } 
            else 
        {
            $db -> Execute("DELETE FROM potions WHERE id=".$przed -> fields['id']);
        }
        message("success", YOU_ADD.$_POST['amount'].T_AMOUNT."<b>".$przed -> fields['name'].TO_WARE);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$owner -> fields['owner'].", '".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.ADD_TO.$_POST['amount'].T_AMOUNT.$przed -> fields['name'].".', ".$strDate.", 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$owner -> fields['owner'].", '".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.ADD_TO.$_POST['amount'].T_AMOUNT.$przed -> fields['name'].".', ".$strDate.")");
        $objPerm = $db -> Execute("SELECT player FROM tribe_perm WHERE tribe=".$player -> tribe." AND warehouse=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.ADD_TO.$_POST['amount'].T_AMOUNT.$przed -> fields['name'].".', ".$strDate.", 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $przed -> Close();
	$_GET['step'] = '';
	$_GET['step2'] = '';
    }
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

/**
* Main menu
*/
if ($_GET['step'] == '' && $_GET['daj'] == '' && $_GET['step2'] == '' && $_GET['step3'] == '' && $_GET['reserve'] == '') 
{
    $smarty -> assign(array("Wareinfo" => WARE_INFO,
        "Aadd" => A_ADD,
        "Ashow" => A_SHOW));
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
    $smarty -> assign(array("Step" =>$_GET['step'], 
			    "Step2" => $_GET['step2'], 
			    "Give" => $_GET['daj'], 
			    "Step3" => $_GET['step3'],
			    "Reserve" => $_GET['reserve']));
    $smarty -> display('tribeware.tpl');
    
    require_once("includes/foot.php");
  }
?>
