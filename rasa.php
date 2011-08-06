<?php
/**
 *   File functions:
 *   Select player race
 *
 *   @name                 : rasa.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.1
 *   @since                : 06.03.2006
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
// $Id: rasa.php 566 2006-09-13 09:31:08Z thindil $

$title = "Wybierz rasę";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/rasa.php");

if ($player -> race)
{
    error(YOU_HAVE);
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'czlowiek' && $player -> race == '') 
{
    $smarty -> assign(array("Raceinfo" => RACE_INFO,
        "Aback" => A_BACK,
        "Aselect" => A_SELECT));
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> race == '') 
    {
        $db -> Execute("UPDATE players SET rasa='Człowiek' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'elf' && $player -> race == '') 
{
    $smarty -> assign(array("Raceinfo" => RACE_INFO,
        "Aback" => A_BACK,
        "Aselect" => A_SELECT));
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> race == '') 
    {
        $db -> Execute("UPDATE players SET rasa='Elf' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'krasnolud' && $player -> race == '') 
{
    $smarty -> assign(array("Raceinfo" => RACE_INFO,
        "Aback" => A_BACK,
        "Aselect" => A_SELECT));
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> race == '') 
    {
        $db -> Execute("UPDATE players SET rasa='Krasnolud' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'hobbit' && $player -> race == '') 
{
    $smarty -> assign(array("Raceinfo" => RACE_INFO,
        "Aback" => A_BACK,
        "Aselect" => A_SELECT));
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> race == '') 
    {
        $db -> Execute("UPDATE players SET rasa='Hobbit' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'reptilion' && $player -> race == '') 
{
    $smarty -> assign(array("Raceinfo" => RACE_INFO,
        "Aback" => A_BACK,
        "Aselect" => A_SELECT));
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> race == '') 
    {
        $db -> Execute("UPDATE players SET rasa='Jaszczuroczłek' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'gnome' && $player -> race == '') 
{
    $smarty -> assign(array("Raceinfo" => RACE_INFO,
        "Aback" => A_BACK,
        "Aselect" => A_SELECT));
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> race == '') 
    {
        $db -> Execute("UPDATE players SET rasa='Gnom' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['rasa'])) 
{
    $_GET['rasa'] = '';
    $smarty -> assign(array("Raceinfo" => RACE_INFO,
        "Ahuman" => A_HUMAN,
        "Aelf" => A_ELF,
        "Adwarf" => A_DWARF,
        "Ahobbit" => A_HOBBIT,
        "Areptilion" => A_REPTILION,
        "Agnome" => A_GNOME));
}

/**
* Assign variable to template and display page
*/
$smarty -> assign("Race", $_GET['rasa']);
$smarty -> display('rasa.tpl');

require_once("includes/foot.php");
?>
