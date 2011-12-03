<?php
/**
 *   File functions:
 *   Game resets by Cron
 *
 *   @name                 : reset.php                            
 *   @copyright            : (C) 2004,2005,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 03.12.2011
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
/**
* Check remote address (allow only from localhost). If not working - write here 'localhost' or IP your server
*/
/*if ($_SERVER['REMOTE_ADDR'] != '83.142.73.186')
{
	die("Ciekawe co chciałeś tutaj zrobić?");
} */

if (!isset($_GET['step']))
  {
    die("Zapomnij o tym.");
  }
require_once("includes/config.php");
require_once('includes/resets.php');
switch ($_GET['step'])
  {
  case 'reset':
    mainreset();
    break;
  case 'revive':
    smallreset(TRUE);
    break;
  case 'energy':
    energyreset(TRUE);
    break;
  deault:
    break;
}
?>
