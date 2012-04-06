<?php
/**
 *   File functions:
 *   Polish language for outposts
 *
 *   @name                 : outposts.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 05.04.2012
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

define("NOT_IN_CITY", "Nie masz prawa tutaj przebywać");
define("NOTHING", "brak");
define("I_POWER", " (siła: ");
define("MONSTER", "bestię");
define("VETERAN", "weterana");
define("AND_LOST", "Dodatkowo ");
define("T_LOST", " traci ");
define("GC", "sz");
define("NO_OUTPOST", "Nie masz dostępu do strażnicy! Za 500 sztuk złota możesz wykupić kawałek ziemi pod nią. Więc jak, chcesz kupić?");
define("A_BACK", "Menu");
define("ERROR", "Zapomnij o tym!");
define("YOU_DEAD", "Ponieważ jesteś martwy, nie możesz korzystać ze strażnicy.");

if (!isset($_GET["view"]))
{
    define("OUT_INFO", "Witaj w strażnicy. Jeżeli pierwszy raz tu jesteś, powinieneś przeczytać instrukcję.");
    define("A_MY", "Moja Strażnica");
    define("A_TAX", "Ściągnij daniny z wiosek");
    define("A_SHOP", "Rozbudowa oraz zaciąg armii");
    define("A_GOLD", "Skarbiec");
    define("A_ATTACK", "Atakuj Strażnicę");
    define("A_LIST", "Lista Strażnic");
    define("A_GUIDE", "Instrukcja Strażnicy");
}

if (isset ($_GET['action']) && $_GET['action'] == 'buy') 
{
    define("YOU_BUY", "Kupiłeś nieco ziemii! Kliknij <a href=\"outposts.php\">tutaj</a> aby wrócić.");
    define("NO_MONEY", "Nie masz wystarczająco dużo pieniędzy aby zakupić ziemię pod strażnicę.");
    define("GO_TO", "Możesz już udać się to swojej strażnicy! Kliknij <a href=\"outposts.php\">tutaj</a> aby wrócić.");
}

if (isset ($_GET['view']) && $_GET['view'] == 'gold') 
{
    define("GOLD_INFO", "Tutaj możesz przekazać złoto od siebie do strażnicy. Przelicznik w przypadku wybrania sztuk złota ze strażnicy jest 2:1 (czyli za 2 sztuki złota ze strażnicy dostajesz 1 sztukę złota do ręki), natomiast w przypadku dotowania strażnicy przelicznik wynosi 1:1. Obecnie w strażnicy posiadasz");
    define("GOLD_COINS", "sztuk złota");
    define("A_TAKE", "Zabierz");
    define("A_ADD", "Dodaj");
    define("FROM_OUT", "sztuk złota ze strażnicy");
    define("TO_OUT", "sztuk złota do strażnicy");
    if (isset ($_GET['step']) && $_GET['step'] == 'player') 
    {
        define("HOW_MANY", "Podaj ile sztuk złota chcesz zamienić!");
        define("NO_MONEY", "Nie masz tyle sztuk złota w strażnicy!");
        define("YOU_CHANGE", "Zamieniłeś <b>");
        define("GOLD_ON", "</b> sztuk złota ze strażnicy na <b>");
        define("GOLD_ON2", "</b> sztuk złota do ręki.");
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'outpost') 
    {
        define("HOW_MANY", "Podaj ile sztuk złota chcesz dodać do strażnicy!");
        define("NO_MONEY", "Nie masz tyle sztuk złota");
        define("YOU_ADD", "Dodałeś <b>");
        define("GOLD_TO", "</b> sztuk złota do strażnicy.");
    }
}

if (isset($_GET['view']) && $_GET['view'] == 'veterans') 
{
    define("NO_VETERANS", "Nie masz weteranów w strażnicy!");
    define("NO_VETERAN", "Nie wybrałeś weterana!");
    define("NOT_YOUR", "To nie jest twój weteran!");
    define("YOU_ADD", "Dodałeś weteranowi ekwipunek: ");
    define("V_NAME", "Imię");
    define("V_WEP", "Broń");
    define("V_ARMOR", "Zbroja");
    define("V_HELMET", "Hełm");
    define("V_LEGS", "Nagolenniki");
    define("V_ATTACK", "Atak");
    define("V_DEFENSE", "Obrona");
    define("I_POWER2", "Siła");
    define("A_MODIFY", "Modyfikuj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'myoutpost') 
{
    define("WELCOME", "Witaj w swojej Strażnicy,");
    define("OUT_INFO", "Informacje o Strażnicy");
    define("LAND_A", "Liczba ziem");
    define("AP", "Punktów Ataku");
    define("T_SOLDIERS", "Piechoty");
    define("T_ARCHERS", "Łuczników");
    define("T_CATAPULTS", "Machin");
    define("T_FORTS", "Fortyfikacji");
    define("T_LAIRS", "Legowiska bestii");
    define("T_MONSTERS", "Bestie");
    define("T_BARRACKS", "Kwater weteranów");
    define("T_VETERANS", "Weteranów");
    define("T_FATIGUE", "Zmęczenie Armii");
    define("T_MORALE", "Morale Armii");
    define("T_COST", "Koszt");
    define("T_BONUS", "Premie dowódcy");
    define("T_ATTACK", "Siła ataku");
    define("T_DEFENSE", "Obrona");
    define("T_TAX", "Wpływy z danin");
    define("T_LOSSES", "Straty w bitwie");
    define("T_COST_B", "Koszt utrzymania");
    define("MORALE1", "Bojowe");
    define("MORALE2", "Neutralne");
    define("MORALE3", "Bunt");
    define("T_FREE", "wolne");
    define("T_POWER", "siła");
    define("U_DEFENSE", "obrona");
    define("T_MAX", "maksymalnie");
    define("T_COST_G", "sztuk złota na reset");
    define("A_ADD", "Dodaj premię");
    define("T_GOLD_COINS", "Sztuk złota");
    if (isset($_GET['step']) && $_GET['step'] == 'add') 
    {
        define("NO_BONUS", "Nie możesz podnieść jakiejkolwiek premii");
        define("MAX_LEVEL", "Osiągnąłeś już maksymalny poziom tej premii");
        define("YOU_ADD", "Premia dodana. <a href=\"outposts.php?view=myoutpost\">Odśwież</a>");
    }
}

if (isset ($_GET['view']) && $_GET['view'] == 'taxes') 
{
    define("TAX_INFO", "Tutaj możesz zbierać daninę z wiosek otaczających twoją strażnicę. Im więcej posiadasz piechoty oraz łuczników, tym większy obszar mogą oni zwiedzić i zebrać więcej sztuk złota. Każda taka wyprawa pochłania 1 Punkt Ataku.");
    define("A_SEND", "Wyślij");
    define("SOLDIERS", "żołnierzy");
    define("TIMES", "razy do wiosek");
    define("NO_EN_AP", "Nie masz tylu Punktów Ataku aby zbierać podatki");
    define("NO_SOLDIERS", "Nie masz żołnierzy aby zbierali podatki!");
    define("HOW_MANY", "Podaj ile razy chcesz wysłać żołnierzy!");
    define("NO_AP", "Nie masz tyle Punktów Ataku!");
    define("YOU_ARMY", "Twoi żołnierze wyruszyli ");
    define("TIMES_FOR", " razy na zbieranie danin z wiosek i zebrali w ten sposób ");
    define("GOLD_COINS", " sztuk złota.");
}

if (isset ($_GET['view']) && $_GET['view'] == 'shop') 
{
    define("SHOP_INFO", "Witaj w sklepie w Strażnicy! Kupisz tutaj żołnierzy, fortyfikacje oraz machiny oblężnicze, możesz również zwiększyć rozmiar swojej Strażnicy. Obecnie możesz jeszcze dokupić");
    define("SHOP_INFO2", "oraz");
    define("SHOP_INFO3", ".<br/>Posiadasz: ");
    define("OUTPOST_DEVELOPMENT", "Rozbudowa Strażnicy");
    define("LEVEL_INFO", "Powiększ rozmiar Strażnicy");
    define("NO_LEVEL_INFO", "Nie stać Cię na powiększenie rozmiaru Strażnicy. Potrzebujesz ");
    define("LAIR", "Legowiska");
    define("LAIR_INFO", "Dokup Legowiska Bestii");
    define("NO_LAIR_INFO", "Nie stać Cię na dokupienie Legowisk Bestii lub nie ma na nie miejsca. Do budowy potrzebujesz ");
    define("BARRACK", "Kwatery");
    define("BARRACK_INFO", "Dokup Kwatery Weteranów");
    define("NO_BARRACK_INFO", "Nie stać Cię na dokupienie Kwater Weteranów (złoto, meteoryt, adamantium) lub nie ma na nie miejsca.");
    define("A_BUY", "Kup");
    define("A_REFRESH", "Odśwież");
    define("ARMY_DEVELOPMENT", "Rozbudowa armii");
    define("B_SOLDIERS", "piechurów (+3 atak, +1 obrona, 25 sztuk złota jeden). (Dostępnych");
    define("B_ARCHERS", "łuczników (+1 atak, +3 obrona, 25 sztuk złota jeden). (Dostępnych");
    define("B_MACHINES", "machin oblężniczych (+3 atak, 35 sztuk złota jedna).  (Dostępnych");
    define("B_FORTS", "fortyfikacji (+3 obrona, 35 sztuk złota jedna). (Dostępnych");
    define("GOLD_COINS", " sztuk złota");
    define("PLATINUM_PIECES", " sztuk(i) mithrilu");
    define("PINE_PIECES", " sosny");
    define("CRYSTAL_PIECES", " kryształów");
    define("ADAMANTIUM_PIECES", " adamantium");
    define("METEOR_PIECES", " meteorytu");
    define("MANAGEMENT", "Zarządzaj weteranami i bestiami");
    define("NO_LAIR", "Nie masz wolnych legowisk na kolejne bestie.");
    define("NO_BARRACKS", "Nie masz wolnych kwater dla kolejnych weteranów.");
    define("A_ADD", "Dodaj");
    define("U_ATTACK", "Atak");
    define("U_DEFENSE", "Obrona");
    define("FOR_A", "do strażnicy (2000 sztuk złota jeden)");
    define("V_NAME2", "weterana o imieniu");
    define("I_POWER2", "Siła");
    define("V_ARMOR", "Zbroja");
    define("V_HELMET", "Hełm");
    define("V_LEGS", "Nagolenniki");
    define("NO_MONEY", "Nie stać Cię na to.");
    define("SOLDIERS", "piechurów");
    define("ARCHERS", "łuczników");
    define("FORTS", "fortyfikacji");
    define("MACHINES", "machin oblężniczych");
    define("BUY_ALL", "Kup wszystko");
    if (isset($_GET['buy']) && ($_GET['buy'] != 's' && $_GET['buy'] != 'f' && $_GET['buy'] != 'r' && $_GET['buy'] != 'v')) 
    {
        define("HOW_MANY", "Podaj ile wojska chcesz kupić!");
    }
    if (isset($_GET['buy']) && $_GET['buy'] == 'v') 
    {
        define("NO_NAME", "Podaj imię weterana");
        define("NAME_TAKEN", "Któryś z twoich weteranów posiada już takie imię!");
        define("YOU_ADD", "Dodałeś weterana: <b>");
        define("FOR_A2", "</b> wydając 2000 sztuk złota. Posiada: ");
    }
    if (isset($_GET['buy']) && $_GET['buy'] == 'r') 
    {
        define("MAX_BAR", "Nie możesz dokupić nowej kwatery ponieważ masz już maksymalną ilość!");
        define("YOU_ADD", "Masz teraz w Strażnicy");
        define("YOU_PAID", "Kwater Weteranów. Zapłaciłeś");
        define("YOU_NEED", "Potrzebujesz");
    }
    if (isset($_GET['buy']) && $_GET['buy'] == 'm') 
    {
        define("NOT_YOUR", "To nie twój chowaniec!");
        define("YOU_ADD", "Dodałeś bestię: <b>");
        define("WITH_P", "</b> o sile ");
        define("AND_D", " i obronie ");
        define("IT_COST", ". Wydałeś na to 2000 sztuk złota.");
    }
    if (isset($_GET['buy']) && $_GET['buy'] == 'f') 
    {
        define("MAX_LAIR", "Nie możesz dokupić nowego legowiska ponieważ masz już maksymalną ilość!");
        define("YOU_ADD", "Masz teraz w Strażnicy");
        define("YOU_PAID", "Legowisk Bestii. Zapłaciłeś");
        define("YOU_NEED", "Potrzebujesz");
    }
    if (isset ($_GET['buy']) && $_GET['buy'] == 's') 
    {
        define("YOU_ADD", "Powiększyłeś rozmiar swojej Strażnicy do poziomu");
        define("YOU_PAID", "Zapłaciłeś");
        define("YOU_NEED", "Potrzebujesz");
    }
    if (isset($_GET['buy']) && $_GET['buy'] == 'all')
    {
        define("YOU_BUY", "Dokupiłeś do Strażnicy:<br /><b>");
        define("YOU_SPEND", "<br />Wydałeś na to <b> ");
        define("NO_SOLDIERS", "Nie ma tylu piechurów do kupienia!");
        define("NO_ARCHERS", "Nie ma tylu łuczników do kupienia!");
        define("NO_FORTS", "Nie ma tyle fortyfikacji do kupienia!");
        define("NO_MACHINES", "Nie ma tyle machin oblężniczych do kupienia!");
        define("TOO_MUCH", "Nie możesz kupić tak wielu ");
        define("ENLARGE", " do Strażnicy. Zwiększ jej rozmiar, zanim zakupisz więcej ");
        define("YOU_ADD", "Dodałeś <b>");
        define("TO_OUTPOST", " do Strażnicy za <b>");
    }
}

if (isset ($_GET['view']) && $_GET['view'] == 'listing') 
{
    define("NO_SLEVEL", "Podaj początkowy poziom!");
    define("A_SHOW", "Pokaż");
    define("FROM_L", "strażnice od rozmiaru");
    define("TO_L", "do");
    define("OUT_ID", "ID Strażnicy");
    define("OUT_SIZE", "Rozmiar strażnicy");
    define("OUT_OWNER", "Właściciel");
    define("OUT_ATTACK", "Atakować?");
    define("A_ATTACK", "Atak");
}

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
    define("MACHINES", "katapult");
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
    define("INFO1a", "Podstawowym zadaniem w grze jest posiadanie największej strażnicy i najsilniejszej armii Podczas każdego resetu, dostajesz 2 punkty ataku ale również co reset musisz opłacać swoje wojska. Koszty mogą być znacznie niższe jeżeli inwestujesz odpowiednio w umiejętność Dowodzenie. Możesz zrobić co chcesz z tymi punktami.");
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
