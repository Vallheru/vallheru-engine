{if $Action == ""}
    {$Traininfo}<br />
    {$Train}{$Train2}
    <form method="post" action="train.php?action=train">
    {$Iwant} <select name="train">
        <option value="strength">{$Trstr}</option>
        <option value="agility">{$Tragi}</option>
        <option value="inteli">{$Trint}</option>
        <option value="szyb">{$Trspeed}</option>
        <option value="wytrz">{$Trcon}</option>
        <option value="wisdom">{$Trwis}</option>
    </select> 
    <input type="text" size="3" value="0" name="rep" /> {$Tamount}. <input type="submit" value="{$Atrain}" />
    </form>
{/if}
{if $Action == "train"}
    {$Allcost} {$Maxcost} {$Goldcoins}<br />
    <form method="post" action="train.php?action=train&amp;step=next">
        <input type="hidden" name="train" value="{$Train}" />
        <input type="hidden" name="rep" value="{$Rep}" />
        <input type="submit" value="{$Atrain}" />
    </form>
{/if}
