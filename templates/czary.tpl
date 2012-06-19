<u>{$Usedspells}</u>:<br />
{$Battle}
{$Defence}

<br /><u>{$Spellbook}</u>:<br />
{if $Bamount == 0 && $Damount == 0 && $Eamount == 0}
    {$Nospells}
{/if}

{if $Bamount > 0}
    <b>-{$Bspells}:</b><br />
    {foreach $Bspells2 as $Bspells3}
        <div>
        <label for="spells{$Bspells3@index}" class="toggle">+{$Telement} {$Bspells3@key}</label>
        <input id="spells{$Bspells3@index}" type="checkbox" class="toggle" {$Checked} />
        <div><br />
            {foreach $Bspells3 as $Spell}
                {$Spell.name} (+{$Spell.dmg} x {$Bdamage}) [ <a href="czary.php?naucz={$Spell.id}">{$Usethis}</a> ]<br />
            {/foreach}
	</div><br />
    {/foreach}
{/if}

{if $Damount > 0}
    <br /><b>-{$Dspells}:</b><br />
    {foreach $Dspells2 as $Dspells3}
        <div>
        <label for="dspells{$Dspells3@index}" class="toggle">+{$Telement} {$Dspells3@key}</label>
        <input id="dspells{$Dspells3@index}" type="checkbox" class="toggle" {$Checked} />
        <div><br />
            {foreach $Dspells3 as $Spell}
                {$Spell.name} (+{$Spell.def} x {$Ddefense}) [ <a href="czary.php?naucz={$Spell.id}">{$Usethis}</a> ]<br />
            {/foreach}
        </div><br />
    {/foreach}
{/if}

{if $Eamount > 0}
    <br /><b>-{$Espells}:</b><br />
    {section name=spell3 loop=$Uname}
        {$Uname[spell3]} ({$Ueffect[spell3]}) [ <a href="czary.php?cast={$Uid[spell3]}">{$Castthis}</a> ]<br />
    {/section}
{/if}

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

