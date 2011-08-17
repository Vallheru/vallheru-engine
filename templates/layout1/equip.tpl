<u>{$Equipped}</u>:<br />
{$Weapon}
{$Arrows}
{$Helmet}
{$Armor}
{$Shield}
{$Legs}
{$Ring1}
{$Ring2}
{$Repairequip}
{$Hide}

{section name=item1 loop=$Bweapons}
    {$Bweapons[item1]}
{/section}

{section name=item2 loop=$Bstaffs}
    {$Bstaffs[item2]}
{/section}

{if $Arrows1 != ""}
    {$Arrows1}
    {section name=item3 loop=$Barrows}
        {$Barrows[item3]} (+{$Barrpower[item3]}) ({$Barramount[item3]} {$Tarrows}) [ <a href="equip.php?equip={$Barrid[item3]}">{$Awear}</a> | <a href="equip.php?sell={$Barrid[item3]}">{$Asell}</a> {$Fora} {$Barrcost[item3]} {$Goldcoins} ]<br />
    {/section}
    (<a href="equip.php?sprzedaj=R">{$Sellall}</a>)<br /><br />
{/if}

{section name=item4 loop=$Bhelmets}
    {$Bhelmets[item4]}
{/section}

{section name=item5 loop=$Barmors}
    {$Barmors[item5]}
{/section}

{section name=item6 loop=$Bshields}
    {$Bshields[item6]}
{/section}

{section name=item7 loop=$Bcapes}
    {$Bcapes[item7]}
{/section}

{section name=item8 loop=$Blegs}
    {$Blegs[item8]}
{/section}

{if $Rings1 != ""}
    {$Rings1}
    {section name=rings loop=$Bringid}
        <b>({$Amount}: {$Bringamount[rings]})</b> {$Brings[rings]} (+{$Bringpower[rings]}) [ <a href="equip.php?equip={$Bringid[rings]}">{$Awear}</a> | <a href="equip.php?sell={$Bringid[rings]}">{$Asell}</a> {$Fora} {$Bringcost[rings]} {$Goldcoins} ]<br />
    {/section}
    (<a href="equip.php?sprzedaj=I">{$Sellallrings}</a>)<br /><br />
{/if}

{if $Potions1 > "0"}
    <br /><u>{$Potions2}</u>:<br />
    {section name=item10 loop=$Pname1}
        <b>({$Amount}: {$Pamount1[item10]} )</b> {$Pname1[item10]} ({$Peffect1[item10]}) {$Ppower1[item10]} {$Paction1[item10]}<br />
    {/section}
    {$Sellallp}
{/if}

{$Action}

{if $Poison > "0"}
    <form method="post" action="equip.php?poison={$Poison}&amp;step=poison"><input type="submit" value="{$Poisonit}" /> <select name="weapon">
    {section name=item loop=$Poisonitem}
        <option value="{$Poisonid[item]}">{$Poisonitem[item]} ({$Tamount}: {$Poisonamount[item]})</option>
    {/section}
    </select>
    <input type="hidden" value="{$Poison}" name="poison" />
    </form>
    {if $Step == "poison"}
        {$Item}
    {/if}
{/if}
