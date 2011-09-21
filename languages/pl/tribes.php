<?php
/**
 *   File functions:
 *   Polish language for clans
 *
 *   @name                 : tribes.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : mori <ziniquel@users.sourceforge.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 21.09.2011
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

if (!isset ($_GET['view']) && !isset($_GET['join'])) 
{
    define("MAKE_NEW", "Stwórz nowy klan (2,500,000 sztuk złota)");
    define("CLAN_INFO", "Witaj w Domu Klanów. Tutaj możesz zobaczyć, dołączyć lub nawet stworzyć nowy klan.");
    define("A_SHOW", "Zobacz listę klanów");
}

if (isset ($_GET['view']) && $_GET['view'] == 'all') 
{
    define("NO_CLANS", "<br />Na razie nie ma jakiegokolwiek klanu.");
    define("SHOW_INFO", "Tutaj jest lista wszystkich klanów.");
    define("LEADER_ID", "ID Przywódcy");
}

if (isset ($_GET['view']) && $_GET['view'] == 'view') 
{
    define("NO_CLAN", "Nie ma takiego klanu!");
    define("CLAN_PAGE", "Strona klanu");
    define("LEADER", "Przywódca");
    define("YOU_SEE", "Oglądasz");
    define("LEADER2", "Przywódca: ID");
    define("MEM_AMOUNT", "Liczba członków");
    define("A_MEMBERS", "Członkowie");
    define("WIN_AMOUNT", "Wygranych walk");
    define("LOST_AMOUNT", "Przegranych walk");
    define("JOIN_TO", "Dołącz do klanu");
    define("A_JOIN", "Dołącz");
    define("MEMBER_LIST", "Lista członków klanu");
    define("A_STEAL", "Ukradnij astralny komponent");
    define("ASTRAL", "Postęp prac nad Astralną Machiną: ");
    define("ASTRAL1", "Przygotowuje się");
    define("ASTRAL2", "Rozpoczął");
    define("ASTRAL3", "Buduje");
    define("ASTRAL4", "Wykańcza");
    define("ASTRAL5", "Niemalże ukończył");
    define("ASTRAL6", "Ukończył");
    if (isset($_GET['step']) && $_GET['step'] == 'steal')
    {
        define("NO_CRIME", "Nie możesz próbować kradzieży astralnego komponentu, ponieważ niedawno próbowałeś już swoich sił!");
        define("YOU_DEAD", "Nie możesz okradać klanu ponieważ jesteś martwy");
        define("SAME_CLAN", "Nie możesz okradać klanu do którego należysz!");
        define("VERDICT", "Próba kradzieży astralnego komponentu");
        define("L_REASON", "Zostałeś wtrącony do więzienia na 2 dni za próbę kradzieży astralnego komponentu. Możesz wyjść z więzienia za kaucją: ");
        define("C_CACHED", "Kiedy próbowałeś dostać się do astralnego skarbca, zauważyli ciebie strażnicy. Błyskawicznie otoczyli i zmusili do poddania. I tak oto znalazłeś się w lochach.");
        define("NO_AMOUNT", "Szczęśliwie pokonałeś wszelkie przeszkody i niezauważony zbliżasz się do astralnego skarbca. Kiedy wchodzisz do środka i rozglądasz się w około masz ochotę zawyć ze złości. Nic tutaj nie ma! Tyle nerwów na marne. Zdenerwowany opuszczasz skarbiec.");
        define("SUCCESFULL", "Ostrożnie, mijając wszystkie zastawione pułapki wchodzisz do astralnego skarbca. Twoim oczom ukazuje się długo oczekiwany widok - w około leżą różne astralne komponenty. Nie mając za bardzo czasu przyjrzeć się im dokładnie, chwytasz szybko jeden z nich i opuszczasz skarbiec. To twój szczęśliwy dzień! Ukradłeś");
        define("L_CACHED", "Mieszkaniec ");
        define("L_CACHED2", ", ID: <b>");
        define("L_CACHED3", "</b> próbował ukraść astralny komponent z twojego klanu. Na szczęście został pojmany przez strażników.");
        define("ASTRAL_GONE", "Kiedy przeglądałeś astralne komponenty należące do twojego klanu, zauważyłeś że jednego brakuje. Ktoś prawdopodobnie okradł twój klan! Ze skarbca zniknął");
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
}

if (isset($_GET['join'])) 
{
    define("YOU_IN_CLAN", "Jesteś w klanie!");
    define("L_PLAYER", "Gracz ");
    define("L_ID", "</a></b>, ID ");
    define("HE_WANT", " prosi o przyjęcie do klanu.");
    define("YOU_SEND", "Wysłałeś swoje zgłoszenie do klanu ");
    define("YOU_WAIT", "Oczekujesz już na wejście do innego klanu! Czy chcesz zmienić swoje zgłoszenie?");
}

if (isset ($_GET['view']) && $_GET['view'] == 'make') 
{
    define("NO_MONEY", "Nie masz tylu sztuk złota.");
    define("YOU_IN_CLAN", "Jesteś już w klanie!");
    define("NO_NAME", "Podaj nazwę klanu.");
    define("YOU_MAKE", "Stworzyłeś nowy klan, <i>");
    define("CLAN_NAME", "Nazwa klanu");
    define("A_MAKE", "Załóż");
    define("YOU_DEAD", "Nie możesz założyć klanu ponieważ jesteś martwy!");
}

if (isset ($_GET['view']) && $_GET['view'] == 'my') 
{
    define("NOT_IN", "Nie jesteś w klanie!");
    define("CLAN_PAGE", "Strona klanu");
    define("L_PLAYER", "Gracz");
    define("L_ID", "</a></b>, ID ");
    define("NOT_IN_CLAN", "Ten gracz nie jest w twoim klanie!");
    define("MY_CLAN", "Mój klan");
    define("MENU1", "Główna");
    define("MENU2", "Dotuj");
    define("MENU3", "Członkowie");
    define("MENU4", "Zbrojownia");
    define("MENU5", "Magazyn");
    define("MENU6", "Skarbiec");
    define("MENU7", "Zielnik");
    define("MENU8", "Opuść klan");
    define("MENU9", "Opcje przywódcy");
    define("MENU10", "Forum klanu");
    define("MENU11", "Astralny skarbiec");
    if (!isset($_GET['step']))
    {
        define("WELCOME", "Witaj w swoim klanie.");
        define("CLAN_NAME", "Nazwa klanu");
        define("MEM_AMOUNT", "Liczba członków");
        define("LEADER", "Przywódca");
        define("GOLD_COINS", "Sztuk(i) złota");
        define("MITH_COINS", "Sztuk(i) mithrilu");
        define("WIN_AMOUNT", "Wygranych walk");
        define("LOST_AMOUNT", "Przegranych walk");
        define("T_SOLDIERS", "Żołnierzy");
        define("T_FORTS", "Fortyfikacji");
        define("UNKNOWN", "Nieznane");
        define("A_MACHINE", "Astralna maszyna");
        define("A_PERCENT", "wykonano");
    }
    if (isset($_GET['step']) && $_GET['step'] == 'astral')
    {
        define("NO_COMPONENTS", "Klan nie może budować astralnej machiny ponieważ brakuje mu komponentów!");
        define("ASTRAL_BUILD", "Klan nie może budować astralnej machiny ponieważ już została ona wybudowana!");
        define("A_USED", "Do tej pory przeznaczono na budowę");
        define("A_ENERGY", "energii.");
        define("A_TODAY", "Dzisiaj przeznaczono na budowę");
        define("A_MAX", "Maksymalnie");
        define("A_DIRECT", "Przeznacz");
        define("A_FORM", "na wykonanie astralnej maszyny");
        define("NO_ENERGY", "Nie masz tyle energii!");
        define("TOO_MUCH", "W sumie klan nie może przeznaczyć więcej niż 2000 energii w ciągu jednego dnia!");
        define("TOO_MUCH2", "Nie możesz przeznaczyć więcej energii niż potrzeba!");
        define("YOU_ADD", "Dodałeś ");
        define("A_REFRESH", "Odśwież");
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'donate') 
    {
        define("GOLD_COINS", "sztuk(i) złota");
        define("MITH_COINS", "sztuk(i) Mithrilu");
        define("NO_AMOUNT", "Nie masz wystarczająco dużo ");        
        define("YOU_GIVE", "Dałeś swojemu klanowi <b>");
        define("HE_ADD", " dodał do skarbca klanu ");
        define("DON_INFO", "Proszę daj pieniądze swojemu klanowi i pomóż mu finansowo.");
        define("A_DONATE", "Dotuj");
        define("GOLD_COINS2", "Sztuk(i) Złota");
        define("MITH_COINS2", "Sztuk(i) Mithrilu");
        define("TO_CLAN", " do swojego klanu.");
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'zielnik') 
    {
        define("HERB1", "Illani");
        define("HERB2", "Illanias");
        define("HERB3", "Nutari");
        define("HERB4", "Dynallca");
        define("HERB5", "Nasiona Illani");
        define("HERB6", "Nasiona Illanias");
        define("HERB7", "Nasiona Nutari");
        define("HERB8", "Nasiona Dynallca");
        define("NO_AMOUNT", "Klan nie ma takiej ilości ");
        define("YOU_SEND1", "Klan przekazał graczowi ");
        define("YOU_SEND2", ", ID ");
        define("YOU_GET", "Dostałeś od klanu ");
        define("NO_AMOUNT2", "Nie masz takiej ilości ");
        define("YOU_ADD", "Dodałeś <b>");
        define("TO_CLAN", "</b> do zielnika klanu.");
        define("HE_ADD", " dodał do zielnika klanu ");
        define("HERBS_INFO", "Witaj w zielniku klanu. Tutaj są składowane zioła należące do klanu. Każdy członek klanu może ofiarować klanowi jakieś zioła ale tylko przywódca lub osoba upoważniona przez niego może darować dane zioła członkom swojego klanu. Aby dać jakieś zioła członkom klanu, kliknij na nazwę owego zioła");
        define("WHAT_YOU", "Co chcesz zrobić?");
        define("A_GIVE_TO", "Dać zioła do klanu");
        define("GIVE_PLAYER", "Daj graczowi ID:");
        define("A_GIVE", "Daj");
        define("ADD_HERB", "Dodaj zioła do zielnika");
        define("HERB", "Zioło");
        define("H_AMOUNT", "Sztuk(i)");
        define("A_ADD", "Dodaj");
        define("T_AMOUNT", "z posiadanych");
        define("H_AMOUNT2", "sztuk");
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'skarbiec') 
    {
        define("MIN1", "Rudy miedzi");
        define("MIN2", "Rudy cynku");
        define("MIN3", "Rudy cyny");
        define("MIN4", "Rudy żelaza");
        define("MIN5", "Sztabki miedzi");
        define("MIN6", "Sztabki brązu");
        define("MIN7", "Sztabki mosiądzu");
        define("MIN8", "Sztabki żelaza");
        define("MIN9", "Sztabki stali");
        define("MIN10", "Bryły węgla");
        define("MIN11", "Bryły adamantium");
        define("MIN12", "Kawałki meteorytu");
        define("MIN13", "Kryształy");
        define("MIN14", "Drewno sosnowe");
        define("MIN15", "Drewno z leszczyny");
        define("MIN16", "Drewno cisowe");
        define("MIN17", "Drewno z wiązu");
        define("MINE1", "rudy miedzi");
        define("MINE2", "rudy cynku");
        define("MINE3", "rudy cyny");
        define("MINE4", "rudy żelaza");
        define("MINE5", "sztabek miedzi");
        define("MINE6", "sztabek brązu");
        define("MINE7", "sztabek mosiądzu");
        define("MINE8", "sztabek żelaza");
        define("MINE9", "sztabek stali");
        define("MINE10", "brył węgla");
        define("MINE11", "brył adamantium");
        define("MINE12", "kawałków meteorytu");
        define("MINE13", "kryształów");
        define("MINE14", "drewna sosnowego");
        define("MINE15", "drewna z leszczyny");
        define("MINE16", "drewna cisowego");
        define("MINE17", "drewna z wiązu");
        define("NO_AMOUNT", "Klan nie ma takiej ilości ");
        define("CLAN_SEND1", "Klan przekazał graczowi ");
        define("CLAN_SEND2", ", ID ");
        define("YOU_SEND", "Przekazałeś graczowi ID ");
        define("YOU_GET", "Dostałeś od klanu ");
        define("NO_AMOUNT2", "Nie masz takiej ilości ");
        define("YOU_ADD", "Dodałeś <b>");
        define("TO_CLAN", "</b> do skarbca klanu.");
        define("HE_ADD", " dodał do skarbca klanu ");
        define("PIECES", " sztuk(i) ");
        define("MIN_INFO", "Witaj w skarbcu klanu. Tutaj są składowane minerały należące do klanu. Każdy członek klanu może ofiarować klanowi jakiś minerał ale tylko przywódca lub osoba upoważniona przez niego może darować dany minerał członkom swojego klanu. Aby dać jakiś minerał członkom klanu, kliknij na nazwę owego minerału.");
        define("WHAT_YOU", "Co chcesz zrobić?");
        define("A_GIVE_TO", "Dać minerały do klanu");
        define("GIVE_PLAYER", "Daj graczowi ID:");
        define("A_GIVE", "Daj");
        define("ADD_MIN", "Dodaj minerały do skarbca");
        define("MINERAL", "Minerał");
        define("M_AMOUNT", "Ilość minerału");
        define("A_ADD", "Dodaj");
        define("T_AMOUNT", "z posiadanych");
        define("M_AMOUNT2", "sztuk");
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'members') 
    {
        define("LEADER", "Przywódca");
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'quit') 
    {
        define("L_LEADER", "Opuszczasz klan. Ponieważ jesteś przywódcą, klan został usunięty.");
        define("L_MEMBER", "Opuszczasz klan.");
        define("Q_LEADER", "Czy na pewno chcesz odejść z klanu? Jeżeli to zrobisz, klan zostanie zlikwidowany!");
        define("Q_MEMBER", "Czy na pewno chcesz odejść z klanu?");
        define("M_LEAVE", " opuścił Twój klan.");
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'owner') 
    {
        define("NO_PERM2", "Tylko przywódca lub osoba upoważniona może przebywać tutaj!");
        define("NOT_LEADER", "Nie masz prawa tutaj przebywać.");
        define("PANEL_INFO", "Witaj w panelu przywódcy klanu. Co chcesz zrobić?");
        define("A_PERM", "Ustawić uprawnienia członków klanu");
        define("A_RANK", "Ustawić rangi członkom klanu");
        define("A_DESC", "Edytować opis klanu, wiadomość dla członków oraz herb klanu i stronę klanu");
        define("A_WAITING", "Sprawdź listę oczekujących na dołączenie do klanu");
        define("A_KICK", "Wyrzucić Członka");
        define("A_ARMY", "Dokupić żołnierzy lub fortyfikacji do klanu");
        define("A_ATTACK2", "Zaatakować inny klan");
        define("A_LOAN", "Pożycz pieniądze członkowi");
        define("A_MISC", "Dodatki klanu");
        define("A_MAIL2", "Wyślij pocztę do członków klanu");
        if (isset ($_GET['step2']) && $_GET['step2'] == 'rank') 
        {
            define("GIVE_RANK", "Daj członkowi klanu rangę");
            define("RANK_CREATED", "Rangi utworzone");
            define("BACK_TO", "Wróć do menu rang");
            define("RANK_CHANGED", "Rangi zmienione");
            define("NO_RANKS", "Nie ma określonych rang!<br />");
            define("YOU_GIVE", "Dałeś graczowi ID ");
            define("T_RANK", " rangę: ");
            define("RANKS_INFO", "Tutaj możesz ustawić rangi członkom swojego klanu, jak również określić ich nazwy. Maksymalnie możesz ustalić 10 rang.");
            define("A_ADD_RANKS", "Stwórz nowe rangi lub edytuj istniejące");
            define("NO_RANKS2", "Na razie nie posiadasz określonych jakichkolwiek rang w klanie! Możesz właśnie je stworzyć. Możesz je wpisywać w dowolnej kolejności, dodatkowo nie musisz wypełniać wszystkich pól (możesz np zrobić tylko 3 rangi");
            define("RANK", "ranga");
            define("EDIT_RANKS", "Posiadasz już określone kilka rang w klanie. Jeżeli chcesz możesz edytować istniejące lub dodać nowe");
            define("A_MAKE", "Utwórz");
            define("A_SAVE", "Zapisz");
            define("SET_RANK", "Ustaw rangę");
            define("RANK_PLAYER", "graczowi o ID");
            define("A_SET", "Ustaw");
        }
        if (isset ($_GET['step2']) && $_GET['step2'] == 'permissions') 
        {
            define("ONLY_LEADER", "Tylko przywódca może ustawiać zezwolenia!");
            define("BACK_TO", "Wróć do menu przywódcy");
            define("YOU_SET", "Ustawiłeś uprawnienia członkowi klanu.");
            define("PERM_INFO", "Tutaj możesz ustawić uprawnienia do różnych miejsc dowolnym członkom klanu. Wybierz z listy odpowiedniego członka klanu a następnie ustaw mu odpowiednie uprawnienia. Nie ma ograniczeń co do ilości osób uprawnionych.");
            define("T_PERM1", "Może edytować opisy klanu");
            define("T_PERM2", "Może dołączać nowych członków");
            define("T_PERM3", "Może wyrzucać członków z klanu");
            define("T_PERM4", "Może kupować żołnierzy oraz fortyfikacje");
            define("T_PERM5", "Może wykonywać ataki na inny klan");
            define("T_PERM6", "Może pożyczać pieniądze członkom klanu");
            define("T_PERM7", "Może dawać przedmioty ze zbrojowni");
            define("T_PERM8", "Może dawać przedmioty z magazynu");
            define("T_PERM9", "Może dawać minerały ze skarbca");
            define("T_PERM10", "Może dawać zioła z zielnika");
            define("T_PERM11", "Może kasować posty na forum");
            define("T_PERM12", "Może ustalać rangi członkom klanu");
            define("T_PERM13", "Może wysyłać listy do członków klanu");
            define("T_PERM14", "Nie może oglądać informacji o klanie");
            define("T_PERM15", "Może łączyć astralne plany, dawać je członkom klanu");
            define("A_SAVE", "Zapisz");
            define("A_NEXT", "Dalej");
            define("T_USER", "Nadaj uprawnienia: ");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'mail')
        {
            define("T_BODY", "Treść");
            define("A_SEND", "Wyślij");
            define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
            define("YOU_SEND", "Wysłałeś(aś) wiadomość do członków swojego klanu");
            define("T_TITLE", "Tytuł");
            define("T_CLAN", "<b>[Klan]</b> ");
        }
        if (isset ($_GET['step2']) && $_GET['step2'] == 'wojsko') 
        {
            define("NO_PERM", "Tylko przywódca lub osoba upoważniona może kupować żołnierzy i fortyfikacje do klanu!");
            define("EMPTY_FIELDS", "Wypełnij chociaż jedno pole!");
            define("NO_MONEY", "Klan nie ma tyle sztuk złota");
            define("YOU_BUY", "Kupiłeś dla swojego klanu ");
            define("SOLDIERS", " żołnierzy za ");
            define("BARRICADES", " fortyfikacji za ");
            define("FOR_A", " sztuk(i) złota<br />");
            define("ALL_COST", "W sumie wydałeś na wszystko ");
            define("ARMY_INFO", "Tutaj możesz dokupić żołnierzy oraz fortyfikacje dla klanu. Żołnierze dodają do siły ataku twojego klanu, natomiast fortyfikacje dodają do jego obrony. Koszt pojedynczego żołnierza lub fortyfikacji wynosi: ilość żołnierzy(bądź fortyfikacji) kupowanych * 1000 sztuk złota.");
            define("HOW_MANY_S", "Ilu żołnierzy chcesz kupić?");
            define("HOW_MANY_F", "Ile fortyfikacji chcesz kupić?");
            define("A_BUY", "Kupuj");
            define("SOMEONE_BUY", "Zakupiono dla klanu ");
            define("ALL_COST2", "W sumie wydano na wszystko ");
        }
        if (isset ($_GET['step2']) && $_GET['step2'] == 'nowy') 
        {
            define("YOU_DROP", "Odrzuciłeś kandydata o ID ");
            define("L_TRIBE", "Klan ");
            define("L_DROP", " odrzucił twoją kandydaturę.");
            define("YOU_ACCEPT", "Zaakceptowałeś kandydata o ID ");
            define("L_ACCEPT", " przyjął twoją kandydaturę. Jesteś już członkiem klanu.");
            define("WAIT_LIST", "Lista oczekujących");
            define("T_ID", "ID gracza");
            define("T_ACCEPT", "Dodaj");
            define("T_DROP", "Odrzuć");
        }
        if (isset ($_GET['step2']) && $_GET['step2'] == 'walka') 
        {
            define("ONLY_ONE", "Klan może atakować inne klany tylko raz na reset!");
            define("A_ATTACK", "Atakuj klan ");
            define("SELECT_TRIBE", "Wybierz klan, który chcecie zaatakować");
            define("YOU_WANT", "Naprawdę chcesz zaatakować klan ");
            define("L_TRIBE", "Klan ");
            define("ATTACK_YOU", " zaatakował i pokonał Twój klan. Staciliście ");
            define("GOLD_COINS", " sztuk(i) złota oraz ");
            define("MITHRIL_COINS", " sztuk(i) mithrilu!");
            define("ATTACK_YOU2", " zaatakował i został pokonany przez Twój klan. Zdobyliście ");
            define("SELECT_CLAN", "Wybierz klan, który chcecie zaatakować");
            define("YOU_WIN", "Stoisz razem ze swoimi towarzyszami i przyjaciółmi przed armią . Zaraz zacznie się bitwa... ATAK!!! Słyszysz jak przywodca klanu");
            define("YOU_WIN2", "wykrzyknął komendę do jego towarzyszy. Ruszasz razem z twoimi podwładnymi w wir walki. W powietrzu latają ogniste kule, strzały oraz inne pociski. Zabijasz jednego wroga po drugim. Patrząc w chwili spokoju na swoich towarzyszy zauważasz iż oni także z wielkim zapałem niszczą przeciwników w imie klanu");
            define("YOU_WIN3", "Wygraliście tę wspaniałą bitwę. Ze skarbca pokonanego klanu wynieśliście");
            define("ENEMY_WIN2", "wykrzyknął komende do jego towarzyszy. Ruszasz razem z twoimi podwładnymi i zaczyna sie bitwa. W powietrzu latają ogniste kule, strzały oraz inne pociski. Atakujesz przywodcę klanu");
            define("ENEMY_WIN3", "i podczas walki giniesz. Padając na ziemię patrzysz jak inni twoi przyjaciele dzielą twoj los... Przegraliście tą walkę...  Z waszego skarbca przeciwnik wyniósł");
            define("WIN_COMPONENT", " Oprócz tego ze skarbca pokonanego klanu wynosicie");
            define("WIN_COMPONENT2", " Oprócz tego z waszego skarbca przeciwnicy wynoszą");
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
        if (isset ($_GET['step2']) && $_GET['step2'] == 'messages') 
        {
            define("WWW_SET", "Adres strony ustawiony na");
            define("A_REFRESH", "Odśwież");
            define("LOGO_DEL", "Herb usunięty.");
            define("NO_FILE", "Nie ma takiego pliku!<br />");
            define("BAD_TYPE", "Zły typ pliku!");
            define("NO_COPY", "Nie skopiowano pliku!");
            define("LOGO_LOAD", "Herb załadowany!");
            define("MSG_CHANGED", "Wiadomość została zmieniona.");
            define("CLAN_DESC", "Opis klanu");
            define("MSG_TO_MEM", "Wiadomość dla członków");
            define("A_CHANGE", "Zmień");
            define("A_SET", "Zatwierdź");
            define("CLAN_SITE", "Adres strony klanu (wpisz bez http://)");
            define("LOGO_INFO", "Tutaj możesz zmienić herb swojego klanu. <b>Uwaga!</b> Jeżeli klan już posiada herb, stary zostanie skasowany. Maksymalny rozmiar herbu to 10 kB. Herb możesz załadować tylko z własnego komputera. Musi on mieć rozszerzenie *.jpg, *.jpeg, *.gif lub *.png");
            define("A_DELETE", "Skasuj");
            define("A_SEND", "Wyślij");
            define("LOGO_NAME", "Nazwa pliku graficznego");
        }
        if (isset ($_GET['step2']) && $_GET['step2'] == 'kick') 
        {
            define("NO_ID", "Podaj ID gracza!");
            define("YOU_DROP", "Zostałeś wyrzucony z ");
            define("D_ID", "ID ");
            define("NOT_IS", " nie jest już członkiem klanu.");
            define("IS_LEADER", "Nie możesz wyrzucić Przywódcy.");
            define("KICK_ID", "Wyrzuć ID");
            define("FROM_CLAN", "z klanu");
            define("A_KICK2", "Wyrzuć");
            define("CLAN_KICK1", "Gracz ");
            define("CLAN_KICK2", ", ID ");
            define("HAS_BEEN", " został wyrzucony z klanu ");
        }
        if (isset ($_GET['step2']) && $_GET['step2'] == 'loan') 
        {
            define("GOLD_COINS", "sztuk(i) złota");
            define("MITHRIL_COINS", "sztuk(i) mithrilu");
            define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
            define("NO_AMOUNT", "Klan nie ma tyle ");
            define("SEND_YOU", "Klan pożyczył ci ");
            define("YOU_GIVE", "Pożyczyłeś graczowi o ID ");
            define("A_LOAN2", "Pożycz");
            define("PLAYER_ID", "osobie o ID ");
            define("CLAN_GIVE1", "Klan pożyczył graczowi ");
            define("CLAN_GIVE2", ", ID ");
        }
        if (isset ($_GET['step2']) && $_GET['step2'] == 'te') 
        {
            define("NO_MITH", "Klan nie ma tyle sztuk mithrilu");
            define("A_BACK", "wróć");
            define("CLAN_HAVE", "Klan posiada zniżkę na leczenie w szpitalu");
            define("MISC_INFO", "Witaj w panelu dodatków klanu. Co chcesz zrobić?");
            define("A_FREE_HEAL", "Kup zniżkę na leczenie w szpitalu dla klanu (100 sztuk mithrilu)");
            define("YOU_BUY", "Kupiłeś zniżkę na leczenie dla członków swojego klanu w szpitalu");
        }
    }
}
?>
