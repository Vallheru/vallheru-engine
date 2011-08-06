{if $Action == "" && $Battle == ""}
    {$Battleinfo}
    <br /><br />
    - <a href="battle.php?action=levellist">{$Ashowlevel}</a><br />
    - <a href="battle.php?action=showalive">{$Ashowalive}</a><br />
    - <a href="battle.php?action=monster">{$Ashowmonster}</a><br />
{/if}

{if $Action == "showalive"}
    {$Showinfo} {$Level}...<br /><br />
    <table>
    <tr>
    <td width="20"><b><u>{$Lid}</u></b></td>
    <td width="100"><b><u>{$Lname}</u></b></td>
    <td width="100"><b><u>{$Lrank}</u></b></td>
    <td width="20"><b><u>{$Lclan}</u></b></td>
    <td width="60"><b><u>{$Loption}</u></b></td>
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
        <table>
        <tr>
        <td width="20"><b><u>{$Lid}</u></b></td>
        <td width="100"><b><u>{$Lname}</u></b></td>
        <td width="100"><b><u>{$Lrank}</u></b></td>
        <td width="20"><b><u>{$Lclan}</u></b></td>
        <td width="60"><b><u>{$Loption}</u></b></td>
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
        {if $Dalej > 0}
            <form method="post" action="battle.php?action=monster&amp;fight={$Id}">
            <input type="submit" value="{$Abattle2}" /> {$Witha} <input type="text" size="5" name="razy" value="1" /> {$Name}{$Nend}
            <input type="text" size="5" name="times" value="1" /> {$Mtimes}</form>
        {/if}
        {if $Next > 0}
            <form method="post" action="battle.php?action=monster&amp;fight1={$Id}">
            <input type="submit" value="{$Abattle2}" /> {$Witha} <input type="text" size="5" name="razy" value="1" /> {$Name}{$Nend}
            <input type="hidden" name="write" value="Y" />
            </form>
        {/if}
        <table>
        <tr>
        <td width="100"><b><u>{$Mname}</u></b></td>
        <td width="50"><b><u>{$Mlevel}</u></b></td>
        <td width="50"><b><u>{$Mhealth}</u></b></td>
        <td><b><u>{$Mturn}</u></b></td>
        <td><b><u>{$Mfast}</u></b></td>
        </tr>
        {section name=monster loop=$Enemyid}
            <tr>
            <td>{$Enemyname[monster]}</td>
            <td>{$Enemylevel[monster]}</td>
            <td>{$Enemyhp[monster]}</td>
            <td><a href="battle.php?action=monster&amp;next={$Enemyid[monster]}">{$Abattle}</a></td>
            <td><a href="battle.php?action=monster&amp;dalej={$Enemyid[monster]}">{$Abattle}</a></td>
            </tr>
        {/section}
        </table><br />{$Orback2} <a href="battle.php">{$Bback2}</a>.
    {/if}
{/if}

