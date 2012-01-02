<?xml version="1.0" encoding="{$Charset}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="refresh" content="15;url=chatmsgs.php" />
<meta http-equiv="content-type" content="text/html; charset={$Charset}" />
<link type="text/css" rel="stylesheet" href="templates/layout1/layout1.css" />
</head>
<body style="background : url('images/center.jpg');">
{if $Text1 != 0}
    <font face="verdana" size="-1">
    {section name=player loop=$Text}
        <div title="{$Sdate[player]}">{if $Author[player] != ""}<b>{$Author[player]} {if $Showid == "1"}{$Cid}:{$Senderid[player]}{/if}</b>:{/if} {$Text[player]}</div>
    {/section}
    </font>
{/if}
<br /><br /><br /><center><font size="-2">{$Player}<br />
{$Thereis} <b>{$Text1}</b> {$Texts}. | <b>{$Online}</b> {$Cplayers}.</font><br />
</center>
</body>
</html>

