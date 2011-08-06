{if $Step == ''}
   {$Apinfo} <b>{$Ap}</b> {$Ap2}.<br /><br />
    <form method="post" action="ap.php?step=add">
    - +{$Strength} {$Nstrength} <input type="text" name="strength" size="5" value="0" /><br />
    - +{$Agility} {$Nagility} <input type="text" name="agility" size="5" value="0" /><br />
    - +{$Speed} {$Nspeed} <input type="text" name="szyb" size="5" value="0" /><br />
    - +{$Endurance} {$Ncond} <input type="text" name="wytrz" size="5" value="0" /><br />
    - +{$Stat} {$Nint} <input type="text" name="inteli" size="5" value="0" /><br />
    - +{if $Stat2 != "0"}{$Stat2}{/if}{if $Stat2 == "0"}{$Stat}{/if} {$Nwisdom} <input type="text" name="wisdom" size="5" value="0" /><br />
    <input type="submit" value="{$Aadd}" /></form>
{/if}

{if $Step == 'add'}
    {$Youget}: <br />
    {section name=stats loop=$Amount}
        <b>{$Amount[stats]}</b> {$Name[stats]}<br />
    {/section}
    {$Click} <a href="stats.php">{$Here}</a> {$Fora}.
{/if}
