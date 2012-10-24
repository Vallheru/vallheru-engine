<?php
/**
 *   File functions:
 *   Quest in labirynth
 *
 *   @name                 : quest5.php                            
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
// $Id$
 
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
require_once("languages/".$lang."/quest5.php");

$objAction = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=5");
$objQuest = new Quests('grid.php', 5, $objAction -> fields['action']);

/**
* Check if player is on quest
*/
if (isset($_GET['step']) && $_GET['step'] == 'quest' && empty($objAction -> fields['action'])) 
{
    $db -> Execute("UPDATE players SET miejsce='Altara' WHERE id=".$player -> id);
    error(NO_QUEST);
}

/**
* Show text based on players actions
*/
if ((!$objAction -> fields['action'] || $objAction -> fields['action'] == 'start') && !isset($_POST['box1'])) 
{
    $objQuest -> Box('1');
}

if ((isset($_POST['box1']) && $_POST['box1'] == 2) || $objAction -> fields['action'] == '2') 
{
    $smarty -> assign(array("Start" => '', "Box" => ''));
    $objQuest -> Show('2');
    $objQuest -> Resign();
}

if ((isset($_POST['box1']) && $_POST['box1'] == 1) || $objAction -> fields['action'] == '1') 
{
    $smarty -> assign("Start", ''); 
    $objQuest -> Show('1');
    $objQuest -> Box(2);
}

if ((isset($_POST['box2']) && $_POST['box2'] == 3) || $objAction -> fields['action'] == '1.3' || (isset($_POST['box5']) && $_POST['box5'] == 2)) 
{
    $objQuest -> Show('1.3');
    $smarty -> assign( array("Box" => '', "Link" => ''));
    $objQuest -> Finish(5, array('condition'));
}

if (isset($_POST['box2']) && $_POST['box2'] == 1) 
{
    $objQuest -> Show('1.1');
    $smarty -> assign( array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ""));
}

if ($objAction -> fields['action'] == '1.1') 
{
    $intChance = ($player->stats['inteli'][2] + rand(1,100));
    if ($intChance < 100) 
    {
        $objQuest -> Show('int2');
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    } 
        else 
    {
        $objQuest -> Show('int1');
        $objQuest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    }
}

if ($objAction -> fields['action'] == 'int2') 
{
    if ($player -> hp > 10)
    {
        $db -> Execute("UPDATE players SET hp=hp-10 WHERE id=".$player -> id);
        $objQuest -> Show('hp1');
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    }
        else
    {
        $objQuest -> Show('hp2');
	if ($player->antidote != 'R')
	  {
	    $db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	  }
	else
	  {
	    $db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	  }
        $objQuest -> Finish(5, array('inteli'));
        $smarty -> assign(array("Box" => ''));
    }
}

if ($objAction -> fields['action'] == 'int1' || $objAction -> fields['action'] == 'hp1' || $objAction -> fields['action'] == '1.1.next') 
{
    $objQuest -> Show('1.1.next');
    $objQuest -> Box(3);
}

if (((isset($_POST['box3']) && $_POST['box3'] == 2) || $objAction -> fields['action'] == '1.1.next' || $objAction -> fields['action'] == '1.1.2') && !isset($_POST['box5']))
{
    $objQuest -> Show('1.1.2');
    $objQuest -> Box(5);
}

if ((isset($_POST['box3']) && $_POST['box3'] == 1) && $objAction -> fields['action'] != 'int4')
{
    $intChance = ($player->stats['inteli'][2] + rand(1,100));
    if ($intChance < 100) 
    {
        $objQuest -> Show('int3');
        $objQuest -> Box(5);
    } 
        else 
    {
        $objQuest -> Show('int4');
        $objQuest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    }
}

if ($objAction -> fields['action'] == 'int3') 
{
    $objQuest -> Show('int3');
    $objQuest -> Box(5);
}

if ($objAction -> fields['action'] == 'int4') 
{
    $objQuest -> Show('1.1.1.next');
    $objQuest -> Box(4);
}

if ((isset($_POST['box4']) && $_POST['box4'] == 2) || $objAction -> fields['action'] == '1.1.1.2') 
{
    $objQuest -> Show('1.1.1.2');
    $objQuest -> Box(5);
}

if ((isset($_POST['box4']) && $_POST['box4'] == 1) && $objAction -> fields['action'] != '1.1.1.1')
{
  $objQuest -> Gainexp(20, array('condition'));
    $db -> Execute("UPDATE players SET energy=energy-1 WHERE id".$player -> id);
}

if ((isset($_POST['box4']) && $_POST['box4'] == 1) || $objAction -> fields['action'] == '1.1.1.1') 
{
    $objQuest -> Show('1.1.1.1');
    $objQuest -> Box(5);
}

if ((isset($_POST['box2']) && $_POST['box2'] == 2) || $objAction -> fields['action'] == '1.2' || (isset($_POST['box5']) && $_POST['box5'] == 1)) 
{
    $objQuest -> Show('1.2');
    $objQuest -> Box(6);
}

if ((isset($_POST['box6']) && $_POST['box6'] == 4) || $objAction -> fields['action'] == '1.2.4' || (isset($_POST['box8']) && $_POST['box8'] == 3) || (isset($_POST['box10']) && $_POST['box10'] == 2)) 
{
    $objQuest -> Show('1.2.4');
    $smarty -> assign( array("Box" => '', "Link" => ''));
    $objQuest -> Finish(5, array('condition'));
}

if ((isset($_POST['box6']) && $_POST['box6'] == 1) || $objAction -> fields['action'] == '1.2.1') 
{
    $objQuest -> Show('1.2.1');
    $objQuest -> Box(7);
}

if ((isset($_POST['box7']) && $_POST['box7'] == 2) || $objAction -> fields['action'] == '1.2.1.2') 
{
    $objQuest -> Show('1.2.1.2');
    $objQuest -> Box(8);
}

if ((isset($_POST['box7']) && $_POST['box7'] == 1)) 
{
    $objQuest -> Show('1.2.1.1');
    $db -> Execute("UPDATE players SET fight=39 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2.1.1') 
{
    $objQuest -> Battle('grid.php?step=quest');
    $objFight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($objFight -> fields['fight'] == 0) {
        $objHealth = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($objHealth -> fields['hp'] <= 0) {
            $objQuest -> Show('lostfight1');
            $objQuest -> Finish(10, array('condition'));
            $smarty -> assign(array("Box" => ''));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $objQuest -> Show('winfight1');
            $objQuest -> Box(8);
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $objQuest -> Show('escape1');
            $objQuest -> Finish(10, array('speed'));
            $smarty -> assign(array("Box" => ''));
        }
        $objHealth -> Close();
    } 
    $objFight -> Close();
}

if ($objAction -> fields['action'] == 'winfight1') 
{
    $objQuest -> Show('winfight1');
    $objQuest -> Box(8);
}

if ((isset($_POST['box6']) && $_POST['box6'] == 2) || $objAction -> fields['action'] == '1.2.2' || (isset($_POST['box8']) && $_POST['box8'] == 1)) 
{
    $objQuest -> Show('1.2.2');
    $objQuest -> Box(9);
}

if ((isset($_POST['box9']) && $_POST['box9'] == 2) || $objAction -> fields['action'] == '1.2.2.2') 
{
    $objQuest -> Show('1.2.2.2');
    $objQuest -> Box(10);
}

if (isset($_POST['box9']) && $_POST['box9'] == 1) 
{
    $objQuest -> Show('1.2.2.1');
    $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2.2.1') 
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('1.2.2.1','answer1','Y');
    $objAmount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($intChance != 1 && $objAmount -> fields['temp'] <= 0) {
        $smarty -> assign( array("Link" => '', "Box" => '', "Answer" => ''));
        $objQuest ->Show('answer2');
        $objQuest -> Finish(30, array('inteli'));
    }
    $objAmount -> Close();
    if ($intChance == 1) {
        $smarty -> assign(array("Link" => '', "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $db -> Execute("UPDATE players SET maps=maps+1 WHERE id=".$player -> id);
        $objQuest ->Show('answer3');
        $objQuest -> Finish(50, array('inteli'));
    }
}

if ((isset($_POST['box6']) && $_POST['box6'] == 3) || (isset($_POST['box8']) && $_POST['box8'] == 2) || (isset($_POST['box10']) && $_POST['box10'] == 1)) 
{
    $objQuest -> Show('1.2.3');
    $db -> Execute("UPDATE players SET fight=3 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2.3') 
{
    $objQuest -> Battle('grid.php?step=quest');
    $objFight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($objFight -> fields['fight'] == 0) {
        $objHealth = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($objHealth -> fields['hp'] <= 0) {
            $objQuest -> Show('lostfight2');
            $objQuest -> Finish(10, array('condition'));
            $smarty -> assign(array("Box" => ''));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $objQuest -> Show('winfight2');
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $objQuest -> Show('escape2');
            $objQuest -> Finish(10, array('speed'));
            $smarty -> assign(array("Box" => ''));
        }
        $objHealth -> Close();
    } 
    $objFight -> Close();
}

if ($objAction -> fields['action'] == 'winfight2') 
{
    $objQuest -> Show('1.2.3.next');
    $smarty -> assign( array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ""));
}

if ($objAction -> fields['action'] == '1.2.3.next')
{
    $intChance = ($player->stats['inteli'][2] + rand(1,100));
    if ($intChance < 100) 
    {
        $objQuest -> Show('int5');
        $objQuest -> Finish(5, array('inteli'));
        $smarty -> assign(array("Box" => '', "Link" => ''));
    } 
        else 
    {
        $objQuest -> Show('int6');
        $objQuest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    }
}

if ($objAction -> fields['action'] == 'int6' || $objAction -> fields['action'] == '1.2.3.next2')
{
    $objQuest -> Show('1.2.3.next2');
    $objQuest -> Box(11);
}

if ((isset($_POST['box11']) && $_POST['box11'] == 3) || $objAction -> fields['action'] == '1.2.3.3' || (isset($_POST['box12']) && $_POST['box12'] == 2)) 
{
    $objQuest -> Show('1.2.3.3');
    $smarty -> assign( array("Box" => '', "Link" => ''));
    $objQuest -> Finish(5, array('condition'));
}

if ((isset($_POST['box11']) && $_POST['box11'] == 1) && $objAction -> fields['action'] != '1.2.3.1') 
{
    $db -> Execute("UPDATE players SET credits=credits+1000 WHERE id=".$player -> id);
}

if (isset($_POST['box11']) && $_POST['box11'] == 1) 
{
    $objQuest -> Show('1.2.3.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2.3.1' || $objAction -> fields['action'] == '1.2.3.1.next') 
{
    $objQuest -> Show('1.2.3.1.next');
    $objQuest -> Box(12);
}

if ((isset($_POST['box11']) && $_POST['box11'] == 2) || $objAction -> fields['action'] == '1.2.3.2' || (isset($_POST['box12']) && $_POST['box12'] == 1)) 
{
    $smarty -> assign("Box", '');
    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM."',50,'A',5000,5,500,15,500,1,'N',0,0,'N')");
    $objQuest -> Show('1.2.3.2');
    $objQuest -> Finish(10, array('condition'));
}

/**
* Free memory and display page
*/
$objAction -> Close();
$smarty -> display('quest.tpl');
?>
