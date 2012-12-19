<?xml version="1.0" encoding="{$Charset}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$Gamename} :: {$Title}</title>
<link type="text/css" rel="stylesheet" href="css/{$Style}" />
<link type="text/css" rel="stylesheet" href="temporary.css" />
<link rel="shortcut icon" href="{$Gameadress}/favicon.png" type="image/png"/>
<meta http-equiv="Content-Type" content="text/html; charset={$Charset}" />
</head>
<body onload="window.status='{$Gamename}'">

<table width="90%" class="td" cellpadding="0" cellspacing="0" align="center">
    <tr>
        <td colspan="3" valign="top" align="center">
            <b>{$Gametime}:</b> {$Time} <b>{$Gamename}</b>
        </td>
    </tr>
    <tr>
        {if $Stephead != "new"}
        <td valign="top" width="15%">
            <table cellpadding="0" cellspacing="0" class="td" width="100%">
                <tr>
                    <td align="center">
                        <b>{$Nstatistics}</b>
                    </td>
                </tr>
		{if $Avatar != ""}
		<tr>
		    <td align="center">
		      <img src="{$Avatar}" width="{$Awidth}" height="{$Aheight}" />
		    </td>
		</tr>
		{/if}
                <tr>
                    <td>
                        <center><b><u>{$Name}</u></b> ({$Id})</center><br />
                        <b>{$Healthpts}:</b> {$Health}/{$Maxhealth}<br />
                        {if $Graphbar == "Y"}
			    <div class="vial" title="{$Healthpts}: {$Health}/{$Maxhealth} ({$Healthper}%)">
			      <div class="subvial" style="width: {$Vial2}%; background-image:url('images/life.jpg');"></div>
			    </div>
                        {/if}
                        <b>{$Manapts}:</b> {$Mana} <br />
                        {if $Graphbar == "Y"}
			    <div class="vial" title="{$Manapts}: {$Mana} ({$Manaper}%)">
			      <div class="subvial" style="width: {$Vial3}%; background-image:url('images/mana.jpg');"></div>
			    </div>
                        {/if}
                        <b>{$Energypts}:</b> {$Energy}/{$Maxenergy}<br />
			{if $Graphbar == "Y"}
			    <div class="vial" title="{$Energypts}: {$Energy}/{$Maxenergy} ({$Energyper}%)">
			      <div class="subvial" style="width: {$Vial4}%; background-image:url('images/energy.jpg');"></div>
			    </div>
                        {/if}<br />
                        <b>{$Goldinhand}:</b> {$Gold}<br />
                        <b>{$Goldinbank}:</b> {$Bank}<br />
                        <b>{$Hmithril}:</b> {$Mithril}<br />
                        <b>{$Vallars}:</b> <a href="referrals.php?id={$Id}">{$Referals}</a><br />
                    </td>
                </tr>
            </table>
            <br />
            <table cellpadding="0" cellspacing="0" class="td" width="100%">
                <tr>
                    <td align="center">
                        <b>{$Navigation}</b>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <ul>
			    {foreach $Links.character as $link=>$text}
			        <li><a href="{$link}">{$text}</a></li>
			    {/foreach}
			    <br />
                            {foreach $Links.location as $link}
			        <li>{$link}</li>
			    {/foreach}
			    <!--<li><a href="team.php">{$Nteam}</a></li>-->
			    <br />
                            <li><a href="mail.php{$Mailadd}">{$Npost}</a> [{$Unread}]</li>
                            <li><a href="forums.php?view=categories">{$Nforums}</a> {$Funread}</li>
                            {$Tforum}
                            <li><a href="chat.php">{$Ninn} [{$Players}]</a></li>
			    {$Room}
			    {foreach $Links.own as $link=>$text}
			        <li><a href="{$link}">{$text}</a></li>
			    {/foreach}
			    <br />
                            <li><a href="account.php">{$Noptions}</a></li>
                            <li><a href="logout.php?did={$Id}">{$Nlogout}</a></li>
                            <li><a href="{$Gameadress}/wiki/" target="_blank">{$Nhelp}</a></li>
			    <li><a href="account.php?view=bugreport&amp;loc={$Title}">{$Reportbug}</a></li>
                            <li><a href="map.php">{$Nmap}</a><br /><br /></li>
                            {$Special}
                        </ul>
                    </td>
                </tr>
            </table>
        </td>
        <td valign="top">
        {/if}
        {if $Stephead == "new"}
        <td width="100%" colspan="3" align="center">
        {/if}
            <table cellpadding="0" cellspacing="0" class="td" width="100%">
                <tr>
                    <td align="center">
                        {$Title}
                    </td>
                </tr>
                <tr>
                    <td align="left">

