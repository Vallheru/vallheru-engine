{if $View == "" && $Step == ""}
    {$Newplayer}
    <b>{$Title1}</b> {$Writeby} <b>{$Starter}</b>{$Date}... {$Modtext}<br /><br />
    "{$Update}".<br />
    <a href="updates.php?step=comments&amp;text={$Updid}">{$Acomments}</a>: {$Comments}<br /><br />
    (<a href="updates.php?view=all">{$Alast10}</a>)
{/if}

{if $View == "all" && $Step == ""}
    {section name=upd loop=$Title1}
        <b>{$Title1[upd]}</b> {$Writeby} <b>{$Starter[upd]}</b>{$Date[upd]}... {$Modtext[upd]}<br /><br /> 
        "{$Update[upd]}"<br />
        <a href="updates.php?step=comments&amp;text={$Updid[upd]}">{$Acomments}</a>: {$Comments[upd]}<br /><br />
    {/section}
{/if}

{if $Step == "comments"}
    {if $Amount == "0"}
        <center>{$Nocomments}</center>
    {/if}
    {if $Amount > "0"}
        {section name=update loop=$Tauthor}
            <b>{$Tauthor[update]}</b> {if $Tdate[update] != ""} ({$Tdate[update]}) {/if}{$Writed}: {if $Rank == "Admin"} (<a href="updates.php?step=comments&amp;action=delete&amp;cid={$Cid[update]}">{$Adelete}</a>) {/if}<br />
            {$Tbody[update]}<br /><br />
        {/section}
    {/if}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="updates.php?step=comments&text={$Text}&page={$page}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
    <br /><br /><center>
    <form method="post" action="updates.php?step=comments&amp;text={$Text}">
        {$Addcomment}:<textarea name="body" rows="20" cols="50"></textarea><br />
        <input type="submit" value="{$Aadd}" />
    </form>
    <br /><br />
    <a href="updates.php">{$Aback}</a></center>
{/if}
