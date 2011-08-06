{$Message}
<table class="dark">
<tr>
<td width="50"><a href="memberlist.php?lista=id&amp;limit=0"><b><u>{$Plid}</u></b></a></td>
<td width="100"><a href="memberlist.php?lista=user&amp;limit=0"><b><u>{$Plname}</u></b></a></td>
<td width="100"><a href="memberlist.php?lista=rank&amp;limit=0"><b><u>{$Plrank}</u></b></a></td>
<td width="50"><a href="memberlist.php?lista=rasa&amp;limit=0"><b><u>{$Plrace}</u></b></a></td>
<td width="50"><a href="memberlist.php?lista=level&amp;limit=0"><b><u>{$Pllevel}</u></b></a></td>
</tr>
{section name=list1 loop=$Name}
    <tr>
    <td>{$Memid[list1]}</td>
    <td><a href="view.php?view={$Memid[list1]}">{$Name[list1]}</a></td>
    <td>{$Rank[list1]}</td>
    <td>{$Race[list1]}</td>
    <td>{$Level[list1]}</td>
    </tr>
{/section}
</table>
{$Previous} {$Next}
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