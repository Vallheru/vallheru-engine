<?php
/**
 *   File functions:
 *   Judge Panel - change rank lawyers and members
 *
 *   @name                 : sedzia.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 25.05.2012
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

$title = "Panel Sędziego";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/sedzia.php");

if ($player -> rank != 'Sędzia') 
{
  error (YOU_NOT);
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Adda" => ADD_A,
    "Asa" => AS_A,
    "Rmember" => R_MEMBER,
    "Rlawyer" => R_LAWYER,
    "Rjudge" => R_JUDGE,
    "Aadd" => A_ADD));
$smarty -> display ('sedzia.tpl');

/**
 * Add selected player selected rank
 */
if (isset ($_GET['step']) && $_GET['step'] == 'add') 
  {
    checkvalue($_POST['aid']);
    $ranga = $db -> Execute("SELECT rank FROM players WHERE id=".$_POST['aid']);
    if ($ranga -> fields['rank'] == 'Member' || $ranga -> fields['rank'] == 'Ławnik' || $ranga -> fields['rank'] == 'Sędzia' || $ranga -> fields['rank'] == 'Prawnik') 
      {
	$arrRank = array('Member', 'Ławnik', 'Prawnik');
        $strRank = $db -> qstr($_POST['rank'], get_magic_quotes_gpc());
        if (!in_array($_POST['rank'], $arrRank)) 
	  {
	    error(ERROR);
	  }
        $db -> Execute("UPDATE players SET rank=".$strRank." WHERE id=".$_POST['aid']);
        error (YOU_ADD.$_POST['aid'].AS_A.$_POST['rank']);
      }
    $ranga -> Close();
  }

require_once("includes/foot.php");
?>
