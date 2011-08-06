<?php
/**
 *   File functions:
 *   Labyrynth - explore and quests
 *
 *   @name                 : grid.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 24.07.2006
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
// $Id: grid.php 520 2006-07-24 10:19:05Z thindil $

$title = "Labirynt";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/grid.php");

if ($player -> location != 'Altara' && $player -> location != 'Podróż') 
{
    error (ERROR);
}

$smarty -> assign(array("Chance" => '', 
                        "Maps" => ''));

$query = $db -> Execute("SELECT quest FROM questaction WHERE player=".$player -> id." AND action!='end'");
if (!isset($_GET['action']) && !isset($_GET['step']) && empty($query -> fields['quest']))
{
    $smarty -> assign(array("Labinfo" => LAB_INFO,
                            "Ayes" => YES));
}

if (isset ($_GET['action']) && $_GET['action'] == 'explore' && empty($query -> fields['quest'])) 
{
    if ($player -> energy < .3) 
    {
        error (NO_ENERGY);
    }
    if ($player -> hp == 0) 
    {
        error (YOU_DEAD);
    }
    $chance = rand(1,11);
    $db -> Execute("UPDATE players SET energy=energy-.3 WHERE id=".$player -> id);
    if ($chance == 1)
    {
        $smarty -> assign("Action2", ACTION1);
    }
    if ($chance == 2)
    {
        $smarty -> assign("Action2", ACTION2);
    }
    if ($chance == 3) 
    {
        $crgain = rand(1,100);
        $smarty -> assign (array("Goldgain" => $crgain,
                                 "Action2" => ACTION3,
                                 "Action3" => ACTION3_1));
        $db -> Execute("UPDATE players SET credits=credits+".$crgain." WHERE id=".$player -> id);
    }
    if ($chance == 4)
    {
        $smarty -> assign("Action2", ACTION4);
    }
    if ($chance == 5)
    {
        $smarty -> assign("Action2", ACTION5);
    }
    if ($chance == 6) 
    {
        $plgain = rand(1,3);
        $smarty -> assign (array("Mithgain" => $plgain,
                                 "Action2" => ACTION6,
                                 "Action3" => ACTION6_1));
        $db -> Execute("UPDATE players SET platinum=platinum+".$plgain." WHERE id=".$player -> id);
    }
    if ($chance == 7) 
    {
        $smarty -> assign("Action2", ACTION7_1);
        $db -> Execute("UPDATE players SET energy=energy+1 WHERE id=".$player -> id);
    }
    if ($chance == 8)
    {
        $smarty -> assign("Action2", ACTION8);
    }
    if ($chance == 10) 
    {
        $intRoll = rand(1, 5);
        if ($intRoll == 5)
        {
            $aviable = $db -> Execute("SELECT `qid` FROM `quests` WHERE `location`='grid.php' AND `name`='start'");
            $number = $aviable -> RecordCount();
            if ($number > 0) 
            {
                $arramount = array();
                $i = 0;
                while (!$aviable -> EOF) 
                {
                    $query = $db -> Execute("SELECT `id` FROM `questaction` WHERE `quest`=".$aviable -> fields['qid']." AND `player`=".$player -> id);
                    if (empty($query -> fields['id'])) 
                    {
                        $arramount[$i] = $aviable -> fields['qid'];
                        $i = $i + 1;
                    }
                    $query -> Close();
                    $aviable -> MoveNext();
                }
                $i = $i - 1;
                if ($i >= 0) 
                {
                    $roll = rand(0,$i);
                    $name = "quest".$arramount[$roll].".php";
                    require_once('includes/statsbonus.php');
                    $arrCurstats = statbonus();
                    require_once("quests/".$name);
                } 
                    else 
                {
                    $chance = 9;
                }
            }
            $aviable -> Close();
        }
            else
        {
            $chance = 9;
        }
    }
    /**
     * Find astral components
     */
    if ($chance == 11)
    {
        require_once('includes/findastral.php');
        $strResult = findastral(5);
        if ($strResult != false)
        {
            $smarty -> assign("Action2", ACTION11.$strResult);
        }
            else
        {
            $chance = 9;
        }
    }
    if ($chance == 9) 
    {
        $objMaps = $db -> Execute("SELECT value FROM settings WHERE setting='maps'");
        $roll = rand(1,50);
        if ($roll == 50 && $objMaps -> fields['value'] > 0 && $player -> maps < 20 && $player -> rank != 'Bohater') 
        {
            $text = CHEST;
            if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
            {
                $text= $text.CHEST2;
            }
            if ($player -> clas == 'Mag') 
            {
                $text = $text.CHEST3;
            }
            if ($player -> clas == 'Obywatel' || $player -> clas == 'Złodziej' || $player -> clas == '') 
            {
                $text = $text.CHEST4;
            }
            $text = $text.CHEST5;
            $smarty -> assign ("Action2", $text);
            $db -> Execute("UPDATE players SET maps=maps+1 WHERE id=".$player -> id);
            $intMaps = $objMaps -> fields['value'] - 1;
            $db -> Execute("UPDATE settings SET value='".$intMaps."' WHERE setting='maps'");
        }
            else
        {
            $smarty -> assign("Action2", ACTION9);
        }
        $objMaps -> Close();
    }
    $energyleft = ($player -> energy - .3);
    $smarty -> assign(array("Chance" => $chance, 
                            "Energyleft" => $energyleft,
                            "Aexp" => A_EXP,
                            "Tnext" => T_NEXT,
                            "Enpts" => EN_PTS));
}

if ((isset($_GET['step']) && $_GET['step'] == 'quest') || !empty($query -> fields['quest'])) 
{
    $name = "quest".$query -> fields['quest'].".php";
    if ($query -> fields['quest'])
    {
        require_once('includes/statsbonus.php');
        $arrCurstats = statbonus();
        require_once("quests/".$name);   
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
}
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (empty($query -> fields['quest']))
{
    $strQuest = 'N';
}
    else
{
    $strQuest = 'Y';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'], 
                        "Step" => $_GET['step'],
                        "Quest" => $strQuest));
$smarty -> display ('grid.tpl');

$query -> Close();
require_once("includes/foot.php");
?>
