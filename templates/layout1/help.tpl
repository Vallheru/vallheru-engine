{if $Help == ""}
    {$Helpinfo}
	<ul>
    <li><a href="help.php?help=history">{$Ahistory}</a></li>
    <li><a href="help.php?help=overview">{$Aoverview}</a></li>
    <li><a href="help.php?help=equipment">{$Aequipment}</a></li>
    <li><a href="help.php?help=eventlog">{$Aeventlog}</a></li>
    <li><a href="help.php?help=indocron">{$Aindocron}</a></li>
    <li><a href="help.php?help=battle">{$Abattle2}</a></li>
    <li><a href="help.php?help=money">{$Amoney}</a></li>
    <li><a href="help.php?help=energy">{$Aenergy}</a></li>
    <li><a href="help.php?help=faq">{$Afaq}</a></li>
	</ul>
{/if}

{if $Help != "" && $Step2 == ""}
   {$Helptext}
{/if}

{if $Help == "faq"}
    <a href=mailto:{$Mail}>{$Amail}</a>{$Helptext2}
{/if}

{if $Help == "indocron"}
    {if $Step == ""}
        {$Helpinfo2}
        <ul>
        <li><a href="help.php?help=indocron&amp;step=wojenne">{$Abattle}</a></li>
        <li><a href="help.php?help=indocron&amp;step=zachodni">{$Awest}</a></li>
        <li><a href="help.php?help=indocron&amp;step=praca">{$Ajob}</a></li>
        <li><a href="help.php?help=indocron&amp;step=spol">{$Asoc}</a></li>
        <li><a href="help.php?help=indocron&amp;step=mieszkalna">{$Ahome}</a></li>
        <li><a href="help.php?help=indocron&amp;step=poludniowa">{$Asouth}</a></li>
        <li><a href="help.php?help=indocron&amp;step=podgrodzie">{$Avillage}</a></li>
        <li><a href="help.php?help=indocron&amp;step=zamek">{$Acastle}</a></li>
        </ul>
        {$Info}
    {/if}
    {if $Step == "mieszkalna"}
        {$Helpinfo}
        <ul>
        <li><a href="help.php?help=indocron&amp;step=mieszkalna&amp;step2=spis">{$Alist}</a></li>
        <li><a href="help.php?help=indocron&amp;step=mieszkalna&amp;step2=posagi">{$Amonuments}</a></li>
        </ul>
        {if $Step2 != ""}
            {$Helptext}
        {/if}
    {/if}
    {if $Step == "podgrodzie"}
        {$Helpinfo}
        <ul>
        <li><a href="help.php?help=indocron&amp;step=podgrodzie&amp;step2=szkola">{$Aschool}</a></li>
        <li><a href="help.php?help=indocron&amp;step=podgrodzie&amp;step2=kopalnia">{$Amine}</a></li>
        <li><a href="help.php?help=indocron&amp;step=podgrodzie&amp;step2=chowance">{$Acore}</a></li>
        </ul>
        {if $Step2 != ""}
            {$Helptext}
        {/if}
    {/if}
    {if $Step == "poludniowa"}
        {$Helpinfo}
        <ul>
        <li><a href="help.php?help=indocron&amp;step=poludniowa&amp;step2=rynek">{$Amarket}</a></li>
        <li><a href="help.php?help=indocron&amp;step=poludniowa&amp;step2=stajnia">{$Atravel}</a></li></ul>
        {if $Step2 == "rynek"}
            {$Helptext}
        {/if}
    {/if}
    {if $Step == "praca"}
        {$Helpinfo}
        <ul>
	    <li><a href="help.php?help=indocron&amp;step=praca&amp;step2=oczyszczanie">{$Aclear}</a></li>
	    <li><a href="help.php?help=indocron&amp;step=praca&amp;step2=kuznia">{$Asmith}</a></li>
        <li><a href="help.php?help=indocron&amp;step=praca&amp;step2=alchemik">{$Aalchemy}</a></li>
	    <li><a href="help.php?help=indocron&amp;step=praca&amp;step2=tartak">{$Alumbermill}</a></li>
        </ul>
        {if $Step2 != ""}
            {$Helptext}
        {/if}
    {/if}
    {if $Step == "spol"}
        {$Helpinfo}
        <ul>
        <li><a href="help.php?help=indocron&amp;step=spol&amp;step2=plotki">{$Anews}</a></li>
        <li><a href="help.php?help=indocron&amp;step=spol&amp;step2=forum">{$Aforums}</a></li>
        <li><a href="help.php?help=indocron&amp;step=spol&amp;step2=karczma">{$Ainn}</a></li>
        <li><a href="help.php?help=indocron&amp;step=spol&amp;step2=poczta">{$Amail2}</a></li>
        <li><a href="help.php?help=indocron&amp;step=spol&amp;step2=klany">{$Aclans}</a></ul></li>
        {if $Step2 != ""}
            {$Helptext}
        {/if}
    {/if}
    {if $Step == "wojenne"}
        {$Helpinfo}
        <ul>
        <li><a href="help.php?help=indocron&amp;step=wojenne&amp;step2=arena">{$Aarena2}</a></li>
        <li><a href="help.php?help=indocron&amp;step=wojenne&amp;step2=zbrojmistrz">{$Aweapon}</a></li>
        <li><a href="help.php?help=indocron&amp;step=wojenne&amp;step2=platnerz">{$Aarmor}</a></li>
        <li><a href="help.php?help=indocron&amp;step=wojenne&amp;step2=fleczer">{$Afletcher}</a></li>
        <li><a href="help.php?help=indocron&amp;step=wojenne&amp;step2=straznica">{$Aoutpost}</a></li></ul>
        {if $Step2 != ""}
           {$Helptext}
        {/if}
    {/if}
    {if $Step == "zachodni"}
        {$Helpinfo}
        <ul>
        <li><a href="help.php?help=indocron&amp;step=zachodni&amp;step2=labirynt">{$Alabyrynth}</a></li>
        <li><a href="help.php?help=indocron&amp;step=zachodni&amp;step2=mithril">{$Amith}</a></li>
        <li><a href="help.php?help=indocron&amp;step=zachodni&amp;step2=wieza">{$Atower}</a></li>
        <li><a href="help.php?help=indocron&amp;step=zachodni&amp;step2=swiatynia">{$Atemple}</a></li>
        <li><a href="help.php?help=indocron&amp;step=zachodni&amp;step2=alchemik">{$Aalchemy}</a></li></ul>
        {if $Step2 != ""}
            {$Helptext}
        {/if}
    {/if}
    {if $Step == "zamek"}
        {$Helpinfo}
        <ul>
        <li><a href="help.php?help=indocron&steo=zamek&amp;step2=wiesci">{$Anews}</a></li>
        <li><a href="help.php?help=indocron&steo=zamek&amp;step2=zegar">{$Atimer}</a></li>
        <li><a href="help.php?help=indocron&steo=zamek&amp;step2=poleceni">{$Areff}</a></li>
        </ul>
        {if $Step2 != ""}
            {$Helptext}
        {/if}
    {/if}
{/if}

{if $Help != ""}
    <br /><br />(<a href="help.php">{$Aback}</a>)<br /><br />
{/if}

<br /><br />{$Hauthor}
