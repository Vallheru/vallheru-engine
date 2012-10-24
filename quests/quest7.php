<?php
/**
 *   File functions:
 *   Quest in labirynth - concept author Nubia
 *
 *   @name                 : quest7.php                            
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
require_once("languages/".$lang."/quest7.php");

$objAction = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=7");
$objQuest = new Quests('grid.php', 7, $objAction -> fields['action']);

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
    $objQuest -> Box(1);
}

if ((isset($_POST['box1']) && $_POST['box1'] == 2) || $objAction -> fields['action'] == '2') 
{
    $smarty -> assign(array("Start" => '', "Box" => ''));
    $objQuest -> Show('2');
    $objQuest -> Resign();
}

if ((isset($_POST['box1']) && $_POST['box1'] == 1) || $objAction -> fields['action'] == '1') 
{   
    $objQuest -> Show('1');
    $objQuest -> Box(2);
    $smarty -> assign(array("Start" => ''));    
}

if ((isset($_POST['box2']) && $_POST['box2'] == 1) || $objAction -> fields['action'] == 'winfight1') 
{
    $objQuest -> Show('1.1');
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Box" => ''));    
}

if ($objAction -> fields['action'] == '1.1')
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('1.1','answer8','Y');
    $objTest = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($intChance == 1) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $objQuest -> Show('answer2');
        $objQuest -> Gainexp(20, array('inteli'));
    }
        elseif (!$objTest -> fields['temp'])
    {
        $objQuest -> Show('answer1');
        $db -> Execute("UPDATE players SET fight=2 WHERE id=".$player -> id);
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => '', "Answer" => ''));
    }
    $objTest -> Close();
}

if ($objAction -> fields['action'] == 'answer1' || $objAction -> fields['action'] == '1.1.1.1' || $objAction -> fields['action'] == 'answer10' || $objAction -> fields['action'] == '1.2.n.3.n.n' || $objAction -> fields['action'] == 'answer12')
{
    $_SESSION['razy'] = 20;
    $objQuest -> Battle('grid.php?step=quest');
    $objFight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($objFight -> fields['fight'] == 0) 
    {
        $objHealth = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($objHealth -> fields['hp'] <= 0) 
        {
            $objQuest -> Show('lostfight1');
            $objQuest -> Finish(10, array('condition'));
            $smarty -> assign(array("Box" => ''));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            if ($objAction -> fields['action'] == 'answer1')
            {
                $objQuest -> Show('winfight1');
                $db -> Execute("UPDATE players SET temp=1000 WHERE id=".$player -> id);
                $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
            }
            if ($objAction -> fields['action'] == '1.1.1.1')
            {
                $objQuest -> Show('winfight7');
                $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
            }
            if ($objAction -> fields['action'] == 'answer10')
            {
                $objQuest -> Show('winfight8');
                $db -> Execute("UPDATE players SET temp=1000 WHERE id=".$player -> id);
                $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
            }
            if ($objAction -> fields['action'] == '1.2.n.3.n.n')
            {
                $objQuest -> Show('winfight5');
                $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
            }
            if ($objAction -> fields['action'] == 'answer12')
            {
                $objQuest -> Show('winfight9');
                $db -> Execute("UPDATE players SET temp=1000 WHERE id=".$player -> id);
                $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
            }
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

if ($objAction -> fields['action'] == 'answer2' || $objAction -> fields['action'] == 'answer2next')
{
    $objQuest -> Show('answer2next');
    $objQuest -> Box(3);
}

if ((isset($_POST['box3']) && $_POST['box3'] == 1) && $objAction -> fields['action'] != 'answer2next.1') 
{   
    $objQuest -> Show('answer2next.1');
    $objQuest -> Finish(20, array('inteli'));
    $smarty -> assign(array("Box" => ''));    
}

if ((isset($_POST['box3']) && $_POST['box3'] == 2) || $objAction -> fields['action'] == '1.1.1') 
{   
    $objQuest -> Show('1.1.1');
    $objQuest -> Box(4);    
}

if (isset($_POST['box4']) && $_POST['box4'] == 1)
{
    $objQuest -> Show('1.1.1.1');
    $db -> Execute("UPDATE players SET fight=2 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => '', "Answer" => ''));
}

if ($objAction -> fields['action'] == 'winfight7' || $objAction -> fields['action'] == '1.1.1.a')
{
    $objQuest -> Show('1.1.1.a');
    $objQuest -> Box(20);
}

if ((isset($_POST['box4']) && $_POST['box4'] == 2) || (isset($_POST['box20']) && $_POST['box20'] == 1))
{
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    $objQuest -> Show('1.1.1.2');
}

if (((isset($_POST['box4']) && $_POST['box4'] == 3) || (isset($_POST['box20']) && $_POST['box20'] == 2)) && $objAction -> fields['action'] != '1.1.1.3')
{
    $smarty -> assign(array("Link" => '', "Box" => ''));
    $objQuest -> Show('1.1.1.3');
    if ($player->antidote != 'R')
      {
	$db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
      }
    else
      {
	$db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
      }
    $objQuest -> Finish(20, array('condition'));
}

if ((isset($_POST['box4']) && $_POST['box4'] == 4) || (isset($_POST['box20']) && $_POST['box20'] == 3))
{
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
    $objQuest -> Show('1.1.1.4');
}

if ($objAction -> fields['action'] == '1.1.1.2')
{
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_SWIM."</a>)"));
    $objQuest -> Show('1.1.1.2.next');
}

if ($objAction -> fields['action'] == '1.1.1.2.next')
{
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)"));
    $objQuest -> Show('1.1.1.2.next.2');
}

if ($objAction -> fields['action'] == '1.1.1.2.next.2')
{
    $smarty -> assign(array("Link" => ''));
    $objQuest -> Show('1.1.1.2.next.3');
    $objQuest -> Box(5);
}

if (isset($_POST['box5']) && $_POST['box5'] == 1)
{
    $objQuest -> Show('1.1.1.2.next.3.1');
    $db -> Execute("UPDATE players SET fight=35 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.1.1.2.next.3.1' || $objAction -> fields['action'] == '1.1.1.2.next.3.2' || $objAction -> fields['action'] == '1.2.next.a' || $objAction -> fields['action'] == '1.2.next.2.1' || $objAction -> fields['action'] == '17.1')
{
    $_SESSION['razy'] = 1;
    $objQuest -> Battle('grid.php?step=quest');
    $objFight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($objFight -> fields['fight'] == 0) 
    {
        $objHealth = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($objHealth -> fields['hp'] <= 0) 
        {
            if ($objAction -> fields['action'] == '1.2.next.a' || $objAction -> fields['action'] == '1.2.next.2.1')
            {
                $objQuest -> Show('lostfight3');
            }
                elseif ($objAction -> fields['action'] == '17.1')
            {
                $objQuest -> Show('lostfight4');
            }
                else
            {
                $objQuest -> Show('lostfight2');
            }
            $objQuest -> Finish(10, array('condition'));
            $smarty -> assign(array("Box" => ''));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            if ($objAction -> fields['action'] == '1.1.1.2.next.3.1')
            {
                $objQuest -> Show('winfight2');
                $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
            }
            if ($objAction -> fields['action'] == '1.1.1.2.next.3.2')
            {
                $objQuest -> Show('winfight3');
                $objQuest -> Box(6);
            }
            if ($objAction -> fields['action'] == '1.2.next.a' || $objAction -> fields['action'] == '1.2.next.2.1')
            {
                $objQuest -> Show('winfight4');
                $db -> Execute("UPDATE players SET credits=credits+10000 WHERE id=".$player -> id);
                $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
            }
            if ($objAction -> fields['action'] == '17.1')
            {
                $objQuest -> Show('winfight6');
                $smarty -> assign(array("Link" => "", "Box" => ''));
                $objQuest -> Finish(20, array('condition'));
            }
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
    $smarty -> assign(array("Link" => ""));
    $objQuest -> Show('1.1.1.2.n.3.1.n');
    $objQuest -> Finish(20, array('condition'));
}

if (isset($_POST['box5']) && $_POST['box5'] == 2)
{
    $objQuest -> Show('1.1.1.2.next.3.2');
    $db -> Execute("UPDATE players SET fight=46 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == 'winfight3')
{
    $objQuest -> Show('winfight3');
    $objQuest -> Box(6);
}

if ((isset($_POST['box6']) && $_POST['box6'] == 1) && $objAction -> fields['action'] != '1.1.1.2.next.3.2.1')
{
    $smarty -> assign("Box", '');
    $objQuest -> Show('1.1.1.2.next.3.2.1');
    $objQuest -> Finish(20, array('inteli'));
}

if ((isset($_POST['box6']) && $_POST['box6'] == 2) && $objAction -> fields['action'] != '1.1.1.2.next.3.2.2')
{
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)"));
    $db -> Execute("UPDATE players SET credits=credits+10000 WHERE id=".$player -> id);
    $objQuest -> Show('1.1.1.2.next.3.2.2');
}

if ($objAction -> fields['action'] == '1.1.1.2.next.3.2.2')
{
    $objQuest -> Show('1.1.1.2.n.3.1.2.n');
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.1.1.2.n.3.1.2.n' || $objAction -> fields['action'] == 'winfight8')
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('1.1.1.2.n.3.1.2.n','answer9','Y');
    $objTest = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($intChance == 1) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $objQuest -> Show('answer3');
        $objQuest -> Box(7);
        $objQuest -> Gainexp(20, array('inteli'));
    }
        elseif (!$objTest -> fields['temp'])
    {
        $objQuest -> Show('answer10');
        $db -> Execute("UPDATE players SET fight=2 WHERE id=".$player -> id);
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => '', "Answer" => ''));
    }
    $objTest -> Close();
}

if ($objAction -> fields['action'] == 'answer3')
{
    $objQuest -> Show('answer3');
    $objQuest -> Box(7);
}

if (isset($_POST['box7']) && $_POST['box7'] == 1)
{
    $objQuest -> Show('1.1.1.2.n.3.1.2.n.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ((isset($_POST['box7']) && $_POST['box7'] == 2) || ($objAction -> fields['action'] == '1.1.1.2.n.3.1.2.n.1'))
{
    $objQuest -> Show('1.1.1.2.n.3.1.2.n.2');
    $smarty -> assign(array("Link" => "", "Box" => ''));
    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM."',1,'T',1,0,1,1,1,1,'N',0,0,'N')");
    $objQuest -> Finish(30, array('condition'));
}

if ((isset($_POST['box2']) && $_POST['box2'] == 2) || ($objAction -> fields['action'] == '1.1.1.4'))
{
    $objQuest -> Show('1.2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2' || $objAction -> fields['action'] == '1.2.next')
{
    $objQuest -> Show('1.2.next');
    $objQuest -> Box(8);
}

if (isset($_POST['box8']) && $_POST['box8'] == 1)
{
    $objQuest -> Show('1.2.next.a');
    $db -> Execute("UPDATE players SET fight=10 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == 'winfight4')
{
    $objQuest -> Show('1.2.next.1');
    $objQuest -> Finish(20, array('condition'));
}

if ((isset($_POST['box8']) && $_POST['box8'] == 2) || $objAction -> fields['action'] == '1.2.next.2')
{
    $objQuest -> Show('1.2.next.2');
    $objQuest -> Box(9);
}

if (isset($_POST['box9']) && $_POST['box9'] == 1)
{
    $objQuest -> Show('1.2.next.2.1');
    $db -> Execute("UPDATE players SET fight=10 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ((isset($_POST['box8']) && $_POST['box8'] == 3) || $objAction -> fields['action'] == '1.2.next.3')
{
    $objQuest -> Show('1.2.next.3');
    $objQuest -> Box(10);
}

if (isset($_POST['box10']) && $_POST['box10'] == 1)
{
    $objQuest -> Show('1.2.next.3.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if (isset($_POST['box10']) && $_POST['box10'] == 2)
{
    $objQuest -> Show('1.2.next.3.2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.2.next.3.1' || $objAction -> fields['action'] == '1.2.next.3.2')
{
    $objQuest -> Show('1.2.n.3.n');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)"));
}

if ($objAction -> fields['action'] == '1.2.n.3.n')
{
    $objQuest -> Show('1.2.n.3.n.n');
    $db -> Execute("UPDATE players SET fight=2 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)"));
}

if ((isset($_POST['box9']) && $_POST['box9'] == 2) && $objAction -> fields['action'] != '1.2.next.2.2')
{
    $objQuest -> Show('1.2.next.2.2');
    if ($player->antidote != 'R')
      {
	$db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
      }
    else
      {
	$db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
      }
    $smarty -> assign(array("Link" => "", "Box" => ''));
    $objQuest -> Finish(20, array('condition'));
}

if ($objAction -> fields['action'] == 'winfight5' || $objAction -> fields['action'] == '1.2.n.3.n.n.n')
{
    $objQuest -> Show('1.2.n.3.n.n.n');
    $objQuest -> Box(11);
    $smarty -> assign("Link", '');
}

if (isset($_POST['box11']) && $_POST['box11'] == 1)
{
    $objQuest -> Show('1.2.n.3.n.n.n.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));   
}

if ($objAction -> fields['action'] == '1.2.n.3.n.n.n.1')
{
    $objQuest -> Show('1.2.n.3.n.n.n.1.n');
    $smarty -> assign(array("Link" => "", "Box" => ''));
    $objQuest -> Finish(20, array('condition'));
}

if (isset($_POST['box11']) && $_POST['box11'] == 2)
{
    $objQuest -> Show('1.2.n.3.n.n.n.2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));   
}

if ($objAction -> fields['action'] == '1.2.n.3.n.n.n.2') 
{
    $objQuest -> Show('1.2.n.3.n.n.n.2.n');
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Box" => ''));    
}

if ($objAction -> fields['action'] == '1.2.n.3.n.n.n.2.n')
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('1.2.n.3.n.n.n.2.n','','N');
    if ($intChance == 1) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $objQuest -> Show('answer6');
        $objQuest -> Gainexp(20, array('inteli'));
    }
        else
    {
        $objQuest -> Show('answer4');
        $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Box" => ''));
    }
}

if ($objAction -> fields['action'] == 'answer4')
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('1.2.n.3.n.n.n.2.n','','N');
    if ($intChance == 1) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $objQuest -> Show('answer6');
        $objQuest -> Gainexp(20, array('inteli'));
    }
        else
    {
        $objQuest -> Show('answer5');
	if ($player->antidote != 'R')
	  {
	    $db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	  }
	else
	  {
	    $db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	  }
        $smarty -> assign(array("Link" => "", "Box" => '', "Answer" => ''));
        $objQuest -> Finish(20, array('inteli'));
    }
}

if ($objAction -> fields['action'] == 'answer6')
{
    $objQuest -> Show('answer6.next');
    $objQuest -> Box(12);
}

if ((isset($_POST['box2']) && $_POST['box2'] == 3) || $objAction -> fields['action'] == '1.3')
{
    $objQuest -> Show('1.3');
    $objQuest -> Box(13);
}

if (isset($_POST['box13']) && $_POST['box13'] == 1)
{
    $objQuest -> Show('1.3.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.3.1')
{
    $objQuest -> Show('1.3.1.next');
    if ($player->antidote != 'R')
      {
	$db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
      }
    else
      {
	$db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
      }
    $objQuest -> Finish(10, array('condition'));
}

if ((isset($_POST['box13']) && $_POST['box13'] == 2) || $objAction -> fields['action'] == '1.3.2')
{
    $objQuest -> Show('1.3.2');
    $objQuest -> Box(14);
}

if (((isset($_POST['box14']) && $_POST['box14'] == 1) || $objAction -> fields['action'] == '1.3.2.2') && $objAction -> fields['action'] != '1.3.2.1')
{
    $objQuest -> Show('1.3.2.1');
    $db -> Execute("UPDATE players SET credits=credits+1000 WHERE id=".$player -> id);
    $objQuest -> Gainexp(10, array('condition'));
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ((isset($_POST['box14']) && $_POST['box14'] == 2) && $objAction -> fields['action'] != '1.3.2.2')
{
    $objQuest -> Show('1.3.2.2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.3.2.1')
{
    $objQuest -> Show('1.3.2.1.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.3.2.1.1' || $objAction -> fields['action'] == '1.3.2.1.1.n')
{
    $objQuest -> Show('1.3.2.1.1.n');
    $objQuest -> Box(15);
    $smarty -> assign("Link", '');
}

if ((isset($_POST['box15']) && $_POST['box15'] == 2) && $objAction -> fields['action'] != 'answer2.next.1')
{
    $objQuest -> Show('answer2.next.1');
    $smarty -> assign("Box", '');
    $objQuest -> Finish(20, array('inteli'));
}

if ((isset($_POST['box15']) && $_POST['box15'] == 1) || $objAction -> fields['action'] == '1.3.2.1.1.n.1')
{
    $objQuest -> Show('1.3.2.1.1.n.1');
    $objQuest -> Box(16);
}

if (isset($_POST['box16']) && $_POST['box16'] != 4)
{
    $objQuest -> Show('1.3.2.1.1.n.1.1');
    $smarty -> assign("Box", '');
    $objQuest -> Finish(20, array('condition'));
}

if ((isset($_POST['box16']) && $_POST['box16'] == 4) && $objAction -> fields['action'] != '1.3.2.1.1.n.1.2')
{
    $objQuest -> Show('1.3.2.1.1.n.1.2');
    $objQuest -> Gainexp(10, array('condition'));
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ($objAction -> fields['action'] == '1.3.2.1.1.n.1.2')
{
    $objQuest -> Show('1.3.2.1.1.n.1.2.n');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)"));
}

if ($objAction -> fields['action'] == '1.3.2.1.1.n.1.2.n' || $objAction -> fields['action'] == '1.3.2.1.1.n.1.2.n.n')
{
    $objQuest -> Show('1.3.2.1.1.n.1.2.n.n');
    $objQuest -> Box(17);
}

if (isset($_POST['box17']) && $_POST['box17'] == 1)
{
    $objQuest -> Show('17.1');
    $db -> Execute("UPDATE players SET fight=32 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => ''));
}

if ((isset($_POST['box17']) && $_POST['box17'] == 2) && $objAction -> fields['action'] != '17.2')
{
  $objQuest -> Gainexp(10, array('condition'));
}

if ((isset($_POST['box17']) && $_POST['box17'] == 2) || $objAction -> fields['action'] == '17.2')
{
    $objQuest -> Show('17.2');
    $objQuest -> Box(18);
}

if ((isset($_POST['box18']) && $_POST['box18'] == 1) && $objAction -> fields['action'] != '17.2.1')
{
    $db -> Execute("UPDATE players SET credits=credits+10 WHERE id=".$player -> id);
}

if (isset($_POST['box18']) && $_POST['box18'] == 1)
{
    $objQuest -> Show('17.2.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ((isset($_POST['box18']) && $_POST['box18'] == 2) || $objAction -> fields['action'] == '17.2.1')
{
    $objQuest -> Show('17.2.2');
    $smarty -> assign(array("Link" => "", "Box" => ''));
    $objQuest -> Finish(20, array('condition'));
}

if (isset($_POST['box12']) && $_POST['box12'] == 1)
{
    $objQuest -> Show('12.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}

if ((isset($_POST['box12']) && $_POST['box12'] == 2) && $objAction -> fields['action'] != '12.2')
{
    $objQuest -> Show('12.2');
    $smarty -> assign(array("Link" => "", "Box" => ''));
    $objQuest -> Finish(20, array('condition'));
}

if ($objAction -> fields['action'] == '12.1' || $objAction -> fields['action'] == '12.1.n')
{
    $objQuest -> Show('12.1.n');
    $objQuest -> Box(19);
    $smarty -> assign("Link", '');
}

if ((isset($_POST['box19']) && $_POST['box19'] == '2') && $objAction -> fields['action'] != '12.1.n.2')
{
    $objQuest -> Show('12.1.n.2');
    $smarty -> assign(array("Link" => "", "Box" => ''));
    $objQuest -> Finish(20, array('condition'));
}

if (isset($_POST['box19']) && $_POST['box19'] == 1) 
{
    $objQuest -> Show('12.1.n.1');
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Box" => ''));    
}

if ($objAction -> fields['action'] == '12.1.n.1')
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('12.1.n.1','answer11','Y');
    $objTest = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($intChance == 1) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $objQuest -> Show('answer7');
        $objQuest -> Gainexp(20, array('inteli'));
    }
        elseif (!$objTest -> fields['temp'])
    {
        $objQuest -> Show('answer12');
        $db -> Execute("UPDATE players SET fight=2 WHERE id=".$player -> id);
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", "Box" => '', "Answer" => ''));
    }
    $objTest -> Close();
}

if ($objAction -> fields['action'] == 'answer7')
{
    $objQuest -> Show('answer7.next');
    $db -> Execute("UPDATE players SET credits=credits+5000 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)"));
}

if ($objAction -> fields['action'] == 'answer7')
{
    $objQuest -> Show('answer7.next.n');
    $smarty -> assign("Link", '');
    $objQuest -> Finish(20, array('condition'));
}

/**
* Free memory and display page
*/
$objAction -> Close();
$smarty -> display('quest.tpl');
?>
