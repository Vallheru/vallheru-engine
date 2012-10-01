<?php
/**
 *   File functions:
 *   City menu and resets without Cron
 *
 *   @name                 : city.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 01.10.2012
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

$title = "Altara"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/city.php");

if($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (NO_CITY." <a href=");
}

if ($player -> location == 'Altara')
{
    $objItem = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".ITEM."' AND `owner`=".$player -> id);
    if (!$objItem -> fields['id'])
    {
        $intItem = 0;
        $objPoll = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='poll'");
        if ($objPoll -> fields['value'] == 'Y' && $player -> poll == 'N')
        {
            $strInfo = "<b>N</b> ";
        }
            else
        {
            $strInfo = '';
        }
        $objPoll -> Close();
	$strShort = substr(CITY_INFO, 0, 150);
	$strLong = substr(CITY_INFO, 150);
        $arrTitles = array(BATTLE_FIELD, COMMUNITY, VILLAGE, WEST_SIDE, HOUSES_SIDE, CASTLE, JOBS, SOUTH_SIDE);
        $arrFiles = array(array('battle.php', 'armor.php', 'weapons.php', 'bows.php', 'outposts.php', 'hunters.php', 'guilds2.php', 'outpost.php'),
                          array('news.php', 'forums.php?view=categories', 'chat.php', 'mail.php', 'tribes.php', 'newspaper.php'),
                          array('train.php', 'mines.php', 'farm.php', 'core.php'),
                          array('grid.php', 'wieza.php', 'temple.php', 'msklep.php', 'jewellershop.php'),
                          array('house.php', 'memberlist.php?limit=0&amp;lista=id', 'hof2.php', 'library.php', 'chronicle.php'),
                          array('updates.php', 'tower.php', 'jail.php', 'court.php', 'polls.php', 'alley.php', 'stafflist.php'),
                          array('landfill.php', 'smelter.php', 'kowal.php', 'alchemik.php', 'guilds.php', 'crafts.php'),
                          array('market.php', 'warehouse.php', 'travel.php', 'thieves.php'));
        $arrNames = array(array(BATTLE_ARENA, ARMOR_SHOP, WEAPON_SHOP, BOWS_SHOP, OUTPOSTS, 'Gildia Łowców', 'Aula Gladiatorów', 'Prefektura Gwardii'),
                          array(NEWS, FORUMS, INN, PRIV_M, CLANS, PAPER),
                          array(SCHOOL, MINES, FARMS, CORES),
                          array(LABYRYNTH, MAGIC_TOWER, TEMPLE, ALCHEMY_SHOP, JEWELLER_SHOP),
                          array(HOUSES, PLAYERS_L, 'Galeria Machin', LIBRARY, 'Kronika'),
                          array(UPDATES, TIMER, JAIL2, COURT, $strInfo.POLLS, WELLEARNED, STAFF_LIST),
                          array(CLEAN_CITY, SMELTER, BLACKSMITH, ALCHEMY_MILL, 'Gildia Rzemieślników', 'Cześnik'),
                          array(MARKET, WAREHOUSE, TRAVEL, 'Złodziejska Spelunka'));
        $smarty -> assign(array("Titles" => $arrTitles,
                                "Files" => $arrFiles,
                                "Names" => $arrNames,
                                "Cityinfo" => $strShort,
				"Citylong" => $strLong,
				"Anext" => "(czytaj dalej)"));
    }
        else
    {
        $intItem = 1;
        if (!isset($_GET['step']))
        {
            $smarty -> assign(array("Staffquest" => STAFF_QUEST,
                                    "Sqbox1" => SQ_BOX1,
                                    "Sqbox2" => SQ_BOX2));
        }
            else
        {
            if ($_GET['step'] == 'give')
            {
                $smarty -> assign(array("Staffquest" => STAFF_QUEST1,
                                        "Temple" => TEMPLE));
                $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objItem -> fields['id']);
                $db -> Execute("UPDATE `players` SET `credits`=`credits`+100000 WHERE `id`=".$player -> id);
                require_once("includes/checkexp.php");
                checkexp($player -> exp, 10000, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, '', 0);
            }
                elseif ($_GET['step'] == 'take')
            {
                $smarty -> assign("Staffquest", STAFF_QUEST2);
                $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objItem -> fields['id']);
                $db -> Execute("UPDATE `players` SET `credits`=`credits`+10000 WHERE `id`=".$player -> id);
            }
        }
    }
    $objItem -> Close();
}
    else
{
    $intItem = 0;
    if (!isset($_GET['step']))
    {
        $objPoll = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='poll'");
        if ($objPoll -> fields['value'] == 'Y' && $player -> poll == 'N')
        {
            $strInfo = "<b>N</b> ";
        }
            else
        {
            $strInfo = '';
        }
        $objPoll -> Close();
        $arrTitles = array(BATTLE_FIELD, COMMUNITY, VILLAGE, WEST_SIDE, HOUSES_SIDE, CASTLE, JOBS, SOUTH_SIDE);
        $arrFiles = array(array('temple.php', 'library.php', 'chronicle.php', 'jeweller.php'),
                          array('bows.php', 'msklep.php', 'wieza.php', 'forums.php?view=categories', 'chat.php'),
                          array('jail.php', 'maze.php', 'mail.php', 'tribes.php'),
                          array('alchemik.php', 'lumbermill.php', 'train.php', 'jewellershop.php', 'guilds.php', 'crafts.php'),
                          array('landfill.php', 'warehouse.php', 'market.php', 'battle.php', 'core.php', 'polls.php', 'guilds2.php', 'outpost.php'),
                          array('updates.php', 'tower.php', 'news.php', 'newspaper.php', 'alley.php', 'stafflist.php', 'court.php'),
                          array('house.php', 'memberlist.php?limit=0&amp;lista=id', 'outposts.php', 'farm.php', 'hunters.php'),
                          array('travel.php', 'city.php?step=forest', 'thieves.php'));
        $arrNames = array(array(TEMPLE, LIBRARY, 'Kronika', JEWELLER),
                          array(BOWS_SHOP, ALCHEMY_SHOP, MAGIC_TOWER, FORUMS, INN),
                          array(JAIL2, LABYRYNTH, PRIV_M, CLANS),
                          array(ALCHEMY_MILL, LUMBER_MILL, SCHOOL, JEWELLER_SHOP, 'Gildia rzemieślników', 'Cześnik'),
                          array(CLEAN_CITY, WAREHOUSE, MARKET, BATTLE_ARENA, CORES, $strInfo.POLLS, 'Aula Gladiatorów', 'Prefektura Gwardii'),
                          array(UPDATES, TIMER, NEWS, PAPER, WELLEARNED, STAFF_LIST, COURT),
                          array(HOUSES, PLAYERS_L, OUTPOSTS, FARMS, 'Gildia Łowców'),
                          array(TRAVEL, FOREST2, 'Złodziejska Spelunka'));
        $smarty -> assign(array("Titles" => $arrTitles,
                                "Files" => $arrFiles,
                                "Names" => $arrNames,
                                "Cityinfo" => CITY_INFO));
    }
        else
    {
        $db -> Execute("UPDATE `players` SET `miejsce`='Las' WHERE `id`=".$player -> id);
        $smarty -> assign("Message", GO_FOREST);
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Item" => $intItem,
                        "Step" => $_GET['step'],
                        "Location" => $player -> location)); 
$smarty -> display ('city.tpl');

require_once("includes/foot.php"); 
?>
