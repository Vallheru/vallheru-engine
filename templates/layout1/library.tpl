{if $Step == ""}
    {$Welcome} {$Gamename}. {$Welcome2} {$Gamename}. {$Welcome3} {$Gamename}. {$Welcome4}<br /><br />
    {$Welcome5}<br /><br /> 
    {$Welcome6} {$Atextin} {$Welcome7} {$Atextnot} {$Welcome8}<br /><br />
    <table align="center">
        <tr>
            <td valign="top">
                <a href="library.php?step=tales">{$Atales}</a> ({$Amounttales} {$Tinfo1})<br />
                <a href="library.php?step=poetry">{$Apoetry}</a> ({$Amountpoetry} {$Tinfo2})<br /><br />
                <a href="library.php?step=rules">{$Arules}</a><br />
                <a href="library.php?step=add">{$Aaddtext}</a><br />
                {if $Rank == "Admin" || $Rank == "Bibliotekarz"}
                    <a href="library.php?step=addtext">{$Aadmin}</a>
                {/if}
            </td>
        </tr>
    </table>
{/if}

{if $Step == "add"}
    <script src="js/editor.js"></script>
    {$Addinfo}<br />
    <form method="post" action="library.php?step=add&amp;step2=add" name="add">
        {$Ttype2}: <select name="type">
            {section name=library2 loop=$Ttype}
                <option value="{$Ttype[library2]}">{$Ttype[library2]}</option>
            {/section}
        </select><br />
        {$Ttitle2}: <input type="text" name="ttitle" /><br />
        {$Tbody2}: <br />
	<input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'add', 'body')" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'add', 'body')" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'add', 'body')" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'add', 'body')" />
	<input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'add', 'body')" />
	<input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'add', 'body')" />
	<input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'add', 'body')" /> {html_options name=colors options=$Ocolors}<br />
	<textarea name="body" rows="30" cols="60"></textarea><br />
        <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $Step == "addtext"}
    {$Admininfo}<br />
    {$Admininfo2}<br />
    {$Admininfo3}<br />
    {$Admininfo4}<br /><br />
    {$Admininfo5}:
    <table width="100%">
        {section name=library3 loop=$Ttitle}
            <tr>
                <td>{$Ttitle[library3]} ({$Tauthor2}: {$Tauthor[library3]})</td>
                <td><a href="library.php?step=addtext&amp;action=modify&amp;text={$Tid[library3]}">{$Amodify}</a></td>
                <td><a href="library.php?step=addtext&amp;action=add&amp;text={$Tid[library3]}">{$Aadd}</a></td>
                <td><a href="library.php?step=addtext&amp;action=delete&amp;text={$Tid[library3]}">{$Adelete}</a></td>
            </tr>
        {/section}
    </table>
{/if}

{if $Step == 'tales' || $Step == 'poetry'}
    {if $Tamount == "0" && $Text == ""}
        <br /><br /><center>{$Noitems} {$Ttype} {$Inlib}</center>
    {/if}
    {if $Tamount > "0" && $Text == ""}
        {$Listinfo} {$Ttype} {$Listinfo2}<br />
        {if $Author == ""}
            {$Sortinfo}:
            <form method="post" action="library.php?step={$Step}">
                <select name="sort">
                    <option value="author">{$Oauthor}</option>
                    <option value="id">{$Odate}</option>
                    <option value="title">{$Otitle}</option>
                </select><br />
                <input type="submit" value="{$Asort}" />
            </form>
        {/if}
        {if $Author == "" && ($Sort == "author" || $Sort == "")}
            <ul>
                {section name=library6 loop=$Tauthor}
                    <li><a href="library.php?step={$Step}&amp;author={$Tauthorid[library6]}">{$Tauthor[library6]}</a><br />
                    {$Ttype}: {$Ttexts[library6]}<br /><br /></li>
                {/section}
            </ul>
        {/if}
        {if $Author != "" || ($Sort != "" && $Sort != "author")}
            <ul>
                {section name=library4 loop=$Ttitle}
                    <li><a href="library.php?step={$Step}&amp;text={$Tid[library4]}">{$Ttitle[library4]}</a><br /> ({$Tauthor2}: <a href="view.php?view={$Tauthorid[library4]}">{$Tauthor[library4]}</a>)<br /> (<a href="library.php?step=comments&amp;text={$Tid[library4]}">{$Tcomments}</a>: {$Comments[library4]})<br /><br /></li>
                {/section}
            </ul>
        {/if}
    {/if}
    {if $Text != ""}
        {if $Rank == "Admin" || $Rank == "Bibliotekarz"}
            (<a href="library.php?step=addtext&amp;action=modify&amp;text={$Tid}">{$Amodify}</a>)<br />
        {/if}
        <b>{$Ttitle2}</b>:{$Ttitle}<br />
        <b>{$Tauthor2}</b>:<a href="view.php?view={$Tauthorid}">{$Tauthor}</a><br />
        <b>{$Tbody2}</b>:<br />
        {$Tbody}<br /><br />
        <a href="library.php?step=comments&amp;text={$Text}">{$Tcomments}</a>
    {/if}
{/if}

{if $Step == "comments"}
    {if $Amount == "0"}
        <center>{$Nocomments}</center>
    {/if}
    {if $Amount > "0"}
        {section name=library5 loop=$Tauthor}
            <b><a href="view.php?view={$Taid[library5]}">{$Tauthor[library5]}</a></b> {if $Tdate[library5] != ""} ({$Tdate[library5]}) {/if}{$Writed}: {if $Rank == "Admin" || $Rank == "Bibliotekarz"} (<a href="library.php?step=comments&amp;action=delete&amp;cid={$Cid[library5]}">{$Adelete}</a>) {/if}<br />
            {$Tbody[library5]}<br /><br />
        {/section}
    {/if}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="library.php?step=comments&text={$Text}&page={$page}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
    <br /><br /><center>
        {include file="comments.tpl"}
    </center>
{/if}

{if $Step == "rules"}
    {$Rules}
{/if}

{if $Action == "modify"}
    <script src="js/editor.js"></script>
    <form method="post" action="library.php?step=addtext&amp;action=modify&amp;text={$Tid}" name="edit">
    {$Ttype2}: <select name="type">
        <option value="{$Ttypet}" {if $Ttype == "tale"} selected="true" {/if}>{$Ttypet}</option>
        <option value="{$Ttypep}" {if $Ttype == "poetry"} selected="true" {/if}>{$Ttypep}</option>
    </select>
    <input type="hidden" name="tid" value="{$Tid}" /><br />
    {$Ttitle2}: <input type="text" name="ttitle" value="{$Ttitle}" /><br />
    {$Tbody2}: <br />
    <input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'edit', 'body')" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'edit', 'body')" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'edit', 'body')" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'edit', 'body')" />
	<input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'edit', 'body')" />
	<input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'edit', 'body')" />
	<input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'edit', 'body')" /> {html_options name=colors options=$Ocolors}<br />
    <textarea name="body" rows="30" cols="60">{$Tbody}</textarea><br />
    <input type="hidden" name="tid" value="{$Tid}" />
    <input type="submit" value="{$Achange}" />
</form>
{/if}

{if $Step != ""}
    <br /><br /><a href="library.php">{$Aback}</a>
{/if}
