{if $Buy == ""}
    {$Weaponinfo}<br /><br />
    <table class="dark">
    <tr>
    <td width="100"><b><u>{$Iname}</u></b></td>
    <td width="100"><b><u>{$Iefect}</u></b></td>
    <td width="50"><b><u>{$Ispeed}</u></b></td>
    <td width="50"><b><u>{$Idur}</u></b></td>
    <td width="50"><b><u>{$Icost}</u></b></td>
    <td><b><u>{$Ilevel}</u></b></td>
    <td><b><u>{$Ioption}</u></b></td>
    </tr>
    {section name=item loop=$Level}
        <tr>
        <td>{$Name[item]}</td>
        <td>+{$Power[item]} Atak</td>
        <td>+{$Speed[item]}%</td>
        <td>{$Durability[item]}</td>
        <td>{$Cost[item]}</td>
        <td>{$Level[item]}</td>
        <td>- <a href="weapons.php?buy={$Itemid[item]}">{$Abuy}</a>{if $Crime > "0"}<br /><a href="weapons.php?steal={$Itemid[item]}">{$Asteal}</a>{/if}</td>
        </tr>
    {/section}
    </table>
{/if}

{if $Buy > 0}
    {$Youpay} <b>{$Cost}</b> {$Andbuy} <b>{$Name} {$Withp} +{$Power}</b> {$Topower}
{/if}        
