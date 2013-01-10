{if $Action == "" && $Battle == ""}
    {$Battleinfo}
    <br />
    <ul>
    <li><a href="battle.php?action=showalive">{$Ashowalive}</a></li>
    <li><a href="battle.php?action=monster">{$Ashowmonster}</a></li>
    </ul>
{/if}

{if $Action == "showalive"}
    {$Showinfo}<br /><br />
    <table width="90%" align="center">
    <tr>
        <th>{$Lid}</th>
        <th>{$Lname}</th>
        <th>{$Lrank}</th>
        <th>{$Lclan}</th>
	<th>{$Lrep}</th>
        <th>{$Loption}</th>
    </tr>
    {section name=player loop=$Enemyid}
        <tr align="center">
        <td>{$Enemyid[player]}</td>
        <td><a href="view.php?view={$Enemyid[player]}">{$Enemyname[player]}</a></td>
        <td>{$Enemyrank[player]}</td>
        <td>{$Enemytribe[player]}</td>
	<td>{$Enemyrep[player]}</td>
        <td>- <A href="battle.php?battle={$Enemyid[player]}">{$Aattack}</a></td>
        </tr>
    {/section}
    </table><br />{$Orback} <a href="battle.php">{$Bback}</a>.
{/if}

{if $Battle > "0"}
    <b><u>{$Name} {$Versus} {$Enemyname}</u></b><br />
{/if}

{if $Action == "monster"}
    {if !$Fight && !$Fight1}
        <script src="js/battle.js"></script>
        {$Monsterinfo}
        <br /><br />
        <table align="center" width="95%">
        <tr>
        <th>{$Mname}</th>
        <th>{$Mlevel}</th>
        <th>{$Mturn}</th>
        <th>{$Mfast}</th>
	<th>{$Mamount}<br />{$Mmonsters}</th>
	<th>{$Mamount}<br />{$Mtimes}</th>
	<th>{$Menergy}<br />{$Menergy2}</th>
        </tr>
	{foreach $Monsters as $monster}
            <tr>
            <td>{$monster.name}</td>
            <td>{$monster.elevel}</td>
	    <form id="fight{$monster@index}" method="post" action="battle.php?action=monster">
                <td><input type="submit" name="fight1" value="{$Mturn}" />
		    <input type="hidden" value="{$monster.id}" name="mid" />
		    <input type="hidden" name="write" value="Y" />
		</td>
		<td><input type="submit" name="fight" value="{$Mfast}" />
		</td>
		<td>
		    <input id="amount" type="text" size="5" name="razy" value="1" onChange="countEnergy({$monster.level}, {$monster@index});" />
		</td>
		<td>
		    <input id="times" type="text" size="5" name="times" value="1" onChange="countEnergy({$monster.level}, {$monster@index});" />
		</td>
 	    </form>
	    <td id="mon{$monster@index}">{$monster.energy}/{$monster.energy}</td>
            </tr>
        {/foreach}
        </table><br />{$Orback2} <a href="battle.php">{$Bback2}</a>.
    {/if}
{/if}

