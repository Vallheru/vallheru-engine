{include file="tribemenu.tpl"}

{if $Step == "" && $Step2 == "" && $Step3 == "" && $Give == ""}
    {$Wareinfo}<br />
    <ul>
    <li><a href="tribeware.php?step=zobacz&amp;lista=id">{$Ashow}</a></li>
    <li><a href="tribeware.php?step=daj">{$Aadd}</a></li></ul>
{/if}

{if $Step == "zobacz"}
    {$Inware} {$Amount1} {$Potions}<br />
    <table class="dark">
    <tr>
    <td width="100"><a href="tribeware.php?step=zobacz&amp;lista=name"><b><u>{$Tname}</u></b></a></td>
    <td width="100"><a href="tribeware.php?step=zobacz&amp;lista=efect"><b><u>{$Tefect}</u></b></a></td>
    <td width="50"><b><u>{$Tamount2}</u></b></td>
    <td width="100"><b><u>{$Toptions}</u></b></td>
    </tr>
    {section name=tribeware loop=$Name}
        <tr>
        <td>{$Name[tribeware]}</td>
        <td align="center">{$Efect[tribeware]}</td>
        <td align="center">{$Amount[tribeware]}</td>
        {$Link[tribeware]}
    {/section}
    </table>
{/if}

{if $Give != ""}
    {if $Step3 == ""}
        <form method="post" action="tribeware.php?daj={$Id}&amp;step3=add">
        <input type="submit" value="{$Agive}" /> <input type="text" name="amount" value="{$Amount}" size="5" /> {$Tamount}<b>{$Name}</b> {$Playerid}: <input type="text" name="did" size="5" /><br /></form>
    {/if}
    {$Message}
{/if}

{if $Step == "daj"}
    {$Additem}<br /><br />
    <form method="post" action="tribeware.php?step=daj&amp;step2=add"><table class="dark">
    <tr><td>{$Potion}: <select name="przedmiot">
    {section name=tribeware1 loop=$Name}
        <option value="{$Itemid[tribeware1]}">({$Amount2}: {$Amount[tribeware1]}) {$Name[tribeware1]}</option>
    {/section}
    </select> {$Tamount2} <input type="text" name="amount" size="5" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
    {$Message}
{/if}
