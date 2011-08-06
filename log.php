<?php
/**
 *   File functions:
 *   Player log - events
 *
 *   @name                 : log.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 16.10.2006
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
// $Id: log.php 725 2006-10-16 15:47:57Z thindil $

$title = "Dziennik";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/log.php");

$db -> Execute("UPDATE log SET unread='T' WHERE unread='F' AND owner=".$player -> id);

if (!isset($_GET['limit']))
{
    $_GET['limit'] = 0;
}

if (!ereg("^[0-9]*$", $_GET['limit']))
{
    error(ERROR);
}

$objTest = $db -> Execute("SELECT count(*) FROM `log` WHERE `owner`=".$player -> id." ORDER BY `id` DESC");
$intAmount = $objTest -> fields['count(*)'];
$objTest -> Close();
if ($intAmount < $_GET['limit'])
{
    error(ERROR);
}

$smarty -> assign(array("Previous" => '',
                        "Next" => ''));

$log = $db -> SelectLimit("SELECT `id`, `log`, `czas` FROM `log` WHERE `owner`=".$player -> id." ORDER BY `id` DESC", 30, $_GET['limit']);
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
    $i = $i + 1;
}
$log -> Close();

if ($_GET['limit'] >= 30) 
{
    $intLimit = $_GET['limit'] - 30;
    $smarty -> assign("Previous", "<form method=\"post\" action=\"log.php?limit=".$intLimit."\"><input type=\"submit\" value=\"".A_PREVIOUS."\" /></form> ");
}
$_GET['limit'] = $_GET['limit'] + 30;
if ($intAmount > 30 && $_GET['limit'] < $intAmount) 
{
    $smarty -> assign("Next", "<form method=\"post\" action=\"log.php?limit=".$_GET['limit']."\"><input type=\"submit\" value=\"".A_NEXT."\" /></form>");
}

if (isset($_GET['akcja']) && $_GET['akcja'] == 'wyczysc') 
{
    $db -> Execute("DELETE FROM log WHERE owner=".$player -> id);
    error ("<br />".YOU_CLEAR." (<a href=\"log.php\">".A_REFRESH."</a>)");
}

if (isset($_GET['send'])) 
{
    $sid = $db -> Execute("SELECT id, user FROM players WHERE rank='Admin' OR rank='Staff'");
    $arrname = array();
    $arrid = array();
    $i = 0;
    while (!$sid -> EOF) {
        $arrname[$i] = $sid -> fields['user'];
        $arrid[$i] = $sid -> fields['id'];
        $sid -> MoveNext();
        $i = $i + 1;
    }
    $sid -> Close();
    $smarty -> assign(array("Name" => $arrname, 
                            "StaffId" => $arrid,
                            "Sendthis" => SEND_THIS,
                            "Asend" => A_SEND));
    if (isset ($_GET['step']) && $_GET['step'] == 'send') 
    {
        if (!ereg("^[1-9][0-9]*$", $_POST['staff'])) 
        {
            error (ERROR);
        }
        if (!ereg("^[1-9][0-9]*$", $_POST['lid'])) 
        {
            error (ERROR);
        }
        $arrtest = $db -> Execute("SELECT id, user, rank FROM players WHERE id=".$_POST['staff']);
        if (!$arrtest -> fields['id']) 
        {
            error (NO_PLAYER);
        }
        if ($arrtest -> fields['rank'] != 'Admin' && $arrtest -> fields['rank'] != 'Staff') 
        {
            error (NOT_STAFF);
        }
        $arrmessage = $db -> Execute("SELECT * FROM log WHERE id=".$_POST['lid']);
        if (!$arrmessage -> fields['id']) 
        {
            error (NO_EVENT);
        }
        if ($arrmessage -> fields['owner'] != $player -> id) 
        {
            error (NOT_YOUR);
        }
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO log (owner, log, czas) VALUES(".$arrtest -> fields['id'].",'".L_PLAYER."<a href=view.php?view=".$player -> id.">".$player -> user."</a>".L_ID.$player -> id.SEND_YOU."', ".$strDate.")");
        $db -> Execute("INSERT INTO mail (sender,senderid,owner,subject,body) values('".$player -> user."','".$player -> id."',".$arrtest -> fields['id'].",'".L_TITLE."','".$arrmessage -> fields['czas']."<br />".$arrmessage -> fields['log']."')");
        error (YOU_SEND.$arrtest -> fields['user'].". <a href=log.php>".A_REFRESH."</a>");
    }
}

/**
* Delete selected logs
*/
if (isset($_GET['action']) && $_GET['action'] == 'delete')
{
    $objLid = $db -> Execute("SELECT id FROM log WHERE owner=".$player -> id);
    $arrId = array();
    $i = 0;
    while (!$objLid -> EOF)
    {
        $arrId[$i] = $objLid -> fields['id'];
        $i = $i + 1;
        $objLid -> MoveNext();
    }
    $objLid -> Close();
    foreach ($arrId as $bid) 
    {
        if (isset($_POST[$bid])) 
        {
            $db -> Execute("DELETE FROM log WHERE id=".$bid);
        }
    }
    error(DELETED);
}

/**
 * Delete old logs
 */
if (isset($_GET['step']) && $_GET['step'] == 'deleteold')
{
    $arrAmount = array(7, 14, 30);
    if (!in_array($_POST['oldtime'], $arrAmount))
    {
        error(ERROR);
    }
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
    error(DELETED2);
}

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
                        "Loginfo" => LOG_INFO2,
                        "Event" => EVENT,
                        "Edate" => E_DATE,
                        "Sendevent" => SEND_EVENT,
                        "Clearlog" => CLEAR_LOG,
                        "Adeleteold" => A_DELETE_OLD,
                        "Aweek" => A_WEEK,
                        "A2week" => A_2WEEK,
                        "Amonth" => A_MONTH,
                        "Adelete" => A_DELETE));
$smarty -> display ('log.tpl');

require_once("includes/foot.php");
?>
