{include file="head.tpl"}
{if $Step == ""}
    <div class="pagename">{$Welcome}</div>
    <div class="text">{$Whatis} {$Gamename}? {$Description} <a href="index.php?step=rules">{$Codex} {$Gamename}</a> {$Codex2}{$Codexdate}).</div>
    <div class="mail">{$Adminname} <a href="mailto:{$Adminmail}">{$Adminmail1}</a></div>
    <div class="newstop">{$News}</div>
    {foreach $Update as $news}
        <div class="news">{$news}</div>
    {/foreach}
{/if}
{if $Step == "donate"}
    <div class="pagename">{$Pagetitle}</div>
    <div class="text">{$Dinfo}</div>
    <div class="text">{$Dinfo2} <a href="mailto:{$Adminmail}">{$Dmail}</a> {$Dinfo3}</div>
{/if}
{include file="foot.tpl"}
