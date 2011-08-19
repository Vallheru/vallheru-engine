{if $View == "" && $Delete == "" && $Buy == ""}
    {$Minfo}<br />
    <ul>
    <li><a href="{$SCRIPT_NAME}?view=market&amp;lista=id&amp;limit=0">{$Aview}</a>
    <li><a href="{$SCRIPT_NAME}?view=szukaj">{$Asearch}</a></li>
    <li><a href="{$SCRIPT_NAME}?view=add">{$Aadd}</a>
    <li><a href="{$SCRIPT_NAME}?view=del">{$Adelete}</a>
    <li><a href="{$SCRIPT_NAME}?view=all">{$Alist}</a>
    </ul>
    (<a href="market.php">{$Aback2}</a>)
{/if}

{if $View == "szukaj"}
    {$Sinfo} <a href="pmarket.php">{$Aback}</a>.<br /><br />
    <form method="post" action="pmarket.php?view=market&amp;limit=0&amp;lista=nazwa"><table class="dark">
    <tr><td colspan="2">{$Mineral}: <input type="text" name="szukany" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Asearch}" /></td></tr>
    </table></form>
{/if}

{if $View == "market"}
    {$Viewinfo} <a href="pmarket.php">{$Aback}</a>.<br /><br />
    <table class="dark">
    <tr>
    <td width="100"><a href="pmarket.php?view=market&amp;lista=nazwa&amp;limit=0"><b><u>{$Mineral}</u></b></a></td>
    <td width="100"><a href="pmarket.php?view=market&amp;lista=ilosc&amp;limit=0"><b><u>{$Tamount}</u></b></a></td>
    <td width="100"><a href="pmarket.php?view=market&amp;lista=cost&amp;limit=0"><b><u>{$Tcost}</u></b></a></td>
    <td width="100"><a href="pmarket.php?view=market&amp;lista=seller&amp;limit=0"><b><u>{$Tseller}</u></b></a></td>
    <td width="100"><b><u>{$Toptions}</u></b></td>
    </tr>
    {section name=pmarket loop=$Name}
        <tr>
        <td>{$Name[pmarket]}</td>
        <td>{$Amount[pmarket]}</td>
        <td>{$Cost[pmarket]}</td>
        <td><a href="view.php?view={$Seller[pmarket]}">{$User[pmarket]}</a></td>
        <td>
	{if $Seller[pmarket] == $Pid}
	    <a href="market.php?view=myoferts&amp;type=pmarket&amp;delete={$Iid[pmarket]}">{$Adelete}</a><br />
            <a href="market.php?view=myoferts&amp;type=pmarket&amp;change={$Iid[pmarket]}">{$Achange}</a><br />
            <a href="market.php?view=myoferts&amp;type=pmarket&amp;add={$Iid[pmarket]}">{$Aadd}</a>
	{else}
	    <a href=pmarket.php?buy={$Iid[pmarket]}>{$Abuy}</a>
	{/if}
	</td>
	</tr>
    {/section}
    </table>
    {$Previous}{$Next}
{/if}

{if $View == "add"}
    {$Addinfo} <a href="pmarket.php">{$Aback}</a>.<br /><br />
    {if $Addofert == "0"}
        <form method="post" action="pmarket.php?view=add&amp;step=add"><table class="dark">
        <tr><td>{$Mineral}:</td><td><select name="mineral">
            {section name=pmarket2 loop=$Minerals}
                <option value="{$smarty.section.pmarket2.index}">{$Minerals[pmarket2]} ({$Tamount}: {if $Mineralsamount[pmarket2] != ""}{$Mineralsamount[pmarket2]}{else}0{/if})</option>
            {/section}
        </select></td></tr>
        <tr><td>{$Mamount}:</td><td><input type="text" name="amount" /></td></tr>
        <tr><td>{$Mcost}:</td><td><input type="text" name="cost" /></td></tr>
        <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
        </table></form>
    {/if}
    {if $Addofert != "0"}
        <form method="post" action="pmarket.php?view=add&amp;step=add">
            {$Youwant}<br />
            <input type="hidden" name="ofert" value="{$Addofert}" />
            <input type="hidden" name="mineral" value="{$Mineralname}" />
            <input type="hidden" name="amount" value="{$Mineralamount}" />
            <input type="hidden" name="cost" value="{$Mineralcost}" />
            <input type="submit" value="{$Ayes}" />
        </form>
    {/if}
{/if}

{if $View == "all"}
    {$Listinfo}<br />
    <table class="dark">
    <tr>
    <td><b><u>{$Mname}</u></b></td><td><b><u>{$Mamount}</u></b></td><td align="center"><b><u>{$Maction}</u></b></td>
    </tr>
    {section name=all loop=$Name}
        <tr>
        <td>{$Name[all]}</td>
    <td align="center">{$Amount[all]}</td>
    <td><form method="post" action="pmarket.php?view=market&amp;limit=0&amp;lista=id">
        <input type="hidden" name="szukany" value="{$Name[all]}" />
        <input type="submit" value="{$Ashow}" /></form>
    </td>
    </tr>
    {/section}
    </table>
{/if}

{if $Buy != ""}
    {$Buyinfo} <a href="pmarket.php">{$Aback}</a>.<br /><br />
    <b>{$Mineral}:</b> {$Name} <br />
    <b>{$Oamount}:</b> {$Amount1} <br />
    <b>{$Mcost}:</b> {$Cost} <br />
    <b>{$Mseller}:</b> <a href="view.php?view={$Sellerid}">{$Seller}</a> <br /><br />
    <table class="dark"><form method="post" action="pmarket.php?buy={$Itemid}&amp;step=buy">
    <tr><td>{$Bamount}:</td><td><input type="text" name="amount" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Abuy}" /></td></tr>
    </form></table>
{/if}

{$Message}
