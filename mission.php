<?php
/**
 *   File functions:
 *   Random missions
 *
 *   @name                 : mission.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 13.01.2012
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
require_once("includes/checkexp.php");

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
				 'exits' => explode(';', $objMission->fields['exits']),
				 'mobs' => explode(';', $objMission->fields['mobs']),
				 'items' => explode(';', $objMission->fields['items']),
				 'type' => $objMission->fields['type'],
				 'loot' => $objMission->fields['loot'],
				 'rooms' => $objMission->fields['rooms']);
    $objMission->Close();
  }

/**
 * Generate new room
 */
$strFinish = '';
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
    if (!in_array($_POST['action'], $arrEactions) && !in_array($_POST['action'], $arrIactions) && !in_array($_POST['action'], $arrMactions))
      {
	error('Zapomnij o tym!');
      }
    $blnEnd = FALSE;
    //Player choice action with mob
    if (in_array($_POST['action'], $arrEactions) || in_array($_POST['action'], $arrIactions))
      {
	$intDiff = 10;
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
	  }
	$intRoll = rand(1, 100);
	//Thieves
	if ($_SESSION['maction']['type'] == 'T')
	  {
	    //Mission fail
	    if ($intRoll <= $intDiff)
	      {
		$strFinish = '(<a href="lochy.php">Koniec</a>)';
		$objFinish = $db->Execute("SELECT `id` FROM `missions` WHERE `name`='thieffail' ORDER BY RAND() LIMIT 1");
		$_SESSION['maction']['location'] = $objFinish->fields['id'];
		$cost = 1000 * $player -> level;
		checkexp($player->exp, $player->level, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'thievery', 0.01);
		$db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `crime`=`crime`-1 WHERE `id`=".$player -> id);
		$strDate = $db -> DBDate($newdate);
		$db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player->id.",'Nieudane zadanie', 7, ".$cost.", '".$data."')");                
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'Zostałeś wtrącony do więzienia na 1 dzień. Możesz wyjść za kaucją: ".$cost.".', ".$strDate.", 'T')");
		$blnEnd = TRUE;
	      }
	    else
	      {
		$intExpgain = $player->level * 10;
		$fltSkill = $player->level / 100;
		checkexp($player->exp, $player->level, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'thievery', 0.01);
	      }
	  }
      }
    $_SESSION['maction']['rooms'] --;
    //Generate new room
    if (!$blnEnd)
      {
	//Finish mission
	if ($_SESSION['maction']['rooms'] == 0)
	  {
	    //Thieves
	    if ($_SESSION['maction']['type'] == 'T')
	      {
		$strFinish = '(<a href="city.php">Koniec</a>)';
		$objFinish = $db->Execute("SELECT `text` FROM `missions` WHERE `name`='thieffinish' ORDER BY RAND() LIMIT 1");
		$_SESSION['maction']['location'] = $objFinish->fields['id'];
		$objFinish->Close();
	      }
	  }
	//Next room
	else
	  {
	    $objMission = $db->Execute("SELECT * FROM `missions` WHERE `name`='".$_POST['action']."' ORDER BY RAND() LIMIT 1");
	    $_SESSION['maction']['location'] = $objMission->fields['id'];
	    $_SESSION['maction']['exits'] = array();
	    $_SESSION['maction']['items'] = array();
	    $_SESSION['maction']['mobs'] = array();
	    //Generate exits
	    $arrTmp = explode(';', $objMission->fields['exits']);
	    $arrChances = explode(';', $objMission->fields['chances']);
	    while (count($arrOptions) == 0)
	      {
		for ($i = 0; $i < count($arrChances); $i++)
		  {
		    $intRoll = rand(0, 100);
		    if ($intRoll < $arrChances[$i])
		      {
			$_SESSION['maction']['exits'][] = $arrTmp[$i];
		      }
		  }
	      }
	    //Generate items
	    $arrTmp = explode(';', $objMission->fields['items']);
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
	    $arrTmp = explode(';', $objMission->fields['mobs']);
	    $arrChances = explode(';', $objMission->fields['chances2']);
	    for ($i = 0; $i < count($arrChances); $i ++)
	      {
		$intRoll = rand(0, 100);
		if ($intRoll < $arrChances[$i])
		  {
		    $_SESSION['maction']['mobs'][] = $arrTmp[$i];
		  }
	      }
	    $objMission->Close();
	    $db->Execute("UPDATE `mactions` SET `location`=".$_SESSION['maction']['location'].", `exits`='".$_SESSION['maction']['exits']."', `mobs`='".$_SESSION['maction']['mobs']."', `items`='".$_SESSION['maction']['items']."', `rooms`=`rooms`-1 WHERE `pid`=".$player->id);
	  }
      }
  }

/**
 * Show room
 */
$objText = $db->Execute("SELECT `text` FROM `missions` WHERE `id`='".$_SESSION['maction']['location']."'");
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
else
  {
    //Successful mission
    if ($_SESSION['maction']['rooms'] == 0)
      {
	if ($_SESSION['maction']['type'] == 'T')
	  {
	    $intExpgain = $player->level * 20;
	    $fltSkill = $player->level / 50;
	    $intGold = $player->level * 50;
	    checkexp($player->exp, $player->level, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'thievery', 0.01);
	    $strText .= '<br /><br />Zdobywasz '.$intGold.' sztuk złota, '.$intExpgain.' punktów doświadczenia oraz '.$fltSkill.' do umiejętności Złodziejstwo.';
	    if ($_SESSION['maction']['loot'] != '')
	      {
		$arrLoot = explode(';', $_SESSION['maction']['loot']);
		$objLoot = $db->Execute("SELECT * FROM `".$arrLoot[0]."` WHERE `level`=".$arrLoot[1]." AND `type`='",$arrLoot[2]."' ORDER BY RAND() LIMIT 1");
		$objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='Wytrychy z miedzi' AND `power`=".$objLoot->fields['power']." AND `cost`=1 AND `wt`=".$objLoot->fields['dur']." AND `maxwt`=".$objLoot->fields['dur']." AND `type`='E' AND `minlev`=".$objLoot->fields['level']." AND repair=".$objLoot->fields['repair']);
		if (!$objTest->fields['id'])
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", 'Wytrychy z miedzi', ".$objLoot->fields['power'].", 'E', 1, 0, ".$objLoot->fields['dur'].", ".$objLoot->fields['level'].", ".$objLoot->fields['dur'].", 1, 'N', 0, 0, 'N', ".$objLoot->fields['repair'].")") or die($db->ErrorMsg());
		  }
		else
		  {
		    $db->Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest->fields['id']);
		  }
		$objTest->Close();
		$objLoot->Close();
		$strText .= 'Oprócz tego dostajesz nowe wytrychy.';
	      }
	    $db->Execute("UPDATE `players` SET `miejsce`='Altara', `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	  }
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