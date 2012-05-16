{if $Item == "0" && $Location == 'Altara'}
    {$Cityinfo} 
    <label for="citydesc" class="toggle">{$Anext}</label>
    <input id="citydesc" type="checkbox" class="toggle" checked=checked/>
    <span>{$Citylong}</span>
    <br /><br />
    
    <table align="center" width="90%">
        {section name=row1 loop=$Titles}
            {if $smarty.section.row1.index == 0 || $smarty.section.row1.index == 3 || $smarty.section.row1.index == 6}
                <tr>
            {/if}
                <td width="35%" valign="top"><b><u>{$Titles[row1]}</u></b><br />
                    {section name=locations loop=$Files[row1]}
                        <a href="{$Files[row1][locations]}">{$Names[row1][locations]}</a><br />
                    {/section}
                <br /></td>
            {if $smarty.section.row1.index == 2 || $smarty.section.row1.index == 5 || $smarty.section.row1.index == 7}
                </tr>
            {/if}
        {/section}
    </table>
{/if}

{if $Item == "1"}
    {if $Step == ""}
        {$Staffquest}<br />
        <a href="city.php?step=give">{$Sqbox1}</a><br />
        <a href="city.php?step=take">{$Sqbox2}</a>
    {/if}
    {if $Step != ""}
        {$Staffquest}<br />
        <a href="temple.php?temp=book&amp;book=2">{$Temple}</a>
    {/if}
{/if}

{if $Location == 'Ardulith' && $Step == ""}
    {$Cityinfo}<br /><br />
    
    <table align="center" width="90%">
        {section name=row1 loop=$Titles}
            {if $smarty.section.row1.index == 0 || $smarty.section.row1.index == 3 || $smarty.section.row1.index == 6}
                <tr>
            {/if}
                <td width="35%" valign="top"><b><u>{$Titles[row1]}</u></b><br />
                    {section name=locations loop=$Files[row1]}
                        <a href="{$Files[row1][locations]}">{$Names[row1][locations]}</a><br />
                    {/section}
                <br /></td>
            {if $smarty.section.row1.index == 2 || $smarty.section.row1.index == 5 || $smarty.section.row1.index == 7}
                </tr>
            {/if}
        {/section}
    </table>
{/if}

{if $Location == 'Ardulith' && $Step == "forest"}
    {$Message}
{/if}
