<?php
/**
 *   File functions:
 *   Polish language for equipment
 *
 *   @name                 : equip.php                            
 *   @copyright            : (C) 2004,2005,2006,2007 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 21.02.2007
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
// $Id: equip.php 890 2007-02-21 19:37:53Z thindil $

define("IN_BACKPACK", "Zapasowe");
define("EQUIP_AGI", "zr");
define("EQUIP_SPEED", "szyb");
define("AMOUNT", "Ilość");
define("EQUIP_MANA", "punktów magii");
define("A_WEAR", "załóż");
define("A_SELL", "sprzedaj");
define("GOLD_COINS", "sztuk złota");
define("SPELL_POWER", "Zwiększa siłę czarów");
define("A_REPAIR", "napraw");
define("FOR_A", "za");
define("DURABILITY", "wytrzymałości");
define("A_SELL_ALL", "sprzedaj wszystkie zapasowe");
define("WEAPON", "Broń");
define("EMPTY_SLOT", "brak");
define("HIDE_WEP", "schowaj broń");
define("STAFF", "Różdżka");
define("HIDE", "schowaj");
define("QUIVER", "Kołczan");
define("ARROWS", "strzał");
define("HIDE_ARR", "schowaj strzały");
define("HELMET", "Hełm");
define("WEAR_OFF", "zdejmij");
define("ARMOR", "Zbroja");
define("CAPE", "Szata");
define("SHIELD", "Tarcza");
define("LEGS", "Nagolenniki");
define("RING", "Pierścień");
define("ERROR", "Zapomnij o tym!");
define("A_REPAIR2", "napraw używane");
define("WEAPONS", "bronie");
define("STAFFS", "różdżki");
define("BACK_QUIVER", "Zapasowe kołczany");
define("HELMETS", "hełmy");
define("ARMORS", "zbroje");
define("LEGS2", "nagolenniki");
define("CAPES", "szaty");
define("SHIELDS", "tarcze");
define("A_DRINK", "wypij");
define("A_POISON", "zatruj broń");
define("POWER", "moc");
define("NO_ITEMS", "Nie masz takiego przedmiotu!\n");
define("NOT_YOUR", "To nie twój przedmiot!\n");
define("REFRESH", "odśwież");
define("ARROWS2", "strzały");
define("EQUIPPED", "Obecnie używane przedmioty");
define("SELL_ALL_ARR", "sprzedaj wszystkie zapasowe kołczany strzał");
define("POTIONS", "Mikstury");
define("ARR_AMOUNT", "sztuk");
define("A_FILL", "uzupełnij");
define("BACK_RINGS", "Zapasowe pierścienie");
define("SELL_ALL_RINGS", "sprzedaj wszystkie zapasowe pierścienie");
define("RINGS", "pierścienie");
define("NO_ITEM", "Nie masz takiego przedmiotu!");

if (isset($_GET['schowaj']))
{ 
    define("E_DB", "Nie mogę zdjąć!"); 
}

if (isset($_GET['sell']))
{
    define("BROKEN_ITEM", "Nie możesz sprzedać uszkodzonego przedmiotu!");
    define("YOU_SELL", "Sprzedałeś");
}

if (isset($_GET['sprzedaj']))
{
    define("NO_ITEMS2", "Nie masz rzeczy na sprzedaż!\n");
    define("YOU_SELL", "Sprzedałeś wszystkie zapasowe");
}

if (isset($_GET['napraw_uzywane']))
{
    define("NO_MONEY", "Nie stać cię na to!");
    define("YOU_REPAIR", "Naprawiłeś");
    define("AND_COST", "i kosztowało cię to");
}

if (isset($_GET['napraw']))
{
    define("NO_REPAIR", "Ta rzecz nie wymaga naprawy!\n");
    define("NO_MONEY", "Nie stać cię na to!\n");
    define("E_REPAIR", "Nie możesz naprawiać strzał!\n");
    define("YOU_REPAIR", "Naprawiłeś");
}

if (isset($_GET['poison']))
{
    define("NO_POTION", "Nie masz takiej mikstury!");
    define("NO_WEAPON", "To nie jest broń!");
    define("IS_POISONED", "Ten przedmiot jest już zatruty");
    define("NOT_IN", "Nie masz tego przedmiotu w plecaku!");
    define("POISONED", "Zatruty");
    define("YOU_POISON", "Zatrułeś");
    define("POISON_IT", "Zatruj");
    define("YOU_DESTROY", "Kiedy polałeś trucizną ostrze, nagle zaczęło ono się topić. Przerażony próbowałeś usunąć miksturę z broni, lecz niestety nie udało się. Twoja broń została kompletnie zniszczona.");
    define("YOU_POISON2", "Próbowałeś zatruć broń");
    define("BUT_NOT", "lecz niestety ta nie zadziałała");
}

if (isset($_GET['fill']))
{
    define("NOT_NEED", "Nie musisz uzupełniać kołczanu!");
    define("YOU_FILL", "Uzupełniłeś kołczan.");
    define("NO_ARROWS", "Nie masz zapasowych strzał!");
}
?>
