<?php
/**
 *   File functions:
 *   Quest in labirynth
 *
 *   @name                 : quest4.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
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
require_once("languages/".$lang."/quest4.php");

$test = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=4");
$quest = new Quests('grid.php',4,$test -> fields['action']);

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

if ((isset($_POST['box2']) && $_POST['box2'] == 1) || $test -> fields['action'] == '1.1') 
{
    $quest -> Show('1.1');
    $quest -> Box(3);
}

if ((isset($_POST['box3']) && $_POST['box3'] == 1) || $test -> fields['action'] == '1.1.1') 
{
    $wep = $db -> Execute("SELECT id FROM equipment WHERE name='".ITEM."' AND owner=".$player -> id." AND cost=200");
    if (empty($wep -> fields['id'])) 
    {
        $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM."',1,'W',200,0,10,1,10,1,'N',0,0,'N')");
    }
    $wep -> Close();
    $quest -> Show('1.1.1');
    $quest -> Box(4);
}

if ((isset($_POST['box3']) && $_POST['box3'] == 2) || $test -> fields['action'] == '1.1.2' || (isset($_POST['box4']) && $_POST['box4'] == 1)) 
{
    $quest -> Show('1.1.2');
    $quest -> Box(5);
}

if ((isset($_POST['box5']) && $_POST['box5'] == 2) || $test -> fields['action'] == '1.1.2.2') 
{
    $quest -> Show('1.1.2.2');
    $quest -> Box(8);
}

if ((isset($_POST['box2']) && $_POST['box2'] == 4) || $test -> fields['action'] == '1.4' || (isset($_POST['box8']) && $_POST['box8'] == 3) || (isset($_POST['box9']) && $_POST['box9'] == 2 && $test -> fields['action'] != 'winfight2') || (isset($_POST['box7']) && $_POST['box7'] == 2)) 
{
    $quest -> Show('1.4');
    $smarty -> assign( array("Box" => "", 
        "Link" => ""));
    $quest -> Finish(5, array('condition'));
}

if ((isset($_POST['box3']) && $_POST['box3'] == 3) || $test -> fields['action'] == '1.1.3' || (isset($_POST['box4']) && $_POST['box4'] == 2)) 
{
    $quest -> Show('1.1.3');
    $smarty -> assign(array("Box" => ""));
    $quest -> Finish(5, array('condition'));
}

if (isset($_POST['box5']) && $_POST['box5'] == 1) 
{
    $quest -> Show('1.1.2.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.1.2.1') 
{
    $chance = ($player->stats['agility'][2] + $player->stats['speed'][2] + rand(1,100));
    if ($chance < 50) 
    {
        $quest -> Show('jump1');
	if ($player->antidote != 'R')
	  {
	    $db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
	  }
	else
	  {
	    $db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
	  }
        $quest -> Finish(10, array('agility', 'speed'));
        $smarty -> assign(array("Box" => ""));
    } 
        else 
    {
        $quest -> Show('jump2');
        $quest -> Gainexp(20, array('agility', 'speed'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    }
}

if ($test -> fields['action'] == 'jump2') 
{
    $chance = ($player->stats['inteli'][2] + rand(1,100));
    if ($chance < 50) 
    {
        $quest -> Show('int1');
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    } 
        else 
    {
        $quest -> Show('int2');
        $quest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    }
}

if ($test -> fields['action'] == 'int1' || $test -> fields['action'] == 'int2' || $test -> fields['action'] == '1.1.2.1next') 
{
    $quest -> Show('1.1.2.1next');
    $quest -> Box(8);
}

if ((isset($_POST['box8']) && $_POST['box8'] == 1) || $test -> fields['action'] == '1.2' || (isset($_POST['box2']) && $_POST['box2'] == 2)) 
{
    $quest -> Show('1.2');
    $quest -> Box(6);
}

if ((isset($_POST['box6']) && $_POST['box6'] == 3) || $test -> fields['action'] == '1.2.3' || (isset($_POST['box9']) && $_POST['box9'] == 2)) 
{
    $quest -> Show('1.2.3');
    $quest -> Box(7);
}

if ((isset($_POST['box6']) && $_POST['box6'] == 1)) 
{
    $quest -> Show('1.2.1');
    $db -> Execute("UPDATE players SET fight=1 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.2.1') 
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
            $smarty -> assign(array("Box" => ""));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $quest -> Show('winfight1');
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)",
                "Box" => ""));
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $quest -> Show('escape1');
            $quest -> Finish(10, array('speed'));
            $smarty -> assign(array("Box" => ""));
        }
        $health -> Close();
    } 
    $fight -> Close();
}

if ($test -> fields['action'] == 'winfight1') 
{
    $quest -> Show('1.2.1next');
    $db -> Execute("UPDATE players SET fight=1 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.2.1next') 
{
    $_SESSION['razy'] = 15;
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
            $quest -> Show('winfight2');
            $quest -> Box(9);
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $quest -> Show('escape1');
            $quest -> Finish(10, array('speed'));
        }
        $health -> Close();
    } 
    $fight -> Close();
}

if ($test -> fields['action'] == 'winfight2') 
{
    $quest -> Show('winfight2');
    $quest -> Box(9);
}

if (((isset($_POST['box9']) && $_POST['box9'] == 1) || $test -> fields['action'] == '1.2.2' || (isset($_POST['box6']) && $_POST['box6'] == 2)) && $test -> fields['action'] != '1.2.2.1next') 
{
    $quest -> Show('1.2.2');
    $quest -> Box(10, array('condition'));
}

if ((isset($_POST['box10']) && $_POST['box10'] == 2) || $test -> fields['action'] == '1.2.2.2') 
{
    $quest -> Show('1.2.2.2');
    $quest -> Box(9);
}

if ((isset($_POST['box10']) && $_POST['box10'] == 1) || (isset($_POST['box11']) && $_POST['box11'] == 1)) 
{
    $quest -> Show('1.2.2.1');
    $db -> Execute("UPDATE players SET fight=2 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.2.2.1') 
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
            $smarty -> assign(array("Box" => ""));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $quest -> Show('winfight3');
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)",
                "Box" => ""));
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $quest -> Show('escape2');
            $quest -> Finish(10, array('speed'));
            $smarty -> assign(array("Box" => ""));
        }
        $health -> Close();
    } 
    $fight -> Close();
}

if (($test -> fields['action'] == 'winfight3' || $test -> fields['action'] == '1.2.2.1next' || $test -> fields['action'] == 'talk2.2' || $test -> fields['action'] == 'gold1') && (!isset($_POST['box9']))) 
{
    $quest -> Show('1.2.2.1next');
    $quest -> Box(9);
}

if ((isset($_POST['box10']) && $_POST['box10'] == 3) || $test -> fields['action'] == '1.2.2.3') 
{
    $quest -> Show('1.2.2.3');
    $quest -> Box(11);
}

if ((isset($_POST['box11']) && $_POST['box11'] == 2) || $test -> fields['action'] == 'talk1') 
{
    $quest -> Show('talk1');
    $quest -> Box(12);
}

if ((isset($_POST['box12']) && $_POST['box12'] == 1) || $test -> fields['action'] == 'talk2.1') 
{
    $quest -> Show('talk2.1');
    $quest -> Box(13);
}

if ((isset($_POST['box12']) && $_POST['box12'] == 2) || (isset($_POST['box13']) && $_POST['box13'] == 2)) 
{
    $quest -> Show('talk2.2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ((isset($_POST['box13']) && $_POST['box13'] == 1) || $test -> fields['action'] == 'talk3') 
{
    $quest -> Show('talk3');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == 'talk3') 
{
    $chance = ($player -> credits);
    if ($chance < 100) 
    {
        $quest -> Show('gold1');
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    } 
        else 
    {
        $quest -> Show('gold2');
        $db -> Execute("UPDATE players SET credits=credits-100 WHERE id=".$player -> id);
        $quest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    }
}

if ($test -> fields['action'] == 'gold2') 
{
    $quest -> Show('talk3next');
    $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php", 
        "Box" => ""));
}

if ($test -> fields['action'] == 'talk3next') 
{
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php"));
    $chance = $quest -> Answer('talk3next','answer1','Y');
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
        $smarty -> assign(array("Link" => "", 
            "Box" => "", 
            "Answer" => ""));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $db -> Execute("UPDATE players SET maps=maps+1 WHERE id=".$player -> id);
        $quest ->Show('answer3');
        $quest -> Finish(50, array('inteli'));
    }
}

if ((isset($_POST['box8']) && $_POST['box8'] == 2) || $test -> fields['action'] == '1.3' || (isset($_POST['box2']) && $_POST['box2'] == 3) || (isset($_POST['box9']) && $_POST['box9'] == 1 && $test -> fields['action'] != 'winfight2') || (isset($_POST['box7']) && $_POST['box7'] == 1)) 
{
    $quest -> Show('1.3');
    $quest -> Box(15);
}

if ((isset($_POST['box15']) && $_POST['box15'] == 4) || $test -> fields['action'] == '1.3.4' || (isset($_POST['box16']) && $_POST['box16'] == 3) || (isset($_POST['box17']) && $_POST['box17'] == 2)) 
{
    $quest -> Show('1.3.4');
    $smarty -> assign(array("Box" => ""));
    $quest -> Finish(5, array('inteli'));
}

if (isset($_POST['box15']) && $_POST['box15'] == 1) 
{
    $quest -> Show('1.3.1');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.3.1') 
{
    $chance = ($player->stats['inteli'][2] + rand(1,100));
    if ($chance < 50) 
    {
        $quest -> Show('int3');
        if ($player -> hp - 10 > 0) 
        {
            $db -> Execute("UPDATE players SET hp=hp-10 WHERE id=".$player -> id);
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)",
                "Box" => ""));
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
            $smarty -> assign( array("Box" => ""));
            $quest -> Finish(5, array('inteli'));
	  }
    } 
        else 
    {
        $quest -> Show('int4');
        $quest -> Gainexp(10, array('inteli'));
        $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
            "Box" => ""));
    }
}

if ($test -> fields['action'] == 'int3' || $test -> fields['action'] == 'int4') 
{
    $quest -> Show('1.3.1next');
    $db -> Execute("UPDATE players SET temp=10 WHERE id=".$player -> id);
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.3.1next') 
{
    $smarty -> assign(array("Answer" => "Y", 
        "File" => "grid.php"));
    $chance = $quest -> Answer('1.3.1next','answer4','Y');
    $amount = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($chance != 1 && $amount -> fields['temp'] <= 0) 
    {
        $smarty -> assign(array("Link" => '', 
            "Answer" => ""));
        $quest ->Show('answer5');
        $quest -> Box(16);
    }
    $amount -> Close();
    if ($chance == 1) 
    {
        $smarty -> assign(array("Link" => "", 
            "Answer" => ""));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $quest ->Show('answer6');
        $quest -> Box(16);
        $quest -> Gainexp(10, array('inteli'));
        if ($test -> fields['action'] != 'answer6')
        {
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ITEM2."',50,'A',5000,5,500,15,500,1,'N',0,0,'N')");
        }
    }
}

if ($test -> fields['action'] == 'answer5') 
{
    $quest ->Show('answer5');
    $quest -> Box(16);
}

if ($test -> fields['action'] == 'answer6' && !isset($_POST['box16'])) 
{
    $quest ->Show('answer6');
    $quest -> Box(16);
}

if ((isset($_POST['box16']) && $_POST['box16'] == 1) || (isset($_POST['box15']) && $_POST['box15'] == 2)){
    $quest -> Show('1.3.2');
    $db -> Execute("UPDATE players SET fight=10 WHERE id=".$player -> id);
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.3.2') 
{
    $quest -> Battle('grid.php?step=quest');
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);            
    if ($fight -> fields['fight'] == 0) 
    {
        $health = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
        if ($health -> fields['hp'] <= 0) 
        {
            $quest -> Show('lostfight4');
            $quest -> Finish(10, array('condition'));
            $smarty -> assign(array("Link" => "", 
                "Box" => ""));
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $quest -> Show('winfight4');
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)",
                "Box" => ""));
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $quest -> Show('escape3');
            $quest -> Finish(10, array('speed'));
        }
        $health -> Close();
    } 
    $fight -> Close();
}

if ($test -> fields['action'] == 'winfight4' || $test -> fields['action'] == '1.3.2next') 
{
    $quest ->Show('1.3.2next');
    $quest -> Box(17);
}

if ((isset($_POST['box17']) && $_POST['box17'] == 1) || (isset($_POST['box16']) && $_POST['box16'] == 2) || $test -> fields['action'] == '1.3.3' || (isset($_POST['box15']) && $_POST['box15'] == 3))
{
    $quest -> Show('1.3.3');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
}

if ($test -> fields['action'] == '1.3.3') 
{
    if ($player -> clas == 'Rzemieślnik' || $player -> clas == 'Złodziej' || $player -> clas == 'Mag') 
    {
        $quest -> Show('1.3.3.1a');
    } 
        else 
    {
        $quest -> Show('1.3.3.1b');
    }
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", 
        "Box" => ""));
    $quest -> Gainexp(10, array('condition'));
}

if ($test -> fields['action'] == '1.3.3.1a' || $test -> fields['action'] == '1.3.3.1b') 
{
    $quest -> Show('1.3.3.1next');
    $gr = $db -> Execute("SELECT id FROM herbs WHERE gracz=".$player -> id);
    if (!$gr -> fields['id']) 
    {
        $db -> Execute("INSERT INTO herbs (gracz, nutari) VALUES(".$player -> id.",100)");
    } 
        else 
    {
        $db -> Execute("UPDATE herbs SET nutari=nutari+100 WHERE gracz=".$player -> id);
    }
    $quest -> Finish(30, array('condition'));
}

$test -> Close();

$smarty -> display('quest.tpl');
?>
