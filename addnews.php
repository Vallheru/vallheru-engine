<?php
/**
 *   File functions:
 *   Adding news in game
 *
 *   @name                 : addnews.php                            
 *   @copyright            : (C) 2004,2005,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 23.05.2012
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
// 

$title = "Dodaj Plotkę"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/addnews.php");

if ($player -> rank != "Admin" && $player -> rank != 'Staff' && $player -> rank != 'Kronikarz') 
{
	error (NOT_HAVE);
}

/**
* Check avaible languages
*/
$arrLanguage = scandir('languages/', 1);
$arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));

/**
* Assign variables and display page
*/
$smarty -> assign(array("Ntitle" => N_TITLE,
    "Ntext" => N_TEXT,
    "Nadd" => N_ADD,
    "Nlangsel" => N_LANG_S,
    "Nlang" => $arrLanguage));
$smarty -> display('addnews.tpl');

if (isset ($_GET['action']) && $_GET['action'] == 'add') 
  {
    if (empty ($_POST['addtitle']) || empty ($_POST['addnews'])) 
      {
	error (EMPTY_FIELDS);
      }
    $_POST['addnews'] = nl2br($_POST['addnews']);
    $db -> Execute("INSERT INTO `news` (`starter`, `title`, `news`, `lang`, `added`) VALUES('".$arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1]." (".$player -> id.")','".$_POST['addtitle']."','".$_POST['addnews']."', '".$lang."', 'N')") or error(E_DB);
    error (N_SUCCES);
}

require_once("includes/foot.php");
?>
