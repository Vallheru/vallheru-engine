{* Copyright notice: Code for smart display of odd/even number of monuments is based on http://smarty.php.net/contribs/examples/dynamic_table_columns/table.tpl.txt *}
{$Guildinfo}<br />
{* Each monument group is a separate table. *}
    <table align="center" width="100%">
    <tr>
    {section name=j loop=$Titles}
        {if $smarty.section.j.last && ($smarty.section.j.iteration % 2 == 1)}
{* If its last element, it has to be aligned to center and cover ALL columns. *}
        <td colspan="2" align="center">
        {else}
        <td align="center">
        {/if}
        
{* !!! Display each monument - start. *}
            <table class="td" cellpadding="0" cellspacing="4">
                <tr>
                    <th style="border-bottom: solid gray 1px;" align="center" colspan="2">{$Titles[j]}</th>
                </tr>
                <tr>
                    <td width="100"><b><u>{$Mname}</u></b></td>
                    <td width="100" align="center"><b><u>{$Descriptions[j]}</u></b></td>
                </tr>
                {section name=k loop=$Monuments[j]}
                    <tr>
                        <td align="left">
                            <a href="view.php?view={$Monuments[j][k].id}">{$Monuments[j][k].user}</a>&nbsp;({$Monuments[j][k].id})
                        </td>
                        <td align="center">{$Monuments[j][k].value}</td>
                    </tr>
                {/section}
            </table>
{* !!! Display each monument -stop. *}
        </td>        
{* should we go to the next row? *}
        {if ! $smarty.section.j.last}
            {if !($smarty.section.j.rownum % 2)}
                </tr><tr>
            {/if}
        {else}
            </tr>
        {/if}
    {/section}
</table>
{$Guildinfo2}<br/>
<ul>
    {section name=mon loop=$Monsters}
    <li>{$Monsters[mon]}</li>
    {/section}
</ul>
