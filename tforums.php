<?php
/**
 *   File functions:
 *   Clans forums
 *
 *   @name                 : tforums.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 28.09.2012
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
require_once("languages/".$lang."/tforums.php");

if ($player -> tribe == 0) 
{
    error (ERROR);
}

/**
 * Show unread posts
 */
if (isset($_GET['view']) && ($_GET['view'] == 'newposts'))
  {
    if (!isset($_SESSION['tforums']))
    {
        $_SESSION['tforums'] = $player->tforumtime;
        $db -> Execute("UPDATE `players` SET `tforum_time`=".$ctime." WHERE `id`=".$player -> id);
    }
    if ($intFunread2 == 0)
      {
	error("Nie ma nowych wpisów na forum. (<a href=tforums.php?view=topics>Wróć</a>)");
      }
    $pages = ceil($intFunread2 / 25);
    if (isset($_GET['page']))
     {
       checkvalue($_GET['page']);
       $page = $_GET['page'];
     }
    else
     {
       $page = $pages;
     }
    $objTopics = $db->SelectLimit("SELECT `id`, `topic` FROM `tribe_topics` WHERE `tribe`=".$player->tribe." AND `w_time`>".$intForums2, 25, 25 * ($page - 1)) or die($db->ErrorMsg());
    $arrTitles = array();
    $arrIds = array();
    $arrCats = array();
    while (!$objTopics->EOF)
      {
	$arrTitles[] = $objTopics->fields['topic'];
	$arrIds[] = $objTopics->fields['id'];
	$objTopics->MoveNext();
      }
    $objTopics->Close();
    $smarty->assign(array("Titles" => $arrTitles,
			  "Tid" => $arrIds,
			  "Tpages" => $pages,
			  "Tpage" => $page,
			  "Fpage" => "Idź do strony:",));
  }

/**
* Delete topic
*/
if (isset($_GET['kasuj1'])) 
{
    checkvalue($_GET['kasuj1']);
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $perm = $db -> Execute("SELECT forum FROM tribe_perm WHERE tribe=".$klan -> fields['id']." AND player=".$player -> id);
    if ($player -> id != $klan -> fields['owner'] && !$perm -> fields['forum']) 
      {
	message('error', NO_PERM);
      }
    else
      {
	$test = $db -> Execute("SELECT id FROM tribe_topics WHERE id=".$_GET['kasuj1']." AND tribe=".$player -> tribe);
	if (!$test -> fields['id']) 
	  {
	    message('error', ERROR);
	  } 
        else 
	  {
	    $db -> Execute("DELETE FROM tribe_replies WHERE topic_id=".$_GET['kasuj1']);
	    $db -> Execute("DELETE FROM tribe_topics WHERE id=".$_GET['kasuj1']);
	    message('success', TOPIC_DEL);
	    $_GET['view'] = 'topics';
	    $_GET['kasuj1'] = '';
	  }
	$test->Close();
      }
    $perm -> Close();
    $klan -> Close();
}

/**
 * Delete selected topics
 */
if (isset($_GET['action']) && $_GET['action'] == 'deltopics')
  {
    $klan = $db -> Execute("SELECT `owner` FROM `tribes` WHERE `id`=".$player->tribe);
    $perm = $db -> Execute("SELECT `forum` FROM `tribe_perm` WHERE `tribe`=".$player->tribe." AND `player`=".$player->id);
    if ($player->id != $klan->fields['owner'] && !$perm->fields['forum']) 
      {
        message('error', "Nie posiadasz odpowiednich uprawnień.");
      }
    else
      {
	$objTopics = $db->Execute("SELECT `id` FROM `tribe_topics` WHERE `tribe`=".$player->tribe);
	while (!$objTopics->EOF)
	  {
	    if (isset($_POST[$objTopics->fields['id']]))
	      {
		$db -> Execute("DELETE FROM `tribe_replies` WHERE `topic_id`=".$objTopics->fields['id']);
		$db -> Execute("DELETE FROM `tribe_topics` WHERE `id`=".$objTopics->fields['id']);
	      }
	    $objTopics->MoveNext();
	  }
	$objTopics->Close();
	message('success', "Wybrane tematy zostały skasowane.");
	$_GET['action'] = '';
	$_GET['view'] = 'topics';
      }
    $perm->Close();
    $klan->Close();
  }

/**
* Add topic
*/
if (isset ($_GET['action']) && $_GET['action'] == 'addtopic') 
  {
    $blnValid = TRUE;
    if (isset($_SESSION['posttime']))
    {
      if ($ctime - $_SESSION['posttime'] < 10)
	{
	  message('error', 'Zapomnij o tym.');
	  $blnValid = FALSE;
	}
    }
    if (empty ($_POST['title2']) || empty ($_POST['body'])) 
      {
        message('error', EMPTY_FIELDS);
	$_GET['view'] = 'topics';
	$blnValid = FALSE;
      }
    if ($blnValid)
      {
	$strSticky = 'N';
	if (isset($_POST['sticky']))
	  {	    
	    $klan = $db -> Execute("SELECT `id`, `owner` FROM `tribes` WHERE `id`=".$player -> tribe);
	    $perm = $db -> Execute("SELECT `forum` FROM `tribe_perm` WHERE `tribe`=".$klan -> fields['id']." AND `player`=".$player -> id);
	    if ($player -> id == $klan -> fields['owner'] || $perm -> fields['forum']) 
	      {
		$strSticky = 'Y';
	      }
	    $perm -> Close();
	    $klan -> Close();
	  }
	$_POST['title2'] = strip_tags($_POST['title2']);
	require_once('includes/bbcode.php');
	$_POST['body'] = bbcodetohtml($_POST['body']);
	$strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
	$_POST['title2'] = "<b>".$data." ".$time."</b> ".$_POST['title2'];
	$strTitle = $db -> qstr($_POST['title2'], get_magic_quotes_gpc());
	$strAuthor = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];
	$db -> Execute("INSERT INTO `tribe_topics` (`topic`, `body`, `starter`, `tribe`, `w_time`, `sticky`, `pid`) VALUES(".$strTitle.", ".$strBody.", '".$strAuthor."', '".$player -> tribe."', ".$ctime.", '".$strSticky."', ".$player -> id.")") or $db -> ErrorMsg();
	$objNewTopic = $db->Execute("SELECT MAX(`id`) FROM `tribe_topics` WHERE `tribe`=".$player->tribe);
	$_GET['topic'] = $objNewTopic->fields['MAX(`id`)'];
	$objNewTopic->Close();
	message('success', TOPIC_ADD);
	$_SESSION['posttime'] = $ctime;
      }
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
	$_SESSION['tforums'] = $player->tforumtime;
        $db -> Execute("UPDATE `players` SET `tforum_time`=".$ctime." WHERE `id`=".$player -> id);
    }
    
    /**
     * Show sticky threads
     */
    $topic = $db -> Execute("SELECT * FROM `tribe_topics` WHERE `tribe`=".$player -> tribe." AND `sticky`='Y' ORDER BY `id` ASC");
    $arrid = array();
    $arrrep = array();
    $arrtopic = array();
    $arrstarter = array();
    $arrNewtopic = array();
    $arrStarterid = array();
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
        $query = $db -> Execute("SELECT count(`id`) FROM `tribe_replies` WHERE `topic_id`=".$topic -> fields['id']);
        $arrrep[$i] = $query->fields['count(`id`)'];
        $query -> Close();
        $arrtopic[$i] = "<b>".$topic -> fields['topic']."</b>";
        $arrstarter[$i] = $topic -> fields['starter'];
	$arrStarterid[$i] = $topic->fields['pid'];
        $topic -> MoveNext();
        $i = $i + 1;
    }
    $topic -> Close();

    //Count amount of pages
    $objAmount = $db->Execute("SELECT count(`id`) FROM `tribe_topics` WHERE `tribe`=".$player->tribe." AND `sticky`='N'");
    $intPages = ceil($objAmount->fields['count(`id`)'] / 25);
    $objAmount->Close();
    if (isset($_GET['page']))
      {
	checkvalue($_GET['page']);
	$intPage = $_GET['page'];
      }
    else
      {
	$intPage = 1;
      }
    
    $topic = $db->SelectLimit("SELECT * FROM `tribe_topics` WHERE `tribe`=".$player->tribe." AND `sticky`='N' ORDER BY `w_time` DESC", 25, 25 * ($intPage - 1));
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
        $query = $db -> Execute("SELECT count(`id`) FROM `tribe_replies` WHERE `topic_id`=".$topic -> fields['id']);
        $arrrep[$i] = $query->fields['count(`id`)'];
        $query -> Close();
        $arrtopic[$i] = $topic -> fields['topic'];
        $arrstarter[$i] = $topic -> fields['starter'];
	$arrStarterid[$i] = $topic->fields['pid'];
        $topic -> MoveNext();
        $i = $i + 1;
    }
    $topic -> Close();
    $smarty -> assign(array("Topicid" => $arrid, 
			    "Topic" => $arrtopic, 
			    "Replies" => $arrrep, 
			    "Starter" => $arrstarter,
			    "Starterid" => $arrStarterid,
			    "Fpage" => "Idź do strony:",
			    "Tpage" => $intPage,
			    "Tpages" => $intPages,
			    "Ttopic" => T_TOPIC,
			    "Tauthor" => T_AUTHOR,
			    "Treplies" => T_REPLIES,
			    "Addtopic" => ADD_TOPIC,
			    "Ttext" => T_TEXT,
			    "Asearch" => A_SEARCH,
			    "Tword" => T_WORD,
			    "Abold" => "Pogrubienie",
			    "Aitalic" => "Kursywa",
			    "Aunderline" => "Podkreślenie",
			    "Aemote" => "Emocje/Czynność",
			    "Ocolors" => array("red" => "czerwony",
					       "green" => "zielony",
					       "white" => "biały",
					       "yellow" => "żółty",
					       "blue" => "niebieski",
					       "aqua" => "cyjan",
					       "fuchsia" => "fuksja",
					       "grey" => "szary",
					       "lime" => "limonka",
					       "maroon" => "wiśniowy",
					       "navy" => "granatowy",
					       "olive" => "oliwkowy",
					       "purple" => "purpurowy",
					       "silver" => "srebrny",
					       "teal" => "morski"),
			    "Acolor" => "Kolor",
			    "Acenter" => "Wycentrowanie",
			    "Aquote" => "Cytat",
			    "Adelete" => "Skasuj wybrane tematy",
			    "Thelp" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i>[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>",
			    "Newtopic" => $arrNewtopic));
}

/**
* Add reply
*/
if (isset($_GET['reply'])) 
{
    checkvalue($_GET['reply']);
    $blnValid = TRUE;
    if (isset($_SESSION['posttime']))
    {
      if ($ctime - $_SESSION['posttime'] < 10)
	{
	  message('error', 'Zapomnij o tym.');
	  $blnValid = FALSE;
	}
    }
    $test = $db -> Execute("SELECT `id` FROM `tribe_topics` WHERE `id`=".$_GET['reply']." AND `tribe`=".$player -> tribe);
    if (!$test -> fields['id']) 
      {
	message('error', NO_TOPIC);
	$blnValid = FALSE;
      }
    if (empty ($_POST['rep'])) 
      {
	message('error', EMPTY_FIELDS);
	$blnValid = FALSE;
      }
    if ($blnValid)
      {
	require_once('includes/bbcode.php');
	$_POST['rep'] = bbcodetohtml($_POST['rep']);
	$_POST['rep'] = "<b>".$data." ".$time."</b><br />".$_POST['rep'];
	$strRep = $db -> qstr($_POST['rep'], get_magic_quotes_gpc());
	$strAuthor = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];
	$db -> Execute("INSERT INTO `tribe_replies` (`starter`, `topic_id`, `body`, `pid`) VALUES('".$strAuthor."', ".$_GET['reply'].", ".$strRep." , ".$player -> id.")") or error("Could not add reply.");
	$db->Execute("UPDATE `tribe_topics` SET `w_time`=".$ctime." WHERE `id`=".$_GET['reply']);
	message('success', REPLY_ADD);
	$_SESSION['posttime'] = $ctime;
      }
    $_GET['topic'] = $_GET['reply'];
    $_GET['reply'] = '';
    $test -> Close();
}

/**
 * Sticky/Unsticky topics
 */
if (isset($_GET['sticky']))
{
    checkvalue($_GET['sticky']);
    if ($_GET['action'] != 'Y' && $_GET['action'] != 'N')
    {
        error(ERROR);
    }
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $perm = $db -> Execute("SELECT forum FROM tribe_perm WHERE tribe=".$klan -> fields['id']." AND player=".$player -> id);
    if ($player -> id != $klan -> fields['owner'] && !$perm -> fields['forum']) 
      {
	message('error', NO_PERM);
      }
    else
      {
	$test = $db -> Execute("SELECT id FROM tribe_topics WHERE id=".$_GET['sticky']." AND tribe=".$player -> tribe);
	if (!$test -> fields['id']) 
	  {
	    message('error', 'Nie ma takiego tematu.');
	  }
	else
	  {
	    $db -> Execute("UPDATE tribe_topics SET sticky='".$_GET['action']."' WHERE id=".$_GET['sticky']);
	    if ($_GET['action'] == 'Y')
	      {
		$strInfo = YOU_STICKY;
	      }
	    else
	      {
		$strInfo = YOU_UNSTICKY;
	      }
	    message('success', $strInfo);
	    $_GET['topic'] = $_GET['sticky'];
	  }
	$test -> Close();
      }
    $perm -> Close();
    $klan -> Close();
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
    checkvalue($_GET['kasuj']);
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $perm = $db -> Execute("SELECT forum FROM tribe_perm WHERE tribe=".$klan -> fields['id']." AND player=".$player -> id);
    if ($player -> id != $klan -> fields['owner'] && !$perm -> fields['forum']) 
      {
	message('error', NO_PERM);
      }
    else
      {
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
		message('success', POST_DEL);
		$_GET['topic'] = $test->fields['topic_id'];
	      }
	    $test2->Close();
	  }
	$test->Close();
      }
    $perm -> Close();
    $klan -> Close();
}

/**
* View topic
*/
if (isset($_GET['topic'])) 
{
    checkvalue($_GET['topic']);
    $klan = $db -> Execute("SELECT id, owner FROM tribes WHERE id=".$player -> tribe);
    $topicinfo = $db -> Execute("SELECT * FROM tribe_topics WHERE id=".$_GET['topic']." AND tribe=".$player -> tribe);
    $perm = $db -> Execute("SELECT forum FROM tribe_perm WHERE tribe=".$klan -> fields['id']." AND player=".$player -> id);
    if (!$topicinfo -> fields['id']) 
    {
        error (NO_TOPIC);
    }
    $strReplytext = R_TEXT;
    if (isset($_GET['quote']))
    {
	checkvalue($_GET['quote']);
        $objTest = $db -> Execute("SELECT `id`, `body` FROM `tribe_replies` WHERE `id`=".$_GET['quote']." AND `topic_id`=".$topicinfo -> fields['id']);
        if (!$objTest -> fields['id'])
        {
            error(ERROR);
        }
	else
	  {
	    $strText = preg_replace("/[0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]/", "", $objTest->fields['body']);
            $strText = str_replace("<b></b><br />", "", $strText);
	    require_once('includes/bbcode.php');
	    $strText = htmltobbcode($strText);
            $strReplytext = "[quote]".$strText."[/quote]";
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
    $smarty -> assign ("Topictext", $text);
    $objAmount = $db->Execute("SELECT count(`id`) FROM `tribe_replies` WHERE `topic_id`=".$topicinfo -> fields['id']);
    $intPages = ceil($objAmount->fields['count(`id`)'] / 25);
    $objAmount->close();
    if (isset($_GET['page']))
     {
       checkvalue($_GET['page']);
       $intPage = $_GET['page'];
     }
   else
     {
       $intPage = $intPages;
     }
    $reply = $db->SelectLimit("SELECT * FROM `tribe_replies` WHERE `topic_id`=".$topicinfo -> fields['id']." ORDER BY `id` ASC", 25, 25 * ($intPage - 1));
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
			    "Fpage" => "Idź do strony:",
			    "Tpages" => $intPages,
			    "Tpage" => $intPage,
			    "Abold" => "Pogrubienie",
			    "Aitalic" => "Kursywa",
			    "Aunderline" => "Podkreślenie",
			    "Aemote" => "Emocje/Czynność",
			    "Ocolors" => array("red" => "czerwony",
					       "green" => "zielony",
					       "white" => "biały",
					       "yellow" => "żółty",
					       "blue" => "niebieski",
					       "aqua" => "cyjan",
					       "fuchsia" => "fuksja",
					       "grey" => "szary",
					       "lime" => "limonka",
					       "maroon" => "wiśniowy",
					       "navy" => "granatowy",
					       "olive" => "oliwkowy",
					       "purple" => "purpurowy",
					       "silver" => "srebrny",
					       "teal" => "morski"),
			    "Acolor" => "Kolor",
			    "Acenter" => "Wycentrowanie",
			    "Aquote" => "Cytat",
			    "Thelp" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i>[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>",
                            "Aback" => A_BACK));
    $topicinfo -> Close();
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
    $strSearch = htmlspecialchars($_POST['search'], ENT_QUOTES);
    
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
