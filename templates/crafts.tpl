{if $Step == ""}
    {$Craftinfo}<br /><br />
    <a href="crafts.php?step=first">{$Ajob}</a>
{/if}
{if $Step == "first"}
    {$Jobinfo}<br />
    <a href="crafts.php?step=second">{$Ayes}</a><br />
    <a href="city.php">{$Ano}</a><br /><br />
    {$Jobinfo2}
{/if}
