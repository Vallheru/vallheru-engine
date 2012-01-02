<?php
/**
 *   File functions:
 *   Game time
 *
 *   @name                 : tower.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 02.01.2012
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

$title = "Zegar miejski";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/tower.php");

$objAge = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='age'");
$objDay = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='day'");

/**
 * Count time to reset
 */
require_once('includes/counttime.php');
$arrTime = counttime();

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Text1" => TEXT1,
                        "Text2" => TEXT2,
                        "Text3" => TEXT3,
                        "Text4" => "Odzyskanie punktów życia i punktów magii dodatkowo o godzinach 14, 16, 18, 20, 22, 24",
			"Text5" => "O pełnych godzinach oraz 20 i 40 minut po nich odzyskuje się ".(round($player->max_energy / 72, 2))." punktów energii.",
                        "Tage" => $objAge -> fields['value'],
                        "Tday" => $objDay -> fields['value'],
                        "Thours" => $arrTime[0],
                        "Tminutes" => $arrTime[1],
                        "Tage2" => T_AGE2,
                        "Tday2" => T_DAY2));
$smarty -> display ('tower.tpl');

$objAge -> Close();
$objDay -> Close();

require_once("includes/foot.php");
?>
