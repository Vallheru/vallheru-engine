<?php
/**
 *   File functions:
 *   Players farms - herbs
 *
 *   @name                 : farm.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 09.05.2012
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

$title = "Farma";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/farm.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error(ERROR);
}

/**
 * Main menu
 */
if (!isset($_GET['step']))
{
    $smarty -> assign(array("Farminfo" => FARM_INFO,
                            "Aplantation" => A_PLANTATION,
                            "Ahouse" => A_HOUSE,
                            "Aencyclopedia" => A_ENCYCLOPEDIA));
    $_GET['step'] = '';
    $_GET['action'] = '';
}

/**
 * Herbs info
 */
if (isset($_GET['step']) && $_GET['step'] == 'herbsinfo')
{
    $smarty -> assign(array("Herbsinfo" => HERBS_INFO,
                            "Ilaniinfo" => ILANI_INFO,
                            "Illaniasinfo" => ILLANIAS_INFO,
                            "Nutariinfo" => NUTARI_INFO,
                            "Dynallcainfo" => DYNALLCA_INFO));
    $_GET['action'] = '';
}

require_once('includes/checkexp.php');

/**
 * House of gardener
 */
if (isset($_GET['step']) && $_GET['step'] == 'house')
{
    $objHerbs = $db -> Execute("SELECT `illani`, `illanias`, `nutari`, `dynallca` FROM `herbs` WHERE `gracz`=".$player -> id);
    if (!$objHerbs -> fields['illani'] && !$objHerbs -> fields['illanias'] && !$objHerbs -> fields['nutari'] && !$objHerbs -> fields['dynallca'])
    {
        error(NO_HERBS);
    }
    $arrHerbs = array('illani', 'illanias', 'nutari', 'dynallca');
    $arrHerbsname = array(HERB1, HERB2, HERB3, HERB4);
    $arrHerbsaviable = array();
    $arrHerbsoption = array();
    $arrHerbsamount = array();
    $j = 0;
    for ($i = 0; $i < 4; $i++)
    {
        $strName = $arrHerbs[$i];
        if ($objHerbs -> fields[$strName])
        {
            $arrHerbsaviable[$j] = $arrHerbsname[$i];
            $arrHerbsamount[$j] = $objHerbs -> fields[$strName];
            $arrHerbsoption[$j] = $strName;
            $j++;
        }
    }
    if (!isset($_GET['action']))
    {
        $_GET['action'] = '';
    }

    /**
     * Dryings herbs
     */
    if (isset($_GET['action']) && $_GET['action'] == 'dry')
    {
        if (!isset($_POST['amount']))
	  {
	    error("Podaj ile ziół chcesz wysuszyć.");
	  }
	checkvalue($_POST['amount']);
        if (!in_array($_POST['herb'], $arrHerbs))
        {
            error(ERROR);
        }
        $intAmountherbs = 10 * $_POST['amount'];
        if ($intAmountherbs > $objHerbs -> fields[$_POST['herb']])
        {
            error(NO_HERB);
        }
        $intAmountenergy = 0.2 * $_POST['amount'];
        if ($intAmountenergy > $player -> energy)
        {
            error(NO_ENERGY);
        }
        if ($player -> hp < 1)
        {
            error(YOU_DEAD);
        }
        $intKey = array_search($_POST['herb'], $arrHerbs);
        $intAmountseeds = 0;
        for ($i = 0; $i < $_POST['amount']; $i++)
        {
            $intRoll = rand(1, 100);
            if ($intRoll > 5)
            {
                $intAmountseeds++;
            }
        }
	$fltSkill = $intAmountseeds / 100;
	$intExp = $intAmountseeds * 10;
	if ($player->clas == 'Rzemieślnik') 
	  {
	    $fltSkill = $fltSkill * 2;
	    $intExp = $intExp * 2;
	  }
        $arrSeeds = array('ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
        $db -> Execute("UPDATE `herbs` SET `".$arrHerbs[$intKey]."`=`".$arrHerbs[$intKey]."`-".$intAmountherbs.", `".$arrSeeds[$intKey]."`=`".$arrSeeds[$intKey]."`+".$intAmountseeds." WHERE `gracz`=".$player -> id);
	checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'herbalist', $fltSkill);
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intAmountenergy." WHERE `id`=".$player -> id);
	if ($player->gender == 'F')
	  {
	    $strLast = "aś";
	  }
	else
	  {
	    $strLast = "eś";
	  }
        message("success", YOU_MAKE.$intAmountherbs.T_HERB.$intAmountseeds.T_PACKS." Zdobył".$strLast." <b>".$fltSkill."</b> do umiejętności Zielarstwo oraz ".$intExp." PD.");
    }
    $smarty -> assign(array("Houseinfo" => HOUSE_INFO,
                            "Adry" => A_DRY,
                            "Tdry" => T_DRY,
                            "Tpack" => T_PACK,
                            "Tamount" => T_AMOUNT,
                            "Herbsname" => $arrHerbsaviable,
                            "Herbsamount" => $arrHerbsamount,
                            "Herbsoption" => $arrHerbsoption,
                            "Action" => $_GET['action']));
    $objHerbs -> Close();
}

/**
 * Plantation
 */
if (isset($_GET['step']) && $_GET['step'] == 'plantation')
{
    $objPlantation = $db -> Execute("SELECT * FROM `farms` WHERE `owner`=".$player -> id);
    if ($objPlantation->fields['lands'])
      {
	$intLandsamount = $objPlantation -> fields['lands'];
      }
    else
      {
	$intLandsamount = '';
      }
    $smarty -> assign("Farminfo", FARM_INFO);
    if (!isset($_GET['action']))
    {
        $_GET['action'] = '';
    }

    /**
     * Upgrade plantation
     */
    if (isset($_GET['action']) && $_GET['action'] == 'upgrade')
    {
        if (!$objPlantation -> fields['lands'])
	  {
            $intMithcost = 20;
	  }
	else
	  {
	    $intMithcost = 0;
	    if (isset($_POST['lamount']))
	      {
		checkvalue($_POST['lamount']);
		for ($i = 0; $i < $_POST['lamount']; $i++)
		  {
		    $intMithcost += 20 * ($objPlantation -> fields['lands'] + $i);
		  }
	      }
	  }
        $smarty -> assign(array("Tmith" => T_MITH,
                                "Buyland" => BUY_LAND,
                                "Buylandcost" => $intMithcost));

        /**
         * Buy land, glasshouse, irrigation etc
         */
        if (isset($_GET['buy']))
        {
            $arrBuy = array('L', 'G', 'I', 'C');
            if (!in_array($_GET['buy'], $arrBuy))
            {
                error(ERROR);
            }
	    $strBuyitem = '';
            if ($_GET['buy'] == 'L')
            {
                if ($player -> platinum < $intMithcost)
		  {
		    message('error', NO_MITH);
		  }
		elseif (!isset($_POST['lamount']))
		  {
		    message('error', 'Podaj ile ziemi chcesz dokupić do plantacji.');
		  }
		elseif ($intLandsamount + $_POST['lamount'] > 100)
		  {
		    message('error', "Nie możesz dokupić większej ilości ziemi do farmy.");
		  }
		else
		  {
		    if (!$objPlantation -> fields['lands'])
		      {
			$db -> Execute("INSERT INTO `farms` (`owner`, `lands`) VALUES(".$player -> id.", 1)");
			$objPlantation = $db -> Execute("SELECT * FROM `farms` WHERE `owner`=".$player -> id);
			$intLandsamount = $objPlantation -> fields['lands'];
			$strBuyitem = ' nieco ziemi pod uprawę. Kosztowało to 20 sztuk mithrilu.';
		      }
                    else
		      {
			$db -> Execute("UPDATE `farms` SET `lands`=`lands`+".$_POST['lamount']." WHERE `id`=".$objPlantation -> fields['id']);
			$objPlantation->fields['lands'] += $_POST['lamount'];
			$intLandsamount += $_POST['lamount'];
			$strBuyitem = ' '.$_POST['lamount'].' obszar(ów) ziemi do plantacji. Kosztowało to '.$intMithcost.' sztuk mithrilu.';
		      }
		    $db -> Execute("UPDATE players SET platinum=platinum-".$intMithcost." WHERE id=".$player -> id);
		  }
            }
	    else
	      {
		checkvalue($_POST[strtolower($_GET['buy']).'amount']);
		$intGoldcost = $_POST[strtolower($_GET['buy']).'amount'] * 1000;
                $arrItems = array('glasshouse', 'irrigation', 'creeper');
                $intKey = array_search($_GET['buy'], $arrBuy) - 1;
                if ($player -> credits < $intGoldcost)
		  {
		    message('error', NO_MONEY);
		  }
                elseif ($objPlantation -> fields['lands'] < ($objPlantation -> fields[$arrItems[$intKey]] + $_POST[strtolower($_GET['buy']).'amount']))
		  {
                    message('error', NO_LANDS);
		  }
		else
		  {
		    $db -> Execute("UPDATE farms SET ".$arrItems[$intKey]."=".$arrItems[$intKey]."+".$_POST[strtolower($_GET['buy']).'amount']." WHERE owner=".$player -> id) or die($db -> ErrorMsg());
		    $db -> Execute("UPDATE players SET credits=credits-".$intGoldcost." WHERE id=".$player -> id);
		    $arrText = array('szklarnię(i)', 'system(y) nawiadniający(e)', 'konstrukcję(i) na pnącza');
		    $strBuyitem = ' '.$_POST[strtolower($_GET['buy']).'amount'].' '.$arrText[$intKey].'. Kosztowało to '.$intGoldcost.' sztuk złota.';
		    $objPlantation->fields[$arrItems[$intKey]] += $_POST[strtolower($_GET['buy']).'amount'];
		  }
            }
	    if ($strBuyitem != '')
	      {
		message("success", YOU_BUY.$strBuyitem);
	      }
	    $_GET['action'] = '';
        }
    }

    /**
     * Sow herbs
     */
    if (isset($_GET['action']) && $_GET['action'] == 'sow')
    {
        if (!$objPlantation -> fields['lands'])
        {
            error(NO_FARM);
        }
	$objUsedlands = $db -> Execute("SELECT SUM(`amount`) FROM `farm` WHERE `owner`=".$player -> id);
	if (!$objUsedlands->fields['SUM(`amount`)'])
	  {
	    $intUsedlands = 0;
	  }
	else
	  {
	    $intUsedlands = $objUsedlands->fields['SUM(`amount`)'];
	  }
	$objUsedlands -> Close();
	$intFreelands = $objPlantation->fields['lands'] - $intUsedlands;
        if ($objPlantation -> fields['lands'] == $intUsedlands)
        {
            error(NO_LAND);
        }
        $objSeeds = $db -> Execute("SELECT ilani_seeds, illanias_seeds, nutari_seeds, dynallca_seeds FROM herbs WHERE gracz=".$player -> id);
        if (!$objSeeds -> fields['ilani_seeds'] && !$objSeeds -> fields['illanias_seeds'] && !$objSeeds -> fields['nutari_seeds'] && !$objSeeds -> fields['dynallca_seeds'])
        {
            error(NO_SEEDS);
        }
        $arrSeeds = array('ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
        $arrSeedsname = array(HERB1, HERB2, HERB3, HERB4);
        $arrSeedsaviable = array();
        $arrSeedsoption = array();
        $arrSeedsamount = array();
        $j = 0;
        for ($i = 0; $i < 4; $i++)
        {
            $strName = $arrSeeds[$i];
            if ($objSeeds -> fields[$strName])
            {
                $arrSeedsaviable[$j] = $arrSeedsname[$i];
                $arrSeedsamount[$j] = $objSeeds -> fields[$strName];
                $arrSeedsoption[$j] = $strName;
                $j++;
            }
        }
        $smarty -> assign(array("Farminfo" => SAW_INFO,
                                "Asaw" => A_SAW,
                                "Tlands" => T_LANDS,
                                "Tamount" => T_AMOUNT,
                                "Seedsname" => $arrSeedsaviable,
                                "Seedsamount" => $arrSeedsamount,
                                "Seedsoption" => $arrSeedsoption));

        /**
         * Start sow herbs
         */
        if (isset($_GET['step2']) && $_GET['step2'] == 'next')
        {
            if (!in_array($_POST['seeds'], $arrSeeds))
            {
                error(ERROR);
            }
 	    checkvalue($_POST['amount']);
            if ($_POST['amount'] > $intFreelands)
            {
                error(NO_FREE);
            }
            if ($player -> hp < 1)
            {
                error(YOU_DEAD);
            }
            $intEnergy = $_POST['amount'] * 0.2;
            if ($intEnergy > $player -> energy)
            {
                error(NO_ENERGY);
            }
            $intKey = array_search($_POST['seeds'], $arrSeeds);
            if ($_POST['amount'] > $objSeeds -> fields[$arrSeeds[$intKey]])
            {
                error(NO_SEED);
            }
            $arrHerbsname = array('illani', 'illanias', 'nutari', 'dynallca');
            if ($intKey)
            {
                $arrItems = array(0, $objPlantation -> fields['glasshouse'], $objPlantation -> fields['irrigation'], $objPlantation -> fields['creeper']);
                $objFarm = $db -> Execute("SELECT amount, name FROM farm WHERE owner=".$player -> id);
                $arrNeeditems = array(0, 0, 0, 0);
                while (!$objFarm -> EOF)
                {
                    $intKey2 = array_search($objFarm -> fields['name'], $arrHerbsname);
                    if ($intKey2 == 1)
                    {
                        $arrNeeditems[1] = $arrNeeditems[1] + $objFarm -> fields['amount'];
                    }
                        elseif ($intKey2 == 2)
                    {
                        $arrNeeditems[1] = $arrNeeditems[1] + $objFarm -> fields['amount'];
                        $arrNeeditems[2] = $arrNeeditems[2] + $objFarm -> fields['amount'];
                    }
                        elseif ($intKey2 == 3)
                    {
                        $arrNeeditems[1] = $arrNeeditems[1] + $objFarm -> fields['amount'];
                        $arrNeeditems[2] = $arrNeeditems[2] + $objFarm -> fields['amount'];
                        $arrNeeditems[3] = $arrNeeditems[3] + $objFarm -> fields['amount'];
                    }
                    $objFarm -> MoveNext();
                }
                $objFarm -> Close();
                for ($i = 1; $i <= $intKey; $i++)
                {
                    $arrNeeditems[$i] = $_POST['amount'] + $arrNeeditems[$i];
                    if (!$arrItems[$i] || $arrItems[$i] < $arrNeeditems[$i])
                    {
                        error(NO_ITEMS);
                    }
                }
            }
            $fltAbility = $_POST['amount'] * 0.01;
	    $intExp = $_POST['amount'] * 5;
	    if ($player->clas == 'Rzemieślnik') 
	      {
		$fltAbility = $fltAbility * 2;
		$intExp = $intExp * 2;
	      }
            $db -> Execute("UPDATE herbs SET ".$arrSeeds[$intKey]."=".$arrSeeds[$intKey]."-".$_POST['amount']." WHERE gracz=".$player -> id);
            $db -> Execute("INSERT INTO farm (owner, amount, name, age) VALUES(".$player -> id.", ".$_POST['amount'].", '".$arrHerbsname[$intKey]."', 0)");
	    checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'herbalist', $fltAbility);
            $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
            message("success", YOU_SAW.$_POST['amount'].T_LANDS2.$arrHerbsname[$intKey].YOU_GAIN.$fltAbility.T_ABILITY." oraz ".$intExp." PD.");
        }
    }

    /**
     * Gather herbs
     */
    if (isset($_GET['action']) && $_GET['action'] == 'chop')
    {
        if (!$objPlantation -> fields['lands'])
        {
            error(NO_FARM);
        }
        $objHerbs = $db -> Execute("SELECT id, amount, name, age FROM farm WHERE owner=".$player -> id);
        if (!$objHerbs -> fields['id'])
        {
            error(NO_HERBS);
        }
        $objHerbs -> Close();
        $smarty -> assign(array("Farminfo" => CHOP_INFO));
        /**
         * Start gather
         */
        if (isset($_GET['id']))
        {
	    checkvalue($_GET['id']);
            $objHerb = $db -> Execute("SELECT `owner`, `amount`, `name`, `age` FROM `farm` WHERE `id`=".$_GET['id']);
            if ($objHerb -> fields['owner'] != $player -> id)
            {
                error(NOT_YOUR);
            }
            $smarty -> assign(array("Herbid" => $_GET['id'],
                                    "Herbname" => $objHerb -> fields['name'],
                                    "Agather" => A_GATHER,
                                    "Froma" => FROM_A,
                                    "Tlands3" => T_LANDS3));
            /**
             * Gather some herbs
             */
            if (isset($_GET['step2']) && $_GET['step2'] == 'next')
            {
	        if (!isset($_POST['amount']))
		  {
		    error(ERROR);
		  }
		checkvalue($_POST['amount']);
                if (!$objHerb -> fields['age'])
                {
                    error(TOO_YOUNG);
                }
                if ($objHerb -> fields['amount'] < $_POST['amount'])
                {
                    error(TO_MUCH);
                }
                if ($player -> hp < 1)
                {
                    error(YOU_DEAD);
                }
                $intEnergy = $_POST['amount'] * 0.2;
                if ($intEnergy > $player -> energy)
                {
                    error(NO_ENERGY);
                }
                $arrAge = array(0, 1, 2, 2, 3, 3, 4, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1);
                $arrHerbname = array('illani', 'illanias', 'nutari', 'dynallca');
                $arrHerbmodif = array(1, 1.5, 2, 2.5);
                $intKey = array_search($objHerb -> fields['name'], $arrHerbname);
                $intRoll = rand(-15, 15) / 100;
                $intKey2 = $objHerb -> fields['age'] - 1;

		/**
		 * Add bonuses to ability
		 */
		$player->curstats(array(), TRUE);
		$player->curskills(array('herbalist'), TRUE, TRUE);

		$intFactor = 1 + ($player->herbalist / 20);
		if ($intFactor > 10)
		  {
		    $intFactor = 10;
		  }
                $intAmount = floor((($arrAge[$intKey2] * $_POST['amount']) / $arrHerbmodif[$intKey]) * $intFactor);
                $intAmount = floor($intAmount + ($intAmount * $intRoll));
                if ($intAmount < 0)
                {
                    $intAmount = 0;
                }
		if ($objHerb -> fields['age'] > 3)
		  {
		    $fltAbility = $intAmount * 0.01;
		    $intExp = $intAmount * 2;
		  }
		else
		  {
		    $fltAbility = 0.01;
		    $intExp = 0;
		  }
		if ($player->clas == 'Rzemieślnik') 
		  {
		    $fltAbility = $fltAbility * 2;
		    $intExp = $intExp * 2;
		  }
                if ($_POST['amount'] < $objHerb -> fields['amount'])
                {
                    $db -> Execute("UPDATE `farm` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$_GET['id']);
                }
                    else
                {
                    $db -> Execute("DELETE FROM `farm` WHERE `id`=".$_GET['id']);
                }
                $db -> Execute("UPDATE `herbs` SET `".$arrHerbname[$intKey]."`=`".$arrHerbname[$intKey]."`+".$intAmount." WHERE `gracz`=".$player -> id);
		checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'herbalist', $fltAbility);
                $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
                message("success", YOU_GATHER.$intAmount.T_AMOUNT2.$arrHerbname[$intKey].T_FARM.$fltAbility.T_ABILITY." oraz ".$intExp." PD");
            }
            $objHerb -> Close();
        }
    }

    $smarty -> assign("Lands", $intLandsamount);
    if (!$objPlantation -> fields['lands'])
      {
	$smarty -> assign("Aupgrade", NO_PLANT);
      }
    else
      {
	$smarty -> assign("Asow", A_SOW);
      }
    if ($objPlantation -> fields['id'])
      {
	$objUsedlands = $db -> Execute("SELECT SUM(`amount`) FROM `farm` WHERE `owner`=".$player -> id);
	if (!$objUsedlands->fields['SUM(`amount`)'])
	  {
	    $intUsedlands = 0;
	  }
	else
	  {
	    $intUsedlands = $objUsedlands->fields['SUM(`amount`)'];
	  }
	$objUsedlands -> Close();
	$intFreelands = $objPlantation->fields['lands'] - $intUsedlands;
	$arrHerbs = $db->GetAll("SELECT * FROM `farm` WHERE `owner`=".$player->id);
	foreach ($arrHerbs as &$arrHerb)
	  {
	    $arrHerb['stage'] = 'test';
	    $arrAge = array(0, 1, 2, 2, 3, 3, 4, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1);
	    if ($arrHerb['age'] < 2)
	      {
		$arrHerb['stage'] = '(zasiana)';
	      }
	    elseif ($arrHerb['age'] > 1 && $arrHerb['age'] < 7)
	      {
		$arrHerb['stage'] = '(sadzonka)';
	      }
	    elseif ($arrHerb['age'] > 6 && $arrHerb['age'] < 11)
	      {
		$arrHerb['stage'] = '(młoda roślina)';
	      }
	    elseif ($arrHerb['age'] > 10 && $arrHerb['age'] < 14)
	      {
		$arrHerb['stage'] = '(rozkwita)';
	      }
	    elseif ($arrHerb['age'] > 13 && $arrHerb['age'] < 18)
	      {
		$arrHerb['stage'] = '(gotowa do zebrania)';
	      }
	    elseif ($arrHerb['age'] > 17 && $arrHerb['age'] < 23)
	      {
		$arrHerb['stage'] = '(przekwita)';
	      }
	    elseif ($arrHerb['age'] > 22)
	      {
		$arrHerb['stage'] = '(zwiędła)';
	      }
	  }
	if (count($arrHerbs))
	  {
	    $strHerbs = 'Lista hodowanych ziół:';
	  }
	else
	  {
	    $strHerbs = 'Obecnie nic nie hodujesz na farmie.';
	  }
	if ($intLandsamount >= 100)
	  {
	    $strBuylands = '';
	  }
	else
	  {
	    $strBuylands = 'obszar(ów) ziemi za';
	  }
	if ($objPlantation->fields['glasshouse'] == $intLandsamount)
	  {
	    $strBuyglass = '';
	  }
	else
	  {
	    $strBuyglass = 'szklarnię(e) za';
	  }
	if ($objPlantation->fields['irrigation'] == $intLandsamount)
	  {
	    $strBuyirrigation = '';
	  }
	else
	  {
	    $strBuyirrigation = 'system(y) nawadniający(e) za';
	  }
	if ($objPlantation->fields['creeper'] == $intLandsamount)
	  {
	    $strBuycreeper = '';
	  }
	else
	  {
	    $strBuycreeper = 'konstrukcję(e) na pnącza za';
	  }
	$smarty->assign(array("Ilands" => I_LANDS,
			      "Iglass" => I_GLASS,
			      "Iirrigation" => I_IRRIGATION,
			      "Icreeper" => I_CREEPER,
			      "Ifreelands" => FREE_LANDS,
			      "Tamount" => T_AMOUNT,
			      "Tage" => T_AGE,
			      "Abuy" => "Dokup",
			      "Tmithcost" => 'sztuk mithrilu.',
			      "Tgoldcost" => 'sztuk złota.',
			      "Aland" => $strBuylands,
			      "Aglass" => $strBuyglass,
			      "Airrigation" => $strBuyirrigation,
			      "Acreeper" => $strBuycreeper,
			      "Therbs" => $strHerbs,
			      "Freelands" => $intFreelands,
			      "Herbs" => $arrHerbs,
			      "Glasshouse" => $objPlantation -> fields['glasshouse'],
			      "Irrigation" => $objPlantation -> fields['irrigation'],
			      "Creeper" => $objPlantation -> fields['creeper']));
        $objPlantation -> Close();
      }
}

/**
* Assign variable to template and display page
*/
$smarty -> assign(array("Step" => $_GET['step'],
			"Action" => $_GET['action'],
                        "Aback" => A_BACK));
$smarty -> display ('farm.tpl');

require_once("includes/foot.php");
