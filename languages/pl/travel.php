<?php
/**
 *   File functions:
 *   Polish language for travel
 *
 *   @name                 : travel.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 14.09.2011
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

define("MESSAGE1", "Podróż przebiegała spokojnie, po pewnym czasie widzisz przed sobą cel swej podróży.");
define("MESSAGE3", "Niestety tym razem przeznaczenie okazało się silniejsze od ciebie. Ostatnim obrazem jaki pamiętasz, to spadająca na twoją głowę szabla bandyty. Budzisz się w szpitalu w ".$city1a.". Kliknij <a href=hospital.php>tutaj</a>");
define("MESSAGE4", "Po pokonaniu przeciwników karawana ponownie wyrusza w drogę. Tym razem przebiega ona bez niespodzianek.");
define("NO_MONEY", "Nie masz tyle pieniędzy!");
define("YOU_DEAD", "Nie możesz podróżować, ponieważ jesteś martwy!");
define("NO_ENERGY3", "Nie masz energii aby podróżować!");

if (!isset($_GET['akcja']) && !isset($_GET['action'])) 
{
    define("ALTARA_INFO", "Witaj w Stajniach. Stąd możesz wyruszyć do innych miejsc świata Vallheru.");
    define("A_FOREST", "Las Avantiel");
    define("A_ELFCITY", $city2);
    define("A_MOUNTAINS", "Góry Kazad-nar");
    define("PORTAL1", "Nieco na uboczu dostrzegasz dziwnego starca przyglądającego się tobie z ciekawością. Kiedy zauważa że mu się przyglądasz, daje znak ręką abyś podszedł do niego. Obaj odchodzicie ze stajni w niewielką boczną uliczkę ".$city1b.". Kiedy zatrzymujecie się starzec cichym głosem mówi: <i> Wyczuwam, iż chcesz wyruszyć na wyprawę po skarby dawno zaginione. Wiem że masz przy sobie odpowiednią rzecz, która pozwoli Ci znaleźć rzeczy Pierwszych. Jeżeli chcesz mogę cię przenieść w odpowiednie miejsce. Lecz uważaj - skarbów strzeże niebezpieczne istota pozostawiona  tam przez właścicieli. Czy chcesz wyruszyć w to miejsce?</i>");
    define("OUTSIDE", "Witaj w Stajniach. Tędy możesz wrócić do stolicy Vallheru, ".$city1b.".");
    define("A_ALTARA", "Jedź do ".$city1b."");
    define("MAP1", "Plan demoniczny");
    define("MAP2", "Plan ognisty");
    define("MAP3", "Plan piekielny");
    define("MAP4", "Plan pustynny");
    define("MAP5", "Plan wodny");
    define("MAP6", "Plan niebiański");
    define("MAP7", "Plan śmiertelny");
    define("T_PORTALS", "Na jednej ze ścian stajni nagle zauważasz magiczne portale. Domyślasz się że prowadzą one do nieznanych, niebezpiecznych światów, a to że obecnie działają jest zasługą tego iż posiadasz przy sobie skompletowane mapy. Powoli podchodzisz w tym kierunku i przechodzisz przez portal na...");
}

if (isset($_GET['action']))
{
    if (($player -> location == 'Ardulith' && $_GET['action'] == 'gory') || ($player -> location == 'Góry' && $_GET['action'] == 'las'))
    {
        define("A_CARAVAN", "Podróżuj z karawaną (1200 sztuk złota)");
        define("A_WALK", "Idź na piechotę (6 punktów energii)");
    }
        else
    {
        define("A_CARAVAN", "Podróżuj z karawaną (1000 sztuk złota)");
        define("A_WALK", "Idź na piechotę (5 punktów energii)");
    }
    define("A_BACK", "Wróć");
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'gory') 
{
    define("YOU_REACH", " Dotarłeś do Gór Kazad-nar. Wejdź <a href=gory.php>tutaj</a> aby zobaczyć co ciebie czeka.");
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'las') 
{
    define("YOU_REACH", " Dotarłeś do Lasu Avantiel. Wejdź <a href=las.php>tutaj</a> aby zobaczyć co ciebie czeka.");
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'city2') 
{
    define("YOU_REACH", " Dotarłeś do ".$city2.". Wejdź <a href=city.php>tutaj</a> aby zobaczyć co ciebie czeka.");
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'powrot') 
{
    define("YOU_REACH", " Dotarłeś do ".$city1b.". Wejdź <a href=city.php>tutaj</a> aby zobaczyć co ciebie czeka.");
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'tak')
{
    define("PORTAL2", "Starzec zamyka oczy i bezgłośnie zaczyna wymawiać jakieś zaklęcie, kreśląc rękami w powietrzu dziwne wzory. Nagle tuż przed tobą otwiera się zielony portal. Starzec patrzy na ciebie i mówi: <i>Ruszaj więc po swoje przeznaczenie i niech szczęśćie ci sprzyja.</i> Niepewnie przechodzisz przez <a href=\"portal.php\">portal</a>");
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'nie')
{
    define("PORTAL3", "Starzec patrzy na ciebie przez chwilę i odpowiada: <i>Dobrze więc, jeżeli będziesz chciał wyruszyć na poszukiwania przybądź w to samo miejsce gdzie spotkaliśmy się za pierwszym razem. Będę tam na ciebie czekał.</i> Po tych słowach odchodzi. Ty natomiast wracasz do <a href=\"city.php\">miasta</a>");
}
?>
