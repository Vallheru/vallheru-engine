{if $Type == "D"}
    <form method="post" action="proposals.php?type=D&amp;send">
        {$Tselect} {html_options name=loc options=$Loptions}<br /><br />
	<b>{$Tdesc}</b> <br /><textarea name="desc" rows="15" cols="70"></textarea><br />
	{$Hdesc}<br /><br />
	<b>{$Tinfo}</b> <br /><textarea name="info" rows="15" cols="70"></textarea><br />
	{$Hinfo}<br /><br />
	<input type="submit" value="{$Asend}" />
    </form>
{/if}
