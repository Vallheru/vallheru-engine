/**
 *   File functions:
 *   JavaScript functions for bank
 *
 *   @name                 : bank.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 29.03.2012
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

function showIdField(intValue, strNumber)
{
    if (parseInt(intValue) == 0)
    {
	document.getElementById("pid" + strNumber).style.visibility = "visible";
    }
    else
    {
	document.getElementById("pid" + strNumber).style.visibility = "hidden";
    }
}

function showAfield(strNumber)
{
    if (document.getElementById("addall" + strNumber).checked)
    {
	document.getElementById("amount" + strNumber).style.visibility = "hidden";
    }
    else
    {
	document.getElementById("amount" + strNumber).style.visibility = "visible";
    }
}
