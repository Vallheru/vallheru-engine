<?php
/**
 *   File functions:
 *   Resurect players
 *
 *   @name                 : resurect.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 11.10.2006
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
// $Id: resurect.php 697 2006-10-11 02:05:14Z thindil $

/**
* Check - player need ressurection?
*/
if ($player -> hp > 0)
{
    error(NOT_NEED);
}

$crneed = (50 * $player -> level);

$pdpr1 = ceil($player -> exp / 100);
$pdpr = ($pdpr1 * 2);
if ($pdpr < 0)
{
    $pdpr = $pdpr * -1;
}
$pd = ($player -> exp - $pdpr);
if ($pd < 0 && $player -> exp > 0)
{
    $pd = 0;
}

if ($crneed > $player -> credits) 
{
    error (NO_MONEY_FOR);
}
$db -> Execute("UPDATE `players` SET `exp`=".$pd.", `hp`=`max_hp`, `credits`=`credits`-".$crneed." WHERE `id`=".$player -> id);
?>
