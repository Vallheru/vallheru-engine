<?php
// File generated by VQE 1.0rc1.
/**
*   File functions:
*   Quest in labirynth, concept author TYHE
*
*   @author               : thindil <thindil@users.sourceforge.net>
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
$smarty -> assign(array("End" => '', 
                        "Text" => '', 
                        "Box" => '', 
                        "Link" => '', 
                        "Answer" => '',
                        "Start" => ''));
require_once('class/quests_class.php');
require_once("languages/".$lang."/quest10.php");
$objAction = $db -> Execute("SELECT action FROM questaction WHERE player=".$player -> id." AND quest=10");
$objQuest = new Quests('grid.php', 10, $objAction -> fields['action']);
if (isset($_GET['step']) && $_GET['step'] == 'quest' && empty($objAction -> fields['action']))
{
    $db -> Execute("UPDATE players SET miejsce='Altara' WHERE id=".$player -> id);
    error(NO_QUEST);
}
if ((!$objAction -> fields['action'] || $objAction -> fields['action'] == 'start') && !isset($_POST['box1']))
{
    $objQuest -> Box(1);
}
if ((isset($_POST['box1']) && $_POST['box1'] == 1) && $objAction -> fields['action'] == 'start')
{
    $smarty -> assign("Start", "");
    $objQuest -> Show('1.1');
    $smarty -> assign("Link", "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)");
}
if ((isset($_POST['box1']) && $_POST['box1'] == 2) || $objAction -> fields['action'] == 'end1')
{
    $smarty -> assign("Start", "");
    $objQuest -> Show('end1');
    $objQuest -> Resign();
}
if ($objAction -> fields['action'] == '1.1')
{
    $objQuest -> Show('1.1.next');
    $smarty -> assign("Link", "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)");
}
if ($objAction -> fields['action'] == '1.1.next')
{
    $objQuest -> Show('1.1.n.n');
    $smarty -> assign("Link", "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)");
}
if ($objAction -> fields['action'] == '1.1.n.n' || $objAction -> fields['action'] == '1.1.n.n.n')
{
    $objQuest -> Show('1.1.n.n.n');
    $objQuest -> Box(2);
}
if ((isset($_POST['box2']) && $_POST['box2'] == 1) && ($objAction -> fields['action'] == '1.1.n.n.n'))
{
    $objQuest -> Show('2.1');
    $objMinerals = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$player -> id);
    if ($objMinerals -> fields['owner'])
    {
        $db -> Execute("UPDATE `minerals` SET `crystal`=`crystal`+3 WHERE `owner`=".$player -> id);
    }
        else
    {
        $db -> Execute("INSERT INTO `minerals` (`owner`, `crystal`) VALUES(".$player -> id.", 3)");
    }
    $objMinerals -> Close();
    $smarty -> assign("Box", "");
    $objQuest -> Finish(10, array('condition'));
}
if ($player->stats['strength'][2] < 30 && $objAction -> fields['action'] == '1.1.n.n.n' && (isset($_POST['box2']) && $_POST['box2'] == 2))
{
    $objQuest -> Show('str1');
    $objMinerals = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$player -> id);
    if ($objMinerals -> fields['owner'])
    {
        $db -> Execute("UPDATE `minerals` SET `crystal`=`crystal`+3 WHERE `owner`=".$player -> id);
    }
        else
    {
        $db -> Execute("INSERT INTO `minerals` (`owner`, `crystal`) VALUES(".$player -> id.", 3)");
    }
    $objMinerals -> Close();
    $smarty -> assign("Box", "");
    $objQuest -> Finish(5, array('strength'));
}
if ($player->stats['strength'][2] >= 30 && $objAction -> fields['action'] == '1.1.n.n.n')
{
    $objQuest -> Show('str2');
    $smarty -> assign(array("Link" => "<br /><br />(<a href=\"grid.php?step=quest\">".A_NEXT2."</a>)", "Box" => ''));
}
if ($player->stats['agility'][2] < 30 && $objAction -> fields['action'] == 'str2')
{
    $objQuest -> Show('agi1');
    $intStatpenalty = 30;
    if ($player -> hp < 30)
    {
        $intStatpenalty = $player -> hp;
    }
    $db -> Execute("UPDATE `players` SET `hp`=`hp`-".$intStatpenalty."  WHERE `id`=".$player -> id);
    $objMinerals = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$player -> id);
    if ($objMinerals -> fields['owner'])
    {
        $db -> Execute("UPDATE `minerals` SET `crystal`=`crystal`+1 WHERE `owner`=".$player -> id);
    }
        else
    {
        $db -> Execute("INSERT INTO `minerals` (`owner`, `crystal`) VALUES(".$player -> id.", 1)");
    }
    $objMinerals -> Close();
    $objQuest -> Finish(10, array('condition'));
}
if ($player->stats['agility'][2] >= 30 && $objAction -> fields['action'] == 'str2')
{
    $objQuest -> Show('agi2');
    $objMinerals = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$player -> id);
    if ($objMinerals -> fields['owner'])
    {
        $db -> Execute("UPDATE `minerals` SET `crystal`=`crystal`+8 WHERE `owner`=".$player -> id);
    }
        else
    {
        $db -> Execute("INSERT INTO `minerals` (`owner`, `crystal`) VALUES(".$player -> id.", 8)");
    }
    $objMinerals -> Close();
    $objQuest -> Finish(5, array('condition'));
}
$objAction -> Close();
$smarty -> display('quest.tpl');
?>