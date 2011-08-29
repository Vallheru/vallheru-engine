<?php
/**
 *   File functions:
 *   Weapons shop
 *
 *   @name                 : weapons.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 29.08.2011
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

$title = "Zbrojmistrz";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/weapons.php");

if ($player -> location != 'Altara') 
{
    error (ERROR);
}

if (!isset ($_GET['buy'])) 
{
    $wep = $db -> Execute("SELECT * FROM equipment WHERE type='W' AND status='S' AND owner=0 ORDER BY cost ASC");
    $arrname = array();
    $arrpower = array();
    $arrspeed = array();
    $arrdur = array();
    $arrlevel = array();
    $arrcost = array();
    $arrid = array();
    $i = 0;
    while (!$wep -> EOF) 
    {
        $arrname[$i] = $wep -> fields['name'];
        $arrpower[$i] = $wep -> fields['power'];
        $arrspeed[$i] = $wep -> fields['szyb'];
        $arrdur[$i] = $wep -> fields['maxwt'];
        $arrlevel[$i] = $wep -> fields['minlev'];
        $arrcost[$i] = $wep -> fields['cost'];
        $arrid[$i] = $wep -> fields['id'];
        $wep -> MoveNext();
        $i = $i + 1;        
    }
    $wep -> Close();
    $smarty -> assign(array("Name" => $arrname, 
        "Power" => $arrpower, 
        "Speed" => $arrspeed, 
        "Durability" => $arrdur, 
        "Level" => $arrlevel, 
        "Cost" => $arrcost, 
        "Itemid" => $arrid,
        "Weaponinfo" => WEAPON_INFO,
        "Iname" => I_NAME,
        "Idur" => I_DUR,
        "Iefect" => I_EFECT,
        "Ispeed" => I_SPEED,
        "Icost" => I_COST,
        "Ioption" => I_OPTION,
        "Abuy" => A_BUY,
        "Asteal" => A_STEAL,
        "Ilevel" => I_LEVEL));
}

if (isset ($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $arm = $db -> Execute("SELECT * FROM equipment WHERE id=".$_GET['buy']);
    if (!$arm -> fields['id']) 
    {
        error (NO_ITEM);
    }
    if ($arm -> fields['status'] != 'S') 
    {
        error (BAD_STATUS);
    }
    if ($arm -> fields['cost'] > $player -> credits) 
    {
        error (NO_MONEY);
    }
    $newcost = ceil($arm -> fields['cost'] * .75);
    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arm -> fields['name']."' AND wt=".$arm -> fields['wt']." AND type='W' AND status='U' AND owner=".$player -> id." AND power=".$arm -> fields['power']." AND zr=".$arm -> fields['zr']." AND szyb=".$arm -> fields['szyb']." AND maxwt=".$arm -> fields['maxwt']." AND poison=".$arm -> fields['poison']." AND cost=".$newcost);
    if (!$test -> fields['id']) 
    {
        $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, repair) VALUES(".$player -> id.",'".$arm -> fields['name']."',".$arm -> fields['power'].",'".$arm -> fields['type']."',".$newcost.",".$arm -> fields['zr'].",".$arm -> fields['wt'].",".$arm -> fields['minlev'].",".$arm -> fields['maxwt'].",1,'".$arm -> fields['magic']."',".$arm -> fields['poison'].",".$arm -> fields['szyb'].",'".$arm -> fields['twohand']."', ".$arm -> fields['repair'].")");
    } 
        else 
    {
        $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
    }
    $test -> Close();
    $smarty -> assign(array("Name" => $arm -> fields['name'], 
        "Power" => $arm -> fields['power'], 
        "Cost" => $arm -> fields['cost'],
        "Youpay" => YOU_PAY,
        "Andbuy" => AND_BUY,
        "Withp" => WITH_P,
        "Topower" => TO_POWER)); 
    $db -> Execute("UPDATE players SET credits=credits-".$arm -> fields['cost']." WHERE id=".$player -> id);
    $arm -> Close();
}

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
* Initialization of variable
*/
if (!isset($_GET['buy'])) 
{
    $_GET['buy'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Buy" => $_GET['buy'], 
    "Crime" => $player -> crime));
$smarty -> display('weapons.tpl');

require_once("includes/foot.php");
?>
