{include file="head.tpl"}
{if $Step == ""}
<div class="pagename">{$Welcome}</div>
<div class="text">{$Whatis} {$Gamename}? {$Description} <a href="index.php?step=rules">{$Codex} {$Gamename}</a> {$Codex2}{$Codexdate}).</div>
<div class="mail">{$Adminname} <a href="mailto:{$Adminmail}">{$Adminmail1}</a></div>

<!-- <a href="index.php?lang=pl"><img src="images/polish.gif" title="Polski" border="0" /></a> 
<a href="index.php?lang=en"><img src="images/english.gif" title="English" border="0" /></a> -->

<div class="pagename">{$News}</div>
{foreach $Update as $news}
<div class="news">{$news}</div>
{/foreach}
{/if}
{include file="foot.tpl"}
