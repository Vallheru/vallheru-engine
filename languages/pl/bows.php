<?php
/**
 *   File functions:
 *   Polish language for fletcher shop
 *
 *   @name                 : bows.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 25.07.2012
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
define("NO_ITEM", "Nie ma takiego przedmiotu");
define("NO_MONEY", "Nie stać cię!");
define("E_DB", "nie mogę dodać");
define("YOU_BUY", "Zapłaciłeś");
define("GOLD_COINS", "sztuk złota, ale teraz masz nowy");
define("DAMAGE", "do Obrażeń.");
define("WITH", "z");
if ($player -> location != 'Ardulith')
  {
    define("SHOP_INFO", "Podchodzisz do wielkich drewnianych drzwi z metalowymi pięknymi zawiasami w kształcie lian, wyglądają one jakby żywe, okalające drzwi winorośle. Uchylasz drzwi i dostrzegasz, że w środku kilka Elfów i Człowiek strzelają do celu, nieźle się przy tym bawiąc. Podchodzisz do nich bliżej, zauważasz dopiero teraz, że dalej przed nimi są porozstawiane ogromne tarcze, do których strzelają. Zauważasz, że jeden z nich strzela ciągle ani razu nie chybiając, a przy tym prawie w ogóle nie patrząc na cel. Zachwyca Cię ogromnie, ale w pewnej chwili przypominasz sobie, że to przecież");
    define("SHOP_INFO2", ", najlepszy strzelec w całym królestwie. Chcesz do niego podejść i porozmawiać, ale nie starcza ci odwagi, żeby porozmawiać z kimś kto jest tak wielki, jak on.<br />W tej samej prawie chwili drzwi obok wychodzi Hobbit, którego najpierw nie zauważasz go, ale kiedy odzywa się do Ciebie:<br />\"Panie, proszę dalej Łucznik czeka.\" - orientujesz się, że mały Hobbit do Ciebie mówi. Bez słowa idziesz do pomieszczenia wskazanego przez śmiesznego małego ludka.<br />Kiedy wchodzisz do pokoju widzisz, że sprawny Krasnolud oczyszcza piękną kusze bojową - jest ona wykonana niezwykle starannie i dokładnie, a jej cięciwa wygląda jakby ze złota.<br />- \"Witaj, wędrowcze. \" -mówi Krasnolud. - \"Co Ciebie do nas sprowadza, może chcesz kupić jeden z moich łuków lub kuszy? A może chodzi Ci o strzały? Jestem najlepszym kowalem w całym królestwie, ale nie jestem tani .Wiec co chcesz? \"<br />- \"Chcę coś kupić odpowiedniego dla siebie \"- odpowiadasz niepewnie, jakby zamurowany charyzmą rzemieślnika.<br />- \"Wybierz sobie coś z moich towarów. Ten hobbit zaprowadzi Cię do magazynu. \"<br /><br />Idziesz z Hobbitem do magazynu. Ten otwiera wielkie żelazne drzwi, za nimi znajduje się wielka sala z wywieszonymi na ścianach łukami, kuszami i strzałami. A wokół ścian na podłodze w koszach znajdują się groty różnych kształtów. Obok nich stoją kołczany na strzały, zrobione z wielką dokładnością tak, jakby ktoś poświecił na to całe swoje serce. Jesteś zachwycony tym wystrojem. W końcu Hobbit mówi:<br />- \"Wybierz coś dla siebie Panie\" - i oddala się za wielkie drzwi. ");
  }
 else
   {
     define("SHOP_INFO", "We wschodniej części sadu znajduje się słynący ze swoich produktów sklep Łucznika.<br />Postanawiasz sam sprawdzić czy mity o wytwarzanych przez Elfy łukach są prawdziwe. Wchodzisz do pięknego i doskonale wkomponowanego w otaczającego go gałęzie sklepu. Niski sprzedawca. Zaraz... niski, niski elf podchodzi do ciebie i rzecze: <br /><br /><i>Witaj w moim sklepie nieznajomy. Po pierwsze nie jestem elfem tylko hobbitem. Na dowód mogę pokazać moje nogi. Widzisz, że całe są owłosione i nie ma na nich butów. Ale wracajmy do interesów. Możesz u mnie kupić najprzedniejsze łuki oraz najlepsze strzały jakie wyszły spod ręki elfich rzemieślników i nie tylko ich, jeśli wiesz co mam na myśli - wskazując na piękny krótki łuk. Czym mogę służyć?</i>");
   }
define("I_NAME", "Nazwa");
define("I_DUR", "Wt.");
define("I_EFECT", "Efekt");
define("I_COST", "Cena");
define("I_LEVEL", "Wymagany poziom");
define("I_SPEED", "Szyb");
define("I_OPTION", "Opcje");
define("A_BUY", "Kup");
define("A_STEAL", "Kradzież");
define("T_ARROWS", "kołczanów");
define("T_AMOUNT", "sztuk złota lub");
define("FOR_A", "za");
define("T_AMOUNT2", "sztuk");
define("T_AMOUNT3", "sztuk złota.");
?>
