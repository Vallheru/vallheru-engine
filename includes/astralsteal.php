<?php
/**
 *   File functions:
 *   Functions to steal astral components
 *
 *   @name                 : astralsteal.php                            
 *   @copyright            : (C) 2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 10.05.2012
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

/**
* Get the localization for game
*/
require_once("languages/".$lang."/astralsteal.php");

/**
 * Function to steal astral components
 */
function astralsteal($intVictim, $strLocation, $intOwner = 0, $intId = 0)
{
    global $db;
    global $player;
    global $newdate;

    /**
     * Add bonus from rings
     */
    $arrEquip = $player -> equipment();
    $player->curstats($arrEquip);
    $player->curskills(array('thievery'));

    $intStats = ($player->agility + $player->inteli + $player->thievery);
    /**
     * Add bonus from tools
     */
    if ($arrEquip[12][0])
      {
	$intStats += (($arrEquip[12][2] / 100) * $intStats);
      }

    /**
     * Check for succesful steal
     */
    $intRoll = rand(1, ($player -> level * 100));
    $intChance = $intStats - $intRoll;
    if ($strLocation == 'R')
    {
        $intChance =  $intStats - ($intStats * 0.7) - $intRoll;
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
        checkexp($player -> exp, $intExpgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', 0.01);
        $db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `astralcrime`='N' WHERE `id`=".$player -> id);
        $db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.", '".VERDICT."', ".$intDays.", ".$intBail.", ".$strDate.")");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".L_REASON.": ".$intBail.".', ".$strDate.", 'T')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$intVictim.",'".L_CACHED."<b><a href=\"view.php?view=".$player -> id."\">".$player -> user."</a></b>".L_CACHED2.'<b>'.$player -> id.'</b>'.L_CACHED3."',".$strDate.", 'T')");
	if ($arrEquip[12][0])
	  {
	    $db->Execute("DELETE FROM `equipment` WHERE `id`=".$arrEquip[12][0]);
	  }
	$objTool = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
	if ($objTool->fields['id'])
	  {
	    $intRoll = rand(1, 100);
	    if ($intRoll < 50)
	      {
		$db->Execute("DELETE FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
	      }
	  }
	$objTool->Close();
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
	$fltThief = ($player->level / 100);
        checkexp($player -> exp, $intExpgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
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
        $intKey = array_search($objAmount->fields['type']{0}, $arrNames);
	$intKey2 = (int)substr($objAmount->fields['type'], 1);
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
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$intVictim.",'".ASTRAL_GONE.$strType.$strCompname."</b>."."',".$strDate.", 'T')");
        }
            else
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$intOwner.",'".ASTRAL_GONE.$strType.$strCompname."</b>."."',".$strDate.", 'T')");
        }
	if ($arrEquip[12][0])
	  {
	    $arrEquip[12][6] --;
	    if ($arrEquip[12][6] <= 0)
	      {
		$db->Execute("DELETE FROM `equipment` WHERE `id`=".$arrEquip[12][0]);
	      }
	    else
	      {
		$db->Execute("UPDATE `equipment` SET `wt`=`wt`-1 WHERE `id`=".$arrEquip[12][0]);
	      }
	  }
        error(SUCCESFULL.$strType.$strCompname."</b>. Zdobyłeś ".$fltThief." w umiejętności Złodziejstwo.");
    }
}
?>
