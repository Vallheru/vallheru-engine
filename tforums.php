<?php
/**
 *   File functions:
 *   Clans forums
 *
 *   @name                 : tforums.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 28.08.2011
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

$title = "Forum klanu";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/tforums.php");

if ($player -> tribe == 0) 
{
    error (ERROR);
}

/**
* The topics list
*/
if (isset ($_GET['view']) && $_GET['view'] == 'topics') 
{
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $perm = $db -> Execute("SELECT `forum` FROM tribe_perm WHERE `tribe`=".$klan -> fields['id']." AND `player`=".$player -> id);
    if ($player -> id == $klan -> fields['owner'] || $perm -> fields['forum']) 
    {
        $smarty -> assign ("Sticky", "<input type=\"checkbox\" name=\"sticky\" />".T_STICKY."<br />");
    } 
        else 
    {
        $smarty -> assign("Sticky", '');
    }
    $perm -> Close();
    $klan -> Close();

    /**
    * Show new topic and replies on forums
    */
    if (!isset($_SESSION['tforums']))
    {
        $objLasttime = $db -> Execute("SELECT tforum_time FROM players WHERE id=".$player -> id);
        $_SESSION['tforums'] = $objLasttime -> fields['tforum_time'];
        $objLasttime -> Close();
        $db -> Execute("UPDATE players SET tforum_time=".$ctime." WHERE id=".$player -> id);
    }
    
    /**
     * Show sticky threads
     */
    $topic = $db -> Execute("SELECT * FROM tribe_topics WHERE tribe=".$player -> tribe." AND sticky='Y' ORDER BY id ASC");
    $arrid = array();
    $arrrep = array();
    $arrtopic = array();
    $arrstarter = array();
    $arrNewtopic = array();
    $i = 0;
    while (!$topic -> EOF) 
    {
        $arrid[$i] = $topic -> fields['id'];
        if ($topic -> fields['w_time'] > $_SESSION['tforums'])
        {
            $arrNewtopic[$i] = 'Y';
        }
            else
        {
            $arrNewtopic[$i] = 'N';
        }
        $query = $db -> Execute("SELECT w_time FROM tribe_replies WHERE topic_id=".$topic -> fields['id']);
        if ($arrNewtopic[$i] == 'N')
        {
            while (!$query -> EOF)
            {
                if ($query -> fields['w_time'] > $_SESSION['tforums'])
                {
                    $arrNewtopic[$i] = 'Y';
                    break;
                }
                $query -> MoveNext();
            }
        }
        $arrrep[$i] = $query -> RecordCount();
        $query -> Close();
        $arrtopic[$i] = "<b>".$topic -> fields['topic']."</b>";
        $arrstarter[$i] = $topic -> fields['starter'];
        $topic -> MoveNext();
        $i = $i + 1;
    }
    $topic -> Close();
    
    $topic = $db -> Execute("SELECT * FROM tribe_topics WHERE tribe=".$player -> tribe." AND sticky='N' ORDER BY id ASC");
    while (!$topic -> EOF) 
    {
        $arrid[$i] = $topic -> fields['id'];
        if ($topic -> fields['w_time'] > $_SESSION['tforums'])
        {
            $arrNewtopic[$i] = 'Y';
        }
            else
        {
            $arrNewtopic[$i] = 'N';
        }
        $query = $db -> Execute("SELECT w_time FROM tribe_replies WHERE topic_id=".$topic -> fields['id']);
        if ($arrNewtopic[$i] == 'N')
        {
            while (!$query -> EOF)
            {
                if ($query -> fields['w_time'] > $_SESSION['tforums'])
                {
                    $arrNewtopic[$i] = 'Y';
                    break;
                }
                $query -> MoveNext();
            }
        }
        $arrrep[$i] = $query -> RecordCount();
        $query -> Close();
        $arrtopic[$i] = $topic -> fields['topic'];
        $arrstarter[$i] = $topic -> fields['starter'];
        $topic -> MoveNext();
        $i = $i + 1;
    }
    $topic -> Close();
    $smarty -> assign(array("Topicid" => $arrid, 
        "Topic" => $arrtopic, 
        "Replies" => $arrrep, 
        "Starter" => $arrstarter,
        "Ttopic" => T_TOPIC,
        "Tauthor" => T_AUTHOR,
        "Treplies" => T_REPLIES,
        "Addtopic" => ADD_TOPIC,
        "Ttext" => T_TEXT,
        "Asearch" => A_SEARCH,
        "Tword" => T_WORD,
        "Newtopic" => $arrNewtopic));
}

/**
* View topic
*/
if (isset($_GET['topic'])) 
{
    checkvalue($_GET['topis']);
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $topicinfo = $db -> Execute("SELECT * FROM tribe_topics WHERE id=".$_GET['topic']." AND tribe=".$player -> tribe);
    $perm = $db -> Execute("SELECT forum FROM tribe_perm WHERE tribe=".$klan -> fields['id']." AND player=".$player -> id);
    if (!$topicinfo -> fields['id']) 
    {
        error (NO_TOPIC);
    }
    if (isset($_GET['quote']))
    {
        if (!(int)$_GET['quote'])
        {
            error(ERROR);
        }
        $objTest = $db -> Execute("SELECT id FROM tribe_replies WHERE id=".$_GET['quote']." AND topic_id=".$topicinfo -> fields['id']);
        if (!$objTest -> fields['id'])
        {
            error(ERROR);
        }
        $objTest -> Close();
    }
    $smarty -> assign(array("Topic" => $topicinfo -> fields['topic'], 
                            "Starter" => $topicinfo -> fields['starter'],
                            "Starterid" => $topicinfo -> fields['pid']));
    if ($topicinfo -> fields['sticky'] == 'N')
    {
        $strStickyaction = " (<a href=\"tforums.php?sticky=".$topicinfo -> fields['id']."&amp;action=Y\">".A_STICKY."</a>)";
    }
        else
    {
        $strStickyaction = " (<a href=\"tforums.php?sticky=".$topicinfo -> fields['id']."&amp;action=N\">".A_UNSTICKY."</a>)";
    }
    if ($player -> id == $klan -> fields['owner'] || $perm -> fields['forum']) 
    {
        $smarty -> assign ("Delete", " (<a href=tforums.php?kasuj1=".$topicinfo -> fields['id'].">".A_DELETE."</a>)".$strStickyaction);
    } 
        else 
    {
        $smarty -> assign("Delete", '');
    }
    $text = wordwrap($topicinfo -> fields['body'],45,"\n",1);
    if (isset($_GET['quotet']))
    {
        $strReplytext = "[quote]".$text."[/quote]";
    }
        else
    {
        $strReplytext = R_TEXT;
    }
    $smarty -> assign ("Topictext", $text);
    $reply = $db -> Execute("SELECT * FROM tribe_replies WHERE topic_id=".$topicinfo -> fields['id']." ORDER BY id ASC");
    $arrstarter = array();
    $arraction = array();
    $arrtext = array();
    $arrRid = array();
    $arrStarterid = array();
    $i = 0;
    while (!$reply -> EOF) 
    {
        $arrstarter[$i] = $reply -> fields['starter'];
        $arrStarterid[$i] = $reply -> fields['pid'];
        if ($player -> id == $klan -> fields['owner'] || $perm -> fields['forum']) 
        {
            $arraction[$i] = "(<a href=tforums.php?kasuj=".$reply -> fields['id'].">".A_DELETE."</a>)";
        } 
            else 
        {
            $arraction[$i] = '';
        }
        if (isset($_GET['quote']) && $_GET['quote'] == $reply -> fields['id'])
        {
            $strText = preg_replace("/[0-9][0-9]-[0-9][0-9]-[0-9][0-9]/", "", $reply -> fields['body']);
            $strText = str_replace("<b></b><br />", "", $strText);
            $strReplytext = "[quote]".$strText."[/quote]";
        }
        $arrtext[$i] = wordwrap($reply -> fields['body'],45,"\n",1);
        $arrRid[$i] = $reply -> fields['id'];
        $reply -> MoveNext();
        $i = $i + 1;
    }
    $reply -> Close();
    $klan -> Close();
    $smarty -> assign(array("Id" => $topicinfo -> fields['id'], 
                            "Repstarter" => $arrstarter, 
                            "Repstarterid" => $arrStarterid,
                            "Action" => $arraction, 
                            "Reptext" => $arrtext,
                            "Writeby" => WRITE_BY,
                            "Write" => WRITE,
                            "Areply" => A_REPLY,
                            "Rid" => $arrRid,
                            "Aquote" => A_QUOTE,
                            "Rtext" => $strReplytext,
                            "Aback" => A_BACK));
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
    if (isset($_POST['sticky']))
    {
        $klan = $db -> Execute("SELECT `id`, `owner` FROM `tribes` WHERE `id`=".$player -> tribe);
        $perm = $db -> Execute("SELECT `forum` FROM `tribe_perm` WHERE `tribe`=".$klan -> fields['id']." AND `player`=".$player -> id);
        if ($player -> id != $klan -> fields['owner'] && !$perm -> fields['forum']) 
        {
            error(NO_PERM);
        }
        $perm -> Close();
        $klan -> Close();
        $strSticky = 'Y';
    }
        else
    {
        $strSticky = 'N';
    }
    $_POST['title2'] = strip_tags($_POST['title2']);
    require_once('includes/bbcode.php');
    $_POST['body'] = bbcodetohtml($_POST['body']);
    $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
    $_POST['title2'] = "<b>".$data."</b> ".$_POST['title2'];
    $strTitle = $db -> qstr($_POST['title2'], get_magic_quotes_gpc());
    $db -> Execute("INSERT INTO `tribe_topics` (`topic`, `body`, `starter`, `tribe`, `w_time`, `sticky`, `pid`) VALUES(".$strTitle.", ".$strBody.", '".$player -> user."', '".$player -> tribe."', ".$ctime.", '".$strSticky."', ".$player -> id.")") or $db -> ErrorMsg();
    error (TOPIC_ADD." <a href=tforums.php?view=topics>".TO_BACK);
}

/**
* Add reply
*/
if (isset($_GET['reply'])) 
{
    checkvalue($_GET['reply']);
    $test = $db -> Execute("SELECT `tribe` FROM `tribe_topics` WHERE `id`=".$_GET['reply']." AND `tribe`=".$player -> tribe);
    if (!$test -> fields['tribe']) 
    {
        error (ERROR);
    }
    $test -> Close();
    $query = $db -> Execute("SELECT count(*) FROM `tribe_topics` WHERE `id`=".$_GET['reply']);
    $exists = $query -> fields['count(*)'];
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
    $strRep = $db -> qstr($_POST['rep'], get_magic_quotes_gpc());
    $db -> Execute("INSERT INTO `tribe_replies` (`starter`, `topic_id`, `body`, `w_time`, `pid`) VALUES('".$player -> user."', ".$_GET['reply'].", ".$strRep." ,".$ctime.", ".$player -> id.")") or error("Could not add reply.");
    error (REPLY_ADD." <a href=tforums.php?topic=".$_GET['reply'].">".A_HERE."</a>.");
}

/**
 * Sticky/Unsticky topics
 */
if (isset($_GET['sticky']))
{
    if (!(int)$_GET['sticky'])
    {
        error(ERROR);
    }
    if ($_GET['action'] != 'Y' && $_GET['action'] != 'N')
    {
        error(ERROR);
    }
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $perm = $db -> Execute("SELECT forum FROM tribe_perm WHERE tribe=".$klan -> fields['id']." AND player=".$player -> id);
    if ($player -> id != $klan -> fields['owner'] && !$perm -> fields['forum']) 
    {
        error(NO_PERM);
    }
    $perm -> Close();
    $klan -> Close();
    $test = $db -> Execute("SELECT id FROM tribe_topics WHERE id=".$_GET['sticky']." AND tribe=".$player -> tribe);
    if (!$test -> fields['id']) 
    {
        error (ERROR);
    } 
    $test -> Close();
    $db -> Execute("UPDATE tribe_topics SET sticky='".$_GET['action']."' WHERE id=".$_GET['sticky']);
    if ($_GET['action'] == 'Y')
    {
        $strInfo = YOU_STICKY;
    }
        else
    {
        $strInfo = YOU_UNSTICKY;
    }
    error($strInfo." <a href=tforums.php?view=topics>".A_BACK."</a>");
}

/**
* Delete post
*/
if (isset($_GET['kasuj'])) 
{
    if (!(int)$_GET['kasuj']) 
    {
        error (ERROR);
    }
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $perm = $db -> Execute("SELECT forum FROM tribe_perm WHERE tribe=".$klan -> fields['id']." AND player=".$player -> id);
    if ($player -> id != $klan -> fields['owner'] && !$perm -> fields['forum']) 
    {
        error(NO_PERM);
    }
    $perm -> Close();
    $klan -> Close();
    $test = $db -> Execute("SELECT topic_id FROM tribe_replies WHERE id=".$_GET['kasuj']);
    if ($test -> fields['topic_id']) 
    {
        $test2 = $db -> Execute("SELECT id FROM tribe_topics WHERE id=".$test -> fields['topic_id']." and tribe=".$player -> tribe);
        if (!$test2 -> fields['id']) 
        {
            error (ERROR);
        } 
            else 
        {
            $db -> Execute("DELETE FROM tribe_replies WHERE id=".$_GET['kasuj']);
            error (POST_DEL." <a href=tforums.php?view=topics>".A_BACK."</a>");
        }
    }
}

/**
* Delete topic
*/
if (isset($_GET['kasuj1'])) 
{
    if (!(int)$_GET['kasuj1']) 
    {
        error (ERROR);
    }
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $perm = $db -> Execute("SELECT forum FROM tribe_perm WHERE tribe=".$klan -> fields['id']." AND player=".$player -> id);
    if ($player -> id != $klan -> fields['owner'] && !$perm -> fields['forum']) 
    {
        error(NO_PERM);
    }
    $perm -> Close();
    $klan -> Close();
    $test = $db -> Execute("SELECT id FROM tribe_topics WHERE id=".$_GET['kasuj1']." AND tribe=".$player -> tribe);
    if (!$test -> fields['id']) 
    {
        error (ERROR);
    } 
        else 
    {
        $db -> Execute("DELETE FROM tribe_replies WHERE topic_id=".$_GET['kasuj1']);
        $db -> Execute("DELETE FROM tribe_topics WHERE id=".$_GET['kasuj1']);
        error (TOPIC_DEL." <a href=tforums.php?view=topics>".A_BACK."</a>");
    }
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
    $strSearch = strip_tags($_POST['search']);
    
    /**
    * Search string in topics
    */
    $objResult = $db -> Execute("SELECT id, tribe FROM tribe_topics WHERE tribe=".$player -> tribe." AND topic LIKE '%".$strSearch."%' OR body LIKE '%".$strSearch."%'");
    $arrResult = array();
    $i = 0;
    while (!$objResult -> EOF)
    {
        if ($objResult -> fields['tribe'] == $player -> tribe)
        {
            $arrResult[$i] = $objResult -> fields['id'];
            $i = $i + 1;
        }
        $objResult -> MoveNext();
    }
    $objResult -> Close();

    /**
    * Search string in replies
    */
    $objTopics = $db -> Execute("SELECT id FROM tribe_topics WHERE tribe=".$player -> tribe);
    $intTest = 0;
    while (!$objTopics -> EOF)
    {
        $objResult2 = $db -> Execute("SELECT topic_id FROM tribe_replies WHERE topic_id=".$objTopics -> fields['id']." AND body LIKE '%".$strSearch."%'");
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
        $objTopic = $db -> Execute("SELECT id, topic FROM tribe_topics WHERE id=".$intResult);
        $arrTopic[$i] = $objTopic -> fields['topic'];
        $arrId[$i] = $objTopic -> fields['id'];
        $i = $i + 1;
        $objTopic -> Close();
    }
    $smarty -> assign(array("Aback" => A_BACK,
        "Amount" => $i,
        "Ttopic" => $arrTopic,
        "Tid" => $arrId,
        "Nosearch" => NO_SEARCH,
        "Youfind" => YOU_FIND));
}

/**
* Initialization of variables
*/
if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
}
if (!isset($_GET['topic'])) 
{
    $_GET['topic'] = '';
}
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("View" => $_GET['view'], 
    "Topics" => $_GET['topic'],
    "Action2" => $_GET['action']));
$smarty -> display('tforums.tpl');

require_once("includes/foot.php");
?>
