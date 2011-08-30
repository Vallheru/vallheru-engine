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

{if $Bweaponsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        {foreach $Bweapons as $weapon}
	    {$weapon}
	{/foreach}
	<input type="submit" value="{$Bweaponssell}" />
    </form>
{/if}

{if $Bstaffsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        {foreach $Bstaffs as $staff}
	    {$staff}
	{/foreach}
	<input type="submit" value="{$Bstaffssell}" />
    </form>
{/if}

{if $Arrows1 != ""}
    {$Arrows1}
    <form method="POST" action="equip.php?sellchecked=A">
        {section name=item3 loop=$Barrows}
            <input type="checkbox" name="{$Barrid[item3]}" /> {$Barrows[item3]} (+{$Barrpower[item3]}) ({$Barramount[item3]} {$Tarrows}) [ <a href="equip.php?equip={$Barrid[item3]}">{$Awear}</a> | <a href="equip.php?sell={$Barrid[item3]}">{$Asell}</a> {$Fora} {$Barrcost[item3]} {$Goldcoins} ]<br />
        {/section}
        (<a href="equip.php?sprzedaj=R">{$Sellall}</a>)<br />
	<input type="submit" value="{$Barrowssell}" />
    </form>
{/if}

{if $Bhelmetsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        {foreach $Bhelmets as $helmet}
	    {$helmet}
	{/foreach}
  	<input type="submit" value="{$Bhelmetssell}" />
    </form>
{/if}

{if $Barmorsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        {foreach $Barmors as $armor}
            {$armor}
	{/foreach}
    <input type="submit" value="{$Barmorssell}" />
    </form>
{/if}

{if $Bshieldsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        {foreach $Bshields as $shield}
	    {$shield}
	{/foreach}
        <input type="submit" value="{$Bshieldssell}" />
    </form>
{/if}

{if $Bcapesamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        {foreach $Bcapes as $cape}
	    {$cape}
	{/foreach}
    	<input type="submit" value="{$Bcapessell}" />
    </form>
{/if}

{if $Blegsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        {foreach $Blegs as $leg}
	    {$leg}
	{/foreach}
    	<input type="submit" value="{$Blegssell}" />
    </form>
{/if}

{if $Rings1 != ""}
    {$Rings1}
    <form method="POST" action="equip.php?sellchecked=E">
        {section name=rings loop=$Bringid}
            <input type="checkbox" name="{$Bringid[rings]}" /><b>({$Amount}: {$Bringamount[rings]})</b> {$Brings[rings]} (+{$Bringpower[rings]}) [ <a href="equip.php?equip={$Bringid[rings]}">{$Awear}</a> | <a href="equip.php?sell={$Bringid[rings]}">{$Asell}</a> {$Fora} {$Bringcost[rings]} {$Goldcoins} ]<br />
        {/section}
        (<a href="equip.php?sprzedaj=I">{$Sellallrings}</a>)<br />
        <input type="submit" value="{$Ringssell}" />
    </form>
{/if}

{if $Potions1 > "0"}
    <br /><u>{$Potions2}</u>:<br />
    <form method="POST" action="equip.php?sellchecked=P">
        {section name=item10 loop=$Pname1}
            <input type="checkbox" name="{$Potionid1[item10]}" /><b>({$Amount}: {$Pamount1[item10]} )</b> {$Pname1[item10]} ({$Peffect1[item10]}) {$Ppower1[item10]} {$Paction1[item10]}<br />
        {/section}
        {$Sellallp}
	<input type="submit" value="{$Potionssell}" />
    </form>
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

{if $Drinkfew > 0}
    <form method="post" action="equip.php?drinkfew={$Drinkfew}&amp;step=drink">
        <input type="submit" value="{$Adrink}" /> {$Pname} <input type="text" size="5" value="{$Pamount}" name="amount" /> {$Tamount}
    </form>
    {if $Step == "drink"}
       {$Effect}
    {/if}
{/if}
