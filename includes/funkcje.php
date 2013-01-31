<?php
/**
 *   File functions:
 *   Functions to fight in PvP and fast fight PvM
 *
 *   @name                 : funkcje.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 31.01.2013
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

/**
* Get the localization for game
*/
require_once("languages/".$lang."/funkcje.php");

/**
 * Function return hit location
 */
function hitlocation()
{
  $intResult = rand(1, 100);
  if ($intResult < 11)
    {
      return 0;
    }
  elseif ($intResult > 10 && $intResult < 71)
    {
      return 1;
    }
  elseif ($intResult > 70 && $intResult < 86)
    {
      return 2;
    }
  elseif ($intResult > 85)
    {
      return 3;
    }
}

/**
 * Function check did pet survive battle
 */
function checkpet($intPid, &$arrPet, $intEid, $blnLost = FALSE)
{
  global $db;

  if ($arrPet[0] == 0)
    {
      return;
    }

  if (!$blnLost)
    {
      $intRoll = rand(1, 100);
    }
  else
    {
      $intRoll = 1;
    }
  if ($intRoll == 1)
    {
      $db->Execute("UPDATE `core` SET `status`='Dead', `active`='N' WHERE `id`=".$arrPet[0]);
      $arrPet = array(0, 0, 0);
      if ($intPid == $intEid)
	{
	  print "<br />Twój chowaniec nie przeżył tej walki.<br />";
	}
    }
}

/**
 * Function update battle records
 */
function battlerecords($strEname, $intLevel, $intPid)
{
  global $db;

  //Don't count bandits in travel
  if ($strEname == 'Bandyta')
    {
      return;
    }
  //Update battle records
  $blnAdd = FALSE;
  $objTest = $db->Execute("SELECT `pid` FROM `brecords` WHERE `pid`=".$intPid." AND `mlevel`>=".$intLevel);
  if (!$objTest->fields['pid'])
    {
      $blnAdd = TRUE;
    }
  else
    {
      $objTest = $db->Execute("SELECT `pid` FROM `brecords` WHERE `mlevel`=".$intLevel." AND `mname`='".$strEname."'");
      if (!$objTest->fields['pid'])
	{
	  $blnAdd = TRUE;
	}
    }
  $objTest->Close();
  if ($blnAdd)
    {
      $objDay = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='day'");
      $db->Execute("INSERT INTO `brecords` (`pid`, `mdate`, `mlevel`, `mname`) VALUES(".$intPid.", ".$objDay->fields['value'].", ".$intLevel.", '".$strEname."')") or die($db->ErrorMsg());
      $objDay->Close();
    }
}

/**
 * Function check for monsters loot
 */
function monsterloot($arrNames, $arrChances, $intLevel, $intMonsters = 1)
{
  global $db;
  global $player;

  //No loot, exit
  if (count($arrNames) < 3)
    {
      return;
    }

  $arrFound = array();

  for ($i = 0; $i < $intMonsters; $i++)
    {
      $fltChance = (float)100 / $intLevel;
      $fltRandom = (float)rand(0, 5000) / 100;
      
      //Bad luck, exit
      if ($fltRandom > $fltChance)
	{
	  continue;
	}
  
      //Check which component player found
      $intKey = -1;
      $intRoll = rand(1, 100);
      foreach ($arrChances as $intChance)
	{
	  $intKey++;
	  if ($intRoll <= $intChance)
	    {
	      break;
	    }
	}
      
      if (array_key_exists($intKey, $arrFound))
	{
	  $arrFound[$intKey]++;
	}
      else
	{
	  $arrFound[$intKey] = 1;
	}
    }

  if (count($arrFound) == 0)
    {
      return;
    }

  //Add component to player equipment
  $strMessage = "<br />Ze zwłok potwora wyciągasz:<br />";
  foreach ($arrFound as $intKey => $intAmount)
    {
      $intPrice = $intLevel * 5;
      $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `name`='".$arrNames[$intKey]."' AND `owner`=".$player->id." AND `status`='U' AND `type`='O' AND `minlev`=".$intLevel." AND `cost`=".$intPrice);
      if (!$objTest->fields['id'])
	{
	  $db->Execute("INSERT INTO `equipment` (`owner`, `name`, `type`, `cost`, `minlev`, `amount`, `status`) VALUES(".$player->id.", '".$arrNames[$intKey]."', 'O', ".$intPrice.", ".$intLevel.", ".$intAmount.", 'U')");
	}
      else
	{
	  $db->Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest->fields['id']);
	}
      $objTest->Close();
      $strMessage .= $intAmount." ".$arrNames[$intKey]."<br />";
    }
  print $strMessage;
}

/**
 * Function auto reload quivers
 */
function autofill($intPlayerid, &$arrArrows, $intPlayer2, $intPlevel)
{
  global $db;

  if ($arrArrows[6] == 25)
    {
      return;
    }
  if ($arrArrows[0])
    {
      $objNewArrows = $db->Execute("SELECT `wt`, `id` FROM `equipment` WHERE name='".$arrArrows[1]."' AND `power`=".$arrArrows[2]." AND `status`='U' AND `owner`=".$intPlayerid." AND `ptype`='".$arrArrows[3]."' AND `poison`=".$arrArrows[8]);
      if (!$objNewArrows->fields['id'])
	{
	  if ($intPlayerid == $intPlayer2)
	    {
	      print "<br />Nie masz strzał aby uzupełnić kołczan!<br />";
	    }
	  if ($arrArrows[6] <= 0)
	    {
	      $db->Execute("DELETE FROM `equipment` WHERE `id`=".$arrArrows[0]);
	    }
	  else
	    {
	      $db->Execute("UPDATE `equipment` SET `wt`=".$arrArrows[6]." WHERE `id`=".$arrArrows[0]);
	    }
	  return;
	}
      $intAmount = 25 - $arrArrows[6];
      if ($intAmount > $objNewArrows->fields['wt'])
	{
	  $intAmount = $objNewArrows->fields['wt'];
	}
      $db->Execute("UPDATE `equipment` SET `wt`=".($intAmount + $arrArrows[6])." WHERE `id`=".$arrArrows[0]);
      $arrArrows[6] += $intAmount;
      if ($intPlayerid == $intPlayer2)
	{
	  print "<br />Uzupełniłeś(aś) kołczan ".$intAmount." strzałami.<br />";
	}
    }
  else
    {
      $objNewArrows = $db->SelectLimit("SELECT * FROM `equipment` WHERE `owner`=".$intPlayerid." AND `type`='R' AND status='U' AND `minlev`<=".$intPlevel." ORDER BY `power` ASC", 1);
      if (!$objNewArrows->fields['id'])
	{
	  if ($intPlayerid == $intPlayer2)
	    {
	      print "<br />Twój kołczan jest pusty a Ty nie masz strzał aby go uzupełnić!<br />";
	    }
	  return;
	}
      if ($objNewArrows->fields['wt'] > 25)
	{
	  $intAmount = 25;
	}
      else
	{
	  $intAmount = $objNewArrows->fields['wt'];
	}
      $db -> Execute("INSERT INTO `equipment` (`name`, `wt`, `power`, `status`, `type`, `owner`, `ptype`, `poison`, `minlev`) VALUES('".$objNewArrows->fields['name']."',".$intAmount.",".$objNewArrows->fields['power'].",'E','R',".$intPlayerid.", '".$objNewArrows->fields['ptype']."', ".$objNewArrows->fields['poison'].", ".$objNewArrows->fields['minlev'].")");
      $arrArrows[0] = $objNewArrows -> fields['id'];
      $arrArrows[1] = $objNewArrows -> fields['name'];
      $arrArrows[2] = $objNewArrows -> fields['power'];
      $arrArrows[3] = $objNewArrows -> fields['ptype'];
      $arrArrows[4] = $objNewArrows -> fields['minlev'];
      $arrArrows[5] = $objNewArrows -> fields['zr'];
      $arrArrows[6] = $objNewArrows -> fields['wt'];
      $arrArrows[7] = $objNewArrows -> fields['szyb'];
      $arrArrows[8] = $objNewArrows -> fields['poison'];
      $arrArrows[9] = $objNewArrows -> fields['maxwt'];
      $arrArrows[10] = $objNewArrows->fields['magic'];
      $arrArrows[11] = $objNewArrows->fields['repair'];
      if ($intPlayerid == $intPlayer2)
	{
	  print "<br />Włożyłeś(aś) ".$objNewArrows->fields['name']." do kołczanu.<br />";
	}
    }
  if ($objNewArrows->fields['wt'] == $intAmount)
    {
      $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objNewArrows->fields['id']);
    }
  else
    {
      $db -> Execute("UPDATE `equipment` SET `wt`=`wt`-".$intAmount." WHERE `id`=".$objNewArrows->fields['id']);
    }
  $objNewArrows->Close();
}

/**
* Function count lost stats in battle
*/
function loststat($objPlayer, $winid, $winuser, $starter) 
{
    global $db;
    global $newdate;

    $blnCheat = FALSE;
    $strMessage = '';
    if ($objPlayer->antidote[0] == 'R')
      {
	$db->Execute("UPDATE `players` SET `antidote`='', `hp`=1 WHERE `id`=".$objPlayer->id);
	$intPower = substr($objPlayer->antidote, 1);
	if (rand(1, 100) < $intPower)
	  {
	    $blnCheat = TRUE;
	  }
      }
    if (!$blnCheat)
      {
	$strMessage =  ' '.$objPlayer->loststat();
	$db -> Execute("UPDATE `players` SET `hp`=0, `antidote`='' WHERE `id`=".$objPlayer->id);
      }
    if ($lostid == $starter) 
      {
        $attacktext = YOU_ATTACK;
      } 
    else 
      {
        $attacktext = YOU_ATTACKED;
      }
    if ($winid > 0) 
      {
        $strDate = $db -> DBDate($newdate);
	if ($antidote != 'R')
	  {
	    $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$lostid.",'".$attacktext." ".YOU_LOSE." <b><a href=view.php?view=".$winid.">".$winuser."</a> </b>(poziom: ".$winlevel.") <b>".ID.":".$winid."</b>.".$strMessage."', ".$strDate.", 'B')") or die(E_LOG);
	  }
	else
	  {
	    $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$lostid.",'".$attacktext." ".YOU_LOSE." <b><a href=view.php?view=".$winid.">".$winuser."</a> ".ID.":".$winid."</b>. Na szczęście udało ci się tym razem oszukać śmierć.', ".$strDate.", 'B')") or die(E_LOG);
	    $_SESSION['ressurect'] = 'Y';
	  }
      } 
    else 
      {
        if (!isset($_POST['razy'])) 
	  {
            $_POST['razy'] = 1;
	  }
	if ($antidote != 'R')
	  {
	    if ($lost > 0)
	      {
		$strMessage = " ".YOU_LOST." ".$lost." ".$stat;
	      }
	    print "<br /><b>".B_RESULT." <b>".$_POST['razy']." ".$winuser."</b>.".$strMessage;
	  }
	else
	  {
	    print "<br /><b>".B_RESULT." <b>".$_POST['razy']." ".$winuser."</b>. Na szczęście udało ci się tym razem oszukać śmierć.";
	    $_SESSION['ressurect'] = 'Y';
	  }
      }
}

/**
* Function count gaining abilities in fight
*/
function gainability ($objPlayer, $intExp, $gunik, $gatak, $gmagia, $player2, $stats) 
{
    global $db;

    $arrStats = array('condition' => 0,
		      'wisdom' => 0,
		      'speed' => 0);
    $arrSkills = array();
    if ($gunik > 0)
      {
	$arrStats['agility'] = 0;
	$arrSkills['dodge'] = 0;
      }
    if ($gatak > 0)
      {
	switch ($stats)
	  {
	  case 'ranged':
	    $arrStats['agility'] = 0;
	    $arrStats['strength'] = 0;
	    $arrSkills['shoot'] = 0;
	    break;
	  case 'melee':
	    $arrStats['strength'] = 0;
	    $arrSkills['attack'] = 0;
	    break;
	  default:
	    break;
	  }
	
      }
    if ($gmagia > 0)
      {
	$arrStats['inteli'] = 0;
	$arrSkills['magic'] = 0;
      }
    $intDiv = count($arrStats) + count($arrSkills);
    foreach ($arrStats as &$intStat)
      {
	$intStat = ceil($intExp / $intDiv);
      }
    foreach ($arrSkills as &$intSkill)
      {
	$intSkill = ceil($intExp / $intDiv);
      }
    $objPlayer->checkexp($arrStats, $player2, 'stats');
    $objPlayer->checkexp($arrSkills, $player2, 'skills');
    if ($objPlayer->mana <= 0) 
    {
        $objPlayer->mana = 0;
    }
    $db -> Execute("UPDATE `players` SET `pm`=".$objPlayer->mana." WHERE `id`=".$objPlayer->id);
    $objPlayer->save();
}

/**
* Function count damage of weapons and armors in fight
*/
function lostitem(&$arrEquip, $pid, $player2, $intLevel) 
{
    global $db;

    $arrKeys = array(0, 1, 2, 3, 4, 5, 11);
    $arrPrefix = array(YOU_WEAPON, YOU_WEAPON, YOU_HELMET, YOU_ARMOR, YOU_LEGS, YOU_SHIELD, YOU_WEAPON);
    $arrSuffix = array(HAS_BEEN1, HAS_BEEN1, HAS_BEEN1, HAS_BEEN1, HAS_BEEN2, HAS_BEEN1, HAS_BEEN1);
    $i = 0;
    foreach ($arrKeys as $intKey)
      {
	if ($arrEquip[$intKey][0])
	  {
	    if ($arrEquip[$intKey][6] == 0)
	      {
		$db->Execute("DELETE FROM `equipment` WHERE `id`=".$arrEquip[$intKey][0]);
		if ($pid == $player2)
		  {
		    print "<br />".$arrPrefix[$i]." ".$arrSuffix[$i]." ".IS_BROKEN."!<br />";
		  }
	      }
	    else
	      {
		$db->Execute("UPDATE `equipment` SET `wt`=".$arrEquip[$intKey][6]." WHERE `id`=".$arrEquip[$intKey][0]);
		if ($pid == $player2 && $arrEquip[$intKey][6] < 15)
		  {
		    if ($i != 4)
		      {
			$strBroken = 'zniszczy';
		      }
		    else
		      {
			$strBroken = 'zniszczą';
		      }
		    print "<br />".$arrPrefix[$i]." niedługo się ".$strBroken.".<br />";
		  }
	      }
	  }
	$i++;
      }
    //Check quiver
    if ($arrEquip[6][0])
      {
	if ($arrEquip[6][6] == 0)
	  {
	    $db->Execute("DELETE FROM `equipment` WHERE `id`=".$arrEquip[6][0]);
	    $arrEquip[6][0] = 0;
	  }
	 autofill($pid, $arrEquip[6], $player2, $intLevel);
      }
}

/**
 * Function return message for critical hit
 */
function showcritical($strLocation, $strAtype, $strBtype, $strEnemy, $strAttacker = '')
{
  $strMessage = '';
  switch ($strBtype)
    {
    case 'pve':
      $strMessage = 'Jednym niezwykle celnym ';
      switch ($strAtype)
	{
	case 'melee':
	  $strMessage .= 'ciosem trafiasz '.$strEnemy.' '.$strLocation.'. ';
	  switch ($strLocation)
	    {
	    case 'w głowę':
	      $strMessage .= 'Ciężkie jest życie głupka, który na dodatek ma pecha. Dlatego umiera na miejscu.';
	      break;
	    default:
	      $strMessage .= 'Niemal natychmiast twój przeciwnik przechodzi w czas przeszły, dokonany.';
	      break;
	    }
	  break;
	case 'ranged':
	  $strMessage .= 'strzałem trafiasz '.$strEnemy.' '.$strLocation.'. ';
	  switch ($strLocation)
	    {
	    case 'w głowę':
	      $strMessage .= 'Ofiara natychmiast pada martwa, pozbywając się również brudu z małżowin usznych.';
	      break;
	    default:
	      $strMessage .= 'Twój przeciwnik właśnie się przekonał, że nie jest nieśmiertelny.';
	      break;
	    }
	  break;
	case 'spell':
	  $strMessage .= 'czarem trafiasz '.$strEnemy.' '.$strLocation.'. ';
	  switch ($strLocation)
	    {
	    case 'w głowę':
	      $strMessage .= 'Nienaruszony korpus jeszcze przez chwilę stoi w miejscu, jakby nie zauważył braku pewnej części ciała.';
	      break;
	    default:
	      $strMessage .= 'Jedyna pamiątka jaka pozostaje po przeciwniku to nieco dymu w okolicy.';
	      break;
	    }
	  break;
	default:
	  break;
	}
      $strMessage .= '<br />';
      break;
    case 'pvp':
      $strMessage = '<b>'.$strAttacker.'</b> jednym niezwykle celnym ';
      switch ($strAtype)
	{
	case 'melee':
	  $strMessage .= 'ciosem trafia <b>'.$strEnemy.'</b> '.$strLocation.'. ';
	  switch ($strLocation)
	    {
	    case 'w głowę':
	      $strMessage .= 'Ciężkie jest życie głupka, który na dodatek ma pecha. Dlatego <b>'.$strEnemy.'</b> umiera na miejscu.';
	      break;
	    default:
	      $strMessage .= 'Niemal natychmiast <b>'.$strEnemy.'</b> przechodzi w czas przeszły, dokonany.';
	      break;
	    }
	  break;
	case 'ranged':
	  $strMessage .= 'strzałem trafia <b>'.$strEnemy.'</b> '.$strLocation.'. ';
	  switch ($strLocation)
	    {
	    case 'w głowę':
	      $strMessage .= '<b>'.$strEnemy.'</b> natychmiast pada martwy, pozbywając się również brudu z małżowin usznych.';
	      break;
	    default:
	      $strMessage .= '<b>'.$strEnemy.'</b> właśnie się przekonał, że nie jest nieśmiertelny.';
	      break;
	    }
	  break;
	case 'spell':
	  $strMessage .= 'czarem trafia <b>'.$strEnemy.'</b> '.$strLocation.'. ';
	  switch ($strLocation)
	    {
	    case 'w głowę':
	      $strMessage .= 'Nienaruszony korpus jeszcze przez chwilę stoi w miejscu, jakby nie zauważył braku pewnej części ciała.';
	      break;
	    default:
	      $strMessage .= 'Jedyna pamiątka jaka pozostaje po <b>'.$strEnemy.'</b> to nieco dymu w okolicy.';
	      break;
	    }
	  break;
	default:
	  break;
	}
      break;
    default:
      break;
    }
  return $strMessage;
}

/**
 * Function made monster attack
 */
function monsterattack2($intMydodge, &$zmeczenie, &$gunik, &$enemy, $times, $mczaro, $intBlock)
{
  global $player;
  global $smarty;
  global $number;
  global $arrElements3;
  global $arrElements4;

  $strMessage = '';
  //Player dodge
  $intDodgemax = 100;
  $intDodgemax2 = 97;
  if ($enemy['agility'] < 100)
    {
      $intDodgemax = $enemy['agility'];
      if ($intDodgemax < 4)
	{
	  $intDodgemax = 4;
	}
      $intDodgemax2 = floor($intDodgemax * 0.97);
    }
  $szansa = rand(1, $intDodgemax);
  if ($intMydodge >= $szansa && $zmeczenie <= $player->stats['condition'][2] && $szansa < $intDodgemax2) 
    {
      if ($times == 1) 
	{
	  $strMessage = YOU_DODGE." <b>".$enemy['name']."</b>!<br />";
	}
      $gunik++;
      $zmeczenie += ($player->equip[3][4] / 10);
      return $strMessage;
    } 
  //Player block attack with shield
  $szansa = rand(1, 100);
  if ($szansa <= $intBlock && $player->equip[5][6] > 0)
    {
      $strMessage = "Zablokowałeś tarczą atak <b>".$enemy['name']."</b>!<br />";
      $zmeczenie += ($player->equip[5][4] / 10);
      $player->equip[5][6] --;
      return $strMessage;
    }
  //Monster hit
  $arrLocations = array('w głowę i zadaje(ą)', 'w tułów i zadaje(ą)', 'w nogę i zadaje(ą)', 'w rękę i zadaje(ą)');
  $intHit = hitlocation();
  $defpower = 0;
  if ($player->pet[0])
    {
      if ($player->pet[2] > $player->skills['dodge'][1])
	{
	  $defpower = $player->skills['dodge'][1];
	}
      else
	{
	  $defpower = $player->pet[2];
	}
    }
  if ($player->equip[$intHit + 2][0] && $player->equip[$intHit + 2][6] > 0)
    {
      $defpower += ($player->equip[$intHit + 2][2] + ($player->equip[$intHit + 2][2] * $player->checkbonus('defender')));
      $player->equip[$intHit + 2][6] --;
      if ($player->equip[$intHit + 2][10] != 'N')
	{
	  if ($arrElements3[$enemy['dmgtype']] == $player->equip[$intHit + 2][10])
	    {
	      $defpower += $player->equip[$intHit + 2][2];
	    }
	  elseif ($arrElements4[$enemy['dmgtype']] == $player->equip[$intHit + 2][10])
	    {
	      $defpower -= ceil($player->equip[$intHit + 2][2] / 2);
	    }
	}
    }
  $defpower -= ($defpower * $player->checkbonus('rage'));
  $enemy['damage'] -= $defpower;
  if ($enemy['damage'] < 1)
    {
      $enemy['damage'] = 1;
    }
  $player->hp -= $enemy['damage'];
  if ($times == 1) 
    {
      $strMessage = "<b>".$enemy['name']."</b> ".ENEMY_HIT.$arrLocations[$intHit]." <b>".$enemy['damage']."</b> ".DAMAGE."! (".$player -> hp." ".LEFT.")<br>";
    }
  if ($mczaro -> fields['id']) 
    {
      $lost_mana = ceil($mczaro -> fields['poziom'] / 2.5);
      $lost_mana = $lost_mana - (int)($player->skills['magic'][1] / 25);
      if ($lost_mana < 1)
	{
	  $lost_mana = 1;
	}
      if ($player->mana >= $lost_mana)
	{
	  $player->mana -= $lost_mana;
	}
    }
  $enemy['damage'] += $defpower;
  return $strMessage;
}

/**
 * Function made player attack
 */
function playerattack($eunik, $mczar, &$zmeczenie, &$gatak, $stat, &$enemy, &$gmagia, $times, $intPldamage, $krytyk, $enemyhp, $strAtype)
{
  global $player;
  global $number;

  $strMessage = '';
  if ($zmeczenie <= $player->stats['condition'][2]) 
    {
      $intDodgemax = 100;
      $intDodgemax2 = 97;
      if ($player->equip[0][0] || $player->equip[11][0])
	{
	  $strSkill = 'attack';
	}
      elseif ($player->equip[1][0])
	{
	  $strSkill = 'shoot';
	}
      else
	{
	  $strSkill = 'magic';
	}
      if ($player->stats['agility'][2] + $player->skills[$strSkill][1] < 100)
	{
	  $intDodgemax = $player->stats['agility'][2] + $player->skills[$strSkill][1];
	  if ($intDodgemax < 4)
	    {
	      $intDodgemax = 4;
	    }
	  $intDodgemax2 = floor($intDodgemax * 0.97);
	}
      $szansa = rand(1, $intDodgemax);
      //Monster dodge
      if ($eunik >= $szansa && $szansa < $intDodgemax2) 
	{
	  if ($times == 1) 
	    {
	      if ((($player->equip[0][6] > 0 || $player->equip[11][6] > 0) || ($player->equip[1][6] > 0 && $player->equip[6][6] > 0)) && ($player->equip[0][0] || $player->equip[1][0])) 
		{
		  $strMessage = "<b>".$enemy['name']."</b> ".ENEMY_DODGE."<br />";
		}
	      if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
		{
		  $strMessage =  "<b>".$enemy['name']."</b> ".ENEMY_DODGE."<br />";
		}
	    }
	  if ((($player->equip[0][6] > 0 || $player->equip[11][6] > 0) || ($player->equip[1][6] > 0 && $player->equip[6][6] > 0)) && ($player->equip[0][0] || $player->equip[1][0])) 
	    {
	      if ($player->equip[1][0]) 
		{
		  $zmeczenie += ($player->equip[1][4] / 10);
		  $player->equip[1][6] --;
		  $player->equip[6][6] --;
		}
	      if ($player->equip[0][0]) 
		{
		  $zmeczenie += ($player->equip[0][4] / 10);
		}
	      if ($player->equip[11][0])
		{
		  $zmeczenie += ($player->equip[11][4] / 10);
		}
	    }
	  return $strMessage;
	} 
      //Player hit
      else
	{
	  //Critical hit
	  $rzut = rand(1, 1000) / 10;
	  $intRoll = rand(1, 100);
	  if ($krytyk >= $rzut && $intRoll <= $krytyk)
            {
	      //Hit with weapon
	      if ((($player->equip[0][6] > 0 || $player->equip[11][6] > 0) || ($player->equip[1][6] > 0 && $player->equip[6][6] > 0)) && ($player->equip[0][0] || $player->equip[1][0])) 
		{
		  $gatak++;
		  $enemy['hp'] -= $enemyhp;
		  if ($player->equip[0][0])
		    {
		      $player->equip[0][6] --;
		    }
		  if ($player->equip[11][0])
		    {
		      $player->equip[11][6] -- ;
		    }
		  if ($player->equip[1][0])
		    {
		      $player->equip[1][6] --;
		      $player->equip[6][6] --;
		    }
		  if ($times == 1)
		    {
		      $arrLocations = array('w tułów', 'w głowę', 'w kończynę');
		      $intHit = rand(0, 2);
		      $strMessage = showcritical($arrLocations[$intHit], $strAtype, 'pve', $enemy['name']);
		    }
		}
	      //Hit with spell
	      if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
		{
		  $player -> mana --;
		  $gmagia++;
		  $enemy['hp'] -= $enemyhp;
		  if ($times == 1)
		    {
		      $arrLocations = array('w tułów', 'w głowę', 'w kończynę');
		      $intHit = rand(0, 2);
		      $strMessage = showcritical($arrLocations[$intHit], $strAtype, 'pve', $enemy['name']);
		    }
		}
	      return $strMessage;
	    }
	  //Hit with weapon
	  if ((($player->equip[0][6] > 0 || $player->equip[11][6] > 0) || ($player->equip[1][6] > 0 && $player->equip[6][6] > 0)) && ($player->equip[0][0] || $player->equip[1][0])) 
	    {
	      $enemy['hp'] -= $stat['damage'];
	      if ($times == 1) 
		{
		  $arrLocations = array('w tułów', 'w głowę', 'w kończynę');
		  $intHit = rand(0, 2);
		  $strMessage = YOU_HIT." <b>".$enemy['name']."</b> ".$arrLocations[$intHit]." ".INFLICT." <b>".$stat['damage']."</b> ".DAMAGE."! (".$enemy['hp']." ".LEFT.")</font><br>";
		}
	      if ($player->equip[0][0]) 
		{
		  $zmeczenie += ($player->equip[0][4] / 10);
		  $player->equip[0][6] --;
		} 
	      elseif ($player->equip[1][0]) 
		{
		  $zmeczenie += ($player->equip[1][4] / 10);
		  $player->equip[1][6] --;
		  $player->equip[6][6] --;
		}
	      if ($player->equip[11][0])
		{
		  $zmeczenie += ($player->equip[11][4] / 10);
		  $player->equip[11][6] --;
		}
	      if ($stat['damage'] > 0) 
		{
		  $gatak++;
		}
	      return $strMessage;
	    }
	  //Hit with spell
	  if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
	    {
	      $pech = floor($player->skills['magic'][1] - $mczar -> fields['poziom']);
	      if ($pech > 0) 
		{
		  $pech = 0;
		}
	      $pech += rand(1,100);
	      //Proper hit
	      if ($pech > 5) 
		{
		  $player -> mana --;
		  $enemy['hp'] = ($enemy['hp'] - $stat['damage']);
		  if ($times == 1) 
		    {
		      $arrLocations = array('w tułów', 'w głowę', 'w kończynę');
		      $intHit = rand(0, 2);
		      $strMessage = YOU_HIT." <b>".$enemy['name']."</b> ".$arrLocations[$intHit]." ".INFLICT." <b>".$stat['damage']."</b> ".DAMAGE."! (".$enemy['hp']." ".LEFT.")</font><br>";
		    }
		  if ($stat['damage'] > 0) 
		    {
		      $gmagia++;
		    }
		  return $strMessage;
		}
	      else 
		{
		  $pechowy = rand(1,100);
		  if ($pechowy <= 25) 
		    {
		      if ($times == 1) 
			{
			  $strMessage = "<b>".$player -> user."</b> ".YOU_MISS1." <b>".$mczar -> fields['poziom']."</b> ".MANA.".<br />";
			}
		      $player->mana --;
		    }
		  elseif ($pechowy > 25 && $pechowy <= 45)
		    {
		      if ($times == 1)
			{
			  $strMessage = "<b>".$player->user."</b> zapatrzył się na szybko poruszającego się żółwia i stracił koncentrację.<br />";
			}
		    }
		  elseif ($pechowy > 45 && $pechowy <= 50)
		    {
		      if ($times == 1) 
			{
			  $strMessage = "<b>".$player -> user."</b> ".YOU_MISS2.".<br>";
			}
		      $player->mana = 0;
		    }
		  elseif ($pechowy > 50 && $pechowy <= 55)
		    {
		      if ($times == 1) 
			{
			  $strMessage = "<b>".$player -> user."</b> ".YOU_MISS3." ".$intPldamage.HP."!<br />";
			}
		      $player->hp -= $intPldamage;
		    }
		  elseif ($pechowy > 55 && $pechowy <= 85)
		    {
		      if ($pechowy < 65)
			{
			  $intDamage = floor($stat['damage'] * 0.75);
			}
		      elseif ($pechowy < 75)
			{
			  $intDamage = floor($stat['damage'] * 0.5);
			}
		      else
			{
			  $intDamage = floor($stat['damage'] * 0.25);
			}
		      $enemy['hp'] -= $intDamage;
		      $player->mana --;
		      if ($times == 1)
			{
			  $strMessage = "<b>".$player -> user."</b> nie do końca opanował zaklęcie, dlatego jego czar zadaje <b>".$intDamage."</b> obrażeń. (".$enemy['hp']." zostało)<br />";
			}
		    }
		  else
		    {
		      if ($pechowy < 90)
			{
			  $intDamage = floor($stat['damage'] * 0.25);
			}
		      elseif ($pechowy < 95)
			{
			  $intDamage = floor($stat['damage'] * 0.5);
			}
		      else
			{
			  $intDamage = floor($stat['damage'] * 0.75);
			}
		      $enemy['hp'] -= $intDamage;
		      $player->mana --;
		      $player->hp -= $intDamage;
		      if ($times == 1)
			{
			  $strMessage = "<b>".$player -> user."</b> próbował rzucić zaklęcie, ale eksplodowało ono w rękach, raniąc jego oraz wroga. Traci przez to ".$intDamage." punktów życia (".$player->hp." zostało), <b>".$enemy['name']."</b> otrzymuje ".$intDamage." obrażeń (".$enemy['hp']." zostało)<br />";
			}
		    }
		  return $strMessage;
		}
	    }
	}
    }
}

/**
* Function fast fight between players and monsters
*/
function fightmonster($enemy, $expgain, $goldgain, $times) 
{
    global $player;
    global $smarty;
    global $title;
    global $newdate;
    global $db;
    global $number;
    global $arrTags;
    global $lang;
    global $arrElements3;
    global $arrElements4;

    $mczar = $db -> Execute("SELECT * FROM `czary` WHERE `status`='E' AND `gracz`=".$player -> id." AND `typ`='B'");
    $mczaro = $db -> Execute("SELECT * FROM `czary` WHERE `status`='E' AND `gracz`=".$player -> id." AND `typ`='O'");
    $strName = $player->user;
    $player->user = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];
    $player->skills['attack'][1] += $player->checkbonus('weaponmaster');
    $player->skills['shoot'][1] += $player->checkbonus('weaponmaster');
    $player->stats['speed'][2] += $player->checkbonus('tactic');

    if (isset ($_POST['razy']) && $_POST['razy'] > 1) 
    {
        $enemyhp = $enemy['hp'] / $_POST['razy'];
    } 
        else 
    {
        $enemyhp = $enemy['hp'];
        $_POST['razy'] = 1;
    }
    if (empty ($enemy)) 
      {
        require_once('includes/monsters.php');
	$enemy = encounter();
      }
    if ($title == 'Arena Walk') 
    {
        if (!$player->equip[0][0] && !$mczar -> fields['id'] && !$player->equip[1][0]) 
        {
            error (E_WEAPON);
        }
        if (($player->equip[0][0] && $mczar -> fields['id']) || ($player->equip[1][0] && $mczar -> fields['id']) || ($player->equip[0][0] && $player->equip[1][0])) 
        {
            error (E_WEAPON_SPELL);
        }
        if ($player->equip[1][0] && !$player->equip[6][0]) 
        {
            error (E_QUIVER);
        }
        if ($player -> clas != 'Mag' && $mczar -> fields['id']) 
        {
            error (E_SPELL);
        }
        if ($player -> clas == 'Mag' && $mczar -> fields['id'] && $player -> mana == 0) 
        {
            error (E_MANA);
        }
    }
    $arrElements3 = array('water' => 'W',
			  'fire' => 'F',
			  'wind' => 'A',
			  'earth' => 'E');
    $arrElements4 = array('water' => 'F',
			    'fire' => 'A',
			    'wind' => 'E',
			    'earth' => 'W');
    $strAtype = 'none';
    $enemy['damage'] = $enemy['strength'] - ($player->stats['condition'][2] + ($player->stats['condition'][2] * $player->checkbonus('defender')));
    if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
      {
	$enemy['damage'] -= ceil($player->skills['dodge'][1] / 10);
      }
    $strSkill = 'magic';
    if ($player->equip[0][0]) 
    {
        if ($player->equip[0][3] == 'D') 
        {
            $player->equip[0][2] = $player->equip[0][2] + $player->equip[0][8];
        }
	if ($player->equip[0][10] != 'N' && $player->equip[0][10] == $enemy['resistance'][0])
	  {
	    switch ($enemy['resistance'][1])
	      {
	      case 'weak':
		$player->equip[0][2] -= ($player->equip[0][2] * 0.1);
		break;
	      case 'medium':
		$player->equip[0][2] -= ($player->equip[0][2] * 0.25);
		break;
	      case 'strong':
		$player->equip[0][2] -= ($player->equip[0][2] * 0.5);
		break;
	      default:
		break;
	      }
	  }
        if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
	  {
	    $stat['damage'] = (($player ->stats['strength'][2] + $player->equip[0][2]) + ceil($player->skills['attack'][1]));
	  } 
	else 
	  {
            $stat['damage'] = ($player->stats['strength'][2] + $player->equip[0][2]);
	  }
        if ($player->skills['attack'][1] > 5) 
        {
            $krytyk = 6;
        } 
            else 
        {
            $krytyk = $player->skills['attack'][1];
        }
	$krytyk += $player->checkbonus('assasin');
	$strAtype = 'melee';
	$strSkill = 'attack';
    }
    if ($player->equip[11][0])
      {
	$stat['damage'] += (($player->equip[11][2] + $player->stats['strength'][2]) + ceil($player->skills['attack'][1] / 10));
	$strSkill = 'attack';
      }
    if ($player->equip[1][0]) 
    {
        $bonus = $player->equip[1][2] + $player->equip[6][2];
	if ($player->equip[6][3] == 'D') 
        {
	  $bonus += $player->equip[6][8];
	}
	if ($player->equip[6][10] != 'N' && $player->equip[6][10] == $enemy['resistance'][0])
	  {
	    switch ($enemy['resistance'][1])
	      {
	      case 'weak':
		$bonus -= ($player->equip[6][2] * 0.1);
		break;
	      case 'medium':
		$bonus -= ($player->equip[6][2] * 0.25);
		break;
	      case 'strong':
		$bonus -= ($player->equip[6][2] * 0.5);
		break;
	      default:
		break;
	      }
	  }
        $bonus2 = (($player->stats['strength'][2] / 2) + ($player->stats['agility'][2] / 2));
        if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
	  {
	    $stat['damage'] = (($bonus2 + $bonus) + ceil($player->skills['shoot'][1] / 10));
        } 
            else 
        {
            $stat['damage'] = ($bonus2 + $bonus);
        }
        if ($player ->skills['shoot'][1] > 5) 
        {
            $krytyk = 6;
        } 
            else 
        {
            $krytyk = $player ->skills['shoot'][1];
        }
	$krytyk += $player->checkbonus('assasin');
        if (!$player->equip[6][0]) 
        {
            $stat['damage'] = 0;
        }
	$strAtype = 'ranged';
	$strSkill = 'shoot';
    }
    if ($mczar -> fields['id']) 
    {
        $stat['damage'] = ($mczar -> fields['obr'] * $player ->stats['inteli'][2]);
	$intBonus = $player->checkbonus('bspells');
	$stat['damage'] += ($stat['damage'] * $intBonus);
	$intBonus = $player->checkbonus($mczar->fields['element']);
	$stat['damage'] += ($stat['damage'] * $intBonus);
	if ($enemy['resistance'][0] == $mczar->fields['element'])
	  {
	    switch ($enemy['resistance'][1])
	      {
	      case 'weak':
		$stat['damage'] -= ($stat['damage'] * 0.25);
		break;
	      case 'medium':
		$stat['damage'] -= ($stat['damage'] * 0.5);
		break;
	      case 'strong':
		$stat['damage'] -= ($stat['damage'] * 0.75);
		break;
	      default:
		break;
	      }
	  }
	if ($player->equip[3][0])
	  {
	    $stat['damage'] -= ($stat['damage'] * ($player->equip[3][4] / 100));
	  }
        if ($player->equip[2][0]) 
        {
	  $stat['damage'] -= ($stat['damage'] * ($player->equip[2][4] / 100));
        }
        if ($player->equip[4][0]) 
        {
            $stat['damage'] -= ($stat['damage'] * ($player->equip[4][4] / 100));
        }
        if ($player->equip[5][0]) 
        {
            $stat['damage'] -= ($stat['damage'] * ($player->equip[5][4] / 100));
        }
        if ($player->equip[7][0]) 
        {
            $intN = ceil($player->equip[7][4] / 20);
            $intBonus = $player->skills['magic'][1] * rand(1, $intN);
            $stat['damage'] += $intBonus;
        }
        if ($stat['damage'] < 0) 
        {
            $stat['damage'] = 0;
        }
        if ($player ->skills['magic'][1] > 5) 
        {
            $krytyk = 6;
        } 
            else 
        {
            $krytyk = $player ->skills['magic'][1];
        }
	$strAtype = 'spell';
	$strSkill = 'magic';
    }
    if ($mczaro -> fields['id']) 
    {
        $myczarobr = ($player -> stats['wisdom'][2] * $mczaro -> fields['obr']);
	$intBonus = $player->checkbonus('dspells');
	$myczarobr += ($myczarobr * $intBonus);
	$intBonus = $player->checkbonus($mczaro->fields['element']);
	$myczarobr += ($myczarobr * $intBonus);
	$fltBasedef = $myczarobr;
	if ($enemy['dmgtype'] != 'none')
	  {
	    if ($mczaro->fields['element'] == $enemy['dmgtype'])
	      {
		$myczarobr = $myczarobr * 2;
	      }
	    $arrElements = array('water' => 'fire',
				 'fire' => 'wind',
				 'wind' => 'earth',
				 'earth' => 'water');
	    if ($mczaro->fields['element']  == $arrElements[$enemy['dmgtype']])
	      {
		$myczarobr = $myczarobr / 2;
	      }
	  }
	if ($player->equip[3][0])
	  {
	    $myczarobr -= ($fltBasedef * ($player->equip[3][4] / 100));
	  }
        if ($player->equip[2][0]) 
        {
            $myczarobr -= ($fltBasedef * ($player->equip[2][4] / 100));
        }
        if ($player->equip[4][0]) 
        {
            $myczarobr -= ($fltBasedef * ($player->equip[4][4] / 100));
        }
        if ($player->equip[5][0]) 
        {
            $myczarobr -= ($fltBasedef * ($player->equip[5][4] / 100));
        }
        if ($player->equip[7][0]) 
        {
	    $intN = ceil($player->equip[7][4] / 20);
            $intBonus = $player->skills['magic'][1] * rand(1, $intN);
            $myczarobr = ($myczarobr + $intBonus);
        }
        if ($myczarobr < 0) 
        {
            $myczarobr = 0;
        }
        $enemy['damage'] -= $myczarobr;
    }
    $eunik = (($enemy['agility'] - $player->stats['agility'][2]) - $player->skills[$strSkill][1]);
    if ($player->equip[11][0])
      {
	$eunik -= ($player->skills['attack'][1] / 5);
      }
    if ($player -> clas == 'Wojownik'  || $player -> clas == 'Barbarzyńca') 
      {
	$myunik = (($player->stats['agility'][2] - $enemy['agility']) + ceil($player->skills['dodge'][1] / 10) + $player ->skills['dodge'][1]);
    }
    else
    {
        $myunik = ($player->stats['agility'][2] - $enemy['agility'] + $player->skills['dodge'][1]);
    }
    if (!isset($myunik) || $myunik < 1) 
    {
        $myunik = 1;
    }
    if (!isset($eunik) || $eunik < 1) 
    {
        $eunik = 1;
    }
    if ($player->equip[1][0]) 
      {
	$eunik -= $player->checkbonus('eagleeye');
        $eunik = $eunik * 2;
      }
    $gunik = 0;
    $gatak = 0;
    $gmagia = 0;
    $zmeczenie = 0;
    $runda = 1;
    if (!isset($stat['damage'])) 
    {
        $stat['damage'] = 0;
    }
    if ($player -> clas == 'Rzemieślnik')
    {
        $stat['damage'] = $stat['damage'] - ($stat['damage'] / 4);
    }
    $stat['damage'] += ($stat['damage'] * $player->checkbonus('rage'));
    if ($player->pet[0])
      {
	if ($player->pet[1] > $player->skills[$strSkill][1])
	  {
	    $stat['damage'] += $player->skills[$strSkill][1];
	  }
	else
	  {
	    $stat['damage'] += $player->pet[1];
	  }
      }
    $intPldamage = $stat['damage'];
    $stat['damage'] = ($stat['damage'] - $enemy['endurance']);
    $rzut2 = rand(1, $player ->skills[$strSkill][1]);
    $stat['damage'] += $rzut2;
    $intPldamage += $rzut2;
    if ($stat['damage'] < 1) 
    {
        $stat['damage'] = 0 ;
    }
    if ($stat['damage'] > $enemyhp) 
    {
        $stat['damage'] = $enemyhp;
    }
    $rzut1 = rand(0, $enemy['level']);
    if (!isset($enemy['damage'])) 
      {
	$enemy['damage'] = $enemy['strength'] - ($player -> stats['condition'][2] + ($player->stats['condition'][2] * $player->checkbonus('defender')));
      }
    $enemy['damage'] = ($enemy['damage'] + $rzut1);
    if ($enemy['damage'] < 1) 
    {
        $enemy['damage'] = 1;
    }
    $stat['attackstr'] = ceil($player->stats['speed'][2] / $enemy['speed']);
    if ($stat['attackstr'] > 5) 
    {
        $stat['attackstr'] = 5;
    }
    $enemy['attackstr'] = ceil($enemy['speed'] / $player->stats['speed'][2]);
    if ($enemy['attackstr'] > 5) 
    {
        $enemy['attackstr'] = 5;
    }
    //Shield block chance
    $intBlock = 0;
    if ($player->equip[5][0])
      {
	$intBlock = ceil($player->equip[5][2] / 5);
	if ($intBlock > 20)
	  {
	    $intBlock = 20;
	  }
      }
    $strMessage = "<ul><li><b>".$player -> user."</b> ".VERSUS." <b>".$enemy['name']."</b><br />";

    while ($player -> hp > 0 && $enemy['hp'] > 0 && $runda < 25) 
    {
        if ($zmeczenie > $player -> stats['condition'][2]) 
        {
            $enemy['damage'] = $enemy['strength'];
            if ($player -> clas == 'Mag' && $player -> mana >= $mczaro -> fields['poziom'] && $mczaro -> fields['id'])
            {
                $enemy['damage'] = $enemy['damage'] - $myczarobr;
                if ($enemy['damage'] < 1)
                {
                    $enemy['damage'] = 1;
                }
            }
        }
        if ($player -> mana < 1 && $mczar->fields['id']) 
        {
            $stat['damage'] = 0;
        }
        if ($player -> mana < $mczaro -> fields['poziom']) 
        {
            $enemy['damage'] = $enemy['strength'];
        }
        if ($player->equip[0][0] && $player->equip[0][6] < 0) 
        {
            $stat['damage'] = 0;
            $krytyk = 1;
        }
        if ($player->equip[1][0] && ($player->equip[1][6] < 0 || $player->equip[6][6] < 0)) 
        {
            $stat['damage'] = 0;
            $krytyk = 1;
        }
        if ($player->equip[1][0] && !$player->equip[6][0]) 
        {
            $stat['damage'] = 0;
            $krytyk = 1;
        }
        if (isset ($_POST['razy']) && $_POST['razy'] > 1) 
        {
            $amount1 = $enemy['hp'] / $enemyhp;
            $amount = ($amount1 * $enemy['attackstr']);
        } 
            else 
        {
            $amount = $enemy['attackstr'];
        }
        if ($stat['attackstr'] >= $enemy['attackstr']) 
        {
            for ($i = 1;$i <= $stat['attackstr']; $i++) 
            {
                if ($enemy['hp'] > 0 && $player -> hp > 0) 
		  {
		    $strMessage .= playerattack($eunik, $mczar, $zmeczenie, $gatak, $stat, $enemy, $gmagia, $times, $intPldamage, $krytyk, $enemyhp, $strAtype);
		  }
            }
            if (isset ($_POST['razy']) && $_POST['razy'] > 1) 
            {
                $amount1 = $enemy['hp'] / $enemyhp;
                $amount = ($amount1 * $enemy['attackstr']);
                if ($amount < 1)
                {
                    $amount = 1;
                }
                if (isset($amount) && $amount < 6)
                {
                    if (isset($myunik))
                    {
                        $intMydodge = ceil($myunik / $amount);
                    }
                        else
                    {
                        $intMydodge = 1;
                    }
                }
                    else
                {
                    $intMydodge = 1;
                }
            } 
                else 
            {
                $amount = $enemy['attackstr'];
                $intMydodge = $myunik;
            }
            for ($i = 1;$i <= $amount; $i++) 
            {
                if ($player -> hp > 0 && $enemy['hp'] > 0) 
		  {
		    $strMessage .= monsterattack2($intMydodge, $zmeczenie, $gunik, $enemy, $times, $mczaro, $intBlock);
		  }
            }
        } 
            else 
        {
            if (isset ($_POST['razy']) && $_POST['razy'] > 1) 
            {
                $amount1 = $enemy['hp'] / $enemyhp;
                $amount = ($amount1 * $enemy['attackstr']);
                if ($amount < 6)
                {
                    $intMydodge = ceil($myunik / $amount);
                }
                    else
                {
                    $intMydodge = 1;
                }
            } 
                else 
            {
                $amount = $enemy['attackstr'];
                $intMydodge = $myunik;
            }
            for ($i = 1;$i <= $amount; $i++) 
            {
                if ($player -> hp > 0 && $enemy['hp'] > 0) 
		  {
		    $strMessage .= monsterattack2($intMydodge, $zmeczenie, $gunik, $enemy, $times, $mczaro, $intBlock);
		  }
            }
            for ($i = 1;$i <= $stat['attackstr']; $i++) 
            {
                if ($enemy['hp'] > 0 && $player -> hp > 0) 
		  {
                    $strMessage .= playerattack($eunik, $mczar, $zmeczenie, $gatak, $stat, $enemy, $gmagia, $times, $intPldamage, $krytyk, $enemyhp, $strAtype);
		  }
            }
        }
        $runda++;
    }
    $smarty->assign('Message', $strMessage);
    $smarty->display('error1.tpl');
    if ($player -> hp <= 0) 
      {
	if ($player->antidote == 'R')
	  {
	    $player->hp = 1;
	  }
	$db -> Execute("UPDATE `players` SET `antidote`='' WHERE `id`=".$player -> id);
        if ($title != 'Arena Walk') 
	  {
	    $player->dying();
	  } 
	else 
	  {
	    $strMessage = "</ul>".LOST_FIGHT."...<br />";
	    if ($player->antidote == 'R')
	      {
		$strMessage .= "Nagle oślepia ciebie jasne światło. Leżysz na ziemi zmęczony ale i szczęśliwy, kiedy zdajesz sobie sprawę, że tym razem udało ci się uniknąć śmierci.<br />";
		$player->antidote = '';
	      }
            $smarty -> assign ("Message", $strMessage);
            $smarty -> display ('error1.tpl');
	  }
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player -> user." ".EVENT3." ".$_POST['razy']." ".$enemy['name']."')");
	checkpet($player->id, $player->pet, $player->id, TRUE);
      } 
    elseif ($runda > 24 && ($player -> hp > 0 && $enemy['hp'] > 0)) 
      {
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player -> user." ".EVENT1." ".$_POST['razy']." ".$enemy['name']." ".EVENT2."')");
        $smarty -> assign ("Message", "<br /><li><b>".B_RESULT1.": ");
        $smarty -> display ('error1.tpl');
	if ($player->hp < 1)
	  {
	    $player->hp = 1;
	  }
      } 
    else 
      {
        monsterloot($enemy['lootnames'], $enemy['lootchances'], $enemy['level'], $_POST['razy']);
	battlerecords($enemy['name'], $enemy['level'], $player->id);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$goldgain." WHERE `id`=".$player -> id);
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player -> user." ".EVENT." ".$_POST['razy']." ".$enemy['name']."')");
        $smarty -> assign ("Message", "<br /><li><b".B_RESULT2." <b>".$_POST['razy']." ".$enemy['name']."</b>.");
        $smarty -> display ('error1.tpl');
        print "<li><b>".REWARD." <b>".$expgain."</b> ".EXPERIENCE." <b>".$goldgain."</b> ".GOLD." ";
	if ($player->equip[0][0] || $player->equip[11][0]) 
	  {
	    $strType = 'melee';
	  }
	else
	  {
	    $strType = 'ranged';
	  }
	if ($player->hp < 1)
	  {
	    $player->hp = 1;
	  }
	gainability($player, $expgain, $gunik, $gatak, $gmagia, $player->id, $strType);
      }
    lostitem($player->equip, $player->id, $player->id, $player->skills['shoot'][1]);
    checkpet($player->id, $player->pet, $player->id);
    if ($player -> hp < 0) 
    {
        $player -> hp = 0;
    }
    if (($player->hp > 0) && ($player->settings['autodrink'] != 'N'))
      {
	require_once("includes/functions.php");
	if ($player->settings['autodrink'] == 'A')
	  {
	    drinkfew(0, 0, 'M');
	    drinkfew(0, 0, 'H');
	  }
	else
	  {
	    drinkfew(0, 0, $player->settings['autodrink']);
	  }
      }
    $mczar -> Close();
    $mczaro -> Close();
    $smarty -> assign ("Message", "</ul>");
    $smarty -> display ('error1.tpl');
    if ($title == 'Arena Walk') 
    {
        $smarty -> assign ("Message", "<ul><li><b>".OPTIONS."</b><br /><a href=battle.php?action=monster>".BACK."</a><br /></li></ul>");
        $smarty -> display ('error1.tpl');
    }
    if ($player -> location == 'Portal') 
    {
        $db -> Execute("UPDATE settings SET value=".$enemy['hp']." WHERE setting='monsterhp'");
    }
    if ($title == "Arena Walk")
      {
	$intLostenergy = ($_POST['razy'] * floor(1 + ($enemy['level'] / 20)));
      }
    else
      {
	$intLostenergy = 1;
      }
    $db->Execute("UPDATE `players` SET `hp`=".$player->hp.", `fight`=0, `bless`='', `blessval`=0, `energy`=`energy`-".$intLostenergy.", `pm`=".$player->mana." WHERE `id`=".$player->id);
    $player->user = $strName;
    $player->skills['attack'][1] -= $player->checkbonus('weaponmaster');
    $player->skills['shoot'][1] -= $player->checkbonus('weaponmaster');
    $player->stats['speed'][2] -= $player->checkbonus('tactic');
}
?>
