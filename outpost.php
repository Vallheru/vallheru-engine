<?php
/**
 *   File functions:
 *   Outpost - random missions for fighters, barbarians and mages
 *
 *   @name                 : outpost.php                            
 *   @copyright            : (C) 2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 12.12.2012
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

require_once('includes/funkcje.php');
require_once('includes/turnfight.php');

if($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

if (!in_array($player->clas, array('Wojownik', 'Barbarzyńca', 'Mag')))
  {
    error("Drogę zastępuje tobie straż. <i>Tylko parający się bronią bądź magią mają wstęp do tego budynku!</i><a href=");
  }

/**
 * Fight with bandits
 */
function battle($intEnemy = 0)
{
  global $db;
  global $player;
  global $enemy;
  global $smarty;

  if (!isset($_SESSION['enemy']))
    {
      if ($_GET['step'] != 'fight')
	{
	  $_SESSION['index'] = $_GET['step'];
	}
      if (!$intEnemy)
	{
	  require_once('includes/monsters.php');
	  $enemy = randommonster('Bandyta');
	}
      else
	{
	  $objMonster = $db->Execute("SELECT * FROM `monsters` WHERE `id`=".$intEnemy);
	  $intElevel = $objMonster->fields['strength'] + $objMonster->fields['agility'] + $objMonster->fields['speed'] + $objMonster->fields['endurance'] + $objMonster->fields['level'] + $objMonster->fields['hp'];
	  $intPlevel = $player->stats['condition'][2] + $player->stats['speed'][2] + $player->stats['agility'][2] + $player->skills['dodge'][1] + $player->hp;
	  if ($player->equip[0][0] || $player->equip[11][0] || $player->equip[1][0])
	    {
	      $intPlevel += $player->stats['strength'][2];
	      if ($player->equip[0][0] || $player->equip[11][0])
		{
		  $intPlevel += $player->skills['attack'][1];
		}
	      else
		{
		  $intPlevel += $player->skills['shoot'][1];
		}
	    }
	  else
	    {
	      $intPlevel += $player->stats['wisdom'][2] + $player->stats['inteli'][2] + $player->skills['magic'][1];
	    }
	  $span = ($intElevel / $intPlevel);
	  if ($span > 2) 
	    {
	      $span = 2;
	    }
	  $goldgain = ceil($intElevel * $span); 
	  $expgain = ceil($intElevel * $span);
	  $enemy = array('name' => $objMonster->fields['name'],
			 'strength' => $objMonster->fields['strength'],
			 'agility' => $objMonster->fields['agility'],
			 'hp' => $objMonster->fields['hp'],
			 'level' => $objMonster->fields['level'],
			 'endurance' => $objMonster->fields['endurance'],
			 'speed' => $objMonster->fields['speed'],
			 'exp1' => $expgain,
			 'gold' => $goldgain,
			 "lootnames" => explode(";", $objMonster->fields['lootnames']),
			 "lootchances" => explode(";", $objMonster->fields['lootchances']),
			 "resistance" => explode(";", $objMonster->fields['resistance']),
			 "dmgtype" => $objMonster->fields['dmgtype']);
	}
      $_SESSION['enemy'] = $enemy;
    }
  else
    {
      $enemy = $_SESSION['enemy'];
    }
  if ($player->fight == 0)
    {
      if (!$intEnemy)
	{
	  $db -> Execute("UPDATE `players` SET `fight`=99999 WHERE `id`=".$player->id);
	}
      else
	{
	  $db -> Execute("UPDATE `players` SET `fight`=".$intEnemy." WHERE `id`=".$player->id);
	}
    }
  $arrehp = array ();
  if (!isset ($_POST['action'])) 
    {
      turnfight ($enemy['exp1'], $enemy['gold'], '', 'outpost.php?step=fight');
    } 
  else 
    {
      turnfight ($enemy['exp1'], $enemy['gold'], $_POST['action'], 'outpost.php?step=fight');
    }
  $myhp = $db -> Execute("SELECT `hp`, `fight` FROM `players` WHERE `id`=".$player -> id);
  if ($myhp -> fields['fight'] == 0) 
    {
      unset($_SESSION['enemy']);
      if ($myhp->fields['hp'] == 0 || isset($_SESSION['ressurect']))
	{
	  $_SESSION['result'] = 2;
	  $smarty->assign("Message", '<br /><br />Niestety, tym razem przeciwnik okazał się silniejszy od Ciebie.');
	}
      else
	{
	  if (isset($_POST['action']) && $_POST['action'] == 'escape')
	    {
	      $_SESSION['result'] = 2;
	      $smarty -> assign ("Message", '<br /><br />Przerażony uciekasz z pola walki.');
	    }
	  else
	    {
	      $_SESSION['result'] = 1;
	      $smarty -> assign ("Message", '<br /><br />Dobijasz przeciwnika i rozglądasz się w około jak radzą sobie twoi towarzysze.');
	    }
	}
      $smarty -> display ('error1.tpl');
    }
  $myhp -> Close();
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
	if ($objJob->fields['craftmission'] <= 0)
	  {
	    error("Niestety, na chwilę obecną, nie mamy dla ciebie jakiegokolwiek rozkazu. Proszę wróć za jakiś czas. (<a href=city.php>Powrót do miasta</a>)");
	  }
	if ($player->hp <= 0)
	  {
	    unset($_SESSION['craft'], $_SESSION['mdata']);
	    error("Nie możesz przyjąć zlecenia, ponieważ jesteś martwy.");
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
	$intPlevel = $player->stats['condition'][2] + $player->stats['speed'][2] + $player->stats['agility'][2] + $player->skills['dodge'][1] + $player->hp;
	if ($player->equip[0][0] || $player->equip[11][0] || $player->equip[1][0])
	  {
	    $intPlevel += $player->stats['strength'][2];
	    if ($player->equip[0][0] || $player->equip[11][0])
	      {
		$intPlevel += $player->skills['attack'][1];
	      }
	    else
	      {
		$intPlevel += $player->skills['shoot'][1];
	      }
	  }
	else
	  {
	    $intPlevel += $player->stats['wisdom'][2] + $player->stats['inteli'][2] + $player->skills['magic'][1];
	  }
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
		$objMonster = $db->Execute("SELECT `id`, `name` FROM `monsters` WHERE `location`='".$strLoc."' AND `level`<=".$intPlevel." ORDER BY RAND() LIMIT 1");
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
		$objMonster = $db->Execute("SELECT `id`, `name` FROM `monsters` WHERE `location`='".$strLoc."' AND `level`<=".$intPlevel." ORDER BY RAND() LIMIT 1");
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
	$objJob->fields['craftmission'] --;
	if ($objJob->fields['craftmission'] > 0)
	  {
	    $strRefresh = 'Odśwież listę (możesz odświeżyć jeszcze '.$objJob->fields['craftmission'].' razy).';
	  }
	else
	  {
	    $strRefresh = '';
	  }
	$smarty->assign(array('Jobinfo' => 'Mężczyzna zaprasza ciebie do swojego biura. Tam przez pewien czas odpowiadasz na krótkie pytania dotyczące twoich umiejętności. Po każdej odpowiedzi, gwardzista zapisuje coś na kartce. W pewnym momencie przegląda stos papierów leżący na biurku, wybiera kilka z nich i oświadcza:<br /><i>-Zdaje się, że mamy zlecienia dla ciebie</i>',
			      "Ayes" => "Przyjmuję zlecienie (koszt: 5 energii)",
			      "Jobs" => $arrInfo,
			      "Ano" => "Nie, dziękuję",
			      'Jobinfo2' => $strRefresh));
	$db->Execute("UPDATE `players` SET `craftmission`=`craftmission`-1 WHERE `id`=".$player->id);
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
	if ($intIndex < 0 || $intIndex > 2)
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
	$intPlevel = $player->stats['condition'][2] + $player->stats['speed'][2] + $player->stats['agility'][2] + $player->skills['dodge'][1] + $player->hp;
	if ($player->equip[0][0] || $player->equip[11][0] || $player->equip[1][0])
	  {
	    $intPlevel += $player->stats['strength'][2];
	    if ($player->equip[0][0] || $player->equip[11][0])
	      {
		$intPlevel += $player->skills['attack'][1];
		$strSkill = 'attack';
	      }
	    else
	      {
		$intPlevel += $player->skills['shoot'][1];
		$strSkill = 'shoot';
	      }
	  }
	else
	  {
	    $intPlevel += $player->stats['wisdom'][2] + $player->stats['inteli'][2] + $player->skills['magic'][1];
	    $strSkill = 'magic';
	  }
	$intGold = $intPlevel * 5;
	if ($player->gender == 'M')
	  {
	    $strSuffix = 'eś';
	  }
	else
	  {
	    $strSuffix = 'aś';
	  }
	switch ($_SESSION['craft'][$intIndex])
	  {
	    //Road, mountain and forest patrol
	  case 0:
	  case 3:
	  case 4:
	    if ($intRoll < 80)
	      {
		$intExp = $intPlevel;
		$strResult = 'Patrol minął bez większych niespodzanek. Zwiedził'.$strSuffix.' sobie nieco okolicę, jednak nic ciekawego się nie wydarzyło. Po powrocie otrzymał'.$strSuffix.' '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		$player->checkexp(array($strSkill => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 79 && $intRoll < 91)
	      {
		$intExp = 2 * $intPlevel;
		$intGold += $intPlevel * 10;
		$strResult = 'Patrol zakończył się pełnym powodzeniem! Udało wam się podejść i ująć tych, których szukaliście. Po odeskortowaniu więźniów do miasta, otrzymał'.$strSuffix.' '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		$player->checkexp(array($strSkill => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 90 && $intRoll < 95)
	      {
		$smarty->assign("Message", 'Udało wam się odnaleźć tych, których szukaliście. Niestety, nie udało się ich zaskoczyć. Bandyci stanęli do walki!');
		$smarty -> display ('error1.tpl');
		battle();
	      }
	    else
	      {
		$intDamage = ceil(($player->max_hp / 100) * rand(1, 25));
		$player->hp -= $intDamage;
		if ($player->hp < 0)
		  {
		    $player->hp = 0;
		  }
		$db->Execute("UPDATE `players` SET `hp`=".$player->hp." WHERE `id`=".$player->id);
		$strResult = 'To nie był twój szczęśliwy dzień. Najpierw natknęliście się na resztki ograbionej karawany, następnie sami wpadliście w zasadkę bandytów. Odniosł'.$strSuffix.' '.$intDamage.' obrażeń. Niestety po powrocie, nie otrzymał'.$strSuffix.' zapłaty za zmarnowany czas.';
	      }
	    break;
	    //Train soldiers or adepts
	  case 1:
	  case 10:
	    if ($intRoll < 20)
	      {
		$intGold += $intPlevel * 5;
		$intExp = $intPlevel;
		$strResult = 'Szkolenie poszło nadspodziewanie dobrze. Nawet Ty nauczył'.$strSuffix.' się czegoś nowego. W nagrodę za swoją pracę dostajesz '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		$player->checkexp(array($strSkill => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 19 && $intRoll < 80)
	      {
		$intExp = $intPlevel;
		$strResult = 'Szkolenie poszło całkiem nieźle, Ty też czegoś się przy okazji nauczył'.$strSuffix.'. W nagrodę za swoją pracę dostajesz '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		$player->checkexp(array($strSkill => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    else
	      {
		$strResult = 'Albo trafił'.$strSuffix.' na jakieś beztalencia albo Ty nie potrafisz nauczać. Nic z tego nie wyszło, nawet nie otrzymał'.$strSuffix.' zapłaty za swoją pracę.';
	      }
	    break;
	    //Hunt for bandits
	  case 2:
	    if ($intRoll < 80)
	      {
		$intExp = $intPlevel;
		$strResult = 'Udaliście się we wskazane na mapie miejsce. Po jakimś czasie zauważyliście z oddali obozowisko bandytów. Tym razem udało się ich przechytrzyć. Zaskoczeni w ogóle nie stawiali oporu. Związanych przestępców dostarczyliście do lochów w mieście. W nagrodę otrzymał'.$strSuffix.' '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		$player->checkexp(array($strSkill => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 79 && $intRoll < 95)
	      {
		$smarty->assign("Message", 'Udało wam się odnaleźć tych, których szukaliście. Niestety, nie udało się ich zaskoczyć. Bandyci stanęli do walki!');
		$smarty -> display ('error1.tpl');
		battle();
	      }
	    else
	      {
		$strResult = 'Po dotarciu na miejsce, znaleźliście tylko resztki dawnego obozowiska przestępców. Niestety w okolicy brak jakichkolwiek śladów dokąd odeszli. Tym razem nawet nie otrzymał'.$strSuffix.' wynagrodzenia za swój stracony czas.';
	      }
	    break;
	    //City patrol
	  case 5:
	    if ($intRoll < 20)
	      {
		$intGold += $intPlevel * 10;
		$intExp = $intPlevel;
		$strResult = 'Ten dzień zaliczysz do udanych. W pewnym momencie, udało ci się przyuważyć złodziejaszka, który próbował okraść mieszkańca. Dzięki Twojej szybkiej reakcji, przestępca trafił do lochów. W nagrodę za swoją pracę dostajesz '.$intGold.' sztuk złota, '.$intExp.' punktów doświadczenia do umiejętności Spostrzegawczość.';
		$player->checkexp(array("perception" => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 19 && $intRoll < 80)
	      {
		$intExp = $intPlevel;
		$strResult = 'Nudny patrol ma się ku końcowi. Nic nie widział'.$strSuffix.', nic nie słyszał'.$strSuffix.'. Jednak mimo wszystko co nieco się nauczył'.$strSuffix.'. W nagrodę za swoją pracę dostajesz '.$intGold.' sztuk złota, '.$intExp.' punktów doświadczenia do umiejętności Spostrzegawczość.';
		$player->checkexp(array("perception" => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    else
	      {
		$strResult = 'Po prostu pech. Zauważyłeś złodzieja jak okrada mieszkańca ale ten, zdążył uciec przed Tobą. Na dodatek w trakcie pogoni podarł'.$strSuffix.' mundur. Tym razem nie otrzymasz zapłaty za swoją pracę.';
	      }
	    break;
	    //Hunt for monsters
	  case 6:
	    if ($intRoll < 90)
	      {
		$smarty->assign("Message", 'Udało wam się odnaleźć bestie, których szukaliście. Z okrzykiem bojowym na ustach, ruszasz wprost na najbliższą bestię');
		$smarty -> display ('error1.tpl');
		battle($_SESSION['mdata'][$intIndex]);
	      }
	    else
	      {
		$strResult = 'Po dotarciu na miejsce, znaleźliście tylko ślady bytności bestii. Niestety w okolicy brak jakichkolwiek wskazówek dokąd odeszły. Tym razem nawet nie otrzymał'.$strSuffix.' wynagrodzenia za swój stracony czas.';
	      }
	    break;
	    //Attack on bandits
	  case 7:
	    if ($intRoll < 50)
	      {
		$intGold += $intPlevel * 5;
		$intExp = 2 * $intPlevel;
		$strResult = 'Dotarłszy na miejsce, sprawnie dowodzisz swoim oddziałem. Posuwacie się ostrożnie przed siebie. Niezauważeni podchodzicie pod obóz bandytów. Na twój sygnał oddział rusza do akcji. Przestępcy kompletnie zaskoczeni, nie stawiają oporu. Związujecie ich wszystkich i wracacie do miasta. W nagrodę za doskonale przeprowadzoną akcję otrzymujesz '.$intGold.' sztuk złota, '.$intExp.' punktów doświadczenia do umiejętności Dowodzenie.';
		$player->checkexp(array('leadership' => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 49 && $intRoll < 95)
	      {
		$intExp = $intPlevel;
		$strResult = 'Mimo twoich najlepszych chęci, nie udało wam się podejść niezauważenie bandytów. Wywiązała się walka między nimi. Uważnie obserwujesz i wydajesz rozkazy swoim podwładnym. Na szczęście walka kończy się dobrze. Tych bandytów, którzy ocaleli, doprowadzacie do miasta. W nagrodę otrzymujesz '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		$player->checkexp(array('leadership' => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    else
	      {
		$strResult = 'Po dotarciu na miejsce, znaleźliście tylko resztki dawnego obozowiska przestępców. Niestety w okolicy brak jakichkolwiek śladów dokąd odeszli. Tym razem nawet nie otrzymał'.$strSuffix.' wynagrodzenia za swój stracony czas.';
	      }
	    break;
	    //Attack on monsters
	  case 8:
	    if ($intRoll < 50)
	      {
		$intGold += $intPlevel * 10;
		$intExp = $intPlevel;
		$strResult = 'Bez problemu odnajdujecie kryjówkę bestii. Te nie zauważają waszej obecności. W odpowienim momencie z okrzykiem bojowym na ustach, prowadzisz swoich żołnierzy do ataku. Walka jest krótka ale zacięta. Udaje wam się zabić wszystkie potwory. Po powrocie do miasta w nagrodę otrzymujesz '.$intGold.' sztuk złota, '.$intExp.' punktów doświadczenia do umiejętności Dowodzenie.';
		$player->checkexp(array('leadership' => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 49 && $intRoll < 95)
	      {
		$smarty->assign("Message", 'Docieracie na miejsce, jednak tym razem bestie nie dały się zaskoczyć. Na wasz atak odpowiedziały własnym atakiem. Przywódca stada rzuca się wprost na ciebie. Rozpoczyna się walka!');
		$smarty -> display ('error1.tpl');
		battle($_SESSION['mdata'][$intIndex]);
	      }
	    else
	      {
		$strResult = 'Po dotarciu na miejsce, znaleźliście tylko ślady bytności bestii. Niestety w okolicy brak jakichkolwiek wskazówek dokąd odeszły. Tym razem nawet nie otrzymał'.$strSuffix.' wynagrodzenia za swój stracony czas.';
	      }
	    break;
	    //Guard caravan
	  case 9:
	    if ($intRoll < 80)
	      {
		$intExp = $intPlevel;
		$strResult = 'Podróż minęła spokojnie, nie licząc kłótni, które od czasu do czasu wybuchały w karawanie. Zwiedził'.$strSuffix.' sobie nieco okolicę, jednak nic ciekawego się nie wydarzyło. Po powrocie otrzymał'.$strSuffix.' '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		$player->checkexp(array($strSkill => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 79 && $intRoll < 91)
	      {
		$intExp = $intPlevel;
		$intGold += $intPlevel * 5;
		$strResult = 'Rzeczywiście, ostatnio bandyci się rozplenili w okolicy. Tym razem jednak nie przewidzieli, że karawana posiada zwiększoną obronę. Bez problemu wyłapaliście ich. Po odeskortowaniu więźniów do miasta, otrzymał'.$strSuffix.' '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
		$player->checkexp(array($strSkill => $intExp), $player->id, "skills");
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	      }
	    elseif ($intRoll > 90 && $intRoll < 95)
	      {
		$smarty->assign("Message", 'Bandyci pojawili się nagle na drodze. Na moment zawachali się, widząc zwiększoną ochronę karawany, ale już po chwili ruszyli do ataku. Rozpoczyna się walka!');
		$smarty -> display ('error1.tpl');
		battle();
	      }
	    else
	      {
		$intDamage = ceil(($player->max_hp / 100) * rand(1, 25));
		$player->hp -= $intDamage;
		if ($player->hp < 0)
		  {
		    $player->hp = 0;
		  }
		$db->Execute("UPDATE `players` SET `hp`=".$player->hp." WHERE `id`=".$player->id);
		$strResult = 'Chwila nieuwagi i wpadliście w zasadkę bandytów. Odniosł'.$strSuffix.' '.$intDamage.' obrażeń. Niestety po powrocie, nie otrzymał'.$strSuffix.' zapłaty za zmarnowany czas.';
	      }
	    break;
	  default:
	    break;
	  }
	if (isset($strResult))
	  {
	    if ($objJob->fields['craftmission'] > 0)
	      {
		$strResult .= '<br /><br />(<a href="outpost.php?step=first">Wróć do listy zadań</a>)';
	      }
	    else
	      {
		$strResult .= '<br /><br />(<a href="city.php">Wróć do miasta</a>)';
	      }	      
	    $smarty->assign("Result", $strResult);
	    unset($_SESSION['craft'], $_SESSION['mdata']);
	  }
	else
	  {
	    $smarty->assign("Result", '');
	  }
	if (isset($_SESSION['result']))
	  {
	    $_GET['step'] = 'fight';
	  }
      }
    /**
     * Fight during task
     */
    if ($_GET['step'] == 'fight')
      {
	if (!isset($_SESSION['craft']))
	  {
	    error('Zapomnij o tym.');
	  }
	$objFight = $db->Execute("SELECT `fight` FROM `players` WHERE `id`=".$player->id);
	if (!$objFight->fields['fight'] && !isset($_SESSION['result']))
	  {
	    error('Zapomnij o tym.');
	  }
	if (!isset($_SESSION['result']))
	  {
	    $_SESSION['result'] = -1;
	  }
	if ($_SESSION['result'] == -1)
	  {
	    if ($objFight->fields['fight'] != 99999)
	      {
		battle($objFight->fields['fight']);
	      }
	    else
	      {
		battle();
	      }
	  }
	if ($_SESSION['result'] == 1)
	  {
	    $intPlevel = $player->stats['condition'][2] + $player->stats['speed'][2] + $player->stats['agility'][2] + $player->skills['dodge'][1] + $player->hp;
	    if ($player->equip[0][0] || $player->equip[11][0] || $player->equip[1][0])
	      {
		$intPlevel += $player->stats['strength'][2];
		if ($player->equip[0][0] || $player->equip[11][0])
		  {
		    $intPlevel += $player->skills['attack'][1];
		    $strSkill = 'attack';
		  }
		else
		  {
		    $intPlevel += $player->skills['shoot'][1];
		    $strSkill = 'shoot';
		  }
	      }
	    else
	      {
		$intPlevel += $player->stats['wisdom'][2] + $player->stats['inteli'][2] + $player->skills['magic'][1];
		$strSkill = 'magic';
	      }
	    $intExp = $intPlevel;
	    $intGold = $intPlevel * 5;
	    if ($player->gender == 'M')
	      {
		$strSuffix = 'eś';
	      }
	    else
	      {
		$strSuffix = 'aś';
	      }
	    $player->checkexp(array($strSkill => $intExp), $player->id, "skills");
	    $db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	    if (in_array($_SESSION['craft'][$_SESSION['index']], array(0, 2, 3, 4, 9)))
	      {
		$strResult = 'Po zakończonej walce, odeskortowaliście więźniów do miasta. Otrzymał'.$strSuffix.' '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
	      }
	    elseif (in_array($_SESSION['craft'][$_SESSION['index']], array(6, 8)))
	      {
		$strResult = 'Po zakończonej walce spaliliście zwłoki bestii i wróciliście do miasta. Otrzymał'.$strSuffix.' '.$intGold.' sztuk złota oraz '.$intExp.' punktów doświadczenia.';
	      }
	    else
	      {
		error('Zapomnij o tym.');
		unset($_SESSION['craft'], $_SESSION['mdata'], $_SESSION['result'], $_SESSION['index']);
	      }
	    if ($objJob->fields['craftmission'] > 0)
	      {
		$strResult .= '<br /><br />(<a href="outpost.php?step=first">Wróć do listy zadań</a>)';
	      }
	    else
	      {
		$strResult .= '<br /><br />(<a href="city.php">Wróć do miasta</a>)';
	      }
	  }
	if ($_SESSION['result'] == 2)
	  {
	    $strResult = 'Ponieważ nie udało Ci się wykonać zadania dlatego nie dostajesz jakiejkolwiek nagrody za nie. (<a href="city.php">Wróć do miasta</a>)';
	  }
	if (isset($strResult))
	  {
	    $smarty->assign("Result", $strResult);
	    unset($_SESSION['craft'], $_SESSION['mdata'], $_SESSION['result'], $_SESSION['index']);
	  }
	else
	  {
	    $smarty->assign("Result", '');
	  }
	$objFight->Close();
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