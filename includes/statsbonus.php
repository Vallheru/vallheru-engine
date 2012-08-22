<?php
/**
 *   File functions:
 *   Bonus to stats from equipment
 *
 *   @name                 : statsbonus.php                            
 *   @copyright            : (C) 2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 22.08.2011
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
 * Bonus from equipment - return new values of stats
 */
function statbonus ()
{
    global $db;
    global $player;

    $fltCuragi = $player -> agility;
    if ($player->equip[3][0])
    {
        if ($player->equip[3][5] < 0) 
        {
            $intAgibonus = str_replace("-","",$player->equip[3][5]);
        } 
            elseif ($player->equip[3][5] >= 0) 
        {
            $intAgibonus = 0 - $player->equip[3][5];
        }
        $fltCuragi = $fltCuragi + $intAgibonus;
    }
    if ($player->equip[5][0])
    {
        if ($player->equip[5][5] < 0) 
        {
            $intAgibonus = str_replace("-","",$player->equip[5][5]);
        } 
            elseif ($player->equip[5][5] >= 0) 
        {
            $intAgibonus = 0 - $player->equip[5][5];
        }
        $fltCuragi = $fltCuragi + $intAgibonus;
    }
    if ($player->equip[4][0])
    {
        if ($player->equip[4][5] < 0) 
        {
            $intAgibonus = str_replace("-","",$player->equip[4][5]);
        } 
            elseif ($player->equip[4][5] >= 0) 
        {
            $intAgibonus = 0 - $player->equip[4][5];
        }
        $fltCuragi = $fltCuragi + $intAgibonus;
    }
    $fltCurspeed = $player -> speed;
    if ($player->equip[1][0])
    {
        $fltCurspeed = $fltCurspeed + $player->equip[1][7];
    }
    $arrCurstats = array($fltCuragi, $player -> strength, $player -> inteli, $player -> wisdom, $fltCurspeed, $player -> cond);
    if ($player->equip[9][2])
    {
        $arrRings = array(R_AGI, R_STR, R_INT, R_WIS, R_SPE, R_CON);
        $arrRingtype = explode(" ", $player->equip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $arrCurstats[$intKey] = $arrCurstats[$intKey] + $player->equip[9][2];
    }
    if ($player->equip[10][2])
    {
        $arrRings = array(R_AGI, R_STR, R_INT, R_WIS, R_SPE, R_CON);
        $arrRingtype = explode(" ", $player->equip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $arrCurstats[$intKey] = $arrCurstats[$intKey] + $player->equip[10][2];
    }
    return $arrCurstats;
}
?>
