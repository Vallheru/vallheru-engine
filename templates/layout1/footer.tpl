                            </td>
                        </tr>
                    </table>
                </td>
                {if $Stephead != "new"}
                <td width="150" valign="top">
                    <table cellpadding="0" cellspacing="0" class="panels" width="100%">
                        <tr>                
                            <td align="center">
                                <span class="light">
                                <b>{$Statistics}</b></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="light">
                                {$Playerslist}:<br /><br />
                                {section name=players loop=$List}
                                    {$List[players]}
                                {/section}
                                <br /><b>{$Players} </b> {$Registeredplayers}.<br />
                                <b>{$Online}</b> {$Playersonline}.<br /></span>
                            </td>
                        </tr>
                    </table>
                </td>
                {/if}
            </tr>
{if $Show == "1"}
            <tr>
                <td colspan="3" align="center">
                    <span class="light">
                    {$Loadingtime}: {$Duration} | {$Gzipcomp}: {$Compress} | {$Pmtime} PHP/MySQL: {$PHPtime}/{$Sqltime} | {$Queries}: {$Numquery} | {$Memory}: {$Memusage} {$MB} <a href="source.php?file={$Filename}" target="_blank">{$Asource}</a></span>
                </td>
            </tr>
{/if}
            <tr>
                {if $Stephead != "new"}
                <td class="foot">
                </td>
                {/if}
                <td align="center">
                    <span class="light">
                    &copy; 2004-2012 <a href="https://launchpad.net/vallheru">Vallheru Team</a> based on <a href="http://sourceforge.net/projects/gamers-fusion">Gamers-Fusion 2.5</a></span>
                </td>
                {if $Stephead != "new"}
                <td class="foot">
                </td>
                {/if}
            </tr>
        </table>
    <!--          (C) 2004,2005,2006,2007,2011,2012 Vallheru Team                         -->
    <!--           game based on code Gamers Fusion ver 2.5                               -->
    </body>
</html>                        


