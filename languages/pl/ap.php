<?php
/**
 *   File functions:
 *   Polish language for ap.php
 *
 *   @name                 : ap.php                            
 *   @copyright            : (C) 2004-2005 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 0.8 beta
 *   @since                : 05.03.2005
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

define("NO_CLASS", "Aby rozdysponować AP musisz wpierw wybrać rasę oraz klasę!");
if (isset ($_GET['step']) && $_GET['step'] == 'add') 
{
    define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
    define("ERROR", "Zapomnij o tym!");
    define("NO_AP", "Nie masz tyle Astralnych Punktów!");
    define("NO_AP2", "Nie przeznaczyłeś(aś) jakichkolwiek Astralnych Punktów!");
    define("A_STRENGTH", "Siły");
    define("A_AGILITY", "Zręczności");
    define("A_INTELIGENCE", "Inteligencji");
    define("A_WISDOM", "Siły Woli");
    define("A_SPEED", "Szybkości");
    define("A_CONDITION", "Wytrzymałości");
    define("YOU_GET", "Zyskujesz");
    define("CLICK", "Kliknij");
    define("HERE", "tutaj");
    define("FOR_A", " aby wrócić do statystyk");
}
    else
{
    define("AP_INFO", "Tutaj możesz uzyć AP do zwiększenia swoich statystyk. Po prostu wpisz w odpowiednie pole ile chcesz przeznaczyć AP na daną cechę. Posiadasz");
    define("AP", "AP");
    define("N_STRENGTH", "Siła za 1 ap");
    define("N_AGILITY", "Zręczność za 1 ap");
    define("N_INT", "Inteligencja za 1 ap");
    define("N_WISDOM", "Siła Woli za 1 ap");
    define("N_SPEED", "Szybkość za 1 ap");
    define("N_COND", "Wytrzymałość za 1 ap");
    define("A_ADD", "Dodaj");
}
?>
