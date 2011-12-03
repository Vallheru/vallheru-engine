{include file="head.tpl"}
{if $Step == ""}
    <div class="pagename">{$Welcome}</div>
    <table class="online">
        <tr>
	    <th>{$Tonline}</th>
	</tr>
        {section name=online loop=$Ponline}
	    <tr>
	        {if $Ponline[online].id > 0}
		    <td>{$Ponline[online].user} {$Tid} {$Ponline[online].id}</td>
		{else}
		    <td>{$Ponline[online].user}</td>
		{/if}
 	    </tr>
	{/section}
    </table>
    <div class="text">{$Whatis} {$Gamename}? {$Description} <a href="index.php?step=rules">{$Codex} {$Gamename}</a> {$Codex2}{$Codexdate}).</div>
    <div class="mail">{$Adminname} <a href="mailto:{$Adminmail}">{$Adminmail1}</a></div>
    <div class="newstop">{$News}</div>
    {foreach $Update as $news}
        <div class="news">{$news}</div>
    {/foreach}
{/if}
{if $Step == "donate"}
    <div class="pagename">{$Pagetitle}</div>
    <div class="text2">{$Dinfo}</div>
    <div class="text2">{$Dinfo2} <a href="mailto:{$Adminmail}">{$Dmail}</a> {$Dinfo3}</div>
{/if}
{if $Step == "promote"}
    <div class="pagename">{$Pagetitle}</div>
    <div class="text2">{$Pinfo}</div>
    <div class="pagename">{$Pinfo2}</div>
    <div class="pagename"><img src="images/vuserbar.gif" alt="Vallheru userbar" /></div>
    <div align="center">http://vallheru.net/images/vuserbar.gif (adres)<br />
    &lt;a href="http://vallheru.net">&lt;img src="http://vallheru.net/images/vuserbar.gif" border="0" alt="{$Gamename} RPG" /></a> (kod HTML)<br />
    [url=http://vallheru.net][img=http://vallheru.net/images/vuserbar.gif][/url] (BBCode - do wklejenia na forach itd)</div>
    <div class="pagename">{$Pinfo3}</div>
    <div class="pagename"><img src="images/vbutton.png" alt="Vallheru przycisk" /></div>
    <div align="center">http://vallheru.net/images/vbutton.png (adres)<br />
    &lt;a href="http://vallheru.net">&lt;img src="http://vallheru.net/images/vbutton.png" border="0" alt="{$Gamename} RPG" /></a> (kod HTML)<br />
    [url=http://vallheru.net][img=http://vallheru.net/images/vbutton.png][/url] (BBCode - do wklejenia na forach itd)</div>
    <div class="pagename">{$Pinfo4}</div>
    <div class="pagename"><img src="images/vantipixel.png" alt="Vallheru antypixel" /></div>
    <div align="center">http://vallheru.net/images/vantipixel.png (adres)<br />
    &lt;a href="http://vallheru.net">&lt;img src="http://vallheru.net/images/vantipixel.png" border="0" alt="{$Gamename} RPG" /></a> (kod HTML)<br />
    [url=http://vallheru.net][img=http://vallheru.net/images/vantipixel.png][/url] (BBCode - do wklejenia na forach itd)</div>
{/if}
{include file="foot.tpl"}
