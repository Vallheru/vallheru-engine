<?php
/**
 *   File functions:
 *   Mines in moutains
 *
 *   @name                 : kopalnia.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 19.08.2011
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

$title = "Kopalnia";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/kopalnia.php");

if ($player -> location != 'Góry') 
{
    error(ERROR);
}

/**
 * Dig for minerals
 */
if (isset($_GET['action']) && $_GET['action'] == 'dig')
{
    if (!isset($_POST['amount'])) 
    {
        error(ERROR);
    }
    checkvalue($_POST['amount']);
    if ($player -> hp <= 0) 
    {
        error(YOU_DEAD." (<a href=\"gory.php\">".BACK."</a>)");
    }
    if ($player -> energy < $_POST['amount']) 
    {
        error(NO_ENERGY." (<a href=\"gory.php\">".BACK."</a>)");
    }

    /**
     * Count bonus to ability
     */
    require_once('includes/abilitybonus.php');
    $fltAbility = abilitybonus('mining');

    $fltGainability = 0;
    $arrMinerals = array(0, 0);
    $arrGold = array(0, 0);
    $strInfo = '';

    for ($i = 1; $i <= $_POST['amount']; $i++)
    {
        $intRoll = rand(1, 10);
        if ($intRoll > 4 && $intRoll < 10)
        {
            $fltGainability = $fltGainability + 0.1;
        }
        if ($intRoll == 5)
        {
            $intAmount = ceil((rand(1,20) * 1/8) * (1 + ($fltAbility + $fltGainability) / 20));
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrMinerals[0] = $arrMinerals[0] + $intAmount;
        }
        if ($intRoll == 6 || $intRoll == 7)
        {
            $intAmount = ceil((rand(1,20) * 1/5) * (1 + ($fltAbility  + $fltGainability) / 20));
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrMinerals[1] = $arrMinerals[1] + $intAmount;
        }
        if ($intRoll == 8)
        {
            $intAmount = ceil((rand(1,20) * 1/3) * (1 + ($fltAbility + $fltGainability) / 20));
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrGold[1] = $arrGold[1] + $intAmount;
        }
        if ($intRoll == 9)
        {
            $intAmount = ceil(rand(50,200) * (1 + ($fltAbility + $fltGainability) / 20));
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrGold[0] = $arrGold[0] + $intAmount;
        }
        if ($intRoll == 10)
        {
            $intRoll2 = rand(1, 100);
            if ($intRoll2 <= 50) 
            {
                $player -> hp = 0;
                $strInfo = M_DEAD;
            } 
                else
            {
                $strInfo = M_LUCK;
            }
            break;
        }
    }

    $intMinsum = array_sum($arrMinerals);
    $intGoldsum = array_sum($arrGold);
    $i --;
    if (!$i)
    {
        $i = 1;
    }
    
    if ($intMinsum)
    {
        $objMinerals = $db -> Execute("SELECT `adamantium`, `crystal`, `owner` FROM `minerals` WHERE `owner`=".$player -> id);
        if ($objMinerals -> fields['owner'])
        {
            $db -> Execute("UPDATE `minerals` SET `crystal`=`crystal`+".$arrMinerals[0].", `adamantium`=`adamantium`+".$arrMinerals[1]." WHERE `owner`=".$player -> id);
        }
        else
        {
            $db -> Execute("INSERT INTO `minerals` (`owner`, `crystal`, `adamantium`) VALUES(".$player -> id.", ".$arrMinerals[0].", ".$arrMinerals[1].")");
        }
        $objMinerals -> Close();
    }
    $strFind = YOU_GO.$i.T_AMOUNT2;
    if ($intGoldsum || $intMinsum)
    {
        $strFind = $strFind.YOU_FIND;
        if ($arrMinerals[0])
        {
            $strFind = $strFind.$arrMinerals[0].T_CRYSTALS;
        }
        if ($arrMinerals[1])
        {
            $strFind = $strFind.$arrMinerals[1].T_ADAMANTIUM;
        }
        if ($arrGold[1])
        {
            $strFind = $strFind.$arrGold[1].T_MITHRIL;
        }
        if ($arrGold[0])
        {
            $strFind = $strFind.T_GOLD.$arrGold[0].T_GOLD2;
        }
        $strFind = $strFind.$fltGainability.T_ABILITY;
    }
    if (!$intGoldsum && !$intMinsum && $strInfo == '')
    {
        $strFind = $strFind.T_NOTHING;
    }
    $strFind = $strFind.$strInfo;
    $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$arrGold[0].", `platinum`=`platinum`+".$arrGold[1].", `hp`=".$player -> hp.", `energy`=`energy`-".$i.", `mining`=`mining`+".$fltGainability." WHERE `id`=".$player -> id);
    $smarty -> assign("Youfind", $strFind);
    $player->energy -= $i;
    if ($player->hp <= 0)
      {
	$smarty -> assign(array("Youdead" => YOU_DEAD2,
				"Backto" => BACK_TO,
				"Stayhere" => STAY_HERE));
      }
}

/**
* Initialization of variables
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'],
                        "Ano" => NO,
                        "Minesinfo" => MINES_INFO,
                        "Asearch" => A_SEARCH,
                        "Tminerals" => T_MINERALS,
                        "Tamount" => T_AMOUNT,
			"Curen" => $player->energy,
                        "Health" => $player -> hp));
$smarty -> display ('kopalnia.tpl');

require_once("includes/foot.php"); 
?>
