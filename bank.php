<?php
/**
 *   Funkcje pliku:
 *   Bank - deposit gold and give item to another player
 *
 *   @name                 : bank.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : yeskov <yeskov@users.sourceforge.net>
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

$title = "Bank";
require_once("includes/head.php");

/**
* Get the localization for game
*/

require_once("languages/".$lang."/bank.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

if (isset($_GET['action']))
  {
    /**
     * Withdraw gold from bank
     */
    if ($_GET['action'] == 'withdraw') 
      {
	if (!isset($_POST['with'])) 
	  {
	    message('error', EMPTY_FIELD);
	  }
	else
	  {
	    checkvalue($_POST['with']);
	    if ($_POST['with'] > $player -> bank) 
	      {
		$_POST['with'] = $player->bank;
	      }
	    $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$_POST['with'].", `bank`=`bank`-".$_POST['with']." WHERE `id`=".$player -> id);
	    $player->credits += $_POST['with'];
	    $player->bank -= $_POST['with'];
	    message('success', WITHDRAW." ".$_POST['with']." ".GOLD_COINS);
	  }
      }

    /**
     * Deposit gold to bank
     */
    elseif ($_GET['action'] == 'deposit') 
      {
	if (!isset($_POST['dep'])) 
	  {
	    message('error', EMPTY_FIELD);
	  }
	else
	  {
	    checkvalue($_POST['dep']);
	    if ($_POST['dep'] > $player -> credits) 
	      {
		$_POST['dep'] = $player->credits;
	      }
	    $db -> Execute("UPDATE players SET credits=credits-".$_POST['dep'].", bank=bank+".$_POST['dep']." WHERE id=".$player -> id);
	    $player->credits -= $_POST['dep'];
	    $player->bank += $_POST['dep'];
	    message ('success', DEPOSIT." ".$_POST['dep']." ".GOLD_COINS);
	  }
      }

    /**
     * Donations of gold for another player
     */
    elseif ($_GET['action'] == 'donation') 
      {
	if ($_POST['player'] == 0)
	  {
	    checkvalue($_POST['pid']);
	  }
	else
	  {
	    checkvalue($_POST['player']);
	    $_POST['pid'] = $_POST['player'];
	  }
	checkvalue($_POST['with']);	
	if (strlen($_POST['title']) > 0)
	  {
	    $strTitle = htmlspecialchars($_POST['title'], ENT_QUOTES);
	    $strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
	  }
	else
	  {
	    $strTitle = '';
	  }
	$blnValid = TRUE;
	if ($player->bank < $_POST['with'])
	  {
	    message('error', NO_GOLD);
	    $blnValid = FALSE;
	  }
	if ($player -> credits < 0)
	  {
	    message('error', MINUS_GOLD);
	    $blnValid = FALSE;
	  }
	if ($_POST['pid'] == $player -> id) 
	  {
	    message('error', BAD_PLAYER);
	    $blnValid = FALSE;
	  }
	$objDonated = $db -> Execute("SELECT `id`, `user`, `tribe` FROM `players` WHERE `id`=".$_POST['pid']);
	if (!$objDonated -> fields['id'])
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $strPlayerName = $arrTags[$objDonated->fields['tribe']][0].' '.$objDonated -> fields['user'].' '.$arrTags[$objDonated->fields['tribe']][1];
	    $db -> Execute("UPDATE `players` SET `bank`=`bank`+".$_POST['with']." WHERE `id`=".$_POST['pid']);
	    $db -> Execute("UPDATE `players` SET `bank`=`bank`-".$_POST['with']." WHERE `id`=".$player -> id);
	    $player->bank -= $_POST['with'];
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER." <b><a href=view.php?view=".$player -> id.">".$arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1]."</a></b>".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['with']." sztuk złota".$strTitle.".', ".$strDate.", 'N')");    
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid']."</b> ".G_AMOUNT." ".$_POST['with']." sztuk złota".$strTitle.".', ".$strDate.", 'N')");    
	    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid']."</b> ".G_AMOUNT." ".$_POST['with']." sztuk złota".$strTitle.".', ".$strDate.")");
	    message('success', YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid']."</b> ".G_AMOUNT." ".$_POST['with']." ".GOLD_COINS);
	  }
	$objDonated -> Close();
      }

    /**
     * Mithril donations to another player
     */
    elseif ($_GET['action'] == 'mithril') 
      {
	if ($_POST['player'] == 0)
	  {
	    checkvalue($_POST['pid']);
	  }
	else
	  {
	    checkvalue($_POST['player']);
	    $_POST['pid'] = $_POST['player'];
	  }
	checkvalue($_POST['mithril']);	
	if (strlen($_POST['title']) > 0)
	  {
	    $strTitle = htmlspecialchars($_POST['title'], ENT_QUOTES);;
	    $strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
	  }
	else
	  {
	    $strTitle = '';
	  }
	$blnValid = TRUE;
	if ($_POST['pid'] == $player -> id) 
	  {
	    message('error', BAD_PLAYER);
	    $blnValid = FALSE;
	  }
	if ($player->platinum < $_POST['mithril'])
	  {
	    message('error', NO_MITHRIL);
	    $blnValid = FALSE;
	  }
	$objDonated = $db -> Execute("SELECT `id`, `user`, `tribe` FROM `players` WHERE `id`=".$_POST['pid']);
	if (!$objDonated -> fields['id'])
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $strPlayerName = $arrTags[$objDonated->fields['tribe']][0].' '.$objDonated -> fields['user'].' '.$arrTags[$objDonated->fields['tribe']][1];
	    $db -> Execute("UPDATE `players` SET `platinum`=`platinum`+".$_POST['mithril']." WHERE `id`=".$_POST['pid']);
	    $db -> Execute("UPDATE `players` SET `platinum`=`platinum`-".$_POST['mithril']." WHERE `id`=".$player -> id);
	    $player->platinum -= $_POST['mithril'];
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER." <b><a href=view.php?view=".$player -> id.">".$arrTags[$player->tribe][0].' '.$player ->user.' '.$arrTags[$player->tribe][1]."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['mithril']." sztuk mithrilu".$strTitle.".', ".$strDate.", 'N')");
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['mithril']." sztuk mithrilu".$strTitle.".', ".$strDate.", 'N')");
	    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['mithril']." sztuk mithrilu ".$strTitle.".', ".$strDate.")");
	    message('success', YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['mithril']." ".M_AMOUNT."");
	  }
	$objDonated -> Close();
      }

    /**
     * Give minerals to another player
     */
    elseif ($_GET['action'] == 'minerals') 
      {
	if ($_POST['player'] == 0)
	  {
	    checkvalue($_POST['pid']);
	  }
	else
	  {
	    checkvalue($_POST['player']);
	    $_POST['pid'] = $_POST['player'];
	  }
	if (strlen($_POST['title']) > 0)
	  {
	    $strTitle = htmlspecialchars($_POST['title'], ENT_QUOTES);;
	    $strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
	  }
	else
	  {
	    $strTitle = '';
	  }
	$arrSqlname = array('copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm');
	$blnValid = TRUE;
	if (!in_array($_POST['item'], $arrSqlname)) 
	  {
	    message('error', ERROR);
	    $blnValid = FALSE;
	  }
	$intKey = array_search($_POST['item'], $arrSqlname);
	$objMinerals = $db -> Execute("SELECT ".$_POST['item']." FROM `minerals` WHERE `owner`=".$player -> id);
	if (!$objMinerals -> fields[$_POST['item']]) 
	  {
	    message('error', NO_MINERALS);
	    $blnValid = FALSE;
	  }
	if (!isset($_POST['addall']))
	  {
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
	    message('error', NO_MINERAL." ".$strMineralname);
	    $blnValid = FALSE;
	  }
	$objMinerals -> Close();
	if ($_POST['pid'] == $player -> id) 
	  {
	    message('error', BAD_PLAYER);
	    $blnValid = FALSE;
	  }
	$objDonated = $db -> Execute("SELECT `id`, `user`, `tribe` FROM `players` WHERE `id`=".$_POST['pid']);
	if (empty($objDonated -> fields['id'])) 
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $strPlayerName = $arrTags[$objDonated->fields['tribe']][0].' '.$objDonated -> fields['user'].' '.$arrTags[$objDonated->fields['tribe']][1];
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
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER." <b><a href=view.php?view=".$player -> id.">".$arrTags[$player->tribe][0].' '.$player ->user.' '.$arrTags[$player->tribe][1]."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$strMineralname.$strTitle.".', ".$strDate.", 'N')");
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['amount']." ".T_AMOUNT." ".$strMineralname.$strTitle.".', ".$strDate.", 'N')");
	    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.", '".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].", ".$_POST['amount']." ".T_AMOUNT." ".$strMineralname.$strTitle.".', ".$strDate.")");
	    message('success', YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, <b>".$_POST['amount']."</b> ".T_AMOUNT." <b>".$strMineralname."</b>.");
	  }
	$objDonated -> Close();
      }

    /**
     * Give herbs to another player
     */
    elseif ($_GET['action'] == 'herbs') 
      {
	if ($_POST['player'] == 0)
	  {
	    checkvalue($_POST['pid']);
	  }
	else
	  {
	    checkvalue($_POST['player']);
	    $_POST['pid'] = $_POST['player'];
	  }
	if (strlen($_POST['title']) > 0)
	  {
	    $strTitle = htmlspecialchars($_POST['title'], ENT_QUOTES);
	    $strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
	  }
	else
	  {
	    $strTitle = '';
	  }
	$arrHerbs = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	$blnValid = TRUE;
	if (!in_array($_POST['item'], $arrHerbs))
	  {
	    message('error', ERROR);
	    $blnValid = FALSE;
	  }
	$herbs = $db -> Execute("SELECT `id`, `".$_POST['item']."` FROM `herbs` WHERE `gracz`=".$player -> id);
	if (empty ($herbs -> fields['id'])) 
	  {
	    message('error', NO_HERBS);
	    $blnValid = FALSE;
	  }
	$herb = "$_POST[item]";
	$arrName = array(HERB1, HERB2, HERB3, HERB4, HERB5, HERB6, HERB7, HERB8);
	$intKey = array_search($_POST['item'], $arrHerbs);
	if (!isset($_POST['addall']))
	  {
	    checkvalue($_POST['amount']);
	  }
	else
	  {
	    $_POST['amount'] = $herbs -> fields[$herb];
	  }
	if ($herbs -> fields[$herb] < $_POST['amount']) 
	  {
	    message('error', NO_MINERAL." ".$arrName[$intKey]);
	    $blnValid = FALSE;
	  }
	$herbs -> Close();
	if ($_POST['pid'] == $player -> id) 
	  {
	    message('error', BAD_PLAYER);
	    $blnValid = FALSE;
	  }
	$objDonated = $db -> Execute("SELECT `id`, `user`, `tribe` FROM `players` WHERE `id`=".$_POST['pid']);
	if (empty ($objDonated -> fields['id'])) 
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $strPlayerName = $arrTags[$objDonated->fields['tribe']][0].' '.$objDonated -> fields['user'].' '.$arrTags[$objDonated->fields['tribe']][1];
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
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$arrTags[$player->tribe][0].' '.$player ->user.' '.$arrTags[$player->tribe][1]."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$arrName[$intKey].$strTitle.".', ".$strDate.", 'N')");
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['amount']." ".$arrName[$intKey].$strTitle.".', ".$strDate.", 'N')");
	    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$arrName[$intKey].$strTitle.".', ".$strDate.")");
	    message('success', YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, <b>".$_POST['amount']."</b> ".$arrName[$intKey]);
	  }
	$objDonated -> Close();
      }

    /**
     * Give potions to another player
     */
    elseif ($_GET['action'] == 'potions') 
      {
	if ($_POST['player'] == 0)
	  {
	    checkvalue($_POST['pid']);
	  }
	else
	  {
	    checkvalue($_POST['player']);
	    $_POST['pid'] = $_POST['player'];
	  }
	checkvalue($_POST['item']);
	if (strlen($_POST['title']) > 0)
	  {
	    $strTitle = htmlspecialchars($_POST['title'], ENT_QUOTES);
	    $strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
	  }
	else
	  {
	    $strTitle = '';
	  }
	$item = $db -> Execute("SELECT * FROM `potions` WHERE `id`=".$_POST['item']);
	$blnValid = TRUE;
	if ($player -> id != $item -> fields['owner']) 
	  {
	    message('error', NOT_YOUR);
	    $blnValid = FALSE;
	  }
	if (empty ($item -> fields['id'])) 
	  {
	    message('error', NO_ITEM);
	    $blnValid = FALSE;
	  }
	if ($_POST['pid'] == $player -> id) 
	  {
	    message('error', BAD_PLAYER);
	    $blnValid = FALSE;
	  }
	if ($item->fields['status'] != 'K')
	  {
	    message('error', ERROR);
	    $blnValid = FALSE;
	  }
	$objDonated = $db -> Execute("SELECT `id`, `user`, `tribe` FROM `players` WHERE `id`=".$_POST['pid']);
	if (empty ($objDonated -> fields['id'])) 
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
	$strPlayerName = $arrTags[$objDonated->fields['tribe']][0].' '.$objDonated -> fields['user'].' '.$arrTags[$objDonated->fields['tribe']][1];
	$objDonated -> Close();
	if (!isset($_POST['addall']))
	  {
	    checkvalue($_POST['amount']);
	  }
	else
	  {
	    $_POST['amount'] = $item -> fields['amount'];
	  }
	if ($item -> fields['amount'] < $_POST['amount']) 
	  {
	    message('error', NO_MINERAL." ".$item -> fields['name']);
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
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
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$arrTags[$player->tribe][0].' '.$player ->user.' '.$arrTags[$player->tribe][1]."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$item ->  fields['name']." (+".$item -> fields['power'].")".$strTitle.".', ".$strDate.", 'N')") or error (E_DB3);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid']."</b>, ".$_POST['amount']." ".T_AMOUNT." ".$item -> fields['name']." (+".$item -> fields['power'].")".$strTitle.".', ".$strDate.", 'N')");
	    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].",'".T_PLAYER."<b><a href=view.php?view=".$player -> id.">".$player ->user."</a></b> ".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".$item ->  fields['name']." (+".$item -> fields['power'].")".$strTitle.".', ".$strDate.")");
	    message('success', YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid']."</b>, <b>".$_POST['amount']."</b> ".T_AMOUNT." <b>".$item -> fields['name']."</b> (+".$item -> fields['power'].").");
	  }
	$item -> Close();
      }

    /**
     * Donations of item to another player
     */
    elseif ($_GET['action'] == 'items') 
      {
	if ($_POST['player'] == 0)
	  {
	    checkvalue($_POST['pid']);
	  }
	else
	  {
	    checkvalue($_POST['player']);
	    $_POST['pid'] = $_POST['player'];
	  }
	checkvalue($_POST['item']);
	if (!isset($_POST['addall']))
	  {
	    checkvalue($_POST['amount']);
	  }
	if (strlen($_POST['title']) > 0)
	  {
	    $strTitle = htmlspecialchars($_POST['title'], ENT_QUOTES);
	    $strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
	  }
	else
	  {
	    $strTitle = '';
	  }
	$item = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_POST['item']." AND `status`='U' AND `owner`=".$player->id);
	$blnValid = TRUE;
	if (empty($item -> fields['id'])) 
	  {
	    message('error', NO_ITEM);
	    $blnValid = FALSE;
	  }
	if ($item->fields['type'] == 'Q')
	  {
	    message('error', 'Nie możesz przekazać tego przemiotu.');
	    $blnValid = FALSE;
	  }
	if ($_POST['pid'] == $player -> id) 
	  {
	    message('error', BAD_PLAYER);
	    $blnValid = FALSE;
	  }
	$objDonated = $db -> Execute("SELECT `id`, `user`, `tribe` FROM `players` WHERE `id`=".$_POST['pid']);
	if (empty ($objDonated -> fields['id'])) 
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
	if (($item->fields['type'] != 'R') && ($item -> fields['amount'] < $_POST['amount']))
	  {
	    message('error', NO_MINERAL." ".$item -> fields['name']);
	    $blnValid = FALSE;
	  }
	elseif (($item->fields['type'] == 'R') && ($item -> fields['wt'] < $_POST['amount']))
	  {
	    message('error', NO_MINERAL." ".$item -> fields['name']);
	    $blnValid = FALSE;
	  }
	$strPlayerName = $arrTags[$objDonated->fields['tribe']][0].' '.$objDonated -> fields['user'].' '.$arrTags[$objDonated->fields['tribe']][1];
	$objDonated -> Close();
	if ($blnValid)
	  {
	    if ($item->fields['type'] != 'R')
	      {
		if (isset($_POST['addall']))
		  {
		    $_POST['amount'] = $item -> fields['amount'];
		  }
		$test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$item -> fields['name']."' AND `wt`=".$item -> fields['wt']." AND `type`='".$item -> fields['type']."' AND `status`='U' AND `owner`=".$_POST['pid']." AND `power`=".$item -> fields['power']." AND `zr`=".$item -> fields['zr']." AND `szyb`=".$item -> fields['szyb']." AND `maxwt`=".$item -> fields['maxwt']." AND `poison`=".$item -> fields['poison']." AND `cost`=".$item -> fields['cost']." AND `minlev`=".$item->fields['minlev']." AND `magic`='".$item->fields['magic']."' AND `ptype`='".$item->fields['ptype']."'");
		if (empty ($test -> fields['id'])) 
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`, `ptype`) VALUES(".$_POST['pid'].",'".$item -> fields['name']."',".$item -> fields['power'].",'".$item -> fields['type']."',".$item -> fields['cost'].",".$item -> fields['zr'].",".$item -> fields['wt'].",".$item -> fields['minlev'].",".$item -> fields['maxwt'].",".$_POST['amount'].",'".$item -> fields['magic']."',".$item -> fields['poison'].",".$item -> fields['szyb'].",'".$item  -> fields['twohand']."', ".$item -> fields['repair'].", '".$item->fields['ptype']."')") or error(E_DB4);
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
		$test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$item -> fields['name']."' AND `type`='R' AND `status`='U' AND `owner`=".$_POST['pid']." AND `power`=".$item -> fields['power']." AND `zr`=".$item -> fields['zr']." AND `szyb`=".$item -> fields['szyb']." AND `poison`=".$item -> fields['poison']." AND `cost`=".$item -> fields['cost']." AND `magic`='".$item->fields['magic']."' AND `ptype`='".$item->fields['ptype']."'");
		if (empty ($test -> fields['id'])) 
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`, `ptype`) VALUES(".$_POST['pid'].",'".$item -> fields['name']."',".$item -> fields['power'].",'".$item -> fields['type']."',".$item -> fields['cost'].",".$item -> fields['zr'].",".$_POST['amount'].",".$item -> fields['minlev'].",".$_POST['amount'].",1,'".$item -> fields['magic']."',".$item -> fields['poison'].",".$item -> fields['szyb'].",'".$item  -> fields['twohand']."', ".$item -> fields['repair'].", '".$item->fields['ptype']."')") or error(E_DB4);
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
	    
	    // Display detailed information about bonuses of each item.
	    $strAttributes = '(';
	    if ($item->fields['zr'] != 0)
	      {
		$strAgi = I_AGI.' '.($item->fields['zr'] * -1).', ';
	      }
	    else
	      {
		$strAgi = '';
	      }
	    switch($item -> fields['type'])
	      {
	      case 'A':   // Pieces of armor: defense, agility and durability.
	      case 'L':
		$strAttributes.= I_DEF.' +'.$item -> fields['power'].', '.$strAgi.I_DUR.' '.$item -> fields['wt'].'/'.$item -> fields['maxwt'];
		break;
	      case 'B':   // Bows: speed and durability.
		$strAttributes.= I_SPE.' +'.$item -> fields['szyb'].', '.$strAgi.I_DUR.' '.$item -> fields['wt'].'/'.$item -> fields['maxwt'];
		break;
	      case 'H':   // Helmets, 
	      case 'S':   // and shiels: defense and durability.
		$strAttributes.= I_DEF.' +'.$item -> fields['power'].', '.$strAgi.I_DUR.' '.$item -> fields['wt'].'/'.$item -> fields['maxwt'];
		break;
	      case 'R':   // Arrows: attack.
		$strAttributes.= I_ATT.' +'.$item -> fields['power'];
		break;
	      case 'W':   // Melee weapons: attack and durability.
		$strAttributes.= I_ATT.' +'.$item -> fields['power'].', '.$strAgi.I_DUR.' '.$item -> fields['wt'].'/'.$item -> fields['maxwt'];
		break;
		
	      case 'C':   // Mage robe: percent bonus to mana.
		$strAttributes.= I_MANA.' +'.$item -> fields['power'].'%';
		break;
	      case 'T':   // Mage wand: percent bonus to spell strength.
		$strAttributes.= I_ATT.' +'.$item -> fields['power'].'%';
		break;
		// Crafts plans
	      case 'P':
		$strAttributes .= 'plan, poziom: '.$item->fields['minlev'];
		break;
	      case 'I':   // Rings: bonus type is in ring's description, so here we only display value.
	      default:    // same for items that may be added in future, only 'power' field in database with no description.
		$strAttributes.= '+'.$item -> fields['power'];
		break;
	      }
	    $strAttributes.= ')'.$strTitle.'.';
	    
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].",'".T_PLAYER." <b><a href=view.php?view=".$player -> id.">".$arrTags[$player->tribe][0].' '.$player ->user.' '.$arrTags[$player->tribe][1]."</a></b>".T_ID."<b>".$player -> id."</b>, ".T_GIVE." ".$_POST['amount']." ".I_AMOUNT." ".$item -> fields['name']." ".$strAttributes."', ".$strDate.", 'N')") or die($db->ErrorMsg());   
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].'</b>, '.$_POST['amount']." ".T_AMOUNT." ".$item -> fields['name']." ".$strAttributes."', ".$strDate.", 'N')")or die($db->ErrorMsg());
	    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b> ID<b> ".$_POST['pid'].'</b>, '.$_POST['amount']." ".T_AMOUNT." ".$item -> fields['name']." ".$strAttributes."', ".$strDate.")")or die($db->ErrorMsg());
    
	    message('success', YOU_SEND." <b><a href=view.php?view=".$_POST['pid'].">".$strPlayerName."</a></b>, ID<b> ".$_POST['pid'].'</b> '.$_POST['amount']." ".T_AMOUNT." ".$item -> fields['name']." ".$strAttributes);
	  }
      }

    /**
     * Donation of pet to other player
     */
    elseif($_GET['action'] == 'pets')
      {
	if ($_POST['player'] == 0)
	  {
	    checkvalue($_POST['pid']);
	  }
	else
	  {
	    checkvalue($_POST['player']);
	    $_POST['pid'] = $_POST['player'];
	  }
	checkvalue($_POST['item']);
	if (strlen($_POST['title']) > 0)
	  {
	    $strTitle = htmlspecialchars($_POST['title'], ENT_QUOTES);
	    $strTitle = ", tytułem: <b>".substr($strTitle, 0, 50)."</b>";
	  }
	else
	  {
	    $strTitle = '';
	  }
	$blnValid = TRUE;
	$objPet = $db->Execute("SELECT `id`, `corename`, `name` FROM `core` WHERE `owner`=".$player->id." AND `id`=".$_POST['item']) or die($db->ErrorMsg());
	if (!$objPet->fields['id'])
	  {
	    message('error', 'Nie ma takiego chowańca.');
	    $blnValid = FALSE;
	  }
	if ($_POST['pid'] == $player -> id) 
	  {
	    message('error', 'Nie możesz przekazać chowańca samemu sobie.');
	    $blnValid = FALSE;
	  }
	$objDonated = $db -> Execute("SELECT `id`, `user`, `tribe` FROM `players` WHERE `id`=".$_POST['pid']);
	if (empty ($objDonated -> fields['id'])) 
	  {
	    message('error', 'Nie ma takiego gracza.');
	    $blnValid = FALSE;
	  }
	if ($objPet->fields['corename'] != '')
	  {
	    $strCorename = $objPet->fields['corename'].' ('.$objPet->fields['name'].')';
	  }
	else
	  {
	    $strCorename = $objPet->fields['name'];
	  }
	$objPet->Close();
	$strPlayerName = $arrTags[$objDonated->fields['tribe']][0].' '.$objDonated -> fields['user'].' '.$arrTags[$objDonated->fields['tribe']][1];
	$objDonated -> Close();
	if ($blnValid)
	  {
	    $db->Execute("UPDATE `core` SET `owner`=".$_POST['pid']." WHERE `id`=".$_POST['item']);
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES('".$_POST['pid']."','Gracz <b><a href=view.php?view=".$player->id.">".$arrTags[$player->tribe][0].' '.$player ->user.' '.$arrTags[$player->tribe][1]."</a></b> ID:<b>".$player->id."</b>, przekazał tobie chowańca ".$strCorename.$strTitle.".', ".$strDate.", 'R')");
	    $db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES('".$_POST['pid']."','Gracz <b><a href=view.php?view=".$player->id.">".$arrTags[$player->tribe][0].' '.$player ->user.' '.$arrTags[$player->tribe][1]."</a></b> ID:<b>".$player->id."</b>, przekazał tobie chowańca ".$strCorename.".', ".$strDate.")");
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES('".$player -> id."', 'Przekazałeś chowańca <b>".$strCorename."</b> graczowi <b><a href=\"view.php?view=".$_POST['pid']."\">".$strPlayerName."</a></b> ID:<b>".$_POST['pid']."</b>".$strTitle.".', ".$strDate.", 'R')") or die($db->ErrorMsg());
	    message('success', "Przekazałeś chowańca <b>".$strCorename."</b> graczowi <b><a href=\"view.php?view=".$_POST['pid']."\">".$strPlayerName."</a></b> ID:<b>".$_POST['pid']."</b>".$strTitle.".");
	  }
      }

    /**
     * Bank robbery
     */
    elseif ($_GET['action'] == 'steal' && $player->clas == 'Złodziej') 
      {
	$blnValid = TRUE;
	if (!isset($_POST['tp']))
	  {
	    message('error', "Podaj ile punktów kradzieży chcesz przeznaczyć na próbę.");
	    $blnValid = FALSE;
	  }
	else
	  {
	    checkvalue($_POST['tp']);
	    if ($_POST['tp'] > 100)
	      {
		message('error', "Nie możesz przeznaczyć aż tyle energii (maksymalnie 100).");
		$blnValid = FALSE;
	      }
	    if ($_POST['tp'] < 10)
	      {
		message('error', 'Musisz przeznaczyć co najmniej 10 energii.');
		$blnValid = FALSE;
	      }
	    if ($_POST['tp'] > $player->energy)
	      {
		message('error', "Nie masz tyle energii!");
		$blnValid = FALSE;
	      }
	  }
	if ($player->hp <= 0)
	  {
	    message('error', "Nie możesz okradać banku kiedy jesteś martwy.");
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $intMax = (350 - $_POST['tp']);
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
		$player->curskills(array('thievery'));
		
		$intStats = ($player->stats['agility'][2] + $player->stats['inteli'][2] + $player->skills['thievery'][1] + $player->stats['speed'][2] + $player->checkbonus('steal'));
		$player->clearbless(array('agility', 'inteli', 'speed'));
		/**
		 * Add bonus from tools
		 */
		if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
		  {
		    $intStats += (($player->equip[12][2] / 100) * $intStats);
		  }
	    
		$chance = $intStats - $roll;
	      }
	    if ($chance < 1) 
	      {
		$cost = 1000 * $player->skills['thievery'][1];
		$player->checkexp(array('agility' => 1,
					'inteli' => 1,
					'speed' => 1), $player->id, 'stats');
		$player->checkexp(array('thievery' => 1), $player->id, 'skills');
		$db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `energy`=`energy`-".$_POST['tp']." WHERE `id`=".$player -> id);
		$player->energy -= $_POST['tp'];
		$strDate = $db -> DBDate($newdate);
		$db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.", '".VERDICT."', 7, ".$cost.", ".$strDate.")") or error (E_DB4);
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".L_REASON.": ".$cost.".','".$newdate."', 'T')");
		if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
		  {
		    $db->Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[12][0]);
		  }
		$objTool = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U' AND `name` LIKE 'Wytrychy%'");
		if ($objTool->fields['id'])
		  {
		    $intRoll = rand(1, 100);
		    if ($intRoll < 50)
		      {
			$db->Execute("DELETE FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
		      }
		  }
		$objTool->Close();
		message('error', C_CACHED);
	      }
	    else 
	      { 
		$gain = $roll * 1000;
		$expgain = $roll * 20;
		if ($chance == 1000000)
		  {
		    $gain = 2 * $gain;
		    $expgain = 2 * $expgain;
		  }
		$player->checkexp(array('speed' => ($expgain / 4),
					'inteli' => ($expgain / 4),
					'agility' => ($expgain / 4)), $player->id, 'stats');
		$player->checkexp(array('thievery' => ($expgain / 4)), $player->id, 'skills');
		if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
		  {
		    $player->equip[12][6] --;
		    if ($player->equip[12][6] <= 0)
		      {
			$db->Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[12][0]);
		      }
		    else
		      {
			$db->Execute("UPDATE `equipment` SET `wt`=`wt`-1 WHERE `id`=".$player->equip[12][0]);
		      }
		  }
		$db->Execute("UPDATE `players` SET `credits`=`credits`+".$gain.", `energy`=`energy`-".$_POST['tp']." WHERE `id`=".$player->id);
		$player->credits += $gain;
		$player->energy -= $_POST['tp'];
		message('success', C_SUCCES.$gain.C_SUCCES2.".");
	      }
	  }
      }

    /**
     * Buy safe box for astral components
     */
    elseif ($_GET['action'] == 'safe')
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
	    $blnValid = TRUE;
	    if ($player -> credits < $arrSafeneed[$intKey][0])
	      {
		message('error', NO_MONEY);
		$blnValid = FALSE;
	      }
	    if ($player -> platinum < $arrSafeneed[$intKey][1])
	      {
		message('error', NO_MITHRIL);
		$blnValid = FALSE;
	      }
	    $objMinerals = $db -> Execute("SELECT `crystal`, `adamantium` FROM `minerals` WHERE `owner`=".$player -> id);
	    if ($objMinerals -> fields['adamantium'] < $arrSafeneed[$intKey][2])
	      {
		message('error', NO_MINERAL." ".ADAMANTIUM."!");
		$blnValid = FALSE;
	      }
	    if ($objMinerals -> fields['crystal'] < $arrSafeneed[$intKey][3])
	      {
		message('error', NO_MINERAL." ".CRYSTAL."!");
		$blnValid = FALSE;
	      }
	    if ($blnValid)
	      {
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
		message('success', YOU_UPGRADE);
	      }
	  }
        else
	  {
	    message('error', SAFE_ENOUGH);
	  }
	$objSafebox -> Close();
      }

    /**
     * Astral vault
     */
    elseif ($_GET['action'] == 'astral')
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
				    "Taplayer" => "graczowi",
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

	if (isset($_GET['step']))
	  {
	    /**
	     * Merge plans, maps, recipes
	     */
	    if (!in_array($_GET['step'], array('piece', 'component', 'plan', 'all')))
	      {
		mergeplans('V', $player -> id);
		message('success', YOU_MERGE);
	      }

	    if (in_array($_GET['step'], array('piece', 'component', 'plan')))
	      {
		$_POST['name'] = intval($_POST['name']);
		if ($_POST['name'] < 0)
		  {
		    error(ERROR);
		  }
		if ($_POST['player'] == 0)
		  {
		    checkvalue($_POST['pid']);
		  }
		else
		  {
		    checkvalue($_POST['player']);
		    $_POST['pid'] = $_POST['player'];
		  }
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
	    if ($_GET['step'] == 'plan')
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
		$strMessage = YOU_GIVE." wszystkie części ".$strCompname." ".D_PLAYER." ID: ".$_POST['pid'].".";
		$strMessage2 = YOU_GET." wszystkie części ".$strCompname." ".D_PLAYER2.$player -> id.".";
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'N')");
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."', 'N')");
		$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."')");
		message("success", $strMessage);
	      }

	    /**
	     * Give everything to selected player
	     */
	    if ($_GET['step'] == 'all')
	      {
		if ($_POST['player'] == 0)
		  {
		    checkvalue($_POST['pid']);
		  }
		else
		  {
		    checkvalue($_POST['player']);
		    $_POST['pid'] = $_POST['player'];
		  }
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
		$strMessage = YOU_GIVE." wszystkie części astralne ".D_PLAYER." ID: ".$_POST['pid'].".";
		$strMessage2 = YOU_GET." wszystkie części astralne ".D_PLAYER2.$player -> id.".";
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'N')");
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."', 'N')");
		$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."')");
		message("success", $strMessage);
	      }

	    /**
	     * Give item to player
	     */
	    if ($_GET['step'] == 'piece' || $_GET['step'] == 'component')
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
		$strMessage = YOU_GIVE.$strType.$strCompname.M_AMOUNT2.$_POST['amount']." ".D_PLAYER." ID: ".$_POST['pid'].".";
		$strMessage2 = YOU_GET.$strType.$strCompname.M_AMOUNT2.$_POST['amount'].D_PLAYER2.$player -> id.".";
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'N')");
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."', 'N')");
		$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.", '".$strMessage."','".$newdate."', 'N')");
		message('success', $strMessage);
	      }
	  }
      }
  }
else
  {
    $_GET['action'] = '';
  }

//Contacts
$objContacts = $db->Execute("SELECT `contacts`.`pid`, `players`.`user`, `players`.`tribe` FROM `contacts` JOIN `players` ON `contacts`.`pid`=`players`.`id` WHERE `owner`=".$player->id." ORDER BY `order` ASC");
    $arrContacts = array(0 => "ID (numer)");
    while (!$objContacts->EOF)
      {
	$arrContacts[$objContacts->fields['pid']] = $arrTags[$objContacts->fields['tribe']][0]." ".$objContacts->fields['user']." ".$arrTags[$objContacts->fields['tribe']][1].' (ID: '.$objContacts->fields['pid'].')';;
	$objContacts->MoveNext();
      }
    $objContacts->Close();

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
			"Contacts" => $arrContacts,
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
    $item = $db -> Execute("SELECT * FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='U' AND `type`!='Q'");
    if ($item -> fields['id']) 
    {
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
	    if ($item->fields['maxwt'] > 1 && $item->fields['type'] != 'R')
	      {
		$strDur = ' ('.$item->fields['wt'].'/'.$item->fields['maxwt'].' wt)';
	      }
	    $strAgi = '';
	    $strSpeed = '';
	    if ($item->fields['zr'] != 0)
	      {
		$strAgi = " (".($item->fields['zr'] * -1)." ".I_AGI.")";
	      }
	    if ($item->fields['szyb'] != 0)
	      {
		$strSpeed = " (".$item->fields['szyb']." szyb)";
	      }
	    if ($item->fields['type'] == 'P')
	      {
		$item->fields['name'] = 'Plan: '.$item->fields['name'].' poziom: '.$item->fields['minlev'];
	      }
	    if ($item->fields['power'] != 0)
	      {
		$strPower = " (+".$item->fields['power'].")";
	      }
	    else
	      {
		$strPower = '';
	      }
	    $arrItems[$item->fields['id']] = $item->fields['name'].$strPower.$strAgi.$strSpeed.$strDur." (".I_AMOUNT2.": ".$strAmount.")";
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
     * List of pets
     */
    $objPets = $db->Execute("SELECT `id`, `name`, `power`, `defense`, `gender`, `corename` FROM `core` WHERE `owner`=".$player->id);
    $arrPets = array();
    while (!$objPets->EOF)
      {
	if ($objPets->fields['gender'] == 'F')
	  {
	    $strGender = 'Samica';
	  }
	else
	  {
	    $strGender = 'Samiec';
	  }
	if ($objPets->fields['corename'] != '')
	  {
	    $strName = $objPets->fields['corename'].' ('.$objPets->fields['name'].')';
	  }
	else
	  {
	    $strName = $objPets->fields['name'];
	  }
	$arrPets[$objPets->fields['id']] = $strName.' ('.$strGender.') Siła:'.$objPets->fields['power'].' Obrona:'.$objPets->fields['defense'];
	$objPets->MoveNext();
      }
    $objPets->Close();
    if (count($arrPets))
      {
	$intCores = 1;
      }
    else
      {
	$intCores = 0;
      }
    $smarty->assign(array("Pets1" => $intCores,
			  "Coptions" => $arrPets));

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
    if ($player->clas == 'Złodziej' && $player->energy > 0) 
      {
	$smarty->assign(array("Crime" => "Y",
			      "Asteal" => "Okradnij",
			      "Tcrime" => "bank przeznaczając na to",
			      "Ttp" => "energii (maksymalnie 100 energii)."));
      }
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
