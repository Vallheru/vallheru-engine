<?php
/**
 *   File functions:
 *   Quest in labirynth - concept author ???
 *
 *   @name                 : quest9.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 24.10.2012
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
// 
 
/**
* Assign variables to template
*/
$smarty -> assign(array("Start" => '', 
    "End" => '', 
    "Text" => '', 
    "Box" => '', 
    "Link" => '', 
    "Answer" => ''));

require_once('class/quests_class.php');

/**
* Get the localization for game
*/
require_once("languages/".$lang."/quest9.php");

$objAction = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=9");
$objQuest = new Quests('grid.php', 9, $objAction -> fields['action']);

/**
* Check if player is on quest
*/
if (isset($_GET['step']) && $_GET['step'] == 'quest' && empty($objAction -> fields['action'])) 
{
    $db -> Execute("UPDATE players SET miejsce='Altara' WHERE id=".$player -> id);
    error(NO_QUEST);
}

/**
* Select texts from database based on players actions
*/
if (!$objAction -> fields['action']) 
{
    $smarty -> assign("Link", "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)");
}

if ($objAction -> fields['action'] == 'start')
{
    $objQuest -> Show('next');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
        "Start" => ''));
    $db -> Execute("UPDATE players SET fight=12 WHERE id=".$player -> id);
}

if ($objAction -> fields['action'] == 'next')
{
    $_SESSION['razy'] = 4;
    $objQuest -> Battle('grid.php?step=quest');
    $objFight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($objFight -> fields['fight'] == 0) 
    {
        $objHealth = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($objHealth -> fields['hp'] <= 0) 
        {
            $objQuest -> Show('lostfight');
            $objQuest -> Finish(10, array('condition'));
            $smarty -> assign(array("Box" => ''));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $objQuest -> Show('winfight');
            $objQuest -> Box(1);
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, status, minlev, wt) VALUES(".$player -> id.", '".I_ARROWHEAD."', 41, 'G', 1, 'U', 10, 10)");
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $objQuest -> Show('escape');
            $objQuest -> Finish(10, array('speed'));
            $smarty -> assign(array("Box" => ''));
	    if ($player->antidote != 'R')
	      {
		$db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	      }
	    else
	      {
		$db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	      }
        }
        $objHealth -> Close();
    } 
    $objFight -> Close();
}

if ($objAction -> fields['action'] == 'winfight')
{
    $objQuest -> Show('winfight');
    $objQuest -> Box(1);
}

if (isset($_POST['box1']) && $_POST['box1'] == 3) 
{
    $objQuest -> Show('3');
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php", 
        "Link" => '', 
        "Box" => ''));
}

if ($objAction -> fields['action'] == '3')
{
    $smarty -> assign("Answer", '');
    $intChance = $objQuest -> Answer('3','','N');
    if ($intChance == 1) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => '', 
            "Answer" => ''));
        $db -> Execute("INSERT INTO equipment (owner, name, cost, minlev, type, power, status) VALUES(".$player -> id.", '".I_CAPE."', 1, 30, 'C', 60, 'U')");
        $objQuest -> Show('answer1');
        $objQuest -> Gainexp(20, array('inteli'));
    }
        else
    {
        $objQuest -> Show('answer2');
        $objQuest -> Box(2);
        $smarty -> assign(array("Answer" => "", 
            "Link" => ''));
    }
}

if ($objAction -> fields['action'] == 'answer1' || $objAction -> fields['action'] == 'answer2')
{
    $objQuest -> Show('answer2');
    $objQuest -> Box(2);
    $smarty -> assign(array("Answer" => "", 
        "Link" => ''));
}

if ((isset($_POST['box1']) && $_POST['box1'] == 1) || (isset($_POST['box2']) && $_POST['box2'] == 1))
{
    $objQuest -> Show('1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
        "Box" => ''));
    $db -> Execute("UPDATE players SET fight=26 WHERE id=".$player -> id);
}

if ($objAction -> fields['action'] == '1')
{
    $_SESSION['razy'] = 1;
    $objQuest -> Battle('grid.php?step=quest');
    $objFight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($objFight -> fields['fight'] == 0) 
    {
        $objHealth = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($objHealth -> fields['hp'] <= 0) 
        {
            $objQuest -> Show('lostfight2');
            $objQuest -> Finish(10, array('condition'));
            $smarty -> assign(array("Box" => ''));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $objQuest -> Show('winfight2');
            $smarty -> assign(array("Answer" => "Y", 
                "File" => "grid.php", 
                "Link" => '', 
                "Box" => ''));
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $objQuest -> Show('escape2');
            $objQuest -> Finish(10, array('speed'));
            $smarty -> assign(array("Box" => ''));
	    if ($player->antidote != 'R')
	      {
		$db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	      }
	    else
	      {
		$db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	      }
        }
        $objHealth -> Close();
    } 
    $objFight -> Close();
}

if ($objAction -> fields['action'] == 'winfight2')
{
    $smarty -> assign("Answer", '');
    $intChance = $objQuest -> Answer('winfight2','','N');
    if ($intChance == 1) 
    {
        $smarty -> assign(array("Link" => '', 
            "Box" => '', 
            "Answer" => ''));
        $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.", '".I_WEAPON."', 200, 'W', 1, 0, 400, 40, 400, 1, 'N', 0, 40, 'Y')");
        $objQuest -> Show('answer3');
        $objQuest -> Finish(50, array('inteli'));
    }
        else
    {
        $objQuest -> Show('answer4');
        $objQuest -> Finish(20, array('inteli'));
    }
}

if (((isset($_POST['box1']) && $_POST['box1'] == 2) || (isset($_POST['box2']) && $_POST['box2'] == 2)) && $objAction -> fields['action'] != '2')
{
    $smarty -> assign(array("Link" => '', 
        "Box" => ''));
    $objQuest -> Show('2');
    $objQuest -> Finish(30, array('condition'));
}

/**
* Free memory and display page
*/
$objAction -> Close();
$smarty -> display('quest.tpl');
?>
