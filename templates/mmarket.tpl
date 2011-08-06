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
    {$Sinfo} <a href="mmarket.php">{$Aback}</a>. {$Sinfo2}<br /><br />
    <form method="post" action="mmarket.php?view=market&amp;limit=0&amp;lista=name"><table>
    <tr><td colspan="2">{$Potion2} <input type="text" name="szukany" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Asearch}" /></td></tr>
    </table></form>
{/if}

{if $View == "market"}
    {$Viewinfo} <a href=mmarket.php>{$Aback}</a>.<br /><br />
    <table>
    <tr>
    <td width="150"><a href="mmarket.php?view=market&amp;lista=name&amp;limit=0"><b><u>{$Tname}</u></b></a></td>
    <td width="100" align="center"><a href="mmarket.php?view=market&amp;lista=efect&amp;limit=0"><b><u>{$Tefect}</u></b></a></td>
    <td width="50"><a href="mmarket.php?view=market&amp;lista=amount&amp;limit=0"><b><u>{$Tamount}</u></b></a></td>
    <td width="50"><a href="mmarket.php?view=market&amp;lista=cost&amp;limit=0"><b><u>{$Tcost}</u></b></a></td>
    <td width="100"><a href="mmarket.php?view=market&amp;lista=owner&amp;limit=0"><b><u>{$Tseller}</u></b></a></td>
    <td width="100"><b><u>{$Toptions}</u></b></td>
    </tr>
    {section name=mmarket loop=$Item}
        {$Item[mmarket]}
    {$Link[mmarket]}
    {/section}
    </table>
    {$Previous}{$Next}
{/if}

{if $View == "add"}
    {$Addinfo} <a href="mmarket.php">{$Aback}</a>.<br /><br />
    <form method="post" action="mmarket.php?view=add&amp;step=add"><table>
    <tr><td colspan="2">{$Potion}: <select name="przedmiot">
    {section name=mmarket1 loop=$Name}
        <option value="{$Itemid[mmarket1]}">{$Name[mmarket1]} ({$Pamount2}: {$Amount[mmarket1]})</option>
    {/section}</select></td></tr>
    <tr><td>{$Pamount}:</td><td><input type="text" name="amount" /></td></tr>
    <tr><td>{$Pcost}:</td><td><input type="text" name="cost" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}

{if $View == "all"}
    {$Listinfo}<br />
    <table>
    <tr>
    <td><b><u>{$Pname}</u></b></td><td><b><u>{$Pamount}</u></b></td><td align="center"><b><u>{$Paction}</u></b></td>
    </tr>
    {section name=all loop=$Name}
        <tr>
        <td>{$Name[all]}</td>
    <td align="center">{$Amount[all]}</td>
    <td><form method="post" action="mmarket.php?view=market&amp;limit=0&amp;lista=id">
        <input type="hidden" name="szukany" value="{$Name[all]}" />
        <input type="submit" value="{$Ashow}" /></form>
    </td>
    </tr>
    {/section}
    </table>
{/if}

{if $Buy != ""}
    {$Buyinfo} <a href="mmarket.php">{$Aback}</a>.<br /><br />
    <b>{$Potion}:</b> {$Name} <br />
    {if $Type == "M" || $Type == "P"}
        <b>{$Ppower}:</b> {$Power} %<br />
    {/if}
    <b>{$Oamount}:</b> {$Amount1} <br />
    <b>{$Pcost}:</b> {$Cost} <br />
    <b>{$Pseller}:</b> <a href="view.php?view={$Sid}">{$Seller}</a> <br /><br />
    <table><form method="post" action="mmarket.php?buy={$Itemid}&amp;step=buy">
    <tr><td>{$Bamount}:</td><td><input type="text" name="amount" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Abuy}" /></td></tr>
    </form></table>
{/if}

{$Message}
