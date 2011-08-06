{if $Temple != "book" && $Temple != "pantheon" && $Temple != "sluzba"}
    {if $Location == "Altara"}
        {$Templeinfo} {$God} {$Templeinfo2}<br /><br />
    {/if}
    {if $Location == "Ardulith"}
        {$Templeinfo}<br /><br />
    {/if}
{/if}

{if $Temple == ""}
    <ul>
    <li><a href="temple.php?temp=sluzba">{$Awork}</a></li>
    <li><a href="temple.php?temp=modlitwa">{$Apray}</a></li>
    <li><a href="temple.php?temp=book">{$Abook}</a></li>
    <li><a href="temple.php?temp=pantheon">{$Apantheon}</a></li>
    </ul>
{/if}

{if $Temple == "sluzba"}
    {$Templeinfow} {$God} {$Templeinfo2w}<br /><br />
    <form method="post" action="temple.php?temp=sluzba&amp;dalej=tak">
    {$Iwant} <input type="text" size="3" value="0" name="rep" /> {$Tamount}. <input type="submit" value="{$Awork}" />
    </form>
    <br />
    <a href="temple.php">{$Aback}</a>
{/if}

{if $Temple == "modlitwa"}
    <ul>
    <li>
        <form method="post" action="temple.php?temp=modlitwa&amp;modl=tak">
            <select name="praytype">
                <option value="1">{$Pray1} - (1 {$Energypts})</option>
                <option value="2">{$Pray2} - (2 {$Energypts})</option>
                <option value="4">{$Pray3} - (4 {$Energypts})</option>
                <option value="6">{$Pray4} - (6 {$Energypts})</option>
            </select><br />
            <select name="pray">
                {section name=temple loop=$Prays}
                    <option value="{$Praysval[temple]}">{$Blessfor}{$Prays[temple]} ({$Praycost[temple]} {$Pwpts})</option>
                {/section}
            </select><br />
            <input type="submit" value="{$Ayes}" />
        </form>
    </li>
    <li><a href="temple.php">{$Ano}</a></li></ul>
{/if}

{if $Temple == "book"}
    {if $Book == ""}
        {$Bookinfo}<br /><br /><br />
        <i>{$Booktext1}</i><br /><br />
        <a href="temple.php?temp=book&amp;book=1">{$Nextpage}</a>
    {/if}
    {if $Book != ""}
        <i>{$Booktext2}</i><br /><br />
        {if $Book2 != ""}
            <a href="temple.php?temp=book&amp;book={$Book2}">{$Nextpage}</a>
        {/if}
    {/if}
{/if}

{if $Temple == "pantheon"}
    {section name=pantheon loop=$Godnames}
        <b><u>{$Godnames[pantheon]}</u></b><br /><br />
        {$Goddesc[pantheon]}<br /><br />
        <hr /><br />
    {/section}
{/if}

{$Message}
