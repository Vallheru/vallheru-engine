<?php
/**
 *   File functions:
 *   Main file of room - room info and administration
 *
 *   @name                 : room.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 07.09.2012
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
if ($objRoom->fields['owners'] != '')
  {
    $arrOwners = explode(';', $objRoom->fields['owners']);
  }
else
  {
    $arrOwners = array();
  }
$objPlayers = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `room`=".$player->room);
$arrPlayers = array();
while (!$objPlayers->EOF)
  {
    $arrPlayers[$objPlayers->fields['id']] = $objPlayers->fields['user'];
    $objPlayers->MoveNext();
  }
$objPlayers->Close();
if ($player->id == $objRoom->fields['owner'] || in_array($player->id, $arrOwners))
  {
    $strOwner = '+ Panel administracyjny';
    if (strlen($objRoom->fields['npcs']) > 0)
      {
	$arrNPC = explode(';', $objRoom->fields['npcs']);
	$arrTalk = array_merge(array('Ty', 'Opis'), $arrNPC);
	$strDNPC = 'Y';
      }
    else
      {
	$arrTalk = array('Ty', 'Opis');
	$arrNPC = array();
	$strDNPC = 'N';
      }
    $arrRoptions = array('1' => '1 dzień za 100',
			 '3' => '3 dni za 300',
			 '7' => '7 dni za 700',
			 '14' => '14 dni za 1400',
			 '21' => '21 dni za 2100');
    $arrColors = array('aqua' => 'cyjan',
		       'blue' => 'niebieski',
		       'fuchsia' => 'fuksja',
		       'green' => 'zielony',
		       'grey' => 'szary',
		       'lime' => 'limonka',
		       'maroon' => 'wiśniowy',
		       'navy' => 'granatowy',
		       'olive' => 'oliwkowy',
		       'purple' => 'fioletowy',
		       'red' => 'czerwony',
		       'silver' => 'srebrny',
		       'teal' => 'morski',
		       'yellow' => 'żółty');
    $smarty->assign(array('Akick' => 'Wyrzuć',
			  'Ainv' => 'Zaproś',
			  'Tid' => 'gracza o ID:',
			  'Tid2' => 'gracza:',
			  'Troom' => 'do pokoju.',
			  'Froom' => 'z pokoju.',
			  'Achange' => 'Zmień',
			  'Tdesc' => 'opis pokoju:',
			  'Tname' => 'nazwę pokoju na',
			  'Aadd' => 'Dodaj',
			  'Towners' => 'jako właściciela do pokoju.',
			  'Aremove' => 'Usuń',
			  'Amake' => 'Ustaw',
			  'Tas' => 'jako',
			  'Toptions' => $arrTalk,
			  'Dnpc' => $strDNPC,
			  'Noptions' => $arrNPC,
			  'Tnpc' => 'imię NPC, które będziesz używał',
			  'Tnpc2' => 'NPC o imieniu',
			  'Tcolor' => 'kolor graczowi: ',
			  'Arent' => 'Przedłuż',
			  'Trent2' => 'wynajem pokoju o ',
			  'Trent3' => 'sztuk złota.',
			  'Roptions' => $arrRoptions,
			  'Coptions' => $arrColors));
  }
else
  {
    $strOwner = '';
  }

if (isset($_GET['more']))
  {
    $_SESSION['roomlength'] += 25;
    if ($_SESSION['roomlength'] > 150)
      {
	$_SESSION['roomlength'] = 150;
      }
  }
if (isset($_GET['less']))
  {
    $_SESSION['roomlength'] -= 25;
    if ($_SESSION['roomlength'] < 25)
      {
	$_SESSION['roomlength'] = 25;
      }
  }

$db -> Execute("UPDATE `players` SET `page`='Pokój w karczmie' WHERE `id`=".$player->id);
if (isset ($_GET['action']) && $_GET['action'] == 'chat') 
{
    if (isset($_POST['msg']) && $_POST['msg'] != '') 
      {
	$arrColors2 = array();
	if ($objRoom->fields['colors'] != '')
	  {
	    $arrTmp = explode(';', $objRoom->fields['colors']);
	    foreach ($arrTmp as $strColor)
	      {
		$arrTmp2 = explode(',', $strColor);
		if (count($arrTmp2) == 2)
		  {
		    $arrColors2[$arrTmp2[0]] = $arrTmp2[1];
		  }
	      }
	  }
	if ($strOwner == '')
	  {
	    if (array_key_exists($player->id, $arrColors2))
	      {
		$starter = '<a href="view.php?view='.$player->id.'" target="_parent" style="color:'.$arrColors2[$player->id].';">'.$player->user.'</a>';
	      }
	    else
	      {
		$starter = '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a>';
	      }
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
		if (array_key_exists($player->id, $arrColors2))
		  {
		    $starter = '<a href="view.php?view='.$player->id.'" target="_parent" style="color:'.$arrColors2[$player->id].';">'.$player->user.'</a>';
		  }
		else
		  {
		    $starter = '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a>';
		  }
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
	if ($strOwner != '')
	  {
	    if ($_POST['person'] < 2)
	      {
		if (strpos($message, '@me') !== FALSE)
		  {
		    $starter = '';
		    $message = str_replace('@me', '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a>', $message);
		  }
	      }
	    else
	      {
		if (strpos($message, $starter) !== FALSE)
		  {
		    $starter = '';
		  }
	      }
	  }
	else
	  {
	    if (strpos($message, '@me') !== FALSE)
	      {
		$starter = '';
		$message = str_replace('@me', '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a>', $message);
	      }
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
if (isset($_GET['delete']) && ($objRoom->fields['owner'] == $player->id || in_array($player->id, $arrOwners)))
  {
    if (!is_numeric($_GET['delete']))
      {
	error('Zapomnij o tym.');
      }
    $_GET['delete'] = intval($_GET['delete']);
    if ($_GET['delete'] < 1)
      {
	error('Zapomnij o tym.');
      }
    $objText = $db->Execute("SELECT `id`, `room` FROM `chatrooms` WHERE `id`=".$_GET['delete']);
    if (!$objText->fields['id'])
      {
	error('Nie ma takiego tekstu.');
      }
    if ($objText->fields['room'] != $player->room)
      {
	error('Nie ma takiego tekstu w twoim pokoju.');
      }
    $objText->Close();
    $db->Execute("DELETE FROM `chatrooms` WHERE `id`=".$_GET['delete']);
  }
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
	    if (in_array($player->id, $arrOwners))
	      {
		$intIndex = array_search($player->id, $arrOwners);
		unset($arrOwners[$intIndex]);
		$arrOwners = array_values($arrOwners);
		if (count($arrOwners))
		  {
		    $db->Execute("UPDATE `rooms` SET `owners`='".implode(';', $arrOwners)."' WHERE `id`=".$player->room);
		  }
		else
		  {
		    $db->Execute("UPDATE `rooms` SET `owners`='' WHERE `id`=".$player->room);
		  }
	      }
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
	    //Invite player to room
	  case 'invite':
	    checkvalue($_POST['pid']);
	    $blnValid = TRUE;
	    $objInv = $db->Execute("SELECT `id`, `room`, `settings` FROM `players` WHERE `id`=".$_POST['pid']);
	    if (!$objInv->fields['id'])
	      {
		message('error', 'Nie ma takiego gracza');
		$blnValid = FALSE;
	      }
	    elseif ($objInv->fields['room'])
	      {
		message('error', 'Ten gracz posiada już pokój bądź został już zaproszony do jakiegoś pokoju.');
		$blnValid = FALSE;
	      }
	    $objTest = $db->Execute("SELECT `id` FROM `ignored` WHERE `owner`=".$objInv->fields['id']." AND `pid`=".$player->id." AND `inn`='Y'");
	    if ($objTest->fields['id'])
	      {
		message('error', 'Ten gracz ignoruje zaproszenia od ciebie.');
		$blnValid = FALSE;
	      }
	    $objTest->Close();
	    if (strpos($objInv->fields['settings'], 'rinvites:N') !== FALSE)
	      {
		message('error', 'Ta osoba ignoruje wszystkie zaproszenia do pokojów w karczmie.');
		$blnValid = FALSE;
	      }
	    if ($blnValid)
	      {
		$strDate = $db -> DBDate($newdate);
		$db->Execute("UPDATE `players` SET `room`=".$player->room." WHERE `id`=".$objInv->fields['id']);
		message('success', 'Zaprosiłeś(aś) gracza o ID: '.$_POST['pid'].' do swojego pokoju.');
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objInv->fields['id'].", '".$player->user." zaprosił(a) Ciebie do swojego pokoju w karczmie.', ".$strDate.", 'E')");
	      }
	    $objInv->Close();
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
	    $objTest = $db->Execute("SELECT `id` FROM `players` WHERE `user`='".$_POST['npc']."'");
	    if ($objTest->fields['id'] || in_array($_POST['npc'], $arrNPC))
	      {
		message('error', 'Nie możesz dodać NPC o takim imieniu.');
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
	    //Delete npc from room
	  case 'dnpc':
	    $_POST['npc'] = str_replace("'","",strip_tags($_POST['npc']));
	    $_POST['npc'] = str_replace("&nbsp;", "", $_POST['npc']);
	    $_POST['npc'] = trim($_POST['npc']);
	    $intKey = array_search($_POST['npc'], $arrNPC);
	    if ($intKey === FALSE)
	      {
		message('error', 'Nie ma takiego NPC w pokoju.');
	      }
	    else
	      {
		unset($arrNPC[$intKey]);
		$strNPC = implode(';', $arrNPC);
		$db->Execute("UPDATE `rooms` SET `npcs`='".$strNPC."' WHERE `id`=".$player->room);
		message('success', 'Usunąłeś(aś) NPC do pokoju', '(<a href="room.php">Odśwież</a>)');
	      }
	    break;
	    //Add/remove admins to rooms
	  case 'admin':
	    checkvalue($_POST['pid']);
	    $objTest = $db->Execute("SELECT `id`, `room` FROM `players` WHERE `id`=".$_POST['pid']);
	    if ($player->id != $objRoom->fields['owner'])
	      {
		message('error', 'Tylko właściciel pokoju może ustawiać współwłaścicieli.');
	      }
	    elseif ($_POST['pid'] == $player->id)
	      {
		message('error', 'Nie możesz dodać/usunąć siebie jako współwłaściciela');
	      }
	    elseif (!$objTest->fields['id'])
	      {
		message('error', 'Nie ma takiego gracza.');
	      }
	    elseif ($objTest->fields['room'] != $player->room)
	      {
		message('error', 'Ten gracz nie został zaproszony do tego pokoju.');
	      }
	    elseif (in_array($_POST['pid'], $arrOwners) && $_POST['action'] == 0)
	      {
		message('error', 'Ten gracz jest już współwłaścicielem Twojego pokoju.');
	      }
	    elseif (!in_array($_POST['pid'], $arrOwners) && $_POST['action'] == 1)
	      {
		message('error', 'Ten gracz nie jest współwłaścicielem Twojego pokoju.');
	      }
	    elseif ($_POST['action'] == 0)
	      {
		$arrOwners[] = $_POST['pid'];
		$db->Execute("UPDATE `rooms` SET `owners`='".implode(';', $arrOwners)."' WHERE `id`=".$player->room);
		$strDate = $db -> DBDate($newdate);
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$player->user." dodał(a) Ciebie jako współwłaściciela pokoju w karczmie.', ".$strDate.", 'E')");
		message('success', 'Dodałeś gracza o ID: '.$_POST['pid'].' jako współwłaściciela do pokoju.', '(<a href="room.php">Odśwież</a>)');
		
	      }
	    else
	      {
		$intIndex = array_search($_POST['pid'], $arrOwners);
		unset($arrOwners[$intIndex]);
		$arrOwners = array_values($arrOwners);
		if (count($arrOwners))
		  {
		    $db->Execute("UPDATE `rooms` SET `owners`='".implode(';', $arrOwners)."' WHERE `id`=".$player->room);
		  }
		else
		  {
		    $db->Execute("UPDATE `rooms` SET `owners`='' WHERE `id`=".$player->room);
		  }
		$strDate = $db -> DBDate($newdate);
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$player->user." usunął Ciebie jako współwłaściciela pokoju w karczmie.', ".$strDate.", 'E')");
		message('success', 'Usunąłeś gracza o ID: '.$_POST['pid'].' jako współwłaściciela do pokoju.', '(<a href="room.php">Odśwież</a>)');
	      }
	    break;
	    //Change color for room members
	  case 'color':
	    checkvalue($_POST['pid']);
	    if (!array_key_exists($_POST['color'], $arrColors))
	      {
		error('Zapomnij o tym.');
	      }
	    $objTest = $db->Execute("SELECT `id`, `room` FROM `players` WHERE `id`=".$_POST['pid']);
	    if (!$objTest->fields['id'])
	      {
		message('error', 'Nie ma takiego gracza.');
	      }
	    elseif ($objTest->fields['room'] != $player->room)
	      {
		message('error', 'Ten gracz nie został zaproszony do tego pokoju.');
	      }
	    else
	      {
		$arrColors2 = array();
		if ($objRoom->fields['colors'] != '')
		  {
		    $arrTmp = explode(';', $objRoom->fields['colors']);
		    foreach ($arrTmp as $strColor)
		      {
			$arrTmp2 = explode(',', $strColor);
			if (count($arrTmp2) == 2)
			  {
			    $arrColors2[$arrTmp2[0]] = $arrTmp2[1];
			  }
		      }
		  }
		$strColor = '';
		$arrColors2[$_POST['pid']] = $_POST['color'];
		foreach ($arrColors2 as $key=>$value)
		  {
		    if ($key == '' || $value == '')
		      {
			continue;
		      }
		    $strColor .= $key.','.$value.';';
		  }
		$db->Execute("UPDATE `rooms` SET `colors`='".$strColor."' WHERE `id`=".$player->room) or die($db->ErrorMsg());
		message('success', 'Ustawiłeś(aś) graczowi o ID: '.$_POST['pid'].' kolor nicka.');
	      }
	    break;
	    //Rent room for longer
	  case 'rent':
	    checkvalue($_POST['rent']);
	    if (!in_array($_POST['rent'], array(1, 3, 7, 14, 21)))
	      {
		error('Zapomnij o tym.');
	      }
	    $intGold = $_POST['rent'] * 100;
	    if ($player->credits < $intGold)
	      {
		message('error', 'Nie masz tyle sztuk złota przy sobie. Potrzebujesz '.$intGold.' sztuk złota.');
	      }
	    elseif ($_POST['rent'] + $objRoom->fields['days'] > 100)
	      {
		message('error', 'Nie możesz przedłużyć aż o tyle dni wynajęcia pokoju.');
	      }
	    else
	      {
		$db->Execute("UPDATE `rooms` SET `days`=`days`+".$_POST['rent']." WHERE `id`=".$player->room);
		$db->Execute("UPDATE `players` SET `credits`=`credits`-".$intGold." WHERE `id`=".$player->id);
		$objRoom->fields['days'] += $_POST['rent'];
		message('success', 'Przedłużyłeś(aś) wynajem pokoju o '.$_POST['rent'].' dni.');
	      }
	    break;
	  default:
	    error('Zapomnij o tym.');
	    break;
	  }
      }
  }

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
  {
    $strChecked = "";
  }
else
  {
    $strChecked = "checked=checkded";
  }

if (!isset($player->settings['oldchat']) || $player->settings['oldchat'] == 'N')
  {
    $strOldchat = 'N';
  }
else
  {
    $strOldchat = 'Y';
  }

$smarty -> assign (array("Arefresh" => 'Odśwież',
                         "Asend" => 'Wyślij',
			 'Amanage' => 'Zarządzaj pokojem',
                         "Inn" => $objRoom->fields['name'],
			 "Adesc" => '+ Opis',
			 "Aleft" => 'Opuść pokój',
			 "Aowner" => $strOwner,
			 "Desc" => $objRoom->fields['desc'],
			 'Poptions' => $arrPlayers,
			 'Tinroom' => '+ Osoby w pokoju',
			 'Checked' => $strChecked,
			 "Desc2" => htmltobbcode($objRoom->fields['desc']),
			 'Trent' => 'Pokój będzie istniał jeszcze przez '.$objRoom->fields['days'].' dni.',
                         "Rank" => $player->rank,
			 "Oldchat" => $strOldchat,
			 "Abold" => "Pogrubienie",
			 "Aitalic" => "Kursywa",
			 "Aunderline" => "Podkreślenie",
			 "Aemote" => "Emocje/czynność"));
$smarty -> display ('room.tpl');
$objRoom->Close();

require_once("includes/foot.php");
?>
