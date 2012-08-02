<?php
/**
 *   File functions:
 *   Show text in chat
 *
 *   @name                 : chatmsg.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 02.08.2012
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

$stat = $db -> Execute("SELECT `id`, `rank`, `lpv`, `settings`, `chattimes` FROM `players` WHERE `email`='".$_SESSION['email']."'");
if (!$stat->fields['id'])
  {
    exit;
  }
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
* Get the localization for game
*/
require_once("languages/pl/chatmsg.php");

/**
* Select style for chat
*/
if ($arrSettings['graphic'] != '') 
{
    $smarty -> template_dir = "./templates/".$arrSettings['graphic'];
    $smarty -> compile_dir = "./templates_c/".$arrSettings['graphic'];
}   
    else
{
    $smarty -> template_dir = './templates';
    $smarty -> compile_dir = './templates_c';
}

if (!isset($_SESSION['chatlength']))
  {
    $_SESSION['chatlength'] = 25;
  }
if (!isset($_SESSION['chattab']))
  {
    $_SESSION['chattab'] = 0;
    $_SESSION['chattabs'] = array(0);
    if ($stat->fields['chattimes'] == '')
      {
	$_SESSION['chatclean'] = array(0);
      }
    else
      {
	$arrTimes = explode(',', $stat->fields['chattimes']);
	for ($i = 0; $i < count($arrTimes); $i += 2)
	  {
	    if ($i + 1 >= count($arrTimes))
	      {
		break;
	      }
	    $_SESSION['chatclean'][$arrTimes[$i]] = $arrTimes[$i + 1];
	  }
      }
  }

if ($_SESSION['chattab'] == 0)
  {
    $chat = $db -> SelectLimit("SELECT * FROM `chat` WHERE `ownerid`=0 ORDER BY `id` DESC", $_SESSION['chatlength']);
  }
else
  {
    if (!isset($_SESSION['chatclean'][$_SESSION['chattab']]))
      {
	$_SESSION['chatclean'][$_SESSION['chattab']] = 0;
      }
    $chat = $db->SelectLimit("SELECT * FROM `chat` WHERE ((`ownerid`=".$stat->fields['id']." AND `senderid`=".$_SESSION['chattab'].") OR (`ownerid`=".$_SESSION['chattab']." AND `senderid`=".$stat->fields['id'].")) AND `sdate`>'".date("Y-m-d H:i:s", $_SESSION['chatclean'][$_SESSION['chattab']])."' ORDER BY `id` DESC", $_SESSION['chatlength']);
  }
$pl = $db -> Execute("SELECT `rank`, `id`, `lpv`, `user` FROM `players` WHERE `page`='Chat'");
$arrtext = array();
$arrauthor = array();
$arrsenderid = array();
$arrSdate = array();
$arrTextid = array();
if ($stat -> fields['rank'] == 'Admin' || $stat -> fields['rank'] == 'Staff' || $stat -> fields['rank'] == 'Karczmarka') 
{
    $smarty -> assign ("Showid", 1);
}
else
  {
    $smarty->assign("Showid", '');
  }
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
    $arrTextid[] = $chat->fields['id'];
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
    $arrTextid = array_reverse($arrTextid);
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
$query = $db -> Execute("SELECT count(`id`) FROM `chat` WHERE `ownerid`=0");
$numchat = $query -> fields['count(`id`)'];
$query -> Close();
if (!isset($arrSettings['oldchat']) || $arrSettings['oldchat'] == 'N')
  {
    $strOldchat = 'N';
  }
else
  {
    $strOldchat = 'Y';
  }
//Check did we have any whispers and then create buttons for tabs
$objWhisps = $db->Execute("SELECT `senderid` FROM `chat` WHERE `ownerid`=".$stat->fields['id']." GROUP BY `senderid`");
$arrTabs = array();
if ($objWhisps->fields['senderid'])
  {
    $arrTabs = array();
    while (!$objWhisps->EOF)
      {
	if (isset($_SESSION['chatclean'][$objWhisps->fields['senderid']]))
	  {
	    $objTest = $db->Execute("SELECT count(`id`) FROM `chat` WHERE ((`ownerid`=".$stat->fields['id']." AND `senderid`=".$objWhisps->fields['senderid'].") OR (`ownerid`=".$objWhisps->fields['senderid']." AND `senderid`=".$stat->fields['id'].")) AND `sdate`>'".date("Y-m-d H:i:s", $_SESSION['chatclean'][$objWhisps->fields['senderid']])."'");
	    if ($objTest->fields['count(`id`)'] == 0)
	      {
		$objWhisps->MoveNext();
		$objTest->Close();
		continue;
	      }
	    $objTest->Close();
	  }
	$arrTabs[$objWhisps->fields['senderid']] = $objWhisps->fields['senderid'];
	if (!array_key_exists($objWhisps->fields['senderid'], $_SESSION['chattabs']))
	  {
	    $_SESSION['chattabs'][$objWhisps->fields['senderid']] = 0;
	  }
	$objWhisps->MoveNext();
	if (count($_SESSION['chattabs']) > 5)
	  {
	    break;
	  }
      }
  }
$objWhisps->Close();
$objWhisps = $db->Execute("SELECT `ownerid` FROM `chat` WHERE `senderid`=".$stat->fields['id']." AND `ownerid`!=0 GROUP BY `ownerid`");
if ($objWhisps->fields['ownerid'])
  {
    if (count($arrTabs) == 0)
      {
	$arrTabs = array();
      }
    while (!$objWhisps->EOF)
      {
	if (!array_key_exists($objWhisps->fields['ownerid'], $arrTabs))
	  {
	    if (isset($_SESSION['chatclean'][$objWhisps->fields['ownerid']]))
	      {
		$objTest = $db->Execute("SELECT count(`id`) FROM `chat` WHERE ((`ownerid`=".$stat->fields['id']." AND `senderid`=".$objWhisps->fields['ownerid'].") OR (`ownerid`=".$objWhisps->fields['ownerid']." AND `senderid`=".$stat->fields['id'].")) AND `sdate`>'".date("Y-m-d H:i:s", $_SESSION['chatclean'][$objWhisps->fields['ownerid']])."'");
		if ($objTest->fields['count(`id`)'] == 0)
		  {
		    $objWhisps->MoveNext();
		    $objTest->Close();
		    continue;
		  }
		$objTest->Close();
	      }
	    $arrTabs[$objWhisps->fields['ownerid']] = $objWhisps->fields['ownerid'];
	    if (!array_key_exists($objWhisps->fields['ownerid'], $_SESSION['chattabs']))
	      {
		$_SESSION['chattabs'][$objWhisps->fields['ownerid']] = 0;
	      }
	  }
	if (count($_SESSION['chattabs']) > 5)
	  {
	    break;
	  }
	$objWhisps->MoveNext();
      }
  }
$objWhisps->Close();
foreach ($_SESSION['chattabs'] as $key => $value)
{
  if ($key == 0)
    {
      continue;
    }
  if (!array_key_exists($key, $arrTabs))
    {
      unset($_SESSION['chattabs'][$key]);
    }
}
if (count($arrTabs) > 0)
  {
    $objNames = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `id` IN (".implode(', ', $arrTabs).")");
    $arrTabs2 = $arrTabs;
    $arrTabs[0] = 'Karczma';
    while (!$objNames->EOF)
      {
	$arrTabs[$objNames->fields['id']] = $objNames->fields['user'];
	$objNames->MoveNext();
      }
    $objNames->Close();
    ksort($arrTabs);

    //Check for new messages
    foreach ($arrTabs2 as $intPid)
      {
	$objCount = $db->Execute("SELECT count(`id`) FROM `chat` WHERE ((`ownerid`=".$stat->fields['id']." AND `senderid`=".$intPid.") OR (`ownerid`=".$intPid." AND `senderid`=".$stat->fields['id'].")) AND `sdate`>'".date("Y-m-d H:i:s", $_SESSION['chattabs'][$intPid])."'");
	if ($objCount->fields['count(`id`)'] > 0)
	  {
	    $arrTabs[$intPid] .= '*';
	  }
	$objCount->Close();
      }
    $objCount = $db->Execute("SELECT count(`id`) FROM `chat` WHERE `ownerid`=0 AND `sdate`>'".date("Y-m-d H:i:s", $_SESSION['chattabs'][0])."'");
    if ($objCount->fields['count(`id`)'] > 0)
      {
	$arrTabs[0] .= '*';
      }
  }

$smarty->assign(array("Player" => $on, 
		      "Text1" => $numchat, 
		      "Online" => $numon, 
		      "Author" => $arrauthor, 
		      "Text" => $arrtext, 
		      "Senderid" => $arrsenderid,
		      "Sdate" => $arrSdate,
		      "Thereis" => THERE_IS,
		      "Texts" => TEXTS,
		      "Cplayers" => C_PLAYERS,
		      "Cid" => C_ID,
		      "Id" => $stat->fields['id'],
		      "Tid" => $arrTextid,
		      "Awhisper" => "Szepnij",
		      "Amore" => "Więcej",
		      "Aless" => "Mniej",
		      "Chatlength" => $_SESSION['chatlength'],
		      "Chattab" => $_SESSION['chattab'],
		      "Aclose" => 'Zamknij',
		      "Oldchat" => $strOldchat,
		      "Tabs" => $arrTabs));
$smarty -> display ('chatmsgs.tpl');
if ($compress)
  {
    ob_end_flush();
  }
?>
