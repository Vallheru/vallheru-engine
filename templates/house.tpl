{if $Action == ""}
    {$Houseinfo}<br /><br />
    <ul>
    {if $Houseid == ""}
        <li><a href="house.php?action=land">{$Aland}</a></li>
        <li><a href="house.php?action=list">{$Alist}</a></li>
        <li><a href="house.php?action=rent">{$Arent}</a></li>
    {/if}
    {if $Houseid > "0"}
        <li><a href="house.php?action=my">{$Ahouse}</a></li>
        <li><a href="house.php?action=build">{$Aworkshop}</a></li>
        <li><a href="house.php?action=land">{$Aland}</a></li>
        <li><a href="house.php?action=list">{$Alist}</a></li>
        <li><a href="house.php?action=rent">{$Arent}</a></li>
    {/if}
    </ul>
{/if}

{if $Action == "rent"}
    <table width="95%" align="center">
    <tr>
    <th>{$Hnumber}</th>
    <th>{$Hseller}</th>
    <th>{$Hname}</th>
    <th>{$Hsize}</th>
    <th>{$Htype}</th>
    <th>{$Hcost}</th>
    <th>{$Hoption}</th>
    </tr>
    {foreach $Houses as $house}
        <tr>
        <td>{$house.id}</td>
        <td><a href="view.php?view={$house.seller}">{$house.sellername}</a></td>
        <td>{$house.name}</td>
        <td>{$house.build}</td>
        <td>{$house.housetype}</td>
    	<td>{$house.cost}</td>
    	<td>{$house.link}</td>
        </tr>
    {/foreach}
    </table> <a href="house.php">{$Aback}</a>
{/if}

{if $Action == "land"}
    {$Landinfo}
    <ul>
    <li><a href="house.php?action=land&amp;step=buy">{$Buya} {$Cost}</a></li>
    <li><a href="house.php">{$Aback}</a></li>
    </ul>
{/if}

{if $Action == "build"}
    {$Buildinfo} <b>{$Points}</b> {$Buildinfo2}:<br /><br /><br />
    {if $Step != "new" && $Step != "upgrade"}
        {$Buildhouse}
        {$Buildbed}
        {$Buildwardrobe}
        {$Upgrade}
    {/if}
    {if $Step == "new"}
        <form method="post" action="house.php?action=build&amp;step=new&amp;step2=make">
        {$Hname} <input type="text" name="name" /><br />
        <input type="submit" value="{$Abuild}" /></form><br />
    {/if}
    {if $Step == "upgrade"}
        {$Upginfo}<br />
        <form method="post" action="house.php?action=build&amp;step=upgrade&amp;step2=make">
        {$Upgrade3} <input type="text" name="points" size="5" /> {$Upgrade2}<br />
        <input type="submit" value="{$Awork}" /></form><br />
    {/if}
    <br /><br /><a href="house.php">{$Aback}</a>
{/if}

{if $Action == "list"}
    <table width="95%" align="center">
    <tr>
    <th>{$Hnumber}</th>
    <th>{$Howner}</th>
    <th>{$Hlocator}</th>
    <th>{$Hname}</th>
    <th>{$Hsize}</th>
    <th>{$Htype}</th>
    </tr>
    {foreach $Houses as $house}
        <tr>
        <td>{$house.id}</td>
        <td><a href="view.php?view={$house.owner}">{$house.ownername}</a></td>
    	<td>{$house.locator}</td>
        <td>{$house.name}</td>
        <td>{$house.build}</td>
        <td>{$house.housetype}</td>
        </tr>
    {/foreach}
    </table> <a href="house.php">{$Aback}</a>
{/if}

{if $Action == "my"}
    {if $Step == ""}
        {$Houseinfo}<br /><br />
        {$Hname}: {$Name} {if $Owner == $Id} [<a href="house.php?action=my&amp;step=name">{$Cname}</a>] {/if}<br />
        {$Howner}: <a href="view.php?view={$Owner}">{$Ownername}</a><br />
        {$Hsize}: {$Build}<br />
        {$Lamount}: {$Size}<br />
        {$Frooms}: {$Unused}<br />
        {$Hvalue}: {$Value} {$Housename}<br />
        {$Hlocator}: {$Locator}<br />
        {$Ibedroom}: {$Bedroom}<br />
        {$Wamount}: {$Wardrobe}<br />
        {$Iamount}: {$Items}<br /><br />
        {$Bedroomlink}
        {$Wardrobelink}
        {$Locatorlink}
        {$Sellhouse}
        {$Locleave}
        <br />(<a href="house.php">{$Aback}</a>)
    {/if}
    {if $Step == "leave" && $Step2 == ""}
        {$Youwant}
        <form method="post" action="house.php?action=my&amp;step=leave&amp;step2=confirm">
            <input type="submit" value="{$Yes}" />
        </form>
    {/if}
    {if $Step == "sell"}
        {if $Step2 == ""}
            {$Sellinfo}<br />
            <form method="post" action="house.php?action=my&amp;step=sell&amp;step2=sell">
            <input type="submit" value="{$Asend}" /> {$Housesale} <input type="text" name="cost" />{$Goldcoins}
            </form>
        {/if}
    {/if}
    {if $Step == "locator"}
        {if $Step2 == ""}
            <form method="post" action="house.php?action=my&amp;step=locator&amp;step2=change">
        <select name="loc"><option value="add">{$Oadd}</option>
        <option value="delete">{$Odelete}</option></select> {$Second}<br />
        {$Lid2}: <input type="text" name="lid" size="5" value="{$Locid}" /><br />
        <input type="submit" value="{$Amake}" /></form><br />
    {/if}
    {/if}
    {if $Step == "name"}
        <form method="post" action="house.php?action=my&amp;step=name&amp;step2=change">
        <input type="submit" value="{$Achange}" /> {$Ona}: <input type="text" name="name" />
        </form><br />
    {/if}
    {if $Step == "bedroom"}
        {$Bedinfo}<br />
        - <a href="house.php?action=my&amp;step=bedroom&amp;step2=rest">{$Arest}</a><br />
        - <a href="logout.php?rest=Y&amp;did={$Id}">{$Asleep}</a><br />
    {/if}
    {if $Step == "wardrobe"}
        {$Winfo} <b>{$Wardrobe} {$Wamount}</b> {$And2} <b>{$Amount} {$Iamount4}</b> {$Inw}
	<ul>
            <li><a href="house.php?action=my&amp;step=wardrobe&amp;step2=add">{$Ahidei}</a></li>
       	    <li><a href="house.php?action=my&amp;step=wardrobe&amp;step2=list">{$Alist}</a></li>
       </ul>
       {if $Step2 == "list"}
           <table width="95%" align="center">
           <tr>
           <th>{$Iname}</th>
           <th>{$Ipower}</th>
           <th>{$Idur}</th>
           <th>{$Iagi}</th>
           <th>{$Ispeed}</th>
           <th>{$Iamount2}</th>
           <th>{$Ioption}</th>
           </tr>
	   {foreach $Items as $item}
               <tr>
               <td>{$item.name}</td>
               <td align="center">{$item.power}</td>
               <td align="center">{$item.wt}/{$item.maxwt}</td>
               <td align="center">{$item.zr}</td>
               <td align="center">{$item.szyb}</td>
               <td align="center">{$item.amount}</td>
               <td>- <a href="house.php?action=my&amp;step=wardrobe&amp;step2=take&amp;take={$item.id}">{$Aget}</a></td>
               </tr>
           {/foreach}
           </table>
        {/if}
        {if $Step2 == "take"}
            {if $Step3 == ""}
                <form method="post" action="house.php?action=my&amp;step=wardrobe&amp;step2=take&amp;take={$Id}&amp;step3">
                <input type="submit" value="{$Aget}" /> {$Fromh} <input type="text" name="amount" value="{$Amount23}" size="5" /> {$Amount2} <b>{$Name}</b><br />
                </form>
            {/if}
        {/if}
        {if $Step2 == "add"}
            <br />
	    <form method="post" action="house.php?action=my&amp;step=wardrobe&amp;step2=add&amp;step3">
	        <input type="submit" value="{$Ahide}" /> {$Item}: {html_options name=przedmiot options=$Ioptions} <input type="text" name="amount" size="5" /> (<input type="checkbox" name="addall" value="Y" />{$Tall}) {$Amount2}
            </form>
        {/if}
    {/if}
    {if $Step != ""}
        <br /><br />(<a href="house.php?action=my">{$Aback}</a>)
    {/if}
{/if}
