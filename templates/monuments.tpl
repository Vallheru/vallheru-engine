{* Copyright notice: Code for smart display of odd/even number of monuments is based on http://smarty.php.net/contribs/examples/dynamic_table_columns/table.tpl.txt *}

{* Display "Table of Contents". "ToC" links to monument groups, and the titles of groups link back to "ToC" *}
<div style="margin: 0 auto; width: 50%">
    <a name="toc" />
    <ol type="I"> 
    {section name=toc loop=$Groups}
        <li><a href="#group{$smarty.section.toc.index}">{$Groups[toc]}</a></li>
    {/section}
    </ol>
</div>

{* For each group title... *}
{section name=i loop=$Titles}
    <hr />
    <h4 align="center"><a name="group{$smarty.section.i.index}" href="#toc">{$Groups[i]}</a></h4>
{* ...display it. Delete this part if you don't want links. *}

{* Each monument group is a separate table. *}
    <table align="center">
    <tr>
    {section name=j loop=$Titles[i]}
        {if $smarty.section.j.last && ($smarty.section.j.iteration % 2 == 1)}
{* If its last element, it has to be aligned to center and cover ALL columns. *}
        <td colspan="2" align="center">
        {else}
        <td align="center">
        {/if}
        
{* !!! Display each monument - start. *}
            <table class="td" cellpadding="0" cellspacing="4">
                <tr>
                    <th style="border-bottom: solid gray 1px;" align="center" colspan="2">{$Titles[i][j]}</th>
                </tr>
                <tr>
                    <td width="100" align="center"><b><u>{$Mname}</u></b></td>
                    <td width="100" align="center"><b><u>{$Descriptions[i][j]}</u></b></td>
                </tr>
                {section name=k loop=$Monuments[i][j]}
                    <tr>
                        <td align="left">
                            <a href="view.php?view={$Monuments[i][j][k].id}">{$Monuments[i][j][k].user}</a>&nbsp;({$Monuments[i][j][k].id})
                        </td>
                        <td align="right">{$Monuments[i][j][k].value}</td>
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
{/section}