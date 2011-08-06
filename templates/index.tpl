{include file="head.tpl"}
{if $Step == ""}
<div class="pagename">{$Welcome}</div>
<div class="text">{$Whatis} {$Gamename}? {$Description} <a href="index.php?step=rules">{$Codex} {$Gamename}</a> {$Codex2}{$Codexdate}).</div>
<div class="mail">{$Adminname} <a href="mailto:{$Adminmail}">{$Adminmail1}</a></div>

<!-- <a href="index.php?lang=pl"><img src="images/polish.gif" title="Polski" border="0" /></a> 
<a href="index.php?lang=en"><img src="images/english.gif" title="English" border="0" /></a> -->

<div class="pagename">{$News}</div>
<div class="news">{$Update[0]}</div>
<div class="news">{$Update[1]}</div>
<div class="news">{$Update[2]}</div>
<div class="menu"><a href="http://www.firefox.pl/"><img src="http://www.firefox.pl/promo/firefox_apxl.png" alt="Pobierz Firefoksa!" title="Pobierz Firefoxa!" border="0" /></a></div>
<div class="menu3"><a href="http://www.niebij.pl/"><img src="images/niebij_button.png" alt="Nie bij!" title="Nie bij!" border="0" /></a></div>
<div class="menu3"><a href="http://bramaswiatow.pl"><img src="http://bramaswiatow.pl/bannery/banner120x60.gif" width="120" height="60" border="0" alt="Brama Swiatow" title="Czy odważysz się ją przekroczyć?" /></a></div>
{/if}
{include file="foot.tpl"}
