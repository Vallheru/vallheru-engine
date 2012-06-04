{if $Action == "" && $Health > "0"}
    {$Portaltext}<br />
    <ul>
    <li><a href="portal.php?action1=fight">{$Afight2}</a></li>
    <li><a href="portal.php?action1=retreat">{$Aretreat}</a></li>
    </ul>
{/if}

{if $Action == "retreat"}
    {$Portaltext} <a href="city.php">{$Ahere}</a>.
{/if}

{if $Win == "1"}
    {$Portaltext}<br />
    <ul>
    <li><a href="portal.php?action1=fight&amp;step=sword">{$Anext}</a></li>
    </ul>
{/if}

{if $Step != ""}
    {$Steptext} {$Item} {$Tgo} <a href=equip.php>{$Ahere}</a>
{/if}
