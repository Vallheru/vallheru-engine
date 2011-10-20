<?php
/**
 *   File functions:
 *   Polish language for smelter
 *
 *   @name                 : smelter.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 20.10.2011
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
define("A_BACK", "wróć");

if (!isset($_GET['step']))
{
    define("SMELTER_INFO", "Witaj w hucie. Tutaj możesz wytapiać różne minerały.");
    define("NO_SMELTER", "Najpierw musisz nieco ");
    define("A_UPGRADE", "rozbudować");
    define("NO_SMELTER2", " hutę nim zaczniesz wytapiać w niej surowce");
    define("A_SMELT", "Wytapiaj surowce");
    define("A_UPGRADE2", "Ulepsz hutę");
}

if (isset($_GET['step']) && $_GET['step'] == 'upgrade')
{
    define("NO_UPGRADE", "Nie możesz więcej rozbudowywać huty!");
    define("LEVEL1", "Poziom 1 - wytapianie miedzi (1000 sztuk złota)");
    define("LEVEL2", "Poziom 2 - wytapianie miedzi, brązu (5000 sztuk złota)");
    define("LEVEL3", "Poziom 3 - wytapianie miedzi, brązu, mosiądzu (20000 sztuk złota)");
    define("LEVEL4", "Poziom 4 - wytapianie miedzi, brązu, mosiadzu, żelaza (60000 sztuk złota)");
    define("LEVEL5", "Poziom 5 - wytapianie miedzi, brązu, mosiądzu, żelaza, stali (120000 sztuk złota)");
    if (!isset($_GET['upgrade']))
    {
        define("UPGRADE_INFO", "Tutaj możesz ulepszyć swoją hutę. Każdy nowy poziom huty pozwala ci wytapiać kolejne surowce");
        define("A_UPGRADE", "Ulepsz");
    }
    if (isset($_GET['upgrade']) && $_GET['upgrade'] == 'Y')
    {
        define("NO_MONEY", "Nie masz tyle sztuk złota!");
        define("YOU_UPGRADE", "Rozbudowałeś swoją hutę. Obecny poziom huty:<br />");
    }
}

if (isset($_GET['step']) && $_GET['step'] == 'smelt')
{
    define("NO_SMELT", "Nie możesz wytapiać rud surowców ponieważ nie masz odpowiednio rozbudowanej huty!");
    define("SMELT_INFO", "Tutaj możesz wytapiać rudy minerałów w celu zdobycia surowców potrzebnych do wytwarzania przedmiotów. Koszt wytopu zależy od rodzaju metalu, który chcesz wytworzyć.");
    define("SMELT1", "Wytapiaj miedź (2 rudy miedzi + 1 bryła węgla = 1 sztabka miedzi)");
    define("SMELT2", "Wytapiaj brąz (1 ruda miedzi + 1 ruda cyny + 2 bryły węgla = 1 sztabka brązu)");
    define("SMELT3", "Wytapiaj mosiądz (2 rudy miedzi + 1 ruda cynku + 2 bryły węgla = 1 sztabka mosiądzu)");
    define("SMELT4", "Wytapiaj żelazo (2 rudy żelaza + 3 bryły węgla = 1 sztabka żelaza)");
    define("SMELT5", "Wytapiaj stal (3 rudy żelaza + 7 brył węgla = 1 sztabka stali)");
    if (isset($_GET['smelt']))
    {
        define("NO_MINERALS", "Nie masz minerałów potrzebnych do wytapiania tego surowca!");
        define("SMELTM1", "sztabek miedzi.");
        define("SMELTM2", "sztabek brązu.");
        define("SMELTM3", "sztabek mosiądzu.");
        define("SMELTM4", "sztabek żelaza.");
        define("SMELTM5", "sztabek stali.");
        define("A_SMELT", "Wytop");
        define("NO_ENERGY", "Nie masz energii aby wytapiać surowce");
        define("NO_ENERGY2", "Nie masz tyle energii!");
        define("NO_MINERALS2", "Nie masz tylu rud aby wytopić taką ilość sztabek");
        define("YOU_SMELT", "Uzyskałeś");
        define("YOU_DEAD", "Nie możesz wytapiać sztabek ponieważ jesteś martwy");
        define("YOU_HAVE", "Posiadasz <b>");
        define("MIN1", "</b> rud miedzi, <b>");
        define("MIN2", "</b> rud cyny, <b>");
        define("MIN3", "</b> rud cynku, <b>");
        define("MIN4", "</b> rud żelaza, <b>");
        define("MIN5", "</b> brył węgla.");
        define("YOU_MAY", "Możesz z tego wytopić maksymalnie");
    }
}
?>
