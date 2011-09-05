<article class="nine addnews">
	<header>
		<h1>{$Title}</h1>
		<p>{$Welcome}</p>
	</header>
	
	<section>
		<form method="post" action="addnews.php?action=add">
			<label>
				<span>{$Nlangsel}:</span>
				<select id="addlang" name="addlang">
					{section name=addnews loop=$Nlang}
						<option value="{$Nlang[addnews]}">{$Nlang[addnews]}</option>
					{/section}
				</select>
			</label>
			
			<label>
				<span>{$Ntitle}:</span>
				<input type="text" name="addtitle" value="" />
			</label>
			
			<label>
				<span>{$Ntext}:</span>
				<textarea id="addnews" name="addnews"></textarea>
			</label>
			
			<div class="buttons">
				<input class="normal-button" type="submit" value="{$Nadd}" />
			</div>
		</form>
	</section>

</article>
