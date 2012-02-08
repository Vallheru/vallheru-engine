<?xml version="1.0" encoding="{$Charset}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$Gamename} :: {$Pagetitle}</title>
<link type="text/css" rel="Stylesheet" href="main.css" />
<link rel="shortcut icon" href="{$Gameadress}/favicon.png" type="image/png"/>
<meta http-equiv="Content-Type" content="text/html; charset={$Charset}" />
<link rel="alternate" type="application/xml" title="RSS" href="{$Gameadress}/rss.php" />
{$Meta}
<meta http-equiv="Content-Language" content="pl" />
{if $Metakeywords != ""}
    <meta name="keywords" content="{$Metakeywords}" />
{/if}
{if $Metadescription != ""}
    <meta name="description" content="{$Metadescription}" />
{/if}
</head>
<body>
<div><img src="vallheru.jpg" width="560" height="352" alt="Vallheru-MMORPG" title="Vallheru-MMORPG" border="0" /></div>
<div class="info">
    <span class="info">{$Ctime}: {$Time}</span>
</div>
<form method="post" action="updates.php">
    <div class="login1">
        <span class="login">{$Email}: <input type="text" name="email" /></span>
        <span class="login">{$Password}: <input type="password" name="pass" /></span>
        <span class="login2"><input type="submit" value="{$Login}" /></span><br />
        <a href="index.php?step=lostpasswd">{$Lostpasswd}</a><br />
	<a href="register.php">{$Register}</a>
    </div>
</form>
<div class="menu">[<a href="index.php">{$Welcome}</a>]</div>
<div class="menu2">[<a href="index.php?step=rules">{$Rules}</a>]</div>
<div class="menu2">[<a href="index.php?step=donate">{$Donate}</a>]</div>
<div class="menu2">[<a href="index.php?step=promote">{$Promote}</a>]</div>
<div class="menu2">[<a href="wiki/">{$Help}</a>]</div>
