<?php
/**
 *   File functions:
 *   Admin panel
 *
 *   @name                 : admin.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 19.03.2012
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

$title = "Panel Administracyjny"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/admin.php");

if ($player -> rank != "Admin") 
{
    error (NOT_ADMIN);
}

$smarty -> assign("Message", '');

/**
 * Functions from includes/admin dir
 */
if (isset($_GET['view']))
  {
    $smarty -> assign("Aback", A_BACK);
    $arrView = array('takeaway', 'clearc', 'czat', 'tags', 'jail', 'innarchive', 'banmail', 'addtext', 'logs');
    $intKey = array_search($_GET['view'], $arrView);
    if ($_GET['view'] == 'bforum')
      {
	$intKey = 2;
      }
    if ($intKey !== false)
    {
        require_once("includes/admin/".$arrView[$intKey].".php");
    }
    /**
     * Reported bugs
     */
    elseif ($_GET['view'] == 'bugreport')
      {
	$smarty -> assign(array("Bugtype" => BUG_TYPE,
				"Bugloc" => BUG_LOC,
				"Bugid" => BUG_ID,
				"Bugname" => BUG_NAME));
	/**
	 * Bugs list
	 */
	if (!isset($_GET['step']))
	  {
	    $objBugs = $db -> Execute("SELECT `id`, `sender`, `title`, `type`, `location` FROM `bugreport` WHERE `resolution`=0 ORDER BY `id` ASC");
	    $arrId = array();
	    $arrReporter = array();
	    $arrTitle = array();
	    $arrType = array();
	    $arrLocation = array();
	    while (!$objBugs -> EOF)
	      {
		$arrId[] = $objBugs -> fields['id'];
		$arrReporter[] = $objBugs -> fields['sender'];
		$arrTitle[] = $objBugs -> fields['title'];
		$arrLocation[] = $objBugs -> fields['location'];
		if ($objBugs -> fields['type'] == 'text')
		  {
		    $arrType[] = BUG_TEXT;
		  }
                else
		  {
		    $arrType[] = BUG_CODE;
		  }
		$objBugs -> MoveNext();
	      }
	    $objBugs -> Close();
	    $smarty -> assign(array("Bugreporter" => BUG_REPORTER,
				    "Bugstype" => $arrType,
				    "Bugsloc" => $arrLocation,
				    "Bugsid" => $arrId,
				    "Bugsreporter" => $arrReporter,
				    "Bugsname" => $arrTitle));
	  }
	/**
	 * Edit bug
	 */
        else
	  {
	    checkvalue($_GET['step']);
	    $objBug = $db -> Execute("SELECT `id`, `sender`, `title`, `type`, `location`, `desc` FROM `bugreport` WHERE `id`=".$_GET['step']);
	    if (!$objBug -> fields['id'])
	      {
		error(ERROR);
	      }
	    if ($objBug -> fields['type'] == 'text')
	      {
		$strType = BUG_TEXT;
	      }
            else
	      {
		$strType = BUG_CODE;
	      }
	    require_once('includes/bbcode.php');
	    $strDesc = htmltobbcode($objBug -> fields['desc']);
	    $arrOptions = array(BUG_FIXED, NOT_BUG, WORK_FOR_ME, MORE_INFO, BUG_DOUBLE);
	    $arrActions = array('fixed', 'notbug', 'workforme', 'moreinfo', 'duplicate');
	    $smarty -> assign(array("Bugdesc" => BUG_DESC,
				    "Bugactions" => BUG_ACTIONS,
				    "Bugoptions" => $arrOptions,
				    "Bugactions2" => $arrActions,
				    "Amake" => A_MAKE,
				    "Tcomment" => T_COMMENT2,
				    "Bugname2" => $objBug -> fields['title'],
				    "Bugtype2" => $strType,
				    "Bugloc2" => $objBug -> fields['location'],
				    "Bugdesc2" => $strDesc));
	    /**
	     * Set bug status
	     */
	    if (isset($_POST['actions']))
	      {
		if (!in_array($_POST['actions'], $arrActions))
		  {
		    error(ERROR);
		  }
		$strInfo = YOUR_BUG.$objBug -> fields['title'].B_ID.$_GET['step'];
		$strDate = $db -> DBDate($newdate);
		$intKey = array_search($_POST['actions'], $arrActions);
		if ($intKey == 3)
		  {
		    $strInfo = $strInfo.MORE_INFO2;
		    $strMessage = MORE_INFO3;
		  }
		if ($intKey == 4)
		  {
		    $strInfo = $strInfo.BUG_DOUBLE2;
		    $strMessage = BUG_DOUBLE3;
		  }
		if ($intKey == 2)
		  {
		    $strInfo = $strInfo.WORK_FOR_ME2;
		    $strMessage = WORK_FOR_ME3;
		  }
		if ($intKey == 1)
		  {
		    $strInfo = $strInfo.NOT_BUG3;
		    $strMessage = NOT_BUG2;
		  }
		if ($intKey == 0)
		  {
		    $strInfo = $strInfo.HAS_FIXED;
		    $strMessage = HAS_FIXED2;
		    $strAuthor = '<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id.'</b>';
		    $strDesc = T_BUG.$strType."): ".$objBug -> fields['title']. REPORTED_BY.$objBug -> fields['sender'];
		    $db -> Execute("INSERT INTO `changelog` (`author`, `location`, `text`, `date`, `lang`) VALUES('".$strAuthor."', '".$objBug -> fields['location']."', '".$strDesc."', ".$strDate.", '".$player -> lang."')");
		    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+".$_POST['vallars']." WHERE `id`=".$objBug->fields['sender']);
		    if ($_POST['vallars'] != 0)
		      {
			$db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$objBug->fields['sender'].", ".$_POST['vallars'].", 'Zgłoszenie błędu.')");
		      }
		  }
		$db -> Execute("DELETE FROM `bugreport` WHERE `id`=".$_GET['step']);
		if (isset($_POST['bugcomment']) && !empty($_POST['bugcomment']))
		  {
		    $strInfo = $strInfo." <b>".T_COMMENT2.":</b> ".$_POST['bugcomment'];
		  }
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objBug -> fields['sender'].", '".$strInfo."', ".$strDate.", 'A')");
		error($strMessage);
	      }
	    $objBug -> Close();
	  }
      }
    /**
     * Add/remove player vallars
     */
    elseif (isset($_GET['view']) && $_GET['view'] == 'vallars')
      {
	$smarty->assign(array("Valid" => "ID",
			      "Aadd" => "Dodaj",
			      "Vreason" => "Przyczyna"));
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    $strDate = $db -> DBDate($newdate);
	    checkvalue($_POST['id']);
	    $_POST['amount'] = intval($_POST['amount']);
	    if ($_POST['amount'] > 0)
	      {
		$strInfo = "Przyznano Ci ";
	      }
	    else
	      {
		$strInfo = "Zabrano Ci ";
	      }
	    if ($_POST['amount'] < -1 || $_POST['amount'] > 1 || $_POST['amount'] == 0)
	      {
		$intLast = $_POST['amount'] % 10;
		if ($intLast < 5 && $intLast > -5)
		  {
		    $strVallars = 'Vallary';
		  }
		else
		  {
		    $strVallars = 'Vallarów';
		  }
	      }
	    else
	      {
		$strVallars = 'Vallar';
	      }
	    $strInfo .= abs($_POST['amount'])." ".$strVallars.". Przyczyna: ".$_POST['reason'].".";
	    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+".$_POST['amount']." WHERE `id`=".$_POST['id']);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['id'].", '".$strInfo."', ".$strDate.", 'V')");
	    if ($_POST['amount'] != 0)
	      {
		$db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$_POST['id'].", ".$_POST['amount'].", '".$_POST['reason']."')");
	      }
	    error($strInfo);
	  }
      }
    /**
     * Add player to quest
     */
    elseif ($_GET['view'] == 'playerquest')
      {
	$smarty -> assign(array("Addplayer" => ADD_PLAYER,
				"Toquest" => TO_QUEST,
				"Aadd" => A_ADD));
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    if (empty($_POST['pid']) || empty($_POST['qid']))
	      {
		error(EMPTY_FIELDS);
	      }
	    $db -> Execute("DELETE FROM `questaction` WHERE player=".$_POST['pid']);
	    $db -> Execute("INSERT INTO `questaction` (`player`, `quest`, `action`) VALUES(".$_POST['pid'].", ".$_POST['qid'].", 'start')");
	    $db -> Execute("UPDATE `players` SET `miejsce`='Podróż' WHERE id=".$_POST['pid']);
	    $smarty -> assign("Message", YOU_ADD);
	  }
      }
    /**
     * Add info about changes in game
     */
    elseif ($_GET['view'] == 'changelog')
      {
	if ($player -> id != 1)
	  {
	    error(ONLY_MAIN);
	  }
	$smarty -> assign(array("Changeinfo" => CHANGE_INFO,
				"Changelocation" => CHANGE_LOCATION,
				"Changetext" => CHANGE_TEXT,
				"Aadd" => A_ADD));
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    if (empty($_POST['location']) || empty($_POST['changetext']))
	      {
		error(EMPTY_FIELDS);
	      }
	    $strDate = $db -> DBDate($newdate);
	    $strAuthor = '<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id.'</b>';
	    require_once('includes/bbcode.php');
	    $strText = bbcodetohtml($_POST['changetext']); 
	    $db -> Execute("INSERT INTO `changelog` (`author`, `location`, `text`, `date`, `lang`) VALUES('".$strAuthor."', '".$_POST['location']."', '".$strText."', ".$strDate.", '".$player -> lang."')");
	    $smarty -> assign("Message", CHANGE_ADDED);
	  }
      }

    /**
     * Edit meta informations
     */
    elseif ($_GET['view'] == 'meta')
      {
	if ($player -> id != 1)
	  {
	    error(ONLY_MAIN);
	  }
	$smarty -> assign(array("Metainfo" => META_INFO,
				"Metakey" => META_KEY,
				"Metadesc" => META_DESC,
				"Aadd" => A_ADD));
	/**
	 * Change meta info
	 */
	if (isset($_GET['step']) && $_GET['step'] == 'modify')
	  {
	    $db -> Execute("UPDATE `settings` SET `value`='".$_POST['metakey']."' WHERE `setting`='metakeywords'");
	    $db -> Execute("UPDATE `settings` SET `value`='".$_POST['metadesc']."' WHERE `setting`='metadescr'");
	    $smarty -> assign("Message", META_UPGRADE);
	  }
      }

    /**
     * Add/Modify forum categories
     */
    elseif ($_GET['view'] == 'forums')
      {
	$arrLanguage = scandir('languages/', 1);
	$arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));
	$objCatforum = $db -> Execute("SELECT id, name FROM categories");
	$arrId = array();
	$arrName = array();
	$i = 0;
	while (!$objCatforum -> EOF)
	  {
	    $arrId[$i] = $objCatforum -> fields['id'];
	    $arrName[$i] = $objCatforum -> fields['name'];
	    $objCatforum -> MoveNext();
	    $i++;
	  }
	$objCatforum -> Close();
	$arrOptionw = array('All;', 'Staff;', 'Sędzia;', 'Kanclerz_Sądu;', 'Marszałek Rady;', 'Poseł;', 'Prawnik;', 'Ławnik;', 'Prokurator;');
	$arrOptionv = array('1All;', '1Staff;', '1Sędzia;', '1Kanclerz_Sądu;', '1Marszałek Rady;', '1Poseł;', '1Prawnik;', '1Ławnik;', '1Prokurator;');
	$arrOptionname = array(T_ALL, T_STAFF, T_JUDGE, T_JUDGE2, T_COUNT, T_COUNT2, T_LAWYER, T_JUDGE3, T_PROCURATOR);
	$arrLangsel = array('', '');
	$arrOptionwsel = array('', '', '', '', '', '', '', '', '');
	$arrOptionvsel = array('', '', '', '', '', '', '', '', '');
	$smarty -> assign(array("Catlist" => CAT_LIST,
				"Aadd" => A_ADD,
				"Tname" => T_NAME,
				"Tdesc" => T_DESC,
				"Tlang" => T_LANG,
				"Twrite" => T_WRITE,
				"Tvisit" => T_VISIT,
				"Tcatdesc" => '',
				"Tcatname" => '',
				"Catid2" => $i + 2,
				"Catid" => $arrId,
				"Catname" => $arrName,
				"Catlang" => $arrLanguage,
				"Toptionw" => $arrOptionw,
				"Toptionv" => $arrOptionv,
				"Toptionname" => $arrOptionname,
				"Tlangsel" => $arrLangsel,
				"Toptionwsel" => $arrOptionwsel,
				"Toptionvsel" => $arrOptionvsel));
	/**
	 * When category is selected
	 */
	if (isset($_GET['id']) && !isset($_GET['step']))
	  {
	    $objCategory = $db -> Execute("SELECT * FROM categories WHERE id=".$_GET['id']);
	    $i = 0;
	    foreach ($arrLanguage as $strLanguage)
	      {
		if ($strLanguage == $objCategory -> fields['lang'])
		  {
		    $arrLangsel[$i] = 'selected';
		    break;
		  }
		$i++;
	      }
	    $i = 0;
	    foreach ($arrOptionw as $strOptionw)
	      {
		$strOptionw = str_replace("_", " ", $strOptionw);
		$intFind = strpos($objCategory -> fields['perm_write'], $strOptionw);
		if ($intFind !== false)
		  {
		    $arrOptionwsel[$i] = 'checked';
		  }
		$i++;
	      }
	    $i = 0;
	    foreach ($arrOptionw as $strOptionv)
	      {
		$strOptionv = str_replace("_", " ", $strOptionv);
		$intFind = strpos($objCategory -> fields['perm_visit'], $strOptionv);
		if ($intFind !== false)
		  {
		    $arrOptionvsel[$i] = 'checked';
		  }
		$i++;
	      }
	    $smarty -> assign(array("Catid2" => $_GET['id'],
				    "Tcatdesc" => $objCategory -> fields['desc'],
				    "Tcatname" => $objCategory -> fields['name'],
				    "Tlangsel" => $arrLangsel,
				    "Toptionwsel" => $arrOptionwsel,
				    "Toptionvsel" => $arrOptionvsel));
	    $objCategory -> Close();
	  }
	/**
	 * Edit/add category
	 */
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    $strPermwrite = '';
	    foreach ($arrOptionw as $strOptionw)
	      {
		if (isset($_POST[$strOptionw]))
		  {
		    $strOptionw = str_replace("_", " ", $strOptionw);
		    $strPermwrite = $strPermwrite.$strOptionw;
		  }
	      }
	    $strPermvisit = '';
	    $i = 0;
	    foreach ($arrOptionv as $strOptionv)
	      {
		if (isset($_POST[$strOptionv]))
		  {
		    $strOption = str_replace("_", " ", $arrOptionw[$i]);
		    $strPermvisit = $strPermvisit.$strOption;
		  }
		$i++;
	      }
	    $objTest = $db -> Execute("SELECT id FROM categories WHERE id=".$_GET['id']);
	    if ($objTest -> fields['id'])
	      {
		$db -> Execute("UPDATE categories SET `name`='".$_POST['catname']."', `desc`='".$_POST['catdesc']."', `lang`='".$_POST['catlang']."', `perm_write`='".$strPermwrite."', `perm_visit`='".$strPermvisit."' WHERE id=".$_GET['id']) or die($db -> ErrorMsg());
		$smarty -> assign("Message", CATEGORY_MODIFIED);
	      }
	    else
	      {
		$db -> Execute("INSERT INTO categories (`name`, `desc`, `lang`, `perm_write`, `perm_visit`) VALUES('".$_POST['catname']."', '".$_POST['catdesc']."', '".$_POST['catlang']."', '".$strPermwrite."', '".$strPermvisit."')") or die($db -> ErrorMsg());
		$smarty -> assign("Message", CATEGORY_ADDED);
	      }
	    $objTest -> Close();
	  }
      }

    /**
     * Add new plans in mill
     */
    elseif ($_GET['view'] == 'mill') 
      {
	$smarty -> assign(array("Sname" => S_NAME,
				"Scost" => S_COST,
				"Samount" => S_AMOUNT,
				"Slevel" => S_LEVEL,
				"Stype" => S_TYPE,
				"Sbow" => S_BOW,
				"Sarrow" => S_ARROWS,
				"Aadd" => A_ADD,
				"Selite" => "Elitarny",
				"Selitetype" => "Elitarny typ",
				"Dragon" => "Smoczy",
				"Elven" => "Elfi"));
	if (isset ($_GET['step']) && $_GET['step'] == 'mill') 
	  {
	    if (!$_POST['nazwa'] || !$_POST['cena'] || !$_POST['poziom']) 
	      {
		error (EMPTY_FIELDS);
	      }
	    if ($_POST['type'] == 'B')
	      {
		$strTwohand = 'Y';
	      }
            else
	      {
		$strTwohand = 'N';
	      }
	    $_POST['elite'] = intval($_POST['elite']);
	    $strName = $db -> qstr($_POST['nazwa'], get_magic_quotes_gpc());
	    $db -> Execute("INSERT INTO `mill` (`name`, `cost`, `level`, `amount`, `type`, `twohand`, `elite`, `elitetype`) VALUES(".$strName.", ".$_POST['cena'].", ".$_POST['poziom'].", ".$_POST['amount'].", '".$_POST['type']."', '".$strTwohand."', ".$_POST['elite'].", '".$_POST['elitetype']."')");
	  }
      }

    /**
     * Add player to list of donators
     */
    elseif ($_GET['view'] == 'donator')
      {
	if (!isset($_GET['step']))
	  {
	    $smarty -> assign(array("Donatorinfo" => DONATOR_INFO,
				    "Pname" => P_NAME,
				    "Aadd" => A_ADD));
	  }
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    if (empty($_POST['plname']))
	      {
		error(ERROR);
	      }
	    $strName = $db -> qstr($_POST['plname'], get_magic_quotes_gpc());
	    $db -> Execute("INSERT INTO donators (name) VALUES('".$_POST['plname']."')");
	    $smarty -> assign("Message", YOU_ADD.$_POST['plname'].TO_DONATORS);
	  }
      }

    /**
     * Edit monsters
     */
    elseif ($_GET['view'] == 'monster2')
      {
	if (!isset($_GET['step']))
	  {
	    $objMonsters = $db -> Execute("SELECT id, name FROM monsters");
	    $arrMonsters = array();
	    $arrMid = array();
	    $i = 0;
	    while (!$objMonsters -> EOF)
	      {
		$arrMonsters[$i] = $objMonsters -> fields['name'];
		$arrMid[$i] = $objMonsters -> fields['id'];
		$i++ ;
		$objMonsters -> MoveNext();
	      }
	    $objMonsters -> Close();
	    $smarty -> assign(array("Mname" => M_NAME,
				    "Names" => $arrMonsters,
				    "Mid" => $arrMid,
				    "Anext" => A_NEXT));
	  }
	if (isset($_GET['step']))
	  {
	    require_once("includes/bbcode.php");
	    if ($_GET['step'] == 'next')
	      {
		checkvalue($_POST['mid']);
		$objMonster = $db -> Execute("SELECT * FROM `monsters` WHERE `id`=".$_POST['mid']);
		$strDesc = htmltobbcode($objMonster->fields['desc']);
		$smarty -> assign(array("Mname" => $objMonster -> fields['name'],
					"Mlvl" => $objMonster -> fields['level'],
					"Mhp" => $objMonster -> fields['hp'],
					"Magility" => $objMonster -> fields['agility'],
					"Mstrength" => $objMonster -> fields['strength'],
					"Mspeed" => $objMonster -> fields['speed'],
					"Mendurance" => $objMonster -> fields['endurance'],
					"Mcredits1" => $objMonster -> fields['credits1'],
					"Mcredits2" => $objMonster -> fields['credits2'],
					"Mexp1" => $objMonster -> fields['exp1'],
					"Mexp2" => $objMonster -> fields['exp2'],
					"Mlocation" => $objMonster -> fields['location'],
					"Mlootname" => $objMonster->fields['lootnames'],
					"Mlootchance" => $objMonster->fields['lootchances'],
					"Mdesc" => $strDesc,
					"Tmdesc" => "Opis potwora",
					"Tmname" => M_NAME,
					"Tmlevel" => M_LEVEL,
					"Tmhp" => M_HP,
					"Tmagi" => M_AGI,
					"Tmpower" => M_POWER,
					"Tmspeed" => M_SPEED,
					"Tmcond" => M_COND,
					"Tmmingold" => M_MIN_GOLD,
					"Tmmaxgold" => M_MAX_GOLD,
					"Tmminexp" => M_MIN_EXP,
					"Tmmaxexp" => M_MAX_EXP,
					"Tmlocation" => M_LOCATION,
					"Aedit" => A_EDIT,
					"Tmlootnames" => "Nazwy komponentów",
					"Tmlootchances" => "Szansa na komponenty",
					"Mid" => $_POST['mid']));
		$objMonster -> Close();
	      }
	    elseif ($_GET['step'] == 'monster')
	      {
		if (!$_POST['name'] || !$_POST['level'] || !$_POST['hp'] || !$_POST['agility'] || !$_POST['strength'] || !$_POST['credits1'] || !$_POST['credits2'] || !$_POST['exp1'] || !$_POST['exp2'] || !$_POST['speed'] || !$_POST['endurance']|| !$_POST['location']) 
		  {
		    error (EMPTY_FIELDS);
		  }
		$strName = $db -> qstr($_POST['name'], get_magic_quotes_gpc());
		$strLocation = $db -> qstr($_POST['location'], get_magic_quotes_gpc());
		$strLoot1 = $db-> qstr($_POST['lootnames'], get_magic_quotes_gpc());
		$strLoot2 = $db-> qstr($_POST['lootchances'], get_magic_quotes_gpc());
		$strDesc = bbcodetohtml($_POST['mdesc']);
		$db -> Execute("UPDATE `monsters` SET `name`=".$strName.", `level`=".$_POST['level'].", `hp`=".$_POST['hp'].", `agility`=".$_POST['agility'].", `strength`=".$_POST['strength'].", `credits1`=".$_POST['credits1'].", `credits2`=".$_POST['credits2'].", `exp1`=".$_POST['exp1'].", `exp2`=".$_POST['exp2'].", `speed`=".$_POST['speed'].", `endurance`=".$_POST['endurance'].", `location`=".$strLocation.", `lootnames`=".$strLoot1.", `lootchances`=".$strLoot2.", `desc`='".$strDesc."' WHERE `id`=".$_POST['mid']);
		$smarty -> assign("Message", YOU_EDIT.$_POST['name']);
	      }
	  }
      }

    /**
     * Release player from jail
     */
    elseif ($_GET['view'] == 'jailbreak')
      {
	if (!isset($_GET['step']))
	  {
	    $smarty -> assign(array("Afree" => A_FREE,
				    "Jailid" => JAIL_ID));
	  }
	if (isset($_GET['step']) && $_GET['step'] == 'next')
	  {
	    checkvalue($_POST['jid']);
	    $objPrisoner = $db -> Execute("SELECT prisoner FROM jail WHERE prisoner=".$_POST['jid']);
	    if (!$objPrisoner -> fields['prisoner'])
	      {
		error(NO_PLAYER2);
	      }
	    $objPrisoner -> Close();
	    $db -> Execute("DELETE FROM jail WHERE prisoner=".$_POST['jid']);
	    $db -> Execute("UPDATE players SET miejsce='Altara' WHERE id=".$_POST['jid']);
	    $smarty -> assign("Message", T_MESSAGE.$_POST['jid']);
	  }
      }


    /**
     * Add new poll
     */
    elseif ($_GET['view'] == 'poll')
      {
	if (!isset($_GET['step']))
	  {
	    $arrLanguage = scandir('languages/', 1);
	    $arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));
	    $smarty -> assign(array("Tamount" => T_AMOUNT,
				    "Anext" => A_NEXT,
				    "Tlang" => T_LANG,
				    "Llang" => $arrLanguage,
				    "Tdays" => T_DAYS,
				    "Tdesc" => "Opcjonalny opis"));
	  }
	$smarty -> assign("Tquestion", T_QUESTION);
	/**
	 * Add answers to poll
	 */
	if (isset($_GET['step']) && $_GET['step'] == 'second')
	  {
	    if (empty($_POST['question']) || empty($_POST['amount']) || empty($_POST['days']))
	      {
		error(EMPTY_FIELDS);
	      }
	    if (!isset($_POST['desc']))
	      {
		$_POST['desc'] = '';
	      }
	    checkvalue($_POST['amount']);
	    checkvalue($_POST['days']);
	    $arrAnswers = array();
	    for ($i = 0; $i < $_POST['amount']; $i++)
	      {
		$arrAnswers[$i] = "answer".$i;
	      }
	    $objPollid = $db -> Execute("SELECT id FROM polls ORDER BY id DESC");
	    if (!$objPollid -> fields['id'])
	      {
		$intId = 1;
	      }
            else
	      {
		$intId = $objPollid -> fields['id'] + 1;
	      }
	    /**
	     * Update amount of players
	     */
	    $objQuery = $db -> Execute("SELECT id FROM players");
	    $intMembers = $objQuery -> RecordCount();
	    $objQuery -> Close();
	    $db -> Execute("UPDATE polls SET members=".$intMembers." WHERE id=".$objPollid -> fields['id']." AND votes=-1");
	    $objPollid -> Close();
	    $strQuestion = $db -> qstr($_POST['question'], get_magic_quotes_gpc());
	    $strDesc = $db->qstr($_POST['desc'], get_magic_quotes_gpc());
	    $db -> Execute("INSERT INTO `polls` (`id`, `poll`, `votes`, `lang`, `days`, `desc`) VALUES(".$intId.", ".$strQuestion.", -1, '".$_POST['lang']."', ".$_POST['days'].", ".$strDesc.")") or $db -> ErrorMsg();
	    $smarty -> assign(array("Answers" => $arrAnswers,
				    "Question" => $_POST['question'],
				    "Amount" => $_POST['amount'],
				    "Aadd" => A_ADD,
				    "Tanswer" => T_ANSWER,
				    "Llang" => $_POST['lang'],
				    "Pollid" => $intId,
				    "Adays" => $_POST['days']));
	  }
	/**
	 * Add poll
	 */
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    for($i = 0; $i < $_POST['amount']; $i++)
	      {
		$strName = "answer".$i;
		if (empty($_POST[$strName]))
		  {
		    error(EMPTY_FIELDS);
		  }
		$strAnswer = $db -> qstr($_POST[$strName], get_magic_quotes_gpc());
		$db -> Execute("INSERT INTO polls (id, poll, lang) VALUES(".$_POST['pid'].", ".$strAnswer.", '".$_POST['lang']."')");
	      }
	    $db -> Execute("UPDATE players SET poll='N'");
	    $db -> Execute("UPDATE settings SET value='Y' WHERE setting='poll'");
	    $smarty -> assign("Message", POLL_ADDED);
	  }
      }

    /**
     * Add new word to censorship
     */
    elseif ($_GET['view'] == 'censorship')
      {
	/**
	 * Bad words list
	 */
	$objWords = $db -> Execute("SELECT * FROM bad_words");
	$arrWords = array();
	$i = 0;
	while (!$objWords -> EOF)
	  {
	    $arrWords[$i] = $objWords -> fields['bword'];
	    $i = $i + 1;
	    $objWords -> MoveNext();
	  }
	$objWords -> Close();
	$smarty -> assign(array("Amake" => A_MAKE,
				"Words" => $arrWords,
				"Aadd" => A_ADD,
				"Adelete" => A_DELETE,
				"Tword" => T_WORD,
				"Wordslist" => WORDS_LIST));
	if (isset($_GET['step']) && $_GET['step'] == 'modify') 
	  {
	    $strWord = $db -> qstr($_POST['bword'], get_magic_quotes_gpc());
	    /**
	     * Add word
	     */
	    if ($_POST['action'] == 'add') 
	      {
		$db -> Execute("INSERT INTO bad_words (bword) VALUES(".$strWord.")");
		$smarty -> assign("Message", YOU_ADD." <b>".$_POST['bword']."</b>. (<a href=\"admin.php?view=censorship\">".REFRESH."</a>)");
	      }
	    /**
	     * Delete word
	     */
	    if ($_POST['action'] == 'delete') 
	      {
		$db -> Execute("DELETE FROM bad_words WHERE bword=".$strWord);
		$smarty -> assign("Message", YOU_DELETE." <b>".$_POST['bword']."</b>. (<a href=\"admin.php?view=censorship\">".REFRESH."</a>)");
	      }
	  }
      }

    /**
     * Close registration new players
     */
    elseif ($_GET['view'] == 'register')
      {
	$smarty -> assign(array("Gopen" => G_OPEN,
				"Gclose" => G_CLOSE,
				"Ifclose" => IF_CLOSE,
				"Amake" => A_MAKE));
	if (isset ($_GET['step']) && $_GET['step'] == 'close') 
	  {
	    if ($_POST['close'] == 'close') 
	      {
		$db -> Execute("UPDATE settings SET value='N' WHERE setting='register'");
		$strReason = $db -> qstr($_POST['reason'], get_magic_quotes_gpc());
		$db -> Execute("UPDATE settings SET value=".$strReason." WHERE setting='close_register'");
		error (YOU_CLOSE);
	      }
	    if ($_POST['close'] == 'open') 
	      {
		$db -> Execute("UPDATE settings SET value='Y' WHERE setting='register'");
		$db -> Execute("UPDATE settings SET value='' WHERE setting='close_register'");
		error (YOU_OPEN);
	      }
	  }   
      }

    /**
     * Ban and unban players by IP, emali, nick or ID
     */
    elseif ($_GET['view'] == 'ban') 
      {
	$smarty -> assign(array("Banlist" => BAN_LIST,
				"Baninfo" => BAN_INFO,
				"Banvalue" => BAN_VALUE,
				"Banip" => BAN_IP,
				"Banemail" => BAN_EMAIL,
				"Bannick" => BAN_NICK,
				"Banid" => BAN_ID,
				"Abanpl" => A_BAN_PL,
				"Aunban" => A_UNBAN,
				"Anext" => A_NEXT,
				"Bantype" => BAN_TYPE,
				"Banval" => BAN_VAL,
				"Banned" => BANNED));
	/**
	 * Banlist
	 */
	$arrtype = array();
	$arramount = array();
	$i = 0;
	$ban = $db -> Execute("SELECT type, amount FROM ban");
	while (!$ban -> EOF)
	  {
	    $arrtype[$i] = $ban -> fields['type'];
	    $arramount[$i] = $ban -> fields['amount'];
	    $i = $i + 1;
	    $ban -> MoveNext();
	  }
	$ban -> Close();
	$smarty -> assign(array("Type" => $arrtype, 
				"Amount" => $arramount));
	if (isset($_GET['step']) && $_GET['step'] == 'modify') 
	  {
	    $strAmount = $db -> qstr($_POST['amount'], get_magic_quotes_gpc());
	    /**
	     * Ban player
	     */
	    if ($_POST['action'] == 'ban') 
	      {
		$db -> Execute("INSERT INTO ban (type, amount) VALUES('".$_POST['type']."', ".$strAmount.")");
		$smarty -> assign("Message", YOU_BAN." <b>".$_POST['type']."</b> ".$_POST['amount'].". (<a href=\"admin.php?view=ban\">".REFRESH."</a>)");
	      }
	    /**
	     * Unban player
	     */
	    if ($_POST['action'] == 'unban') 
	      {
		$db -> Execute("DELETE FROM ban WHERE type='".$_POST['type']."' AND amount=".$strAmount);
		$smarty -> assign("Message", YOU_UNBAN." <b>".$_POST['type']."</b> ".$_POST['amount'].". (<a href=\"admin.php?view=ban\">".REFRESH."</a>)");
	      }
	  }
      }

    /**
     * Delete players which not login long than 21 days
     */
    elseif ($_GET['view'] == 'delplayers') 
      {
	if ($player -> id != 1) 
	  {
	    error(ONLY_MAIN);
	  }
	$curenttime = time();
	$lpv = $curenttime - 1900800;
	$arrdelete = $db -> Execute("SELECT `id`, `avatar` FROM `players` WHERE `age`>21 AND `lpv`<".$lpv);
	while (!$arrdelete -> EOF) 
	  {
	    $db -> Execute("DELETE FROM `players` WHERE `id`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `core` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `core_market` WHERE `seller`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `equipment` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `smith` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `log` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `mail` WHERE `owner`=".$arrdelete -> fields['id']);
	    $objOutid = $db -> Execute("SELECT `id` FROM `outposts` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `outpost_mosters` WHERE `outpost`=".$objOutid -> fields['id']);
	    $db -> Execute("DELETE FROM `outpost_veterans` WHERE `outpost`=".$objOutid -> fields['id']);
	    $objOutid -> Close();
	    $db -> Execute("DELETE FROM `outposts` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `pmarket` WHERE `seller`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `hmarket` WHERE `seller`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `potions` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `herbs` WHERE `gracz`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `minerals` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `alchemy_mill` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `czary` WHERE `gracz`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `smith_work` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `notatnik` WHERE `gracz`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `tribe_oczek` WHERE `gracz`=".$arrdelete -> fields['id']);
	    $objHouse = $db -> Execute("SELECT `locator` FROM `houses` WHERE `owner`=".$arrdelete -> fields['id']);
	    if ($objHouse -> fields['locator'])
	      {
		$db -> Execute("UPDATE `houses` SET `owner`=".$objHouse -> fields['locator'].", `locator`=0 WHERE `owner`=".$arrdelete -> fields['id']) or $db -> ErrorMsg();
	      }
            else
	      {
		$db -> Execute("DELETE FROM `houses` WHERE `owner`=".$arrdelete -> fields['id']);
	      }
	    $objHouse -> Close();
	    $db -> Execute("DELETE FROM `farms` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `farm` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `jail` WHERE `prisoner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `mill_work` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `mill` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `questaction` WHERE `player`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `amarket` WHERE `seller`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$arrdelete -> fields['id']." AND `location`='V'");
	    $db -> Execute("DELETE FROM `astral_bank` WHERE `owner`=".$arrdelete -> fields['id']." AND `location`='V'");
	    $db -> Execute("DELETE FROM `astral_plans` WHERE `owner`=".$arrdelete -> fields['id']." AND `location`='V'");
	    $db -> Execute("DELETE FROM `lost_pass` WHERE `id`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `ban` WHERE `type`='ID' AND `amount`='".$arrdelete -> fields['id']."'");
	    $db -> Execute("DELETE FROM `jeweller` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `jeweller_work` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `ban_mail` WHERE `id`=".$arrdelete -> fields['id']);
	    $db -> Execute("DELETE FROM `links` WHERE `owner`=".$arrdelete -> fields['id']);
	    $db->Execute("DELETE FROM `revent` WHERE `pid`=".$arrdelete->fields['id']);
	    $db->Execute("DELETE FROM `contacts` WHERE `owner`=".$arrdelete->fields['id']);
	    $db->Execute("DELETE FROM `contacts` WHERE `pid`=".$arrdelete->fields['id']);
	    $db->Execute("DELETE FROM `ignored` WHERE `owner`=".$arrdelete->fields['id']);
	    $db->Execute("DELETE FROM `ignored` WHERE `pid`=".$arrdelete->fields['id']);
	    $strFile = 'avatars/'.$arrdelete -> fields['avatar'];
	    if (is_file($strFile)) 
	      {
		unlink($strFile);
	      }
	    $arrdelete -> MoveNext();
	  }
	$number = $arrdelete -> RecordCount();
	$arrdelete -> Close();
	$arrdelete = $db -> Execute("SELECT `id` FROM `players` WHERE `age`>3 AND `lpv`=0");
	while (!$arrdelete -> EOF) 
	  {
	    $db -> Execute("DELETE FROM `players` WHERE `id`=".$arrdelete -> fields['id']);
	    $arrdelete -> MoveNext();
	  }
	$number2 = $arrdelete -> RecordCount();
	$arrdelete -> Close();      
	$smarty -> assign ("Message", YOU_DELETE." ".$number." ".INACTIVE." ".$number2." ".NEVER_LOGGED.".");
      }

    /**
     * Send email to all players
     */
    elseif ($_GET['view'] == 'mail') 
      {
	$smarty -> assign(array("Mailinfo" => MAIL_INFO,
				"Asend" => A_SEND));
	if (isset ($_GET['step']) && $_GET['step'] == 'send') 
	  {
	    $mail1 = $db -> Execute("SELECT email FROM players");
	    $adress = '';
	    $message = $_POST['message'];        
	    require_once('mailer/mailerconfig.php');                
	    while (!$mail1 -> EOF) 
	      {
		$mail -> AddAddress($mail1 -> fields['email']);
		require_once("languages/".$player -> lang."/admin1.php");
		$subject = M_SUBJECT." ".$gamename;
		if (!$mail -> Send()) 
		  {
		    error(M_ERROR."<br /> ".$mail -> ErrorInfo);
		  }             
		$mail1 -> MoveNext();
		$mail->ClearAddresses();            
	      }       
	    $mail1 -> Close();
	    error (M_SEND);
	  }
      }

    /**
     * Add question on bridge of death
     */
    elseif ($_GET['view'] == 'bridge') 
      {
	$smarty -> assign(array("Bquestion" => B_QUESTION,
				"Banswer" => B_ANSWER,
				"Aadd" => A_ADD));
	if (isset ($_GET['step']) && $_GET['step'] == 'add') 
	  {
	    $strQuestion = $db -> qstr($_POST['question'], get_magic_quotes_gpc());
	    $strAnswer = $db -> qstr($_POST['answer'], get_magic_quotes_gpc());
	    $db -> Execute("INSERT INTO `bridge` (`question`, `answer`) VALUES(".$strQuestion.", ".$strAnswer.")") or error (E_DB);
	    error (YOU_ADD_Q." <b>".$_POST['question']."</b> z odpowiedzią <b>".$_POST['answer']."</b>.");
	  }
      }
    
    /**
     * Delete player
     */
    elseif ($_GET['view'] == 'del') 
      {
	$smarty -> assign(array("Deleteid" => DELETE_ID,
				"Adeletepl" => A_DELETE_PL));
	if (isset ($_GET['step']) && $_GET['step'] == 'del') 
	  {
	    if ($_POST['did'] != 1) 
	      {
		$objAvatar = $db -> Execute("SELECT `avatar` FROM `players` WHERE `id`=".$_POST['did']);
		$strFile = 'avatars/'.$objAvatar -> fields['avatar'];
		if (is_file($strFile)) 
		  {
		    unlink($strFile);
		  }
		$objAvatar -> Close();
		$db -> Execute("DELETE FROM `players` WHERE `id`=".$_POST['did']);
		$db -> Execute("DELETE FROM `core` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `core_market` WHERE `seller`=".$_POST['did']);
		$db -> Execute("DELETE FROM `equipment` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `smith` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `log` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `mail` WHERE `owner`=".$_POST['did']);
		$objOutid = $db -> Execute("SELECT `id` FROM `outposts` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `outpost_mosters` WHERE `outpost`=".$objOutid -> fields['id']);
		$db -> Execute("DELETE FROM `outpost_veterans` WHERE `outpost`=".$objOutid -> fields['id']);
		$objOutid -> Close();
		$db -> Execute("DELETE FROM `outposts` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `pmarket` WHERE `seller`=".$_POST['did']);
		$db -> Execute("DELETE FROM `hmarket` WHERE `seller`=".$_POST['did']);
		$db -> Execute("DELETE FROM `potions` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `herbs` WHERE `gracz`=".$_POST['did']);
		$db -> Execute("DELETE FROM `minerals` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `alchemy_mill` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `czary` WHERE `gracz`=".$_POST['did']);
		$db -> Execute("DELETE FROM `smith_work` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `notatnik` WHERE `gracz`=".$_POST['did']);
		$db -> Execute("DELETE FROM `tribe_oczek` WHERE `gracz`=".$_POST['did']);
		$objHouse = $db -> Execute("SELECT `locator` FROM `houses` WHERE `owner`=".$_POST['did']);
		if ($objHouse -> fields['locator'])
		  {
		    $db -> Execute("UPDATE `houses` SET `owner`=".$objHouse -> fields['locator'].", `locator`=0 WHERE `owner`=".$_POST['did']) or $db -> ErrorMsg();
		    $objHouse -> Close();
		  }
                else
		  {
		    $db -> Execute("DELETE FROM `houses` WHERE `owner`=".$_POST['did']);
		  }
		$db -> Execute("DELETE FROM `farms` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `farm` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `jail` WHERE `prisoner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `mill_work` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `mill` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `questaction` WHERE `player`=".$_POST['did']);
		$db -> Execute("DELETE FROM `amarket` WHERE `seller`=".$_POST['did']);
		$db -> Execute("DELETE FROM `astral` WHERE `owner`=".$_POST['did']." AND `location`='V'");
		$db -> Execute("DELETE FROM `astral_bank` WHERE `owner`=".$_POST['did']." AND `location`='V'");
		$db -> Execute("DELETE FROM `astral_plans` WHERE `owner`=".$_POST['did']." AND `location`='V'");
		$db -> Execute("DELETE FROM `lost_pass` WHERE `id`=".$_POST['did']);
		$db -> Execute("DELETE FROM `ban` WHERE `type`='ID' AND `amount`='".$_POST['did']."'");
		$db -> Execute("DELETE FROM `jeweller` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `jeweller_work` WHERE `owner`=".$_POST['did']);
		$db -> Execute("DELETE FROM `ban_mail` WHERE `id`=".$_POST['did']);
		$db -> Execute("DELETE FROM `links` WHERE `owner`=".$_POST['did']);
		$db->Execute("DELETE FROM `revent` WHERE `pid`=".$_POST['did']);
		$db->Execute("DELETE FROM `contacts` WHERE `owner`=".$_POST['did']);
		$db->Execute("DELETE FROM `contacts` WHERE `pid`=".$_POST['did']);
		$db->Execute("DELETE FROM `ignored` WHERE `owner`=".$_POST['did']);
		$db->Execute("DELETE FROM `ignored` WHERE `pid`=".$_POST['did']);
		$smarty -> assign ("Message", YOU_DELETE2." ".$_POST['did']);
	      } 
            else 
	      {
		$smarty -> assign ("Message", YOU_NOT_D);
	      }
	  }
      }

    /**
     * Change player rank
     */
    elseif ($_GET['view'] == 'add') 
      {
	$smarty -> assign(array("Addid" => ADD_ID,
				"Newrank" => NEW_RANK,
				"Rmember" => R_MEMBER,
				"Rking" => R_KING,
				"Rstaff" => R_STAFF,
				"Rjudge" => R_JUDGE,
				"Rjudge2" => R_JUDGE2,
				"Rjudge3" => R_JUDGE3,
				"Rlawyer" => R_LAWYER,
				"Rbeggar" => R_BEGGAR,
				"Rbarbarian" => R_BARBARIAN,
				"Rscribe" => R_SCRIBE,
				"Rknight" => R_KNIGHT,
				"Rlady" => R_LADY,
				"Rcount" => R_COUNT,
				"Rcount2" => R_COUNT2,
				"Rredactor" => R_REDACTOR,
				"Rinnkeeper" => R_INNKEEPER,
				"Rprocurator" => R_PROCURATOR,
				"Aadd" => A_ADD));
	if (isset ($_GET['step']) && $_GET['step'] == 'add') 
	  {
	    if ($_POST['aid'] != 1) 
	      {
		$strRank = $db -> qstr($_POST['rank'], get_magic_quotes_gpc());
		$db -> Execute("UPDATE `players` SET `rank`=".$strRank." WHERE `id`=".$_POST['aid']);
		error (YOU_ADD_R." ".$_POST['aid']." ".NEW_RANK." ".$_POST['rank'].".");
	      }
	  }
      }

    /**
     * Give player unique rank
     */
    elseif ($_GET['view'] == 'srank')
      {
	$smarty->assign(array("Aadd" => "Nadaj",
			      "Newrank" => "rangę",
			      "Plid" => "graczowi o ID"));
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    checkvalue($_POST['aid']);
	    if ($_POST['aid'] != 1)
	      {
		$strRank = $db -> qstr($_POST['rank'], get_magic_quotes_gpc());
		$db -> Execute("UPDATE `players` SET `rank`=".$strRank." WHERE `id`=".$_POST['aid']);
		error("Nadałeś graczowi o ID: ".$_POST['aid']." rangę: ".$_POST['rank'].".");
	      }
	  }
      }

    /**
     * Prune forums
     */
    elseif ($_GET['view'] == 'clearf')
      {
	if (!isset($_GET['step']))
	  {
	    $smarty -> assign(array("Fquestion" => F_QUESTION,
				    "Ayes" => YES));
	  }
	if (isset($_GET['step']) && $_GET['step'] == 'Y')
	  {
	    $db -> Execute("DELETE FROM topics");
	    $db -> Execute("DELETE FROM replies");
	    error (FORUM_PRUNE);
	  }
      }

    /**
     * Add new items
     */
    elseif ($_GET['view'] == 'equipment') 
      {
	$smarty -> assign(array("Itemname" => ITEM_NAME,
				"Hasa" => HAS_A,
				"Iweapon" => I_WEAPON,
				"Iarmor" => I_ARMOR,
				"Ihelmet" => I_HELMET,
				"Ilegs" => I_LEGS,
				"Ibow" => I_BOW,
				"Ishield" => I_SHIELD,
				"Iarrows" => I_ARROWS,
				"Istaff" => I_STAFF,
				"Icape" => I_CAPE,
				"Aadd" => A_ADD,
				"Iwith" => I_WITH,
				"Ipower" => I_POWER,
				"Icost" => I_COST,
				"Iminlev" => I_MIN_LEV,
				"Iagi" => I_AGI,
				"Ispeed" => I_SPEED,
				"Irepair" => I_REPAIR,
				"Idur" => I_DUR));
	if (isset ($_GET['step']) && $_GET['step'] == 'add') 
	  {
	    if (empty ($_POST['name']) || empty ($_POST['cost'])) 
	      {
		error (EMPTY_FIELDS);
	      }
	    if (empty($_POST['zr'])) 
	      {
		$_POST['zr'] = 0;
	      }
	    if (empty($_POST['szyb'])) 
	      {
		$_POST['szyb'] = 0;
	      }
	    $strName = $db -> qstr($_POST['name'], get_magic_quotes_gpc());
	    if ($_POST['type'] != 'B' && $_POST['type'] != 'R' && $_POST['type'] != 'T' && $_POST['type'] != 'C') 
	      {
		$sql = "INSERT INTO equipment ( id , owner , name , power , status , type , cost , minlev, zr, szyb, wt, maxwt, repair ) VALUES ( '', '0', ".$strName.", '".$_POST['power']."', 'S', '".$_POST['type']."', '".$_POST['cost']."', '".$_POST['minlev']."', '".$_POST['zr']."', '".$_POST['szyb']."', '".$_POST['maxwt']."', '".$_POST['maxwt']."', ".$_POST['repair']." )";
	      }
	    if ($_POST['type'] == 'B' || $_POST['type'] == 'R') 
	      {
		$sql = "INSERT INTO bows (name, power, type, cost, minlev, zr, szyb, maxwt, repair) VALUES(".$strName.", '".$_POST['power']."', '".$_POST['type']."', '".$_POST['cost']."', '".$_POST['minlev']."', '".$_POST['zr']."', '".$_POST['szyb']."', '".$_POST['maxwt']."', ".$_POST['repair'].")";
	      }
	    if ($_POST['type'] == 'T' || $_POST['type'] == 'C') 
	      {
		$sql = "INSERT INTO mage_items (id, name, power, type, cost, minlev) VALUES('',".$strName.", '".$_POST['power']."', '".$_POST['type']."', '".$_POST['cost']."', '".$_POST['minlev']."')";
	      }
	    $db -> Execute($sql) or die($db -> ErrorMsg());
	    error (YOU_ADD_ITEM." ".$_POST['name']." ".HAS_A." ".$_POST['type']." ".POWER." ".$_POST['power']." ".COST." ".$_POST['cost']." ".MIN_LEVEL." ".$_POST['minlev']." ".ITEM_LEVEL." ".$_POST['zr']." % ".ITEM_SPEED." ".$_POST['zr']." % ".ITEM_DUR." ".$_POST['maxwt']." .");
	  }
      }

    /**
     * Player donation
     */
    elseif ($_GET['view'] == 'donate') 
      {
	$smarty -> assign(array("Donateid" => DONATE_ID,
				"Donateamount" => DONATE_AMOUNT,
				"Adonate" => A_DONATE));
	if (isset ($_GET['step']) && $_GET['step'] == 'donated') 
	  {
	    checkvalue($_POST['amount']);
	    $db -> Execute("UPDATE players SET credits=credits+".$_POST['amount']." WHERE id=".$_POST['id']);
	    error (YOU_SEND_M);
	  }
      }

    /**
     * Add new monsters
     */
    elseif ($_GET['view'] == 'monster') 
      {
	$smarty -> assign(array("Mname" => M_NAME,
				"Mlevel" => M_LEVEL,
				"Mhp" => M_HP,
				"Magi" => M_AGI,
				"Mpower" => M_POWER,
				"Mspeed" => M_SPEED,
				"Mcond" => M_COND,
				"Mmingold" => M_MIN_GOLD,
				"Mmaxgold" => M_MAX_GOLD,
				"Mminexp" => M_MIN_EXP,
				"Mmaxexp" => M_MAX_EXP,
				"Aadd" => A_ADD,
				"Mlocation" => M_LOCATION,
				"Mcity1" => M_CITY1,
				"Mcity2" => M_CITY2,
				"Mcity3" => M_CITY3,
				"Mlootnames" => "Nazwy komponentów",
				"Mlootchances" => "Szanse na komponenty",));
	if (isset ($_GET['step']) && $_GET['step'] == 'monster') 
	  {
	    if (!$_POST['nazwa'] || !$_POST['poziom'] || !$_POST['pz'] || !$_POST['zr'] || !$_POST['sila'] || !$_POST['minzl'] || !$_POST['maxzl'] || !$_POST['minpd'] || !$_POST['maxpd'] || !$_POST['speed'] || !$_POST['endurance']) 
	      {
		error (EMPTY_FIELDS);
	      }
	    $strName = $db -> qstr($_POST['nazwa'], get_magic_quotes_gpc());
	    $strLoot = $db -> qstr($_POST['lootnames'], get_magic_quotes_gpc());
	    $strLoot2 = $db -> qstr($_POST['lootchances'], get_magic_quotes_gpc());
	    $db -> Execute("INSERT INTO `monsters` (`name`, `level`, `hp`, `agility`, `strength`, `credits1`, `credits2`, `exp1`, `exp2`, `speed`, `endurance`, `location`, `lootnames`, `lootchances`) VALUES(".$strName.", ".$_POST['poziom'].", ".$_POST['pz'].", ".$_POST['zr'].", ".$_POST['sila'].", ".$_POST['minzl'].", ".$_POST['maxzl'].", ".$_POST['minpd'].", ".$_POST['maxpd'].", ".$_POST['speed'].", ".$_POST['endurance'].", '".$_POST['location']."', ".$strLoot.", ".$strLoot2.")");
	  }
      }

    /**
     * Add new plans in smith
     */
    elseif ($_GET['view'] == 'kowal') 
      {
	$smarty -> assign(array("Sname" => S_NAME,
				"Scost" => S_COST,
				"Samount" => S_AMOUNT,
				"Stwohand" => S_TWOHAND,
				"Ayes" => YES,
				"Ano" => NO,
				"Slevel" => S_LEVEL,
				"Stype" => S_TYPE,
				"Sweapon" => S_WEAPON,
				"Sarmor" => S_ARMOR,
				"Shelmet" => S_HELMET,
				"Sshield" => S_SHIELD,
				"Slegs" => S_LEGS,
				"Stwohand" => S_TWOHAND,
				"Aadd" => A_ADD,
				"Selite" => "Elitarny",
				"Selitetype" => "Elitarny typu",
				"Dragon" => "Smoczy",
				"Elven" => "Elfi"));
	if (isset ($_GET['step']) && $_GET['step'] == 'kowal') 
	  {
	    if (!$_POST['nazwa'] || !$_POST['cena'] || !$_POST['poziom']) 
	      {
		error (EMPTY_FIELDS);
	      }
	    $strName = $db -> qstr($_POST['nazwa'], get_magic_quotes_gpc());
	    $_POST['elite'] = intval($_POST['elite']);
	    $db -> Execute("INSERT INTO `smith` (`name`, `cost`, `level`, `amount`, `type`, `twohand`, `elite`, `elitetype`) VALUES(".$strName.", ".$_POST['cena'].", ".$_POST['poziom'].", ".$_POST['amount'].", '".$_POST['type']."', '".$_POST['twohand']."', ".$_POST['elite'].", '".$_POST['elitetype']."')");
	  }
      }

    /**
     * Send message to all players
     */
    elseif ($_GET['view'] == 'poczta') 
      {
	$smarty -> assign(array("Pmsubject" => PM_SUBJECT,
				"Pmbody" => PM_BODY,
				"Asend" => A_SEND));
	if (isset ($_GET['step']) && $_GET['step'] == 'send') 
	  {
	    if (empty ($_POST['body']) || empty($_POST['subject'])) 
	      {
		error (EMPTY_FIELDS);
	      }
	    $_POST['subject'] = strip_tags($_POST['subject']);
	    $_POST['body'] = strip_tags($_POST['body']);
	    $strSubject = $db -> qstr($_POST['subject'], get_magic_quotes_gpc());
	    $strBody = $db -> qstr($_POST['body'], get_magic_quotes_gpc());
	    $strDate = $db -> DBDate($newdate);
	    $odbio = $db -> Execute("SELECT id FROM players");
	    $gracze = 0;
	    while (!$odbio -> EOF) 
	      {
		$db -> Execute("INSERT INTO mail (sender, senderid, owner, subject, body, date) VALUES('".$player -> user."','".$player -> id."',".$odbio -> fields['id'].", ".$strSubject.", ".$strBody.", ".$strDate.")") or error(E_DB);
		$gracze = $gracze + 1;
		$odbio -> MoveNext();
	      }
	    $odbio -> Close();
	    error (YOU_SEND_PM." ".$gracze." ".PLAYERS_A);
	  }
      }

    /**
     * Add new spells
     */
    elseif ($_GET['view'] == 'czary') 
      {
	$smarty -> assign(array("Spellname" => SPELL_NAME,
				"Swith" => S_WITH,
				"Sbattle" => S_BATTLE,
				"Sdefense" => S_DEFENSE,
				"Scost" => S_COST,
				"Spower" => S_POWER,
				"Sminlev" => S_MIN_LEV,
				"Hasas" => HAS_A_S,
				"Aadd" => A_ADD));
	if (isset ($_GET['step']) && $_GET['step'] == 'add') 
	  {
	    if (empty($_POST['name']) || empty($_POST['power']) || empty($_POST['cost']) || empty($_POST['minlev'])) 
	      {
		error (EMPTY_FIELDS);
	      }
	    $strName = $db -> qstr($_POST['name'], get_magic_quotes_gpc());
	    $db -> Execute("INSERT INTO czary (nazwa, cena, poziom, typ, obr) VALUES(".$strName.", ".$_POST['cost'].", ".$_POST['minlev'].", '".$_POST['type']."', ".$_POST['power'].")");
	    error (YOU_ADD_SPELL." ".$_POST['name']." ".HAS_A_S." ".$_POST['type']." ".POWER_S." ".$_POST['power']." ".COST." ".$_POST['cost']." ".MIN_LEV_S." ".$_POST['minlev']);
	  }
      }

    /**
     * Add new potions
     */
    elseif ($_GET['view'] == 'potions')
      {
	$smarty->assign(array("Pname" => "Nazwa mikstury",
			      "Poison" => "Trucizna",
			      "Antidote" => "Antidotum",
			      "Healing" => "Lecząca zdrowie",
			      "Mana" => "Uzupełnienie many",
			      "Tefect" => "Efekt",
			      "Ppower" => "Moc",
			      "Aadd" => "Dodaj"));
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    if (empty($_POST['name']) || empty($_POST['type']) || empty($_POST['effect']) || empty($_POST['power']))
	      {
		error("Wypełnij wszystkie pola");
	      }
	    if (!in_array($_POST['type'], array('P', 'A', 'H', 'M')))
	      {
		error(ERROR);
	      }
	    $strName = $db -> qstr($_POST['name'], get_magic_quotes_gpc());
	    $strEffect = $db -> qstr($_POST['effect'], get_magic_quotes_gpc());
	    $intPower = intval($_POST['power']);
	    $db->Execute("INSERT INTO `potions` (`name`, `type`, `efect`, `power`, `status`) VALUES(".$strName.", '".$_POST['type']."', ".$strEffect.", ".$intPower.", 'A')") or die($db->ErrorMsg());
	  }
      }

    /**
     * Add new plan in alchemy
     */
    elseif ($_GET['view'] == 'alchemy')
      {
	$smarty->assign(array("Pname" => "Nazwa planu",
			      "Therb1" => "Illani",
			      "Therb2" => "Illanias",
			      "Therb3" => "Nutari",
			      "Therb4" => "Dynallca",
			      "Tlevel" => "Poziom",
			      "Tcost" => "Cena",
			      "Aadd" => "Dodaj"));
	if (isset($_GET['step']) && $_GET['step'] == 'add')
	  {
	    if (empty($_POST['name']) || empty($_POST['illani']) || empty($_POST['illanias']) || empty($_POST['nutari']) || empty($_POST['dynallca']) || empty($_POST['level']) || empty($_POST['cost']))
	      {
		error("Wypełnij wszystkie pola.");
	      }
	    $strName = $db -> qstr($_POST['name'], get_magic_quotes_gpc());
	    $arrForm = array('illani', 'illanias', 'nutari', 'dynallca', 'level', 'cost');
	    foreach ($arrForm as $strKey)
	      {
		$_POST[$strKey] = intval($_POST[$strKey]);
		if ($_POST[$strKey] < 0)
		  {
		    error(ERROR);
		  }
	      }
	    $db->Execute("INSERT INTO `alchemy_mill` (`name`, `illani`, `illanias`, `nutari`, `dynallca`, `level`, `cost`) VALUES(".$strName.", ".$_POST['illani'].", ".$_POST['illanias'].", ".$_POST['nutari'].", ".$_POST['dynallca'].", ".$_POST['level'].", ".$_POST['cost'].")") or die($db->ErrorMsg());
	  }
      }

    /**
     * Close/open game
     */
    elseif ($_GET['view'] == 'close') 
      {
	$smarty -> assign(array("Gopen" => G_OPEN,
				"Gclose" => G_CLOSE,
				"Ifclose" => IF_CLOSE,
				"Amake" => A_MAKE));
	if (isset ($_GET['step']) && $_GET['step'] == 'close') 
	  {
	    if ($_POST['close'] == 'close') 
	      {
		$strReason = $db -> qstr($_POST['reason'], get_magic_quotes_gpc());
		$db -> Execute("UPDATE settings SET value='N' WHERE setting='open'");
		$db -> Execute("UPDATE settings SET value=".$strReason." WHERE setting='close_reason'");
		error (YOU_CLOSE);
	      }
	    if ($_POST['close'] == 'open') 
	      {
		$db -> Execute("UPDATE settings SET value='Y' WHERE setting='open'");
		$db -> Execute("UPDATE settings SET value='' WHERE setting='close_reason'");
		error (YOU_OPEN);
	      }
	  }
      }
    /**
     * Description proposals
     */
    elseif ($_GET['view'] == 'pdescriptions')
      {
	//Show list
	$arrProposals = $db->GetAll("SELECT `id`, `pid`, `name` FROM `proposals` WHERE `type`='D'");
	$smarty->assign(array("Tid" => "ID",
			      "Treporter" => "Zgłaszający",
			      "Tlocation" => "Lokacja",
			      "Proposals" => $arrProposals));
	if (isset($_GET['step']))
	  {
	    checkvalue($_GET['step']);
	    $objProposal = $db->Execute("SELECT `pid`, `name`, `data`, `info` FROM `proposals` WHERE `id`=".$_GET['step']);
	    $smarty->assign(array("Tdesc" => "Opis:",
				  "Tinfo" => "Informacje:",
				  "Desc" => $objProposal->fields['data'],
				  "Info" => $objProposal->fields['info'],
				  "Tloc" => "Lokacja",
				  "Location" => $objProposal->fields['name'],
				  "Asend" => "Wyślij",
				  "Accepted" => "Zaakceptowany",
				  "Rejected" => "Odrzucony",
				  "Tvallars" => "Ilość vallarów",
				  "Treason" => "Przyczyna"));
	    if (isset($_GET['confirm']))
	      {
		$strMessage = 'Twój opis lokacji '.$objProposal->fields['name'].' został ';
		$strDate = $db -> DBDate($newdate);
		if ($_POST['response'] == 'A')
		  {
		    $strMessage .= 'zaakceptowany. Dostałeś za to '.$_POST['valars'].' Vallara(y).';
		    $strAuthor = '<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id.'</b>';
		    $db -> Execute("INSERT INTO `changelog` (`author`, `location`, `text`, `date`, `lang`) VALUES('".$strAuthor."', '".$objProposal->fields['name']."', 'Nowy opis lokacji autorstwa ID: ".$objProposal->fields['pid']."', ".$strDate.", 'pl')");
		    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+".$_POST['valars']." WHERE `id`=".$objProposal->fields['pid']);
		    $db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$objProposal->fields['pid'].", ".$_POST['valars'].", 'Opis lokacji.')");
		    $strResult = "Zaakceptowałeś opis";
		  }
		else
		  {
		    $strMessage .= 'odrzucony. Przyczyna: '.$_POST['reason'];
		    $strResult = "Odrzuciłeś opis.";
		  }
		$db->Execute("DELETE FROM `proposals` WHERE `id`=".$_GET['step']);
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objProposal->fields['pid'].", '".$strMessage."', ".$strDate.", 'A')") or die($db->ErrorMsg());
		$smarty->assign("Message", $strResult);
	      }
	    $objProposal->Close();
	  }
      }
    /**
     * Items proposals
     */
    elseif ($_GET['view'] == 'pitems')
      {
	//Show list
	$arrProposals = $db->GetAll("SELECT `id`, `pid`, `name` FROM `proposals` WHERE `type`='I'");
	$smarty->assign(array("Tid" => "ID",
			      "Treporter" => "Zgłaszający",
			      "Tlocation" => "Nazwa",
			      "Proposals" => $arrProposals));
	if (isset($_GET['step']))
	  {
	    checkvalue($_GET['step']);
	    $objProposal = $db->Execute("SELECT `pid`, `name`, `data`, `info` FROM `proposals` WHERE `id`=".$_GET['step']);
	    $smarty->assign(array("Tdesc" => "Typ:",
				  "Tinfo" => "Poziom:",
				  "Desc" => $objProposal->fields['data'],
				  "Info" => $objProposal->fields['info'],
				  "Tloc" => "Nazwa:",
				  "Location" => $objProposal->fields['name'],
				  "Asend" => "Wyślij",
				  "Accepted" => "Zaakceptowany",
				  "Rejected" => "Odrzucony",
				  "Treason" => "Przyczyna"));
	    if (isset($_GET['confirm']))
	      {
		$strMessage = 'Twój przedmiot '.$objProposal->fields['name'].' został ';
		$strDate = $db -> DBDate($newdate);
		if ($_POST['response'] == 'A')
		  {
		    $strMessage .= 'zaakceptowany. Dostałeś za to 1 Vallara.';
		    $strAuthor = '<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id.'</b>';
		    $db -> Execute("INSERT INTO `changelog` (`author`, `location`, `text`, `date`, `lang`) VALUES('".$strAuthor."', 'Ogólnie', 'Nowy przedmiot autorstwa ID: ".$objProposal->fields['pid']."', ".$strDate.", 'pl')");
		    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+1 WHERE `id`=".$objProposal->fields['pid']);
		    $db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$objProposal->fields['pid'].", 1, 'Nowy przedmiot.')");
		    $strResult = "Zaakceptowałeś przedmiot";
		    //Item stats
		    $arrItem = array('name' => $objProposal->fields['name'],
				     'minlev' => $objProposal->fields['info'],
				     'twohand' => 'N',
				     'type' => $objProposal->fields['data'],
				     'power' => $objProposal->fields['info'],
				     'speed' => 0,
				     'zr' => 0,
				     'wt' => 40,
				     'cost' => 0,
				     'repaircost' => $objProposal->fields['info'] * 2,
				     'materials' => 0,
				     'plancost' => 0);
		    switch ($objProposal->fields['data'])
		      {
		      case 'W2':
			$arrItem['type'] = 'W';
			$arrItem['power'] = floor($objProposal->fields['info'] * 1.25);
			$arrItem['twohand'] = 'Y';
			break;
		      case 'B':
			$arrItem['twohand'] = 'Y';
			$arrItem['power'] = 0;
			$arrItem['speed'] = $objProposal->fields['info'];
			break;
		      case 'R':
			$arrItem['wt'] = 20;
			$arrItem['repaircost'] = 0;
			break;
		      case 'A':
			$arrItem['zr'] = floor($objProposal->fields['info'] / 2);
			$arrItem['power'] = $objProposal->fields['info'] * 3;
			break;
		      case 'L':
			$arrItem['zr'] = floor($objProposal->fields['info'] / 3);
			$arrItem['wt'] = 20;
			break;
		      case 'H':
		      case 'S':
			$arrItem['wt'] = 20;
			break;
		      default:
			break;
		      }
		    //Count price and materials for plans
		    if ($arrItem['type'] != 'B' && $arrItem['type'] != 'R')
		      {
			$objMinlev = $db->Execute("SELECT max(`minlev`) FROM `equipment` WHERE `owner`=0 AND `type`='".$arrItem['type']."' AND `minlev`<=".$arrItem['minlev']);
			$objMaxlev = $db->Execute("SELECT min(`minlev`) FROM `equipment` WHERE `owner`=0 AND `type`='".$arrItem['type']."' AND `minlev`>=".$arrItem['minlev']);
			$objCost = $db->Execute("SELECT `cost` FROM `equipment` WHERE `owner`=0 AND `type`='".$arrItem['type']."' AND `minlev`=".$objMaxlev->fields['min(`minlev`)']);
			$objMats = $db->Execute("SELECT `amount`, `cost` FROM `smith` WHERE `owner`=0 AND `type`='".$arrItem['type']."' AND `level`=".$objMaxlev->fields['min(`minlev`)']);
		      }
		    else
		      {
			$objMinlev = $db->Execute("SELECT max(`minlev`) FROM `bows` WHERE `type`='".$arrItem['type']."' AND `minlev`<=".$arrItem['minlev']);
			$objMaxlev = $db->Execute("SELECT min(`minlev`) FROM `bows` WHERE `type`='".$arrItem['type']."' AND `minlev`>=".$arrItem['minlev']);
			$objCost = $db->Execute("SELECT `cost` FROM `bows` WHERE `type`='".$arrItem['type']."' AND `minlev`=".$objMaxlev->fields['min(`minlev`)']);
			$objMats = $db->Execute("SELECT `amount`, `cost` FROM `mill` WHERE `owner`=0 AND `type`='".$arrItem['type']."' AND `level`=".$objMaxlev->fields['min(`minlev`)']);
		      }
		    $arrItem['cost'] = ceil(($objMinlev->fields['max(`minlev`)'] / $objMaxlev->fields['min(`minlev`)']) * $objCost->fields['cost']);
		    $arrItem['materials'] = ceil(($objMinlev->fields['max(`minlev`)'] / $objMaxlev->fields['min(`minlev`)']) * $objMats->fields['amount']);
		    $arrItem['plancost'] = ceil(($objMinlev->fields['max(`minlev`)'] / $objMaxlev->fields['min(`minlev`)']) * $objMats->fields['cost']);
		    $objMinlev->Close();
		    $objMaxlev->Close();
		    $objCost->Close();
		    $objMats->Close();
		    //Add items and plans
		    if ($arrItem['type'] != 'B' && $arrItem['type'] != 'R')
		      {
			$db->Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `zr`, `wt`, `szyb`, `maxwt`, `twohand`, `repair`) VALUES(0, '".$arrItem['name']." z miedzi', ".$arrItem['power'].", 'S', '".$arrItem['type']."', ".$arrItem['cost'].", ".$arrItem['minlev'].", ".$arrItem['zr'].", ".$arrItem['wt'].", ".$arrItem['speed'].", ".$arrItem['wt'].", '".$arrItem['twohand']."', ".$arrItem['repaircost'].")") or die($db->ErrorMsg());
			$db->Execute("INSERT INTO `smith` (`owner`, `name`, `type`, `cost`, `amount`, `level`, `twohand`) VALUES(0, '".$arrItem['name']."', '".$arrItem['type']."', ".$arrItem['plancost'].", ".$arrItem['materials'].", ".$arrItem['minlev'].", '".$arrItem['twohand']."')") or die($db->ErrorMsg());
		      }
		    else
		      {
			if ($arrItem['type'] == 'B')
			  {
			    $strName = $arrItem['name']." z leszczyny";
			  }
			else
			  {
			    $strName = $arrItem['name'];
			  }
			$db->Execute("INSERT INTO `bows` (`name`, `power`, `type`, `cost, `minlev`, `zr`, `szyb`, `maxwt`, `reapair`) VALUES(0, '".$strName."', ".$arrItem['power'].", '".$arrItem['type']."', ".$arrItem['cost'].", ".$arrItem['minlev'].", ".$arrItem['zr'].", ".$arrItem['speed'].", ".$arrItem['wt'].", ".$arrItem['repaircost'].")") or die("error3");
			$db->Execute("INSERT INTO `mill` (`owner`, `name`, `type`, `cost`, `amount`, `level`, `twohand`) VALUES(0, '".$arrItem['name']."', '".$arrItem['type']."', ".$arrItem['plancost'].", ".$arrItem['materials'].", ".$arrItem['minlev'].", '".$arrItem['twohand']."')") or die("error4");
		      }
		  }
		else
		  {
		    $strMessage .= 'odrzucony. Przyczyna: '.$_POST['reason'];
		    $strResult = "Odrzuciłeś przedmiot.";
		  }
		$db->Execute("DELETE FROM `proposals` WHERE `id`=".$_GET['step']);
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objProposal->fields['pid'].", '".$strMessage."', ".$strDate.", 'A')") or die($db->ErrorMsg());
		$smarty->assign("Message", $strResult);
	      }
	    $objProposal->Close();
	  }
      }
    /**
     * Monsters proposals
     */
    elseif ($_GET['view'] == 'pmonsters')
      {
	//Show list
	$arrProposals = $db->GetAll("SELECT `id`, `pid`, `name` FROM `proposals` WHERE `type`='M'");
	$smarty->assign(array("Tid" => "ID",
			      "Treporter" => "Zgłaszający",
			      "Tlocation" => "Nazwa",
			      "Proposals" => $arrProposals));
	if (isset($_GET['step']))
	  {
	    checkvalue($_GET['step']);
	    $objProposal = $db->Execute("SELECT `pid`, `name`, `data`, `info` FROM `proposals` WHERE `id`=".$_GET['step']);
	    $arrLocations = array($city1, $city2);
	    $arrStats = array("Wysoka", "Normalna", "Niska");
	    $arrLoots = array("Dużo", "Normalnie", "Mało");
	    $arrData = explode(';', $objProposal->fields['data']);
	    $smarty->assign(array("Tname" => "Nazwa:",
				  "Name" => $objProposal->fields['name'],
				  "Tloc" => "Lokacja:",
				  "Loc" => $arrLocations[$objProposal->fields['info']],
				  "Tstr" => "Siła:",
				  "Str" => $arrStats[$arrData[0]],
				  "Tagi" => "Zręczność:",
				  "Agi" => $arrStats[$arrData[1]],
				  "Tspeed" => "Szybkość:",
				  "Speed" => $arrStats[$arrData[2]],
				  "Tcon" => "Wytrzymałość:",
				  "Con" => $arrStats[$arrData[3]],
				  "Tgold" => "Złota:",
				  "Mgold" => $arrLoots[$arrData[4]],
				  "Texp" => "Doświadczenie:",
				  "Mexp" => $arrLoots[$arrData[5]],
				  "Tlevel" => "Poziom:",
				  "Mlevel" => $arrData[6],
				  "Tloot1" => "Łup 1:",
				  "Loot1" => $arrData[7],
				  "Tloot2" => "Łup 2:",
				  "Loot2" => $arrData[8],
				  "Tloot3" => "Łup 3:",
				  "Loot3" => $arrData[9],
				  "Tloot4" => "Łup 4:",
				  "Loot4" => $arrData[10],
				  "Asend" => "Wyślij",
				  "Accepted" => "Zaakceptowany",
				  "Rejected" => "Odrzucony",
				  "Treason" => "Przyczyna"));
	    if (isset($_GET['confirm']))
	      {
		$strMessage = 'Twój potwór '.$objProposal->fields['name'].' został ';
		$strDate = $db -> DBDate($newdate);
		if ($_POST['response'] == 'A')
		  {
		    $strMessage .= 'zaakceptowany. Dostałeś za to 1 Vallara.';
		    $strAuthor = '<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id.'</b>';
		    $db -> Execute("INSERT INTO `changelog` (`author`, `location`, `text`, `date`, `lang`) VALUES('".$strAuthor."', 'Ogólnie', 'Nowy potwór autorstwa ID: ".$objProposal->fields['pid']."', ".$strDate.", 'pl')");
		    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+1 WHERE `id`=".$objProposal->fields['pid']);
		    $db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$objProposal->fields['pid'].", 1, 'Nowy potwór na arenie.')");
		    $strResult = "Zaakceptowałeś potwora";
		    //Monster stats
		    $objMinlev = $db->Execute("SELECT max(`level`) FROM `monsters` WHERE `level`<=".$arrData[6]." AND `location`='".$arrLocations[$objProposal->fields['info']]."'");
		    $objMaxlev = $db->Execute("SELECT min(`level`) FROM `monsters` WHERE `level`>=".$arrData[6]." AND `location`='".$arrLocations[$objProposal->fields['info']]."'");
		    $objStats = $db->Execute("SELECT * FROM `monsters` WHERE `level`=".$objMaxlev->fields['min(`level`)']." AND `location`='".$arrLocations[$objProposal->fields['info']]."'");
		    $objHp = $db->Execute("SELECT `hp` FROM `monsters` WHERE `level`=".$objMinlev->fields['max(`level`)']." AND `location`='".$arrLocations[$objProposal->fields['info']]."'");
		    $arrMob = array("str" => 0,
				    "agi" => 0,
				    "speed" => 0,
				    "con" => 0,
				    "hp" => 0,
				    "gold1" => 0,
				    "gold2" => 0,
				    "exp1" => 0,
				    "exp2" => 0);
		    $fltFraction = ($objMinlev->fields['max(`level`)'] / $objMaxlev->fields['min(`level`)']);
		    $arrMob['str'] = ceil($fltFraction * $objStats->fields['strength']);
		    if ($arrData[0] == 0)
		      {
			$arrMob['str'] += ceil($arrMob['str'] / 10);
		      }
		    elseif ($arrData[0] == 2)
		      {
			$arrMob['str'] -= ceil($arrMob['str'] / 10);
		      }
		    $arrMob['agi'] = ceil($fltFraction * $objStats->fields['agility']);
		    if ($arrData[1] == 0)
		      {
			$arrMob['agi'] += ceil($arrMob['agi'] / 10);
		      }
		    elseif ($arrData[1] == 2)
		      {
			$arrMob['agi'] -= ceil($arrMob['agi'] / 10);
		      }
		    $arrMob['speed'] = ceil($fltFraction * $objStats->fields['speed']);
		    if ($arrData[2] == 0)
		      {
			$arrMob['speed'] += ceil($arrMob['speed'] / 10);
		      }
		    elseif ($arrData[2] == 2)
		      {
			$arrMob['speed'] -= ceil($arrMob['speed'] / 10);
		      }
		    $arrMob['con'] = ceil($fltFraction * $objStats->fields['endurance']);
		    if ($arrData[3] == 0)
		      {
			$arrMob['con'] += ceil($arrMob['con'] / 10);
		      }
		    elseif ($arrData[3] == 2)
		      {
			$arrMob['con'] -= ceil($arrMob['con'] / 10);
		      }
		    $arrMob['hp'] = $objHp->fields['hp'] + ceil($fltFraction * ($objStats->fields['hp'] - $objHp->fields['hp']));
		    $arrMob['gold1'] = ceil($fltFraction * $objStats->fields['credits1']);
		    $arrMob['gold2'] = ceil($fltFraction * $objStats->fields['credits2']);
		    if ($arrData[1] == 0)
		      {
			$arrMob['gold1'] += ceil($arrMob['gold1'] / 10);
			$arrMob['gold1'] += ceil($arrMob['gold1'] / 10);
		      }
		    elseif ($arrData[1] == 2)
		      {
			$arrMob['gold1'] -= ceil($arrMob['gold1'] / 10);
			$arrMob['gold2'] -= ceil($arrMob['gold2'] / 10);
		      }
		    $arrMob['exp1'] = ceil($fltFraction * $objStats->fields['exp1']);
		    $arrMob['exp2'] = ceil($fltFraction * $objStats->fields['exp2']);
		    if ($arrData[1] == 0)
		      {
			$arrMob['exp1'] += ceil($arrMob['exp1'] / 10);
			$arrMob['exp2'] += ceil($arrMob['exp2'] / 10);
		      }
		    elseif ($arrData[1] == 2)
		      {
			$arrMob['exp1'] -= ceil($arrMob['exp1'] / 10);
			$arrMob['exp2'] += ceil($arrMob['exp2'] / 10);
		      }
		    $db->Execute("INSERT INTO `monsters` (`name`, `level`, `hp`, `agility`, `strength`, `speed`, `endurance`, `credits1`, `credits2`, `exp1`, `exp2`, `location`, `lootnames`, `lootchances`) VALUES('".$objProposal->fields['name']."', ".$arrData[6].", ".$arrMob['hp'].", ".$arrMob['agi'].", ".$arrMob['str'].", ".$arrMob['speed'].", ".$arrMob['con'].", ".$arrMob['gold1'].", ".$arrMob['gold2'].", ".$arrMob['exp1'].", ".$arrMob['exp2'].", '".$arrLocations[$objProposal->fields['info']]."', '".$arrData[7].";".$arrData[8].";".$arrData[9].";".$arrData[10]."', '55;77;95;100')") or die($db->ErrorMsg());
		  }
		else
		  {
		    $strMessage .= 'odrzucony. Przyczyna: '.$_POST['reason'];
		    $strResult = "Odrzuciłeś potwora.";
		  }
		$db->Execute("DELETE FROM `proposals` WHERE `id`=".$_GET['step']);
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objProposal->fields['pid'].", '".$strMessage."', ".$strDate.", 'A')") or die($db->ErrorMsg());
		$smarty->assign("Message", $strResult);
	      }
	    $objProposal->Close();
	  }
      }
    /**
     * Bridge questions proposals
     */
    elseif ($_GET['view'] == 'pbridge')
      {
	//Show list
	$arrProposals = $db->GetAll("SELECT `id`, `pid`, `name` FROM `proposals` WHERE `type`='B'");
	$smarty->assign(array("Tid" => "ID",
			      "Treporter" => "Zgłaszający",
			      "Tlocation" => "Lokacja",
			      "Proposals" => $arrProposals));
	if (isset($_GET['step']))
	  {
	    checkvalue($_GET['step']);
	    $objProposal = $db->Execute("SELECT `pid`, `name`, `data`, `info` FROM `proposals` WHERE `id`=".$_GET['step']);
	    $smarty->assign(array("Tdesc" => "Pytanie:",
				  "Tinfo" => "Odpowiedź:",
				  "Desc" => $objProposal->fields['data'],
				  "Info" => $objProposal->fields['info'],
				  "Tloc" => "Lokacja",
				  "Location" => $objProposal->fields['name'],
				  "Asend" => "Wyślij",
				  "Accepted" => "Zaakceptowany",
				  "Rejected" => "Odrzucony",
				  "Tvallars" => "Ilość vallarów",
				  "Treason" => "Przyczyna"));
	    if (isset($_GET['confirm']))
	      {
		$strMessage = 'Twoje pytanie na Moście Śmierci zostało ';
		$strDate = $db -> DBDate($newdate);
		if ($_POST['response'] == 'A')
		  {
		    $strMessage .= 'zaakceptowane. Dostałeś za to 1 Vallara.';
		    $strAuthor = '<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id.'</b>';
		    $db -> Execute("INSERT INTO `changelog` (`author`, `location`, `text`, `date`, `lang`) VALUES('".$strAuthor."', 'Most Śmierci', 'Nowe pytanie na moście autorstwa ID: ".$objProposal->fields['pid']."', ".$strDate.", 'pl')");
		    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+1 WHERE `id`=".$objProposal->fields['pid']);
		    $db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$objProposal->fields['pid'].", 1, 'Pytanie na moście śmierci.')");
		    $strResult = "Zaakceptowałeś pytanie";
		    $db->Execute("INSERT INTO `bridge` (`question`, `answer`) VALUES('".$_POST['desc']."', '".$_POST['info']."')");
		  }
		else
		  {
		    $strMessage .= 'odrzucona. Przyczyna: '.$_POST['reason'];
		    $strResult = "Odrzuciłeś pytanie.";
		  }
		$db->Execute("DELETE FROM `proposals` WHERE `id`=".$_GET['step']);
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objProposal->fields['pid'].", '".$strMessage."', ".$strDate.", 'A')") or die($db->ErrorMsg());
		$smarty->assign("Message", $strResult);
	      }
	    $objProposal->Close();
	  }
      }
    /**
     * Monster description proposals
     */
    elseif ($_GET['view'] == 'pmdesc')
      {
	//Show list
	$arrProposals = $db->GetAll("SELECT `id`, `pid`, `name` FROM `proposals` WHERE `type`='E'");
	$smarty->assign(array("Tid" => "ID",
			      "Treporter" => "Zgłaszający",
			      "Tlocation" => "Potwór",
			      "Proposals" => $arrProposals));
	if (isset($_GET['step']))
	  {
	    checkvalue($_GET['step']);
	    $objProposal = $db->Execute("SELECT `pid`, `name`, `data`, `info` FROM `proposals` WHERE `id`=".$_GET['step']);
	    $smarty->assign(array("Tdesc" => "Opis:",
				  "Tinfo" => "ID potwora:",
				  "Desc" => $objProposal->fields['data'],
				  "Info" => $objProposal->fields['info'],
				  "Tloc" => "Potwór:",
				  "Location" => $objProposal->fields['name'],
				  "Asend" => "Wyślij",
				  "Accepted" => "Zaakceptowany",
				  "Rejected" => "Odrzucony",
				  "Tvallars" => "Ilość vallarów",
				  "Treason" => "Przyczyna"));
	    if (isset($_GET['confirm']))
	      {
		$strMessage = 'Twój opis potwora '.$objProposal->fields['name'].' został ';
		$strDate = $db -> DBDate($newdate);
		if ($_POST['response'] == 'A')
		  {
		    $strMessage .= 'zaakceptowany. Dostałeś za to '.$_POST['valars'].' Vallara(y).';
		    $strAuthor = '<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id.'</b>';
		    $db -> Execute("INSERT INTO `changelog` (`author`, `location`, `text`, `date`, `lang`) VALUES('".$strAuthor."', 'Gildia Łowców', 'Nowy opis potwora: ".$objProposal->fields['name']." autorstwa ID: ".$objProposal->fields['pid']."', ".$strDate.", 'pl')");
		    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+".$_POST['valars']." WHERE `id`=".$objProposal->fields['pid']);
		    $db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$objProposal->fields['pid'].", ".$_POST['valars'].", 'Opis potwora ".$objProposal->fields['name'].".')");
		    $db->Execute("UPDATE `monsters` SET `desc`='".$_POST['desc']."' WHERE `id`=".$_POST['info']) or die($db->ErrorMsg());
		    $strResult = "Zaakceptowałeś opis";
		  }
		else
		  {
		    $strMessage .= 'odrzucony. Przyczyna: '.$_POST['reason'];
		    $strResult = "Odrzuciłeś opis.";
		  }
		$db->Execute("DELETE FROM `proposals` WHERE `id`=".$_GET['step']);
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objProposal->fields['pid'].", '".$strMessage."', ".$strDate.", 'A')") or die($db->ErrorMsg());
		$smarty->assign("Message", $strResult);
	      }
	    $objProposal->Close();
	  }
      }
    /**
     * Random mission location
     */
    elseif ($_GET['view'] == 'rmission')
      {
	$smarty->assign(array('Tname' => 'Nazwa lokacji',
			      'Ttext' => 'Wyświetlany tekst',
			      'Texits' => 'Wyjścia z lokacji',
			      'Tchances' => 'Szanse na wystąpienie konkretnego wyjścia',
			      'Tmobs' => 'NPC w lokacji',
			      'Tchances2' => 'Szansa na wystąpienie konkretnego NPC',
			      'Titems' => 'Rzeczy w lokacji',
			      'Tchances3' => 'Szansa na wystąpinie konkretnego przedmiotu',
			      'Aadd' => 'Dodaj'));
	if (isset($_GET['step']))
	  {
	    if (empty($_POST['name']) || empty($_POST['text']) || empty($_POST['exits']) || empty($_POST['chances']))
	      {
		error('Wypełnij wszystkie pola');
	      }
	    require_once('includes/bbcode.php');
	    $_POST['text'] = bbcodetohtml($_POST['text']);
	    $db->Execute("INSERT INTO `missions` (`name`, `text`, `exits`, `chances`, `mobs`, `chances2`, `items`, `chances3`) VALUES('".$_POST['name']."', '".$_POST['text']."', '".$_POST['exits']."', '".$_POST['chances']."', '".$_POST['mobs']."', '".$_POST['chances2']."', '".$_POST['items']."', '".$_POST['chances3']."')");
	    $smarty->assign("Message", 'Dodałeś nową lokację do losowych zadań');
	  }
      }
  }
/**
* Initialization of variables
*/
else 
  {
    $_GET['view'] = '';
    $arrView1 = array('bridge', 'poll', 'addtext', 'pdescriptions', 'pitems', 'pmonsters', 'pbridge', 'pmdesc', 'rmission');
    $arrLinks1 = array(A_BRIDGE, A_POLL, A_ADD_NEWS, 'Propozycje opisów', 'Propozycje przedmiotów', 'Propozycje potworów', 'Propozycje pytań na moście', 'Propozycje opisu potworów', 'Dodaj tekst do losowych misji');
    $arrView2 = array('del', 'donate', 'takeaway', 'add', 'tags', 'czat', 'bforum', 'jail', 'jailbreak', 'delplayers', 'ban', 'donator', 'logs', 'playerquest', 'banmail', 'vallars', 'srank');
    $arrLinks2 = array(A_DELETE, A_DONATION, A_TAKE, A_RANK, A_IMMU, A_CHAT_BAN, 'Zablokuj/Odblokuj pisanie przez gracza na forum', A_JAIL, A_JAILBREAK, A_DEL_PLAYERS, A_BAN, A_DONATOR, A_LOGS, A_PLAYERQUEST, A_BAN_MAIL, 'Daj/Zabierz Vallary graczowi', 'Nadaj unikalną rangę graczowi');
    $arrView3 = array('clearf', 'clearc', 'forums', 'innarchive');
    $arrLinks3 = array(A_FORUM_P, A_CHAT_P, A_FORUMS, A_INNARCHIVE);
    $arrView4 = array('equipment', 'monster', 'monster2', 'kowal', 'czary', 'mill', 'potions', 'alchemy');
    $arrLinks4 = array(A_EQUIP, A_MONSTERS, A_MONSTER2, A_SMITH, A_SPELLS, A_MILL, 'Dodaj miksturę', 'Dodaj plan u alchemika');
    $arrView5 = array('poczta', 'mail');
    $arrLinks5 = array(A_PM, A_MAIL);
    $arrView6 = array('close', 'register', 'censorship', 'meta', 'changelog', 'bugreport');
    $arrLinks6 = array(A_CLOSE, A_REGISTER, A_CENSORSHIP, A_META, A_CHANGELOG, A_BUG_REPORT);
    $smarty -> assign(array("View1" => $arrView1,
                            "Links1" => $arrLinks1,
                            "View2" => $arrView2,
                            "Links2" => $arrLinks2,
                            "View3" => $arrView3,
                            "Links3" => $arrLinks3,
                            "View4" => $arrView4,
                            "Links4" => $arrLinks4,
                            "View5" => $arrView5,
                            "Links5" => $arrLinks5,
                            "View6" => $arrView6,
                            "Links6" => $arrLinks6,
                            "Aaddupdate" => A_ADDUPDATE,
                            "Aaddnews" => A_ADDNEWS,
                            "Awelcome" => A_WELCOME,
                            "Abugtrack" => A_BUGTRACK));
  }

if (!isset($_GET['step'])) 
  {
    $_GET['step'] = '';
  }

if (!isset($_GET['action']))
  {
    $_GET['action'] = '';
  }

/**
* Assign variables and display page
*/
$smarty -> assign(array("View" => $_GET['view'], 
                        "Step" => $_GET['step'],
                        "Action" => $_GET['action']));
$smarty -> display('admin.tpl');

require_once("includes/foot.php");
?>
