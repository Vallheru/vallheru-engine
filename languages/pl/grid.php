<?php
/**
 *   File functions:
 *   Polish language for labyrynt
 *
 *   @name                 : grid.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 10.08.2011
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
define("R_AGI", "zręczności");
define("R_STR", "siły");
define("R_INT", "inteligencji");
define("R_WIS", "woli");
define("R_SPE", "szybkości");
define("R_CON", "wytrzymałości");

if (isset ($_GET['action']) && $_GET['action'] == 'explore') 
{
    define("NO_ENERGY", "Nie masz wystarczająco energii aby zwiedzać labirynt.");
    define("YOU_DEAD", "Nie możesz zwiedzać labiryntu ponieważ jesteś martwy!");
}

if (!isset($_GET['action']) && !isset($_GET['step']))
{
    define("LAB_INFO", "Idziesz sobie starą drogą gdzie po bokach stoją stare kamieniczki całe z kamienia już prawie zniszczonego. Nagle w dali zauważasz stary, potężny labirynt jest on wykonany z marmuru a jego mury liczą około pięć metrów wysokości. Wejście jest zablokowane kamieniami, chociaż nikt tam nie wchodził od setek lat! Nagle podchodzi do ciebie stary mędrzec i mówi <i>Patrzysz na stary labirynt młodzieńcze. Nikt tam nie wchodził od wielu lat. Wielu mężnych wojowników wchodziło tam zabić potwora. W końcu go zgładzili, lecz gdy chcieli wychodzić zostali zasypani głazami. Szczury na pewno poroznosiły ich skarby, będziesz mógł znaleźć tam interesujące rzeczy a teraz żegnaj</i>. Podchodzisz do wejścia i robisz sobie przejście. Pod gruzami leży wielu martwych rycerzy.<br /> Czy mimo tego chcesz tam wejść?");
    define("A_EXP", "Zwiedzaj");
}
?>
