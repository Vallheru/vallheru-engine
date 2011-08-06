<form method="post" action="{$Link}">
<table>
{if $Link == "addupdate.php?action=add"}
    <tr><td>{$Ulangsel}:</td><td><select name="addlang">
    {section name=addupdate loop=$Ulang}
        <option value="{$Ulang[addupdate]}">{$Ulang[addupdate]}</option>
    {/section}
    </select></td></tr>
{/if}
<tr><td>{$Utitle}:</td><td><input type="text" name="addtitle" value="{$Title1}" /></td></tr>
<tr><td valign="top">{$Utext}:</td><td><textarea name="addupdate" rows="15" cols="40">{$Text}</textarea></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="{$Button}" /></td></tr>
</table>
</form>
