<?php
/**
 *   File functions:
 *   Forums in game
 *
 *   @name                 : forums.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : mori <ziniquel@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 09.08.2011
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

$title = "Forum"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/forums.php");

/**
* Category list
*/
if (isset ($_GET['view']) && $_GET['view'] == 'categories') 
{
    /**
     * Display categories viewable for all
     */
    $cat = $db -> Execute("SELECT `id`, `name`, `desc` FROM `categories` WHERE `perm_visit` LIKE 'All;' AND `lang`='".$player -> lang."' OR `lang`='".$player -> seclang."' ORDER BY `id` ASC");
    $arrid = array();
    $arrname = array();
    $arrtopics = array();
    $arrdesc = array();
    $i = 0;
    while (!$cat -> EOF) 
    {
        $query = $db -> Execute("SELECT count(`id`) FROM `topics` WHERE `cat_id`=".$cat -> fields['id']);
        $arrtopics[$i] = $query -> fields['count(`id`)'];
        $query -> Close();
        $arrid[$i] = $cat -> fields['id'];
        $arrname[$i] = $cat -> fields['name'];
        $arrdesc[$i] = $cat -> fields['desc'];
        $cat -> MoveNext();
        $i ++;
    }
    $cat -> Close();
    /**
     * Display categories with permission to view
     */
    if ($player -> rank == 'Admin')
    {
        $strPermission = '%';
    }
    else
    {
        $strPermission = $player -> rank;
    }
    $cat = $db -> Execute("SELECT `id`, `name`, `desc` FROM `categories` WHERE `perm_visit` LIKE '%".$strPermission."%' AND `lang`='".$player -> lang."' OR `lang`='".$player -> seclang."' ORDER BY `id` ASC");
    while (!$cat -> EOF) 
    {
        if (in_array($cat -> fields['id'], $arrid))
        {
            $cat -> MoveNext();
            continue;
        }
        $query = $db -> Execute("SELECT count(`id`) FROM `topics` WHERE `cat_id`=".$cat -> fields['id']);
        $arrtopics[$i] = $query -> fields['count(`id`)'];
        $query -> Close();
        $arrid[$i] = $cat -> fields['id'];
        $arrname[$i] = $cat -> fields['name'];
        $arrdesc[$i] = $cat -> fields['desc'];
        $cat -> MoveNext();
        $i ++;
    }
    $cat -> Close();
    $smarty -> assign(array("Id" => $arrid, 
        "Name" => $arrname, 
        "Topics1" => $arrtopics, 
        "Description" => $arrdesc,
        "Tcategory" => T_CATEGORY,
        "Ttopics" => T_TOPICS));
}

/**
* Topic list
*/
if (isset($_GET['topics'])) 
 {
   checkvalue($_GET['topics']);
   //Count amount of pages
    $objAmount = $db->Execute("SELECT count(`id`) FROM `topics` WHERE `cat_id`=".$_GET['topics']." AND `lang`='".$player -> lang."' OR `lang`='".$player -> seclang."'");
    $pages = ceil($objAmount->fields['count(`id`)'] / 25);
    $objAmount->close();
    if (isset($_GET['page']))
     {
       $_GET['page'] = intval($_GET['page']);
       if ($_GET['page'] == 0)
	 {
	   error(ERROR);
	 }
       $page = $_GET['page'];
     }
    else
     {
       $page = $pages;
     }
    /**
     * Check for permissions
     */
    if ($player -> rank != 'Admin')
    {
        $objPerm = $db -> Execute("SELECT `perm_visit` FROM `categories` WHERE `id`=".$_GET['topics']);
        if ($objPerm -> fields['perm_visit'] != 'All;')
        {
            $intPerm = strpos($objPerm -> fields['perm_visit'], $player -> rank);
            if ($intPerm === false)
            {
                error(NO_PERM);
            }
        }
        $objPerm -> Close();
    }

    /**
    * Show new topic and replies on forums
    */
    if (!isset($_SESSION['forums']))
    {
        $objLasttime = $db -> Execute("SELECT `forum_time` FROM `players` WHERE `id`=".$player -> id);
        $_SESSION['forums'] = $objLasttime -> fields['forum_time'];
        $objLasttime -> Close();
        $db -> Execute("UPDATE `players` SET `forum_time`=".$ctime." WHERE `id`=".$player -> id);
    }

    /**
     * Select sticky threads
     */
    $intOffset = 25 * ($page - 1);
    $topic = $db -> SelectLimit("SELECT `w_time`, `id`, `topic`, `starter` FROM `topics` WHERE `sticky`='Y' AND `cat_id`=".$_GET['topics']." AND `lang`='".$player -> lang."' OR `lang`='".$player -> seclang."' ORDER BY `id` ASC", 25, $intOffset);
    $arrid = array();
    $arrtopic = array();
    $arrstarter = array();
    $arrreplies = array();
    $arrNewtopic = array();
    $i = 0;
    while (!$topic -> EOF) 
    {
        if ($topic -> fields['w_time'] > $_SESSION['forums'])
        {
            $arrNewtopic[$i] = 'Y';
        }
	else
        {
            $arrNewtopic[$i] = 'N';
        }
        $query = $db -> Execute("SELECT `w_time` FROM `replies` WHERE `topic_id`=".$topic -> fields['id']);
        if ($arrNewtopic[$i] == 'N')
        {
            while (!$query -> EOF)
            {
                if ($query -> fields['w_time'] > $_SESSION['forums'])
                {
                    $arrNewtopic[$i] = 'Y';
                    break;
                }
                $query -> MoveNext();
            }
        }
        $replies = $query -> RecordCount();
        $query -> Close();
        $arrid[$i] = $topic -> fields['id'];
        $arrtopic[$i] = "<b>".$topic -> fields['topic']."</b>";
        $arrstarter[$i] = $topic -> fields['starter'];
        $arrreplies[$i] = $replies;
        $topic -> MoveNext();
	$i++;
    }
    $topic -> Close();

    if ($i == 0)
      {
	$objTest = $db->Execute("SELECT count(`id`) FROM `topics` WHERE `sticky`='Y' AND `cat_id`=".$_GET['topics']." AND `lang`='".$player -> lang."' OR `lang`='".$player -> seclang."'");
	$j = $objTest->fields['count(`id`)'];
	$objTest->Close();
      }
    else
      {
	$j = $i;
      }

    if ($intOffset - $j < 0)
      {
	$intOffset += $j;
      }

    /**
     * Select normal threads
     */
    $topic = $db -> SelectLimit("SELECT `w_time`, `id`, `topic`, `starter` FROM `topics` WHERE `sticky`='N' AND `cat_id`=".$_GET['topics']." AND `lang`='".$player -> lang."' OR `lang`='".$player -> seclang."' ORDER BY `id` ASC", 25, $intOffset - $j);
    while (!$topic -> EOF) 
      {
	if ($topic -> fields['w_time'] > $_SESSION['forums'])
	  {
	    $arrNewtopic[$i] = 'Y';
	  }
	else
	  {
	    $arrNewtopic[$i] = 'N';
	  }
	$query = $db -> Execute("SELECT `w_time` FROM `replies` WHERE `topic_id`=".$topic -> fields['id']);
	if ($arrNewtopic[$i] == 'N')
	  {
	    while (!$query -> EOF)
	      {
		if ($query -> fields['w_time'] > $_SESSION['forums'])
		  {
		    $arrNewtopic[$i] = 'Y';
		    break;
		  }
		$query -> MoveNext();
	      }
	  }
	$replies = $query -> RecordCount();
	$query -> Close();
	$arrid[$i] = $topic -> fields['id'];
	$arrtopic[$i] = $topic -> fields['topic'];
	$arrstarter[$i] = $topic -> fields['starter'];
	$arrreplies[$i] = $replies;
	$topic -> MoveNext();
	$i++;
	if ($i > 24)
	  {
	    break;
	  }
      }
    $topic -> Close();
    
    $smarty -> assign(array("Category" => $_GET['topics'], 
        "Id" => $arrid, 
        "Topic1" => $arrtopic, 
        "Starter1" => $arrstarter, 
        "Replies1" => $arrreplies,
        "Ttopic" => T_TOPIC,
        "Tauthor" => T_AUTHOR,
        "Treplies" => T_REPLIES,
        "Addtopic" => ADD_TOPIC,
        "Ttext" => T_TEXT,
        "Aback" => A_BACK,
        "Tocategories" => TO_CATEGORIES,
        "Asearch" => A_SEARCH,
        "Tword" => T_WORD,
        "Tsticky" => T_STICKY,
	"Tpages" => $pages,
	"Tpage" => $page,
	"Fpage" => "Idź do strony:",
        "Newtopic" => $arrNewtopic));
}

/**
* View topic
*/
if (isset($_GET['topic'])) 
  {
    checkvalue($_GET['topic']);
    $topicinfo = $db -> Execute("SELECT * FROM `topics` WHERE `id`=".$_GET['topic']);
    if (!$topicinfo -> fields['id']) 
    {
        error (NO_TOPIC);
    }
    $objAmount = $db->Execute("SELECT count(`id`) FROM `replies` WHERE `topic_id`=".$topicinfo -> fields['id']);
    $pages = ceil($objAmount->fields['count(`id`)'] / 25);
    $objAmount->close();
    if (isset($_GET['page']))
     {
       $_GET['page'] = intval($_GET['page']);
       if ($_GET['page'] == 0)
	 {
	   error(ERROR);
	 }
       $page = $_GET['page'];
     }
   else
     {
       $page = $pages;
     }
    /**
     * Check for permissions
     */
    if ($player -> rank != 'Admin')
    {
        $objPerm = $db -> Execute("SELECT `perm_visit` FROM `categories` WHERE `id`=".$topicinfo -> fields['cat_id']);
        if ($objPerm -> fields['perm_visit'] != 'All;')
        {
            $intPerm = strpos($objPerm -> fields['perm_visit'], $player -> rank);
            if ($intPerm === false)
            {
                error(NO_PERM);
            }
        }
        $objPerm -> Close();
    }
    if ($topicinfo -> fields['sticky'] == 'N')
    {
        $strStickyaction = " (<a href=\"forums.php?sticky=".$topicinfo -> fields['id']."&amp;action=Y\">".A_STICKY."</a>)";
    }
    else
    {
        $strStickyaction = " (<a href=\"forums.php?sticky=".$topicinfo -> fields['id']."&amp;action=N\">".A_UNSTICKY."</a>)";
    }
    if ($player -> rank == 'Admin' || $player -> rank == 'Staff') 
    {
        $smarty -> assign ("Action", " (<a href=forums.php?kasuj1=".$topicinfo -> fields['id'].">".A_DELETE."</a>)".$strStickyaction);
    } 
    else 
    {
        $smarty -> assign("Action", '');
    }
    $text1 = wordwrap($topicinfo -> fields['body'],45,"\n",1);
    if (isset($_GET['quotet']))
      {
	$strReplytext = "[quote]".$text1."[/quote]";
      }
    else
    {
        $strReplytext = R_TEXT;
    }
    $reply = $db->SelectLimit("SELECT * FROM `replies` WHERE `topic_id`=".$topicinfo -> fields['id']." ORDER BY `id` ASC", 25, 25 * ($page - 1));
    $arrstarter = array();
    $arrplayerid = array();
    $arrtext = array();
    $arraction = array();
    $arrRid = array();
    $i = 0;
    while (!$reply -> EOF) 
    {
        $arrstarter[$i] = $reply -> fields['starter'];
        $arrplayerid[$i] = $reply -> fields['gracz'];
        if ($player -> rank == 'Admin' || $player -> rank == 'Staff') 
        {
            $arraction[$i] = "(<a href=forums.php?kasuj=".$reply -> fields['id'].">".A_DELETE."</a>)";
        } 
            else 
        {
            $arraction[$i] = '';
        }
        $text = wordwrap($reply -> fields['body'],45,"\n",1);
        if (isset($_GET['quote']) && $_GET['quote'] == $reply -> fields['id'])
        {
            $strText = preg_replace("/[0-9][0-9]-[0-9][0-9]-[0-9][0-9]/", "", $reply -> fields['body']);
            $strText = str_replace("<b></b><br />", "", $strText);
            $strReplytext = "[quote]".$strText."[/quote]";
        }
        $arrtext[$i] = $text;
        $arrRid[$i] = $reply -> fields['id'];
        $reply -> MoveNext();
        $i = $i + 1;
    }
    $reply -> Close();
    $smarty -> assign(array("Topic2" => $topicinfo -> fields['topic'], 
        "Starter" => $topicinfo -> fields['starter'], 
        "Playerid" => $topicinfo -> fields['gracz'], 
        "Category" => $topicinfo -> fields['cat_id'], 
        "Ttext" => $text1, 
        "Rstarter" => $arrstarter, 
        "Rplayerid" => $arrplayerid, 
        "Rtext" => $arrtext, 
        "Action2" => $arraction, 
        "Id" => $topicinfo -> fields['id'],
        "Rid" => $arrRid,
        "Writeby" => WRITE_BY,
        "Wid" => W_ID,
        "Areply" => A_REPLY,
        "Rtext2" => $strReplytext,
        "Aback" => A_BACK,
        "Aquote" => A_QUOTE,
	"Tpages" => $pages,
	"Tpage" => $page,
        "Fpage" => "Idź  do strony:",
        "Write" => WRITE));
    $topicinfo -> Close();
}

/**
* Add topic
*/
if (isset ($_GET['action']) && $_GET['action'] == 'addtopic') 
{
    if (empty ($_POST['title2']) || empty ($_POST['body'])) 
    {
        error (EMPTY_FIELDS);
    }
    /**
     * Check for permissions
     */
    checkvalue($_POST['catid']);
    if ($player -> rank != 'Admin')
    {
        $objPerm = $db -> Execute("SELECT `perm_write` FROM `categories` WHERE `id`=".$_POST['catid']);
        if ($objPerm -> fields['perm_write'] != 'All;')
        {
            $intPerm = strpos($objPerm -> fields['perm_write'], $player -> rank);
            if ($intPerm === false)
            {
                error(NO_PERM2);
            }
        }
        $objPerm -> Close();
    }
    if (isset($_POST['sticky']))
    {
        if ($player -> rank != 'Admin' && $player -> rank != 'Staff')
        {
            error(NO_PERM3);
        }
        $strSticky = 'Y';
    }
        else
    {
        $strSticky = 'N';
    }
    $_POST['title2'] = strip_tags($_POST['title2']);
    require_once('includes/bbcode.php');
    $_POST['body'] = bbcodetohtml($_POST['body']);    
    $_POST['title2'] = "<b>".$data."</b> ".$_POST['title2'];
    $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
    $strTitle = $db -> qstr($_POST['title2'], get_magic_quotes_gpc());
    $db -> Execute("INSERT INTO `topics` (`topic`, `body`, `starter`, `gracz`, `cat_id`, `w_time`, `sticky`) VALUES(".$strTitle.", ".$strBody.", '".$player -> user."', ".$player -> id.", ".$_POST['catid'].", ".$ctime.", '".$strSticky."')") or die("Could not add topic.");
    error (TOPIC_ADD." <a href=forums.php?topics=".$_POST['catid'].">".TO_BACK);
}

/**
* Add reply
*/
if (isset($_GET['reply'])) 
  {
    checkvalue($_GET['reply']);
    $query = $db -> Execute("SELECT `cat_id` FROM `topics` WHERE `id`=".$_GET['reply']);
    /**
     * Check for permissions
     */
    if ($player -> rank != 'Admin')
    {
        $objPerm = $db -> Execute("SELECT `perm_write` FROM `categories` WHERE `id`=".$query -> fields['cat_id']);
        if ($objPerm -> fields['perm_write'] != 'All;')
        {
            $intPerm = strpos($objPerm -> fields['perm_write'], $player -> rank);
            if ($intPerm === false)
            {
                error(NO_PERM2);
            }
        }
        $objPerm -> Close();
    }
    $exists = $query -> RecordCount();
    $query -> Close();
    if ($exists <= 0) 
    {
        error (NO_TOPIC);
    }
    if (empty ($_POST['rep'])) 
    {
        error (EMPTY_FIELDS);
    }
    require_once('includes/bbcode.php');
    $_POST['rep'] = bbcodetohtml($_POST['rep']);
    $_POST['rep'] = "<b>".$data."</b><br />".$_POST['rep'];
    $strBody = $db -> qstr($_POST['rep'], get_magic_quotes_gpc());
    $db -> Execute("INSERT INTO `replies` (`starter`, `topic_id`, `body`, `gracz`, `w_time`) VALUES('".$player -> user."', ".$_GET['reply'].", ".$strBody.", ".$player -> id.", ".$ctime.")") or die("Could not add reply.");
    error (REPLY_ADD." <a href=forums.php?topic=".$_GET['reply'].">".A_HERE."</a>.");
}

/**
 * Sticky/Unsticky topics
 */
if (isset($_GET['sticky']))
{
    if ($player -> rank != 'Admin' && $player -> rank != 'Staff')
    {
        error(ERROR);
    }
    checkvalue($_GET['sticky']);
    if ($_GET['action'] != 'Y' && $_GET['action'] != 'N')
    {
        error(ERROR);
    }
    $db -> Execute("UPDATE topics SET sticky='".$_GET['action']."' WHERE id=".$_GET['sticky']);
    if ($_GET['action'] == 'Y')
    {
      $strInfo = YOU_STICKY;
    }
        else
    {
        $strInfo = YOU_UNSTICKY;
    }
    error($strInfo." <a href=forums.php?topic=".$_GET['sticky'].">".A_BACK."</a>");
}

/**
* Delete post
*/
if (isset($_GET['kasuj'])) 
{
    if ($player -> rank != 'Admin' && $player -> rank != 'Staff')
    {
        error(ERROR);
    }
    checkvalue($_GET['kasuj']);
    $tid = $db -> Execute("SELECT `topic_id` FROM `replies` WHERE `id`=".$_GET['kasuj']);
    $db -> Execute("DELETE FROM `replies` WHERE `id`=".$_GET['kasuj']);
    error (POST_DEL." <a href=forums.php?topic=".$tid -> fields['topic_id'].">".A_BACK."</a>");
}

/**
* Delete topic
*/
if (isset($_GET['kasuj1'])) 
{
    if ($player -> rank != 'Admin' && $player -> rank != 'Staff')
    {
        error(ERROR);
    }
    checkvalue($_GET['kasuj1']);
    $cid = $db -> Execute("SELECT `cat_id` FROM `topics` WHERE `id`=".$_GET['kasuj1']);
    $db -> Execute("DELETE FROM `replies` WHERE `topic_id`=".$_GET['kasuj1']);
    $db -> Execute("DELETE FROM `topics` WHERE `id`=".$_GET['kasuj1']);
    error (TOPIC_DEL." <a href=forums.php?topics=".$cid -> fields['cat_id'].">".A_BACK."</a>");
}

/**
* Search words
*/
if (isset($_GET['action']) && $_GET['action'] == 'search')
{
    if (empty($_POST['search']))
    {
        error(EMPTY_FIELDS);
    }
    checkvalue($_POST['catid']);
    $strSearch = strip_tags($_POST['search']);
    
    /**
    * Search string in topics
    */
    $objResult = $db -> Execute("SELECT `id` FROM `topics` WHERE `cat_id`=".$_POST['catid']." AND `topic` LIKE '%".$strSearch."%' OR `body` LIKE '%".$strSearch."%'");
    $arrResult = array();
    $i = 0;
    while (!$objResult -> EOF)
    {
        $arrResult[$i] = $objResult -> fields['id'];
        $i = $i + 1;
        $objResult -> MoveNext();
    }
    $objResult -> Close();

    /**
    * Search string in replies
    */
    $objTopics = $db -> Execute("SELECT `id` FROM `topics` WHERE `cat_id`=".$_POST['catid']);
    $intTest = 0;
    while (!$objTopics -> EOF)
    {
        $objResult2 = $db -> Execute("SELECT `topic_id` FROM `replies` WHERE `topic_id`=".$objTopics -> fields['id']." AND `body` LIKE '%".$strSearch."%'");
        foreach ($arrResult as $intResult)
        {
            if ($intResult == $objResult2 -> fields['topic_id'])
            {
                $intTest = 1;
                break;
            }
        }
        if (!$intTest && $objResult2 -> fields['topic_id'])
        {
            $arrResult[$i] = $objResult2 -> fields['topic_id'];
            $i = $i + 1;
            $intTest = 0;
        }
        $objResult2 -> Close();
        $objTopics -> MoveNext();
    }
    $objTopics -> Close();

    /**
    * Display search result
    */
    $arrTopic = array();
    $arrId = array();
    $i = 0;
    foreach ($arrResult as $intResult)
    {
        $objTopic = $db -> Execute("SELECT `id`, `topic`, `cat_id` FROM `topics` WHERE `id`=".$intResult);
        $objPerm = $db -> Execute("SELECT `perm_visit` FROM `categories` WHERE `id`=".$objTopic -> fields['cat_id']);
        if ($objPerm -> fields['perm_visit'] != 'All;' && $player -> rank != 'Admin')
        {
            $intPerm = strpos($objPerm -> fields['perm_visit'], $player -> rank);
            if ($intPerm === false)
            {
                continue;
            }
        }
        $objPerm -> Close();
        $arrTopic[$i] = $objTopic -> fields['topic'];
        $arrId[$i] = $objTopic -> fields['id'];
        $i = $i + 1;
        $objTopic -> Close();
    }
    $smarty -> assign(array("Category" => $_POST['catid'],
        "Aback" => A_BACK,
        "Amount" => $i,
        "Ttopic" => $arrTopic,
        "Tid" => $arrId,
        "Nosearch" => NO_SEARCH,
        "Youfind" => YOU_FIND));
}

/**
* Initialization of variables
*/
if (!isset($_GET['topics'])) 
{
    $_GET['topics'] = '';
}

if (!isset($_GET['topic'])) 
{
    $_GET['topic'] = '';
}

if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
}

if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("View" => $_GET['view'], 
    "Topics" => $_GET['topics'], 
    "Topic" => $_GET['topic'],
    "Action3" => $_GET['action'],
    "Rank" => $player -> rank));
$smarty -> display ('forums.tpl');

require_once("includes/foot.php");
?>
