{if $View == "" && $Join == ""}
    {$Claninfo}
    <ul>
    {$Mytribe}
    {$Make}
    <li><a href="tribes.php?view=all">{$Ashow}</a>
    </ul>
{/if}

{if $View == "all"}
    {$Showinfo}
    {$Text}
    {section name=ltribes loop=$Tribes}
        <li><a href="tribes.php?view=view&amp;id={$Tribes[ltribes].id}">{$Tribes[ltribes].name}</a>, {$Leaderid} <a href="view.php?view={$Tribes[ltribes].owner}">{$Tribes[ltribes].owner}</a>.
    {/section}
    </ul>
{/if}

{if $View == "view"}
    {if $Step == ""}
        <ul>
        <li>{$Yousee}: {$Name}<br /><br />
        {$Logo}
        {$Leader2} <a href="view.php?view={$Owner}">{$Owner}</a><br /><br />
        {$Memamount}: {$Members}<br />
        <a href="tribes.php?view=view&amp;step=members&tid={$Tribeid}">{$Amembers}</a><br />
        {$Winamount}: {$Wins}<br />
        {$Lostamount}: {$Lost}<br />
        {$Astral}
        {$WWW}<br />
        {$Pubmessage}<br /><br />
        <form method="post" action="tribes.php?join={$Tribeid}">
        {$Jointo} {$Name}<br />
        <input type="submit" value="{$Ajoin}" />
        </form>
	{if $Asteal != ""}
	    <br /><br /><a href="tribes.php?view=view&amp;id={$Tribeid}&amp;step=steal">{$Asteal}</a><br />
	    {if $Asabotage != ""}
	        <a href="tribes.php?view=view&amp;id={$Tribeid}&amp;step=sabotage">{$Asabotage}</a><br />
	    {/if}
	    <a href="tribes.php?view=view&amp;id={$Tribeid}&amp;step=espionage">{$Aespionage}</a>
        {/if}
    {/if}
    {if $Step == "members"}
        {$Memberlist} {$Name}<br />
        {section name=tribes1 loop=$Link}
            {$Link[tribes1]}
        {/section}
    {/if}
{/if}

{if $Join != ""}
    {if $Check == "1"}
        {$Youwait}<br />
        <a href="tribes.php?join={$Tribeid}&amp;change={$Playerid}">{$Ayes}</a><br />
        <a href="tribes.php">{$Ano}</a><br />
    {/if}
{/if}

{if $View == "make"}
    <table><form method="post" action="tribes.php?view=make&amp;step=make">
    <tr><td>{$Clanname}:</td><td><input type="text" name="name" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Amake}" /></td></tr>
    </form></table>
{/if}

{if $View == "my"}
    {include file="tribemenu.tpl"}
	
	<table width="98%" class="td" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="border-bottom: solid black 1px;"><b>{$Myclan}: {$Name}</b></td></tr>
    <tr><td width="100%" valign="top">
    {if $Step == ""}
        {$Logo}
        {$Welcome}
        <ul>
        <li>{$Clanname}: {$Name}</li>
        <li>{$Memamount}: {$Members}</li>
        <li>{$Leader}: <a href="view.php?view={$Ownerid}">{$Owner}</a></li>
        <li>{$Goldcoins}: {$Gold}</li>
        <li>{$Mithcoins}: {$Mithril}</li>
        <li>{$Winamount}: {$Wins}</li>
        <li>{$Lostamount}: {$Lost}</li>
        <li>{$Tsoldiers}: {$Soldiers}</li>
        <li>{$Tforts}: {$Forts}</li>
        {$WWW}
        <li>{$Amachine} ({$Percent}% {$Apercent})</li>
        </ul>
        {$Privmessage}
    {/if}
    {if $Step == "astral"}
        {$Aused} {$Eused} {$Aenergy}<br />
        {$Atoday} {$Edirected} {$Aenergy} {$Amax} 2000 {$Aenergy}<br />
        <form method="post" action="tribes.php?view=my&amp;step=astral&amp;step2=add">
            <input type="submit" value="{$Adirect}" /> {$Aform} <input type="text" name="amount" size="5" value="0" /> {$Aenergy}
        </form>
        {$Message}
        <br /><br />
    {/if}
    {if $Step == "donate"}
        {$Doninfo}
        <form method="post" action="tribes.php?view=my&amp;step=donate&amp;step2=donate">
        {$Adonate} <input type="text" size="5" name="amount" value="0" /> <select name="type">
        <option value="credits">{$Goldcoins}</option>
        <option value="platinum">{$Mithcoins}</option>
        </select> {$Toclan} <input type="submit" value="{$Adonate}" />
        </form>
        {$Message}
    {/if}
    {if $Step == "zielnik"}
        {if $Step2 == "" && $Step3 == "" && $Step4 == "" && $Give == ""}
            {$Herbsinfo}<br />
            <table>
                <tr>
                    {section name=herbstribe2 loop=$Ttable}
                        {$Ttable[herbstribe2]}
                    {/section}
                </tr>
                <tr>
                    {section name=herbstribe3 loop=$Tamount}
                        <td align="center">{$Tamount[herbstribe3]}</td>
                    {/section}
                </tr>
            </table>
            {$Whatyou}<br />
            <ul>
            <li><a href="tribes.php?view=my&amp;step=zielnik&amp;step2=daj">{$Agiveto}</a></li>
            </ul>
        {/if}
        {if $Give != ""}
            {if $Step4 == ""}
                <form method=post action="tribes.php?view=my&amp;step=zielnik&amp;daj={$Itemid}&amp;step4=add">
                    {$Giveplayer} <input type="text" name="did" size="5" /><br />
                    <input type="text" name="ilosc" size="5" /> {$Nameherb} {$Tamount} {$Tamount2} {$Hamount2}.<br />
                    <input type="hidden" name="min" value="{$Nameherb}" />
                    <input type="submit" value="{$Agive}" /><br />
                </form>
            {/if}
            {$Message}
        {/if}
        {if $Step2 == "daj"}
            {$Addherb}<br /><br />
            <form method="post" action="tribes.php?view=my&amp;step=zielnik&amp;step2=daj&amp;step3=add"><table>
            <tr><td>{$Herb}:</td><td><select name="mineral">
                {section name=herbstribe loop=$Herbname}
                    <option value="{$Sqlname[herbstribe]}">{$Herbname[herbstribe]}</option>
                {/section}
            </select></td></tr>
            <tr><td>{$Hamount}:</td><td><input type="text" name="ilosc" /></td></tr>
            <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
            </table></form>
            {$Message}
        {/if}
    {/if}
    {if $Step == "skarbiec"}
        {if $Step2 == "" && $Step3 == "" && $Step4 == "" && $Give == ""}
            {$Mininfo}<br />
            <table>
            {$Menu}
                {section name=tribemin loop=$Ttable}
                    {$Ttable[tribemin]}
                {/section}
            </table>
            {$Whatyou}<br />
            <ul>
            <li><a href="tribes.php?view=my&amp;step=skarbiec&amp;step2=daj">{$Agiveto}</a></li>
            </ul>
        {/if}
        {if $Give != ""}
            <form method="post" action="tribes.php?view=my&amp;step=skarbiec&amp;daj={$Itemid}&amp;step4=add">
                {$Giveplayer} <input type="text" name="did" size="5" /><br />
                <input type="text" name="ilosc" size="7" /> {$Namemin} {$Tamount} {$Tamount2} {$Mamount2}.<br />
                <input type="hidden" name="min" value="{$Namemin}" />
                <input type="submit" value="{$Agive}" /><br />
            </form>
            {$Message}
        {/if}
        {if $Step2 == "daj"}
            {$Addmin}<br /><br />
            <form method="post" action="tribes.php?view=my&amp;step=skarbiec&amp;step2=daj&amp;step3=add"><table>
            <tr><td>{$Mineral}:</td><td><select name="mineral">
                {section name=tribemin2 loop=$Minname}
                    <option value="{$Minsql[tribemin2]}">{$Minname[tribemin2]}</option>
                {/section}
            </select></td></tr>
            <tr><td>{$Mamount}:</td><td><input type="text" name="ilosc" /></td></tr>
            <tr><td colspan="2" align="center"><input type="submit" value="{$Aadd}" /></td></tr>
            </table></form>
            {$Message}
        {/if}
    {/if}
    {if $Step == "members"}
        {section name=tribes2 loop=$Link}
            {$Link[tribes2]}
        {/section}
    {/if}
    {if $Step == "quit"}
        {if $Owner == "1"}
            {$Qleader}<br />
            <a href="tribes.php?view=my&amp;step=quit&amp;dalej=tak">{$Ayes}</a><br />
            <a href="tribes.php?view=my">{$Ano}</a><br />
        {/if}
        {if $Owner != "1"}
            {$Qmember}<br />
            <a href="tribes.php?view=my&amp;step=quit&amp;dalej=tak">{$Ayes}</a><br />
            <a href="tribes.php?view=my">{$Ano}</a><br />
        {/if}
    {/if}
    {if $Step == "owner"}
        {if $Step2 == ""}
            Witaj w panelu przywódcy klanu. Co chcesz zrobić?
            <ul>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=permissions">{$Aperm}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=rank">{$Arank}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=mail">{$Amail2}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=messages">{$Adesc}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=nowy">{$Awaiting}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=kick">{$Akick}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=wojsko">{$Aarmy}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=walka">{$Aattack2}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=loan">{$Aloan}</a></li>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=te">{$Amisc}</a></li>
            </ul>
        {/if}
        {if $Step2 == "rank"}
            {if $Step3 == ""}
                {$Ranksinfo}<br />
                <ul>
                <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=rank&amp;step3=set">{$Aaddranks}</a></li>
                {$Menu}
                </ul>
            {/if}
            {if $Step3 == "set"}
                {if $Empty == "1"}
                    {$Noranks2}<br />
                    <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=rank&amp;step3=set&amp;step4=add">
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
                    <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=rank&amp;step3=set&amp;step4=edit">
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
                {$Message}
            {/if}
            {if $Step3 == "get"}
                <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=rank&amp;step3=get&amp;step4=add">
                {$Setrank} <select name="rank">
                {section name=tribes3 loop=$Rank}
                    <option value="{$Rank[tribes3]}">{$Rank[tribes3]}</option>
                {/section}
                </select> {$Rankplayer}: <input type="tekst" name="rid" /><br />
                <input type="submit" value="{$Aset}" /></form><br />
                {$Message}
            {/if}
        {/if}
        {if $Step2 == "permissions"}
            {if $Step3 == ""}
                {$Perminfo}<br />
                {if $Next == ""}
                    <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=permissions&amp;next=add">
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
                    <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=permissions&amp;step3=add">
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
            {$Message}
        {/if}
        {if $Step2 == "mail"}
            {if $Step3 == ""}
                <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=mail&amp;step3=send">
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
            {if $Step3 == "send"}
                {$Message}
            {/if}
        {/if}
        {if $Step2 == "wojsko"}
            {if $Action == ""}
                {$Armyinfo}<br />
                <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=wojsko&amp;action=kup">
                {$Howmanys} <input type="text" name="zolnierze" value="0" /><br />
                {$Howmanyf} <input type="text" name="forty" value="0" /><br />
                <input type="submit" value="{$Abuy}" /></form>
            {/if}
            {$Message}
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
            {$Message}
        {/if}
        {if $Step2 == "walka"}
            {if $Step3 == ""}
                {$Selecttribe}:<br />
                {section name=tribes5 loop=$Link}
                    {$Link[tribes5]}
                {/section}
                {if $Attack != ""}
                    <br />{$Youwant}<b>{$Entribe}</b>?
                    <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=walka&amp;step3=confirm&amp;atak={$Attack}">
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
            <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=messages&amp;action=edit"><table>
            <tr><td valign="top">{$Clandesc}:</td><td><textarea name="public_msg" rows="13" cols="50">{$Pubmessage}</textarea></td></tr>
            <tr><td valign="top">{$Msgtomem}:</td><td><textarea name="private_msg" rows="13" cols="50">{$Privmessage}</textarea></td></tr>
            <tr><td colspan="2" align="center"><input type="submit" value="{$Achange}" /></td></tr>
            </table></form>
            <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=messages&amp;action=www">
            {$Clansite}: <input type="text" name="www" value="{$WWW}" /><br />
            <input type="submit" value="{$Aset}" /></form><br />
            {$Logo}
            {$Logoinfo}<br />
            {if $Change == "Y"}
                <form action="tribes.php?view=my&amp;step=owner&amp;step2=messages&amp;step4=usun" method="post">
                <input type="hidden" name="av" value="{$Logo}" />
                <input type="submit" value="{$Adelete}" />
                </form>
            {/if}
            <form enctype="multipart/form-data" action="tribes.php?view=my&amp;step=owner&amp;step2=messages&amp;step4=dodaj" method="post">
            <input type="hidden" name="MAX_FILE_SIZE" value="10240" />
            {$Logoname}: <input name="plik" type="file" /><br />
            <input type="submit" value="{$Asend}" /></form>
            {$Message}
        {/if}
        {if $Step2 == "kick"}
            <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=kick&amp;action=kick">
            {$Kickid} <input type="text" size="5" name="id" /> {$Fromclan}. <input type="submit" value="{$Akick}" />
            </form>
            {$Message}
        {/if}
        {if $Step2 == "loan"}
            <form method="post" action="tribes.php?view=my&amp;step=owner&amp;step2=loan&amp;action=loan">
            {$Aloan2} <input type="text" size="5" name="amount" /> <select name="currency">
            <option value="credits">{$Goldcoins}</option>
            <option value="platinum">{$Mithcoins}</option></select>
            {$Playerid} <input type="text" size="5" name="id" />. <input type="submit" value="{$Aloan2}" />
            </form>
            {$Message}
        {/if}
        {if $Step2 == "te"}
            {$Message1}
            {$Miscinfo}
            <ul>
            <li><a href="tribes.php?view=my&amp;step=owner&amp;step2=te&amp;step3=hospass">{$Afreeheal}</a>
            </ul>
            {if $Hospass1 == "1"}
                {$Youbuy}
                <a href="tribes.php?view=my&amp;step=owner">... {$Aback}</a>
            {/if}
        {/if}
        {$Message2}
    {/if}
    </td></tr></table></center><br />
{/if}
