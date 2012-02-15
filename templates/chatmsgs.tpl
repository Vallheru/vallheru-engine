{if $Text1 != 0}
    <table id="tchat">
    <th></th>
    <th></th>
    {section name=player loop=$Text}
	{if $smarty.section.player.iteration is div by 2}
	    <tr class="alt">
	{else}
	    <tr>
	{/if}
	        <td>{if $Showid == "1"}<a href="chat.php?step=delete&amp;tid={$Tid[player]}">X</a> {/if}{if $Author[player] != ""}<b>{$Author[player]} {if $Showid == "1"}{$Cid}:{$Senderid[player]}{/if}</b><br />{if $Senderid[player] != 0 && $Id != $Senderid[player]}<a href="view.php?view={$Senderid[player]}">{$Aprofile}</a> <a href="javascript:formatText({$Senderid[player]});">{$Awhisper}</a><br />{/if}{$Sdate[player]}{/if}</td>
	        <td width="80%">{$Text[player]}</td>
	    </tr>
    {/section}
    </table>
{/if}
<br /><center>{$Player}<br />
{$Thereis} <b>{$Text1}</b> {$Texts}. | <b>{$Online}</b> {$Cplayers}.<br />
</center><br /><br />

