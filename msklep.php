<?php
/**
 *   File functions:
 *   Potions shop in city
 *
 *   @name                 : msklep.php                            
 *   @copyright            : (C) 2004,2005,2006,2007 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 24.02.2007
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
// $Id: msklep.php 900 2007-02-24 21:25:14Z thindil $

$title = "Alchemik";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/msklep.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error(ERROR);
}

/**
* Show list of potions in shop
*/
if (!isset($_GET['buy'])) 
{
    $objPotions = $db -> Execute("SELECT * FROM `potions` WHERE `owner`=0 AND `status`='S' AND `lang`='".$player -> lang."' ORDER BY `power` ASC");
    $arrName = array();
    $arrPower = array();
    $arrEfect = array();
    $arrAmount = array();
    $arrItemid = array();
    $arrCost = array();
    $i = 0;
    while (!$objPotions -> EOF) 
    {
        if ($objPotions -> fields['type'] == 'M')
        {
            $arrCost[$i] = ($objPotions -> fields['power'] * 3);
        }
            else
        {
            $arrCost[$i] = ((2 * $objPotions -> fields['power']) * 3);
        }
        $arrName[$i] = $objPotions -> fields['name'];
        $arrPower[$i] = $objPotions -> fields['power'];
        $arrEfect[$i] = $objPotions -> fields['efect'];
        $arrAmount[$i] = $objPotions -> fields['amount'];
        $arrItemid[$i] = $objPotions -> fields['id'];
        $objPotions -> MoveNext();
        $i++;
    }
    $objPotions -> Close();
    $smarty -> assign (array("Pname" => $arrName,
                             "Ppower" => $arrPower,
                             "Pefect" => $arrEfect,
                             "Pamount" => $arrAmount,
                             "Pid" => $arrItemid,
                             "Pcost" => $arrCost,
                             "Npower" => POWER,
                             "Abuy" => A_BUY,
                             "Pwelcome" => WELCOME,
                             "Nname" => N_NAME,
                             "Nefect" => N_EFECT,
                             "Ncost" => N_COST,
                             "Namount" => N_AMOUNT,
                             "Poption" => P_OPTION));
}

if (isset($_GET['buy'])) 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['buy'])) 
    {
        error(ERROR);
    }
    $objName = $db -> Execute("SELECT `name` FROM `potions` WHERE `id`=".$_GET['buy']);
    $smarty -> assign (array("Pid" => $_GET['buy'], 
                             "Name" => $objName -> fields['name'],
                             "Abuy" => A_BUY,
                             "Pamount" => P_AMOUNT));
    $objName -> Close();
    if (isset ($_GET['step']) && $_GET['step'] == 'buy')
    {
        $objPotions = $db -> Execute("SELECT * FROM `potions` WHERE `id`=".$_GET['buy']);
        if (!isset($_POST['amount']) || !ereg("^[1-9][0-9]*$", $_GET['buy']) || !ereg("^[1-9][0-9]*$", $_POST['amount'])) 
        {
            error(ERROR);
        }
        if ($_POST['amount'] > $objPotions -> fields['amount']) 
        {
            error(TOO_MUCH);
        }
        if (!$objPotions -> fields['id']) 
        {
            error(NO_POTION);
        }
        if ($objPotions -> fields['status'] != 'S') 
        {
            error(NO_TRADE);
        }
        if ($objPotions -> fields['type'] == 'M')
        {
            $intCostone = ($objPotions -> fields['power'] * 3);
        }
            else
        {
            $intCostone = ((2 * $objPotions -> fields['power']) * 3);
        }
        $intCost = ($intCostone * $_POST['amount']);
        if ($intCost > $player -> credits) 
        {
            error(NO_MONEY);
        }
        $objTest = $db -> Execute("SELECT `id` FROM `potions` WHERE `name`='".$objPotions -> fields['name']."' AND `owner`=".$player -> id." AND `status`='K'");
        if (!$objTest -> fields['id']) 
        {
            $db -> Execute("INSERT INTO `potions` (`name`, `owner`, `efect`, `type`, `power`, `status`, `amount`) VALUES('".$objPotions -> fields['name']."',".$player -> id.",'".$objPotions -> fields['efect']."','".$objPotions -> fields['type']."',".$objPotions -> fields['power'].",'K',".$_POST['amount'].")") or error(E_DB);
        } 
            else 
        {
            $db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$objTest -> fields['id']);
        }
        $objTest -> Close();
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$intCost." WHERE `id`=".$player -> id);
        $db -> Execute("UPDATE `potions` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$objPotions -> fields['id']);
        error(YOU_PAY." <b>".$intCost."</b> ".P_COINS." ".$_POST['amount']." ".POTIONS." ".$objPotions -> fields['name']."</b>. <a href=\"msklep.php\">".S_BACK."</a>");
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['buy'])) 
{
    $_GET['buy'] = '';
}

/**
* Assign variable to template and display page
*/
$smarty -> assign ("Buy", $_GET['buy']);
$smarty -> display ('msklep.tpl');

require_once("includes/foot.php"); 
?>
