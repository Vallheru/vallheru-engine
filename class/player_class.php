<?php
/**
 *   File functions:
 *   Class with information about player and making some things with player (e.g. atributes in array)
 *
 *   @name                 : player_class.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 04.01.2013
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

class Player 
{
    var $user;
    var $id;
    var $hp;
    var $max_hp;
    var $mana;
    var $energy;
    var $max_energy;
    var $credits;
    var $bank;
    var $platinum;
    var $tribe;
    var $rank;
    var $location;
    var $ap;
    var $race;
    var $clas;
    var $pw;
    var $wins;
    var $losses;
    var $lastkilled;
    var $lastkilledby;
    var $age;
    var $logins;
    var $ip;
    var $gg;
    var $avatar;
    var $tribe_rank;
    var $immunited;
    var $corepass;
    var $trains;
    var $fight;
    var $deity;
    var $maps;
    var $rest;
    var $page;
    var $profile;
    var $gender;
    var $antidote;
    var $poll;
    var $vallars;
    var $newbie;
    var $forumtime;
    var $tforumtime;
    var $metallurgy;
    var $revent;
    var $room;
    var $oldstats;
    var $settings;
    var $chattimes;
    var $oldskills;
    // Player equipment
    var $equip;
    // Player statistics
    var $stats;
    // Player skills
    var $skills;
    // Player bonuses from AP
    var $bonuses;
    // Player max mana
    var $maxmana;
    // Player battle pet
    var $pet;
    // Room invitation
    var $rinvite;
    // Team invitation
    var $tinvite;
    // Player team
    var $team;
    // Player reputation
    var $reputation;
    
/**
* Class constructor - get data from database and write it to variables
*/
    function Player($pid) 
    {
        global $db;
	$pid = intval($pid);
        $stats = $db -> Execute("SELECT * FROM `players` WHERE `id`=".$pid);
        if ($stats -> fields['id'] != $pid) 
	  {
	    return FALSE;
	  }
        $this -> user = $stats -> fields['user'];
        $this -> id = $stats -> fields['id'];
        $this -> hp = $stats -> fields['hp'];
        $this -> max_hp = $stats -> fields['max_hp'];
        $this -> mana = $stats -> fields['pm'];
        $this -> energy = $stats -> fields['energy'];
        $this -> max_energy = $stats -> fields['max_energy'];
        $this -> credits = $stats -> fields['credits'];
        $this -> bank = $stats -> fields['bank'];
        $this -> platinum = $stats -> fields['platinum'];
        $this -> tribe = $stats -> fields['tribe'];
        $this -> rank = $stats -> fields['rank'];
        $this -> location = $stats -> fields['miejsce'];
        $this -> ap = $stats -> fields['ap'];
        $this -> race = $stats -> fields['rasa'];
        $this -> clas = $stats -> fields['klasa'];
        $this -> pw = $stats -> fields['pw'];
        $this -> wins = $stats -> fields['wins'];
        $this -> losses = $stats -> fields['losses'];
        $this -> lastkilled = $stats -> fields['lastkilled'];
        $this -> lastkilledby = $stats -> fields['lastkilledby'];
        $this -> age = $stats -> fields['age'];
        $this -> logins = $stats -> fields['logins'];
        $this -> ip = $stats -> fields['ip'];
        $this -> gg = $stats -> fields['gg'];
        $this -> avatar = $stats -> fields['avatar'];
        $this -> tribe_rank = $stats -> fields['tribe_rank'];
        $this -> immunited = $stats -> fields['immu'];
        $this -> corepass = $stats -> fields['corepass'];
        $this -> trains = $stats -> fields['trains'];
        $this -> fight = $stats -> fields['fight'];
        $this -> deity = $stats -> fields['deity'];
        $this -> maps = $stats -> fields['maps'];
        $this -> rest = $stats -> fields['rest'];
        $this -> page = $stats -> fields['page'];
        $this -> profile = $stats -> fields['profile'];
        $this -> gender = $stats -> fields['gender'];
        if (!empty($stats -> fields['antidote']))
        {
            $this -> antidote = $stats -> fields['antidote'];
        }
            else
        {
            $this -> antidote = '';
        }
        $this -> poll = $stats -> fields['poll'];
	$this->vallars = $stats->fields['vallars'];
	$this->newbie = $stats->fields['newbie'];
	$this->forumtime = $stats->fields['forum_time'];
	$this->tforumtime = $stats->fields['tforum_time'];
	$objRevent = $db->Execute("SELECT `state` FROM `revent` WHERE `pid`=".$pid);
	if (!$objRevent->fields['state'])
	  {
	    $this->revent = 0;
	  }
	else
	  {
	    $this->revent = $objRevent->fields['state'];
	  }
	$objRevent->Close();
	$this->room = $stats->fields['room'];
	$this->chattimes = $stats->fields['chattimes'];
	$this->rinvite = $stats->fields['rinvite'];
	$this->tinvite = $stats->fields['tinvite'];
	$this->team = $stats->fields['team'];
	$this->reputation = $stats->fields['reputation'];
	$this->settings = $this->toarray($stats->fields['settings']);
	$this->stats = $this->toarray($stats->fields['stats'], 'stats');
	$this->skills = $this->toarray($stats->fields['skills'], 'stats');
	$this->bonuses = array();
	if ($stats->fields['bonuses'] != '')
	  {
	    $arrBonuses = explode(';', $stats->fields['bonuses']);
	    foreach ($arrBonuses as $key => $strBonus)
	      {
		if ($strBonus == '')
		  {
		    break;
		  }
		$this->bonuses[$key] = explode(',', $strBonus);
	      }
	  }
	else
	  {
	    $this->bonuses = array();
	  }
	$this->oldskills = $this->skills;
	$this->oldstats = $this->stats;
	$this->equip = $this->equipment();
	$this->curstats();
	$this->skills['perception'][1] += $this->checkbonus('seeker');
	$stats -> Close();
	$objPet = $db->Execute("SELECT `id`, `power`, `defense` FROM `core` WHERE `owner`=".$this->id." AND `active`='B'");
	if (!$objPet->fields['id'])
	  {
	    $this->pet = array(0, 0, 0);
	  }
	else
	  {
	    $this->pet = array($objPet->fields['id'], $objPet->fields['power'], $objPet->fields['defense']);
	  }
	$objPet->Close();
    }

    /**
     * Function convert string value to array
     */
    function toarray($strValue, $strType = 'settings')
    {
      $arrTmp = explode(';', $strValue);
      $arrValues = array();
      foreach ($arrTmp as $strField)
	{
	  $arrTmp2 = explode(':', $strField);
	  if ($arrTmp2[0] == '')
	    {
	      continue;
	    }
	  if ($strType == 'settings')
	    {
	      $arrValues[$arrTmp2[0]] = $arrTmp2[1];
	    }
	  else
	    {
	      $arrValues[$arrTmp2[0]] = explode(',', $arrTmp2[1]);
	    }
	}
      return $arrValues;
    }

    /**
     * Function convert array values to string
     */
    function tostring($arrValues, $strType = 'settings')
    {
      $strValue = '';
      foreach ($arrValues as $key => $value)
	{
	  if ($strType == 'settings')
	    {
	      $strValue .= $key.':'.$value.';';
	    }
	  else
	    {
	      $strValue .= $key.':'.implode(',', $value).';';
	    }
	}
      return $strValue;
    }

    /**
     * Function return modified player stats
     */
    function curstats()
    {
      global $db;

      //Add bonuses from equipment
      foreach ($this->equip as $arrEquip)
	{
	  if ($arrEquip[0])
	    {
	      $this->stats['agility'][2] += ($arrEquip[5] * -1);
	    }
	}
      if ($this->equip[1][0])
	{
	  $this->stats['speed'][2] += $this->equip[1][7];
	}
      $arrStats = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition');
      //Add bonuses from rings
      if ($this->equip[9][2])
	{
	  $arrRings = array("zręczności", "siły", "inteligencji", "woli", "szybkości", "kondycji");
	  $arrRingtype = explode(" ", $this->equip[9][1]);
	  $intAmount = count($arrRingtype) - 1;
	  $intKey = array_search($arrRingtype[$intAmount], $arrRings);
	  $this->stats[$arrStats[$intKey]][2] += $this->equip[9][2];
	}
      if ($this->equip[10][2])
	{
	  $arrRings = array("zręczności", "siły", "inteligencji", "woli", "szybkości", "kondycji");
	  $arrRingtype = explode(" ", $this->equip[10][1]);
	  $intAmount = count($arrRingtype) - 1;
	  $intKey = array_search($arrRingtype[$intAmount], $arrRings);
	  $this->stats[$arrStats[$intKey]][2] += $this->equip[10][2];
	}
      //Add bonuses from bless
      $objBless = $db -> Execute("SELECT `bless`, `blessval` FROM `players` WHERE `id`=".$this->id);
      if (in_array($objBless->fields['bless'], $arrStats))
	{
	  $this->stats[$objBless->fields['bless']][2] += $objBless->fields['blessval'];
	}
      $objBless->Close();
      //Add bonuses
      foreach ($arrStats as $strStat)
	{
	  $this->stats[$strStat][2] += $this->checkbonus($strStat);
	}
    }

    /**
     * Function remove bless
     */
    function clearbless($arrNames)
    {
      global $db;

      $objBless = $db->Execute("SELECT `bless` FROM `players` WHERE `id`=".$this->id);
      if (in_array($objBless->fields['bless'], $arrNames))
	{
	  $db->Execute("UPDATE `players` SET `bless`='', `blessval`=0 WHERE `id`=".$this->id);
	}
      $objBless->Close();
    }

    /**
     * Function return modified player skills
     */
    function curskills($arrNames, $blnClear = TRUE, $blnCraft = FALSE)
    {
      global $db;

      /**
       * Add bless
       */
      $objBless = $db -> Execute("SELECT `bless`, `blessval` FROM `players` WHERE `id`=".$this->id);
      if ($objBless->fields['bless'] != '' && array_key_exists($objBless->fields['bless'], $this->skills))
	{
	  $this->skills[$objBless->fields['bless']][1] += $objBless->fields['blessval'];
	  if ($blnClear)
	    {
	      $db -> Execute("UPDATE `players` SET `bless`='', `blessval`=0 WHERE `id`=".$this->id);
	    }
	}
      $objBless -> Close();
    
      /**
       * Add bonus from race and class
       */
      if ($this->clas == 'Rzemieślnik' && $blnCraft)
	{
	  foreach ($arrNames as $strName)
	    {
	      $intBonus = ceil($this->skills[$strName][1] / 10);
	      if ($this->race == 'Gnom')
		{
		  $intBonus += ceil($this->skills[$strName][1] / 20);
		}
	      $this->skills[$strName][1] += $intBonus;
	    }
	}
      if ($blnCraft)
	{
	  $arrTools = array('smelting' => 'miechy',
			    'lumberjack' => 'piła',
			    'mining' => 'kilof',
			    'breeding' => 'uprząż',
			    'jewellry' => 'nożyk',
			    'herbalism' => 'sierp',
			    'alchemy' => 'moździerz',
			    'carpentry' => 'ciesak',
			    'smith' => 'młot');
	  foreach ($arrNames as $strName)
	    {
	      if (!array_key_exists($strName, $arrTools))
		{
		  continue;
		}
	      if (stripos($this->equip[12][1], $arrTools[$strName]) !== FALSE)
		{
		  $this->skills[$strName][1] += floor(($this->equip[12][2] / 100) * $this->skills[$strName][1]);
		  if ($blnClear)
		    {
		      $this->equip[12][6] --;
		      if ($this->equip[12][6] <= 0)
			{
			  $db->Execute("DELETE FROM `equipment` WHERE `id`=".$this->equip[12][0]);
			}
		      else
			{
			  $db->Execute("UPDATE `equipment` SET `wt`=".$this->equip[12][6]." WHERE `id`=".$this->equip[12][0]);
			}
		    }
		}
	    }
	}
    }

    /**
     * Function return values of selected atributes in array
     */
    function stats($stats) 
    {
        $arrstats = array();
        foreach ($stats as $value) 
        {
            $arrstats[$value] = $this->stats[$value][2];
        }
        return $arrstats;
    }

    /**
     * Function return values of equiped items
     */
    function equipment()
    {
        global $db;

        $arrEquip = array(array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
			  array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0),
			  array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'N', 0));
        $arrEquiptype = array('W', 'B', 'H', 'A', 'L', 'S', 'R', 'T', 'C', 'I', 'E');
        $objEquip = $db -> Execute("SELECT `id`, `name`, `power`, `type`, `minlev`, `zr`, `wt`, `szyb`, `poison`, `ptype`, `maxwt`, `magic`, `repair` FROM `equipment` WHERE `owner`=".$this -> id." AND status='E'");
        while (!$objEquip -> EOF)
	  {
            $intKey = array_search($objEquip -> fields['type'], $arrEquiptype);
            if ($arrEquip[9][0] && $objEquip -> fields['id'] != $arrEquip[9][0] && $objEquip -> fields['type'] == 'I')
            {
                $intKey = 10;
            }
	    elseif ($arrEquip[0][0] && $objEquip -> fields['id'] != $arrEquip[0][0] && $objEquip -> fields['type'] == 'W')
	      {
		$intKey = 11;
	      }
	    elseif ($objEquip->fields['type'] == 'E')
	      {
		$intKey = 12;
	      }
            $arrEquip[$intKey][0] = $objEquip -> fields['id'];
            $arrEquip[$intKey][1] = $objEquip -> fields['name'];
            $arrEquip[$intKey][2] = $objEquip -> fields['power'];
            $arrEquip[$intKey][3] = $objEquip -> fields['ptype'];
            $arrEquip[$intKey][4] = $objEquip -> fields['minlev'];
            $arrEquip[$intKey][5] = $objEquip -> fields['zr'];
            $arrEquip[$intKey][6] = $objEquip -> fields['wt'];
            $arrEquip[$intKey][7] = $objEquip -> fields['szyb'];
            $arrEquip[$intKey][8] = $objEquip -> fields['poison'];
            $arrEquip[$intKey][9] = $objEquip -> fields['maxwt'];
	    $arrEquip[$intKey][10] = $objEquip->fields['magic'];
	    $arrEquip[$intKey][11] = $objEquip->fields['repair'];
            $objEquip -> MoveNext();
        }
        $objEquip -> Close();
        return $arrEquip;
    }

    /**
     * Function check experience gained by player
     */
    function checkexp($arrExp, $intEid, $strType, $blnFight = FALSE)
    {
      global $db;
      global $newdate;
      
      $arrGained = array();
      foreach ($arrExp as $stat => $value)
	{
	  if ($strType == 'stats')
	    {
	      if ($this->oldstats[$stat][2] == $this->oldstats[$stat][1])
		{
		  continue;
		}
	      $intNeeded = $this->oldstats[$stat][2] * 500;
	      $this->stats[$stat][3] += $value;
	      $this->oldstats[$stat][3] += $value;
	      while (TRUE)
		{
		  if ($this->oldstats[$stat][2] == $this->oldstats[$stat][1])
		    {
		      break;
		    }
		  if ($this->oldstats[$stat][3] < $intNeeded)
		    {
		      break;
		    }
		  $this->ap ++;
		  $this->stats[$stat][2] ++;
		  $this->oldstats[$stat][2] ++;
		  $this->stats[$stat][3] -= $intNeeded;
		  $this->oldstats[$stat][3] -= $intNeeded;
		  $intNeeded = $this->oldstats[$stat][2] * 500;
		  if ($stat == 'condition')
		    {
		      $arrHp = array('Barbarzyńca' => 6,
				     'Wojownik' => 5,
				     'Złodziej' => 4,
				     'Mag' => 3,
				     'Rzemieślnik' => 2,
				     'Człowiek' => 4,
				     'Elf' => 3,
				     'Krasnolud' => 5,
				     'Jaszczuroczłek' => 5,
				     'Hobbit' => 4,
				     'Gnom' => 2);
		      $this->hp += ($arrHp[$this->race] + $arrHp[$this->clas]);
		      $this->max_hp += ($arrHp[$this->race] + $arrHp[$this->clas]);
		    }
		  if (!in_array($stat, $arrGained))
		    {
		      $arrGained[] = $stat;
		    }
		}
	    }
	  else
	    {
	      if ($this->oldskills[$stat][1] == 100)
		{
		  continue;
		}
	      $intNeeded = $this->oldskills[$stat][1] * 100;
	      $this->skills[$stat][2] += $value;
	      $this->oldskills[$stat][2] += $value;
	      while (TRUE)
		{
		  if ($this->oldskills[$stat][1] == 100)
		    {
		      break;
		    }
		  if ($this->oldskills[$stat][2] < $intNeeded)
		    {
		      break;
		    }
		  $this->skills[$stat][1] ++;
		  $this->oldskills[$stat][1] ++;
		  $this->skills[$stat][2] -= $intNeeded;
		  $this->oldskills[$stat][2] -= $intNeeded;
		  $intNeeded = $this->oldskills[$stat][1] * 100;
		  if (!in_array($stat, $arrGained))
		    {
		      $arrGained[] = $stat;
		    }
		}
	    }
	}
      $strMessage = '';
      $intGained = count($arrGained);
      if ($intGained > 0)
	{
	  if ($intGained == 1)
	    {
	      if ($strType == 'stats')
		{
		  $strMessage = 'Twoja '.$this->stats[$arrGained[0]][0].' wzrasta.';
		}
	      else
		{
		  $strMessage = 'Twoja umiejętność '.$this->skills[$arrGained[0]][0].' wzrasta';
		}
	    }
	  else
	    {
	      if ($strType == 'stats')
		{
		  $strMessage = 'Twoje cechy:';
		  foreach ($arrGained as $strKey)
		    {
		      $strMessage .= ' '.$this->stats[$strKey][0].',';
		    }
		  $strMessage = rtrim($strMessage, ',');
		  $strMessage .= ' rosną.';
		}
	      else
		{
		  $strMessage = 'Twoje umiejętności:';
		  foreach ($arrGained as $strKey)
		    {
		      $strMessage .= ' '.$this->skills[$strKey][0].',';
		    }
		  $strMessage = rtrim($strMessage, ',');
		  $strMessage .= ' rosną.';
		}
	    }
	  if ($this->id == $intEid && $strMessage != '')
	    {
	      if ($blnFight)
		{
		  echo $strMessage.'<br />';
		}
	      else
		{
		  message('info', $strMessage);
		}
	    }
	  elseif ($intEid > 0 && $strMessage != '')
	    {
	      $strDate = $db -> DBDate($newdate);
	      $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$intEid.",'Podczas walki z <b>".$this->user." ID:".$this->id."</b>, ".lcfirst($strMessage).".', ".$strDate.", 'B')");
	    }
	}
    }

    /**
     * Function count lost experience in stats/skills
     */
    function dying($blnFight = FALSE)
    {
      global $db;

      $this->hp = 0;
      if ($this->antidote != '' && $this->antidote[0] == 'R')
	{
	  $this->antidote = '';
	  $intPower = substr($this->antidote, 1);
	  if (rand(1, 100) < $intPower)
	    {
	      $this->hp = 1;
	      if ($blnFight)
		{
		  $_SESSION['ressurect'] = 'Y';
		}
	    }
	}
      $db->Execute("UPDATE `players` SET `hp`=".$this->hp.", `antidote`='".$this->antidote."', `pm`=".$this->mana." WHERE `id`=".$this->id);
      if ($this->hp == 1)
	{
	  return 'Na szczęście udało ci się tym razem oszukać śmierć.';
	}
      $rand = rand(1, 100);
      //Lost experience in stats
      if ($rand < 51)
	{
	  $strKey = array_rand($this->stats);
	  if ($this->oldstats[$strKey][2] == $this->oldstats[$strKey][1])
	    {
	      $lostexp = $this->oldstats[$strKey][1] * 500;
	      $this->oldstats[$strKey][2] --;
	      $this->oldstats[$strKey][3] = 0;
	      $this->stats[$strKey][2] --;
	      $this->stats[$strKey][3] = 0;
	      $this->ap --;
	      if ($strKey == 'condition')
		{
		  $arrHp = array('Barbarzyńca' => 6,
				 'Wojownik' => 5,
				 'Złodziej' => 4,
				 'Mag' => 3,
				 'Rzemieślnik' => 2,
				 'Człowiek' => 4,
				 'Elf' => 3,
				 'Krasnolud' => 5,
				 'Jaszczuroczłek' => 5,
				 'Hobbit' => 4,
				 'Gnom' => 2);
		      $this->max_hp -= ($arrHp[$this->race] + $arrHp[$this->clas]);
		}
	      $strMessage = 'Tracisz poziom cechy: '.$this->oldstats[$strKey][0];
	    }
	  else
	    {
	      $lostexp = ceil($this->oldstats[$strKey][3] / 100);
	      if ($lostexp > $this->oldstats[$strKey][3])
		{
		  $lostexp = $this->oldstats[$strKey][3];
		}
	      $this->stats[$strKey][3] -= $lostexp;
	      $this->oldstats[$strKey][3] -= $lostexp;
	      $strMessage = 'Tracisz nieco doświadczenia do cechy: '.$this->oldstats[$strKey][0];
	    }
	}
      //Lost experience in skills
      else
	{
	  $strKey = array_rand($this->skills);
	  if ($this->oldskills[$strKey][1] == 100)
	    {
	      $lostexp = 10000;
	      $this->oldskills[$strKey][1] --;
	      $this->oldskills[$strKey][2] = 0;
	      $this->skills[$strKey][1] --;
	      $this->skills[$strKey][2] = 0;
	      $strMessage = 'Tracisz poziom umiejętności: '.$this->oldskills[$strKey][0];
	    }
	  else
	    {
	      $lostexp = ceil($this->oldskills[$strKey][2] / 10);
	      if ($lostexp > $this->oldskills[$strKey][2])
		{
		  $lostexp = $this->oldskills[$strKey][2];
		}
	      $this->skills[$strKey][2] -= $lostexp;
	      $this->oldskills[$strKey][2] -= $lostexp;
	      $strMessage = 'Tracisz nieco doświadczenia w umiejętności: '.$this->oldskills[$strKey][0];
	    }
	}
      return $strMessage;
    }

    /**
     * Check player bonuses
     */
    function checkbonus($strTrigger)
    {
      //Find proper bonus
      $intKey = -1;
      foreach ($this->bonuses as $key => $arrBonus)
	{
	  if ($arrBonus[2] == $strTrigger)
	    {
	      $intKey = $key;
	      break;
	    }
	}
      if ($intKey == -1)
	{
	  return 0;
	}
      switch ($strTrigger)
	{
	case 'mining':
	case 'crystal':
	case 'adamantium':
	case 'copper':
	case 'zinc':
	case 'tin':
	case 'iron':
	case 'coal':
	  $intBonus = ceil($this->skills['mining'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'herbalism':
	case 'illani':
	case 'illanias':
	case 'nutari':
	case 'dynallca':
	  $intBonus = ceil($this->skills['herbalism'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'smith':
	case 'weaponsmith':
	case 'armorsmith':
	case 'helmsmith':
	case 'shieldsmith':
	case 'legsmith':
	case 'toolsmith':
	  $intBonus = ceil($this->skills['smith'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'carpentry':
	case 'bowyer':
	case 'arrowmaker':
	  $intBonus = ceil($this->skills['carpentry'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'alchemy':
	case 'amana':
	case 'ahealth':
	case 'apoison':
	case 'aantidote':
	  $intBonus = ceil($this->skills['alchemy'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'jewellry':
	case 'smelting':
	case 'breeding':
	case 'lumberjack':
	  $intBonus = ceil($this->skills[$strTrigger][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'enchant':
	  $intBonus = ceil($this->skills['magic'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'tactic':
	  $intBonus = ceil($this->stats['speed'][2] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'will':
	case 'antimagic':
	  $intBonus = ceil($this->stats['wisdom'][2] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'eagleeye':
	  $intBonus = ceil($this->skills['shoot'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'pickpocket':
	case 'steal':
	case 'spy':
	  $intBonus = ceil($this->skills['thievery'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'assasin':
	  $intBonus = ($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]);
	  break;
	case 'seeker':
	  $intBonus = ceil($this->skills['perception'][1] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	case 'speed':
	case 'strength':
	case 'wisdom':
	case 'inteli':
	case 'agility':
	case 'condition':
	  $intBonus = ceil($this->stats[$strTrigger][2] * (($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100));
	  break;
	default:
	  $intBonus = ($this->bonuses[$intKey][1] * $this->bonuses[$intKey][3]) / 100;
	  break;
	}
      return $intBonus;
    }
    
    /**
     * Save player to database
     */
    function save()
    {
      global $db;
      global $ctime;

      if (isset($_SESSION['chatclean']))
	{
	  $strChattimes = '';
	  foreach ($_SESSION['chatclean'] as $key => $value)
	    {
	      if ($key == '' || $value == '')
		{
		  continue;
		}
	      $strChattimes .= $key.','.$value.',';
	    }
	}
      else
	{
	  $strChattimes = $this->chattimes;
	}
      $db->Execute("UPDATE `players` SET `max_hp`=".$this->max_hp.", `ap`=".$this->ap.", `settings`='".$this->tostring($this->settings)."', `ip`='".$this->ip."', `chattimes`='".$strChattimes."', `stats`='".$this->tostring($this->oldstats, 'stats')."', skills='".$this->tostring($this->oldskills, 'stats')."' WHERE `id`=".$this->id) or die("nie mogę zapisać gracza");
    }
}
