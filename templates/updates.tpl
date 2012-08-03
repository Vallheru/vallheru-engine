{if $View == "" && $Step == ""}
    {$Newplayer}
    <b>{$Title1}</b> {$Writeby} <b>{$Starter}</b>{$Date}... {$Modtext}<br /><br />
    "{$Update}".<br />
    <a href="updates.php?step=comments&amp;text={$Updid}">{$Acomments}</a>: {$Comments}<br /><br />
    (<a href="updates.php?view=all">{$Alast10}</a>)<br /><br />
    <table width="100%">
        <tr>
            <th width="50%">{$Tchanges}</th>
	    <th width="50%">{$Tvallars}</th>
	</tr>
	{section name=utable loop=$Locations}
	    <tr>
	        {if $Cdate[utable] != ""}
	            <td>{$Tdate}: <b>{$Cdate[utable]}</b><br /> {$Tloc}: <b>{$Locations[utable]}</b><br /> {$Changes[utable]}<br /><br /></td>
		{else}
		    <td></td>
		{/if}
		{if $Ownerid[utable] != ""}
		    <td>{$Tdate}: <b>{$Vdate[utable]}</b><br /> {$Tgrant}: <b><a href="view.php?view={$Ownerid[utable]}">{$Owner[utable]}</a></b><br />{$Reason[utable]}<br /><br /></td>
		{else}
		    <td></td>
		{/if}
	    </tr>
	{/section}
	<tr>
	    <td><a href="account.php?view=changes">{$Achanges}</a></td>
	    <td><a href="account.php?view=vallars">{$Avallars}</a></td>
	</tr>
    </table>
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
            <b><a href="view.php?view={$Taid[update]}">{$Tauthor[update]}</a></b> {if $Tdate[update] != ""} ({$Tdate[update]}) {/if}{$Writed}: {if $Rank == "Admin"} (<a href="updates.php?step=comments&amp;action=delete&amp;cid={$Cid[update]}">{$Adelete}</a>) {/if}<br />
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
        {include file="comments.tpl"}
    <br /><br />
    <a href="updates.php">{$Aback}</a></center>
{/if}
