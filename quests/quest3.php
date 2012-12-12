<?php
/**
 *   File functions:
 *   Quest in labirynth
 *
 *   @name                 : quest3.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 12.12.2012
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
require_once("languages/".$lang."/quest3.php");

$test = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=3");
$quest = new Quests('grid.php',3,$test -> fields['action']);

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

if ((isset($_POST['box2']) && $_POST['box2'] == 3) || $test -> fields['action'] == '1.3') 
{
    $smarty -> assign("Box", '');
    $quest -> Show('1.3');
    $quest -> Finish(5, array('condition'));
}

if (isset($_POST['box2']) && $_POST['box2'] == 1) 
{
    $quest -> Show('1.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] === '1.1') 
{
    $chance = ($player->stats['inteli'][2] + rand(1,100));
    if ($chance < 30) 
    {
        $quest -> Show('int1');
        $db -> Execute("UPDATE players SET hp=hp-5 WHERE id=".$player -> id);
    } 
        else 
    {
        $quest -> Show('int2');
        $quest -> Gainexp(10, array('inteli'));
    }
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == 'int1' || $test -> fields['action'] == 'int2' || $test -> fields['action'] == '1.1next') 
{
    $quest -> Show('1.1next');
    $quest -> Box(3);
}

if ((isset($_POST['box3']) && ($_POST['box3'] == 1 || $_POST['box3'] == 2 || $_POST['box3'] == 3 || $_POST['box3'] == 4 || $_POST['box3'] == 6 || $_POST['box3'] == 7 || $_POST['box3'] == 8)) || $test -> fields['action'] == 'plates1') 
{
    $quest -> Show('plates1');
    $quest -> Box(3);
}

if ((isset($_POST['box3']) && $_POST['box3'] == 5) || $test -> fields['action'] == 'plates2') 
{
    $quest -> Show('plates2');
    if ($test -> fields['action'] != 'plates2')
    {
        $db -> Execute("UPDATE players SET credits=credits+2000 WHERE id=".$player -> id);
    }
    $quest -> Box(4);
}

if ((isset($_POST['box4']) && $_POST['box4'] == 1) || $test -> fields['action'] == '1.1.1.2') 
{
    $quest -> Show('1.1.1.2');
    $smarty -> assign("Box", "");
    $quest -> Finish(10, array('condition'));
}

if ((isset($_POST['box2']) && $_POST['box2'] == 2) || (isset($_POST['box4']) && $_POST['box4'] == 2) || $test -> fields['action'] == '1.2') 
{
    $quest -> Show('1.2');
    $quest -> Box(5);
}

if ((isset($_POST['box5']) && $_POST['box5'] == 3) || $test -> fields['action'] == '1.2.3') 
{
    $smarty -> assign("Box", '');
    $quest -> Show('1.2.3');
    $quest -> Finish(10, array('condition'));
}

if (isset($_POST['box5']) && $_POST['box5'] == 2) 
{
    $quest -> Show('1.2.2');
    $db -> Execute("UPDATE players SET fight=14 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
        "Box" => ""));
}

if (isset($_POST['box5']) && $_POST['box5'] == 1) 
{
    $quest -> Show('1.2.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] === '1.2.1') 
{
    $chance = ($player->stats['agility'][2] + rand(1,100));
    if ($chance < 200) 
    {
        $quest -> Show('agi1');
        $db -> Execute("UPDATE players SET fight=14 WHERE id=".$player -> id);
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
            "Box" => ""));
    } 
        else 
    {
        $quest -> Show('agi2');
        $quest -> Gainexp(20, array('agility'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    }
}

if ($test -> fields['action'] == '1.2.2' || $test -> fields['action'] == 'agi1') 
{
    $quest -> Battle('grid.php?step=quest');
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($fight -> fields['fight'] == 0) 
    {
        $health = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($health -> fields['hp'] <= 0) 
        {
            $quest -> Show('lostfight');
            $quest -> Finish(10, array('condition'));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $quest -> Show('winfight');
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
                "Box" => ""));
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

if ($test -> fields['action'] == 'winfight' || $test -> fields['action'] == 'agi2' || $test -> fields['action'] == '1.2.1next') 
{
    $quest -> Show('1.2.1next');
    $quest -> box(6);
}

if ((isset($_POST['box6']) && $_POST['box6'] == 3) || (isset($_POST['box9']) && $_POST['box9'] == 2) || $test -> fields['action'] == '1.2.1.3') 
{
    $smarty -> assign("Box", '');
    $quest -> Show('1.2.1.3');
    $quest -> Finish(10, array('condition'));
}

if (isset($_POST['box6']) && $_POST['box6'] == 1) 
{
    $quest -> Show('1.2.1.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.2.1.1') 
{
    $chance = ($player->inteli + rand(1,100));
    if ($chance < 50) 
    {
        $quest -> Show('int3');
        $quest -> Box(9);
    } 
        else 
    {
        $quest -> Show('int4');
        $quest -> Gainexp(10, array('condition'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    }
}

if ($test -> fields['action'] == 'int3') 
{
    $quest -> Show('int3');
    $quest -> Box(9);
}

if ($test -> fields['action'] == 'int4') 
{
    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM."',25,'W',20000,0,250,25,250,1,'N',0,10,'N')");
    $quest -> Show('1.2.1.1next');
    $quest -> Box(9);
}

if ($test -> fields['action'] == '1.2.1.1next') 
{
    $quest -> Show('1.2.1.1next');
    $quest -> Box(9);
}

if ((isset($_POST['box6']) && $_POST['box6'] == 2) || (isset($_POST['box9']) && $_POST['box9'] == 1)) 
{
    $quest -> Show('1.2.1.2');
    $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
    $smarty -> assign( array("Answer" => "Y", 
        "File" => "grid.php", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.2.1.2') 
{
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php"));
    $chance = $quest -> Answer('1.2.1.2','answer1','Y');
    $amount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($chance != 1 && $amount -> fields['temp'] <= 0) 
    {
        $smarty -> assign(array("Link" => '', 
            "Box" => '', 
            "Answer" => ""));
        $quest ->Show('answer2');
        $quest -> Finish(30, array('inteli'));
    }
    $amount -> Close();
    if ($chance == 1) 
    {
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => "", 
            "Answer" => ""));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $quest ->Show('answer3');
    }
}

if ($test -> fields['action'] == 'answer3' || $test -> fields['action'] == 'answernext') 
{
    $quest -> Show('answernext');
    $quest -> Box(7);
}

if ((isset($_POST['box7']) && $_POST['box7'] == 3) || $test -> fields['action'] == '1.4.3') 
{
    $smarty -> assign("Box", '');
    $quest -> Show('1.4.3');
    $quest -> Finish(10, array('condition'));
}

if ((isset($_POST['box7']) && $_POST['box7'] == 2) || $test -> fields['action'] == '1.4.2') 
{
    $quest -> Show('1.4.2');
    $quest -> Box(8);
}

if ((isset($_POST['box8']) && $_POST['box8'] == 2 && $test -> fields['action'] == '1.4.2') || $test -> fields['action'] == '1.4.2.2') 
{
    $smarty -> assign(array("Link" => '', 
        "Box" => '', 
        "Answer" => ""));
    $quest -> Show('1.4.2.2');
    $quest -> Finish(20, array('condition'));
}

if ((isset($_POST['box8']) && $_POST['box8'] == 1 && $test -> fields['action'] == '1.4.2') || $test -> fields['action'] == '1.4.2.1') 
{
    $smarty -> assign(array("Link" => '', 
        "Box" => '', 
        "Answer" => ""));
    $quest -> Show('1.4.2.1');
    $db -> Execute("UPDATE players SET alchemia=alchemia+2 WHERE id=".$player -> id);
    $db -> Execute("UPDATE players SET energy=energy-2 WHERE id=".$player -> id);
    $quest -> Finish(30, array('condition'));
}

if ((isset($_POST['box7']) && $_POST['box7'] == 1) || $test -> fields['action'] == '1.4.1') 
{
    $quest -> Show('1.4.1');
    $quest -> Box(8);
}

if ((isset($_POST['box8']) && $_POST['box8'] == 2 && $test -> fields['action'] == '1.4.1') || $test -> fields['action'] == '1.4.1.2') 
{
    $smarty -> assign(array("Link" => '', 
        "Box" => '', 
        "Answer" => ""));
    $quest -> Show('1.4.2.2');
    $quest -> Finish(20, array('condition'));
}

if (isset($_POST['box8']) && $_POST['box8'] == 1 && $test -> fields['action'] == '1.4.1') 
{
    $quest -> Show('1.4.1.1');
    $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.4.1.1') 
{
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php"));
    $chance = $quest -> Answer('1.4.1.1','answer4','Y');
    $amount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($chance != 1 && $amount -> fields['temp'] <= 0) 
    {
        $smarty -> assign(array("Link" => '', 
            "Box" => '', 
            "Answer" => ""));
        $quest ->Show('answer5');
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
        $quest ->Show('answer6');
        $quest -> Finish(50, array('inteli'));
    }
}

$test -> Close();

$smarty -> display('quest.tpl');
?>
