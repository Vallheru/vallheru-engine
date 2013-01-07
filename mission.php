<?php
/**
 *   File functions:
 *   Random missions
 *
 *   @name                 : mission.php                            
 *   @copyright            : (C) 2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 07.01.2013
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

$title = "Przygoda";
require_once("includes/head.php");
require_once('includes/funkcje.php');
require_once('includes/turnfight.php');

/**
 * Set session variables
 */
if (!isset($_SESSION['maction']))
  {
    $objMission = $db->Execute("SELECT * FROM `mactions` WHERE `pid`=".$player->id);
    if (!$objMission->fields['pid'])
      {
	error('Nie znajdujesz się w przygodzie.');
      }
    $_SESSION['maction'] = array('location' => $objMission->fields['location'],
				 'exits' => array(),
				 'mobs' => array(),
				 'items' => array(),
				 'type' => $objMission->fields['type'],
				 'loot' => $objMission->fields['loot'],
				 'rooms' => $objMission->fields['rooms'],
				 'successes' => $objMission->fields['successes'],
				 'bonus' => $objMission->fields['bonus'],
				 'place' => $objMission->fields['place'],
				 'target' => $objMission->fields['target'],
				 'moreinfo' => array());
    if ($objMission->fields['exits'] != '')
      {
	$_SESSION['maction']['exits'] = explode(';', $objMission->fields['exits']);
      }
    if ($objMission->fields['mobs'] != '')
      {
	$_SESSION['maction']['mobs'] = explode(';', $objMission->fields['mobs']);
      }
    if ($objMission->fields['items'] != '')
      {
	$_SESSION['maction']['items'] = explode(';', $objMission->fields['items']);
      }
    if ($objMission->fields['moreinfo'] != '')
      {
	$_SESSION['maction']['moreinfo'] = explode(';', $objMission->fields['moreinfo']);
      }
    $objMission->Close();
  }
$strFinish = '';
$blnEnd = FALSE;

if ($player->hp <= 0)
  {
    if ($player->location != $_SESSION['maction']['place'])
      {
	$db -> Execute("UPDATE `players` SET `miejsce`='".$_SESSION['maction']['place']."' WHERE `id`=".$player->id);
      }
    unset($_SESSION['maction']);
    error('Nie możesz uczestniczyć w przygodzie, ponieważ jesteś martwy.');
  }

function battle()
{
  global $player;
  global $db;
  global $lang;
  global $enemy;
  global $blnEnd;
  global $strFinish;

  //Generate new monster
    if (!isset($_SESSION['enemy']))
    {
      require_once('includes/monsters.php');
      $_SESSION['enemy'] = randommonster($_SESSION['maction']['moreinfo'][1]);
      $enemy = $_SESSION['enemy'];
    }
    else
      {
	$enemy = $_SESSION['enemy'];
      }
    if (!isset($_SESSION['razy']))
      {
	if (count($_SESSION['maction']['moreinfo']) > 2)
	  {
	    $_SESSION['razy'] = $_SESSION['maction']['moreinfo'][2];
	  }
	else
	  {
	    $_SESSION['razy'] = 1;
	  }
      }
    if ($player->fight != 8888)
      {
	$db -> Execute("UPDATE `players` SET `fight`=8888 WHERE `id`=".$player->id);
      }
    if (!isset ($_POST['action'])) 
      {
	turnfight ($enemy['exp1'], $enemy['gold'], '', 'mission.php');
      } 
    else 
      {
	turnfight ($enemy['exp1'], $enemy['gold'], $_POST['action'], 'mission.php');
      }
    $myhp = $db -> Execute("SELECT `hp`, `fight` FROM `players` WHERE `id`=".$player -> id);
    //End of fight
    if ($myhp -> fields['fight'] == 0 || $myhp->fields['fight'] == -1) 
      {
	unset($_SESSION['enemy']);
	$player->energy--;
	if ($player->energy < 0)
	  {
	    $player->energy = 0;
	  }
	if ($myhp->fields['fight'] == -1)
	  {
	    $db -> Execute("UPDATE `players` SET `energy`=".$player->energy.", `miejsce`='".$_SESSION['maction']['place']."', `fight`=0 WHERE `id`=".$player -> id);
	    $strFinish = '(<a href="city.php">Koniec</a>)';
	    $blnEnd = TRUE;
	    $strName = $_SESSION['maction']['moreinfo'][4];
	    $player->fight = -1;
	  }
	else
	  {
	    $db -> Execute("UPDATE `players` SET `energy`=".$player->energy." WHERE `id`=".$player -> id);
	    $strName = $_SESSION['maction']['moreinfo'][3];
	    $player->fight = 0;
	  }
	$objFinish = $db->Execute("SELECT `id` FROM `missions` WHERE `name`='".$strName."' ORDER BY RAND() LIMIT 1");
	$_SESSION['maction']['location'] = $objFinish->fields['id'];
	$objFinish->Close();
	$_POST['action'] = $strName;
      }
    $myhp -> Close();
}

/**
 * Fight with monsters
 */
if ($player->fight == 8888)
  {
    battle();
    $myhp = $db -> Execute("SELECT `fight` FROM `players` WHERE `id`=".$player -> id);
    if ($myhp->fields['fight'] > 0)
      {
	require_once("includes/foot.php");
	return;
      }
    $myhp->Close();
  }

/**
 * Generate new room
 */
$blnQuest = FALSE;
if (isset($_POST['action']))
  {
    //Read exits
    $arrEactions = array();
    foreach ($_SESSION['maction']['exits'] as $strExit)
      {
	$arrTmp2 = explode(',', $strExit);
	$arrEactions[] = $arrTmp2[1];
      }
    //Read items
    $arrIactions = array();
    foreach ($_SESSION['maction']['items'] as $strItem)
      {
	$arrTmp2 = explode(',', $strItem);
	for ($j = 4; $j < count($arrTmp2); $j += 2)
	  {
	    $arrIactions[] = $arrTmp2[$j];
	  }
      }
    //Read mobs
    $arrMactions = array();
    foreach ($_SESSION['maction']['mobs'] as $strMob)
      {
	$arrTmp2 = explode(',', $strMob);
	for ($j = 4; $j < count($arrTmp2); $j += 2)
	  {
	    $arrMactions[] = $arrTmp2[$j];
	  }
      }
    if (count($_SESSION['maction']['moreinfo']) > 0)
      {
	if ($_SESSION['maction']['moreinfo'][0] == 'combat')
	  {
	    $arrEactions[] = $_SESSION['maction']['moreinfo'][3];
	    $arrEactions[] = $_SESSION['maction']['moreinfo'][4];
	    $_SESSION['maction']['moreinfo'] = array();
	  }
      }
    if (!in_array($_POST['action'], $arrEactions) && !in_array($_POST['action'], $arrIactions) && !in_array($_POST['action'], $arrMactions))
      {
	error('Zapomnij o tym!');
      }
    $objName = $db->Execute("SELECT `name` FROM `missions` WHERE `id`=".$_SESSION['maction']['location']);
    //Player choice action with mob or item
    if (in_array($_POST['action'], $arrMactions) || in_array($_POST['action'], $arrIactions))
      {
	$intDiff = 10;
	$blnTarget = FALSE;
	foreach ($_SESSION['maction']['mobs'] as $strMob)
	  {
	    $intDiff += 5;
	    $arrTmp2 = explode(',', $strMob);
	    if (in_array('A', $arrTmp2))
	      {
		$intDiff += 10;
		if (in_array($_POST['action'], $arrTmp2))
		  {
		    $intDiff += 20;
		  }
	      }
	    elseif (in_array('T', $arrTmp2) && in_array($_POST['action'], $arrTmp2))
	      {
		$blnTarget = TRUE;
	      }
	    elseif (in_array('Q', $arrTmp2) && in_array($_POST['action'], $arrTmp2))
	      {
		$blnQuest = TRUE;
	      }
	  }
	$intDiff -= $player->skills['thievery'][1];
	if ($intDiff < 5)
	  {
	    $intDiff = 5;
	  }
	if ($intDiff > 95)
	  {
	    $intDiff = 95;
	  }
	$intRoll = rand(1, 100);
	if ($intRoll <= $intDiff)
	  {
	    preg_match('/^[a-zA-Z]+[0-9]+/', $objName->fields['name'], $arrResults);
	    $objName->Close();
	    $objFinish = $db->Execute("SELECT `id` FROM `missions` WHERE `name`='".$arrResults[0]."fail' ORDER BY RAND() LIMIT 1");
	    $_SESSION['maction']['location'] = $objFinish->fields['id'];
	    $objFinish->Close();
	    $blnEnd = TRUE;
	    if ($_SESSION['maction']['type'] != 'T')
	      {
		$db -> Execute("UPDATE `players` SET `miejsce`='".$_SESSION['maction']['place']."' WHERE `id`=".$player -> id);
		$strFinish = '(<a href="city.php">Koniec</a>)';
	      }
	  }
	//Thieves missions
	if ($_SESSION['maction']['type'] == 'T')
	  {
	    //Mission fail
	    if ($blnEnd)
	      {
		$strFinish = '(<a href="jail.php">Koniec</a>)';
		$cost = 1000 * $player->skills['thievery'][1];
		$player->checkexp(array('thievery' => $player->skills['thievery'][1]), $player->id, "skills");
		$db -> Execute("UPDATE `players` SET `miejsce`='Lochy' WHERE `id`=".$player -> id);
		$strDate = $db -> DBDate($newdate);
		$db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player->id.",'Nieudane zadanie', 7, ".$cost.", '".$data."')");                
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'Zostałeś wtrącony do więzienia na 1 dzień. Możesz wyjść za kaucją: ".$cost.".', ".$strDate.", 'T')");
	      }
	    else
	      {
		$player->checkexp(array('thievery' => $player->skills['thievery'][1]), $player->id, "skills");
	      }
	  }
	if (!$blnEnd)
	  {
	    if ($blnTarget || $blnQuest)
	      {
		$_SESSION['maction']['successes']++;
		if ($_SESSION['maction']['successes'] == 10)
		  {
		    $_SESSION['maction']['rooms'] = 1;
		  }
		if ($blnQuest)
		  {
		    $_SESSION['maction']['rooms'] = 1;
		  }
	      }
	    //Iteraction with item
	    elseif (in_array($_POST['action'], $arrIactions))
	      {
		$intItem = array_search($_POST['action'], $arrIactions);
		$arrItem = explode(',', $_SESSION['maction']['items'][$intItem]);
		switch ($arrItem[1])
		  {
		    //Quest target = finish mission
		  case 'Q':
		    $_SESSION['maction']['rooms'] = 1;
		    $blnQuest = TRUE;
		    break;
		    //Target item
		  case 'T':
		    $_SESSION['maction']['successes']++;
		    if ($_SESSION['maction']['successes'] == 10)
		      {
			$_SESSION['maction']['rooms'] = 1;
		      }
		    break;
		    //Other item
		  case 'O':
		    $intCost = $player->stats['condition'][2] * 100;
		    $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$arrItem[0]."' AND `type`='Q' AND `cost`=".$intCost);
		    if (!$objTest->fields['id'])
		      {
			$db->Execute("INSERT INTO `equipment` (`owner`, `name`, `type`, `cost`) VALUES (".$player->id.", '".$arrItem[0]."', 'Q', ".$intCost.")") or die($db->ErrorMsg());
		      }
		    else
		      {
			$db->Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest->fields['id']);
		      }
		    $objTest->Close();
		    break;
		    //Item from equipment
		  case 'E':
		    //Search for weapons/armors
		    $objItem = $db->Execute("SELECT * FROM `equipment` WHERE `owner`=0 AND `name`='".$arrItem[0]."'");
		    //Search for bows/arrows
		    if (!$objItem->fields['id'])
		      {
			$objItem = $db->Execute("SELECT * FROM `bows` WHERE `name`='".$arrItem[0]."'");
			if ($objItem->fields['type'] == 'B')
			  {
			    $objItem->fields['twohand'] = 'Y';
			  }
			else
			  {
			    $objItem->fields['twohand'] = 'N';
			  }
		      }
		    $objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$arrItem[0]."' AND `wt`=".$objItem->fields['maxwt']." AND `type`='".$objItem->fields['type']."' AND `status`='U' AND `owner`=".$player->id." AND `power`=".$objItem->fields['power']." AND `zr`=".$objItem->fields['zr']." AND `szyb`=".$objItem->fields['szyb']." AND `maxwt`=".$objItem->fields['maxwt']." AND `poison`=0 AND `cost`=1 AND `twohand`='".$objItem->fields['twohand']."' AND `repair`=".$objItem->fields['repair']) or die($db->ErrorMsg());
		    if (!$objTest->fields['id']) 
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`, `status`) VALUES(".$player->id.", '".$arrItem[0]."', ".$objItem->fields['power'].", '".$objItem->fields['type']."', 1, ".$objItem->fields['zr'].", ".$objItem->fields['maxwt'].", ".$objItem->fields['minlev'].", ".$objItem->fields['maxwt'].", 1, 'N', 0, 0, '".$objItem->fields['twohand']."', ".$objItem->fields['repair'].", 'U')") or die($db->ErrorMsg());
		      } 
		    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest->fields['id']);
		      }
		    $objTest->Close();
		    break;
		  default:
		    break;
		  }
	      }
	  }
      }
    $_SESSION['maction']['rooms'] --;
    //Generate new room
    if (!$blnEnd)
      {
	//Finish mission
	if ($_SESSION['maction']['rooms'] <= 0)
	  {
	    $strFinish = '(<a href="city.php">Koniec</a>)';
	    preg_match('/^[a-zA-Z]+[0-9]+/', $objName->fields['name'], $arrResults);
	    $objName->Close();
	    if (!$blnQuest)
	      {
		$objFinish = $db->Execute("SELECT `id` FROM `missions` WHERE `name`='".$arrResults[0]."finish' ORDER BY RAND() LIMIT 1");
	      }
	    else
	      {
		$objFinish = $db->Execute("SELECT `id` FROM `missions` WHERE `name`='".$arrResults[0]."finishgood' ORDER BY RAND() LIMIT 1");
	      }
	    $_SESSION['maction']['location'] = $objFinish->fields['id'];
	    $objFinish->Close();
	  }
	//Next room
	else
	  {
	    //Function parse actions available for players
	    function parseOptions($arrOptions)
	    {
	      global $player;
	      $arrTmp = explode(';', $arrOptions);
	      $arrProfs = array('Wojownik' => '%fight%', 'Mag' => '%mag%', 'Barbarzyńca' => '%barb%', 'Złodziej' => '%thief%', 'Rzemieślnik' => '%craft%');
	      foreach ($arrTmp as &$strOption)
		{
		  if (strpos($strOption, '%prof%') !== FALSE)
		    {
		      $strOption = str_replace('%prof%', $player->clas, $strOption);
		    }
		  foreach ($arrProfs as $clas => $prof)
		    {
		      $intPos = strpos($strOption, $prof);
		      if ($intPos !== FALSE)
			{
			  if ($player->clas != $clas)
			    {
			      $arrTmp2 = explode(',', $strOption);
			      $intIndex = 0;
			      for ($i = 0; $i < count($arrTmp2); $i++)
				{
				  $intIndex = strpos($arrTmp2[$i], $prof);
				  if ($intIndex !== FALSE)
				    {
				      $intIndex = $i;
				      break;
				    }
				}
			      unset($arrTmp2[$intIndex], $arrTmp2[$intIndex - 1]);
			      $strOption = implode(',', $arrTmp2);
			    }
			  else
			    {
			      $strOption = str_replace($prof, $player->clas, $strOption);
			    }
			}
		    }
		}
	      return $arrTmp;
	    }

	    $objMission = $db->Execute("SELECT * FROM `missions` WHERE `name`='".$_POST['action']."' ORDER BY RAND() LIMIT 1");
	    $_SESSION['maction']['location'] = $objMission->fields['id'];
	    $_SESSION['maction']['exits'] = array();
	    $_SESSION['maction']['items'] = array();
	    $_SESSION['maction']['mobs'] = array();
	    $_SESSION['maction']['moreinfo'] = array();
	    //Generate exits
	    $arrTmp = parseOptions($objMission->fields['exits']);
	    $arrChances = explode(';', $objMission->fields['chances']);
	    while (count($_SESSION['maction']['exits']) == 0)
	      {
		for ($i = 0; $i < count($arrChances); $i++)
		  {
		    if ($arrTmp[$i] == '')
		      {
			continue;
		      }
		    if (preg_match('/\[[a-zA-Z]\]/', $arrTmp[$i], $arrResults))
		      {
			if ($arrResults[0][1] != $_SESSION['maction']['type'])
			  {
			    continue;
			  }
			$arrTmp[$i] = str_replace($arrResults[0], '', $arrTmp[$i]);
		      }
		    $intRoll = rand(0, 100);
		    if ($intRoll < $arrChances[$i])
		      {
			$_SESSION['maction']['exits'][] = $arrTmp[$i];
		      }
		  }
	      }
	    //Generate items
	    $arrTmp = parseOptions($objMission->fields['items']);
	    $arrChances = explode(';', $objMission->fields['chances3']);
	    for ($i = 0; $i < count($arrChances); $i ++)
	      {
		$intRoll = rand(0, 100);
		if ($intRoll < $arrChances[$i])
		  {
		    $_SESSION['maction']['items'][] = $arrTmp[$i];
		  }
	      }
	    //Generate mobs
	    $arrTmp = parseOptions($objMission->fields['mobs']);
	    $arrChances = explode(';', $objMission->fields['chances2']);
	    for ($i = 0; $i < count($arrChances); $i ++)
	      {
		$intRoll = rand(0, 100);
		if ($intRoll < $arrChances[$i])
		  {
		    $_SESSION['maction']['mobs'][] = $arrTmp[$i];
		  }
	      }
	    //More data for location
	    if ($objMission->fields['moreinfo'] != '')
	      {
		$_SESSION['maction']['moreinfo'] = explode(';', $objMission->fields['moreinfo']);
		if ($_SESSION['maction']['moreinfo'][0] == 'skill')
		  {
		    $strSkill = $_SESSION['maction']['moreinfo'][1];
		    $player->checkexp(array($strSkill => $player->skills[$strSkill][1]), $player->id, "skills");
		  }
	      }
	    $objMission->Close();
	    $db->Execute("UPDATE `mactions` SET `location`=".$_SESSION['maction']['location'].", `exits`='".implode(';', $_SESSION['maction']['exits'])."', `mobs`='".implode(';', $_SESSION['maction']['mobs'])."', `items`='".implode(';', $_SESSION['maction']['items'])."', `rooms`=`rooms`-1, `successes`=".$_SESSION['maction']['successes'].", `moreinfo`='".implode(';', $_SESSION['maction']['moreinfo'])."' WHERE `pid`=".$player->id);
	  }
      }
  }

/**
 * Show room
 */
$objText = $db->Execute("SELECT `id`, `name`, `text` FROM `missions` WHERE `id`='".$_SESSION['maction']['location']."'");
$strText = $objText->fields['text'];
$arrOptions = array();
if (strpos($objText->fields['name'], 'resign') !== FALSE)
  {
    $blnEnd = TRUE;
    $strFinish = '(<a href="city.php">Koniec</a>)';
  }
if (count($_SESSION['maction']['moreinfo']) > 0)
  {
    if ($_SESSION['maction']['moreinfo'][0] == 'combat')
      {
	$strFinish = 'combat';
      }
  }
if ($strFinish == '')
  {
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
elseif ($strFinish == 'combat')
{
  $strFinish = '';
  $smarty->assign(array("Text" => $strText,
			'Moptions' => array(),
			'Anext' => '',
			'Afinish' => ' '));
  $smarty->display('mission.tpl');
  unset($_POST['action']);
  battle();
  if ($objText->fields['id'] != $_SESSION['maction']['location'])
    {
      $objText = $db->Execute("SELECT `id`, `text` FROM `missions` WHERE `id`='".$_SESSION['maction']['location']."'");
      $strText = $objText->fields['text'];
      $objText->Close();
      $arrOptions = array();
      if ($strFinish == '')
	{
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
    }
  else
    {
      require_once("includes/foot.php");
      return;
    }
}
$objText->Close();
if ($strFinish != '' && $strFinish != 'combat')
  {
    //Successful mission
    if ($_SESSION['maction']['rooms'] == 0 && !$blnEnd)
      {
	if ($_SESSION['maction']['successes'] > 0)
	  {
	    if ($_SESSION['maction']['target'] == 'Y' && !$blnQuest)
	      {
		$intExpgain = (5 * $_SESSION['maction']['successes']);
		$intGold = ($_SESSION['maction']['successes'] * 50);
		$intMpoint = 0;
	      }
	    else
	      {
		$intExpgain = (5 * $_SESSION['maction']['successes']) + (5 * $_SESSION['maction']['bonus']);
		$intGold = (5 * $_SESSION['maction']['successes'] * 50) + (10 * $_SESSION['maction']['bonus']);
		$intMpoint = 1;
	      }
	  }
	else
	  {
	    $intExpgain = 1;
	    $intGold = 0;
	    $intMpoint = 0;
	  }
	$strSkill = '';
	$strText .= '<br /><br />Zdobywasz ';
	if ($intGold > 0)
	  {
	    $strText .= $intGold.' sztuk złota, ';
	  }
	$strText .= $intExpgain.' punktów doświadczenia';
	if ($_SESSION['maction']['type'] == 'T')
	  {
	    if ($_SESSION['maction']['loot'] != '' && $intMpoint)
	      {
		$strItemname = 'Wytrychy z miedzi';
		$strPlanname = 'Wytrychy';
		$strItemtext = 'nowe wytrychy.';
		$strPlantext = 'plan wytrychów.';
	      }
	  }
	$strText .= '.';
	if ($_SESSION['maction']['loot'] != '' && $intMpoint)
	  {
	    $arrLoot = explode(';', $_SESSION['maction']['loot']);
	    $objLoot = $db->Execute("SELECT * FROM `".$arrLoot[0]."` WHERE `level`".$arrLoot[1]." AND `type`='".$arrLoot[2]."' ORDER BY RAND() LIMIT 1") or die($db->ErrorMsg());
	    if ($arrLoot[0] == 'tools')
	      {
		$objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$strItemname."' AND `power`=".$objLoot->fields['power']." AND `cost`=1 AND `wt`=".$objLoot->fields['dur']." AND `maxwt`=".$objLoot->fields['dur']." AND `type`='E' AND `minlev`=".$objLoot->fields['level']." AND repair=".$objLoot->fields['repair']);
	      }
	    else
	      {
		$objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$strPlanname."' AND `minlev`=".$objLoot->fields['level']." AND `cost`=1 AND `type`='P'");
	      }
	    if (!$objTest->fields['id'])
	      {
		if ($arrLoot[0] == 'tools')
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$strItemname."', ".$objLoot->fields['power'].", 'E', 1, 0, ".$objLoot->fields['dur'].", ".$objLoot->fields['level'].", ".$objLoot->fields['dur'].", 1, 'N', 0, 0, 'N', ".$objLoot->fields['repair'].")") or die($db->ErrorMsg());
		  }
		else
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$strPlanname."', 0, 'P', 1, 0, 1, ".$objLoot->fields['level'].", 1, 1, 'N', 0, 0, 'N', 1)") or die($db->ErrorMsg());
		  }
	      }
	    else
	      {
		$db->Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest->fields['id']);
	      }
	    $objTest->Close();
	    $objLoot->Close();
	    if ($arrLoot[0] == 'tools')
	      {
		$strText .= 'Oprócz tego dostajesz '.$strItemtext;
	      }
	    else
	      {
		$strText .= 'Oprócz tego dostajesz '.$strPlantext;
	      }
	  }
	if ($strSkill != '')
	  {
	    $player->checkexp(array($strSkill => $intExpgain), $player->id, "skills");
	  }
	$player->checkexp(array('condition' => $intExpgain), $player->id, "stats");
	$db->Execute("UPDATE `players` SET `miejsce`='".$_SESSION['maction']['place']."', `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+".$intMpoint." WHERE `id`=".$player->id);
      }
    elseif ($_SESSION['maction']['type'] != 'T')
      {
	$db->Execute("UPDATE `players` SET `miejsce`='".$_SESSION['maction']['place']."' WHERE `id`=".$player->id);
      }
    unset($_SESSION['maction']);
    $db->Execute("DELETE FROM `mactions` WHERE `pid`=".$player->id);
  }

/**
 * Assign variables to template and display page
 */
$smarty->assign(array("Text" => $strText,
		      'Moptions' => $arrOptions,
		      'Anext' => 'Dalej',
		      'Afinish' => $strFinish));
$smarty->display('mission.tpl');

require_once("includes/foot.php");
?>