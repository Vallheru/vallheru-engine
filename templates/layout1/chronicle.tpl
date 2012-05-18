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
	<div style="margin: 5%; text-align: justify;">{$Intro}</div>
	{if $Mgo}
	    <div align="center">
	    <form method="post" action="chronicle.php?step=go">
	        <input type="hidden" name="qid" value="{$Mid}" />
		<input type="submit" value="{$Aread}" />
	    </form>
	    </div>
	{/if}
	<a href="chronicle.php">{$Aback}</a>
    {else}
        {$Text}
    	<form method="post" action="mission.php">
            {html_radios name=action options=$Moptions separator='<br />'}<br />
	    <input type="submit" value="{$Anext}" />
        </form>
    {/if}
{/if}
