<script src="js/chat.js"></script>
<div align="center">
<form method="post" action="chat.php?action=chat" name="chat">
<input type="text" name="msg" size="55" /> <input type="submit" value="{$Asend}" />
</form></div>
<u><b>{$Inn}</b></u><br /><br />

<div id="chatmsgs"></div>

<div align="center"><br />
<form method="post" action="chat.php?room">
    <input type="submit" value="{$Arent}" /> {$Troom} {html_options name=room options=$Roptions} {$Tgold}
</form>
</div>

{if $Rank == "Admin" || $Rank == "Karczmarka"}
    <div align="center"><br /><br />
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
{/if}
