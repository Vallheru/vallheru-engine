/**
 *   File functions:
 *   JavaScript functions for rooms
 *
 *   @name                 : room.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
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

function showChat()
{
    if (window.XMLHttpRequest)
    {
	xmlhttp = new XMLHttpRequest();
    }
    else
    {
	xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function()
    {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
	{
	    document.getElementById("chatmsgs").innerHTML = xmlhttp.responseText;
	}
    }
    xmlhttp.open("GET","roommsgs.php", true);
    xmlhttp.send();
}

function refreshChat()
{
    document.forms['chat'].elements['msg'].focus();
    showChat();
    setInterval("showChat()", 3000);
}

function sendMsg(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode == "13")
    {
	document.forms['chat'].submit();
    }
    else
    {
	var textarea = document.forms['chat'].elements['msg'];
	if (textarea.value.length > (textarea.cols * textarea.rows))
	{
	    textarea.rows += 1;
	}
    }
}

function insertAtCursor(myField, myValue, intPos) 
{
    //IE support
    myField.focus();
    if (document.selection) 
    {
	sel = document.selection.createRange();
	sel.text = myValue;
    }
    //MOZILLA/NETSCAPE support
    else if (myField.selectionStart || myField.selectionStart == '0') 
    {
	var startPos = myField.selectionStart;
	var endPos = myField.selectionEnd;
	myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
	myField.setSelectionRange(startPos + intPos, startPos + intPos);
    } 
    else 
    {
	myField.value += myValue;
    }
    
}

function formatText(button)
{
    switch (button)
    {
    case "bold":
	insertAtCursor(document.forms['chat'].elements['msg'], "[b][/b]", 3);
	break;
    case "italic":
	insertAtCursor(document.forms['chat'].elements['msg'], "[i][/i]", 3);
	break;
    case "underline":
	insertAtCursor(document.forms['chat'].elements['msg'], "[u][/u]", 3);
	break;
    case "emote":
	insertAtCursor(document.forms['chat'].elements['msg'], "**", 1);
	break;
    default:
	if(!isNaN(parseInt(button)))
	{
	    insertAtCursor(document.forms['chat'].elements['msg'], button+"=", (button+"=").length);
	}
	break;
    }
}

window.onload = refreshChat;
