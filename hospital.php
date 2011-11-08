<?php
/**
 *   File functions:
 *   Hospital - heal and resurrect players
 *
 *   @name                 : hospital.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 08.11.2011
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

$title = "Szpital";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/hospital.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

$mytribe = $db -> Execute("SELECT `hospass` FROM `tribes` WHERE `id`=".$player -> tribe);
if (!isset ($_GET['action'])) 
{
    if ($player -> hp == $player -> max_hp) 
    {
        error(STOP_WASTE." (<a href=\"city.php\">".BACK."</a>)");
    }
    if ($player -> tribe > 0) 
    {
        if ($mytribe -> fields['hospass'] == "Y" && $player -> hp > 0) 
	  {
	    $crneed = ($player -> max_hp - $player -> hp);
	    error(COULD_YOU." <a href=hospital.php?action=heal>".A_HEAL."</a>?<br />".SURE_IT.$crneed.GOLD_COINS);
	  }
    }
    if ($player -> hp > 0) 
    {
        $crneed = ($player -> max_hp - $player -> hp) * $player->level;
        if ($crneed < 0)
        {
            $crneed = 0;
        }
        if ($crneed > $player -> credits) 
        {
            error(NO_MONEY2.$crneed.GOLD_COINS." (<a href=\"city.php\">".BACK."</a>)");
        }
        $smarty -> assign ("Need",$crneed);
    }
    if ($player -> hp <= 0) 
    {
        $crneed = (50 * $player -> level);
        if ($crneed > $player -> credits) 
        {
            error(NO_MONEY.$crneed.GOLD_COINS." (<a href=\"city.php\">".BACK."</a>)");
        }
        $smarty -> assign ("Need",$crneed);
    }
    $_GET['action'] = '';
    $smarty -> assign(array("Ayes" => YES,
                            "Couldyou" => COULD_YOU,
                            "Couldyou2" => COULD_YOU2,
                            "Itcost" => IT_COST,
                            "Itcost2" => IT_COST2,
                            "Goldcoins" => GOLD_COINS2,
                            "Aheal" => A_HEAL));
}
else
  {
    if ($_GET['action'] == 'heal') 
      {
	if ($player -> hp <= 0) 
	  {
	    error(BAD_ACTION);
	  }
	if ($mytribe -> fields['hospass'] == "Y") 
	  {
	    $crneed = $player -> max_hp - $player -> hp;
	  }
	else
	  {
	    $crneed = ($player -> max_hp - $player -> hp) * $player->level;
	  }
	if ($crneed < 0)
	  {
	    $crneed = 0;
	  }
	if ($crneed > $player -> credits) 
	  {
	    error (NO_MONEY.$crneed.GOLD_COINS." (<a href=\"city.php\">".BACK."</a>)");
	  }
	$db -> Execute("UPDATE `players` SET `hp`=`max_hp`, `credits`=`credits`-".$crneed." WHERE `id`=".$player -> id);
	error(YOU_HEALED." (<a href=\"city.php\">".BACK."</a>)");
      }
    elseif ($_GET['action'] == 'ressurect') 
      {
	require_once('includes/resurect.php');
	error(YOU_RES.$pdpr.LOST_EXP." (<a href=\"city.php\">".BACK."</a>)");
      }
  }
$mytribe -> Close();

/**
* Assign variables to template and display page
*/
$smarty -> assign ("Action", $_GET['action']);
$smarty -> display ('hospital.tpl');

require_once("includes/foot.php");
?>
