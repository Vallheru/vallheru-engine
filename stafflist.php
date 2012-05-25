<?php
/**
 *   File functions:
 *   Game staff list
 *
 *   @name                 : stafflist.php                            
 *   @copyright            : (C) 2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
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
// $Id$

$title = "Sala audiencyjna";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/stafflist.php");

$arrStafflist = array(array(array(),
                            array()),
                      array(array(),
                            array()),
                      array(array(),
                            array()),
                      array(array(),
                            array()),
                      array(array(),
                            array()),
                      array(array(),
                            array()),
                      array(array(),
                            array()),
                      array(array(),
                            array()),
                      array(array(),
                            array()));
$arrKey = array(0, 0, 0, 0, 0, 0, 0, 0, 0);
$arrNames = array('Admin', 'Staff', 'Karczmarka', 'Bibliotekarz', 'Marszałek Rady', 'Poseł', 'Redaktor', 'Rycerz', 'Dama'); 
$objStafflist = $db -> Execute("SELECT `id`, `user`, `rank` FROM `players` WHERE `rank`='Admin' OR `rank`='Staff' OR `rank`='Karczmarka' OR `rank`='Bibliotekarz' OR `rank`='Marszałek Rady' OR `rank`='Poseł' OR `rank`='Redaktor' OR `rank`='Rycerz' OR `rank`='Dama'") or die("Błąd!");
while (!$objStafflist -> EOF)
{
    $intKey = array_search($objStafflist -> fields['rank'], $arrNames);
    $intKey2 = $arrKey[$intKey];
    $arrStafflist[$intKey][0][$intKey2] = $objStafflist -> fields['id'];
    $arrStafflist[$intKey][1][$intKey2] = $objStafflist -> fields['user'];
    $arrKey[$intKey] = $intKey2 + 1;
    $objStafflist -> MoveNext();
}
$objStafflist -> Close();
$arrSecnames = array(SEC_NAME1, SEC_NAME2, SEC_NAME3, SEC_NAME4, SEC_NAME5);
$arrRanks = array(KINGS, STAFF, INNKEEP, LIBRARIANS, M_COUNT, COUNT, REDACTOR, KNIGHTS, LADIES);

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Listinfo" => LIST_INFO,
                        "Secnames" => $arrSecnames,
                        "Stafflist" => $arrStafflist,
                        "Ranknames" => $arrRanks,
                        "Sec3desc" => SEC3_DESC,
                        "Sec3desc2" => SEC3_DESC2,
                        "Pllocation" => $player -> location));
$smarty -> display ('stafflist.tpl');

require_once("includes/foot.php");
?>
