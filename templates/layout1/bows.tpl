    {if $Location == "Altara"}
        {$Shopinfo}{$Shopinfo2}<br /><br />
    {/if}
    {if $Location == "Ardulith"}
        {$Shopinfo}<br /><br />
    {/if}
    {if $Arrows > 0}
        <script src="js/bows.js"></script>
            <form method="post" action="bows.php?arrows={$Arrowsid}&amp;step=buy">
		<input type="submit" value="{$Abuy}" /> <input type="text" name="arrows1" size="5" value="0" onChange="countPrice(this.value, {$Arrowscost}, 'Q');" /> {$Tarrows} <b>{$Arrowsname}</b> {$Fora} <b><span id="quivercost">0</span></b> {$Tamount} <input type="text" name="arrows2" size="5" value="0" onChange="countPrice(this.value, {$Arrowscost2}, 'A');" /> {$Tamount2} <b>{$Arrowsname}</b> {$Fora} <b><span id="arrowcost">0</span></b> {$Tamount3}
            </form>
    {/if}
    <table class="dark">
    <tr>
    <th>{$Iname}</th>
    <th>{$Iefect}</th>
    <th>{$Ispeed}</th>
    <th>{$Idur}</th>
    <th>{$Icost}</th>
    <th>{$Ilevel}</th>
    <th>{$Ioption}</th></tr>
    {section name=item loop=$Level}
        <tr>
        <td>{$Name[item]}</td>
        <td>+{$Power[item]} Atak</td>
        <td>+{$Speed[item]}</td>
        <td>{$Durability[item]}</td>
        <td>{$Cost[item]}</td>
        <td>{$Level[item]}</td>
        <td>- <a href="bows.php?{$Tlink[item]}{$Itemid[item]}">{$Abuy}</a>{if $Crime > "0"}<br /><a href="bows.php?steal={$Itemid[item]}">{$Asteal}</a>{/if}</td>
        </tr>
    {/section}
    </table>


