<script src="js/room.js"></script>
{if $Oldchat == 'Y'}
<div align="center">
    <form method="post" action="room.php?action=chat" name="chat">
        <input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id)" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id)" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id)" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id)" /><br />
	<textarea name="msg" rows="1" cols="60" onKeyDown="javascript:return sendMsg(event);"></textarea><br />
	<input type="submit" value="{$Asend}" /> {if $Aowner != ""} {$Tas} {html_options name=person options=$Toptions}{/if}<br />
	[<a href="room.php">{$Arefresh}</a>]
    </form>
</div>
<a name="thebottom"></a>
{/if}
<div align="center"><u><b>{$Inn}</b></u><br /><br />
{if $Desc != ""}
    <label for="mytoggle" class="toggle">{$Adesc}</label>
    <input id="mytoggle" type="checkbox" class="toggle" {$Checked} />
    <div>{$Desc}</div>
{/if}</div>
<br /><br />

<div id="chatmsgs"></div>

{if $Oldchat == 'N'}
<div align="center">
    <form method="post" action="room.php?action=chat" name="chat">
        <input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id)" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id)" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id)" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id)" /><br />
	<textarea name="msg" rows="1" cols="60" onKeyDown="javascript:return sendMsg(event);"></textarea><br />
	<input type="submit" value="{$Asend}" /> {if $Aowner != ""} {$Tas} {html_options name=person options=$Toptions}{/if}<br />
	[<a href="room.php">{$Arefresh}</a>]
    </form>
</div>
<a name="thebottom"></a>
{/if}

<a href="room.php?step=quit">{$Aleft}</a><br /><br />

<div align="center">
    <label for="mytoggle3" class="toggle">{$Tinroom}</label>
    <input id="mytoggle3" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        {foreach $Poptions as $Plroom}
	    <a href="view.php?view={$Plroom@key}">{$Plroom}</a>
	{/foreach}
    </div>
</div><br />

{if $Aowner != ""}
    <div align="center">
    <label for="mytoggle2" class="toggle">{$Aowner}</label>
    <input id="mytoggle2" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="post" action="room.php?step=admin&amp;action=invite">
	    <input type="submit" value="{$Ainv}" /> {$Tid} <input type="text" name="pid" size="5" /> {$Troom}
	</form><br />
        <form method="post" action="room.php?step=admin&amp;action=remove">
	    <input type="submit" value="{$Akick}" /> {$Tid2} {html_options name=pid options=$Poptions} {$Froom}
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
	</form><br />
	{if $Dnpc == "Y"}
	    <form method="post" action="room.php?step=admin&amp;action=dnpc">
	        <input type="submit" value="{$Aremove}" /> {$Tnpc2} <select name="npc">
		    {foreach $Noptions as $npc}
		        <option value="{$npc}">{$npc}</option>
		    {/foreach}
		</select>
	    </form><br />
	{/if}
	{$Trent}<br />
	<form method="post" action="room.php?step=admin&amp;action=rent">
	    <input type="submit" value="{$Arent}" /> {$Trent2} {html_options name=rent options=$Roptions} {$Trent3}
	</form><br />
    </div>
    </div><br />
{/if}
