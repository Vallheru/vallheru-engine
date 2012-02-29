{if $View == "market"}
    {$Listinfo}<br /><br />
    <table width="100%" align="center">
        <tr>
	    <th>{$Liname}</th>
	    <th>{$Lipower}</th>
	    <th>{$Lidefense}</th>
            <th>{$Liid}</th>
            <th>{$Licost}</th>
            <th>{$Coptions}</th>
	</tr>
        {section name=core5 loop=$Link}
            {$Link[core5]}
        {/section}
    </table>
{/if}
{if $View == "add"}
    {$Addinfo}
    <form method="post" action="cmarket.php?view=add&amp;action=add">
        {$Addmy} <select name="add_core">
        {section name=core6 loop=$Corename}
            <option value="{$Coreid1[core6]}">{$Corename[core6]}</option>
        {/section}
        </select> {$Addcore} <input type="text" size="7" name="cost" /> {$Coins} <input type=submit value="{$Asell}" />
    </form>
{/if}
{if $View == ""}
    {$Marketinfo}
    <ul>
        <li><a href="cmarket.php?view=market">{$Ashow}</a></li>
        <li><a href="cmarket.php?view=add">{$Aadd}</a></li>
	<li><a href="cmarket.php?view=del">{$Adelete}</a></li>
    </ul>
    (<a href="market.php">{$Aback2}</a>)
{else}
    <br /><br />(<a href="cmarket.php">{$Aback}</a>)
{/if}
