<?php
/**
 *   File functions:
 *   Weapons shop
 *
 *   @name                 : weapons.php                            
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

$title = "Zbrojmistrz";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/weapons.php");

if ($player -> location != 'Altara') 
{
    error (ERROR);
}

if (isset ($_GET['steal'])) 
{
    require_once("includes/steal.php");
    require_once("includes/checkexp.php");
    steal($_GET['steal']);
}

if (isset ($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $arm = $db -> Execute("SELECT * FROM equipment WHERE id=".$_GET['buy']);
    $blnValid = TRUE;
    if (!$arm -> fields['id']) 
      {
	message('error', NO_ITEM);
	$blnValid = FALSE;
      }
    if ($arm -> fields['status'] != 'S') 
      {
	message('error',BAD_STATUS);
	$blnValid = FALSE;
      }
    if ($arm -> fields['cost'] > $player -> credits) 
      {
        message('error', NO_MONEY);
	$blnValid = FALSE;
      }
    if ($blnValid)
      {
	$newcost = ceil($arm -> fields['cost'] * .75);
	$test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arm -> fields['name']."' AND wt=".$arm -> fields['wt']." AND type='W' AND status='U' AND owner=".$player -> id." AND power=".$arm -> fields['power']." AND zr=".$arm -> fields['zr']." AND szyb=".$arm -> fields['szyb']." AND maxwt=".$arm -> fields['maxwt']." AND poison=".$arm -> fields['poison']." AND cost=".$newcost);
	if (!$test -> fields['id']) 
	  {
	    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, repair) VALUES(".$player -> id.",'".$arm -> fields['name']."',".$arm -> fields['power'].",'".$arm -> fields['type']."',".$newcost.",".$arm -> fields['zr'].",".$arm -> fields['wt'].",".$arm -> fields['minlev'].",".$arm -> fields['maxwt'].",1,'".$arm -> fields['magic']."',".$arm -> fields['poison'].",".$arm -> fields['szyb'].",'".$arm -> fields['twohand']."', ".$arm -> fields['repair'].")");
	  } 
        else 
	  {
	    $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
	  }
	$test -> Close();
	message('success', YOU_PAY.' <b>'.$arm->fields['cost'].'</b> '.AND_BUY.' <b>'.$arm->fields['name'].' '.WITH_P.' +'.$arm->fields['power'].'</b> '.TO_POWER);
	$db -> Execute("UPDATE players SET credits=credits-".$arm -> fields['cost']." WHERE id=".$player -> id);
      }
    $arm -> Close();
}

$arrWeapons = $db->GetAll("SELECT * FROM `equipment` WHERE `type`='W' AND `status`='S' AND `owner`=0 ORDER BY `cost` ASC");

if ($player->clas == 'Złodziej' && $player->energy > 2)
  {
    $intCrime = 1;
  }
 else
   {
     $intCrime = 0;
   }

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Weapons" => $arrWeapons,
			"Weaponinfo" => WEAPON_INFO,
			"Iname" => I_NAME,
			"Idur" => I_DUR,
			"Iefect" => I_EFECT,
			"Ispeed" => I_SPEED,
			"Icost" => I_COST,
			"Ioption" => I_OPTION,
			"Abuy" => A_BUY,
			"Asteal" => A_STEAL,
			"Ilevel" => I_LEVEL,
			"Crime" => $intCrime));
$smarty -> display('weapons.tpl');

require_once("includes/foot.php");
?>
