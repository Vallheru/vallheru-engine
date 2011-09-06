<article class="nine bows">
	<header>
		<h1>{$Title}</h1>
		{if $Buy == 0 && $Step == ""}
		    <p>
			{if $Location == "Altara"}
				{$Shopinfo} {$Archername} {$Shopinfo2}
			{/if}
			{if $Location == "Ardulith"}
				{$Shopinfo}
			{/if}
		    </p>
		{/if}
	</header>
	
	{if $Buy == 0 && $Step == ""}
		{if $Arrows > 0}
			{if $Step == ""}
				<form method="post" action="bows.php?arrows={$Arrowsid}&amp;step=buy">
					<input type="submit" value="{$Abuy}" /> <input type="text" name="arrows1" size="5" value="0" /> {$Tarrows} <b>{$Arrowsname}</b> {$Fora} <b>{$Arrowscost}</b> {$Tamount} <input type="text" name="arrows2" size="5" value="0" /> {$Tamount2} <b>{$Arrowsname}</b> {$Fora} <b>{$Arrowscost2}</b> {$Tamount3}
				</form>
			{/if}
		{/if}   
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
						<td>+{$Speed[item]}</td>
						<td>{$Durability[item]}</td>
						<td>{$Cost[item]}</td>
						<td>{$Level[item]}</td>
						<td>
							<ul>
								<li><a href="bows.php?{$Tlink[item]}{$Itemid[item]}">{$Abuy}</a></li>
								{if $Crime > "0"}<li><a href="bows.php?steal={$Itemid[item]}">{$Asteal}</a></li>{/if}
							</ul>
						</td>
					</tr>
				{/section}
			</tbody>
		</table>
	{/if}

	{if $Buy > 0 || $Step == "buy"}
		<p>{$Youbuy} <b>{$Cost}</b> {$Goldcoins} {$Tamount4} <b>{$Name}</b> {$With} <b>+{$Power}</b> {$Damage}</p>
	{/if}
	
</article>
