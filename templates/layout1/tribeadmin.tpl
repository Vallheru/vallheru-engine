{include file="tribemenu.tpl"}

{if $Step2 == ""}
    {$Panelinfo}
    <ul>
        {foreach $Links as $value}
            <li><a href="tribeadmin.php?step2={$value@key}">{$value}</a></li>
        {/foreach}
    </ul>
{/if}
{if $Step2 == "asks"}
    {$Asksinfo}<br />
    <form method="post" action="tribeadmin.php?step2=asks&amp;step3=next">
        <table align="center" width="90%">
	    <tr>
	        <th>{$Taccept}</th>
	        <th>{$Trefuse}</th>
	        <th>{$Tplayer}</th>
	        <th>{$Tamount}</th>
	        <th>{$Titem}</th>
	    </tr>
	    {section name=asks loop=$Cplayers}
	        <tr>
	            <td><input type="radio" name="{$Cids[asks]}" value="{$Taccept}" checked="checked" /></td>
		    <td><input type="radio" name="{$Cids[asks]}" value="{$Trefuse}" /></td>
		    <td>{$Cplayers[asks]}</td>
		    <td>{$Camounts[asks]}</td>
		    <td>{$Citems[asks]}</td>
		<tr>
	    {/section}
	</table>
	<input type="submit" value="{$Anext}" />
    </form>
{/if}
{if $Step2 == "rank"}
    {if $Step3 == ""}
        {$Ranksinfo}<br />
        <ul>
            <li><a href="tribeadmin.php?step2=rank&amp;step3=set">{$Aaddranks}</a></li>
            {$Menu}
        </ul>
    {/if}
    {if $Step3 == "set"}
        {if $Empty == "1"}
            {$Noranks2}<br />
            <form method="post" action="tribeadmin.php?step2=rank&amp;step3=set&amp;step4=add">
	        {for $rank=1 to 10}
		    {$rank} {$Rank} <input type="text" name="rank{$rank}" /><br />
		{/for}
            <input type="submit" value="{$Amake}" /></form><br />
        {/if}
        {if $Empty != "1"}
            {$Editranks}<br />
            <form method="post" action="tribeadmin.php?step2=rank&amp;step3=set&amp;step4=edit">
	        {foreach $Ranks as $rank}
		    {$rank@iteration} {$Rank} <input type="text" name="rank{$rank@iteration}" value="{$rank}" /><br />
		{/foreach}
                <input type="submit" value="{$Asave}" /></form><br />
        {/if}
    {/if}
    {if $Step3 == "get"}
        <form method="post" action="tribeadmin.php?step2=rank&amp;step3=get&amp;step4=add">
            {$Setrank} <select name="rank">
            {section name=tribes3 loop=$Rank}
                <option value="{$Rank[tribes3]}">{$Rank[tribes3]}</option>
            {/section}
            </select> {$Rankplayer}: {html_options name=rid options=$Members}<br />
            <input type="submit" value="{$Aset}" />
    	</form><br />
    {/if}
{/if}
{if $Step2 == "permissions"}
    {if $Step3 == ""}
        {$Perminfo}<br />
        {if $Next == ""}
            <form method="post" action="tribeadmin.php?step2=permissions&amp;next=add">
		{html_options name=memid options=$Members}
                <input type="submit" value="{$Anext}" />
            </form>
        {/if}
        {if $Next == "add"}
            <br />
            {$Tuser} <b>{$Tname}</b>
            <form method="post" action="tribeadmin.php?step2=permissions&amp;step3=add">
                <input type="hidden" name="memid" value="{$Memid2}" />
		{foreach $Tperms as $Perm}
		    {$Perm}: <select name="{$Tnames[$Perm@index]}">
		        <option value="0">{$No}</option>
			<option value="1"{$Tselected[$Perm@index]}>{$Yes}</option>
		    </select><br />
		{/foreach}
                <input type="submit" value="{$Asave}" />
            </form>
        {/if}
    {/if}
{/if}
{if $Step2 == "mail"}
    <script src="js/editor.js"></script>
    {if $Step3 == ""}
        <form method="post" action="tribeadmin.php?step2=mail&amp;step3=send" name="mail">
            <table>
                <tr>
                    <td>{$Ttitle}:</td>
                    <td><input type="text" name="mtitle" size="55" /></td>
                </tr>
                <tr>
                    <td valign="top">{$Tbody}:</td>
                    <td><input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'mail', 'body')" />
		    <input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'mail', 'body')" />
		    <input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'mail', 'body')" />
		    <input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'mail', 'body')" />
		    <input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'mail', 'body')" />
		    <input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'mail', 'body')" />
		    <input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'mail', 'body')" /> {html_options name=colors options=$Ocolors}<br /><textarea name="body" rows="13" cols="55"></textarea></td>
                </tr>
                <tr>
                    <td align="center" colspan="2"><input type="submit" value="{$Asend}" /></td>
                </tr>
            </table>
        </form>
    {/if}
{/if}
{if $Step2 == "traps"}
    {$Trapsinfo}<br /><br/>
    <form method="post" action="tribeadmin.php?step2=traps&amp;action=buy">
        <input type="submit" value="{$Abuy}" /> <input type="text" name="traps" value="0" size="3" /> {$Ttraps} <input type="text" name="agents" value="0" size="3" /> {$Tagents}
    </form>
{/if}
{if $Step2 == "wojsko"}
    {$Armyinfo}<br />
    <form method="post" action="tribeadmin.php?step2=wojsko&amp;action=kup">
        {$Howmanys} <input type="text" name="zolnierze" value="0" /><br />
        {$Howmanyf} <input type="text" name="forty" value="0" /><br />
        <input type="submit" value="{$Abuy}" />
    </form>
{/if}
{if $Step2 == "nowy"}
    {if $New == "1"}
        {$Waitlist}<br />
        <table>
            <tr>
                <td width="100"><b><u>{$Tid}</u></b></td>
                <td width="100"><b><u>{$Taccept}</u></b></td>
                <td width="100"><b><u>{$Tdrop}</u></b></td>
            </tr>
            {section name=tribes4 loop=$Link}
                {$Link[tribes4]}
            {/section}
        </table>
    {/if}
{/if}
{if $Step2 == "walka"}
    {if $Step3 == ""}
        {$Selecttribe}:<br />
        {section name=tribes5 loop=$Link}
            {$Link[tribes5]}
        {/section}
        {if $Attack != ""}
            <br />{$Youwant}<b>{$Entribe}</b>?
            <form method="post" action="tribeadmin.php?step2=walka&amp;step3=confirm&amp;atak={$Attack}">
                <input type="submit" value="{$Ayes}" />
            </form>
        {/if}
    {/if}
    {if $Step3 == "confirm"}
        {if $Victory == "My"}
            {$Youwin} {$Ename} {$Youwin2} {$Myname}... {$Youwin3} <b>{$Gold}</b> {$Goldcoins} <b>{$Mithril}</b> {$Mithrilcoins}{$Astral}
        {/if}
        {if $Victory == "Enemy"}
            {$Ewin} {$Ename} {$Ewin2} {$Ename} {$Ewin3} <b>{$Gold}</b> {$Goldcoins} <b>{$Mithril}</b> {$Mithrilcoins}{$Astral}
        {/if}
    {/if}
{/if}
{if $Step2 == "messages"}
    <script src="js/editor.js"></script>
    <form method="post" action="tribeadmin.php?step2=messages&amp;action=tags">
        {$Tprefix} <input type="text" name="prefix" size="5" value="{$Prefix}" /><br />
	{$Tsuffix} <input type="text" name="suffix" size="5" value="{$Suffix}" /><br />
	{$Tinfo}<br />
	<input type="submit" value="{$Achange}" />
    </form><br /><br />
    <form method="post" action="tribeadmin.php?step2=messages&amp;action=edit" name="messages"><table>
        <tr><td valign="top">{$Clandesc}:</td><td><input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'messages', 'public_msg')" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'messages', 'public_msg')" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'messages', 'public_msg')" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'messages', 'public_msg')" />
	<input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'messages', 'public_msg')" />
	<input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'messages', 'public_msg')" />
	<input id="color" type="button" value="{$Acolor}" onClick="formatText(this.id, 'messages', 'public_msg')" /> {html_options name=colors options=$Ocolors}<br />
	<textarea name="public_msg" rows="13" cols="50">{$Pubmessage}</textarea></td></tr>
        <tr><td valign="top">{$Msgtomem}:</td><td><input id="bold" type="button" value="{$Abold}" onClick="formatText(this.id, 'messages', 'private_msg')" />
	<input id="italic" type="button" value="{$Aitalic}" onClick="formatText(this.id, 'messages', 'private_msg')" />
	<input id="underline" type="button" value="{$Aunderline}" onClick="formatText(this.id, 'messages', 'private_msg')" />
	<input id="emote" type="button" value="{$Aemote}" onClick="formatText(this.id, 'messages', 'private_msg')" />
	<input id="center" type="button" value="{$Acenter}" onClick="formatText(this.id, 'messages', 'private_msg')" />
	<input id="quote" type="button" value="{$Aquote}" onClick="formatText(this.id, 'messages', 'private_msg')" /><br />
	<textarea name="private_msg" rows="13" cols="50">{$Privmessage}</textarea></td></tr>
        <tr><td colspan="2" align="center"><input type="submit" value="{$Achange}" /></td></tr>
        </table></form>
    <form method="post" action="tribeadmin.php?step2=messages&amp;action=www">
        {$Clansite}: <input type="text" name="www" value="{$WWW}" /><br />
        <input type="submit" value="{$Aset}" /></form><br />
        {$Logo}
        {$Logoinfo}<br />
        {if $Change == "Y"}
            <form action="tribeadmin.php?step2=messages&amp;step4=usun" method="post">
            <input type="hidden" name="av" value="{$Logo}" />
            <input type="submit" value="{$Adelete}" />
            </form>
        {/if}
    <form enctype="multipart/form-data" action="tribeadmin.php?step2=messages&amp;step4=dodaj" method="post">
        <input type="hidden" name="MAX_FILE_SIZE" value="10240" />
        {$Logoname}: <input name="plik" type="file" /><br />
        <input type="submit" value="{$Asend}" /></form>
{/if}
{if $Step2 == "kick"}
    <form method="post" action="tribeadmin.php?step2=kick&amp;action=kick">
        {$Kickid} {html_options name=id options=$Members} {$Fromclan}. <input type="submit" value="{$Akick}" />
    </form>
{/if}
{if $Step2 == "loan"}
    <form method="post" action="tribeadmin.php?step2=loan&amp;action=loan">
        <input type="submit" value="{$Aloan2}" /> <input type="text" size="5" name="amount" /> <select name="currency">
        <option value="credits">{$Goldcoins}</option>
        <option value="platinum">{$Mithcoins}</option></select>
        {$Playerid} {html_options name=id options=$Members}.
    </form>
{/if}
{if $Step2 == "te"}
    {$Miscinfo}
    <ul>
        <li><a href="tribeadmin.php?step2=te&amp;step3=hospass">{$Afreeheal}</a>
    </ul>
    {if $Hospass1 == "1"}
        {$Youbuy}
        <a href="tribeadmin.php">... {$Aback}</a>
    {/if}
{/if}
{if $Step2 == "upgrade"}
    {$Upgradeinfo}<br />
    <form method="post" action="tribeadmin.php?step2=upgrade&amp;upgrade">
        {html_radios name=update options=$Uoptions separator='<br />'}
	<input type="submit" value="{$Aupgrade}" />
    </form>
{/if}
