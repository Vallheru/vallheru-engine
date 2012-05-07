<?php
/**
 *   File functions:
 *   Function to fight PvP
 *
 *   @name                 : battle.php                            
 *   @copyright            : (C) 2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
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

/**
 * Function count critical hit
 */
function critical($fltAbility)
{
    if ($fltAbility < 1) 
    {
        $intCritical = 1;
    }
    if ($fltAbility > 5) 
    {
        $intCrit = $fltAbility / 100;
        $intCritical = (5 + $intCrit);
    } 
        else 
    {
        $intCritical = $fltAbility;
    }
    return $intCritical;
}

/**
* Function for fight player vs player
*/
function attack1($attacker, $defender, $arrAtequip, $arrDeequip, $attack_bspell, $def_bspell, $attack_dspell, $def_dspell, $attack_stam, $def_stam, $attack_miss, $def_miss, $attack_attack, $def_attack, $attack_magic, $def_magic, $attack_durwep, $def_durwep, $attack_durarm, $def_durarm, $starter, $strMessage) 
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

    $krytyk = 0;
    $repeat = ($attacker['speed'] / $defender['speed']);
    $attackstr = ceil($repeat);
    $runda = ($runda + 0.5);
    $earmor = checkarmor($arrDeequip[3][0], $arrDeequip[2][0], $arrDeequip[4][0], $arrDeequip[5][0]);
    if ($attackstr <= 0) 
    {
        $attackstr = 1;
    }
    $mypower = 0;
    $epower = 0;
    $krytyk = 0;
    $unik = ($defender['agility'] - $attacker['agility'] + ($defender['miss']));

    /**
    * Calculate dodge defender and power of attack, critical shot (bow)
    */
    if ($arrAtequip[1][0] && $attack_durwep <= $arrAtequip[1][6] && $attack_durwep <= $arrAtequip[6][6]) 
    {
        $unik -= $attacker['shoot'];
        $bonus = (($attacker['strength'] / 2) + ($attacker['agility'] / 2));
        if ($attacker['clas'] == 'Wojownik' || $attacker['clas'] == 'Barbarzyńca') 
        {
            $mypower = (($arrAtequip[1][2] + $bonus) + $attacker['level'] + $arrAtequip[6][2]);
            $unik = ($unik - $attacker['level']);
        } 
            else 
        {
            $mypower = ($arrAtequip[1][2] + $bonus + $arrAtequip[6][2]);
        }
        $krytyk = critical($attacker['shoot']);
	$strAtype = 'ranged';
    }

    /**
    * Calculate dodge defender and power of attack, critical hit (weapon)
    */
    if ($arrAtequip[0][0] && $attack_durwep <= $arrAtequip[0][6]) 
    {
        $unik -= $attacker['attack'];
        if ($attacker['clas'] == 'Wojownik' || $attacker['clas'] == 'Barbarzyńca') 
        {
            $mypower = (($arrAtequip[0][2] + $attacker['strength']) + $attacker['level']);
            $unik = ($unik - $attacker['level']);
        } 
            else 
        {
            $mypower = ($arrAtequip[0][2] + $attacker['strength']);
        }
        $krytyk = critical($attacker['attack']);
	$strAtype = 'melee';
    }
    //Secondary weapon (Barbarian only)
    if ($arrAtequip[11][0] && $attack_durwep <= $arrAtequip[11][6])
      {
	$unik -= ($attacker['attack'] / 5);
	$mypower += (($arrAtequip[11][2] + $attacker['strength']) + $attacker['level']);
      }

    /**
    * Calculate dodge defender and power of attack, critical hit (spell)
    */
    if ($attack_bspell -> fields['id'] && (!$arrAtequip[1][0] && !$arrAtequip[0][0])) 
    {
        $unik -= $attacker['magic'];
        $mypower = ($attack_bspell -> fields['obr'] * $attacker['inteli']) - (($attack_bspell -> fields['obr'] * $attacker['inteli']) * ($arrAtequip[3][4] / 100));
        if ($arrAtequip[2][0]) 
        {
            $mypower = ($mypower - (($attack_bspell -> fields['obr'] * $attacker['inteli']) * ($arrAtequip[2][4] / 100)));
            if ($mypower < 0) 
            {
                $mypower = 0;
            }
        }
        if ($arrAtequip[4][0]) 
        {
            $mypower = ($mypower - (($attack_bspell -> fields['obr'] * $attacker['inteli']) * ($arrAtequip[4][4] / 100)));
            if ($mypower < 0) 
            {
                $mypower = 0;
            }
        }
        if ($arrAtequip[5][0]) 
        {
            $mypower = ($mypower - (($attack_bspell -> fields['obr'] * $attacker['inteli']) * ($arrAtequip[5][4] / 100)));
            if ($mypower < 0) 
            {
                $mypower = 0;
            }
        }
        if ($arrAtequip[7][0]) 
        {
            $intN = 6 - (int)($arrAtequip[7][4] / 20);
            $intBonus = (10 / $intN) * $attacker['level'] * rand(1, $intN);
            $mypower = $mypower + $intBonus;
        }
        $pech = floor($attacker['magic'] - $attack_bspell -> fields['poziom']);
        if ($pech > 0) 
        {
            $pech = 0;
        }
        $pech = ($pech + rand(1,100));
        $krytyk = critical($attacker['magic']);
        if ($attacker['mana'] <= 0) 
        {
            $mypower = 0;
        }
	$strAtype = 'spell';
    }

    /**
    * Add bonus to dodge defender and power defense
    */
    if ($defender['clas'] == 'Wojownik' || $defender['clas'] == 'Barbarzyńca') 
    {
        $epower = ($arrDeequip[3][2] + $defender['level'] + $defender['cond'] + $arrDeequip[2][2] + $arrDeequip[4][2] + $arrDeequip[5][2]);
        $unik = ($unik + $defender['level']);
        if ($arrAtequip[1][0]) 
        {
            $unik = $unik * 2;
        }
    }
    elseif ($defender['clas'] == 'Rzemieślnik' || $defender['clas'] == 'Złodziej') 
    {
        $epower = ($arrDeequip[3][2] + $defender['cond'] + $arrDeequip[2][2] + $arrDeequip[4][2] + $arrDeequip[5][2]);
        if ($arrAtequip[1][0]) 
        {
            $unik = $unik * 2;
        }
    }
    elseif ($defender['clas'] == 'Mag') 
    {
        if ($defender['mana'] <= 0) 
        {
            $epower = ($arrDeequip[3][2] + $defender['cond'] + $arrDeequip[2][2] + $arrDeequip[4][2] + $arrDeequip[5][2]);
        } 
            else 
        {
            $eczarobr = ($defender['wisdom'] * $def_dspell -> fields['obr']) - (($def_dspell -> fields['obr'] * $defender['wisdom']) * ($arrDeequip[3][4] / 100));
            if ($arrDeequip[2][0]) 
            {
                $eczarobr = ($eczarobr - (($def_dspell -> fields['obr'] * $defender['wisdom']) * ($arrDeequip[2][4] / 100)));
                if ($eczarobr < 0) 
                {
                    $eczarobr = 0;
                }
            }
            if ($arrDeequip[4][0]) 
            {
                $eczarobr = ($eczarobr - (($def_dspell -> fields['obr'] * $defender['wisdom']) * ($arrDeequip[4][4] / 100)));
                if ($eczarobr < 0) 
                {
                    $eczarobr = 0;
                }
            }
            if ($arrDeequip[5][0]) 
            {
                $eczarobr = ($eczarobr - (($def_dspell -> fields['obr'] * $defender['wisdom']) * ($arrDeequip[5][4] / 100)));
                if ($eczarobr < 0) 
                {
                    $eczarobr = 0;
                }
            }
            if ($arrDeequip[7][0]) 
            {
                $intN = 6 - (int)($arrDeequip[7][4] / 20);
                $intBonus = (10 / $intN) * $defender['level'] * rand(1, $intN);
                $eczarobr = ($eczarobr + $intBonus);
            }
            $epower = ($arrDeequip[3][2] + $eczarobr + $defender['cond'] + $arrDeequip[2][2] + $arrDeequip[4][2] + $arrDeequip[5][2]);
        }
        if ($arrAtequip[1][0]) 
        {
            $unik = $unik * 2;
        }
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
    if ($attacker['clas'] == 'Rzemieślnik')
    {
        $mypower = $mypower - ($mypower / 4);
    }
    //Shield block chance
    $intBlock = 0;
    if ($arrDeequip[5][0])
      {
	$intBlock = ceil($arrDeequip[5][2] / 5);
	if ($intBlock > 20)
	  {
	    $intBlock = 20;
	  }
      }

    $arrLocations = array('w tułów', 'w głowę', 'w nogę', 'w rękę');
    $intHit = rand(0, 3);
    
    /**
    * Start fight
    */
    while ($round <= $attackstr && $defender['hp'] >= 0) 
    {
        $rzut1 = (rand(1, $attacker['level']) * 10);
        $mypower = ($mypower + $rzut1);
        $rzut2 = (rand(1, $defender['level']) * 10);
        $epower = ($epower + $rzut2);
	$blnMiss = FALSE;
        if ($attack_stam > $attacker['cond']) 
        {
            $mypower = 0;
            $unik = 0;
        }
        if ($def_stam > $defender['cond']) 
        {
            $epower = 0;
            if ($defender['clas'] == 'Mag' && $defender['mana'] >= $def_dspell -> fields['poziom'] && $def_dspell -> fields['id'])
            {
                $epower = $eczarobr;
            }
            $unik = 0;
        }
        $attackdmg = ($mypower - $epower);
        if ($attackdmg <= 0) 
        {
            $attackdmg = 0;
        }
        $szansa = rand(1,100);
        if ($arrAtequip[0][0]) 
        {
            $attack_stam += $arrAtequip[0][4];
        } 
	elseif ($arrAtequip[1][0]) 
        {
            $attack_stam += $arrAtequip[1][4];
        }
	if ($arrAtequip[11][0])
	  {
	    $attack_stam += $arrAtequip[11][4];
	  }
        /**
        * Player dodge
        */
        if ($unik >= $szansa && $szansa < 90 && $def_stam <= $defender['cond']) 
        {
	  if (($attack_bspell -> fields['id'] && $attacker['mana'] > $attack_bspell -> fields['poziom']) || (($arrAtequip[0][6] > $attack_durwep) || ($arrAtequip[11][6] > $attack_durwep)) || ($arrAtequip[1][6] > $attack_durwep && $arrAtequip[6][6] > $attack_durwep))
            {
                $strMessage = $strMessage."<b>".$defender['user']."</b> ".P_DODGE." <b>".$attacker['user']."</b><br />";
                if ($arrAtequip[1][0] && $arrAtequip[6][6] > $attack_durwep) 
                {
                    $attack_durwep = ($attack_durwep + 1);
                }
                $def_miss = ($def_miss + 1);
                $def_stam = ($def_stam + $arrDeequip[3][4] + 1);
            }
	    $blnMiss = TRUE;
        } 
	//Player block attack with shield
	$szansa = rand(1, 100);
	if ($szansa <= $intBlock && !$blnMiss)
	  {
	    if (($attack_bspell -> fields['id'] && $attacker['mana'] > $attack_bspell -> fields['poziom']) || (($arrAtequip[0][6] > $attack_durwep) || ($arrAtequip[11][6] > $attack_durwep)) || ($arrAtequip[1][6] > $attack_durwep && $arrAtequip[6][6] > $attack_durwep))
	      {
                $strMessage = $strMessage."<b>".$defender['user']."</b> zablokował tarczą atak <b>".$attacker['user']."</b><br />";
                if ($arrAtequip[1][0] && $arrAtequip[6][6] > $attack_durwep) 
                {
                    $attack_durwep = ($attack_durwep + 1);
                }
                $def_stam = ($def_stam + $arrDeequip[5][4] + 1);
	      }
	    $def_durarm[3]++;
	    $blnMiss = TRUE;
	  }
	if ($attack_stam <= $attacker['cond'] && !$blnMiss) 
        {
            $rzut = rand(1, 1000) / 10;
            $intRoll = rand(1, 100);
            /**
             * Kill with one blow
             */
            if ($krytyk >= $rzut && $intRoll <= $krytyk) 
            {
                if ($arrDeequip[3][0] || $arrDeequip[2][0] || $arrDeequip[4][0] || $arrDeequip[5][0]) 
                {
                    $efekt = rand(0,$number);
		    switch ($earmor[$efekt])
		      {
		      case 'torso':
			$intHit = 0;
			break;
		      case 'head':
			$intHit = 1;
			break;
		      case 'legs':
			$intHit = 2;
			break;
		      case 'shield':
			$intHit = 3;
			break;
		      }
		    $def_durarm[$intHit] ++;
                }
                /**
                 * Count lost mana by defender
                 */
                if ($def_dspell -> fields['id']) 
                {
                    $lost_mana = ceil($def_dspell -> fields['poziom'] / 2.5);
		    if ($defender['antidote'] != 'N')
		      {
			if ($arrAtequip[0][3] == 'N')
			  {
			    $lost_mana = $lost_mana + $arrAtequip[0][8];
			  }
			if ($arrAtequip[6][3] == 'N')
			  {
			    $lost_mana = $lost_mana + $arrAtequip[6][8];
			  }
		      }
                    $lost_mana = $lost_mana - (int)($defender['magic'] / 25);
                    if ($lost_mana < 1)
                    {
                        $lost_mana = 1;
                    }
                    $defender['mana'] = ($defender['mana'] - $lost_mana);
                }
                if (($arrAtequip[0][6] > $attack_durwep || $arrAtequip[11][6] > $attack_durwep) || ($arrAtequip[1][6] > $attack_durwep && $arrAtequip[6][6] > $attack_durwep)) 
                {
                    $attack_durwep = ($attack_durwep + 1);
                    $attack_attack = ($attack_attack + 1);
                    $defender['hp'] = 0;
		    if (!isset($efekt))
		      {
			$intHit = rand(0, 3);
		      }
		    $strMessage = $strMessage.showcritical($arrLocations[$intHit], $strAtype, 'pvp', $defender['user'], $attacker['user']);
                }
                elseif ($attack_bspell -> fields['id'] && $attacker['mana'] > $attack_bspell -> fields['poziom'] && (!$arrAtequip[0][0] && !$arrAtequip[1][0])) 
                {
                    if ($pech > 5) 
                    {
                        $attack_magic = ($attack_magic + 1);
                        $defender['hp'] = 0;
			if (!isset($efekt))
			  {
			    $intHit = rand(0, 3);
			  }
			$strMessage = $strMessage.showcritical($arrLocations[$intHit], $strAtype, 'pvp', $defender['user'], $attacker['user']);
                    } 
                        else 
                    {
                        $pechowy = rand(1,100);
			if ($pechowy <= 25) 
			  {
			    $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS1." <b>".$attack_bspell -> fields['poziom']."</b> ".MANA.".<br />";
                            $attacker['mana'] = ($attacker['mana'] - $attack_bspell -> fields['poziom']);
			  }
			elseif ($pechowy > 25 && $pechowy <= 45)
			  {
			    $strMessage = $strMessage."<b>".$attacker['user']."</b> zapatrzył się na szybko poruszającego się żółwia i stracił koncentrację.<br />";
			  }
			elseif ($pechowy > 45 && $pechowy <= 50)
			  {
			    $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS2.".<br />";
                            $attacker['mana'] = 0;
			  }
			elseif ($pechowy > 50 && $pechowy <= 55)
			  {
			     $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS3." ".$mypower." ".HP."!<br />";
			     $attacker['hp'] = ($attacker['hp'] - $mypower);			  
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
			    $defender['hp'] -= $intDamage;
			    $attacker['mana'] -= $attack_bspell->fields['poziom'];
			    $strMessage = $strMessage."<b>".$attacker['user']."</b> nie do końca opanował zaklęcie, dlatego jego czar zadaje <b>".$intDamage."</b> obrażeń. (".$defender['hp']." zostało)<br />";
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
			    $defender['hp'] -= $intDamage;
			    $attacker['mana'] -= $attack_bspell->fields['poziom'];
			    $attacker['hp'] -= $intDamage;
			    $strMesage = $strMessage."<b>".$attacker['user']."</b> próbował rzucić zaklęcie, ale eksplodowało ono w rękach, raniąc jego oraz wroga. Traci przez to ".$intDamage." punktów życia (".$attacker['hp']." zostało), <b>".$defender['user']."</b> otrzymuje ".$intDamage." obrażeń (".$defender['hp']." zostało)<br />";
			  }
                        break;
                    }
                }
                break;
            } 
                else 
            {
	      if (($arrAtequip[0][6] > $attack_durwep || $arrAtequip[11][6] > $attack_durwep) || ($arrAtequip[1][6] > $attack_durwep && $arrAtequip[6][6] > $attack_durwep)) 
                {
                    if ($krytyk >= $rzut)
                    {
                        if ($arrAtequip[0][0])
                        {
                            $fltAbility = $attacker['attack'];
                        }
                            else
                        {
                            $fltAbility = $attacker['shoot'];
                        }
                        if ($intRoll <= 10)
                        {
                            $attackdmg = $attackdmg + $epower;
                        }
                        if ($intRoll > 10 && $intRoll <= 40)
                        {
                            $attackdmg = $attackdmg + $mypower + $fltAbility;
                        }
                        if ($intRoll > 40)
                        {
                            $attackdmg = $attackdmg + $fltAbility;
                        }
                    }
                    $attack_durwep = ($attack_durwep + 1);
                    $defender['hp'] -= $attackdmg;
		    $intAttackdmg = $attackdmg;
		    if ($defender['antidote'] != 'I')
		      {
			if ($arrAtequip[0][3] == 'I')
			  {
			    $defender['hp'] -= $arrAtequip[0][8];
			    $intAttackdmg += $arrAtequip[0][8];
			  }
			if ($arrAtequip[6][3] == 'I')
			  {
			    $defender['hp'] -= $arrAtequip[6][8];
			    $intAttackdmg += $arrAtequip[6][8];
			  }
		      }
		    if ($arrDeequip[3][0] || $arrDeequip[2][0] || $arrDeequip[4][0] || $arrDeequip[5][0]) 
		      {
			$efekt = rand(0,$number);
			switch ($earmor[$efekt])
			  {
			  case 'torso':
			    $intHit = 0;
			    break;
			  case 'head':
			    $intHit = 1;
			    break;
			  case 'legs':
			    $intHit = 2;
			    break;
			  case 'shield':
			    $intHit = 3;
			    break;
			  }
			$def_durarm[$intHit] ++;
		      }
		    if (!isset($efekt))
		      {
			$intHit = rand(0, 3);
		      }
                    $strMessage = $strMessage."<b>".$attacker['user']."</b> ".P_ATTACK." <b>".$defender['user']."</b> ".$arrLocations[$intHit]." ".B_DAMAGE." <b>".$intAttackdmg."</b> ".DAMAGE."! (".$defender['hp']." ".LEFT.")<br />";
                    if ($attackdmg > 0) 
                    {
                        $attack_attack = ($attack_attack + 1);
                    }
                    /**
                     * Count lost mana for defender
                     */
                    if ($def_dspell -> fields['id']) 
                    {
                        $lost_mana = ceil($def_dspell -> fields['poziom'] / 2.5);
			if ($defender['antidote'] != 'N')
			  {
			    if ($arrAtequip[0][3] == 'N')
			      {
				$lost_mana = $lost_mana + $arrAtequip[0][8];
			      }
			    if ($arrAtequip[6][3] == 'N')
			      {
				$lost_mana = $lost_mana + $arrAtequip[6][8];
			      }
			  }
                        $lost_mana = $lost_mana - (int)($defender['magic'] / 25);
                        if ($lost_mana < 1)
                        {
                            $lost_mana = 1;
                        }
                        $defender['mana'] = ($defender['mana'] - $lost_mana);
                    }
                    if ($defender['hp'] <= 0) 
                    {
                        break;
                    }
                }
	      elseif ($attack_bspell -> fields['id'] && $attacker['mana'] > $attack_bspell -> fields['poziom'] && (!$arrAtequip[0][0] && !$arrAtequip[1][0])) 
                {
                    if ($pech > 5) 
                    {
                        if ($krytyk >= $rzut)
                        {
                            if ($intRoll <= 10)
                            {
                                $attackdmg = $attackdmg + $epower;
                            }
                            if ($intRoll > 10 && $intRoll <= 40)
                            {
                                $attackdmg = $attackdmg + $mypower + $attacker['magic'];
                            }
                            if ($intRoll > 40)
                            {
                                $attackdmg = $attackdmg + $attacker['magic'];
                            }
                        }
                        if ($def_dspell -> fields['id']) 
                        {
                            $lost_mana = ceil($def_dspell -> fields['poziom'] / 2.5);
			    if ($defender['antidote'] != 'N')
			      {
				if ($arrAtequip[0][3] == 'N')
				  {
				    $lost_mana = $lost_mana + $arrAtequip[0][8];
				  }
				if ($arrAtequip[6][3] == 'N')
				  {
				    $lost_mana = $lost_mana + $arrAtequip[6][8];
				  }
			      }
                            $lost_mana = $lost_mana - (int)($defender['magic'] / 25);
                            if ($lost_mana < 1)
                            {
                                $lost_mana = 1;
                            }
                            $defender['mana'] = ($defender['mana'] - $lost_mana);
                        }
                        $lost_mana = ceil($attack_bspell -> fields['poziom'] / 2.5);
                        $lost_mana = $lost_mana - (int)($attacker['magic'] / 25);
                        if ($lost_mana < 1)
                        {
                            $lost_mana = 1;
                        }
                        $attacker['mana'] = ($attacker['mana'] - $lost_mana);
                        if ($defender['clas'] == 'Barbarzyńca') 
                        {
                            $roll = rand(1,100);
                            $chance = ceil($defender['level'] / 5);
                            if ($chance > 20) 
                            {
                                $chance = 20;
                            }
                            if ($roll < $chance) 
                            {
                                $strMessage = $strMessage."<b>".$attacker['user']."</b> ".P_ATTACK." <b>".$defender['user']."</b> ".B_BAR."! (".$defender['hp']." ".LEFT.")<br />";
                                break;
                            }
                        }
                        $defender['hp'] = ($defender['hp'] - $attackdmg);
			if ($arrDeequip[3][0] || $arrDeequip[2][0] || $arrDeequip[4][0] || $arrDeequip[5][0]) 
			  {
			    $efekt = rand(0,$number);
			    switch ($earmor[$efekt])
			      {
			      case 'torso':
				$intHit = 0;
				break;
			      case 'head':
				$intHit = 1;
				break;
			      case 'legs':
				$intHit = 2;
				break;
			      case 'shield':
				$intHit = 3;
				break;
			      }
			    $def_durarm[$intHit] ++;
			  }
			if (!isset($efekt))
			  {
			    $intHit = rand(0, 3);
			  }
                        $strMessage = $strMessage."<b>".$attacker['user']."</b> ".P_ATTACK." <b>".$arrLocations[$intHit]." ".$defender['user']."</b> ".B_DAMAGE." <b>".$attackdmg."</b> ".DAMAGE."! (".$defender['hp']." ".LEFT.")<br />";
                        if ($attackdmg > 0) 
                        {
                            $attack_magic = ($attack_magic + 1);
                        }
                        if ($defender['hp'] <= 0) 
                        {
                            break;
                        }
                    } 
                        else 
                    {
                        $pechowy = rand(1,100);
			if ($pechowy <= 25) 
			  {
			    $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS1." <b>".$attack_bspell -> fields['poziom']."</b> ".MANA.".<br />";
                            $attacker['mana'] = ($attacker['mana'] - $attack_bspell -> fields['poziom']);
			  }
			elseif ($pechowy > 25 && $pechowy <= 45)
			  {
			    $strMessage = $strMessage."<b>".$attacker['user']."</b> zapatrzył się na szybko poruszającego się żółwia i stracił koncentrację.<br />";
			  }
			elseif ($pechowy > 45 && $pechowy <= 50)
			  {
			    $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS2.".<br />";
                            $attacker['mana'] = 0;
			  }
			elseif ($pechowy > 50 && $pechowy <= 55)
			  {
			     $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS3." ".$mypower." ".HP."!<br />";
			     $attacker['hp'] = ($attacker['hp'] - $mypower);			  
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
			    $defender['hp'] -= $intDamage;
			    $attacker['mana'] -= $attack_bspell->fields['poziom'];
			    $strMessage = $strMessage."<b>".$attacker['user']."</b> nie do końca opanował zaklęcie, dlatego jego czar zadaje <b>".$intDamage."</b> obrażeń. (".$defender['hp']." zostało)<br />";
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
			    $defender['hp'] -= $intDamage;
			    $attacker['mana'] -= $attack_bspell->fields['poziom'];
			    $attacker['hp'] -= $intDamage;
			    $strMesage = $strMessage."<b>".$attacker['user']."</b> próbował rzucić zaklęcie, ale eksplodowało ono w rękach, raniąc jego oraz wroga. Traci przez to ".$intDamage." punktów życia (".$attacker['hp']." zostało), <b>".$defender['user']."</b> otrzymuje ".$intDamage." obrażeń (".$defender['hp']." zostało)<br />";
			  }
                        break;
                    }
                }
            }
        }
        $round = ($round + 1);
    }

    if ((($attack_stam > $attacker['cond'] && $def_stam > $defender['cond']) || ($runda >= 25)) && ($attacker['hp'] > 0 && $defender['hp'] > 0)) 
      {
	if ($attacker['hp'] < 1)
	  {
	    $attacker['hp'] = 1;
	  }
	if ($defender['hp'] < 1)
	  {
	    $defender['hp'] = 1;
	  }
        $strMessage = $strMessage."<br />".B_NO_WIN."<br />";
        $smarty -> assign ("Message", $strMessage);
        $smarty -> display ('error1.tpl');
        /**
         * Count gained dodge skill (attacker)
         */
        $intDamount = 0;
        $intNewfib = 1;
        $intOldfib = 1;
        $intTempfib = 1;
        while ($intNewfib)
        {
            $attack_miss = $attack_miss - $intNewfib;
            if ($attack_miss < 0)
            {
                break;
            }
                else
            {
                $intDamount ++;
                if ($intNewfib == 1)
                {
                    $intNewfib = 3;
                }
                    else
                {
                    $intTempfib = $intNewfib;
                    $intNewfib = $intNewfib + $intOldfib;
                    $intOldfib = $intTempfib;
                }
            }
        }
        gainability($attacker['id'], $attacker['user'], $intDamount, 0, $attack_magic, $attacker['mana'], $starter,'');
        /**
         * Count gained dodge skill (defender)
         */
        $intDamount = 0;
        $intNewfib = 1;
        $intOldfib = 1;
        $intTempfib = 1;
        while ($intNewfib)
        {
            $def_miss = $def_miss - $intNewfib;
            if ($def_miss < 0)
            {
                break;
            }
                else
            {
                $intDamount ++;
                if ($intNewfib == 1)
                {
                    $intNewfib = 3;
                }
                    else
                {
                    $intTempfib = $intNewfib;
                    $intNewfib = $intNewfib + $intOldfib;
                    $intOldfib = $intTempfib;
                }
            }
        }
        gainability($defender['id'], $defender['user'], $intDamount, 0, $def_magic, $defender['mana'], $starter,'');
        if ($arrAtequip[0][0]) 
        {
            gainability($attacker['id'], $attacker['user'], 0, $attack_attack, 0, $attacker['mana'], $starter, 'weapon');
            lostitem($attack_durwep, $arrAtequip[0][6], YOU_WEAPON, $attacker['id'], $arrAtequip[0][0], $starter, HAS_BEEN1, $attacker['level']);
        }
        if ($arrAtequip[1][0]) 
        {
            gainability($attacker['id'], $attacker['user'], 0, $attack_attack, 0, $attacker['mana'], $starter, 'bow');
            lostitem($attack_durwep, $arrAtequip[1][6], YOU_WEAPON, $attacker['id'], $arrAtequip[1][0], $starter, HAS_BEEN1, $attacker['level']);
            lostitem($attack_durwep, $arrAtequip[6][6], YOU_QUIVER, $attacker['id'], $arrAtequip[6][0], $starter, HAS_BEEN1, $attacker['level']);
        }
	if ($arrAtequip[11][0])
	  {
	    if (!$arrAtequip[0][0])
	      {
		gainability($attacker['id'], $attacker['user'], 0, $attack_attack, 0, $attacker['mana'], $starter, 'weapon');
	      }
	    lostitem($attack_durwep, $arrAtequip[11][6], YOU_WEAPON, $attacker['id'], $arrAtequip[11][0], $starter, HAS_BEEN1, $attacker['level']);
	  }
        if ($arrDeequip[0][0]) 
        {
            gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'weapon');
            lostitem($def_durwep, $arrDeequip[0][6], YOU_WEAPON, $defender['id'], $arrDeequip[0][0], $starter, HAS_BEEN1, $defender['level']);
        }
        if ($arrDeequip[1][0]) 
        {
            gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'bow');
            lostitem($def_durwep, $arrDeequip[1][6], YOU_WEAPON, $defender['id'], $arrDeequip[1][0], $starter, HAS_BEEN1, $defender['level']);
            lostitem($def_durwep, $arrDeequip[6][6], YOU_QUIVER, $defender['id'], $arrDeequip[6][0], $starter, HAS_BEEN1, $defender['level']);
        }
	if ($arrDeequip[11][0])
	  {
	    if (!$arrDeequip[0][0])
	      {
		gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'weapon');
	      }
	    lostitem($def_durwep, $arrDeequip[11][6], YOU_WEAPON, $defender['id'], $arrDeequip[11][0], $starter, HAS_BEEN1, $defender['level']);
	  }
        if ($arrDeequip[3][0]) 
        {
            lostitem($def_durarm[0], $arrDeequip[3][6], YOU_ARMOR, $defender['id'], $arrDeequip[3][0], $starter, HAS_BEEN1, $defender['level']);
        }
        if ($arrDeequip[2][0]) 
        {
            lostitem($def_durarm[1], $arrDeequip[2][6], YOU_HELMET, $defender['id'], $arrDeequip[2][0], $starter, HAS_BEEN1, $defender['level']);
        }
        if ($arrDeequip[4][0]) 
        {
            lostitem($def_durarm[2], $arrDeequip[4][6], YOU_LEGS, $defender['id'], $arrDeequip[4][0], $starter, HAS_BEEN2, $defender['level']);
        }
        if ($arrDeequip[5][0]) 
        {
            lostitem($def_durarm[3], $arrDeequip[5][6], YOU_SHIELD, $defender['id'], $arrDeequip[5][0], $starter, HAS_BEEN1, $defender['level']);
        }
        if ($arrAtequip[3][0]) 
        {
            lostitem($attack_durarm[0], $arrAtequip[3][6], YOU_ARMOR, $attacker['id'], $arrAtequip[3][0], $starter, HAS_BEEN1, $attacker['level']);
        }
        if ($arrAtequip[2][0]) 
        {
            lostitem($attack_durarm[1], $arrAtequip[2][6], YOU_HELMET, $attacker['id'], $arrAtequip[2][0], $starter, HAS_BEEN1, $attacker['level']);
        }
        if ($arrAtequip[4][0]) 
        {
            lostitem($attack_durarm[2], $arrAtequip[4][6], YOU_LEGS, $attacker['id'], $arrAtequip[4][0], $starter, HAS_BEEN2, $attacker['level']);
        }
        if ($arrAtequip[5][0]) 
        {
            lostitem($attack_durarm[3], $arrAtequip[5][6], YOU_SHIELD, $attacker['id'], $arrAtequip[5][0], $starter, HAS_BEEN1, $attacker['level']);
        }
        $db -> Execute("UPDATE `players` SET `hp`=".$attacker['hp'].", `bless`='', `blessval`=0 WHERE `id`=".$attacker['id']);
        $db -> Execute("UPDATE `players` SET `hp`=".$defender['hp'].", `bless`='', `blessval`=0 WHERE `id`=".$defender['id']);
        if ($attacker['id'] == $starter) 
        {
            $attacktext = YOU_ATTACK_BUT;
            $defendtext = YOU_ATTACKED_BUT;
            $startuser = $attacker['user'];
            $secuser = $defender['user'];
        } 
            else 
        {
            $defendtext = YOU_ATTACK_BUT;
            $attacktext = YOU_ATTACKED_BUT;
            $startuser = $defender['user'];
            $secuser = $attacker['user'];
        }
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('".L_PLAYER." ".$startuser." ".L_ATTACK." ".$secuser." ".L_BATTLE."')");
        $strDate = $db -> DBDate($newdate);
        /**
         * Send battle logs
         */
        if (($attacker['battlelog'] == 'Y') || ($attacker['id'] == $starter && $attacker['battlelog'] == 'A') || ($attacker['id'] != $starter && $attacker['battlelog'] == 'D'))
        {
            $strSubject = T_SUBJECT.$defender['user'].T_SUB_ID.$defender['id'];
            $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".T_SENDER."','0',".$attacker['id'].",'".$strSubject."','".$strMessage."', ".$strDate.")");
        }
        if (($defender['battlelog'] == 'Y') || ($defender['id'] == $starter && $defender['battlelog'] == 'A') || ($defender['id'] != $starter && $defender['battlelog'] == 'D'))
        {
             $strSubject = T_SUBJECT.$attacker['user'].T_SUB_ID.$attacker['id'];
            $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".T_SENDER."','0',".$defender['id'].",'".$strSubject."','".$strMessage."', ".$strDate.")");
        }
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$attacker['id'].",'".$attacktext." ".L_BATTLE2."  <b><a href=view.php?view=".$defender['id'].">".$defender['user']."</a></b> (poziom: ".$defender['level'].') '.L_ID.'<b>'.$defender['id']."</b>.', ".$strDate.", 'B')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$defender['id'].",'".$defendtext." ".L_BATTLE2." <b><a href=view.php?view=".$attacker['id'].">".$attacker['user']."</a></b> (poziom: ".$attacker['level'].') '.L_ID.'<b>'.$attacker['id']."</b>.', ".$strDate.", 'B')");
        require_once("includes/foot.php");
        exit;
    }
    if ($defender['hp'] <= 0) 
    {
        $defender['hp'] = 0;
	if ($attacker['hp'] < 1)
	  {
	    $attacker['hp'] = 1;
	  }
        $strMessage = $strMessage."<br /><b>".$attacker['user']."</b> ".B_WIN."!<br />";
        $roll = rand (1,20);
        if ($roll == 20 && $defender['maps'] > 0) 
        {
            $db -> Execute("UPDATE `players` SET `maps`=`maps`+1 WHERE id=".$attacker['id']);
            $db -> Execute("UPDATE `players` SET `maps`=`maps`-1 WHERE id=".$defender['id']);
            $text = AND_MAP;
        } 
            else 
        {
            $text = '';
        }
        $expgain = (rand(5,10) * $defender['level']);
        $creditgain = floor($defender['credits'] / 10);
        if ($creditgain < 0)
        {
            $creditgain = 0;
        }
        $strMessage = $strMessage."<b>".$attacker['user']."</b> ".HE_GET." <b>".$expgain."</b> ".EXPERIENCE." <b>".$creditgain."</b> ".GOLD_COINS." ".$text."<br />";
        $smarty -> assign ("Message", $strMessage);
        $smarty -> display ('error1.tpl');
        /**
         * Count gained dodge skill (attacker)
         */
        $intDamount = 0;
        $intNewfib = 1;
        $intOldfib = 1;
        $intTempfib = 1;
        while ($intNewfib)
        {
            $attack_miss = $attack_miss - $intNewfib;
            if ($attack_miss < 0)
            {
                break;
            }
                else
            {
                $intDamount ++;
                if ($intNewfib == 1)
                {
                    $intNewfib = 3;
                }
                    else
                {
                    $intTempfib = $intNewfib;
                    $intNewfib = $intNewfib + $intOldfib;
                    $intOldfib = $intTempfib;
                }
            }
        }
        gainability($attacker['id'], $attacker['user'], $intDamount, 0, $attack_magic, $attacker['mana'], $starter,'');
        /**
         * Count gained dodge skill (defender)
         */
        $intDamount = 0;
        $intNewfib = 1;
        $intOldfib = 1;
        $intTempfib = 1;
        while ($intNewfib)
        {
            $def_miss = $def_miss - $intNewfib;
            if ($def_miss < 0)
            {
                break;
            }
                else
            {
                $intDamount ++;
                if ($intNewfib == 1)
                {
                    $intNewfib = 3;
                }
                    else
                {
                    $intTempfib = $intNewfib;
                    $intNewfib = $intNewfib + $intOldfib;
                    $intOldfib = $intTempfib;
                }
            }
        }
        gainability($defender['id'], $defender['user'], $intDamount, 0, $def_magic, $defender['mana'], $starter,'');
        if ($arrAtequip[0][0]) 
        {
            gainability($attacker['id'], $attacker['user'], 0, $attack_attack, 0, $attacker['mana'], $starter, 'weapon');
            lostitem($attack_durwep, $arrAtequip[0][6], YOU_WEAPON, $attacker['id'], $arrAtequip[0][0], $starter, HAS_BEEN1, $attacker['level']);
        }
        if ($arrAtequip[1][0]) 
        {
            gainability($attacker['id'], $attacker['user'], 0, $attack_attack, 0, $attacker['mana'], $starter, 'bow');
            lostitem($attack_durwep, $arrAtequip[1][6], YOU_WEAPON, $attacker['id'], $arrAtequip[1][0], $starter, HAS_BEEN1, $attacker['level']);
            lostitem($attack_durwep, $arrAtequip[6][6], YOU_QUIVER, $attacker['id'], $arrAtequip[6][0], $starter, HAS_BEEN1, $attacker['level']);
        }
	if ($arrAtequip[11][0])
	  {
	    if (!$arrAtequip[0][0])
	      {
		gainability($attacker['id'], $attacker['user'], 0, $attack_attack, 0, $attacker['mana'], $starter, 'weapon');
	      }
	    lostitem($attack_durwep, $arrAtequip[11][6], YOU_WEAPON, $attacker['id'], $arrAtequip[11][0], $starter, HAS_BEEN1, $attacker['level']);
	  }
        if ($arrDeequip[0][0]) 
        {
            gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'weapon');
            lostitem($def_durwep, $arrDeequip[0][6], YOU_WEAPON, $defender['id'], $arrDeequip[0][0], $starter, HAS_BEEN1, $defender['level']);
        }
        if ($arrDeequip[1][0]) 
        {
            gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'bow');
            lostitem($def_durwep, $arrDeequip[1][6], YOU_WEAPON, $defender['id'], $arrDeequip[1][0], $starter, HAS_BEEN1, $defender['level']);
            lostitem($def_durwep, $arrDeequip[6][6], YOU_QUIVER, $defender['id'], $arrDeequip[6][0], $starter, HAS_BEEN1, $defender['level']);
        }
	if ($arrDeequip[11][0])
	  {
	    if (!$arrDeequip[0][0])
	      {
		gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'weapon');
	      }
	    lostitem($def_durwep, $arrDeequip[11][6], YOU_WEAPON, $defender['id'], $arrDeequip[11][0], $starter, HAS_BEEN1, $defender['level']);
	  }
        if ($arrDeequip[3][0]) 
        {
            lostitem($def_durarm[0], $arrDeequip[3][6], YOU_ARMOR, $defender['id'], $arrDeequip[3][0], $starter, HAS_BEEN1, $defender['level']);
        }
        if ($arrDeequip[2][0]) 
        {
            lostitem($def_durarm[1], $arrDeequip[2][6], YOU_HELMET, $defender['id'], $arrDeequip[2][0], $starter, HAS_BEEN1, $defender['level']);
        }
        if ($arrDeequip[4][0]) 
        {
            lostitem($def_durarm[2], $arrDeequip[4][6], YOU_LEGS, $defender['id'], $arrDeequip[4][0], $starter, HAS_BEEN2, $defender['level']);
        }
        if ($arrDeequip[5][0]) 
        {
            lostitem($def_durarm[3], $arrDeequip[5][6], YOU_SHIELD, $defender['id'], $arrDeequip[5][0], $starter, HAS_BEEN1, $defender['level']);
        }
        if ($arrAtequip[3][0]) 
        {
            lostitem($attack_durarm[0], $arrAtequip[3][6], YOU_ARMOR, $attacker['id'], $arrAtequip[3][0], $starter, HAS_BEEN1, $attacker['level']);
        }
        if ($arrAtequip[2][0]) 
        {
            lostitem($attack_durarm[1], $arrAtequip[2][6], YOU_HELMET, $attacker['id'], $arrAtequip[2][0], $starter, HAS_BEEN1, $attacker['level']);
        }
        if ($arrAtequip[4][0]) 
        {
            lostitem($attack_durarm[2], $arrAtequip[4][6], YOU_LEGS, $attacker['id'], $arrAtequip[4][0], $starter, HAS_BEEN2, $attacker['level']);
        }
        if ($arrAtequip[5][0]) 
        {
            lostitem($attack_durarm[3], $arrAtequip[5][6], YOU_SHIELD, $attacker['id'], $arrAtequip[5][0], $starter, HAS_BEEN1, $attacker['level']);
        }
        $db -> Execute("UPDATE `players` SET `hp`=".$attacker['hp'].", `credits`=`credits`+".$creditgain.", `wins`=`wins`+1, `lastkilled`='".'<a href="view.php?view='.$defender['id'].'">'.$defender['user']."</a>', `bless`='', `blessval`=0 WHERE `id`=".$attacker['id']);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$creditgain.", `losses`=`losses`+1, `lastkilledby`='".'<a href="view.php?view='.$attacker['id'].'">'.$attacker['user']."</a>', `bless`='', `blessval`=0 WHERE `id`=".$defender['id']);
        checkexp($attacker['exp'],$expgain,$attacker['level'],$attacker['race'],$attacker['user'],$attacker['id'],$defender['id'],$defender['user'],$attacker['id'],'',0);
        if ($attacker['id'] == $starter) 
        {
            $attacktext = YOU_ATTACK_AND;
            $startuser = $attacker['user'];
            $secuser = $defender['user'];
        } 
            else 
        {
            $attacktext = YOU_ATTACKED_AND;
            $startuser = $defender['user'];
            $secuser = $attacker['user'];
        }
        loststat($defender['id'], $defender['oldstats'], $attacker['id'], $attacker['user'], $starter, $defender['antidote'], $attacker['level']);
        $db -> Execute("INSERT INTO `events` (`text`) VALUES('".L_PLAYER." ".$startuser." ".L_ATTACK." ".$secuser.". ".BATTLE_WIN." ".$attacker['user']."')");
        $strDate = $db -> DBDate($newdate);
        if (($attacker['battlelog'] == 'Y')  || ($attacker['id'] == $starter && $attacker['battlelog'] == 'A') || ($attacker['id'] != $starter && $attacker['battlelog'] == 'D'))
        {
            $strSubject = T_SUBJECT.$defender['user'].T_SUB_ID.$defender['id'];
            $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".T_SENDER."','0',".$attacker['id'].",'".$strSubject."','".$strMessage."', ".$strDate.")");
        }
        if (($defender['battlelog'] == 'Y') || ($defender['id'] == $starter && $defender['battlelog'] == 'A') || ($defender['id'] != $starter && $defender['battlelog'] == 'D'))
        {
            $strSubject = T_SUBJECT.$attacker['user'].T_SUB_ID.$attacker['id'];
            $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`) VALUES('".T_SENDER."','0',".$defender['id'].",'".$strSubject."','".$strMessage."', ".$strDate.")");
        }
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$attacker['id'].",'".$attacktext." ".YOU_DEFEAT." <b><a href=view.php?view=".$defender['id'].">".$defender['user']."</a></b> (poziom: ".$defender['level'].') '.L_ID.'<b>'.$defender['id']."</b>. ".YOU_REWARD." <b>$expgain</b> ".EXPERIENCE." <b>$creditgain</b> ".GOLD_COINS.".', ".$strDate.", 'B')");
        require_once("includes/foot.php");
        exit;
    }
    attack1($defender, $attacker, $arrDeequip, $arrAtequip, $def_bspell, $attack_bspell, $def_dspell, $attack_dspell, $def_stam, $attack_stam, $def_miss, $attack_miss, $def_attack, $attack_attack, $def_magic, $attack_magic, $def_durwep, $attack_durwep, $def_durarm, $attack_durarm, $starter, $strMessage);
}
?>
