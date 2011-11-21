<a name="top"><u>{$Equipped}</u>:<br /></a>
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
{$Repairequip}
</form>
{if $Action != ""}
{$Action}  (<a href="equip.php">{$Refresh}</a>)<br />
{/if}

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
        <input type="submit" value="{$Adrink}" /> {$Pname} <input type="text" size="5" value="{$Pamount}" name="amount" /> {$Tamount}
    </form></br >
    {if $Step == "drink"}
       {$Effect}
    {/if}
{/if}

{if $Bweaponsamount > 1}
    <div align="center"><a href="#weapons">{$Bweaponsmenu[0]}</a></div>
{/if}
{if $Bstaffsamount > 1}
    <div align="center"><a href="#staffs">{$Bstaffsmenu[0]}</a></div>
{/if}
{if $Barrowsamount > 1}
    <div align="center"><a href="#arrows">{$Barrowsmenu[0]}</a></div>
{/if}
{if $Bhelmetsamount > 1}
    <div align="center"><a href="#helmets">{$Bhelmetsmenu[0]}</a></div>
{/if}
{if $Barmorsamount > 1}
    <div align="center"><a href="#armors">{$Barmorsmenu[0]}</a></div>
{/if}
{if $Bshieldsamount > 1}
    <div align="center"><a href="#shields">{$Bshieldsmenu[0]}</a></div>
{/if}
{if $Bcapesamount > 1}
    <div align="center"><a href="#capes">{$Bcapesmenu[0]}</a></div>
{/if}
{if $Blegsamount > 1}
    <div align="center"><a href="#legs">{$Blegsmenu[0]}</a></div>
{/if}
{if $Bringsamount > 1}
    <div align="center"><a href="#rings">{$Bringsmenu[0]}</a></div>
{/if}
{if $Blootsamount > 1}
    <div align="center"><a href="#loots">{$Blootsmenu[0]}</a></div>
{/if}
{if $Potions1 > "0"}
    <div align="center"><a href="#potions">{$Potions2}</a></div>
{/if}
{if $Bquestsamount > 1}
    <div align="center"><a href="#quests">{$Bquestsmenu[0]}</a></div>
{/if}

{if $Bweaponsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="weapons"><u>{$Bweaponsmenu[0]}:</u></a><br />
        {foreach $Bweapons as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Bweaponsmenu[1]}
	<input type="submit" value="{$Bweaponssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Bstaffsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="staffs"><u>{$Bstaffsmenu[0]}:</u></a><br />
        {foreach $Bstaffs as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Bstaffsmenu[1]}
	<input type="submit" value="{$Bstaffssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Barrowsamount > 1}
    <form method="POST" action="equip.php?sellchecked=A">
        <br /><a name="arrows"><u>{$Barrowsmenu[0]}:</u></a><br />
        {foreach $Barrows as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Barrowsmenu[1]}
	<input type="submit" value="{$Barrowssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Bhelmetsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="helmets"><u>{$Bhelmetsmenu[0]}:</u></a><br />
        {foreach $Bhelmets as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Bhelmetsmenu[1]}
  	<input type="submit" value="{$Bhelmetssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Barmorsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="armors"><u>{$Barmorsmenu[0]}:</u></a><br />
        {foreach $Barmors as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Barmorsmenu[1]}
    <input type="submit" value="{$Barmorssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Bshieldsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="shields"><u>{$Bshieldsmenu[0]}:</u></a><br />
        {foreach $Bshields as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Bshieldsmenu[1]}
        <input type="submit" value="{$Bshieldssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Bcapesamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="capes"><u>{$Bcapesmenu[0]}:</u></a><br />
        {foreach $Bcapes as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Bcapesmenu[1]}
    	<input type="submit" value="{$Bcapessell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Blegsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="legs"><u>{$Blegsmenu[0]}:</u></a><br />
        {foreach $Blegs as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Blegsmenu[1]}
    	<input type="submit" value="{$Blegssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Bringsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="rings"><u>{$Bringsmenu[0]}:</u></a><br />
        {foreach $Brings as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Bringsmenu[1]}
    	<input type="submit" value="{$Bringssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Blootsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="loots"><u>{$Blootsmenu[0]}:</u></a><br />
        {foreach $Bloots as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Blootsmenu[1]}
    	<input type="submit" value="{$Blootssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Potions1 > "0"}
    <br /><a name="potions"><u>{$Potions2}:</u></a><br />
    <form method="POST" action="equip.php?sellchecked=P">
        {section name=item10 loop=$Pname1}
            <input type="checkbox" name="{$Potionid1[item10]}" /><b>({$Amount}: {$Pamount1[item10]} )</b> {$Pname1[item10]} ({$Peffect1[item10]}) {$Ppower1[item10]} {$Paction1[item10]}<br />
        {/section}
        {$Sellallp}
	<input type="submit" value="{$Potionssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}

{if $Bquestsamount > 1}
    <form method="POST" action="equip.php?sellchecked=E">
        <br /><a name="quests"><u>{$Bquestsmenu[0]}:</u></a><br />
        {foreach $Bquests as $key => $value}
	    {if $key > 0}
	        {$Ilevel} {$key}:<br />
	        {foreach $value as $weapon}
	            {$weapon}
	        {/foreach}
		<br />
	    {/if}
	{/foreach}
	{$Bquestsmenu[1]}
    	<input type="submit" value="{$Bquestssell}" />
    </form>
    <a href="#top">{$Aback}</a><br />
{/if}
