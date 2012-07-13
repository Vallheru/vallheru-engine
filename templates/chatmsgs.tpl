{if $Text1 != 0}
    <div>
        <form method="post" action="chat.php?tabs">
	    {foreach $Tabs as $key=>$name}
	        <input type="submit" value="{$name}" name="{$key}">
	    {/foreach}
	    {if $Chattab != 0}
	        [<a href="chat.php?close">{$Aclose}</a>]
	    {/if}
	</form>
    </div>
    {if $Oldchat == 'N'}
        <div>{if $Text1 > $Chatlength}<a href="chat.php?more">&#171; {$Amore}</a>{/if} {if $Chatlength > 25} <a href="chat.php?less">{$Aless} &#187;</a>{/if}</div>
    {/if}
    {section name=player loop=$Text}
	     <div title="{$Sdate[player]}" style="margin: 3px;">{if $Showid == "1"}[<a href="chat.php?step=delete&amp;tid={$Tid[player]}" title="Skasuj">X</a>] {/if}{if $Author[player] != ""}{if $Senderid[player] != 0 && $Id != $Senderid[player]}[<a href="javascript:formatText({$Senderid[player]});" title="{$Awhisper}">S</a>] {/if}<b>{$Author[player]}{if $Showid == "1"} {$Cid}:{$Senderid[player]}{/if}</b>:{/if} {$Text[player]}</div>
	</tr>
    {/section}
    {if $Oldchat == 'Y'}
        <div>{if $Text1 > $Chatlength}<a href="chat.php?more">&#171; {$Amore}</a>{/if} {if $Chatlength > 25} <a href="chat.php?less">{$Aless} &#187;</a>{/if}</div>
    {/if}
    </table>
{/if}
<br /><center>{$Player}<br />
{$Thereis} <b>{$Text1}</b> {$Texts}. | <b>{$Online}</b> {$Cplayers}.<br />
</center><br /><br />

