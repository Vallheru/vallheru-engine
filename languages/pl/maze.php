<?php
/**
 *   File functions:
 *   Labyrynth in forrest city
 *
 *   @name                 : maze.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 30.10.2006
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
define("FIGHT2", ". Czy chcesz spróbować walki?");
define("FIGHT1", "Nie możesz wędrować po labiryncie, ponieważ jesteś w trakcie walki!<br />Napotkałeś ");
define("Y_TURN_F", "Tak (turowa walka)");
define("Y_NORM_F", "Tak (szybka walka)");
define("YOU_MEET", "Napotkałeś");
define("A_EXPLORE", "Zwiedzaj dalej");

if (isset($_GET['step']) && $_GET['step'] == 'run') 
{
    define("ESCAPE_SUCC", "Udało ci się uciec przed");
    define("ESCAPE_SUCC2", "Zdobywasz za to");
    define("ESCAPE_SUCC3", "PD");
    define("ESCAPE_FAIL", "Nie udało ci się uciec przed");
    define("ESCAPE_FAIL2", "Rozpoczyna się walka");
    define("R_SPE5", "szybkości");
}

if (!isset($_GET['action']))
{
    define("MAZE_INFO", "Dochodzisz powoli do Starego buku. Zauważasz, że dwóch elfickich wartowników pilnuje jakiegoś podziemnego wejścia. Podchodzisz z zamiarem zbadania podziemia, a jeden z nich zagaduje Ciebie:<br />- Czy na pewno chcesz tam wejść? Zanim podejmiesz decyzje wiesz, że jest to stara i opuszczona siedziba potężnego czarownika Myrdalisa. Podziemia te nigdy nie zostały dokładnie spenetrowane... Nie istnieje żadna mapa która mogła by być pomocna przy znalezieniu tam czegokolwiek. Na dodatek całe podziemia aż roją się od potworów. Czarownik przyzywał często różne demony, a te po jego śmierci stały się całkowicie wolne. Niektóre opuściły podziemia i zostały schwytane bądź zabite, lecz spora ich część nadal się tam znajduje. Zaletą penetracji tych podziemi jest fakt, że można tam znaleźć mnóstwo skarbów, ale czy jesteś gotów podjąć ryzyko?");
}
    else
{
    define("NO_ENERGY", "Nie masz energii!!");
    define("YOU_DEAD", "Nie możesz zwiedzać, ponieważ jesteś martwy!");
    define("EMPTY_1", "Krążąc po podziemnych korytarzach nie napotykasz na nic wartego uwagi");
    define("EMPTY_2", "Znalazłeś jakieś opuszczone pomieszczenie. Widzisz poprzewracane i zniszczone meble. Na podłodze walają się zniszczone pergaminy, porozbijane naczynia. Nie znajdujesz nic ciekawego.");
    define("EMPTY_3", "Idąc zaciemnionym korytarzem natrafiasz na jego koniec. Przez chwilkę szukasz jakiegoś tajemnego przejścia. Po kilku minutach poddajesz się i zawracasz.");
    define("EMPTY_4", "Idąc korytarzem, zauważasz jakieś wejście do pomieszczenia na jego końcu. Wchodzisz ostrożnie do środka. Twoim oczom ukazuje się obraz zniszczenia. Najprawdopodobniej miał tu miejsce jakiś wybuch. Przejście prowadzące dalej jest zawalone gruzem, w pomieszczenie bardziej przypomina rumowisko niż cokolwiek innego. Po chwili zastanowienia dochodzisz do wniosku, ze nic ciekawego tu nie znajdziesz. Postanowiłeś zawrócić.");
    define("EMPTY_5", "Podążasz ostrożnie ciemnym, wąskim korytarzem. Co chwila opadają na ciebie strzępy pajęczyn, które strzepujesz z siebie z wielkim obrzydzeniem. Twoje kroki dźwięczą głucho niepokojąc twoje zmysły. - \"Jeśli ja słyszę to pewnie słyszy to także każde inne stworzenie w okolicy\" - wzdrygasz się na samą myśl o spotkaniu z potworem. Jednak wiesz, że musisz iść dalej. Trzymasz broń w pogotowiu na wypadek zasadzki i dalej ruszasz powoli w mrok korytarza. Twoja pochodnia rozświetla pobliskie ściany, na których gdzie nigdzie widać kawałki misternie zdobionego tynku. Masz wrażenie, że korytarz ciągnie się kilometrami...  Po kilku godzinach błądzenia nie napotykasz niczego interesującego.");
    define("EMPTY_6", "Idziesz powoli rozglądając się uważnie. Dostrzegasz jakiś błysk. Niestety to
tylko odbicie pochodni w kałuży.");
    define("F_HERBS", "Wchodzisz do pomieszczenia wyglądającego na jakieś laboratorium. Większość przyrządów jest zniszczona, lecz Twoją uwagę skupiają zioła leżące na stoliku, które zapewne miały służyć do przyrządzenia mikstury. Stwierdzając, iż niewiele straciły ze swej świeżości, zabierasz je ze sobą. Zdobyłeś ");
    define("I_AMOUNT", " sztuk ");
    define("F_LUMBER", "Odnalazłeś niewielki składzik drewna. Część z zapasów została zniszczona przez pożar jaki miał tu miejsce, jednak reszta będzie się nadawała do użytku. Postanawiasz zabrać je ze sobą. Zdobywasz ");
    define("T_PINE", " drewna sosnowego.");
    define("T_HAZEL", " drewna z leszczyny.");
    define("T_YEW", " drewna cisowego.");
    define("T_ELM", " drewna z wiązu.");
    define("F_MITHRIL", "Idąc korytarzem słyszysz nagle rumor kilkanaście metrów dalej. Zatrzymujesz się i nasłuchujesz. Cisza... Postanawiasz iść w miejsce skąd dobiegał hałas. Dostrzegasz boczne drzwi. Otwierasz je. W malutkim i wąskim pomieszczeniu jest pełno dymu. Zauważasz że był to jakiś składzik, który właśnie co się zawalił. Wśród gruzu dostrzegasz jednak kilka grudek jakiegoś metalu. To mithril! Udaje Ci się odnaleźć ");
    define("M_AMOUNT", " sztuk mithrilu. Zatrzymujesz je i kontynuujesz wędrówkę.");
    define("F_ENERGY", "Wchodzisz do pomieszczenia. Na środku dostrzegasz kamienny posążek przedstawiający miecz z jelcem w kształcie skrzydeł, wbity w kolumnę. Rozglądasz się po pokoju - nie ma w nim nic więcej oprócz posągu. Wyczuwasz jednak pozytywną energię tu panującą. Postanawiasz chwile odsapnąć. Odzyskujesz 1 energi.");
    define("F_PLANBOW", "Badając opustoszałe pomieszczenia wieży, odnajdujesz pokój, przypominający warsztat stolarski. Widzisz kilka niedokończonych łuków i kusz na stojakach. Dbałość o niektóre elementy czyni z nich naprawdę piękną broń, więc jesteś trochę zawiedziony, że rzemieślnik nie zdążył skończyć tych wyrobów. Twój humor ulega jednak natychmiastowej poprawie, gdy zauważasz na stoliku jakieś szkice. Zaczynasz je pobieżnie przeglądasz i wkrótce odkrywasz, że jest tu dokładnie opisana metoda wyrobu broni dystansowej!<br /><br /><br />Zdobywasz plan ");
    define("F_PLANPOTION", "Wchodzisz do pomieszczenia. Na pierwszy rzut oka dostrzegasz, że jest to pracownia alchemiczna. Niestety większość przyrządów nie nadaje się już do niczego. Potłuczone probówki, naczynia, porozlewane ciecze. Jednak na biurku obok dostrzegasz jakieś papiery z notatkami. Zaczynasz je przeglądać. Rozpoznajesz że jest to przepis na miksturę!<br /><br /><br />Zdobywasz przepis na ");
    define("F_STAFF", "Wkraczasz do niewielkiego, okrągłego pomieszczenia. Na środku ustawiona jest kadzielnica, a zaraz za nią znajduje się jakaś księga. Domyślasz się, że jest to komnata przyzwań. Podchodzisz do księgi, ciekaw, co też ona zawiera. Niestety nie potrafisz nic z niej odczytać - jest napisana w jakimś nieznanym języku. Twoją uwagę zwraca jedna smukły przedmiot leżący za księgą. Podnosisz go. To ");
    define("F_STAFF2", "! Postanawiasz wziąć ją ze sobą.");
    define("F_CAPE", "Wchodzisz do dość ładnie umeblowanego pokoju. Stylowe łóżko, kuferek, jakaś szafa i regał. Otwierasz kuferek, jednak ze smutkiem stwierdzasz, że nie ma w nim nic ciekawego. Jakieś papiery z bazgrołami, zniszczone szaty. Również regał nie zawiera nic wartościowego. Kilka opowiadań, które znajdują się również w bibliotece. Zrezygnowany kładziesz się na łóżko z zamiarem krótkiego odpoczynku. Nagle jednak uświadamiasz sobie, że w pomieszczeniu jest jeszcze szafa. Zrywasz się i otwierasz ją. Widzisz kilka kompletów ubrań. Te jednak kompletnie Ci się nie podobają. Wprost nie możesz uwierzyć, że istnieje lub istniała osoba, która mogła chodzić w czymś takim. Przesuwasz wieszaki z ubraniami, patrząc z obrzydzeniem. Nagle dotykając jednej podłużnej szaty, wyczuwasz delikatne wibracje w palcach. Ta szata musi mieć właściwości magiczne! Wyciągasz ją i zaczynasz się jej przyglądać. Rozpoznajesz, że jest to ");
    define("F_CAPE2", ". Postanawiasz ją zabrać ze sobą.");
    define("F_SPELL", "Przeszukując pomieszczenia Avan Tirith, trafiasz do pokoju wyglądającego na małą, prywatną bibliotekę. Kilka regałów zostało zniszczonych przez jakiś pożar, jednak jeden stojący na uboczu ocalał. Zaczynasz przeglądać zawartość pierwszej księgi z brzegu. Książka mówi o jakiś eksperymentach mających tutaj miejsce... Twą uwagę odwraca jakiś pergamin, który wypadł z księgi trzymanej przez Ciebie. Odkładasz książkę na miejsce i podnosisz pergamin. Rozpoznajesz że jest to czar ");
    define("F_SPELL2", "! Kopiujesz go do swojej księgi.");
    define("F_BOW", "Idąc korytarzem dostrzegasz nagle leżące zwłoki jakiegoś humanoida. Nie jesteś w stanie rozpoznać dokładnie gdyż ciało jest strasznie zmasakrowane. U jego stóp zauważasz jednak broń. To ");
    define("F_ASTRAL", "Idziesz po labiryncie i napotykasz na stary wytarty kawałek papieru. Okazuje się że jest to ");
}
?>
