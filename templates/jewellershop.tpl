<article class="nine jewellershop">
	<header>
		<h1>{$Title}</h1>
		<p>{$Shopinfo}</p>
	</header>
	
	<table>
		<thead>
			<tr>
				<td>{$Tname}</td>
				<td>{$Tbonus}</td>
				<td>{$Tamount}</td>
				<td>{$Tcost}</td>
				<td>{$Taction}</td>
			</tr>
		</thead>
		<tbody>
			{section name=jeweller loop=$Rid}
				<tr>
					<td>{$Rname[jeweller]}</td>
					<td>1</td>
					<td>{$Ramount[jeweller]}</td>
					<td>500</td>
					<td><a href="jewellershop.php?buy={$Rid[jeweller]}">{$Abuy}</td>
				</tr>
			{/section}
		</tbody>
	</table>
	
	<p>{$Message}</p>
	
</article	>