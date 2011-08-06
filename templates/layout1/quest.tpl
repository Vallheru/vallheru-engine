{$Start}

{$Text}

{$Link}

{$End}

{if $Box != ""}
    <form method="post" action="{$File}?step=quest">
    {section name=quest loop=$Box}
        <input type="radio" name="{$Name}" value="{$Option[quest]}" />{$Box[quest]}<br /><br />
    {/section}
    <input type="submit" value="{$Aselect}" /></form>
{/if}

{if $Answer == "Y"}
    <form method="post" action="{$File}?step=quest">
    {$Addanswer}:<br />
    <input type="text" name="planswer" /><br />
    <input type="submit" value="{$Anext}" /></form>
{/if}
