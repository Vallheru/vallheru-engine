{if $Step == ""}
    {if $Smelterlevel == "0"}
        {$Nosmelter}<a href="smelter.php?step=upgrade">{$Aupgrade}</a>{$Nosmelter2}
    {/if}
    {if $Smelterlevel > "0"}
        {$Smelterinfo}<br />
        - <a href="smelter.php?step=smelt">{$Asmelt}</a><br />
        {if $Smelterlevel < "5"}
            - <a href="smelter.php?step=upgrade">{$Aupgrade}</a><br />
        {/if}
    {/if}
{/if}

{if $Step == "upgrade"}
    {if $Upgrade == ""}
        {if $Smelterlevel < "5"}
            {$Upgradeinfo}<br />
            <form method="post" action="smelter.php?step=upgrade&amp;upgrade=Y">
                {$Levelinfo}<br />
                <input type="submit" value="{$Aupgrade}" />
            </form>
        {/if}
        {if $Smelterlevel == "5"}
            {$Levelinfo}<br />
        {/if}
    {/if}
    {if $Upgrade == "Y"}
        {$Message}
    {/if}
{/if}

{if $Step == "smelt"}
    {$Smeltinfo}<br />
    {section name=smelt1 loop=$Asmelt}
        - <a href="smelter.php?step=smelt&amp;smelt={$Smeltaction[smelt1]}">{$Asmelt[smelt1]}</a><br />
    {/section}
    {if $Smelt != ""}
        <br />
        <form method="post" action="smelter.php?step=smelt&amp;smelt={$Smelt}">
            <input type="submit" value="{$Asmelt2}" /> <input type="text" name="amount" size="5" value="{$Maxamount}" /> {$Smeltm} {$Youhave}
        </form>
    {/if}
    <br /><br />{$Message}
{/if}

{if $Step != ""}
    <br /><br />(<a href="smelter.php">{$Aback}</a>)
{/if}
