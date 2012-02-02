<?php
/**
 *   File functions:
 *   Magic portal - special location
 *
 *   @name                 : portal.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 02.02.2012
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

$title = "Portal";
require_once("includes/head.php");
require_once ("includes/funkcje.php");
require_once("includes/turnfight.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/portal.php");

if ($player -> location != 'Portal') 
{
    error (ERROR);
}

/**
* Assign variable to template
*/
$smarty -> assign("Win", '');

if ($player -> hp <= 0 && !isset($_GET['action1'])) 
{
    $db -> Execute("UPDATE players SET miejsce='Altara' WHERE id=".$player -> id);
    error (YOU_DEAD);
}

if (!isset ($_GET['action1'])) 
{
    $smarty -> assign(array("Portaltext" => PORTAL_TEXT,
			    "Afight2" => A_FIGHT2,
			    "Aretreat" => A_RETREAT));
}

if (isset ($_GET['action1']) && $_GET['action1'] == 'retreat' && $player -> hp > 0) 
{
    $db -> Execute("UPDATE players SET miejsce='Altara' WHERE id=".$player -> id);
    $db -> Execute("UPDATE players SET energy=0 WHERE id=".$player -> id);
    $smarty -> assign(array("Portaltext" => PORTAL_TEXT,
			    "Ahere" => A_HERE));
}

if (isset ($_GET['action1']) && $_GET['action1'] == 'fight' && $player -> hp > 0) 
{
    global $arrehp;
    if (!isset ($_GET['step'])) 
    {
        $smarty -> assign ("Message", START_FIGHT);
        $smarty -> display ('error1.tpl');
        $enemy = array ('name' => MONSTER_NAME, 
                        'strength' => 15000, 
                        'agility' => 15000, 
                        'hp' => 50000, 
                        'level' => 1, 
                        'endurance' => 15000, 
                        'speed' => 15000,
			'lootnames' => array(),
			'lootchance' => array(),
			'exp1' => 0,
			'exp2' => 0);
        if ($player -> hp <= 0) 
        {
            error (NO_HP);
        }
        if ($player -> energy <= 0) 
        {
            error (NO_ENERGY);
        }
        $db -> Execute("UPDATE players SET fight=999 WHERE id=".$player -> id);
        $arrehp = array ();
        if (!isset ($_POST['action'])) 
        {
            turnfight (1000000, 1000000, '', 'portal.php?action1=fight');
        } 
            else 
        {
            turnfight (1000000, 1000000, $_POST['action'], 'portal.php?action1=fight');
        }
        $myhp = $db -> Execute("SELECT `hp`, `fight`, `miejsce` FROM `players` WHERE `id`=".$player -> id);
	echo $myhp->fields['fight'];
        $item = $db -> Execute("SELECT value FROM settings WHERE setting='item'");
        if ($myhp -> fields['hp'] <= 0 || isset($_SESSION['ressurect']) || $myhp->fields['miejsce'] == 'Altara') 
        {
            $db -> Execute("UPDATE players SET energy=0, miejsce='Altara' WHERE id=".$player -> id);
            error (LOST_FIGHT2);
        } 
	elseif (!$item -> fields['value'] && $myhp -> fields['hp'] > 0 && $myhp -> fields['fight'] == 0) 
        {
            $db -> Execute("UPDATE players SET energy=0 WHERE id=".$player -> id);
            $smarty -> assign(array("Win" => 1,
                                    "Portaltext" => PORTAL_TEXT,
                                    "Sword" => SWORD,
                                    "Armor" => ARMOR,
                                    "Istaff" => I_STAFF,
                                    "Cape" => CAPE));
        }
	if ($myhp->fields['miejsce'] == 'Altara' || $myhp->fields['fight'] == 9999) 
	  {
	    $_GET['action1'] == 'retreat';
	    $db->Execute("UPDATE `players` SET `energy`=0, `fight`=0 WHERE `id`=".$player->id);
	  }
        $myhp -> Close();
        $item -> Close();
    }
    if (isset ($_GET['step'])) 
    {
        if ($_GET['step'] != 'sword' && $_GET['step'] != 'armor' && $_GET['step'] != 'staff' && $_GET['step'] != 'cape') 
        {
            error (ERROR);
        }
        $winner = $db -> Execute("SELECT value FROM settings WHERE setting='player'");
        if ($winner -> fields['value'] > 0)
        {
            error(ITEM_TAKE);
        }
        $winner -> Close();
        if ($_GET['step'] == 'sword') 
        {
            $smarty -> assign ("Item", SWORD);
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".SWORD."',500,'W',1,0,5000,1,5000,1,'Y',0,100,'Y')");
            $db -> Execute("UPDATE settings SET value='sword' WHERE setting='item'");
        }
        if ($_GET['step'] == 'armor') 
        {
            $smarty -> assign ("Item", ARMOR);
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".ARMOR."',2000,'A',1,-100,20000,1,20000,1,'Y',0,100,'N')");
            $db -> Execute("UPDATE settings SET value='armor' WHERE setting='item'");
        }
        if ($_GET['step'] == 'staff') 
        {
            $smarty -> assign ("Item", I_STAFF);
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".I_STAFF."',500,'S',1,0,0,1,0,1,'Y',0,0,'N')");
            $db -> Execute("UPDATE settings SET value='staff' WHERE setting='item'");
        }
        if ($_GET['step'] == 'cape') 
        {
            $smarty -> assign ("Item", CAPE);
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand) VALUES(".$player -> id.",'".CAPE."',500,'Z',1,0,0,1,0,1,'Y',0,0,'N')");
            $db -> Execute("UPDATE settings SET value='cape' WHERE setting='item'");
        }
        $db -> Execute("UPDATE players SET miejsce='Altara', rank='Bohater' WHERE id=".$player -> id);
        $db -> Execute("UPDATE settings SET value=".$player -> id." WHERE setting='player'");
	$strHero = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];
        $db -> Execute("INSERT INTO halloffame (heroid, oldname, herorace, newid) VALUES(".$player -> id.", '".$strHero."', '".$player -> race." ".$player -> clas."', ".$player -> id.")");
        $db -> Execute("INSERT INTO updates (starter, title, updates, lang) VALUES('(Herold)','".U_TITLE."','".U_TEXT.$gamename.U_TEXT2.$gamename.U_TEXT3.$newdate.U_TEXT4.$strHero.U_TEXT5.$player -> id.U_TEXT6."','".$player -> lang."')");
        $smarty -> assign(array("Steptext" => STEP_TEXT,
                                "Tgo" => T_GO,
                                "Ahere" => A_HERE));
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['action1']))
{
    $_GET['action1'] = '';
}
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action1'], 
                        "Step" => $_GET['step']));
$smarty -> display('portal.tpl');

require_once("includes/foot.php");
?>
