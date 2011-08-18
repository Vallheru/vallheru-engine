<?php
/**
 *   File functions:
 *   Function to fight PvP
 *
 *   @name                 : battle.php                            
 *   @copyright            : (C) 2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 18.08.2011
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
        $unik = (($defender['agility'] - $attacker['agility']) + ($defender['miss'] - $attacker['shoot']));
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
    }

    /**
    * Calculate dodge defender and power of attack, critical hit (weapon)
    */
    if ($arrAtequip[0][0] && $attack_durwep <= $arrAtequip[0][6]) 
    {
        $unik = (($defender['agility'] - $attacker['agility']) + ($defender['miss'] - $attacker['attack']));
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
    }

    /**
    * Calculate dodge defender and power of attack, critical hit (spell)
    */
    if ($attack_bspell -> fields['id'] && (!$arrAtequip[1][0] || $arrAtequip[0][0])) 
    {
        $unik = (($defender['agility'] - $attacker['agility']) + ($defender['miss'] - $attacker['magic']));
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
    if ($defender['clas'] == 'Rzemieślnik' || $defender['clas'] == 'Złodziej') 
    {
        $epower = ($arrDeequip[3][2] + $defender['cond'] + $arrDeequip[2][2] + $arrDeequip[4][2] + $arrDeequip[5][2]);
        if ($arrAtequip[1][0]) 
        {
            $unik = $unik * 2;
        }
    }
    if ($defender['clas'] == 'Mag') 
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
    if (!($attack_bspell -> fields['id'] && $attacker['mana'] > $attack_bspell -> fields['poziom']) && !($arrAtequip[0][6] > $attack_durwep) && !($arrAtequip[1][6] > $attack_durwep && $arrAtequip[6][6] > $attack_durwep)) 
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
    
    /**
    * Start fight
    */
    while ($round <= $attackstr && $defender['hp'] >= 0) 
    {
        $rzut1 = (rand(1, $attacker['level']) * 10);
        $mypower = ($mypower + $rzut1);
        $rzut2 = (rand(1, $defender['level']) * 10);
        $epower = ($epower + $rzut2);
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
            $attack_stam = ($attack_stam + $arrAtequip[0][4]);
        } 
            elseif ($arrAtequip[1][0]) 
        {
            $attack_stam = ($attack_stam + $arrAtequip[1][4]);
        }
        /**
        * Player dodge
        */
        if ($unik >= $szansa && $szansa < 90 && $def_stam <= $defender['cond']) 
        {
            if (($attack_bspell -> fields['id'] && $attacker['mana'] > $attack_bspell -> fields['poziom']) || ($arrAtequip[0][6] > $attack_durwep) || ($arrAtequip[1][6] > $attack_durwep && $arrAtequip[6][6] > $attack_durwep))
            {
                $strMessage = $strMessage."<b>".$defender['user']."</b> ".P_DODGE." <b>".$attacker['user']."</b><br />";
                if ($arrAtequip[1][0] && $arrAtequip[6][6] > $attack_durwep) 
                {
                    $attack_durwep = ($attack_durwep + 1);
                }
                $def_miss = ($def_miss + 1);
                $def_stam = ($def_stam + $arrDeequip[3][4] + 1);
            }
        } 
            elseif ($attack_stam <= $attacker['cond']) 
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
                    if ($earmor[$efekt] == 'torso') 
                    {
                        $def_durarm[0] = ($def_durarm[0] + 1);
                    }
                    if ($earmor[$efekt] == 'head') 
                    {
                        $def_durarm[1] = ($def_durarm[1] + 1);
                    }
                    if ($earmor[$efekt] == 'legs') 
                    {
                        $def_durarm[2] = ($def_durarm[2] + 1);
                    }
                    if ($earmor[$efekt] == 'shield') 
                    {
                        $def_durarm[3] = ($def_durarm[3] + 1);
                    }
                }
                /**
                 * Count lost mana by defender
                 */
                if ($def_dspell -> fields['id']) 
                {
                    $lost_mana = ceil($def_dspell -> fields['poziom'] / 2.5);
                    if ($arrAtequip[0][3] == 'N' && $defender['antidote'] != 'N')
                    {
                        $lost_mana = $lost_mana + $arrAtequip[0][8];
                    }
                    $lost_mana = $lost_mana - (int)($defender['magic'] / 25);
                    if ($lost_mana < 1)
                    {
                        $lost_mana = 1;
                    }
                    $defender['mana'] = ($defender['mana'] - $lost_mana);
                }
                if ($arrAtequip[0][6] > $attack_durwep || ($arrAtequip[1][6] > $attack_durwep && $arrAtequip[6][6] > $attack_durwep)) 
                {
                    $attack_durwep = ($attack_durwep + 1);
                    $attack_attack = ($attack_attack + 1);
                    $defender['hp'] = 0;
                    $strMessage = $strMessage."<b>".$attacker['user']."</b> ".P_ATTACK." <b>".$defender['user']."</b> ".AND_KILL." (".$defender['hp']." ".HP_LEFT.")<br />";
                }
                if ($attack_bspell -> fields['id'] && $attacker['mana'] > $attack_bspell -> fields['poziom']) 
                {
                    if ($pech > 5) 
                    {
                        $attack_magic = ($attack_magic + 1);
                        $defender['hp'] = 0;
                        $strMessage = $strMessage."<b>".$attacker['user']."</b> ".P_ATTACK." <b>".$defender['user']."</b> ".AND_KILL2." (".$defender['hp']." ".HP_LEFT.")<br />";
                    } 
                        else 
                    {
                        $pechowy = rand(1,100);
                        if ($pechowy <= 70) 
                        {
                            $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS1." <b>".$attack_bspell -> fields['poziom']."</b> ".MANA.".<br />";
                            $attacker['mana'] = ($attacker['mana'] - $attack_bspell -> fields['poziom']);
                        }
                        if ($pechowy > 70 && $pechowy <= 90) 
                        {
                            $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS2.".<br />";
                            $attacker['mana'] = 0;
                        }
                        if ($pechowy > 90) 
                        {
                            $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS3." ".$mypower." ".HP."!<br />";
                            $attacker['hp'] = ($attacker['hp'] - $mypower);
                        }
                        break;
                    }
                }
                break;
            } 
                else 
            {
                if ($arrAtequip[0][6] > $attack_durwep || ($arrAtequip[1][6] > $attack_durwep && $arrAtequip[6][6] > $attack_durwep)) 
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
                    $defender['hp'] = ($defender['hp'] - $attackdmg);
                    if ($arrAtequip[0][3] == 'I' && $defender['antidote'] != 'I')
                    {
                        $defender['hp'] = ($defender['hp'] - $arrAtequip[0][8]);
                        $intAttackdmg = $attackdmg + $arrAtequip[0][8];
                    }
                        else
                    {
                        $intAttackdmg = $attackdmg;
                    }
                    $strMessage = $strMessage."<b>".$attacker['user']."</b> ".P_ATTACK." <b>".$defender['user']."</b> ".B_DAMAGE." <b>".$intAttackdmg."</b> ".DAMAGE."! (".$defender['hp']." ".LEFT.")<br />";
                    if ($attackdmg > 0) 
                    {
                        $attack_attack = ($attack_attack + 1);
                    }
                    if ($arrDeequip[3][0] || $arrDeequip[2][0] || $arrDeequip[4][0] || $arrDeequip[5][0]) 
                    {
                        $efekt = rand(0,$number);
                        if ($earmor[$efekt] == 'torso') 
                        {
                            $def_durarm[0] = ($def_durarm[0] + 1);
                        }
                        if ($earmor[$efekt] == 'head') 
                        {
                            $def_durarm[1] = ($def_durarm[1] + 1);
                        }
                        if ($earmor[$efekt] == 'legs') 
                        {
                            $def_durarm[2] = ($def_durarm[2] + 1);
                        }
                        if ($earmor[$efekt] == 'shield') 
                        {
                            $def_durarm[3] = ($def_durarm[3] + 1);
                        }
                    }
                    /**
                     * Count lost mana for defender
                     */
                    if ($def_dspell -> fields['id']) 
                    {
                        $lost_mana = ceil($def_dspell -> fields['poziom'] / 2.5);
                        if ($arrAtequip[0][3] == 'N' && $defender['antidote'] != 'N')
                        {
                            $lost_mana = $lost_mana + $arrAtequip[0][8];
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
                if ($attack_bspell -> fields['id'] && $attacker['mana'] > $attack_bspell -> fields['poziom']) 
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
                            if ($arrAtequip[0][3] == 'N' && $defender['antidote'] != 'N')
                            {
                                $lost_mana = $lost_mana + $arrAtequip[0][8];
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
                            $chance = ceil($defender['level'] / 10);
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
                        $strMessage = $strMessage."<b>".$attacker['user']."</b> ".P_ATTACK." <b>".$defender['user']."</b> ".B_DAMAGE." <b>".$attackdmg."</b> ".DAMAGE."! (".$defender['hp']." ".LEFT.")<br />";
                        if ($attackdmg > 0) 
                        {
                            $attack_magic = ($attack_magic + 1);
                        }
                        if ($arrDeequip[3][0] || $arrDeequip[2][0] || $arrDeequip[4][0] || $arrDeequip[5][0]) 
                        {
                            $efekt = rand(0,$number);
                            if ($earmor[$efekt] == 'torso') 
                            {
                                $def_durarm[0] = ($def_durarm[0] + 1);
                            }
                            if ($earmor[$efekt] == 'head') 
                            {
                                $def_durarm[1] = ($def_durarm[1] + 1);
                            }
                            if ($earmor[$efekt] == 'legs') 
                            {
                                $def_durarm[2] = ($def_durarm[2] + 1);
                            }
                            if ($earmor[$efekt] == 'shield') 
                            {
                                $def_durarm[3] = ($def_durarm[3] + 1);
                            }
                        }
                        if ($defender['hp'] <= 0) 
                        {
                            break;
                        }
                    } 
                        else 
                    {
                        $pechowy = rand(1,100);
                        if ($pechowy <= 70) 
                        {
                            $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS1." <b>".$attack_bspell -> fields['poziom']."</b> ".MANA.".<br />";
                            $attacker['mana'] = ($attacker['mana'] - $attack_bspell -> fields['poziom']);
                        }
                        if ($pechowy > 70 && $pechowy <= 90) 
                        {
                            $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS2.".<br />";
                            $attacker['mana'] = 0;
                        }
                        if ($pechowy > 90) 
                        {
                            $strMessage = $strMessage."<b>".$attacker['user']."</b> ".YOU_MISS3." ".$mypower." ".HP."!<br />";
                            $attacker['hp'] = ($attacker['hp'] - $mypower);
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
            lostitem($attack_durwep, $arrAtequip[0][6], YOU_WEAPON, $attacker['id'], $arrAtequip[0][0], $starter, HAS_BEEN1);
        }
        if ($arrAtequip[1][0]) 
        {
            gainability($attacker['id'], $attacker['user'], 0, $attack_attack, 0, $attacker['mana'], $starter, 'bow');
            lostitem($attack_durwep, $arrAtequip[1][6], YOU_WEAPON, $attacker['id'], $arrAtequip[1][0], $starter, HAS_BEEN1);
            lostitem($attack_durwep, $arrAtequip[6][6], YOU_QUIVER, $attacker['id'], $arrAtequip[6][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[0][0]) 
        {
            gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'weapon');
            lostitem($def_durwep, $arrDeequip[0][6], YOU_WEAPON, $defender['id'], $arrDeequip[0][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[1][0]) 
        {
            gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'bow');
            lostitem($def_durwep, $arrDeequip[1][6], YOU_WEAPON, $defender['id'], $arrDeequip[1][0], $starter, HAS_BEEN1);
            lostitem($def_durwep, $arrDeequip[6][6], YOU_QUIVER, $defender['id'], $arrDeequip[6][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[3][0]) 
        {
            lostitem($def_durarm[0], $arrDeequip[3][6], YOU_ARMOR, $defender['id'], $arrDeequip[3][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[2][0]) 
        {
            lostitem($def_durarm[1], $arrDeequip[2][6], YOU_HELMET, $defender['id'], $arrDeequip[2][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[4][0]) 
        {
            lostitem($def_durarm[2], $arrDeequip[4][6], YOU_LEGS, $defender['id'], $arrDeequip[4][0], $starter, HAS_BEEN2);
        }
        if ($arrDeequip[5][0]) 
        {
            lostitem($def_durarm[3], $arrDeequip[5][6], YOU_SHIELD, $defender['id'], $arrDeequip[5][0], $starter, HAS_BEEN1);
        }
        if ($arrAtequip[3][0]) 
        {
            lostitem($attack_durarm[0], $arrAtequip[3][6], YOU_ARMOR, $attacker['id'], $arrAtequip[3][0], $starter, HAS_BEEN1);
        }
        if ($arrAtequip[2][0]) 
        {
            lostitem($attack_durarm[1], $arrAtequip[2][6], YOU_HELMET, $attacker['id'], $arrAtequip[2][0], $starter, HAS_BEEN1);
        }
        if ($arrAtequip[4][0]) 
        {
            lostitem($attack_durarm[2], $arrAtequip[4][6], YOU_LEGS, $attacker['id'], $arrAtequip[4][0], $starter, HAS_BEEN2);
        }
        if ($arrAtequip[5][0]) 
        {
            lostitem($attack_durarm[3], $arrAtequip[5][6], YOU_SHIELD, $attacker['id'], $arrAtequip[5][0], $starter, HAS_BEEN1);
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
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$attacker['id'].",'".$attacktext." ".L_BATTLE2."  <b><a href=view.php?view=".$defender['id'].">".$defender['user']."</a></b> ".L_ID.'<b>'.$defender['id']."</b>.', ".$strDate.")");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$defender['id'].",'".$defendtext." ".L_BATTLE2." <b><a href=view.php?view=".$attacker['id'].">".$attacker['user']."</a></b> ".L_ID.'<b>'.$attacker['id']."</b>.', ".$strDate.")");
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
        $creditgain = ($defender['credits'] / 10);
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
            lostitem($attack_durwep, $arrAtequip[0][6], YOU_WEAPON, $attacker['id'], $arrAtequip[0][0], $starter, HAS_BEEN1);
        }
        if ($arrAtequip[1][0]) 
        {
            gainability($attacker['id'], $attacker['user'], 0, $attack_attack, 0, $attacker['mana'], $starter, 'bow');
            lostitem($attack_durwep, $arrAtequip[1][6], YOU_WEAPON, $attacker['id'], $arrAtequip[1][0], $starter, HAS_BEEN1);
            lostitem($attack_durwep, $arrAtequip[6][6], YOU_QUIVER, $attacker['id'], $arrAtequip[6][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[0][0]) 
        {
            gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'weapon');
            lostitem($def_durwep, $arrDeequip[0][6], YOU_WEAPON, $defender['id'], $arrDeequip[0][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[1][0]) 
        {
            gainability($defender['id'], $defender['user'], 0, $def_attack, 0, $defender['mana'], $starter, 'bow');
            lostitem($def_durwep, $arrDeequip[1][6], YOU_WEAPON, $defender['id'], $arrDeequip[1][0], $starter, HAS_BEEN1);
            lostitem($def_durwep, $arrDeequip[6][6], YOU_QUIVER, $defender['id'], $arrDeequip[6][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[3][0]) 
        {
            lostitem($def_durarm[0], $arrDeequip[3][6], YOU_ARMOR, $defender['id'], $arrDeequip[3][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[2][0]) 
        {
            lostitem($def_durarm[1], $arrDeequip[2][6], YOU_HELMET, $defender['id'], $arrDeequip[2][0], $starter, HAS_BEEN1);
        }
        if ($arrDeequip[4][0]) 
        {
            lostitem($def_durarm[2], $arrDeequip[4][6], YOU_LEGS, $defender['id'], $arrDeequip[4][0], $starter, HAS_BEEN2);
        }
        if ($arrDeequip[5][0]) 
        {
            lostitem($def_durarm[3], $arrDeequip[5][6], YOU_SHIELD, $defender['id'], $arrDeequip[5][0], $starter, HAS_BEEN1);
        }
        if ($arrAtequip[3][0]) 
        {
            lostitem($attack_durarm[0], $arrAtequip[3][6], YOU_ARMOR, $attacker['id'], $arrAtequip[3][0], $starter, HAS_BEEN1);
        }
        if ($arrAtequip[2][0]) 
        {
            lostitem($attack_durarm[1], $arrAtequip[2][6], YOU_HELMET, $attacker['id'], $arrAtequip[2][0], $starter, HAS_BEEN1);
        }
        if ($arrAtequip[4][0]) 
        {
            lostitem($attack_durarm[2], $arrAtequip[4][6], YOU_LEGS, $attacker['id'], $arrAtequip[4][0], $starter, HAS_BEEN2);
        }
        if ($arrAtequip[5][0]) 
        {
            lostitem($attack_durarm[3], $arrAtequip[5][6], YOU_SHIELD, $attacker['id'], $arrAtequip[5][0], $starter, HAS_BEEN1);
        }
        $db -> Execute("UPDATE `players` SET `hp`=".$attacker['hp'].", `credits`=`credits`+".$creditgain.", `wins`=`wins`+1, `lastkilled`='".'<a href="view.php?view='.$defender['id'].'">'.$defender['user']."</a>', `bless`='', `blessval`=0 WHERE `id`=".$attacker['id']);
        $db -> Execute("UPDATE `players` SET `hp`=0, `credits`=`credits`-".$creditgain.", `losses`=`losses`+1, `lastkilledby`='".'<a href="view.php?view='.$attacker['id'].'">'.$attacker['user']."</a>', `bless`='', `blessval`=0, `antidote`='' WHERE `id`=".$defender['id']);
        checkexp($attacker['exp'],$expgain,$attacker['level'],$attacker['race'],$attacker['user'],$attacker['id'],$defender['id'],$defender['user'],$attacker['id'],'',0);
        if ($attacker['id'] == $starter) 
        {
            $attacktext = YOU_ATTACK_AND;
            $startuser = $attacker['user'];
            $secuser = $defender['user'];
            $defender['agility'] = $enemy -> agility;
            $defender['strength'] = $enemy -> strength;
            $defender['inteli'] = $enemy -> inteli;
            $defender['cond'] = $enemy -> cond;
            $defender['speed'] = $enemy -> speed;
            $defender['wisdom'] = $enemy -> wisdom;
        } 
            else 
        {
            $attacktext = YOU_ATTACKED_AND;
            $startuser = $defender['user'];
            $secuser = $attacker['user'];
            $defender['agility'] = $player -> agility;
            $defender['strength'] = $player -> strength;
            $defender['inteli'] = $player -> inteli;
            $defender['cond'] = $player -> cond;
            $defender['speed'] = $player -> speed;
            $defender['wisdom'] = $player -> wisdom;
        }
        loststat($defender['id'], $defender['strength'], $defender['agility'], $defender['inteli'], $defender['cond'], $defender['speed'], $defender['wisdom'], $attacker['id'], $attacker['user'], $starter);
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
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$attacker['id'].",'".$attacktext." ".YOU_DEFEAT." <b><a href=view.php?view=".$defender['id'].">".$defender['user']."</a></b>".L_ID.'<b>'.$defender['id']."</b>. ".YOU_REWARD." <b>$expgain</b> ".EXPERIENCE." <b>$creditgain</b> ".GOLD_COINS.".', ".$strDate.")");
        require_once("includes/foot.php");
        exit;
    }
    attack1($defender, $attacker, $arrDeequip, $arrAtequip, $def_bspell, $attack_bspell, $def_dspell, $attack_dspell, $def_stam, $attack_stam, $def_miss, $attack_miss, $def_attack, $attack_attack, $def_magic, $attack_magic, $def_durwep, $attack_durwep, $def_durarm, $attack_durarm, $starter, $strMessage);
}
?>
