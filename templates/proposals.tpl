{if $Proposal == 0}
    {$Pinfo}<br /><br />
        <table width="100%">
    	    <tr>
		 <th width="65%">{$Ptitle}</th>
		 <th>{$Pauthor}</th>
		 <th>{$Pstatus}</th>
		 <th>{$Pup}</th>
		 <th>{$Pdown}</th>
    	    </tr>
	    {section name=proposals loop=$Titles}
	    	 <tr>
		     <td><a href="proposals.php?proposal={$PIds[proposals]}">{$Titles[proposals]}</a></td>
		     <td>{$Authors[proposals]}</td>
		     <td>{$Statuses[proposals]}</td>
		     <td>{$Votesup[proposals]}</td>
		     <td>{$Votesdown[proposals]}</td>
		 </tr>
	    {/section}
	</table><br />
	<a href="proposals.php?proposal=-1">{$Padd}</a>
{/if}

{if $Proposal == -1}
    {if $Send == 0}
    	<form method="POST" action="proposals.php?proposal=-1&amp;send">
            {$Ptitle}: <input type="text" name="ptitle" /><br /><br />
	    {$Pdesc}:<br />
	    <textarea rows="20" cols="50" name="pbody" title="{$Pdesc}"></textarea><br />
	    <input type="submit" value="{$Psend}" />
    	</form><br />
    {else}
	{$Added}<br />
    {/if}
    <a href="proposals.php">{$Aback}</a>
{/if}

{if $Proposal > 0}
    {$Up}: {$Aup}<br />
    {$Down}: {$Adown}<br />
    <form method="POST" action="proposals.php?proposal={$Proposal}">
        <input type="submit" name="vote" value="+"/> <input type="submit" name="vote" value="-" /><br />
	<input type="submit" name="vote" value="{$Vremove}" />
    </form><br />
    {$Pauthor}: {$Pauthor1}<br />
    {$Ptitle}: {$Ptitle1}<br />
    {$Pdesc}: {$Pbody}<br /><br /><br />
    {if $Amount == "0"}
        <center>{$Nocomments}</center>
    {/if}
    {if $Amount > "0"}
        {section name=update loop=$Tauthor}
            <b>{$Tauthor[update]}</b> {if $Tdate[update] != ""} ({$Tdate[update]}) {/if}{$Writed}: {if $Rank == "Admin" || $Rank == "Staff"} (<a href="proposals.php?proposal={$Proposal}&amp;step=delete&amp;cid={$Cid[update]}">{$Adelete}</a>) {/if}<br />
            {$Tbody[update]}<br /><br />
        {/section}
    {/if}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="proposals.php?proposal={$Proposal}&page={$page}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
    <br /><br /><center>
    <form method="post" action="proposals.php?proposal={$Proposal}&amp;step=add">
        {$Addcomment}:<textarea name="body" rows="20" cols="50"></textarea><br />
        <input type="hidden" name="pid" value="{$Proposal}" />
        <input type="submit" value="{$Aadd}" />
    </form></center>
    <br /><br />
    <a href="proposals.php">{$Aback}</a>
{/if}
