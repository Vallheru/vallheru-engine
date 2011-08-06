<?php
/**
 *   File functions:
 *   Polish language for astral plans
 *
 *   @name                 : portals.php                            
 *   @copyright            : (C) 2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.1
 *   @since                : 24.04.2006
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
// $Id: portals.php 566 2006-09-13 09:31:08Z thindil $

define("ERROR", "Zapomnij o tym!");
define("NO_MAP", "Nie masz mapy do tego planu!");
define("MONSTER1", "Glabrezu");
define("MONSTER2", "Barlog");
define("MONSTER3", "Zgłębiczart");
define("MONSTER4", "Pustynna Skorpendra");
define("MONSTER5", "Podwodny Kraken");
define("MONSTER6", "Niebiański Tytan");
define("MONSTER7", "Szkarłatny Lich");
define("PLAN1", "plan demoniczny");
define("PLAN2", "plan ognisty");
define("PLAN3", "plan piekielny");
define("PLAN4", "plan pustynny");
define("PLAN5", "plan wodny");
define("PLAN6", "plan niebiański");
define("PLAN7", "plan śmiertelny");

if (!isset($_GET['go']))
{
    define("DESC1", "Przekraczasz portal i wchodzisz na");
    define("DESC2", ". Czujesz lekkie zawroty głowy spowodowane magiczną podróżą. Kiedy dochodzisz do siebie, twoją uwagę przykuwa szarżujący wprost na ciebie");
    define("DESC3", "! Rozpoczyna się walka!");
    define("A_FIGHT", "Walcz");
}

if (isset($_GET['go']) && $_GET['go'] == 'fight')
{
    define("YOU_LOST2", "<br />Ostatnią rzeczą jaką widzisz, jest spadające na ciebie uderzenie potwora. Potem otacza ciebie już tylko nieprzenikniona ciemność oraz najgłębsza cisza. Po pewnym czasie budzisz się w szpitalu w ".$city1a.". <a href=\"hospital.php\">Dalej</a>");
    define("YOU_WIN", "<br />Jeszcze tylko jeden cios i bestia pada martwa przed tobą. Przez chwilę stoisz i odpoczywasz po walce. Następnie bierzesz się do przeszukiwania zwłok potwora w poszukiwaniu skarbów. ");
    define("NOTHING", "<br />Po pewnym czasie stwierdzasz że to bezsensowne, ta poczwara nic przy sobie nie miała! Zrezygnowany siadasz na ziemi. Przez chwilę siedzisz tak zatopiony w myślach. Do rzeczywistości przywraca ciebie gwar głosów. Zdziwiony rozglądasz się i widzisz, że znajdujesz się w jednym z zaułków ".$city1b.", prowadzącym do głównej ulicy miasta. Po chwili dochodzisz do siebie, wstajesz i wchodzisz w miejską krzątaninę. <a href=\"city.php\">Dalej</a>");
    define("COMPONENT", "<br />Krzywiąc się lekko z powodu nieprzyjemnego zapachu martwej bestii, przeszukujesz jej zwłoki. Okazuje się że twoje poświęcenie nie poszło na marne. Z cielska wyciągasz astralny komponent! Przez chwilę spoglądasz na bezcenny skarb. Do rzeczywistości przywraca ciebie gwar głosów. Zdziwiony rozglądasz się i widzisz, że znajdujesz się w jednym z zaułków ".$city1b.", prowadzącym do głównej ulicy miasta. Po chwili dochodzisz do siebie, wstajesz i wchodzisz w miejską krzątaninę. <a href=\"city.php\">Dalej</a>");
    define("YOU_ESCAPE", "<br />Przerażony rzucasz się do ucieczki. Biegnąc ile sił w nogach, odwracasz głowę aby spojrzeć za siebie. Widzisz, że bestia nadal ciebie goni. Szybko odwracasz się do przodu... i wpadasz na zdumionego przechodnia, przewracając go. Widzisz, że znów jesteś na ulicach ".$city1b.". Po chwili dochodzisz do siebie, wstajesz i wchodzisz w miejską krzątaninę. <a href=\"city.php\">Dalej</a>");
}
?>
