<?php
/**
 *   File functions:
 *   Jeweller shop
 *
 *   @name                 : jewellershop.php                            
 *   @copyright            : (C) 2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 23.05.2012
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

$title = "Jubiler";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/jewellershop.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

$strMessage = '';

$objRings = $db -> Execute("SELECT `id`, `name`, `amount` FROM `rings`");
$i = 0;
$arrId = array();
$arrName = array();
$arrAmount = array();
while (!$objRings -> EOF)
{
    $arrId[$i] = $objRings -> fields['id'];
    $arrName[$i] = $objRings -> fields['name'];
    $arrAmount[$i] = $objRings -> fields['amount'];
    $i ++;
    $objRings -> MoveNext();
}
$objRings -> Close();

/**
 * Buy rings
 */
if (isset($_GET['buy']))
{
    $_GET['buy'] = intval($_GET['buy']);
    if ($_GET['buy'] < 0 || $_GET['buy'] > 6)
      {
	error(ERROR);
      }
    if ($player -> credits < 500)
    {
        error(NO_MONEY);
    }
    $intKey = $_GET['buy'] - 1;
    if (!$arrAmount[$intKey])
    {
        error(NO_RING);
    }
    $db -> Execute("UPDATE `rings` SET `amount`=`amount`-1 WHERE `id`=".$_GET['buy']);
    $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND `name`='".$arrName[$intKey]."' AND `status`='U' AND `cost`=50");
    if (!$objTest -> fields['id'])
    {
        $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", '".$arrName[$intKey]."', '1', 'U', 'I', '50', '1', '1')");
    }
        else
    {
        $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest -> fields['id']);
    }
    $objTest -> Close();
    $db -> Execute("UPDATE `players` SET `credits`=`credits`-500 WHERE `id`=".$player -> id);
    $strMessage = YOU_BUY.$arrName[$intKey].FOR_A." <a href=\"jewellershop.php\">".A_REFRESH."</a>";
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
$smarty -> assign(array("Shopinfo" => SHOP_INFO,
                        "Rid" => $arrId,
                        "Rname" => $arrName,
                        "Ramount" => $arrAmount,
                        "Tname" => T_NAME,
                        "Tamount" => T_AMOUNT,
                        "Tbonus" => T_BONUS,
                        "Tcost" => T_COST,
                        "Taction" => T_ACTION,
                        "Abuy" => A_BUY,
                        "Message" => $strMessage));
$smarty -> display ('jewellershop.tpl');

require_once("includes/foot.php");
?>
