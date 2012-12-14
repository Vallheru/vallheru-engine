<?php
/**
 *   File functions:
 *   Resurect players
 *
 *   @name                 : resurect.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 14.12.2012
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
* Check - player need ressurection?
*/
if ($player -> hp > 0)
{
    error("Nie potrzebujesz wskrzeszenia.");
}

$crneed = (50 * $player->stats['condition'][2]);

if ($crneed > $player -> credits) 
{
    error ("Nie masz przy sobie pieniędzy na wskrzeszenie. Potrzebujesz ".$crneed." sztuk złota.");
}

/**
 * Count lost experience
 */
$rand = rand(1, 100);
//Lost experience in stats
if ($rand < 51)
  {
    $strKey = array_rand($player->stats);
    if ($player->oldstats[$strKey][2] == $player->oldstats[$strKey][1])
      {
	$lostexp = $player->oldstats[$strKey][2] * 2000;
	$player->oldstats[$strKey][2] --;
	$player->oldstats[$strKey][3] = 0;
	$player->stats[$strKey][2] --;
	$player->stats[$strKey][3] = 0;
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
	    $player->max_hp -= ($arrHp[$player->race] + $arrHp[$player->clas]);
	  }
      }
    else
      {
	$lostexp = ceil($player->oldstats[$strKey][3] / 100);
	if ($lostexp > $player->oldstats[$strKey][3])
	  {
	    $lostexp = $player->oldstats[$strKey][3];
	  }
	$player->stats[$strKey][3] -= $lostexp;
	$player->oldstats[$strKey][3] -= $lostexp;
      }
    $strLoststat = 'cechy '.$player->oldstats[$strKey][0];
  }
//Lost experience in skills
else
  {
    $strKey = array_rand($player->skills);
    if ($player->oldskills[$strKey][1] == 100)
      {
	$lostexp = 10000;
	$player->oldskills[$strKey][1] --;
	$player->oldskills[$strKey][2] = 0;
	$player->skills[$strKey][1] --;
	$player->skills[$strKey][2] = 0;
      }
    else
      {
	$lostexp = ceil($player->oldskills[$strKey][2] / 10);
	if ($lostexp > $player->oldskills[$strKey][2])
	  {
	    $lostexp = $player->oldskills[$strKey][2];
	  }
	$player->skills[$strKey][2] -= $lostexp;
	$player->oldskills[$strKey][2] -= $lostexp;
      }
    $strLoststat = 'umiejętności '.$player->oldskills[$strKey][0];
  }

$db -> Execute("UPDATE `players` SET `hp`=".$player->max_hp.", `credits`=`credits`-".$crneed." WHERE `id`=".$player -> id);
?>
