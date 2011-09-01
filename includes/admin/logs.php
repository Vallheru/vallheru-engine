<?php
/**
 *   File functions:
 *   Players logs
 *
 *   @name                 : logs.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 01.09.2011
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

$objAmount = $db -> Execute("SELECT count(*) FROM `logs`");
$intPages = ceil($objAmount -> fields['count(*)'] / 50);
$objAmount -> Close();
if (isset($_GET['page']))
  {
    $_GET['page'] = intval($_GET['page']);
       if ($_GET['page'] == 0)
	 {
	   error(ERROR);
	 }
       $page = $_GET['page'];
  }
else
  {
    $page = 1;
  }
$objLogs = $db -> SelectLimit("SELECT `owner`, `log` FROM `logs`", 50, 50 * ($page - 1));
$arrOwner = array();
$arrLog = array();
while (!$objLogs -> EOF)
  {
    $arrOwner[] = $objLogs -> fields['owner'];
    $arrLog[] = $objLogs -> fields['log'];
    $objLogs -> MoveNext();
  }
$objLogs -> Close();
$smarty -> assign(array("Logsinfo" => "Tutaj możesz przeglądać logi z niektórych akcji graczy.",
			"Lowner" => "Właściciel (ID)",
			"Ltext" => "Treść",
			"Lclear" => "Wyczyść",
			"Aowner" => $arrOwner,
			"Alog" => $arrLog,
			"Page" => $page,
			"Pages" => $intPages));
/**
 * Clear logs
 */
if (isset($_GET['step']) && $_GET['step'] == 'clear')
  {
    $db -> Execute("TRUNCATE TABLE `logs`") or die($db -> ErrorMsg());
    $smarty -> assign("Message", "Logi wyczyczone");
  }
?>