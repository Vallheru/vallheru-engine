<?php
/**
 *   File functions:
 *   Comments to news, updates and polls
 *
 *   @name                 : comments.php                            
 *   @copyright            : (C) 2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 03.08.2012
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
 * Function to display comments
 */
function displaycomments($intItemid, $strItemtable, $strCommentstable, $strCommentsid)
{
    global $arrBody;
    global $arrAuthor;
    global $arrId;
    global $arrDate;
    global $db;
    global $intPages;
    global $intPage;
    global $arrAuthorid;

    checkvalue($intItemid);
    $objText = $db -> Execute("SELECT `id` FROM `".$strItemtable."` WHERE `id`=".$intItemid);
    if (!$objText -> fields['id'])
    {
        error(NO_TEXT);
    }
    $objText -> Close();
    $objQuery = $db -> Execute("SELECT count(`id`) FROM `".$strCommentstable."` WHERE `".$strCommentsid."`=".$intItemid);
    $intPages = ceil($objQuery->fields['count(`id`)'] / 25);
    $objQuery -> Close();
    $intPage = intval($intPage);
    if ($intPage == 0)
      {
	error(ERROR);
      }
    else if ($intPage == -1)
      {
	$intPage = $intPages;
      }
    $objComments = $db->SelectLimit("SELECT `id`, `body`, `author`, `time` FROM `".$strCommentstable."` WHERE `".$strCommentsid."`=".$intItemid." ORDER BY `id`", 25, 25 * ($intPage - 1)) or die($db -> ErrorMsg());
    $arrBody = array();
    $arrAuthor = array();
    $arrId = array();
    $arrDate = array();
    $arrAuthorid = array();
    $i = 0;
    while (!$objComments -> EOF)
    {
        $arrBody[$i] = $objComments -> fields['body'];
        $arrAuthor[$i] = $objComments -> fields['author'];
	$tmpArray = explode(" ", $objComments->fields['author']);
	$arrAuthorid[$i] = $tmpArray[count($tmpArray) - 1];
        $arrId[$i] = $objComments -> fields['id'];
        $arrDate[$i] = $objComments -> fields['time'];
        $i = $i + 1;
        $objComments -> MoveNext();
    }
    $objComments -> Close();
    return $i;
}

/**
 * Function to add comments
 */
function addcomments($intItemid, $strCommentstable, $strCommentsid)
{
    global $db;
    global $player;
    global $data;
    global $arrTags;

    if (empty($_POST['body']))
      {
	message('error', EMPTY_FIELDS);
      }
    else
      {
	checkvalue($intItemid);
	require_once('includes/bbcode.php');
	$strAuthor = $arrTags[$player->tribe][0]." ".$player->user." ".$arrTags[$player->tribe][1]." ID: ".$player -> id;
	$_POST['body'] = bbcodetohtml($_POST['body']);
	$strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
	$strDate = $db -> DBDate($data);
	$db -> Execute("INSERT INTO `".$strCommentstable."` (`".$strCommentsid."`, `author`, `body`, `time`) VALUES(".$intItemid.", '".$strAuthor."', ".$strBody.", ".$strDate.")");
	message('success', C_ADDED);
      }
}

/**
 * Function to delete comments
 */
function deletecomments($strCommentstable, $arrRanks = array('Admin', 'Staff'))
{
    global $db;
    global $player;

    if (!in_array($player->rank, $arrRanks))
    {
        error(NO_PERM);
    }
    checkvalue($_GET['cid']);
    $db -> Execute("DELETE FROM `".$strCommentstable."` WHERE `id`=".$_GET['cid']);
    message('success', C_DELETED);
}
?>
