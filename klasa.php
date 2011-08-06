<?php
/**
 *   File functions:
 *   Select player class
 *
 *   @name                 : klasa.php                            
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
// 

$title = "Wybierz klasę";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/klasa.php");

if ($player -> clas)
{
    error(YOU_HAVE);
}

if (!isset ($_GET['klasa']) && $player -> clas == '') 
{
    $smarty -> assign(array("Info" => INFO,
        "Awarrior" => A_WARRIOR,
        "Amage" => A_MAGE,
        "Aworker" => A_WORKER,
        "Athief" => A_THIEF,
        "Abarbarian" => A_BARBARIAN));
}

if (isset ($_GET['klasa']) && $_GET['klasa'] == 'wojownik' && $player -> clas == '') 
{
    $smarty -> assign("Classinfo", CLASS_INFO);
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> clas == '') 
    {
        $db -> Execute("UPDATE players SET klasa='Wojownik' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset ($_GET['klasa']) && $_GET['klasa'] == 'mag' && $player -> clas == '') 
{
    $smarty -> assign("Classinfo", CLASS_INFO);
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> clas == '') 
    {
        $db -> Execute("UPDATE players SET klasa='Mag' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset ($_GET['klasa']) && $_GET['klasa'] == 'craftsman' && $player -> clas == '') 
{
    $smarty -> assign("Classinfo", CLASS_INFO);
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> clas == '') 
    {
        $db -> Execute("UPDATE players SET klasa='Rzemieślnik' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset ($_GET['klasa']) && $_GET['klasa'] == 'barbarzynca' && $player -> clas == '') 
{
    $smarty -> assign("Classinfo", CLASS_INFO);
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> clas == '') 
    {
        $db -> Execute("UPDATE players SET klasa='Barbarzyńca' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

if (isset ($_GET['klasa']) && $_GET['klasa'] == 'zlodziej' && $player -> clas == '') 
{
    $smarty -> assign("Classinfo", CLASS_INFO);
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> clas == '') 
    {
        $db -> Execute("UPDATE players SET klasa='Złodziej' WHERE id=".$player -> id);
        error (YOU_SELECT);
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['klasa'])) 
{
    $_GET['klasa'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Clas" => $_GET['klasa'], 
    "Plclass" => $player -> clas,
    "Aback" => A_BACK,
    "Aselect" => A_SELECT));
$smarty -> display ('klasa.tpl');

require_once("includes/foot.php");
?>
