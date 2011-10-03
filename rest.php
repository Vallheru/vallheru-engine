<?php
/**
 *   File functions:
 *   Rest - regenerate mana for a energy
 *
 *   @name                 : rest.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 03.10.2011
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

$title = "Odpoczynek"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/rest.php");

$arrEquip = $player -> equipment();
$arrRings = array(R_INT, 'woli');
$arrStat = array('inteli', 'wisdom');
if ($arrEquip[9][0])
{
    $arrRingtype = explode(" ", $arrEquip[9][1]);
    $intAmount = count($arrRingtype) - 1;
    $intKey = array_search($arrRingtype[$intAmount], $arrRings);
    if ($intKey != NULL)
    {
        $strStat = $arrStat[$intKey];
        $player -> $strStat = $player -> $strStat + $arrEquip[9][2];
    }
}
if ($arrEquip[10][0])
{
    $arrRingtype = explode(" ", $arrEquip[10][1]);
    $intAmount = count($arrRingtype) - 1;
    $intKey = array_search($arrRingtype[$intAmount], $arrRings);
    if ($intKey != NULL)
    {
        $strStat = $arrStat[$intKey];
        $player -> $strStat = $player -> $strStat + $arrEquip[10][2];
    }
}
$maxmana = ($player -> inteli + $player -> wisdom);
$maxmana = $maxmana + (($arrEquip[8][2] / 100) * $maxmana);

if (!isset ($_GET['akcja'])) 
{
    $koszt = ceil(($maxmana - $player -> mana) / 10);
    $smarty -> assign(array("Energy" => $koszt,
                            "Trest" => T_REST,
                            "Restinfo" => REST_INFO,
                            "Restinfo2" => REST_INFO2,
                            "Iwant" => I_WANT,
                            "Rmana" => R_MANA,
                            "Arest" => A_REST,
                            "Aback" => A_BACK));
    $smarty -> display ('rest.tpl');
}
if (isset($_GET['akcja']) && $_GET['akcja'] == 'all') 
{
    if (!isset($_POST['pm']))
    {
        error(HOW_MANY);
    }
    checkvalue($_POST['pm']);
    $energia = $_POST['pm'] / 10;
    $energia = round($energia,"2");
    if ($player -> energy < $energia) 
    {
        error (NO_ENERGY);
    }
    if ($player -> mana == $maxmana) 
    {
        error (NO_REST);
    }
    $zpm = ($_POST['pm'] + $player -> mana);
    if ($zpm > $maxmana) 
    {
        error (TOO_MUCH);
    }
    $db -> Execute("UPDATE `players` SET `pm`=".$zpm.", `energy`=`energy`-".$energia." WHERE `id`=".$player -> id);
    error (YOU_REST.$_POST['pm'].YOU_REST2.$energia.YOU_REST3);
}

require_once("includes/foot.php");
?>
