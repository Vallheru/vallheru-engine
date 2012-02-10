{if $Text1 != 0}
    {section name=player loop=$Text}
        <div title="{$Sdate[player]}">{if $Author[player] != ""}<b>{$Author[player]} {if $Showid == "1"}{$Cid}:{$Senderid[player]}{/if}</b>:{/if} {$Text[player]}</div>
    {/section}
{/if}
<br /><br /><br /><center>{$Player}<br />
{$Thereis} <b>{$Text1}</b> {$Texts}. | <b>{$Online}</b> {$Cplayers}.<br />
</center>

