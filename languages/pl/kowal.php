<?php
/**
 *   File functions:
 *   Polish language for blacksmith
 *
 *   @name                 : kowal.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 28.09.2011
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

if (!isset($_GET['kowal'])) 
{
    define("SMITH_INFO", "Witaj w kuźni. Tutaj możesz wyrabiać różne przedmioty. Aby móc je wykonywać musisz najpierw posiadać plany odpowiedniej rzeczy oraz odpowiednią ilość surowców.");
    define("A_PLANS", "Kup plany przedmiotu");
    define("A_SMITH", "Idź do kuźni");
    define("A_ASTRAL", "Buduj astralną konstrukcję");
}
    else
{
    define("A_BACK", "Wróć");
}

if (isset ($_GET['kowal']) && $_GET['kowal'] == 'plany') 
{
    define("PLANS_INFO", "Witaj w sklepie dla kowali. Tutaj możesz kupić plany przedmiotów, które chcesz wykonywać. Aby kupić dany plan, musisz mieć przy sobie odpowiednią ilość sztuk złota.");
    define("A_PLANS_W", "Kup plany broni");
    define("A_PLANS_A", "Kup plany zbroi");
    define("A_PLANS_H", "Kup plany hełmu");
    define("A_PLANS_S", "Kup plany tarczy");
    define("A_PLANS_L", "Kup plany nagolenników");
    define("I_NAME", "Nazwa");
    define("I_COST", "Cena");
    define("I_LEVEL", "Poziom");
    define("I_OPTION", "Opcje");
    define("HERE_IS", "Oto lista dostępnych planów");
    define("A_BUY", "Kup");
    if (isset($_GET['buy'])) 
    {
        define("YOU_HAVE", "Masz już taki plan!");
        define("NOT_FOR_SALE", "Tutaj tego nie sprzedasz.");
        define("NO_MONEY", "Nie stać cię!");
        define("NO_PLAN", "Nie ma takiego planu.");
        define("YOU_PAY", "Zapłaciłeś");
        define("AND_BUY", "sztuk złota, i kupiłeś za to nowy plan przedmiotu");
    }
}

if (isset ($_GET['kowal']) && ($_GET['kowal'] == 'kuznia' || $_GET['kowal'] == 'elite')) 
{
    if (!isset($_GET['konty']) && !isset($_GET['rob'])) 
    {
        define("SMITH_INFO", "Tutaj możesz wykonywać przedmioty co do których masz plany. Aby wykonać przedmiot, musisz posiadać również odpowiednią ilość surowców. Każda próba kosztuje ciebie tyle energii jaki jest poziom przedmiotu. Nawet za nieudaną próbę dostajesz 0,01 do umiejętności.");
        define("INFO", "Oto lista przedmiotów, które możesz wykonywać. Jeżeli nie masz tyle energii aby wykonać ów przedmiot, możesz po prostu wykonywać go po kawałku");
        define("A_MAKE_W", "Wykonuj broń");
        define("A_MAKE_A", "Wykonuj zbroje");
        define("A_MAKE_S", "Wykonuj tarcze");
        define("A_MAKE_H", "Wykonuj hełmy");
        define("A_MAKE_L", "Wykonuj nagolenniki");
        define("A_MAKE_R", "Wykonuj groty strzał");
        define("I_NAME", "Nazwa");
        define("I_LEVEL", "Poziom");
        define("I_AMOUNT", "Minerałów");
        define("INFO3", "Oto przedmiot jaki obecnie wykonujesz");
        define("I_PERCENT", "Wykonany(w %)");
        define("I_ENERGY", "Potrzebnej energii");
    }
    if (isset($_GET['ko']) || isset($_GET['dalej'])) 
    {
        define("YOU_DEAD", "Nie możesz kuć ponieważ jesteś martwy!");
        define("ASSIGN_EN", "Przeznacz na wykonanie");
        define("S_ENERGY", " energii.");
        define("A_MAKE", "Wykonaj");
        define("M_COPPER", "z miedzi");
        define("M_BRONZE", "z brązu");
        define("M_BRASS", "z mosiądzu");
        define("M_IRON", "z żelaza");
        define("M_STEEL", "ze stali");
    }
    if (isset($_GET['konty']) || isset($_GET['rob'])) 
    {
        define("NO_ENERGY", "Nie masz tyle energii.");
        define("DRAGON1", "Smoczy ");
        define("DRAGON2", "Smocza ");
        define("DRAGON3", "Smocze ");
        define("ELFS2" ,"Elfia ");
        define("ELFS3", "Elfie ");
        define("DWARFS1", "Krasnoludzki ");
        define("DWARFS2", "Krasnoludzka ");
        define("DWARFS3", "Krasnoludzkie ");
        define("YOU_MAKE", "Wykonałeś <b>");
        define("AND_EXP2", "</b> PD oraz <b>");
        define("IN_SMITH", "</b> poziomu w umiejętności Kowalstwo.<br />");
        define("M_COPPER", "z miedzi");
        define("M_BRONZE", "z brązu");
        define("M_BRASS", "z mosiądzu");
        define("M_IRON", "z żelaza");
        define("M_STEEL", "ze stali");
        define("S_ENERGY", " energii.");
        define("R_AGI", "zręczności");
        define("R_STR", "siły");
        define("R_INT", "inteligencji");
    }
    if (isset($_GET['konty'])) 
    {
        define("TOO_MUCH", "Nie możesz przeznaczyć na przedmiot więcej energii niż trzeba!");
        define("NO_ITEM", "Nie wykonujesz takiego przedmiotu!");
        define("AND_GAIN2", "</b>. Zdobywasz <b>");
        define("YOU_TRY", "Próbowałeś wykonać <b>");
        define("BUT_FAIL", "</b>, niestety nie udało się. Zdobywasz <b>");
        define("YOU_WORK", "Poświęciłeś na wykonanie ");
        define("NEXT_EN", " kolejne ");
        define("NOW_IS", " energii. Teraz jest on wykonany w ");
        define("YOU_NEED2", " procentach. Aby go ukonczyć potrzebujesz ");
    }
    if (isset($_GET['rob'])) 
    {
        define("HOW_MANY", "Podaj ile przedmiotów chcesz wykonać!");
        define("NO_MAT", "Nie masz tylu materiałów!");
        define("NO_PLANS", "Nie masz takiego planu");
        define("AND_GAIN2", "</b> razy. Zdobywasz <b>");
        define("YOU_WORK", "Pracowałeś nad ");
        define("YOU_USE", ", zużywając ");
        define("AND_MAKE", " energii i wykonałeś go w ");
        define("TO_END", " procentach. Aby ukończyć przedmiot potrzebujesz jeszcze ");
        define("YOU_MAKE2", "Nie możesz wykonywać nowego przedmiotu ponieważ pracujesz już nad jednym!");
    }
}

if (isset($_GET['kowal']) && $_GET['kowal'] == 'astral')
{
    define("MIN1", "Brył adamantium");
    define("MIN2", "Kryształów");
    define("MIN3", "Kawałków meteorytu");
    define("MIN5", "Drewna sosnowego");
    define("MIN4", "Drewna z leszczyny");
    define("MIN6", "Drewna cisowego");
    define("MIN7", "Drewna z wiązu");
    define("MIN13", "Sztabek stali");
    define("MIN8", "Rudy żelaza");
    define("MIN9", "Rudy miedzi");
    define("MIN10", "Rudy cyny");
    define("MIN11", "Rudy cynku");
    define("MIN12", "Brył węgla");
    define("CONST1", "Astralny komponent");
    define("CONST2", "Gwiezdny portal");
    define("CONST3", "Świetlny obelisk");
    define("CONST4", "Płomienny znicz");
    define("CONST5", "Srebrzysta fontanna");
    define("SMITH_INFO", "Tutaj możesz wykonywać różne astralne konstrukcje. W danym momencie możesz wykonywać tylko te, których plany posiadasz.");
    define("NO_PLAN", "Nie masz takiego planu");
    define("A_BUILD", "Buduj");
    define("T_NAME", "Nazwa");
    define("NO_AMOUNT", "Nie masz takiej ilości ");
    define("NO_MITH", "Nie masz takiej ilości mithrilu");
    define("NO_ENERGY", "Nie masz takiej ilości energii");
    define("YOU_MAKE", "Wykonałeś ");
    define("YOU_GAIN11", "Zdobywasz ");
    define("YOU_GAIN12", " punktów doświadczenia oraz ");
    define("YOU_GAIN13", " poziom(y) w umiejętności kowalstwo. ");
    define("YOU_FAIL", "Próbowałeś wykonać ");
    define("YOU_FAIL2",", niestety nie udało się.");
    define("YOU_USE", "Zużyłeś na to:<br />");
}
?>
