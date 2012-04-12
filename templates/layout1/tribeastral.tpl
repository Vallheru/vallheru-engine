{include file="tribemenu.tpl"}

{if $Action == ""}
    {$Wareinfo}<br />
    <ul>
        <li><a href="tribeastral.php?action=view&amp;type=p">{$Ashow}</a></li>
        <li><a href="tribeastral.php?action=view&amp;type=c">{$Ashow2}</a></li>
        <li><a href="tribeastral.php?action=add">{$Aadd}</a></li>
        {if $Agive != ""}
            <li><a href="tribeastral.php?action=give">{$Agive}</a></li>
        {/if}
        {if $Safebox != ""}
            <li>{$Safebox}</li>
        {/if}
    </ul>
{/if}

{if $Action == 'view'}

    {$Message}
    {if $Type == "p"}
        <table align="center">
            <tr>
                <td><b><u>{$Tname}</u></b></td>
                {section name=astral loop=16 start=1}
                    <td align="center"><b><u>{$smarty.section.astral.index}</u></b></td>
                {/section}
            </tr>
            <tr>
                <td colspan="17">{$Tmaps}</td>
            </tr>
            {section name=astral2 loop=$Mapsname}
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;{$Mapsname[astral2]}</td>
                    {section name=astral3 loop=5 start=0}
                        <td align="center">{$Mapsamount[astral2][astral3]}</td>
                    {/section}
                    <td colspan="10"></td>
                </tr>
            {/section}
            <tr>
                <td colspan="17"><hr /></td>
            </tr>
            <tr>
                <td colspan="17">{$Tplans}</td>
            </tr>
            {section name=astral4 loop=$Plansname}
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;{$Plansname[astral4]}</td>
                    {section name=astral5 loop=15 start=0}
                        <td align="center">{$Plansamount[astral4][astral5]}</td>
                    {/section}
                </tr>
            {/section}
            <tr>
                <td colspan="17"><hr /></td>
            </tr>
            <tr>
                <td colspan="17">{$Trecipes}</td>
            </tr>
            {section name=astral6 loop=$Recipesname}
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;{$Recipesname[astral6]}</td>
                    {section name=astral7 loop=15 start=0}
                        <td align="center">{$Recipesamount[astral6][astral7]}</td>
                    {/section}
                </tr>
            {/section}
            <tr>
                <td colspan="17"><hr /></td>
            </tr>
            <tr>
                <td colspan="17">{$Tmaps2}</td>
            </tr>
            {section name=astral8 loop=$Mapsname2}
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;{$Mapsname2[astral8]}</td>
                    <td colspan="15">{$Mapsamount2[astral8]}</td>
                </tr>
            {/section}
            <tr>
                <td colspan="17"><hr /></td>
            </tr>
            <tr>
                <td colspan="17">{$Tplans2}</td>
            </tr>
            {section name=astral9 loop=$Plansname2}
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;{$Plansname2[astral9]}</td>
                    <td colspan="15">{$Plansamount2[astral9]}</td>
                </tr>
            {/section}
            <tr>
                <td colspan="17"><hr /></td>
            </tr>
            <tr>
                <td colspan="17">{$Trecipes2}</td>
            </tr>
            {section name=astral10 loop=$Recipesname2}
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;{$Recipesname2[astral10]}</td>
                    <td colspan="15">{$Recipesamount2[astral10]}</td>
                </tr>
            {/section}
        </table>
    {/if}
    {if $Type == "c"}
        <table align="center">
            <tr>
                <td><b><u>{$Tname}</u></b></td>
                <td></td>
            </tr>
            {section name=comp loop=$Tmagic}
                <tr>
                    <td colspan="2">{$Tmagic[comp]}</td>
                </tr>
                {section name=comp2 loop=$Tcomp[comp]}
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{$Tcomp[comp][comp2]}</td>
                        <td>{$Components[comp][comp2]}</td>
                    </tr>
                {/section}
                <tr>
                    <td colspan="2"><hr /></td>
                </tr>
            {/section}
        </table>
    {/if}
{/if}

{if $Action == 'add'}
    <form method="post" action="tribeastral.php?action=add&amp;step=piece">
        {$Addto} <select name="name">
            {section name=addastral loop=$Inames}
                <option value="{$smarty.section.addastral.index}">{$Inames[addastral]}</option>
            {/section}
        </select><br />
        {$Piecenumber} <input type="text" name="number" size="5" /><br />
        {$Astralamount} <input type="text" name="amount" size="5" /><br />
        <input type="submit" value="{$Aadd}" />
    </form><br />

    <form method="post" action="tribeastral.php?action=add&amp;step=plan">
	<input type="submit" value="{$Asend}" /> {$Taplan} <select name="name">
            {section name=giveastral2 loop=$Inames}
                <option value="{$smarty.section.giveastral2.index}">{$Inames[giveastral2]}</option>
            {/section}
        </select>
    </form><br />

    <form method="post" action="tribeastral.php?action=add&amp;step=all">
        <input type="submit" value="{$Tall}" />
    </form><br /><br />

    <form method="post" action="tribeastral.php?action=add&amp;step=component">
        {$Addto2} <select name="name">
            {section name=addastral2 loop=$Inames2}
                <option value="{$smarty.section.addastral2.index}">{$Inames2[addastral2]}</option>
            {/section}
        </select><br />
        <input type="hidden" name="number" value="1" />
        {$Astralamount} <input type="text" name="amount" size="5" /><br />
        <input type="submit" value="{$Aadd}" />
    </form><br />
    {$Message}
{/if}

{if $Action == 'give'}
    <form method="post" action="tribeastral.php?action=give&amp;step=piece">
        {$Addto} {html_options name=pid options=$Members} <select name="name">
            {section name=giveastral loop=$Inames}
                <option value="{$smarty.section.giveastral.index}">{$Inames[giveastral]}</option>
            {/section}
        </select><br />
        {$Piecenumber} <input type="text" name="number" size="5" /><br />
        {$Astralamount} <input type="text" name="amount" size="5" /><br />
        <input type="submit" value="{$Agive}" />
    </form><br />
    <form method="post" action="tribeastral.php?action=give&amp;step=plan">
	<input type="submit" value="{$Agive}" /> {$Taplan} {html_options name=pid options=$Members} <select name="name">
            {section name=giveastral2 loop=$Inames}
                <option value="{$smarty.section.giveastral2.index}">{$Inames[giveastral2]}</option>
            {/section}
        </select>
    </form><br />
    <form method="post" action="tribeastral.php?action=give&amp;step=all">
        {$Addto2} {html_options name=pid options=$Members} <select name="name">
            {section name=giveastral2 loop=$Inames}
                <option value="{$smarty.section.giveastral2.index}">{$Inames[giveastral2]}</option>
            {/section}
        </select><br />
        {$Astralamount} <input type="text" name="amount" size="5" /><br />
        <input type="submit" value="{$Agive}" />
    </form><br />
    <form method="post" action="tribeastral.php?action=give&amp;step=component">
        {$Addto3} {html_options name=pid options=$Members} <select name="name">
            {section name=giveastral3 loop=$Inames2}
                <option value="{$smarty.section.giveastral3.index}">{$Inames2[giveastral3]}</option>
            {/section}
        </select><br />
        <input type="hidden" name="number" value="1" />
        {$Astralamount} <input type="text" name="amount" size="5" /><br />
        <input type="submit" value="{$Agive}" />
    </form><br />
    {$Message}
{/if}
