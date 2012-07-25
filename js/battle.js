/**
 *   File functions:
 *   JavaScript functions for battle arena
 *
 *   @name                 : battle.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 25.07.2012
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

function countEnergy(intLevel, intIndex)
{
    var amount = parseInt(document.forms["fight" + intIndex].elements["amount"].value);
    if (amount > 20)
    {
	amount = 0;
    }
    var times = parseInt(document.forms["fight" + intIndex].elements["times"].value);
    if (times > 20)
    {
	times = 0;
    }
    var scost = amount * times * Math.floor(1 + (intLevel / 20));
    var tcost = amount * Math.floor(1 + (intLevel / 20));
    document.getElementById("mon" + intIndex).innerHTML = scost + "/" + tcost;
}
