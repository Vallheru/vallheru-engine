{if $View == ""}
    {if $Location == "Altara"}
        {$Marketinfo}<br /><br />
    {/if}

    <a href="market.php?view=myoferts">{$Amyoferts}</a><br /><br />

    {section name=market loop=$Markets}
        {$Markets[market]}<br />
        <ul>
            <li><a href="{$Filesname[market]}.php?view=market&amp;lista=id">{$Ashow}</a></li>
            <li><a href="{$Filesname[market]}.php?view=add">{$Aadd}</a></li>
            <li><a href="{$Filesname[market]}.php?view=del">{$Adelete}</a></li>
	    {if $smarty.section.market.index != 7}
                {if $smarty.section.market.index != "1"}
                    <li><a href="{$Filesname[market]}.php?view=all">{$Alist}</a></li>
                {else}
                    <li><a href="{$Filesname[market]}.php?view=all&amp;limit=0">{$Alist}</a></li>
                {/if}
	    {/if}
        </ul>
    {/section}
{/if}

{if $View == "myoferts"}
    {if $Type == ""}
        {section name=myoferts loop=$Markets}
            <a href="market.php?view=myoferts&amp;type={$Filesname[myoferts]}">{$Markets[myoferts]} {$Oamount[myoferts]}</a><br />
        {/section}
        <br />
        <a href="market.php?view=myoferts&amp;deleteall=no">{$Deleteall}</a><br />
        {if $Actiondelete == "no"}
            <form method="post" action="market.php?view=myoferts&amp;deleteall=yes">
                <br />{$Youwant2}<br />
                <input type="submit" value="{$Ayes}" />
            </form>
        {/if}
        {if $Actiondelete == "yes"}
            <br />{$Message}<br />
        {/if}
    {/if}
    {if $Type != ""}
        {if $Change == "" && $Delete == "" && $Add == ""}
            <table align="center" width="100%" class="dark">
                <tr>
                    {section name=thead loop=$Ttable}
                        <td align="center"><b><u>{$Ttable[thead]}</u></b></td>
                    {/section}
                </tr>
                {section name=oferts loop=$Tvalues}
                    <tr>
                        {section name=ofert loop=$Tvalues[oferts]}
                            <td align="center">{$Tvalues[oferts][ofert]}</td>
                        {/section}
                        <td><a href="market.php?view=myoferts&amp;type={$Type}&amp;delete={$Tid[oferts]}">{$Adelete}</a><br />
                            <a href="market.php?view=myoferts&amp;type={$Type}&amp;change={$Tid[oferts]}">{$Achange}</a><br />
                            <a href="market.php?view=myoferts&amp;type={$Type}&amp;add={$Tid[oferts]}">{$Aadd}</a>
                        </td>
                    </tr>
                {/section}
            </table>
        {else}
            {$Message}<br /><br />
        {/if}
        {if $Delete != "" && $Message == ""}
	    <form method="post" action="market.php?view=myoferts&amp;type={$Type}&amp;delete={$Delete}&amp;confirm=yes">
                <input type="submit" value="{$Aremove}" /> <input type="text" name="amount" value="{$Oamount}" size="5" /> {$Tamount} <b>{$Oname}</b> {$Tmarket}
            </form>
        {/if}
        {if $Add != "" && $Message == ""}
            <form method="post" action="market.php?view=myoferts&amp;type={$Type}&amp;add={$Add}&amp;confirm=yes">
                <input type="submit" value="{$Aadd}" /> {$Toofert}{$Oname} <input type="text" name="amount" value="0" size="5" />{$Tamount2} <input type="checkbox" value="Y" name="addall" />{$Taddall}. {$Tyouhave}.<br />
            </form>
        {/if}
        {if $Change != "" && $Message == ""}
            <form method="post" action="market.php?view=myoferts&amp;type={$Type}&amp;change={$Change}&amp;confirm=yes">
                <input type="submit" value="{$Achange}" /> {$Tofert}{$Oname} {$Ona} <input type="text" name="amount" value="0" size="5" /> {$Goldcoins}
            </form>
        {/if}
    {/if}
    <br /><br /><a href="market.php">{$Aback}</a>
{/if}
