{include file="head.tpl"}
<!-- <a href="register.php?lang=pl"><img src="images/polish.gif" title="Polski" border="0" alt="" /></a> 
<a href="register.php?lang=en"><img src="images/english.gif" title="English" border="0" alt="" /></a><br /><br /> -->

{if $Action == ""}
<div class="text3">{$Description2}</div>
<div class="text2">{$Description}{$Players}{$Registered}</div>

<form method="post" action="register.php?action=register">

    <div class="forms2">{$Nick} <input type="text" name="user" /></div>
    <div class="forms2">{$Email}: <input type="text" name="email" /></div>
    <div class="forms2">{$Confemail} <input type="text" name="vemail" /></div>
    <div class="forms2">{$Password}: <input type="password" name="pass" /></div>
    <div class="forms3">{$Gtype}:</div>
    <div class="forms3"><input type="radio" name="gtype" value="T" checked="checked" />{$Gtext}</div>
    <div class="forms3"><input type="radio" name="gtype" value="G" />{$Ggraphic}</div>
    <div class="forms2"><em>{$Ginfo}</em></div>
    <div class="forms2">{$Referralid} <input type="text" name="ref" readonly="readonly" value="{$Referal}" /></div>
    <div class="forms2"><em>{$Ifnoid}</em></div>
    <div class="forms2"><input type="submit" value="{$Register2}" /></div>
    </form>

<div class="text2">{$Shortrules}</div>
<ol>
<li>{$Rule1}</li>
<li>{$Rule2}</li>
<li>{$Rule3}</li>
<li>{$Rule4}</li>
<li>{$Rule5}</li>
<li>{$Rule6}</li>
<li>{$Rule7}</li>
<li>{$Rule8}</li>
</ol>
{/if}

{if $Action == "register"}
    <div class="foot">{$Registersuccess}</div>
{/if}
{include file="foot.tpl"}
