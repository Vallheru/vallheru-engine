<?php
/**
 *   File functions:
 *   Polish language for exploring forest and moutains
 *
 *   @name                 : explore.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 23.07.2012
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

define("NO_LIFE", "Nie masz wystarczająco dużo życia aby walczyć.");
define("A_REFRESH", "Odśwież");
define("FIGHT1", "Nie możesz wędrować po górach, ponieważ jesteś w trakcie walki!<br />Napotkałeś ");
define("FIGHT2", ". Czy chcesz spróbować walki?");
define("FIGHT3", "Nie możesz wędrować po lesie, ponieważ jesteś w trakcie walki!<br />Napotkałeś ");
define("Y_TURN_F", "Tak (turowa walka)");
define("Y_NORM_F", "Tak (szybka walka)");
define("YOU_WANT", "Czy chcesz zwiedzać dalej?");
define("YOU_MEET", "Napotkałeś");
define("YOU_DEAD2", "Nie możesz wędrować po lesie ponieważ jesteś martwy");

if (isset($_GET['step']) && $_GET['step'] == 'run') 
{
    define("ESCAPE_SUCC", "Udało ci się uciec przed");
    define("ESCAPE_SUCC2", "Zdobywasz za to");
    define("ESCAPE_SUCC3", "PD");
    define("ESCAPE_FAIL", "Nie udało ci się uciec przed");
    define("ESCAPE_FAIL2", "Rozpoczyna się walka");
    define("R_SPE4", "szybkości");
}

if (isset($_GET['action']))
{
    define("TIRED2", "Nie masz tyle energii");
    define("HERB1", " Illani<br />");
    define("HERB2", " Illanias<br />");
    define("HERB3", " Nutari<br />");
    define("HERB4", " Dynallca<br />");
    define("T_MAPS", " kawałków map<br />");
    define("T_ASTRALS", " astralnych komponentów<br />");
    define("FIND_NOTHING", "Niestety nie znalazłeś nic ciekawego.<br /><br />");
    define("T_AMOUNT2", " energii.<br /><br />");
    define("YOU_FIND", "Zdobyłeś:<br /><br />");
    define("T_GOLD", " sztuk złota<br />");
}

if ($player -> hp > 0 && !isset ($_GET['action']) && $player -> location == 'Góry' && !isset($_GET['step'])) 
{
    define("M_INFO", "Dookoła siebie widzisz wysokie szczyty Gór Kazad-nar. Czy chcesz zobaczyć co znajduje się wśród nich? Każde zwiedzanie kosztuje 0,5 energii.");
}

if ($player -> location == 'Góry')
{
    define("HOW_MUCH", "Chcę przeznaczyć na wędrówkę po górach");;
    define("T_ENERGY", "energii.");
    define("T_WALK", "Wędruj");
}

if (isset ($_GET['action']) && $_GET['action'] == 'moutains' && $player -> location == 'Góry') 
{
    if (!isset($_GET['step']))
    {
        define("ACTION8", " W pewnym momencie dostrzegasz przed sobą most linowy przerzucony nad przepaścią. Obok wejścia na most stoi zakapturzona postać. Kiedy podchodzisz bliżej, odwraca się w twoim kierunku i cichym zmęczonym głosem mówi: <i>To Most Śmierci, tylko najmądrzejsi mogą tędy przejść na drugą stronę. Jeżeli chcesz przejść przez most musisz odpowiedzieć na 3 pytania. Jeżeli ci się uda, otrzymasz nagrodę. Jeżeli nie odpowiesz poprawnie - zginiesz. </i>Czy chcesz spróbować przejść przez most?<br />");
        define("YOU_GO", "Przeznaczyłeś na zwiedzanie gór ");
        define("T_METEOR", " kawałków meteorytu<br />");
        define("YOU_DEAD", "Nie możesz zwiedzać gór ponieważ jesteś martwy");
    }
        else
    {
        define("ONLY_ONCE", "Możesz odpowiedzieć na pytania tylko raz na reset!");
        define("F_QUESTION", "Dobrze oto pierwsze pytanie: <b>Jaki jest twój numer (ID)");
        define("A_NEXT", "Dalej");
        define("S_QUESTION", "Doskonale, odpowiedziałeś na pierwsze pytanie! Oto drugie pytanie: <b>Jak nazywa się kraina w której żyjesz");
        define("T_QUESTION", "Doskonale, odpowiedziałeś na drugie pytanie! Cóż za niezwykła inteligencja! Oto trzecie pytanie");
        define("Q_FAIL", "Kiedy wypowiedziałeś odpowiedź, nagle poczułeś że ziemia pod twoimi nogami zaczyna się osuwać a ty lecisz w dół przepaści. Ponieważ spadałeś dość długo zdążyłeś zobaczyć przed oczami całe swoje życie (przy nudniejszych fragmentach udało ci się nawet przysnąć). Zanim zderzyłeś się z dnem przepaści ostatnią twoją myślą było: ZAPAMIĘTAĆ - UNIKAĆ STARYCH WARIATÓW PRZY MOSTACH ZADAJĄCYCH GŁUPIE PYTANIA!");
        define("Q_SUCC", "Doskonale, odpowiedziałeś na trzecie pytanie! Zdobyłeś w nagrodę");
        define("Q_SUCC2", "oraz możesz przejść przez most! Postać w kapturze powoli odchodzi, mrucząc pod nosem: <i>Co za świat, człowiek jak już dostaje jakąś rolę to praktycznie niewiele wartą. Muszę pogadać z moim agentem, cały czas daje mi takie same role");
    }
}

if ($player -> location == 'Las')
{
    define("HOW_MUCH", "Chcę przeznaczyć na wędrówkę po lesie");
    define("T_ENERGY", "energii.");
    define("T_WALK", "Wędruj");
}

if (isset ($_GET['action']) && $_GET['action'] == 'forest' && $player -> location == 'Las') 
{
    define("YOU_GO", "Przeznaczyłeś na zwiedzanie lasu ");
    define("T_ENERGY2", " energii<br />");
}
?>
