<?php
/**
 *   File functions:
 *   Functions to delete all oferts from markets
 *
 *   @name                 : marketdelall.php                            
 *   @copyright            : (C) 2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 29.02.2012
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
 * Function to delete oferts from mineral market
 */
function deleteallmin($intOwner, $arrMinerals)
{
    global $db;

    $del = $db -> Execute("SELECT * FROM pmarket WHERE seller=".$intOwner);
    $arrSqlname = array('', 'copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
    while (!$del -> EOF) 
    {
        $intKey = array_search($del -> fields['nazwa'], $arrMinerals);
        $strMineral = $arrSqlname[$intKey];
        if ($del -> fields['nazwa'] == 'Mithril') 
        {
            $db -> Execute("UPDATE players SET platinum=platinum+".$del -> fields['ilosc']." WHERE id=".$intOwner);
        } 
            else 
        {
            $db -> Execute("UPDATE minerals SET ".$strMineral."=".$strMineral."+".$del -> fields['ilosc']." WHERE owner=".$intOwner);
        }
        $db -> Execute("DELETE FROM pmarket WHERE id=".$del -> fields['id']);
        $del -> MoveNext();
    }
    $del -> Close();
}

/**
 * Function to delete oferts from herbs market
 */
function deleteallherb($intOwner, $arrName)
{
    global $db;

    $del = $db -> Execute("SELECT * FROM `hmarket` WHERE `seller`=".$intOwner);
    $arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
    while (!$del -> EOF) 
    {
        $intKey = array_search($del -> fields['nazwa'], $arrName);
        $db -> Execute("UPDATE `herbs` SET `".$arrSqlname[$intKey]."`=`".$arrSqlname[$intKey]."`+".$del -> fields['ilosc']." WHERE `gracz`=".$intOwner);
        $db -> Execute("DELETE FROM `hmarket` WHERE `id`=".$del -> fields['id']);
        $del -> MoveNext();
    }
    $del -> Close();
}

/**
 * Function to delete ofert from potion market
 */
function deleteallpotion($intOwner)
{
    global $db;

    $objPotion = $db -> Execute("SELECT * FROM `potions` WHERE `owner`=".$intOwner." AND `status`='R' GROUP BY `name`, `power`");
    while (!$objPotion -> EOF)
    {
        $objTest = $db -> Execute("SELECT `id` FROM `potions` WHERE `owner`=".$intOwner." AND `status`='K' AND `name`='".$objPotion -> fields['name']."' AND `power`=".$objPotion -> fields['power']);
        if (!$objTest -> fields['id'])
        {
            $db -> Execute("UPDATE `potions` SET `status`='K', `cost`=1 WHERE `id`=".$objPotion -> fields['id']);
        }  
            else 
        {
            $db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$objPotion -> fields['amount']." WHERE `id`=".$objTest -> fields['id']);
            $db -> Execute("DELETE FROM `potions` WHERE `id`=".$objPotion -> fields['id']);
        }
        $objTest -> Close();
        $objPotion -> MoveNext();
    }
}

/**
 * Function to delete ofert from astral market
 */
function deleteallastral($intOwner)
{
    global $db;

    $del = $db -> Execute("SELECT `id`, `seller`, `type`, `number`, `amount` FROM `amarket` WHERE `seller`=".$intOwner);
    while (!$del -> EOF) 
    {
        $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$intOwner." AND `type`='".$del -> fields['type']."' AND `number`=".$del -> fields['number']." AND `location`='V'");
        if (!$objTest -> fields['amount'])
        {
            $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$intOwner.", '".$del -> fields['type']."', ".$del -> fields['number'].", ".$del -> fields['amount'].", 'V')");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$del -> fields['amount']." WHERE `owner`=".$intOwner." AND `type`='".$del -> fields['type']."' AND `number`=".$del -> fields['number']." AND `location`='V'");
        }
        $objTest -> Close();
        $db -> Execute("DELETE FROM `amarket` WHERE `id`=".$del -> fields['id']);
        $del -> MoveNext();
    }
    $del -> Close();
}

/**
 * Function to delete all oferts from core market
 */
function deleteallcores($intOwner)
{
  global $db;

  $rem = $db->Execute("SELECT * FROM `core_market` WHERE `seller`=".$intOwner);
  while(!$rem->EOF)
    {
      $db -> Execute("INSERT INTO `core` (`owner`, `name`, `type`, `power`, `defense`, `gender`, `ref_id`, `wins`, `losses`) VALUES(".$intOwner.",'".$rem -> fields['name']."','".$rem -> fields['type']."',".$rem -> fields['power'].",".$rem -> fields['defense'].", '".$rem -> fields['gender']."', ".$rem -> fields['ref_id'].", ".$rem -> fields['wins'].", ".$rem -> fields['losses'].")") or error($db->ErrorMsg());
      $db -> Execute("DELETE FROM core_market WHERE id=".$rem -> fields['id']);
      $rem->MoveNext();
    }
  $rem->Close();
}
?>
