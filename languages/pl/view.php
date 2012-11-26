<?php
/**
 *   File functions:
 *   Polish language for view other players
 *
 *   @name                 : view.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 26.11.2012
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
define("NO_PLAYER", "Nie ma takiego gracza");
define("GG_NUMBER", "Komunikator ");
define("HAVE_IMMU", "Posiada immunitet");
define("G_MALE", "Mężczyzna");
define("G_FEMALE", "Kobieta");
define("T_GENDER", "Płeć");
define("T_DEITY", "Wyznanie");
define("S_LIVE", "Żywy");
define("S_DEAD", "Martwy");
define("T_CLAN", "Klan");
define("T_CLAN_RANK", "Ranga w klanie");
define("NOTHING", "brak");
define("A_ATTACK", "Atak");
define("A_WRITE_PM", "Napisz wiadomość");
define("A_STEAL", "Kradzież kieszonkowa");
define("A_STEAL2", "Ukradnij astralny komponent");
define("PLAYER_IP", "IP gracza: ");
define("T_RANK", "Ranga");
define("T_LOCATION", "Lokacja");
define("T_AGE", "Wiek");
define("T_RACE", "Rasa");
define("T_CLASS2", "Klasa");
define("T_LEVEL", "Poziom");
define("T_STATUS", "Status");
define("T_MAX_HP", "Maksymalne PŻ");
define("T_FIGHTS", "Wyniki");
define("T_LAST_KILL", "Ostatnio zabity/a");
define("T_REFS", "Vallary");
define("T_PROFILE", "Profil");
define("T_OPTIONS", "Opcje");
define("T_LANG", "Główny język");
define("T_SEC_LANG", "Dodatkowy język: ");
define("T_FREEZED", "Konto zablokowane");
$arrTitle = array('Opcje konta', 'Dodaj Plotkę', 'Dodaj Wieść', 'Alchemik', 'Pracownia alchemiczna',
                  'Ekwipunek', 'Panel Administracyjny', 'Dystrybucja AP', 'Płatnerz', 'Bank', 'Arena Walk', 'Fleczer',
                  'Chat', 'Altara', 'Polana Chowańców', 'Księga czarów', 'Wybierz wyznanie', 'Poszukiwania', 'Farma',
                  'Forum',  'Labirynt', 'Pomoc', 'Rynek ziół', 'Galeria Bohaterów', 'Szpital', 
                  'Domy', 'Rynek z przedmiotami', 'Lochy', 'Wybierz klasę', 'Kuźnia', 
                  'Oczyszczanie miasta', 'Dziennik', 'Tartak', 'Poczta', 'Rynek', 
                  'Lista mieszkańców', 'Rynek z miksturami', 'Posągi', 'Miejskie Plotki', 'Notatnik',
                  'Strażnica', 'Rynek minerałów', 'Portal', 'Wybierz rasę', 'Hala zgromadzeń', 'Odpoczynek', 
                  'Panel Sędziego', 'Statystyki', 'Świątynia', 'Forum klanu', 'Zegar miejski', 'Szkolenie', 'Stajnie',
                  'Zbrojownia klanu', 'Klany', 'Magazyn klanu', 'Zobacz', 'Zbrojmistrz', 'Magiczna wieża', 'Bogactwa', 
                  'Gmach sądu', 'Biblioteka', 'Magazyn Królewski', 'Redakcja gazety', 'Mapa', 'Centrum poleconych', 
                  'Góry Kazad-nar', 'Las Avatiel', 'Wyrąb', 'Aleja zasłużonych', 'Astralny skarbiec', 'Astralny rynek', 'Spis książąt',
                  'Jubiler', 'Rynek jubilerski', 'Kopalnia', 'Kopalnie', 'Huta', 'Astralny plan', 'Warsztat jubilerski',
		  'Rynek z łupami', 'Galeria Machin', 'Propozycje', 'Gildia Łowców', 'Gildia Rzemieślników', 'Cześnik', 'Aula Gladiatorów',
		  'Prefektura Gwardii', 'Pokój w karczmie', 'Złodziejska Spelunka', 'Przygoda', 'Rynek chowańców', 'Kronika', 'Drużyna');
if ($view -> location != 'Ardulith')
{
    $arrTitle2 = $arrTitle;
    $arrTitle2[11] = 'Łucznik';
    $arrTitle2[12] = 'Karczma';
    $arrTitle2[13] = $city1;
    $arrTitle2[67] = 'Las Avantiel';
    $arrTitle2[72] = 'Sala audiencyjna';
}
    else
{
    $arrTitle2 = $arrTitle;
    $arrTitle2[11] = 'Łucznik';
    $arrTitle2[12] = 'Karczma';
    $arrTitle2[13] = $city2;
    $arrTitle2[20] = 'Avan Tirith';
    $arrTitle2[30] = 'Polana drwali';
    $arrTitle2[44] = 'Brzoza przeznaczenia';
    $arrTitle2[62] = 'Leśny skład';
    $arrTitle2[67] = 'Las Avantiel';
    $arrTitle2[72] = 'Korzeń przeznaczenia';
}
$intKey = array_search($view -> page, $arrTitle);
$strViewpage = $arrTitle2[$intKey];

if (isset ($_GET['steal']) || isset($_GET['spy'])) 
{
    define("SAME_CLAN", "Nie możesz okradać członków tego samego klanu!");
    define("ACCOUNT_FREEZED", "Nie możesz okradać tego konta, ponieważ jest ono zablokowane.");
    define("NO_PLAYER2", "Zdecyduj się kogo chcesz okraść!");
    define("NO_CRIME", "Nie możesz próbować kradzieży kieszonkowej, ponieważ niedawno próbowałeś już swoich sił!");
    define("BAD_LOCATION", "Nie możesz okradać gracza, który nie jest w tej samej lokacji co ty!");
    define("IS_DEAD", "Nie możesz okradać martwych!");
    define("YOU_DEAD", "Nie możesz okradać innych ponieważ jesteś martwy");
    define("YOU_IMMU", "Nie możesz okradać innych graczy ponieważ posiadasz immunitet");
    define("HE_IMMU", "Nie możesz okradać gracza, który posiada immunitet");
    define("VERDICT", "Próba okradzenia mieszkańca Vallheru");
    define("YOU_IN_JAIL", "Zostałeś wtrącony do więzienia na 1 dzień za próbę okradzenia gracza. Możesz wyjść z więzienia za kaucją: ");
    define("YOU_CATCH", "Kiedy wędrowałeś sobie spokojnie poczułeś nagle że ktoś grzebie ci przy sakiewce. To ");
    define("L_ID", "</a></b>, ID <b>");
    define("YOU_CATCH2", "</b>. Szybko pochwyciłeś jego rękę i przekazałeś strażnikom.");
    define("YOU_JAILED", "Kiedy sięgałeś do sakiewki mieszkańca, ten zauważył twój ruch. Szybko złapał ciebie za nadgarstek i wezwał straż. Obrót spraw tak ciebie zaskoczył iż zapomniałeś nawet zareagować w jakiś sposób. I tak oto znalazłeś się w lochach!");
    define("WHEN_YOU", "Kiedy siedziałeś sobie spokojnie w celi poczułeś nagle że twój współokator grzebie ci przy sakiewce. To ");
    define("YOU_TRY_IN", "Próbowałeś okraść współwięźnia ale niestety nie udało ci się");
    define("NOT_CATCH", "</b>! Próbowałeś go złapać, niestety wyrwał ci się wraz z twoimi ");
    define("GOLD_COINS", " sztukami złota.");
    define("GOLD_COINS2", " sztuk złota.");
    define("WHEN_YOU2", "Kiedy sięgałeś do sakiewki mieszkańca, ten zauważył twój ruch. Szybko złapał Cię za nadgarstek, na szczęście zdążyłeś się wyswobodzić i uciec wraz z ");
    define("WHEN_YOU21", " sztukami złota. Niestety ów mieszkaniec zapamiętał Cię...");
    define("EMPTY_BAG", "! Próbowałeś go złapać, niestety wyrwał Ci się. Ciekawe czy mocno się zdziwi kiedy zauważy, że nie miałeś w sakiewce ani grosza.");
    define("EMPTY_BAG2", "Kiedy sięgałeś do sakiewki mieszkańca, ten zauważył twój ruch. Szybko złapał Cię za nadgarstek, na szczęście zdążyłeś się wyswobodzić i uciec. Kiedy zajrzałeś do sakiewki, omal krew cię nie zalała - tyle kłopotów z powodu pustej sakiewki!");
    define("YOU_CRIME", "Wędrowałeś sobie spokojnie, kiedy pewne niejasne przeczucie powiedziało ci, że coś jest nie w porządku. Przyglądając się uważnie sobie, zauważasz iż brakuje ci sakiewki wraz z ");
    define("YOU_CRIME2", " sztukami złota. Ktoś cię okradł!");
    define("SUCCESS", "Ostrożnie manipulując przy sakiewce ofiary, udało ci się nieco ją odchudzić. Nieprzyuważony przez nikogo spokojnie oddalasz się. Dopiero po pewnym czasie dokładnie przeliczasz swój zarobek - to ");
    define("EMPTY_BAG3", "Wędrowałeś sobie spokojnie, kiedy pewne niejasne przeczucie powiedziało ci, że coś jest nie w porządku. Przyglądając się uważnie sobie, zauważasz iż brakuje ci sakiewki. Ktoś cię okradł! Zastanawiasz się, na co mu była pusta sakiewka");
    define("EMPTY_BAG4", "Ostrożnie zabierając sakiewkę ofierze, odchodzisz. Jednak wydaje ci się ona podejrzanie lekka. Kiedy przyglądasz się jej, zauważasz że jest pusta. To chyba nie jest twój najlepszy dzień...");
    define("TOO_YOUNG", "Nie możesz okradać nowych mieszkańców, posiadających ochronę.");
    define("R_INT", "inteligencji");
    define("R_AGI", "zręczności");
}

if (isset($_GET['steal_astral']))
{
    define("NO_PLAYER2", "Zdecyduj się kogo chcesz okraść!");
    define("NO_CRIME", "Nie możesz próbować kradzieży astralnego komponentu, ponieważ niedawno próbowałeś już swoich sił!");
    define("YOU_DEAD", "Nie możesz okradać innych ponieważ jesteś martwy");
    define("SAME_CLAN", "Nie możesz okradać członków tego samego klanu!");
    define("VERDICT", "Próba kradzieży astralnego komponentu");
    define("L_REASON", "Zostałeś wtrącony do więzienia na 1 dzień za próbę kradzieży astralnego komponentu. Możesz wyjść z więzienia za kaucją: ");
    define("C_CACHED", "Kiedy próbowałeś dostać się do astralnego skarbca, zauważyli ciebie strażnicy. Błyskawicznie otoczyli i zmusili do poddania. I tak oto znalazłeś się w lochach.");
    define("NO_AMOUNT", "Szczęśliwie pokonałeś wszelkie przeszkody i niezauważony zbliżasz się do astralnego skarbca. Kiedy wchodzisz do środka i rozglądasz się w około masz ochotę zawyć ze złości. Nic tutaj nie ma! Tyle nerwów na marne. Zdenerwowany opuszczasz skarbiec.");
    define("SUCCESFULL", "Ostrożnie, mijając wszystkie zastawione pułapki wchodzisz do astralnego skarbca. Twoim oczom ukazuje się długo oczekiwany widok - w około leżą różne astralne komponenty. Nie mając za bardzo czasu przyjrzeć się im dokładnie, chwytasz szybko jeden z nich i opuszczasz skarbiec. To twój szczęśliwy dzień! Ukradłeś");
    define("L_CACHED", "Mieszkaniec ");
    define("L_CACHED2", ", ID ");
    define("L_CACHED3", " próbował ukraść astralny komponent z twojej skrytki. Na szczęście został pojmany przez strażników.");
    define("ASTRAL_GONE", "Kiedy przeglądałeś swoje astralne komponenty, zauważyłeś że jednego brakuje. Ktoś prawdopodobnie ciebie okradł! Ze skarbca zniknął");
    define("COMP1", "Ząb Glabrezu");
    define("COMP2", "Ognisty pył");
    define("COMP3", "Pazur Zgłębiczarta");
    define("COMP4", "Łuska Skorpendry");
    define("COMP5", "Macka Krakena");
    define("COMP6", "Piorun Tytana");
    define("COMP7", "Żebro Licha");
    define("CONST1", "Astralny komponent");
    define("CONST2", "Gwiezdny portal");
    define("CONST3", "Świetlny obelisk");
    define("CONST4", "Płomienny znicz");
    define("CONST5", "Srebrzysta fontanna");
    define("POTION1", "Magiczna esensja");
    define("POTION2", "Gwiezdna maść");
    define("POTION3", "Eliksir Illuminati");
    define("POTION4", "Astralne medium");
    define("POTION5", "Magiczny absynt");
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
    define("PIECE", " kawałek mapy/planu <b>");
    define("COMPONENT", " kompletny komponent <b>");
}
?>
