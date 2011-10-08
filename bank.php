<?php
/**
 *   Funkcje pliku:
 *   Bank - deposit gold and give item to another player
 *
 *   @name                 : bank.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : yeskov <yeskov@users.sourceforge.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 08.10.2011
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

$title = "Bank";
require_once("includes/head.php");

/**
* Get the localization for game
*/

require_once("languages/".$player -> lang."/bank.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Withdraw gold from bank
*/
if (isset ($_GET['action']) && $_GET['action'] == 'withdraw') 
{
    if (!isset($_POST['with'])) 
    {
        error(EMPTY_FIELD);
    }
    integercheck($_POST['with']);
    checkvalue($_POST['with']);
    if ($_POST['with'] > $player -> bank) 
    {
        error (NO_MONEY);
    }
    $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$_POST['with'].", `bank`=`bank`-".$_POST['with']." WHERE `id`=".$player -> id);
    error ("<br />".WITHDRAW." ".$_POST['with']." ".GOLD_COINS);
}

/**
* Deposit gold to bank
*/
if (isset ($_GET['action']) && $_GET['action'] == 'deposit') 
{
    if (!isset($_POST['dep'])) 
    {
        error (EMPTY_FIELD);
    }
    integercheck($_POST['dep']);
    checkvalue($_POST['dep']);
    if ($_POST['dep'] > $player -> credits || $_POST['dep'] <= 0) 
    {
        error (NO_MONEY);
    }
    $db -> Execute("UPDATE players SET credits=credits-".$_POST['dep'].", bank=bank+".$_POST['dep']." WHERE id=".$player -> id);
    error ("<br />".DEPOSIT." ".$_POST['dep']." ".GOLD_COINS);
}

/**
* Donations of gold for another player
*/
if (isset ($_GET['action']) && $_GET['action'] == 'donation') 
{
    checkvalue($_POST['pid']);
    checkvalue($_POST['with']);
    integercheck($_POST['with']);

    if (strlen($_POST['title']) > 0)
      {
	$strTitle = strip_tags($_POST['title']);
	$strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
      }
    else
      {
	$strTitle = '';
      }
    if ($player->bank < $_POST['with'])
    {
        error(NO_GOLD);
    }
    if ($player -> credits < 0)
    {
        error(MINUS_GOLD);
    }
    if ($_POST['pid'] == $player -> id) 
    {
        error (BAD_PLAYER);
    }
    $objDonated = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['pid']);
    if (!$objDonated -> fields['id'])
    {
        error (NO_PLAYER);
    }
    $strPlayerName = $objDonated -> fields['user'];
    $objDonated -> Close();
    $db -> Execute("UPDATE `players` SET `bank`=`bank`+".$_POST['with']." WHERE `id`=".$_POST['pid']);
    $db -> Execute("UPDATE `players` SET `bank`=`bank`-".$_POST['with']." WHERE `id`=".$player -> id);
    $strDate = $db -> DBDate($newdate);
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER." <b><a href=view.php?view=".$player -> id.">".$player -> user."</a></b>".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['with']." sztuk złota".$strTitle.".', ".$strDate.", 'N')");    
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid']."</b> ".G_AMOUNT." ".$_POST['with']." sztuk złota".$strTitle.".', ".$strDate.", 'N')");    
    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid']."</b> ".G_AMOUNT." ".$_POST['with']." sztuk złota".$strTitle.".', ".$strDate.")");
    error("<br />".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid']."</b> ".G_AMOUNT." ".$_POST['with']." ".GOLD_COINS);
}

/**
* Mithril donations to another player
*/
if (isset ($_GET['action']) && $_GET['action'] == 'mithril') 
{
    checkvalue($_POST['pid']);
    integercheck($_POST['mithril']);
    checkvalue($_POST['mithril']);

    if (strlen($_POST['title']) > 0)
      {
	$strTitle = strip_tags($_POST['title']);
	$strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
      }
    else
      {
	$strTitle = '';
      }
    if ($_POST['pid'] == $player -> id) 
    {
        error (BAD_PLAYER);
    }
    if ($player->platinum < $_POST['mithril'])
    {
        error(NO_MITHRIL);
    }
    $objDonated = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['pid']);
    if (!$objDonated -> fields['id'])
    {
        error (NO_PLAYER);
    }
    $strPlayerName = $objDonated -> fields['user'];
    $objDonated -> Close();
    $db -> Execute("UPDATE `players` SET `platinum`=`platinum`+".$_POST['mithril']." WHERE `id`=".$_POST['pid']);
    $db -> Execute("UPDATE `players` SET `platinum`=`platinum`-".$_POST['mithril']." WHERE `id`=".$player -> id);
    $strDate = $db -> DBDate($newdate);
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['mithril']." sztuk mithrilu".$strTitle.".', ".$strDate.", 'N')");
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['mithril']." sztuk mithrilu".$strTitle.".', ".$strDate.", 'N')");
    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['mithril']." sztuk mithrilu ".$strTitle.".', ".$strDate.")");
    error ("<br />".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['mithril']." ".M_AMOUNT."");
}

/**
* Give minerals to another player
*/
if (isset ($_GET['action']) && $_GET['action'] == 'minerals') 
{
    if (!isset($_POST['pid']))
      {
	error(ERROR);
      }
    checkvalue($_POST['pid']);
    if (strlen($_POST['title']) > 0)
      {
	$strTitle = strip_tags($_POST['title']);
	$strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
      }
    else
      {
	$strTitle = '';
      }
    $arrSqlname = array('copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
    if (!in_array($_POST['item'], $arrSqlname)) 
    {
        error(ERROR);
    }
    $intKey = array_search($_POST['item'], $arrSqlname);
    $objMinerals = $db -> Execute("SELECT ".$_POST['item']." FROM `minerals` WHERE `owner`=".$player -> id);
    if (!$objMinerals -> fields[$_POST['item']]) 
    {
        error(NO_MINERALS);
    }
    if (!isset($_POST['addall']))
      {
	integercheck($_POST['amount']);
	checkvalue($_POST['amount']);
      }
    else
      {
	$_POST['amount'] = $objMinerals -> fields[$_POST['item']];
      }
    $arrName = array(COPPERORE, ZINCORE, TINORE, IRONORE, COPPER, BRONZE, BRASS, IRON, STEEL, COAL, ADAMANTIUM, METEOR, CRYSTAL, PINE, HAZEL, YEW, ELM);
    $strMineralname = $arrName[$intKey];
    if ($objMinerals -> fields[$_POST['item']] < $_POST['amount']) 
    {
        error(NO_MINERAL." ".$strMineralname);
    }
    $objMinerals -> Close();
    if ($_POST['pid'] == $player -> id) 
    {
        error(BAD_PLAYER);
    }
    $objDonated = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['pid']);
    if (empty($objDonated -> fields['id'])) 
    {
        error(NO_PLAYER);
    }
    $strPlayerName = $objDonated -> fields['user'];
    $objDonated -> Close();
    $objHave = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$_POST['pid']);
    if (empty($objHave -> fields['owner'])) 
    {
        $db -> Execute("INSERT INTO `minerals` (`owner`, `".$_POST['item']."`) VALUES(".$_POST['pid'].",".$_POST['amount'].")") or error (E_DB);
    } 
        else 
    {
        $db -> Execute("UPDATE `minerals` SET `".$_POST['item']."`=`".$_POST['item']."`+".$_POST['amount']." WHERE `owner`=".$_POST['pid']) or error (E_DB2);
    }
    $objHave  -> Close();
    $db -> Execute("UPDATE `minerals` SET `".$_POST['item']."`=`".$_POST['item']."`-".$_POST['amount']." WHERE `owner`=".$player -> id);
    $strDate = $db -> DBDate($newdate);
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$strMineralname.$strTitle.".', ".$strDate.", 'N')");
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].", ".$_POST['amount']." ".T_AMOUNT." ".$strMineralname.$strTitle.".', ".$strDate.", 'N')");
    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.", '".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].", ".$_POST['amount']." ".T_AMOUNT." ".$strMineralname.$strTitle.".', ".$strDate.")");
    error ("<br />".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, <b>".$_POST['amount']."</b> ".T_AMOUNT." <b>".$strMineralname."</b>.");
}

/**
* Give herbs to another player
*/
if (isset ($_GET['action']) && $_GET['action'] == 'herbs') 
{
    checkvalue($_POST['pid']);
    if (strlen($_POST['title']) > 0)
      {
	$strTitle = strip_tags($_POST['title']);
	$strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
      }
    else
      {
	$strTitle = '';
      }
    $arrHerbs = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
    if (!in_array($_POST['item'], $arrHerbs))
    {
        error (ERROR);
    }
    $herbs = $db -> Execute("SELECT `id`, `".$_POST['item']."` FROM `herbs` WHERE `gracz`=".$player -> id);
    if (empty ($herbs -> fields['id'])) 
    {
        error (NO_HERBS);
    }
    $herb = "$_POST[item]";
    $arrName = array(HERB1, HERB2, HERB3, HERB4, HERB5, HERB6, HERB7, HERB8);
    $intKey = array_search($_POST['item'], $arrHerbs);
    if (!isset($_POST['addall']))
      {
	integercheck($_POST['amount']);
	checkvalue($_POST['amount']);
      }
    else
      {
	$_POST['amount'] = $herbs -> fields[$herb];
      }
    if ($herbs -> fields[$herb] < $_POST['amount']) 
    {
        error (NO_MINERAL." ".$arrName[$intKey]);
    }
    $herbs -> Close();
    if ($_POST['pid'] == $player -> id) 
    {
        error (BAD_PLAYER);
    }
    $objDonated = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['pid']);
    if (empty ($objDonated -> fields['id'])) 
    {
        error (NO_PLAYER);
    }
    $strPlayerName = $objDonated -> fields['user'];
    $objDonated -> Close();
    $have = $db -> Execute("SELECT `id` FROM `herbs` WHERE `gracz`=".$_POST['pid']);
    if (empty ($have -> fields['id'])) 
    {
        $db -> Execute("INSERT INTO `herbs` (`gracz`, `".$_POST['item']."`) VALUES(".$_POST['pid'].",".$_POST['amount'].")");
    } 
        else 
    {
        $db -> Execute("UPDATE `herbs` SET `".$_POST['item']."`=`".$_POST['item']."`+".$_POST['amount']." WHERE `gracz`=".$_POST['pid']);
    }
    $have -> Close();
    $db -> Execute("UPDATE `herbs` SET `".$_POST['item']."`=`".$_POST['item']."`-".$_POST['amount']." WHERE `gracz`=".$player -> id);
    $strDate = $db -> DBDate($newdate);
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$arrName[$intKey].$strTitle.".', ".$strDate.", 'N')");
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].", ".$_POST['amount']." ".$arrName[$intKey].$strTitle.".', ".$strDate.", 'N')");
    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$arrName[$intKey].$strTitle.".', ".$strDate.")");
    error ("<br />".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, <b>".$_POST['amount']."</b> ".$arrName[$intKey]);
}

/**
* Give potions to another player
*/
if (isset ($_GET['action']) && $_GET['action'] == 'potions') 
{
    checkvalue($_POST['pid']);
    checkvalue($_POST['item']);
    if (strlen($_POST['title']) > 0)
      {
	$strTitle = strip_tags($_POST['title']);
	$strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
      }
    else
      {
	$strTitle = '';
      }
    $item = $db -> Execute("SELECT * FROM `potions` WHERE `id`=".$_POST['item']);
    if ($player -> id != $item -> fields['owner']) 
    {
        error (NOT_YOUR);
    }
    if (empty ($item -> fields['id'])) 
    {
        error (NO_ITEM);
    }
    if ($_POST['pid'] == $player -> id) 
    {
        error (BAD_PLAYER);
    }
    if ($item->fields['status'] != 'K')
      {
	error(ERROR);
      }
    $objDonated = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['pid']);
    if (empty ($objDonated -> fields['id'])) 
    {
        error (NO_PLAYER);
    }
    $strPlayerName = $objDonated -> fields['user'];
    $objDonated -> Close();
    if (!isset($_POST['addall']))
      {
	integercheck($_POST['amount']);
	checkvalue($_POST['amount']);
      }
    else
      {
	$_POST['amount'] = $item -> fields['amount'];
      }
    if ($item -> fields['amount'] < $_POST['amount']) 
    {
        error (NO_MINERAL." ".$item -> fields['name']);
    }
    $test = $db -> Execute("SELECT `id` FROM `potions` WHERE `name`='".$item -> fields['name']."' AND `owner`=".$_POST['pid']." AND `status`='K' AND `power`=".$item -> fields['power']);
    if (empty ($test -> fields['id'])) 
    {
        $db -> Execute("INSERT INTO potions (`owner`, `name`, `efect`, `power`, `amount`, `status`, `type`) VALUES(".$_POST['pid'].",'".$item -> fields['name']."','".$item -> fields['efect']."',".$item -> fields['power'].",".$_POST['amount'].",'K','".$item ->fields['type']."')") or error(E_DB4);
    } 
        else 
    {
        $db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$test ->fields['id']);
    }
    $test -> Close();
    if ($_POST['amount'] < $item -> fields['amount']) 
    {
        $db -> Execute("UPDATE `potions` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$item -> fields['id']);
    } 
        else 
    {
        $db -> Execute("DELETE FROM `potions` WHERE `id`=".$item -> fields['id']);
    }
    $strDate = $db -> DBDate($newdate);
    $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$item ->  fields['name']." (+".$item -> fields['power'].")".$strTitle.".', ".$strDate.", 'N')") or error (E_DB3);
    $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].", ".$_POST['amount']." ".T_AMOUNT." ".$item -> fields['name']." (+".$item -> fields['power'].")".$strTitle.".', ".$strDate.", 'N')");
    $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$item ->  fields['name']." (+".$item -> fields['power'].")".$strTitle.".', ".$strDate.")");
    error ("<br />".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid']."</b>, <b>".$_POST['amount']."</b> ".T_AMOUNT." <b>".$item -> fields['name']."</b> (+".$item -> fields['power'].").");
    $item -> Close();
}

/**
* Donations of item to another player
*/
if (isset ($_GET['action']) && $_GET['action'] == 'items') 
{
    checkvalue($_POST['pid']);
    checkvalue($_POST['item']);
    if (!isset($_POST['addall']))
      {
	integercheck($_POST['amount']);
	checkvalue($_POST['amount']);
      }
    if (strlen($_POST['title']) > 0)
      {
	$strTitle = strip_tags($_POST['title']);
	$strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
      }
    else
      {
	$strTitle = '';
      }
    $item = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_POST['item']);
    if ($item -> fields['status'] != 'U')
    {
        error(ERROR);
    }
    if ($player -> id != $item -> fields['owner']) 
    {
        error (NOT_YOUR);
    }
    if (empty ($item -> fields['id'])) 
    {
        error (NO_ITEM);
    }
    if ($_POST['pid'] == $player -> id) 
    {
        error (BAD_PLAYER);
    }
    $objDonated = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['pid']);
    if (empty ($objDonated -> fields['id'])) 
    {
        error (NO_PLAYER);
    }
    $strPlayerName = $objDonated -> fields['user'];
    $objDonated -> Close();
    if ($item->fields['type'] != 'R')
      {
	if (isset($_POST['addall']))
	  {
	    $_POST['amount'] = $item -> fields['amount'];
	  }
	if ($item -> fields['amount'] < $_POST['amount']) 
	  {
	    error (NO_MINERAL." ".$item -> fields['name']);
	  }
	$test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$item -> fields['name']."' AND `wt`=".$item -> fields['wt']." AND `type`='".$item -> fields['type']."' AND `status`='U' AND `owner`=".$_POST['pid']." AND `power`=".$item -> fields['power']." AND `zr`=".$item -> fields['zr']." AND `szyb`=".$item -> fields['szyb']." AND `maxwt`=".$item -> fields['maxwt']." AND `poison`=".$item -> fields['poison']." AND `cost`=".$item -> fields['cost']);
	if (empty ($test -> fields['id'])) 
	  {
	    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$_POST['pid'].",'".$item -> fields['name']."',".$item -> fields['power'].",'".$item -> fields['type']."',".$item -> fields['cost'].",".$item -> fields['zr'].",".$item -> fields['wt'].",".$item -> fields['minlev'].",".$item -> fields['maxwt'].",".$_POST['amount'].",'".$item -> fields['magic']."',".$item -> fields['poison'].",".$item -> fields['szyb'].",'".$item  -> fields['twohand']."', ".$item -> fields['repair'].")") or error(E_DB4);
	  } 
        else 
	  {
	    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
	  }
	if ($_POST['amount'] < $item -> fields['amount']) 
	  {
	    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$item -> fields['id']);
	  } 
        else 
	  {
	    $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$item -> fields['id']);
	  }
      }
    else
      {
	if (isset($_POST['addall']))
	  {
	    $_POST['amount'] = $item -> fields['wt'];
	  }
	if ($item -> fields['wt'] < $_POST['amount']) 
	  {
	    error (NO_MINERAL." ".$item -> fields['name']);
	  }
	$test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$item -> fields['name']."' AND `type`='R' AND `status`='U' AND `owner`=".$_POST['pid']." AND `power`=".$item -> fields['power']." AND `zr`=".$item -> fields['zr']." AND `szyb`=".$item -> fields['szyb']." AND `poison`=".$item -> fields['poison']." AND `cost`=".$item -> fields['cost']);
	if (empty ($test -> fields['id'])) 
	  {
	    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$_POST['pid'].",'".$item -> fields['name']."',".$item -> fields['power'].",'".$item -> fields['type']."',".$item -> fields['cost'].",".$item -> fields['zr'].",".$_POST['amount'].",".$item -> fields['minlev'].",".$_POST['amount'].",1,'".$item -> fields['magic']."',".$item -> fields['poison'].",".$item -> fields['szyb'].",'".$item  -> fields['twohand']."', ".$item -> fields['repair'].")") or error(E_DB4);
	  } 
        else 
	  {
	    $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$_POST['amount']." WHERE `id`=".$test -> fields['id']);
	  }
	if ($_POST['amount'] < $item -> fields['wt']) 
	  {
	    $db -> Execute("UPDATE `equipment` SET `wt`=`wt`-".$_POST['amount']." WHERE `id`=".$item -> fields['id']);
	  } 
        else 
	  {
	    $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$item -> fields['id']);
	  }
      }
    $test -> Close();
    
    // Display detailed information about bonuses of each item.
    $strAttributes = '(';
    switch($item -> fields['type'])
    {
        case 'A':   // Pieces of armor: defense, agility and durability.
        case 'L':
            $intAgi = $item -> fields['zr'] * -1;
            $strAttributes.= I_DEF.' +'.$item -> fields['power'].', '.I_AGI.' '.$intAgi.'%, '.I_DUR.' '.$item -> fields['wt'].'/'.$item -> fields['maxwt'];
            break;
        case 'B':   // Bows: speed and durability.
            $strAttributes.= I_SPE.' +'.$item -> fields['szyb'].', '.I_DUR.' '.$item -> fields['wt'].'/'.$item -> fields['maxwt'];
            break;
        case 'H':   // Helmets, 
        case 'P':   // plate legs,
        case 'S':   // and shiels: defense and durability.
            $strAttributes.= I_DEF.' +'.$item -> fields['power'].', '.I_DUR.' '.$item -> fields['wt'].'/'.$item -> fields['maxwt'];
            break;
        case 'R':   // Arrows: attack.
            $strAttributes.= I_ATT.' +'.$item -> fields['power'];
            break;
        case 'W':   // Melee weapons: attack and durability.
            $strAttributes.= I_ATT.' +'.$item -> fields['power'].', '.I_DUR.' '.$item -> fields['wt'].'/'.$item -> fields['maxwt'];
            break;
            
        case 'C':   // Mage robe: percent bonus to mana.
            $strAttributes.= I_MANA.' +'.$item -> fields['power'].'%';
            break;
        case 'T':   // Mage wand: percent bonus to spell strength.
            $strAttributes.= I_ATT.' +'.$item -> fields['power'].'%';
            break;
            
        case 'I':   // Rings: bonus type is in ring's description, so here we only display value.
        default:    // same for items that may be added in future, only 'power' field in database with no description.
            $strAttributes.= '+'.$item -> fields['power'];
	    break;
    }
    $strAttributes.= ')'.$strTitle.'.';

    $strDate = $db -> DBDate($newdate);
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER." <b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b>".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".I_AMOUNT." ".$item -> fields['name']." ".$strAttributes."', ".$strDate.", 'N')") or die($db->ErrorMsg());   
    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].'</b>, '.$_POST['amount']." ".T_AMOUNT." ".$item -> fields['name']." ".$strAttributes."', ".$strDate.", 'N')")or die($db->ErrorMsg());
    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].'</b>, '.$_POST['amount']." ".T_AMOUNT." ".$item -> fields['name']." ".$strAttributes."', ".$strDate.")")or die($db->ErrorMsg());
    
    error ("<br />".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid'].'</b> '.$_POST['amount']." ".T_AMOUNT." ".$item -> fields['name']." ".$strAttributes);
}


/**
* Bank robbery
*/
if ((isset ($_GET['action']) && $_GET['action'] == 'steal') && $player->clas == 'Złodziej') 
{
    checkvalue($_POST['tp']);
    if ($_POST['tp'] > 12)
      {
	error("Nie możesz przeznaczyć aż tylu punktów kradzieży (maksymalnie 12).");
      }
    if ($_POST['tp'] > $player->crime)
      {
	error("Nie masz tylu punktów kradzieży!");
      }
    require_once("includes/checkexp.php");
    $intMax = (50 - ($_POST['tp'] * 2)) * $player->level;
    $roll = rand (1, $intMax);
    if ($roll == 1)
      {
	$chance = 0;
      }
    elseif ($roll == $intMax)
      {
	$chance = 1000000;
      }
    else
      {
	/**
	 * Add bonus from bless
	 */
	$strBless = FALSE;
	$objBless = $db -> Execute("SELECT bless, blessval FROM players WHERE id=".$player -> id);
	if ($objBless -> fields['bless'] == 'inteli')
	  {
	    $player -> inteli = $player -> inteli + $objBless -> fields['blessval'];
	    $strBless = 'inteli';
	  }
	elseif ($objBless -> fields['bless'] == 'agility')
	  {
	    $player -> agility = $player -> agility + $objBless -> fields['blessval'];
	    $strBless = 'agility';
	  }
	elseif ($objBless -> fields['bless'] == 'speed')
	  {
	    $player->speed = $player->speed + $objBless -> fields['blessval'];
	    $strBless = 'speed';
	  }
	$objBless -> Close();
	if ($strBless)
	  {
	    $db -> Execute("UPDATE players SET bless='', blessval=0 WHERE id=".$player -> id);
	  }
	
	/**
	 * Add bonus from rings
	 */
	$arrEquip = $player -> equipment();
	$arrRings = array('zręczności', 'inteligencji', 'szybkości');
	$arrStat = array('agility', 'inteli', 'speed');
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

	$chance = ($player->agility + $player->inteli + $player->thievery + $player->speed) - $roll;
      }
    if ($chance < 1) 
      {
        $cost = 1000 * $player -> level;
        $expgain = ceil($player -> level / 10);
        checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', 0.01);
        $db -> Execute("UPDATE players SET miejsce='Lochy', crime=crime-".$_POST['tp']." WHERE id=".$player -> id);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.", '".VERDICT."', 7, ".$cost.", ".$strDate.")") or error (E_DB4);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".L_REASON.": ".$cost.".','".$newdate."', 'T')");
        error (C_CACHED);
      }
    else 
      { 
        $gain = $player -> level * 1000;
        $expgain = ($player -> level * 10);
	$fltThief = ($player->level / 100);
	if ($chance == 1000000)
	  {
	    $gain = 2 * $gain;
	    $expgain = 2 * $expgain;
	    $fltThief = 2 * $fltThief;
	  }
        $db -> Execute("UPDATE `players` SET `crime`=`crime`-".$_POST['tp'].", `credits`=`credits`+".$gain." WHERE `id`=".$player -> id);
        checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
        error (C_SUCCES.$gain.C_SUCCES2." Zdobyłeś ".$fltThief." w umiejętności Złodziejstwo.");
      }
}

/**
 * Buy safe box for astral components
 */
if (isset($_GET['action']) && $_GET['action'] == 'safe')
{
    $objSafebox = $db -> Execute("SELECT `level` FROM `astral_bank` WHERE `owner`=".$player -> id." AND `location`='V'");
    if ($objSafebox -> fields['level'] != 3)
    {
        if (!$objSafebox -> fields['level'])
        {
            $objSafebox -> fields['level'] = 0;
        }
        $arrSafeneed = array(array(20000, 150, 0, 0),
                             array(40000, 300, 50, 0),
                             array(100000, 450, 100, 50));
        $intKey = $objSafebox -> fields['level'];
        if ($player -> credits < $arrSafeneed[$intKey][0])
        {
            error(NO_MONEY);
        }
        if ($player -> platinum < $arrSafeneed[$intKey][1])
        {
            error(NO_MITHRIL);
        }
        $objMinerals = $db -> Execute("SELECT `crystal`, `adamantium` FROM `minerals` WHERE `owner`=".$player -> id);
        if ($objMinerals -> fields['adamantium'] < $arrSafeneed[$intKey][2])
        {
            error(NO_MINERAL." ".ADAMANTIUM."!");
        }
        if ($objMinerals -> fields['crystal'] < $arrSafeneed[$intKey][3])
        {
            error(NO_MINERAL." ".CRYSTAL."!");
        }
        if (!$objSafebox -> fields['level'])
        {
            $db -> Execute("INSERT INTO `astral_bank` (`owner`, `level`, `location`) VALUES(".$player -> id.", 1, 'V')");
        }
            else
        {
            $db -> Execute("UPDATE `astral_bank` SET `level`=`level`+1 WHERE `owner`=".$player -> id." AND `location`='V'");
        }
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$arrSafeneed[$intKey][0].", `platinum`=`platinum`-".$arrSafeneed[$intKey][1]." WHERE `id`=".$player -> id);
        $db -> Execute("UPDATE `minerals` SET `adamantium`=`adamantium`-".$arrSafeneed[$intKey][2].", `crystal`=`crystal`-".$arrSafeneed[$intKey][3]." WHERE `owner`=".$player -> id);
        error(YOU_UPGRADE);
    }
        else
    {
        error(SAFE_ENOUGH);
    }
    $objSafebox -> Close();
}

/**
* Assign variables to template
*/
$smarty -> assign(array("Potions" => '', 
                        "Minerals" => '', 
                        "Items" => '', 
                        "Crime" => '', 
                        "Herbs" => '',
                        "Safebox" => '',
                        "Bankinfo" => BANK_INFO,
                        "Iwant" => I_WANT,
                        "Awithdraw" => A_WITHDRAW,
                        "Adeposit" => A_DEPOSIT,
                        "Agive" => A_GIVE,
                        "Dplayer" => D_PLAYER,
                        "Goldcoins" => GOLD_COINS,
                        "Mamount" => M_AMOUNT,
                        "Iamount" => I_AMOUNT,
                        "Iamount2" => I_AMOUNT2,
                        "Aastral" => A_ASTRAL,
                        "Aastral2" => A_ASTRAL2,
                        "Hamount" => H_AMOUNT,
			"Goldcoins2" => "sztuk złota tytułem (opcjonalne, maks 50 znaków)",
			"Ttitle" => "tytułem (opcjonalne, maks 50 znaków)",
			"Tall" => "wszystkie posiadane"));

/**
 * Main menu
 */
if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] != 'astral'))
{
    /**
     * List of items
     */
    $item = $db -> Execute("SELECT `id`, `name`, `amount`, `power`, `zr`, `szyb`, `wt`, `type` FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='U'");
    if ($item -> fields['id']) 
    {
	$arrItems = array();
        while (!$item -> EOF) 
        {
	    if ($item->fields['type'] != 'R')
	      {
		$strAmount = $item->fields['amount'];
	      }
	    else
	      {
		$strAmount = $item->fields['wt'];
	      }
	    $strAgi = '';
	    $strSpeed = '';
	    if ($item->fields['zr'] != 0)
	      {
		$strAgi = " (".($item->fields['zr'] * -1)." ".I_AGI.")";
	      }
	    if ($item->fields['szyb'] != 0)
	      {
		$strSpeed = " (".$item->fields['szyb']." ".I_SPE.")";
	      }
	    $arrItems[$item->fields['id']] = $item->fields['name']." (+".$item->fields['power'].")".$strAgi.$strSpeed." (".I_AMOUNT2.": ".$strAmount.")";
            $item -> MoveNext();
        }
        $smarty -> assign (array("Ioptions" => $arrItems,
                                 "Items" => 1));
    }
    $item -> Close();

    /**
     * List of potions
     */
    $miks = $db -> Execute("SELECT `id`, `name`, `amount`, `power` FROM `potions` WHERE `owner`=".$player -> id." AND `status`='K'");
    if ($miks -> fields['id']) 
    {
	$arrPotions = array();
        while (!$miks -> EOF) 
        {
	    $arrPotions[$miks->fields['id']] = $miks->fields['name']." (+".$miks->fields['power'].") (".I_AMOUNT2.": ".$miks->fields['amount'].")";
            $miks -> MoveNext();
        }
        $smarty -> assign (array("Poptions" => $arrPotions,
                                 "Potions" => 1));
    }
    $miks -> Close();

    /**
     * List of herbs
     */
    $test = $db -> Execute("SELECT `illani`, `illanias`, `nutari`, `dynallca`, `ilani_seeds`, `illanias_seeds`, `nutari_seeds`, `dynallca_seeds` FROM `herbs` WHERE `gracz`=".$player -> id);
    $arrname = array ('illani','illanias','nutari','dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
    $arrName = array(HERB1, HERB2, HERB3, HERB4, HERB5, HERB6, HERB7, HERB8);
    $arrItem = array();
    for ($i = 0; $i < count($arrname); $i++)
      {
	if ($test->fields[$arrname[$i]] > 0)
	  {
	    $arrItem[$arrname[$i]] = $arrName[$i]." (".I_AMOUNT2.": ".$test->fields[$arrname[$i]].")";
	  }
      }
    if (count($arrItem) > 0)
      {
	$intHerbs = 1;
      }
    else
      {
	$intHerbs = 0;
      }
    $smarty -> assign (array("Herbs" => $intHerbs, 
			     "Hoptions" => $arrItem));
    $test -> Close();

    /**
     * List of minerals
     */
    $objMinerals = $db -> Execute("SELECT `copperore`, `zincore`, `tinore`, `ironore`, `coal`, `copper`, `bronze`, `brass`, `iron`, `steel`, `pine`, `hazel`, `yew`, `elm`, `crystal`, `adamantium`, `meteor` FROM `minerals` WHERE `owner`=".$player -> id);
    $arrSqlname = array('copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
    $arrMinerals = array(MIN1, MIN2, MIN3, MIN4, MIN5, MIN6, MIN7, MIN8, MIN9, MIN10, MIN11, MIN12, MIN13, MIN14, MIN15, MIN16, MIN17);
    $arrOptions = array();
    for ($i = 0; $i < count($arrSqlname); $i++)
      {
	if ($objMinerals->fields[$arrSqlname[$i]] > 0)
	  {
	    $arrOptions[$arrSqlname[$i]] = $arrMinerals[$i]." (".I_AMOUNT2.": ".$objMinerals->fields[$arrSqlname[$i]].")";
	  }
      }
    if (count($arrOptions) > 0)
      {
	$intMinerals = 1;
      }
    else
      {
	$intMinerals = 0;
      }
    $smarty -> assign (array("Minerals" => $intMinerals, 
			     "Moptions" => $arrOptions));
    $objMinerals -> Close();

    /**
     * Buy safe box
     */
    $objSafebox = $db -> Execute("SELECT `level` FROM `astral_bank` WHERE `owner`=".$player -> id." AND `location`='V'");
    if ($objSafebox -> fields['level'] != 3)
    {
        if (!$objSafebox -> fields['level'])
        {
            $objSafebox -> fields['level'] = 0;
        }
        $arrSafeneed = array(array(20000, 150, 0, 0),
                             array(40000, 300, 50, 0),
                             array(100000, 450, 100, 50));
        $intKey = $objSafebox -> fields['level'];
        $intUpgrade = $intKey + 1;
        $strSafebox = BUY_SAFE.$intUpgrade.BUY_SAFE2.$arrSafeneed[$intKey][0].ASTRAL_GOLD.", ".$arrSafeneed[$intKey][1].ASTRAL_MITH.", ".$arrSafeneed[$intKey][2]." ".ADAMANTIUM.", ".$arrSafeneed[$intKey][3]." ".CRYSTAL.".";
        $smarty -> assign("Safebox", "<a href=\"bank.php?action=safe\">".$strSafebox."</a><br />");
    }
    $objSafebox -> Close();
    
    /**
     * Steal action (only for thief)
     */
    if ($player->clas == 'Złodziej' && $player->crime > 0) 
      {
	$smarty->assign(array("Crime" => "Y",
			      "Asteal" => "Okradnij",
			      "Tcrime" => "bank przeznaczając na to",
			      "Ttp" => "punktów kradzieży (maksymalnie 12 punktów)."));
      }
}

/**
 * Astral vault
 */
if (isset($_GET['action']) && $_GET['action'] == 'astral')
{
    if (!isset($_GET['type']))
    {
        error(ERROR);
    }

    $smarty -> assign("Type", $_GET['type']);

    /**
     * List of maps, plans, recipes
     */
    if ($_GET['type'] == 'p')
    {
        $arrMaps = array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7);
        $arrMaps2 = array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7);
        $arrPlans = array(PLAN1, PLAN2, PLAN3, PLAN4, PLAN5);
        $arrPlans2 = array(PLAN1, PLAN2, PLAN3, PLAN4, PLAN5);
        $arrRecipes = array(RECIPE1, RECIPE2, RECIPE3, RECIPE4, RECIPE5);
        $arrRecipes2 = array(RECIPE1, RECIPE2, RECIPE3, RECIPE4, RECIPE5);
        $arrNames = array_merge($arrMaps, $arrPlans, $arrRecipes);

        require_once('includes/astralvault.php');

        showastral('V', 'bank.php?action=astral&amp;type=p', $player -> id);
    
        $smarty -> assign(array("Tname" => T_NAME,
                                "Tmaps" => T_MAPS,
                                "Tplans" => T_PLANS,
                                "Trecipes" => T_RECIPES,
                                "Tmaps2" => T_MAPS2,
                                "Tplans2" => T_PLANS2,
                                "Trecipes2" => T_RECIPES2,
                                "Mapsname" => $arrMaps,
                                "Plansname" => $arrPlans,
                                "Recipesname" => $arrRecipes,
                                "Mapsname2" => $arrMaps2,
                                "Plansname2" => $arrPlans2,
                                "Recipesname2" => $arrRecipes2,
                                "Mapsamount" => $arrMapsamount,
                                "Plansamount" => $arrPlansamount,
                                "Recipesamount" => $arrRecipesamount,
                                "Mapsamount2" => $arrCmapsamount,
                                "Plansamount2" => $arrCplansamount,
                                "Recipesamount2" => $arrCrecipesamount,
                                "Tcomponents" => $arrNames,
                                "Tsend" => T_SEND,
                                "Tpiece" => T_PIECE,
                                "Tnumber" => T_NUMBER,
                                "Tamount" => T_AMOUNT2,
                                "Agive" => A_GIVE2,
				"Asend" => "Wyślij",
				"Tall" => "wszystko",
				"Taplayer" => "graczowi o ID",
				"Taplan" => "wszystkie kawałki",
                                "Message" => ''));
    }

    /**
     * List of components
     */
    if ($_GET['type'] == 'c')
    {
        $arrCompnames = array(array(COMP1, COMP2, COMP3, COMP4, COMP5, COMP6, COMP7), 
                              array(CONST1, CONST2, CONST3, CONST4, CONST5), 
                              array(POTION1, POTION2, POTION3, POTION4, POTION5));
        $arrCompnames2 = array(MAGICCOMP, MAGICCONST, MAGICPOTIONS);
        $arrNames2 = array_merge($arrCompnames[0], $arrCompnames[1], $arrCompnames[2]);
        
        require_once('includes/astralvault.php');

        $arrComponents = showcomponents('V', $player -> id);
    
        $smarty -> assign(array("Tname" => T_NAME,
                                "Tmagic" => $arrCompnames2,
                                "Tcomp" => $arrCompnames,
                                "Components" => $arrComponents,
                                "Tcomponents2" => $arrNames2,
                                "Tsend" => T_SEND,
                                "Tnumber" => T_NUMBER,
                                "Tamount" => T_AMOUNT2,
                                "Agive" => A_GIVE2,
                                "Tcomponent3" => T_COMPONENT,
                                "Message" => ''));
    }

    /**
     * Merge plans, maps, recipes
     */
    if (isset($_GET['step']) && (!in_array($_GET['step'], array('piece', 'component', 'plan', 'all'))))
    {
        mergeplans('V', $player -> id);
        $smarty -> assign("Message", YOU_MERGE);
    }

    if (isset($_GET['step']) && (in_array($_GET['step'], array('piece', 'component', 'plan'))))
      {
	$_POST['name'] = intval($_POST['name']);
        if ($_POST['name'] < 0)
	  {
            error(ERROR);
	  }
	checkvalue($_POST['pid']);
	$objDonated = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_POST['pid']);
        if (empty($objDonated -> fields['id'])) 
	  {
            error(NO_PLAYER);
	  }
        $objDonated -> Close();
        if ($_POST['pid'] == $player -> id)
	  {
            error(BAD_PLAYER);
	  }
        $intCompname = $_POST['name'];
	if ($_GET['step'] != 'component')
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
	    $strType = PIECE;
            $strCompname = $arrNames[$intCompname];
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
            $strType = COMPONENT;
            $strCompname = $arrNames2[$intCompname];
	  }
	$arrNumber = array(0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 0, 1, 2, 3, 4);
        $strPiecename = $strName.$arrNumber[$_POST['name']];
      }

    /**
     * Give all selected plan parts to selected player
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'plan'))
      {
	$objAmount = $db -> Execute("SELECT `amount`, `number` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `location`='V'") or die($db -> ErrorMsg());
        if (!$objAmount -> fields['amount'])
	  {
            error(NO_AMOUNT);
	  }
	while(!$objAmount->EOF)
	  {
	    $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$_POST['pid']." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	    if (!$objTest -> fields['amount'])
	      {
		$db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$_POST['pid'].", '".$strPiecename."', ".$objAmount->fields['number'].", ".$objAmount->fields['amount'].", 'V')");
	      }
            else
	      {
		$db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$objAmount->fields['amount']." WHERE `owner`=".$_POST['pid']." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	      }
	    $objTest -> Close();
	    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	    $objAmount->MoveNext();
	  }
	$objAmount->Close();
	$strMessage = YOU_GIVE." wszystkie części ".$strCompname." ".D_PLAYER.": ".$_POST['pid'].".";
        $strMessage2 = YOU_GET." wszystkie części ".$strCompname." ".D_PLAYER2.$player -> id.".";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'N')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."', 'N')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."')");
	$smarty -> assign("Message", $strMessage);
      }

    /**
     * Give everything to selected player
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'all'))
      {
	checkvalue($_POST['pid']);
	$objDonated = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_POST['pid']);
        if (empty($objDonated -> fields['id'])) 
	  {
            error(NO_PLAYER);
	  }
        $objDonated -> Close();
        if ($_POST['pid'] == $player -> id)
	  {
            error(BAD_PLAYER);
	  }
	$objAmount = $db -> Execute("SELECT `amount`, `number`, `type` FROM `astral` WHERE `owner`=".$player -> id." AND (`type` LIKE 'M%' OR `type` LIKE 'P%' OR `type` LIKE 'R%') AND `location`='V'") or die($db -> ErrorMsg());
	while (!$objAmount->EOF)
	  {
	    $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$_POST['pid']." AND `type`='".$objAmount->fields['type']."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	    if (!$objTest -> fields['amount'])
	      {
		$db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$_POST['pid'].", '".$objAmount->fields['type']."', ".$objAmount->fields['number'].", ".$objAmount->fields['amount'].", 'V')");
	      }
            else
	      {
		$db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$objAmount->fields['amount']." WHERE `owner`=".$_POST['pid']." AND `type`='".$objAmount->fields['type']."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	      }
	    $objTest -> Close();
	    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$objAmount->fields['type']."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	    $objAmount->MoveNext();
	  }
	$objAmount->Close();
	$strMessage = YOU_GIVE." wszystkie części astralne ".D_PLAYER.": ".$_POST['pid'].".";
        $strMessage2 = YOU_GET." wszystkie części astralne ".D_PLAYER2.$player -> id.".";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'N')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."', 'N')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."')");
	$smarty -> assign("Message", $strMessage);
      }

    /**
     * Give item to player
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'piece' || $_GET['step'] == 'component'))
    {
        integercheck($_POST['amount']);
	checkvalue($_POST['amount']);
	checkvalue($_POST['number']);
        $intNumber = $_POST['number'] - 1;
	$objAmount = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'") or die($db -> ErrorMsg());
        if (!$objAmount -> fields['amount'])
	  {
            error(NO_AMOUNT);
	  }
        if ($objAmount -> fields['amount'] < $_POST['amount'])
        {
            error(NO_AMOUNT);
        }
	$objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$_POST['pid']." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
        if (!$objTest -> fields['amount'])
        {
            $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$_POST['pid'].", '".$strPiecename."', ".$intNumber.", ".$_POST['amount'].", 'V')");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$_POST['amount']." WHERE `owner`=".$_POST['pid']." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
        }
        $objTest -> Close();
        if ($objAmount -> fields['amount'] == $_POST['amount'])
        {
            $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`-".$_POST['amount']." WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
        }
        $objAmount -> Close();
        $strMessage = YOU_GIVE.$strType.$strCompname.M_AMOUNT2.$_POST['amount']." ".D_PLAYER.": ".$_POST['pid'].".";
        $strMessage2 = YOU_GET.$strType.$strCompname.M_AMOUNT2.$_POST['amount'].D_PLAYER2.$player -> id.".";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'N')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."', 'N')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."', 'N')");
        $smarty -> assign("Message", $strMessage);
    }
}

/**
* Initialization of variable
*/
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}

/**
* Display site
*/
$smarty -> assign (array("Bank" => $player -> bank,
    "Gold" => $player -> credits, 
    "Action" => $_GET['action']));
$smarty -> display ('bank.tpl');

require_once("includes/foot.php");

?>
