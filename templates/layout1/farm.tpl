{if $Step == ""}
    {$Farminfo}<br /><br />
    - <a href="farm.php?step=plantation">{$Aplantation}</a><br />
    - <a href="farm.php?step=house">{$Ahouse}</a><br />
    - <a href="farm.php?step=herbsinfo">{$Aencyclopedia}</a>
{/if}

{if $Step == "herbsinfo"}
    <b>{$Herbsinfo}</b><br /><br />
    <ul>
        <li>{$Ilaniinfo}<br /><br /></li>
        <li>{$Illaniasinfo}<br /><br /></li>
        <li>{$Nutariinfo}<br /><br /></li>
        <li>{$Dynallcainfo}<br /><br /></li>
    </ul><br />
{/if}

{if $Step == "house"}
    {$Houseinfo}<br /><br />
    <form method="post" action="farm.php?step=house&amp;action=dry">
        <input type="submit" value="{$Adry}" /> <select name="herb">
            {section name=farm loop=$Herbsname}
                <option value="{$Herbsoption[farm]}">{$Herbsname[farm]} {$Tamount} {$Herbsamount[farm]}</option>
            {/section}
        </select> {$Tdry} <input type="text" name="amount" size="5" /> {$Tpack}.
    </form>
{/if}

{if $Step == "plantation"}
    {$Farminfo}<br /><br />
    {if $Lands != ""}
        <script src="js/farm.js"></script>
        <form method="post" action="farm.php?step=plantation&amp;action=upgrade&amp;buy=L">
            {$Ilands} <b>{$Lands}</b> 
	    {if $Aland != ''}
	        <input type="submit" value="{$Abuy}" /> <input type="text" name="lamount" id="lamount" size="5" value="0" onChange="checkcost('{$Lands}', 'l', '0')"/> {$Aland} <span id="lcost">0</span> {$Tmithcost}
	    {/if}
	</form>
        {$Ifreelands} <b>{$Freelands}</b><br /> 
	<form method="post" action="farm.php?step=plantation&amp;action=upgrade&amp;buy=G">
	    {$Iglass} <b>{$Glasshouse}</b>
	    {if $Aglass != ''}
	        <input type="submit" value="{$Abuy}" /> <input type="text" name="gamount" id="gamount" size="5" value="0" onChange="checkcost('{$Lands}', 'g', '{$Glasshouse}')"/> {$Aglass} <span id="gcost">0</span> {$Tgoldcost}
	    {/if}
	</form>
	<form method="post" action="farm.php?step=plantation&amp;action=upgrade&amp;buy=I">
	    {$Iirrigation} <b>{$Irrigation}</b>
	    {if $Airrigation != ''}
	        <input type="submit" value="{$Abuy}" /> <input type="text" name="iamount" id="iamount" size="5" value="0" onChange="checkcost('{$Lands}', 'i', '{$Irrigation}')"/> {$Airrigation} <span id="icost">0</span> {$Tgoldcost}
	    {/if}
	</form>
	<form method="post" action="farm.php?step=plantation&amp;action=upgrade&amp;buy=C">
	    {$Icreeper} <b>{$Creeper}</b>
	    {if $Acreeper != ''}
	        <input type="submit" value="{$Abuy}" /> <input type="text" name="camount" id="camount" size="5" value="0" onChange="checkcost('{$Lands}', 'c', '{$Creeper}')"/> {$Acreeper} <span id="ccost">0</span> {$Tgoldcost}
	    {/if}
	</form><br />
	{$Therbs}
	<ul>
	{section name=farm3 loop=$Herbs}
            <li><a href="farm.php?step=plantation&amp;action=chop&amp;id={$Herbs[farm3].id}">{$Herbs[farm3].name}</a> {$Tamount} {$Herbs[farm3].amount} {$Tage} {$Herbs[farm3].age} {$Herbs[farm3].stage}</li>
        {/section}
	</ul>
    {/if}
    {if $Action == ""}
        {if $Lands != ""}
            <a href="farm.php?step=plantation&amp;action=sow">{$Asow}</a><br />
	{else}
	    <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=L">{$Aupgrade}</a><br />
        {/if}
    {/if}
    {if $Action == "sow"}
        <form method="post" action="farm.php?step=plantation&amp;action=sow&amp;step2=next">
            <input type="submit" value="{$Asaw}" /> <input type="text" name="amount" size="5" /> {$Tlands} <select name="seeds">
                {section name=farm2 loop=$Seedsname}
                    <option value="{$Seedsoption[farm2]}">{$Seedsname[farm2]} {$Tamount} {$Seedsamount[farm2]}</option>
                {/section}
            </select>
        </form><br />
    {/if}
    {if $Action == "chop"}
        {if $Herbid != "0"}
            <form method="post" action="farm.php?step=plantation&amp;action=chop&amp;id={$Herbid}&amp;step2=next">
                <input type="submit" value="{$Agather}" /> {$Herbname} {$Froma} <input type="text" name="amount" size="5" /> {$Tlands3}
            </form>
        {/if}
    {/if}
{/if}

{if $Step != ""}
    <br /><br />(<a href="farm.php">{$Aback}</a>)
{/if}
