{include file="tribemenu.tpl"}

{if $Step2 == "" && $Step3 == "" && $Step4 == "" && $Give == "" && $Reserve == ""}
    {$Mininfo}<br />
    <table>
        {section name=tribemin loop=$Ttable}
            {$Ttable[tribemin]}
        {/section}
    </table>
    {$Whatyou}<br />
    <ul>
        <li><a href="tribeminerals.php?step2=daj">{$Agiveto}</a></li>
    </ul>
{/if}
{if $Give != ""}
    <form method="post" action="tribeminerals.php?daj={$Itemid}&amp;step4=add">
        {$Giveplayer} {html_options name=did options=$Members}<br />
        <input type="text" name="ilosc" size="7" /> {$Namemin} {$Tamount3} {$Tamount2} {$Mamount2}.<br />
        <input type="submit" value="{$Agive}" /><br />
    </form>
{/if}
{if $Step2 == "daj"}
    {$Addmin}<br /><br />
    <form method="post" action="tribeminerals.php?step2=daj&amp;step3=add"><table>
        <tr><td>{$Mineral}:</td>
	    <td>{html_options name=mineral options=$Minname}</td></tr>
        <tr><td>{$Mamount}:</td><td><input type="text" name="ilosc" /></td></tr>
        <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}
{if $Reserve != ""}
    {if $Step3 == ""}
        <form method="post" action="tribeminerals.php?reserve={$Reserve}&amp;step3=add">
        <input type="submit" value="{$Aask}" />
        <input type="text" name="amount" value="{$Amount}" size=5 /> {$Tamount2} <b>{$Name}</b><br />
        </form>
    {/if}
{/if}
