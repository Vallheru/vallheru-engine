<?php
/**
 *   File functions:
 *   Tribes menu
 *
 *   @name                 : tribemenu.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 21.09.2012
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

$arrMenus = array(array("address" => 'tribes.php?view=my', "name" => 'Główna'),
		  array("address" => 'tribes.php?view=my&amp;step=members', "name" => 'Członkowie'),
		  array("address" => 'tribes.php?view=my&amp;step=quit', "name" => 'Opuść klan'),
		  array("address" => 'tforums.php?view=topics', "name" => 'Forum klanu'),
		  array("address" => 'tribeminerals.php', "name" => 'Skarbiec'));
$objTribe = $db->Execute("SELECT `owner`, `level` FROM `tribes` WHERE `id`=".$player->tribe);
if ($objTribe->fields['level'] > 1)
  {
    $arrMenus[] = array("address" => 'tribearmor.php', "name" => 'Zbrojownia');
    $arrMenus[] = array("address" => 'tribeware.php', "name" => 'Magazyn');
  }
if ($objTribe->fields['level'] > 2)
  {
    $arrMenus[] = array("address" => 'tribeherbs.php', "name" => 'Zielnik');
    $arrMenus[] = array("address" => 'tribeastral.php', "name" => 'Astralny skarbiec');
  }
$perm = $db -> Execute("SELECT * FROM tribe_perm WHERE tribe=".$player->tribe." AND player=".$player->id);
$test = array($perm -> fields['messages'],$perm -> fields['wait'],$perm -> fields['kick'],$perm -> fields['army'],$perm -> fields['attack'],$perm -> fields['loan'],$perm -> fields['armory'],$perm -> fields['warehouse'],$perm -> fields['bank'],$perm -> fields['herbs'], $perm -> fields['mail'], $perm -> fields['ranks']);
if (in_array(1, $test) || $player->id == $objTribe->fields['owner'])
  {
    $arrMenus[] = array("address" => 'tribeadmin.php', "name" => 'Opcje przywódcy');
  }
$objTribe->Close();

$smarty->assign("Menus", $arrMenus);

?>