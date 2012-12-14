<?php
/**
 *   File functions:
 *   Rest - regenerate mana for a energy
 *
 *   @name                 : rest.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 14.12.2012
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

$maxmana = ($player->stats['inteli'][2] + $player->stats['wisdom'][2]);
if ($player->clas == 'Mag')
  {
    $maxmana = $maxmana * 2;
  }
$maxmana += floor(($player->equip[8][2] / 100) * $maxmana);

if (!isset ($_GET['akcja'])) 
{
    $koszt = ceil(($maxmana - $player -> mana) / 10);
    $smarty -> assign(array("Energy" => $koszt,
                            "Trest" => "Odpoczynek",
                            "Restinfo" => "Tutaj możesz odpocząć regenerując swoje <b>punkty magii</b>. Całkowite odzyskanie <b>punktów magii</b> będzie ciebie kosztować",
                            "Restinfo2" => "energii. Jeżeli nie masz tyle energii możesz również odzyskać częściowo <b>punkty magii</b> w stosunku 10 punktów magii za 1 punkt energii",
                            "Iwant" => "Chcę odzyskać",
                            "Rmana" => "punktów magii",
                            "Arest" => "Odpoczywaj",
                            "Aback" => "Powrót do statystyk"));
    $smarty -> display ('rest.tpl');
}
if (isset($_GET['akcja']) && $_GET['akcja'] == 'all') 
{
    if (!isset($_POST['pm']))
    {
        error("Podaj ile punktów magii chcesz odzyskać");
    }
    checkvalue($_POST['pm']);
    $energia = $_POST['pm'] / 10;
    $energia = round($energia,"2");
    if ($player -> energy < $energia) 
    {
        error ("Nie masz tyle energii!");
    }
    if ($player -> mana == $maxmana) 
    {
        error ("Nie musisz odpoczywać");
    }
    $zpm = ($_POST['pm'] + $player -> mana);
    if ($zpm > $maxmana) 
    {
        error ("Nie możesz odzyskać więcej Punktów Magii niż masz maksymalnie!");
    }
    $db -> Execute("UPDATE `players` SET `pm`=".$zpm.", `energy`=`energy`-".$energia." WHERE `id`=".$player -> id);
    error ("Opocząłeś sobie przez jakiś czas i odzyskałeś ".$_POST['pm']." punkty magii w zamian za ".$energia." energii. <a href=stats.php>Powrót do statystyk</a>");
}

require_once("includes/foot.php");
?>
