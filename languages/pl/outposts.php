<?php
/**
 *   File functions:
 *   Polish language for outposts
 *
 *   @name                 : outposts.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 06.08.2012
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

define("NOTHING", "brak");
define("I_POWER", " (siła: ");
define("MONSTER", "bestię");
define("VETERAN", "weterana");
define("AND_LOST", "Dodatkowo ");
define("T_LOST", " traci");
define("GC", "sz");
define("NO_OUTPOST", "Nie masz dostępu do strażnicy! Za 500 sztuk złota możesz wykupić kawałek ziemi pod nią. Więc jak, chcesz kupić?");
define("A_BACK", "Menu");
define("ERROR", "Zapomnij o tym!");
define("YOU_DEAD", "Ponieważ jesteś martwy, nie możesz korzystać ze strażnicy.");

if (isset ($_GET['view']) && $_GET['view'] == 'battle') 
{
    define("BATTLE_INFO", "Witaj w pokoju narad. Wpisz ID Strażnicy bądź ID jej właściciela oraz ile razy ma nastąpić atak.");
    define("OUT_ID", "ID Strażnicy");
    define("AMOUNT_A", "Ilość ataków");
    define("A_ATTACK", "Atak");
    define("TOO_FAT", "Twoja armia jest zbyt zmęczona by atakować!");
    define("NO_AP", "Nie masz wystarczającej ilości punktów ataku.");
    define("NO_ARMY", "Nie masz wojsk aby atakować innego gracza!");
    define("NO_ID", "Podaj id strażnicy do ataku");
    define("NO_OUT", "Nie ma takiej strażnicy.");
    define("ITS_YOUR", "Nie możesz zaatakować własnej Strażnicy.");
    define("TOO_MUCH_A", "Jedna strażnica może być zaatakowana tylko 3 razy na reset!");
    define("TOO_FAT2", "Twoja armia jest zbyt zmęczona aby mogła atakować dalej!");
    define("SOLDIERS", "piechurów");
    define("ARCHERS", "łuczników");
    define("MACHINES", "machin");
    define("FORTS", "fortyfikacji");
    define("YOU_ATTACK", "<br />Atakujesz strażnicę gracza ");
    define("AND_WIN", " i wygrywasz!<br />(Siła ataku: ");
    define("DEFENSE", " Siła obrony: ");
    define("YOU_GAIN", ")<br />Zdobywasz ");
    define("GOLD_COINS", " sztuk złota oraz ");
    define("IN_LEADER", " poziomu w umiejętności Dowodzenie. Tracisz:<br />");
    define("ENEMY_LOST", "Przeciwnik traci:<br />");
    define("L_PLAYER", "Gracz");
    define("L_ID", ", ID ");
    define("HE_ATTACK", " zaatakował twoją strażnicę i wygrał. Tracisz ");
    define("L_GOLD", " sztuk złota, ");
    define("L_AND", " oraz ");
    define("BUT_FAIL", " lecz niestety przegrywasz!<br />(Siła ataku: ");
    define("HE_ATTACK2", " zaatakował twoją strażnicę i przegrał. Tracisz ");
    define("ATTACK_LIMIT", "<br />Nie możesz więcej atakować tej strażnicy! Musisz poczekać do kolejnego resetu.<br />");
}

if (isset ($_GET['view']) && $_GET['view'] == 'guide') 
{
    define("INFO1", "Podstawy");
    define("INFO1a", "Podstawowym zadaniem w grze jest posiadanie największej strażnicy i najsilniejszej armii. Podczas każdego resetu, dostajesz 2 punkty ataku, ale również codziennie musisz opłacać swoje wojska. Koszty mogą być znacznie niższe jeżeli inwestujesz odpowiednio w umiejętność Dowodzenie.");
    define("INFO2", "Moja strażnica");
    define("INFO2a", "To centrum zarządzania twoją strażnicą - tutaj znajdziesz wszystkie informacje na jej temat - liczbę posiadanych wojsk, machin oraz fortyfikacji i budynków specjalnych (tylko wtedy jeżeli twoja strażnica osiągnie odpowiedni rozmiar). Oprócz tego tutaj również znajdują się informacje na temat premii jakie posiadasz z umiejętności Dowodzenie. Każdy punkt w tej umiejętności pozwala podnieść jedną premię o 1 % do maksymalnego poziomu 15 % w danej premii. Kiedy będziesz miał możliwość podniesienia jakiejś premii, obok niej, pojawi się link informujący o tym.<br />Oprócz tego tutaj również możesz dozbrajać swoich weteranów (jeżeli takowych posiadasz). Aby to zrobić, wystarczy kliknąć na imię danego weterana.");
    define("INFO3", "Zbieranie danin");
    define("INFO3a", "Daniny są bardzo dobrym sposobem na zarobienie pieniędzy. Wydobycie zabiera jeden Punkt Ataku. Za każdym razem, kiedy zbierasz daniny, dostajesz sztuki złota. Jego ilość zależy od liczby twoich piechurów oraz łuczników.");
    define("INFO4", "Rozbudowa oraz zaciąg armii");
    define("INFO4a", "To jest podstawowy sklep, gdzie możesz kupować żołnierzy, fortyfikacje, machiny, jednostki i budynki specjalne oraz rozbudowywać strażnicę. Wojsko dzieli się na 2 typy - bardziej ofensywne jednostki (piechurzy) oraz bardziej defensywne (łucznicy). Dodatkowo możesz kupować fortyfikacje które podniosą obronę twojej strażnicy oraz machiny oblężnicze które zwiększają twoje zdolności ataku na inne strażnice. Oprócz tego, jeżeli spełniasz odpowiednie warunki (masz odpowiedni rozmiar strażnicy) możesz dokupić specjalne budynki takie jak Legowisko Bestii czy Kwatera Weterana, które pozwolą ci rekrutować specjalne jednostki. Ale nie tylko budynki są potrzebne do tego celu. Potrzeba jeszcze sztuk złota do tego celu oraz: <br />- dla Bestii - musisz mieć przy najmniej jednego chowańca<br />- dla Weterana - musisz mieć przy najmniej jedną sztukę broni w plecaku<br />    Podczas wynajmowania Weteranów możesz wybrać ich uzbrojenie oraz opancerzenie - nie musisz wybierać wszystkiego na raz - w późniejszym okresie będziesz mógł go dozbroić.");
    define("INFO5", "Skarbiec");
    define("INFO5a", "W skarbcu strażnicy możesz wymienić sztuki złota jakie się w nim znajdują, na te które masz w ręku i na odwrót. W przypadku kiedy wpłacasz przelicznik wynosi 1:1 (czyli za 10 wpłaconych sztuk złota w strażnicy pojawia się 10 sztuk złota), natomiast w przypadku wypłaty przelicznik wynosi 2:1 (czyli za 10 wypłaconych ze strażnicy sztuk złota w ręku pojawia się 5 sztuk złota).");
    define("INFO6", "Atakowanie Strażnicy");
    define("INFO6a", "Atakowanie innych strażnic to również sposób na zdobywanie złota. Jednak podczas ataku musisz uważać - nawet jeżeli wygrasz, tracisz część ze swojego wojska (jest również mała szansa na stratę jednostek specjalnych). Ale ten który przegra zawsze gorzej na tym wychodzi. W nagrodę za udany atak dostajesz nie tylko złoto ale również podnosisz swój poziom w umiejętności Dowodzenie.");
}
?>
