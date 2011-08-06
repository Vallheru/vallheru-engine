<?php
/**
 *   File functions:
 *  Site footer, close gzip compression, show players list
 *
 *   @name                 : foot.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.1
 *   @since                : 13.04.2006
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
// $Id: foot.php 143 2006-04-13 17:55:41Z thindil $

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/foot.php");
$span = (time() - 180);
$objQuery = $db -> Execute("SELECT `id`, `rank`, `user` FROM `players` WHERE `lpv`>=".$span." ORDER BY `id` ASC");

$objPlayers = $db -> Execute("SELECT count(*) FROM `players`");
$intPlayers = $objPlayers -> fields['count(*)'];
$objPlayers -> Close();

$intNumo = 0;
$arrplayers = array();
while (!$objQuery -> EOF) 
{
    if ($objQuery -> fields['rank'] == 'Admin') 
    {
        $arrplayers[$intNumo] = "<img src=\"images/admin.gif\" title=\"".KING."\" alt=\"".KING."\" /> <a href=\"view.php?view=".$objQuery -> fields['id']."\">".$objQuery -> fields['user']."</a> (".$objQuery -> fields['id'].")<br />";
    } 
        elseif ($objQuery -> fields['rank'] == 'Staff') 
    {
        $arrplayers[$intNumo] = "<img src=\"images/staff.gif\" title=\"".PRINCE."\" alt=\"".PRINCE."\" /> <a href=\"view.php?view=".$objQuery -> fields['id']."\">".$objQuery -> fields['user']."</a> (".$objQuery -> fields['id'].")<br />";
    } 
        elseif ($objQuery -> fields['rank'] == 'Prawnik') 
    {
        $arrplayers[$intNumo] = "<img src=\"images/law.gif\" title=\"".LAWYER."\" alt=\"".LAWYER."\" /> <a href=\"view.php?view=".$objQuery -> fields['id']."\">".$objQuery -> fields['user']."</a> (".$objQuery -> fields['id'].")<br />";
    } 
        elseif ($objQuery -> fields['rank'] == 'Królewski Błazen')
    {
        $arrplayers[$intNumo] = "<img src=\"images/joker.gif\" title=\"".JOKER."\" alt=\"".JOKER."\" /> <a href=\"view.php?view=".$objQuery -> fields['id']."\">".$objQuery -> fields['user']."</a> (".$objQuery -> fields['id'].")<br />";
    }
        else 
    {
        $arrplayers[$intNumo] = "<a href=\"view.php?view=".$objQuery -> fields['id']."\">".$objQuery -> fields['user']."</a> (".$objQuery -> fields['id'].")<br />";
    }
    $intNumo ++;
    $objQuery -> MoveNext();
}
$objQuery -> Close();
$db -> LogSQL(false);
list($a_dec, $a_sec) = explode(' ', $start_time);
list($b_dec, $b_sec) = explode(' ', microtime());
$duration = sprintf("%0.3f", $b_sec - $a_sec + $b_dec - $a_dec);
if ($player -> rank == 'Admin') 
{
    $sqltime = 0;
    $query = $db -> Execute("SELECT `timer` FROM `adodb_logsql`");
    $numquery = 0;
    while (!$query -> EOF) 
    {
        $sqltime = $sqltime + $query -> fields['timer'];
        $numquery ++;
        $query -> MoveNext();
    }
    $query -> Close();
    $phptime = round($duration - $sqltime, 3);
    $sqltime = round($sqltime, 3);
    $show = 1;
} 
    else 
{
    $show = 0;
}
$db -> Execute("TRUNCATE TABLE `adodb_logsql`");

/**
* Initialization of variables
*/
if (!isset($numquery)) 
{
    $numquery = 0;
}
if (!isset($phptime)) 
{
    $phptime = 0;
}
if (!isset($sqltime)) 
{
    $sqltime = 0;
}

$numquery = $numquery ++;
$db -> Close();
if (isset($compress)) 
{
    $comp = YES;
} 
    else 
{
    $comp = NO;
}
if (!isset($do_gzip_compress)) 
{
    $do_gzip_compress = false;
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
    "Playersonline" => PLAYERS_ONLINE,
    "Loadingtime" => LOADING_TIME,
    "Gzipcomp" => GZIP_COMP,
    "Pmtime" => PM_TIME,
    "Queries" => QUERIES,
    "Numquery" => $numquery,
    "Filename" => $strFilename,
    "Asource" => SOURCE));
$smarty -> display ('footer.tpl');
/*
if ( $do_gzip_compress )
{
    //
    // Borrowed from php.net!
    //
    $gzip_contents = ob_get_contents();
    ob_end_clean();

    $gzip_size = strlen($gzip_contents);
    $gzip_crc = crc32($gzip_contents);

    $gzip_contents = gzcompress($gzip_contents, 9);
    $gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

    echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
    echo $gzip_contents;
    echo pack('V', $gzip_crc);
    echo pack('V', $gzip_size);
}*/
session_write_close();
exit;
?>

