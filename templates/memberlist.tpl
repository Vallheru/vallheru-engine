{$Message}
<table>
<tr>
<td width="50"><a href="memberlist.php?lista=id&amp;order={$Torder}"><b><u>{$Plid}</u></b></a></td>
<td width="100"><a href="memberlist.php?lista=user&amp;order={$Torder}"><b><u>{$Plname}</u></b></a></td>
<td width="100"><a href="memberlist.php?lista=rank&amp;order={$Torder}"><b><u>{$Plrank}</u></b></a></td>
<td width="50"><a href="memberlist.php?lista=rasa&amp;order={$Torder}"><b><u>{$Plrace}</u></b></a></td>
<td width="50"><a href="memberlist.php?lista=level&amp;order={$Torder}"><b><u>{$Pllevel}</u></b></a></td>
<td><b><u>{$Plroleplay}</u><b></td>
</tr>
{section name=list1 loop=$Name}
    <tr>
    <td>{$Memid[list1]}</td>
    <td><a href="view.php?view={$Memid[list1]}">{$Name[list1]}</a></td>
    <td>{$Rank[list1]}</td>
    <td>{$Race[list1]}</td>
    <td>{$Level[list1]}</td>
    <td>{$Roleplay[list1]}</td>
    </tr>
{/section}
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
