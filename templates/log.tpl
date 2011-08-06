{$Loginfo}<br /><br />

{if $Send != ""}
    <form method="post" action="log.php?send&amp;step=send">
    {$Sendthis}: <select name="staff">
    {section name=log1 loop=$Name}
        <option value="{$StaffId[log1]}">{$Name[log1]}</option>
    {/section}
    </select><br />
    <input type="hidden" name="lid" value="{$Send}" />
    <input type="submit" value="{$Asend}" /></form>
{/if}

{if $LogId[0] != "0"}
	<table align="center" width="50%">
		<tr>
			{if $Previous != ""}
			<td align="left">{$Previous}</td>
			{/if}
			{if $Next != ""}
			<td align="right">{$Next}</td>
			{/if}
		</tr>
	</table>
    <form method="post" action="log.php?action=delete">
        <table>
            {section name=log loop=$Date}
                <tr>
                    <td><input type="checkbox" name="{$LogId[log]}" /></td>
                    <td><b>{$Event}:<br />
                        </b>{$Edate}:{$Date[log]}<br />
                        {$Text[log]}<br />
                        <a href="log.php?send={$LogId[log]}">{$Sendevent}</a><br /><br />
                    </td>
                </tr>
            {/section}
        </table><br />
        <input type="submit" value="{$Adelete}" />
    </form>
    <form method="post" action="log.php?step=deleteold">
        <input type="submit" value="{$Adeleteold}" /> <select name="oldtime">
            <option value="7">{$Aweek}</option>
            <option value="14">{$A2week}</option>
            <option value="30">{$Amonth}</option>
        </select>
    </form>
    <a href="log.php?akcja=wyczysc">({$Clearlog})</a><br />
{/if}
