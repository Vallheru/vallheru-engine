<?php
/**
 *   File functions:
 *   Register new players
 *
 *   @name                 : register.php                            
 *   @copyright            : (C) 2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 10.12.2012
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

require 'libs/Smarty.class.php';
require_once ('includes/config.php');

$smarty = new Smarty;
$smarty -> compile_check = true;

$objOpenreg = $db -> Execute("SELECT value FROM settings WHERE setting='register'");
/**
* When registration is closed
*/
if ($objOpenreg -> fields['value'] == 'N') 
{
    $objReason = $db -> Execute("SELECT value FROM settings WHERE setting='close_register'");
    $smarty -> assign ("Error", REASON.":<br />".$objReason -> fields['value']);
    $objReason -> Close();
    $smarty -> display ('error.tpl');
    exit;
}
$objOpenreg -> Close();

$query = $db -> Execute("SELECT count(`id`) FROM `players`");
$nump = $query->fields['count(`id`)'];
$query -> Close(); 

$time = date("H:i:s");
$hour = explode(":", $time);
$newhour = $hour[0] + 0;
if ($newhour > 23) 
{
    $newhour = $newhour - 24;
}
$arrtime = array($newhour, $hour[1], $hour[2]);
$newtime = implode(":",$arrtime);

$smarty -> assign(array("Gamename" => $gamename, 
                        "Meta" => '',
                        "Welcome" => "Witaj",
                        "Register" => "Dołącz do nas",
                        "Rules" => "Zasady",
                        "Links" => "Linki",
                        "Time" => $newtime, 
                        "Players" => $nump, 
                        "Email" => "Email",
                        "Password" => "Hasło",
                        "Login" => "Zaloguj",
                        "Lostpasswd" => "Zapomniałem hasła",
                        "Ctime" => "Obecny czas",
                        "Whave" => "Mamy",
                        "Registered" => " zarejestrowanych graczy",
                        "Ingame" => "graczy w grze",
                        "Charset" => "utf-8",
			"Donate" => "Dotuj nas",
			"Promote" => "Promocja gry",
			"Help" => "Poradnik",
                        "Pagetitle" => "Rejestracja",
			"Gtype" => "Wygląd gry",
			"Gtext" => "Tekstowy (minimum grafiki, czarne tło)",
			"Ggraphic" => "Graficzny (jasne tło, obecnie w fazie rozwoju)",
			"Ginfo" => "Te ustawienia można zmienić później w grze"));

if (isset($_GET['ref'])) 
{
    $smarty -> assign("Referal",$_GET['ref']);
} 
    else 
{
    $smarty -> assign("Referal","");
}

if (!isset($_GET['action']))
{
    $smarty -> assign(array("Description" => "Zarejestruj się w grze. To nic nie kosztuje. Po rejestracji na twoje konto email zostanie wysłany specjalny link aktywacyjny. <b>Uwaga!</b> Jeżeli mail nie dojdzie w ciągu 5-10 minut - sprawdź czy nie znajduje się w spamie w skrzynce pocztowej. Jeżeli mimo wszystko nie otrzymałeś maila aktywacyjnego na swoją skrzynkę pocztową, prosimy, skontaktuj się z nami na adres podany na stronie głównej gry. <br />Hasło musi składać się z co najmniej 5 znaków z czego musi być co najmniej jedna wielka litera (A,G,W, itd) oraz cyfra.<br />Mamy obecnie ",
        "Nick" => "Pseudonim:",
        "Confemail" => "Potwierdź email:",
        "Referralid" => "ID polecającego:",
        "Ifnoid" => "Jeżeli nie jesteś czyimś poleconym, to pole pozostaw puste.",
        "Register2" => "Zarejestruj",
        "Shortrules" => "Krótki spis zasad w grze:",
        "Rule1" => "W grze obowiązuje netykieta - w wielkim skrócie - nie rób drugiemu co tobie nie miłe.",
        "Rule2" => "Wielokrotne ataki na jednego gracza w ciągu kilku minut - czyli zwykłe nękanie - są karane.",
        "Rule3" => "Wykorzystywanie błędów w grze do zdobycia przewagi nad innymi kończy się najczęściej skasowaniem postaci. Natomiast pomoc w ich znalezieniu może zostać nagrodzona przyznaniem specjalnej rangi.",
        "Rule4" => "W sprawie jakichkolwiek naruszeń prawa możesz zgłaszać to do książąt - oni najczęściej również wymierzają kary.",
        "Rule5" => "Jeżeli nie zgadzasz się z karą, możesz zawsze decyzję zaskarżyć do Sądu Najwyższego ".$gamename." - jego siedziba znajduje się w każdym mieście.",
        "Rule6" => "Zabrania się posiadania więcej niż 1 konta na osobę.",
        "Rule7" => "Więcej informacji na ten temat znajdziesz w <a href=\"index.php?step=rules\">regulaminie gry</a>.",
        "Rule8" => "Pamiętaj, jeżeli chcesz grać w tę grę, musisz zaakceptować zasady w niej obowiązujące.",
        "Description2" => "Nagle otacza Cię ciemność. Czujesz subtelne zawirowanie w otaczającym Cię powietrzu. W tej samej chwili pojawia się błękitnawa sfera, we wewnętrzu której zauważasz liczne wyładowania elektryczne. Powietrze wypełnia się charakterystycznym ostrym zapachem, co niewatpliwie jest wynikiem działania wielkiej magii. Owa sfera to magiczny portal, przez który wychodzi powoli, dumnie starzec, ubrany w szmaragdowozieloną, wyszywaną srebrzystymi runami szatę. Oburącz trzyma kościaną laskę, a u jego pasa wisi kilka różnorakich różdżek.<br />- \"Jestem Elmanir, Nadworny Mag Jaśnie Wielmożnego Króla Thindilla I - władcy Królestwa Vallheru. Siedziałem w mojej astralnej wieży, studiując stare runy, gdy nagle usłyszałem cichy głos - Twój głos...\"<br />- \"Mój?\" - pytasz zdziwiniony i lekko przestrzaszony - \"Przecież nic nie mówiłem.\"<br />- \"Tak - Twój wewnętrzny głos - zew Twojego ducha. Jesteś żądny przygód i nie ukryjesz tego przed moimi wieszczymi czarami.\"<br />Nagle głos maga stężał i stał się bardzo donośny - \"Czy chcesz zagłębić się w królestwo magii i miecza? Czy chcesz zostać jednym z bohaterów pokonując straszliwe bestie, a nawet samego Astralnego Strażnika? Być może to Ty jesteś owym śmiałkiem, o którym opowiadają przepowiednie zapisane na starożytnych zwojach - bohater, który wprowadzi świat Vallheru w kolejną burzliwą erę.\" - słowa starego maga brzmią dostojnie i zagadkowo. Wciąż jesteś oszołomiony pokazem tak wielkiej i niespotykanej mocy. Cały drżysz, na myśl o tym co stanie się za chwilę. Jednak czai się w Tobie także iskierka awanturnika, poszukiwacza przygód, żądnego sławy obieżyświata.<br />- \"Widzę, że moje słowa mocno zaintrygowały Cię.\" - uśmiechnął się krzywo, lekko znudzony mag, odgadując Twoje skryte pragnienia.<br />- \"Jeśli masz na tyle odwagi, aby stawić czoła surowemu i niebezpiecznemu światu podążaj za mną.\" - to mówiąc czarodziej odwrócił się i wkroczył w elektryczną sferę, po czym zniknął, wśród ławicy licznych iskier. Pozostałeś sam, stojąc niepewny, walcząc jednocześnie ze strachem i ciekawością. Czy masz odwagę by wkroczyć w nowy nieznany świat...?"));
}

if (isset ($_GET['action']) && $_GET['action'] == 'register') 
{
/**
* Check for empty fields
*/
    if (!$_POST['user'] || !$_POST['email'] || !$_POST['vemail'] || !$_POST['pass'] ) 
    {
        $smarty -> assign ("Error", "Musisz wypełnić wszystkie pola.");
        $smarty -> display ('error.tpl');
        exit;
    }
    
/**
* Email adress validation
*/       
    require_once('includes/verifymail.php');
    if (MailVal($_POST['email'], 2)) 
    {
        $smarty -> assign ("Error", "Nieprawidłowy adres email.");
        $smarty -> display ('error.tpl');
        exit;
    }
    require_once('includes/verifypass.php');
    verifypass($_POST['pass'],'register');

/**
* Check nick
*/
    $strUser = $db -> qstr($_POST['user'], get_magic_quotes_gpc());
    $query = $db -> Execute("SELECT id FROM players WHERE user=".$strUser);
    $dupe1 = $query -> RecordCount();
    $query -> Close();  
    if ($dupe1 > 0) 
    {
        $smarty -> assign ("Error", "Ktoś już wybrał taki pseudonim.");
        $smarty -> display ('error.tpl');
        exit;
    }

/**
* Check mail adress in database
*/   
    $strEmail = $db -> qstr($_POST['email'], get_magic_quotes_gpc());
    $query = $db -> Execute("SELECT id FROM players WHERE email=".$strEmail);
    $dupe2 = $query -> RecordCount();
    $query -> Close();
    if ($dupe2 > 0) 
    {
        $smarty -> assign ("Error", "Ktoś już posiada taki adres mailowy.");
        $smarty -> display ('error.tpl');
        exit;
    }

/**
* Check email adress writed on registration
*/ 
    if ($_POST['email'] != $_POST['vemail']) 
    {
        $smarty -> assign ("Error", "Oba adresy emailowe nie zgadzają się.");
        $smarty -> display ('error.tpl');
        exit;
    }
    
    if (!$_POST['ref']) 
    {
        $_POST['ref'] = 0;
    }
    
    $ref = intval($_POST['ref']);
    $_POST['user'] = strip_tags($_POST['user']);
    $strUser = $db -> qstr($_POST['user'], get_magic_quotes_gpc());
    $_POST['email'] = strip_tags($_POST['email']);
    $strEmail = $db -> qstr($_POST['email'], get_magic_quotes_gpc());
    $_POST['pass'] = strip_tags($_POST['pass']);
    if (!in_array($_POST['gtype'], array('T', 'G')))
      {
	$smarty -> assign ("Error", "Zapomnij o tym.");
        $smarty -> display ('error.tpl');
        exit;
      }
    $aktw = rand(1,10000000);
    $data = date("y-m-d");
    $strDate = $db -> DBDate($data);
    $ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
    $message = "Witaj w ".$gamename.". Twój link aktywacyjny to: ".$gameadress."/aktywacja.php?kod=".$aktw."  <br /> Życzę miłej zabawy w ".$gamename.". ".$adminname;
    $adress = $_POST['email'];
    $subject = "Rejestracja na ".$gamename;
    require_once('mailer/mailerconfig.php');
    if (!$mail -> Send()) 
    {
        $smarty -> assign("Error", "Wiadomość nie została wysłana. Błąd:<br /> ".$mail -> ErrorInfo);
        $smarty -> display('error.tpl');
        exit;
    }
    $strPass = MD5($_POST['pass']);
    $db -> Execute("INSERT INTO `aktywacja` (`user`, `email`, `pass`, `refs`, `aktyw`, `data`, `ip`, `gtype`) VALUES(".$strUser.", ".$strEmail.", '".$strPass."', ".$ref.", ".$aktw.", ".$strDate." , '".$ip."', '".$_POST['gtype']."')") or die($db -> ErrorMsg());
    $smarty -> assign("Registersuccess", "Jesteś już zarejestrowany. Sprawdź swoją skrzynkę pocztową.");
}

/**
* Initialization of variable
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
}

/**
* Assign variables and display page
*/
$objKeywords = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='metakeywords'");
$objDesc = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='metadescr'");
$smarty -> assign(array("Action" => $_GET['action'], 
			"Metakeywords" => $objKeywords->fields['value'], 
			"Metadescription" => $objDesc->fields['value']));
$objKeywords->Close();
$objDesc->Close();
$smarty -> display('register.tpl');

$db -> Close();
?>
