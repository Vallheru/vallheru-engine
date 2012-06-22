    {$Towerinfo}<br /><br />
    <ul>
    <li><a href="wieza.php?dalej=T">{$Abuyst}</a></li>
    <li><a href="wieza.php?dalej=C">{$Abuyc}</a></li>
    <li><a href="wieza.php?dalej=B">{$Abuys}</a></li>
    <li><a href="wieza.php?dalej=O">{$Abuys2}</a></li>
    <li><a href="wieza.php?dalej=U">{$Abuys3}</a></li>
    </ul>
    {if $Next != ""}
        {if $Next == "B" || $Next == "O" || $Next == "U"}
	    {foreach $Spells as $Spells2}
		<b>{$Telement} {$Spells2@key}</b><br />
	    	<table class="dark">
            	    <tr>
            	        <th>{$Tname}</th>
            		<th>{$Tpower}</th>
            		<th>{$Tcost}</th>
            		<th>{$Tlevel}</th>
            		<th>{$Toptions}</th>
            	    </tr>
		    {foreach $Spells2 as $Spell}
		        <tr>
			    <td>{$Spell.name}</td>
			    <td>{$Spell.effect}</td>
			    <td>{$Spell.price}</td>
			    <td>{$Spell.level}</td>
			    <td>- <a href="wieza.php?dalej={$Next}&amp;buy={$Spell.id}&amp;type=S">{$Abuy}</a></td>
			</tr>
		    {/foreach}
	        </table><br />
	    {/foreach}
        {else}
	    <table class="dark">
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
                <td>- <A href="wieza.php?dalej={$Next}&amp;buy={$Itemid[tower1]}&amp;type=I">{$Abuy}</a></td>
                </tr>
            {/section}
	    </table>
        {/if}
    {/if}
