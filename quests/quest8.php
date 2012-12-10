<?php
/**
 *   File functions:
 *   Quest in labirynth - concept author ???
 *
 *   @name                 : quest8.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 10.12.2012
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
require_once("languages/".$lang."/quest8.php");

$objAction = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=8");
$objQuest = new Quests('grid.php', 8, $objAction -> fields['action']);

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

if ((isset($_POST['box1']) && $_POST['box1'] == 1) || $objAction -> fields['action'] == '1') 
{
    $smarty -> assign(array("Start" => '', "Box" => ''));
    $objQuest -> Show('1');
    $objQuest -> Resign();
}

if ((isset($_POST['box1']) && $_POST['box1'] == 2) || $objAction -> fields['action'] == '2') 
{
    $smarty -> assign("Start", '');
    $objQuest -> Show('2');
    $objQuest -> Box(2);
}

if ((isset($_POST['box2']) && $_POST['box2'] == 1) && $objAction -> fields['action'] != '2.1') 
{
    $db -> Execute("INSERT INTO equipment (owner, name, type) VALUES(".$player -> id.", '".I_KEY."', 'Q')");
}

if ((isset($_POST['box2']) && $_POST['box2'] == 1) || $objAction -> fields['action'] == '2.1') 
{
    $objQuest -> Show('2.1');
    $objQuest -> Box(3);
}

if ((isset($_POST['box3']) && $_POST['box3'] == 1) || $objAction -> fields['action'] == '2.1.1') 
{
    $objQuest -> Show('2.1.1');
    $objQuest -> Finish(5, array('condition'));
    $smarty -> assign('Box', '');
    $db -> Execute("DELETE FROM equipment WHERE name='".I_KEY."' AND owner=".$player -> id);
}

if ((isset($_POST['box3']) && $_POST['box3'] == 2) || (isset($_POST['box2']) && $_POST['box2'] == 2) || $objAction -> fields['action'] == '2.2') 
{
    $objQuest -> Show('2.2');
    $objQuest -> Box(4);
}

if ((isset($_POST['box4']) && $_POST['box4'] == 1) || $objAction -> fields['action'] == '2.2.1') 
{
    $objQuest -> Show('2.2.1');
    $objQuest -> Box(5);
}

if ((isset($_POST['box4']) && $_POST['box4'] == 2) || $objAction -> fields['action'] == '2.2.2') 
{
    $objQuest -> Show('2.2.2');
    $objQuest -> Box(5);
}

if ((isset($_POST['box5']) && $_POST['box5'] == 2) || $objAction -> fields['action'] == '2.2.2.2') 
{
    $objQuest -> Show('2.2.2.2');
    $objQuest -> Finish(10, array('condition'));
    $smarty -> assign('Box', '');
    if ($player->antidote != 'R')
      {
	$db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
      }
    else
      {
	$db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
      }
    $db -> Execute("DELETE FROM equipment WHERE name='".I_KEY."' AND owner=".$player -> id);
}

if (isset($_POST['box5']) && $_POST['box5'] == 1)
{
    $objQuest -> Show('2.2.2.1');
    $smarty -> assign(array("Box" => '', "Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_FIGHT2."</a>)"));
    $db -> Execute("UPDATE players SET fight=16 WHERE id=".$player -> id);
}

if ($objAction -> fields['action'] == '2.2.2.1')
{
    $_SESSION['razy'] = 2;
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
            $db -> Execute("DELETE FROM equipment WHERE name='".I_KEY."' AND owner=".$player -> id);
        } 
            elseif ($_POST['action'] != 'escape') 
        {
            $objQuest -> Show('winfight1');
            $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
        } 
            elseif ($_POST['action'] == 'escape') 
        {
            $objQuest -> Show('2.2.2.2');
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
            $db -> Execute("DELETE FROM equipment WHERE name='".I_KEY."' AND owner=".$player -> id);
        }
        $objHealth -> Close();
    } 
    $objFight -> Close();
}

$objKey = $db -> Execute("SELECT id FROM equipment WHERE name='".I_KEY."' AND owner=".$player -> id);
if (($objAction -> fields['action'] == 'winfight1' && $objKey -> fields['id']) || $objAction -> fields['action'] == '2.2.2.1.1')
{
    $objQuest -> Show('2.2.2.1.1');
    $objQuest -> Box(6);
    $smarty -> assign("Link", '');
}

if ($objAction -> fields['action'] == 'winfight1' && !$objKey -> fields['id'])
{
    $objQuest -> Show('2.2.2.1.2');
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php", "Link" => ''));
    $db -> Execute("UPDATE players SET temp=5 WHERE id=".$player -> id);
}
$objKey -> Close();

if ($objAction -> fields['action'] == '2.2.2.1.2' || $objAction -> fields['action'] == 'answer2')
{
    $smarty -> assign(array("Answer" => "Y", "File" => "grid.php"));
    $intChance = $objQuest -> Answer('2.2.2.1.2','answer2','Y');
    $objTest = $db -> Execute("SELECT temp FROM players WHERE id=".$player -> id);
    if ($intChance == 1) 
    {
        $smarty -> assign(array("Link" => '', "Box" => '', "Answer" => ''));
        $db -> Execute("UPDATE players SET temp=0 WHERE id=".$player -> id);
        $objQuest -> Show('answer1');
        $objQuest -> Finish(50, array('inteli'));
    }
        elseif (!$objTest -> fields['temp'])
    {
        $objQuest -> Show('answer3');
        $objQuest -> Finish(10, array('inteli'));
    }
    $objTest -> Close();
}

if ((isset($_POST['box6']) && $_POST['box6'] == 1) || $objAction -> fields['action'] == '2.2.2.1.1.1') 
{
    $objQuest -> Show('2.2.2.1.1.1');
    $objQuest -> Finish(50, array('condition'));
    $db -> Execute("DELETE FROM equipment WHERE name='".I_KEY."' AND owner=".$player -> id);
    $smarty -> assign("Box", '');
}

if ((isset($_POST['box6']) && $_POST['box6'] == 2) || $objAction -> fields['action'] == '2.2.2.1.1.2') 
{
    $objQuest -> Show('2.2.2.1.1.2');
    $objQuest -> Finish(10, array('condition'));
    $db -> Execute("DELETE FROM equipment WHERE name='".I_KEY."' AND owner=".$player -> id);
    $smarty -> assign("Box", '');
}

/**
* Free memory and display page
*/
$objAction -> Close();
$smarty -> display('quest.tpl');
?>
