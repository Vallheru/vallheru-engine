{$Text}
{if $Afinish == ""}
    <form method="post" action="mission.php">
        {html_radios name=action options=$Moptions separator='<br />'}<br />
        <input type="submit" value="{$Anext}" />
    </form>
{else}
    <br /><br />{$Afinish}
{/if}
