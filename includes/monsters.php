<?php
/**
 *   File functions:
 *   Functions to generate random monsters
 *
 *   @name                 : monsters.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 08.11.2012
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

  $intLevel = 0;
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
  if ($player -> location == 'Góry') 
    {
      $strLocation = 'Altara';
    }
  elseif ($player->location == 'Las')
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
      $fltMin = 0.5
      $fltMax = 1.2
    }
  else
    {
      $fltMin = 1.2;
      $fltMax = 2.0;
    }
  $objMonster = $db->SelectLimit("SELECT * FROM `monsters` WHERE `location`='".$strLocation."' AND ((`strength`+`agility`+`speed`+`endurance`+`level`+`hp`)/".$intPlevel.")>=".$fltMin." AND ((`strength`+`agility`+`speed`+`endurance`+`level`+`hp`)/".$intPlevel.")<=".$fltMax." ORDER BY RAND()", 1);
  $enemy = array("strength" => $objMonster->fields['strength'], 
		 "agility" => $objMonster->fields['agility'], 
		 "speed" => $objMonster->fields['speed'], 
		 "endurance" => $objMonster->fields['endurance'], 
		 "hp" => $objMonster->fields['hp'], 
		 "name" => $objMonster->fields['name'], 
		 "id" => $objMonster->fields['id'], 
		 "level" => $enemy1 -> fields['level'],
		 "lootnames" => explode(";", $objMonster->fields['lootnames']),
		 "lootchances" => explode(";", $objMonster->fields['lootchances']),
		 "resistance" => explode(";", $objMonster->fields['resistance']),
		 "dmgtype" => $objMonster->fields['dmgtype']);
  $objMonster->Close();
  return $enemy;
}

?>