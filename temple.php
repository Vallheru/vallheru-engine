<?php
/**
 *   File functions:
 *   Temple
 *
 *   @name                 : temple.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 19.12.2012
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
require_once("languages/".$lang."/temple.php");

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
        if (!isset($_POST['rep'])) 
        {
            error (ERROR);
        }
	checkvalue($_POST['rep']);
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
    switch ($player->race)
      {
      case "Człowiek":
	$arrPraysCost = array('10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10');
	break;
      case 'Elf':
	$arrPraysCost = array('7', '15', '10', '10', '7', '15', '15', '7', '7', '15', '7', '10', '7', '7', '15', '7', '7', '10', '7', '10', '15');
	break;
      case 'Krasnolud':
        $arrPraysCost = array('15', '7', '10', '10', '15', '7', '7', '15', '10', '7', '15', '10', '15', '15', '7', '15', '15', '7', '10', '15', '7');
	break;
      case 'Hobbit':
        $arrPraysCost = array('7', '15', '10', '10', '7', '10', '10', '15', '10', '10', '10', '10', '7', '7', '10', '10', '7', '10', '7', '7', '10');
	break;
      case 'Jaszczuroczłek':
        $arrPraysCost = array('7', '7', '15', '15', '7', '7', '10', '10', '10', '10', '10', '10', '15', '15', '15', '15', '15', '15', '15', '15', '15');
	break;
      case 'Gnom':
        $arrPraysCost = array('10', '15', '10', '15', '15', '10', '7', '7', '7', '15', '15', '15', '15', '7', '7', '15', '7', '7', '10', '7', '10');
	break;
      default:
        error(NO_RACE);
	break;
    }
    switch ($player->deity)
      {
      case 'Illuminati':
	$arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, ALC, FLE, WEA, SHO, DOD, CAS, BRE, MINI, LUMBER, HERBS, JEWEL, "Spostrzegawczości", "Złodziejstwa", 'Hutnictwa');
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20);
        for ($i = 0; $i < 20; $i ++)
        {
            $arrPraysCost[$i] = $arrPraysCost[$i] * 2;
        }
	break;
      case 'Karserth':
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, WEA, SHO, DOD);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 9, 10, 11);
	break;
      case 'Anariel':
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, ALC, DOD, CAS);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 7, 11, 12);
	break;
      case 'Heluvald':
	$arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, ALC, FLE, BRE, MINI, LUMBER, HERBS, JEWEL, "Spostrzegawczości", 'Hutnictwa');
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 13, 14, 15, 16, 17, 18, 20);
	break;
      case 'Daeraell':
	$arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, ALC, FLE, BRE, MINI, LUMBER, HERBS, JEWEL, "Spostrzegawczości", "Złodziejstwa", 'Hutnictwa');
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 13, 14, 15, 16, 17, 18, 19, 20);
	break;
      case 'Tartus':
	$arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, WEA, CAS);
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 9, 12);
	break;
      case 'Oregarl':
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, WEA, MINI, JEWEL, "Spostrzegawczości", 'Hutnictwa');
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 6, 9, 14, 17, 18, 20);
	break;
      case 'Teathe-di':
        $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SHO, DOD, "Spostrzegawczości", "Złodziejstwa");
        $arrPraysVal = array(0, 1, 2, 3, 4, 5, 10, 11, 18, 19);
	break;
      default:
        error(NO_DEITY);
	break;
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
	$_POST['praytype'] = intval($_POST['praytype']);
	$_POST['pray'] = intval($_POST['pray']);
	if ($_POST['pray'] < 0 || $_POST['praytype'] < 0 || $_POST['praytype'] > 6 || $_POST['pray'] > (count($arrPraysCost) - 1))
        {
            error(ERROR);
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
	    $arrBless = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition', 'smith', 'alchemy', 'carpentry', 'weapon', 'shoot', 'dodge', 'cast', 'breeding', 'mining', 'lumberjack', 'herbalist', 'jeweller', 'perception', 'thievery', 'metallurgy');
            $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, ALC, FLE, WEA, SHO, DOD, CAS, BRE, MINI, LUMBER, HERBS, JEWEL, "Spostrzegawczości", "Złodziejstwa", 'Hutnictwa');
            $strBless = $arrBless[$intNumber];
	    if (array_key_exists($strBless, $player->stats))
	      {
		$intLevel = $player->stats[$strBless][2];
	      }
	    else
	      {
		$intLevel = $player->skills[$strBless][1];
	      }
            $intBonus = $_POST['praytype'] + $intLevel;
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
