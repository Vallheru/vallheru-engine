{if $Race == ""}
    {$Raceinfo}<br /><br />
    - <a href="rasa.php?rasa=czlowiek">{$Ahuman}</a><br />
    - <a href="rasa.php?rasa=elf">{$Aelf}</a><br />
    - <a href="rasa.php?rasa=krasnolud">{$Adwarf}</a><br />
    - <a href="rasa.php?rasa=hobbit">{$Ahobbit}</a><br />
    - <a href="rasa.php?rasa=reptilion">{$Areptilion}</a><br />
    - <a href="rasa.php?rasa=gnome">{$Agnome}</a><br />
{/if}

{if $Race != ""}
    {$Raceinfo}<br />
    <ul>
        {section name=info loop=$Stats}
	    <li>+ {$Stats[info]} {$Tstats[info]} {$Maxstats[info]}</li>
        {/section}
    </ul>
    <form method="post" action="rasa.php?rasa={$Race}&amp;step">
    <input type="submit" value="{$Aselect}" />
    </form><br />
    (<a href="rasa.php">{$Aback}</a>)
{/if}
