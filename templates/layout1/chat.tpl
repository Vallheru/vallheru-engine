<script src="js/chat.js"></script>

{if $Oldchat == 'Y'}
<div align="center">
    <form method="post" action="chat.php" name="chat">
        <input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id)" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id)" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id)" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id)" /><br />
	<textarea name="msg" rows="1" cols="60" onKeyDown="javascript:return sendMsg(event);"></textarea><br />
	<input type="submit" value="{$Asend}" /> [<a href="chat.php">{$Arefresh}</a>]
    </form>
</div>
<a name="thebottom"></a>
{/if}

<u><b>{$Inn}</b></u><br /><br />

<div id="chatmsgs"></div>

{if $Oldchat == 'N'}
<div align="center">
    <form method="post" action="chat.php" name="chat">
        <input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id)" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id)" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id)" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id)" /><br />
	<textarea name="msg" rows="1" cols="60" onKeyDown="javascript:return sendMsg(event);"></textarea><br />
	<input type="submit" value="{$Asend}" /> [<a href="chat.php">{$Arefresh}</a>]
    </form>
</div>
<a name="thebottom"></a>
{/if}

<div align="center"><br /><br /><br />
<form method="post" action="chat.php?room">
    <input type="submit" value="{$Arent}" /> {$Troom} <input type="text" name="room" id="room" size="5" value="0" onChange="checkCost()" /> {$Tfor} <span id="rcost">0</span> {$Tgold}
</form>
</div>

{if $Rank == "Admin" || $Rank == "Karczmarka" || $Rank == "Staff"}
    <br /><br /><div align="center">
    <label for="mytoggle2" class="toggle">{$Apanel}</label>
    <input id="mytoggle2" type="checkbox" class="toggle" {$Checked} />
    <div align="center"><br />
    <form method="post" action="chat.php?step=give">
        <input type="submit" value="{$Agive}" /> {$Chatid2} <input type="text" size="5" name="giveid" />
        <select name="item">
            {section name=chat loop=$Items}
                <option value="{$Items[chat]}">{$Items[chat]}</option>
            {/section}
        </select> {$Tor} <input type="text" name="item2" size="10" /><br />
        {$Withcomm}:<br />
        <input type="text" name="innkeeper" size="55" />
    </form><br />
    <form method="post" action="chat.php?step=ban">
        <select name="ban">
            <option value="ban">{$Aban}</option>
            <option value="unban">{$Aunban}</option>
        </select>
        {$Chatid} <input type="text" size="5" name="banid" /> {$Ona} <input type="text" size="5" name="duration" value="1" />{$Tdays}<br />
		<textarea name="verdict"></textarea><br /><input type="submit" value="{$Asend}" />.
    </form>
    <a href="chat.php?step=clearc">{$Aprune}</a>
    </div>
    </div>
{/if}
