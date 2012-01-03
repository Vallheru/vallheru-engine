<div align="center"><form method="post" action="room.php?action=chat">
[<a href="room.php">{$Arefresh}</a>] <input type="text" name="msg" size="55" /> <input type="submit" value="{$Asend}" />
</form></div>
<script type="text/javascript" language="JavaScript">
document.forms['chat'].elements['msg'].focus();
</script>
<u><b>{$Inn}</b></u><br /><br />
{if $Desc != ""}
    <label for="mytoggle" class="toggle">{$Adesc}</label>
    <input id="mytoggle" type="checkbox" class="toggle" checked="checked" />
    <div>{$Desc}</div>
{/if}
<br /><br />

<iframe src="roommsgs.php" width="105%" height="500" name="ifr" frameborder="0"></iframe>
