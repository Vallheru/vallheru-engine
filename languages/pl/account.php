<?php
/**
 *   File functions:
 *   Polish language for account.php
 *
 *   @name                 : account.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 14.01.2013
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
define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
define("WELCOME", "Witaj w ustawieniach konta. Proszę, wybierz opcję.");
define("A_NAME", "Zmień imię");
define("A_PASS", "Zmień hasło");
define("A_PROFILE", "Edytuj profil");
define("A_EMAIL", "Edytuj informacje kontaktowe");
define("A_AVATAR", "Edytuj avatara");
define("A_RESET", "Resetuj postać");
define("A_IMMU", "Immunitet");
define("A_STYLE", "Dostosuj wygląd gry");
define("A_LANG", "Wybierz język gry");
define("CHANGE", "Zmień");
define("A_FREEZE", "Zablokowanie konta");
define("A_OPTIONS", "Dodatkowe opcje");
define("A_CHANGES", "Ostatnie zmiany");
define("A_BUGREPORT", "Zgłoś błąd");
define("A_BUGTRACK", "Lista błędów");
define("A_LINKS", "Własne linki");

if (isset($_GET['view']))
  {
    if ($_GET['view'] == 'links')
      {
	define("LINKS_INFO", "Poniżej możesz dodać bądź edytować dodatkowe linki jakie mają pojawiać się w menu Nawigacja. Linki możesz dodawać albo tylko jako nazwa pliku (np \"city.php\") albo cały adres (np \"".$gameadress."/city.php\"). Możesz również ustawić kolejność wyświetlania linku poprzez nadanie mu odpowiedniego numeru. Linki z niższym numerem będą wyswietlane jako pierwsze.");
	define("T_LINK", "Adres");
	define("T_NAME", "Nazwa");
	define("T_ACTIONS", "Akcje");
	define("A_DELETE", "Skasuj");
	define("A_EDIT", "Edytuj");
	define("A_ADD", "Dodaj");
	define("NOT_YOUR", "To nie jest twój link!");
	define("YOU_CHANGE", "Zmieniłeś link w menu Nawigacja.");
	define("YOU_ADD", "Dodałeś link do menu Nawigacja.");
	define("LINK_DELETED", "Usunąłeś link z menu Nawigacja.");
      }

    if ($_GET['view'] == 'bugtrack' || $_GET['view'] == 'bugreport')
      {
	define("BUG_TYPE", "Rodzaj błędu");
	define("BUG_TEXT", "Literówka");
	define("BUG_CODE", "Błąd w grze");
	define("BUG_LOC", "Lokacja");
	define("BUG_NAME", "Tytuł zgłoszenia");
      }

    if ($_GET['view'] == 'bugtrack')
      {
	define("BUGTRACK_INFO", "Poniżej znajduje się lista zgłoszonych ale jeszcze nie naprawionych błędów.");
	define("BUG_ID", "Numer");
	define("C_ADDED", "Komentarz dodany!");
	define("C_DELETED", "Komentarz skasowany!");
	define("A_DELETE", "Skasuj");
      }

    if ($_GET['view'] == 'bugreport')
      {
	define("BUG_DESC", "Opis błędu");
	define("A_REPORT", "Zgłoś");
	define("B_REPORTED", "Błąd zgłoszony.");
	define("BUG_INFO", "Tutaj możesz zgłosić błąd w grze. Staraj się opisać dokładnie co się wydarzyło, co robiłeś zanim ten błąd wystąpił oraz jeżeli na ekranie pojawiły się jakieś informacje na ten temat, podaj je. Postaraj się nadać zgłoszeniu odpowiedni tytuł. Zgłoszenia typu \"Błąd\" bądź z niewiele mówiącym tytułem nie będą brane pod uwagę!");
      }

    elseif ($_GET['view'] == "changes") 
      {
	define("CHANGES_INFO", "Poniżej zamieszczone są informacje na temat ostatnich 30 zmian dokonanych na ".$gamename.".");
	define("CHANGE_LOC", "Lokacja");
      }

    elseif ($_GET['view'] == "options") 
      {
	define("T_OPTIONS", "Tutaj możesz ustawić dodatkowe opcje twojego konta.");
	define("T_BATTLELOG", "Wysyłanie przebiegu walki na pocztę");
	define("T_GRAPHBAR", "Wyświetlanie graficznych pasków życia/zdrowia/energii (dotyczy tylko trybu tekstowego).");
	define("A_NEXT", "Dalej");
	define("A_SAVED", "Dodatkowe opcje zostały zapisane. <a href=\"account.php\">Odśwież</a>");
      }
    
    elseif ($_GET['view'] == "freeze") 
      {
	define("FREEZE_INFO", "Tutaj możesz zamrozić swoje konto na określony czas. W tym czasie nie będziesz mógł wejść na nie, ale również nie będziesz mógł być okradziony czy też zaatakowany. Niestety, nie ma możliwości zamrożenia również twojej strażnicy. Na zamrożone konto nie przychodzi energia co reset. Maksymalny czas blokady - 21 dni. Blokada rozpoczyna od zaraz.");
	define("HOW_MANY", "Podaj na ile dni chcesz zablokować konto");
	define("A_FREEZE2", "Zablokuj");
	define("TOO_MUCH", "Podałeś zbyt długi okres blokady.");
	define("YOU_BLOCK", "Zablokowałeś swoje konto na okres ");
	define("NOW_EXIT", " dni. Ponieważ twoje konto jest już zablokowane, zostałeś wylogowany z gry");
      }

    elseif ($_GET['view'] == 'immu')
      {
	define("IMMU_INFO", "Tutaj możesz samodzielnie dodać sobie immunitet. Immunitet chroni ciebie przed atakami oraz kradzieżami dokonywanymi przez innych graczy, ale sam też nie będziesz mógł atakować ani okradać (dotyczy tylko złodzieja) ich. Na dodatek immunitet jest na stałe, można go zdjąć jedynie resetując postać. Immunitet możesz wybrać dopiero w momencie wyboru klasy postaci. Czy na pewno chcesz immunitet");
	define("YOU_HAVE", "Posiadasz już immunitet!");
	define("YOU_NOT_CLASS", "Musisz najpierw wybrać klasę postaci");
	define("IMMU_SELECT", "Od tej chwili posiadasz immunitet. Kliknij");
	define("HERE", "tutaj");
	define("IMMU_SELECT2", "aby wrócić od opcji konta");
      }

    elseif ($_GET['view'] == "reset") 
      {
	define("RESET_INFO", "Tutaj możesz zresetować swoją postać. Na twój mail zostanie wysłany specjalny link aktywacyjny. Dopiero po kliknięciu na niego twoja postać zostanie zresetowana. W przypadku, kiedy wybierzesz całkowity reset, zostanie jej jedynie id, nick, hasło, mail, profil, poleceni oraz wiek. W przypadku małego resetu zachowasz również ekwipunek, pieniądze oraz dom. W obu przypadkach przepadają takie rzeczy jak kopalnia, strażnica oraz farma.");
	if (isset ($_GET['step']) && $_GET['step'] == 'make') 
	  {
	    define("MESSAGE1", "Dostałeś ten list ponieważ chciałeś zresetować postać. Jeżeli nadal pragniesz zresetować swoją postać na");
	    define("ID", "ID");
	    define("MESSAGE2", "wejdź w ten link");
	    define("MESSAGE3", "Jeżeli jednak nie chcesz resetować postaci (bądź ktoś inny za ciebie zgłosił taką chęć) wejdź w ten link");
	    define("MESSAGE4", "Pozdrawiam");
	    define("MSG_TITLE", "Reset konta gracza na");
	    define("E_MAIL", "Wiadomość nie została wysłana. Błąd");
	    define("E_DB", "Nie mogę wykonać zapytania!");
	    define("RESET_SELECT", "Na twoje konto pocztowe został wysłany mail z prośbą o potwierdzenie resetu postaci");
	  }
      }

    elseif ($_GET['view'] == "avatar") 
      {
	define("REFRESH", "Odśwież");
	define("AVATAR_INFO", "Tutaj możesz zmienić swojego avatara. <b>Uwaga!</b> Jeżeli już posiadasz avatara, stary zostanie skasowany. Maksymalny rozmiar avatara to 30 kB. Avatara możesz załadować tylko z własnego komputera. Musi on mieć rozszerzenie *.jpg, *.jpeg, *.gif lub *.png");
	define("A_DELETE", "Skasuj");
	define("FILE_NAME", "Nazwa pliku graficznego");
	define("A_SELECT", "Wyślij");
	if (isset($_GET['step']))
	  {
	    if ($_GET['step'] == 'usun') 
	      {
		define("E_DB", "Nie mogę skasować!");
		define("DELETED", "Avatar usunięty");
		define("NO_FILE", "Nie ma takiego pliku!");
	      }
	    elseif ($_GET['step'] == 'dodaj') 
	      {
		define("NO_NAME", "Nie podałeś nazwy pliku!");
		define("BAD_TYPE", "Zły typ pliku!");
		define("NOT_COPY", "Nie skopiowano pliku!");
		define("LOADED", "Avatar załadowany");
	      }
	  }
      }

    elseif ($_GET['view'] == "name") 
      {
	define("MY_NAME", "moje imię na");
	if (isset($_GET['step']) && $_GET['step'] == "name") 
	  {
	    define("EMPTY_NAME", "Podaj imię");
	    define("NAME_BLOCK", "To imię jest już zajęte!");
	    define("YOU_CHANGE", "Zmieniłeś imię na");
	  }
      }

    elseif ($_GET['view'] == "pass") 
      {
	define("PASS_INFO", "Nie używaj HTML, ani pojedyńczego cudzysłowu. Nie próbuj go używać, będzie usunięty.");
	define("OLD_PASS", "Obecne hasło");
	define("NEW_PASS", "Nowe hasło");
	if (isset($_GET['step']) && $_GET['step'] == "cp") 
	  {
	    define("YOU_CHANGE", "Zmieniłeś hasło z");
	    define("ON", "na");
	  }
      }

    elseif ($_GET['view'] == "profile") 
      {
	define("PROFILE_INFO", "Dodaj/Modyfikuj swój profil. Nie używaj html ani pojedyńczego cudzysłowu! Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i><[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>");
	define("NEW_PROFILE", "Nowy profil");
	define("NEW_PROFILE2", "Twój nowy profil");
      }

    elseif ($_GET['view'] == 'eci') 
      {
	define("OLD_EMAIL", "Obecny adres e-mail");
	define("NEW_EMAIL", "Nowy adres e-mail");
	define("NEW_GG", "Identyfikator");
	define("COMM1", "Gadu-Gadu");
	define("COMM2", "Tlen");
	define("COMM3", "Jabber");
	define("COMLINK1", "gg:");
	define("COMLINK2", "http://ludzie.tlen.pl/");
	define("COMLINK3", "xmpp:");
	define("T_COMMUNICATOR", "Komunikator");
	define("T_DELETE", "Usuń");
	if (isset($_GET['step']))
	  {
	    if ($_GET['step'] == "gg") 
	      {
		define("GG_BLOCK", "Ktoś już posiada taki identyfikator.");
		define("E_DB", "Nie mogę zapisać w bazie danych!");
		define("YOU_CHANGE", "Zmieniłeś identyfikator komunikatora ");
		define("YOU_DELETE", "Usunąłeś informację o komunikatorze.");
	      }
	    elseif ($_GET['step'] == "ce") 
	      {
		define("BAD_EMAIL", "Nieprawidłowy adres email.");
		define("EMAIL_BLOCK", "Ktoś już posiada taki adres email.");
		define("YOU_CHANGE", "Zmieniasz adres email. Aby dokończyć procedurę, odbierz z nowego adresu wysłany do ciebie list a następnie kliknij w link umieszczony w mailu. Dopiero wtedy zostanie zmieniony ten adres mailowy.");
		define("MESSAGE_PART1", "Dostałeś ten mail, ponieważ chciałeś zmienić hasło w");
		define("MESSAGE_PART2", "Aby aktywować nowy adres email kliknij w link znajdujący się poniżej:");
		define("MESSAGE_PART3", "Życzę miłej zabawy w");
		define("MESSAGE_SUBJECT", "Zmiana adresu email na ");
		define("MESSAGE_NOT_SEND", "Wiadomość nie została wysłana. Błąd:<br />");
	      }
	  }
      }

    elseif ($_GET['view'] == 'style') 
      {
	define("S_SELECT", "Wybierz");
	define("TEXT_STYLE", "tekstowy wygląd gry");
	define("GRAPH_STYLE2", "Możesz również wybrać styl graficzny gry.");
	define("GRAPH_STYLE", "graficzny wygląd gry");
	define("YOU_CHANGE", "Zmieniłeś wygląd gry");
	define("ERROR2", "Błąd!");
	define("REFRESH", "Odśwież");
      }
  }
?>
