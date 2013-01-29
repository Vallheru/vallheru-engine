{if $View == ""}
    {$Awelcome}
    <table align="center" width="75%">
        <tr>
            <td align="left" width="50%" valign="top">
                <a href="addupdate.php">{$Aaddupdate}</a><br />
                <a href="addnews.php">{$Aaddnews}</a><br />
                {section name=menu1 loop=$View1}
                    <a href="admin.php?view={$View1[menu1]}">{$Links1[menu1]}</a><br />
                {/section}
            </td>
            <td>
                {section name=menu2 loop=$View2}
                    <a href="admin.php?view={$View2[menu2]}">{$Links2[menu2]}</a><br />
                {/section}
            </td>
        </tr>
        <tr>
            <td align="left" width="50%" valign="top">
                {section name=menu3 loop=$View3}
                    <a href="admin.php?view={$View3[menu3]}">{$Links3[menu3]}</a><br />
                {/section}
            </td>
            <td>
                {section name=menu4 loop=$View4}
                    <a href="admin.php?view={$View4[menu4]}">{$Links4[menu4]}</a><br />
                {/section}
            </td>
        </tr>
        <tr>
            <td align="left" width="50%" valign="top">
                {section name=menu5 loop=$View5}
                    <a href="admin.php?view={$View5[menu5]}">{$Links5[menu5]}</a><br />
                {/section}
            </td>
            <td>
                {section name=menu6 loop=$View6}
                    <a href="admin.php?view={$View6[menu6]}">{$Links6[menu6]}</a><br />
                {/section}
                <a href="bugtrack.php">{$Abugtrack}</a>
            </td>
        </tr>
    </table>
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
                <td><a href="admin.php?view=addtext&amp;action=modify&amp;text={$Tid[staff2]}">{$Amodify}</a></td>
                <td><a href="admin.php?view=addtext&amp;action=add&amp;text={$Tid[staff2]}">{$Aadd}</a></td>
                <td><a href="admin.php?view=addtext&amp;action=delete&amp;text={$Tid[staff2]}">{$Adelete}</a></td>
            </tr>
        {/section}
    </table>

    {if $Action == "modify"}
        <form method="post" action="admin.php?view=addtext&amp;action=modify&amp;text={$Tid}">
            {$Ttitle2}: <input type="text" name="ttitle" value="{$Ttitle}" /><br />
            {$Tbody2}: <br /><textarea name="body" rows="30" cols="60">{$Tbody}</textarea><br />
            <input type="hidden" name="tid" value="{$Tid}" />
            <input type="submit" value="{$Achange}" />
        </form>
    {/if}
{/if}

{if $View == "bugreport"}
    {if $Step != ""}
        <b>{$Bugname}:</b> {$Bugname2}<br />
        <b>{$Bugtype}:</b> {$Bugtype2}<br />
        <b>{$Bugloc}:</b> {$Bugloc2}<br />
        <b>{$Bugdesc}:</b> {$Bugdesc2}<br />
        <form method="post" action="admin.php?view=bugreport&amp;step={$Step}">
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
                    <td align="center"><a href="admin.php?view=bugreport&amp;step={$Bugsid[bugtrack]}">{$Bugsid[bugtrack]}</td>
                    <td align="center">{$Bugsreporter[bugtrack]}</td>
                    <td align="center">{$Bugstype[bugtrack]}</td>
                    <td align="center">{$Bugsloc[bugtrack]}</td>
                    <td align="center">{$Bugsname[bugtrack]}</td>
                </tr>
            {/section}
        </table>
    {/if}
{/if}

{if $View == "vallars"}
    <form method="post" action="admin.php?view=vallars&amp;step=add">
    {$Valid}: <input type="text" name="id" /> <br />
    {$Vallars}: <input type="text" name="amount" /><br />
    {$Vreason}: <textarea name="reason"></textarea><br />
    <input type="submit" value="{$Aadd}" /></form>
{/if}

{if $View == "banmail"}
    {$Blocklist}<br />
    {section name=banmail loop=$List1}
        ID {$List1[banmail]}<br />
    {/section}
    <form method="post" action="admin.php?view=banmail&amp;step=mail">
    <select name="mail">
        <option value="blok">{$Ablock}</option>
        <option value="odblok">{$Aunblock}</option>
    </select>
    {$Mailid} <input type="text" name="mail_id" size="5" /><br />
    <input type="submit" value="{$Amake}" /></form>
{/if}

{if $View == "playerquest"}
    <form method="post" action="admin.php?view=playerquest&amp;step=add">
        <input type="submit" value="{$Aadd}" /> {$Addplayer} <input type="text" name="pid" size="5" /> {$Toquest} <input type="text" name="qid" size="5" />.
    </form>
{/if}

{if $View == "innarchive"}
    {if $Text[0] != ""}
        <form method="post" action="admin.php?view=innarchive">
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
                <a href="admin.php?view=innarchive&page={$page}&amp;whispers={$Whispers}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
{/if}

{if $View == "changelog"}
    {$Changeinfo}<br /><br />
    <form method="post" action="admin.php?view=changelog&amp;step=add">
        {$Changelocation}: <input type="text" name="location" /><br />
        {$Changetext}: <textarea name="changetext" rows="5" cols="30"></textarea><br /><br />
        <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "logs" || $View == "slog"}
    {$Logsinfo}<br /><br />
    <form method="post" action="admin.php?view={$View}">
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
	        {if $View == "logs"}
                    <td>{$log.owner}</td>
                    <td>{$log.czas}</td>
		{else}
		    <td>{$log.pid}</td>
		    <td>{$log.date}</td>
		{/if}
		    <td>{$log.log}</td>
            </tr>
	{/foreach}
    </table><br />
    {if $Pages > 1}
    	{for $page = 1 to $Pages}
	    {if $page == $Page}
	        {$page}
	    {else}
                <a href="admin.php?view={$View}&amp;page={$page}{$Lid}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
    <form method="post" action="admin.php?view={$View}&amp;step=clear">
        <input type="submit" value="{$Lclear}" />
    </form>
{/if}

{if $View == "meta"}
    {$Metainfo}
    <form method="post" action="admin.php?view=meta&amp;step=modify">
        Meta keywords ({$Metakey}): <input type="text" name="metakey" size="30"/><br />
        Meta description ({$Metadesc}): <input type="text" name="metadesc" size="40" /><br />
        <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "forums"}
    {$Catlist}<br />
    {section name=forum loop=$Catname}
        <a href="admin.php?view=forums&amp;id={$Catid[forum]}">{$Catname[forum]}</a><br />
    {/section}<br /><br />
    <form method=post action="admin.php?view=forums&amp;id={$Catid2}&amp;step=add">
        {$Tname}: <input type="text" name="catname" value="{$Tcatname}" /><br />
        {$Tdesc}: <textarea name="catdesc" rows="5" cols="30">{$Tcatdesc}</textarea><br />
	<div style="float: right;">
	    {$Tvisit}: <br />
	    {html_checkboxes name="visit" values=$Toptions output=$Toptionname selected=$Toptionvsel separator="<br />"}
        </div>
        <div>
	    {$Twrite}: <br />
	    {html_checkboxes name="write" values=$Toptions output=$Toptionname selected=$Toptionwsel separator="<br />"}
        </div>
	<div>
	    {$Ttopics}: <br />
	    {html_checkboxes name="topics" values=$Toptions output=$Toptionname selected=$Toptiontsel separator="<br />"}
        </div>
        <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "mill"}
    <form method="post" action="admin.php?view=mill&amp;step=mill">
    {$Sname}: <input type="text" name="nazwa" /> <br />
    {$Scost}: <input type="text" name="cena" /> <br />
    {$Samount}: <input type="text" name="amount" /><br />
    {$Slevel}: <input type="text" name="poziom" /> <br />
    {$Stype}: <select name="type" id="type">
    <option value="B">{$Sbow}</option>
    <option value="R">{$Sarrow}</option>
    </select><br />
    {$Selite}: <input type="text" value="0" name="elite" size="5"/><br />
    {$Selitetype}: <select name="elitetype">
        <option value="S">{$Dragon}</option>
	<option value="E">{$Elven}</option>
    </select><br />
    <br /><input type="submit" value="{$Aadd}" /></form>
{/if}

{if $View == "donator"}
    {if $Step == ""}
        {$Donatorinfo}<br />
        <form method="post" action="admin.php?view=donator&amp;step=add">
            {$Pname}: <input type="text" name="plname" /><br />
            <input type="submit" value="{$Aadd}" />
        </form>
    {/if}
{/if}

{if $View == "clearf"}
    {if $Step == ""}
    {$Fquestion}<br />
    - <a href="admin.php?view=clearf&amp;step=Y">{$Ayes}</a><br />
    {/if}
{/if}

{if $View == "monster2"}
    {if $Step == ""}
        <form method="post" action="admin.php?view=monster2&amp;step=next">
            {$Mname}: <select name="mid">
                {section name=monster2 loop=$Mid}
                    <option value="{$Mid[monster2]}">{$Names[monster2]}</option>
                {/section}
            </select><br />
            <input type="submit" value="{$Anext}" />
        </form>
    {/if}
    {if $Step == "next"}
        <form method="post" action="admin.php?view=monster2&amp;step=monster">
            {$Tmname}: <input type="text" name="name" value="{$Mname}" /> <br />
            {$Tmlevel}: <input type="text" name="level" value="{$Mlvl}" /> <br />
            {$Tmhp}: <input type="text" name="hp" value="{$Mhp}" /> <br />
            {$Tmagi}: <input type="text" name="agility" value="{$Magility}" /> <br />
            {$Tmpower}: <input type="text" name="strength" value="{$Mstrength}" /> <br />
            {$Tmspeed}: <input type="text" name="speed" value="{$Mspeed}" /> <br />
            {$Tmcond}: <input type="text" name="endurance" value="{$Mendurance}" /> <br />
            {$Tmmingold}: <input type="text" name="credits1" value="{$Mcredits1}" /> <br />
            {$Tmmaxgold}: <input type="text" name="credits2" value="{$Mcredits2}" /> <br />
            {$Tmminexp}: <input type="text" name="exp1" value="{$Mexp1}" /> <br />
            {$Tmmaxexp}: <input type="text" name="exp2" value="{$Mexp2}" /> <br />
            {$Tmlocation}: <input type="text" name="location" value="{$Mlocation}" /><br />
	    {$Tmlootnames}: <input type="text" name="lootnames" value="{$Mlootname}" /><br />
	    {$Tmlootchances}: <input type="text" name="lootchances" value="{$Mlootchance}" /><br />
	    {$Tmresistance}: <input type="text" name="resistance" value="{$Mresistance}" /><br />
	    {$Tmdmgtype}: <input type="text" name="dmgtype" value="{$Mdmgtype}" /><br />
	    {$Tmdesc}: <br />
	    <textarea name="mdesc">{$Mdesc}</textarea><br /><br />
            <input type="hidden" name="mid" value="{$Mid}" />
            <input type="submit" value="{$Aedit}" />
        </form>
    {/if}
{/if}

{if $View == "jailbreak"}
    {if $Step == ""}
        <form method="post" action="admin.php?view=jailbreak&amp;step=next">
            <input type="submit" value="{$Afree}" /> {$Jailid} <input type="text" name="jid" size="5" />
        </form>
    {/if}
{/if}

{if $View == "poll"}
    {if $Step == ""}
        <form method="post" action="admin.php?view=poll&amp;step=second">
            {$Tquestion}: <input type="text" name="question" /><br />
	    {$Tdesc}: <textarea name="desc"></textarea><br />
            {$Tlang}: <select name="lang">
            {section name=poll2 loop=$Llang}
                <option value="{$Llang[poll2]}">{$Llang[poll2]}</option>
            {/section}
            </select><br />
            {$Tamount}: <input type="text" size="5" name="amount" /><br />
            {$Tdays}: <input type="text" size="5" name="days" /><br />
            <input type="submit" value="{$Anext}" />
        </form>
    {/if}
    {if $Step == "second"}
        {$Tquestion}: {$Question} ({$Llang}) ({$Adays} dni)<br />
        <form method="post" action="admin.php?view=poll&amp;step=add">
            {section name=poll loop=$Answers}
                {$Tanswer}: <input type="text" name="{$Answers[poll]}" /><br />
            {/section}
            <input type="hidden" name="amount" value="{$Amount}" />
            <input type="hidden" name="pid" value="{$Pollid}" />
            <input type="hidden" name="lang" value="{$Llang}" />
            <input type="submit" value="{$Aadd}" />
        </form>
    {/if}
{/if}

{if $View == "censorship"}
    <form method="post" action="admin.php?view=censorship&amp;step=modify">
        <select name="action">
            <option value="add">{$Aadd}</option>
            <option value="delete">{$Adelete}</option>
        </select>
        {$Tword} <input type="text" name="bword" /><br />
        <input type="submit" value="{$Amake}" />
    </form>
    {$Wordslist}:<br />
    {section name=censor loop=$Words}
        {$Words[censor]}<br />
    {/section}
{/if}

{if $View == "register" || $View == "close"}
    <form method="post" action="admin.php?view={$View}&amp;step=close">
    <select name="close">
    <option value="close">{$Gclose}</option>
    <option value="open">{$Gopen}</option>
    </select><br />
    {$Ifclose}:<br />
    <textarea name="reason" rows="13" cols="55"></textarea><br />
    <input type="submit" value="{$Amake}" /></form>
{/if}

{if $View == "ban"}
    {$Banlist}<br />
    <form method="post" action="admin.php?view=ban&amp;step=modify">
    {$Banvalue}: <input type="text" name="amount" /><br />
    {$Banned}: <select name="type"><option value="IP">{$Banip}</option>
    <option value="mailadres">{$Banemail}</option>
    <option value="nick">{$Bannick}</option>
    <option value="ID">{$Banid}</option>
    </select><br />
    <select name="action"><option value="ban">{$Abanpl}</option>
    <option value="unban">{$Aunban}</option>
    </select>
    <input type="submit" value="{$Anext}" /></form>
    {section name=ban loop=$Type}
        <b>{$Bantype}:</b> {$Type[ban]} <b>{$Banval}:</b> {$Amount[ban]}<br />
    {/section}
    {$Baninfo}
{/if}

{if $View == "mail"}
    <form method="post" action="admin.php?view=mail&amp;step=send">
    {$Mailinfo}<br />
    <textarea name="message"></textarea><br />
    <input type="submit" value="{$Asend}" /></form>
{/if}

{if $View == "bridge"}
    <form method="post" action="admin.php?view=bridge&amp;step=add">
    {$Bquestion}: <textarea name="question"></textarea><br />
    {$Banswer}: <textarea name="answer"></textarea><br />
    <input type="submit" value="{$Aadd}" /></form>
{/if}

{if $View == "jail"}
    <form method="post" action="admin.php?view=jail&amp;step=add">
    {$Jailid}: <input type="text" name="prisoner" /><br />
    {$Jailreason}: <textarea name="verdict"></textarea><br />
    {$Jailtime}: <input type="text" name="time" /><br />
    <input type="submit" value="{$Aadd}" /></form>
{/if}

{if $View == "del"}
    <form method="post" action="admin.php?view=del&amp;step=del">
    {$Deleteid}<input type="text" name="did" />.<input type="submit" value="{$Adeletepl}" />
    </form>
{/if}

{if $View == "add"}
    <form method="post" action="admin.php?view=add&amp;step=add">
    {$Addid} <input type="text" name="aid" size="5" /> {$Newrank} {html_options name=rank options=$Ranks}. <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "srank"}
    <form method="post" action="admin.php?view=srank&amp;step=add">
    <input type="submit" value="{$Aadd}" /> {$Newrank} <input type="text" name="rank" size="20" /> {$Plid} <input type="text" name="aid" size="5" />
    </form>
{/if}

{if $View == "tags"}
    <form method="post" action="admin.php?view=tags&amp;step=tag">
    {$Giveimmu} <input type="text" name="tag_id" size="5" />. <input type="submit" value="{$Agive}" />
    </form>
{/if}

{if $View == "equipment"}
    <form method="post" action="admin.php?view=equipment&amp;step=add">
    {$Itemname} <input type="text" name="name" /> {$Hasa}
    <select name="type" id="type">
    <option value="W">{$Iweapon}</option>
    <option value="A">{$Iarmor}</option>
    <option value="H">{$Ihelmet}</option>
    <option value="L">{$Ilegs}</option>
    <option value="S">{$Ishield}</option>
    <option value="B">{$Ibow}</option>
    <option value="R">{$Iarrows}</option>
    <option value="T">{$Istaff}</option>
    <option value="C">{$Icape}</option>
    </select>
    <br /> {$Iwith}
    <input name="power" type="number" id="power" /> {$Ipower}<br />
    {$Icost} <input type="number" name="cost" />
    <br /> {$Iminlev}
    <input type="number" name="minlev" /><br /> {$Iagi}
    <input name="zr" type="number" /><br /> {$Ispeed}
    <input type="number" name="szyb" /><br />
    {$Idur}<input type="number" name="maxwt" /><br />
    {$Irepair}<input type="number" name="repair" /><br />
    <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "donate"}
    <form method="post" action="admin.php?view=donate&amp;step=donated">
    {$Donateid}: <input type="text" name="id" /> <br />
    {$Donateamount}: <input type="text" name="amount" /> {$Tbudget} {$Budget}<br />
    <input type="submit" value="{$Adonate}" /></form>
{/if}

{if $View == "takeaway"}
    {$Takeinfo}<br />
    <form method="post" action="admin.php?view=takeaway&amp;step=takenaway">
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

{if $View == "monster"}
    <form method="post" action="admin.php?view=monster&amp;step=monster">
    {$Mname}: <input type="text" name="nazwa" /> <br />
    {$Mlevel}: <input type="text" name="poziom" /> <br />
    {$Mhp}: <input type="text" name="pz" /> <br />
    {$Magi}: <input type="text" name="zr" /> <br />
    {$Mpower}: <input type="text" name="sila" /> <br />
    {$Mspeed}: <input type="text" name="speed" /> <br />
    {$Mcond}: <input type="text" name="endurance" /> <br />
    {$Mmingold}: <input type="text" name="minzl" /> <br />
    {$Mmaxgold}: <input type="text" name="maxzl" /> <br />
    {$Mminexp}: <input type="text" name="minpd" /> <br />
    {$Mmaxexp}: <input type="text" name="maxpd" /> <br />
    {$Mlocation}: <select name="location">
        <option value="Altara">{$Mcity1}</option>
        <option value="Ardulith">{$Mcity2}</option>
        <option value="Cytadela">{$Mcity3}</option>
    </select><br />
    {$Mlootnames}: <input type="text" name="lootnames" /><br />
    {$Mlootchances}: <input type="text" name="lootchances" /><br />
    {$Mresistance}: <input type="text" name="resistance" /><br />
    {$Mdmgtype}: <input type="text" name="dmgtype" /><br />
    <input type="submit" value="{$Aadd}" /></form>
{/if}

{if $View == "kowal"}
    <form method="post" action="admin.php?view=kowal&amp;step=kowal">
    {$Sname}: <input type="text" name="nazwa" /> <br />
    {$Scost}: <input type="text" name="cena" /> <br />
    {$Samount}: <input type="text" name="amount" /><br />
    {$Slevel}: <input type="text" name="poziom" /> <br />
    {$Stype}: <select name="type" id="type">
    <option value="W">{$Sweapon}</option>
    <option value="A">{$Sarmor}</option>
    <option value="H">{$Shelmet}</option>
    <option value="L">{$Slegs}</option>
    <option value="S">{$Sshield}</option>
    </select><br />
    {$Stwohand}: <select name="twohand">
        <option value="N">{$Ano}</option>
        <option value="Y">{$Ayes}</option>
    </select><br />
    {$Selite}: <input type="text" value="0" name="elite" size="5"/><br />
    {$Selitetype}: <select name="elitetype">
        <option value="S">{$Dragon}</option>
	<option value="E">{$Elven}</option>
    </select><br /><br />
    <input type="submit" value="{$Aadd}" /></form>
{/if}

{if $View == "poczta"}
    <table>
    <form method="post" action="admin.php?view=poczta&amp;step=send">
    <tr><td>{$Pmsubject}:</td><td><input type="text" name="subject" /></td></tr>
    <tr><td valign="top">{$Pmbody}:</td><td><textarea name="body" rows="5" cols="19"></textarea></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Asend}" /></td></tr>
    </form></table>
{/if}

{if $View == "czat" || $View == "bforum"}
    {$Blocklist}<br />
    {section name=player loop=$List1}
        ID {$List1[player]}<br />
    {/section}
    <form method="post" action="admin.php?view={$View}&amp;step=czat">
    <select name="czat">
        <option value="blok">{$Ablock}</option>
        <option value="odblok">{$Aunblock}</option>
    </select>
    {$Chatid} <input type="text" name="czat_id" size="5" /> {$Ona} <input type="text" size="5" name="duration" value="1" />{$Tdays}<br />
    <textarea name="verdict"></textarea><br />
    <input type="submit" value="{$Amake}" /></form>
{/if}

{if $View == "czary"}
    <form method="post" action="admin.php?view=czary&amp;step=add">
    {$Spellname} <input type="text" name="name" /> {$Hasas}
    <select name="type">
    <option value="B">{$Sbattle}</option>
    <option value="O">{$Sdefense}</option>
    </select><br />
     {$Swith} <input name="power" type="number" /> {$Spower}<br /> 
    {$Scost} <input type="number" name="cost" /><br /> 
    {$Sminlev} <input type="number" name="minlev" /><br />
    {$Selement} {html_options name=element options=$Soptions}<br />
    <input type="submit" value="{$Aadd}" /></form>
{/if}

{if $View == "potions"}
    <form method="post" action="admin.php?view=potions&amp;step=add">
    {$Pname}: <input type="text" name="name" /> <select name="type">
        <option value="P">{$Poison}</option>
	<option value="A">{$Antidote}</option>
	<option value="H">{$Healing}</option>
	<option value="M">{$Mana}</option>
    </select><br />
    {$Tefect}: <input type="text" name="effect" /><br />
    {$Ppower}: <input type="text" name="power" /><br /> 
    <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "alchemy"}
    <form method="post" action="admin.php?view=alchemy&amp;step=add">
    {$Pname}: <input type="text" name="name" /><br />
    {$Therb1}: <input type="text" name="illani" /><br />
    {$Therb2}: <input type="text" name="illanias" /><br />
    {$Therb3}: <input type="text" name="nutari" /><br />
    {$Therb4}: <input type="text" name="dynallca" /><br />
    {$Tlevel}: <input type="text" name="level" /><br />
    {$Tcost}: <input type="text" name="cost" /><br />
    <input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "pdescriptions" || $View == "pitems" || $View == "pbridge" || $View == 'pmdesc'}
    <table align="center">
        <tr>
	    <th>{$Tid}</th>
	    <th>{$Treporter}</th>
	    <th>{$Tlocation}</th>
	</tr>
	{section name=desc loop=$Proposals}
	    <tr>
	        <td><a href="admin.php?view={$View}&amp;step={$Proposals[desc].id}">{$Proposals[desc].id}</a></td>
		<td>{$Proposals[desc].pid}</td>
		<td>{$Proposals[desc].name}</td>
	    </tr>
	{/section}
    </table>
    {if $Step != ""}
    	<b>{$Tloc}</b> {$Location}<br /><br />
	<form method="post" action="admin.php?view={$View}&amp;step={$Step}&amp;confirm">
        <b>{$Tdesc}</b><br /><textarea name="desc">{$Desc}</textarea><br /><br />
	<b>{$Tinfo}</b><br /><textarea name="info">{$Info}</textarea><br /><br />
	    <select name="response"><option value="A">{$Accepted}</option>
	        <option value="R">{$Rejected}</option>
	    </select><br />
	    {$Tvallars} <input type="text" name="valars" value="1" size="5" /><br />
	    {$Treason} <textarea name="reason"></textarea><br />
	    <input type="submit" value="{$Asend}" />
	</form>
    {/if}
{/if}

{if $View == "pmonsters"}
    <table align="center">
        <tr>
	    <th>{$Tid}</th>
	    <th>{$Treporter}</th>
	    <th>{$Tlocation}</th>
	</tr>
	{section name=desc loop=$Proposals}
	    <tr>
	        <td><a href="admin.php?view={$View}&amp;step={$Proposals[desc].id}">{$Proposals[desc].id}</a></td>
		<td>{$Proposals[desc].pid}</td>
		<td>{$Proposals[desc].name}</td>
	    </tr>
	{/section}
    </table>
    {if $Step != ""}
        {$Tname} {$Name}<br />
	{$Tlevel} {$Mlevel}<br />
	{$Tloc} {$Loc}<br />
	{$Tstr} {$Str}<br />
	{$Tagi} {$Agi}<br />
	{$Tspeed} {$Speed}<br />
	{$Tcon} {$Con}<br />
	{$Tgold} {$Mgold}<br />
	{$Texp} {$Mexp}<br />
	{$Tresistance} {$Mresistance}<br />
	{$Tdmgtype} {$Mdmgtype}<br />
	{$Tloot1} {$Loot1}<br />
	{$Tloot2} {$Loot2}<br />
	{$Tloot3} {$Loot3}<br />
	{$Tloot4} {$Loot4}<br /><br />
	<form method="post" action="admin.php?view={$View}&amp;step={$Step}&amp;confirm">
	    <select name="response"><option value="A">{$Accepted}</option>
	        <option value="R">{$Rejected}</option>
	    </select><br />
	    {$Treason} <textarea name="reason"></textarea><br />
	    <input type="submit" value="{$Asend}" />
	</form>
    {/if}
{/if}

{if $View == "rmission"}
    <form method="post" action="admin.php?view=rmission&amp;step=add">
        {$Tname} <input type="text" name="name" /><br />
	{$Ttext} <textarea name="text"></textarea><br />
	{$Texits} <textarea name="exits"></textarea><br />
	{$Tchances} <input type="text" name="chances" /><br />
	{$Tmobs} <textarea name="mobs"></textarea><br />
	{$Tchances2} <input type="text" name="chances2" /><br />
	{$Titems} <textarea name="items"></textarea><br />
	{$Tchances3} <input type="text" name="chances3" /><br />
	<input type="submit" value="{$Aadd}" />
    </form>
{/if}

{if $View == "slogconf"}
    {$Tlist}<br />
    {foreach $Slist as $followed}
        {$followed.id}<br />
    {/foreach}
    <form method="post" action="admin.php?view=slogconf&amp;step">
        <select name="action">
	    <option value="add">{$Add}</option>
	    <option value="delete">{$Remove}</option>
	</select> {$Tplayer} <input type="text" name="pid" size="5" /> <input type="submit" value="{$Asend}" />
    </form>
{/if}

{$Message}

{if $View != ""}
    <br />(<a href="admin.php">{$Aback}</a>)
{/if}
