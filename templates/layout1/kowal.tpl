{if $Smith == ""}
    {$Smithinfo}<br /><br />
    <ul>
    <li><a href="kowal.php?kowal=plany">{$Aplans}</a></li>
    <li><a href="kowal.php?kowal=kuznia">{$Asmith}</a></li>
    {if $Aelite != ''}
        <li><a href="kowal.php?kowal=elite">{$Aelite}</a></li>
    {/if}
    {if $Astral == "Y"}
        <li><a href="kowal.php?kowal=astral">{$Aastral}</a></li>
    {/if}
    </ul>
{/if}

{if $Smith == "plany"}
    {$Plansinfo}<br />
    <ul>
    <li><a href="kowal.php?kowal=plany&amp;dalej=W">{$Aplansw}</a></li>
    <li><a href="kowal.php?kowal=plany&amp;dalej=A">{$Aplansa}</a></li>
    <li><a href="kowal.php?kowal=plany&amp;dalej=S">{$Aplanss}</a></li>
    <li><a href="kowal.php?kowal=plany&amp;dalej=H">{$Aplansh}</a></li>
    <li><a href="kowal.php?kowal=plany&amp;dalej=L">{$Aplansl}</a></li>
    </ul>
    {if $Next != ""}
        {$Hereis}:
        <table class="dark">
        <tr>
        <td width="100"><b><u>{$Iname}</u></b></td>
        <td width="50"><b><u>{$Icost}</u></b></td>
        <td><b><u>{$Ilevel}</u></b></td>
        <td><b><u>{$Ioption}</u></b></td>
        </tr>
        {section name=smith loop=$Name}
            <tr>
            <td>{$Name[smith]}</td>
            <td>{$Cost[smith]}</td>
            <td>{$Level[smith]}</td>
            <td>- <a href="kowal.php?kowal=plany&amp;buy={$Id[smith]}">{$Abuy}</a></td>
            </tr>
        {/section}
        </table>
    {/if}
    {if $Buy != ""}
        {$Youpay} <b>{$Cost}</b> {$Andbuy}: <b>{$Plan}</b>.
    {/if}
{/if}

{if $Smith == "kuznia" || $Smith == "elite"}
    {if $Make == "" && $Continue == ""}
        {$Smithinfo}
        {if $Maked == ""}
            <ul>
            <li><a href="kowal.php?kowal={$Smith}&amp;type=W">{$Amakew}</a></li>
            <li><a href="kowal.php?kowal={$Smith}&amp;type=A">{$Amakea}</a></li>
            <li><a href="kowal.php?kowal={$Smith}&amp;type=S">{$Amakes}</a></li>
            <li><a href="kowal.php?kowal={$Smith}&amp;type=H">{$Amakeh}</a></li>
            <li><a href="kowal.php?kowal={$Smith}&amp;type=L">{$Amakel}</a></li>
            </ul>
            {if $Type != ""}
                {$Info}:
                <table class="dark">
                    <tr>
                        <td width="100"><b><u>{$Iname}</u></b></td>
                        <td width="50"><b><u>{$Ilevel}</u></b></td>
                        <td><b><u>{$Iamount}</u></b></td>
			{if $Smith == "elite"}
	        	    <td><b><u>{$Tloot}</u></b></td>
             		{/if}
                    </tr>
                    {section name=smith2 loop=$Name}
                        <tr>
                        <td><a href="kowal.php?kowal={$Smith}&amp;dalej={$Id[smith2]}">{$Name[smith2]}</a></td>
                        <td>{$Level[smith2]}</td>
                        <td>{$Amount[smith2]}</td>
			{if $Smith == "elite"}
		     	    <td>{$Loot[smith2]}</td>
		 	{/if}
                        </tr>
                    {/section}
                </table>
            {/if}
        {/if}
        {if $Maked != ""}
            {$Info3}:
            <table class="dark">
            <tr>
            <td width="100"><b><u>{$Iname}</u></b></td>
            <td width="50"><b><u>{$Ipercent}</u></b></td>
            <td width="50"><b><u>{$Ienergy}</u></b></td>
            </tr>
            <tr>
            <td><a href="kowal.php?kowal={$Smith}&amp;ko={$Id}">{$Name}</a></td>
            <td>{$Percent}</td>
            <td>{$Need}</td>
            </tr>
            </table>
        {/if}
    {/if}
    {if $Cont != "" || $Next != ""}
        <form method="post" action="{$Link}">
            {$Assignen} <b>{$Name}</b> <input type="text" name="razy" size="5" />{$Senergy}
            <input type="submit" value="{$Amake}" />{if $Next != ""} <b>{$Name}</b> <select name="mineral">
                <option value="copper">{$Mcopper}</option>
                <option value="bronze">{$Mbronze}</option>
                <option value="brass">{$Mbrass}</option>
                <option value="iron">{$Miron}</option>
                <option value="steel">{$Msteel}</option>
            </select>{/if}
        </form>
    {/if}
    {if $Continue != ""}
        {$Message}
    {/if}
    {if $Make != ""}
        {$Message}
	{if $Amt > 0}
	    <p>{$Youmade}</p>
	    {section name=maked loop=$Items}
	        {$Items[maked].name} (+ {$Items[maked].power}) ({$Items[maked].zr}% {$Iagi}) ({$Items[maked].wt}/{$Items[maked].wt}) {$Iamount}: {$Amount[maked]}<br />
	    {/section}
	{/if}
    {/if}
{/if}

{if $Smith == "astral"}
    {$Smithinfo}<br /><br />
    {$Message}<br /><br />
    {section name=astral loop=$Aviablecom}
        <b>{$Tname}:</b> {$Aviablecom[astral]}<br />
        {section name=astral2 loop=$Mineralsname}
            <b>{$Mineralsname[astral2]}:</b> {$Minamount[astral][astral2]}<br />
        {/section}
        <form method="post" action="kowal.php?kowal=astral&amp;component={$Compnumber[astral]}">
            <br /><input type="submit" value="{$Abuild}" />
        </form>
        <br />
    {/section}
{/if}

{if $Smith != ""}
    <br /><br /><a href=kowal.php>({$Aback})</a>
{/if}

