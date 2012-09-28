/**
 *   File functions:
 *   JavaScript functions for equipment
 *
 *   @name                 : equip.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 28.09.2012
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

//Check all checkboxes
function checkall(arrFields)
{
    for (i = 0; i < arrFields.length; i++)
    {
	arrFields[i].checked = true;
    }
}

//Uncheck all checkboxes
function uncheckall(arrFields)
{
    for (i = 0; i < arrFields.length; i++)
    {
	arrFields[i].checked = false;
    }
}

//Reverse selection of checkboxes
function changeselected(arrFields)
{
    for (i = 0; i < arrFields.length; i++)
    {
	if (arrFields[i].checked)
	{
	    arrFields[i].checked = false;
	}
	else
	{
	    arrFields[i].checked = true;
	}
    }
}
