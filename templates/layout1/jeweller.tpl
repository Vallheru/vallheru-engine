{if $Step == ""}
    {$Jewellerinfo}<br /><br />
    <ul>
        <li><a href="jeweller.php?step=plans">{$Aplans}</a></li>
        <li><a href="jeweller.php?step=make">{$Aring}</a></li>
        {if $Playerclass == "Rzemieślnik"}
        <li><a href="jeweller.php?step=make2">{$Amakering}</a></li>
        <li><a href="jeweller.php?step=make3">{$Amakering2}</a></li>
        {/if}
    </ul>
{/if}

{if $Step == "plans"}
    {$Plansinfo}<br /><br />
    <table align="center" width="75%" class="dark">
        <tr>
            <td><b><u>{$Tname}</u></b></td>
            <td><b><u>{$Tcost}</u></b></td>
            <td><b><u>{$Tlevel}</u></b></td>
            <td><b><u>{$Taction}</u></b></td>
        </tr>
        {section name=plans loop=$Planid}
            <tr>
                <td>{$Planname[plans]}</td>
                <td>{$Plancost[plans]}</td>
                <td>{$Planlevel[plans]}</td>
                <td><a href="jeweller.php?step=plans&amp;buy={$Planid[plans]}">{$Abuy}</a></td>
            </tr>
        {/section}
    </table>
    <br /><br />
{/if}

{if $Step == "make"}
    {$Ringinfo}<br /><br />
    <form method="post" action="jeweller.php?step=make&amp;make=Y">
        <input type="submit" value="{$Amake}" /> <input type="text" name="amount" size="5" /> {$Ramount}
    </form>
{/if}

{if $Step == "make2"}
    {if $Action == ""}
        {if $Maked == ""}
            {$Ringinfo}<br /><br />
            <table class="dark">
                <tr>
                    <td><b><u>{$Tname}</u></b></td>
                    <td><b><u>{$Tlevel}</u></b></td>
                    <td><b><u>{$Tadam}</u></b></td>
                    <td><b><u>{$Tcryst}</u></b></td>
                    <td><b><u>{$Tmeteor}</u></b></td>
                    <td><b><u>{$Tenergy}</u></b></td>
                    <td><b><u>{$Tbonus}</u></b></td>
                    <td><b><u>{$Tchange}</u></b></td>
                </tr>
                {section name=rings loop=$Rid}
                <tr>
                    <td><a href="jeweller.php?step=make2&amp;make={$Rid[rings]}">{$Rname[rings]}</a></td>
                    <td align="center">{$Rlevel[rings]}</td>
                    <td align="center">{$Radam[rings]}</td>
                    <td align="center">{$Rcryst[rings]}</td>
                    <td align="center">{$Rmeteor[rings]}</td>
                    <td align="center">{$Renergy[rings]}</td>
                    <td align="center">{$Rbonus[rings]}</td>
                    <td align="center">{$Rchange[rings]}</td>
                </tr>
                {/section}
            </table>
            {if $Make != ""}
                <br /><br />
                <form method="post" action="jeweller.php?step=make2&amp;action=create">
                    <input type="submit" value="{$Youmake}" /> <b>{$Rname2}</b>{if $Change == "Y"} {$Withbon} <select name="bonus">
                    {section name=make loop=$Rbonus2}
                        <option value="{$Rbonus2[make]}">{$Rbonus2[make]}</option>
                    {/section}
                    </select>{/if} {$Ramount} <input type="text" name="amount" size="5" value="0"/> {$Tenergy3} {$Youhave}
                    <input type="hidden" value="{$Make}" name="make" />
                </form>
            {/if}
        {else}
            {$Ringinfo}<br /><br />
            <table class="dark">
                <tr>
                    <td><b><u>{$Tname}</u></b></td>
                    <td><b><u>{$Tenergy}</u></b></td>
                    <td><b><u>{$Tenergy2}</u></b></td>
                </tr>
                <tr>
                    <td>{$Rname}</td>
                    <td align="center">{$Renergy}</td>
                    <td align="center">{$Renergy2}</td>
                </tr>
            </table>
            <br /><br />
            <form method="post" action="jeweller.php?step=make2&amp;action=continue">
                <input type="submit" value="{$Youcontinue}" /> <b>{$Rname}</b> {$Ramount} <input type="text" name="amount" size="5" value="0"/> {$Tenergy3}
                <input type="hidden" value="{$Maked}" name="make" />
            </form>
        {/if}
    {/if}
{/if}

{if $Step == "make3"}
    {$Ringinfo}<br /><br />
    {if $Maked == ""}
        <table align="center">
            <tr>
                <td><b><u>{$Tname}</u></b></td>
                <td><b><u>{$Tlevel}</u></b></td>
                <td><b><u>{$Tmeteor}</u></b></td>
                <td><b><u>{$Tenergy}</u></b></td>
            </tr>
            {section name=rings2 loop=$Rname}
            <tr>
                <td>{$Rname[rings2]}</td>
                <td align="center">{$Rlevel[rings2]}</td>
                <td align="center">{$Rmeteor[rings2]}</td>
                <td align="center">{$Renergy[rings2]}</td>
            </tr>
            {/section}
        </table>
        <br /><br />
        <form method="post" action="jeweller.php?step=make3&amp;action=create">
            <input type="submit" value="{$Amake}" /> <select name="rings">
            {section name=rings3 loop=$Rid2}
                <option value="{$Rid2[rings3]}">{$Rname2[rings3]} +{$Rpower[rings3]} {$Ramount3} {$Ramount2[rings3]}</option>
            {/section}
            </select> {$Onspecial} {$Ramount4} <input type="text" name="amount" value="0" size="5" /> {$Renergy2} {$Ameteor}
        </form>
    {else}
        <table>
            <tr>
                <td><b><u>{$Tname}</u></b></td>
                <td><b><u>{$Tenergy}</u></b></td>
                <td><b><u>{$Tenergy2}</u></b></td>
            </tr>
            <tr>
                <td>{$Rname}</td>
                <td align="center">{$Renergy}</td>
                <td align="center">{$Renergy2}</td>
            </tr>
        </table>
        <br /><br />
        <form method="post" action="jeweller.php?step=make3&amp;action=continue">
            <input type="submit" value="{$Youcontinue}" /> <b>{$Rname}</b> {$Ramount} <input type="text" name="amount" size="5" value="0"/> {$Tenergy3}
            <input type="hidden" value="{$Maked}" name="make" />
        </form>
    {/if}
{/if}

{if $Step != ""}
    {$Message2}
    {if $Tmaked != ''}
        <br />{$Tmaked}:<br />
	{section name=making loop=$Iamount}
	    {$Iname} (+ {$Ibonus[making]}) {$Tamount}: {$Iamount[making]}<br />
	{/section}
    {/if}
    {if $Tmaked2 == 1}
	{section name=making2 loop=$Iamount}
	    {if $Iamount[making2] > 0} 
	        {$Iname[making2]} (+ {$Ibonus[making2]}) {$Tamount}: {$Iamount[making2]}<br />
	    {/if}
	{/section}
    {/if}
    <br /><br /><a href="jeweller.php">{$Aback}</a>
{/if}
