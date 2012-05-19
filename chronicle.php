<?php
/**
 *   File functions:
 *   Game chronicle - quests and history of game
 *
 *   @name                 : chronicle.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 19.05.2012
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

$title = "Kronika";
require_once("includes/head.php");

if($player->location != 'Altara' && $player->location != 'Ardulith') 
  {
    error("Nie znajdujesz się w mieście. <a href=");
  }

//Main menu
if (!isset($_GET['step']))
  {
    $_GET['step'] = '';
    $objMissions = $db->Execute("SELECT `id`, `shortdesc`, `type`, `chapter` FROM `missions2` WHERE `location`='".$player->location."'");
    $objChapter = $db->Execute("SELECT `chapter` FROM `players` WHERE `id`=".$player->id);
    $arrStory = array();
    $arrOldstory = array();
    $arrOther = array();
    if ($objMissions->fields['id'])
      {
	while (!$objMissions->EOF)
	  {
	    if ($objMissions->fields['type'] == 'Q' && $objMissions->fields['chapter'] > $objChapter->fields['chapter'])
	      {
		continue;
	      }
	    switch ($objMissions->fields['type'])
	      {
	      case 'Q':
		$arrStory[$objMissions->fields['id']] = $objMissions->fields['shortdesc'];
		break;
	      case 'O':
		$arrOldstory[$objMissions->fields['id']] = $objMissions->fields['shortdesc'];
		break;
	      default:
		$arrOther[$objMissions->fields['id']] = $objMissions->fields['shortdesc'];
		break;
	      }
	    $objMissions->MoveNext();
	  }
      }
    $objChapter->Close();
    $objMissions->Close();
    $intStory = count($arrStory);
    $intOldstory = count($arrOldstory);
    $intOther = count($arrOther);
    if ($intStory > 0)
      {
	$arrStory[0] = 'Obecne wydarzenia:';
      }
    if ($intOldstory > 0)
      {
	$arrOldstory[0] = 'Dawne historie:';
      }
    if ($intOther > 0)
      {
	$arrOther[0] = 'Poboczne wydarzenia:';
      }
    $smarty->assign(array("Info" => "Wchodzisz do niewielkiego budynku. Kiedy tylko mijasz wejście, gwar dobiegający z ulicy niemal natychmiast milknie. Wnętrze budynku jest niemal puste, tylko na jego środku stoi średnich rozmiarów, kamienny pedestał a na nim znajduje się olbrzymia księga. Kiedy podchodzisz bliżej, księga zaczyna lekko lśnić białym blaskiem. Bez twojej pomocy otwiera się na spisie treści.",
			  "Stories" => $arrStory,
			  "Oldstories" => $arrOldstory,
			  "Others" => $arrOther,
			  "Istory" => $intStory,
			  "Ioldstory" => $intOldstory,
			  "Iother" => $intOther));
  }
else
  {
    //Start mission
    if ($_GET['step'] == 'go')
      {
	$objMaction = $db->Execute("SELECT * FROM `mactions` WHERE `pid`=".$player->id);
	//Generate mission
	if (!isset($_SESSION['maction']) && !$objMaction->fields['pid'])
	  {
	    checkvalue($_POST['qid']);
	    $objMission = $db->Execute("SELECT `id`, `name`, `location`, `type`, `chapter` FROM `missions2` WHERE `id`=".$_POST['qid']);
	    if (!$objMission->fields['id'])
	      {
		error('Nie ma takiej przygody.');
	      }
	    if ($objMission->fields['location'] != $player->location)
	      {
		error('Ta przygoda rozpoczyna się w innym mieście.');
	      }
	    $objChapter = $db->Execute("SELECT `chapter` FROM `players` WHERE `id`=".$player->id);
	    if ($objMission->fields['type'] == 'Q' && $objMission->fields['chapter'] > $objChapter->fields['chapter'])
	      {
		error('Nie masz dostępu do tej przygody.');
	      }
	    if ($player->energy < 2)
	      {
		error('Nie masz wystarczającej ilości energii aby iść na tą przygodę.');
	      }
	    if ($player->hp <= 0)
	      {
		error('Nie możesz uczestniczyć w przygodzie, ponieważ jesteś martwy.');
	      }
	    $objChapter->Close();
	    $objJob = $db->Execute("SELECT `craftmission` FROM `players` WHERE `id`=".$player->id);
	    if ($objJob->fields['craftmission'] <= 0)
	      {
		error('Nie możesz już dziś wyruszyć na przygodę.');
	      }
	    $objJob->Close();
	    $objStart = $db->Execute("SELECT * FROM `missions` WHERE `name`='".$objMission->fields['name']."start' ORDER BY RAND() LIMIT 1");
	    $strText = $objStart->fields['text'];
	    $intRooms = rand(25, 50);
	    $_SESSION['maction'] = array('location' => $objStart->fields['id'],
					 'exits' => array(),
					 'mobs' => array(),
					 'items' => array(),
					 'type' => $objMission->fields['type'],
					 'loot' => '',
					 'rooms' => $intRooms,
					 'successes' => 0,
					 'bonus' => 10,
					 'target' => 'Y',
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
	    $db->Execute("INSERT INTO `mactions` (`pid`, `location`, `exits`, `mobs`, `items`, `type`, `loot`, `rooms`, `bonus`, `place`, `target`) VALUES(".$player->id.", ".$_SESSION['maction']['location'].", '".implode(';', $_SESSION['maction']['exits'])."', '".implode(';', $_SESSION['maction']['mobs'])."', '".implode(';', $_SESSION['maction']['items'])."', '".$objMission->fields['type']."', '', ".$intRooms.", ".$_SESSION['maction']['bonus'].", '".$_SESSION['maction']['place']."', '".$_SESSION['maction']['target']."')");
	    $db->Execute("UPDATE `players` SET `miejsce`='Przygoda', `energy`=`energy`-2, `craftmission`=`craftmission`-1 WHERE `id`=".$player->id);
	  }
	else
	  {
	    if (!isset($_SESSION['maction']))
	      {
		$_SESSION['maction'] = array('location' => $objMaction->fields['location'],
					     'exits' => explode(';', $objMaction->fields['exits']),
					     'mobs' => explode(';', $objMaction->fields['mobs']),
					     'items' => explode(';', $objMaction->fields['items']),
					     'type' => $objMaction->fields['type'],
					     'loot' => $objMaction->fields['loot'],
					     'rooms' => $objMaction->fields['rooms'],
					     'id' => $objMaction->fields['id'],
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
	$smarty->assign(array("Text" => $strText,
			      'Moptions' => $arrOptions,
			      'Anext' => 'Dalej'));
	$objMaction->Close();
      }
    //Show mission info
    else
      {
	checkvalue($_GET['step']);
	$objMission = $db->Execute("SELECT `id`, `intro`, `shortdesc`, `type`, `location`, `chapter` FROM `missions2` WHERE `id`=".$_GET['step']);
	if (!$objMission->fields['id'])
	  {
	    error('Nie ma takiej przygody.');
	  }
	if ($objMission->fields['location'] != $player->location)
	  {
	    error('Ta przygoda rozpoczyna się w innym mieście.');
	  }
	$objChapter = $db->Execute("SELECT `chapter` FROM `players` WHERE `id`=".$player->id);
	$blnGo = TRUE;
	if ($objMission->fields['type'] == 'Q')
	  {
	    if ($objMission->fields['chapter'] > $objChapter->fields['chapter'])
	      {
		error('Nie masz dostępu do tej przygody.');
	      }
	    if ($objMission->fields['chapter'] == $objChapter->fields['chapter'])
	      {
		$blnGo = FALSE;
	      }
	  }
	$objChapter->Close();
	$smarty->assign(array("Mname" => $objMission->fields['shortdesc'],
			      "Intro" => $objMission->fields['intro'],
			      "Mid" => $objMission->fields['id'],
			      "Aback" => "Wróć",
			      "Aread" => "Zbadaj wydarzenie (koszt: 2 punkty energii)",
			      "Mgo" => $blnGo));
	$objMission->Close();
      }
  }

/**
* Assign variables to template and display page
*/
$smarty -> assign("Step", $_GET['step']);
$smarty -> display ('chronicle.tpl');

require_once("includes/foot.php");
?>
