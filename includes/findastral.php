<?php
/**
 *   File functions:
 *   Find astral components function
 *
 *   @name                 : findastral.php                            
 *   @copyright            : (C) 2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 25.05.2012
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
require_once("languages/".$lang."/findastral.php");

/**
 * Find astral components in some locations
 */
function Findastral ($intChance)
{
    global $player;
    global $db;

    $intRoll = rand(1, $intChance);
    if ($intRoll == $intChance)
    {
        $intRoll2 = rand(1, 3);
        switch ($intRoll2)
        {
            case 1 :
              $strType = 'M';
              $intMaxnumber = 4;
              $intMaxnumber2 = 6;
              $strComponent = I_MAP;
              break;
            case 2 :
              $strType = 'P';
              $intMaxnumber = 14;
              $intMaxnumber2 = 4;
              $strComponent = I_PLAN;
              break;
            case 3 :
              $strType = 'R';
              $intMaxnumber = 14;
              $intMaxnumber2 = 4;
              $strComponent = I_RECIPE;
              break;
        }
        $intNumber = rand(0, $intMaxnumber);
        $intNumber2 = rand(0, $intMaxnumber2);
        $strType = $strType.$intNumber2;
        $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strType."' AND `number`=".$intNumber." AND `location`='V'") or die($db -> ErrorMsg());
        if ($objTest -> fields['amount'])
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`+1 WHERE `owner`=".$player -> id." AND `type`='".$strType."' AND `number`=".$intNumber." AND `location`='V'") or die($db -> ErrorMsg());
        }
            else
        {
          $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`) VALUES(".$player -> id.", '".$strType."', ".$intNumber.")") or die($db -> ErrorMsg());
        }
        return $strComponent;
    }
        else
    {
        return false;
    }
}


?>
