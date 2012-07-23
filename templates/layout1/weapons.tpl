{$Weaponinfo}<br /><br />
<table class="dark">
<tr>
<th>{$Iname}</th>
<th>{$Iefect}</th>
<th>{$Ispeed}</th>
<th>{$Idur}</th>
<th>{$Icost}</th>
<th>{$Ilevel}</th>
<th>{$Ioption}</th>
</tr>
{foreach $Weapons as $weapon}
    <tr>
    <td>{$weapon.name}</td>
    <td>+{$weapon.power} Atak</td>
    <td>+{$weapon.szyb}%</td>
    <td>{$weapon.maxwt}</td>
    <td>{$weapon.cost}</td>
    <td>{$weapon.minlev}</td>
    <td>- <a href="weapons.php?buy={$weapon.id}">{$Abuy}</a>{if $Crime > "0"}<br /><a href="weapons.php?steal={$weapon.id}">{$Asteal}</a>{/if}</td>
    </tr>
{/foreach}
</table>   
