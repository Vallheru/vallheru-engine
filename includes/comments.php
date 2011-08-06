<?php
/**
 *   File functions:
 *   Comments to news, updates and polls
 *
 *   @name                 : comments.php                            
 *   @copyright            : (C) 2006,2007 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 28.02.2007
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
// $Id: comments.php 903 2007-02-28 21:31:27Z thindil $

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

    if (!ereg("^[1-9][0-9]*$", $intItemid))
    {
        error(ERROR);
    }
    $objText = $db -> Execute("SELECT `id` FROM `".$strItemtable."` WHERE `id`=".$intItemid);
    if (!$objText -> fields['id'])
    {
        error(NO_TEXT);
    }
    $objText -> Close();
    $objComments = $db -> Execute("SELECT `id`, `body`, `author`, `time` FROM `".$strCommentstable."` WHERE `".$strCommentsid."`=".$intItemid." ORDER BY `id`") or die($db -> ErrorMsg());
    $arrBody = array();
    $arrAuthor = array();
    $arrId = array();
    $arrDate = array();
    $i = 0;
    while (!$objComments -> EOF)
    {
        $arrBody[$i] = $objComments -> fields['body'];
        $arrAuthor[$i] = $objComments -> fields['author'];
        $arrId[$i] = $objComments -> fields['id'];
        $arrDate[$i] = $objComments -> fields['time'];
        $i = $i + 1;
        $objComments -> MoveNext();
    }
    $objComments -> Close();
}

/**
 * Function to add comments
 */
function addcomments($intItemid, $strCommentstable, $strCommentsid)
{
    global $db;
    global $player;
    global $data;

    if (empty($_POST['body']))
    {
        error(EMPTY_FIELDS);
    }
    if (!ereg("^[1-9][0-9]*$", $intItemid))
    {
        error(ERROR);
    }
    require_once('includes/bbcode.php');
    $strAuthor = $player -> user." ID: ".$player -> id;
    $_POST['body'] = bbcodetohtml($_POST['body']);
    $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
    $strDate = $db -> DBDate($data);
    $db -> Execute("INSERT INTO `".$strCommentstable."` (`".$strCommentsid."`, `author`, `body`, `time`) VALUES(".$intItemid.", '".$strAuthor."', ".$strBody.", ".$strDate.")");
    error(C_ADDED);
}

/**
 * Function to delete comments
 */
function deletecomments($strCommentstable)
{
    global $db;
    global $player;

    if ($player -> rank != 'Admin' && $player -> rank != 'Staff')
    {
        error(NO_PERM);
    }
    if (!ereg("^[1-9][0-9]*$", $_GET['cid']))
    {
        error(ERROR);
    }
    $db -> Execute("DELETE FROM `".$strCommentstable."` WHERE `id`=".$_GET['cid']);
    error(C_DELETED);
}
?>
