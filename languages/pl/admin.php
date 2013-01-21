<?php
/**
 *   File functions:
 *   Polish language for admin panel
 *
 *   @name                 : admin.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 21.01.2013
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
define("NOT_ADMIN", "Nie jesteś władcą!");
define("REFRESH", "Odśwież");
define("E_DB", "Nie mogę dodać wpisu!");
define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
define("A_ADD", "Dodaj");
define("A_SEND", "Wyślij");
define("A_MAKE", "Zrób");
define("A_BACK", "Wróć do menu");
define("A_NEXT", "Dalej");
define("M_NAME", "Nazwa");
define("M_LEVEL", "Poziom");
define("M_POWER", "Siła");
define("M_HP", "PŻ");
define("M_AGI", "Zręczność");
define("M_SPEED", "Szybkość");
define("M_COND", "Wytrzymałość");
define("M_MIN_GOLD", "Min złota");
define("M_MAX_GOLD", "Max złota");
define("M_MIN_EXP", "Min PD");
define("M_MAX_EXP", "Max PD");
define("M_LOCATION", "Lokacja");
define("M_CITY1", "Altara");
define("M_CITY2", "Ardulith");
define("M_CITY3", "Cytadela");
define("ONLY_MAIN", "Tylko główny władca ma dostęp do tej opcji!");

if (!isset($_GET['view']))
{
    define("A_WELCOME", "Witaj w panelu administracyjnym. Co chcesz zrobić?");
    define("A_ADDUPDATE", "Dodaj wieść");
    define("A_ADDNEWS", "Dodaj plotkę");
    define("A_DELETE", "Skasować użytkownika");
    define("A_DONATION", "Dotować użytkownika");
    define("A_TAKE", "Zabierz złoto graczowi");
    define("A_RANK", "Zmień rangę graczowi");
    define("A_IMMU", "Daj immunitet");
    define("A_FORUM_P", "Wyczyść forum");
    define("A_CHAT_P", "Wyczyść karczmę");
    define("A_EQUIP", "Dodaj przedmiot");
    define("A_MONSTERS", "Dodaj potwora");
    define("A_SMITH", "Dodaj plan u kowala");
    define("A_SPELLS", "Dodaj czar");
    define("A_PM", "Wyślij wiadomość do wszystkich graczy");
    define("A_CHAT_BAN", "Zablokuj/odblokuj wiadomości od gracza na czacie");
    define("A_JAIL", "Wyślij gracza do więzienia");
    define("A_BRIDGE", "Dodaj pytanie na moście śmierci");
    define("A_MAIL", "Wyślij maila do wszystkich graczy");
    define("A_CLOSE", "Zablokuj/odblokuj grę");
    define("A_DEL_PLAYERS", "Wykasuj nieaktywnych graczy");
    define("A_BAN", "Zbanuj/odbanuj gracza");
    define("A_BUGTRACK", "Bugtrack");
    define("A_REGISTER", "Zablokuj/odblokuj rejestrację");
    define("A_CENSORSHIP", "Dodaj/usuń słowo do listy słów zabronionych");
    define("A_POLL", "Dodaj nową ankietę");
    define("A_JAILBREAK", "Uwolnij gracza z więzienia");
    define("A_MONSTER2", "Edytuj potwora");
    define("A_DONATOR", "Dodaj gracza do Alei zasłużonych");
    define("A_MILL", "Dodaj plan w tartaku");
    define("A_FORUMS", "Dodaj/Edytuj kategorie forum");
    define("A_META", "Edytuj znaczniki meta");
    define("A_LOGS", "Logi graczy");
    define("A_CHANGELOG", "Dodaj wpis o zmianach w grze");
    define("A_INNARCHIVE", "Archiwum karczmy");
    define("A_PLAYERQUEST", "Dodaj gracza do przygody");
    define("A_BAN_MAIL", "Zablokuj/Odblokuj wiadomości od gracza na poczcie");
    define("A_BUG_REPORT", "Zgłoszone błędy");
    define("A_ADD_NEWS", "Sprawdź oczekujące plotki");
}

if (isset($_GET['view']) && $_GET['view'] == 'addtext')
{
    define("MODIFIED", "Zmodyfikowano tekst");
    define("ADDED", "Tekst dodany do Plotek!");
    define("DELETED", "Tekst wykasowany!");
    define("ADMIN_INFO", "Tutaj możesz dodawać, modyfikować oraz usuwać teksty będące na liście oczekujących. Pamiętaj jednak, że nie wolno Ci zmieniać treści pracy, a jedynie poprawiać w niej błędy!");
    define("ADMIN_INFO2", "Modyfikuj - zobacz treść pracy i ewentualnie zmodyfikuj");
    define("ADMIN_INFO3", "Dodaj - po prostu dodaje tekst od razu do Plotek bez czytania (nie będziesz pytany o potwierdzenie)");
    define("ADMIN_INFO4", "Usuń - usuwa tekst z listy oczekujących do dodania do Plotek (nie będziesz pytany o potwierdzenie)");
    define("ADMIN_INFO5", "Oto lista oczekujących tekstów");
    define("A_DELETE", "Usuń");
    define("A_CHANGE", "Zmień");
    define("A_MODIFY", "Modyfikuj");
    define("T_AUTHOR", "Autor");
    define("T_TITLE", "Tytuł");
    define("T_BODY", "Treść");
    define("YOUR_NEWS", "Twoja plotka <b>");
    define("HAS_ADDED", " </b>została zaakceptowana przez ");
    define("HAS_DELETED", " </b>została odrzucona przez ");
    define("L_ID", ", ID ");
}

if (isset($_GET['view']) && $_GET['view'] == 'bugreport')
{
    define("BUG_TYPE", "Rodzaj błędu");
    define("BUG_TEXT", "Literówka");
    define("BUG_CODE", "Błąd w grze");
    define("BUG_LOC", "Lokacja");
    define("BUG_NAME", "Tytuł zgłoszenia");
    define("BUG_ID", "Numer");
    define("BUG_REPORTER", "Zgłaszający");
    define("BUG_DESC", "Opis");
    define("BUG_ACTIONS", "Ustaw jako");
    define("BUG_FIXED", "Naprawiony");
    define("NOT_BUG", "To nie jest błąd");
    define("BUG_DOUBLE", "Duplikat");
    define("MORE_INFO", "Wymaga więcej informacji");
    define("YOUR_BUG", "Zgłoszony przez ciebie błąd: <b>");
    define("B_ID", "</b> ID: <b>");
    define("NOT_BUG3", "</b> nie jest błędem.");
    define("HAS_FIXED", "</b> został naprawiony.");
    define("MORE_INFO2", "</b> wymaga więcej informacji aby mogło zostać naprawione.");
    define("WORK_FOR_ME2", "</b> zostało zaktualizowane. <b>Przyczyna:</b> u mnie działa poprawnie, wymaga więcej informacji aby mogło zostać naprawione.");
    define("BUG_DOUBLE2", "</b> zostało odrzucone. <b>Przyczyna:</b> wcześniej ktoś zgłosił już ten błąd.");
    define("NOT_BUG2", "Oznaczyłeś ten błąd jako nieprawidłowy.");
    define("HAS_FIXED2", "Oznaczyłeś ten błąd jako naprawiony.");
    define("WORK_FOR_ME3", "Oznaczyłeś ten błąd jako pomyłkę (u mnie działa).");
    define("MORE_INFO3", "Oznaczyłeś ten błąd jako nienaprawialny (wymaga więcej informacji).");
    define("BUG_DOUBLE3", "Oznaczyłeś ten błąd jako duplikat innego błędu.");
    define("T_BUG", "Naprawiony błąd");
    define("REPORTED_BY", " zgłoszony przez ID: ");
    define("WORK_FOR_ME", "U mnie działa");
    define("T_COMMENT2", "Komentarz");
}

if (isset ($_GET['view']) && $_GET['view'] == 'banmail') 
{
    define("BLOCK_LIST", "Lista zablokowanych");
    define("A_BLOCK", "Zablokuj");
    define("A_UNBLOCK", "Odblokuj");
    define("MAIL_ID", "ID");
    define("YOU_BLOCK", "Zablokowałeś wysyłanie wiadomości na poczcie przez gracza");
    define("YOU_UNBLOCK", "Odblokowałeś wysyłanie wiadomości na poczcie przez gracza");
}

if (isset($_GET['view']) && $_GET['view'] == 'playerquest') 
{
    define("ADD_PLAYER", "gracza o ID");
    define("TO_QUEST", "do przygody numer");
    define("YOU_ADD", "Dodałeś gracza do przygody.");
}

if (isset($_GET['view']) && $_GET['view'] == 'innarchive') 
{
    define("A_NEXT2", "Kolejne wpisy");
    define("A_PREVIOUS", "Poprzednie wpisy");
    define("C_ID", "ID");
}

if (isset($_GET['view']) && $_GET['view'] == 'changelog') 
{
    define("CHANGE_INFO", "Jeżeli wprowadziłeś jakieś zmiany w grze, wpisz tutaj informacje na ich temat aby poinformować o tym graczy.");
    define("CHANGE_LOCATION", "Zmienione lokacje");
    define("CHANGE_TEXT", "Opis zmian");
    define("CHANGE_ADDED", "Dodano informację o zmianach.");
}

if (isset($_GET['view']) && $_GET['view'] == 'logs') 
{
    define("LOGS_INFO", "Tutaj możesz przeglądać logi z niektórych akcji graczy.");
    define("L_OWNER", "Właściciel (ID)");
    define("L_TEXT", "Treść");
    define("L_CLEAR", "Wyczyść");
    define("NO_LOGS", "Nie masz jeszcze logów");
    define("A_PREVIOUS", "Poprzednie");
    define("LOGS_CLEARED", "Logi wyczyszczone");
}

if (isset($_GET['view']) && $_GET['view'] == 'meta') 
{
    define("META_INFO", "Tutaj możesz edytować tzw meta znaczniki strony - służą one do opisu gry w wyszukiwarkach<br />Znacznik keywords - to słowa po wpisaniu których w wyszukiwarce w wynikach pojawiać się będzie twoja gra. Jeżeli wcześniej wpisałeś jakieś wartości dla tych znaczników, zostaną one teraz zastąpione nowymi<br />Znacznik description - to opis gry jaki będzie się pojawiał przy wyświetlaniu wyników<br /><br />");
    define("META_KEY", "słowa kluczowe");
    define("META_DESC", "opis");
    define("META_UPGRADE", "Zaktualizowano znaczniki meta");
}

if (isset($_GET['view']) && $_GET['view'] == 'forums') 
{
    define("CAT_LIST", "Lista kategorii forum");
    define("T_NAME", "Nazwa kategorii");
    define("T_DESC", "Opis kategorii");
    define("T_LANG", "Język kategorii");
    define("T_WRITE", "Mogą pisać w kategorii");
    define("T_VISIT", "Mogą przeglądać kategorię");
    define("T_ALL", "Wszyscy");
    define("T_STAFF", "Książęta");
    define("T_JUDGE", "Sędziowie");
    define("T_JUDGE2", "Kanclerz Sądu");
    define("T_COUNT", "Marszałek Rady");
    define("T_COUNT2", "Posłowie");
    define("T_LAWYER", "Prawnicy");
    define("T_JUDGE3", "Ławnicy");
    define("T_PROCURATOR", "Prokuratorzy");
    define("CATEGORY_MODIFIED", "Kategoria została zmodyfikowana");
    define("CATEGORY_ADDED", "Kategoria została dodana");
}

if (isset ($_GET['view']) && $_GET['view'] == 'mill') 
{
    define("S_NAME", "Nazwa");
    define("S_COST", "Cena");
    define("S_AMOUNT", "Ilość minerałów");
    define("S_LEVEL", "Poziom");
    define("S_TYPE", "Typ");
    define("S_BOW", "łuk");
    define("S_ARROWS", "strzały");
}

if (isset($_GET['view']) && $_GET['view'] == 'donator')
{
    define("DONATOR_INFO", "Dodaj gracza do alei zasłużonych");
    define("P_NAME", "Imię");
    define("YOU_ADD", "Dodałeś gracza o imieniu ");
    define("TO_DONATORS", " do Alei zasłużonych.");
}

if (isset($_GET['view']) && $_GET['view'] == 'monster2')
{
    define("A_EDIT", "Edytuj");
    define("YOU_EDIT", "Edycja zakończona pomyślnie. Zmieniłeś statystyki potworowi: ");
}

if (isset($_GET['view']) && $_GET['view'] == 'jailbreak')
{
    define("JAIL_ID", " gracza o ID: ");
    define("A_FREE", "Uwolnij");
    define("T_MESSAGE", "Uwolniłeś z więzienia gracza o ID: ");
    define("NO_PLAYER2", "Nie ma takiego gracza!");
}

if (isset($_GET['view']) && $_GET['view'] == 'poll')
{
    define("T_QUESTION", "Pytanie");
    define("T_AMOUNT", "Ilość odpowiedzi");
    define("T_ANSWER", "Odpowiedź");
    define("POLL_ADDED", "Ankieta dodana");
    define("T_LANG", "Język");
    define("T_DAYS", "Czas trwania (dni)");
}

if (isset($_GET['view']) && $_GET['view'] == 'censorship')
{
    define("YOU_ADD", "Dodałeś nowe słowo do listy słów zabronionych: ");
    define("YOU_DELETE", "Skasowałeś słowo z listy słów zabronionych: ");
    define("A_DELETE", "Usuń");
    define("T_WORD", "słowo");
    define("WORDS_LIST", "Lista cenzurowanych słów");
}

if (isset($_GET['view']) && $_GET['view'] == 'register')
{
    define("YOU_CLOSE", "Zablokowałęś rejestrację nowych graczy");
    define("YOU_OPEN", "Odblokowałeś rejestrację nowych graczy");
    define("G_OPEN", "Odblokuj");
    define("G_CLOSE", "Zablokuj");
    define("IF_CLOSE", "Jeżeli blokujesz rejestrację, podaj przyczynę");
}
    
if (isset($_GET['view']) && $_GET['view'] == 'ban') 
{
    define("YOU_BAN", "Zbanowałeś");
    define("YOU_UNBAN", "Odbanowałeś");
    define("BAN_LIST", "Lista zablokowanych");
    define("BAN_INFO", "Możesz zablokować gracza aby nie miał dostępu do gry poprzez IP, adres mailowy, nick lub ID. Możesz również odblokować gracza");
    define("BAN_VALUE", "Podaj IP, adres mailowy, nick lub ID");
    define("BAN_IP", "IP");
    define("BAN_EMAIL", "mail");
    define("BAN_NICK", "nick");
    define("BAN_ID", "ID");
    define("A_BAN_PL", "Zbanuj");
    define("A_UNBAN", "Odbanuj");
    define("BAN_TYPE", "Typ");
    define("BAN_VAL", "Wartość");
    define("BANNED", "Zbanowany");
}

if (isset($_GET['view']) && $_GET['view'] == 'delplayers') 
{
    define("YOU_DELETE", "Skasowałeś");
    define("INACTIVE", "nieaktywnych użytkowników");
    define("NEVER_LOGGED", "nigdy nie zalogowanych");
}

if (isset ($_GET['view']) && $_GET['view'] == 'mail') 
{
    define("MAIL_INFO", "Treść maila, jeżeli chcesz zrobić nową linię użyj znacznika \\n (backslash n) do tego celu");
    define("M_ERROR", "Wiadomość nie została wysłana. Błąd:");
    define("M_SEND", "Maile zostały rozesłane.");
}

if (isset ($_GET['view']) && $_GET['view'] == 'bridge') 
{
    define("YOU_ADD_Q", "Dodałeś do mostu pytanie");
    define("WITH_AN", "z odpowiedzią");
    define("B_QUESTION", "Pytanie");
    define("B_ANSWER", "Odpowiedź");
}

if (isset ($_GET['view']) && $_GET['view'] == 'jail') 
{
    define("JAIL_ID", "ID gracza");
    define("JAIL_REASON", "Przyczyna");
    define("JAIL_TIME", "Czas (w dniach)");
    define("PLAYER_IN_JAIL", "Ten gracz jest już w lochach!");
    define("PLAYER_JAIL", "Gracz o ID");
    define("HAS_BEEN_J", " został wtrącony do więzienia na ");
    define("DAYS", " dni za ");
}

if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    define("YOU_DELETE2", "Skasowałeś ID");
    define("YOU_NOT_D", "Nie skasowałeś użytkownika");
    define("DELETE_ID", "Skasuj ID");
    define("A_DELETE_PL", "Skasuj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    define("YOU_ADD_R","Dodałeś ID");
    define("NEW_RANK", "jako");
    define("ADD_ID", "Dodaj ID");
    define("R_MEMBER", "Mieszkaniec");
    define("R_KING", "Władca");
    define("R_STAFF", "Książę");
    define("R_JUDGE", "Sędzia");
    define("R_JUDGE2", "Ławnik");
    define("R_LAWYER", "Prawnik");
    define("R_BEGGAR", "Żebrak");
    define("R_BARBARIAN", "Nikczemnik");
    define("R_SCRIBE", "Bibliotekarz");
    define("R_KNIGHT", "Rycerz");
    define("R_LADY", "Dama");
    define("R_COUNT", "Marszałek Rady");
    define("R_COUNT2", "Poseł");
    define("R_JUDGE3", "Kanclerz Sądu");
    define("R_REDACTOR", "Redaktor");
    define("R_INNKEEPER", "Karczmarka");
    define("R_PROCURATOR", "Prokurator");
}

if (isset ($_GET['view']) && $_GET['view'] == 'clearf') 
{
    define("FORUM_PRUNE", "Wyczyściłeś forum.");
    define("F_QUESTION", "Jesteś pewien?");
}

if (isset ($_GET['view']) && $_GET['view'] == 'clearc') 
{
    define("CHAT_PRUNE", "Wyczyściłeś czat.");
}

if (isset ($_GET['view']) && $_GET['view'] == 'tags') 
{
    define("YOU_ADD_I", "Dałeś immunitet ID");
    define("GIVE_IMMU", "Daj immunitet ID");
    define("A_GIVE", "Daj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'equipment') 
{
    define("ITEM_NAME", "Nazwa");
    define("I_WEAPON", "broń");
    define("I_ARMOR", "zbroja");
    define("I_LEGS", "nagolenniki");
    define("I_HELMET", "hełm");
    define("I_BOW", "łuk");
    define("I_SHIELD", "tarcza");
    define("I_ARROWS", "strzały");
    define("I_STAFF", "różdżka");
    define("I_CAPE", "szata");
    define("I_WITH", "z");
    define("I_POWER", "Siłą");
    define("I_COST", "i Kosztującą");
    define("I_REPAIR", "Koszt naprawy");
    define("I_MIN_LEV", "z minimalnym poziomem");
    define("I_AGI", "z ograniczeniem zr (zbroja, nagolenniki oraz tarcza)");
    define("I_SPEED", "dodająca do szybkości (broń tylko)");
    define("I_DUR", "z wytrzymałością (nie dotyczy różdżek i szat)");
    define("YOU_ADD_ITEM", "Dodałeś");
    define("HAS_A", "jako");
    define("POWER", "z siłą");
    define("COST", "kosztującą");
    define("MIN_LEVEL", "oraz tylko dla tych, którzy osiągneli");
    define("ITEM_LEVEL", "poziom ograniczającą zręczność (nagolenniki i zbroja)");
    define("ITEM_SPEED", "dodającą do szybkości (tylko broń)");
    define("ITEM_DUR", "oraz z wytrzymałością (nie dotyczy różdżek i szat)");
}

if (isset ($_GET['view']) && $_GET['view'] == 'donate') 
{
    define("YOU_SEND_M", "Przekazałeś pieniądze");
    define("DONATE_ID", "ID");
    define("DONATE_AMOUNT", "Ilość");
    define("A_DONATE", "Dotuj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'takeaway') 
{
    define("GOLD_TAKEN", "sztuk złota zostało zabranych ID");
    define("TAKE_ID", "ID");
    define("TAKE_AMOUNT", "Ilość");
    define("A_TAKE_G", "Zabierz");
    define("YOU_GET", "Zostałeś ukarany za: ");
    define("T_AMOUNT", " kwotą ");
    define("GOLD_COINS", " sztuk złota. Karę wymierzył: ");
    define("T_REASON", "Przyczyna:");
    define("T_INJURED", "Poszkodowany (ID):");
    define("TAKE_INFO", "Wymierz karę pieniężną:");
    define("T_PLAYER1", "Gracz ");
    define("T_PLAYER2", ", ID ");
    define("HAS_TAKEN", " został ukarany za: ");
    define("SANCTION_SET", " Karę wymierzył: ");
}

if (isset ($_GET['view']) && $_GET['view'] == 'kowal') 
{
    define("S_NAME", "Nazwa");
    define("S_COST", "Cena");
    define("S_AMOUNT", "Ilość minerałów");
    define("S_LEVEL", "Poziom");
    define("S_TYPE", "Typ");
    define("S_WEAPON", "broń");
    define("S_ARMOR", "zbroja");
    define("S_HELMET", "hełm");
    define("S_SHIELD", "tarcza");
    define("S_LEGS", "nagolenniki");
    define("S_TWOHAND", "Dwuręczna");
}

if (isset ($_GET['view']) && $_GET['view'] == 'poczta') 
{
    define("YOU_SEND_PM", "Wysłałeś wiadomość do");
    define("PLAYERS_A", "graczy.");
    define("PM_SUBJECT", "Temat");
    define("PM_BODY", "Treść");
}

if (isset ($_GET['view']) && ($_GET['view'] == 'czat' || $_GET['view'] == 'bforum')) 
{
    define("BLOCK_LIST", "Lista zablokowanych");
    define("A_BLOCK", "Zablokuj");
    define("A_UNBLOCK", "Odblokuj");
    define("CHAT_ID", "ID");
    define("YOU_BLOCK", "Zablokowałeś wysyłanie wiadomości na czacie przez gracza");
    define("YOU_UNBLOCK", "Odblokowałeś wysyłanie wiadomości na czacie przez gracza");
    define("T_DAYS", " dni z przyczyny: ");
    define("YOU_BLOCK2", "Masz zakaz wchodzenia do karczmy na ");
    define("BLOCK_BY", ". Zablokował ciebie: ");
    define("ON_A", "na");
}

if (isset ($_GET['view']) && $_GET['view'] == 'czary') 
{
    define("YOU_ADD_SPELL", "Dodałeś czar");
    define("HAS_A_S", "jako czar");
    define("POWER_S", "z siłą");
    define("COST_S", "kosztujący");
    define("MIN_LEV_S", "dla graczy, którzy osiągneli poziom");
    define("SPELL_NAME", "Nazwa");
    define("S_BATTLE", "Bojowy");
    define("S_DEFENSE", "Obronny");
    define("S_WITH", "z");
    define("S_POWER", "Siłą");
    define("S_COST", "i Kosztujący");
    define("S_MIN_LEV", "z minimalnym poziomem");
}

if (isset ($_GET['view']) && $_GET['view'] == 'close') 
{
    define("YOU_CLOSE", "Zablokowałęś grę");
    define("YOU_OPEN", "Odblokowałeś grę");
    define("G_OPEN", "Odblokuj");
    define("G_CLOSE", "Zablokuj");
    define("IF_CLOSE", "Jeżeli blokujesz grę, podaj przyczynę");
}
?>
