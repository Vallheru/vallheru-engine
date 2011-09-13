{$Link}
{if $Step == "run"}
    {if $Chance > "0"}
        {$Escapesucc} {$Ename}! {$Escapesucc2} {$Expgain} {$Escapesucc3}<br />
    {/if}
    {if $Health > "0" && $Chance > "0"}
        <br />{$Youwant}<br />
        <a href={$Yes}>{$Ayes}</a><br />
        <a href={$No}>{$Ano}</a><br />
    {/if}
{/if}

{if $Health > "0" && $Location == "Góry" && $Step == "" && $Fight == "0" && $Action == ""}
    {$Minfo}<br /><br />
    <form method="post" action="explore.php?action=moutains">
        {$Howmuch} <input type="text" name="amount" value="{$Curen}" size="5" /> {$Tenergy}<br />
        <input type="submit" value="{$Awalk}" />
    </form><br />
    <a href="gory.php">{$Ano}</a><br />
{/if}

{if $Action == "moutains" && $Location == "Góry" && $Step == ""}
    {$Youfind}{$Enemy}{$Bridge}<br /><br />
    {if $Health > "0" && $Enemy == "" && $Bridge == ""}
        <form method="post" action="explore.php?action=moutains">
            {$Howmuch} <input type="text" name="amount" value="{$Curen}" size="5" /> {$Tenergy}<br />
            <input type="submit" value="{$Awalk}" />
        </form><br />
        <a href="gory.php">{$Ano}</a><br />
    {/if}
    {if $Bridge != ""}
        <form method="post" action="explore.php?action=moutains&amp;step=first">
            <input type="submit" value="{$Ayes}" />
            <input type="hidden" name="check" value="first" />
        </form>
        - <a href="explore.php">{$Ano}</a>
    {/if}
{/if}

{if $Action == "moutains" && $Location == "Góry" && $Step != ""}
    {if $Step == "first"}
        {$Fquestion}?</b><br />
        <form method="post" action="explore.php?action=moutains&amp;step=second">
            <input type="text" name="fanswer" /><br />
            <input type="submit" value="{$Anext}" />
        </form>
    {/if}
    {if $Step == "second"}
        {if $Answer == "true"}
            {$Squestion}?</b><br />
            <form method="post" action="explore.php?action=moutains&amp;step=third">
                <input type="text" name="sanswer" /><br />
                <input type="submit" value="{$Anext}" />
            </form>
        {/if}
        {if $Answer == "false"}
            {$Qfail}
        {/if}
    {/if}
    {if $Step == "third"}
        {if $Answer == "true"}
            {$Tquestion}: <b>{$Question}?</b><br />
            <form method="post" action="explore.php?action=moutains&amp;step=forth">
                <input type="text" name="tanswer" /><br />
                <input type="submit" value="{$Anext}" />
            </form>
        {/if}
        {if $Answer == "false"}
            {$Qfail}
        {/if}
    {/if}
    {if $Step == "forth"}
        {if $Answer == "true"}
            {$Qsucc} {$Item} {$Qsucc2}...</i><br />
            (<a href="explore.php"> {$Arefresh}</a>)
        {/if}
        {if $Answer == "false"}
            {$Qfail}
        {/if}
    {/if}
{/if}

{if $Health > "0" && $Location == "Las" && $Step == "" && $Fight == "0" && $Action == ""}
    {$Finfo}<br /><br />
    <form method="post" action="explore.php?action=forest">
        {$Howmuch} <input type="text" name="amount" value="{$Curen}" size="5" /> {$Tenergy}<br />
        <input type="submit" value="{$Awalk}" />
    </form><br />
    <a href="las.php">{$Ano}</a><br />
{/if}

{if $Action == "forest" && $Location == "Las"}
    {$Youfind}{$Enemy}<br /><br />
    {if $Health > "0" && $Enemy == ""}
        <form method="post" action="explore.php?action=forest">
            {$Howmuch} <input type="text" name="amount" value="{$Curen}" size="5" /> {$Tenergy}<br />
            <input type="submit" value="{$Awalk}" />
        </form><br />
        <a href="las.php">{$Ano}</a><br />
    {/if}
{/if}

