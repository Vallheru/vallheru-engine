<?php
/**
 *   File functions:
 *   Outpost - random missions for fighters, barbarians and mages
 *
 *   @name                 : outpost.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 20.12.2011
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

$title = 'Prefektura Gwardii';
require_once('includes/head.php');

if($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

if (!in_array($player->clas, array('Wojownik', 'Barbarzyńca', 'Mag')))
  {
    error("Drogę zastępuje tobie straż. <i>Tylko parający się bronią bądź magią mają wstęp do tego budynku!</i><a href=");
  }

/**
 * Get job
 */
$objJob = $db->Execute("SELECT `craftmission` FROM `players` WHERE `id`=".$player->id);
if (isset($_GET['step']))
  {
    /**
     * Generate random tasks
     */
    if ($_GET['step'] == 'first')
      {
	if ($objJob->fields['craftmission'] == 'Y')
	  {
	    error("Niestety, na chwilę obecną, nie mamy dla ciebie jakiegokolwiek rozkazu. Proszę wróć za jakiś czas. (<a href=city.php>Powrót do miasta</a>)");
	  }
	switch ($player->clas)
	  {
	  case 'Wojownik':
	    $arrAvailable = array(0, 1, 2, 5, 7, 9);
	    break;
	  case 'Mag':
	    $arrAvailable = array(0, 2, 7, 9, 10);
	    break;
	  case 'Barbarzyńca':
	    $arrAvailable = array(1, 3, 4, 6, 8);
	    break;
	  default:
	    break;
	  }
	$_SESSION['craft'] = array();
	$_SESSION['mdata'] = array();
	$arrInfo = array();
	for ($i = 0; $i < 3; $i++)
	  {
	    $intIndex = array_rand($arrAvailable);
	    $_SESSION['craft'][$i] = $arrAvailable[$intIndex];
	    switch ($arrAvailable[$intIndex])
	      {
		//Road patrol
	      case 0:
		if ($player->location == 'Altara')
		  {
		    $strCity = $city2;
		  }
		else
		  {
		    $strCity = $city1b;
		  }
		$arrLocations = array($strCity, 'lasu Avantiel', 'gór Kazad-nar');
		$intLoc = array_rand($arrLocations);
		$_SESSION['mdata'][$i] = $intLoc;
		$arrInfo[$i] = 'Potrzebujemy kogoś, kto dołączy do patrolu na drodze do '.$arrLocations[$intLoc].'.';
		break;
		//Train soldiers
	      case 1:
		$arrInfo[$i] = 'Ktoś o twoich umiejętnościach mógłby nam pomóc szkolić rekrutów.';
		break;
		//Hunt for bandits
	      case 2:
		$arrLocations = array($city1b, $city2, 'lasu Avantiel', 'gór Kazad-nar');
		$intLoc = array_rand($arrLocations);
		$_SESSION['mdata'][$i] = $intLoc;
		$arrInfo[$i] = 'Namierzyliśmy ostatnio kryjówkę bandytów niedaleko '.$arrLocations[$i].' i organizujemy grupę by ich schwytać.';
		break;
		//Mountain patrol
	      case 3:
		$arrInfo[$i] = 'Potrzebujemy kogoś, kto dołączy do patrolu w Górach Kazad-nar.';
		break;
		//Forest patrol
	      case 4:
		$arrInfo[$i] = 'Potrzebujemy kogoś, kto dołączy do patrolu w Lesie Avantiel.';
		break;
		//City patrol
	      case 5:
		$arrInfo[$i] = 'Ostatnio złodzieje w mieście nieco się rozpanoszyli. Przyda się ktoś, kto wesprze nasze patrole.';
		break;
		//Hunt for monsters
	      case 6:
		$arrLocations = array('lesie Avantiel', 'górach Kazad-nar');
		$intLoc = array_rand($arrLocations);
		if ($intLoc == 0)
		  {
		    $strLoc = 'Ardulith';
		  }
		else
		  {
		    $strLoc = 'Altara';
		  }
		$objMonster = $db->Execute("SELECT `id`, `name` FROM `monsters` WHERE `location`='".$strLoc."' AND `level`<=".$player->level." ORDER BY RAND() LIMIT 1");
		$_SESSION['mdata'][$i] = $objMonster->fields['id'];
		$arrInfo[$i] = 'Namierzyliśmy gniazdo potworów: '.$objMonster->fields['name'].', które napadają na karawany w '.$arrLocations[$intLoc].' i organizujemy grupę aby je wytępić.';
		$objMonster->Close();
		break;
		//Attack on bandits
	      case 7:
		$arrLocations = array($city1b, $city2, 'lasu Avantiel', 'gór Kazad-nar');
		$intLoc = array_rand($arrLocations);
		$_SESSION['mdata'][$i] = $intLoc;
		$arrInfo[$i] = 'Ostatnio mocno dają się we znaki bandyci w okolicach '.$arrLocations[$intLoc].'. Twoje umiejętności kwalifikują ciebie jako dowódcę patrolu. Dostaniesz paru żołnierzy i spróbujesz ich odnaleźć.';
		break;
		//Attack on monsters
	      case 8:
		$arrLocations = array('lesie Avantiel', 'górach Kazad-nar');
		$intLoc = array_rand($arrLocations);
		if ($intLoc == 0)
		  {
		    $strLoc = 'Ardulith';
		  }
		else
		  {
		    $strLoc = 'Altara';
		  }
		$objMonster = $db->Execute("SELECT `id`, `name` FROM `monsters` WHERE `location`='".$strLoc."' AND `level`<=".$player->level." ORDER BY RAND() LIMIT 1");
		$_SESSION['mdata'][$i] = $objMonster->fields['id'];
		$arrInfo[$i] = 'Potwory: '.$objMonster->fields['name'].' w '.$arrLocations[$intLoc].' rozzuchwaliły się do tego stopnia, że zaczęły atakować karawany. Dostaniesz paru ludzi i spróbujesz odnaleźć je.';
		$objMonster->Close();
		break;
		//Guard caravan
	      case 9:
		if ($player->location == 'Altara')
		  {
		    $strCity = $city2;
		  }
		else
		  {
		    $strCity = $city1b;
		  }
		$arrLocations = array($strCity, 'lasu Avantiel', 'gór Kazad-nar');
		$intLoc = array_rand($arrLocations);
		$_SESSION['mdata'][$i] = $intLoc;
		$arrInfo[$i] = 'Ostatnimi czasy podróżni skarżą się na bandytów którzy napadają na karawany. Dlatego organizujemy oddział który dołączy do najbliższej karawany do '.$arrLocations[$intLoc].'.';
		break;
		//Train adepts
	      case 10:
		$arrInfo[$i] = 'Ktoś o twoich umiejętnościach mógłby nam pomóc szkolić naszych adeptów magii.';
		break;
	      default:
		break;
	      }
	  }
	$smarty->assign(array('Jobinfo' => 'Mężczyzna zaprasza ciebie do swojego biura. Tam przez pewien czas odpowiadasz na krótkie pytania dotyczące twoich umiejętności. Po każdej odpowiedzi, gwardzista zapisuje coś na kartce. W pewnym momencie przegląda stos papierów leżący na biurku, wybiera kilka z nich i oświadcza:<br /><i>-Zdaje się, że mamy zlecienia dla ciebie</i>',
			      "Ayes" => "Przyjmuję zlecienie (koszt: 5 energii)",
			      "Jobs" => $arrInfo,
			      "Ano" => "Nie, dziękuję",
			      'Jobinfo2' => 'Pamiętaj, oferta jest ważna tylko w tym momencie, jeżeli ją odrzucisz, następna szansa dopiero po kolejnym resecie.'));
	//$db->Execute("UPDATE `players` SET `craftmission`='Y' WHERE `id`=".$player->id);
      }
    /**
     * Start task
     */
    elseif ($_GET['step'] != 'first' && $_GET['step'] != 'fight')
      {
	if (!isset($_SESSION['craft']))
	  {
	    error('Zapomnij o tym.');
	  }
	$intIndex = intval($_GET['step']);
	if ($intIndex < 0 || $intIndex > 10)
	  {
	    error('Zapomnij o tym');
	  }
	if ($player->energy < 5)
	  {
	    unset($_SESSION['craft'], $_SESSION['mdata']);
	    error("Nie masz tyle energii.");
	  }
	$db->Execute("UPDATE `players` SET `energy`=`energy`-5  WHERE `id`=".$player->id);
	$intRoll = rand(1, 100);
	if ($player->gender == 'M')
	  {
	    $strSuffix = 'eś';
	  }
	else
	  {
	    $strSuffix = 'aś';
	  }
	switch ($_SESSION['craft'])
	  {
	    //Road patrol
	  case 0:
	    break;
	    //Train soldiers or adepts
	  case 1:
	  case 10:
	    $intGold = $player->level * 125;
	    if ($intRoll < 20)
	      {
		$intGold += $player->level * 50;
		$intExp = rand(1, 20) * $player->level;
		$strResult = 'Szkolenie poszło nadspodziewanie dobrze. Nawet Ty nauczył'.$strSuffix.' się czegoś nowego. W nagrodę za swoją pracę dostajesz '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		require_once("includes/checkexp.php");
		checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, "", 0);
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll < 80)
	      {
		$intExp = rand(1, 5) * $player->level;
		$strResult = 'Szkolenie poszło całkiem nieźle, Ty też czegoś się przy okazji nauczył'.$strSuffix.'. W nagrodę za swoją pracę dostajesz '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		require_once("includes/checkexp.php");
		checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, "", 0);
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    else
	      {
		$strResult = 'Albo trafił'.$strSuffix.' na jakieś beztalencia albo Ty nie potrafisz nauczać. Nic z tego nie wyszło, nawet nie otrzymał'.$strSuffix.' zapłaty za swoją pracę.';
	      }
	    break;
	    //Hunt for bandits
	  case 2:
	    break;
	    //Mountain patrol
	  case 3:
	    break;
	    //Forest patrol
	  case 4:
	    break;
	    //City patrol
	  case 5:
	    $intGold = $player->level * 125;
	    if ($intRoll < 20)
	      {
		$intGold += $player->level * 50;
		$intExp = rand(1, 20) * $player->level;
		$fltSkill = rand(1, 10) / 100;
		$strResult = 'Ten dzień zaliczysz do udanych. W pewnym momencie, udało ci się przyuważyć złodziejaszka, który próbował okraść mieszkańca. Dzięki Twojej szybkiej reakcji, przestępca trafił do lochów. W nagrodę za swoją pracę dostajesz '.$intGold.' sztuk złota, '.$intExp.' punktów doświadczenia oraz '$fltSkill' do umiejętności Spostrzegawczość.';
		require_once("includes/checkexp.php");
		checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, "perception", $fltSkill);
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll < 80)
	      {
		$intExp = rand(1, 5) * $player->level;
		$strResult = 'Nudny patrol ma się ku końcowi. Nic nie widział'.$strSuffix.', nic nie słyszał'.$strSuffix.'. Jednak mimo wszystko co nieco się nauczył'.$strSuffix.'. W nagrodę za swoją pracę dostajesz '.$intGold.' sztuk złota, '.$intExp.' punktów doświadczenia oraz 0.01 do umiejętności Spostrzegawczość.';
		require_once("includes/checkexp.php");
		checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, "perception", 0.01);
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    else
	      {
		$strResult = 'Po prostu pech. Zauważyłeś złodzieja jak okrada mieszkańca ale ten, zdążył uciec przed Tobą. Na dodatek w trakcie pogoni podarł'.$strSuffix.' mundur. Tym razem nie otrzymasz zapłaty za swoją pracę.';
	      }
	    break;
	    //Hunt for monsters
	  case 6:
	    break;
	    //Attack on bandits
	  case 7:
	    break;
	    //Attack on monsters
	  case 8:
	    break;
	    //Guard caravan
	  case 9:
	    break;
	  default:
	    break;
	  }
      }
  }
else
  {
    $_GET['step'] = '';
  }
$objJob->Close();
/**
 * Assign variables to template and display page
 */
$smarty->assign(array("Outinfo" => 'Znajdujesz się w dużym, wykonanym z szarego kamienia budynku. Na jego ścianach wiszą proporce królestwa. Długi hol prowadzi na kolejne piętro. Wokół panuje cisza. Kiedy tak rozglądasz się po okolicy, otwierają się jedne z drzwi i na korytarz wychodzi mężczyzna w mundurze Gwardii Królewskiej. Sprężystym krokiem podchodzi do ciebie i pyta:<br /><i>Słucham, w czym mogę pomóc?</i>',
		      "Ajob" => 'Szukam jakiejś pracy do wykonania.',
		      "Step" => $_GET['step']));
$smarty->display('outpost.tpl');

require_once('includes/foot.php');
?>