<?php
/**
 *   File functions:
 *   Fletcher shop - buy arrows and bows
 *
 *   @name                 : bows.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 09.07.2006
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
// $Id: bows.php 449 2006-07-09 19:12:03Z thindil $

$title = "Fleczer"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/bows.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Show list items in shop
*/
if (!isset ($_GET['buy']) && !isset($_GET['step'])) 
{
    $arrname = array();
    $arrpower = array();
    $arrspeed = array();
    $arrdur = array();
    $arrlevel = array();
    $arrcost = array();
    $arrid = array();
    $arrLink = array();
    $i = 0;
    $wep = $db -> Execute("SELECT * FROM bows WHERE lang='".$player -> lang."' ORDER BY minlev ASC");
    while (!$wep -> EOF) 
    {
        $arrname[$i] = $wep -> fields['name'];
        $arrpower[$i] = $wep -> fields['power'];
        $arrspeed[$i] = $wep -> fields['szyb'];
        $arrdur[$i] = $wep -> fields['maxwt'];
        $arrlevel[$i] = $wep -> fields['minlev'];
        $arrcost[$i] = $wep -> fields['cost'];
        $arrid[$i] = $wep -> fields['id'];
        if ($wep -> fields['type'] == 'R')
        {
            $arrLink[$i] = 'arrows=';
        }
            else
        {
            $arrLink[$i] = 'buy=';
        }
        $wep -> MoveNext();
        $i = $i + 1;
    }
    $wep -> Close();
    /**
    * Select name of the best archer
    */
    $objArcher = $db -> Execute("SELECT user FROM players ORDER BY shoot DESC");
    if ($objArcher -> fields['user'] == $player -> user)
    {
        $objArcher = $db -> Execute("SELECT user FROM players WHERE user!='".$player -> user."' ORDER BY shoot DESC");
    }
    $strArcher = $objArcher -> fields['user'];
    $objArcher -> Close();
    $smarty -> assign ( array("Name" => $arrname, 
        "Power" => $arrpower, 
        "Speed" => $arrspeed, 
        "Durability" => $arrdur, 
        "Level" => $arrlevel, 
        "Cost" => $arrcost, 
        "Itemid" => $arrid,
        "Tlink" => $arrLink,
        "Shopinfo" => SHOP_INFO,
        "Archername" => $strArcher,
        "Shopinfo2" => SHOP_INFO2,
        "Iname" => I_NAME,
        "Idur" => I_DUR,
        "Iefect" => I_EFECT,
        "Icost" => I_COST,
        "Ilevel" => I_LEVEL,
        "Ispeed" => I_SPEED,
        "Ioption" => I_OPTION,
        "Abuy" => A_BUY,
        "Asteal" => A_STEAL));
}

/**
* Buy bows
*/
if (isset ($_GET['buy'])) 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['buy'])) 
    {
        error (ERROR);
    }
    $arm = $db -> Execute("SELECT * FROM bows WHERE id=".$_GET['buy']);
    if (!$arm -> fields['id']) 
    {
        error (NO_ITEM);
    }
    if ($arm -> fields['cost'] > $player -> credits) 
    {
        error (NO_MONEY);
    }
    $newcost = ceil($arm -> fields['cost'] * .75);
    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arm -> fields['name']."' AND wt=".$arm -> fields['maxwt']." AND type='B' AND status='U' AND owner=".$player -> id." AND power=".$arm -> fields['power']." AND zr=".$arm -> fields['zr']." AND szyb=".$arm -> fields['szyb']." AND maxwt=".$arm -> fields['maxwt']." AND cost=".$newcost);
    if (!$test -> fields['id']) 
    {
        $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, szyb, twohand, repair) VALUES(".$player -> id.",'".$arm -> fields['name']."',".$arm -> fields['power'].",'B',".$newcost.",".$arm -> fields['zr'].",".$arm -> fields['maxwt'].",".$arm -> fields['minlev'].",".$arm -> fields['maxwt'].",1,".$arm -> fields['szyb'].",'Y', ".$arm -> fields['repair'].")") or error(E_DB);
    } 
        else 
    {
        $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
    }
    $test -> Close();
    $db -> Execute("UPDATE players SET credits=credits-".$arm -> fields['cost']." WHERE id=".$player -> id);
    $smarty -> assign ( array("Name" => $arm -> fields['name'], 
        "Power" => $arm -> fields['power'], 
        "Cost" => $arm -> fields['cost'],
        "Youbuy" => YOU_BUY,
        "Goldcoins" => GOLD_COINS,
        "Damage" => DAMAGE,
        "With" => WITH,
        "Tamount4" => ''));
    $arm -> Close();
}

/**
* Buy arrows
*/
if (isset($_GET['arrows']))
{
    if (!ereg("^[1-9][0-9]*$", $_GET['arrows'])) 
    {
        error (ERROR);
    }
    $objArm = $db -> Execute("SELECT * FROM bows WHERE id=".$_GET['arrows']);
    if (!$objArm -> fields['id']) 
    {
        error (NO_ITEM);
    }
    $intAllcost = $objArm -> fields['cost'];
    $intOnecost = ceil($objArm -> fields['cost'] / 20);
    $smarty -> assign(array("Arrowsname" => $objArm -> fields['name'],
        "Arrowscost" => $intAllcost,
        "Arrowscost2" => $intOnecost,
        "Arrowsid" => $objArm -> fields['id'],
        "Tamount" => T_AMOUNT,
        "Fora" => FOR_A,
        "Tamount2" => T_AMOUNT2,
        "Tamount3" => T_AMOUNT3,
        "Tarrows" => T_ARROWS));
    if (isset($_GET['step']) && $_GET['step'] == 'buy')
    {
        if ((isset($_POST['arrows1']) && $_POST['arrows1'] > 0) && (isset($_POST['arrows2']) && $_POST['arrows2'] > 0))
        {
            error(ERROR);
        }
        if (isset($_POST['arrows1']) && $_POST['arrows1'] > 0)
        {
            if (!ereg("^[1-9][0-9]*$", $_POST['arrows1'])) 
            {
                error (ERROR);
            }
            $intCost = $intAllcost * $_POST['arrows1'];
            if ($intCost > $player -> credits) 
            {
                error (NO_MONEY);
            }
            $intAmount = $objArm -> fields['maxwt'] * $_POST['arrows1'];
        }
        if (isset($_POST['arrows2']) && $_POST['arrows2'] > 0)
        {
            if (!ereg("^[1-9][0-9]*$", $_POST['arrows2'])) 
            {
                error (ERROR);
            }
            $intCost = ceil($intOnecost * $_POST['arrows2']);
            if ($intCost > $player -> credits) 
            {
                error (NO_MONEY);
            }
            $intAmount = $_POST['arrows2'];
        }
        if ((isset($_POST['arrows1']) && $_POST['arrows1'] > 0) || (isset($_POST['arrows2']) && $_POST['arrows2'] > 0))
        {
            $intNewcost = ceil($intAllcost * 0.75);
            $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$objArm -> fields['name']."' AND owner=".$player -> id." AND status='U' AND cost=".$intNewcost);
            if (!$test -> fields['id']) 
            {
                $db -> Execute("INSERT INTO equipment (owner, name, power, cost, wt, szyb, minlev, maxwt, type) VALUES(".$player -> id.",'".$objArm -> fields['name']."',".$objArm -> fields['power'].",".$intNewcost.",".$intAmount.",".$objArm -> fields['szyb'].",".$objArm -> fields['minlev'].",".$intAmount.",'".$objArm -> fields['type']."')") or error(E_DB);
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET wt=wt+".$intAmount.", maxwt=maxwt+".$intAmount." WHERE id=".$test -> fields['id']);
            }
            $test -> Close();
            $db -> Execute("UPDATE players SET credits=credits-".$intCost." WHERE id=".$player -> id);
            $smarty -> assign(array("Name" => $objArm -> fields['name'], 
                "Power" => $objArm -> fields['power'], 
                "Cost" => $intCost,
                "Youbuy" => YOU_BUY,
                "Goldcoins" => GOLD_COINS,
                "Damage" => DAMAGE,
                "With" => WITH,
                "Tamount4" => $intAmount));
        }
            else
        {
            error(ERROR);
        }
    }
    $objArm -> Close();
}

/**
* Steal items from shop
*/
if (isset ($_GET['steal'])) 
{
    require_once("includes/steal.php");
    require_once("includes/checkexp.php");
    steal($_GET['steal']);
}
if ($player -> clas != 'Złodziej') 
{
    $player -> crime = 0;
}

/**
* Initialization of variables
*/
if (!isset($_GET['buy'])) 
{
    $_GET['buy'] = '';
}
if (!isset($_GET['arrows']))
{
    $_GET['arrows'] = '';
}
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables and display page
*/
$smarty -> assign(array("Buy" => $_GET['buy'], 
    "Crime" => $player -> crime,
    "Arrows" => $_GET['arrows'],
    "Step" => $_GET['step'],
	"Location" => $player -> location));
$smarty -> display ('bows.tpl');

require_once("includes/foot.php");
?>
