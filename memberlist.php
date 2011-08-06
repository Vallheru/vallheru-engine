<?php
/**
 *   File functions:
 *   Players list
 *
 *   @name                 : memberlist.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 05.08.2011
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
// $Id: memberlist.php 880 2007-02-07 19:14:14Z thindil $

$title = "Lista mieszkańców"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/memberlist.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Assign variables to template
*/
$smarty -> assign(array("Previous" => '', "Next" => ''));

if (!isset($_POST['id'])) 
{
    $_POST['id'] = 0;
}

if (isset($_GET['szukany']))
{
    $_POST['szukany'] = $_GET['szukany'];
}

$strSearch = '';
if (isset($_POST['szukany']))
{
    $_POST['szukany'] = strip_tags($_POST['szukany']);
    $_POST['szukany'] = str_replace("*","%", $_POST['szukany']);
    $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
    if ($strSearch == "''")
    {
        $strSearch = '';
    }
}

if (empty($strSearch) && $_POST['id'] == 0) 
{
    $msel = $db -> Execute("SELECT count(*) FROM `players`");
} 
    else 
{
    if (!ereg("^[0-9]*$", $_POST['id'])) 
    {
        error (ERROR);
    }
    if (!empty($strSearch) && $_POST['id'] == 0) 
    {
        $msel = $db -> Execute("SELECT count(*) FROM `players` WHERE `user` LIKE ".$strSearch);
    } 
        elseif (!empty($strSearch) && $_POST['id'] > 0) 
    {
        $msel = $db -> Execute("SELECT count(*) FROM `players` WHERE `id`=".$_POST['id']." AND `user` LIKE ".$strSearch);
    } 
        elseif (empty($strSearch) && $_POST['id'] > 0) 
    {
        $msel = $db -> Execute("SELECT count(*) FROM `players` WHERE `id`=".$_POST['id']);
    }
}

$graczy = $msel -> fields['count(*)'];
$msel -> Close();
if ($graczy == 0 && isset($_POST['szukany'])) 
{
    $strMessage = NO_PLAYER.$_POST['szukany'];
}
if ($graczy == 0 && isset($_POST['id']) && $_POST['id'] > 0) 
{
    $strMessage = NO_PLAYER2.$_POST['id'];
}
if (!isset($_GET['limit'])) 
{
    $_GET['limit'] = 0;
}
if (!isset ($_GET['lista'])) 
{
    $_GET['lista'] = 'id';
}
$_GET['limit'] = intval($_GET['limit']);
if (!in_array($_GET['lista'], array('id', 'user', 'rank', 'rasa', 'level'))) 
  {
    error(ERROR);
  }
if ($_GET['limit'] < $graczy) 
{
    if (empty($_POST['szukany']) && $_POST['id'] == 0 && empty($_POST['ip'])) 
    {
        $mem = $db -> SelectLimit("SELECT `id`, `user`, `rank`, `rasa`, `level`, `gender` FROM `players` ORDER BY `".$_GET['lista']."` ASC", 30, $_GET['limit']);
    } 
        elseif  (!empty($_POST['szukany']) && $_POST['id'] == 0) 
    {
        $mem = $db -> SelectLimit("SELECT `id`, `user`, `rank`, `rasa`, `level`, `gender` FROM `players` WHERE `user` LIKE ".$strSearch." ORDER BY `".$_GET['lista']."` ASC", 30, $_GET['limit']);
    } 
        elseif (!empty($_POST['szukany']) && $_POST['id'] > 0) 
    {
        $mem = $db -> SelectLimit("SELECT `id`, `user`, `rank`, `rasa`, `level`, `gender` FROM `players` WHERE `id`=".$_POST['id']." AND `user` LIKE ".$strSearch." ORDER BY `".$_GET['lista']."` ASC", 30,  $_GET['limit']);
    } 
        elseif (empty($_POST['szukany']) && $_POST['id'] > 0) 
    {
        $mem = $db -> SelectLimit("SELECT `id`, `user`, `rank`, `rasa`, `level`, `gender` FROM `players` WHERE `id`=".$_POST['id']." ORDER BY `".$_GET['lista']."` ASC", 30, $_GET['limit']);
    }
        elseif(!empty($_POST['ip']))
    {
        if ($player -> rank != 'Admin' && $player -> rank != 'Staff')
        {
            error(NO_PERM);
        }
        $_POST['ip'] = str_replace("*","%", $_POST['ip']);
        $mem = $db -> SelectLimit("SELECT `id`, `user`, `rank`, `rasa`, `level`, `gender` FROM `players` WHERE `ip` LIKE '".$_POST['ip']."' ORDER BY `".$_GET['lista']."` ASC", 30, $_GET['limit']);
    }
    $arrrank = array();
    $arrid = array();
    $arrname = array();
    $arrrace = array();
    $arrlevel = array();
    $i = 0;
    while (!$mem -> EOF) 
    {
        /**
         * Select player rank
         */
        require_once('includes/ranks.php');
        $arrrank[$i] = selectrank($mem -> fields['rank'], $mem -> fields['gender']);

        $arrid[$i] = $mem -> fields['id'];
        $arrname[$i] = $mem -> fields['user'];
        $arrrace[$i] = $mem -> fields['rasa'];
        $arrlevel[$i] = $mem -> fields['level'];
        $mem -> MoveNext();
        $i = $i + 1;
    }
    $mem -> Close();
    $smarty -> assign(array("Memid" => $arrid, 
        "Name" => $arrname, 
        "Race" => $arrrace, 
        "Rank" => $arrrank, 
        "Level" => $arrlevel));
    if (isset($_POST['szukany']))
    {
        $strSearchpl = "&amp;szukany=".$_POST['szukany'];
    }
        else
    {
        $strSearchpl = '';
    }
    if ($_GET['limit'] >= 30) 
    {
        $lim = $_GET['limit'] - 30;
        $smarty -> assign ("Previous", "<a href=memberlist.php?limit=".$lim."&lista=".$_GET['lista'].$strSearchpl.">".PREVIOUS_PL."</a> ");
    }
    $_GET['limit'] = $_GET['limit'] + 30;
    if ($graczy > 30 && $_GET['limit'] < $graczy) 
    {
        $smarty -> assign ("Next", "<a href=memberlist.php?limit=".$_GET['limit']."&lista=".$_GET['lista'].$strSearchpl.">".NEXT_PL."</a>");
    } 
}

/**
* Initialization of variable
*/
if (!isset($strMessage))
{
    $strMessage = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Message" => $strMessage,
    "Plid" => PL_ID,
    "Plname" => PL_NAME,
    "Plrank" => PL_RANK,
    "Plrace" => PL_RACE,
    "Pllevel" => PL_LEVEL,
    "Search" => SEARCH,
    "Search2" => SEARCH2,
    "Splayer" => S_PLAYER,
    "Asearch" => A_SEARCH,
    "Searchip" => SEARCH_IP,
    "Searchinfo" => SEARCH_INFO,
    "Rank2" => $player -> rank));
$smarty -> display ('memberlist.tpl');

require_once("includes/foot.php");
?>
