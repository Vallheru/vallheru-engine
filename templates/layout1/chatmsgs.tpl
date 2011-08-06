<?xml version="1.0" encoding="{$Charset}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="refresh" content="15;url=chatmsgs.php" />
<meta http-equiv="content-type" content="text/html; charset={$Charset}" />
</head>
<body bgcolor="black" text="#FFFC9F" alink="red" link="#FFD700" vlink="#FFD700">
{if $Text[0] != ""}
    <font face="verdana" size="-2">
    {section name=player loop=$Text}
        <b>{$Author[player]} {if $Showid == "1"}{$Cid}:{$Senderid[player]}{/if}</b>: {$Text[player]}<br />
    {/section}
    </font>
{/if}
<br /><br /><br /><center><font size="-2">{$Player}<br />
{$Thereis} <b>{$Text1}</b> {$Texts}. | <b>{$Online}</b> {$Cplayers}.</font><br />
</center>
</body>
</html>

