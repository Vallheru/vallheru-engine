<?php
/**
 *   File functions:
 *   Magic tower - buy spells, staffs and capes
 *
 *   @name                 : wieza.php                            
 *   @copyright            : (C) 2004,2005,2006,2007 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 21.02.2007
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
// $Id: wieza.php 890 2007-02-21 19:37:53Z thindil $

$title = "Magiczna wieża";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/wieza.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

if (!isset($_GET['buy'])) 
{
    $smarty -> assign(array("Towerinfo" => TOWER_INFO,
        "Abuys" => A_BUY_S,
        "Abuyc" => A_BUY_C,
        "Abuyst" => A_BUY_ST,
        "Tname" => T_NAME,
        "Tpower" => T_POWER,
        "Tcost" => T_COST,
        "Tlevel" => T_LEVEL,
        "Toptions" => T_OPTIONS,
        "Abuy" => A_BUY));
    if (isset ($_GET['dalej'])) 
    {
        if ($_GET['dalej'] != 'T' && $_GET['dalej'] != 'C' && $_GET['dalej'] != 'P') 
        {
            error (ERROR);
        }
        if ($_GET['dalej'] == 'P') 
        {
            $czary = $db -> Execute("SELECT * FROM czary WHERE gracz=0 AND status='S' ORDER BY poziom ASC");
            $arrname = array();
            $arrefect = array();
            $arrcost = array();
            $arrlevel = array();
            $arrid = array();
            $i = 0;
            while (!$czary -> EOF) 
            {
                $arrname[$i] = $czary -> fields['nazwa'];
                if ($czary -> fields['typ'] == 'B') 
                {
                    $arrefect[$i] = "<td>+".$czary -> fields['obr'].S_POWER;
                }
                if ($czary -> fields['typ'] == 'O') 
                {
                    $arrefect[$i] = "<td>+".$czary -> fields['obr'].S_POWER2;
                }
                if ($czary -> fields['nazwa'] == 'Ulepszenie przedmiotu') 
                {
                    $arrefect[$i] = S_POWER3;
                }
                if ($czary -> fields['nazwa'] == 'Utwardzenie przedmiotu') 
                {
                    $arrefect[$i] = S_POWER4;
                }
                if ($czary -> fields['nazwa'] == 'Umagicznienie przedmiotu') 
                {
                    $arrefect[$i] = S_POWER5;
                }
                $arrcost[$i] = $czary -> fields['cena'];
                $arrlevel[$i] = $czary -> fields['poziom'];
                $arrid[$i] = $czary -> fields['id'];
                $czary -> MoveNext();
                $i = $i + 1;
            }
            $czary -> Close();
            $smarty -> assign(array("Name" => $arrname, 
                "Efect" => $arrefect, 
                "Cost" => $arrcost, 
                "Itemlevel" => $arrlevel, 
                "Itemid" => $arrid));
        } 
            else 
        {
            $items = $db -> Execute("SELECT * FROM `mage_items` WHERE `type`='".$_GET['dalej']."' ORDER BY `cost` ASC");
            $arrname = array();
            $arrpower = array();
            $arrcost = array();
            $arrlevel = array();
            $arrid = array();
            $i = 0;
            while (!$items -> EOF) 
            {
                if ($items -> fields['type'] == 'T') 
                {
                    $arrpower[$i] = ST_POWER;
                } 
                    else 
                {
                    $arrpower[$i] = "+".$items -> fields['power'].C_POWER;
                }
                $arrname[$i] = $items -> fields['name'];
                $arrcost[$i] = $items -> fields['cost'];
                $arrlevel[$i] = $items -> fields['minlev'];
                $arrid[$i] = $items -> fields['id'];
                $items -> MoveNext();
                $i = $i + 1;
            }
            $items -> Close();
            $smarty -> assign(array("Name" => $arrname, 
                                    "Power" => $arrpower, 
                                    "Cost" => $arrcost, 
                                    "Itemlevel" => $arrlevel, 
                                    "Itemid" => $arrid));
        }
    }
}

if (isset($_GET['buy'])) 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['buy'])) 
    {
        error (ERROR);
    }
    if ($_GET['type'] == 'S') 
    {
        $czary = $db -> Execute("SELECT * FROM czary WHERE id=".$_GET['buy']);
        $test = $db -> Execute("SELECT id FROM czary WHERE nazwa='".$czary -> fields['nazwa']."' AND gracz=".$player -> id);
        if ($test -> fields['id']) 
        {
            error (YOU_HAVE);
        }
        $test -> Close();
        if (!$czary -> fields['id']) 
        {
            error (NO_SPELL);
        }
        if ($czary -> fields['poziom'] > $player -> level) 
        {
            error (TOO_LOW);
        }
        if ($czary -> fields['cena'] > $player -> credits) 
        {
            error (NO_MONEY);
        }
        if ($player -> clas != 'Mag' && ($czary -> fields['typ'] == 'B' || $czary -> fields['typ'] == 'O')) 
        {
            error (ONLY_MAGE);
        }
        $db -> Execute("INSERT INTO czary (gracz, nazwa, cena, poziom, typ, obr, status) VALUES(".$player -> id.",'".$czary -> fields['nazwa']."',".$czary -> fields['cena'].",".$czary -> fields['poziom'].",'".$czary -> fields['typ']."',".$czary -> fields['obr'].",'U')");
        $smarty -> assign ("Message", YOU_PAY.$czary -> fields['cena'].AND_BUY.$czary -> fields['nazwa']."</b>.");
        $db -> Execute("UPDATE players SET credits=credits-".$czary -> fields['cena']." WHERE id=".$player -> id);
        $czary -> Close();
    } 
        elseif ($_GET['type'] == 'I') 
    {
        $items = $db -> Execute("SELECT * FROM mage_items WHERE id=".$_GET['buy']);
        if (!$items -> fields['id']) 
        {
            error (NO_ITEM);
        }
        if ($items -> fields['minlev'] > $player -> level) 
        {
            error (TOO_LOW);
        }
        if ($items -> fields['cost'] > $player -> credits) 
        {
            error (NO_MONEY);
        }
        if ($player -> clas != 'Mag') 
        {
            error (ONLY_MAGE2);
        }
        $newcost = ceil($items -> fields['cost'] * 0.75);
        $db -> Execute("INSERT INTO equipment (owner, name, cost, minlev, type, power, status) VALUES(".$player -> id.",'".$items -> fields['name']."',".$newcost.",".$items -> fields['minlev'].",'".$items -> fields['type']."',".$items -> fields['power'].",'U')");
        $smarty -> assign ("Message", YOU_PAY.$items -> fields['cost'].AND_BUY2.$items -> fields['name']."</b>.");
        $db -> Execute("UPDATE players SET credits=credits-".$items -> fields['cost']." WHERE id=".$player -> id);
        $items -> Close();
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['buy'])) 
{
    $_GET['buy'] = '';
}
if (!isset($_GET['dalej'])) 
{
    $_GET['dalej'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Buy" => $_GET['buy'], 
    "Next" => $_GET['dalej']));
$smarty -> display('wieza.tpl');

require_once("includes/foot.php"); 
?>
