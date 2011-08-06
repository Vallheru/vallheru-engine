<?php
/**
 *   File functions:
 *   Game time
 *
 *   @name                 : tower.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 05.10.2006
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
// $Id: tower.php 673 2006-10-05 15:32:49Z thindil $

$title = "Zegar miejski";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/tower.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

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
                        "Text4" => TEXT4,
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
