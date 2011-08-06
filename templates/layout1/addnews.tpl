<form method="post" action="addnews.php?action=add">
<table class="dark">
<tr><td>{$Nlangsel}:</td><td><select name="addlang">
{section name=addnews loop=$Nlang}
    <option value="{$Nlang[addnews]}">{$Nlang[addnews]}</option>
{/section}
</select></td></tr>
<tr><td>{$Ntitle}:</td><td><input type="text" name="addtitle" /></td></tr>
<tr><td valign="top">{$Ntext}:</td><td><textarea name="addnews" rows="15" cols="40"></textarea></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="{$Nadd}" /></td></tr>
</table>
</form>
