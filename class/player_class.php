<?php
/**
 *   File functions:
 *   Class with information about player and making some things with player (e.g. atributes in array)
 *
 *   @name                 : player_class.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 07.05.2012
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
    var $level;
    var $exp;
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
    var $agility;
    var $strength;
    var $inteli;
    var $pw;
    var $wins;
    var $losses;
    var $lastkilled;
    var $lastkilledby;
    var $age;
    var $logins;
    var $smith;
    var $attack;
    var $miss;
    var $magic;
    var $ip;
    var $speed;
    var $cond;
    var $alchemy;
    var $gg;
    var $avatar;
    var $wisdom;
    var $shoot;
    var $tribe_rank;
    var $fletcher;
    var $immunited;
    var $corepass;
    var $trains;
    var $fight;
    var $deity;
    var $maps;
    var $rest;
    var $page;
    var $profile;
    var $crime;
    var $gender;
    var $style;
    var $leadership;
    var $graphic;
    var $lang;
    var $seclang;
    var $antidote;
    var $breeding;
    var $battlelog;
    var $poll;
    var $mining;
    var $lumberjack;
    var $herbalist;
    var $jeweller;
    var $graphbar;
    var $vallars;
    var $newbie;
    var $autodrink;
    var $thievery;
    var $perception;
    var $forumtime;
    var $tforumtime;
    var $forumcats;
    var $metallurgy;
    var $revent;
    var $room;
    var $rinvites;
    var $oldstats;
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
        $this -> level = $stats -> fields['level'];
        $this -> exp = $stats -> fields['exp'];
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
        $this -> agility = $stats -> fields['agility'];
        $this -> strength = $stats -> fields['strength'];
        $this -> inteli = $stats -> fields['inteli'];
        $this -> pw = $stats -> fields['pw'];
        $this -> wins = $stats -> fields['wins'];
        $this -> losses = $stats -> fields['losses'];
        $this -> lastkilled = $stats -> fields['lastkilled'];
        $this -> lastkilledby = $stats -> fields['lastkilledby'];
        $this -> age = $stats -> fields['age'];
        $this -> logins = $stats -> fields['logins'];
        $this -> smith = $stats -> fields['ability'];
        $this -> attack = $stats -> fields['atak'];
        $this -> miss = $stats -> fields['unik'];
        $this -> magic = $stats -> fields['magia'];
        $this -> ip = $stats -> fields['ip'];
        $this -> speed = $stats -> fields['szyb'];
        $this -> cond = $stats -> fields['wytrz'];
        $this -> alchemy = $stats -> fields['alchemia'];
        $this -> gg = $stats -> fields['gg'];
        $this -> avatar = $stats -> fields['avatar'];
        $this -> wisdom = $stats -> fields['wisdom'];
        $this -> shoot = $stats -> fields['shoot'];
        $this -> tribe_rank = $stats -> fields['tribe_rank'];
        $this -> fletcher = $stats -> fields['fletcher'];
        $this -> immunited = $stats -> fields['immu'];
        $this -> corepass = $stats -> fields['corepass'];
        $this -> trains = $stats -> fields['trains'];
        $this -> fight = $stats -> fields['fight'];
        $this -> deity = $stats -> fields['deity'];
        $this -> maps = $stats -> fields['maps'];
        $this -> rest = $stats -> fields['rest'];
        $this -> page = $stats -> fields['page'];
        $this -> profile = $stats -> fields['profile'];
        $this -> crime = $stats -> fields['crime'];
        $this -> gender = $stats -> fields['gender'];
        $this -> style = $stats -> fields['style'];
        $this -> leadership = $stats -> fields['leadership'];
        $this -> graphic = $stats -> fields['graphic'];
        $this -> lang = $stats -> fields['lang'];
        $this -> seclang = $stats -> fields['seclang'];
        if (!empty($stats -> fields['antidote']))
        {
            $this -> antidote = $stats -> fields['antidote']{0};
        }
            else
        {
            $this -> antidote = '';
        }
        $this -> breeding = $stats -> fields['breeding'];
        $this -> battlelog = $stats -> fields['battlelog'];
        $this -> poll = $stats -> fields['poll'];
        $this -> mining = $stats -> fields['mining'];
        $this -> lumberjack = $stats -> fields['lumberjack'];
        $this -> herbalist = $stats -> fields['herbalist'];
        $this -> jeweller = $stats -> fields['jeweller'];
        $this -> graphbar = $stats -> fields['graphbar'];
	$this->vallars = $stats->fields['vallars'];
	$this->newbie = $stats->fields['newbie'];
	$this->autodrink = $stats->fields['autodrink'];
	$this->thievery = $stats->fields['thievery'];
	$this->perception = $stats->fields['perception'];
	$this->forumtime = $stats->fields['forum_time'];
	$this->tforumtime = $stats->fields['tforum_time'];
	$this->forumcats = $stats->fields['forumcats'];
	$this->metallurgy = $stats->fields['metallurgy'];
        $stats -> Close();
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
	$this->rinvites = $stats->fields['rinvites'];
	$this->oldstats = array($this->agility, $this->strength, $this->inteli, $this->wisdom, $this->speed, $this->cond);
    }

    /**
     * Function return modified player stats
     */
    function curstats($arrEquip = array(), $blnClear = FALSE)
    {
      global $db;

      if (count($arrEquip) == 0)
	{
	  $arrEquip = $this->equipment();
	}
      //Add bonuses from equipment
      if ($arrEquip[3][0])
	{
	  if ($arrEquip[3][5] < 0) 
	    {
	      $intAgibonus = str_replace("-","",$arrEquip[3][5]);
	    } 
	  elseif ($arrEquip[3][5] >= 0) 
	    {
	      $intAgibonus = 0 - $arrEquip[3][5];
	    }
	  $this->agility += $intAgibonus;
	}
      if ($arrEquip[5][0])
	{
	  if ($arrEquip[5][5] < 0) 
	    {
	      $intAgibonus = str_replace("-","",$arrEquip[5][5]);
	    } 
	  elseif ($arrEquip[5][5] >= 0) 
	    {
	      $intAgibonus = 0 - $arrEquip[5][5];
	    }
	  $this->agility += $intAgibonus;
	}
      if ($arrEquip[4][0])
	{
	  if ($arrEquip[4][5] < 0) 
	    {
	      $intAgibonus = str_replace("-","",$arrEquip[4][5]);
	    } 
	  elseif ($arrEquip[4][5] >= 0) 
	    {
	      $intAgibonus = 0 - $arrEquip[4][5];
	    }
	  $this->agility += $intAgibonus;
	}
      if ($arrEquip[1][0])
	{
	  $this->speed += $arrEquip[1][7];
	}
      $arrStats = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'cond');
      //Add bonuses from rings
      if ($arrEquip[9][2])
	{
	  $arrRings = array("zręczności", "siły", "inteligencji", "woli", "szybkości", "wytrzymałości");
	  $arrRingtype = explode(" ", $arrEquip[9][1]);
	  $intAmount = count($arrRingtype) - 1;
	  $intKey = array_search($arrRingtype[$intAmount], $arrRings);
	  $this->$arrStats[$intKey] += $arrEquip[9][2];
	}
      if ($arrEquip[10][2])
	{
	  $arrRings = array("zręczności", "siły", "inteligencji", "woli", "szybkości", "wytrzymałości");
	  $arrRingtype = explode(" ", $arrEquip[10][1]);
	  $intAmount = count($arrRingtype) - 1;
	  $intKey = array_search($arrRingtype[$intAmount], $arrRings);
	  $this->$arrStats[$intKey] += $arrEquip[10][2];
	}
      //Add bonuses from bless
      $objBless = $db -> Execute("SELECT `bless`, `blessval` FROM `players` WHERE `id`=".$this->id);
      if (in_array($objBless->fields['bless'], $arrStats))
	{
	  $strVarname = $objBless->fields['bless'];
	  $this->$strVarname += $objBless->fields['blessval'];
	  if ($blnClear)
	    {
	      $db -> Execute("UPDATE `players` SET `bless`='', `blessval`=0 WHERE `id`=".$this->id);
	    }
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
      foreach ($arrNames as $strName)
	{
	  if ($objBless -> fields['bless'] == $strName)
	    {
	      $this->$strName += $objBless->fields['blessval'];
	      if ($blnClear)
		{
		  $db -> Execute("UPDATE `players` SET `bless`='', `blessval`=0 WHERE `id`=".$this->id);
		}
	    }
	}
      $objBless -> Close();
    
      /**
       * Add bonus from race and class
       */
      if ($this->clas == 'Rzemieślnik' && $blnCraft)
	{
	  $intBonus = ($this->level / 10);
	  if ($this->race == 'Gnom')
	    {
	      $intBonus += ($this->level / 5);
	    }
	  foreach ($arrNames as $strName)
	    {
	      $intMaxbonus = $this->$strName * 2;
	      if ($intBonus > $intMaxbonus)
		{
		  $intBonus = $intMaxbonus;
		}
	      $this->$strName += $intBonus;
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
            $arrstats[$value] = $this -> $value;
        }
        return $arrstats;
    }

    /**
     * Function return values of equiped items
     */
    function equipment()
    {
        global $db;

        $arrEquip = array(array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			  array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			  array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
        $arrEquiptype = array('W', 'B', 'H', 'A', 'L', 'S', 'R', 'T', 'C', 'I', 'E');
        $objEquip = $db -> Execute("SELECT `id`, `name`, `power`, `type`, `minlev`, `zr`, `wt`, `szyb`, `poison`, `ptype`, `maxwt` FROM `equipment` WHERE `owner`=".$this -> id." AND status='E'");
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
            $objEquip -> MoveNext();
        }
        $objEquip -> Close();
        return $arrEquip;
    }
}
