<?php
/**
 *   File functions:
 *   Functions to add items to ofert on markets
 *
 *   @name                 : marketaddto.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 29.09.2006
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
// $Id: marketaddto.php 642 2006-09-29 09:31:36Z thindil $


/**
 * Function to add to ofert on mineral market
 */
function addtomin($intId, $strMineral, $intIndex, $intOwner)
{
    global $db;

    if ($intIndex == 0) 
    {
        $db -> Execute("UPDATE `players` SET `platinum`=`platinum`-".$_POST['amount']." WHERE `id`=".$intOwner);
    } 
        else 
    {
        $db -> Execute("UPDATE `minerals` SET `".$strMineral."`=`".$strMineral."`-".$_POST['amount']." WHERE `owner`=".$intOwner);
    }
    $db -> Execute("UPDATE `pmarket` SET `ilosc`=`ilosc`+".$_POST['amount']." WHERE `id`=".$intId);
}

/**
 * Function to add to ofert on item and rings market
 */
function addtoitem($strType, $intId, $intDur)
{
    global $db;

    if ($strType != 'R')
    {
        $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$intId);
    }
        else
    {
        $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$intDur." WHERE `id`=".$intId);
    }
}

/**
 * Function to add to ofert on herb market
 */
function addtoherb($intId, $strName, $intOwner, $intAmount)
{
    global $db;

    $db -> Execute("UPDATE `hmarket` SET `ilosc`=`ilosc`+".$intAmount." WHERE `id`=".$intId);
    $db -> Execute("UPDATE `herbs` SET `".$strName."`=`".$strName."`-".$intAmount." WHERE `gracz`=".$intOwner);
}

/**
 * Function to add to ofert on astral market
 */
function addtoastral($intId, $intAmount, $intOwner, $strType, $intNumber)
{
    global $db;

    $db -> Execute("UPDATE `amarket` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$intId);
    if ($intAmount == $_POST['amount'])
    {
        $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$intOwner." AND `type`='".$strType."' AND `number`=".$intNumber." AND `location`='V'");
    }
    else
    {
        $db -> Execute("UPDATE `astral` SET `amount`=`amount`-".$_POST['amount']." WHERE `owner`=".$intOwner." AND `type`='".$strType."' AND `number`=".$intNumber." AND `location`='V'");
    }
}
?>
