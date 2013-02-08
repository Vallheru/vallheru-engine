<?php
/**
 *   File functions:
 *   Account options - change avatar, email, password and nick
 *
 *   @name                 : account.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 08.02.2013
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

$title = 'Opcje konta';
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/account.php");

/**
* Assign variable to template
*/
$smarty -> assign("Avatar", '');


if (isset($_GET['view']))
  {
    /**
     * Links
     */
    if ($_GET['view'] == 'links')
      {
	$objLinks = $db -> Execute("SELECT `id`, `file`, `text`, `number` FROM `links` WHERE `owner`=".$player -> id." ORDER BY `number` ASC");
	$arrId = array(0);
	$arrFile = array();
	$arrText = array();
	$arrNumber = array();
	$i = 0;
	while (!$objLinks -> EOF)
	  {
	    $arrId[$i] = $objLinks -> fields['id'];
	    $arrFile[$i] = $objLinks -> fields['file'];
	    $arrText[$i] = $objLinks -> fields['text'];
	    $arrNumber[$i] = $objLinks->fields['number'];
	    $objLinks -> MoveNext();
	    $i++;
	  }
	$objLinks -> Close();
	if (!isset($_GET['lid']))
	  {
	    $strFormaction = A_ADD;
	    $intLinkid = 0;
	  }
        else
	  {
	    $_GET['lid'] = intval($_GET['lid']);
	    if ($_GET['lid'] == 0)
	      {
		$strFormaction = A_ADD;
	      }
	    else
	      {
		$strFormaction = A_EDIT;
	      }
	    $intLinkid = $_GET['lid'];
	  }
	$smarty -> assign(array("Linksinfo" => LINKS_INFO,
				"Tfile" => T_LINK,
				"Tname" => T_NAME,
				"Tnumber" => "Numer",
				"Tactions" => T_ACTIONS,
				"Adelete" => A_DELETE,
				"Aedit" => A_EDIT,
				"Aform" => $strFormaction,
				"Linksid" => $arrId,
				"Linksfile" => $arrFile,
				"Linkstext" => $arrText,
				"Linkid" => $intLinkid,
				"Linksnumber" => $arrNumber,
				"Linkfile" => '',
				"Linkname" => '',
				"Linknumber" => ''));
	
	if (isset($_GET['step']))
	  {
	    /**
	     * Add/edit links
	     */
	    if ($_GET['step'] == 'edit')
	      {
		if (!isset($_GET['action']) && $_GET['lid'] > 0)
		  {
		    $objLink = $db -> Execute("SELECT `id`, `file`, `text`, `number` FROM `links` WHERE `id`=".$_GET['lid']." AND `owner`=".$player -> id);
		    if (!$objLink -> fields['id'])
		      {
			error(NOT_YOUR);
		      }
		    $smarty -> assign(array("Linkfile" => $objLink -> fields['file'],
					    "Linkname" => $objLink -> fields['text'],
					    "Linknumber" => $objLink->fields['number']));
		    $objLink -> Close();
		  }
		if (isset($_GET['action']) && $_GET['action'] == 'change')
		  {
		    $strFile = htmlspecialchars($_POST['linkadress'], ENT_QUOTES);
		    $strText = htmlspecialchars($_POST['linkname'], ENT_QUOTES);
		    if (empty($strFile) || empty($strText) || !isset($_POST['linknumber']))
		      {
			error(EMPTY_FIELDS);
		      }
		    $_POST['linknumber'] = intval($_POST['linknumber']);
		    $arrForbidden = array('config.php', 'session.php', 'reset.php', 'resets.php', 'quest', 'portal');
		    foreach ($arrForbidden as $strForbidden)
		      {
			$intPos = strpos($strFile, $strForbidden);
			if ($intPos !== false)
			  {
			    error(ERROR);
			  }
		      }
		    if ($_GET['lid'] > 0)
		      {
			$db -> Execute("UPDATE `links` SET `file`='".$strFile."', `text`='".$strText."', `number`=".$_POST['linknumber']." WHERE `id`=".$_GET['lid']." AND `owner`=".$player -> id);
			$strMessage = YOU_CHANGE;
		      }
		    else
		      {
			$db -> Execute("INSERT INTO `links` (`owner`, `file`, `text`, `number`) VALUES(".$player -> id.", '".$strFile."', '".$strText."', ".$_POST['linknumber'].")");
			$strMessage = YOU_ADD;
		      }
		    error($strMessage);
		  }
	      }

	    /**
	     * Delete links
	     */
	    elseif ($_GET['step'] == 'delete')
	      {
		$objLink = $db -> Execute("SELECT `id` FROM `links` WHERE `id`=".$_GET['lid']." AND `owner`=".$player -> id);
		if (!$objLink -> fields['id'])
		  {
		    error(NOT_YOUR);
		  }
		$objLink -> Close();
		$db -> Execute("DELETE FROM `links` WHERE `id`=".$_GET['lid']." AND `owner`=".$player -> id);
		error(LINK_DELETED);
	      }
	  }
      }

    /**
     * Bugtrack
     */
    elseif ($_GET['view'] == 'bugtrack')
      {
	if (!isset($_GET['bug']))
	  {
	    $arrBugs = $db->GetAll("SELECT `id`, `title`, `resolution`, `location` FROM `bugreport` ORDER BY `id` ASC");
	    foreach ($arrBugs as &$arrBug)
	      {
		switch ($arrBug['resolution'])
		  {
		  case 0:
		    $arrBug['status'] = 'Oczekuje na sprawdzenie';
		    break;
		  case 2:
		  case 3:
		    $arrBug['status'] = 'Wymaga więcej informacji';
		    break;
		  default:
		    break;
		  }
	      }
	    $smarty -> assign(array("Bugtype" => "Status błędu",
				    "Bugloc" => BUG_LOC,
				    "Bugid" => BUG_ID,
				    "Bugname" => BUG_NAME,
				    "Bugtrackinfo" => BUGTRACK_INFO,
				    "Bugs" => $arrBugs,
				    "Bug" => 0));
	  }
	else
	  {
	    checkvalue($_GET['bug']);
	    $arrBug = $db->GetRow("SELECT * FROM `bugreport` WHERE `id`=".$_GET['bug']);
	    if (!$arrBug['id'])
	      {
		error('Nie ma takiego błędu.');
	      }
	    switch ($arrBug['resolution'])
	      {
	      case 0:
		$arrBug['status'] = 'Oczekuje na sprawdzenie';
		break;
	      case 2:
	      case 3:
		$arrBug['status'] = 'Wymaga więcej informacji';
		break;
	      default:
		break;
	      }
	    $smarty -> assign("Amount", '');
    
	    require_once('includes/comments.php');

	    /**
	     * Add comment
	     */
	    if (isset($_POST['body']))
	      {
		addcomments($_GET['bug'], 'bug_comments', 'bugid');
	      }
	    
	    /**
	     * Delete comment
	     */
	    if (isset($_GET['action']) && $_GET['action'] == 'delete')
	      {
		deletecomments('bug_comments');
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
	    $amount = displaycomments($_GET['bug'], 'bugreport', 'bug_comments', 'bugid');
	    $smarty -> assign(array("Tauthor" => $arrAuthor,
				    "Taid" => $arrAuthorid,
				    "Tbody" => $arrBody,
				    "Amount" => $amount,
				    "Cid" => $arrId,
				    "Tdate" => $arrDate,
				    "Nocomments" => "Brak komentarzy",
				    "Addcomment" => "Dodaj komentarz",
				    "Adelete" => "Skasuj",
				    "Aadd" => "Dodaj",
				    "Aback" => "Wróć",
				    "Tpages" => $intPages,
				    "Tpage" => $intPage,
				    "Fpage" => "Idź do strony:",
				    "Faction" => "account.php?view=bugtrack&amp;bug=".$_GET['bug'],
				    "Bug" => $_GET['bug'],
				    "Bugtype" => "Status błędu",
				    "Bugtext" => "Treść",
				    "Bugloc" => BUG_LOC,
				    "Bugid" => BUG_ID,
				    "Bugname" => BUG_NAME,
				    "Bug2" => $arrBug,
				    "Rank" => $player->rank,
				    "Abold" => "",
				    "Writed" => "napisał(a)"));
	  }
      }

    /**
     * Bug report
     */
    elseif ($_GET['view'] == 'bugreport')
      {
	if (isset($_GET['loc']))
	  {
	    $strLoc = $_GET['loc'];
	  }
	else
	  {
	    $strLoc = '';
	  }
	$smarty -> assign(array("Bugloc" => BUG_LOC,
				"Bugdesc" => BUG_DESC,
				"Areport" => A_REPORT,
				"Bugname" => BUG_NAME,
				"Buginfo" => BUG_INFO,
				"Bugtitle" => "Krótkie, jedno zdanie podsumowujące błąd.",
				"Bugdesc2" => "Co trzeba zrobić (krok po kroku) aby wywołać dany błąd?\n1.\n2.\n3.\n\nCo jest efektem wykonania owych kroków?\n\nCo Twoim zdaniem powinno się wydarzyć?\n\nJakiej przeglądarki używasz?\n\nWszelkie dodatkowe informacje jakie możesz przekazać na temat tego błędu proszę wpisać poniżej. Im więcej informacji jest dostarczonych, tym szybciej błąd będzie naprawiony.",
				"Loc" => $strLoc));
	/**
	 * Report bug
	 */
	if (isset($_GET['step']) && $_GET['step'] == 'report')
	  {
	    $arrFields = array($_POST['bugtitle'], $_POST['location'], $_POST['desc']);
	    require_once('includes/bbcode.php');
	    foreach ($arrFields as &$strField)
	      {
		$strField = bbcodetohtml($strField);
		if (preg_match("/\S+/", $strField) == 0)
		  {
		    error(EMPTY_FIELDS);
		  }
	      }
	    $db -> Execute("INSERT INTO `bugreport` (`sender`, `title`, `location`, `desc`) VALUES(".$player -> id.", '".$arrFields[0]."', '".$arrFields[1]."', '".$arrFields[2]."')");
	    $objStaff = $db -> Execute("SELECT `id` FROM `players` WHERE `rank` IN ('Admin', 'Budowniczy')");
	    $strDate = $db -> DBDate($newdate);
	    while (!$objStaff->EOF) 
	      {
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objStaff->fields['id'].", 'Zgłoszono nowy błąd.', ".$strDate.", 'A')") or die($db->ErrorMsg());
		$objStaff->MoveNext();
	      }
	    $objStaff->Close();
	    error(B_REPORTED);
	  }
      }

    /**
     * Display info about changes in game
     */
    elseif ($_GET['view'] == 'changes')
      {
	//Pagination
	$objAmount = $db->Execute("SELECT count(`id`) FROM `changelog`");
	$intPages = ceil($objAmount->fields['count(`id`)'] / 30);
	$objAmount->Close();
	if (!isset($_GET['page']))
	  {
	    $intPage = 1;
	  }
	else
	  {
	    $intPage = $_GET['page'];
	  }

	$objChanges = $db -> SelectLimit("SELECT `author`, `location`, `text`, `date` FROM `changelog` ORDER BY `id` DESC", 30, 30 * ($intPage - 1));
	$arrAuthor = array();
	$arrDate = array();
	$arrText = array();
	$arrLocation = array();
	while (!$objChanges -> EOF)
	  {
	    $arrAuthor[] = $objChanges -> fields['author'];
	    $arrDate[] = $objChanges -> fields['date'];
	    $arrLocation[] = $objChanges -> fields['location'];
	    $arrText[] = $objChanges -> fields['text'];
	    $objChanges -> MoveNext();
	  }
	$objChanges -> Close();
	$smarty -> assign(array("Changesinfo" => CHANGES_INFO,
				"Changeloc" => CHANGE_LOC,
				"Changeauthor" => $arrAuthor,
				"Changedate" => $arrDate,
				"Changelocation" => $arrLocation,
				"Tpages" => $intPages,
				"Tpage" => $intPage,
				"Fpage" => "Idź do strony:",
				"Changetext" => $arrText));
      }

    /**
     * Additional options
     */
    elseif ($_GET['view'] == 'options')
      {
	//Battlelogs
	if ($player->settings['battlelog'] == 'Y')
	  {
	    $strChecked = 'checked="checked"';
	    $strChecked3 = 'checked="checked"';
	    $strChecked4 = '';
	    $strChecked5 = '';
	  }
	else
	  {
	    $strChecked = '';
	    $strChecked3 = '';
	    $strChecked4 = '';
	    $strChecked5 = '';
	    if ($player->settings['battlelog'] == 'A')
	      {
		$strChecked4 = 'checked="checked"';
		$strChecked = 'checked="checked"';
	      }
	    elseif ($player->settings['battlelog'] == 'D')
	      {
		$strChecked5 = 'checked="checked"';
		$strChecked = 'checked="checked"';
	      }
	  }
	//Graph bars in text mode
	if ($player->settings['graphbar'] == 'Y')
	  {
	    $strChecked2 = 'checked="checked"';
	  }
        else
	  {
	    $strChecked2 = '';
	  }
	//Autodrink potions
	$strChecked7 = '';
	$strChecked8 = '';
	$strChecked9 = '';
	$strChecked6 = '';
	if ($player->settings['autodrink'] != 'N')
	  {
	    $strChecked6 = 'checked="checked"';
	    switch ($player->settings['autodrink'])
	      {
	      case 'H':
		$strChecked7 = 'checked="checked"';
		break;
	      case 'M':
		$strChecked8 = 'checked="checked"';
		break;
	      case 'A':
		$strChecked9 = 'checked="checked"';
		break;
	      default:
		break;
	      }
	  }
	//Enable/disable room invitations
	if ($player->settings['rinvites'] == 'N')
	  {
	    $strChecked10 = 'checked="checked"';
	  }
	else
	  {
	    $strChecked10 = '';
	  }
	//Old chat design
	if (!isset($player->settings['oldchat']) || $player->settings['oldchat'] == 'N')
	  {
	    $strChecked11 = '';
	  }
	else
	  {
	    $strChecked11 = 'checked="checked"';
	  }
	//Show avatar
	if (!isset($player->settings['avatar']) || $player->settings['avatar'] == 'N')
	  {
	    $strChecked12 = '';
	  }
	else
	  {
	    $strChecked12 = 'checked="checked"';
	  }
	$smarty -> assign(array("Toptions" => T_OPTIONS,
				"Tbattlelog" => T_BATTLELOG,
				"Tgraphbar" => T_GRAPHBAR,
				"Anext" => A_NEXT,
				"Tonlyattack" => "Kiedy Ty atakowałeś(aś)",
				"Tonlyattacked" => "Kiedy zostałeś(aś) zaatakowany(a)",
				"Talways" => "Zawsze (po ataku i zaatakowaniu)",
				"Tautodrink" => "Automatycznie używaj mikstur po każdej walce",
				"Tautoheal" => "Do leczenia obrażeń",
				"Tautomana" => "Do regeneracji punktów magii",
				"Tautoall" => "Do regeneracji punktów życia oraz magii",
				"Trinvites" => "Zablokuj zaproszenia do karczmy",
				"Toldchat" => "Stary układ karczmy",
				"Tavatar" => "Wyświetlanie awatara postaci w trybie tekstowym",
				"Checked" => $strChecked,
				"Checked3" => $strChecked3,
				"Checked4" => $strChecked4,
				"Checked5" => $strChecked5,
				"Checked6" => $strChecked6,
				"Checked7" => $strChecked7,
				"Checked8" => $strChecked8,
				"Checked9" => $strChecked9,
				"Checked2" => $strChecked2,
				"Checked10" => $strChecked10,
				"Checked11" => $strChecked11,
				"Checked12" => $strChecked12));
	if (isset($_GET['step']) && $_GET['step'] == 'options')
	  {
	    if (isset($_POST['battlelog']))
	      {
		if (!isset($_POST['battle']))
		  {
		    $_POST['battle'] = 'Y';
		  }
		$arrOptions = array('A', 'D', 'Y');
		if (!in_array($_POST['battle'], $arrOptions))
		  {
		    error(ERROR);
		  }
		$player->settings['battlelog'] = $_POST['battle'];
	      }
	    else
	      {
		$player->settings['battlelog'] = 'N';
	      }
	    if (isset($_POST['graphbar']))
	      {
		$player->settings['graphbar'] = 'Y';
	      }
	    else
	      {
		$player->settings['graphbar'] = 'N';
	      }
	    if (isset($_POST['autodrink']))
	      {
		$arrOptions = array('H', 'M', 'A');
		if (!in_array($_POST['drink'], $arrOptions))
		  {
		    error(ERROR);
		  }
		$player->settings['autodrink'] = $_POST['drink'];
	      }
	    else
	      {
		$player->settings['autodrink'] = 'N';
	      }
	    if (isset($_POST['rinvites']))
	      {
		$player->settings['rinvites'] = 'N';
	      }
	    else
	      {
		$player->settings['rinvites'] = 'Y';
	      }
	    if (isset($_POST['oldchat']))
	      {
		$player->settings['oldchat'] = 'Y';
	      }
	    else
	      {
		$player->settings['oldchat'] = 'N';
	      }
	    if (isset($_POST['avatar']))
	      {
		$player->settings['avatar'] = 'Y';
	      }
	    else
	      {
		$player->settings['avatar'] = 'N';
	      }
	    message('success', A_SAVED);
	  }
      }

    /**
     * Account freeze
     */
    elseif ($_GET['view'] == 'freeze')
      {
	$smarty -> assign(array("Freezeinfo" => FREEZE_INFO,
				"Howmany" => HOW_MANY,
				"Afreeze2" => A_FREEZE2));
	if (isset($_GET['step']) && $_GET['step'] == 'freeze')
	  {
	    checkvalue($_POST['amount']);
	    if ($_POST['amount'] > 21)
	      {
		error(TOO_MUCH);
	      }
	    $db -> Execute("UPDATE players SET freeze=".$_POST['amount']." WHERE id=".$player -> id);
	    $smarty -> assign("Message", YOU_BLOCK.$_POST['amount'].NOW_EXIT);
	    session_unset();
	    session_destroy();
	  }
      }

    /**
     * Assign immunited
     */
    elseif ($_GET['view'] == "immu") 
      {
	$smarty -> assign(array("Immuinfo" => IMMU_INFO,
				"Yes" => YES,
				"No" => NO));
	if (isset ($_GET['step']) && $_GET['step'] == 'yes') 
	  {
	    if ($player -> immunited == 'Y') 
	      {
		error (YOU_HAVE);
	      }
	    if ($player -> clas == '') 
	      {
		error (YOU_NOT_CLASS);
	      }
	    $db -> Execute("UPDATE players SET immu='Y' WHERE id=".$player -> id);
	    $smarty -> assign(array("Immuselect" => IMMU_SELECT,
				    "Here" => HERE,
				    "Immuselect2" => IMMU_SELECT2));
	  }
      }

    /**
     * Player reset
     */
    elseif ($_GET['view'] == "reset") 
      {
	$smarty -> assign(array("Resetinfo" => RESET_INFO,
				"Allreset" => "Całkowity reset",
				"Partreset" => "Częściowy reset",
				"Areset" => "Resetuj postać"));
	if (isset ($_GET['step']) && $_GET['step'] == 'make') 
	  {
	    if ($_POST['reset'] != 'A' && $_POST['reset'] != 'P')
	      {
		error(ERROR);
	      }
	    $code = rand(1,1000000);
	    $message = MESSAGE1." ".$gamename." (".$player -> user." ".ID.": ".$player -> id.") ".MESSAGE2." ".$gameadress."/preset.php?id=".$player -> id."&code=".$code." ".MESSAGE3." ".$gameadress."/preset.php?id=".$player -> id." ".MESSAGE4." ".$adminname;
	    $adress = $_SESSION['email'];
	    $subject = MSG_TITLE." ".$gamename;
	    require_once('mailer/mailerconfig.php');
	    if (!$mail -> Send()) 
	      {
		error(E_MAIL.":<br /> ".$mail -> ErrorInfo);
	      }
	    $db -> Execute("INSERT INTO `reset` (`player`, `code`, `type`) VALUES(".$player -> id.",".$code.", '".$_POST['reset']."')") or error(E_DB);
	    $smarty -> assign("Resetselect", RESET_SELECT);
	  }
      }

    /**
     * Avatar options
     */
    elseif ($_GET['view'] == "avatar") 
      {
	$smarty -> assign(array("Avatarinfo" => AVATAR_INFO,
				"Delete" => A_DELETE,
				"Afilename" => FILE_NAME,
				"Aselect" => A_SELECT));
	$file = 'avatars/'.$player -> avatar;
	if (is_file($file)) 
	  {
	    $smarty -> assign (array ("Value" => $player -> avatar, "Avatar" => $file));
	  }
	if (isset($_GET['step']))
	  {
	    //Delete avatar
	    if ($_GET['step'] == 'usun') 
	      {
		if ($_POST['av'] != $player -> avatar) 
		  {
		    error(ERROR);
		  }
		$plik = 'avatars/'.$_POST['av'];
		if (is_file($plik)) 
		  {
		    unlink($plik);
		    $db -> Execute("UPDATE players SET avatar='' WHERE id=".$player -> id) or error(E_DB);
		    error (DELETED.". <a href=\"account.php?view=avatar\">".REFRESH."</a><br />");
		  } 
		else 
		  {
		    error (NO_FILE);
		  }
	      }
	    //Add avatar
	    elseif ($_GET['step'] == 'dodaj') 
	      {
		if (!isset($_FILES['plik']['name'])) 
		  {
		    error(NO_NAME);
		  }
		if (isset($_FILES['plik']['error']))
		  {
		    switch ($_FILES['plik']['error'])
		      {
		      case 1:
		      case 2:
			$strMessage = "Ten avatar zajmuje za dużo miejsca (maksymalnie 30kB)!";
			break;
		      case 3:
			$strMessage = 'Niekompletny avatar. Widać coś było nie tak w trakcie przesyłania na serwer. Proszę, prześlij ponownie.';
			break;
		      case 4:
			$strMessage = 'Nie wysłano jakiegokolwiek avatara.';
			  break;
		      default:
			break;
		      }
		    if (isset($strMessage))
		      {
			error($strMessage);
		      }
		  }
		$plik = $_FILES['plik']['tmp_name'];
		$nazwa = $_FILES['plik']['name'];
		$typ = $_FILES['plik']['type'];
		if ($_FILES['plik']['size'] > 30720)
		  {
		    error("Ten avatar zajmuje za dużo miejsca (maksymalnie 30kB)!");
		  }
		$liczba = rand(1,1000000);
		switch ($typ)
		  {
		  case 'image/pjpeg':
		  case 'image/jpeg':
		    $newname = md5($liczba).'.jpg';
		    break;
		  case 'image/gif':
		    $newname = md5($liczba).'.gif';
		    break;
		  case 'image/png':
		    $newname = md5($liczba).'.png';
		    break;
		  default:
		    error (BAD_TYPE);
		    break;
		  }
		$miejsce = 'avatars/'.$newname;
		if (is_uploaded_file($plik)) 
		  {
		    if (!move_uploaded_file($plik,$miejsce)) 
		      {
			error (NOT_COPY);
		      }
		  } 
		else 
		  {
		    error (ERROR);
		  }
		$db -> Execute("UPDATE players SET avatar='".$newname."' WHERE id=".$player -> id) or error($db->ErrorMsg());
		message('success', LOADED."! <a href=\"account.php?view=avatar\">".REFRESH."</a><br />");
	      }
	  }
      }

    /**
     * Change nick for player
     */
    elseif ($_GET['view'] == "name") 
      {
	$smarty -> assign(array("Change" => CHANGE,
				"Myname" => MY_NAME));
	if (isset($_GET['step']) && $_GET['step'] == "name") 
	  {
	    if (empty ($_POST['name'])) 
	      {
		error (EMPTY_NAME);
	      } 
	    $_POST['name'] = str_replace("'","",strip_tags($_POST['name']));
	    $_POST['name'] = str_replace("&nbsp;", "", $_POST['name']);
	    $_POST['name'] = trim($_POST['name']);
	    if ($_POST['name'] == 'Admin' || $_POST['name'] == 'Staff' || empty($_POST['name']) || (preg_match("/\S+/", $_POST['name']) == 0)) 
	      {
		error (ERROR);
	      } 	
	    $query = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `user`='".$_POST['name']."' AND `id`!=".$player->id);
	    $dupe = $query -> fields['count(`id`)'];
	    $query -> Close();
	    if ($dupe > 0) 
	      {
		error (NAME_BLOCK);
	      } 
	    $db -> Execute("UPDATE `players` SET `user`='".$_POST['name']."' WHERE `id`=".$player -> id);
	    error (YOU_CHANGE." <b>".$_POST['name']."</b>.");
	  }
      }

    /**
     * Change password to account
     */
    elseif ($_GET['view'] == "pass") 
      {
	$smarty -> assign(array("Passinfo" => PASS_INFO,
				"Oldpass" => OLD_PASS,
				"Newpass" => NEW_PASS,
				"Change" => CHANGE));
	if (isset($_GET['step']) && $_GET['step'] == "cp") 
	  {
	    if (empty ($_POST['np']) || empty($_POST['cp'])) 
	      {
		error (EMPTY_FIELDS);
	      }
	    require_once('includes/verifypass.php');
	    verifypass($_POST['np'],'account');     
	    $_POST['np'] = str_replace("'","",strip_tags($_POST['np']));
	    $_POST['cp'] = str_replace("'","",strip_tags($_POST['cp']));
	    $db -> Execute("UPDATE players SET pass=MD5('".$_POST['np']."') WHERE pass=MD5('".$_POST['cp']."') AND id=".$player -> id);
	    $_SESSION['pass'] = MD5($_POST['np']);       
	    error (YOU_CHANGE." ".$_POST['cp']." ".ON." ".$_POST['np']);
	  }
      }

    /**
     * Profile edit
     */
    elseif ($_GET['view'] == "profile") 
      {
	require_once('includes/bbcode.php');
	$profile = htmltobbcode($player -> profile);
	$smarty -> assign(array("Profileinfo" => PROFILE_INFO,
				"Newprofile" => NEW_PROFILE,
				"Profile2" => $profile,
				"Change" => CHANGE,
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
	if (isset($_GET['step']) && $_GET['step'] == "profile") 
	  {
	    if (empty ($_POST['profile'])) 
	      {
		error (EMPTY_FIELDS);
	      }
	    $_POST['profile'] = bbcodetohtml($_POST['profile']);
	    $strProfile = $db -> qstr($_POST['profile'], get_magic_quotes_gpc());
	    $db -> Execute("UPDATE `players` SET `profile`=".$strProfile." WHERE `id`=".$player->id);
	    $smarty -> assign (array("Profile" => $_POST['profile'],
				     "Newprofile2" => NEW_PROFILE2,
				     "Profile2" => htmltobbcode($_POST['profile'])));
	  }
      }

    /**
     * Email and comunicators edit
     */
    elseif ($_GET['view'] == 'eci') 
      {
	$arrComname = array(COMM1, COMM2, COMM3, T_DELETE);
	$arrComlink = array(COMLINK1, COMLINK2, COMLINK3, T_DELETE);
	$smarty -> assign(array("Oldemail" => OLD_EMAIL,
				"Newemail" => NEW_EMAIL,
				"Newgg" => NEW_GG,
				"Change" => CHANGE,
				"Tcommunicator" => T_COMMUNICATOR,
				"Tcom" => $arrComname,
				"Comm" => $arrComlink));
	if (isset($_GET['step']))
	  {
	    /**
	     * Change communicator
	     */
	    if ($_GET['step'] == "gg") 
	      {
		$_POST['gg'] = str_replace("'", "", strip_tags($_POST['gg']));
		$intKey = array_search($_POST['communicator'], $arrComlink);
		if ($intKey === 0)
		  {
		    checkvalue($_POST['gg']);
		  }
		if ($intKey < 3)
		  {
		    if (empty($_POST['gg']))
		      {
			error(EMPTY_FIELDS);
		      }
		    $strCom = $arrComname[$intKey].": <a href=\"".$_POST['communicator'].$_POST['gg']."\">".$_POST['gg']."</a>";
		    $query= $db -> Execute("SELECT count(`id`) FROM `players` WHERE `gg`='".$strCom."'");
		    $dupe = $query -> fields['count(`id`)'];
		    $query -> Close();
		    if ($dupe > 0) 
		      {
			error(GG_BLOCK);
		      }
		  }
		else
		  {
		    $strCom = '';
		  }
		$db -> Execute("UPDATE `players` SET `gg`='".$strCom."' WHERE `id`=".$player -> id) or error(E_DB);
		if ($intKey < 3)
		  {
		    error(YOU_CHANGE.$arrComname[$intKey].".");
		  }
		else
		  {
		    error(YOU_DELETE);
		  }
	      }

	    /**
	     * Change email
	     */
	    if ($_GET['step'] == "ce") 
	      {
		if (empty($_POST["ne"]) || empty($_POST['ce'])) 
		  {
		    error(EMPTY_FIELDS);
		  }
		$_POST['ne'] = mysql_escape_string(strip_tags($_POST['ne']));
		$_POST['ce'] = mysql_escape_string(strip_tags($_POST['ce']));
		require_once('includes/verifymail.php');
		if (MailVal($_POST['ne'], 2)) 
		  {
		    error(BAD_EMAIL);
		  }       
		$query = $db -> Execute("SELECT `id` FROM `players` WHERE `email`='".$_POST['ne']."'");    
		if ($query -> fields['id']) 
		  {
		    error(EMAIL_BLOCK);
		  }
		$query -> Close();
		$intNumber = substr(md5(uniqid(rand(), true)), 3, 9);
		$strLink = $gameadress."/index.php?step=newemail&code=".$intNumber."&email=".$_POST['ne'];
		$adress = $_POST['ne'];
		$message = MESSAGE_PART1.$gamename."\n".MESSAGE_PART2."\n".$strLink."\n".MESSAGE_PART3." ".$gamename."\n".$adminname;
		$subject = MESSAGE_SUBJECT.$gamename;
		require_once('mailer/mailerconfig.php');
		if (!$mail -> Send()) 
		  {
		    $smarty -> assign ("Error", MESSAGE_NOT_SEND." ".$mail -> ErrorInfo);
		    $smarty -> display ('error.tpl');
		    exit;
		  }
		$db -> Execute("INSERT INTO `lost_pass` (`number`, `email`, `id`, `newemail`) VALUES('".$intNumber."', '".$_POST['ce']."', ".$player -> id.", '".$_POST['ne']."')") or $db -> ErrorMsg();
		error(YOU_CHANGE);
	      }
	  }
      }

    /**
     * Graphic style change
     */
    elseif ($_GET['view'] == 'style') 
      {
	/**
	 * Text style choice
	 */
	$path = 'css/';
	$dir = opendir($path);
	$arrname = array();
	while ($file = readdir($dir)) 
	  {
	    $strExt = pathinfo($file, PATHINFO_EXTENSION);
	    if ($strExt == "css")
	      {
		$arrname[] = $file;
	      }
	  }
	closedir($dir);    
	/**
	 * Check avaible layouts
	 */    
	$path = 'templates/';
	$dir = opendir($path);
	$arrname1 = array();
	while ($file = readdir($dir))
	  {
	    if (strrchr($file, '.') === FALSE)
	      {
		$arrname1[] = $file;
	      }
	  }
	closedir($dir);
	/**
	 * Assign variables to template
	 */
	$smarty -> assign(array("Sselect" => S_SELECT,
				"Textstyle" => TEXT_STYLE,
				"Graphstyle" => GRAPH_STYLE,
				"Graphstyle2" => GRAPH_STYLE2,
				"Youchange" => YOU_CHANGE,
				"Stylename" => $arrname,
				"Refresh" => REFRESH,
				"Layoutname" => $arrname1));
	if (isset($_GET['step']))
	  {
	    /**
	     * If player choice text style
	     */
	    if ($_GET['step'] == 'style') 
	      {
		if (!isset($_POST['newstyle']))
		  {
		    error(ERROR);
		  }
		$_POST['newstyle'] = htmlspecialchars($_POST['newstyle'], ENT_QUOTES);
		if ($player->settings['graphic'] != '')
		  {
		    $player->settings['graphic'] = '';
		  }
		$player->settings['style'] = $_POST['newstyle'];
	      }
	    /**
	     * If player choice graphic layout
	     */
	    elseif ($_GET['step'] == 'graph')
	      {
		if (!isset($_POST['graphserver']))
		  {
		    error(ERROR);
		  }
		$_POST['graphserver'] = htmlspecialchars($_POST['graphserver'], ENT_QUOTES);
		$player->settings['graphic'] = $_POST['graphserver'];
	      }
	  }
      }
    
    /**
     * Show last gained Vallars
     */
    elseif ($_GET['view'] == 'vallars')
      {
	//Pagination
	$objAmount = $db->Execute("SELECT count(`owner`) FROM `vallars`");
	$intPages = ceil($objAmount->fields['count(`owner`)'] / 30);
	$objAmount->Close();
	if (!isset($_GET['page']))
	  {
	    $intPage = 1;
	  }
	else
	  {
	    $intPage = $_GET['page'];
	  }

	$objHist = $db->SelectLimit("SELECT * FROM `vallars` ORDER BY `vdate` DESC", 30, 30 * ($intPage - 1));
	$arrDate = array();
	$arrReason = array();
	$arrAmount = array();
	$arrOwner = array();
	$arrOwnerID = array();
	while (!$objHist->EOF)
	  {
	    $tmpArr = explode(" ", $objHist->fields['vdate']);
	    $arrDate[] = $tmpArr[0];
	    $arrReason[] = $objHist->fields['reason'];
	    $arrAmount[] = $objHist->fields['amount'];
	    $arrOwnerid[] = $objHist->fields['owner'];
	    $objOwner = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$objHist->fields['owner']);
	    $arrOwner[] = $objOwner->fields['user'];
	    $objOwner->Close();
	    $objHist->MoveNext();
	  }
	$objHist->Close();
	$smarty->assign(array("Info" => "Poniżej znajduje się lista ostatnich 30 akcji przyznania Vallarów za wkład w rozwój gry.",
			      "Tamount" => "Ilość przyznanych Vallarów",
			      "Treason" => "Uzasadnienie",
			      "Id" => "ID",
			      "Tgranted" => "Nagrodzony(a)",
			      "Tpages" => $intPages,
			      "Tpage" => $intPage,
			      "Fpage" => "Idź do strony:",
			      "Date" => $arrDate,
			      "Owner" => $arrOwner,
			      "Ownerid" => $arrOwnerid,
			      "Amount" => $arrAmount,
			      "Reason" => $arrReason));
      }

    /**
     * Set observed forums
     */
    elseif ($_GET['view'] == 'forums')
      {
	/**
	 * Display categories viewable for all
	 */
	$cat = $db -> Execute("SELECT `id`, `name` FROM `categories` WHERE `perm_visit` LIKE 'All;' ORDER BY `id` ASC");
	$arrid = array();
	$arrname = array();
	$arrChecked = array();
	$arrPlchecked = explode(",", $player->settings['forumcats']);
	while (!$cat -> EOF) 
	  {
	    $arrid[] = $cat -> fields['id'];
	    $arrname[] = $cat -> fields['name'];
	    if (in_array($cat->fields['id'], $arrPlchecked))
	      {
		$arrChecked[] = 'checked="checked"';
	      }
	    else
	      {
		$arrChecked[] = '';
	      }
	    $cat -> MoveNext();
	  }
	$cat -> Close();
	/**
	 * Display categories with permission to view
	 */
	if ($player -> rank == 'Admin')
	  {
	    $strPermission = '%';
	  }
	else
	  {
	    $strPermission = $player -> rank;
	  }
	$cat = $db -> Execute("SELECT `id`, `name` FROM `categories` WHERE `perm_visit` LIKE '%".$strPermission."%' ORDER BY `id` ASC");
	while (!$cat -> EOF) 
	  {
	    if (in_array($cat -> fields['id'], $arrid))
	      {
		$cat -> MoveNext();
		continue;
	      }
	    $arrid[] = $cat -> fields['id'];
	    $arrname[] = $cat -> fields['name'];
	    if (in_array($cat->fields['id'], $arrPlchecked))
	      {
		$arrChecked[] = 'checked="checked"';
	      }
	    else
	      {
		$arrChecked[] = '';
	      }
	    $cat -> MoveNext();
	  }
	$cat -> Close();
	if ($player->settings['forumcats'] == 'All')
	  {
	    $strAllchecked = 'checked="checked"';
	    $strSchecked = '';
	  }
	else
	  {
	    $strAllchecked = '';
	    $strSchecked = 'checked="checked"';
	  }
	$smarty -> assign(array("Id" => $arrid, 
				"Name" => $arrname,
				"Achecked" => $strAllchecked,
				"Schecked" => $strSchecked,
				"Checked" => $arrChecked,
				"Aset" => "Ustaw",
				"Oall" => "Wszystkie",
				"Oselected" => "Wybrane poniżej",
				"Info" => "Poniżej możesz wybrać, które kategorie na forum chcesz obserwować. Jeżeli w wybranej kategorii znajdą się nowe wpisy, ich liczba zostanie podana przy odnośniku do forum."));
	if (isset($_GET['step']) && $_GET['step'] == 'set')
	  {
	    $arrSet = array();
	    foreach ($arrid as $intFid)
	      {
		if (isset($_POST[$intFid]))
		  {
		    $arrSet[] = $intFid;
		  }
	      }
	    if (count($arrSet) == 0 && $_POST['forums'] == 'Selected')
	      {
		error("Wybierz które katgorie chcesz subskrybować");
	      }
	    if ($_POST['forums'] == 'All')
	      {
		$strCats = 'All';
	      }
	    else
	      {
		$strCats = implode(",", $arrSet);
	      }
	    $player->settings['forumcats'] = $strCats;
	  }
      }

    /**
     * Set roleplay profile.
     */
    elseif ($_GET['view'] == 'roleplay')
      {
	$objProfile = $db->Execute("SELECT `roleplay`, `ooc`, `shortrpg` FROM `players` WHERE `id`=".$player->id);
	require_once('includes/bbcode.php');
	$smarty->assign(array("Info" => "Tutaj możesz ustawić swój profil fabularny. Używany jest on jedynie do sesji z innymi graczami, jest czymś w rodzaju twojej wizytówki. Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i>[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>",
			      "Rprofile" => htmltobbcode($objProfile->fields['roleplay']),
			      "Ooc" => htmltobbcode($objProfile->fields['ooc']),
			      "Shortrp" => $objProfile->fields['shortrpg'],
			      'Rprofileinfo' => 'Tutaj wpisz informacje związane z twoją postacią dla <b>postaci</b> innych graczy, czyli na przykład wygląd itp sprawy.',
			      'Oocinfo' => 'Tutaj wpisz informacje dla innych graczy. Na przykład ogólne zasady na jakich chciałbyś(chciałabyś) tworzyć sesję itd.',
			      'Shortrpinfo' => 'Bardzo krótki (najlepiej dwa, trzy słowa) opis twojej postaci. Zostanie on umieszczony na liście mieszkańców i będzie odnośnikiem do twojego profilu fabularnego. Jeżeli wykasujesz tekst z tego pola, odnośnik na liście mieszkańców do twojego profilu fabularnego również zniknie.',
			      "Aset" => "Ustaw",
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
	if (isset($_GET['step']) && $_GET['step'] == 'set')
	  {
	    if (!isset($_POST['shortrp']))
	      {
		$_POST['shortrp'] = '';
	      }
	    $strProfile = bbcodetohtml($_POST['roleplay']);
	    $strOOC = bbcodetohtml($_POST['ooc']);
	    $strShort = str_replace("'","",strip_tags($_POST['shortrp']));
	    if ($strShort != '' && (strlen($strProfile) == 0 && strlen($strOOC == 0)))
	      {
		error("Wypełnij wszystkie pola!");
	      }
	    $db->Execute("UPDATE `players` SET `roleplay`='".$strProfile."', `ooc`='".$strOOC."', `shortrpg`='".$strShort."' WHERE `id`=".$player->id);
	    $smarty->assign(array("Rprofile" => htmltobbcode($_POST['roleplay']),
				  "Ooc" => htmltobbcode($_POST['ooc'])));
	  }
      }

    /**
     * Manage ignored
     */
    elseif ($_GET['view'] == 'ignored')
      {
	$arrIgnored = $db->GetAll("SELECT `id`, `pid`, `mail`, `inn` FROM `ignored` WHERE `owner`=".$player->id." ORDER BY `pid` ASC");
	foreach ($arrIgnored as &$arrIgnored2)
	  {
	    $objUser = $db->Execute("SELECT `user`, `tribe` FROM `players` WHERE `id`=".$arrIgnored2['pid']);
	    $arrIgnored2['user'] = $arrTags[$objUser->fields['tribe']][0].' '.$objUser->fields['user'].' '.$arrTags[$objUser->fields['tribe']][1];
	    $objUser->Close();
	    if ($arrIgnored2['mail'] == 'Y')
	      {
		$arrIgnored2['mail'] = 'checked="checked"';
	      }
	    else
	      {
		$arrIgnored2['mail'] = '';
	      }
	    if ($arrIgnored2['inn'] == 'Y')
	      {
		$arrIgnored2['inn'] = 'checked="checked"';
	      }
	    else
	      {
		$arrIgnored2['inn'] = '';
	      }
	  }
	$intAmount = count($arrIgnored);
	$smarty->assign(array("Info" => "Tutaj możesz zarządzać osobami ignorowanymi w grze. Możesz maksymalnie posiadać 30 osób ignorowanych. Twoja lista ignorowanych:",
			      "Ignored" => $arrIgnored,
			      "Adelete" => "Skasuj",
			      "Aedit" => "Edytuj",
			      "Iamount" => $intAmount,
			      "Tid" => "ID",
			      "Tplayer" => "Imię",
			      "Tmail" => "Blokada poczty",
			      "Tinn" => "Blokada zaproszeń do pokoju",
			      "Toptions" => "Opcje",
			      "Noignored" => "Nikogo jeszcze nie ignorujesz.",
			      "Aadd" => "Dodaj",
			      "Tadd" => "gracza o ID:",
			      "Tadd2" => "do ignorowanych (automatycznie poczta i zaproszenia)",
			      "Message" => ''));
	//Add ignored
	if (isset($_GET['add']))
	  {
	    if ($intAmount >= 30)
	      {
		error("Twoja lista ignorowanych jest już pełna. Nie możesz dodać do niej nowej osoby.");
	      }
	    if (isset($_GET['pid']))
	      {
		$_POST['pid'] = $_GET['pid'];
	      }
	    checkvalue($_POST['pid']);
	    foreach ($arrIgnored as $arrIgnored2)
	      {
		if ($arrIgnored2['pid'] == $_POST['pid'])
		  {
		    error("Ignorujesz już tego gracza.");
		  }
	      }
	    $db->Execute("INSERT INTO `ignored` (`owner`, `pid`) VALUES (".$player->id.", ".$_POST['pid'].")");
	    $smarty->assign("Message", "Dodałeś wybranego gracza do listy ignorowanych. (<a href=account.php?view=ignored>Odśwież</a>)");
	  }
	//Edit selected ignored
	if (isset($_GET['edit']))
	  {
	    checkvalue($_GET['edit']);
	    $objTest = $db->Execute("SELECT `owner` FROM `ignored` WHERE `id`=".$_GET['edit']);
	    if (!$objTest->fields['owner'])
	      {
		error("Nie ignorujesz tego gracza!");
	      }
	    if ($objTest->fields['owner'] != $player->id)
	      {
		error("To nie jest twój ignorowany");
	      }
	    $objTest->Close();
	    if (isset($_GET['delete']))
	      {
		$db->Execute("DELETE FROM `ignored` WHERE `id`=".$_GET['edit']);
		$smarty->assign("Message", "Usunąłeś wybranego gracza z listy ignorowanych. (<a href=account.php?view=ignored>Odśwież</a>)");
	      }
	    else
	      {
		$strMail = 'N';
		if (isset($_POST['mail'.$_GET['edit']]))
		  {
		    $strMail = 'Y';
		  }
		$strInn = 'N';
		if (isset($_POST['inn'.$_GET['edit']]))
		  {
		    $strInn = 'Y';
		  }
		$db->Execute("UPDATE `ignored` SET `mail`='".$strMail."', `inn`='".$strInn."' WHERE `id`=".$_GET['edit']);
		$smarty->assign("Message", "Edytowałeś wybraną ignorowaną osobę. (<a href=account.php?view=ignored>Odśwież</a>)");
	      }
	  }
      }

    /**
     * Manage contacts
     */
    elseif ($_GET['view'] == 'contacts')
      {
	$arrContacts = $db->GetAll("SELECT `id`, `pid` FROM `contacts` WHERE `owner`=".$player->id." ORDER BY `order`, `pid` ASC");
	foreach ($arrContacts as &$arrContact)
	  {
	    $objUser = $db->Execute("SELECT `user`, `tribe` FROM `players` WHERE `id`=".$arrContact['pid']);
	    $arrContact['user'] = $arrTags[$objUser->fields['tribe']][0].' '.$objUser->fields['user'].' '.$arrTags[$objUser->fields['tribe']][1];
	    $objUser->Close();
	  }
	$intAmount = count($arrContacts);
	$smarty->assign(array("Info" => "Tutaj możesz usuwać bądź ustalać kolejność swoich kontaktów w grze. Możesz posiadać maksymalnie 30 kontaktów. Twoja lista kontaktów:",
			      "Contacts" => $arrContacts,
			      "Aup" => "Wyżej",
			      "Adown" => "Niżej",
			      "Adelete" => "Skasuj",
			      "Camount" => $intAmount,
			      "Tplayer" => "Imię",
			      "Toptions" => "Opcje",
			      "Nocontacts" => "Nie masz jeszcze żadnych kontaktów.",
			      "Aadd" => "Dodaj",
			      "Tadd" => "gracza o ID:",
			      "Tadd2" => "do kontaktów",
			      "Tid" => "ID",
			      "Twrite" => "Napisz wiadomość",
			      "Message" => ''));
	//Add contact
	if (isset($_GET['add']))
	  {
	    if ($intAmount >= 30)
	      {
		error("Twoja lista kontaktów jest już pełna. Nie możesz dodawać nowych kontaktów.");
	      }
	    if (isset($_GET['pid']))
	      {
		$_POST['pid'] = $_GET['pid'];
	      }
	    checkvalue($_POST['pid']);
	    foreach ($arrContacts as $arrContact)
	      {
		if ($arrContact['pid'] == $_POST['pid'])
		  {
		    error("Masz już tego gracza w kontaktach");
		  }
	      }
	    $db->Execute("INSERT INTO `contacts` (`owner`, `pid`) VALUES(".$player->id.", ".$_POST['pid'].")");
	    $smarty->assign("Message", "Dodałeś wybranego gracza do kontaktów. (<a href=account.php?view=contacts>Odśwież</a>)");
	  }
	//Edit selected contact
	if (isset($_GET['edit']))
	  {
	    checkvalue($_GET['edit']);
	    $objTest = $db->Execute("SELECT `owner` FROM `contacts` WHERE `id`=".$_GET['edit']);
	    if (!$objTest->fields['owner'])
	      {
		error("Nie ma takiego kontaktu!");
	      }
	    if ($objTest->fields['owner'] != $player->id)
	      {
		error("To nie jest twój kontakt");
	      }
	    $objTest->Close();
	    if (isset($_GET['delete']))
	      {
		$db->Execute("DELETE FROM `contacts` WHERE `id`=".$_GET['edit']);
		$smarty->assign("Message", "Skasowałeś wybrany kontakt. (<a href=account.php?view=contacts>Odśwież</a>)");
	      }
	    elseif (isset($_GET['up']))
	      {
		if ($intAmount > 1)
		  {
		    $db->Execute("UPDATE `contacts` SET `order`=`order`-1 WHERE `id`=".$_GET['edit']);
		  }
	      }
	    elseif (isset($_GET['down']))
	      {
		if ($intAmount > 1)
		  {
		    $db->Execute("UPDATE `contacts` SET `order`=`order`+1 WHERE `id`=".$_GET['edit']);
		  }
	      }
	  }
      }
    elseif ($_GET['view'] == 'proposals')
      {
	$arrLinks2 = array('D' => "Propozycja opisu lokacji", 
			  'I' => "Propozycja nowego przedmiotu", 
			  'M' => "Propozycja nowego potwora", 
			  'B' => "Propozycja pytania na Moście Śmierci", 
			  'E' => "Propozycja opisu potwora");
	$smarty->assign(array("Info" => "Tutaj możesz zgłaszać propozycje dotyczące opisów lokacji, nowych przedmiotów, potworów oraz pytań na Moście Śmierci. Więcej informacji co i jak znajdziesz po wybraniu odpowiedniej opcji. Zanim dana propozycja pojawi się w grze, musi zostać zaakceptowana przez władzę.",
			      "Links2" => $arrLinks2));
      }
  }
else
  {
    $_GET['view'] = '';
  }

/**
* Initialization of variables
*/
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}

/**
* Assign variables and display page
*/
$arrMenus = array("Konto" => array('name' => A_NAME,
					      'pass' => A_PASS,
					      'eci' => A_EMAIL,
					      'reset' => A_RESET,
					      'immu' => A_IMMU,
					      'freeze' => A_FREEZE),
		  "Profil" => array('profile' => A_PROFILE,
				    'roleplay' => 'Edytuj profil fabularny',
				    'avatar' => A_AVATAR),
		  "Socjalne" => array('contacts' => 'Kontakty',
				      'ignored' => 'Ignorowani'),
		  
		  "Ustawienia" => array('style' => A_STYLE,
					'options' => A_OPTIONS,
					'links' => A_LINKS,
					'forums' => 'Obserwowane fora'),
		  "Inne" => array('changes' => A_CHANGES,
				  'vallars' => 'Ostatnio nagrodzeni Vallarami',
				  'bugreport' => A_BUGREPORT,
				  'bugtrack' => A_BUGTRACK,
				  'proposals' => 'Zgłoś propozycję'));
$smarty -> assign (array ("View" => $_GET['view'], 
                          "Step" => $_GET['step'],
                          "Welcome" => WELCOME,
                          "Menus" => $arrMenus));
$smarty -> display('account.tpl');

require_once("includes/foot.php");
?>
