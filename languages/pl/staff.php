<?php
/**
 *   File functions:
 *   Polish language for staff panel
 *
 *   @name                 : staff.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 29.01.2013
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

define("YOU_NOT", "Nie jesteś Księciem");
define("ERROR", "Zapomnij o tym!");
define("A_MAKE", "Zrób");

if (!isset($_GET['view'])) 
{
    define("PANEL_INFO", "Witaj w panelu administracyjnym. Co chcesz zrobić?");
    define("A_NEWS", "Dodaj Plotkę");
    define("A_TAKE", "Zabrać sztuki złota");
    define("A_CLEAR", "Wyczyścić Czat");
    define("A_CHAT", "Zablokuj/odblokuj wiadomości od gracza na czacie");
    define("A_IMMU", "Daj Immunitet");
    define("A_JAIL", "Wyślij gracza do więzienia");
    define("A_ADD_NEWS", "Sprawdź oczekujące plotki");
    define("A_INNARCHIVE", "Archiwum karczmy");
    define("A_BAN_MAIL", "Zablokuj/Odblokuj wiadomości od gracza na poczcie");
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

if (isset($_GET['view']) && $_GET['view'] == 'innarchive') 
{
    define("A_NEXT2", "Kolejne wpisy");
    define("A_PREVIOUS", "Poprzednie wpisy");
    define("C_ID", "ID");
}

if (isset ($_GET['view']) && $_GET['view'] == 'jail') 
{
    define("JAIL_ID", "ID gracza");
    define("JAIL_REASON", "Przyczyna");
    define("JAIL_TIME", "Czas (w dniach)");
    define("A_ADD", "Dodaj");
    define("PLAYER_IN_JAIL", "Ten gracz jest już w lochach!");
    define("PLAYER_JAIL", "Gracz o ID: ");
    define("HAS_BEEN_J", " został wtrącony do więzienia na ");
    define("DAYS", " dni za ");
}

if (isset ($_GET['view']) && $_GET['view'] == 'tags') 
{
    define("YOU_ADD", "Dałeś immunitet ID <b>");
    define("GIVE_IMMU", "Daj immunitet ID");
    define("A_ADD", "Daj");
}

if (isset ($_GET['view']) && $_GET['view'] == 'clearc') 
{
    define("CHAT_PRUNE", "Wyczyściłeś karczmę");
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

if (isset ($_GET['view']) && ($_GET['view'] == 'czat' || $_GET['view'] == 'bforum')) 
{
    define("YOU_BLOCK", "Zablokowałeś wysyłanie wiadomości na czacie przez gracza ");
    define("YOU_UNBLOCK", "Odblokowałeś wysyłanie wiadomości na czacie przez gracza ");
    define("BLOCK_LIST", "Lista zablokowanych");
    define("CHAT_ID", "ID");
    define("A_BLOCK", "Zablokuj");
    define("A_UNBLOCK", "Odblokuj");
    define("T_DAYS", " dni z przyczyny: ");
    define("YOU_BLOCK2", "Masz zakaz wchodzenia do karczmy na ");
    define("BLOCK_BY", ". Zablokował ciebie: ");
    define("ON_A", "na");
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
    define("A_ADD", "Dodaj");
    define("A_DELETE", "Usuń");
    define("A_CHANGE", "Zmień");
    define("A_MODIFY", "Modyfikuj");
    define("T_AUTHOR", "Autor");
    define("T_TITLE", "Tytuł");
    define("T_BODY", "Treść");
    define("YOUR_NEWS", "Twoja plotka <b>");
    define("HAS_ADDED", " </b>została zaakceptowana przez ");
    define("HAS_DELETED", " </b>została odrzucona przez ");
    define("L_ID", " ID ");
}
?>
