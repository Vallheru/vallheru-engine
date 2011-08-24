{if $Action == ""}
   {$Landinfo} <b>{$Gold}</b> {$Landinfo2}<br /><br />
    <form method="post" action="landfill.php?action=work">
    <input type="submit" value="{$Awork}" /> <input type="text" name="amount" value="{$Energy}" size="5" /> {$Times}</form>
{/if}
{if $Action == "work"}
    {$Inwork} <b>{$Amount}</b> {$Inwork2} <b>{$Gain}</b> {$Goldcoins}
    <br />[<a href="landfill.php">{$Aback}</a>]
{/if}
