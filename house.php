<?php
/**
 *   File functions:
 *   Players houses
 *
 *   @name                 : house.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 27.12.2012
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

$house = $db -> Execute("SELECT * FROM `houses` WHERE `location`='".$player -> location."' AND (`owner`=".$player -> id." OR `locator`=".$player -> id.")");

/**
* Assign variables to template
*/
$smarty -> assign(array("Bedroomlink" => '', 
			"Locatorlink" => '', 
			"Buildbed" => '', 
			"Buildwardrobe" => '', 
			"Upgrade" => '', 
			"Wardrobelink" => '', 
			"Buildhouse" => ''));

if (isset($_GET['action']))
  {
    /**
     * Buy areas for house
     */
    if ($_GET['action'] == 'land') 
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
	if (isset ($_GET['step']) && $_GET['step'] == 'buy') 
	  {
	    if (!$house -> fields['id']) 
	      {
		if ($player -> platinum < 20) 
		  {
		    message('error', NO_MITH);
		  }
		else
		  {
		    $db -> Execute("INSERT INTO `houses` (`owner`, `location`) VALUES(".$player -> id.", '".$player -> location."')") or error ("Nie mogę dodać ziemii!");
		    $db -> Execute("UPDATE `players` SET `platinum`=`platinum`-20 WHERE `id`=".$player -> id);
		    $house = $db->Execute("SELECT * FROM `houses` WHERE `owner`=".$player->id." AND `location`='".$player->location."'");
		    message('success', BUY_AREA."<a href=house.php?action=build>".WORKSHOP."</a>".FOR_A);
		  }
	      } 
            else 
	      {
		if ($player -> credits < $cost1) 
		  {
		    message('error', NO_GOLD);
		  }
		else
		  {
		    $db -> Execute("UPDATE `houses` SET `size`=`size`+1 WHERE `id`=".$house -> fields['id']);
		    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$cost1." WHERE `id`=".$player -> id);
		    $house->fields['size'] ++;
		    message('success', BUY_AREA2);
		  }
	      }
	  }
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
      }

    /**
     * Builder workshop
     */
    elseif($_GET['action'] == 'build') 
      {
	if ($house -> fields['points'] == 0) 
	  {
	    error (NO_POINTS);
	  }
	if (!$house -> fields['id']) 
	  {
	    error ("Nie posiadasz ziemi ani domu aby móc cokolwiek budować.");
	  }
	if (isset($_GET['step']))
	  {
	    //Build new house
	    if ($_GET['step'] == 'new')
	      {
		unset($_GET['step']);
		if ($house -> fields['build'] > 0) 
		  {
		    message('error', YOU_HAVE);
		  }
		elseif ($player -> credits < 1000) 
		  {
		    message('error', NO_GOLD);
		  }
		elseif ($house -> fields['points'] < 10) 
		  {
		    message('error', NO_POINTS);
		  }
		else
		  {
		    $smarty -> assign(array("Hname" => H_NAME,
					    "Abuild" => A_BUILD));
		    $_GET['step'] = 'new';
		  }
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
		    $house->fields['name'] = $strName;
		    $house->fields['build'] ++;
		    $house->fields['points'] -= 10;
		    message('success', "Zbudowałeś swój dom.");
		    unset($_GET['step']);
		  }
	      }
	    //Upgrade house
	    elseif ($_GET['step'] == 'add') 
	      {
		$cost = 1000 * $house -> fields['build'];
		if ($player -> credits < $cost) 
		  {
		    message('error', NO_GOLD);
		  }
		elseif ($house -> fields['size'] == $house -> fields['build']) 
		  {
		    message('error', NO_FIELDS);
		  }
		elseif ($house -> fields['points'] < 10) 
		  {
		    message('error', NO_POINTS);
		  }
		else
		  {
		    $house -> fields['value'] -= 10;
		    $house->fields['points'] -= 10;
		    $house->fields['build'] ++;
		    if ($house -> fields['value'] < 1) 
		      {
			$house -> fields['value'] = 1;
		      }
		    $db -> Execute("UPDATE `houses` SET `build`=`build`+1, `points`=`points`-10, `value`=".$house -> fields['value']." WHERE `id`=".$house -> fields['id']);
		    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$cost." WHERE `id`=".$player -> id);
		    message('success', YOU_UPGRADE);
		  }
	      }
	    //Build bedroom
	    elseif (isset ($_GET['step']) && $_GET['step'] == 'bedroom') 
	      {
		if ($house -> fields['used'] == $house -> fields['build']) 
		  {
		    message('error', NO_FREE);
		  }
		elseif ($house -> fields['bedroom'] == 'Y') 
		  {
		    message('error', YOU_HAVE);
		  }
		elseif ($player -> credits < 10000) 
		  {
		    message('error', NO_GOLD);
		  }
		elseif ($house -> fields['points'] < 10) 
		  {
		    message('error', NO_POINTS2);
		  }
		else
		  {
		    $house->fields['bedroom'] = 'Y';
		    $house->fields['points'] -= 10;
		    $house->fields['used'] ++;
		    $db -> Execute("UPDATE `houses` SET `bedroom`='Y', `points`=`points`-10, `used`=`used`+1 WHERE `id`=".$house -> fields['id']);
		    $db -> Execute("UPDATE `players` SET `credits`=`credits`-10000 WHERE `id`=".$player -> id);
		    message('success', YOU_BUILD);
		  }
	      }
	    //Build wardrobe
	    elseif (isset ($_GET['step']) && $_GET['step'] == 'wardrobe') 
	      {
		$cost = $house -> fields['wardrobe'] * 1000;
		if ($cost == 0) 
		  {
		    $cost = 1000;
		  }
		if ($house -> fields['used'] == $house -> fields['build']) 
		  {
		    message('error', NO_FREE);
		  }
		elseif ($player -> credits < $cost) 
		  {
		    message('error', NO_GOLD);
		  }
		elseif ($house -> fields['points'] < 10) 
		  {
		    message('error', NO_POINTS2);
		  }
		else
		  {
		    $house->fields['wardrobe'] ++;
		    $house->fields['points'] -= 10;
		    $house->fields['used'] ++;
		    $db -> Execute("UPDATE `houses` SET `wardrobe`=`wardrobe`+1, `points`=`points`-10, `used`=`used`+1 WHERE `id`=".$house -> fields['id']);
		    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$cost." WHERE `id`=".$player -> id);
		    message('success', YOU_BUILD);
		  }
	      }
	    //House adorment
	    elseif (isset ($_GET['step']) && $_GET['step'] == 'upgrade') 
	      {
		unset($_GET['step']);
		if ($house -> fields['points'] < 1) 
		  {
		    message('error', NO_POINTS2);
		  }
		else
		  {
		    $smarty -> assign(array("Upginfo" => UPG_INFO,
					    "Upgrade3" => UPGRADE,
					    "Upgrade2" => UPGRADE2,
					    "Awork" => A_WORK));
		    $_GET['step'] = 'upgrade';
		  }
		if (isset ($_GET['step2']) && $_GET['step2'] == 'make') 
		  {
		    checkvalue($_POST['points']);
		    $cost = 1000 * $_POST['points'];
		    if ($player -> credits < $cost) 
		      {
			message('error', NO_GOLD);
		      }
		    elseif ($_POST['points'] > $house -> fields['points']) 
		      {
			message('error', NO_POINTS);
		      }
		    else
		      {
			$house->fields['points'] -= $_POST['points'];
			$house->fields['value'] += $_POST['points'];
			$db -> Execute("UPDATE `players` SET `credits`=`credits`-".$cost." WHERE `id`=".$player -> id);
			$db -> Execute("UPDATE `houses` SET `points`=`points`-".$_POST['points'].", `value`=`value`+".$_POST['points']." WHERE `id`=".$house -> fields['id']);
			message('success', YOU_UPGRADE);
		      }
		  }
		if ($house->fields['points'] == 0)
		  {
		    unset($_GET['action']);
		  }
	      }
	  }
	$smarty -> assign(array("Points" => $house -> fields['points'],
				"Buildinfo" => BUILD_INFO,
				"Buildinfo2" => BUILD_INFO2,
				"Aback" => A_BACK));
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
      }

    /**
     * List of best players houses (50 houses max on list)
     */
    elseif ($_GET['action'] == 'list') 
      {
	$arrHouses = $db->GetAll("SELECT * FROM `houses` WHERE `build`>0 AND `owner`>0 AND `location`='".$player -> location."' ORDER BY `build` DESC LIMIT 0, 50");
	if (count($arrHouses) < 1)
	  {
	    message('error', 'Nie ma jeszcze wybudowanych domów w tym mieście.');
	    unset($_GET['action']);
	  }
	else
	  {
	    $arrMembers = array();
	    foreach ($arrHouses as &$arrHouse)
	      {
		if (!in_array($arrHouse['owner'], $arrMembers))
		  {
		    $arrMembers[] = $arrHouse['owner'];
		  }
		if ($arrHouse['locator'])
		  {
		    if (!in_array($arrHouse['locator'], $arrMembers))
		      {
			$arrMembers[] = $arrHouse['locator'];
		      }
		  }
		else
		  {
		    $arrHouse['locator'] = L_EMPTY;
		  }
		$arrHouse['housetype'] = housetype($arrHouse['value'], $arrHouse['build']);
	      }
	    $arrMembers = $db->GetAll("SELECT `id`, `user` FROM `players` WHERE `id` IN (".implode(', ', $arrMembers).")");
	    foreach ($arrMembers as &$arrMember)
	      {
		foreach ($arrHouses as &$arrHouse)
		  {
		    if ($arrHouse['owner'] == $arrMember['id'])
		      {
			$arrHouse['ownername'] = $arrMember['user'];
			break;
		      }
		    elseif ($arrHouse['locator'] == $arrMember['id'])
		      {
			$arrHouse['locator'] = "<a href=\"view.php?view=".$arrHouse['locator']."\">".$arrMember['user']."</a>";
			break;
		      }
		  }
	      }
	    $smarty -> assign(array("Houses" => $arrHouses, 
				    "Hname" => H_NAME,
				    "Hnumber" => H_NUMBER,
				    "Htype" => H_TYPE,
				    "Hsize" => H_SIZE,
				    "Howner" => H_OWNER,
				    "Hlocator" => H_LOCATOR,
				    "Aback" => A_BACK));
	  }
      }

    /**
     * List of houses for sale
     */
    elseif ($_GET['action'] == 'rent') 
      {
	if (isset($_GET['buy'])) 
	  {
	    checkvalue($_GET['buy']);
	    $blnValid = TRUE;
	    if ($house -> fields['id']) 
	      {
		message('error', YOU_HAVE);
		$blnValid = FALSE;
	      }
	    $buy = $db -> Execute("SELECT `id`, `owner`, `cost`, `seller` FROM `houses` WHERE `id`=".$_GET['buy']);
	    if (!$buy -> fields['id']) 
	      {
		message('error', NO_HOUSE);
		$blnValid = FALSE;
	      }
	    if ($buy -> fields['owner']) 
	      {
		message('error', NOT_FOR_SALE);
		$blnValid = FALSE;
	      }
	    if ($player -> credits < $buy -> fields['cost']) 
	      {
		message('error', NO_GOLD);
		$blnValid = FALSE;
	      }
	    if ($blnValid)
	      {
		$db -> Execute("UPDATE `players` SET `credits`=`credits`-".$buy -> fields['cost']." WHERE `id`=".$player -> id);
		$db -> Execute("UPDATE `players` SET `bank`=`bank`+".$buy -> fields['cost']." WHERE `id`=".$buy -> fields['seller']);
		$db -> Execute("UPDATE `houses` SET `cost`=0, `seller`=0, `owner`=".$player -> id." WHERE `id`=".$buy -> fields['id']);
		$strDate = $db -> DBDate($newdate);
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['seller'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ACCEPT.$buy -> fields['cost'].L_BANK."', ".$strDate.", 'H')");
		$house = $db->Execute("SELECT * FROM `houses` WHERE `id`=".$buy->fields['id']);
		message("success", YOU_BUY);
	      }
	    $buy -> Close();
	  }
	if (isset($_GET['back']))
	  {
	    checkvalue($_GET['back']);
	    $blnValid = TRUE;
	    if ($house -> fields['id']) 
	      {
		message('error', YOU_HAVE);
		$blnValid = FALSE;
	      }
	    $buy = $db -> Execute("SELECT `id`, `owner`, `seller` FROM `houses` WHERE `id`=".$_GET['back']);
	    if (!$buy -> fields['id']) 
	      {
		message('error', NO_HOUSE);
		$blnValid = FALSE;
	      }
	    if ($buy -> fields['owner']) 
	      {
		message('error', NOT_FOR_SALE);
		$blnValid = FALSE;
	      }
	    if ($buy -> fields['seller'] != $player -> id)
	      {
		message('error', NOT_YOUR);
		$blnValid = FALSE;
	      }
	    if ($blnValid)
	      {
		$db -> Execute("UPDATE `houses` SET `cost`=0, `seller`=0, `owner`=".$player -> id." WHERE `id`=".$buy -> fields['id']);
		$house = $db->Execute("SELECT * FROM `houses` WHERE `id`=".$buy->fields['id']);
		message('success', YOU_WITHDRAW);
	      }
	    $buy -> Close();
	  }
	$arrHouses = $db->GetAll("SELECT * FROM `houses` WHERE `owner`=0 AND `location`='".$player -> location."' ORDER BY `build` DESC");
	if (count($arrHouses) < 1)
	  {
	    message('error', 'Nie ma jeszcze domów na sprzedaż w tym mieście.');
	    unset($_GET['action']);
	  }
	else
	  {
	    $arrMembers = array();
	    foreach ($arrHouses as &$arrHouse)
	      {
		if (!in_array($arrHouse['seller'], $arrMembers))
		  {
		    $arrMembers[] = $arrHouse['seller'];
		  }
		if ($player -> id == $arrHouse['seller']) 
		  {
		    $arrHouse['link'] = "<a href=\"house.php?action=rent&amp;back=".$arrHouse['id']."\">".YOUR_OFERT."</a>";
		  } 
		elseif ($house -> fields['id']) 
		  {
		    $arrHouse['link'] = L_EMPTY;
		  } 
		else 
		  {
		    $arrHouse['link'] = "<a href=\"house.php?action=rent&amp;buy=".$arrHouse['id']."\">".A_BUY."</a>";
		  }
		$arrHouse['housetype'] = housetype($arrHouse['value'], $arrHouse['build']);
		$arrHouse['sellername'] = 'Nieobecny';
	      }
	    $arrMembers = $db->GetAll("SELECT `id`, `user` FROM `players` WHERE `id` IN (".implode(', ', $arrMembers).")");
	    foreach ($arrMembers as &$arrMember)
	      {
		foreach ($arrHouses as &$arrHouse)
		  {
		    if ($arrHouse['seller'] == $arrMember['id'])
		      {
			$arrHouse['sellername'] = $arrMember['user'];
		      }
		  }
	      }
	    $smarty -> assign(array("Houses" => $arrHouses, 
				    "Hnumber" => H_NUMBER,
				    "Hseller" => H_SELLER,
				    "Hname" => H_NAME,
				    "Hsize" => H_SIZE,
				    "Htype" => H_TYPE,
				    "Hcost" => H_COST,
				    "Hoption" => H_OPTION,
				    "Aback" => A_BACK));
	  }
      }

    /**
     * Player house
     */
    elseif ($_GET['action'] == 'my') 
      {
	$smarty -> assign("Aback", A_BACK);
	if (!$house -> fields['id']) 
	  {
	    error (NO_HOUSE);
	  }
	if (isset($_GET['step']))
	  {
	    /**
	     * Leave house
	     */
	    if ($_GET['step'] == 'leave')
	      {
		if (($player -> id != $house -> fields['locator']) && ($player->id != $house->fields['owner']))
		  {
		    error(ERROR);
		  }
		if (!isset($_GET['step2']))
		  {
		    $smarty -> assign(array("Youwant" => YOU_WANT,
					    "Yes" => YES));
		  }
		elseif ($_GET['step2'] == 'confirm')
		  {
		    if ($player->id == $house->fields['locator'])
		      {
			$db -> Execute("UPDATE `houses` SET `locator`=0 WHERE `id`=".$house -> fields['id']);
		      }
		    elseif ($player->id == $house->fields['owner'])
		      {
			if ($house->fields['locator'])
			  {
			    $db->Execute("UPDATE `houses` SET `owner`=".$house->fields['locator'].", `locator`=0 WHERE `id`=".$house->fields['id']);
			  }
			else
			  {
			    $db->Execute("DELETE FROM `houses` WHERE `id`=".$house->fields['id']);
			  }
		      }
		    message('success', YOU_LEAVE);
		    unset($_GET['action']);
		    $house = $db -> Execute("SELECT * FROM `houses` WHERE `location`='".$player -> location."' AND (`owner`=".$player -> id." OR `locator`=".$player -> id.")");
		  }
	      }
	    /**
	     * Set house for sale
	     */
	    elseif ($_GET['step'] == 'sell') 
	      {
		if ($player -> id != $house -> fields['owner']) 
		  {
		    error (ONLY_OWNER);
		  }
		$smarty -> assign(array("Sellinfo" => SELL_INFO,
					"Housesale" => HOUSE_SALE,
					"Goldcoins" => GOLD_COINS,
					"Asend" => A_SEND));
		if (isset($_GET['step2']) && $_GET['step2'] == 'sell') 
		  {
		    checkvalue($_POST['cost']);
		    $db -> Execute("UPDATE `houses` SET `cost`=".$_POST['cost'].", `seller`=".$player -> id.", `owner`=0, `locator`=0 WHERE `id`=".$house -> fields['id']);
		    $house->fields['owner'] = 0;
		    $house->fields['locator'] = 0;
		    $house->fields['id'] = 0;
		    message("success", YOU_SELL.$_POST['cost'].GOLD_COINS.".");
		    unset($_GET['action']);
		  }
	      } 
	    /**
	     * Add/delete locator to/from house
	     */
	    elseif ($_GET['step'] == 'locator') 
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
		    $blnValid = TRUE;
		    if ($_POST['loc'] == 'add') 
		      {
			if ($house -> fields['locator']) 
			  {
			    message('error', YOU_HAVE);
			    $blnValid = FALSE;
			  }
			$test = $db -> Execute("SELECT `id` FROM `houses` WHERE `owner`=".$_POST['lid']." AND `location`='".$player -> location."'");
			if ($test -> fields['id']) 
			  {
			    message('error', BAD_PL);
			    $blnValid = FALSE;
			  }
			$test = $db -> Execute("SELECT `id` FROM `houses` WHERE `locator`=".$_POST['lid']." AND `location`='".$player -> location."'");
			if ($test -> fields['id']) 
			  {
			    message('error', LIVE_ANOTHER);
			    $blnValid = FALSE;
			  }
			$test = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_POST['lid']);
			if (!$test -> fields['id']) 
			  {
			    message('error', NO_PLAYER);
			    $blnValid = FALSE;
			  }
			$test -> Close();
			if ($blnValid)
			  {
			    $db -> Execute("UPDATE `houses` SET `locator`=".$_POST['lid']." WHERE `id`=".$house -> fields['id']);
			    message("success", YOU_ADD);
			    $strLog = YOU_GET;
			  }
		      }
		    elseif ($_POST['loc'] == 'delete') 
		      {
			if (!$house -> fields['locator']) 
			  {
			    message('error', NO_LOC);
			    $blnValid = FALSE;
			  }
			elseif ($_POST['lid'] != $house -> fields['locator']) 
			  {
			    message('error', NO_LOC2);
			    $blnValid = FALSE;
			  }
			else
			  {
			    $db -> Execute("UPDATE `houses` SET `locator`=0 WHERE `id`=".$house -> fields['id']);
			    message("success", YOU_DELETE);
			    $strLog = YOU_FIRED;
			  }
		      }
		    if ($blnValid)
		      {
			$strDate = $db -> DBDate($newdate);
			$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['lid'].",'".$strLog."<b><a href=\"view.php?view=".$player -> id."\">".$player -> user."</a></b>.', ".$strDate.", 'H')");
			unset($_GET['step']);
		      }
		  }
	      }
	    /**
	     * Rename house
	     */
	    elseif ($_GET['step'] == 'name') 
	      {
		if ($player -> id != $house -> fields['owner']) 
		  {
		    error (ONLY_OWNER);
		  }
		$smarty -> assign(array("Achange" => A_CHANGE,
					"Ona" => ON_A));
		if (isset ($_GET['step2']) && $_GET['step2'] == 'change') 
		  {
		    if (empty ($_POST['name'])) 
		      {
			message('error', EMPTY_NAME);
		      }
		    else
		      {
			$_POST['name'] = strip_tags($_POST['name']);
			$strName = $db -> qstr($_POST['name'], get_magic_quotes_gpc());
			$db -> Execute("UPDATE `houses` SET `name`=".$strName." WHERE `id`=".$house -> fields['id']);
			message('success', YOU_CHANGE.$_POST['name']);
			unset($_GET['step']);
		      }
		  }
	      }
	    /**
	     * Rest in house
	     */
	    elseif ($_GET['step'] == 'bedroom') 
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
		    $blnValid = TRUE;
		    if ($player -> hp == 0) 
		      {
			message('error', YOU_DEAD);
			$blnValid = FALSE;
		      }
		    if ($player -> race == '' || $player -> clas == '')
		      {
			message('error', NO_RACE);
			$blnValid = FALSE;
		      }
		    $objTest = $db -> Execute("SELECT `houserest` FROM `players` WHERE `id`=".$player -> id);
		    if ($objTest -> fields['houserest'] == 'Y') 
		      {
			message("error", ONLY_ONCE);
			$blnValid = FALSE;
		      }
		    $objTest -> Close();
		    if ($blnValid)
		      {
			$db -> Execute("UPDATE `players` SET `houserest`='Y' WHERE id=".$player -> id);
			$roll = rand(1, 100);
			if ($roll > 5) 
			  {
			    $gainenergy =  ceil(($player -> max_energy / 100) * $house -> fields['value']);
			    $gainhp = ceil(($player -> max_hp / 100) * $house -> fields['value']);
			    $maxmana = floor($player->stats['inteli'][2] + $player->stats['wisdom'][2]);
			    if ($player->clas == 'Mag')
			      {
				$maxmana = $maxmana * 2;
			      }
			    $maxmana += floor(($player->equip[8][2] / 100) * $maxmana);
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
			    message("success", YOU_REST.$gainenergy.G_ENERGY.$intGainlife.G_LIFE.$intGainmagic.G_MAGIC);
			  } 
			else 
			  {
			    message("error", YOU_REST2);
			  }
			unset($_GET['step']);
		      }
		  }
	      }
	    /**
	     * Wardrobe - store item in house
	     */
	    elseif ($_GET['step'] == 'wardrobe') 
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
		if (isset($_GET['step2']))
		  {
		    /**
		     * List of item in house
		     */
		    if($_GET['step2'] == 'list') 
		      {
			$arrItems = $db->GetAll("SELECT * FROM `equipment` WHERE `owner`=".$player->id." AND `status`='H' AND `location`='".$player->location."'");
			if (count($arrItems) == 0)
			  {
			    message('error', 'Nie masz jakichkolwiek przedmiotów w szafach.');
			    unset($_GET['step2']);
			  }
			else
			  {
			    foreach ($arrItems as &$arrItem)
			      {
				switch ($arrItem['ptype'])
				  {
				  case 'D':
				    $arrItem['name'] .= ' (Dynallca +'.$arrItem['poison'].')';
				    break;
				  case 'N':
				    $arrItem['name'] .= ' (Nutari +'.$arrItem['poison'].')';
				    break;
				  case 'I':
				    $arrItem['name'] .= ' (Illani +'.$arrItem['poison'].')';
				    break;
				  default:
				    break;
				  }
				if ($arrItem['type'] == 'R')
				  {
				    $arrItem['amount'] = $arrItem['wt'];
				    $arrItem['wt'] = 1;
				    $arrItem['maxwt'] = 1;
				  }
				$arrItem['zr'] = $arrItem['zr'] * -1;
				if ($arrItem['poison'] > 0)
				  {
				    $arrItem['power'] += $arrItem['poison'];
				  }
			      }
			    $smarty->assign("Items", $arrItems);
			  }
		      }
		    /**
		     * Take items from house
		     */
		    elseif ($_GET['step2'] == 'take') 
		      {
			checkvalue($_GET['take']);
			$zbroj = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['take']." AND `status`='H' AND `owner`=".$player->id);
			if (!$zbroj->fields['id'])
			  {
			    error(ERROR);
			  }
			if (isset($_GET['step3'])) 
			  {
			    if (!isset($_POST['amount'])) 
			      {
				message('error', 'Podaj ile przedmiotów chcesz zabrać z szaf.');
			      }
			    else
			      {
				integercheck($_POST['amount']);
				checkvalue($_POST['amount']);
				if ($zbroj->fields['type'] != 'R')
				  {
				    if ($zbroj -> fields['amount'] < $_POST['amount']) 
				      {
					message('error', NO_AMOUNT);
				      }
				    else
				      {
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
					$test->Close();
					message('success', YOU_GET.$_POST['amount'].I_AMOUNTS.$zbroj -> fields['name']);
				      }
				  }
				else
				  {
				    if ($zbroj -> fields['wt'] < $_POST['amount']) 
				      {
					message('error', NO_AMOUNT);
				      }
				    else
				      {
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
					$test->Close();
					message('success', YOU_GET.$_POST['amount'].I_AMOUNTS.$zbroj -> fields['name']);
				      }
				  }
			      }
			  }
			if ($zbroj->fields['type'] == 'R')
			  {
			    $intAmount = $zbroj->fields['wt'];
			  }
			else
			  {
			    $intAmount = $zbroj->fields['amount'];
			  }
			$smarty -> assign(array("Id" => $_GET['take'], 
						"Amount23" => $intAmount, 
						"Name" => $zbroj -> fields['name'],
						"Fromh" => FROM_H,
						"Amount2" => AMOUNT2));
			$zbroj->Close();
		      }
		    /**
		     * Add item to wardrobe
		     */
		    elseif ($_GET['step2'] == 'add') 
		      {
			if (isset ($_GET['step3'])) 
			  {
			    if (!isset($_POST['przedmiot'])) 
			      {
				message('error', 'Podaj jaki przedmiot chcesz schować w domu.');
			      }
			    else
			      {
				checkvalue($_POST['przedmiot']);
				if (!isset($_POST['addall']))
				  {
				    integercheck($_POST['amount']);
				    checkvalue($_POST['amount']);
				  }
				$przed = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_POST['przedmiot']." AND `type`!='Q' AND status='U' AND `owner`=".$player->id);
				if (!$przed -> fields['id']) 
				  {
				    error (ERROR);
				  }
				$amount = ($house -> fields['wardrobe'] * 100) - $items;
				if ($przed->fields['type'] != 'R')
				  {
				    if (isset($_POST['addall']))
				      {
					$_POST['amount'] = $przed -> fields['amount'];
				      }
				    if ($przed -> fields['amount'] < $_POST['amount']) 
				      {
					message('error', NOT_ENOUGH);
				      }
				    elseif ($amount < $_POST['amount']) 
				      {
					message('error', NOT_ENOUGH2);
				      }
				    else
				      {
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
					$test -> Close();
					message('success', YOU_HIDE.$_POST['amount'].I_AMOUNTS.$przed -> fields['name'].IN_HOUSE);
				      }
				  }
				else
				  {
				    if (isset($_POST['addall']))
				      {
					$_POST['amount'] = $przed -> fields['wt'];
				      }
				    if ($przed -> fields['wt'] < $_POST['amount']) 
				      {
					message('error', NOT_ENOUGH);
				      }
				    if ($amount - 1 < 0)
				      {
					message('error', NOT_ENOUGH2);
				      }
				    else
				      {
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
					$test -> Close();
					message('success', YOU_HIDE.$_POST['amount'].I_AMOUNTS.$przed -> fields['name'].IN_HOUSE);
				      }
				  }
			      }
			  }
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
						"Tall" => "wszystkie posiadane",
						"Amount2" => AMOUNT2));
		      }
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
					"Wamount" => W_AMOUNT2,
					"And2" => AND2,
					"Iamount4" => I_AMOUNT4,
					"Iamount2" => I_AMOUNT2,
					"Inw" => IN_W,
					"Alist" => A_LIST2,
					"Ahidei" => A_HIDE_I,
					"Iname" => I_NAME,
					"Ipower" => I_POWER,
					"Iagi" => I_AGI,
					"Ispeed" => I_SPEED,
					"Ioption" => I_OPTION,
					"Aget" => A_GET,
					"Idur" => I_DUR));
	      }
	  }
	//House main menu
	if (!isset ($_GET['step'])) 
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
	    $objOwner = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$house->fields['owner']);
	    $smarty -> assign(array("Name" => $house -> fields['name'], 
				    "Size" => $house -> fields['size'], 
				    "Build" => $house -> fields['build'], 
				    "Value" => $house -> fields['value'], 
				    "Owner" => $house->fields['owner'],
				    "Ownername" => $objOwner->fields['user'],
				    "Housename" => $homename, 
				    "Unused" => $unused, 
				    "Wardrobe" => $house -> fields['wardrobe'], 
				    "Items" => $intAmount,
				    "Houseinfo" => HOUSE_INFO3,
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
	    $objOwner->Close();
	    if ($house -> fields['locator']) 
	      {
		$objLocator = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$house->fields['locator']);
		$smarty -> assign(array("Locator" => "<a href=\"view.php?view=".$house -> fields['locator']."\">".$objLocator->fields['user']."</a>", 
					"Locleave" => "- <a href=\"house.php?action=my&amp;step=leave\">".A_LEAVE."</a><br />"));
		$objLocator->Close();
	      } 
            else 
	      {
		$smarty -> assign(array("Locator" => L_EMPTY, 
					"Locleave" => "- <a href=\"house.php?action=my&amp;step=leave\">".A_LEAVE."</a><br />"));
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
			"Step3" => $_GET['step3']));
$house -> Close();
$smarty -> display ('house.tpl');

require_once("includes/foot.php");
?>
