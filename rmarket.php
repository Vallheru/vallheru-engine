<?php
/**
 *   File functions:
 *   Jewellers market
 *
 *   @name                 : rmarket.php                            
 *   @copyright            : (C) 2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 10.06.2012
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
require_once("languages/".$lang."/rmarket.php");

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
    if (isset($_GET['search']))
      {
	$_POST['szukany1'] = $_GET['search'];
      }
    if (empty($_POST['szukany']) && empty($_POST['szukany1'])) 
      {
        $msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type`='I'");
      }
    elseif (isset($_POST['szukany']))
      {
	$_POST['szukany'] = strip_tags($_POST['szukany']);
        $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
	$msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type`='I' AND name=".$strSearch);
      }
    else 
      {
        $_POST['szukany1'] = strip_tags($_POST['szukany1']);
        $strSearch = $db -> qstr("*".$_POST['szukany1']."*", get_magic_quotes_gpc());
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
    $arrHeaders = array("Nazwa", "Premia", "Poziom", "Ilość", "Cena szt / wszystko", "Sprzedający");
    $arrHlinks = array('name', 'power', 'minlev', 'amount', 'cost', 'owner');
    $smarty -> assign(array("Headers" => $arrHeaders,
			    "Aheaders" => $arrHlinks,
                            "Viewinfo" => VIEW_INFO,
			    "Asearch" => A_SEARCH,
                            "Toptions" => T_OPTIONS,
			    "Tname" => "Nazwa",
			    "Aadd2" => "Dodaj ofertę"));
    if (!in_array($_GET['lista'], array('id', 'name', 'power', 'minlev',  'amount', 'cost', 'owner'))) 
      {
	error(ERROR); 
      }
    if (!isset($_GET['order']))
      {
	$_GET['order'] = 'DESC';
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
    $intLimit = 30 * ($page - 1);
    if (empty($_POST['szukany']) && empty($_POST['szukany1'])) 
      {
	$arrOferts = $db->GetAll("SELECT * FROM `equipment` WHERE `status`='R' AND `type`='I' ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".$intLimit.", 30");
	$_POST['szukany1'] = '';
      }
    elseif (isset($_POST['szukany']))
      {
	$arrOferts = $db->GetAll("SELECT * FROM `equipment` WHERE `status`='R' AND `type`='I' AND name=".$strSearch." ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".$intLimit.", 30");
	$_POST['szukany1'] = $_POST['szukany'];
      }
    else 
      {
	$arrOferts = $db->GetAll("SELECT * FROM `equipment` WHERE `status`='R' AND `type`='I' AND MATCH(`name`) AGAINST (".$strSearch." IN BOOLEAN MODE) ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".$intLimit.", 30");
      }
    $arrKeys = array('name', 'power', 'minlev', 'amount', 'cost');
    foreach ($arrOferts as &$arrOfert)
      {
	$arrOfert['cost'] = $arrOfert['cost']."/".($arrOfert['cost'] * $arrOfert['amount']);
	$query = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$arrOfert['owner']);
	$arrOfert['user'] = $query->fields['user'];
	$arrOfert['seller'] = $arrOfert['owner'];
	$query->Close();
      }
    $smarty -> assign(array("Oferts" => $arrOferts,
			    "Okeys" => $arrKeys,
			    "Mtype" => "rmarket",
			    "Pid" => $player->id,
			    "Tpages" => $pages,
			    "Tpage" => $page,
			    "Aorder" => $_GET['order'],
			    "Aorder2" => $strOrder,
			    "Fpage" => "Idź do strony:",
			    "Mlist" => $_GET['lista'],
			    "Abuy" => A_BUY,
			    "Aadd" => A_ADD,
			    "Adelete" => A_DELETE,
			    "Asearch2" => $_POST['szukany1'],
			    "Achange" => A_CHANGE));
}

/**
* Add oferts to market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    $rzecz = $db -> Execute("SELECT `id`, `name`, `amount`, `power` FROM `equipment` WHERE `status`='U' AND `type`='I' AND `owner`=".$player -> id);
    $arrOptions = array();
    while (!$rzecz -> EOF) 
    {
        $arrOptions[$rzecz->fields['id']] = $rzecz->fields['name']." (+".$rzecz->fields['power'].") (ilość: ".$rzecz->fields['amount'].")";
        $rzecz -> MoveNext();
    }
    $rzecz -> Close();
    if (count($arrOptions) == 0)
    {
        error(NO_ITEMS);
    }
    $smarty -> assign (array("Ioptions" => $arrOptions,
                             "Addinfo" => ADD_INFO,
                             "Item" => ITEM,
                             "Aadd" => A_ADD,
                             "Amount" => I_AMOUNT2,
			     "Addall" => "wszystkie posiadane",
                             "Cost" => I_COST,
			     "Addofert" => 0));
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!isset($_POST['cost'])) 
        {
            error(ERROR);
        }
	checkvalue($_POST['cost']);
	checkvalue($_POST['item']);
        $item = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_POST['item']." AND `status`='U' AND `type`='I' AND `owner`=".$player -> id);
	if (!$item->fields['id'])
	  {
	    error(ERROR);
	  }
	if (!isset($_POST['addall']))
	  {
	    checkvalue($_POST['amount']);
	  }
	else
	  {
	    $_POST['amount'] = $item->fields['amount'];
	  }
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
    $arrInfo = array("Premia" => $buy->fields['power']);
    $smarty -> assign(array("Name" => $buy -> fields['name'], 
                            "Itemid" => $buy -> fields['id'], 
                            "Amount1" => $buy -> fields['amount'], 
                            "Cost" => $buy -> fields['cost'], 
                            "Seller" => $seller -> fields['user'], 
                            "Sid" => $buy -> fields['owner'], 
			    "Infos" => $arrInfo,
                            "Bitem" => ITEM,
                            "Buyinfo" => BUY_INFO,
                            "Oamount" => O_AMOUNT,
                            "Icost" => I_COST,
                            "Iseller" => SELLER,
                            "Bamount" => B_AMOUNT,
                            "Abuy" => A_BUY));
    $seller -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
        if (!isset($_POST['amount'])) 
        {
            error(ERROR);
        }
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
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['owner'].", '<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['name'].YOU_GET.$price.TO_BANK."', ".$strDate.", 'M')");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$buy -> fields['name'].FOR_A.$price.GOLD_COINS);
    }
    $buy->Close();
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
        $query = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `name`='".$arrname[$i]."'");
        $arramount[$i] = $query -> fields['count(`id`)'];
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
$smarty -> display('market2.tpl');

require_once("includes/foot.php"); 
?>
