{if $View == "" && $Read == "" && $Write == "" && $Delete == "" && $Send == "" && $Step == "" && $Block == ""}
    {$Mailinfo}<br /><br />
    - <a href="mail.php?view=inbox">{$Ainbox}</a><br />
    - <a href="mail.php?view=saved">{$Asaved}</a><br />
    - <a href="mail.php?view=write">{$Awrite}</a><br />
    - <a href="mail.php?view=blocks">{$Ablocked}</a><br />
    - <a href="mail.php?view=search">{$Asearch}</a>
{/if}

{if $View == "search"}
    <form method="post" action="mail.php?view=search&amp;step">
        <input type="submit" value="{$Asearch}" /> <input type="text" name="search" />
    </form>
    {if $Amount > 0}
        <ul>
        {section name=search loop=$Senders}
	    <li>{$Senders[search]} <a href="mail.php?read={$Mailid[search]}">{$Subjects[search]}</a></li>
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
        <input type="submit" value="{$Asort}" /> {$Tsender}: <select name="sort1">
	    <option value="-1">{$Sall}</option>
	    {section name=sort1 loop=$Sendersid}
	        <option value="{$Sendersid[sort1]}">{$Senders[sort1]}</option>
	    {/section}
	</select> {$Ttime}: <select name="sort2">
	    <option value="-1">{$Sall2}</option>
	    <option value="7">{$Tlastweek}</option>
	    <option value="30">{$Tlastmonth}</option>
	    <option value="31">{$Toldest}</option>
	</select>
    </form>
    <form method="post" action="mail.php?step=mail">
    <table>
    <tr>
    <td width="20"><b><u>{$Tselect}</u></b></td>
    <td width="75"><b><u>{$From}</u></b></td>
    <td width="75"><b><u>{$Sid}</u></b></td>
    <td><b><u>{$Mtitle}</u></b></td>
    </tr>
    {section name=mail loop=$Sender}
        <tr>
        <td><input type="checkbox" name="{$Mailid[mail]}" /></td>
        <td><a href="view.php?view={$Senderid[mail]}">{$Sender[mail]}</a></td>
        <td>{$Senderid[mail]}</td>
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
    <form method="post" action="mail.php?view=zapis&amp;sort">
        <input type="submit" value="{$Asort}" /> {$Tsender}: <select name="sort1">
	    <option value="-1">{$Sall}</option>
	    {section name=sort1 loop=$Sendersid}
	        <option value="{$Sendersid[sort1]}">{$Senders[sort1]}</option>
	    {/section}
	</select> {$Ttime}: <select name="sort2">
	    <option value="-1">{$Sall2}</option>
	    <option value="7">{$Tlastweek}</option>
	    <option value="30">{$Tlastmonth}</option>
	    <option value="31">{$Toldest}</option>
	</select>
    </form>
    <form method="post" action="mail.php?step=mail">
    <table>
    <tr>
    <td width="20"><b><u>{$Tselect}</u></b></td>
    <td width="75"><b><u>{$From}</u></b></td>
    <td width="75"><b><u>{$Sid}</u></b></td>
    <td><b><u>{$Mtitle}</u></b></td>
    </tr>
    {section name=mail1 loop=$Sender}
        <tr>
        <td><input type="checkbox" name="{$Mailid[mail1]}" /></td>
        <td><a href="view.php?view={$Senderid[mail1]}">{$Sender[mail1]}</a></td>
        <td>{$Senderid[mail1]}</td>
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
    <table>
    <form method="post" action="mail.php?view=write&amp;step=send">
    <tr><td>{$Sto} {html_options name=player options=$Contacts}:</td><td><input type="text" name="to" value="{$To}" /></td></tr>
    <tr><td>{$Mtitle}:</td><td><input type="text" name="subject" size="55" /></td></tr>
    <tr><td valign="top">{$Mbody}:</td><td><textarea name="body" rows="13" cols="55"></textarea></td></tr>
    <tr><td></td><td align="center"><input type="submit" value="{$Asend}" /></td></tr>
    </form></table><br />
    {$Mhelp}
{/if}

{if $Read != ""}
    {section name=read loop=$Sendersid}
        {$Date2[read]} <b><a href="view.php?view={$Sendersid[read]}">{$Senders[read]}</a></b> {$Twrite}... <br />
	"{$Bodies[read]}"<br />
	{if $One == 0}
	    [<a href="mail.php?zapisz={$Mailsid[read]}">{$Asave}</a>]
        {/if}
    	[<a href="mail.php?kasuj={$Mailsid[read]}">{$Adelete}</a>]
	{if $Sendersid[read] != $Id}
	    [<a href="mail.php?send={$Mailsid[read]}">{$Asend2}</a>]
    	    [<a href="mail.php?block={$Sendersid[read]}">{$Ablock}</a>]
	{/if}
	<br /><br />
    {/section}
    {if $One == 0}
        <form method="post" action="mail.php?view=write&amp;step=send">
            <textarea name="body" rows="13" cols="55"></textarea><br />
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

{if $View == "blocks"}
    {if $Blockid[0] != ""}
        <form method="post" action="mail.php?view=blocks&amp;step=unblock">
            <table>
                <tr>
                    {section name=blocks loop=$Blockid}
                        <td><input type="checkbox" name="{$Blockid[blocks]}" /></td>
                        <td>{$Blockname[blocks]} ID: {$Blockid[blocks]}</td>
                    {/section}
                </tr>
            </table>
            <input type="submit" value="{$Aunblock}" />
        </form>
    {else}
        {$Nobanned}
    {/if}
    <br /><br />(<a href="mail.php">{$Aback}</a>)
{/if}
