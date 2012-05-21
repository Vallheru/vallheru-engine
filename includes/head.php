<?php
/**
 *   File functions:
 *   Main file of game, compress site, get information about player from database and more
 *
 *   @name                 : head.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 21.05.2012
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

/**
* GZIP compression
*/
$compress = FALSE;
if ($compress)
  {
    if (!ob_start("ob_gzhandler"))
      {
	ob_start();
      }
  }

$start_time = microtime(true);

require_once ('includes/config.php');
require_once("languages/".$lang."/head.php");

require_once('includes/sessions.php');
require_once 'libs/Smarty.class.php';
require_once('class/player_class.php');

$smarty = new Smarty;

$smarty-> compile_check = true;

$numquery = 0;
$sqltime = 0;

function &CountExecs($db, $sql, $inputarray)
{
  global $numquery;
  global $sqltime;
  if (!is_array($inputarray)) 
    {
      $numquery++;
    }
  else if (is_array(reset($inputarray))) 
    {
      $numquery += sizeof($inputarray);
    }
  else 
    {
      $numquery++;
    }
  $db->fnExecute = false;
  $stime = microtime(true);
  $result = $db->Execute($sql, $inputarray);
  $sqltime += microtime(true) - $stime;
  $db->fnExecute = 'CountExecs';
  return $result;
}
$db->fnExecute = 'CountExecs';

/**
* Errors reporting level
*/
error_reporting(E_ALL);
ini_set('display_errors','On');

/**
* function to catch errors and write it to bugtrack
*/
function catcherror($errortype, $errorinfo, $errorfile, $errorline) 
{
    global $db;
    global $smarty;
    $reported = 0;
    $file = explode("/", $errorfile);
    $elements = count($file);
    $numfile = $elements - 1;
    if (!isset($_SERVER['HTTP_REFERER']))
    {
        $_SERVER['HTTP_REFERER'] = '';
    }
    $referer = explode("/", $_SERVER['HTTP_REFERER']);
    $elements1 = count($referer);
    $numrefer = $elements1 - 1;
    $errorinfo = $db->qstr($errorinfo, get_magic_quotes_gpc());
    $objtest = $db -> Execute("SELECT `id` FROM `bugtrack` WHERE `file`='".$file[$numfile]."' AND `line`=".$errorline." AND `info`=".$errorinfo." AND `type`=".$errortype." AND `referer`='".$referer[$numrefer]."'");
    if ($objtest -> fields['id'] > 0)
      {
	$db -> Execute("UPDATE `bugtrack` SET `amount`=`amount`+1 WHERE `id`=".$objtest -> fields['id']);
      }
    else
      {
        $db -> Execute("INSERT INTO `bugtrack` (`type`, `info`, `file`, `line`, `referer`) VALUES(".$errortype.", ".$errorinfo.", '".$file[$numfile]."', ".$errorline.", '".$referer[$numrefer]."')");
      }
    $objtest -> Close();
    if ($errortype == E_USER_ERROR || $errortype == E_ERROR) 
      {
        $smarty -> assign("Message", E_ERRORS);
        $smarty -> display('error1.tpl');
        exit;
      } 
}

/**
* set catching errors
*/
set_error_handler('catcherror');

$smarty -> assign("Charset", CHARSET);

/**
* Login to game and set session variables
*/
if (isset ($_POST['pass']) && $title == 'Wieści') 
{
    if (!$_POST['email'] || !$_POST['pass']) 
    {
        $smarty -> assign (array("Error" => EMPTY_LOGIN, 
            "Gamename" => $gamename, 
            "Meta" => ''));
        $smarty -> display ('error.tpl');
        exit;
    }
    $pass = MD5($_POST['pass']);
    $strEmail = $db -> qstr($_POST['email'], get_magic_quotes_gpc());
    $query = $db -> Execute("SELECT `id`, `user`, `email`, `rank`, `freeze` FROM `players` WHERE `email`=".$strEmail." AND `pass`='".$pass."'");
    $logres = $query -> RecordCount();

    /**
     * Check for ban
     */
    $banned = false;
    $arrBans = array('IP', 'ID', 'nick', 'mailadres');
    $arrAmount = array($_SERVER['REMOTE_ADDR'], $query -> fields['id'], $query -> fields['user'], $query -> fields['email']);
    $i = 0;
    foreach ($arrBans as $strBan)
    {
        $objBan = $db -> Execute("SELECT `type` FROM `ban` WHERE `type`='".$strBan."' AND `amount`='".$arrAmount[$i]."'");
        if ($objBan -> fields['type'])
        {
            $banned = true;
            $objBan -> Close();
            break;
        }
        $i ++;
    }
    if ($banned) 
    {
        $smarty -> assign (array("Error" => BANNED, 
                                 "Gamename" => $gamename, 
                                 "Meta" => ''));
        $smarty -> display ('error.tpl');
        exit;
    }
    if ($logres <= 0) 
    {
        $smarty -> assign (array("Error" => E_LOGIN, 
                                 "Gamename" => $gamename, 
                                 "Meta" => ''));
        $smarty -> display ('error.tpl');
        exit;
    } 
        else 
    {
        $intCtime = time() - 180;
        $objQuery = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `rank`!='Admin' AND rank!='Staff' AND `lpv`>=".$intCtime);
        $numo = $objQuery -> fields['count(`id`)'];
        $objQuery -> Close();
        if ($numo >= $pllimit && $query -> fields['rank'] != 'Admin' && $query -> fields['rank'] != 'Staff' && $query -> fields['rank'] != "Królewski Błazen") 
        {
            $smarty -> assign(array("Error" => MAX_PLAYERS, 
                                    "Gamename" => $gamename, 
                                    "Meta" => ''));
            $smarty -> display('error.tpl');
            exit;
        }
        if ($query -> fields['freeze'])
        {
            $smarty -> assign(array("Error" => ACCOUNT_BLOCKED.$query -> fields['freeze'].ACCOUNT_DAYS,
                                    "Gamename" => $gamename,
                                    "Meta" =>  ''));
            $smarty -> display('error.tpl');
            exit;
        }
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['pass'] = MD5($_POST['pass']);
        $db -> Execute("UPDATE players SET logins=logins+1, rest='N' WHERE email=".$strEmail."");
        $objFight = $db -> Execute("SELECT `fight` FROM `players` WHERE `email`=".$strEmail);
        if ($objFight -> fields['fight'])
        {
            $db -> Execute("UPDATE `players` SET `hp`=0, `fight`=0, `energy`=`energy`-1 WHERE `email`=".$strEmail);
        }
        $objFight -> Close();
        $query -> Close();
    }
}

/**
* End session
*/
if (empty($_SESSION['email']) || empty($_SESSION['pass'])) 
{
        $smarty -> assign (array("Error" => E_SESSIONS, 
                                 "Gamename" => $gamename, 
                                 "Meta" => ''));
        $smarty -> display ('error.tpl');
        exit;
}

date_default_timezone_set('Europe/Warsaw');
$time = date("H:i:s");
$data = date("y-m-d");
$arrtemp = array($data, $time);
$newdate = implode(" ",$arrtemp);

$stat = $db -> Execute("SELECT `id` FROM `players` WHERE `email`='".$_SESSION['email']."' AND `pass`='".$_SESSION['pass']."'");

if (!$stat -> fields['id']) 
{
    $smarty -> assign (array("Error" => E_PLAYER, 
                             "Gamename" => $gamename, 
                             "Meta" => ''));
    $smarty -> display ('error.tpl');
    exit;
}

$ctime = time();
$ip = $_SERVER['REMOTE_ADDR'];
$title = strip_tags($title);
$db -> Execute("UPDATE `players` SET `lpv`=".$ctime.", `ip`='".$ip."', `page`='".$title."' WHERE `id`=".$stat -> fields['id']);

$player = new Player($stat -> fields['id']);
$stat -> Close();

$objOpen = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='open'");
if ($objOpen -> fields['value'] == 'N' && $player -> rank != 'Admin') 
{
    $objReason = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='close_reason'");
    $smarty -> assign (array("Error" => REASON."<br />".$objReason -> fields['value'], 
                             "Gamename" => $gamename, 
                             "Meta" => ''));
    $objReason -> Close();
    $smarty -> display ('error.tpl');
    exit;
}
$objOpen -> Close();

/**
* Get the localization for game
*/
require_once("languages/".$lang."/head1.php");

if ($player -> graphic != '') 
{
    $smarty -> template_dir = "./templates/".$player -> graphic;
    $smarty -> compile_dir = "./templates_c/".$player -> graphic;
}   
    else
{
    $smarty -> template_dir = './templates';
    $smarty -> compile_dir = './templates_c';
}

$arrLevel = array(100, 200, 300, 400, 500, 600, 700, 800, 900, 100);
$arrPow = array(50, 250, 500, 1000, 2500, 5000, 10000, 25000, 50000, 100000);
$intLevels = 0;
foreach ($arrLevel as $intLevel)
{
    if ($player -> level < $intLevel)
    {
        $expn = (pow($player -> level,2) * $arrPow[$intLevels]);
        break;
    }
    $intLevels ++;
}

$pct = (($player -> exp / $expn) * 100);
$pct = round($pct,"0");

$query = $db -> Execute("SELECT count(`id`) FROM `log` WHERE `unread`='F' AND `owner`=".$player -> id);
$numlog = $query -> fields['count(`id`)'];
$query -> Close();

/**
 * Graph bars
 */
if ($player -> graphic != '' || $player -> graphbar == 'Y') 
{
    $intExpperc = $pct;
    if ($pct > 97)
    {
        $intExpperc = 97;
    }
    $intVial = 97 - $intExpperc;
    $intPerhealth = (($player -> hp / $player -> max_hp) * 100);
    $intPerhealth = round($intPerhealth, '0');
    $strHealth = $intPerhealth;
    if ($intPerhealth > 97)
    {
        $intPerhealth = 97;
    }
    $intVial2 = 97 - $intPerhealth;
    $cape = $db -> Execute("SELECT `power` FROM `equipment` WHERE `owner`=".$player -> id." AND `type`='C' AND `status`='E'");
    $maxmana = ($player -> inteli + $player -> wisdom);
    $maxmana = $maxmana + (($cape -> fields['power'] / 100) * $maxmana);
    $cape -> Close();
    $intPermana = (($player -> mana / $maxmana) * 100);
    $intPermana = round($intPermana, '0');
    $strMana = $intPermana;
    if ($intPermana > 97)
    {
        $intPermana = 97;
    }
    $intVial3 = 97 - $intPermana;
    $smarty -> assign(array("Barsize" => $intPerhealth,
                            "Healthper" => $strHealth,
                            "Barsize2" => $intPermana,
                            "Manaper" => $strMana,
                            "Expper" => $intExpperc,
                            "Vial" => $intVial,
                            "Vial2" => $intVial2,
                            "Vial3" => $intVial3));
}

$arrFilename = explode("/", $_SERVER['PHP_SELF']);
$intKey = count($arrFilename) - 1;
$strFilename = $arrFilename[$intKey];
$strFname = substr($strFilename, 0, -4);
//Tribe tags
$objTags = $db->Execute("SELECT `id`, `prefix`, `suffix` FROM `tribes`");
$arrTags = array(array('', ''));
while(!$objTags->EOF)
  {
    $arrTags[$objTags->fields['id']] = array($objTags->fields['prefix'], $objTags->fields['suffix']);
    $objTags->MoveNext();
  }
$objTags->Close();
$strUsername = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];
$smarty -> assign (array ("Time" => $time,
                          "Date" => $newdate,
                          "Title" => $title1,
                          "Name" =>  $strUsername,
                          "Id" => $player -> id,
                          "Level" => $player -> level,
                          "Exp" => $player -> exp,
                          "Expneed" => $expn,
                          "Percent" => $pct,
                          "Health" => $player -> hp,
                          "Maxhealth" => $player -> max_hp,
                          "Mana" => $player -> mana,
                          "Energy" => $player -> energy,
                          "Maxenergy" => $player -> max_energy,
                          "Gold" => $player -> credits,
                          "Bank" => $player -> bank,
                          "Mithril" => $player -> platinum,
                          "Referals" => $player->vallars,
                          "Numlog" => $numlog,
                          "Style" => $player -> style,
                          "Graphbar" => $player -> graphbar,
                          "Gamename" => $gamename,
			  "Gameadress" => $gameadress,
                          "Hospital" => '',
                          "Battle" => '',
                          "Tribe" => '',
                          "Lbank" => '',
                          "Special" => '',
                          "Tforum" => '',
                          "Spells" => '',
                          "Location" => '',
                          "Plevel" => LEVEL,
                          "Exppts" => EXP_PTS,
                          "Healthpts" => HEALTH_PTS,
                          "Manapts" => MANA_PTS,
                          "Energypts" => ENERGY_PTS,
                          "Goldinhand" => GOLD_IN_HAND,
                          "Goldinbank" => GOLD_IN_BANK,
                          "Hmithril" => MITHRIL,
                          "Vallars" => VALLARS,
                          "Navigation" => NAVIGATION,
                          "Gametime" => GAME_TIME,
                          "Nstatistics" => N_STATISTICS,
                          "Nitems" => N_ITEMS,
                          "Nequipment" => N_EQUIPMENT,
                          "Nlog" => N_LOG,
                          "Nnotes" => N_NOTES,
                          "Npost" => N_POST,
                          "Nforums" => N_FORUMS,
                          "Ninn" => N_INN,
                          "Noptions" => N_OPTIONS,
                          "Nlogout" => N_LOGOUT,
                          "Nhelp" => N_HELP,
                          "Nmap" => N_MAP,
                          "Stephead" => '',
			  "Filename" => $strFname,
			  "Message" => '',
			  "Reportbug" => 'Zgłoś błąd'));

if (isset($_GET['step']) && $strFilename == 'newspaper.php')
{
    $arrFile = array('');
    $smarty -> assign(array("Stephead" => $_GET['step'],
                            "Linksfile" => $arrFile));
}
 else
{
    $objLinks = $db -> Execute("SELECT `id`, `file`, `text` FROM `links` WHERE `owner`=".$player -> id." ORDER BY `number` ASC");
    $arrFile = array('');
    $arrText = array('');
    $intLinks = 0;
    if ($objLinks -> fields['id'])
    {
        while (!$objLinks -> EOF)
        {
            $arrFile[$intLinks] = $objLinks -> fields['file'];
            $arrText[$intLinks] = $objLinks -> fields['text'];
            $intLinks ++;
            $objLinks -> MoveNext();
        }
        $objLinks -> Close();
    }
    $smarty -> assign(array("Linksfile" => $arrFile,
                            "Linkstext" => $arrText,
                            "Linksnum" => $intLinks));
}

if ($player -> clas != 'Barbarzyńca') 
{
    $smarty -> assign ("Spells", "<li><a href=\"czary.php\">".SPELLS_BOOK."</a></li>");
}

switch($player->location)
  {
  case 'Altara':
  case 'Ardulith':
    if ($player -> hp > 0) 
      {
        $objHospass = $db -> Execute("SELECT `hospass` FROM `tribes` WHERE `id`=".$player -> tribe);
        if ($objHospass -> fields['hospass'] == "Y") 
	  {
            $healneed = ($player -> max_hp - $player -> hp);
	  }
        else
	  {
            $healneed = ($player -> max_hp - $player -> hp) * $player->level;
	  }
        $objHospass -> Close();
      } 
    else 
      {
        $healneed = (50 * $player -> level);
      }
    if ($healneed < 0)
      {
        $healneed = 0;
      }
    $smarty -> assign(array("Location" => "<li><a href=\"city.php\">".CITY."</a></li>",
                            "Battle" => "<li><a href=\"battle.php\">".B_ARENA."</a></li>",
                            "Hospital" => "<li><a href=\"hospital.php\">".HOSPITAL."</a> [".$healneed." sz]</li>",
                            "Lbank" => "<li><a href=\"bank.php\">".BANK."</a><br /><br /></li>"));
    if ($player -> tribe) 
      {
        $smarty -> assign ("Tribe", "<li><a href=\"tribes.php?view=my\">".MY_TRIBE."</a></li>");
      }
    break;
  case 'Podróż':
    $test = $db -> Execute("SELECT quest FROM questaction WHERE player=".$player -> id." AND action!='end'");
    if ($test -> fields['quest'] != 0) 
      {
        $qlocation = $db -> Execute("SELECT location FROM quests WHERE qid=".$test -> fields['quest']);
        $smarty -> assign("Location", "<li><a href=\"".$qlocation -> fields['location']."?step=quest\">".RETURN_TO."</a></li>");
        $qlocation -> Close();
      }
    else
      {
        if (!isset($_GET['step']))
	  {
	    if (isset($_SESSION['travel2']))
	      {
		$_GET['step'] = $_SESSION['travel2'];
	      }
	  }
	if (!isset($_GET['akcja']))
	  {
	    if (isset($_SESSION['travel']))
	      {
		$_GET['akcja'] = $_SESSION['travel'];
	      }
	  }
	if (isset($_GET['akcja']))
	  {
	    $smarty -> assign("Location", "<li><a href=\"travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']."\">".RETURN_TO2."</a></li>");
	  }
	else
	  {
	    $db->Execute("UPDATE `players` SET `miejsce`='Altara' WHERE `id`=".$player->id);
	    $player->location = 'Altara';
	    $smarty -> assign("Location", "<li><a href=\"city.php\">".$city1."</a></li>");
	  }
      }
    $test -> Close();
    break;
  case 'Góry':
    if ($player -> fight == 0) 
      {
        $smarty -> assign ("Location", "<li><a href=\"gory.php\">".MOUNTAINS."</a></li>");
      } 
    else 
      {
        $smarty -> assign ("Location", "<li><a href=\"explore.php?akcja=gory\">".MOUNTAINS."</a></li>");
      }
    break;
  case 'Las':
    if  ($player -> fight == 0) 
      {
        $smarty -> assign ("Location", "<li><a href=\"las.php\">".FOREST."</a></li>");
      } 
    else 
      {
        $smarty -> assign ("Location", "<li><a href=\"explore.php?akcja=las\">".FOREST."</a></li>");
      }
    break;
  case 'Lochy':
    $smarty -> assign ("Location", "<li><a href=\"jail.php\">".JAIL."</a></li>");
    break;
  case 'Portal':
    $smarty -> assign ("Location", "<li><a href=\"portal.php\">".PORTAL."</a></li>");
    break;
  case 'Astralny plan':
    $objFight = $db -> Execute("SELECT `fight` FROM `players` WHERE `id`=".$player -> id);
    $smarty -> assign ("Location", "<li><a href=\"portals.php?step=".$objFight -> fields['fight']."\">".ASTRAL_PLAN."</a></li>");
    $objFight -> Close();
    break;
  case 'Przygoda':
    $smarty->assign("Location", '<li><a href="mission.php">Przygoda</a></li>');
    break;
  default:
    break;
  }

$unread = $db -> Execute("SELECT `topic` FROM `mail` WHERE `owner`=".$player -> id." AND `unread`='F' ORDER BY `id` DESC");
$intUnreadmails = $unread->RecordCount();
if ($intUnreadmails)
  {
    $strUnread = '<a href="mail.php?read='.$unread->fields['topic'].'"><blink>'.$intUnreadmails.'</blink></a>';
    $strMailadd = '?view=inbox';
  }
 else
   {
     $strUnread = $intUnreadmails;
     $strMailadd = '';
   }
$unread -> Close();

/**
* Delete sessions variables when player exit forums
*/
if (isset($_SESSION['forums']) && ($strFilename != "forums.php"))
  {
    $db -> Execute("UPDATE `players` SET `forum_time`=".$ctime." WHERE `id`=".$player -> id);
    unset($_SESSION['forums']);
  }

if (isset($_SESSION['tforums']) && ($strFilename != "tforums.php"))
{
    $db -> Execute("UPDATE `players` SET `tforum_time`=".$ctime." WHERE `id`=".$player -> id);
    unset($_SESSION['tforums']);
}

/**
 * Unread posts on forum
 */
if (!isset($_SESSION['forums']))
  {
    $intForums = $player->forumtime;
  }
else
  {
    $intForums = $_SESSION['forums'];
  }
if ($player->forumcats == 'All')
  {
    $objFcat = $db->Execute("SELECT `id` FROM `categories` WHERE `perm_visit` LIKE 'All;'");
    $arrForums = array();
    while (!$objFcat -> EOF) 
      {
	$arrForums[] = $objFcat->fields['id'];
	$objFcat->MoveNext();
      }
    $objFcat->Close();
    if (count($arrForums) > 0)
      {
	$objFunread = $db->Execute("SELECT count(`id`) FROM `topics` WHERE `w_time`>".$intForums." AND `cat_id` IN(".implode(",", $arrForums).")") or die($db->ErrorMsg());
	$intFunread = $objFunread->fields['count(`id`)'];
	$objFunread->Close();
      }
  }
else
  {
    $arrForums = explode(",", $player->forumcats);
    $objFunread = $db->Execute("SELECT count(`id`) FROM `topics` WHERE `w_time`>".$intForums." AND `cat_id` IN(".$player->forumcats.")") or die($db->ErrorMsg());
    $intFunread = $objFunread->fields['count(`id`)'];
    $objFunread->Close();
  }
if (!isset($intFunread))
  {
    $intFunread = 0;
  }
if ($intFunread == 0)
  {
    $strFunread = '[0]';
  }
else
  {
    $strFunread = '[<a href="forums.php?view=newposts">'.$intFunread.'</a>]';
  }

if ($player->tribe) 
  {
    $strTforum = "<li><a href=\"tforums.php?view=topics\">".T_FORUM."</a>";
    if (!isset($_SESSION['tforums']))
      {
	$intForums2 = $player->tforumtime;
      }
    else
      {
	$intForums2 = $_SESSION['tforums'];
      }
    $arrForums2 = array();
    $objFunread2 = $db->Execute("SELECT count(`id`) FROM `tribe_topics` WHERE `tribe`=".$player->tribe." AND `w_time`>".$intForums2) or die($db->ErrorMsg());
    $intFunread2 = $objFunread2->fields['count(`id`)'];
    $objFunread2->Close();
    if ($intFunread2 == 0)
      {
	$strTforum .= ' [0]</li>';
      }
    else
      {
	$strTforum .= ' [<a href="tforums.php?view=newposts">'.$intFunread2.'</a>]</li>';
      }
    $smarty->assign("Tforum", $strTforum);
  }

$intCtime = time() - 180;
/**
 * Chatroom
 */
if ($player->room)
  {
    $objQuery = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `page`='Pokój w karczmie' AND `lpv`>=".$intCtime." AND `room`=".$player->room);
    $numoc = $objQuery -> fields['count(`id`)'];
    $smarty->assign("Room", '<li><a href="room.php">Pokój w karczmie ['.$numoc.']</a><br /><br /></li>');
  }
else
  {
    $smarty->assign("Room", '<br /><br />');
  }

$objQuery = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `page`='Chat' AND `lpv`>=".$intCtime);
$numoc = $objQuery -> fields['count(`id`)'];
$objQuery -> Close();
$smarty -> assign(array("Players" => $numoc,
			"Unread" => $strUnread,
			"Funread" => $strFunread,
			"Mailadd" => $strMailadd));

switch ($player->rank)
  {
  case 'Admin':
    $smarty -> assign ("Special", "<li><a href=\"admin.php\">".KING."</a></li>");
    break;
  case 'Staff':
    $smarty -> assign ("Special", "<li><a href=\"staff.php\">".PRINCE."</a></li>");
    break;
  case 'Sędzia':
    $smarty -> assign ("Special", "<li><a href=\"sedzia.php\">".JUDGE."</a></li>");
    break;
  default:
    break;
  }

$smarty -> display ('header.tpl');

/**
* Function which exit from script when error is reported
*/
function error($text) 
{
    global $smarty;
    global $db;
    global $start_time;
    global $player;
    global $numquery;
    global $compress;
    global $sqltime;
    global $phptime;
    global $gamename;
    global $arrTags;
    if (strpos($text, "<a href") === FALSE)
    {
        $text = $text." (<a href=\"".$_SERVER['PHP_SELF']."\">".BACK."</a>)";
    }
    $smarty -> assign(array("Message" => $text, 
                            "Gamename" => $gamename, 
                            "Meta" => ''));
    $smarty -> display ('error1.tpl');
    require_once("includes/foot.php");
    exit;
}

/**
 * Function show message
 */
function message($strType, $strText, $strLink = '')
{
  global $smarty;
  global $player;

  if (strlen($strLink) != 0)
    {
      $strText = $strText.' '.$strLink;
    }
  if ($player->graphic != '' || $player->style == 'light.css')
    {
      $strMessage = '<div class="'.$strType.'">'.$strText.'</div>';
    }
  else
    {
      $strMessage = '<div style="text-align:center;font-weight:bold;text-decoration:underline;margin:10px;">'.$strText.'</div>';
    }
  $smarty->assign("Message", $strMessage);
  $smarty->display('message.tpl');
}

/**
 * Function check for integer overflow in forms
 */ 
function integercheck($strField)
{
    $intStrlen = strlen($strField);
    if ($intStrlen > 15)
    {
        error("Zapomnij o tym.");
    } 
}

/**
 * Function check if value is valid.
 */
function checkvalue($value)
{
  if (!is_numeric($value))
    {
      error("Zapomnij o tym.");
    }
  $value = intval($value);
  if ($value < 1)
    {
      error("Zapomnij o tym.");
    }
}

/**
* Delete session when player escape from fight
*/
$arrTitles = array('Arena Walk', 'Labirynt', 'Portal', 'Astralny plan', 'Gildia Łowców', 'Prefektura Gwardii', 'Przygoda');
$arrLocations = array('Altara', 'Ardulith', 'Portal', 'Astralny plan', 'Przygoda');
if ($player -> fight != 0 && (!in_array($title, $arrTitles)) && (in_array($player -> location, $arrLocations)) && $player->revent != 8) 
{
    $db -> Execute("UPDATE `players` SET `hp`=0, `fight`=0, `bless`='', `blessval`=0 WHERE `id`=".$player -> id) or die($db -> ErrorMsg());
    if (!isset($_SESSION['amount']))
    {
        $_SESSION['amount'] = 1;
    }
    for ($k = 0; $k < $_SESSION['amount']; $k ++) 
    {
        $strIndex = "mon".$k;
        unset($_SESSION[$strIndex]);
    }
    unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['amount'], $_SESSION['dodge'], $_SESSION['miss']);
    error (ESCAPE);
}

/**
 * Random event - check if happen
 */
if ($player->revent == 0 && ($player->location == 'Altara' || $player->location == 'Ardulith') && $player->fight == 0 && (!in_array($title, array('Karczma', 'Poczta', 'Forum', 'Forum klanu', 'Opcje konta'))))
  {
    $intRoll = rand(1, 500);
    if ($intRoll == 1)
      {
	require_once('includes/revent.php');
      }
  }

/**
 * Random event - accepting
 */
elseif ($player->revent == 1 || $player->revent == 6)
  {
    require_once('includes/revent.php');
  }

/**
 * Random event - finish it
 */
elseif ($player->revent == 2 && ($player->location == 'Altara' || $player->location == 'Ardulith') && $player->fight == 0)
  {
    $objRevent = $db->Execute("SELECT `location` FROM `revent` WHERE `pid`=".$player->id);
    $arrLoc = explode(';', $objRevent->fields['location']);
    $objRevent->Close();
    if ($arrLoc[1] == $title && $player->location == $arrLoc[0])
      {
	require_once('includes/revent.php');
      }
  }

/**
 * Random event - fight
 */
if ($player->revent == 8)
  {
    require_once('includes/revent.php');
  }
?>

