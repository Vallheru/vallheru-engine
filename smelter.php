<?php
/**
 *   File functions:
 *   Smelter - smelt minerals
 *
 *   @name                 : smelter.php                            
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

$title = "Huta";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/smelter.php");

if ($player -> location != 'Altara') 
{
    error (ERROR);
}

$objSmelter = $db -> Execute("SELECT level FROM smelter WHERE owner=".$player -> id);

/**
 * Main menu
 */
if (!isset($_GET['step']))
{
    if (!$objSmelter -> fields['level'])
    {
        $smarty -> assign(array("Nosmelter" => NO_SMELTER,
                                "Nosmelter2" => NO_SMELTER2,
                                "Aupgrade" => A_UPGRADE,
                                "Smelterlevel" => 0));
    }
        else
    {
        $smarty -> assign(array("Smelterinfo" => SMELTER_INFO,
                                "Smelterlevel" => $objSmelter -> fields['level'],
                                "Aupgrade" => A_UPGRADE2,
                                "Asmelt" => A_SMELT));
    }
}

/**
 * Upgrade smelter
 */
if (isset($_GET['step']) && $_GET['step'] == 'upgrade')
{
    if (!isset($objSmelter -> fields['level']))
    {
        $intSmelterlevel = 0;
    }
        else
    {
        $intSmelterlevel = $objSmelter -> fields['level'];
    }
    if (!$objSmelter -> fields['level'])
    {
        $strLevel = LEVEL1;
    }
    if ($objSmelter -> fields['level'] == 1)
    {
        $strLevel = LEVEL2;
    }
    if ($objSmelter -> fields['level'] == 2)
    {
        $strLevel = LEVEL3;
    }
    if ($objSmelter -> fields['level'] == 3)
    {
        $strLevel = LEVEL4;
    }
    if ($objSmelter -> fields['level'] == 4)
    {
        $strLevel = LEVEL5;
    }
    /**
     * Upgrade menu
     */
    if (!isset($_GET['upgrade']))
    {
        $_GET['upgrade'] = '';
        if ($objSmelter -> fields['level'] == 5)
        {
            $strLevel = NO_UPGRADE;
        }
        $smarty -> assign(array("Upgradeinfo" => UPGRADE_INFO,
                                "Levelinfo" => $strLevel,
                                "Aupgrade" => A_UPGRADE,
                                "Message" => ''));
    }
    /**
     * Start upgrade
     */
    if (isset($_GET['upgrade']) && $_GET['upgrade'] == 'Y')
    {
        if ($objSmelter -> fields['level'] == 5)
        {
            error(NO_UPGRADE);
        }
        $arrCost = array(1000, 5000, 20000, 60000, 120000);
        if ($player -> credits < $arrCost[$intSmelterlevel])
        {
            error(NO_MONEY);
        }
        if ($intSmelterlevel == 0)
        {
            $db -> Execute("INSERT INTO smelter (owner, level) VALUES(".$player -> id.", 1)");
        }
            else
        {
            $db -> Execute("UPDATE smelter SET level=level+1 WHERE owner=".$player -> id);
        }
        $db -> Execute("UPDATE players SET credits=credits-".$arrCost[$intSmelterlevel]." WHERE id=".$player -> id);
        $smarty -> assign("Message", YOU_UPGRADE.$strLevel);
    }
    $smarty -> assign(array("Upgrade" => $_GET['upgrade'],
                            "Smelterlevel" => $intSmelterlevel));
}

/**
 * Smelt ore
 */
if (isset($_GET['step']) && $_GET['step'] == 'smelt')
{
    if (!$objSmelter -> fields['level'])
    {
        error(NO_SMELT);
    }
    $arrAction = array('copper', 'bronze', 'brass', 'iron', 'steel');
    $arrBillets = array(1, 2, 3, 5, 8);
    /**
     * Smelt menu
     */
     $arrSmelt = array(SMELT1, SMELT2, SMELT3, SMELT4, SMELT5);
     $arrSmelt = array_slice($arrSmelt, 0, $objSmelter -> fields['level']);
     $arrAction = array_slice($arrAction, 0, $objSmelter -> fields['level']);
     $smarty -> assign(array("Smeltinfo" => SMELT_INFO,
                             "Asmelt" => $arrSmelt,
                             "Smeltaction" => $arrAction,
                             "Message" => ''));
    if (isset($_GET['smelt']))
    {
        if (!in_array($_GET['smelt'], $arrAction))
        {
            error(ERROR);
        }
        if ($player -> hp < 1)
        {
            error(YOU_DEAD);
        }
        $intKey = array_search($_GET['smelt'], $arrAction);
        $arrSql = array('copperore, coal', 'copperore, tinore, coal', 'copperore, zincore, coal', 'ironore, coal', 'ironore, coal');
        $strSql = $arrSql[$intKey];
        $arrOres = explode(", ", $strSql);
        $objTestore = $db -> Execute("SELECT ".$strSql." FROM minerals WHERE owner=".$player -> id) or die($db -> ErrorMsg());
        foreach ($arrOres as $strOres)
        {
            if (!$objTestore -> fields[$strOres])
            {
                error(NO_MINERALS);
            }
        }
        if (!$player -> energy)
        {
            error(NO_ENERGY);
        }
        $arrSmeltamount = array(array(2, 1),
                                array(1, 1, 2),
                                array(2, 1, 2),
                                array(2, 3),
                                array(3, 7));
        $arrOresamount = $arrSmeltamount[$intKey];
        $arrOresname = array('copperore' => MIN1,
                             'tinore' => MIN2,
                             'zincore' => MIN3,
                             'ironore' => MIN4,
                             'coal' => MIN5);
        $arrAmount2 = array();
        $arrOresname2 = array();
        $strOreshave = YOU_HAVE;
        foreach ($arrOres as $strKey)
        {
            $strOreshave = $strOreshave.$objTestore -> fields[$strKey].$arrOresname[$strKey];
        }
        $i = 0;
        $intMaxamount = 0;
	$intEnergy = $player->energy / ($arrBillets[$intKey] / 10);
        foreach ($arrOresamount as $intAmount)
        {
            $strKey = $arrOres[$i];
            $intTestamount = floor($objTestore -> fields[$strKey] / $intAmount);
            if ($intTestamount < $intMaxamount || !$i)
            {
                $intMaxamount = $intTestamount;
            }
            $i ++;
        }
	if ($intEnergy < $intMaxamount)
	  {
	    $intMaxamount = $intEnergy;
	  }
        $arrSmeltmineral = array(SMELTM1, SMELTM2, SMELTM3, SMELTM4, SMELTM5);
        $smarty -> assign(array("Asmelt2" => A_SMELT,
                                "Smeltm" => $arrSmeltmineral[$intKey],
                                "Youhave" => $strOreshave,
                                "Maxamount" => floor($intMaxamount)));
        /**
         * Start smelting
         */
        if (isset($_POST['amount']))
        {
	    checkvalue($_POST['amount']);
	    $intEnergy = ($arrBillets[$intKey] / 10) * $_POST['amount'];
	    $intEnergy = round($intEnergy, 2);
            if ($intEnergy > $player -> energy)
            {
                error(NO_ENERGY2);
            }
            $i = 0;
            $arrAmount = array();
	    $player->curstats(array(), TRUE);
	    $player->curskills(array('metallurgy'), TRUE, TRUE);
            foreach ($arrOres as $strOres)
            {
                $arrAmount[$i] = $_POST['amount'] * $arrOresamount[$i];
                if ($objTestore -> fields[$strOres] < $arrAmount[$i])
                {
                    error(NO_MINERALS2);
                }
                $i++;
            }
	    $intAmount = 0;
	    $intDiff = 100 * $arrBillets[$intKey]; 
	    for ($i = 0; $i < $_POST['amount']; $i++)
	      {
		$intRoll = rand(1, $player->metallurgy * 100);
		$intRoll2 = rand(1, $intDiff);
		if ($intRoll > $intRoll2)
		  {
		    $intAmount++;
		    if ($intRoll * 2 > $intDiff)
		      {
			$intAmount++;
		      }
		  }
	      }
            $intMinerals = count($arrAmount);
            $strSql = '';
            for ($i = 0; $i < $intMinerals; $i++)
            {
                $strSql = $strSql.", ".$arrOres[$i]."=".$arrOres[$i]."-".$arrAmount[$i];
            }
	    $fltAbility = round(($intAmount / 50)  + (($_POST['amount'] - $intAmount) * 0.01), 2);
	    $intExp = $intAmount * ($arrBillets[$intKey] / 4);
	    if ($player->clas == 'Rzemieślnik')
	      {
		$fltAbility = $fltAbility * 2;
		$intExp = $intExp * 2;
	      }
            $db -> Execute("UPDATE minerals SET ".$_GET['smelt']."=".$_GET['smelt']."+".$intAmount.$strSql." WHERE owner=".$player -> id);
	    require_once('includes/checkexp.php');
	    checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'metallurgy', $fltAbility);
            $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
            $smarty -> assign("Message", YOU_SMELT." ".$intAmount." ".$arrSmeltmineral[$intKey].". Zdobywasz ".$fltAbility." w umiejętności Hutnictwo oraz ".$intExp." PD.");
        }
    }
}

/**
 * Free memory
 */
$objSmelter -> Close();

/**
 * Initialization of variable
 */
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}
if (!isset($_GET['smelt']))
{
    $_GET['smelt'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Step" => $_GET['step'],
    "Aback" => A_BACK,
    "Smelt" => $_GET['smelt']));
$smarty -> display ('smelter.tpl');

require_once("includes/foot.php");
?>
