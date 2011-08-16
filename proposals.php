<?php
/**
 *   File functions:
 *   Proposals in game
 *
 *   @name                 : proposals.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 16.08.2011
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

$title = "Plac myślicieli";
require_once("includes/head.php");

//Add new proposition
if ((isset($_GET['proposal'])) && ($_GET['proposal'] == -1))
  {
    $smarty->assign("Add", 0);
    if (!isset($_GET['send']))
      {
	$smarty->assign(array("Pdesc" => "Dłuższy opis",
			      "Psend" => "Dodaj",
			      "Send" => 0));
      }
    else
      {
	if ((!isset($_POST['ptitle'])) || (!isset($_POST['pbody'])))
	  {
	    error("Wypełnij wszystkie pola!");
	  }
	require_once('includes/bbcode.php');
	$_POST['ptitle'] = strip_tags($_POST['ptitle']);
	require_once('includes/bbcode.php');
	$_POST['pbody'] = bbcodetohtml($_POST['pbody']);
	$strBody = $db -> qstr($_POST['pbody'], get_magic_quotes_gpc());
	$strTitle = $db -> qstr($_POST['ptitle'], get_magic_quotes_gpc());
	$db->Execute("INSERT INTO `proposals` (`title`, `body`, `author`, `state`, `voters`) VALUES(".$strTitle.", ".$strBody.", ".$player->id.", 'Dyskusja', '".$player->id."')");
	$smarty->assign(array("Send" => 1,
			      "Added" => "Propozycja została dodana."));
      }
  }

//Show selected proposal
if ((isset($_GET['proposal'])) && ($_GET['proposal'] != -1))
  {
    checkvalue($_GET['proposal']);
    $objProposal = $db->Execute("SELECT * FROM `proposals` WHERE `id`=".$_GET['proposal']);
    if (!$objProposal->fields['id'])
      {
	error("Nie ma takiej propozycji.");
      }
    $objName = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$objProposal->fields['author']);
    $strName = $objName->fields['user'];
    $objName->Close();
    if (strlen($objProposal->fields['voters']) > 0)
      {
	$arrTmp = explode(";", $objProposal->fields['voters']);
	$intVotesup = count($arrTmp);
      }
    else
      {
	$intVotesup = 0;
      }
    if (strlen($objProposal->fields['voters2']) > 0)
      {
	$arrTmp = explode(";", $objProposal->fields['voters2']);
	$intVotesdown = count($arrTmp);
      }
    else
      {
	$intVotesdown = 0;
      }
    require_once('includes/comments.php');
    if (!isset($_GET['page']))
      {
	$intPage = -1;
      }
    else
      {
	$intPage = $_GET['page'];
      }
    $intAmount = displaycomments($_GET['proposal'], 'proposals', 'prop_comments', 'proposalid');
    define("C_DELETED", "Komentarz skasowany");
    define("C_ADDED", "Komentarz dodany.");
    $smarty->assign(array("Vremove" => "Cofnij głos",
			  "Up" => "Za",
			  "Down" => "Przeciw",
			  "Aup" => $intVotesup,
			  "Adown" => $intVotesdown,
			  "Pdesc" => "Opis",
			  "Pauthor" => "Autor",
			  "Pauthor1" => $strName,
			  "Ptitle1" => $objProposal->fields['title'],
			  "Nocomments" => "Brak dyskusji",
			  "Tbody" => $arrBody,
			  "Amount" => $intAmount,
			  "Cid" => $arrId,
			  "Tdate" => $arrDate,
			  "Tpages" => $intPages,
			  "Tpage" => $intPage,
			  "Tauthor" => $arrAuthor,
			  "Addcomment" => "Dodaj komentarz",
			  "Adelete" => "Skasuj",
			  "Writed" => "napisał(a)",
			  "Aadd" => "Dodaj",
			  "Rank" => $player->rank,
			  "Pbody" => $objProposal->fields['body']));
    //Up/down or remove vote
    if (isset($_POST['vote']))
      {
	echo $_POST['vote'];
      }
    /**
    * Add comment
    */
    if (isset($_GET['step']) && $_GET['step'] == 'add')
      {
        addcomments($_GET['proposal'], 'prop_comments', 'proposalid');
      }

    /**
    * Delete comment
    */
    if (isset($_GET['step']) && $_GET['step'] == 'delete')
      {
        deletecomments('prop_comments');
      }
  }

//Show main menu
if ((!isset($_GET['proposal'])) && (!isset($_GET['add'])))
  {
    $arrIds = array();
    $arrTitles = array();
    $arrAuthors = array();
    $arrStatuses = array();
    $arrVotesup = array();
    $arrVotesdown = array();
    $objProposals = $db->Execute("SELECT * FROM `proposals` WHERE state<>'Dodana'");
    while (!$objProposals->EOF)
      {
	$arrIds[] = $objProposals->fields['id'];
	$arrTitles[] = $objProposals->fields['title'];
	$objName = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$objProposals->fields['author']);
	$arrAuthors[] = $objName->fields['user'];
	$objName->Close();
	$arrStatuses[] = $objProposals->fields['state'];
	if (strlen($objProposals->fields['voters']) > 0)
	  {
	    $arrTmp = explode(";", $objProposals->fields['voters']);
	    $arrVotesup[] = count($arrTmp);
	  }
	else
	  {
	    $arrVotesup[] = 0;
	  }
	if (strlen($objProposals->fields['voters2']) > 0)
	  {
	    $arrTmp = explode(";", $objProposals->fields['voters2']);
	    $arrVotesdown[] = count($arrTmp);
	  }
	else
	  {
	    $arrVotesdown[] = 0;
	  }
	$objProposals->MoveNext();
      }
    $objProposals->Close();
    $smarty->assign(array("Pinfo" => "Witaj na placu myślicieli. Tutaj możesz zgłosić własną propozycję dotyczącą zmian w królestwie, bądź też dyskutować i głosować nad pomysłami innych.",
			  "PIds" => $arrIds,
			  "Titles" => $arrTitles,
			  "Authors" => $arrAuthors,
			  "Statuses" => $arrStatuses,
			  "Votesup" => $arrVotesup,
			  "Votesdown" => $arrVotesdown,
			  "Padd" => "Dodaj nową propozycję"));
    $_GET['proposal'] = 0;
  }

$smarty->assign(array("Ptitle" => "Krótki opis",
		      "Pup" => "Za",
		      "Pdown" => "Przeciw",
		      "Pstatus" => "Status",
		      "Pauthor" => "Autor",
		      "Aback" => "Wróć",
		      "Proposal" => $_GET['proposal']));
$smarty->display('proposals.tpl');
require_once("includes/foot.php");
?>