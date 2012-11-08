<table width="90%">
<tr>
<th><a href="memberlist.php?lista=id&amp;order={$Torder}">{$Plid}</a></th>
<th><a href="memberlist.php?lista=user&amp;order={$Torder}">{$Plname}</a></th>
<th><a href="memberlist.php?lista=rank&amp;order={$Torder}">{$Plrank}</a></th>
<th><a href="memberlist.php?lista=rasa&amp;order={$Torder}">{$Plrace}</a></th>
<th><a href="memberlist.php?lista=miejsce&amp;order={$Torder}">{$Pllocation}</a></th>
<th><a href="memberlist.php?lista=shortrpg&amp;order={$Torder}">{$Plroleplay}</a></th>
</tr>
{foreach $Playerslist as $Playerl}
    <tr>
        <td>{$Playerl.id}</td>
	<td><a href="view.php?view={$Playerl.id}">{$Playerl.user}</a></td>
	<td>{$Playerl.rank}</td>
	<td>{$Playerl.rasa}</td>
	<td>{$Playerl.miejsce}</td>
	<td>{$Playerl.shortrpg}</td>
    </tr>
{/foreach}
</table>
{if $Tpages > 1}
    <br />{$Fpage}
    {for $page = 1 to $Tpages}
        {if $page == $Tpage}
	    {$page}
	{else}
            <a href="memberlist.php?page={$page}&amp;lista={$Mlist}&amp;order={$Torder2}">{$page}</a>
	{/if}
    {/for}
{/if}
<br /><br />
<form method="post" action="memberlist.php?limit=0&amp;lista=user">
{$Search}<br />
{$Splayer}: <input type="text" name="szukany" /><br />
{$Search2}<br />
{$Plid}: <input type="text" name="id" value="0" /><br />
<input type="submit" value="{$Asearch}" />
</form>

{if $Rank2 == 'Admin' || $Rank2 == 'Staff'}
    {$Searchinfo}<br />
    <form method="post" action="memberlist.php?limit=0&amp;lista=user">
        {$Searchip}: <input type="text" name="ip" /><br />
        <input type="submit" value="{$Asearch}" />
    </form>
{/if}
