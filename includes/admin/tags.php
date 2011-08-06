<?php
/**
 *   File functions:
 *   Add immunited to player
 *
 *   @name                 : tags.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 13.10.2006
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
// $Id: tags.php 712 2006-10-13 15:30:50Z thindil $

$smarty -> assign(array("Giveimmu" => GIVE_IMMU,
                        "Agive" => A_GIVE));
if (isset ($_GET['step']) && $_GET['step'] == "tag") 
{
    $db -> Execute("UPDATE players SET immu='Y' WHERE id=".$_POST['tag_id']);
    error (YOU_ADD_I." <b>".$_POST['tag_id']."</b>.");
}
?>
