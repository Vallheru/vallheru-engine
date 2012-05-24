<?php
/**
 *   File functions:
 *   Show world map
 *
 *   @name                 : map.php                            
 *   @copyright            : (C) 2004,2005,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 23.05.2012
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

$title = "Mapa";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/map.php");

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Aaltara" => A_ALTARA,
    "Amountains" => A_MOUNTAINS,
    "Aforest" => A_FOREST,
	"Aelfcity" => A_ELFCITY));
$smarty -> display ('map.tpl');

require_once("includes/foot.php");
?>
