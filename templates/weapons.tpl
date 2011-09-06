<article class="nine weapons">
	<header>
		<h1>{$Title}</h1>
		{if $Buy == ""}
		    <p>{$Weaponinfo}</p>
		{/if}
	</header>
	
	{if $Buy == ""}
		<table>
			<thead>
				<tr>
					<th>{$Iname}</th>
					<th>{$Iefect}</th>
					<th>{$Ispeed}</th>
					<th>{$Idur}</th>
					<th>{$Icost}</th>
					<th>{$Ilevel}</th>
					<th>{$Ioption}</th>
				</tr>
			</thead>
			<tbody>
				{section name=item loop=$Level}
					<tr>
						<td>{$Name[item]}</td>
						<td>+{$Power[item]} Atak</td>
						<td>+{$Speed[item]}%</td>
						<td>{$Durability[item]}</td>
						<td>{$Cost[item]}</td>
						<td>{$Level[item]}</td>
						<td>
							<ul>
								<li><a href="weapons.php?buy={$Itemid[item]}">{$Abuy}</a></li>{if $Crime > "0"}
								<li><a href="weapons.php?steal={$Itemid[item]}">{$Asteal}</a></li>{/if}
							</ul>
						</td>
					</tr>
				{/section}
			</tbody>
		</table>
	{/if}
	
	{if $Buy > 0}
		{$Youpay} <b>{$Cost}</b> {$Andbuy} <b>{$Name} {$Withp} +{$Power}</b> {$Topower}
	{/if}
	
</article>

