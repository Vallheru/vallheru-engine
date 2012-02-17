{if ($Location == "Altara" || $Location == "Ardulith") && $Prisoner == ""}
    {$Jailinfo}<br /><br /><br />
    {if $Number > "0"}
        {section name=jail loop=$Name}
            <b>{$Pname}:</b> <a href="view.php?view={$Id[jail]}">{$Name[jail]}</a><br />
            <b>{$Pid}:</b> {$Id[jail]}<br />
            <b>{$Pdate}:</b> {$Date[jail]}<br />
            <b>{$Preason}:</b> {$Verdict[jail]}<br />
            <b>{$Pduration}:</b> {$Duration[jail]} ({$Duration2[jail]} {$Pduration2})<br />
	    {if $Cost[jail] > 0}
                <b>{$Pcost}:</b> <a href=jail.php?prisoner={$Jailid[jail]}>{$Cost[jail]} {$Goldcoins}</a><br /><br /><br />
	    {else}
	        <b>{$Nocost}</b><br /><br /><br />
	    {/if}
        {/section}
    {/if}
    {if $Number == "0"}
        <center>{$Noprisoners}</center>
    {/if}
{/if}

{if $Location == "Lochy"}
    {$Youare}<br />
    <b>{$Pdate}:</b> {$Date}<br />
    <b>{$Pduration}:</b> {$Duration} ({$Duration2} {$Pduration2})<br />
    <b>{$Preason}:</b> {$Verdict}<br />
    {if $Cost > 0}
        <b>{$Pcost}:</b> {$Cost}<br />
    {else}
        <b>{$Nocost}</b></br />
    {/if}
    {if $Escape != ""}
        <a href="jail.php?escape">{$Escape}</a>
    {/if}
{/if}

{if $Prisoner != ""}
    {if $Step == ""}
        {$Youwant}<b>{$Prisonername}</b>?
        <form method="post" action="jail.php?prisoner={$Prisoner}&step=confirm">
            <input type="submit" value="{$Ayes}" />
        </form>
        <br />
        (<a href="jail.php">{$Aback}</a>)
    {/if}
{/if}

