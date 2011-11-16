{if $Step == ''}
    {$Desc}<br /><br />
    {if $Aquest != ''}
        <a href="hunters.php?step=table">{$Aquest}</a><br />
    {/if}
    <a href="hunters.php?step=bestiary">{$Abestiary}</a>
{/if}

{if $Step == 'bestiary'}
    {$Bestiary}<br /><br />
    {if $Amount == 0}
        {$Nodesc}
    {else}
        <table width="90%" align="center">
	    <tr>
	        <th>{$Tmonsters}</th>
		<th>{$Tmonsters2}</th>
	    </tr>
            {section name=beast loop=$Monsters}
	        <tr>
		    {if $Monsters[beast].id != 0}
	                <td><a href="hunters.php?step={$Monsters[beast].id}">{$Monsters[beast].name}</a></td>
		    {else}
		        <td></td>
		    {/if}
		    {if $Monsters2[beast].id != 0}
		        <td><a href="hunters.php?step={$Monsters2[beast].id}">{$Monsters2[beast].name}</a></td>
		    {else}
		        <td></td>
		    {/if}
		</tr>
	    {/section}
        </table>
    {/if}
{/if}

{if $Step == 'table'}
    {$Quest}
    <form method="post" action="hunters.php?step=quest">
        <input type="submit" value="{$Amade}" />
    </form>
    <a href="hunters.php">{$Aback}</a>
{/if}

{if $Step != '' && $Step != 'bestiary' && $Step != 'table' && $Step != 'quest'}
    <center>{$Mname}</center><br /><br />
    {$Mdesc}<br /><br />
    <a href="hunters.php?step=bestiary">{$Aback}</a>
{/if}

{$Message}
