<?php
/**
 *   File functions:
 *   Manage clans
 *
 *   @name                 : tribeadmin.php                            
 *   @copyright            : (C) 2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 28.01.2013
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

$title = "Klany";
require_once('includes/head.php');

if ($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error('Zapomnij o tym.');
}
if ($player->tribe == 0)
  {
    error('Nie masz klanu aby móc nim zarządzać.');
  }

$mytribe = $db -> Execute("SELECT * FROM tribes WHERE id=".$player -> tribe);
$perm = $db -> Execute("SELECT * FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']." AND player=".$player->id);
$test = array($perm -> fields['messages'],$perm -> fields['wait'],$perm -> fields['kick'],$perm -> fields['army'],$perm -> fields['attack'],$perm -> fields['loan'],$perm -> fields['armory'],$perm -> fields['warehouse'],$perm -> fields['bank'],$perm -> fields['herbs'], $perm -> fields['mail'], $perm -> fields['ranks']);

if (!in_array(1, $test) && $player->id != $mytribe->fields['owner'])
  {
    error('Nie masz prawa przebywać tutaj. (<a href="tribes.php">Wróć</a>)');
  }

/**
* Get the localization for game
*/
require_once("languages/".$lang."/tribeadmin.php");

if (!isset($_GET['step2']))
  {
    $arrLinks = array('permissions' => "Ustawić uprawnienia członków klanu",
		      'rank' => "Ustawić rangi członkom klanu",
		      'mail' => "Wyślij pocztę do członków klanu",
		      'messages' => "Edytować opis klanu, wiadomość dla członków oraz herb klanu i stronę klanu",
		      'nowy' => "Sprawdź listę oczekujących na dołączenie do klanu",
		      'kick' => "Wyrzucić Członka",
		      'wojsko' => "Dokupić żołnierzy lub fortyfikacji do klanu",
		      'walka' => "Zaatakować inny klan",
		      'loan' => "Pożycz pieniądze członkowi",
		      'te' => "Dodatki klanu",
		      'asks' => 'Sprawdź prośby o przedmioty z klanu',
		      'traps' => 'Dokupić pułapki lub strażników do klanu');
    if ($mytribe->fields['level'] < 5)
      {
	unset($arrLinks['wojsko'], $arrLinks['walka']);
      }
    if ($mytribe->fields['level'] < 4)
      {
	$arrLinks['upgrade'] = 'Rozbuduj klan';
      }
    $smarty -> assign(array("Panelinfo" => "Witaj w panelu przywódcy klanu. Co chcesz zrobić?",
			    "Links" => $arrLinks));
    $_GET['step2'] = '';
  }
else
  {
    /**
     * Check asks for items from tribe
     */
    if ($_GET['step2'] == 'asks')
      {
	if($player->id != $mytribe -> fields['owner'] && (!$perm -> fields['armory'] || !$perm->fields['herbs'] || !$perm->fields['bank'] || !$perm->fields['warehouse'])) 
	  {
	    error('Nie masz prawa tutaj przebywać.');
	  }
	$objAsks = $db->SelectLimit("SELECT * FROM `tribe_reserv` WHERE `tribe`=".$player->tribe, 10);
	$arrPlayers = array();
	$arrItems = array();
	$arrIds = array();
	$arrAmounts = array();
	$arrName = array("Illani", "Illanias", "Nutari", "Dynallca", "Nasiona Illani", "Nasiona Illanias", "Nasiona Nutari", "Nasiona Dynallca");
	$arrName2 = array("rudy miedzi", "rudy cynku", "rudy cyny", "rudy żelaza", "sztabek miedzi", "sztabek brązu", "sztabek mosiądzu", "sztabek żelaza", "sztabek stali", "brył węgla", "brył adamantium", "kawałków meteorytu", "kryształów", "drewna sosnowego", "drewna z leszczyny", "drewna cisowego", "drewna z wiązu", 'złoto', 'mithril');
	while (!$objAsks->EOF)
	  {
	    switch ($objAsks->fields['type'])
	      {
		//Items
	      case 'A':
		$objItem = $db->Execute("SELECT `name` FROM `tribe_zbroj` WHERE `id`=".$objAsks->fields['iid']);
		$arrItems[] = $objItem->fields['name'];
		$objItem->Close();
		break;
		//Potions
	      case 'P':
		$objItem = $db->Execute("SELECT `name` FROM `tribe_mag` WHERE `id`=".$objAsks->fields['iid']);
		$arrItems[] = $objItem->fields['name'];
		$objItem->Close();
		break;
		//Herbs
	      case 'H':
		$arrItems[] = $arrName[$objAsks->fields['iid']];
		break;
		//Minerals
	      case 'M':
		$arrItems[] = ucfirst($arrName2[$objAsks->fields['iid']]);
		break;
	      default:
		break;
	      }
	    $arrAmounts[] = $objAsks->fields['amount'];
	    $objPlname = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$objAsks->fields['pid']);
	    $arrPlayers[] = $objPlname->fields['user'].' ID: '.$objAsks->fields['pid'];
	    $objPlname->Close();
	    $arrIds[] = $objAsks->fields['id'];
	    $objAsks->MoveNext();
	  }
	$smarty->assign(array("Taccept" => 'Akceptuj',
			      "Trefuse" => 'Odrzuć',
			      "Tplayer" => 'Gracz',
			      "Titem" => 'Przedmiot',
			      "Tamount" => 'Sztuk',
			      "Anext" => 'Rozpatrz',
			      'Cplayers' => $arrPlayers,
			      'Cids' => $arrIds,
			      'Camounts' => $arrAmounts,
			      'Citems' => $arrItems,
			      'Asksinfo' => 'Oto ostatnie 10 próśb o przedmioty z klanu.'));
	if (isset($_GET['step3']))
	  {
	    $arrRejected = array();
	    $objAsks->MoveFirst();
	    $arrSqlname = array('illani', 'illanias', 'nutari', 'dynallca', 'illani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	    $arrSqlname2 = array('copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm', 'credits', 'platinum');
	    while(!$objAsks->EOF)
	      {
		if ($_POST[$objAsks->fields['id']] == 'Odrzuć')
		  {
		    $arrRejected[] = $objAsks->fields['pid'];
		  }
		else
		  {
		    //Items or potions
		    if ($objAsks->fields['type'] == 'A' || $objAsks->fields['type'] == 'P')
		      {
			$_GET['step3'] = 'add';
			$_GET['daj'] = $objAsks->fields['iid'];
			$_POST['did'] = $objAsks->fields['pid'];
			$_POST['amount'] = $objAsks->fields['amount'];
			$intReserved = $objAsks->fields['amount'];
			if ($objAsks->fields['type'] == 'A')
			  {
			    require('tribearmor.php');
			  }
			else
			  {
			    require('tribeware.php');
			  }
		      }
		    //Herbs or minerals
		    elseif($objAsks->fields['type'] == 'H' || $objAsks->fields['type'] == 'M')
		      {
			$_POST['did'] = $objAsks->fields['pid'];
			$_POST['ilosc'] = $objAsks->fields['amount'];
			$_GET['step4'] = 'add';
			$intReserved = $objAsks->fields['amount'];
			if ($objAsks->fields['type'] == 'H')
			  {
			    $_GET['daj'] = $arrSqlname[$objAsks->fields['iid']];
			    require('tribeherbs.php');
			  }
			else
			  {
			    $_GET['daj'] = $arrSqlname2[$objAsks->fields['iid']];
			    require('tribeminerals.php');
			  }
		      }
		  }
		switch ($objAsks->fields['type'])
		  {
		    //Items
		  case 'A':
		    $db->Execute("UPDATE `tribe_zbroj` SET `reserved`=`reserved`-".$objAsks->fields['amount']." WHERE `id`=".$objAsks->fields['iid']);
		    break;
		    //Potions
		  case 'P':
		    $db->Execute("UPDATE `tribe_mag` SET `reserved`=`reserved`-".$objAsks->fields['amount']." WHERE `id`=".$objAsks->fields['iid']);
		    break;
		    //Herbs
		  case 'H':
		    $db->Execute("UPDATE `tribe_herbs` SET `r".$arrSqlname[$objAsks->fields['iid']]."`=`r".$arrSqlname[$objAsks->fields['iid']]."`-".$objAsks->fields['amount']." WHERE `id`=".$player->tribe);
		    break;
		    //Minerals
		  case 'M':
		    if (!in_array($arrSqlname2[$objAsks->fields['iid']], array('credits', 'platinum')))
		      {
			$db->Execute("UPDATE `tribe_minerals` SET `r".$arrSqlname2[$objAsks->fields['iid']]."`=`r".$arrSqlname2[$objAsks->fields['iid']]."`-".$objAsks->fields['amount']." WHERE `id`=".$player->tribe);
		      }
		    else
		      {
			$db->Execute("UPDATE `tribes` SET `r".$arrSqlname2[$objAsks->fields['iid']]."`=`r".$arrSqlname2[$objAsks->fields['iid']]."`-".$objAsks->fields['amount']." WHERE `id`=".$player->tribe);
		      }
		    break;
		  default:
		    break;
		  }
		$objAsks->MoveNext();
	      }
	    if (count($arrRejected))
	      {
		$strMessage = 'Twoja prośba o przedmiot z klanu została odrzucona.';
		$strSQL = "INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES";
		foreach ($arrRejected as $intRejected)
		  {
		    $strSQL .= "(".$intRejected.", '".$strMessage."','".$newdate."', 'C'),";
		  }
		$strSQL = trim($strSQL, ',');
		$db -> Execute($strSQL);
	      }
	    $db->Execute("DELETE FROM `tribe_reserv` WHERE `id` IN (".implode(',', $arrIds).")");
	    message('success', 'Rozpatrzyłeś prośby o przedmioty z klanu.');
	  }
	$objAsks->Close();
      }
    /**
     * Set ranks for members
     */
    elseif ($_GET['step2'] == 'rank')
      {
	if($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['ranks']) 
	  {
	    error (NO_PERM2);
	  }
	if (!isset ($_GET['step3'])) 
	  {
	    $smarty -> assign(array("Ranksinfo" => RANKS_INFO,
				    "Aaddranks" => A_ADD_RANKS));
	    $test = $db -> Execute("SELECT id FROM tribe_rank WHERE tribe_id=".$mytribe -> fields['id']);
	    if ($test -> fields['id']) 
	      {
		$smarty -> assign ("Menu", "<li><a href=\"tribeadmin.php?step2=rank&amp;step3=get\">".GIVE_RANK."</a></li>");
	      } 
	    else 
	      {
		$smarty -> assign("Menu", '');
	      }
	    $test -> Close();
	  }
	if (isset ($_GET['step3']) && $_GET['step3'] == 'set') 
	  {
	    $ranks = $db -> Execute("SELECT id, rank1, rank2, rank3, rank4, rank5, rank6, rank7, rank8, rank9, rank10 FROM tribe_rank WHERE tribe_id=".$mytribe -> fields['id']);
	    if (!$ranks -> fields['id']) 
	      {
		$smarty -> assign(array("Empty" => 1,
					"Noranks2" => NO_RANKS2,
					"Amake" => A_MAKE,
					"Rank" => RANK));
	      } 
	    else 
	      {
		$arrRanks = array($ranks -> fields['rank1'], $ranks -> fields['rank2'], $ranks -> fields['rank3'], $ranks -> fields['rank4'], $ranks -> fields['rank5'], $ranks -> fields['rank6'], $ranks -> fields['rank7'], $ranks -> fields['rank8'], $ranks -> fields['rank9'], $ranks -> fields['rank10']);
		$smarty -> assign(array("Ranks" => $arrRanks,
					"Empty" => 0,
					"Editranks" => EDIT_RANKS,
					"Asave" => A_SAVE,
					"Rank" => RANK));
	      }
	    if (isset ($_GET['step4']) && $_GET['step4'] == 'add') 
	      {
		$arrRank = array();
		for ($i=1;$i<11;$i++) 
		  {
		    $number = "rank".$i;
		    $j = $i - 1;
		    $_POST[$number] = strip_tags($_POST[$number]);
		    $arrRank[$j] = $db -> qstr($_POST[$number], get_magic_quotes_gpc());
		  }
		$db -> Execute("INSERT INTO tribe_rank (tribe_id, rank1, rank2, rank3, rank4, rank5, rank6, rank7, rank8, rank9, rank10) VALUES(".$mytribe -> fields['id'].", ".$arrRank[0].", ".$arrRank[1].", ".$arrRank[2].", ".$arrRank[3].", ".$arrRank[4].", ".$arrRank[5].", ".$arrRank[6].", ".$arrRank[7].", ".$arrRank[8].", ".$arrRank[9].")");
		message("success", RANK_CREATED.". <a href=\"tribeadmin.php?step2=rank\">".BACK_TO."</a><br />");
	      }
	    if (isset ($_GET['step4']) && $_GET['step4'] == 'edit') 
	      {
		$arrRank = array();
		for ($i=1;$i<11;$i++) 
		  {
		    $number = "rank".$i;
		    $j = $i - 1;
		    $_POST[$number] = strip_tags($_POST[$number]);
		    $arrRank[$j] = $db -> qstr($_POST[$number], get_magic_quotes_gpc());
		  }
		$db -> Execute("UPDATE tribe_rank SET rank1=".$arrRank[0].", rank2=".$arrRank[1].", rank3=".$arrRank[2].", rank4=".$arrRank[3].", rank5=".$arrRank[4].", rank6=".$arrRank[5].", rank7=".$arrRank[6].", rank8=".$arrRank[7].", rank9=".$arrRank[8].", rank10=".$arrRank[9]." WHERE tribe_id=".$mytribe -> fields['id']);
		message("success", RANK_CHANGED.". <a href=\"tribeadmin.php?step2=rank\">".BACK_TO."</a><br />");
	      }
	  }
	if (isset ($_GET['step3']) && $_GET['step3'] == 'get') 
	  {
	    $test = $db -> Execute("SELECT id FROM tribe_rank WHERE tribe_id=".$mytribe -> fields['id']);
	    if (!$test -> fields['id']) 
	      {
		error (NO_RANKS);
	      }
	    $test -> Close();
	    $rank = $db -> Execute("SELECT rank1, rank2, rank3, rank4, rank5, rank6, rank7, rank8, rank9, rank10 FROM tribe_rank WHERE tribe_id=".$mytribe -> fields['id']);
	    $name = array('rank1','rank2','rank3','rank4','rank5','rank6','rank7','rank8','rank9','rank10');
	    $arrname = array();
	    foreach ($name as $strName)
	      {
		if ($rank->fields[$strName])
		  {
		    $arrname[] = $rank->fields[$strName];
		  }
	      }
	    $rank -> Close();
	    $objMembers = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$mytribe -> fields['id']);
	    $arrMembers = array();
	    while (!$objMembers -> EOF)
	      {
		$arrMembers[$objMembers->fields['id']] = $objMembers -> fields['user'].' ID:'.$objMembers->fields['id'];
		$objMembers -> MoveNext();
	      }
	    $objMembers -> Close();
	    $smarty -> assign (array("Rank" => $arrname,
				     "Members" => $arrMembers,
				     "Aset" => A_SET,
				     "Setrank" => SET_RANK,
				     "Rankplayer" => RANK_PLAYER));
	    if (isset ($_GET['step4']) && $_GET['step4'] == 'add') 
	      {
		$_POST['rank'] = strip_tags($_POST['rank']);
		checkvalue($_POST['rid']);
		$test = $db -> Execute("SELECT tribe FROM players WHERE id=".$_POST['rid']);
		if ($test -> fields['tribe'] != $mytribe -> fields['id']) 
		  {
		    error (NOT_IN_CLAN);
		  }
		$test -> Close();
		$strRank = $db -> qstr($_POST['rank'], get_magic_quotes_gpc());
		$db -> Execute("UPDATE players SET tribe_rank=".$strRank." WHERE id=".$_POST['rid']);
		message("success", YOU_GIVE.$_POST['rid'].T_RANK.$_POST['rank']."<br />");
	      }
	  }
      }
    /**
     * Add members permission in clan - only clan leader
     */
    elseif ($_GET['step2'] == 'permissions')
      {
	if ($player -> id != $mytribe -> fields['owner']) 
	  {
	    error (ONLY_LEADER);
	  }
	if (!isset ($_GET['step3'])) 
	  {
	    $objMembers = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$mytribe -> fields['id']." AND `id`!=".$mytribe -> fields['owner']);
	    $arrMembers = array();
	    while (!$objMembers -> EOF)
	      {
		$arrMembers[$objMembers->fields['id']] = $objMembers -> fields['user'].' ID:'.$objMembers->fields['id'];
		$objMembers -> MoveNext();
	      }
	    $objMembers -> Close();
	    if (!isset($_GET['next']))
	      {
		$_GET['next'] = '';
	      }
	    $arrPerms = array("Może edytować opisy klanu", 
			      "Może dołączać nowych członków", 
			      "Może wyrzucać członków z klanu", 
			      "Może kupować żołnierzy oraz fortyfikacje", 
			      "Może wykonywać ataki na inny klan", 
			      "Może pożyczać pieniądze członkom klanu", 
			      "Może dawać przedmioty ze zbrojowni", 
			      "Może dawać przedmioty z magazynu", 
			      "Może dawać minerały ze skarbca", 
			      "Może dawać zioła z zielnika", 
			      "Może kasować posty na forum", 
			      "Może ustalać rangi członkom klanu", 
			      "Może wysyłać listy do członków klanu", 
			      "Nie może oglądać informacji o klanie", 
			      "Może łączyć astralne plany, dawać je członkom klanu");
	    if ($mytribe->fields['level'] < 5)
	      {
		unset($arrPerms[3], $arrPerms[4]);
	      }
	    if ($mytribe->fields['level'] < 3)
	      {
		unset($arrPerms[14], $arrPerms[8], $arrPerms[9]);
	      }
	    if ($mytribe->fields['level'] == 1)
	      {
		unset($arrPerms[6], $arrPerms[7]);
	      }
	    $smarty -> assign(array("Perminfo" => PERM_INFO,
				    "Tperms" => $arrPerms,
				    "Asave" => A_SAVE,
				    "Anext" => A_NEXT,
				    "Next" => $_GET['next'],
				    "Members" => $arrMembers,
				    "Yes" => YES,
				    "No" => NO));
	    if (isset($_GET['next']) && $_GET['next'] == 'add')
	      {
		if (!isset($_POST['memid']))
		  {
		    error('Zapomnij o tym.');
		  }
		checkvalue($_POST['memid']);
		$objTest = $db -> Execute("SELECT * FROM tribe_perm WHERE player=".$_POST['memid']) or die ($db->ErrorMsg());
		$arrTest = array($objTest -> fields['messages'],
				 $objTest -> fields['wait'],
				 $objTest -> fields['kick'],
				 $objTest -> fields['army'],
				 $objTest -> fields['attack'],
				 $objTest -> fields['loan'],
				 $objTest -> fields['armory'],
				 $objTest -> fields['warehouse'],
				 $objTest -> fields['bank'],
				 $objTest -> fields['herbs'],
				 $objTest -> fields['forum'],
				 $objTest -> fields['ranks'],
				 $objTest -> fields['mail'],
				 $objTest -> fields['info'],
				 $objTest -> fields['astralvault']);
		if ($mytribe->fields['level'] < 5)
		  {
		    unset($arrTest[3], $arrTest[4]);
		  }
		if ($mytribe->fields['level'] < 3)
		  {
		    unset($arrTest[14], $arrTest[8], $arrTest[9]);
		  }
		if ($mytribe->fields['level'] == 1)
		  {
		    unset($arrTest[6], $arrTest[7]);
		  }
		$objTest -> Close();
		$objName = $db -> Execute("SELECT user FROM players WHERE id=".$_POST['memid']);
		$arrSelected = array();
		foreach ($arrTest as $intTest)
		  {
		    if ($intTest == 1)
		      {
			$arrSelected[] = ' selected="selected"';
		      }
		    else
		      {
			$arrSelected[] = '';
		      }
		  }
		$arrNames = array('messages', 'wait', 'kick', 'army', 'attack', 'loan', 'armory', 'warehouse', 'bank', 'herbs', 'forum', 'ranks', 'mail', 'info', 'astralvault');
		if ($mytribe->fields['level'] < 5)
		  {
		    unset($arrNames[3], $arrNames[4]);
		  }
		if ($mytribe->fields['level'] < 3)
		  {
		    unset($arrNames[14], $arrNames[8], $arrNames[9]);
		  }
		if ($mytribe->fields['level'] == 1)
		  {
		    unset($arrNames[6], $arrNames[7]);
		  }
		$arrTmpnames = array();
		foreach ($arrNames as $strName)
		  {
		    $arrTmpnames[] = $strName;
		  }
		$arrNames = $arrTmpnames;
		$smarty -> assign(array("Memid2" => $_POST['memid'],
					"Tselected" => $arrSelected,
					"Tnames" => $arrNames,
					"Tname" => $objName -> fields['user'],
					"Tuser" => T_USER));
		$objName -> Close();
	      }
	  }
	if (isset ($_GET['step3'])) 
	  {
	    if ($mytribe->fields['level'] < 5)
	      {
		$_POST['army'] = 0;
		$_POST['attack'] = 0;
	      }
	    if ($mytribe->fields['level'] < 3)
	      {
		$_POST['astralvault'] = 0;
		$_POST['bank'] = 0;
		$_POST['herbs'] = 0;
	      }
	    if ($mytribe->fields['level'] == 1)
	      {
		$_POST['armory'] = 0;
		$_POST['warehouse'] = 0;
	      }
	    $test = array($_POST['messages'],
			  $_POST['wait'],
			  $_POST['kick'],
			  $_POST['army'],
			  $_POST['attack'],
			  $_POST['loan'],
			  $_POST['armory'],
			  $_POST['warehouse'],
			  $_POST['bank'],
			  $_POST['herbs'],
			  $_POST['forum'], 
			  $_POST['ranks'], 
			  $_POST['mail'], 
			  $_POST['info'],
			  $_POST['astralvault']);
	    checkvalue($_POST['memid']);
	    $ttribe = $db -> Execute("SELECT tribe FROM players WHERE id=".$_POST['memid']);
	    if ($ttribe -> fields['tribe'] != $mytribe -> fields['id']) 
	      {
		error (NOT_IN_CLAN." <a href=\"tribeadmin.php\">".BACK_TO."</a>");
	      }
	    for ($i=0; $i<15; $i++) 
	      {
		$test[$i] = intval($test[$i]);
		if ($test[$i] != 0 && $test[$i] != 1)
		  {
		    error(ERROR);
		  }
	      }
	  }
	if (isset ($_GET['step3']) && $_GET['step3'] == 'add') 
	  {
	    $objTest = $db -> Execute("SELECT id FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']." AND player=".$_POST['memid']);
	    if (!$objTest -> fields['id'])
	      {
		$db -> Execute("INSERT INTO tribe_perm (tribe, player, messages, wait, kick, army, attack, loan, armory, warehouse, bank, herbs, forum, ranks, mail, info, astralvault) VALUES(".$mytribe -> fields['id'].", ".$_POST['memid'].", ".$_POST['messages'].", ".$_POST['wait'].", ".$_POST['kick'].", ".$_POST['army'].", ".$_POST['attack'].", ".$_POST['loan'].", ".$_POST['armory'].", ".$_POST['warehouse'].", ".$_POST['bank'].", ".$_POST['herbs'].", ".$_POST['forum'].", ".$_POST['ranks'].", ".$_POST['mail'].", ".$_POST['info'].", ".$_POST['astralvault'].")") or die($db->ErrorMsg());
	      }
	    else
	      {
		$db -> Execute("UPDATE tribe_perm SET messages=".$_POST['messages'].", wait=".$_POST['wait'].", kick=".$_POST['kick'].", army=".$_POST['army'].", attack=".$_POST['attack'].", loan=".$_POST['loan'].", armory=".$_POST['armory'].", warehouse=".$_POST['warehouse'].", bank=".$_POST['bank'].", herbs=".$_POST['herbs'].", forum=".$_POST['forum'].", ranks=".$_POST['ranks'].", mail=".$_POST['mail'].", info=".$_POST['info'].", astralvault=".$_POST['astralvault']." WHERE id=".$objTest -> fields['id']);
	      }
	    $objTest -> Close();
	    message("success", YOU_SET." <a href=\"tribeadmin.php\">".BACK_TO."</a>");
	  }
      }
    /**
     * Send mail to all members
     */
    elseif ($_GET['step2'] == 'mail') 
      {
	if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['mail']) 
	  {
	    error (NO_PERM2);
	  }
	$smarty->assign(array("Abold" => "Pogrubienie",
			      "Aitalic" => "Kursywa",
			      "Aunderline" => "Podkreślenie",
			      "Aemote" => "Emocje/Czynność",
			      "Ocolors" => array("red" => "czerwony",
						 "green" => "zielony",
						 "white" => "biały",
						 "yellow" => "żółty",
						 "blue" => "niebieski",
						 "aqua" => "cyjan",
						 "fuchsia" => "fuksja",
						 "grey" => "szary",
						 "lime" => "limonka",
						 "maroon" => "wiśniowy",
						 "navy" => "granatowy",
						 "olive" => "oliwkowy",
						 "purple" => "purpurowy",
						 "silver" => "srebrny",
						 "teal" => "morski"),
			      "Acolor" => "Kolor",
			      "Acenter" => "Wycentrowanie",
			      "Aquote" => "Cytat"));
	if (isset($_GET['step3']) && $_GET['step3'] == 'send')
	  {
	    if (!isset($_POST['body']) || !isset($_POST['mtitle']))
	      {
		error(ERROR);
	      }
	    $strBody = strip_tags($_POST['body']);
	    $strTitle = strip_tags($_POST['mtitle']);
	    if (empty($strBody) || empty($strTitle))
	      {
		error(EMPTY_FIELDS);
	      }
	    require_once('includes/bbcode.php');
	    $strBody = bbcodetohtml($strBody);
	    $strTitle = bbcodetohtml($strTitle);
	    $strTitle = "[Klan] ".$strTitle;
	    $objOwner = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$mytribe -> fields['id']." AND `id`!=".$player -> id);
	    $strDate = $db -> DBDate($newdate);
	    $objTopic = $db->Execute("SELECT max(`topic`) FROM `mail`");
	    $intTopic = $objTopic->fields['max(`topic`)'] + 1;
	    $objTopic->Close();
	    while (!$objOwner -> EOF)
	      {
		$db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`, `date`, `topic`, `to`, `toname`) VALUES('".$player -> user."','".$player -> id."',".$objOwner -> fields['id'].",'".$strTitle."','".$strBody."', ".$strDate.", ".$intTopic.", ".$objOwner->fields['id'].", '".$objOwner->fields['user']."')") or die($db->ErrorMsg);
		$objOwner -> MoveNext();
	      }
	    $objOwner -> Close();
	    message("success", YOU_SEND);
	  }
	else
	  {
	    $smarty -> assign(array("Tbody" => T_BODY,
				    "Asend" => A_SEND,
				    "Ttitle" => T_TITLE));
	  }
      }
    /**
     * Buying traps and agents to clan
     */
    elseif ($_GET['step2'] == 'traps')
      {
	if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['army']) 
	  {
	    error ("Nie masz uprawnień aby tutaj przebywać.");
	  }
	$arrAmount = array(0, 5, 10, 20, 40, 50);
	$intMax = $arrAmount[$mytribe->fields['level']];
	//Buying traps and agents
	if (isset($_GET['action']) && $_GET['action'] == 'buy')
	  {
	    if (!isset($_POST['traps']) || !isset($_POST['agents']))
	      {
		error('Zapomnij o tym.');
	      }
	    $_POST['traps'] = intval($_POST['traps']);
	    $_POST['agents'] = intval($_POST['agents']);
	    if ($_POST['traps'] < 0 || $_POST['agents'] < 0)
	      {
		error('Zapomnij o tym.');
	      }
	    if ($_POST['traps'] == 0 && $_POST['agents'] == 0)
	      {
		error('Podaj co chcesz kupić.');
	      }
	    $intTraps = $_POST['traps'] + $mytribe->fields['traps'];
	    $intAgents = $_POST['agents'] + $mytribe->fields['agents'];
	    if ($intTraps > $intMax)
	      {
		error('Nie możesz dokupić tak wiele pułapek.');
	      }
	    if ($intAgents > $intMax)
	      {
		error('Nie możesz dokupić tak wielu strażników.');
	      }
	    $intCost = ($_POST['traps'] * 1000) + ($_POST['agents'] * 10000);
	    if ($intCost > $mytribe->fields['credits'])
	      {
		error('Klan nie posiada takiej ilości złota.');
	      }
	    $mytribe->fields['traps'] = $intTraps;
	    $mytribe->fields['agents'] = $intAgents;
	    $mytribe->fields['credits'] -= $intCost;
	    $db->Execute("UPDATE `tribes` SET `credits`=`credits`-".$intCost.", `traps`=".$intTraps.", `agents`=".$intAgents." WHERE `id`=".$mytribe->fields['id']);
	    $strMessage = 'Dokupiłeś do klanu ';
	    $strLog = $player->user.' dokupił do klanu ';
	    if ($_POST['traps'] > 0)
	      {
		$strMessage .= $_POST['traps'].' pułapek';
		$strLog .= $_POST['traps'].' pułapek';
	      }
	    if ($_POST['traps'] > 0 && $_POST['agents'] > 0)
	      {
		$strMessage .= ' oraz ';
		$strLog .= ' oraz ';
	      }
	    if ($_POST['agents'] > 0)
	      {
		$strMessage .= $_POST['agents'].' strażników';
		$strLog .= $_POST['agents'].' strażników';
	      }
	    $strMessage .= ' za '.$intCost.' sztuk złota.';
	    $strLog .= ' za '.$intCost.' sztuk złota';
	    message('success', $strMessage);
	    /**
	     * Send informations about buy traps and agents
	     */
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '".$strLog."', ".$strDate.", 'C')");
	    $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `army`=1");
	    while (!$objPerm -> EOF)
	      {
		$db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".$strLog."', ".$strDate.", 'C')");
		$objPerm -> MoveNext();
	      }
	    $objPerm -> Close();
	  }
	$smarty->assign(array("Trapsinfo" => 'Tutaj możesz dokupić pułapki bądź strażników, którzy będą chronić klan przed złodziejami. Pułapki to jednorazowy wydatek 1000 sztuk złota za jedną, jednak po rozbrojeniu przez złodzieja, trzeba je kupić ponownie. Każdy strażnik kosztuje 10000 sztuk złota i pobiera codziennie pensję w wysokości 1000 sztuk złota z tym, że strażnicy są już na stałe przy klanie. Im więcej klan posiada pułapek oraz strażników, tym trudniej złodziejom dostać się do niego. Obecnie klan posiada <b>'.$mytribe->fields['traps'].'</b> pułapek (maksymalnie '.$intMax.') oraz <b>'.$mytribe->fields['agents'].'</b> strażników (maksymalnie '.$intMax.').',
			      "Abuy" => 'Dokup',
			      "Ttraps" => 'pułapek oraz',
			      "Tagents" => 'strażników.'));
      }
    /**
     * Buy army and barricades to clan
     */
    elseif ($_GET['step2'] == 'wojsko') 
      {
	if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['army']) 
	  {
	    error (NO_PERM);
	  }
	if ($mytribe->fields['level'] < 5)
	  {
	    error('Rozbuduj klan do zamku, aby móc kupować wojsko.');
	  }
	$smarty -> assign(array("Armyinfo" => ARMY_INFO,
				"Howmanys" => HOW_MANY_S,
				"Howmanyf" => HOW_MANY_F,
				"Abuy" => A_BUY));
	if (isset ($_GET['action']) && $_GET['action'] == 'kup') 
	  {
	    if (!isset($_POST['zolnierze']) || !isset($_POST['forty']))
	      {
		error(ERROR);
	      }
	    $_POST['zolnierze'] = intval($_POST['zolnierze']);
	    $_POST['forty'] = intval($_POST['forty']);
	    if ($_POST['zolnierze'] < 0 || $_POST['forty'] < 0)
	      {
		error(ERROR);
	      }
	    if ($_POST["zolnierze"] == 0 && $_POST["forty"] == 0) 
	      {
		error (EMPTY_FIELDS);
	      }
	    $cenaz = ($_POST["zolnierze"] * 1000);
	    $cenaf = ($_POST["forty"] * 1000);
	    $suma = $cenaz + $cenaf;
	    if ($suma > $mytribe -> fields['credits']) 
	      {
		error (NO_MONEY);
	      }
	    $message = '';
	    $strLog = '';
	    if ($_POST["zolnierze"] > 0) 
	      {
		$db -> Execute("UPDATE `tribes` SET `zolnierze`=`zolnierze`+".$_POST['zolnierze']." WHERE `id`=".$mytribe -> fields['id']);
		$message =  $message.YOU_BUY.$_POST['zolnierze'].SOLDIERS.$cenaz.FOR_A;
		$strLog =  $strLog.SOMEONE_BUY.$_POST['zolnierze'].SOLDIERS.$cenaz.FOR_A;
	      }
	    if ($_POST["forty"] > 0) 
	      {
		$db -> Execute("UPDATE `tribes` SET `forty`=`forty`+".$_POST['forty']." WHERE `id`=".$mytribe -> fields['id']);
		$message = $message.YOU_BUY.$_POST['forty'].BARRICADES.$cenaf.FOR_A;
		$strLog = $strLog.SOMEONE_BUY.$_POST['forty'].BARRICADES.$cenaf.FOR_A;
	      }
	    $db -> Execute("UPDATE `tribes` SET `credits`=`credits`-".$suma." WHERE `id`=".$mytribe -> fields['id']);
	    $message = $message.ALL_COST.$suma.FOR_A;
	    $strLog = $strLog.ALL_COST2.$suma.FOR_A;
	    message("success", $message);
	    /**
	     * Send informations about buy army
	     */
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '".$strLog."', ".$strDate.", 'C')");
	    $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `army`=1");
	    while (!$objPerm -> EOF)
	      {
		$db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".$strLog."', ".$strDate.", 'C')");
		$objPerm -> MoveNext();
	      }
	    $objPerm -> Close();
	  }
      }
    /**
     * Waiting for join to clan
     */
    elseif ($_GET['step2'] == 'nowy') 
      {
	if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['wait']) 
	  {
	    error (NO_PERM2);
	  }
	$smarty -> assign("New", 0);
	if (!isset($_GET['odrzuc']) && !isset($_GET['dodaj'])) 
	  {
	    $smarty -> assign ("New", 1);
	    $czeka = $db -> Execute("SELECT * FROM tribe_oczek WHERE klan=".$mytribe -> fields['id']);
	    $arrlink = array();
	    $i = 0;
	    while (!$czeka -> EOF) 
	      {
		$arrlink[$i] = "<tr><td><a href=\"view.php?view=".$czeka -> fields['gracz']."\">".$czeka -> fields['gracz']."</a></td><td><a href=\"tribeadmin.php?step2=nowy&dodaj=".$czeka -> fields['id']."\">".YES."</a></td><td><a href=\"tribeadmin.php?step2=nowy&odrzuc=".$czeka -> fields['id']."\">".YES."</a></td></tr>";
		$czeka -> MoveNext();
		$i = $i + 1;
	      }
	    $czeka -> Close();
	    $smarty -> assign (array("Link" => $arrlink,
				     "Waitlist" => WAIT_LIST,
				     "Tid" => T_ID,
				     "Taccept" => T_ACCEPT,
				     "Tdrop" => T_DROP));
	  }
	if (isset($_GET['odrzuc'])) 
	  {
	    $del = $db -> Execute("SELECT * FROM tribe_oczek WHERE id=".$_GET['odrzuc']." AND klan=".$mytribe -> fields['id']);
	    if (!$del -> fields['id'])
	      {
		error(ERROR);
	      }
	    message("success", YOU_DROP.$del -> fields['gracz'].".");
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$del -> fields['gracz'].",'".L_TRIBE.'<b><a href="tribes.php?view=view&amp;id='.$mytribe -> fields['id'].'">'.$mytribe -> fields['name'].'</a></b>'.L_DROP."', ".$strDate.", 'C')");
	    $db -> Execute("DELETE FROM tribe_oczek WHERE id=".$del -> fields['id']);
	    $del -> Close();
	    $objPerm = $db -> Execute("SELECT player FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']." AND wait=1");
	    while (!$objPerm -> EOF)
	      {
		$db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".YOU_DROP.'<b>'.$del -> fields['gracz']."</b>', ".$strDate.", 'C')");
		$objPerm -> MoveNext();
	      }
	    $objPerm -> Close();
	  }
	if (isset($_GET['dodaj'])) 
	  {
	    if ($mytribe->fields['level'] < 4)
	      {
		$arrMax = array(1 => 5,
				2 => 10,
				3 => 20);
		$objMembers = $db->Execute("SELECT count(`id`) FROM `players` WHERE `tribe`=".$mytribe->fields['id']);
		if ($objMembers->fields['count(`id`)'] == $arrMax[$mytribe->fields['level']])
		  {
		    error('Nie masz miejsca w klanie na nowych członków. Najpierw trzeba rozbudować klan.');
		  }
	      }
	    $dod = $db -> Execute("SELECT * FROM tribe_oczek WHERE id=".$_GET['dodaj']);
	    message("success", YOU_ACCEPT.$dod -> fields['gracz'].".");
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$dod -> fields['gracz'].",'".L_TRIBE.'<b><a href="tribes.php?view=my">'.$mytribe -> fields['name'].'</a></b>'.L_ACCEPT."', ".$strDate.", 'C')");
	    $db -> Execute("UPDATE players SET tribe=".$dod -> fields['klan']." WHERE id=".$dod -> fields['gracz']);
	    $db -> Execute("DELETE FROM tribe_oczek WHERE id=".$dod -> fields['id']);
	    $dod -> Close();
	    $objPerm = $db -> Execute("SELECT player FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']." AND wait=1");
	    while (!$objPerm -> EOF)
	      {
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$objPerm -> fields['player'].", '".YOU_ACCEPT.'<b>'.$dod -> fields['gracz']."</b>', ".$strDate.", 'C')");
		$objPerm -> MoveNext();
	      }
	    $objPerm -> Close();
	  }
      }
    /**
     * Clan's fight
     */
    elseif ($_GET['step2'] == 'walka') 
      {
	if ($mytribe->fields['level'] < 5)
	  {
	    error('Rozbuduj klan do zamku, aby móc atakować inne klany.');
	  }
	require_once('includes/tribefight.php');
      }
    
    /**
     * Messages about clan, logo and www site
     */
    elseif ($_GET['step2'] == 'messages')
      {
	if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['messages']) 
	  {
	    error (NO_PERM2);
	  }
	require_once('includes/bbcode.php');
	$mytribe -> fields['public_msg'] = htmltobbcode($mytribe -> fields['public_msg']);
	$mytribe -> fields['private_msg'] = htmltobbcode($mytribe -> fields['private_msg']);
	$smarty -> assign(array("Pubmessage" => $mytribe -> fields['public_msg'], 
				"Privmessage" => $mytribe -> fields['private_msg'], 
				"WWW" => $mytribe -> fields['www'],
				"Clandesc" => CLAN_DESC,
				"Msgtomem" => MSG_TO_MEM,
				"Aset" => A_SET,
				"Asend" => A_SEND,
				"Adelete" => A_DELETE,
				"Achange" => A_CHANGE,
				"Clansite" => CLAN_SITE,
				"Logoinfo" => LOGO_INFO,
				"Logoname" => LOGO_NAME,
				"Abold" => "Pogrubienie",
				"Aitalic" => "Kursywa",
				"Aunderline" => "Podkreślenie",
				"Aemote" => "Emocje/Czynność",
				"Ocolors" => array("red" => "czerwony",
						   "green" => "zielony",
						   "white" => "biały",
						   "yellow" => "żółty",
						   "blue" => "niebieski",
						   "aqua" => "cyjan",
						   "fuchsia" => "fuksja",
						   "grey" => "szary",
						   "lime" => "limonka",
						   "maroon" => "wiśniowy",
						   "navy" => "granatowy",
						   "olive" => "oliwkowy",
						   "purple" => "purpurowy",
						   "silver" => "srebrny",
						   "teal" => "morski"),
				"Acolor" => "Kolor",
				"Acenter" => "Wycentrowanie",
				"Aquote" => "Cytat",
				"Tprefix" => "Tag przed imieniem postaci",
				"Tsuffix" => "Tag po imieniu postaci",
				"Prefix" => $mytribe->fields['prefix'],
				"Suffix" => $mytribe->fields['suffix'],
				"Tinfo" => "Tagi klanowe mogą mieć maksymalnie 5 znaków długości."));
	$avatar = $db -> Execute("SELECT logo FROM tribes WHERE id=".$mytribe -> fields['id']);
	$plik = 'images/tribes/'.$avatar -> fields['logo'];
	if (is_file($plik)) 
	  {
	    $smarty -> assign ("Logo1", "<center><img src=\"".$plik."\" heigth=\"100\" width=\"100\" /></center>");
	  }
	if (is_file($plik)) 
	  {
	    $smarty -> assign(array("Logo" => $avatar -> fields['logo'], 
				    "Change" => "Y"));
	  } 
	else 
	  {
	    $smarty -> assign(array("Change" => '',
				    "Logo" => ''));
	  }
	//Set tribe tags
	if (isset($_GET['action']) && $_GET['action'] == 'tags')
	  {
	    $_POST['prefix'] = str_replace("'","",strip_tags($_POST['prefix']));
	    $_POST['suffix'] = str_replace("'","",strip_tags($_POST['suffix']));
	    if (mb_strlen($_POST['prefix'], "UTF-8") > 5 || mb_strlen($_POST['suffix'], "UTF-8") > 5)
	      {
		error("Tagi są zbyt długie.");
	      }
	    $db->Execute("UPDATE `tribes` SET `prefix`='".$_POST['prefix']."', `suffix`='".$_POST['suffix']."' WHERE `id`=".$mytribe->fields['id']);
	    message("success", "Ustawiłeś tagi klanowe. <a href=\"tribeadmin.php?step2=messages\">".A_REFRESH."</a><br />");
	  }
	//Set tribe webpage
	if (isset ($_GET['action']) && $_GET['action'] == 'www') 
	  {
	    $_POST['www'] = str_replace("'","",strip_tags($_POST['www']));
	    $strWWW = $db -> qstr($_POST['www'], get_magic_quotes_gpc());
	    $db -> Execute("UPDATE tribes SET www=".$strWWW." WHERE id=".$mytribe -> fields['id']);
	    message("success", WWW_SET." <a href=\"http://".$_POST['www']."\" target=\"_blank\">".$_POST['www']."</a>. <a href=\"tribeadmin.php?step2=messages\">".A_REFRESH."</a><br />");
	  }
	if (isset ($_GET['step4']) && $_GET['step4'] == 'usun') 
	  {
	    if ($_POST['av'] != $mytribe -> fields['logo']) 
	      {
		error(ERROR);
	      }
	    $plik = 'images/tribes/'.$_POST['av'];
	    if (is_file($plik)) 
	      {
		unlink($plik);
		$db -> Execute("UPDATE tribes SET logo='' where id=".$mytribe -> fields['id']) or error ("nie mogÄ skasowaÄ");
		message("success", LOGO_DEL." <a href=\"tribeadmin.php?step2=messages\">".A_REFRESH."</a><br />");
	      } 
	    else 
	      {
		error (NO_FILE);
	      }
	  }
	if (isset ($_GET['step4']) && $_GET['step4'] == 'dodaj') 
	  {
	    $plik = $_FILES['plik']['tmp_name'];
	    $nazwa = $_FILES['plik']['name'];
	    $typ = $_FILES['plik']['type'];
	    if ($typ != 'image/pjpeg' && $typ != 'image/jpeg' && $typ != 'image/gif'  && $typ != 'image/png') 
	      {
		error (BAD_TYPE);
	      }
	    if ($typ == 'image/pjpeg' || $typ == 'image/jpeg') 
	      {
		$liczba = rand(1,1000000);
		$newname = md5($liczba).'.jpg';
		$miejsce = 'images/tribes/'.$newname;
	      }
	    if ($typ == 'image/gif') 
	      {
		$liczba = rand(1,1000000);
		$newname = md5($liczba).'.gif';
		$miejsce = 'images/tribes/'.$newname;
	      }
	    if ($typ == 'image/png') 
	      {
		$liczba = rand(1,1000000);
		$newname = md5($liczba).'.png';
		$miejsce = 'images/tribes/'.$newname;
	      }
	    if (is_uploaded_file($plik)) 
	      {
		if (!move_uploaded_file($plik,$miejsce)) 
		  {
		    error (NO_COPY);
		  }
	      } 
	    else 
	      {
		error (ERROR);
	      }
	    $db -> Execute("UPDATE tribes SET logo='".$newname."' where id=".$mytribe -> fields['id']);
	    message("success",  LOGO_LOAD." <a href=\"tribeadmin.php?step2=messages\">".A_REFRESH."</a><br />");
	  }
	/**
	 * Set new description and message
	 */
	if (isset ($_GET['action']) && $_GET['action'] == 'edit') 
	  {
	    $strPublicmsg =  bbcodetohtml($_POST['public_msg']);
	    $strPrivatemsg =  bbcodetohtml($_POST['private_msg']);
	    $strPrivatemsg = $db -> qstr($strPrivatemsg, get_magic_quotes_gpc());
	    $strPublicmsg = $db -> qstr($strPublicmsg, get_magic_quotes_gpc());
	    $db -> Execute("UPDATE `tribes` SET `private_msg`=".$strPrivatemsg.",  `public_msg`=".$strPublicmsg." WHERE `id`=".$mytribe -> fields['id']);
	    message("success", MSG_CHANGED);
	    $smarty -> assign(array("Pubmessage" => $_POST['public_msg'], 
				    "Privmessage" => $_POST['private_msg']));
	  }
      }
    /**
     * Drop members from clan
     */
    elseif ($_GET['step2'] == 'kick') 
      {
	if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['kick']) 
	  {
	    error (NO_PERM2);
	  }
	$objMembers = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$mytribe -> fields['id']." AND `id`!=".$mytribe -> fields['owner']);
	$arrMembers = array();
	while (!$objMembers -> EOF)
	  {
	    $arrMembers[$objMembers->fields['id']] = $objMembers -> fields['user'].' ID:'.$objMembers->fields['id'];
	    $objMembers -> MoveNext();
	  }
	$objMembers -> Close();
	$smarty -> assign(array("Kickid" => KICK_ID,
				"Fromclan" => FROM_CLAN,
				"Akick" => A_KICK2,
				"Members" => $arrMembers));
	if (isset ($_GET['action']) && $_GET['action'] == 'kick') 
	  {
	    if (!isset($_POST['id']))
	      {
		error(NO_ID);
	      }
	    checkvalue($_POST['id']);
	    $objTribe = $db -> Execute("SELECT `tribe` FROM `players` WHERE `id`=".$_POST['id']);
	    if ($objTribe -> fields['tribe'] != $mytribe -> fields['id'])
	      {
		error(NOT_IN_CLAN);
	      }
	    $objTribe -> Close();
	    if ($_POST['id'] != $mytribe -> fields['owner']) 
	      {
		$strDate = $db -> DBDate($newdate);
		$db -> Execute("UPDATE `players` SET `tribe`=0, `tribe_rank`='' WHERE `id`=".$_POST['id']." AND `tribe`=".$mytribe -> fields['id']);
		$db -> Execute("DELETE FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `player`=".$_POST['id']);
		/**
		 * Send info about kick
		 */
		// Get name of the person which is being kicked.
		$objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['id'].';');
		$strReceiversName = $objGetName -> fields['user'];
		$objGetName -> Close();
		
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['id'].",'".YOU_DROP.'<b><a href="tribes.php?view=view&amp;id='.$mytribe -> fields['id'].'">'.$mytribe -> fields['name']."</a></b>.', ".$strDate.", 'C')");
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '".CLAN_KICK1.'<b><a href="view.php?view='.$_POST['id'].'">'.$strReceiversName.'</a></b>'. CLAN_KICK2.'<b>'.$_POST['id'].'</b>'.HAS_BEEN.$mytribe -> fields['name'].".', ".$strDate.", 'C')");
		$objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `kick`=1");
		while (!$objPerm -> EOF)
		  {
		    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".CLAN_KICK1.'<b><a href="view.php?view='.$_POST['id'].'">'.$strReceiversName.'</a></b>'. CLAN_KICK2.'<b>'.$_POST['id'].'</b>'.HAS_BEEN.$mytribe -> fields['name'].".', ".$strDate.", 'C')");
		    $objPerm -> MoveNext();
		  }
		$objPerm -> Close();
		message("success", D_ID.$_POST['id'].NOT_IS);
	      } 
	    else 
	      {
		message("error", IS_LEADER);
	      }
	  }
      }
    /**
     * Give money from clan to members
     */
    elseif ($_GET['step2'] == 'loan')
      {
	if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['loan']) 
	  {
	    error (NO_PERM2);
	  }
	$objMembers = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$player->tribe);
	$arrMembers = array();
	while (!$objMembers->EOF)
	  {
	    $arrMembers[$objMembers->fields['id']] = $objMembers->fields['user'].' ID:'.$objMembers->fields['id'];
	    $objMembers->MoveNext();
	  }
	$objMembers->Close();
	$smarty -> assign(array("Aloan2" => A_LOAN2,
				"Playerid" => PLAYER_ID,
				"Goldcoins" => GOLD_COINS,
				"Mithcoins" => MITHRIL_COINS,
				"Members" => $arrMembers));
	if (isset($_GET['action']) && $_GET['action'] == 'loan') 
	  {
	    integercheck($_POST['amount']);
	    checkvalue($_POST['amount']);
	    if ($_POST['currency'] != 'credits' && $_POST['currency'] != 'platinum') 
	      {
		error (ERROR);
	      }
	    if ($_POST['currency'] == 'credits') 
	      {
		$poz = GOLD_COINS;
	      }
	    if ($_POST['currency'] == 'platinum') 
	      {
		$poz = MITHRIL_COINS;
	      }
	    $rec = $db -> Execute("SELECT tribe FROM players WHERE id=".$_POST['id']);
	    if ($rec -> fields['tribe'] != $mytribe -> fields['id']) 
	      {
		message("error", NOT_IN_CLAN);
	      } 
	    else 
	      {
		if (!$_POST['amount'] || !$_POST['id']) 
		  {
		    message("error", EMPTY_FIELDS);
		  } 
		else 
		  {
		    checkvalue($_POST['amount']);
		    if ($_POST['amount'] > $mytribe -> fields[$_POST['currency']]) 
		      {
			message("error", NO_AMOUNT.$poz.".");
		      } 
		    else 
		      {
			$db -> Execute("UPDATE `players` SET `".$_POST['currency']."`=`".$_POST['currency']."`+".$_POST['amount']." WHERE `id`=".$_POST['id']);
			$db -> Execute("UPDATE `tribes` SET `".$_POST['currency']."`=`".$_POST['currency']."`-".$_POST['amount']." WHERE `id`=".$mytribe -> fields['id']);
			$strDate = $db -> DBDate($newdate);
			// Get name of the person which is receiving money.
			$objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['id'].';');
			$strReceiversName = $objGetName -> fields['user'];
			$objGetName -> Close();
			$db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$_POST['id'].",'".SEND_YOU.$_POST['amount']." ".$poz.".', ".$strDate.", 'C')");
			/**
			 * Send informations about donation to player
			 */
			$db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '".CLAN_GIVE1.'<b><a href="view.php?view='.$_POST['id'].'">'.$strReceiversName.'</a></b>'.CLAN_GIVE2.'<b>'.$_POST['id']."</b> ".$_POST['amount']." ".$poz.".', ".$strDate.", 'C')");
			$objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$mytribe -> fields['id']." AND `loan`=1");
			while (!$objPerm -> EOF)
			  {
			    $db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".CLAN_GIVE1.'<b><a href="view.php?view='.$_POST['id'].'">'.$strReceiversName.'</a></b>'.CLAN_GIVE2.'<b>'.$_POST['id']."</b> ".$_POST['amount']." ".$poz.".', ".$strDate.", 'C')");
			    $objPerm -> MoveNext();
			  }
			$objPerm -> Close();
			message("success", YOU_GIVE.$_POST['id']." ".$_POST['amount']." ".$poz.".");
		      }
		  }
	      }
	  }
      }
    /**
     * Special options in clan
     */
    elseif ($_GET['step2'] == 'te') 
      {
	$smarty -> assign(array("Miscinfo" => MISC_INFO,
				"Afreeheal" => A_FREE_HEAL,
				"Youbuy" => YOU_BUY,
				"Aback" => A_BACK,
				'Hospass1' => 0));
	if ($mytribe -> fields['hospass'] == "Y") 
	  {
	    error (CLAN_HAVE."<br /><a href=\"tribeadmin.php\">...".A_BACK."</a>");
	  }
	if (isset($_GET['step3']) && $_GET['step3'] == 'hospass') 
	  {
	    if ($mytribe -> fields['platinum'] < 100) 
	      {
		error (NO_MITH."<br /><a href=\"tribeadmin.php\">...".A_BACK."</a>");
	      } 
	    else 
	      {
		$db -> Execute("UPDATE tribes SET platinum=platinum-100 WHERE id=".$mytribe -> fields['id']);
		$db -> Execute("UPDATE tribes SET hospass='Y' WHERE id=".$mytribe -> fields['id']);
		$smarty -> assign ("Hospass1", 1);
	      }
	  }
      }
    /**
     * Upgrading clan
     */
    elseif ($_GET['step2'] == 'upgrade')
      {
	if ($player->id != $mytribe -> fields['owner'])
	  {
	    error("Tylko przywódca może rozbudowywać klan.");
	  }
	if (isset($_GET['upgrade']))
	  {
	    if (!isset($_POST['update']))
	      {
		error('Zapomnij o tym.');
	      }
	    checkvalue($_POST['update']);
	    $intCost = ($_POST['update'] * 500000) - ($mytribe->fields['level'] * 500000);
	    if ($mytribe->fields['credits'] < $intCost)
	      {
		message('error', 'Klan nie posiada takiej ilości złota.');
	      }
	    elseif ($mytribe->fields['level'] >= $_POST['update'])
	      {
		message('error', 'Nie możesz obniżyć poziomu rozbudowy swojego klanu.');
	      }
	    elseif ($_POST['update'] > 5)
	      {
		error('Zapomnij o tym.');
	      }
	    else
	      {
		$db->Execute("UPDATE `tribes` SET `level`=".$_POST['update'].", `credits`=`credits`-".$intCost." WHERE `id`=".$mytribe->fields['id']) or die($db->ErrorMsg());
		$mytribe->fields['level'] = $_POST['update'];
		message('success', 'Rozbudowałeś swój klan.');
	      }
	  }
	if ($mytribe->fields['level'] > 3)
	  {
	    error("Nie możesz już rozbudowywać klanu.");
	  }
	$arrOptions = array(2 => 'Kamienica (koszt: 500000 sztuk złota, dodaje zbrojownię oraz magazyn klanowy, maksymalnie 10 osób w klanie)',
			    3 => 'Dworek (koszt: '.(1500000 - ($mytribe->fields['level'] * 500000)).' sztuk złota, dodaje skarbiec, astralny skarbiec oraz zielnik klanowy, maksymalnie 20 osób w klanie)',
			    4 => 'Dwór (koszt: '.(2000000 - ($mytribe->fields['level'] * 500000)).' sztuk złota, liczba osób w klanie bez ograniczeń) <br /><b>albo</b>',
			    5 => 'Zamek (koszt: '.(2500000 - ($mytribe->fields['level'] * 500000)).' sztuk złota, liczba osób w klanie bez ograniczeń, pozwala budować Astralną Machinę oraz uczestniczyć w walkach klanowych)');
	if ($mytribe->fields['level'] > 1)
	  {
	    for ($i = 2; $i <= $mytribe->fields['level']; $i++)
	      {
		unset($arrOptions[$i]);
	      }
	  }
	$smarty->assign(array("Upgradeinfo" => "Tutaj możesz ulepszyć swój klan do wyższego poziomu. Co dokładnie otrzyma klan w zamian za ulepszenie, podane jest poniżej. Pamiętaj jednak, że nie można cofnąć takiej decyzji.",
			      "Uoptions" => $arrOptions,
			      "Aupgrade" => "Ulepsz"));
      }
  }

if (!isset($_GET['step3']))
  {
    $_GET['step3'] = '';
  }

require_once("includes/tribemenu.php");
$smarty -> assign (array("Step2" => $_GET['step2'],
			 "Step3" => $_GET['step3']));
$smarty->display('tribeadmin.tpl');
require_once('includes/foot.php');
?>