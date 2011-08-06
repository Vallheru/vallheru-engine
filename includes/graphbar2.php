<?php
/**
 *   File functions:
 *   Create graph bars - empty vial
 *
 *   @name                 : graphbar2.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 30.06.2006
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
// $Id: graphbar2.php 411 2006-06-30 12:30:41Z thindil $

$imgImage = ImageCreate(1, 10);

$imgColor = ImageColorAllocate($imgImage, 255, 0, 0);
ImageColorTransparent($imgImage, $imgColor);

ImageFill($imgImage, 0, 0, $imgColor);

header('Content-type: image/png');
ImagePng($imgImage);

ImageDestroy($imgImage);
?>
