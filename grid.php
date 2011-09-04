<?php
/**
 *   File functions:
 *   Labyrynth - explore and quests
 *
 *   @name                 : grid.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 26.08.2011
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

$title = "Labirynt";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/grid.php");

if ($player -> location != 'Altara' && $player -> location != 'Podróż') 
{
    error (ERROR);
}

$smarty -> assign(array("Chance" => '', 
                        "Maps" => ''));

$query = $db -> Execute("SELECT `quest` FROM `questaction` WHERE `player`=".$player -> id." AND `action`!='end'");
// Enter to labirynth
if (!isset($_GET['action']) && !isset($_GET['step']) && empty($query -> fields['quest']))
  {
    $smarty -> assign(array("Labinfo" => LAB_INFO,
			    "Explore" => A_EXP,
			    "Amount" => floor($player->energy / 0.3),
			    "Times" => "razy"));
  }

$strQuestName = "";

//Explore labirynth
if (isset ($_GET['action']) && $_GET['action'] == 'explore' && empty($query -> fields['quest'])) 
  {

    function findmap()
    {
      global $db;
      global $player;
      $objMaps = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='maps'");
      $roll = rand(1,50);
      if ($roll == 50 && $objMaps -> fields['value'] > 0 && $player -> maps < 20 && $player -> rank != 'Bohater') 
	{
	  $objMaps -> Close();
	  return 1;
	}
      $objMaps -> Close();
      return 0;
    }

    if (!isset($_POST['amount']))
      {
	error("Podaj ile razy chcesz zwiedzać labirynt.");
      }
    integercheck($_POST['amount']);
    checkvalue($_POST['amount']);
    $intAmount = intval($_POST['amount']);
    $intNeeded = $intAmount * 0.3;
    if ($player -> energy < $intNeeded) 
    {
        error (NO_ENERGY);
    }
    if ($player -> hp == 0) 
    {
        error (YOU_DEAD);
    }
    $intGold = 0;
    $intMithril = 0;
    $intEnergy = 0;
    $intTimes = 0;
    $finish = FALSE;
    $arrAstral = array();
    $intMaps = 0;
    while (!$finish)
      {
	$chance = rand(1, 11);
	switch ($chance)
	  {
	  case 3:
	    $intGold += rand(1, 100);
	    break;
	  case 6:
	    $intMithril += rand(1, 3);
	    break;
	  case 7:
	    $intEnergy++;
	    break;
	  case 10:
	    $intRoll = rand(1, 5);
	    if ($intRoll == 5)
	      {
		$available = $db -> Execute("SELECT `qid` FROM `quests` WHERE `location`='grid.php' AND `name`='start'");
		$number = $available -> RecordCount();
		if ($number > 0) 
		  {
		    $arramount = array();
		    $i = 0;
		    while (!$available -> EOF) 
		      {
			$query = $db -> Execute("SELECT `id` FROM `questaction` WHERE `quest`=".$available -> fields['qid']." AND `player`=".$player -> id);
			if (empty($query -> fields['id'])) 
			  {
			    $arramount[$i] = $available -> fields['qid'];
			    $i = $i + 1;
			  }
			$query -> Close();
			$available -> MoveNext();
		      }
		    $i = $i - 1;
		    if ($i >= 0) 
		      {
			$roll = rand(0,$i);
			$strQuestName = "quest".$arramount[$roll].".php";
			$finish = TRUE;
		      } 
                    else 
		      {
			$intMaps += findmap();
		      } 
		  }
		$available -> Close();
	      }
	    break;
	  case 11:
	    require_once('includes/findastral.php');
	    $strResult = findastral(5);
	    if ($strResult != false)
	      {
		$arrAstral[] = $strResult;
	      }
            else
	      {
		$intMaps += findmap();
	      }
	    break;
	  case 9:
	    $intMaps += findmap();
	    break;
	  default:
	    break;
	  }
	$intTimes ++;
	if ($intTimes == $intAmount)
	  {
	    $finish = TRUE;
	  }
      }
    //Count what we found
    if ($player->gender == 'F')
      {
	$strLast = "aś";
      }
    else
      {
	$strLast = "eś";
      }
    
    if (($intGold > 0) || ($intMithril > 0) || ($intEnergy > 0) || ($intMaps > 0) || (count($arrAstral) > 0))
      {
	$strText = "Podczas swojej wędrówki znalazł".$strLast.":<br />";
      }
    else
      {
	$strText = "Wędrował".$strLast." jakiś czas ale nic ciekawego nie znalazł".$strLast.".<br />";
      }
    if ($intGold > 0)
      {
	$strText .= $intGold." sztuk złota<br />";
      }
    if ($intMithril > 0)
      {
	$strText .= $intMithril." sztuk mithrilu<br />";
      }
    if ($intEnergy > 0)
      {
	$strText .= $intEnergy." razy źródełko<br />";
      }
    if ($intMaps > 0)
      {
	$strText .= $intMaps." kawałków mapy<br />";
	$objMaps = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='maps'");
	$intMaps2 = $objMaps -> fields['value'] - $intMaps;
	$db->Execute("UPDATE `settings` SET `value`='".$intMaps2."' WHERE `setting`='maps'");
      }
    foreach ($arrAstral as $strAstral)
      {
	$strText .= $strAstral."<br />";
      }
    $intTimes = $intTimes * 0.3;
    $strText .= "Zużył".$strLast." na to ".$intTimes." energii.<br />";
    if ($strQuestName == '')
      {
	$strBack = "Wróć";
      }
    else
      {
	$strBack = "";
      }
    $smarty->assign(array("Action2" => $strText,
			  "Back" => $strBack));
    $intEnergy -= $intTimes;
    $db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `platinum`=`platinum`+".$intMithril.", `energy`=`energy`+".$intEnergy.", `maps`=`maps`+".$intMaps." WHERE `id`=".$player->id);
}

if ((isset($_GET['step']) && $_GET['step'] == 'quest') || !empty($query -> fields['quest'])) 
{
    $name = "quest".$query -> fields['quest'].".php";
    if ($query -> fields['quest'])
    {
        require_once('includes/statsbonus.php');
        $arrCurstats = statbonus();
        require_once("quests/".$name);   
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
}
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (empty($query -> fields['quest']))
{
    $strQuest = 'N';
}
    else
{
    $strQuest = 'Y';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'], 
                        "Step" => $_GET['step'],
                        "Quest" => $strQuest));
$smarty -> display ('grid.tpl');

$query -> Close();

if ($strQuestName != "")
  {
    require_once('includes/statsbonus.php');
    $arrCurstats = statbonus();
    require_once("quests/".$strQuestName);
  }
require_once("includes/foot.php");
?>
