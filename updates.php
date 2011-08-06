<?php
/**
 *   File functions:
 *   Show main news in game
 *
 *   @name                 : updates.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @author               : mori <ziniquel@users.sourceforge.net>
 *   @version              : 1.0
 *   @since                : 15.02.2006
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
// $Id: updates.php 566 2006-09-13 09:31:08Z thindil $

$title = "Wieści";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/updates.php");

/**
* Informations for new players
*/
if ($player -> logins < 5)
{
    $smarty -> assign("Newplayer", NEW_PLAYER);
}
    else
{
    $smarty -> assign("Newplayer", '');
}

if (!isset ($_GET['view'])) 
{
    $upd = $db -> SelectLimit("SELECT * FROM updates WHERE lang='".$player -> lang."' ORDER BY id DESC", 1);
    if ($player -> rank == 'Admin') 
    {
        $modtext = "(<a href=\"addupdate.php?modify=".$upd -> fields['id']."\">".A_CHANGE."</a>)";
    } 
        else 
    {
        $modtext = '';
    }
    if (isset($upd -> fields['id']))
    {
        $objQuery = $db -> Execute("SELECT id FROM upd_comments WHERE updateid=".$upd -> fields['id']);
        $intComments = $objQuery -> RecordCount();
        $objQuery -> Close();
    }
        else
    {
        $intComments = 0;
    }
    $smarty -> assign(array("Title1" => $upd -> fields['title'], 
        "Starter" => $upd -> fields['starter'], 
        "Update" => $upd -> fields['updates'], 
        "Modtext" => $modtext, 
        "Date" => T_DAY.$upd -> fields['time'],
        "Alast10" => A_LAST10,
        "Comments" => $intComments,
        "Updid" => $upd -> fields['id']));    
} 

if (isset($_GET['view']))
{
    $upd = $db -> SelectLimit("SELECT * FROM updates WHERE lang='".$player -> lang."' ORDER BY id DESC", 10);
    $arrtitle = array();
    $arrstarter = array();
    $arrnews = array();
    $arrmodtext = array();
    $arrtimestamp = array();
    $arrComments = array();
    $arrUpdid = array();
    $i = 0;    
    while (!$upd -> EOF) 
    {
        if ($player -> rank == 'Admin') 
        {
            $arrmodtext[$i] = "(<a href=\"addupdate.php?modify=".$upd -> fields['id']."\">".A_CHANGE."</a>)";
        } 
            else 
        {
            $arrmodtext[$i] = '';
        }
        $objQuery = $db -> Execute("SELECT id FROM upd_comments WHERE updateid=".$upd -> fields['id']);
        $arrComments[$i] = $objQuery -> RecordCount();
        $objQuery -> Close();
        $arrtitle[$i] = $upd -> fields['title'];
        $arrstarter[$i] = $upd -> fields['starter'];
        $arrnews[$i] = $upd -> fields['updates'];
        $arrUpdid[$i] = $upd -> fields['id'];
        if (isset($upd -> fields['time']))
        {
            $arrtimestamp[$i] = T_DAY.$upd -> fields['time'];
        }
            else
        {
            $arrtimestamp[$i] = '';
        }
        $upd -> MoveNext();
        $i = $i + 1;        
    }
    $upd -> Close();
    $smarty -> assign(array("Title1" => $arrtitle, 
        "Starter" => $arrstarter, 
        "Update" => $arrnews, 
        "Modtext" => $arrmodtext, 
        "Date" => $arrtimestamp,
        "Comments" => $arrComments,
        "Updid" => $arrUpdid));    
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
        displaycomments($_GET['text'], 'updates', 'upd_comments', 'updateid');
        $smarty -> assign(array("Tauthor" => $arrAuthor,
            "Tbody" => $arrBody,
            "Amount" => $i,
            "Cid" => $arrId,
            "Tdate" => $arrDate,
            "Nocomments" => NO_COMMENTS,
            "Addcomment" => ADD_COMMENT,
            "Adelete" => A_DELETE,
            "Aadd" => A_ADD,
            "Aback" => A_BACK,
            "Writed" => WRITED));
    }

    /**
    * Add comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'add')
    {
        addcomments($_POST['tid'], 'upd_comments', 'updateid');
    }

    /**
    * Delete comment
    */
    if (isset($_GET['action']) && $_GET['action'] == 'delete')
    {
        deletecomments('upd_comments');
    }
}

/**
* Initialization of variables
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
$smarty -> assign(array("View" => $_GET['view'],
    "Writeby" => WRITE_BY,
    "Step" => $_GET['step'],
    "Text" => $_GET['text'],
    "Rank" => $player -> rank,
    "Acomments" => A_COMMENTS));
$smarty -> display ('updates.tpl');

require_once("includes/foot.php");
?>
