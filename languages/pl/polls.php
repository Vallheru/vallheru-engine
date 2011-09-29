<?php
/**
 *   File functions:
 *   Polish language for city polls
 *
 *   @name                 : polls.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 29.09.2011
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
define("T_VOTES", "głosów");
define("A_BACK", "Wróć");
define("SUM_VOTES", "Głosowało");
define("T_MEMBERS", "mieszkańców");
define("A_COMMENTS", "Komentarze");

if (!isset($_GET['action']))
{
    if ($player -> location == 'Altara')
    {
        define("POLLS_INFO", "W centrum ".$city1b." zebrał się niespotykany tłum. Dziś ważny dzień w życiu stolicy Vallheru. Król Thindill we własnej osobie spotka się ze swymi mieszkańcami, aby zdecydować o sprawach wielkiej wagi. Przed Tobą potężny gmach, w którym odbędzie się zgromadzenie, nie mające precedensu w nowożytnej historii ".$city1b.". Budynek wybudował niejaki Agrandriss - elficki architekt znany w całej krainie.<br />Hala Zgromadzeń jest jednym z najwyższych budynków w całym mieście. Wejście jest otoczone misternie rzeźbionymi kolumnami, z przewodnim motywem liścia, stanowiącego godło Agrandrissa.  Żelazne drzwi, inkrustowane mithrillem, wysokie na dziewięć stóp. Wartę przy nich pełnią królewscy strażnicy.<br /><br />Przeciskasz się przez wciąż gęstniejący tłum. Chcesz dostać się do Hali, tak jak i inni mieszkańcy królestwa. Wchodzisz do środka i wstrzymujesz oddech z zachwytu. Gmach wygląda jak ogromny amfiteatr. W samym jego  centrum znajduje się wielki marmurowy tron. Tysiące osób siedzą wygodnie na kamiennych ławkach wokół podestu, jednak owa grupa jest zaledwie kroplą w morzu napływającej ludności.<br />- \"Tutaj mogą pomieścić się wszyscy mieszkańcy całego królestwa Vallheru!\" - stwierdzasz zachwycony. Widzisz jak strzeliste kolumny wspierają wysoki strop. Ogromne okna, wypełnione misternymi witrażami, ogniskują i rozświetlają wnętrze Hali specyficznym blaskiem.<br />Nagle rozlega się dźwięk trąb i na podest wchodzi orszak królewski. Najpierw pojawia się król Thindill, za nim Namiestnik Storm, a następnie książęta, sędziowie, radni i reszta świty. Cały podest jest otoczony przez gwardię królewską, chroniącą króla. Thindill zatrzymuje się przy tronie, spogląda z satysfakcją na zgromadzonych mieszkańców i siada dostojnie. Wkrótce Namiestnik rozpoczyna publiczne głosowanie...");
        define("POLLS_INFO2", "Jak co dzień o tej porze wychodząc z karczmy idziesz w lewo w ulicę Południową, skręcasz w prawo i przechodzisz przez pusty plac ...<br />- Stój - natarczywy glos strażnika dochodzi zza Twoich pleców- dzisiaj nie ma obrad, przyjdź jutro tylko... ogól się i doprowadź do porządku.<br />Odwracasz się na pięcie nie chcąc wchodzić w kłótnię z tym gburem i zastanawiasz się jakie obrady mają mieć miejsce na placu, przez który od lat przecinał Twoją drogę do domu. Wiedząc, że niczego nie wskórasz, obierasz inną drogę. Odwracasz się jednak by jeszcze spojrzeć na swój pusty placyk, na którym nie raz zdarzało Ci się zasypiać i...<br />- O Wielki Illuminati - krzyczysz na całe gardło sprowadzając na siebie spojrzenia przechodzących - cóż to jest<br />Twój pusty plac prowadzący skrótem do Twojego łóżka nie był już pusty. \"Zapełniał\" go ogromny budynek wysoki chyba na 1000 stóp, pięknie zdobiony i... równie pięknie strzeżony przez gwardzistów w zbrojach, którzy teraz dziwnie patrzyli się na Ciebie. Jeden z nich chyba zauważył ogromne zdziwienie na Twej twarzy, doszedł do wniosku, że długo nie opuszczałeś... karczmy, podszedł do Ciebie i rzekł:<br />- To co tutaj widzisz to Hala Zgromadzeń. Budynek powstał nie dawno i będzie miejscem publicznych obrad króla i jego poddanych. Będzie można zabrać głos, wypowiedzieć się na temat, który Cię interesuje. Jeżeli coś Ci się nie podoba w państwie będziesz mógł o tym powiedzieć królowi. Jak już mówiłem, obrady rozpoczynają się jutro, więc możesz przyjść.<br />Zerkałeś oniemiały to na strażnika to na Halę Zgromadzeń, a w Twej głowie krążyła jedna myśl:<br />- Jutro obrady, jutro obrady... jakie u licha obrady<br />Wolnym krokiem podążyłeś z powrotem do karczmy.");
    }
        else
    {
        define("POLLS_INFO", "Stoisz właśnie przed niezbyt wysoką brzozą. Na jej pniu widnieją magiczne symbole, który powoli układają się w pytanie:");
    }
    define("NO_POLLS", "Nie ma jeszcze ankiet");
    define("LAST_POLL", "Oto ostatnia ankieta");
    define("A_SEND", "Wyślij");
    define("A_LAST_10", "Pokaż ostatnie 10 ankiet");
    define("POLL_DAYS", "Ankieta potrwa jeszcze");
    define("T_DAYS", "dni");
    define("POLL_END", "Ankieta zakończona");
}

if (isset($_GET['action']) && $_GET['action'] == 'vote')
{
    define("VOTE_SUCC", "Dziękujemy za oddanie głosu");
}

if (isset($_GET['action']) && $_GET['action'] == 'last')
{
    define("LAST_INFO", "Oto lista ostatnich 10 ankiet");
}

if (isset($_GET['action']) && $_GET['action'] == 'comments')
{
    define("C_ADDED", "Komentarz dodany!");
    define("C_DELETED", "Komentarz skasowany!");
    define("NO_COMMENTS", "Nie ma jeszcze komentarzy do tej ankiety!");
    define("A_DELETE", "Skasuj");
    define("ADD_COMMENT", "Dodaj komentarz");
    define("A_ADD", "Dodaj");
    define("NO_PERM", "Nie masz prawa przebywać tutaj!");
    define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
    define("WRITED", "napisał(a)");
    define("NO_TEXT", "Nie ma takiej ankiety!");
}
?>
