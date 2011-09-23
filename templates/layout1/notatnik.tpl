{if $Action == ""}
    {$Notesinfo}<br /><br />
    {section name=notes loop=$Noteid}
        {$Ntime}:{$Notetime[notes]}<br />{$Notetext[notes]}<br /> (<a href="notatnik.php?akcja=skasuj&amp;nid={$Noteid[notes]}">{$Adelete}</a>) (<a href="notatnik.php?akcja=edit&amp;nid={$Noteid[notes]}">{$Aedit}</a>)<br /><br />
    {/section}
    <br /><br /><a href="notatnik.php?akcja=dodaj">({$Aadd})</a>
{/if}

{if $Action == "edit" || $Action == "dodaj"}
    <form method="post" action="notatnik.php?akcja={$Nlink}">
    <table class="dark">
    <tr><td valign="top">{$Note}:</td><td><textarea name="body" rows="20" cols="40">{$Ntext}</textarea></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Asave}" /></td></tr>
    </table>
    </form>
{/if}
