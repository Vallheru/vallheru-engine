<?php
/**
 *   File functions:
 *   Bonus to stats from equipment
 *
 *   @name                 : statsbonus.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 25.07.2006
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
// $Id: statsbonus.php 527 2006-07-25 20:25:50Z thindil $

/**
 * Bonus from equipment - return new values of stats
 */
function statbonus ()
{
    global $db;
    global $player;

    $arrEquip = $player -> equipment();
    $fltCuragi = $player -> agility;
    if ($arrEquip[3][0])
    {
        if ($arrEquip[3][5] < 0) 
        {
            $intAgibonus = str_replace("-","",$arrEquip[3][5]);
        } 
            elseif ($arrEquip[3][5] >= 0) 
        {
            $intAgibonus = 0 - $arrEquip[3][5];
        }
        $fltCuragi = $fltCuragi + $intAgibonus;
    }
    if ($arrEquip[5][0])
    {
        if ($arrEquip[5][5] < 0) 
        {
            $intAgibonus = str_replace("-","",$arrEquip[5][5]);
        } 
            elseif ($arrEquip[5][5] >= 0) 
        {
            $intAgibonus = 0 - $arrEquip[5][5];
        }
        $fltCuragi = $fltCuragi + $intAgibonus;
    }
    if ($arrEquip[4][0])
    {
        if ($arrEquip[4][5] < 0) 
        {
            $intAgibonus = str_replace("-","",$arrEquip[4][5]);
        } 
            elseif ($arrEquip[4][5] >= 0) 
        {
            $intAgibonus = 0 - $arrEquip[4][5];
        }
        $fltCuragi = $fltCuragi + $intAgibonus;
    }
    $fltCurspeed = $player -> speed;
    if ($arrEquip[0][0])
    {
        $fltCurspeed = $fltCurspeed + $arrEquip[0][7];
    }
    $arrCurstats = array($fltCuragi, $player -> strength, $player -> inteli, $player -> wisdom, $fltCurspeed, $player -> cond);
    if ($arrEquip[9][2])
    {
        $arrRings = array(R_AGI, R_STR, R_INT, R_WIS, R_SPE, R_CON);
        $arrRingtype = explode(" ", $arrEquip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $arrCurstats[$intKey] = $arrCurstats[$intKey] + $arrEquip[9][2];
    }
    if ($arrEquip[10][2])
    {
        $arrRings = array(R_AGI, R_STR, R_INT, R_WIS, R_SPE, R_CON);
        $arrRingtype = explode(" ", $arrEquip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $arrCurstats[$intKey] = $arrCurstats[$intKey] + $arrEquip[10][2];
    }
    return $arrCurstats;
}
?>
