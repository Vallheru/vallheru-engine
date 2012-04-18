<?php
/**
 *   File functions:
 *   Players' outposts and all functions related with these (except taxes).
 *
 *   @name                 : outposts.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 18.04.2012
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
* Page title
*/
$title = "Strażnica";

/**
* Add head and left side of page
*/
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/outposts.php");

/**
* Check player location - if player not in city, block page
*/
if($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (NOT_IN_CITY);
}

/**
* Assign variables to template
*/
$smarty -> assign(array("Message" => '', 
			"Result" => ''));

/**
* Function show equipment which may be added to veterans
*/
function equip($type) 
{
    global $player;
    global $db;
    global $arrid;
    global $arrname;
    global $arrpower;
    if ($type != 'I')
      {
	if ($type != 'W')
	  {
	    $armor = $db->Execute("SELECT `id`, `name`, `power`, `wt` FROM `equipment` WHERE `owner`=".$player->id." AND `type`='".$type."' AND `status`='U'");
	  }
	else
	  {
	    $armor = $db->Execute("SELECT `id`, `name`, `power`, `wt` FROM `equipment` WHERE `owner`=".$player->id." AND `type` IN ('W', 'B') AND `status`='U'");
	  }
      }
    else
      {
	$armor = $db->Execute("SELECT `id`, `name`, `power` FROM `equipment` WHERE `owner`=".$player->id." AND `type`='I' AND `status`='U' AND (`name` LIKE '%siły' OR `name` LIKE '%zręczności' OR `name` LIKE '%wytrzymałości' OR `name` LIKE '%szybkości')");
      }
    $arrid = array(0);
    $arrname = array(NOTHING);
    $arrpower = array(0);
    while (!$armor->EOF) 
      {
	if ($type == 'R')
	  {
	    if ($armor->fields['wt'] < 20)
	      {
		$armor->MoveNext();
		continue;
	      }
	  }
        $arrid[] = $armor->fields['id'];
        $arrname[] = $armor->fields['name'];
        $arrpower[] = ceil($armor->fields['power'] / 10);
        $armor->MoveNext();
      }
    $armor->Close();
}

/**
* Function to equip veterans
*/
function wear($eid, $vid, $type, $powertype) 
{
    global $player;
    global $db;
    if ($type != 'ring1' && $type != 'ring2')
      {
	$item1 = $db -> Execute("SELECT `id`, `name`, `power`, `amount`, `wt` FROM `equipment` WHERE `id`=".$eid." AND `owner`=".$player->id." AND `status`='U'");
      }
    else
      {
	$item1 = $db -> Execute("SELECT `id`, `name`, `power`, `amount` FROM `equipment` WHERE `id`=".$eid." AND `owner`=".$player->id." AND `status`='U' AND (`name` LIKE '%siły' OR `name` LIKE '%zręczności' OR `name` LIKE '%wytrzymałości' OR `name` LIKE '%szybkości')");
      }
    if (!$item1->fields['id'])
      {
	error(ERROR);
      }
    $item = array($item1 -> fields['name'], ceil($item1 -> fields['power'] / 10));
    $info = $item[0].I_POWER.$item[1].") ";
    if ($type != 'arrows')
      {
	if ($item1 -> fields['amount']  == 1) 
	  {
	    $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$eid." AND `owner`=".$player -> id);
	  } 
	else 
	  {
	    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`-1 WHERE `id`=".$eid." AND `owner`=".$player -> id);
	  }
      }
    else
      {
	if ($item1->fields['wt'] < 20)
	  {
	    error("Nie masz tylu strzał.");
	  }
	if ($item1->fields['wt'] == 20)
	  {
	    $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$eid." AND `owner`=".$player -> id);
	  } 
	else 
	  {
	    $db -> Execute("UPDATE `equipment` SET `wt`=`wt`-20 WHERE `id`=".$eid." AND `owner`=".$player -> id);
	  }
      }
    $item1 -> Close();
    $db -> Execute("UPDATE `outpost_veterans` SET `".$type."`='".$item[0]."', `".$powertype."`=".$item[1]." WHERE `id`=".$vid);
    return $info;
}

/**
* Function display amount of veterans, monsters and special buildings and count cost of special units
*/
function showspecials($table, $field) 
{
    global $out;
    global $db;
    global $smarty;
    global $maxarmy;
    global $cost1;
    global $numarmy;
    $query = $db -> Execute("SELECT count(`id`) FROM outpost_".$table." WHERE outpost=".$out -> fields['id']);
    $numarmy = $query -> fields['count(`id`)'];
    $query -> Close();
    $maxarmy = $out -> fields[$field] - $numarmy;
    $cost1 = $numarmy * 70;
    if ($numarmy) 
    {
        $army = $db -> Execute("SELECT * FROM outpost_".$table." WHERE outpost=".$out -> fields['id']);
        $arrname = array();
        $arrpower = array();
        $arrdefense = array();
        if ($table == 'veterans') 
        {
            $arrid = array();
        }
        $i = 0;
        while (!$army -> EOF) 
        {
            $arrname[$i] = $army -> fields['name'];
            if ($table == 'monsters') 
            {
                $arrpower[$i] = $army -> fields['power'];
                $arrdefense[$i] = $army -> fields['defense'];
            } 
                else 
            {
                $arrid[$i] = $army -> fields['id'];
                $arrpower[$i] = $army -> fields['wpower'] + 1;
		if (strpos($army->fields['weapon'], 'Łuk') !== FALSE || strpos($army->fields['weapon'], 'łuk') !== FALSE)
		  {
		    $arrpower[$i] += $army->fields['opower'];
		  }
                $arrdefense[$i] = $army -> fields['apower'] + $army -> fields['hpower'] + $army -> fields['lpower'] + 1;
		if ($army->fields['ring1'])
		  {
		    if (strpos($army->fields['ring1'], 'siły') !== FALSE || strpos($army->fields['ring1'], 'zręczności') !== FALSE)
		      {
			$arrpower[$i] += $army->fields['rpower1'];
		      }
		    else
		      {
			$arrdefense[$i] += $army->fields['rpower1'];
		      }
		  }
		if ($army->fields['ring2'])
		  {
		    if (strpos($army->fields['ring2'], 'siły') !== FALSE || strpos($army->fields['ring2'], 'zręczności') !== FALSE)
		      {
			$arrpower[$i] += $army->fields['rpower2'];
		      }
		    else
		      {
			$arrdefense[$i] += $army->fields['rpower2'];
		      }
		  }
            }
            $army -> MoveNext();
            $i = $i + 1;
        }
        $army -> Close();
        if ($table == 'monsters') 
        {
            $smarty -> assign(array("Mname" => $arrname, 
                "Mpower" => $arrpower, 
                "Mdefense" => $arrdefense, 
                "Mid" => 1));
        } 
            else 
        {
            $smarty -> assign(array("Vname" => $arrname, 
                "Vpower" => $arrpower, 
                "Vdefense" => $arrdefense, 
                "Vid" => $arrid));
        }
    } 
        else 
    {
        if ($table == 'monsters') 
        {
            $smarty -> assign("Mid", 0);
        } 
            else 
        {
            $smarty -> assign("Vid", 0);
        }
    }
}

/**
* Function count loss monsters and veterans in fight
*/
function lostspecials($table, $user, $arrid, $arrname)
{
    global $db;
    $i = 0;
    $info = '';
    if ($table == 'monsters') 
    {
        $type = MONSTER;
    } 
        else 
    {
        $type = VETERAN;
    }
    if (!$user) 
    {
        $lost = GC;
    } 
        else 
    {
        $lost = '';
    }
    foreach ($arrname as $name) 
    {
        $roll = rand(1,100);
        if ($roll < 6) 
        {
            $db -> Execute("DELETE FROM outpost_".$table." WHERE id=".$arrid[$i]);
            $info = $info.AND_LOST.$user.T_LOST.$lost." ".$type." ".$name."<br />";
        }
        $i = $i + 1;
    }
    return $info;
}

/**
* Function count attack and defense of outpost
*/
function outpoststats($outpost,$veteran,$monster) 
{
    global $attack;
    global $defense;
    global $arrvname;
    global $arrvid;
    global $arrmname;
    global $arrmid;
    global $db;
    $attack1 = ($outpost -> fields['warriors'] * 3) + $outpost -> fields['archers'] + ($outpost -> fields['catapults'] * 3);
    $defense1 = $outpost -> fields['warriors'] + ($outpost -> fields['archers'] * 3) + ($outpost -> fields['barricades'] * 3);
    $arrmid = array();
    $arrmname = array();
    $arrvid = array();
    $arrvname = array();
    $i = 0;
    while (!$monster -> EOF) 
    {
        $attack1 = $attack1 + $monster -> fields['power'];
        $defense1 = $defense1 + $monster -> fields['defense'];
        $arrmid[$i] = $monster -> fields['id'];
        $arrmname[$i] = $monster -> fields['name'];
        $monster -> MoveNext();
        $i = $i + 1;
    }
    $monster -> Close();
    $i = 0;
    while (!$veteran -> EOF) 
    {
        $attack1 = $attack1 + $veteran -> fields['wpower'] + 1;
	if (strpos($veteran->fields['weapon'], 'Łuk') !== FALSE || strpos($veteran->fields['weapon'], 'łuk') !== FALSE)
	  {
	    $attack1 += $veteran->fields['opower'];
	  }
        $defense1 = $defense1 + $veteran -> fields['apower']  + $veteran -> fields['hpower']  + $veteran -> fields['lpower'] + 1;
	if ($veteran->fields['ring1'])
	  {
	    if (strpos($veteran->fields['ring1'], 'siły') !== FALSE || strpos($veteran->fields['ring1'], 'zręczności') !== FALSE)
	      {
		$attack1 += $veteran->fields['rpower1'];
	      }
	    else
	      {
		$defense1 += $veteran->fields['rpower1'];
	      }
	  }
	if ($veteran->fields['ring2'])
	  {
	    if (strpos($veteran->fields['ring2'], 'siły') !== FALSE || strpos($veteran->fields['ring2'], 'zręczności') !== FALSE)
	      {
		$attack1 += $veteran->fields['rpower2'];
	      }
	    else
	      {
		$defense1 += $veteran->fields['rpower2'];
	      }
	  }
        $arrvid[$i] = $veteran -> fields['id'];
        $arrvname[$i] = $veteran -> fields['name'];
        $veteran -> MoveNext();
        $i = $i + 1;
    }
    $veteran -> Close();
    $bonus = (rand(-5,5) / 100);
    $fltAttack1 = 0;
    $fltDefense1 = 0;
    if ($outpost -> fields['morale'] >= 50)
    {
        if ($outpost -> fields['morale'] == 50)
        {
            $intBonus = 5;
        }
            else
        {
            $intBonus = floor(($outpost -> fields['morale'] - 50) / 75);
            $intBonus = ($intBonus * 5) + 5;
            if ($intBonus > 80)
            {
                $intBonus = 80;
            }
        }
    }
    if ($outpost -> fields['morale'] <= -50)
    {
        if ($outpost -> fields['morale'] == -50)
        {
            $intBonus = -5;
        }
            else
        {
            $intBonus = floor(($outpost -> fields['morale'] + 50) / 75);
            $intBonus = ($intBonus * 5) - 5;
            if ($intBonus < -75)
            {
                $intBonus = -75;
            }
        }
    }
    if (isset($intBonus))
    {
        $fltAttack1 = ($attack1 + ($attack1 * ($intBonus / 100)));
        $fltDefense1 = ($defense1 + ($defense1 * ($intBonus / 100)));
    }
        else
    {
        $fltAttack1 = $attack1;
        $fltDefense1 = $defense1;
    }
    $attackbonus = ($fltAttack1 * ($outpost -> fields['battack'] / 100));
    $defensebonus = ($fltDefense1 * ($outpost -> fields['bdefense'] / 100));
    $attack = $fltAttack1 + ($fltAttack1 * $bonus) + $attackbonus;
    $defense = $fltDefense1 + ($fltDefense1 * $bonus) + $defensebonus;
}

/**
* Function count losses in soldiers and machines attacking player and fatigue attacking soldiers
*/
function lostattacker($min,$max,$defense,$intDefenderlvl) 
{
    global $db;
    global $myout;
    global $arrarmy;
    global $j;
    global $arrlost;
    global $eattack;
    global $mydefense;
    for ($i = 0; $i < 3; $i ++) 
    {
        $field = $arrarmy[$i];
        if ($myout -> fields[$field]) 
        {
            $roll = ceil($myout -> fields[$field] * (rand($min,$max) / 100));
            $arrlost[$j] = $myout -> fields[$field] - $roll;
            if ($eattack > $mydefense) 
            {
                $roll1 = ceil($myout -> fields[$field] * 0.03);
                $arrlost[$j] = $arrlost[$j] - $roll1;
            } 
                else 
            {
                $roll1 = 0;
            }
            $bonus = ceil(($roll + $roll1) * ($myout -> fields['blost'] / 100));
            if ($bonus > ($roll + $roll1) || !$defense) 
            {
                $bonus = $roll + $roll1;
            }
            $arrlost[$j] = $arrlost[$j] + $bonus;
            if ($arrlost[$j] < 0) 
            {
                $arrlost[$j] = 0;
            }
            $db -> Execute("UPDATE outposts SET ".$arrarmy[$i]."=".$arrlost[$j]." WHERE id=".$myout -> fields['id']);
        } 
            else 
        {
            $arrlost[$j] = 0;
        }
        $j = $j + 1;
    }
    if ($myout -> fields['size'] < $intDefenderlvl && $myout -> fields['fatigue'] > 40)
    {
        $intFatigue = $myout -> fields['fatigue'] - 20;
    }
    if ($myout -> fields['fatigue'] <= 40 && $myout -> fields['fatigue'] > 30)
    {
        $intFatigue = 30;
    }
    if ($myout -> fields['fatigue']  <= 30)
    {
        $intFatigue = 25;
    }
    if ($myout -> fields['size'] >= $intDefenderlvl && $myout -> fields['fatigue'] > 40)
    {
        $intFatigue = $myout -> fields['fatigue'] - 15;
    }
    $db -> Execute("UPDATE outposts SET fatigue=".$intFatigue." WHERE id=".$myout -> fields['id']);
}

/**
* Function count losses in soldiers in defense player
*/
function lostdefender($min,$max,$defender=0) 
{
    global $db;
    global $enemy;
    global $arrarmy;
    global $j;
    global $arrlost;
    global $myout;
    if ($defender) 
    {
        $maxlost = 0;
        for ($i = 0; $i < 3; $i ++) 
        {
            $field = $arrarmy[$i];
            $maxlost = $maxlost + ($myout -> fields[$field] - $arrlost[$i]);
        }
        $deflost = 0;
    }
    for ($i = 0; $i < 4; $i ++) 
    {
        $field = $arrarmy[$i];
        if ($enemy -> fields[$field]) 
        {
            $roll = ceil($enemy -> fields[$field] * (rand($min,$max) / 100));
            $arrlost[$j] = $enemy -> fields[$field] - $roll;
            $bonus = ceil($roll * ($enemy -> fields['blost'] / 100));
            if ($bonus > $roll) 
            {
                $bonus = $roll;
            }
            $arrlost[$j] = $arrlost[$j] + $bonus;
            if ($defender) 
            {
                $deflost = $deflost + ($enemy -> fields[$field] - $arrlost[$j]);
            } 
                else 
            {
                $db -> Execute("UPDATE outposts SET ".$arrarmy[$i]."=".$arrlost[$j]." WHERE id=".$enemy -> fields['id']);
            }
        } 
            else 
        {
            $arrlost[$j] = 0;
        }
        $j = $j + 1;
    }
    if ($defender) 
    {
        $j = $j - 4;
        if ($deflost > $maxlost) 
        {
            $deflost = $deflost - $maxlost;
        }
        for ($i = 0; $i < 4; $i ++) 
        {
            $field = $arrarmy[$i];
            if ($enemy -> fields[$field]) 
            {
                $lost = $enemy -> fields[$field] - $arrlost[$j];
                if ($lost > $maxlost) 
                {
                    $lost = $maxlost;
                    $arrlost[$j] = $enemy -> fields[$field] - $lost;
                }
                $maxlost = $maxlost - $lost;
                $db -> Execute("UPDATE outposts SET ".$arrarmy[$i]."=".$arrlost[$j]." WHERE id=".$enemy -> fields['id']);
            } 
                else 
            {
                $arrlost[$j] = 0;
            }
            $j = $j + 1;
        }
    }
}

/**
* Function count gaining leadership by winning players
*/
function gainexpwin($leadership,$playerid) 
{
    global $db;
    global $myout;
    global $enemy;
    global $arrlost;
    $gainexp = (($myout -> fields['warriors'] - $arrlost[0]) + ($myout -> fields['archers'] - $arrlost[1]) + ($enemy -> fields['warriors'] - $arrlost[3]) + ($enemy -> fields['archers'] - $arrlost[4]) + $enemy -> fields['size']);
    if ($gainexp > 0)
    {
        $gainexp = ($gainexp / 200) / ceil($leadership);
        $gainexp = round($gainexp,"2");
    }
        else
    {
        $gainexp = 0;
    }
    if ($gainexp < 0.01) 
    {
        $gainexp = 0.01;
    }
    $db -> Execute("UPDATE players SET leadership=leadership+".$gainexp." WHERE id=".$playerid);
    return $gainexp;
}

/**
* Function count gaining leadership by lost player
*/
function gainexplost($leadership,$playerid) 
{
    global $db;
    global $myout;
    global $enemy;
    global $arrlost;
    $gainexp = ($myout -> fields['warriors'] - $arrlost[0]) + ($myout -> fields['archers'] - $arrlost[1]) + ($enemy -> fields['warriors'] - $arrlost[3]) + ($enemy -> fields['archers'] - $arrlost[4]);
    if ($gainexp > 0) 
    {
        $gainexp = ($gainexp / 200) / ceil($leadership);
    } 
        else 
    {
        $gainexp = 0;
    }
    $gainexp = round($gainexp,"2");
    if ($gainexp < 0.01) 
    {
        $gainexp = 0.01;
    }
    $db -> Execute("UPDATE players SET leadership=leadership+".$gainexp." WHERE id=".$playerid);
    return $gainexp;
}

/**
* Function to check what player can build with his current resources.
* Possible improvements: increase outpost's size, add beast's lair, add veteran's barrack.
*
* $strImprovement - what should be checked ('size', 'barracks' or 'fence')
* $intArg1 - first mineral required to improve outpost (size - platinum, lairs and barracks - meteor)
* $intArg2 - second mineral (size requires pine, lairs - crystal, barracks - adamantium)
* Function returns how many additional levels/lairs/barracks can be build.
*/
function checkresources($strImprovement, $intArg1, $intArg2)
{
    global $out;
    if ($strImprovement == 'size')
    {
    // Compute how much gold, pine and platinum can be spent.
    // Each level of outpost costs: (current_level + 2) * 250 gold, (current_level) pine and 10 platinum.
        $intDelta = 4 * $out -> fields['size'] * ($out -> fields['size'] + 3) + 9 + 4 * $out -> fields['gold'] / 125;
        $intMaxGold = floor ((sqrt ($intDelta) - 3) / 2 - $out -> fields['size']);
        $intMaxPlatinum = floor ($intArg1 / 10 );
        $intDelta = 4 * $out -> fields['size'] * ($out -> fields['size'] - 1) + 1 + 8 * $intArg2;
        $intMaxPine = floor ((sqrt ($intDelta) + 1 - 2 * $out -> fields['size']) / 2);
    // select lowest level
        $intMaxGold = ($intMaxGold > $intMaxPine) ? $intMaxPine : $intMaxGold;
        return ($intMaxGold > $intMaxPlatinum) ? $intMaxPlatinum: $intMaxGold;
    }

    if (($strImprovement != 'fence') && ($strImprovement != 'barracks'))    // invalid argument
    {
        return 0;
    }
    // Each lair costs: (no_of_lairs + 2) * 50 gold, (no_of_lairs + 1) * 5 crystal and (no_of_lairs + 1) meteor.
    // Each barrack costs: (no_of_barracks + 2) * 50 gold, (no_of_barracks + 1) * 5 adamantium and (no_of_barracks + 1) meteor.
    // Number of barracks + number of lairs must be less or equal to outpost's size / 4.
    $intDelta = 4 * $out -> fields[$strImprovement] * ($out -> fields[$strImprovement] + 3) + 9 + 4 * $out -> fields['gold'] / 25;
    $intMaxGold = floor ((sqrt ($intDelta) - 3) / 2 - $out -> fields[$strImprovement]);
    $intDelta = 4 * $out -> fields[$strImprovement] * ($out -> fields[$strImprovement] + 1) + 1 + 8 * $intArg1;
    $intMaxMeteor = floor ((sqrt ($intDelta) + 1 - 2 * $out -> fields[$strImprovement]) / 2) - 1;
    $intDelta = 4 * $out -> fields[$strImprovement] * ($out -> fields[$strImprovement] + 1) + 1 + 8 * $intArg2 / 5;
    $intArg2 = floor ((sqrt ($intDelta) + 1 - 2 * $out -> fields[$strImprovement]) / 2) - 1;
    $intMaxArg2 = min ($intMaxMeteor, $intArg2);
    $intMaxNumber = floor ( $out -> fields['size'] / 4) - $out -> fields['fence'] - $out -> fields['barracks'];
    // select lowest level
    $intMaxGold = ($intMaxGold > $intMaxMeteor) ? $intMaxMeteor : $intMaxGold;
    $intMaxGold = ($intMaxGold > $intMaxArg2) ? $intMaxArg2: $intMaxGold;
    return ($intMaxGold > $intMaxNumber) ? $intMaxNumber: $intMaxGold;
}

/**
* Get information about outposts from database
*/
$out = $db -> Execute("SELECT * FROM outposts WHERE owner=".$player -> id);

/**
* If player not have a outpost - give him a ability to buy
*/
if (!$out -> fields['id']) 
{
    if (isset ($_GET['action']) && $_GET['action'] == 'buy') 
    {
        $out = $db -> Execute("SELECT id FROM outposts WHERE owner=".$player -> id);
        if ($out -> fields['id']) 
        {
            error (YOU_BUY);
        } 
            else 
        {
            if ($player -> credits < 500) 
            {
                error (NO_MONEY);
            }    
                else 
            {
                $db -> Execute("UPDATE players SET credits=credits-500 WHERE id=".$player -> id);
                $db -> Execute("INSERT INTO outposts (owner) VALUES(".$player -> id.")");
                error (GO_TO);
            }
        }
    }
}

/**
* Outpost menu
*/
if (!isset($_GET['view']) && $out -> fields['id']) 
{
    $smarty -> assign(array("Outinfo" => OUT_INFO,
        "Amy" => A_MY,
        "Atax" => A_TAX,
        "Ashop" => A_SHOP,
        "Agold" => A_GOLD,
        "Aattack" => A_ATTACK,
        "Alist" => A_LIST,
        "Aguide" => A_GUIDE));
}

/**
* Add gold to outpost or take it from outpost
*/
if (isset ($_GET['view']) && $_GET['view'] == 'gold') 
{
    $smarty -> assign(array("Treasury" => $out -> fields['gold'],
                            "GoldInHand" => $player -> credits,
                            "Goldinfo" => GOLD_INFO,
                            "Goldcoins" => GOLD_COINS,
                            "Atake" => A_TAKE,
                            "Aadd" => A_ADD,
                            "Fromout" => FROM_OUT,
                            "Toout" => TO_OUT));
    /**
    * Get gold from outpost
    */
    if (isset ($_GET['step']) && $_GET['step'] == 'player') 
    {
        if (!isset($_POST['zeton']))
        {
            error(HOW_MANY);
        }
        integercheck($_POST['zeton']);
	checkvalue($_POST['zeton']);
        if ($_POST['zeton'] > $out -> fields['gold']) 
        {
            error(NO_MONEY);
        }
        $zmiana = floor($_POST['zeton'] / 2);
        $zmiana = (int)$zmiana;
        $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$zmiana." WHERE `id`=".$player -> id);
        $db -> Execute("UPDATE `outposts` SET `gold`=`gold`-".$_POST['zeton']." WHERE `owner`=".$player -> id);
        $smarty -> assign ("Message", YOU_CHANGE.$_POST['zeton'].GOLD_ON.$zmiana.GOLD_ON2);
    }
    /**
    * Add gold to outpost
    */
    if (isset ($_GET['step']) && $_GET['step'] == 'outpost') 
    {
        if (!isset($_POST['sztuki'])) 
        {
            error(HOW_MANY);
        }
        integercheck($_POST['sztuki']);
	checkvalue($_POST['sztuki']);
        if ($_POST['sztuki'] > $player -> credits) 
        {
            error(NO_MONEY);
        }
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$_POST['sztuki']." WHERE `id`=".$player -> id);
        $db -> Execute("UPDATE `outposts` SET `gold`=`gold`+".$_POST['sztuki']." WHERE `owner`=".$player -> id);
        $smarty -> assign ("Message", YOU_ADD.$_POST['sztuki'].GOLD_TO);
    }
}

/**
* Edit veterans (add equipment) and information about veterans
*/
if (isset($_GET['view']) && $_GET['view'] == 'veterans') 
{
    $query = $db -> Execute("SELECT count(`id`) FROM `outpost_veterans` WHERE `outpost`=".$out -> fields['id']);
    $numveterans = $query->fields['count(`id`)'];
    $query -> Close();
    if (!$numveterans) 
    {
        error(NO_VETERANS);
    }
    if (!isset($_GET['id'])) 
    {
        error(NO_VETERAN);
    }
    checkvalue($_GET['id']);
    $veteran = $db -> Execute("SELECT * FROM `outpost_veterans` WHERE `id`=".$_GET['id']);
    if ($veteran -> fields['outpost'] != $out -> fields['id']) 
    {
        error(NOT_YOUR);
    }
    $power = $veteran -> fields['wpower'] + 1;
    if (strpos($veteran->fields['weapon'], 'Łuk') !== FALSE || strpos($veteran->fields['weapon'], 'łuk') !== FALSE)
      {
	$power += $veteran->fields['opower'];
      }
    $defense = $veteran -> fields['apower'] + $veteran -> fields['hpower'] + $veteran -> fields['lpower'] + 1;
    equip('W');
    $arrwid = $arrid;
    $arrwname = $arrname;
    $arrwpower = $arrpower;
    if ($veteran->fields['arrows'])
      {
	$arrows = $veteran->fields['arrows'];
      }
    else
      {
	$arrows = NOTHING;
      }
    equip('R');
    $arroid = $arrid;
    $arroname = $arrname;
    $arropower = $arrpower;
    if ($veteran -> fields['armor']) 
      {
        $armor = $veteran -> fields['armor'];
      }
    else
      {
	$armor = NOTHING;
      }
    equip('A');
    $arraid = $arrid;
    $arraname = $arrname;
    $arrapower = $arrpower;
    if ($veteran -> fields['helm']) 
      {
	$helm = $veteran -> fields['helm'];
      } 
    else 
      {
        $helm = NOTHING;
      }
    equip('H');
    $arrhid = $arrid;
    $arrhname = $arrname;
    $arrhpower = $arrpower;
    if ($veteran -> fields['legs']) 
      {
        $legs = $veteran -> fields['legs'];
      }
    else 
      {
        $legs = NOTHING;
      }
    equip('L');
    $arrlid = $arrid;
    $arrlname = $arrname;
    $arrlpower = $arrpower;
    if ($veteran->fields['ring1'])
      {
	$ring1 = $veteran->fields['ring1'];
	if (strpos($veteran->fields['ring1'], 'siły') !== FALSE || strpos($veteran->fields['ring1'], 'zręczności') !== FALSE)
	  {
	    $power += $veteran->fields['rpower1'];
	  }
	else
	  {
	    $defense += $veteran->fields['rpower1'];
	  }
      }
    else
      {
	$ring1 = NOTHING;
      }
    equip('I');
    $arrrid = $arrid;
    $arrrname = $arrname;
    $arrrpower = $arrpower;
    if ($veteran->fields['ring2'])
      {
	$ring2 = $veteran->fields['ring2'];
	if (strpos($veteran->fields['ring2'], 'siły') !== FALSE || strpos($veteran->fields['ring2'], 'zręczności') !== FALSE)
	  {
	    $power += $veteran->fields['rpower1'];
	  }
	else
	  {
	    $defense += $veteran->fields['rpower1'];
	  }
      }
    else
      {
	$ring2 = NOTHING;
      }
    equip('I');
    $arrrid2 = $arrid;
    $arrrname2 = $arrname;
    $arrrpower2 = $arrpower;
    $smarty -> assign(array("Vname" => $veteran -> fields['name'],
			    "Wname" => $veteran -> fields['weapon'],
			    "Wpower" => $veteran -> fields['wpower'],
			    "Wname1" => $arrwname,
			    "Wpower1" => $arrwpower,
			    "Wid" => $arrwid,
			    "Rname1" => $ring1,
			    "Rname2" => $ring2,
			    "Rname12" => $arrrname,
			    "Rpower12" => $arrrpower,
			    "Rid1" => $arrrid,
			    "Rname22" => $arrrname2,
			    "Rpower22" => $arrrpower2,
			    "Rid2" => $arrrid2,
			    "Tring" => "Pierścień",
			    "Rpower1" => $veteran->fields['rpower1'],
			    "Rpower2" => $veteran->fields['rpower2'],
			    "Oname" => $arrows,
			    "Opower" => $veteran->fields['opower'],
			    "Oid" => $arroid,
			    "Oname1" => $arroname,
			    "Opower1" => $arropower,
			    "Tarrows" => "Strzały",
        "Aname" => $armor,
        "Apower" => $veteran -> fields['apower'],
        "Hname" => $helm,
        "Hpower" => $veteran -> fields['hpower'],
        "Lname" => $legs,
        "Lpower" => $veteran -> fields['lpower'],
        "Power" => $power,
        "Defense" => $defense,
        "Aid" => $arraid,
        "Aname1" => $arraname,
        "Apower1" => $arrapower,
        "Hid" => $arrhid,
        "Hname1" => $arrhname,
        "Hpower1" => $arrhpower,
        "Lid" => $arrlid,
        "Lname1" => $arrlname,
        "Lpower1" => $arrlpower,
        "Vid" => $_GET['id'],
        "Vname2" => V_NAME,
        "Vwep" => V_WEP,
        "Varmor" => V_ARMOR,
        "Vhelmet" => V_HELMET,
        "Vlegs" => V_LEGS,
        "Vattack" => V_ATTACK,
        "Vdefense" => V_DEFENSE,
        "Ipower" => I_POWER2,
        "Amodify" => A_MODIFY,
        "Nothing" => NOTHING));
    /**
    * Modify veterans equipment
    */
    if (isset($_GET['step']) && $_GET['step'] == 'modify') 
    {
        if (isset($_POST['armor']) && intval($_POST['armor']) > 0) 
        {
            $text1 = wear($_POST['armor'], $_GET['id'],'armor','apower');
            $text = YOU_ADD.$text1;
        }
        if (isset($_POST['helm']) && intval($_POST['helm']) > 0) 
        {
            $text1 = wear($_POST['helm'], $_GET['id'],'helm','hpower');
            $text = YOU_ADD.$text1;
        }
        if (isset($_POST['legs']) && intval($_POST['legs']) > 0) 
        {
            $text1 = wear($_POST['legs'], $_GET['id'],'legs','lpower');
            $text = YOU_ADD.$text1;
        }
	if (isset($_POST['weapon']) && intval($_POST['weapon']) > 0)
	  {
	    $text1 = wear($_POST['weapon'], $_GET['id'], 'weapon', 'wpower');
            $text = YOU_ADD.$text1;
	  }
	if (isset($_POST['ring1']) && intval($_POST['ring1']) > 0)
	  {
	    $text1 = wear($_POST['ring1'], $_GET['id'], 'ring1', 'rpower1');
            $text = YOU_ADD.$text1;
	  }
	if (isset($_POST['ring2']) && intval($_POST['ring2']) > 0)
	  {
	    $text1 = wear($_POST['ring2'], $_GET['id'], 'ring2', 'rpower2');
            $text = YOU_ADD.$text1;
	  }
	if (isset($_POST['arrows']) && intval($_POST['arrows']) > 0)
	  {
	    $text1 = wear($_POST['arrows'], $_GET['id'], 'arrows', 'opower');
            $text = YOU_ADD.$text1;
	  }
        if (isset($text1)) 
        {
            $smarty -> assign("Message", $text);
        }
    }
}

/**
* Informations about outpost and gaining leadership ability
*/
if (isset ($_GET['view']) && $_GET['view'] == 'myoutpost') 
{
    $maxtroops = ($out -> fields['size'] * 20) - $out -> fields['warriors'] - $out -> fields['archers'];
    $maxequips = ($out -> fields['size'] * 10) - $out -> fields['catapults'] - $out -> fields['barricades'];
    $cost = (($out -> fields['warriors'] * 7) + ($out -> fields['archers'] * 7)) + ($out -> fields['catapults'] * 14);
    $testability = $out -> fields['battack'] + $out -> fields['bdefense'] + $out -> fields['btax'] + $out -> fields['blost'] + $out -> fields['bcost'];
    $ability = floor($player -> leadership);
    if ($testability < $ability) 
    {
        $link = 'Y';
    } 
        else 
    {
        $link = '';
    }
    showspecials('monsters','fence');
    $maxmonsters = $maxarmy;
    $nummonsters = $numarmy;
    $maxfence = (floor($out -> fields['size'] / 4) - $out -> fields['barracks']);
    $maxbarracks = (floor($out -> fields['size'] / 4) - $out -> fields['fence']);
    $cost = $cost + $cost1;
    showspecials('veterans','barracks');
    $maxveterans = $maxarmy;
    $numveterans = $numarmy;
    $cost = $cost + $cost1;
    $bonus = ($cost * ($out -> fields['bcost'] / 100));
    $bonus = round($bonus, "0");
    $cost = $cost - $bonus;
    $fatigue = 100 - $out -> fields['fatigue'];
    if ($out -> fields['morale'] > -49 && $out -> fields['morale'] < 49)
    {
        $strMorale = MORALE2;
    }
    if ($out -> fields['morale'] < -49)
    {
        $strMorale = MORALE3;
    }
    if ($out -> fields['morale'] > 49)
    {
        $strMorale = MORALE1;
    }
    $smarty -> assign ( array("User" => $player -> user,
        "Size" => $out -> fields['size'],
        "Turns" => $out -> fields['turns'],
        "Gold" => $out -> fields['gold'],
        "Warriors" => $out -> fields['warriors'],
        "Barricades" => $out -> fields['barricades'],
        "Archers" => $out -> fields['archers'],
        "Catapults" => $out -> fields['catapults'],
        "Maxtroops" => $maxtroops,
        "Maxequip" => $maxequips,
        "Cost" => $cost,
        "Attack" => $out -> fields['battack'],
        "Defense" => $out -> fields['bdefense'],
        "Tax" => $out -> fields['btax'],
        "Lost" => $out -> fields['blost'],
        "Bcost" => $out -> fields['bcost'],
        "Link" => $link,
        "Fence" => $out -> fields['fence'],
        "Maxfence" => $maxfence,
        "Monster" => $nummonsters,
        "Maxmonster" => $maxmonsters,
        "Barracks" => $out -> fields['barracks'],
        "Maxbarracks" => $maxbarracks,
        "Veterans" => $numveterans,
        "Maxveterans" => $maxveterans,
        "Fatigue" => $fatigue,
        "Morale" => $out -> fields['morale'],
        "Moralename" => $strMorale,
        "Welcome" => WELCOME,
        "Outinfo" => OUT_INFO,
        "Landa" => LAND_A,
        "Ap" => AP,
        "Tsoldiers" => T_SOLDIERS,
        "Tarchers" => T_ARCHERS,
        "Tcatapults" => T_CATAPULTS,
        "Tforts" => T_FORTS,
        "Tlairs" => T_LAIRS,
        "Tmonsters" => T_MONSTERS,
        "Tbarracks" => T_BARRACKS,
        "Tveterans" => T_VETERANS,
        "Tfatigue" => T_FATIGUE,
        "Tcost" => T_COST,
        "Tmorale" => T_MORALE,
        "Tbonus" => T_BONUS,
        "Tattack" => T_ATTACK,
        "Tdefense" => T_DEFENSE,
        "Ttax" => T_TAX,
        "Tlosses" => T_LOSSES,
        "Tcostb" => T_COST_B,
        "Tfree" => T_FREE,
        "Tpower" => T_POWER,
        "Udefense" => U_DEFENSE,
        "Tmax" => T_MAX,
        "Tcostg" => T_COST_G,
        "Aadd" => A_ADD,
        "Tgoldcoins" => T_GOLD_COINS));
    /**
    * Gaining ability leadership
    */
    if (isset($_GET['step']) && $_GET['step'] == 'add') 
    {
        if ($_GET['ability'] != 'battack' && $_GET['ability'] != 'bdefense' && $_GET['ability'] != 'btax' && $_GET['ability'] != 'blost' && $_GET['ability'] != 'bcost') 
        {
            error(ERROR);
        }
        $testability = $out -> fields['battack'] + $out -> fields['bdefense'] + $out -> fields['btax'] + $out -> fields['blost'] + $out -> fields['bcost'];
        $ability = floor($player -> leadership);
        if ($testability >= $ability) 
        {
            error(NO_BONUS);
        }
        $field = $_GET['ability'];
        if ($out -> fields[$field] > 14) 
        {
            error(MAX_LEVEL);
        }
        $db -> Execute("UPDATE outposts SET ".$_GET['ability']."=".$_GET['ability']."+1 WHERE id=".$out -> fields['id']);
        $smarty -> assign("Message", YOU_ADD);
    }
}

/**
* Get tax from villages
*/
if (isset ($_GET['view']) && $_GET['view'] == 'taxes') 
{
    if ($out -> fields['turns'] < 1) 
    {
        error(NO_EN_AP);
    }
    $intArmy = $out -> fields['warriors'] + $out -> fields['archers'];
    if (!$intArmy) 
    {
        error(NO_SOLDIERS);
    }
    if (isset($_POST['amount']))
      {
	$intAmount = $_POST['amount'];
      }
    else
      {
	$intAmount = 0;
      }
    $smarty -> assign(array("Taxinfo" => TAX_INFO." Obecnie posiadasz <b>".$out->fields['turns']."</b> Punktów Ataku.",
			    "Asend" => A_SEND,
			    "Soldiers" => SOLDIERS,
			    "Amount" => $intAmount,
			    "Times" => TIMES));
    if (isset($_GET['step']) && $_GET['step'] == 'gain') 
    {
        if (!isset($_POST['amount'])) 
        {
            error(HOW_MANY);
        }
	checkvalue($_POST['amount']);
        if ($_POST['amount'] > $out -> fields['turns']) 
        {
            error (NO_AP);
        }
        $intGaingold = 0;
        for ($i = 0; $i < $_POST['amount']; $i++) 
        {
            $intGaingold += rand(1,5);
        }
        $intGaingold *= $intArmy;
        $fltBonus = ($intGaingold * ($out -> fields['btax'] / 100));
        $intBonus = round($fltBonus, "0");
        $intGaingold = (int)($intGaingold + $intBonus);
        $intFatigue = $out -> fields['fatigue'] + (10 * $_POST['amount']);
        if ($intFatigue > 100)
        {
            $intFatigue = 100;
        }
	$fltMorale = $out->fields['morale'] + ($_POST['amount'] / 10);
	if ($fltMorale > 10)
	  {
	    $fltMorale = 10;
	  }
        $db -> Execute("UPDATE `outposts` SET `fatigue`=".$intFatigue.", `gold`=`gold`+".$intGaingold.", `turns`=`turns`-".$_POST['amount'].", `morale`=".$fltMorale." WHERE `id`=".$out -> fields['id']);
        $smarty -> assign ("Message", YOU_ARMY.$_POST['amount'].TIMES_FOR.$intGaingold.GOLD_COINS);
    }
}

/**
* Buy army and new buildings to outpost
*/
if (isset ($_GET['view']) && $_GET['view'] == 'shop') 
{
    $dbMinerals = $db -> Execute ("SELECT `owner`, `pine`, `crystal`, `adamantium`, `meteor` FROM `minerals` WHERE owner=".$player -> id);
    $smarty -> assign (array("Shopinfo" => SHOP_INFO,
                             "Shopinfo2" => SHOP_INFO2,
                             "Shopinfo3" => SHOP_INFO3,
                             "Gold" => $out -> fields['gold'],
                             "Platinum" => $player -> platinum,
                             "Pine" => $dbMinerals -> fields['pine'],
                             "Crystal" => $dbMinerals -> fields['crystal'],
                             "Adamantium" => $dbMinerals -> fields['adamantium'],
                             "Meteor" => $dbMinerals -> fields['meteor'],
                             "Goldcoins" => GOLD_COINS,
                             "Platinumpcs" => PLATINUM_PIECES,
                             "Pinepcs" => PINE_PIECES,
                             "Crystalpcs" => CRYSTAL_PIECES,
                             "Adamantiumpcs" => ADAMANTIUM_PIECES,
                             "Meteorpcs" => METEOR_PIECES));
    $arrOutpostLevels = array();
    if ($intMaxLevel = checkresources ('size', $player -> platinum, $dbMinerals -> fields['pine']))
    {
        $intGoldSum = 0;
        $intPineSum = 0;
        for ($i = 1; $i <= $intMaxLevel; $i++)
        {
            $intGoldSum += ($i + $out -> fields['size'] + 1) * 250;
            $intPineSum += $i + $out -> fields['size'] - 1;
            $arrOutpostLevels[ $i ] = ($i + $out -> fields['size']).': '.$intGoldSum.GOLD_COINS.', '.($i * 10).PLATINUM_PIECES.', '.$intPineSum.PINE_PIECES;
        }
    }
    $arrLairLevels = array();
    if ($intMaxLair = checkresources ('fence', $dbMinerals -> fields['meteor'], $dbMinerals -> fields['crystal']))
    {
        $intMeteorSum = 0;
        for ($i = 1; $i <= $intMaxLair; $i++)
        {
            $intMeteorSum += $i + $out -> fields['fence'];
            $arrLairLevels[ $i ] = ($i + $out -> fields['fence']).': '.(50 * $intMeteorSum).GOLD_COINS.', '.$intMeteorSum.METEOR_PIECES.', '.(5 * $intMeteorSum).CRYSTAL_PIECES;
        }
    }
    $arrBarrackLevels = array();
    if ($intMaxBarracks = checkresources ('barracks', $dbMinerals -> fields['meteor'], $dbMinerals -> fields['adamantium']))
    {
        $intMeteorSum = 0;
        for ($i = 1; $i <= $intMaxBarracks; $i++)
        {
            $intMeteorSum += $i + $out -> fields['barracks'];
            $arrBarrackLevels[ $i ] = ($i + $out -> fields['barracks']).': '.(50 * $intMeteorSum).GOLD_COINS.', '.$intMeteorSum.METEOR_PIECES.', '.(5 * $intMeteorSum).ADAMANTIUM_PIECES;
        }
    }
    $strNolevelinfo = NO_LEVEL_INFO." ".(($out->fields['size'] + 2) * 250)." złota, 10 mithrilu, ".$out->fields['size']." sosny.";
    $strNolairinfo = NO_LAIR_INFO." ".(($out->fields['fence'] + 2) * 50)." złota, ".(($out->fields['fence'] + 1) * 5)." kryształów, ".($out->fields['fence'] + 1)." meteorytu.";
    $strNobarrackinfo = NO_BARRACK_INFO." ".(($out->fields['barracks'] + 2) * 50)." złota, ".(($out->fields['barracks'] + 1) * 5)." adamantium, ".($out->fields['barracks'] + 1)." meteorytu.";
    $smarty -> assign (array("OutpostDevelopment" => OUTPOST_DEVELOPMENT,
                             "MaxPossibleLevel" => $intMaxLevel,
                             "Level" => LEVEL,
                             "LevelInfo" => LEVEL_INFO,
                             "NoLevelInfo" => $strNolevelinfo,
                             "OutpostLevels" => $arrOutpostLevels,
                             "MaxPossibleLair" => $intMaxLair,
                             "Lair" => LAIR,
                             "LairInfo" => LAIR_INFO,
                             "NoLairInfo" => $strNolairinfo,
                             "LairLevels" => $arrLairLevels,
                             "MaxPossibleBarrack" => $intMaxBarracks,
                             "Barrack" => BARRACK,
                             "BarrackInfo" => BARRACK_INFO,
                             "NoBarrackInfo" => $strNobarrackinfo,
                             "BarrackLevels" => $arrBarrackLevels));
    $dbMinerals -> Close();
    $query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id);
    $numcore = $query -> RecordCount();
    $query -> Close();
    $query = $db -> Execute("SELECT id FROM outpost_monsters WHERE outpost=".$out -> fields['id']);
    $nummonsters = $query -> RecordCount();
    $query -> Close();
    if ($numcore && $nummonsters < $out -> fields['fence']) 
    {
        $freefence = 'Y';
        $core = $db -> Execute("SELECT id, name, power, defense FROM core WHERE owner=".$player -> id);
        $arrname = array();
        $arrpower = array();
        $arrdefense = array();
        $arrid = array();
        $i = 0;
        while (!$core -> EOF) 
        {
            $arrid[$i] = $core -> fields['id'];
            $arrname[$i] = $core -> fields['name'];
            $arrpower[$i] = ceil($core -> fields['power'] / 10);
            $arrdefense[$i] = ceil($core -> fields['defense'] / 10);
            $core -> MoveNext();
            $i = $i + 1;
        }
        $core -> Close();
        $smarty -> assign(array("Mid" => $arrid, 
            "Mname" => $arrname, 
            "Power" => $arrpower, 
            "Defense" => $arrdefense,
            "Aadd" => A_ADD,
            "Udefense" => U_DEFENSE,
            "Uattack" => U_ATTACK,
            "Fora" => FOR_A));
    } 
        else 
    {
        $freefence = '';
    }
    $query = $db -> Execute("SELECT id FROM equipment WHERE owner=".$player -> id." AND type='W' AND status='U'");
    $numwep1 = $query -> RecordCount();
    $query -> Close();
    $query = $db -> Execute("SELECT id FROM equipment WHERE owner=".$player -> id." AND type='B' AND status='U'");
    $numwep2 = $query -> RecordCount();
    $query -> Close();
    $query = $db -> Execute("SELECT id FROM outpost_veterans WHERE outpost=".$out -> fields['id']);
    $numveterans = $query -> RecordCount();
    $query -> Close();
    if ($numveterans < $out -> fields['barracks'] && ($numwep1 || $numwep2)) 
    {
        $freebarracks = 'Y';
        $weapon = $db -> Execute("SELECT id, name, power FROM equipment WHERE owner=".$player -> id." AND type='W' AND status='U'");
        $arrwname = array();
        $arrwpower = array();
        $arrwid = array();
        $i = 0;
        while (!$weapon -> EOF) 
        {
            $arrwid[$i] = $weapon -> fields['id'];
            $arrwname[$i] = $weapon -> fields['name'];
            $arrwpower[$i] = ceil($weapon -> fields['power'] / 10);
            $weapon -> MoveNext();
            $i = $i + 1;
        }
        $weapon = $db -> Execute("SELECT id, name, power FROM equipment WHERE owner=".$player -> id." AND type='B' AND status='U'");
        while (!$weapon -> EOF) 
        {
            $arrwid[$i] = $weapon -> fields['id'];
            $arrwname[$i] = $weapon -> fields['name'];
            $arrwpower[$i] = ceil($weapon -> fields['power'] / 10);
            $weapon -> MoveNext();
            $i = $i + 1;
        }
        $weapon -> Close();
	equip('R');
        $arroid = $arrid;
        $arroname = $arrname;
        $arropower = $arrpower;
	if (!isset($arrid[1])) 
        {
            $arroid[1] = 0;
            $arroname[1] = 0;
            $arropower[1] = 0;
        }
        equip('A');
        $arraid = $arrid;
        $arraname = $arrname;
        $arrapower = $arrpower;
	if (!isset($arrid[1])) 
        {
            $arraid[1] = 0;
            $arraname[1] = 0;
            $arrapower[1] = 0;
        }
        equip('H');
        $arrhid = $arrid;
        $arrhname = $arrname;
        $arrhpower = $arrpower;
	if (!isset($arrid[1])) 
        {
            $arrhid[1] = 0;
            $arrhname[1] = 0;
            $arrhpower[1] = 0;
        }
        equip('L');
        $arrlid = $arrid;
        $arrlname = $arrname;
        $arrlpower = $arrpower;
	if (!isset($arrid[1])) 
        {
            $arrlid[1] = 0;
            $arrlname[1] = 0;
            $arrlpower[1] = 0;
        }
	equip('I');
        $arrrid = $arrid;
        $arrrname = $arrname;
        $arrrpower = $arrpower;
	if (!isset($arrid[1])) 
        {
            $arrrid[1] = 0;
            $arrrname[1] = 0;
            $arrrpower[1] = 0;
        }
	equip('I');
        $arrrid2 = $arrid;
        $arrrname2 = $arrname;
        $arrrpower2 = $arrpower;
	if (!isset($arrid[1])) 
        {
            $arrrid2[1] = 0;
            $arrrname2[1] = 0;
            $arrrpower2[1] = 0;
        }
        $smarty -> assign(array("Wid" => $arrwid,
				"Tring" => "Pierścień",
				"Rid1" => $arrrid,
				"Rname1" => $arrrname,
				"Rpower1" => $arrrpower,
				"Rid2" => $arrrid2,
				"Rname2" => $arrrname2,
				"Rpower2" => $arrrpower2,
				"Tarrows" => "Strzały",
				"Oid" => $arroid,
				"Oname" => $arroname,
				"Opower" => $arropower,
            "Wname" => $arrwname,
            "Wpower" => $arrwpower,
            "Aid" => $arraid,
            "Aname" => $arraname,
            "Apower" => $arrapower,
            "Hid" => $arrhid,
            "Hname" => $arrhname,
            "Hpower" => $arrhpower,
            "Lid" => $arrlid,
            "Lname" => $arrlname,
            "Lpower" => $arrlpower,
            "Vname2" => V_NAME2,
            "Aadd" => A_ADD,
            "Uattack" => U_ATTACK,
            "Udefense" => U_DEFENSE,
            "Fora" => FOR_A,
            "Ipower2" => I_POWER2,
            "Varmor" => V_ARMOR,
            "Vhelmet" => V_HELMET,
            "Vlegs" => V_LEGS,
            "Nothing" => NOTHING));
    } 
        else 
    {
        $freebarracks = '';
    }
    $arrTypes = array("'warriors'", "'archers'", "'catapults'", "'barricades'");
    $arrTypes2 = array("warriors", "archers", "catapults", "barricades");
    $objReserves = $db->Execute("SELECT `setting`, `value` FROM `settings` WHERE `setting` IN (".implode(',', $arrTypes).")");
    $arrReserves = array(0, 0, 0, 0);
    while(!$objReserves->EOF)
      {
	$intKey = array_search($objReserves->fields['setting'], $arrTypes2);
	$arrReserves[$intKey] = $objReserves->fields['value'];
	$objReserves->MoveNext();
      }
    $objReserves->Close();
    $maxtroops = ($out -> fields['size'] * 20) - $out -> fields['warriors'] - $out -> fields['archers'];
    $maxtroops = floor (($maxtroops > $out -> fields['gold'] / 25)? $out -> fields['gold'] / 25 : $maxtroops);
    if ($maxtroops == 1)
      {
	$strMaxtroops = "żołnierza";
      }
    else
      {
	$strMaxtroops = "żołnierzy";
      }
    $maxequips = ($out -> fields['size'] * 10) - $out -> fields['catapults'] - $out -> fields['barricades'];
    $maxequips = floor (($maxequips > $out -> fields['gold'] / 35)? $out -> fields['gold'] / 35 : $maxequips);
    if ($maxequips == 1)
      {
	$strMaxequips = "machinę lub fortyfikację";
      }
    else
      {
	$strMaxequips = "machin lub fortyfikacji";
      }
    $smarty -> assign (array("ArmyDevelopment" => ARMY_DEVELOPMENT,
                             "Maxtroops" => $maxtroops,
                             "Maxequips" => $maxequips,
			     "Ttroops"=> $strMaxtroops,
			     "Tequips" => $strMaxequips,
                             "Abuy" => A_BUY,
                             "Bsoldiers" => B_SOLDIERS,
                             "Barchers" => B_ARCHERS,
                             "Bmachines" => B_MACHINES,
                             "Bforts" => B_FORTS,
                             "Awarriors" => $arrReserves[0],
                             "Aarchers" => $arrReserves[1],
                             "Acatapults" => $arrReserves[2],
                             "Abarricades" => $arrReserves[3],
                             "Buyall" => BUY_ALL,
                             "Management" => MANAGEMENT,
                             "Fence" => $freefence,
                             "Barracks" => $freebarracks,
                             "NoLair" => NO_LAIR,
                             "NoBarracks" => NO_BARRACKS));
    /**
    * Add veterans to outpost
    */
    if (isset($_GET['buy']) && $_GET['buy'] == 'v') 
    {
        if ($out -> fields['gold'] < 2000) 
        {
            error(NO_MONEY);
        }
        $query = $db -> Execute("SELECT count(`id`) FROM `outpost_veterans` WHERE `outpost`=".$out -> fields['id']);
        $numveterans = $query->fields['count(`id`)'];
        $query->Close();
        if ($numveterans >= $out -> fields['barracks']) 
        {
            error(NO_BARRACKS);
        }
        $_POST['vname'] = htmlspecialchars($_POST['vname'], ENT_QUOTES);
        if (empty($_POST['vname'])) 
        {
            error(NO_NAME);
        }
        $objQuery = $db -> Execute("SELECT name FROM outpost_veterans WHERE outpost=".$out -> fields['id']);
        while (!$objQuery -> EOF)
        {
            if ($_POST['vname'] == $objQuery -> fields['name'])
            {
                error(NAME_TAKEN);
            }
            $objQuery -> MoveNext();
        }
        $objQuery -> Close();
        $strName = $db -> qstr($_POST['vname'], get_magic_quotes_gpc());
        $db -> Execute("INSERT INTO outpost_veterans (`outpost`, `name`) VALUES(".$out -> fields['id'].", ".$strName.")");
        $vetid = $db -> Execute("SELECT `id` FROM `outpost_veterans` WHERE `outpost`=".$out -> fields['id']." AND `name`=".$strName);
        $text = '';
        if (intval($_POST['weapon']) > 0) 
        {
            $text1 = wear($_POST['weapon'], $vetid -> fields['id'],'weapon','wpower');
            $text = $text.$text1;
        }
        if (isset($_POST['armor']) && intval($_POST['armor']) > 0) 
        {
            $text1 = wear($_POST['armor'], $vetid -> fields['id'],'armor','apower');
            $text = $text.$text1;
        }
        if (isset($_POST['helm']) && intval($_POST['helm']) > 0) 
        {
            $text1 = wear($_POST['helm'], $vetid -> fields['id'],'helm','hpower');
            $text = $text.$text1;
        }
        if (isset($_POST['legs']) && intval($_POST['legs']) > 0) 
        {
            $text1 = wear($_POST['legs'], $vetid -> fields['id'],'legs','lpower');
            $text = $text.$text1;
        }
	if (isset($_POST['ring1']) && intval($_POST['ring1']) > 0) 
	  {
            $text1 = wear($_POST['ring1'], $vetid -> fields['id'], 'ring1', 'rpower1');
            $text = $text.$text1;
	  }
	if (isset($_POST['ring2']) && intval($_POST['ring2']) > 0) 
	  {
            $text1 = wear($_POST['ring2'], $vetid -> fields['id'], 'ring2', 'rpower2');
            $text = $text.$text1;
	  }
	if (isset($_POST['arrows']) && intval($_POST['arrows']) > 0) 
	  {
            $text1 = wear($_POST['arrows'], $vetid -> fields['id'], 'arrows', 'opower');
            $text = $text.$text1;
	  }
        $vetid -> Close();
        $db -> Execute("UPDATE outposts SET `gold`=`gold`-2000 WHERE `id`=".$out -> fields['id']);
        $smarty -> assign("Message", YOU_ADD.$_POST['vname'].FOR_A2.$text.'<a href=outposts.php?view=shop>'.A_REFRESH.'</a>');
    }
    /**
    * Buy barracks to outpost
    */
    if (isset($_GET['buy']) && $_GET['buy'] == 'r') 
    {
        if (!isset ($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] < 1)
        {
            error(ERROR);
        }
        if (floor($out -> fields['size'] / 4) - $out -> fields['fence'] <= $out -> fields['barracks'])
        {
            error(MAX_BAR);
        }
        $intNeededMeteor = $_POST['amount'] * ($out -> fields['barracks'] + ($_POST['amount'] + 1) / 2);
        if (($intNeededMeteor * 50 > $out -> fields['gold']) || ($intNeededMeteor * 5> $dbMinerals -> fields['adamantium']) ||
            ($intNeededMeteor > $dbMinerals -> fields['meteor']))
        {
            error (NO_MONEY.' '.YOU_NEED.': '.($intNeededMeteor * 50).' '.GOLD_COINS.', '.$intNeededMeteor.' '.METEOR_PIECES. ', '.($intNeededMeteor * 5).' '.ADAMANTIUM_PIECES);
        }
        $db -> Execute('UPDATE `outposts` SET `gold`=`gold`-'.($intNeededMeteor * 50).', `barracks`=`barracks`+'.$_POST['amount'].' WHERE `id`='.$out -> fields['id']);
        $db -> Execute('UPDATE `minerals` SET `meteor`=`meteor`-'.$intNeededMeteor.', `adamantium`=`adamantium`-'.($intNeededMeteor * 5).' WHERE `owner`='.$out -> fields['owner']);
        $smarty -> assign ("Message", YOU_ADD.' <b>'.($_POST['amount'] + $out -> fields['barracks']).'</b> (+'.$_POST['amount'].') '.YOU_PAID.': '.($intNeededMeteor * 50).' '.GOLD_COINS.', '.$intNeededMeteor.                 ' '.METEOR_PIECES.', '.($intNeededMeteor * 5).' '.ADAMANTIUM_PIECES.'. <a href=outposts.php?view=shop>'.A_REFRESH.'</a>');
    }

    /**
    * Add cores to outpost
    */
    if (isset($_GET['buy']) && $_GET['buy'] == 'm') 
    {
        if ($out -> fields['gold'] < 2000) 
        {
            error(NO_MONEY);
        }
	checkvalue($_POST['army']);
        $query = $db -> Execute("SELECT count(`id`) FROM `outpost_monsters` WHERE `outpost`=".$out -> fields['id']);
        $nummonsters = $query->fields['count(`id`)'];
        $query -> Close();
        if ($nummonsters >= $out -> fields['fence']) 
        {
            error(NO_LAIR);
        }
        $core = $db -> Execute("SELECT name, power, defense, owner FROM core WHERE id=".$_POST['army']);
        if ($core -> fields['owner'] != $player -> id) 
        {
            error(NOT_YOUR);
        }
        $power = ceil($core -> fields['power'] / 10);
        $defense = ceil($core -> fields['defense'] / 10);
        $db -> Execute("INSERT INTO outpost_monsters (outpost, name, power, defense) VALUES(".$out -> fields['id'].",'".$core -> fields['name']."',".$power.",".$defense.")");
        $db -> Execute("DELETE FROM core WHERE id=".$_POST['army']);
        $db -> Execute("UPDATE outposts SET gold=gold-2000 WHERE id=".$out -> fields['id']);
        $smarty -> assign("Message", YOU_ADD.$core -> fields['name'].WITH_P.$power.AND_D.$defense.IT_COST.' <a href=outposts.php?view=shop>'.A_REFRESH.'</a>');
        $core -> Close();
    }
    /**
    * Buy lair to outpost
    */
    if (isset($_GET['buy']) && $_GET['buy'] == 'f') 
    {
        if (!isset ($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] < 1)
        {
            error(ERROR);
        }
        if (floor($out -> fields['size'] / 4) - $out -> fields['barracks'] <= $out -> fields['fence'])
        {
            error("Nie masz wolnego miejsca dla kolejnych legowisk bestii. Rozbuduj najpierw strażnicę.");
        }
        $intNeededMeteor = $_POST['amount'] * ($out -> fields['fence'] + ($_POST['amount'] + 1) / 2);
        if (($intNeededMeteor * 50 > $out -> fields['gold']) || ($intNeededMeteor * 5> $dbMinerals -> fields['crystal']) ||
            ($intNeededMeteor > $dbMinerals -> fields['meteor']))
        {
            error (NO_MONEY.' '.YOU_NEED.': '.($intNeededMeteor * 50).' '.GOLD_COINS.', '.$intNeededMeteor.' '.METEOR_PIECES.', '.($intNeededMeteor * 5).' '.CRYSTAL_PIECES);
        }
        $db -> Execute('UPDATE `outposts` SET `gold`=`gold`-'.($intNeededMeteor * 50).', `fence`=`fence`+'.$_POST['amount'].' WHERE `id`='.$out -> fields['id']);
        $db -> Execute('UPDATE `minerals` SET `meteor`=`meteor`-'.$intNeededMeteor.', `crystal`=`crystal`-'.($intNeededMeteor * 5).' WHERE `owner`='.$out -> fields['owner']);
        $smarty -> assign ("Message", YOU_ADD.' <b>'.($_POST['amount'] + $out -> fields['fence']).'</b> (+'.$_POST['amount'].') '.YOU_PAID.': '.($intNeededMeteor * 50).' '.GOLD_COINS.', '.$intNeededMeteor.' '.METEOR_PIECES.', '.($intNeededMeteor * 5).' '.CRYSTAL_PIECES.'. <a href=outposts.php?view=shop>'.A_REFRESH.'</a>');
    }
    /**
    * Buy land for outpost
    */
    if (isset ($_GET['buy']) && $_GET['buy'] == 's') 
    {
        if (!isset ($_POST['amount']))
        {
            error(ERROR);
        }
	checkvalue($_POST['amount']);
        $intNeededGold = 125 * $_POST['amount'] * (2 * $out -> fields['size'] + $_POST['amount'] + 3);
        $intNeededPine = $_POST['amount'] * ($out -> fields['size'] + ($_POST['amount'] - 1) / 2);
        if (($intNeededGold > $out -> fields['gold']) || ($intNeededPine > $dbMinerals -> fields['pine']) || ($_POST['amount'] * 10 > $player -> platinum))
	  {
            error (NO_MONEY.' '.YOU_NEED.': '.$intNeededGold.' '.GOLD_COINS.', '.($_POST['amount'] * 10).' '.PLATINUM_PIECES.', '.$intNeededPine.' '.PINE_PIECES);
	  }
        $db -> Execute('UPDATE `outposts` SET `gold`=`gold`-'.$intNeededGold.', `size`=`size`+'.$_POST['amount'].' WHERE `id`='.$out -> fields['id']);
        $db -> Execute('UPDATE `players` SET `platinum`=`platinum`-'.($_POST['amount'] * 10).' WHERE `id`='.$out -> fields['owner']);
        $db -> Execute('UPDATE `minerals` SET `pine`=`pine`-'.$intNeededPine.' WHERE `owner`='.$out -> fields['owner']);
        $smarty -> assign ("Message", YOU_ADD.' <b>'.($_POST['amount'] + $out -> fields['size']).'</b> (+'.$_POST['amount'].'). '.YOU_PAID.': '.$intNeededGold.' '.GOLD_COINS.', '.($_POST['amount'] * 10).                           ' '.PLATINUM_PIECES.', '.$intNeededPine.' '.PINE_PIECES.'. <a href=outposts.php?view=shop>'.A_REFRESH.'</a>');
    }
    /**
    * Buy army and machines to outpost (single operation)
    */
    if ((isset($_GET['buy']) && $_GET['buy'] ==  'all') && !isset($_POST['buyall'])) 
    {
        if (isset($_POST['buy0']))
        {
            $intKey = 0;
        }
            elseif (isset($_POST['buy1']))
        {
            $intKey = 1;
        }
            elseif (isset($_POST['buy2']))
        {
            $intKey = 2;
        }
            elseif (isset($_POST['buy3']))
        {
            $intKey = 3;
        }
        $strArmy = "army".$intKey;
        if (!isset($_POST[$strArmy])) 
        {
            error(HOW_MANY);
        }
	checkvalue($_POST[$strArmy]);
        $arrCost = array(25, 25, 35, 35);
        $cost = ($_POST[$strArmy] * $arrCost[$intKey]);
        if ($cost > $out -> fields['gold']) 
        {
            error(NO_MONEY);
        }
        if ($intKey < 2)
        {
            $max = ($out -> fields['size'] * 20) - $out -> fields['warriors'] - $out -> fields['archers'];
        }
            else
        {
            $max = ($out -> fields['size'] * 10) - $out -> fields['barricades'] - $out -> fields['catapults'];
        }
        $arrArmyname = array('warriors', 'archers', 'barricades', 'catapults');
        $arrErrortext = array(NO_SOLDIERS, NO_ARCHERS, NO_FORTS, NO_MACHINES);
        $objArmy = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='".$arrArmyname[$intKey]."'");
        if ($objArmy -> fields['value'] < $_POST[$strArmy])
        {
            error($arrErrortext[$intKey]);
        }
        $objArmy -> Close();
        $arrMessagetext = array(SOLDIERS, ARCHERS, FORTS, MACHINES);
        if ($_POST[$strArmy] > $max) 
        {
            error(TOO_MUCH.$arrMessagetext[$intKey].ENLARGE.$arrMessagetext[$intKey].".");
        }
        $db -> Execute("UPDATE `outposts` SET `gold`=`gold`-".$cost.", `".$arrArmyname[$intKey]."`=`".$arrArmyname[$intKey]."`+".$_POST[$strArmy]." WHERE `id`=".$out -> fields['id']);
        $objArmy = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='".$arrArmyname[$intKey]."'");
        $intArmy = $objArmy -> fields['value'] - $_POST[$strArmy];
        $objArmy -> Close();
        $db -> Execute("UPDATE `settings` SET `value`='".$intArmy."' WHERE `setting`='".$arrArmyname[$intKey]."'");
        $smarty -> assign ("Message", YOU_ADD.$_POST[$strArmy]."</b> ".$arrMessagetext[$intKey].TO_OUTPOST.$cost.'</b>'.GOLD_COINS.' <a href=outposts.php?view=shop>'.A_REFRESH.'</a>');
    }

    /**
    * Buy army and machines to outpost (all in one)
    */
    if ((isset($_GET['buy']) && $_GET['buy'] == 'all') && isset($_POST['buyall']))
    {
        $intCost = 0;
        $arrArmyname = array('warriors', 'archers', 'barricades', 'catapults');
        $arrArmyamount = array(0, 0, 0, 0);
        $intSoldiers = 0;
        $intBarricades = 0;
        for ($i = 0; $i < 4; $i++)
        {
            $strArmy = "army".$i;
            if (!isset($_POST[$strArmy]) || !is_numeric($_POST[$strArmy])) 
            {
                $_POST[$strArmy] = 0;
            }
	    $_POST[$strArmy] = intval($_POST[$strArmy]);
            if ($_POST[$strArmy] < 0) 
            {
                error(ERROR);
            }
            if ($i < 2)
            {
                $intMax = ($out -> fields['size'] * 20) - $out -> fields['warriors'] - $out -> fields['archers'] - $intSoldiers;
                $intSoldiers = $intSoldiers + $_POST[$strArmy];
            }
                else
            {
                $intMax = ($out -> fields['size'] * 10) - $out -> fields['barricades'] - $out -> fields['catapults'] - $intBarricades;
                $intBarricades = $intBarricades + $_POST[$strArmy];
            }
            if ($intMax < 0)
            {
                $intMax = 0;
            }
            if ($_POST[$strArmy] > $intMax)
            {
                $_POST[$strArmy] = $intMax;
            }
            $objArmy = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='".$arrArmyname[$i]."'");
            $arrArmyamount[$i] = $objArmy -> fields['value'];
            $objArmy -> Close();
            if ($arrArmyamount[$i] < $_POST[$strArmy])
            {
                $_POST[$strArmy] = $arrArmyamount[$i];
            }
            $arrCost = array(25, 25, 35, 35);
            $intCost = $intCost + ($_POST[$strArmy] * $arrCost[$i]);
            if ($intCost > $out -> fields['gold']) 
            {
                error(NO_MONEY);
            }
        }
        $db -> Execute("UPDATE `outposts` SET `gold`=`gold`-".$intCost.", `warriors`=`warriors`+".$_POST['army0'].", `archers`=`archers`+".$_POST['army1'].", `barricades`=`barricades`+".$_POST['army2'].", `catapults`=`catapults`+".$_POST['army3']." WHERE `id`=".$out -> fields['id']);
        for ($i = 0; $i < 4; $i++)
        {
            $strArmy = "army".$i;
            $intArmy = $arrArmyamount[$i] - $_POST[$strArmy];
            $db -> Execute("UPDATE `settings` SET `value`='".$intArmy."' WHERE `setting`='".$arrArmyname[$i]."'");
        }
        $strMessage = YOU_BUY.$_POST['army0']."</b> ".SOLDIERS."<br /><b>".$_POST['army1']."</b> ".ARCHERS."<br /><b>".$_POST['army2']."</b> ".FORTS."<br /><b>".$_POST['army3']."</b> ".MACHINES.YOU_SPEND.$intCost.'</b>'.GOLD_COINS.' <a href=outposts.php?view=shop>'.A_REFRESH.'</a>';
        $smarty -> assign("Message", $strMessage);
    }
}

/**
* Outposts list by levels from slevel to elevel
*/
if (isset ($_GET['view']) && $_GET['view'] == 'listing') 
  {
    $smarty -> assign(array("Ashow" => A_SHOW,
                            "Froml" => FROM_L,
                            "Tol" => TO_L,
                            "Outid" => OUT_ID,
                            "Outsize" => OUT_SIZE,
                            "Outowner" => OUT_OWNER,
                            "Outattack" => OUT_ATTACK,
                            "Aattack" => A_ATTACK,
			    "Outmin" => ceil($out->fields['size'] / 2),
			    "Outmax" => ($out->fields['size'] * 2)));
    if (isset ($_GET['step']) && $_GET['step'] == 'list') 
    {
        if (!isset($_POST['slevel']))
        {
            error(NO_SLEVEL);
        }
	checkvalue($_POST['slevel']);
	checkvalue($_POST['elevel']);
        if ($_POST['slevel'] > $_POST['elevel']) 
        {
            error (ERROR);
        }
        $op = $db -> Execute("SELECT `id`, `size`, `owner` FROM `outposts` WHERE `size`>=".$_POST['slevel']." AND `size`<=".$_POST['elevel']." AND `id`!=".$out -> fields['id']." AND `attacks`<3 ORDER BY `size` DESC");
        $arrid = array();
        $arrsize = array();
        $arrowner = array();
        $arrPlayer = array();
        while (!$op -> EOF) 
        {
            $arrid[] = $op -> fields['id'];
            $objName = $db -> Execute("SELECT `user`, `tribe` FROM `players` WHERE `id`=".$op -> fields['owner']);
            $arrPlayer[] = $arrTags[$objName->fields['tribe']][0].' '.$objName -> fields['user'].' '.$arrTags[$objName->fields['tribe']][1];
            $objName -> Close();
            $arrsize[] = $op -> fields['size'];
            $arrowner[] = $op -> fields['owner'];
            $op -> MoveNext();
        }
        $op -> Close();
        $smarty -> assign(array("Oid" => $arrid, 
                                "Size" => $arrsize, 
                                "Owner" => $arrowner,
                                "Ownername" => $arrPlayer));
    }
}

/**
* Battle of outposts
*/
if (isset ($_GET['view']) && $_GET['view'] == 'battle') 
{
    $smarty -> assign(array("Battleinfo" => BATTLE_INFO,
			    "Outid" => OUT_ID,
			    "Pid" => "ID właściciela",
			    "Amounta" => AMOUNT_A,
			    "Tor" => "lub",
			    "Aattack" => A_ATTACK));
    /**
    * Assign id attacked outpost
    */
    if (isset($_GET['oid'])) 
    {
        $smarty -> assign("Id", $_GET['oid']);
    } 
        else 
    {
        $smarty -> assign("Id", 0);
    }
    if (isset($_GET['pid']))
      {
	$smarty->assign("Pid2", $_GET['pid']);
      }
    else
      {
	$smarty->assign("Pid2", 0);
      }
    /**
    * Attack outpost
    */
    if (isset ($_GET['action']) && $_GET['action'] == 'battle') 
    {
        if (!isset($_POST['amount'])) 
        {
            $_POST['amount'] = 1;
        }
        if ($out -> fields['fatigue'] <= 25)
        {
            error(TOO_FAT);
        }
	checkvalue($_POST['amount']);
        if ($out -> fields['turns'] < $_POST['amount']) 
        {
            error (NO_AP);
        }
        if ($out -> fields['warriors'] < 0 || $out -> fields['archers'] < 0) 
        {
            error (ERROR);
        }
        if (!$out -> fields['warriors'] && !$out -> fields['archers']) 
        {
            error (NO_ARMY);
        }
        if (isset($_GET['oid'])) 
        {
            $_POST['oid'] = $_GET['oid'];
        }
	$_POST['oid'] = intval($_POST['oid']);
	if (isset($_GET['pid'])) 
        {
            $_POST['pid'] = $_GET['pid'];
        }
	$_POST['pid'] = intval($_POST['pid']);
	if ($_POST['pid'] <= 0 && $_POST['oid'] <= 0)
	  {
	    error("Nie wybrałeś celu ataku!");
	  }
	if ($player -> hp <= 0)
	  {
	    error(YOU_DEAD);
	  }
	if ($_POST['oid'] > 0)
	  {
	    if ($_POST['oid'] == $out->fields['id']) 
	      {
		error (ITS_YOUR);
	      }
	    $enemy = $db -> Execute("SELECT * FROM `outposts` WHERE `id`=".$_POST['oid']);
	  }
	elseif ($_POST['pid'] > 0)
	  {
	    if ($_POST['pid'] == $player->id)
	      {
		error("Nie możesz atakować sam siebie.");
	      }
	    $enemy = $db -> Execute("SELECT * FROM `outposts` WHERE `owner`=".$_POST['pid']);
	  }
	if (!$enemy -> fields['id']) 
	  {
	    error (NO_OUT);
	  }
        $myout = $db -> Execute("SELECT * FROM outposts WHERE id=".$out -> fields['id']);
        $mymonsters = $db -> Execute("SELECT id, name, power, defense FROM outpost_monsters WHERE outpost=".$out -> fields['id']);
        $emonsters = $db -> Execute("SELECT id, name, power, defense FROM outpost_monsters WHERE outpost=".$_POST['oid']);
        $myveterans = $db -> Execute("SELECT * FROM outpost_veterans WHERE outpost=".$out -> fields['id']);
        $eveterans = $db -> Execute("SELECT * FROM outpost_veterans WHERE outpost=".$_POST['oid']);
        $defenduser = $db -> Execute("SELECT user, leadership FROM players WHERE id=".$enemy -> fields['owner']);
        if ($_POST['amount'] > 3 || $enemy -> fields['attacks'] > 2)
        {
            error(TOO_MUCH_A);
        }
        if ($out -> fields['fatigue'] <= 25)
        {
            error(TOO_FAT2);
        }
        /**
        * Make few attacks
        */
        for ($k = 0; $k < $_POST['amount']; $k++) 
        {  
            /**
            * Count attack and defense attacker outpost
            */
            outpoststats($myout,$myveterans,$mymonsters);
            $myattack = $attack;
            $mydefense = $defense;
            $arrmymid = $arrmid;
            $arrmymname = $arrmname;
            $arrmyvid = $arrvid;
            $arrmyvname = $arrvname;
            /**
            * Count attack and defense defender outpost
            */
            outpoststats($enemy,$eveterans,$emonsters);
            $eattack = $attack;
            $edefense = $defense;
            $arremid = $arrmid;
            $arremname = $arrmname;
            $arrevid = $arrvid;
            $arrevname = $arrvname;
            /**
            * If attacker win
            */
            if ($myattack > $edefense) 
            {
                $arrlost = array();
                $arrarmy = array('warriors', 'archers', 'catapults', 'barricades');
                $j = 0;
                /**
                * Losses attacker army
                */
                lostattacker(0, 8, $edefense, $enemy -> fields['size']);
                /**
                * Losses defender army
                */
                lostdefender(25,35);
                /**
                * Losses defender gold
                */
                if ($enemy -> fields['gold']) 
                {
                    $lostgold = ceil($enemy -> fields['gold'] / 10);
                    $db -> Execute("UPDATE outposts SET gold=gold-".$lostgold." WHERE id=".$enemy -> fields['id']);
                } 
                    else 
                {
                    $lostgold = 0;
                }
                /**
                * Gain gold by attacker
                */
                $gaingold = $lostgold + ((($myout -> fields['warriors'] - $arrlost[0]) + ($myout -> fields['archers'] - $arrlost[1]) + ($enemy -> fields['warriors'] - $arrlost[3]) + ($enemy -> fields['archers'] - $arrlost[4])) * 10) + ((($enemy -> fields['warriors'] - $arrlost[3]) + ($enemy -> fields['archers'] - $arrlost[4])) * 6);
                $roll = rand(1,100);
                if ($roll < 6) 
                {
                    $gaingold = $gaingold + ($gaingold * ($roll / 100));
                }
                $db -> Execute("UPDATE outposts SET gold=gold+".$gaingold." WHERE id=".$out -> fields['id']);
                /**
                * Count exp gained by attacker
                */
                $gainexp1 = gainexpwin($player -> leadership, $player -> id);
                /**
                * Count exp gained by defender
                */
                $gainexp = gainexplost($defenduser -> fields['leadership'], $enemy -> fields['owner']);
                /**
                * Create info about fight
                */
                $arrname = array(SOLDIERS, ARCHERS, MACHINES, FORTS);
                $arrmessage[$k] = YOU_ATTACK.$defenduser -> fields['user'].AND_WIN.$myattack.DEFENSE.$edefense.YOU_GAIN.$gaingold.GOLD_COINS.$gainexp1.IN_LEADER;
                for ($i = 0; $i < 3; $i ++) 
                {
                    $field = $arrarmy[$i];
                    $lost = $myout -> fields[$field] - $arrlost[$i];
                    $arrmessage[$k] = $arrmessage[$k]."- ".$lost." ".$arrname[$i]."<br />";
                }
                $arrmessage[$k] = $arrmessage[$k].ENEMY_LOST;
                $arrlog = array();
                for ($i = 3; $i < 7; $i ++) 
                {
                    $num = $i - 3;
                    $field = $arrarmy[$num];
                    $arrlog[$num] = $enemy -> fields[$field] - $arrlost[$i];
                    $arrmessage[$k] = $arrmessage[$k]."- ".$arrlog[$num]." ".$arrname[$num]."<br />";
                }
                /**
                * Modify morale of attacker and defender
                */
                $db -> Execute("UPDATE `outposts` SET `morale`=`morale`+7.5 WHERE `id`=".$myout -> fields['id']);
                $db -> Execute("UPDATE `outposts` SET `morale`=`morale`-10 WHERE `id`=".$enemy -> fields['id']);
                /**
                * Add event in log defender player
                */
                $strDate = $db -> DBDate($newdate);
                $strMessage = HE_ATTACK.$lostgold.L_GOLD.$arrlog[0]." ".SOLDIERS.", ".$arrlog[1]." ".ARCHERS.", ".$arrlog[2]." ".MACHINES.L_AND.$arrlog[3]." ".FORTS.". Zdobywasz ".$gainexp." w umiejętności Dowodzenie.";
            } 
                else 
            {
                /**
                * If win defender player
                */
                $arrlost = array();
                $arrarmy = array('warriors', 'archers', 'catapults', 'barricades');
                $j = 0;
                /**
                * Count losses attacker player
                */
                lostattacker(25, 35, $edefense, $enemy -> fields['size']);
                /**
                * Count losses defender player
                */
                lostdefender(0,8,1);
                /**
                * Count gaining exp by defender player
                */
                $gainexp1 = gainexpwin($defenduser -> fields['leadership'], $enemy -> fields['owner']);
                /**
                * Count gaining exp by attacker player
                */
                $gainexp = gainexplost($player -> leadership, $player -> id);
                /**
                * Create info about fight
                */
                $arrname = array(SOLDIERS, ARCHERS, MACHINES, FORTS);
                $arrmessage[$k] = YOU_ATTACK.$defenduser -> fields['user'].BUT_FAIL.$myattack.DEFENSE.$edefense.YOU_GAIN.$gainexp.IN_LEADER;
                for ($i = 0; $i < 3; $i ++) 
                {
                    $field = $arrarmy[$i];
                    $lost = $myout -> fields[$field] - $arrlost[$i];
                    $arrmessage[$k] = $arrmessage[$k]."- ".$lost." ".$arrname[$i]."<br />";
                }
                $arrmessage[$k] = $arrmessage[$k].ENEMY_LOST;
                $arrlog = array();
                for ($i = 3; $i < 7; $i ++) 
                {
                    $num = $i - 3;
                    $field = $arrarmy[$num];
                    $arrlog[$num] = $enemy -> fields[$field] - $arrlost[$i];
                    $arrmessage[$k] = $arrmessage[$k]."- ".$arrlog[$num]." ".$arrname[$num]."<br />";
                }
                /**
                * Modify morale attacker and defender army
                */
                $db -> Execute("UPDATE `outposts` SET `morale`=`morale`-10 WHERE `id`=".$myout -> fields['id']);
                $db -> Execute("UPDATE `outposts` SET `morale`=`morale`+7.5 WHERE `id`=".$enemy -> fields['id']);
                $strMessage = HE_ATTACK2.$arrlog[0]." ".SOLDIERS.", ".$arrlog[1]." ".ARCHERS.", ".$arrlog[2]." ".MACHINES.L_AND.$arrlog[3]." ".FORTS.". Zdobywasz ".$gainexp1." w umiejętności Dowodzenie.";
            }
            /**
            * Count losses in monsters of attacker
            */
            $arrmessage[$k] = $arrmessage[$k].lostspecials('monsters', '', $arrmymid, $arrmymname);
            /**
            * Count losses in monsters of defender
            */
	    $strLost = lostspecials('monsters', $defenduser -> fields['user'], $arremid, $arremname);
            /**
            * Count losses in veterans of attacker
            */
            $$arrmessage[$k] = $arrmessage[$k].lostspecials('veterans', '', $arrmyvid, $arrmyvname);
            /**
            * Count losses in veterans of defender
            */
	    $strLost .= lostspecials('veterans', $defenduser -> fields['user'], $arrevid, $arrevname);
            $$arrmessage[$k] .= $strLost;
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$enemy -> fields['owner'].",'".L_PLAYER." <b><a href=\"view.php?view=".$player -> id."\">".$player -> user."</a></b>".L_ID."<b>".$player -> id."</b>".$strMessage." ".str_replace("<br />", " ", $strLost)."', ".$strDate.", 'O')") or die($db->ErrorMsg());
            /**
            * Add attack to attacks limit
            */
            $db -> Execute("UPDATE outposts SET attacks=attacks+1 WHERE id=".$enemy -> fields['id']);
            /**
            * Check again info about army all players
            */
	    if ($_POST['oid'] > 0)
	      {
		$enemy = $db -> Execute("SELECT * FROM `outposts` WHERE `id`=".$_POST['oid']);
	      }
	    elseif ($_POST['pid'] > 0)
	      {
		$enemy = $db -> Execute("SELECT * FROM `outposts` WHERE `owner`=".$_POST['pid']);
	      }
            $myout = $db -> Execute("SELECT * FROM outposts WHERE id=".$out -> fields['id']);
            $mymonsters = $db -> Execute("SELECT id, name, power, defense FROM outpost_monsters WHERE outpost=".$out -> fields['id']);
            $emonsters = $db -> Execute("SELECT id, name, power, defense FROM outpost_monsters WHERE outpost=".$_POST['oid']);
            $myveterans = $db -> Execute("SELECT * FROM outpost_veterans WHERE outpost=".$out -> fields['id']);
            $eveterans = $db -> Execute("SELECT * FROM outpost_veterans WHERE outpost=".$_POST['oid']);
            $db -> Execute("UPDATE outposts SET turns=turns-1 WHERE owner=".$player -> id);
            /**
            * Chceck fatigue of attacker army - if more than 75% - army cannot attack
            */
            if ($myout -> fields['fatigue'] <= 25)
            {
                $arrmessage[$k] = $arrmessage[$k]."<br />".TOO_FAT2."<br />";
                break;
            }
            /**
            * Check attacks limit - if more than 3, end of attacks
            */
            if ($enemy -> fields['attacks'] > 2)
            {
                $arrmessage[$k] = $arrmessage[$k].ATTACK_LIMIT;
                break;
            }
        }
        $smarty -> assign("Result", $arrmessage);
        $myout -> Close();
        $enemy -> Close();
        $defenduser -> Close();
    }
}

/**
* Outposts guide
*/
if (isset ($_GET['view']) && $_GET['view'] == 'guide') 
{
    $smarty -> assign(array("Info1" => INFO1,
        "Info1a" => INFO1a,
        "Info2" => INFO2,
        "Info2a" => INFO2a,
        "Info3" => INFO3,
        "Info3a" => INFO3a,
        "Info4" => INFO4,
        "Info4a" => INFO4a,
        "Info5" => INFO5,
        "Info5a" => INFO5a,
        "Info6" => INFO6,
        "Info6a" => INFO6a));
}

/**
* Initialization of variables
*/
if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
}
    else
{
    $smarty -> assign("Aback", A_BACK);
}

if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Outpost" => $out -> fields['id'], 
    "View" => $_GET['view'], 
    "Step" => $_GET['step'],
    "Nooutpost" => NO_OUTPOST,
    "Ayes" => YES,
    "Ano" => NO));
$smarty -> display('outposts.tpl');

/**
* Add players list (right side) and footer of page
*/
require_once("includes/foot.php");
?>
