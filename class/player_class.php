<?php
/**
 *   File functions:
 *   Class with information about player and making some things with player (e.g. atributes in array)
 *
 *   @name                 : player_class.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 23.08.2011
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
        $stats -> Close();
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
                          array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
        $arrEquiptype = array('W', 'B', 'H', 'A', 'L', 'S', 'R', 'T', 'C', 'I');
        $objEquip = $db -> Execute("SELECT `id`, `name`, `power`, `type`, `minlev`, `zr`, `wt`, `szyb`, `poison`, `ptype`, `maxwt` FROM `equipment` WHERE `owner`=".$this -> id." AND status='E'");
        while (!$objEquip -> EOF)
        {
            $intKey = array_search($objEquip -> fields['type'], $arrEquiptype);
            if ($arrEquip[9][0] && $objEquip -> fields['id'] != $arrEquip[9][0] && $objEquip -> fields['type'] == 'I')
            {
                $intKey = 10;
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
