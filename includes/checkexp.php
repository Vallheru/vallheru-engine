<?php
/**
 *   File functions:
 *   Check for gaining level by player when he gain experience
 *
 *   @name                 : checkexp.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 04.12.2011
 *
 */

//
//
//       This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; either version 2 of the License, or
//   (at your option) any later version.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/checkexp.php");

/**
* Function check for gaining level by player
*/
function checkexp ($exp,$expgain,$level,$rasa,$user,$eid,$enemyid,$enemyuser,$player,$skill,$amount) 
{
    global $db;
    global $smarty;
    global $newdate;
    $poziom = 0;
    $ap = 0;
    $pz = 0;
    $energia = 0;
    $texp = ($exp + $expgain);
    if ($level < 100) 
    {
        $expn = (pow($level,2) * 50);
    }
    if ($level > 99 && $level < 200) 
    {
        $expn = (pow($level,2) * 250);
    }
    if ($level > 199 && $level < 300) 
    {
        $expn = (pow($level,2) * 500);
    }
    if ($level > 299 && $level < 400) 
    {
        $expn = (pow($level,2) * 1000);
    }
    if ($level > 399 && $level < 500) 
    {
        $expn = (pow($level,2) * 2500);
    }
    if ($level > 499 && $level < 600) 
    {
        $expn = (pow($level,2) * 5000);
    }
    if ($level > 599 && $level < 700) 
    {
        $expn = (pow($level,2) * 10000);
    }
    if ($level > 699 && $level < 800) 
    {
        $expn = (pow($level,2) * 25000);
    }
    if ($level > 799 && $level < 900) 
    {
        $expn = (pow($level,2) * 50000);
    }
    if ($level > 899 && $level < 1000) 
    {
        $expn = (pow($level,2) * 100000);
    }
    $energy = $db -> Execute("SELECT max_energy FROM players WHERE id=".$eid);          
    while ($texp >= $expn) 
    {
        $poziom = ($poziom + 1);
        $ap = ($ap + 5);
        $texp = ($texp - $expn);
        $level = ($level + 1);
        if ($level < 100) 
        {
            $expn = (pow($level,2) * 50);
        }
        if ($level > 99 && $level < 200) 
        {
            $expn = (pow($level,2) * 250);
        }
        if ($level > 199 && $level < 300) 
        {
            $expn = (pow($level,2) * 500);
        }
        if ($level > 299 && $level < 400) 
        {
            $expn = (pow($level,2) * 1000);
        }
        if ($level > 399 && $level < 500) 
        {
            $expn = (pow($level,2) * 2500);
        }
        if ($level > 499 && $level < 600) 
        {
            $expn = (pow($level,2) * 5000);
        }
        if ($level > 599 && $level < 700) 
        {
            $expn = (pow($level,2) * 10000);
        }
        if ($level > 699 && $level < 800) 
        {
            $expn = (pow($level,2) * 25000);
        }
        if ($level > 799 && $level < 900) 
        {
            $expn = (pow($level,2) * 50000);
        }
        if ($level > 899 && $level < 1000) 
        {
            $expn = (pow($level,2) * 100000);
        }
        if ($rasa == 'Człowiek') 
        {
            $pz = ($pz + 5);
        }
        if ($rasa == 'Elf') 
        {
            $pz = ($pz + 4);
        }
        if ($rasa == 'Krasnolud') 
        {
            $pz = ($pz + 6);
        }
        if ($rasa == 'Hobbit') 
        {
            $pz = ($pz + 3);
        }
        if ($rasa == 'Jaszczuroczłek') 
        {
            $pz = ($pz + 5);
        }
        if ($rasa == 'Gnom')
        {
            $pz = ($pz + 3);
        }
        $energia = $energia + 2.1;
        $maxenergy = $energy -> fields['max_energy'] + $energia;
    }
    $energy -> Close();    
    if ($poziom > 0) 
    {
        $objValls = $db->Execute("SELECT `level`, `refs` FROM `players` WHERE `id`=".$eid);
	if (($objValls->fields['level'] + $poziom >= 5) && ($objValls->fields['refs'] > 0))
	  {
	    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+1 WHERE `id`=".$objValls->fields['refs']);
	    $db->Execute("UPDATE `players` SET `refs`=0 WHERE `id`=".$eid);
	    $db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$objValls->fields['refs'].", 1, 'Polecony gracz.')");
	  }
	$objValls->Close();
        if ($player == $eid) 
        {
            $text = "<br /><b>Zdobyłeś poziom</b> ".$user.".<br />". $poziom." ".LEVELS."<br />".$ap." ".AP."<br />".$pz." ".HIT_POINTS."<br />";
            if ($energia > 0) 
            {
                $text = $text.$energia." ".GAIN_ENERGY."<br />";
            }
            print $text;
        }
        $db -> Execute("UPDATE `players` SET `max_hp`=`max_hp`+".$pz.", `ap`=`ap`+".$ap.", `level`=`level`+".$poziom.", `exp`=".$texp." WHERE `id`=".$eid);
        if ($energia > 0) 
        {
            $db -> Execute("UPDATE players SET max_energy=".$maxenergy." WHERE id=".$eid);
        }
        if ($enemyid != 0) 
        {
            $strDate = $db -> DBDate($newdate);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$eid.",'".IN_FIGHT." <b>".$enemyuser." ID:".$enemyid."</b>, ".YOU_GAIN2.".', ".$strDate.", 'B')");
        }
    } 
        else 
    {
        $db -> Execute("UPDATE players SET exp=".$texp." WHERE id=".$eid);
    }
    if ($amount > 0) 
    {
        $db -> Execute("UPDATE players SET ".$skill."=".$skill."+".$amount." WHERE id=".$eid);
    }
}

?>
