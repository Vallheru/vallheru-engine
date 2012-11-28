<?php
/**
 *   File functions:
 *   Class with teams functions
 *
 *   @name                 : team_class.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 28.11.2012
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

class Team
{
  //Team members
  var $arrMembers;
  //Did team exists
  var $blnTeam;
  //Size of team
  var $intSize;
  //Player hit points
  var $oldHp;
  //Player max hit points
  var $oldMaxhp;

  //Class constructor - get data from database and write it to variables
  function Team($tid, $pid)
  {
    global $db;
    
    if ($tid == 0)
      {
	$this->blnTeam = FALSE;
	return;
      }
    $tid = intval($tid);
    $objTeam = $db->Execute("SELECT * FROM `teams` WHERE `id`=".$tid);
    if (!$objTeam->fields['id'])
      {
	$this->blnTeam = FALSE;
	return;
      }
    if ($objTeam->fields['leader'] != $pid)
      {
	$this->blnTeam = FALSE;
	return;
      }
    $arrIds = array();
    foreach (array('slot1', 'slot2', 'slot3', 'slot4', 'slot5') as $strSlot)
      {
	if ($objTeam->fields[$strSlot] > 0 && $objTeam->fields[$strSlot] != $pid)
	  {
	    $arrIds[] = $objTeam->fields[$strSlot];
	  }
      }
    $objTeam->Close();
    if (count($arrIds) == 1)
      {
	$this->blnTeam = FALSE;
	return;
      }
    $this->Size = 1;
    foreach ($arrIds as $intId)
      {
	$objMember = new Player($intId);
	if ($objMember->hp > 0 && $objMember->energy > 0)
	  {
	    $this->arrMembers[] = $objMember;
	    $this->Size++;
	  }
      }
  }

  //Get team stats
  function getStats()
  {
    global $player;
    $this->oldHp = $player->hp;
    $this->oldMaxhp = $player->max_hp;
    
    if (!$blnTeam)
      {
	return;
      }
    $arrBskills = array('shoot', 'magic', 'attack');
    $arrSkills = array();
    foreach ($player->skills as $key=>$arrSkill)
      {
	if (in_array($key, $arrBskills))
	  {
	    continue;
	  }
	$arrSkills[] = $key;
      }
    foreach ($this->arrMembers as $objMember)
      {
	if ($objMember->id == $player->id)
	  {
	    continue;
	  }
	//Get stats
	foreach ($objMember->stats as $key=>$arrStat)
	  {
	    $player->stats[$key][2] += $arrStat[2];
	  }
	//Get skills
	$objMember->curskills($arrBskills);
	$objMember->curskills($arrSkills, FALSE, TRUE);
	foreach ($objMember->skills as $key=>$arrSkill)
	  {
	    if (in_array($key, $arrBskills))
	      {
		continue;
	      }
	    $player->skills[$key][1] += $arrSkill[1];
	  }
	//Get battle skills
	$intBonus = max(array($objMember->skills['attack'][1], $objMember->skills['magic'][1], $objMember->skills['shoot'][1]));
	foreach ($arrBskills as $strSkill)
	  {
	    $player->skills[$strSkill][1] += $intBonus;
	  }
	$player->hp += $objMember->hp;
	$player->max_hp += $objMember->max_hp;
      }
  }

  //Function count gained experience
  function checkexp($arrExp, $intEid, $strType, $blnFight = FALSE)
  {
    global $player;

    foreach ($arrExp as &$intExp)
      {
	$intExp = ceil($intExp / $this->Size);
      }
    $player->checkexp($arrExp, $intEid, $strType, $blnFight);
    if (!$blnTeam)
      {
	return;
      }
    foreach ($arrMembers as $objMember)
      {
	$objMember->checkexp($arrExp, $intEid, $strType, $blnFight);
      }
  }

  //Dying
  function dying($blnFight = FALSE)
  {
    global $player;

    $strMessage = $player->dying($blnFight);
    if (!$blnTeam)
      {
	return $strMessage;
      }
    foreach ($arrMembers as $objMember)
      {
	$objMember->dying($blnFight);
      }
    return $strMessage;
  }

  //Count damage - TODO
  function countdamage()
  {
    global $player;
    
    if (!$blnTeam)
      {
	$this->oldHp = $player->hp;
	$this->oldMaxhp = $player->max_hp;
	return;
      }
    
  }
}