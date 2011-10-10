<?php
/**
 *   File functions:
 *   Functions to delete one ofert from markets
 *
 *   @name                 : marketdel.php                            
 *   @copyright            : (C) 2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 10.10.2011
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
function deletemin($intId, $strName, $intAmount, $intOwner, $arrNames, $intAmount2 = 0)
{
    global $db;

    $arrSqlname = array('', 'copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
    $intKey = array_search($strName, $arrNames);
    $strMineral = $arrSqlname[$intKey];
    if ($intAmount2 > 0)
      {
	$intAmount = $intAmount2;
	$db->Execute("UPDATE `pmarket` SET `ilosc`=`ilosc`-".$intAmount." WHERE `id`=".$intId) or die($db->ErrorMsg());
      }
    else
      {
	$db -> Execute("DELETE FROM `pmarket` WHERE `id`=".$intId);
      }
    if ($strName == 'Mithril') 
      {
        $db -> Execute("UPDATE `players` SET `platinum`=`platinum`+".$intAmount." WHERE `id`=".$intOwner);
      } 
    else 
      {
	$db -> Execute("UPDATE `minerals` SET `".$strMineral."`=`".$strMineral."`+".$intAmount." WHERE `owner`=".$intOwner);
      }
}

/**
 * Function to delete ofert from items markets
 */
function deleteitem($objItem, $intOwner, $intAmount = 0)
{
    global $db;
    
    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$objItem -> fields['name']."' AND `wt`=".$objItem -> fields['wt']." AND `type`='".$objItem -> fields['type']."' AND `status`='U' AND `owner`=".$intOwner." AND `power`=".$objItem -> fields['power']." AND `zr`=".$objItem -> fields['zr']." AND `szyb`=".$objItem -> fields['szyb']." AND `maxwt`=".$objItem -> fields['maxwt']." AND `poison`=".$objItem -> fields['poison']." AND `cost`=1 AND `ptype`='".$objItem -> fields['ptype']."' AND `twohand`='".$objItem -> fields['twohand']."'");
    if (!$test -> fields['id']) 
      {
	if ($intAmount == 0)
	  {
	    $db -> Execute("UPDATE `equipment` SET `status`='U', `cost`=1 WHERE `id`=".$objItem -> fields['id']);
	  }
	else
	  {
	    if ($objItem->fields['type'] != 'R')
	      {
		$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `ptype`, `repair`) VALUES(".$intOwner.", '".$objItem->fields['name']."', ".$objItem->fields['power'].", '".$objItem->fields['type']."', 1, ".$objItem->fields['zr'].", ".$objItem->fields['wt'].", ".$objItem->fields['minlev'].", ".$objItem->fields['maxwt'].", ".$intAmount.", '".$objItem->fields['magic']."', ".$objItem->fields['poison'].", ".$objItem->fields['szyb'].", '".$objItem->fields['twohand']."', '".$objItem->fields['ptype']."', ".$objItem->fields['repair'].")");
	      }
	    else
	      {
		$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `ptype`, `repair`) VALUES(".$intOwner.", '".$objItem->fields['name']."', ".$objItem->fields['power'].", '".$objItem->fields['type']."', 1, ".$objItem->fields['zr'].", ".$intAmount.", ".$objItem->fields['minlev'].", ".$intAmount.", 1, '".$objItem->fields['magic']."', ".$objItem->fields['poison'].", ".$objItem->fields['szyb'].", '".$objItem->fields['twohand']."', '".$objItem->fields['ptype']."', ".$objItem->fields['repair'].")");
	      }
	  }
      } 
    else 
      {
	if ($intAmount > 0)
	  {
	    if ($objItem -> fields['type'] != 'R')
	      {
		$objItem->fields['amount'] = $intAmount;
	      }
	    else
	      {
		$objItem->fields['wt'] = $intAmount;
	      }
	  }
	else
	  {
	    $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objItem -> fields['id']);
	  }
	if ($objItem -> fields['type'] != 'R')
	  {
	    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$objItem -> fields['amount']." WHERE `id`=".$test -> fields['id']);
	  }
	else
	  {
	    $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$objItem -> fields['wt']." WHERE `id`=".$test -> fields['id']);
	  }
      }
    $test -> Close();
    if ($intAmount > 0)
      {
	if ($objItem->fields['type'] != 'R')
	  {
	    $db->Execute("UPDATE `equipment` SET `amount`=`amount`-".$intAmount." WHERE `id`=".$objItem->fields['id']);
	  }
	else
	  {
	    $db->Execute("UPDATE `equipment` SET `wt`=`wt`-".$intAmount." WHERE `id`=".$objItem->fields['id']);
	  }
      }
}

/**
 * Function to delete ofert from herbs market
 */
function deleteherb($intId, $strName, $intAmount, $intOwner, $arrNames, $intAmount2 = 0)
{
    global $db;

    $arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
    $intKey = array_search($strName, $arrNames);
    if ($intAmount2 > 0)
      {
	$intAmount = $intAmount2;
	$db->Execute("UPDATE `hmarket` SET `ilosc`=`ilosc`-".$intAmount." WHERE `id`=".$intId);
      }
    else
      {
	$db -> Execute("DELETE FROM `hmarket` WHERE `id`=".$intId);
      }
    $db -> Execute("UPDATE `herbs` SET `".$arrSqlname[$intKey]."`=`".$arrSqlname[$intKey]."`+".$intAmount." WHERE `gracz`=".$intOwner);
}

/**
 * Function to delete ofert from potion market
 */
function deletepotion($objItem, $intOwner, $intAmount = 0)
{
    global $db;

    $test = $db -> Execute("SELECT `id` FROM `potions` WHERE `name`='".$objItem -> fields['name']."' AND `owner`=".$intOwner." AND `status`='K' AND `power`=".$objItem -> fields['power']);
    if (!$test -> fields['id']) 
      {
	if ($intAmount == 0)
	  {
	    $db -> Execute("UPDATE `potions` SET `status`='K', `cost`=1 WHERE `id`=".$objItem -> fields['id']);
	  }
	else
	  {
	    $db -> Execute("INSERT INTO `potions` (`owner`, `name`, `efect`, `power`, `status`, `cost`, `type`, `amount`) VALUES(".$intOwner.", '".$objItem->fields['name']."', '".$objItem->fields['efect']."', ".$objItem->fields['power'].", 'K' , 1, '".$objItem->fields['type']."', ".$intAmount.")");
	  }
      } 
    else 
      {
	if ($intAmount > 0)
	  {
	    $objItem->fields['amount'] = $intAmount;
	  }
	else
	  {
	    $db -> Execute("DELETE FROM `potions` WHERE `id`=".$objItem -> fields['id']);
	  }
	$db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$objItem -> fields['amount']." WHERE `id`=".$test -> fields['id']);
      }
    $test -> Close();
    if ($intAmount > 0)
      {
	$db->Execute("UPDATE `potions` SET `amount`=`amount`-".$intAmount." WHERE `id`=".$objItem->fields['id']);
      }
}

/**
 * Function to delete ofert from astral market
 */
function deleteastral($objItem, $intOwner, $intAmount = 0)
{
    global $db;

    if ($intAmount > 0)
      {
	$objItem->fields['amount'] = $intAmount;
	$db->Execute("UPDATE `amarket` SET `amount`=`amount`-".$intAmount." WHERE `id`=".$objItem->fields['id']);
      }
    else
      {
	$db -> Execute("DELETE FROM `amarket` WHERE `id`=".$objItem -> fields['id']);
      }
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
}
?>
