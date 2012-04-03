<?php
/**
 *   File functions:
 *   Polish language for hospital
 *
 *   @name                 : hospital.php                            
 *   @copyright            : (C) 2004,2005,2007,2012 Vallheru Team based on Gamers-Fusion ver 2.5
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
// $Id$

define("ERROR", "Zapomnij o tym!");

if (!isset ($_GET['action'])) 
{
    define("STOP_WASTE", "Nie marnuj mojego czasu!");
    define("COULD_YOU", "Czy możesz mnie ");
    define("A_HEAL", "uzdrowić");
    define("SURE_IT", "Jasne, twój klan ma zniżkę na leczenie. Będzie cię to kosztowało <b>");
    define("NO_MONEY", "Nie możesz zostać wskrzeszony. Potrzebujesz <b>");
    define("GOLD_COINS", "</b> sztuk złota.");
    define("NO_MONEY2", "Nie możesz być wyleczony. Potrzebujesz <b>");
    define("IT_COST", "Jasne, będzie cię to kosztowało");
    define("COULD_YOU2", "Czy chcesz aby twa dusza wróciła do ciała?");
    define("IT_COST2", "Będzie kosztowało to");
    define("GOLD_COINS2", "sztuk złota.");
}

if (isset ($_GET['action']) && $_GET['action'] == 'heal') 
{
    define("BAD_ACTION", "Musisz być wskrzeszony nie uleczony");
    define("GOLD_COINS", "</b> sztuk złota.");
    define("NO_MONEY", "Nie możesz być wyleczony. Potrzebujesz <b>");
    define("YOU_HEALED", "<br />Jesteś kompletnie wyleczony.");
}

if (isset ($_GET['action']) && $_GET['action'] == 'ressurect') 
{
    define("NO_MONEY_FOR", "Nie możesz być wskrzeszony.");
    define("YOU_RES", "<br>Zostałeś wskrzeszony, ale straciłeś ");
    define("LOST_EXP", " Punktów Doświadczenia.");
    define("NOT_NEED", "Nie potrzebujesz wskrzeszenia!");
}
?>
