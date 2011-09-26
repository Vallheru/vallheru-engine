<?php
/**
 *   File functions:
 *   Functions to fight in PvP and fast fight PvM
 *
 *   @name                 : funkcje.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 26.09.2011
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
require_once("languages/".$player -> lang."/funkcje.php");

/**
 * Function check for monsters loot
 */
function monsterloot($arrNames, $arrChances, $intLevel)
{
  global $db;
  global $player;

  //No loot, exit
  if (count($arrNames) < 3)
    {
      return;
    }

  $fltChance = (float)100 / $intLevel;
  $fltRandom = (float)rand(0, 10000) / 100;
  
  //Bad luck, exit
  if ($fltRandom > $fltChance)
    {
      return;
    }
  
  //Check which component player found
  $intKey = -1;
  $intRoll = rand(1, 100);
  echo $intRoll."<br />";
  foreach ($arrChances as $intChance)
    {
      $intKey++;
      if ($intRoll <= $intChance)
	{
	  break;
	}
    }

  //Add component to player equipment
  $intPrice = ceil(($intLevel * 10) / $arrChances[$intKey]);
  $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `name`='".$arrNames[$intKey]."' AND `owner`=".$player->id." AND `status`='U' AND `type`='O' AND `minlev`=".$intLevel." AND `cost`=".$intPrice);
  if (!$objTest->fields['id'])
    {
      $db->Execute("INSERT INTO `equipment` (`owner`, `name`, `type`, `cost`, `minlev`, `amount`, `status`) VALUES(".$player->id.", '".$arrNames[$intKey]."', 'O', ".$intPrice.", ".$intLevel.", 1, 'U')");
    }
  else
    {
      $db->Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest->fields['id']);
    }
  $objTest->Close();
  print "<br />Ze zwłok potwora wyciągasz ".$arrNames[$intKey].".<br />";
}

/**
 * Function auto reload quivers
 */
function autofill($intPlayerid, $intArrowId, $intPlayer2)
{
  global $db;

  $objArrows = $db->Execute("SELECT * FROM `equipment` WHERE `id`=".$intArrowId);
  if ($objArrows->fields['wt'] == 20)
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
      $intAmount = 20 - $objArrows -> fields['wt'];
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
      $objNewArrows = $db->SelectLimit("SELECT * FROM `equipment` WHERE `owner`=".$intPlayerid." AND `type`='R' AND status='U'", 1);
      if (!$objNewArrows->fields['id'])
	{
	  if ($intPlayerid == $intPlayer2)
	    {
	      print "<br />Twój kołczan jest pusty a Ty nie masz strzał aby go uzupełnić!<br />";
	    }
	  return;
	}
      if ($objNewArrows->fields['wt'] > 20)
	{
	  $intAmount = 20;
	}
      else
	{
	  $intAmount = $objNewArrows->fields['wt'];
	}
      $db -> Execute("INSERT INTO `equipment` (`name`, `wt`, `power`, `status`, `type`, `owner`, `ptype`, `poison`) VALUES('".$objNewArrows->fields['name']."',".$intAmount.",".$objNewArrows->fields['power'].",'E','R',".$intPlayerid.", '".$objNewArrows->fields['ptype']."', ".$objNewArrows->fields['poison'].")");
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
function loststat($lostid,$strength,$agility,$inteli,$wytrz,$szyb,$wisdom,$winid,$winuser,$starter) 
{
    global $db;
    global $newdate;

    $number = rand(0,5);
    $values = array($strength,$agility,$inteli,$wytrz,$szyb,$wisdom);
    $stats = array('strength','agility','inteli','wytrz','szyb','wisdom');
    $name = array(STRENGTH,AGILITY,INTELIGENCE,CONDITION,SPEED,WISDOM);
    $lost = ($values[$number] / 200);
    $db -> Execute("UPDATE players SET ".$stats[$number]."=".$stats[$number]."-".$lost." WHERE id=".$lostid);
    $stat = $name[$number];
    if ($lostid == $starter) 
    {
        $attacktext = YOU_ATTACK;
    } 
        else 
    {
        $attacktext = YOU_ATTACKED;
    }
    if ($winid) 
    {
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$lostid.",'".$attacktext." ".YOU_LOSE." <b><a href=view.php?view=".$winid.">".$winuser."</a> ".ID.":".$winid."</b>. ".YOU_LOST." ".$lost." ".$stat."', ".$strDate.", 'B')") or die(E_LOG);
    } 
        else 
    {
        if (!isset($_POST['razy'])) 
        {
            $_POST['razy'] = 1;
        }
        print "<br /><b>".B_RESULT." <b>".$_POST['razy']." ".$winuser."</b>. ".YOU_LOST." ".$lost." ".$stat;
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
function lostitem($lostdur,$itemdur,$type,$player,$itemid,$player2,$lost) 
{
    global $db;

    $itemdur = ($itemdur - $lostdur);
    if ($itemdur < 1) 
    {
        $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$itemid);
        if ($type == YOU_QUIVER)
	  {
	    autofill($player, $itemid, $player2);
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
	  autofill($player, $itemid, $player2);
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
* Function count player's agility
*/
function checkagility($agility, $armor, $legs, $shield) 
{
    if ($armor > -1)
    {
        $intArmor = ($agility * ($armor / 100));
    }
        else
    {
        $intArmor = $armor;
    }
    if ($legs > -1)
    {
        $agi2 = ($agility * ($legs / 100));
    }
        else
    {
        $agi2 = $legs;
    }
    if ($shield > -1)
    {
        $agi3 = ($agility * ($shield / 100));
    }
        else
    {
        $agi3 = $shield;
    }
    $agi1 = ($agility - $intArmor);
    $newagi = ($agi1 - $agi2);
    $newagility = ($newagi - $agi3);
    return $newagility;
}

/**
* Function count player's speed
*/
function checkspeed($speed, $weapon, $bow) 
{
    $speed2 = ($speed + ($speed * ($weapon / 100)));
    $newspeed = ($speed2 + $bow);
    return $newspeed;
}

/**
 * Function made monster attack
 */
function monsterattack2($intMydodge, &$zmeczenie, &$gunik, $arrEquip, &$enemy, $times, $armor, $mczaro, &$gwt)
{
  global $player;
  global $smarty;
  global $number;

  $szansa = rand(1, 100);
  //Player dodge
  if ($intMydodge >= $szansa && $zmeczenie <= $player->cond && $szansa < 97) 
    {
      if ($times == 1) 
	{
	  $strMessage = YOU_DODGE." <b>".$enemy['name']."</b>!<br />";
	}
      $gunik++;
      $zmeczenie = ($zmeczenie + $arrEquip[3][4] + 1);
    } 
  //Monster hit
  else 
    {
      $player->hp -= $enemy['damage'];
      if ($times == 1) 
	{
	  $strMessage = "<b>".$enemy['name']."</b> ".ENEMY_HIT." <b>".$enemy['damage']."</b> .".DAMAGE."! (".$player -> hp." ".LEFT.")<br>";
	}
      if ($arrEquip[3][0] || $arrEquip[2][0] || $arrEquip[4][0] || $arrEquip[5][0]) 
	{
	  $efekt = rand(0, $number);
	  if ($armor[$efekt] == 'torso') 
	    {
	      $gwt[0]++;
	    }
	  if ($armor[$efekt] == 'head') 
	    {
	      $gwt[1]++;
	    }
	  if ($armor[$efekt] == 'legs') 
	    {
	      $gwt[2]++;
	    }
	  if ($armor[$efekt] == 'shield') 
	    {
	      $gwt[3]++;
	    }
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
function playerattack($eunik, &$gwtbr, $arrEquip, $mczar, &$zmeczenie, &$gatak, $stat, &$enemy, &$gmagia, $times, $intPldamage, $krytyk)
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
	      if (($arrEquip[0][6] > $gwtbr || ($arrEquip[1][6] > $gwtbr && $arrEquip[6][6] > $gwtbr)) && ($arrEquip[0][0] || $arrEquip[1][0])) 
		{
		  $strMessage = "<b>".$enemy['name']."</b> ".ENEMY_DODGE."<br />";
		}
	      if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
		{
		  $strMessage =  "<b>".$enemy['name']."</b> ".ENEMY_DODGE."<br />";
		}
	    }
	  if (($arrEquip[0][6] > $gwtbr || ($arrEquip[1][6] > $gwtbr && $arrEquip[6][6] > $gwtbr)) && ($arrEquip[0][0] || $arrEquip[1][0])) 
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
	      $enemy['hp'] = 0;
	      //Hit with weapon
	      if (($arrEquip[0][6] > $gwtbr || ($arrEquip[1][6] > $gwtbr && $arrEquip[6][6] > $gwtbr)) && ($arrEquip[0][0] || $arrEquip[1][0])) 
		{
		  $gwtbr++;
		  $gatak++;
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
		}
	      if ($times == 1)
		{
		  $smarty->assign("Message", "Jednym niezwykle celnym trafieniem powalasz ".$enemy['name']." na ziemię!<br />");
		  $smarty->display('error1.tpl');
		}
	      return FALSE;
	    }
	  //Hit with weapon
	  if (($arrEquip[0][6] > $gwtbr || ($arrEquip[1][6] > $gwtbr && $arrEquip[6][6] > $gwtbr)) && ($arrEquip[0][0] || $arrEquip[1][0])) 
	    {
	      $enemy['hp'] -= $stat['damage'];
	      if ($times == 1) 
		{
		  $strMessage = YOU_HIT." <b>".$enemy['name']."</b> ".INFLICT." <b>".$stat['damage']."</b> ".DAMAGE."! (".$enemy['hp']." ".LEFT.")</font><br>";
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
		      $strMessage = YOU_HIT." <b>".$enemy['name']."</b> ".INFLICT." <b>".$stat['damage']."</b> ".DAMAGE."! (".$enemy['hp']." ".LEFT.")</font><br>";
		    }
		  if ($stat['damage'] > 0) 
		    {
		      $gmagia++;
		    }
		}
	      else 
		{
		  $pechowy = rand(1,100);
		  if ($pechowy <= 70) 
		    {
		      if ($times == 1) 
			{
			  $strMessage = "<b>".$player -> user."</b> ".YOU_MISS1." <b>".$mczar -> fields['poziom']."</b> ".MANA.".<br />";
			}
		      $player->mana -= $mczar -> fields['poziom'];
		    }
		  if ($pechowy > 70 && $pechowy <= 90) 
		    {
		      if ($times == 1) 
			{
			  $strMessage = "<b>".$player -> user."</b> ".YOU_MISS2.".<br>";
			}
		      $player->mana = 0;
		    }
		  if ($pechowy > 90) 
		    {
		      if ($times == 1) 
			{
			  $strMessage = "<b>".$player -> user."</b> ".YOU_MISS3." ".$intPldamage.HP."!<br />";
			}
		      $player->hp -= $intPldamage;
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

    $arrEquip = $player -> equipment();
    $mczar = $db -> Execute("SELECT * FROM `czary` WHERE `status`='E' AND `gracz`=".$player -> id." AND `typ`='B'");
    $mczaro = $db -> Execute("SELECT * FROM `czary` WHERE `status`='E' AND `gracz`=".$player -> id." AND `typ`='O'");
    $premia = 0;
    $arrStat = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'cond', 'attack', 'shoot', 'miss', 'magic');

    /**
    * Add bless to stats
    */
    $objMybless = $db -> Execute("SELECT bless, blessval FROM players WHERE id=".$player -> id);
    if (!empty($objMybless -> fields['bless']))
    {
        $arrBless = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition', 'weapon', 'shoot', 'dodge', 'cast');
        $intKey = array_search($objMybless -> fields['bless'], $arrBless);
        $strStat = $arrStat[$intKey];
        $player -> $strStat = ($player -> $strStat + $objMybless -> fields['blessval']);
    }
    $objMybless -> Close();

    /**
     * Add bonus to stats from rings
     */
    if ($arrEquip[9][2])
    {
        $arrRings = array(AGILITY, STRENGTH, INTELIGENCE, R_WIS3, SPEED, CONDITION);
        $arrRingtype = explode(" ", $arrEquip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $strStat = $arrStat[$intKey];
        $player -> $strStat = $player -> $strStat + $arrEquip[9][2];
    }
    if ($arrEquip[10][2])
    {
        $arrRings = array(AGILITY, STRENGTH, INTELIGENCE, R_WIS3, SPEED, CONDITION);
        $arrRingtype = explode(" ", $arrEquip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $strStat = $arrStat[$intKey];
        $player -> $strStat = $player -> $strStat + $arrEquip[10][2];
    }

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
            $arrmonsters = array(2,3,6,7,16,17,22,23);
            $rzut2 = rand(0,7);
            $enemy1 = $db -> Execute("SELECT * FROM monsters WHERE id=".$arrmonsters[$rzut2]);
        }
        if ($player -> location == 'Las') 
        {
            $arrmonsters = array(1,4,11,13,14,19,22,28);
            $rzut2 = rand(0,7);
            $enemy1 = $db -> Execute("SELECT * FROM monsters WHERE id=".$arrmonsters[$rzut2]);
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
    $myagility = checkagility($player -> agility, $arrEquip[3][5], $arrEquip[4][5], $arrEquip[5][5]);
    $myspeed = checkspeed($player -> speed, $arrEquip[0][7], $arrEquip[1][7]);
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
    }
    if ($arrEquip[1][0]) 
    {
        $bonus = $arrEquip[1][2] + $arrEquip[6][2];
	if ($arrEquip[6][3] == 'D') 
        {
	  $bonus += $arrEquip[6][8];
	}
        $bonus2 = (($player  -> strength / 2) + ($myagility / 2));
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
        $myunik = (($myagility - $enemy['agility']) + $player -> level + $player -> miss);
        $eunik = (($enemy['agility'] - $myagility) - ($player -> attack + $player -> level));
    }
    if ($player -> clas == 'Rzemieślnik' || $player -> clas == 'Złodziej') 
    {
        $myunik = ($myagility - $enemy['agility'] + $player -> miss);
        $eunik = (($enemy['agility'] - $myagility) - $player -> attack);
    }
    if ($player -> clas == 'Mag') 
    {
        $myunik = ($myagility - $enemy['agility'] + $player -> miss);
        $eunik = (($enemy['agility'] - $myagility) - ($player -> magic + $player -> level));
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
    if ($myspeed < 1)
    {
        $myspeed = 1;
    }
    $stat['attackstr'] = ceil($myspeed / $enemy['speed']);
    if ($stat['attackstr'] > 5) 
    {
        $stat['attackstr'] = 5;
    }
    $enemy['attackstr'] = ceil($enemy['speed'] / $myspeed);
    if ($enemy['attackstr'] > 5) 
    {
        $enemy['attackstr'] = 5;
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
		    if (!playerattack($eunik, $gwtbr, $arrEquip, $mczar, $zmeczenie, $gatak, $stat, $enemy, $gmagia, $times, $intPldamage, $krytyk))
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
		    monsterattack2($intMydodge, $zmeczenie, $gunik, $arrEquip, $enemy, $times, $armor, $mczaro, $gwt);
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
		    monsterattack2($intMydodge, $zmeczenie, $gunik, $arrEquip, $enemy, $times, $armor, $mczaro, $gwt);
		  }
            }
            for ($i = 1;$i <= $stat['attackstr']; $i++) 
            {
                if ($enemy['hp'] > 0 && $player -> hp > 0) 
		  {
                    if (!playerattack($eunik, $gwtbr, $arrEquip, $mczar, $zmeczenie, $gatak, $stat, $enemy, $gmagia, $times, $intPldamage, $krytyk))
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
	    $db -> Execute("UPDATE `players` SET `antidote`='' WHERE `id`=".$player -> id);
        if ($title != 'Arena Walk') 
        {
            loststat($player -> id,$player -> strength,$player -> agility,$player -> inteli,$player -> cond,$player -> speed,$player -> wisdom,0,$enemy['name'],0);
        } 
            else 
        {
            $smarty -> assign ("Message", "</ul>".LOST_FIGHT."...<br />");
            $smarty -> display ('error1.tpl');
        }
        $db -> Execute("INSERT INTO events (text) VALUES('Gracz ".$player -> user." ".EVENT3." ".$_POST['razy']." ".$enemy['name']."')");
    } 
        elseif ($runda > 24 && ($player -> hp > 0 && $enemy['hp'] > 0)) 
    {
        $db -> Execute("INSERT INTO events (text) VALUES('Gracz ".$player -> user." ".EVENT1." ".$_POST['razy']." ".$enemy['name']." ".EVENT2."')");
        $smarty -> assign ("Message", "<br /><li><b>".B_RESULT1.": ");
        $smarty -> display ('error1.tpl');
    } 
        else 
    {
        $db -> Execute("UPDATE players SET credits=credits+".$goldgain." WHERE id=".$player -> id);
        $db -> Execute("INSERT INTO events (text) VALUES('Gracz ".$player -> user." ".EVENT." ".$_POST['razy']." ".$enemy['name']."')");
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
        lostitem($gwtbr, $arrEquip[0][6], YOU_WEAPON, $player -> id, $arrEquip[0][0], $player -> id, HAS_BEEN1);
    }
    if ($arrEquip[1][0]) 
    {
        gainability($player -> id, $player -> user, 0, $gatak, 0, $player -> mana, $player -> id, 'bow');
        lostitem($gwtbr, $arrEquip[1][6], YOU_WEAPON, $player -> id, $arrEquip[1][0], $player -> id, HAS_BEEN1);
        lostitem($gwtbr, $arrEquip[6][6], YOU_QUIVER, $player -> id, $arrEquip[6][0], $player -> id, HAS_BEEN1);
    }
    if ($arrEquip[3][0]) 
    {
        lostitem($gwt[0], $arrEquip[3][6], YOU_ARMOR, $player -> id, $arrEquip[3][0], $player -> id, HAS_BEEN1);
    }
    if ($arrEquip[2][0]) 
    {
        lostitem($gwt[1], $arrEquip[2][6], YOU_HELMET, $player -> id, $arrEquip[2][0], $player -> id, HAS_BEEN1);
    }
    if ($arrEquip[4][0]) 
    {
        lostitem($gwt[2], $arrEquip[4][6], YOU_LEGS, $player -> id, $arrEquip[4][0], $player -> id, HAS_BEEN2);
    }
    if ($arrEquip[5][0]) 
    {
        lostitem($gwt[3], $arrEquip[5][6], YOU_SHIELD, $player -> id, $arrEquip[5][0], $player -> id, HAS_BEEN2);
    }
    monsterloot($enemy['lootnames'], $enemy['lootchances'], $enemy['level']);
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
    $db->Execute("UPDATE `players` SET `hp`=".$player->hp.", `fight`=0, `bless`='', `blessval`=0 WHERE `id`=".$player->id);
}
?>
