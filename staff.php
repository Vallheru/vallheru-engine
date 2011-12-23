<?php
/**
 *   File functions:
 *   Staff panel - give immunited, send players to jailetc
 *
 *   @name                 : staff.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 23.12.2011
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

$title = "Panel Administracyjny";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/staff.php");

if ($player -> rank != 'Staff' && $player -> rank != 'Admin') 
{
    error (YOU_NOT);
}

if (isset($_GET['view']))
{
    $arrView = array('takeaway', 'clearc', 'czat', 'tags', 'jail', 'innarchive', 'banmail', 'addtext', 'logs');
    $intKey = array_search($_GET['view'], $arrView);
    if ($_GET['view'] == 'bforum')
      {
	$intKey = 2;
      }
    if ($intKey !== false)
    {
        require_once("includes/admin/".$arrView[$intKey].".php");
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
    $smarty -> assign(array("Panelinfo" => PANEL_INFO,
                            "Anews" => A_NEWS,
                            "Atake" => A_TAKE,
                            "Aclear" => A_CLEAR,
                            "Achat" => A_CHAT,
			    "Aforum" => 'Zablokuj/Odblokuj pisanie przez gracza na forum',
                            "Aimmu" => A_IMMU,
                            "Ajail" => A_JAIL,
                            "Aaddnews" => A_ADD_NEWS,
                            "Ainnarchive" => A_INNARCHIVE,
			    "Alogs" => "Logi graczy",
                            "Abanmail" => A_BAN_MAIL));
}

if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("View" => $_GET['view'],
                        "Action" => $_GET['action']));
$smarty -> display('staff.tpl');

require_once("includes/foot.php");
?>
