<?php
/**
 *   File functions:
 *   Quest in labirynth
 *
 *   @name                 : quest2.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 11.12.2012
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
require_once("languages/".$lang."/quest2.php");

$test = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=2");
$quest = new Quests('grid.php',2,$test -> fields['action']);

if (isset($_GET['step']) && $_GET['step'] == 'quest' && empty($test -> fields['action'])) 
{
    $db -> Execute("UPDATE players SET miejsce='Altara' WHERE id=".$player -> id);
    error(NO_QUEST);
}

if ((!$test -> fields['action'] || $test -> fields['action'] == 'start') && !isset($_POST['box1'])) 
{
    $quest -> Box('1');
}

if ((isset($_POST['box1']) && $_POST['box1'] == 2) || $test -> fields['action'] == '2') 
{
    $smarty -> assign("Start", "");
    $quest -> Show('2');
    $quest -> Resign();
}

if ((isset($_POST['box1']) && $_POST['box1'] == 1) || $test -> fields['action'] == '1') 
{
    $smarty -> assign("Start", ""); 
    $quest -> Show('1');
    $quest -> Box(2);
}

if ((isset($_POST['box2']) && $_POST['box2'] == 4) || $test -> fields['action'] == '1.4' || (isset($_POST['box4']) && $_POST['box4'] == 3) || (isset($_POST['box6']) && $_POST['box6'] == 2)) 
{
    $smarty -> assign("Box","");
    $quest -> Show('1.4');
    $quest -> Finish(10, array('condition'));
}

if (isset($_POST['box2']) && $_POST['box2'] == 1) 
{
    $quest -> Show('1.1');
    $smarty -> assign( array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ""));
}

if ($test -> fields['action'] === '1.1') 
{
    $chance = ($player->stats['inteli'][2] + rand(1,100));
    if ($chance < 100) 
    {
        $quest -> Show('int2');
        $db -> Execute("UPDATE players SET hp=hp-1 WHERE id=".$player -> id);
    } 
        else 
    {
        $quest -> Show('int1');
        $quest -> Gainexp(10, array('inteli'));
    }
    $smarty -> assign( array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)"));       
}

if ($test -> fields['action'] == 'int1' || $test -> fields['action'] == 'int2' || $test -> fields['action'] == '1.1next') 
{
    $quest -> Show('1.1next');
    $quest -> Box(3);
}

if (isset($_POST['box3']) && $_POST['box3'] == '2' && $test -> fields['action'] == '1.1next')
{
    $quest -> Gainexp(10);
}

if (((isset($_POST['box3']) && $_POST['box3'] == 2) || $test -> fields['action'] == '1.1.2') && !isset($_POST['box4'])) 
{
    $quest -> Show('1.1.2');
    $quest -> Box(4);
}

if ((isset($_POST['box3']) && $_POST['box3'] == 1) || $test -> fields['action'] == '1.1.1') 
{
    $quest -> Show('1.1.1');
    $smarty -> assign( array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_DIVE."</a>)", "Box" => ""));
}

if ($test -> fields['action'] === '1.1.1') 
{
    $chance = ($player->stats['condition'][2] + rand(1,100));
    if ($chance > 100) 
    {
        $quest -> Show('con1');
        $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM."',25,'W',20000,0,250,25,250,1,'N',0,10,'N')");
        $quest -> Gainexp(20, array('condtion'));
    } 
        else 
    {
        $quest -> Show('con2');
        $quest -> Gainexp(10, array('condition'));
    }
    $smarty -> assign("Link", "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)");     
}

if (($test -> fields['action'] == 'con1' || $test -> fields['action'] == 'con2' || $test -> fields['action'] == '1.1.1next') && !isset($_POST['box4'])) 
{
    $quest -> Show('1.1.1next');
    $quest -> Box(4);
}

if ((isset($_POST['box2']) && $_POST['box2'] == 2) || $test -> fields['action'] == '1.2' || (isset($_POST['box4']) && $_POST['box4'] == 1)) 
{
    $quest -> Show('1.2');
    $smarty -> assign( array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ""));
}

if ($test -> fields['action'] === '1.2') 
{
    $chance = ($player->stats['inteli'][2] + rand(1,100));
    if ($chance > 100) 
    {
        $quest -> Show('int3');
        $quest -> Box(5);
        $smarty -> assign("Link", "");
    } 
        else 
    {
        $quest -> Show('int4');
        $smarty -> assign("Link", "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)");
    }
}

if ($test -> fields['action'] == 'int3') 
{
    $quest -> Show('int3');
    $quest -> Box(5);
    $smarty -> assign("Link", "");
}

if ($test -> fields['action'] == 'int4') 
{
    $chance = (rand(1,100));
    if ($chance < 51) 
    {
        $quest -> Show('1.2.2');
    } 
        else 
    {
        $quest -> Show('1.2.1');
    }
    if ($player -> hp > 0) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    }
}

if ((isset($_POST['box5']) && $_POST['box5'] == 1) || $test -> fields['action'] == '1.2.1') 
{
    $quest -> Show('1.2.1');
    $player -> hp = $player -> hp - 100;
    if ($player -> hp > 0) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    }
}

if ((isset($_POST['box5']) && $_POST['box5'] == 2) || $test -> fields['action'] == '1.2.2') 
{
    $quest -> Show('1.2.2');
    if (isset($_POST['box5'])) 
    {
      $quest -> Gainexp(20, array('condition'));
    }
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.2.1' && $player -> hp < 1) 
{
    $quest -> Show('hp2');
    $db -> Execute("UPDATE players SET hp=".$player -> hp." WHERE id=".$player -> id);
    $quest -> Finish(0, array());
}

if ($test -> fields['action'] == '1.2.1' && $player -> hp > 0) 
{
    $quest -> Show('hp1');
    $db -> Execute("UPDATE players SET hp=".$player -> hp." WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.2.2' || $test -> fields['action'] == 'hp1') 
{
    $quest -> Show('1.2next');
    $db -> Execute("UPDATE players SET fight=2 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.2next') 
{
    $_SESSION['razy'] = 5;
    $quest -> Battle('grid.php?step=quest');
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($fight -> fields['fight'] == 0) 
    {
        $health = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($health -> fields['hp'] <= 0) 
        {
            $quest -> Show('lostfight1');
            $quest -> Finish(10, array('condition'));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $quest -> Show('winfight1');
            $quest -> Box(6);
            $quest -> Gainexp(20, array('condition'));
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, minlev, amount) VALUES(".$player -> id.",'".ITEM2."',100,'T',40000,60,1)");
            $db -> Execute("UPDATE players SET credits=credits+1000 WHERE id=".$player -> id);
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $quest -> Show('escape');
            $quest -> Finish(10, array('speed'));
        }
        $health -> Close();
    } 
    $fight -> Close();
}

if ($test -> fields['action'] == 'winfight1' && !isset($_POST['box6'])) {
    $quest -> Show('winfight1');
    $quest -> Box(6);
}

if ((isset($_POST['box2']) && $_POST['box2'] == 3) || $test -> fields['action'] == '1.3' || (isset($_POST['box4']) && $_POST['box4'] == 2) || (isset($_POST['box6']) && $_POST['box6'] == 1)) 
{
    $quest -> Show('1.3');
    $quest -> Box(7);
}

if (isset($_POST['box7']) && $_POST['box7'] == 3) 
{
    $quest -> Show('1.4');
    $smarty -> assign("Box", "");
    $quest -> Finish(10);
}

if ((isset($_POST['box7']) && $_POST['box7'] == 1) && $test -> fields['action'] != '1.3.1') 
{
    $chance = ($player->stats['agility'][2] + rand(1,100));
    if ($chance < 100) 
    {
        $quest -> Show('door1');
        $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_AGAIN."</a>)", 
            "Box" => ""));
    } 
        else 
    {
        $quest -> Show('door3');
        if ($test -> fields['action'] != 'door3')
        {
	  $quest -> Gainexp(30, array('agility'));
        }
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_DOOR."</a>)", 
            "Box" => ""));
    }
}

if ($test -> fields['action'] == 'door1') 
{
    $amount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    $chance = ($player->stats['agility'][2] + rand(1,100));
    if ($chance < 100 && $amount -> fields['temp'] <= 0) 
    {
        $quest ->Show('door2');
        $quest -> Finish(10, array('agility'));
    }
    $amount -> Close();
    if ($chance >= 100) 
    {
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $quest ->Show('door3');
        if ($test -> fields['action'] != 'door3')
        {
	  $quest -> Gainexp(30, array('agility'));
        }
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_DOOR."</a>)", 
            "Box" => ""));
    }
    if ($chance < 100 && $amount -> fields['temp'] > 0) 
    {
        $quest ->Show('door1');
        $db -> Execute("UPDATE players SET temp=temp-1 WHERE id=".$player -> id);
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_AGAIN."</a>)", 
            "Box" => ""));
    }
}

if ((isset($_POST['box7']) && $_POST['box7'] == 2) && $test -> fields['action'] != '1.3.1') 
{
    $quest -> Show('door4');
    if ($test -> fields['action'] != 'door4')
    {
      $quest -> Gainexp(30, array('condition'));
    }
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_DOOR."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == 'door3' || $test -> fields['action'] == 'door4' || $test -> fields['action'] == '1.3.1') 
{
   $quest -> Show('1.3.1');
   $quest -> Box(8);
}  

if ((isset($_POST['box8']) && $_POST['box8'] == 1) || $test -> fields['action'] == '1.3.1.2') 
{
    $smarty -> assign("Box", '');
    $quest -> Show('1.3.1.2');
    $quest -> Finish(20, array('condition'));
}

if (isset($_POST['box8']) && $_POST['box8'] == 2) 
{
    $quest -> Show('1.3.1.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.3.1.1') 
{
    $chance = (rand(1,100));
    if ($chance < 51) 
    {
        $quest -> Show('1.3.1.1.1');
        $db -> Execute("UPDATE players SET fight=16 WHERE id=".$player -> id);
        $smarty -> assign("Link", "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)");
    }
        else 
    {
        $quest -> Show('1.3.1.1.2');
        $quest -> Box(9);
    }
}

if ($test -> fields['action'] == '1.3.1.1.2') 
{
    $quest -> Show('1.3.1.1.2');
    $quest -> Box(9);
}

if ($test -> fields['action'] == '1.3.1.1.1') 
{
    $quest -> Battle('grid.php?step=quest');
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($fight -> fields['fight'] == 0) 
    {
        $health = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($health -> fields['hp'] <= 0) 
        {
            $quest -> Show('lostfight2');
            $quest -> Finish(10, array('condition'));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $quest -> Show('winfight2');
            $quest -> Box(9);
            $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $quest -> Show('escape');
            $quest -> Finish(10, array('speed'));
        }
        $health -> Close();
    } 
    $fight -> Close();
}

if (isset($_POST['box9']) && $_POST['box9'] == 1) 
{
    $quest -> Show('winfight2');
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php"));
}

if ($test -> fields['action'] == 'winfight2' && !isset($_POST['box9'])) 
{
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php"));
    $chance = $quest -> Answer('winfight2','chest1','Y');
    $amount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($chance != 1 && $amount -> fields['temp'] <= 0) 
    {
        $smarty -> assign(array("Link" => '', 
            "Box" => '', 
            "Answer" => ""));
        $quest ->Show('chest2');
        $quest -> Finish(30, array('inteli'));
    }
    $amount -> Close();
    if ($chance == 1) 
    {
        $smarty -> assign(array("Link" => '', 
            "Box" => '', 
            "Answer" => ""));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $db -> Execute("UPDATE players SET maps=maps+1 WHERE id=".$player -> id);
        $quest ->Show('chest3');
        $quest -> Finish(50, array('inteli'));
    }
}

if ((isset($_POST['box9']) && $_POST['box9'] == 1 && $test -> fields['action'] != 'winfight2') || $test -> fields['action'] == 'oldman1') 
{
    $smarty -> assign(array("Answer" => "", "Box" => ''));
    $quest -> Show('oldman1');
    if ($player -> deity == 'Illuminati') 
    {
        $db -> Execute("UPDATE players SET pw=pw+100 WHERE id=".$player -> id);
    } 
        else 
    {
        $db -> Execute("UPDATE players SET pw=pw+10 WHERE id=".$player -> id);
    }
    $quest -> Finish(40, array('condition'));
}

if ((isset($_POST['box9']) && $_POST['box9'] == 2 && $test -> fields['action'] != 'winfight2') || $test -> fields['action'] == 'oldman2') 
{
    $smarty -> assign("Box", '');
    $quest -> Show('oldman2');
    $quest -> Finish(30, array('condition'));
}

$test -> Close();

$smarty -> display('quest.tpl');
?>
