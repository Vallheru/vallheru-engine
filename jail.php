<?php
/**
 *   File functions:
 *   Jail
 *
 *   @name                 : jail.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 12.09.2011
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

$title = "Lochy";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/jail.php");

if ($player -> location != 'Altara' && $player -> location != 'Lochy' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

$smarty -> assign("Prisoner", '');

/**
* If player is in city - show prisoners
*/
if ($player -> location == 'Altara' || $player -> location == 'Ardulith') 
{
    $smarty -> display ('jail.tpl');
    $jail = $db -> Execute("SELECT * FROM `jail` ORDER BY `id` ASC");
    $number = $jail -> RecordCount();
    $smarty -> assign ("Number", $number);
    if ($number > 0) 
    {
        $arrid = array();
        $arrname = array();
        $arrdate = array();
        $arrverdict = array();
        $arrduration = array();
        $arrjailid = array();
        $arrcost = array();
        $arrDurationr = array();
        $i = 0;
        while (!$jail -> EOF) 
        {
            $pname = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$jail -> fields['prisoner']);
            $arrid[$i] = $jail -> fields['prisoner'];
            $arrname[$i] = $pname -> fields['user'];
            $arrdate[$i] = $jail -> fields['data'];
            $arrverdict[$i] = $jail -> fields['verdict'];
            $arrduration[$i] = ceil($jail -> fields['duration'] / 7);
            $arrDurationr[$i] = $jail -> fields['duration'];
            /**
             * Easter egg - delete if you want
             */
            if ($arrid[$i] == 471)
            {
                $arrduration[$i] = 'Dożywocie';
            }
            $arrjailid[$i] = $jail -> fields['id'];
            $arrcost[$i] = $jail -> fields['cost'];
            $jail -> MoveNext();
            $i = $i + 1;
        }
        $smarty -> assign(array("Id" => $arrid, 
                                "Name" => $arrname, 
                                "Date" => $arrdate, 
                                "Verdict" => $arrverdict, 
                                "Duration" => $arrduration, 
                                "Jailid" => $arrjailid, 
                                "Cost" => $arrcost,
                                "Duration2" => $arrDurationr,
                                "Pname" => P_NAME,
                                "Pid" => P_ID,
                                "Pdate" => P_DATE,
                                "Preason" => P_REASON,
                                "Pduration" => P_DURATION,
                                "Pduration2" => P_DURATION_R,
                                "Pcost" => P_COST,
                                "Goldcoins" => GOLD_COINS,
                                "Jailinfo" => JAIL_INFO));
    }
        else
    {
        $smarty -> assign(array("Noprisoners" => NO_PRISONERS,
                                "Jailinfo" => JAIL_INFO));
    }
    $jail -> Close();
}

/**
* If player is in jail - show info about it
*/
if ($player -> location == 'Lochy') 
{
    $prisoner = $db -> Execute("SELECT * FROM `jail` WHERE `prisoner`=".$player -> id);
    $intTime = ceil($prisoner -> fields['duration'] / 7);
    /**
     * Easter egg - delete if you want
     */
    if ($player -> id == 471)
    {
        $intTime = 'Dożywocie';
    }
    $smarty -> assign(array("Date" => $prisoner -> fields['data'], 
                            "Verdict" => $prisoner -> fields['verdict'], 
                            "Duration" => $intTime, 
                            "Cost" => $prisoner -> fields['cost'],
                            "Duration2" => $prisoner -> fields['duration'],
                            "Youare" => YOU_ARE,
                            "Pdate" => P_DATE,
                            "Pduration" => P_DURATION,
                            "Pduration2" => P_DURATION_R,
                            "Preason" => P_REASON,
                            "Pcost" => P_COST));
    $prisoner -> Close();
}

/**
* Pay for free prisoner
*/
if (isset($_GET['prisoner'])) 
{
    checkvalue($_GET['prisoner']);
    $prisoner = $db -> Execute("SELECT * FROM `jail` WHERE `id`=".$_GET['prisoner']);
    if (!$prisoner -> fields['id']) 
    {
        error (NO_PRISONER);
    }
    if ($prisoner -> fields['cost'] > $player -> credits) 
    {
        error (NO_MONEY);
    }
    if ($player -> id == $prisoner -> fields['prisoner']) 
    {
        error (NO_PERM);
    }
    $pname = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$prisoner -> fields['prisoner']);
    if (!isset($_GET['step']))
    {
        $_GET['step'] = '';
        $smarty -> assign(array("Youwant" => YOU_WANT,
                                "Ayes" => YES,
                                "Prisonername" => $pname -> fields['user'],
                                "Aback" => A_BACK));
    }
    if (isset($_GET['step']) && $_GET['step'] == 'confirm')
    {
        $db -> Execute("UPDATE `players` SET `miejsce`='Altara' WHERE `id`=".$prisoner -> fields['prisoner']);
        $db -> Execute("DELETE FROM `jail` WHERE `id`=".$prisoner -> fields['id']);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$prisoner -> fields['cost']." WHERE `id`=".$player -> id);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$prisoner -> fields['prisoner'].",'".L_PLAYER.'<a href="view.php?view='.$player -> id.'">'.$player -> user.'</a>'.L_ID.'<b>'.$player -> id.'</b>'.PAY_FOR."', ".$strDate.", 'J')");
        error (YOU_PAY.$prisoner -> fields['cost'].GOLD_FOR.$pname -> fields['user'].L_ID.$prisoner -> fields['prisoner']);
    }
    $smarty -> assign("Step", $_GET['step']);
}
    else
{
    $_GET['prisoner'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Location" => $player -> location,
                        "Prisoner" => $_GET['prisoner']));
$smarty -> display ('jail.tpl');

require_once("includes/foot.php");
?>
