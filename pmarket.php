<?php
/**
 *   Funkcje pliku:
 *   Minerals market
 *
 *   @name                 : pmarket.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 20.08.2011
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

$title = "Rynek minerałów";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/pmarket.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

$objMinerals = $db -> Execute("SELECT `owner`, `copperore`, `zincore`, `tinore`, `ironore`, `coal`, `copper`, `bronze`, `brass`, `iron`, `steel`, `pine`, `hazel`, `yew`, `elm`, `crystal`, `adamantium`, `meteor` FROM `minerals` WHERE `owner`=".$player -> id);
$arrMinerals = array(MIN1, MIN15, MIN16, MIN17, MIN18, MIN2, MIN9, MIN10, MIN3, MIN11, MIN4, MIN5, MIN6, MIN7, MIN8, MIN12, MIN13, MIN14);

/**
* Assign variables to template
*/
$smarty -> assign(array("Message" => '', 
                        "Previous" => '', 
                        "Next" => ''));

/**
 * Main menu
 */
if (!isset($_GET['view']) && !isset($_GET['buy']) && !isset($_GET['wyc']))
{
    $smarty -> assign(array("Minfo" => M_INFO,
                            "Aview" => A_VIEW,
                            "Asearch" => A_SEARCH,
                            "Adelete" => A_DELETE,
                            "Aadd" => A_ADD,
                            "Alist" => A_LIST,
                            "Aback2" => A_BACK2));
}

/**
 * List of offerts
 */
if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    if (empty($_POST['szukany'])) 
    {
        $msel = $db -> Execute("SELECT id FROM pmarket");
        $_POST['szukany'] = '';
    } 
        else 
    {
        $_POST['szukany'] = strip_tags($_POST['szukany']);
        $_POST['szukany'] = str_replace("*","%", $_POST['szukany']);
        $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
	for ($i = 0; $i < count($arrMinerals); $i++)
	  {
	    if (stripos($arrMinerals[$i], $_POST['szukany']) !== FALSE)
	      {
		$strSearch = $arrMinerals[$i];
		$strSearch = $db -> qstr($strSearch, get_magic_quotes_gpc());
		break;
	      }
	  }
        $msel = $db -> Execute("SELECT id FROM pmarket WHERE nazwa LIKE ".$strSearch);
    }
    $oferty = $msel -> RecordCount();
    $msel -> Close();
    if ($oferty == 0) 
    {
        error (NO_OFERTS);
    }
    if (!isset($_GET['limit'])) 
    {
        $_GET['limit'] = 0;
    }
    if (!isset($_GET['lista'])) 
    {
        $_GET['lista'] = 'id';
    }
    $smarty -> assign(array("Mineral" => MINERAL,
                            "Viewinfo" => VIEW_INFO,
                            "Tamount" => T_AMOUNT,
                            "Tcost" => T_COST,
                            "Tseller" => T_SELLER,
			    "Asearch" => A_SEARCH,
                            "Toptions" => T_OPTIONS));
    if ($_GET['limit'] < $oferty) 
      {
	$_GET['limit'] = intval($_GET['limit']);
        if (!in_array($_GET['lista'], array('nazwa', 'ilosc', 'cost', 'seller', 'id'))) 
	  {
	    error(ERROR);  
	  }
        if (empty($_POST['szukany'])) 
        {
            $pm = $db -> SelectLimit("SELECT * FROM pmarket ORDER BY ".$_GET['lista']." DESC", 30, $_GET['limit']);
        } 
            else 
        {
            $pm = $db -> SelectLimit("SELECT * FROM pmarket WHERE nazwa LIKE ".$strSearch." ORDER BY ".$_GET['lista']." DESC", 30, $_GET['limit']);
        }
        $arrname = array();
        $arramount = array();
        $arrcost = array();
        $arrseller = array();
        $arruser = array();
	$arrId = array();
        $i = 0;
        while (!$pm -> EOF) 
        {
            $arrname[$i] = $pm -> fields['nazwa'];
            $arramount[$i] = $pm -> fields['ilosc'];
            $arrcost[$i] = $pm -> fields['cost'];
            $arrseller[$i] = $pm -> fields['seller'];
            $seller = $db -> Execute("SELECT user FROM players WHERE id=".$pm -> fields['seller']);
            $arruser[$i] = $seller -> fields['user'];
	    $arrId[$i] = $pm->fields['id'];
            $seller -> Close();
            $pm -> MoveNext();
            $i = $i + 1;
        }
        $pm -> Close();
        $smarty -> assign(array("Name" => $arrname, 
				"Amount" => $arramount, 
				"Cost" => $arrcost, 
				"Seller" => $arrseller,
				"Iid" => $arrId,
				"Pid" => $player->id,
				"Abuy" => A_BUY,
				"Aadd" => A_ADD,
				"Adelete" => A_DELETE,
				"Achange" => A_CHANGE,
				"User" => $arruser));
        if ($_GET['limit'] >= 30) 
        {
            $lim = $_GET['limit'] - 30;
            $smarty -> assign ("Previous", "<form method=\"post\" action=\"pmarket.php?view=market&limit=".$lim."&lista=".$_GET['lista']."\"><input type=\"hidden\" name=\"szukany\" value=\"".$_POST['szukany']."\"><input type=\"submit\" value=\"".A_PREVIOUS."\"></form> ");
        }
        $_GET['limit'] = $_GET['limit'] + 30;
        if ($oferty > 30 && $_GET['limit'] < $oferty) 
        {
            $smarty -> assign ("Next", " <form method=\"post\" action=\"pmarket.php?view=market&limit=".$_GET['limit']."&lista=".$_GET['lista']."\"><input type=\"hidden\" name=\"szukany\" value=\"".$_POST['szukany']."\"><input type=\"submit\" value=\"".A_NEXT."\"></form>");
        }
    }
}

/**
 * Add ofert to market
 */
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    $arrAmount = array($player -> platinum, $objMinerals -> fields['copperore'], $objMinerals -> fields['zincore'], $objMinerals -> fields['tinore'], $objMinerals -> fields['ironore'], $objMinerals -> fields['copper'], $objMinerals -> fields['bronze'], $objMinerals -> fields['brass'], $objMinerals -> fields['iron'], $objMinerals -> fields['steel'], $objMinerals -> fields['coal'], $objMinerals -> fields['adamantium'], $objMinerals -> fields['meteor'], $objMinerals -> fields['crystal'], $objMinerals -> fields['pine'], $objMinerals -> fields['hazel'], $objMinerals -> fields['yew'], $objMinerals -> fields['elm']);
    $smarty -> assign(array("Addinfo" => ADD_INFO,
                            "Mamount" => M_AMOUNT,
                            "Mcost" => M_COST,
                            "Aadd" => A_ADD,
                            "Minerals" => $arrMinerals,
                            "Mineralsamount" => $arrAmount,
                            "Mineral" => MINERAL,
                            "Tamount" => T_AMOUNT,
                            "Addofert" => 0));
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!isset($_POST['mineral']) || !ereg("^[0-9]*$", $_POST['mineral'])) 
        {
            error(ERROR);
        }
        $arrSqlname = array('', 'copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
        $arrName = array(MITH, COPPERORE, ZINCORE, TINORE, IRONORE, COPPER, BRONZE, BRASS, IRON, STEEL, COAL, ADAMANTIUM, METEOR, CRYSTAL, PINE, HAZEL, YEW, ELM);
        $strMineral = $arrSqlname[$_POST['mineral']];
        $strName = $arrName[$_POST['mineral']];
        $intAmount = $arrAmount[$_POST['mineral']];
        $strSqlname = $arrMinerals[$_POST['mineral']];
        if (!isset($_POST['amount']))
        {
            error(ERROR);
        }
        if ($_POST['amount'] > $intAmount)
        {
            error(NO_AMOUNT.$strName);
        }
	checkvalue($_POST['amount']);
	checkvalue($_POST['cost']);
        $objTest = $db -> Execute("SELECT `id` FROM `pmarket` WHERE `seller`=".$player -> id." AND `nazwa`='".$strSqlname."'");
        if (!$objTest -> fields['id'])
        {
            if ($_POST['mineral'] == 0) 
            {
                $db -> Execute("UPDATE players SET platinum=platinum-".$_POST['amount']." WHERE id=".$player -> id);
            } 
                else 
            {
                $db -> Execute("UPDATE minerals SET ".$strMineral."=".$strMineral."-".$_POST['amount']." WHERE owner=".$player -> id);
            }
            $db -> Execute("INSERT INTO pmarket (seller, ilosc, cost, nazwa, lang) VALUES(".$player -> id.",".$_POST['amount'].",".$_POST['cost'].",'".$strSqlname."', '".$player -> lang."')");
            $smarty -> assign("Message", YOU_ADD.$_POST['amount']."</b> ".$strName.ON_MARKET.$_POST['cost'].FOR_GOLDS." <a href=\"pmarket.php?view=add\">".A_REFRESH."</a>");
        }
            else
        {
            $smarty -> assign(array("Addofert" => $objTest -> fields['id'],
                                    "Youwant" => YOU_WANT,
                                    "Ayes" => YES,
                                    "Mineralname" => $_POST['mineral'],
                                    "Mineralamount" => $_POST['amount'],
                                    "Mineralcost" => $_POST['cost']));
            if (isset($_POST['ofert']))
            {
		checkvalue($_POST['ofert']);
                require_once('includes/marketaddto.php');
                addtomin($_POST['ofert'], $strMineral, $_POST['mineral'], $player -> id);
                $smarty -> assign("Message", YOU_ADD.$_POST['amount']."</b> ".$strName.ON_MARKET2." <a href=\"pmarket.php?view=add\">".A_REFRESH."</a>");
            }
        }
        $objTest -> Close();
    }
}

/**
 * Delete all oferts from market
 */
if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    require_once('includes/marketdelall.php');
    deleteallmin($player -> id, $arrMinerals);
    $smarty -> assign("Message", YOU_DELETE." (<a href=\"pmarket.php\">".A_BACK."</a>)");
}

/**
 * Buy minerals from market
 */
if (isset($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $buy = $db -> Execute("SELECT * FROM pmarket WHERE id=".$_GET['buy']);
    if (!$buy -> fields['id']) 
    {
        error (NO_OFERTS);
    }
    if ($buy -> fields['seller'] == $player -> id) 
    {
        error (ITS_YOUR);
    }
    $seller = $db -> Execute("SELECT user FROM players WHERE id=".$buy -> fields['seller']);
    $smarty -> assign(array("Name" => $buy -> fields['nazwa'], 
        "Amount1" => $buy -> fields['ilosc'], 
        "Itemid" => $buy -> fields['id'], 
        "Cost" => $buy -> fields['cost'], 
        "Seller" => $seller -> fields['user'],
        "Sellerid" => $buy -> fields['seller'],
        "Buyinfo" => BUY_INFO,
        "Oamount" => O_AMOUNT,
        "Mcost" => M_COST,
        "Mseller" => M_SELLER,
        "Bamount" => B_AMOUNT,
        "Abuy" => A_BUY,
        "Mineral" => MINERAL));
    $buy -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
        if (!isset($_POST['amount']))
        {
            error(ERROR);
        }
	checkvalue($_POST['amount']);
        $buy = $db -> Execute("SELECT * FROM pmarket WHERE id=".$_GET['buy']);
        $price = $_POST['amount'] * $buy -> fields['cost'];
        if ($price > $player -> credits) 
        {
            error (NO_MONEY);
        }
        if ($_POST['amount'] > $buy -> fields['ilosc']) 
        {
            error(NO_AMOUNT.$buy -> fields['nazwa'].ON_MARKET);
        }
        $arrSqlname = array('', 'copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
        $intKey = array_search($buy -> fields['nazwa'], $arrMinerals);
        $strMineral = $arrSqlname[$intKey];
        $db -> Execute("UPDATE players SET bank=bank+".$price." WHERE id=".$buy -> fields['seller']);
        $db -> Execute("UPDATE players SET credits=credits-".$price." WHERE id=".$player -> id);
        if ($buy -> fields['nazwa'] == 'Mithril') 
        {
            $db -> Execute("UPDATE players SET platinum=platinum+".$_POST['amount']." where id=".$player -> id);
        } 
            else 
        {
            if (!$objMinerals -> fields['owner']) 
            {
                $db -> Execute("INSERT INTO minerals (owner, ".$strMineral.") VALUES(".$player -> id.",".$_POST['amount'].")");
            } 
                else 
            {
                $db -> Execute("UPDATE minerals SET ".$strMineral."=".$strMineral."+".$_POST['amount']." WHERE owner=".$player -> id);
            }
        }
        if ($_POST['amount'] == $buy -> fields['ilosc']) 
        {
            $db -> Execute("DELETE FROM pmarket WHERE id=".$buy -> fields['id']);
        } 
            else 
        {
            $db -> Execute("UPDATE pmarket set ilosc=ilosc-".$_POST['amount']." WHERE id=".$buy -> fields['id']);
        }
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$buy -> fields['seller'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['nazwa'].YOU_GET.$price.TO_BANK."', ".$strDate.")");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$buy -> fields['nazwa'].FOR_A.$price.GOLD_COINS);
    }
}

/**
 * Delete selected offert from market
 */
if (isset($_GET['wyc'])) 
{
    checkvalue($_GET['wyc']);
    $dwyc = $db -> Execute("SELECT * FROM pmarket WHERE id=".$_GET['wyc']);
    if ($dwyc -> fields['seller'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    require_once('includes/marketdel.php');
    deletemin($_GET['wyc'], $dwyc -> fields['nazwa'], $dwyc -> fields['ilosc'], $player -> id, $arrMinerals);
    $smarty -> assign("Message", YOU_DELETE." (<a href=\"pmarket.php\">".A_BACK."</a>)");
}

/**
* List of all oferts on market
*/
if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    $oferts = $db -> Execute("SELECT nazwa FROM pmarket GROUP BY nazwa");
    $arrname = array();
    $arramount = array();
    $i = 0;
    while (!$oferts -> EOF) 
    {
        $arrname[$i] = $oferts -> fields['nazwa'];
        $arramount[$i] = 0;
        $query = $db -> Execute("SELECT id FROM pmarket WHERE nazwa='".$arrname[$i]."'");
        while (!$query -> EOF) 
        {
            $arramount[$i] = $arramount[$i] + 1;
            $query -> MoveNext();
        }
        $query -> Close();
        $oferts -> MoveNext();
        $i = $i + 1;
    }
    $oferts -> Close();
    $smarty -> assign(array("Name" => $arrname, 
        "Amount" => $arramount, 
        "Message" => "<br />(<a href=\"pmarket.php\">".A_BACK."</a>)",
        "Listinfo" => LIST_INFO,
        "Mname" => M_NAME,
        "Mamount" => M_AMOUNT,
        "Maction" => M_ACTION,
        "Ashow" => A_SHOW));
}

/**
* Initialization of variables
*/
if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
}
if (!isset($_GET['wyc'])) 
{
    $_GET['wyc'] = '';
}
if (!isset($_GET['buy'])) 
{
    $_GET['buy'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("View" => $_GET['view'], 
    "Delete" => $_GET['wyc'], 
    "Buy" => $_GET['buy'],
    "Aback" => A_BACK));
$smarty -> display('pmarket.tpl');

require_once("includes/foot.php");
?>
