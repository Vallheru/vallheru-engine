<?php
/**
 *   File functions:
 *   Players houses
 *
 *   @name                 : house.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 16.03.2012
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

$title = "Domy";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/house.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
 * Function return  house type (palace, village house, etc)
 */
function housetype($intHousevalue, $intHousebuild)
{
    $strHousename = H_RANK1;
    if ($intHousevalue > 5 && $intHousebuild > 3) 
    {
        $strHousename = H_RANK2;
    }
    if ($intHousevalue > 20 && $intHousebuild > 5) 
    {
        $strHousename = H_RANK3;
    }
    if ($intHousevalue > 50 && $intHousebuild > 10) 
    {
        $strHousename = H_RANK4;
    }
    if ($intHousevalue > 99 && $intHousebuild > 20) 
    {
        $strHousename = H_RANK5;
    }
    return $strHousename;
}

$house = $db -> Execute("SELECT * FROM houses WHERE location='".$player -> location."' AND (owner=".$player -> id." OR locator=".$player -> id.")");

/**
* Assign variables to template
*/
$smarty -> assign(array("Message" => '', 
    "Bedroomlink" => '', 
    "Locatorlink" => '', 
    "Buildbed" => '', 
    "Buildwardrobe" => '', 
    "Upgrade" => '', 
    "Wardrobelink" => '', 
    "Buildhouse" => ''));

/**
* Buy areas for house
*/
if (isset ($_GET['action']) && $_GET['action'] == 'land') 
{
    if (!$house -> fields['id']) 
    {
        $cost = COST1;
    } 
        else 
    {
        $cost1 = $house -> fields['size'] * 1000;
        $cost = $cost1.GOLD_COINS;
    }
    $smarty -> assign (array("Cost" => $cost,
        "Landinfo" => LAND_INFO,
        "Buya" => BUY_A,
        "Aback" => A_BACK));
    if (isset ($_GET['step']) && $_GET['step'] == 'buy') 
    {
        if (!$house -> fields['id']) 
        {
            if ($player -> platinum < 20) 
            {
                error (NO_MITH);
            }
            $db -> Execute("INSERT INTO houses (owner, location) VALUES(".$player -> id.", '".$player -> location."')") or error ("Nie mogÄ dodaÄ ziemi!");
            $db -> Execute("UPDATE players SET platinum=platinum-20 WHERE id=".$player -> id);
            error (BUY_AREA."<a href=house.php?action=build>".WORKSHOP."</a>".FOR_A);
        } 
            else 
        {
            if ($player -> credits < $cost1) 
            {
                error (NO_GOLD);
            }
            $db -> Execute("UPDATE houses SET size=size+1 WHERE id=".$house -> fields['id']);
            $db -> Execute("UPDATE players SET credits=credits-".$cost1." WHERE id=".$player -> id);
            error (BUY_AREA2);
        }
    }
}

/**
* Builder workshop
*/
if(isset ($_GET['action']) && $_GET['action'] == 'build') 
{
    $smarty -> assign(array("Points" => $house -> fields['points'],
                            "Buildinfo" => BUILD_INFO,
                            "Buildinfo2" => BUILD_INFO2,
                            "Aback" => A_BACK));
    if ($house -> fields['points'] == 0) 
    {
        error (NO_POINTS);
    }
    if ($house -> fields['build'] == 0) 
    {
        $smarty -> assign ("Buildhouse", "<a href=house.php?action=build&amp;step=new>".B_HOUSE);
    } 
        else 
    {
        if ($house -> fields['build'] < $house -> fields['size'] && $house -> fields['points'] > 9) 
        {
            $cost = 1000 * $house -> fields['build'];
            $smarty -> assign ("Buildhouse", "<a href=house.php?action=build&amp;step=add>".U_HOUSE.$cost.GOLD_COINS."<br />");
        }
        if ($house -> fields['used'] < $house -> fields['build'] && $house -> fields['points'] > 9) 
        {
            if ($house -> fields['bedroom'] == 'N') 
            {
                $smarty -> assign ("Buildbed", "<a href=house.php?action=build&amp;step=bedroom>".B_BEDROOM);
            }
            $cost = $house -> fields['wardrobe'] * 1000;
            if ($cost == 0) 
            {
                $cost = 1000;
            }
            $smarty -> assign ("Buildwardrobe", "<a href=house.php?action=build&amp;step=wardrobe>".B_WARDROBE.$cost.GOLD_COINS."<br />");
        }
        if ($house -> fields['points'] > 0) 
        {
            $smarty -> assign ("Upgrade", "<a href=house.php?action=build&amp;step=upgrade>".HOUSE_B."</a><br />");
        }
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'new')
    {
        if (!$house -> fields['id']) 
        {
            error (NO_AREA);
        }
        if ($house -> fields['build'] > 0) 
        {
            error (YOU_HAVE);
        }
        if ($player -> credits < 1000) 
        {
            error (NO_GOLD);
        }
        if ($house -> fields['points'] < 10) 
        {
            error (NO_POINTS);
        }
        $smarty -> assign(array("Hname" => H_NAME,
            "Abuild" => A_BUILD));
        if (isset ($_GET['step2']) && $_GET['step2'] == 'make') 
        {
	    if (!isset($_POST['name']))
	      {
		$_POST['name'] = '';
	      }
            $_POST['name'] = strip_tags($_POST['name']);
            $strName = $db -> qstr($_POST['name'], get_magic_quotes_gpc());
            $db -> Execute("UPDATE `houses` SET `name`=".$strName.", `build`=`build`+1, `points`=`points`-10 WHERE `id`=".$house -> fields['id']);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`-1000 WHERE `id`=".$player -> id);
            error (YOU_BUILD);
        }
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'add') 
    {
        if (!$house -> fields['id']) {
            error (NO_AREA);
        }
        if ($house -> fields['size'] == $house -> fields['build']) 
        {
            error (NO_FIELDS);
        }
        $cost = 1000 * $house -> fields['build'];
        if ($player -> credits < $cost) 
        {
            error (NO_GOLD);
        }
        if ($house -> fields['points'] < 10) 
        {
            error (NO_POINTS);
        }
        $house -> fields['value'] = $house -> fields['value'] - 10;
        if ($house -> fields['value'] < 1) 
        {
            $house -> fields['value'] = 1;
        }
        $db -> Execute("UPDATE houses SET build=build+1, points=points-10, value=".$house -> fields['value']." WHERE id=".$house -> fields['id']);
        $db -> Execute("UPDATE players SET credits=credits-".$cost." WHERE id=".$player -> id);
        error (YOU_UPGRADE);
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'bedroom') 
    {
        if (!$house -> fields['id']) 
        {
            error (NO_HOUSE);
        }
        if ($house -> fields['used'] == $house -> fields['build']) 
        {
            error (NO_FREE);
        }
        if ($house -> fields['bedroom'] == 'Y') 
        {
            error (YOU_HAVE);
        }
        if ($player -> credits < 10000) 
        {
            error (NO_GOLD);
        }
        if ($house -> fields['points'] < 10) 
        {
            error (NO_POINTS2);
        }
        $db -> Execute("UPDATE houses SET bedroom='Y', points=points-10, used=used+1 WHERE id=".$house -> fields['id']);
        $db -> Execute("UPDATE players SET credits=credits-10000 WHERE id=".$player -> id);
        error (YOU_BUILD);
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'wardrobe') 
    {
        if (!$house -> fields['id']) 
        {
            error (NO_HOUSE);
        }
        if ($house -> fields['used'] == $house -> fields['build']) 
        {
            error (NO_FREE);
        }
        $cost = $house -> fields['wardrobe'] * 1000;
        if ($cost == 0) 
        {
            $cost = 1000;
        }
        if ($player -> credits < $cost) 
        {
            error (NO_GOLD);
        }
        if ($house -> fields['points'] < 10) 
        {
            error (NO_POINTS2);
        }
        $db -> Execute("UPDATE houses SET wardrobe=wardrobe+1, points=points-10, used=used+1 WHERE id=".$house -> fields['id']);
        $db -> Execute("UPDATE players SET credits=credits-".$cost." WHERE id=".$player -> id);
        error (YOU_BUILD);
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'upgrade') 
    {
        if (!$house -> fields['id']) 
        {
            error (NO_HOUSE);
        }
        if ($house -> fields['points'] < 1) 
        {
            error (NO_POINTS2);
        }
        $smarty -> assign(array("Upginfo" => UPG_INFO,
            "Upgrade3" => UPGRADE,
            "Upgrade2" => UPGRADE2,
            "Awork" => A_WORK));
        if (isset ($_GET['step2']) && $_GET['step2'] == 'make') 
        {
	    checkvalue($_POST['points']);
            if ($_POST['points'] > $house -> fields['points']) 
            {
                error (NO_POINTS);
            }
            $cost = 1000 * $_POST['points'];
            if ($player -> credits < $cost) 
            {
                error (NO_GOLD);
            }
            $db -> Execute("UPDATE players SET credits=credits-".$cost." WHERE id=".$player -> id);
            $db -> Execute("UPDATE houses SET points=points-".$_POST['points'].", value=value+".$_POST['points']." WHERE id=".$house -> fields['id']);
            error (YOU_UPGRADE);
        }
    }
}

/**
* List of best players houses (50 houses max on list)
*/
if (isset ($_GET['action']) && $_GET['action'] == 'list') 
{
    $houses = $db -> SelectLimit("SELECT * FROM houses WHERE build>0 AND owner>0 AND location='".$player -> location."' ORDER BY build DESC", 50);
    $arrid = array();
    $arrowner = array();
    $arrname = array();
    $arrbuild = array();
    $arrtype = array();
    $arrlocator = array();
    $i = 0;
    while (!$houses -> EOF) {
        $arrid[$i] = $houses -> fields['id'];
        $arrowner[$i] = $houses -> fields['owner'];
        $arrname[$i] = $houses -> fields['name'];
        $arrbuild[$i] = $houses -> fields['build'];
        if ($houses -> fields['locator']) 
        {
            $arrlocator[$i] = "<a href=\"view.php?view=".$houses -> fields['locator']."\">".$houses -> fields['locator']."</a>";
        } 
            else 
        {
            $arrlocator[$i] = L_EMPTY;
        }
        $arrtype[$i] = housetype($houses -> fields['value'], $houses -> fields['build']);
        $houses -> MoveNext();
        $i = $i + 1;
    }
    $houses -> Close();
    $smarty -> assign(array("Housesname" => $arrname, 
        "Housesid" => $arrid, 
        "Housesowner" => $arrowner, 
        "Housesbuild" => $arrbuild, 
        "Housestype" => $arrtype, 
        "Locator" => $arrlocator,
        "Hname" => H_NAME,
        "Hnumber" => H_NUMBER,
        "Htype" => H_TYPE,
        "Hsize" => H_SIZE,
        "Howner" => H_OWNER,
        "Hlocator" => H_LOCATOR,
        "Aback" => A_BACK));
}

/**
* List of houses for sale
*/
if (isset ($_GET['action']) && $_GET['action'] == 'rent') 
{
    $houses = $db -> Execute("SELECT * FROM houses WHERE owner=0 AND location='".$player -> location."' ORDER BY build DESC");
    $arrid = array();
    $arrname = array();
    $arrbuild = array();
    $arrtype = array();
    $arrlink = array();
    $arrcost = array();
    $arrseller = array();
    $i = 0;
    while (!$houses -> EOF) 
    {
        $arrid[$i] = $houses -> fields['id'];
        $arrname[$i] = $houses -> fields['name'];
        $arrbuild[$i] = $houses -> fields['build'];
        $arrcost[$i] = $houses -> fields['cost'];
        $arrseller[$i] = $houses -> fields['seller'];
        $arrtype[$i] = housetype($houses -> fields['value'], $houses -> fields['build']);
        if ($player -> id == $houses -> fields['seller']) 
        {
            $arrlink[$i] = "<a href=\"house.php?action=rent&amp;back=".$houses -> fields['id']."\">".YOUR_OFERT."</a>";
        } 
            elseif ($house -> fields['id']) 
        {
            $arrlink[$i] = L_EMPTY;
        } 
            else 
        {
            $arrlink[$i] = "<a href=\"house.php?action=rent&amp;buy=".$houses -> fields['id']."\">".A_BUY."</a>";
        }
        $houses -> MoveNext();
        $i = $i + 1;
    }
    $houses -> Close();
    $smarty -> assign(array("Housesname" => $arrname, 
        "Housesid" => $arrid, 
        "Housesseller" => $arrseller, 
        "Housesbuild" => $arrbuild, 
        "Housestype" => $arrtype, 
        "Housescost" => $arrcost, 
        "Houseslink" => $arrlink,
        "Hnumber" => H_NUMBER,
        "Hseller" => H_SELLER,
        "Hname" => H_NAME,
        "Hsize" => H_SIZE,
        "Htype" => H_TYPE,
        "Hcost" => H_COST,
        "Hoption" => H_OPTION,
        "Aback" => A_BACK));
    if (isset($_GET['buy'])) 
    {
	checkvalue($_GET['buy']);
        if ($house -> fields['id']) 
        {
            error(YOU_HAVE);
        }
        $buy = $db -> Execute("SELECT id, owner, cost, seller FROM houses WHERE id=".$_GET['buy']);
        if (!$buy -> fields['id']) 
        {
            error(NO_HOUSE);
        }
        if ($buy -> fields['owner']) 
        {
            error(NOT_FOR_SALE);
        }
        if ($player -> credits < $buy -> fields['cost']) 
        {
            error(NO_GOLD);
        }
        $db -> Execute("UPDATE players SET credits=credits-".$buy -> fields['cost']." WHERE id=".$player -> id);
        $db -> Execute("UPDATE players SET bank=bank+".$buy -> fields['cost']." WHERE id=".$buy -> fields['seller']);
        $db -> Execute("UPDATE houses SET cost=0, seller=0, owner=".$player -> id." WHERE id=".$buy -> fields['id']);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['seller'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$buy -> fields['cost'].L_BANK."', ".$strDate.", 'H')");
        $smarty -> assign("Message", YOU_BUY);
        $buy -> Close();
    }
    if (isset($_GET['back']))
    {
	checkvalue($_GET['back']);
        if ($house -> fields['id']) 
        {
            error(YOU_HAVE);
        }
        $buy = $db -> Execute("SELECT id, owner, seller FROM houses WHERE id=".$_GET['back']);
        if (!$buy -> fields['id']) 
        {
            error(NO_HOUSE);
        }
        if ($buy -> fields['owner']) 
        {
            error(NOT_FOR_SALE);
        }
        if ($buy -> fields['seller'] != $player -> id)
        {
            error(NOT_YOUR);
        }
        $db -> Execute("UPDATE houses SET cost=0, seller=0, owner=".$player -> id." WHERE id=".$buy -> fields['id']);
        $smarty -> assign("Message", YOU_WITHDRAW);
        $buy -> Close();
    }
}

/**
* Player house
*/
if (isset ($_GET['action']) && $_GET['action'] == 'my') 
{
    $smarty -> assign("Aback", A_BACK);
    if (!$house -> fields['id']) 
    {
        error (NO_HOUSE);
    }
    if (!isset ($_GET['step']) && !isset ($_GET['step2'])) 
    {
        $homename = housetype($house -> fields['value'], $house -> fields['build']);
        if ($house -> fields['bedroom'] == 'Y') 
        {
            $smarty -> assign ("Bedroom", YES);
        } 
            else 
        {
            $smarty -> assign ("Bedroom", NO);
        }
        $unused = $house -> fields['build'] - $house -> fields['used'];
        $amount = $db -> Execute("SELECT SUM(`amount`) FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='H' AND `location`='".$player->location."'") or die($db->ErrorMsg());
	if (!$amount->fields['SUM(`amount`)'])
	  {
	    $intAmount = 0;
	  }
	else
	  {
	    $intAmount = $amount->fields['SUM(`amount`)'];
	  }
	$amount->Close();
        $smarty -> assign(array("Name" => $house -> fields['name'], 
                                "Size" => $house -> fields['size'], 
                                "Build" => $house -> fields['build'], 
                                "Value" => $house -> fields['value'], 
                                "Housename" => $homename, 
                                "Unused" => $unused, 
                                "Wardrobe" => $house -> fields['wardrobe'], 
                                "Items" => $intAmount,
                                "Houseinfo" => HOUSE_INFO,
                                "Hname" => H_NAME,
                                "Hsize" => H_SIZE,
                                "Howner" => H_OWNER,
                                "Hlocator" => H_LOCATOR,
                                "Lamount" => L_AMOUNT,
                                "Frooms" => F_ROOMS,
                                "Hvalue" => H_VALUE,
                                "Ibedroom" => I_BEDROOM,
                                "Wamount" => W_AMOUNT,
                                "Iamount" => I_AMOUNT,
                                "Cname" => C_NAME));
        if ($house -> fields['locator']) 
        {
            $smarty -> assign(array("Locator" => "<a href=\"view.php?view=".$house -> fields['locator']."\">".$house -> fields['locator']."</a>", 
                                    "Locleave" => "- <a href=\"house.php?action=my&amp;step=leave\">".A_LEAVE."</a><br />"));
        } 
            else 
        {
            $smarty -> assign(array("Locator" => L_EMPTY, 
                                    "Locleave" => ''));
        }
        if ($house -> fields['bedroom'] == 'Y') 
        {
            $smarty -> assign ("Bedroomlink", "- <a href=house.php?action=my&amp;step=bedroom>".GO_TO_BED."</a><br />");
        }
        if ($house -> fields['wardrobe'] > 0) 
        {
            $smarty -> assign ("Wardrobelink", "- <a href=house.php?action=my&amp;step=wardrobe>".GO_TO_WAR."</a><br />");
        }
        if ($house -> fields['build'] > 3 && $player -> id == $house -> fields['owner']) 
        {
            $smarty -> assign("Locatorlink", "- <a href=\"house.php?action=my&amp;step=locator\">".A_LOCATOR."</a><br />");
        }
        if ($player -> id == $house -> fields['owner']) 
        {
            $smarty -> assign("Sellhouse", "- <a href=\"house.php?action=my&amp;step=sell\">".A_SELL."</a><br />");
        } 
            else 
        {
            $smarty -> assign("Sellhouse", '');
        }
    }
    /**
    * Leave house (locator)
    */
    if (isset($_GET['step']) && $_GET['step'] == 'leave')
    {
        if ($player -> id != $house -> fields['locator'])
        {
            error(ERROR);
        }
        if (!isset($_GET['step2']))
        {
            $smarty -> assign(array("Youwant" => YOU_WANT,
                                    "Yes" => YES));
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'confirm')
        {
            $db -> Execute("UPDATE `houses` SET `locator`=0 WHERE `id`=".$house -> fields['id']);
            error(YOU_LEAVE);
        }
    }
    /**
    * Set house for sale
    */
    if (isset($_GET['step']) && $_GET['step'] == 'sell') 
    {
        $smarty -> assign(array("Sellinfo" => SELL_INFO,
                                "Housesale" => HOUSE_SALE,
                                "Goldcoins" => GOLD_COINS,
                                "Asend" => A_SEND));
        if ($player -> id != $house -> fields['owner']) 
        {
            error (ONLY_OWNER);
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'sell') 
        {
	    checkvalue($_POST['cost']);
            $db -> Execute("UPDATE houses SET cost=".$_POST['cost'].", seller=".$player -> id.", owner=0, locator=0 WHERE id=".$house -> fields['id']);
            $smarty -> assign("Message", YOU_SELL.$_POST['cost'].GOLD_COINS.".");
        }
    } 
    /**
     * Add/delete locator to/from house
     */
    if (isset($_GET['step']) && $_GET['step'] == 'locator') 
    {
        if ($player -> id != $house -> fields['owner']) 
        {
            error (ONLY_OWNER);
        }
        $smarty -> assign(array("Locid" => $house -> fields['locator'],
            "Oadd" => O_ADD,
            "Odelete" => O_DELETE,
            "Second" => SECOND,
            "Lid2" => L_ID,
            "Amake" => A_MAKE));
        if (isset($_GET['step2']) && $_GET['step2'] == 'change') 
        {
	    checkvalue($_POST['lid']);
            if ($_POST['loc'] == 'add') 
            {
                if ($house -> fields['locator']) 
                {
                    error (YOU_HAVE);
                }
                $test = $db -> Execute("SELECT `id` FROM `houses` WHERE `owner`=".$_POST['lid']." AND `location`='".$player -> location."'");
                if ($test -> fields['id']) 
                {
                    error (BAD_PL);
                }
                $test = $db -> Execute("SELECT `id` FROM `houses` WHERE `locator`=".$_POST['lid']." AND `location`='".$player -> location."'");
                if ($test -> fields['id']) 
                {
                    error(LIVE_ANOTHER);
                }
                $test = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_POST['lid']);
                if (!$test -> fields['id']) 
                {
                    error(NO_PLAYER);
                }
                $test -> Close();
                $db -> Execute("UPDATE `houses` SET `locator`=".$_POST['lid']." WHERE `id`=".$house -> fields['id']);
                $smarty -> assign("Message", YOU_ADD);
                $strLog = YOU_GET;
            }
            if ($_POST['loc'] == 'delete') 
            {
                if (!$house -> fields['locator']) 
                {
                    error (NO_LOC);
                }
                if ($_POST['lid'] != $house -> fields['locator']) 
                {
                    error (NO_LOC2);
                }
                $db -> Execute("UPDATE `houses` SET `locator`=0 WHERE `id`=".$house -> fields['id']);
                $smarty -> assign("Message", YOU_DELETE);
                $strLog = YOU_FIRED;
            }
            $strDate = $db -> DBDate($newdate);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['lid'].",'".$strLog."<b><a href=\"view.php?view=".$player -> id."\">".$player -> user."</a></b>.', ".$strDate.", 'H')");
        }
    }
    /**
    * Rename house
    */
    if (isset ($_GET['step']) && $_GET['step'] == 'name') 
    {
        $smarty -> assign(array("Achange" => A_CHANGE,
            "Ona" => ON_A));
        if ($player -> id != $house -> fields['owner']) 
        {
            error (ONLY_OWNER);
        }
        if (isset ($_GET['step2']) && $_GET['step2'] == 'change') 
        {
            if (empty ($_POST['name'])) 
            {
                error (EMPTY_NAME);
            }
            $_POST['name'] = strip_tags($_POST['name']);
            $strName = $db -> qstr($_POST['name'], get_magic_quotes_gpc());
            $db -> Execute("UPDATE houses SET name=".$strName." WHERE id=".$house -> fields['id']);
            error (YOU_CHANGE.$_POST['name']);
        }
    }
    
    /**
    * Rest in house
    */
    if (isset ($_GET['step']) && $_GET['step'] == 'bedroom') 
    {
        if ($house -> fields['bedroom'] == 'N') 
        {
            error (NO_BEDROOM);
        }
        $smarty -> assign(array("Id" => $player -> id,
            "Bedinfo" => BED_INFO,
            "Arest" => A_REST,
            "Asleep" => A_SLEEP));
        if (isset ($_GET['step2']) && $_GET['step2'] == 'rest') 
        {
            if ($player -> hp == 0) 
            {
                error ("<br /><br />".YOU_DEAD);
            }
            if ($player -> race == '' || $player -> clas == '')
            {
                error(NO_RACE);
            }
            $objTest = $db -> Execute("SELECT `houserest` FROM `players` WHERE `id`=".$player -> id);
            if ($objTest -> fields['houserest'] == 'Y') 
            {
                error ("<br /><br />".ONLY_ONCE);
            }
            $objTest -> Close();
            $db -> Execute("UPDATE `players` SET `houserest`='Y' WHERE id=".$player -> id);
            $roll = rand(1, 100);
            if ($roll > 5) 
            {
                $gainenergy =  ceil(($player -> max_energy / 100) * $house -> fields['value']);
                $gainhp = ceil(($player -> max_hp / 100) * $house -> fields['value']);
		$arrEquip = $player -> equipment();
		$arrRings = array('inteligencji', 'woli');
		$arrStat = array('inteli', 'wisdom');
		if ($arrEquip[9][0])
		  {
		    $arrRingtype = explode(" ", $arrEquip[9][1]);
		    $intAmount = count($arrRingtype) - 1;
		    $intKey = array_search($arrRingtype[$intAmount], $arrRings);
		    if ($intKey != NULL)
		      {
			$strStat = $arrStat[$intKey];
			$player -> $strStat = $player -> $strStat + $arrEquip[9][2];
		      }
		  }
		if ($arrEquip[10][0])
		  {
		    $arrRingtype = explode(" ", $arrEquip[10][1]);
		    $intAmount = count($arrRingtype) - 1;
		    $intKey = array_search($arrRingtype[$intAmount], $arrRings);
		    if ($intKey != NULL)
		      {
			$strStat = $arrStat[$intKey];
			$player -> $strStat = $player -> $strStat + $arrEquip[10][2];
		      }
		  }
		$maxmana = ($player -> inteli + $player -> wisdom);
		$maxmana = $maxmana + (($arrEquip[8][2] / 100) * $maxmana);
                $gainmana = ceil(($maxmana / 100) * $house -> fields['value']);
                $gainlife = (4 * $gainhp) + $player -> hp;
                if ($gainlife > $player -> max_hp) 
                {
                    $gainlife = $player -> max_hp;
                }
                $gainmagic = (4 * $gainmana) + $player -> mana;
                if ($gainmagic > $maxmana) 
                {
                    $gainmagic = $maxmana;
                }
                if ($gainenergy > ($player->max_energy * 0.75)) 
		  {
		    $gainenergy = ($player->max_energy * 0.75);
		  }
                $db -> Execute("UPDATE `players` SET `hp`=".$gainlife.", `energy`=`energy`+".$gainenergy.", `pm`=".$gainmagic." WHERE `id`=".$player -> id);
                $intGainlife = $gainlife - $player -> hp;
                $intGainmagic = $gainmagic - $player -> mana;
                if ($intGainmagic < 0)
                {
                    $intGainmagic = 0;
                }
                error ("<br /><br />".YOU_REST.$gainenergy.G_ENERGY.$intGainlife.G_LIFE.$intGainmagic.G_MAGIC);
            } 
                else 
            {
                error ("<br /><br />".YOU_REST2);
            }
        }
    }
    /**
     * Wardrobe - store item in house
     */
    if (isset ($_GET['step']) && $_GET['step'] == 'wardrobe') 
    {
        if ($house -> fields['wardrobe'] == 0) 
        {
            error (NO_WARDROBE);
        }
        $amount = $db -> Execute("SELECT SUM(`amount`) FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='H' AND location='".$player -> location."'");
	if (!$amount->fields['SUM(`amount`)'])
	  {
	    $items = 0;
	  }
	else
	  {
	    $items = $amount->fields['SUM(`amount`)'];
	  }
        $amount -> Close();
        $smarty -> assign(array("Amount" => $items,
            "Wardrobe" => $house -> fields['wardrobe'],
            "Winfo" => W_INFO,
            "Wamount" => W_AMOUNT,
            "And2" => AND2,
            "Iamount4" => I_AMOUNT4,
            "Iamount2" => I_AMOUNT2,
            "Inw" => IN_W,
            "Alist" => A_LIST,
            "Ahidei" => A_HIDE_I,
            "Iname" => I_NAME,
            "Ipower" => I_POWER,
            "Iagi" => I_AGI,
            "Ispeed" => I_SPEED,
            "Ioption" => I_OPTION,
            "Aget" => A_GET,
            "Idur" => I_DUR));
        /**
         * List of item in house
         */
        if(isset ($_GET['step2']) && $_GET['step2'] == 'list') 
        {
            $arritem = $db -> Execute("SELECT * FROM equipment WHERE owner=".$player -> id." AND status='H' AND location='".$player -> location."'");
            $arrname = array();
            $arrpower = array();
            $arrdur = array();
            $arrmaxdur = array();
            $arragility = array();
            $arrspeed = array();
            $arramount = array();
            $arrid = array();
            $i = 0;
            while (!$arritem -> EOF) 
            {
	        switch ($arritem->fields['ptype'])
		  {
		  case 'D':
		    $arritem->fields['name'] .= ' (Dynallca +'.$arritem->fields['poison'].')';
		    break;
		  case 'N':
		    $arritem->fields['name'] .= ' (Nutari +'.$arritem->fields['poison'].')';
		    break;
		  case 'I':
		    $arritem->fields['name'] .= ' (Illani +'.$arritem->fields['poison'].')';
		    break;
		  default:
		    break;
		  }
                $arrname[$i] = $arritem -> fields['name'];
		if ($arritem->fields['type'] != 'R')
		  {
		    $arrdur[$i] = $arritem -> fields['wt'];
		    $arrmaxdur[$i] = $arritem -> fields['maxwt'];
		    $arramount[$i] = $arritem -> fields['amount'];
		  }
		else
		  {
		    $arrdur[$i] = 1;
		    $arrmaxdur[$i] = 1;
		    $arramount[$i] = $arritem->fields['wt'];
		  }
		$arrspeed[$i] = $arritem -> fields['szyb'];
                $arrid[$i] = $arritem -> fields['id'];
                $arrpower[$i] = $arritem -> fields['power'];
                if ($arritem -> fields['zr'] < 1) 
                {
                    $arragility[$i] = str_replace("-","",$arritem -> fields['zr']);
                } 
                    else 
                {
                    $arragility[$i] = "-".$arritem -> fields['zr'];
                }
                if ($arritem -> fields['poison'] > 0) 
                {
                    $arrpower[$i] = $arritem -> fields['power'] + $arritem -> fields['poison'];
                }
                $arritem -> MoveNext();
                $i = $i + 1;
            }
            $arritem -> Close();
            $smarty -> assign(array("Itemname" => $arrname, 
                "Itemdur" => $arrdur, 
                "Itemmaxdur" => $arrmaxdur, 
                "Itemspeed" => $arrspeed, 
                "Itemamount" => $arramount, 
                "Itemid" => $arrid, 
                "Itempower" => $arrpower, 
                "Itemagility" => $arragility));
        }
        /**
         * Take items from house
         */
        if (isset ($_GET['take'])) 
	  {
	    checkvalue($_GET['take']);
	    $zbroj = $db -> Execute("SELECT * FROM equipment WHERE id=".$_GET['take']." AND `status`='H' AND `owner`=".$player->id);
	    if (!$zbroj->fields['id'])
	      {
		error(ERROR);
	      }
            if (!isset($_GET['step3'])) 
	      {
                $smarty -> assign(array("Id" => $_GET['take'], 
                    "Amount" => $zbroj -> fields['amount'], 
                    "Name" => $zbroj -> fields['name'],
                    "Fromh" => FROM_H,
                    "Amount2" => AMOUNT2));
            }
            if (isset($_GET['step3']) && $_GET['step3'] == 'add') 
            {
	        if (!isset($_POST['amount'])) 
                {
                    error (ERROR);
                }
                integercheck($_POST['amount']);
		checkvalue($_POST['amount']);
		if ($zbroj->fields['type'] != 'R')
		  {
		    if ($zbroj -> fields['amount'] < $_POST['amount']) 
		      {
			error (NO_AMOUNT);
		      }
		    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$zbroj -> fields['name']."' AND owner=".$player -> id." AND wt=".$zbroj -> fields['wt']." AND type='".$zbroj -> fields['type']."' AND power=".$zbroj -> fields['power']." AND szyb=".$zbroj -> fields['szyb']." AND zr=".$zbroj -> fields['zr']." AND maxwt=".$zbroj -> fields['maxwt']." AND poison=".$zbroj -> fields['poison']." AND status='U' AND ptype='".$zbroj -> fields['ptype']."' AND cost=".$zbroj -> fields['cost']);
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$zbroj -> fields['name']."',".$zbroj -> fields['power'].",'".$zbroj -> fields['type']."',".$zbroj -> fields['cost'].",".$zbroj -> fields['zr'].",".$zbroj -> fields['wt'].",".$zbroj -> fields['minlev'].",".$zbroj -> fields['maxwt'].",".$_POST['amount'].",'".$zbroj -> fields['magic']."',".$zbroj -> fields['poison'].",".$zbroj -> fields['szyb'].",'".$zbroj -> fields['twohand']."','".$zbroj -> fields['ptype']."', ".$zbroj -> fields['repair'].")");
		      } 
                    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
		      }
		    if ($_POST['amount'] < $zbroj -> fields['amount']) 
		      {
			$db -> Execute("UPDATE equipment SET amount=amount-".$_POST['amount']." WHERE id=".$zbroj -> fields['id']);
		      } 
                    else 
		      {
			$db -> Execute("DELETE FROM equipment WHERE id=".$zbroj -> fields['id']);
		      }
		  }
		else
		  {
		    if ($zbroj -> fields['wt'] < $_POST['amount']) 
		      {
			error (NO_AMOUNT);
		      }
		    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$zbroj -> fields['name']."' AND `owner`=".$player -> id." AND `type`='R' AND `power`=".$zbroj -> fields['power']." AND `szyb`=".$zbroj -> fields['szyb']." AND `zr`=".$zbroj -> fields['zr']." AND `poison`=".$zbroj -> fields['poison']." AND `status`='U' AND `ptype`='".$zbroj -> fields['ptype']."' AND `cost`=".$zbroj -> fields['cost']);
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$zbroj -> fields['name']."',".$zbroj -> fields['power'].",'R',".$zbroj -> fields['cost'].",".$zbroj -> fields['zr'].",".$_POST['amount'].",".$zbroj -> fields['minlev'].",".$_POST['amount'].",1,'".$zbroj -> fields['magic']."',".$zbroj -> fields['poison'].",".$zbroj -> fields['szyb'].",'".$zbroj -> fields['twohand']."','".$zbroj -> fields['ptype']."', ".$zbroj -> fields['repair'].")");
		      } 
                    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
		      }
		    if ($_POST['amount'] < $zbroj -> fields['wt']) 
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`-".$_POST['amount']." WHERE `id`=".$zbroj -> fields['id']);
		      } 
                    else 
		      {
			$db -> Execute("DELETE FROM `equipment` WHERE `id`=".$zbroj -> fields['id']);
		      }
		  }
		$test -> Close();
                error (YOU_GET.$_POST['amount'].I_AMOUNT.$zbroj -> fields['name']);
            }
	    $zbroj->Close();
	  }
        /**
         * Add item to wardrobe
         */
        if (isset ($_GET['step2']) && $_GET['step2'] == 'add') 
        {
            $item = $db -> Execute("SELECT * FROM `equipment` WHERE `status`='U' AND `type`!='Q' AND `owner`=".$player -> id);
	    $arrItems = array();
            while (!$item -> EOF) 
	      {
		switch ($item->fields['ptype'])
		  {
		  case 'D':
		    $item->fields['name'] .= ' (Dynallca +'.$item->fields['poison'].')';
		    break;
		  case 'N':
		    $item->fields['name'] .= ' (Nutari +'.$item->fields['poison'].')';
		    break;
		  case 'I':
		    $item->fields['name'] .= ' (Illani +'.$item->fields['poison'].')';
		    break;
		  default:
		    break;
		  }
		if ($item->fields['type'] != 'R')
		  {
		    $strAmount = $item->fields['amount'];
		  }
		else
		  {
		    $strAmount = $item->fields['wt'];
		  }
		$strDur = '';
		if ($item->fields['maxwt'] > 1)
		  {
		    $strDur = ' ('.$item->fields['wt'].'/'.$item->fields['maxwt'].' wt)';
		  }
		$strAgi = '';
		$strSpeed = '';
		if ($item->fields['zr'] != 0)
		  {
		    $strAgi = " (".($item->fields['zr'] * -1)." zr)";
		  }
		if ($item->fields['szyb'] != 0)
		  {
		    $strSpeed = " (".$item->fields['szyb']." szyb)";
		  }
		$arrItems[$item->fields['id']] = $item->fields['name']." (+".$item->fields['power'].")".$strAgi.$strSpeed.$strDur." (ilość: ".$strAmount.")";
                $item -> MoveNext();
	      }
            $item -> Close();
            $smarty -> assign(array("Ioptions" => $arrItems,
                "Item" => "przedmiot",
                "Iamount3" => I_AMOUNT3,
                "Ahide" => A_HIDE,
                "Amount2" => AMOUNT2));
            if (isset ($_GET['step3']) && $_GET['step3'] == 'add') 
            {
                if (!isset($_POST['przedmiot'])) 
                {
                    error(NO_ITEM);
                }
                integercheck($_POST['amount']);
		checkvalue($_POST['przedmiot']);
		checkvalue($_POST['amount']);
                $przed = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_POST['przedmiot']." AND `type`!='Q' AND status='U' AND `owner`=".$player->id);
                if (!$przed -> fields['id']) 
                {
                    error (ERROR);
                }
                $amount = ($house -> fields['wardrobe'] * 100) - $items;
		if ($przed->fields['type'] != 'R')
		  {
		    if ($przed -> fields['amount'] < $_POST['amount']) 
		      {
			error (NOT_ENOUGH);
		      }
		    if ($amount < $_POST['amount']) 
		      {
			error (NOT_ENOUGH2);
		      }
		    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$przed -> fields['name']."' AND owner=".$player -> id." AND wt=".$przed -> fields['wt']." AND type='".$przed -> fields['type']."' AND power=".$przed -> fields['power']." AND szyb=".$przed -> fields['szyb']." AND zr=".$przed -> fields['zr']." AND maxwt=".$przed -> fields['maxwt']." AND poison=".$przed -> fields['poison']." AND status='H' AND ptype='".$przed -> fields['ptype']."' AND cost=".$przed -> fields['cost']." AND location='".$player -> location."'");
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, status, ptype, repair, location) VALUES(".$player -> id.",'".$przed -> fields['name']."',".$przed -> fields['power'].",'".$przed -> fields['type']."',".$przed -> fields['cost'].",".$przed -> fields['zr'].",".$przed -> fields['wt'].",".$przed -> fields['minlev'].",".$przed -> fields['maxwt'].",".$_POST['amount'].",'".$przed -> fields['magic']."',".$przed -> fields['poison'].",".$przed -> fields['szyb'].",'".$przed -> fields['twohand']."','H','".$przed -> fields['ptype']."', ".$przed -> fields['repair'].", '".$player -> location."')") or die($db -> ErrorMsg());
		      } 
                    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
		      }
		    if ($_POST['amount'] < $przed -> fields['amount']) 
		      {
			$db -> Execute("UPDATE equipment SET amount=amount-".$_POST['amount']." WHERE id=".$przed -> fields['id']);
		      } 
                    else 
		      {
			$db -> Execute("DELETE FROM equipment WHERE id=".$przed -> fields['id']);
		      }
		  }
		else
		  {
		    if ($przed -> fields['wt'] < $_POST['amount']) 
		      {
			error (NOT_ENOUGH);
		      }
		    if ($amount - 1 < 0)
		      {
			error (NOT_ENOUGH2);
		      }
		    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$przed -> fields['name']."' AND `owner`=".$player -> id." AND `type`='R' AND `power`=".$przed -> fields['power']." AND `szyb`=".$przed -> fields['szyb']." AND `zr`=".$przed -> fields['zr']." AND `poison`=".$przed -> fields['poison']." AND `status`='H' AND `ptype`='".$przed -> fields['ptype']."' AND `cost`=".$przed -> fields['cost']." AND `location`='".$player -> location."'");
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, status, ptype, repair, location) VALUES(".$player -> id.",'".$przed -> fields['name']."',".$przed -> fields['power'].",'R',".$przed -> fields['cost'].",".$przed -> fields['zr'].",".$_POST['amount'].",".$przed -> fields['minlev'].",".$_POST['amount'].",1,'".$przed -> fields['magic']."',".$przed -> fields['poison'].",".$przed -> fields['szyb'].",'".$przed -> fields['twohand']."','H','".$przed -> fields['ptype']."', ".$przed -> fields['repair'].", '".$player -> location."')") or die($db -> ErrorMsg());
		      } 
                    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
		      }
		    if ($_POST['amount'] < $przed -> fields['wt']) 
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`-".$_POST['amount']." WHERE `id`=".$przed -> fields['id']);
		      } 
                    else 
		      {
			$db -> Execute("DELETE FROM `equipment` WHERE `id`=".$przed -> fields['id']);
		      }
		  }
                $test -> Close();
                error (YOU_HIDE.$_POST['amount'].I_AMOUNT.$przed -> fields['name'].IN_HOUSE);
            }
        }
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
    $fltLogins = fmod($player -> logins, 2);
    if ($fltLogins)
    {
        $strHouseinfo = HOUSE_INFO;
    }
        else
    {
        $strHouseinfo = HOUSE_INFO2;
    }
    $smarty -> assign(array("Houseinfo" => $strHouseinfo,
        "Aland" => A_LAND,
        "Alist" => A_LIST,
        "Arent" => A_RENT,
        "Ahouse" => A_HOUSE,
        "Aworkshop" => A_WORKSHOP));
}
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (!isset($_GET['step2'])) 
{
    $_GET['step2'] = '';
}
if (!isset($_GET['take'])) 
{
    $_GET['take'] = '';
}
if (!isset($_GET['step3'])) 
{
    $_GET['step3'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'], 
    "Houseid" => $house -> fields['id'], 
    "Step" => $_GET['step'], 
    "Step2" => $_GET['step2'], 
    "Take" => $_GET['take'], 
    "Step3" => $_GET['step3'], 
    "Owner" => $house -> fields['owner']));
$house -> Close();
$smarty -> display ('house.tpl');

require_once("includes/foot.php");
?>
