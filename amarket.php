<?php
/**
 *   File functions:
 *   Astral market - add, buy astral components from other players
 *
 *   @name                 : amarket.php                            
 *   @copyright            : (C) 2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 08.01.2013
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

$title = "Astralny rynek";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/amarket.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

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
			    "Alist" => A_LIST,
			    "Aadd" => A_ADD,
			    "Aback2" => A_BACK2));
}

$arrNames = array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7, PLAN1, PLAN2, PLAN3, PLAN4, PLAN5, RECIPE1, RECIPE2, RECIPE3, RECIPE4, RECIPE5);
$arrNames2 = array(COMP1, COMP2, COMP3, COMP4, COMP5, COMP6, COMP7, CONST1, CONST2, CONST3, CONST4, CONST5, POTION1, POTION2, POTION3, POTION4, POTION5);

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
	$arrNames = array_merge($arrNames, $arrNames2);
	if (empty($_POST['szukany'])) 
	  {
	    $msel = $db -> Execute("SELECT count(`id`) FROM `amarket`");
	    $strSearch = '';
	  } 
        else 
	  {
	    $_POST['szukany'] = htmlspecialchars($_POST['szukany'], ENT_QUOTES);
	    $intKey = array_search($_POST['szukany'], $arrNames);
	    if ($intKey == NULL)
	      {
		for ($i = 0; $i < count($arrNames); $i++)
		  {
		    if (stripos($arrNames[$i], $_POST['szukany']) !== FALSE)
		      {
			$intKey = $i;
			break;
		      }
		  }
		if ($intKey == NULL)
		  {
		    $strSearch = 'A';
		  }
	      }
	    if ($intKey < 7)
	      {
		$strSearch = "M".$intKey;
	      }
	    if ($intKey > 6 && $intKey < 12)
	      {
		$intNumber = $intKey - 7;
		$strSearch = "P".$intNumber;
	      }
	    if ($intKey > 11 && $intKey < 17)
	      {
		$intNumber = $intKey - 12;
		$strSearch = "R".$intNumber;
	      }
	    if ($intKey > 16 && $intKey < 24)
	      {
		$intNumber = $intKey - 17;
		$strSearch = "C".$intNumber;
	      }
	    if ($intKey > 23 && $intKey < 29)
	      {
		$intNumber = $intKey - 24;
		$strSearch = "O".$intNumber;
	      }
	    if ($intKey > 28)
	      {
		$intNumber = $intKey - 29;
		$strSearch = "T".$intNumber;
	      }
	    $msel = $db -> Execute("SELECT count(`id`) FROM `amarket` WHERE `type`='".$strSearch."'");
	  }
	$oferty = $msel -> fields['count(`id`)'];
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
	if (!in_array($_GET['lista'], array('id', 'type', 'number', 'amount', 'cost', 'seller'))) 
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
	if (empty($_POST['szukany'])) 
	  {
	    $pm = $db -> SelectLimit("SELECT * FROM `amarket` ORDER BY ".$_GET['lista']." ".$_GET['order'], 30, (30 * ($page - 1)));
	  } 
	else 
	  {
	    $pm = $db -> SelectLimit("SELECT * FROM `amarket` WHERE `type`='".$strSearch."' ORDER BY ".$_GET['lista']." ".$_GET['order'], 30, (30 * ($page - 1)));
	    $_GET['order'] .= '&amp;search='.$_POST['szukany'];
	    $strOrder .= '&amp;search='.$_POST['szukany'];
	  }
	$arrname = array();
	$arramount = array();
	$arrcost = array();
	$arrseller = array();
	$arraction = array();
	$arruser = array();
	$arrNumber = array();
	$arrFcost = array();
	$i = 0;
	while (!$pm -> EOF) 
	  {
	    switch ($pm->fields['type'][0])
	      {
	      case 'M':
		$intKey = str_replace("M", "", $pm -> fields['type']);
		$intNumber = $intKey;
		$arrNumber[$i] = $pm -> fields['number'] + 1;
		break;
	      case 'P':
		$intKey = str_replace("P", "", $pm -> fields['type']);
		$intNumber = $intKey + 7;
		$arrNumber[$i] = $pm -> fields['number'] + 1;
		break;
	      case 'R':
		$intKey = str_replace("R", "", $pm -> fields['type']);
		$intNumber = $intKey + 12;
		$arrNumber[$i] = $pm -> fields['number'] + 1;
		break;
	      case 'C':
		$intKey = str_replace("C", "", $pm -> fields['type']);
		$intNumber = $intKey + 17;
		$arrNumber[$i] = '-';
		break;
	      case 'O':
		$intKey = str_replace("O", "", $pm -> fields['type']);
		$intNumber = $intKey + 24;
		$arrNumber[$i] = '-';
		break;
	      case 'T':
		$intKey = str_replace("T", "", $pm -> fields['type']);
		$intNumber = $intKey + 29;
		$arrNumber[$i] = '-';
	    break;
	      default:
		break;
	      }
	    $arrname[$i] = $arrNames[$intNumber];
	    $arramount[$i] = $pm -> fields['amount'];
	    $arrcost[$i] = $pm -> fields['cost'];
	    $arrFcost[$i] = $pm->fields['cost'] * $pm->fields['amount'];
	    $arrseller[$i] = $pm -> fields['seller'];
	    $seller = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$pm -> fields['seller']);
	    $arruser[$i] = $seller -> fields['user'];
	    $seller -> Close();
	    if ($player -> id == $pm -> fields['seller']) 
	      {
		$arraction[$i] = "<td><a href=\"market.php?view=myoferts&amp;type=amarket&amp;delete=".$pm->fields['id']."\">".A_DELETE."</a><br />
            <a href=\"market.php?view=myoferts&amp;type=amarket&amp;change=".$pm->fields['id']."\">".A_CHANGE."</a><br />
            <a href=\"market.php?view=myoferts&amp;type=rmarket&amp;add=".$pm->fields['id']."\">".A_ADD."</a>";
	      } 
	    else 
	      {
		$arraction[$i] = "<td>- <a href=\"amarket.php?buy=".$pm -> fields['id']."\">".A_BUY."</a>";
		if ($player -> clas == 'Złodziej')
		  {
		    $arraction[$i] = $arraction[$i]."<br />- <a href=\"amarket.php?steal=".$pm -> fields['id']."\">".A_STEAL."</a>";
		  }
	      }
	    $arraction[$i] = $arraction[$i]."</td></tr>";
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
				"Number" => $arrNumber,
				"Tpages" => $pages,
				"Tpage" => $page,
				"Fcost" => $arrFcost,
				"Aorder" => $_GET['order'],
				"Mlist" => $_GET['lista'],
				"Aorder2" => $strOrder,
				"Fpage" => "Idź do strony:",
				"Tastral" => ASTRAL,
				"Tamount" => T_AMOUNT,
				"Tcost" => "Cena szt / wszystko",
				"Tseller" => T_SELLER,
				"Tnumber" => T_NUMBER,
				"Toptions" => T_OPTIONS,
				"Astral" => ASTRAL,
				"Asearch" => A_SEARCH,
				"Aadd" => "Dodaj ofertę",
				"Viewinfo" => VIEW_INFO));
      }

    /**
     * Add ofert on market
     */
    elseif ($_GET['view'] == 'add') 
      {
	$smarty -> assign(array("Addinfo" => ADD_INFO,
				"Astral" => ASTRAL,
				"Herbname" => $arrNames,
				"Aname" => $arrNames2,
				"Hamount" => H_AMOUNT,
				"Hcost" => H_COST,
				"Aadd" => A_ADD,
				"Tadd" => T_ADD,
				"Tadd2" => T_ADD2,
				"Anumber" => A_NUMBER,
				"Addall" => "wszystkie posiadane",
				"Addofert" => 0));
	if (isset ($_GET['step']) && ($_GET['step'] == 'piece' || $_GET['step'] == 'component')) 
	  {
	    $_POST['name'] = intval($_POST['name']);
	    if ($_POST['name'] < 0 || $_POST['name'] > 16)
	      {
		error(ERROR);
	      }
	    checkvalue($_POST['number']);
	    checkvalue($_POST['cost']);
	    if ($_GET['step'] == 'piece')
	      {
		if ($_POST['name'] < 7)
		  {
		    $strName = 'M';
		  }
		if ($_POST['name'] > 6 && $_POST['name'] < 12)
		  {
		    $strName = 'P';
		  }
		if ($_POST['name'] > 11)
		  {
		    $strName = 'R';
		  }
		$strName2 = $arrNames[$_POST['name']];
	      }
            else
	      {
		if ($_POST['name'] < 7)
		  {
		    $strName = 'C';
		  }
		if ($_POST['name'] > 6 && $_POST['name'] < 12)
		  {
		    $strName = 'O';
		  }
		if ($_POST['name'] > 11)
		  {
		    $strName = 'T';
		  }
		$strName2 = $arrNames2[$_POST['name']];
	      }
	    $arrNumber = array(0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 0, 1, 2, 3, 4);
	    $strPiecename = $strName.$arrNumber[$_POST['name']];
	    $intNumber = $_POST['number'] - 1;
	    $objAmount = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'") or die($db -> ErrorMsg());
	    if (!isset($_POST['addall']))
	      {
		checkvalue($_POST['amount']);
	      }
	    else
	      {
		$_POST['amount'] = $objAmount->fields['amount'];
	      }
	    if (!$objAmount -> fields['amount'])
	      {
		error(NO_AMOUNT.$strName2);
	      }
	    if ($objAmount -> fields['amount'] < $_POST['amount'])
	      {
		error(NO_AMOUNT.$strName2);
	      }
	    $objTest = $db -> Execute("SELECT `id` FROM `amarket` WHERE `seller`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber);
	    if (!$objTest -> fields['id'])
	      {
		$db -> Execute("INSERT INTO `amarket` (`seller`, `type`, `number`, `amount`, `cost`) VALUES(".$player -> id.", '".$strPiecename."', ".$intNumber.", ".$_POST['amount'].", ".$_POST['cost'].")") or die($db -> ErrorMsg());
		if ($objAmount -> fields['amount'] == $_POST['amount'])
		  {
		    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
		  }
                else
		  {
		    $db -> Execute("UPDATE `astral` SET `amount`=`amount`-".$_POST['amount']." WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
		  }
		$smarty -> assign("Message", YOU_ADD." <a href=\"amarket.php?view=add\">".A_REFRESH."</a>");
	      }
            else
	      {
		$smarty -> assign(array("Addofert" => $objTest -> fields['id'],
					"Youwant" => YOU_WANT,
					"Ayes" => YES,
					"Herbname" => $_POST['name'],
					"Herbamount" => $_POST['amount'],
					"Astralnumber" => $_POST['number'],
					"Herbcost" => $_POST['cost'],
					"Step" => $_GET['step']));
		if (isset($_POST['ofert']))
		  {
		    checkvalue($_POST['ofert']);
		    require_once('includes/marketaddto.php');
		    addtoastral($objTest -> fields['id'], $objAmount -> fields['amount'], $player -> id, $strPiecename, $intNumber);
		    $smarty -> assign("Message", YOU_ADD." <a href=\"amarket.php?view=add\">".A_REFRESH."</a>");
		  }
	      }
	    $objTest -> Close();
	    $objAmount -> Close();
	  }
      }

    /**
     * Delete all oferts one player from market
     */
    elseif ($_GET['view'] == 'del') 
      {
	require_once('includes/marketdelall.php');
	deleteallastral($player -> id);
	$smarty -> assign("Message", YOU_DELETE." (<a href=\"amarket.php\">".A_BACK."</a>)");
      }

    /**
     * List of all ofers on market
     */
    elseif ($_GET['view'] == 'all') 
      {
	$oferts = $db -> Execute("SELECT `type` FROM `amarket` GROUP BY `type`");
	$arrname = array();
	$arramount = array();
	$arrNames = array_merge($arrNames, $arrNames2);
	while (!$oferts -> EOF) 
	  {
	    switch ($oferts->fields['type'][0])
	      {
	      case 'M':
		$intKey = str_replace("M", "", $oferts -> fields['type']);
		$intNumber = $intKey;
		break;
	      case 'P':
		$intKey = str_replace("P", "", $oferts -> fields['type']);
		$intNumber = $intKey + 7;
		break;
	      case 'R':
		$intKey = str_replace("R", "", $oferts -> fields['type']);
		$intNumber = $intKey + 12;
		break;
	      case 'C':
		$intKey = str_replace("C", "", $oferts -> fields['type']);
		$intNumber = $intKey + 17;
		break;
	      case 'O':
		$intKey = str_replace("O", "", $oferts -> fields['type']);
		$intNumber = $intKey + 24;
		break;
	      case 'T':
		$intKey = str_replace("T", "", $oferts -> fields['type']);
		$intNumber = $intKey + 29;
		break;
	      default:
		break;
	      }
	    $arrname[] = $arrNames[$intNumber];
	    $query = $db -> Execute("SELECT count(`id`) FROM `amarket` WHERE `type`='".$oferts -> fields['type']."'");
            $arramount[] = $query->fields['count(`id`)'];
	    $query -> Close();
	    $oferts -> MoveNext();
	  }
	$oferts -> Close();
	$smarty -> assign(array("Name" => $arrname, 
				"Amount" => $arramount, 
				"Message" => "<br />(<a href=\"amarket.php\">".A_BACK."</a>)",
				"Listinfo" => LIST_INFO,
				"Hname" => H_NAME,
				"Hamount" => H_AMOUNT,
				"Haction" => H_ACTION,
				"Ashow" => A_SHOW));
      }
  }

/**
* Buy components from market
*/
if (isset($_GET['buy'])) 
{
    checkvalue($_GET['buy']);
    $buy = $db -> Execute("SELECT * FROM `amarket` WHERE `id`=".$_GET['buy']) ;
    if (!$buy -> fields['id']) 
    {
        error (NO_OFERTS);
    }
    if ($buy -> fields['seller'] == $player -> id) 
    {
        error (IS_YOUR);
    }
    $seller = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$buy -> fields['seller']);
    switch ($buy->fields['type'][0])
      {
      case 'M':
	$intKey = str_replace("M", "", $buy -> fields['type']);
        $intNumber = $intKey;
	break;
      case 'P':
	$intKey = str_replace("P", "", $buy -> fields['type']);
        $intNumber = $intKey + 7;
	break;
      case 'R':
	$intKey = str_replace("R", "", $buy -> fields['type']);
        $intNumber = $intKey + 12;
	break;
      case 'C':
	$intKey = str_replace("C", "", $buy -> fields['type']);
        $intNumber = $intKey + 17;
        $buy -> fields['number'] = '-';
	break;
      case 'O':
	$intKey = str_replace("O", "", $buy -> fields['type']);
        $intNumber = $intKey + 24;
        $buy -> fields['number'] = '-';
	break;
      case 'T':
	$intKey = str_replace("T", "", $buy -> fields['type']);
        $intNumber = $intKey + 29;
        $buy -> fields['number'] = '-';
	break;
      default:
	break;
      }
    $arrNames = array_merge($arrNames, $arrNames2);
    $strName = $arrNames[$intNumber];
    $intAstralnumber = $buy -> fields['number'] + 1;
    $smarty -> assign(array("Name" => $strName, 
                            "Amount1" => $buy -> fields['amount'], 
                            "Itemid" => $buy -> fields['id'], 
                            "Cost" => $buy -> fields['cost'], 
                            "Seller" => $seller -> fields['user'], 
                            "Sid" => $buy -> fields['seller'],
                            "Anumber" => $intAstralnumber,
                            "Buyinfo" => BUY_INFO,
                            "Bherb" => ASTRAL,
                            "Oamount" => O_AMOUNT,
                            "Hcost" => H_COST,
                            "Hseller" => SELLER,
                            "Bamount" => B_AMOUNT,
                            "Tnumber" => T_NUMBER,
                            "Abuy" => A_BUY));
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
	checkvalue($_POST['amount']);
        $price = $_POST['amount'] * $buy -> fields['cost'];
        if ($price > $player -> credits) 
        {
            error (NO_MONEY);
        }
        if ($_POST['amount'] > $buy -> fields['amount']) 
        {
            error(NO_AMOUNT.$strName.ON_MARKET);
        }
        $db -> Execute("UPDATE `players` SET `bank`=`bank`+".$price." WHERE `id`=".$buy -> fields['seller']);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$price." WHERE `id`=".$player -> id);
        $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$buy -> fields['type']."' AND `number`=".$buy -> fields['number']." AND `location`='V'");
        if (!$objTest -> fields['amount'])
        {
            $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$player -> id.", '".$buy -> fields['type']."', ".$buy -> fields['number'].", ".$_POST['amount'].", 'V')");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$_POST['amount']." WHERE `owner`=".$player -> id." AND `type`='".$buy -> fields['type']."' AND `number`=".$buy -> fields['number']." AND `location`='V'");
        }
        $objTest -> Close();
        if ($_POST['amount'] == $buy -> fields['amount']) 
        {
            $db -> Execute("DELETE FROM `amarket` WHERE `id`=".$buy -> fields['id']);
        } 
            else 
        {
            $db -> Execute("UPDATE `amarket` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$buy -> fields['id']);
        }
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['seller'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$strName.YOU_GET.$price.TO_BANK."', ".$strDate.", 'M')");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$strName.FOR_A.$price.GOLD_COINS);
    }
    $buy->Close();
}

/**
 * Delete one ofert from market
 */
if (isset($_GET['wyc'])) 
{
    checkvalue($_GET['wyc']);
    $dwyc = $db -> Execute("SELECT * FROM `amarket` WHERE `id`=".$_GET['wyc']);
    if ($dwyc -> fields['seller'] != $player -> id) 
    {
        error (NOT_YOUR);
    }
    require_once('includes/marketdel.php');
    deleteastral($dwyc, $player -> id);
    $smarty -> assign("Message", YOU_DELETE." (<a href=\"amarket.php\">".A_BACK."</a>)");
}

/**
 * Steal components from market
 */
if (isset($_GET['steal']))
{
    checkvalue($_GET['steal']);
    $objOwner = $db -> Execute("SELECT `seller` FROM `amarket` WHERE `id`=".$_GET['steal']);
    if ($objOwner -> fields['seller'] == $player -> id || $player -> clas != 'Złodziej' || $player -> location == 'Lochy')
    {
        error(ERROR);
    }
    $objCrime = $db -> Execute("SELECT `astralcrime` FROM `players` WHERE `id`=".$player -> id);
    if ($objCrime -> fields['astralcrime'] == 'N') 
    {
        error (NO_CRIME);
    }
    if ($player->energy < 5)
      {
	error('Nie masz wystarczającej ilości energii.');
      }
    if ($player -> hp <= 0) 
    {
        error (YOU_DEAD);
    }
    require_once('includes/astralsteal.php');
    astralsteal($objOwner -> fields['seller'], 'R', 0, $_GET['steal']);
    $objOwner -> Close();
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
$smarty -> display ('amarket.tpl');

require_once("includes/foot.php");
?>
