<?php
/**
 *   File functions:
 *   Logout from game
 *
 *   @name                 : logout.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 05.12.2011
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

require_once('includes/sessions.php'); 
require_once('libs/Smarty.class.php');
require_once ('includes/config.php');

$smarty = new Smarty;

$smarty -> compile_check = true;

require_once("languages/".$lang."/logout.php");


$_GET['did'] = intval($_GET['did']);
if ($_GET['did'] < 1)
  {
    $smarty -> assign ("Error", ERROR);
    $smarty -> display ('error.tpl');
    exit; 
  }

$stat = $db -> Execute("SELECT `id` FROM `players` WHERE `email`='".$_SESSION['email']."' AND `pass`='".$_SESSION['pass']."'");
if ($stat -> fields['id'] != $_GET['did']) 
{
    $smarty -> assign ("Error", ERROR);
    $smarty -> display ('error.tpl');
    exit;
}

$stat -> Close();
if (isset($_GET['rest']) && $_GET['rest'] == 'Y') 
{
    $test = $db -> Execute("SELECT `id` FROM `houses` WHERE `owner`=".$_GET['did']);
    $test1 = $db -> Execute("SELECT `id` FROM `houses` WHERE `locator`=".$_GET['did']);
    if (!$test -> fields['id'] && !$test1 -> fields['id']) 
    {
        $smarty -> assign ("Error", NOT_SLEEP);
        $smarty -> display ('error.tpl');
        exit;
    }
    $test -> Close();
    $test1 -> Close();    
    $db -> Execute("UPDATE `players` SET `rest`='Y' WHERE `id`=".$_GET['did']);
}
$db -> Execute("UPDATE `players` SET `lpv`=`lpv`-180 WHERE `id`=".$_GET['did']);
session_unset();
session_destroy();
$smarty -> assign(array("Gamename" => $gamename,
			"Meta" => '',
			"Youare" => YOU_ARE,
			"Ahere" => A_HERE,
			"Fora" => FOR_A,
			"Charset" => CHARSET));
$smarty -> display ('logout.tpl');
