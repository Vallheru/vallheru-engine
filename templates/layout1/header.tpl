<?xml version="1.0" encoding="{$Charset}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$Gamename} :: {$Title}</title>
<link type="text/css" rel="stylesheet" href="templates/layout1/layout1.css" />
<link rel="shortcut icon" href="{$Gameadress}/favicon.png" type="image/png"/>
<meta http-equiv="Content-Type" content="text/html; charset={$Charset}" />
</head>

<body onload="window.status='{$Gamename}'">

<table width="90%" class="main" cellpadding="0" cellspacing="0" align="center">
    <tr>
        <td colspan="3" valign="top" align="center" class="head">
            <span class="light"><b>{$Gametime}:</b> {$Time} <b>{$Gamename}</b></span>
        </td>
    </tr>
    <tr>
        {if $Stephead != "new"}
        <td valign="top" width="15%">
            <table cellpadding="0" cellspacing="0" width="100%" class="panels">
                <tr>
                    <td align="center">
                        <span class="light"><b>{$Nstatistics}</b><br /></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="light">
                        <b><u>{$Name}</u></b> ({$Id})<br /><br />
                        </span>
                        <div class="light" align="left">
                        <b>{$Plevel}:</b> {$Level}<br />
                        <b>{$Exppts}:</b> {$Exp}/{$Expneed} ({$Percent}%)<br />
                        <img src="includes/graphbar.php?statusbar=exp" height="7" width="{$Expper}%" alt="{$Exppts}" title="{$Exppts}: {$Percent}%" style="margin-top: 2px; margin-bottom: 2px; border-style: outset; border-right: 0px;" border="2" /><img src="includes/graphbar2.php" height="7" width="{$Vial}%" alt="{$Exppts}" title="{$Exppts}: {$Percent}%" style="margin-top: 2px; margin-bottom: 2px; border-style: outset; border-left: 0px;" border="2" /><br />
                        <b>{$Healthpts}:</b> {$Health}/{$Maxhealth}<br />
                        <img src="includes/graphbar.php?statusbar=health" height="7" width="{$Barsize}%" alt="{$Healthpts}" title="{$Healthpts}: {$Healthper}%" style="margin-top: 2px; margin-bottom: 2px; border-style: outset; border-right: 0px;" border="2" /><img src="includes/graphbar2.php" height="7" width="{$Vial2}%" alt="{$Healthpts}" title="{$Healthpts}: {$Healthper}%" style="margin-top: 2px; margin-bottom: 2px; border-style: outset; border-left: 0px;" border="2" /><br />
                        <b>{$Manapts}:</b> {$Mana} <br />
                        <img src="includes/graphbar.php?statusbar=mana" height="7" width="{$Barsize2}%" alt="{$Manapts}" title="{$Manapts}: {$Manaper}%" style="margin-top: 2px; margin-bottom: 2px; border-style: outset; border-right: 0px;" border="2" /><img src="includes/graphbar2.php" height="7" width="{$Vial3}%" alt="{$Manapts}" title="{$Manapts}: {$Manaper}%" style="margin-top: 2px; margin-bottom: 2px; border-style: outset; border-left: 0px;" border="2" /><br />
                        <b>{$Energypts}:</b> {$Energy}/{$Maxenergy}<br /><br />
                        <b>{$Goldinhand}:</b> {$Gold}<br />
                        <b>{$Goldinbank}:</b> {$Bank}<br />
                        <b>{$Hmithril}:</b> {$Mithril}<br />
                        <b>{$Vallars}:</b> <a href="referrals.php?id={$Id}">{$Referals}</a><br /></div>
                    </td>
                </tr>
            </table>
            <br />
            <table cellpadding="0" cellspacing="0" width="100%" class="panels">
                <tr>
                    <td align="center">
                        <span class="light"><b>{$Navigation}</b></span>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <div class="light">
                        <ul>
                            <li><a href="stats.php">{$Nstatistics}</a></li>
                            <li><a href="zloto.php">{$Nitems}</a></li>
                            <li> <a href="equip.php">{$Nequipment}</a></li>
                            {$Spells}
                            <li><a href="log.php">{$Nlog}</a> [{$Numlog}]</li>
                            <li><a href="notatnik.php">{$Nnotes}</a><br /><br /></li>
                            {$Location}
                            {$Battle}
                            {$Hospital}
                            {$Tribe}
                            <li><a href="mail.php{$Mailadd}">{$Npost}</a> [{$Unread}]</li>
                            {$Lbank}
                            <li><a href="forums.php?view=categories">{$Nforums}</a> {$Funread}</li>
                            {$Tforum}
                            <li><a href="chat.php">{$Ninn} [{$Players}]</a></li>
			    {$Room}
                            {if $Linksfile[0] != ""}
                                {section name=hlinks loop=$Linksfile}
                                    <li><a href="{$Linksfile[hlinks]}">{$Linkstext[hlinks]}</a>{if $smarty.section.hlinks.iteration == $Linksnum}<br /><br />{/if}</li>
                                {/section}
                                <br />
                            {/if}
                            <li><a href="account.php">{$Noptions}</a></li>
                            <li><a href="logout.php?did={$Id}">{$Nlogout}</a></li>
                            <li><a href="{$Gameadress}/wiki/" target="_blank">{$Nhelp}</a></li>
			    <li><a href="account.php?view=bugreport&amp;loc={$Title}">{$Reportbug}</a></li>
                            <li><a href="map.php">{$Nmap}</a><br /><br /></li>
                            {$Special}
                        </ul>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        <td width="400" valign="top" class="center">
        {/if}
        {if $Stephead == "new"}
        <td width="100%" colspan="3" align="center" class="center">
        {/if}
            <table cellpadding="0" cellspacing="0" class="center" width="100%">
                <tr>
                    <td align="center">
                        <span class="dark"><b>{$Title}</b></span>
                    </td>
                </tr>
                <tr>
                    <td valign="bottom" class="dark" align="left">

