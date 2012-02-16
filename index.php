<?php
/**
 *   File functions:
 *   Main site of game
 *
 *   @name                 : index.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 16.02.2012
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
            
    require_once("languages/".$lang."/index.php");

    $smarty -> assign(array("Gamename" => $gamename, 
                            "Gameadress" => $gameadress,
                            "Meta" => '',
                            "Welcome" => WELCOME,
                            "Register" => "Dołącz do nas",
                            "Rules" => RULES,
                            "Forums" => FORUMS,
                            "Irc" => IRC,
			    "Help" => "Poradnik",
			    "Donate" => "Dotuj nas",
			    "Promote" => "Promocja gry"));

    $objOpengame = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='open'");
    /**
    * When game is close
    */
    if ($objOpengame -> fields['value'] == 'N') 
    {
        $objReason = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='close_reason'");
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

    $objMetakey = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='metakeywords'");
    $objMetadesc = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='metadescr'");

    $smarty->assign(array("Time" => $newtime, 
                          "Email" => EMAIL,
                          "Password" => PASSWORD,
                          "Login" => LOGIN,
                          "Lostpasswd" => LOST_PASSWORD,
                          "Ctime" => CURRENT_TIME,
                          "Whave" => WE_HAVE,
                          "Registered" => REGISTERED,
                          "Metakeywords" => $objMetakey -> fields['value'],
                          "Metadescription" => $objMetadesc -> fields['value'],
			  "Charset" => CHARSET,
                          "Ingame" => IN_GAME));
    $objMetakey -> Close();
    $objMetadesc -> Close();

    /**
    * Main Page
    */
    if (!isset ($_GET['step'])) 
    {
    
        $update = $db -> SelectLimit("SELECT * FROM `updates` ORDER BY `id` DESC", 3);
        $arrnews = array();
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
            $arrnews[] = '<div class="news2"><b>'.$update -> fields['title']."</b> ".WRITE_BY." <b>".$update -> fields['starter']."</b>".$time."...</div>\"".$update -> fields['updates']."\".";
            $update -> MoveNext();        
        }
        $update -> Close();

        $adminmail1 = str_replace("@","[at]",$adminmail);

        $objCodexdate = $db -> Execute("SELECT `date` FROM `court` WHERE `title`='".CODEX." ".$gamename."'");

	$arrUsers = $db->GetAll("SELECT `id`, `user` FROM `players` WHERE `lpv`>=".(time() - 180)." ORDER BY `id` ASC");
	if (count($arrUsers) == 0)
	  {
	    $arrUsers[] = array('user' => 'Cisza i spokój', 'id' => 0);
	  }
    
        $smarty->assign( array ("Update" => $arrnews, 
                                "Adminname" => $adminname, 
                                "Adminmail" => $adminmail, 
                                "Adminmail1" => $adminmail1,
                                "Whatis" => WHAT_IS,
                                "Description" => DESCRIPTION,
                                "Codex" => CODEX,
                                "Codex2" => CODEX2,
                                "News" => NEWS,
                                "Codexdate" => $objCodexdate -> fields['date'],
                                "Pagetitle" => WELCOME,
				"Tonline" => "Grasują w okolicy:",
				"Ponline" => $arrUsers,
				"Tid" => "ID:",
				"Step" => ""));
        $smarty->display('index.tpl');
        $objCodexdate -> Close();
    }
    else
      {
	/**
	 * Game rules
	 */
	if ($_GET['step'] == 'rules') 
	  {
	    $objRules = $db -> Execute("SELECT body FROM court WHERE title='".CODEX." ".$gamename."'");
	    $smarty -> assign(array("Title" => PAGE_NAME,
				    "Rules2" => $objRules -> fields['body'],
				    "Pagetitle" => RULES));
	    $smarty -> display('rules.tpl');
	    $objRules -> Close();
	  }

	/**
	 * Password reminder
	 */
	elseif ($_GET['step'] == 'lostpasswd') 
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
		$_POST['email'] = htmlspecialchars($_POST['email'], ENT_QUOTES);
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
		$strEmail = htmlspecialchars($_GET['email'], ENT_QUOTES);
		$strCode =  htmlspecialchars($_GET['code'], ENT_QUOTES);
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
				    "Message" => $strMessage,
				    "Pagetitle" => LOST_PASSWORD));
	    $smarty -> display ('passwd.tpl');
	  }

	/**
	 * Write new email to database
	 */
	elseif (isset($_GET['code']) && isset($_GET['email']) && $_GET['step'] == 'newemail')
	  {
	    $strEmail = htmlspecialchars($_GET['email'], ENT_QUOTES);
	    $strCode =  htmlspecialchars($_GET['code'], ENT_QUOTES);
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
	    $smarty -> assign("Error", MAIL_CHANGED);
	    $smarty -> display('error.tpl');
	    exit;
	  }

	/**
	 * Donate game
	 */
	elseif ($_GET['step'] == 'donate')
	  {
	    $smarty->assign(array("Pagetitle" => "Dotuj nas",
				  "Step" => "donate",
				  "Dinfo" => $gamename." jest przedsięwzięciem niekomercyjnym. Wszystkie opcje w grze są dostępne bez ograniczeń. Jednakże utrzymanie gry (serwer oraz domena) kosztują. Dlatego jeżeli masz ochotę, możesz wesprzeć nas finansowo, korzystając z poniższych opcji. Osoba, która przekaże jakąkolwiek kwotę zostanie dopisana do Alei Zasłużonych (chyba że nie będzie sobie życzyła takiej opcji).",
				  "Dinfo2" => "Jeżeli chciałbyś skorzystać z opcji bezpośredniego (przelew na konto) przekazania pieniędzy ",
				  "Dmail" => "skontaktuj się z nami",
				  "Dinfo3" => "wtedy przekażemy wszelkie potrzebne informacje do wykonania przelewu.",
				  "Adminmail" => $adminmail));
	    $smarty->display('index.tpl');
	  }

	/**
	 * Promote game
	 */
	elseif ($_GET['step'] == 'promote')
	  {
	    $smarty->assign(array("Pagetitle" => "Promocja gry",
				  "Step" => "promote",
				  "Pinfo" => "Możesz wspomóc ".$gamename." poprzez promowanie jej wśród znajomych. Im więcej nas będzie bawić się w grze, tym sama zabawa będzie ciekawsza. W tym celu przygotowaliśmy parę grafik:",
				  "Pinfo2" => "- Userbar",
				  "Pinfo3" => "- Przycisk (button) (100x51 pikseli)",
				  "Pinfo4" => "- Anty-pixel"));
	    $smarty->display('index.tpl');
	  }
	$db -> Close();
      }
  }
?>
