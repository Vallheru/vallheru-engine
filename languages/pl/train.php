<?php
/**
 *   File functions:
 *   Polish language for school
 *
 *   @name                 : train.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.5
 *   @since                : 14.05.2012
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
define("YOU_DEAD", "Nie możesz się szkolić, ponieważ jesteś martwy!");
define("NO_RACE", "Nie możesz się szkolić ponieważ nie wybrałeś jeszcze rasy!");
define("NO_CLASS", "Nie możesz się szkolić ponieważ nie wybrałeś jeszcze klasy!");
define("T_TRAIN", "0,3 energii za 0,06 Siły (");
define("T_TRAIN_2", " sztuk złota)<br /> Zręczności (");
define("T_TRAIN_3", " sztuk złota)<br /> Szybkości (");
define("T_TRAIN_4", " sztuk złota)<br /> Wytrzymałości (");
define("T_TRAIN_5", " sztuk złota)<br />");
define("T_TRAIN2", "0,4 energii za 0,06 Siły ("); 
define("T_TRAIN2_2", " sztuk złota)<br />lub Wytrzymałości (");
define("T_TRAIN2_3", " sztuk złota)<br /> 0,2 energii za 0,06 Zręczności (");
define("T_TRAIN2_4", " sztuk złota)<br />lub Szybkości (");
define("T_TRAIN2_5", " sztuk złota)<br />");
define("T_TRAIN3", "0,2 energii za 0,06 Siły (");
define("T_TRAIN3_2", " sztuk złota)<br /> lub Wytrzymałości (");
define("T_TRAIN3_3", " sztuk złota)<br />0,4 energii za 0,06 Zręczności (");
define("T_TRAIN3_4", " sztuk złota)<br /> lub Szybkości (");
define("T_TRAIN3_5", " sztuk złota)<br />");
define("T_TRAIN4", "0,4 energii za 0,06 Siły (");
define("T_TRAIN4_2", " sztuk złota)<br />lub Szybkość (");
define("T_TRAIN4_3", " sztuk złota)<br /> 0,2 energii za 0,06 Zręczności (");
define("T_TRAIN4_4", " sztuk złota)<br />lub Wytrzymałość (");
define("T_TRAIN4_5", " sztuk złota)<br />");
define("T_TRAIN5", "0,4 energii za 0,06 Zręczność (");
define("T_TRAIN5_2", " sztuk złota)<br />lub Wytrzymałości (");
define("T_TRAIN5_3", " sztuk złota)<br />0,2 energii za 0,06 Siły (");
define("T_TRAIN5_4", " sztuk złota)<br />lub Szybkości (");
define("T_TRAIN5_5", " sztuk złota)<br />");
define("T_TRAIN6", " oraz 0,4 energii za 0,06 Siły Woli (");
define("T_TRAIN6_2", " sztuk złota)<br />lub Inteligencji (");
define("T_TRAIN6_3", " sztuk złota)");
define("T_TRAIN7", " oraz 0,2 energii za 0,06 Siły Woli (");
define("T_TRAIN7_2", " sztuk złota)<br />lub Inteligencji (");
define("T_TRAIN7_3", " sztuk złota)");
define("T_TRAIN8", " oraz 0,3 energii za 0,06 Siły Woli (");
define("T_TRAIN8_2", " sztuk złota)<br />lub Inteligencji (");
define("T_TRAIN8_3", " sztuk złota)");
define("T_TRAIN9", "sztuk złota)<br />0,3 energii za 0,06 Zręczności (");
define("T_TRAIN9_1", " oraz 0,4 energii za 0,06 Siły Woli (");
define("T_TRAIN9_2", " sztuk złota)<br />lub 0,3 energii za 0,06 Inteligencji (");
define("T_TRAIN9_3", " sztuk złota)");
if ($player -> location == 'Altara')
{
    define("TRAIN_INFO", "Ogromne filary podtrzymujące sklepienie budynku pamiętają wszystko, co działo się w Szkole Vallheryjskiej. Świadczą o tym ich odrapane wykończenia. Podchodzisz bliżej i widzisz pomieszczenie do złudzenia przypominające Arenę Walk. Na środku sali dwóch krasnoludów walczy ze sobą na ogromne topory obosieczne, które zdają się być większe od nich samych. Nieco dalej elficka para uczy się strzelać do ruchomego celu, którym jest maleńki gnom biegający po podwyższeniu stojącym w odległości 20 metrów od pary. W pewnym momencie słyszysz ruch za swoimi plecami, odwracasz się szybko a w ręku Twym już widać broń. Stoi przed Tobą starzec, który chrypliwym głosem mówi:<br /><br />- Witaj w Szkole Umięjętności w ".$city1a.". Jeżeli chciałbyś trenować swoją krzepę, szybkość czy nawet zgłębiać tajniki magii możesz to zrobić u nas. Za odpowiednią opłatą oczywiście. Starzec podniósł dłoń pokazując tablicę z Twoim imieniem. Zdziwiony skąd oni wiedzą jak się nazywasz podchodzisz blizej i czytasz ceny odpowiednie do Twojej postaci.<br /> ");
}
    else
{
    define("TRAIN_INFO", "Uniwersytet imienia ".$city2."a ... Stoisz przed wierzbą, na konarach której mieści się uniwersytet założony przez ".$city2."a, a po jego śmierci nazwany tym imieniem. Możesz tu poświęcić swój czas w celu zdobycia większych umiejętności.");
}
define("GOLD_COINS", " sztuk złota.");
define("I_WANT", "Chcę trenować moją");
define("TR_STR", "Siłę");
define("TR_AGI", "Zręczność");
define("TR_CON", "Wytrzymałość");
define("TR_SPEED", "Szybkość");
define("TR_INT", "Inteligencję");
define("TR_WIS", "Siłę Woli");
define("T_AMOUNT", "razy");
define("A_TRAIN", "Trenuj");

if (isset ($_GET['action']) && $_GET['action'] == 'train') 
{
    define("HOW_MANY", "Podaj ile razy chcesz ćwiczyć!");
    define("NO_MONEY", "Nie możesz tyle ćwiczyć, ponieważ nie stać ciebie na to! <br />Potrzebujesz ");
    define("NO_ENERGY", "Nie masz wystarczającej ilości energii.");
    define("T_STR", "Siły");
    define("T_AGI", "Zręczności");
    define("T_CON", "Wytrzymałości");
    define("T_SPEED", "Szybkości");
    define("T_INT", "Inteligencji");
    define("T_WIS", "Siły Woli");
    define("YOU_GAIN", "Zyskujesz <b>");
    define("YOU_PAY", "</b>. Zapłaciłeś(aś) za to ");
}
?>
