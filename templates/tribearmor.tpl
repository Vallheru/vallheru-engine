{include file="tribemenu.tpl"}

{if $Step == "" && $Step2 == "" && $Give == "" && $Reserve == ""}
    {$Armorinfo}<br />
    <ul>
    {section name=armorlinks loop=$Armortype}
        <li><a href="tribearmor.php?step=zobacz&amp;lista=id&amp;type={$Armortype[armorlinks]}">{$Armorlink[armorlinks]}</a></li>
    {/section}
    <li><a href="tribearmor.php?step=daj">{$Aadd}</a></li>
    </ul>
{/if}

{if $Step == "zobacz"}
    {$Inarmor2} {$Amount1} {$Name1}<br />
    <form method="post" action="tribearmor.php?step=zobacz&amp;lista=id&amp;type={$Type}&amp;levels=yes">
        {$Tor} <input type="submit" value="{$Tseek}" /> {$Titems} <input type="text" name="min" value="1" size="5" />  {$Tto} <input type="text" name="max" value="1" size="5" />
    </form>
    <table width="100%" align="center">
    <tr>
    {section name=armortable loop=$Ttypes}
        <th><a href="tribearmor.php?step=zobacz&amp;lista={$Ttypes[armortable]}&amp;limit=0&amp;type={$Type}">{$Tinfos[armortable]}</a></th>
    {/section}
    <th>{$Tamount2}</th>
    <th>{$Toptions}</th>
    </tr>
    {section name=tribearmor loop=$Name}
        <tr>
        <td>{$Name[tribearmor]}</td>
        <td align="center">{$Power[tribearmor]}</td>
        {if $Type != "I"}
            <td align="center">{$Durability[tribearmor]}/{$Maxdurability[tribearmor]}</td>
            <td align="center">{$Agility[tribearmor]}</td>
            <td align="center">{$Speed[tribearmor]}</td>
        {/if}
        <td align="center">{$Ilevel[tribearmor]}</td>
        <td align="center">{$Amount[tribearmor]} / {$Reserved[tribearmor]}</td>
        {$Action[tribearmor]}
    {/section}
    </table>
{/if}

{if $Give != ""}
    {if $Step3 == ""}
        <form method="post" action="tribearmor.php?daj={$Give}&amp;step3=add">
        <input type="submit" value="{$Agive}" />
        <input type="text" name="amount" value="{$Amount}" size=5 /> {$Tamount} <b>{$Name}</b> {$Playerid}:
	{html_options name=did options=$Members}<br />
        </form>
    {/if}
{/if}

{if $Step == "daj"}
    {$Additem}<br /><br />
    <form method="post" action="tribearmor.php?step=daj&amp;step2=add">
    <table><tr><td>
    {$Item}: <select name="przedmiot">
    {section name=tribearmor1 loop=$Name}
        <option value="{$Itemid[tribearmor1]}">{$Name[tribearmor1]} (+{$Ipower[tribearmor1]})
	{if $Ispeed[tribearmor1] > 0}
	    (+{$Ispeed[tribearmor1]} {$Ispd})
	{/if}
	{if $Iagi[tribearmor1] != 0}
	    ({$Iagi[tribearmor1]}% {$Iag})
	{/if}
	{if $Imaxdur[tribearmor1] > 1}
	    ({$Idur[tribearmor1]}/{$Imaxdur[tribearmor1]})
	{/if}
	({$Amount2}: {$Amount[tribearmor1]})</option>
    {/section}
    </select> sztuk <input type="text" name="amount" size="5" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}

{if $Reserve != ""}
    {if $Step3 == ""}
        <form method="post" action="tribearmor.php?reserve={$Reserve}&amp;step3=add">
        <input type="submit" value="{$Aask}" />
        <input type="text" name="amount" value="{$Amount}" size=5 /> {$Tamount} <b>{$Name}</b><br />
        </form>
    {/if}
{/if}
