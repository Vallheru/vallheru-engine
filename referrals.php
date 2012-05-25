<?php
/**
 *   File functions:
 *   Show referrals link and amount of referrals
 *
 *   @name                 : referrals.php                            
 *   @copyright            : (C) 2004,2005,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 25.05.2012
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

$title = "Vallary";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/referrals.php");

if (!isset($_GET['id']))
  {
    error("Zapomnij o tym!");
  }
checkvalue($_GET['id']);
$objVals = $db->Execute("SELECT `id`, `user`, `vallars` FROM `players` WHERE `id`=".$_GET['id']);
if (!$objVals->fields['id'])
  {
    error("Nie ma takiego gracza!");
  }

$arrDate = array();
$arrReason = array();
$arrAmount = array();
$objHist = $db->SelectLimit("SELECT * FROM `vallars` WHERE `owner`=".$_GET['id']." ORDER BY `vdate` DESC", 30);
while (!$objHist->EOF)
  {
    $tmpArr = explode(" ", $objHist->fields['vdate']);
    $arrDate[] = $tmpArr[0];
    $arrReason[] = $objHist->fields['reason'];
    $arrAmount[] = $objHist->fields['amount'];
    $objHist->MoveNext();
  }
if ($_GET['id'] == $player->id)
  {
    $strOwner = "posiadasz";
    $strLink = "zdobywasz";
  }
else
  {
    $strOwner = $objVals->fields['user']." posiada";
    $strLink = $objVals->fields['user']." zdobywa";
  }

$smarty -> assign(array("Adress" => $gameadress, 
			"Id" => $_GET['id'], 
			"Refs" => $objVals->fields['vallars'],
			"Date" => $arrDate,
			"Reason" => $arrReason,
			"Amount" => $arrAmount,
			"Owner" => $strOwner,
			"Linkinfo" => $strLink,
			"Tdate" => "Data",
			"Tamount" => "Ilość",
			"Treason" => "Uzasadnienie",
			"Ref4" => "jednego Vallara. Obecnie",
			"Ref1" => REF1,
			"Ref2" => REF2,
			"Ref3" => REF3 ));
$smarty -> display ('referrals.tpl');

require_once("includes/foot.php");
?>
