<?php
/**
 *   File functions:
 *   Polish language for player stats
 *
 *   @name                 : stats.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 29.09.2011
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

define("A_USE", "Użyj");
define("A_SELECT", "Wybierz");
define("A_CHANGE", "Zmień");
define("GENDER_M", "Mężczyzna");
define("GENDER_F", "Kobieta");
define("A_REST", "Odpocznij");
define("CRIME_T", "Ilość kradzieży:");
define("GG_NUM", "Komunikator ");
define("TRIBE_RANK", "Ranga w klanie:");
define("NOTHING", "brak");
define("STATS_INFO", "Witaj w swoich statystykach. Możesz tutaj zobaczyć informacje na temat swojej postaci w grze.");
define("T_STATS", "Statystyki w grze");
define("T_AP", "AP");
define("T_RACE", "Rasa");
define("T_CLASS2", "Klasa");
define("T_DEITY", "Wyznanie");
define("T_GENDER", "Płeć");
define("T_AGI", "Zręczność");
define("T_STR", "Siła");
define("T_INT", "Inteligencja");
define("T_WIS", "Siła Woli");
define("T_SPEED", "Szybkość");
define("T_CON", "Wytrzymałość");
define("T_MANA", "Punkty Magii");
define("T_PW", "Punkty Wiary");
define("T_FIGHTS", "Wyniki");
define("T_LAST", "Ostatnio zabity");
define("T_LAST2", "Ostatnio zabity przez");
define("T_INFO", "Informacje");
define("T_RANK", "Ranga");
define("T_LOC", "Lokacja");
define("T_AGE", "Wiek");
define("T_LOGINS", "Logowań");
define("T_IP", "IP");
define("T_EMAIL", "Email");
define("T_CLAN", "Klan");
define("T_ABILITY", "Umiejętności");
define("T_SMITH", "Kowalstwo");
define("T_ALCHEMY", "Alchemia");
define("T_LUMBER", "Stolarstwo");
define("T_FIGHT", "Walka bronią");
define("T_SHOOT", "Strzelectwo");
define("T_DODGE", "Uniki");
define("T_CAST", "Rzucanie czarów");
define("T_LEADER", "Dowodzenie");
define("T_MINING", "Górnictwo");
define("T_LUMBERJACK", "Drwalnictwo");
define("T_HERBALIST", "Zielarstwo");
define("AGI", "Zręczności");
define("STR", "Siły");
define("INTELI", "Inteligencji");
define("WIS", "Siły Woli");
define("SPE", "Szybkości");
define("CON", "Wytrzymałości");
define("SMI", "Kowalstwa");
define("ALC", "Alchemii");
define("FLE", "Stolarstwa");
define("WEA", "Walki bronią");
define("SHO", "Strzelectwa");
define("DOD", "Uników");
define("CAS", "Rzucania czarów");
define("BRE", "Hodowli");
define("MINI", "Górnictwa");
define("LUMBER", "Drwalnictwa");
define("HERBS", "Zielarstwa");
define("JEWEL", "Jubilerstwa");
define("BLESS_FOR", "Błogosławieństwo do ");
define("T_BREEDING", "Hodowla");
define("T_JEWELLER", "Jubilerstwo");
define("R_AGI", "zręczności");
define("R_STR", "siły");
define("R_INT", "inteligencji");
define("R_WIS", "woli");
define("R_SPE", "szybkości");
define("R_CON", "wytrzymałości");

if (isset ($_GET['action']) && $_GET['action'] == 'gender') 
{
    define("YOU_HAVE", "Masz już wybraną płeć!");
    define("NO_GENDER", "Wybierz płeć!");
    define("YOU_SELECT", "Wybrałeś płeć. <a href=stats.php>Odśwież</a>");
}
?>
