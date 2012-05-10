<?php
/**
 *   File functions:
 *   Functions to fight in PvP and fast fight PvM
 *
 *   @name                 : funkcje.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 10.05.2012
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

require_once ('includes/checkexp.php');

/**
* Get the localization for game
*/
require_once("languages/".$lang."/funkcje.php");

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
  $objTest = $db->Execute("SELECT `pid` FROM `brecords` WHERE `pid`=".$intPid." AND `mlevel`>=".$intLevel);
  if (!$objTest->fields['pid'])
    {
      $objDay = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='day'");
      $db->Execute("INSERT INTO `brecords` (`pid`, `mdate`, `mlevel`, `mname`) VALUES(".$intPid.", ".$objDay->fields['value'].", ".$intLevel.", '".$strEname."')") or die($db->ErrorMsg());
      $objDay->Close();
    }
  $objTest->Close();
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
      $fltRandom = (float)rand(0, 10000) / 100;
      
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
function autofill($intPlayerid, $intArrowId, $intPlayer2, $intPlevel)
{
  global $db;

  $objArrows = $db->Execute("SELECT * FROM `equipment` WHERE `id`=".$intArrowId);
  if ($objArrows->fields['wt'] == 25)
    {
      return;
    }
  if ($objArrows->fields['id'])
    {
      $objNewArrows = $db->Execute("SELECT `wt`, `id` FROM `equipment` WHERE name='".$objArrows -> fields['name']."' AND `power`=".$objArrows -> fields['power']." AND `status`='U' AND `owner`=".$intPlayerid." AND `ptype`='".$objArrows->fields['ptype']."' AND `poison`=".$objArrows->fields['poison']);
      if (!$objNewArrows->fields['id'])
	{
	  if ($intPlayerid == $intPlayer2)
	    {
	      print "<br />Nie masz strzał aby uzupełnić kołczan!<br />";
	    }
	  return;
	}
      $intAmount = 25 - $objArrows -> fields['wt'];
      if ($intAmount > $objNewArrows->fields['wt'])
	{
	  $intAmount = $objNewArrows->fields['wt'];
	}
      $db->Execute("UPDATE `equipment` SET `wt`=`wt`+".$intAmount." WHERE `id`=".$objArrows -> fields['id']);
      $objArrows->Close();
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
function loststat($lostid, $values, $winid, $winuser, $starter, $antidote, $winlevel = 0) 
{
    global $db;
    global $newdate;

    if ($antidote == 'R')
      {
	$db->Execute("UPDATE `players` SET `antidote`='', `hp`=1 WHERE `id`=".$lostid);
      }
    else
      {
	$number = rand(0,5);
	$stats = array('agility', 'strength', 'inteli', 'wisdom', 'szyb', 'cond');
	$name = array(AGILITY, STRENGTH, INTELIGENCE, WISDOM, SPEED, CONDITION);
	$lost = ($values[$number] / 200);
	if ($values[$number] - $lost < 3)
	  {
	    $lost = 0;
	    $values[$number] = 3;
	  }
	else
	  {
	    $values[$number] -= $lost;
	  }
	$db -> Execute("UPDATE `players` SET `".$stats[$number]."`=".$values[$number].", `hp`=0, `antidote`='' WHERE `id`=".$lostid);
	$stat = $name[$number];
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
	    $strMessage = '';
	    if ($lost > 0)
	      {
		$strMessage = " ".YOU_LOST." ".$lost." ".$stat;
	      }
	    $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$lostid.",'".$attacktext." ".YOU_LOSE." <b><a href=view.php?view=".$winid.">".$winuser."</a> </b>(poziom: ".$winlevel.") <b>".ID.":".$winid."</b>.".$strMessage."', ".$strDate.", 'B')") or die(E_LOG);
	  }
	else
	  {
	    $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$lostid.",'".$attacktext." ".YOU_LOSE." <b><a href=view.php?view=".$winid.">".$winuser."</a> ".ID.":".$winid."</b>. Na szczęście udało ci się tym razem, oszukać śmierć.', ".$strDate.", 'B')") or die(E_LOG);
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
	    $strMessage = '';
	    if ($lost > 0)
	      {
		$strMessage = " ".YOU_LOST." ".$lost." ".$stat;
	      }
	    print "<br /><b>".B_RESULT." <b>".$_POST['razy']." ".$winuser."</b>.".$strMessage;
	  }
	else
	  {
	    print "<br /><b>".B_RESULT." <b>".$_POST['razy']." ".$winuser."</b>. Na szczęście udało ci się tym razem, oszukać śmierć.";
	    $_SESSION['ressurect'] = 'Y';
	  }
      }
}

/**
* Function count gaining abilities in fight
*/
function gainability ($gid,$user,$gunik,$gatak,$gmagia,$pm,$player2,$stats) 
{
    global $db;

    if (($gunik || $gatak || $gmagia) && ($player2 == $gid)) 
    {
        print "<br />".$user." ".GAIN.":<br />";
    }
    if ($gunik > 0) 
    {
        $dunik = ($gunik / 100);
        if ($player2 == $gid) 
        {
            print "<b>".$dunik."</b> ".ABILITY." ".DODGE."<br />";
        }
        $db -> Execute("UPDATE `players` SET `unik`=`unik`+".$dunik." WHERE `id`=".$gid);
    }
    if ($gatak > 0 && $stats == 'weapon') 
    {
        $datak = ($gatak / 100);
        if ($player2 == $gid) 
        {
            print "<b>".$datak."</b> ".ABILITY." ".A_FIGHT."<br />";
        }
        $db -> Execute("UPDATE `players` SET `atak`=`atak`+".$datak." WHERE `id`=".$gid);
    }
    if ($gatak > 0 && $stats == 'bow') 
    {
        $datak = ($gatak / 100);
        if ($player2 == $gid) 
        {
            print "<b>".$datak."</b> ".ABILITY." ".SHOOTING."<br />";
        }
        $db -> Execute("UPDATE `players` SET `shoot`=`shoot`+".$datak." WHERE `id`=".$gid);
    }
    if ($gmagia > 0) 
    {
        $dmagia = ($gmagia / 100);
        if ($player2 == $gid) 
        {
            print "<b>".$dmagia."</b> ".ABILITY." ".C_SPELL."<br />";
        }
        $db -> Execute("UPDATE `players` SET `magia`=`magia`+".$dmagia." WHERE `id`=".$gid);
    }
    if ($pm <= 0) 
    {
        $pm = 0;
    }
    $db -> Execute("UPDATE `players` SET `pm`=".$pm." WHERE `id`=".$gid);
}

/**
* Function count damage of weapons and armors in fight
*/
function lostitem($lostdur,$itemdur,$type,$player,$itemid,$player2,$lost, $intLevel) 
{
    global $db;

    $itemdur = ($itemdur - $lostdur);
    if ($itemdur < 1) 
    {
        $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$itemid);
        if ($type == YOU_QUIVER)
	  {
	    autofill($player, $itemid, $player2, $intLevel);
	  }
        if (($player == $player2) && ($type != YOU_QUIVER))
        {
	  print "<br />".$type." ".$lost." ".IS_BROKEN."!<br />";
        }
    } 
        else 
    {
      if (($player == $player2) && ($type != YOU_QUIVER))
        {
	  print "<br />".$type." ".LOST1." ".$lostdur." ".DURABILITY.".<br />";
        }
      $db -> Execute("UPDATE `equipment` SET `wt`=".$itemdur." WHERE `id`=".$itemid);
      if ($type == YOU_QUIVER)
	{
	  autofill($player, $itemid, $player2, $intLevel);
	}
    }
}

/**
* Function check what armor player have
*/
function checkarmor($torso,$head,$legs,$shield) 
{
    global $armor;
    global $number;

    $test = array($torso,$head,$legs,$shield);
    $number = -1;
    $j = 0;
    $armor = array();
    for ($i=0;$i<4;$i++) 
    {
        if ($test[$i] != 0) 
        {
            $number = ($number + 1);
            if ($i == 0) 
            {
                $armor[$j] = 'torso';
            }
            if ($i == 1) 
            {
                $armor[$j] = 'head';
            }
            if ($i == 2) 
            {
                $armor[$j] = 'legs';
            }
            if ($i == 3) 
            {
                $armor[$j] = 'shield';
            }
            $j = ($j + 1);
        }
    }
    return $armor;
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
function monsterattack2($intMydodge, &$zmeczenie, &$gunik, $arrEquip, &$enemy, $times, $armor, $mczaro, &$gwt, $intBlock)
{
  global $player;
  global $smarty;
  global $number;

  $szansa = rand(1, 100);
  $blnMiss = FALSE;
  //Player dodge
  if ($intMydodge >= $szansa && $zmeczenie <= $player->cond && $szansa < 97) 
    {
      if ($times == 1) 
	{
	  $strMessage = YOU_DODGE." <b>".$enemy['name']."</b>!<br />";
	}
      $gunik++;
      $zmeczenie = ($zmeczenie + $arrEquip[3][4] + 1);
      $blnMiss = TRUE;
    } 
  //Player block attack with shield
  $szansa = rand(1, 100);
  if ($szansa <= $intBlock && !$blnMiss)
    {
      $strMessage = "Zablokowałeś tarczą atak <b>".$enemy['name']."</b>!<br />";
      $zmeczenie = ($zmeczenie + $arrEquip[5][4] + 1);
      $gwt[3]++;
      $blnMiss = TRUE;
    }
  //Monster hit
  if (!$blnMiss)
    {
      $arrLocations = array('w tułów i zadaje(ą)', 'w głowę i zadaje(ą)', 'w nogę i zadaje(ą)', 'w rękę i zadaje(ą)');
      if ($arrEquip[3][0] || $arrEquip[2][0] || $arrEquip[4][0] || $arrEquip[5][0]) 
	{
	  $efekt = rand(0, $number);
	  switch ($armor[$efekt])
	    {
	    case 'torso':
	      $gwt[0]++;
	      $intHit = 0;
	      break;
	    case 'head':
	      $gwt[1]++;
	      $intHit = 1;
	      break;
	    case 'legs':
	      $gwt[2]++;
	      $intHit = 2;
	      break;
	    case 'shield':
	      $gwt[3]++;
	      $intHit = 3;
	      break;
	    default:
	      break;
	    }
	}
      else
	{
	  $intHit = rand(0, 3);
	}
      $player->hp -= $enemy['damage'];
      if ($times == 1) 
	{
	  $strMessage = "<b>".$enemy['name']."</b> ".ENEMY_HIT.$arrLocations[$intHit]." <b>".$enemy['damage']."</b> .".DAMAGE."! (".$player -> hp." ".LEFT.")<br>";
	}
      if ($mczaro -> fields['id']) 
	{
	  $lost_mana = ceil($mczaro -> fields['poziom'] / 2.5);
	  $lost_mana = $lost_mana - (int)($player -> magic / 25);
	  if ($lost_mana < 1)
	    {
	      $lost_mana = 1;
	    }
	  $player->mana -= $lost_mana;
	}
    }
  if ($times == 1)
    {
      $smarty -> assign ("Message", $strMessage);
      $smarty -> display ('error1.tpl');
    }
}

/**
 * Function made player attack
 */
function playerattack($eunik, &$gwtbr, $arrEquip, $mczar, &$zmeczenie, &$gatak, $stat, &$enemy, &$gmagia, $times, $intPldamage, $krytyk, $enemyhp, $strAtype)
{
  global $player;
  global $smarty;
  global $number;

  if ($zmeczenie <= $player->cond) 
    {
      $szansa = rand(1, 100);
      //Monster dodge
      if ($eunik >= $szansa && $szansa < 97) 
	{
	  if ($times == 1) 
	    {
	      if ((($arrEquip[0][6] > $gwtbr || $arrEquip[11][6] > $gwtbr) || ($arrEquip[1][6] > $gwtbr && $arrEquip[6][6] > $gwtbr)) && ($arrEquip[0][0] || $arrEquip[1][0])) 
		{
		  $strMessage = "<b>".$enemy['name']."</b> ".ENEMY_DODGE."<br />";
		}
	      if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
		{
		  $strMessage =  "<b>".$enemy['name']."</b> ".ENEMY_DODGE."<br />";
		}
	    }
	  if ((($arrEquip[0][6] > $gwtbr || $arrEquip[11][6] > $gwtbr) || ($arrEquip[1][6] > $gwtbr && $arrEquip[6][6] > $gwtbr)) && ($arrEquip[0][0] || $arrEquip[1][0])) 
	    {
	      if ($arrEquip[1][0]) 
		{
		  $gwtbr++;
		  $zmeczenie += $arrEquip[1][4];
		}
	      if ($arrEquip[0][0]) 
		{
		  $zmeczenie += $arrEquip[0][4];
		}
	      if ($arrEquip[11][0])
		{
		  $zmeczenie += $arrEquip[11][4];
		}
	    }
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
	      $blnHit = FALSE;
	      if ((($arrEquip[0][6] > $gwtbr || $arrEquip[11][6] > $gwtbr) || ($arrEquip[1][6] > $gwtbr && $arrEquip[6][6] > $gwtbr)) && ($arrEquip[0][0] || $arrEquip[1][0])) 
		{
		  $gwtbr++;
		  $gatak++;
		  $enemy['hp'] -= $enemyhp;
		  $blnHit = TRUE;
		}
	      //Hit with spell
	      if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
		{
		  $lost_mana = ceil($mczar -> fields['poziom'] / 2.5);
		  $lost_mana = $lost_mana - (int)($player -> magic / 25);
		  if ($lost_mana < 1)
		    {
		      $lost_mana = 1;
		    }
		  $player -> mana = ($player -> mana - $lost_mana);
		  $gmagia++;
		  $enemy['hp'] -= $enemyhp;
		  $blnHit = TRUE;
		}
	      if ($times == 1 && $blnHit)
		{
		  $arrLocations = array('w tułów', 'w głowę', 'w kończynę');
		  $intHit = rand(0, 2);
		  $smarty->assign("Message", showcritical($arrLocations[$intHit], $strAtype, 'pve', $enemy['name']));
		  $smarty->display('error1.tpl');
		}
	      return $blnHit;
	    }
	  //Hit with weapon
	  if ((($arrEquip[0][6] > $gwtbr || $arrEquip[11][6] > $gwtbr) || ($arrEquip[1][6] > $gwtbr && $arrEquip[6][6] > $gwtbr)) && ($arrEquip[0][0] || $arrEquip[1][0])) 
	    {
	      $enemy['hp'] -= $stat['damage'];
	      if ($times == 1) 
		{
		  $arrLocations = array('w tułów', 'w głowę', 'w kończynę');
		  $intHit = rand(0, 2);
		  $strMessage = YOU_HIT." <b>".$enemy['name']."</b> ".$arrLocations[$intHit]." ".INFLICT." <b>".$stat['damage']."</b> ".DAMAGE."! (".$enemy['hp']." ".LEFT.")</font><br>";
		}
	      $gwtbr++;
	      if ($arrEquip[0][0]) 
		{
		  $zmeczenie += $arrEquip[0][4];
		} 
	      elseif ($arrEquip[1][0]) 
		{
		  $zmeczenie += $arrEquip[1][4];
		}
	      if ($arrEquip[11][0])
		{
		  $zmeczenie += $arrEquip[11][4];
		}
	      if ($stat['damage'] > 0) 
		{
		  $gatak++;
		}
	    }
	  //Hit with spell
	  if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
	    {
	      $pech = floor($player->magic - $mczar -> fields['poziom']);
	      if ($pech > 0) 
		{
		  $pech = 0;
		}
	      $pech += rand(1,100);
	      //Proper hit
	      if ($pech > 5) 
		{
		  $lost_mana = ceil($mczar -> fields['poziom'] / 2.5);
		  $lost_mana = $lost_mana - (int)($player -> magic / 25);
		  if ($lost_mana < 1)
		    {
		      $lost_mana = 1;
		    }
		  $player -> mana = ($player -> mana - $lost_mana);
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
		      $player->mana -= $mczar -> fields['poziom'];
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
		      $player->mana -= $mczar -> fields['poziom'];
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
		      $player->mana -= $mczar -> fields['poziom'];
		      $player->hp -= $intDamage;
		      if ($times == 1)
			{
			  $strMessage = "<b>".$player -> user."</b> próbował rzucić zaklęcie, ale eksplodowało ono w rękach, raniąc jego oraz wroga. Traci przez to ".$intDamage." punktów życia (".$player->hp." zostało), <b>".$enemy['name']."</b> otrzymuje ".$intDamage." obrażeń (".$enemy['hp']." zostało)<br />";
			}
		    }
		  if ($times == 1)
		    {
		      $smarty->assign("Message", $strMessage);
		      $smarty->display('error1.tpl');
		    }
		  return FALSE;
		}
	    }
	}
    }
  if (($times == 1) && (isset($strMessage)))
    {
      $smarty->assign("Message", $strMessage);
      $smarty->display('error1.tpl');
    }
  return TRUE;
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

    $arrEquip = $player -> equipment();
    $mczar = $db -> Execute("SELECT * FROM `czary` WHERE `status`='E' AND `gracz`=".$player -> id." AND `typ`='B'");
    $mczaro = $db -> Execute("SELECT * FROM `czary` WHERE `status`='E' AND `gracz`=".$player -> id." AND `typ`='O'");
    $premia = 0;
    $strName = $player->user;
    $player->user = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];
    $player->curstats($arrEquip);

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
        if ($player -> location == 'Góry') 
        {
	    $intRoll2 = rand(1, 100);
	    if ($intRoll2 < 25)
	      {
		$enemy1 = $db->SelectLimit("SELECT * FROM `monsters` WHERE `level`<=".$player->level." AND `location`='Altara' ORDER BY RAND()", 1);
	      }
	    elseif ($intRoll2 > 24 && $intRoll2 < 90)
	      {
		$enemy1 = $db->SelectLimit("SELECT * FROM `monsters` WHERE `level`<=".$player->level." AND `location`='Altara' ORDER BY `level` DESC", 1);
	      }
	    else
	      {
		$enemy1 = $db->SelectLimit("SELECT * FROM `monsters` WHERE `level`>=".$player->level." AND `location`='Altara' ORDER BY RAND()", 1);
	      }
        }
        if ($player -> location == 'Las') 
        {
	    $intRoll2 = rand(1, 100);
	    if ($intRoll2 < 25)
	      {
		$enemy1 = $db->SelectLimit("SELECT * FROM `monsters` WHERE `level`<=".$player->level." AND `location`='Ardulith' ORDER BY RAND()", 1);
	      }
	    elseif ($intRoll2 > 24 && $intRoll2 < 90)
	      {
		$enemy1 = $db->SelectLimit("SELECT * FROM `monsters` WHERE `level`<=".$player->level." AND `location`='Ardulith' ORDER BY `level` DESC", 1);
	      }
	    else
	      {
		$enemy1 = $db->SelectLimit("SELECT * FROM `monsters` WHERE `level`>=".$player->level." AND `location`='Ardulith' ORDER BY RAND()", 1);
	      }
        }
        $enemy = array("strength" => $enemy1 -> fields['strength'], 
                       "agility" => $enemy1 -> fields['agility'], 
                       "speed" => $enemy1 -> fields['speed'], 
                       "endurance" => $enemy1 -> fields['endurance'], 
                       "hp" => $enemy1 -> fields['hp'], 
                       "name" => $enemy1 -> fields['name'], 
                       "id" => $enemy1 -> fields['id'], 
                       "exp1" => $enemy1 -> fields['exp1'], 
                       "exp2" => $enemy1 -> fields['exp2'], 
                       "level" => $enemy1 -> fields['level']);
        $enemy1 -> Close();
    }
    if ($title == 'Arena Walk') 
    {
        if (!$arrEquip[0][0] && !$mczar -> fields['id'] && !$arrEquip[1][0]) 
        {
            error (E_WEAPON);
        }
        if (($arrEquip[0][0] && $mczar -> fields['id']) || ($arrEquip[1][0] && $mczar -> fields['id']) || ($arrEquip[0][0] && $arrEquip[1][0])) 
        {
            error (E_WEAPON_SPELL);
        }
        if ($arrEquip[1][0] && !$arrEquip[6][0]) 
        {
            error (E_QUIVER);
        }
        if (in_array($player -> clas, array('Wojownik', 'Rzemieślik', 'Złodziej', 'Barbarzyńca')) && $mczar -> fields['id']) 
        {
            error (E_SPELL);
        }
        if ($player -> clas == 'Mag' && $mczar -> fields['id'] && $player -> mana == 0) 
        {
            error (E_MANA);
        }
    }
    if ($arrEquip[2][0]) 
    {
        $premia = ($premia + $arrEquip[2][2]);
    }
    if ($arrEquip[4][0]) 
    {
        $premia = ($premia + $arrEquip[4][2]);
    }
    if ($arrEquip[5][0]) 
    {
        $premia = ($premia + $arrEquip[5][2]);
    }
    if ($arrEquip[3][0]) 
    {
        $premia = ($premia + $arrEquip[3][2]);
    }
    $strAtype = 'none';
    if ($arrEquip[0][0]) 
    {
        if ($arrEquip[0][3] == 'D') 
        {
            $arrEquip[0][2] = $arrEquip[0][2] + $arrEquip[0][8];
        }
        if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
        {
            $stat['damage'] = (($player -> strength + $arrEquip[0][2]) + $player -> level);
            $enemy['damage'] = ($enemy['strength'] - ($player -> level + $player -> cond + $premia));
        } 
            else 
        {
            $stat['damage'] = ($player -> strength + $arrEquip[0][2]);
            $enemy['damage'] = ($enemy['strength'] - ($player -> cond + $premia));
        }
        if ($player -> attack < 1) 
        {
            $krytyk = 1;
        }
        if ($player -> attack > 5) 
        {
            $kr = ceil($player -> attack / 100);
            $krytyk = (5 + $kr);
        } 
            else 
        {
            $krytyk = $player -> attack;
        }
	$strAtype = 'melee';
    }
    if ($arrEquip[11][0])
      {
	$stat['damage'] += (($arrEquip[11][2] + $player->strength) + $player->level);
      }
    if ($arrEquip[1][0]) 
    {
        $bonus = $arrEquip[1][2] + $arrEquip[6][2];
	if ($arrEquip[6][3] == 'D') 
        {
	  $bonus += $arrEquip[6][8];
	}
        $bonus2 = (($player  -> strength / 2) + ($player->agility / 2));
        if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
        {
            $stat['damage'] = (($bonus2 + $bonus) + $player -> level);
            $enemy['damage'] = ($enemy['strength'] - ($player -> level + $player -> cond + $premia));
        } 
            else 
        {
            $stat['damage'] = ($bonus2 + $bonus);
            $enemy['damage'] = ($enemy['strength'] - ($player -> cond + $premia));
        }
        if ($player -> shoot < 1) 
        {
            $krytyk = 1;
        }
        if ($player -> shoot > 5) 
        {
            $kr = ceil($player -> shoot / 100);
            $krytyk = (5 + $kr);
        } 
            else 
        {
            $krytyk = $player -> shoot;
        }
        if (!$arrEquip[6][0]) 
        {
            $stat['damage'] = 0;
        }
	$strAtype = 'ranged';
    }
    if ($mczar -> fields['id']) 
    {
        $stat['damage'] = ($mczar -> fields['obr'] * $player -> inteli) - (($mczar -> fields['obr'] * $player -> inteli) * ($arrEquip[3][4] / 100));
        if ($arrEquip[2][0]) 
        {
            $stat['damage'] = $stat['damage'] - ($stat['damage'] * ($arrEquip[2][4] / 100));
        }
        if ($arrEquip[4][0]) 
        {
            $stat['damage'] = $stat['damage'] - ($stat['damage'] * ($arrEquip[4][4] / 100));
        }
        if ($arrEquip[5][0]) 
        {
            $stat['damage'] = $stat['damage'] - ($stat['damage'] * ($arrEquip[5][4] / 100));
        }
        if ($arrEquip[7][0]) 
        {
            $intN = 6 - (int)($arrEquip[7][4] / 20);
            $intBonus = (10 / $intN) * $player -> level * rand(1, $intN);
            $stat['damage'] = $stat['damage'] + $intBonus;
        }
        if ($stat['damage'] < 0) 
        {
            $stat['damage'] = 0;
        }
        if ($player -> magic < 1) 
        {
            $krytyk = 1;
        }
        if ($player -> magic > 5) 
        {
            $kr = ceil($player -> magic / 100);
            $krytyk = (5 + $kr);
        } 
            else 
        {
            $krytyk = $player -> magic;
        }
	$strAtype = 'spell';
    }
    if ($mczaro -> fields['id']) 
    {
        $myczarobr = ($player -> wisdom * $mczaro -> fields['obr']) - (($mczaro -> fields['obr'] * $player -> wisdom) * ($arrEquip[3][4] / 100));
        if ($arrEquip[2][0]) 
        {
            $myczarobr = ($myczarobr - (($mczaro -> fields['obr'] * $player -> wisdom) * ($arrEquip[2][4] / 100)));
        }
        if ($arrEquip[4][0]) 
        {
            $myczarobr = ($myczarobr - (($mczaro -> fields['obr'] * $player -> wisdom) * ($arrEquip[4][4] / 100)));
        }
        if ($arrEquip[5][0]) 
        {
            $myczarobr = ($myczarobr - (($mczaro -> fields['obr'] * $player -> wisdom) * ($arrEquip[5][4] / 100)));
        }
        if ($arrEquip[7][0]) 
        {
            $intN = 6 - (int)($arrEquip[7][4] / 20);
            $intBonus = (10 / $intN) * $player -> level * rand(1, $intN);
            $myczarobr = ($myczarobr + $intBonus);
        }
        if ($myczarobr < 0) 
        {
            $myczarobr = 0;
        }
        $myobrona = ($myczarobr + $player -> cond + $premia);
        $enemy['damage'] = ($enemy['strength'] - $myobrona);
    }
    if ($player -> clas == 'Wojownik'  || $player -> clas == 'Barbarzyńca') 
    {
        $myunik = (($player->agility - $enemy['agility']) + $player -> level + $player -> miss);
        $eunik = (($enemy['agility'] - $player->agility) - ($player -> attack + $player -> level));
	if ($arrEquip[11][0])
	  {
	    $eunik -= ($player->attack / 5);
	  }
    }
    if ($player -> clas == 'Rzemieślnik' || $player -> clas == 'Złodziej') 
    {
        $myunik = ($player->agility - $enemy['agility'] + $player -> miss);
        $eunik = (($enemy['agility'] - $player->agility) - $player -> attack);
    }
    if ($player -> clas == 'Mag') 
    {
        $myunik = ($player->agility - $enemy['agility'] + $player -> miss);
        $eunik = (($enemy['agility'] - $player->agility) - ($player -> magic + $player -> level));
    }
    if (!isset($myunik) || $myunik < 1) 
    {
        $myunik = 1;
    }
    if (!isset($eunik) || $eunik < 1) 
    {
        $eunik = 1;
    }
    if ($arrEquip[1][0]) 
    {
        $eunik = $eunik * 2;
    }
    $gunik = 0;
    $gatak = 0;
    $gmagia = 0;
    $gwtbr = 0;
    $gwt = array(0,0,0,0);
    $armor = checkarmor($arrEquip[3][0], $arrEquip[2][0], $arrEquip[4][0], $arrEquip[5][0]);
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
    $intPldamage = $stat['damage'];
    $stat['damage'] = ($stat['damage'] - $enemy['endurance']);
    $rzut2 = (rand(1,($player -> level * 10)));
    $stat['damage'] = ($stat['damage'] + $rzut2);
    $intPldamage = $intPldamage + $rzut2;
    if ($stat['damage'] < 1) 
    {
        $stat['damage'] = 0 ;
    }
    if ($stat['damage'] > $enemyhp) 
    {
        $stat['damage'] = $enemyhp;
    }
    $rzut1 = (rand(1,($enemy['level'] * 10)));
    if (!isset($enemy['damage'])) 
    {
        $enemy['damage'] = ($enemy['strength'] - $player -> cond);
    }
    $enemy['damage'] = ($enemy['damage'] + $rzut1);
    if ($enemy['damage'] < 1) 
    {
        $enemy['damage'] = 1;
    }
    if ($player->speed < 1)
    {
        $player->speed = 1;
    }
    $stat['attackstr'] = ceil($player->speed / $enemy['speed']);
    if ($stat['attackstr'] > 5) 
    {
        $stat['attackstr'] = 5;
    }
    $enemy['attackstr'] = ceil($enemy['speed'] / $player->speed);
    if ($enemy['attackstr'] > 5) 
    {
        $enemy['attackstr'] = 5;
    }
    //Shield block chance
    $intBlock = 0;
    if ($arrEquip[5][0])
      {
	$intBlock = ceil($arrEquip[5][2] / 5);
	if ($intBlock > 20)
	  {
	    $intBlock = 20;
	  }
      }
    $smarty -> assign ("Message", "<ul><li><b>".$player -> user."</b> ".VERSUS." <b>".$enemy['name']."</b><br />");
    $smarty -> display ('error1.tpl');

    while ($player -> hp > 0 && $enemy['hp'] > 0 && $runda < 25) 
    {
        if ($zmeczenie > $player -> cond) 
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
        if ($player -> mana < $mczar -> fields['poziom']) 
        {
            $stat['damage'] = 0;
        }
        if ($player -> mana < $mczaro -> fields['poziom']) 
        {
            $enemy['damage'] = $enemy['strength'];
        }
        if ($arrEquip[0][0] && $gwtbr > $arrEquip[0][6]) 
        {
            $stat['damage'] = 0;
            $krytyk = 1;
        }
        if ($arrEquip[1][0] && ($gwtbr > $arrEquip[1][6] || $gwtbr > $arrEquip[6][6])) 
        {
            $stat['damage'] = 0;
            $krytyk = 1;
        }
        if ($arrEquip[1][0] && !$arrEquip[6][0]) 
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
        if ($stat['attackstr'] > $enemy['attackstr']) 
        {
            for ($i = 1;$i <= $stat['attackstr']; $i++) 
            {
                if ($enemy['hp'] > 0 && $player -> hp > 0) 
		  {
		    if (!playerattack($eunik, $gwtbr, $arrEquip, $mczar, $zmeczenie, $gatak, $stat, $enemy, $gmagia, $times, $intPldamage, $krytyk, $enemyhp, $strAtype))
		      {
			break;
		      }
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
		    monsterattack2($intMydodge, $zmeczenie, $gunik, $arrEquip, $enemy, $times, $armor, $mczaro, $gwt, $intBlock);
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
		    monsterattack2($intMydodge, $zmeczenie, $gunik, $arrEquip, $enemy, $times, $armor, $mczaro, $gwt, $intBlock);
		  }
            }
            for ($i = 1;$i <= $stat['attackstr']; $i++) 
            {
                if ($enemy['hp'] > 0 && $player -> hp > 0) 
		  {
                    if (!playerattack($eunik, $gwtbr, $arrEquip, $mczar, $zmeczenie, $gatak, $stat, $enemy, $gmagia, $times, $intPldamage, $krytyk, $enemyhp, $strAtype))
		      {
			break;
		      }
		  }
            }
        }
        $runda = ($runda + 1);
    }
    if ($player -> hp <= 0) 
      {
	if ($player->antidote == 'R')
	  {
	    $player->hp = 1;
	  }
	$db -> Execute("UPDATE `players` SET `antidote`='' WHERE `id`=".$player -> id);
        if ($title != 'Arena Walk') 
	  {
            loststat($player->id, $player->oldstats, 0, $enemy['name'], 0, $player->antidote);
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
      } 
    elseif ($runda > 24 && ($player -> hp > 0 && $enemy['hp'] > 0)) 
      {
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player -> user." ".EVENT1." ".$_POST['razy']." ".$enemy['name']." ".EVENT2."')");
        $smarty -> assign ("Message", "<br /><li><b>".B_RESULT1.": ");
        $smarty -> display ('error1.tpl');
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
        checkexp($player -> exp,$expgain,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'',0);
      }
    /**
     * Count gained dodge skill
     */
    $intDamount = 0;
    $intNewfib = 1;
    $intOldfib = 1;
    $intTempfib = 1;
    while ($intNewfib)
    {
        $gunik = $gunik - $intNewfib;
        if ($gunik < 0)
        {
            break;
        }
            else
        {
            $intDamount ++;
            if ($intNewfib == 1)
            {
                $intNewfib = 3;
            }
                else
            {
                $intTempfib = $intNewfib;
                $intNewfib = $intNewfib + $intOldfib;
                $intOldfib = $intTempfib;
            }
        }
    }
    gainability($player -> id, $player -> user, $intDamount, 0, $gmagia, $player -> mana, $player -> id, '');
    if ($arrEquip[0][0]) 
    {
        gainability($player -> id, $player -> user, 0, $gatak, 0, $player -> mana, $player -> id, 'weapon');
        lostitem($gwtbr, $arrEquip[0][6], YOU_WEAPON, $player -> id, $arrEquip[0][0], $player -> id, HAS_BEEN1, $player->level);
    }
    if ($arrEquip[11][0])
      {
	if (!$arrEquip[0][0])
	  {
	    gainability($player -> id, $player -> user, 0, $gatak, 0, $player -> mana, $player -> id, 'weapon');
	  }
	lostitem($gwtbr, $arrEquip[11][6], YOU_WEAPON, $player -> id, $arrEquip[11][0], $player -> id, HAS_BEEN1, $player->level);
      }
    if ($arrEquip[1][0]) 
    {
        gainability($player -> id, $player -> user, 0, $gatak, 0, $player -> mana, $player -> id, 'bow');
        lostitem($gwtbr, $arrEquip[1][6], YOU_WEAPON, $player -> id, $arrEquip[1][0], $player -> id, HAS_BEEN1, $player->level);
        lostitem($gwtbr, $arrEquip[6][6], YOU_QUIVER, $player -> id, $arrEquip[6][0], $player -> id, HAS_BEEN1, $player->level);
    }
    if ($arrEquip[3][0]) 
    {
      lostitem($gwt[0], $arrEquip[3][6], YOU_ARMOR, $player -> id, $arrEquip[3][0], $player -> id, HAS_BEEN1, $player->level);
    }
    if ($arrEquip[2][0]) 
    {
      lostitem($gwt[1], $arrEquip[2][6], YOU_HELMET, $player -> id, $arrEquip[2][0], $player -> id, HAS_BEEN1, $player->level);
    }
    if ($arrEquip[4][0]) 
    {
      lostitem($gwt[2], $arrEquip[4][6], YOU_LEGS, $player -> id, $arrEquip[4][0], $player -> id, HAS_BEEN2, $player->level);
    }
    if ($arrEquip[5][0]) 
    {
      lostitem($gwt[3], $arrEquip[5][6], YOU_SHIELD, $player -> id, $arrEquip[5][0], $player -> id, HAS_BEEN2, $player->level);
    }
    if ($player -> hp < 0) 
    {
        $player -> hp = 0;
    }
    if (($player->hp > 0) && ($player->autodrink != 'N'))
      {
	require_once("includes/functions.php");
	if ($player->autodrink == 'A')
	  {
	    drinkfew(0, 0, 'M');
	    drinkfew(0, 0, 'H');
	  }
	else
	  {
	    drinkfew(0, 0, $player->autodrink);
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
    $db->Execute("UPDATE `players` SET `hp`=".$player->hp.", `fight`=0, `bless`='', `blessval`=0, `energy`=`energy`-".$_POST['razy']." WHERE `id`=".$player->id);
    $player->user = $strName;
}
?>
