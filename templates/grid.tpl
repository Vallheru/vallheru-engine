{if $Action == "" && $Step == "" && $Quest == 'N'}
    {$Labinfo}<br /><br />
    <form method="post" action="grid.php?action=explore">
        <input type="submit" value="{$Explore}" /> <input type="text" name="amount" value="{$Amount}" size="5" /> {$Times}.
    </form>
{/if}

{if $Action == "explore" && $Quest == 'N'}
    {$Action2}
    {if $Back != ""}
        <a href="grid.php">{$Back}</a>
    {else}
        <br />
    {/if}
{/if}

