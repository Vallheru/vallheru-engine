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
        {$Ilands} <b>{$Lands}</b> <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=L">{$Aland}</a><br />
        {$Ifreelands} <b>{$Freelands}</b><br />
        {$Iglass} <b>{$Glasshouse}</b> <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=G">{$Aglass}</a><br />
        {$Iirrigation} <b>{$Irrigation}</b> <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=I">{$Airrigation}</a><br />
        {$Icreeper} <b>{$Creeper}</b> <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=C">{$Acreeper}</a><br /><br />
	{$Therbs}
	<ul>
	{section name=farm3 loop=$Herbsname}
            <li><a href="farm.php?step=plantation&amp;action=chop&amp;id={$Herbsid[farm3]}">{$Herbsname[farm3]}</a> {$Tamount} {$Herbsamount[farm3]} {$Tage} {$Herbsage[farm3]}</li>
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
