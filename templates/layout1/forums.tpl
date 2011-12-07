{if $View == "categories"}
    {if $Funread > 0}
        <a href="forums.php?view=newposts">{$Anew}</a><br /><br />
    {/if}
    <table class="dark"><tr><td width="75%"><b><u>{$Tcategory}</u></b></td><td><b><u>{$Ttopics}</u></b></td></tr>
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
    </table><br /><br />
{/if}

{if $View == "newposts"}
    <ul>
        {section name=numbers loop=$Titles}
	    <li><b>{$Tcats[numbers]}:</b> <a href="forums.php?topic={$Tid[numbers]}">{$Titles[numbers]}</a></li>
	{/section}
    </ul>
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="forums.php?view=newposts&amp;page={$page}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
{/if}

{if $Topics != ""}
    <a href="forums.php?view=categories">{$Aback}</a> {$Tocategories}.<br /><br />
    <form method="post" action="forums.php?action=search">
        <input type="submit" value="{$Asearch}" /> {$Tword}: <input type="text" name="search" />
        <input type="hidden" name="catid" value="{$Category}" />
    </form><br />
    <form method="post" action="forums.php?topics={$Topics}" />
        <input type="submit" value="{$Asort}" /> {$Tsort} {html_options name=sort options=$Onames selected=$Selected}
    </form>
    {if $Prank == "Admin" || $Prank == "Staff"}
        <form method="post" action="forums.php?action=deltopics">
    {/if}
    <table class="dark"><tr>{if $Prank == "Admin" || $Prank == "Staff"}<td width="20"></td>{/if}<td><u><b>{$Ttopic}</b></u></td><td width="20%"><u><b>{$Tauthor}</b></u></td><td width="10%"><b><u>{$Treplies}</u></b></td></tr>
    {section name=number1 loop=$Topic1}
        <tr>
	{if $Prank == "Admin" || $Prank == "Staff"}
	    <td><input type="checkbox" name="{$Id[number1]}" /></td>
	{/if}
        <td>{if $Newtopic[number1] == "Y"}<blink>N</blink>{/if} {$Closed[number1]}<a href="forums.php?topic={$Id[number1]}">{$Topic1[number1]}</a></td>
        <td><a href="view.php?view={$Playersid[number1]}">{$Starter1[number1]}</a></td>
        <td>{$Replies1[number1]}</td>
        </tr>
    {/section}
    </table>
    {if $Prank == "Admin" || $Prank == "Staff"}
        <input type="hidden" name="catid" value="{$Category}" />
        <input type="submit" value="{$Adelete}" />
	</form>
    {/if}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="forums.php?topics={$Topics}&page={$page}">{$page}</a>
	    {/if}
    	{/for}
	<br /><br />
    {/if}
    <form method="post" action="forums.php?action=addtopic">
        {$Addtopic}:<br />
        <input type="text" name="title2" value="Temat" size="40" /><br />
        <textarea name="body" cols="40" rows="10">{$Ttext}</textarea><br />
        <input type="hidden" name="catid" value="{$Category}" />
        {if $Rank == "Admin" || $Rank == "Staff"}
            <input type="checkbox" name="sticky" />{$Tsticky}<br />
        {/if}
        <input type="submit" value="{$Addtopic}" />
    </form><br />{$Thelp}<br /><br />
    <a href="forums.php?view=categories">{$Aback}</a> {$Tocategories}.
{/if}

{if $Topic != ""}
    <br />
    <table class="td" width="98%" cellpadding="0" cellspacing="0">
    <tr>
    <td><b>{$Topic2}</b> {$Writeby} <a href="view.php?view={$Playerid}">{$Starter}</a> {$Wid} {$Playerid} (<a href="forums.php?topics={$Category}">{$Aback}</a>) {if $Closed == 'N'}(<a href="forums.php?topic={$Topic}&amp;quotet=Y">{$Aquote}</a>){/if} {$Action}
    </td>
    </tr>
    <tr>
    <td>{$Ttext}</td>
    </tr>
    </table><br />
    {if $Prank == "Admin" || $Prank == "Staff"}
        <form method="post" action="forums.php?delreplies={$Topic}">
    {/if}
    <center>
    {section name=number2 loop=$Rtext}
        <table class="td" width="98%" cellpadding="0" cellspacing="0">
        <tr>
        <td><b><a href="view.php?view={$Rplayerid[number2]}">{$Rstarter[number2]}</a></b> {$Wid} {$Rplayerid[number2]} {$Write}... (<a href="forums.php?topics={$Category}">{$Aback}</a>) {if $Closed == 'N'}(<a href="forums.php?topic={$Topic}&amp;quote={$Rid[number2]}">{$Aquote}</a>){/if}
         {$Action2[number2]}
        </td>
        </tr>
        <tr>
        <td>{$Rtext[number2]}</td></tr></table><br />
    {/section}
    </center>
    {if $Prank == "Admin" || $Prank == "Staff"}
    	<input type="submit" value="{$Adelete}" />
        </form>
    {/if}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="forums.php?topic={$Topic}&page={$page}">{$page}</a>
	    {/if}
    	{/for}
	<br /><br />
    {/if}
    {if $Closed == 'N'}
        <form method="post" action="forums.php?reply={$Id}">
    	{$Areply}:<br />
    	<textarea name="rep" cols="40" rows="10">{$Rtext2}</textarea><br />
    	<input type="submit" value="{$Areply}" />
    	</form><br />
	{$Thelp}
    {/if}
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

{if $Action3 == "move"}
    <form method="post" action="forums.php?action=move&amp;tid={$Tid}">
    	  <input type="submit" value="{$Amove}" /> {$Tto} <select name="category">
	      {section name=move loop=$Categories}
	          <option value="{$Catid[move]}">{$Categories[move]}</option>
	      {/section}
	      </select>
    </form>
{/if}
