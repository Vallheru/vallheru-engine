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
    {if $Showinfo != ''}
        {section name=ltribes loop=$Tribes}
         <li><a href="tribes.php?view=view&amp;id={$Tribes[ltribes].id}">{$Tribes[ltribes].name}</a>, {$Leaderid} <a href="view.php?view={$Tribes[ltribes].owner}">{$Tribes[ltribes].ownername}</a>, {$Ttype} {$Tribes[ltribes].level}.
        {/section}
    {/if}
    </ul>
{/if}

{if $View == "view"}
    {if $Step == ""}
        <h4 align="center">{$Yousee}: {$Name}</h4>
        {$Logo}
        {$Leader2} <a href="view.php?view={$Owner}">{$Ownername}</a><br /><br />
        {$Memamount}: {$Members}<br />
        <a href="tribes.php?view=view&amp;step=members&tid={$Tribeid}">{$Amembers}</a><br />
	{$Tlevel} {$Tlevel2}<br />
        {$Wins}
        {$Lost}
        {$Astral}
        {$WWW}<br />
        {$Pubmessage}
	{if $Ptribe == 0}
            <br /><br /><form method="post" action="tribes.php?join={$Tribeid}">
                {$Jointo} {$Name}<br />
            <input type="submit" value="{$Ajoin}" />
	{/if}
	{if $Trapsinfo != ""}
	    <br /><br />{$Trapsinfo}
	    <ul>
	        {foreach $Tactions as $Action => $Link}
		    <li><a href="tribes.php?view=view&amp;id={$Tribeid}&amp;step={$Action}">{$Link}</a></li>
		{/foreach}
	    </ul>
	{/if}
	{if $Asteal != ""}
	    <ul>
	    <li><a href="tribes.php?view=view&amp;id={$Tribeid}&amp;step=steal">{$Asteal}</a></li>
	    {if $Asabotage != ""}
	        <li><a href="tribes.php?view=view&amp;id={$Tribeid}&amp;step=sabotage">{$Asabotage}</a></li>
	    {/if}
	    <li><a href="tribes.php?view=view&amp;id={$Tribeid}&amp;step=espionage">{$Aespionage}</a></li>
	    </ul>
        {/if}
	<br /><br /><a href="tribes.php?view=all">{$Aback}</a>
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
    <form method="post" action="tribes.php?view=make&amp;step=make">
        {$Clanname}:<input type="text" name="name" /><br />
	{html_radios name=ctype options=$Coptions selected=5 separator='<br />'}
        <input type="submit" value="{$Amake}" />
    </form>
{/if}

{if $View == "my"}
    {include file="tribemenu.tpl"}
    <table width="98%" class="dark" cellpadding="0" cellspacing="0">
    <tr><td align="center"><b>{$Myclan}: {$Name}</b></td></tr>
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
	{if $Tlevel == 5}
            <li>{$Winamount}: {$Wins}</li>
            <li>{$Lostamount}: {$Lost}</li>
            <li>{$Tsoldiers}: {$Soldiers}</li>
            <li>{$Tforts}: {$Forts}</li>
	    <li>{$Amachine} ({$Percent}% {$Apercent})</li>
	{/if}
        {$WWW}
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
