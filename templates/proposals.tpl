{if $Type == "D"}
    <form method="post" action="proposals.php?type=D&amp;send">
        {$Tselect} {html_options name=loc options=$Loptions}<br /><br />
	{$Pdesc}
	<b>{$Tdesc}</b> <br /><textarea name="desc" rows="15" cols="70">{$Desc}</textarea><br />
	{$Hdesc}<br /><br />
	<b>{$Tinfo}</b> <br /><textarea name="info" rows="15" cols="70">{$Info}</textarea><br />
	{$Hinfo}<br /><br />
	<input type="submit" value="{$Apreview}" name="sdesc" /> <input type="submit" value="{$Asend}" name="sdesc" />
    </form>
{/if}

{if $Type == "I"}
    <form method="post" action="proposals.php?type=I&amp;send">
        {$Tname} <input type="text" name="iname" size="20" /><br />
	{$Ninfo}<br /><br />
	{$Ttype} {html_options name=itype options=$Toptions}<br />
	{$Tinfo}<br /><br />
	{$Tlevel} <input type="text" name="level" size="5" /><br />
	{$Linfo}<br /><br />
	<input type="submit" value="{$Asend}" />
    </form>
{/if}
