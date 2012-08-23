   {$Armorinfo}
    <ul>
    <li><a href="armor.php?dalej=A">{$Aarmors}</a></li>
    <li><a href="armor.php?dalej=H">{$Ahelmets}</a></li>
    <li><a href="armor.php?dalej=L">{$Alegs}</a></li>
    <li><a href="armor.php?dalej=S">{$Ashields}</a></li>
    </ul>
    {if $Next != ''}
        <table class="dark">
        <tr>
        <td width="100"><b><u>{$Iname}</u></b></td>
        <td><b><u>{$Idur}</u></b></td>
        <td width="100"><b><u>{$Iefect}</u></b></td>
        <td width="50"><b><u>{$Icost}</u></b></td>
        <td><b><u>{$Ilevel}</u></b></td>
        <td><b><u>{$Iagi}</u></b></td>
        <td><b><u>{$Ioption}</u></b></td>
        </tr>
        {section name=number loop=$Name}
            <tr>
            <td>{$Name[number]}</td>
            <td>{$Durability[number]}</td>
            <td>+{$Power[number]} Obrona</td>
            <td>{$Cost[number]}</td>
            <td>{$Level[number]}</td>
            <td>{$Agility[number]}</td>
            <td>- <a href="armor.php?dalej={$Next}&amp;buy={$Id[number]}">{$Abuy}</a>{if $Crime > "0"}<br /><a href="armor.php?steal={$Id[number]}">{$Asteal}</a>{/if}</td>
            </tr>
        {/section}
        </table>
    {/if}

