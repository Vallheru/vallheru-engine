<article class="nine kowal">
	{if $Smith == ""}
		<header>
			<h1>{$Title}</h1>
			<p>{$Smithinfo}</p>
		</header>
		
		<ul class="submenu">
			<li><a href="kowal.php?kowal=plany">{$Aplans}</a></li>
			<li><a href="kowal.php?kowal=kuznia">{$Asmith}</a></li>
			{if $Aelite != ''}
        		    <li><a href="kowal.php?kowal=elite">{$Aelite}</a></li>
    			{/if}
			{if $Astral == "Y"}<li><a href="kowal.php?kowal=astral">{$Aastral}</a></li>{/if}
		</ul>
	{/if}
	
	{if $Smith == "plany"}
		<header>
			<h1>{$Title}</h1>
			<p>{$Plansinfo}</p>
		</header>
		
		<ul>
			<li><a href="kowal.php?kowal=plany&amp;dalej=W">{$Aplansw}</a></li>
			<li><a href="kowal.php?kowal=plany&amp;dalej=A">{$Aplansa}</a></li>
			<li><a href="kowal.php?kowal=plany&amp;dalej=S">{$Aplanss}</a></li>
			<li><a href="kowal.php?kowal=plany&amp;dalej=H">{$Aplansh}</a></li>
			<li><a href="kowal.php?kowal=plany&amp;dalej=L">{$Aplansl}</a></li>
		</ul>
		{if $Next != ""}
			{$Hereis}:
			<table>
				<thead>
					<tr>
						<th>{$Iname}</th>
						<th>{$Icost}</th>
						<th>{$Ilevel}</th>
						<th>{$Ioption}</th>
					</tr>
				</thead>
				<tbody>
					{section name=smith loop=$Name}
						<tr>
							<td>{$Name[smith]}</td>
							<td>{$Cost[smith]}</td>
							<td>{$Level[smith]}</td>
							<td>
								<ul>
									<li><a href="kowal.php?kowal=plany&amp;buy={$Id[smith]}">{$Abuy}</a></li>
								</ul>
							</td>
						</tr>
					{/section}
				</tbody>
			</table>
		{/if}
		{if $Buy != ""}
			<p>{$Youpay} <b>{$Cost}</b> {$Andbuy}: <b>{$Plan}</b>.</p>
		{/if}
	{/if}
	
	{if $Smith == "kuznia" || $Smith == "elite"}
		{if $Make == "" && $Continue == ""}
			<header>
				<h1>{$Title}</h1>
				<p>{$Smithinfo}</p>
			</header>
			{if $Maked == ""}
				<ul>
				    {section name=smith3 loop=$Amake}
				        <li><a href="kowal.php?kowal={$Smith}&amp;type={$Atype[smith3]}">{$Amake[smith3]}</a></li>
				    {/section}
				</ul>
				{if $Type != ""}
					{$Info}:
					<table>
						<thead>
							<tr>
								<th>{$Iname}</th>
								<th>{$Ilevel}</th>
								<th>{$Iamount}</th>
								{if $Smith == "elite"}
	         				    		    <th>{$Tloot}</th>
             							{/if}
							</tr>
						</thead>
						<tbody>
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
						</tbody>
					</table>
				{/if}
			{/if}
			{if $Maked != ""}
				{$Info3}:
				<table>
					<thead>
						<tr>
							<th>{$Iname}</th>
							<th>{$Ipercent}</th>
							<th>{$Ienergy}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><a href="kowal.php?kowal={$Smith}&amp;ko={$Id}">{$Name}</a></td>
							<td>{$Percent}</td>
							<td>{$Need}</td>
						</tr>
					</tbody>
				</table>
			{/if}
		{/if}
		{if $Cont != "" || $Next != ""}
			<form method="post" action="{$Link}">
				{$Assignen} <b>{$Name}</b> <input type="text" name="razy" size="5" />{$Senergy}
				<input type="submit" value="{$Amake}" />
				{if $Next != ""} <b>{$Name}</b> 
				{html_options name=mineral options=$Moptions}
				{/if}
			</form>
		{/if}
		{if $Continue != ""}
			<p>{$Message}</p>
		{/if}
		{if $Make != ""}
			<p>{$Message}</p>
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
				<input type="submit" value="{$Abuild}" />
			</form>
		{/section}
	{/if}
	
	{if $Smith != ""}
		<p><a href=kowal.php>({$Aback})</a></p>
	{/if}
	
</article>
