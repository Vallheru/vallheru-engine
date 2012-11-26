<?php
/**
 *   File functions:
 *   Polish language for site header
 *
 *   @name                 : head1.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 26.11.2012
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

if (isset($title) && !isset($title1))
{
    $arrTitle = array('Opcje konta', 'Dodaj Plotkę', 'Dodaj Wieść', 'Alchemik', 'Pracownia alchemiczna',
                      'Ekwipunek', 'Panel Administracyjny', 'Dystrybucja AP', 'Płatnerz', 'Bank', 'Arena Walk', 'Łucznik',
                      'Karczma', 'Altara', 'Polana Chowańców', 'Księga czarów', 'Wybierz wyznanie', 'Poszukiwania', 'Farma',
                      'Forum',  'Labirynt', 'Pomoc', 'Rynek ziół', 'Galeria Bohaterów', 'Szpital', 
                      'Domy', 'Rynek z przedmiotami', 'Lochy', 'Wybierz klasę', 'Kuźnia', 
                      'Oczyszczanie miasta', 'Dziennik', 'Tartak', 'Poczta', 'Rynek', 
                      'Lista mieszkańców', 'Rynek z miksturami', 'Posągi', 'Miejskie Plotki', 'Notatnik',
                      'Strażnica', 'Rynek minerałów', 'Portal', 'Wybierz rasę', 'Hala zgromadzeń', 'Odpoczynek', 
                      'Panel Sędziego', 'Statystyki', 'Świątynia', 'Forum klanu', 'Zegar miejski', 'Szkolenie', 'Stajnie',
                      'Zbrojownia klanu', 'Klany', 'Magazyn klanu', 'Zobacz', 'Zbrojmistrz', 'Magiczna wieża', 'Bogactwa', 
                      'Gmach sądu', 'Biblioteka', 'Magazyn Królewski', 'Redakcja gazety', 'Mapa', 'Vallary', 
                      'Góry Kazad-nar', 'Las Avantiel', 'Wyrąb', 'Aleja zasłużonych', 'Astralny skarbiec', 'Astralny rynek', 
                      'Sala audiencyjna', 'Jubiler', 'Rynek jubilerski', 'Kopalnia', 'Kopalnie', 'Huta', 'Astralny plan', 'Warsztat jubilerski', 
		      'Rynek z łupami', 'Galeria Machin', 'Propozycje', 'Gildia Łowców', 'Gildia Rzemieślników', 'Cześnik', 'Aula Gladiatorów', 
		      'Prefektura Gwardii', 'Złodziejska Spelunka', 'Pokój w karczmie', 'Przygoda', 'Rynek chowańców', 'Kronika', 'Drużyna');
        if ($player -> location != 'Ardulith')
        {
            $arrTitle2 = $arrTitle;
            $arrTitle2[13] = $city1;
	    if ($player->location != 'Altara')
	      {
		$arrTitle2[44] = 'Brzoza przeznaczenia';
	      }
        }
        else
        {
            $arrTitle2 = $arrTitle;
            $arrTitle2[13] = $city2;
            $arrTitle2[20] = 'Avan Tirith';
            $arrTitle2[30] = 'Polana drwali';
            $arrTitle2[44] = 'Brzoza przeznaczenia';
            $arrTitle2[62] = 'Leśny skład';
            $arrTitle2[72] = 'Korzeń przeznaczenia';
        }
    $intKey = array_search($title, $arrTitle);
    $title1 = $arrTitle2[$intKey];
}

define("SPELLS_BOOK", "Księga Czarów");

if ($player -> location == 'Altara')
{
    define("CITY", $city1);
    define("B_ARENA", "Arena Walk");
    define("HOSPITAL", "Szpital");
    define("MY_TRIBE", "Mój klan");
    define("BANK", "Bank");
}

if ($player -> location == 'Ardulith')
{
    define("CITY", $city2);
    define("B_ARENA", "Arena Walk");
    define("HOSPITAL", "Szpital");
    define("MY_TRIBE", "Mój klan");
    define("BANK", "Bank");
}

if ($player -> location == 'Podróż')
{
    define("RETURN_TO", "Powrót do przygody");
    define("RETURN_TO2", "Podróż");
}

if ($player -> location == 'Góry')
{
    define("MOUNTAINS", "Góry Kazad-nar");
}

if ($player -> location == 'Las')
{
    define("FOREST", "Las Avantiel");
}

if ($player -> location == 'Lochy')
{
    define("JAIL", "Lochy");
}

if ($player -> location == 'Portal')
{
    define("PORTAL", "Portal");
}

if ($player -> location == 'Astralny plan')
{
    define("ASTRAL_PLAN", "Astralny plan");
}

if ($player -> tribe)
{
    define("T_FORUM", "Forum klanu");
}

define("KING", "Król");
define("PRINCE", "Książę");
define("BACK", "Wróć");

if ($player -> rank == 'Sędzia')
{
    define("JUDGE", "Sędzia");
}

define("ESCAPE", "Kiedy próbowałeś uciec przez płot areny, potwór dopadł ciebie. Poczułeś na karku jego oddech i to była ostatnia rzecz jaką pamiętasz");
define("GAME_TIME", "Czas w grze");
define("LEVEL", "Poziom");
define("EXP_PTS", "PD");
define("HEALTH_PTS", "Zdrowie");
define("MANA_PTS", "Punkty Magii");
define("ENERGY_PTS", "Energia");
define("GOLD_IN_HAND", "Złoto");
define("GOLD_IN_BANK", "Bank");
define("MITHRIL", "Mithril");
define("VALLARS", "Vallary");
define("N_POST", "Poczta");
define("N_FORUMS", "Forum");
define("N_INN", "Karczma");
define("N_OPTIONS", "Opcje konta");
define("N_LOGOUT", "Wylogowanie");
define("N_HELP", "Pomoc");
define("YES", "Tak");
define("NO", "Nie");
define("N_MAP", "Mapa");
?>
