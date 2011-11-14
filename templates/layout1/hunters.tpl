{if $Step == ''}
    {$Desc}<br /><br />
    <a href="hunters.php?step=bestiary">{$Abestiary}</a>
{/if}

{if $Step == 'bestiary'}
    {$Bestiary}<br /><br />
    {if $Amount == 0}
        {$Nodesc}
    {else}
        <ul>
            {section name=beast loop=$Monsters}
	        <li><a href="hunters.php?step={$Monsters[beast].id}">{$Monsters[beast].name}</a></li>
	    {/section}
        </ul>
    {/if}
{/if}

{if $Step != '' && $Step != 'bestiary'}
    <center>{$Mname}</center><br /><br />
    {$Mdesc}<br /><br />
    <a href="hunters.php?step=bestiary">{$Aback}</a>
{/if}
