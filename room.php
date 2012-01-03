<?php
/**
 *   File functions:
 *   Main file of room - room info and administration
 *
 *   @name                 : room.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 03.01.2012
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

$db -> Execute("UPDATE `players` SET `page`='Pokój w karczmie' WHERE `id`=".$player->id);
if (isset ($_GET['action']) && $_GET['action'] == 'chat') 
{
    if (isset($_POST['msg']) && $_POST['msg'] != '') 
      {
	$starter = '<a href="view.php?view='.$player->id.'" target="_parent">'.$player->user.'</a>';
        require_once('includes/bbcode.php');
        $_POST['msg'] = bbcodetohtml($_POST['msg'], TRUE);
        if (preg_match("/\S+/", $_POST['msg']) == 0)
        {
	  error('Zapomnij o tym.');
	}
	$message = $_POST['msg'];
	//Emote
	if (strpos($message, '*') == 0 && strrpos($message, '*') == (strlen($message) - 1))
	  {
	    if (strpos($message, $player->user) !== FALSE)
	      {
		$starter = '';
	      }
	    $message = '<i>'.$message.'</i>';
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
	if (isset($strTarget))
	  {
	    $db -> Execute("INSERT INTO `chatrooms` (`user`, `chat`, `sdate`, `room`) VALUES('".$strTarget."', '".$strMessage."', '".$newdate."', ".$player->room.")");
	  }
    }
}

$objRoom = $db->Execute("SELECT * FROM `rooms` WHERE `id`=".$player->room);

$smarty -> assign (array("Arefresh" => 'Odśwież',
                         "Asend" => 'Wyślij',
			 'Amanage' => 'Zarządzaj pokojem',
                         "Inn" => $objRoom->fields['name'],
			 "Adesc" => 'Opis',
			 "Desc" => $objRoom->fields['desc'],
                         "Rank" => $player->rank));
$smarty -> display ('room.tpl');
$objRoom->Close();

require_once("includes/foot.php");
?>
