<?php
/**
 *   File functions:
 *   Lumbermill - making arrows and bows
 *
 *   @name                 : lumbermill.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
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

$title="Tartak";
require_once("includes/head.php");
require_once('includes/checkexp.php');

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/lumbermill.php");

if ($player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
 * Function to create items
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
    global $arrMaxbonus;
    global $intKey;

    $intRoll = rand(1,100);
    $arrResult = array();
    if ($intRoll <= $intChance) 
    {
        $strName = $arrItem['name'];
        $intPower = $arrItem['power'];
        $intSpeed = $arrItem['szyb'];
        $intDur = $arrItem['wt'];
        $intRoll2 = rand(1,100);
        if ($player -> clas == 'Rzemieślnik') 
        {
            $intBonus = ($player -> fletcher / 100);
            if ($player -> race == 'Gnom')
            {
                $intBonus = $intBonus + ($player -> fletcher / 100);
            }
            $intRoll2 = floor($intRoll2 - $intBonus);
        }
        $blnSpecial = false;
        if ($intRoll2 < 21)
        {
            $intRoll3 = rand(1, 101);
            $intItembonus = rand(1, ceil($player -> fletcher));
            if ($intRoll3 < 34 && $arrItem['type'] == 'R')
            {
                $intPowerbonus = $intItembonus + ($player -> strength / 50);
                $intMaxbonus = $arrItem['level'] * 10;
                if ($intPowerbonus > $intMaxbonus)
                {
                    $intPowerbonus = $intMaxbonus;
                }
                $intPower = $arrItem['power'] + $intPowerbonus;
                $strName = DRAGON2.$arrItem['name'];
                $blnSpecial = true;
            }
            if ($intRoll3 > 33 && $intRoll < 67 && $arrItem['type'] == 'B' && $player -> clas == 'Rzemieślnik')
            {
                $intAbibonus = $intItembonus + ($player -> agility / 50);
                $intMaxbonus = $arrMaxbonus[$intKey] * $arrItem['szyb'];
                if ($intAbibonus > $intMaxbonus)
                {
                    $intAbibonus = $intMaxbonus;
                }
                $intSpeed = $arrItem['szyb'] + $intAbibonus;
                $strName = ELFS1.$arrItem['name'];
                $blnSpecial = true;
            }
            if ($intRoll3 > 66 && $player -> clas == 'Rzemieślnik')
            {
                $intDurbonus = $intItembonus + ($player -> inteli / 50);
                if ($arrItem['type'] == 'B')
                {
                    $strName = DWARFS1.$arrItem['name'];
                    $intMaxbonus = $arrItem['wt'] * 10;
                    if ($intDurbonus > $intMaxbonus)
                    {
                        $intDurbonus = $intMaxbonus;
                    }
                }
                    else
                {
                    $intDurbonus = rand(1, ceil($player -> fletcher / 10));
                    if ($intDurbonus > 100)
                    {
                        $intDurbonus = 100;
                    }
                }
                $intDur = $arrItem['wt'] + $intDurbonus;
                $blnSpecial = true;
            }
            if ($blnSpecial)
            {
                $intGainexp = $intGainexp + ($arrItem['level'] * (100 + $player -> fletcher / 10));
            }
        }
            elseif ($intRoll2 > 20 || !$blnSpecial)
        {
            $intGainexp = $intGainexp + $arrItem['level'];
        }
        $intItems ++;
        $intAbility = ($intAbility + ($arrItem['level'] / 100));
        if ($arrItem['type'] == 'B') 
        {
            $arrRepair = array(1, 4, 16, 64, 256);
            $intRepaircost = $arrItem['level'] * $arrRepair[$intKey] * 2;
        } 
            else 
        {
	  $intRepaircost = 0;
        }
	$arrResult = array("name" => $strName,
			   "wt" => (int)$intDur,
			   "power" => (int)$intPower,
			   "speed" => (int)$intSpeed,
			   "repaircost" => $intRepaircost);
    }
        else
    {
        $intAbility = $intAbility + 0.01;
    }
    return $arrResult;
}

$objLumberjack = $db -> Execute("SELECT level FROM lumberjack WHERE owner=".$player -> id);
if (!$objLumberjack -> fields['level'])
{
    $intLevel = 0;
}
    else
{
    $intLevel = $objLumberjack -> fields['level'];
}
$objLumberjack -> Close();

/**
* Assign variable to template
*/
$smarty -> assign("Maked", '');

/**
 * Lumberjack licenses
 */
if (isset($_GET['mill']) && $_GET['mill'] == 'licenses')
{
    if ($intLevel > 3)
    {
        error(NO_LICENSES);
    }
    if (!isset($_GET['step']))
    {
        $_GET['step'] = '';
        $arrLicenses = array(LICENSE1, LICENSE2, LICENSE3, LICENSE4);
        $smarty -> assign("Alicense", $arrLicenses[$intLevel]);
    }
    /**
     * Buy licenses
     */
    if (isset($_GET['step']) && $_GET['step'] == 'buy')
    {
        $arrGold = array(1000, 2000, 10000, 50000);
        $arrMithril = array(0, 10, 50, 250);
        if ($player -> credits < $arrGold[$intLevel])
        {
            error(NO_MONEY);
        }
        if ($player -> platinum < $arrMithril[$intLevel])
        {
            error(NO_MITH);
        }
        if (!$intLevel)
        {
            $db -> Execute("INSERT INTO lumberjack (owner, level) VALUES(".$player -> id.", 1)");
        }
            else
        {
            $db -> Execute("UPDATE lumberjack SET level=level+1 WHERE owner=".$player -> id);
        }
        $db -> Execute("UPDATE players SET credits=credits-".$arrGold[$intLevel].", platinum=platinum-".$arrMithril[$intLevel]." WHERE id=".$player -> id);
        $arrLicenses = array(LICENSE1, LICENSE2, LICENSE3, LICENSE4);
        $smarty -> assign("Message", YOU_BUY.$arrLicenses[$intLevel]);
    }
    $smarty -> assign("Step", $_GET['step']);
}

/**
* Buy plans of items
*/
if (isset ($_GET['mill']) && $_GET['mill'] == 'plany') 
{
    $objOwned = $db->Execute("SELECT `name` FROM `mill` WHERE `owner`=".$player->id);
    $arrOwned = array();
    while (!$objOwned->EOF)
      {
	$arrOwned[] = $objOwned->fields['name'];
	$objOwned->MoveNext();
      }
    $objOwned->Close();
    $objPlans = $db -> Execute("SELECT `id`, `name`, `level`, `cost` FROM `mill` WHERE `owner`=0 ORDER BY `level` ASC");
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
                            "Planid" => $arrid,
                            "Plansinfo" => PLANS_INFO,
                            "Iname" => I_NAME,
                            "Icost" => I_COST,
                            "Ilevel" => I_LEVEL,
                            "Ioption" => I_OPTION,
                            "Hereis" => HERE_IS,
                            "Abuy" => A_BUY));
    if (isset($_GET['buy'])) 
    {
	checkvalue($_GET['buy']);
        $objPlan = $db -> Execute("SELECT * FROM mill WHERE id=".$_GET['buy']);
        if (in_array($objPlan->fields['name'], $arrOwned)) 
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
        $db -> Execute("INSERT INTO mill (owner, name, type, cost, amount, level, lang, twohand) VALUES(".$player -> id.", '".$objPlan -> fields['name']."', '".$objPlan -> fields['type']."', ".$objPlan -> fields['cost'].", ".$objPlan -> fields['amount'].", ".$objPlan -> fields['level'].", '".$player -> lang."', '".$objPlan -> fields['twohand']."')") or error("Nie mogÄ dodaÄ planu.");
        $db -> Execute("UPDATE players SET credits=credits-".$objPlan -> fields['cost']." WHERE id=".$player -> id);
        $smarty -> assign(array("Cost1" => $objPlan -> fields['cost'], 
                                "Name1" => $objPlan -> fields['name'],
                                "Youpay" => YOU_PAY,
                                "Andbuy" => AND_BUY));
        $objPlan -> Close();
    }
}

/**
* Make items
*/
if (isset ($_GET['mill']) && $_GET['mill'] == 'mill') 
{
    if (!isset($_GET['rob']) && !isset($_GET['konty'])) 
    {
        $smarty -> assign(array("Millinfo" => MILL_INFO,
                                "Info" => INFO,
                                "Iname" => I_NAME,
                                "Ilevel" => I_LEVEL,
                                "Ilumber" => I_LUMBER));
        $objMaked = $db -> Execute("SELECT * FROM mill_work WHERE owner=".$player -> id);
        if (!$objMaked -> fields['id']) 
        {
            $objLumber = $db -> Execute("SELECT * FROM mill WHERE owner=".$player -> id." ORDER BY level ASC");
            $arrid = array();
            $arrname = array();
            $arrlevel = array();
            $arrlumber = array();
            $i = 0;
            while (!$objLumber -> EOF) 
            {
                $arrid[$i] = $objLumber -> fields['id'];
                $arrname[$i] = $objLumber -> fields['name'];
                $arrlevel[$i] = $objLumber -> fields['level'];
                $arrlumber[$i] = $objLumber -> fields['amount'];
                $objLumber -> MoveNext();
                $i = $i + 1;
            }
            $objLumber -> Close();
            $smarty -> assign(array("Name" => $arrname, 
                                    "Planid" => $arrid, 
                                    "Level" => $arrlevel, 
                                    "Lumber" => $arrlumber));
        } 
            else 
        {
            $procent = (($objMaked -> fields['u_energy'] / $objMaked -> fields['n_energy']) * 100);
            $procent = round($procent,"0");
            $need = ($objMaked -> fields['n_energy'] - $objMaked -> fields['u_energy']);
            $smarty -> assign(array("Maked" => 1, 
                                    "Planid" => $objMaked -> fields['id'], 
                                    "Name" => $objMaked -> fields['name'], 
                                    "Percent" => $procent, 
                                    "Need" => $need,
                                    "Info2" => INFO2,
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
        $objMaked = $db -> Execute("SELECT name FROM mill_work WHERE id=".$_GET['ko']);
        $smarty -> assign(array("Id" => $_GET['ko'], 
                                "Name" => $objMaked -> fields['name'],
                                "Assignen" => ASSIGN_EN,
                                "Menergy" => M_ENERGY,
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
        $objLumber = $db -> Execute("SELECT name, type FROM mill WHERE id=".$_GET['dalej']);
        $smarty -> assign(array("Id" => $_GET['dalej'], 
                                "Name" => $objLumber -> fields['name'],
                                "Type" => $objLumber -> fields['type'],
                                "Lhazel" => L_HAZEL,
                                "Lyew" => L_YEW,
                                "Lelm" => L_ELM,
                                "Lharder" => L_HARDER,
                                "Lcomposite" => L_COMPOSITE,
                                "Assignen" => ASSIGN_EN,
                                "Menergy" => M_ENERGY,
                                "Amake" => A_MAKE));
        $objLumber -> Close();
    }
    /**
    * Continue making items
    */
    if (isset($_GET['konty'])) 
    {
	checkvalue($_GET['konty']);
        $objWork = $db -> Execute("SELECT * FROM mill_work WHERE id=".$_GET['konty']);
        $objLumber = $db -> Execute("SELECT name, type, cost, amount, level, twohand FROM mill WHERE owner=".$player -> id." AND name='".$objWork -> fields['name']."'");
	checkvalue($_POST['razy']);
        if ($player -> energy < $_POST['razy']) 
        {
            error(NO_ENERGY);
        }
        $need = ($objWork -> fields['n_energy'] - $objWork -> fields['u_energy']);
        if ($_POST['razy'] > $need) 
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
        $player -> fletcher = abilitybonus('fletcher');

        /**
         * Count item attributes
         */
        if ($objLumber -> fields['type'] == 'B')
        {
            $intPower = 0;
            $intSpeed = $objLumber -> fields['level'];
            $arrMineral = array('H', 'Y', 'E', 'A', 'C');
            $intKey = array_search($objWork -> fields['mineral'], $arrMineral);
            $arrMaxdur = array(40, 80, 160, 320, 640);
            $intMaxdur = $arrMaxdur[$intKey];
            $arrBowname = array(L_HAZEL, L_YEW, L_ELM, L_HARDER, L_COMPOSITE);
            $strName = $objLumber -> fields['name']." ".$arrBowname[$intKey];
            $arrMaxbonus = array(6, 10, 14, 17, 20);
            $intModif = $arrMaxbonus[$intKey];
        }
        else
        {
            $intPower = $objLumber -> fields['level'];
            $intSpeed = 0;
            $intMaxdur = 100;
            $strName = $objLumber -> fields['name'];
            $intModif = 0;
        }
        if ($objLumber -> fields['type'] == 'B')
        {
            $intCost = ceil($objLumber -> fields['cost'] / 20);
        }
        else
        {
            $intCost = $objLumber -> fields['level'] * 5;
        }
        $arrItem = array('power' => $intPower,
                         'wt' => $intMaxdur,
                         'name' => $strName,
                         'type' => $objLumber -> fields['type'],
                         'level' => $objLumber -> fields['level'],
                         'szyb' => $intSpeed,
                         'zr' => 0,
                         'twohand' => $objLumber -> fields['twohand']);
        $intItems = 0;
        $intGainexp = 0;
        $intAbility = 0;
        $intChance = (50 - $intModif) * $player -> fletcher / $objLumber -> fields['level'];
        if ($intChance > 95)
        {
            $intChance = 95;
        }
        if ($_POST['razy'] == $need) 
        {
            $arrMaked = createitem();
            if ($intItems)
            {
                if ($player -> clas == 'Rzemieślnik') 
                {
                    $intGainexp = $intGainexp * 2;
                    $intAbility = $intAbility * 2;
                }
                $intGainexp = ceil($intGainexp);
		if ($arrItem['type'] == 'B')
		  {
		    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$arrMaked['name']."' AND `wt`=".$arrMaked['wt']." AND `type`='B' AND `status`='U' AND `owner`=".$player->id." AND `power`=".$arrMaked['power']." AND `zr`=0 AND `szyb`=".$arrMaked['speed']." AND `maxwt`=".$arrMaked['wt']." AND `poison`=0 AND `cost`=".$intCost);
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$arrMaked['name']."', ".$arrMaked['power'].", 'B', ".$intCost.", 0, ".$arrMaked['wt'].", ".$arrItem['level'].", ".$arrMaked['wt'].", 1, 'N', 0, ".$arrMaked['speed'].",'Y', ".$arrMaked['repaircost'].")");
		      } 
		    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$test -> fields['id']);
		      }
		    $test -> Close();
		  }
		else
		  {
		    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$arrMaked['name']."' AND `power`=".$arrMaked['power']." AND `status`='U' AND `cost`=".$intCost." AND `poison`=0");
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `status`, `minlev`, `wt`) VALUES(".$player->id.", '".$arrMaked['name']."', ".$arrMaked['power'].", 'R', ".$intCost.", 'U', ".$arrItem['level'].", ".$arrMaked['wt'].")");
		      } 
		    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$arrMaked['wt']." WHERE `id`=".$test -> fields['id']);
		      }
		    $test -> Close();
		  }
                $smarty -> assign ("Message", YOU_MAKE.$arrMaked['name']."(+ ".$arrMaked['power'].") (".$arrMaked['speed']."% szyb) (".$arrMaked['wt']."/".$arrMaked['wt'].")".AND_GAIN2.$intGainexp.AND_EXP2.$intAbility.IN_MILL);
            } 
                else 
            {
                $intAbility = 0.01;
                $intGainexp = 0;
                $smarty -> assign ("Message", YOU_TRY.$arrItem['name'].BUT_FAIL.$intAbility.IN_MILL);
            }
            $db -> Execute("DELETE FROM mill_work WHERE owner=".$player -> id);
            checkexp($player -> exp, $intGainexp, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'fletcher', $intAbility);
        } 
            else 
        {
            $uenergia = ($_POST['razy'] + $objWork -> fields['u_energy']);
            $procent = (($uenergia / $objWork -> fields['n_energy']) * 100);
            $procent = round($procent,"0");
            $need = $objWork -> fields['n_energy'] - $uenergia;
            $smarty -> assign ("Message", YOU_WORK.$arrItem['name'].NEXT_EN.$_POST['razy'].NOW_IS.$procent.YOU_NEED2.$need.M_ENERGY);
            $db -> Execute("UPDATE mill_work SET u_energy=u_energy+".$_POST['razy']." WHERE owner=".$player -> id);
        }
        $db -> Execute("UPDATE players SET energy=energy-".$_POST['razy'].", bless='', blessval=0 WHERE id=".$player -> id);
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
        $objTest = $db -> Execute("SELECT `id` FROM `mill_work` WHERE `owner`=".$player -> id);
        if ($objTest -> fields['id'])
        {
            error(YOU_MAKE2);
        }
        $objTest -> Close();
        $objLumber = $db -> Execute("SELECT * FROM mill WHERE id=".$_GET['rob']);
        if ($objLumber -> fields['type'] == 'B')
        {
            $arrMineral = array('H', 'Y', 'E', 'A', 'C');
            if (!in_array($_POST['lumber'], $arrMineral))
            {
                error(ERROR);
            }
        }
        $intAmount = floor($_POST['razy'] / $objLumber -> fields['level']);
        if ($intAmount > 1) 
        {
            $intNeedmineral = ($intAmount * $objLumber -> fields['amount']);
        } 
            else 
        {
            $intNeedmineral = ($objLumber -> fields['amount']);
        }
        if ($objLumber -> fields['type'] == 'R')
        {
            $arrNeedmineral = array('pine');
        }
            else
        {
            $intKey = array_search($_POST['lumber'], $arrMineral);
            if ($intKey < 3)
            {
                $arrMinerals = array('hazel', 'yew', 'elm');
                $arrNeedmineral = array($arrMinerals[$intKey]);
            }
                elseif ($intKey == 3)
            {
                $arrNeedmineral = array('hazel', 'elm');
            }
                elseif ($intKey == 4)
            {
                $arrNeedmineral = array('pine', 'hazel', 'yew', 'elm');
            }
        }
        foreach ($arrNeedmineral as $strNeedmineral)
        {
            $objMineral = $db -> Execute("SELECT ".$strNeedmineral." FROM minerals WHERE owner=".$player -> id);
            if ($objMineral -> fields[$strNeedmineral] < $intNeedmineral)
            {
                error(NO_MAT);
            }
            $objMineral -> Close();
        }
        if ($player -> energy < $_POST['razy']) 
        {
            error(NO_ENERGY);
        }
        if ($objLumber -> fields['owner'] != $player -> id) 
        {
            error(NO_PLANS);
        }

        /**
         * Add bonuses to ability
         */
        require_once('includes/abilitybonus.php');
        $player -> fletcher = abilitybonus('fletcher');

        /**
         * Count item attributes
         */
        if ($objLumber -> fields['type'] == 'B')
        {
            $intPower = 0;
            $intSpeed = $objLumber -> fields['level'];
            $arrMaxdur = array(40, 80, 160, 320, 640);
            $intMaxdur = $arrMaxdur[$intKey];
            $arrBowname = array(L_HAZEL, L_YEW, L_ELM, L_HARDER, L_COMPOSITE);
            $strName = $objLumber -> fields['name']." ".$arrBowname[$intKey];
            $arrMaxbonus = array(6, 10, 14, 17, 20);
            $intCost = ceil($objLumber -> fields['cost'] / 20);
            $intModif = $arrMaxbonus[$intKey];
        }
            else
        {
            $intPower = $objLumber -> fields['level'];
            $intSpeed = 0;
            $intMaxdur = 100;
            $strName = $objLumber -> fields['name'];
            $intCost = $objLumber -> fields['level'] * 5;
            $intModif = 0;
        }
        $arrItem = array('power' => $intPower,
                         'wt' => $intMaxdur,
                         'name' => $strName,
                         'type' => $objLumber -> fields['type'],
                         'level' => $objLumber -> fields['level'],
                         'szyb' => $intSpeed,
                         'zr' => 0,
                         'cost' => $intCost,
                         'twohand' => $objLumber -> fields['twohand']);
        $intItems = 0;
        $intGainexp = 0;
        $intAbility = 0;
        $intChance = (50 - $intModif) * $player -> fletcher / $objLumber -> fields['level'];
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
		if ($arrItem['type'] == 'B')
		  {
		    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$arrMaked[$i]['name']."' AND `wt`=".$arrMaked[$i]['wt']." AND `type`='B' AND `status`='U' AND `owner`=".$player->id." AND `power`=".$arrMaked[$i]['power']." AND `zr`=0 AND `szyb`=".$arrMaked[$i]['speed']." AND `maxwt`=".$arrMaked[$i]['wt']." AND `poison`=0 AND `cost`=".$intCost);
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$arrMaked[$i]['name']."', ".$arrMaked[$i]['power'].", 'B', ".$intCost.", 0, ".$arrMaked[$i]['wt'].", ".$arrItem['level'].", ".$arrMaked[$i]['wt'].", ".$arrAmount[$i].", 'N', 0, ".$arrMaked[$i]['speed'].",'Y', ".$arrMaked[$i]['repaircost'].")");
		      } 
		    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$arrAmount[$i]." WHERE `id`=".$test -> fields['id']);
		      }
		    $test -> Close();
		  }
		else
		  {
		    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$arrMaked[$i]['name']."' AND `power`=".$arrMaked[$i]['power']." AND `status`='U' AND `cost`=".$intCost." AND `poison`=0");
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `status`, `minlev`, `wt`) VALUES(".$player->id.", '".$arrMaked[$i]['name']."', ".$arrMaked[$i]['power'].", 'R', ".$intCost.", 'U', ".$arrItem['level'].", ".($arrMaked[$i]['wt'] * $arrAmount[$i]).")");
		      } 
		    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".($arrAmount[$i] * $arrMaked[$i]['wt'])." WHERE `id`=".$test -> fields['id']);
		      }
		    $test -> Close();
		  }
	      }
            if ($player -> clas == 'Rzemieślnik') 
            {
                $intGainexp = $intGainexp * 2;
                $intAbility = $intAbility * 2;
            }
            $intGainexp = ceil($intGainexp);
            $smarty->assign(array("Message" => YOU_MAKE.$arrItem['name']."</b> <b>".$intItems.AND_GAIN2.$intGainexp.AND_EXP2.$intAbility.IN_MILL,
				  "Youmade" => "Wykonane przedmioty:",
				  "Ispeed" => "szyb",
				  "Iamount" => "ilość",
				  "Items" => $arrMaked,
				  "Amount" => $arrAmount,
				  "Amt" => $intItems));
            checkexp($player -> exp, $intGainexp, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id,'fletcher',$intAbility);
        } 
            else 
        {
            $procent = (($_POST['razy'] / $arrItem['level']) * 100);
            $procent = round($procent,"0");
            $need = ($objLumber -> fields['level'] - $_POST['razy']);
            $smarty -> assign ("Message", YOU_WORK.$arrItem['name'].YOU_USE.$_POST['razy'].AND_MAKE.$procent.TO_END.$need.M_ENERGY);
            if ($objLumber -> fields['type'] == 'R') 
            {
                $db -> Execute("INSERT INTO mill_work (owner, name, u_energy, n_energy) VALUES(".$player -> id.", '".$objLumber -> fields['name']."', ".$_POST['razy'].", ".$arrItem['level'].")");
            } 
                else 
            {
                $db -> Execute("INSERT INTO mill_work (owner, name, u_energy, n_energy, mineral) VALUES(".$player -> id.", '".$objLumber -> fields['name']."', ".$_POST['razy'].", ".$arrItem['level'].", '".$_POST['lumber']."')") or error("nie mogÄ dodaÄ przedmiotu");
            }
        }
        foreach ($arrNeedmineral as $strNeedmineral)
        {
            $objMineral = $db -> Execute("UPDATE minerals SET ".$strNeedmineral."=".$strNeedmineral."-".$intNeedmineral." WHERE owner=".$player -> id);
        }
        $db -> Execute("UPDATE players SET energy=energy-".$_POST['razy'].", bless='', blessval=0 WHERE id=".$player -> id);
    }
}

/**
 * Make astral constructions
 */
if (isset($_GET['mill']) && $_GET['mill'] == 'astral')
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

    $smarty -> assign(array("Millinfo" => MILL_INFO,
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
        if (!ereg("^[1-5]*$", $_GET['component']))
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
            checkexp($player -> exp, $intGainexp, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'fletcher', $arrAbility[$intKey]);
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
            for ($i = 0; $i < 13; $i ++)
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
        $db -> Execute("UPDATE `minerals` SET `adamantium`=`adamantium`-".$arrAmount[$intKey][0].", `crystal`=`crystal`-".$arrAmount[$intKey][1].", `meteor`=`meteor`-".$arrAmount[$intKey][2].", `pine`=`pine`-".$arrAmount[$intKey][3].", `hazel`=`hazel`-".$arrAmount[$intKey][4].", `yew`=`yew`-".$arrAmount[$intKey][5].", `elm`=`elm`-".$arrAmount[$intKey][6].", `steel`=`steel`-".$arrAmount[$intKey][7].", `ironore`=`ironore`-".$arrAmount[$intKey][8].", `copperore`=`copperore`-".$arrAmount[$intKey][9].", `tinore`=`tinore`-".$arrAmount[$intKey][10].", `zincore`=`zincore`-".$arrAmount[$intKey][11].", `coal`=`coal`-".$arrAmount[$intKey][12]." WHERE `owner`=".$player -> id);
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['mill'])) 
{
    $_GET['mill'] = '';
    $smarty -> assign(array("Millinfo" => MILL_INFO,
                            "Aplans" => A_PLANS,
                            "Amill" => A_MILL,
                            "Llevel" => $intLevel,
                            "Alicenses" => A_LICENSES,
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
if (!isset($_GET['ko'])) 
{
    $_GET['ko'] = '';
}
if (!isset($_GET['dalej'])) 
{
    $_GET['dalej'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Mill" => $_GET['mill'], 
                        "Buy" => $_GET['buy'], 
                        "Make" => $_GET['rob'], 
                        "Continue" => $_GET['konty'], 
                        "Cont" => $_GET['ko'], 
                        "Next" => $_GET['dalej']));
$smarty -> display ('lumbermill.tpl');

require_once("includes/foot.php");
?>
