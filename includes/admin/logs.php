<?php
/**
 *   File functions:
 *   Players logs
 *
 *   @name                 : logs.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 30.08.2011
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

if (!isset($_GET['limit']))
  {
    $_GET['limit'] = 0;
  }
$objAmount = $db -> Execute("SELECT count(*) FROM `logs`");
$intAmount = $objAmount -> fields['count(*)'];
$objAmount -> Close();
if (!$intAmount || $_GET['limit'] > $intAmount)
  {
    error("Brak logów");
  }
$objLogs = $db -> SelectLimit("SELECT `owner`, `log` FROM `logs`", 50, $_GET['limit']);
$arrOwner = array();
$arrLog = array();
$i = 0;
while (!$objLogs -> EOF)
  {
    $arrOwner[$i] = $objLogs -> fields['owner'];
    $arrLog[$i] = $objLogs -> fields['log'];
    $i++;
    $objLogs -> MoveNext();
  }
$objLogs -> Close();
if ($_GET['limit'] >= 50) 
  {
    $intLimit = $_GET['limit'] - 50;
    $strPrevious = "<a href=\"admin.php?view=logs&amp;limit=".$intLimit."\">Poprzednie wpisy</a>";
    }
 else
   {
     $strPrevious = '';
   }
$intLimit = $_GET['limit'] + 50;
if ($intLimit < $intAmount && $intAmount > 50)
  {
    $strNext = "<a href=\"admin.php?view=logs&amp;limit=".$intLimit."\">Następne wpisy</a>";
  }
 else
   {
     $strNext = '';
   }
$smarty -> assign(array("Logsinfo" => "Tutaj możesz przeglądać logi z niektórych akcji graczy.",
			"Lowner" => "Właściciel (ID)",
			"Ltext" => "Treść",
			"Lclear" => "Wyczyść",
			"Aowner" => $arrOwner,
			"Alog" => $arrLog,
			"Aprevious" => $strPrevious,
			"Anext" => $strNext));
/**
 * Clear logs
 */
if (isset($_GET['step']) && $_GET['step'] == 'clear')
  {
    $db -> Execute("TRUNCATE TABLE `logs`") or die($db -> ErrorMsg());
    $smarty -> assign("Message", "Logi wyczyczone");
  }
?>