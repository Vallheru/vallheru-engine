{if $View == "" && $Read == "" && $Write == "" && $Delete == "" && $Send == "" && $Step == "" && $Block == ""}
    {$Mailinfo}<br /><br />
    - <a href="mail.php?view=inbox">{$Ainbox}</a><br />
    - <a href="mail.php?view=zapis">{$Asaved}</a><br />
    - <a href="mail.php?view=send">{$Aoutbox}</a><br />
    - <a href="mail.php?view=write">{$Awrite}</a><br />
    - <a href="mail.php?view=blocks">{$Ablocklist}</a>
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
    <form method="post" action="mail.php?step=mail&amp;box=I">
    <table class="dark">
    <tr>
    <td width="20"><b><u>{$Tselect}</u></b></td>
    <td width="75"><b><u>{$From}</u></b></td>
    <td width="75"><b><u>{$Sid}</u></b></td>
    <td width="100"><b><u>{$Mtitle}</u></b></td>
    <td width="50"><b><u>{$Moption}</u></b></td>
    </tr>
    {section name=mail loop=$Sender}
        <tr>
        <td><input type="checkbox" name="{$Mailid[mail]}" /></td>
        <td><a href="view.php?view={$Senderid[mail]}">{$Sender[mail]}</a></td>
        <td>{$Senderid[mail]}</td>
        <td>{$Subject[mail]}</td>
        <td>- <a href="mail.php?read={$Mailid[mail]}">{$Aread}</a>{if $Mread[mail] == "Y"} <blink>!!</blink> {/if}</td>
        </tr>
    {/section}
    </table><br />
    <input type="submit" value="{$Adeletes}" name="delete" /> <input type="submit" name="write" value="{$Asaves}" /> <input type="submit" name="read2" value="{$Aread2}" /> <input type="submit" name="unread" value="{$Aunread}" /><br />
    </form>
    <form method="post" action="mail.php?step=deleteold&amp;box=I">
        <input type="submit" value="{$Adeleteold}" /> <select name="oldtime">
            <option value="7">{$Aweek}</option>
            <option value="14">{$A2week}</option>
            <option value="30">{$Amonth}</option>
        </select>
    </form>
    [<a href="mail.php?view=zapis">{$Asaved}</a>]
    [<a href="mail.php?view=inbox&amp;step=clear">{$Aclear}</a>]
    [<a href="mail.php?view=write">{$Awrite}</a>]
{/if}

{if $View == "zapis"}
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
    <form method="post" action="mail.php?step=mail&amp;box=W">
    <table class="dark">
    <tr>
    <td width="20"><b><u>{$Tselect}</u></b></td>
    <td width="75"><b><u>{$From}</u></b></td>
    <td width="75"><b><u>{$Sid}</u></b></td>
    <td width="100"><b><u>{$Mtitle}</u></b></td>
    <td width="50"><b><u>{$Moption}</u></b></td>
    </tr>
    {section name=mail1 loop=$Sender}
        <tr>
        <td><input type="checkbox" name="{$Mailid[mail1]}" /></td>
        <td><a href="view.php?view={$Senderid[mail1]}">{$Sender[mail1]}</a></td>
        <td>{$Senderid[mail1]}</td>
        <td>{$Subject[mail1]}</td>
        <td>- <a href="mail.php?read={$Mailid[mail1]}">{$Aread}</a></td>
        </tr>
    {/section}
    </table><br />
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

{if $View == "send"}
    <form method="post" action="mail.php?view=send&amp;sort">
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
    <form method="post" action="mail.php?step=mail&amp;box=S">
    <table class="dark">
    <tr>
    <td width="20"><b><u>{$Tselect}</u></b></td>
    <td width="75"><b><u>{$Sto}</u></b></td></td>
    <td width="100"><b><u>{$Mtitle}</u></b></td>
    <td width="50"><b><u>{$Moption}</u></b></td>
    </tr>
    {section name=mail2 loop=$Send1}
        <tr>
        <td><input type="checkbox" name="{$Mailid[mail2]}" /></td>
        <td><a href="view.php?view={$Send1[mail2]}">{$Send1[mail2]}</a></td>
        <td>{$Subject[mail2]}</td>
        <td>- <a href="mail.php?read={$Mailid[mail2]}">{$Aread}</a></td>
        </tr>
    {/section}
    </table><br />
    <input type="submit" value="{$Adeletes}" name="delete" /> <input type="submit" name="write" value="{$Asaves}" /><br />
    </form>
    <form method="post" action="mail.php?step=deleteold&amp;box=S">
        <input type="submit" value="{$Adeleteold}" /> <select name="oldtime">
            <option value="7">{$Aweek}</option>
            <option value="14">{$A2week}</option>
            <option value="30">{$Amonth}</option>
        </select>
    </form>
    [<a href="mail.php">{$Aback}</a>]
    [<a href="mail.php?view=send&amp;step=clear">{$Aclear}</a>]
    [<a href="mail.php?view=write">{$Awrite}</a>]
{/if}

{if $View == "write"}
    [<a href="mail.php?view=inbox">{$Ainbox}</a>]<br /><br />
    <table class="dark">
    <form method="post" action="mail.php?view=write&amp;step=send">
    <tr><td>{$Sto}:</td><td><input type="text" name="to" value="{$To}" /></td></tr>
    <tr><td>{$Mtitle}:</td><td><input type="text" name="subject" size="55" value="{$Reply}" /></td></tr>
    <tr><td valign="top">{$Mbody}:</td><td><textarea name="body" rows="13" cols="55">{$Body}</textarea></td></tr>
    <tr><td></td><td align="center"><input type="submit" value="{$Asend}" /></td></tr>
    </form></table>
{/if}

{if $Read != ""}
    {$Tday} <b><a href="view.php?view={$Senderid}">{$Sender}</a></b> {$Twrite}... "{$Body}"<br /><br />
    [<a href="mail.php?view=inbox">{$Ainbox}</a>]
    [<a href="mail.php?zapisz={$Mailid}">{$Asave}</a>]
    [<a href="mail.php?kasuj={$Mailid}">{$Adelete}</a>]
    [<a href="mail.php?view=write">{$Awrite}</a>]
    [<a href="mail.php?view=write&amp;to={$Senderid}&amp;re=Odp:{$Subject}&amp;id={$Mailid}">{$Areply}</a>]
    [<a href="mail.php?send={$Mailid}">{$Asend}</a>]
    [<a href="mail.php?block={$Senderid}">{$Ablock}</a>]
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
