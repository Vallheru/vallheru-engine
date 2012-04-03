<?php
/**
 *   File functions:
 *   Polish language for mountains menu
 *
 *   @name                 : gory.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 03.04.2012
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

define("NOT_IN", "Nie znajdujesz się w górach");
define("PL_DEAD", "Ponieważ jesteś martwy, twa dusza podąża z powrotem do szpitala w ".$city1a.". Kliknij ");
define("A_HERE", "tutaj");
define("MOUNT_INFO", "  Witaj w Górach Kazad-nar, co chcesz robić?");
define("A_MINE", "Idź do kopalni");
define("A_SEARCH", "Zwiedzaj góry");
define("A_TRAVEL", "Stajnia");
define("YOU_DEAD", "Jesteś martwy");
define("BACK_TO", "Powrót do ".$city1b."");
define("STAY_HERE", "Pozostań na miejscu");
define("NOT_NEED", "Nie potrzebujesz wskrzeszenia!");

if (isset($_GET['action']))
{
    define("NO_MONEY_FOR", "Nie możesz być wskrzeszony.");
    define("YOU_RES", "<br />Zostałeś wskrzeszony, ale straciłeś ");
    define("LOST_EXP", " Punktów Doświadczenia.");
    define("A_BACK", "Wróć");
    define("HERMIT", "Twoje ciało odnalazł mieszkający w pobliżu pustelnik. Ku twemu zdziwieniu, starzec ten posiadł zdolność kontaktowania się z duszami umarłych... Zauważasz to, gdy zaczynasz słyszeć słowa...");
    define("HERMIT2", "Bardzo nieostrożnie z Twojej strony wybierać się samemu w tak daleką podróż. Wiele niebezpieczeństw czyha na samotników. Ale spróbuję Ci pomóc. Spróbuję przygotować odpowiedni czar, który Cię wskrzesi, jednak to trochę potrwa. Jest jeszcze możliwość skorzystania z pobliskiego źródełka z pobłogosławioną przez Illuminati wodą. Jej efekt jest natychmiastowy, jednak aby zadziałała muszę złożyć ofiarę w postaci złota. Jestem pustelnikiem więc nie mam żadnego złota przy sobie. O ile wyrazisz zgodę to wezmę złoto z Twojej sakwy. Przysięgam, że oprócz potrzebnej ilości nie wezmę ani sztuki złota więcej. Wybór należy do Ciebie...");
    define("A_RESURECT", "Skorzystaj z pobłogosławionej wody");
    define("T_GOLD", "Ofiara:");
    define("GOLD_COINS", "sztuk złota");
    define("A_WAIT", "Zaczekaj aż pustelnik przygotuje czar");
    if (isset($_GET['action2']) && $_GET['action2'] == 'wait')
    {
        define("WAIT_INFO", "Przed Twoimi oczami przebiegają wydarzenie z przeszłości... To wspomnienia. Czas dłuży się niesamowicie... Nagle słyszysz słowa:<br /><br /><i>Cierpliwości. Właśnie przygotowuję czar dla Ciebie. Na szczęście mam już potrzebne składniki, ale rzucenie wskrzeszającego czaru to nie taka prosta sprawa. Trzeba być ostrożnym, gdyż nie wiem jakie konsekwencje mogłaby mieć moja pomyłka.</i>");
    }
    if (isset($_GET['action2']) && $_GET['action2'] == 'resurect')
    {
        define("RES1", "Otwierasz powoli oczy... Widzisz klęczącego nad tobą starca, a w ustach odczuwasz jeszcze słodkawy smak błogosławionej wody... Ku twemu zdziwieniu nie czujesz żadnego bólu. Po ranach, które odniosłeś nie ma śladu...");
        define("RES2", "Tutaj są Twoje rzeczy. Ja niestety muszę iść, gdyż zapewne są inni potrzebujący pomocy. Na przyszłość postaraj się zachować nieco więcej ostrożności. Bywaj w zdrowiu...");
        define("RES3", "Po wypowiedzeniu tych słów pustelnik oddala się. Po chwili odpoczynku podnosisz się, zbierasz swój ekwipunek i wyruszasz w drogę.");
    }
}
?>
