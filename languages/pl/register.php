<?php
/**
 *   File functions:
 *   Polish language for registration page
 *
 *   @name                 : register.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.5
 *   @since                : 08.12.2011
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

define("CHARSET", "utf-8");
define("WELCOME", "Witaj");
define("REGISTER", "Rejestracja");
define("RULES", "Zasady");
define("LINKS", "Linki");
define("FORUMS", "Forum");
define("REASON", "Przyczyna wyłączenia rejestracji");
define("EMAIL", "Email");
define("PASSWORD", "Hasło");
define("LOGIN", "Zaloguj");
define("LOST_PASSWORD", "Zapomniałem hasła");
define("CURRENT_TIME", "Obecny czas");
define("WE_HAVE", "Mamy");
define("REGISTERED", " zarejestrowanych graczy");
define("IN_GAME", "graczy w grze");

if (!isset($_GET['action']))
{
    define("DESCRIPTION", "Zarejestruj się w grze. To nic nie kosztuje. Po rejestracji na twoje konto email zostanie wysłany specjalny link aktywacyjny. <b>Uwaga!</b> Jeżeli korzystasz z konta na Interii lub Tlenie - sprawdź czy nie masz ustawionego filtru anty-spamowego. Ponieważ mail jest wysyłany programowo, nie ręcznie jest traktowany jako spam i może nigdy nie dojść do ciebie! Konta założone na Wirtualnej Polsce w ogóle nie przyjmują takich maili. Dlatego aby założyć konto w grze, nie używaj konta WP.<br />Hasło musi składać się z co najmniej 5 znaków z czego musi być co najmniej jedna wielka litera (A,G,W, itd) oraz cyfra.<br />Mamy obecnie ");
    define("DESCRIPTION2", "Nagle otacza Cię ciemność. Czujesz subtelne zawirowanie w otaczającym Cię powietrzu. W tej samej chwili pojawia się błękitnawa sfera, we wewnętrzu której zauważasz liczne wyładowania elektryczne. Powietrze wypełnia się charakterystycznym ostrym zapachem, co niewatpliwie jest wynikiem działania wielkiej magii. Owa sfera to magiczny portal, przez który wychodzi powoli, dumnie starzec, ubrany w szmaragdowozieloną, wyszywaną srebrzystymi runami szatę. Oburącz trzyma kościaną laskę, a u jego pasa wisi kilka różnorakich różdżek.<br />- \"Jestem Elmanir, Nadworny Mag Jaśnie Wielmożnego Króla Thindilla I - władcy Królestwa Vallheru. Siedziałem w mojej astralnej wieży, studiując stare runy, gdy nagle usłyszałem cichy głos - Twój głos...\"<br />- \"Mój?\" - pytasz zdziwiniony i lekko przestrzaszony - \"Przecież nic nie mówiłem.\"<br />- \"Tak - Twój wewnętrzny głos - zew Twojego ducha. Jesteś żądny przygód i nie ukryjesz tego przed moimi wieszczymi czarami.\"<br />Nagle głos maga stężał i stał się bardzo donośny - \"Czy chcesz zagłębić się w królestwo magii i miecza? Czy chcesz zostać jednym z bohaterów pokonując straszliwe bestie, a nawet samego Astralnego Strażnika? Być może to Ty jesteś owym śmiałkiem, o którym opowiadają przepowiednie zapisane na starożytnych zwojach - bohater, który wprowadzi świat Vallheru w kolejną burzliwą erę.\" - słowa starego maga brzmią dostojnie i zagadkowo. Wciąż jesteś oszołomiony pokazem tak wielkiej i niespotykanej mocy. Cały drżysz, na myśl o tym co stanie się za chwilę. Jednak czai się w Tobie także iskierka awanturnika, poszukiwacza przygód, żądnego sławy obieżyświata.<br />- \"Widzę, że moje słowa mocno zaintrygowały Cię.\" - uśmiechnął się krzywo, lekko znudzony mag, odgadując Twoje skryte pragnienia.<br />- \"Jeśli masz na tyle odwagi, aby stawić czoła surowemu i niebezpiecznemu światu podążaj za mną.\" - to mówiąc czarodziej odwrócił się i wkroczył w elektryczną sferę, po czym zniknął, wśród ławicy licznych iskier. Pozostałeś sam, stojąc niepewny, walcząc jednocześnie ze strachem i ciekawością. Czy masz odwagę by wkroczyć w nowy nieznany świat...?");
    define("NICK", "Pseudonim:");
    define("CONF_EMAIL", "Potwierdź email:");
    define("REFERRAL_ID", "ID Polecającego:");
    define("IF_NO_ID", "Jeżeli nie jesteś czyimś poleconym, to pole jest puste.");
    define("REGISTER2", "Zarejestruj");
    define("SHORT_RULES", "Krótki spis zasad w grze:");
    define("T_LANG", "Wybierz język gry:");
    define("RULE1", "W grze obowiązuje netykieta - w wielkim skrócie - nie rób drugiemu co tobie nie miłe.");
    define("RULE2", "Wielokrotne ataki na jednego gracza w ciągu kilku minut - czyli zwykłe nękanie - są karane.");
    define("RULE3", "Wykorzystywanie błędów w grze do zdobycia przewagi nad innymi kończy się najczęściej skasowaniem postaci. Natomiast pomoc w ich znalezieniu może zostać nagrodzona przyznaniem specjalnej rangi.");
    define("RULE4", "W sprawie jakichkolwiek naruszeń prawa możesz zgłaszać to do książąt - oni najczęściej również wymierzają kary.");
    define("RULE5", "Jeżeli nie zgadzasz się z karą, możesz zawsze decyzję zaskarżyć do Sądu Najwyższego ".$gamename." - jego siedziba znajduje się w każdym mieście.");
    define("RULE6", "Zabrania się posiadania więcej niż 1 konta na osobę.");
    define("RULE7", "Więcej informacji na ten temat znajdziesz <a href=\"index.php?step=rules\">tutaj</a>.");
    define("RULE8", "Pamiętaj, jeżeli chcesz grać w tę grę, musisz zaakceptować zasady w niej obowiązujące.");
}
    else
{
    define("EMPTY_FIELDS", "Musisz wypełnić wszystkie pola.");
    define("BAD_EMAIL", "Nieprawidłowy adres email.");
    define("BAD_NICK", "Ktoś już wybrał taki pseudonim.");
    define("EMAIL_HAVE", "Ktoś już posiada taki adres mailowy.");
    define("EMAIL_MISS", "Zły adres email.");
    define("WELCOME_TO", "Witaj w");
    define("YOUR_LINK", ". Twój link aktywacyjny to:");
    define("ACTIV_LINK", "/aktywacja.php?kod=");
    define("NICE_PLAYING", "\n Życzę miłej zabawy w");
    define("SUBJECT", "Rejestracja na");
    define("EMAIL_ERROR", "Wiadomość nie została wysłana. Błąd:<br /> ");
    define("REGISTER_FAILED", "Nie mogę zarejestrować.");
    define("REGISTER_SUCCESS", "Jesteś już zarejestrowany. Sprawdź swoją skrzynkę pocztową.");
}
?>
