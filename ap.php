<?php
/**
 *   File functions:
 *   Distribution of Astral Poinst
 *
 *   @name                 : ap.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 11.01.2013
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

$title = "Dystrybucja AP";
require_once("includes/head.php");

if ($player->clas == '')
  {
    error('Musisz najpierw wybrać klasę. (<a href=stats.php>Wróć</a>)');
  }
if ($player->race == '')
  {
    error('Musisz najpierw wybrać rasę postaci. (<a href=stats.php>Wróć</a>)');
  }

//Get selected bonus
if (isset($_GET['select']))
  {
    checkvalue($_GET['select']);
    $objBonus = $db->Execute("SELECT * FROM `bonuses` WHERE `id`=".$_GET['select']);
    $blnValid = TRUE;
    $intKey = -1;
    if (!isset($_POST[$_GET['select']]))
      {
	message('error', 'Zaznacz, którą premię wybierasz.');
	$blnValid = FALSE;
      }
    if (!$objBonus->fields['id'])
      {
	message('error', 'Nie ma takiej premii.');
	$blnValid = FALSE;
      }
    if (strpos($objBonus->fields['race'], $player->race) === FALSE && strpos($objBonus->fields['race'], 'All') === FALSE)
      {
	message('error', 'Ta premia nie jest dostępna dla twojej rasy.');
	$blnValid = FALSE;
      }
    if (strpos($objBonus->fields['clas'], $player->clas) === FALSE && strpos($objBonus->fields['clas'], 'All') === FALSE)
      {
	message('error', 'Ta premia nie jest dostępna dla twojej klasy.');
	$blnValid = FALSE;
      }
    $intCost = $objBonus->fields['cost'];
    foreach ($player->bonuses as $key => $arrBonus)
      {
	if ($arrBonus[0] == $objBonus->fields['id'])
	  {
	    if ($arrBonus[1] == $objBonus->fields['levels'])
	      {
		message('error', 'Osiągnąłeś już maksymalny poziom tej premii.');
		$blnValid = FALSE;
	      }
	    else
	      {
		$intCost = $intCost * ($arrBonus[1] + 1);
		$intKey = $key;
	      }
	    break;
	  }
      }
    if ($intCost > $player->ap)
      {
	message('error', 'Nie masz tylu Astralnych Punktów aby kupić tę premię.');
	$blnValid = FALSE;
      }
    if ($blnValid)
      {
	if ($intKey == -1)
	  {
	    $player->bonuses[] = array($objBonus->fields['id'], 1, $objBonus->fields['trigger'], $objBonus->fields['bonus']);
	  }
	else
	  {
	    $player->bonuses[$intKey][1]++;
	  }
	$player->ap -= $intCost;
	$arrBonuses = array();
	foreach ($player->bonuses as $arrBonus)
	  {
	    $arrBonuses[] = implode(',', $arrBonus);
	  }
	$strBonuses = implode(';', $arrBonuses).';';
	$db->Execute("UPDATE `players` SET `bonuses`='".$strBonuses."' WHERE `id`=".$player->id);
	message('success', 'Wykupiłeś poziom premii: '.$objBonus->fields['name']);
      }
    $objBonus->Close();
  }

//Select available bonuses for player
$arrBonuses = array();
$objBonuses = $db->Execute("SELECT * FROM `bonuses`");
while (!$objBonuses->EOF)
  {
    if (strpos($objBonuses->fields['race'], $player->race) === FALSE && strpos($objBonuses->fields['race'], 'All') === FALSE)
      {
	$objBonuses->MoveNext();
	continue;
      }
    if (strpos($objBonuses->fields['clas'], $player->clas) === FALSE && strpos($objBonuses->fields['clas'], 'All') === FALSE)
      {
	$objBonuses->MoveNext();
	continue;
      }
    $blnValid = TRUE;
    $intCost = $objBonuses->fields['cost'];
    $strCurlevel = 0;
    foreach ($player->bonuses as $arrBonus)
      {
	if ($arrBonus[0] == $objBonuses->fields['id'])
	  {
	    $strCurlevel = $arrBonus[1];
	    if ($arrBonus[1] == $objBonuses->fields['levels'])
	      {
		$blnValid = FALSE;
	      }
	    else
	      {
		$intCost = $intCost * ($arrBonus[1] + 1);
	      }
	    break;
	  }
      }
    if (!$blnValid)
      {
	$objBonuses->MoveNext();
	continue;
      }
    if ($strCurlevel == 0)
      {
	$strCurlevel = 'brak';
      }
    $arrBonuses[] = array('name' => $objBonuses->fields['name'],
			  'curlevel' => $strCurlevel,
			  'desc' => $objBonuses->fields['desc'],
			  'cost' => $intCost,
			  'levels' => $objBonuses->fields['levels'],
			  'id' => $objBonuses->fields['id']);
    $objBonuses->MoveNext();
  }
$objBonuses->Close();

//Assign variables to template and display page
$smarty->assign(array("Bonuses" => $arrBonuses,
		      "Info" => 'Oto lista dostępnych premii dla twojej postaci. Obecnie posiadasz '.$player->ap.' Punktów Astralnych.',
		      "Cost" => 'Koszt:',
		      "AP" => 'AP',
		      "Tlevel" => 'Obecny poziom:',
		      "Tlevels" => 'Maksymalny poziom:',
		      "Select" => 'Wybierz'));
$smarty -> display ('ap.tpl');

require_once("includes/foot.php");
?>
