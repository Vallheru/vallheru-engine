<?php
/**
 *   File functions:
 *   Function to scale avatars
 *
 *   @name                 : avatars.php                            
 *   @copyright            : (C) 2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 22.11.2011
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
 * Function to scale avatars
 */
function scaleavatar($strFilename, $intMaxWidth = 400, $intMaxHeight = 200)
{
    $arrImageparams = getimagesize($strFilename);
    $intWidth = $arrImageparams[0];
    $intHeight = $arrImageparams[1];
    if ($intWidth > $intMaxWidth || $intHeight > $intMaxHeight)
      {
	if ($intWidth - $intMaxWidth > $intHeight - $intMaxHeight)
	  {
	    $fltPercent = $intWidth / 100;
	  }
	else
	  {
	    $fltPercent = $intHeight / 100;
	  }
	if ($arrImageparams[0] > $intMaxWidth)
	  {
	    $intWidth = $intMaxWidth;
	    $fltPercent2 = ($arrImageparams[0] - $intWidth) / $fltPercent;
	    $intHeight = ceil($intHeight - ($intHeight * ($fltPercent2 / 100)));
	  }
	if ($arrImageparams[1] > $intMaxHeight)
	  {
	    $intHeight = $intMaxHeight;
	    $fltPercent2 = ($arrImageparams[1] - $intHeight) / $fltPercent;
	    $intWidth = ceil($intWidth - ($intWidth * ($fltPercent2 / 100)));
	  }
    }
    $arrParams = array($intWidth, $intHeight);
    return $arrParams;
}
?>
