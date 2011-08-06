<?php
/**
 *   File functions:
 *   Polish language for news
 *
 *   @name                 : news.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.0
 *   @since                : 20.02.2006
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
// $Id: news.php 566 2006-09-13 09:31:08Z thindil $

define("ERROR", "Zapomnij o tym!");
define("WRITE_BY", "napisana przez");
define("LAST_10", "ostatnie 10 Plotek");
define("A_COMMENTS", "Komentarze");
define("A_ADD_NEWS", "Dodaj nową Plotkę");
define("A_BACK", "Wróć");
define("T_WAITING", "oczekujące");
define("T_ACCEPTED", "zaakceptowane");

if (isset($_GET['step']) && $_GET['step'] == 'comments')
{
    define("C_ADDED", "Komentarz dodany!");
    define("C_DELETED", "Komentarz skasowany!");
    define("NO_COMMENTS", "Nie ma jeszcze komentarzy do tego tekstu!");
    define("A_DELETE", "Skasuj");
    define("ADD_COMMENT", "Dodaj komentarz");
    define("A_ADD", "Dodaj");
    define("NO_PERM", "Nie masz prawa przebywać tutaj!");
    define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
    define("WRITED", "napisał(a)");
}

if (isset($_GET['step']) && $_GET['step'] == 'add')
{
    define("YOU_ADD", "Dodałeś już plotkę do listy oczekujących. Teraz zajmą się nią Książęta.");
    define("ADD_INFO", "Tutaj możesz dodać swoją Plotkę. Pamiętaj aby dokładnie sprawdzić poprawność pisowni. Możesz używać pogrubienia ([b]tekst[/b]), kursywy ([i]tekst[/i]) oraz podkreśleń ([u]tekst[/u]). Pamiętaj też, że Książęta mają prawo poprawić twój tekst jeżeli uznają to za stosowne.<br />Pisząc plotkę staraj się przestrzegać zasad netykiety. Plotka, która obraża jakąś osobę bądź też nie jest utrzymana w klimacie gry zostanie usunięta. Władcy oraz książęta rezerwują sobie prawo do usuwania danej plotki (nawet po pojawieniu się jej) jednak autor(ka) ma prawo domagania się przyczyn takiego stanu rzeczy.");
    define("T_LANG", "Język");
    define("A_ADD", "Dodaj");
    define("T_TITLE", "Tytuł");
    define("T_BODY", "Treść");
    define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
}
?>
