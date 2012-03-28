{$Statsinfo}<br /><br />
{$Avatar}
<table width="100%">
<tr><td width="50%" valign="top">
    <center><b><u>{$Tstats}</u></b></center><br />
    <b>{$Tap}:</b> {$Ap}
    <b>{$Trace}:</b> {$Race}
    <b>{$Tclass}:</b> {$Clas}
    <b>{$Tdeity}:</b> {$Deity}
    <b>{$Tgender}:</b> {$Gender}
    {section name=stats1 loop=$Tstats2}
        <b>{$Tstats2[stats1]}:</b> {$Stats[stats1]} {$Curstats[stats1]}
    {/section}
    <b>{$Tmana}:</b> {$Mana} {$Rest}
    <b>{$Tpw}:</b> {$PW}
    <b>{$Tenergy}:</b> {$Energy}
    <b>{$Blessfor}</b>{$Pray} <b>{$Blessval}</b>
    <b>{$Effect}</b> {$Antidote}<br />
    {$Crime}<br />
    <b>{$Tfights}:</b> {$Total}
    <b>{$Tlast}:</b> {$Lastkilled}
    <b>{$Tlast2}:</b>  {$Lastkilledby}<br /><br />
    <b>{$Tmissions}:</b> {$Mpoints}<br />
</td><td width="50%" valign="top">
    <center><b><u>{$Tinfo}</u></b></center><br />
        <b>{$Trank}:</b> {$Rank}
        <b>{$Tloc}:</b> {$Location}
        <b>{$Tage}:</b> {$Age}
        <b>{$Tlogins}:</b> {$Logins}
        <b>{$Tip}:</b> {$Ip}
        <b>{$Temail}:</b> {$Email}
	{$GG}
	{if $Newbie > 0}
	<b>{$Tnewbie}:</b> {$Newbie} {if $Newbie > 1}{$Tdays}{else}{$Tday}{/if} (<a href="stats.php?action=newbie">{$Adisable}</a>)<br />
	{/if}
        <b>{$Tclan}:</b> {$Tribe}
        {$Triberank}
</td></tr><tr>
<td width="50%" valign="top" colspan="2">
    <center><b><u>{$Tability}</u></b></center><br />
        <table align="center" valign="top">
        <tr>
        <td><b>{$Tsmith}:</b> {$Smith}
        <b>{$Talchemy}:</b> {$Alchemy}
        <b>{$Tlumber}:</b> {$Fletcher}
        <b>{$Tfight}:</b> {$Attack}
        <b>{$Tbreeding}:</b> {$Breeding}
        <b>{$Tlumberjack}:</b> {$Lumberjack}
        <b>{$Tjeweller}:</b> {$Jeweller}
	{$Thievery}</td>
        <td valign="top"><b>{$Tshoot}:</b> {$Shoot}
        <b>{$Tdodge}:</b> {$Miss}
        <b>{$Tcast}:</b> {$Magic}
        <b>{$Tleader}:</b> {$Leadership}
        <b>{$Tmining}:</b> {$Mining}
        <b>{$Therbalist}:</b> {$Herbalist}
	<b>{$Tmetallurgy}:</b> {$Metallurgy}
	<b>{$Tperception}:</b> {$Perception}</td>
        </tr>
        </table>
</td></tr>
</table>
{if $Action == "gender"}
    <form method="post" action="stats.php?action=gender&amp;step=gender">
    <select name="gender"><option value="M">{$Genderm}</option>
    <option value="F">{$Genderf}</option></select><br />
    <input type="submit" value="{$Aselect}" /></form>
{/if}
{if $Action == "newbie"}
    {$Newbieinfo}<br />
    <a href="stats.php?action=newbie&amp;disable">{$Ayes}</a><br />
    <a href="stats.php">{$Ano}</a><br />
{/if}
