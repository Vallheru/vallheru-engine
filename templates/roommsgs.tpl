    {section name=player loop=$Text}
        <div title="{$Sdate[player]}">{if $Showid == "1"}<a href="room.php?delete={$Tid[player]}">x</a> {/if}{if $Author[player] != ""}<b>{$Author[player]} {if $Showid == "1"}{$Cid}:{$Senderid[player]}{/if}</b>:{/if} {$Text[player]}</div>
    {/section}
<br /><br /><br /><center>{$Player}<br />
{$Thereis} <b>{$Online}</b> {$Cplayers}.<br />
</center>

