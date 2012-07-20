<?php
/**
 *   File functions:
 *   Take money from player
 *
 *   @name                 : takeaway.php                            
 *   @copyright            : (C) 2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 20.07.2012
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

$smarty -> assign(array("Takeid" => TAKE_ID,
                        "Takeamount" => TAKE_AMOUNT,
                        "Atakeg" => A_TAKE_G,
                        "Treason" => T_REASON,
                        "Tinjured" => T_INJURED,
                        "Takeinfo" => TAKE_INFO));
if (isset ($_GET['step']) && $_GET['step'] == 'takenaway') 
{
    $_POST['verdict'] = strip_tags($_POST['verdict']);
    checkvalue($_POST['taken']);
    checkvalue($_POST['id']);
    checkvalue($_POST['id2']);
    if (empty($_POST['verdict'])) 
    {
        error ('Podaj powód kary.');
    }    
    $dotowany = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['id']);
    $strReceiversName = $dotowany -> fields['user'];
    if (!$dotowany -> fields['id']) 
    {
        error ('Nie ma takiego gracza (karany)');
    }
    $dotowany -> Close();

    $dotowany = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_POST['id2']);
    if (!$dotowany -> fields['id']) 
    {
        error ('Nie ma takiego gracza (poszkodowany)');
    }
    $dotowany -> Close();
    $intGold = floor($_POST['taken'] / 2);

    $strDate = $db -> DBDate($newdate);
    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$_POST['taken']." WHERE `id`=".$_POST['id']);
    $db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold." WHERE `id`=".$_POST['id2']);
    $objKgold = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='gold'");
    $intGold2 = $objKgold->fields['value'] + $intGold;
    $objKgold->Close();
    $db->Execute("UPDATE `settings` SET `value`='".$intGold2."' WHERE `setting`='gold'");
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['id'].", '".YOU_GET.$_POST['verdict'].T_AMOUNT.$_POST['taken'].GOLD_COINS.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user." </a></b>, ID: <b>".$player -> id."</b>.', ".$strDate.")");

    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['id2'].", '".T_PLAYER1.'<b><a href="view.php?view='.$_POST['id'].'">'.$strReceiversName.'</a></b>'.T_PLAYER2.'<b>'.$_POST['id'].'</b>'.HAS_TAKEN.$_POST['verdict'].SANCTION_SET.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>".T_PLAYER2."<b>".$player -> id."</b>.', ".$strDate.")");
    error ($_POST['taken']." ".GOLD_TAKEN.": ".$_POST['id']);
}
?>
