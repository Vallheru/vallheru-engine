<?php
/**
 *   File functions:
 *   Herbs market - add, buy herbs from other players
 *
 *   @name                 : hmarket.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 17.08.2011
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

$title = "Rynek ziół";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/hmarket.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}
$gr = $db -> Execute("SELECT `id`, `illani`, `illanias`, `nutari`, `dynallca`, `ilani_seeds`, `illanias_seeds`, `nutari_seeds`, `dynallca_seeds` FROM `herbs` WHERE `gracz`=".$player -> id) or die($db -> ErrorMsg());
$arrName = array(N_HERB1, N_HERB2, N_HERB3, N_HERB4, N_HERB5, N_HERB6, N_HERB7, N_HERB8);

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
                            "Aadd" => A_ADD,
                            "Adelete" => A_DELETE,
                            "Alist" => A_LIST,
                            "Aback2" => A_BACK2));
}

/**
* Search herbs on market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'szukaj') 
{
    $smarty -> assign(array("Sinfo" => S_INFO,
                            "Sinfo2" => S_INFO2,
                            "Herb" => HERB,
                            "Asearch" => A_SEARCH));
}

/**
* View oferts on market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    if (empty($_POST['szukany'])) 
    {
        $msel = $db -> Execute("SELECT id FROM hmarket");
        $strSearch = '';
    } 
        else 
    {
        $_POST['szukany'] = strip_tags($_POST['szukany']);
        $_POST['szukany'] = str_replace("*","%", $_POST['szukany']);
        $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
        $msel = $db -> Execute("SELECT id FROM hmarket WHERE nazwa LIKE ".$strSearch);
    }
    $oferty = $msel -> RecordCount();
    $msel -> Close();
    if (!isset($_GET['limit'])) 
    {
        $_GET['limit'] = 0;
    }
    if (!isset($_GET['lista'])) 
    {
        $_GET['lista'] = 0;
    }
    if ($oferty == 0) 
    {
        error(NO_OFERTS);
    }
    if ($_GET['limit'] < $oferty) 
      {
	$_GET['limit'] = intval($_GET['limit']);
        if (!in_array($_GET['lista'], array('id', 'nazwa', 'ilosc', 'cost', 'seller'))) 
	  {
	    error(ERROR);
	  }
        if (empty($_POST['szukany'])) 
        {
            $pm = $db -> SelectLimit("SELECT * FROM hmarket ORDER BY ".$_GET['lista']." DESC", 30, $_GET['limit']);
        } 
            else 
        {
            $pm = $db -> SelectLimit("SELECT * FROM hmarket WHERE nazwa LIKE ".$strSearch." ORDER BY ".$_GET['lista']." DESC", 30, $_GET['limit']);
        }
        $arrname = array();
        $arramount = array();
        $arrcost = array();
        $arrseller = array();
        $arraction = array();
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
            $seller -> Close();
            $arrId[$i] = $pm->fields['id'];
            $pm -> MoveNext();
            $i = $i + 1;
        }
        $pm -> Close();
        $smarty -> assign(array("Name" => $arrname, 
                                "Amount" => $arramount, 
                                "Cost" => $arrcost, 
                                "Seller" => $arrseller, 
                                "Action" => $arraction, 
                                "User" => $arruser,
                                "Therb" => HERB,
                                "Tamount" => T_AMOUNT,
                                "Tcost" => T_COST,
                                "Tseller" => T_SELLER,
                                "Toptions" => T_OPTIONS,
				"Iid" => $arrId,
				"Pid" => $player->id,
				"Abuy" => A_BUY,
				"Aadd" => A_ADD,
				"Adelete" => A_DELETE,
				"Achange" => A_CHANGE,
                                "Viewinfo" => VIEW_INFO));
        if (!isset($_POST['szukany']))
        {
            $_POST['szukany'] = '';
        }
        if ($_GET['limit'] >= 30) 
        {
            $lim = $_GET['limit'] - 30;
            $smarty -> assign ("Previous", "<form method=\"post\" action=\"hmarket.php?view=market&limit=".$lim."&lista=".$_GET['lista']."\"><input type=\"hidden\" name=\"szukany\" value=\"".$_POST['szukany']."\"><input type=\"submit\" value=\"".A_PREVIOUS."\"></form> ");
        }
        $_GET['limit'] = $_GET['limit'] + 30;
        if ($oferty > 30 && $_GET['limit'] < $oferty) 
        {
            $smarty -> assign ("Next", " <form method=\"post\" action=\"hmarket.php?view=market&limit=".$_GET['limit']."&lista=".$_GET['lista']."\"><input type=\"hidden\" name=\"szukany\" value=\"".$_POST['szukany']."\"><input type=\"submit\" value=\"".A_NEXT."\"></form>");
        }
    }
}

/**
* Add ofert on market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    $arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
    $arrAmount = array($gr -> fields['illani'], $gr -> fields['illanias'], $gr -> fields['nutari'], $gr -> fields['dynallca'], $gr -> fields['ilani_seeds'], $gr -> fields['illanias_seeds'], $gr -> fields['nutari_seeds'], $gr -> fields['dynallca_seeds']);
    $smarty -> assign(array("Addinfo" => ADD_INFO,
                            "Herb" => HERB,
                            "Herbname" => $arrName,
                            "Sqlname" => $arrSqlname,
                            "Hamount" => H_AMOUNT,
                            "Hcost" => H_COST,
                            "Aadd" => A_ADD,
                            "Herbamount" => $arrAmount,
                            "Tamount" => T_AMOUNT,
                            "Addofert" => 0));
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!isset($_POST['ilosc']) || !isset($_POST['cost'])) 
        {
            error (ERROR);
        }
	checkvalue($_POST['ilosc']);
	checkvalue($_POST['cost']);
        if (!in_array($_POST['mineral'], $arrSqlname))
        {
            error(ERROR);
        }
        $intKey = array_search($_POST['mineral'], $arrSqlname);
        if ($_POST['ilosc'] > $gr -> fields[$arrSqlname[$intKey]])
        {
            error(NO_AMOUNT.$arrName[$intKey]);
        }
        $objTest = $db -> Execute("SELECT `id` FROM `hmarket` WHERE `seller`=".$player -> id." AND `nazwa`='".$arrName[$intKey]."'");
        if (!$objTest -> fields['id'])
        {
            $db -> Execute("INSERT INTO `hmarket` (`seller`, `ilosc`, `cost`, `nazwa`, `lang`) VALUES(".$player -> id.",".$_POST['ilosc'].",".$_POST['cost'].",'".$arrName[$intKey]."', '".$player -> lang."')") or die($db -> ErrorMsg());
            $db -> Execute("UPDATE `herbs` SET `".$_POST['mineral']."`=`".$_POST['mineral']."`-".$_POST['ilosc']." WHERE `gracz`=".$player -> id);
            $smarty -> assign("Message", YOU_ADD.$_POST['ilosc']."</b> ".$arrName[$intKey].ON_MARKET.$_POST['cost'].FOR_GOLDS." <a href=\"hmarket.php?view=add\">".A_REFRESH."</a>");
        }
            else
        {
            $smarty -> assign(array("Addofert" => $objTest -> fields['id'],
                                    "Youwant" => YOU_WANT,
                                    "Ayes" => YES,
                                    "Herbname" => $_POST['mineral'],
                                    "Herbamount" => $_POST['ilosc'],
                                    "Herbcost" => $_POST['cost']));
            if (isset($_POST['ofert']))
            {
		checkvalue($_POST['ofert']);
                require_once('includes/marketaddto.php');
                addtoherb($_POST['ofert'], $_POST['mineral'], $player -> id, $_POST['ilosc']);
                $smarty -> assign("Message", YOU_ADD.$_POST['ilosc']."</b> ".$arrName[$intKey].ON_MARKET2." <a href=\"hmarket.php?view=add\">".A_REFRESH."</a>");
            }
        }
        $objTest -> Close();
    }
}

/**
* Delete all oferts one player from market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    require_once('includes/marketdelall.php');
    deleteallherb($player -> id, $arrName);
    $smarty -> assign("Message", YOU_DELETE." (<A href=hmarket.php>".A_BACK."</a>)");
}

/**
* Buy herbs from market
*/
if (isset($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $buy = $db -> Execute("SELECT * FROM hmarket WHERE id=".$_GET['buy']) ;
    if (!$buy -> fields['id']) 
    {
        error (NO_OFERTS);
    }
    if ($buy -> fields['seller'] == $player -> id) 
    {
        error (IS_YOUR);
    }
    $seller = $db -> Execute("SELECT user FROM players WHERE id=".$buy -> fields['seller']);
    $smarty -> assign(array("Name" => $buy -> fields['nazwa'], 
                            "Amount1" => $buy -> fields['ilosc'], 
                            "Itemid" => $buy -> fields['id'], 
                            "Cost" => $buy -> fields['cost'], 
                            "Seller" => $seller -> fields['user'], 
                            "Sid" => $buy -> fields['seller'],
                            "Buyinfo" => BUY_INFO,
                            "Bherb" => HERB,
                            "Oamount" => O_AMOUNT,
                            "Hcost" => H_COST,
                            "Hseller" => SELLER,
                            "Bamount" => B_AMOUNT,
                            "Abuy" => A_BUY));
    $buy -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
	checkvalue($_POST['amount']);
        $buy = $db -> Execute("SELECT * FROM hmarket WHERE id=".$_GET['buy']);
        $price = $_POST['amount'] * $buy -> fields['cost'];
        if ($price > $player -> credits) 
        {
            error (NO_MONEY);
        }
        if ($_POST['amount'] > $buy -> fields['ilosc']) 
        {
            error(NO_AMOUNT.$buy -> fields['nazwa'].ON_MARKET);
        }
        $db -> Execute("UPDATE players SET bank=bank+".$price." WHERE id=".$buy -> fields['seller']);
        $db -> Execute("UPDATE players SET credits=credits-".$price." WHERE id=".$player -> id);
        $arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
        $intKey = array_search($buy -> fields['nazwa'], $arrName);
        if (!$gr -> fields['id']) 
        {
            $db -> Execute("INSERT INTO herbs (gracz, ".$arrSqlname[$intKey].") VALUES(".$player -> id.",".$_POST['amount'].")");
        } 
            else 
        {
            $db -> Execute("UPDATE herbs SET ".$arrSqlname[$intKey]."=".$arrSqlname[$intKey]."+".$_POST['amount']." WHERE gracz=".$player -> id);
        }
        if ($_POST['amount'] == $buy -> fields['ilosc']) 
        {
            $db -> Execute("DELETE FROM hmarket WHERE id=".$buy -> fields['id']);
        } 
            else 
        {
            $db -> Execute("UPDATE hmarket SET ilosc=ilosc-".$_POST['amount']." WHERE id=".$buy -> fields['id']);
        }
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$buy -> fields['seller'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['nazwa'].YOU_GET.$price.TO_BANK."', ".$strDate.")");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$buy -> fields['nazwa'].FOR_A.$price.GOLD_COINS);
    }
}

if (isset($_GET['wyc'])) 
{
    checkvalue($_GET['wyc']);
    $dwyc = $db -> Execute("SELECT * FROM hmarket WHERE id=".$_GET['wyc']);
    if ($dwyc -> fields['seller'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    require_once('includes/marketdel.php');
    deleteherb($_GET['wyc'], $dwyc -> fields['nazwa'], $dwyc -> fields['ilosc'], $player -> id, $arrName);
    $smarty -> assign("Message", YOU_DELETE." (<A href=hmarket.php>".A_BACK."</a>)");
}

/**
* List of all ofers on market
*/
if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    $oferts = $db -> Execute("SELECT nazwa FROM hmarket GROUP BY nazwa");
    $arrname = array();
    $arramount = array();
    $i = 0;
    while (!$oferts -> EOF) 
    {
        $arrname[$i] = $oferts -> fields['nazwa'];
        $arramount[$i] = 0;
        $query = $db -> Execute("SELECT id FROM hmarket WHERE nazwa='".$arrname[$i]."'");
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
        "Message" => "<br />(<a href=\"hmarket.php\">".A_BACK."</a>)",
        "Listinfo" => LIST_INFO,
        "Hname" => H_NAME,
        "Hamount" => H_AMOUNT,
        "Haction" => H_ACTION,
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
    "Remowe" => $_GET['wyc'], 
    "Buy" => $_GET['buy'],
    "Aback" => A_BACK));
$smarty -> display ('hmarket.tpl');

require_once("includes/foot.php");
?>
