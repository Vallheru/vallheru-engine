<?php
/**
 *   File functions:
 *   Polish language for minerals market
 *
 *   @name                 : pmarket.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 06.10.2011
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
define("MINERAL", "Minerał");
define("MIN1", "Mithril");
define("MIN2", "Sztabki miedzi");
define("MIN3", "Sztabki żelaza");
define("MIN4", "Bryły węgla");
define("MIN5", "Bryły adamantium");
define("MIN6", "Kawałki meteorytu");
define("MIN7", "Kryształów");
define("MIN8", "Drewno sosnowe");
define("MIN9", "Sztabki brązu");
define("MIN10", "Sztabki mosiądzu");
define("MIN11", "Sztabki stali");
define("MIN12", "Drewno z leszczyny");
define("MIN13", "Drewno cisowe");
define("MIN14", "Drewno z wiązu");
define("MIN15", "Ruda miedzi");
define("MIN16", "Ruda cynku");
define("MIN17", "Ruda cyny");
define("MIN18", "Ruda żelaza");

if (!isset($_GET['view']) && !isset($_GET['buy']) && !isset($_GET['wyc']))
{
    define("A_BACK2", "Wróć na rynek.");
    define("A_VIEW", "Zobacz oferty");
    define("A_SEARCH", "Szukaj ofert");
    define("A_ADD", "Dodaj ofertę");
    define("A_DELETE", "Skasuj wszystkie swoje oferty");
    define("A_LIST", "Spis wszystkich ofert na rynku");
    define("M_INFO", "Tutaj jest rynek z minerałami. Masz parę opcji");
}

if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    define("NO_OFERTS", "Nie ma ofert na rynku.");
    define("A_BUY", "Kup");
    define("A_DELETE", "Wycofaj");
    define("A_PREVIOUS", "Poprzednie");
    define("A_NEXT", "Następne");
    define("VIEW_INFO", "Zobacz ceny minerałów lub");
    define("T_AMOUNT", "Ilość");
    define("T_COST", "Koszt");
    define("T_SELLER", "Sprzedający");
    define("T_OPTIONS", "Opcje");
    define("A_ADD", "Dodaj");
    define("A_CHANGE", "Zmień cenę");
    define("A_SEARCH", "Szukaj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    define("MITH", "mithrilu");
    define("COPPER", "sztabek miedzi");
    define("IRON", "sztabek żelaza");
    define("COAL", "brył węgla");
    define("ADAMANTIUM", "brył adamantium");
    define("METEOR", "kawałków meteorytu");
    define("CRYSTAL", "kryształów");
    define("PINE", "drewna sosnowego");
    define("COPPERORE", "rudy miedzi");
    define("ZINCORE", "rudy cynku");
    define("TINORE", "rudy cyny");
    define("IRONORE", "rudy żelaza");
    define("BRONZE", "sztabek brązu");
    define("BRASS", "sztabek mosiądzu");
    define("STEEL", "sztabek stali");
    define("HAZEL", "drewna z leszczyny");
    define("YEW", "drewna cisowego");
    define("ELM", "drewna z wiązu");
    define("NO_AMOUNT", "Nie masz takiej ilości ");
    define("YOU_ADD", "Dodałeś <b>");
    define("ON_MARKET", " na rynku za <b>");
    define("FOR_GOLDS", "</b> sztuk złota.");
    define("A_REFRESH", "Odśwież");
    define("ADD_INFO", "Dodaj ofertę na rynku lub");
    define("M_AMOUNT", "Ilość minerałów");
    define("M_COST", "Cena za sztukę");
    define("A_ADD", "Dodaj");
    define("YOU_WANT", "Czy chcesz dodać tę ofertę do już istniejącej własnej oferty?");
    define("ON_MARKET2", " do już istniejącej własnej oferty.");
    define("T_AMOUNT", "ilość");
}

if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    define("YOU_DELETE", "Usunąłeś wszystkie swoje oferty i twoje minerały wróciły do ciebie.");
}

if (isset($_GET['buy'])) 
{
    define("BUY_INFO", "Zakup minerały lub");
    define("O_AMOUNT", "Ilość w ofercie");
    define("M_COST", "Cena za sztukę");
    define("M_SELLER", "Sprzedający");
    define("B_AMOUNT", "Ilość");
    define("A_BUY", "Kup");
    define("NO_OFERTS", "Nie ma takiej oferty na rynku.");
    define("IS_YOUR", "Nie możesz kupić swoich minerałów!");
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
        define("NO_MONEY", "Nie stać cię!");
        define("NO_AMOUNT", "Nie ma takiej ilości ");
        define("ON_MARKET", " na rynku!");
        define("L_ACCEPT", "</a></b>, ID <b>");
        define("L_ACCEPT2", "</b> zaakceptował Twoją ofertę za ");
        define("L_AMOUNT", " sztuk ");
        define("YOU_GET", ". Dostałeś <b>");
        define("TO_BANK", "</b> sztuk złota do banku.");
        define("YOU_BUY", "Kupiłeś <b>");
        define("I_AMOUNT", " sztuk</b> ");
        define("FOR_A", " za ");
        define("GOLD_COINS", " sztuk złota.");
    }
}

if (isset($_GET['wyc'])) 
{
    define("NOT_YOUR", "Nie możesz wycofać cudzych ofert!");
    define("YOU_DELETE", "Usunąłeś swoją ofertę i twoje minerały wróciły do ciebie.");
}

if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    define("LIST_INFO", "Tutaj masz spis wszystkich ofert jakie są na rynku.");
    define("M_NAME", "Nazwa");
    define("M_AMOUNT", "Ofert");
    define("M_ACTION", "Akcja");
    define("A_SHOW", "Pokaż");
}
?>
