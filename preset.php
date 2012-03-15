<?php
/**
 *   File functions:
 *   Reset account by player
 *
 *   @name                 : preset.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 15.03.2012
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

require 'libs/Smarty.class.php';
require_once ('includes/config.php');

$smarty = new Smarty;

$smarty -> compile_check = true;

/**
* Check avaible languages
*/    
$arrLanguage = scandir('languages/', 1);
$arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm")); 

/**
* Get the localization for game
*/
$strLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
foreach ($arrLanguage as $strTrans)
{
    $strSearch = "^".$strTrans;
    if (eregi($strSearch, $strLanguage))
    {
        $strTranslation = $strTrans;
        break;
    }
}
if (!isset($strTranslation))
{
    $strTranslation = 'pl';
}
require_once("languages/".$strTranslation."/preset.php");

if (isset ($_GET['id'])) 
{
    if (intval($_GET['id']) < 1) 
    {
        $smarty -> assign ("Error", ERROR);
        $smarty -> display ('error.tpl');
        exit;
    }
    if (!isset ($_GET['code'])) 
    {
        $db -> Execute("DELETE FROM `reset` WHERE `player`=".$_GET['id']);
        $smarty -> assign ("Error", R_CANCEL);
        $smarty -> display ('error.tpl');
    } 
        else 
    {
        if (intval($_GET['code']) < 1) 
        {
            $smarty -> assign ("Error", ERROR);
            $smarty -> display ('error.tpl');
            exit;
        }
        $reset = $db -> Execute("SELECT `id`, `type` FROM `reset` WHERE `player`=".$_GET['id']." AND `code`=".$_GET['code']);
        if (!$reset -> fields['id']) 
        {
            $smarty -> assign ("Error", NO_RESET);
            $smarty -> display ('error.tpl');
            exit;
        }
	if ($reset->fields['type'] == 'A')
	  {
	    $db -> Execute("DELETE FROM `equipment` WHERE `owner`=".$_GET['id']);
	    $db -> Execute("DELETE FROM `pmarket` WHERE `seller`=".$_GET['id']);
	    $db -> Execute("DELETE FROM `hmarket` WHERE `seller`=".$_GET['id']);
	    $db -> Execute("DELETE FROM `potions` WHERE `owner`=".$_GET['id']);
	    $db -> Execute("DELETE FROM `herbs` WHERE `gracz`=".$_GET['id']);
	    $db -> Execute("UPDATE `players` SET `level`=1, `exp`=0, `credits`=0, `energy`=0, `max_energy`=70, `strength`=3, `agility`=3, `ap`=5, `platinum`=0, `hp`=15, `max_hp`=15, `bank`=0, `ability`=0.01, `corepass`='N', `trains`=5, `inteli`=3, `pw`=0, `atak`=0.01, `unik`=0.01, `magia`=0.01, `immu`='N', `pm`=6, `szyb`=3, `wytrz`=3, `alchemia`=0.01, `wisdom`=3, `shoot`=0.01, `fletcher`=0.01, `rasa`='', `klasa`='', `deity`='', `gender`='', `leadership`=0.01, `wins`=0, `losses`=0, `lastkilled`='...', `lastkilledby`='...', `breeding`=0.01, `mining`=0.01, `lumberjack`=0.01, `herbalist`=0.01, `crime`=1, `maps`=0, `jeweller`=0.01, `thievery`=0.01, `perception`=0.01, `craftmission`=7, `mpoints`=0, `metallurgy`=0.01 WHERE `id`=".$_GET['id']);
	    $objHouse = $db -> Execute("SELECT `locator` FROM `houses` WHERE `owner`=".$_GET['id']);
	    if ($objHouse -> fields['locator'])
	      {
		$db -> Execute("UPDATE `houses` SET `owner`=".$objHouse -> fields['locator'].", `locator`=0 WHERE `owner`=".$_GET['id']) or $db -> ErrorMsg();
	      }
            else
	      {
		$db -> Execute("DELETE FROM `houses` WHERE `owner`=".$_GET['id']);
	      }
	    $objHouse -> Close();
	    $db -> Execute("DELETE FROM `minerals` WHERE `owner`=".$_GET['id']);
	  }
	else
	  {
	    $db -> Execute("UPDATE `players` SET `level`=1, `exp`=0, `energy`=0, `max_energy`=70, `strength`=3, `agility`=3, `ap`=5, `hp`=15, `max_hp`=15, `ability`=0.01, `corepass`='N', `trains`=5, `inteli`=3, `pw`=0, `atak`=0.01, `unik`=0.01, `magia`=0.01, `immu`='N', `pm`=6, `szyb`=3, `wytrz`=3, `alchemia`=0.01, `wisdom`=3, `shoot`=0.01, `fletcher`=0.01, `rasa`='', `klasa`='', `deity`='', `gender`='', `leadership`=0.01, `wins`=0, `losses`=0, `lastkilled`='...', `lastkilledby`='...', `breeding`=0.01, `mining`=0.01, `lumberjack`=0.01, `herbalist`=0.01, `crime`=1, `maps`=0, `jeweller`=0.01, `thievery`=0.01, `perception`=0.01, `craftmission`=7, `mpoints`=0, `metallurgy`=0.01 WHERE `id`=".$_GET['id']);
	    $db->Execute("UPDATE `equipment` SET `status`='U' WHERE `owner`=".$_GET['id']);
	    $db->Execute("UPDATE `equipment` SET `amount`=1 WHERE `amount`=0 AND `owner`=".$_GET['id']);
	  }
	$db->Execute("DELETE FROM `czary` WHERE `gracz`=".$_GET['id']);
        $db -> Execute("DELETE FROM `core` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `core_market` WHERE `seller`=".$_GET['id']);
        $db -> Execute("DELETE FROM `log` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `outposts` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `notatnik` WHERE `gracz`=".$_GET['id']);
        $db -> Execute("DELETE FROM `tribe_oczek` WHERE `gracz`=".$_GET['id']);
        $db -> Execute("UPDATE `players` SET `miejsce`='Altara' WHERE `miejsce`!='Lochy' AND `id`=".$_GET['id']);
        $db -> Execute("DELETE FROM `farms` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `farm` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `reset` WHERE `players`=".$_GET['id']." AND `code`=".$_GET['code']);
        $db -> Execute("DELETE FROM `questaction` WHERE `player`=".$_GET['id']);
        $db -> Execute("DELETE FROM `lumberjack` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `mines` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `mines_search` WHERE `player`=".$_GET['id']);
        $db -> Execute("DELETE FROM `smelter` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `smith` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `smith_work` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `jeweller` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `jeweller_work` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `astral` WHERE `location`='V' AND `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `astral_bank` WHERE `location`='V' AND `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `astral_plans` WHERE `location`='V' AND `owner`=".$_GET['id']);
	$reset->Close();
        $smarty -> assign ("Error", R_MAKED);
        $smarty -> display ('error.tpl');
    }
}

