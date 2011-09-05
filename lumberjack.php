<?php
/**
 *   File functions:
 *   Chop trees
 *
 *   @name                 : lumberjack.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 05.09.2011
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

$title = "Wyrąb";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/lumberjack.php");

if ($player -> location != 'Las') 
{
    error (ERROR);
}

/**
 * Chop down trees
 */
if (isset ($_GET['action']) && $_GET['action'] == 'chop') 
{
    if (!isset($_POST['amount']))
    {
        error(ERROR);
    }
    checkvalue($_POST['amount']);
    if ($player-> energy < $_POST['amount']) 
    {
        error(NO_ENERGY);
    }
    if ($player -> hp <= 0) 
    {
        error(YOU_DEAD);
    }
    $objLumberjack = $db -> Execute("SELECT `level` FROM `lumberjack` WHERE `owner`=".$player -> id);
    if (!$objLumberjack -> fields['level'])
    {
        error(NO_LICENSE);
    }
    /**
     * Count bonus to ability
     */
    require_once('includes/abilitybonus.php');
    $fltAbility = abilitybonus('lumberjack');
    
    $intAmountgold = 0;
    $arrKey = array(1, 2, 3, 4);
    $arrAmount = array(0, 0, 0, 0);
    $intLosthp = 0;
    $strMessage = '';
    $intAmountability = 0;
    $strInfo = '';
    for ($i = 1; $i <= $_POST['amount']; $i++)
    {
        $intRoll = rand(1,8);
        if ($intRoll == 5 || $intRoll == 6) 
        {
            $intKey = (rand(1, $objLumberjack -> fields['level']) - 1);
            $intAmount = ceil(((rand(1, 20) * 1 / $arrKey[$intKey]) * (1 + $fltAbility / 20)) - $arrKey[$intKey]);
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrAmount[$intKey] = $arrAmount[$intKey] + $intAmount;
            $intAmountability = $intAmountability + 0.1;
        }
        if ($intRoll == 7) 
        {
            $intAmount = rand(1,100);
            $intAmountgold = $intAmountgold + $intAmount;
        }
        if ($intRoll == 8) 
        {
            $intRoll2 = rand(1,100);
            if ($intRoll2 <= 50) 
            {
                $intLosthp = rand(1,100);
                if ($intLosthp > $player -> hp) 
                {
                    $intLosthp = $player -> hp;
                }
                $strInfo = TREE_STOMP.YOU_UNLUCK.$intLosthp.T_HITS;
                $player -> hp = $player -> hp - $intLosthp;
            }
                else
            {
                $strInfo = TREE_STOMP.YOU_LUCK;
            }
            break;
        }
    }
    $objLumberjack -> Close();
    $i --;
    $strMessage = YOU_GO.$i.T_ENERGY2;
    $intTest = array_sum($arrAmount) + $intAmountgold + $intAmountability;
    if (!$intTest)
    {
        $strMessage = $strMessage.NOTHING;
    }
        else
    {
        $strMessage = $strMessage.YOU_FIND;
    }
    if ($arrAmount[0])
    {
        $strMessage = $strMessage.$arrAmount[0].T_PINE;
    }
    if ($arrAmount[1])
    {
        $strMessage = $strMessage.$arrAmount[1].T_HAZEL;
    }
    if ($arrAmount[2])
    {
        $strMessage = $strMessage.$arrAmount[2].T_YEW;
    }
    if ($arrAmount[3])
    {
        $strMessage = $strMessage.$arrAmount[3].T_ELM;
    }
    if ($intAmountgold)
    {
        $strMessage = $strMessage.$intAmountgold.T_GOLD;
    }
    if ($intAmountability)
    {
        $strMessage = $strMessage.$intAmountability.T_ABILITY;
    }
    $strMessage = $strMessage.$strInfo;
    $smarty -> assign("Message", $strMessage);
    $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$i.", `credits`=`credits`+".$intAmountgold.", `hp`=`hp`-".$intLosthp.", `lumberjack`=`lumberjack`+".$intAmountability." WHERE `id`=".$player -> id);
    $objLumber = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$player -> id);
    if (!$objLumber -> fields['owner'])
    {
        $db -> Execute("INSERT INTO `minerals` (`owner`, `pine`, `hazel`, `yew`, `elm`) VALUES(".$player -> id.", ".$arrAmount[0].", ".$arrAmount[1].", ".$arrAmount[2].", ".$arrAmount[3].")") or die($db -> ErrorMsg());
    }
        else
    {
      $db -> Execute("UPDATE `minerals` SET `pine`=`pine`+".$arrAmount[0].", `hazel`=`hazel`+".$arrAmount[1].", `yew`=`yew`+".$arrAmount[2].", `elm`=`elm`+".$arrAmount[3]." WHERE `owner`=".$player -> id);
    }
    $objLumber -> Close();
    $player->energy -= $i;
}

/**
* Initialization of variable
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
    $smarty -> assign("Youwant", YOU_WANT);
}

/**
* Assign variables to template and display page
*/
$smarty -> assign (array("Action" => $_GET['action'],
                         "Aback" => A_BACK,
                         "Health" => $player -> hp,
			 "Achop" => A_CHOP,
			 "Onchop" => ON_CHOP,
			 "Curen" => $player->energy,
			 "Tenergy" => T_ENERGY));
$smarty -> display ('lumberjack.tpl');

require_once("includes/foot.php"); 
?>
