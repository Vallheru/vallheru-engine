<?php
/**
 *   File functions:
 *   Polish language for jeweller workshop
 *
 *   @name                 : jeweller.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 16.10.2006
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
define("RING", "pierścień");
define("YOU_DEAD", "Nie możesz wykonywać pierścieni ponieważ jesteś martwy!");

if (!isset($_GET['step']))
{
    define("JEWELLER_INFO", "Witaj w warsztacie jubilerskim. Możesz tutaj wykonywać różne pierścienie. Tylko rzemieślnicy mogą wykonywać pierścienie z premią do cech.");
    define("A_PLANS", "Zakup plan pierścienia");
    define("A_RING", "Wykonuj pierścień");
    define("A_MAKE_RING", "Wykonaj artefakt");
    define("A_MAKE_RING2", "Wykonaj relikt");
}
    else
{
    define("A_BACK", "Wróć");
}

if (isset($_GET['step']) && $_GET['step'] == 'plans')
{
    define("T_NAME", "Nazwa");
    define("T_COST", "Cena");
    define("T_LEVEL", "Poziom");
    define("T_ACTION", "Akcja");
    define("A_BUY", "Kup");
    define("PLANS_INFO", "Tutaj możesz kupić plany różnych pierścieni.");
    if (isset($_GET['buy']))
    {
        define("WRONG_CLASS", "Tylko rzemieślnik może kupić ten plan!");
        define("NO_MONEY", "Nie masz tylu sztuk złota.");
        define("YOU_HAVE", "Masz już taki plan.");
        define("YOU_SPEND", "Wydałeś <b>");
        define("AND_BUY", "</b> sztuk złota i kupiłeś za to plan przedmiotu: <b>");
    }
}

if (isset($_GET['step']) && $_GET['step'] == 'make')
{
    define("NO_PLAN", "Nie masz takiego planu!");
    define("RING_INFO", "Tutaj możesz wykonywać zwykłe pieścienie. Wykonanie jednego pierścienia kosztuje 1 bryłę adamantium oraz 1 punkt energii.");
    define("A_MAKE", "Wykonaj");
    define("R_AMOUNT", "pierścieni.");
    if (isset($_GET['make']) && $_GET['make'] == 'Y')
    {
        define("NO_ADAMANTIUM", "Nie masz tyle brył adamantium.");
        define("NO_ENERGY", "Nie masz tyle energii");
        define("YOU_MAKE", "Wykonałeś <b>");
        define("YOU_GAIN3", " Zdobywasz <b>");
        define("T_PD", "</b> punktów doświadczenia oraz <b>");
        define("T_ABILITY", "</b> poziomów umiejętności jubilerstwo.");
    }
}

if (isset($_GET['step']) && $_GET['step'] == 'make2')
{
    define("RING_INFO", "Tutaj możesz wykonywać pierścienie z premią do cech. Wykonanie jednego pierścienia kosztuje punkty energii oraz podaną przy każdym planie liczbę brył adamantium, kryształów bądź brył meteorytu. Jeżeli posiadasz odpowiednio wysoką wartość umiejętności jubilerstwo możesz samodzielnie wybrać do jakiej cechy ma mieć premię pierścień.");
    define("RING_INFO2", "Obecnie pracujesz już nad pierścieniem. Aby kontynuować pracę, po prostu podaj ile energii chcesz użyć do wykonania tego przedmiotu. Pamiętaj że nie możesz przeznaczyć więcej energii niż potrzeba.");
    define("T_NAME", "Nazwa");
    define("T_LEVEL", "Poziom");
    define("T_ADAM", "Adamantium");
    define("T_CRYST", "Kryształ");
    define("T_METEOR", "Meteoryt");
    define("T_ENERGY", "Energii");
    define("T_ENERGY2", "Użytej energii");
    define("WRONG_CLASS", "Tylko rzemieślnik może wykonywać takie pierścienie!");
    define("NO_PLANS", "Nie masz jeszcze jakichkolwiek planów pierścieni.");
    define("T_CHANGE", "Wybór cechy");
    define("T_BONUS", "Maks. premia");
    define("R_STR", "siły");
    define("R_AGI", "zręczności");
    define("R_INT", "inteligencji");
    define("R_SPE", "szybkości");
    define("R_CON", "wytrzymałości");
    define("R_WIS", "siły woli");
    define("YOU_CONTINUE", "Pracuj nad");
    define("R_ENERGY", "energii.");
    define("R_AMOUNT2", "przeznaczając na to");
    if (isset($_GET['make']))
    {
        define("YOU_MAKE", "Wykonaj");
        define("WITH_BON", "z premią do");
    }
    if (isset($_GET['action']) && ($_GET['action'] == 'create' || $_GET['action'] == 'continue'))
    {
        define("YOU_MAKE", "Wykonałeś <b>");
        define("R_AMOUNT", "</b> sztuk <b>");
        define("YOU_GAIN3", "</b>. Zdobywasz <b>");
        define("AND_EXP2", "</b> PD oraz <b>");
        define("IN_JEWELLER", "</b> poziomów umiejętności jubilerstwo.<br />");
        define("YOU_TRY", "Próbowałeś wykonać <b>");
        define("YOU_GAIN4", "</b> jednak wykonałeś go tylko częściowo.");
    }
    if (isset($_GET['action']) && $_GET['action'] == 'create')
    {
        define("NO_PLAN", "Nie masz takiego planu!");
        define("NO_MINERALS", "Nie masz tyle minerałów.");
        define("NO_ENERGY", "Nie masz tyle energii.");
        define("NO_RINGS", "Nie masz tylu pierścieni.");
    }
    if (isset($_GET['action']) && $_GET['action'] == 'continue')
    {
        define("NO_WORK", "Nie pracujesz nad takim pierścieniem!");
        define("TOO_MUCH", "Nie możesz przeznaczyć aż tyle energii.");
        define("BUT_FAIL", "</b> niestety nie udało się. Zdobywasz <b>");
    }
}

if (isset($_GET['step']) && $_GET['step'] == 'make3')
{
    define("WRONG_CLASS", "Tylko rzemieślnik może wykonywać takie pierścienie!");
    define("RING_INFO", "Tutaj możesz wykonywać specjalne pierścienie (elfie, krasoludzkie, boskie itp). Aby móc je wykonać potrzebujesz odpowiedniej ilości brył meteorytu, energii, pierścieni z premią do jakiejś cechy oraz planu danego pierścienia. Poniżej znajduje się lista planów pierścieni jakie możesz ulepszać");
    define("RING_INFO2", "Obecnie pracujesz już nad pierścieniem. Aby kontynuować pracę, po prostu podaj ile energii chcesz użyć do wykonania tego przedmiotu. Pamiętaj że nie możesz przeznaczyć więcej energii niż potrzeba.");
    define("T_ENERGY2", "Użytej energii");
    define("T_NAME", "Nazwa");
    define("T_LEVEL", "Poziom");
    define("T_METEOR", "Meteoryt");
    define("T_ENERGY", "Energii");
    define("NO_PLANS", "Nie masz jeszcze jakichkolwiek planów pierścieni.");
    define("A_MAKE", "Przekuj");
    define("R_AMOUNT", "sztuk");
    define("ON_SPECIAL", "na relikty,");
    define("R_AMOUNT2", "ilość:");
    define("R_ELF", "Elfi ");
    define("R_DWARF", "Krasnoludzki ");
    define("R_GNOME", "Gnomi ");
    define("R_GOD", "Boski ");
    define("YOU_CONTINUE", "Pracuj nad");
    define("R_ENERGY", "energii.");
    define("R_AMOUNT4", "przeznaczając na to");
    if (isset($_GET['action']) && ($_GET['action'] == 'create' || $_GET['action'] == 'continue'))
    {
        define("YOU_MAKE", "Przekułeś <b>");
        define("R_AMOUNT3", "</b> sztuk <b>");
        define("YOU_GAIN3", "</b>. Zdobywasz <b>");
        define("AND_EXP2", "</b> PD oraz <b>");
        define("IN_JEWELLER", "</b> poziomów umiejętności jubilerstwo.<br />");
        define("YOU_TRY", "<br />Próbowałeś wykonać <b>");
        define("YOU_GAIN4", "</b> jednak wykonałeś go tylko częściowo.");
        define("R_STR", "siły");
        define("R_AGI", "zręczności");
        define("R_INT", "inteligencji");
        define("R_SPE", "szybkości");
        define("R_CON", "wytrzymałości");
        define("R_WIS", "woli");
    }
    if (isset($_GET['action']) && $_GET['action'] == 'continue')
    {
        define("NO_WORK", "Nie pracujesz nad takim pierścieniem!");
        define("TOO_MUCH", "Nie możesz przeznaczyć aż tyle energii.");
        define("BUT_FAIL", "</b> niestety nie udało się. Zdobywasz <b>");
    }
    if (isset($_GET['action']) && $_GET['action'] == 'create')
    {
        define("NO_METEOR", "Nie masz takiej ilości brył meteorytu.");
        define("NO_ENERGY", "Nie masz tyle energii.");
        define("NO_RINGS", "Nie masz tylu pierścieni.");
    }
}
?>
