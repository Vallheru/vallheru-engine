<?php
/**
 *   File functions:
 *   Thieves den, items, monuments and missions for thieves
 *
 *   @name                 : thieves.php                            
 *   @copyright            : (C) 2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 08.01.2013
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

$title = "Złodziejska Spelunka";
require_once("includes/head.php");

if($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

if ($player->clas != 'Złodziej' && $player->rank != 'Admin')
  {
    error("Drogę zastępują tobie podejrzanie wyglądający osobnicy. <i>Dokąd to! Nic tutaj ciekawego nie znajdziesz, zwiewaj!</i><a href=");
  }

if ($player->gender == 'M')
  {
    $strSuffix = 'eś';
    $strSuffix2 = 'y';
  }
 else
   {
     $strSuffix = 'aś';
     $strSuffix2 = 'a';
   }

/**
 * Main menu
 */
if (!isset($_GET['step']))
  {
    $smarty->assign(array('Thiefinfo' => 'Wchodzisz do niewielkiego, drewnianego budynku. Już od drzwi uderza w Ciebie zapach potu, palonego fajkowego ziela oraz kiepskiej jakości alkoholu. Na moment wszystkie rozmowy cichną, kiedy bywalcy tego miejsca uważnie przyglądają się Tobie. Pokazujesz sekretny znak i po chwili wszystko wraca do normy. Podchodzisz do lady i mówisz do barmana:',
			  'Amonuments' => 'Potrzebuję nieco informacji o mieszkańcach '.$gamename.'.',
			  'Aitems' => 'Potrzebuję narzędzi.',
			  'Amissions' => 'Szukam jakiejś roboty'));
    $_GET['step'] = '';
  }
else
  {
    /**
     * Monuments
     */
    if ($_GET['step'] == 'monuments')
      {
	$arrMembers = $db->GetAll("SELECT `id`, `user`, `tribe`, `skills` FROM `players`");
	foreach ($arrMembers as &$arrMember)
	  {
	    $arrMember['user'] = $arrTags[$arrMember['tribe']][0].' '.$arrMember['user'].' '.$arrTags[$arrMember['tribe']][1];
	    $arrTmp = explode(';', $arrMember['skills']);
	    $arrValues = array();
	    foreach ($arrTmp as $strField)
	      {
		$arrTmp2 = explode(':', $strField);
		if ($arrTmp2[0] == '')
		  {
		    continue;
		  }
		$arrValues[$arrTmp2[0]] = explode(',', $arrTmp2[1]);
	      }
	    foreach ($arrValues as $strKey => $arrValues)
	      {
		$arrMember[$strKey] = $arrValues[1];
	      }
	  }
	
	function topplayers($strSkill)
	{
	  global $arrMembers;
	  
	  $arrSkills = array();
	  foreach ($arrMembers as $key => $value)
	    {
	      $arrSkills[$key] = $value[$strSkill];
	    }
	  array_multisort($arrSkills, SORT_DESC, $arrMembers);
	  if (count($arrMembers) < 10)
	    {
	      $intMax = count($arrMembers);
	    }
	  else
	    {
	      $intMax = 10;
	    }
	  $arrTop = array();
	  for ($i = 0; $i < $intMax; $i++)
	    {
	      $arrTop[] = array('user' => $arrMembers[$i]['user'],
				'id' => $arrMembers[$i]['id'],
				'value' => $arrMembers[$i][$strSkill]);
	    }
	  return $arrTop;
	}

	/**
	 * Function to get data from database and return array of top 10 players' names, ID's and skill values. 
	 */
	function topplayers2($strDbfield)
	{
	  global $db;
	  global $arrTags;
	  
	  switch ($strDbfield)
	    {
	    case 'mpoints':
	      $objTop = $db -> SelectLimit("SELECT `id`, `user`, `".$strDbfield."`, `tribe` FROM `players` WHERE `klasa`='Złodziej' ORDER BY `".$strDbfield."` DESC", 10);
	      break;
	    case 'credits':
	      $objTop = $db -> SelectLimit("SELECT `id`, `user`, `".$strDbfield."`, `tribe` FROM `players` WHERE `immu`='N' ORDER BY `".$strDbfield."` DESC", 10);
	      break;
	    default:
	      $objTop = $db -> SelectLimit('SELECT `id`, `user`, `'.$strDbfield.'`, `tribe` FROM `players` ORDER BY `'.$strDbfield.'` DESC', 10);
	      break;
	    }

	  $arrTop = array();
	  while (!$objTop -> EOF) 
	    {
	      $objTop->fields['user'] = $arrTags[$objTop->fields['tribe']][0].' '.$objTop->fields['user'].' '.$arrTags[$objTop->fields['tribe']][1];
	      $arrTop[] = array('user' => $objTop -> fields['user'],
				'id' => $objTop -> fields['id'],
				'value' => $objTop -> fields[$strDbfield]);
	      $objTop -> MoveNext();
	    }
	  $objTop -> Close();
	  return $arrTop;
	}

	$arrayMonumentTitles = array("Najwięcej złota (w sakiewce)", "Najwięcej złota (w banku)", "Najwyższa Spostrzegawczość", "Najwyższe Złodziejstwo", 'Wykonanych Zadań');

	$arrayMonumentDescriptions = array("Złoto (w sakiewce)", "Złoto (w banku)", "Spostrzegawczość", "Złodziejstwo", 'Zadań');
        
	$arrayMonuments = array(topplayers2('credits'), topplayers2('bank'), topplayers('perception'), topplayers('thievery'), topplayers2('mpoints'));
	$smarty->assign(array('Titles' => $arrayMonumentTitles,
			      'Descriptions' => $arrayMonumentDescriptions,
			      'Monuments' => $arrayMonuments,
			      'Mname' => "Imię (ID)",
			      "Minfo" => 'Oto najświeższe dane wywiadowcze zebrane przez członków naszego bractwa'));
      }
    /**
     * Thieves tools shop
     */
    elseif ($_GET['step'] == 'shop')
      {
	$smarty->assign(array('Sinfo' => 'Barman wskazuje Tobie głową drogę na zaplecze. Tam w niewielkim pokoiku siedzi pokryty bliznami gnom. Kiedy się zbliżasz do niego, podnosi na ciebie wzrok i pyta: <i>Chcesz kupić narzędzia do pracy? 200 sztuk złota jedno.</i>',
			      'Abuy' => 'Kupię',
			      'Tamount' => 'sztuk narzędzi.'));
	if (isset($_GET['buy']))
	  {
	    checkvalue($_POST['amount']);
	    $intGold = 200 * $_POST['amount'];
	    if ($player->credits < $intGold)
	      {
		message('error', 'Nie masz tylu sztuk złota przy sobie');
	      }
	    else
	      {
		$db->Execute("UPDATE `players` SET `credits`=`credits`-".$intGold." WHERE `id`=".$player->id);
		$objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='Wytrychy z miedzi' AND `power`=10 AND `cost`=20 AND `wt`=10 AND `maxwt`=10 AND `type`='E'");
		if (!$objTest->fields['id'])
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", 'Wytrychy z miedzi', 10, 'E', 20, 0, 10, 1, 10, ".$_POST['amount'].", 'N', 0, 0, 'N', 20)") or die($db->ErrorMsg());
		  }
		else
		  {
		    $db->Execute("UPDATE `equipment` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$objTest->fields['id']);
		  }
		$objTest->Close();
		message('success', 'Zakupił'.$strSuffix.' '.$_POST['amount'].' sztuk(i) wytrychów za '.$intGold.' sztuk złota.');
	      }
	  }
      }
    /**
     * Generate random missions
     */
    elseif ($_GET['step'] == 'missions')
      {
	/**
	 * Function return random key from array
	 */
	function getoption($arrOptions, $intPoints)
	{
	  $arrAvailable = array();
	  foreach ($arrOptions as $key => $value)
	    {
	      if ($key > $intPoints)
		{
		  break;
		}
	      $arrAvailable[$key] = $value;
	    }
	  return array_rand($arrAvailable);
	}
	
	$objJob = $db->Execute("SELECT `craftmission`, `mpoints` FROM `players` WHERE `id`=".$player->id);
	if ($objJob->fields['craftmission'] <= 0)
	  {
	    error('Niestety, na chwilę obecną nie ma dostępnych prac dla ciebie. Proszę wróć później.');
	  }
	if ($player->hp <= 0)
	  {
	    error('Nie możesz wykonywać zadań, ponieważ jesteś martwy.');
	  }
	if ($objJob->fields['mpoints'] < 30)
	  {
	    $strTalk = 'Czołem, now'.$strSuffix2.'. Słyszałem, że szukasz roboty.';
	  }
	elseif ($objJob->fields['mpoints'] < 60)
	  {
	    $strTalk = 'Witaj młod'.$strSuffix2.'. Ponoć szukasz roboty.';
	  }
	else
	  {
	    $strTalk = 'Witaj ponownie '.$player->user.'. Znowu chcesz coś dla nas zrobić?';
	  }
	$arrJobs = array();
	$i = 0;
	$_SESSION['mission'] = array();
	$_SESSION['mtype'] = array();
	while ($i < 3)
	  {
	    $intKey = rand(0, 3);
	    switch ($intKey)
	      {
		//Home robbery
	      case 0:
		if ($objJob->fields['mpoints'] < 10)
		  {
		    $intKey = -1;
		  }
		else
		  {
		    $arrLocations = array(10 => 'jedno takie mieszkanie w kamienicy. ', 
					  30 => 'jedną taką niewielką posiadłość. ', 
					  50 => 'jedną taką kamienicę. ', 
					  100 => 'jedną taką dużą posiadłość. ', 
					  150 => 'tutejszy bank. ');
		    $intOption = getoption($arrLocations, $objJob->fields['mpoints']);
		    $strJob = 'Robimy '.$arrLocations[$intOption].'Przyda się nam ktoś do pomocy. Jak dobrze pójdzie to dostaniesz część łupów';
		  }
		break;
		//Steal from people
	      case 1:
		$arrLocations = array(0 => 'Przejdziesz się po ulicy i poucinasz parę mieszków. ',
				      15 => 'Spróbjesz obrobić jednego kupca na mieście. ',
				      40 => 'Jeden zadufany szlachciura potrzebuje nieco nauki, ulżysz jego sakiewce. ',
				      80 => 'Pewien kupiec nie rozumie co się do niego mówi. Zajmiesz się jego sakiewką. ');
		$intOption = getoption($arrLocations, $objJob->fields['mpoints']);
		$strJob = 'Przyda się ktoś o zwinnych palcach. '.$arrLocations[$intOption].' Jak Ci się uda, to dostaniesz część łupu';
		break;
		//Tracking people
	      case 2:
		$arrLocations = array(0 => 'To mieszczanin, ale mamy oko na jego chatę. ',
				      20 => 'To kupiec, dobrze by było oskubać jego magazyn kiedy będzie zajęty. ',
				      60 => 'To bogaty szlachcic, ma bardzo ciekawą kolekcję obrazów w domu, dobrze by było wiedzieć kiedy jest nieobecny. ',
				      90 => 'To jeden z naszych. Ostatnio zaczął się dziwnie zachowywać, dobrze byłoby sprawdzić co porabia. ',
				      120 => 'To jakiś podejrzany typ. Nie pytaj się kto jest zainteresowany nim. ');
		$intOption = getoption($arrLocations, $objJob->fields['mpoints']);
		$strJob = 'Trzeba powęszyć za jedną osobą. '.$arrLocations[$intOption].'Jak dowiesz się czego trzeba i zostaniesz niezauważon'.$strSuffix2.' to dostaniesz trochę złota';
		break;
		//Guard position
	      case 3:
		if ($objJob->fields['mpoints'] < 5)
		  {
		    $intKey = -1;
		  }
		else
		  {
		    $arrLocations = array(5 => 'Przypilnujesz aby strażnicy za bardzo nie interesowali się jednym z naszych, kiedy będzie obrabiał sakiewki przechodniów. ',
					  10 => 'Będziesz mieć oko na okolicę, kiedy będziemy obrabiać pewne mieszkanie w kamienicy. ',
					  15 => 'Upewnisz się że okolica jest bezpieczna, kiedy my zajmiemy się pewną posiadłością. ',
					  20 => 'Trzeba się upewnić że nikt niepowołany nie będzie się kręcił w okolicy naszego kasyna. ',
					  25 => 'Mamy nieco towaru do przerzucenia przez mury i nie chcemy aby ktoś nam przeszkadzał. ',
					  30 => 'Przepatrzysz okolicę pewnej interesującej nas kamienicy. ',
					  35 => 'Często próbują wejść do naszej knajpy różne takie typy. Zajmiesz się nimi. ',
					  40 => 'Będziesz mieć oko na okolicę, kiedy my zajmiemy się pewną posiadłością. ',
					  50 => 'Robimy dzisiaj bank i nie chcemy aby ktoś nam przeszkadzał. ');
		    $intOption = getoption($arrLocations, $objJob->fields['mpoints']);
		    $strJob = 'Potrzebujemy kogoś, kto stanie na czatach w czasie roboty. '.$arrLocations[$intOption].'Jeżeli nikt nam nie przeszkodzi w robocie, to dostaniesz trochę złota';
		  }
		break;
	      default:
		break;
	      }
	    if ($intKey > -1)
	      {
		$arrLoots = array(0 => '.',
				  80 => ' oraz nowe wytrychy.',
				  85 => ' oraz lepsze wytrychy.',
				  90 => ' oraz plan wytrychów.',
				  97 => ' oraz plan lepszych wytrychów.');
		$intLoot = getoption($arrLoots, rand(1, 100));
		$arrJobs[$i] = $strJob.$arrLoots[$intLoot];
		$_SESSION['mission'][$i] = $intKey;
		$_SESSION['mtype'][$i] = $intOption;
		$_SESSION['reward'][$i] = $intLoot;
		$i ++;
	      }
	  }
	$objJob->fields['craftmission']--;
	if ($objJob->fields['craftmission'] > 0)
	  {
	    $strRefresh = 'Odśwież listę (możesz odświeżyć jeszcze '.$objJob->fields['craftmission'].' razy).';
	  }
	else
	  {
	    $strRefresh = '';
	  }
	$smarty->assign(array('Minfo' => 'Ruchem głowy, barman pokazuje tobie schody na górę. Udajesz się we wskazanym kierunku. Dochodzisz do dość ciemnego pokoju na górze. Na jego środku stoi niewielki stolik przy którym siedzi jakiś człowiek, ruchem dłoni wskazuje tobie miejsce przy stoliku. Bardziej wyczuwasz niż widzisz, że w pomieszczeniu znajdują się jeszcze inne osoby. Kiedy zajmujesz swoje miejsce siedzący mężczyzna odzywa się do ciebie.<i>'.$strTalk.' tak się składa, że chyba mamy parę zadań dla ciebie. Zainteresowan'.$strSuffix2.'?</i>',
			      'Jobinfo2' => $strRefresh,
			      "Jobs" => $arrJobs,
			      "Ayes" => "Biorę tę robotę. (koszt: 5 energii)",));
	$objJob->Close();
	$db->Execute("UPDATE `players` SET `craftmission`=`craftmission`-1 WHERE `id`=".$player->id);
      }
    /**
     * Start random mission
     */
    elseif ($_GET['step'] == 'confirm')
      {
	if (!isset($_GET['number']) || !isset($_SESSION['mission']) || !isset($_SESSION['mtype']))
	  {
	    error('Zapomnij o tym!');
	  }
	if ($player->energy < 5)
	  {
	    error('Nie masz tyle energii.');
	  }
	$_GET['number'] = intval($_GET['number']);
	if ($_GET['number'] < 0 || $_GET['number'] > 3)
	  {
	    error('Zapomnij o tym!');
	  }
	$strTarget = 'N';
	switch ($_SESSION['mission'][$_GET['number']])
	  {
	    //Home robbery
	  case 0:
	    switch ($_SESSION['mtype'][$_GET['number']])
	      {
	      case 10:
		$intRooms = rand(4, 15);
		break;
	      case 30:
		$intRooms = rand(5, 15);
		break;
	      case 50:
		$intRooms = rand(5, 20);
		break;
	      case 100:
		$intRooms = rand(6, 25);
		break;
	      case 150:
		$intRooms = rand(8, 40);
		$strTarget = 'Y';
		break;
	      default:
		break;
	      }
	    break;
	    //Steal from people
	  case 1:
	    switch ($_SESSION['mtype'][$_GET['number']])
	      {
	      case 0:
		$intRooms = rand(3, 10);
		break;
	      case 15:
		$intRooms = rand(4, 15);
		$strTarget = 'Y';
		break;
	      case 40:
		$intRooms = rand(5, 15);
		$strTarget = 'Y';
		break;
	      case 80:
		$intRooms = rand(6, 25);
		$strTarget = 'Y';
		break;
	      default:
		break;
	      }
	    break;
	    //Tracking people
	  case 2:
	    switch ($_SESSION['mtype'][$_GET['number']])
	      {
	      case 0:
		$intRooms = rand(5, 15);
		break;
	      case 20:
		$intRooms = rand(6, 20);
		break;
	      case 60:
		$intRooms = rand(8, 25);
		break;
	      case 90:
		$intRooms = rand(10, 35);
		$strTarget = 'Y';
		break;
	      case 120:
		$intRooms = rand(12, 50);
		$strTarget = 'Y';
		break;
	      default:
		break;
	      }
	    break;
	    //Guard position
	  case 3:
	    switch ($_SESSION['mtype'][$_GET['number']])
	      {
	      case 5:
		$intRooms = rand(3, 8);
		break;
	      case 10:
		$intRooms = rand(4, 10);
		break;
	      case 15:
		$intRooms = rand(5, 12);
		break;
	      case 20:
		$intRooms = rand(4, 10);
		break;
	      case 25:
		$intRooms = rand(4, 10);
		break;
	      case 30:
		$intRooms = rand(6, 14);
		break;
	      case 35:
		$intRooms = rand(4, 10);
		break;
	      case 40:
		$intRooms = rand(8, 18);
		break;
	      case 50:
		$intRooms = rand(10, 20);
		break;
	      }
	    break;
	  default:
	    break;
	  }
	switch ($_SESSION['reward'][$_GET['number']])
	  {
	    //Nothing
	  case 0:
	    $strLoot = '';
	    break;
	    //Ordinary lockpick
	  case 80:
	    $strLoot = 'tools;=1;T';
	    break;
	    //Better lockpick
	  case 85:
	    $strLoot = 'tools;>1;T';
	    break;
	    //Ordinary lockpick plan
	  case 90:
	    $strLoot = 'plans;=1;T';
	    break;
	    //Better lockpic plan
	  case 97:
	    $strLoot = 'plans;>1;T';
	    break;
	  default:
	    break;
	  }
	$objMission = $db->Execute("SELECT * FROM `mactions` WHERE `pid`=".$player->id);
	//Generate mission
	if (!isset($_SESSION['maction']) && !$objMission->fields['pid'])
	  {
	    $objStart = $db->Execute("SELECT * FROM `missions` WHERE `name`='thief".$_SESSION['mission'][$_GET['number']].$_SESSION['mtype'][$_GET['number']]."start' ORDER BY RAND() LIMIT 1");
	    $strText = $objStart->fields['text'];
	    $_SESSION['maction'] = array('location' => $objStart->fields['id'],
					 'exits' => array(),
					 'mobs' => array(),
					 'items' => array(),
					 'type' => 'T',
					 'loot' => $strLoot,
					 'rooms' => $intRooms,
					 'successes' => 0,
					 'bonus' => $_SESSION['mtype'][$_GET['number']],
					 'target' => $strTarget,
					 'place' => $player->location,
					 'moreinfo' => array());
	    $arrOptions = array();
	    //Generate exits
	    $arrTmp = explode(';', $objStart->fields['exits']);
	    $arrChances = explode(';', $objStart->fields['chances']);
	    while (count($arrOptions) == 0)
	      {
		for ($i = 0; $i < count($arrChances); $i++)
		  {
		    $intRoll = rand(0, 100);
		    if ($intRoll < $arrChances[$i])
		      {
			$_SESSION['maction']['exits'][] = $arrTmp[$i];
			$arrTmp2 = explode(',', $arrTmp[$i]);
			$arrOptions[$arrTmp2[1]] = $arrTmp2[0];
		      }
		  }
	      }
	    //Generate items
	    $arrTmp = explode(';', $objStart->fields['items']);
	    $arrChances = explode(';', $objStart->fields['chances3']);
	    for ($i = 0; $i < count($arrChances); $i ++)
	      {
		$intRoll = rand(0, 100);
		if ($intRoll < $arrChances[$i])
		  {
		    $_SESSION['maction']['items'][] = $arrTmp[$i];
		    $arrTmp2 = explode(',', $arrTmp[$i]);
		    $strText .= ' '.$arrTmp2[2];
		    for ($j = 3; $j < count($arrTmp2); $j += 2)
		      {
			$arrOptions[$arrTmp2[($j + 1)]] = $arrTmp2[$j];
		      }
		  }
	      }
	    //Generate mobs
	    $arrTmp = explode(';', $objStart->fields['mobs']);
	    $arrChances = explode(';', $objStart->fields['chances2']);
	    for ($i = 0; $i < count($arrChances); $i ++)
	      {
		$intRoll = rand(0, 100);
		if ($intRoll < $arrChances[$i])
		  {
		    $_SESSION['maction']['mobs'][] = $arrTmp[$i];
		    $arrTmp2 = explode(',', $arrTmp[$i]);
		    $strText .= ' '.$arrTmp2[2];
		    for ($j = 3; $j < count($arrTmp2); $j += 2)
		      {
			$arrOptions[$arrTmp2[($j + 1)]] = $arrTmp2[$j];
		      }
		  }
	      }
	    $db->Execute("INSERT INTO `mactions` (`pid`, `location`, `exits`, `mobs`, `items`, `type`, `loot`, `rooms`, `bonus`, `place`, `target`) VALUES(".$player->id.", ".$_SESSION['maction']['location'].", '".implode(';', $_SESSION['maction']['exits'])."', '".implode(';', $_SESSION['maction']['mobs'])."', '".implode(';', $_SESSION['maction']['items'])."', 'T', '".$strLoot."', ".$intRooms.", ".$_SESSION['maction']['bonus'].", '".$_SESSION['maction']['place']."', '".$_SESSION['maction']['target']."')");
	  }
	else
	  {
	    if (!isset($_SESSION['maction']))
	      {
		$_SESSION['maction'] = array('location' => $objMission->fields['location'],
					     'exits' => explode(';', $objMission->fields['exits']),
					     'mobs' => explode(';', $objMission->fields['mobs']),
					     'items' => explode(';', $objMission->fields['items']),
					     'type' => $objMission->fields['type'],
					     'loot' => $objMission->fields['loot'],
					     'rooms' => $objMission->fields['rooms'],
					     'id' => $objMission->fields['id'],
					     'moreinfo' => array());
	      }
	    $objStart = $db->Execute("SELECT `text` FROM `missions` WHERE `id`='".$_SESSION['maction']['location']."'");
	    $strText = $objStart->fields['text'];
	    $objStart->Close();
	    $arrOptions = array();
	    //Read exits
	    foreach ($_SESSION['maction']['exits'] as $strExit)
	      {
		$arrTmp2 = explode(',', $strExit);
		$arrOptions[$arrTmp2[1]] = $arrTmp2[0];
	      }
	    //Read items
	    foreach ($_SESSION['maction']['items'] as $strItem)
	      {
		$arrTmp2 = explode(',', $strItem);
		$strText .= ' '.$arrTmp2[2];
		for ($j = 3; $j < count($arrTmp2); $j += 2)
		  {
		    $arrOptions[$arrTmp2[($j + 1)]] = $arrTmp2[$j];
		  }
	      }
	    //Read mobs
	    foreach ($_SESSION['maction']['mobs'] as $strMob)
	      {
		$arrTmp2 = explode(',', $strMob);
		$strText .= ' '.$arrTmp2[2];
		for ($j = 3; $j < count($arrTmp2); $j += 2)
		  {
		    $arrOptions[$arrTmp2[($j + 1)]] = $arrTmp2[$j];
		  }
	      }
	  }
	$db->Execute("UPDATE `players` SET `miejsce`='Przygoda', `energy`=`energy`-5 WHERE `id`=".$player->id);
	$smarty->assign(array("Text" => $strText,
			      'Moptions' => $arrOptions,
			      'Anext' => 'Dalej'));
	unset($_SESSION['mission'], $_SESSION['mtype']);
      }
  }

/**
 * Assign variables to template and display page
 */
$smarty->assign(array('Step' => $_GET['step'],
		      'Aback' => 'Wróć'));
$smarty->display('thieves.tpl');

require_once("includes/foot.php");
?>
