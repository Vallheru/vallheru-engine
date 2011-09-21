<?php
/**
 *   File functions:
 *   Blacksmith - making items - weapons, armors, shields, helmets, plate legs, arrowsheads
 *
 *   @name                 : kowal.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 21.09.2011
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

$title="Kuźnia";
require_once("includes/head.php");
require_once("includes/checkexp.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/kowal.php");

if ($player -> location != 'Altara') 
{
    error (ERROR);
}

/**
 * Function create items
 */
function createitem()
{
    global $db;
    global $player;
    global $arrItem;
    global $intAbility;
    global $intItems;
    global $intGainexp;
    global $intChance;
    global $intExp;
    global $arrMaxbonus;
    global $intKey;

    $arrResult = array();
    $intRoll = rand(1,100);
    if ($intRoll <= $intChance) 
    {
        $strName = $arrItem['name'];
        $intPower = $arrItem['power'];
        $intAgi = $arrItem['zr'];
        $intDur = $arrItem['wt'];
        $intRoll2 = rand(1,100);
        if ($player -> clas == 'Rzemieślnik') 
        {
            $intBonus = ($player -> smith / 100);
            if ($player -> race == 'Gnom')
            {
                $intBonus = $intBonus + ($player -> smith / 100);
            }
            $intRoll2 = floor($intRoll2 - $intBonus);
        }
        $blnSpecial = false;
        if ($intRoll2 < 21)
        {
            $intRoll3 = rand(1, 101);
            $intItembonus = rand(1, ceil($player -> smith));
            if ($arrItem['type'] == 'A' || $arrItem['type'] == 'L')
            {
                if ($intRoll3 < 34)
                {
                    if ($arrItem['type'] == 'A')
                    {
                        $strName = DRAGON2.$arrItem['name'];
                        $intItembonus = $intItembonus * 2;
                    }
		    else
                    {
                        $strName = DRAGON3.$arrItem['name'];
                    }
                    $intPowerbonus = $intItembonus + ($player -> strength / 50);
                    $intMaxbonus = $arrMaxbonus[$intKey] * $arrItem['power'];
                    if ($intPowerbonus > $intMaxbonus)
                    {
                        $intPowerbonus = $intMaxbonus;
                    }
                    $intPower = $arrItem['power'] + $intPowerbonus;
                    $blnSpecial = true;
                }
                if (($intRoll3 > 34 && $intRoll3 < 68) && $player -> clas == 'Rzemieślnik')
                {
                    if ($arrItem['type'] == 'A') 
                    {
                        $strName = DWARFS2.$arrItem['name'];
                    }
                        else 
                    {
                        $strName = DWARFS3.$arrItem['name'];
                    }
                    $intDurbonus = $intItembonus + ($player -> inteli / 50);
                    $intMaxbonus = $arrItem['wt'] * 10;
                    if ($intDurbonus > $intMaxbonus)
                    {
                        $intDurbonus = $intMaxbonus;
                    }
                    $intDur = $arrItem['wt'] + $intDurbonus;
                    $blnSpecial = true;
                }
                if ($intRoll3 > 68 && $player -> clas == 'Rzemieślnik')
                {
                    if ($arrItem['type'] == 'A') 
                    {
                        $strName = ELFS2.$arrItem['name'];
                    }
                        else 
                    {
                        $strName = ELFS3.$arrItem['name'];
                        $intItembonus = ceil($intItembonus / 2);
                    }
                    $intAbibonus = $intItembonus + ($player -> agility / 50);
                    $intMaxbonus = $arrMaxbonus[$intKey] * $arrItem['zr'];
                    if ($intAbibonus > $intMaxbonus)
                    {
                        $intAbibonus = $intMaxbonus;
                    }
                    $intAgi = $arrItem['zr'] - $intAbibonus;
                    $blnSpecial = true;
                }
                if ($intRoll3 == 34 && $player -> clas == 'Rzemieślnik')
                {
                    if ($arrItem['type'] == 'A')
                    {
                        $strName = DRAGON2.$arrItem['name'];
                        $intItembonus = $intItembonus * 2;
                        $intPowerbonus = ($intItembonus * 2) + ($player -> strength / 50);
                    }
                        else
                    {
                        $strName = DRAGON3.$arrItem['name'];
                        $intPowerbonus = $intItembonus + ($player -> strength / 50);
                    }
                    $intMaxbonus = $arrMaxbonus[$intKey] * $arrItem['power'];
                    if ($intPowerbonus > $intMaxbonus)
                    {
                        $intPowerbonus = $intMaxbonus;
                    }
                    $intPower = $arrItem['power'] + $intPowerbonus;
                    $intDurbonus = $intItembonus + ($player -> inteli / 50);
                    $intMaxbonus = $arrItem['wt'] * 10;
                    if ($intDurbonus > $intMaxbonus)
                    {
                        $intDurbonus = $intMaxbonus;
                    }
                    $intDur = $arrItem['wt'] + $intDurbonus;
                    $blnSpecial = true;
                }
                if ($intRoll3 == 68 && $player -> clas == 'Rzemieślnik')
                {
                    if ($arrItem['type'] == 'A') 
                    {
                        $strName = ELFS2.$arrItem['name'];
                        $intAbibonus = $intItembonus + ($player -> agility / 50);
                    }
                    if ($arrItem['type'] == 'L') 
                    {
                        $strName = ELFS3.$arrItem['name'];
                        $intAbibonus = ceil($intItembonus / 2) + ($player -> agility / 50);
                    }
                    $intMaxbonus = $arrMaxbonus[$intKey] * $arrItem['zr'];
                    if ($intAbibonus > $intMaxbonus)
                    {
                        $intAbibonus = $intMaxbonus;
                    }
                    $intAgi = $arrItem['zr'] - $intAbibonus;
                    $intDurbonus = $intItembonus + ($player -> inteli / 50);
                    $intMaxbonus = $arrItem['wt'] * 10;
                    if ($intDurbonus > $intMaxbonus)
                    {
                        $intDurbonus = $intMaxbonus;
                    }
                    $intDur = $arrItem['wt'] + $intDurbonus;
                    $blnSpecial = true;
                }
            }
                else
            {
                if ($intRoll3 < 51)
                {
                    if ($arrItem['type'] == 'W' || $arrItem['type'] == 'H') 
                    {
                        $strName = DRAGON1.$arrItem['name'];
                    }
                    if ($arrItem['type'] == 'S') 
                    {
                        $strName = DRAGON2.$arrItem['name'];
                    }
                    $intPowerbonus = $intItembonus + ($player -> strength / 50);
                    $intMaxbonus = $arrMaxbonus[$intKey] * $arrItem['power'];
                    if ($intPowerbonus > $intMaxbonus)
                    {
                        $intPowerbonus = $intMaxbonus;
                    }
                    $intPower = $arrItem['power'] + $intPowerbonus;
                    $blnSpecial = true;
                }
                if ($intRoll3 == 51 && $player -> clas == 'Rzemieślnik')
                {
                    if ($arrItem['type'] == 'W' || $arrItem['type'] == 'H') 
                    {
                        $strName = DRAGON1.$arrItem['name'];
                    }
                    if ($arrItem['type'] == 'S') 
                    {
                        $strName = DRAGON2.$arrItem['name'];
                    }
                    $intPowerbonus = $intItembonus + ($player -> strength / 50);
                    $intMaxbonus = $arrMaxbonus[$intKey] * $arrItem['power'];
                    if ($intPowerbonus > $intMaxbonus)
                    {
                        $intPowerbonus = $intMaxbonus;
                    }
                    $intPower = $arrItem['power'] + $intPowerbonus;
                    $intDurbonus = $intItembonus + ($player -> inteli / 50);
                    $intMaxbonus = $arrItem['wt'] * 10;
                    if ($intDurbonus > $intMaxbonus)
                    {
                        $intDurbonus = $intMaxbonus;
                    }
                    $intDur = $arrItem['wt'] + $intDurbonus;
                    $blnSpecial = true;
                }
                if ($intRoll3 > 51 && $player -> clas == 'Rzemieślnik')
                {
                    if ($arrItem['type'] == 'H' || $arrItem['type'] == 'W') 
                    {
                        $strName = DWARFS1.$arrItem['name'];
                    }
                    if ($arrItem['type'] == 'S') 
                    {
                        $strName = DWARFS2.$arrItem['name'];
                    }
                    $intDurbonus = $intItembonus + ($player -> inteli / 50);
                    $intMaxbonus = $arrItem['wt'] * 10;
                    if ($intDurbonus > $intMaxbonus)
                    {
                        $intDurbonus = $intMaxbonus;
                    }
                    $intDur = $arrItem['wt'] + $intDurbonus;
                    $blnSpecial = true;
                }
            }
            if ($blnSpecial)
            {
                $intGainexp = $intGainexp + (($arrItem['level'] * (100 + $player -> smith / 10)) * $intExp);
            }
        }
	elseif ($intRoll2 > 20 || !$blnSpecial)
        {
            $intGainexp = $intGainexp + ($arrItem['level'] * $intExp);
        }
        $intItems ++;
        $intAbility = ($intAbility + ($arrItem['level'] / 100));
        if ($arrItem['type'] == 'A')
        {
            $intAbility = $intAbility + ($arrItem['level'] / 100);
        }
        $arrRepair = array(1, 4, 16, 64, 256);
        if ($arrItem['type'] == 'W' || $arrItem['type'] == 'A')
        {
            $intRepaircost = $arrItem['level'] * $arrRepair[$intKey] * 2;
        }
            else
        {
            $intRepaircost = $arrItem['level'] * $arrRepair[$intKey] * 1;
        }
	$arrResult = array("name" => $strName,
			   "wt" => (int)$intDur,
			   "power" => (int)$intPower,
			   "zr" => (int)$intAgi,
			   "repaircost" => $intRepaircost);
    } 
        else 
    {
        $intAbility = ($intAbility + 0.01);
    }
    return $arrResult;
}

/**
* Buy plans of items
*/
if (isset ($_GET['kowal']) && $_GET['kowal'] == 'plany') 
{
    $smarty -> assign(array("Plansinfo" => PLANS_INFO,
                            "Aplansw" => A_PLANS_W,
                            "Aplansa" => A_PLANS_A,
                            "Aplansh" => A_PLANS_H,
                            "Aplansl" => A_PLANS_L,
                            "Aplanss" => A_PLANS_S));
    /**
     * Show available plans
     */
    if (isset($_GET['dalej'])) 
    {
        $arrType = array('W', 'A', 'H', 'L', 'S');
        if (!in_array($_GET['dalej'], $arrType)) 
        {
            error (ERROR);
        }
	$objOwned = $db->Execute("SELECT `name` FROM `smith` WHERE `owner`=".$player->id." AND `type`='".$_GET['dalej']."'");
	$arrOwned = array();
	while (!$objOwned->EOF)
	  {
	    $arrOwned[] = $objOwned->fields['name'];
	    $objOwned->MoveNext();
	  }
	$objOwned->Close();
        $objPlans = $db -> Execute("SELECT `id`, `name`, `cost`, `level` FROM `smith` WHERE `owner`=0 AND `type`='".$_GET['dalej']."' ORDER BY `level` ASC");
        $arrname = array();
        $arrcost = array();
        $arrlevel = array();
        $arrid = array();
        while (!$objPlans -> EOF) 
	  {
	    if (!in_array($objPlans->fields['name'], $arrOwned))
	      {
		$arrname[] = $objPlans -> fields['name'];
		$arrcost[] = $objPlans -> fields['cost'];
		$arrlevel[] = $objPlans -> fields['level'];
		$arrid[] = $objPlans -> fields['id'];
	      }
            $objPlans -> MoveNext();
	  }
        $objPlans -> Close();
        $smarty -> assign(array("Name" => $arrname, 
                                "Cost" => $arrcost, 
                                "Level" => $arrlevel, 
                                "Id" => $arrid,
                                "Iname" => I_NAME,
                                "Ilevel" => I_LEVEL,
                                "Icost" => I_COST,
                                "Ioption" => I_OPTION,
                                "Abuy" => A_BUY,
                                "Hereis" => HERE_IS));
    }
    /**
     * Buy new plan
     */
    if (isset($_GET['buy'])) 
    {
	checkvalue($_GET['buy']);
        $objPlan = $db -> Execute("SELECT * FROM smith WHERE id=".$_GET['buy']);
        $objTest = $db -> Execute("SELECT id FROM smith WHERE owner=".$player -> id." AND name='".$objPlan -> fields['name']."'");
        if ($objTest -> fields['id']) 
        {
            error (YOU_HAVE);
        }
        $objTest -> Close();
        if (!$objPlan -> fields['id']) 
        {
            error (NO_PLAN);
        }
        if ($objPlan -> fields['owner']) 
        {
            error (NOT_FOR_SALE);
        }
        if ($objPlan -> fields['cost'] > $player -> credits) 
        {
            error (NO_MONEY);
        }
        $db -> Execute("INSERT INTO smith (owner, name, type, cost, amount, level, lang) VALUES(".$player -> id.", '".$objPlan -> fields['name']."', '".$objPlan -> fields['type']."', ".$objPlan -> fields['cost'].", ".$objPlan -> fields['amount'].", ".$objPlan -> fields['level'].", '".$objPlan -> fields['lang']."')");
        $db -> Execute("UPDATE players SET credits=credits-".$objPlan -> fields['cost']." WHERE id=".$player -> id);
        $smarty -> assign(array("Cost" => $objPlan -> fields['cost'], 
                                "Plan" => $objPlan -> fields['name'],
                                "Youpay" => YOU_PAY,
                                "Andbuy" => AND_BUY));
        $objPlan -> Close();
    }
}

/**
* Making items
*/
if (isset ($_GET['kowal']) && $_GET['kowal'] == 'kuznia') 
{
    if (!isset($_GET['rob']) && !isset($_GET['konty'])) 
    {
        $objMaked = $db -> Execute("SELECT * FROM smith_work WHERE owner=".$player -> id);
        $smarty -> assign(array("Maked" => $objMaked -> fields['id'],
                                "Smithinfo" => SMITH_INFO,
                                "Amakew" => A_MAKE_W,
                                "Amakea" => A_MAKE_A,
                                "Amakeh" => A_MAKE_H,
                                "Amakel" => A_MAKE_L,
                                "Amakes" => A_MAKE_S,
                                "Amaker" => A_MAKE_R,
                                "Info" => INFO,
                                "Iname" => I_NAME,
                                "Ilevel" => I_LEVEL,
                                "Iamount" => I_AMOUNT));
        if (!$objMaked -> fields['id']) 
        {
            if (isset($_GET['type'])) 
            {
                $arrType = array('W', 'A', 'H', 'L', 'S');
                if (!in_array($_GET['type'], $arrType)) 
                {
                    error (ERROR);
                }
                $objSmith = $db -> Execute("SELECT id, name, amount, level FROM smith WHERE owner=".$player-> id." AND type='".$_GET['type']."' ORDER BY level ASC");
                $arrname = array();
                $arrid = array();
                $arrlevel = array();
                $arrAmount = array();
                $i = 0;
                while (!$objSmith -> EOF) 
                {
                    $arrname[$i] = $objSmith -> fields['name'];
                    $arrid[$i] = $objSmith -> fields['id'];
                    $arrlevel[$i] = $objSmith -> fields['level'];
                    $arrAmount[$i] = $objSmith -> fields['amount'];
                    $objSmith -> MoveNext();
                    $i = $i + 1;
                }
                $objSmith -> Close();
                $smarty -> assign(array("Name" => $arrname, 
                                        "Id" => $arrid, 
                                        "Level" => $arrlevel, 
                                        "Amount" => $arrAmount));
            }
        } 
            else 
        {
            $procent = (($objMaked -> fields['u_energy'] / $objMaked -> fields['n_energy']) * 100);
            $procent = round($procent,"0");
            $need = ($objMaked -> fields['n_energy'] - $objMaked -> fields['u_energy']);
            $smarty -> assign(array("Id" => $objMaked -> fields['id'], 
                                    "Name" => $objMaked -> fields['name'], 
                                    "Percent" => $procent, 
                                    "Need" => $need,
                                    "Info3" => INFO3,
                                    "Ipercent" => I_PERCENT,
                                    "Ienergy" => I_ENERGY,
                                    "Iname" => I_NAME));
        }
        $objMaked -> Close();
    }
        else
    {
        $arrEquip = $player -> equipment();
        $arrRings = array(R_AGI, R_STR, R_INT);
        $arrStat = array('agility', 'strength', 'inteli');
        if ($arrEquip[9][2])
        {
            $arrRingtype = explode(" ", $arrEquip[9][1]);
            $intAmount = count($arrRingtype) - 1;
            $intKey = array_search($arrRingtype[$intAmount], $arrRings);
            if ($intKey != NULL)
            {
                $strStat = $arrStat[$intKey];
                $player -> $strStat = $player -> $strStat + $arrEquip[9][2];
            }
        }
        if ($arrEquip[10][2])
        {
            $arrRingtype = explode(" ", $arrEquip[10][1]);
            $intAmount = count($arrRingtype) - 1;
            $intKey = array_search($arrRingtype[$intAmount], $arrRings);
            if ($intKey != NULL)
            {
                $strStat = $arrStat[$intKey];
                $player -> $strStat = $player -> $strStat + $arrEquip[10][2];
            }
        }
    }
    if (isset($_GET['ko'])) 
    {
        if ($player -> hp == 0) 
        {
            error (YOU_DEAD);
        }
	checkvalue($_GET['ko']);
        $objMaked = $db -> Execute("SELECT name FROM smith_work WHERE id=".$_GET['ko']);
        $smarty -> assign(array("Link" => "kowal.php?kowal=kuznia&konty=".$_GET['ko'], 
                                "Name" => $objMaked -> fields['name'],
                                "Assignen" => ASSIGN_EN,
                                "Senergy" => S_ENERGY,
                                "Amake" => A_MAKE));
        $objMaked -> Close();
    }
    if (isset($_GET['dalej'])) 
    {
        if ($player -> hp == 0) 
        {
            error (YOU_DEAD);
        }
	checkvalue($_GET['dalej']);
        $objSmith = $db -> Execute("SELECT name FROM smith WHERE id=".$_GET['dalej']);
        $smarty -> assign(array("Link" => "kowal.php?kowal=kuznia&rob=".$_GET['dalej'], 
                                "Name" => $objSmith -> fields['name'],
                                "Assignen" => ASSIGN_EN,
                                "Senergy" => S_ENERGY,
                                "Amake" => A_MAKE,
                                "Mcopper" => M_COPPER,
                                "Mbronze" => M_BRONZE,
                                "Mbrass" => M_BRASS,
                                "Miron" => M_IRON,
                                "Msteel" => M_STEEL));
        $objSmith -> Close();
    }
    /**
     * Continue making items
     */
    if (isset($_GET['konty'])) 
    {
	checkvalue($_GET['konty']);
	checkvalue($_POST['razy']);
        $objWork = $db -> Execute("SELECT * FROM smith_work WHERE id=".$_GET['konty']);
        $objSmith = $db -> Execute("SELECT name, type, cost, amount, level, twohand FROM smith WHERE owner=".$player -> id." AND name='".$objWork -> fields['name']."'");
        if ($player -> energy < $_POST['razy']) 
        {
            error (NO_ENERGY);
        }
        $intNeed = ($objWork -> fields['n_energy'] - $objWork -> fields['u_energy']);
        if ($_POST['razy'] > $intNeed) 
        {
            error (TOO_MUCH);
        }
        if ($objWork -> fields['owner'] != $player -> id) 
        {
            error (NO_ITEM);
        }

        /**
         * Add bonuses to ability
         */
        require_once('includes/abilitybonus.php');
        $intSmith = abilitybonus('smith');

        $intItems = 0;
        $intGainexp = 0;
        $intAbility = 0;
        $intCost = ceil($objSmith -> fields['cost'] / 20);
        $arrName = array(M_COPPER, M_BRONZE, M_BRASS, M_IRON, M_STEEL);
        $arrMaxbonus = array(6, 10, 14, 17, 20);
        if ($objSmith -> fields['type'] == 'W' || $objSmith -> fields['type'] == 'A')
        {
            $arrDur = array(40, 80, 160, 320, 640);
        }
            else
        {
            $arrDur = array(20, 40, 80, 160, 320);
        }
        if ($objSmith -> fields['type'] == 'A')
        {
            $intPower = $objSmith -> fields['level'] * 3;
            $intAgility = floor($objSmith -> fields['level'] / 2);
            $intExp = 2;
        }
            elseif ($objSmith -> fields['type'] == 'L')
        {
            $intPower = $objSmith -> fields['level'];
            $intAgility = floor($objSmith -> fields['level'] / 5);
            $intExp = 1;
        }
            else
        {
            $intPower = $objSmith -> fields['level'];
            $intAgility = 0;
            $intExp = 1;
        }
        $arrMineral = array('copper', 'bronze', 'brass', 'iron', 'steel');
        $intKey = array_search($objWork -> fields['mineral'], $arrMineral);
        $strName = $objSmith -> fields['name']." ".$arrName[$intKey];
        $arrItem = array('power' => $intPower,
                         'wt' => $arrDur[$intKey],
                         'name' => $strName,
                         'type' => $objSmith -> fields['type'],
                         'level' => $objSmith -> fields['level'],
                         'szyb' => 0,
                         'zr' => $intAgility,
                         'twohand' => $objSmith -> fields['twohand']);
        if ($player -> clas == 'Rzemieślnik')
        {
            $intExp = $intExp * 2;
        }
        $intChance = (50 - $arrMaxbonus[$intKey]) * $intSmith / $objSmith -> fields['level'];
        if ($intChance > 95)
        {
            $intChance = 95;
        }
        if ($_POST['razy'] == $intNeed) 
        {
            $arrMaked = createitem();
            if ($player -> clas == 'Rzemieślnik')
            {
                $intAbility = $intAbility * 2;
            }
            $intGainexp = ceil($intGainexp);
            if ($intItems) 
	      {
		$test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$arrMaked['name']."' AND `wt`=".$arrMaked['wt']." AND `type`='".$arrItem['type']."' AND `status`='U' AND `owner`=".$player->id." AND `power`=".$arrMaked['power']." AND `zr`=".$arrMaked['zr']." AND `szyb`=".$arrItem['szyb']." AND `maxwt`=".$arrMaked['wt']." AND `poison`=0 AND `cost`=".$intCost) or die($db -> ErrorMsg());
		if (!$test->fields['id']) 
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$arrMaked['name']."', ".$arrMaked['power'].", '".$arrItem['type']."', ".$intCost.", ".$arrMaked['zr'].", ".$arrMaked['wt'].", ".$arrItem['level'].", ".$arrMaked['wt'].", 1, 'N', 0,".$arrItem['szyb'].", '".$arrItem['twohand']."', ".$arrMaked['repaircost'].")");
		  } 
		else 
		  {
		    $db->Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$test -> fields['id']);
		  }
		$test -> Close();
		if ($arrMaked['zr'] < 0)
		  {
		    $arrMaked['zr'] = abs($arrMaked['zr']);
		  }
		else
		  {
		    $arrMaked['zr'] = 0 - $arrMaked['zr'];
		  }
		$arrMaked['zr'] = abs($arrMaked['zr']);
                $smarty -> assign ("Message", YOU_MAKE.$arrMaked['name']."(+ ".$arrMaked['power'].") (".$arrMaked['zr']."% zr) (".$arrMaked['wt']."/".$arrMaked['wt'].")".AND_GAIN2.$intGainexp.AND_EXP2.$intAbility.IN_SMITH);
	      } 
	    else 
	      {
                $intAbility = 0.01;
                if ($player -> clas == 'Rzemieślnik')
                {
                    $intAbility = 0.02;
                }
                $intGainexp = 0;
                $smarty -> assign ("Message", YOU_TRY.$strName.BUT_FAIL.$intAbility.IN_SMITH);
	      }
            $db -> Execute("DELETE FROM `smith_work` WHERE `owner`=".$player -> id);
            checkexp($player -> exp, $intGainexp, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'ability', $intAbility);
        } 
            else 
        {
            $uenergia = ($_POST['razy'] + $objWork -> fields['u_energy']);
            if ($objSmith -> fields['type'] == 'A')
            {
                $intEnergy = $objSmith -> fields['level'] * 2;
            }
                else
            {
                $intEnergy = $objSmith -> fields['level'];
            }
            $procent = (($uenergia / $intEnergy) * 100);
            $procent = round($procent, "0");
            $need = $objWork -> fields['n_energy'] - $uenergia;
            $db -> Execute("UPDATE `smith_work` SET `u_energy`=`u_energy`+".$_POST['razy']." WHERE `owner`=".$player -> id);
            $smarty -> assign ("Message", YOU_WORK.$strName.NEXT_EN.$_POST['razy'].NOW_IS.$procent.YOU_NEED2.$need.S_ENERGY);
        }
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$_POST['razy']." WHERE `id`=".$player -> id);
    }
    /**
     * Start making items
     */
    if (isset($_GET['rob'])) 
    {
	checkvalue($_GET['rob']);
        if (!isset($_POST['razy']))
        {
            error(HOW_MANY);
        }
	checkvalue($_POST['razy']);
        $arrMineral = array('copper', 'bronze', 'brass', 'iron', 'steel');
        if (!in_array($_POST['mineral'], $arrMineral))
        {
            error(ERROR);
        }
        $objTest = $db -> Execute("SELECT `id` FROM `smith_work` WHERE `owner`=".$player -> id);
        if ($objTest -> fields['id'])
        {
            error(YOU_MAKE2);
        }
        $objTest -> Close();
        $objSmith = $db -> Execute("SELECT owner, name, type, cost, amount, level, twohand FROM smith WHERE id=".$_GET['rob']);
        $objMineral = $db -> Execute("SELECT ".$_POST['mineral']." FROM minerals WHERE owner=".$player -> id);
        $strMineral = $_POST['mineral'];
        if ($objSmith -> fields['type'] == 'A')
        {
            $intAmount = floor($_POST['razy'] / ($objSmith -> fields['level'] * 2));
            $intEnergy = $objSmith -> fields['level'] * 2;
            $intEnergy2 = $intAmount * ($objSmith -> fields['level'] * 2);
        }
            else
        {
            $intAmount = floor($_POST['razy'] / $objSmith -> fields['level']);
            $intEnergy = $objSmith -> fields['level'];
            $intEnergy2 = $intAmount * $objSmith -> fields['level'];
        }
        if ($intAmount)
        {
            $intAmineral = $intAmount * $objSmith -> fields['amount'];
        }
            else
        {
            $intAmineral = $objSmith -> fields['amount'];
        }
        if ($intAmineral > $objMineral -> fields[$strMineral])
        {
            error (NO_MAT);
        }
        if ($player -> energy < $_POST['razy']) 
        {
            error (NO_ENERGY);
        }
        if ($objSmith -> fields['owner'] != $player -> id) 
        {
            error (NO_PLANS);
        }

        /**
         * Add bonuses to ability
         */
        require_once('includes/abilitybonus.php');
        $intSmith = abilitybonus('smith');

        $intItems = 0;
        $intGainexp = 0;
        $intAbility = 0;
        $intCost = ceil($objSmith -> fields['cost'] / 20);
        $arrName = array(M_COPPER, M_BRONZE, M_BRASS, M_IRON, M_STEEL);
        $arrMaxbonus = array(6, 10, 14, 17, 20);
        if ($objSmith -> fields['type'] == 'W' || $objSmith -> fields['type'] == 'A')
        {
            $arrDur = array(40, 80, 160, 320, 640);
        }
            else
        {
            $arrDur = array(20, 40, 80, 160, 320);
        }
        if ($objSmith -> fields['type'] == 'A')
        {
            $intPower = $objSmith -> fields['level'] * 3;
            $intAgility = floor($objSmith -> fields['level'] / 2);
            $intExp = 2;
        }
            elseif ($objSmith -> fields['type'] == 'L')
        {
            $intPower = $objSmith -> fields['level'];
            $intAgility = floor($objSmith -> fields['level'] / 5);
            $intExp = 1;
        }
            else
        {
            $intPower = $objSmith -> fields['level'];
            $intAgility = 0;
            $intExp = 1;
        }
        $intKey = array_search($_POST['mineral'], $arrMineral);
        $strName = $objSmith -> fields['name']." ".$arrName[$intKey];
        $arrItem = array('power' => $intPower,
                         'wt' => $arrDur[$intKey],
                         'name' => $strName,
                         'type' => $objSmith -> fields['type'],
                         'level' => $objSmith -> fields['level'],
                         'szyb' => 0,
                         'zr' => $intAgility,
                         'twohand' => $objSmith -> fields['twohand']);
        if ($player -> clas == 'Rzemieślnik')
        {
            $intExp = $intExp * 2;
        }
        $intChance = (50 - $arrMaxbonus[$intKey]) * $intSmith / $objSmith -> fields['level'];
        if ($intChance > 95)
        {
            $intChance = 95;
        }
        if ($intAmount > 0) 
	  {
	    $arrMaked = array();
	    $arrAmount = array();
            for ($i = 1; $i <= $intAmount; $i++) 
            {
                $arrTmp = createitem();
		if (count($arrTmp) > 0)
		  {
		    $intIndex = array_search($arrTmp, $arrMaked);
		    if ($intIndex === FALSE)
		      {
			$arrMaked[] = $arrTmp;
			$arrAmount[] = 1;
		      }
		    else
		      {
			$arrAmount[$intIndex]++;
		      }
		  }
            }
	    for ($i = 0; $i < count($arrMaked); $i++)
	      {
		$test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$arrMaked[$i]['name']."' AND `wt`=".$arrMaked[$i]['wt']." AND `type`='".$arrItem['type']."' AND `status`='U' AND `owner`=".$player->id." AND `power`=".$arrMaked[$i]['power']." AND `zr`=".$arrMaked[$i]['zr']." AND `szyb`=".$arrItem['szyb']." AND `maxwt`=".$arrMaked[$i]['wt']." AND `poison`=0 AND `cost`=".$intCost) or die($db -> ErrorMsg());
		if (!$test->fields['id']) 
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$arrMaked[$i]['name']."', ".$arrMaked[$i]['power'].", '".$arrItem['type']."', ".$intCost.", ".$arrMaked[$i]['zr'].", ".$arrMaked[$i]['wt'].", ".$arrItem['level'].", ".$arrMaked[$i]['wt'].", ".$arrAmount[$i].", 'N', 0,".$arrItem['szyb'].", '".$arrItem['twohand']."', ".$arrMaked[$i]['repaircost'].")");
		  } 
		else 
		  {
		    $db->Execute("UPDATE `equipment` SET `amount`=`amount`+".$arrAmount[$i]." WHERE `id`=".$test -> fields['id']);
		  }
		$test -> Close();
		if ($arrMaked[$i]['zr'] > 0)
		  {
		    $arrMaked[$i]['zr'] = 0 - $arrMaked[$i]['zr'];
		  }
		else
		  {
		    $arrMaked[$i]['zr'] = abs($arrMaked[$i]['zr']);
		  }
	      }
            $intGainexp = ceil($intGainexp);
            if ($player -> clas == 'Rzemieślnik')
            {
                $intAbility = $intAbility * 2;
            }
            $smarty->assign(array("Message" => YOU_MAKE.$objSmith -> fields['name']."</b> <b>".$intItems.AND_GAIN2.$intGainexp.AND_EXP2.$intAbility.IN_SMITH,
				  "Youmade" => "Wykonane przedmioty:",
				  "Iagi" => "zr",
				  "Iamount" => "ilość",
				  "Items" => $arrMaked,
				  "Amount" => $arrAmount,
				  "Amt" => $intItems));
            checkexp($player -> exp, $intGainexp, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'ability', $intAbility); 
	  } 
	else 
	  {
            $procent = (($_POST['razy'] / $intEnergy) * 100);
            $procent = round($procent,"0");
            $need = ($intEnergy - $_POST['razy']);
            $intEnergy2 = $_POST['razy'];
            $db -> Execute("INSERT INTO `smith_work` (`owner`, `name`, `u_energy`, `n_energy`, `mineral`) VALUES(".$player -> id.", '".$objSmith -> fields['name']."', ".$_POST['razy'].", ".$intEnergy.", '".$_POST['mineral']."')");
            $smarty -> assign ("Message", YOU_WORK.$objSmith -> fields['name'].YOU_USE.$_POST['razy'].AND_MAKE.$procent.TO_END.$need.S_ENERGY);
	  }
        $db -> Execute("UPDATE `minerals` SET `".$_POST['mineral']."`=`".$_POST['mineral']."`-".$intAmineral." WHERE `owner`=".$player -> id);
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy2." WHERE `id`=".$player -> id);
    }
}

/**
 * Make astral constructions
 */
if (isset($_GET['kowal']) && $_GET['kowal'] == 'astral')
{
    $objAstral = $db -> Execute("SELECT `name` FROM `astral_plans` WHERE `owner`=".$player -> id." AND `name` LIKE 'P%' AND `location`='V'") or die($db -> ErrorMsg());
    if (!$objAstral -> fields['name'])
    {
        error(NO_PLAN);
    }
    $arrMinerals = array(MIN1, MIN2, MIN3, MIN5, MIN4, MIN6, MIN7, MIN13, MIN8, MIN9, MIN10, MIN11, MIN12, MITHRIL, ENERGY_PTS);
    $arrAmount = array(array(2500, 1250, 250, 6000, 4000, 1500, 1000, 500, 750, 3000, 2000, 1000, 10000, 4000, 50),
                       array(4000, 2000, 300, 8000, 5500, 2500, 1500, 750, 1000, 5000, 3000, 2000, 15000, 5000, 75),
                       array(6500, 2500, 400, 12000, 7000, 4000, 2000, 1000, 1500, 7000, 4000, 3000, 20000, 6000, 100),
                       array(8000, 4000, 500, 17000, 8500, 5500, 2500, 1250, 2000, 9000, 5000, 4000, 25000, 7000, 125),
                       array(10000, 5000, 600, 20000, 10000, 7000, 3000, 1500, 2500, 11000, 6000, 5000, 30000, 8000, 150));
    $arrNames = array(CONST1, CONST2, CONST3, CONST4, CONST5);
    $arrAviable = array();
    $arrAmount2 = array();
    $arrNumber = array();
    $i = 0;
    while (!$objAstral -> EOF)
    {
        $intKey = str_replace("P", "", $objAstral -> fields['name']);
        $arrNumber[$i] = $intKey;
        $intKey = $intKey - 1;
        $arrAviable[$i] = $arrNames[$intKey];
        $arrAmount2[$i] = $arrAmount[$intKey];
        $i ++;
        $objAstral -> MoveNext();
    }
    $objAstral -> Close();

    $smarty -> assign(array("Smithinfo" => SMITH_INFO,
                            "Aviablecom" => $arrAviable,
                            "Mineralsname" => $arrMinerals,
                            "Minamount" => $arrAmount2,
                            "Compnumber" => $arrNumber,
                            "Abuild" => A_BUILD,
                            "Tname" => T_NAME,
                            "Message" => ''));

    /**
     * Start make components
     */
    if (isset($_GET['component']))
    {
	$_GET['component'] = intvalue($_GET['component']);
	if (($_GET['component'] < 1) || ($_GET['component'] > 5))
	  {
	    error(ERROR);
	  }
        $strName = "P".$_GET['component'];
        $objAstral = $db -> Execute("SELECT `amount` FROM `astral_plans` WHERE `owner`=".$player -> id." AND `name`='".$strName."' AND `location`='V'");
        if (!$objAstral -> fields['amount'])
        {
            error(NO_PLAN);
        }
        $objAstral -> Close();
        $intKey = $_GET['component'] - 1;
        $arrSqlminerals = array('adamantium', 'crystal', 'meteor', 'pine', 'hazel', 'yew', 'elm', 'steel', 'ironore', 'copperore', 'tinore', 'zincore', 'coal');
        $objMinerals = $db -> Execute ("SELECT `adamantium`, `crystal`, `meteor`, `pine`, `hazel`, `yew`, `elm`, `steel`, `ironore`, `copperore`, `tinore`, `zincore`, `coal` FROM `minerals` WHERE `owner`=".$player -> id);
        for ($i = 0; $i < 13; $i++)
        {
            $strSqlname = $arrSqlminerals[$i];
            if ($objMinerals -> fields[$strSqlname] < $arrAmount[$intKey][$i])
            {
                error(NO_AMOUNT.$arrMinerals[$i]);
            }
        }
        if ($player -> platinum < $arrAmount[$intKey][13])
        {
            error(NO_MITH);
        }
        if ($player -> energy < $arrAmount[$intKey][14])
        {
            error(NO_ENERGY);
        }
        $arrChance = array(0.25, 0.2, 0.15, 0.1, 0.05);
        $intChance = floor(($player -> smith * $arrChance[$intKey]) + ($player -> fletcher * $arrChance[$intKey]));
        if ($intChance > 95)
        {
            $intChance = 95;
        }
        $intRoll = rand(1, 100);
        if ($intRoll <= $intChance)
        {
            $strCompname = "O".$intKey;
            $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strCompname."' AND `number`=0 AND `location`='V'");
            if (!$objTest -> fields['amount'])
            {
                $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$player -> id.", '".$strCompname."', 0, 1, 'V')");
            }
                else
            {
                $db -> Execute("UPDATE `astral` SET `amount`=`amount`+1 WHERE `owner`=".$player -> id." AND `type`='".$strCompname."' AND `number`=0 AND `location`='V'");
            }
            $objTest -> Close();
            $arrExp1 = array(2000, 3000, 4000, 5000, 7000);
            $arrExp2 = array(3000, 4000, 5000, 6000, 8000);
            $intGainexp = rand($arrExp1[$intKey], $arrExp2[$intKey]);
            $arrAbility = array(1, 1.5, 2, 2.5, 3);
            checkexp($player -> exp, $intGainexp, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'ability', $arrAbility[$intKey]);
            $strMessage = YOU_MAKE.$arrNames[$intKey]."! ".YOU_GAIN11.$intGainexp.YOU_GAIN12.$arrAbility[$intKey].YOU_GAIN13.YOU_USE;
        }
            else
        {
            $intRoll2 = rand(1, 100);
            if ($player -> clas == 'Rzemieślnik')
            {
                if ($intRoll2 < 6)
                {
                    $fltBonus = 0;
                }
                if ($intRoll2 > 5 && $intRoll2 < 21)
                {
                    $fltBonus = 0.2;
                }
                if ($intRoll2 > 20 && $intRoll2 < 51)
                {
                    $fltBonus = 0.25;
                }
                if ($intRoll2 > 50)
                {
                    $fltBonus = 0.33;
                }
            }
                else
            {
                if ($intRoll2 < 6)
                {
                    $fltBonus = 0;
                }
                if ($intRoll2 > 5 && $intRoll2 < 21)
                {
                    $fltBonus = 0.4;
                }
                if ($intRoll2 > 20 && $intRoll2 < 51)
                {
                    $fltBonus = 0.5;
                }
                if ($intRoll2 > 50)
                {
                    $fltBonus = 0.66;
                }
            }
            for ($i = 0; $i < 14; $i ++)
            {
                $arrAmount[$intKey][$i] = ceil($arrAmount[$intKey][$i] * $fltBonus);
            }
            $strMessage = YOU_FAIL.$arrNames[$intKey].YOU_FAIL2.YOU_USE;
        }
        for ($i = 0; $i < 14; $i++)
        {
            $strMessage = $strMessage.$arrMinerals[$i].": ".$arrAmount[$intKey][$i]."<br />";
        }
        $smarty -> assign("Message", $strMessage);
        $db -> Execute("UPDATE `players` SET `platinum`=`platinum`-".$arrAmount[$intKey][13].", `energy`=`energy`-".$arrAmount[$intKey][14]." WHERE `id`=".$player -> id);
        $db -> Execute("UPDATE `minerals` SET `adamantium`=`adamantium`-".$arrAmount[$intKey][0].", `crystal`=`crystal`-".$arrAmount[$intKey][1].", `meteor`=`meteor`-".$arrAmount[$intKey][2].", `pine`=`pine`-".$arrAmount[$intKey][3].", `hazel`=`hazel`-".$arrAmount[$intKey][4].", `yew`=`yew`-".$arrAmount[$intKey][5].", `elm`=`elm`-".$arrAmount[$intKey][6].", `steel`=`steel`-".$arrAmount[$intKey][7].", `ironore`=`ironore`-".$arrAmount[$intKey][8].", `copperore`=`copperore`-".$arrAmount[$intKey][9].", `tinore`=`tinore`-".$arrAmount[$intKey][10].", `zincore`=`zincore`-".$arrAmount[$intKey][11].", `coal`=`coal`-".$arrAmount[$intKey][12]." WHERE `owner`=".$player -> id) or die("Błąd!");
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['kowal'])) 
{
    $_GET['kowal'] = '';
    $smarty -> assign(array("Smithinfo" => SMITH_INFO,
                            "Aplans" => A_PLANS,
                            "Asmith" => A_SMITH,
                            "Aastral" => A_ASTRAL));
    $objAstral = $db -> SelectLimit("SELECT `amount` FROM `astral_plans` WHERE `owner`=".$player -> id." AND `name` LIKE 'P%' AND `location`='V'", 1);
    if ($objAstral -> fields['amount'])
    {
        $smarty -> assign("Astral", 'Y');
    }
        else
    {
        $smarty -> assign("Astral", '');
    }
    $objAstral -> Close();
}
    else
{
    $smarty -> assign("Aback", A_BACK);
}
if (!isset($_GET['dalej'])) 
{
    $_GET['dalej'] = '';
}
if (!isset($_GET['buy'])) 
{
    $_GET['buy'] = '';
}
if (!isset($_GET['rob'])) 
{
    $_GET['rob'] = '';
}
if (!isset($_GET['konty'])) 
{
    $_GET['konty'] = '';
}
if (!isset($_GET['type'])) 
{
    $_GET['type'] = '';
}
if (!isset($_GET['ko'])) 
{
    $_GET['ko'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Smith" => $_GET['kowal'], 
                        "Next" => $_GET['dalej'], 
                        "Buy" => $_GET['buy'], 
                        "Make" => $_GET['rob'], 
                        "Continue" => $_GET['konty'], 
                        "Type" => $_GET['type'], 
                        "Cont" => $_GET['ko']));
$smarty -> display ('kowal.tpl');

require_once("includes/foot.php");
?>
