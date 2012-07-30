<?php
/**
 *   File functions:
 *   Polish language for tribe warehouse
 *
 *   @name                 : tribeware.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 30.07.2012
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

define("WARE_INFO", "Witaj w magazynie klanu. Tutaj są składowane mikstury należące do klanu. Każdy członek klanu może ofiarować klanowi jakąś miksturę, ale tylko przywódca lub osoba upoważniona przez niego może darować daną miksturę członkom swojego klanu. Co chcesz zrobić?");
define("A_ADD", "Dać miksturę do klanu");
define("A_SHOW", "Zobaczyć listę mikstur w magazynie klanu");

if (isset($_GET['step']) && $_GET['step'] == 'zobacz') 
{
    define("NO_ITEMS", "Nie ma mikstur w magazynie klanu!");
    define("IN_WARE", "W magazynie klanu jest");
    define("POTIONS", "mikstur.");
    define("A_GIVE", "Daj");
    define("T_NAME", "Nazwa");
    define("T_EFECT", "Efekt");
    define("T_AMOUNT2", "Ilość");
    define("T_OPTIONS", "Opcje");
}

if (isset ($_GET['daj'])) 
{
    define("NO_ITEMS", "Klan nie ma tylu mikstur tego typu!");
    define("NOT_IN_CLAN", "Ten gracz nie jest w twoim klanie!");
    define("YOU_GIVE1", "Przekazałeś graczowi ");
    define("YOU_GIVE2", ", ID ");
    define("YOU_GET", "Dostałeś od klanu ");
    define("A_GIVE", "Daj");
    define("PLAYER_ID", "graczowi");
}

if (isset ($_GET['step']) && $_GET['step'] == 'daj') 
{
    define("NO_AMOUNT", "Nie masz tyle mikstur tego typu!");
    define("YOU_ADD", "Dodałeś ");
    define("TO_WARE", "</b> do magazynu klanu.");
    define("L_PLAYER", "Gracz <b>");
    define("L_ID", "</a></b>, ID <b>");
    define("ADD_TO", "</b> dodał do magazynu klanu ");
    define("ADD_ITEM", "Dodaj miksturę do magazynu");
    define("POTION", "Mikstura");
    define("AMOUNT2", "ilość");
    define("T_AMOUNT2", "sztuk(i)");
}
?>
