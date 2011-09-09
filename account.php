<?php
/**
 *   File functions:
 *   Account options - change avatar, email, password and nick
 *
 *   @name                 : account.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 09.09.2011
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

$title = 'Opcje konta';
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/account.php");

/**
* Assign variable to template
*/
$smarty -> assign("Avatar", '');

/**
 * Links
 */
if (isset($_GET['view']) && $_GET['view'] == 'links')
{
    $objLinks = $db -> Execute("SELECT `id`, `file`, `text` FROM `links` WHERE `owner`=".$player -> id." ORDER BY `id` ASC");
    $arrId = array(0);
    $arrFile = array();
    $arrText = array();
    $i = 0;
    while (!$objLinks -> EOF)
    {
        $arrId[$i] = $objLinks -> fields['id'];
        $arrFile[$i] = $objLinks -> fields['file'];
        $arrText[$i] = $objLinks -> fields['text'];
        $i ++;
        $objLinks -> MoveNext();
    }
    $objLinks -> Close();
    if (!isset($_GET['lid']))
    {
        $strFormaction = A_ADD;
        $intLinkid = 0;
    }
        else
    {
       $_GET['lid'] = intval($_GET['lid']);
       if ($_GET['lid'] == 0)
       {
           $strFormaction = A_ADD;
       }
           else
       {
           $strFormaction = A_EDIT;
       }
       $intLinkid = $_GET['lid'];
    }
    $smarty -> assign(array("Linksinfo" => LINKS_INFO,
                            "Tfile" => T_LINK,
                            "Tname" => T_NAME,
                            "Tactions" => T_ACTIONS,
                            "Adelete" => A_DELETE,
                            "Aedit" => A_EDIT,
                            "Aform" => $strFormaction,
                            "Linksid" => $arrId,
                            "Linksfile" => $arrFile,
                            "Linkstext" => $arrText,
                            "Linkid" => $intLinkid,
                            "Linkfile" => '',
                            "Linkname" => ''));

    /**
     * Add/edit links
     */
    if (isset($_GET['step']) && $_GET['step'] == 'edit')
    {
        if (!isset($_GET['action']) && $_GET['lid'] > 0)
        {
            $objLink = $db -> Execute("SELECT `id`, `file`, `text` FROM `links` WHERE `id`=".$_GET['lid']." AND `owner`=".$player -> id);
            if (!$objLink -> fields['id'])
            {
                error(NOT_YOUR);
            }
            $smarty -> assign(array("Linkfile" => $objLink -> fields['file'],
                                    "Linkname" => $objLink -> fields['text']));
            $objLink -> Close();
        }
        if (isset($_GET['action']) && $_GET['action'] == 'change')
        {
            $strFile = strip_tags($_POST['linkadress']);
            $strText = strip_tags($_POST['linkname']);
            if (empty($strFile) || empty($strText))
            {
                error(EMPTY_FIELDS);
            }
            $arrForbidden = array('config.php', 'session.php', 'reset.php', 'resets.php', 'quest', 'portal');
            foreach ($arrForbidden as $strForbidden)
            {
                $intPos = strpos($strFile, $strForbidden);
                if ($intPos !== false)
                {
                    error(ERROR);
                }
            }
            if ($_GET['lid'] > 0)
            {
                $db -> Execute("UPDATE `links` SET `file`='".$strFile."', `text`='".$strText."' WHERE `id`=".$_GET['lid']." AND `owner`=".$player -> id);
                $strMessage = YOU_CHANGE;
            }
                else
            {
                $db -> Execute("INSERT INTO `links` (`owner`, `file`, `text`) VALUES(".$player -> id.", '".$strFile."', '".$strText."')");
                $strMessage = YOU_ADD;
            }
            error($strMessage);
        }
    }

    /**
     * Delete links
     */
    if (isset($_GET['step']) && $_GET['step'] == 'delete')
    {
        $objLink = $db -> Execute("SELECT `id` FROM `links` WHERE `id`=".$_GET['lid']." AND `owner`=".$player -> id);
        if (!$objLink -> fields['id'])
        {
            error(NOT_YOUR);
        }
        $objLink -> Close();
        $db -> Execute("DELETE FROM `links` WHERE `id`=".$_GET['lid']." AND `owner`=".$player -> id);
        error(LINK_DELETED);
    }
}

/**
 * Bugtrack
 */
if (isset($_GET['view']) && $_GET['view'] == 'bugtrack')
{
    $objBugs = $db -> Execute("SELECT `id`, `title`, `type`, `location` FROM `bugreport` WHERE `resolution`=0 ORDER BY `id` ASC");
    $arrId = array();
    $arrTitle = array();
    $arrType = array();
    $arrLocation = array();
    $i = 0;
    while (!$objBugs -> EOF)
    {
        $arrId[$i] = $objBugs -> fields['id'];
        $arrTitle[$i] = $objBugs -> fields['title'];
        $arrLocation[$i] = $objBugs -> fields['location'];
        if ($objBugs -> fields['type'] == 'text')
        {
            $arrType[$i] = BUG_TEXT;
        }
            else
        {
            $arrType[$i] = BUG_CODE;
        }
        $i++;
        $objBugs -> MoveNext();
    }
    $objBugs -> Close();
    $smarty -> assign(array("Bugtype" => BUG_TYPE,
                            "Bugloc" => BUG_LOC,
                            "Bugid" => BUG_ID,
                            "Bugname" => BUG_NAME,
                            "Bugtrackinfo" => BUGTRACK_INFO,
                            "Bugstype" => $arrType,
                            "Bugsloc" => $arrLocation,
                            "Bugsid" => $arrId,
                            "Bugsname" => $arrTitle));
}

/**
 * Bug report
 */
if (isset($_GET['view']) && $_GET['view'] == 'bugreport')
{
    $smarty -> assign(array("Bugtype" => BUG_TYPE,
                            "Bugtext" => BUG_TEXT,
                            "Bugcode" => BUG_CODE,
                            "Bugloc" => BUG_LOC,
                            "Bugdesc" => BUG_DESC,
                            "Areport" => A_REPORT,
                            "Bugname" => BUG_NAME,
                            "Buginfo" => BUG_INFO));
    /**
     * Report bug
     */
    if (isset($_GET['step']) && $_GET['step'] == 'report')
    {
        $arrFields = array($_POST['bugtitle'], $_POST['type'], $_POST['location'], $_POST['desc']);
        require_once('includes/bbcode.php');
        foreach ($arrFields as $strField)
        {
            $strField = strip_tags($strField);
            $strField = bbcodetohtml($strField);
	    if (preg_match("/\S+/", $strField) == 0)
            {
                error(EMPTY_FIELDS);
            }
        }
        if (!in_array($arrFields[1], array('text', 'code')))
        {
            error(ERROR);
        }
        $intDesc = strlen($arrFields[3]);
        if ($intDesc < 50)
        {
            error(TOO_SHORT);
        }
        $db -> Execute("INSERT INTO `bugreport` (`sender`, `title`, `type`, `location`, `desc`) VALUES(".$player -> id.", '".$arrFields[0]."', '".$arrFields[1]."', '".$arrFields[2]."', '".$arrFields[3]."')");
	$objStaff = $db -> Execute("SELECT `id` FROM `players` WHERE `rank`='Admin'");
	$strDate = $db -> DBDate($newdate);
	while (!$objStaff->EOF) 
	  {
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$objStaff->fields['id'].", 'Zgłoszono nowy błąd.', ".$strDate.")") or die($db->ErrorMsg());
	    $objStaff->MoveNext();
	  }
	$objStaff->Close();
        error(B_REPORTED);
    }
}

/**
* Select game localization
*/
if (isset ($_GET['view']) && $_GET['view'] == "lang") 
  {
    /**
     * Check avaible languages
     */
    $arrLanguage = scandir('languages/', 1);
    $arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));
    
    /**
    * Show select menu
    */
    $smarty -> assign(array("Langinfo" => LANG_INFO,
                            "Flang" => F_LANG,
                            "Slang" => S_LANG,
                            "Aselect" => A_SELECT,
                            "Lang" => $arrLanguage));

    /**
    * Write selected information to database
    */
    if (isset ($_GET['step']) && $_GET['step'] == 'lang') 
    {
        if (!isset($_POST['mainlang']) || !isset($_POST['seclang']))
        {
            error(EMPTY_FIELDS);
        }
        if (!in_array($_POST['mainlang'], $arrLanguage) || !in_array($_POST['seclang'], $arrLanguage))
        {
            error(ERROR);
        }
        $db -> Execute("UPDATE players SET lang='".$_POST['mainlang']."' WHERE id=".$player -> id);
        $strMessage = YOU_SELECT.$_POST['mainlang'];
        if ($_POST['seclang'] != $_POST['mainlang'] || isset($player -> seclang))
        {
            $db -> Execute("UPDATE players SET seclang='".$_POST['seclang']."' WHERE id=".$player -> id);
            $strMessage = $strMessage.AND_SECOND.$_POST['seclang'];
        }
        $strMessage = $strMessage." <a href=\"account.php\">".A_REFRESH."</a>";
        $smarty -> assign("Message", $strMessage);
    }
  }

/**
 * Display info about changes in game
 */
if (isset($_GET['view']) && $_GET['view'] == 'changes')
{
    $objChanges = $db -> SelectLimit("SELECT `author`, `location`, `text`, `date` FROM `changelog` WHERE `lang`='".$player -> lang."' ORDER BY `id` DESC", 30);
    $i = 0;
    $arrAuthor = array();
    $arrDate = array();
    $arrText = array();
    $arrLocation = array();
    while (!$objChanges -> EOF)
    {
        $arrAuthor[$i] = $objChanges -> fields['author'];
        $arrDate[$i] = $objChanges -> fields['date'];
        $arrLocation[$i] = $objChanges -> fields['location'];
        $arrText[$i] = $objChanges -> fields['text'];
        $i ++;
        $objChanges -> MoveNext();
    }
    $objChanges -> Close();
    $smarty -> assign(array("Changesinfo" => CHANGES_INFO,
                            "Changeloc" => CHANGE_LOC,
                            "Changeauthor" => $arrAuthor,
                            "Changedate" => $arrDate,
                            "Changelocation" => $arrLocation,
                            "Changetext" => $arrText));
}

/**
* Additional options
*/
if (isset($_GET['view']) && $_GET['view'] == 'options')
{
    //Battlelogs
    if ($player->battlelog == 'Y')
    {
        $strChecked = 'checked="checked"';
	$strChecked3 = 'checked="checked"';
	$strChecked4 = '';
	$strChecked5 = '';
    }
    else
    {
        $strChecked = '';
	$strChecked3 = '';
	$strChecked4 = '';
	$strChecked5 = '';
	if ($player->battlelog == 'A')
	  {
	    $strChecked4 = 'checked="checked"';
	    $strChecked = 'checked="checked"';
	  }
	elseif ($player->battlelog == 'D')
	  {
	    $strChecked5 = 'checked="checked"';
	    $strChecked = 'checked="checked"';
	  }
    }
    //Graph bars in text mode
    if ($player -> graphbar == 'Y')
    {
        $strChecked2 = 'checked="checked"';
    }
        else
    {
        $strChecked2 = '';
    }
    //Autodrink potions
    $strChecked7 = '';
    $strChecked8 = '';
    $strChecked9 = '';
    $strChecked6 = '';
    if ($player->autodrink != 'N')
      {
	$strChecked6 = 'checked="checked"';
	switch ($player->autodrink)
	  {
	  case 'H':
	    $strChecked7 = 'checked="checked"';
	    break;
	  case 'M':
	    $strChecked8 = 'checked="checked"';
	    break;
	  case 'A':
	    $strChecked9 = 'checked="checked"';
	    break;
	  default:
	    break;
	  }
      }
    $smarty -> assign(array("Toptions" => T_OPTIONS,
                            "Tbattlelog" => T_BATTLELOG,
                            "Tgraphbar" => T_GRAPHBAR,
                            "Anext" => A_NEXT,
			    "Tonlyattack" => "Kiedy Ty atakowałeś(aś)",
			    "Tonlyattacked" => "Kiedy zostałeś(aś) zaatakowany(a)",
			    "Talways" => "Zawsze (po ataku i zaatakowaniu)",
			    "Tautodrink" => "Automatycznie używaj mikstur po każdej walce",
			    "Tautoheal" => "Do leczenia obrażeń",
			    "Tautomana" => "Do regeneracji punktów magii",
			    "Tautoall" => "Do regeneracji punktów życia oraz magii",
                            "Checked" => $strChecked,
			    "Checked3" => $strChecked3,
			    "Checked4" => $strChecked4,
			    "Checked5" => $strChecked5,
			    "Checked6" => $strChecked6,
			    "Checked7" => $strChecked7,
			    "Checked8" => $strChecked8,
			    "Checked9" => $strChecked9,
                            "Checked2" => $strChecked2));
    if (isset($_GET['step']) && $_GET['step'] == 'options')
    {
        if (isset($_POST['battlelog']))
	  {
	    if (!isset($_POST['battle']))
	      {
		$_POST['battle'] = 'Y';
	      }
	    $arrOptions = array('A', 'D', 'Y');
	    if (!in_array($_POST['battle'], $arrOptions))
	      {
		error(ERROR);
	      }
            $db -> Execute("UPDATE `players` SET `battlelog`='".$_POST['battle']."' WHERE `id`=".$player -> id);
	  }
	else
	  {
            $db -> Execute("UPDATE `players` SET `battlelog`='N' WHERE `id`=".$player -> id);
	  }
        if (isset($_POST['graphbar']))
	  {
            $db -> Execute("UPDATE `players` SET `graphbar`='Y' WHERE `id`=".$player -> id);
	  }
	else
	  {
            $db -> Execute("UPDATE `players` SET `graphbar`='N' WHERE `id`=".$player -> id);
	  }
	if (isset($_POST['autodrink']))
	  {
	    $arrOptions = array('H', 'M', 'A');
	    if (!in_array($_POST['drink'], $arrOptions))
	      {
		error(ERROR);
	      }
	    $db -> Execute("UPDATE `players` SET `autodrink`='".$_POST['drink']."' WHERE `id`=".$player -> id);
	  }
	else
	  {
	    $db -> Execute("UPDATE `players` SET `autodrink`='N' WHERE `id`=".$player -> id);
	  }
        $smarty -> assign("Message", A_SAVED);
    }
}

/**
* Account freeze
*/
if (isset($_GET['view']) && $_GET['view'] == 'freeze')
{
    $smarty -> assign(array("Freezeinfo" => FREEZE_INFO,
                            "Howmany" => HOW_MANY,
                            "Afreeze2" => A_FREEZE2));
    if (isset($_GET['step']) && $_GET['step'] == 'freeze')
    {
	checkvalue($_POST['amount']);
        if ($_POST['amount'] > 21)
        {
            error(TOO_MUCH);
        }
        $db -> Execute("UPDATE players SET freeze=".$_POST['amount']." WHERE id=".$player -> id);
        $smarty -> assign("Message", YOU_BLOCK.$_POST['amount'].NOW_EXIT);
        session_unset();
        session_destroy();
    }
}

/**
* Assign immunited
*/
if (isset ($_GET['view']) && $_GET['view'] == "immu") 
{
    $smarty -> assign(array("Immuinfo" => IMMU_INFO,
                            "Yes" => YES,
                            "No" => NO));
    if (isset ($_GET['step']) && $_GET['step'] == 'yes') 
    {
        if ($player -> immunited == 'Y') 
        {
            error (YOU_HAVE);
        }
        if ($player -> clas == '') 
        {
            error (YOU_NOT_CLASS);
        }
        $db -> Execute("UPDATE players SET immu='Y' WHERE id=".$player -> id);
        $smarty -> assign(array("Immuselect" => IMMU_SELECT,
            "Here" => HERE,
            "Immuselect2" => IMMU_SELECT2));
    }
}

/**
* Player reset
*/
if (isset ($_GET['view']) && $_GET['view'] == "reset") 
{
    $smarty -> assign(array("Resetinfo" => RESET_INFO,
                            "Yes" => YES,
                            "No" => NO));
    if (isset ($_GET['step']) && $_GET['step'] == 'make') 
    {
        $code = rand(1,1000000);
        $message = MESSAGE1." ".$gamename." (".$player -> user." ".ID.": ".$player -> id.") ".MESSAGE2." ".$gameadress."/preset.php?id=".$player -> id."&code=".$code." ".MESSAGE4." ".$gameadress."/preset.php?id=".$player -> id." ".MESSAGE4." ".$adminname;
        $adress = $_SESSION['email'];
        $subject = MSG_TITLE." ".$gamename;
        require_once('mailer/mailerconfig.php');
        if (!$mail -> Send()) 
        {
           error(E_MAIL.":<br /> ".$mail -> ErrorInfo);
        }
        $db -> Execute("INSERT INTO reset (player, code) VALUES(".$player -> id.",".$code.")") or error(E_DB);
        $smarty -> assign("Resetselect", RESET_SELECT);
    }
}

/**
* Avatar options
*/
if (isset($_GET['view']) && $_GET['view'] == "avatar") 
{
    $smarty -> assign(array("Avatarinfo" => AVATAR_INFO,
                            "Delete" => A_DELETE,
                            "Afilename" => FILE_NAME,
                            "Aselect" => A_SELECT));
    $file = 'avatars/'.$player -> avatar;
    if (is_file($file)) 
    {
        $smarty -> assign (array ("Value" => $player -> avatar, "Avatar" => $file));
    }
    //Delete avatar
    if (isset($_GET['step']) && $_GET['step'] == 'usun') 
      {
	if ($_POST['av'] != $player -> avatar) 
	  {
	    error(ERROR);
	  }
        $plik = 'avatars/'.$_POST['av'];
        if (is_file($plik)) 
        {
            unlink($plik);
            $db -> Execute("UPDATE players SET avatar='' WHERE id=".$player -> id) or error(E_DB);
            error (DELETED.". <a href=\"account.php?view=avatar\">".REFRESH."</a><br />");
        } 
            else 
        {
            error (NO_FILE);
        }
    }
    //Add avatar
    if (isset($_GET['step']) && $_GET['step'] == 'dodaj') 
    {
        if (!isset($_FILES['plik']['name'])) 
        {
            error(NO_NAME);
        }
        $plik = $_FILES['plik']['tmp_name'];
        $nazwa = $_FILES['plik']['name'];
        $typ = $_FILES['plik']['type'];
	$intSize = $_FILES['plik']['size'];
	if ($intSize > 10240)
	  {
	    error("Ten avatar zajmuje za dużo miejsca (maksymalnie 10kB)!");
	  }
        if ($typ != 'image/pjpeg' && $typ != 'image/jpeg' && $typ != 'image/gif' && $typ != 'image/png') 
        {
            error (BAD_TYPE);
        }
        if ($typ == 'image/pjpeg' || $typ == 'image/jpeg') 
        {
            $liczba = rand(1,1000000);
            $newname = md5($liczba).'.jpg';
            $miejsce = 'avatars/'.$newname;
        }
        if ($typ == 'image/gif') 
        {
            $liczba = rand(1,1000000);
            $newname = md5($liczba).'.gif';
            $miejsce = 'avatars/'.$newname;
        }
        if ($typ == 'image/png') 
        {
            $liczba = rand(1,1000000);
            $newname = md5($liczba).'.png';
            $miejsce = 'avatars/'.$newname;
        }
        if (is_uploaded_file($plik)) 
        {
            if (!move_uploaded_file($plik,$miejsce)) 
            {
                error (NOT_COPY);
            }
        } 
            else 
        {
            error (ERROR);
        }
        $db -> Execute("UPDATE players SET avatar='".$newname."' WHERE id=".$player -> id) or error($db->ErrorMsg());
        error (LOADED."! <a href=\"account.php?view=avatar\">".REFRESH."</a><br />");
    }
}

/**
* Change nick for player
*/
if (isset($_GET['view']) && $_GET['view'] == "name") 
{
    $smarty -> assign(array("Change" => CHANGE,
                            "Myname" => MY_NAME));
    if (isset($_GET['step']) && $_GET['step'] == "name") 
    {
        if (empty ($_POST['name'])) 
        {
            error (EMPTY_NAME);
        } 
        $_POST['name'] = str_replace("'","",strip_tags($_POST['name']));
        if ($_POST['name'] == 'Admin' || $_POST['name'] == 'Staff' || empty($_POST['name']) || (preg_match("/\S+/", $_POST['name']) == 0)) 
        {
            error (ERROR);
        } 	
        $query = $db -> Execute("SELECT count(*) FROM `players` WHERE `user`='".$_POST['name']."'");
        $dupe = $query -> fields['count(*)'];
        $query -> Close();
        if ($dupe > 0) 
        {
            error (NAME_BLOCK);
        } 
        $db -> Execute("UPDATE `players` SET `user`='".$_POST['name']."' WHERE `id`=".$player -> id);
        error (YOU_CHANGE." <b>".$_POST['name']."</b>.");
    }
}

/**
* Change password to account
*/
if (isset($_GET['view']) && $_GET['view'] == "pass") 
{
    $smarty -> assign(array("Passinfo" => PASS_INFO,
                            "Oldpass" => OLD_PASS,
                            "Newpass" => NEW_PASS,
                            "Change" => CHANGE));
    if (isset($_GET['step']) && $_GET['step'] == "cp") 
    {
        if (empty ($_POST['np']) || empty($_POST['cp'])) 
        {
            error (EMPTY_FIELDS);
        }
        require_once('includes/verifypass.php');
        verifypass($_POST['np'],'account');     
        $_POST['np'] = str_replace("'","",strip_tags($_POST['np']));
        $_POST['cp'] = str_replace("'","",strip_tags($_POST['cp']));
        $db -> Execute("UPDATE players SET pass=MD5('".$_POST['np']."') WHERE pass=MD5('".$_POST['cp']."') AND id=".$player -> id);
        $_SESSION['pass'] = MD5($_POST['np']);       
        error (YOU_CHANGE." ".$_POST['cp']." ".ON." ".$_POST['np']);
    }
}

/**
* Profile edit
*/
if (isset($_GET['view']) && $_GET['view'] == "profile") 
{
    require_once('includes/bbcode.php');
    $profile = htmltobbcode($player -> profile);
    $smarty -> assign(array("Profileinfo" => PROFILE_INFO,
                            "Newprofile" => NEW_PROFILE,
                            "Profile2" => $profile,
                            "Change" => CHANGE));
    if (isset($_GET['step']) && $_GET['step'] == "profile") 
    {
        if (empty ($_POST['profile'])) 
        {
            error (EMPTY_FIELDS);
        }
        $_POST['profile'] = bbcodetohtml($_POST['profile']);
        $strProfile = $db -> qstr($_POST['profile'], get_magic_quotes_gpc());
        $db -> Execute("UPDATE players SET profile = ".$strProfile." WHERE id = '".$player -> id."'");
        $smarty -> assign (array("Profile" => $_POST['profile'],
                                 "Newprofile2" => NEW_PROFILE2));
    }
}

/**
* Email and comunicators edit
*/
if (isset($_GET['view']) && $_GET['view'] == 'eci') 
{
    $arrComname = array(COMM1, COMM2, COMM3, T_DELETE);
    $arrComlink = array(COMLINK1, COMLINK2, COMLINK3, T_DELETE);
    $smarty -> assign(array("Oldemail" => OLD_EMAIL,
                            "Newemail" => NEW_EMAIL,
                            "Newgg" => NEW_GG,
                            "Change" => CHANGE,
                            "Tcommunicator" => T_COMMUNICATOR,
                            "Tcom" => $arrComname,
                            "Comm" => $arrComlink));

    /**
     * Change communicator
     */
    if (isset($_GET['step']) && $_GET['step'] == "gg") 
    {
        $_POST['gg'] = str_replace("'", "", strip_tags($_POST['gg']));
        $intKey = array_search($_POST['communicator'], $arrComlink);
        if ($intKey === 0)
        {
	    checkvalue($_POST['gg']);
        }
        if ($intKey < 3)
        {
            if (empty($_POST['gg']))
            {
                error(EMPTY_FIELDS);
            }
            $strCom = $arrComname[$intKey].": <a href=\"".$_POST['communicator'].$_POST['gg']."\">".$_POST['gg']."</a>";
            $query= $db -> Execute("SELECT count(*) FROM `players` WHERE `gg`='".$strCom."'");
            $dupe = $query -> fields['count(*)'];
            $query -> Close();
            if ($dupe > 0) 
            {
                error(GG_BLOCK);
            }
        }
            else
        {
            $strCom = '';
        }
        $db -> Execute("UPDATE `players` SET `gg`='".$strCom."' WHERE `id`=".$player -> id) or error(E_DB);
        if ($intKey < 3)
        {
            error(YOU_CHANGE.$arrComname[$intKey].".");
        }
            else
        {
            error(YOU_DELETE);
        }
    }

    /**
     * Change email
     */
    if (isset($_GET['step']) && $_GET['step'] == "ce") 
    {
        if (empty($_POST["ne"]) || empty($_POST['ce'])) 
        {
            error(EMPTY_FIELDS);
        }
	$_POST['ne'] = mysql_escape_string(strip_tags($_POST['ne']));
        $_POST['ce'] = mysql_escape_string(strip_tags($_POST['ce']));
        require_once('includes/verifymail.php');
        if (MailVal($_POST['ne'], 2)) 
        {
            error(BAD_EMAIL);
        }       
        $query = $db -> Execute("SELECT `id` FROM `players` WHERE `email`='".$_POST['ne']."'");    
        if ($query -> fields['id']) 
        {
            error(EMAIL_BLOCK);
        }
        $query -> Close();
        $intNumber = substr(md5(uniqid(rand(), true)), 3, 9);
        $strLink = $gameadress."/index.php?step=newemail&code=".$intNumber."&email=".$_POST['ne'];
        $adress = $_POST['ne'];
        $message = MESSAGE_PART1.$gamename."\n".MESSAGE_PART2."\n".$strLink."\n".MESSAGE_PART3." ".$gamename."\n".$adminname;
        $subject = MESSAGE_SUBJECT.$gamename;
        require_once('mailer/mailerconfig.php');
        if (!$mail -> Send()) 
        {
            $smarty -> assign ("Error", MESSAGE_NOT_SEND." ".$mail -> ErrorInfo);
            $smarty -> display ('error.tpl');
            exit;
        }
        $db -> Execute("INSERT INTO `lost_pass` (`number`, `email`, `id`, `newemail`) VALUES('".$intNumber."', '".$_POST['ce']."', ".$player -> id.", '".$_POST['ne']."')") or $db -> ErrorMsg();
        error(YOU_CHANGE);
    }
}

/**
* Graphic style change
*/
if (isset($_GET['view']) && $_GET['view'] == 'style') 
{
    /**
    * Text style choice
    */
    $path = 'css/';
    $dir = opendir($path);
    $arrname = array();
    $i = 0;
    while ($file = readdir($dir)) 
      {
	$strExt = pathinfo($file, PATHINFO_EXTENSION);
	if ($strExt == "css")
        {
            $arrname[$i] = $file;
            $i = $i + 1;
        }
      }
    closedir($dir);    
    /**
    * Check avaible layouts
    */    
    $path = 'templates/';
    $dir = opendir($path);
    $arrname1 = array();
    $i = 0;
    while ($file = readdir($dir))
      {
        if (strrchr($file, '.') === FALSE)
        {
	  $arrname1[$i] = $file;
	  $i = $i + 1;
        }
    }
    closedir($dir);
    /**
    * Assign variables to template
    */
    $smarty -> assign(array("Sselect" => S_SELECT,
                            "Textstyle" => TEXT_STYLE,
                            "Graphstyle" => GRAPH_STYLE,
                            "Graphstyle2" => GRAPH_STYLE2,
                            "Youchange" => YOU_CHANGE,
                            "Stylename" => $arrname,
                            "Refresh" => REFRESH,
                            "Layoutname" => $arrname1));
    /**
    * If player choice text style
    */
    if (isset($_GET['step']) && $_GET['step'] == 'style') 
    {
        if (!isset($_POST['newstyle']))
        {
            error(ERROR);
        }
        $_POST['newstyle'] = strip_tags($_POST['newstyle']);
        if ($player -> graphic)
        {
            $db -> Execute("UPDATE players SET graphic='' WHERE id=".$player -> id);
        }
        $db -> Execute("UPDATE players SET style='".$_POST['newstyle']."' where id=".$player -> id);
    }
    /**
    * If player choice graphic layout
    */
    if (isset($_GET['step']) && $_GET['step'] == 'graph')
    {
        if (!isset($_POST['graphserver']))
        {
            error(ERROR);
        }
        $_POST['graphserver'] = strip_tags($_POST['graphserver']);
        $db -> Execute("UPDATE players SET graphic='".$_POST['graphserver']."' WHERE id=".$player -> id) or error(ERROR2.$path." ".$player -> id);
    }
}

/**
 * Show last gained Vallars
 */
if (isset($_GET['view']) && $_GET['view'] == 'vallars')
  {
    $objHist = $db->SelectLimit("SELECT * FROM `vallars` ORDER BY `vdate` DESC", 30);
    $arrDate = array();
    $arrReason = array();
    $arrAmount = array();
    $arrOwner = array();
    $arrOwnerID = array();
    while (!$objHist->EOF)
      {
	$tmpArr = explode(" ", $objHist->fields['vdate']);
	$arrDate[] = $tmpArr[0];
	$arrReason[] = $objHist->fields['reason'];
	$arrAmount[] = $objHist->fields['amount'];
	$arrOwnerid[] = $objHist->fields['owner'];
	$objOwner = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$objHist->fields['owner']);
	$arrOwner[] = $objOwner->fields['user'];
	$objOwner->Close();
	$objHist->MoveNext();
      }
    $objHist->Close();
    $smarty->assign(array("Info" => "Poniżej znajduje się lista ostatnich 30 akcji przyznania Vallarów za wkład w rozwój gry.",
			  "Tamount" => "Ilość przyznanych Vallarów",
			  "Treason" => "Uzasadnienie",
			  "Id" => "ID",
			  "Tgranted" => "Nagrodzony(a)",
			  "Date" => $arrDate,
			  "Owner" => $arrOwner,
			  "Ownerid" => $arrOwnerid,
			  "Amount" => $arrAmount,
			  "Reason" => $arrReason));
  }

/**
* Initialization of variables
*/
if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
}
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}

/**
* Assign variables and display page
*/
$arrStep = array('name', 'pass', 'profile', 'eci', 'avatar', 'reset', 'immu', 'style', 'lang', 'freeze', 'options', 'changes', 'vallars', 'bugreport', 'bugtrack', 'links');
$arrLinks = array(A_NAME, A_PASS, A_PROFILE, A_EMAIL, A_AVATAR, A_RESET, A_IMMU, A_STYLE, A_LANG, A_FREEZE, A_OPTIONS, A_CHANGES, "Ostatnio nagrodzeni Vallarami", A_BUGREPORT, A_BUGTRACK, A_LINKS);
$smarty -> assign (array ("View" => $_GET['view'], 
                          "Step" => $_GET['step'],
                          "Welcome" => WELCOME,
                          "Steps" => $arrStep,
                          "Links" => $arrLinks));
$smarty -> display('account.tpl');

require_once("includes/foot.php");
?>
