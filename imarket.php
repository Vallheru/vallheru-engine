<?php
/**
 *   File functions:
 *   Items market
 *
 *   @name                 : imarket.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 13.08.2011
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
require_once("languages/".$player -> lang."/imarket.php");

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
        "Aadd" => A_ADD,
        "Adelete" => A_DELETE,
        "Alist" => A_LIST,
        "Aback2" => A_BACK2));
}

/**
* Search items on market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'szukaj') 
{
    $smarty -> assign(array("Sinfo" => S_INFO,
        "Sinfo2" => S_INFO2,
        "Item" => ITEM,
        "Asearch" => A_SEARCH));
}

/**
* Show oferts in market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'market') 
{
    if (empty($_POST['szukany'])) 
    {
        $msel = $db -> Execute("SELECT count(*) FROM `equipment` WHERE `status`='R' AND `type`!='I'");
    } 
        else 
    {
        $_POST['szukany'] = strip_tags($_POST['szukany']);
        $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
        $msel = $db -> Execute("SELECT count(*) FROM `equipment` WHERE `status`='R' AND `type`!='I' AND `name`=".$strSearch) or die($db -> ErrorMsg());
    }
    $przed = $msel -> fields['count(*)'];
    $msel -> Close();
    if ($przed == 0) 
    {
        error (NO_OFERTS);
    }
    if (!isset($_GET['limit']))
    {
        $_GET['limit'] = 0;
    }
    $smarty -> assign(array("Tname" => T_NAME,
                            "Tpower" => T_POWER,
                            "Tcost" => T_COST,
                            "Tseller" => T_SELLER,
                            "Tdur" => T_DUR,
                            "Tspeed" => T_SPEED,
                            "Tagi" => T_AGI,
                            "Tamount" => T_AMOUNT,
                            "Tlevel" => T_LEVEL,
                            "Viewinfo" => VIEW_INFO,
                            "Toptions" => T_OPTIONS));
    if ($_GET['limit'] < $przed) 
      {
	$_GET['limit'] = intval($_GET['limit']);
        if (!in_array($_GET['lista'], array('id', 'name', 'power', 'wt', 'szyb', 'zr', 'minlev', 'amount', 'cost', 'owner'))) 
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
        if (empty($_POST['szukany'])) 
        {
            $pm = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `status`='R' AND `type`!='I' ORDER BY ".$_GET['lista'].$strOrder, 30, $_GET['limit']);
        } 
            else 
        {
            $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
            $pm = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `status`='R' AND `name`=".$strSearch." AND `type`!='I' ORDER BY ".$_GET['lista'].$strOrder, 30, $_GET['limit']);
        }
        $arrname = array();
        $arrpower = array();
        $arrdur = array();
        $arrmaxdur = array();
        $arrspeed = array();
        $arragility = array();
        $arrcost = array();
        $arrowner = array();
        $arraction = array();
        $arramount = array();
        $arrlevel = array();
        $arrseller = array();
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
            $arrname[$i] = $pm -> fields['name'];
            $arrpower[$i] = $pm -> fields['power'];
            $arrdur[$i] =  $pm -> fields['wt'];
            $arrmaxdur[$i] = $pm -> fields['maxwt'];
            $arrspeed[$i] = $speed;
            $arragility[$i] = $agility;
            $arrcost[$i] = $pm -> fields['cost'];
            $arrowner[$i] = $pm -> fields['owner'];
            $arramount[$i] = $pm -> fields['amount'];
            $arrlevel[$i] = $pm -> fields['minlev'];
            $seller = $db -> Execute("SELECT user FROM players WHERE id=".$pm -> fields['owner']);
            $arrseller[$i] = $seller -> fields['user'];
            $seller -> Close();
            if ($player -> id == $pm -> fields['owner']) 
            {
                $arraction[$i] = "<td><a href=imarket.php?wyc=".$pm -> fields['id'].">".A_DELETE."</a></td></tr>";
            } 
                else 
            {
                $arraction[$i] = "<td><a href=imarket.php?buy=".$pm -> fields['id'].">".A_BUY."</a></td></tr>";
            }
            $pm -> MoveNext();
            $i = $i + 1;
        }
        $pm -> Close();
        $smarty -> assign(array("Name" => $arrname, 
                                "Power" => $arrpower, 
                                "Cost" => $arrcost, 
                                "Owner" => $arrowner, 
                                "Action" => $arraction, 
                                "Maxdur" => $arrmaxdur, 
                                "Durability" => $arrdur, 
                                "Speed" => $arrspeed, 
                                "Agility" => $arragility, 
                                "Amount" => $arramount, 
                                "Minlev" => $arrlevel, 
                                "Seller" => $arrseller));
        if (!isset($_POST['szukany'])) 
        {
            $_POST['szukany'] = '';
        }
        if ($_GET['limit'] >= 30) 
        {
            $lim = $_GET['limit'] - 30;
            $smarty -> assign ("Previous", "<form method=\"post\" action=\"imarket.php?view=market&limit=".$lim."&lista=".$_GET['lista']."\"><input type=\"hidden\" name=\"szukany\" value=\"".$_POST['szukany']."\"><input type=\"submit\" value=\"".A_PREVIOUS."\"></form> ");
        }
        $_GET['limit'] = $_GET['limit'] + 30;
        if ($przed > 30 && $_GET['limit'] < $przed) 
        {
            $smarty -> assign ("Next", " <form method=\"post\" action=\"imarket.php?view=market&limit=".$_GET['limit']."&lista=".$_GET['lista']."\"><input type=\"hidden\" name=\"szukany\" value=\"".$_POST['szukany']."\"><input type=\"submit\" value=\"".A_NEXT."\"></form>");
        }
    }
}

/**
* Add oferts to market
*/
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
{
    $rzecz = $db -> Execute("SELECT `id`, `name`, `amount`, `power`, `szyb` FROM `equipment` WHERE `status`='U' AND `type`!='I' AND `owner`=".$player -> id);
    $arrname = array();
    $arrid = array(0);
    $arramount = array();
    $arrPower = array();
    $arrSpeed = array();
    $i = 0;
    while (!$rzecz -> EOF) 
    {
        $arrname[$i] = $rzecz -> fields['name'];
        $arrid[$i] = $rzecz -> fields['id'];
        $arramount[$i] = $rzecz -> fields['amount'];
	$arrPower[$i] = $rzecz->fields['power'];
	$arrSpeed[$i] = $rzecz->fields['szyb'];
        $rzecz -> MoveNext();
        $i = $i + 1;
    }
    $rzecz -> Close();
    if (!$arrid[0])
    {
        error(NO_ITEMS);
    }
    $smarty -> assign (array("Name" => $arrname, 
                             "Itemid" => $arrid, 
                             "Amount" => $arramount,
			     "Ipower" => $arrPower,
			     "Ispeed" => $arrSpeed,
                             "Addinfo" => ADD_INFO,
                             "Item" => ITEM,
                             "Aadd" => A_ADD,
                             "Iamount" => I_AMOUNT,
                             "Iamount2" => I_AMOUNT2,
                             "Icost" => I_COST,
			     "Ispd" => "szyb"));
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!isset($_POST['cost'])) 
        {
            error(ERROR);
        }
        if (!ereg("^[1-9][0-9]*$", $_POST['cost'])) 
        {
            error(ERROR);
        }
        if (!ereg("^[1-9][0-9]*$", $_POST['przedmiot']) || !ereg("^[1-9][0-9]*$", $_POST['amount'])) 
        {
            error(ERROR);
        }
        $item = $db -> Execute("SELECT * FROM equipment WHERE id=".$_POST['przedmiot']);
        if ($item -> fields['amount'] < $_POST['amount']) 
        {
            error (NO_AMOUNT.$item -> fields['name']);
        }
        if ($item -> fields['type'] == 'I')
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
        $test -> Close();
    }
}

/**
* Delete selected ofert from market
*/
if (isset($_GET['wyc'])) 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['wyc'])) 
    {
        error (ERROR);
    }
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
    $objArm = $db -> Execute("SELECT * FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='R' AND `type`!='I'");
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
    $db -> Execute("DELETE FROM `equipment` WHERE `status`='R' AND `type`!='I' AND `owner`=".$player -> id);
    $smarty -> assign("Message",YOU_DELETE." (<a href=\"imarket.php\">".A_BACK."</a>)");
}

/**
* Buy items from market
*/
if (isset($_GET['buy'])) 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['buy'])) 
    {
        error (ERROR);
    }
    $buy = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['buy']." AND `type`!='I' AND `status`='R'");
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
        $buy -> fields['zr'] = str_replace("-","",$buy -> fields['zr']);
        $agility = "+".$buy -> fields['zr'];
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
    $seller = $db -> Execute("SELECT user FROM players WHERE id=".$buy -> fields['owner']);    
    $smarty -> assign(array("Name" => $buy -> fields['name'], 
                            "Itemid" => $buy -> fields['id'], 
                            "Amount1" => $buy -> fields['amount'], 
                            "Cost" => $buy -> fields['cost'], 
                            "Seller" => $seller -> fields['user'], 
                            "Sid" => $buy -> fields['owner'], 
                            "Power" => $buy -> fields['power'], 
                            "Dur" => $buy -> fields['wt'], 
                            "MaxDur" => $buy -> fields['maxwt'], 
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
    $buy -> Close();
    $seller -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'buy') 
    {
        if (!isset($_POST['amount'])) 
        {
            error(ERROR);
        }
        if (!ereg("^[1-9][0-9]*$", $_POST['amount'])) 
        {
            error (ERROR);
        }
        $buy = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['buy']." AND `type`!='I'");
        if ($_POST['amount'] > $buy -> fields['amount']) 
        {
            error(NO_AMOUNT.$buy -> fields['name'].ON_MARKET);
        }
        $price = $_POST['amount'] * $buy -> fields['cost'];
        if ($price > $player -> credits) 
        {
            error (NO_MONEY);
        }
        $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$buy -> fields['name']."' AND wt=".$buy -> fields['wt']." AND type='".$buy -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$buy -> fields['power']." AND zr=".$buy -> fields['zr']." AND szyb=".$buy -> fields['szyb']." AND maxwt=".$buy -> fields['maxwt']." AND poison=".$buy -> fields['poison']." AND cost=1 AND ptype='".$buy -> fields['ptype']."' AND `twohand`='".$buy -> fields['twohand']."'");
        if (!$test -> fields['id']) 
        {
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$buy -> fields['name']."',".$buy -> fields['power'].",'".$buy -> fields['type']."',1,".$buy -> fields['zr'].",".$buy -> fields['wt'].",".$buy -> fields['minlev'].",".$buy -> fields['maxwt'].",".$_POST['amount'].",'".$buy -> fields['magic']."',".$buy -> fields['poison'].",".$buy -> fields['szyb'].",'".$buy -> fields['twohand']."','".$buy -> fields['ptype']."', ".$buy -> fields['repair'].")");
        } 
            else 
        {
            if ($buy -> fields['type'] != 'R')
            {
                $db -> Execute("UPDATE equipment SET amount=amount+".$_POST['amount']." WHERE id=".$test -> fields['id']);
            }
                else
            {
                $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$buy -> fields['wt']." WHERE `id`=".$test -> fields['id']);
            }
        }
        $test -> Close();
        if ($_POST['amount'] == $buy -> fields['amount']) 
        {
            $db -> Execute("DELETE FROM equipment WHERE id=".$buy -> fields['id']);
        } 
            else 
        {
            $db -> Execute("UPDATE equipment SET amount=amount-".$_POST['amount']." WHERE id=".$buy -> fields['id']);
        }
        $db -> Execute("UPDATE players SET bank=bank+".$price." WHERE id=".$buy -> fields['owner']);
        $db -> Execute("UPDATE players SET credits=credits-".$price." WHERE id=".$player -> id);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$buy -> fields['owner'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$player -> id.L_ACCEPT2.$_POST['amount'].L_AMOUNT.$buy -> fields['name'].YOU_GET.$price.TO_BANK."', ".$strDate.")");
        $smarty -> assign("Message", YOU_BUY.$_POST['amount'].I_AMOUNT.$buy -> fields['name'].FOR_A.$price.GOLD_COINS);
    }
}

/**
* List of all oferts on market
*/
if (isset($_GET['view']) && $_GET['view'] == 'all') 
{
    if (!ereg("^[0-9]*$", $_GET['limit'])) 
    {
        error(ERROR);
    }
    $objAmount = $db -> Execute("SELECT `id` FROM `equipment` WHERE `status`='R' AND `type`!='I' GROUP BY `name`");
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
    $oferts = $db -> SelectLimit("SELECT `name` FROM `equipment` WHERE `status`='R' AND `type`!='I' GROUP BY `name`", 30, $_GET['limit']);
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
