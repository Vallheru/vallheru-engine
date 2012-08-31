                        </td>
                    </tr>
                </table>
            </td>
            {if $Stephead != "new"}
            <td width="15%" valign="top" align="right">
                <table cellpadding="0" cellspacing="0" class="td" width="100%">
                    <tr>
                        <td align="center">
                            <b>{$Statistics}</b>
                        </td>
                    </tr>
                    <tr>
                        <td align="left">
                            {$Playerslist}:<br /><br />
			    {foreach $List as $player}
        		        {if $player.image != ""}
	    			    <img src="images/{$player.image}.gif" title="{$player.title}" alt="{$player.title}" />
				{/if}
				{$player.prefix} <a href="view.php?view={$player.id}">{$player.user}</a> {$player.suffix} ({$player.id})<br />
    			    {/foreach}
                            <br /><b>{$Players} </b> <a href="memberlist.php">{$Registeredplayers}</a>.<br />
                            <b>{$Online}</b> {$Playersonline}.<br />
                        </td>
                    </tr>
                </table>
            </td>
            {/if}
        </tr>
    </table>
    <center>
    {if $Show == "1"}
        {$Loadingtime}: {$Duration} |{$Gzipcomp}: {$Compress} | {$Pmtime} PHP/MySQL: {$PHPtime}/{$Sqltime} | {$Queries}: {$Numquery} | {$Memory}: {$Memusage} {$MB}
        <a href="source.php?file={$Filename}" target="_blank">{$Asource}</a><br />
    {/if}
    &copy; 2004-2012 <a href="https://launchpad.net/vallheru">Vallheru Team</a> based on <a href="http://sourceforge.net/projects/gamers-fusion">Gamers-Fusion 2.5</a>
    </center>
    <!--          (C) 2004,2005,2006,2007,2011,2012 Vallheru Team                               -->
    <!--           game based on Gamers Fusion ver 2.5 code                                     -->
    </body>
</html>                        

