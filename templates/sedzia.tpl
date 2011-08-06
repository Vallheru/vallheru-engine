<form method=post action="sedzia.php?step=add">
	{$Adda} <input type="text" name="aid" /> {$Asa}
	<select name="rank">
	<option value="Member">{$Rmember}</option>
	<option value="Prawnik">{$Rlawyer}</option>
	<option value="Ławnik">{$Rjudge}</option>
	</select>. <input type="submit" value="{$Aadd}" />
	</form>
