<?php
/**
 *   File functions:
 *   Player log - events
 *
 *   @name                 : log.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 24.04.2012
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

$objTest = $db -> Execute("SELECT count(`id`) FROM `log` WHERE `owner`=".$player -> id." ORDER BY `id` DESC");
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
		  'I' => 'Kopalnia');

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

if (!isset($_POST['type']) && !isset($_GET['type']))
  {
    $log = $db -> SelectLimit("SELECT `id`, `log`, `czas` FROM `log` WHERE `owner`=".$player -> id." ORDER BY `id` DESC", 30, 30 * ($intPage - 1));
    $strSort = '';
  }
else
  {
    if (isset($_GET['type']))
      {
	$_POST['type'] = $_GET['type'];
      }
    if (!in_array($_POST['type'], array_keys($arrTypes)))
      {
	error(ERROR);
      }
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
