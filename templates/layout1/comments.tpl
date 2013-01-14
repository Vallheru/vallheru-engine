<script src="js/editor.js"></script>
<form method="post" action="{$Faction}" name="comment">
    {if $Abold != ""}
        <input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'comment', 'body')" />
        <input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'comment', 'body')" />
        <input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'comment', 'body')" />
        <input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'comment', 'body')" />
        <input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'comment', 'body')" />
        <input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'comment', 'body')" />
        <input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'comment', 'body')" /> {html_options name=colors options=$Ocolors}<br />
    {/if}
    {$Addcomment}:<textarea name="body" rows="20" cols="50"></textarea><br />
    <input type="submit" value="{$Aadd}" />
</form>
