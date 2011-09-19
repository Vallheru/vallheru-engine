<?php
/**
 *   File functions:
 *   Warehouse - sell minerals and herbs
 *
 *   @name                 : warehouse.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 19.09.2011
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

$title = "Magazyn Królewski";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/warehouse.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

$arrItems = array('copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm', 'mithril', 'illani', 'illanias', 'nutari', 'dynallca', 'illani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');

/**
* Main menu
*/
if (!isset($_GET['action']))
{
    $arrMinname = array(MIN1, MIN2, MIN3, MIN4, MIN5, MIN6, MIN7, MIN8, MIN9, MIN10, MIN11, MIN12, MIN13, MIN14, MIN15, MIN16, MIN17, MIN18);
    $arrHerbname = array(HERB1, HERB2, HERB3, HERB4, HERB5, HERB6, HERB7, HERB8);
    $arrHerb = array(18, 19, 20, 21, 22, 23, 24, 25);
    $arrCostmin = array();
    $arrCostherb = array();
    $arrSellmin = array();
    $arrSellherb = array();
    $arrAmountmin = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    $arrAmountherb = array(0, 0, 0, 0, 0, 0, 0, 0);
    $arrItems2 = array("'copperore'", "'zincore'", "'tinore'", "'ironore'", "'copper'", "'bronze'", "'brass'", "'iron'", "'steel'", "'coal'", "'adamantium'", "'meteor'", "'crystal'", "'pine'", "'hazel'", "'yew'", "'elm'", "'mithril'", "'illani'", "'illanias'", "'nutari'", "'dynallca'", "'illani_seeds'", "'illanias_seeds'", "'nutari_seeds'", "'dynallca_seeds'");
    $objValue = $db->Execute("SELECT `value`, `setting` FROM `settings` WHERE `setting` IN (".implode(',', $arrItems2).")") or die($db->ErrorMsg());
    while(!$objValue->EOF)
      {
	$intKey = array_search($objValue->fields['setting'], $arrItems);
	if ($intKey < 18)
	  {
	    $arrCostmin[$intKey] = $objValue->fields['value'];
	    $arrSellmin[$intKey] = ceil($arrCostmin[$intKey] * 2);
	  }
	else
	  {
	    $arrCostherb[$intKey - 18] = $objValue->fields['value'];
	    $arrSellherb[$intKey - 18] = ceil($arrCostherb[$intKey - 18] * 2);
	  }
	$objValue->MoveNext();
      }
    $objValue->Close();
    $objAmount = $db->Execute("SELECT `amount`, `mineral` FROM `warehouse` WHERE `reset`=1 AND `mineral` IN (".implode(',', $arrItems2).")");
    while (!$objAmount->EOF)
      {
	$intKey = array_search($objAmount->fields['mineral'], $arrItems);
	if ($intKey < 18)
	  {
	    $arrAmountmin[$intKey] = $objAmount -> fields['amount'];
	  }
	else
	  {
	    $arrAmountherb[$intKey - 18] = $objAmount -> fields['amount'];
	  }
	$objAmount->MoveNext();
      }
    $objAmount->Close();
    /**
     * Info about caravan
     */
    $objCaravan = $db -> Execute("SELECT value FROM settings WHERE setting='caravan'");
    if ($objCaravan -> fields['value'] == 'Y')
    {
        $strCaravan = CARAVAN_VISIT;
    }
        else
    {
        $strCaravan = "<br /><br />";
    }
    $smarty -> assign(array("Minname" => $arrMinname,
        "Herbname" => $arrHerbname,
        "Costmin" => $arrCostmin,
        "Costherb" => $arrCostherb,
        "Herb" => $arrHerb,
        "Sellmin" => $arrSellmin,
        "Sellherb" => $arrSellherb,
        "Amountmin" => $arrAmountmin,
        "Amountherb" => $arrAmountherb,
        "Caravaninfo" => $strCaravan,
        "Tmin" => T_MIN,
        "Therb" => T_HERB,
        "Tcost" => T_COST,
        "Taction" => T_ACTION,
        "Asell" => A_SELL,
        "Abuy" => A_BUY,
        "Tamount" => T_AMOUNT));
}

/**
* Sell or buy herbs and minerals
*/
if (isset($_GET['action']) && ($_GET['action'] == 'sell' || $_GET['action'] == 'buy'))
  {
    $_GET['item'] = intval($_GET['item']);
    if ($_GET['item'] < 0 || $_GET['item'] > 25)
    {
        error(ERROR);
    }
    $intItem = $_GET['item'];
    $arrItemname = array(MIN1, MIN2, MIN3, MIN4, MIN5, MIN6, MIN7, MIN8, MIN9, MIN10, MIN11, MIN12, MIN13, MIN14, MIN15, MIN16, MIN17, MIN18, HERB1, HERB2, HERB3, HERB4, HERB5, HERB6, HERB7, HERB8);
    if ($_GET['action'] == 'sell')
    {
        if ($intItem == 17)
        {
            $intAmount = $player -> platinum;
        }
        if ($intItem < 17)
        {
            $objTest = $db -> Execute("SELECT `".$arrItems[$intItem]."` FROM `minerals` WHERE `owner`=".$player -> id);
            $intAmount = $objTest -> fields[$arrItems[$intItem]];
        }
        if ($intItem > 17)
        {
            if ($arrItems[$intItem] == 'illani_seeds')
            {
                $strHerb = 'ilani_seeds';
            }
            else
            {
                $strHerb = $arrItems[$intItem];
            }
            $objTest = $db -> Execute("SELECT `".$strHerb."` FROM `herbs` WHERE `gracz`=".$player -> id);
            $intAmount = $objTest -> fields[$strHerb];
        }
        $smarty -> assign(array("Asell" => A_SELL,
            "Youhave" => YOU_HAVE));
    }
        else
    {
        $objAmount = $db -> Execute("SELECT amount FROM warehouse WHERE reset=1 AND mineral='".$arrItems[$intItem]."'");
        if ($objAmount -> fields['amount'])
        {
            $intAmount = $objAmount -> fields['amount'];
        }
            else
        {
            $intAmount = 0;
        }
        $objAmount -> Close();
        $smarty -> assign(array("Abuy" => A_BUY,
            "Wamount" => W_AMOUNT));
    }
    $smarty -> assign(array("Itemname" => $arrItemname[$intItem],
        "Item" => $_GET['item'],
        "Aback" => A_BACK,
        "Tamount" => AMOUNT,
        "Iamount" => $intAmount));
    if (isset($_GET['action2']) && ($_GET['action2'] == 'sell' || $_GET['action2'] == 'buy'))
    {
        if (!isset($_POST['amount']))
        {
            error(ERROR);
        }
	checkvalue($_POST['amount']);
        if ($_POST['amount'] > $intAmount)
        {
            error(NO_AMOUNT.$arrItemname[$intItem]);
        }
        $objPrice = $db -> Execute("SELECT value FROM settings WHERE setting='".$arrItems[$intItem]."'");
        $objReset = $db -> Execute("SELECT reset FROM warehouse WHERE reset=1 AND mineral='".$arrItems[$intItem]."'");
        if ($_GET['action2'] == 'sell')
        {
            $intGold = ($objPrice -> fields['value'] * $_POST['amount']);
            $db -> Execute("UPDATE players SET credits=credits+".$intGold." WHERE id=".$player -> id);
            if ($objReset -> fields['reset'])
            {
                $db -> Execute("UPDATE warehouse SET sell=sell+".$_POST['amount'].", amount=amount+".$_POST['amount']." WHERE reset=1 AND mineral='".$arrItems[$intItem]."'");
            }
        }
            else
        {
            $intGold = ($objPrice -> fields['value'] * $_POST['amount']) * 2;
            if ($intGold > $player -> credits)
            {
                error(NO_MONEY);
            }
            $db -> Execute("UPDATE players SET credits=credits-".$intGold." WHERE id=".$player -> id);
            if ($objReset -> fields['reset'])
            {
                $db -> Execute("UPDATE warehouse SET buy=buy+".$_POST['amount'].", amount=amount-".$_POST['amount']." WHERE reset=1 AND mineral='".$arrItems[$intItem]."'");
            }
        }
        $objReset -> Close();
        $objPrice -> Close();
        if ($intItem == 17)
        {
            if ($_GET['action2'] == 'sell')
            {
                $db -> Execute("UPDATE players SET platinum=platinum-".$_POST['amount']." WHERE id=".$player -> id);
            }
                else
            {
                $db -> Execute("UPDATE players SET platinum=platinum+".$_POST['amount']." WHERE id=".$player -> id);
            }
        }
        if ($intItem < 17)
        {
            if ($_GET['action2'] == 'sell')
            {
                $db -> Execute("UPDATE minerals SET ".$arrItems[$intItem]."=".$arrItems[$intItem]."-".$_POST['amount']." WHERE owner=".$player -> id);
            }
                else
            {
                $objTest = $db -> Execute("SELECT owner FROM minerals WHERE owner=".$player -> id);
                if (!$objTest -> fields['owner']) 
                {
                    $db -> Execute("INSERT INTO minerals (owner, ".$arrItems[$intItem].") VALUES(".$player -> id.",".$_POST['amount'].")");
                } 
                    else 
                {
                    $db -> Execute("UPDATE minerals SET ".$arrItems[$intItem]."=".$arrItems[$intItem]."+".$_POST['amount']." WHERE owner=".$player -> id);
                }
                $objTest -> Close();
            }
        }
        if ($intItem > 17)
        {
            if ($arrItems[$intItem] == 'illani_seeds')
            {
                $strHerb = 'ilani_seeds';
            }
                else
            {
                $strHerb = $arrItems[$intItem];
            }
            if ($_GET['action2'] == 'sell')
            {
                $db -> Execute("UPDATE herbs SET ".$strHerb."=".$strHerb."-".$_POST['amount']." WHERE gracz=".$player -> id);
            }
                else
            {
                $objTest = $db -> Execute("SELECT gracz FROM herbs WHERE gracz=".$player -> id);
                if (!$objTest -> fields['gracz']) 
                {
                    $db -> Execute("INSERT INTO herbs (gracz, ".$strHerb.") VALUES(".$player -> id.",".$_POST['amount'].")");
                } 
                    else 
                {
                    $db -> Execute("UPDATE herbs SET ".$strHerb."=".$strHerb."+".$_POST['amount']." WHERE gracz=".$player -> id);
                }
                $objTest -> Close();
            }
        }
        if ($_GET['action2'] == 'sell')
        {
            error(YOU_SELL.$_POST['amount'].AMOUNT.$arrItemname[$intItem].FOR_A.$intGold.GOLD_COINS);
        }
            else
        {
            error(YOU_BUY.$_POST['amount'].AMOUNT.$arrItemname[$intItem].FOR_A.$intGold.GOLD_COINS);
        }
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'],
    "Warehouseinfo" => WAREHOUSE_INFO,
    "Warehouseinfo2" => WAREHOUSE_INFO2,
    "Warehouseinfo3" => WAREHOUSE_INFO3));
$smarty -> display('warehouse.tpl');

require_once("includes/foot.php");
?>
