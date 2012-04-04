<?php
/**
 *   File functions:
 *   Polish language for tribe armor
 *
 *   @name                 : tribearmor.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 04.04.2012
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

define("ERROR", "Zapomnij o tym!");
define("NO_CLAN", "Nie jesteś w klanie.");
define("T_AMOUNT", " sztuk(i) ");
define("A_MAIN", "Główna");
define("A_DONATE", "Dotuj");
define("A_MEMBERS", "Członkowie");
define("A_ARMOR", "Zbrojownia");
define("A_POTIONS", "Magazyn");
define("A_MINERALS", "Skarbiec");
define("A_HERBS", "Zielnik");
define("A_LEFT", "Opuść klan");
define("A_LEADER", "Opcje przywódcy");
define("A_FORUMS", "Forum klanu");
define("A_ASTRAL", "Astralny skarbiec");

define("ARMOR_INFO", "Witaj w zbrojowni klanu. Tutaj są składowane przedmioty należące do klanu. Każdy członek klanu może ofiarować klanowi jakiś przedmiot, ale tylko przywódca lub osoba upoważniona przez niego może darować dany przedmiot członkom swojego klanu. Co chcesz zrobić?");
define("A_SHOW_W", "Zobaczyć listę broni w zbrojowni klanu");
define("A_SHOW_A", "Zobaczyć listę zbrój w zbrojowni klanu");
define("A_SHOW_H", "Zobaczyć listę hełmów w zbrojowni klanu");
define("A_SHOW_D", "Zobaczyć listę tarcz w zbrojowni klanu");
define("A_SHOW_L", "Zobaczyć listę nagolenników w zbrojowni klanu");
define("A_SHOW_B", "Zobaczyć listę łuków w zbrojowni klanu");
define("A_SHOW_R", "Zobaczyć listę kołczanów strzał w zbrojowni klanu");
define("A_SHOW_S", "Zobaczyć listę różdżek w zbrojowni klanu");
define("A_SHOW_C", "Zobaczyć listę szat w zbrojowni klanu");
define("A_SHOW_I", "Zobaczyć listę pierścieni w zbrojowni klanu");
define("A_ADD", "Dać przedmiot do klanu");

if (isset($_GET['step']) && $_GET['step'] == 'zobacz') 
{
    define("WHAT_YOU", "Czego dokładnie szukasz?");
    define("T_WEAPONS", "broni");
    define("T_ARMORS", "zbrój");
    define("T_HELMETS", "hełmów");
    define("T_SHIELDS", "tarcz");
    define("T_LEGS", "nagolenników");
    define("T_BOWS", "łuków");
    define("T_ARROWS", "kołczanów strzał");
    define("T_STAFFS", "różdżek");
    define("T_CAPES", "szat");
    define("T_RINGS", "pierścieni");
    define("NO_ITEMS", "Nie ma ");
    define("IN_ARMOR", " w zbrojowni klanu!");
    define("IN_ARMOR2", "W zbrojowni klanu jest");
    define("A_GIVE", "Daj");
    define("T_NAME", "Nazwa");
    define("T_POWER", "Siła");
    define("T_AGI", "Premia do zręczności");
    define("T_SPEED", "Premia do szybkości");
    define("T_DUR", "Wytrzymałość");
    define("T_OPTIONS", "Opcje");
    define("T_LEVEL", "Poziom");
    define("T_OR", "Lub");
    define("T_SEEK" ,"szukaj");
    define("T_ITEMS", "przedmiotów z poziomów od");
    define("T_TO", "do");
}

if (isset ($_GET['daj'])) 
{
    define("NO_ITEMS", "Klan nie ma tylu przedmiotów!");
    define("NOT_IN_CLAN", "Ten gracz nie jest w twoim klanie!");
    define("YOU_GIVE1", "Przekazałeś graczowi ");
    define("YOU_GIVE2", ", ID ");
    define("YOU_GET", "Dostałeś od klanu ");
    define("A_GIVE", "Daj");
    define("PLAYER_ID", "graczowi ID");
}

if (isset ($_GET['step']) && $_GET['step'] == 'daj') 
{
    define("SELECT_ITEM", "Wybierz przedmiot!");
    define("NO_AMOUNT", "Nie masz tyle przedmiotów tego typu!");
    define("YOU_ADD", "Dodałeś <b>");
    define("TO_ARMOR", "</b> do zbrojowni klanu.");
    define("L_PLAYER", "Gracz <b>");
    define("L_ID", "</a></b>, ID: <b>");
    define("ADD_TO", "</b> dodał do zbrojowni klanu ");
    define("ADD_ITEM", "Dodaj przedmiot do zbrojowni");
    define("ITEM", "Przedmiot");
    define("AMOUNT2", "ilość");
    define("A_ADD2", "Dodaj");
}
?>
