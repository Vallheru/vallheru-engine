<?php
/**
 *   File functions:
 *   Reset account by player
 *
 *   @name                 : preset.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 09.01.2013
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

if (isset ($_GET['id'])) 
{
    if (intval($_GET['id']) < 1) 
    {
        $smarty -> assign ("Error", 'Zapomnij o tym.');
        $smarty -> display ('error.tpl');
        exit;
    }
    if (!isset ($_GET['code'])) 
    {
        $db -> Execute("DELETE FROM `reset` WHERE `player`=".$_GET['id']);
        $smarty -> assign ("Error", "Próba resetu została anulowana.");
        $smarty -> display ('error.tpl');
    } 
        else 
    {
        if (intval($_GET['code']) < 1) 
        {
            $smarty -> assign ("Error", 'Zapomnij o tym');
            $smarty -> display ('error.tpl');
            exit;
        }
        $reset = $db -> Execute("SELECT `id`, `type` FROM `reset` WHERE `player`=".$_GET['id']." AND `code`=".$_GET['code']);
        if (!$reset -> fields['id']) 
        {
            $smarty -> assign ("Error", "Nie ma takiego zgłoszenia.");
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
	    $db -> Execute("UPDATE `players` SET `credits`=0, `energy`=0, `max_energy`=100, `ap`=5, `platinum`=0, `hp`=10, `max_hp`=10, `bank`=0, `corepass`='N', `trains`=5, `pw`=0, `immu`='N', `pm`=6, `rasa`='', `klasa`='', `deity`='', `gender`='', `wins`=0, `losses`=0, `lastkilled`='...', `lastkilledby`='...', `maps`=0, `craftmission`=7, `mpoints`=0, `stats`='strength:Siła,0,0,0;agility:Zręczność,0,0,0;condition:Kondycja,0,0,0;speed:Szybkość,0,0,0;inteli:Inteligencja,0,0,0;wisdom:Siła Woli,0,0,0;', `skills`='smith:Kowalstwo,1,0;shoot:Strzelectwo,1,0;alchemy:Alchemia,1,0;dodge:Uniki,1,0;carpentry:Stolarstwo,1,0;magic:Rzucanie Czarów,1,0;attack:Walka Bronią,1,0;leadership:Dowodzenie,1,0;breeding:Hodowla,1,0;mining:Górnictwo,1,0;lumberjack:Drwalnictwo,1,0;herbalism:Zielarstwo,1,0;jewellry:Jubilerstwo,1,0;smelting:Hutnictwo,1,0;thievery:Złodziejstwo,1,0;perception:Spostrzegawczość,1,0;', `bonuses`='', `bless`='', `blessval`=0 WHERE `id`=".$_GET['id']);
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
	    $db -> Execute("UPDATE `players` SET `energy`=0, `max_energy`=100, `ap`=5, `hp`=10, `max_hp`=10, `corepass`='N', `trains`=5, `pw`=0, `immu`='N', `pm`=6, `rasa`='', `klasa`='', `deity`='', `gender`='', `wins`=0, `losses`=0, `lastkilled`='...', `lastkilledby`='...', `maps`=0, `craftmission`=7, `mpoints`=0, `stats`='strength:Siła,0,0,0;agility:Zręczność,0,0,0;condition:Kondycja,0,0,0;speed:Szybkość,0,0,0;inteli:Inteligencja,0,0,0;wisdom:Siła Woli,0,0,0;', `skills`='smith:Kowalstwo,1,0;shoot:Strzelectwo,1,0;alchemy:Alchemia,1,0;dodge:Uniki,1,0;carpentry:Stolarstwo,1,0;magic:Rzucanie Czarów,1,0;attack:Walka Bronią,1,0;leadership:Dowodzenie,1,0;breeding:Hodowla,1,0;mining:Górnictwo,1,0;lumberjack:Drwalnictwo,1,0;herbalism:Zielarstwo,1,0;jewellry:Jubilerstwo,1,0;smelting:Hutnictwo,1,0;thievery:Złodziejstwo,1,0;perception:Spostrzegawczość,1,0;', `bonuses`='', `bless`='', `blessval`=0 WHERE `id`=".$_GET['id']);
	    $db->Execute("UPDATE `equipment` SET `status`='U', `cost`=1 WHERE `owner`=".$_GET['id']);
	    $db->Execute("UPDATE `equipment` SET `amount`=1 WHERE `amount`=0 AND `owner`=".$_GET['id']);
	  }
	$db->Execute("DELETE FROM `czary` WHERE `gracz`=".$_GET['id']);
        $db -> Execute("DELETE FROM `core` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `core_market` WHERE `seller`=".$_GET['id']);
        $db -> Execute("DELETE FROM `log` WHERE `owner`=".$_GET['id']);
        $db -> Execute("DELETE FROM `outposts` WHERE `owner`=".$_GET['id']);
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
        $smarty -> assign ("Error", "Postać została zresetowana.");
        $smarty -> display ('error.tpl');
    }
}

