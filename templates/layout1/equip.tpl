<script src="js/equip.js"></script>

<u>{$Equipped}</u>:<br />
<form method="post" action="equip.php?repair">
{$Weapon}
{$Weapon2}
{$Arrows}
{$Helmet}
{$Armor}
{$Shield}
{$Legs}
{$Ring1}
{$Ring2}
{$Tool}
{$Pet}
{$Repairequip}
</form><br />

{if $Poison > "0"}
    <br /><form method="post" action="equip.php?poison={$Poison}&amp;step=poison"><input type="submit" value="{$Poisonit}" /> <select name="weapon">
    {section name=item loop=$Poisonitem}
        <option value="{$Poisonid[item]}">{$Poisonitem[item]} (+{$Poisonpower[item]}) ({$Tamount}: {$Poisonamount[item]})</option>
    {/section}
    </select>
    <input type="hidden" value="{$Poison}" name="poison" />
    </form><br />
{/if}

{if $Drinkfew > 0}
    <br /><form method="post" action="equip.php?drinkfew={$Drinkfew}&amp;step=drink">
        <input type="submit" value="{$Adrink}" /> {$Poname} <input type="text" size="5" value="{$Pamount}" name="amount" /> {$Tamount}
    </form></br >
{/if}

{if $Petname > 0 && $Step == ""}
    <br /><form method="post" action="equip.php?name={$Petname}&amp;step=rename">
        <input type="submit" value="{$Achange}" /> {$Tpname} <input type="text" name="cname" />
    </form><br />
{/if}

{foreach $Backpack as $Bitems}
    {if $Bitems.amount > 1}
        <div>
    	<label for="{$Bitems.name}" class="toggle">+{$Bitems.menu[0]}</label>
    	<input id="{$Bitems.name}" type="checkbox" class="toggle" {$Checked} />
    	<div><br />
	    <form method="POST" action="equip.php?sellchecked={$Bitems.selltype}" style="display: inline;" name="{$Bitems.name}">
                {foreach $Bitems.items as $key => $value}
	            {if $key > 0}
	                {$Ilevel} {$key}:<br />
	                {foreach $value as $weapon}
	                    {$weapon}
	                {/foreach}
		        <br />
	            {/if}
	        {/foreach}
	    	<input type="button" value="{$Checkall}" onClick="checkall(document.{$Bitems.name}.list);" />
	    	<input type="button" value="{$Uncheckall}" onClick="uncheckall(document.{$Bitems.name}.list);" />
	    	<input type="button" value="{$Changeselected}" onClick="changeselected(document.{$Bitems.name}.list);" /><br />
	    	<input type="submit" value="{$Bitems.sell}" />
            </form>
	    <form method="POST" action="equip.php?sprzedaj={$Bitems.type}" style="display: inline;">
	        <input type="submit" value="{$Bitems.menu[1]}" />
	    </form>
        </div><br />
    {/if}
{/foreach}

{if $Potions1 > "0"}
    <div>
    <label for="potions" class="toggle">+{$Potions2}</label>
    <input id="potions" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=P" style="display: inline;">
            {section name=item10 loop=$Pname1}
                <input type="checkbox" name="{$Potionid1[item10]}" /><b>({$Amount}: {$Pamount1[item10]} )</b> {$Pname1[item10]} ({$Peffect1[item10]}) {$Ppower1[item10]} {$Paction1[item10]}<br />
            {/section}
	    <input type="submit" value="{$Potionssell}" />
        </form>
	<form method="POST" action="equip.php?sellpotions" style="display: inline;">
	    <input type="submit" value="{$Sellallp}" />
	</form>
    </div><br />
{/if}

{if $Pets1 > "0"}
    <div>
    <label for="pets" class="toggle">+{$Tpets}</label>
    <input id="pets" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?releasefew" style="display: inline;" name="pets">
	    {foreach $Pets as $pet}
	        <input type="checkbox" name="{$pet.id}" id="list" /><b>{$Pname}</b> {$pet.name} <b>{$Pgender}</b> {$pet.gender} <b>{$Ppower}</b> {$pet.power} <b>{$Pdefense}</b> {$pet.defense} [ <a href="equip.php?name={$pet.id}">{$Pchname}</a> | <a href="equip.php?release={$pet.id}">{$Prelease}</a>{if $pet.heal != ''} | <a href="equip.php?heal={$pet.id}">{$Pheal} {$pet.heal}</a>{else} | <a href="equip.php?usep={$pet.id}">{$Puse}</a>{/if} ]<br />
            {/foreach}
	    <input type="button" value="{$Checkall}" onClick="checkall(document.pets.list);" />
	    <input type="button" value="{$Uncheckall}" onClick="uncheckall(document.pets.list);" />
	    <input type="button" value="{$Changeselected}" onClick="changeselected(document.pets.list);" /><br />
	    <input type="submit" value="{$Petrelease}" />
	</form>
    </div><br />
{/if}
