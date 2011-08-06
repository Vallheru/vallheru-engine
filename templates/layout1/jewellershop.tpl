{$Shopinfo}<br /><br />

<table align="center" width="75%" class="dark">
    <tr>
        <td><b>{$Tname}</b></td>
        <td><b>{$Tbonus}</b></td>
        <td><b>{$Tamount}</b></td>
        <td><b>{$Tcost}</b></td>
        <td><b>{$Taction}</b></td>
    </tr>
    {section name=jeweller loop=$Rid}
    <tr>
        <td>{$Rname[jeweller]}</td>
        <td align="center">1</td>
        <td align="center">{$Ramount[jeweller]}</td>
        <td align="center">500</td>
        <td align="center"><a href="jewellershop.php?buy={$Rid[jeweller]}">{$Abuy}</td>
    </tr>
    {/section}
</table>

<br /><br />{$Message}