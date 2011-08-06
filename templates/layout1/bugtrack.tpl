{$Buginfo}
<form method="post" action="bugtrack.php?action=delete">
<table width="80%">
<tr>
    <td>{$Adelete}</td>
    <td>{$Bid2}</td>
    <td>{$Btype2}</td>
    <td>{$Bfile2}</td>
    <td>{$Bref}</td>
    <td>{$Bline2}</td>
    <td>{$Binfo2}</td>
    <td>{$Bamount2}</td>
</tr>
{section name=bugtrack loop=$Bamount}
    <tr>
        <td><input type="checkbox" name="{$Bid[bugtrack]}" /></td>
        <td>{$Bid[bugtrack]}</td>
        <td>{$Btype[bugtrack]}</td>
        <td>{$Bfile[bugtrack]}</td>
        <td>{$Brefer[bugtrack]}</td>
        <td>{$Bline[bugtrack]}</td>
        <td>{$Binfo[bugtrack]}</td>
        <td>{$Bamount[bugtrack]}</td>
    </tr>
{/section}
<tr>
    <td colspan="7" align="center"><input type="submit" value="{$Adelete}" /></td>
</tr>
</table>
</form>
{$Message}
