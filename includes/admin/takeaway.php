<?php
/**
 *   File functions:
 *   Take money from player
 *
 *   @name                 : takeaway.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 05.12.2006
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
// $Id: takeaway.php 879 2007-01-23 17:19:03Z thindil $

$smarty -> assign(array("Takeid" => TAKE_ID,
                        "Takeamount" => TAKE_AMOUNT,
                        "Atakeg" => A_TAKE_G,
                        "Treason" => T_REASON,
                        "Tinjured" => T_INJURED,
                        "Takeinfo" => TAKE_INFO));
if (isset ($_GET['step']) && $_GET['step'] == 'takenaway') 
{
    $_POST['verdict'] = strip_tags($_POST['verdict']);
    if (!ereg("^[1-9][0-9]*$", $_POST['taken']) || empty($_POST['verdict']) || !ereg("^[1-9][0-9]*$", $_POST['id']) || !ereg("^[1-9][0-9]*$", $_POST['id2'])) 
    {
        error (ERROR);
    }
    $strDate = $db -> DBDate($newdate);
    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$_POST['taken']." WHERE `id`=".$_POST['id']);
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['id'].", '".YOU_GET.$_POST['verdict'].T_AMOUNT.$_POST['taken'].GOLD_COINS.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user." </a></b>, ID: <b>".$player -> id."</b>.', ".$strDate.")");
    
    $dotowany = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['id']);
    $strReceiversName = $dotowany -> fields['user'];
    if (!$dotowany -> fields['id']) 
    {
        error (NO_PLAYER);
    }
    $dotowany -> Close();

    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['id2'].", '".T_PLAYER1.'<b><a href="view.php?view='.$_POST['id'].'">'.$strReceiversName.'</a></b>'.T_PLAYER2.'<b>'.$_POST['id'].'</b>'.HAS_TAKEN.$_POST['verdict'].SANCTION_SET.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>".T_PLAYER2."<b>".$player -> id."</b>.', ".$strDate.")");
    error ($_POST['taken']." ".GOLD_TAKEN.": ".$_POST['id']);
}
?>
