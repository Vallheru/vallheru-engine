{if $Corepass != "Y"}
    {$Corepassinfo}
    {if $Gold >= "500"}
        <br />{$Havemoney}
        <ul>
        <li><a href="core.php?answer=yes">{$Ayes}</a></li>
        <li><a href="city.php">{$Ano}</a></li>
        </ul>
    {/if}
{/if}

{if $View == "" && $Corepass == "Y"}
    {$Coremain}
    <ul>
    <li><a href="core.php?view=mycores">{$Amycore}</a></li>
    <li><a href="core.php?view=arena">{$Aarena}</a></li>
    <li><a href="core.php?view=train">{$Atrain}</a></li>
    <li><a href="core.php?view=breed">{$Abreed}</a></li>
    <li><a href="core.php?view=explore">{$Asearch}</a></li>
    <li><a href="core.php?view=library">{$Alibrary}</a>
    <li><a href="core.php?view=best">{$Amonuments}</a></li>
    </ul>
{/if}

{if $View == "best"}
    <table align="center">
    <tr>
    <td width="200">
        <table class="td" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center" colspan="2"><b><u>{$Tarens[0]}</u></b></td></tr>
        <tr><td width="100" align="center"><b><u>{$Cname}</u></b></td><td width="100" align="center"><b><u>{$Twins}</u></b></td></tr>
        {section name=list loop=$Top1}
            {$Top1[list]}
        {/section}
        </table>
    </td>
    <td width="200">
        <table class="td" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center" colspan="2"><b><u>{$Tarens[1]}</u></b></td></tr>
        <tr><td width="100" align="center"><b><u>{$Cname}</u></b></td><td width="100" align="center"><b><u>{$Twins}</u></b></td></tr>
        {section name=list2 loop=$Top2}
            {$Top2[list2]}
        {/section}
        </table>
    </td>
    </tr>
    <tr>
    <td width="200">
        <table class="td" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center" colspan="2"><b><u>{$Tarens[2]}</u></b></td></tr>
        <tr><td width="100" align="center"><b><u>{$Cname}</u></b></td><td width="100" align="center"><b><u>{$Twins}</u></b></td></tr>
        {section name=list3 loop=$Top3}
            {$Top3[list3]}
        {/section}
        </table>
    </td>
    <td width="200">
        <table class="td" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center" colspan="2"><b><u>{$Tarens[3]}</u></b></td></tr>
        <tr><td width="100" align="center"><b><u>{$Cname}</u></b></td><td width="100" align="center"><b><u>{$Twins}</u></b></td></tr>
        {section name=list4 loop=$Top4}
            {$Top4[list4]}
        {/section}
        </table>
    </td>
    </tr>
    <tr>
    <td width="200">
        <table class="td" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center" colspan="2"><b><u>{$Tarens[4]}</u></b></td></tr>
        <tr><td width="100" align="center"><b><u>{$Cname}</u></b></td><td width="100" align="center"><b><u>{$Twins}</u></b></td></tr>
        {section name=list5 loop=$Top5}
            {$Top5[list5]}
        {/section}
        </table>
    </td>
    <td width="200">
        <table class="td" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center" colspan="2"><b><u>{$Tarens[5]}</u></b></td></tr>
        <tr><td width="100" align="center"><b><u>{$Cname}</u></b></td><td width="100" align="center"><b><u>{$Twins}</u></b></td></tr>
        {section name=list6 loop=$Top6}
            {$Top6[list6]}
        {/section}
        </table>
    </td>
    </tr>
    </table>
{/if}

{if $View == "breed"}
    {if $Step == ""}
        {$Breedinfo} <b>{$Trains}</b> {$Trainpts}<br /><br />
        <form method="post" action="core.php?view=breed&amp;step=breed">
            <input type="submit" value="{$Abreed}" /> {$Tcores}: <select name="coremale">
            {section name=core8 loop=$Coremale}
                <option value="{$Coremaleid[core8]}">{$Coremale[core8]}</option>
            {/section}
            </select> {$Tand} <select name="corefemale">
            {section name=core9 loop=$Corefemale}
                <option value="{$Corefemaleid[core9]}">{$Corefemale[core9]}</option>
            {/section}
            </select>
        </form>
    {/if}
    {if $Step == "breed"}
        {if $Next == ""}
            {$Thiscost} {$Cost} {$Mithcoins}<br />
            {$Doyou}
            <form method="post" action="core.php?view=breed&amp;step=breed&amp;next=breed">
                <input type="hidden" name="coremale" value="{$Maleid}" />
                <input type="hidden" name="corefemale" value="{$Femaleid}" />
                <input type="submit" value="{$Ayes}" />
            </form>
            - <a href="core.php?view=breed">{$Ano}</a><br /><br />
        {/if}
        {if $Next == "breed"}
            {$Message}
        {/if}
    {/if}
{/if}

{if $View == "mycores"}
    {if $Coreid == "" && $Name2 == "" && $Release == ""}
        {$Mycoresinfo}
        <ul>
        {section name=core loop=$Name}
            <li><a href="core.php?view=mycores&amp;id={$Coreid1[core]}">{$Corename[core]}{$Name[core]} ({$Tgender2[core]}) {$Tcore}</a> {$Activ[core]}</li>
        {/section}
        </ul>
    {/if}
    {if $Coreid > "0"}
        <center><br /><table class="td" width="300" cellpadding="0" cellspacing="0">
        <tr><td align="center" style="border-bottom: solid black 1px;" colspan="2"><b>{$Showcore}</b></td></tr>
        <tr><td width="150" valign="top">+ <b>{$Mainstats}</b>
        <ul>
        <li>{$Cid}: {$Id}</li>
        <li>{$Tcname}: <b>{$Corename}</b></li>
        <li>{$Cname}: {$Name}</li>
        <li>{$Ctype}: {$Type}</li>
        <li>{$Tgender}: {$Cgender}</li>
        </ul>
        </td><td width="150" valign="top" style="border-left: solid black 1px">
        + <b>{$Attributes}</b>
        <ul>
        <li>{$Cstatus}: {$Stat}</li>
        <li>{$Cpower}: {$Power}</li>
        <li>{$Cdefense}: {$Defense}</li>
        <li>{$Twins}: {$Wins}</li>
        <li>{$Tlosses}: {$Losses}</li>
        </ul>
        </td></tr>
        <tr><td colspan="2" align="center" style="border-top: solid black 1px;">{$Coptions}:
        (<a href="core.php?view=library&amp;id={$Library}">{$Showdesc}</a>)
        {$Link}
        (<a href="core.php?view=mycores&amp;release={$Id}">{$Freec}</a>)
        (<a href="core.php?view=mycores&amp;give={$Id}">{$Sendcore}</a>)
        (<a href="core.php?view=mycores&amp;name={$Id}">{$Acname}</a>)</td></tr>
        </table></center>
    {/if}
    {if $Name2 > "0"}
        <form method="post" action="core.php?view=mycores&amp;name={$Id}&amp;step=name">
            <input type="submit" value="{$Achange}" /> {$Tchange}: <input type="text" name="cname" /><br />
        </form>
    {/if}
    {if $Give > "0"}
        <form method="post" action="core.php?view=mycores&amp;give={$Id}&amp;step=give">
        <input type="submit" value="{$Aadd}" /> {$CoreName2} {$Tplayer}: <input type="text" name="gid" size="5" />
        </form>
    {/if}
    {if $Release > "0"}
        {$Doyou} {$Corename}?<br />
        - <a href="core.php?view=mycores&amp;release={$Release}&amp;next=yes">{$Ayes}</a><br />
        - <a href="core.php?view=mycores">{$Ano}</a>
    {/if}
{/if}

{if $View == "library"}
    {if $Coreid == ""}
        {$Libinfo1} {$Name}. {$Libinfo2} <b>{$Plcores}</b> {$Libinfo3} <b>{$Allcores}</b> {$Libinfo4}
        <br /><br />+ <b>{$Ncore}</b><br />
        <ul>
        {section name=core loop=$Normalcore}
            {$Normalcore[core]}
        {/section}
        </ul>
        {if $Hybridcore > "0"}
            + <b>{$Hcore}</b><br />
            <ul>
            {section name=core1 loop=$Hybridcore1}
                {$Hybridcore1[core1]}
            {/section}
            </ul>
        {/if}
        {if $Specialcore > "0"}
            + <b>{$Score}</b><br />
            <ul>
            {section name=core2 loop=$Specialcore1}
                {$Specialcore1[core2]}
            {/section}
            </ul>
        {/if}
    {/if}
    {if $Coreid > "0"}
        {if $Yourcore > 0}
        <br /><center>
        <table cellpadding="0" cellspacing="0" class="td" align="center" width="300">
        <tr><td colspan="2" align="center" style="border-bottom: solid black 1px;"><b>{$Showcore}</b></td></tr>
        <tr><td colspan="2" align="center"><img src="images/pets/{$Id}.jpg" /></td></tr>
        <tr><td valign="top" width="150">
        + <b>{$Maininfo}</b>
        <ul>
        <li>{$Standid}: {$Id}</li>
        <li>{$Lname}: {$Name}</li>
        <li>{$Ltype}: {$Type}</li>
        <li>{$Lrar}: {$Rarity}</li>
        <li>{$Lcat}: {$Caught}</li>
        </ul>
        </td><td width="150" valign="top" style="border-left: solid black 1px;">
        {$Description}
        </td></tr></table></center>
        {/if}
    {/if}
{/if}

{if $View == "arena"}
    {if $Step == "" && $Attack == ""}
        {$Arenainfo}
        <ul>
        {$Forest}
        {$Sea}
        {$Mountains}
        {$Plant}
        {$Desert}
        {$Magic}
        <li><a href="core.php?view=arena&amp;step=heal">{$Aheal}</a>.</li>
        </ul>
    {/if}
    {if $Step == "battles" && $Attack == ""}
        <table><tr><td width=100><b><u>{$Tcore}</u></b></td><td width=100><b><u>{$TOwner}</u></b></td><td width=100><b><u>{$Coptions}</u></b></td></tr>
        {section name=core3 loop=$Corename}
            <tr>
            <td><a href="core.php?view=library&amp;id={$Library[core3]}">{$Corename[core3]}</a> {$Tcore}</td>
            <td><a href="view.php?view={$Owner[core3]}">{$Owner[core3]}</a></td>
            <td><a href="core.php?view=arena&amp;attack={$Attackid[core3]}">Atak</a></td>
            </tr>
        {/section}
        </table>
    {/if}
    {if $Attack > "0"}
        + <b>{$Coreb}</b><br />
        {$Ycore1} {$Ycorename} {$Ycore2} {$Ecoreowner} {$Ecorename} {$Ycore3}<br /><br />
        {$Ecore1} <b>{$Ecorename}</b> {$Ecore2} {$Ecoreattack}!<br />{$Ecore3} <b>{$Ycorename}</b> {$Ecore2} {$Ycoreattack}!<br /><br />
        {$Result}
        {$Info}
        {$Gains}
    {/if}
    {if $Step == "heal"}
        {$Itcost} <b>{$Cost}</b> {$Gold2} <b>{$Mithcost}</b> {$Gold3} <b>{$Number}</b> {$Dcores}
        <ul>
        <li><a href="core.php?view=arena&amp;step=heal&amp;answer=yes">{$Ayes2}</a></li>
        <li><a href="core.php">{$Ano2}</a>.</li>
        </ul>
    {/if}
{/if}

{if $View == "train"}
    {$Traininfo} <b>{$Trains}</b> {$Traininfo2}
    <form method="post" action="core.php?view=train&amp;step=train">
    {$Trainmy} <select name="train_core">
    {section name=core4 loop=$Corename}
        <option value="{$Coreid1[core4]}">{$Corename[core4]}</option>
    {/section}
    </select> 
    {$Trcore} <input type="text" size="5" name="reps" /> {$Tamount2} <select name="technique">
    <option value="power">{$Tpower2}</option>
    <option value="defense">{$Tdefense2}</option>
    </select>. <input type="submit" value="{$Atrain}" /></form>
{/if}

{if $View == "explore"}
    {if $Next == ""}
        {$Exploreinfo}
        <form method="post" action="core.php?view=explore&amp;next=yes">
        <select name="explore"><option value="Forest">{$Region1} (0 {$Mith2})</option>
        <option value="Ocean">{$Region2} (50 {$Mith2})</option>
        <option value="Mountains">{$Region3} (100 {$Mith2})</option>
        <option value="Plains">{$Region4} (150 {$Mith2})</option>
        <option value="Desert">{$Region5} (200 {$Mith2})</option>
        <option value="Magic">{$Region6} (250 {$Mith2})</option></select><br />
        {$Asearch} <input type="text" name="repeat" size="4" /> {$Eamount}<br />
        <input type="submit" value="{$Asearch}" /></form>
    {/if}
    {if $Next == "yes"}
        {$Youstart}: <b>{$Area}</b>...<br />
        {section name=core7 loop=$Find1}
            {$Find1[core7]}
            {$Find2[core7]}
            {$Find3[core7]}
        {/section}
    <br />{$Yousearch} {$Repeat} {$Eamount}<br />
        <br />{$Again}<br />
        <a href="core.php?view=explore">{$Ayes}</a><br />
        <a href="core.php">{$Ano}</a><br />
    {/if}
{/if}

{if $View != ""}
    <br /><br />... <a href="core.php">{$Asector}</a>.
{/if}

