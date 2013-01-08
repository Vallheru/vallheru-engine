<?php
/**
 *   File functions:
 *   Steal items from shops
 *
 *   @name                 : steal.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 08.01.2013
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
// $Id:$

/**
* Get the localization for game
*/
require_once("languages/".$lang."/steal.php");

/**
* Steal items from shops
*/
function steal ($itemid) 
{
    global $player;
    global $smarty;
    global $title;
    global $newdate;
    global $db;
    
    if ($player -> clas != 'Złodziej')
    {
        error(ERROR);
    }
    checkvalue($itemid);
    if ($player -> hp <= 0) 
    {
        error (E_DEAD);
    }
    if ($player->energy <= 2) 
    {
        error ('Nie masz tylu energii.');
    }
    if ($title != 'Łucznik') 
      {
	$arritem = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$itemid);
      } 
    else 
      {
	$arritem = $db -> Execute("SELECT * FROM `bows` WHERE `id`=".$itemid);
      }
    $roll = rand (1, $arritem->fields['minlev']);

    $player->curskills(array('thievery'));
    $player->clearbless(array('agility', 'inteli'));

    $intStats = ($player->stats['agility'][2] + $player->stats['inteli'][2] + $player->skills['thievery'][1] + $player->checkbonus('steal'));
    /**
     * Add bonus from tools
     */
    if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
      {
	$intStats += (($player->equip[12][2] / 100) * $intStats);
      }

    $chance = $intStats - $roll;
    $strDate = $db -> DBDate($newdate);
    if ($chance < 1) 
    {
        $cost = 1000 * $player ->skills['thievery'][1];
        $player->checkexp(array('agility' => 1,
				'inteli' => 1), $player->id, 'stats');
	$player->checkexp(array('thievery' => 1), $player->id, 'skills');
	$player->energy -= 2;
        $db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `energy`=`energy`-2 WHERE `id`=".$player -> id);
        $db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.", '".VERDICT."', 7, ".$cost.", ".$strDate.")") or die("Błąd!");
        $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".S_LOG_INFO." ".$cost.".', ".$strDate.", 'T')");
	if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
	  {
	    $db->Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[12][0]);
	  }
	$objTool = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
	if ($objTool->fields['id'])
	  {
	    $intRoll = rand(1, 100);
	    if ($intRoll < 50)
	      {
		$db->Execute("DELETE FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
	      }
	  }
	$objTool->Close();
        error (CRIME_RESULT1);
    } 
    else 
      {       
        $player->energy -= 2;
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-2 WHERE `id`=".$player -> id);
        $expgain = $arritem->fields['minlev'] * 5;
	$player->checkexp(array('agility' => ($expgain / 3),
				'inteli' => ($expgain / 3)), $player->id, 'stats');
	$player->checkexp(array('thievery' => ($expgain / 3)), $player->id, 'skills');
        if ($arritem -> fields['type'] == 'R') 
        {
            $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arritem -> fields['name']."' AND owner=".$player -> id." AND status='U' AND cost=1");
            if (empty ($test -> fields['id'])) 
            {
                $db -> Execute("INSERT INTO equipment (owner, name, power, cost, wt, szyb, minlev, maxwt, type) VALUES(".$player -> id.",'".$arritem -> fields['name']."',".$arritem -> fields['power'].",1,".$arritem -> fields['maxwt'].",".$arritem -> fields['szyb'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",'".$arritem -> fields['type']."')") or error(DB_ERROR2);
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET wt=wt+".$arritem -> fields['maxwt']." WHERE id=".$test -> fields['id']);
                $db -> Execute("UPDATE equipment SET maxwt=maxwt+".$arritem -> fields['maxwt']." where id=".$test -> fields['id']);
            }
            $test -> Close();
        } 
            else 
        {
            $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arritem -> fields['name']."' AND wt=".$arritem -> fields['maxwt']." AND type='".$arritem -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$arritem -> fields['power']." AND zr=".$arritem -> fields['zr']." AND szyb=".$arritem -> fields['szyb']." AND maxwt=".$arritem -> fields['maxwt']." and cost=1");
            if (empty ($test -> fields['id'])) 
            {
                if ($arritem -> fields['type'] == 'B') 
                {
                    $arritem -> fields['twohand'] = 'Y';
                }
                $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, szyb, twohand, repair) VALUES(".$player -> id.",'".$arritem -> fields['name']."',".$arritem -> fields['power'].",'".$arritem -> fields['type']."',1,".$arritem -> fields['zr'].",".$arritem -> fields['maxwt'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",1,".$arritem -> fields['szyb'].",'".$arritem -> fields['twohand']."', ".$arritem -> fields['repair'].")") or error(DB_ERROR3);
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
            }
            $test -> Close();
        }
	if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
	  {
	    $player->equip[12][6] --;
	    if ($player->equip[12][6] <= 0)
	      {
		$db->Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[12][0]);
	      }
	    else
	      {
		$db->Execute("UPDATE `equipment` SET `wt`=`wt`-1 WHERE `id`=".$player->equip[12][0]);
	      }
	  }
        error (CRIME_RESULT2." ".$arritem -> fields['name'].CRIME_RESULT3." Zdobyłeś ".$expgain." punktów doświadczenia.");
    }
}
?>
