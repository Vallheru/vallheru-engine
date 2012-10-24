<?php
/**
 *   File functions:
 *   Quest in labirynth
 *
 *   @name                 : quest6.php                            
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
require_once("languages/".$lang."/quest6.php");

$objAction = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=6");
$objQuest = new Quests('grid.php', 6, $objAction -> fields['action']);

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

if (isset($_POST['box1']) && $_POST['box1'] == 1) 
{   
    $objQuest -> Show('1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => "", "Start" => ''));    
}

if ($objAction -> fields['action'] == '1') 
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
    if ($player -> hp < 21)
      {
	if ($player->antidote != 'R')
	  {
	    $db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	  }
	else
	  {
	    $db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	  }
        $objQuest -> Show('hp2');
        $objQuest -> Finish(5, array('inteli'));
      }
        else
    {
        $db -> Execute("UPDATE players SET hp=hp-20 WHERE id=".$player -> id);
        $objQuest -> Show('hp1');
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)"));
    }
}

if (($objAction -> fields['action'] == 'hp1' || $objAction -> fields['action'] == '1.next' || $objAction -> fields['action'] == 'int1') && !isset($_POST['box2']))
{
    $objQuest -> Show('1.next');
    $objQuest -> Box(2);
}

if ((isset($_POST['box2']) && $_POST['box2'] == '4') || (isset($_POST['box4']) && $_POST['box4'] == '3') || (isset($_POST['box7']) && $_POST['box7'] == '2') || $objAction -> fields['action'] == '1.4')
{
    $objQuest -> Show('1.4');
    $objQuest -> Finish(5, array('condition'));
}

if (isset($_POST['box2']) && $_POST['box2'] == '1')
{
    $objQuest -> Show('1.1');
    $db -> Execute("UPDATE players SET fight=8 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.1')
{
    $_SESSION['razy'] = 2;
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
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
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

if ($objAction -> fields['action'] == 'winfight1' || $objAction -> fields['action'] == '1.1.next')
{
    $objQuest -> Show('1.1.next');
    $objQuest -> Box(3);
}

if (((isset($_POST['box3']) && $_POST['box3'] == '3') || (isset($_POST['box5']) && $_POST['box5'] == '2') || $objAction -> fields['action'] == '1.1.3') && !isset($_POST['box4']))
{
    $objQuest -> Show('1.1.3');
    $objQuest -> Box(4);
}

if (isset($_POST['box3']) && $_POST['box3'] == '1')
{
    $objQuest -> Show('1.1.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ""));
}

if ($objAction -> fields['action'] == '1.1.1') 
{
    $intChance = ($player->inteli + rand(1,100));
    if ($intChance < 100) 
      {
	if ($player->antidote != 'R')
	  {
	    $db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	  }
	else
	  {
	    $db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	  }
        $objQuest -> Show('int4');
        $objQuest -> Finish(5, array('inteli'));
      } 
        else 
    {
        $objQuest -> Show('int3');
        $objQuest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    }
}

if ($objAction -> fields['action'] == 'int3')
{
    $objQuest -> Show('1.1.1.next');
    $db -> Execute("UPDATE players SET temp=3 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_OPEN."</a>)"));
}

if ($objAction -> fields['action'] == '1.1.1.next' || $objAction -> fields['action'] == 'agi2')
{
    $objAmount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    $intChance = ($player->stats['agility'][2] + rand(1,100));
    if ($intChance < 100 && $objAmount -> fields['temp'] <= 0) 
    {
        $objQuest ->Show('agi3');
        $objQuest -> Box(5);
    }
    if ($intChance >= 100) 
    {
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        if ($objAmount -> fields['temp'] > 0)
        {
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM."',20,'W',10000,0,200,20,200,1,'N',0,20,'N')");
        }
        $objQuest ->Show('agi1');
        $objQuest -> Gainexp(10, array('agility'));
        $objQuest -> Box(5);
    }
    if ($intChance < 100 && $objAmount -> fields['temp'] > 0) {
        $objQuest ->Show('agi2');
        $db -> Execute("UPDATE players SET temp=temp-1 WHERE id=".$player -> id);
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_AGAIN."</a>)", "Box" => ""));
    }
    $objAmount -> Close();
}

if ($objAction -> fields['action'] == 'agi1' && !isset($_POST['box5']))
{
    $objQuest -> Show('agi1');
    $objQuest -> Box(5);
}

if ($objAction -> fields['action'] == 'agi3' && !isset($_POST['box5']))
{
    $objQuest -> Show('agi3');
    $objQuest -> Box(5);
}

if ((isset($_POST['box3']) && $_POST['box3'] == '2') || (isset($_POST['box5']) && $_POST['box5'] == '1'))
{
    $objQuest -> Show('1.1.2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ""));
}

if ($objAction -> fields['action'] == '1.1.2')
{
    $intChance = ($player->stats['inteli'][2] + rand(1,100));
    if ($intChance < 100) 
    {
        $objQuest -> Show('int6');
        $objQuest -> Box(4);
    } 
        else 
    {
        $objQuest -> Show('int5');
        $objQuest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    }
}

if ($objAction -> fields['action'] == 'int6')
{
    $objQuest -> Show('int6');
    $objQuest -> Box(4);
}

if ($objAction -> fields['action'] == 'int5')
{
    $db -> Execute("UPDATE players SET credits=credits+200 WHERE id=".$player -> id);
    $objQuest -> Show('1.1.2.next');
    $objQuest -> Box(4);
}

if ($objAction -> fields['action'] == '1.1.2.next')
{
    $objQuest -> Show('1.1.2.next');
    $objQuest -> Box(4);
}

if ((isset($_POST['box2']) && $_POST['box2'] == '2') || (isset($_POST['box4']) && $_POST['box4'] == '1'))
{
    $objQuest -> Show('1.2');
    $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2') 
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('1.2','answer1','Y');
    $objAmount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($intChance != 1 && $objAmount -> fields['temp'] <= 0) {
        $smarty -> assign(array("Link" => '', "Box" => '', "Answer" => ''));
        $objQuest -> Show('answer2');
        $objQuest -> Box(7);
    }
    $objAmount -> Close();
    if ($intChance == 1) {
        $smarty -> assign(array("Link" => '', "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $objQuest -> Show('answer3');
        $objQuest -> Box(6);
        $objQuest -> Gainexp(10, array('inteli'));
    }
}

if ($objAction -> fields['action'] == 'answer2')
{
    $objQuest -> Show('answer2');
    $objQuest -> Box(7);
}

if ($objAction -> fields['action'] == 'answer3')
{
    $objQuest -> Show('answer3');
    $objQuest -> Box(6);
}

if (((isset($_POST['box6']) && $_POST['box6'] == '4') || (isset($_POST['box8']) && $_POST['box8'] == '3') || (isset($_POST['box9']) && $_POST['box9'] == '2') || $objAction -> fields['action'] == '1.2.4') && !isset($_POST['box7']))
{
    $objQuest -> Show('1.2.4');
    $objQuest -> Box(7);
}

if ((isset($_POST['box6']) && $_POST['box6'] == '1') || $objAction -> fields['action'] == '1.2.1')
{
    $objQuest -> Show('1.2.1');
    $objQuest -> Box(8);
}

if ((isset($_POST['box6']) && $_POST['box6'] == '2') || (isset($_POST['box8']) && $_POST['box8'] == '1'))
{
    $objQuest -> Show('1.2.2');
    $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2.2') 
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('1.2.2','answer4','Y');
    $objAmount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($intChance != 1 && $objAmount -> fields['temp'] <= 0) {
        $smarty -> assign(array("Link" => '', "Box" => '', "Answer" => ''));
        $objQuest -> Show('answer5');
        $objQuest -> Box(9);
    }
    $objAmount -> Close();
    if ($intChance == 1) {
        $smarty -> assign(array("Link" => '', "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $db -> Execute("UPDATE players SET maps=maps+1 WHERE id=".$player -> id);
        $objQuest -> Show('answer6');
        $objQuest -> Finish(50, array('inteli'));
    }
}

if ((isset($_POST['box6']) && $_POST['box6'] == '3') || (isset($_POST['box8']) && $_POST['box8'] == '2') || (isset($_POST['box9']) && $_POST['box9'] == '1') || $objAction -> fields['action'] == '1.2.3')
{
    $objQuest -> Show('1.2.3');
    $objQuest -> Box(10);
}

if ((isset($_POST['box10']) && $_POST['box10'] == '3') || (isset($_POST['box12']) && $_POST['box12'] == '2') || ($objAction -> fields['action'] == '1.2.3.3' && !isset($_POST['box7'])))
{
    $objQuest -> Show('1.2.3.3');
    $objQuest -> Box(7);
}

if ((isset($_POST['box10']) && $_POST['box10'] == '1') || $objAction -> fields['action'] == '1.2.3.1')
{
    $objQuest -> Show('1.2.3.1');
    $objQuest -> Box(11);
}

if ((isset($_POST['box11']) && $_POST['box11'] == '3') || $objAction -> fields['action'] == '1.2.3.1.3')
{
    $objQuest -> Show('1.2.3.1.3');
    $objQuest -> Box(12);
}

if (isset($_POST['box11']) && $_POST['box11'] == '2') 
{   
    $objQuest -> Show('1.2.3.1.2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ""));    
}

if ($objAction -> fields['action'] == '1.2.3.1.2') 
{
    $intChance = ($player->stats['agility'][2] + rand(1,100));
    if ($intChance < 100) 
    {
        $objQuest -> Show('agi5');
        $db -> Execute("UPDATE players SET fight=3 WHERE id=".$player -> id);
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
    } 
        else 
    {
        $objQuest -> Show('agi4');
        $objQuest -> Gainexp(10, array('agility'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    }
}

if (isset($_POST['box11']) && $_POST['box11'] == '1')
{
    $objQuest -> Show('1.2.3.1.1');
    $db -> Execute("UPDATE players SET fight=3 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == 'agi4')
{
    $db -> Execute("UPDATE players SET platinum=platinum+20 WHERE id=".$player -> id);
    $objQuest -> Show('1.2.3.1.2.next');
    $objQuest -> Box(12);
}

if ($objAction -> fields['action'] == '1.2.3.1.2.next' && !isset($_POST['box12']))
{
    $objQuest -> Show('1.2.3.1.2.next');
    $objQuest -> Box(12);
}

if (($objAction -> fields['action'] == '1.2.3.1.1.next' || $objAction -> fields['action'] == 'winfight2') && !isset($_POST['box12']))
{
    $objQuest -> Show('1.2.3.1.1.next');
    $objQuest -> Box(12);
}

if ($objAction -> fields['action'] == 'agi5' || $objAction -> fields['action'] == '1.2.3.1.1')
{
    $_SESSION['razy'] = 3;
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
            $db -> Execute("UPDATE players SET platinum=platinum+20 WHERE id=".$player -> id);
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

if ((isset($_POST['box10']) && $_POST['box10'] == '2') || (isset($_POST['box12']) && $_POST['box12'] == '1'))
{
    $objQuest -> Show('1.2.3.2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2.3.2')
{
    $intChance = ($player->stats['inteli'][2] + rand(1,100));
    if ($intChance < 100) 
    {
        if ($player -> hp > 20)
        {
            $db -> Execute("UPDATE players SET hp=hp-20 WHERE id=".$player -> id);
            $objQuest -> Show('hp3');
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
        }
	else
	  {
	    if ($player->antidote != 'R')
	      {
		$db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	      }
	    else
	      {
		$db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	      }
            $objQuest -> Show('hp4');
            $objQuest -> Finish(5, array('inteli'));
        }
    } 
        else 
    {
        $objQuest -> Show('int7');
        $objQuest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    }
}

if (($objAction -> fields['action'] == 'int7' || $objAction -> fields['action'] == 'hp3' || $objAction -> fields['action'] == '1.2.3.2.next') && !isset($_POST['box13']))
{
    $objQuest -> Show('1.2.3.2.next');
    $objQuest -> Box(13);
}

if (isset($_POST['box13']) && $_POST['box13'] == '2')
{
    $objQuest -> Show('1.2.3.2.2');
    $objQuest -> Finish(10, array('condition'));
}

if (((isset($_POST['box13']) && $_POST['box13'] == '1') || $objAction -> fields['action'] == '1.2.3.2.1') && !isset($_POST['box7']))
{
    $objQuest -> Show('1.2.3.2.1');
    $objQuest -> Box(7);
}

if (((isset($_POST['box2']) && $_POST['box2'] == '3') || (isset($_POST['box4']) && $_POST['box4'] == '2') || (isset($_POST['box7']) && $_POST['box7'] == '1') || $objAction -> fields['action'] == '1.3') && !isset($_POST['box14']))
{
    $objQuest -> Show('1.3');
    $objQuest -> Box(14);
}

if ((isset($_POST['box14']) && $_POST['box14'] == '1') || ($objAction -> fields['action'] == '1.3.1' && !isset($_POST['box15']) && !isset($_POST['box14'])))
{
    $objQuest -> Show('1.3.1');
    $objQuest -> Box(15);
}

if ((isset($_POST['box14']) && $_POST['box14'] == '3') || (isset($_POST['box15']) && $_POST['box15'] == '2'))
{
    $objQuest -> Show('1.3.3');
    $objQuest -> Finish(10, array('condition'));
}

if ((isset($_POST['box14']) && $_POST['box14'] == '2') || (isset($_POST['box15']) && $_POST['box15'] == '1') || $objAction -> fields['action'] == '1.3.2')
{
    $objQuest -> Show('1.3.2');
    $objQuest -> Box(16);
}

if ((isset($_POST['box16']) && $_POST['box16'] == '3') || (isset($_POST['box17']) && $_POST['box17'] == '2'))
{
    $objQuest -> Show('1.3.2.3');
    $objQuest -> Finish(10, array('condition'));
}

if ((isset($_POST['box16']) && $_POST['box16'] == '1') && $objAction -> fields['action'] != '1.3.2.1')
{
    $objAmount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($objAmount -> fields['temp'] == 0)
    {
        $db -> Execute("UPDATE players SET temp=1 WHERE id=".$player -> id);
        $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM2."',5,'W',1000,0,40,5,50,1,'N',0,0,'N')");
    }
    $objAmount -> Close();
    $objQuest -> Show('1.3.2.1');
    $objQuest -> Box(17);
}

if ($objAction -> fields['action'] == '1.3.2.1')
{
    $objQuest -> Show('1.3.2.1');
    $objQuest -> Box(17);
    $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
}

if (((isset($_POST['box16']) && $_POST['box16'] == '2') || (isset($_POST['box17']) && $_POST['box17'] == '1') || $objAction -> fields['action'] == '1.3.2.2') && !isset($_POST['box18']))
{
    $objQuest -> Show('1.3.2.2');
    $objQuest -> Box(18);
}

if (isset($_POST['box18']) && $_POST['box18'] == '2')
{
    $objQuest -> Show('1.3.2.2.2');
    $objQuest -> Finish(20, array('condition'));
}

if (isset($_POST['box18']) && $_POST['box18'] == '1')
{
    $objQuest -> Show('1.3.2.2.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.3.2.2.1')
{
    $intChance = (($player->stats['strength'][2] + $player->stats['agility'][2] + $player->stats['inteli'][2] + $player->stats['wisdom'][2] + $player->stats['condition'][2] + $player->stats['speed'][2]) + rand(1,100));
    if ($intChance < 600)
      {
	if ($player->antidote != 'R')
	  {
	    $db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	  }
	else
	  {
	    $db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	  }
        $objQuest -> Show('atr1');
        $objQuest -> Finish(2000, array('strength', 'agility', 'inteli', 'wisdom', 'condition', 'speed'));
    }
        else
    {
        $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM3."',65,'W',100000,0,650,65,650,1,'N',0,20,'Y')");
        $objQuest -> Show('atr2');
	$objQuest -> Finish(5000, array('strength', 'agility', 'inteli', 'wisdom', 'condition', 'speed'));
    }
}

/**
* Free memory and display page
*/
$objAction -> Close();
$smarty -> display('quest.tpl');
?>
