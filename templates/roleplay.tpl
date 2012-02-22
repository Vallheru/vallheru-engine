<center><b>{$Pname}</b></center><br /><br /><br />{$Roleplay}<br /><br /><br />
{$Info}:<br />
{$OOC}<br /><br />
{if $Awrite != ""}
    <a href="mail.php?view=write&amp;to={$Vid}">{$Awrite}</a><br /><br />
{/if}
<table width="100%">
    <tr>
        <td align="left"><a href="roleplay.php?view={$Previous}">{$Aprevious}</a></td>
        <td align="right"><a href="roleplay.php?view={$Next}">{$Anext}</a></td>
    </tr>
</table>
