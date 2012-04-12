{include file="tribemenu.tpl"}

{if $Step == "" && $Step2 == "" && $Step3 == "" && $Give == "" && $Reserve == ""}
    {$Wareinfo}<br />
    <ul>
    <li><a href="tribeware.php?step=zobacz&amp;lista=id">{$Ashow}</a></li>
    <li><a href="tribeware.php?step=daj">{$Aadd}</a></li></ul>
{/if}

{if $Step == "zobacz"}
    {$Inware} {$Amount1} {$Potions}<br />
    <table width="95%">
    <tr>
    <th><a href="tribeware.php?step=zobacz&amp;lista=name">{$Tname}</a></th>
    <th><a href="tribeware.php?step=zobacz&amp;lista=efect">{$Tefect}</a></th>
    <th>{$Tamount2}</th>
    <th>{$Toptions}</th>
    </tr>
    {section name=tribeware loop=$Name}
        <tr>
        <td>{$Name[tribeware]}</td>
        <td>{$Efect[tribeware]}</td>
        <td>{$Amount[tribeware]}</td>
        {$Link[tribeware]}
    {/section}
    </table>
{/if}

{if $Give != ""}
    {if $Step3 == ""}
        <form method="post" action="tribeware.php?daj={$Id}&amp;step3=add">
        <input type="submit" value="{$Agive}" /> <input type="text" name="amount" value="{$Amount}" size="5" /> {$Tamount}<b>{$Name}</b> {$Playerid}: {html_options name=did options=$Members}<br /></form>
    {/if}
{/if}

{if $Step == "daj"}
    {$Additem}<br /><br />
    <form method="post" action="tribeware.php?step=daj&amp;step2=add"><table>
    <tr><td>{$Potion}: {html_options name=przedmiot options=$Potions} {$Tamount2} <input type="text" name="amount" size="5" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
    </table></form>
{/if}

{if $Reserve != ""}
    {if $Step3 == ""}
        <form method="post" action="tribeware.php?reserve={$Reserve}&amp;step3=add">
        <input type="submit" value="{$Aask}" />
        <input type="text" name="amount" value="{$Amount}" size=5 /> {$Tamount} <b>{$Name}</b><br />
        </form>
    {/if}
{/if}
