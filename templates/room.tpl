<table width="100%">
<tr><td colspan="2" align="center">
<form method="post" action="room.php?action=chat" name="chat">
[<a href="room.php">{$Arefresh}</a>] <input type="text" name="msg" size="55" /> <input type="submit" value="{$Asend}" />
</form>
<script type="text/javascript" language="JavaScript">
document.forms['chat'].elements['msg'].focus();
</script>
</td></tr>
<tr><td width="95%" valign="top">
<u><b>{$Inn}</b></u><br /><br />
{if $Desc != ""}
    <label for="mytoggle" class="toggle">{$Adesc}</label>
    <input id="mytoggle" type="checkbox" class="toggle" />
    <div>{$Desc}</div>
{/if}
<br /><br />

<iframe src="roommsgs.php" width="105%" height="500" name="ifr" frameborder="0"></iframe>

</td><td width="100" valign="top">
&nbsp;</td></tr>
</table>
