{if $Text1 != 0}
    {section name=player loop=$Text}
	     <div title="{$Sdate[player]}" style="margin: 3px;">{if $Showid == "1"}[<a href="chat.php?step=delete&amp;tid={$Tid[player]}" title="Skasuj">X</a>] {/if}{if $Author[player] != ""}{if $Senderid[player] != 0 && $Id != $Senderid[player]}[<a href="javascript:formatText({$Senderid[player]});" title="{$Awhisper}">S</a>] {/if}<b>{$Author[player]}{if $Showid == "1"} {$Cid}:{$Senderid[player]}{/if}</b>:{/if} {$Text[player]}</div>
	</tr>
    {/section}
    </table>
{/if}
<br /><center>{$Player}<br />
{$Thereis} <b>{$Text1}</b> {$Texts}. | <b>{$Online}</b> {$Cplayers}.<br />
</center><br /><br />

