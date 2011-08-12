<?php
/**
 *   File functions:
 *   Functions to delete one ofert from markets
 *
 *   @name                 : marketdel.php                            
 *   @copyright            : (C) 2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 12.08.2011
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
 * Function to delete ofert from mineral market
 */
function deletemin($intId, $strName, $intAmount, $intOwner, $arrNames)
{
    global $db;

    $arrSqlname = array('', 'copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
    $intKey = array_search($strName, $arrNames);
    $strMineral = $arrSqlname[$intKey];
    if ($strName == 'Mithril') 
    {
        $db -> Execute("UPDATE `players` SET `platinum`=`platinum`+".$intAmount." WHERE `id`=".$intOwner);
    } 
        else 
    {
        $db -> Execute("UPDATE `minerals` SET `".$strMineral."`=`".$strMineral."`+".$intAmount." WHERE `owner`=".$intOwner);
    }
    $db -> Execute("DELETE FROM `pmarket` WHERE `id`=".$intId);
}

/**
 * Function to delete ofert from items markets
 */
function deleteitem($objItem, $intOwner)
{
    global $db;
    
    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$objItem -> fields['name']."' AND `wt`=".$objItem -> fields['wt']." AND `type`='".$objItem -> fields['type']."' AND `status`='U' AND `owner`=".$intOwner." AND `power`=".$objItem -> fields['power']." AND `zr`=".$objItem -> fields['zr']." AND `szyb`=".$objItem -> fields['szyb']." AND `maxwt`=".$objItem -> fields['maxwt']." AND `poison`=".$objItem -> fields['poison']." AND `cost`=1 AND `ptype`='".$objItem -> fields['ptype']."' AND `twohand`='".$objItem -> fields['twohand']."'");
    if (!$test -> fields['id']) 
    {
        $db -> Execute("UPDATE `equipment` SET `status`='U', `cost`=1 WHERE id=".$objItem -> fields['id']);
    } 
        else 
    {
        if ($objItem -> fields['type'] != 'R')
        {
            $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$objItem -> fields['amount']." WHERE `id`=".$test -> fields['id']);
        }
            else
        {
            $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$objItem -> fields['wt']." WHERE `id`=".$test -> fields['id']);
        }
        $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objItem -> fields['id']);
    }
    $test -> Close();
}

/**
 * Function to delete ofert from herbs market
 */
function deleteherb($intId, $strName, $intAmount, $intOwner, $arrNames)
{
    global $db;

    $arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
    $intKey = array_search($strName, $arrNames);
    $db -> Execute("UPDATE `herbs` SET `".$arrSqlname[$intKey]."`=`".$arrSqlname[$intKey]."`+".$intAmount." WHERE `gracz`=".$intOwner);
    $db -> Execute("DELETE FROM `hmarket` WHERE `id`=".$intId);
}

/**
 * Function to delete ofert from potion market
 */
function deletepotion($objItem, $intOwner)
{
    global $db;

    $test = $db -> Execute("SELECT `id` FROM `potions` WHERE `name`='".$objItem -> fields['name']."' AND `owner`=".$intOwner." AND `status`='K' AND `power`=".$objItem -> fields['power']);
    if (!$test -> fields['id']) 
    {
        $db -> Execute("UPDATE `potions` SET `status`='K', `cost`=1 WHERE `id`=".$objItem -> fields['id']);
    } 
        else 
    {
        $db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$objItem -> fields['amount']." WHERE `id`=".$test -> fields['id']);
        $db -> Execute("DELETE FROM `potions` WHERE `id`=".$objItem -> fields['id']);
    }
    $test -> Close();
}

/**
 * Function to delete ofert from astral market
 */
function deleteastral($objItem, $intOwner)
{
    global $db;

    $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$intOwner." AND `type`='".$objItem -> fields['type']."' AND `number`=".$objItem -> fields['number']." AND `location`='V'");
    if (!$objTest -> fields['amount'])
    {
        $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$intOwner.", '".$objItem -> fields['type']."', ".$objItem -> fields['number'].", ".$objItem -> fields['amount'].", 'V')");
    }
        else
    {
        $db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$objItem -> fields['amount']." WHERE `owner`=".$intOwner." AND `type`='".$objItem -> fields['type']."' AND `number`=".$objItem -> fields['number']." AND `location`='V'");
    }
    $objTest -> Close();
    $db -> Execute("DELETE FROM `amarket` WHERE `id`=".$objItem -> fields['id']);
}
?>
