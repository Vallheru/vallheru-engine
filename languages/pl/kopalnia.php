<?php
/**
 *   File functions:
 *   Polish language for mines in moutanins
 *
 *   @name                 : kopalnia.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 02.04.2012
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
define("A_SEARCH", "Szukaj");
define("T_MINERALS", "minerałów");
define("T_AMOUNT", "razy.");

if (!isset ($_GET['action'])) 
{
    define("MINES_INFO", "Witaj w kopalniach w górach Kazad-nar, czy chcesz wyruszyć na poszukiwanie minerałów? Każde poszukiwanie zabiera 1 punkt energii.");
}

if (isset($_GET['action']) && $_GET['action'] == 'dig')
{
    define("NO_ENERGY", "Nie masz tyle energii!");
    define("YOU_DEAD", "Nie możesz pracować w kopalni, ponieważ jesteś martwy!");
    define("M_DEAD", "<br /><br />Nagle poczułeś, jak całe wyrobisko powoli zaczyna się rozpadać. Najszybciej jak potrafisz uciekasz w kierunku wyjścia. Niestety, tym razem żywioł okazał się szybszy od ciebie. Potężna lawina kamieni spadła na ciebie,");
    define("M_LUCK", "<br /><br />Nagle poczułeś, jak całe wyrobisko powoli zaczyna się rozpadać. Najszybciej jak potrafisz uciekasz w kierunku wyjścia. W ostatnim momencie udało ci się wybiec z rejonu zagrożenia, poczułeś jedynie na plecach podmuch walących się ton skał.");
    define("MINES_INFO", "Czy chcesz wyruszyć na poszukiwanie minerałów ponownie?");
    define("YOU_GO", "Wybrałeś się na poszukiwanie minerałów ");
    define("T_AMOUNT2", " razy.");
    define("YOU_FIND", "<br /><br />Zdobyłeś:<br /><br />");
    define("T_ABILITY", " punktów do umiejętności górnictwo");
    define("T_CRYSTALS", " kryształów<br />");
    define("T_ADAMANTIUM", " brył adamantium<br />");
    define("T_MITHRIL", " sztuk mithrilu<br />");
    define("T_GOLD", "nieco diamentów wartych ");
    define("T_GOLD2", " sztuk złota<br />");
    define("T_NOTHING", "<br /><br />Niestety nic nie znalazłeś.");
    define("YOU_DEAD2", "Jesteś martwy");
    define("BACK_TO", "Powrót do ".$city1b."");
    define("STAY_HERE", "Pozostań na miejscu");
}
?>
