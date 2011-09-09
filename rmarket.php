<?php
/**
 *   File functions:
 *   Jewellers market
 *
 *   @name                 : rmarket.php                            
 *   @copyright            : (C) 2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 09.09.2011
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

$title = "Rynek jubilerski";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/rmarket.php");

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

/**
* Show oferts in market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    if (empty($_POST['szukany']) && !isset($_POST['szukany1'])) 
      {
        $msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type`='I'");
      }
    elseif (isset($_POST['szukany1']))
      {
	$_POST['szukany1'] = strip_tags($_POST['szukany1']);
        $strSearch = $db -> qstr($_POST['szukany1'], get_magic_quotes_gpc());
	$msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type`='I' AND name=".$strSearch);
      }
    else 
      {
        $_POST['szukany'] = strip_tags($_POST['szukany']);
        $strSearch = $db -> qstr("*".$_POST['szukany']."*", get_magic_quotes_gpc());
        $msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type`='I' AND MATCH(`name`) AGAINST (".$strSearch." IN BOOLEAN MODE)") or die($db -> ErrorMsg());
      }
    $przed = $msel -> fields['count(`id`)'];
    $msel -> Close();
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
                            "Tpower" => T_POWER,
                            "Tcost" => "Cena szt / wszystko",
                            "Tseller" => T_SELLER,
                            "Tamount" => T_AMOUNT,
                            "Tlevel" => T_LEVEL,
                            "Viewinfo" => VIEW_INFO,
			    "Asearch" => A_SEARCH,
                            "Toptions" => T_OPTIONS));
    if (!in_array($_GET['lista'], array('id', 'name', 'power', 'minlev',  'amount', 'cost', 'owner'))) 
      {
	error(ERROR); 
      }
    if ($_GET['lista'] == 'zr')
      {
	$strOrder = ' ASC';
      }
    else
      {
	$strOrder = ' DESC';
      }
    if (empty($_POST['szukany']) && !isset($_POST['szukany1'])) 
      {
	$pm = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `status`='R' AND `type`='I' ORDER BY ".$_GET['lista'].$strOrder, 30, (30 * ($page - 1)));
      }
    elseif (isset($_POST['szukany1']))
      {
	$pm = $db -> Execute("SELECT * FROM `equipment` WHERE `status`='R' AND `type`='I' AND name=".$strSearch);
      }
    else 
      {
	$pm = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `status`='R' AND `type`='I' AND MATCH(`name`) AGAINST (".$strSearch." IN BOOLEAN MODE) ORDER BY ".$_GET['lista'].$strOrder, 30, (30 * ($page - 1)));
      }
    $arrname = array();
    $arrpower = array();
    $arrcost = array();
    $arrowner = array();
    $arraction = array();
    $arramount = array();
    $arrlevel = array();
    $arrseller = array();
    $arrId = array();
    $arrFcost = array();
    while (!$pm -> EOF) 
      {
	$arrname[] = $pm -> fields['name'];
	$arrpower[] = $pm -> fields['power'];
	$arrcost[] = $pm -> fields['cost'];
	$arrFcost[] = $pm->fields['cost'] * $pm->fields['amount'];
	$arrowner[] = $pm -> fields['owner'];
	$arramount[] = $pm -> fields['amount'];
	$arrlevel[] = $pm -> fields['minlev'];
	$seller = $db -> Execute("SELECT user FROM players WHERE id=".$pm -> fields['owner']);
	$arrseller[] = $seller -> fields['user'];
	$seller -> Close();
	$arrId[] = $pm->fields['id'];
	$pm -> MoveNext();
      }
    $pm -> Close();
    $smarty -> assign(array("Name" => $arrname, 
			    "Power" => $arrpower, 
			    "Cost" => $arrcost, 
			    "Owner" => $arrowner, 
			    "Action" => $arraction,  
			    "Amount" => $arramount, 
			    "Minlev" => $arrlevel,
			    "Iid" => $arrId,
			    "Pid" => $player->id,
			    "Tpages" => $pages,
			    "Tpage" => $page,
			    "Fcost" => $arrFcost,
			    "Fpage" => "Idź do strony:",
			    "Mlist" => $_GET['lista'],
			    "Abuy" => A_BUY,
			    "Aadd" => A_ADD,
			    "Adelete" => A_DELETE,
			    "Achange" => A_CHANGE,
			    "Seller" => $arrseller));
    if (!isset($_POST['szukany'])) 
        {
	  $_POST['szukany'] = '';
        }
}

/**
* Add oferts to market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    $rzecz = $db -> Execute("SELECT `id`, `name`, `amount`, `power` FROM `equipment` WHERE `status`='U' AND `type`='I' AND `owner`=".$player -> id);
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
    if (!$arrid[0])
    {
        error(NO_ITEMS);
    }
    $smarty -> assign (array("Name" => $arrname, 
                             "Itemid" => $arrid, 
                             "Amount" => $arramount,
			     "Rpower" => $arrPower,
                             "Addinfo" => ADD_INFO,
                             "Item" => ITEM,
                             "Aadd" => A_ADD,
                             "Iamount" => I_AMOUNT,
                             "Iamount2" => I_AMOUNT2,
                             "Icost" => I_COST));
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!isset($_POST['cost'])) 
        {
            error(ERROR);
        }
	checkvalue($_POST['cost']);
	checkvalue($_POST['przedmiot']);
	checkvalue($_POST['amount']);
        $item = $db -> Execute("SELECT * FROM equipment WHERE id=".$_POST['przedmiot']);
        if ($item -> fields['amount'] < $_POST['amount']) 
        {
            error (NO_AMOUNT.$item -> fields['name']);
        }
        if ($item -> fields['type'] != 'I')
        {
            error(ERROR);
        }
        $amount = $item -> fields['amount'] - $_POST['amount'];
        if ($amount > 0) 
        {
            $db -> Execute("UPDATE equipment SET amount=".$amount." where id=".$item -> fields['id']);
        } 
            else
        {
            $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$item -> fields['id']);
        }
        $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$item -> fields['name']."' AND `type`='".$item -> fields['type']."' AND `status`='R' AND `owner`=".$player -> id." AND `power`=".$item -> fields['power']." AND `minlev`=".$item -> fields['minlev']);
        if (!$test -> fields['id']) 
        {
            $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `minlev`, `status`, `amount`) VALUES(".$player -> id.", '".$item -> fields['name']."', ".$item -> fields['power'].", '".$item -> fields['type']."', ".$_POST['cost'].", ".$item -> fields['minlev'].", 'R', ".$_POST['amount'].")");
            $smarty -> assign("Message", YOU_ADD.$_POST['amount'].I_AMOUNT3.$item -> fields['name'].ON_MARKET.$_POST['cost'].FOR_GOLDS.". <a href=\"rmarket.php?view=add\">".A_REFRESH."</a>");
        } 
            else 
        {
            $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
            $smarty -> assign("Message", YOU_ADD.$_POST['amount'].I_AMOUNT3.$item -> fields['name']."</b>. <a href=\"rmarket.php?view=add\">".A_REFRESH."</a>");
        }
        $test -> Close();
    }
}

/**
* Delete selected ofert from market
*/
if (isset($_GET['wyc'])) 
{
    checkvalue($_GET['wyc']);
    $dwyc = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['wyc']);
    if ($dwyc -> fields['owner'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    require_once('includes/marketdel.php');
    deleteitem($dwyc, $player -> id);
    $smarty -> assign("Message", YOU_DELETE." (<a href=\"rmarket.php\">".A_BACK."</a>)");
}

/**
* Delete oferts from market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    $objArm = $db -> Execute("SELECT * FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='R' AND `type`='I'");
    while (!$objArm -> EOF)
    {
        $intTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$objArm -> fields['name']."' AND `type`='".$objArm -> fields['type']."' AND `status`='U' AND `owner`=".$player -> id." AND `power`=".$objArm -> fields['power']." AND `cost`=1 AND `minlev`=".$objArm -> fields['minlev']);
        if (!$intTest -> fields['id']) 
        {
            $db -> Execute("UPDATE `equipment` SET `status`='U', `cost`=1 WHERE `id`=".$objArm -> fields['id']);
        } 
            else 
        {
            $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$objArm -> fields['amount']." WHERE `id`=".$intTest -> fields['id']);
        }
        $intTest -> Close();
        $objArm -> MoveNext();
    } 
    $db -> Execute("DELETE FROM `equipment` WHERE `status`='R' AND `type`='I' AND `owner`=".$player -> id);
    $smarty -> assign("Message",YOU_DELETE." (<a href=\"rmarket.php\">".A_BACK."</a>)");
}

/**
* Buy items from market
*/
if (isset($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $buy = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['buy']." AND `type`='I' AND `status`='R'");
    if (!$buy -> fields['id']) 
    {
        error (NO_OFERTS);
    }
    if ($buy -> fields['owner'] == $player -> id) 
    {
        error (IS_YOUR);
    }
    $seller = $db -> Execute("SELECT user FROM players WHERE id=".$buy -> fields['owner']);    
    $smarty -> assign(array("Name" => $buy -> fields['name'], 
                            "Itemid" => $buy -> fields['id'], 
                            "Amount1" => $buy -> fields['amount'], 
                            "Cost" => $buy -> fields['cost'], 
                            "Seller" => $seller -> fields['user'], 
                            "Sid" => $buy -> fields['owner'], 
                            "Power" => $buy -> fields['power'], 
                            "Type" => $buy -> fields['type'], 
                            "Item" => ITEM,
                            "Buyinfo" => BUY_INFO,
                            "Ipower" => I_POWER,
                            "Oamount" => O_AMOUNT,
                            "Icost" => I_COST,
                            "Iseller" => SELLER,
                            "Bamount" => B_AMOUNT,
                            "Abuy" => A_BUY));
    $buy -> Close();
    $seller -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
        if (!isset($_POST['amount'])) 
        {
            error(ERROR);
        }
	checkvalue($_POST['amount']);
        $buy = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['buy']." AND `type`='I'");
        if ($_POST['amount'] > $buy -> fields['amount']) 
        {
            error(NO_AMOUNT.$buy -> fields['name'].ON_MARKET);
        }
        $price = $_POST['amount'] * $buy -> fields['cost'];
        if ($price > $player -> credits) 
        {
            error (NO_MONEY);
        }
        $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$buy -> fields['name']."' AND `type`='".$buy -> fields['type']."' AND `status`='U' AND `owner`=".$player -> id." AND `power`=".$buy -> fields['power']." AND `cost`=1 AND `minlev`=".$buy -> fields['minlev']);
        if (!$test -> fields['id']) 
        {
            $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `ptype`, `repair`) VALUES(".$player -> id.", '".$buy -> fields['name']."', ".$buy -> fields['power'].", '".$buy -> fields['type']."', 1, ".$buy -> fields['zr'].", ".$buy -> fields['wt'].", ".$buy -> fields['minlev'].", ".$buy -> fields['maxwt'].", ".$_POST['amount'].", '".$buy -> fields['magic']."', ".$buy -> fields['poison'].", ".$buy -> fields['szyb'].", '".$buy -> fields['twohand']."', '".$buy -> fields['ptype']."', ".$buy -> fields['repair'].")");
        } 
            else 
        {
            $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
        }
        $test -> Close();
        if ($_POST['amount'] == $buy -> fields['amount']) 
        {
            $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$buy -> fields['id']);
        } 
            else 
        {
            $db -> Execute("UPDATE `equipment` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$buy -> fields['id']);
        }
        $db -> Execute("UPDATE `players` SET `bank`=`bank`+".$price." WHERE `id`=".$buy -> fields['owner']);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$price." WHERE `id`=".$player -> id);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$buy -> fields['owner'].", '<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['name'].YOU_GET.$price.TO_BANK."', ".$strDate.")");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$buy -> fields['name'].FOR_A.$price.GOLD_COINS);
    }
}

/**
* List of all oferts on market
*/
if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    $oferts = $db -> Execute("SELECT `name` FROM `equipment` WHERE `status`='R' AND `type`='I' GROUP BY `name`");
    $arrname = array();
    $arramount = array();
    $i = 0;
    while (!$oferts -> EOF) 
    {
        $arrname[$i] = $oferts -> fields['name'];
        $arramount[$i] = 0;
        $query = $db -> Execute("SELECT count(*) FROM `equipment` WHERE `status`='R' AND `name`='".$arrname[$i]."'");
        $arramount[$i] = $query -> fields['count(*)'];
        $query -> Close();
        $oferts -> MoveNext();
        $i = $i + 1;
    }
    $oferts -> Close();
    $smarty -> assign(array("Name" => $arrname, 
                            "Amount" => $arramount, 
                            "Message" => "<br />(<a href=\"rmarket.php\">".A_BACK."</a>)",
                            "Listinfo" => LIST_INFO,
                            "Iname" => I_NAME,
                            "Iamount" => I_AMOUNT,
                            "Iaction" => I_ACTION,
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
$smarty -> display('rmarket.tpl');

require_once("includes/foot.php"); 
?>
