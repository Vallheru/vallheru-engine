<?php
/**
 *   File functions:
 *   Staff panel - give immunited, send players to jailetc
 *
 *   @name                 : staff.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 01.02.2013
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
require_once("languages/".$lang."/staff.php");

if (!in_array($player -> rank, array('Staff', 'Admin', 'Budowniczy'))) 
  {
    error (YOU_NOT);
  }

if (isset($_GET['view']))
  {
    if ($player->rank != 'Budowniczy')
      {
	$arrView = array('takeaway', 'clearc', 'czat', 'tags', 'jail', 'innarchive', 'banmail', 'addtext', 'logs', 'bugreport');
	$intKey = array_search($_GET['view'], $arrView);
	if ($_GET['view'] == 'bforum')
	  {
	    $intKey = 2;
	  }
      }
    else
      {
	$arrView = array('bugreport');
	$intKey = array_search($_GET['view'], $arrView);
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
    if ($player->rank != 'Budowniczy')
      {
	$arrLinks = array(array(A_NEWS, 'addnews.php'),
			  array(A_TAKE, 'staff.php?view=takeaway'),
			  array(A_CLEAR, 'staff.php?view=clearc'),
			  array(A_CHAT, 'staff.php?view=czat'),
			  array('Zablokuj/Odblokuj pisanie przez gracza na forum', 'staff.php?view=bforum'),
			  array(A_IMMU, 'staff.php?view=tags'),
			  array(A_JAIL, 'staff.php?view=jail'),
			  array(A_ADD_NEWS, 'staff.php?view=addtext'),
			  array(A_INNARCHIVE, 'staff.php?view=innarchive'),
			  array('Logi graczy', 'staff.php?view=logs'),
			  array(A_BAN_MAIL, 'staff.php?view=banmail'),
			  array('Bugtrack', 'bugtrack.php'),
			  array('Zgłoszone błędy', 'staff.php?view=bugreport'));
      }
    else
      {
	$arrLinks = array(array('Bugtrack', 'bugtrack.php'),
			  array('Zgłoszone błędy', 'staff.php?view=bugreport'));
      }
    $smarty->assign(array("Links" => $arrLinks,
			  "Panelinfo" => PANEL_INFO));
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
