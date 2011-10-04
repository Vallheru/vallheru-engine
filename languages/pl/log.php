<?php
/**
 *   File functions:
 *   Polish language for players log
 *
 *   @name                 : log.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 04.10.2011
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
define("A_REFRESH", "Odśwież");
define("LOG_INFO2", "Tu jest spis wydarzeń związanych z twoją postacią.");
define("EVENT", "Wydarzenie");
define("E_DATE", "Data");
define("SEND_EVENT", "Wyślij wpis do księcia lub władcy");
define("CLEAR_LOG", "Wyczyść dziennik");
define("DELETED", "Wybrane wpisy zostały wykasowane");
define("A_DELETE", "Kasuj zaznaczone");
define("A_DELETE_OLD", "Kasuj starsze niż");
define("A_WEEK", "tydzień");
define("A_2WEEK", "2 tygodnie");
define("A_MONTH", "30 dni");
define("DELETED2", "Wykasowałeś stare wpisy.");
define("A_PREVIOUS", "Późniejsze wydarzenia");
define("A_NEXT", "Wcześniejsze wydarzenia");
define("NO_PLAYER", "Nie ma takiego gracza!");
define("NOT_STAFF", "Ten gracz nie jest ani władcą ani księciem!");
define("NO_EVENT", "Nie ma takiego wpisu!");
define("NOT_YOUR", "To nie twój wpis!");
define("L_PLAYER", "Gracz ");
define("L_ID", " o ID:");
define("SEND_YOU", " wysłał ci fragment swojego dziennika na pocztę.");
define("L_TITLE", "Fragment dziennika");
define("YOU_SEND", "Wysłałeś(aś) wpis do ");
define("SEND_THIS", "Wyślij ten wpis z dziennika do");
define("A_SEND", "Wyślij");

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'wyczysc') 
{
    define("YOU_CLEAR", "Dziennik wyczyszczony.");
}
?>
