<?php
/**
 *   File functions:
 *   Main file of room - room info and administration
 *
 *   @name                 : room.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 05.01.2012
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

$title = "Pokój w karczmie";
require_once("includes/head.php");

if (!$player->room)
  {
    error("Nie posiadasz bądź nie zostałeś zaproszony do jakiegokolwiek pokoju. <a href=");
  }

require_once('includes/bbcode.php');

$objRoom = $db->Execute("SELECT * FROM `rooms` WHERE `id`=".$player->room);
if ($player->id == $objRoom->fields['owner'])
  {
    $strOwner = '+ Panel administracyjny';
    if (strlen($objRoom->fields['npcs']) > 0)
      {
	$arrNPC = explode(';', $objRoom->fields['npcs']);
	$arrTalk = array_merge(array('Ty', 'Opis'), $arrNPC);
      }
    else
      {
	$arrTalk = array('Ty', 'Opis');
	$arrNPC = array();
      }
    $smarty->assign(array('Akick' => 'Wyrzuć',
			  'Tid' => 'gracza o ID:',
			  'Froom' => 'z pokoju.',
			  'Achange' => 'Zmień',
			  'Tdesc' => 'opis pokoju:',
			  'Tname' => 'nazwę pokoju na',
			  'Aadd' => 'Dodaj',
			  'Tas' => 'jako',
			  'Toptions' => $arrTalk,
			  'Tnpc' => 'imię NPC, które będziesz używał'));
  }
else
  {
    $strOwner = '';
  }

$db -> Execute("UPDATE `players` SET `page`='Pokój w karczmie' WHERE `id`=".$player->id);
if (isset ($_GET['action']) && $_GET['action'] == 'chat') 
{
    if (isset($_POST['msg']) && $_POST['msg'] != '') 
      {
	if ($strOwner == '')
	  {
	    $starter = '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a>';
	  }
	else
	  {
	    $_POST['person'] = intval($_POST['person']);
	    if ($_POST['person'] < 0 || ($_POST['person'] - 2) > count($arrNPC))
	      {
		error('Zapomnij o tym.');
	      }
	    if ($_POST['person'] == 0)
	      {
		$starter = '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a>';
	      }
	    elseif($_POST['person'] == 1)
	      {
		$starter = '';
	      }
	    else
	      {
		$intIndex = $_POST['person'] - 2;
		$starter = $arrNPC[$intIndex];
		$_POST['msg'] = str_replace('/me', $starter, $_POST['msg']);
	      }
	  }
        $_POST['msg'] = bbcodetohtml($_POST['msg'], TRUE);
        if (preg_match("/\S+/", $_POST['msg']) == 0)
        {
	  error('Zapomnij o tym.');
	}
	$message = $_POST['msg'];
	//Emote
	if (strpos($message, '*') == 0 && strrpos($message, '*') == (strlen($message) - 1))
	  {
	    if ($_POST['person'] < 2)
	      {
		if (strpos($message, $player->user) !== FALSE)
		  {
		    $starter = '';
		  }
	      }
	    else
	      {
		if (strpos($message, $starter) !== FALSE)
		  {
		    $starter = '';
		  }
	      }
	    $message = '<b><i>'.$message.'</i></b>';
	  }
	//Private message
        $test1 = explode("=", $_POST['msg']);
        if (is_numeric($test1[0]) && (count($test1) > 1)) 
        {
            $user = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$test1[0]);
            $id = $user -> fields['user'];
	    $owner = 0;
            if ($id) 
	      {
		$owner = $test1[0];
		array_shift($test1);
		$message = "<b>".$id.">>></b> ".join("=", $test1);
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
	  $db -> Execute("INSERT INTO `chatrooms` (`user`, `chat`, `senderid`, `ownerid`, `sdate`, `room`) VALUES('".$starter."', ".$message.", ".$player -> id.", ".$owner.", '".$newdate."', ".$player->room.")") or die($db->ErrorMsg());
        }
    }
}

/**
 * Room administation
 */
if (isset($_GET['step']))
  {
    //Quit from room
    if ($_GET['step'] == 'quit')
      {
	if ($player->id == $objRoom->fields['owner'])
	  {
	    $db->Execute("DELETE FROM `rooms` WHERE `id`=".$player->room);
	    $db->Execute("DELETE FROM `chatrooms` WHERE `room`=".$player->room);
	    $objMates = $db->Execute("SELECT `id` FROM `players` WHERE `room`=".$player->room) or die($db->ErrorMsg());
	    $strDate = $db -> DBDate($newdate);
	    while (!$objMates->EOF)
	      {
		if ($objMates->fields['id'] != $player->id)
		  {
		    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objMates->fields['id'].", '".$player->user." zlikwidował(a) pokój w karczmie.', ".$strDate.", 'E')");
		  }
		$objMates->MoveNext();
	      }
	    $objMates->Close();
	    $db->Execute("UPDATE `players` SET `room`=0 WHERE `room`=".$player->room);
	    error("Zlikwidowałeś(aś) swój pokój w karczmie. <a href=");
	  }
	else
	  {
	    $strMessage = '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a> opuścił(a) pokój.';
	    $strMessage = $db -> qstr($strMessage, get_magic_quotes_gpc());
	    $db -> Execute("INSERT INTO `chatrooms` (`user`, `chat`, `senderid`, `ownerid`, `sdate`, `room`) VALUES('', ".$strMessage.", ".$player -> id.", 0, '".$newdate."', ".$player->room.")") or die($db->ErrorMsg());
	    $db->Execute("UPDATE `players` SET `room`=0 WHERE `id`=".$player->id);
	    error("Opuściłeś(aś) pokój w karczmie. <a href=");
	  }
      }
    //Change room settings
    elseif ($_GET['step'] == 'admin')
      {
	if ($strOwner == '' || !isset($_GET['action']))
	  {
	    error('Zapomnij o tym.');
	  }
	switch ($_GET['action'])
	  {
	    //Remove player from room
	  case 'remove':
	    checkvalue($_POST['pid']);
	    $objTest = $db->Execute("SELECT `room` FROM `players` WHERE `id`=".$_POST['pid']);
	    if ($objTest->fields['room'] != $player->room)
	      {
		message('error', 'Ten gracz nie został zaproszony do tego pokoju.');
	      }
	    elseif ($_POST['pid'] == $player->id)
	      {
		message('error', 'Nie możesz wyrzucić siebie z pokoju.');
	      }
	    else
	      {
		$strDate = $db -> DBDate($newdate);
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$player->user." wyrzucił(a) Ciebie z pokoju w karczmie.', ".$strDate.", 'E')");
		$db->Execute("UPDATE `players` SET `room`=0 WHERE `id`=".$_POST['pid']);
		message('success', 'Wyrzuciłeś(aś) gracza o ID: '.$_POST['pid'].' z pokoju.');
	      }
	    break;
	    //Change room description
	  case 'desc':
	    if (!isset($_POST['desc']))
	      {
		error('Zapomnij o tym.');
	      }
	    $_POST['desc'] = bbcodetohtml($_POST['desc']);
	    $objRoom->fields['desc'] = $_POST['desc'];
	    $_POST['desc'] = $db->qstr($_POST['desc'], get_magic_quotes_gpc());
	    $db->Execute("UPDATE `rooms` SET `desc`=".$_POST['desc']." WHERE `id`=".$player->room);
	    message('success', 'Zmieniłeś(aś) opis pokoju');
	    break;
	    //Change room name
	  case 'name':
	    $_POST['rname'] = str_replace("'","",strip_tags($_POST['rname']));
	    $_POST['rname'] = str_replace("&nbsp;", "", $_POST['rname']);
	    $_POST['rname'] = trim($_POST['rname']);
	    $objRoom->fields['name'] = $_POST['rname'];
	    $db->Execute("UPDATE `rooms` SET `name`='".$_POST['rname']."' WHERE `id`=".$player->room);
	    message('success', 'Zmieniłeś(aś) nazwę pokoju');
	    break;
	    //Add npc to room
	  case 'npc':
	    $_POST['npc'] = str_replace("'","",strip_tags($_POST['npc']));
	    $_POST['npc'] = str_replace("&nbsp;", "", $_POST['npc']);
	    $_POST['npc'] = trim($_POST['npc']);
	    $blnValid = TRUE;
	    $objTest = $db->Execute("SELECT `id` FROM `players` WHERE `user`='".$_POST['npc']."'");
	    if ($objTest->fields['id'] || in_array($_POST['npc'], $arrNPC))
	      {
		message('error', 'Nie możesz dodać NPC o takim imieniu.');
		$blnValid = FALSE;
	      }
	    else
	      {
		$arrNPC[] = $_POST['npc'];
		$strNPC = implode(';', $arrNPC);
		$db->Execute("UPDATE `rooms` SET `npcs`='".$strNPC."' WHERE `id`=".$player->room);
		message('success', 'Dodałeś(aś) NPC do pokoju', '(<a href="room.php">Odśwież</a>)');
	      }
	    $objTest->Close();
	    break;
	  default:
	    error('Zapomnij o tym.');
	    break;
	  }
      }
  }

$smarty -> assign (array("Arefresh" => 'Odśwież',
                         "Asend" => 'Wyślij',
			 'Amanage' => 'Zarządzaj pokojem',
                         "Inn" => $objRoom->fields['name'],
			 "Adesc" => '+ Opis',
			 "Aleft" => 'Opuść pokój',
			 "Aowner" => $strOwner,
			 "Desc" => $objRoom->fields['desc'],
			 "Desc2" => htmltobbcode($objRoom->fields['desc']),
                         "Rank" => $player->rank));
$smarty -> display ('room.tpl');
$objRoom->Close();

require_once("includes/foot.php");
?>
