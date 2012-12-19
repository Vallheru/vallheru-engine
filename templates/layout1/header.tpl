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
<div><img class="bkgd" src="templates/layout1/images/pergamin.jpg" /></div>
<div style="text-align: center; margin-top: 20px;">{$Gametime}: {$Time}</div>
<div style="margin-left: 20px;">
    <div style="text-align:center; max-width: 12%; float:left;">
      <a href="stats.php">
	{if $Avatar != ""}
	  <img src="avatars/{$Avatar}" height="90px" alt="{$Nstatistics}" border="1px" class="avatar" />
	{else}
	  <img src="templates/layout1/images/noavatar.png" height="90px" alt="{$Nstatistics}" border="0" class="avatar" />
	{/if}<br />
      <b>{$Name} ({$Id})</b></a>
    </div>
    <div style="width:150px; max-width: 150px; float:left; margin-left:10px; margin-top:10px;">
      <div class="vial" title="{$Healthpts}: {$Health}/{$Maxhealth} ({$Healthper}%)">
	  <div class="subvial" style="width: {$Vial2}%; background-image:url('images/life.jpg');"></div>
      </div>
      <div class="vial" title="{$Manapts}: {$Mana} ({$Manaper}%)">
	  <div class="subvial" style="width: {$Vial3}%; background-image:url('images/mana.jpg');"></div>
      </div>
      <div class="vial" title="{$Energypts}: {$Energy}/{$Maxenergy} ({$Energyper}%)">
	  <div class="subvial" style="width: {$Vial4}%; background-image:url('images/energy.jpg');"></div>
      </div>
    </div>
    <div style="width: 12%; float: left; margin-left: 20px;">
       <div style="margin-top: 10px;"><img src="templates/layout1/images/coins.png" /> <b>{$Goldinhand}:</b> {$Gold}</div>
       <div style="margin-top: 7px;"><img src="templates/layout1/images/bank.png" /> <b>{$Goldinbank}:</b> {$Bank}</div>
       <div style="margin-top: 7px;"><img src="templates/layout1/images/mithril.png" /> <b>{$Hmithril}:</b> {$Mithril}</div>
       <div style="margin-top: 7px;"><b>{$Vallars}:</b> <a href="referrals.php?id={$Id}">{$Referals}</a></div>
    </div>
    <div style="width: 12%; float: left;">
      <ul>
	{foreach $Links.character as $link=>$text}
	    <li><a href="{$link}">{$text}</a></li>
	{/foreach}
      </ul>
    </div>
    <div style="width: 12%; float: left;">
      <ul>
	{foreach $Links.location as $link}
	    <li>{$link}</li>
	{/foreach}
	<!--<li><a href="team.php">{$Nteam}</a></li>-->
      </ul>
    </div>
    <div style="width: 12%; float: left;">
      <ul>
	<li><a href="mail.php{$Mailadd}">{$Npost}</a> [{$Unread}]</li>
	<li><a href="forums.php?view=categories">{$Nforums}</a> {$Funread}</li>
        {$Tforum}
        <li><a href="chat.php">{$Ninn} [{$Players}]</a></li>
	{$Room}
      </ul>
    </div>
    <div style="width: 12%; float: left;">
      {if $Ownlinks > 4}
          <br />
	  <select name="ownlinks" size="4" id="ownlinks" onChange="location.href=this.value;">
	    {foreach $Links.own as $link=>$text}
	        <option value="{$link}">{$text}</option>
	    {/foreach}
          </select>
      {else}
          <ul>
	    {foreach $Links.own as $link=>$text}
	        <li><a href="{$link}">{$text}</a></li>
	    {/foreach}
	  </ul>
      {/if}
    </div>
    <div style="width: 12%; float: left;">
      <ul>
	<li><a href="account.php">{$Noptions}</a></li>
        <li><a href="logout.php?did={$Id}">{$Nlogout}</a></li>
        <li><a href="{$Gameadress}/wiki/" target="_blank">{$Nhelp}</a></li>
	<li><a href="account.php?view=bugreport&amp;loc={$Title}">{$Reportbug}</a></li>
        <li><a href="map.php">{$Nmap}</a><br /><br /></li>
        {$Special}
      </ul>
    </div>
</div>
<div id="scroller">
<div align="center"><b>{$Title}</b></div>
<table style="margin-left: auto; margin-right: auto; width: 93%; max-width: 1280px;">
<tr>
  <td width="85%" valign="top">

