<?php
/**
 *   File functions:
 *   Armory shop - buying armors, legs, helmets and shields
 *
 *   @name                 : armor.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 28.01.2013
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

$title = "Płatnerz"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/armor.php");

if ($player -> location != 'Altara') 
{
    error (ERROR);
}

if (isset($_GET['buy']))
  {
     checkvalue($_GET['buy']);
     $arm = $db -> Execute("SELECT * FROM equipment WHERE id=".$_GET['buy']);
     if ($arm -> fields['id'] == 0) 
       {
	 message('error', NO_ITEM);
       }
     elseif ($arm -> fields['status'] != 'S') 
       {
	 message('error', BAD_STATUS);
       }
     elseif ($arm -> fields['cost'] > $player -> credits) 
       {
	 message('error', NO_MONEY);
       }
     else
       {
	 $newcost = ceil($arm -> fields['cost'] * .75);
	 $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arm -> fields['name']."' AND wt=".$arm -> fields['wt']." AND type='".$arm -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$arm -> fields['power']." AND zr=".$arm -> fields['zr']." AND szyb=".$arm -> fields['szyb']." AND maxwt=".$arm -> fields['maxwt']." AND poison=0 AND cost=".$newcost);
	 if ($test -> fields['id'] == 0) 
	   {
	     $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, szyb, lang, repair) VALUES(".$player -> id.",'".$arm -> fields['name']."',".$arm -> fields['power'].",'".$arm -> fields['type']."',".$newcost.",".$arm -> fields['zr'].",".$arm -> fields['wt'].",".$arm -> fields['minlev'].",".$arm -> fields['maxwt'].",1,'".$arm -> fields['magic']."',".$arm -> fields['szyb'].",'".$lang."', ".$arm -> fields['repair'].")");
	   } 
	 else 
	   {
	     $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
	   }
	 $test -> Close();
	 $db -> Execute("UPDATE players SET credits=credits-".$arm -> fields['cost']." WHERE id=".$player -> id);
	 message('success', YOU_PAY.' <b>'.$arm->fields['cost'].'</b> '.AND_BUY.' <b>'.$arm->fields['name'].' '.I_POWER.' + '.$arm->fields['power'].'</b>.');
	 $arm -> Close();
       }
   }

$smarty -> assign(array("Armorinfo" => ARMOR_INFO,
			"Aarmors" => A_ARMORS,
			"Ahelmets" => A_HELMETS,
			"Alegs" => A_LEGS,
			"Ashields" => A_SHIELDS));
if (isset($_GET['dalej'])) 
  {
    /**
     * Show aviable armors
     */
    if ($_GET['dalej'] != 'A' && $_GET['dalej'] != 'H' && $_GET['dalej'] != 'L' && $_GET['dalej'] != 'S') 
      {
	error (ERROR);
      }
    $arrname = array();
    $arrcost = array();
    $arrlevel = array();
    $arrid = array();
    $arrdur = array();
    $arrpower = array();
    $arragility = array();
    $arm = $db -> Execute("SELECT * FROM equipment WHERE type='".$_GET['dalej']."' AND status='S' AND owner=0 AND lang='".$lang."' ORDER BY cost ASC");
    while (!$arm -> EOF) 
      {
	$arrname[] = $arm -> fields['name'];
	$arrcost[] = $arm -> fields['cost'];
	$arrlevel[] = $arm -> fields['minlev'];
	$arrid[] = $arm -> fields['id'];
	$arrdur[] = $arm -> fields['wt'];
	$arrpower[] = $arm -> fields['power'];
	$arragility[] = $arm -> fields['zr'];
	$arm -> MoveNext();
      }
    $arm -> Close();
    $smarty -> assign(array("Name" => $arrname, 
			    "Cost" => $arrcost, 
			    "Level" => $arrlevel, 
			    "Id" => $arrid, 
			    "Durability" => $arrdur, 
			    "Power" => $arrpower, 
			    "Agility" => $arragility,
			    "Iname" => I_NAME,
			    "Idur" => I_DUR,
			    "Iefect" => I_EFECT,
			    "Icost" => I_COST,
			    "Ilevel" => I_LEVEL,
			    "Iagi" => I_AGI,
			    "Ioption" => I_OPTION,
			    "Abuy" => A_BUY,
			    "Asteal" => A_STEAL));
  }

/**
* Stealing items from shop
*/
if (isset ($_GET['steal'])) 
{
    require_once("includes/steal.php");
    require_once("includes/checkexp.php");
    steal($_GET['steal']);
}
if ($player -> clas != 'Złodziej') 
{
    $player -> crime = 0;
}

/**
* Initialization of variables and assign variables to template
*/
if (!isset($_GET['dalej'])) 
{
    $_GET['dalej'] = '';
}

if ($player->clas == 'Złodziej' && $player->energy > 2)
  {
    $intCrime = 1;
  }
 else
   {
     $intCrime = 0;
   }

/**
* Assign variables and display page
*/
$smarty -> assign(array("Next" => $_GET['dalej'], 
			"Crime" => $intCrime));
$smarty -> display ('armor.tpl');

require_once("includes/foot.php");
?>
