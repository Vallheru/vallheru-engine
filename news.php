<?php
/**
 *   File functions:
 *   Show game news
 *
 *   @name                 : news.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
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

$title = "Miejskie Plotki";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/news.php");

/**
* Display one news
*/
if (!isset ($_GET['view'])) 
{
    $upd = $db -> SelectLimit("SELECT * FROM `news` WHERE `lang`='".$player -> lang."' AND `added`='Y' AND `show`='Y' ORDER BY `id` DESC", 1);
    if (isset($upd -> fields['id']))
    {
        $objQuery = $db -> Execute("SELECT count(`id`) FROM `news_comments` WHERE `newsid`=".$upd -> fields['id']);
        $intComments = $objQuery -> fields['count(`id`)'];
        $objQuery -> Close();
    }
        else
    {
        $intComments = 0;
    }
    $objQuery = $db -> Execute("SELECT count(`id`) FROM `news` WHERE `lang`='".$player -> lang."' AND `added`='N'");
    $intWaiting = $objQuery -> fields['count(`id`)'];
    $objQuery -> Close();
    $objAccepted = $db -> Execute("SELECT count(`id`) FROM `news` WHERE `lang`='".$player -> lang."' AND `show`='N'");
    $intAccepted = $objAccepted -> fields['count(`id`)'];
    $objAccepted -> Close();
    $smarty -> assign(array("Title1" => $upd -> fields['title'], 
        "Starter" => $upd -> fields['starter'], 
        "News" => $upd -> fields['news'],
        "Comments" => $intComments,
        "Newsid" => $upd -> fields['id'],
        "Aaddnews" => A_ADD_NEWS,
        "Twaiting" => T_WAITING,
        "Taccepted" => T_ACCEPTED,
        "Accepted" => $intAccepted,
        "Waiting" => $intWaiting));
} 

/**
* Display last 10 news
*/
if (isset($_GET['view']))
{
    $upd = $db -> SelectLimit("SELECT * FROM news WHERE `lang`='".$player -> lang."' AND `added`='Y' AND `show`='Y'  ORDER BY `id` DESC", 10);
    $arrtitle = array();
    $arrstarter = array();
    $arrnews = array();
    $arrId = array();
    $arrComments = array();
    $i = 0;
    while (!$upd -> EOF) 
    {
        $objQuery = $db -> Execute("SELECT count(`id`) FROM `news_comments` WHERE `newsid`=".$upd -> fields['id']);
        $arrComments[$i] = $objQuery -> fields['count(`id`)'];
        $objQuery -> Close();
        $arrtitle[$i] = $upd -> fields['title'];
        $arrstarter[$i] = $upd -> fields['starter'];
        $arrnews[$i] = $upd -> fields['news'];
        $arrId[$i] = $upd -> fields['id'];
        $upd -> MoveNext();
        $i = $i + 1;
    }
    $upd -> Close();
    $smarty -> assign(array("Title1" => $arrtitle, 
        "Starter" => $arrstarter, 
        "News" => $arrnews,
        "Newsid" => $arrId,
        "Comments" => $arrComments));
}

/**
* Comments to text
*/
if (isset($_GET['step']) && $_GET['step'] == 'comments')
{
    $smarty -> assign("Amount", '');
    
    require_once('includes/comments.php');
    /**
    * Display comments
    */
    if (!isset($_GET['action']))
    {
        if (!isset($_GET['page']))
	  {
	    $intPage = -1;
	  }
	else
	  {
	    $intPage = $_GET['page'];
	  }
        displaycomments($_GET['text'], 'news', 'news_comments', 'newsid');
        $smarty -> assign(array("Tauthor" => $arrAuthor,
            "Tbody" => $arrBody,
            "Amount" => $i,
            "Cid" => $arrId,
            "Tdate" => $arrDate,
            "Nocomments" => NO_COMMENTS,
            "Addcomment" => ADD_COMMENT,
            "Adelete" => A_DELETE,
            "Aadd" => A_ADD,
            "Tpages" => $intPages,
	    "Tpage" => $intPage,
	    "Fpage" => "Idź do strony:",
            "Writed" => WRITED));
    }

    /**
    * Add comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'add')
      {
	checkvalue($_POST['tid']);
        addcomments($_POST['tid'], 'news_comments', 'newsid');
      }

    /**
    * Delete comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'delete')
    {
        deletecomments('news_comments');
    }
}

/**
* Add news (simple user)
*/
if (isset($_GET['step']) && $_GET['step'] == 'add')
{
    $arrLanguage = scandir('languages/', 1);
    $arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));

    $smarty -> assign(array("Llang" => $arrLanguage,
        "Tlang" => T_LANG,
        "Ttitle2" => T_TITLE,
        "Tbody2" => T_BODY,
        "Aadd" => A_ADD,
        "Addinfo" => ADD_INFO));

    if (isset($_GET['step2']))
    {
        if (empty($_POST['ttitle']) || empty($_POST['body']))
        {
            error(EMPTY_FIELDS);
        }
	if (!in_array($_POST['lang'], $arrLanguage))
	  {
	    error(ERROR);
	  }
        $_POST['body'] = nl2br($_POST['body']);
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strAuthor = $player -> user." (".$player -> id.")";
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $strTitle = $db -> qstr($_POST['ttitle'], get_magic_quotes_gpc());
        $db -> Execute("INSERT INTO news (title, news, added, lang, starter) VALUES(".$strTitle.", ".$strBody.", 'N', '".$_POST['lang']."', '".$strAuthor."')");
        error(YOU_ADD);
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
}
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}
if (!isset($_GET['text']))
{
    $_GET['text'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign (array("View" => $_GET['view'],
    "Writeby" => WRITE_BY,
    "Last10" => LAST_10,
    "Step" => $_GET['step'],
    "Rank" => $player -> rank,
    "Text" => $_GET['text'],
    "Acomments" => A_COMMENTS,
    "Aback" => A_BACK));
$smarty -> display ('news.tpl');

require_once("includes/foot.php");
?>
