{if $View == "" && $Remowe == "" && $Buy == ""}
    {$Minfo}.<br />
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
    {$Sinfo} <a href="amarket.php">{$Aback}</a>.<br /><br />
    <form method="post" action="amarket.php?view=market&amp;limit=0&amp;lista=type"><table>
    <tr><td colspan="2">{$Astral}: <input type="text" name="szukany" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Asearch}" /></td></tr>
    </table></form>
{/if}

{if $View == "market"}
    {$Viewinfo} <a href="amarket.php">{$Aback}</a>.<br /><br />
    <table>
    <tr>
    <td width="100"><a href="amarket.php?view=market&amp;lista=type&amp;limit=0"><b><u>{$Tastral}</u></b></a></td>
    <td width="100"><a href="amarket.php?view=market&amp;lista=number&amp;limit=0"><b><u>{$Tnumber}</u></b></a></td>
    <td width="100"><a href="amarket.php?view=market&amp;lista=amount&amp;limit=0"><b><u>{$Tamount}</u></b></a></td>
    <td width="100"><a href="amarket.php?view=market&amp;lista=cost&amp;limit=0"><b><u>{$Tcost}</u></b></a></td>
    <td width="100"><a href="amarket.php?view=market&amp;lista=seller&amp;limit=0"><b><u>{$Tseller}</u></b></a></td>
    <td width="100"><b><u>{$Toptions}</u></b></td>
    </tr>
    {section name=herb loop=$Name}
        <tr>
        <td>{$Name[herb]}</td>
        <td>{$Number[herb]}</td>
        <td>{$Amount[herb]}</td>
        <td>{$Cost[herb]}</td>
        <td><a href="view.php?view={$Seller[herb]}">{$User[herb]}</a></td>
        {$Action[herb]}
    {/section}
    </table>
    {$Previous}{$Next}
{/if}

{if $View == "add"}
    {$Addinfo} <a href="amarket.php">{$Aback}</a>.<br /><br />
    {if $Addofert == "0"}
        {$Tadd}<br />
        <form method="post" action="amarket.php?view=add&amp;step=piece"><table class="dark">
        <tr><td>{$Astral}:</td><td><select name="name">
            {section name=addherb loop=$Herbname}
                <option value="{$smarty.section.addherb.index}">{$Herbname[addherb]}</option>
            {/section}
        </select></td></tr>
        <tr><td>{$Anumber}:</td><td><input type="text" name="number" /></td></tr>
        <tr><td>{$Hamount}:</td><td><input type="text" name="amount" /></td></tr>
        <tr><td>{$Hcost}:</td><td><input type="text" name="cost" /></td></tr>
        <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
        </table></form><br />
        {$Tadd2}<br />
        <form method="post" action="amarket.php?view=add&amp;step=component"><table class="dark">
        <tr><td>{$Astral}:</td><td><select name="name">
            {section name=addherb2 loop=$Aname}
                <option value="{$smarty.section.addherb2.index}">{$Aname[addherb2]}</option>
            {/section}
        </select></td></tr>
        <tr><td><input type="hidden" name="number" value="1" /></td></tr>
        <tr><td>{$Hamount}:</td><td><input type="text" name="amount" /></td></tr>
        <tr><td>{$Hcost}:</td><td><input type="text" name="cost" /></td></tr>
        <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
        </table></form>
    {/if}
    {if $Addofert != "0"}
        <form method="post" action="amarket.php?view=add&amp;step={$Step}">
            {$Youwant}<br />
            <input type="hidden" name="ofert" value="{$Addofert}" />
            <input type="hidden" name="mineral" value="{$Herbname}" />
            <input type="hidden" name="amount" value="{$Herbamount}" />
            <input type="hidden" name="cost" value="{$Herbcost}" />
            <input type="hidden" name="number" value="{$Astralnumber}" />
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
    <td><form method="post" action="amarket.php?view=market&amp;limit=0&amp;lista=id">
        <input type="hidden" name="szukany" value="{$Name[all]}" />
        <input type="submit" value="{$Ashow}" /></form>
    </td>
    </tr>
    {/section}
    </table>
{/if}

{if $Buy != ""}
    {$Buyinfo} <a href="amarket.php">{$Aback}</a>.<br /><br />
    <b>{$Bherb}:</b> {$Name} <br />
    <b>{$Tnumber}:</b> {$Anumber} <br />
    <b>{$Oamount}:</b> {$Amount1} <br />
    <b>{$Hcost}:</b> {$Cost} <br />
    <b>{$Hseller}:</b> <a href="view.php?view={$Sid}">{$Seller}</a> <br /><br />
    <table><form method="post" action="amarket.php?buy={$Itemid}&amp;step=buy">
    <tr><td>{$Bamount}:</td><td><input type="text" name="amount" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Abuy}" /></td></tr>
    </form></table>
{/if}

{$Message}
