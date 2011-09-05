<article class="nine add-update">
	<header>
		<h1>{$Title}</h1>
		<p>{$Welcome}</p>
	</header>
	
	<form method="post" action="{$Link}">
		<label>
			<span>{$Ulangsel}:</span>
			{if $Link == "addupdate.php?action=add"}
				<select name="addlang">
					{section name=addupdate loop=$Ulang}
						<option value="{$Ulang[addupdate]}">{$Ulang[addupdate]}</option>
					{/section}
				</select>
			{/if}
		</label>
		<label>
			<span>{$Utitle}:</span>
			<input type="text" name="addtitle" value="{$Title1}" />
		</label>
		<label>
			<span>{$Utext}:</span>
			<textarea id="addupdate" name="addupdate">{$Text}</textarea>
		</label>
		<div class="buttons">
			<input class="normal-button" type="submit" value="{$Button}" />
		</div>
	</form>
	
</article>

