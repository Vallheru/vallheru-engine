<?php
/**
 *   File functions:
 *   Polish language for jeweller shop
 *
 *   @name                 : jewellershop.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 17.07.2006
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
define("SHOP_INFO", "Witaj w sklepie jubilerskim. Możesz tutaj kupić różne magiczne pierścienie.");
define("T_NAME", "Nazwa");
define("T_BONUS", "Premia");
define("T_AMOUNT", "Ilość");
define("T_COST", "Cena");
define("T_ACTION", "Akcja");
define("A_BUY", "Kup"); 

if (isset($_GET['buy']))
{
    define("NO_MONEY", "Nie masz tylu pieniędzy przy sobie.");
    define("NO_RING", "Niestety nie ma tego pierścienia na składzie.");
    define("YOU_BUY", "Kupiłeś <b>");
    define("FOR_A", "</b> za <b>500</b> sztuk złota.");
    define("A_REFRESH", "Odśwież");
}
?>
