{if $Action == "" && $Step == "" && $Quest == 'N'}
    {$Labinfo} <a href="grid.php?action=explore">{$Ayes}.</a>
{/if}

{if ($Chance == "1" || $Chance == "2" || $Chance == "4" || $Chance == "5" || $Chance == "7" || $Chance == "8" || $Chance == "9" || $Chance == 11) && $Quest == 'N'}
    {$Action2}
{/if}

{if $Chance == "3" && $Quest == 'N'}
    {$Action2} <b>{$Goldgain}</b> {$Action3}
{/if}

{if $Chance == "6" && $Quest == 'N'}
    {$Action2} <b>{$Mithgain}</b> {$Action3}
{/if}

{if $Action == "explore" && ($Chance < 10 || $Chance == 11) && $Quest == 'N'}
    <br /><br />... <a href="grid.php?action=explore">{$Aexp}</a> {$Tnext} {$Energyleft} {$Enpts}.)
{/if}

