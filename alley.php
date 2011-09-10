<?php
/**
 *   File functions:
 *   List of donators to game
 *
 *   @name                 : alley.php                            
 *   @copyright            : (C) 2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 10.09.2011
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
require_once("languages/".$player -> lang."/alley.php");

$objDonators = $db -> Execute("SELECT name FROM donators ORDER BY name");
$arrDonators = array();
$i = 0;
while (!$objDonators -> EOF)
{
    $arrDonators[$i] = $objDonators -> fields['name'];
    $i ++;
    $objDonators -> MoveNext();
}
$objDonators -> Close();

/**
 * Assign variables to template and display page
 */
$smarty -> assign(array("Donators" => $arrDonators,
                        "Alleyinfo" => ALLEY_INFO));
$smarty -> display ('alley.tpl');

require_once("includes/foot.php");
?>
