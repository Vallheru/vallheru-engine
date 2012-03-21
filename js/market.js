/**
 *   File functions:
 *   JavaScript functions for markets
 *
 *   @name                 : market.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 21.03.2012
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

function countPrice(intCost, intAmount, intAamount)
{
    if (parseInt(intAmount) > 0 && parseInt(intAmount) <= parseInt(intAamount))
    {
	document.getElementById("acost").innerHTML = "za " + (parseInt(intCost) * parseInt(intAmount)) + " sztuk złota.";
    }
    else
    {
	document.getElementById("acost").innerHTML = '';
    }
}

function buyAll(intAmount, intCost)
{
    document.forms["buy"].elements["amount"].value = intAmount;
    countPrice(intCost, intAmount, intAmount);
    return false;
}
