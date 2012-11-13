<article class="nine bows">
	<header>
		<h1>{$Title}</h1>
		    <p>
			{if $Location == "Altara"}
				{$Shopinfo}{$Shopinfo2}
			{/if}
			{if $Location == "Ardulith"}
				{$Shopinfo}
			{/if}
		    </p>
	</header>
	
		{if $Arrows > 0}
		    <script src="js/bows.js"></script>
				<form method="post" action="bows.php?arrows={$Arrowsid}&amp;step=buy">
					<input type="submit" value="{$Abuy}" /> <input type="text" name="arrows1" size="5" value="0" onChange="countPrice(this.value, {$Arrowscost}, 'Q');" /> {$Tarrows} <b>{$Arrowsname}</b> {$Fora} <b><span id="quivercost">0</span></b> {$Tamount} <input type="text" name="arrows2" size="5" value="0" onChange="countPrice(this.value, {$Arrowscost2}, 'A');" /> {$Tamount2} <b>{$Arrowsname}</b> {$Fora} <b><span id="arrowcost">0</span></b> {$Tamount3}
				</form>
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
	
</article>
