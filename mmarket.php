<?php
/**
 *   File functions:
 *   Potions market
 *
 *   @name                : mmarket.php
 *   @copyright           : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author              : thindil <thindil@vallheru.net>
 *   @author              : eyescream <tduda@users.sourceforge.net>
 *   @version             : 1.6
 *   @since               : 24.05.2012
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

$title = "Rynek z miksturami";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/mmarket.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Assign variables to template
*/
$smarty -> assign("Message", '');

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

if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    if (isset($_GET['search']))
      {
	$_POST['szukany'] = $_GET['search'];
      }
    if (empty($_POST['szukany']) && !isset($_POST['szukany1'])) 
      {
        $msel = $db -> Execute("SELECT count(`id`) FROM `potions` WHERE `status`='R'");
        $_POST['szukany'] = '';
      } 
    elseif (isset($_POST['szukany1']))
      {
	$_POST['szukany1'] = strip_tags($_POST['szukany1']);
        $strSearch = $db -> qstr($_POST['szukany1'], get_magic_quotes_gpc());
	$msel = $db -> Execute("SELECT count(`id`) FROM `potions` WHERE `status`='R' AND name=".$strSearch);
      }
    else 
      {
        $_POST['szukany'] = strip_tags($_POST['szukany']);
        $strSearch = $db -> qstr("*".$_POST['szukany']."*", get_magic_quotes_gpc());
        $msel = $db -> Execute("SELECT count(`id`) FROM `potions` WHERE `status`='R' AND MATCH(`name`) AGAINST (".$strSearch." IN BOOLEAN MODE)");
      }
    $przed = $msel -> fields['count(`id`)'];
    $msel->Close();
    if ($przed == 0) 
    {
        error (NO_OFERTS);
    }
    $pages = ceil($przed / 30);
    if (isset($_GET['page']))
      {
	checkvalue($_GET['page']);
	$page = $_GET['page'];
      }
    else
      {
	$page = 1;
      }
    if ($page > $pages)
      {
	$page = $pages;
      }
    $smarty -> assign(array("Tname" => T_NAME,
			    "Tefect" => T_EFECT,
			    "Tamount" => T_AMOUNT,
			    "Tcost" => "Cena szt / wszystko",
			    "Tseller" => T_SELLER,
			    "Toptions" => T_OPTIONS,
			    "Asearch" => A_SEARCH,
			    "Viewinfo" => VIEW_INFO,
			    "Aadd2" => "Dodaj ofertę"));
    if (!in_array($_GET['lista'], array('id', 'name', 'efect', 'amount', 'cost', 'owner'))) 
      {
	error(ERROR);
      }
    if (!isset($_GET['order']))
      {
	$_GET['order'] = 'ASC';
      }
    elseif ($_GET['order'] != 'DESC' && $_GET['order'] != 'ASC')
      {
	error(ERROR);
      }
    if ($_GET['order'] == 'DESC')
      {
	$strOrder = 'ASC';
      }
    else
      {
	$strOrder = 'DESC';
      }
    if (empty($_POST['szukany']) && !isset($_POST['szukany1'])) 
      {
	$pm = $db -> SelectLimit("SELECT * FROM `potions` WHERE `status`='R' ORDER BY ".$_GET['lista']." ".$strOrder, 30, (30 * ($page - 1)));
      } 
    elseif (isset($_POST['szukany1']))
      {
	$pm = $db -> Execute("SELECT * FROM `potions` WHERE `status`='R' AND name=".$strSearch);
	$_POST['szukany'] = $_POST['szukany1'];
      }
    else 
      {
	$pm = $db -> SelectLimit("SELECT * FROM `potions` WHERE status='R' AND MATCH(`name`) AGAINST (".$strSearch." IN BOOLEAN MODE) ORDER BY ".$_GET['lista']." ".$strOrder, 30, (30 * ($page - 1)));
      }
    if (!empty($_POST['szukany']))
      {
	$_GET['order'] .= '&amp;search='.$_POST['szukany'];
	$strOrder .= '&amp;search='.$_POST['szukany'];
      }
    $arritem = array();
    $arrId = array();
    $arrOwner = array();
    while (!$pm -> EOF) 
      {
	$seller = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$pm -> fields['owner']);
	$arritem[] = "<tr><td>".$pm -> fields['name']." (moc: ".$pm -> fields['power'].")</td><td align=center>".$pm -> fields['efect']."</td><td align=\"center\">".$pm -> fields['amount']."</td><td align=center>".$pm -> fields['cost']." / ".($pm->fields['cost'] * $pm->fields['amount'])."</td><td><a href=view.php?view=".$pm -> fields['owner'].">".$seller -> fields['user']."</a></td>";
	$seller -> Close();
	$arrId[] = $pm->fields['id'];
	$arrOwner[] = $pm->fields['owner'];
	$pm -> MoveNext();
      }
    $pm -> Close();
    $smarty -> assign(array("Item" => $arritem, 
			    "Iid" => $arrId,
			    "Pid" => $player->id,
			    "Owner" => $arrOwner,
			    "Tpages" => $pages,
			    "Tpage" => $page,
			    "Aorder" => $_GET['order'],
			    "Aorder2" => $strOrder,
			    "Fpage" => "Idź do strony:",
			    "Mlist" => $_GET['lista'],
			    "Abuy" => A_BUY,
			    "Aadd" => A_ADD,
			    "Adelete" => A_DELETE,
			    "Achange" => A_CHANGE));
}

/**
* Add potions to market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    $rzecz = $db -> Execute("SELECT * FROM `potions` WHERE `owner`=".$player -> id." AND `status`='K'");
    $arrname = array();
    $arrid = array();
    $arramount = array();
    $arrPower = array();
    while (!$rzecz -> EOF) 
    {
        $arrname[] = $rzecz -> fields['name'];
        $arrid[] = $rzecz -> fields['id'];
        $arramount[] = $rzecz -> fields['amount'];
	$arrPower[] = $rzecz->fields['power'];
        $rzecz -> MoveNext();
    }
    $rzecz -> Close();
    $smarty -> assign(array("Name" => $arrname, 
			    "Itemid" => $arrid, 
			    "Amount" => $arramount,
			    "Power" => $arrPower,
			    "Ppower" => "Moc",
			    "Addinfo" => ADD_INFO,
			    "Aadd" => A_ADD,
			    "Potion" => POTION,
			    "Pamount" => P_AMOUNT,
			    "Pamount2" => P_AMOUNT2,
			    "Addall" => "wszystkie posiadane",
			    "Pcost" => P_COST));
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!$_POST['cost']) 
        {
            error (ERROR);
        }
	checkvalue($_POST['cost']);
	checkvalue($_POST['przedmiot']);
        $item = $db -> Execute("SELECT * FROM `potions` WHERE `id`=".$_POST['przedmiot']." AND `owner`=".$player -> id." AND `status`='K'");
	if (!isset($_POST['addall']))
	  {
	    checkvalue($_POST['amount']);
	  }
	else
	  {
	    $_POST['amount'] = $item->fields['amount'];
	  }
        if ($_POST['amount'] > $item -> fields['amount']) 
        {
            error(NO_AMOUNT.$item -> fields['name'].". <a href=\"mmarket.php\">".A_BACK."</a>");
        }
	$test = $db -> Execute("SELECT `id` FROM `potions` WHERE `name`='".$item->fields['name']."' AND `owner`=".$player->id." AND `status`='R' AND `power`=".$item->fields['power']." AND `cost`=".$_POST['cost']);
        if (!$test->fields['id']) 
        {
	    $db -> Execute("INSERT INTO potions (owner, name, efect, power, status, cost, type, amount) VALUES(".$player -> id.",'".$item -> fields['name']."','".$item -> fields['efect']."',".$item -> fields['power'].",'R',".$_POST['cost'].",'".$item -> fields['type']."',".$_POST['amount'].")");
        } 
	else 
        {
            $db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$_POST['amount']." WHERE id=".$test -> fields['id']);
        }
	$test->Close();
        $amount = $item -> fields['amount'] - $_POST['amount'];
        if ($amount < 1) 
        {
            $db -> Execute("DELETE FROM `potions` WHERE `id`=".$item -> fields['id']);
        } 
	else 
        {
            $db -> Execute("UPDATE `potions` SET `amount`=".$amount." WHERE `id`=".$item -> fields['id']);
        }
        $smarty -> assign("Message", YOU_ADD.$_POST['amount'].AMOUNT.$item -> fields['name'].ON_MARKET.$_POST['cost'].FOR_GOLDS.". <A href=mmarket.php>".A_BACK."</a>");
	$item->Close();
    }
}

/**
 * Delete offer from market
 */
if (isset($_GET['wyc'])) 
{
    checkvalue($_GET['wyc']);
    $item = $db -> Execute("SELECT * FROM `potions` WHERE `id`=".$_GET['wyc']);
    if ($item -> fields['owner'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    require_once('includes/marketdel.php');
    deletepotion($item, $player -> id);
    $smarty -> assign("Message", YOU_DELETE." (<a href=\"mmarket.php\">".A_BACK."</a>)");
}

/**
* Delete all player's potions from market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    require_once('includes/marketdelall.php');
    deleteallpotion($player -> id);
    $smarty -> assign("Message", YOU_DELETE." (<a href=\"mmarket.php\">".A_BACK."</a>)");
}

/**
* Buy potions on market
*/
if (isset($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $buy = $db -> Execute("SELECT * FROM `potions` WHERE `id`=".$_GET['buy']." AND `status`='R'");
    if (!$buy -> fields['id']) 
    {
        error (NO_OFERTS);
    }
    if ($buy -> fields['owner'] == $player -> id) 
    {
        error (IS_YOUR);
    }
    $seller = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$buy -> fields['owner']);
    $smarty -> assign( array("Name" => $buy -> fields['name'], 
        "Power" => $buy -> fields['power'], 
        "Amount1" => $buy -> fields['amount'], 
        "Itemid" => $buy -> fields['id'], 
        "Cost" => $buy -> fields['cost'], 
        "Seller" => $seller -> fields['user'], 
        "Type" => $buy -> fields['type'], 
        "Sid" => $buy -> fields['owner'],
        "Buyinfo" => BUY_INFO,
        "Potion" => POTION,
        "Oamount" => O_AMOUNT,
        "Pcost" => P_COST,
        "Pseller" => P_SELLER,
        "Bamount" => B_AMOUNT,
        "Ppower" => P_POWER,
        "Abuy" => A_BUY));
    $seller -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
	checkvalue($_POST['amount']);
        if ($_POST['amount'] > $buy -> fields['amount']) 
        {
            error(NO_AMOUNT.$buy -> fields['name'].ON_MARKET);
        }
        $price = $_POST['amount'] * $buy -> fields['cost'];
        if ($price > $player -> credits) 
        {
            error (NO_MONEY);
        }
        $ncost = ceil($buy -> fields['cost'] * .5);
        $test = $db -> Execute("SELECT `id` FROM `potions` WHERE `name`='".$buy -> fields['name']."' AND `owner`=".$player -> id." AND `status`='K' AND `power`=".$buy -> fields['power']);
        if (!$test -> fields['id']) 
        {
            $db -> Execute("INSERT INTO potions (name, owner, efect, type, power, status, amount) VALUES('".$buy -> fields['name']."',".$player -> id.",'".$buy -> fields['efect']."','".$buy -> fields['type']."',".$buy -> fields['power'].",'K',".$_POST['amount'].")");
        } 
	else 
        {
            $db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$_POST['amount']." WHERE id=".$test -> fields['id']);
        }
        $test -> Close();
        if ($_POST['amount'] == $buy -> fields['amount']) 
        {
            $db -> Execute("DELETE FROM `potions` WHERE `id`=".$buy -> fields['id']);
        } 
	else 
        {
            $db -> Execute("UPDATE `potions` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$buy -> fields['id']);
        }
        $db -> Execute("UPDATE `players` SET `bank`=`bank`+".$price." WHERE `id`=".$buy -> fields['owner']);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$price." WHERE `id`=".$player -> id);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['owner'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['name'].YOU_GET.$price.TO_BANK."', ".$strDate.", 'M')");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].L_AMOUNT.$buy -> fields['name'].FOR_A.$price.GOLD_COINS);
    }
    $buy->Close();
}

/**
* List of all offerts on market
*/
if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    $oferts = $db -> Execute("SELECT `name` FROM `potions` WHERE `status`='R' GROUP BY `name`");
    $arrname = array();
    $arramount = array();
    $i = 0;
    while (!$oferts -> EOF) 
    {
        $arrname[$i] = $oferts -> fields['name'];
        $arramount[$i] = 0;
        $query = $db -> Execute("SELECT `id` FROM `potions` WHERE `status`='R' AND `name`='".$arrname[$i]."'");
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
        "Message" => "<br />(<a href=\"mmarket.php\">".A_BACK."</a>)",
        "Listinfo" => LIST_INFO,
        "Pname" => P_NAME,
        "Pamount" => P_AMOUNT,
        "Paction" => P_ACTION,
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
$smarty -> display('mmarket.tpl');

require_once("includes/foot.php");
?>
