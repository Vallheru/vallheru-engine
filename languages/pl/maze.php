<?php
/**
 *   File functions:
 *   Labyrynth in forrest city
 *
 *   @name                 : maze.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 04.04.2012
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

define("NO_LIFE", "Nie masz wystarczająco dużo życia aby walczyć.");
define("FIGHT2", ". Czy chcesz spróbować walki?");
define("FIGHT1", "Nie możesz wędrować po labiryncie, ponieważ jesteś w trakcie walki!<br />Napotkałeś ");
define("Y_TURN_F", "Tak (turowa walka)");
define("Y_NORM_F", "Tak (szybka walka)");
define("YOU_MEET", "Napotkałeś");
define("A_EXPLORE", "Zwiedzaj");

if (isset($_GET['step']) && $_GET['step'] == 'run') 
{
    define("ESCAPE_SUCC", "Udało ci się uciec przed");
    define("ESCAPE_SUCC2", "Zdobywasz za to");
    define("ESCAPE_SUCC3", "PD");
    define("ESCAPE_FAIL", "Nie udało ci się uciec przed");
    define("ESCAPE_FAIL2", "Rozpoczyna się walka");
    define("R_SPE5", "szybkości");
}

if (!isset($_GET['action']))
{
    define("MAZE_INFO", "Dochodzisz powoli do starego buku. Zauważasz, że dwóch elfickich wartowników pilnuje jakiegoś podziemnego wejścia. Podchodzisz z zamiarem zbadania podziemia, a jeden z nich zagaduje Ciebie:<br />- Czy na pewno chcesz tam wejść? Zanim podejmiesz decyzje powinieneś wiedzieć, że jest to stara i opuszczona siedziba potężnego czarownika Myrdalisa. Podziemia te nigdy nie zostały dokładnie spenetrowane... Nie istnieje żadna mapa która mogła by być pomocna przy znalezieniu tam czegokolwiek. Na dodatek całe podziemia aż roją się od potworów. Czarownik przyzywał często różne demony, a te po jego śmierci stały się całkowicie wolne. Niektóre opuściły podziemia i zostały schwytane bądź zabite, lecz spora ich część nadal się tam znajduje. Zaletą penetracji tych podziemi jest fakt, że można tam znaleźć mnóstwo skarbów, ale czy jesteś gotów podjąć ryzyko?");
}
    else
{
    define("NO_ENERGY", "Nie masz energii!!");
    define("YOU_DEAD", "Nie możesz zwiedzać, ponieważ jesteś martwy!");
    define("T_PINE", " drewna sosnowego.");
    define("T_HAZEL", " drewna z leszczyny.");
    define("T_YEW", " drewna cisowego.");
    define("T_ELM", " drewna z wiązu.");
}
?>
