{if $Step == ""}
    {$Craftinfo}<br /><br />
    <ul>
        <li><a href="crafts.php?step=register">{$Aregister}</a></li>
        <li><a href="crafts.php?step=first">{$Ajob}</a></li>
    </ul>
{/if}
{if $Step == "register"}
    {$Reginfo}<br /><br />
    {$Reginfo2}<br /><br />
    <form method="post" action="crafts.php?step=register">
        <input type="submit" value="{$Aregister2}" /> {$Tregister} {html_options name=skill options=$Oskills}
    </form><br />
    <a href="crafts.php">{$Ano}</a>
{/if}
{if $Step == "first"}
    {$Jobinfo}<br />
    <ul>
    {section name=job loop=$Jobs}
        <li>{$Jobs[job]}<br />
        <a href="crafts.php?step={$smarty.section.job.index}">{$Ayes}{$Jenergy[job]}{$Ayes2}</a><br /><br /></li>
    {/section}
    </ul>
    <a href="crafts.php">{$Ano}</a><br /><br />
    <a href="crafts.php?step=first">{$Jobinfo2}</a>
{/if}
{if $Step != "" && $Step != "first" && $Step != "register"}
    {$Result}<br />
{/if}
