<?php
/**
 *   File functions:
 *   Library with players texts
 *
 *   @name                 : library.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 08.08.2012
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

$title = "Biblioteka";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/library.php");

$strQuery = "lang='".$lang."'";

/**
* Main menu
*/
if (!isset($_GET['step']))
{
    $objQuery = $db -> Execute("SELECT count(`id`) FROM library WHERE added='N' AND ".$strQuery);
    $intTextnot = $objQuery -> fields['count(`id`)'];
    $objQuery = $db -> Execute("SELECT count(`id`) FROM library WHERE added='Y' AND ".$strQuery);
    $intTextin = $objQuery -> fields['count(`id`)'];
    $objQuery = $db -> Execute("SELECT count(`id`) FROM library WHERE type='tale' AND added='Y' AND ".$strQuery);
    $intTales = $objQuery -> fields['count(`id`)'];
    $objQuery = $db -> Execute("SELECT count(`id`) FROM library WHERE type='poetry' AND added='Y' AND ".$strQuery);
    $intPoetry = $objQuery -> fields['count(`id`)'];
    $objQuery -> Close();
    
    $smarty -> assign(array("Atextnot" => $intTextnot,
        "Atextin" => $intTextin,
        "Amounttales" => $intTales,
        "Amountpoetry" => $intPoetry,
        "Welcome" => WELCOME,
        "Welcome2" => WELCOME2,
        "Welcome3" => WELCOME3,
        "Welcome4" => WELCOME4,
        "Welcome5" => WELCOME5,
        "Welcome6" => WELCOME6,
        "Welcome7" => WELCOME7,
        "Welcome8" => WELCOME8,
        "Atales" => A_TALES,
        "Apoetry" => A_POETRY,
        "Tinfo1" => T_INFO1,
        "Tinfo2" => T_INFO2,
        "Aaddtext" => A_ADD_TEXT,
        "Aadmin" => A_ADMIN,
        "Arules" => A_RULES));
}
    else
{
    $smarty -> assign("Aback", A_BACK);
}

/**
* Add text to library (simple user)
*/
if (isset($_GET['step']) && $_GET['step'] == 'add')
{

    $arrType = array(T_TYPE1, T_TYPE2);
    $smarty -> assign(array("Ttype" => $arrType,
			    "Ttype2" => T_TYPE,
			    "Ttitle2" => T_TITLE,
			    "Tbody2" => T_BODY,
			    "Aadd" => A_ADD,
			    "Addinfo" => ADD_INFO,
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
			    "Aquote" => "Cytat"));

    if (isset($_GET['step2']))
    {
        if (!in_array($_POST['type'], $arrType))
        {
            error(ERROR);
        }
        if (empty($_POST['ttitle']) || empty($_POST['body']))
        {
            error(EMPTY_FIELDS);
        }
        if ($_POST['type'] == T_TYPE1)
        {
            $strType = 'tale';
        }
        if ($_POST['type'] == T_TYPE2)
        {
            $strType = 'poetry';
        }
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strAuthor = $player -> user." ID: ".$player -> id;
        $strTitle = $db -> qstr($_POST['ttitle'], get_magic_quotes_gpc());
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $db -> Execute("INSERT INTO library (title, body, type, lang, author) VALUES(".$strTitle.", ".$strBody.", '".$strType."', '".$lang."', '".$strAuthor."')");
        error(YOU_ADD);
    }
}

/**
* Add text to library (librarian)
*/
if (isset($_GET['step']) && $_GET['step'] == 'addtext')
{
    if ($player -> rank != 'Bibliotekarz' && $player -> rank != 'Admin')
    {
        error(NO_PERM);
    }
    $objText = $db -> Execute("SELECT `id`, `title`, `author` FROM `library` WHERE `added`='N' AND ".$strQuery);
    $arrId = array();
    $arrTitle = array();
    $arrAuthor = array();
    while (!$objText -> EOF)
    {
        $arrId[] = $objText -> fields['id'];
        $arrTitle[] = $objText -> fields['title'];
        $arrAuthor[] = $objText -> fields['author'];
        $objText -> MoveNext();
    }
    $objText -> Close();
    $smarty -> assign(array("Ttitle" => $arrTitle,
        "Tid" => $arrId,
        "Tauthor" => $arrAuthor,
        "Admininfo" => ADMIN_INFO,
        "Admininfo2" => ADMIN_INFO2,
        "Admininfo3" => ADMIN_INFO3,
        "Admininfo4" => ADMIN_INFO4,
        "Admininfo5" => ADMIN_INFO5,
        "Tauthor2" => T_AUTHOR,
        "Amodify" => A_MODIFY,
        "Aadd" => A_ADD,
        "Adelete" => A_DELETE));
    /**
    * Modify text
    */
    if (isset($_GET['action']) && $_GET['action'] == 'modify')
      {
	require_once('includes/bbcode.php');
	checkvalue($_GET['text']);
        $objText = $db -> Execute("SELECT id, title, body, type FROM library WHERE id=".$_GET['text']);
        $smarty -> assign(array("Ttitle" => $objText -> fields['title'],
				"Tbody" => htmltobbcode($objText -> fields['body']),
				"Ttype" => $objText -> fields['type'],
				"Tid" => $objText -> fields['id'],
				"Ttitle2" => T_TITLE,
				"Tbody2" => T_BODY,
				"Ttype2" => T_TYPE,
				"Achange" => A_CHANGE,
				"Ttypet" => T_TYPE1,
				"Ttypep" => T_TYPE2,
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
				"Aquote" => "Cytat",));
        $objText -> Close();
        if (isset($_POST['tid']))
        {
	    checkvalue($_POST['tid']);
            if (empty($_POST['ttitle']) || empty($_POST['body']))
            {
                error(EMPTY_FIELDS);
            }
            if ($_POST['type'] == T_TYPE1)
            {
                $strType = 'tale';
            }
            if ($_POST['type'] == T_TYPE2)
            {
                $strType = 'poetry';
            }
            $_POST['body'] = bbcodetohtml($_POST['body']);
            $strTitle = $db -> qstr($_POST['ttitle'], get_magic_quotes_gpc());
            $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
            $db -> Execute("UPDATE `library` SET `title`=".$strTitle.", `body`=".$strBody.", `type`='".$strType."' WHERE `id`=".$_POST['tid']);
            error (MODIFIED);
        }
    }
    /**
    * Add or delete texts in library
    */
    if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'delete'))
    {
	checkvalue($_GET['text']);
        $objText = $db -> Execute("SELECT `id`, `author`, `title` FROM `library` WHERE `id`=".$_GET['text']);
        if (!$objText -> fields['id'])
	  {
            error(NO_TEXT);
	  }
	$strDate = $db -> DBDate($newdate);
	$arrAuthor = explode(": ", $objText->fields['author']);
        if ($_GET['action'] == 'add')
	  {
            $db -> Execute("UPDATE `library` SET `added`='Y' WHERE `id`=".$_GET['text']);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$arrAuthor[1].", 'Twój tekst \"".$objText->fields['title']."\" został dodany do biblioteki.', ".$strDate.", 'L')") or die($db->ErrorMsg());
            error(ADDED);
	  }
	else
	  {
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$arrAuthor[1].", 'Twój tekst \"".$objText->fields['title']."\" został odrzucony z biblioteki.', ".$strDate.", 'L')") or die($db->ErrorMsg());
            $db -> Execute("DELETE FROM `library` WHERE `id`=".$_GET['text']);
            error(DELETED);
	  }
    }
}

/**
* Display texts in library
*/
if (isset($_GET['step']) && ($_GET['step'] == 'tales' || $_GET['step'] == 'poetry'))
{
    if ($_GET['step'] == 'tales')
    {
        $strType = 'tale';
        $strInfo = T_INFO1;
    }
    if ($_GET['step'] == 'poetry')
    {
        $strType = 'poetry';
        $strInfo = T_INFO2;
    }
    $objQuery = $db -> Execute("SELECT id FROM library WHERE added='Y' AND type='".$strType."' AND ".$strQuery);
    $intAmount = $objQuery -> RecordCount();
    $objQuery -> Close();
    $smarty -> assign(array("Tamount" => $intAmount,
        "Listinfo" => LIST_INFO,
        "Listinfo2" => LIST_INFO2,
        "Ttype" => $strInfo,
        "Noitems" => NO_ITEMS,
        "Tauthor2" => T_AUTHOR,
        "Ttitle2" => T_TITLE,
        "Tbody2" => T_BODY,
        "Inlib" => IN_LIB,
        "Sortinfo" => SORT_INFO,
        "Asort" => A_SORT,
        "Oauthor" => O_AUTHOR,
        "Otitle" => O_TITLE,
        "Odate" => O_DATE));
    /**
    * Display sorted texts
    */
    if (isset($_POST['sort']) && $_POST['sort'] != 'author')
    {
        $arrSort = array('title', 'id');
        if (!in_array($_POST['sort'], $arrSort))
        {
            error(ERROR);
        }
        $arrTitle = array();
        $arrAuthor = array();
        $arrId = array();
        $arrPid = array();
        $arrComments = array();
        $i = 0;
        $objList = $db -> Execute("SELECT `id`, `title`, `author` FROM `library` WHERE `added`='Y' AND `type`='".$strType."' AND ".$strQuery." ORDER BY ".$_POST['sort']." DESC") or error($db -> ErrorMsg());
        while (!$objList -> EOF)
        {
            $arrTitle[$i] = $objList -> fields['title'];
            $arrId[$i] = $objList -> fields['id'];
            $arrAuthor[$i] = $objList -> fields['author'];
            $arrAuthorId = explode(": ", $objList -> fields['author']);
            $arrPid[$i] = $arrAuthorId[1];
            $objQuery = $db -> Execute("SELECT count(`id`) FROM `lib_comments` WHERE `textid`=".$objList -> fields['id']);
            $arrComments[$i] = $objQuery -> fields['count(`id`)'];
            $objQuery -> Close();
            $i = $i + 1;
            $objList -> MoveNext();
        }
        $objList -> Close();
        $smarty -> assign(array("Tid" => $arrId,
            "Ttitle" => $arrTitle,
            "Tauthor" => $arrAuthor,
            "Tauthorid" => $arrPid,
            "Comments" => $arrComments,
            "Tcomments" => T_COMMENTS));
    }
    /**
    * Display authors list
    */
    if (!isset($_GET['author']) && (!isset($_POST['sort']) || (isset($_POST['sort']) && $_POST['sort'] == 'author')))
    {
        $objAuthor = $db -> Execute("SELECT `author` FROM `library` WHERE `added`='Y' AND `type`='".$strType."' AND ".$strQuery." GROUP BY `author`");
        $arrAuthor = array();
        $arrTexts = array();
        $arrId = array();
        $i = 0;
        while (!$objAuthor -> EOF)
        {
            $intTest = 0;
            $arrAuthorId = explode(": ", $objAuthor -> fields['author']);
            foreach ($arrId as $intId)
            {
                if ($arrAuthorId[1] == $intId)
                {
                    $intTest = 1;
                    break;
                }
            }
            if (!$intTest)
            {
                $arrAuthor[$i] = $objAuthor -> fields['author'];
                $objQuery2 = $db -> Execute("SELECT count(`id`) FROM `library` WHERE `author` LIKE '%ID: ".$arrAuthorId[1]."' AND `added`='Y' AND `type`='".$strType."' AND ".$strQuery) or error($db -> ErrorMsg());
                $arrTexts[$i] = $objQuery2 -> fields['count(`id`)'];
                $objQuery2 -> Close();
                $arrId[$i] = $arrAuthorId[1];
                $i = $i + 1;
            }
            $objAuthor -> MoveNext();
        }
        $objAuthor -> Close();
        $smarty -> assign(array("Tauthor" => $arrAuthor,
            "Ttexts" => $arrTexts,
            "Tauthorid" => $arrId));
    }
    /**
    * Display texts selected author
    */
    if (isset($_GET['author']))
    {
	checkvalue($_GET['author']);
        $objAuthor = $db -> Execute("SELECT `author` FROM `library` WHERE `author` LIKE '%ID: ".$_GET['author']."' GROUP BY `author`") or ($db -> ErrorMsg());
        $arrTitle = array();
        $arrAuthor = array();
        $arrId = array();
        $arrPid = array();
        $arrComments = array();
        $i = 0;
        while (!$objAuthor -> EOF)
        {
            $objList = $db -> Execute("SELECT `id`, `title`, `author` FROM `library` WHERE `added`='Y' AND `type`='".$strType."' AND `author`='".$objAuthor -> fields['author']."' AND ".$strQuery." ORDER BY `id` DESC") or error($db -> ErrorMsg());
            while (!$objList -> EOF)
            {
                $arrTitle[$i] = $objList -> fields['title'];
                $arrId[$i] = $objList -> fields['id'];
                $arrAuthor[$i] = $objList -> fields['author'];
                $arrAuthorId = explode(": ", $objList -> fields['author']);
                $arrPid[$i] = $arrAuthorId[1];
                $objQuery = $db -> Execute("SELECT count(`id`) FROM `lib_comments` WHERE `textid`=".$objList -> fields['id']);
                $arrComments[$i] = $objQuery -> fields['count(`id`)'];
                $objQuery -> Close();
                $i = $i + 1;
                $objList -> MoveNext();
            }
            $objAuthor -> MoveNext();
        }
        $objAuthor -> Close();
        $objList -> Close();
        $smarty -> assign(array("Tid" => $arrId,
            "Ttitle" => $arrTitle,
            "Tauthor" => $arrAuthor,
            "Tauthorid" => $arrPid,
            "Comments" => $arrComments,
            "Tcomments" => T_COMMENTS));
    }
    /**
    * Display selected text
    */
    if (isset($_GET['text']))
    {
	checkvalue($_GET['text']);
        $objText = $db -> Execute("SELECT `id`, `title`, `author`, `body` FROM `library` WHERE `id`=".$_GET['text']);
        if (!$objText -> fields['id'])
        {
            error(NO_TEXT);
        }
        $arrAuthorId = explode(": ", $objText -> fields['author']);
        $smarty -> assign(array("Ttitle" => $objText -> fields['title'],
            "Tauthor" => $objText -> fields['author'],
            "Tbody" => $objText -> fields['body'],
            "Tauthorid" => $arrAuthorId[1],
            "Tid" => $objText -> fields['id'],
            "Tcomments" => T_COMMENTS2,
            "Amodify" => A_MODIFY));
    }
}

/**
* Comments to text
*/
if (isset($_GET['step']) && $_GET['step'] == 'comments')
{
    $smarty -> assign("Amount", '');
    
    require_once('includes/comments.php');

    /**
    * Add comment
    */
    if (isset($_POST['body']))
      {
        addcomments($_GET['text'], 'lib_comments', 'textid');
      }

    /**
    * Delete comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'delete')
    {
        deletecomments('lib_comments');
    }

    /**
    * Display comments
    */
    if (!isset($_GET['page']))
      {
	$intPage = -1;
      }
    else
      {
	$intPage = $_GET['page'];
      }
    $intAmount = displaycomments($_GET['text'], 'library', 'lib_comments', 'textid');
    $smarty -> assign(array("Tauthor" => $arrAuthor,
			    "Taid" => $arrAuthorid,
			    "Tbody" => $arrBody,
			    "Amount" => $intAmount,
			    "Cid" => $arrId,
			    "Tdate" => $arrDate,
			    "Nocomments" => NO_COMMENTS,
			    "Addcomment" => ADD_COMMENT,
			    "Adelete" => A_DELETE,
			    "Aadd" => A_ADD,
			    "Tpages" => $intPages,
			    "Tpage" => $intPage,
			    "Fpage" => "Idź do strony:",
			    "Faction" => "library.php?step=comments&amp;text=".$_GET['text'],
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
			    "Writed" => WRITED));
}

/**
* Display library rules
*/
if (isset($_GET['step']) && $_GET['step'] == 'rules')
{
    $smarty -> assign("Rules", RULES);
}

/**
* Initialization of variable
*/
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}
if (!isset($_GET['text']))
{
    $_GET['text'] = '';
}
if (!isset($_GET['author']))
{
    $_GET['author'] = '';
}
if (!isset($_POST['sort']))
{
    $_POST['sort'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Rank" => $player -> rank,
    "Step" => $_GET['step'],
    "Action" => $_GET['action'],
    "Text" => $_GET['text'],
    "Author" => $_GET['author'],
    "Sort" => $_POST['sort']));
$smarty -> display ('library.tpl');

require_once("includes/foot.php");
?>
