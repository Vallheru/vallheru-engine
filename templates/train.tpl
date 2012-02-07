{if $Action == ""}
    {$Traininfo}<br />
    {$Train}{$Train2}<br /><br />
    <script src="js/train.js"></script>
    <form method="post" action="train.php?action=train">
    {$Iwant} <select name="train" id="train" onChange="checkcost('{$Plrace}', '{$Plclass}', {$Tcosts})">
        <option value="strength">{$Trstr}</option>
        <option value="agility">{$Tragi}</option>
        <option value="inteli">{$Trint}</option>
        <option value="szyb">{$Trspeed}</option>
        <option value="wytrz">{$Trcon}</option>
        <option value="wisdom">{$Trwis}</option>
    </select> 
    <input type="text" size="3" value="{$Rep}" name="rep" id="rep" onChange="checkcost('{$Plrace}', '{$Plclass}', {$Tcosts})" /> {$Tamount}. <input type="submit" value="{$Atrain}" /><br /><br />
    <p id="info">{$Tinfo}</p>
    </form>
{/if}
