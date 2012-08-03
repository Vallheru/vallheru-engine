<script src="js/editor.js"></script>

{if $View == "" && $Read == "" && $Send == "" && $Step == ""}
    {$Mailinfo}<br /><br />
    - <a href="mail.php?view=inbox">{$Ainbox}</a><br />
    - <a href="mail.php?view=saved">{$Asaved}</a><br />
    - <a href="mail.php?view=write">{$Awrite}</a><br />
    - <a href="mail.php?view=search">{$Asearch}</a>
{/if}

{if $View == "search"}
    <form method="post" action="mail.php?view=search&amp;step">
        <input type="submit" value="{$Asearch}" /> <input type="text" name="search" />
    </form>
    {if $Amount > 0}
        <ul>
        {section name=search loop=$Senders}
	    <li>{$Senders[search]} <a href="mail.php?read={$Mailid[search]}&amp;one">{$Subjects[search]}</a></li>
	{/section}
	</ul>
	{if $Tpages > 1}
    	    <br />{$Fpage}
    	    {for $page = 1 to $Tpages}
	        {if $page == $Tpage}
	            {$page}
	        {else}
                    <a href="mail.php?view=search&page={$page}">{$page}</a>
	        {/if}
    	    {/for}
    	{/if}
    {/if}
{/if}

{if $View == "inbox"}
    <form method="post" action="mail.php?view=inbox&amp;sort">
        <input type="submit" value="{$Asort}" /> {$Tsender}: {html_options name=sort1 options=$Senders} {$Ttime}: <select name="sort2">
	    <option value="-1">{$Sall2}</option>
	    <option value="7">{$Tlastweek}</option>
	    <option value="30">{$Tlastmonth}</option>
	    <option value="31">{$Toldest}</option>
	</select>
    </form>
    <form method="post" action="mail.php?step=mail&amp;box=I">
    <table align="center" width="90%">
    <tr>
    <th width="20">{$Tselect}</th>
    <th>{$From}</th>
    <th width="60%">{$Mtitle}</th>
    </tr>
    {section name=mail loop=$Sender}
        <tr>
        <td><input type="checkbox" name="{$Mailid[mail]}" /></td>
        <td><a href="view.php?view={$Senderid[mail]}">{$Sender[mail]}</a></td>
        <td><a href="mail.php?read={$Mtopic[mail]}">{$Subject[mail]}</a>{if $Mread[mail] == "Y"} <blink>!!</blink> {/if}</td>
        </tr>
    {/section}
    </table><br />
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="mail.php?view=inbox&page={$page}{$Lpage}">{$page}</a>
	    {/if}
    	{/for}
    {/if}<br /><br />
    <input type="submit" value="{$Adeletes}" name="delete" /> <input type="submit" name="read2" value="{$Aread2}" /> <input type="submit" name="unread" value="{$Aunread}" /><br />
    </form>
    <form method="post" action="mail.php?step=deleteold&amp;box=I">
        <input type="submit" value="{$Adeleteold}" /> <select name="oldtime">
            <option value="7">{$Aweek}</option>
            <option value="14">{$A2week}</option>
            <option value="30">{$Amonth}</option>
        </select>
    </form>
    [<a href="mail.php?view=saved">{$Asaved}</a>]
    [<a href="mail.php?view=inbox&amp;step=clear">{$Aclear}</a>]
    [<a href="mail.php?view=write">{$Awrite}</a>]
{/if}

{if $View == "saved"}
    <form method="post" action="mail.php?view=saved&amp;sort">
        <input type="submit" value="{$Asort}" /> {$Tsender}: {html_options name=sort1 options=$Senders} {$Ttime}: <select name="sort2">
	    <option value="-1">{$Sall2}</option>
	    <option value="7">{$Tlastweek}</option>
	    <option value="30">{$Tlastmonth}</option>
	    <option value="31">{$Toldest}</option>
	</select>
    </form>
    <form method="post" action="mail.php?step=mail&amp;box=W">
    <table align="center" width="90%">
    <tr>
    <th width="20">{$Tselect}</th>
    <th>{$From}</th>
    <th width="60%">{$Mtitle}</th>
    </tr>
    {section name=mail1 loop=$Sender}
        <tr>
        <td><input type="checkbox" name="{$Mailid[mail1]}" /></td>
        <td><a href="view.php?view={$Senderid[mail1]}">{$Sender[mail1]}</a></td>
        <td><a href="mail.php?read={$Mailid[mail1]}&amp;one">{$Subject[mail1]}</a></td>
        </tr>
    {/section}
    </table><br />
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="mail.php?view=zapis&page={$page}{$Lpage}">{$page}</a>
	    {/if}
    	{/for}
    {/if}<br /><br />
    <input type="submit" value="{$Adeletes}" name="delete" /><br />
    </form>
    <form method="post" action="mail.php?step=deleteold&amp;box=W">
        <input type="submit" value="{$Adeleteold}" /> <select name="oldtime">
            <option value="7">{$Aweek}</option>
            <option value="14">{$A2week}</option>
            <option value="30">{$Amonth}</option>
        </select>
    </form>
    [<a href="mail.php?view=inbox">{$Ainbox}</a>]
    [<a href="mail.php?view=zapis&amp;step=clear">{$Aclear}</a>]
    [<a href="mail.php?view=write">{$Awrite}</a>]
{/if}

{if $View == "write"}
    [<a href="mail.php?view=inbox">{$Ainbox}</a>]<br /><br />
    <form method="post" action="mail.php?view=write&amp;step=send" name="newmail">
    <table>
    <tr><td>{$Sto} {html_options name=player options=$Contacts}:</td><td><input type="text" name="to" value="{$To}" /></td></tr>
    <tr><td>{$Mtitle}:</td><td><input type="text" name="subject" size="55" value="{$Subject}" /></td></tr>
    <tr><td colspan="2"><input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'newmail', 'body')" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'newmail', 'body')" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'newmail', 'body')" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'newmail', 'body')" />
	<input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'newmail', 'body')" />
	<input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'newmail', 'body')" />
	<input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'newmail', 'body')" /> {html_options name=colors options=$Ocolors}</td></tr>
    <tr><td valign="top">{$Mbody}:</td><td><textarea name="body" rows="13" cols="55">{$Body}</textarea></td></tr>
    <tr><td align="center" colspan="2"><input type="submit" value="{$Asend}" /></td></tr>
    </table></form><br />
    {$Mhelp}
{/if}

{if $Read != ""}
    <b>{$Subject}</b><br /><br />
    {section name=read loop=$Sendersid}
        {$Date2[read]} <a href="view.php?view={$Sendersid[read]}">{$Senders[read]}</a> {$Twrite}... <br />
	"{$Bodies[read]}"<br />
	{if $One == 0}
	    [<a href="mail.php?zapisz={$Mailsid[read]}">{$Asave}</a>]
        {/if}
    	[<a href="mail.php?kasuj={$Mailsid[read]}">{$Adelete}</a>]
	{if $Sendersid[read] != $Id}
	    [<a href="mail.php?send={$Mailsid[read]}">{$Asend2}</a>]
    	    [<a href="mail.php?block={$Sendersid[read]}&amp;read={$Read}">{$Ablock}</a>]
	{/if}
	<br /><br />
    {/section}
    {if $One == 0}
        {if $Tpages > 1}
    	    <br />{$Fpage}
    	    {for $page = 1 to $Tpages}
	        {if $page == $Tpage}
	            {$page}
	        {else}
                    <a href="mail.php?read={$Read}&page={$page}">{$page}</a>
	        {/if}
    	    {/for}
        {/if}
        <form method="post" action="mail.php?view=write&amp;step=send" name="reply">
	    <input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'reply', 'body')" />
	    <input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'reply', 'body')" />
	    <input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'reply', 'body')" />
	    <input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'reply', 'body')" />
	    <input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'reply', 'body')" />
	    <input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'reply', 'body')" />
	    <input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'reply', 'body')" /> {html_options name=colors options=$Ocolors}<br />
            <textarea name="body" rows="13" cols="55">{$Body}</textarea><br />
            <input type="hidden" name="topic" value="{$Mtopic}" />
	    <input type="hidden" name="player" value="{$Receiver}" />
	    <input type="hidden" name="subject" value="{$Subject}" />
	    <input type="submit" value="{$Asend}" />
        </form><br />
        {$Mhelp}<br /><br />
    {/if}
    [<a href="mail.php">{$Amail}</a>]
    [<a href="mail.php?view=inbox">{$Ainbox}</a>]
    [<a href="mail.php?view=saved">{$Asaved}</a>]
    [<a href="mail.php?view=write">{$Awrite}</a>]
{/if}

{if $Send != ""}
    <form method="post" action="mail.php?send&amp;step=send">
    {$Sendthis}: <select name="staff">
    {section name=mail3 loop=$Name}
        <option value="{$Staffid[mail3]}">{$Name[mail3]}</option>
    {/section}
    </select><br />
    <input type="hidden" name="mid" value="{$Send}" />
    <input type="submit" value="{$Asend}" /></form>
{/if}
