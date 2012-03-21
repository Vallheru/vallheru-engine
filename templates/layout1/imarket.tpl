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
    {$Viewinfo} <a href="imarket.php">{$Aback}</a>.<br />
    <a href="{$SCRIPT_NAME}?view=add">{$Aadd2}</a><br /><br />
    <form method="post" action="imarket.php?view=market&amp;lista=name">
        {$Tname}: <input type="text" name="szukany" />
        <input type="submit" value="{$Asearch}" />
    </form><br />
    <form method="post" action="imarket.php?view=market&amp;lista=name">
        <input type="submit" value="{$Ashow}" /> {$Tofferts} <select name="type">
	{section name=itype loop=$Onames}
	    <option value="{$Otypes[itype]}">{$Onames[itype]}</option>
	{/section}</select> {$Tlevels} <input type="text" name="mlevel" size="5" /> {$Tto} <input type="text" name="maxlev" size="5" />
    </form><br />
    <table class="dark">
    <tr>
    <td width="100"><a href="imarket.php?view=market{$Atype2}&amp;lista=name"><b><u>{$Tname}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market{$Atype2}&amp;lista=power"><b><u>{$Tpower}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market{$Atype2}&amp;lista=wt"><b><u>{$Tdur}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market{$Atype2}&amp;lista=szyb"><b><u>{$Tspeed}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market{$Atype2}&amp;lista=zr"><b><u>{$Tagi}</u></b></a></td>
    <td width="50"><a href="imarket.php?view=market{$Atype2}&amp;lista=minlev"><b><u>{$Tlevel}</u></b></a></td>
    <td width="50"><a href="imarket.php?view=market{$Atype2}&amp;lista=amount"><b><u>{$Tamount}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market{$Atype2}&amp;lista=cost"><b><u>{$Tcost}</u></b></a></td>
    <td width="50"><a href="imarket.php?view=market{$Atype2}&amp;lista=owner"><b><u>{$Tseller}</u></b></a></td>
    <td width="100"><b><u>{$Toptions}</u></b></td>
    </tr>
    {section name=item loop=$Name}
        <tr>
        <td>{$Name[item]}</td>
        <td align="center">{$Power[item]}</td>
        <td align="center">{$Durability[item]}/{$Maxdur[item]}</td>
        <td align="center">{$Speed[item]}</td>
        <td align="center">{$Agility[item]}</td>
    <td align="center">{$Minlev[item]}</td>
        <td align="center">{$Amount[item]}</td>
        <td>{$Cost[item]} / {$Fcost[item]}</td>
        <td><a href="view.php?view={$Owner[item]}">{$Seller[item]}</a></td>
	<td>
	{if $Owner[item] == $Pid}
	    <a href="market.php?view=myoferts&amp;type=imarket&amp;delete={$Iid[item]}">{$Adelete}</a><br />
            <a href="market.php?view=myoferts&amp;type=imarket&amp;change={$Iid[item]}">{$Achange}</a><br />
            <a href="market.php?view=myoferts&amp;type=imarket&amp;add={$Iid[item]}">{$Aadd}</a>
	{else}
	    <a href=imarket.php?buy={$Iid[item]}>{$Abuy}</a>
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
                <a href="imarket.php?view=market&page={$page}{$Atype}&amp;lista={$Mlist}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
{/if}

{if $View == "add"}
    {$Addinfo} <a href="imarket.php">{$Aback}</a>.<br /><br />
    <form method="post" action="imarket.php?view=add&amp;step=add"><table class="dark">
    <tr><td colspan="2">
    {$Item}: <select name="przedmiot">
    {section name=item1 loop=$Name}
        <option value="{$Itemid[item1]}">{$Name[item1]} (+{$Ipower[item1]})
	{if $Ispeed[item1] > 0}
	    (+{$Ispeed[item1]} {$Ispd})
	{/if}
	{if $Iagi[item1] != 0}
	    ({$Iagi[item1]}% {$Iag})
	{/if}
	{if $Imaxdur[item1] > 1}
	    ({$Idur[item1]}/{$Imaxdur[item1]})
	{/if}
	({$Iamount}: {$Amount[item1]})</option>
    {/section}</select></td></tr>
    <tr><td>{$Iamount2}:</td><td><input type="text" name="amount" /><br />
    				 <input type="checkbox" name="addall" value="Y" />{$Addall}</td></tr>
    <tr><td>{$Icost}:</td><td><input type="text" name="cost" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}

{if $Buy != ""}
    <script src="js/market.js"></script>
    {$Buyinfo} <a href="imarket.php">{$Aback}</a>.<br /><br />
    <b>{$Item}:</b> {$Name} <br />
    <b>{$Ipower}:</b> {$Power} <br />
    {if $Agi != "0"}
        <b>{$Iagi}:</b> {$Agi} <br />
    {/if}
    {if $Speed != "0"}
        <b>{$Ispeed}:</b> {$Speed} <br />
    {/if}
    {if $Type != "R" && $Type != "S" && $Type != "Z" && $Type != "G"}
        <b>{$Idur}:</b> {$Dur}/{$MaxDur} <br />
    {/if}
    {if $Type == "G"}
        <b>{$Hamount}:</b> {$Dur} <br />
    {/if}
    <b>{$Oamount}:</b> <a href="#" onClick="buyAll({$Amount1}, {$Cost});">{$Amount1}</a> <br />
    <b>{$Icost}:</b> {$Cost} <br />
    <b>{$Iseller}:</b> <a href="view.php?view={$Sid}">{$Seller}</a> <br /><br />
    <form id="buy" method="post" action="imarket.php?buy={$Itemid}&amp;step=buy"><table class="dark">
    <tr><td>{$Bamount}:</td><td><input type="text" name="amount" onChange="countPrice({$Cost}, this.value, {$Amount1});"/> <span id="acost"></span></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Abuy}" /></td></tr>
    </table></form>
{/if}

{if $View == "all"}
    {$Listinfo}<br />
    <table class="dark">
    <tr>
    <td><b><u>{$Iname}</u></b></td><td><b><u>{$Iamount}</u></b></td><td align="center"><b><u>{$Iaction}</u></b></td>
    </tr>
    {section name=all loop=$Name}
        <tr>
        <td>{$Name[all]}</td>
        <td align="center">{$Amount[all]}</td>
        <td><form method="post" action="imarket.php?view=market&amp;lista=id">
            <input type="hidden" name="szukany1" value="{$Name[all]}" />
            <input type="submit" value="{$Ashow}" /></form>
        </td>
        </tr>
    {/section}
    </table>
    {$Tlinks}
{/if}

{$Message}
