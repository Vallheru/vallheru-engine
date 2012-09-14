<?php
/**
 *   File functions:
 *   Players logs
 *
 *   @name                 : logs.php                            
 *   @copyright            : (C) 2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 14.09.2012
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

if (isset($_GET['lid']))
  {
    $_POST['lid'] = $_GET['lid'];
  }
if (isset($_POST['lid']))
  {
    checkvalue($_POST['lid']);
    $strQuery = " WHERE `owner`=".$_POST['lid']." ORDER BY `id` DESC";
    $strPage = '&amp;lid='.$_POST['lid'];
  }
else
  {
    $strQuery = ' ORDER BY `id` DESC';
    $strPage = '';
  }

$objAmount = $db -> Execute("SELECT count(*) FROM `logs`".$strQuery);
if ($objAmount->fields['count(*)'] == 0 && isset($_POST['lid']))
  {
    error('Nie ma logów tego gracza. (<a href="'.$_SERVER['PHP_SELF'].'?view=logs">Wróć</a>)');
  }
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
$arrLogs = $db->Execute("SELECT `owner`, `log`, `czas` FROM `logs`".$strQuery." LIMIT ".(50 * ($page - 1)).", 50");
$smarty -> assign(array("Logsinfo" => "Tutaj możesz przeglądać logi z niektórych akcji graczy.",
			"Lowner" => "Właściciel (ID)",
			"Ltime" => "Data",
			"Ltext" => "Treść",
			"Lclear" => "Wyczyść",
			"Tsearch" => "logi gracza o ID:",
			"Asearch" => "Szukaj",
			"Lid" => $strPage,
			"Logs" => $arrLogs,
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