<?php
/**
 *   File functions:
 *   Register new players
 *
 *   @name                 : register.php                            
 *   @copyright            : (C) 2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 08.12.2011
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

require 'libs/Smarty.class.php';
require_once ('includes/config.php');

$smarty = new Smarty;
$smarty -> compile_check = true;

require_once("languages/".$lang."/register.php");

$objOpenreg = $db -> Execute("SELECT value FROM settings WHERE setting='register'");
/**
* When registration is closed
*/
if ($objOpenreg -> fields['value'] == 'N') 
{
    $objReason = $db -> Execute("SELECT value FROM settings WHERE setting='close_register'");
    $smarty -> assign ("Error", REASON.":<br />".$objReason -> fields['value']);
    $objReason -> Close();
    $smarty -> display ('error.tpl');
    exit;
}
$objOpenreg -> Close();

$query = $db -> Execute("SELECT count(`id`) FROM `players`");
$nump = $query->fields['count(`id`)'];
$query -> Close(); 

$time = date("H:i:s");
$hour = explode(":", $time);
$newhour = $hour[0] + 0;
if ($newhour > 23) 
{
    $newhour = $newhour - 24;
}
$arrtime = array($newhour, $hour[1], $hour[2]);
$newtime = implode(":",$arrtime);

$smarty -> assign(array("Gamename" => $gamename, 
                        "Meta" => '',
                        "Welcome" => WELCOME,
                        "Register" => "Dołącz do nas",
                        "Rules" => RULES,
                        "Links" => LINKS,
                        "Forums" => FORUMS,
                        "Time" => $newtime, 
                        "Players" => $nump, 
                        "Email" => EMAIL,
                        "Password" => PASSWORD,
                        "Login" => LOGIN,
                        "Lostpasswd" => LOST_PASSWORD,
                        "Ctime" => CURRENT_TIME,
                        "Whave" => WE_HAVE,
                        "Registered" => REGISTERED,
                        "Ingame" => IN_GAME,
                        "Charset" => CHARSET,
			"Donate" => "Dotuj nas",
			"Promote" => "Promocja gry",
			"Help" => "Poradnik",
                        "Pagetitle" => REGISTER));

if (isset($_GET['ref'])) 
{
    $smarty -> assign("Referal",$_GET['ref']);
} 
    else 
{
    $smarty -> assign("Referal","");
}

if (!isset($_GET['action']))
{
    $smarty -> assign(array("Description" => DESCRIPTION,
        "Nick" => NICK,
        "Confemail" => CONF_EMAIL,
        "Referralid" => REFERRAL_ID,
        "Ifnoid" => IF_NO_ID,
        "Register2" => REGISTER2,
        "Shortrules" => SHORT_RULES,
        "Rule1" => RULE1,
        "Rule2" => RULE2,
        "Rule3" => RULE3,
        "Rule4" => RULE4,
        "Rule5" => RULE5,
        "Rule6" => RULE6,
        "Rule7" => RULE7,
        "Rule8" => RULE8,
        "Description2" => DESCRIPTION2));
}

if (isset ($_GET['action']) && $_GET['action'] == 'register') 
{
/**
* Check for empty fields
*/
    if (!$_POST['user'] || !$_POST['email'] || !$_POST['vemail'] || !$_POST['pass'] ) 
    {
        $smarty -> assign ("Error", EMPTY_FIELDS);
        $smarty -> display ('error.tpl');
        exit;
    }
    
/**
* Email adress validation
*/       
    require_once('includes/verifymail.php');
    if (MailVal($_POST['email'], 2)) 
    {
        $smarty -> assign ("Error", BAD_EMAIL);
        $smarty -> display ('error.tpl');
        exit;
    }
    require_once('includes/verifypass.php');
    verifypass($_POST['pass'],'register');

/**
* Check nick
*/
    $strUser = $db -> qstr($_POST['user'], get_magic_quotes_gpc());
    $query = $db -> Execute("SELECT id FROM players WHERE user=".$strUser);
    $dupe1 = $query -> RecordCount();
    $query -> Close();  
    if ($dupe1 > 0) 
    {
        $smarty -> assign ("Error", BAD_NICK);
        $smarty -> display ('error.tpl');
        exit;
    }

/**
* Check mail adress in database
*/   
    $strEmail = $db -> qstr($_POST['email'], get_magic_quotes_gpc());
    $query = $db -> Execute("SELECT id FROM players WHERE email=".$strEmail);
    $dupe2 = $query -> RecordCount();
    $query -> Close();
    if ($dupe2 > 0) 
    {
        $smarty -> assign ("Error", EMAIL_HAVE);
        $smarty -> display ('error.tpl');
        exit;
    }

/**
* Check email adress writed on registration
*/ 
    if ($_POST['email'] != $_POST['vemail']) 
    {
        $smarty -> assign ("Error", EMAIL_MISS);
        $smarty -> display ('error.tpl');
        exit;
    }
    
    if (!$_POST['ref']) 
    {
        $_POST['ref'] = 0;
    }
    
    $ref = intval($_POST['ref']);
    $_POST['user'] = strip_tags($_POST['user']);
    $strUser = $db -> qstr($_POST['user'], get_magic_quotes_gpc());
    $_POST['email'] = strip_tags($_POST['email']);
    $strEmail = $db -> qstr($_POST['email'], get_magic_quotes_gpc());
    $_POST['pass'] = strip_tags($_POST['pass']);
    $aktw = rand(1,10000000);
    $data = date("y-m-d");
    $strDate = $db -> DBDate($data);
    $ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
    $message = WELCOME_TO." ".$gamename.YOUR_LINK."  ".$gameadress.ACTIV_LINK.$aktw.NICE_PLAYING." ".$gamename.". ".$adminname;
    $adress = $_POST['email'];
    $subject = SUBJECT." ".$gamename;
    require_once('mailer/mailerconfig.php');
    if (!$mail -> Send()) 
    {
        $smarty -> assign("Error", EMAIL_ERROR.$mail -> ErrorInfo);
        $smarty -> display('error.tpl');
        exit;
    }
    $strPass = MD5($_POST['pass']);
    $db -> Execute("INSERT INTO `aktywacja` (`user`, `email`, `pass`, `refs`, `aktyw`, `data`, `ip`) VALUES(".$strUser.", ".$strEmail.", '".$strPass."', ".$ref.", ".$aktw.", ".$strDate." , '".$ip."')") or die($db -> ErrorMsg());
    $smarty -> assign("Registersuccess", REGISTER_SUCCESS);
}

/**
* Initialization of variable
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
}

/**
* Assign variables and display page
*/
$objKeywords = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='metakeywords'");
$objDesc = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='metadescr'");
$smarty -> assign(array("Action" => $_GET['action'], 
			"Metakeywords" => $objKeywords->fields['value'], 
			"Metadescription" => $objDesc->fields['value']));
$objKeywords->Close();
$objDesc->Close();
$smarty -> display('register.tpl');

$db -> Close();
?>
