<?php
/**
 *   File functions:
 *   Hospital - heal and resurrect players
 *
 *   @name                 : hospital.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 13.12.2012
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

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error ('Nie znajdujesz się w mieście.');
}

$mytribe = $db -> Execute("SELECT `hospass` FROM `tribes` WHERE `id`=".$player -> tribe);
if (!isset ($_GET['action'])) 
{
    if ($player -> hp == $player -> max_hp) 
    {
        error("Nie potrzebujesz leczenia (<a href=\"city.php\">Wróć do miasta</a>)");
    }
    if ($player -> tribe > 0) 
    {
        if ($mytribe -> fields['hospass'] == "Y" && $player -> hp > 0) 
	  {
	    $crneed = ($player -> max_hp - $player -> hp);
	    error("Czy możesz mnie  <a href=hospital.php?action=heal>uzdrowić</a>?<br />"."Jasne, twój klan ma zniżkę na leczenie. Będzie cię to kosztowało <b>".$crneed."</b> sztuk złota.");
	  }
    }
    if ($player -> hp > 0) 
    {
        $crneed = ($player -> max_hp - $player -> hp) * 2;
        if ($crneed < 0)
        {
            $crneed = 0;
        }
        if ($crneed > $player -> credits) 
        {
            error("Nie możesz być wyleczony. Potrzebujesz <b>".$crneed."</b> sztuk złota. (<a href=\"city.php\">Wróć</a>)");
        }
        $smarty -> assign ("Need",$crneed);
    }
    if ($player -> hp <= 0) 
    {
        $crneed = (50 * $player -> stats['condition'][2]);
        if ($crneed > $player -> credits) 
        {
            error("Nie możesz zostać wskrzeszony. Potrzebujesz <b>".$crneed."</b> sztuk złota. (<a href=\"city.php\">Wróć</a>)");
        }
        $smarty -> assign ("Need",$crneed);
    }
    $_GET['action'] = '';
    $smarty -> assign(array("Ayes" => 'Tak',
                            "Couldyou" => "Czy możesz mnie ",
                            "Couldyou2" => "Czy chcesz aby twa dusza wróciła do ciała?",
                            "Itcost" => "Jasne, będzie cię to kosztowało",
                            "Itcost2" => "Będzie kosztowało to",
                            "Goldcoins" => "sztuk złota.",
                            "Aheal" => "uzdrowić"));
}
else
  {
    if ($_GET['action'] == 'heal') 
      {
	if ($player -> hp <= 0) 
	  {
	    error("Musisz być wskrzeszony nie uleczony");
	  }
	if ($mytribe -> fields['hospass'] == "Y") 
	  {
	    $crneed = $player -> max_hp - $player -> hp;
	  }
	else
	  {
	    $crneed = ($player -> max_hp - $player -> hp) * 2;
	  }
	if ($crneed < 0)
	  {
	    $crneed = 0;
	  }
	if ($crneed > $player -> credits) 
	  {
	    error ("Nie możesz być wyleczony. Potrzebujesz <b>".$crneed."</b> sztuk złota. (<a href=\"city.php\">Wróć</a>)");
	  }
	$db -> Execute("UPDATE `players` SET `hp`=`max_hp`, `credits`=`credits`-".$crneed." WHERE `id`=".$player -> id);
	error("<br />Jesteś kompletnie wyleczony. (<a href=\"city.php\">Wróć</a>)");
      }
    elseif ($_GET['action'] == 'ressurect') 
      {
	require_once('includes/resurect.php');
	if ($lostexp > 0)
	  {
	    $strLost = ', ale straciłeś '.$lostexp.' Punktów Doświadczenia do '.$strLoststat;
	  }
	else
	  {
	    $strLost = '';
	  }
	error("<br>Zostałeś wskrzeszony".$strLost.". (<a href=\"city.php\">Wróć</a>)");
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
