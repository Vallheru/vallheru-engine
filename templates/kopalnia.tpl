{if $Action == "dig"}
    {$Youfind}<br /><br />
{/if}

{if $Health > "0"}
    {$Minesinfo}<br /><br />
    <form method="post" action="kopalnia.php?action=dig">
        <input type="submit" value="{$Asearch}" /> {$Tminerals} <input type="text" name="amount" size="5" value="{$Curen}" /> {$Tamount}
    </form><br />
    - <a href="gory.php">{$Ano}</a><br />
{/if}
