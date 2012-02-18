<?php
/**
 *   File functions:
 *   Messages to other players
 *
 *   @name                 : mail.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 18.02.2012
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

$title = "Poczta"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/mail.php");

if (!isset($_GET['view']) && !isset($_GET['read']) && !isset($_GET['zapisz']))
{
    $smarty -> assign(array("Mailinfo" => MAIL_INFO,
                            "Asaved" => A_SAVED,
                            "Awrite" => A_WRITE,
                            "Ablocked" => A_BLOCK_LIST,
			    "Asearch" => "Szukaj wiadomości"));
}

$strQuery = '';
if (isset($_GET['page']))
  {
    checkvalue($_GET['page']);
    $intPage = $_GET['page'];
  }

/**
 * Search mails.
 */
if (isset($_GET['view']) && $_GET['view'] == 'search')
  {
    $smarty->assign(array("Asearch" => "Szukaj",
			  "Amount" => 0));
    if (isset($_GET['step']))
      {
	if (!isset($_POST['search']))
	  {
	    message('error', ERROR);
	  }
	else
	  {
	    $_POST['search'] = strip_tags($_POST['search']);
	    $strSearch = $db -> qstr("*".$_POST['search']."*", get_magic_quotes_gpc());
	    $objAmount = $db->Execute("SELECT count(`id`) FROM `mail` WHERE `owner`=".$player->id." AND MATCH(`subject`, `body`) AGAINST (".$strSearch." IN BOOLEAN MODE)") or die($db -> ErrorMsg());
	    if ($objAmount->fields['count(`id`)'] == 0)
	      {
		message('info', "Nie znaleziono jakiejkolwiek wiadomości.");
	      }
	    else
	      {
		$intPages = ceil($objAmount->fields['count(`id`)'] / 30);
		$objAmount->Close();
		if (!isset($intPage))
		  {
		    $intPage = 1;
		  }
		
		$objMails = $db->SelectLimit("SELECT `id`, `sender`, `subject` FROM `mail` WHERE `owner`=".$player->id." AND MATCH(`subject`, `body`) AGAINST (".$strSearch." IN BOOLEAN MODE)", 30, 30 * ($intPage - 1)) or die($db -> ErrorMsg());
		$arrsender = array();
		$arrsubject = array();
		$arrid = array();
		while (!$objMails->EOF)
		  {
		    $arrsender[] = $objMails->fields['sender'];
		    $arrsubject[] = $objMails->fields['subject'];
		    $arrid[] = $objMails->fields['id'];
		    $objMails->MoveNext();
		  }
		$objMails->Close();
		$smarty->assign(array("Amount" => 1,
				      "Senders" => $arrsender,
				      "Subjects" => $arrsubject,
				      "Mailid" => $arrid,
				      "Tpages" => $intPages,
				      "Tpage" => $intPage,
				      "Fpage" => "Idź do strony:"));
	      }
	  }
      }
  }

/**
* Delete, save, mark as read/unread selected messages
*/
if (isset($_GET['step']) && $_GET['step'] == 'mail')
{
    $objMid = $db -> Execute("SELECT `id` FROM `mail` WHERE `owner`=".$player->id);
    while (!$objMid -> EOF)
      {
	if (isset($_POST['delete']) && isset($_POST[$objMid->fields['id']]))
	  {
	    $objTopic = $db->Execute("SELECT `topic` FROM `mail` WHERE `id`=".$objMid->fields['id']);
	    $db->Execute("DELETE FROM `mail` WHERE `topic`=".$objTopic->fields['topic']." AND `owner`=".$player->id);
	    $objTopic->Close();
	  }
	elseif (isset($_POST['read2']) && isset($_POST[$objMid->fields['id']]))
	  {
	    $db->Execute("UPDATE `mail` SET `unread`='T' WHERE `id`=".$objMid->fields['id']);
	  }
	elseif (isset($_POST['unread']) && isset($_POST[$objMid->fields['id']]))
	  {
	    $db->Execute("UPDATE `mail` SET `unread`='F' WHERE `id`=".$objMid->fields['id']);
	  }
        $objMid -> MoveNext();
      }
    $objMid -> Close();
    if (isset($_POST['delete']))
      {
	message('success', DELETED);
      }
    elseif (isset($_POST['write']))
      {
        message('success', SAVED);
      }
    elseif (isset($_POST['read2']))
      {
        message('success', MARK_AS_READ);
      }
    elseif (isset($_POST['unread']))
      {
	message('success', MARK_AS_UNREAD);
      }
    $_GET['step'] = '';
    if ($_GET['box'] == 'I')
      {
	$_GET['view'] = 'inbox';
      }
    else
      {
	$_GET['view'] = 'saved';
      }
}

/**
 * Delete old messages
 */
if (isset($_GET['step']) && $_GET['step'] == 'deleteold')
{
  $arrType = array('I', 'W');
  $arrAmount = array(7, 14, 30);
  if (!in_array($_GET['box'], $arrType) || !in_array($_POST['oldtime'], $arrAmount))
    {
      message('error', ERROR);
    }
  else
    {
      $arrDate = explode("-", $data);
      $arrDate[0] = date("Y");
      $arrDate[2] = $arrDate[2] - $_POST['oldtime'];
      if ($arrDate[2] < 1)
	{
	  $arrDays = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	  $arrDate[1] = $arrDate[1] - 1;
	  if ($arrDate[1] == 0)
	    {
	      $arrDate[1] = 12;
	    }
	  $intKey = $arrDate[1] - 1;
	  $arrDate[2] = $arrDays[$intKey] + $arrDate[2];
	}
      $strDate = implode("-", $arrDate);
      $strDate = $db -> DBDate($strDate);
      if ($_GET['box'] == 'I')
	{
	  $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$player->id." AND `saved`='N' AND `date`<".$strDate);
	  $_GET['view'] = 'inbox';
	}
      elseif ($_GET['box'] == 'W')
	{
	  $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$player->id." AND `saved`='Y' AND `date`<".$strDate);
	  $_GET['view'] = 'saved';
	}
      message('success', DELETED2);
      $_GET['step'] = '';
    }
}

/**
 * Sorting of mails.
 */
if (isset($_GET['view']) && in_array($_GET['view'], array('inbox', 'saved')))
  {
    if (isset($_GET['sort1']))
      {
	$_POST['sort1'] = $_GET['sort1'];
	$_POST['sort2'] = $_GET['sort2'];
      }
    if (isset($_POST['sort1']))
      {
	$_POST['sort1'] = intval($_POST['sort1']);
	if ($_POST['sort1'] != -1)
	  {
	    $strQuery = " AND `senderid`=".$_POST['sort1'];
	  }
	$_POST['sort2'] = intval($_POST['sort2']);
	if ($_POST['sort2'] != -1)
	  {
	    $arrDate = explode("-", $data);
	    $arrDate[0] = date("Y");
	    $arrDate[2] = $arrDate[2] - $_POST['sort2'];
	    if ($arrDate[2] < 1)
	      {
		$arrDays = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$arrDate[1] = $arrDate[1] - 1;
		if ($arrDate[1] == 0)
		  {
		    $arrDate[1] = 12;
		  }
		$intKey = $arrDate[1] - 1;
		$arrDate[2] = $arrDays[$intKey] + $arrDate[2];
	      }
	    $strDate = implode("-", $arrDate);
	    $strDate = $db -> DBDate($strDate);
	    if ($_POST['sort2'] != 31)
	      {
		$strQuery .= " AND `date`>".$strDate;
	      }
	    else
	      {
		$strQuery .= " AND `date`<".$strDate;
	      }
	  }
	$strPage = '&amp;sort1='.$_POST['sort1']."&amp;sort2=".$_POST['sort2'];
      }
    else
      {
	$strPage = '';
      }
  }

/**
 * Mail inbox
 */
if (isset ($_GET['view']) && $_GET['view'] == 'inbox') 
  {
    $objSort = $db->Execute("SELECT `sender`, `senderid` FROM `mail`  WHERE `owner`=".$player -> id." AND `senderid`!=".$player->id." GROUP BY `senderid` ASC");
    $arrSendersid = array();
    $arrSenders = array();
    while (!$objSort->EOF) 
      {
	$arrSenders[] = $objSort->fields['sender'];
	$arrSendersid[] = $objSort->fields['senderid'];
	$objSort->MoveNext();
      }
    $objSort->Close();

    //Pagination
    $objAmount = $db->Execute("SELECT `id` FROM `mail` WHERE `owner`=".$player->id.$strQuery." GROUP BY `topic`");
    $intPages = ceil($objAmount->RecordCount() / 30);
    $objAmount->Close();
    if (!isset($intPage))
      {
	$intPage = 1;
      }

    $objMail = $db->Execute("SELECT `topic` FROM `mail` WHERE `owner`=".$player->id.$strQuery." ORDER BY `id` DESC");
    $arrTopic1 = array();
    while (!$objMail->EOF)
      {
	if (!in_array($objMail->fields['topic'], $arrTopic1))
	  {
	    $arrTopic1[] = $objMail->fields['topic'];
	  }
	$objMail->MoveNext();
      }
    $objMail->Close();
    $intStart = 30 * ($intPage - 1);
    $intLimit = 30 * $intPage;
    $arrTopic = array();
    for ($i = $intStart; $i < $intLimit; $i++)
      {
	if ($i == count($arrTopic1))
	  {
	    break;
	  }
	$arrTopic[] = $arrTopic1[$i];
      }
    $arrsender = array();
    $arrsenderid = array();
    $arrsubject = array();
    $arrid = array();
    $arrRead = array();
    foreach ($arrTopic as $intTopic)
      {
	$mail = $db->SelectLimit("SELECT * FROM `mail` WHERE `owner`=".$player->id." AND `topic`=".$intTopic." ORDER BY `id` ASC", 1);
	if ($mail->fields['senderid'] == $player->id && $mail->fields['to'] > 0)
	  {
	    $arrsenderid[] = $mail->fields['to'];
	    $arrsender[] = $mail->fields['toname'];
	  }
	else
	  {
	    $arrsender[] = $mail -> fields['sender'];
	    $arrsenderid[] = $mail -> fields['senderid'];
	  }
	$arrsubject[] = $mail -> fields['subject'];
	$arrid[] = $mail -> fields['id'];
	if ($mail -> fields['unread'] == 'F')
	  {
	    $arrRead[] = 'Y';
	  }
	else
	  {
	    $arrRead[] = 'N';
	  }
	$mail -> Close();
      }
    $smarty -> assign(array("Sender" => $arrsender, 
                            "Senderid" => $arrsenderid, 
                            "Subject" => $arrsubject, 
                            "Mailid" => $arrid,
                            "Aclear" => A_CLEAR2,
                            "From" => "Od/Do",
                            "Sid" => S_ID,
                            "Mtitle" => M_TITLE,
                            "Aread" => A_READ,
                            "Asaved" => A_SAVED,
                            "Moption" => M_OPTION,
                            "Adeleteold" => A_DELETE_OLD,
                            "Aweek" => A_WEEK,
                            "A2week" => A_2WEEK,
                            "Amonth" => A_MONTH,
                            "Aread2" => A_READ2,
                            "Aunread" => A_UNREAD,
			    "Asort" => "Sortuj",
			    "Sall" => "Wszyscy",
			    "Sall2" => "Bez ograniczeń",
			    "Senders" => $arrSenders,
			    "Sendersid" => $arrSendersid,
			    "Tsender" => "Nadawca",
			    "Ttime" => "Okres",
			    "Tlastweek" => "Ostatni tydzień",
			    "Tlastmonth" => "Ostatni miesiąc",
			    "Toldest" => "Starsze niż miesiąc",
			    "Tpages" => $intPages,
			    "Tpage" => $intPage,
			    "Fpage" => "Idź do strony:",
			    "Lpage" => $strPage,
			    "Mtopic" => $arrTopic,
                            "Mread" => $arrRead));
    if (isset ($_GET['step']) && $_GET['step'] == 'clear') 
    {
        $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$player -> id." AND `saved`='N'");
	message("success", 'Wykasowano wszystkie wiadomości.', '(<a href=mail.php?view=inbox>Odśwież</a>)');
    }
}

/**
 * Marked mails
 */
if (isset ($_GET['view']) && $_GET['view'] == 'saved') 
{
    $objSort = $db->Execute("SELECT `sender`, `senderid` FROM `mail`  WHERE `owner`=".$player->id." AND `saved`='Y' GROUP BY `senderid` ASC");
    $arrSendersid = array();
    $arrSenders = array();
    while (!$objSort->EOF) 
      {
	$arrSenders[] = $objSort->fields['sender'];
	$arrSendersid[] = $objSort->fields['senderid'];
	$objSort->MoveNext();
      }
    $objSort->Close();

    //Pagination
    $objAmount = $db->Execute("SELECT `id` FROM `mail` WHERE `owner`=".$player -> id." AND `saved`='Y'".$strQuery." GROUP BY `topic`");
    $intPages = ceil($objAmount->RecordCount() / 30);
    $objAmount->Close();
    if (!isset($intPage))
      {
	$intPage = 1;
      }

    $mail = $db->SelectLimit("SELECT * FROM (SELECT * FROM `mail` WHERE `owner`=".$player -> id." AND `saved`='Y'".$strQuery." ORDER BY `id` ASC) AS s GROUP BY `topic`", 30, 30 * ($intPage - 1));
    $arrsender = array();
    $arrsenderid = array();
    $arrsubject = array();
    $arrid = array();
    $i = 0;
    while (!$mail -> EOF) 
    {
        $arrsender[$i] = $mail -> fields['sender'];
        $arrsenderid[$i] = $mail -> fields['senderid'];
        $arrsubject[$i] = $mail -> fields['subject'];
        $arrid[$i] = $mail -> fields['id'];
        $mail -> MoveNext();
        $i = $i + 1;
    }
    $mail -> Close();
    $smarty -> assign(array("Sender" => $arrsender, 
                            "Senderid" => $arrsenderid, 
                            "Subject" => $arrsubject, 
                            "Mailid" => $arrid,
                            "Aclear" => A_CLEAR,
                            "From" => FROM,
                            "Sid" => S_ID,
                            "Mtitle" => M_TITLE,
                            "Aread" => A_READ,
                            "Adeleteold" => A_DELETE_OLD,
                            "Aweek" => A_WEEK,
                            "A2week" => A_2WEEK,
                            "Amonth" => A_MONTH,
			    "Asort" => "Sortuj",
			    "Sall" => "Wszyscy",
			    "Sall2" => "Bez ograniczeń",
			    "Senders" => $arrSenders,
			    "Sendersid" => $arrSendersid,
			    "Tsender" => "Nadawca",
			    "Ttime" => "Okres",
			    "Tlastweek" => "Ostatni tydzień",
			    "Tlastmonth" => "Ostatni miesiąc",
			    "Toldest" => "Starsze niż miesiąc",
			    "Tpages" => $intPages,
			    "Tpage" => $intPage,
			    "Fpage" => "Idź do strony:",
			    "Lpage" => $strPage,
                            "Moption" => M_OPTION));
    if (isset ($_GET['step']) && $_GET['step'] == 'clear') 
    {
        $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$player -> id." AND `saved`='Y'");
	message("success", 'Wykasowano wszystkie zapisane wiadomości.', '(<a href=mail.php?view=saved>Odśwież</a>)');
    }
}

/**
* Write new message
*/
if (isset ($_GET['view']) && $_GET['view'] == 'write') 
{
    $objBan = $db -> Execute("SELECT `id` FROM `ban_mail` WHERE `owner`=0 AND `id`=".$player -> id);
    if ($objBan -> fields['id'])
      {
	error(YOU_CANNOT);
      }
    $objBan -> Close();
    //Contacts
    $objContacts = $db->Execute("SELECT `pid` FROM `contacts` WHERE `owner`=".$player->id." ORDER BY `order` ASC");
    $arrContacts = array(0 => "ID (numer)");
    while (!$objContacts->EOF)
      {
	$objUser = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$objContacts->fields['pid']);
	$arrContacts[$objContacts->fields['pid']] = $objUser->fields['user'];
	$objUser->Close();
	$objContacts->MoveNext();
      }
    $objContacts->Close();
    if (!isset ($_GET['to'])) 
      {
        $_GET['to'] = '';
      }
    $smarty -> assign(array("To" => $_GET['to'], 
                            "Sto" => S_TO,
                            "Mtitle" => M_TITLE,
                            "Mbody" => M_BODY,
                            "Asend" => A_SEND2,
			    "Contacts" => $arrContacts,
			    "Body" => '',
			    "Subject" => '',
			    "Mhelp" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i>[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>"));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
        if (isset($_POST['player']) && $_POST['player'] != 0)
	  {
	    checkvalue($_POST['player']);
	    $_POST['to'] = $_POST['player'];
	  }
	else
	  {
	    checkvalue($_POST['to']);
	  }
	$_POST['subject'] = str_replace("&nbsp", " ", $_POST['subject']);
	$_POST['subject'] = trim(htmlspecialchars($_POST['subject'], ENT_QUOTES));
	$blnValid = TRUE;
        if (empty ($_POST['to']) || empty ($_POST['body']) || preg_match("/[a-zA-Z0-9]+/", $_POST['subject']) == 0 || empty($_POST['subject'])) 
        {
	    message('error', EMPTY_FIELDS);
	    $blnValid = FALSE;
        }
	if (isset($_POST['topic']))
	  {
	    checkvalue($_POST['topic']);
	  }
        $rec = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['to']);
        if (!$rec -> fields['id']) 
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
        if ($_POST['to'] == $player -> id)
	  {
	    message('error', YOURSELF);
	    $blnValid = FALSE;
	  }
        $objBan = $db -> Execute("SELECT `id` FROM `ban_mail` WHERE `owner`=".$_POST['to']." AND `id`=".$player -> id);
        if ($objBan -> fields['id'])
	  {
            message('error', YOU_CANNOT);
	    $blnValid = FALSE;
	  }
        $objBan -> Close();
	if ($blnValid)
	  {
	    require_once('includes/bbcode.php');
	    $_POST['body'] = bbcodetohtml($_POST['body']);
	    $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
	    $strSubject = $db -> qstr($_POST['subject'], get_magic_quotes_gpc());
	    $strDate = $db -> DBDate($newdate);
	    if (!isset($_POST['topic']))
	      {
		$objTopic = $db->Execute("SELECT max(`topic`) FROM `mail`");
		$_POST['topic'] = $objTopic->fields['max(`topic`)'] + 1;
		$objTopic->Close();
		$strUnread = 'F';
	      }
	    else
	      {
		$strUnread = 'T';
	      }
	    $db -> Execute("INSERT INTO mail (`sender`, `senderid`, `owner`, `subject`, `body`, `date`, `topic`, `unread`, `to`, `toname`) VALUES('".$player -> user."','".$player -> id."',".$_POST['to'].", ".$strSubject." , ".$strBody.", ".$strDate.", ".$_POST['topic'].", '".$strUnread."', ".$_POST['to'].", '".$rec->fields['user']."')");
	    if (isset($_POST['topic']))
	      {
		$objId = $db->Execute("SELECT min(`id`) FROM `mail` WHERE `owner`=".$_POST['to']." AND `topic`=".$_POST['topic']);
		$db->Execute("UPDATE `mail` SET `unread`='F' WHERE `id`=".$objId->fields['min(`id`)']);
		$objId->Close();
	      }
	    $db -> Execute("INSERT INTO mail (`sender`, `senderid`, `owner`, `subject`, `body`, `date`, `unread`, `topic`, `to`, `toname`) VALUES('".$player -> user."','".$player -> id."',".$player -> id.", ".$strSubject.", ".$strBody.", ".$strDate.", 'T', ".$_POST['topic'].", ".$_POST['to'].", '".$rec->fields['user']."')");
	    message('success', YOU_SEND.$rec -> fields['user'].".");
	    $_GET['view'] = '';
	    $_GET['read'] = $_POST['topic'];
	  }
	else
	  {
	    if (!isset($_POST['body']))
	      {
		$_POST['body'] = '';
	      }
	    if (!isset($_POST['subject']))
	      {
		$_POST['subject'] = '';
	      }
	    $smarty->assign(array("Body" => $_POST['body'],
				  "Subject" => $_POST['subject'],
				  "To" => $_POST['to']));
	  }
    }
}

/**
 * Mark selected message
 */
if (isset ($_GET['zapisz'])) 
{
    checkvalue($_GET['zapisz']);
    $mail = $db -> Execute("SELECT `id`, `topic` FROM `mail` WHERE `id`=".$_GET['zapisz']." AND `owner`=".$player->id);
    if (!$mail -> fields['id']) 
      {
	error(NO_MAIL);
      }
    else
      {
	$db -> Execute("UPDATE `mail` SET `saved`='Y' WHERE `id`=".$_GET['zapisz']);
	message('success', MAIL_SAVE.".");
	$_GET['zapisz'] = '';
	$_GET['read'] = $mail->fields['topic'];
      }
    $mail->Close();
}

/**
 * Delete selected message
 */
if (isset ($_GET['kasuj'])) 
{
    checkvalue($_GET['kasuj']);
    $mail = $db -> Execute("SELECT `id`, `topic` FROM `mail` WHERE `id`=".$_GET['kasuj']." AND `owner`=".$player->id);
    if (!$mail -> fields['id']) 
      {
        error(NO_MAIL);
      }
    else
      {
	$db->Execute("DELETE FROM `mail` WHERE `id`=".$_GET['kasuj']);
	message('success', "Wiadomość usunięta.");
	$objAmount = $db->Execute("SELECT `id` FROM `mail` WHERE `owner`=".$player->id." AND `topic`=".$mail->fields['topic']);
	if ($objAmount->fields['id'])
	  {
	    $_GET['read'] = $mail->fields['topic'];
	  }
      }
    $mail->Close();
}

/**
 * Ban/unban players on mail
 */
if (isset($_GET['block']))
{
    checkvalue($_GET['block']);
    $objPlayer = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_GET['block']);
    if (!$objPlayer -> fields['id'])
      {
	message('error', NO_PLAYER);
      }
    $objPlayer -> Close();
    $objBan = $db -> Execute("SELECT `id` FROM `ban_mail` WHERE `id`=".$_GET['block']." AND `owner`=".$player -> id);
    if ($objBan -> fields['id'])
      {
        $db -> Execute("DELETE FROM `ban_mail` WHERE `id`=".$_GET['block']." AND `owner`=".$player -> id);
        message('success', YOU_UNBLOCK);
      }
    else
      {
        $db -> Execute("INSERT INTO `ban_mail` (`id`, `owner`) VALUES(".$_GET['block'].", ".$player -> id.")");
        message('success', YOU_BLOCK);
      }
    $objBan -> Close();
}

/**
* Send mail to admin or staff
*/
if (isset ($_GET['send'])) 
{
    $sid = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `rank`='Admin' OR `rank`='Staff'");
    $arrid = array();
    $arrname = array();
    while (!$sid -> EOF) 
    {
        $arrid[] = $sid -> fields['id'];
        $arrname[] = $sid -> fields['user'];
        $sid -> MoveNext();
    }
    $sid -> Close();
    $smarty -> assign(array("Send" => $_GET['send'], 
                            "Staffid" => $arrid, 
                            "Name" => $arrname,
                            "Sendthis" => SEND_THIS,
                            "Asend" => A_SEND2));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
	checkvalue($_POST['staff']);
	checkvalue($_POST['mid']);
        $arrtest = $db -> Execute("SELECT `id`, `user`, `rank` FROM `players` WHERE `id`=".$_POST['staff']);
	$blnValid = TRUE;
        if (!$arrtest -> fields['id']) 
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
        if ($arrtest -> fields['rank'] != 'Admin' && $arrtest -> fields['rank'] != 'Staff') 
	  {
	    message('error', NOT_STAFF);
	    $blnValid = FALSE;
	  }
        $arrmessage = $db -> Execute("SELECT * FROM `mail` WHERE `id`=".$_POST['mid']." AND `owner`=".$player->id);
        if (!$arrmessage -> fields['id']) 
	  {
            message('error', NOT_MAIL);
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$arrtest -> fields['id'].",'".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user."</a>".L_ID.$player -> id.SEND_YOU.$arrmessage -> fields['sender'].L_ID.$arrmessage -> fields['senderid'].".', ".$strDate.", 'A')");
	    $objTopic = $db->Execute("SELECT max(`topic`) FROM `mail`");
	    $intTopic = $objTopic->fields['max(`topic`)'] + 1;
	    $objTopic->Close();
	    $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`, `topic`) VALUES('".$player -> user."','".$player -> id."',".$arrtest -> fields['id'].",'".M_TITTLE.$arrmessage -> fields['sender'].L_ID.$arrmessage -> fields['senderid']."','".M_TITLE2.$arrmessage -> fields['subject'].M_DATE.$arrmessage -> fields['date'].M_BODY.$arrmessage -> fields['body']."', ".$strDate.", ".$intTopic.")");
	    message('success', YOU_SEND.$arrtest -> fields['user'].".");
	    $_GET['read'] = $arrmessage->fields['topic'];
	  }
	$arrmessage->Close();
	$arrtest->Close();
    }
}

/**
 * Read selected message/topic
 */
if (isset ($_GET['read'])) 
{
    checkvalue($_GET['read']);
    $intReceiver = 0;
    $db -> Execute("UPDATE `mail` SET `unread`='T' WHERE `topic`=".$_GET['read']." AND `owner`=".$player->id);
    $arrSender = array();
    $arrSenderid = array();
    $arrMid = array();
    $arrBody = array();
    $arrDate = array();

    if (!isset($_GET['one']))
      {
	//Pagination
	$objAmount = $db->Execute("SELECT count(`id`) FROM `mail` WHERE `owner`=".$player->id.$strQuery." AND `topic`=".$_GET['read']);
	$intPages = ceil($objAmount->fields['count(`id`)'] / 20);
	$objAmount->Close();
	if (!isset($intPage))
	  {
	    $intPage = $intPages;
	  }
	
	$objMails = $db->SelectLimit("SELECT * FROM `mail` WHERE `topic`=".$_GET['read']." AND `owner`=".$player->id." ORDER BY `id` ASC", 20, 20 * ($intPage - 1));
	$_GET['one'] = 0;
      }
    else
      {
	$intPage = 0;
	$intPages = 0;
	$objMails = $db->Execute("SELECT * FROM `mail` WHERE `id`=".$_GET['read']." AND `owner`=".$player->id);
	$_GET['one'] = 1;
      }
    if (!$objMails->fields['id'])
      {
	error("Nie ma takiej wiadomości.");
      }
    $strSubject = $objMails->fields['subject'];
    while (!$objMails->EOF)
      {
	if ($intReceiver == 0)
	  {
	    if ($objMails->fields['senderid'] != $player->id)
	      {
		$intReceiver = $objMails->fields['senderid'];
	      }
	    elseif ($objMails->fields['to'] != $player->id)
	      {
		$intReceiver = $objMails->fields['to'];
	      }
	  }
	$arrSender[] = $objMails->fields['sender'];
	$arrSenderid[] = $objMails->fields['senderid'];
	$arrMid[] = $objMails->fields['id'];
	$arrBody[] = $objMails->fields['body'];
	$arrDate[] = T_DAY.$objMails->fields['date'];
	$objMails->MoveNext();
      }
    $objMails->Close();
    $smarty -> assign(array("Senders" => $arrSender,
			    "Sendersid" => $arrSenderid,
			    "Mailsid" => $arrMid,
			    "Bodies" => $arrBody,
			    "Date2" => $arrDate,
			    "Mtopic" => $_GET['read'],
			    "Receiver" => $intReceiver,
			    "Subject" => $strSubject,
			    "One" => $_GET['one'],
                            "Asave" => A_SAVE,
                            "Adelete" => A_DELETE,
                            "Twrite" => T_WRITE,
                            "Asend2" => A_SEND,
                            "Ablock" => A_BLOCK,
			    "Asaved" => "Oznaczone",
			    "Amail" => "Poczta",
			    "Topic" => "Temat",
			    "Asend" => "Odpisz",
			    "Tpages" => $intPages,
			    "Tpage" => $intPage,
			    "Fpage" => "Idź do strony:",
			    "Body" => '',
			    "Mhelp" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i>[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>"));
}

/**
 * Blocked list
 */
if (isset($_GET['view']) && $_GET['view'] == 'blocks')
{
    $objBlocked = $db -> Execute("SELECT `id` FROM `ban_mail` WHERE `owner`=".$player -> id);
    $arrId = array(0);
    $arrName = array();
    $i = 0;
    while (!$objBlocked -> EOF)
    {
        $arrId[$i] = $objBlocked -> fields['id'];
        $objName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$objBlocked -> fields['id']);
        $arrName[$i] = $objName -> fields['user'];
        $objBlocked -> MoveNext();
        $i ++;
    }
    $objBlocked -> Close();
    $smarty -> assign(array("Blockid" => $arrId,
                            "Blockname" => $arrName,
                            "Aunblock" => A_UNBLOCK,
                            "Nobanned" => NO_BANNED,
                            "Aback" => A_BACK));
    if (isset($_GET['step']) && $_GET['step'] == 'unblock')
    {
        foreach ($arrId as $bid) 
        {
            if (isset($_POST[$bid])) 
            {
                $db -> Execute("DELETE FROM `ban_mail` WHERE `id`=".$bid." AND `owner`=".$player -> id);
            }
        }
        message('success', YOU_UNBAN, '(<a href="mail.php?view=blocks">Odśwież</a>)');
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
}
if (!isset($_GET['read'])) 
{
    $_GET['read'] = '';
}
if (!isset($_GET['send'])) 
{
    $_GET['send'] = '';
}
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("View" => $_GET['view'], 
                        "Read" => $_GET['read'], 
                        "Send"  => $_GET['send'],
                        "Awrite" => A_WRITE,
                        "Ainbox" => A_INBOX,
                        "Step" => $_GET['step'],
                        "Adeletes" => A_DELETE_S,
                        "Tselect" => T_SELECT,
                        "Asaves" => A_SAVE_S));
$smarty -> display ('mail.tpl');

require_once("includes/foot.php"); 
?>
