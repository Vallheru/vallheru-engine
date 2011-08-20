<?php
/**
 *   File functions:
 *   Polish language for potions market
 *
 *   @name                 : mmarket.php                            
 *   @copyright            : (C) 2004,2005,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 20.08.2011
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
// $Id$

define("ERROR", "Zapomnij o tym!");
define("A_BACK", "wróć");

if (!isset($_GET['view']) && !isset($_GET['buy']) && !isset($_GET['wyc']))
{
    define("A_BACK2", "Wróć na rynek.");
    define("A_VIEW", "Zobacz oferty");
    define("A_SEARCH", "Szukaj ofert");
    define("A_ADD", "Dodaj ofertę");
    define("A_DELETE", "Skasuj wszystkie swoje oferty");
    define("A_LIST", "Spis wszystkich ofert na rynku");
    define("M_INFO", "Tutaj jest rynek z miksturami. Masz parę opcji");
}

if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    define("NO_OFERTS", "Nie ma ofert na rynku.");
    define("A_BUY", "Kup");
    define("A_DELETE", "Wycofaj");
    define("A_PREVIOUS", "Poprzednie");
    define("A_NEXT", "Następne");
    define("VIEW_INFO", "Zobacz ceny mikstur lub");
    define("T_AMOUNT", "Ilość");
    define("T_COST", "Koszt");
    define("T_SELLER", "Sprzedający");
    define("T_OPTIONS", "Opcje");
    define("T_NAME", "Nazwa");
    define("T_EFECT", "Efekt");
    define("A_ADD", "Dodaj");
    define("A_CHANGE", "Zmień cenę");
    define("A_SEARCH", "Szukaj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    define("NO_AMOUNT", "Nie masz takiej ilości ");
    define("YOU_ADD", "Dodałeś <b>");
    define("AMOUNT", " sztuk ");
    define("ON_MARKET", "</b> na rynku za <b>");
    define("FOR_GOLDS", "</b> sztuk złota każda");
    define("ADD_INFO", "Dodaj ofertę na rynku lub");
    define("P_AMOUNT", "Ilość");
    define("P_AMOUNT2", "ilość");
    define("POTION", "Mikstura");
    define("P_COST", "Cena za jedną miksturę");
    define("A_ADD", "Dodaj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    define("YOU_DELETE", "Usunąłeś wszystkie swoje oferty i twoje mikstury wróciły do ciebie.");
}

if (isset($_GET['buy'])) 
{
    define("BUY_INFO", "Zakup miksturę lub");
    define("O_AMOUNT", "Ilość w ofercie");
    define("P_COST", "Cena za sztukę");
    define("P_SELLER", "Sprzedający");
    define("B_AMOUNT", "Ilość");
    define("A_BUY", "Kup");
    define("P_POWER", "Moc");
    define("POTION", "Mikstura");
    define("NO_OFERTS", "Nie ma takiej oferty na rynku.");
    define("IS_YOUR", "Nie możesz kupić swoich mikstur!");
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
        define("YOU_BUY", "Kupiłeś <b>");
        define("FOR_A", "</b> za <b>");
        define("GOLD_COINS", " sztuk złota.");
    }
}

if (isset($_GET['wyc'])) 
{
    define("NOT_YOUR", "Nie możesz wycofać cudzych ofert!");
    define("YOU_DELETE", "Usunąłeś swoją ofertę i twoje mikstury wróciły do ciebie.");
}

if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    define("LIST_INFO", "Tutaj masz spis wszystkich ofert jakie są na rynku.");
    define("P_NAME", "Nazwa");
    define("P_AMOUNT", "Ofert");
    define("P_ACTION", "Akcja");
    define("A_SHOW", "Pokaż");
}
?>
