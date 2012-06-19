<?php
/**
 *   File functions:
 *   Polish language for armor shop
 *
 *   @name                 : armor.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 19.06.2012
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

if (isset($_GET['buy']))
{
    define("NO_ITEM", "Nie ma takiego przedmiotu!");
    define("BAD_STATUS", "Tutaj tego nie sprzedasz!");
    define("NO_MONEY", "Nie stać cię na to!");
    define("YOU_PAY", "Zapłaciłeś");
    define("AND_BUY", "sztuk złota, i kupiłeś za to nową");
    define("I_POWER", "z Obroną");
}
define("ARMOR_INFO", " Stoisz przed małym sklepem z bogato ozdobionym szyldem PŁATNERZ. Kiedy wchodzisz do środka widzisz kilka stojaków z żelaznymi i stalowymi zbrojami i kolczugami. Za ladą z narzędziami płatnerskimi stoi stary aczkolwiek tęgi i silny krasnolud. <i>Witaj. W czym stary Brodur może ci pomóc? Mam wszystko co tu widzisz chyba ze szukasz czegoś droższego...</i> Mówiąc to odsłonił małą firankę na zaplecze gdzie na manekinach powieszone były wspaniale mithrilowe i meteorytowe, adamantowe a nawet kryształowe pełne zbroje. <i>Więc? Bierzesz coś?</i>");
define("A_ARMORS", "Zbroje");
define("A_HELMETS", "Hełmy");
define("A_LEGS", "Nagolenniki");
define("A_SHIELDS", "Tarcze");
define("I_NAME", "Nazwa");
define("I_DUR", "Wt");
define("I_EFECT", "Efekt");
define("I_COST", "Cena");
define("I_LEVEL", "Wymagany poziom");
define("I_AGI", "Ograniczenie zręczności");
define("I_OPTION", "Opcje");
define("A_BUY", "Kup");
define("A_STEAL", "Kradzież");
?>
