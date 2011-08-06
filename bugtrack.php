<?php
/**
 *   File functions:
 *   Bugtrack - automated errors and warnings
 *
 *   @name                 : bugtrack.php                            
 *   @copyright            : (C) 2004-2005 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 0.8 beta
 *   @since                : 06.04.2005
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

/**
* Site name
*/
$title = "Panel Administracyjny";

/**
* Add left and head side of site
*/
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/bugtrack.php");

/**
* Check permission
*/
if($player -> rank != 'Admin') 
{
	error (NO_PERM);
}

/**
* Get data from database and assign to variables
*/
$bugs = $db -> Execute("SELECT * FROM bugtrack ORDER BY amount desc");
$arrid = array();
$arrtype = array();
$arrinfo = array();
$arramount = array();
$arrfile = array();
$arrline = array();
$arrrefer = array();
$i = 0;
while (!$bugs -> EOF) 
{
    $arrid[$i] = $bugs -> fields['id'];
    $arrtype[$i] = $bugs -> fields['type'];
    $arrinfo[$i] = $bugs -> fields['info'];
    $arramount[$i] = $bugs -> fields['amount'];
    $arrfile[$i] = wordwrap($bugs -> fields['file'],20,"\n",1);
    $arrline[$i] = $bugs -> fields['line'];
    $arrrefer[$i] = wordwrap($bugs -> fields['referer'],20,"\n",1);
    $i = $i + 1;
    $bugs -> MoveNext();
}
$bugs -> Close();

/**
* Delete bugs from bugtrack
*/
if (isset($_GET['action']) && $_GET['action'] == 'delete') 
{
    foreach ($arrid as $bid) 
    {
        if (isset($_POST[$bid])) 
        {
            $db -> Execute("DELETE FROM bugtrack WHERE id=".$bid);
        }
    }
    $smarty -> assign("Message", DELETED);
} 
    else 
{
    $smarty -> assign("Message", "");
}

/**
* Assign variables and display page
*/
$smarty -> assign(array("Bid" => $arrid,
    "Btype" => $arrtype,
    "Binfo" => $arrinfo,
    "Bamount" => $arramount,
    "Bfile" => $arrfile,
    "Brefer" => $arrrefer,
    "Bline" => $arrline,
    "Buginfo" => BUG_INFO,
    "Bid2" => B_ID,
    "Btype2" => B_TYPE,
    "Bamount2" => B_AMOUNT,
    "Bfile2" => B_FILE,
    "Bref" => B_REF,
    "Bline2" => B_LINE,
	"Adelete" => A_DELETE,
    "Binfo2" => B_INFO));
$smarty -> display('bugtrack.tpl');

/**
* Add right and bottom side of page
*/
require_once("includes/foot.php");
?>
