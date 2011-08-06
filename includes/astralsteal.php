<?php
/**
 *   File functions:
 *   Functions to steal astral components
 *
 *   @name                 : astralsteal.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 23.11.2006
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
// $Id: astralsteal.php 840 2006-11-24 16:41:26Z thindil $

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/astralsteal.php");

/**
 * Function to steal astral components
 */
function astralsteal($intVictim, $strLocation, $intOwner = 0, $intId = 0)
{
    global $db;
    global $player;
    global $newdate;

    /**
     * Add bonus from bless
     */
    $strBless = FALSE;
    $objBless = $db -> Execute("SELECT `bless`, `blessval` FROM `players` WHERE `id`=".$player -> id);
    if ($objBless -> fields['bless'] == 'inteli')
    {
        $player -> inteli = $player -> inteli + $objBless -> fields['blessval'];
        $strBless = 'inteli';
    }
    if ($objBless -> fields['bless'] == 'agility')
    {
        $player -> agility = $player -> agility + $objBless -> fields['blessval'];
        $strBless = 'agility';
    }
    $objBless -> Close();
    if ($strBless)
    {
        $db -> Execute("UPDATE `players` SET `bless`='', `blessval`=0 WHERE `id`=".$player -> id);
    }

    /**
     * Add bonus from rings
     */
    $arrEquip = $player -> equipment();
    $arrRings = array(R_AGI, R_INT);
    $arrStat = array('agility', 'inteli');
    if ($arrEquip[9][0])
    {
        $arrRingtype = explode(" ", $arrEquip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        if ($intKey != NULL)
        {
            $strStat = $arrStat[$intKey];
            $player -> $strStat = $player -> $strStat + $arrEquip[9][2];
        }
    }
    if ($arrEquip[10][0])
    {
        $arrRingtype = explode(" ", $arrEquip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        if ($intKey != NULL)
        {
            $strStat = $arrStat[$intKey];
            $player -> $strStat = $player -> $strStat + $arrEquip[10][2];
        }
    }

    /**
     * Check for succesful steal
     */
    $intRoll = rand(1, ($player -> level * 100));
    $intChance = ($player -> agility + $player -> inteli) - $intRoll;
    if ($strLocation == 'R')
    {
        $intChars = $player -> agility + $player -> inteli;
        $intChance =  $intChars - ($intChars * 0.9) - $intRoll;
    }
    if ($intChance < 1)
    {
        $blnAstralcrime = false;
    }
        else
    {
        $objVault = $db -> Execute("SELECT `level` FROM `astral_bank` WHERE `owner`=".$intVictim." AND `location`='".$strLocation."'");
        if (isset($objVault -> fields['level']) && $strLocation != 'R')
        {
            $intRoll = rand(1, ($player -> level * 100));
            $intKey = $objVault -> fields['level'] - 1;
            if ($strLocation == 'V')
            {
                $arrBonus = array(0.1, 0.2, 0.4);
            }
                elseif ($strLocation == 'C')
            {
                $arrBonus = array(0.3, 0.5, 0.8);
            }
            $intChars = $player -> agility + $player -> inteli;
            $intChance2 = $intChars - ($intChars * $arrBonus[$intKey]) - $intRoll;
            if ($intChance2 < 1)
            {
                $blnAstralcrime = false;
            }
                else
            {
                $blnAstralcrime = true;
            }
        }
        else
        {
            $blnAstralcrime = true;
        }
        $objVault -> Close();
    }

    require_once('includes/checkexp.php');

    $strDate = $db -> DBDate($newdate);

    /**
     * If player caught
     */
    if (!$blnAstralcrime)
    {
        if ($strLocation == 'V')
        {
            $intBail = 10000 * $player -> level;
            $intDays = 7;
        }
            else
        {
            $intBail = 50000 * $player -> level;
            $intDays = 14;
        }
        $intExpgain = ceil($player -> level / 10);
        checkexp($player -> exp, $intExpgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, '', 0);
        $db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `astralcrime`='N' WHERE `id`=".$player -> id);
        $db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.", '".VERDICT."', ".$intDays.", ".$intBail.", ".$strDate.")");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".L_REASON.": ".$intBail.".', ".$strDate.")");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$intVictim.",'".L_CACHED."<b><a href=\"view.php?view=".$player -> id."\">".$player -> user."</a></b>".L_CACHED2.'<b>'.$player -> id.'</b>'.L_CACHED3."',".$strDate.")");
        error (C_CACHED);
    }
    /**
     * If player steal succesfull
     */
        else
    {
        if ($strLocation == 'V')
        {
            $intExpgain = $player -> level * 10;
        }
            else
        {
            $intExpgain = $player -> level * 50;
        }
        checkexp($player -> exp, $intExpgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, '', 0);
        $db -> Execute("UPDATE `players` SET `astralcrime`='N' WHERE `id`=".$player -> id);
        if ($strLocation != 'R')
        {
            $objCount = $db -> Execute("SELECT count(*) FROM `astral` WHERE `owner`=".$intVictim." AND `location`='".$strLocation."'");
            $intOffset = rand(1, $objCount -> fields['count(*)']) - 1;
            $objCount -> Close();
            $objAmount = $db -> SelectLimit("SELECT `type`, `number`, `amount` FROM `astral` WHERE `owner`=".$intVictim." AND `location`='".$strLocation."'", 1, $intOffset) or die($db -> ErrorMsg());
        }
            else
        {
            $objAmount = $db -> Execute("SELECT `type`, `number`, `amount` FROM `amarket` WHERE `id`=".$intId);
        }
        if (!$objAmount -> fields['amount'])
        {
            error(NO_AMOUNT);
        }
        $arrCompnames = array(array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7),
                                      array(PLAN1, PLAN2, PLAN3, PLAN4, PLAN5),
                                      array(RECIPE1, RECIPE2, RECIPE3, RECIPE4, RECIPE5),
                                      array(COMP1, COMP2, COMP3, COMP4, COMP5, COMP6, COMP7), 
                                      array(CONST1, CONST2, CONST3, CONST4, CONST5), 
                                      array(POTION1, POTION2, POTION3, POTION4, POTION5));
        $arrNames = array('M', 'P', 'R', 'C', 'O', 'T');
        $strName = ereg_replace("[0-9]", "", $objAmount -> fields['type']);
        $intKey = array_search($strName, $arrNames);
        $intKey2 = (int)ereg_replace($arrNames[$intKey], "", $objAmount -> fields['type']);
        $strCompname = $arrCompnames[$intKey][$intKey2];
        if ($intKey < 3)
        {
            $strType = PIECE;
        }
            else
        {
            $strType = COMPONENT;
        }
        $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$objAmount -> fields['type']."' AND `number`=".$objAmount -> fields['number']." AND `location`='V'");
        if (!$objTest -> fields['amount'])
        {
            $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$player -> id.", '".$objAmount -> fields['type']."', ".$objAmount -> fields['number'].", 1, 'V')");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`+1 WHERE `owner`=".$player -> id." AND `type`='".$objAmount -> fields['type']."' AND `number`=".$objAmount -> fields['number']." AND `location`='V'");
        }
        $objTest -> Close();
        if ($objAmount -> fields['amount'] == 1)
        {
            if ($strLocation != 'R')
            {
                $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$intVictim." AND `type`='".$objAmount -> fields['type']."' AND `number`=".$objAmount -> fields['number']." AND `location`='".$strLocation."'");
            }
                else
            {
                $db -> Execute("DELETE FROM `amarket` WHERE `id`=".$intId);
            }
        }
            else
        {
            if ($strLocation != 'R')
            {
                $db -> Execute("UPDATE `astral` SET `amount`=`amount`-1 WHERE `owner`=".$intVictim." AND `type`='".$objAmount -> fields['type']."' AND `number`=".$objAmount -> fields['number']." AND `location`='".$strLocation."'");
            }
                else
            {
                $db -> Execute("UPDATE `amarket` SET `amount`=`amount`-1 WHERE `id`=".$intId);
            }
        }
        $objAmount -> Close();
        if ($strLocation == 'V' || $strLocation == 'R')
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$intVictim.",'".ASTRAL_GONE.$strType.$strCompname."</b>."."',".$strDate.")");
        }
            else
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$intOwner.",'".ASTRAL_GONE.$strType.$strCompname."</b>."."',".$strDate.")");
        }
        error(SUCCESFULL.$strType.$strCompname."</b>.");
    }
}
?>
