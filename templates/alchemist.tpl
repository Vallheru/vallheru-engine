<article class="nine alchemist">
	{if $Alchemist == ''}
		<header>
			<h1>{$Title}</h1>
			<p>{$Awelcome}</p>
		</header>
		
		<ul class="submenu">
			<li><a href="alchemik.php?alchemik=przepisy">{$Arecipes}</a></li>
			<li><a href="alchemik.php?alchemik=pracownia">{$Amake}</a></li>
			{if $Astral == "Y"}<li><a href="alchemik.php?alchemik=astral">{$Aastral}</a></li>{/if}
		</ul>
	{/if}
	
	{if $Alchemist == "przepisy"}
		<header>
			<h1>{$Title}</h1>
			<p>{$Recipesinfo}</p>
		</header>
		
		<table>
			<thead>
				<tr>
					<th>{$Rname}</th>
					<th>{$Rcost}</th>
					<th>{$Rlevel}</th>
					<th>{$Roption}</th>
				</tr>
			</thead>
			<tbody>
				{section name=alchemy loop=$Name}
					<tr>
						<td>{$Name[alchemy]}</td>
						<td>{$Cost[alchemy]}</td>
						<td>{$Level[alchemy]}</td>
						<td>
							<ul>
								<li><a href="alchemik.php?alchemik=przepisy&amp;buy={$Id[alchemy]}">{$Abuy}</a></li>
							</ul>
						</td>
					</tr>
				{/section}
			</tbody>
		</table>
	{/if}
	
	{if $Alchemist == "pracownia"}
		{if $Make == 0}
			<header>
				<h1>{$Title}</h1>
				<p>{$Alchemistinfo}</p>
			</header>
			
			<table>
				<thead>
					<tr>
						<th>{$Rname}</th>
						<th>{$Rlevel}</th>
						<th>{$Rillani}</th>
						<th>{$Rillanias}</th>
						<th>{$Rnutari}</th>
						<th>{$Rdynallca}</th>
					</tr>
				</thead>
				<tbody>
					{foreach $Plans as $plan}
						<tr>
							<td><a href="alchemik.php?alchemik=pracownia&amp;dalej={$plan.id}">{$plan.name}</a></td>
							<td>{$plan.level}</td>
							<td>{$plan.illani}</td>
							<td>{$plan.illanias}</td>
							<td>{$plan.nutari}</td>
							<td>{$plan.dynallca}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		{/if}
		{if $Next != 0}
			<form method="post" action="alchemik.php?alchemik=pracownia&amp;rob={$Id1}">
				{$Pstart} <input type="submit" value="{$Amake}" /> <b>{$Name1}</b> <input type="text" name="razy" size="5" value="{$Tamount}" /> {$Pamount}. {$Therb}
			</form>
		{/if}
		{if $Make != 0}
			<p>{$Youmake} <b>{$Name}</b> <b>{$Amount}</b> {$Pgain} <b>{$Exp}</b> {$Exp_and}</p>
			<p>{$Youmade}</p>
			<p>{foreach $Imaked as $value}
			    {$value@key} ({$Ipower}: {$value[0]}) {$Iamount}: {$value[1]}<br />
			{/foreach}</p>
		{/if}
	{/if}
	
	{if $Alchemist == "astral"}
		<header>
			<h1>{$Title}</h1>
			<p>{$Awelcome}</p>
			<p>{$Message}</p>
		</header>
		
		{section name=astral loop=$Aviablecom}
			<b>{$Tname}:</b> {$Aviablecom[astral]}<br />
			{section name=astral2 loop=$Mineralsname}
				<b>{$Mineralsname[astral2]}:</b> {$Minamount[astral][astral2]}<br />
			{/section}
			<form method="post" action="alchemik.php?alchemik=astral&amp;potion={$Compnumber[astral]}">
				<input type="submit" value="{$Abuild}" />
			</form>
		{/section}
	{/if}
	
	{if $Alchemist != ''}
		<a href="alchemik.php">({$Back})</a>
	{/if}
</article>
