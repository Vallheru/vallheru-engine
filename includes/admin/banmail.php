<?php
/**
 *   File functions:
 *   Ban sending mails in game by player
 *
 *   @name                 : banmail.php                            
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
// $Id: banmail.php 712 2006-10-13 15:30:50Z thindil $

$smarty -> assign(array("Blocklist" => BLOCK_LIST,
                        "Ablock" => A_BLOCK,
                        "Aunblock" => A_UNBLOCK,
                        "Mailid" => MAIL_ID,
                        "Amake" => A_MAKE));
$arrTemp = array();
$i = 0;
$objBan = $db -> Execute("SELECT `id` FROM `ban_mail` WHERE owner=0");
while (!$objBan -> EOF)
{
    $arrTemp[$i] = $objBan -> fields['id'];
    $i++;
    $objBan -> MoveNext();
}
$objBan -> Close();
$smarty -> assign ("List1", $arrTemp);
if (isset ($_GET['step']) && $_GET['step'] == 'mail') 
{
    if ($_POST['mail'] == 'blok') 
    {
        $db -> Execute("INSERT INTO `ban_mail` (`id`) VALUES(".$_POST['mail_id'].")");
        error (YOU_BLOCK." ".$_POST['mail_id']);
    }
    if ($_POST['mail'] == 'odblok') 
    {
        $db -> Execute("DELETE FROM `ban_mail` WHERE `id`=".$_POST['mail_id']." AND `owner`=0");
        error (YOU_UNBLOCK." ".$_POST['mail_id']);
    }
}
?>
