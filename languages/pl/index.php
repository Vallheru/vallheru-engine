<?php
/**
 *   File functions:
 *   Polish language for main site index.php
 *
 *   @name                 : index.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 27.08.2012
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

define("CHARSET", "utf-8");
define("WELCOME", "Witaj");
define("REGISTER", "Rejestracja");
define("RULES", "Zasady");
define("FORUMS", "Forum");
define("REASON", "Przyczyna wyłączenia gry");
define("IRC", "IRC #vallheru");
define("CODEX", "Kodeks");
define("EMAIL", "Email");
define("PASSWORD", "Hasło");
define("LOGIN", "Zaloguj");
define("LOST_PASSWORD", "Zapomniałem hasła");
define("CURRENT_TIME", "Obecny czas");
define("WE_HAVE", "Mamy");
define("REGISTERED", "zarejestrowanych graczy");
define("IN_GAME", "graczy w grze");

if (!isset($_GET['step']))
{
    define("WHAT_IS", "Co to jest");
    define("DESCRIPTION", "Jest to tekstowy RPG dla wielu graczy, rozgrywany turowo. Możesz tutaj walczyć z potworami, innymi graczami, zarządzać własną strażnicą czy też zarabiać pieniądze na własnej kopalni bądź wraz z innymi graczami stworzyć własny klan. Nie spodziewaj się tutaj oszałamiającej grafiki - to gra bardziej na wyobraźnię. Aby w nią grać nie trzeba posiadać potężnego sprzętu czy też ściągać z sieci jakichś programów. Jeżeli zainteresowało Cię to, co do tej pory napisałem, zarejestruj się w grze i dołącz do nas. Zanim jednak to zrobisz, przeczytaj");
    define("CODEX2", "(czyli zasady obowiązujące w grze - aktualizacja ");
    define("NEWS", "Wieści");
    define("DAY", "dnia");
    define("WRITE_BY", "napisana przez");
}

if (isset($_GET['step']) && $_GET['step'] == 'lostpasswd')
{
    define("SUCCESS", "Mail z hasłem oraz linkiem aktywującym został wysłany na podany adres e-mail. Musisz jeszcze aktywować nowe hasło");
    define("LOST_PASSWORD2", "Jeżeli zapomniałeś hasła do swojej postaci, wpisz tutaj swój adres email. Jednak ze względu na to, że hasła w bazie danych są kodowane, niemożliwe jest odzyskanie twojego starego hasła. Dlatego dostaniesz nowe hasło. Jeżeli twoje konto istnieje, informacja o haśle zostanie wysłana pod podany mail. <b>Uwaga!</b> jeżeli masz na swoim koncie włączony filtr anty-spamowy, wyłącz go przed wysłaniem maila, inaczej informacja o haśle nie dojdzie do ciebie!");
    define("SEND", "Wyślij");
    if (isset($_GET['action']) && $_GET['action'] == 'haslo')
    {
        define("ERROR_MAIL", "Podaj adres email.");
        define("ERROR_NOEMAIL", "Nie ma takiego maila w bazie danych.");
        define("MESSAGE_PART1", "Dostałeś ten mail, ponieważ chciałeś zmienić hasło w");
        define("MESSAGE_PART2", "Twoje nowe hasło do konta to");
        define("MESSAGE_PART3", "Aby aktywować nowe hasło kliknij w link znajdujący się poniżej:");
        define("MESSAGE_PART4", "Zmień je jak tylko wejdziesz do gry. Życzę miłej zabawy w");
        define("MESSAGE_SUBJECT", "Przypomnienie hasła na");
        define("MESSAGE_NOT_SEND", "Wiadomość nie została wysłana. Błąd:<br />");
    }
        else
    {
        define("PASS_CHANGED", "Hasło do konta zostało zmienione.");
        define("ERROR", "Zapomnij o tym!");
    }
}

if (isset($_GET['step']) && $_GET['step'] == 'rules')
{
    define("PAGE_NAME", "Kodeks ".$gamename." (zasady)");
}

if (isset($_GET['step']) && $_GET['step'] == 'newemail')
{
    define("ERROR", "Zapomnij o tym!");
    define("MAIL_CHANGED", "Adres email został zmieniony. Zaloguj się teraz do gry korzystając z nowego adresu email.");
}
?>
