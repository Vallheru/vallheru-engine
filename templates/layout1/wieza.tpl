{if $Buy == ""}
    {$Towerinfo}<br /><br />
    <ul>
    <li><a href="wieza.php?dalej=T">{$Abuyst}</a></li>
    <li><a href="wieza.php?dalej=C">{$Abuyc}</a></li>
    <li><a href="wieza.php?dalej=B">{$Abuys}</a></li>
    <li><a href="wieza.php?dalej=O">{$Abuys2}</a></li>
    <li><a href="wieza.php?dalej=U">{$Abuys3}</a></li>
    </ul>
    {if $Next != ""}
        <table class="dark">
        {if $Next == "B" || $Next == "O" || $Next == "U"}
            <tr>
            <th>{$Tname}</th>
            <th>{$Tpower}</th>
            <th>{$Tcost}</th>
            <th>{$Tlevel}</th>
	    <th>{$Telement}</th>
            <th>{$Toptions}</th>
            </tr>
            {section name=tower loop=$Spells}
                <tr>
                <td>{$Spells[tower].nazwa}</td>
                <td>{$Spells[tower].effect}</td>
                <td>{$Spells[tower].cena}</td>
                <td>{$Spells[tower].poziom}</td>
		<td>{$Spells[tower].element}</td>
                <td>- <A href="wieza.php?buy={$Itemid[tower]}&type=S">{$Abuy}</a></td>
                </tr>
            {/section}
        {else}
            <tr>
           <td width="100"><b><u>{$Tname}</u></b></td>
            <td width="100"><b><u>{$Tpower}</u></b></td>
            <td width="50"><b><u>{$Tcost}</u></b></td>
            <td><b><u>{$Tlevel}</u></b></td>
            <td><b><u>{$Toptions}</u></b></td>
            </tr>
            {section name=tower1 loop=$Name}
                <tr>
                <td>{$Name[tower1]}</td>
                <td>{$Power[tower1]}</td>
                <td>{$Cost[tower1]}</td>
                <td>{$Itemlevel[tower1]}</td>
                <td>- <A href="wieza.php?buy={$Itemid[tower1]}&amp;type=I">{$Abuy}</a></td>
                </tr>
            {/section}
        {/if}
        </table>
    {/if}
{/if}

{if $Buy != ""}
    {$Message}
{/if}
