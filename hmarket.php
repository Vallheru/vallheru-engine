<?php
/**
 *   File functions:
 *   Herbs market - add, buy herbs from other players
 *
 *   @name                 : hmarket.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 23.05.2012
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
require_once("languages/".$lang."/hmarket.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR."<a href");
}
$gr = $db -> Execute("SELECT `id`, `illani`, `illanias`, `nutari`, `dynallca`, `ilani_seeds`, `illanias_seeds`, `nutari_seeds`, `dynallca_seeds` FROM `herbs` WHERE `gracz`=".$player -> id) or die($db -> ErrorMsg());
$arrName = array(N_HERB1, N_HERB2, N_HERB3, N_HERB4, N_HERB5, N_HERB6, N_HERB7, N_HERB8);

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
if (isset($_GET['view']))
  {
    /**
     * View oferts on market
     */
    if ($_GET['view'] == 'market') 
      {
	if (isset($_GET['search']))
	  {
	    $_POST['szukany'] = $_GET['search'];
	  }
	if (empty($_POST['szukany']) && empty($_POST['szukany1'])) 
	  {
	    $msel = $db -> Execute("SELECT count(`id`) FROM `hmarket`");
	    $strSearch = '';
	    $_POST['szukany'] = '';
	  } 
	else 
	  {
	    if (isset($_POST['szukany1']))
	      {
		$_POST['szukany'] = $_POST['szukany1'];
	      }
	    $_POST['szukany'] = strip_tags($_POST['szukany']);
	    $_POST['szukany'] = str_replace("*","%", $_POST['szukany']);
	    $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
	    for ($i = 0; $i < count($arrName); $i++)
	      {
		if (stripos($arrName[$i], $_POST['szukany']) !== FALSE)
		  {
		    $strSearch = $arrName[$i];
		    $strSearch = $db -> qstr($strSearch, get_magic_quotes_gpc());
		    break;
		  }
	      }
	    $msel = $db->Execute("SELECT count(`id`) FROM `hmarket` WHERE `nazwa` LIKE ".$strSearch) or die($db->ErrorMsg());
	  }
	$oferty = $msel->fields['count(`id`)'];
	$msel -> Close();
	if (!isset($_GET['lista'])) 
	  {
	    $_GET['lista'] = 0;
	  }
	if ($oferty == 0) 
	  {
	    error(NO_OFERTS);
	  }
	$pages = ceil($oferty / 30);
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
	if (!in_array($_GET['lista'], array('id', 'nazwa', 'ilosc', 'cost', 'seller'))) 
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
	if (empty($_POST['szukany'])) 
	  {
	    $arrOferts = $db->GetAll("SELECT * FROM `hmarket` ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".$intLimit.", 30");
	  } 
	else 
	  {
	    $arrOferts = $db->GetAll("SELECT * FROM `hmarket` WHERE `nazwa` LIKE ".$strSearch." ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".$intLimit.", 30");
	  }
	$arrHeaders = array(HERB, T_AMOUNT, "Cena szt / wszystko", T_SELLER);
	$arrHlinks = array('nazwa', 'ilosc', 'cost', 'seller');
	$arrKeys = array('nazwa', 'ilosc', 'cost');
	foreach ($arrOferts as &$arrOfert)
	  {
	    $arrOfert['cost'] = $arrOfert['cost']."/".($arrOfert['cost'] * $arrOfert['ilosc']);
	    $query = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$arrOfert['seller']);
	    $arrOfert['user'] = $query->fields['user'];
	    $query->Close();
	  }
	$smarty -> assign(array("Headers" => $arrHeaders,
				"Aheaders" => $arrHlinks,
				"Tname" => "Nazwa",
				"Oferts" => $arrOferts,
				"Okeys" => $arrKeys,
				"Mtype" => "hmarket",
				"Toptions" => T_OPTIONS,
				"Pid" => $player->id,
				"Tpages" => $pages,
				"Tpage" => $page,
				"Fpage" => "Idź do strony:",
				"Mlist" => $_GET['lista'],
				"Aorder" => $_GET['order'],
				"Aorder2" => $strOrder,
				"Abuy" => A_BUY,
				"Aadd" => A_ADD,
				"Adelete" => A_DELETE,
				"Achange" => A_CHANGE,
				"Asearch" => A_SEARCH,
				"Asearch2" => $_POST['szukany'],
				"Aadd2" => "Dodaj ofertę",
				"Viewinfo" => VIEW_INFO));
	if (!isset($_POST['szukany']))
	  {
	    $_POST['szukany'] = '';
	  }
      }

    /**
     * Add ofert on market
     */
    elseif ($_GET['view'] == 'add') 
      {
	$arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	$arrOptions = array();
	for ($i = 0; $i < count($arrSqlname); $i++)
	  {
	    if ($gr->fields[$arrSqlname[$i]] > 0)
	      {
		$arrOptions[$arrSqlname[$i]] = $arrName[$i]." (".T_AMOUNT.": ".$gr->fields[$arrSqlname[$i]].")";
	      }
	  }
	$smarty -> assign(array("Addinfo" => ADD_INFO,
				"Item" => HERB,
				"Ioptions" => $arrOptions,
				"Amount" => H_AMOUNT,
				"Cost" => H_COST,
				"Aadd" => A_ADD,
				"Addall" => "wszystkie posiadane",
				"Addofert" => 0));
	if (isset ($_GET['step']) && $_GET['step'] == 'add') 
	  {
	    if (!isset($_POST['cost'])) 
	      {
		error (ERROR);
	      }
	    checkvalue($_POST['cost']);
	    if (!in_array($_POST['item'], $arrSqlname))
	      {
		error(ERROR);
	      }
	    $intKey = array_search($_POST['item'], $arrSqlname);
	    if (!isset($_POST['addall']))
	      {
		if (!isset($_POST['amount']))
		  {
		    error(ERROR);
		  }
		checkvalue($_POST['amount']);
		if ($_POST['amount'] > $gr -> fields[$arrSqlname[$intKey]])
		  {
		    error(NO_AMOUNT.$arrName[$intKey]);
		  }
	      }
	    else
	      {
		$_POST['amount'] = $gr -> fields[$arrSqlname[$intKey]];
	      }
	    $objTest = $db -> Execute("SELECT `id` FROM `hmarket` WHERE `seller`=".$player -> id." AND `nazwa`='".$arrName[$intKey]."'");
	    if (!$objTest -> fields['id'])
	      {
		$db -> Execute("INSERT INTO `hmarket` (`seller`, `ilosc`, `cost`, `nazwa`, `lang`) VALUES(".$player -> id.",".$_POST['amount'].",".$_POST['cost'].",'".$arrName[$intKey]."', '".$lang."')") or die($db -> ErrorMsg());
		$db -> Execute("UPDATE `herbs` SET `".$_POST['item']."`=`".$_POST['item']."`-".$_POST['amount']." WHERE `gracz`=".$player -> id);
		$smarty -> assign("Message", YOU_ADD.$_POST['amount']."</b> ".$arrName[$intKey].ON_MARKET.$_POST['cost'].FOR_GOLDS." <a href=\"hmarket.php?view=add\">".A_REFRESH."</a>");
	      }
            else
	      {
		$smarty -> assign(array("Addofert" => $objTest -> fields['id'],
					"Youwant" => YOU_WANT,
					"Ayes" => YES,
					"Iname" => $_POST['item'],
					"Iamount" => $_POST['amount'],
					"Icost" => $_POST['cost']));
		if (isset($_POST['ofert']))
		  {
		    checkvalue($_POST['ofert']);
		    require_once('includes/marketaddto.php');
		    addtoherb($_POST['ofert'], $_POST['item'], $player -> id, $_POST['amount']);
		    $smarty -> assign("Message", YOU_ADD.$_POST['amount']."</b> ".$arrName[$intKey].ON_MARKET2." <a href=\"hmarket.php?view=add\">".A_REFRESH."</a>");
		  }
	      }
	    $objTest -> Close();
	  }
      }
    
    /**
     * Delete all oferts one player from market
     */
    elseif ($_GET['view'] == 'del') 
      {
	require_once('includes/marketdelall.php');
	deleteallherb($player -> id, $arrName);
	$smarty -> assign("Message", YOU_DELETE." (<A href=hmarket.php>".A_BACK."</a>)");
      }

    /**
     * List of all ofers on market
     */
    elseif ($_GET['view'] == 'all') 
      {
	$oferts = $db -> Execute("SELECT `nazwa` FROM `hmarket` GROUP BY `nazwa`");
	$arrname = array();
	$arramount = array();
	while (!$oferts -> EOF) 
	  {
	    $arrname[] = $oferts -> fields['nazwa'];
	    $query = $db -> Execute("SELECT count(`id`) FROM `hmarket` WHERE `nazwa`='".$oferts->fields['nazwa']."'");
	    $arramount[] = $query->fields['count(`id`)'];
	    $query -> Close();
	    $oferts -> MoveNext();
	  }
	$oferts -> Close();
	$smarty -> assign(array("Name" => $arrname, 
				"Amount" => $arramount, 
				"Message" => "<br />(<a href=\"hmarket.php\">".A_BACK."</a>)",
				"Listinfo" => LIST_INFO,
				"Iname" => H_NAME,
				"Iamount" => H_AMOUNT,
				"Iaction" => H_ACTION,
				"Ashow" => A_SHOW));
      }
  }
else
  {
    $_GET['view'] = '';
  }

/**
* Buy herbs from market
*/
if (isset($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $buy = $db -> Execute("SELECT * FROM `hmarket` WHERE `id`=".$_GET['buy']) ;
    if (!$buy -> fields['id']) 
    {
        error (NO_OFERTS);
    }
    if ($buy -> fields['seller'] == $player -> id) 
    {
        error (IS_YOUR);
    }
    $seller = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$buy -> fields['seller']);
    $smarty -> assign(array("Name" => $buy -> fields['nazwa'], 
                            "Amount1" => $buy -> fields['ilosc'], 
                            "Itemid" => $buy -> fields['id'], 
                            "Cost" => $buy -> fields['cost'], 
                            "Seller" => $seller -> fields['user'], 
                            "Sid" => $buy -> fields['seller'],
			    "Infos" => array(),
                            "Buyinfo" => BUY_INFO,
                            "Bitem" => HERB,
                            "Oamount" => O_AMOUNT,
                            "Icost" => H_COST,
                            "Iseller" => SELLER,
                            "Bamount" => B_AMOUNT,
                            "Abuy" => A_BUY));
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
        if (!isset($_POST['amount']))
	  {
	    error(ERROR);
	  }
	checkvalue($_POST['amount']);
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
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['seller'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['nazwa'].YOU_GET.$price.TO_BANK."', ".$strDate.", 'M')");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$buy -> fields['nazwa'].FOR_A.$price.GOLD_COINS);
    }
    $buy->Close();
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
* Initialization of variables
*/
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
$smarty -> display ('market2.tpl');

require_once("includes/foot.php");
?>
