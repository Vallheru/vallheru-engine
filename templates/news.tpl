{if $View == "" && $Step == ""}
    <b>{$Title1}</b> {$Writeby} <b>{$Starter}</b>...<br /><br />
    "{$News}"<br />
    <a href="news.php?step=comments&amp;text={$Newsid}">{$Acomments}</a>: {$Comments}<br /><br />
    (<a href="news.php?view=all">{$Last10}</a>)<br /><br />
    <a href="news.php?step=add">{$Aaddnews}</a> ({$Twaiting}: {$Waiting} | {$Taccepted}: {$Accepted})
{/if}

{if $View == "all" && $Step == ""}
    {section name=news loop=$Title1}
        <b>{$Title1[news]}</b> {$Writeby} <b>{$Starter[news]}</b>...<br /><br />
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
            <b>{$Tauthor[update]}</b> {if $Tdate[update] != ""} ({$Tdate[update]}) {/if}{$Writed}: {if $Rank == "Admin" || $Rank == "Staff"} (<a href="news.php?step=comments&amp;action=delete&amp;cid={$Cid[update]}">{$Adelete}</a>) {/if}<br />
            {$Tbody[update]}<br /><br />
        {/section}
    {/if}
    <br /><br /><center>
    <form method="post" action="news.php?step=comments&amp;action=add">
        {$Addcomment}:<textarea name="body" rows="20" cols="50"></textarea><br />
        <input type="hidden" name="tid" value="{$Text}" />
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
