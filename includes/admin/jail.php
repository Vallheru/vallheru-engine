<?php
/**
 *   File functions:
 *   Send player to jail
 *
 *   @name                 : jail.php                            
 *   @copyright            : (C) 2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 31.08.2012
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

if ($player->rank != 'Admin' && $player->rank != 'Staff')
  {
    error('Zapomnij o tym');
  }
$smarty -> assign(array("Jailid" => JAIL_ID,
                        "Jailreason" => JAIL_REASON,
                        "Jailtime" => JAIL_TIME,
                        "Aadd" => A_ADD));
if (isset ($_GET['step']) && $_GET['step'] == 'add') 
{
    $objTest = $db -> Execute("SELECT `miejsce` FROM `players` WHERE `id`=".$_POST['prisoner']);
    if ($_POST['prisoner'] != 1) 
    {
        $strDate = $db -> DBDate($newdate);
        $intTime = $_POST['time'] * 7;
        if ($objTest -> fields['miejsce'] == 'Lochy')
        {
            $objVerdict = $db -> Execute("SELECT `verdict` FROM `jail` WHERE `prisoner`=".$_POST['prisoner']);
            $strVerdict = $objVerdict -> fields['verdict']."; ".$_POST['verdict'];
            $objVerdict -> Close();
            $db -> Execute("UPDATE `jail` SET `duration`=`duration`+".$intTime.", `verdict`='".$strVerdict."' WHERE `prisoner`=".$_POST['prisoner']);
            require_once("languages/".$lang."/admin1.php");
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['prisoner'].", '".YOU_JAIL." ".$_POST['time']." ".DAYS2." ".$_POST['verdict'].". ".SEND_YOU.' <b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id."</b>.', ".$strDate.")") or die($db -> ErrorMsg());
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(1,'".$_POST['prisoner']." - ".YOU_JAIL.$_POST['time'].DAYS.$_POST['verdict'].SEND_YOU.$player -> user." ID: ".$player -> id."', ".$strDate.")");
        }
            else
        {
            $db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$_POST['prisoner'].", '".$_POST['verdict']."', ".$intTime.", 0, ".$strDate.")");
            $db -> Execute("UPDATE `players` SET `miejsce`='Lochy' WHERE `id`=".$_POST['prisoner']);
            require_once("languages/".$lang."/admin1.php");
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['prisoner'].", '".YOU_JAIL." ".$_POST['time']." ".DAYS2." ".$_POST['verdict'].". ".SEND_YOU.": ".$player -> user." ID: ".$player -> id."', ".$strDate.")") or die($db -> ErrorMsg());
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(1,'".$_POST['prisoner']." - ".YOU_JAIL.$_POST['time'].DAYS.$_POST['verdict'].SEND_YOU.$player -> user." ID: ".$player -> id."', ".$strDate.")");
        }
        error(PLAYER_JAIL.": ".$_POST['prisoner']." ".HAS_BEEN_J." ".$_POST['time']." ".DAYS." ".$_POST['verdict'].".");
    }
}
?>
