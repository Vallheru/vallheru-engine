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

window.onload = refreshChat;
