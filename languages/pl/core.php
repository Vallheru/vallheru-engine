<?php
/**
 *   File functions:
 *   Polish language for core arena
 *
 *   @name                 : core.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 22.10.2012
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

define("ERROR", "Zapomnij o tym");
define("NO_MONEY", "Nie masz tyle sztuk złota.");
define("C_TYPE1", "Leśny");
define("C_TYPE2", "Wodny");
define("C_TYPE3", "Górski");
define("C_TYPE4", "Polny");
define("C_TYPE5", "Pustynny");
define("C_TYPE6", "Magiczny");
define("NO_CORE", "Nie ma takiego chowańca");
define("SHOW_CORE", "Zobacz Chowańca");
define("T_CORE", "Chowaniec");
define("C_OPTIONS", "Opcje");
define("C_MALE", "Samiec");
define("C_FEMALE", "Samica");
define("NOT_YOUR", "To nie twój chowaniec!");
define("NO_TRAIN_P", "Nie masz wystarczająco dużo punktów treningowych.");
define("C_POWER", "Siła");
define("C_DEFENSE", "Obrona");
define("ARENA1", "Arena leśna");
define("ARENA2", "Arena morska");
define("ARENA3", "Arena górska");
define("ARENA4", "Arena polna");
define("ARENA5", "Arena pustynna");
define("ARENA6", "Arena magiczna");
define("T_CNAME", "Imię chowańca");
define("T_WINS", "Zwycięstw");

if ($player -> corepass != 'Y')
{
    define("NO_LICENSE", "<br />Licencja kosztuje 500 sztuk złota - których akurat nie masz przy sobie. Proszę, wróć kiedy będziesz miał odpowiednią sumę.");
    define("YES_LICENSE", "Świetnie - masz już Licencję na Chowańca. Kliknij <a href=\"core.php\">tutaj</a> aby kontynuować.");
    define("COREPASS_INFO", "Witaj na Polanie Chowańców. Polana Chowańców jest to miescje gdzie trzymane są zwierzęta występujące na Vallheru. Normalnie poluje się na nie jako na zwierzynę łowną, ale tutaj są trzymane pod strażą. Jeżeli kupisz Licencje na Chowańca, będziesz mógł złapać i trenować własnego chowańca.");
    define("A_YES", "Jasne, kupuję.");
    define("A_NO", "Nie...");
    define("HAVE_MONEY", "Masz 500 sztuk złota - dlaczego nie kupisz licencji? To otworzy ci kolejne miejsce w mieście.");
}

if (!isset($_GET['view']))
{
    if ($player -> location == 'Altara')
    {
        define("CORE_MAIN", "Witaj w Sektorze! Widzę, że masz swoją licencję... w porządku, baw się dobrze.");
    }
        else
    {
        define("CORE_MAIN", "Po krótkim spacerze przybywasz na Polanę Chowańców. Twoim oczom ukazuje się przepiękny widok. Polana usiana jest egzotycznymi kwiatami, a gdzieniegdzie widać jak ludzie, elfy i przedstawiciele innych ras krzątają się między klatkami z chowańcami. W najdalszym miejscu Polany widzisz Arenę, na której walczą ze sobą dwa chowańce. Co robisz?");
    }
    define("A_MY_CORE", "Moje Chowańce");
    define("A_LIBRARY", "Biblioteka Chowańców");
    define("A_ARENA", "Arena Chowańców");
    define("A_TRAIN", "Sala Treningowa chowańców");
    define("A_MARKET", "Sklep z Chowańcami");
    define("A_SEARCH", "Szukaj");
    define("A_BREED", "Zagroda Chowańców");
    define("A_MONUMENTS", "Najlepsze chowańce");
}
    else
{
    define("A_SECTOR", "Polana chowańców");
}

if (isset ($_GET['view']) && $_GET['view'] == 'breed') 
{
    define("BREED_INFO", "Witaj w Zagrodzie Chowańców. Tutaj możesz łączyć Chowańce w pary, dzięki czemu wyhodujesz nowego Chowańca.<br />Możesz łączyć tylko chowańce tego samego typu. Jeżeli ci się powiedzie cała operacja, młody chowaniec będzie miał statystyki zależne od statystyk rodziców oraz twojej umiejętności hodowli. Koszt takiej operacji to 15 punktów treningowych oraz ilość mithrilu zależna od statystyk rodziców. Posiadasz");
    define("TRAIN_PTS", "punktów treningowych.");
    define("WRONG_TYPE", "Nie możesz połączyć tych chowańców, ponieważ są one różnych typów!");
    define("NO_MITH2", "Nie masz tyle mithrilu.");
    define("YOU_SUCC", "Udało ci się wyhodować ");
    define("YOU_GAIN3", "). Zdobyłeś za to ");
    define("AND_GAIN", " punktów doświadczenia.");
    define("IN_BREEDING", " umiejętności Hodowla.");
    define("YOU_FAIL", "Próbowałeś wyhodować nowego chowańca, niestety nie udało się.");
    define("T_CORE2", " Chowańca (");
    define("A_BREED", "Skrzyżuj");
    define("T_CORES", "chowańce");
    define("T_AND", "oraz");
    define("THIS_COST", "Połączenie tych dwóch chowańców będzie ciebie kosztować");
    define("MITH_COINS", "sztuk mithrilu.");
    define("DO_YOU", "Czy chcesz je skrzyżować?");
}

if (isset ($_GET['view']) && $_GET['view'] == 'mycores') 
{
    define("MY_CORES_INFO", "Tutaj jest lista wszystkich Chowańców jakie znalazłeś.");
    define("C_ACTIVE", "(Aktywny)");
    define("C_ALIVE", "Żywy");
    define("C_DEAD", "Martwy"); 
    define("A_ACTIV", "Aktywuj");
    define("A_DEACTIV", "Dezaktywuj");
    define("A_REFRESH", "odśwież"); 
    define("MAIN_STATS", "Podstawowe Informacje");
    define("C_ID", "ID");
    define("C_NAME", "Nazwa");
    define("C_TYPE", "Typ");
    define("C_STATUS", "Status");
    define("ATTRIBUTES", "Fizyczne informacje");
    define("SHOW_DESC", "Zobacz opis");
    define("FREE_C", "Uwolnij");
    define("SEND_CORE", "Przekaż innemu graczowi");
    define("NO_NAME", "bezimienny");
    define("A_CNAME", "Zmień imię");
    define("T_GENDER", "Płeć");
    define("T_LOSSES", "Porażek");
    if (isset($_GET['activate'])) 
    {
        define("YOU_ACTIV", "Aktywowałeś swojego Chowańca");
    }
    if (isset($_GET['dezaktywuj'])) 
    {
        define("YOU_DEACT", "Dezaktywowałeś swojego Chowańca");
    }
    if (isset($_GET['release'])) 
    {
        define("YOU_FREE", "Uwolniłeś swojego Chowańca");
        define("DO_YOU", "Czy na pewno chcesz uwolnić chowańca");
    }
    if (isset($_GET['name'])) 
    {
        define("A_CHANGE", "Zmień");
        define("T_CHANGE", "imię chowańca na ");
        define("YOU_CHANGE", "Zmieniłeś imię swojemu chowańcowi na ");
    }
    if (isset($_GET['give'])) 
    {
        define("BAD_PLAYER", "Nie możesz przekazać chowańca samemu sobie!");
        define("NO_PLAYER", "Nie ma takiego gracza");
        define("YOU_SEND", "Przekazałeś swojego Chowańca");
        define("SEND2", "</b> graczowi ");
        define("L_PLAYER", "Gracz");
        define("L_ID", " , ID ");
        define("SEND_YOU", "przekazał ci Chowańca");
        define("A_ADD", "Daj");
        define("T_PLAYER", "graczowi ");
    }
}

if (isset ($_GET['view']) && $_GET['view'] == 'library') 
{
    define("LIB_INFO1", "Witaj w Bibliotece Chowańców,");
    define("LIB_INFO2", "Znajdziesz tutaj informacje o twoich");
    define("LIB_INFO3", "chowańcach pośród informacji o wszystkich");
    define("LIB_INFO4", " znajdujących się w bibliotece. <br /><br />Możesz używać naszej biblioteki aby zobaczyć informacje tylko o tych chowańcach, które obecnie posiadasz.");
    define("N_CORE", "Podstawowe Chowańce");
    define("H_CORE", "Łączone chowańce");
    define("S_CORE", "Specjalne chowańce");
    define("MAIN_INFO", "Podstawowe informacje");
    define("STAND_ID", "Standardowy ID");
    define("L_NAME", "Nazwa");
    define("L_TYPE", "Typ");
    define("L_RAR", "Rzadkość");
    define("L_CAT", "Złapano");
    define("OWNED", "Posiadane");
    define("DESC", "Opis");
    define("NO_CORE2", "Nie zdobyłeś tego chowańca");
}

if (isset ($_GET['view']) && $_GET['view'] == 'arena') 
{
    define("ARENA_INFO", "Witaj na Arenie Chowańców. Są pewne różnice w stosunku do walki na Arenie Walk - każdy Chowaniec zadaje jeden cios innemu. Ten, który zada najwięcej obrażeń jest uznawany za zwycięzcę.");
    define("A_HEAL", "Ulecz moje chowańce");
    define("ALL_HEALED", "Wszystkie twoje chowańce zostały wyleczone.");
    if (isset ($_GET['step']) && $_GET['step'] == 'battles') 
    {
        define("OWNER", "Właściciel");
    }
    if (isset($_GET['attack'])) 
    {
        define("NO_ENERGY", "Nie masz tyle energii!");
        define("CORE_DEAD", "Twój aktywny chowaniec jest martwy!");
        define("ITS_YOUR", "Nie możesz walczyć z własnym Chowańcem!");
        define("CORE_DEAD2", "Ten chowaniec jest martwy.");
        define("YOU_NOT_FIGHT", "Nie możesz walczyć chowańcem ");
        define("BAD_TYPE", " ponieważ są różnych typów");
        define("NOT_ACTIVE", " ponieważ nie jest on aktywny!");
        define("RESULT1", "Bitwa nie <b>rozstrzygnięta</b>.");
        define("RESULT2", " Chowaniec pokonał ");
        define("RESULT3", " Chowańca.<br />");
        define("RESULT4", "Twój <b>Chowaniec ");
        define("RESULT5", "</b> pokonał ");
        define("RESULT6", " Chowańca</b>!<br /><br />");
        define("HAS_BEEN", "</b> został pokonany przez ");
        define("V_GAIN", " zdobywa <b>");
        define("GOLD_COINS", "</b> sztuk złota za bitwę oraz <b>");
        define("C_MITH", "</b> mithrilu!");
        define("CORE_B", "Walka Chowańców");
        define("Y_CORE1", "Twój Chowaniec");
        define("Y_CORE2", "przeciwko ID");
        define("Y_CORE3", "Chowańcowi.");
        define("E_CORE1", "Wrogi Chowaniec");
        define("E_CORE2", "atakuje za");
        define("E_CORE3", "Twój Chowaniec");
        define("LOG_ENTRY", "Twój Chowaniec został zaatakowany przez Chowańca mieszkańca <b><a href=\"view.php?view=");
        define("L_ID", " , ID ");
        define("LOG_ENTRY2", ". Twój Chowaniec wygrał.");
        define("LOG_ENTRY3", ". Twój Chowaniec przegrał.");
        define("LOG_ENTRY4", ". Walka została nierozstrzygnięta.");
    } 
    if (isset ($_GET['step']) && $_GET['step'] == 'heal') 
    {
        define("IT_COST", "To będzie kosztować");
        define("GOLD2", "sztuk złota oraz");
        define("GOLD3", "sztuk mithrilu aby wyleczyć wszystkie twoje");
        define("D_CORES", "martwe Chowańce.");
        define("A_YES2", "Wylecz je");
        define("A_NO2", "Nie chcę leczyć ich teraz");
    }
}

if (isset ($_GET['view']) && $_GET['view'] == 'train') 
{
    define("TRAIN_INFO", "Witaj w sali treningowej. Dostajesz 15 punktów treningu każdego dnia. Każdy punkt podwyższa Chowańcowi 0.125 w odpowiedniej statystyce. Obecnie masz");
    define("TRAIN_INFO2", "wolnych punktów treningowych.");
    define("TRAIN_MY", "Trenuj mojego");
    define("TR_CORE", "Chowańca");
    define("T_AMOUNT2", "razy w");
    define("T_POWER2", "Sile");
    define("T_DEFENSE2", "Obronie");
    define("A_TRAIN", "Trenuj");
    define("HOW_MANY", "Nie podałeś ile razy.");
    define("YOU_DEAD","Nie możesz trenować chowańca, ponieważ jesteś martwy!");
    define("T_POWER", "Siły");
    define("T_DEFENSE", "Obrony");
    define("YOU_TRAIN", "Trenowałeś swojego Chowańca <b>");
    define("T_AMOUNT", "</b> razy, zużywając <b>");
    define("T_TRAIN", "</b> punktów treningowych. Dostaje za to <b>");
}

if (isset ($_GET['view']) && $_GET['view'] == 'explore') 
{
    define("EXPLORE_INFO", "Witaj w centrum poszukiwań Chowańców. Proszę, wybierz region, z którego szukasz chowańca. Jest wiele regionów, ale musisz posiadać odpowiednią ilość mithrilu aby móc wejść w jeden z nich. Mithril wymagany do wejścia w wybrany region, jest zabierany za każdego znalezionego chowańca. Każde poszukiwanie chowańca kosztuje 0.1 energii. Chowańce przyciąga mithril z wielu powodów...");
    define("MITH2", "mith");
    define("A_SEARCH", "Szukaj");
    define("E_AMOUNT", "razy");
    define("NO_ENERGY2", "Nie masz wystarczająco dużo energii aby szukać. <a href=\"core.php?view=explore\">Wróć</a>");
    define("YOU_DEAD2", "Nie możesz wyruszyć na poszukiwanie ponieważ jesteś martwy! <a href=\"core.php?view=explore\">Wróć</a>");
    define("NO_REGION", "Nie ma takiego regionu!");
    define("REGION1", "Las");
    define("REGION2", "Ocean");
    define("REGION3", "Góry");
    define("REGION4", "Łąki");
    define("REGION5", "Pustynia");
    define("REGION6", "Inny wymiar");
    define("NO_MITH", "Nie masz tyle mithrilu. <a href=core.php?view=explore>Wróć</a>");
    define("YOU_FIND", "Znalazłeś <b>");
    define("FIND2", " Chowańca</b>! Jest on rodzaju <b>");
    define("RARITY1", "<br />Ten Chowaniec jest <b>często spotykany</b>.");
    define("RARITY2", "<br />Ten Chowaniec jest <b>rzadki</b>.");
    define("RARITY3", "<br />Ten Chowaniec jest <b>bardzo rzadki</b>.");
    define("ITS_FIRST", "<br />To jest twój pierwszy Chowaniec tego typu!<br />");
    define("YOU_HAVE", "<br />Masz już takiego Chowańca.<br />");
    define("YOU_START", "Chcesz szukać Chowańca w regionie");
    define("YOU_SEARCH", "Szukałeś chowańców");
    define("AGAIN", "Chcesz szukać ponownie?");
}
?>
