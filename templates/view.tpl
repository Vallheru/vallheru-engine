{if $Logs == 'N'}
    <center><b><u>{$User}</u></b> ({$Id})</center><br />
    {if $Avatar != ""}
        <center><img src="{$Avatar}" width="{$Awidth}" height="{$Aheight}" /></center>
    {/if}
    {$IP}<br />
    {$GG}
    <b>{$Tfreezed}</b>
    {$Trank}: {$Rank}<br />
    {$Tlocation}: {$Location}<br />
    {$Immu}
    {$Tlastseen}: {$Page}<br />
    {$Seen}: {$Lastseen}<br />
    {$Tage}: {$Age}<br />
    {$Trace}: {$Race}<br />
    {$Tclass2}: {$Clas}<br />
    {$Gender}
    {$Deity}
    {$Tstatus}: {$Status}
    {$Clan}
    {$Tmaxhp}: {$Maxhp}<br /><br />
    {$Treputation}: {$Reputation}<br />
    <a href="view.php?view={$Id}&amp;logs">{$Tfights}</a>: {$Wins}/{$Losses} {$Fratio}<br />
    {$Tlastkill}: {$Lastkilled}<br />
    {$Tlastkilled}: {$Lastkilledby}<br />
    {$Trefs}: <a href="referrals.php?id={$Id}">{$Refs}</a><br /><br />
    {if $Attack != "" || $Mail != "" || $Crime != ""}
        {$Toptions}:<br />
    	<ul>
            {$Attack}
    	    {$Mail}
    	    {$Crime}
    	    {$Crime2}
    	</ul>
    {/if}
    {$Rprofile}<br /><br />
    {$Tprofile}:<br />{$Profile}<br /><br />
    <table width="100%">
        <tr>
            <td align="left"><a href="view.php?view={$Previous}">{$Aprevious}</a></td>
            <td align="right"><a href="view.php?view={$Next}">{$Anext}</a></td>
        </tr>
    </table>
{else}
    {if $Lamount > 0}
        <ul>
            {foreach $Blogs as $Log}
	        <li>{$Log.bdate} {$Tage} {$Log.result} {$Twith} <a href="view.php?view={$Log.did}">{$Log.ename}</a></li>
	    {/foreach}
        </ul>
	{if $Tpages > 1}
    	    {$Fpage}
    	    {for $page = 1 to $Tpages}
	        {if $page == $Tpage}
	            {$page}
	        {else}
                    <a href="view.php?view={$Id}&amp;logs&amp;page={$page}">{$page}</a>
	        {/if}
    	    {/for}<br />
        {/if}
    {else}
        {$Nologs}
    {/if}
    (<a href="view.php?view={$Id}">{$Aback}</a>)
{/if}
