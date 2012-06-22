<article class="nine wieza">
	<header>
		<h1>{$Title}</h1>
		<p>{$Towerinfo}</p>
	</header>
	
		<ul>
			<li><a href="wieza.php?dalej=T">{$Abuyst}</a></li>
			<li><a href="wieza.php?dalej=C">{$Abuyc}</a></li>
			<li><a href="wieza.php?dalej=B">{$Abuys}</a></li>
			<li><a href="wieza.php?dalej=O">{$Abuys2}</a></li>
			<li><a href="wieza.php?dalej=U">{$Abuys3}</a></li>
		</ul>
		
		{if $Next != ""}
			{if $Next == "B" || $Next == "O" || $Next == "U"}
			    {foreach $Spells as $Spells2}
			        <b>{$Telement} {$Spells2@key}</b><br />
				<table>
				    <thead>
				        <tr>
					    <th>{$Tname}</th>
					    <th>{$Tpower}</th>
					    <th>{$Tcost}</th>
					    <th>{$Tlevel}</th>
					    <th>{$Toptions}</th>
					</tr>
				    </thead>
				    <tbody>
					{foreach $Spells2 as $Spell}
					    <tr>
					        <td>{$Spell.name}</td>
					        <td>{$Spell.effect}</td>
					        <td>{$Spell.price}</td>
					        <td>{$Spell.level}</td>
					        <td>
						    <ul>
						        <li><a href="wieza.php?dalej={$Next}&amp;buy={$Spell.id}&amp;type=S">{$Abuy}</a></li>
						    </ul>
					        </td>
					    </tr>
					{/foreach}
				    </tbody>
				</table><br />
			    {/foreach}
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
										<li><a href="wieza.php?dalej={$Next}&amp;buy={$Itemid[tower1]}&amp;type=I">{$Abuy}</a></li>
									</ul>
								</td>
							</tr>
						{/section}
					</tbody>
				</table>
			{/if}
			
	{/if}
	
</article>
