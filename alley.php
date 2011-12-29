<?php
/**
 *   File functions:
 *   List of donators to game
 *
 *   @name                 : alley.php                            
 *   @copyright            : (C) 2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 29.12.2011
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

$title = "Aleja zasłużonych";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/alley.php");

/**
 * Get list of donators
 */
$objDonators = $db->Execute("SELECT `name` FROM `donators` ORDER BY `name`");
$arrDonators = array();
while (!$objDonators -> EOF)
{
    $arrDonators[] = $objDonators -> fields['name'];
    $objDonators -> MoveNext();
}
$objDonators -> Close();

/**
 * Get list of Vallars
 */
$objVallars = $db->SelectLimit("SELECT `id`, `user`, `tribe`, `vallars` FROM `players` ORDER BY `vallars` DESC", 10);
$arrVallars = array('<b><u>Imię (ID)</u></b>', '<b><u>Vallarów</u></b>');
while (!$objVallars->EOF)
  {
    $arrVallars[] = '<a href="view.php?view='.$objVallars->fields['id'].'">'.$arrTags[$objVallars->fields['tribe']][0].' '.$objVallars->fields['user'].' '.$arrTags[$objVallars->fields['tribe']][1].'</a> ('.$objVallars->fields['id'].')';
    $arrVallars[] = $objVallars->fields['vallars'];
    $objVallars->MoveNext();
  }
$objVallars->Close();

/**
 * Assign variables to template and display page
 */
$smarty -> assign(array("Donators" => $arrDonators,
			"Vallars" => $arrVallars,
                        "Alleyinfo" => ALLEY_INFO,
			'Alleyinfo2' => 'Dodatkowo możesz wspierać grę poprzez zgłaszanie propozycji, błędów oraz zachęcanie innych do gry. Za taką pomoc otrzymujesz specjalne punkty zwane Vallarami. Poniżej znajduje się lista osób, które zdobyły najwięcej Vallarów.'));
$smarty -> display ('alley.tpl');

require_once("includes/foot.php");
?>
