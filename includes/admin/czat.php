<?php
/**
 *   File functions:
 *   Ban/Unban player on chat or forums
 *
 *   @name                 : czat.php                            
 *   @copyright            : (C) 2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 25.04.2012
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

$smarty -> assign(array("Blocklist" => BLOCK_LIST,
                        "Ablock" => A_BLOCK,
                        "Aunblock" => A_UNBLOCK,
                        "Chatid" => CHAT_ID,
                        "Amake" => A_MAKE,
                        "Tdays" => T_DAYS,
                        "Ona" => ON_A));
$arrtemp = array();
if ($_GET['view'] == 'czat')
  {
    $czatb = $db -> Execute("SELECT `gracz`, `resets` FROM `chat_config`");
    while (!$czatb -> EOF)
      {
	$arrtemp[] = $czatb -> fields['gracz'].' czas: '.$czatb->fields['resets'].' resetów';
	$czatb -> MoveNext();
      }
    $czatb -> Close();
  }
else
  {
    $czatb = $db -> Execute("SELECT `pid` FROM `ban_forum`");
    while (!$czatb -> EOF)
      {
	$arrtemp[] = $czatb -> fields['pid'];
	$czatb -> MoveNext();
      }
    $czatb -> Close();
  }
$smarty -> assign ("List1", $arrtemp);
if (isset ($_GET['step']))
  {
    if ($_GET['view'] == 'czat') 
      {
	if ($_POST['czat'] == 'blok') 
	  {
	    $intTime = $_POST['duration'] * 7;
	    $db -> Execute("INSERT INTO `chat_config` (`gracz`, `resets`) VALUES(".$_POST['czat_id'].", ".$intTime.")");
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['czat_id'].", '".YOU_BLOCK2.$_POST['duration'].T_DAYS.$_POST['verdict'].BLOCK_BY.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID ".$player -> id.".', ".$strDate.")") or die($db -> ErrorMsg());
	    error (YOU_BLOCK." ".$_POST['czat_id']);
	  }
	else 
	  {
	    $db -> Execute("DELETE FROM `chat_config` WHERE `gracz`=".$_POST['czat_id']);
	    error (YOU_UNBLOCK." ".$_POST['czat_id']);
	  }
      }
    else
      {
	if ($_POST['czat'] == 'blok')
	  {
	    $intTime = $_POST['duration'] * 7;
	    $db -> Execute("INSERT INTO `ban_forum` (`pid`, `resets`) VALUES(".$_POST['czat_id'].", ".$intTime.")");
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['czat_id'].", 'Masz zakaz pisania na forum przez ".$_POST['duration'].T_DAYS.$_POST['verdict'].BLOCK_BY.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID ".$player -> id.".', ".$strDate.")") or die($db -> ErrorMsg());
	    error ("Zablokowałeś pisanie na forum graczowi o ID: ".$_POST['czat_id']);
	  }
	else
	  {
	    $db -> Execute("DELETE FROM `ban_forum` WHERE `pid`=".$_POST['czat_id']);
	    error ("Odblokowałeś pisanie na forum graczowi o ID: ".$_POST['czat_id']);
	  }
      }
  }
?>
