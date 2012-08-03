<?php
/**
 *   File functions:
 *   City newspaper
 *
 *   @name                 : newspaper.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 03.08.2012
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

$title = "Redakcja gazety";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/newspaper.php");

/**
* Assign variables to template
*/
$smarty -> assign(array("Message" => '', 
    "Aedit" => '', 
    "Apublic" => A_PUBLIC));

/**
* Main menu
*/
if (!isset($_GET['comments']) && !isset($_GET['step']) && !isset($_GET['read']))
{
    $smarty -> assign(array("Paperinfo" => PAPERINFO,
                            "Paperinfo2" => PAPERINFO2,
                            "Paperinfo3" => PAPERINFO3,
                            "Anewpaper" => A_NEW_PAPER,
                            "Aarchive" => A_ARCHIVE,
                            "Aredaction" => A_REDACTION,
                            "Aredmail" => A_RED_MAIL));
}

/**
* Read and edit newspaper
*/
if ((isset($_GET['step']) && $_GET['step'] == 'new') || (isset($_GET['read']) || (isset($_GET['step3']) && $_GET['step3'] == 'S')))
  {
    if (isset($_GET['read']))
      {
	checkvalue($_GET['read']);
      }
    if (isset($_GET['step3']))
    {
        if ($player -> rank != 'Admin' && $player -> rank != 'Redaktor')
        {
            error(NO_PERM);
        }
    }
    else
    {
        $_GET['step3'] = '';
    }
    $strNext = '';
    $strPrevious = '';
    $intPage = 0;
    $intPage2 = 0;
    $arrTypes = array('N', 'M', 'O', 'R', 'K', 'C', 'S', 'H', 'I', 'A', 'P');
    $arrSecnames = array(A_NEWS, A_NEWS2, A_COURT, A_ROYAL, A_KING, A_CHRONICLE." ".$gamename, A_SENSATIONS, A_HUMOR, A_INTER, A_NEWS3, A_POETRY);
    if (isset($_GET['page']) && $_GET['page'] == 'contents')
    {
        $arrId = array(array());
        $arrTitle = array(array());
        $arrAuthor = array(array());
	$arrComments = array(array());
        $j = 0;
        $blnPage = false;
        foreach ($arrTypes as $strType)
        {
            if (isset($_GET['step']) && $_GET['step'] == 'new')
            {
                $objPaperid = $db -> Execute("SELECT `paper_id` FROM `newspaper` WHERE `added`='Y' GROUP BY `paper_id` DESC");
                $objArticles = $db -> Execute("SELECT `title`, `author`, `id` FROM `newspaper` WHERE `paper_id`=".$objPaperid -> fields['paper_id']." AND `type`='".$strType."' AND `added`='Y'");
                $objPaperid -> Close();
            }
                elseif (isset($_GET['read']))
            {
                $objArticles = $db -> Execute("SELECT `title`, `author`, `id` FROM `newspaper` WHERE `paper_id`=".$_GET['read']." AND `type`='".$strType."' AND `added`='Y'");
            }
                elseif (isset($_GET['step3']))
            {
                $objArticles = $db -> Execute("SELECT `title`, `author`, `id` FROM `newspaper` WHERE `added`='N' AND `type`='".$strType."'");
            }
            $i = 0;
            if (empty($objArticles -> fields['id']))
            {
                $arrId[$j][0] = 0;
                $arrTitle[$j][0] = '';
                $arrAuthor[$j][0] = '';
		$arrComments[$j][0] = 0;
            }
	    elseif (!$blnPage)
            {
                $intPage = $objArticles -> fields['id'];
                $blnPage = true;
            }
	    if (!empty($objArticles->fields['id']))
	      {
		while (!$objArticles -> EOF)
		  {
		    $arrId[$j][$i] = $objArticles -> fields['id'];
		    $arrTitle[$j][$i] = $objArticles -> fields['title'];
		    $arrAuthor[$j][$i] = $objArticles -> fields['author'];
		    $objComments = $db->Execute("SELECT count(`id`) FROM `newspaper_comments` WHERE `textid`=",$objArticles->fields['id']);
		    $arrComments[$j][$i] = $objComments->fields['count(`id`)'];
		    $objComments->Close();
		    $i ++;
		    $objArticles -> MoveNext();
		  }
		$objArticles -> Close();
	      }
            $j ++;
        }
        $strNext = "<input type=\"submit\" name=\"next\" value=\"".A_NEXT."\" />";
        $smarty -> assign(array("Artidm" => $arrId,
                                "Arttitlem" => $arrTitle,
                                "Artauthorm" => $arrAuthor,
				"Artcomments" => $arrComments,
				"Tcomments" => "(komentarzy: "));
    }
    if (isset($_GET['step']) && $_GET['step'] == 'new')
    {
        $smarty -> assign("Newslink", "step=new");
    }
        elseif (isset($_GET['read']))
    {
        $smarty -> assign("Newslink", "read=".$_GET['read']);
    }
        elseif (isset($_GET['step3']))
    {
        $smarty -> assign(array("Aedit" => A_EDIT,
                                "Adelete" => A_DELETE,
                                "Newslink" => "step3=S"));
    }
    if (!isset($_GET['page']))
    {
        $_GET['page'] = '';
    }
    $smarty -> assign(array("Readinfo" => READINFO,
                            "Aauthor" => A_AUTHOR,
                            "Amain" => A_MAIN,
                            "Acontents" => A_CONTENTS,
                            "Aend" => A_END,
                            "Page" => $_GET['page'],
                            "Arttypes" => $arrTypes,
                            "Secnames" => $arrSecnames,
                            "Next" => $strNext,
                            "Previous" => $strPrevious,
                            "Pageid" => $intPage,
                            "Pageid2" => $intPage2));
}

/**
* Newspaper archive
*/
if (isset($_GET['step']) && $_GET['step'] == 'archive')
{
    $objPaperid = $db -> Execute("SELECT paper_id FROM newspaper WHERE added='Y' GROUP BY paper_id DESC");
    if (!$objPaperid -> fields['paper_id'])
    {
        error(EMPTY_ARCHIVE);
    }
    $objPaperid2 = $db -> Execute("SELECT paper_id FROM newspaper WHERE paper_id<".$objPaperid -> fields['paper_id']." AND paper_id!=0 GROUP BY paper_id");
    $objPaperid -> Close();
    $arrPaperid = array();
    $i = 0;
    while (!$objPaperid2 -> EOF)
    {
        $arrPaperid[$i] = $objPaperid2 -> fields['paper_id'];
        $i ++;
        $objPaperid2 -> MoveNext();
    }
    $objPaperid2 -> Close();
    $smarty -> assign(array("Paperid" => $arrPaperid,
                            "Anumber" => A_NUMBER,
                            "Archiveinfo" => ARCHIVEINFO));
}

/**
* Comments to text
*/
if (isset($_GET['comments']))
{
    $smarty -> assign("Amount", '');
    
    require_once('includes/comments.php');

    /**
    * Add comment
    */
    if (isset($_POST['body']))
    {
        addcomments($_GET['comments'], 'newspaper_comments', 'textid');
    }

    /**
    * Delete comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'delete')
    {
      deletecomments('newspaper_comments', array('Admin', 'Staff', 'Redaktor'));
    }

    /**
    * Display comments
    */
    if (!isset($_GET['page']) || empty($_GET['page']))
      {
	$intPage = -1;
      }
    else
      {
	$intPage = $_GET['page'];
      }
    $intAmount = displaycomments($_GET['comments'], 'newspaper', 'newspaper_comments', 'textid');
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
			    "Faction" => "newspaper.php?comments=".$_GET['comments'],
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
			    "Wrote" => WROTE));
}

/**
* Newspaper redaction
*/
if (isset($_GET['step']) && $_GET['step'] == 'redaction')
{
    if ($player -> rank != 'Admin' && $player -> rank != 'Redaktor')
    {
        error(NO_PERM);
    }
    $smarty -> assign(array("Redactioninfo" => REDACTIONINFO,
                            "Ashow" => A_SHOW,
                            "Aredaction" => A_REDACTION));

    /**
    * Edit article
    */
    if (isset($_GET['step3']) && ($_GET['step3'] == 'edit' || $_GET['step3'] == 'R'))
    {
        if (isset($_GET['edit']))
	  {
	    checkvalue($_GET['edit']);
	  }
        if ($_GET['step3'] == 'R')
        {
            $_GET['edit'] = '';
        }
        $arrTypes = array('N', 'M', 'O', 'R', 'K', 'C', 'S', 'H', 'I', 'A', 'P');
        $arrSecnames = array(A_NEWS, A_NEWS2, A_COURT, A_ROYAL, A_KING, A_CHRONICLE." ".$gamename, A_SENSATIONS, A_HUMOR, A_INTER, A_NEWS3, A_POETRY);
        $smarty -> assign(array("Ttitle" => T_TITLE,
                                "Tbody" => T_BODY,
                                "Mailtype" => MAIL_TYPE,
                                "Ashow" => A_SHOW2,
                                "Asend" => A_SEND,
                                "Showmail" => '',
                                "Mtitle" => '',
                                "Mbody" => '',
                                "Mtype" => '',
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
                                "Edit" => $_GET['edit'],
                                "Youedit" => YOU_EDIT,
                                "Arttypes" => $arrTypes,
                                "Sectionnames" => $arrSecnames));
        if ($_GET['step3'] == 'edit')
        {
	    require_once('includes/bbcode.php');
            $objArticle = $db -> Execute("SELECT `title`, `type`, `body` FROM `newspaper` WHERE `id`=".$_GET['edit']);
            $smarty -> assign(array("Mtitle" => htmltobbcode($objArticle -> fields['title']),
                                    "Mbody" => htmltobbcode($objArticle -> fields['body']),
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
                                    "Mtype" => $objArticle -> fields['type']));
            $objArticle -> Close();
        }
        if (isset($_POST['show']))
        {
            $arrType = array('M', 'N', 'O', 'R', 'K', 'C', 'S', 'H', 'I', 'A', 'P');
            if (!in_array($_POST['mail'], $arrType))
            {
                error(ERROR);
            }
            if (empty($_POST['mtitle']) || empty($_POST['mbody']))
            {
                error(EMPTY_FIELDS);
            }
            require_once('includes/bbcode.php');
            $strBody = bbcodetohtml($_POST['mbody']);
            $strTitle = bbcodetohtml($_POST['mtitle']);
            $strMail = T_TITLE." ".$strTitle."<br />".T_BODY." <br />".$strBody;
            $smarty -> assign(array("Showmail" => $strMail,
                                    "Mtitle" => $_POST['mtitle'],
                                    "Mbody" => $_POST['mbody'],
                                    "Mtype" => $_POST['mail']));
        }
        if (isset($_POST['sendmail']))
        {
            $arrType = array('M', 'N', 'O', 'R', 'K', 'C', 'S', 'H', 'I', 'A', 'P');
            if (!in_array($_POST['mail'], $arrType))
            {
                error(ERROR);
            }
            if (empty($_POST['mtitle']) || empty($_POST['mbody']))
            {
                error(EMPTY_FIELDS);
            }
            require_once('includes/bbcode.php');
            $_POST['mbody'] = bbcodetohtml($_POST['mbody']);
            $_POST['mtitle'] = bbcodetohtml($_POST['mtitle']);
            $strBody = $db -> qstr($_POST['mbody'], get_magic_quotes_gpc());
            $strTitle = $db -> qstr($_POST['mtitle'], get_magic_quotes_gpc());
            $strAuthor = $player -> user." ID: ".$player -> id;
            $_POST['mbody'] = $_POST['mbody']."<br /><br />".EDITED_BY.$strAuthor;
            if ($_GET['step3'] == 'edit')
            {
                $db -> Execute("UPDATE newspaper SET title=".$strTitle.", body=".$strBody.", type='".$_POST['mail']."' WHERE id=".$_GET['edit']);
            }
                else
            {
                $strAuthor = $player -> user." ID: ".$player -> id;
                $objPaperid = $db -> Execute("SELECT paper_id FROM newspaper WHERE added='Y' GROUP BY paper_id DESC");
                $intPaperid = $objPaperid -> fields['paper_id'] + 1;
                $objPaperid -> Close();
                $db -> Execute("INSERT INTO newspaper (paper_id, title, body, author, lang, added, type) VALUES(".$intPaperid.", ".$strTitle.", ".$strBody.", '".$strAuthor."', '".$lang."', 'N', '".$_POST['mail']."')");
            }
            $smarty -> assign("Message", "<br /><br />".MAIL_SEND);
        }
    }

    /**
    * Release new newspaper
    */
    if (isset($_GET['step3']) && $_GET['step3'] == 'release')
    {
        $db -> Execute("UPDATE newspaper SET added='Y' WHERE added='N'");
        $smarty -> assign("Message", "<br /><br />".NEWSPAPER_RELEASED);
    }

    /**
    * Delete selected article
    */
    if (isset($_GET['step3']) && $_GET['step3'] == 'delete')
    {
        $db -> Execute("DELETE FROM newspaper WHERE id=".$_GET['del']);
        $smarty -> assign("Message", "<br /><br />".ARTICLE_DELETED);
    }
}

/**
* Newspaper mail
*/
if (isset($_GET['step']) && $_GET['step'] == 'mail')
{
    $smarty -> assign(array("Anews" => A_NEWS,
                            "Anews2" => A_NEWS2,
                            "Anews3" => A_NEWS3,
                            "Acourt" => A_COURT,
                            "Aroyal" => A_ROYAL,
                            "Aking" => A_KING,
                            "Achronicle" => A_CHRONICLE,
                            "Asensations" => A_SENSATIONS,
                            "Ahumor" => A_HUMOR,
                            "Ainter" => A_INTER,
                            "Apoetry" => A_POETRY,
                            "Ttitle" => T_TITLE,
                            "Tbody" => T_BODY,
                            "Mailinfo" => MAILINFO,
                            "Mailtype" => MAIL_TYPE,
                            "Ashow" => A_SHOW,
                            "Asend" => A_SEND,
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
                            "Showmail" => '',
                            "Mtitle" => '',
                            "Mbody" => '',
                            "Mtype" => ''));
    if (isset($_GET['step3']) && $_GET['step3'] == 'add')
    {
        $arrType = array('M', 'N', 'O', 'R', 'K', 'C', 'S', 'H', 'I', 'A', 'P');
        if (!in_array($_POST['mail'], $arrType))
        {
            error(ERROR);
        }
        if (empty($_POST['mtitle']) || empty($_POST['mbody']))
        {
            error(EMPTY_FIELDS);
        }
        require_once('includes/bbcode.php');
        $strBody = bbcodetohtml($_POST['mbody']);
        $strTitle = bbcodetohtml($_POST['mtitle']);
        if (isset($_POST['show']))
        {
            $strMail = T_TITLE." ".$strTitle."<br />".T_BODY." <br />".$strBody;
            $smarty -> assign(array("Showmail" => $strMail,
                                    "Mtitle" => $_POST['mtitle'],
                                    "Mbody" => $_POST['mbody'],
                                    "Mtype" => $_POST['mail']));
        }
        if (isset($_POST['sendmail']))
        {
            $strAuthor = $player -> user." ID: ".$player -> id;
            $objPaperid = $db -> Execute("SELECT `paper_id` FROM `newspaper` WHERE `added`='Y' GROUP BY `paper_id` DESC");
            $intPaperid = $objPaperid -> fields['paper_id'] + 1;
            $objPaperid -> Close();
            $strBody = $db -> qstr($strBody, get_magic_quotes_gpc());
            $strTitle = $db -> qstr($strTitle, get_magic_quotes_gpc());
            $db -> Execute("INSERT INTO `newspaper` (`paper_id`, `title`, `body`, `author`, `lang`, `added`, `type`) VALUES(".$intPaperid.", ".$strTitle.", ".$strBody.", '".$strAuthor."', '".$lang."', 'N', '".$_POST['mail']."')");
            $smarty -> assign("Message", "<br /><br />".MAIL_SEND);
        }
    }
}

/**
 * Read article
 */
if (isset($_GET['article']))
{
  checkvalue($_GET['article']);
    $objArticle = $db -> Execute("SELECT `id`, `paper_id`, `title`, `body`, `author`, `added` FROM `newspaper` WHERE `id`=".$_GET['article']);
    if (!$objArticle -> fields['id'] || ($objArticle -> fields['added'] == 'N' && $player -> rank != 'Admin' && $player -> rank != 'Redaktor'))
    {
        error(ERROR);
    }
    $intPage = $_GET['article'] + 1;
    $strNext = '';
    $objNext = $db -> Execute("SELECT `id` FROM `newspaper` WHERE `id`=".$intPage." AND `paper_id`=".$objArticle -> fields['paper_id']);
    if (!$objNext -> fields['id'])
    {
        $intPage = 0;
    }
        else
    {
        $strNext = "<input type=\"submit\" name=\"next\" value=\"".A_NEXT."\" />";
    }
    $objNext -> Close();
    $intPage2 = $_GET['article'] - 1;
    $strPrevious = '';
    $objPrevious = $db -> Execute("SELECT `id` FROM `newspaper` WHERE `id`=".$intPage2." AND `paper_id`=".$objArticle -> fields['paper_id']);
    if (!$objPrevious -> fields['id'])
    {
        $intPage2 = 0;
    }
        else
    {
        $strPrevious = "<input type=\"submit\" name=\"next\" value=\"".A_PREVIOUS."\" />";
    }
    $objPrevious -> Close();
    $objComments = $db -> Execute("SELECT count(*) FROM `newspaper_comments` WHERE `textid`=".$objArticle -> fields['id']);
    $intComments = $objComments -> fields['count(*)'];
    $objComments -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'new')
    {
        $smarty -> assign("Newslink", "step=new");
    }
        elseif (isset($_GET['read']))
    {
        $smarty -> assign("Newslink", "read=".$_GET['read']);
    }
        elseif (isset($_GET['step3']))
    {
        $smarty -> assign("Newslink", "step3=S");
    }
    $smarty -> assign(array("Artid" => $objArticle -> fields['id'],
                            "Arttitle" => $objArticle -> fields['title'],
                            "Artauthor" => $objArticle -> fields['author'],
                            "Artbody" => $objArticle -> fields['body'],
                            "Artcomments" => $intComments,
                            "Next" => $strNext,
                            "Previous" => $strPrevious,
                            "Pageid" => $intPage,
                            "Pageid2" => $intPage2,
                            "Acomment" => A_COMMENT,
                            "Twrite" => T_WRITE,
                            "Tcomments" => T_COMMENTS,
                            "Ttitle" => T_TITLE));
    $objArticle -> Close();
}

/**
* Initialization of variables
*/
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}
if (!isset($_GET['step2']))
{
    $_GET['step2'] = '';
}
if (!isset($_GET['read']))
{
    $_GET['read'] = '';
}
if (!isset($_GET['comments']))
{
    $_GET['comments'] = '';
}
if (!isset($_GET['step3']))
{
    $_GET['step3'] = '';
}
if (!isset($_GET['article']))
{
    $_GET['article'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Step" => $_GET['step'],
                        "Step2" => $_GET['step2'],
                        "Read" => $_GET['read'],
                        "Comments" => $_GET['comments'],
                        "Rank" => $player -> rank,
                        "Aback" => A_BACK,
                        "Step3" => $_GET['step3'],
                        "Article" => $_GET['article']));
$smarty -> display('newspaper.tpl');

require_once("includes/foot.php");
?>
