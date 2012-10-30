<?php
/**
 *   File functions:
 *   Magic tower - buy spells, staffs and capes
 *
 *   @name                 : wieza.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 30.10.2012
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

$title = "Magiczna wieża";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/wieza.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error ('Nie znajdujesz się w mieście.');
}

if (isset($_GET['buy']))
  {
    checkvalue($_GET['buy']);
    if ($_GET['type'] == 'S') 
      {
        $czary = $db -> Execute("SELECT * FROM `czary` WHERE `id`=".$_GET['buy']);
        $test = $db -> Execute("SELECT `id` FROM `czary` WHERE `nazwa`='".$czary -> fields['nazwa']."' AND `element`='".$czary->fields['element']."' AND `gracz`=".$player -> id);
        if ($test -> fields['id']) 
	  {
            message('error', YOU_HAVE);
	  }
        elseif (!$czary -> fields['id']) 
	  {
            message('error', NO_SPELL);
	  }
        elseif ($czary -> fields['poziom'] > $player->skills['magic'][1]) 
	  {
            message('error', TOO_LOW);
	  }
        elseif ($czary -> fields['cena'] > $player -> credits) 
	  {
            message('error', NO_MONEY);
	  }
        elseif ($player -> clas != 'Mag' && ($czary -> fields['typ'] == 'B' || $czary -> fields['typ'] == 'O')) 
	  {
            message('error', ONLY_MAGE);
	  }
	else
	  {
	    $db -> Execute("INSERT INTO czary (`gracz`, `nazwa`, `cena`, `poziom`, `typ`, `obr`, `status`, `element`) VALUES(".$player -> id.",'".$czary -> fields['nazwa']."',".$czary -> fields['cena'].",".$czary -> fields['poziom'].",'".$czary -> fields['typ']."',".$czary -> fields['obr'].",'U', '".$czary->fields['element']."')");
	    message("success", YOU_PAY.$czary -> fields['cena'].AND_BUY.$czary -> fields['nazwa']."</b>.");
	    $player->credits -= $czary -> fields['cena'];
	    $db->Execute("UPDATE `players` SET `credits`=`credits`-".$czary->fields['cena']." WHERE `id`=".$player->id);
	  }
	$test -> Close();
        $czary -> Close();
      } 
    elseif ($_GET['type'] == 'I') 
      {
        $items = $db -> Execute("SELECT * FROM mage_items WHERE id=".$_GET['buy']);
        if (!$items -> fields['id']) 
	  {
	    message('error', NO_ITEM);
	  }
        elseif ($items -> fields['minlev'] > $player->skills['magic'][1]) 
	  {
            message('error', TOO_LOW);
	  }
        elseif ($items -> fields['cost'] > $player -> credits) 
	  {
            message('error', NO_MONEY);
	  }
        elseif ($player -> clas != 'Mag') 
	  {
            message('error', ONLY_MAGE2);
	  }
	else
	  {
	    $newcost = ceil($items -> fields['cost'] * 0.75);
	    $db -> Execute("INSERT INTO equipment (owner, name, cost, minlev, type, power, status) VALUES(".$player -> id.",'".$items -> fields['name']."',".$newcost.",".$items -> fields['minlev'].",'".$items -> fields['type']."',".$items -> fields['power'].",'U')");
	    message("success", YOU_PAY.$items -> fields['cost'].AND_BUY2.$items -> fields['name']."</b>.");
	    $db->Execute("UPDATE `players` SET `credits`=`credits`-".$items->fields['cost']." WHERE `id`=".$player->id);
	    $player->credits -= $items -> fields['cost'];
	  }
        $items -> Close();
      }
  }

$smarty -> assign(array("Towerinfo" => TOWER_INFO,
			"Abuys" => 'Kup czary bojowe',
			"Abuys2" => 'Kup czary obronne',
			"Abuys3" => 'Kup czary użytkowe',
			"Abuyc" => A_BUY_C,
			"Abuyst" => A_BUY_ST,
			"Tname" => T_NAME,
			"Tcost" => T_COST,
			"Tlevel" => T_LEVEL,
			"Toptions" => T_OPTIONS,
			"Abuy" => A_BUY));

if (isset ($_GET['dalej'])) 
  {
    if (!in_array($_GET['dalej'], array('T', 'C', 'B', 'O', 'U'))) 
      {
	error (ERROR);
      }
    if (in_array($_GET['dalej'], array('B', 'O', 'U'))) 
      {
	switch ($_GET['dalej'])
	  {
	  case 'B':
	    $strPower = 'Obrażenia';
	    break;
	  case 'O':
	    $strPower = 'Obrona';
	    break;
	  case 'U':
	    $strPower = 'Efekt';
	    break;
	  default:
	    break;
	  }
	$objSpells = $db->Execute("SELECT * FROM `czary` WHERE `gracz`=0 AND `status`='S' AND `typ`='".$_GET['dalej']."' AND `poziom`<=".$player->skills['magic'][1]." ORDER BY `poziom` ASC");
	$objOwned = $db->Execute("SELECT `nazwa`, `element` FROM `czary` WHERE `gracz`=".$player->id." AND `typ`='".$_GET['dalej']."'");
	$arrOwned = array();
	$arrOelement = array();
	while (!$objOwned->EOF)
	  {
	    $arrOwned[] = $objOwned->fields['nazwa'];
	    $arrOelement[] = $objOwned->fields['element'];
	    $objOwned->MoveNext();
	  }
	$objOwned->Close();
	$arrSpells = array("Ziemia" => array(), "Woda" => array(), "Powietrze" => array(), "Ogień" => array());
	$arrElements = array('earth' => 'Ziemia', 'water' => 'Woda', 'wind' => 'Powietrze', 'fire' => 'Ogień');
	$strElement = "Zywioł:";
	while (!$objSpells->EOF)
	  {
	    if (in_array($objSpells->fields['nazwa'], $arrOwned))
	      {
		if ($objSpells->fields['typ'] != 'U')
		  {
		    $objSpells->MoveNext();
		    continue;
		  }
		else
		  {
		    $intKey = array_search($objSpells->fields['nazwa'], $arrOwned);
		    if ($objSpells->fields['element'] == $arrOelement[$intKey])
		      {
			$objSpells->MoveNext();
			continue;
		      }
		  }
	      }
	    $Key = $arrElements[$objSpells->fields['element']];
	    if ($objSpells->fields['typ'] == 'B')
	      {
		$strEffect = $objSpells->fields['obr'].S_POWER;
	      }
	    elseif ($objSpells->fields['typ'] == 'O')
	      {
		$strEffect = $objSpells->fields['obr'].S_POWER2;
	      }
	    elseif ($objSpells->fields['nazwa'] == 'Ulepszenie przedmiotu') 
	      {
		$strEffect = S_POWER3;
	      }
	    elseif ($objSpells->fields['nazwa'] == 'Utwardzenie przedmiotu') 
	      {
		$strEffect = S_POWER4;
	      }
	    elseif ($objSpells->fields['nazwa'] == 'Umagicznienie przedmiotu') 
	      {
		$strEffect = S_POWER5;
	      }
	    $arrSpells[$Key][] = array("id" => $objSpells->fields['id'],
				       "name" => $objSpells->fields['nazwa'],
				       "effect" => $strEffect,
				       "price" => $objSpells->fields['cena'],
				       "level" => $objSpells->fields['poziom']);
	    $objSpells->MoveNext();
	  }
	$objSpells->Close();
	$smarty -> assign(array("Spells" => $arrSpells,
				"Tpower" => $strPower,
				"Telement" => $strElement));
      } 
    else 
      {
	$items = $db -> Execute("SELECT * FROM `mage_items` WHERE `type`='".$_GET['dalej']."' ORDER BY `cost` ASC");
	$arrname = array();
	$arrpower = array();
	$arrcost = array();
	$arrlevel = array();
	$arrid = array();
	while (!$items -> EOF) 
	  {
	    if ($items -> fields['type'] == 'T') 
	      {
		$arrpower[] = ST_POWER;
	      } 
	    else 
	      {
		$arrpower[] = "+".$items -> fields['power'].C_POWER;
	      }
	    $arrname[] = $items -> fields['name'];
	    $arrcost[] = $items -> fields['cost'];
	    $arrlevel[] = $items -> fields['minlev'];
	    $arrid[] = $items -> fields['id'];
	    $items -> MoveNext();
	  }
	$items -> Close();
	$smarty -> assign(array("Name" => $arrname, 
				"Power" => $arrpower, 
				"Cost" => $arrcost, 
				"Itemlevel" => $arrlevel, 
				"Itemid" => $arrid,
				"Tpower" => 'Siła'));
      }
  }
else
  {
    $_GET['dalej'] = '';
  }

/**
* Assign variables to template and display page
*/
$smarty -> assign("Next", $_GET['dalej']);
$smarty -> display('wieza.tpl');

require_once("includes/foot.php"); 
?>
