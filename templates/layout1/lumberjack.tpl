{if $Action == ""}
    {if $Health > "0"}
        {$Youwant}<br /><br />
        <form method="post" action="lumberjack.php?action=chop">
            <input type="submit" value="{$Achop}" /> {$Onchop} {html_options name=type options=$Loptions} <input type="text" name="amount" size="5" value="{$Curen}"> {$Tenergy}
        </form>
    {/if}
    <a href="las.php">{$Aback}</a><br />
{/if}

{if $Action == "chop"}
    {$Message2}<br /><br />
    {if $Health > "0"}
        {if $Curen >= 0}
            <form method="post" action="lumberjack.php?action=chop">
                <input type="submit" value="{$Achop}" /> {$Onchop} {html_options name=type options=$Loptions selected=$Lselected} <input type="text" name="amount" size="5" value="{$Curen}"> {$Tenergy}
            </form>
	{/if}
        <a href="lumberjack.php">{$Aback}</a><br />
    {else}
        <a href="las.php">{$Aback}</a><br />
    {/if}
{/if}
