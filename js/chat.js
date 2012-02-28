/**
 *   File functions:
 *   JavaScript functions for chat
 *
 *   @name                 : chat.js                          
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 28.02.2012
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

function showChat(intBottom)
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
	    if (window.location.search == '?action=chat' && parseInt(intBottom) == 1)
	    {
		window.scrollTo(0,document.body.scrollHeight);
	    }
	}
    }
    xmlhttp.open("GET","chatmsgs.php", true);
    xmlhttp.send();
}

function refreshChat()
{
    document.forms['chat'].elements['msg'].focus();
    showChat('1');
    setInterval("showChat('0')", 3000);
}

function sendMsg(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode == "13")
    {
	document.forms['chat'].submit();
    }
}

function insertAtCursor(myField, myValue) 
{
    //IE support
    if (document.selection) 
    {
	myField.focus();
	sel = document.selection.createRange();
	sel.text = myValue;
    }
    //MOZILLA/NETSCAPE support
    else if (myField.selectionStart || myField.selectionStart == '0') 
    {
	var startPos = myField.selectionStart;
	var endPos = myField.selectionEnd;
	myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
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
	insertAtCursor(document.forms['chat'].elements['msg'], "[b][/b]");
	break;
    case "italic":
	insertAtCursor(document.forms['chat'].elements['msg'], "[i][/i]");
	break;
    case "underline":
	insertAtCursor(document.forms['chat'].elements['msg'], "[u][/u]");
	break;
    case "emote":
	insertAtCursor(document.forms['chat'].elements['msg'], "**");
	break;
    default:
	if(!isNaN(parseInt(button)))
	{
	    insertAtCursor(document.forms['chat'].elements['msg'], button+"=");
	}
	break;
    }
}

function checkCost()
{
    var intValue = document.getElementById("room").value;
    if((parseFloat(intValue) == parseInt(intValue)) && !isNaN(intValue) && (parseInt(intValue) > 0))
    {
	if (parseInt(intValue) > 100)
	{
	    intValue = 100;
	}
	var intCost = intValue * 100;
	document.getElementById("rcost").innerHTML = Math.round(intCost);
    }
    else
    {
	document.getElementById("rcost").innerHTML = "0";
    }
}

window.onload = refreshChat;
