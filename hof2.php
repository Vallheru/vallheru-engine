<?php
/**
 *   File functions:
 *   Hall of Machines - list of all tribes which build astral machines
 *
 *   @name                 : hof2.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 11.10.2011
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

$title = "Galeria Machin";

require_once("includes/head.php");

if ($player->location != 'Altara') 
{
    error("Nie znajdujesz się w Altarze.");
}

$objMachines = $db->Execute("SELECT * FROM `halloffame2` ORDER BY `id` ASC");
$arrTribes = array();
$arrLeaders = array();
$arrDates = array();
if (!$objMachines->fields['tribe']) 
  {
    $smarty -> assign("Message", "Nie wybudowano jeszcze machin na tym świecie.<br /><br />");
  } 
 else 
   {
    $smarty -> assign("Message", '');
   }
while (!$objMachines->EOF) 
  {
    $arrTribes[] = $objMachines->fields['tribe'];
    $arrLeaders[] = $objMachines->fields['leader'];
    $arrDates[] = $objMachines->fields['bdate'];
    $objMachines->MoveNext();
  }
$objMachines->Close();

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Ttribe" => "Klan",
			"Towned" => "dowodzony przez",
			"Tbuild" => ", wybudował Astralną Machinę dnia",
			"Tera" => "ery.",
			"Tremember" => "Pamiętaj, iż pewnego dnia i twój klan może znaleźć się w tym panteonie. Tak więc - do pracy!",
			"Hof2info" => "Stoisz w potężnym, granitowym budynku. Jest to Galeria Machin - miejsce gdzie znaleźć możesz historię najlepszych klanów w historii świata. Na ścianach wiszą niewielkie, złote tabliczki, na których zapisano informacje o zwycięzcach.",
			"Tribes" => $arrTribes,
			"Leaders" => $arrLeaders,
			"Dates" => $arrDates));
$smarty -> display ('hof2.tpl');

require_once("includes/foot.php");
?>
