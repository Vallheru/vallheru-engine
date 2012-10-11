{$Traininfo}<br />
{$Train}{$Train2}<br /><br />
<script src="js/train.js"></script>
<form method="post" action="train.php?action=train">
    {$Iwant} <select name="train" id="train" onChange="checkcost('{$Plrace}', '{$Plclass}', {$Tcosts})">
        {html_options options=$Toptions}
    </select> 
    <input type="text" size="3" value="{$Rep}" name="rep" id="rep" onChange="checkcost('{$Plrace}', '{$Plclass}', {$Tcosts})" /> {$Tamount}. <input type="submit" value="{$Atrain}" /><br /><br />
    <p id="info">{$Tinfo}</p>
</form>
