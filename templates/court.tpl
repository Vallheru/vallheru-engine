{if $Step2 == "" && $Modify == ""}
    {$Cinfo}<br /><br />
    <table width="100%">
    <tr>
        <td valign="top">
            <u><b>{$Tlists}</b></u><br />
            <a href="court.php?list=judges">{$Ajudges}</a><br />
            <a href="court.php?list=aldermen">{$Aaldermen}</a><br />
            <a href="court.php?list=lawyers">{$Alawyers}</a><br />
            {if $Rank == "Admin" || $Rank == "Sędzia" || $Rank == "Kanclerz Sądu"}
                <a href="court.php?step=admin">{$Aadmin}</a><br />
            {/if}
        </td>
        <td valign="top">
            <u><b>{$Tinfo}</b></u><br />
            <a href="court.php?step=rules">{$Arules}</a><br />
            <a href="court.php?step=cases">{$Acases}</a><br />
            <a href="court.php?step=verdicts">{$Averdicts}</a><br />
        </td>
    </tr>
    </table>

    {if $List != ""}
        {if $Amount == "0"}
            <br /><br /><center>{$Nopeople} {$Trank} {$Ina} {$Gamename}</center>
        {/if}
        {if $Amount > "0"}
            <br /><br /><center>{$Listinfo} {$Trank} {$Ina} {$Gamename}<br />
            {section name="court" loop=$Jname}
                <a href="view.php?view={$Jid[court]}">{$Jname[court]}</a><br />
            {/section}
            </center>
        {/if}
    {/if}

    {if $Step == "rules" || $Step == "cases" || $Step == "verdicts"}
        {if $Amount == "0"}
            <br /><br /><center>{$Tnoitems} {$Noitems} {$Gamename}</center>
        {/if}
        {if $Amount > "0"}
            {$Listinfo} {$Itemsinfo} {$Gamename}. {$Listinfo2}<br />
            {section name="court2" loop=$Rtitle}
                <a href="court.php?step={$Step}&amp;step2={$Rid[court2]}">{$Rtitle[court2]}</a><br />
            {/section}
        {/if}
    {/if}

    {if $Step == "admin"}
        <br /><br />
        <a href="court.php?step=admin&amp;step2=addrule">{$Aaddrule}</a><br />
        <a href="court.php?step=admin&amp;step2=addcase">{$Aaddcase}</a><br />
        <a href="court.php?step=admin&amp;step2=addverdict">{$Aaddverd}</a><br />
    {/if}
{/if}

{if $Step2 == "comments"}
    {if $Amount == "0"}
        <center>{$Nocomments}</center>
    {/if}
    {if $Amount > "0"}
        {section name=library5 loop=$Tauthor}
            <b>{$Tauthor[library5]}</b> napisał(a): {if $Rank == "Admin" || $Rank == "Sędzia" || $Rank == "Kanclerz Sądu"} (<a href="court.php?step2=comments&amp;action=delete&amp;cid={$Cid[library5]}">{$Adelete}</a>) {/if}<br />
            {$Tbody[library5]}<br /><br />
        {/section}
    {/if}
    <br /><br />
    {if $Rank == "Admin" || $Rank == "Sędzia" || $Rank == "Prawnik"}
        <center>
        <form method="post" action="court.php?step2=comments&amp;action=add">
            {$Addcomment}:<textarea name="body" rows="20" cols="50"></textarea><br />
            <input type="hidden" name="tid" value="{$Text}" />
            <input type="submit" value="{$Aadd}" />
        </form></center>
    {/if}
{/if}

{if $Step2 != ""}
    {if $Step == "rules" || $Step == "cases" || $Step == "verdicts"}
        <center><b>{$Rtitle}</b> {$Mdate}: <b>{$Rdate}</b> {if $Rank == "Admin" || $Rank == "Sędzia" || $Rank == "Kanclerz Sądu"} <a href="court.php?modify={$Rid}">{$Achange}</a>{/if}</center><br />
        {$Rbody}<br /><br />
        {if $Step == "cases"}
            <a href="court.php?step2=comments&amp;text={$Rid}">{$Acomments}</a>
        {/if}
    {/if}
    
    {if $Step == 'admin'}
        <form method="post" action="court.php?step=admin&amp;step2={$Step2}&amp;action=add">
            {$Ttitle2}: <input type="text" name="ttitle" /><br />
            {$Tbody2}: <br /><textarea name="body" rows="30" cols="60"></textarea><br />
            <input type="submit" value="{$Aadd}" />
        </form>
    {/if}
<br /><br /><a href="court.php">{$Aback}</a>
{/if}

{if $Modify != ""}
<form method="post" action="court.php?modify={$Tid}&amp;action=change">
    {$Ttitle2}: <input type="text" name="ttitle" value="{$Ttitle}" /><br />
    {$Tbody2}: <br /><textarea name="body" rows="30" cols="60">{$Tbody}</textarea><br />
    <input type="hidden" name="tid" value="{$Tid}" />
    <input type="submit" value="{$Achange}" />
</form>
<br /><br /><a href="court.php">{$Aback}</a>
{/if}
