<u>{$Usedspells}</u>:<br />
{$Battle}
{$Defence}

<br /><u>{$Spellbook}</u>:<br />
<b>-{$Bspells}:</b><br />
{foreach $Bspells2 as $Bspells3}
    <u>{$Telement} {$Bspells3@key}</u><br />
    {foreach $Bspells3 as $Spell}
        {$Spell.name} (+{$Spell.dmg} x {$Bdamage}) [ <a href="czary.php?naucz={$Spell.id}">{$Usethis}</a> ]<br />
    {/foreach}
    <br />
{/foreach}

<br /><b>-{$Dspells}:</b><br />
{foreach $Dspells2 as $Dspells3}
    <u>{$Telement} {$Dspells3@key}</u><br />
    {foreach $Dspells3 as $Spell}
        {$Spell.name} (+{$Spell.def} x {$Ddefense}) [ <a href="czary.php?naucz={$Spell.id}">{$Usethis}</a> ]<br />
    {/foreach}
    <br />
{/foreach}

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

