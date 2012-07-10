{if $Oldchat == 'N'}
        <div>{if $Text1 > $Chatlength}<a href="room.php?more">&#171; {$Amore}</a>{/if} {if $Chatlength > 25} <a href="room.php?less">{$Aless} &#187;</a>{/if}</div>
    {/if}    
{section name=player loop=$Text}
        <div title="{$Sdate[player]}">{if $Showid == "1"}[<a href="room.php?delete={$Tid[player]}" title="Skasuj">X</a>] {/if}{if $Author[player] != ""}{if $Senderid[player] != 0 && $Id != $Senderid[player]}[<a href="javascript:formatText({$Senderid[player]});" title="{$Awhisper}">S</a>] {/if}<b>{$Author[player]} {if $Showid == "1"}{$Cid}:{$Senderid[player]}{/if}</b>:{/if} {$Text[player]}</div>
    {/section}
    {if $Oldchat == 'Y'}
        <div>{if $Text1 > $Chatlength}<a href="room.php?more">&#171; {$Amore}</a>{/if} {if $Chatlength > 25} <a href="room.php?less">{$Aless} &#187;</a>{/if}</div>
    {/if}
<br /><br /><br /><center>{$Player}<br />
{$Thereis} <b>{$Online}</b> {$Cplayers}.<br />
</center>

