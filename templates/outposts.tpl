{if $Outpost == ""}
    {$Nooutpost}<br /><br />
    "<a href="outposts.php?action=buy">{$Ayes}</a>."<br />
    "<a href="city.php">{$Ano}</a>."<br />
{/if}

{if $View == "" && $Outpost != ""}
    {$Outinfo}
    <ul>
    <li><a href="outposts.php?view=myoutpost">{$Amy}</a></li>
    <li><a href="outposts.php?view=taxes">{$Atax}</a></li>
    <li><a href="outposts.php?view=shop">{$Ashop}</a></li>
    <li><a href="outposts.php?view=gold">{$Agold}</a></li>
    <li><a href="outposts.php?view=battle">{$Aattack}</a></li>
    <li><a href="outposts.php?view=listing">{$Alist}</a><br /><br /></li>
    <li><a href="outposts.php?view=guide">{$Aguide}</a></li>
    </ul>
{/if}

{if $View == "gold"}
    {$Goldinfo} <b>{$Treasury}</b> {$Goldcoins}.<br /><br />
    <form method="post" action="outposts.php?view=gold&amp;step=player">
    <input type="submit" value="{$Atake}" /> <input type="text" name="zeton" value="{$Treasury}" /> {$Fromout}.</form>
    <form method="post" action="outposts.php?view=gold&amp;step=outpost">
    <input type="submit" value="{$Aadd}" /> <input type="text" name="sztuki" value="{$GoldInHand}" /> {$Toout}.</form>
    {$Message2}
{/if}

{if $View == "veterans"}
    <form method="post" action="outposts.php?view=veterans&amp;id={$Vid}&amp;step=modify">
    <table>
    <tr><td><b>{$Vname2}</b>:</td><td>{$Vname}</td></tr>
    <tr><td><b>{$Vwep}</b>:</td><td>{$Wname} ({$Ipower}: {$Wpower}) <select name="weapon"> {section name=vetwep loop=$Wid}
                    <option value="{$Wid[vetwep]}">{$Wname1[vetwep]} ({$Ipower}: {$Wpower1[vetwep]})</option>
                  {/section} </select></td></tr>
    <tr><td><b>{$Tarrows}</b>:</td><td>{$Oname} ({$Ipower}: {$Opower}) <select name="arrows"> {section name=vetarr loop=$Oid}
                    <option value="{$Oid[vetarr]}">{$Oname1[vetarr]} ({$Ipower}: {$Opower1[vetarr]})</option>
                  {/section} </select></td></tr>
    <tr><td><b>{$Varmor}</b>:</td><td>{$Aname} {if $Aname != $Nothing} ({$Vdefense}: {$Apower}) {/if} <select name="armor"> {section name=outpost9 loop=$Aid}
                    <option value="{$Aid[outpost9]}">{$Aname1[outpost9]} ({$Vdefense}: {$Apower1[outpost9]})</option>
                  {/section} </select></td></tr>
    <tr><td><b>{$Vhelmet}</b>:</td><td>{$Hname} {if $Hname != $Nothing} ({$Vdefense}: {$Hpower}) {/if} <select name="helm"> {section name=outpost10 loop=$Hid}
                    <option value="{$Hid[outpost10]}">{$Hname1[outpost10]} ({$Vdefense}: {$Hpower1[outpost10]})</option>
                  {/section} </select></td></tr>
    <tr><td><b>{$Vlegs}</b>:</td><td>{$Lname} {if $Lname != $Nothing} ({$Vdefense}: {$Lpower}) {/if} <select name="legs"> {section name=outpost11 loop=$Lid}
                    <option value="{$Lid[outpost11]}">{$Lname1[outpost11]} ({$Vdefense}: {$Lpower1[outpost11]})</option>
                  {/section} </select></td></tr>
    <tr><td><b>{$Tring}</b>:</td><td>{$Rname1} {if $Rname1 != $Nothing} ({$Ipower}: {$Rpower1}) {/if} <select name="ring1"> {section name=vetring loop=$Rid1}
                    <option value="{$Rid1[vetring]}">{$Rname12[vetring]} ({$Ipower}: {$Rpower12[vetring]})</option>
                  {/section} </select></td></tr>
    <tr><td><b>{$Tring}</b>:</td><td>{$Rname2} {if $Rname2 != $Nothing} ({$Ipower}: {$Rpower2}) {/if} <select name="ring2"> {section name=vetring2 loop=$Rid2}
                    <option value="{$Rid2[vetring2]}">{$Rname22[vetring2]} ({$Ipower}: {$Rpower22[vetring2]})</option>
                  {/section} </select></td></tr>
    <tr><td><b>{$Vattack}</b>:</td><td>{$Power}</td></tr>
    <tr><td><b>{$Vdefense}</b>:</td><td>{$Defense}</td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Amodify}" />
    </table>
    </form>
    {$Message2}
{/if}

{if $View == "myoutpost"}
    {$Welcome} {$User}.<br /><br /><b><u>{$Outinfo}</u></b><br />
    <table>
    <tr><td><b>{$Landa}</b>:</td><td>{$Size}</td></tr>
    <tr><td><b>{$Ap}</b>:</td><td>{$Turns}</td></tr>
    <tr><td><b>{$Tgoldcoins}</b>:</td><td>{$Gold}</td></tr>
    <tr><td><b>{$Tsoldiers}</b>:</td><td>{$Warriors} ({$Tfree}: {$Maxtroops})</td></tr>
    <tr><td><b>{$Tarchers}</b>:</td><td>{$Archers} ({$Tfree}: {$Maxtroops})</td></tr>
    <tr><td><b>{$Tforts}</b>:</td><td>{$Barricades} ({$Tfree}: {$Maxequip})</td></tr>
    <tr><td><b>{$Tcatapults}</b>:</td><td>{$Catapults} ({$Tfree}: {$Maxequip})</td></tr>
    {if $Size > 3}
        <tr><td><b>{$Tlairs}</b>:</td><td>{$Fence} ({$Tmax}: {$Maxfence})</td></tr>
        <tr><td><b>{$Tmonsters}</b>:</td><td>{$Monster} ({$Tfree}: {$Maxmonster})</td></tr>
        {if $Mid != "0"}
            {section name=outpost3 loop=$Mname}
                <tr><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>{$Mname[outpost3]}</b> ({$Tpower}: {$Mpower[outpost3]} {$Udefense}: {$Mdefense[outpost3]})</td></tr>
            {/section}
        {/if}
        <tr><td><b>{$Tbarracks}</b>:</td><td>{$Barracks} ({$Tmax}: {$Maxbarracks})</td></tr>
        <tr><td><b>{$Tveterans}</b>:</td><td>{$Veterans} ({$Tfree}: {$Maxveterans})</td></tr>
        {if $Vid != "0"}
            {section name=outpost8 loop=$Vname}
                <tr><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b><a href="outposts.php?view=veterans&amp;id={$Vid[outpost8]}">{$Vname[outpost8]}</a></b> ({$Tpower}: {$Vpower[outpost8]} {$Udefense}: {$Vdefense[outpost8]})</td></tr>
            {/section}
        {/if}
    {/if}
    {if $Warriors > 0 || $Archers > 0}
        <tr><td><b>{$Tfatigue}</b>:</td><td>{$Fatigue}%</td></tr>
        <tr><td><b>{$Tmorale}</b>:</td><td>{$Morale} ({$Moralename})</td></tr>
    {/if}
    <tr><td><b>{$Tcost}</b>:</td><td>{$Cost} {$Tcostg}</td></tr>
    </table>
    <br /><br /><b><u>{$Tbonus}</u></b><br />
    <table>
    <tr><td><b>{$Tattack}</b>:</td><td>+ {$Attack} % {if $Link == "Y" && $Attack < 30}<a href="outposts.php?view=myoutpost&amp;step=add&amp;ability=battack">{$Aadd}</a>{/if}</td></tr>
    <tr><td><b>{$Tdefense}</b>:</td><td>+ {$Defense} % {if $Link == "Y" && $Defense < 30}<a href="outposts.php?view=myoutpost&amp;step=add&amp;ability=bdefense">{$Aadd}</a>{/if}</td></tr>
    <tr><td><b>{$Ttax}</b>:</td><td>+ {$Tax} % {if $Link == "Y" && $Tax < 30}<a href="outposts.php?view=myoutpost&amp;step=add&amp;ability=btax">{$Aadd}</a>{/if}</td></tr>
    <tr><td><b>{$Tlosses}</b>:</td><td>- {$Lost} % {if $Link == "Y" && $Lost < 30}<a href="outposts.php?view=myoutpost&amp;step=add&amp;ability=blost">{$Aadd}</a>{/if}</td></tr>
    <tr><td><b>{$Tcostb}</b>:</td><td>- {$Bcost} % {if $Link == "Y" && $Bcost < 30}<a href="outposts.php?view=myoutpost&amp;step=add&amp;ability=bcost">{$Aadd}</a>{/if}</td></tr>
    </table>
    {$Message2}
{/if}

{if $View == "taxes"}
    {$Taxinfo}<br /><br />
    <form method="post" action="outposts.php?view=taxes&amp;step=gain">
    <input type="submit" value="{$Asend}" /> {$Soldiers} <input type="text" name="amount" value="{$Amount}" size="5" /> {$Times}</form>
    {$Message2}
{/if}

{if $View == "shop"}
{* Part 1: Display shop info and player's resources. *}
    {$Shopinfo} <b>{$Maxtroops}</b> {$Ttroops} {$Shopinfo2} <b>{$Maxequips}</b> {$Tequips}{$Shopinfo3}
    <ul>
        <li>{$Gold}{$Goldcoins}</li>
        <li>{$Platinum}{$Platinumpcs}</li>
        <li>{$Pine}{$Pinepcs}</li>
        <li>{$Crystal}{$Crystalpcs}</li>
        <li>{$Adamantium}{$Adamantiumpcs}</li>
        <li>{$Meteor}{$Meteorpcs}</li>
    </ul>
    {if $Message2}
        <br />{$Message2}<br />
    {/if}
{* Part 2: Form to increase outpost's size, buy beasts' lair or veterans' barracks. *}
    <fieldset><legend>{$OutpostDevelopment}</legend> 
        {if $MaxPossibleLevel > "0"}
            {$LevelInfo}<br />
            <form method="post" action="outposts.php?view=shop&amp;buy=s">
            <input type="submit" value="{$Level}"/> {html_options name=amount options=$OutpostLevels}
            </form>
        {else}
            {$NoLevelInfo}<br />
        {/if}
        {if $MaxPossibleLair > "0"}
            {$LairInfo}<br />
            <form method="post" action="outposts.php?view=shop&amp;buy=f">
            <input type="submit" value="{$Lair}"/> {html_options name=amount options=$LairLevels}
            </form>
        {else}
            {$NoLairInfo}<br />
        {/if}
        {if $MaxPossibleBarrack > "0"}
            {$BarrackInfo}<br />
            <form method="post" action="outposts.php?view=shop&amp;buy=r">
            <input type="submit" value="{$Barrack}"/> {html_options name=amount options=$BarrackLevels}
            </form>
        {else}
            {$NoBarrackInfo}<br />
        {/if}
    </fieldset>
{* Part 3: Form to hire army, war machines and barricades *}
    <fieldset><legend>{$ArmyDevelopment}</legend>
        <form method="post" action="outposts.php?view=shop&amp;buy=all">
            <input type="submit" value="{$Buyall}" name="buyall" /><br /><br />
            <input type="submit" value="{$Abuy}" name="buy0"/> <input type="text" name="army0" value="{if $Awarriors < $Maxtroops}{$Awarriors}{else}{$Maxtroops}{/if}" size="5" /> {$Bsoldiers}: {$Awarriors})<br />
            <input type="submit" value="{$Abuy}" name="buy1"/> <input type="text" name="army1" value="{if $Aarchers < $Maxtroops}{$Aarchers}{else}{$Maxtroops}{/if}" size="5" /> {$Barchers}: {$Aarchers})<br />
            <input type="submit" value="{$Abuy}" name="buy2"/> <input type="text" name="army2" value="{if $Abarricades < $Maxequips}{$Abarricades}{else}{$Maxequips}{/if}" size="5" /> {$Bforts}: {$Abarricades})<br />
            <input type="submit" value="{$Abuy}" name="buy3"/> <input type="text" name="army3" value="{if $Acatapults < $Maxequips}{$Acatapults}{else}{$Maxequips}{/if}" size="5" /> {$Bmachines}: {$Acatapults})<br />
        </form>
    </fieldset>
{* Part 4: Manage empty barracks and lairs *}
    {if $Fence == "Y" || $Barracks == "Y"}
        <fieldset><legend>{$Management}</legend>
        {if $Fence == "Y"}
            <form method="post" action="outposts.php?view=shop&amp;buy=m">
            <input type="submit" value="{$Aadd}" /> <select name="army">
            {section name=outpost2 loop=$Mid}
                <option value="{$Mid[outpost2]}">{$Mname[outpost2]} ({$Uattack}: {$Power[outpost2]} | {$Udefense}: {$Defense[outpost2]})</option>
            {/section}
            </select> {$Fora}</form>
        {else}
            {$NoLair}<br />
        {/if}
        {if $Barracks == "Y"}
            <form method="post" action="outposts.php?view=shop&amp;buy=v">
            <input type="submit" value="{$Aadd}" /> {$Vname2} <input type="text" name="vname" /><br />
            <b>{$Uattack}:</b> 1 + <select name="weapon">
            {section name=outpost4 loop=$Wid}
                <option value="{$Wid[outpost4]}">{$Wname[outpost4]} ({$Ipower2}: {$Wpower[outpost4]})</option>
            {/section}
            </select><br />
	    {$Tarrows}:
	    {if $Oid[1] != "0"}
                <select name="arrows">
                {section name=vetarr loop=$Oid}
                    <option value="{$Oid[vetarr]}">{$Oname[vetarr]} ({$Ipower2}: {$Opower[vetarr]})</option>
                {/section}
                </select><br />
            {else}
                {$Nothing}<br />
            {/if}
            <b>{$Udefense}:</b> 1 + <br />
            {$Varmor}:
            {if $Aid[1] != "0"}
                <select name="armor">
                {section name=outpost5 loop=$Aid}
                    <option value="{$Aid[outpost5]}">{$Aname[outpost5]} ({$Udefense}: {$Apower[outpost5]})</option>
                {/section}
                </select><br />
            {else}
                {$Nothing}<br />
            {/if}
            {$Vhelmet}:
            {if $Hid[1] != "0"}
                <select name="helm">
                {section name=outpost6 loop=$Hid}
                    <option value="{$Hid[outpost6]}">{$Hname[outpost6]} ({$Udefense}: {$Hpower[outpost6]})</option>
                {/section}
                </select><br />
            {else}
                {$Nothing}<br />
            {/if}
            {$Vlegs}:
            {if $Lid[1] != "0"}
                <select name="legs">
                {section name=outpost7 loop=$Lid}
                    <option value="{$Lid[outpost7]}">{$Lname[outpost7]} ({$Udefense}: {$Lpower[outpost7]})</option>
                {/section}
                </select><br />
            {else}
                {$Nothing}<br />
            {/if}
	    {$Tring}:
            {if $Rid1[1] != "0"}
                <select name="ring1">
                {section name=vetring loop=$Rid1}
                    <option value="{$Rid1[vetring]}">{$Rname1[vetring]} ({$Ipower2}: {$Rpower1[vetring]})</option>
                {/section}
                </select><br />
            {else}
                {$Nothing}<br />
            {/if}
	    {$Tring}:
            {if $Rid2[1] != "0"}
                <select name="ring2">
                {section name=vetring2 loop=$Rid2}
                    <option value="{$Rid2[vetring2]}">{$Rname2[vetring2]} ({$Ipower2}: {$Rpower2[vetring2]})</option>
                {/section}
                </select><br />
            {else}
                {$Nothing}<br />
            {/if}
            {$Fora}</form>
        {else}
            {$NoBarracks}<br />
        {/if}
        </fieldset>
    {/if}
{/if}

{if $View == "listing"}
    {if $Step == ""}
        <form method="post" action="outposts.php?view=listing&amp;step=list">
        <input type="submit" value="{$Ashow}" /> {$Froml} <input type="text" name="slevel" size="5" value="{$Outmin}" /> {$Tol}
        <input type="text" name="elevel" size="5" value="{$Outmax}" /></form>
    {/if}
    {if $Step != ""}
        <table>
        <tr>
        <td width="100"><b><u>{$Outid}</u></b></td>
        <td width="100"><b><u>{$Outsize}</u></b></td>
        <td width="100"><b><u>{$Outowner}</u></b></td>
        <td width="100"><b><u>{$Outattack}</u></b></td>
        </tr>
        {section name=outposts1 loop=$Oid}
            <tr>
            <td>{$Oid[outposts1]}</td>
            <td>{$Size[outposts1]}</td>
            <td><a href="view.php?view={$Owner[outposts1]}">{$Ownername[outposts1]} ID: {$Owner[outposts1]}</a></td>
            <td>- <a href="outposts.php?view=battle&oid={$Oid[outposts1]}">{$Aattack}</a></td>
            </tr>
        {/section}
        </table>
    {/if}
{/if}

{if $View == "battle"}
    {$Battleinfo}
    <form method="post" action="outposts.php?view=battle&amp;action=battle"><table>
    <tr><td>{$Outid}:</td><td><input type="text" name="oid" size="6" value="{$Id}" /> {$Tor}</td></tr>
    <tr><td>{$Pid}:</td><td><input type="text" name="pid" size="6" value="{$Pid2}" /></td></tr>
    <tr><td>{$Amounta}:</td><td><input type="text" name="amount" size="6"/></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="{$Aattack}" /></td></tr>
    </table></form>
    {section name=outposts loop=$Result}
        {$Result[outposts]}
    {/section}
{/if}

{if $View == "guide"}
    <u><b>{$Info1}</b></u><br />
    {$Info1a}
    <br /><br />
    <u><b>{$Info2}</b></u><br />
    {$Info2a}
    <br /><br />
    <u><b>{$Info3}</b></u><br />
    {$Info3a}
    <br /><br />
    <u><b>{$Info4}</b></u><br />
    {$Info4a}
    <br /><br />
    <u><b>{$Info5}</b></u><br />
    {$Info5a}
    <br /><br />
    <b><u>{$Info6}</u></b><br />
    {$Info6a}
{/if}

{if $View != ""}
    <br /><br />[<a href="outposts.php">{$Aback}</a>]
{/if}
