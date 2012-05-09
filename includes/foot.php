<?php
/**
 *   File functions:
 *  Site footer, close gzip compression, show players list
 *
 *   @name                 : foot.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 09.05.2012
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
require_once("languages/".$player->lang."/foot.php");
$span = (time() - 180);
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
  $strLink = $arrTags[$objQuery->fields['tribe']][0].' <a href="view.php?view='.$objQuery -> fields['id'].'">'.$objQuery->fields['user'].'</a> '.$arrTags[$objQuery->fields['tribe']][1];
  switch ($objQuery -> fields['rank'])
    {
    case 'Admin':
      $arrplayers[$intNumo] = "<img src=\"images/admin.gif\" title=\"".KING."\" alt=\"".KING."\" /> ".$strLink." (".$objQuery -> fields['id'].")<br />";
      break;
    case 'Staff':
      $arrplayers[$intNumo] = "<img src=\"images/staff.gif\" title=\"".PRINCE."\" alt=\"".PRINCE."\" /> ".$strLink." (".$objQuery -> fields['id'].")<br />";
      break;
    case 'Prawnik':
      $arrplayers[$intNumo] = "<img src=\"images/law.gif\" title=\"".LAWYER."\" alt=\"".LAWYER."\" /> ".$strLink." (".$objQuery -> fields['id'].")<br />";
      break;
    case 'Królewski Błazen':
      $arrplayers[$intNumo] = "<img src=\"images/joker.gif\" title=\"".JOKER."\" alt=\"".JOKER."\" /> ".$strLink." (".$objQuery -> fields['id'].")<br />";
      break;
    case 'Sędzia':
      $arrplayers[$intNumo] = "<img src=\"images/judge.gif\" title=\"Sędzia\" alt=\"Sędzia\" /> ".$strLink." (".$objQuery -> fields['id'].")<br />";
      break;
    case 'Redaktor':
      $arrplayers[$intNumo] = "<img src=\"images/redactor.gif\" title=\"Redaktor\" alt=\"Redaktor\" /> ".$strLink." (".$objQuery -> fields['id'].")<br />";
      break;
    default:
      $arrplayers[$intNumo] = $strLink." (".$objQuery -> fields['id'].")<br />";
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

