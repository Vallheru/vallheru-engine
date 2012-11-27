{if $Pteam == 0}
    <center>{$Noteam} <a href="team.php?create">{$Acreate}</a> {$Noteam2}</center>
{else}
    {$Tinfo}<br />
    <ul>
        <li>{$Tleader} <a href="view.php?view={$Leaderid}">{$Tleader2}</a></li>
    </ul>
    <table align="center" width="50%">
        <tr>
	    <th>{$Tmembers}</th>
	    <th>{$Tstatus}</th>
	    <th>{$Tactions}</th>
	</tr>
	{foreach $Tmembers2 as $Member}
	    <tr>
	        <td><a href="view.php?view={$Member.id}">{$Member.user}</a></td>
		<td>{$Member.status}</td>
		<td>{if $Member.action != ""}<a href="team.php?kick={$Member.id}">{$Member.action}{/if}</a></td>
	    </tr>
	{/foreach}
    </table>
    <a href="team.php?left">{$Aleft}</a>
{/if}
