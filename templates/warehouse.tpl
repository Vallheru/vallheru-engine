{if $Action == ""}
    {$Warehouseinfo}<br />
    {$Caravaninfo}
    <table align="center" width="95%">
        <tr>
            <th>{$Tmin}</th>
            <th align="center">{$Tcost}</th>
            <th align="center">{$Tamount}</th>
            <th>{$Taction}</th>
        </tr>
        {section name=warehouse loop=$Minname}
            <tr>
                <td>{$Minname[warehouse]}</td>
                <td align="center">{$Costmin[warehouse]} / {$Sellmin[warehouse]}</td>
                <td align="center">{$Amountmin[warehouse]}</td>
                <td>
                    <a href="warehouse.php?action=sell&amp;item={$smarty.section.warehouse.index}">{$Asell}</a><br />
                    <a href="warehouse.php?action=buy&amp;item={$smarty.section.warehouse.index}">{$Abuy}</a>
                </td>
            </tr>
        {/section}
    </table>
    <br /><br />
    <table align="center" width="95%">
        <tr>
            <th>{$Therb}</th>
            <th align="center">{$Tcost}</th>
            <th align="center">{$Tamount}</th>
            <th>{$Taction}</th>
        </tr>
        {section name=warehouse2 loop=$Herbname}
            <tr>
                <td>{$Herbname[warehouse2]}</td>
                <td align="center">{$Costherb[warehouse2]} / {$Sellherb[warehouse2]}</td>
                <td align="center">{$Amountherb[warehouse2]}</td>
                <td>
                    <a href="warehouse.php?action=sell&amp;item={$Herb[warehouse2]}">{$Asell}</a><br />
                    <a href="warehouse.php?action=buy&amp;item={$Herb[warehouse2]}">{$Abuy}</a><br />
                </td>
            </tr>
        {/section}
    </table>
{else}
    <script src="js/warehouse.js"></script>
{/if}

{if $Action == "sell"}
    {$Warehouseinfo2}<br />
    {$Tgold}<br /><br />
    <form method="post" id="warehouse" action="warehouse.php?action=sell&amp;item={$Item}&amp;action2=sell">
        <input type="submit" value="{$Asell}" /> <input type="text" name="amount" size="5" onChange="countPrice({$Price}, this.value, {$Iamount});"/>{$Tamount}{$Itemname}<span id="acost"></span>{$Youhave}<a href="#" onClick="buyAll({$Iamount}, {$Price});">{$Iamount}</a>{$Tamount}{$Itemname}.
    </form>
    <br /><br />
    <a href="warehouse.php">{$Aback}</a>
{/if}

{if $Action == "buy"}
    {$Warehouseinfo3}<br /><br />
    <form method="post" id="warehouse" action="warehouse.php?action=buy&amp;item={$Item}&amp;action2=buy">
        <input type="submit" value="{$Abuy}" /> <input type="text" name="amount" size="5" onChange="countPrice({$Price}, this.value, {$Iamount});"/>{$Tamount}{$Itemname} <span id="acost"></span> - {$Wamount}<a href="#" onClick="buyAll({$Iamount}, {$Price});">{$Iamount}</a>{$Tamount}{$Itemname}.
    </form>
    <br /><br />
    <a href="warehouse.php">{$Aback}</a>
{/if}
