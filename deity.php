<?php
/**
 *   File functions:
 *   Change deity of player
 *
 *   @name                 : deity.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 23.05.2012
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

$title = "Wybierz wyznanie";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/deity.php");

if (isset ($_GET['deity']) && $player -> deity == '') 
{
    $smarty -> assign(array("Aselect" => A_SELECT,
                            "Aback" => A_BACK));
}

if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> deity == '') 
{
    $smarty -> assign(array("Youselect" => YOU_SELECT,
                            "Click" => CLICK,
                            "Here" => HERE,
                            "Forback" => FOR_BACK));
}

$arrOptionname = array('illuminati', 'karserth', 'anariel', 'heluvald', 'tartus', 'oregarl', 'daeraell', 'teathedi');
$arrDeityname = array(GOD1, GOD2, GOD3, GOD4, GOD5, GOD6, GOD7, GOD8);
$arrDeityname2 = array('Illuminati', 'Karsertha', 'Anariel', 'Heluvalda', 'Tartusa', 'Oregarla', 'Daeraell', 'Teathe-di');

/**
 * Show info about deity
 */
if (isset($_GET['deity']) && in_array($_GET['deity'], $arrOptionname) && $player -> deity == '')
{
    $arrDeityinfo = array(GOD1_INFO, GOD2_INFO, GOD3_INFO, GOD4_INFO, GOD5_INFO, GOD6_INFO, GOD7_INFO, GOD8_INFO);
    $intKey = array_search($_GET['deity'], $arrOptionname);
    $smarty -> assign("Godinfo", $arrDeityinfo[$intKey]);
    /**
     * Select deity
     */
    if (isset ($_GET['step']) && $_GET['step'] == 'wybierz' && $player -> deity == '') 
    {
        $db -> Execute("UPDATE `players` SET `deity`='".$arrDeityname[$intKey]."' WHERE `id`=".$player -> id);
        $smarty -> assign ("God", $arrDeityname2[$intKey]);
    }
}
    elseif (isset($_GET['deity']) && in_array($_GET['deity'], $arrOptionname) && $player -> deity)
{
	error(YOU_HAVE." (<a href=\"stats.php\">".A_BACK."</a>)");
}


/**
 * Change deity
 */
if (isset($_GET['step']) && $_GET['step'] == 'change')
{
    if ($player -> deity == '')
    {
        error(ERROR);
    }
    $objPoints = $db -> Execute("SELECT `changedeity` FROM `players` WHERE `id`=".$player -> id);
    $intCost = 100 * pow(2, $objPoints -> fields['changedeity']);
    $objPoints -> Close();
    if (!isset($_GET['step2']))
    {
        $smarty -> assign(array("Change" => CHANGE,
                                "Change2" => CHANGE2,
                                "Ccost" => $intCost,
                                "Ayes" => YES,
                                "Ano" => NO,
                                "Step2" => ''));
    }
        else
    {
        $intKey = array_search($player -> deity, $arrDeityname);
        $db -> Execute("UPDATE `players` SET `deity`='', `pw`=`pw`-".$intCost.", `changedeity`=`changedeity`+1 WHERE `id`=".$player -> id);
        $smarty -> assign(array("Step2" => $_GET['step2'],
                                "Youchange" => YOU_CHANGE,
                                "Youmay" => YOU_MAY,
                                "Pdeity" => $arrDeityname2[$intKey],
                                "Aselect2" => A_SELECT2,
                                "Tdeity" => T_DEITY));
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['deity'])) 
{
    $_GET['deity'] = '';
    if (!isset($_GET['step']))
    {
	    if ($player -> deity)
		{
		    error(YOU_HAVE." (<a href=\"stats.php\">".A_BACK."</a>)");
		}
        $smarty -> assign(array("Deityinfo" => DEITY_INFO,
                                "Godname" => $arrDeityname,
                                "Godoption" => $arrOptionname,
								"Ateist" => ATEIST));
    }
}
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Deity" => $_GET['deity'], 
                        "Step" => $_GET['step'], 
                        "Pldeity" => $player -> deity));
$smarty -> display ('deity.tpl');

require_once("includes/foot.php"); 
?>
