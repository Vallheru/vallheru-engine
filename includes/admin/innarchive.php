<?php
/**
 *   File functions:
 *   Archive of chat
 *
 *   @name                 : innarchive.php                            
 *   @copyright            : (C) 2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 25.04.2012
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
// $Id:$

if (isset($_GET['page']))
  {
    checkvalue($_GET['page']);
    $intPage = $_GET['page'];
  }
$objQuery = $db -> Execute("SELECT count(`id`) FROM `chat`");
$intPages = $objQuery -> fields['count(`id`)'] / 30;
$objQuery -> Close();
if (!isset($intPage))
  {
    $intPage = 1;
  }
$objChat = $db -> SelectLimit("SELECT `user`, `chat`, `senderid`, `sdate` FROM `chat` ORDER BY `id` DESC", 30, 30 * ($intPage - 1));
$arrText = array();
$arrAuthor = array();
$arrSenderid = array();
$arrSdate = array();
while (!$objChat -> EOF) 
{
    $arrText[] = wordwrap($objChat -> fields['chat'],30,"\n",1);
    $arrAuthor[] = $objChat -> fields['user'];
    $arrSenderid[] = $objChat -> fields['senderid'];
    $arrSdate[] = $objChat->fields['sdate'];
    $objChat -> MoveNext();
}
$objChat -> Close();
$smarty -> assign(array("Author" => $arrAuthor,
                        "Text" => $arrText,
                        "Cid" => C_ID,
                        "Senderid" => $arrSenderid,
			"Sdate" => $arrSdate,
			"Fpage" => "Idź do strony: ",
			"Tpages" => $intPages,
			"Tpage" => $intPage));
?>
