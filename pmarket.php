<?php
/**
 *   Funkcje pliku:
 *   Minerals market
 *
 *   @name                 : pmarket.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 21.12.2012
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
require_once("languages/".$lang."/pmarket.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR."<a href");
}

$objMinerals = $db -> Execute("SELECT `owner`, `copperore`, `zincore`, `tinore`, `ironore`, `coal`, `copper`, `bronze`, `brass`, `iron`, `steel`, `pine`, `hazel`, `yew`, `elm`, `crystal`, `adamantium`, `meteor` FROM `minerals` WHERE `owner`=".$player -> id);
$arrMinerals = array(MIN1, MIN15, MIN16, MIN17, MIN18, MIN2, MIN9, MIN10, MIN3, MIN11, MIN4, MIN5, MIN6, MIN7, MIN8, MIN12, MIN13, MIN14);

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
    if (isset($_GET['search']))
      {
	$_POST['szukany'] = $_GET['search'];
      }
    if (empty($_POST['szukany']) && empty($_POST['szukany1'])) 
      {
        $msel = $db -> Execute("SELECT count(`id`) FROM pmarket");
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
	if (strpos($_POST['szukany'], '%') === FALSE)
	  {
	    $_POST['szukany'] = '%'.$_POST['szukany'].'%';
	  }
        $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
        $msel = $db -> Execute("SELECT count(`id`) FROM `pmarket` WHERE `nazwa` LIKE ".$strSearch);
      }
    $oferty = $msel->fields['count(`id`)'];
    $msel -> Close();
    if ($oferty == 0) 
    {
        error (NO_OFERTS);
    }
    if (!isset($_GET['lista'])) 
    {
        $_GET['lista'] = 'id';
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
    $arrHeaders = array(MINERAL, T_AMOUNT, "Cena szt / wszystko", T_SELLER);
    $arrHlinks = array('nazwa', 'ilosc', 'cost', 'seller');
    $smarty -> assign(array("Headers" => $arrHeaders,
			    "Aheaders" => $arrHlinks,
                            "Viewinfo" => VIEW_INFO,
			    "Asearch" => A_SEARCH,
                            "Toptions" => T_OPTIONS,
			    "Aadd2" => "Dodaj ofertę"));
    if (!in_array($_GET['lista'], array('nazwa', 'ilosc', 'cost', 'seller', 'id'))) 
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
	$arrOferts = $db->GetAll("SELECT * FROM `pmarket` ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".$intLimit.", 30");
      } 
    else 
      {
	$arrOferts = $db->GetAll("SELECT * FROM `pmarket` WHERE `nazwa` LIKE ".$strSearch." ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".$intLimit.", 30");
      }
    $arrKeys = array('nazwa', 'ilosc', 'cost');
    foreach ($arrOferts as &$arrOfert)
      {
	$arrOfert['cost'] = $arrOfert['cost']."/".($arrOfert['cost'] * $arrOfert['ilosc']);
	$query = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$arrOfert['seller']);
	$arrOfert['user'] = $query->fields['user'];
	$query->Close();
      }
    $smarty -> assign(array("Oferts" => $arrOferts,
			    "Okeys" => $arrKeys,
			    "Mtype" => "pmarket",
			    "Pid" => $player->id,
			    "Tpages" => $pages,
			    "Tpage" => $page,
			    "Tname" => "Nazwa",
			    "Aorder" => $_GET['order'],
			    "Aorder2" => $strOrder,
			    "Fpage" => "Idź do strony:",
			    "Mlist" => $_GET['lista'],
			    "Abuy" => A_BUY,
			    "Aadd" => A_ADD,
			    "Adelete" => A_DELETE,
			    "Asearch2" => $_POST['szukany'],
			    "Achange" => A_CHANGE));
}

/**
 * Add ofert to market
 */
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    $arrAmount = array($player -> platinum, $objMinerals -> fields['copperore'], $objMinerals -> fields['zincore'], $objMinerals -> fields['tinore'], $objMinerals -> fields['ironore'], $objMinerals -> fields['copper'], $objMinerals -> fields['bronze'], $objMinerals -> fields['brass'], $objMinerals -> fields['iron'], $objMinerals -> fields['steel'], $objMinerals -> fields['coal'], $objMinerals -> fields['adamantium'], $objMinerals -> fields['meteor'], $objMinerals -> fields['crystal'], $objMinerals -> fields['pine'], $objMinerals -> fields['hazel'], $objMinerals -> fields['yew'], $objMinerals -> fields['elm']);
    $arrOptions = array();
    for ($i = 0; $i < count($arrMinerals); $i++)
      {
	if ($arrAmount[$i] > 0)
	  {
	    $arrOptions[$i] = $arrMinerals[$i]." (".T_AMOUNT.": ".$arrAmount[$i].")";
	  }
      }
    $smarty -> assign(array("Addinfo" => ADD_INFO,
                            "Amount" => M_AMOUNT,
                            "Cost" => M_COST,
                            "Aadd" => A_ADD,
                            "Ioptions" => $arrOptions,
                            "Item" => MINERAL,
                            "Tamount" => T_AMOUNT,
			    "Addall" => "wszystkie posiadane",
                            "Addofert" => 0));
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!isset($_POST['item'])) 
        {
            error(ERROR);
        }
	$_POST['item'] = intval($_POST['item']);
	if ($_POST['item'] < 0)
	  {
	    error(ERROR);
	  }
        $arrSqlname = array('', 'copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
        $arrName = array(MITH, COPPERORE, ZINCORE, TINORE, IRONORE, COPPER, BRONZE, BRASS, IRON, STEEL, COAL, ADAMANTIUM, METEOR, CRYSTAL, PINE, HAZEL, YEW, ELM);
        $strMineral = $arrSqlname[$_POST['item']];
        $strName = $arrName[$_POST['item']];
        $intAmount = $arrAmount[$_POST['item']];
        $strSqlname = $arrMinerals[$_POST['item']];
	if (isset($_POST['addall']))
	  {
	    $_POST['amount'] = $intAmount;
	  }
	else
	  {
	    if (!isset($_POST['amount']))
	      {
		error(ERROR);
	      }
	    checkvalue($_POST['amount']);
	    if ($_POST['amount'] > $intAmount)
	      {
		error(NO_AMOUNT.$strName);
	      }
	  }
	checkvalue($_POST['cost']);
        $objTest = $db -> Execute("SELECT `id` FROM `pmarket` WHERE `seller`=".$player -> id." AND `nazwa`='".$strSqlname."'");
        if (!$objTest -> fields['id'])
        {
            if ($_POST['item'] == 0) 
            {
                $db -> Execute("UPDATE players SET platinum=platinum-".$_POST['amount']." WHERE id=".$player -> id);
            } 
                else 
            {
                $db -> Execute("UPDATE minerals SET ".$strMineral."=".$strMineral."-".$_POST['amount']." WHERE owner=".$player -> id);
            }
            $db -> Execute("INSERT INTO pmarket (seller, ilosc, cost, nazwa, lang) VALUES(".$player -> id.",".$_POST['amount'].",".$_POST['cost'].",'".$strSqlname."', '".$lang."')");
            $smarty -> assign("Message", YOU_ADD.$_POST['amount']."</b> ".$strName.ON_MARKET.$_POST['cost'].FOR_GOLDS." <a href=\"pmarket.php?view=add\">".A_REFRESH."</a>");
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
                addtomin($_POST['ofert'], $strMineral, $_POST['item'], $player -> id);
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
    $buy = $db -> Execute("SELECT * FROM `pmarket` WHERE `id`=".$_GET['buy']);
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
			    "Sid" => $buy -> fields['seller'],
			    "Infos" => array(),
			    "Buyinfo" => BUY_INFO,
			    "Oamount" => O_AMOUNT,
			    "Icost" => M_COST,
			    "Iseller" => M_SELLER,
			    "Bamount" => B_AMOUNT,
			    "Abuy" => A_BUY,
			    "Bitem" => MINERAL));
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
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['seller'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['nazwa'].YOU_GET.$price.TO_BANK."', ".$strDate.", 'M')");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$buy -> fields['nazwa'].FOR_A.$price.GOLD_COINS);
    }
    $buy->Close();
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
    while (!$oferts -> EOF) 
    {
        $arrname[] = $oferts -> fields['nazwa'];
        $query = $db -> Execute("SELECT count(`id`) FROM `pmarket` WHERE `nazwa`='".$oferts->fields['nazwa']."'");
        $arramount[] = $query->fields['count(`id`)'];
        $query -> Close();
        $oferts -> MoveNext();
    }
    $oferts -> Close();
    $smarty -> assign(array("Name" => $arrname, 
			    "Amount" => $arramount, 
			    "Message" => "<br />(<a href=\"pmarket.php\">".A_BACK."</a>)",
			    "Listinfo" => LIST_INFO,
			    "Iname" => M_NAME,
			    "Iamount" => M_AMOUNT,
			    "Iaction" => M_ACTION,
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
