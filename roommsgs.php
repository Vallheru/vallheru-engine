<?php
/**
 *   File functions:
 *   Show text in room
 *
 *   @name                 : roommsg.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 10.07.2012
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

$stat = $db -> Execute("SELECT `id`, `settings`, `room` FROM `players` WHERE `email`='".$_SESSION['email']."'");
$arrTmp = explode(';', $stat->fields['settings']);
$arrSettings = array();
foreach ($arrTmp as $strField)
{
  $arrTmp2 = explode(':', $strField);
  if ($arrTmp2[0] == '')
    {
      continue;
    }
  $arrSettings[$arrTmp2[0]] = $arrTmp2[1];
}

/**
* Select style for chat
*/
if ($arrSettings['graphic']) 
{
    $smarty -> template_dir = "./templates/".$arrSettings['graphic'];
    $smarty -> compile_dir = "./templates_c/".$arrSettings['graphic'];
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

if (!isset($_SESSION['roomlength']))
  {
    $_SESSION['roomlength'] = 25;
  }

$chat = $db -> SelectLimit("SELECT * FROM `chatrooms` WHERE (`ownerid`=0 OR `ownerid`=".$stat -> fields['id']." OR `senderid`=".$stat -> fields['id'].") AND `room`=".$stat->fields['room']." ORDER BY `id` DESC", $_SESSION['roomlength']);
$pl = $db -> Execute("SELECT `id`, `lpv`, `user` FROM `players` WHERE `page`='Pokój w karczmie' AND `room`=".$stat->fields['room']);
$arrtext = array();
$arrauthor = array();
$arrsenderid = array();
$arrSdate = array();
$arrTid = array();
date_default_timezone_set('Europe/Warsaw');
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
    $arrTid[] = $chat->fields['id'];
    $time = time() - strtotime($chat->fields['sdate']);
    if ($time < 60)
      {
	$arrSdate[] = $time.' sekund temu ('.$chat->fields['sdate'].')';
      }
    elseif ($time > 59 && $time < 3600)
      {
	$arrSdate[] = floor($time / 60).' minut temu ('.$chat->fields['sdate'].')';
      }
    else
      {
	$arrSdate[] = floor($time / 3600).' godzin temu ('.$chat->fields['sdate'].')';
      }
    $chat -> MoveNext();
  }
$chat -> Close();

if (!isset($arrSettings['oldchat']) || $arrSettings['oldchat'] == 'N')
  {
    $arrtext = array_reverse($arrtext);
    $arrauthor = array_reverse($arrauthor);
    $arrsenderid = array_reverse($arrsenderid);
    $arrSdate = array_reverse($arrSdate);
    $arrTid = array_reverse($arrTid);
  }

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

if (!isset($arrSettings['oldchat']) || $arrSettings['oldchat'] == 'N')
  {
    $strOldchat = 'N';
  }
else
  {
    $strOldchat = 'Y';
  }
$query = $db -> Execute("SELECT count(`id`) FROM `chat`");
$numchat = $query -> fields['count(`id`)'];
$query -> Close();

$smarty->assign(array("Text1" => $numchat, 
		      "Player" => $on, 
		      "Online" => $numon, 
		      "Author" => $arrauthor, 
		      "Text" => $arrtext, 
		      "Senderid" => $arrsenderid,
		      "Sdate" => $arrSdate,
		      "Tid" => $arrTid,
		      "Thereis" => 'Jest',
		      "Cplayers" => 'osób w pokoju',
		      "Amore" => "Więcej",
		      "Aless" => "Mniej",
		      "Awhisper" => "Szepnij",
		      "Id" => $stat->fields['id'],
		      "Cid" => 'ID',
		      "Chatlength" => $_SESSION['roomlength'],
		      "Oldchat" => $strOldchat));
$smarty -> display ('roommsgs.tpl');
if ($compress)
  {
    ob_end_flush();
  }
?>
