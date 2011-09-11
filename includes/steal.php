<?php
/**
 *   File functions:
 *   Steal items from shops
 *
 *   @name                 : steal.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 11.09.2011
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
// $Id:$

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/steal.php");

/**
* Steal items from shops
*/
function steal ($itemid) 
{
    global $player;
    global $smarty;
    global $title;
    global $newdata;
    global $db;
    
    if ($player -> clas != 'Złodziej')
    {
        error(ERROR);
    }
    checkvalue($itemid);
    if ($player -> hp <= 0) 
    {
        error (E_DEAD);
    }
    if ($player -> crime <= 0) 
    {
        error (E_CRIME);
    }
    $roll = rand (1, ($player -> level * 100));
    /**
     * Add bonus from bless
     */
    $strBless = FALSE;
    $objBless = $db -> Execute("SELECT bless, blessval FROM players WHERE id=".$player -> id);
    if ($objBless -> fields['bless'] == 'inteli')
    {
        $player -> inteli = $player -> inteli + $objBless -> fields['blessval'];
        $strBless = 'inteli';
    }
    if ($objBless -> fields['bless'] == 'agility')
    {
        $player -> agility = $player -> agility + $objBless -> fields['blessval'];
        $strBless = 'agility';
    }
    $objBless -> Close();
    if ($strBless)
    {
        $db -> Execute("UPDATE players SET bless='', blessval=0 WHERE id=".$player -> id);
    }

    /**
     * Add bonus from rings
     */
    $arrEquip = $player -> equipment();
    $arrRings = array(R_AGI, R_INT);
    $arrStat = array('agility', 'inteli');
    if ($arrEquip[9][0])
    {
        $arrRingtype = explode(" ", $arrEquip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        if ($intKey != NULL)
        {
            $strStat = $arrStat[$intKey];
            $player -> $strStat = $player -> $strStat + $arrEquip[9][2];
        }
    }
    if ($arrEquip[10][0])
    {
        $arrRingtype = explode(" ", $arrEquip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        if ($intKey != NULL)
        {
            $strStat = $arrStat[$intKey];
            $player -> $strStat = $player -> $strStat + $arrEquip[10][2];
        }
    }

    $chance = ($player->agility + $player->inteli + $player->thievery) - $roll;
    $strDate = $db -> DBDate(date("y-m-d"));
    if ($chance < 1) 
    {
        $cost = 1000 * $player -> level;
        $expgain = ceil ($player -> level / 10);
        checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', 0.01);
        $db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `crime`=`crime`-1 WHERE `id`=".$player -> id);
        $db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.", '".VERDICT."', 7, ".$cost.", ".$strDate.")") or die("Błąd!");
        $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".S_LOG_INFO." ".$cost.".', ".$strDate.")");
        error (CRIME_RESULT1);
    } 
        else 
    {
        if ($title != 'Fleczer') 
        {
            $arritem = $db -> Execute("SELECT * FROM equipment WHERE id=".$itemid);
        } 
            else 
        {
            $arritem = $db -> Execute("SELECT * FROM bows WHERE id=".$itemid);
        }       
        $db -> Execute("UPDATE players SET crime=crime-1 WHERE id=".$player -> id);
        $expgain = ($player -> level * 10); 
	$fltThief = ($arritem->fields['level'] / 100);
        checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
        if ($arritem -> fields['type'] == 'R') 
        {
            $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arritem -> fields['name']."' AND owner=".$player -> id." AND status='U' AND cost=1");
            if (empty ($test -> fields['id'])) 
            {
                $db -> Execute("INSERT INTO equipment (owner, name, power, cost, wt, szyb, minlev, maxwt, type) VALUES(".$player -> id.",'".$arritem -> fields['name']."',".$arritem -> fields['power'].",1,".$arritem -> fields['maxwt'].",".$arritem -> fields['szyb'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",'".$arritem -> fields['type']."')") or error(DB_ERROR2);
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET wt=wt+".$arritem -> fields['maxwt']." WHERE id=".$test -> fields['id']);
                $db -> Execute("UPDATE equipment SET maxwt=maxwt+".$arritem -> fields['maxwt']." where id=".$test -> fields['id']);
            }
            $test -> Close();
        } 
            else 
        {
            $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arritem -> fields['name']."' AND wt=".$arritem -> fields['maxwt']." AND type='".$arritem -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$arritem -> fields['power']." AND zr=".$arritem -> fields['zr']." AND szyb=".$arritem -> fields['szyb']." AND maxwt=".$arritem -> fields['maxwt']." and cost=1");
            if (empty ($test -> fields['id'])) 
            {
                if ($arritem -> fields['type'] == 'B') 
                {
                    $arritem -> fields['twohand'] = 'Y';
                }
                $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, szyb, twohand, repair) VALUES(".$player -> id.",'".$arritem -> fields['name']."',".$arritem -> fields['power'].",'".$arritem -> fields['type']."',1,".$arritem -> fields['zr'].",".$arritem -> fields['maxwt'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",1,".$arritem -> fields['szyb'].",'".$arritem -> fields['twohand']."', ".$arritem -> fields['repair'].")") or error(DB_ERROR3);
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
            }
            $test -> Close();
        }
        error (CRIME_RESULT2." ".$arritem -> fields['name'].CRIME_RESULT3);
    }
}
?>
