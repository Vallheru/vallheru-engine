<map name="{$Gamename}">
    {if $Action == "" && $Location == "Altara" && $Action2 == ""}
        <area shape="rect" coords="40, 50, 260, 135" href="travel.php?action=gory" title="{$Amountains}" />
        <area shape="rect" coords="40, 150, 150, 280" href="travel.php?action=las" title="{$Aforest}" />
        <area shape="rect" coords="80, 290, 100, 310" href="travel.php?action=city2" title="{$Aelfcity}" />
    {/if}
    {if $Action == "" && $Location == "Las" && $Action2 == ""}
        <area shape="rect" coords="190, 220, 210, 250" href="travel.php?action=powrot" title="{$Aaltara}" />
    {/if}
    {if $Action == "" && $Location == "Ardulith" && $Action2 == ""}
        <area shape="rect" coords="40, 50, 260, 135" href="travel.php?action=gory" title="{$Amountains}" />
        <area shape="rect" coords="190, 220, 210, 250" href="travel.php?action=powrot" title="{$Aaltara}" />
    {/if}
    {if $Action == "" && $Location == "Góry" && $Action2 == ""}
        <area shape="rect" coords="40, 150, 150, 280" href="travel.php?action=las" title="{$Aforest}" />
        <area shape="rect" coords="190, 220, 210, 250" href="travel.php?action=powrot" title="{$Aaltara}" />
    {/if}
</map>
{if $Action == "" && $Location == "Altara" && $Action2 == ""}
    {$Altarainfo}<br />
    <img src="images/mapa.jpg" usemap="#{$Gamename}" width="400" border="0" /><br />
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

{if $Action == "" && $Location == "Ardulith" && $Action2 == ""}
    {$Altarainfo}<br />
    <img src="images/mapa.jpg" usemap="#{$Gamename}" width="400" border="0" /><br />
{/if}

{if $Portal == "Y"}
    {$Portal2}
{/if}

{if $Portal == "N"}
    {$Portal3}
{/if}

{if $Action == "" && ($Location != "Altara" && $Location != "Ardulith") && $Action2 == ""}
    {$Outside}<br />
    <img src="images/mapa.jpg" usemap="#{$Gamename}" width="400" border="0" /><br />
{/if}
