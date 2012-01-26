{include file="tribemenu.tpl"}

{if $Step2 == "" && $Step3 == "" && $Step4 == "" && $Give == ""}
    {$Herbsinfo}<br />
    <table>
        <tr>
            {section name=herbstribe2 loop=$Ttable}
                {$Ttable[herbstribe2]}
            {/section}
        </tr>
        <tr>
            {section name=herbstribe3 loop=$Tamount}
                <td align="center">{$Tamount[herbstribe3]}</td>
            {/section}
        </tr>
    </table>
    {$Whatyou}<br />
    <ul>
        <li><a href="tribeherbs.php?step2=daj">{$Agiveto}</a></li>
    </ul>
{/if}
{if $Give != ""}
    {if $Step4 == ""}
        <form method=post action="tribeherbs.php?daj={$Itemid}&amp;step4=add">
            {$Giveplayer} <input type="text" name="did" size="5" /><br />
            <input type="text" name="ilosc" size="5" /> {$Nameherb} {$Tamount} {$Tamount2} {$Hamount2}.<br />
            <input type="hidden" name="min" value="{$Nameherb}" />
            <input type="submit" value="{$Agive}" /><br />
        </form>
    {/if}
{/if}
{if $Step2 == "daj"}
    {$Addherb}<br /><br />
    <form method="post" action="tribeherbs.php?step2=daj&amp;step3=add"><table>
    <tr><td>{$Herb}:</td>
        <td>{html_options name=mineral options=$Hoptions}</td>
    </tr>
    <tr><td>{$Hamount}:</td><td><input type="text" name="ilosc" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}
