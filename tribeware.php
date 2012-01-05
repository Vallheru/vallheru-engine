<?php
/**
 *   File functions:
 *   Tribe magazine - add potions to clan, give potions to clan members
 *
 *   @name                 : tribeware.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 05.01.2012
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
* Assign variable to template
*/
$smarty -> assign("Message",'');

/**
* Check who have permission to give potions
*/
$perm = $db -> Execute("SELECT warehouse FROM tribe_perm WHERE tribe=".$player -> tribe." AND player=".$player -> id);
$owner = $db -> Execute("SELECT owner FROM tribes WHERE id=".$player -> tribe);

/**
* Main menu
*/
if (!isset($_GET['step']) && !isset($_GET['daj']) && !isset($_GET['step2']) && !isset($_GET['step3'])) 
{
    $smarty -> assign(array("Wareinfo" => WARE_INFO,
        "Aadd" => A_ADD,
        "Ashow" => A_SHOW));
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
    $miks = $db -> Execute("SELECT * FROM tribe_mag WHERE owner=".$player -> tribe." ORDER BY ".$_GET['lista']." DESC");
    if (!$miks -> fields['id']) 
    {
        error(NO_ITEMS);
    }
    $amount = $db -> Execute("SELECT amount FROM tribe_mag WHERE owner=".$player-> tribe);
    $przed = 0;
    while (!$amount -> EOF) 
    {
        $przed = $przed + $amount -> fields['amount'];
        $amount -> MoveNext();
    }
    $amount -> Close();
    $arrname = array();
    $arrefect = array();
    $arramount = array();
    $arrlink = array();
    $i = 0;
    while (!$miks -> EOF) 
    {
        $arrname[$i] = $miks -> fields['name'];
        if ($miks -> fields['type'] != 'A')
        {
            $arrname[$i] = $arrname[$i]." (moc:".$miks -> fields['power'].")";
        }
        $arrefect[$i] = $miks -> fields['efect'];
        $arramount[$i] = $miks -> fields['amount'];
        if ($player -> id == $owner -> fields['owner'] || $perm -> fields['warehouse']) 
        {
            $arrlink[$i] = "<td>- <a href=tribeware.php?daj=".$miks -> fields['id'].">".A_GIVE."</a></td>";
        } 
            else 
        {
            $arrlink[$i] = "<td></td>";
        }
        $miks -> MoveNext();
        $i = $i + 1;
    }
    $miks -> Close();
    $smarty -> assign ( array("Amount1" => $przed, 
        "Name" => $arrname, 
        "Efect" => $arrefect, 
        "Amount" => $arramount,
        "Link" => $arrlink,
        "Inware" => IN_WARE,
        "Potions" => POTIONS,
        "Tname" => T_NAME,
        "Tefect" => T_EFECT,
        "Tamount2" => T_AMOUNT2,
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
        $smarty -> assign ( array("Id" => $_GET['daj'], 
            "Name" => $miks -> fields['name'], 
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
        if ($_POST['amount'] < $zbroj -> fields['amount']) 
        {
            $db -> Execute("UPDATE tribe_mag SET amount=amount-".$_POST['amount']." WHERE id=".$zbroj -> fields['id']);
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

        $smarty -> assign ("Message", YOU_GIVE1.'<b><a href="view.php?view='.$_POST['did'].'">'.$strReceiversName.'</a></b>'.YOU_GIVE2.'<b>'.$_POST['did']."</b> ".$_POST['amount'].T_AMOUNT.$zbroj -> fields['name']);
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
    }
}

/**
* Add potions to tribe warehouse
*/
if (isset ($_GET['step']) && $_GET['step'] == 'daj') 
{
    $miks = $db -> Execute("SELECT * FROM potions WHERE status='K' AND owner=".$player -> id);
    $arrid = array();
    $arrname = array();
    $arramount = array();
    $i = 0;
    while (!$miks -> EOF) 
    {
        $arrid[$i] = $miks -> fields['id'];
        $arrname[$i] = $miks -> fields['name'];
        $arramount[$i] = $miks -> fields['amount'];
        $miks -> MoveNext();
        $i = $i + 1;
    }
    $miks -> Close();
    $smarty -> assign( array("Itemid" => $arrid,
        "Name" => $arrname, 
        "Amount" => $arramount,
        "Additem" => ADD_ITEM,
        "Potion" => POTION,
        "Amount2" => AMOUNT2,
        "Aadd" => A_ADD,
        "Tamount2" => T_AMOUNT2));
    if (isset ($_GET['step2']) && $_GET['step2'] == 'add') 
    {
        integercheck($_POST['amount']);
	checkvalue($_POST['przedmiot']);
	checkvalue($_POST['amount']);
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
        $smarty -> assign ("Message", YOU_ADD.$_POST['amount'].T_AMOUNT."<b>".$przed -> fields['name'].TO_WARE);
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
* Assign variables to template and display page
*/
$smarty -> assign(array("Step" =>$_GET['step'], 
                        "Step2" => $_GET['step2'], 
                        "Give" => $_GET['daj'], 
                        "Step3" => $_GET['step3'],
                        "Amain" => A_MAIN,
                        "Adonate" => A_DONATE,
                        "Amembers" => A_MEMBERS,
                        "Apotions" => A_POTIONS,
                        "Aminerals" => A_MINERALS,
                        "Aherbs" => A_HERBS,
                        "Aleft" => A_LEFT,
                        "Aleader" => A_LEADER,
                        "Aforums" => A_FORUMS,
                        "Aarmor" => A_ARMOR,
                        "Aastral" => A_ASTRAL));
$smarty -> display('tribeware.tpl');

require_once("includes/foot.php");
?>
