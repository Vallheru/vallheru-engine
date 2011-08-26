{$Ref1} (<i>{$Adress}/register.php?ref={$Id}</i>) {$Ref2} {$Linkinfo} {$Ref4} {$Owner} <b>{$Refs}</b> {$Ref3}.<br />
<table align="center" width="100%">
    <tr>
        <th width="20%">{$Tdate}</th>
	<th width="10%">{$Tamount}</th>
	<th>{$Treason}</th>
    </tr>
    {section name=log loop=$Date}
    <tr>
        <td>{$Date[log]}</td>
	<td>{$Amount[log]}</td>
	<td>{$Reason[log]}</td>
    </tr>
    {/section}
</table>
