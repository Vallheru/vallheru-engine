<?php
/**
 *   File functions:
 *   Player log - events
 *
 *   @name                 : log.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 27.11.2012
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

$title = "Dziennik";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/log.php");

/**
 * Clear log
 */
if (isset($_GET['akcja']) && $_GET['akcja'] == 'wyczysc') 
{
    $db -> Execute("DELETE FROM `log` WHERE `owner`=".$player -> id);
    message('success', YOU_CLEAR);
}

/**
 * Delete old logs
 */
if (isset($_GET['step']) && $_GET['step'] == 'deleteold')
{
    $arrAmount = array(7, 14, 30);
    if (!in_array($_POST['oldtime'], $arrAmount))
      {
	message('error', ERROR);
      }
    else
      {
	$arrDate = explode("-", $data);
	$arrDate[0] = date("Y");
	$arrDate[2] = $arrDate[2] - $_POST['oldtime'];
	if ($arrDate[2] < 1)
	  {
	    $arrDays = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	    $arrDate[1] = $arrDate[1] - 1;
	    if ($arrDate[1] == 0)
	      {
		$arrDate[1] = 12;
	      }
	    $intKey = $arrDate[1] - 1;
	    $arrDate[2] = $arrDays[$intKey] + $arrDate[2];
	  }
	$strDate = implode("-", $arrDate);
	$strDate = $db -> DBDate($strDate);
	$db -> Execute("DELETE FROM `log` WHERE `owner`=".$player -> id." AND `czas`<".$strDate);
	message('success', DELETED2);
      }
}

$db -> Execute("UPDATE log SET unread='T' WHERE unread='F' AND owner=".$player -> id);

$sid = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `rank`='Admin' OR `rank`='Staff'");
$arrStaff = array();
while (!$sid->EOF) 
  {
    $arrStaff[$sid->fields['id']] = $sid -> fields['user'];
    $sid -> MoveNext();
  }
$sid -> Close();

/**
 * Send log to admin/staff
 */
if (isset($_GET['send'])) 
{
    $smarty -> assign(array("Sendthis" => SEND_THIS,
                            "Asend" => A_SEND));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
	checkvalue($_POST['staff']);
	checkvalue($_POST['lid']);
        $arrtest = $db -> Execute("SELECT `id`, `user`, `rank` FROM `players` WHERE `id`=".$_POST['staff']);
	$blnValid = TRUE;
        if (!$arrtest -> fields['id']) 
	  {
	    message('error', NO_PLAYER);
	    $blnValid = FALSE;
	  }
        if ($arrtest -> fields['rank'] != 'Admin' && $arrtest -> fields['rank'] != 'Staff') 
	  {
            message('error', NOT_STAFF);
	    $blnValid = FALSE;
	  }
        $arrmessage = $db -> Execute("SELECT * FROM `log` WHERE id=".$_POST['lid']);
        if (!$arrmessage -> fields['id']) 
	  {
            message('error', NO_EVENT);
	    $blnValid = FALSE;
	  }
        if ($arrmessage -> fields['owner'] != $player -> id) 
	  {
            message('error', NOT_YOUR);
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $strDate = $db -> DBDate($newdate);
	    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$arrtest -> fields['id'].",'".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user."</a>".L_ID.$player -> id.SEND_YOU."', ".$strDate.", 'A')");
	    $db -> Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`) values('".$player -> user."','".$player -> id."',".$arrtest -> fields['id'].",'".L_TITLE."','".$arrmessage -> fields['czas']."<br />".$arrmessage -> fields['log']."')");
	    message('success', YOU_SEND.$arrtest -> fields['user'].". <a href=log.php>".A_REFRESH."</a>");
	  }
    }
}

/**
* Delete or send to staff selected logs
*/
if (isset($_GET['action']) && $_GET['action'] == 'selected')
  {
    if (isset($_POST['selected']))
      {
	if ($_POST['selected'] == A_DELETE)
	  {
	    $objLid = $db -> Execute("SELECT `id` FROM `log` WHERE `owner`=".$player -> id);
	    while (!$objLid->EOF)
	      {
		if (isset($_POST[$objLid->fields['id']]))
		  {
		    $db->Execute("DELETE FROM `log` WHERE `id`=".$objLid->fields['id']);
		  }
		$objLid->MoveNext();
	      }
	    $objLid->Close();
	    message('success', DELETED);
	  }
	else
	  {
	    checkvalue($_POST['staff']);
	    $arrtest = $db -> Execute("SELECT `id`, `user`, `rank` FROM `players` WHERE `id`=".$_POST['staff']);
	    $blnValid = TRUE;
	    if (!$arrtest -> fields['id']) 
	      {
		message('error', NO_PLAYER);
		$blnValid = FALSE;
	      }
	    if ($arrtest -> fields['rank'] != 'Admin' && $arrtest -> fields['rank'] != 'Staff') 
	      {
		message('error', NOT_STAFF);
		$blnValid = FALSE;
	      }
	    if ($blnValid)
	      {
		$strDate = $db -> DBDate($newdate);
		$objMessages = $db -> Execute("SELECT * FROM `log` WHERE `owner`=".$player->id);
		$blnSent = FALSE;
		while (!$objMessages->EOF)
		  {
		    if (isset($_POST[$objMessages->fields['id']]))
		      {
			$db->Execute("INSERT INTO `mail` (`sender`, `senderid`, `owner`, `subject`, `body`) values('".$player->user."','".$player->id."',".$arrtest->fields['id'].",'".L_TITLE."','".$objMessages->fields['czas']."<br />".$objMessages->fields['log']."')");
			$blnSent = TRUE;
		      }
		    $objMessages->MoveNext();
		  }
		if ($blnSent)
		  {
		    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$arrtest->fields['id'].",'".L_PLAYER."<a href=view.php?view=".$player->id.">".$player->user."</a>".L_ID.$player -> id." wysłał tobie fragmenty swojego dziennika na pocztę.', ".$strDate.", 'A')");
		  }
		$objMessages->Close();
		message('success', "Wysłałeś wybrane wpisy do ".$arrtest->fields['user'].".");
	      }
	  }
      }
  }

/**
 * Accept invitation
 */
if (isset($_GET['accept']))
  {
    if ($_GET['accept'] != 'R' && $_GET['accept'] != 'T')
      {
	error('Zapomnij o tym.');
      }
    //Room invitation
    if ($_GET['accept'] == 'R')
      {
	if ($player->room)
	  {
	    message('error', 'Jesteś już w jakimś pokoju. Najpierw musisz go opuścić aby dołączyć do kolejnego.');
	  }
	elseif ($player->rinvite == 0)
	  {
	    message('error', 'Nie masz zaproszeń do pokoju w karczmie.');
	  }
	else
	  {
	    $player->room = $player->rinvite;
	    $db->Execute("UPDATE `players` SET `room`=".$player->rinvite.", `rinvite`=0 WHERE `id`=".$player->id);
	    $player->rinvite = 0;
	    $objRoom = $db->Execute("SELECT `owner`, `owners` FROM `rooms` WHERE `id`=".$player->room);
	    if ($objRoom->fields['owners'] != '')
	      {
		$arrOwners = explode(';', $objRoom->fields['owners']);
	      }
	    else
	      {
		$arrOwners = array();
	      }
	    $arrOwners[] = $objRoom->fields['owner'];
	    $objRoom->Close();
	    $strDate = $db -> DBDate($newdate);
	    $strSql = "INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES";
	    foreach ($arrOwners as $intOwner)
	      {
		$strSql .= "(".$intOwner.", '<a href=view.php?view=".$player->id.">".$player->user."</a> zaakceptował zaproszenie do twojego pokoju.', ".$strDate.", 'E'),";
	      }
	    $strSql = rtrim($strSql, ",").';';
	    $db->Execute($strSql);
	    message('success', 'Dołączyłeś do pokoju w karczmie.');
	  }
      }
    //Team invitation
    elseif ($_GET['accept'] == 'T')
      {
	if ($player->team)
	  {
	    message('error', 'Jesteś już w jakieś drużynie. Najpierw musisz ją opuścić aby dołączyć do kolejnej.');
	  }
	elseif ($player->tinvite == 0)
	  {
	    message('error', 'Nie masz zaproszeń do drużyny.');
	  }
	else
	  {
	    $player->team = $player->tinvite;
	    $db->Execute("UPDATE `players` SET `team`=".$player->team.", `tinvite`=0 WHERE `id`=".$player->id);
	    $player->tinvite = 0;
	    $objTeam = $db->Execute("SELECT * FROM `teams` WHERE `id`=".$player->team);
	    $strEmpty = '';
	    foreach (array('slot1', 'slot2', 'slot3', 'slot4', 'slot5') as $strSlot)
	      {
		if ($objTeam->fields[$strSlot] == 0)
		  {
		    $strEmpty = $strSlot;
		    break;
		  }
	      }
	    $db->Execute("UPDATE `teams` SET `".$strEmpty."`=".$player->id." WHERE `id`=".$player->team);
	    $strDate = $db -> DBDate($newdate);
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objTeam->fields['leader'].", '<a href=view.php?view=".$player->id.">".$player->user."</a> dołączył do twojej drużyny.', ".$strDate.", 'D')");
	    $objTeam->Close();
	    message('success', 'Dołączyłeś do drużyny.');
	  }
      }
  }

/**
 * Reject invitations
 */
if (isset($_GET['refuse']))
  {
    if ($_GET['refuse'] != 'R' && $_GET['refuse'] != 'T')
      {
	error('Zapomnij o tym.');
      }
    //Room invitation
    if ($_GET['refuse'] == 'R')
      {
	if ($player->rinvite == 0)
	  {
	    message('error', 'Nie masz zaproszeń do pokoju w karczmie.');
	  }
	else
	  {
	    $objRoom = $db->Execute("SELECT `owner`, `owners` FROM `rooms` WHERE `id`=".$player->rinvite);
	    if ($objRoom->fields['owners'] != '')
	      {
		$arrOwners = explode(';', $objRoom->fields['owners']);
	      }
	    else
	      {
		$arrOwners = array();
	      }
	    $arrOwners[] = $objRoom->fields['owner'];
	    $objRoom->Close();
	    $strDate = $db -> DBDate($newdate);
	    $strSql = "INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES";
	    foreach ($arrOwners as $intOwner)
	      {
		$strSql .= "(".$intOwner.", '<a href=view.php?view=".$player->id.">".$player->user."</a> odrzucił zaproszenie do twojego pokoju.', ".$strDate.", 'E'),";
	      }
	    $strSql = rtrim($strSql, ",").';';
	    $db->Execute($strSql);
	    $db->Execute("UPDATE `players` SET `rinvite`=0 WHERE `id`=".$player->id);
	    $player->rivite = 0;
	    message('success', 'Odrzuciłeś zaproszenie do pokoju w karczmie.');
	  }
      }
    elseif ($_GET['refuse'] == 'T')
      {
	if ($player->tinvite == 0)
	  {
	    message('error', 'Nie masz zaproszeń do drużyny.');
	  }
	else
	  {
	    $strDate = $db -> DBDate($newdate);
	    $objTeam = $db->Execute("SELECT `leader` FROM `teams` WHERE `id`=".$player->tinvite);
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objTeam->fields['leader'].", '<a href=view.php?view=".$player->id.">".$player->user."</a> odrzucił zaproszenie do twojej drużyny.', ".$strDate.", 'D')");
	    $objTeam->Close();
	    $db->Execute("UPDATE `players` SET `tinvite`=0 WHERE `id`=".$player->id);
	    $player->tinvite = 0;
	    message('success', 'Odrzuciłeś zaproszenie do drużyny.');
	  }
      }
  }

$arrTypes = array('O' => 'Strażnica', 
		  'V' => 'Vallary', 
		  'B' => 'Walka', 
		  'T' => 'Złodziejstwo', 
		  'M' => 'Rynek', 
		  'C' => 'Klan', 
		  'A' => 'Administracja', 
		  'N' => 'Bank', 
		  'R' => 'Chowańce', 
		  'H' => 'Dom', 
		  'J' => 'Więzienie', 
		  'L' => 'Biblioteka', 
		  'U' => 'Brak typu', 
		  'E' => 'Różne', 
		  'F' => 'Farma',
		  'I' => 'Kopalnia',
		  'D' => 'Drużyna');

/**
 * Get all available log types
 */
$objTypes = $db->Execute("SELECT `type` FROM `log` WHERE `owner`=".$player->id." GROUP BY `type`");
$arrOtypes = array();
$arrAtypes = array();
while (!$objTypes->EOF)
  {
    $arrOtypes[] = $objTypes->fields['type'];
    $arrAtypes[] = $arrTypes[$objTypes->fields['type']];
    $objTypes->MoveNext();
  }
$objTypes->Close();

if (isset($_GET['type']))
  {
    $_POST['type'] = $_GET['type'];
  }
if (isset($_POST['type']))
  {
    if (!in_array($_POST['type'], array_keys($arrTypes)))
      {
	error(ERROR);
      }
  }

if (!isset($_POST['type']))
  {
    $objTest = $db -> Execute("SELECT count(`id`) FROM `log` WHERE `owner`=".$player -> id." ORDER BY `id` DESC");
  }
else
  {
    $objTest = $db -> Execute("SELECT count(`id`) FROM `log` WHERE `owner`=".$player -> id." AND `type`='".$_POST['type']."' ORDER BY `id` DESC");
  }
$intPages = ceil($objTest -> fields['count(`id`)'] / 30);
$objTest -> Close();
if (isset($_GET['page']))
  {
    checkvalue($_GET['page']);
    $intPage = $_GET['page'];
  }
 else
   {
     $intPage = 1;
   }

if (!isset($_POST['type']))
  {
    $log = $db -> SelectLimit("SELECT `id`, `log`, `czas` FROM `log` WHERE `owner`=".$player -> id." ORDER BY `id` DESC", 30, 30 * ($intPage - 1));
    $strSort = '';
  }
else
  {
    $log = $db -> SelectLimit("SELECT `id`, `log`, `czas` FROM `log` WHERE `owner`=".$player -> id." AND `type`='".$_POST['type']."' ORDER BY `id` DESC", 30, 30 * ($intPage - 1));
    $strSort = '&amp;type='.$_POST['type'];
  }
$arrdate = array();
$arrtext = array();
$arrid1 = array(0);
$i = 0;
while (!$log -> EOF) 
{
    $arrdate[$i] = $log -> fields['czas'];
    $arrtext[$i] = $log -> fields['log'];
    $arrid1[$i] = $log -> fields['id'];
    $log -> MoveNext();
    $i++;
}
$log -> Close();

/**
* Initialization of variable
*/
if (!isset($_GET['send'])) 
{
    $_GET['send'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Date" => $arrdate, 
                        "Text" => $arrtext, 
                        "LogId" => $arrid1, 
                        "Send" => $_GET['send'],
			"Tpages" => $intPages,
			"Tpage" => $intPage,
			"Otypes" => $arrOtypes,
			"Atypes" => $arrAtypes,
			"Gsort" => $strSort,
			"Asort" => "Pokaż",
			"Tlogs" => "wpisy typu:",
			"Fpage" => "Idź do strony:",
                        "Loginfo" => LOG_INFO2,
                        "Event" => EVENT,
                        "Edate" => E_DATE,
                        "Sendevent" => SEND_EVENT,
                        "Clearlog" => CLEAR_LOG,
                        "Adeleteold" => A_DELETE_OLD,
                        "Aweek" => A_WEEK,
                        "A2week" => A_2WEEK,
                        "Amonth" => A_MONTH,
                        "Adelete" => A_DELETE,
			"Asend2" => "Wyślij zaznaczone do",
			"Ostaff" => $arrStaff));
$smarty -> display ('log.tpl');

require_once("includes/foot.php");
?>
