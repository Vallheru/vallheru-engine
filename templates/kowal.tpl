<article class="nine kowal">
	{if $Smith == ""}
		<header>
			<h1>{$Title}</h1>
			<p>{$Smithinfo}</p>
		</header>
		
		<ul class="submenu">
			<li><a href="kowal.php?kowal=plany">{$Aplans}</a></li>
			<li><a href="kowal.php?kowal=kuznia">{$Asmith}</a></li>
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
	
	{if $Smith == "kuznia"}
		{if $Make == "" && $Continue == ""}
			<header>
				<h1>{$Title}</h1>
				<p>{$Smithinfo}</p>
			</header>
			{if $Maked == ""}
				<ul>
					<li><a href="kowal.php?kowal=kuznia&amp;type=W">{$Amakew}</a></li>
					<li><a href="kowal.php?kowal=kuznia&amp;type=A">{$Amakea}</a></li>
					<li><a href="kowal.php?kowal=kuznia&amp;type=S">{$Amakes}</a></li>
					<li><a href="kowal.php?kowal=kuznia&amp;type=H">{$Amakeh}</a></li>
					<li><a href="kowal.php?kowal=kuznia&amp;type=L">{$Amakel}</a></li>
				</ul>
				{if $Type != ""}
					{$Info}:
					<table>
						<thead>
							<tr>
								<th>{$Iname}</th>
								<th>{$Ilevel}</th>
								<th>{$Iamount}</th>
							</tr>
						</thead>
						<tbody>
							{section name=smith2 loop=$Name}
								<tr>
									<td><a href="kowal.php?kowal=kuznia&amp;dalej={$Id[smith2]}">{$Name[smith2]}</a></td>
									<td>{$Level[smith2]}</td>
									<td>{$Amount[smith2]}</td>
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
							<td><a href="kowal.php?kowal=kuznia&amp;ko={$Id}">{$Name}</a></td>
							<td>{$Percent}</td>
							<td>{$Need}</td>
						</tr>
					</tbody>
				</table>
			{/if}
		{/if}
		{if $Cont != "" || $Next != ""}
			<form method="post" action="{$Link}">
				{$Assignen} <b>{$Name}</b> <input type="text" name="razy" />{$Senergy}
				<input type="submit" value="{$Amake}" />
				{if $Next != ""} <b>{$Name}</b> 
				<select name="mineral">
					<option value="copper">{$Mcopper}</option>
					<option value="bronze">{$Mbronze}</option>
					<option value="brass">{$Mbrass}</option>
					<option value="iron">{$Miron}</option>
					<option value="steel">{$Msteel}</option>
				</select>
				{/if}
			</form>
		{/if}
		{if $Continue != ""}
			<p>{$Message}</p>
		{/if}
		{if $Make != ""}
			<p>{$Message}</p>
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
