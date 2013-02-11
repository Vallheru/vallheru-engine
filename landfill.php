<?php
/**
 *   File functions:
 *   Clean city and earn money
 *
 *   @name                 : landfill.php                            
 *   @copyright            : (C) 2004,2005,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 11.02.2013
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

$title = "Oczyszczanie miasta";
require_once("includes/head.php");

if ($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

if ($player -> hp == 0) 
{
    error ("Nie możesz pracować, ponieważ jesteś martwy!. (<a href=city.php>Wróć</a>)");
}

if (isset($_GET['action'])) 
{
    if (!isset($_POST['amount'])) 
    {
        error("Podaj ile czasu chcesz spędzić pracując!");
    }
    checkvalue($_POST['amount']);
    if ($player -> energy < $_POST['amount']) 
      {
	message('error', "Nie masz tyle energii aby pracować.");
      }
    else
      {
	$gain = (($player->stats['condition'][2] * 25) * $_POST['amount']);
	$db -> Execute("UPDATE players SET energy=energy-".$_POST['amount'].", credits=credits+".$gain." WHERE id=".$player -> id);
	$player->energy -= $_POST['amount'];
	$player->checkexp(array('condition' => $_POST['amount']), $player->id, 'stats');
	message('success', "Podczas pracy zużyłeś ".$_POST['amount']." punkt(ów) energii i zarobiłeś ".$gain." sztuk złota oraz <b>".$_POST['amount']."</b> punktów doświadczenia.");
      }
}
if ($player->location == 'Altara')
  {
    $strDesc = "Pragniesz zarobić nieco sztuk złota? W porządku. Za każdy worek śmieci jakie uprzątniesz, dam ci";
  }
 else
   {
     $strDesc = "Witaj na Polanie drwali. Możesz tutaj poświęcić nieco swojego czasu, aby zarobić złoto. Za każdy punkt energii jaki zużyjesz, dostaniesz";
   }
$gain = ($player->stats['condition'][2] * 25);
$smarty -> assign(array("Gold" => $gain,
			"Landinfo" => $strDesc,
			"Landinfo2" => "sztuk złota.",
			"Energy" => floor($player->energy),
			"Awork" => "Pracuj",
			"Times" => "razy."));

/**
* Assign variables to template and display page
*/
$smarty -> display ('landfill.tpl');

require_once("includes/foot.php");
?>
