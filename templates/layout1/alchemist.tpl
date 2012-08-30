{if $Alchemist == ''}
    {$Awelcome}<br /><br />
    <ul>
    <li><a href="alchemik.php?alchemik=przepisy">{$Arecipes}</a></li>
    <li><a href="alchemik.php?alchemik=pracownia">{$Amake}</a></li>
    {if $Astral == "Y"}
        <li><a href="alchemik.php?alchemik=astral">{$Aastral}</a></li>
    {/if}
    </ul>
{/if}

{if $Alchemist == "przepisy"}
    {$Recipesinfo}
    <table align="center" width="95%">
    <tr>
    <th>{$Rname}</th>
    <th>{$Rcost}</th>
    <th>{$Rlevel}</th>
    <th>{$Roption}</th>
    </tr>
    {section name=alchemy loop=$Name}
        <tr>
        <td>{$Name[alchemy]}</td>
        <td align="center">{$Cost[alchemy]}</td>
        <td align="center">{$Level[alchemy]}</td>
        <td align="center">- <a href="alchemik.php?alchemik=przepisy&amp;buy={$Id[alchemy]}">{$Abuy}</a></td>
        </tr>
    {/section}
    </table>
{/if}

{if $Alchemist == "pracownia"}
    {if $Make == 0}
        {$Alchemistinfo}
        <table align="center" width="95%">
        <tr>
        <th>{$Rname}</th>
        <th>{$Rlevel}</th>
        <th>{$Rillani}</th>
        <th>{$Rillanias}</th>
        <th>{$Rnutari}</th>
        <th>{$Rdynallca}</th>
        </tr>
        {section name=number loop=$Name}
            <tr>
            <td><a href="alchemik.php?alchemik=pracownia&amp;dalej={$Id[number]}">{$Name[number]}</a></td>
            <td align="center">{$Level[number]}</td>
            <td align="center">{$Illani[number]}</td>
            <td align="center">{$Illanias[number]}</td>
            <td align="center">{$Nutari[number]}</td>
            <td align="center">{$Dynallca[number]}</td>
            </tr>
        {/section}
        </table>
    {/if}
    {if $Next != 0}
        <form method="post" action="alchemik.php?alchemik=pracownia&amp;rob={$Id1}">
        {$Pstart} <input type="submit" value="{$Amake}" /> <b>{$Name1}</b> <input type="text" name="razy" size="5" value="{$Tamount}" /> {$Pamount}. {$Therb}
        </form>
    {/if}
    {if $Make != 0}
        {$Youmake} <b>{$Name}</b> <b>{$Amount}</b> {$Pgain} <b>{$Exp}</b> {$Exp_and} <b>{$Ability}</b> {$Alchemylevel}<br />
	<p>{$Youmade}</p>
	<p>{foreach $Imaked as $value}
	    {$value@key} ({$Ipower}: {$value[0]}) {$Iamount}: {$value[1]}<br />
	{/foreach}</p>
    {/if}
{/if}

{if $Alchemist == "astral"}
    {$Awelcome}<br /><br />
    {$Message}<br /><br />
    {section name=astral loop=$Aviablecom}
        <b>{$Tname}:</b> {$Aviablecom[astral]}<br />
        {section name=astral2 loop=$Mineralsname}
            <b>{$Mineralsname[astral2]}:</b> {$Minamount[astral][astral2]}<br />
        {/section}
        <form method="post" action="alchemik.php?alchemik=astral&amp;potion={$Compnumber[astral]}">
            <br /><input type="submit" value="{$Abuild}" />
        </form>
        <br />
    {/section}
{/if}

{if $Alchemist != ''}
<br /><br /><a href="alchemik.php">({$Back})</a>
{/if}
