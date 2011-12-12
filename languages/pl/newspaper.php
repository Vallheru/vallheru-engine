<?php
/**
 *   File functions:
 *   Polish language for newspaper
 *
 *   @name                 : newspaper.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 12.12.2011
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
define("A_BACK", "Wróć");
define("A_NEWS", "Nowości");
define("A_NEWS2", "Ploty");
define("A_COURT","Sąd Najwyższy");
define("A_ROYAL", "Rada Królewska");
define("A_KING", "Serwis władców");
define("A_CHRONICLE", "Kronika");
define("A_SENSATIONS", "Sensacje");
define("A_HUMOR", "Humor");
define("A_INTER", "Wywiady");
define("A_NEWS3", "Ogłoszenia drobne");
define("A_POETRY", "Twórczość");
define("NO_PERM", "Nie masz prawa przebywać tutaj!");
define("A_PUBLIC", "Publikuj najnowszy numer");
define("A_AUTHOR", "Autor");
define("T_TITLE", "Tytuł:");
define("A_NEXT", "Następna strona");
define("A_PREVIOUS", "Poprzednia strona");
define("A_DELETE", "Kasuj");

if (isset($_GET['comments']))
{
    define("C_ADDED", "Komentarz dodany!");
    define("C_DELETED", "Komentarz skasowany!");
    define("NO_COMMENTS", "Nie ma jeszcze komentarzy do tego tekstu!");
    define("ADD_COMMENT", "Dodaj komentarz");
    define("A_ADD", "Dodaj");
    define("WROTE", "napisał(a)");
}

if (!isset($_GET['comments']) && !isset($_GET['step']) && !isset($_GET['read']))
{
    define("PAPERINFO", "Witaj w redakcji gazety");
    define("PAPERINFO2", "- Vallweek. Możesz tutaj przeczytać najnowsze wydanie tygodnika, pracowicie tworzone przez grupę ochotników.");
    define("PAPERINFO3", "W dziale \"Archiwum\" znajdziesz wcześniejsze prace zespołu redakcyjnego.");
    define("A_NEW_PAPER", "Czytaj najnowszy numer Vallweeka");
    define("A_ARCHIVE", "Archiwum Vallweeka");
    define("A_REDACTION", "Redakcja Vallweeka");
    define("A_RED_MAIL", "Skrzynka pocztowa");
}

if (isset($_GET['step']) && $_GET['step'] == 'archive')
{
    define("ARCHIVEINFO", "Oto lista numerów archiwalnych Vallweeka");
    define("A_NUMBER", "Numer");
    define("EMPTY_ARCHIVE", "Archiwum jest puste!");
}

if ((isset($_GET['step']) && $_GET['step'] == 'new') || isset($_GET['read']) || (isset($_GET['step3']) && $_GET['step3'] == 'S'))
{
    define("A_COMMENT", "Skomentuj");
    define("T_WRITE", "napisano");
    define("T_COMMENTS", "komentarzy");
    define("READINFO", " Wybierz artykuł który chcesz przeczytać.");
    define("A_EDIT", "Edytuj");
    define("NO_PAPER", "Nie ma jeszcze wydań gazety!");
    define("A_MAIN", "Strona główna");
    define("A_CONTENTS", "Spis treści");
    define("A_END", "Zakończ czytanie");
}

if (isset($_GET['step']) && $_GET['step'] == 'redaction')
{
    define("REDACTIONINFO", "Tutaj znajduje się redakcja gazety. Może tutaj dokonywać poprawek w kolejnych wydaniach gazety oraz czytać pocztę wysłaną przez innych mieszkańców");
    define("A_SHOW", "Podgląd najnowszego numeru");
    define("A_REDACTION", "Dodaj nowy artykuł");
    define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
    define("T_BODY", "Treść:");
    define("MAIL_TYPE", "Dział gazety");
    define("A_SHOW2", "Podgląd");
    define("EDITED_BY", "Zmieniony przez ");
    if (isset($_GET['step3']) && $_GET['step3'] != 'R')
    {
        define("YOU_EDIT", "Edytujesz istniejący artykuł");
        define("MAIL_SEND", "Artykuł został zmodyfikowany.");
        define("A_SEND", "Modyfikuj");
    }
        else
    {
        define("YOU_EDIT", "Napisz nowy artykuł");
        define("MAIL_SEND", "Artykuł został dodany.");
        define("A_SEND", "Dodaj");
    }
    define("NEWSPAPER_RELEASED", "Opublikowałeś najnowszy numer Vallweeka");
    define("ARTICLE_DELETED", "Artykuł został wykasowany");
}

if (isset($_GET['step']) && $_GET['step'] == 'mail')
{
    define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
    define("T_BODY", "Treść:");
    define("MAILINFO", "W skrzynce pocztowej możesz przesyłać redaktorom swoje artykuły oraz ogłoszenia do gazety.");
    define("MAIL_TYPE", "Rodzaj przesyłki");
    define("A_SHOW", "Podgląd");
    define("A_SEND", "Wyślij");
    define("MAIL_SEND", "Poczta została wysłana.");
}
?>
