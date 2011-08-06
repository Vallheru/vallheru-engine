<?php
/**
 *   File functions:
 *   Check for aviable contruction astral machine
 *
 *   @name                 : checkastral.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.1
 *   @since                : 24.06.2006
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
// $Id: checkastral.php 377 2006-06-25 06:52:23Z thindil $

/**
 * Function check for aviable construction astral machine
 */
function checkastral($intTribeid)
{
    global $db;

    $arrComponents = array(array(8, 8, 6, 6, 4, 4, 2), 
                           array(10, 8, 6, 4, 2), 
                           array(10, 8, 6, 4, 2));
    $arrType = array('C', 'O', 'T');
    $objAstral = $db -> Execute("SELECT `owner` FROM `astral_machine` WHERE `owner`=".$intTribeid);
    if (!$objAstral -> fields['owner'])
    {
        $db -> Execute("INSERT INTO `astral_machine` (`owner`, `aviable`) VALUES(".$intTribeid.", 'N')");
    }
    $objAstral -> Close();
    for ($i = 0; $i < 3; $i++)
    {
        $strType = $arrType[$i]."%";
        $objComponent = $db -> Execute("SELECT `amount`, `type` FROM `astral` WHERE `owner`=".$intTribeid." AND `type` LIKE '".$strType."' AND `location`='C'");
        if (!$objComponent -> fields['amount'])
        {
            $db -> Execute("UPDATE `astral_machine` SET `aviable`='N' WHERE `owner`=".$intTribeid);
            return;
        }
        while (!$objComponent -> EOF)
        {
            $intNumber = (int)str_replace($arrType[$i], "", $objComponent -> fields['type']);
            if ($arrComponents[$i][$intNumber] > $objComponent -> fields['amount'])
            {
                $db -> Execute("UPDATE `astral_machine` SET `aviable`='N' WHERE `owner`=".$intTribeid);
                return;
            }
            $objComponent -> MoveNext();
        }
        $objComponent -> Close();
    }
    $db -> Execute("UPDATE `astral_machine` SET `aviable`='Y' WHERE `owner`=".$intTribeid);
}
?>
