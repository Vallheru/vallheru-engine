<?php
/**
 *   File functions:
 *   Polish language for clans
 *
 *   @name                 : tribes.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : mori <ziniquel@users.sourceforge.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 30.01.2012
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
}
?>
