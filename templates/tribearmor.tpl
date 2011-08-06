{include file="tribemenu.tpl"}

{if $Step == "" && $Step2 == "" && $Give == "" && $Step3 == ""}
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
    <table>
    <tr>
    {section name=armortable loop=$Ttypes}
        <td width="100"><a href="tribearmor.php?step=zobacz&amp;lista={$Ttypes[armortable]}&amp;limit=0&amp;type={$Type}"><b><u>{$Tinfos[armortable]}</u></b></a></td>
    {/section}
    <td width="50"><b><u>{$Tamount2}</u></b></td>
    <td width="100"><b><u>{$Toptions}</u></b></td>
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
        <td align="center">{$Amount[tribearmor]}</td>
        {$Action[tribearmor]}
    {/section}
    </table>
{/if}

{if $Give != ""}
    {if $Step3 == ""}
        <form method="post" action="tribearmor.php?daj={$Id}&amp;step3=add">
        <input type="submit" value="{$Agive}" />
        <input type="text" name="amount" value="{$Amount}" size=5 /> {$Tamount} <b>{$Name}</b> {$Playerid}:
        <input type="text" name="did" size="5" /><br />
        </form>
    {/if}
{/if}

{if $Step == "daj"}
    {$Additem}<br /><br />
    <form method="post" action="tribearmor.php?step=daj&amp;step2=add">
    <table><tr><td>
    {$Item}: <select name="przedmiot">
    {section name=tribearmor1 loop=$Name}
        <option value="{$Itemid[tribearmor1]}">({$Amount2}: {$Amount[tribearmor1]}) {$Name[tribearmor1]}</option>
    {/section}
    </select> sztuk <input type="text" name="amount" size="5" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}

{$Message}
