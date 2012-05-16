<?php
/**
 *   File functions:
 *   Game chronicle - quests and history of game
 *
 *   @name                 : chronicle.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 16.05.2012
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

$title = "Kronika";
require_once("includes/head.php");

if($player->location != 'Altara' && $player->location != 'Ardulith') 
  {
    error("Nie znajdujesz się w mieście. <a href=");
  }

//Main menu
if (!isset($_GET['step']))
  {
    $_GET['step'] = '';
    $objMissions = $db->Execute("SELECT `id`, `shortdesc`, `type` FROM `missions2` WHERE `location`='".$player->location);
    $arrStory = array();
    $arrOldstory = array();
    $arrOther = array();
    if ($objMissions->fields['id'])
      {
	while (!$objMissions->EOF)
	  {
	    switch ($objMissions->fields['type'])
	      {
	      case 'Q':
		$arrStory[$objMissions->fields['id']] = $objMissions->fields['shortdesc'];
		break;
	      case 'O':
		$arrOldstory[$objMissions->fields['id']] = $objMissions->fields['shortdesc'];
		break;
	      default:
		$arrOther[$objMissions->fields['id']] = $objMissions->fields['shortdesc'];
		break;
	      }
	    $objMissions->MoveNext();
	  }
	$objMissions->Close();
      }
    if (count($arrStory) > 0)
      {
	$arrStory[0] = 'Obecne wydarzenia:';
      }
    if (count($arrOldstory) > 0)
      {
	$arrOldstory[0] = 'Dawne historie:';
      }
    if (count($arrOther) > 0)
      {
	$arrOther[0] = 'Poboczne wydarzenia:';
      }
    $smarty->assign(array("Info" => "Wchodzisz do niewielkiego budynku. Kiedy tylko mijasz wejście, gwar dobiegający z ulicy niemal natychmiast milknie. Wnętrze budynku jest niemal puste, tylko na jego środku stoi średnich rozmiarów, kamienny pedestał a na nim znajduje się olbrzymia księga. Kiedy podchodzisz bliżej, księga zaczyna lekko lśnić białym blaskiem. Bez twojej pomocy otwiera się na spisie treści.",
			  "Story" => $arrStory,
			  "Oldstory" => $arrOldstory,
			  "Other" => $arrOther));
  }

/**
* Assign variables to template and display page
*/
$smarty -> assign("Step", $_GET['step']);
$smarty -> display ('chronicle.tpl');

require_once("includes/foot.php");
?>
