<?php
/**
 *   File functions:
 *   Distribution of Astral Poinst between player statistics
 *
 *   @name                 : ap.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 09.09.2011
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

$title = "Dystrybucja AP";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/ap.php");

if (!$player -> race || !$player -> clas) 
  {
    error (NO_CLASS." <a href=\"stats.php\">Wróć</a>");
  }

/**
* Assign variables to template
*/
$smarty -> assign(array("Strength" => '',
    "Agility" => '',
    "Speed" => '',
    "Endurance" => '',
    "Stat" => '',
	"Stat2" => 0));

if (!isset ($_GET['step']) && $player -> race == 'Człowiek' && $player -> ap > 0) 
{
    $smarty -> assign (array("Strength" => 2.5, 
        "Agility" => 2.5, 
        "Speed" => 2.5, 
        "Endurance" => 2.5));
}

if (!isset ($_GET['step']) && $player -> race == 'Elf' && $player -> ap > 0) 
{
    $smarty -> assign (array("Strength" => 2, 
        "Agility" => 3, 
        "Speed" => 3, 
        "Endurance" => 2));
}

if (!isset ($_GET['step']) && $player -> race == 'Krasnolud' && $player -> ap > 0) 
{
    $smarty -> assign (array("Strength" => 3.5, 
        "Agility" => 2, 
        "Speed" => 2, 
        "Endurance" => 3));
}

if (!isset ($_GET['step']) && $player -> race == 'Hobbit' && $player -> ap > 0) 
{
    $smarty -> assign (array("Strength" => 1.5, 
        "Agility" => 3.5, 
        "Speed" => 1.5, 
        "Endurance" => 2.5));
}

if (!isset ($_GET['step']) && $player -> race == 'Jaszczuroczłek' && $player -> ap > 0) 
{
    $smarty -> assign (array("Strength" => 3.5, 
        "Agility" => 2.5, 
        "Speed" => 3, 
        "Endurance" => 2.5));
}

if (!isset ($_GET['step']) && $player -> race == 'Gnom' && $player -> ap > 0) 
{
    $smarty -> assign (array("Strength" => 1.5, 
        "Agility" => 2.5, 
        "Speed" => 1.5, 
        "Endurance" => 2.5,
		"Stat2" => 2));
}

if (!isset ($_GET['step']) && $player -> clas == 'Wojownik' && $player -> ap > 0) 
{
    $smarty -> assign ("Stat", 2);
}

if (!isset ($_GET['step']) && $player -> clas == 'Barbarzyńca' && $player -> ap > 0) 
{
    $smarty -> assign ("Stat", 2);
}

if (!isset ($_GET['step']) && $player -> clas == 'Mag' && $player -> ap > 0) 
{
    $smarty -> assign ("Stat", 3);
}

if (!isset ($_GET['step']) && $player -> clas == 'Rzemieślnik' && $player -> ap > 0) 
{
    $smarty -> assign ("Stat", 2.5);
}

if (!isset ($_GET['step']) && $player -> clas == 'Złodziej' && $player -> ap > 0) 
{
    $smarty -> assign ("Stat", 2.5);
}

if (!isset ($_GET['step']) && $player -> clas == 'Wojownik' && $player -> ap > 0 && $player -> race == 'Jaszczuroczłek') 
{
    $smarty -> assign ("Stat", 1);
}

if (!isset ($_GET['step']) && $player -> clas == 'Mag' && $player -> ap > 0 && $player -> race == 'Jaszczuroczłek') {
    $smarty -> assign ("Stat", 2);
}

if (!isset ($_GET['step']) && $player -> clas == 'Rzemieślnik' && $player -> ap > 0 && $player -> race == 'Jaszczuroczłek') 
{
    $smarty -> assign ("Stat", 1.5);
}

/**
* Distribution of Astral Points
*/
if (isset ($_GET['step']) && $_GET['step'] == 'add') 
{
    if (!isset($_POST['strength']) || !isset($_POST['agility']) || !isset($_POST['szyb']) || !isset($_POST['wytrz']) || !isset($_POST['inteli']) || !isset($_POST['wisdom'])) 
    {
        error(EMPTY_FIELDS);
    }
    $arrchar = array($_POST['strength'],$_POST['agility'],$_POST['szyb'],$_POST['wytrz'],$_POST['inteli'],$_POST['wisdom']);
    $arrgain = array(0,0,0,0,0,0);
    $sum = 0;
    foreach ($arrchar as $stat) 
    {
	$stat = intval($stat);
	if ($stat < 0)
	  {
	    error(ERROR);
	  }
        $sum = ($sum + $stat);
    }
    if ($sum > $player -> ap) 
    {
        error (NO_AP);
    }
    if ($sum == 0) 
    {
        error (NO_AP2);
    }
    if ($player -> race == 'Człowiek') 
    {
	    $arrgain[0] = 2.5;
	    $arrgain[1] = 2.5;
	    $arrgain[2] = 2.5;
	    $arrgain[3] = 2.5;
    }
    if ($player -> race == 'Elf') 
    {
	    $arrgain[0] = 2;
	    $arrgain[1] = 3;
	    $arrgain[2] = 3;
	    $arrgain[3] = 2;
    }
    if ($player -> race == 'Krasnolud') 
    {
	    $arrgain[0] = 3.5;
	    $arrgain[1] = 2;
	    $arrgain[2] = 2;
	    $arrgain[3] = 3;
    }
    if ($player -> race == 'Hobbit') 
    {
	    $arrgain[0] = 1.5;
	    $arrgain[1] = 3.5;
	    $arrgain[2] = 1.5;
	    $arrgain[3] = 2.5;
    }
    if ($player -> race == 'Jaszczuroczłek') 
    {
	    $arrgain[0] = 3.5;
	    $arrgain[1] = 2.5;
	    $arrgain[2] = 3;
	    $arrgain[3] = 2.5;
    }
    if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
    {
	    $arrgain[4] = 2;
	    $arrgain[5] = 2;
    }
    if ($player -> clas == 'Mag') 
    {
	    $arrgain[4] = 3;
	    $arrgain[5] = 3;
    }
    if ($player -> clas == 'Rzemieślnik' || $player -> clas == 'Złodziej') 
    {
	    $arrgain[4] = 2.5;
	    $arrgain[5] = 2.5;
    }
    if ($player -> clas == 'Wojownik' && $player -> race == 'Jaszczuroczłek') 
    {
	    $arrgain[4] = 1;
	    $arrgain[5] = 1;
    }
    if ($player -> clas == 'Barbarzyńca' && $player -> race == 'Jaszczuroczłek') 
    {
	    $arrgain[4] = 1;
	    $arrgain[5] = 1;
    }
    if ($player -> clas == 'Złodziej' && $player -> race == 'Jaszczuroczłek') 
    {
	    $arrgain[4] = 1.5;
	    $arrgain[5] = 1.5;
    }
    if ($player -> clas == 'Mag' && $player -> race == 'Jaszczuroczłek') 
    {
	    $arrgain[4] = 2;
	    $arrgain[5] = 2;
    }
    if ($player -> clas == 'Rzemieślnik' && $player -> race == 'Jaszczuroczłek') 
    {
	    $arrgain[4] = 1.5;
	    $arrgain[5] = 1.5;
    }
    if ($player -> race == 'Gnom') 
    {
	    $arrgain[0] = 1.5;
	    $arrgain[1] = 2.5;
	    $arrgain[2] = 1.5;
	    $arrgain[3] = 2.5;
		$arrgain[5] = 2;
    }
    $arrpoints = array(0,0,0,0,0,0);
    $arrname = array(A_STRENGTH, A_AGILITY, A_SPEED, A_CONDITION, A_INTELIGENCE, A_WISDOM);
    for ($i=0;$i<6;$i++) 
    {
        $arrpoints[$i] = $arrchar[$i] * $arrgain[$i];
    }
    if ($arrpoints[0] > 0) 
    {
	    $db -> Execute("UPDATE players SET strength=strength+".$arrpoints[0]." WHERE id=".$player -> id);
    }
    if ($arrpoints[1] > 0) 
    {
	    $db -> Execute("UPDATE players SET agility=agility+".$arrpoints[1]." WHERE id=".$player -> id);
    }
    if ($arrpoints[2] > 0) 
    {
	    $db -> Execute("UPDATE players SET szyb=szyb+".$arrpoints[2]." WHERE id=".$player -> id);
    }
    if ($arrpoints[3] > 0) 
    {
        $db -> Execute("UPDATE players SET max_hp=max_hp+".$arrpoints[3].", wytrz=wytrz+".$arrpoints[3]." WHERE id=".$player -> id);
    }
    if ($arrpoints[4] > 0) 
    {
	    $db -> Execute("UPDATE players SET inteli=inteli+".$arrpoints[4]." WHERE id=".$player -> id);
    }
    if ($arrpoints[5] > 0) 
    {
	    $db -> Execute("UPDATE players SET wisdom=wisdom+".$arrpoints[5]." WHERE id=".$player -> id);
    }
    $db -> Execute("UPDATE players SET ap=ap-".$sum." WHERE id=".$player -> id);
    $smarty -> assign (array("Amount" => $arrpoints, 
        "Name" => $arrname,
        "Youget" => YOU_GET,
        "Click" => CLICK,
        "Here" => HERE,
        "Fora" => FOR_A));
}

/**
* Initialization of variable and assign variables to template
*/
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
	$smarty -> assign(array("Apinfo" => AP_INFO,
        "Ap2" => AP,
        "Nstrength" => N_STRENGTH,
        "Nagility" => N_AGILITY,
        "Nspeed" => N_SPEED,
        "Ncond" => N_COND,
        "Nint" => N_INT,
        "Nwisdom" => N_WISDOM,
        "Aadd" => A_ADD));
}

/**
* Assign variables and display page
*/
$smarty -> assign (array("Step" => $_GET['step'], 
    "Ap" => $player -> ap));
$smarty -> display ('ap.tpl');

require_once("includes/foot.php");
?>
