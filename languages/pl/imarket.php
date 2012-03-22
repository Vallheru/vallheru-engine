<?php
/**
 *   File functions:
 *   Polish language for items market
 *
 *   @name                 : imarket.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 22.03.2012
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
define("ITEM", "Przedmiot");
define("A_PREVIOUS", "Poprzednie");
define("A_NEXT", "Następne");

if (!isset($_GET['view']) && !isset($_GET['buy']) && !isset($_GET['wyc']))
{
    define("A_BACK2", "Wróć na rynek.");
    define("A_VIEW", "Zobacz oferty");
    define("A_SEARCH", "Szukaj ofert");
    define("A_ADD", "Dodaj ofertę");
    define("A_DELETE", "Skasuj wszystkie swoje oferty");
    define("A_LIST", "Spis wszystkich ofert na rynku");
    define("M_INFO", "Tutaj jest rynek z przedmiotami. Masz parę opcji");
}

if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    define("NO_OFERTS", "Nie ma ofert na rynku.");
    define("A_BUY", "Kup");
    define("A_DELETE", "Wycofaj");
    define("VIEW_INFO", "Zobacz oferty lub");
    define("T_AMOUNT", "Ilość");
    define("T_COST", "Koszt");
    define("T_SELLER", "Sprzedający");
    define("T_OPTIONS", "Opcje");
    define("T_NAME", "Nazwa");
    define("T_POWER", "Premia");
    define("T_DUR", "Wytrzymałość");
    define("T_SPEED", "Szybkość");
    define("T_AGI", "Zręczność");
    define("T_LEVEL", "Poziom");
    define("A_ADD", "Dodaj");
    define("A_CHANGE", "Zmień cenę");
    define("A_SEARCH", "Szukaj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    define("NO_ITEMS", "Nie masz przedmiotów na sprzedaż!");
    define("NO_AMOUNT", "Nie masz takiej ilości ");
    define("YOU_ADD", "Dodałeś <b>");
    define("ON_MARKET", "</b> na rynku za <b>");
    define("FOR_GOLDS", "</b> sztuk złota");
    define("A_REFRESH", "Odśwież");
    define("ADD_INFO", "Dodaj ofertę na rynku lub");
    define("I_COST", "Cena za sztukę");
    define("I_AMOUNT", "ilość");
    define("I_AMOUNT2", "Ilość");
    define("A_ADD", "Dodaj");
    define("MAX_OFERTS", "Możesz dać na rynku maksymalnie 5 ofert!");
    define("I_AMOUNT3", " sztuk ");
}

if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    define("YOU_DELETE", "Usunąłeś wszystkie swoje oferty i twoje przedmioty wróciły do ciebie.");
}

if (isset($_GET['buy'])) 
{
    define("O_AMOUNT", "Ilość w ofercie");
    define("I_COST", "Cena za sztukę");
    define("SELLER", "Sprzedający");
    define("B_AMOUNT", "Ilość");
    define("A_BUY", "Kup");
    define("NO_OFERTS", "Nie ma takiej oferty na rynku.");
    define("IS_YOUR", "Nie możesz kupić swoich przedmiotów!");
    define("BUY_INFO", "Zakup przedmiot lub");
    define("I_POWER", "Siła");
    define("I_AGI", "Premia do zręczności");
    define("I_SPEED", "Premia do szybkości");
    define("I_DUR", "Wytrzymałość");
    define("A_AMOUNT", "Liczba strzał");
    define("H_AMOUNT", "Liczba grotów");
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
        define("NO_MONEY", "Nie stać cię!");
        define("NO_AMOUNT", "Nie ma takiej ilości ");
        define("ON_MARKET", " na rynku!");
        define("L_ACCEPT", "</a></b>, ID <b>");
        define("L_ACCEPT2", "</b> zaakceptował Twoją ofertę za ");
        define("L_AMOUNT", " sztuk ");
        define("YOU_GET", "</b>. Dostałeś <b>");
        define("TO_BANK", "</b> sztuk złota do banku.");
        define("YOU_BUY", "<br />Kupiłeś ");
        define("I_AMOUNT", " sztuk przedmiotu: ");
        define("FOR_A", "</b> za <b>");
        define("GOLD_COINS", "</b> sztuk złota.");
    }
}

if (isset($_GET['wyc'])) 
{
    define("NOT_YOUR", "Nie możesz wycofać cudzych ofert!");
    define("YOU_DELETE", "Usunąłeś swoją ofertę i twój przedmiot wrócił do ciebie.");
}

if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    define("LIST_INFO", "Tutaj masz spis wszystkich ofert jakie są na rynku.");
    define("I_NAME", "Nazwa");
    define("I_AMOUNT", "Ofert");
    define("I_ACTION", "Akcja");
    define("A_SHOW", "Pokaż");
}
?>
