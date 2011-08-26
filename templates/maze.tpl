{if $Action == "" && $Step == ""}
    {$Mazeinfo}<br /><br />
    <form method="post" action="maze.php?action=explore">
        <input type="submit" value="{$Explore}" /> <input type="text" name="amount" value="{$Amount}" size="5" /> {$Times}.
    </form>
{/if}

{if $Action == "explore" && $Step == ""}
    {$Action2}
    {if $Back != ""}
        <a href="maze.php">{$Back}</a>
    {else}
        <br />
    {/if}
    {if $Encounter == TRUE}
        {$Youmeet} {$Name}{$Fight2}<br />
       <a href="maze.php?step=battle&amp;type=T">{$Yturnf}</a><br />
       <a href="maze.php?step=battle&amp;type=S">{$Ynormf}</a><br />
       <a href="maze.php?step=run">{$Ano}</a><br />
    {/if}
{/if}

{if $Step == "run"}
    {if $Chance > "0"}
        {$Escapesucc} {$Ename}! {$Escapesucc2} {$Expgain} {$Escapesucc3}<br />
    {/if}
    {if $Health > "0" && $Chance > "0"}
        <br />{$Link}
    {/if}
{/if}

{if $Step == "battle"}
    {$Link}
{/if}
