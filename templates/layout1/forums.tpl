{if $View == "categories"}
    <table class="dark"><tr><td><b><u>{$Tcategory}</u></b></td><td><b><u>{$Ttopics}</u></b></td></tr>
    {section name=number loop=$Name}
        <tr>
        <td><a href="forums.php?topics={$Id[number]}"><u>{$Name[number]}</u></a></td>
        <td><a href="forums.php?topics={$Id[number]}">{$Topics1[number]}</a></td>
        </tr>
        <tr>
        <td><a href="forums.php?topics={$Id[number]}"><i>{$Description[number]}</i></a></td>
        </tr>
        <tr>
        <td colspan="2"><hr /></td>
        </tr>
    {/section}
    </table>
{/if}

{if $Topics != ""}
    <a href="forums.php?view=categories">{$Aback}</a> {$Tocategories}.<br /><br />
    <form method="post" action="forums.php?action=search">
        <input type="submit" value="{$Asearch}" /> {$Tword}: <input type="text" name="search" />
        <input type="hidden" name="catid" value="{$Category}" />
    </form>
    <table class="dark"><tr><td width="150"><u><b>{$Ttopic}</b></u></td><td width="100"><u><b>{$Tauthor}</b></u></td><td width="50"><b><u>{$Treplies}</u></b></td></tr>
    {section name=number1 loop=$Topic1}
        <tr>
        <td>{if $Newtopic[number1] == "Y"}<blink>N</blink> {/if}<a href="forums.php?topic={$Id[number1]}">{$Topic1[number1]}</a></td>
        <td>{$Starter1[number1]}</td>
        <td>{$Replies1[number1]}</td>
        </tr>
    {/section}
    </table>
    <form method="post" action="forums.php?action=addtopic">
        {$Addtopic}:<br />
        <input type="text" name="title2" value="Temat" size="40" /><br />
        <textarea name="body" cols="40" rows="10">{$Ttext}</textarea><br />
        <input type="hidden" name="catid" value="{$Category}" />
        {if $Rank == "Admin" || $Rank == "Staff"}
            <input type="checkbox" name="sticky" />{$Tsticky}<br />
        {/if}
        <input type="submit" value="{$Addtopic}" />
    </form><br /><br />
    <a href="forums.php?view=categories">{$Aback}</a> {$Tocategories}.
{/if}

{if $Topic != ""}
    <br />
    <table class="td" width="98%" cellpadding="0" cellspacing="0">
    <tr>
    <td><b>{$Topic2}</b> {$Writeby} {$Starter} {$Wid} {$Playerid} (<a href="forums.php?topics={$Category}">{$Aback}</a>) (<a href="forums.php?topic={$Topic}&amp;quotet=Y">{$Aquote}</a>) {$Action}
    </td>
    </tr>
    <tr>
    <td>{$Ttext}</td>
    </tr>
    </table><br />
    <center>
    {section name=number2 loop=$Rtext}
        <table class="td" width="98%" cellpadding="0" cellspacing="0">
        <tr>
        <td><b>{$Rstarter[number2]}</b> {$Wid} {$Rplayerid[number2]} {$Write}... (<a href="forums.php?topics={$Category}">{$Aback}</a>) (<a href="forums.php?topic={$Topic}&amp;quote={$Rid[number2]}">{$Aquote}</a>)
         {$Action2[number2]}
        </td>
        </tr>
        <tr>
        <td>{$Rtext[number2]}</td></tr></table><br />
    {/section}
    </center>
    <form method="post" action="forums.php?reply={$Id}">
    {$Areply}:<br />
    <textarea name="rep" cols="40" rows="10">{$Rtext2}</textarea><br />
    <input type="submit" value="{$Areply}" />
    </form>
{/if}

{if $Action3 == "search"}
    {if $Amount == "0"}
        <br /><br /><center>{$Nosearch}</center><br />
    {/if}
    {if $Amount > "0"}
        {$Youfind}:<br /><br />
        {section name=number3 loop=$Ttopic}
            <a href="forums.php?topic={$Tid[number3]}">{$Ttopic[number3]}</a><br />
        {/section}
    {/if}
    <br /><br /><a href="forums.php?topics={$Category}">{$Aback}</a>
{/if}
