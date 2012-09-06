{if $Step == ""}
    {if $Smelterlevel == "0"}
        {$Nosmelter}<a href="smelter.php?step=upgrade">{$Aupgrade}</a>{$Nosmelter2}
    {else}
        {$Smelterinfo}<br />
	<ul>
        <li><a href="smelter.php?step=smelt">{$Asmelt}</a></li>
	<li><a href="smelter.php?step=smelt2">{$Asmelt2}</a></li>
        {if $Smelterlevel < "5"}
            <li><a href="smelter.php?step=upgrade">{$Aupgrade}</a></li>
        {/if}
	</ul>
    {/if}
{/if}

{if $Step == "upgrade"}
    {$Upgradeinfo}<br />
    <form method="post" action="smelter.php?step=upgrade&amp;upgrade">
         {$Levelinfo}<br />
         <input type="submit" value="{$Aupgrade}" />
    </form>
{/if}

{if $Step == "smelt"}
    {$Smeltinfo}<br />
    <ul>
    {section name=smelt1 loop=$Asmelt}
        <li><a href="smelter.php?step=smelt&amp;smelt={$Smeltaction[smelt1]}">{$Asmelt[smelt1]}</a></li>
    {/section}
    </ul>
    {if $Smelt != ""}
        <br />
        <form method="post" action="smelter.php?step=smelt&amp;smelt={$Smelt}">
            <input type="submit" value="{$Asmelt2}" /> <input type="text" name="amount" size="5" value="{$Maxamount}" /> {$Smeltm} {$Youhave}
        </form>
    {/if}
{/if}

{if $Step == "smelt2"}
    {$Smeltinfo}<br /><br />
    <form method="post" action="smelter.php?step=smelt2&amp;smelt">
        <input type="submit" value="{$Asmelt3}" /> <input type="text" name="amount" size="5" /> (<input type="checkbox" name="all" /> {$Tall}) {$Tamount} {html_options name=item options=$Ioptions} {$Tsmelt}
    </form>
{/if}

{if $Step != ""}
    <br /><br />(<a href="smelter.php">{$Aback}</a>)
{/if}
