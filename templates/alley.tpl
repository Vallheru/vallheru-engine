<article class="nine alley">
	<header>
		<h1>{$Title}</h1>
		<p>{$Alleyinfo}</p>
	</header>
	
	<ol>
		{section name=donators loop=$Donators}
			<li>{$Donators[donators]}</li>
		{/section}
	</ol>
	
</article>