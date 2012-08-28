<?php
/**
 *   File functions:
 *   Player notes
 *
 *   @name                 : notatnik.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 28.08.2012
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


if (isset($_GET['akcja']))
  {
    $blnSuccess = FALSE;
    /**
     * Delete post
     */
    if ($_GET['akcja'] == 'skasuj') 
      {
	checkvalue($_GET['nid']);
	$did = $db -> Execute("SELECT `id` FROM `notatnik` WHERE `id`=".$_GET['nid']." AND `gracz`=".$player->id);
	if (!$did -> fields['id']) 
	  {
	    message('error', "Nie ma takiego wpisu!");
	  }
	else
	  {
	    $db -> Execute("DELETE FROM `notatnik` WHERE `gracz`=".$player -> id." AND `id`=".$_GET['nid']);
	    message('success', "Skasowałeś wpis.");
	  }
	$blnSuccess = TRUE;
      }

    if (($_GET['akcja'] == 'dodaj' || $_GET['akcja'] == 'edit'))
      {
	$smarty -> assign(array("Ttitle" => 'Tytuł:',
				"Note" => "Notatka",
				"Asave" => "Zapisz",
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
      }

    /**
     * Add post
     */
    if ($_GET['akcja'] == 'dodaj') 
      {
	$smarty -> assign(array("Nlink" => "dodaj&amp;step=send",
				"Ntext" => '',
				"Ntitle" => ''));
	if (isset ($_GET['step']) && $_GET['step'] == 'send') 
	  {
	    if (empty ($_POST['body']) || empty($_POST['title'])) 
	      {
		message('error', "Podaj tytuł oraz treść notatki.");
		$smarty->assign(array("Ntext" => $_POST['body'],
				      "Ntitle" => $_POST['title']));
	      }
	    else
	      {
		require_once('includes/bbcode.php');
		$_POST['body'] = bbcodetohtml($_POST['body']);
		$strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
		$strDate = $db -> DBDate($newdate);
		$_POST['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES);
		$db -> Execute("INSERT INTO `notatnik` (`gracz`, `tekst`, `czas`, `title`) VALUES(".$player -> id.", ".$strBody.", ".$strDate.", '".$_POST['title']."')");
		message('success', "Notatka dodana.");
		$blnSuccess = TRUE;
	      }
	  }
      }

    /**
     * Edit post
     */
    if ($_GET['akcja'] == 'edit')
      {
	checkvalue($_GET['nid']);
	$objText = $db -> Execute("SELECT `id`, `tekst`, `title` FROM `notatnik` WHERE `id`=".$_GET['nid']." AND `gracz`=".$player->id);
	if (!$objText -> fields['id']) 
	  {
	    message('error', "Nie ma takiego wpisu!");
	    unset($_GET['akcja']);
	  }
	else
	  {
	    require_once('includes/bbcode.php');
	    $strNbody = htmltobbcode($objText -> fields['tekst']);
	    $smarty -> assign(array("Ntext" => $strNbody,
				    "Ntitle" => $objText->fields['title'],
				    "Nlink" => "edit&amp;nid=".$_GET['nid']."&amp;step=edit"));
	  }
	$objText -> Close();
	if (isset($_GET['step']) && $_GET['step'] == 'edit') 
	  {
	    if (empty ($_POST['body']) || empty($_POST['title'])) 
	      {
		message('error', "Podaj tytuł oraz treść notatki.");
		$smarty->assign(array("Ntext" => $_POST['body'],
				      "Ntitle" => $_POST['title']));
		
	      }
	    else
	      {
		$_POST['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES);
		require_once('includes/bbcode.php');
		$_POST['body'] = bbcodetohtml($_POST['body']);
		$strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
		$strDate = $db -> DBDate($newdate);
		$db -> Execute("UPDATE `notatnik` SET `tekst`=".$strBody.", `czas`=".$strDate.", `title`='".$_POST['title']."' WHERE `id`=".$_GET['nid']);
		message('success', "Notatka zmieniona.");
		$blnSuccess = TRUE;
	      }
	  }
      }

    if ($blnSuccess)
      {
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
			    "Notesinfo" => "Tutaj możesz zapisywać sobie różne przydatne informacje.",
			    "Notesinfo2" => "Oto lista notatek:",
			    "Ntime" => "Czas",
			    "Adelete" => "skasuj",
			    "Aadd" => "Dodaj wpis",
			    "Aedit" => "edytuj",
			    "Tpages" => $intPages,
			    "Tpage" => $intPage,
			    "Fpage" => "Idź do strony:",
			    "Checked" => $strChecked));
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
