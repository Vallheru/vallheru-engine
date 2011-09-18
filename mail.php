<?php
/**
 *   File functions:
 *   Messages to other players
 *
 *   @name                 : mail.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 18.09.2011
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
                            "Aoutbox" => A_OUTBOX,
                            "Asaved" => A_SAVED,
                            "Awrite" => A_WRITE,
                            "Ablocklist" => A_BLOCK_LIST));
}

$strQuery = '';

/**
 * Sorting of mails.
 */
if (isset($_GET['view']) && in_array($_GET['view'], array('inbox', 'zapis', 'send')))
  {
    if (isset($_POST['sort1']))
      {
	$_POST['sort1'] = intval($_POST['sort1']);
	if ($_POST['sort1'] != -1)
	  {
	    if ($_GET['view'] != 'send')
	      {
		$strQuery = " AND `senderid`=".$_POST['sort1'];
	      }
	    else
	      {
		$strQuery = " AND `send`=".$_POST['sort1'];
	      }
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
      }

    if (isset($_GET['page']))
      {
	checkvalue($_GET['page']);
	$intPage = $_GET['page'];
      }
  }

/**
 * Mail inbox
 */
if (isset ($_GET['view']) && $_GET['view'] == 'inbox') 
  {
    $objSort = $db->Execute("SELECT `sender`, `senderid` FROM `mail`  WHERE `owner`=".$player -> id." AND `zapis`='N' AND `send`=0 GROUP BY `senderid` ASC");
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
    $objAmount = $db->Execute("SELECT count(`id`) FROM `mail` WHERE `owner`=".$player -> id." AND `zapis`='N' AND `send`=0".$strQuery);
    $intPages = ceil($objAmount->fields['count(`id`)'] / 30);
    $objAmount->Close();
    if (!isset($intPage))
      {
	$intPage = 1;
      }

    $mail = $db->SelectLimit("SELECT * FROM `mail` WHERE `owner`=".$player -> id." AND `zapis`='N' AND `send`=0".$strQuery." ORDER BY `id` DESC", 30, 30 * ($intPage - 1));
    $arrsender = array();
    $arrsenderid = array();
    $arrsubject = array();
    $arrid = array();
    $arrRead = array();
    while (!$mail -> EOF) 
      {
        $arrsender[] = $mail -> fields['sender'];
        $arrsenderid[] = $mail -> fields['senderid'];
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
                            "Mread" => $arrRead));
    if (isset ($_GET['step']) && $_GET['step'] == 'clear') 
    {
        $db -> Execute("DELETE FROM mail WHERE owner=".$player -> id." AND zapis='N' AND send=0");
        error (MAIL_DEL.". (<a href=mail.php?view=inbox>".A_REFRESH."</a>)");
    }
}

if (isset ($_GET['view']) && $_GET['view'] == 'zapis') 
{
    $objSort = $db->Execute("SELECT `sender`, `senderid` FROM `mail`  WHERE `owner`=".$player->id." AND `zapis`='Y' GROUP BY `senderid` ASC");
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
    $objAmount = $db->Execute("SELECT count(`id`) FROM `mail` WHERE `owner`=".$player -> id." AND `zapis`='Y' AND `send`=0".$strQuery);
    $intPages = ceil($objAmount->fields['count(`id`)'] / 30);
    $objAmount->Close();
    if (!isset($intPage))
      {
	$intPage = 1;
      }

    $mail = $db->SelectLimit("SELECT * FROM `mail` WHERE `owner`=".$player -> id." AND `zapis`='Y'".$strQuery." ORDER BY id DESC", 30, 30 * ($intPage - 1));
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
                            "Moption" => M_OPTION));
    if (isset ($_GET['step']) && $_GET['step'] == 'clear') 
    {
        $db -> Execute("DELETE FROM mail WHERE owner=".$player -> id." AND zapis='Y'");
        error (MAIL_DEL.". (<a href=mail.php?view=zapis>".A_REFRESH."</a>)");
    }
}

if (isset ($_GET['view']) && $_GET['view'] == 'send') 
{
    $objSort = $db->Execute("SELECT `send` FROM `mail`  WHERE `owner`=".$player->id." AND `send`!=0 GROUP BY `send` ASC");
    $arrSendersid = array();
    $arrSenders = array();
    while (!$objSort->EOF) 
      {
	$arrSendersid[] = $objSort->fields['send'];
	$objName = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$objSort->fields['send']);
	$arrSenders[] = $objName->fields['user'];
	$objName->Close();
	$objSort->MoveNext();
      }
    $objSort->Close();

    //Pagination
    $objAmount = $db->Execute("SELECT count(`id`) FROM `mail` WHERE `send`!=0 AND `owner`=".$player->id.$strQuery);
    $intPages = ceil($objAmount->fields['count(`id`)'] / 30);
    $objAmount->Close();
    if (!isset($intPage))
      {
	$intPage = 1;
      }

    $mail = $db -> Execute("SELECT * FROM mail WHERE send!=0 AND owner=".$player->id.$strQuery." ORDER BY id DESC");
    $arrsend = array();
    $arrsubject = array();
    $arrid = array();
    $i = 0;
    while (!$mail -> EOF) 
    {
        $arrsend[$i] = $mail -> fields['send'];
        $arrsubject[$i] = $mail -> fields['subject'];
        $arrid[$i] = $mail -> fields['id'];
        $mail -> MoveNext();
        $i = $i + 1;
    }
    $mail -> Close();
    $smarty -> assign(array("Send1" => $arrsend, 
                            "Subject" => $arrsubject, 
                            "Mailid" => $arrid,
                            "Aclear" => A_CLEAR,
                            "Sto" => S_TO,
                            "Mtitle" => M_TITLE,
                            "Aread" => A_READ,
                            "Aback" => A_BACK,
                            "Adeleteold" => A_DELETE_OLD,
                            "Aweek" => A_WEEK,
                            "A2week" => A_2WEEK,
                            "Amonth" => A_MONTH,
			    "Asort" => "Sortuj",
			    "Sall" => "Wszyscy",
			    "Sall2" => "Bez ograniczeń",
			    "Senders" => $arrSenders,
			    "Sendersid" => $arrSendersid,
			    "Tsender" => "Odbiorca",
			    "Ttime" => "Okres",
			    "Tlastweek" => "Ostatni tydzień",
			    "Tlastmonth" => "Ostatni miesiąc",
			    "Toldest" => "Starsze niż miesiąc",
			    "Tpages" => $intPages,
			    "Tpage" => $intPage,
			    "Fpage" => "Idź do strony:",
                            "Moption" => M_OPTION));
    if (isset ($_GET['step']) && $_GET['step'] == 'clear') 
    {
        $db -> Execute("DELETE FROM mail WHERE send!=0 AND owner=".$player -> id);
        error (MAIL_DEL.". (<a href=mail.php?view=send>".A_REFRESH."</a>)");
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
    if (!isset ($_GET['to'])) 
    {
        $_GET['to'] = '';
    }
    if (!isset ($_GET['re'])) 
    {
        $_GET['re'] = '';
    }
    $body = '';
    if (!empty ($_GET['id'])) 
    {
	checkvalue($_GET['id']);
        $mail = $db -> Execute("SELECT `body`, `owner`, `sender` FROM `mail` WHERE `id`=".$_GET['id']);
        if ($mail -> fields['owner'] != $player -> id) 
        {
            error(NOT_YOUR);
        }
        require_once('includes/bbcode.php');
        $postbody = htmltobbcode($mail -> fields['body']);
        $body = "Gracz ".$mail -> fields['sender']." napisał(a):[quote]".$postbody."[/quote]";
        $mail -> Close();
    }
    $smarty -> assign(array("To" => $_GET['to'], 
                            "Reply" => $_GET['re'], 
                            "Body" => $body,
                            "Sto" => S_TO,
                            "Mtitle" => M_TITLE,
                            "Mbody" => M_BODY,
                            "Asend" => A_SEND));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
        if (empty ($_POST['to']) || empty ($_POST['body'])) 
        {
            error (EMPTY_FIELDS);
        }
        if (empty ($_POST['subject'])) 
        {
            $_POST['subject'] = "Brak";
        }
	checkvalue($_POST['to']);
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
        $_POST['subject'] = strip_tags($_POST['subject']);
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $strSubject = $db -> qstr($_POST['subject'], get_magic_quotes_gpc());
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO mail (sender, senderid, owner, subject, body, date) VALUES('".$player -> user."','".$player -> id."',".$_POST['to'].", ".$strSubject." , ".$strBody.", ".$strDate.")");
        $db -> Execute("INSERT INTO mail (sender, senderid, owner, subject, body,  send, date) VALUES('".$player -> user."','".$player -> id."',".$player -> id.", ".$strSubject.", ".$strBody.",".$_POST['to'].", ".$strDate.")");
        error (YOU_SEND.$rec -> fields['user'].".");
    }
}

if (isset ($_GET['read'])) 
{
    checkvalue($_GET['read']);
    $mail = $db -> Execute("SELECT * FROM mail WHERE id=".$_GET['read']);
    if (!$mail -> fields['id']) 
    {
        error (NO_MAIL);
    }
    if ($mail -> fields['owner'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    $db -> Execute("UPDATE mail SET unread='T' WHERE id=".$mail -> fields['id']);
    if ($mail -> fields['date'])
    {
        $strDay = T_DAY.$mail -> fields['date'];
    }
        else
    {
        $strDay = '';
    }
    $smarty -> assign(array("Sender" => $mail -> fields['sender'], 
                            "Body" => $mail -> fields['body'], 
                            "Mailid" => $mail -> fields['id'], 
                            "Senderid" => $mail -> fields['senderid'], 
                            "Subject" => $mail -> fields['subject'],
                            "Asave" => A_SAVE,
                            "Adelete" => A_DELETE,
                            "Areply" => A_REPLY,
                            "Twrite" => T_WRITE,
                            "Asend" => A_SEND,
                            "Ablock" => A_BLOCK,
                            "Tday" => $strDay));
    $mail -> Close();
}

if (isset ($_GET['zapisz'])) 
{
    checkvalue($_GET['zapisz']);
    $mail = $db -> Execute("SELECT id, owner FROM mail WHERE id=".$_GET['zapisz']);
    if (!$mail -> fields['id']) 
    {
        error (NO_MAIL);
    }
    if ($mail -> fields['owner'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    $db -> Execute("UPDATE mail SET zapis='Y' WHERE id=".$_GET['zapisz']);
    error (MAIL_SAVE.". (<a href=mail.php>".A_REFRESH."</a>)");
}

if (isset ($_GET['kasuj'])) 
{
    checkvalue($_GET['kasuj']);
    $mail = $db -> Execute("SELECT id, owner FROM mail WHERE id=".$_GET['kasuj']);
    if (!$mail -> fields['id']) 
    {
        error (NO_MAIL);
    }
    if ($mail -> fields['owner'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    $db -> Execute("DELETE FROM mail WHERE id=".$_GET['kasuj']);
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
        $arrmessage = $db -> Execute("SELECT * FROM `mail` WHERE `id`=".$_POST['mid']);
        if (!$arrmessage -> fields['id']) 
        {
            error (NOT_MAIL);
        }
        if ($arrmessage -> fields['owner'] != $player -> id) 
        {
            error (NOT_YOUR);
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
    if (!isset($_GET['box']))
    {
        error(ERROR);
    }
    $arrType = array('I', 'W', 'S');
    if (!in_array($_GET['box'], $arrType))
    {
        error(ERROR);
    }
    if ($_GET['box'] == 'I')
    {
        $objMid = $db -> Execute("SELECT `id` FROM `mail` WHERE `owner`=".$player -> id." AND `zapis`='N' AND `send`=0");
    }
    if ($_GET['box'] == 'W')
    {
        $objMid = $db -> Execute("SELECT `id` FROM `mail` WHERE `owner`=".$player -> id." AND `zapis`='Y'");
    }
    if ($_GET['box'] == 'S')
    {
        $objMid = $db -> Execute("SELECT `id` FROM `mail` WHERE `send`!=0 AND `owner`=".$player -> id);
    }
    $arrId = array();
    $i = 0;
    while (!$objMid -> EOF)
    {
        $arrId[$i] = $objMid -> fields['id'];
        $i = $i + 1;
        $objMid -> MoveNext();
    }
    $objMid -> Close();
    foreach ($arrId as $bid) 
    {
        if (isset($_POST['delete']))
        {
            if (isset($_POST[$bid])) 
            {
                $db -> Execute("DELETE FROM `mail` WHERE `id`=".$bid);
            }
        }
        if (isset($_POST['write']))
        {
            if (isset($_POST[$bid])) 
            {
                $db -> Execute("UPDATE `mail` SET `zapis`='Y' WHERE `id`=".$bid);
            }
        }
        if (isset($_POST['read2']))
        {
            if (isset($_POST[$bid])) 
            {
                $db -> Execute("UPDATE `mail` SET `unread`='T' WHERE `id`=".$bid);
            }
        }
        if (isset($_POST['unread']))
        {
            if (isset($_POST[$bid])) 
            {
                $db -> Execute("UPDATE `mail` SET `unread`='F' WHERE `id`=".$bid);
            }
        }
    }
    if (isset($_POST['delete']))
    {
        error(DELETED);
    }
    if (isset($_POST['write']))
    {
        error(SAVED);
    }
    if (isset($_POST['read2']))
    {
        error(MARK_AS_READ);
    }
    if (isset($_POST['unread']))
    {
        error(MARK_AS_UNREAD);
    }
}

/**
 * Delete old messages
 */
if (isset($_GET['step']) && $_GET['step'] == 'deleteold')
{
    $arrType = array('I', 'W', 'S');
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
        $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$player -> id." AND `zapis`='N' AND `send`=0 AND `date`<".$strDate);
    }
    if ($_GET['box'] == 'W')
    {
        $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$player -> id." AND `zapis`='Y' AND `date`<".$strDate);
    }
    if ($_GET['box'] == 'S')
    {
        $db -> Execute("DELETE FROM `mail` WHERE `send`!=0 AND `owner`=".$player -> id." AND `date`<".$strDate);
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
