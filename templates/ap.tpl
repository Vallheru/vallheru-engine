{$Info}<br /><br />
<table>
{foreach $Bonuses as $bonus}
    {if $bonus@iteration is not div by 2}
        <tr>
    {/if}
    <td style="padding: 3%;">
        <form method="post" action="ap.php?select={$bonus.id}"/>
            <input type="checkbox" name="{$bonus.id}" value="Y"><u><b>{$bonus.name}</b></u><br />
    	    {$bonus.desc}<br />
	    {$Tlevel} {$bonus.curlevel}<br />
    	    {$Tlevels} {$bonus.levels}<br />
    	    {$Cost} {$bonus.cost}<br />
	    <input type="submit" value="{$Select}" />
        </form>
    </td>
    {if $bonus@iteration is div by 2}
        </tr>
    {/if}
{/foreach}
</table>
