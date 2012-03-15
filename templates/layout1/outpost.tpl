{if $Step == ""}
    {$Outinfo}<br /><br />
    <a href="outpost.php?step=first">{$Ajob}</a>
{/if}
{if $Step == "first"}
    {$Jobinfo}<br />
    <ul>
    {section name=job loop=$Jobs}
        <li>{$Jobs[job]}<br />
        <a href="outpost.php?step={$smarty.section.job.index}">{$Ayes}</a><br /><br /></li>
    {/section}
    </ul>
    <a href="city.php">{$Ano}</a><br /><br />
    <a href="outpost.php?step=first">{$Jobinfo2}</a>
{/if}
{if $Step != "" && $Step != "first"}
    {$Result}<br />
{/if}
