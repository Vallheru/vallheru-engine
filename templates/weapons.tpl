<article class="nine weapons">
	<header>
		<h1>{$Title}</h1>
		<p>{$Weaponinfo}</p>
	</header>
	
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
			{foreach $Weapons as $weapon}
				<tr>
					<td>{$weapon.name}</td>
					<td>+{$weapon.power} Atak</td>
					<td>+{$weapon.szyb}%</td>
					<td>{$weapon.maxwt}</td>
					<td>{$weapon.cost}</td>
					<td>{$weapon.minlev}</td>
					<td>
						<ul>
							<li><a href="weapons.php?buy={$weapon.id}">{$Abuy}</a></li>{if $Crime > "0"}
							<li><a href="weapons.php?steal={$weapon.id}">{$Asteal}</a></li>{/if}
						</ul>
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	
</article>

