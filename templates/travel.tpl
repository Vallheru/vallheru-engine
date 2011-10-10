{if $Action == "" && $Location == "Altara" && $Action2 == ""}
    {$Altarainfo}<br />
    <ul>
    <li><a href="travel.php?action=gory">{$Amountains}</a></li>
    <li><a href="travel.php?action=las">{$Aforest}</a></li>
    <li><a href="travel.php?action=city2">{$Aelfcity}</a></li>
    </ul>
    {if $Maps == "1"}
        {$Portal1}<br />
        - <a href="travel.php?akcja=tak">{$Ayes}</a><br />
        - <a href="travel.php?akcja=nie">{$Ano}</a><br />
    {/if}
    {if $Tportals[0] != ""}
        <br /><br />{$Tporinfo}<br />
        {section name=portals loop=$Tportals}
            <a href="portals.php?step={$Tporlink[portals]}">{$Tportals[portals]}</a><br />
        {/section}
    {/if}
{/if}

{if $Action2 != ""}
    <ul>
    <li><a href="travel.php?akcja={$Action2}&amp;step=caravan">{$Acaravan}</a></li>
    <li><a href="travel.php?akcja={$Action2}&amp;step=walk">{$Awalk}</a></li>
    <li><a href="travel.php?akcja={$Action2}&amp;step=magic">{$Aportal}</a></li>
    <li><a href="travel.php">{$Aback}</a></li>
    </ul>
{/if}

{if $Portal == "Y"}
    {$Portal2}
{/if}

{if $Portal == "N"}
    {$Portal3}
{/if}

{if $Action == "" && $Location == "Las" && $Action2 == ""}
    {$Outside}<br />
    - <a href="travel.php?action=powrot">{$Aaltara}</a>
{/if}

{if $Action == "" && $Location == "Góry" && $Action2 == ""}
    {$Outside}<br />
    <ul>
    <li><a href="travel.php?action=las">{$Aforest}</a></li>
    <li><a href="travel.php?action=powrot">{$Aaltara}</a></li>
    </ul>
{/if}

{if $Action == "" && $Location == "Ardulith" && $Action2 == ""}
    {$Altarainfo}<br />
    <ul>
    <li><a href="travel.php?action=gory">{$Amountains}</a></li>
    <li><a href="travel.php?action=powrot">{$Aaltara}</a></li>
    </ul>
{/if}
