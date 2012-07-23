<?php
/**
 *   File functions:
 *   Polish language for weapons shop
 *
 *   @name                 : weapons.php                            
 *   @copyright            : (C) 2004-2005,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 23.07.2012
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

if (isset($_GET['buy']))
{
    define("NO_ITEM", "Nie ma takiego przedmiotu!");
    define("BAD_STATUS", "Tutaj tego nie sprzedasz!");
    define("NO_MONEY", "Nie stać cię na to!");
    define("YOU_PAY", "Zapłaciłeś");
    define("AND_BUY", "sztuk złota, i kupiłeś za to nową");
    define("WITH_P", "z");
    define("TO_POWER", "do Obrażeń.");
}
define("WEAPON_INFO", "Kiedy wchodzisz do dużego domu z szyldem ZBROJMISTRZ dostrzegasz kilku kupców, wojowników i dwóch elfów za ladą. Podchodzisz do jednego z nich a ten pyta: <i>Witaj, czy chcesz kupić u nas oręż? Jeśli tak to powiedz którą z broni wybierasz.</i> W mithrilowej szkatule przez szybę widać wszystkie rodzaje broni jakie wykuwa się w tym zakładzie. Asortyment jest przeogromny - od sztyletów po adamantytowe miecze dwuręczne. Zza firanki za ladą słychać tylko pospieszne kucie broni i krasnoludzkie sapanie <i>Za... Dużo... Tych... Wojaków... Arg... No UKOŃCZONE!</i>. Elf patrząc na Ciebie z uśmiechem mówi tylko: <i>Co podać?</i>");
define("I_NAME", "Nazwa");
define("I_DUR", "Wt");
define("I_EFECT", "Efekt");
define("I_COST", "Cena");
define("I_LEVEL", "Wymagany poziom");
define("I_SPEED", "Szyb");
define("I_OPTION", "Opcje");
define("A_BUY", "Kup");
define("A_STEAL", "Kradzież");
?>
