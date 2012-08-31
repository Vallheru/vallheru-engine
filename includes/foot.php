<?php
/**
 *   File functions:
 *  Site footer, close gzip compression, show players list
 *
 *   @name                 : foot.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 31.08.2012
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

/**
* Get the localization for game
*/
require_once("languages/".$lang."/foot.php");
$span = (time() - 180);
$player->save();
$objQuery = $db -> Execute("SELECT `id`, `rank`, `user`, `tribe` FROM `players` WHERE `lpv`>=".$span." ORDER BY `id` ASC");

$objPlayers = $db -> Execute("SELECT count(`id`) FROM `players`");
$intPlayers = $objPlayers -> fields['count(`id`)'];
$objPlayers -> Close();

$intNumo = 0;
$arrplayers = array();
while (!$objQuery -> EOF) 
{
  if (!array_key_exists($objQuery->fields['tribe'], $arrTags))
    {
      $objQuery->fields['tribe'] = 0;
    }
  $arrplayers[$intNumo] = array('id' => $objQuery->fields['id'],
				'user' => $objQuery->fields['user'],
				'prefix' => $arrTags[$objQuery->fields['tribe']][0],
				'suffix' => $arrTags[$objQuery->fields['tribe']][1]);
  switch ($objQuery -> fields['rank'])
    {
    case 'Admin':
      $arrplayers[$intNumo]['title'] = 'Król';
      $arrplayers[$intNumo]['image'] = 'admin';
      break;
    case 'Staff':
      $arrplayers[$intNumo]['title'] = 'Książę';
      $arrplayers[$intNumo]['image'] = 'staff';
      break;
    case 'Prawnik':
      $arrplayers[$intNumo]['title'] = 'Prawnik';
      $arrplayers[$intNumo]['image'] = 'law';
      break;
    case 'Królewski Błazen':
      $arrplayers[$intNumo]['title'] = 'Królewski Błazen';
      $arrplayers[$intNumo]['image'] = 'joker';
      break;
    case 'Sędzia':
      $arrplayers[$intNumo]['title'] = 'Sędzia';
      $arrplayers[$intNumo]['image'] = 'judge';
      break;
    case 'Redaktor':
      $arrplayers[$intNumo]['title'] = 'Redaktor';
      $arrplayers[$intNumo]['image'] = 'redactor';
      break;
    default:
      $arrplayers[$intNumo]['title'] = '';
      $arrplayers[$intNumo]['image'] = '';
      break;
    }
    $intNumo ++;
    $objQuery -> MoveNext();
}
$objQuery -> Close();
if ($intNumo == 1)
  {
    $strOnline = "gracz w grze";
  }
else
  {
    $strOnline = PLAYERS_ONLINE;
  }
$duration = round(microtime(true) - $start_time, 3);
$sqltime = round($sqltime, 3);
$fltMemusage = memory_get_usage(true) / 1048576.0;
if ($player -> rank == 'Admin') 
{
    $phptime = round($duration - $sqltime, 3);
    $show = 1;
} 
    else 
{
  $phptime = 0;
  $show = 0;
}
$db -> Close();
if ($compress) 
{
    $comp = YES;
} 
    else 
{
    $comp = NO;
}

$arrFilename = explode('/', $_SERVER['SCRIPT_NAME']);
$intFile = (count($arrFilename) - 1);
$strFilename = $arrFilename[$intFile];

/**
* Assign variables and show page
*/
$smarty -> assign (array ("Players" => $intPlayers, 
			  "Online" => $intNumo, 
			  "List" => $arrplayers, 
			  "Duration" => $duration, 
			  "Show" => $show, 
			  "Compress" => $comp, 
			  "Sqltime" => $sqltime, 
			  "PHPtime" => $phptime,
			  "Statistics" => STATISTICS,
			  "Playerslist" => PLAYERS_LIST,
			  "Registeredplayers" => REGISTERED_PLAYERS,
			  "Playersonline" => $strOnline,
			  "Loadingtime" => LOADING_TIME,
			  "Gzipcomp" => GZIP_COMP,
			  "Pmtime" => PM_TIME,
			  "Queries" => QUERIES,
			  "Numquery" => $numquery,
			  "Filename" => $strFilename,
			  "Memory" => "Użycie pamięci",
			  "Memusage" => $fltMemusage,
			  "MB" => "MB",
			  "Asource" => SOURCE));
$smarty -> display ('footer.tpl');
if ($compress)
  {
    ob_end_flush();
  }
session_write_close();
exit;
?>

