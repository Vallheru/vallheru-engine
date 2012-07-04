<?php
/**
 *   File functions:
 *   Player notes
 *
 *   @name                 : notatnik.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 04.07.2012
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

$title = "Notatnik";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/notatnik.php");


/**
 * Delete post
 */
if (isset ($_GET['akcja']) && $_GET['akcja'] == 'skasuj') 
{
    checkvalue($_GET['nid']);
    $did = $db -> Execute("SELECT `id`, `gracz` FROM `notatnik` WHERE `id`=".$_GET['nid']);
    if (!$did -> fields['id']) 
      {
        message('error', NO_TEXT);
      }
    elseif ($player -> id != $did -> fields['gracz']) 
      {
        message('error', NOT_YOUR);
      }
    else
      {
	$db -> Execute("DELETE FROM `notatnik` WHERE `gracz`=".$player -> id." AND `id`=".$_GET['nid']);
	message('success', YOU_DELETE);
      }
    unset($_GET['akcja']);
}

/**
 * Add post
 */
if (isset ($_GET['akcja']) && $_GET['akcja'] == 'dodaj') 
  {
    $smarty -> assign(array("Ttitle" => 'Tytuł:',
			    "Note" => NOTE,
                            "Asave" => A_SAVE,
                            "Nlink" => "dodaj&amp;step=send",
                            "Ntext" => '',
			    "Ntitle" => ''));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
      if (empty ($_POST['body']) || empty($_POST['title'])) 
        {
            error ("Podaj tytuł oraz treść notatki.");
        }
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $strDate = $db -> DBDate($newdate);
	$_POST['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES);
        $db -> Execute("INSERT INTO `notatnik` (`gracz`, `tekst`, `czas`, `title`) VALUES(".$player -> id.", ".$strBody.", ".$strDate.", '".$_POST['title']."')");
        message('success', YOU_ADD);
	unset($_GET['akcja']);
    }
}

/**
 * Edit post
 */
if (isset($_GET['akcja']) && $_GET['akcja'] == 'edit')
{
    checkvalue($_GET['nid']);
    $objText = $db -> Execute("SELECT `id`, `gracz`, `tekst`, `title` FROM `notatnik` WHERE `id`=".$_GET['nid']);
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
			    "Ntitle" => $objText->fields['title'],
			    "Ttitle" => "Tytuł:",
                            "Nlink" => "edit&amp;nid=".$_GET['nid']."&amp;step=edit"));
    $objText -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'edit') 
    {
        if (empty ($_POST['body']) || empty($_POST['title'])) 
        {
            error("Podaj tytuł oraz treść notatki.");
        }
	$_POST['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES);
        require_once('includes/bbcode.php');
        $_POST['body'] = bbcodetohtml($_POST['body']);
        $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("UPDATE `notatnik` SET `tekst`=".$strBody.", `czas`=".$strDate.", `title`='".$_POST['title']."' WHERE `id`=".$_GET['nid']);
        message('success', YOU_EDIT);
	unset($_GET['akcja']);
    }
}

if (!isset($_GET['akcja']))
  {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
      {
	$strChecked = "";
      }
    else
      {
	$strChecked = 'checked="checkded"';
      }

    //Pagination
    $objAmount = $db->Execute("SELECT count(`id`) FROM `notatnik` WHERE `gracz`=".$player->id);
    $intPages = ceil($objAmount->fields['count(`id`)'] / 25);
    $objAmount->Close();
    if (!isset($_GET['page']))
      {
	$intPage = 1;
      }
    else
      {
	$intPage = $_GET['page'];
      }
    
    $arrNotes = $db->GetAll("SELECT `id`, `tekst`, `czas`, `title` FROM `notatnik` WHERE `gracz`=".$player -> id." ORDER BY `id` DESC LIMIT ".(25 * ($intPage - 1)).", 25");
    $smarty -> assign(array("Notes" => $arrNotes,
			    "Notesinfo" => NOTES_INFO,
			    "Ntime" => N_TIME,
			    "Adelete" => A_DELETE,
			    "Aadd" => A_ADD,
			    "Aedit" => A_EDIT,
			    "Tpages" => $intPages,
			    "Tpage" => $intPage,
			    "Fpage" => "Idź do strony:",
			    "Checked" => $strChecked));
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
$smarty -> assign(array("Action" => $_GET['akcja'],
			"Aback" => "Wróć"));
$smarty -> display ('notatnik.tpl');

require_once("includes/foot.php");
?>
