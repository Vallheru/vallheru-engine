{$Listinfo}<br /><br />

<center><u>{$Secnames[0]}</u></center>
<table width="100%" class="dark">
    <tr>
        <td width="50%"><b>{$Ranknames[0]}</b></td>
        <td><b>{$Ranknames[1]}</b></td>
    </tr>
    <tr>
        <td valign="top">
            {section name=admins loop=$Stafflist[0][0]}
            <a href="view.php?view={$Stafflist[0][0][admins]}">{$Stafflist[0][1][admins]}</a> ID: {$Stafflist[0][0][admins]}<br />
            {/section}
        </td>
        <td valign="top">
            {section name=staff loop=$Stafflist[1][0]}
            <a href="view.php?view={$Stafflist[1][0][staff]}">{$Stafflist[1][1][staff]}</a> ID: {$Stafflist[1][0][staff]}<br />
            {/section}
        </td>
    </tr>
    <tr>
        <td><br /><br /></td>
        <td><br /><br /></td>
    </tr>
    <tr>
        <td><b>{$Ranknames[2]}</b></td>
        <td><b>{$Ranknames[3]}</b></td>
    </tr>
    <tr>
        <td valign="top">
            {section name=innkeeper loop=$Stafflist[2][0]}
            <a href="view.php?view={$Stafflist[2][0][innkeeper]}">{$Stafflist[2][1][innkeeper]}</a> ID: {$Stafflist[2][0][innkeeper]}<br />
            {/section}
        </td>
        <td valign="top">
            {section name=librarian loop=$Stafflist[3][0]}
            <a href="view.php?view={$Stafflist[3][0][librarian]}">{$Stafflist[3][1][librarian]}</a> ID: {$Stafflist[3][0][librarian]}<br />
            {/section}
        </td>
    </tr>
</table><br /><br />

<center><u>{$Secnames[1]}</u></center>
<table width="100%" class="dark">
    <tr>
        <td width="50%"><b>{$Ranknames[4]}</b></td>
        <td><b>{$Ranknames[5]}</b></td>
    </tr>
    <tr>
        <td valign="top">
            {section name=mcount loop=$Stafflist[4][0]}
            <a href="view.php?view={$Stafflist[4][0][mcount]}">{$Stafflist[4][1][mcount]}</a> ID: {$Stafflist[4][0][mcount]}<br />
            {/section}
        </td>
        <td valign="top">
            {section name=count loop=$Stafflist[5][0]}
            <a href="view.php?view={$Stafflist[5][0][count]}">{$Stafflist[5][1][count]}</a> ID: {$Stafflist[5][0][count]}<br />
            {/section}
        </td>
    </tr>
</table><br /><br />

<center><u>{$Secnames[3]}</u></center>
<table width="100%" class="dark">
    <tr>
        <td><b>{$Ranknames[6]}</b></td>
    </tr>
    <tr>
        <td valign="top">
            {section name=redactor loop=$Stafflist[6][0]}
            <a href="view.php?view={$Stafflist[6][0][redactor]}">{$Stafflist[6][1][redactor]}</a> ID: {$Stafflist[6][0][redactor]}<br />
            {/section}
        </td>
    </tr>
</table><br /><br />

<center><u>{$Secnames[2]}</u></center><br /><br />
{$Sec3desc} <a href="court.php">{$Sec3desc2}</a><br /><br />

<center><u>{$Secnames[4]}</u></center>
<table width="100%" class="dark">
    <tr>
        <td width="50%"><b>{$Ranknames[7]}</b></td>
        <td><b>{$Ranknames[8]}</b></td>
    </tr>
    <tr>
        <td valign="top">
            {section name=knight loop=$Stafflist[7][0]}
            <a href="view.php?view={$Stafflist[7][0][knight]}">{$Stafflist[7][1][knight]}</a> ID: {$Stafflist[7][0][knight]}<br />
            {/section}
        </td>
        <td valign="top">
            {section name=lady loop=$Stafflist[8][0]}
            <a href="view.php?view={$Stafflist[8][0][lady]}">{$Stafflist[8][1][lady]}</a> ID: {$Stafflist[8][0][lady]}<br />
            {/section}
        </td>
    </tr>
</table>