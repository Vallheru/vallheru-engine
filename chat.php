<?php
/**
 *   File functions:
 *   Main file of chat - bot Innkeeper and private talk to other players
 *
 *   @name                 : chat.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 17.08.2012
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

$title = "Karczma";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/chat.php");

$db -> Execute("UPDATE `players` SET `page`='Chat' WHERE `id`=".$player -> id);
//Show more messages
if (isset($_GET['more']))
  {
    $_SESSION['chatlength'] += 25;
    if ($_SESSION['chatlength'] > 150)
      {
	$_SESSION['chatlength'] = 150;
      }
  }
//Show less messages
if (isset($_GET['less']))
  {
    $_SESSION['chatlength'] -= 25;
    if ($_SESSION['chatlength'] < 25)
      {
	$_SESSION['chatlength'] = 25;
      }
  }
//Switch tabs in chat
if (isset($_GET['tabs']))
  {
    if (!isset($_SESSION['chattabs']))
      {
	error('Nikt do Ciebie nie szeptał ostatnio.');
      }
    $arrTabs = array_keys($_SESSION['chattabs']);
    foreach ($arrTabs as $intTab)
      {
	if (isset($_POST[$intTab]))
	  {
	    $_SESSION['chattab'] = $intTab;
	    break;
	  }
      }
  }
//Update time for active tab
if (isset($_SESSION['chattab']))
  {
    $_SESSION['chattabs'][$_SESSION['chattab']] = $ctime;
  }
//Close selected tab
if (isset($_GET['close']))
  {
    checkvalue($_SESSION['chattab']);
    $_SESSION['chatclean'][$_SESSION['chattab']] = $ctime;
    unset($_SESSION['chattabs'][$_SESSION['chattab']]);
    $_SESSION['chattab'] = 0;
  }
//Send message
if (isset($_POST['msg']) && $_POST['msg'] != '') 
  {
    $czat = $db -> Execute("SELECT `gracz` FROM `chat_config` WHERE `gracz`=".$player -> id);
    if ($czat -> fields['gracz'])
      {
	error (NO_PERM);
      }
    $czat -> Close();
    if (isset($_SESSION['lastchat']))
      {
	if ($_SESSION['lastchat'] == $_POST['msg'])
	  {
	    $_POST['msg'] = '';
	  }
      }
    if ($_POST['msg'] != '')
      {
	$_SESSION['lastchat'] = $_POST['msg'];
      }

    if (isset($_SESSION['chattab']))
      {
	if ($_SESSION['chattab'] != 0)
	  {
	    $_POST['msg'] = $_SESSION['chattab'].'='.$_POST['msg'];
	  }
      }
    switch ($player->rank)
      {
      case 'Admin':
	$starter = "<span style=\"color: #0066cc;\">".$player->user."</span>";
	break;
      case 'Staff':
	$starter = "<span style=\"color: #00ff00;\">".$player->user."</span>";
	break;
      default:
	$starter = $player->user;
	break;
      }
    $starter = $arrTags[$player->tribe][0].' <a href="view.php?view='.$player->id.'">'.$starter.'</a> '.$arrTags[$player->tribe][1];
    require_once('includes/bbcode.php');
    $_POST['msg'] = bbcodetohtml($_POST['msg'], TRUE);
    if (strlen(trim(strip_tags($_POST['msg'], '<img>'))) > 0)
      {
	/**
	 * Start innkeeper bot
	 */
	require_once('class/bot_class.php');
	$objBot = new Bot($_POST['msg'], 'Karczmarzu');
	$blnCheckbot = $objBot -> Checkbot();
	if ($blnCheckbot)
	  {
	    $strAnswer = $objBot -> Botanswer();
	    $arrText = explode(" ", $_POST['msg']);
	    $id = intval(end($arrText));
	    if ($id > 0) 
	      { 
		$user = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$id);
		$id = $user -> fields['user'];
		$intValues = count($arrText) - 2;
		$strItem = ' ';
		for ($i = 0; $i < $intValues; $i++)
		  {
		    $strItem = $strItem.$arrText[$i]." ";
		  }
		$message = $strItem." ".FOR_A." ".$id;
		$user -> Close();
	      }
	    else
	      {
		$message = $_POST['msg'];
	      }
	  }
	else
	  {
	    $message = $_POST['msg'];
	    //Throwing/shooting in inn
	    if (stripos($message, "*rzuca") !== FALSE || stripos($message, "*strzela do") !== FALSE)
	      {
		$arrEnd = array("karczmarza*", "barnab", "barda*");
		$arrTarget = array("Karczmarz", "Barnaba", "Bard");
		$intIndex = -1;
		for ($i = 0; $i < count($arrEnd); $i++)
		  {
		    if (stripos($message, $arrEnd[$i]) !== FALSE)
		      {
			$intIndex = $i;
			break;
		      }
		  }
		if ($intIndex > -1)
		  {
		    $arrAnswers = array("Ała, za co?",
					$player->user." jednym niezwykle celnym trafieniem powala cel na ziemię. ".$player->user." dostaje gazylion PD i jeszcze więcej do umiejętności Powalanie.",
					"*".$player->user." nie trafia celu.* Buahahahahaha",
					"*".$arrTarget[$intIndex]." zgrabnym ruchem unika trafienia.*",
					"*".$arrTarget[$intIndex]." odpowiada ogniem ciągłym.*");
		    $intIndex2 = rand(0, count($arrAnswers) - 1);
		    $strTarget = "<i>".$arrTarget[$intIndex]."</i>";
		    $strMessage = $arrAnswers[$intIndex2];
		  }
	      }
	  }
	//Emote
	if (strpos($message, '@me') !== FALSE)
	  {
	    $starter = '';
	    $message = str_replace('@me', '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a>', $message);
	  }
	//Private message
	$test1 = explode("=", $_POST['msg']);
	if (is_numeric($test1[0]) && (count($test1) > 1)) 
	  {
	    if ($test1[0] == $player->id)
	      {
		error('Nie możesz szeptać do siebie.');
	      }
	    $user = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$test1[0]);
	    $id = $user -> fields['user'];
	    $owner = 0;
	    if ($id) 
	      {
		$owner = $test1[0];
		array_shift($test1);
		$message = join("=", $test1);
	      } 
	  } 
	else 
	  {
	    $owner = 0;
	    if (!isset($test1[0])) 
	      {
		$evade = true;
	      }
	  }
	$message = $db -> qstr($message, get_magic_quotes_gpc());
	if (!isset($evade)) 
	  {
	    $db -> Execute("INSERT INTO `chat` (`user`, `chat`, `senderid`, `ownerid`, `sdate`) VALUES('".$starter."', ".$message.", ".$player -> id.", ".$owner.", '".$newdate."')") or die($db->ErrorMsg());
	  }
	if (isset($strTarget))
	  {
	    $db -> Execute("INSERT INTO `chat` (`user`, `chat`, `sdate`) VALUES('".$strTarget."', '".$strMessage."', '".$newdate."')");
	  }
	$intLpv = (time() - 180);
	$objInnkeeper = $db -> Execute("SELECT `user` FROM `players` WHERE `rank`='Karczmarka' AND `page`='Chat' AND `lpv`>=".$intLpv);
	if (!$objInnkeeper -> fields['user'])
	  {
	    if (isset($strAnswer))
	      {
		$strAnswer = $db -> qstr($strAnswer, get_magic_quotes_gpc());
		$db -> Execute("INSERT INTO `chat` (`user`, `chat`, `sdate`) VALUES('<i>".INNKEEPER2."</i>', ".$strAnswer.", '".$newdate."')");
	      }
	  }
	else
	  {
	    if ($blnCheckbot)
	      {
		$db -> Execute("INSERT INTO `chat` (`user`, `chat`, `sdate`) VALUES('<i>Barnaba</i>', '".INNKEEPER_GONE.$objInnkeeper -> fields['user'].RULES."', '".$newdate."')");
	      }
	  }
	$objInnkeeper -> Close();
      }
  }

/**
 * Chat administration
 */
if (isset($_GET['step']))
  {
    if (!in_array($player->rank, array('Admin', 'Staff', 'Karczmarka')))
    {
        error(ERROR);
    }
    if (in_array($_GET['step'], array('give', 'ban', 'clearc')))
      {
	$_SESSION['chatctrl'] = 1;
      }
    /**
     * Give items to player
     */
    if ($_GET['step'] == 'give')
      {
	$_POST['giveid'] = intval($_POST['giveid']);
	if ($_POST['giveid'] < 0)
	  {
	    message('error', ERROR);
	  }
	else
	  {
	    if (isset($_POST['item2']) && strlen($_POST['item2']) > 0)
	      {
		$_POST['item'] = strip_tags($_POST['item2']);
	      }
	    if ($_POST['giveid'] > 0)
	      {
		$objUser = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['giveid']);
		if (!isset($_POST['innkeeper']))
		  {
		    $_POST['innkeeper'] = '';
		  }
		$db -> Execute("INSERT INTO `chat` (`user`, `chat`) VALUES('<i>".$player -> user."</i>', '".PLEASE." ".$objUser -> fields['user']." ".HERE_IS." ".$_POST['item']." ".$_POST['innkeeper']."')");
		$objUser -> Close();
	      }
	    else
	      {
		if (!isset($_POST['innkeeper']))
		  {
		    $_POST['innkeeper'] = '';
		  }
		$db -> Execute("INSERT INTO `chat` (`user`, `chat`) VALUES('<i>".$player -> user."</i>', '".FOR_ALL2." ".$_POST['item'].FOR_ALL3." ".$_POST['innkeeper']."')");
	      }
	  }
      }

    /**
     * Ban/unban players in chat
     */
    elseif ($_GET['step'] == 'ban')
      {
	$_POST['banid'] = intval($_POST['banid']);
	$_POST['duration'] = intval($_POST['duration']);
	if (($_POST['duration'] < 1) || ($_POST['banid'] < 2)) 
	  {
	    message('error', ERROR);
	  }
	else
	  {
	    if ($_POST['ban'] == 'ban') 
	      {
		$intTime = $_POST['duration'] * 7;
		$db -> Execute("INSERT INTO `chat_config` (`gracz`, `resets`) VALUES(".$_POST['banid'].", ".$intTime.")");
		$strDate = $db -> DBDate($newdate);
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['banid'].", '".YOU_BLOCK2.$_POST['duration'].T_DAYS.$_POST['verdict'].BLOCK_BY.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id."</b>.', ".$strDate.", 'A')") or die($db -> ErrorMsg());
		message('success', YOU_BLOCK." ".$_POST['banid']);
	      }
	    elseif ($_POST['ban'] == 'unban') 
	      {
		$db -> Execute("DELETE FROM `chat_config` WHERE `gracz`=".$_POST['banid']);
		message('success', YOU_UNBLOCK." ".$_POST['banid']);
	      }
	  }
      }

    /**
     * Chat prune
     */
    elseif ($_GET['step'] == 'clearc') 
      {
	$db -> Execute("TRUNCATE TABLE `chat`");
        message('success', CHAT_PRUNE);
      }
    /**
     * Delete one message
     */
    elseif ($_GET['step'] == 'delete')
      {
	checkvalue($_GET['tid']);
	$db->Execute("DELETE FROM `chat` WHERE `id`=".$_GET['tid']);
      }
  }

/**
 * Rent a room
 */
if (isset($_GET['room']))
  {
    $blnValid = TRUE;
    checkvalue($_POST['room']);
    if ($_POST['room'] > 100)
      {
	$blnValid = FALSE;
	message('error', 'Możesz wynająć pokój maksymalnie na 100 dni.');
      }
    $intGold = $_POST['room'] * 100;
    if ($player->credits < $intGold)
      {
	message('error', 'Nie masz tyle sztuk złota przy sobie. Potrzebujesz '.$intGold.' sztuk złota.');
	$blnValid = FALSE;
      }
    if ($player->room)
      {
	message('error', 'Masz już pokój bądź należysz już do jakiegoś pokoju.');
	$blnValid = FALSE;
      }
    if ($blnValid)
      {
	$db->Execute("INSERT INTO `rooms` (`owner`, `days`) VALUES(".$player->id.", ".$_POST['room'].")");
	$objRoom = $db->Execute("SELECT `id` FROM `rooms` WHERE `owner`=".$player->id);
	$db->Execute("UPDATE `players` SET `credits`=`credits`-".$intGold.", `room`=".$objRoom->fields['id']." WHERE `id`=".$player->id);
	$objRoom->Close();
	message('success', 'Wynająłeś pokój w karczmie. Teraz możesz ustawić wszystko w panelu pokoju oraz zaprosić innych graczy do pokoju.', '(<a href="chat.php">Odśwież</a>)');
      }
  }


if (in_array($player->rank, array('Admin', 'Karczmarka', 'Staff')))
  {
    if (!isset($_SESSION['chatctrl']))
      {
	$_SESSION['chatctrl'] = 0;
      }
    if ($_SESSION['chatctrl'] == 0)
      {
	$strChecked = 'checked="checked"';
      }
    else
      {
	$strChecked = '';
      }
    $arritems = array(BEER, HONEY, WINE, MUSTAK, JUICE, CUCUMBERS, TEA, MEAT, MEAT2, MEAT3, MEAT4, FOOD, FOOD2, FOOD3, EGGS, EGG, EGG2, MILK, ICE, CHICKEN, FLAPJACK, COFFE, "orzeszki");
    $smarty -> assign(array("Aban" => A_BAN,
                            "Aunban" => A_UNBAN,
                            "Chatid" => CHAT_ID,
			    "Chatid2" => "graczowi o ID",
			    "Tor" => "lub rzecz",
                            "Agive" => A_GIVE,
                            "Items" => $arritems,
                            "Withcomm" => WITH_COMM,
                            "Aprune" => A_PRUNE,
                            "Ona" => ON_A,
                            "Tdays" => T_DAYS,
			    "Checked" => $strChecked,
			    "Apanel" => "+ Kontrola karczmy"));
  }

if (!isset($player->settings['oldchat']) || $player->settings['oldchat'] == 'N')
  {
    $strOldchat = 'N';
  }
else
  {
    $strOldchat = 'Y';
  }

$smarty -> assign (array("Arefresh" => A_REFRESH,
			 'Arent' => 'Wynajmij',
			 'Troom' => 'pokój na',
			 "Tfor" => 'dni za',
			 'Tgold' => 'sztuk złota.',
                         "Asend" => A_SEND,
                         "Inn" => INN,
                         "Rank" => $player -> rank,
			 "Oldchat" => $strOldchat,
			 "Abold" => "Pogrubienie",
			 "Aitalic" => "Kursywa",
			 "Aunderline" => "Podkreślenie",
			 "Aemote" => "Emocje/czynność"));
$smarty -> display ('chat.tpl');
require_once("includes/foot.php");

?>
