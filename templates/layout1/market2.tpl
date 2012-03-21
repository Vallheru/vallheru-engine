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
    {$Viewinfo} <a href="{$SCRIPT_NAME}">{$Aback}</a>.<br />
    <a href="{$SCRIPT_NAME}?view=add">{$Aadd2}</a><br /><br />
    <form method="post" action="{$SCRIPT_NAME}?view=market&amp;lista={$Aheaders[0]}">
        {$Tname}: <input type="text" name="szukany1" /> <input type="submit" value="{$Asearch}" />
    </form><br />
    <table class="dark">
    <tr>
    {section name=headers loop=$Headers}
        <th width="100"><a href="{$SCRIPT_NAME}?view=market&amp;lista={$Aheaders[headers]}&amp;order={$Aorder2}{if $Asearch2 != ""}&amp;search={$Asearch2}{/if}">{$Headers[headers]}</a></th>
    {/section}
    <th width="100">{$Toptions}</th>
    </tr>
    {section name=ofert loop=$Oferts}
        <tr>
	    {foreach $Okeys as $Key}
	        <td>{$Oferts[ofert].$Key}</td>
	    {/foreach}
	    <td><a href="view.php?view={$Oferts[ofert].seller}">{$Oferts[ofert].user}</a></td>
	    <td>
	        {if $Oferts[ofert].seller == $Pid}
	    	    <a href="market.php?view=myoferts&amp;type={$Mtype}&amp;delete={$Oferts[ofert].id}">{$Adelete}</a><br />
            	    <a href="market.php?view=myoferts&amp;type={$Mtype}&amp;change={$Oferts[ofert].id}">{$Achange}</a><br />
            	    <a href="market.php?view=myoferts&amp;type={$Mtype}&amp;add={$Oferts[ofert].id}">{$Aadd}</a>
		{else}
	    	    <a href={$SCRIPT_NAME}?buy={$Oferts[ofert].id}>{$Abuy}</a>
		{/if}
	</tr>
    {/section}
    </table>
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="{$SCRIPT_NAME}?view=market&page={$page}&amp;lista={$Mlist}&amp;order={$Aorder}{if $Asearch2 != ""}&amp;search={$Asearch2}{/if}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
{/if}

{if $View == "add"}
    {$Addinfo} <a href="{$SCRIPT_NAME}">{$Aback}</a>.<br /><br />
    {if $Addofert == "0"}
        <form method="post" action="{$SCRIPT_NAME}?view=add&amp;step=add"><table class="dark">
        <tr><td>{$Item}:</td><td>{html_options name=item options=$Ioptions}</td></tr>
        <tr><td>{$Amount}:</td><td><input type="text" name="amount" /><br />
    				 <input type="checkbox" name="addall" value="Y" />{$Addall}</td></tr>
        <tr><td>{$Cost}:</td><td><input type="text" name="cost" /></td></tr>
        <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
        </table></form>
    {/if}
    {if $Addofert != "0"}
        <form method="post" action="{$SCRIPT_NAME}?view=add&amp;step=add">
            {$Youwant}<br />
            <input type="hidden" name="ofert" value="{$Addofert}" />
            <input type="hidden" name="item" value="{$Iname}" />
            <input type="hidden" name="amount" value="{$Iamount}" />
            <input type="hidden" name="cost" value="{$Icost}" />
            <input type="submit" value="{$Ayes}" />
        </form>
    {/if}
{/if}

{if $View == "all"}
    {$Listinfo}<br />
    <table align="center" class="dark">
    <tr>
    <th>{$Iname}</th>
    <th>{$Iamount}</th>
    <th align="center">{$Iaction}</td>
    </tr>
    {section name=all loop=$Name}
        <tr>
        <td>{$Name[all]}</td>
        <td align="center">{$Amount[all]}</td>
        <td><form method="post" action="{$SCRIPT_NAME}?view=market&amp;lista=id">
            <input type="hidden" name="szukany" value="{$Name[all]}" />
            <input type="submit" value="{$Ashow}" /></form>
        </td>
        </tr>
    {/section}
    </table>
{/if}

{if $Buy != ""}
    <script src="js/market.js"></script>
    {$Buyinfo} <a href="{$SCRIPT_NAME}">{$Aback}</a>.<br /><br />
    <b>{$Bitem}:</b> {$Name} <br />
    {foreach $Infos as $Info}
        <b>{$Info@key}:</b> {$Info} <br />
    {/foreach}
    <b>{$Oamount}:</b> <a href="#" onClick="buyAll({$Amount1}, {$Cost});">{$Amount1}</a> <br />
    <b>{$Icost}:</b> {$Cost} <br />
    <b>{$Iseller}:</b> <a href="view.php?view={$Sid}">{$Seller}</a> <br /><br />
    <table class="dark"><form id="buy" method="post" action="{$SCRIPT_NAME}?buy={$Itemid}&amp;step=buy">
    <tr><td>{$Bamount}:</td><td><input type="text" name="amount" onChange="countPrice({$Cost}, this.value, {$Amount1});"/> <span id="acost"></span></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Abuy}" /></td></tr>
    </form></table>
{/if}

{$Message}
