{$Bankinfo}<br /><br />

<a href="bank.php?action=astral&amp;type=p">{$Aastral}</a><br />
<a href="bank.php?action=astral&amp;type=c">{$Aastral2}</a><br />
{$Safebox}
<br />
<script src="js/bank.js"></script>
{if $Action != "astral"}

    <form method="post" action="bank.php?action=withdraw">
    {$Iwant} <input type="submit" value="{$Awithdraw}" /> <input type="text" value="{$Bank}" name="with" size="10" /> {$Goldcoins}
    </form><br />

    <form method="post" action="bank.php?action=deposit">
    {$Iwant} <input type="submit" value="{$Adeposit}" /> <input type="text" value="{$Gold}" name="dep" size="10" /> {$Goldcoins}
    </form><br />

    <form method="post" action="bank.php?action=donation">
    {$Iwant} <input type="submit" value="{$Agive}" /> {$Dplayer} <select name="player" onClick="showIdField(this.value, 1);">{html_options options=$Contacts}</select> <input type="text" id="pid1" name="pid" size="3" />
    <input type="text" value="{$Bank}" name="with" size="10" /> {$Goldcoins2} <input type="text" name="title" size="50" />
    </form><br />

    {if $Mithril > 0}
        <form method="post" action="bank.php?action=mithril">
        {$Iwant} <input type="submit" value="{$Agive}" /> {$Dplayer} <select name="player" onClick="showIdField(this.value, 2);">{html_options options=$Contacts}</select> <input type="tekst" id="pid2" name="pid" size="3" />
        <input type="text" name="mithril" size="10" value="{$Mithril}" /> {$Mamount} {$Ttitle} <input type="text" name="title" size="50" />
        </form><br />
    {/if}

    {if $Items == 1}
        <form method="post" action="bank.php?action=items">
        {$Iwant} <input type="submit" value="{$Agive}" /> {$Dplayer} <select name="player" onClick="showIdField(this.value, 3);">{html_options options=$Contacts}</select> <input type="tekst" id="pid3" name="pid" size="3" />
        <input type="text" id="amount1" name="amount" size="3" /> (<input type="checkbox" id="addall1" name="addall" value="Y" onClick="showAfield(1);" />{$Tall}) {$Iamount}
	{html_options name=item options=$Ioptions} {$Ttitle} <input type="text" name="title" size="50" />
        </form><br />
    {/if}

    {if $Potions == 1}
        <form method="post" action="bank.php?action=potions">
        {$Iwant} <input type="submit" value="{$Agive}" /> {$Dplayer} <select name="player" onClick="showIdField(this.value, 4);">{html_options options=$Contacts}</select> <input type="tekst" id="pid4" name="pid" size="3" />
        <input type="text" id="amount2" name="amount" size="3" /> (<input type="checkbox" id="addall2" name="addall" value="Y" onClick="showAfield(2);" />{$Tall}) {$Iamount}
	{html_options name=item options=$Poptions} {$Ttitle} <input type="text" name="title" size="50" />
        </form><br />
    {/if}

    {if $Herbs == 1}
        <form method="post" action="bank.php?action=herbs">
        {$Iwant} <input type="submit" value="{$Agive}" /> {$Dplayer} <select name="player" onClick="showIdField(this.value, 5);">{html_options options=$Contacts}</select> <input type="tekst" id="pid5" name="pid" size="3" />
	{html_options name=item options=$Hoptions} 
	{$Hamount} <input type="text" id="amount3" name="amount" size="5" /> (<input type="checkbox" id="addall3" name="addall" value="Y" onClick="showAfield(3);" />{$Tall}) {$Ttitle} <input type="text" name="title" size="50" />
        </form><br />
    {/if}

    {if $Minerals == 1}
        <form method="post" action="bank.php?action=minerals">
        {$Iwant} <input type="submit" value="{$Agive}" /> {$Dplayer} <select name="player" onClick="showIdField(this.value, 6);">{html_options options=$Contacts}</select> <input type="tekst" id="pid6" name="pid" size="3" /> 
	{html_options name=item options=$Moptions} 
	{$Hamount} <input type="text" id="amount4" name="amount" size="5" /> (<input type="checkbox" id="addall4" name="addall" value="Y" onClick="showAfield(4);" />{$Tall}) {$Ttitle} <input type="text" name="title" size="50" />
        </form><br />
    {/if}

    {if $Pets1 == 1}
        <form method="post" action="bank.php?action=pets">
	    {$Iwant} <input type="submit" value="{$Agive}" /> {$Dplayer} <select name="player" onClick="showIdField(this.value, 7);">{html_options options=$Contacts}</select> <input type="tekst" id="pid7" name="pid" size="3" /> 
	    {html_options name=item options=$Coptions} {$Ttitle} <input type="text" name="title" size="50" />
	</form><br />
    {/if}

    {if $Crime == 'Y'}
        <form method="post" action="bank.php?action=steal">
	    <br /><br /><input type="submit" value="{$Asteal}" /> {$Tcrime} <input type="text" size="5" name="tp" value="10" /> {$Ttp}
	</form>
    {/if}

{/if}

{if $Action == 'astral'}
    {if $Type == "p"}
        <form method="post" action="bank.php?action=astral&amp;type=p&amp;step=piece">
            {$Tsend} <select name="player" onClick="showIdField(this.value, 8);">{html_options options=$Contacts}</select> <input type="text" id="pid8" name="pid" size="5" /> {$Tpiece} <select name="name">
                {section name=giveastral loop=$Tcomponents}
                    <option value="{$smarty.section.giveastral.index}">{$Tcomponents[giveastral]}</option>
                {/section}
            </select><br />
            {$Tnumber} <input type="text" name="number" size="5" /><br />
            {$Tamount} <input type="text" name="amount" size="5" /><br />
            <input type="submit" value="{$Agive}" />
        </form><br />

	<form method="post" action="bank.php?action=astral&amp;type=p&amp;step=plan">
	    <input type="submit" value={$Asend} /> {$Taplan} <select name="name">
                {section name=giveastral2 loop=$Tcomponents}
                    <option value="{$smarty.section.giveastral2.index}">{$Tcomponents[giveastral2]}</option>
                {/section}
            </select> {$Taplayer} <select name="player" onClick="showIdField(this.value, 9);">{html_options options=$Contacts}</select> <input type="text" id="pid9" name="pid" size="5" />
	</form><br />

	<form method="post" action="bank.php?action=astral&amp;type=p&amp;step=all">
	    <input type="submit" value="{$Asend}" /> {$Tall} {$Taplayer} <select name="player" onClick="showIdField(this.value, 10);">{html_options options=$Contacts}</select> <input type="text" id="pid10" name="pid" size="5" />
	</form><br />

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
        <form method="post" action="bank.php?action=astral&amp;type=c&amp;step=component">
            {$Tsend} <select name="player" onClick="showIdField(this.value, 11);">{html_options options=$Contacts}</select> <input type="text" id="pid11" name="pid" size="5" /> {$Tcomponent3} <select name="name">
                {section name=giveastral2 loop=$Tcomponents2}
                    <option value="{$smarty.section.giveastral2.index}">{$Tcomponents2[giveastral2]}</option>
                {/section}
            </select><br />
            <input type="hidden" name="number" value="1" />
            {$Tamount} <input type="text" name="amount" size="5" /><br />
            <input type="submit" value="{$Agive}" />
        </form><br />

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
