{if $Clas == ""}
    {$Info}<br /><br />
    - <a href="klasa.php?klasa=wojownik">{$Awarrior}</a><br />
    - <a href="klasa.php?klasa=mag">{$Amage}</a><br />
    - <a href="klasa.php?klasa=craftsman">{$Aworker}</a><br />
    - <a href="klasa.php?klasa=barbarzynca">{$Abarbarian}</a><br />
    - <a href="klasa.php?klasa=zlodziej">{$Athief}</a><br /><br />
{/if}

{if $Clas != ""}
    {$Classinfo}
    {section name=info loop=$Stats}
        <li>{$Stats[info]} {$Tstats[info]}</li>
    {/section}
    </ul>
    <form method=post action="klasa.php?klasa={$Clas}&amp;step=wybierz">
    <input type="submit" value="{$Aselect}" /></form><br />
    (<a href="klasa.php">{$Aback}</a>)
{/if}
