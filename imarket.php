<?php
/**
 *   File functions:
 *   Items market
 *
 *   @name                 : imarket.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 06.02.2012
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

$title = "Rynek z przedmiotami";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/imarket.php");

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
	$_POST['szukany'] = $_GET['search'];
      }
    $arrTypes = array('W', 'B', 'T', 'R', 'H', 'A', 'S', 'C', 'L', 'E', 'P');
    if (!isset($_GET['order']))
      {
	$_GET['order'] = 'desc';
      }
    $strType = '';
    if (isset($_GET['type']))
      {
	$_POST['type'] = $_GET['type'];
	if (isset($_GET['mlevel']))
	  {
	    $_POST['mlevel'] = $_GET['mlevel'];
	  }
	if (isset($_GET['maxlev']))
	  {
	    $_POST['maxlev'] = $_GET['maxlev'];
	  }
      }
    if (empty($_POST['szukany']) && !isset($_POST['szukany1']) && !isset($_POST['type'])) 
      {
        $msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type` NOT IN ('I', 'O', 'Q')");
      }
    elseif (isset($_POST['type']))
      {
	if (!in_array($_POST['type'], $arrTypes))
	  {
	    error(ERROR);
	  }
	$strType = '&amp;type='.$_POST['type'];
	$strSql = '';
	if (isset($_POST['mlevel']) && $_POST['mlevel'] != '')
	  {
	    checkvalue($_POST['mlevel']);
	    $strSql = " AND minlev>=".$_POST['mlevel'];
	    $strType .= '&amp;mlevel='.$_POST['mlevel'];
	  }
	if (isset($_POST['maxlev']) && $_POST['maxlev'] != '')
	  {
	    checkvalue($_POST['maxlev']);
	    if (isset($_POST['mlevel']) && $_POST['mlevel'] != '' && $_POST['mlevel'] > $_POST['maxlev'])
	      {
		error(ERROR);
	      }
	    $strSql .= " AND minlev<=".$_POST['maxlev'];
	    $strType .= '&amp;maxlev='.$_POST['maxlev'];
	  }
	$msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type`='".$_POST['type']."'".$strSql);
      }
    elseif (isset($_POST['szukany1']))
      {
	$_POST['szukany1'] = strip_tags($_POST['szukany1']);
        $strSearch = $db -> qstr($_POST['szukany1'], get_magic_quotes_gpc());
	$msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type` NOT IN ('I', 'O', 'Q') AND name=".$strSearch);
      }
    else 
      {
	$_POST['szukany'] = strip_tags($_POST['szukany']);
        $strSearch = $db -> qstr("*".$_POST['szukany']."*", get_magic_quotes_gpc());
        $msel = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='R' AND `type` NOT IN('I', 'O', 'Q') AND MATCH(`name`) AGAINST (".$strSearch." IN BOOLEAN MODE)") or die($db -> ErrorMsg());
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
    $arrNames = array('broni', 'łuków', 'różdżek', 'strzał', 'hełmów', 'zbrój', 'tarcz', 'szat', 'nagolenników', 'narzędzi', 'planów');
    $smarty -> assign(array("Tname" => T_NAME,
                            "Tpower" => T_POWER,
                            "Tcost" => "Cena szt / wszystko",
                            "Tseller" => T_SELLER,
                            "Tdur" => T_DUR,
                            "Tspeed" => T_SPEED,
                            "Tagi" => T_AGI,
                            "Tamount" => T_AMOUNT,
                            "Tlevel" => T_LEVEL,
                            "Viewinfo" => VIEW_INFO,
			    "Asearch" => A_SEARCH,
                            "Toptions" => T_OPTIONS,
			    "Ashow" => "Pokaż",
			    "Tofferts" => "oferty",
			    "Tlevels" => "na poziomach od",
			    "Tto" => "do",
			    "Aadd2" => "Dodaj ofertę",
			    "Otypes" => $arrTypes,
			    "Onames" => $arrNames));
    if (!in_array($_GET['lista'], array('id', 'name', 'power', 'wt', 'szyb', 'zr', 'minlev', 'amount', 'cost', 'owner'))) 
      {
	error(ERROR);
      }
    if ($_GET['lista'] == 'zr')
      {
	if ($_GET['order'] == 'desc')
	  {
	    $strOrder = ' ASC';
	  }
	else
	  {
	    $strOrder = ' DESC';
	  }
      }
    else
      {
	if ($_GET['order'] == 'desc')
	  {
	    $strOrder = ' DESC';
	  }
	else
	  {
	    $strOrder = ' ASC';
	  }
      }
    if ($_GET['order'] == 'desc')
      {
	$strType2 = '&amp;order=asc'.$strType;
      }
    else
      {
	$strType2 = '&amp;order=desc'.$strType;
      }
    $strType .= '&amp;order='.$_GET['order'];
    if (empty($_POST['szukany']) && !isset($_POST['szukany1']) && !isset($_POST['type'])) 
      {
	$pm = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `status`='R' AND `type` NOT IN ('I', 'O', 'Q') ORDER BY ".$_GET['lista'].$strOrder, 30, (30 * ($page - 1)));
      } 
    elseif (isset($_POST['type']))
      {
	$pm = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `status`='R' AND `type`='".$_POST['type']."'".$strSql." ORDER BY ".$_GET['lista'].$strOrder, 30, (30 * ($page - 1)));
      }
    elseif (isset($_POST['szukany1']))
      {
	$pm = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `status`='R' AND `type` NOT IN ('I', 'O', 'Q') AND name=".$strSearch." ORDER BY ".$_GET['lista'].$strOrder, 30, (30 * ($page - 1)));
	$_POST['szukany'] = $_POST['szukany1'];
      }
    else 
      {
	$pm = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `status`='R' AND `type` NOT IN ('I', 'O', 'Q') AND MATCH(`name`) AGAINST (".$strSearch." IN BOOLEAN MODE) ORDER BY ".$_GET['lista'].$strOrder, 30,  (30 * ($page - 1)));
      }
    if (!empty($_POST['szukany']))
      {
	$strType2 .= '&amp;search='.$_POST['szukany'];
	$strType .= '&amp;search='.$_POST['szukany'];
      }
    else
      {
	$_POST['szukany'] = '';
      }
    $arrname = array();
    $arrpower = array();
    $arrdur = array();
    $arrmaxdur = array();
    $arrspeed = array();
    $arragility = array();
    $arrcost = array();
    $arrowner = array();
    $arramount = array();
    $arrlevel = array();
    $arrseller = array();
    $arrFcost = array();
    $arrId = array();
    $i = 0;
    while (!$pm -> EOF) 
      {
	if ($pm -> fields['zr'] <= 0) 
	  {
	    $pm -> fields['zr'] = str_replace("-","",$pm -> fields['zr']);
	    $agility = "+".$pm -> fields['zr'];
	  } 
	elseif ($pm -> fields['zr'] > 0) 
	  {
	    $agility = "-".$pm -> fields['zr'];
	  }
	if ($pm -> fields['szyb'] > 0) 
	  {
	    $speed = "+".$pm -> fields['szyb'];
	  } 
	else 
	  {
	    $speed = 0;
	  }
	switch ($pm->fields['ptype'])
	  {
	  case 'D':
	    $pm->fields['name'] .= ' (Dynallca +'.$pm->fields['poison'].')';
	    break;
	  case 'N':
	    $pm->fields['name'] .= ' (Nutari +'.$pm->fields['poison'].')';
	    break;
	  case 'I':
	    $pm->fields['name'] .= ' (Illani +'.$pm->fields['poison'].')';
	    break;
	  default:
	    break;
	  }
	$arrname[$i] = $pm -> fields['name'];
	$arrpower[$i] = $pm -> fields['power'];
	if ($pm->fields['type'] != 'R')
	  {
	    $arrdur[$i] =  $pm -> fields['wt'];
	    $arrmaxdur[$i] = $pm -> fields['maxwt'];
	    $arramount[$i] = $pm -> fields['amount'];
	  }
	else
	  {
	    $arrdur[$i] = 1;
	    $arrmaxdur[$i] = 1;
	    $arramount[$i] = $pm->fields['wt'];
	  }
	$arrspeed[$i] = $speed;
	$arragility[$i] = $agility;
	$arrcost[$i] = $pm -> fields['cost'];
	$arrFcost[$i] = $pm->fields['cost'] * $arramount[$i];
	$arrowner[$i] = $pm -> fields['owner'];
	$arrlevel[$i] = $pm -> fields['minlev'];
	$seller = $db -> Execute("SELECT user FROM players WHERE id=".$pm -> fields['owner']);
	$arrseller[$i] = $seller -> fields['user'];
	$arrId[$i] = $pm->fields['id'];
	$seller -> Close();
	$pm -> MoveNext();
	$i = $i + 1;
      }
    $pm -> Close();
    $smarty -> assign(array("Name" => $arrname, 
			    "Power" => $arrpower, 
			    "Cost" => $arrcost, 
			    "Owner" => $arrowner, 
			    "Maxdur" => $arrmaxdur, 
			    "Durability" => $arrdur, 
			    "Speed" => $arrspeed, 
			    "Agility" => $arragility, 
			    "Amount" => $arramount, 
			    "Minlev" => $arrlevel,
			    "Iid" => $arrId,
			    "Pid" => $player->id,
			    "Tpages" => $pages,
			    "Tpage" => $page,
			    "Fcost" => $arrFcost,
			    "Fpage" => "Idź do strony:",
			    "Abuy" => A_BUY,
			    "Aadd" => A_ADD,
			    "Adelete" => A_DELETE,
			    "Achange" => A_CHANGE,
			    "Mlist" => $_GET['lista'],
			    "Atype" => $strType,
			    "Atype2" => $strType2,
			    "Asearch2" => $_POST['szukany'],
			    "Seller" => $arrseller));
}

/**
* Add oferts to market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    $rzecz = $db -> Execute("SELECT * FROM `equipment` WHERE `status`='U' AND `type` NOT IN ('I', 'O', 'Q') AND `owner`=".$player -> id);
    $arrname = array();
    $arrid = array();
    $arramount = array();
    $arrPower = array();
    $arrSpeed = array();
    $arrDur = array();
    $arrMaxduf = array();
    while (!$rzecz -> EOF) 
    {
        switch ($rzecz->fields['ptype'])
	  {
	  case 'D':
	    $rzecz->fields['name'] .= ' (Dynallca +'.$rzecz->fields['poison'].')';
	    break;
	  case 'N':
	    $rzecz->fields['name'] .= ' (Nutari +'.$rzecz->fields['poison'].')';
	    break;
	  case 'I':
	    $rzecz->fields['name'] .= ' (Illani +'.$rzecz->fields['poison'].')';
	    break;
	  default:
	    break;
	  }
        $arrname[] = $rzecz -> fields['name'];
        $arrid[] = $rzecz -> fields['id'];
	if ($rzecz->fields['type'] != 'R')
	  {
	    $arrDur[] =  $rzecz->fields['wt'];
	    $arrMaxdur[] = $rzecz->fields['maxwt'];
	    $arramount[] = $rzecz->fields['amount'];
	  }
	else
	  {
	    $arrDur[] = 1;
	    $arrMaxdur[] = 1;
	    $arramount[] = $rzecz->fields['wt'];
	  }
	$arrPower[] = $rzecz->fields['power'];
	$arrSpeed[] = $rzecz->fields['szyb'];
	if ($rzecz->fields['zr'] >= 0)
	  {
	    $arrAgi[] = $rzecz->fields['zr'] * -1;
	  }
	else
	  {
	    $arrAgi[] = '+'.$rzecz->fields['zr'] * -1;
	  }
        $rzecz -> MoveNext();
    }
    $rzecz -> Close();
    if (count($arrid) == 0)
    {
        error(NO_ITEMS);
    }
    $smarty -> assign (array("Name" => $arrname, 
                             "Itemid" => $arrid, 
                             "Amount" => $arramount,
			     "Ipower" => $arrPower,
			     "Ispeed" => $arrSpeed,
			     "Idur" => $arrDur,
			     "Imaxdur" => $arrMaxdur,
			     "Iagi" => $arrAgi,
                             "Addinfo" => ADD_INFO,
                             "Item" => ITEM,
                             "Aadd" => A_ADD,
                             "Iamount" => I_AMOUNT,
                             "Iamount2" => I_AMOUNT2,
                             "Icost" => I_COST,
			     "Iag" => "zr",
			     "Ispd" => "szyb",
			     "Addall" => "wszystkie posiadane"));
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!isset($_POST['cost'])) 
        {
            error("Podaj cenę przedmiotu.");
        }
	checkvalue($_POST['cost']);
	checkvalue($_POST['przedmiot']);
        $item = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_POST['przedmiot']." AND `status`='U' AND `type` NOT IN ('I', 'O', 'Q') AND `owner`=".$player -> id);
	if (!$item->fields['id'])
	  {
	    error("Nie posiadasz takiego przedmiotu.");
	  }
	if (!isset($_POST['addall']))
	  {
	    checkvalue($_POST['amount']);
	  }
	else
	  {
	    if ($item->fields['type'] != 'R')
	      {
		$_POST['amount'] = $item->fields['amount'];
	      }
	    else
	      {
		$_POST['amount'] = $item->fields['wt'];
	      }
	  }
	if ($item->fields['type'] != 'R')
	  {
	    if ($item -> fields['amount'] < $_POST['amount']) 
	      {
		error (NO_AMOUNT.$item -> fields['name']);
	      }
	    $amount = $item -> fields['amount'] - $_POST['amount'];
	    if ($amount > 0) 
	      {
		$db -> Execute("UPDATE equipment SET amount=".$amount." where id=".$item -> fields['id']);
	      } 
            else 
	      {
		$db -> Execute("DELETE FROM equipment WHERE id=".$item -> fields['id']);
	      }
	    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$item -> fields['name']."' AND wt=".$item -> fields['wt']." AND type='".$item -> fields['type']."' AND status='R' AND owner=".$player -> id." AND power=".$item -> fields['power']." AND zr=".$item -> fields['zr']." AND szyb=".$item -> fields['szyb']." AND maxwt=".$item -> fields['maxwt']." AND poison=".$item -> fields['poison']." AND ptype='".$item -> fields['ptype']."' AND `twohand`='".$item -> fields['twohand']."'");
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, status, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$item -> fields['name']."',".$item -> fields['power'].",'".$item -> fields['type']."',".$_POST['cost'].",".$item -> fields['zr'].",".$item -> fields['wt'].",".$item -> fields['minlev'].",".$item -> fields['maxwt'].",".$_POST['amount'].",'".$item -> fields['magic']."',".$item -> fields['poison'].",'R',".$item -> fields['szyb'].",'".$item -> fields['twohand']."','".$item -> fields['ptype']."', ".$item -> fields['repair'].")");
		$smarty -> assign("Message", YOU_ADD.$_POST['amount'].I_AMOUNT3.$item -> fields['name'].ON_MARKET.$_POST['cost'].FOR_GOLDS.". <a href=\"imarket.php?view=add\">".A_REFRESH."</a>");
	      } 
            else 
	      {
		require_once('includes/marketaddto.php');
		addtoitem($item -> fields['type'], $test -> fields['id'], $item -> fields['wt']);
		$smarty -> assign("Message", YOU_ADD.$_POST['amount'].I_AMOUNT3.$item -> fields['name']."</b>. <a href=\"imarket.php?view=add\">".A_REFRESH."</a>");
	      }
	  }
	else
	  {
	    if ($item->fields['wt'] < $_POST['amount']) 
	      {
		error (NO_AMOUNT.$item->fields['name']);
	      }
	    $amount = $item->fields['wt'] - $_POST['amount'];
	    if ($amount > 0) 
	      {
		$db -> Execute("UPDATE `equipment` SET `wt`=".$amount." where `id`=".$item -> fields['id']);
	      } 
            else 
	      {
		$db -> Execute("DELETE FROM `equipment` WHERE `id`=".$item -> fields['id']);
	      }
	    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$item -> fields['name']."' AND `type`='R' AND `status`='R' AND `owner`=".$player -> id." AND `power`=".$item -> fields['power']." AND `zr`=".$item -> fields['zr']." AND `szyb`=".$item -> fields['szyb']." AND `poison`=".$item -> fields['poison']." AND `ptype`='".$item -> fields['ptype']."' AND `twohand`='".$item -> fields['twohand']."'");
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, status, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$item -> fields['name']."',".$item -> fields['power'].",'".$item -> fields['type']."',".$_POST['cost'].",".$item -> fields['zr'].",".$_POST['amount'].",".$item -> fields['minlev'].",".$_POST['amount'].",1,'".$item -> fields['magic']."',".$item -> fields['poison'].",'R',".$item -> fields['szyb'].",'".$item -> fields['twohand']."','".$item -> fields['ptype']."', ".$item -> fields['repair'].")");
		$smarty -> assign("Message", YOU_ADD.$_POST['amount'].I_AMOUNT3.$item -> fields['name'].ON_MARKET.$_POST['cost'].FOR_GOLDS.". <a href=\"imarket.php?view=add\">".A_REFRESH."</a>");
	      } 
            else 
	      {
		require_once('includes/marketaddto.php');
		addtoitem($item -> fields['type'], $test -> fields['id'], $item -> fields['wt']);
		$smarty -> assign("Message", YOU_ADD.$_POST['amount'].I_AMOUNT3.$item -> fields['name']."</b>. <a href=\"imarket.php?view=add\">".A_REFRESH."</a>");
	      }
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
    $dwyc = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['wyc']." AND `status`='R'");
    if ($dwyc -> fields['owner'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    require_once('includes/marketdel.php');
    deleteitem($dwyc, $player -> id);
    $smarty -> assign("Message", YOU_DELETE." (<a href=\"imarket.php\">".A_BACK."</a>)");
}

/**
* Delete oferts from market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'del') 
{
    $objArm = $db -> Execute("SELECT * FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='R' AND `type` NOT IN ('I', 'O', 'Q')");
    while (!$objArm -> EOF)
    {
        $intTest = $db -> Execute("SELECT id FROM equipment WHERE name='".$objArm -> fields['name']."' AND wt=".$objArm -> fields['wt']." AND type='".$objArm -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$objArm -> fields['power']." AND zr=".$objArm -> fields['zr']." AND szyb=".$objArm -> fields['szyb']." AND maxwt=".$objArm -> fields['maxwt']." AND poison=".$objArm -> fields['poison']." AND cost=1 AND ptype='".$objArm -> fields['ptype']."' AND `twohand`='".$objArm -> fields['twohand']."'");
        if (!$intTest -> fields['id']) 
        {
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$objArm -> fields['name']."',".$objArm -> fields['power'].",'".$objArm -> fields['type']."',1,".$objArm -> fields['zr'].",".$objArm -> fields['wt'].",".$objArm -> fields['minlev'].",".$objArm -> fields['maxwt'].",".$objArm -> fields['amount'].",'".$objArm -> fields['magic']."',".$objArm -> fields['poison'].",".$objArm -> fields['szyb'].",'".$objArm -> fields['twohand']."','".$objArm -> fields['ptype']."', ".$objArm -> fields['repair'].")");
        } 
            else 
        {
            if ($objArm -> fields['type'] != 'R')
            {
                $db -> Execute("UPDATE equipment SET amount=amount+".$objArm -> fields['amount']." WHERE id=".$intTest -> fields['id']);
            }
                else
            {
                $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$objArm -> fields['wt']." WHERE `id`=".$intTest -> fields['id']);
            }
        }
        $intTest -> Close();
        $objArm -> MoveNext();
    } 
    $db -> Execute("DELETE FROM `equipment` WHERE `status`='R' AND `type` NOT IN ('I', 'O') AND `owner`=".$player -> id);
    $smarty -> assign("Message",YOU_DELETE." (<a href=\"imarket.php\">".A_BACK."</a>)");
}

/**
* Buy items from market
*/
if (isset($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $buy = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['buy']." AND `type` NOT IN ('I', 'O', 'Q') AND `status`='R'");
    if (!$buy -> fields['id']) 
    {
        error (NO_OFERTS);
    }
    if ($buy -> fields['owner'] == $player -> id) 
    {
        error (IS_YOUR);
    }
    if ($buy -> fields['zr'] <= 0) 
    {
        $agility = str_replace("-", "+", $buy->fields['zr']);
    } 
    elseif ($buy -> fields['zr'] > 0) 
    {
        $agility = "-".$buy -> fields['zr'];
    }
    if ($buy -> fields['szyb'] > 0) 
    {
        $speed = "+".$buy -> fields['szyb'];
    } 
        else 
    {
        $speed = 0;
    }
    if ($buy->fields['type'] != 'R')
      {
	$intAmount = $buy->fields['amount'];
	$intDur = $buy->fields['wt'];
	$intMaxdur = $buy->fields['maxwt'];
      }
    else
      {
	$intAmount = $buy->fields['wt'];
	$intDur = $buy->fields['wt'];
	$intMaxdur = $buy->fields['wt'];
      }
    $seller = $db -> Execute("SELECT user FROM players WHERE id=".$buy -> fields['owner']);    
    $smarty -> assign(array("Name" => $buy -> fields['name'], 
                            "Itemid" => $buy -> fields['id'], 
                            "Amount1" => $intAmount,
                            "Cost" => $buy -> fields['cost'], 
                            "Seller" => $seller -> fields['user'], 
                            "Sid" => $buy -> fields['owner'], 
                            "Power" => $buy -> fields['power'], 
                            "Dur" => $intDur, 
                            "MaxDur" => $intMaxdur, 
                            "Type" => $buy -> fields['type'], 
                            "Agi" => $agility, 
                            "Speed" => $speed,
                            "Item" => ITEM,
                            "Buyinfo" => BUY_INFO,
                            "Ipower" => I_POWER,
                            "Iagi" => I_AGI,
                            "Ispeed" => I_SPEED,
                            "Idur" => I_DUR,
                            "Aamount" => A_AMOUNT,
                            "Hamount" => H_AMOUNT,
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
	$price = $_POST['amount'] * $buy -> fields['cost'];
        if ($price > $player -> credits) 
        {
            error (NO_MONEY);
        }
	if ($buy->fields['type'] != 'R')
	  {
	    if ($_POST['amount'] > $buy -> fields['amount']) 
	      {
		error(NO_AMOUNT.$buy -> fields['name'].ON_MARKET);
	      }
	    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$buy -> fields['name']."' AND wt=".$buy -> fields['wt']." AND type='".$buy -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$buy -> fields['power']." AND zr=".$buy -> fields['zr']." AND szyb=".$buy -> fields['szyb']." AND maxwt=".$buy -> fields['maxwt']." AND poison=".$buy -> fields['poison']." AND cost=1 AND ptype='".$buy -> fields['ptype']."' AND `twohand`='".$buy -> fields['twohand']."'");
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$buy -> fields['name']."',".$buy -> fields['power'].",'".$buy -> fields['type']."',1,".$buy -> fields['zr'].",".$buy -> fields['wt'].",".$buy -> fields['minlev'].",".$buy -> fields['maxwt'].",".$_POST['amount'].",'".$buy -> fields['magic']."',".$buy -> fields['poison'].",".$buy -> fields['szyb'].",'".$buy -> fields['twohand']."','".$buy -> fields['ptype']."', ".$buy -> fields['repair'].")");
	      } 
            else 
	      {
		$db -> Execute("UPDATE equipment SET amount=amount+".$_POST['amount']." WHERE id=".$test -> fields['id']);
	      }
	    if ($_POST['amount'] == $buy -> fields['amount']) 
	      {
		$db -> Execute("DELETE FROM equipment WHERE id=".$buy -> fields['id']);
	      } 
            else 
	      {
		$db -> Execute("UPDATE equipment SET amount=amount-".$_POST['amount']." WHERE id=".$buy -> fields['id']);
	      }
	  }
	else
	  {
	    if ($_POST['amount'] > $buy->fields['wt']) 
	      {
		error(NO_AMOUNT.$buy->fields['name'].ON_MARKET);
	      }
	    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$buy -> fields['name']."' AND `type`='R' AND `status`='U' AND `owner`=".$player -> id." AND `power`=".$buy -> fields['power']." AND `zr`=".$buy -> fields['zr']." AND `szyb`=".$buy -> fields['szyb']." AND `poison`=".$buy -> fields['poison']." AND `cost`=1 AND `ptype`='".$buy -> fields['ptype']."' AND `twohand`='".$buy -> fields['twohand']."'");
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$buy -> fields['name']."',".$buy -> fields['power'].",'".$buy -> fields['type']."',1,".$buy -> fields['zr'].",".$_POST['amount'].",".$buy -> fields['minlev'].",".$_POST['amount'].",1,'".$buy -> fields['magic']."',".$buy -> fields['poison'].",".$buy -> fields['szyb'].",'".$buy -> fields['twohand']."','".$buy -> fields['ptype']."', ".$buy -> fields['repair'].")");
	      } 
            else 
	      {
		$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
	      }
	    if ($_POST['amount'] == $buy->fields['wt']) 
	      {
		$db -> Execute("DELETE FROM `equipment` WHERE `id`=".$buy -> fields['id']);
	      } 
            else 
	      {
		$db -> Execute("UPDATE `equipment` SET `wt`=`wt`-".$_POST['amount']." WHERE `id`=".$buy -> fields['id']);
	      }
	  }
        $test -> Close();
        $db -> Execute("UPDATE players SET bank=bank+".$price." WHERE id=".$buy -> fields['owner']);
        $db -> Execute("UPDATE players SET credits=credits-".$price." WHERE id=".$player -> id);
        $strDate = $db -> DBDate($newdate);
	$agility .= ' zr';
	$speed = "(".$speed." szyb)";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['owner'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['name']." (+".$buy->fields['power'].") ".$speed." (".$agility.")".YOU_GET.$price.TO_BANK."', ".$strDate.", 'M')");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$buy -> fields['name'].FOR_A.$price.GOLD_COINS);
    }
    $buy->Close();
}

/**
* List of all oferts on market
*/
if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    $_GET['limit'] = intval($_GET['limit']);
    if ($_GET['limit'] < 0)
      {
	error(ERROR);
      }
    $objAmount = $db -> Execute("SELECT `id` FROM `equipment` WHERE `status`='R' AND `type` NOT IN ('I', 'O', 'Q') GROUP BY `name`");
    $intAmount = $objAmount -> RecordCount();
    $objAmount -> Close();
    if (isset($_POST['previous']))
    {
        $_GET['limit'] = $_GET['limit'] - 30;
    }
    if (isset($_POST['next']))
    {
        $_GET['limit'] = $_GET['limit'] + 30;
    }
    if ($_GET['limit'] > $intAmount && $_GET['limit'] != 0)
    {
        error(ERROR);
    }
    $strNext = '';
    $strPrevious = '';
    $oferts = $db -> SelectLimit("SELECT `name` FROM `equipment` WHERE `status`='R' AND `type` NOT IN ('I', 'O', 'Q') GROUP BY `name`", 30, $_GET['limit']);
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
    if ($_GET['limit'] >= 30) 
    {
        $strPrevious = "<input type=\"submit\" name=\"previous\" value=\"".A_PREVIOUS."\"> ";
    }
    $intLimit = $_GET['limit'] + 30;
    if ($intAmount > 30 && $intLimit < $intAmount) 
    {
        $strNext = "<input type=\"submit\" name=\"next\" value=\"".A_NEXT."\">";
    }
    $strLinks = "<form method=\"post\" action=\"imarket.php?view=all&amp;limit=".$_GET['limit']."\">".$strPrevious.$strNext."</form>";
    $smarty -> assign(array("Name" => $arrname, 
                            "Amount" => $arramount, 
                            "Message" => "<br />(<a href=\"imarket.php\">".A_BACK."</a>)",
                            "Tlinks" => $strLinks,
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
$smarty -> display('imarket.tpl');

require_once("includes/foot.php"); 
?>
