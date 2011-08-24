<?php
/**
 *   File functions:
 *   Show players minerals, herbs, gold and maps
 *
 *   @name                 : zloto.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 24.08.2011
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

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/zloto.php");

$gr1 = $db -> Execute("SELECT id, nutari, illani, illanias, dynallca, ilani_seeds, illanias_seeds, nutari_seeds, dynallca_seeds FROM herbs WHERE gracz=".$player -> id);
$smarty -> assign(array("Refs" => $player->vallars, 
    "Maps" => $player -> maps, 
    "Gold" => $player -> credits, 
    "Bank" => $player -> bank, 
    "Mithril" => $player -> platinum,
    "Itemsinfo" => ITEMS_INFO,
    "Goldinhands" => GOLD_IN_HANDS,
    "Goldinbank" => GOLD_IN_BANK2,
    "Tmithril" => T_MITHRIL,
    "Trefs" => T_REFS,
    "Tmaps" => T_MAPS,
    "Min1" => MIN1,
    "Min2" => MIN2,
    "Min3" => MIN3,
    "Min4" => MIN4,
    "Min5" => MIN5,
    "Min6" => MIN6,
    "Min7" => MIN7,
    "Min8" => MIN8,
    "Min9" => MIN9,
    "Min10" => MIN10,
    "Min11" => MIN11,
    "Min12" => MIN12,
    "Min13" => MIN13,
    "Ore1" => ORE1,
    "Ore2" => ORE2,
    "Ore3" => ORE3,
    "Ore4" => ORE4,
    "Herb1" => HERB1,
    "Herb2" => HERB2,
    "Herb3" => HERB3,
    "Herb4" => HERB4,
    "Seeds1" => SEEDS1,
    "Seeds2" => SEEDS2,
    "Seeds3" => SEEDS3,
    "Seeds4" => SEEDS4,
    "Minerals" => MINERALS,
    "Lumber" => LUMBER,
    "Seeds" => SEEDS,
    "Herbs" => HERBS));

if ($gr1 -> fields['id']) 
{
    $smarty -> assign(array("Illani" => $gr1 -> fields['illani'], 
                            "Illanias" => $gr1 -> fields['illanias'], 
                            "Nutari" => $gr1 -> fields['nutari'], 
                            "Dynallca" => $gr1 -> fields['dynallca'],
                            "Ilaniseeds" => $gr1 -> fields['ilani_seeds'],
                            "Illaniasseeds" => $gr1 -> fields['illanias_seeds'],
                            "Nutariseeds" => $gr1 -> fields['nutari_seeds'],
                            "Dynallcaseeds" => $gr1 -> fields['dynallca_seeds']));
} 
    else 
{
    $smarty -> assign(array("Illani" => 0, 
                            "Illanias" => 0, 
                            "Nutari" => 0, 
                            "Dynallca" => 0,
                            "Ilaniseeds" => 0,
                            "Illaniasseeds" => 0,
                            "Nutariseeds" => 0,
                            "Dynallcaseeds" => 0));
}

$objOres = $db -> Execute("SELECT owner, copperore, zincore, tinore, ironore, coal, copper, bronze, brass, iron, steel, pine, hazel, yew, elm, crystal, adamantium, meteor FROM minerals WHERE owner=".$player -> id) or die($db -> ErrorMsg());
if ($objOres -> fields['owner'])
{
    $smarty -> assign(array("Copperore" => $objOres -> fields['copperore'],
        "Zincore" => $objOres -> fields['zincore'],
        "Tinore" => $objOres -> fields['tinore'],
        "Ironore" => $objOres -> fields['ironore'],
        "Coal" => $objOres -> fields['coal'],
        "Copper" => $objOres -> fields['copper'],
        "Iron" => $objOres -> fields['iron'],
        "Bronze" => $objOres -> fields['bronze'],
        "Brass" => $objOres -> fields['brass'],
        "Steel" => $objOres -> fields['steel'],
        "Pine" => $objOres -> fields['pine'],
        "Hazel" => $objOres -> fields['hazel'],
        "Yew" => $objOres -> fields['yew'],
        "Elm" => $objOres -> fields['elm'],
        "Crystal" => $objOres -> fields['crystal'],
        "Adamantium" => $objOres -> fields['adamantium'],
        "Meteor" => $objOres -> fields['meteor']));
}
    else
{
    $smarty -> assign(array("Copperore" => 0,
        "Zincore" => 0,
        "Tinore" => 0,
        "Ironore" => 0,
        "Coal" => 0,
        "Copper" => 0,
        "Iron" => 0,
        "Bronze" => 0,
        "Brass" => 0,
        "Steel" => 0,
        "Pine" => 0,
        "Hazel" => 0,
        "Yew" => 0,
        "Elm" => 0,
        "Crystal" => 0,
        "Adamantium" => 0,
        "Meteor" => 0));
}
$objOres -> Close();

$smarty -> display ('zloto.tpl');

require_once("includes/foot.php");
?>
