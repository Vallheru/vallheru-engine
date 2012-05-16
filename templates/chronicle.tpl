{if $Step == ""}
    {$Info}<br /><br />
    {if $Ioldstory > 0}
        <div style="float: right; width: 30%;">
	    <b>{$Stories[0]}</b>
	    <ul>
	    {foreach $Stories as $number => $mission}
	        {if $mission@first}
		    {continue}
		{/if}
		<li><a href="chronicle.php?step={$number}">{$mission}</a></li>
	    {/foreach}
	    </ul>
        </div>
    {/if}
    {if $Istory > 0}
        <div style="margin-left: 10%; width: 30%;">
	    <b>{$Oldstories[0]}</b>
	    <ul>
	    {foreach $Oldstories as $number => $mission}
	        {if $mission@first}
		    {continue}
		{/if}
		<li><a href="chronicle.php?step={$number}">{$mission}</a></li>
	    {/foreach}
	    </ul>
        </div>
    {/if}
    {if $Iother > 0}
        <div style="margin-left: 10%; width: 30%;">
	    <b>{$Others[0]}</b>
	    <ul>
	    {foreach $Others as $number => $mission}
	        {if $number == 0}
		    {continue}
		{/if}
		<li><a href="chronicle.php?step={$number}">{$mission}</a></li>
	    {/foreach}
	    </ul>
        </div>
    {/if}
{else}
    {if $Step != "go"}
        <div align="center"><b>{$Mname}</b></div><br />
	<div align="center" width="80%">{$Intro}</div>
    {/if}
{/if}
