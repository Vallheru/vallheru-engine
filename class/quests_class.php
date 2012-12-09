<?php
/**
 *   File functions:
 *   Quest class - actions in quest
 *
 *   @name                 : quests_class.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 09.12.2012
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

class Quests 
{
    var $number;
    var $location;

    /**
    * Class constructor - start quest and show first text, check for phishing
    */
    function Quests($location, $number, $action) 
    {
        global $db;
        global $smarty;
        global $player;
        
        if (!isset($_SERVER['HTTP_REFERER']))
        {
            error("Zapomnij o tym.");
        }
        $arrAddress = explode("/", $_SERVER['HTTP_REFERER']);
        if ($_SERVER['HTTP_HOST'] != $arrAddress[2])
	  {
	    error("Zapomnij o tym.");
	  }
        if (!$action || $action == 'start') 
        {
            $text = $db -> Execute("SELECT `text` FROM `quests` WHERE `qid`=".$number." AND `location`='".$location."' AND `name`='start'");
            $smarty -> assign("Start", $text -> fields['text']);
            $text -> Close();
            $db -> Execute("UPDATE `players` SET `miejsce`='Podróż' WHERE `id`=".$player -> id);
            $test = $db -> Execute("SELECT `id` FROM `questaction` WHERE `player`=".$player -> id." AND `quest`=".$number);
            if (empty($test -> fields['id'])) 
            {
                $db -> Execute("INSERT INTO `questaction` (`player`, `action`, `quest`) VALUES(".$player -> id.",'start',".$number.")");
            }
            $test -> Close();
        }
        if ($action == 'end') 
        {
            error("Już byłeś na tej przygodzie!");
        }           
        $smarty -> assign(array("Aselect" => "Wybierz",
                                "Anext" => "Zatwierdź",
                                "Addanswer" => "Podaj odpowiedź"));
        $this -> number = $number;
        $this -> location = $location;
    }

    /**
    * Checkbox for player actions
    */
    function Box($num) 
    {
        global $db;
        global $smarty;

        $name = "box".$num;
        $box = $db -> Execute("SELECT text FROM quests WHERE qid=".$this -> number." AND location='".$this -> location."' AND name='".$name."'");
        $arrtext = array();
        $arroption = array();
        $i = 0;
        while (!$box -> EOF) 
        {
            $arrtext[$i] = $box -> fields['text'];
            $arroption[$i] = $i + 1;
            $i = $i + 1;
            $box -> MoveNext();
        }
        $box -> Close();
        $smarty -> assign( array("Box" => $arrtext, "File" => $this -> location, "Name" => $name, "Option" => $arroption));
    }

    /**
    * Show quest text
    */
    function Show($num) 
    {
        global $db;
        global $smarty;
        global $player;
        global $city1;
        global $city1a;
        global $city1b;
        global $city2;

        $db -> Execute("UPDATE `questaction` SET `action`='".$num."' WHERE `player`=".$player -> id." AND `quest`=".$this -> number);
        $text = $db -> Execute("SELECT `text` FROM `quests` WHERE `qid`=".$this -> number." AND `location`='".$this -> location."' AND `name`='".$num."'");
        $arrSearch = array("city1a", "city1b", "city2", "city1");
        $arrReplace = array($city1a, $city1b, $city2, $city1);
        $strText = str_replace($arrSearch, $arrReplace, $text -> fields['text']);
        $smarty -> assign("Text", $strText);
        $text -> Close();
    }

    /**
    * Finish quest
    */
    function Finish($exp, $arrStats, $arrSkills = array()) 
    {
        global $db;
        global $smarty;
        global $player;
        global $city1;
	global $lang;

        $db -> Execute("UPDATE `questaction` SET `action`='end' WHERE `player`=".$player -> id." AND `quest`=".$this -> number);
	$intQuests = count(glob('quests/*.php'));
	$objPlquests = $db->Execute("SELECT count(`id`) FROM `questaction` WHERE `player`=".$player->id." AND `action`='end'");
	if ($intQuests == $objPlquests->fields['count(`id`)'])
	  {
	    $db->Execute("DELETE FROM `questaction` WHERE `player`=".$player->id);
	  }
	$objPlquests->Close();
        $db -> Execute("UPDATE `players` SET `miejsce`='Altara' WHERE `id`=".$player -> id);
        if ($exp > 0) 
        {
            $text =  "Dostajesz ".$exp." Punktów Doświadczenia.";
	    $intDiv = count($arrStats) + count($arrSkills);
	    if (count($arrStats))
	      {
		$arrStats2 = array();
		foreach ($arrStats as $strStat)
		  {
		    $arrStats2[$strStat] = ceil($exp / $intDiv);
		  }
		$player->checkexp($arrStats2, $player->id, 'stats');
	      }
	    if (count($arrSkills))
	      {
		$arrSkills2 = array();
		foreach ($arrSkills as $strSkill)
		  {
		    $arrSkills2[$strSkill] = ceil($exp / $intDiv);
		  }
		$player->checkexp($arrSkill2, $player->id, 'skills');
	      }
        } 
            else 
        {
            $text = '';
        }
        $smarty -> assign("End", $text."<br /><br />(<a href=\"city.php\">".$city1."</a>)");
    }

    /**
    * Turn fight with monsters in quest
    */
    function Battle($adress) 
    {
        global $player;
        global $smarty;
        global $enemy;
        global $arrehp;
        global $db;
        global $newdate;
	global $lang;

        require_once('includes/turnfight.php');
        require_once('includes/funkcje.php');

        $fight = $db -> Execute("SELECT `fight` FROM `players` WHERE `id`=".$player -> id);
        $enemy1 = $db -> Execute("SELECT * FROM `monsters` WHERE `id`=".$fight -> fields['fight']);
        $enemy = array("strength" => $enemy1 -> fields['strength'], 
		       "agility" => $enemy1 -> fields['agility'], 
		       "speed" => $enemy1 -> fields['speed'], 
		       "endurance" => $enemy1 -> fields['endurance'], 
		       "hp" => $enemy1 -> fields['hp'], 
		       "name" => $enemy1 -> fields['name'], 
		       "level" => $enemy1 -> fields['level'],
		       "lootnames" => $enemy1->fields['lootnames'],
		       "lootchances" => $enemy1->fields['lootchances'],
		       "resistance" => explode(";", $enemy1->fields['resistance']),
		       "dmgtype" => $enemy1->fields['dmgtype']);
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
        /**
        * Count gained experience
        */
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
	$expgain1 = ceil($intElevel * $span);
	$expgain = $expgain1;
        if (isset($_POST['razy']) && $_POST['razy'] > 1)
        {
            for ($k = 2; $k <= $_POST['razy']; $k++)
            {
                $expgain = $expgain + ceil($expgain1 / 5 * (sqrt($k) + 4.5));
            }
        }
	$goldgain = ceil($intElevel * $span);
        $enemy1 -> Close();        
        $arrehp = array ();
        if (!isset ($_POST['action'])) 
        {
            turnfight ($expgain,$goldgain,'',$adress);
        } 
            else 
        {
            turnfight ($expgain,$goldgain,$_POST['action'],$adress);
        }
        if ($fight -> fields['fight'] == 0) 
        {
            $player -> energy = $player -> energy - 1;
            $db -> Execute("UPDATE players SET energy=".$player -> energy." WHERE id=".$player -> id);
       }
       $fight -> Close();
   }

   /**
   * Gain experience in quest
   */
   function Gainexp($exp, $arrStats, $arrSkills = array()) 
   {
        global $db;
        global $smarty;
        global $player;
	global $lang;
        
	$text =  "Dostajesz ".$exp." Punktów Doświadczenia.";
        $intDiv = count($arrStats) + count($arrSkills);
	if (count($arrStats))
	  {
	    $arrTmp = array();
	    foreach ($arrStats as $strStat)
	      {
		$arrTmp[$strStat] = ceil($exp / $intDiv);
	      }
	    $player->checkexp($arrTmp, $player->id, 'stats');
	  }
	if (count($arrSkills))
	  {
	    $arrTmp = array();
	    foreach ($arrSkills as $strSkill)
	      {
		$arrTmp[$strSkill] = ceil($exp / $intDiv);
	      }
	    $player->checkexp($arrTmp, $player->id, 'skills');
	  }
        $smarty -> assign("End", "<br /><br />".$text);        
    }

    /**
    * Answer on questions in quest
    */
    function Answer($num,$answfalse,$repeat) 
    {
        global $db;
        global $player;
        global $smarty;

        if (!isset($_POST['planswer'])) 
        {
            $_POST['planswer'] = '';
        }
        $_POST['planswer'] = strip_tags($_POST['planswer']);
        $ans = $db -> Execute("SELECT * FROM quests WHERE qid=".$this -> number." AND location='".$this -> location."' AND name='".$num."'");
        if ($repeat == 'Y') 
        {
            $db -> Execute("UPDATE players SET temp=temp-1 WHERE id=".$player -> id);
        }
        $_POST['planswer'] = strtolower($_POST['planswer']);
        $ans -> fields['option'] = strtolower($ans -> fields['option']);
        if (isset($_POST['planswer']) && $_POST['planswer'] == $ans -> fields['option']) 
        {
            return 1;
        } 
            elseif (isset($_POST['planswer']) && $_POST['planswer'] != $ans -> fields['option']) 
        {
            if (!empty($answfalse)) 
            {
                $text = $db -> Execute("SELECT text FROM quests WHERE qid=".$this -> number." AND location='".$this -> location."' AND name='".$answfalse."'");
                $smarty -> assign("Link", $text -> fields['text']);
                $text -> Close();
            }
            return 0;               
        }
    }

    /**
    * Resign from quest
    */
    function Resign() 
    {
        global $db;
        global $player;
        global $smarty;
        $db -> Execute("DELETE FROM questaction WHERE player=".$player -> id." AND quest=".$this -> number);
        $db -> Execute("UPDATE players SET miejsce='Altara' WHERE id=".$player -> id);
        $smarty -> assign("End", "(<a href=\"grid.php\">Labirynt</a>)");
    }
}
?>
