<?php
/**
 *   File functions:
 *   Main site of game
 *
 *   @name                 : index.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 05.09.2011
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

require_once ('includes/config.php');
if (!$gamename) 
{
    $host = $_SERVER['HTTP_HOST'];
    $path = str_replace("index.php","",$_SERVER['PHP_SELF']);
    $address = "http://".$host.$path."install/install.php";
    $meta = "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$address."\">";
    print "<html><head>".$meta."</head><body></body></html>";
    exit;
} 
    else 
{

    require_once ('libs/Smarty.class.php');

    $smarty = new Smarty;
    $smarty->compile_check = true;

    /**
    * Check avaible languages
    */
    $arrLanguage = scandir('languages/', 1);
    $arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));

    /**
    * Get the localization for game
    */
    $strLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    foreach ($arrLanguage as $strTrans)
    {
	if (strpos($strLanguage, $strTrans) === 0)
	  {
	    $strTranlsation = $strTrans;
	    break;
	  }
    }
    if (!isset($strTranslation))
    {
        $strTranslation = 'pl';
    }
    if (isset($_GET['lang']))
    {
        if (in_array($_GET['lang'], $arrLanguage))
        {
            $strTranslation = $_GET['lang'];
        }
    }
            
    require_once("languages/".$strTranslation."/index.php");

    $smarty -> assign(array("Gamename" => $gamename, 
                            "Gameadress" => $gameadress,
                            "Meta" => '',
                            "Welcome" => WELCOME,
                            "Register" => REGISTER,
                            "Rules" => RULES,
                            "Forums" => FORUMS,
                            "Irc" => IRC));

    $objOpengame = $db -> Execute("SELECT value FROM settings WHERE setting='open'");
    /**
    * When game is close
    */
    if ($objOpengame -> fields['value'] == 'N') 
    {
        $objReason = $db -> Execute("SELECT value FROM settings WHERE setting='close_reason'");
        $smarty -> assign ("Error", REASON.":<br />".$objReason -> fields['value']);
        $objReason -> Close();
        $smarty -> display ('error.tpl');
        exit;
    }
    $objOpengame -> Close();

    $time = date("H:i:s");
    $hour = explode(":", $time);
    $newhour = $hour[0] + 0;
    if ($newhour > 23) 
    {
        $newhour = $newhour - 24;
    }
    $arrtime = array($newhour, $hour[1], $hour[2]);
    $newtime = implode(":",$arrtime);

    $query = $db -> Execute("SELECT count(*) FROM `players`");
    $nump = $query -> fields['count(*)'];
    $query -> Close();
    
    $span = (time() - 180);
    $objQuery = $db -> Execute("SELECT count(*) FROM `players` WHERE `lpv`>=".$span);
    $intNumo = $objQuery -> fields['count(*)'];
    $objQuery -> Close();

    $objMetakey = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='metakeywords'");
    $objMetadesc = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='metadescr'");

    $smarty->assign(array("Time" => $newtime, 
                          "Players" => $nump, 
                          "Online" => $intNumo, 
                          "Email" => EMAIL,
                          "Password" => PASSWORD,
                          "Login" => LOGIN,
                          "Lostpasswd" => LOST_PASSWORD,
                          "Ctime" => CURRENT_TIME,
                          "Whave" => WE_HAVE,
                          "Registered" => REGISTERED,
                          "Metakeywords" => $objMetakey -> fields['value'],
                          "Metadescription" => $objMetadesc -> fields['value'],
                          "Ingame" => IN_GAME));
    $objMetakey -> Close();
    $objMetadesc -> Close();

    /**
    * Main Page
    */
    if (!isset ($_GET['step'])) 
    {
    
        $update = $db -> SelectLimit("SELECT * FROM updates WHERE lang='".$strTranslation."' ORDER BY id DESC", 3);
        $arrnews = array();
        $i = 0;
        while (!$update -> EOF) 
        {
            if (isset($update -> fields['time']))
            {
                $time = " ".DAY." <b>".$update -> fields['time']."</b>";
            }
                else
            {
                $time = '';
            }
            $arrnews[$i] = "<center><b>".$update -> fields['title']."</b> ".WRITE_BY." <b>".$update -> fields['starter']."</b>".$time."...</center>\"".$update -> fields['updates']."\".<br /><br />";
            $i = $i + 1;
            $update -> MoveNext();        
        }
        $update -> Close();

        $adminmail1 = str_replace("@","[at]",$adminmail);

        $objCodexdate = $db -> Execute("SELECT `date` FROM `court` WHERE `title`='".CODEX." ".$gamename."'");
    
        $smarty->assign( array ("Update" => $arrnews, 
                                "Adminname" => $adminname, 
                                "Adminmail" => $adminmail, 
                                "Adminmail1" => $adminmail1,
                                "Whatis" => WHAT_IS,
                                "Description" => DESCRIPTION,
                                "Codex" => CODEX,
                                "Codex2" => CODEX2,
                                "News" => NEWS,
                                "Charset" => CHARSET,
                                "Codexdate" => $objCodexdate -> fields['date'],
                                "Pagetitle" => WELCOME,
				"Step" => ""));
        $smarty->display('index.tpl');
        $objCodexdate -> Close();
    }

    /**
    * Game rules
    */
    if (isset ($_GET['step']) && $_GET['step'] == 'rules') 
    {
        $objRules = $db -> Execute("SELECT body FROM court WHERE title='".CODEX." ".$gamename."'");
        $smarty -> assign(array("Title" => PAGE_NAME,
                                "Rules2" => $objRules -> fields['body'],
                                "Charset" => CHARSET,
                                "Pagetitle" => RULES));
        $smarty -> display('rules.tpl');
        $objRules -> Close();
    }

    /**
    * Password reminder
    */
    if (isset($_GET['step']) && $_GET['step'] == 'lostpasswd') 
    {
        $strMessage = '';
        if (isset($_GET['action']) && $_GET['action'] == 'haslo') 
        {
            if (!$_POST['email']) 
            {
                $smarty -> assign ("Error", ERROR_MAIL);
                $smarty -> display ('error.tpl');
                exit;
            }
            $_POST['email'] =  strip_tags($_POST['email']);
            $query = $db -> Execute("SELECT `id` FROM `players` WHERE `email`='".$_POST['email']."'");
            $intId = $query -> fields['id'];
            $query -> Close();
        
            if (!$intId) 
            {
                $smarty -> assign ("Error", ERROR_NOEMAIL);
                $smarty -> display ('error.tpl');
                exit;
            }
            $new_pass = substr(md5(uniqid(rand(), true)), 3, 9);
            $intNumber = substr(md5(uniqid(rand(), true)), 3, 9);
            $strLink = $gameadress."/index.php?step=lostpasswd&code=".$intNumber."&email=".$_POST['email'];
            $adress = $_POST['email'];
            $message = MESSAGE_PART1." ".$gamename.".".MESSAGE_PART2." \n".$new_pass."\n ".MESSAGE_PART3."\n ".$strLink."\n".MESSAGE_PART4." ".$gamename."\n".$adminname;
            $subject = MESSAGE_SUBJECT." ".$gamename;
            require_once('mailer/mailerconfig.php');
            if (!$mail -> Send()) 
            {
                $smarty -> assign ("Error", MESSAGE_NOT_SEND." ".$mail -> ErrorInfo);
                $smarty -> display ('error.tpl');
                exit;
            }
            $strPass = md5($new_pass);
            $db -> Execute("INSERT INTO `lost_pass` (`number`, `email`, `newpass`, `id`) VALUES('".$intNumber."', '".$_POST['email']."', '".$strPass."', ".$intId.")") or $db -> ErrorMsg();
        }

        /**
         * Write new password to database
         */
        if (isset($_GET['code']) && isset($_GET['email']))
        {
            $strEmail =  strip_tags($_GET['email']);
            $strCode =  strip_tags($_GET['code']);
            if (empty($strCode) || empty($strEmail)) 
            {
                $smarty -> assign ("Error", ERROR);
                $smarty -> display ('error.tpl');
                exit;
            }
            $objTest = $db -> Execute("SELECT `newpass`, `id` FROM `lost_pass` WHERE `number`='".$strCode."' AND `email`='".$strEmail."'");
            if (!$objTest -> fields['newpass'])
            {
                $smarty -> assign ("Error", ERROR);
                $smarty -> display ('error.tpl');
                exit;
            }
            $db -> Execute("UPDATE `players` SET `pass`='".$objTest -> fields['newpass']."' WHERE `email`='".$strEmail."' AND `id`=".$objTest -> fields['id']);
            $db -> Execute("DELETE FROM `lost_pass` WHERE `number`='".$strCode."' AND `email`='".$strEmail."' AND `id`=".$objTest -> fields['id']);
            $objTest -> Close();
            $strMessage = PASS_CHANGED;
        }

        /**
        * Initializantion of variable
        */
        if (!isset($_GET['action'])) 
        {
            $_GET['action'] = '';
        }
        $smarty -> assign(array("Action" => $_GET['action'],
                                "Lostpassword2" => LOST_PASSWORD2,
                                "Success" => SUCCESS,
                                "Email" => EMAIL,
                                "Send" => SEND,
                                "Charset" => CHARSET,
                                "Message" => $strMessage,
                                "Pagetitle" => LOST_PASSWORD));
        $smarty -> display ('passwd.tpl');
    }

    /**
     * Write new email to database
     */
    if (isset($_GET['code']) && isset($_GET['email']) && (isset($_GET['step']) && $_GET['step'] == 'newemail'))
    {
        $strEmail =  strip_tags($_GET['email']);
        $strCode =  strip_tags($_GET['code']);
        if (empty($strCode) || empty($strEmail)) 
        {
            $smarty -> assign ("Error", ERROR);
            $smarty -> display ('error.tpl');
            exit;
        }
        $objTest = $db -> Execute("SELECT `email`, `id` FROM `lost_pass` WHERE `number`='".$strCode."' AND `newemail`='".$strEmail."'");
        if (!$objTest -> fields['email'])
        {
            $smarty -> assign ("Error", ERROR);
            $smarty -> display ('error.tpl');
            exit;
        }
        $db -> Execute("UPDATE `players` SET `email`='".$strEmail."' WHERE `email`='".$objTest -> fields['email']."' AND `id`=".$objTest -> fields['id']);
        $db -> Execute("DELETE FROM `lost_pass` WHERE `number`='".$strCode."' AND `newemail`='".$strEmail."' AND `id`=".$objTest -> fields['id']);
        $objTest -> Close();
        $smarty -> assign(array("Error" => MAIL_CHANGED,
                                "Charset" => CHARSET));
        $smarty -> display('error.tpl');
        exit;
    }

    $db -> Close();
}
?>
