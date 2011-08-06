<?php
/**
 *   File functions:
 *   Polish language for court
 *
 *   @name                 : court.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 14.10.2006
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
// $Id: court.php 718 2006-10-14 15:29:47Z thindil $

define("ERROR", "Zapomnij o tym!");
if (!isset($_GET['step2']) && !isset($_GET['modify']))
{
    define("C_INFO", "Idziesz powoli ulicami ".$strCity.". Przechodzisz koło szarego dużego budynku. Początkowo nie zwracasz na niego uwagi, po chwili cofasz się i przyglądasz mu. Nad masywnymi dębowymi drzwiami widnieje napis <b>Gmach Sądu</b>.<br />Z ciekawości uchylasz drzwi. Wchodzisz do obszernego chłodnego holu z kamienną posadzką, na której ułożona jest mozaika. Przyglądasz się jej bliżej, po czym zauważasz wielką wagę - symbol sprawiedliwości. Rozglądasz się dokładnie i zauważasz kilkoro drzwi, na których wywieszone są tabliczki: ");
    define("C_INFO2", "Tego dnia, niebo nad ".$strCity2." pogrążone jest w mroku. Słońce jednak nie poddaje się miejscami przebijając grubą warstwę chmur, tworząc wielkie słupy światła padające na ziemię. Właśnie w tym momencie jeden z nich dosięgnął stojącego w pobliżu budynku, co przyciągneło Twoją uwagę. Blask promieni połączył się z blaskiem mithrilu rozbłyskując na wszystkie strony. Gdy podszedłeś bliżej twoim oczom ukazał się niespotykany dotąd widok. Mithrilowy pomnik rozświetlony tysiącami barw. Symbol sprawiedliwości...<br />Stoi dumny na środku schodów prowadzących do budynku. Po obu stronach stopni stoją liczne małe latarnie rozświetlające nocą okolice delikatnym światłem. Oprócz głównego wejścia, do budynku prowadzą też dwa mniejsze, jedno z prawej, a drugie z lewej strony. Budynek jest bardzo szeroki lecz najbardziej zadziwiające jest to jak wiele okien zawiera. Każde z nich otoczone jest bogato zdobionymi ornamentami pomiędzy którymi znajdują się jeszcze piękniejsze płaskorzeźby. Pomału podchodzisz do wielkich dębowych wrót. Dostrzegasz nad nimi napis:<br /><i>Błąd łatwiej można dostrzec niż prawdę, bo błąd leży na wierzchu, a prawda w głębi.</i>");
}
define("A_JUDGES", "Sędziowie");
define("A_ALDERMEN", "Ławnicy");
define("A_LAWYERS", "Prawnicy");
define("T_LISTS", "Skład Sądu");
define("A_ADMIN", "Administracja");
define("T_INFO", "Informacje");
define("A_RULES", "Przepisy");
define("A_CASES", "Toczące się sprawy");
define("A_VERDICTS", "Wyroki");
define("T_LANG", "Język");
define("T_TITLE", "Tytuł");
define("T_BODY", "Treść");
define("A_BACK", "Wróć");
define("A_COMMENTS", "Rozprawa");

if (isset($_GET['list']))
{
    define("JUDGE3", "Sędzia");
    define("JUDGE2", "Sędziów");
    define("ALDERMAN", "Ławnik");
    define("ALDERMAN2", "Ławników");
    define("LAWYER2", "Prawnik");
    define("LAWYER3", "Prawników");
    define("NO_PEOPLE", "Nie ma jeszcze");
    define("IN_A", "w");
    define("LIST_INFO", "Oto lista");
}

if (isset($_GET['step']) && ($_GET['step'] == 'rules' || $_GET['step'] == 'cases' || $_GET['step'] == 'verdicts'))
{
    define("RULES", "przepisów w");
    define("RULES2", "przepisów prawnych obowiązujących w");
    define("CASES", "spraw w sądzie na");
    define("CASES2", "obecnie toczących się spraw w sądzie");
    define("VERDICTS", "wyroków w sądzie na");
    define("VERDICTS2", "zasądzonych wyroków w sądzie");
    define("T_NO_ITEMS", "Nie ma żadnych");
    define("LIST_INFO", "Oto lista");
    define("LIST_INFO2", "Aby zobaczyć szczegóły, kliknij na tytule danego tekstu");
    define("M_DATE", "data ostatniej modyfikacji");
    define("A_CHANGE", "Zmień");
}

if (isset($_GET['step']) && $_GET['step'] == 'admin')
{
    define("ADDED", "Dodano tekst");
    define("A_ADD_RULE", "Dodaj przepis");
    define("A_ADD_CASE", "Dodaj sprawę");
    define("A_ADD_VERD", "Dodaj wyrok");
    define("A_ADD", "Dodaj");
}

if (isset($_GET['modify']))
{
    define("CHANGED", "Zmieniono tekst");
    define("EMPTY_FIELDS", "Wypełnij wszystkie pola!");
    define("A_CHANGE", "Zmień");
}

if (isset($_GET['step2']) && $_GET['step2'] == 'comments')
{
    define("C_ADDED", "Wypowiedź dodana!");
    define("C_DELETED", "Wypowiedź skasowana!");
    define("NO_COMMENTS", "Nie ma jeszcze wypowiedzi do tej sprawy!");
    define("A_DELETE", "Skasuj");
    define("ADD_COMMENT", "Dodaj wypowiedź");
    define("A_ADD", "Dodaj");
    define("NO_PERM", "Nie masz prawa przebywać tutaj!");
}
?>
