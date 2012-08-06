<?php
/**
 *   File functions:
 *   Show game news
 *
 *   @name                 : news.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 06.08.2012
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
require_once("languages/".$lang."/news.php");

/**
* Display one news
*/
if (!isset ($_GET['view'])) 
{
    $upd = $db -> SelectLimit("SELECT * FROM `news` WHERE `lang`='".$lang."' AND `added`='Y' AND `show`='Y' ORDER BY `id` DESC", 1);
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
    $objQuery = $db -> Execute("SELECT count(`id`) FROM `news` WHERE `lang`='".$lang."' AND `added`='N'");
    $intWaiting = $objQuery -> fields['count(`id`)'];
    $objQuery -> Close();
    $objAccepted = $db -> Execute("SELECT count(`id`) FROM `news` WHERE `lang`='".$lang."' AND `show`='N'");
    $intAccepted = $objAccepted -> fields['count(`id`)'];
    $objAccepted -> Close();
    $smarty -> assign(array("Title1" => $upd -> fields['title'], 
			    "Starter" => $upd -> fields['starter'],
			    "Pdate" => $upd->fields['pdate'],
			    "News" => $upd -> fields['news'],
			    "Comments" => $intComments,
			    "Newsid" => $upd -> fields['id'],
			    "Aaddnews" => A_ADD_NEWS,
			    "Twaiting" => T_WAITING,
			    "Taccepted" => T_ACCEPTED,
			    "Accepted" => $intAccepted,
			    "Nonews" => "Nie ma jeszcze opublikowanych plotek",
			    "Waiting" => $intWaiting));
    $_GET['view'] = '';
} 

/**
* Display last 10 news
*/
else
{
  if ($_GET['view'] == 'all')
    {
      $upd = $db -> SelectLimit("SELECT * FROM news WHERE `lang`='".$lang."' AND `added`='Y' AND `show`='Y'  ORDER BY `id` DESC", 10);
      $arrtitle = array();
      $arrstarter = array();
      $arrnews = array();
      $arrId = array();
      $arrComments = array();
      $arrDate = array();
      while (!$upd -> EOF) 
	{
	  $objQuery = $db -> Execute("SELECT count(`id`) FROM `news_comments` WHERE `newsid`=".$upd -> fields['id']);
	  $arrComments[] = $objQuery -> fields['count(`id`)'];
	  $objQuery -> Close();
	  $arrtitle[] = $upd -> fields['title'];
	  $arrstarter[] = $upd -> fields['starter'];
	  $arrnews[] = $upd -> fields['news'];
	  $arrId[] = $upd -> fields['id'];
	  $arrDate[] = $upd->fields['pdate'];
	  $upd -> MoveNext();
	}
      $upd -> Close();
      $smarty -> assign(array("Title1" => $arrtitle, 
			      "Starter" => $arrstarter, 
			      "News" => $arrnews,
			      "Newsid" => $arrId,
			      "Newsdate" => $arrDate,
			      "Comments" => $arrComments));
    }
  else
    {
      error('Zapomnij o tym.');
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
        addcomments($_GET['text'], 'news_comments', 'newsid');
      }

    /**
    * Delete comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'delete')
    {
        deletecomments('news_comments');
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
    $intAmount = displaycomments($_GET['text'], 'news', 'news_comments', 'newsid');
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
			    "Faction" => "news.php?step=comments&amp;text=".$_GET['text'],
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
* Add news (simple user)
*/
if (isset($_GET['step']) && $_GET['step'] == 'add')
{
    $smarty -> assign(array("Ttitle2" => T_TITLE,
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
        if (empty($_POST['ttitle']) || empty($_POST['body']))
        {
            error(EMPTY_FIELDS);
        }
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strAuthor = $player -> user." (".$player -> id.")";
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $strTitle = $db -> qstr($_POST['ttitle'], get_magic_quotes_gpc());
        $db -> Execute("INSERT INTO news (title, news, added, lang, starter) VALUES(".$strTitle.", ".$strBody.", 'N', '".$lang."', '".$strAuthor."')");
	$objStaff = $db -> Execute("SELECT `id` FROM `players` WHERE `rank`='Admin' OR `rank`='Staff'");
	$strDate = $db -> DBDate($newdate);
	$_POST['ttitle'] = str_replace("'", "", strip_tags($_POST['ttitle']));
	while (!$objStaff->EOF) 
	  {
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objStaff->fields['id'].", 'Nowa plotka \"".$_POST['ttitle']."\" (autor: <a href=\"view.php?view=".$player->id."\">".$player->user."</a>) oczekuje na akceptację.', ".$strDate.", 'A')") or die($db->ErrorMsg());
	    $objStaff->MoveNext();
	  }
	$objStaff->Close();
        error(YOU_ADD);
    }
}

/**
* Initialization of variable
*/
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
