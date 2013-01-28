<?php
/**
 *   File functions:
 *   Fletcher shop - buy arrows and bows
 *
 *   @name                 : bows.php                            
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

$title = "Łucznik"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/bows.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Buy bows
*/
if (isset ($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $blnValid = TRUE;
    $arm = $db -> Execute("SELECT * FROM bows WHERE id=".$_GET['buy']);
    if (!$arm -> fields['id']) 
      {
	message('error', NO_ITEM);
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
	$test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arm -> fields['name']."' AND wt=".$arm -> fields['maxwt']." AND type='B' AND status='U' AND owner=".$player -> id." AND power=".$arm -> fields['power']." AND zr=".$arm -> fields['zr']." AND szyb=".$arm -> fields['szyb']." AND maxwt=".$arm -> fields['maxwt']." AND cost=".$newcost);
	if (!$test -> fields['id']) 
	  {
	    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, szyb, twohand, repair) VALUES(".$player -> id.",'".$arm -> fields['name']."',".$arm -> fields['power'].",'B',".$newcost.",".$arm -> fields['zr'].",".$arm -> fields['maxwt'].",".$arm -> fields['minlev'].",".$arm -> fields['maxwt'].",1,".$arm -> fields['szyb'].",'Y', ".$arm -> fields['repair'].")") or error(E_DB);
	  } 
        else 
	  {
	    $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
	  }
	$test -> Close();
	$db -> Execute("UPDATE players SET credits=credits-".$arm -> fields['cost']." WHERE id=".$player -> id);
	message('success', YOU_BUY.' <b>'.$arm->fields['cost'].'</b> '.GOLD_COINS.' <b>'.$arm->fields['name'].'</b> '.WITH.' <b>'.$arm->fields['power'].'</b> '.DAMAGE);
      }
    $arm -> Close();
}

/**
* Buy arrows
*/
if (isset($_GET['arrows']))
{
    checkvalue($_GET['arrows']);
    $objArm = $db -> Execute("SELECT * FROM bows WHERE id=".$_GET['arrows']);
    if (!$objArm -> fields['id']) 
    {
        error (NO_ITEM);
    }
    $intAllcost = $objArm -> fields['cost'];
    $intOnecost = ceil($objArm -> fields['cost'] / 25);
    $smarty -> assign(array("Arrowsname" => $objArm -> fields['name'],
			    "Arrowscost" => $intAllcost,
			    "Arrowscost2" => $intOnecost,
			    "Arrowsid" => $objArm -> fields['id'],
			    "Tamount" => T_AMOUNT,
			    "Fora" => FOR_A,
			    "Tamount2" => T_AMOUNT2,
			    "Tamount3" => T_AMOUNT3,
			    "Tarrows" => T_ARROWS));
    if (isset($_GET['step']) && $_GET['step'] == 'buy')
    {
        if ((isset($_POST['arrows1']) && $_POST['arrows1'] > 0) && (isset($_POST['arrows2']) && $_POST['arrows2'] > 0))
        {
            error(ERROR);
        }
	$blnValid = TRUE;
        if (isset($_POST['arrows1']) && $_POST['arrows1'] > 0)
        {
	    checkvalue($_POST['arrows1']);
            $intCost = $intAllcost * $_POST['arrows1'];
            if ($intCost > $player -> credits) 
	      {
		message('error', NO_MONEY);
		$blnValid = FALSE;
	      }
            $intAmount = $objArm -> fields['maxwt'] * $_POST['arrows1'];
        }
        if (isset($_POST['arrows2']) && $_POST['arrows2'] > 0)
        {
	    checkvalue($_POST['arrows2']);
            $intCost = ceil($intOnecost * $_POST['arrows2']);
            if ($intCost > $player -> credits) 
	      {
                message('error', NO_MONEY);
		$blnValid = FALSE;
	      }
            $intAmount = $_POST['arrows2'];
        }
        if (((isset($_POST['arrows1']) && $_POST['arrows1'] > 0) || (isset($_POST['arrows2']) && $_POST['arrows2'] > 0)) && $blnValid)
        {
            $intNewcost = ceil($intAllcost * 0.75);
            $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$objArm -> fields['name']."' AND owner=".$player -> id." AND status='U' AND cost=".$intNewcost);
            if (!$test -> fields['id']) 
            {
                $db -> Execute("INSERT INTO equipment (owner, name, power, cost, wt, szyb, minlev, maxwt, type) VALUES(".$player -> id.",'".$objArm -> fields['name']."',".$objArm -> fields['power'].",".$intNewcost.",".$intAmount.",".$objArm -> fields['szyb'].",".$objArm -> fields['minlev'].",".$intAmount.",'".$objArm -> fields['type']."')") or error(E_DB);
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET wt=wt+".$intAmount.", maxwt=maxwt+".$intAmount." WHERE id=".$test -> fields['id']);
            }
            $test -> Close();
            $db -> Execute("UPDATE players SET credits=credits-".$intCost." WHERE id=".$player -> id);
	    message('success', YOU_BUY.' <b>'.$intCost.'</b> '.GOLD_COINS.' '.$intAmount.' <b>'.$objArm->fields['name'].'</b> '.WITH.' <b>'.$objArm->fields['power'].'</b> '.DAMAGE);														      
        }
    }
    $objArm -> Close();
}
else
  {
    $_GET['arrows'] = '';
  }

/**
* Show list items in shop
*/
$arrname = array();
$arrpower = array();
$arrspeed = array();
$arrdur = array();
$arrlevel = array();
$arrcost = array();
$arrid = array();
$arrLink = array();
$wep = $db -> Execute("SELECT * FROM `bows` ORDER BY `minlev` ASC");
while (!$wep -> EOF) 
  {
    $arrname[] = $wep -> fields['name'];
    $arrpower[] = $wep -> fields['power'];
    $arrspeed[] = $wep -> fields['szyb'];
    $arrdur[] = $wep -> fields['maxwt'];
    $arrlevel[] = $wep -> fields['minlev'];
    if ($wep->fields['type'] == 'R')
      {
	$arrcost[] = $wep->fields['cost']."/".ceil($wep->fields['cost'] / 25);
      }
    else
      {
	$arrcost[] = $wep->fields['cost'];
      }
    $arrid[] = $wep -> fields['id'];
    if ($wep -> fields['type'] == 'R')
      {
	$arrLink[] = 'arrows=';
      }
    else
      {
	$arrLink[] = 'buy=';
      }
    $wep -> MoveNext();
  }
$wep -> Close();
if ($player->location == 'Altara')
  {
    $smarty -> assign(array("Shopinfo2" => SHOP_INFO2));
  }
 else
   {
     $smarty -> assign(array("Shopinfo2" => ""));
   }
/**
* Steal items from shop
*/
if (isset ($_GET['steal'])) 
{
    require_once("includes/steal.php");
    require_once("includes/checkexp.php");
    steal($_GET['steal']);
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
$smarty -> assign ( array("Name" => $arrname, 
			  "Power" => $arrpower, 
			  "Speed" => $arrspeed, 
			  "Durability" => $arrdur, 
			  "Level" => $arrlevel, 
			  "Cost" => $arrcost, 
			  "Itemid" => $arrid,
			  "Tlink" => $arrLink,
			  "Shopinfo" => SHOP_INFO,
			  "Iname" => I_NAME,
			  "Idur" => I_DUR,
			  "Iefect" => I_EFECT,
			  "Icost" => I_COST,
			  "Ilevel" => I_LEVEL,
			  "Ispeed" => I_SPEED,
			  "Ioption" => I_OPTION,
			  "Abuy" => A_BUY,
			  "Asteal" => A_STEAL,
			  "Arrows" => $_GET['arrows'],
			  "Crime" => $intCrime,
			  "Location" => $player -> location));
$smarty -> display ('bows.tpl');

require_once("includes/foot.php");
?>
