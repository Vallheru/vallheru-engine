<?php
/**
 *   File functions:
 *   Logout from game
 *
 *   @name                 : logout.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 29.07.2006
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
// $Id: logout.php 546 2006-07-29 10:42:45Z thindil $

require_once('includes/sessions.php'); 
require_once('libs/Smarty.class.php');
require_once ('includes/config.php');

$smarty = new Smarty;

$smarty -> compile_check = true;

/**
* Check avaible languages
*/    
$path = 'languages/';
$dir = opendir($path);
$arrLanguage = array();
$i = 0;
while ($file = readdir($dir))
{
    if (!ereg(".htm*$", $file))
    {
        if (!ereg("\.$", $file))
        {
            $arrLanguage[$i] = $file;
            $i = $i + 1;
        }
    }
}
closedir($dir);

/**
* Get the localization for game
*/
$strLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
foreach ($arrLanguage as $strTrans)
{
    $strSearch = "^".$strTrans;
    if (eregi($strSearch, $strLanguage))
    {
        $strTranslation = $strTrans;
        break;
    }
}
if (!isset($strTranslation))
{
    $strTranslation = 'pl';
}
require_once("languages/".$strTranslation."/logout.php");

if (!ereg("^[1-9][0-9]*$", $_GET['did'])) 
{
    $smarty -> assign ("Error", ERROR);
    $smarty -> display ('error.tpl');
    exit;
}

$pass = MD5($_SESSION['pass']);
$stat = $db -> Execute("SELECT id FROM players WHERE email='".$_SESSION['email']."' AND pass='".$pass."'");
if ($stat -> fields['id'] != $_GET['did']) 
{
    $smarty -> assign ("Error", ERROR);
    $smarty -> display ('error.tpl');
    exit;
}

$stat -> Close();
if (isset($_GET['rest']) && $_GET['rest'] == 'Y') 
{
    $test = $db -> Execute("SELECT id FROM houses WHERE owner=".$_GET['did']);
    $test1 = $db -> Execute("SELECT id FROM houses WHERE locator=".$_GET['did']);
    if (!$test -> fields['id'] && !$test1 -> fields['id']) 
    {
        $smarty -> assign ("Error", NOT_SLEEP);
        $smarty -> display ('error.tpl');
        exit;
    }
    $test -> Close();
    $test1 -> Close();    
    $db -> Execute("UPDATE players SET rest='Y' WHERE id=".$_GET['did']);
}
$db -> Execute("UPDATE players SET lpv=lpv-180 WHERE id=".$_GET['did']);
session_unset();
session_destroy();
$smarty -> assign(array("Gamename" => $gamename,
    "Youare" => YOU_ARE,
    "Ahere" => A_HERE,
    "Fora" => FOR_A,
    "Charset" => CHARSET));
$smarty -> display ('logout.tpl');
