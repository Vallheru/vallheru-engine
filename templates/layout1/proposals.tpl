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

{if $Type == "M"}
    <form method="post" action="proposals.php?type=M&amp;send">
        {$Tpoints} {$Points}<br />
	{$Pinfo}<br /><br />
	{$Tname} <input type="text" name="mname" value="{$Values[0]}" /><br />
	{$Tloc} {html_options name=loc options=$Coptions}<br />
	{$Tstr} {html_options name=mstr options=$Soptions selected=$Values[5]}<br />
	{$Tagi} {html_options name=magi options=$Soptions selected=$Values[6]}<br />
	{$Tspeed} {html_options name=mspeed options=$Soptions selected=$Values[7]}<br />
	{$Tcon} {html_options name=mcon options=$Soptions selected=$Values[8]}<br />
	{$Tresistance} {html_options name=mres options=$Roptions selected=$Values[9]}<br />
	{$Tdmgtype} {html_options name=mdmg options=$Doptions selected=$Values[10]}<br />
	{$Tloot1} <input type="text" name="loot1" value="{$Values[1]}" /><br />
	{$Tloot2} <input type="text" name="loot2" value="{$Values[2]}" /><br />
	{$Tloot3} <input type="text" name="loot3" value="{$Values[3]}" /><br />
	{$Tloot4} <input type="text" name="loot4" value="{$Values[4]}" /><br />
	{$Linfo}<br /><br />
	<input type="submit" value="{$Acheck}" name="smon" /> <input type="submit" value="{$Asend}" name="smon" />
    </form>
{/if}

{if $Type == "B"}
    <form method="post" action="proposals.php?type=B&amp;send">
        {$Tquestion}<br />
	<textarea name="question" cols="50"></textarea><br /><br />
	{$Tanswer}<br />
	<textarea name="answer" cols="50"></textarea><br />
	{$Tinfo}<br /><br />
	<input type="submit" value="{$Asend}" />
    </form>
{/if}

{if $Type == "E"}
    <form method="post" action="proposals.php?type=E&amp;send">
        {$Tselect} {html_options name=loc options=$Moptions}<br /><br />
	{$Pdesc}
	<b>{$Tdesc}</b> <br /><textarea name="desc" rows="15" cols="70">{$Desc}</textarea><br />
	{$Hdesc}<br /><br />
	<input type="submit" value="{$Apreview}" name="sdesc" /> <input type="submit" value="{$Asend}" name="sdesc" />
    </form>
{/if}
