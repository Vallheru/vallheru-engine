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
{$Repairequip}
</form><br />
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

{if $Petname > 0 && $Step == ""}
    <br /><form method="post" action="equip.php?name={$Petname}&amp;step=rename">
        <input type="submit" value="{$Achange}" /> {$Tpname} <input type="text" name="cname" />
    </form><br />
{/if}


{if $Bweaponsamount > 1}
    <div>
    <label for="weapons" class="toggle">+{$Bweaponsmenu[0]}</label>
    <input id="weapons" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Bstaffsamount > 1}
    <div>
    <label for="staffs" class="toggle">+{$Bstaffsmenu[0]}</label>
    <input id="staffs" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Barrowsamount > 1}
    <div>
    <label for="arrows" class="toggle">+{$Barrowsmenu[0]}</label>
    <input id="arrows" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=A">
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
    </div><br />
{/if}

{if $Bhelmetsamount > 1}
    <div>
    <label for="helmets" class="toggle">+{$Bhelmetsmenu[0]}</label>
    <input id="helmets" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Barmorsamount > 1}
    <div>
    <label for="armors" class="toggle">+{$Barmorsmenu[0]}</label>
    <input id="armors" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Bshieldsamount > 1}
    <div>
    <label for="shields" class="toggle">+{$Bshieldsmenu[0]}</label>
    <input id="shields" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Bcapesamount > 1}
    <div>
    <label for="capes" class="toggle">+{$Bcapesmenu[0]}</label>
    <input id="capes" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Blegsamount > 1}
    <div>
    <label for="legs" class="toggle">+{$Blegsmenu[0]}</label>
    <input id="legs" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Bringsamount > 1}
    <div>
    <label for="rings" class="toggle">+{$Bringsmenu[0]}</label>
    <input id="rings" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Btoolsamount > 1}
    <div>
    <label for="tools" class="toggle">+{$Btoolsmenu[0]}</label>
    <input id="tools" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
            {foreach $Btools as $key => $value}
	        {if $key > 0}
	            {$Ilevel} {$key}:<br />
	            {foreach $value as $weapon}
	                {$weapon}
	            {/foreach}
		    <br />
	        {/if}
	    {/foreach}
	    {$Btoolsmenu[1]}
    	    <input type="submit" value="{$Btoolssell}" />
        </form>
    </div><br />
{/if}

{if $Bplansamount > 1}
    <div>
    <label for="plans" class="toggle">+{$Bplansmenu[0]}</label>
    <input id="plans" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
            {foreach $Bplans as $key => $value}
	        {if $key > 0}
	            {$Ilevel} {$key}:<br />
	            {foreach $value as $weapon}
	                {$weapon}
	            {/foreach}
		    <br />
	        {/if}
	    {/foreach}
	    {$Bplansmenu[1]}
    	    <input type="submit" value="{$Bplanssell}" />
        </form>
    </div><br />
{/if}

{if $Blootsamount > 1}
    <div>
    <label for="loots" class="toggle">+{$Blootsmenu[0]}</label>
    <input id="loots" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}

{if $Potions1 > "0"}
    <div>
    <label for="potions" class="toggle">+{$Potions2}</label>
    <input id="potions" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=P">
            {section name=item10 loop=$Pname1}
                <input type="checkbox" name="{$Potionid1[item10]}" /><b>({$Amount}: {$Pamount1[item10]} )</b> {$Pname1[item10]} ({$Peffect1[item10]}) {$Ppower1[item10]} {$Paction1[item10]}<br />
            {/section}
            {$Sellallp}
	    <input type="submit" value="{$Potionssell}" />
        </form>
    </div><br />
{/if}

{if $Pets1 > "0"}
    <div>
    <label for="pets" class="toggle">+{$Tpets}</label>
    <input id="pets" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        {section name=pets loop=$Pets}
            <b>{$Pname}</b> {$Pets[pets].name} <b>{$Pgender}</b> {$Pets[pets].gender} <b>{$Ppower}</b> {$Pets[pets].power} <b>{$Pdefense}</b> {$Pets[pets].defense} [ <a href="equip.php?name={$Pets[pets].id}">{$Pchname}</a> | <a href="equip.php?release={$Pets[pets].id}">{$Prelease}</a> ]<br />
        {/section}
    </div><br />
{/if}

{if $Bquestsamount > 1}
    <div>
    <label for="quests" class="toggle">+{$Bquestsmenu[0]}</label>
    <input id="quests" type="checkbox" class="toggle" {$Checked} />
    <div><br />
        <form method="POST" action="equip.php?sellchecked=E">
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
    </div><br />
{/if}
