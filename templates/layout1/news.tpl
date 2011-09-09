{if $View == "" && $Step == ""}
    {if $Title1 == ""}
        {$Nonews}
    {else}
        <b>{$Title1}</b> {$Writeby} <b>{$Starter}</b> ({$Pdate})...<br /><br />
    	"{$News}"<br />
    	<a href="news.php?step=comments&amp;text={$Newsid}">{$Acomments}</a>: {$Comments}<br /><br />
    	(<a href="news.php?view=all">{$Last10}</a>)
    {/if}
    <br /><br />
    <a href="news.php?step=add">{$Aaddnews}</a> ({$Twaiting}: {$Waiting} | {$Taccepted}: {$Accepted})
{/if}

{if $View == "all" && $Step == ""}
    {section name=news loop=$Title1}
        <b>{$Title1[news]}</b> {$Writeby} <b>{$Starter[news]}</b> ({$Newsdate[news]})...<br /><br />
        "{$News[news]}"<br />
        <a href="news.php?step=comments&amp;text={$Newsid[news]}">{$Acomments}</a>: {$Comments[news]}<br /><br />
    {/section}
{/if}

{if $Step == "comments"}
    {if $Amount == "0"}
        <center>{$Nocomments}</center>
    {/if}
    {if $Amount > "0"}
        {section name=update loop=$Tauthor}
            <b><a href="view.php?view={$Taid[update]}">{$Tauthor[update]}</a></b> {if $Tdate[update] != ""} ({$Tdate[update]}) {/if}{$Writed}: {if $Rank == "Admin" || $Rank == "Staff"} (<a href="news.php?step=comments&amp;action=delete&amp;cid={$Cid[update]}">{$Adelete}</a>) {/if}<br />
            {$Tbody[update]}<br /><br />
        {/section}
    {/if}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="news.php?step=comments&text={$Text}&page={$page}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
    <br /><br /><center>
    <form method="post" action="news.php?step=comments&amp;text={$Text}">
        {$Addcomment}:<textarea name="body" rows="20" cols="50"></textarea><br />
        <input type="submit" value="{$Aadd}" />
    </form></center>
    <br /><br />
    <a href="news.php">{$Aback}</a>
{/if}

{if $Step == "add"}
    {$Addinfo}<br />
    <form method="post" action="news.php?step=add&amp;step2=add">
        {$Tlang}: <select name="lang">
            {section name=library loop=$Llang}
                <option value="{$Llang[library]}">{$Llang[library]}</option>
            {/section}
        </select><br />
        {$Ttitle2}: <input type="text" name="ttitle" /><br />
        {$Tbody2}: <br /><textarea name="body" rows="30" cols="60"></textarea><br />
        <input type="submit" value="{$Aadd}" />
    </form>
    <a href="news.php">{$Aback}</a>
{/if}
