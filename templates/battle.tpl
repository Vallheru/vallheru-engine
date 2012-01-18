{if $Action == "" && $Battle == ""}
    {$Battleinfo}
    <br /><br />
    - <a href="battle.php?action=levellist">{$Ashowlevel}</a><br />
    - <a href="battle.php?action=showalive">{$Ashowalive}</a><br />
    - <a href="battle.php?action=monster">{$Ashowmonster}</a><br />
{/if}

{if $Action == "showalive"}
    {$Showinfo} {$Level}...<br /><br />
    <table width="90%" align="center">
    <tr>
        <th>{$Lid}</th>
        <th>{$Lname}</th>
        <th>{$Lrank}</th>
        <th>{$Lclan}</th>
        <th>{$Loption}</th>
    </tr>
    {section name=player loop=$Enemyid}
        <tr>
        <td>{$Enemyid[player]}</td>
        <td><a href="view.php?view={$Enemyid[player]}">{$Enemyname[player]}</a></td>
        <td>{$Enemyrank[player]}</td>
        <td>{$Enemytribe[player]}</td>
        <td>- <A href="battle.php?battle={$Enemyid[player]}">{$Aattack}</a></td>
        </tr>
    {/section}
    </table><br />{$Orback} <a href="battle.php">{$Bback}</a>.
{/if}

{if $Action == "levellist"}
    <form method="post" action="battle.php?action=levellist&amp;step=go">
    {$Showall} <input type="text" name="slevel" size="5" /> {$Tolevel} <input type="text" name="elevel" size="5" />
    <input type="submit" value="{$Ago}" /></form>
    {if $Step == "go"}
        <table width="90%" align="center">
        <tr>
        <th>{$Lid}</th>
        <th>{$Lname}</th>
        <th>{$Lrank}</th>
        <th>{$Lclan}</th>
        <th>{$Loption}</th>
        </tr>
        {section name=player loop=$Enemyid}
            <tr>
            <td>{$Enemyid[player]}</td>
            <td><a href="view.php?view={$Enemyid[player]}">{$Enemyname[player]}</a></td>
            <td>{$Enemyrank[player]}</td>
            <td>{$Enemytribe[player]}</td>
            <td>- <A href="battle.php?battle={$Enemyid[player]}">{$Aattack}</a></td>
            </tr>
        {/section}
        </table>
    {/if}
{/if}

{if $Battle > "0"}
    <b><u>{$Name} {$Versus} {$Enemyname}</u></b><br />
{/if}

{if $Action == "monster"}
    {if !$Fight && !$Fight1}
        {$Monsterinfo}
        <br /><br />
        <table>
        <tr>
        <td width="100"><b><u>{$Mname}</u></b></td>
        <td width="50"><b><u>{$Mlevel}</u></b></td>
        <td width="50"><b><u>{$Mhealth}</u></b></td>
        <td><b><u>{$Mturn}</u></b></td>
        <td><b><u>{$Mfast}</u></b></td>
	<td><b><u>{$Mamount}<br />{$Mmonsters}</u></b></td>
	<td><b><u>{$Mamount}<br />{$Mtimes}</u></b></td>
        </tr>
        {section name=monster loop=$Enemyid}
            <tr>
            <td>{$Enemyname[monster]}</td>
            <td>{$Enemylevel[monster]}</td>
            <td>{$Enemyhp[monster]}</td>
	    <form method="post" action="battle.php?action=monster">
                <td><input type="submit" name="fight1" value="{$Mturn}" />
		    <input type="hidden" value="{$Enemyid[monster]}" name="mid" />
		    <input type="hidden" name="write" value="Y" />
		</td>
		<td><input type="submit" name="fight" value="{$Mfast}" />
		    <input type="hidden" value="{$Enemyid[monster]}" name="mid" />
		</td>
		<td>
		    <input type="text" size="5" name="razy" value="1" />
		</td>
		<td>
		    <input type="text" size="5" name="times" value="1" />
		</td>
 	    </form>
            </tr>
        {/section}
        </table><br />{$Orback2} <a href="battle.php">{$Bback2}</a>.
    {/if}
{/if}

