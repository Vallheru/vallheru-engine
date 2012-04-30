{if $Mill == ""}
    {$Millinfo}<br /><br />
    <ul>
    <li><a href="lumbermill.php?mill=plany">{$Aplans}</a></li>
    <li><a href="lumbermill.php?mill=mill">{$Amill}</a></li>
    {if $Aelite != ''}
        <li><a href="lumbermill.php?mill=elite">{$Aelite}</a></li>
    {/if}
    {if $Llevel < "4"}
        <li><a href="lumbermill.php?mill=licenses">{$Alicenses}</a></li>
    {/if}
    {if $Astral == "Y"}
        <li><a href="lumbermill.php?mill=astral">{$Aastral}</a></li>
    {/if}
    </ul>
{/if}

{if $Mill == "licenses"}
    {if $Step == ""}
        - <a href="lumbermill.php?mill=licenses&amp;step=buy">{$Alicense}</a>
    {/if}
    {if $Step == "buy"}
        {$Message}
    {/if}
{/if}

{if $Mill == "plany"}
    {$Plansinfo}<br />
    {$Hereis}:
    <table>
    <tr>
    <td width="100"><b><u>{$Iname}</u></b></td>
    <td width="50"><b><u>{$Icost}</u></b></td>
    <td><b><u>{$Ilevel}</u></b></td>
    <td><b><u>{$Ioption}</u></b></td>
    </tr>
    {section name=mill loop=$Name}
        <tr>
        <td>{$Name[mill]}</td>
        <td>{$Cost[mill]}</td>
        <td>{$Level[mill]}</td>
        <td>- <a href="lumbermill.php?mill=plany&amp;buy={$Planid[mill]}">{$Abuy}</a></td>
        </tr>
    {/section}
    </table>
    {if $Buy != ""}
        {$Youpay} <b>{$Cost1}</b> {$Andbuy}: <b>{$Name1}</b>.
    {/if}
{/if}

{if $Mill == "mill" || $Mill == "elite"}
    {if $Make == "" && $Continue == ""}
        {$Millinfo}
        {if $Maked == ""}
             {$Info}:
             <table>
             <tr>
             <td width="100"><b><u>{$Iname}</u></b></td>
             <td width="50"><b><u>{$Ilevel}</u></b></td>
	     <td width="50"><b><u>{$Ienergy}</u></b></td>
             <td><b><u>{$Ilumber}</u></b></td>
	     {if $Mill == "elite"}
	         <td><b><u>{$Tloot}</u><b></td>
             {/if}
	     </tr>
             {section name=mill1 loop=$Plans}
                 <tr>
                 <td><a href="lumbermill.php?mill={$Mill}&amp;dalej={$Plans[mill1].id}">{$Plans[mill1].name}</a></td>
                 <td>{$Plans[mill1].level}</td>
		 <td>{$Plans[mill1].energy}</td>
                 <td>{$Plans[mill1].amount}</td>
		 {if $Mill == "elite"}
		     <td>{$Loot[mill1]}</td>
		 {/if}
                 </tr>
             {/section}
             </table>
        {/if}
        {if $Maked == "1"}
            {$Info2}:
            <table>
            <tr>
            <td width="100"><b><u>{$Iname}</u></b></td>
            <td width="50"><b><u>{$Ipercent}</u></b></td>
            <td width="50"><b><u>{$Ienergy}</u></b></td>
            </tr>
            <tr>
            <td><a href="lumbermill.php?mill={$Mill}&amp;ko={$Planid}">{$Name}</a></td>
            <td>{$Percent}</td>
            <td>{$Need}</td>
            </tr>
            </table>
        {/if}
        {if $Cont != ""}
            <form method="post" action="lumbermill.php?mill={$Mill}&amp;konty={$Id}">
            {$Assignen} <b>{$Name}</b> <input type="text" name="razy" size="5" /> {$Menergy}
            <input type="submit" value="{$Amake}" /></form>
        {/if}
        {if $Next != ""}
            <form method="post" action="lumbermill.php?mill={$Mill}&amp;rob={$Id}">
                {$Assignen} <b>{$Name}</b> <input type="text" name="razy" size="5" /> {$Menergy}
                <input type=submit value="{$Amake}" />{if $Type == "B"} {html_options name=lumber options=$Loptions}{else} {$Tlumber}{/if}
            </form>
        {/if}
    {/if}
    {if $Continue != ""}
        {$Message}
    {/if}
    {if $Make != ""}
	<p>{$Message}</p>
	{if $Amt > 0}
	    <p>{$Youmade}</p>
	    {section name=maked loop=$Items}
	        {$Items[maked].name} (+ {$Items[maked].power}) (+ {$Items[maked].speed} {$Ispeed}) ({$Items[maked].wt}/{$Items[maked].wt}) {$Iamount}: {$Amount[maked]}<br />
	    {/section}
	{/if}
    {/if}
{/if}

{if $Mill == "astral"}
    {$Millinfo}<br /><br />
    {$Message}<br /><br />
    {section name=astral loop=$Aviablecom}
        <b>{$Tname}:</b> {$Aviablecom[astral]}<br />
        {section name=astral2 loop=$Mineralsname}
            <b>{$Mineralsname[astral2]}:</b> {$Minamount[astral][astral2]}<br />
        {/section}
        <form method="post" action="lumbermill.php?mill=astral&amp;component={$Compnumber[astral]}">
            <br /><input type="submit" value="{$Abuild}" />
        </form>
        <br />
    {/section}
{/if}

{if $Mill != ""}
    <br /><br /><a href="lumbermill.php">({$Aback})</a>
{/if}
