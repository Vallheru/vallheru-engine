<?php
/**
 *   File functions:
 *   Blacksmith - making items - weapons, armors, shields, helmets, plate legs, arrowsheads
 *
 *   @name                 : kowal.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
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

$title="Kuźnia";
require_once("includes/head.php");
require_once("includes/checkexp.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/kowal.php");

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
		    if ($intAbibonus == 0)
		      {
			$intAbibonus = 1;
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
		    if ($intAbibonus == 0)
		      {
			$intAbibonus = 1;
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
                    elseif ($arrItem['type'] == 'S') 
                    {
                        $strName = DRAGON2.$arrItem['name'];
                    }
		    else
		      {
			$strName = 'Smocze '.$arrItem['name'];
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
                    elseif ($arrItem['type'] == 'S') 
                    {
                        $strName = DRAGON2.$arrItem['name'];
                    }
		    else
		      {
			$strName = 'Smocze '.$arrItem['name'];
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
                if ($intRoll3 > 51 && $player -> clas == 'Rzemieślnik' && $arrItem['type'] != 'E')
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
	elseif ($arrItem['type'] != 'E')
        {
            $intRepaircost = $arrItem['level'] * $arrRepair[$intKey] * 1;
        }
	else
	  {
	    $intRepaircost = ($arrItem['level'] + 20) * $arrRepair[$intKey];
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
    /**
     * Buy new plan
     */
    if (isset($_GET['buy'])) 
    {
	checkvalue($_GET['buy']);
        $objPlan = $db -> Execute("SELECT * FROM `smith` WHERE `id`=".$_GET['buy']." AND `owner`=0");
	$objTest = $db -> Execute("SELECT `id` FROM `smith` WHERE `owner`=".$player -> id." AND `name`='".$objPlan -> fields['name']."' AND `elitetype`='".$objPlan->fields['elitetype']."'");
        if ($objTest -> fields['id']) 
	  {
	    message('error', YOU_HAVE);
	  }
	elseif (!$objPlan -> fields['id']) 
	  {
            message('error', NO_PLAN);
        }
        elseif ($objPlan -> fields['cost'] > $player -> credits) 
	  {
            message('error', NO_MONEY);
	  }
	elseif ($objPlan->fields['elite'] > 0 && $player->clas != 'Rzemieślnik')
	  {
	    message('error', "Tylko Rzemieślnik może kupować plany elitarnych przedmiotów.");
	  }
	else
	  {
	    $db -> Execute("INSERT INTO `smith` (`owner`, `name`, `type`, `cost`, `amount`, `level`, `twohand`, `elite`, `elitetype`) VALUES(".$player -> id.", '".$objPlan -> fields['name']."', '".$objPlan -> fields['type']."', ".$objPlan -> fields['cost'].", ".$objPlan -> fields['amount'].", ".$objPlan -> fields['level'].", '".$objPlan -> fields['twohand']."', ".$objPlan->fields['elite'].", '".$objPlan->fields['elitetype']."')");
	    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$objPlan -> fields['cost']." WHERE `id`=".$player -> id);
	    message('success', YOU_PAY." <b>".$objPlan->fields['cost']."</b> ".AND_BUY.": <b>".$objPlan->fields['name']."</b>.");
	  }
        $objTest -> Close();
        $objPlan -> Close();
    }

    /**
     * Show available plans
     */
    $objOwned = $db->Execute("SELECT `name`, `elitetype` FROM `smith` WHERE `owner`=".$player->id."");
    $arrOwned = array();
    while (!$objOwned->EOF)
      {
	$arrOwned[$objOwned->fields['name']] = $objOwned->fields['elitetype'];
	$objOwned->MoveNext();
      }
    $objOwned->Close();
    $arrPlans = array(array(), array(), array(), array(), array());
    $arrTypes = array('W', 'A', 'S', 'H', 'L');
    $arrLinks = array('+ Plany broni', '+ Plany zbrój', '+ Plany tarcz', '+ Plany hełmów', '+ Plany nagolenników');
    $objPlans = $db -> Execute("SELECT * FROM `smith` WHERE `owner`=0 ORDER BY `level` ASC");
    $i = 0;
    while (!$objPlans->EOF)
      {
	if ($player->clas != 'Rzemieślnik' && $objPlans->fields['elite'] > 0)
	  {
	    $objPlans->MoveNext();
	    continue;
	  }
	if (!array_key_exists($objPlans->fields['name'], $arrOwned) || (array_key_exists($objPlans->fields['name'], $arrOwned) && $arrOwned[$objPlans->fields['name']] != $objPlans->fields['elitetype']))
	  {
	    $intKey = array_search($objPlans->fields['type'], $arrTypes);
	    if ($objPlans->fields['elite'] > 0)
	      {
		if ($objPlans->fields['elitetype'] == 'S')
		  {
		    $arrPlans[$intKey][$i]['name'] = $objPlans -> fields['name'].' (smoczy)';
		  }
		else
		  {
		    $arrPlans[$intKey][$i]['name'] = $objPlans -> fields['name'].' (elfi)';
		  }
	      }
	    else
	      {
		$arrPlans[$intKey][$i]['name'] = $objPlans -> fields['name'];
	      }
	    $arrPlans[$intKey][$i]['cost'] = $objPlans -> fields['cost'];
	    $arrPlans[$intKey][$i]['level'] = $objPlans -> fields['level'];
	    $arrPlans[$intKey][$i]['id'] = $objPlans -> fields['id'];
	    $i++;
	  }
	$objPlans -> MoveNext();
      }
    $objPlans -> Close();
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
      {
	$strChecked = "";
      }
    else
      {
	$strChecked = "checked=checkded";
      }
    $smarty -> assign(array("Plansinfo" => PLANS_INFO,
			    "Plans" => $arrPlans,
			    "Links" => $arrLinks,
			    "Checked" => $strChecked,
			    "Iname" => 'Nazwa',
			    "Icost" => 'Cena',
			    "Ilevel" => 'Poziom',
			    "Ioption" => 'Opcje',
			    "Abuy" => 'Kup'));
}

/**
 * Make normal and elite items (shared code)
 */
if (isset($_GET['kowal']) && ($_GET['kowal'] == 'kuznia' || $_GET['kowal'] == 'elite'))
  {
    if (!isset($_GET['rob']) && !isset($_GET['konty'])) 
      {
	$arrType = array('W', 'A', 'H', 'L', 'S', 'E');
	$smarty -> assign(array("Amake" => array(A_MAKE_W, A_MAKE_A, A_MAKE_H, A_MAKE_L, A_MAKE_S, 'Wykonuj narzędzia'),
				"Atype" => $arrType,
                                "Info" => INFO,
                                "Iname" => I_NAME,
                                "Ilevel" => I_LEVEL,
                                "Iamount" => I_AMOUNT));
	if (isset($_GET['type'])) 
	  {
	    if (!in_array($_GET['type'], $arrType)) 
	      {
		error (ERROR);
	      }
	  }
      }
    else
      {
	/**
         * Add bonuses to ability
         */
	$player->curstats(array(), TRUE);
	$player->curskills(array('smith'), TRUE, TRUE);
      }
    if (isset($_GET['ko'])) 
    {
        if ($player -> hp == 0) 
	  {
            error (YOU_DEAD);
	  }
	checkvalue($_GET['ko']);
        $objMaked = $db -> Execute("SELECT `name` FROM `smith_work` WHERE `id`=".$_GET['ko']);
        $smarty -> assign(array("Link" => "kowal.php?kowal=".$_GET['kowal']."&konty=".$_GET['ko'], 
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
	$objMinerals = $db->Execute("SELECT `owner`, `copper`, `bronze`, `brass`, `iron`, `steel` FROM `minerals` WHERE `owner`=".$player->id);
	if (!$objMinerals->fields['owner'])
	  {
	    error("Nie posiadasz sztabek aby wykonać jakikolwiek przedmiot.");
	  }
	$arrKeys = array('copper', 'bronze', 'brass', 'iron', 'steel');
	$arrNames = array('z miedzi (', 'z brązu (', 'z mosiądzu (', 'z żelaza (', 'ze stali (');
	$arrOptions = array();
	for ($i = 0; $i < count($arrKeys); $i++)
	  {
	    if ($objMinerals->fields[$arrKeys[$i]] > 0)
	      {
		$arrOptions[$arrKeys[$i]] = $arrNames[$i].$objMinerals->fields[$arrKeys[$i]]." sztabek)";
	      }
	  }
	if (count($arrOptions) == 0)
	  {
	    error("Nie posiadasz sztabek aby wykonać jakikolwiek przedmiot.");
	  }
        $objSmith = $db -> Execute("SELECT `name` FROM `smith` WHERE `id`=".$_GET['dalej']);
        $smarty -> assign(array("Link" => "kowal.php?kowal=".$_GET['kowal']."&rob=".$_GET['dalej'], 
                                "Name" => $objSmith -> fields['name'],
                                "Assignen" => ASSIGN_EN,
                                "Senergy" => S_ENERGY,
                                "Amake" => A_MAKE,
                                "Moptions" => $arrOptions));
        $objSmith -> Close();
      }
    if (isset($_GET['konty'])) 
      {
	checkvalue($_GET['konty']);
	checkvalue($_POST['razy']);
        $objWork = $db -> Execute("SELECT * FROM `smith_work` WHERE `id`=".$_GET['konty']);
        $objSmith = $db -> Execute("SELECT `name`, `type`, `cost`, `amount`, `level`, `twohand` FROM `smith` WHERE `owner`=".$player -> id." AND `name`='".$objWork -> fields['name']."'");
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
      }
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
	$objSmith = $db -> Execute("SELECT * FROM `smith` WHERE `id`=".$_GET['rob']);
        $objMineral = $db -> Execute("SELECT ".$_POST['mineral']." FROM `minerals` WHERE `owner`=".$player -> id);
        $strMineral = $_POST['mineral'];
	if ($player -> energy < $_POST['razy']) 
	  {
            error (NO_ENERGY);
	  }
        if ($objSmith -> fields['owner'] != $player -> id) 
	  {
            error (NO_PLANS);
	  }
      }
  }

/**
 * Function add item to player equipment
 */
function additem($strType, $strName, $intWt, $intPower, $intAgi, $intCost, $intPid, $intLevel, $intRepair, $strTwohand, $intAmount = 1)
{
  global $db;

  $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$strName."' AND `wt`=".$intWt." AND `type`='".$strType."' AND `status`='U' AND `owner`=".$intPid." AND `power`=".$intPower." AND `zr`=".$intAgi." AND `szyb`=0 AND `maxwt`=".$intWt." AND `poison`=0 AND `cost`=".$intCost) or die($db->ErrorMsg());
  if (!$test -> fields['id']) 
    {
      $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$intPid.", '".$strName."', ".$intPower.", '".$strType."', ".$intCost.", ".$intAgi.", ".$intWt.", ".$intLevel.", ".$intWt.", ".$intAmount.", 'N', 0, 0, '".$strTwohand."', ".$intRepair.")");
    } 
  else 
    {
      $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$intAmount." WHERE `id`=".$test -> fields['id']);
    }
  $test -> Close();
}

/**
* Making items
*/
if (isset ($_GET['kowal']) && $_GET['kowal'] == 'kuznia') 
{
    if (!isset($_GET['rob']) && !isset($_GET['konty'])) 
    {
        $objMaked = $db -> Execute("SELECT * FROM `smith_work` WHERE `owner`=".$player->id." AND `elite`=0");
        $smarty -> assign(array("Maked" => $objMaked -> fields['id'],
				"Smithinfo" => SMITH_INFO));
        if (!$objMaked -> fields['id']) 
        {
            if (isset($_GET['type'])) 
            {
                $objSmith = $db -> Execute("SELECT `id`, `name`, `amount`, `level` FROM `smith` WHERE `owner`=".$player-> id." AND `type`='".$_GET['type']."' AND `elite`=0 ORDER BY `level` ASC");
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
    /**
     * Continue making items
     */
    if (isset($_GET['konty'])) 
      {
	if ($objWork->fields['elite'] > 0)
	  {
	    error("Nie możesz wykonywać tutaj elitarnego ekwipunku.");
	  }

        $intItems = 0;
        $intGainexp = 0;
        $intAbility = 0;
        $intCost = ceil($objSmith -> fields['cost'] / 20);
        $arrName = array(M_COPPER, M_BRONZE, M_BRASS, M_IRON, M_STEEL);
	if ($objSmith->fields['type'] != 'E')
	  {
	    $arrMaxbonus = array(6, 10, 14, 17, 20);
	  }
	else
	  {
	    $arrMaxbonus = array(2, 4, 6, 8, 10);
	  }
        if ($objSmith -> fields['type'] == 'W' || $objSmith -> fields['type'] == 'A')
        {
            $arrDur = array(40, 80, 160, 320, 640);
        }
	elseif ($objSmith->fields['type'] != 'E')
        {
            $arrDur = array(20, 40, 80, 160, 320);
        }
	else
	  {
	    $arrDur = array(10, 15, 20, 25, 30);
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
	elseif ($objSmith->fields['type'] != 'E')
	  {
            $intPower = $objSmith -> fields['level'];
            $intAgility = 0;
            $intExp = 1;
	  }
	else
	  {
	    $intPower = 10 + $objSmith->fields['level'];
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
        $intChance = (50 - $arrMaxbonus[$intKey]) * $player->smith / $objSmith -> fields['level'];
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
		additem($arrItem['type'], $arrMaked['name'], $arrMaked['wt'], $arrMaked['power'], $arrMaked['zr'], $intCost, $player->id, $arrItem['level'], $arrMaked['repaircost'], $arrItem['twohand']);
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
        if ($objSmith -> fields['type'] == 'A')
        {
            $intAmount = floor($_POST['razy'] / ($objSmith -> fields['level'] * 2));
            $intEnergy = $objSmith -> fields['level'] * 2;
	    if ($intAmount)
	      {
		$intEnergy2 = $intAmount * ($objSmith -> fields['level'] * 2);
	      }
	    else
	      {
		$intEnergy2 = $intEnergy;
	      }
        }
            else
        {
            $intAmount = floor($_POST['razy'] / $objSmith -> fields['level']);
            $intEnergy = $objSmith -> fields['level'];
	    if ($intAmount)
	      {
		$intEnergy2 = $intAmount * $objSmith -> fields['level'];
	      }
	    else
	      {
		$intEnergy2 = $intEnergy;
	      }
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
	if ($objSmith->fields['elite'] > 0)
	  {
	    error("Nie możesz wykonywać tutaj elitarnego ekwipunku.");
	  }

        $intItems = 0;
        $intGainexp = 0;
        $intAbility = 0;
        $intCost = ceil($objSmith -> fields['cost'] / 20);
        $arrName = array(M_COPPER, M_BRONZE, M_BRASS, M_IRON, M_STEEL);
	if ($objSmith->fields['type'] != 'E')
	  {
	    $arrMaxbonus = array(6, 10, 14, 17, 20);
	  }
	else
	  {
	    $arrMaxbonus = array(2, 4, 6, 8, 10);
	  }
        if ($objSmith -> fields['type'] == 'W' || $objSmith -> fields['type'] == 'A')
        {
            $arrDur = array(40, 80, 160, 320, 640);
        }
	elseif ($objSmith->fields['type'] != 'E')
        {
            $arrDur = array(20, 40, 80, 160, 320);
        }
	else
	  {
	    $arrDur = array(10, 15, 20, 25, 30);
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
	elseif ($objSmith->fields['type'] != 'E')
        {
            $intPower = $objSmith -> fields['level'];
            $intAgility = 0;
            $intExp = 1;
        }
	else
	  {
	    $intPower = 10 + $objSmith->fields['level'];
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
        $intChance = (50 - $arrMaxbonus[$intKey]) * $player->smith / $objSmith -> fields['level'];
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
		additem($arrItem['type'], $arrMaked[$i]['name'], $arrMaked[$i]['wt'], $arrMaked[$i]['power'], $arrMaked[$i]['zr'], $intCost, $player->id, $arrItem['level'], $arrMaked[$i]['repaircost'], $arrItem['twohand'], $arrAmount[$i]);
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
            $smarty -> assign(array("Message" => YOU_WORK.$objSmith -> fields['name'].YOU_USE.$_POST['razy'].AND_MAKE.$procent.TO_END.$need.S_ENERGY,
				    "Amt" => 0));
	  }
        $db -> Execute("UPDATE `minerals` SET `".$_POST['mineral']."`=`".$_POST['mineral']."`-".$intAmineral." WHERE `owner`=".$player -> id);
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy2." WHERE `id`=".$player -> id);
    }
}

/**
* Making elite items
*/
if (isset ($_GET['kowal']) && $_GET['kowal'] == 'elite') 
{
    if ($player->clas != 'Rzemieślnik')
      {
	error('Tylko rzemieślnik może wykonywać elitarny ekwipunek.');
      }
    if (!isset($_GET['rob']) && !isset($_GET['konty'])) 
    {
        $objMaked = $db -> Execute("SELECT * FROM `smith_work` WHERE `owner`=".$player->id." AND `elite`>0");
        $smarty -> assign(array("Maked" => $objMaked -> fields['id'],
				"Smithinfo" => "Tutaj możesz wykonywać przedmioty co do których masz plany. Aby wykonać przedmiot, musisz posiadać również odpowiednią ilość surowców. Każda próba kosztuje ciebie 10 energii na poziom przedmiotu. Nawet za nieudaną próbę dostajesz 0,01 do umiejętności."));
        if (!$objMaked -> fields['id']) 
        {
            if (isset($_GET['type'])) 
            {
                $objSmith = $db -> Execute("SELECT * FROM `smith` WHERE `owner`=".$player-> id." AND `type`='".$_GET['type']."' AND `elite`>0 ORDER BY `level` ASC");
                $arrname = array();
                $arrid = array();
                $arrlevel = array();
                $arrAmount = array();
		$arrLoot = array();
                while (!$objSmith -> EOF) 
		  {
		    if ($objSmith->fields['elitetype'] == 'S')
		      {
			$arrname[] = $objSmith -> fields['name'].' (smoczy)';
		      }
		    else
		      {
			$arrname[] = $objSmith -> fields['name'].' (elfi)';
		      }
                    $arrid[] = $objSmith -> fields['id'];
                    $arrlevel[] = $objSmith -> fields['level'];
                    $arrAmount[] = $objSmith -> fields['amount'];
		    $objLoot = $db->Execute("SELECT `lootnames` FROM `monsters` WHERE `id`=".$objSmith->fields['elite']);
		    $arrTmp = explode(";", $objLoot->fields['lootnames']);
		    if ($_GET['type'] == 'A' || $_GET['type'] == 'W')
		      {
			$arrLoot[] = $arrTmp[0].":8 ".$arrTmp[1].":4 ".$arrTmp[2].":2 ".$arrTmp[3].":1";
		      }
		    else
		      {
			$arrLoot[] = $arrTmp[0].":4 ".$arrTmp[1].":3 ".$arrTmp[2].":2 ".$arrTmp[3].":1";
		      }
		    $objLoot->Close();
                    $objSmith -> MoveNext();
		  }
                $objSmith -> Close();
                $smarty -> assign(array("Name" => $arrname, 
                                        "Id" => $arrid, 
                                        "Level" => $arrlevel, 
                                        "Amount" => $arrAmount,
					"Tloot" => "Części potwora",
					"Loot" => $arrLoot));
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
    /**
     * Continue making items
     */
    if (isset($_GET['konty'])) 
      {
	if ($objWork->fields['elite'] == 0)
	  {
	    error("Nie możesz wykonywać tutaj zwykłego ekwipunku.");
	  }

        $intItems = 0;
        $intGainexp = 0;
        $intAbility = 0;
        $intCost = ceil($objSmith -> fields['cost'] / 200);
        $arrName = array(M_COPPER, M_BRONZE, M_BRASS, M_IRON, M_STEEL);
        $arrMaxbonus = array(21, 25, 30, 35, 40);
        if ($objSmith -> fields['type'] == 'W' || $objSmith -> fields['type'] == 'A')
	  {
            $arrDur = array(50, 90, 170, 330, 650);
	  }
	else
	  {
            $arrDur = array(30, 50, 90, 170, 330);
	  }
	if ($objSmith -> fields['type'] == 'A')
	  {
            $intAgility = floor($objSmith -> fields['level'] / 2);
	    $intPower = $objSmith -> fields['level'] * 3;
            $intExp = 4;
	  }
	elseif ($objSmith -> fields['type'] == 'L')
	  {
            $intAgility = floor($objSmith -> fields['level'] / 5);
	    $intPower = $objSmith -> fields['level'];
            $intExp = 2;
	  }
	else
	  {
            $intAgility = 0;
	    $intPower = $objSmith -> fields['level'];
            $intExp = 2;
	  }
        $arrMineral = array('copper', 'bronze', 'brass', 'iron', 'steel');
        $intKey = array_search($objWork -> fields['mineral'], $arrMineral);
	$intMaxdur = $arrDur[$intKey];
	$intMaxbonus = $arrMaxbonus[$intKey] * $objSmith->fields['level'];
        $strName = $objSmith -> fields['name']." ".$arrName[$intKey];
        $intChance = $player->smith / $objSmith -> fields['level'];
        if ($intChance > 90)
        {
            $intChance = 90;
        }
        if ($_POST['razy'] == $intNeed) 
        {
	    $intRoll = rand(1, 100);
	    if ($intRoll < $intChance)
	      {
		$intBonus = floor(rand(1, $player->smith) + ($player->strength / 10));
		if ($intBonus > $intMaxbonus)
		  {
		    $intBonus = $intMaxbonus;
		  }
		if ($objWork->fields['elitetype'] == 'S')
		  {
		    $intAgility = 0 - $intBonus;
		  }
		else
		  {
		    $intPower = $intBonus;
		  }
		$intGainexp = ($objSmith->fields['level'] * (100 + $player->smith / 5));
		$intAbility = ($objSmith->fields['level'] / 50);
		$intItems++;
	      }
            if ($intItems) 
	      {
		$intGainexp = $intGainexp * $intExp;
		$intAbility = $intAbility * $intExp;
		$arrRepair = array(1, 4, 16, 64, 256);
		if ($objSmith->fields['type'] == 'W' || $objSmith->fields['type'] == 'A')
		  {
		    $intRepaircost = $arrItem['level'] * $arrRepair[$intKey] * 2;
		  }
		else
		  {
		    $intRepaircost = $arrItem['level'] * $arrRepair[$intKey] * 1;
		  }
		additem($objSmith->fields['type'], $strName, $intMaxdur, $intPower, $intAgility, $intCost, $player->id, $objSmith->fields['level'], $intRepaircost, $objSmith->fields['twohand']);
                $smarty -> assign ("Message", YOU_MAKE.$strName."(+ ".$intPower.") (".($intAgility * -1)."% zr) (".$intMaxdur."/".$intMaxdur.")".AND_GAIN2.$intGainexp.AND_EXP2.$intAbility.IN_SMITH);
	      } 
	    else 
	      {
		$intAbility = 0.02;
                $intGainexp = 0;
                $smarty -> assign ("Message", YOU_TRY.$strName.BUT_FAIL.$intAbility.IN_SMITH);
	      }
            $db -> Execute("DELETE FROM `smith_work` WHERE `id`=".$objWork->fields['id']);
            checkexp($player->exp, $intGainexp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'ability', $intAbility);
        } 
            else 
        {
            $uenergia = ($_POST['razy'] + $objWork -> fields['u_energy']);
            if ($objSmith -> fields['type'] == 'A' || $objSmith->fields['type'] == 'W')
            {
                $intEnergy = $objSmith -> fields['level'] * 10;
            }
                else
            {
                $intEnergy = $objSmith -> fields['level'] * 5;
            }
            $procent = (($uenergia / $intEnergy) * 100);
            $procent = round($procent, "0");
            $need = $objWork -> fields['n_energy'] - $uenergia;
            $db -> Execute("UPDATE `smith_work` SET `u_energy`=`u_energy`+".$_POST['razy']." WHERE `id`=".$objWork->fields['id']);
            $smarty -> assign ("Message", YOU_WORK.$strName.NEXT_EN.$_POST['razy'].NOW_IS.$procent.YOU_NEED2.$need.S_ENERGY);
        }
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$_POST['razy']." WHERE `id`=".$player -> id);
    }
    /**
     * Start making items
     */
    if (isset($_GET['rob'])) 
    {
        if ($objSmith -> fields['type'] == 'A')
        {
            $intAmount = floor($_POST['razy'] / ($objSmith -> fields['level'] * 10));
            $intEnergy = $objSmith -> fields['level'] * 10;
        }
            else
        {
	    if ($objSmith->fields['type'] == 'W')
	      {
		$intAmount = floor($_POST['razy'] / ($objSmith -> fields['level'] * 10));
		$intEnergy = $objSmith -> fields['level'] * 10;
	      }
	    else
	      {
		$intAmount = floor($_POST['razy'] / ($objSmith -> fields['level'] * 5));
		$intEnergy = $objSmith -> fields['level'] * 5;
	      }
        }
        if ($intAmount)
        {
            $intAmineral = $intAmount * $objSmith -> fields['amount'];
	    $intEnergy2 = $intAmount * $intEnergy;
	    if ($objSmith->fields['type'] == 'A' || $objSmith->fields['type'] == 'W')
	      {
		$arrLoots = array(8 * $intAmount, 4 * $intAmount, 3 * $intAmount, $intAmount);
	      }
	    else
	      {
		$arrLoots = array(4 * $intAmount, 3 * $intAmount, 2 * $intAmount, $intAmount);		
	      }
        }
	else
        {
            $intAmineral = $objSmith -> fields['amount'];
	    $intEnergy2 = $intEnergy;
	    if ($objSmith->fields['type'] == 'A' || $objSmith->fields['type'] == 'W')
	      {
		$arrLoots = array(8, 4, 3, 1);
	      }
	    else
	      {
		$arrLoots = array(4, 3, 2, 1);
	      }
        }
        if ($intAmineral > $objMineral -> fields[$strMineral])
        {
            error (NO_MAT);
        }
	//Check if we have enough loots
	$objLoot = $db->Execute("SELECT `lootnames` FROM `monsters` WHERE `id`=".$objSmith->fields['elite']);
	$arrLootsname = explode(";", $objLoot->fields['lootnames']);
	$intLoot = 0;
	for ($i = 0; $i < 4; $i++)
	  {
	    $intLoot = 0;
	    $objAmount = $db->Execute("SELECT `amount` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$arrLootsname[$i]."' AND status='U'");
	    while (!$objAmount->EOF)
	      {
		$intLoot += $objAmount->fields['amount'];
		$objAmount->MoveNext();
	      }
	    if ($intLoot < $arrLoots[$i])
	      {
		error("Nie masz wystarczającej ilości ".$arrLootsname[$i].".");
	      }
	    $objAmount->Close();
	  }
	if ($objSmith->fields['elite'] == 0)
	  {
	    error("Nie możesz wykonywać tutaj zwykłego ekwipunku.");
	  }

        $intItems = 0;
        $intGainexp = 0;
        $intAbility = 0;
        $intCost = ceil($objSmith -> fields['cost'] / 200);
        $arrName = array(M_COPPER, M_BRONZE, M_BRASS, M_IRON, M_STEEL);
        $arrMaxbonus = array(21, 25, 30, 35, 40);
	$intMaxbonus = $arrMaxbonus[$intKey] * $objSmith->fields['level'];
        if ($objSmith -> fields['type'] == 'W' || $objSmith -> fields['type'] == 'A')
        {
            $arrDur = array(50, 90, 170, 330, 650);
        }
            else
        {
            $arrDur = array(30, 50, 90, 170, 330);
        }
	if ($objSmith -> fields['type'] == 'A')
	  {
            $intAgility = floor($objSmith -> fields['level'] / 2);
	    $intPower = $objSmith -> fields['level'] * 3;
            $intExp = 4;
	  }
	elseif ($objSmith -> fields['type'] == 'L')
	  {
            $intAgility = floor($objSmith -> fields['level'] / 5);
	    $intPower = $objSmith -> fields['level'];
            $intExp = 2;
	  }
	else
	  {
            $intAgility = 0;
	    $intPower = $objSmith -> fields['level'];
            $intExp = 2;
	  }
        $intKey = array_search($_POST['mineral'], $arrMineral);
	$intMaxdur = $arrDur[$intKey];
	$intMaxbonus = $arrMaxbonus[$intKey] * $objSmith->fields['level'];
        $strName = $objSmith -> fields['name']." ".$arrName[$intKey];
        $intChance = $player->smith / $objSmith -> fields['level'];
        if ($intChance > 90)
        {
            $intChance = 90;
        }
        if ($intAmount > 0) 
	  {
	    $arrPower = array();
	    $arrAgility = array();
	    $arrAmount = array();
            for ($i = 1; $i <= $intAmount; $i++) 
	      {
		$intRoll = rand(1, 100);
		if ($intRoll < $intChance)
		  {
		    $intBonus = floor(rand(1, $player->smith) + ($player->strength / 10));
		    if ($intBonus > $intMaxbonus)
		      {
			$intBonus = $intMaxbonus;
		      }
		    if ($objSmith->fields['elitetype'] == 'S')
		      {
			$intIndex = array_search($intBonus, $arrPower);
			if ($intIndex === FALSE)
			  {
			    $arrPower[] = $intBonus;
			    $arrAgility[] = $intAgility;
			    $arrAmount[] = 1;
			  }
			else
			  {
			    $arrAmount[$intIndex]++;
			  }
		      }
		    else
		      {
			$intIndex = array_search($intBonus, $arrAgility);
			if ($intIndex === FALSE)
			  {
			    $arrPower[] = $intPower;
			    $arrAgility[] = $intBonus;
			    $arrAmount[] = 1;
			  }
			else
			  {
			    $arrAmount[$intIndex]++;
			  }
		      }
		    $intGainexp += ($objSmith->fields['level'] * (100 + $player->smith / 5));
		    $intAbility += ($objSmith->fields['level'] / 50);
		    $intItems++;
		  }
		else
		  {
		    $intGainexp += 0.01;
		  }
	      }
	    $arrMaked = array();
	    $arrRepair = array(1, 4, 16, 64, 256);
            $intRepaircost = $objSmith->fields['level'] * $arrRepair[$intKey] * 2;
	    for ($i = 0; $i < count($arrPower); $i++)
	      {
		additem($objSmith->fields['type'], $strName, $intMaxdur, $arrPower[$i], $arrAgility[$i], $intCost, $player->id, $objSmith->fields['level'], $intRepaircost, $objSmith->fields['twohand'], $arrAmount[$i]);
		$arrMaked[] = array("name" => $strName, "power" => $arrPower[$i], "zr" => ($arrAgility[$i] * -1), "wt" => $intMaxdur);
	      }
            $intGainexp = ceil($intGainexp);
	    $intAbility = $intAbility * $intExp;
	    $intGainexp = $intGainexp * $intExp;
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
            $db -> Execute("INSERT INTO `smith_work` (`owner`, `name`, `u_energy`, `n_energy`, `mineral`, `elite`) VALUES(".$player -> id.", '".$objSmith -> fields['name']."', ".$_POST['razy'].", ".$intEnergy.", '".$_POST['mineral']."', ".$objSmith->fields['elite'].")");
            $smarty -> assign ("Message", YOU_WORK.$objSmith -> fields['name'].YOU_USE.$_POST['razy'].AND_MAKE.$procent.TO_END.$need.S_ENERGY);
	  }
        $db -> Execute("UPDATE `minerals` SET `".$_POST['mineral']."`=`".$_POST['mineral']."`-".$intAmineral." WHERE `owner`=".$player -> id);
	for ($i = 0; $i < 4; $i++)
	  {
	    $objAmount = $db->Execute("SELECT `id`, `amount` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$arrLootsname[$i]."' AND status='U'");
	    while (!$objAmount->EOF)
	      {
		if ($objAmount->fields['amount'] <= $arrLoots[$i])
		  {
		    $db->Execute("DELETE FROM `equipment` WHERE `id`=".$objAmount->fields['id']);
		    $arrLoots[$i] -= $objAmount->fields['amount'];
		    if ($arrLoots[$i] > 0)
		      {
			$objAmount->MoveNext();
		      }
		    else
		      {
			break;
		      }
		  }
		else
		  {
		    $db->Execute("UPDATE `equipment` SET `amount`=`amount`-".$arrLoots[$i]." WHERE `id`=".$objAmount->fields['id']);
		    break;
		  }
	      }
	    $objAmount->Close();
	  }
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
	$_GET['component'] = intval($_GET['component']);
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
    if ($player->clas == 'Rzemieślnik')
      {
	$strElite = "Wykonaj elitarny ekwipunek";
      }
    else
      {
	$strElite = '';
      }
    $smarty -> assign(array("Smithinfo" => SMITH_INFO,
                            "Aplans" => A_PLANS,
                            "Asmith" => A_SMITH,
                            "Aastral" => A_ASTRAL,
			    "Aelite" => $strElite));
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
                        "Make" => $_GET['rob'], 
                        "Continue" => $_GET['konty'], 
                        "Type" => $_GET['type'], 
                        "Cont" => $_GET['ko']));
$smarty -> display ('kowal.tpl');

require_once("includes/foot.php");
?>
