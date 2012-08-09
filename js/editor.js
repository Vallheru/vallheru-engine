/**
 *   File functions:
 *   JavaScript functions for text entries
 *
 *   @name                 : editor.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 09.08.2012
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

function insertAtCursor(myField, openTag, endTag, intPos) 
{
    //IE support
    myField.focus();
    if (document.selection) 
    {
	sel = document.selection.createRange();
	sel.text = openTag + sel.text + endTag;
    }
    //MOZILLA/NETSCAPE support
    else if (myField.selectionStart || myField.selectionStart == '0') 
    {
	var startPos = myField.selectionStart;
	var endPos = myField.selectionEnd;
	myField.value = myField.value.substring(0, startPos) + openTag + myField.value.substring(startPos, endPos) + endTag + myField.value.substring(endPos, myField.value.length);
	myField.setSelectionRange(startPos + intPos, startPos + intPos);
    } 
    else 
    {
	myField.value += openTag + endTag;
    }
    
}

function formatText(button, frmname, frmfield)
{
    switch (button)
    {
    case "bold":
	insertAtCursor(document.forms[frmname].elements[frmfield], "[b]", "[/b]", 3);
	break;
    case "italic":
	insertAtCursor(document.forms[frmname].elements[frmfield], "[i]", "[/i]", 3);
	break;
    case "underline":
	insertAtCursor(document.forms[frmname].elements[frmfield], "[u]", "[/u]", 3);
	break;
    case "emote":
	insertAtCursor(document.forms[frmname].elements[frmfield], "[b]*", "*[/b]", 4);
	break;
    case "center":
	insertAtCursor(document.forms[frmname].elements[frmfield], "[center]", "[/center]", 8);
	break;
    case "quote":
	insertAtCursor(document.forms[frmname].elements[frmfield], "[quote]", "[/quote]", 7);
	break;
    case "color":
	var color = document.forms[frmname].elements["colors"].value;
	var opentag = "[color " + color + "]";
	insertAtCursor(document.forms[frmname].elements[frmfield], opentag, "[/color]", opentag.length);
	break;
    default:
	break;
    }
}
