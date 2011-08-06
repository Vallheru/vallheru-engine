<?php
/**
 *   File functions:
 *   Bonus to abilities
 *
 *   @name                 : abilitybonus.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 23.08.2006
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
// $Id: abilitybonus.php 566 2006-09-13 09:31:08Z thindil $

/**
 * Bonus to ability - return new value of ability
 */
function abilitybonus($strAbility)
{
    global $db;
    global $player;

    /**
     * Add bless
     */
    $objBless = $db -> Execute("SELECT `bless`, `blessval` FROM `players` WHERE `id`=".$player -> id);
    if ($objBless -> fields['bless'] == $strAbility)
    {
        $player -> $strAbility = $player -> $strAbility + $objBless -> fields['blessval'];
        $db -> Execute("UPDATE `players` SET `bless`='', `blessval`=0 WHERE `id`=".$player -> id);
    }
    $objBless -> Close();
    
    /**
     * Add bonus from race and class
     */
    if ($player -> clas == 'Rzemieślnik')
    {
        $intBonus = ($player -> level / 10);
        if ($player -> race == 'Gnom')
        {
            $intBonus = $intBonus + ($player -> level / 5);
        }
        $intMaxbonus = $player -> $strAbility * 2;
        if ($intBonus > $intMaxbonus)
        {
            $intBonus = $intMaxbonus;
        }
        $intAbility = $player -> $strAbility + $intBonus;
    }
        else
    {
        $intAbility = $player -> $strAbility;
    }
    return $intAbility;
}
?>
