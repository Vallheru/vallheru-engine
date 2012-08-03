{if $Step == "" && $Read == "" && $Comments == "" && $Step3 == ""}
    {$Paperinfo} {$Gamename} {$Paperinfo2}<br />
    {$Paperinfo3}<br /><br /><br />

    <table align="center">
        <tr>
            <td valign="top">
                <a href="newspaper.php?step=new">{$Anewpaper}</a><br />
                <a href="newspaper.php?step=archive">{$Aarchive}</a><br />
                <a href="newspaper.php?step=mail">{$Aredmail}</a><br />
                {if $Rank == "Admin" || $Rank == "Redaktor"}
                    <br /><br /><a href="newspaper.php?step=redaction">{$Aredaction}</a><br />
                {/if}
            </td>
        </tr>
    </table>
{/if}

{if $Article != ""}
    <table align="center" width="80%">
        <tr>
            <td>{$Ttitle} <b>{$Arttitle}</b> ({$Artauthor}) {if $Step3 == "S" && ($Rank == "Admin" || $Rank == "Redaktor")} <a href="newspaper.php?step=redaction&amp;step3=edit&amp;edit={$Artid}">{$Aedit}</a>&nbsp;&nbsp;&nbsp; <a href="newspaper.php?step=redaction&amp;step3=delete&amp;del={$Artid}">{$Adelete}</a>{/if}</td>
        </tr>
        <tr>
            <td>{$Artbody}</td>
        </tr>
        <tr>
            <td><br />{if $Step3 == ""} <a href="newspaper.php?{$Newslink}&amp;comments={$Artid}">{$Acomment}</a> ({$Twrite} {$Artcomments} {$Tcomments}) {/if}<br /><br /></td>
        </tr>
    </table>
{/if}

{if $Comments != ""}
    {if $Amount == "0"}
        <center>{$Nocomments}</center>
    {/if}
    {if $Amount > "0"}
        {section name=newspaper3 loop=$Tauthor}
            <b><a href="view.php?view={$Taid[newspaper3]}">{$Tauthor[newspaper3]}</a></b> {if $Tdate[newspaper3] != ""} ({$Tdate[newspaper3]}) {/if}{$Wrote}: {if $Rank == "Admin" || $Rank == "Redaktor"} (<a href="newspaper.php?comments={$Comments}&amp;action=delete&amp;cid={$Cid[newspaper3]}">{$Adelete}</a>) {/if}<br />
            {$Tbody[newspaper3]}<br /><br />
        {/section}
    {/if}
    {if $Tpages > 1}
    	<br />{$Fpage}
    	{for $page = 1 to $Tpages}
	    {if $page == $Tpage}
	        {$page}
	    {else}
                <a href="newspaper.php?comments={$Comments}&page={$page}">{$page}</a>
	    {/if}
    	{/for}
    {/if}
    <br /><br /><center>
        {include file="comments.tpl"}
    </center><br /><br />
{/if}

{if $Step == "new" || $Read != "" || $Step3 == 'S'}
    {if $Page == "contents"}
    {$Readinfo}<br /><br /><br />
    <table align="center" width="80%">
        <tr>
            <td valign="top">
                {section name=sec1 loop=$Arttypes max=6}
                    {if $Artidm[sec1][0] != "0"}
                        {$Secnames[sec1]}<br />
                        <div style="margin-left: 10px">
                        {section name=art1 loop=$Artidm[sec1]}
                            - <a href="newspaper.php?{$Newslink}&amp;article={$Artidm[sec1][art1]}">{$Arttitlem[sec1][art1]}</a> ({$Aauthor}: {$Artauthorm[sec1][art1]})<br />
			    {$Tcomments}<a href="newspaper.php?{$Newslink}&amp;comments={$Artidm[sec1][art1]}">{$Artcomments[sec1][art1]}</a>)<br />
                        {/section}
                        </div>
                    {/if}
                {/section}
            </td>
            <td valign="top">
                {section name=sec2 loop=$Arttypes start=6}
                    {if $Artidm[sec2][0] != "0"}
                        {$Secnames[sec2]}<br />
                        <div style="margin-left: 10px">
                        {section name=art1 loop=$Artidm[sec2]}
                            - <a href="newspaper.php?{$Newslink}&amp;article={$Artidm[sec2][art1]}">{$Arttitlem[sec2][art1]}</a> ({$Aauthor}: {$Artauthorm[sec2][art1]})<br />
			    {$Tcomments}<a href="newspaper.php?{$Newslink}&amp;comments={$Artidm[sec2][art1]}">{$Artcomments[sec2][art1]}</a>)<br />
                        {/section}
                        </div>
                    {/if}
                {/section}
            </td>
        </tr>
    </table><br /><br />
    {/if}
    {if $Step3 == "S" && $Article == ""}
        <form method="post" action="newspaper.php?step=redaction&amp;step3=release">
            <input type="submit" value="{$Apublic}" />
        </form><br /><br />
    {/if}
    {if $Page == "" && $Comments == "" && $Article == ""}
        <div align="center"><img src="images/vallweek.png" alt="Vallweek" title="Vallweek"/></div>
    {else}
        {if $Step == "new"}
        <div align="center"><a href="newspaper.php?{$Newslink}">{$Amain}</a></div>
        {/if}
    {/if}
    <div align="center"><a href="newspaper.php?{$Newslink}&amp;page=contents">{$Acontents}</a></div>
    {if $Pageid != "" || $Pageid2 != ""}
            <table width="100%">
                <tr>
                    <td width="50%" align="right"><form method="post" action="newspaper.php?{$Newslink}&amp;article={$Pageid2}">{$Previous}</form></td>
                    <td width="50%"><form method="post" action="newspaper.php?{$Newslink}&amp;article={$Pageid}">{$Next}</form></td>
                </tr>
            </table>
    {/if}
    <div align="center"><a href="newspaper.php">{$Aend}</a><br /><br /></div>
{/if}

{if $Step == "archive"}
    {$Archiveinfo}<br /><br />
    {if $Paperid > "0"}
        {section name=newspaper2 loop=$Paperid}
            <a href="newspaper.php?read={$Paperid[newspaper2]}&amp;page=contents">{$Anumber} {$Paperid[newspaper2]}</a><br />
        {/section}
    {/if}
{/if}

{if $Step == 'redaction'}
    <script src="js/editor.js"></script>
    {if $Step3 == ""}
        {$Redactioninfo} {$Gamename}.<br /><br /><br />
        <table align="center" width="80%">
            <tr>
                <td>
                    <a href="newspaper.php?step=redaction&amp;step3=S&amp;page=contents">{$Ashow}</a><br />
                    <a href="newspaper.php?step=redaction&amp;step3=R">{$Aredaction}</a><br />
                </td>
            </tr>
        </table>
    {/if}
    {if $Step3 == "edit" || $Step3 == "R"}
        {$Youedit}<br /><br />
        {$Showmail}<br /><br />
        <form method="post" action="newspaper.php?step=redaction&amp;{if $Step3 == 'edit'}step3=edit&amp;edit={$Edit}{/if}{if $Step3 == "R"}step3=R{/if}" name="earticle">
            {$Mailtype}: <select name="mail">
                {section name=edit loop=$Arttypes}
                <option value="{$Arttypes[edit]}"{if $Mtype == "$Arttypes[edit]"} selected {/if}>{$Sectionnames[edit]}</option>
                {/section}
            </select><br />
            {$Ttitle} <input type="text" name="mtitle" value="{$Mtitle}" /><br />
	    {$Tbody} <br />
	    <input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'earticle', 'mbody')" /> {html_options name=colors options=$Ocolors}<br />
            <textarea name="mbody" rows="13" cols="55">{$Mbody}</textarea><br />
            <input type="submit" value="{$Ashow}" name="show" /> <input type="submit" name="sendmail" value="{$Asend}" />
        </form>
    {/if}
    {$Message}
{/if}

{if $Step == "mail"}
    <script src="js/editor.js"></script>
    {$Mailinfo}<br /><br />
    {$Showmail}<br /><br />
    <form method="post" action="newspaper.php?step=mail&amp;step3=add" name="earticle">
        {$Mailtype}: <select name="mail">
            <option value="M" {if $Mtype == "M"} selected {/if}>{$Anews}</option>
            <option value="N" {if $Mtype == "N"} selected {/if}>{$Anews2}</option>
            <option value="O" {if $Mtype == "O"} selected {/if}>{$Acourt}</option>
            <option value="R" {if $Mtype == "R"} selected {/if}>{$Aroyal}</option>
            {if $Rank == "Admin"}
                <option value="K" {if $Mtype == "K"} selected {/if}>{$Aking}</option>
            {/if}
            <option value="C" {if $Mtype == "C"} selected {/if}>{$Achronicle}</option>
            <option value="S" {if $Mtype == "S"} selected {/if}>{$Asensations}</option>
            <option value="H" {if $Mtype == "H"} selected {/if}>{$Ahumor}</option>
            <option value="I" {if $Mtype == "I"} selected {/if}>{$Ainter}</option>
            <option value="A" {if $Mtype == "A"} selected {/if}>{$Anews3}</option>
            <option value="P" {if $Mtype == "P"} selected {/if}>{$Apoetry}</option>
        </select><br />
        {$Ttitle} <input type="text" name="mtitle" value="{$Mtitle}" /><br />
	{$Tbody} <br />
	<input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'earticle', 'mbody')" />
	    <input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'earticle', 'mbody')" /> {html_options name=colors options=$Ocolors}<br />
        <textarea name="mbody" rows="13" cols="55">{$Mbody}</textarea><br />
        <input type="submit" value="{$Ashow}" name="show" /> <input type="submit" name="sendmail" value="{$Asend}" />
    </form>
    {$Message}
{/if}

{if (($Step != "" && $Step != "new") || ($Comments != "" && $Step == "") || $Step3 != "") && $Article == ""}
    <br /><br /><a href="newspaper.php">{$Aback}</a>
{/if}
