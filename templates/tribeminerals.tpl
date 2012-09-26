{include file="tribemenu.tpl"}

{if $Give == "" && $Reserve == ""}
    {$Mininfo}<br />
    <table align="center" width="70%">
        {section name=tribemin loop=$Ttable}
            {$Ttable[tribemin]}
        {/section}
    </table><br /><br />
    <script src="js/tribearmor.js"></script>
    <div>
    <label for="weapons" class="toggle">+{$Agiveto}</label>
    <input id="weapons" type="checkbox" class="toggle" {$Checked} />
    <div>
    	<form method="post" action="tribeminerals.php?step3=add"><table>
            <tr><td>{$Mineral}:</td>
	        <td>{html_options name=mineral options=$Minname}</td></tr>
            <tr><td>{$Mamount}:</td><td><span id="amount"><input type="text" name="ilosc" /></span> (<input type="checkbox" name="all" id="all" onClick="showField();" />{$Tall}</td></tr>
            <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
        </table></form>
    </div>
{/if}
{if $Give != ""}
    <form method="post" action="tribeminerals.php?daj={$Itemid}&amp;step4=add">
        {$Giveplayer} {html_options name=did options=$Members}<br />
        <input type="text" name="ilosc" size="7" /> {$Namemin} {$Tamount3} {$Tamount2} {$Mamount2}.<br />
        <input type="submit" value="{$Agive}" /><br />
    </form>
{/if}
{if $Reserve != ""}
    {if $Step3 == ""}
        <form method="post" action="tribeminerals.php?reserve={$Reserve}&amp;step3=add">
        <input type="submit" value="{$Aask}" />
        <input type="text" name="amount" value="{$Amount}" size=5 /> {$Tamount2} <b>{$Name}</b><br />
        </form>
    {/if}
{/if}
