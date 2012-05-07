<?php
/**
 *   File functions:
 *   Mines - digging for minerals
 *
 *   @name                 : mines.php
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

$title = "Kopalnie";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/mines.php");

if ($player -> location != 'Altara')
{
    error (ERROR);
}

$objMines = $db -> Execute("SELECT `owner`, `copper`, `zinc`, `tin`, `iron`, `coal` FROM `mines` WHERE `owner`=".$player -> id);

/**
* Main menu
*/
if (!isset($_GET['mine']))
{
    $arrMinerals = array('copper', 'zinc', 'tin', 'iron', 'coal');
    $arrMineralamount = array(0, 0, 0, 0, 0);
    for ($i = 0; $i < 5; $i++)
    {
        $strMinname = $arrMinerals[$i];
        if ($objMines -> fields[$strMinname])
        {
            $arrMineralamount[$i] = $objMines -> fields[$strMinname];
        }
    }
    /**
     * Did player search for minerals?
     */
    $objSearch = $db -> Execute("SELECT `mineral`, `days` FROM `mines_search` WHERE `player`=".$player -> id);
    if ($objSearch -> fields['mineral'])
    {
        $arrMinname = array(M_COPPER, M_ZINC, M_TIN, M_IRON, M_COAL);
        $intKey = array_search($objSearch -> fields['mineral'], $arrMinerals);
        if ($objSearch -> fields['days'] > 1)
        {
            $strDays = SEARCH_DAYS;
        }
            else
        {
            $strDays = SEARCH_DAY;
        }
        $strSearch = YOU_SEARCH.$arrMinname[$intKey].THIS_TAKE.$objSearch -> fields['days'].$strDays;
    }
    else
    {
        $strSearch = '';
    }
    $objSearch -> Close();
    $smarty -> assign(array("Minesinfo" => MINES_INFO,
                            "Acopper" => A_COPPER,
                            "Azinc" => A_ZINC,
                            "Atin" => A_TIN,
                            "Airon" => A_IRON,
                            "Acoal" => A_COAL,
                            "Minamount" => $arrMineralamount,
                            "Minessearch" => $strSearch));
}

/**
* Mine menu
*/
if (isset($_GET['mine']) && !isset($_GET['step']))
{
    $arrMines = array('copper', 'zinc', 'tin', 'iron', 'coal');
    if (!in_array($_GET['mine'], $arrMines))
    {
        error(ERROR);
    }
    $arrMinerals = array(M_COPPER, M_ZINC, M_TIN, M_IRON, M_COAL);
    $intKey = array_search($_GET['mine'], $arrMines);
    if ($objMines -> fields[$_GET['mine']] > 0)
    {
        $smarty -> assign("Mine2", 'N');
    }
    else
    {
        $smarty -> assign("Mine2", 'Y');
    }
    $smarty -> assign(array("Mineinfo" => MINE_INFO,
                            "Minename" => $arrMinerals[$intKey],
                            "Asearch" => A_SEARCH,
                            "Adig" => A_DIG,
                            "Mineinfo2" => MINE_INFO2));
}

/**
* Search for minerals
*/
if (isset($_GET['step']) && $_GET['step'] == 'search')
{
    $objTest = $db -> Execute("SELECT `days` FROM `mines_search` WHERE `player`=".$player -> id);
    if ($objTest -> fields['days'])
    {
        if ($objTest -> fields['days'] > 1)
        {
            $strDays = SEARCH_DAYS;
        }
            else
        {
            $strDays = SEARCH_DAY;
        }
        error(YOU_SEARCH.$objTest -> fields['days'].$strDays);
    }
    $objTest -> Close();
    $arrMines = array('coal', 'copper', 'zinc', 'tin', 'iron');
    if (!in_array($_GET['mine'], $arrMines))
    {
        error(ERROR);
    }
    $arrMinerals = array('coal' => 0.75,
                         'copper' => 1, 
                         'zinc' => 2, 
                         'tin' => 3, 
                         'iron' => 4);
    $intSmallmith = $arrMinerals[$_GET['mine']];
    if ($intSmallmith < 1)
    {
        $intSmallmith = 0;
    }
    $intMediummith = ceil($arrMinerals[$_GET['mine']] * 2);
    $intLargemith = ceil($arrMinerals[$_GET['mine']] * 3);
    $intSmallgold = $arrMinerals[$_GET['mine']] * 500;
    $intMediumgold = $arrMinerals[$_GET['mine']] * 1000;
    $intLargegold = $arrMinerals[$_GET['mine']] * 1500;
    if (!isset($_GET['type']))
    {
        $_GET['type'] = '';
        $smarty -> assign(array("Smallmith" => $intSmallmith,
                                "Mediummith" => $intMediummith,
                                "Largemith" => $intLargemith,
                                "Smallgold" => $intSmallgold,
                                "Mediumgold" => $intMediumgold,
                                "Largegold" => $intLargegold,
                                "Asearch" => A_SEARCH,
                                "Minerals" => MINERALS,
                                "Goldcoins" => GOLD_COINS,
                                "Mithrilcoins" => MITHRIL_COINS,
                                "Searchdays" => SEARCH_DAYS,
                                "Searchday" => SEARCH_DAY,
                                "Type" => $_GET['type']));
    }
        else
    {
        $arrType = array('small', 'medium', 'large');
        if (!in_array($_GET['type'], $arrType))
        {
            error(ERROR);
        }
        if ($_GET['type'] == 'small')
        {
            $intGoldcost = $intSmallgold;
            $intMithcost = $intSmallmith;
            $intDays = 1;
        }
        if ($_GET['type'] == 'medium')
        {
            $intGoldcost = $intMediumgold;
            $intMithcost = $intMediummith;
            $intDays = 2;
        }
        if ($_GET['type'] == 'large')
        {
            $intGoldcost = $intLargegold;
            $intMithcost = $intLargemith;
            $intDays = 3;
        }
        if ($intGoldcost > $player -> credits)
        {
            error(NO_MONEY);
        }
        if ($intMithcost > $player -> platinum)
        {
            error(NO_MITH);
        }
        $db -> Execute("UPDATE players SET credits=credits-".$intGoldcost.", platinum=platinum-".$intMithcost." WHERE id=".$player -> id);
        $db -> Execute("INSERT INTO mines_search (player, days, mineral, searchdays) VALUES(".$player -> id.", ".$intDays.", '".$_GET['mine']."', ".$intDays.")");
        $smarty -> assign(array("Type" => $_GET['type'],
            "Message" => YOU_START));
    }
}

/**
* Dig for minerals
*/
if (isset($_GET['step']) && $_GET['step'] == 'dig')
{
    $arrMines = array('copper', 'zinc', 'tin', 'iron', 'coal');
    if (!in_array($_GET['mine'], $arrMines))
    {
        error(ERROR);
    }
    if (!$objMines -> fields[$_GET['mine']])
    {
        error(NO_MINERALS);
    }
    if (!isset($_POST['amount']))
      {
	$_POST['amount'] = '';
      }
    $arrMinerals = array(M_COPPER, M_ZINC, M_TIN, M_IRON, M_COAL);
    $intKey = array_search($_GET['mine'], $arrMines);
    $smarty -> assign(array("Yousend" => YOU_SEND,
                            "Minname" => $arrMinerals[$intKey],
                            "Adig" => A_DIG,
                            "Menergy" => M_ENERGY,
			    "Amount" => $_POST['amount'],
                            "Message" => ''));
    if (isset($_GET['dig']) && $_GET['dig'] == 'Y')
    {
        if (!isset($_POST['amount']))
        {
            error(ERROR);
        }
	checkvalue($_POST['amount']);
        if ($_POST['amount'] > $player -> energy)
        {
            error(NO_ENERGY);
        }
        if ($player -> hp < 1)
        {
            error(YOU_DEAD);
        }
        $arrMinerals2 = array(1, 2, 3, 4, 1);

        /**
         * Add bonuses to ability
         */
	$player->curstats(array(), TRUE);
	$player->curskills(array('mining'), TRUE, TRUE);

        $intAmount = ceil(((rand(1, 20) * $_POST['amount'] / $arrMinerals2[$intKey]) * (1 + $player->mining / 20)) - $arrMinerals2[$intKey]);
        if ($intAmount < 1)
        {
            $intAmount = 1;
        }
        $intAbility = $_POST['amount'] / 10;
        if ($intAmount > $objMines -> fields[$_GET['mine']])
        {
            $intAmount = $objMines -> fields[$_GET['mine']];
        }
	$intExp = ($intAmount * $arrMinerals2[$intKey]) / 4;
	if ($player->clas == 'Rzemieślnik')
	  {
	    $intExp = $intExp * 2;
	  }
        $db -> Execute("UPDATE mines SET ".$_GET['mine']."=".$_GET['mine']."-".$intAmount." WHERE owner=".$player -> id);
	require_once('includes/checkexp.php');
	checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'mining', $intAbility);
        $db -> Execute("UPDATE players SET energy=energy-".$_POST['amount'].", bless='', blessval=0 WHERE id=".$player -> id);
        $objTest = $db -> Execute("SELECT owner FROM minerals WHERE owner=".$player -> id);
        $arrOre = array('copperore', 'zincore', 'tinore', 'ironore', 'coal');
        if (!$objTest -> fields['owner'])
        {
            $db -> Execute("INSERT INTO minerals (owner, ".$arrOre[$intKey].") VALUES(".$player -> id.", ".$intAmount.")");
        }
          else
        {
            $db -> Execute("UPDATE minerals SET ".$arrOre[$intKey]."=".$arrOre[$intKey]."+".$intAmount." WHERE owner=".$player -> id);
        }
        $objTest -> Close();
        $smarty -> assign("Message", YOU_DIG.$_POST['amount'].M_ENERGY.YOU_GET.$intAmount.T_AMOUNT.$arrMinerals[$intKey].T_ABILITY.$intAbility.T_ABILITY2." oraz ".$intExp." PD.");
    }
}

/**
* Free memory
*/
if ($objMines -> fields['owner'])
{
    $objMines -> Close();
}

/**
* Initialization of variables
*/
if (!isset($_GET['mine']))
{
    $_GET['mine'] = '';
}
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Mine" => $_GET['mine'],
    "Step" => $_GET['step'],
    "Aback" => A_BACK));
$smarty -> display ('mines.tpl');

require_once("includes/foot.php");
?>
