<?php
/**
 *   File functions:
 *   Polish language for magic tower
 *
 *   @name                 : wieza.php                            
 *   @copyright            : (C) 2004,2005,2007,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 01.06.2012
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

    define("S_POWER", " x Int obrażeń");
    define("S_POWER2", " x SW obrony");
    define("S_POWER3", "Zwiększa siłę przedmiotu");
    define("S_POWER4", "Zwiększa wytrzymałość przedmiotu");
    define("S_POWER5", "Zwiększa premię szybkości lub zręczności przedmiotu");
    define("ST_POWER", "Zwiększa siłę czarów");
    define("C_POWER", " % many");
    if ($player -> location != 'Ardulith')
    {
        define("TOWER_INFO", " Widzisz przed sobą ogromną wieżę stojącą na samym środku miasta. Wchodząc wyczuwasz w niej prawdziwą potęgę jej mistycznej magii. Ściany owijają purpurowe migoczące blaski. Od tego pięknego widoku odrywa cię melodyjny głos <i>Witaj, wiem czego chcesz, chodź za mną.</i>. Idąc za elfim magiem dostrzegasz wiele innych postaci w pięknych jedwabnych szatach praktykujących swoją magię. Dochodzisz do ogromnej biblioteki. <i>Które zaklęcie cię interesuje? Jeśli masz pieniądze mogę wprowadzić cię w wyższe arkana naszej magii, lecz te są dostępne tylko dla magów o wielkich umiejętnościach. Chodź, wybierz drogę magii którą chcesz podążać.</i> Czarów obronnych czy bojowych mogą używać tylko magowie.");
    }
        else
    {
        define("TOWER_INFO", "Widzisz przed sobą ogromną wieżę, wyróżniającą się spośród całego sadu. Wchodząc wyczuwasz w niej prawdziwą potęgę jej mistycznej magii. Ściany owijają purpurowe migoczące blaski. Od tego pięknego widoku odrywa cię melodyjny głos: <br /><i>- Witaj, wiem czego chcesz, chodź za mną...</i><br /> Idąc za elfim magiem dostrzegasz wiele innych postaci w pięknych jedwabnych szatach praktykujących swoją magię. Dochodzisz do ogromnej biblioteki. <br /><i>- Które zaklęcie cię interesuje? A może potrzebujesz nowej różdżki lub szaty? Wszystko jest do Twojej dyspozycji, o ile posiadasz odpowiednie pieniądze i odpowiednie doświadczenie w posługiwaniu się magią.</i>");
    }
    define("A_BUY_S", "Kup czary");
    define("A_BUY_C", "Kup szaty");
    define("A_BUY_ST", "Kup różdżki");
    define("T_NAME", "Nazwa");
    define("T_COST", "Cena");
    define("T_OPTIONS", "Opcje");
    define("T_POWER", "Siła");
    define("T_LEVEL", "Wymagany poziom");
    define("A_BUY", "Kup");

    define("YOU_HAVE", "Masz już taki czar!");
    define("NO_SPELL", "Nie ma takiego czaru!");
    define("TOO_LOW", "Twój poziom jest za niski dla tej rzeczy!");
    define("NO_MONEY", "Nie stać cię!");
    define("ONLY_MAGE", "Tylko mag może używać tego typu czarów!");
    define("YOU_PAY", "Zapłaciłeś <b>");
    define("AND_BUY", "</b> sztuk złota, i kupiłeś za to nowy czar <b>");
    define("NO_ITEM", "Nie ma takiego przedmiotu!");
    define("ONLY_MAGE2", "Tylko mag może używać tych przedmiotów!");
    define("AND_BUY2", "</b> sztuk złota, i kupiłeś za to nowy przedmiot <b>");
?>
