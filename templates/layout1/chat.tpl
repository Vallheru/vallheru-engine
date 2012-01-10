<table width="100%" class="dark">
<tr><td colspan="2" align="center">
<form method="post" action="chat.php?action=chat">
[<a href="chat.php">{$Arefresh}</a>] <input type="text" name="msg" size="55" /> <input type="submit" value="{$Asend}" />
</form>
<script type="text/javascript" language="JavaScript">
document.forms['chat'].elements['msg'].focus();
</script>
</td></tr>
<tr><td width="400" valign="top">
<u><b>{$Inn}</b></u><br /><br />

<iframe src="chatmsgs.php" width="105%" height="500" name="ifr" frameborder="0"></iframe>

</td><td width="100" valign="top">
&nbsp;</td></tr>
<tr><td colspan="2" align="center">

<form method="post" action="chat.php?room">
    <input type="submit" value="{$Arent}" /> {$Troom} {html_options name=room options=$Roptions} {$Tgold}
</form>

{if $Rank == "Admin" || $Rank == "Karczmarka"}
    <br />
    <form method="post" action="chat.php?step=give">
        <input type="submit" value="{$Agive}" /> {$Chatid} <input type="text" size="5" name="giveid" />
        <select name="item">
            {section name=chat loop=$Items}
                <option value="{$Items[chat]}">{$Items[chat]}</option>
            {/section}
        </select> {$Tor} <input type="text" name="item2" size="10" /><br />
        {$Withcomm}:<br />
        <input type="text" name="innkeeper" size="55" />
    </form>
    <form method="post" action="chat.php?step=ban">
        <select name="ban">
            <option value="ban">{$Aban}</option>
            <option value="unban">{$Aunban}</option>
        </select>
		{$Chatid} <input type="text" size="5" name="banid" /> {$Ona} <input type="text" size="5" name="duration" value="1" />{$Tdays}<br />
		<textarea name="verdict"></textarea><br /><input type="submit" value="{$Asend}" />.
    </form>
	<a href="chat.php?step=clearc">{$Aprune}</a>
{/if}

</td></tr>
</table>
