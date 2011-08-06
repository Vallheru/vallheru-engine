<?php
/**
 *   File functions:
 *   Player notes
 *
 *   @name                 : notatnik.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 11.09.2006
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
// $Id: notatnik.php 566 2006-09-13 09:31:08Z thindil $

$title = "Notatnik";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/notatnik.php");

$log = $db -> SelectLimit("SELECT `id`, `tekst`, `czas` FROM `notatnik` WHERE `gracz`=".$player -> id." ORDER BY `id` DESC", 25);
$arrtime = array();
$arrtext = array();
$arrid = array();
$i = 0;
while (!$log -> EOF) 
{
    $arrtime[$i] = $log -> fields['czas'];
    $arrtext[$i] = $log -> fields['tekst'];
    $arrid[$i] = $log -> fields['id'];
    $log -> MoveNext();
    $i = $i + 1;
}
$log -> Close();
$smarty -> assign(array("Notetime" => $arrtime, 
                        "Notetext" => $arrtext, 
                        "Noteid" => $arrid,
                        "Notesinfo" => NOTES_INFO,
                        "Ntime" => N_TIME,
                        "Adelete" => A_DELETE,
                        "Aadd" => A_ADD,
                        "Aedit" => A_EDIT));
/**
 * Delete post
 */
if (isset ($_GET['akcja']) && $_GET['akcja'] == 'skasuj') 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['nid'])) 
    {
        error (ERROR);
    }
    $did = $db -> Execute("SELECT `id`, `gracz` FROM `notatnik` WHERE `id`=".$_GET['nid']);
    if (!$did -> fields['id']) 
    {
        error (NO_TEXT);
    }
    if ($player -> id != $did -> fields['gracz']) 
    {
        error (NOT_YOUR);
    }
    $db -> Execute("DELETE FROM `notatnik` WHERE `gracz`=".$player -> id." AND `id`=".$_GET['nid']);
    error (YOU_DELETE);
}

/**
 * Add post
 */
if (isset ($_GET['akcja']) && $_GET['akcja'] == 'dodaj') 
{
    $smarty -> assign(array("Note" => NOTE,
                            "Asave" => A_SAVE,
                            "Nlink" => "dodaj&amp;step=send",
                            "Ntext" => ''));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
        if (empty ($_POST['body'])) 
        {
            error (EMPTY_FIELD);
        }
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `notatnik` (`gracz`, `tekst`, `czas`) VALUES(".$player -> id.", ".$strBody.", ".$strDate.")");
        error (YOU_ADD);
    }
}

/**
 * Edit post
 */
if (isset($_GET['akcja']) && $_GET['akcja'] == 'edit')
{
    if (!ereg("^[1-9][0-9]*$", $_GET['nid'])) 
    {
        error(ERROR);
    }
    $objText = $db -> Execute("SELECT `id`, `gracz`, `tekst` FROM `notatnik` WHERE `id`=".$_GET['nid']);
    if (!$objText -> fields['id']) 
    {
        error(NO_TEXT);
    }
    if ($player -> id != $objText -> fields['gracz']) 
    {
        error(NOT_YOUR);
    }
    require_once('includes/bbcode.php');
    $strNbody = htmltobbcode($objText -> fields['tekst']);
    $smarty -> assign(array("Note" => NOTE,
                            "Asave" => A_SAVE,
                            "Ntext" => $strNbody,
                            "Nlink" => "edit&amp;nid=".$_GET['nid']."&amp;step=edit"));
    $objText -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'edit') 
    {
        if (empty ($_POST['body'])) 
        {
            error(EMPTY_FIELD);
        }
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("UPDATE `notatnik` SET `tekst`=".$strBody.", `czas`=".$strDate." WHERE `id`=".$_GET['nid']);
        error(YOU_EDIT);
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['akcja'])) 
{
    $_GET['akcja'] = '';
}

/**
* Assign variable to template and display page
*/
$smarty -> assign("Action", $_GET['akcja']);
$smarty -> display ('notatnik.tpl');

require_once("includes/foot.php");
?>
