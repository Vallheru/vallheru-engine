<?php
/**
 *   File functions:
 *   Polish language for astral market
 *
 *   @name                 : amarket.php                            
 *   @copyright            : (C) 2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
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
// $Id$

define("ERROR", "Zapomnij o tym!");
define("A_BACK", "wróć");
define("ASTRAL", "Nazwa komponentu");
define("MAP1", "Plan demoniczny");
define("MAP2", "Plan ognisty");
define("MAP3", "Plan piekielny");
define("MAP4", "Plan pustynny");
define("MAP5", "Plan wodny");
define("MAP6", "Plan niebiański");
define("MAP7", "Plan śmiertelny");
define("PLAN1", "Astralny komponent");
define("PLAN2", "Gwiezdny portal");
define("PLAN3", "Świetlisty obelisk");
define("PLAN4", "Płomienny znicz");
define("PLAN5", "Srebrzysta fontanna");
define("RECIPE1", "Magiczna esensja");
define("RECIPE2", "Gwiezdna maść");
define("RECIPE3", "Eliksir Illuminati");
define("RECIPE4", "Astralne medium");
define("RECIPE5", "Magiczny absynt");
define("COMP1", "Ząb Glabrezu");
define("COMP2", "Ognisty pył");
define("COMP3", "Pazur Zgłębiczarta");
define("COMP4", "Łuska Skorpendry");
define("COMP5", "Macka Krakena");
define("COMP6", "Piorun Tytana");
define("COMP7", "Żebro Licha");
define("CONST1", "Astralny komponent (konstrukcja)");
define("CONST2", "Gwiezdny portal (konstrukcja)");
define("CONST3", "Świetlny obelisk (konstrukcja)");
define("CONST4", "Płomienny znicz (konstrukcja)");
define("CONST5", "Srebrzysta fontanna (konstrukcja)");
define("POTION1", "Magiczna esensja (mikstura)");
define("POTION2", "Gwiezdna maść (mikstura)");
define("POTION3", "Eliksir Illuminati (mikstura)");
define("POTION4", "Astralne medium (mikstura)");
define("POTION5", "Magiczny absynt (mikstura)");
define("T_NUMBER", "Numer");

if (!isset($_GET['view']) && !isset($_GET['buy']) && !isset($_GET['wyc']))
{
    define("A_BACK2", "Wróć na rynek.");
    define("A_VIEW", "Zobacz oferty");
    define("A_SEARCH", "Szukaj ofert");
    define("A_ADD", "Dodaj ofertę");
    define("A_DELETE", "Skasuj wszystkie swoje oferty");
    define("A_LIST", "Spis wszystkich ofert na rynku");
    define("M_INFO", "Tutaj jest rynek z astralnymi komponentami. Masz parę opcji");
}

if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    define("NO_OFERTS", "Nie ma ofert na rynku.");
    define("A_BUY", "Kup");
    define("A_DELETE", "Wycofaj");
    define("A_PREVIOUS", "Poprzednie");
    define("A_NEXT", "Następne");
    define("VIEW_INFO", "Zobacz ceny komponentów lub");
    define("T_AMOUNT", "Ilość");
    define("T_COST", "Koszt");
    define("T_SELLER", "Sprzedający");
    define("T_OPTIONS", "Opcje");
    define("A_STEAL", "Kradzież");
    define("A_ADD", "Dodaj");
    define("A_CHANGE", "Zmień cenę");
    define("A_SEARCH", "Szukaj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    define("NO_AMOUNT", "Nie masz takiej ilości ");
    define("YOU_ADD", "Dodałeś ofertę do rynku.");
    define("A_REFRESH", "Odśwież");
    define("ADD_INFO", "Dodaj ofertę na rynku lub");
    define("H_AMOUNT", "Ilość komponentów");
    define("H_COST", "Cena za sztukę");
    define("A_NUMBER", "Numer kawałka");
    define("A_ADD", "Dodaj");
    define("T_ADD", "Dodaj kawałek mapy, planu, przepisu");
    define("T_ADD2", "Dodaj komponent, konstrukcję, miksturę");
    define("YOU_WANT", "Czy chcesz dodać tę ofertę do już istniejącej własnej oferty?");
}

if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    define("YOU_DELETE", "Usunąłeś wszystkie swoje oferty i twoje astralne komponenty wróciły do ciebie.");
}

if (isset($_GET['buy'])) 
{
    define("BUY_INFO", "Zakup komponenty lub");
    define("O_AMOUNT", "Ilość w ofercie");
    define("H_COST", "Cena za sztukę");
    define("SELLER", "Sprzedający");
    define("B_AMOUNT", "Ilość");
    define("A_BUY", "Kup");
    define("NO_OFERTS", "Nie ma takiej oferty na rynku.");
    define("IS_YOUR", "Nie możesz kupić swoich komponentów!");
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
    define("YOU_DELETE", "Usunąłeś swoją ofertę i twoje komponenty wróciły do ciebie.");
}

if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    define("LIST_INFO", "Tutaj masz spis wszystkich ofert jakie są na rynku.");
    define("H_NAME", "Nazwa");
    define("H_AMOUNT", "Ofert");
    define("H_ACTION", "Akcja");
    define("A_SHOW", "Pokaż");
}

if (isset($_GET['steal']))
{
    define("NO_CRIME", "Nie możesz próbować kradzieży astralnego komponentu, ponieważ niedawno próbowałeś już swoich sił!");
    define("YOU_DEAD", "Nie możesz okradać innych ponieważ jesteś martwy");
    define("VERDICT", "Próba kradzieży astralnego komponentu");
    define("L_REASON", "Zostałeś wtrącony do więzienia na 2 dni za próbę kradzieży astralnego komponentu. Możesz wyjść z więzienia za kaucją: ");
    define("C_CACHED", "Kiedy próbowałeś niepostrzeżenie zabrać ze straganu komponent, zauważyli ciebie strażnicy. Błyskawicznie otoczyli i zmusili do poddania. I tak oto znalazłeś się w lochach.");
    define("NO_AMOUNT", "Szczęśliwie pokonałeś wszelkie przeszkody i niezauważony zbliżasz się do astralnego skarbca. Kiedy wchodzisz do środka i rozglądasz się w około masz ochotę zawyć ze złości. Nic tutaj nie ma! Tyle nerwów na marne. Zdenerwowany opuszczasz skarbiec.");
    define("SUCCESFULL", "Ostrożnie, by nie zwrócić na siebie uwagi, wyciągasz rękę po komponent. Wykorzystujesz fakt iż sprzedawca akurat patrzy w inną stronę. Szybkim ruchem chwytasz zdobycz i spokojnie oddalasz się od miejsca zbrodni. Po kilku chwilach słyszysz z oddali zrozpaczony krzyk sprzedawcy. Kiedy wszyscy biegną w tamtym kierunku, ty spokojnie odchodzisz w boczną uliczkę. Udało ci się ukraść");
    define("L_CACHED", "Mieszkaniec ");
    define("L_CACHED2", " ID: ");
    define("L_CACHED3", " próbował ukraść astralny komponent z rynku. Na szczęście został pojmany przez strażników.");
    define("ASTRAL_GONE", "Kiedy przeglądałeś swoją ofertę na rynku astralnych komponentów, zauważyłeś że jednego brakuje. Ktoś prawdopodobnie okradł ciebie! Ze stoiska zniknął");
    define("PIECE", " kawałek mapy/planu <b>");
    define("COMPONENT", " kompletny komponent <b>");
}
?>
