<?php
/**
 *   File functions:
 *   Temple
 *
 *   @name                 : temple.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 16.11.2006
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

$title = "Świątynia";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/temple.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}
   
/**
* Assign variable to template
*/
$smarty -> assign("Message", '');

/**
* Work in temple
*/
if (isset ($_GET['temp']) && $_GET['temp'] == 'sluzba') 
{
    if (empty ($player -> deity)) 
    {
        error (NO_DEITY);
    }
    $smarty -> assign(array("Iwant" => I_WANT,
                            "Tamount" => T_AMOUNT,
                            "Awork" => A_WORK,
                            "Templeinfow" => TEMPLE_INFO_W,
                            "Templeinfo2w" => TEMPLE_INFO2_W,
                            "Aback" => BACK));
    if (isset($_GET['dalej'])) 
    {
        if (!isset($_POST['rep']) || !ereg("^[1-9][0-9]*$", $_POST['rep'])) 
        {
            error (ERROR);
        }
        if ($player -> hp == 0) 
        {
            error (YOU_DEAD);
        }
        $razy = round($_POST['rep'] * 0.2, 1);
        if ($player -> energy < $razy) 
        {
            error (NO_ENERGY);
        }
        $smarty -> assign ("Message", YOU_WORK.$_POST['rep'].YOU_WORK2);
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$razy.", `pw`=`pw`+".$_POST['rep']." WHERE `id`=".$player -> id);
    }
}

/**
* Pray to gods
*/
if (isset ($_GET['temp']) && $_GET['temp'] == 'modlitwa') 
{
    $objBless = $db -> Execute("SELECT `bless` FROM `players` WHERE `id`=".$player -> id);
    if (!empty($objBless -> fields['bless']))
    {
        error(YOU_HAVE);
    }
    $objBless -> Close();
    if ($player -> race == 'Człowiek')
    {
        $arrPraysCost = array('10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10');
    }
        elseif ($player -> race == 'Elf')
    {
        $arrPraysCost = array('7', '15', '10', '10', '7', '15', '15', '7', '7', '15', '7', '10', '7', '7', '15', '7', '7', '10');
    }
        elseif ($player -> race == 'Krasnolud')
    {
        $arrPraysCost = array('15', '7', '10', '10', '15', '7', '7', '15', '10', '7', '15', '10', '15', '15', '7', '15', '15', '7');
    }
        elseif ($player -> race == 'Hobbit')
    {
        $arrPraysCost = array('7', '15', '10', '10', '7', '10', '10', '15', '10', '10', '10', '10', '7', '7', '10', '10', '7', '10');
    }
        elseif ($player -> race == 'Jaszczuroczłek')
    {
        $arrPraysCost = array('7', '7', '15', '15', '7', '7', '10', '10', '10', '10', '10', '10', '15', '15', '15', '15', '15', '15');
    }
        elseif ($player -> race == 'Gnom')
    {
        $arrPraysCost = array('10', '15', '10', '15', '15', '10', '7', '7', '7', '15', '15', '15', '15', '7', '7', '15', '7', '7');
    }
        else
    {
        error(NO_RACE);
    }
    if ($player -> deity == 'Illuminati')
    {
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, ALC, FLE, WEA, SHO, DOD, CAS, BRE, MINI, LUMBER, HERBS, JEWEL);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17);
        for ($i = 0; $i < 17; $i ++)
        {
            $arrPraysCost[$i] = $arrPraysCost[$i] * 2;
        }
    }
        elseif ($player -> deity == 'Karserth')
    {
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, WEA, SHO, DOD);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 9, 10, 11);
    }
        elseif ($player -> deity == 'Anariel')
    {
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, ALC, DOD, CAS);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 7, 11, 12);
    }
        elseif ($player -> deity == 'Heluvald' || $player -> deity == 'Daeraell')
    {
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, ALC, FLE, BRE, MINI, LUMBER, HERBS, JEWEL);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 13, 14, 15, 16, 17);
    }
        elseif($player -> deity == 'Tartus')
    {
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, WEA, CAS);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 9, 12);
    }
        elseif($player -> deity == 'Oregarl')
    {
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, WEA, MINI, JEWEL);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 6, 9, 14, 17);
    }
        elseif($player -> deity == 'Teathe-di')
    {
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SHO, DOD);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 10, 11);
    }
        else
    {
        error(NO_DEITY);
    }
    $i = 0;
    foreach ($arrPraysVal as $intPraysVal)
    {
        $arrPraysCost2[$i] = $arrPraysCost[$intPraysVal];
        $i ++;
    }
    $smarty -> assign(array("Apray" => A_PRAY,
                            "Ayes" => YES,
                            "Ano" => NO,
                            "Prays" => $arrPrays,
                            "Praysval" => $arrPraysVal,
                            "Praycost" => $arrPraysCost2,
                            "Energypts" => ENERGY_PTS2,
                            "Pray1" => PRAY1,
                            "Pray2" => PRAY2,
                            "Pray3" => PRAY3,
                            "Pray4" => PRAY4,
                            "Blessfor" => BLESS_FOR,
                            "Pwpts" => PW_PTS));
    if ($player -> hp == 0) 
    {
        error (YOU_DEAD);
    }
    if (isset($_GET['modl'])) 
    {
        if (!ereg("^[1-6]*$", $_POST['praytype']) || !ereg("^[0-9]*$", $_POST['pray']))
        {
            error(ERROR.$_POST['pray']);
        }
        if ($player -> energy < $_POST['praytype'])
        {
            error(NO_ENERGY);
        }
        $intNumber = $_POST['pray'];
        $intPrayCost = $arrPraysCost[$intNumber];
        if ($player -> pw < $intPrayCost) 
        {
            error (NO_PW);
        }
        if ($player -> deity == 'Heluvald' || $player -> deity == 'Karserth')
        {
            $player -> deity = $player -> deity."a";
        }
        $intRoll = rand(1,10);
        if ($intRoll < 9) 
        {
            $arrBless = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition', 'smith', 'alchemy', 'fletcher', 'weapon', 'shoot', 'dodge', 'cast', 'breeding', 'mining', 'lumberjack', 'herbalist', 'jeweller');
            $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, ALC, FLE, WEA, SHO, DOD, CAS, BRE, MINI, LUMBER, HERBS, JEWEL);
            $strBless = $arrBless[$intNumber];
            $intBonus = $_POST['praytype'] * $player -> level;
            if ($intNumber > 5)
            {
                $intBonus = $intBonus / 10;
            }
            $db -> Execute("UPDATE `players` SET `bless`='".$strBless."', `blessval`=".$intBonus." WHERE `id`=".$player -> id);
            $smarty -> assign("Message", YOU_PRAY.$player -> deity.P_SUCCESS.$arrPrays[$intNumber]);
        }
        if ($intRoll == 9) 
        {
            $smarty -> assign ("Message", YOU_PRAY.$player -> deity.BUT_FAIL);
        }
        if ($intRoll == 10)
        {
            $smarty -> assign("Message", YOU_PRAY.$player -> deity.P_DEAD.$player -> deity.P_DEAD2);
            $db -> Execute("UPDATE `players` SET `hp`=0 WHERE `id`=".$player -> id);
        }
        $db -> Execute("UPDATE `players` SET `pw`=`pw`-".$intPrayCost.", `energy`=`energy`-".$_POST['praytype']." WHERE `id`=".$player -> id);
    }
}

/**
* Temple book
*/
if (isset($_GET['temp']) && $_GET['temp'] == 'book') 
{
    if (!isset($_GET['book']))
    {
        $smarty -> assign(array("Bookinfo" => BOOK_INFO,
                                "Booktext1" => BOOK_TEXT1,
                                "Nextpage" => NEXT_PAGE,
                                "Book" => ''));
    }
        elseif ($_GET['book'] == 1)
    {
        $smarty -> assign(array("Booktext2" => BOOKTEXT2,
                                "Book" => 1,
                                "Book2" => 2,
                                "Nextpage" => NEXT_PAGE));
    }
        elseif ($_GET['book'] == 2)
    {
        $smarty -> assign(array("Booktext2" => BOOKTEXT3,
                                "Book" => 2,
                                "Book2" => "",
                                "Nextpage" => NEXT_PAGE));
    }
}

/**
* Game pantheon
*/
if (isset($_GET['temp']) && $_GET['temp'] == 'pantheon') 
{
    $arrNames = array(GOD1, GOD2, GOD3, GOD4, GOD5, GOD6, GOD7, GOD8);
    $arrDesc = array(GOD1_INFO, GOD2_INFO, GOD3_INFO, GOD4_INFO, GOD5_INFO, GOD6_INFO, GOD7_INFO, GOD8_INFO);
    $smarty -> assign(array("Godnames" => $arrNames,
                            "Goddesc" => $arrDesc));
}

/**
* Initialization of variable
*/
if (!isset($_GET['temp'])) 
{
    $_GET['temp'] = '';
    $smarty -> assign(array("Awork" => A_WORK,
                            "Apray" => A_PRAY,
                            "Abook" => A_BOOK,
                            "Apantheon" => A_PANTHEON));
}

if ($player -> location == 'Ardulith')
{
    $strTempleinfo = '';
}
    else
{
    $strTempleinfo = TEMPLE_INFO2;
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Temple" => $_GET['temp'], 
                        "God" => $player -> deity, 
                        "Templeinfo" => TEMPLE_INFO,
                        "Templeinfo2" => $strTempleinfo,
                        "Location" => $player -> location));
$smarty -> display('temple.tpl');

require_once("includes/foot.php"); 
?>
