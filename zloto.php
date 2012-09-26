<?php
/**
 *   File functions:
 *   Show players minerals, herbs, gold and maps
 *
 *   @name                 : zloto.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 26.09.2012
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

$title = "Bogactwa";
require_once("includes/head.php");

//Get herbs and seeds
$gr1 = $db -> Execute("SELECT * FROM `herbs` WHERE `gracz`=".$player -> id);
if ($gr1->fields['id'])
  {
    $arrHerbs = array(array("Illani", $gr1->fields['illani']),
		      array("Nasiona Illani", $gr1->fields['ilani_seeds']),
		      array("Illanias", $gr1->fields['illanias']),
		      array("Nasiona Illanias", $gr1->fields['illanias_seeds']),
		      array("Nutari", $gr1->fields['nutari']),
		      array("Nasiona Nutari", $gr1->fields['nutari_seeds']),
		      array("Dynallca", $gr1->fields['dynallca']),
		      array("Nasiona Dynallca", $gr1->fields['dynallca_seeds']));
		      
  }
else
  {
    $arrHerbs = array(array("Illani", 0),
		      array("Nasiona Illani", 0),
		      array("Illanias", 0),
		      array("Nasiona Illanias", 0),
		      array("Nutari", 0),
		      array("Nasiona Nutari", 0),
		      array("Dynallca", 0),
		      array("Nasiona Dynallca", 0));
  }
$gr1->Close();

//Get minerals
$objOres = $db -> Execute("SELECT * FROM `minerals` WHERE `owner`=".$player -> id);
if ($objOres->fields['owner'])
  {
    $arrMinerals = array(array("Rudy miedzi", $objOres -> fields['copperore']),
			 array("Drewna sosnowego", $objOres -> fields['pine']),
			 array("Rudy cynku", $objOres -> fields['zincore']),
			 array("Drewna z leszczyny", $objOres -> fields['hazel']),
			 array("Rudy cyny", $objOres -> fields['tinore']),
			 array("Drewna cisowego", $objOres -> fields['yew']),
			 array("Rudy żelaza", $objOres -> fields['ironore']),
			 array("Drewna z wiązu", $objOres->fields['elm']),
			 array("Sztabek miedzi", $objOres -> fields['copper']),
			 array("Sztabek brązu", $objOres -> fields['bronze']),
			 array("Sztabek mosiądzu", $objOres -> fields['brass']),
			 array("Sztabek żelaza", $objOres -> fields['iron']),
			 array("Sztabek stali", $objOres -> fields['steel']),
			 array("Brył węgla", $objOres -> fields['coal']),
			 array("Brył adamantium", $objOres -> fields['adamantium']),
			 array("Kawałków meteorytu", $objOres -> fields['meteor']),
			 array("Kryształów", $objOres -> fields['crystal']));
			 
  }
else
  {
    $arrMinerals = array(array("Rudy miedzi", 0),
			 array("Drewna sosnowego", 0),
			 array("Rudy cynku", 0),
			 array("Drewna z leszczyny", 0),
			 array("Rudy cyny", 0),
			 array("Drewna cisowego", 0),
			 array("Rudy żelaza", 0),
			 array("Drewna z wiązu", 0),
			 array("Sztabek miedzi", 0),
			 array("Sztabek brązu", 0),
			 array("Sztabek mosiądzu", 0),
			 array("Sztabek żelaza", 0),
			 array("Sztabek stali", 0),
			 array("Brył węgla", 0),
			 array("Brył adamantium", 0),
			 array("Kawałków meteorytu", 0),
			 array("Kryształów", 0));
  }
$objOres -> Close();

$smarty -> assign(array("Refs" => $player->vallars, 
			"Maps" => $player -> maps, 
			"Gold" => $player -> credits, 
			"Bank" => $player -> bank, 
			"Mithril" => $player -> platinum,
			"Herbs" => $arrHerbs,
			"Minerals" => $arrMinerals,
			"Itemsinfo" => "Tutaj jest lista posiadanych przez ciebie pieniędzy oraz różnych minerałów.",
			"Goldinhands" => "Sztuk złota przy sobie",
			"Goldinbank" => "Sztuk złota w banku",
			"Tmithril" => "Sztuk Mithrilu",
			"Trefs" => "Vallary",
			"Tmaps" => "Mapy",
			"TMinerals" => "Minerały",
			"Lumber" => "Drewno",
			"Seeds" => "Nasiona",
			"THerbs" => "Zioła"));

$smarty -> display ('zloto.tpl');

require_once("includes/foot.php");
?>
