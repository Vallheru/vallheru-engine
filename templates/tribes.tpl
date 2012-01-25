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
    </td></tr></table></center><br />
{/if}
