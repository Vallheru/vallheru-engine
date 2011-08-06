{if $Health != "0" && $Action == ""}
    {$Mountinfo}<br /><br />
    <table>
    <tr><td width="100" valign="top">
    <a href="kopalnia.php">{$Amine}</a><br />
    <a href="explore.php">{$Asearch}</a><br />
    <a href="travel.php">{$Atravel}</a><br />
    <br /></td></tr>
    </table>
{/if}

{if $Health == "0" && $Action == ""}
    {$Youdead}.<br />
    - <a href="gory.php?action=back">{$Backto}</a><br />
    - <a href="gory.php?action=hermit">{$Stayhere}</a>
{/if}

{if $Action == "hermit" && $Action2 == ""}
    {$Hermit}<br /><br />
    <i>{$Hermit2}</i><br />
    - <a href="gory.php?action=hermit&amp;action2=resurect">{$Aresurect}</a> ({$Tgold} {$Goldneed} {$Goldcoins})<br />
    - <a href="gory.php?action=hermit&amp;action2=wait">{$Await}</a> ({$Waittime}) 
{/if}

{if $Action2 == "resurect"}
    {$Res1}<br /><br />
    <i>{$Res2}</i><br /><br />
    {$Res3}<br />
{/if}

{$Message}

{if $Action2 != ""}
    <br /><a href="gory.php">{$Aback}</a>
{/if}
