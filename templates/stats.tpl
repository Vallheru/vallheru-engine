{$Statsinfo}<br /><br />
{if $Avatar != ''}
<center><img src="{$Avatar}" width="{$Awidth}" height="{$Aheight}" /></center>
{/if}
{if $Action == "newbie"}
    {$Newbieinfo}<br />
    <a href="stats.php?action=newbie&amp;disable">{$Ayes}</a><br />
    <a href="stats.php">{$Ano}</a><br />
{/if}
<table width="100%">
<tr><td width="50%" valign="top">
    <center><b><u>{$Tstats}</u></b></center><br />
    <b>{$Tap}:</b> {$Ap}
    <b>{$Trace}:</b> {$Race}
    <b>{$Tclass}:</b> {$Clas}
    <b>{$Tdeity}:</b> {$Deity}
    <b>{$Tgender}:</b> {if $Gender == ""}
        <form method="post" action="stats.php?action=gender" style="display:inline;">
        <select name="gender"><option value="M">{$Genderm}</option>
        <option value="F">{$Genderf}</option></select>
        <input type="submit" value="{$Aselect}" /></form><br />
    {else}
        {$Gender}<br />
    {/if}
    {section name=stats1 loop=$Curstats}
        {$Curstats[stats1]}<br />
    {/section}
    <b>{$Tmana}:</b> {$Mana} {$Rest}
    <b>{$Tpw}:</b> {$PW}
    <b>{$Tenergy}:</b> {$Energy}
    <b>{$Blessfor}</b>{$Pray} <b>{$Blessval}</b>
    <b>{$Effect}</b> {$Antidote}<br /><br />
    <b>{$Treputation}:</b> {$Reputation}<br />
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
</td></tr>
</table><br />
<div align="center" width="70%">
{html_table loop=$Btable cols=1 caption="<b><u>{$Tbonuses}</u></b>" table_attr='border="0" width="40%" style="float:right;"'}
{html_table loop=$Stable cols=1 caption="<b><u>{$Tability}</u></b>" table_attr='border="0" width="40%"'}
</div>
