<?php
/**
 *   File functions:
 *   Roleplay profile
 *
 *   @name                 : roleplay.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 25.09.2011
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

$title = "Zobacz";
require_once("includes/head.php");

if (!isset($_GET['view']))
  {
    error("Zapomij o tym!<a href");
  }

checkvalue($_GET['view']);
$objProfile = $db->Execute("SELECT `id`, `roleplay`, `ooc` FROM `players` WHERE `id`=".$_GET['view']);
if (!$objProfile->fields['id'])
  {
    error("Nie ma takiego gracza!<a href");
  }

if (strlen($objProfile->fields['roleplay']) == 0)
  {
    error("Ten gracz nie posiada profilu fabularnego.<a href");
  }

$smarty->assign(array("Roleplay" => $objProfile->fields['roleplay'],
		      "OOC" => $objProfile->fields['ooc'],
		      "Info" => "Informacje dodatkowe (Poza Grą)",
		      "Vid" => $objProfile->fields['id']));
$objProfile->Close();
$smarty -> display ('roleplay.tpl');

require_once("includes/foot.php");
?>