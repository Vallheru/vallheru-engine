<?php
/**
 *   File functions:
 *   Function to scale avatars
 *
 *   @name                 : avatars.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.1
 *   @since                : 26.06.2006
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
// $Id: avatars.php 386 2006-06-26 11:25:40Z thindil $


/**
 * Function to scale avatars
 */
function scaleavatar($strFilename)
{
    $arrImageparams = getimagesize($strFilename);
    $intWidth = $arrImageparams[0];
    $intHeight = $arrImageparams[1];
    if ($arrImageparams[0] > 200)
    {
        $intWidth = 200;
        $fltPercent = $arrImageparams[0] / 100;
        $fltPercent2 = ($arrImageparams[0] - $intWidth) / $fltPercent;
        $intHeight = ceil($intHeight - ($intHeight * ($fltPercent2 / 100)));
    }
    if ($arrImageparams[1] > 100)
    {
        $intHeight = 100;
        $fltPercent = $arrImageparams[1] / 100;
        $fltPercent2 = ($arrImageparams[1] - $intHeight) / $fltPercent;
        $intWidth = ceil($intWidth - ($intWidth * ($fltPercent2 / 100)));
    }
    $arrParams = array($intWidth, $intHeight);
    return $arrParams;
}
?>
