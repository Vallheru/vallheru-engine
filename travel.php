<?php
/**
 *   File functions:
 *   Travel to other locations and magic portal
 *
 *   @name                 : travel.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 19.08.2011
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

$title="Stajnie";
require_once("includes/head.php");
require_once ("includes/funkcje.php");
require_once("includes/turnfight.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/travel.php");

if ($player -> location == 'Lochy') 
{
    error(ERROR);
}

/**
* Assign variables to template
*/
$smarty -> assign(array("Portal" => '', 
    "Maps" => ''));

/**
* Random encounters in travel
*/
function travel ($address) 
{
    global $player;
    global $smarty;
    global $enemy;
    global $arrehp;
    global $db;
    global $energy;
    $roll = rand (1,100);
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);
    if (($roll < 80 && $fight -> fields['fight'] == 0) || ($player -> energy < 1)) 
    {
        $smarty -> assign ("Message", MESSAGE1);
        $smarty -> display ('error1.tpl');
    } 
        else 
    {
        $smarty -> assign ("Message", MESSAGE2);
        $smarty -> display ('error1.tpl');
        $arrbandit = array ();
        for ($i=0;$i<8;$i++) 
        {
            $roll2 = rand (1,500);
            $arrbandit[$i] = $roll2;
        }
        $enemy = array('name' => 'Bandyta', 
                       'strength' => $arrbandit[0], 
                       'agility' => $arrbandit[1], 
                       'hp' => $arrbandit[2], 
                       'level' => $arrbandit[3], 
                       'endurance' => $arrbandit[6], 
                       'speed' => $arrbandit[7], 
                       'exp1' => $arrbandit[4], 
                       'exp2' => $arrbandit[4]);
        $db -> Execute("UPDATE players SET fight=99999 WHERE id=".$player -> id);
        $arrehp = array ();
        if (!isset ($_POST['action'])) 
        {
            turnfight ($arrbandit[4],$arrbandit[5],'',$address);
        } 
            else 
        {
            turnfight ($arrbandit[4],$arrbandit[5],$_POST['action'],$address);
        }
        $myhp = $db -> Execute("SELECT hp, fight FROM players WHERE id=".$player -> id);
        if ($myhp -> fields['hp'] == 0 && $myhp -> fields['fight'] == 0) 
        {
            $intEnergy = $player -> energy - 1;
            if ($intEnergy < 0)
            {
                $intEnergy = 0;
            }
            $db -> Execute("UPDATE players SET energy=".$intEnergy.", miejsce='Altara' WHERE id=".$player -> id);
            error (MESSAGE3);
        }
        if ($myhp -> fields['fight'] == 0 && $myhp -> fields['hp'] > 0) 
        {
            $intEnergy = $player -> energy - 1;
            if ($intEnergy < 0)
            {
                $intEnergy = 0;
            }
            $db -> Execute("UPDATE players SET energy=".$intEnergy." WHERE id=".$player -> id);
            $smarty -> assign ("Message", MESSAGE4);
            $smarty -> display ('error1.tpl');
        }
        $myhp -> Close();
    }
    $fight -> Close();
}

$objItem = $db -> Execute("SELECT value FROM settings WHERE setting='item'");

if (!isset ($_GET['akcja']) && $player -> location == 'Altara' && !isset($_GET['action'])) 
{
    if ($player->maps >= 20  &&  !$objItem->fields['value'] && $player->rank != 'Bohater' && $player->immunited == 'N') 
    {
        $smarty -> assign(array("Maps" => 1,
                                "Portal1" => PORTAL1,
                                "Ayes" => YES,
                                "Ano" => NO));
    }
    /**
     * Portals to astral plans
     */
    $arrPlans = array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7);
    $objPlans = $db -> Execute("SELECT `name` FROM `astral_plans` WHERE `owner`=".$player -> id." AND `name` LIKE 'M%' AND `location`='V'");
    $arrName = array('');
    $arrLink = array();
    $i = 0;
    while (!$objPlans -> EOF)
    {
        $intNumber = (int)str_replace("M", "", $objPlans -> fields['name']);
        $intNumber = $intNumber - 1;
        $arrName[$i] = $arrPlans[$intNumber];
        $arrLink[$i] = $intNumber;
        $i++;
        $objPlans -> MoveNext();
    }
    $objPlans -> Close();

    $smarty -> assign(array("Altarainfo" => ALTARA_INFO,
                            "Amountains" => A_MOUNTAINS,
                            "Aforest" => A_FOREST,
                            "Tportals" => $arrName,
                            "Tporlink" => $arrLink,
                            "Tporinfo" => T_PORTALS,
                            "Aelfcity" => A_ELFCITY));
}

if (!isset ($_GET['akcja']) && $player -> location == 'Ardulith' && !isset($_GET['action'])) 
{
    $smarty -> assign(array("Altarainfo" => ALTARA_INFO,
                            "Amountains" => A_MOUNTAINS,
                            "Aaltara" => A_ALTARA));
}

if (!isset ($_GET['akcja']) && $player -> location == 'Las' && !isset($_GET['action'])) 
{
    $smarty -> assign(array("Outside" => OUTSIDE,
                            "Aaltara" => A_ALTARA));
}

if (!isset ($_GET['akcja']) && $player -> location == 'Góry' && !isset($_GET['action'])) 
{
    $smarty -> assign(array("Outside" => OUTSIDE,
                            "Aforest" => A_FOREST,
                            "Aaltara" => A_ALTARA));
}

if (isset($_GET['action']))
{
    $smarty -> assign(array("Acaravan" => A_CARAVAN,
                            "Awalk" => A_WALK,
                            "Aback" => A_BACK));
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'tak' && $player->location == 'Altara' && !$objItem->fields['value'] && $player->maps >= 20 && $player->rank != 'Bohater' && $player->immunited == 'N') 
{
    $db -> Execute("UPDATE players SET miejsce='Portal' WHERE id=".$player -> id);
    $smarty -> assign(array("Portal" => "Y",
                            "Portal2" => PORTAL2));
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'nie' && $player->location == 'Altara' && !$objItem->fields['value']  && $player->maps >= 20 && $player->rank != 'Bohater' && $player->immunited == 'N') 
{
    $smarty -> assign(array("Portal" => "N",
                            "Portal3" => PORTAL3));
}

$objItem -> Close();

/**
* Travel to moutains
*/
if (isset ($_GET['akcja']) && $_GET['akcja'] == 'gory') 
{
    $arrLocation = array('Altara', 'Ardulith', 'Podróż');
    if (!in_array($player -> location, $arrLocation))
    {
        error(ERROR);
    }
    if ($_GET['step'] != 'caravan' && $_GET['step'] != 'walk')
    {
        error(ERROR);
    }
    if ($player -> location != 'Ardulith' && $_GET['step'] == 'caravan')
    {
        $intGoldneed = 1000;
        $strCost = 'credits';
    }
        elseif ($player -> location == 'Ardulith' && $_GET['step'] == 'caravan')
    {
        $intGoldneed = 1200;
        $strCost = 'credits';
    }
    if ($player -> location != 'Ardulith' && $_GET['step'] == 'walk')
    {
        $intGoldneed = 5;
        $strCost = 'energy';
    }
        elseif ($player -> location == 'Ardulith' && $_GET['step'] == 'walk')
    {
        $intGoldneed = 6;
        $strCost = 'energy';
    }
    if ($player -> credits < $intGoldneed && $_GET['step'] == 'caravan') 
    {
        error (NO_MONEY);
    }
    if ($player -> energy < $intGoldneed && $_GET['step'] == 'walk')
    {
        error(NO_ENERGY3);
    }
    if ($player -> hp == 0)
    {
        error(YOU_DEAD);
    }
    $db -> Execute("UPDATE players SET miejsce='Podróż' WHERE id=".$player -> id);
    travel("travel.php?akcja=gory&amp;step=".$_GET['step']);
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);
    if (!$fight -> fields['fight']) 
    {
        $db -> Execute("UPDATE players SET miejsce='Góry', ".$strCost."=".$strCost."-".$intGoldneed." WHERE id=".$player -> id);
        error (YOU_REACH);
    }
}

/**
* Travel to forest
*/
if (isset ($_GET['akcja']) && $_GET['akcja'] == 'las') 
{
    $arrLocation = array('Altara', 'Góry', 'Podróż');
    if (!in_array($player -> location, $arrLocation))
    {
        error(ERROR);
    }
    if ($_GET['step'] != 'caravan' && $_GET['step'] != 'walk')
    {
        error(ERROR);
    }
    if ($player -> location != 'Góry' && $_GET['step'] == 'caravan')
    {
        $intGoldneed = 1000;
        $strCost = 'credits';
    }
        elseif ($player -> location == 'Góry' && $_GET['step'] == 'caravan')
    {
        $intGoldneed = 1200;
        $strCost = 'credits';
    }
    if ($player -> location != 'Góry' && $_GET['step'] == 'walk')
    {
        $intGoldneed = 5;
        $strCost = 'energy';
    }
        elseif ($player -> location == 'Góry' && $_GET['step'] == 'walk')
    {
        $intGoldneed = 6;
        $strCost = 'energy';
    }
    if ($player -> credits < $intGoldneed && $_GET['step'] == 'caravan') 
    {
        error (NO_MONEY);
    }
    if ($player -> energy < $intGoldneed && $_GET['step'] == 'walk')
    {
        error(NO_ENERGY3);
    }
    if ($player -> hp == 0)
    {
        error(YOU_DEAD);
    }
    $db -> Execute("UPDATE `players` SET `miejsce`='Podróż' WHERE id=".$player -> id);
    travel("travel.php?akcja=las&amp;step=".$_GET['step']);
    $fight = $db -> Execute("SELECT `fight` FROM `players` WHERE `id`=".$player -> id);
    if (!$fight -> fields['fight']) 
    {
        $db -> Execute("UPDATE `players` SET `miejsce`='Las', `".$strCost."`=`".$strCost."`-".$intGoldneed." WHERE `id`=".$player -> id);
        error (YOU_REACH);
    }
}

/**
* Travel to Ardulith
*/
if (isset ($_GET['akcja']) && $_GET['akcja'] == 'city2') 
{
    if ($player -> location != 'Altara' && $player -> location != 'Podróż')
    {
        error(ERROR);
    }
    if (!isset($_GET['step']))
    {
        error(ERROR);
    }
    if ($_GET['step'] != 'caravan' && $_GET['step'] != 'walk')
    {
        error(ERROR);
    }
    if ($_GET['step'] == 'caravan')
    {
        $intGoldneed = 1000;
        $strCost = 'credits';
    }
    if ($_GET['step'] == 'walk')
    {
        $intGoldneed = 5;
        $strCost = 'energy';
    }
    if ($player -> credits < $intGoldneed && $_GET['step'] == 'caravan') 
    {
        error (NO_MONEY);
    }
    if ($player -> energy < $intGoldneed && $_GET['step'] == 'walk')
    {
        error(NO_ENERGY3);
    }
    if ($player -> hp == 0)
    {
        error(YOU_DEAD);
    }
    $db -> Execute("UPDATE players SET `miejsce`='Podróż' WHERE `id`=".$player -> id);
    travel("travel.php?akcja=city2&amp;step=".$_GET['step']);
    $fight = $db -> Execute("SELECT `fight` FROM `players` WHERE `id`=".$player -> id);
    if (!$fight -> fields['fight']) 
    {
        $db -> Execute("UPDATE `players` SET `miejsce`='Ardulith', `".$strCost."`=`".$strCost."`-".$intGoldneed." WHERE `id`=".$player -> id);
        error (YOU_REACH);
    }
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'powrot') 
{
    if (!isset($_GET['step']))
    {
        error(ERROR);
    }
    if ($_GET['step'] != 'caravan' && $_GET['step'] != 'walk')
    {
        error(ERROR);
    }
    if ($_GET['step'] == 'caravan')
    {
        $intGoldneed = 1000;
        $strCost = 'credits';
    }
    if ($_GET['step'] == 'walk')
    {
        $intGoldneed = 5;
        $strCost = 'energy';
    }
    if ($player -> credits < $intGoldneed && $_GET['step'] == 'caravan') 
    {
        error (NO_MONEY);
    }
    if ($player -> energy < $intGoldneed && $_GET['step'] == 'walk')
    {
        error(NO_ENERGY3);
    }
    if ($player -> hp == 0)
    {
        error(YOU_DEAD);
    }
    $db -> Execute("UPDATE players SET miejsce='Podróż' WHERE id=".$player -> id);
    travel("travel.php?akcja=powrot&amp;step=".$_GET['step']);
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);
    if (!$fight -> fields['fight']) 
    {
        $db -> Execute("UPDATE players SET miejsce='Altara', ".$strCost."=".$strCost."-".$intGoldneed." WHERE id=".$player -> id);
        error (YOU_REACH);
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['akcja'])) 
{
    $_GET['akcja'] = '';
}
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign ( array("Action" => $_GET['akcja'], 
                          "Location" => $player -> location,
                          "Action2" => $_GET['action']));
$smarty -> display('travel.tpl');

require_once("includes/foot.php"); 
?>
