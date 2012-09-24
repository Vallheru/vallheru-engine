<?php
/**
 *   File functions:
 *   Court of law - information about court, law
 *
 *   @name                 : court.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 22.09.2012
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

$title = "Gmach sądu";
require_once("includes/head.php");

if ($player -> location == 'Altara')
{
    $strCity = $city1b;
    $strCity2 = 'Altarą';
}
    else
{
    $strCity = $city2;
    $strCity2 = $city2;
}

/**
* Get the localization for game
*/
require_once("languages/".$lang."/court.php");

/**
* Main menu
*/
if (!isset($_GET['step2']) && !isset($_GET['modify']))
{
    $fltLogins = fmod($player -> logins, 2);
    if ($fltLogins)
    {
        $strCourtinfo = C_INFO;
    }
        else
    {
        $strCourtinfo = C_INFO2;
    }
    $smarty -> assign(array("Cinfo" => $strCourtinfo,
        "Ajudges" => A_JUDGES,
        "Aaldermen" => A_ALDERMEN,
        "Alawyers" => A_LAWYERS,
        "Tlists" => T_LISTS,
        "Aadmin" => A_ADMIN,
        "Tinfo" => T_INFO,
        "Arules" => A_RULES,
        "Acases" => A_CASES,
        "Averdicts" => A_VERDICTS));
}
    else
{
    $smarty -> assign(array("Tlang" => T_LANG,
        "Ttitle2" => T_TITLE,
        "Tbody2" => T_BODY,
        "Aback" => A_BACK));
}

/**
* List staff of court
*/
if (isset($_GET['list']))
{
    $arrList = array('judges', 'aldermen', 'lawyers');
    if (!in_array($_GET['list'], $arrList))
    {
        error(ERROR);
    }
    $arrName = array();
    $arrId = array();
    $i = 0;
    if ($_GET['list'] == 'judges')
    {
        $strRank = JUDGE3;
        $strText = JUDGE2;
    }
    if ($_GET['list'] == 'aldermen')
    {
        $strRank = ALDERMAN;
        $strText = ALDERMAN2;
    }
    if ($_GET['list'] == 'lawyers')
    {
        $strRank = LAWYER2;
        $strText = LAWYER3;
    }
    $objJudge = $db -> Execute("SELECT id, user FROM players WHERE rank='".$strRank."'");
    while (!$objJudge -> EOF)
    {
        $arrName[$i] = $objJudge -> fields['user'];
        $arrId[$i] = $objJudge -> fields['id'];
        $i = $i + 1;
        $objJudge -> MoveNext();
    }
    $objJudge -> Close();
    $smarty -> assign(array("Jname" => $arrName,
        "Jid" => $arrId,
        "Amount" => $i,
        "Trank" => $strText,
        "Nopeople" => NO_PEOPLE,
        "Listinfo" => LIST_INFO,
        "Ina" => IN_A));
}
    else
{
    $_GET['list'] = '';
}

/**
* List of rules, cases and verdicts in court
*/
if (isset($_GET['step']) && ($_GET['step'] == 'rules' || $_GET['step'] == 'cases' || $_GET['step'] == 'verdicts'))
{
    if (!isset($_GET['step2']))
    {
        if ($_GET['step'] == 'rules')
        {
            $strType = 'rule';
            $strInfo = RULES;
            $strInfo2 = RULES2;
        }
        if ($_GET['step'] == 'cases')
        {
            $strType = 'case';
            $strInfo = CASES;
            $strInfo2 = CASES2;
        }
        if ($_GET['step'] == 'verdicts')
        {
            $strType = 'verdict';
            $strInfo = VERDICTS;
            $strInfo2 = VERDICTS2;
        }
        $objRule = $db -> Execute("SELECT id, title FROM court WHERE lang='".$lang."' AND type='".$strType."'");
        $arrId = array();
        $arrTitle = array();
        $i = 0;
        while(!$objRule -> EOF)
        {
            $arrId[$i] = $objRule -> fields['id'];
            $arrTitle[$i] = $objRule -> fields['title'];
            $i = $i + 1;
            $objRule -> MoveNext();
        }
        $objRule -> Close();
        $smarty -> assign(array("Amount" => $i,
            "Rid" => $arrId,
            "Rtitle" => $arrTitle,
            "Noitems" => $strInfo,
            "Itemsinfo" => $strInfo2,
            "Tnoitems" => T_NO_ITEMS,
            "Listinfo" => LIST_INFO,
            "Listinfo2" => LIST_INFO2));
    }
    if (isset($_GET['step2']))
    {
	checkvalue($_GET['step2']);
        $objRule = $db -> Execute("SELECT id, title, body, date FROM court WHERE id=".$_GET['step2']);
        if (!$objRule -> fields['id'])
        {
            error(ERROR);
        }
        $smarty -> assign(array("Rtitle" => $objRule -> fields['title'],
            "Rbody" => $objRule -> fields['body'],
            "Rdate" => $objRule -> fields['date'],
            "Rid" => $objRule -> fields['id'],
            "Mdate" => M_DATE,
            "Achange" => A_CHANGE,
            "Acomments" => A_COMMENTS));
        $objRule -> Close();
    }
}

/**
* Admins - add rules, cases, verdicts
*/
if (isset($_GET['step']) && $_GET['step'] == 'admin')
{
    $arrRank = array('Admin', 'Sędzia', 'Kanclerz Sądu');
    if (!in_array($player -> rank, $arrRank))
    {
        error(ERROR);
    }
    $smarty -> assign(array("Aaddrule" => A_ADD_RULE,
			    "Aaddcase" => A_ADD_CASE,
			    "Aaddverd" => A_ADD_VERD,
			    "Aadd" => "Dodaj"));
    if (isset($_GET['step2']))
    {
        $arrStep2 = array('addrule', 'addcase', 'addverdict');
        if (!in_array($_GET['step2'], $arrStep2))
        {
            error(ERROR);
        }

        if ($_GET['step2'] == 'addrule')
        {
            $strType = 'rule';
        }
        if ($_GET['step2'] == 'addcase')
        {
            $strType = 'case';
        }
        if ($_GET['step2'] == 'addverdict')
        {
            $strType = 'verdict';
        }
        if (isset($_GET['action']) && $_GET['action'] == 'add')
        {
            require_once('includes/bbcode.php');
            $_POST['body'] = bbcodetohtml($_POST['body']);
            $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
            $strTitle = $db -> qstr($_POST['ttitle'], get_magic_quotes_gpc());
            $strDate = $db -> DBDate($newdate);
            $db -> Execute("INSERT INTO `court` (`title`, `body`, `type`, `date`) VALUES(".$strTitle.", ".$strBody.", '".$strType."', ".$strDate.")");
            error(ADDED);
        }
    }
}

/**
* Modify rules, cases and verdicts
*/
if (isset($_GET['modify']))
{
    $arrRank = array('Admin', 'Sędzia', 'Kanclerz Sądu');
    if (!in_array($player -> rank, $arrRank))
    {
        error(ERROR);
    }
    checkvalue($_GET['modify']);
    $objText = $db -> Execute("SELECT `id`, `title`, `body` FROM `court` WHERE `id`=".$_GET['modify']);
    if (!$objText -> fields['id'])
    {
        error(ERROR);
    }
    require_once('includes/bbcode.php');
    $strBody = htmltobbcode($objText -> fields['body']);
    $smarty -> assign(array("Tid" => $objText -> fields['id'],
        "Ttitle" => $objText -> fields['title'],
        "Tbody" => $strBody,
        "Achange" => A_CHANGE));
    if (isset($_GET['action']) && $_GET['action'] == 'change')
    {
        if (empty($_POST['ttitle']) || empty($_POST['body']))
        {
            error(EMPTY_FIELDS);
        }
	checkvalue($_POST['tid']);
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $strTitle = $db -> qstr($_POST['ttitle'], get_magic_quotes_gpc());
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("UPDATE `court` SET `title`=".$strTitle.", `body`=".$strBody.", `date`=".$strDate." WHERE `id`=".$_POST['tid']);
        error(CHANGED);
    }
}
    else
{
    $_GET['modify'] = '';
}

/**
* Comments to text
*/
if (isset($_GET['step2']) && $_GET['step2'] == 'comments')
{
    $smarty -> assign("Amount", '');
    
    /**
    * Display comments
    */
    if (!isset($_GET['action']))
    {
	checkvalue($_GET['text']);
        $objText = $db -> Execute("SELECT id FROM court WHERE id=".$_GET['text']." AND type='case'");
        if (!$objText -> fields['id'])
        {
            error(NO_TEXT);
        }
        $objText -> Close();
        $objComments = $db -> Execute("SELECT id, body, author FROM court_cases WHERE textid=".$_GET['text']);
        $arrBody = array();
        $arrAuthor = array();
        $arrId = array();
        $i = 0;
        while (!$objComments -> EOF)
        {
            $arrBody[$i] = $objComments -> fields['body'];
            $arrAuthor[$i] = $objComments -> fields['author'];
            $arrId[$i] = $objComments -> fields['id'];
            $i = $i + 1;
            $objComments -> MoveNext();
        }
        $objComments -> Close();
        $smarty -> assign(array("Tauthor" => $arrAuthor,
            "Tbody" => $arrBody,
            "Amount" => $i,
            "Cid" => $arrId,
            "Nocomments" => NO_COMMENTS,
            "Addcomment" => ADD_COMMENT,
            "Adelete" => A_DELETE,
            "Aadd" => A_ADD,
            "Text" => $_GET['text']));
    }

    /**
    * Add comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'add')
    {
        if ($player -> rank != 'Sędzia' && $player -> rank != 'Admin' && $player -> rank != 'Prawnik')
        {
            error(NO_PERM);
        }
        if (empty($_POST['body']))
        {
            error(EMPTY_FIELDS);
        }
	require_once('includes/bbcode.php');
	$_POST['body'] = bbcodetohtml($_POST['body']);
	checkvalue($_POST['tid']);
        $strAuthor = $player -> user." ID: ".$player -> id;
        $db -> Execute("INSERT INTO `court_cases` (`textid`, `author`, `body`) VALUES(".$_POST['tid'].", '".$strAuthor."', '".$_POST['body']."')");
        error(C_ADDED);
    }

    /**
    * Delete comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'delete')
    {
        if ($player -> rank != 'Sędzia' && $player -> rank != 'Admin' && $player -> rank != 'Kanclerz Sądu')
        {
            error(NO_PERM);
        }
	checkvalue($_GET['cid']);
        $db -> Execute("DELETE FROM court_cases WHERE id=".$_GET['cid']);
        error(C_DELETED);
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['step2']))
{
    $_GET['step2'] = '';
}

if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("List" => $_GET['list'],
    "Rank" => $player -> rank,
    "Step" => $_GET['step'],
    "Step2" => $_GET['step2'],
    "Modify" => $_GET['modify']));
$smarty -> display ('court.tpl');

require_once("includes/foot.php");
?>
