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
    {foreach $Plans as $Plans1}
    <div>
        <label for="mytoggle{$Plans1@iteration}" class="toggle">{$Links[$Plans1@key]}</label>
	<input id="mytoggle{$Plans1@iteration}" type="checkbox" class="toggle" {$Checked} />
	<div>
	    <table width="100%">
	        <tr>
	            <th>{$Iname}</th>
		    <th>{$Icost}</th>
		    <th>{$Ilevel}</th>
		    <th>{$Ioption}</th>
		</tr>
		{foreach $Plans1 as $Plan}
		    <tr>
		        <td>{$Plan.name}</td>
			<td>{$Plan.cost}</td>
			<td>{$Plan.level}</td>
			<td><a href="kowal.php?kowal=plany&amp;buy={$Plan.id}">{$Abuy}</a></td>
		    </tr>
		{/foreach}
	    </table>
	</div>
    </div>
    {/foreach}
{/if}

{if $Smith == "kuznia" || $Smith == "elite"}
    {if $Make == "" && $Continue == ""}
        {$Smithinfo}
        {if $Maked == ""}
            <ul>
	        {section name=smith3 loop=$Amake}
		    <li><a href="kowal.php?kowal={$Smith}&amp;type={$Atype[smith3]}">{$Amake[smith3]}</a></li>
		{/section}
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
            <input type="submit" value="{$Amake}" />{if $Next != ""} <b>{$Name}</b> {html_options name=mineral options=$Moptions}{/if}
        </form>
    {/if}
    {if $Continue != ""}
        {$Message2}
    {/if}
    {if $Make != ""}
        {$Message2}
	{if $Amt > 0}
	    <p>{$Youmade}</p>
	    {section name=maked loop=$Items}
	        {$Items[maked].name} (+ {$Items[maked].power}) ({$Items[maked].zr} {$Iagi}) ({$Items[maked].wt}/{$Items[maked].wt}) {$Iamount}: {$Amount[maked]}<br />
	    {/section}
	{/if}
    {/if}
{/if}

{if $Smith == "astral"}
    {$Smithinfo}<br /><br />
    {$Message2}<br /><br />
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

