<?php
/**
 *   File functions:
 *   Turn fight players vs monsters
 *
 *   @name                 : turnfight.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 09.01.2013
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
require_once("languages/".$lang."/turnfight.php");

require_once("includes/functions.php");

/**
* Main funtion - fight player vs monsters
*/
function turnfight($expgain,$goldgain,$action,$addres) 
{
    global $player;
    global $smarty;
    global $db;
    global $title;
    global $enemy;
    global $myczaro;
    global $zmeczenie;
    global $myunik;
    global $amount;
    global $myagility;
    global $intPoisoned;
    global $arrTags;

    $myczaro = $db -> Execute("SELECT * FROM czary WHERE status='E' AND gracz=".$player -> id." AND typ='O'");
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);

    $player->user = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];
    $player->stats['speed'][2] += $player->checkbonus('tactic');

    if ($fight -> fields['fight'] == 0 && $title == 'Arena Walk') 
      {
	error (NO_ENEMY);
      }
    $premia = 0;
    $zmeczenie = 0;
    if (empty ($enemy['name'])) 
    {
      require_once('includes/monsters.php');
      $enemy = encounter();
    }
    if ($title == 'Arena Walk') 
    {
        if ($player -> clas != 'Mag' && $myczaro -> fields['id']) 
        {
            error (ONLY_MAGE);
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
    $myobrona = 0;
    $enemy['damage'] = $enemy['strength'] - ($player->stats['condition'][2] + ($player->stats['condition'][2] * $player->checkbonus('defender')));
    if ($myczaro -> fields['id']) 
    {
	$myczarobr = ($player -> stats['wisdom'][2] * $myczaro -> fields['obr']);
	$intBonus = $player->checkbonus('dspells');
	$myczarobr += ($myczarobr * $intBonus);
	$intBonus = $player->checkbonus('element');
	$myczarobr += ($myczarobr * $intBonus);
	$fltBasedef = $myczarobr;
	if ($enemy['dmgtype'] != 'none')
	  {
	    if ($myczaro->fields['element'] == $enemy['dmgtype'])
	      {
		$myczarobr = $myczarobr * 2;
	      }
	    $arrElements = array('water' => 'fire',
				 'fire' => 'wind',
				 'wind' => 'earth',
				 'earth' => 'water');
	    if ($myczaro->fields['element']  == $arrElements[$enemy['dmgtype']])
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
            $intN = ($player->equip[7][4] / 20);
            $intBonus = $player -> skills['magic'][1] * rand(1, $intN);
            $myczarobr = ($myczarobr + $intBonus);
        }
        if ($myczarobr < 0) 
        {
            $myczarobr = 0;
        }
        $myobrona += $myczarobr;
    }
    if ($player -> clas == 'Wojownik'  || $player -> clas == 'Barbarzyńca') 
    {
      $enemy['damage'] -= (($player -> skills['dodge'][1] / 10) + $myobrona);
    } 
        else 
    {
        $enemy['damage'] -= $myobrona;
    }
    if ($enemy['damage'] < 1)
      {
	$enemy['damage'] = 1;
      }
    if (!isset($_SESSION['round']))
    {
        $_SESSION['round'] = 1;
    }
    if (!isset($_SESSION['gatak']))
      {
	$_SESSION['gatak'] = 0;
      }
    if (!isset($_SESSION['gmagia']))
      {
	$_SESSION['gmagia'] = 0;
      }
    if (!isset($_SESSION['gunik']))
      {
	$_SESSION['gunik'] = 0;
      }
    $smarty -> assign ("Message", "<ul><li><b>".$player -> user."</b> ".VERSUS." <b>".$enemy['name']."</b><br />");
    $smarty -> display ('error1.tpl');
    /**
    * Count points in fight
    */
    if (!isset($_SESSION['points']) || $_SESSION['points'] == 0)
    {
        $_SESSION['points'] = ceil($player->stats['speed'][2] / $enemy['speed']);
    }
    /**
    * Count dodge - player and monster
    */
    if (!isset($_POST['action'])) 
    {
        $_POST['action'] = '';
    }
    $strSkill = 'attack';
    if (in_array($_POST['action'], array('nattack', 'battack', 'aattack', 'dattack')))
      {
	if ($player->equip[0][0] || $player->equip[11][0])
	  {
	    $strSkill = 'attack';
	  }
	elseif ($player->equip[1][0])
	  {
	    $strSkill = 'shoot';
	  }
      }
    elseif (in_array($_POST['action'], array('cast', 'bspell')))
      {
	$strSkill = 'magic';
      }
    if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
    {
        $myunik = (($player->stats['agility'][2] - $enemy['agility']) + $player -> skills['dodge'][1] + ($player -> skills['dodge'][1] / 10));
        $eunik = (($enemy['agility'] - $player->stats['agility'][2]) - ($player -> skills[$strSkill][1] + ($player -> skills[$strSkill][1] / 10)));
	if ($player->equip[11][0])
	  {
	    $eunik -= ($player->skills['attack'][1] / 5);
	  }
    }
    else
    {
        $myunik = ($player->stats['agility'][2] - $enemy['agility'] + $player -> skills['dodge'][1]);
        $eunik = (($enemy['agility'] - $player->stats['agility'][2]) - $player -> skills[$strSkill][1]);
    }
    if (!isset($myunik)) 
    {
        $myunik = 1;
    }
    if (!isset($eunik)) 
    {
        $eunit = 1;
    }
    if ($eunik < 1) 
    {
        $eunik = 1;
    }
    if ($_SESSION['points'] > 5) 
    {
        $_SESSION['points'] = 5;
    }
    if (isset ($_SESSION['exhaust'])) 
    {
        $zmeczenie = $_SESSION['exhaust'];
    }
    if (isset($_SESSION['miss']) && $_SESSION['miss'] > $myunik) 
    {
        $myunik = $_SESSION['miss'];
    }
    $amount = 1;
    if (isset ($_SESSION['razy'])) 
    {
        $amount = $_SESSION['razy'];
    }
    if (isset($_SESSION['mon0'])) 
    {
        $temp = 0;
        for ($k = 0; $k < $amount; $k ++) 
        {
            $strIndex = "mon".$k;
            if ($_SESSION[$strIndex] > 0) 
            {
                $temp = $temp + 1;
            }
        }
    }
        else
    {
        $temp = $amount;
        $_SESSION['amount'] = $amount;
        for ($k = 0; $k < $amount; $k ++) 
        {
            $strIndex = "mon".$k;
            $_SESSION[$strIndex] = $enemy['hp'];
        }
    }
    if (isset($temp)) 
    {
        if ($temp < 6 && $temp > 0)
        {
            if ($myunik < 1)
            {
                $myunik = 1;
            }
            if (isset($myunik))
            {
                $myunik = ceil($myunik / $temp);
            }
                else
            {
                $myunik = 0;
            }
        }
            else
        {
            $myunik = 1;
        }
    }
        else
    {
        if ($amount < 6)
        {
            if (isset($myunik))
            {
                $myunik = ceil($myunik / $amount);
            }
                else
            {
                $myunik = 0;
            }
        }
            else
        {
            $myunik = 1;
        }
    }
    if ($myunik < 1) 
    {
        $myunik = 1;
    }
    $attacks = ceil($enemy['speed'] / $player->stats['speed'][2]);
    if ($attacks > 5) 
    {
        $attacks = 5;
    }
    /**
     * If fight is longer than 24 rounds
     */
    if (isset($_SESSION['round']) && $_SESSION['round'] > 24)
    {
        if ($player->hp < 1)
	  {
	    $player->hp = 1;
	  }
        $db -> Execute("UPDATE `players` SET `fight`=0, `hp`=".$player -> hp.", `bless`='', `blessval`=0 WHERE `id`=".$player -> id);
        unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['gatak'], $_SESSION['gmagia'], $_SESSION['gunik']);
        for ($k = 0; $k < $amount; $k ++) 
        {
            $strIndex = "mon".$k;
            unset($_SESSION[$strIndex]);
        }
        if ($title == 'Arena Walk') 
        {
            if (!$intPoisoned)
            {
                $smarty -> assign ("Message", "</ul>".NOT_DECIDE."...<br /><ul><li><b>".B_OPTIONS."</a><br /></li></ul>");
            }
                else
            {
                $smarty -> assign ("Message", "</ul><br /><ul><li><b>".B_OPTIONS."</a><br /></li></ul>");
            }
            $smarty -> display ('error1.tpl');
        }
	elseif (in_array($title, array('Portal', 'Astralny plan')))
	  {
	    $db->Execute("UPDATE `players` SET `miejsce`='Altara' WHERE `id`=".$player->id);
	  }
        return;
    }
    $fight -> Close();
    if ($_POST['action'] == 'dattack' && $player->clas != 'Wojownik')
      {
	$_POST['action'] = 'nattack';
      }
    if ($_POST['action'] == 'aattack' && ($player->clas != 'Wojownik' && $player->clas != 'Barbarzyńca'))
      {
	$_POST['action'] = 'nattack';
      }
    if ($_SESSION['points'] > 0)
      {
	//Drink potion
	if ($_POST['action'] == 'drink')
	  {
	    $_SESSION['points'] --;
	    drink ($_POST['potion2']);
	    $objMana = $db -> Execute("SELECT pm FROM players WHERE id=".$player -> id);
	    $player -> mana = $objMana -> fields['pm'];
	    $objMana -> Close();
	    if ($_SESSION['points'] >= $attacks && $player -> hp > 0) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
	//Equip arrows
	elseif ($_POST['action'] == 'use')
	  {
	    $_SESSION['points'] --;
	    if (!isset($_POST['arrows']))
	      {
		$_POST['arrows'] = 0;
	      }
	    equip ($_POST['arrows']);
	    $player->equip = $player->equipment();
	    if ($_SESSION['points'] >= $attacks) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
	//Equip weapon
	elseif ($_POST['action'] == 'weapons')
	  {
	    $_SESSION['points'] -= 2;
	    if (!isset($_POST['weapon']))
	      {
		$_POST['weapon'] = 0;
	      }
	    equip ($_POST['weapon']);
	    $player->equip = $player->equipment();
	    if ($_SESSION['points'] >= $attacks) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
	//Cast a spell
	elseif ($_POST['action'] == 'cast')
	  {
	    $_SESSION['points'] --;
	    if (!isset($_POST['castspell']))
	      {
		$_POST['castspell'] = 0;
	      }
	    castspell($_POST['castspell'],0,$eunik);
	    $temp = 0;
	    for ($k=0;$k<$amount;$k++) 
	      {
		$strIndex = "mon".$k;
		if ($_SESSION[$strIndex] > 0) 
		  {
		    $temp = $temp + 1;
		  }
	      }
	    if ($temp > 0 && $attacks <= $_SESSION['points']) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
	//Burst offensive spell
	elseif ($_POST['action'] == 'bspell')
	  {
	    if (intval($_POST['power']) < 1) 
	      {
		$smarty -> assign ("Message", ERROR);
		$smarty -> display ('error1.tpl');
		$_POST['power'] = 0;
	      }
            else
	      {
		if ($_POST['power'] > $player->skills['magic'][1])
		  {
		    $_POST['power'] = $player->skills['magic'][1];
		  }
		checkvalue($_POST['bspellboost']);
		$intSpelllevel = $db -> Execute("SELECT `gracz`, `poziom` FROM `czary` WHERE `id`=".$_POST['bspellboost']);
		if (!$intSpelllevel->fields['gracz'] || $intSpelllevel->fields['gracz'] != $player->id)
		  {
		    $intMaxburst = 0;
		  }
		else
		  {
		    $intMaxburst = $player -> mana - ceil($intSpelllevel -> fields['poziom'] / 10);
		  }
		$intSpelllevel -> Close();
		if ($_POST['power'] > $intMaxburst)
		  {
		    $_POST['power'] = $intMaxburst;
		  }
		$_SESSION['points'] -= 2;
		castspell($_POST['bspellboost'],$_POST['power'],$eunik);
	      }
	    $temp = 0;
	    for ($k=0;$k<$amount;$k++) 
	      {
		$strIndex = "mon".$k;
		if ($_SESSION[$strIndex] > 0) 
		  {
		    $temp ++;
		  }
	      }
	    if ($temp > 0 && $attacks <= $_SESSION['points']) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
	//Normal attack
	elseif ($_POST['action'] == 'nattack')
	  {
	    attack($eunik,0);
	    $temp = 0;
	    for ($k=0;$k<$amount;$k++) 
	      {
		$strIndex = "mon".$k;
		if ($_SESSION[$strIndex] > 0) 
		  {
		    $temp ++;
		  }
	      }
	    $_SESSION['points'] --;
	    if ($temp > 0 && $attacks <= $_SESSION['points']) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
	//Defensive attack
	elseif ($_POST['action'] == 'dattack')
	  {
	    $_SESSION['points'] --;
	    attack($eunik,3);
	    $temp = 0;
	    for ($k=0;$k<$amount;$k++) 
	      {
		$strIndex = "mon".$k;
		if ($_SESSION[$strIndex] > 0) 
		  {
		    $temp ++;
		  }
	      }
	    $myunik += ($myunik / 2);
	    $enemy['damage'] -= ($myobrona / 2);
	    if ($temp > 0 && $attacks <= $_SESSION['points']) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
	//Aggresive attack
	elseif ($_POST['action'] == 'aattack')
	  {
	    $_SESSION['points'] --;
	    attack($eunik,1);
	    $temp = 0;
	    for ($k=0;$k<$amount;$k++) 
	      {
		$strIndex = "mon".$k;
		if ($_SESSION[$strIndex] > 0) 
		  {
		    $temp ++;
		  }
	      }
	    $myunik = $myunik / 2;
	    $enemy['damage'] += ($myobrona / 2);
	    if ($temp > 0 && $attacks <= $_SESSION['points']) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
	elseif ($_POST['action'] == 'battack')
	  {
	    $_SESSION['points'] -= 2;
	    attack($eunik,2);
	    $temp = 0;
	    for ($k=0;$k<$amount;$k++) 
	      {
		$strIndex = "mon".$k;
		if ($_SESSION[$strIndex] > 0) 
		  {
		    $temp ++;
		  }
	      }
	    $myunik = 0;
	    if ($temp > 0 && $attacks <= $_SESSION['points']) 
	      {
		fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
	      }
	  }
      }
    if ($_POST['action'] == 'escape') 
    {
      $chance = (($player->stats['speed'][2] + $player->skills['perception'][1] + rand(1, 100)) - ($enemy['speed'] + rand(1, 100)));
        if ($chance > 0) 
        {
	    $expgain = ceil(($enemy['speed'] + $enemy['endurance'] + $enemy['agility'] + $enemy['strength']) / 100);
            $smarty -> assign ("Message", ESCAPE_SUCC." ".$enemy['name'].YOU_GAIN1." ".$expgain." ".EXP_PTS."<br /></li></ul>");
            $smarty -> display ('error1.tpl');
	    if ($player->equip[0][0] || $player->equip[11][0]) 
	      {
		$strType = 'melee';
	      }
	    else
	      {
	    $strType = 'ranged';
	      }
	    gainability($player, $expgain, $_SESSION['gunik'], $_SESSION['gatak'], $_SESSION['gmagia'], $player->id, $strType);
            $db -> Execute("UPDATE players SET fight=0, bless='', blessval=0 WHERE id=".$player -> id);
            $points = $attacks * 2;
            $temp = 1;
            unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['gatak'], $_SESSION['gmagia'], $_SESSION['gunik']);
            for ($k = 0; $k < $amount; $k ++) 
            {
                $strIndex = "mon".$k;
                unset($_SESSION[$strIndex]);
            }
            if ($title == "Arena Walk") 
            {
                $smarty -> assign ("Message", "</ul><ul><li><b>".B_OPTIONS."</a><br /></li></ul>");
                $smarty -> display ('error1.tpl');
            }
            if ($title == 'Astralny plan' || $title == 'Portal')
            {
                $db -> Execute("UPDATE `players` SET `fight`=9999 WHERE id=".$player -> id);
            }
	    if ($title == 'Przygoda')
	      {
		$db -> Execute("UPDATE `players` SET `fight`=-1 WHERE id=".$player -> id);
	      }
	    if ($player->hp < 1)
	      {
		$player->hp = 1;
	      }
        } 
            else 
        {
            $smarty -> assign ("Message", "<br />".ESCAPE_FAIL." ".$enemy['name']."!");
            $smarty -> display ('error1.tpl');
            monsterattack($attacks,$enemy,$myunik,$amount);
            if ($player -> hp > 0) 
            {
                $_SESSION['round'] = $_SESSION['round'] + 1;
                $_SESSION['points'] = ceil($player->stats['speed'][2] / $enemy['speed']);
                fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
            }
        }
    }
    /**
     * Burst defensive spell
     */
    if ($_POST['action'] == 'dspell') 
    {
        if (intval($_POST['power1']) < 1) 
        {
            $smarty -> assign ("Message", ERROR);
            $smarty -> display ('error1.tpl');
            $_POST['power1'] = 0;
        }
        if ($_POST['power1'] > $player -> skills['magic'][1])
        {
            $_POST['power1'] = $player -> skills['magic'][1];
        }
        $intMaxburst = $player -> mana - $myczaro -> fields['poziom'];
        if ($_POST['power1'] > $intMaxburst)
        {
            $_POST['power1'] = $intMaxburst;
        }
        $enemy['damage'] = $enemy['damage'] - $_POST['power1'];
        $player -> mana = $player -> mana - $_POST['power1'];
        monsterattack($attacks,$enemy,$myunik,$amount);
        if ($player -> hp > 0) 
        {
            $_SESSION['round'] = $_SESSION['round'] + 1;
            $_SESSION['points'] = ceil($player->stats['speed'][2] / $enemy['speed']);
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if (!isset($_SESSION['points']))
    {
        $_SESSION['points'] = 0;
    }
    if($attacks > $_SESSION['points'] && $temp > 0 && $_POST['action'] != 'rest' && $_POST['action'] != 'escape') 
    {
        monsterattack($attacks,$enemy,$myunik,$amount);
        if ($player -> hp > 0) 
        {
            $_SESSION['round'] = $_SESSION['round'] + 1;
            $_SESSION['points'] = ceil($player->stats['speed'][2] / $enemy['speed']);
            if ($_SESSION['points'] > 5) 
            {
                $_SESSION['points'] = 5;
            }
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($_POST['action'] == 'rest') 
    {
        monsterattack($attacks,$enemy,0,$amount);
        if ($player -> hp > 0) 
        {
            $smarty -> assign ("Message", "<br />".REST_SUCC);
            $smarty -> display ('error1.tpl');
            $zmeczenie -= ($player -> stats['condition'][2] / 10);
            if ($zmeczenie < 0) 
            {
                $zmeczenie = 0;
            }
            $_SESSION['exhaust'] = $zmeczenie;
            $_SESSION['round'] ++;
            $_SESSION['points'] = ceil($player->stats['speed'][2] / $enemy['speed']);
            if ($_SESSION['points'] > 5) 
            {
                $_SESSION['points'] = 5;
            }
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    //Lost fight
    if ($player -> hp <= 0) 
    {
        if ($title != 'Arena Walk') 
	  {
	    $player->dying();
	  }
	if ($player->antidote == 'R')
	  {
	    $player->hp = 1;
	  }
        if ($player -> hp < 0) 
        {
            $player -> hp = 0;
        }
	if ($title == 'Przygoda')
	  {
	    $intFight = -1;
	  }
	else
	  {
	    $intFight = 0;
	  }
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player -> user." ".DEFEATED_BY.$enemy['name']."')");
        $db -> Execute("UPDATE `players` SET `fight`=".$intFight.", `hp`=".$player -> hp.", `bless`='', `blessval`=0, `antidote`='' WHERE `id`=".$player -> id);
        unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['gatak'], $_SESSION['gmagia'], $_SESSION['gunik']);
        for ($k = 0; $k < $amount; $k ++) 
        {
            $strIndex = "mon".$k;
            unset($_SESSION[$strIndex]);
        }
        if ($title == 'Arena Walk') 
        {
            if (!$intPoisoned)
            {
                $smarty -> assign ("Message", "</ul>".LOST_FIGHT."...<br /><ul><li><b>".B_OPTIONS."</a><br /></li></ul>");
            }
                else
            {
                $smarty -> assign ("Message", "</ul><br /><ul><li><b>".B_OPTIONS."</a><br /></li></ul>");
            }
            $smarty -> display ('error1.tpl');
        }
	checkpet($player->id, $player->pet, $player->id, TRUE);
    }
    if (isset($_SESSION['points']))
    {
        $intPoints = $_SESSION['points'];
    }
        else
    {
        $intPoints = 0;
    }
    if (!$_POST['action'] && $attacks <= $intPoints) 
    {
        fightmenu($_SESSION['points'],$zmeczenie,1,$addres);
    }
    if ($temp == 0 && $player -> fight > 0 && (isset($_SESSION['round']) && $_SESSION['round'] < 25)) 
    {
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player -> user." ".DEFEAT." ".$enemy['name']."')");
        $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$goldgain." WHERE `id`=".$player -> id);
        $smarty -> assign ("Message", "<br /><li><b>".YOU_DEFEAT." <b>".$amount." ".$enemy['name']."</b>.");
        $smarty -> display ('error1.tpl');
        $smarty -> assign ("Message", "<li><b>".REWARD." <b>".$expgain."</b> ".EXP_PTS." ".AND_GAIN." <b>".$goldgain."</b> ".COINS);
        $smarty -> display ('error1.tpl');
	monsterloot($enemy['lootnames'], $enemy['lootchances'], $enemy['level'], $amount);
	battlerecords($enemy['name'], $enemy['level'], $player->id);
	if ($player->equip[0][0] || $player->equip[11][0]) 
	  {
	    $strType = 'melee';
	  }
	else
	  {
	    $strType = 'ranged';
	  }
	gainability($player, $expgain, $_SESSION['gunik'], $_SESSION['gatak'], $_SESSION['gmagia'], $player->id, $strType);
	checkpet($player->id, $player->pet, $player->id);
        if ($player -> hp < 0) 
        {
            $player -> hp = 0;
        }
	if (($player->hp > 0) && ($player->settings['autodrink'] != 'N'))
	  {
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
        if ($title == 'Arena Walk') 
        {
            $smarty -> assign ("Message", "</ul><ul><li><b>".B_OPTIONS."</a><br /></li></ul>");
            $smarty -> display ('error1.tpl');
        }
	if ($player->hp < 1)
	  {
	    $player->hp = 1;
	  }
        $db -> Execute("UPDATE players SET hp=".$player -> hp.", fight=0, bless='', blessval=0 WHERE id=".$player -> id);
	if (isset($_SESSION['razy']))
	  {
	    unset($_SESSION['razy']);
	  }
        unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['gatak'], $_SESSION['gmagia'], $_SESSION['gunik']);
        for ($k = 0; $k < $amount; $k ++) 
        {
            $strIndex = "mon".$k;
            unset($_SESSION[$strIndex]);
        }
    }
    $myczaro -> Close();
}

/**
* Attack on monster with weapon
*/
function attack($eunik,$bdamage) 
{
    global $smarty;
    global $player;
    global $db;
    global $zmeczenie;
    global $enemy;
    global $amount;
    $number1 = $_POST['monster'] - 1;
    $number = "mon".$number1;
    if (isset ($_SESSION['exhaust'])) 
    {
        $zmeczenie = $_SESSION['exhaust'];
    }
    if (!$player->equip[0][0] && !$player->equip[1][0]) 
    {
        $smarty -> assign("Message", NO_WEAPON);
        $smarty -> display('error1.tpl');
    }
    $player->skills['attack'][1] += $player->checkbonus('weaponmaster');
    $player->skills['shoot'][1] += $player->checkbonus('weaponmaster');
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
            $stat['damage'] = (($player -> stats['strength'][2] + $player->equip[0][2]) + $player -> skills['attack'][1]);
        } 
            else 
        {
            $stat['damage'] = ($player -> stats['strength'][2] + $player->equip[0][2]);
        }
        if ($player -> skills['attack'][1] > 5) 
        {
            $krytyk = 6;
        } 
            else 
        {
            $krytyk = $player -> skills['attack'][1];
        }
        $name = "bronią";
	$strAtype = 'melee';
	$strSkill = 'attack';
    }
    if ($player->equip[11][0])
      {
	$stat['damage'] += (($player->equip[11][2] + $player->stats['strength'][2]) + $player->skills['attack'][1]);
	$name = "obiema brońmi";
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
        $bonus2 = (($player  -> stats['strength'][2] / 2) + ($player->stats['agility'][2] / 2));
        if ($player -> clas == 'Wojownik'  || $player -> clas == 'Barbarzyńca') 
        {
            $stat['damage'] = (($bonus2 + $bonus) + $player -> skills['shoot'][1]);
        } 
            else 
        {
            $stat['damage'] = ($bonus2 + $bonus);
        }
        if ($player -> skills['shoot'][1] > 5) 
        {
            $krytyk = 6;
        } 
            else 
        {
            $krytyk = $player -> skills['shoot'][1];
        }
        if (!$player->equip[6][0]) 
        {
            $stat['damage'] = 0;
        }
	$eunik -= $player->checkbonus('eagleeye');
        $eunik = $eunik * 2;
        $name = "strzałą";
	$strAtype = 'ranged';
	$strSkill = 'shoot';
    }
    if (!isset($stat['damage']))
    {
        $stat['damage'] = 0;
    }
    if ($player -> clas == 'Rzemieślnik')
    {
        $stat['damage'] -= ($stat['damage'] / 4);
    }
    if ($bdamage == 2) 
    {
        $stat['damage'] = $stat['damage'] * 2;
    }
    if ($bdamage == 1) 
    {
        $stat['damage'] = $stat['damage'] + ($stat['damage'] / 2);
        $eunik = $eunik - ($eunik / 10);
    }
    if ($bdamage == 3) 
    {
        $stat['damage'] = $stat['damage'] - ($stat['damage'] / 2);
        $eunik = $eunik + ($eunik / 10);
    }
    $krytyk += $player->checkbonus('assasin');
    $rzut2 = rand(1, $player -> skills[$strSkill][1]);
    $stat['damage'] = ($stat['damage'] + $rzut2);
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
    $stat['damage'] = ($stat['damage'] - $enemy['endurance']);
    if ($stat['damage'] < 1) 
    {
        $stat['damage'] = 0;
    }
    if ($player->equip[0][0] && $player->equip[0][6] < 1) 
      {
	$stat['damage'] = 0;
	if ($player->equip[11][6] > 0)
	  {
	    $stat['damage'] += (($player->equip[11][2] + $player->strength) + $player->skills['attack'][1]);
	  }
	$krytyk = 1;
      }
    if ($player->equip[11][0] && $player->equip[11][6] < 1) 
      {
	$stat['damage'] = 0;
	if ($player->equip[0][6] > 0)
	  {
	    $stat['damage'] += (($player->equip[0][2] + $player->strength) + $player->skills['attack'][1]);
	  }
	$krytyk = 1;
      }
    if ($player->equip[1][0] && ($player->equip[1][6] < 1 || $player->equip[6][6] < 1)) 
    {
        $stat['damage'] = 0;
        $krytyk = 1;
    }
    if ($player->equip[1][0] && !$player->equip[6][0]) 
    {
        $stat['damage'] = 0;
        $krytyk = 1;
    }
    $ehp = $_SESSION[$number];
    if ($ehp <= 0)
    {
        $smarty -> assign("Message", ERROR);
        $smarty -> display('error1.tpl');
    }
    if ($player->equip[1][0]) 
      {
	$eunik -= $player->checkbonus('eagleeye');
        $eunik = $eunik * 2;
      }
    if ($ehp > 0 && $player -> hp > 0) 
    {
        if ($player->equip[0][0] && $player->equip[0][6] > 0) 
	  {
	    $zmeczenie += ($player->equip[0][4] / 10);
	    $player->equip[0][6] --;
	  } 
	elseif ($player->equip[1][0] && $player->equip[1][6] > 0 && $player->equip[6][6] > 0) 
	  {
            $zmeczenie += ($player->equip[1][4] / 10);
	    $player->equip[1][6] --;
	    $player->equip[6][6] --;
	  }
	if ($player->equip[11][0] && $player->equip[11][6] > 0)
	  {
	    $zmeczenie += ($player->equip[11][4] / 10);
	    $player->equip[11][6] --;
	  }
	$intDodgemax = 100;
	$intDodgemax2 = 97;
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
        if ($eunik >= $szansa && $szansa < $intDodgemax2) 
        {
            $smarty -> assign ("Message", "<b>".$enemy['name']."</b> ".ENEMY_DODGE."!<br />");
            $smarty -> display ('error1.tpl');
        } 
	elseif ($zmeczenie <= $player -> stats['condition'][2]) 
        {
            if ($player->equip[0][0] || $player->equip[1][0] || $player->equip[11][0]) 
	      {
		$rzut = rand(1, 1000) / 10;
		$intRoll = rand(1, 100);
		$arrLocations = array('w tułów', 'w głowę', 'w kończynę');
		$intHit = rand(0, 2);
		if ($krytyk >= $rzut && $intRoll <= $krytyk && $player->fight != 999) 
		  {
		    $_SESSION['gatak'] = 1;
		    $ehp = 0;
		    $smarty->assign("Message", showcritical($arrLocations[$intHit], $strAtype, 'pve', $enemy['name']));
		  }
		else
		  {
		    $ehp -= $stat['damage'];
		    $smarty -> assign ("Message", YOU_ATTACK1." ".$name." <b>".$enemy['name']."</b> ".$arrLocations[$intHit]." ".INFLICT." <b>".$stat['damage']."</b> ".DAMAGE."! (".$ehp." ".LEFT.")</font><br />");
		    if ($stat['damage'] > 0) 
		      {
			$_SESSION['gatak'] = 1;
		      }
		  }
		$smarty -> display ('error1.tpl');
	      }
        }
    }
    $_SESSION[$number] = $ehp;
    lostitem($player->equip, $player->id, $player->id, $player->skills['shoot'][1]);
    $_SESSION['exhaust'] = $zmeczenie;
    $player->skills['attack'][1] -= $player->checkbonus('weaponmaster');
    $player->skills['shoot'][1] -= $player->checkbonus('weaponmaster');
}

/**
* Attack on monster by spell
*/
function castspell ($id,$boost,$eunik) 
{
    global $smarty;
    global $player;
    global $db;
    global $enemy;
    global $amount;
    $number1 = $_POST['monster'] - 1;
    $number = "mon".$number1;
    $gmagia = 0;
    $mczar = $db -> Execute("SELECT * FROM czary WHERE id=".$id);
    if ($mczar -> fields['id']) 
    {
	$stat['damage'] = ($mczar -> fields['obr'] * $player -> stats['inteli'][2]);
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
            $intBonus = $player -> skills['magic'][1] * rand(1, $intN);
            $stat['damage'] = $stat['damage'] + $intBonus;
        }
        if ($stat['damage'] < 0) 
        {
            $stat['damage'] = 0;
        }
        if ($player -> skills['magic'][1] > 5) 
        {
            $krytyk = 6;
        } 
            else 
        {
            $krytyk = $player -> skills['magic'][1];
        }
        if ($boost) 
        {
            $stat['damage'] += $boost;
        }
    }
    if (!isset($stat['damage']))
    {
        $stat['damage'] = 0;
    }
    if ($player->pet[0])
      {
	if ($player->pet[1] > $player->skills['magic'][1])
	  {
	    $stat['damage'] += $player->skills['magic'][1];
	  }
	else
	  {
	    $stat['damage'] += $player->pet[1];
	  }
      }
    $rzut2 = rand(1, $player -> skills['magic'][1]);
    $stat['damage'] = ($stat['damage'] + $rzut2);
    $stat['damage'] = ($stat['damage'] - $enemy['endurance']);
    if ($stat['damage'] < 1) 
    {
        $stat['damage'] = 0 ;
    }
    if ($player -> mana < 1) 
    {
         $stat['damage'] = 0;
    }
    $ehp = $_SESSION[$number];
    if ($ehp <= 0)
    {
        $smarty -> assign("Message", ERROR);
        $smarty -> display('error1.tpl');
        $lost_mana = 1 + $boost;
        $player -> mana -= $lost_mana;
    }
    if ($ehp > 0) 
    {
	$intDodgemax = 100;
	$intDodgemax2 = 97;
	if ($player->stats['agility'][2] + $player->skills['magic'][1] < 100)
	  {
	    $intDodgemax = $player->stats['agility'][2] + $player->skills['magic'][1];
	    if ($intDodgemax < 4)
	      {
		$intDodgemax = 4;
	      }
	    $intDodgemax2 = floor($intDodgemax * 0.97);
	}
	$szansa = rand(1, $intDodgemax);
        if ($eunik >= $szansa && $szansa < $intDodgemax2) 
        {
            $smarty -> assign ("Message", "<b>".$enemy['name']."</b> ".ENEMY_DODGE."<br />");
            $smarty -> display ('error1.tpl');
        } 
	else 
        {
            if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
            {
                $pech = floor($player -> skills['magic'][1] - $mczar -> fields['poziom']);
                if ($pech > 0) 
                {
                    $pech = 0;
                }
                $pech += rand(1,100);
                if ($pech > 5) 
                {
                    $lost_mana = 1 + $boost;
                    $player -> mana -= $lost_mana;
		    $rzut = rand(1, 1000) / 10;
		    $intRoll = rand(1, 100);
		    if ($krytyk >= $rzut && $intRoll <= $krytyk && $player->fight != 999) 
		      {
			$_SESSION['gmagia'] = 1;
			$ehp = 0;
			$arrLocations = array('w tułów', 'w głowę', 'w kończynę');
			$intHit = rand(0, 2);
			$smarty->assign("Message", showcritical($arrLocations[$intHit], 'spell', 'pve', $enemy['name']));
		      }
		    else
		      {
			$ehp -= $stat['damage'];
			$arrLocations = array('w tułów', 'w głowę', 'w kończynę');
			$intHit = rand(0, 2);
			$smarty -> assign ("Message", YOU_HIT." <b>".$enemy['name']."</b> ".$arrLocations[$intHit]." ".BY_SPELL." ".$mczar -> fields['nazwa']." ".INFLICT." <b>".$stat['damage']."</b> ".DAMAGE."! (".$ehp." ".LEFT.")</font><br />");
			if ($stat['damage'] > 0) 
			  {
			    $_SESSION['gmagia'] = 1;
			  }
		      }
		    $smarty->display('error1.tpl');
                } 
                    else 
                {
                    $pechowy = rand(1,100);
		    if ($pechowy <= 25) 
		      {
			$smarty -> assign ("Message", "<b>".$player -> user."</b> ".YOU_FAIL1." ".$mczar -> fields['nazwa'].", ".YOU_FAIL2." <b>".$mczar -> fields['poziom']."</b> ".MANA.".<br />");
                        $smarty -> display ('error1.tpl');
                        $player -> mana --;
		      }
		    elseif ($pechowy > 25 && $pechowy <= 45)
		      {
			$smarty->assign("Message", "<b>".$player->user."</b> zapatrzyłeś się na szybko poruszającego się żółwia i straciłeś koncentrację.<br />");
			$smarty->display('error1.tpl');
		      }
		    elseif ($pechowy > 45 && $pechowy <= 50)
		      {
			$smarty -> assign ("Message", "<b>".$player -> user." ".YOU_FAIL1." ".$mczar -> fields['nazwa'].", ".YOU_FAIL3.".</b><br />");
                        $smarty -> display ('error1.tpl');
                        $player -> mana = 0;
		      }
		    elseif ($pechowy > 50 && $pechowy <= 55)
		      {
			$smarty -> assign ("Message", "<b>".$player -> user." ".YOU_FAIL4." ".$mczar -> fields['nazwa']."! ".YOU_FAIL5." ".$stat['damage']." ".HP."!</b><br />");
                        $smarty -> display ('error1.tpl');
                        $player -> hp -= $stat['damage'];
                        if ($player -> hp < 0)
                        {
                            $player -> hp = 0;
                        }
                        $db -> Execute("UPDATE `players` SET `hp`=".$player -> hp." WHERE `id`=".$player -> id);
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
			$ehp -= $intDamage;
			$player->mana --;
			$smarty->assign("Message", "<b>".$player -> user."</b> nie do końca opanowałeś zaklęcie, dlatego twój czar zadaje <b>".$intDamage."</b> obrażeń. (".$ehp." zostało)<br />");
			$smarty->display('error1.tpl');
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
			$ehp -= $intDamage;
			$player->mana --;
			$player->hp -= $intDamage;
			$smarty->assign("Message", "<b>".$player -> user."</b> próbował rzucić zaklęcie, ale eksplodowało ono w rękach, raniąc jego oraz wroga. Traci przez to ".$intDamage." punktów życia (".$player->hp." zostało), <b>".$enemy['name']."</b> otrzymuje ".$intDamage." obrażeń (".$ehp." zostało)<br />");
			$smarty->display('error1.tpl');
			if ($player -> hp < 0)
			  {
                            $player -> hp = 0;
			  }
                        $db -> Execute("UPDATE `players` SET `hp`=".$player -> hp." WHERE `id`=".$player -> id);
		      }
                }
		$db->Execute("UPDATE `players` SET `pm`=".$player->mana." WHERE `id`=".$player->id);
            }
        }
    }
    $_SESSION[$number] = $ehp;
    $mczar -> Close();
}

/**
* Monster attacks
*/
function monsterattack($attacks,$enemy,$myunik,$amount) 
{
    global $smarty;
    global $player;
    global $db;
    global $enemy;
    global $myczaro;
    global $zmeczenie;
    global $number;
    if (isset ($_SESSION['exhaust'])) 
    {
        $zmeczenie = $_SESSION['exhaust'];
    }
    $gunik = 0;
    $temp = 0;
    for ($k=0;$k<$amount;$k++) 
    {
        $strIndex = "mon".$k;
        if ($_SESSION[$strIndex] > 0) 
        {
            $temp ++;
        }
    }
    $amount = $temp;
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
    for ($j = 1;$j <= $amount;++$j) 
    {
        $ename = $enemy['name']." nr".$j;
        for ($i = 1;$i <= $attacks; ++$i) 
        {
            $rzut1 = rand(0,$enemy['level']);
            $intDamage = ($enemy['damage'] + $rzut1);
            if ($intDamage < 1) 
            {
                $intDamage = 1;
            }
            if ($player -> mana < $myczaro -> fields['poziom']) 
            {
                $intDamage = $enemy['strength'] + $rzut1;
            }
            if ($zmeczenie > $player -> stats['condition'][2]) 
            {
                $intDamage = $enemy['strength'] + $rzut1;
            }
	    $blnMiss = FALSE;
            if ($player -> hp > 0) 
            {
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
                if ($myunik >= $szansa && $zmeczenie < $player -> stats['condition'][2] && $szansa < $intDodgemax2) 
                {
                    $smarty -> assign ("Message", "<br>".YOU_DODGE." <b>".$ename."</b>!");
                    $smarty -> display ('error1.tpl');
                    $_SESSION['gunik'] = 1;
                    $zmeczenie += ($player->equip[3][4] / 10);
		    $blnMiss = TRUE;
                } 
		//Player block attack with shield
		$szansa = rand(1, 100);
		if ($szansa <= $intBlock && !$blnMiss && $player->equip[5][6] > 0)
		  {
		    $smarty -> assign ("Message", "<br>Zablokowałeś tarczą atak <b>".$ename."</b>!");
                    $smarty -> display ('error1.tpl');
		    $zmeczenie += ($player->equip[5][4] / 10);
		    $player->equip[5][6] --;
		    $blnMiss = TRUE;
		  }
                if (!$blnMiss)
                {
		    $intHit = hitlocation();
		    $myobrona = 0;
		    if ($player->pet[0])
		      {
			if ($player->pet[2] > $player->skills['dodge'][1])
			  {
			    $myobrona += $player->skills['dodge'][1];
			  }
			else
			  {
			    $myobrona += $player->pet[2];
			  }
		      }
		    if ($player->equip[$intHit + 2][0])
		      {
			$myobrona = ($player->equip[$intHit + 2][2] + ($player->equip[$intHit + 2][2] * $player->checkbonus('defender')));
		        $player->equip[$intHit + 2][6] --;
			if ($enemy['dmgtype'] != 'none')
			  {
			    if ($player->equip[$intHit + 2][10] != 'N')
			      {
				if ($arrElements3[$enemy['dmgtype']] == $player->equip[$intHit + 2][10])
				  {
				    $myobrona += $player->equip[$intHit + 2][2];
				  }
				elseif ($arrElements4[$enemy['dmgtype']] == $player->equip[$intHit + 2][10])
				  {
				    $myobrona -= ceil($player->equip[$intHit + 2][2] / 2);
				  }
			      }
			  }
		      }
		    $myobrona -= ($myobrona * $player->checkbonus('rage'));
		    $intDamage -= $myobrona;
		    if ($intDamage < 1)
		      {
			$intDamage = 1;
		      }
                    $player -> hp -= $intDamage;
                    $db -> Execute("UPDATE `players` SET `hp`=".$player -> hp." WHERE `id`=".$player -> id);
		    $arrLocations = array('w głowę i zadaje(ą)', 'w tułów i zadaje(ą)', 'w nogę i zadaje(ą)', 'w rękę i zadaje(ą)');
                    $smarty -> assign ("Message", "<br><b>".$ename."</b> ".ENEMY_HIT2.$arrLocations[$intHit]." <b>".$intDamage."</b> obrażeń! (".$player -> hp." zostało)");
                    $smarty -> display ('error1.tpl');
                    if ($myczaro -> fields['id'] && $player -> mana >= $myczaro -> fields['poziom']) 
                    {
                        $lost_mana = ceil($myczaro -> fields['poziom'] / 2.5);
                        $lost_mana = $lost_mana - (int)($player->skills['magic'][1] / 25);
                        if ($lost_mana < 1)
                        {
                            $lost_mana = 1;
                        }
                        $player -> mana -= $lost_mana;
			$db->Execute("UPDATE `players` SET `pm`=".$player->mana." WHERE `id`=".$player->id);
                    }
                }
            }
        }
    }
    lostitem($player->equip, $player->id, $player->id, $player->skills['shoot'][1]);
    $_SESSION['exhaust'] = $zmeczenie;
}

/**
* Menu turn fight
*/
function fightmenu ($points,$exhaust,$round,$addres) 
{
    global $player;
    global $smarty;
    global $db;
    global $myunik;
    global $amount;
    global $enemy;
    $smarty -> assign(array("Round" => $round, 
                            "Points" => $points, 
                            "Mana" => $player -> mana, 
                            "HP" => $player -> hp, 
                            "Exhaust" => $exhaust, 
                            "Cond" => $player -> stats['condition'][2], 
                            "Adres" => $addres,
                            "Fround" => F_ROUND,
                            "Actionpts" => ACTION_PTS,
                            "Lifepts" => LIFE_PTS,
                            "Exhausted" => EXHAUSTED,
                            "Quiver" => '',
                            "Arramount" => ''));
    if ($player->equip[6][0])
    {
        $smarty -> assign(array("Quiver" => QUIVER,
                                "Arramount" => $player->equip[6][6]));
    }
    if ($player->equip[0][0] || $player->equip[1][0]) 
    {
        if ($player -> clas == 'Wojownik')
        {
            $smarty -> assign(array("Dattack" => "<input type=\"radio\" name=\"action\" value=\"dattack\"> ".D_ATTACK."<br /><br />",
                                    "Nattack" => "<input type=\"radio\" name=\"action\" value=\"nattack\" checked> ".N_ATTACK."<br /><br />",
                                    "Aattack" => "<input type=\"radio\" name=\"action\" value=\"aattack\"> ".A_ATTACK."<br /><br />"));
        }
	elseif ($player->clas == 'Barbarzyńca')
	  {
	    $smarty -> assign(array("Dattack" => "",
                                    "Nattack" => "<input type=\"radio\" name=\"action\" value=\"nattack\" checked> ".N_ATTACK."<br /><br />",
                                    "Aattack" => "<input type=\"radio\" name=\"action\" value=\"aattack\"> ".A_ATTACK."<br /><br />"));
	  }
            else
        {
            $smarty -> assign(array("Dattack" => '',
                                    "Nattack" => "<input type=\"radio\" name=\"action\" value=\"nattack\" checked> ".N_ATTACK."<br /><br />",
                                    "Aattack" => ''));
        }
    } 
        else 
    {
        $smarty -> assign(array("Dattack" => '', 
                                "Nattack" => '', 
                                "Aattack" => ''));
    }
    $smarty -> display ('turnfight.tpl');
    if ($amount > 0) 
    {
        $smarty -> assign ("Message", ATTACK_MONSTER.": <select name=\"monster\">");
        $smarty -> display ('error1.tpl');
        $strSelect = 'selected';
        for ($i=0;$i<$amount;$i++) 
        {
            $number = $i + 1;
            $strIndex = "mon".$i;
            if ($_SESSION[$strIndex] > 0) 
            {
                $ename = $enemy['name']." nr ".$number;
                $smarty -> assign ("Message", "<option value=\"".$number."\" ".$strSelect.">".$ename." ".LIFE.": ".$_SESSION[$strIndex]."</option>");
                $strSelect = '';
                $smarty -> display ('error1.tpl');
            }
        }
        $smarty -> assign ("Message", "</select><br /><br />");
        $smarty -> display ('error1.tpl');
    }
    if ($player -> clas == 'Mag') 
      {
	$strHtml = "<input type=\"radio\" name=\"action\" value=\"cast\"> ".SPELL_ATTACK." <select name=\"castspell\">";
        $arrspell = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND typ='B'");
        while (!$arrspell->EOF) 
	  {
	    if ($arrspell->fields['status'] == 'U')
	      {
		$strHtml .= "<option value=".$arrspell -> fields['id'].">".$arrspell -> fields['nazwa']." ".POWER.": ".$arrspell -> fields['obr']."</option>";
	      }
	    else
	      {
		$strHtml .= '<option value="'.$arrspell -> fields['id'].'" selected="selected">'.$arrspell -> fields['nazwa']." ".POWER.": ".$arrspell -> fields['obr']."</option>";
	      }
            $arrspell -> MoveNext();
	  }
        $smarty -> assign ("Message", $strHtml."</select><br /><br />");
        $smarty -> display ('error1.tpl');
        $arrspell -> Close();
      }
    $arrpotion1 = $db -> Execute("SELECT * FROM potions WHERE owner=".$player -> id." AND status='K' AND  type!='P'");
    if (!empty($arrpotion1 -> fields['id'])) 
    {
        $smarty -> assign ("Message", "<input type=\"radio\" name=\"action\" value=\"drink\"> ".DRINK_POTION." <select name=\"potion2\">");
        $smarty -> display ('error1.tpl');
        while (!$arrpotion1 -> EOF) 
        {
            $smarty -> assign ("Message", "<option value=".$arrpotion1 -> fields['id'].">".$arrpotion1 -> fields['name']." ".POWER.": ".$arrpotion1 -> fields['power']." ".AMOUNT.": ".$arrpotion1 -> fields['amount']."</option>");
            $smarty -> display ('error1.tpl');
            $arrpotion1 -> MoveNext();
        }
        $smarty -> assign ("Message", "</select><br /><br />");
        $smarty -> display ('error1.tpl');
    }
    $arrpotion1 -> Close();
    if ($player->equip[1][0]) 
    {
        $arrarrows = $db -> Execute("SELECT * FROM equipment WHERE owner=".$player -> id." AND type='R' AND status='U'");
        $intTest = $arrarrows -> RecordCount();
        if ($intTest)
        {
            $smarty -> assign ("Message", "<input type=\"radio\" name=\"action\" value=\"use\"> ".NEW_QUIVER." <select name=\"arrows\">");
            $smarty -> display ('error1.tpl');
            while (!$arrarrows -> EOF) 
            {
                $smarty -> assign ("Message", "<option value=".$arrarrows -> fields['id'].">".$arrarrows -> fields['name']." ".POWER2.": ".$arrarrows -> fields['power']." ".AMOUNT.": ".$arrarrows -> fields['wt']."</option>");
                $smarty -> display ('error1.tpl');
                $arrarrows -> MoveNext();
            }
            $smarty -> assign ("Message", "</select><br /><br />");
            $smarty -> display ('error1.tpl');
        }
        $arrarrows -> Close();
    }
    if ($points > 1) 
    {
        $arrwep = $db -> Execute("SELECT * FROM equipment WHERE owner=".$player -> id." AND type='W' AND status='U'");
        $smarty -> assign ("Message", "<input type=\"radio\" name=\"action\" value=\"weapons\"> ".CHANGE_WEAPON." <select name=\"weapon\">");
        $smarty -> display ('error1.tpl');
        while (!$arrwep -> EOF) 
        {
            $smarty -> assign ("Message", "<option value=".$arrwep -> fields['id'].">".$arrwep -> fields['name']." ".POWER2.": ".$arrwep -> fields['power']."</option>");
            $smarty -> display ('error1.tpl');
            $arrwep -> MoveNext();
        }
        $arrwep -> Close();
        $arrwep1 = $db -> Execute("SELECT * FROM equipment WHERE owner=".$player -> id." AND type='B' AND status='U'");
        while (!$arrwep1 -> EOF) 
        {
            $smarty -> assign ("Message", "<option value=".$arrwep1 -> fields['id'].">".$arrwep1 -> fields['name']." ".POWER2.": ".$arrwep1 -> fields['power']."</option>");
            $smarty -> display ('error1.tpl');
            $arrwep1 -> MoveNext();
        }
        $arrwep1 -> Close();
        $smarty -> assign ("Message", "</select><br /><br />");
        $smarty -> display ('error1.tpl');
        if (($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') && $player->equip[0][0]) 
        {
            $smarty -> assign ("Message", "<input type=\"radio\" name=\"action\" value=\"battack\"> ".BATTLE_RAGE."<br /><br />");
            $smarty -> display ('error1.tpl');
        }
        if ($player -> clas == 'Mag') 
        {
            $arrspell = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND typ='B'");
            $strHtml = "<input type=\"radio\" name=\"action\" value=\"bspell\"> ".SPELL_BURST." ".$player->skills['magic'][1]." ".POWER3.")<select name=\"bspellboost\">";
            while (!$arrspell -> EOF) 
	      {
		if ($arrspell->fields['status'] == 'U')
		  {
		    $strHtml .= "<option value=".$arrspell -> fields['id'].">".$arrspell -> fields['nazwa']." ".POWER.": ".$arrspell -> fields['obr']."</option>";
		  }
		else
		  {
		    $strHtml .= '<option value='.$arrspell -> fields['id'].' selected="selected">'.$arrspell -> fields['nazwa']." ".POWER.": ".$arrspell -> fields['obr']."</option>";
		  }
                $arrspell -> MoveNext();
	      }
            $arrspell -> Close();
            $smarty -> assign ("Message", $strHtml."</select> <input type=\"text\" name=\"power\" size=\"5\" value=\"0\"><br /><br /><input type=\"radio\" name=\"action\" value=\"dspell\"> ".SPELL_BURST2." ".$player -> skills['magic'][1]." ".POWER3.") <input type=\"text\" name=\"power1\" size=\"5\" value=\"0\"><br /><br />");
            $smarty -> display ('error1.tpl');
        }
    }
    $rest = ($player -> stats['condition'][2] / 10);
    $smarty -> assign(array("Rest" => $rest, 
                            "Aescape" => A_ESCAPE,
                            "Arest" => A_REST,
                            "Aexhaust" => EXHAUST,
                            "Next" => S_FIGHT));
    $smarty -> display('turnfight1.tpl');
}

?>
