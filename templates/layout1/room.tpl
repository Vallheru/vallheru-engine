<div align="center"><form method="post" action="room.php?action=chat" name="chat">
[<a href="room.php">{$Arefresh}</a>] <input type="text" name="msg" size="55" /> <input type="submit" value="{$Asend}" />{if $Aowner != ""} {$Tas} {html_options name=person options=$Toptions}{/if}
</form></div><br /><br />
<script type="text/javascript" language="JavaScript">
document.forms['chat'].elements['msg'].focus();
</script>
<div align="center"><u><b>{$Inn}</b></u><br /><br />
{if $Desc != ""}
    <label for="mytoggle" class="toggle">{$Adesc}</label>
    <input id="mytoggle" type="checkbox" class="toggle" checked="checked" />
    <div>{$Desc}</div>
{/if}</div>
<br /><br />

<iframe src="roommsgs.php" width="105%" height="500" name="ifr" frameborder="0"></iframe>

<a href="room.php?step=quit">{$Aleft}</a><br /><br />

<div align="center">
    <label for="mytoggle3" class="toggle">{$Tinroom}</label>
    <input id="mytoggle3" type="checkbox" class="toggle" checked="checked" />
    <div><br />
        {foreach $Poptions as $Plroom}
	    <a href="view.php?view={$Plroom@key}">{$Plroom}</a>
	{/foreach}
    </div>
</div><br />

{if $Aowner != ""}
    <div align="center">
    <label for="mytoggle2" class="toggle">{$Aowner}</label>
    <input id="mytoggle2" type="checkbox" class="toggle" checked="checked" />
    <div><br />
        <form method="post" action="room.php?step=admin&amp;action=invite">
	    <input type="submit" value="{$Ainv}" /> {$Tid} <input type="text" name="pid" size="5" /> {$Troom}
	</form><br />
        <form method="post" action="room.php?step=admin&amp;action=remove">
	    <input type="submit" value="{$Akick}" /> {$Tid} {html_options name=pid options=$Poptions} {$Froom}
	</form><br />
	<form method="post" action="room.php?step=admin&amp;action=admin">
	    <select name="action">
	        <option value="0">{$Aadd}</option>
		<option value="1">{$Aremove}</option>
	    </select>
	    {$Tid} <input type="text" name="pid" size="5" /> {$Towners} <input type="submit" value="{$Amake}" />
	</form><br />
	<form method="post" action="room.php?step=admin&amp;action=color">
	     <input type="submit" value="{$Amake}" /> {html_options name=color options=$Coptions} {$Tcolor} {html_options name=pid options=$Poptions}
	</form><br />
	<form method="post" action="room.php?step=admin&amp;action=desc">
	    <input type="submit" value="{$Achange}" /> {$Tdesc} <br /><textarea name="desc">{$Desc2}</textarea>
	</form><br />
	<form method="post" action="room.php?step=admin&amp;action=name">
	    <input type="submit" value="{$Achange}" /> {$Tname} <input type="text" name="rname" value="{$Inn}" />
	</form><br />
	<form method="post" action="room.php?step=admin&amp;action=npc">
	    <input type="submit" value="{$Aadd}" /> {$Tnpc} <input type="text" name="npc" />
	</form>
    </div>
    </div><br />
{/if}
