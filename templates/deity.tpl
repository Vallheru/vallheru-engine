{if $Deity == "" && $Step == ""}
    {$Deityinfo}<br /><br />
    {section name=deity loop=$Godname}
        - <a href="deity.php?deity={$Godoption[deity]}">{$Godname[deity]}</a><br />
    {/section}
	- <a href="stats.php">{$Ateist}</a>
    <br />
{/if}

{if $Deity != "" && $Pldeity == ""}
    {$Godinfo}
    <form method="post" action="deity.php?deity={$Deity}&amp;step=wybierz">
    <input type="submit" value="{$Aselect}" />
    </form><br />
    (<a href="deity.php">{$Aback}</a>)
{/if}

{if $Step == "wybierz" && $Pldeity == ""}
    <br/ >{$Youselect} {$God}. {$Click} <a href=stats.php>{$Here}</a> {$Forback}.
{/if}

{if $Step == "change"}
    {if $Step2 == ""}
        {$Change} {$Ccost} {$Change2}<br />
        <ul>
            <li><a href="deity.php?step=change&amp;step2=change">{$Ayes}</a></li>
            <li><a href="stats.php">{$Ano}</a></li>
        </ul>
    {/if}
    {if $Step2 == "change"}
        {$Youchange} {$Pdeity}{$Youmay} <a href="deity.php">{$Aselect2}</a> {$Tdeity}
    {/if}
{/if}

