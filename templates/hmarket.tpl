{if $View == "" && $Remowe == "" && $Buy == ""}
    {$Minfo}.<br />
    <ul>
    <li><a href="{$SCRIPT_NAME}?view=market&amp;lista=id">{$Aview}</a>
    <li><a href="{$SCRIPT_NAME}?view=add">{$Aadd}</a>
    <li><a href="{$SCRIPT_NAME}?view=del">{$Adelete}</a>
    <li><a href="{$SCRIPT_NAME}?view=all">{$Alist}</a>
    </ul>
    (<a href="market.php">{$Aback2}</a>)
{/if}

{if $View == "market"}
    {$Viewinfo} <a href="hmarket.php">{$Aback}</a>.<br /><br />
    <form method="post" action="hmarket.php?view=market&amp;lista=nazwa">
        {$Therb}: <input type="text" name="szukany" /><br />
        <input type="submit" value="{$Asearch}" />
    </form><br />
    <table>
    <tr>
    <td width="100"><a href="hmarket.php?view=market&amp;lista=nazwa"><b><u>{$Therb}</u></b></a></td>
    <td width="100"><a href="hmarket.php?view=market&amp;lista=ilosc"><b><u>{$Tamount}</u></b></a></td>
    <td width="100"><a href="hmarket.php?view=market&amp;lista=cost"><b><u>{$Tcost}</u></b></a></td>
    <td width="100"><a href="hmarket.php?view=market&amp;lista=seller"><b><u>{$Tseller}</u></b></a></td>
    <td width="100"><b><u>{$Toptions}</u></b></td>
    </tr>
    {section name=herb loop=$Name}
        <tr>
        <td>{$Name[herb]}</td>
        <td>{$Amount[herb]}</td>
        <td>{$Cost[herb]} / {$Fcost[herb]}</td>
        <td><a href="view.php?view={$Seller[herb]}">{$User[herb]}</a></td>
        <td>
	{if $Seller[herb] == $Pid}
	    <a href="market.php?view=myoferts&amp;type=hmarket&amp;delete={$Iid[herb]}">{$Adelete}</a><br />
            <a href="market.php?view=myoferts&amp;type=hmarket&amp;change={$Iid[herb]}">{$Achange}</a><br />
            <a href="market.php?view=myoferts&amp;type=hmarket&amp;add={$Iid[herb]}">{$Aadd}</a>
	{else}
	    <a href=hmarket.php?buy={$Iid[herb]}>{$Abuy}</a>
	{/if}
	</td>
	</tr>
    {/section}
    </table>
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="hmarket.php?view=market&page={$page}&amp;lista={$Mlist}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
{/if}

{if $View == "add"}
    {$Addinfo} <a href="hmarket.php">{$Aback}</a>.<br /><br />
    {if $Addofert == "0"}
        <form method="post" action="hmarket.php?view=add&amp;step=add"><table>
        <tr><td>{$Herb}:</td><td><select name="mineral">
            {section name=addherb loop=$Herbname}
                <option value="{$Sqlname[addherb]}">{$Herbname[addherb]} ({$Tamount}: {if $Herbamount[addherb] != ""}{$Herbamount[addherb]}{else}0{/if})</option>
            {/section}
        </select></td></tr>
        <tr><td>{$Hamount}:</td><td><input type="text" name="ilosc" /><br />
    				 <input type="checkbox" name="addall" value="Y" />{$Addall}</td></tr>
        <tr><td>{$Hcost}:</td><td><input type="text" name="cost" /></td></tr>
        <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
        </table></form>
    {/if}
    {if $Addofert != "0"}
        <form method="post" action="hmarket.php?view=add&amp;step=add">
            {$Youwant}<br />
            <input type="hidden" name="ofert" value="{$Addofert}" />
            <input type="hidden" name="mineral" value="{$Herbname}" />
            <input type="hidden" name="ilosc" value="{$Herbamount}" />
            <input type="hidden" name="cost" value="{$Herbcost}" />
            <input type="submit" value="{$Ayes}" />
        </form>
    {/if}
{/if}

{if $View == "all"}
    {$Listinfo}<br />
    <table>
    <tr>
    <td><b><u>{$Hname}</u></b></td><td><b><u>{$Hamount}</u></b></td><td align="center"><b><u>{$Haction}</u></b></td>
    </tr>
    {section name=all loop=$Name}
        <tr>
        <td>{$Name[all]}</td>
    <td align="center">{$Amount[all]}</td>
    <td><form method="post" action="hmarket.php?view=market&amp;limit=0&amp;lista=id">
        <input type="hidden" name="szukany" value="{$Name[all]}" />
        <input type="submit" value="{$Ashow}" /></form>
    </td>
    </tr>
    {/section}
    </table>
{/if}

{if $Buy != ""}
    {$Buyinfo} <a href="hmarket.php">{$Aback}</a>.<br /><br />
    <b>{$Bherb}:</b> {$Name} <br />
    <b>{$Oamount}:</b> {$Amount1} <br />
    <b>{$Hcost}:</b> {$Cost} <br />
    <b>{$Hseller}:</b> <a href="view.php?view={$Sid}">{$Seller}</a> <br /><br />
    <table><form method="post" action="hmarket.php?buy={$Itemid}&amp;step=buy">
    <tr><td>{$Bamount}:</td><td><input type="text" name="amount" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Abuy}" /></td></tr>
    </form></table>
{/if}

{$Message}
