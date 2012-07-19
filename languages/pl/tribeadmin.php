<?php
/**
 *   File functions:
 *   Polish language for clans admins
 *
 *   @name                 : tribeadmin.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 19.07.2012
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
define("NO_PERM2", "Tylko przywódca lub osoba upoważniona może przebywać tutaj!");
define("NOT_LEADER", "Nie masz prawa tutaj przebywać.");
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
    define("RANK_PLAYER", "graczowi");
    define("A_SET", "Ustaw");
  }
if (isset ($_GET['step2']) && $_GET['step2'] == 'permissions') 
  {
    define("ONLY_LEADER", "Tylko przywódca może ustawiać zezwolenia!");
    define("BACK_TO", "Wróć do menu przywódcy");
    define("YOU_SET", "Ustawiłeś uprawnienia członkowi klanu.");
    define("PERM_INFO", "Tutaj możesz ustawić uprawnienia do różnych miejsc dowolnym członkom klanu. Wybierz z listy odpowiedniego członka klanu a następnie ustaw mu odpowiednie uprawnienia. Nie ma ograniczeń co do ilości osób uprawnionych.");
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
    define("MSG_CHANGED", "Informacje zostały zmienione.");
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
    define("KICK_ID", "Wyrzuć");
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
    define("PLAYER_ID", "osobie:");
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
?>