<?php
/**
 *   File functions:
 *   History of world and help to game
 *
 *   @name                 : help.php                            
 *   @copyright            : (C) 2004-2005 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 0.8 beta
 *   @since                : 15.03.2005
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
// 

$title = "Pomoc";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/help.php");

if (isset($_GET['help']) && $_GET['help'] == 'history')
{
	$smarty -> assign("Helptext", HELP1);
}

if (isset($_GET['help']) && $_GET['help'] == 'overview')
{
	$smarty -> assign("Helptext", HELP2);
}

if (isset($_GET['help']) && $_GET['help'] == 'eventlog')
{
	$smarty -> assign("Helptext", HELP3);
}

if (isset($_GET['help']) && $_GET['help'] == 'equipment')
{
	$smarty -> assign("Helptext", HELP4);
}

if (isset($_GET['help']) && $_GET['help'] == 'battle')
{
	$smarty -> assign("Helptext", HELP5);
}

if (isset($_GET['help']) && $_GET['help'] == 'money')
{
	$smarty -> assign("Helptext", HELP6);
}

if (isset($_GET['help']) && $_GET['help'] == 'energy')
{
	$smarty -> assign("Helptext", HELP7);
}

if (isset($_GET['help']) && $_GET['help'] == 'faq')
{
	$smarty -> assign(array("Helptext" => HELP8,
	    "Amail" => HELP8_1,
		"Helptext2" => HELP8_2));
}

if (isset($_GET['help']) && $_GET['help'] == 'indocron')
{
	$smarty -> assign("Helptext", '');
	if (!isset($_GET['step']))
	{
		$smarty -> assign(array("Helpinfo2" => HELP_INFO2,
		    "Abattle" => A_BATTLE,
			"Awest" => A_WEST,
			"Ajob" => A_JOB,
			"Asoc" => A_SOC,
			"Ahome" => A_HOME,
			"Asouth" => A_SOUTH,
			"Avillage" => A_VILLAGE,
			"Acastle" => A_CASTLE,
			"Info" => INFO));
    }
	if (isset($_GET['step']) && $_GET['step'] == 'mieszkalna')
	{
		$smarty -> assign(array("Helpinfo" => HELP_INFO3,
		    "Alist" => A_LIST,
			"Amonuments" => A_MONUMENTS));
	}
	if (isset($_GET['step']) && $_GET['step'] == 'podgrodzie')
	{
		$smarty -> assign(array("Helpinfo" => HELP_INFO4,
		    "Aschool" => A_SCHOOL,
			"Amine" => A_MINE,
			"Acore" => A_CORE));
	}
	if (isset($_GET['step']) && $_GET['step'] == 'poludniowa')
	{
		$smarty -> assign(array("Helpinfo" => HELP_INFO5,
		    "Amarket" => A_MARKET,
			"Atravel" => A_TRAVEL));
	}
	if (isset($_GET['step']) && $_GET['step'] == 'praca')
	{
		$smarty -> assign(array("Helpinfo" => HELP_INFO6,
		    "Aclear" => A_CLEAR,
			"Asmith" => A_SMITH,
			"Aalchemy" => A_ALCHEMY,
			"Alumbermill" => A_LUMBERMILL));
	}
	if (isset($_GET['step']) && $_GET['step'] == 'spol')
	{
		$smarty -> assign(array("Helpinfo" => HELP_INFO7,
		    "Anews" => A_NEWS,
			"Ainn" => A_INN,
			"Aforums" => A_FORUMS2,
			"Amail2" => A_MAIL2,
			"Aclans" => A_CLANS));
	}
	if (isset($_GET['step']) && $_GET['step'] == 'wojenne')
	{
		$smarty -> assign(array("Helpinfo" => HELP_INFO8,
		    "Aarena2" => A_ARENA2,
			"Aweapon" => A_WEAPON,
			"Aarmor" => A_ARMOR,
			"Afletcher" => A_FLETCHER,
			"Aoutpost" => A_OUTPOST));
	}
	if (isset($_GET['step']) && $_GET['step'] == 'zachodni')
	{
		$smarty -> assign(array("Helpinfo" => HELP_INFO9,
		    "Alabyrynth" => A_LABYRYNTH,
			"Atower" => A_TOWER,
			"Amith" => A_MITH,
			"Atemple" => A_TEMPLE,
			"Aalchemy" => A_ALCHEMY));
	}
	if (isset($_GET['step']) && $_GET['step'] == 'zamek')
	{
		$smarty -> assign(array("Helpinfo" => HELP_INFO10,
		    "Anews" => A_NEWS,
			"Atimer" => A_TIMER,
			"Areff" => A_REFF));
	}
}

/**
* Initialization of variables
*/
if (!isset($_GET['help'])) 
{
    $_GET['help'] = '';
	$smarty -> assign(array("Ahistory" => A_HISTORY,
	    "Aoverview" => A_OVERVIEW,
		"Aequipment" => A_EQUIPMENT,
		"Aeventlog" => A_EVENTLOG,
		"Aindocron" => A_INDOCRON,
		"Abattle2" => A_BATTLE2,
		"Amoney" => A_MONEY,
		"Aenergy" => A_ENERGY,
		"Afaq" => A_FAQ,
	    "Helpinfo" => HELP_INFO));
}
    else
{
	$smarty -> assign("Aback", A_BACK);
}

if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (!isset($_GET['step2'])) 
{
    $_GET['step2'] = '';
}
    else
{
	$smarty -> assign("Helptext", HELP_TEXT);
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Help" => $_GET['help'], 
    "Step" => $_GET['step'], 
	"Step2" => $_GET['step2'], 
	"Mail" => $adminmail,
    "Hauthor" => H_AUTHOR));
$smarty -> display ('help.tpl');

require_once("includes/foot.php");
?>
