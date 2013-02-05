<?php
/**
 *   File functions:
 *   Bugs reported by players
 *
 *   @name                 : bugreport.php                            
 *   @copyright            : (C) 2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 05.01.2013
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

$smarty -> assign(array("Bugtype" => "Status",
			"Bugloc" => BUG_LOC,
			"Bugid" => BUG_ID,
			"Bugname" => BUG_NAME));
/**
 * Edit bug
 */
if (isset($_GET['step']))
  {
    checkvalue($_GET['step']);
    $objBug = $db -> Execute("SELECT `id`, `sender`, `title`, `resolution`, `location`, `desc` FROM `bugreport` WHERE `id`=".$_GET['step']);
    if (!$objBug -> fields['id'])
      {
	error(ERROR);
      }
    switch ($objBug->fields['resolution'])
      {
      case 0:
	$strStatus = "Oczekuje na sprawdzenie";
	break;
      case 2:
      case 3:
	$strStatus = "Wymaga więcej informacji";
	break;
      default:
	break;
      }
    $arrOptions = array(BUG_FIXED, NOT_BUG, WORK_FOR_ME, MORE_INFO, BUG_DOUBLE, "Nieprawidłowe zgłoszenie");
    $arrActions = array('fixed', 'notbug', 'workforme', 'moreinfo', 'duplicate', 'invalid');
    $smarty -> assign(array("Bugdesc" => BUG_DESC,
			    "Bugactions" => BUG_ACTIONS,
			    "Bugoptions" => $arrOptions,
			    "Bugactions2" => $arrActions,
			    "Amake" => A_MAKE,
			    "Tcomment" => T_COMMENT2,
			    "Bugname2" => $objBug -> fields['title'],
			    "Bugtype2" => $strStatus,
			    "Bugloc2" => $objBug -> fields['location'],
			    "Bugdesc2" => $objBug->fields['desc'],
			    "Step" => $_GET['step']));
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
	switch ($intKey)
	  {
	  case 0:
	    $strInfo = $strInfo.HAS_FIXED;
	    $strMessage = HAS_FIXED2;
	    $strAuthor = '<b><a href="view.php?view='.$player -> id.'">'.$player -> user."</a></b>, ID <b>".$player -> id.'</b>';
	    $strDesc = T_BUG.": ".$objBug -> fields['title']. REPORTED_BY.$objBug -> fields['sender'];
	    $db -> Execute("INSERT INTO `changelog` (`author`, `location`, `text`, `date`, `lang`) VALUES('".$strAuthor."', '".$objBug -> fields['location']."', '".$strDesc."', ".$strDate.", '".$lang."')");
	    $db->Execute("UPDATE `players` SET `vallars`=`vallars`+".$_POST['vallars']." WHERE `id`=".$objBug->fields['sender']);
	    if ($_POST['vallars'] != 0)
	      {
		$db->Execute("INSERT INTO `vallars` (`owner`, `amount`, `reason`) VALUES(".$objBug->fields['sender'].", ".$_POST['vallars'].", 'Zgłoszenie błędu.')");
	      }
	    break;
	  case 1:
	    $strInfo = $strInfo.NOT_BUG3;
	    $strMessage = NOT_BUG2;
	    break;
	  case 2:
	    $strInfo = $strInfo.WORK_FOR_ME2;
	    $strMessage = WORK_FOR_ME3;
	    break;
	  case 3:
	    $strInfo = $strInfo.MORE_INFO2;
	    $strMessage = MORE_INFO3;
	    break;
	  case 4:
	    $strInfo = $strInfo.BUG_DOUBLE2;
	    $strMessage = BUG_DOUBLE3;
	    break;
	  case 5:
	    $strInfo = $strInfo."</b> zostało odrzucone. <b>Przyczyna:</b> zgłoszenie nie zawierało niezbędnych informacji.";
	    $strMessage = "Oznaczyłeś ten błąd jako nieprawidłowe zgłoszenie.";
	    break;
	  default:
	    break;
	  }
	if ($intKey != 2 && $intKey != 3)
	  {
	    $db -> Execute("DELETE FROM `bugreport` WHERE `id`=".$_GET['step']);
	    $db->Execute("DELETE FROM `bug_comments` WHERE `bugid`=".$_GET['step']);
	  }
	else
	  {
	    $db->Execute("UPDATE `bugreport` SET `resolution`=".$intKey." WHERE `id`=".$_GET['step']);
	  }
	//Add a comment to bug report
	if (isset($_POST['bugcomment']) && !empty($_POST['bugcomment']))
	  {
	    require_once('includes/bbcode.php');
	    $_POST['bugcomment'] = bbcodetohtml($_POST['bugcomment']);
	    if (in_array($intKey, array(2, 3)))
	      {
		$db->Execute("INSERT INTO `bug_comments` (`bugid`, `author`, `body`, `time`) VALUES(".$_GET['step'].", '".$player->user." ID:".$player->id."', '".$_POST['bugcomment']."', '".$data."')");
	      }
	    else
	      {
		$strInfo .= ' Dodatkowe informacje: '.$_POST['bugcomment'];
	      }
	  }
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objBug -> fields['sender'].", '".$strInfo."', ".$strDate.", 'A')");
	message('success', $strMessage);
	unset($_GET['step']);
      }
    $objBug -> Close();
  }
/**
 * Bugs list
 */
if (!isset($_GET['step']))
  {
    $objBugs = $db -> Execute("SELECT `id`, `sender`, `title`, `resolution`, `location` FROM `bugreport` ORDER BY `id` ASC");
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
	switch ($objBugs->fields['resolution'])
	  {
	  case 0:
	    $arrType[] = "Oczekuje na sprawdzenie";
	    break;
	  case 2:
	  case 3:
	    $arrType[] = "Wymaga więcej informacji";
	    break;
	  default:
	    break;
	  }
	$objBugs -> MoveNext();
      }
    $objBugs -> Close();
    $smarty -> assign(array("Bugreporter" => BUG_REPORTER,
			    "Bugstype" => $arrType,
			    "Bugsloc" => $arrLocation,
			    "Bugsid" => $arrId,
			    "Bugsreporter" => $arrReporter,
			    "Bugsname" => $arrTitle,
			    "Step" => ""));
  }

?>