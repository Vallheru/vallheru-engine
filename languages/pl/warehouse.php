<?php
/**
 *   File functions:
 *   Polish language for warehouse
 *
 *   @name                 : warehouse.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 19.07.2012
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
define("A_SELL", "Sprzedaj");
define("A_BUY", "Kup");
if ($player -> location != 'Ardulith')
{
    define("WAREHOUSE_INFO", "Wchodzisz do Magazynu Królewskiego. Jesteś pod wrażeniem widząc wnętrze tej ogromnej budowli. Z podziwu wyrywają Cię krzyki. To jakiś krasnolud kłóci się z magazynierem, ostro gestykulując...<br /><br /><i>Przecież surowce z mojej, podkreślam - mojej kopalni,  są więcej warte niż te psie grosze, które mi oferujesz!!! Toż to wyzysk!!! Kiedyś ktoś się wkurzy i puści cały ten wasz magazyn z dymem!!</i><br /><br />Bierze sakwy leżące na stole i wychodzi energicznym krokiem mrucząc pod nosem jakieś przekleństwa. Widzisz jak natychmiast pojawiają się pracownicy i zabierają towar kupiony od krasnoluda. Zauważasz zbliżającego się do Ciebie magazyniera . Na chwilę przystaje. Spogląda na odchodzącego krasnoluda i ze zrezygnowaniem kiwa głową. Po chwili zwraca się do Ciebie.<br /><br /><i>Witam w Królewskim Magazynie. Skupujemy wszelkie surowce, zioła, wszystkich
rodzajów. Oto ceny obowiązujące dzisiaj:</i>");
}
    else
{
    define("WAREHOUSE_INFO", "Schodzisz krętymi schodami w dół. Czujesz lekki swąd palących się świec, a oczy zaczynają ci łzawić. Schody prowadzą do jakiegoś pomieszczenia... Po chwili twoje oczy przyzwyczajają się do półmroku. Widzisz ogromną podziemną halę wypełnioną stertami wszelkiego rodzaju rud, liczne regały z miksturami oraz wielkie wiklinowe kosze wypełnione po brzegi ziołami. Wszystko najlepszej jakości. Zewsząd otacza cię gwar sprzeczek i targów sprzedawców i użerających się z nimi klientów.<br />Nieco onieśmielony podchodzisz do pierwszego stoiska i widzisz uśmiechającego się gnoma ubranego w czerwoną, zdobną szatę.<br /><br />- Czym mogę służyć, Panie? - dochodzi do ciebie miły, służalczy głos...");
}
define("WAREHOUSE_INFO2", "Co możesz nam zaoferować?");
define("WAREHOUSE_INFO3", "Co chcesz kupić?");
define("CARAVAN_VISIT", "<br />Dzisiaj odwiedziła magazyn karawana z dalekich krain wykupując co nieco minerałów.<br /><br />");

define("MIN5A", "Sztabki miedzi");
define("MIN8A", "Sztabki żelaza");
define("MIN10A", "Bryły węgla");
define("MIN11A", "Bryły adamantium");
define("MIN12A", "Kawałki meteorytu");
define("MIN13A", "Kryształów");
define("MIN14A", "Drewno sosnowe");
define("MIN6A", "Sztabki brązu");
define("MIN7A", "Sztabki mosiądzu");
define("MIN9A", "Sztabki stali");
define("MIN15A", "Drewno z leszczyny");
define("MIN16A", "Drewno cisowe");
define("MIN17A", "Drewno z wiązu");
define("MIN18A", "Mithril");
define("MIN1A", "Ruda miedzi");
define("MIN2A", "Ruda cynku");
define("MIN3A", "Ruda cyny");
define("MIN4A", "Ruda żelaza");
define("HERB1A", "Illani");
define("HERB2A", "Illanias");
define("HERB3A", "Nutari");
define("HERB4A", "Dynallca");
define("HERB5A", "Nasiona Illani");
define("HERB6A", "Nasiona Illanias");
define("HERB7A", "Nasiona Nutari");
define("HERB8A", "Nasiona Dynallca");


if (isset($_GET['action']) && ($_GET['action'] == 'sell' || $_GET['action'] == 'buy'))
{
    define("MIN5", "sztabek miedzi");
    define("MIN8", "sztabek żelaza");
    define("MIN10", "brył węgla");
    define("MIN11", "brył adamantium");
    define("MIN12", "kawałków meteorytu");
    define("MIN13", "kryształów");
    define("MIN14", "drewna sosnowego");
    define("MIN6", "sztabek brązu");
    define("MIN7", "sztabek mosiądzu");
    define("MIN9", "sztabek stali");
    define("MIN15", "drewna z leszczyny");
    define("MIN16", "drewna cisowego");
    define("MIN17", "drewna z wiązu");
    define("MIN18", "mithrilu");
    define("MIN1", "rudy miedzi");
    define("MIN2", "rudy cynku");
    define("MIN3", "rudy cyny");
    define("MIN4", "rudy żelaza");
    define("HERB1", "illani");
    define("HERB2", "illanias");
    define("HERB3", "nutari");
    define("HERB4", "dynallca");
    define("HERB5", "nasion illani");
    define("HERB6", "nasion illanias");
    define("HERB7", "nasion nutari");
    define("HERB8", "nasion dynallca");
    define("YOU_SELL", "Sprzedałeś ");
    define("AMOUNT", " sztuk ");
    define("FOR_A", " za ");
    define("GOLD_COINS", " sztuk złota.");
    define("A_BACK", "Wróć");
    define("YOU_HAVE", " spośród posiadanych ");
    define("W_AMOUNT", " w magazynie znajduje się ");
    define("NO_MONEY", "Nie masz tylu sztuk złota przy sobie!");
    define("YOU_BUY", "Kupiłeś ");
    if ($_GET['action'] == 'sell')
    {
        define("NO_AMOUNT", "Nie masz tyle sztuk ");
    }
        else
    {
        define("NO_AMOUNT", "Nie ma tyle sztuk ");
    }
}
?>
