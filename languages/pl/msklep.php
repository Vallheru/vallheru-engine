<?php
/**
 *   File functions:
 *   Polish language for msklep.php
 *
 *   @name                 : msklep.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
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
define("A_BUY", "Kup");

if (!isset($_GET['buy'])) 
{
    define("POWER", "moc");
	if ($player -> location != 'Ardulith')
	{
		define("WELCOME", "Witaj w sklepie alchemika, możesz tutaj kupić różne przydatne mikstury.");
	}
		else
	{
		define("WELCOME", "Krocząc przepięknym pomostem, który - co stwierdzasz po dotknięciu poręczy - jest żywą gałęzią, stajesz przed drzewem, które znane jest w całym mieście z tego, iż swą siedzibę urządził tutaj najlepszy alchemik w całym królestwie - Servalon. Wchodząc do środka dostrzegasz wysokiego i niezwykle młodego elfa... Słyszałeś plotki, że Servalon ma ponad 900 lat i jest jednym z osadników, który założył miasto... jednakże teraz patrząc na niego, nie możesz dać temu wiary. Słyszałeś również, że Servalon jako jedyny w królestwie stworzył miksturę zdolną zatrzymać starzenie się... Nie wiesz jednak czy obie plotki są fałszywe czy obie prawdziwe... Nagle alchemik odzywa się, przerywając Twe przemyślenia:<br /><br /><i>- Witaj w mojej pracowni. Co chciałbyś nabyć?</i>");
	}
    define("N_NAME", "Nazwa");
    define("N_EFECT", "Efekt");
    define("N_COST", "Cena");
    define("N_AMOUNT", "Ilość");
    define("P_OPTION", "Opcje");
}
    else
{
    define("P_AMOUNT", "sztuk(i)");
    if (isset($_GET['step']) && $_GET['step'] == 'buy')
    {
        define("TOO_MUCH", "Nie ma tyle mikstur w sklepie!");
        define("NO_POTION", "Nie ma takiej mikstury w sklepie!");
        define("NO_TRADE", "Tutaj tego nie sprzedasz.");
        define("NO_MONEY", "Nie stać cię na tyle mikstur!");
        define("E_DB", "Nie mogę dodać do bazy danych!");
        define("YOU_PAY", "Zapłaciłeś");
        define("P_COINS", "sztuk złota i kupiłeś za to");
        define("POTIONS", "nową(ych) <b>mikstur(ę)");
        define("S_BACK", "Wróć do sklepu");
    }
}
?>
