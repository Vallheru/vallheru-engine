<?php
/**
 *   File functions:
 *   Polish language for library
 *
 *   @name                 : library.php                            
 *   @copyright            : (C) 2004,2005,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 03.04.2012
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
// 

define("ERROR", "Zapomnij o tym!");
define("T_TYPE1", "Opowiadanie");
define("T_TYPE2", "Wiersz");
define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
define("NO_TEXT", "Nie ma takiego tekstu!");
define("T_TITLE", "Tytuł");
define("T_BODY", "Treść");
define("T_AUTHOR", "Autor");
define("T_TYPE", "Typ");
define("A_BACK", "Wróć");
define("A_MODIFY", "Modyfikuj");

if (!isset($_GET['step']))
{
    define("WELCOME", "Witaj w Bibliotece");
    define("WELCOME2", "Znajdziesz tutaj różne opowiadania, wiersze jakie pozostawili podróżnicy wędrujący ścieżkami");
    define("WELCOME3", "Jeżeli chcesz, rówież możesz zostawić tutaj coś od siebie. Opiekę nad tym miejscem sprawuje Bibliotekarz");
    define("WELCOME4", "Do niego należy ocena czy dany tekst nadaje się do publikacji w Bibliotece.");
    define("WELCOME5", "Wszelkie prawa autorskie należą do autorów tekstów.");
    define("WELCOME6", "Obecnie w Bibilotece znajduje się");
    define("WELCOME7", "tekstów a na dołączenie do niej, oczekuje");
    define("WELCOME8", "tekstów.");
    define("A_TALES", "Opowiadania");
    define("T_INFO1", "opowiadań");
    define("T_INFO2", "wierszy");
    define("A_POETRY", "Wiersze");
    define("A_ADD_TEXT", "Dodaj tekst");
    define("A_ADMIN", "Sprawdź oczekujące teksty");
    define("A_RULES", "Regulamin Biblioteki");
}

if (isset($_GET['step']) && $_GET['step'] == 'add')
{
    define("YOU_ADD", "Dodałeś już tekst do listy oczekujących. Teraz zajmie się nim Bibliotekarz.");
    define("ADD_INFO", "Tutaj możesz dodać swoją pracę do Biblioteki. Pamiętaj aby dokładnie sprawdzić poprawność pisowni. Możesz używać pogrubienia ([b]tekst[/b]), kursywy ([i]tekst[/i]) oraz podkreśleń ([u]tekst[/u]). Pamiętaj też, że Biblitekarz ma prawo poprawić twój tekst jeżeli uzna to za stosowne.");
    define("T_LANG", "Język");
    define("A_ADD", "Dodaj");
}

if (isset($_GET['step']) && $_GET['step'] == 'addtext')
{
    define("NO_PERM", "Nie masz prawa przebywać tutaj!");
    define("MODIFIED", "Zmodyfikowano tekst");
    define("ADDED", "Tekst dodany do biblioteki!");
    define("DELETED", "Tekst wykasowany!");
    define("ADMIN_INFO", "Tutaj możesz dodawać, modyfikować oraz usuwać utwory będące na liście oczekujących. Pamiętaj jednak, że nie wolno Ci zmieniać treści pracy, a jedynie poprawiać w niej błędy!");
    define("ADMIN_INFO2", "Modyfikuj - zobacz treść pracy i ewentualnie zmodyfikuj");
    define("ADMIN_INFO3", "Dodaj - po prostu dodaje tekst od razu do Biblioteki bez czytania (nie będziesz pytany o potwierdzenie)");
    define("ADMIN_INFO4", "Usuń - usuwa tekst z listy oczekujących do dodania do Biblioteki (nie będziesz pytany o potwierdzenie)");
    define("ADMIN_INFO5", "Oto lista oczekujących tekstów");
    define("A_ADD", "Dodaj");
    define("A_DELETE", "Usuń");
    define("A_CHANGE", "Zmień");
}

if (isset($_GET['step']) && ($_GET['step'] == 'tales' || $_GET['step'] == 'poetry'))
{
    define("T_INFO1", "opowiadań");
    define("T_INFO2", "wierszy");
    define("NO_ITEMS", "Nie ma jakichkolwiek");
    define("IN_LIB", "w Bibliotece.");
    define("LIST_INFO", "Oto lista wszystkich");
    define("LIST_INFO2", "jakie znajdują się w Bibliotece. Aby przeczytać dany tekst kliknij na jego tytule.");
    define("T_COMMENTS", "komentarzy");
    define("T_COMMENTS2", "Komentarze");
    define("A_SORT", "Sortuj");
    define("SORT_INFO", "Sortuj według");
    define("O_AUTHOR", "Autora");
    define("O_DATE", "Daty");
    define("O_TITLE", "Tytułu");
}

if (isset($_GET['step']) && $_GET['step'] == 'comments')
{
    define("C_ADDED", "Komentarz dodany!");
    define("C_DELETED", "Komentarz skasowany!");
    define("NO_COMMENTS", "Nie ma jeszcze komentarzy do tego tekstu!");
    define("A_DELETE", "Skasuj");
    define("ADD_COMMENT", "Dodaj komentarz");
    define("A_ADD", "Dodaj");
    define("NO_PERM", "Nie masz prawa przebywać tutaj!");
	define("WRITED", "napisał(a)");
}

if (isset($_GET['step']) && $_GET['step'] == 'rules')
{
    define("RULES", "1. Tekst musi być zgodny z zasadami języka polskiego. Poprawny ortograficznie, stylistycznie i językowo. Jeśli tekst nie będzie poprawny zostanie odesłany do autora w celu korekty.<br /><br />
    2. Tekst musi być kulturalny. Nie może zawierać wulgarnych zwrotów, nie może obrażać żadnej osoby, grupy społecznej czy religijnej. Jeśli ktoś wyśle tekst godzący w czyjąś godność zostanie mu zablokowana możliwość dodawania nowych pozycji do biblioteki.<br /><br />
    3. Tekst musi mieć poprawną budowę graficzną. Nie dodajemy enterów po każdym zdaniu czy wersie. Wyjątkiem od tej zasady są wiersze. Tekst niespełniający tego punktu zostanie odesłany do autora w celu korekty.<br /><br />
    4. Tekst musi zawierać polskie znaki. Piszemy po polsku, dlatego uprasza się o używanie polskich liter. Tekst niespełniający tego punktu zostanie odesłany do autora w celu korekty.<br /><br />
    5. Bibliotekarz zastrzega sobie prawo do wniesienia poprawek lub drobnej korekty niemającej wpływu na treść i przesłanie tekstu.<br /><br />
    6. Ostateczna decyzja odnośnie poprawności tekstu i jego akceptacja należy do bibliotekarza.<br /><br /><br />
    Zasada komentowania tekstów w bibliotece.<br /><br />
    1. Komentujemy tekst, a nie autora. Wszelkie komentarze godzące w inną osobę będą kasowane.<br /><br />
    2. Komentując tekst pamiętaj o kulturze. Krytyka jest potrzebna, ale pamiętaj o tym, czego sam nie chciałbyś usłyszeć. Każdemu, kto naruszy tą zasadę zostanie odebrana możliwość komentowania tekstów.<br /><br />
    3. Odbieraj krytykę z honorem. Pamiętaj, że nie ma ona na celu ugodzenie w Ciebie tylko ma na celu pokazanie błędów, które powinieneś poprawić. Nikt nie jest doskonały.<br /><br />
    4. Komentarze mogą stanowić debatę. Nie należy jednak schodzić z tematu. Jeśli debata przestanie mieć coś wspólnego z tekstem zostanie przerwana.");
}
?>
