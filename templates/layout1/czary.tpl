<u>{$Usedspells}</u>:<br />
{$Battle}
{$Defence}

<br /><u>{$Spellbook}</u>:<br />
<b>-{$Bspells}:</b><br />
{section name=spell1 loop=$Bname}
    {$Bname[spell1]} (+{$Bpower[spell1]} x {$Bdamage}) [ <a href="czary.php?naucz={$Bid[spell1]}">{$Usethis}</a> ]<br />
{/section}

<br /><b>-{$Dspells}:</b><br />
{section name=spell2 loop=$Dname}
    {$Dname[spell2]} (+{$Dpower[spell2]} x {$Ddefense}) [ <a href="czary.php?naucz={$Did[spell2]}">{$Usethis}</a> ]<br />
{/section}

<br /><b>-{$Espells}:</b><br />
{section name=spell3 loop=$Uname}
    {$Uname[spell3]} ({$Ueffect[spell3]}) [ <a href="czary.php?cast={$Uid[spell3]}">{$Castthis}</a> ]<br />
{/section}

{if $Cast != ""}
    <form method="post" action="czary.php?cast={$Cast}&amp;step=items">
    <input type="submit" value="{$Cast2}" /> {$Spell} {$Spellname} {$Ona} <select name="item">
    {section name=spell4 loop=$Itemname}
        <option value="{$Itemid[spell4]}">{$Itemname[spell4]} ({$Iamount}: {$Itemamount[spell4]})</option>
    {/section}
    </select>
    <input type="hidden" name="spell" value="{$Spellname}" /><br />
	</form>
{/if}

