<?php
/**
 *   File functions:
 *   Archive of chat
 *
 *   @name                 : innarchive.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 17.10.2006
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
// $Id: innarchive.php 731 2006-10-17 15:30:38Z thindil $

if (!isset($_GET['limit']))
{
    $_GET['limit'] = 0;
}
$objQuery = $db -> Execute("SELECT count(*) FROM `chat`");
$intAmount = $objQuery -> fields['count(*)'];
$objQuery -> Close();
$objChat = $db -> SelectLimit("SELECT `user`, `chat`, `senderid` FROM `chat` ORDER BY `id` DESC", 30, $_GET['limit']);
$arrText = array();
$arrAuthor = array();
$arrSenderid = array();
$i = 0;
while (!$objChat -> EOF) 
{
    $arrText[$i] = wordwrap($objChat -> fields['chat'],30,"\n",1);
    $arrAuthor[$i] = $objChat -> fields['user'];
    $arrSenderid[$i] = $objChat -> fields['senderid'];
    $objChat -> MoveNext();
    $i = $i + 1;
}
$objChat -> Close();
$smarty -> assign(array("Author" => $arrAuthor,
                        "Text" => $arrText,
                        "Previous" => '',
                        "Next" => '',
                        "Cid" => C_ID,
                        "Senderid" => $arrSenderid));
if ($_GET['limit'] >= 30)
{
    $intBacklimit = $_GET['limit'] - 30;
    $smarty -> assign ("Previous", "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?view=innarchive&limit=".$intBacklimit."\"><input type=\"submit\" value=\"".A_NEXT2."\"></form> ");
}
$_GET['limit'] = $_GET['limit'] + 30;
if ($intAmount > 30 && $_GET['limit'] < $intAmount) 
{
    $smarty -> assign ("Next", " <form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?view=innarchive&limit=".$_GET['limit']."\"><input type=\"submit\" value=\"".A_PREVIOUS."\"></form>");
}
?>
