{if $Health != "0" && $Action == "" && $Action2 == ""}
    {$Forestinfo}<br /><br />
    <table>
    <tr><td width="100" valign="top">
    <a href="lumberjack.php">{$Alumberjack}</a><br />
    <a href="explore.php">{$Aexplore}</a><br />
    <a href="las.php?action2=city">{$Aelfcity}</a><br />
    <a href="travel.php">{$Atravel}</a><br /><br /></td></tr>
    </table>
{/if}

{if $Health == "0" && $Action == ""}
    {$Youdead}.<br />
    - <a href="las.php?action=back">{$Backto}</a><br />
    - <a href="las.php?action=hermit">{$Stayhere}</a>
{/if}

{if $Action == "hermit" && $Action2 == ""}
    {$Hermit}<br /><br />
    <i>{$Hermit2}</i><br />
    - <a href="las.php?action=hermit&amp;action2=resurect">{$Aresurect}</a> ({$Tgold} {$Goldneed} {$Goldcoins})<br />
    - <a href="las.php?action=hermit&amp;action2=wait">{$Await}</a> ({$Waittime}) 
{/if}

{if $Action2 == "resurect"}
    {$Res1}<br /><br />
    <i>{$Res2}</i><br /><br />
    {$Res3}<br />
{/if}

{$Message}

{if $Action2 != ""}
    <br /><a href="las.php">{$Aback}</a>
{/if}
