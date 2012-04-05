<?php
/**
 *   File functions:
 *   Polish language for city
 *
 *   @name                 : city.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
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
// $Id:$

define("NO_CITY", "Nie znajdujesz się w mieście");

if ($player -> location == 'Altara')
{
    define("CITY_INFO", "Do miasta prowadzi tylko jedna brama, znajdująca się w zachodniej części miasta. Jest zamykana po zmierzchu, więc spóźnieni podróżni, którzy nie zdążą dotrzeć do ".$city1b." przed zachodem słońca, zmuszeni są spędzić noc w stajni poza murami. Chyba, że strażnicy będą na tyle uczynni, by zlitować się nad strudzonym podróżnym i wpuszczą go do środka. Główne ulice miasta są szerokie, wybrukowane i oświetlone. W pozostałe strach zaglądać. Wąskie, błotniste uliczki, pełne dziur, ciemnych zakamarków i wszelkiego rodzaju plugastwa, sprawiają, że trzeba być albo bardzo głupim, albo bardzo odważnym, by spacerować po nich, nawet w biały dzień. Miasto podzielone jest na 8 dzielnic, z których każda spełnia określoną rolę. Najważniejszym punktem miasta jest plac zamkowy, ulokowany we wschodniej części ".$city1b.". Na środku placu zbudowano Zamek, który od pokoleń zamieszkują Władcy ".$gamename.". Obok zamku ulokowana jest siedziba sądu i areszt miejski, w którym przetrzymywani są mieszkańcy, którzy złamali prawo, bądź narazili się komuś, komu narażać się nie powinni. Na wschód od Placu Zamkowego znajduje się Dzielnica Mieszkalna, w której zamożniejsi mieszkańcy ".$city1b." mają swoje posiadłości. Tam również znajduje się miejska biblioteka, posiadająca bogaty zbiór ksiąg i rękopisów dostępnych dla wszystkich. Północna część miasta składa się z trzech dzielnic, w których można zarówno nabyć ekwipunek potrzebny każdemu wojakowi, jak i napić się stęchniętego piwa w miejskiej karczmie. Tam również znajduje się szkoła i siedziba miejskiej gazety. Zachodnia część miasta jest mekką dla czarodziei, kapłanów i alchemików, ale również poszukiwacze przygód, których interesuje miejski labirynt, jak i zakochani mężczyźni, chcący kupić jakąś błyskotkę dla żony, czy kochanki często się w nią zapuszczają. Część południowa zamieszkiwana głównie przez plebs i biedniejszych kupców. Znajdują się w niej miejskie magazyny, rynek, pełniący funkcję bazaru, na którym znaleźć można niemal wszystko, jak i kilka pomniejszych budynków, gdzie zdesperowani szukają pracy. To raj dla wszelkiej maści rzezimieszków i złodziei.");
    define("BATTLE_FIELD", "Wojenne Pola");
    define("COMMUNITY", "Społeczność");
    define("VILLAGE", "Podgrodzie");
    define("WEST_SIDE", "Zachodnia Strona");
    define("HOUSES_SIDE", "Dzielnica mieszkalna");
    define("CASTLE", "Zamek");
    define("JOBS", "Praca");
    define("SOUTH_SIDE", "Dzielnica południowa");
    define("BATTLE_ARENA", "Arena Walk");
    define("ARMOR_SHOP", "Płatnerz");
    define("WEAPON_SHOP", "Zbrojmistrz");
    define("BOWS_SHOP", "Łucznik");
    define("OUTPOSTS", "Strażnica");
    define("LABYRYNTH", "Labirynt");
    define("WAREHOUSE", "Magazyn Królewski");
    define("MAGIC_TOWER", "Magiczna Wieża");
    define("TEMPLE", "Świątynia");
    define("ALCHEMY_SHOP", "Alchemik");
    define("CLEAN_CITY", "Oczyszczanie miasta");
    define("BLACKSMITH", "Kuźnia");
    define("ALCHEMY_MILL", "Pracownia alchemiczna");
    define("NEWS", "Plotki");
    define("FORUMS", "Forum");
    define("INN", "Karczma");
    define("PRIV_M", "Poczta");
    define("CLANS", "Klany");
    define("HOUSES", "Domy");
    define("PLAYERS_L", "Spis mieszkańców");
    define("MONUMENTS", "Posągi");
    define("HERO_VALL", "Galeria Bohaterów");
    define("MARKET", "Rynek");
    define("TRAVEL", "Stajnia");
    define("SCHOOL", "Szkoła");
    define("MINES", "Kopalnia");
    define("FARMS", "Farma");
    define("CORES", "Polana Chowańców");
    define("UPDATES", "Wieści");
    define("TIMER", "Zegar miejski");
    define("REFERR", "Poleceni");
    define("JAIL2", "Lochy");
    define("COURT", "Gmach Sądu");
    define("LIBRARY", "Biblioteka");
    define("PAPER", "Redakcja gazety");
    define("POLLS", "Hala zgromadzeń");
    define("SMELTER", "Huta");
    define("WELLEARNED", "Aleja Zasłużonych");
    define("STAFF_LIST", "Sala audiencyjna");
    define("JEWELLER_SHOP", "Jubiler");
    define("STAFF_QUEST", "Idąc ulicami ".$city1b." czujesz się tak, jakbyś po raz pierwszy oglądał jej mury. Wydawała się promienieć pięknem... Zaśmiałeś się w duchu-po tych upiornych labiryntach wszystko byłoby wspaniałe. Wdychane świeże, poranne powietrze przyjemnie łaskotało płuca wywiewając z nich resztki zatęchłego odoru ziemi i wilgoci. Tak... Właśnie tak czuje się Bohater!<br /><br />Co dalej, Bohaterze?<br />");
    define("SQ_BOX1", "kierujesz się do wieży Nubii, by oddać jej różdżkę");
    define("SQ_BOX2", "zatrzymujesz różdżkę dla siebie");
    define("STAFF_QUEST2", "Zmęczony i senny próbowałeś skupić myśli na tym, jak znaleźć drogę do domu, ale dalsza wędrówka wydała się wręcz niemożliwa. Karczma! Tak, to dobry wybór.<br />Gdy wszedłeś w jej progi, powitał cię chytry wzrok, grubego hobbita z namaszczeniem wycierającego drewniany kontuar. Zmierzył cię od stóp do głów i syknął przez zęby:<br />-Wynocha oberwańcu! Wykąp się, bo muchy padają od twojego zapaszku!<br />No tak... Po raz pierwszy od wyjścia z labiryntu spojrzałeś na siebie i widok nie był zachwycający: oberwane, brudne i cuchnące stęchlizną szatki, ziemia, pot i krew zakrzepłe na twarzy. Nie dziwota, że karczmarz nie chciał u siebie takiego gościa. Jednak jest coś, co zmiękczy jego miłość do higieny.<br />-Mam złoto. Chętnie kupię twoją wannę i pokój na kilka dni. Muszę porządnie wypocząć...<br />Gromki rechot hobbita przerwał twoje słowa:<br />-Złoto nie wynagrodzi mi wypłoszonych smrodem klientów! Musiałbyś mi dać coś cenniejszego, niż stu z nich!<br />-Coś cennego powiadasz...? Hmmm... A co dostanę w zamian za diamentową różdżkę...?<br /><br />Zdobywasz 10 000 sztuk złota");
    define("STAFF_QUEST1", "Choć zmęczenie szarpało w dół twe powieki powiedziałeś sobie, że jeszcze dziś musisz dokończyć to, co zacząłeś, dlatego kierujesz swe kroki do Północnej Wieży Nubii. Ciekawość nie daje ci spokoju- co raz to inne wizje nasuwa ci umysł i wyobraźnia, lecz to właśnie dzięki jej bogactwu droga mija szybko i sprawnie.<br />Po chwili stajesz przed drzwiami wieży i sięgasz do kołatki.<br />-Czekałam na ciebie - słyszysz za sobą. Znasz ten głos. Słyszałeś go wielokrotnie w labiryncie. Gdy odwracasz głowę, widzisz Nubię siedzącą u stóp posągu Anariel. Ich podobieństwo jest uderzające...! Teraz rozumiesz, co miał na myśli Neh nazywając ją żywą boginią. Cóż teraz powiedzieć? Stoisz oszołomiony, nie mogąc wykrztusić słowa.<br />-Wspaniale rozwiązałeś zagadkę. Byłam tam z tobą myślami i śledziłam każdy ruch. Tylu wojowników zginęło podczas tej misji... Byli tacy nierozważni... -westchnęła cicho, po czym w zamyśleniu chwyciła cię za rękę i poprowadziła za sobą do świątyni.");
    define("ITEM", "Różdżka Nubii");
}
    else
{
    define("CITY_INFO", "Miasto jest gajem ogromnych drzew, na gałęziach których znajdują się domy i pomieszczenia zbudowane za pomocą magii. Wygląd jest iście baśniowy. Kręte schody wokół konarów z misternie zdobionymi poręczami, piękne szałasy, pomosty łączące poszczególne drzewa i umożliwiające łatwe przemieszczanie się.");
    define("BATTLE_FIELD", "Święty kasztanowiec");
    define("COMMUNITY", "Północny sad");
    define("VILLAGE", "Stary buk");
    define("WEST_SIDE", "Zachodnie wierzby");
    define("HOUSES_SIDE", "Centralna polana");
    define("CASTLE", "Królewski Dąb");
    define("JOBS", "Południowy sad");
    define("SOUTH_SIDE", "Brama");
    define("BATTLE_ARENA", "Arena Walk");
    define("BOWS_SHOP", "Łucznik");
    define("LABYRYNTH", "Opuszczona siedziba czarownika");
    define("WAREHOUSE", "Leśny skład");
    define("MAGIC_TOWER", "Magiczna Wieża");
    define("TEMPLE", "Świątynia");
    define("ALCHEMY_SHOP", "Alchemik");
    define("CLEAN_CITY", "Polana drwala");
    define("ALCHEMY_MILL", "Pracownia alchemiczna");
    define("LUMBER_MILL", "Tartak");
    define("FORUMS", "Forum");
    define("INN", "Karczma");
    define("PRIV_M", "Poczta");
    define("CLANS", "Klany");
    define("HOUSES", "Domy");
    define("PLAYERS_L", "Spis mieszkańców");
    define("MONUMENTS", "Posągi");
    define("MARKET", "Rynek");
    define("TRAVEL", "Stajnia");
    define("SCHOOL", "Szkoła");
    define("FOREST2", "Brama do lasu");
    define("CORES", "Polana Chowańców");
    define("UPDATES", "Wieści");
    define("TIMER", "Zegar miejski");
    define("JAIL2", "Lochy");
    define("POLLS", "Brzoza przeznaczenia");
    define("LIBRARY", "Biblioteka");
    define("NEWS", "Plotki");
    define("FARMS", "Farma");
    define("PAPER", "Redakcja gazety");
    define("OUTPOSTS", "Strażnica");
    define("WELLEARNED", "Aleja Zasłużonych");
    define("STAFF_LIST", "Korzeń przeznaczenia");
    define("JEWELLER_SHOP", "Jubiler");
    define("JEWELLER", "Warsztat jubilerski");
    define("COURT", "Gmach Sądu");
    define("GO_FOREST", "Po krótkim spacerze dochodzisz do potężnych bram odgradzających miasto od najdzikszych i zapominanych przez bogów oraz elfy ostępów lasu Avantiel. Kliknij <a href=\"las.php\">tutaj</a>");
}
?>
