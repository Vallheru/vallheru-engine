{if $Action == ""}
    {if $Health > "0"}
        {$Couldyou} <a href="hospital.php?action=heal">{$Aheal}</a>?
        <br />{$Itcost} <b>{$Need}</b> {$Goldcoins}
    {/if}
    {if $Health <= "0"}
        {$Couldyou2}
        <br />{$Itcost2} <b>{$Need}</b> {$Goldcoins}<br />
        <a href="hospital.php?action=ressurect">{$Ayes}</a>
    {/if}
{/if}

