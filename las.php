<?php
/**
 *   File functions:
 *   Forest menu
 *
 *   @name                 : las.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 13.12.2012
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

$title = "Las Avantiel";
require_once("includes/head.php");

if($player -> location != 'Las') 
{
    error ("Nie znajdujesz się w lesie");
}

$smarty -> assign("Message", '');

if ($player -> hp > 0) 
{
    $smarty -> assign(array("Forestinfo" => "Witaj w Lesie Avantiel, co chcesz robić?",
                            "Alumberjack" => "Idź rąbać drewno",
                            "Aexplore" => "Zwiedzaj las",
                            "Atravel" => "Stajnia",
                            "Aelfcity" => "Bramy ".$city2,
                            "Res1" => '',
                            "Res2" => '',
                            "Res3" => '',
                            "Aback" => '',
                            "Hermit" => '',
                            "Hermit2" => '',
                            "Aresurect" => '',
                            "Await" => '',
                            "Tgold" => '',
                            "Goldcoins" => '',
                            "Goldneed" => '',
                            "Waittime" => ''));
} 
    else 
{
    $smarty -> assign(array("Youdead" => "Jesteś martwy",
                            "Backto" => "Powrót do ".$city2,
                            "Stayhere" => "Pozostań na miejscu"));
    if (isset($_GET['action']) && $_GET['action'] == 'back')
    {
        $db -> Execute("UPDATE `players` SET `miejsce`='Ardulith' WHERE `id`=".$player -> id);
        error ("Ponieważ jesteś martwy, twa dusza podąża z powrotem do szpitala w ".$city2.". Kliknij <a href=\"hospital.php\">tutaj</a>.");
    }
    if (isset($_GET['action']) && $_GET['action'] == 'hermit')
    {
        $crneed = (50 * $player -> stats['condition'][2]);
        require_once('includes/counttime.php');
        $arrTime = counttime();
        $strTime = $arrTime[0].$arrTime[1];
        $smarty -> assign(array("Goldneed" => $crneed,
                                "Waittime" => $strTime,
                                "Aback" => "Wróć",
                                "Hermit" => "Twoje ciało odnalazł mieszkający w pobliżu pustelnik. Ku twemu zdziwieniu, starzec ten posiadł zdolność kontaktowania się z duszami umarłych... Zauważasz to, gdy zaczynasz słyszeć słowa...",
                                "Hermit2" => "Bardzo nieostrożnie z Twojej strony wybierać się samemu w tak daleką podróż. Wiele niebezpieczeństw czyha na samotników. Ale spróbuję Ci pomóc. Spróbuję przygotować odpowiedni czar, który Cię wskrzesi, jednak to trochę potrwa. Jest jeszcze możliwość skorzystania z pobliskiego źródełka z pobłogosławioną przez Illuminati wodą. Jej efekt jest natychmiastowy, jednak aby zadziałała muszę złożyć ofiarę w postaci złota. Jestem pustelnikiem więc nie mam żadnego złota przy sobie. O ile wyrazisz zgodę to wezmę złoto z Twojej sakwy. Przysięgam, że oprócz potrzebnej ilości nie wezmę ani sztuki złota więcej. Wybór należy do Ciebie...",
                                "Aresurect" => "Skorzystaj z pobłogosławionej wody",
                                "Await" => "Zaczekaj aż pustelnik przygotuje czar",
                                "Tgold" => "Ofiara:",
                                "Goldcoins" => "sztuk złota"));
        if (isset($_GET['action2']) && $_GET['action2'] == 'resurect') 
        {
            require_once('includes/resurect.php');
	    if ($lostexp > 0)
	      {
		$strLost = ', ale straciłeś '.$lostexp.' Punktów Doświadczenia do '.$strLoststat;
	      }
	    else
	      {
		$strLost = '';
	      }
            $smarty -> assign(array("Message" => "<br />Zostałeś wskrzeszony".$strLost.".",
                                    "Res1" => "Otwierasz powoli oczy... Widzisz klęczącego nad tobą starca, a w ustach odczuwasz jeszcze słodkawy smak błogosławionej wody... Ku twemu zdziwieniu nie czujesz żadnego bólu. Po ranach, które odniosłeś nie ma śladu...",
                                    "Res2" => "Tutaj są Twoje rzeczy. Ja niestety muszę iść, gdyż zapewne są inni potrzebujący pomocy. Na przyszłość postaraj się zachować nieco więcej ostrożności. Bywaj w zdrowiu...",
                                    "Res3" => "Po wypowiedzeniu tych słów pustelnik oddala się. Po chwili odpoczynku podnosisz się, zbierasz swój ekwipunek i wyruszasz w drogę."));
        }
        if (isset($_GET['action2']) && $_GET['action2'] == 'wait')
        {
            $smarty -> assign("Message", "Przed Twoimi oczami przebiegają wydarzenie z przeszłości... To wspomnienia. Czas dłuży się niesamowicie... Nagle słyszysz słowa:<br /><br /><i>Cierpliwości. Właśnie przygotowuję czar dla Ciebie. Na szczęście mam już potrzebne składniki, ale rzucenie wskrzeszającego czaru to nie taka prosta sprawa. Trzeba być ostrożnym, gdyż nie wiem jakie konsekwencje mogłaby mieć moja pomyłka.</i>");
        }
    }
}

if (isset($_GET['action2']) && $_GET['action2'] == 'city')
{
    $db -> Execute("UPDATE `players` SET `miejsce`='Ardulith' WHERE `id`=".$player -> id);
    $smarty -> assign("Message", "Po krótkim spacerze dochodzisz do potężnych bram odgradzających miasto od najdzikszych i zapominanych przez bogów oraz elfy ostępów lasu Avantiel. Kliknij <a href=\"city.php\">tutaj</a>");
}

/**
* Initialization of variables
*/
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}
if (!isset($_GET['action2']))
{
    $_GET['action2'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Health" => $player -> hp,
                        "Action" => $_GET['action'],
                        "Action2" => $_GET['action2']));
$smarty -> display ('las.tpl');

require_once("includes/foot.php"); 
?>
