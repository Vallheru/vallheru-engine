/**
 *   File functions:
 *   JavaScript functions for school
 *
 *   @name                 : train.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.6
 *   @since                : 10.07.2012
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

function checkcost(strRace, strClass, Strength, Agility, intInt, Speed, Cond, Wisdom, fltStat)
{
    var intValue = document.getElementById("rep").value;
    if((parseFloat(intValue) == parseInt(intValue)) && !isNaN(intValue) && (parseInt(intValue) > 0))
    {
	var strStat = document.getElementById("train").value;
	var fltRepeat = 0;
	var intCost = 0;
	var Cost = 0;
	var fltGain = 0;
	switch (strStat)
	{
	case 'strength':
	    Cost = Strength;
	    break;
	case 'agility':
	    Cost = Agility;
	    break;
	case 'inteli':
	    Cost = intInt;
	    break;
	case 'szyb':
	    Cost = Speed;
	    break;
	case 'wytrz':
	    Cost = Cond;
	    break;
	case 'wisdom':
	    Cost = Wisdom;
	    break;
	default:
	    break;
	}
	if (strRace == 'Człowiek')
	{
	    fltRepeat = intValue * 0.3;
	    fltGain = 0.3;
	}
	else if (strRace == 'Gnom' && (strStat == 'agility' || strStat == 'wytrz'))
	{
	    fltRepeat = intValue * 0.3;
	    fltGain = 0.3;
	}
	else if (strRace == 'Elf' && (strStat == 'strength' || strStat == 'wytrz'))
	{
	    fltRepeat = intValue * 0.4;
	    fltGain = 0.4;
	}
	else if (strRace == 'Elf' && (strStat == 'agility' || strStat == 'szyb'))
	{
	    fltRepeat = intValue * 0.2;
	    fltGain = 0.2;
	}
	else if (strRace == 'Krasnolud' && (strStat == 'strength' || strStat == 'wytrz'))
	{
	    fltRepeat = intValue * 0.2;
	    fltGain = 0.2;
	}
	else if (strRace == 'Krasnolud' && (strStat == 'agility' || strStat == 'szyb'))
	{
	    fltRepeat = intValue * 0.4;
	    fltGain = 0.4;
	}
	else if (strRace == 'Jaszczuroczłek' && (strStat == 'szyb' || strStat == 'strength'))
	{
	    fltRepeat = intValue * 0.2;
	    fltGain = 0.2;
	}
	else if (strRace == 'Jaszczuroczłek' && (strStat == 'wytrz' || strStat == 'agility'))
	{
	    fltRepeat = intValue * 0.4;
	    fltGain = 0.4;
	}
	else if (strRace == 'Hobbit' && (strStat == 'wytrz' || strStat == 'agility'))
	{
	    fltRepeat = intValue * 0.2;
	    fltGain = 0.2;
	}
	else if ((strRace == 'Hobbit' || strRace == 'Gnom') && (strStat == 'szyb' || strStat == 'strength'))
	{
	    fltRepeat = intValue * 0.4;
	    fltGain = 0.4;
	}
	if (strStat == 'inteli' || strStat == 'wisdom')
	{
	    if (strClass == 'Wojownik')
	    {
		fltRepeat = intValue * 0.4;
		fltGain = 0.4;
	    }
	    else if (strClass == 'Rzemieślnik' || strClass == 'Barbarzyńca' || strClass == 'Złodziej')
	    {
		fltRepeat = intValue * 0.3;
		fltGain = 0.3;
	    }
	    else
	    {
		fltRepeat = intValue * 0.2;
		fltGain = 0.2;
	    }
	}
	if (strRace == 'Gnom' && strStat == 'wisdom')
	{
	    fltRepeat = intValue * 0.4;
	    fltGain = 0.4;
	}
	for (i = 0; i < intValue; i++)
	{
	    intCost = intCost + Math.round(Cost / fltStat);
	    Cost = Cost + fltGain;
	}
	document.getElementById("info").innerHTML = "Koszt szkolenia to " + fltRepeat.toFixed(1) + " energii oraz " + Math.round(intCost) + " sztuk złota.";
    }
    else
    {
	document.getElementById("info").innerHTML = "Nieprawidłowa ilość treningów";
    }
}
