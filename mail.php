<?php
/**
 *   File functions:
 *   Messages to other players
 *
 *   @name                 : mail.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 03.12.2011
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
require_once("languages/".$player -> lang."/mail.php");

if (!isset($_GET['view']) && !isset($_GET['read']) && !isset($_GET['zapisz']) && !isset($_GET['kasuj']))
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
	    error(ERROR);
	  }
	$_POST['search'] = strip_tags($_POST['search']);
        $strSearch = $db -> qstr("*".$_POST['search']."*", get_magic_quotes_gpc());
	$objAmount = $db->Execute("SELECT count(`id`) FROM `mail` WHERE `owner`=".$player->id." AND MATCH(`subject`, `body`) AGAINST (".$strSearch." IN BOOLEAN MODE)") or die($db -> ErrorMsg());
	if ($objAmount->fields['count(`id`)'] == 0)
	  {
	    error("Nie znaleziono jakiejkolwiek wiadomości.");
	  }
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
    $objSort = $db->Execute("SELECT `sender`, `senderid` FROM `mail`  WHERE `owner`=".$player -> id." GROUP BY `senderid` ASC");
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
    $objAmount = $db->Execute("SELECT count(`id`) FROM `mail` WHERE `owner`=".$player->id.$strQuery." GROUP BY `topic`");
    $intPages = ceil($objAmount->fields['count(`id`)'] / 30);
    $objAmount->Close();
    if (!isset($intPage))
      {
	$intPage = 1;
      }

    $mail = $db->SelectLimit("SELECT * FROM (SELECT * FROM `mail` WHERE `owner`=".$player->id.$strQuery." ORDER BY `id` ASC) AS s GROUP BY `topic`", 30, 30 * ($intPage - 1));
    $arrsender = array();
    $arrsenderid = array();
    $arrsubject = array();
    $arrid = array();
    $arrRead = array();
    $arrTopic = array();
    while (!$mail -> EOF) 
      {
        $arrsender[] = $mail -> fields['sender'];
        $arrsenderid[] = $mail -> fields['senderid'];
        $arrsubject[] = $mail -> fields['subject'];
        $arrid[] = $mail -> fields['id'];
	$arrTopic[] = $mail->fields['topic'];
        if ($mail -> fields['unread'] == 'F')
        {
            $arrRead[] = 'Y';
        }
            else
        {
            $arrRead[] = 'N';
        }
        $mail -> MoveNext();
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
        error (MAIL_DEL.". (<a href=mail.php?view=inbox>".A_REFRESH."</a>)");
    }
}

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
    $objAmount = $db->Execute("SELECT count(`id`) FROM `mail` WHERE `owner`=".$player -> id." AND `saved`='Y'".$strQuery." GROUP BY `topic`");
    $intPages = ceil($objAmount->fields['count(`id`)'] / 30);
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
        error (MAIL_DEL.". (<a href=mail.php?view=zapis>".A_REFRESH."</a>)");
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
                            "Asend" => A_SEND,
			    "Contacts" => $arrContacts,
			    "Mhelp" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i><[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>"));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
        if ($_POST['player'] != 0)
	  {
	    checkvalue($_POST['player']);
	    $_POST['to'] = $_POST['player'];
	  }
	else
	  {
	    checkvalue($_POST['to']);
	  }
	$_POST['subject'] = strip_tags($_POST['subject']);
        if (empty ($_POST['to']) || empty ($_POST['body']) || empty($_POST['subject'])) 
        {
            error (EMPTY_FIELDS);
        }
	if (isset($_POST['topic']))
	  {
	    checkvalue($_POST['topic']);
	  }
        $rec = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['to']);
        if (!$rec -> fields['id']) 
        {
            error (NO_PLAYER);
        }
        if ($_POST['to'] == $player -> id)
        {
            error(YOURSELF);
        }
        $objBan = $db -> Execute("SELECT `id` FROM `ban_mail` WHERE `owner`=".$_POST['to']." AND `id`=".$player -> id);
        if ($objBan -> fields['id'])
        {
            error(YOU_CANNOT);
        }
        $objBan -> Close();
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
	    $objId = $db->Execute("SELECT min(`id`) FROM `mail` WHERE `owner`=".$_POST['to']." AND `topic`=".$_POST['topic']);
	    $db->Execute("UPDATE `mail` SET `unread`='F' WHERE `id`=".$objId->fields['min(`id`)']);
	    $objId->Close();
	  }
	$db -> Execute("INSERT INTO mail (`sender`, `senderid`, `owner`, `subject`, `body`, `date`, `topic`, `unread`) VALUES('".$player -> user."','".$player -> id."',".$_POST['to'].", ".$strSubject." , ".$strBody.", ".$strDate.", ".$_POST['topic'].", '".$strUnread."')");
	$db -> Execute("INSERT INTO mail (`sender`, `senderid`, `owner`, `subject`, `body`, `date`, `unread`, `topic`) VALUES('".$player -> user."','".$player -> id."',".$player -> id.", ".$strSubject.", ".$strBody.", ".$strDate.", 'T', ".$_POST['topic'].")");
        error (YOU_SEND.$rec -> fields['user'].".");
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
	if ($intReceiver == 0 && $objMails->fields['senderid'] != $player->id)
	  {
	    $intReceiver = $objMails->fields['senderid'];
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
			    "Mhelp" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i><[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>"));
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
        error (NO_MAIL);
    }
    $db -> Execute("UPDATE `mail` SET `saved`='Y' WHERE `id`=".$_GET['zapisz']);
    error (MAIL_SAVE.". (<a href=mail.php>".A_REFRESH."</a>)");
}

/**
 * Delete selected message
 */
if (isset ($_GET['kasuj'])) 
{
    checkvalue($_GET['kasuj']);
    $mail = $db -> Execute("SELECT `id` FROM `mail` WHERE `id`=".$_GET['kasuj']." AND `owner`=".$player->id);
    if (!$mail -> fields['id']) 
    {
        error (NO_MAIL);
    }
    $db->Execute("DELETE FROM `mail` WHERE `id`=".$_GET['kasuj']);
    error (MAIL_DEL.". (<a href=mail.php>".A_REFRESH."</a>)");
}

/**
* Send mail to admin or staff
*/
if (isset ($_GET['send'])) 
{
    $sid = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `rank`='Admin' OR `rank`='Staff'");
    $arrid = array();
    $arrname = array();
    $i = 0;
    while (!$sid -> EOF) 
    {
        $arrid[$i] = $sid -> fields['id'];
        $arrname[$i] = $sid -> fields['user'];
        $sid -> MoveNext();
        $i = $i + 1;
    }
    $sid -> Close();
    $smarty -> assign(array("Send" => $_GET['send'], 
                            "Staffid" => $arrid, 
                            "Name" => $arrname,
                            "Sendthis" => SEND_THIS,
                            "Asend" => A_SEND));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
	checkvalue($_POST['staff']);
	checkvalue($_POST['mid']);
        $arrtest = $db -> Execute("SELECT `id`, `user`, `rank` FROM `players` WHERE `id`=".$_POST['staff']);
        if (!$arrtest -> fields['id']) 
        {
            error (NO_PLAYER);
        }
        if ($arrtest -> fields['rank'] != 'Admin' && $arrtest -> fields['rank'] != 'Staff') 
        {
            error (NOT_STAFF);
        }
        $arrmessage = $db -> Execute("SELECT * FROM `mail` WHERE `id`=".$_POST['mid']." AND `owner`=".$player->id);
        if (!$arrmessage -> fields['id']) 
        {
            error (NOT_MAIL);
        }
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$arrtest -> fields['id'].",'".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user."</a>".L_ID.$player -> id.SEND_YOU.$arrmessage -> fields['sender'].L_ID.$arrmessage -> fields['senderid'].".', ".$strDate.", 'A')");
        $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".$player -> user."','".$player -> id."',".$arrtest -> fields['id'].",'".M_TITTLE.$arrmessage -> fields['sender'].L_ID.$arrmessage -> fields['senderid']."','".M_TITLE2.$arrmessage -> fields['subject'].M_DATE.$arrmessage -> fields['date'].M_BODY.$arrmessage -> fields['body']."', ".$strDate.")");
        error (YOU_SEND.$arrtest -> fields['user'].". <a href=mail.php>".A_REFRESH."</a>");
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
	    $db->Execute("DELETE FROM `mail` WHERE `topic`=".$objTopic->fields['topic']);
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
        error(DELETED);
    }
    elseif (isset($_POST['write']))
    {
        error(SAVED);
    }
    elseif (isset($_POST['read2']))
    {
        error(MARK_AS_READ);
    }
    elseif (isset($_POST['unread']))
    {
        error(MARK_AS_UNREAD);
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
        error(ERROR);
    }
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
    }
    elseif ($_GET['box'] == 'W')
    {
        $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$player->id." AND `saved`='Y' AND `date`<".$strDate);
    }
    error(DELETED2);
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
        error(NO_PLAYER);
    }
    $objPlayer -> Close();
    $objBan = $db -> Execute("SELECT `id` FROM `ban_mail` WHERE `id`=".$_GET['block']." AND `owner`=".$player -> id);
    if ($objBan -> fields['id'])
    {
        $db -> Execute("DELETE FROM `ban_mail` WHERE `id`=".$_GET['block']." AND `owner`=".$player -> id);
        error(YOU_UNBLOCK);
    }
        else
    {
        $db -> Execute("INSERT INTO `ban_mail` (`id`, `owner`) VALUES(".$_GET['block'].", ".$player -> id.")");
        error(YOU_BLOCK);
    }
    $objBan -> Close();
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
        error(YOU_UNBAN);
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
if (!isset($_GET['zapisz'])) 
{
    $_GET['zapisz'] = '';
}
if (!isset($_GET['kasuj'])) 
{
    $_GET['kasuj'] = '';
}
if (!isset($_GET['send'])) 
{
    $_GET['send'] = '';
}
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}
if (!isset($_GET['block']))
{
    $_GET['block'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("View" => $_GET['view'], 
                        "Read" => $_GET['read'], 
                        "Write" => $_GET['zapisz'], 
                        "Delete" => $_GET['kasuj'], 
                        "Send"  => $_GET['send'],
                        "Block" => $_GET['block'],
                        "Awrite" => A_WRITE,
                        "Ainbox" => A_INBOX,
                        "Step" => $_GET['step'],
                        "Adeletes" => A_DELETE_S,
                        "Tselect" => T_SELECT,
                        "Asaves" => A_SAVE_S));
$smarty -> display ('mail.tpl');

require_once("includes/foot.php"); 
?>
