<?php
/**
 *   File functions:
 *   Add and modify game updates
 *
 *   @name                 : addupdate.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
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
// $Id$

$title = "Dodaj Wieść";
require_once("includes/head.php");

/**
* Get the localization of game
*/
require_once("languages/".$lang."/addupdate.php");

if ($player -> rank != "Admin") 
{
    error (NOT_HAVE);
}

/**
* Check available languages
*/    
$arrLanguage = scandir('languages/', 1);
$arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));

$smarty -> assign ( array("Button" => U_ADD, 
                          "Link" => "addupdate.php?action=add", 
                          "Title1" => '', 
                          "Text" => '', 
                          "Ulangsel" => U_LANG_S, 
                          "Ulang" => $arrLanguage, 
                          "Utitle" => U_TITLE, 
                          "Utext" => U_TEXT));

/**
* Add new update
*/
if (isset ($_GET['action']) && $_GET['action'] == 'add') 
{
    if (empty($_POST['addtitle']) || empty($_POST['addupdate'])) 
    {
        error (EMPTY_FIELDS);
    }
    $_POST['addupdate'] = nl2br($_POST['addupdate']);
    $strTitle = $db -> qstr($_POST['addtitle'], get_magic_quotes_gpc());
    $strUpdate = $db -> qstr($_POST['addupdate'], get_magic_quotes_gpc());
    $strDate = $db -> DBDate($data);
    $db -> Execute("INSERT INTO `updates` (`starter`, `title`, `updates`, `time`, `lang`) VALUES('(".$player -> user.")', ".$strTitle.", ".$strUpdate.", ".$strDate.", '".$lang."')") or error(E_DB);
    error (U_SUCCES);
}

/**
* Update modyfication
*/
if (isset ($_GET['modify'])) 
  {
    $_GET['modify'] = intval($_GET['modify']);
    if ($_GET['modify'] < 1) 
      {
        error (ERROR);
      }
    $update = $db -> Execute("SELECT * FROM updates WHERE id=".$_GET['modify']);
    $update -> fields['updates'] = str_replace("<br />", "", $update -> fields['updates']);
    $smarty -> assign(array("Title1" => $update -> fields['title'], 
                            "Text" => $update -> fields['updates'], 
                            "Button" => U_MODIFY, 
                            "Link" => "addupdate.php?action=modify&updid=".$update -> fields['id']));
    $update -> Close();
}

/**
* Save update modifications
*/
if (isset ($_GET['action']) && $_GET['action'] == 'modify') 
{
    if (empty($_POST['addtitle']) || empty($_POST['addupdate'])) 
    {
        error (EMPTY_FIELDS);
    }
    $_GET['updid'] = intval($_GET['updid']);
    if ($_GET['updid'] < 1) 
      {
        error (ERROR);
      }
    $uid = $db -> Execute("SELECT `id`, `lang` FROM `updates` WHERE `id`=".$_GET['updid']);
    if ($uid -> fields['id']) 
    {
        require_once("languages/".$uid -> fields['lang']."/addupdate1.php");
        $_POST['addupdate'] = $_POST['addupdate']."\n \n ".MODIFY_DATE." ".$data." ".MODIFY_BY." <b>".$player -> user."</b>";
        $_POST['addupdate'] = nl2br($_POST['addupdate']);
        $strTitle = $db -> qstr($_POST['addtitle'], get_magic_quotes_gpc());
        $strUpdate = $db -> qstr($_POST['addupdate'], get_magic_quotes_gpc());
        $db -> Execute("UPDATE `updates` SET `title`=".$strTitle.", `updates`=".$strUpdate." WHERE `id`=".$_GET['updid']);
        error (U_MODIFIED);
    } 
        else 
    {
        error (NO_UPD);
    }
    $uid -> Close();
}

$smarty -> display('addupdate.tpl');

require_once("includes/foot.php");
?>
