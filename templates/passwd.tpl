{include file="head.tpl"}

<!-- <a href="index.php?lang=pl&amp;step=lostpasswd"><img src="images/polish.gif" title="Polski" border="0" alt="" /></a> 
<a href="index.php?lang=en&amp;step=lostpasswd"><img src="images/english.gif" title="English" border="0" alt="" /></a><br /><br /> -->

{if $Action != "haslo" && $Message == ""}
<div class="text2">{$Lostpassword2}</div>

<form method="post" action="index.php?step=lostpasswd&amp;action=haslo">
    <div class="forms">{$Email}:<input type="text" name="email" /></div>
    <div class="forms"><input type="submit" value="{$Send}" /></div>
</form>
{/if}

{if $Action == "haslo"}
    <div class="foot">{$Success}</div>
{/if}

{if $Message != ""}
    <div class="foot">{$Message}</div>
{/if}

{include file="foot.tpl"}
