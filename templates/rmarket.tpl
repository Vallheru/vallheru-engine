{if $View == "" && $Remowe == "" && $Buy == ""}
    {$Minfo}.<br />
    <ul>
    <li><a href="{$SCRIPT_NAME}?view=market&amp;lista=id">{$Aview}</a></li>
    <li><a href="{$SCRIPT_NAME}?view=add">{$Aadd}</a></li>
    <li><a href="{$SCRIPT_NAME}?view=del">{$Adelete}</a></li>
    <li><a href="{$SCRIPT_NAME}?view=all&amp;limit=0">{$Alist}</a></li>
    </ul>
    (<a href="market.php">{$Aback2}</a>)
{/if}

{if $View == "market"}
    {$Viewinfo} <a href="rmarket.php">{$Aback}</a>.<br /><br />
    <form method="post" action="rmarket.php?view=market&amp;lista=name">
        {$Tname}: <input type="text" name="szukany" />
        <input type="submit" value="{$Asearch}" />
    </form><br />
    <table>
    <tr>
    <td width="100"><a href="rmarket.php?view=market&amp;lista=name"><b><u>{$Tname}</u></b></a></td>
    <td width="100"><a href="rmarket.php?view=market&amp;lista=power"><b><u>{$Tpower}</u></b></a></td>
    <td width="50"><a href="rmarket.php?view=market&amp;lista=minlev"><b><u>{$Tlevel}</u></b></a></td>
    <td width="50"><a href="rmarket.php?view=market&amp;lista=amount"><b><u>{$Tamount}</u></b></a></td>
    <td width="100"><a href="rmarket.php?view=market&amp;lista=cost"><b><u>{$Tcost}</u></b></a></td>
    <td width="50"><a href="rmarket.php?view=market&amp;lista=owner"><b><u>{$Tseller}</u></b></a></td>
    <td width="100"><b><u>{$Toptions}</u></b></td>
    </tr>
    {section name=item loop=$Name}
        <tr>
        <td>{$Name[item]}</td>
        <td align="center">{$Power[item]}</td>
        <td align="center">{$Minlev[item]}</td>
        <td align="center">{$Amount[item]}</td>
        <td>{$Cost[item]} / {$Fcost[item]}</td>
        <td><a href="view.php?view={$Owner[item]}">{$Seller[item]}</a></td>
        <td>
	{if $Owner[item] == $Pid}
	    <a href="market.php?view=myoferts&amp;type=rmarket&amp;delete={$Iid[item]}">{$Adelete}</a><br />
            <a href="market.php?view=myoferts&amp;type=rmarket&amp;change={$Iid[item]}">{$Achange}</a><br />
            <a href="market.php?view=myoferts&amp;type=rmarket&amp;add={$Iid[item]}">{$Aadd}</a>
	{else}
	    <a href=rmarket.php?buy={$Iid[item]}>{$Abuy}</a>
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
                <a href="rmarket.php?view=market&page={$page}&amp;lista={$Mlist}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
{/if}

{if $View == "add"}
    {$Addinfo} <a href="rmarket.php">{$Aback}</a>.<br /><br />
    <form method="post" action="rmarket.php?view=add&amp;step=add"><table>
    <tr><td colspan="2">
    {$Item}: <select name="przedmiot">
    {section name=item1 loop=$Name}
        <option value="{$Itemid[item1]}">{$Name[item1]} (+{$Rpower[item1]}) ({$Iamount}: {$Amount[item1]})</option>
    {/section}</select></td></tr>
    <tr><td>{$Iamount2}:</td><td><input type="text" name="amount" /><br />
    				 <input type="checkbox" name="addall" value="Y" />{$Addall}</td></tr>
    <tr><td>{$Icost}:</td><td><input type="text" name="cost" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}

{if $Buy != ""}
    {$Buyinfo} <a href="rmarket.php">{$Aback}</a>.<br /><br />
    <b>{$Item}:</b> {$Name} <br />
    <b>{$Ipower}:</b> {$Power} <br />
    <b>{$Oamount}:</b> {$Amount1} <br />
    <b>{$Icost}:</b> {$Cost} <br />
    <b>{$Iseller}:</b> <a href="view.php?view={$Sid}">{$Seller}</a> <br /><br />
    <form method="post" action="rmarket.php?buy={$Itemid}&amp;step=buy"><table>
    <tr><td>{$Bamount}:</td><td><input type="text" name="amount" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Abuy}" /></td></tr>
    </table></form>
{/if}

{if $View == "all"}
    {$Listinfo}<br />
    <table>
    <tr>
    <td><b><u>{$Iname}</u></b></td><td><b><u>{$Iamount}</u></b></td><td align="center"><b><u>{$Iaction}</u></b></td>
    </tr>
    {section name=all loop=$Name}
        <tr>
        <td>{$Name[all]}</td>
        <td align="center">{$Amount[all]}</td>
        <td><form method="post" action="rmarket.php?view=market&amp;lista=id">
            <input type="hidden" name="szukany1" value="{$Name[all]}" />
            <input type="submit" value="{$Ashow}" /></form>
        </td>
        </tr>
    {/section}
    </table>
{/if}

{$Message}
