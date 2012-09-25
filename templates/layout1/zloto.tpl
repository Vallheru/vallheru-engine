<center>{$Itemsinfo}</center><br /><br />
<table align="center" width="50%">
    <tr><td>{$Goldinhands}: {$Gold}</td></tr>
    <tr><td>{$Goldinbank}: {$Bank}</td></tr>
    <tr><td>{$Tmithril}: {$Mithril}</td></tr>
    <tr><td>{$Trefs}: {$Refs}</td></tr>
    <tr><td>{$Tmaps}: {$Maps}</td></tr>
</table><br />
<table align="center" width="70%">
    <tr>
        <th align="left"><u>{$TMinerals}</u></th>
	<th align="left"><u>{$Lumber}</u></th>
    </tr>
    {for $i = 0 to 7 step 2}
        <tr>
	    <td>{$Minerals[$i][0]}: {$Minerals[$i][1]}</td>
	    <td>{$Minerals[$i + 1][0]}: {$Minerals[$i + 1][1]}</td>
	</tr>
    {/for}
    {for $i = 8 to 16}
        <tr>
	    <td>{$Minerals[$i][0]}: {$Minerals[$i][1]}</td>
	</tr>
    {/for}
</table><br />
<table align="center" width="70%">
    <tr>
        <th align="left"><u>{$THerbs}</u></th>
	<th align="left"><u>{$Seeds}</u></th>
    </tr>
    {for $i = 0 to 7 step 2}
        <tr>
	    <td>{$Herbs[$i][0]}: {$Herbs[$i][1]}</td>
	    <td>{$Herbs[$i + 1][0]}: {$Herbs[$i + 1][1]}</td>
	</tr>
    {/for}
</table>
