<?php
/**
 *   File functions:
 *   Turn fight players vs monsters
 *
 *   @name                 : turnfight.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 05.05.2012
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
    global $arrEquip;
    global $myunik;
    global $amount;
    global $myagility;
    global $intPoisoned;
    global $arrTags;

    $arrEquip = $player -> equipment();
    $player->curstats($arrEquip);
    $myczaro = $db -> Execute("SELECT * FROM czary WHERE status='E' AND gracz=".$player -> id." AND typ='O'");
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);

    $player->user = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];

    if ($fight -> fields['fight'] == 0 && $title == 'Arena Walk') 
      {
	error (NO_ENEMY);
      }
    $premia = 0;
    $zmeczenie = 0;
    if (empty ($enemy['id'])) 
    {
        $location = $db -> Execute("SELECT miejsce FROM players WHERE id=".$player -> id);
        if ($location -> fields['miejsce'] == 'Góry') 
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
            $enemy = array("strength" => $enemy1 -> fields['strength'], "agility" => $enemy1 -> fields['agility'], "speed" => $enemy1 -> fields['speed'], "endurance" => $enemy1 -> fields['endurance'], "hp" => $enemy1 -> fields['hp'], "name" => $enemy1 -> fields['name'], "exp1" => $enemy1 -> fields['exp1'], "exp2" => $enemy1 -> fields['exp2'], "level" => $enemy1 -> fields['level']);
            $enemy1 -> Close();
        }
        if ($location -> fields['miejsce'] == 'Las') 
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
            $enemy = array("strength" => $enemy1 -> fields['strength'], "agility" => $enemy1 -> fields['agility'], "speed" => $enemy1 -> fields['speed'], "endurance" => $enemy1 -> fields['endurance'], "hp" => $enemy1 -> fields['hp'], "name" => $enemy1 -> fields['name'], "exp1" => $enemy1 -> fields['exp1'], "exp2" => $enemy1 -> fields['exp2'], "level" => $enemy1 -> fields['level']);
            $enemy1 -> Close();
        }
    }
    if ($title == 'Arena Walk') 
    {
        $arrClass = array('Wojownik','Rzemieślnik', 'Złodziej', 'Barbarzyńca');
        if (in_array($player -> clas, $arrClass) && $myczaro -> fields['id']) 
        {
            error (ONLY_MAGE);
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
    if ($player -> clas == 'Wojownik'  || $player -> clas == 'Barbarzyńca') 
    {
        $enemy['damage'] = ($enemy['strength'] - ($player -> level + $player -> cond + $premia));
    } 
        else 
    {
        $enemy['damage'] = ($enemy['strength'] - ($player -> cond + $premia));
    }
    if ($myczaro -> fields['id']) 
    {
        $myczarobr = ($player -> wisdom * $myczaro -> fields['obr']) - (($myczaro -> fields['obr'] * $player -> wisdom) * ($arrEquip[3][4] / 100));
        if ($arrEquip[2][0]) 
        {
            $myczarobr = ($myczarobr - (($myczaro -> fields['obr'] * $player -> wisdom) * ($arrEquip[2][4] / 100)));
        }
        if ($arrEquip[4][0]) 
        {
            $myczarobr = ($myczarobr - (($myczaro -> fields['obr'] * $player -> wisdom) * ($arrEquip[4][4] / 100)));
        }
        if ($arrEquip[5][0]) 
        {
            $myczarobr = ($myczarobr - (($myczaro -> fields['obr'] * $player -> wisdom) * ($arrEquip[5][4] / 100)));
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
    if (!$arrEquip[3][0] && !$myczaro -> fields['id']) 
    {
        $enemy['damage'] = ($enemy['strength'] - $player -> cond);
    }
    $gmagia = 0;
    if (!isset($_SESSION['round']))
    {
        $_SESSION['round'] = 1;
    }
    $smarty -> assign ("Message", "<ul><li><b>".$player -> user."</b> ".VERSUS." <b>".$enemy['name']."</b><br />");
    $smarty -> display ('error1.tpl');
    /**
    * Count points in fight
    */
    if (!isset($_SESSION['points']) || $_SESSION['points'] == 0)
    {
        $_SESSION['points'] = ceil($player->speed / $enemy['speed']);
    }
    /**
    * Count dodge - player and monster
    */
    if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
    {
        $myunik = (($player->agility - $enemy['agility']) + $player -> level + $player -> miss);
        $eunik = (($enemy['agility'] - $player->agility) - ($player -> attack + $player -> level));
	if ($arrEquip[11][0])
	  {
	    $eunik -= ($player->attack / 5);
	  }
    }
    if ($player -> clas == 'Rzemieślnik' || $player -> clas == 'Złodziej' || $player -> clas == '') 
    {
        $myunik = ($player->agility - $enemy['agility'] + $player -> miss);
        $eunik = (($enemy['agility'] - $player->agility) - $player -> attack);
    }
    if ($player -> clas == 'Mag') 
    {
        $myunik = ($player->agility - $enemy['agility'] + $player -> miss);
        $eunik = (($enemy['agility'] - $player->agility) - ($player -> magic + $player -> level));
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
    $attacks = ceil($enemy['speed'] / $player->speed);
    if ($attacks > 5) 
    {
        $attacks = 5;
    }
    if (!isset($_POST['action'])) 
    {
        $_POST['action'] = '';
    }
    /**
     * If fight is longer than 24 rounds
     */
    if (isset($_SESSION['round']) && $_SESSION['round'] > 24)
    {
        $db -> Execute("UPDATE `players` SET `fight`=0, `hp`=".$player -> hp.", `bless`='', `blessval`=0 WHERE `id`=".$player -> id);
        unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['dodge']);
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
    if ($_POST['action'] == 'drink' && $_SESSION['points'] > 0) 
    {
        $_SESSION['points'] = $_SESSION['points'] - 1;
        drink ($_POST['potion2']);
        $objMana = $db -> Execute("SELECT pm FROM players WHERE id=".$player -> id);
        $player -> mana = $objMana -> fields['pm'];
        $objMana -> Close();
        if ($_SESSION['points'] >= $attacks && $player -> hp > 0) 
        {
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($_POST['action'] == 'use' && $_SESSION['points'] > 0) 
    {
        $_SESSION['points'] = $_SESSION['points'] - 1;
        if (!isset($_POST['arrows']))
        {
            $_POST['arrows'] = 0;
        }
        equip ($_POST['arrows']);
        $arrEquip = $player -> equipment();
        if ($_SESSION['points'] >= $attacks) 
        {
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($_POST['action'] == 'weapons' && $_SESSION['points'] > 0) 
    {
        $_SESSION['points'] = $_SESSION['points'] - 2;
        if (!isset($_POST['weapon']))
        {
            $_POST['weapon'] = 0;
        }
        equip ($_POST['weapon']);
        $arrEquip = $player -> equipment();
        if ($_SESSION['points'] >= $attacks) 
        {
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($_POST['action'] == 'escape') 
    {
        $chance = (rand(1, $player -> level * 100) + $player -> speed - $enemy['agility']);
        if ($chance > 0) 
        {
            $expgain = rand($enemy['exp1'],$enemy['exp2']);
            $expgain = (ceil($expgain / 100));
            $smarty -> assign ("Message", ESCAPE_SUCC." ".$enemy['name'].YOU_GAIN1." ".$expgain." ".EXP_PTS."<br /></li></ul>");
            $smarty -> display ('error1.tpl');
            checkexp($player -> exp,$expgain,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'',0);
            $db -> Execute("UPDATE players SET fight=0, bless='', blessval=0 WHERE id=".$player -> id);
            $points = $attacks * 2;
            $temp = 1;
            unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['dodge']);
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
        } 
            else 
        {
            $smarty -> assign ("Message", "<br />".ESCAPE_FAIL." ".$enemy['name']."!");
            $smarty -> display ('error1.tpl');
            monsterattack($attacks,$enemy,$myunik,$amount);
            if ($player -> hp > 0) 
            {
                $_SESSION['round'] = $_SESSION['round'] + 1;
                $_SESSION['points'] = ceil($player->speed / $enemy['speed']);
                fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
            }
        }
    }
    if ($_POST['action'] == 'cast' && $_SESSION['points'] > 0) 
    {
        $_SESSION['points'] = $_SESSION['points'] - 1;
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
    /**
     * Burst offensive spell
     */
    if ($_POST['action'] == 'bspell' && $_SESSION['points'] > 0) 
    {
        if (intval($_POST['power']) < 1) 
        {
            $smarty -> assign ("Message", ERROR);
            $smarty -> display ('error1.tpl');
            $_POST['power'] = 0;
        }
            else
        {
            if ($_POST['power'] > $player -> level)
            {
                $_POST['power'] = $player -> level;
            }
	    checkvalue($_POST['bspellboost']);
            $intSpelllevel = $db -> Execute("SELECT `gracz, `poziom` FROM `czary` WHERE `id`=".$_POST['bspellboost']);
	    if ($intSpelllevel->fields['gracz'] != $player->id)
	      {
		$intMaxburst = 0;
	      }
	    else
	      {
		$intMaxburst = $player -> mana - $intSpelllevel -> fields['poziom'];
	      }
            $intSpelllevel -> Close();
            if ($_POST['power'] > $intMaxburst)
            {
                $_POST['power'] = $intMaxburst;
            }
            $_SESSION['points'] = $_SESSION['points'] - 2;
            castspell($_POST['bspellboost'],$_POST['power'],$eunik);
        }
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
        if ($_POST['power1'] > $player -> level)
        {
            $_POST['power1'] = $player -> level;
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
            $_SESSION['points'] = ceil($player->speed / $enemy['speed']);
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($_POST['action'] == 'nattack' && $_SESSION['points'] > 0) 
    {
        attack($eunik,0);
        $temp = 0;
        for ($k=0;$k<$amount;$k++) 
        {
            $strIndex = "mon".$k;
            if ($_SESSION[$strIndex] > 0) 
            {
                $temp = $temp + 1;
            }
        }
        $_SESSION['points'] = $_SESSION['points'] - 1;
        if ($temp > 0 && $attacks <= $_SESSION['points']) 
        {
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($_POST['action'] == 'dattack' && $_SESSION['points'] > 0) 
    {
        $_SESSION['points'] = $_SESSION['points'] - 1;
        attack($eunik,3);
        $temp = 0;
        for ($k=0;$k<$amount;$k++) 
        {
            $strIndex = "mon".$k;
            if ($_SESSION[$strIndex] > 0) 
            {
                $temp = $temp + 1;
            }
        }
        $myunik = $myunik + ($myunik / 2);
        if ($temp > 0 && $attacks <= $_SESSION['points']) 
        {
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($_POST['action'] == 'aattack' && $_SESSION['points'] > 0) 
    {
        $_SESSION['points'] = $_SESSION['points'] - 1;
        attack($eunik,1);
        $temp = 0;
        for ($k=0;$k<$amount;$k++) 
        {
            $strIndex = "mon".$k;
            if ($_SESSION[$strIndex] > 0) 
            {
                $temp = $temp + 1;
            }
        }
        $myunik = $myunik / 2;
        if ($temp > 0 && $attacks <= $_SESSION['points']) 
        {
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($_POST['action'] == 'battack' && $_SESSION['points'] > 0) 
    {
        $_SESSION['points'] = $_SESSION['points'] - 2;
        attack($eunik,2);
        $temp = 0;
        for ($k=0;$k<$amount;$k++) 
        {
            $strIndex = "mon".$k;
            if ($_SESSION[$strIndex] > 0) 
            {
                $temp = $temp + 1;
            }
        }
        $myunik = 0;
        if ($temp > 0 && $attacks <= $_SESSION['points']) 
        {
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
            $_SESSION['points'] = ceil($player->speed / $enemy['speed']);
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
            $rest = ($player -> cond / 10);
            $zmeczenie = $zmeczenie - $rest;
            if ($zmeczenie < 0) 
            {
                $zmeczenie = 0;
            }
            $_SESSION['exhaust'] = $zmeczenie;
            $_SESSION['round'] = $_SESSION['round'] + 1;
            $_SESSION['points'] = ceil($player->speed / $enemy['speed']);
            if ($_SESSION['points'] > 5) 
            {
                $_SESSION['points'] = 5;
            }
            fightmenu($_SESSION['points'],$zmeczenie,$_SESSION['round'],$addres);
        }
    }
    if ($player -> hp <= 0) 
    {
        if ($title != 'Arena Walk') 
	  {
	    loststat($player -> id, $player->oldstats, 0, $enemy['name'], 0, $player->antidote);
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
        unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['dodge']);
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
        checkexp($player -> exp,$expgain,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'',0);
        if ($player -> hp < 0) 
        {
            $player -> hp = 0;
        }
	if (($player->hp > 0) && ($player->autodrink != 'N'))
	  {
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
        if ($title == 'Arena Walk') 
        {
            $smarty -> assign ("Message", "</ul><ul><li><b>".B_OPTIONS."</a><br /></li></ul>");
            $smarty -> display ('error1.tpl');
        }
        $db -> Execute("UPDATE players SET hp=".$player -> hp.", fight=0, bless='', blessval=0 WHERE id=".$player -> id);
	if (isset($_SESSION['razy']))
	  {
	    unset($_SESSION['razy']);
	  }
        unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['dodge']);
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
    $gwtbr = 0;
    $gatak = 0;
    if (isset ($_SESSION['exhaust'])) 
    {
        $zmeczenie = $_SESSION['exhaust'];
    }
    $arrEquip = $player -> equipment();
    if (!$arrEquip[0][0] && !$arrEquip[1][0]) 
    {
        $smarty -> assign("Message", NO_WEAPON);
        $smarty -> display('error1.tpl');
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
        } 
            else 
        {
            $stat['damage'] = ($player -> strength + $arrEquip[0][2]);
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
        $name = "bronią";
	$strAtype = 'melee';
    }
    if ($arrEquip[11][0])
      {
	$stat['damage'] += (($arrEquip[11][2] + $player->strength) + $player->level);
	$name = "obiema brońmi";
      }
    if ($arrEquip[1][0]) 
    {
        $bonus = $arrEquip[1][2] + $arrEquip[6][2];
	if ($arrEquip[6][3] == 'D')
	  {
	    $bonus += $arrEquip[6][8];
	  }
        $bonus2 = (($player  -> strength / 2) + ($player->agility / 2));
        if ($player -> clas == 'Wojownik'  || $player -> clas == 'Barbarzyńca') 
        {
            $stat['damage'] = (($bonus2 + $bonus) + $player -> level);
        } 
            else 
        {
            $stat['damage'] = ($bonus2 + $bonus);
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
        $name = "strzałą";
	$strAtype = 'ranged';
    }
    if (!isset($stat['damage']))
    {
        $stat['damage'] = 0;
    }
    if ($player -> clas == 'Rzemieślnik')
    {
        $stat['damage'] = $stat['damage'] - ($stat['damage'] / 4);
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
    $rzut2 = (rand(1,($player -> level * 10)));
    $stat['damage'] = ($stat['damage'] + $rzut2);
    $stat['damage'] = ($stat['damage'] - $enemy['endurance']);
    if ($stat['damage'] < 1) 
    {
        $stat['damage'] = 0;
    }
    if ($arrEquip[0][0] && $gwtbr > $arrEquip[0][6]) 
      {
	$stat['damage'] = 0;
	if ($arrEquip[11][6] > $gwtbr)
	  {
	    $stat['damage'] += (($arrEquip[11][2] + $player->strength) + $player->level);
	  }
	$krytyk = 1;
      }
    if ($arrEquip[11][0] && $gwtbr > $arrEquip[11][6]) 
      {
	$stat['damage'] = 0;
	if ($arrEquip[0][6] > $gwtbr)
	  {
	    $stat['damage'] += (($arrEquip[0][2] + $player->strength) + $player->level);
	  }
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
    $ehp = $_SESSION[$number];
    if ($ehp <= 0)
    {
        $smarty -> assign("Message", ERROR);
        $smarty -> display('error1.tpl');
        $gwtbr = $gwtbr + 1;
    }
    if ($arrEquip[1][0]) 
    {
        $eunik = $eunik * 2;
    }
    if ($ehp > 0 && $player -> hp > 0) 
    {
        if ($arrEquip[0][0]) 
        {
            $zmeczenie = $zmeczenie + $arrEquip[0][4];
        } 
	elseif ($arrEquip[1][0]) 
        {
            $zmeczenie = $zmeczenie + $arrEquip[1][4];
        }
	if ($arrEquip[11][0])
	  {
	    $zmeczenie += $arrEquip[11][4];
	  }
        $szansa = rand(1, 100);
        if ($eunik >= $szansa && $szansa < 97) 
        {
            $smarty -> assign ("Message", "<b>".$enemy['name']."</b> ".ENEMY_DODGE."!<br />");
            $smarty -> display ('error1.tpl');
            if ($arrEquip[1][0]) 
            {
                $gwtbr = ($gwtbr + 1);
            }
        } 
	elseif ($zmeczenie <= $player -> cond) 
        {
            if ($arrEquip[0][0] || $arrEquip[1][0] || $arrEquip[11][0]) 
	      {
		$gwtbr++;
		$rzut = rand(1, 1000) / 10;
		$intRoll = rand(1, 100);
		$arrLocations = array('w tułów', 'w głowę', 'w kończynę');
		$intHit = rand(0, 2);
		if ($krytyk >= $rzut && $intRoll <= $krytyk && $player->fight != 999) 
		  {
		    $gatak++;
		    $ehp = 0;
		    $smarty->assign("Message", showcritical($arrLocations[$intHit], $strAtype, 'pve', $enemy['name']));
		  }
		else
		  {
		    $ehp -= $stat['damage'];
		    $smarty -> assign ("Message", YOU_ATTACK1." ".$name." <b>".$enemy['name']."</b> ".$arrLocations[$intHit]." ".INFLICT." <b>".$stat['damage']."</b> ".DAMAGE."! (".$ehp." ".LEFT.")</font><br />");
		    if ($stat['damage'] > 0) 
		      {
			$gatak = ($gatak + 1);
		      }
		  }
		$smarty -> display ('error1.tpl');
	      }
        }
    }
    $_SESSION[$number] = $ehp;
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
    $_SESSION['exhaust'] = $zmeczenie;
}

/**
* Attack on monster by spell
*/
function castspell ($id,$boost,$eunik) 
{
    global $smarty;
    global $player;
    global $db;
    global $arrEquip;
    global $enemy;
    global $amount;
    $number1 = $_POST['monster'] - 1;
    $number = "mon".$number1;
    $gmagia = 0;
    $mczar = $db -> Execute("SELECT * FROM czary WHERE id=".$id);
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
        if ($boost) 
        {
            $stat['damage'] = $stat['damage'] + $boost;
        }
    }
    if (!isset($stat['damage']))
    {
        $stat['damage'] = 0;
    }
    $rzut2 = (rand(1,($player -> level * 10)));
    $stat['damage'] = ($stat['damage'] + $rzut2);
    $stat['damage'] = ($stat['damage'] - $enemy['endurance']);
    if ($stat['damage'] < 1) 
    {
        $stat['damage'] = 0 ;
    }
    if ($player -> mana < $mczar -> fields['poziom']) 
    {
         $stat['damage'] = 0;
    }
    $ehp = $_SESSION[$number];
    if ($ehp <= 0)
    {
        $smarty -> assign("Message", ERROR);
        $smarty -> display('error1.tpl');
        $lost_mana = ceil($mczar -> fields['poziom'] / 2.5) + $boost;
        $lost_mana = $lost_mana - (int)($player -> magic / 25);
        if ($lost_mana < 1)
        {
            $lost_mana = 1;
        }
        $player -> mana = ($player -> mana - $lost_mana);
    }
    if ($ehp > 0) 
    {
        $szansa = rand(1, 100);
        if ($eunik >= $szansa && $szansa < 97) 
        {
            $smarty -> assign ("Message", "<b>".$enemy['name']."</b> ".ENEMY_DODGE."<br />");
            $smarty -> display ('error1.tpl');
        } 
	else 
        {
            if ($mczar -> fields['id'] && $player -> mana > $mczar -> fields['poziom']) 
            {
                $pech = floor($player -> magic - $mczar -> fields['poziom']);
                if ($pech > 0) 
                {
                    $pech = 0;
                }
                $pech = ($pech + rand(1,100));
                if ($pech > 5) 
                {
                    $lost_mana = ceil($mczar -> fields['poziom'] / 2.5) + $boost;
                    $lost_mana = $lost_mana - (int)($player -> magic / 25);
                    if ($lost_mana < 1)
                    {
                        $lost_mana = 1;
                    }
                    $player -> mana = ($player -> mana - $lost_mana);
		    $rzut = rand(1, 1000) / 10;
		    $intRoll = rand(1, 100);
		    if ($krytyk >= $rzut && $intRoll <= $krytyk && $player->fight != 999) 
		      {
			$gmagia++;
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
			    $gmagia++;
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
                        $player -> mana = ($player -> mana - $mczar -> fields['poziom']);
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
                        $player -> hp = ($player -> hp - $stat['damage']);
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
			$player->mana -= $mczar -> fields['poziom'];
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
			$player->mana -= $mczar -> fields['poziom'];
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
            }
        }
        gainability($player -> id,$player -> user,0,0,$gmagia,$player -> mana,$player -> id,'');
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
    global $arrEquip;
    global $zmeczenie;
    global $number;
    if (isset ($_SESSION['exhaust'])) 
    {
        $zmeczenie = $_SESSION['exhaust'];
    }
    $gunik = 0;
    $gwt = array(0,0,0,0);
    $temp = 0;
    for ($k=0;$k<$amount;$k++) 
    {
        $strIndex = "mon".$k;
        if ($_SESSION[$strIndex] > 0) 
        {
            $temp = $temp + 1;
        }
    }
    $amount = $temp;
    $armor = checkarmor($arrEquip[3][0], $arrEquip[2][0], $arrEquip[4][0], $arrEquip[5][0]);
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
    for ($j = 1;$j <= $amount;++$j) 
    {
        $ename = $enemy['name']." nr".$j;
        for ($i = 1;$i <= $attacks; ++$i) 
        {
            $rzut1 = (rand(1,($enemy['level'] * 10)));
            $intDamage = ($enemy['damage'] + $rzut1);
            if ($intDamage < 1) 
            {
                $intDamage = 1;
            }
            if ($player -> mana < $myczaro -> fields['poziom']) 
            {
                $intDamage = $enemy['strength'];
            }
            if ($zmeczenie > $player -> cond) 
            {
                $intDamage = $enemy['strength'];
            }
	    $blnMiss = FALSE;
            if ($player -> hp > 0) 
            {
                $szansa = rand(1, 100);
                if ($myunik >= $szansa && $zmeczenie < $player -> cond && $szansa < 97) 
                {
                    $smarty -> assign ("Message", "<br>".YOU_DODGE." <b>".$ename."</b>!");
                    $smarty -> display ('error1.tpl');
                    $gunik = ($gunik + 1);
                    $zmeczenie = ($zmeczenie + $arrEquip[3][4] + 1);
		    $blnMiss = TRUE;
                } 
		//Player block attack with shield
		$szansa = rand(1, 100);
		if ($szansa <= $intBlock && !$blnMiss)
		  {
		    $smarty -> assign ("Message", "<br>Zablokowałeś tarczą atak <b>".$ename."</b>!");
                    $smarty -> display ('error1.tpl');
		    $zmeczenie = ($zmeczenie + $arrEquip[5][4] + 1);
		    $gwt[3]++;
		    $blnMiss = TRUE;
		  }
                if (!$blnMiss)
                {
                    $player -> hp = ($player -> hp - $intDamage);
                    $db -> Execute("UPDATE `players` SET `hp`=".$player -> hp." WHERE `id`=".$player -> id);
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
                    $smarty -> assign ("Message", "<br><b>".$ename."</b> ".ENEMY_HIT2.$arrLocations[$intHit]." <b>".$intDamage."</b> obrażeń! (".$player -> hp." zostało)");
                    $smarty -> display ('error1.tpl');
                    if ($myczaro -> fields['id'] && $player -> mana >= $myczaro -> fields['poziom']) 
                    {
                        $lost_mana = ceil($myczaro -> fields['poziom'] / 2.5);
                        $lost_mana = $lost_mana - (int)($player -> magic / 25);
                        if ($lost_mana < 1)
                        {
                            $lost_mana = 1;
                        }
                        $player -> mana = ($player -> mana - $lost_mana);
                    }
                }
            }
        }
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
      lostitem($gwt[3], $arrEquip[5][6], YOU_SHIELD, $player -> id, $arrEquip[5][0], $player -> id, HAS_BEEN1, $player->level);
    }
    $intDamount = 0;
    if ($gunik)
    {
        if (!isset($_SESSION['dodge']))
        {
            $_SESSION['dodge'] = $gunik;
        }
            else
        {
            $_SESSION['dodge'] = $_SESSION['dodge'] + $gunik;
        }
        /**
         * Count gained dodge skill
         */
        $intNewfib = 1;
        $intOldfib = 1;
        $intTempfib = 1;
        while ($intNewfib)
        {
            $_SESSION['dodge'] = $_SESSION['dodge'] - $intNewfib;
            if ($_SESSION['dodge'] < 0)
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
    }
    gainability($player -> id, $player -> user, $intDamount, 0, 0, $player -> mana, $player -> id, '');
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
    global $arrEquip;
    global $amount;
    global $enemy;
    $smarty -> assign(array("Round" => $round, 
                            "Points" => $points, 
                            "Mana" => $player -> mana, 
                            "HP" => $player -> hp, 
                            "Exhaust" => $exhaust, 
                            "Cond" => $player -> cond, 
                            "Adres" => $addres,
                            "Fround" => F_ROUND,
                            "Actionpts" => ACTION_PTS,
                            "Lifepts" => LIFE_PTS,
                            "Exhausted" => EXHAUSTED,
                            "Quiver" => '',
                            "Arramount" => ''));
    if ($arrEquip[6][0])
    {
        $objQuiver = $db -> Execute("SELECT `wt` FROM `equipment` WHERE `id`=".$arrEquip[6][0]);
        $smarty -> assign(array("Quiver" => QUIVER,
                                "Arramount" => $objQuiver -> fields['wt']));
        $objQuiver -> Close();
    }
    if ($arrEquip[0][0] || $arrEquip[1][0]) 
    {
        if ($player -> clas != 'Rzemieślnik')
        {
            $smarty -> assign(array("Dattack" => "<input type=\"radio\" name=\"action\" value=\"dattack\"> ".D_ATTACK."<br /><br />",
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
    if ($arrEquip[1][0]) 
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
        if (($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') && $arrEquip[0][0]) 
        {
            $smarty -> assign ("Message", "<input type=\"radio\" name=\"action\" value=\"battack\"> ".BATTLE_RAGE."<br /><br />");
            $smarty -> display ('error1.tpl');
        }
        if ($player -> clas == 'Mag') 
        {
            $arrspell = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND typ='B'");
            $strHtml = "<input type=\"radio\" name=\"action\" value=\"bspell\"> ".SPELL_BURST." ".$player -> level." ".POWER3.")<select name=\"bspellboost\">";
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
            $smarty -> assign ("Message", $strHtml."</select> <input type=\"text\" name=\"power\" size=\"5\" value=\"0\"><br /><br /><input type=\"radio\" name=\"action\" value=\"dspell\"> ".SPELL_BURST2." ".$player -> level." ".POWER3.") <input type=\"text\" name=\"power1\" size=\"5\" value=\"0\"><br /><br />");
            $smarty -> display ('error1.tpl');
        }
    }
    $rest = ($player -> cond / 10);
    $smarty -> assign(array("Rest" => $rest, 
                            "Aescape" => A_ESCAPE,
                            "Arest" => A_REST,
                            "Aexhaust" => EXHAUST,
                            "Next" => S_FIGHT));
    $smarty -> display('turnfight1.tpl');
}

?>
