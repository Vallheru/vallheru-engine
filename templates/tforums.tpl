{if $View == "newposts"}
    <ul>
        {section name=numbers loop=$Titles}
	    <li><a href="tforums.php?topic={$Tid[numbers]}">{$Titles[numbers]}</a></li>
	{/section}
    </ul>
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="tforums.php?view=newposts&amp;page={$page}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
{/if}

{if $View == "topics"}
    <form method="post" action="tforums.php?action=search">
        <input type="submit" value="{$Asearch}" /> {$Tword}: <input type="text" name="search" />
    </form>
    <table>
    <tr>
    <td width="150"><u><b>{$Ttopic}</b></u></td>
    <td width="100"><u><b>{$Tauthor}</b></u></td>
    <td width="50"><u><b>{$Treplies}</b></u></td>
    </tr>
    {section name=tforums loop=$Topic}
        <tr>
        <td>{if $Newtopic[tforums] == "Y"}<blink>N</blink> {/if}<a href="tforums.php?topic={$Topicid[tforums]}">{$Topic[tforums]}</a></td>
        <td><a href="view.php?view={$Starterid[tforums]}">{$Starter[tforums]}</a></td>
        <td>{$Replies[tforums]}</td>
        </tr>
    {/section}
    </table>
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="tforums.php?view=topics&page={$page}">{$page}</a>
	    {/if}
    	{/for}
	<br /><br />
    {/if}
    <form method="post" action="tforums.php?action=addtopic">
    {$Addtopic}:<br /><input type="text" name="title2" value="" size="40" /><br />
    <textarea name="body" cols="40" rows="10">{$Ttext}</textarea><br />
    {$Sticky}
    <input type="submit" value="{$Addtopic}" /></form><br />
    {$Thelp}
{/if}

{if $Topics != ""}
    <br />
    <table class=td width="98%" cellpadding="0" cellspacing="0">
    <tr>
    <td><b>{$Topic}</b> {$Writeby} <a href="view.php?view={$Starterid}">{$Starter}</a>{if $Starterid > "0"} ID: {$Starterid}{/if} (<a href="tforums.php?view=topics">{$Aback}</a>) (<a href="tforums.php?topic={$Topics}&amp;quotet=Y">{$Aquote}</a>) {$Delete}</td>
    </tr>
    <tr>
    <td>{$Topictext}</td>
    </tr>
    </table><br />
    {section name=tforums1 loop=$Reptext}
        <table class=td width="98%" cellpadding="0" cellspacing="0">
        <tr>
        <td><b><a href="view.php?view={$Repstarterid[tforums1]}">{$Repstarter[tforums1]}</a></b>{if $Repstarterid[tforums1] > "0"} ID: {$Repstarterid[tforums1]}{/if} {$Write}... (<a href="tforums.php?view=topics">{$Aback}</a>) (<a href="tforums.php?topic={$Topics}&amp;quote={$Rid[tforums1]}">{$Aquote}</a>) {$Action[tforums1]}</td>
        </tr>
        <tr>
        <td>{$Reptext[tforums1]}</td>
        </tr>
        </table><br />
    {/section}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="tforums.php?topic={$Topics}&amp;page={$page}">{$page}</a>
	    {/if}
    	{/for}
	<br /><br />
    {/if}
    <form method="post" action="tforums.php?reply={$Id}">
    {$Areply}:<br />
    <textarea name="rep" cols="40" rows="10">{$Rtext}</textarea><br />
    <input type="submit" value="{$Areply}" /></form><br />
    {$Thelp}
{/if}

{if $Action2 == "search"}
    {if $Amount == "0"}
        <br /><br /><center>{$Nosearch}</center><br />
    {/if}
    {if $Amount > "0"}
        {$Youfind}:<br /><br />
        {section name=number3 loop=$Ttopic}
            <a href="tforums.php?topic={$Tid[number3]}">{$Ttopic[number3]}</a><br />
        {/section}
    {/if}
    <br /><br /><a href="tforums.php?view=topics">{$Aback}</a>
{/if}
