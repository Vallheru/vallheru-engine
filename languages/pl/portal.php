<?php
/**
 *   File functions:
 *   Polish language for portal
 *
 *   @name                 : portal.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 26.07.2006
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
// $Id: portal.php 566 2006-09-13 09:31:08Z thindil $

define("YOU_DEAD", "Ponieważ jesteś martwy, twa dusza podąża z powrotem do szpitala w ".$city1a.". Kliknij <a href=hospital.php>tutaj</a>.");
define("A_HERE", "tutaj");

if (!isset ($_GET['action1'])) 
{
    define("PORTAL_TEXT", "Tuż po przejściu przez magiczną bramę, przez moment czujesz jak kręci ci się w głowie. Jednak już po chwili wszystko wraca do normy i zaczynasz rozglądać się do okoła. Dostrzegasz że jesteś w jakimś nieznanym miejscu, gdzie nawet gwiazdy wyglądają inaczej. Wszystko otulone jest lekką szarą mgłą. Przyglądając się kawałkom znalezionych przez siebie map, zaczynasz mniej więcej dostrzegać którą drogą wyruszyć. Idą słyszył wokół siebie szepty w nieznanym ci języku, jednak masz dziwne przeczucie że owe rozmowy dotyczą ciebie. Nie wiesz sam przez jaki okres czasu podróżowałeś przez ten nieco nierealny świat, kiedy twoja podróż dobiega końca. Przed sobą widzisz most przerzucony nad przepaścią prowadzący do największego zamku jaki w życiu widziałeś. Podążając mostem, tuż przy bramie dostrzegasz wysoką, dobrze zbudowaną postać odzianą w pełną zbroję płytową wykonaną z nieznanego ci minerału. Kiedy podchodzisz bliżej z niepokojem zauważasz, że w oczodołach istoty zapalają się niebieskie ognie a ona sama rusza w twoim kierunku. Twój instynkt ci mówi, że teraz masz ostatnią szansę aby uciec zanim rozpocznie się walka. Co robisz?");
    define("A_FIGHT2", "Walczę");
    define("A_RETREAT", "Uciekam");
}

if (isset ($_GET['action1']) && $_GET['action1'] == 'retreat') 
{
    define("PORTAL_TEXT", "Szybko odwracasz się i zaczynasz uciekać w stronę portalu, czując na swoich plecach oddech owej dziwnej istoty. Nie wiesz jak długo biegłeś z powrotem kiedy w końcu szybko wbiegasz w bramę portalu. Po chwili słyszysz do okoła siebie gwar różnych rozmów - znak że jesteś z powrotem w ".$city1a.". Jednak owa podróż zmęczyła cię niesamowicie. Dlatego na razie musisz odpocząć. Kliknij");
}

if (isset ($_GET['action1']) && $_GET['action1'] == 'fight') 
{
    define("START_FIGHT", "Kiedy owa postać zaczyna ruszać w twoją stronę szybko przygotowujesz się do walki<br />");
    define("MONSTER_NAME", "Strażnik Skarbu");
    define("NO_HP", "Nie masz wystarczająco dużo życia aby walczyć.");
    define("NO_ENERGY", "Nie masz wystarczająco dużo energii aby walczyć.");
    define("LOST_FIGHT2", "<br />Ostatnią rzeczą jaką zauważasz to miecz Strażnika spadający na twą głowę, chwilę potem następuje ciemność. Ponieważ jesteś martwy, twa dusza podąża z powrotem do szpitala w ".$city1a.". Kliknij <a href=hospital.php>tutaj</a>.");
    define("PORTAL_TEXT", "Po twoim ostatnim ciosie, Strażnik stanął nieruchomo w miejscu, a następnie powoli zaczął się rozpadać. Droga do zamku stoi przed tobą otworem. Kiedy podchodzisz do masywnych wrót te pchane jakąś niewidzialną siłą otwierają się na oścież. Wewnątrz widzisz olbrzymi, długi korytarz. Na jego ścianach wiszą gobeliny przedstawiające jakieś dziwne istoty żyjące na Vallheru u zarania dziejów. Odgłos twoich kroków tłumi bogato zdobiony czerwony dywan. Mimo iż cała twierdza wygląda na opuszczoną, nigdzie nie dostrzegasz choćby garstki kurzu. Ciekawie rozglądając się, idziesz cały czas przed siebie. Nie dostrzegasz nigdzie drzwi prowadzących do innych pomieszczeń, wydaje się jakby cała budowla była tylko tym jednym korytarzem. Kiedy zbliżasz się do jego końca, zauważasz owego starca, którego spotkałeś w ".$city1a.". Kiedy podchodzisz do niego uśmiecha się na twój widok i mówi:<br /> <i>Witaj bohaterze, udało ci się pokonać Strażnika, moje gratulacje. Dotarłeś do Shar-lan-hazi, ostatniej twierdzy Pierwszych na Vallheru. W nagrodę za twe męstwo oraz upór w dążeniu do tego celu, możesz wybrać dla siebie jeden przedmiot będący w tym zamku. Jaką nagrodę wybierasz dla siebie?</i>");
    define("SWORD", "Miecz Vallheru");
    define("ARMOR", "Zbroja Światła");
    define("I_STAFF", "Różdżka Potęgi");
    define("CAPE", "Szata Pierwszych");
    if (isset ($_GET['step'])) 
    {
        define("ITEM_TAKE", "Już ktoś wziął przedmiot!");
        define("U_TITLE", "Nowy Bohater");
        define("U_TEXT", "I tak oto na ");
        define("U_TEXT2", " pojawił się kolejny bohater, który przeszedł cało i zdrowo tajemnicze miejsce.<br />Władze ");
        define("U_TEXT3", " pragną poinformować swoich poddanych iż dziś (");
        define("U_TEXT4", ") do panteonu bohaterów wstąpił(a) ");
        define("U_TEXT5", " o ID:");
        define("U_TEXT6", ".<br /> Cześć mu i chwała!");
        define("STEP_TEXT", "Kiedy oznajmiłeś mu swój wybór, starzec odzywa się ponownie:<br ><i>Dobrze zatem, twa nagroda niedługo pojawi się u ciebie. A teraz proszę odejdź stąd, pozwól tym, którzy tu mieszkają odpoczywać w spokoju Bohaterze.</i> Powoli wychodzisz z zamku, przechodzisz przez most i wracasz w kierunku portalu. Kiedy w pewnym momencie ostatni raz odwracasz się aby spojrzeć na zamek zauważasz że ten zniknął, a na jego miejscu zostało jedynie niewielkie jezioro. Rozglądając się w około widzisz że dziwna mgła opadła, a do okoła ciebie rosną jakieś nieznane rośliny. W około panuje cisza, jakbyś był jedyną żywą istotą w okolicy. Zbliżasz się do portalu i przechodzisz przez niego. Po chwilowych zawrotach głowy znów słyszysz wokół siebie gwar rozmów różnych istot. Jesteś z powrotem w ".$city1a.". Rozglądając się dookoła dostrzegasz obok siebie zawiniątko. Kiedy je rozwijasz twoim oczom ukazuje się");
        define("T_GO", "Wejdź");
    }
}
?>
