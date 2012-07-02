{$Loginfo}<br /><br />

{if $Send != ""}
    <form method="post" action="log.php?send&amp;step=send">
    {$Sendthis}: {html_options name=staff options=$Ostaff}<br />
    <input type="hidden" name="lid" value="{$Send}" />
    <input type="submit" value="{$Asend}" /></form><br />
{/if}

{if $LogId[0] != "0"}
    <form method="post" action="log.php">
        <input type="submit" value="{$Asort}" /> {$Tlogs} <select name="type">
	    {section name=sort loop=$Otypes}
	        <option value="{$Otypes[sort]}">{$Atypes[sort]}</option>
	    {/section}
	</select>
    </form>
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="log.php?page={$page}{$Gsort}">{$page}</a>
	    {/if}
    	{/for}
	<br /><br />
    {/if}
    <form method="post" action="log.php?action=selected">
        <table>
            {section name=log loop=$Date}
                <tr>
                    <td><input type="checkbox" name="{$LogId[log]}" /></td>
                    <td><b>{$Event}:<br />
                        </b>{$Edate}:{$Date[log]}<br />
                        {$Text[log]}<br />
                        <a href="log.php?send={$LogId[log]}">{$Sendevent}</a><br /><br />
                    </td>
                </tr>
            {/section}
        </table><br />
        <input type="submit" value="{$Adelete}" name="selected" /> <input type="submit" value="{$Asend2}" name="selected" /> {html_options name=staff options=$Ostaff}
    </form>
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="log.php?page={$page}{$Gsort}">{$page}</a>
	    {/if}
    	{/for}
	<br /><br />
    {/if}
    <form method="post" action="log.php?step=deleteold">
        <input type="submit" value="{$Adeleteold}" /> <select name="oldtime">
            <option value="7">{$Aweek}</option>
            <option value="14">{$A2week}</option>
            <option value="30">{$Amonth}</option>
        </select>
    </form>
    <a href="log.php?akcja=wyczysc">({$Clearlog})</a><br />
{/if}
