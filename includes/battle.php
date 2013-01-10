<?php
/**
 *   File functions:
 *   Function to fight PvP
 *
 *   @name                 : battle.php                            
 *   @copyright            : (C) 2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 10.01.2013
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
 * Function count critical hit
 */
function critical($fltAbility, $objPlayer)
{
    if ($fltAbility > 5) 
    {
        $intCritical = 6;
    } 
        else 
    {
        $intCritical = $fltAbility;
    }
    $intCritical += $objPlayer->checkbonus('assasin');
    return $intCritical;
}

/**
* Function for fight player vs player
*/
function attack1($attacker, $defender, $attack_bspell, $def_bspell, $attack_dspell, $def_dspell, $attack_stam, $def_stam, $attack_miss, $def_miss, $attack_attack, $def_attack, $attack_magic, $def_magic, $starter, $strMessage) 
{
    global $runda;
    global $number;
    global $newdate;
    global $smarty;
    global $db;
    global $player;
    global $enemy;
    global $numquery;
    global $sqltime;
    global $compress;
    global $start_time;
    global $arrTags;
    global $lang;

    $krytyk = 0;
    $repeat = ($attacker->stats['speed'][2] / $defender->stats['speed'][2]);
    $attackstr = ceil($repeat);
    $runda = ($runda + 0.5);
    if ($attackstr <= 0) 
    {
        $attackstr = 1;
    }
    $mypower = 0;
    $krytyk = 0;
    $unik = ($defender->stats['agility'][2] - $attacker->stats['agility'][2] + ($defender->skills['dodge'][1]));
    $strAtype = 'melee';
    $strSkill = 'attack';

    /**
    * Calculate dodge defender and power of attack, critical shot (bow)
    */
    if ($attacker->equip[1][0] && $attacker->equip[6][6] > 0 && $attacker->equip[1][6] > 0) 
      {
	$unik -= ($attacker->skills['shoot'][1] + $attacker->checkbonus('eagleeye'));
        $bonus = (($attacker->stats['strength'][2] / 2) + ($attacker->stats['agility'][2] / 2));
        if ($attacker->clas == 'Wojownik' || $attacker->clas == 'Barbarzyńca') 
	  {
	    $mypower = (($attacker->equip[1][2] + $bonus) + ceil($attacker->skills['shoot'][1] / 10)+ $attacker->equip[6][2]);
            $unik = ($unik - ceil($attacker->skills['shoot'][1] / 10));
	  } 
	else 
	  {
            $mypower = ($attacker->equip[1][2] + $bonus + $attacker->equip[6][2]);
	  }
        $krytyk = critical($attacker->skills['shoot'][1], $attacker);
	$strAtype = 'ranged';
	$strSkill = 'shoot';
      }

    /**
    * Calculate dodge defender and power of attack, critical hit (weapon)
    */
    if ($attacker->equip[0][0] && $attacker->equip[0][6] > 0) 
    {
        $unik -= $attacker->skills['attack'][1];
        if ($attacker->clas == 'Wojownik' || $attacker->clas == 'Barbarzyńca') 
	  {
	    $mypower = (($attacker->equip[0][2] + $attacker->stats['strength'][2]) + ceil($attacker->skills['attack'][1] / 10));
	    $unik = ($unik - ceil($attacker->skills['attack'][1] / 10));
	  } 
	else 
	  {
            $mypower = ($attacker->equip[0][2] + $attacker->stats['strength'][2]);
	  }
        $krytyk = critical($attacker->skills['attack'][1], $attacker);
	$strAtype = 'melee';
	$strSkill = 'attack';
    }
    //Secondary weapon (Barbarian only)
    if ($attacker->equip[11][0] && $attacker->equip[11][6] > 0)
      {
	$unik -= ($attacker->skills['attack'][1] / 5);
	$mypower += (($attacker->equip[11][2] + $attacker->stats['strength'][2]) + ceil($attacker->skills['attack'][1] / 10));
	$strAtype = 'melee';
	$strSkill = 'attack';
      }

    /**
    * Calculate dodge defender and power of attack, critical hit (spell)
    */
    $pech = 100;
    if ($attack_bspell -> fields['id'] && (!$attacker->equip[1][0] && !$attacker->equip[0][0])) 
    {
        $unik -= $attacker->skills['magic'][1];
        $mypower = $attack_bspell -> fields['dmg'];
	if ($attacker->equip[3][0])
	  {
	    $mypower -= ($attack_bspell -> fields['dmg'] * ($attacker->equip[3][4] / 100));
	  }
        if ($attacker->equip[2][0]) 
        {
            $mypower -= ($attack_bspell -> fields['dmg'] * ($attacker->equip[2][4] / 100));
        }
        if ($attacker->equip[4][0]) 
        {
	    $mypower -= ($attack_bspell -> fields['dmg'] * ($attacker->equip[4][4] / 100));
        }
        if ($attacker->equip[5][0]) 
        {
            $mypower -= ($attack_bspell -> fields['dmg'] * ($attacker->equip[5][4] / 100));
        }
	if ($mypower < 0) 
	  {
	    $mypower = 0;
	  }
        if ($attacker->equip[7][0]) 
        {
            $intN = ceil($attacker->equip[7][4] / 20);
            $intBonus = ceil($attacker->skills['magic'][1]) * rand(1, $intN);
            $mypower = $mypower + $intBonus;
        }
        $pech = floor($attacker->skills['magic'][1] - $attack_bspell -> fields['poziom']);
        if ($pech > 0) 
        {
            $pech = 0;
        }
        $pech = ($pech + rand(1,100));
        $krytyk = critical($attacker->skills['magic'][1], $attacker);
        if ($attacker->mana <= 0) 
        {
            $mypower = 0;
        }
	$strAtype = 'spell';
	$strSkill = 'magic';
    }

    /**
    * Add bonus to dodge defender and power defense
    */
    $eczarobr = 0;
    if ($defender->clas == 'Wojownik' || $defender->clas == 'Barbarzyńca') 
      {
	$unik = ($unik + ceil($defender->skills['dodge'][1] / 10));
      }
    elseif ($defender->clas == 'Mag') 
    {
        if ($defender->mana > 0) 
	  {
            $eczarobr = $def_dspell -> fields['def'];
	    if ($defender->equip[3][0])
	      {
		$eczarobr -= ($def_dspell -> fields['def'] * ($defender->equip[3][4] / 100));
	      }
            if ($defender->equip[2][0]) 
            {
                $eczarobr -= ($def_dspell -> fields['def'] * ($defender->equip[2][4] / 100));
            }
            if ($defender->equip[4][0]) 
            {
                $eczarobr -= ($def_dspell -> fields['def'] * ($defender->equip[4][4] / 100));
            }
            if ($defender->equip[5][0]) 
            {
                $eczarobr -= ($def_dspell -> fields['def'] * ($defender->equip[5][4] / 100));
            }
	    if ($eczarobr < 0) 
	      {
		$eczarobr = 0;
	      }
            if ($defender->equip[7][0]) 
            {
                $intN = 6 - (int)($defender->equip[7][4] / 20);
                $intBonus = (10 / $intN) * ceil($defender->skills['magic'][1] / 10) * rand(1, $intN);
                $eczarobr = ($eczarobr + $intBonus);
            }
	  }
    }
    if ($attacker->equip[1][0]) 
      {
	$unik = $unik * 2;
      }
    if ($unik < 1) 
    {
        $unik = 1;
    }
    $round = 1;
    if (!isset($strAtype)) 
    {
        $attackstr = 0;
    }
    if ($attackstr > 5) 
    {
        $attackstr = 5;
    }
    if ($attacker->clas == 'Rzemieślnik')
    {
        $mypower -= ($mypower / 4);
    }
    $mypower += ($mypower * $attacker->checkbonus('rage'));
    if ($attacker->pet[0])
      {
	if ($attacker->pet[1] > $attacker->skills[$strSkill][1])
	  {
	    $mypower += $attacker->skills[$strSkill][1];
	  }
	else
	  {
	    $mypower += $attacker->pet[1];
	  }
      }
    //Shield block chance
    $intBlock = 0;
    if ($defender->equip[5][0])
      {
	$intBlock = ceil($defender->equip[5][2] / 5);
	if ($intBlock > 20)
	  {
	    $intBlock = 20;
	  }
	if ($strAtype == 'ranged')
	  {
	    $intBlock = $intBlock * 2;
	  }
      }

    $arrLocations = array('w tułów', 'w głowę', 'w nogę', 'w rękę');
    
    /**
    * Start fight
    */
    while ($round <= $attackstr && $defender->hp >= 0) 
    {
        $rzut1 = (rand(1, $attacker->skills[$strSkill][1]));
        $mypower = ($mypower + $rzut1);
        $rzut2 = (rand(1, $defender->skills['dodge'][1]));
	$blnMiss = FALSE;
	$intHit = hitlocation();
	//Attacker too exhausted
        if ($attack_stam > $attacker->stats['condition'][2]) 
        {
	    $strMessage .= "<b>".$attacker->user."</b> jest zbyt zmęczony(a) aby atakować.<br />";
	    break;
        }
	//Attacker dont have mana
	if ($attack_bspell -> fields['id'] && $attacker->mana < 1)
	  {
	    $strMessage .= "<b>".$attacker->user."</b> nie ma punktów magii aby atakować.<br />";
	    break;
	  }
	//Attacker have bad luck with spell
	$pechowy = 100;
	if ($attack_bspell->fields['id'] && $pech < 6)
	  {
	    $pechowy = rand(1, 100);
	  }
	//Check attacker spell
	if ($attack_bspell -> fields['id'] && $mypower > 0)
	  {
	    $attacker->mana --;
	  }
	//Defender too exhausted
        if ($def_stam > $defender->stats['condition'][2]) 
        {
            $unik = 0;
        }
	//Check attacker weapon (melee)
        if ($attacker->equip[0][0]) 
	  {
	    if ($attacker->equip[0][6] > 0)
	      {
		$attack_stam += ($attacker->equip[0][4] / 10);
		$attacker->equip[0][6] --;
	      }
	    if ($attacker->equip[0][6] <= 0)
	      {
		$strMessage .= "<b>".$attacker->user."</b> nie ma broni aby atakować.<br />";
		break;
	      }
	  } 
	//Check attacker weapon (range)
	if ($attacker->equip[1][0]) 
        {
	  if ($attacker->equip[1][6] > 0 && $attacker->equip[6][6] > 0)
	    {
	      $attack_stam += ($attacker->equip[1][4] / 10);
	      $attacker->equip[1][6] --;
	      $attacker->equip[6][6] --;
	    }
	  if ($attacker->equip[1][6] <= 0 || $attacker->equip[6][6] <= 0)
	    {
	      $strMessage .= "<b>".$attacker->user."</b> nie ma broni aby atakować.<br />";
	      break;
	    }
        }
	//Check second weapon
	if ($attacker->equip[11][0])
	  {
	    if ($attacker->equip[11][6] > 0)
	      {
		$attack_stam += ($attacker->equip[11][4] / 10);
		$attacker->equip[11][6] --;
	      }
	    if ($attacker->equip[11][6] <= 0)
	      {
		$mypower -= $attacker->equip[11][1];
	      }
	  }
	//Check defender armor and pet
	$defpower = ($player->stats['condition'][2] + ($player->stats['condition'][2] * $defender->checkbonus('defender')));
	if ($defender->pet[0])
	  {
	    if ($defender->pet[2] > $defender->skills['dodge'][1])
	      {
		$defpower += $defender->skills['dodge'][1];
	      }
	    else
	      {
		$defpower += $defender->pet[2];
	      }
	  }
	if ($defender->equip[$intHit + 2][0] && $pechowy > 55)
	  {
	    if ($defender->equip[$intHit + 2][6] > 0)
	      {
		$defpower += ($defender->equip[$intHit + 2][2] + ($defender->equip[$intHit + 2][2] * $defender->checkbonus('defender')));
		$defender->equip[$intHit + 2][6] --;
	      }
	  }
	$defpower -= ($defpower * $defender->checkbonus('rage'));
	//Count damage
	$attackdmg = $mypower - ($rzut2 + $defpower + $eczarobr);
        if ($attackdmg <= 0) 
        {
            $attackdmg = 0;
        }
	//Bad luck with attack spell
	if ($pech < 6)
	  {
	    if ($pechowy <= 25) 
	      {
		$strMessage = $strMessage."<b>".$attacker->user."</b> ".YOU_MISS1." <b>1</b> ".MANA.".<br />";
	      }
	    elseif ($pechowy > 25 && $pechowy <= 45)
	      {
		$strMessage = $strMessage."<b>".$attacker->user."</b> zapatrzył się na szybko poruszającego się żółwia i stracił koncentrację.<br />";
	      }
	    elseif ($pechowy > 45 && $pechowy <= 50)
	      {
		$strMessage = $strMessage."<b>".$attacker->user."</b> ".YOU_MISS2.".<br />";
		$attacker->mana = 0;
	      }
	    elseif ($pechowy > 50 && $pechowy <= 55)
	      {
		$strMessage = $strMessage."<b>".$attacker->user."</b> ".YOU_MISS3." ".$mypower." ".HP."!<br />";
		$attacker->hp -= $mypower;			  
	      }
	    elseif ($pechowy > 55 && $pechowy <= 85)
	      {
		if ($pechowy < 65)
		  {
		    $intDamage = floor($attackdmg * 0.75);
		  }
		elseif ($pechowy < 75)
		  {
		    $intDamage = floor($attackdmg * 0.5);
		  }
		else
		  {
		    $intDamage = floor($attackdmg * 0.25);
		  }
		$defender->hp -= $intDamage;
		$strMessage = $strMessage."<b>".$attacker->user."</b> nie do końca opanował zaklęcie, dlatego jego czar zadaje <b>".$intDamage."</b> obrażeń. (".$defender->hp." zostało)<br />";
	      }
	    else
	      {
		if ($pechowy < 90)
		  {
		    $intDamage = floor($attackdmg * 0.25);
		  }
		elseif ($pechowy < 95)
		  {
		    $intDamage = floor($attackdmg * 0.5);
		  }
		else
		  {
		    $intDamage = floor($attackdmg * 0.75);
		  }
		$defender->hp -= $intDamage;
		$attacker->hp -= $intDamage;
		$strMessage = $strMessage."<b>".$attacker->user."</b> próbował rzucić zaklęcie, ale eksplodowało ono w rękach, raniąc jego oraz wroga. Traci przez to ".$intDamage." punktów życia (".$attacker->hp." zostało), <b>".$defender->user."</b> otrzymuje ".$intDamage." obrażeń (".$defender->hp." zostało)<br />";
	      }
	    break;
	  }
        /**
        * Player dodge
        */
	$intDodgemax = 100;
	$intDodgemax2 = 90;
	if ($attacker->stats['agility'][2] + $attacker->skills[$strSkill][1] < 100)
	  {
	    $intDodgemax = $attacker->stats['agility'][2] + $attacker->skills[$strSkill][1];
	    if ($intDodgemax < 4)
	      {
		$intDodgemax = 4;
	      }
	    $intDodgemax2 = floor($intDodgemax * 0.9);
	  }
        $szansa = rand(1, $intDodgemax);
        if ($unik >= $szansa && $szansa < $intDodgemax2 && $unik > 0) 
	  {
	    $strMessage = $strMessage."<b>".$defender->user."</b> ".P_DODGE." <b>".$attacker->user."</b><br />";
	    $def_miss ++;
	    $def_stam += ($defender->equip[3][4] / 10);
	    $blnMiss = TRUE;
	  } 
	//Player block attack with shield
	$szansa = rand(1, 100);
	if ($szansa <= $intBlock && !$blnMiss && $defender->equip[5][6] > 0)
	  {
	    $strMessage = $strMessage."<b>".$defender->user."</b> zablokował tarczą atak <b>".$attacker->user."</b><br />";
	    $def_stam += ($defender->equip[5][4] / 10);
	    $defender->equip[5][6] --;
	    $blnMiss = TRUE;
	  }
	//Count lost mana by defender
	if (!$blnMiss && $def_dspell->fields['id'])
	  {
	    $lost_mana = ceil($def_dspell -> fields['poziom'] / 2.5);
	    if ($defender->antidote != 'N')
	      {
		if ($attacker->equip[0][3] == 'N')
		  {
		    $lost_mana += $attacker->equip[0][8];
		  }
		if ($attacker->equip[6][3] == 'N')
		  {
		    $lost_mana += $attacker->equip[6][8];
		  }
		if ($attacker->equip[11][3] == 'N')
		  {
		    $lost_mana += $attacker->equip[11][8]; 
		  }
	      }
	    $lost_mana -= (int)($defender->skills['magic'][1] / 25);
	    if ($lost_mana < 1)
	      {
		$lost_mana = 1;
	      }
	    if ($defender->mana >= $lost_mana)
	      {
		$defender->mana -= $lost_mana;
	      }
	  }
	//Attacker hit
	if (!$blnMiss) 
        {
            $rzut = rand(1, 1000) / 10;
            $intRoll = rand(1, 100);
            /**
             * Kill with one blow
             */
            if ($krytyk >= $rzut && $intRoll <= $krytyk) 
            {
		if ($strAtype == 'melee' || $strAtype == 'ranged')
		  {
		    $attack_attack ++;
		    $strMessage = $strMessage.showcritical($arrLocations[$intHit], $strAtype, 'pvp', $defender->user, $attacker->user);
		    $defender->hp = 0;
		  }
                else
                {
		  $attack_magic ++;
		  $defender->hp = 0;
		  $strMessage = $strMessage.showcritical($arrLocations[$intHit], $strAtype, 'pvp', $defender->user, $attacker->user); 
                }
                break;
            }
	    //Normal hit
	    else 
            {
	      if ($strAtype == 'melee' || $strAtype == 'ranged') 
                {
                    if ($krytyk >= $rzut)
                    {
                        if ($intRoll <= 40)
                        {
                            $attackdmg += $mypower + $attacker->skills[$strSkill][1];
                        }
                        if ($intRoll > 40)
                        {
                            $attackdmg += $attacker->skills[$strSkill][1];
                        }
                    }
                    $defender->hp -= $attackdmg;
		    $intAttackdmg = $attackdmg;
		    if ($defender->antidote != 'I')
		      {
			if ($attacker->equip[0][3] == 'I')
			  {
			    $defender->hp -= $attacker->equip[0][8];
			    $intAttackdmg += $attacker->equip[0][8];
			  }
			if ($attacker->equip[6][3] == 'I')
			  {
			    $defender->hp -= $attacker->equip[6][8];
			    $intAttackdmg += $attacker->equip[6][8];
			  }
		      }
                    $strMessage = $strMessage."<b>".$attacker->user."</b> ".P_ATTACK." <b>".$defender->user."</b> ".$arrLocations[$intHit]." ".B_DAMAGE." <b>".$intAttackdmg."</b> ".DAMAGE."! (".$defender->hp." ".LEFT.")<br />";
                    if ($attackdmg > 0) 
                    {
                        $attack_attack++;
                    }
                    if ($defender->hp <= 0) 
                    {
                        break;
                    }
                }
	      else
                {
		  if ($krytyk >= $rzut)
		    {
		      if ($intRoll <= 40)
			{
			  $attackdmg += $mypower + $attacker->skills['magic'][1];
			}
		      if ($intRoll > 40)
			{
			  $attackdmg += $attacker->skills['magic'][1];
			}
		    }
		  if ($defender->clas == 'Barbarzyńca') 
		    {
		      $roll = rand(1,100);
		      $chance = ceil($defender->stats['wisdom'][2] / 2);
		      if ($chance > 20) 
			{
			  $chance = 20;
			}
		      if ($roll < $chance) 
			{
			  $strMessage = $strMessage."<b>".$attacker->user."</b> ".P_ATTACK." <b>".$defender->user."</b> ".B_BAR."! (".$defender->hp." ".LEFT.")<br />";
			  break;
			}
		    }
		  $defender->hp -= $attackdmg;
		  $strMessage = $strMessage."<b>".$attacker->user."</b> ".P_ATTACK." <b>".$arrLocations[$intHit]." ".$defender->user."</b> ".B_DAMAGE." <b>".$attackdmg."</b> ".DAMAGE."! (".$defender->hp." ".LEFT.")<br />";
		  if ($attackdmg > 0) 
		    {
		      $attack_magic ++;
		    }
		  if ($defender->hp <= 0) 
		    {
		      break;
		    }
                }
            }
        }
        $round ++;
    }

    //No win in fight
    if ($runda >= 25 && ($attacker->hp > 0 && $defender->hp > 0)) 
      {
	if ($attacker->hp < 1)
	  {
	    $attacker->hp = 1;
	  }
	if ($defender->hp < 1)
	  {
	    $defender->hp = 1;
	  }
        $strMessage = $strMessage."<br />".B_NO_WIN."<br />";
        $smarty -> assign ("Message", $strMessage);
        $smarty -> display ('error1.tpl');
	//Count gained experience by attacker
	$intExpsum = 0;
	foreach (array('strength', 'agility', 'speed', 'condition', 'inteli', 'wisdom') as $strStat)
	  {
	    $intExpsum += $defender->stats[$strStat][2];
	  }
	foreach (array('attack', 'shoot', 'magic', 'dodge') as $strSkill2)
	  {
	    $intExpsum += $defender->skills[$strSkill2][1];
	  }
	//Count gained experience by defender
	$intExpsum2 = 0;
	foreach (array('strength', 'agility', 'speed', 'condition', 'inteli', 'wisdom') as $strStat)
	  {
	    $intExpsum2 += $attacker->stats[$strStat][2];
	  }
	foreach (array('attack', 'shoot', 'magic', 'dodge') as $strSkill2)
	  {
	    $intExpsum2 += $attacker->skills[$strSkill2][1];
	  }
	$fltExpdiv = $intExpsum / $intExpsum2;
	if ($fltExpdiv > 2)
	  {
	    $fltExpdiv = 2;
	  }
	$fltExpdiv2 = $intExpsum2 / $intExpsum;
	if ($fltExpdiv2 > 2)
	  {
	    $fltExpdiv2 = 2;
	  }
	$intExpsum = ceil($intExpsum * $fltExpdiv);
	$intExpsum2 = ceil($intExpsum2 * $fltExpdiv2);
        if ($attacker->equip[0][0] || $attacker->equip[11][0]) 
	  {
	    $strType = 'melee';
	  }
	else
	  {
	    $strType = 'ranged';
	  }
	gainability($attacker, $intExpsum, $attack_miss, $attack_attack, $attack_magic, $starter, $strType);
	lostitem($attacker->equip, $attacker->id, $starter, $attacker->skills['shoot'][1]);
	gainability($defender, $intExpsum2, $def_miss, $def_attack, $def_magic, $starter, $strType);
	lostitem($defender->equip, $defender->id, $starter, $defender->skills['shoot'][1]);
	checkpet($attacker->id, $attacker->pet, $starter);
	checkpet($defender->id, $defender->pet, $starter);
        $db -> Execute("UPDATE `players` SET `hp`=".$attacker->hp.", `bless`='', `blessval`=0 WHERE `id`=".$attacker->id);
        $db -> Execute("UPDATE `players` SET `hp`=".$defender->hp.", `bless`='', `blessval`=0 WHERE `id`=".$defender->id);
        if ($attacker->id == $starter) 
        {
            $attacktext = YOU_ATTACK_BUT;
            $defendtext = YOU_ATTACKED_BUT;
            $startuser = $attacker->user;
            $secuser = $defender->user;
        } 
            else 
        {
            $defendtext = YOU_ATTACK_BUT;
            $attacktext = YOU_ATTACKED_BUT;
            $startuser = $defender->user;
            $secuser = $attacker->user;
        }
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('".L_PLAYER." ".$startuser." ".L_ATTACK." ".$secuser." ".L_BATTLE."')");
        $strDate = $db -> DBDate($newdate);
        /**
         * Send battle logs
         */
        if (($attacker->settings['battlelog'] == 'Y') || ($attacker->id == $starter && $attacker->settings['battlelog'] == 'A') || ($attacker->id != $starter && $attacker->settings['battlelog'] == 'D'))
        {
            $strSubject = T_SUBJECT.$defender->user.T_SUB_ID.$defender->id;
            $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".T_SENDER."','0',".$attacker->id.",'".$strSubject."','".$strMessage."', ".$strDate.")");
        }
        if (($defender->settings['battlelog'] == 'Y') || ($defender->id == $starter && $defender->settings['battlelog'] == 'A') || ($defender->id != $starter && $defender->settings['battlelog'] == 'D'))
        {
             $strSubject = T_SUBJECT.$attacker->user.T_SUB_ID.$attacker->id;
            $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".T_SENDER."','0',".$defender->id.",'".$strSubject."','".$strMessage."', ".$strDate.")");
        }
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$attacker->id.",'".$attacktext." ".L_BATTLE2."  <b><a href=view.php?view=".$defender->id.">".$defender->user."</a></b> ".L_ID.'<b>'.$defender->id."</b>.', ".$strDate.", 'B')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$defender->id.",'".$defendtext." ".L_BATTLE2." <b><a href=view.php?view=".$attacker->id.">".$attacker->user."</a></b> ".L_ID.'<b>'.$attacker->id."</b>.', ".$strDate.", 'B')");
	//Write battle info
	$objDay = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='day'");
	$db->Execute("INSERT INTO `battlelogs` (`pid`, `did`, `wid`, `bdate`) VALUES(".$attacker->id.", ".$defender->id.", 0, ".$objDay->fields['value'].")");
	$db->Execute("INSERT INTO `battlelogs` (`pid`, `did`, `wid`, `bdate`) VALUES(".$defender->id.", ".$attacker->id.", 0, ".$objDay->fields['value'].")");
	$objDay->Close();
	$defender->save();
        require_once("includes/foot.php");
        exit;
    }
    //Attacker win fight
    if ($defender->hp <= 0) 
    {
        $defender->hp = 0;
	if ($attacker->hp < 1)
	  {
	    $attacker->hp = 1;
	  }
        $strMessage = $strMessage."<br /><b>".$attacker->user."</b> ".B_WIN."!<br />";
        $roll = rand (1,20);
        if ($roll == 20 && $defender->maps > 0) 
        {
            $db -> Execute("UPDATE `players` SET `maps`=`maps`+1 WHERE id=".$attacker->id);
            $db -> Execute("UPDATE `players` SET `maps`=`maps`-1 WHERE id=".$defender->id);
            $text = AND_MAP;
        } 
            else 
        {
            $text = '';
        }
        $creditgain = floor($defender->credits / 10);
        if ($creditgain < 0)
        {
            $creditgain = 0;
        }
	if (($attacker->reputation - 10) > $defender->reputation)
	  {
	    $intRepchange = 0;
	    $fltDiv = 0.5;
	  }
	elseif (($attacker->reputation + 10) < $defender->reputation)
	  {
	    $intRepchange = 2;
	    $fltDiv = 1.5;
	  }
	else
	  {
	    $intRepchange = 1;
	    $fltDiv = 1;
	  }
	//Count gained experience by attacker
	$intExpsum = 0;
	foreach (array('strength', 'agility', 'speed', 'condition', 'inteli', 'wisdom') as $strStat)
	  {
	    $intExpsum += $defender->stats[$strStat][2];
	  }
	foreach (array('attack', 'shoot', 'magic', 'dodge') as $strSkill2)
	  {
	    $intExpsum += $defender->skills[$strSkill2][1];
	  }
	$intExpsum = $intExpsum * 2;
	$intExpsum2 = 0;
	foreach (array('strength', 'agility', 'speed', 'condition', 'inteli', 'wisdom') as $strStat)
	  {
	    $intExpsum2 += $attacker->stats[$strStat][2];
	  }
	foreach (array('attack', 'shoot', 'magic', 'dodge') as $strSkill2)
	  {
	    $intExpsum2 += $attacker->skills[$strSkill2][1];
	  }
	$fltExpdiv = $intExpsum / ($intExpsum2 * 2);
	if ($fltExpdiv > 2)
	  {
	    $fltExpdiv = 2;
	  }
	$intExpsum = ceil(($intExpsum * $fltExpdiv) * $fltDiv);
	if ($attacker->equip[0][0] || $attacker->equip[11][0]) 
	  {
	    $strType = 'melee';
	  }
	else
	  {
	    $strType = 'ranged';
	  }
        $strMessage = $strMessage."<b>".$attacker->user."</b> ".HE_GET." <b>".$intExpsum."</b> ".EXPERIENCE." <b>".$intRepchange."</b> reputacji, <b>".$creditgain."</b> ".GOLD_COINS." ".$text."<br />";
	gainability($attacker, $intExpsum, $attack_miss, $attack_attack, $attack_magic, $starter, $strType);
	lostitem($attacker->equip, $attacker->id, $starter, $attacker->skills['shoot'][1]);
	lostitem($defender->equip, $defender->id, $starter, $defender->skills['shoot'][1]);
	checkpet($attacker->id, $attacker->pet, $starter);
	checkpet($defender->id, $defender->pet, $starter, TRUE);
        $db -> Execute("UPDATE `players` SET `hp`=".$attacker->hp.", `credits`=`credits`+".$creditgain.", `wins`=`wins`+1, `lastkilled`='".'<a href="view.php?view='.$defender->id.'">'.$defender->user."</a>', `bless`='', `blessval`=0, `reputation`=`reputation`+".$intRepchange." WHERE `id`=".$attacker->id);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$creditgain.", `losses`=`losses`+1, `lastkilledby`='".'<a href="view.php?view='.$attacker->id.'">'.$attacker->user."</a>', `bless`='', `blessval`=0, `reputation`=`reputation`-".$intRepchange." WHERE `id`=".$defender->id);
        if ($attacker->id == $starter) 
        {
            $attacktext = YOU_ATTACK_AND;
            $startuser = $attacker->user;
            $secuser = $defender->user;
        } 
            else 
        {
            $attacktext = YOU_ATTACKED_AND;
            $startuser = $defender->user;
            $secuser = $attacker->user;
        }
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('".L_PLAYER." ".$startuser." ".L_ATTACK." ".$secuser.". ".BATTLE_WIN." ".$attacker->user."')");
        $strDate = $db -> DBDate($newdate);
        if (($attacker->settings['battlelog'] == 'Y')  || ($attacker->id == $starter && $attacker->settings['battlelog'] == 'A') || ($attacker->id != $starter && $attacker->settings['battlelog'] == 'D'))
        {
            $strSubject = T_SUBJECT.$defender->user.T_SUB_ID.$defender->id;
            $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".T_SENDER."','0',".$attacker->id.",'".$strSubject."','".$strMessage."', ".$strDate.")");
        }
        if (($defender->settings['battlelog'] == 'Y') || ($defender->id == $starter && $defender->settings['battlelog'] == 'A') || ($defender->id != $starter && $defender->settings['battlelog'] == 'D'))
        {
            $strSubject = T_SUBJECT.$attacker->user.T_SUB_ID.$attacker->id;
            $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".T_SENDER."','0',".$defender->id.",'".$strSubject."','".$strMessage."', ".$strDate.")");
        }
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$attacker->id.",'".$attacktext." ".YOU_DEFEAT." <b><a href=view.php?view=".$defender->id.">".$defender->user."</a></b> ".L_ID.'<b>'.$defender->id."</b>. ".YOU_REWARD." <b>".$intExpsum."</b> ".EXPERIENCE." <b>".$creditgain."</b> ".GOLD_COINS.".', ".$strDate.", 'B')");
	//Write battle info
	$objDay = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='day'");
	$db->Execute("INSERT INTO `battlelogs` (`pid`, `did`, `wid`, `bdate`) VALUES(".$attacker->id.", ".$defender->id.", ".$attacker->id.", ".$objDay->fields['value'].")");
	$db->Execute("INSERT INTO `battlelogs` (`pid`, `did`, `wid`, `bdate`) VALUES(".$defender->id.", ".$attacker->id.", ".$attacker->id.", ".$objDay->fields['value'].")");
	$objDay->Close();
	$strLog = $defender->dying();	
	if ($defender->id == $starter) 
	  {
	    $attacktext = YOU_ATTACK;
	    $strMessage .= $strLog;
	  } 
	else 
	  {
	    $attacktext = YOU_ATTACKED;
	  }
	$smarty -> assign ("Message", $strMessage);
        $smarty -> display ('error1.tpl');
	gainability($attacker, $intExpsum, $attack_miss, $attack_attack, $attack_magic, $starter, $strType);
	lostitem($attacker->equip, $attacker->id, $starter, $attacker->skills['shoot'][1]);
	lostitem($defender->equip, $defender->id, $starter, $defender->skills['shoot'][1]);
	$db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$defender->id.",'".$attacktext." ".YOU_LOSE." <b><a href=view.php?view=".$attacker->id.">".$attacker->user."</a> ".ID.":".$attacker->id."</b>. ".$strLog."', ".$strDate.", 'B')");
	$defender->save();
        require_once("includes/foot.php");
        exit;
    }
    attack1($defender, $attacker, $def_bspell, $attack_bspell, $def_dspell, $attack_dspell, $def_stam, $attack_stam, $def_miss, $attack_miss, $def_attack, $attack_attack, $def_magic, $attack_magic, $starter, $strMessage);
}
?>
