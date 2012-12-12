<?php
/**
 *   File functions:
 *   Functions to generate random monsters
 *
 *   @name                 : monsters.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 12.12.2012
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

/**
 * Take random monster from database.
 */
function encounter()
{
  global $player;
  global $db;

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
  if ($player -> location != 'Las') 
    {
      $strLocation = 'Altara';
    }
  else
    {
      $strLocation = 'Ardulith';
    }
  $intRoll2 = rand(1, 100);
  if ($intRoll2 < 25)
    {
      $fltMin = 0.0;
      $fltMax = 0.5;
    }
  elseif ($intRoll2 > 24 && $intRoll2 < 90)
    {
      $fltMin = 0.5;
      $fltMax = 1.2;
    }
  else
    {
      $fltMin = 1.2;
      $fltMax = 2.0;
    }
  $objMonster = $db->SelectLimit("SELECT * FROM `monsters` WHERE `location`='".$strLocation."' AND ((`strength`+`agility`+`speed`+`endurance`+`level`+`hp`)/".$intPlevel.")>=".$fltMin." AND ((`strength`+`agility`+`speed`+`endurance`+`level`+`hp`)/".$intPlevel.")<=".$fltMax." ORDER BY RAND()", 1);
  if (!$objMonster->fields['id'])
    {
      $fltMin = 0.0;
      $fltMax = 2.0;
      $objMonster = $db->SelectLimit("SELECT * FROM `monsters` WHERE `location`='".$strLocation."' AND ((`strength`+`agility`+`speed`+`endurance`+`level`+`hp`)/".$intPlevel.")>=".$fltMin." AND ((`strength`+`agility`+`speed`+`endurance`+`level`+`hp`)/".$intPlevel.")<=".$fltMax." ORDER BY RAND()", 1);
    }
  $enemy = array("strength" => $objMonster->fields['strength'], 
		 "agility" => $objMonster->fields['agility'], 
		 "speed" => $objMonster->fields['speed'], 
		 "endurance" => $objMonster->fields['endurance'], 
		 "hp" => $objMonster->fields['hp'], 
		 "name" => $objMonster->fields['name'], 
		 "id" => $objMonster->fields['id'], 
		 "level" => $objMonster->fields['level'],
		 "lootnames" => explode(";", $objMonster->fields['lootnames']),
		 "lootchances" => explode(";", $objMonster->fields['lootchances']),
		 "resistance" => explode(";", $objMonster->fields['resistance']),
		 "dmgtype" => $objMonster->fields['dmgtype']);
  $objMonster->Close();
  return $enemy;
}

/**
 * Function generate monster based on player stats
 */
function randommonster($strName)
{
  global $player;

  $arrStats = array(0, 0, $player->hp, $player->stats['condition'][2], $player->stats['speed'][2]);
  if ($player->stats['strength'][2] > $player->stats['inteli'][2])
    {
      $arrStats[0] = $player->stats['strength'][2];
    }
  else
    {
      $arrStats[0] = $player->stats['inteli'][2];
    }
  if ($player->stats['agility'][2] > $player->stats['wisdom'][2])
    {
      $arrStats[1] = $player->stats['agility'][2];
    }
  else
    {
      $arrStats[1] = $player->stats['wisdom'][2];
    }
  $arrStats[1] += $player->skills['dodge'][1];
  if ($player->equip[0][0])
    {
      $arrStats[0] += $player->equip[0][2] + $player->skills['attack'][1];
    }
  elseif ($player->equip[1][0])
    {
      $arrStats[0] += $player->equip[1][2] + $player->equip[6][2] + $player->skills['shoot'][1];
    }
  else
    {
      $arrStats[0] += $player->skills['magic'][1];
    }
  foreach ($arrStats as &$fltStat)
    {
      $fltBonus = (rand(0, 15) / 100) * $fltStat;
      if (rand(1, 100) > 50)
	{
	  $fltBonus = $fltBonus * -1;
	}
      $fltStat += $fltBonus;
    }
  $arrElements = array('water', 'fire', 'wind', 'earth');
  $intChance = rand(1, 100);
  if ($intChance < 75)
    {
      $arrResistance = array('none', 'none');
      $strDmgtype = 'none';
    }
  else
    {
      $intKey = rand(0, 3);
      $strDmgtype = $arrElements[$intKey];
    }
  if ($intChance > 74 && $intChance < 90)
    {
      $arrResistance = array($arrElements[$intKey], 'weak');
    }
  elseif ($intChance > 89 && $intChance < 97)
    {
      $arrResistance = array($arrElements[$intKey], 'medium');
    }
  elseif ($intChance > 96)
    {
      $arrResistance = array($arrElements[$intKey], 'strong');
    }
  $intLevel = $arrStats[0] + $arrStats[1] + $arrStats[4] + $arrStats[3] + $arrStats[2];
  $intElevel = $arrStats[0] + $arrStats[1] + $arrStats[4] + $arrStats[3] + $intLevel + $arrStats[2];
  $enemy = array('name' => $strName, 
		 'strength' => $arrStats[0], 
		 'agility' => $arrStats[1], 
		 'hp' => $arrStats[2], 
		 'level' => $intLevel, 
		 'endurance' => $arrStats[3], 
		 'speed' => $arrStats[4], 
		 'exp1' => $intElevel, 
		 "gold" => $intElevel,
		 "lootnames" => array(),
		 "lootchances" => array(),
		 "resistance" => $arrResistance,
		 "dmgtype" => $strDmgtype);
  return $enemy;
}

?>