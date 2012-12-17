<?php
/**
 *   File functions:
 *   Labyrynth in forrest city
 *
 *   @name                 : maze.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 17.12.2012
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
require_once("includes/funkcje.php");
require_once("includes/turnfight.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/maze.php");

if ($player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Function to fight with monsters
*/
function battle($type,$adress) 
{
    global $player;
    global $smarty;
    global $enemy;
    global $arrehp;
    global $db;
    if ($player -> hp <= 0) 
    {
        error (NO_LIFE);
    }
    $enemy1 = $db -> Execute("SELECT * FROM `monsters` WHERE `id`=".$player -> fight);
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
    $intElevel = $enemy1->fields['strength'] + $enemy1->fields['agility'] + $enemy1->fields['speed'] + $enemy1->fields['endurance'] + $enemy1->fields['level'] + $enemy1->fields['hp'];
    $span = ($intElevel / $intPlevel);
    if ($span > 2) 
    {
        $span = 2;
    }
    $expgain = ceil($intElevel  * $span);
    $goldgain = ceil($intElevel * $span);
    $enemy = array("strength" => $enemy1 -> fields['strength'], 
                   "agility" => $enemy1 -> fields['agility'], 
                   "speed" => $enemy1 -> fields['speed'], 
                   "endurance" => $enemy1 -> fields['endurance'], 
                   "hp" => $enemy1 -> fields['hp'], 
                   "name" => $enemy1 -> fields['name'], 
                   "id" => $enemy1 -> fields['id'],  
                   "level" => $enemy1 -> fields['level'],
		   "lootnames" => explode(";", $enemy1->fields['lootnames']),
		   "lootchances" => explode(";", $enemy1->fields['lootchances']),
		   "resistance" => explode(";", $enemy1->fields['resistance']),
		   "dmgtype" => $enemy1->fields['dmgtype']);
    if ($type == 'T') 
    {
        if (!isset ($_POST['action'])) 
        {
            turnfight ($expgain,$goldgain,'',$adress);
        } 
	else 
        {
            turnfight ($expgain,$goldgain,$_POST['action'],$adress);
        }
    } 
    else 
    {
        fightmonster ($enemy,$expgain,$goldgain,1);
    }
    $fight = $db -> Execute("SELECT `fight` FROM `players` WHERE `id`=".$player -> id);
    if ($fight -> fields['fight'] == 0) 
    {
        if ($type == 'T')
	  {
	    $player->energy--;
	    if ($player -> energy < 0) 
	      {
		$player -> energy = 0;
	      }
	    $db -> Execute("UPDATE `players` SET `energy`=".$player -> energy." WHERE `id`=".$player -> id);
	  }
        $smarty -> assign ("Link", "<br /><br /><a href=\"maze.php\">".A_EXPLORE."</a><br />");
    }
    else
    {
        $smarty -> assign("Link", '');
    }
    $fight -> Close();
    $enemy1 -> Close();
}

/** 
 * Add item to player equipment.
 */
function add_item($intId, $type)
{
  global $db;
  global $player;
  if ($type == "M")
    {
      $objItem = $db->Execute("SELECT * FROM `mage_items` WHERE `id`=".$intId);
      $intNewcost = $objItem -> fields['minlev'] * 100;
      $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `cost`, `minlev`, `type`, `power`, `status`) VALUES(".$player -> id.",'".$objItem->fields['name']."',".$intNewcost.",".$objItem->fields['minlev'].",'".$objItem->fields['type']."',".$objItem->fields['power'].",'U')");
    }
  else
    {
      $objItem = $db->Execute("SELECT * FROM `bows` WHERE `id`=".$intId);
      $intNewcost = $objItem -> fields['minlev'] * 100;
      $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `szyb`, `twohand`) VALUES(".$player -> id.",'".$objItem->fields['name']."',".$objItem->fields['power'].",'B',".$intNewcost.",".$objItem->fields['zr'].",".$objItem->fields['maxwt'].",".$objItem->fields['minlev'].",".$objItem->fields['maxwt'].",1,".$objItem->fields['szyb'].",'Y')");
    }
  $strName = $objItem->fields['name'];
  $objItem->Close();
  return $strName;
}

/**
* If player not escape - start fight
*/
if (isset($_GET['step']) && $_GET['step'] == 'battle') 
{
    if (!isset ($_GET['type'])) 
    {
        $type = 'T';
    } 
    else 
    {
        $type = $_GET['type'];
    }
    battle($type,'maze.php?step=battle');
}

/**
* If player escape
*/
if (isset($_GET['step']) && $_GET['step'] == 'run') 
{
    $enemy = $db -> Execute("SELECT * FROM `monsters` WHERE `id`=".$player -> fight);
    if (!$enemy->fields['id'])
      {
	error('Nie masz przed kim uciekać!');
      }
    /**
     * Add bonus to stats and skills
     */
    $player->curskills(array('perception'));
    $player->clearbless(array('speed'));
    $chance = (($player->stats['speed'][2] + $player->skills['perception'][1] + rand(1, 100)) - ($enemy -> fields['speed'] + rand(1, 100)));
    $smarty -> assign ("Chance", $chance);
    if ($chance > 0) 
    {
	$expgain = ceil(($enemy->fields['speed'] + $enemy->fields['endurance'] + $enemy->fields['agility'] + $enemy->fields['strength']) / 100);
        $smarty -> assign(array("Ename" => $enemy -> fields['name'], 
                                "Expgain" => $expgain,
                                "Escapesucc" => ESCAPE_SUCC,
                                "Escapesucc2" => ESCAPE_SUCC2,
                                "Escapesucc3" => ESCAPE_SUCC3."."));
	$player->checkexp(array('speed' => ($expgain / 2)), $player->id, 'stats');
	$player->checkexp(array('perception' => ($expgain / 2)), $player->id, 'skills');
        $db -> Execute("UPDATE `players` SET `fight`=0 WHERE `id`=".$player -> id);
    } 
        else 
    {
        $strMessage = ESCAPE_FAIL." ".$enemy -> fields['name']." ".ESCAPE_FAIL2.".<br />";
	$db->Execute("UPDATE `players` SET `perception`=`perception`+0.01 WHERE `id`=".$player->id);
        $smarty -> assign ("Message", $strMessage);
        $smarty -> display ('error1.tpl');
        battle('T','maze.php?step=battle');
    }
    $hp = $db -> Execute("SELECT `hp` FROM `players` WHERE `id`=".$player -> id);
    $smarty -> assign ("Health", $hp -> fields['hp']);
    if ($hp -> fields['hp'] > 0) 
    {
        $smarty -> assign("Link", "<a href=\"maze.php\">".A_EXPLORE."</a>");
    }
    $hp -> Close();
}

if (!isset($_GET['action']))
{
    $smarty -> assign(array("Mazeinfo" => MAZE_INFO,
			    "Explore" => A_EXPLORE,
			    "Amount" => floor($player->energy / 0.3),
			    "Times" => "razy"));
    $_GET['action'] = '';
}

$strQuestName = "";

/**
 * Explore tower
 */
if (isset($_GET['action']) && $_GET['action'] == 'explore')
{
    if (!isset($_POST['amount']))
      {
	error("Podaj ile razy chcesz zwiedzać.");
      }
    integercheck($_POST['amount']);
    checkvalue($_POST['amount']);
    $intAmount2 = intval($_POST['amount']);
    $intNeeded = $intAmount2 * 0.3;
    if ($player -> energy < $intNeeded) 
    {
        error (NO_ENERGY);
    }
    if ($player -> hp <= 0)
    {
        error(YOU_DEAD);
    }
    if (!empty($player -> fight)) 
    {
        $enemy = $db -> Execute("SELECT `name` FROM `monsters` WHERE `id`=".$player -> fight);
        error (FIGHT1.$enemy -> fields['name'].FIGHT2."<br />
                   <a href=maze.php?step=battle&type=T>".Y_TURN_F."</a><br />
                   <a href=maze.php?step=battle&type=N>".Y_NORM_F."</a><br />
               <a href=maze.php?step=run>".NO."</a><br />");
        $enemy -> Close();
    }
    $finish = FALSE;
    $arrHerbs = array('illani', 'illanias', 'nutari', 'dynallca');
    foreach ($arrHerbs as $strHerb)
      {
	$arrHerbs2[$strHerb] = 0;
      }
    $arrLumber = array('pine', 'hazel', 'yew', 'elm');
    foreach ($arrLumber as $strLumber)
      {
	$arrLumber2[$strLumber] = 0;
      }
    $arrType = array(T_PINE, T_HAZEL, T_YEW, T_ELM);
    $intMithril = 0;
    $intGold = 0;
    $intEnergy = 0;
    $arrSpells = array();
    $arrPlans = array();
    $arrAplans = array();
    $arrMagic = array();
    $arrBows = array();
    $arrAstral = array();
    $intTimes = 0;
    $encounter = FALSE;
    while (!$finish)
      {
	$intRoll = rand(1, 100);
	$intRoll2 = rand(1, 10);
	if ($intRoll > 48 && $intRoll < 64)
	  {
	    $arrmonsters = array(58, 59, 63, 69, 73, 76, 82, 84, 93, 95, 98, 101, 103);
	    $rzut2 = rand(0,12);
	    $enemy = $db -> Execute("SELECT `name` FROM `monsters` WHERE `id`=".$arrmonsters[$rzut2]);
	    $db -> Execute("UPDATE `players` SET `fight`=".$arrmonsters[$rzut2]." WHERE `id`=".$player -> id);
	    $finish = TRUE;
	    $encounter = TRUE;
	    $smarty->assign(array("Name" => $enemy->fields['name'],
				  "Youmeet" => YOU_MEET,
				  "Fight2" => FIGHT2,
				  "Yturnf" => Y_TURN_F,
				  "Ynormf" => Y_NORM_F,
				  "Ano" => NO));
	    $enemy->Close();
	  }
	if ($intRoll > 63 && $intRoll < 70)
	  {
	    $intRoll2 = rand(0, 3);
	    $intAmount = rand(1, 10);
	    $arrHerbs2[$arrHerbs[$intRoll2]] += $intAmount;
	  }
	if ($intRoll > 69 && $intRoll < 76)
	  {
	    $intAmount = rand(1, 10);
	    $intRoll2 = rand(0, 3);
	    $arrLumber2[$arrLumber[$intRoll2]] += $intAmount;
	  }
	if ($intRoll > 75 && $intRoll < 81)
	  {
	    $intMithril += rand(1, 5);
	  }
	if ($intRoll > 80 && $intRoll < 89)
	  {
	    $intEnergy++;
	  }
	if (($intRoll > 88) && ($intRoll < 92))
	  {
	    if ($intRoll2 < 6)
	      {
		$strSymbol = '<';
	      }
	    else
	      {
		$strSymbol = '=';
	      }
	  }
	if (($intRoll > 91) && ($intRoll < 96))
	  {
	    if ($intRoll2 < 6)
	      {
		$strSymbol = '<';
	      }
	    if ($intRoll2 == 6 || $intRoll2 == 7)
	      {
		$strSymbol = '=';
	      }
	    if ($intRoll2 == 8)
	      {
		$strSymbol = '>';
	      }
	  }
	if ($intRoll > 88 && $intRoll < 91)
	  {
	    $intRoll3 = rand(1,3);
	    switch ($intRoll3)
	      {
	      case 1:
		$strType = 'B';
		break;
	      case 2:
		$strType = 'O';
		break;
	      case 3:
		$strType = 'U';
		break;
	      default:
		break;
	      }
	    if ($intRoll2 < 9)
	      {
		$objQuery = $db -> Execute("SELECT count(`id`) FROM `czary` WHERE `gracz`=0 AND `poziom`".$strSymbol."".$player->skills['magic'][1]." AND `typ`='".$strType."'");
		$intAmount = $objQuery->fields['count(`id`)'];
		$objQuery -> Close();
		if ($intAmount > 0)
		  {
		    $objSpell = $db->Execute("SELECT `id`, `nazwa`  FROM `czary` WHERE `gracz`=0 AND `poziom`".$strSymbol."".$player->skills['magic'][1]." AND `typ`='".$strType."' ORDER BY RAND() LIMIT 1");
		    $objTest = $db->Execute("SELECT `id` FROM `czary` WHERE `gracz`=".$player->id." AND `nazwa`='".$objSpell->fields['nazwa']."'");
		    if ((!in_array($objSpell->fields['id'], $arrSpells)) && (!$objTest->fields['id']))
		      {
			$arrSpells[] = $objSpell->fields['id'];
		      }
		    $objTest->Close();
		    $objSpell -> Close();
		  }           
	      }
	  }
	if ($intRoll == 91)
	  {
	    if ($intRoll2 < 9)
	      {
		$objQuery = $db -> Execute("SELECT count(`id`) FROM `mill` WHERE `owner`=0 AND `level`".$strSymbol."".$player->skills['carpentry'][1]." AND `type`='B'");
		$intAmount = $objQuery->fields['count(`id`)'] ;
		$objQuery -> Close();
		if ($intAmount > 0)
		  {
		    $intRoll4 = rand(0, ($intAmount-1));
		    $objPlan = $db -> SelectLimit("SELECT `id`, `name` FROM `mill` WHERE `owner`=0 AND `level`".$strSymbol."".$player->skills['carpentry'][1]." AND `type`='B'", 1, $intRoll4);
		    $objTest = $db->Execute("SELECT `id` FROM `mill` WHERE `owner`=".$player->id." AND `name`='".$objPlan->fields['name']."'");
		    if ((!in_array($objPlan->fields['id'], $arrPlans)) && (!$objTest->fields['id']))
		      {
			$arrPlans[] = $objPlan->fields['id'];
		      }
		    $objTest->Close();
		    $objPlan -> Close();
		  }
 	      }
	  }
	if ($intRoll == 92)
	  {
	    if ($intRoll2 < 9)
	      {
		$objQuery = $db -> Execute("SELECT count(`id`) FROM `alchemy_mill` WHERE `owner`=0 AND `level`".$strSymbol."".$player->skills['alchemy'][1]);
		$intAmount = $objQuery->fields['count(`id`)'];
		$objQuery -> Close();
		if ($intAmount > 0)
		  {
		    $intRoll4 = rand(0, ($intAmount-1));
		    $objPlan = $db -> SelectLimit("SELECT `id`, `name` FROM `alchemy_mill` WHERE `owner`=0 AND `level`".$strSymbol."".$player->skills['alchemy'][1], 1, $intRoll4);
		    $objTest = $db->Execute("SELECT `id` FROM `alchemy_mill` WHERE `owner`=".$player->id." AND `name`='".$objPlan->fields['name']."'");
		    if ((!in_array($objPlan->fields['id'], $arrAplans)) && (!$objTest->fields['id']))
		      {
			$arrAplans[] = $objPlan->fields['id'];
		      }
		    $objTest->Close();
		    $objPlan->Close();
		  }
	      }
        }
	if ($intRoll == 93)
	  {
	    if ($intRoll2 < 9)
	      {
		$objQuery = $db -> Execute("SELECT count(`id`) FROM `mage_items` WHERE `minlev`".$strSymbol."".$player->skills['magic'][1]." AND `type`='T'");
		$intAmount = $objQuery->fields['count(`id`)'];
		$objQuery -> Close();
		if ($intAmount > 0)
		  {
		    $intRoll4 = rand(0, ($intAmount-1));
		    $objStaff = $db -> SelectLimit("SELECT `id`, `name` FROM `mage_items` WHERE `minlev`".$strSymbol."".$player->skills['magic'][1]." AND `type`='T'", 1, $intRoll4);
		    $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$objStaff->fields['name']."'");
		    if ((!in_array($objStaff->fields['id'], $arrMagic)) && (!$objTest->fields['id']))
		      {
			$arrMagic[] = $objStaff->fields['id'];
		      }
		    $objTest->Close();
		    $objStaff -> Close();
		  }
	      }
	  }
	if ($intRoll == 94)
	  {
	    if ($intRoll2 < 9)
	      {
		$objQuery = $db -> Execute("SELECT count(`id`) FROM `mage_items` WHERE `minlev`".$strSymbol."".$player->skills['magic'][1]." AND `type`='C'");
		$intAmount = $objQuery->fields['count(`id`)'];
		$objQuery -> Close();
		if ($intAmount > 0)
		  {
		    $intRoll4 = rand(0, ($intAmount-1));
		    $objCape = $db -> SelectLimit("SELECT `id`, `name` FROM `mage_items` WHERE `minlev`".$strSymbol."".$player->skills['magic'][1]." AND type='C'",1, $intRoll4);
		    $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$objCape->fields['name']."'");
		    if ((!in_array($objCape->fields['id'], $arrMagic)) && (!$objTest->fields['id']))
		      {
			$arrMagic[] = $objCape->fields['id'];
		      }
		    $objTest->Close();
		    $objCape -> Close();
		  }
	      }
	  }   
	if ($intRoll == 95)
	  {
	    if ($intRoll2 < 9)
	      {
		$objQuery = $db -> Execute("SELECT count(`id`) FROM `bows` WHERE `minlev`".$strSymbol."".$player->skills['shoot'][1]." AND `type`='B'");
		$intAmount = $objQuery->fields['count(`id`)'];
		$objQuery -> Close();
		if ($intAmount > 0)
		  {
		    $intRoll4 = rand(0, ($intAmount-1));
		    $objBow = $db -> SelectLimit("SELECT `id`, `name` FROM `bows` WHERE `minlev`".$strSymbol."".$player->skills['shoot'][1]." AND `type`='B'",1,$intRoll4);
		    $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$objBow->fields['name']."'");
		    if ((!in_array($objBow->fields['id'], $arrBows)) && (!$objTest->fields['id']))
		      {
			$arrBows[] = $objBow->fields['id'];
		      }
		    $objTest->Close();
		    $objBow -> Close();
		  }
	      }
	  }
	/**
	 * Find astral components
	 */
	if ($intRoll == 96 || $intRoll == 97)
	  {
	    require_once('includes/findastral.php');
	    $strResult = findastral(1);
	    if ($strResult != false)
	      {
		$arrAstral[] = $strResult;
	      }
	  }
	if ($intRoll > 97)
	  {
	    $available = $db -> Execute("SELECT `qid` FROM `quests` WHERE `location`='maze.php' AND `name`='start'");
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
		    $roll = rand(0, $i);
		    $strQuestName = "quest".$arramount[$roll].".php";
		    $finish = TRUE;
		  }  
	      }
	    $available -> Close();
	  }
	$intTimes ++;
	if ($intTimes == $intAmount2)
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
    $arrTest = array(0, 0, $intMithril, $intGold, $intEnergy, count($arrSpells), count($arrPlans), count($arrAplans), count($arrMagic), count($arrBows), count($arrAstral));
    foreach ($arrHerbs2 as $key => $value)
      {
	$arrTest[0] += $value;
      }
    foreach ($arrLumber2 as $key => $value)
      {
	$arrTest[1] += $value;
      }
    $found = FALSE;
    foreach ($arrTest as $intTest)
      {
	if ($intTest > 0)
	  {
	    $found = TRUE;
	    break;
	  }
      }
    if ($found)
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
	$strText .= $intEnergy." razy odwiedził".$strLast." pokój z posążkiem.<br />";
      }
    if ($arrTest[0] > 0)
      {
	$objHerb = $db->Execute("SELECT `gracz` FROM `herbs` WHERE `gracz`=".$player -> id);
	if (!$objHerb -> fields['gracz']) 
	  {
	    $exists = FALSE;
	  }
	else
	  {
	    $exists = TRUE;
	  }
	$objHerb->Close();
	foreach ($arrHerbs2 as $key => $value)
	  {
	    if ($value == 0)
	      {
		continue;
	      }
	    if (!$exists) 
	      {
		$db -> Execute("INSERT INTO `herbs` (`gracz`, `".$key."`) VALUES(".$player -> id.",".$value.")");
		$exists = TRUE;
	      } 
            else 
	      {
		$db -> Execute("UPDATE `herbs` SET `".$key."`=`".$key."`+".$value." WHERE `gracz`=".$player -> id);
	      }
	    $strText .= $value." sztuk ".$key."<br />";
	  }
      }
    if ($arrTest[1] > 0)
      {
	$objLumber = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$player -> id);
	if (!$objLumber -> fields['owner']) 
	  {
	    $exists = FALSE;
	  }
	else
	  {
	    $exists = TRUE;
	  }
	$objLumber->close();
	$i = -1;
	foreach ($arrLumber2 as $key => $value)
	  {
	    $i ++;
	    if ($value == 0)
	      {
		continue;
	      }
	    if (!$exists) 
	      {
		$db -> Execute("INSERT INTO `minerals` (`owner`, `".$key."`) VALUES(".$player -> id.",".$value.")");
		$exists = TRUE;
	      } 
            else 
	      {
		$db -> Execute("UPDATE `minerals` SET `".$key."`=`".$key."`+".$value." WHERE `owner`=".$player -> id);
	      }
	    $strText .= $value." sztuk ".$arrType[$i]."<br />";
	  }
      }
    foreach ($arrSpells as $intSpell)
      {
	$objSpell = $db->Execute("SELECT * FROM `czary` WHERE `id`=".$intSpell);
	$db -> Execute("INSERT INTO `czary` (`gracz`, `nazwa`, `cena`, `poziom`, `typ`, `obr`, `status`, `element`) VALUES(".$player->id.", '".$objSpell->fields['nazwa']."', ".$objSpell->fields['cena'].", ".$objSpell->fields['poziom'].", '".$objSpell->fields['typ']."', ".$objSpell->fields['obr'].", 'U', '".$objSpell->fields['element']."')");
	$strText .= "Czar ".$objSpell->fields['nazwa']."<br />";
	$objSpell->Close();
      }
    foreach ($arrPlans as $intPlan)
      {
	$objPlan = $db->Execute("SELECT * FROM `mill` WHERE `id`=".$intPlan);
	$db -> Execute("INSERT INTO `mill` (`owner`, `name`, `type`, `cost`, `amount`, `level`, `twohand`) VALUES(".$player -> id.",'".$objPlan->fields['name']."', '".$objPlan->fields['type']."' ,".$objPlan->fields['cost'].", ".$objPlan->fields['amount'].", ".$objPlan->fields['level'].", '".$objPlan->fields['twohand']."')");
	$strText .= "Plan ".$objPlan->fields['name']."<br />";
	$objPlan->Close();
      }
    foreach ($arrAplans as $intAplan)
      {
	$objPlan = $db->Execute("SELECT * FROM `alchemy_mill` WHERE `id`=".$intAplan);
	$db -> Execute("INSERT INTO `alchemy_mill` (`owner`, `name`, `cost`, `status`, `level`, `illani`, `illanias`, `nutari`, `dynallca`) VALUES(".$player -> id.",'".$objPlan->fields['name']."',".$objPlan->fields['cost'].",'N',".$objPlan->fields['level'].",".$objPlan->fields['illani'].",".$objPlan->fields['illanias'].",".$objPlan->fields['nutari'].",".$objPlan->fields['dynallca'].")");
	$strText .= "Plan ".$objPlan->fields['name']."<br />";
	$objPlan->Close();
      }
    foreach ($arrMagic as $intMagic)
      {
	$strName = add_item($intMagic, "M");
	$strText .= $strName."<br />";
      }
    foreach ($arrBows as $intBow)
      {
	$strName = add_item($intBow, "B");
	$strText .= $strName."<br />";
      }
    foreach ($arrAstral as $strAstral)
      {
	$strText .= $strAstral."<br />";
      }
    $intTimes = $intTimes * 0.3;
    $strText .= "Zużył".$strLast." na to ".$intTimes." energii.<br />";
    $intEnergy -= $intTimes;
    $db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `platinum`=`platinum`+".$intMithril.", `energy`=`energy`+".$intEnergy."  WHERE `id`=".$player->id);
    if (($strQuestName == '') && ($encounter == FALSE))
      {
	$strBack = "Wróć";
      }
    else
      {
	$strBack = "";
      }
    $smarty->assign(array("Action2" => $strText,
			  "Back" => $strBack,
			  "Encounter" => $encounter));
}

if (isset($_GET['step']) && $_GET['step'] == 'quest') 
{
    $query = $db -> Execute("SELECT `quest` FROM `questaction` WHERE `player`=".$player -> id." AND `action`!='end'");
    $name = "quest".$query -> fields['quest'].".php";
    if (!empty($query -> fields['quest'])) 
    {   
        require_once("quests/".$name);
    }
    $query -> Close();    
}

/**
* Initialization of variable
*/
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'],
                        "Step" => $_GET['step']));
$smarty -> display ('maze.tpl');

if ($strQuestName != "")
  {
    require_once("quests/".$strQuestName);
  }

require_once("includes/foot.php");
?>
