<?php
/**
 *   File functions:
 *   Select player class
 *
 *   @name                 : klasa.php                            
 *   @copyright            : (C) 2004,2005,2006,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 14.12.2012
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
// 

$title = "Wybierz klasę";
require_once("includes/head.php");

if ($player -> clas)
{
    error("Masz już wybraną klasę");
}

if (isset($_GET['klasa']))
  {
    switch ($_GET['klasa'])
      {
      case 'wojownik':
	$strClass = "Wojownicy są popularną klasa. W większości wojownikami zostają krasnoludy choć elfy i ludzie tez nie rezygnują z tej klasy. Mają największe zdolności bojowe - najważniejsze dla nich są siła i wytrzymałość ale tez bez odpowiedniej szybkości i zręczności się nie obejdzie. W Vallheru wojownicy stanowią największą część społeczeństwa. Podczas walki wojownik posługuje się bronią białą a najczęściej ubiera się w mocne zbroje - choć są w Vallheru znakomici szermierze którym zbroja tylko przeszkadza. Zostają złymi rzezimieszkami, silnymi ochroniarzami, wiernymi obrońcami, przebiegłymi złodziejami, lub po prostu neutralnymi poszukiwaczami przygód. Wojownikami zostają osoby które najczęściej szukają okazji do bitki i przetestowania swoich zdolności bojowych,
    lub z chęci zemsty na kimś ale tez dla samoobrony lub zmierzenia się na arenie z innymi dla chwały.<br />  Krasnoludy które zostają wojownikami są najczęściej nieokrzesani i porywczy, ale i silni oraz bardzo niebezpieczni, posługują się ciężka bronią i zbroja. Elfi szermierze posługują się lekka bronią i zbroja aby móc wykorzystać swoja zwinność podczas walki. Natomiast ludzie akceptujący tę profesję zostają równie dobrymi zbrojnymi co zwinnymi wojownikami.<br /> Cechy charakterystyczne wojownika <ul> <li>Premia do umięjętności atakujących oraz uników w wysokości 1/10 danej umiejętności</li><li>Możliwość używania ataków ofensywnych oraz defensywnych w walce turowej</li>";
	$arrStats = array(1, 1, 1, 0, -1, 0, 5);
	break;
      case 'mag':
	$strClass = "Magowie maja swój stereotyp którego skrupulatnie się trzymają. Elfy najczęściej akceptują tą klasę choć ludzie nie wydają się być ta profesją mniej zainteresowane, natomiast krasnoludy krzywo na nią patrzą. Magów nie obchodzą bronie czy zbroje. Nie uważają iż siła czy wytrzymałość są najważniejsze - w sumie uważają je za zbędne. Siła maga są jego czary. W Vallheru magowie tworzą ścisłą elitę i zazwyczaj trzymają się razem. Są mądrzy i charyzmatyczni, inteligentni i roztropni - lecz są tez szaleni i nierozważni czarodzieje którzy wymyślają nowe dziwne zaklęcia. Magami zostają przeważnie osoby które chcą poznać tajniki wiedzy i potęgi magicznej aby mogli stać się najpotężniejszymi magami swych czasów.<br />Krasnoludy nie wybierają tej klasy chyba że są to jakieś bękarty - tacy zostają najczęściej szalonymi wynalazcami. Jest to ulubiona klasa elfów które są mądre i roztropne. Ludzcy magowie są inteligentni i utalentowani lecz znajdują się też wśród nich dziwni ludzie których nie powinno się dopuszczać do magii.<br />Cechy charakterystyczne Magów:<ul><li>Jako jedyni mogą używać czarów bojowych oraz obronnych</li><li>Mogą używać broni (ale nie jednocześnie z czarem bojowym) oraz nosić pancerze (chociaż te ograniczają ich czary - każdy poziom pancerza obniża siłę czarów maga o 1%)</li><li>Ich liczba punktów magii w zależności od Inteligencji oraz Siły Woli, jest dwa razy większa niż u innych klas.</li>";
	$arrStats = array(0, 0, 0, 0, 1, 1, 3);
	break;
      case 'craftsman':
	$strClass = "Tę klasę tworzą rzemieślnicy, kowale, kolekcjonerzy chowańców i kupcy. Grupa ta nie ma określonych ulubionych ras którzy wybierają ta profesje. Każdy krasnolud, elf czy człowiek nadaje się na kupca czy kowala. Jednak tylko rzemieślnicy potrafią najzręczniej posługiwać się kowalskim młotem lub kupieckim sztucznym urokiem. Używają broni białej gdyż nie interesują ich zaklęcia - w zasadzie walka także lecz nigdy nie wiadomo z kim przyjdzie handlować wiec zawsze noszą przy sobie broń. Zostają bogatymi handlarzami, silnymi kowalami lub chciwymi szulerami lub przemytnikami. Rzemieślnikami zostają osoby którymi prowadzi chęć szybkiego zarobku a potem osiedlenie się i rozkręcanie interesu. Jednak niektórzy zarabiając nielegalnie pieniądze zakładają potajemne gildie złodziei lub przemytników.<br />Każda rasa ma takie same predyspozycje na rzemieślnika.<br />Cechy Rzemieślnika:<ul><li>Premia do umiejętności rzemieślniczych w wysokości 1/10 poziomu wybranej umiejętności</li><li>Kara 25% do zadawanych obrażeń w walce</li>";
	$arrStats = array(0, 0, 0, 0, 0, 0, 2);
	break;
      case 'barbarzynca':
	$strClass = "<i>Byłeś wspaniałym przeciwnikiem! Wspomnę twe imię gdy podczas uczt będę pił najlepsze trunki z twej czaszki!</i><br />Gdyby wojownika nazwać synem wojny, barbarzyńcę trzeba by było nazwać jej mężem. Z dalekich i dzikich miejsc całego Vallheru u bram ".$city1b." pojawili się barbarzyńcy, głodni bogactw oraz sławy. Ich domem jest pole bitwy, ich językiem dźwięk wydawany przez broń w momencie ataku. Są najbardziej nieokrzesaną grupą obywateli wśród mieszkańców Vallheru. Nie cierpią magii ani jakiejkolwiek pomocy ze strony magów, uważają iż o chwale barbarzyńcy decyduje on sam a nie jakieś nieznane moce. Unikają przez to jakiejkolwiek formy magii czy to czarów czy magicznych broni. Jednak owa niechęć sprawia, iż są bardziej odporni na działania uroków niż inne kasty mieszkańców. Owe uprzedzenia do magów wywołały już kilka potężnych bitew z magami. Na ślady owych walk można natknąć się czasami, podróżując po bezdrożach Vallheru. Najwięcej barbarzyńców można spotkać pośród Jaszczuroludzi, najmniej - pośród Hobbitów. Ich główną domeną jest walka, lecz podobnie jak wojownicy jako tako radzą sobie również z kowalstwem czy stolarstwem. Niezależnie czy barbarzyńcą jest Krasnolud czy Człowiek, kasta ta ma takie same cechy<br />Cechy Barbarzyńcy<ul><li>Premia do umiejętności ataku oraz uniku w wysokości 1/10 danej umiejętności</li><li>Premia do odporności na magię w wysokości 1/2 poziomu siły woli</li><li>Możesz używać dwóch broni jednoręcznych na raz</li><li>Barbarzyńcy nie mogą używać ani czarów ani przedmiotów magicznych - mogą nosić je w plecaku ale nie mogą ich zakładać</li><li>W trakcie walki turowej mogą używać ataków ofensywnych";
	$arrStats = array(1, 1, 1, 0, -1, 1, 5);
	break;
      case 'zlodziej':
	$strClass = "<i>Hej, przyjacielu! Potrzebujesz może pomocy? Mogę ci pokazać parę ciekawych miejsc w naszym pięknym mieście! Choć pokażę ci skrót, proszę tą wąską uliczką. Kim są ci uzbrojeni osobnicy? To moi wspólnicy, razem prowadzimy interesy...</i><br /> Mówi się, że kiedy pojawiła się inteligencja pojawili się i oni - Złodzieje. Ponoć to jeden z najstarszych zawodów świata, jednak na pewno jeden z mniej poważanych. Nikt nie lubi złodziei ale każdy chciałby mieć ich po swojej stronie - z jeszcze innej strony zaprzyjaźnienie się z kimś takim to jak zaprzyjaźnienie się z kobrą. Jedno jest pewne - już nie będziesz się nudził więcej w towarzystwie złodzieja. Złodzieje to przede wszystkim sprytni osobnicy, którzy ponad siłę i otwartą, honorową walkę stawiają podstępy oraz oszustwo. Kiedy mogą, unikają walki, lecz gdy już do niej staną starają się użyć wszelkich sposobów aby wygrać. Niestety to wszystko powoduje iż nie są zbyt lubiani pośród mieszkańców Vallheru, a już na pewno siły porządkowe próbują zwalczać ich na każdym kroku. Wielu z nich prowadzi podwójne życie, tak aby zabezpieczyć się na wypadek gdyby coś poszło nie tak. Lecz nawet w tej wydawałoby się do końca zepsutej kaście, czasami natknąć można się na kogoś kto wspiera innych bez oglądania się na własne korzyści. Tacy najczęściej zostają zwiadowcami karawan, czy też przewodnikami grup poszukiwaczy przygów. Jednego można się spodziewać po złodzieju - tego że nic nie jest pewne. Najczęściej tę ścieżkę życiową wybierają Hobbici, choć słyszano również o Jaszczuroludziach którzy pałali się tą profesją. Pewne cechy są niezależne od rasy danej postaci.<br />Cechy Złodzieja:<ul><li>Możliwość okradania banku, gracza lub sklepu</li><li>Możliwość szpiegowania klanów raz na reset</li><li>Możliwość sabotowania budowy Astralnej Machiny raz na reset</li><li>Jeżeli złodziej dokona nieudanej próby kradzieży (zostanie złapany) - trafia do lochów na jakiś czas</li>";
	$arrStats = array(0, 1, -1, 1, 1, -1, 4);
	break;
      default:
	error('Zapomnij o tym.');
	break;
      }
    if (isset($_GET['step']))
      {
	switch ($_GET['klasa'])
	  {
	  case 'wojownik':
	    $player->clas = 'Wojownik';
	    $strClas2 = 'kastę wojowników';
	    break;
	  case 'mag':
	    $player->clas = 'Mag';
	    $strClas2 = 'kastę magów';
	    break;
	  case 'craftsman':
	    $player->clas = 'Rzemieślnik';
	    $strClas2 = 'kastę rzemieślników';
	    break;
	  case 'barbarzynca':
	    $player->clas = 'Barbarzyńca';
	    $strClas2 = 'drogę barbarzyńcy';
	    break;
	  case 'zlodziej':
	    $player->clas = 'Złodziej';
	    $strClas2 = 'ścieżkę złodzieja';
	    break;
	  }
	$i = 0;
	foreach ($player->stats as &$arrStat)
	  {
	    $arrStat[2] += $arrStats[$i];
	    $i++;
	  }
	$i = 0;
	foreach ($player->oldstats as &$arrStat)
	  {
	    $arrStat[2] += $arrStats[$i];
	    $i++;
	  }
	$db -> Execute("UPDATE `players` SET `klasa`='".$player->clas."' WHERE `id`=".$player -> id);
	error ("<br />Wybrałeś ".$strClas2.". Kliknij <a href=\"stats.php\">tutaj</a> aby wrócić.");
      }
    $smarty->assign(array("Classinfo" => $strClass,
			  "Tstats" => array('do siły', 'do zręczności', 'do kondycji', 'do szybkości', 'do inteligencji', 'do siły woli', 'punktów życia za każdy zdobyty poziom Kondycji'),
			  "Stats" => $arrStats));
  }
else
  {
    $_GET['klasa'] = '';
    $smarty -> assign(array("Info" => "Tutaj możesz wybrać klasę swojej postaci. Każda klasa ma swoje plusy i minusy. Zastanów się dobrze, ponieważ poźniej nie będziesz już mógł zmienić swojej klasy",
			    "Awarrior" => "Wojownik",
			    "Amage" => "Mag",
			    "Aworker" => "Rzemieślnik",
			    "Athief" => "Złodziej",
			    "Abarbarian" => "Barbarzyńca"));
  }

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Clas" => $_GET['klasa'], 
			"Aback" => "Wróć",
			"Aselect" => "Wybierz"));
$smarty -> display ('klasa.tpl');

require_once("includes/foot.php");
?>
