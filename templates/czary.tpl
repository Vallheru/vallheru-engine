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
    {foreach $Espells2 as $Espells3}
        <div>
        <label for="espells{$Espells3@index}" class="toggle">+{$Telement} {$Espells3@key}</label>
        <input id="espells{$Espells3@index}" type="checkbox" class="toggle" {$Checked} />
        <div><br />
            {foreach $Espells3 as $Spell}
                {$Spell.name} ({$Spell.effect}) [ <a href="czary.php?cast={$Spell.id}">{$Castthis}</a> ]<br />
	    {/foreach}
        </div><br />
    {/foreach}
{/if}

{if $Cast != ""}
    <form method="post" action="czary.php?cast={$Cast}&amp;step=items">
    <input type="submit" value="{$Cast2}" /> {$Spell23} {$Spellname} {$Ona} <select name="item">
    {foreach $Items as $item}
        <option value="{$item.id}">{$item.name} {$item.power}{$item.zr}{$item.szyb}{$item.wt} ({$Iamount}: {$item.amount})</option>
    {/foreach}
    </select>
    <input type="hidden" name="spell" value="{$Spellname}" /><br />
	</form>
{/if}

