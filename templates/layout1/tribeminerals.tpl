{include file="tribemenu.tpl"}

{if $Step2 == "" && $Step3 == "" && $Step4 == "" && $Give == ""}
    {$Mininfo}<br />
    <table>
        {section name=tribemin loop=$Ttable}
            {$Ttable[tribemin]}
        {/section}
    </table>
    {$Whatyou}<br />
    <ul>
        <li><a href="tribeminerals.php?step=skarbiec&amp;step2=daj">{$Agiveto}</a></li>
    </ul>
{/if}
{if $Give != ""}
    <form method="post" action="tribeminerals.php?step=skarbiec&amp;daj={$Itemid}&amp;step4=add">
        {$Giveplayer} <input type="text" name="did" size="5" /><br />
        <input type="text" name="ilosc" size="7" /> {$Namemin} {$Tamount} {$Tamount2} {$Mamount2}.<br />
        <input type="hidden" name="min" value="{$Namemin}" />
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
