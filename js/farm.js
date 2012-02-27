/**
 *   File functions:
 *   JavaScript functions for farm
 *
 *   @name                 : farm.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 27.02.2012
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

function checkcost(intAmount, strType, intHave)
{
    var intValue = document.getElementById(strType + "amount").value;
    if((parseFloat(intValue) == parseInt(intValue)) && !isNaN(intValue) && (parseInt(intValue) > 0))
    {
	var i;
	var intCost = 0;
	if (strType == 'l')
	{
	    var intSum = parseInt(intValue) + parseInt(intAmount);
	    if (intSum > 100)
	    {
		intValue = 100 - parseInt(intAmount);
	    }
	    for (i = 0; i < intValue; i++)
	    {
		intCost = intCost + (20 * (parseInt(intAmount) + i));
	    }
	}
	else
	{
	    var intSum = parseInt(intValue) + parseInt(intHave);
	    if (intSum > intAmount)
	    {
		intValue = parseInt(intAmount) - parseInt(intHave);
	    }
	    intCost = intValue * 1000;
	}
	document.getElementById(strType + "cost").innerHTML = Math.round(intCost);
    }
    else
    {
	document.getElementById(strType + "cost").innerHTML = "0";
    }
}
