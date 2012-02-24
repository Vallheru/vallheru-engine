<?php
/**
 *   File functions:
 *   Polish language for farms
 *
 *   @name                 : farm.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 24.02.2012
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
define("A_BACK", "Wróć");
define("T_AMOUNT", "ilość:");
define("NO_ENERGY", "Nie masz tyle energii!");

if (!isset($_GET['step']))
{
    define("FARM_INFO", "Witaj na farmach. Możesz tutaj hodować zioła z których później wyrabia się mikstury.");
    define("A_PLANTATION", "Plantacja");
    define("A_HOUSE", "Chatka ogrodnika");
    define("A_ENCYCLOPEDIA", "Encyklopedia roślin");
}

if (isset($_GET['step']) && $_GET['step'] == 'herbsinfo')
{
    define("HERBS_INFO", "Encyklopedia roślin");
    define("ILANI_INFO", "Illani jest roslina zielną, rosnącą czesto dziko na polanach w różnych miejscach ".$gamename.". W zalezności od miejsca przyjmuje różne rozmiary i formy, od ledwie widocznych wśród traw liści w górach, po dobrze wykształcone rosliny w niższych częściach krainy. Kwitnie na żołto, a po zapyleniu produkuje drobne nasiona w pekających owocach.<br />Łatwa i tania w uprawie, gdyż nie potrzebuje specjalnych zabiegow pielęgnacyjnych, ani dodatkowego sprzętu w hodowli. ");
    define("ILLANIAS_INFO", "Illanias jest rośliną zielną, ciepłolubna. Rośnie w ciepłych lasach, w miejscach takich jak dobrze nasłonecznione polany. Jest dwupienna, co oznacza, że do otrzymania nasion potrzebne są osobniki żeńskie i męskie. Oba osobniki wyglądają podobnie - około 20 centymetrowe, podłużne liście wyrastające bezpośrednio z ziemi. W okresie kwitnienia wypuszczają łodygi na których rozwijają się kwiaty. Żeńskie kwiaty są niedużymi szyszkami, męskie to zawieszone na nitkowatych tworach skupiska pylników.<br />Do uprawy w klimacie ".$gamename." niezbędna jest szklarnia, co podwyższa nieco koszty uprawy. Dziko rośnie słabo i nie kwitnie.");
    define("NUTARI_INFO", "Nutari jest tropikalnym mchem bagiennym. Rośnie w miejscach bardzo wilgotnych i nie nasłonecznionych. Roślina ma około 10 centymetrów wysokości i jest ulistnioną łodyżką. Rośnie w dużych skupiskach. Do uprawy w warunkach ".$gamename." niezbędna jest szklarnia i system nawadniający.");
    define("DYNALLCA_INFO", "Dynallca to pnącze rosnące w lasach tropikalnych przypominające trochę bluszcz. W warunkach naturalnych oplata się wokół drzew. Najczęściej rośnie w miejscach dobrze nawodnionych, często zapuszcza korzenie na podmokłych terenach. Wykształciła system korzeni oddechowych, dzięki którym może w takich warunkach egzystować. Kwitnie na biało, wypuszczając małe kwiatki o średnicy nie większej, niż 0,5 centymetra. Na ".$gamename." hodowana jest w szklarniach, na specjalnych konstrukcjach nośnych. Wymaga także systemu nawadniającego.");
}

if (isset($_GET['step']) && $_GET['step'] == 'house')
{
    define("HOUSE_INFO", "Witaj w chatce ogrodnika. Tutaj możesz suszyć zioła, aby otrzymać z nich nasiona potrzebne do zasiania plantacji. Za każde 10 ziół danego rodzaju otrzymujesz 1 paczkę nasion. Koszt suszenia ziół to 0.2 enegii za każdą paczkę ziół");
    define("A_DRY", "Wysusz");
    define("T_DRY", "aby otrzymać");
    define("T_PACK", "paczek nasion");
    define("HERB1", "Illani");
    define("HERB2", "Illanias");
    define("HERB3", "Nutari");
    define("HERB4", "Dynallca");
    define("NO_HERBS", "Nie masz ziół do suszenia!");
    if (isset($_GET['action']) && $_GET['action'] == 'dry')
    {
        define("NO_HERB", "Nie masz takiej ilości ziół!");
        define("YOU_DEAD", "Nie możesz suszyć ziół ponieważ jesteś martwy!");
        define("YOU_MAKE", "Wysuszyłeś <b>");
        define("T_HERB", "</b> sztuk ziół i otrzymaleś w zamian <b>");
        define("T_PACKS", "</b> paczek nasion.");
    }
}

if (isset($_GET['step']) && $_GET['step'] == 'plantation')
{
    define("NO_PLANT", "Nie masz jeszcze plantacji - kup ziemię pod nią za 20 sztuk mithrilu");
    define("A_UPGRADE", "Rozbuduj plantację");
    define("A_SOW", "Idź zasiać zioła");
    define("A_CHOP", "Zbieraj zioła");
    define("I_LANDS", "Obszarów farmy:");
    define("I_GLASS", "Szklarni:");
    define("I_IRRIGATION", "Systemów nawadniających:");
    define("I_CREEPER", "Konstrukcji na pnącza:");
    define("FREE_LANDS", "Wolnych obszarów:");
    define("T_AGE", "wiek:");
    define("FARM_INFO", "Witaj na plantacji. Tutaj możesz hodować różne zioła.");
    if (isset($_GET['action']) && $_GET['action'] == 'upgrade')
    {
        define("BUY_LAND", "Zakup obszar ziemi za");
        define("T_MITH", "sztuk mithrilu.");
        define("BUY_GLASS", "Zakup szklarnię za");
        define("BUY_IRRIGATION", "Zakup system nawadniania za");
        define("BUY_CREEPER", "Zakup konstrukcje na pnącza za");
        define("T_GOLDCOINS", "sztuk złota.");
        if (isset($_GET['buy']))
        {
            define("NO_MITH", "Nie masz takiej ilości mithrilu!");
            define("BUYING_LAND", " nowy obszar ziemi do swojej farmy.");
            define("YOU_BUY", "Dokupiłeś");
            define("NO_MONEY", "Nie masz tylu pieniędzy przy sobie aby dokonać zakupu.");
            define("NO_LANDS", "Nie masz miejsca aby dokupić kolejne ulepszenia do farmy.");
            define("BUYING_GLASS", " nową szklarnię do swojej farmy.");
            define("BUYING_IRRIGATION", " nowy system nawadniający do swojej farmy.");
            define("BUYING_CREEPER", " nową konstrukcję na pnącza do swojej farmy.");
        }
    }
    if (isset($_GET['action']) && $_GET['action'] == 'sow')
    {
        define("SAW_INFO", "Tutaj możesz zasiewać swoją farmę. Jedna paczka nasion starcza na zasianie 1 obszaru. Aby móc hodować odpowiednie zioła, musisz również posiadać odpowiednie wyposażenie. Listę wymaganych rzeczy możesz znaleźć w Encyklopedii roślin w poszczególnych opisach. Zasianie jednego obszaru ziemii kosztuje 0.2 energii, w zamian dostajesz 0.01 do umiejętności Zielarstwo.");
        define("A_SAW", "Zasiej");
        define("T_LANDS", "obszarów farmy");
        define("HERB1", "Illani");
        define("HERB2", "Illanias");
        define("HERB3", "Nutari");
        define("HERB4", "Dynallca");
        define("NO_FARM", "Nie masz farmy aby siać zioła");
        define("NO_SEEDS", "Nie masz nasion aby hodować zioła!");
        define("NO_LAND", "Nie masz wolnych obszarów na farmie!");
        if (isset($_GET['step2']) && $_GET['step2'] == 'next')
        {
            define("NO_FREE", "Nie masz tyle wolnych obszarów!");
            define("NO_SEED", "Nie masz takiej ilości nasion!");
            define("YOU_DEAD", "Nie możesz zasiać ziół ponieważ jesteś martwy!");
            define("YOU_SAW", "Zasiałeś <b>");
            define("T_LANDS2", "</b> obszarów farmy ziołem ");
            define("YOU_GAIN", ". Zdobyłeś <b> ");
            define("T_ABILITY", "</b> poziomów w umiejętności Zielarstwo");
            define("NO_ITEMS", "Nie masz odpowiedniego wyposażenia na farmie aby hodować ten typ ziół!");
        }
    }
    if (isset($_GET['action']) && $_GET['action'] == 'chop')
    {
        define("CHOP_INFO", "Tutaj możesz zbierać zioła które wcześniej zasiałeś na swojej farmie. Zebranie ziół z jednego pola kosztuje 0.2 energii. W zamian otrzymujesz 0.01 do umiejętności Zielarstwo. Zioła możesz zbierać już po jednym resecie od zasiania. Im dłużej będziesz je hodował tym więcej możesz ich zebrać. Jednak jeżeli zbyt długo będą hodowane, po prostu zwiędną. Poniżej znajduje się lista obecnie hodowanych na farmie ziół.");
        define("NO_HERBS", "Nie hodujesz jakichkolwiek ziół!");
        define("NO_FARM", "Nie masz farmy aby zbierać zioła");
        if (isset($_GET['id']))
        {
            define("A_GATHER", "Zbieraj");
            define("FROM_A", "z");
            define("T_LANDS3", "obszarów farmy.");
            define("NOT_YOUR", "Nie możesz zbierać cudzych ziół!");
            define("TOO_YOUNG", "Te zioła jeszcze nie wyrosły!");
            define("TO_MUCH", "Nie możesz zbierać ziół z większej ilości obszarów niż zasiałeś!");
            define("YOU_DEAD", "Nie możesz zbierać ziół ponieważ jesteś martwy!");
            define("YOU_GATHER", "Zebrałeś <b>");
            define("T_AMOUNT2", "</b> sztuk ");
            define("T_FARM", " z farmy. W zamian zdobyłeś ");
            define("T_ABILITY", " poziomów w umiejętności Zielarstwo");
        }
    }
}
?>
