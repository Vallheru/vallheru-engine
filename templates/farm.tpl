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
    {if $Action == "dry"}
        <br />{$Message}
    {/if}
{/if}

{if $Step == "plantation"}
    {if $Action == ""}
        {$Farminfo}<br /><br />
        <a href="farm.php?step=plantation&amp;action=upgrade">{$Aupgrade}</a><br />
        {if $Lands != ""}
            <a href="farm.php?step=plantation&amp;action=sow">{$Asow}</a><br />
            <a href="farm.php?step=plantation&amp;action=chop">{$Achop}</a>
        {/if}
    {/if}
    {if $Action == "upgrade"}
        {$Upgradeinfo}<br /><br />
        <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=L">{$Buyland} {$Buylandcost} {$Tmith}</a><br />
        {if $Lands != ""}
            <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=G">{$Buyglass} {$Tgoldcost} {$Tgoldcoins}</a><br />
            <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=I">{$Buyirrigation} {$Tgoldcost} {$Tgoldcoins}</a><br />
            <a href="farm.php?step=plantation&amp;action=upgrade&amp;buy=C">{$Buycreeper} {$Tgoldcost} {$Tgoldcoins}</a><br />
        {/if}
        {$Message}
    {/if}
    {if $Action == "sow"}
        {$Sawinfo}<br /><br />
        {$Ilands} <b>{$Lands}</b><br />
        {$Ifreelands} <b>{$Freelands}</b><br />
        {$Iglass} <b>{$Glasshouse}</b><br />
        {$Iirrigation} <b>{$Irrigation}</b><br />
        {$Icreeper} <b>{$Creeper}</b><br /><br />
        <form method="post" action="farm.php?step=plantation&amp;action=sow&amp;step2=next">
            <input type="submit" value="{$Asaw}" /> <input type="text" name="amount" size="5" /> {$Tlands} <select name="seeds">
                {section name=farm2 loop=$Seedsname}
                    <option value="{$Seedsoption[farm2]}">{$Seedsname[farm2]} {$Tamount} {$Seedsamount[farm2]}</option>
                {/section}
            </select>
        </form><br />
        {$Message}
    {/if}
    {if $Action == "chop"}
        {$Chopinfo}<br /><br />
        {section name=farm3 loop=$Herbsname}
            - <a href="farm.php?step=plantation&amp;action=chop&amp;id={$Herbsid[farm3]}">{$Herbsname[farm3]}</a> {$Tamount} {$Herbsamount[farm3]} {$Tage} {$Herbsage[farm3]}<br />
        {/section}
        {if $Herbid != "0"}
            <br /><br /><br />
            <form method="post" action="farm.php?step=plantation&amp;action=chop&amp;id={$Herbid}&amp;step2=next">
                <input type="submit" value="{$Agather}" /> {$Herbname} {$Froma} <input type="text" name="amount" size="5" /> {$Tlands3}
            </form>
        {/if}
        {$Message}
    {/if}
{/if}

{if $Step != ""}
    <br /><br />(<a href="farm.php">{$Aback}</a>)
{/if}