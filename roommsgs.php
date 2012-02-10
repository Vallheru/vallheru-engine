<?php
/**
 *   File functions:
 *   Show text in room
 *
 *   @name                 : roommsg.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 10.02.2012
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
* GZIP compression
*/
$compress = FALSE;
if ($compress)
  {
    if (!ob_start("ob_gzhandler"))
      {
	ob_start();
      }
  }

require_once('includes/config.php');
require_once('includes/sessions.php');
require_once('libs/Smarty.class.php');

$smarty = new Smarty;
$smarty->compile_check = true;

$stat = $db -> Execute("SELECT `id`, `style`, `graphic`, `room` FROM `players` WHERE `email`='".$_SESSION['email']."'");

/**
* Select style for chat
*/
if ($stat -> fields['graphic']) 
{
    $smarty -> template_dir = "./templates/".$stat -> fields['graphic'];
    $smarty -> compile_dir = "./templates_c/".$stat -> fields['graphic'];
}   
    else
{
    $smarty -> template_dir = './templates';
    $smarty -> compile_dir = './templates_c';
}

$objOwner = $db->Execute("SELECT `owner`, `owners` FROM `rooms` WHERE `id`=".$stat->fields['room']);
if ($objOwner->fields['owners'] != '')
  {
    $arrOwners = explode(';', $objOwner->fields['owners']);
  }
else
  {
    $arrOwners = array();
  }
if ($objOwner->fields['owner'] == $stat->fields['id'] || in_array($stat->fields['id'], $arrOwners)) 
  {
    $smarty->assign("Showid", 1);
  }
else
{
    $smarty->assign("Showid", '');
}

$objOwner->Close();

$chat = $db -> SelectLimit("SELECT * FROM `chatrooms` WHERE (`ownerid`=0 OR `ownerid`=".$stat -> fields['id']." OR `senderid`=".$stat -> fields['id'].") AND `room`=".$stat->fields['room']." ORDER BY `id` DESC", 25);
$pl = $db -> Execute("SELECT `id`, `lpv`, `user` FROM `players` WHERE `page`='Pokój w karczmie' AND `room`=".$stat->fields['room']);
$arrtext = array();
$arrauthor = array();
$arrsenderid = array();
$arrSdate = array();
$arrTid = array();
while (!$chat -> EOF) 
  {
    if (strpos($chat->fields['chat'], "<a href=") === FALSE)
      {
	$text = wordwrap($chat -> fields['chat'], 60, " ", 1);
      }
    else
      {
	$text = $chat->fields['chat'];
      }
    $arrtext[] = $text;
    $arrauthor[] = $chat -> fields['user'];
    $arrsenderid[] = $chat -> fields['senderid'];
    $arrSdate[] = $chat->fields['sdate'];
    $arrTid[] = $chat->fields['id'];
    $chat -> MoveNext();
  }
$chat -> Close();

$ctime = time();
$on = '';
$numon = 0;
while (!$pl -> EOF) 
{
    $span = ($ctime - $pl -> fields['lpv']);
    if ($span <= 180) 
    {
        $on = $on." [<A href=view.php?view=".$pl -> fields['id']." target=_parent>".$pl -> fields['user']."</a> (".$pl -> fields['id'].")] ";
        $numon = ($numon + 1);
    }
    $pl -> MoveNext();
}
$pl -> Close();

$smarty->assign(array("Player" => $on, 
		      "Online" => $numon, 
		      "Author" => $arrauthor, 
		      "Text" => $arrtext, 
		      "Senderid" => $arrsenderid,
		      "Sdate" => $arrSdate,
		      "Tid" => $arrTid,
		      "Thereis" => 'Jest',
		      "Cplayers" => 'osób w pokoju',
		      "Cid" => 'ID'));
$smarty -> display ('roommsgs.tpl');
if ($compress)
  {
    ob_end_flush();
  }
?>
