<article class="nine armor">
	<header>
		<h1>{$Title}</h1>
	    	<p>{$Armorinfo}</p>
	</header>
	
	<ul class="submenu">
		<li><a href="armor.php?dalej=A">{$Aarmors}</a></li>
		<li><a href="armor.php?dalej=H">{$Ahelmets}</a></li>
		<li><a href="armor.php?dalej=L">{$Alegs}</a></li>
		<li><a href="armor.php?dalej=S">{$Ashields}</a></li>
	</ul>
	
	{if $Next != ''}
		<table>
			<thead>
				<tr>
					<th>{$Iname}</th>
					<th>{$Idur}</th>
					<th>{$Iefect}</th>
					<th>{$Icost}</th>
					<th>{$Ilevel}</th>
					<th>{$Iagi}</th>
					<th>{$Ioption}</th>
				</tr>
			</thead>
			<tbody>
				{section name=number loop=$Name}
					<tr>
						<td>{$Name[number]}</td>
						<td>{$Durability[number]}</td>
						<td>+{$Power[number]} Obrona</td>
						<td>{$Cost[number]}</td>
						<td>{$Level[number]}</td>
						<td>{$Agility[number]}</td>
						<td>
							<ul>
								<li><a href="armor.php?dalej={$Next}&amp;buy={$Id[number]}">{$Abuy}</a></li>
								{if $Crime > "0"}<li><a href="armor.php?steal={$Id[number]}">{$Asteal}</a></li>{/if}
							</ul>
						</td>
					</tr>
				{/section}
			</tbody>
		</table>
	{/if}
</article>
