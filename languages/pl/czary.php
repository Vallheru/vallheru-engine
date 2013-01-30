<?php
/**
 *   File functions:
 *   Polish language czary.php
 *
 *   @name                 : czary.php                            
 *   @copyright            : (C) 2004,2005,2006,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 30.01.2013
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

define("B_SPELL", "Czar bojowy: ");
define("B_DAMAGE", "Inteligencja obrażeń");
define("S_DEACTIV", "Dezaktywuj czar");
define("S_NONE", "brak");
define("D_SPELL", "Czar obronny: ");
define("D_DEFENSE", "Siła Woli obrony");
define("ERROR", "Zapomnij o tym!");
define("NO_SPELL", "Nie posiadasz takiego czaru!");
define("NOT_YOUR", "To nie twój czar!");
define("E_SPELL1", "Ulepszenie przedmiotu");
define("E_SPELL2", "Utwardzenie przedmiotu");
define("E_SPELL3", "Umagicznienie przedmiotu");
define("S_EFECT1", "Zwiększa siłę przedmiotu");
define("S_EFECT2", "Zwiększa wytrzymałość przedmiotu");
define("S_EFECT3", "Zwiększa premię szybkości lub zręczności przedmiotu");
define("TO_LOW_L", "Nie masz odpowiednio wysokiego poziomu!");
define("S_REFRESH", "odśwież");
define("USED_SPELLS", "Obecnie używane czary");
define("SPELL_BOOK", "Czary w księdze");
define("B_SPELLS", "Czary bojowe");
define("D_SPELLS", "Czary obronne");
define("E_SPELLS", "Czary użytkowe");
define("USE_THIS", "Używaj tego czaru");
define("CAST_THIS", "Rzuć ten czar");

if (isset($_GET['cast'])) 
{
    define("NO_MANA", "Nie masz tyle punktów magii!");
    define("YOU_BARBARIAN", "Nie możesz używać czarów ponieważ jesteś Barbarzyńcą!");
    define("NO_ITEM", "Nie ma takiego przedmiotu!");
    define("NOT_YOUR2", "Ten przedmiot nie należy do ciebie!");
    define("IS_MAGIC", "Ten przedmiot jest już umagiczniony!");
    define("NO_ENERGY", "Nie masz tyle energii!");
    define("NO_ENCHANCE", "Nie możesz umagiczniać tego przedmiotu!");
    define("YOU_RISE", "Zwiększyłeś siłę ");
    define("FOR_A", " o ");
    define("NOW_IS", "! Jest to teraz przedmiot magiczny! Zdobyłeś ");
    define("S_EXP", " Punktów Doświadczenia<br />");
    define("E_DB", "Nie mogę dodać");
    define("YOU_TRY", "Próbowałeś umagicznić ");
    define("BUT_FAIL", " ale niestety nie udało się. Na skutek nieudanego zaklęcia przedmiot niszczy się!");
    define("NOT_ABLE1", "Nie można zwiększyć siły łuków");
    define("NOT_ABLE2", "Nie można zwiększyć wytrzymałości strzał");
    define("YOU_RISE2", "Zwiększyłeś wytrzymałość ");
    define("YOU_RISE3", "Zwiększyłeś premię z szybkości");
    define("YOU_RISE4", "Zmniejszyłeś ograniczenie zręczości");
    define("NOT_ABLE3", "Nie możesz umagicznić tego przedmiotu!");
    define("CAST", "Rzuć");
    define("SPELL", "czar");
    define("ON_A", "na");
    define("I_AMOUNT", "sztuk");
    define("I_DRAGON" ,"Smoczy ");
    define("I_DRAGON2", "Smocza ");
    define("I_DRAGON3", "Smocze ");
    define("I_ELVES", "Elfi ");
    define("I_ELVES2", "Elfie ");
    define("I_ELVES3", "Elfia ");
    define("I_DWARVES", "Krasnoludzki ");
    define("I_DWARVES2", "Krasnoludzka ");
    define("I_DWARVES3", "Krasnoludzkie ");
    define("I_COPPER", " z miedzi");
    define("I_BRONZE", " z brązu");
    define("I_BRASS", " z mosiądzu");
    define("I_IRON" ," z żelaza");
    define("I_STEEL", " ze stali");
    define("I_HAZEL", "z leszczyny");
    define("I_YEW", "z cisu");
    define("I_ELM", "z wiązu");
    define("I_HARDER", "wzmocniony");
    define("I_COMPOSITE", "kompozytowy");
}

if (isset($_GET['naucz'])) 
{
    define("ONLY_MAGE", "Tylko mag może używać czarów!");
    define("YOU_USE", "Używasz");
}
?>
