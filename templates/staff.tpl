{if $View == ""}
    {$Panelinfo}
    <ul>
    {foreach $Links as $link}
        <li><a href="{$link.1}">{$link.0}</a></li>
    {/foreach}
    </ul>
{/if}

{if $View == "bugreport"}
    {if $Step != ""}
        <b>{$Bugname}:</b> {$Bugname2}<br />
        <b>{$Bugtype}:</b> {$Bugtype2}<br />
        <b>{$Bugloc}:</b> {$Bugloc2}<br />
        <b>{$Bugdesc}:</b> {$Bugdesc2}<br />
        <form method="post" action="staff.php?view=bugreport&amp;step={$Step}">
            <b>{$Bugactions}:</b> <select name="actions">
                {section name=bugs loop=$Bugoptions}
                    <option value="{$Bugactions2[bugs]}">{$Bugoptions[bugs]}</option>
                {/section}
            </select><br />
	    <b>{$Vallars}:</b> <input type="text" size="5" name="vallars" value="0" /><br />
            <b>{$Tcomment}:</b> <textarea name="bugcomment" rows="5" cols="30"></textarea><br /><br />
            <input type="submit" value="{$Amake}" />
        </form>
    {else}
        <table align="center">
            <tr>
                <td><b>{$Bugid}</b></td>
                <td><b>{$Bugreporter}</b></td>
                <td><b>{$Bugtype}</b></td>
                <td><b>{$Bugloc}</b></td>
                <td><b>{$Bugname}</b></td>
            </tr>
            {section name=bugtrack loop=$Bugsid}
                <tr>
                    <td align="center"><a href="staff.php?view=bugreport&amp;step={$Bugsid[bugtrack]}">{$Bugsid[bugtrack]}</td>
                    <td align="center">{$Bugsreporter[bugtrack]}</td>
                    <td align="center">{$Bugstype[bugtrack]}</td>
                    <td align="center">{$Bugsloc[bugtrack]}</td>
                    <td align="center">{$Bugsname[bugtrack]}</td>
                </tr>
            {/section}
        </table>
    {/if}
{/if}

{if $View == "banmail"}
    {$Blocklist}<br />
    {section name=banmail loop=$List1}
        ID {$List1[banmail]}<br />
    {/section}
    <form method="post" action="staff.php?view=banmail&amp;step=mail">
    <select name="mail">
        <option value="blok">{$Ablock}</option>
        <option value="odblok">{$Aunblock}</option>
    </select>
    {$Mailid} <input type="text" name="mail_id" size="5" /><br />
    <input type="submit" value="{$Amake}" /></form>
{/if}

{if $View == "innarchive"}
    {if $Text[0] != ""}
        <form method="post" action="staff.php?view=innarchive">
	    <input type="submit" value="{$Ashow}" /> <input type="checkbox" name="whispers" value="Y" {$Checked} />{$Twhispers}<br />
	</form>
        {section name=player loop=$Text}
            {$Sdate[player]} <b>{$Author[player]} {$Cid}:{$Senderid[player]}</b>: {$Text[player]}<br />
        {/section}
    {/if}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="staff.php?view=innarchive&page={$page}&amp;whispers={$Whispers}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
{/if}

{if $View == "jail"}
    <form method="post" action="staff.php?view=jail&amp;step=add">
    {$Jailid}: <input type="text" name="prisoner" /><br />
    {$Jailreason}: <textarea name="verdict"></textarea><br />
    {$Jailtime}: <input type="text" name="time" /><br />
    <input type="submit" value="{$Aadd}" /></form>
{/if}

{if $View == "tags"}
    <form method="post" action="staff.php?view=tags&amp;step=tag">
    {$Giveimmu} <input type="text" name="tag_id" size="5" />. <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "takeaway"}
    {$Takeinfo}<br />
    <form method="post" action="staff.php?view=takeaway&amp;step=takenaway">
    <table>
        <tr>
            <td>{$Takeid}:</td><td><input type="text" name="id" size="5" /></td>
        </tr>
        <tr>
            <td>{$Takeamount}:</td><td><input type="text" name="taken" /></td>
        </tr>
        <tr>
            <td>{$Treason}</td><td><textarea name="verdict"></textarea></td>
        </tr>
        <tr>
            <td>{$Tinjured}</td><td><input type="text" name="id2" size="5" /></td>
        </tr>
        <tr>
            <td colspan="2" align="center"><input type="submit" value="{$Atakeg}" /></td>
        </tr>
    </table>
    </form>
{/if}

{if $View == "czat" || $View == "bforum"}
    {$Blocklist}<br />
    {section name=player loop=$List1}
        ID {$List1[player]}<br />
    {/section}
    <form method="post" action="staff.php?view={$View}&amp;step=czat">
    <select name="czat">
    <option value="blok">{$Ablock}</option>
    <option value="odblok">{$Aunblock}</option></select>
    {$Chatid} <input type="text" name="czat_id" size="5" /> {$Ona} <input type="text" size="5" name="duration" value="1" />{$Tdays}<br />
    <textarea name="verdict"></textarea><br />
     <input type="submit" value="{$Amake}" />
    </form>
{/if}

{if $View == "addtext"}
    {$Admininfo}<br />
    {$Admininfo2}<br />
    {$Admininfo3}<br />
    {$Admininfo4}<br /><br />
    {$Admininfo5}:
    <table width="100%">
        {section name=staff2 loop=$Ttitle}
            <tr>
                <td>{$Ttitle[staff2]} ({$Tauthor2}: {$Tauthor[staff2]})</td>
                <td><a href="staff.php?view=addtext&amp;action=modify&amp;text={$Tid[staff2]}">{$Amodify}</a></td>
                <td><a href="staff.php?view=addtext&amp;action=add&amp;text={$Tid[staff2]}">{$Aadd}</a></td>
                <td><a href="staff.php?view=addtext&amp;action=delete&amp;text={$Tid[staff2]}">{$Adelete}</a></td>
            </tr>
        {/section}
    </table>
{/if}

{if $Action == "modify"}
<form method="post" action="staff.php?view=addtext&amp;action=modify&amp;text={$Tid}">
    {$Ttitle2}: <input type="text" name="ttitle" value="{$Ttitle}" /><br />
    {$Tbody2}: <br /><textarea name="body" rows="30" cols="60">{$Tbody}</textarea><br />
    <input type="hidden" name="tid" value="{$Tid}" />
    <input type="submit" value="{$Achange}" />
</form>
{/if}

{if $View == "logs"}
    {$Logsinfo}<br /><br />
    <form method="post" action="staff.php?view=logs">
        <input type="submit" value="{$Asearch}" /> {$Tsearch} <input type="text" name="lid" size="5" />
    </form><br />
    <table align="center" width="95%" align="center">
        <tr>
            <th>{$Lowner}</th>
	    <th>{$Ltime}</th>
            <th>{$Ltext}</th>
        </tr>
	{foreach $Logs as $log}
            <tr>
                <td>{$log.owner}</td>
                <td>{$log.czas}</td>
		<td>{$log.log}</td>
            </tr>
	{/foreach}
    </table><br />
    {if $Pages > 1}
    	{for $page = 1 to $Pages}
	    {if $page == $Page}
	        {$page}
	    {else}
                <a href="staff.php?view=logs&amp;page={$page}{$Lid}">{$page}</a>
	    {/if}
    	{/for}
    {/if}<br /><br />
    <form method="post" action="staff.php?view=logs&amp;step=clear">
        <input type="submit" value="{$Lclear}" />
    </form>
{/if}
