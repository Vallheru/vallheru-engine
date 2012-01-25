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
                1 {$Rank} <input type="text" name="rank1" /><br />
            	2 {$Rank} <input type="text" name="rank2" /><br />
            	3 {$Rank} <input type="text" name="rank3" /><br />
            	4 {$Rank} <input type="text" name="rank4" /><br />
            	5 {$Rank} <input type="text" name="rank5" /><br />
            	6 {$Rank} <input type="text" name="rank6" /><br />
            	7 {$Rank} <input type="text" name="rank7" /><br />
            	8 {$Rank} <input type="text" name="rank8" /><br />
            	9 {$Rank} <input type="text" name="rank9" /><br />
            	10 {$Rank} <input type="text" name="rank10" /><br />
            <input type="submit" value="{$Amake}" /></form><br />
        {/if}
        {if $Empty != "1"}
            {$Editranks}<br />
            <form method="post" action="tribeadmin.php?step2=rank&amp;step3=set&amp;step4=edit">
                1 {$Rank} <input type="text" name="rank1" value="{$Rank1}" /><br />
                2 {$Rank} <input type="text" name="rank2" value="{$Rank2}" /><br />
                3 {$Rank} <input type="text" name="rank3" value="{$Rank3}" /><br />
                4 {$Rank} <input type="text" name="rank4" value="{$Rank4}" /><br />
                5 {$Rank} <input type="text" name="rank5" value="{$Rank5}" /><br />
                6 {$Rank} <input type="text" name="rank6" value="{$Rank6}" /><br />
                7 {$Rank} <input type="text" name="rank7" value="{$Rank7}" /><br />
                8 {$Rank} <input type="text" name="rank8" value="{$Rank8}" /><br />
                9 {$Rank} <input type="text" name="rank9" value="{$Rank9}" /><br />
                10 {$Rank} <input type="text" name="rank10" value="{$Rank10}" /><br />
                <input type="submit" value="{$Asave}" /></form><br />
        {/if}
    {/if}
    {if $Step3 == "get"}
        <form method="post" action="tribeadmin.php?step2=rank&amp;step3=get&amp;step4=add">
            {$Setrank} <select name="rank">
            {section name=tribes3 loop=$Rank}
                <option value="{$Rank[tribes3]}">{$Rank[tribes3]}</option>
            {/section}
            </select> {$Rankplayer}: <input type="tekst" name="rid" /><br />
            <input type="submit" value="{$Aset}" />
    	</form><br />
    {/if}
{/if}
{if $Step2 == "permissions"}
    {if $Step3 == ""}
        {$Perminfo}<br />
        {if $Next == ""}
            <form method="post" action="tribeadmin.php?step2=permissions&amp;next=add">
                <select name="memid">
                {section name=tribes6 loop=$Memid}
                    <option value="{$Memid[tribes6]}">{$Members[tribes6]}</option>
                {/section}
                </select>
                <input type="submit" value="{$Anext}" />
            </form>
        {/if}
        {if $Next == "add"}
            <br />
            {$Tuser} <b>{$Tname}</b>
            <form method="post" action="tribeadmin.php?step2=permissions&amp;step3=add">
                <input type="hidden" name="memid" value="{$Memid2}" />
                {$Tperm1}: <select name="messages">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[0]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm2}: <select name="wait">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[1]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm3}: <select name="kick">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[2]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm4}: <select name="army">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[3]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm5}: <select name="attack">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[4]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm6}: <select name="loan">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[5]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm7}: <select name="armory">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[6]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm8}: <select name="warehouse">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[7]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm9}: <select name="bank">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[8]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm10}: <select name="herbs">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[9]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm11}: <select name="forum">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[10]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm12}: <select name="ranks">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[11]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm13}: <select name="mail">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[12]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm14}: <select name="info">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[13]}>{$Yes}</option>
                </select>
                <br />
                {$Tperm15}: <select name="astralvault">
                    <option value="0">{$No}</option>
                    <option value="1" {$Tselected[14]}>{$Yes}</option>
                </select>
                <br />
                <input type="submit" value="{$Asave}" />
            </form>
        {/if}
    {/if}
{/if}
{if $Step2 == "mail"}
    {if $Step3 == ""}
        <form method="post" action="tribeadmin.php?step2=mail&amp;step3=send">
            <table>
                <tr>
                    <td>{$Ttitle}:</td>
                    <td><input type="text" name="mtitle" size="55" /></td>
                </tr>
                <tr>
                    <td valign="top">{$Tbody}:</td>
                    <td><textarea name="body" rows="13" cols="55"></textarea></td>
                </tr>
                <tr>
                    <td align="center" colspan="2"><input type="submit" value="{$Asend}" /></td>
                </tr>
            </table>
        </form>
    {/if}
{/if}
{if $Step2 == "wojsko"}
    {if $Action == ""}
        {$Armyinfo}<br />
        <form method="post" action="tribeadmin.php?step2=wojsko&amp;action=kup">
            {$Howmanys} <input type="text" name="zolnierze" value="0" /><br />
            {$Howmanyf} <input type="text" name="forty" value="0" /><br />
            <input type="submit" value="{$Abuy}" />
	</form>
    {/if}
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
    <form method="post" action="tribeadmin.php?step2=messages&amp;action=tags">
        {$Tprefix} <input type="text" name="prefix" size="5" value="{$Prefix}" /><br />
	{$Tsuffix} <input type="text" name="suffix" size="5" value="{$Suffix}" /><br />
	{$Tinfo}<br />
	<input type="submit" value="{$Achange}" />
    </form><br /><br />
    <form method="post" action="tribeadmin.php?step2=messages&amp;action=edit"><table>
        <tr><td valign="top">{$Clandesc}:</td><td><textarea name="public_msg" rows="13" cols="50">{$Pubmessage}</textarea></td></tr>
        <tr><td valign="top">{$Msgtomem}:</td><td><textarea name="private_msg" rows="13" cols="50">{$Privmessage}</textarea></td></tr>
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
        {$Kickid} <input type="text" size="5" name="id" /> {$Fromclan}. <input type="submit" value="{$Akick}" />
    </form>
{/if}
{if $Step2 == "loan"}
    <form method="post" action="tribeadmin.php?step2=loan&amp;action=loan">
        {$Aloan2} <input type="text" size="5" name="amount" /> <select name="currency">
        <option value="credits">{$Goldcoins}</option>
        <option value="platinum">{$Mithcoins}</option></select>
        {$Playerid} <input type="text" size="5" name="id" />. <input type="submit" value="{$Aloan2}" />
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
