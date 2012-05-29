<article class="nine wieza">
	<header>
		<h1>{$Title}</h1>
		{if $Buy == ""}
		    <p>{$Towerinfo}</p>
		{/if}
	</header>
	
	{if $Buy == ""}
		<ul>
			<li><a href="wieza.php?dalej=T">{$Abuyst}</a></li>
			<li><a href="wieza.php?dalej=C">{$Abuyc}</a></li>
			<li><a href="wieza.php?dalej=B">{$Abuys}</a></li>
			<li><a href="wieza.php?dalej=O">{$Abuys2}</a></li>
			<li><a href="wieza.php?dalej=U">{$Abuys3}</a></li>
		</ul>
		
		{if $Next != ""}
			{if $Next == "B" || $Next == "O" || $Next == "U"}
				<table>
					<thead>
						<tr>
							<th>{$Tname}</th>
							<th>{$Tpower}</th>
							<th>{$Tcost}</th>
							<th>{$Tlevel}</th>
							<th>{$Telement}</th>
							<th>{$Toptions}</th>
						</tr>
					</thead>
					<tbody>
						{section name=tower loop=$Spells}
							<tr>
								<td>{$Spells[tower].nazwa}</td>
								<td>{$Spells[tower].effect}</td>
								<td>{$Spells[tower].cena}</td>
								<td>{$Spells[tower].poziom}</td>
								<td>{$Spells[tower].element}</td>
								<td>
									<ul>
										<li><a href="wieza.php?buy={$Itemid[tower]}&amp;type=S">{$Abuy}</a></li>
									</ul>
								</td>
							</tr>
						{/section}
					</tbody>
				</table>
			{else}
				<table>
					<thead>
						<tr>
							<th>{$Tname}</td>
							<th>{$Tpower}</td>
							<th>{$Tcost}</td>
							<th>{$Tlevel}</td>
							<th>{$Toptions}</td>
						</tr>
					</thead>
					<tbody>
						{section name=tower1 loop=$Name}
							<tr>
								<td>{$Name[tower1]}</td>
								<td>{$Power[tower1]}</td>
								<td>{$Cost[tower1]}</td>
								<td>{$Itemlevel[tower1]}</td>
								<td>
									<ul>
										<li><a href="wieza.php?buy={$Itemid[tower1]}&amp;type=I">{$Abuy}</a></li>
									</ul>
								</td>
							</tr>
						{/section}
					</tbody>
				</table>
			{/if}
			
		{/if}
	{/if}
	
	{if $Buy != ""}
		{$Message}
	{/if}
	
</article>
