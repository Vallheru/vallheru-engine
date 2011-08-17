{if $View == "" && $Remowe == "" && $Buy == ""}
    {$Minfo}.<br />
    <ul>
    <li><a href="{$SCRIPT_NAME}?view=market&amp;lista=id&amp;limit=0">{$Aview}</a></li>
    <li><a href="{$SCRIPT_NAME}?view=szukaj">{$Asearch}</a></li>
    <li><a href="{$SCRIPT_NAME}?view=add">{$Aadd}</a></li>
    <li><a href="{$SCRIPT_NAME}?view=del">{$Adelete}</a></li>
    <li><a href="{$SCRIPT_NAME}?view=all&amp;limit=0">{$Alist}</a></li>
    </ul>
    (<a href="market.php">{$Aback2}</a>)
{/if}

{if $View == "szukaj"}
    {$Sinfo} <a href="imarket.php">{$Aback}</a>{$Sinfo2}<br /><br />
    <form method="post" action="imarket.php?view=market&amp;limit=0&amp;lista=name"><table>
    <tr><td colspan="2" align="left">{$Item}: <input type="text" name="szukany" /></td></tr>
    <tr><td colspan="2" align="left"><input type="submit" value="{$Asearch}" /></td></tr>
    </table></form>
{/if}

{if $View == "market"}
    {$Viewinfo} <a href="imarket.php">{$Aback}</a>.<br /><br />
    <table>
    <tr>
    <td width="100"><a href="imarket.php?view=market&amp;lista=name&amp;limit=0"><b><u>{$Tname}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market&amp;lista=power&amp;limit=0"><b><u>{$Tpower}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market&amp;lista=wt&amp;limit=0"><b><u>{$Tdur}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market&amp;lista=szyb&amp;limit=0"><b><u>{$Tspeed}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market&amp;lista=zr&amp;limit=0"><b><u>{$Tagi}</u></b></a></td>
    <td width="50"><a href="imarket.php?view=market&amp;lista=minlev&amp;limit=0"><b><u>{$Tlevel}</u></b></a></td>
    <td width="50"><a href="imarket.php?view=market&amp;lista=amount&amp;limit=0"><b><u>{$Tamount}</u></b></a></td>
    <td width="100"><a href="imarket.php?view=market&amp;lista=cost&amp;limit=0"><b><u>{$Tcost}</u></b></a></td>
    <td width="50"><a href="imarket.php?view=market&amp;lista=owner&amp;limit=0"><b><u>{$Tseller}</u></b></a></td>
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
        <td>{$Cost[item]}</td>
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
    {$Previous}{$Next}
{/if}

{if $View == "add"}
    {$Addinfo} <a href="imarket.php">{$Aback}</a>.<br /><br />
    <form method="post" action="imarket.php?view=add&amp;step=add"><table>
    <tr><td colspan="2">
    {$Item}: <select name="przedmiot">
    {section name=item1 loop=$Name}
        <option value="{$Itemid[item1]}">{$Name[item1]} (+{$Ipower[item1]})
	{if $Ispeed[item1] > 0}
	    (+{$Ispeed[item1]} {$Ispd})
	{/if}
	{if $Iagi[item1] > 0}
	    (-{$Iagi[item1]}% {$Iag})
	{elseif $Iagi[item1] < 0}
	    ({$Iagi[item1]}% {$Iag})
	{/if}
	({$Idur[item1]}/{$Imaxdur[item1]}) ({$Iamount}: {$Amount[item1]})</option>
    {/section}</select></td></tr>
    <tr><td>{$Iamount2}:</td><td><input type="text" name="amount" /></td></tr>
    <tr><td>{$Icost}:</td><td><input type="text" name="cost" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}

{if $Buy != ""}
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
    {if $Type == "R"}
        <b>{$Aamount}:</b> {$Dur} <br />
    {/if}
    {if $Type == "G"}
        <b>{$Hamount}:</b> {$Dur} <br />
    {/if}
    <b>{$Oamount}:</b> {$Amount1} <br />
    <b>{$Icost}:</b> {$Cost} <br />
    <b>{$Iseller}:</b> <a href="view.php?view={$Sid}">{$Seller}</a> <br /><br />
    <form method="post" action="imarket.php?buy={$Itemid}&amp;step=buy"><table>
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
        <td><form method="post" action="imarket.php?view=market&amp;limit=0&amp;lista=id">
            <input type="hidden" name="szukany" value="{$Name[all]}" />
            <input type="submit" value="{$Ashow}" /></form>
        </td>
        </tr>
    {/section}
    </table>
    {$Tlinks}
{/if}

{$Message}
