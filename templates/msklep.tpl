<article class="nine msklep">
	<header>
		<h1>{$Title}</h1>
		{if $Buy == ""}
		    <p>{$Pwelcome}</p>
		{/if}
	</header>
	
	{if $Buy == ""}
		<table>
			<thead>
				<tr>
					<th>{$Nname}</th>
					<th>{$Nefect}</th>
					<th>{$Ncost}</th>
					<th>{$Namount}</th>
					<th>{$Poption}</th>
				</tr>
			</thead>
			<tbody>
				{section name=msklep loop=$Pid}
					<tr>
						<td>{$Pname[msklep]}({$Npower}:{$Ppower[msklep]})</td>
						<td>{$Pefect[msklep]}</td>
						<td>{$Pcost[msklep]}</td>
						<td>{$Pamount[msklep]}</td>
						<td>
							<ul>
								<li><a href="msklep.php?buy={$Pid[msklep]}">{$Abuy}</a></li>
							</ul>
						</td>
					</tr>
				{/section}
			</tbody>
		</table>
	{/if}
	
	{if $Buy != ""}
		<form method="post" action="msklep.php?buy={$Pid}&amp;step=buy">
			<input type="submit" value="{$Abuy}" /> <input type="text" name="amount" value="1" /> {$Pamount} {$Name}
		</form>
	{/if}

</article>
