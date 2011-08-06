{if $Clas == ""}
    {$Info}<br /><br />
    - <a href="klasa.php?klasa=wojownik">{$Awarrior}</a><br />
    - <a href="klasa.php?klasa=mag">{$Amage}</a><br />
    - <a href="klasa.php?klasa=craftsman">{$Aworker}</a><br />
    - <a href="klasa.php?klasa=barbarzynca">{$Abarbarian}</a><br />
    - <a href="klasa.php?klasa=zlodziej">{$Athief}</a><br /><br />
{/if}

{if $Clas == "wojownik" && $Plclass == ""}
    {$Classinfo}
    <form method=post action="klasa.php?klasa=wojownik&amp;step=wybierz">
    <input type="submit" value="{$Aselect}" /></form><br />
    (<a href="klasa.php">{$Aback}</a>)
{/if}

{if $Clas == "mag" && $Plclass == ""}
    {$Classinfo}
    <form method="post" action="klasa.php?klasa=mag&amp;step=wybierz">
    <input type="submit" value="{$Aselect}" /></form><br />
    (<a href="klasa.php">{$Aback}</a>)
{/if}

{if $Clas == "craftsman" && $Plclass == ""}
    {$Classinfo}
    <form method="post" action="klasa.php?klasa=craftsman&amp;step=wybierz">
    <input type="submit" value="{$Aselect}" /></form><br />
    (<a href="klasa.php">{$Aback}</a>)
{/if}

{if $Clas == "barbarzynca" && $Plclass == ""}
    {$Classinfo}
    <form method="post" action="klasa.php?klasa=barbarzynca&amp;step=wybierz">
    <input type="submit" value="{$Aselect}" /></form><br />
    (<a href="klasa.php">{$Aback}</a>)
{/if}

{if $Clas == "zlodziej" && $Plclass == ""}
    {$Classinfo}
    <form method="post" action="klasa.php?klasa=zlodziej&amp;step=wybierz">
    <input type="submit" value="{$Aselect}" /></form><br />
    (<a href="klasa.php">{$Aback}</a>)
{/if}
