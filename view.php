<?php
/**
 *   File functions:
 *   View other players, steal money, astral components from other players
 *
 *   @name                 : view.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 10.08.2011
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
// $Id: view.php 840 2006-11-24 16:41:26Z thindil $

$title = "Zobacz"; 
require_once("includes/head.php");
require_once("includes/checkexp.php");

if (!isset($_GET['view'])) 
{
    error("<a href=\"\"></a>");
}

checkvalue($_GET['view']);

$view = new Player($_GET['view']);

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/view.php");

if (empty ($view -> id)) 
{
    error (NO_PLAYER);
}
$smarty -> assign (array("User" => $view -> user, 
                         "Id" => $view -> id, 
                         "Avatar" => '', 
                         "GG" => '', 
                         "Immu" => '', 
                         "Attack" => '', 
                         "Mail" => '', 
                         "Crime" => '', 
                         "Crime2" => '',
                         "Gender" => '', 
                         "Deity" => '', 
                         "IP" => '',
                         "Tseclang" => '',
                         "Trank" => T_RANK,
                         "Tlocation" => T_LOCATION,
                         "Tlastseen" => T_LAST_SEEN,
                         "Tage" => T_AGE,
                         "Tclass2" => T_CLASS2,
                         "Trace" => T_RACE,
                         "Tlevel" => T_LEVEL,
                         "Tstatus" => T_STATUS,
                         "Tmaxhp" => T_MAX_HP,
                         "Tfights" => T_FIGHTS,
                         "Tlastkill" => T_LAST_KILL,
                         "Tlastkilled" => T_LAST_KILLED,
                         "Trefs" => T_REFS,
                         "Tprofile" => T_PROFILE,
                         "Toptions" => T_OPTIONS,
                         "Tlang" => T_LANG));
$plik = 'avatars/'.$view -> avatar;
if (is_file($plik)) 
{
    require_once('includes/avatars.php');
    $arrImage = scaleavatar($plik);
    $smarty -> assign ("Avatar", "<center><img src=\"".$plik."\" width=\"".$arrImage[0]."\" height=\"".$arrImage[1]."\" /></center>");
}
if (!empty($view -> gg)) 
{
    $smarty -> assign ("GG", GG_NUMBER.$view -> gg."<br />");
}

/**
 * Select player rank
 */
require_once('includes/ranks.php');
$strRank = selectrank($view -> rank, $view -> gender);

if($view -> seclang)
{
    $smarty -> assign("Tseclang", T_SEC_LANG.$view -> seclang."<br />");
}

if ($view -> immunited == 'Y') 
{
    $smarty -> assign ("Immu", HAVE_IMMU."<br />");
}

/**
 * Name of player location
 */
$strLocation = $view -> location;
if ($view -> location == 'Altara')
{
    $strLocation = $city1;
}
if ($view -> location == 'Ardulith')
{
    $strLocation = $city2;
}

$smarty -> assign(array("Page" => $strViewpage, 
                        "Age" => $view -> age, 
                        "Race" => $view -> race, 
                        "Clas" => $view -> clas, 
                        "Rank" => $strRank, 
                        "Location" => $strLocation, 
                        "Level" => $view -> level, 
                        "Maxhp" => $view -> max_hp, 
                        "Wins" => $view -> wins, 
                        "Losses" => $view -> losses, 
                        "Lastkilled" => $view -> lastkilled, 
                        "Lastkilledby" => $view -> lastkilledby, 
                        "Profile" => $view -> profile,
                        "Lang" => $view -> lang));

if ($view -> wins || $view -> losses)
{
    $intAllfight = $view -> wins + $view -> losses;
    $fltRatio = round(($view -> wins / $intAllfight) * 100, 3);
    $smarty -> assign("Fratio", "(".$fltRatio." %)");
}
    else
{
    $smarty -> assign("Fratio", '');
}

if ($view -> gender) 
{
    if ($view -> gender == 'M') 
    {
        $gender = G_MALE;
    } 
        else 
    {
        $gender = G_FEMALE;
    }
    $smarty -> assign ("Gender", T_GENDER.": ".$gender."<br />");
}
if (!empty ($view -> deity)) 
{
    $smarty -> assign ("Deity", T_DEITY.": ".$view -> deity."<br />");
}
if ($view -> hp > 0) 
{
    $smarty -> assign ("Status", "<b>".S_LIVE."</b><br />");
} 
    else 
{
    $smarty -> assign ("Status", "<b>".S_DEAD."</b><br />");
}

$tribe = $db -> Execute("SELECT name FROM tribes WHERE id=".$view -> tribe);
if ($tribe -> fields['name']) 
{
    $smarty -> assign ("Clan", T_CLAN.": <a href=tribes.php?view=view&amp;id=".$view -> tribe.">".$tribe -> fields['name']."</a><br />".T_CLAN_RANK.": ".$view -> tribe_rank."<br />");
} 
    else 
{
    $smarty -> assign ("Clan", T_CLAN.": ".NOTHING."<br />");
}
$query = $db -> Execute("SELECT id FROM players WHERE refs=".$view -> id);
$ref = $query -> RecordCount();
$query -> Close();
$smarty -> assign ("Refs", $ref);

$objFreeze = $db -> Execute("SELECT freeze FROM players WHERE id=".$_GET['view']);

if ($player -> location == $view -> location && $view -> immunited == 'N' && $player -> immunited == 'N' && $player -> id != $view -> id) 
{
    if (!$objFreeze -> fields['freeze'])
    {
        $smarty -> assign ("Attack", "<li><a href=battle.php?battle=".$view -> id.">".A_ATTACK."</a></li>");
    }
        else
    {
        $smarty -> assign("Attack", '');
    }
}

if ($player -> id != $view -> id) 
{
    $smarty -> assign ("Mail", "<li><a href=mail.php?view=write&amp;to=".$view -> id.">".A_WRITE_PM."</a></li>");
}

if ($objFreeze -> fields['freeze'])
{
    $smarty -> assign("Tfreezed", T_FREEZED."<br />");
}
    else
{
    $smarty -> assign("Tfreezed", '');
}

if ($player -> clas == 'Złodziej' && $player -> crime > 0 && $player -> location == $view -> location && $player -> id != $view -> id) 
{
    if (!$objFreeze -> fields['freeze'])
    {
        $smarty -> assign("Crime", "<li><a href=view.php?view=".$view -> id."&amp;steal=".$view -> id.">".A_STEAL."</a></li>");
    }
        else
    {
        $smarty -> assign("Crime", '');
    }
}

$blnCrime = false;
if ($player -> clas == 'Złodziej' && $player -> id != $view -> id) 
{
    $objAstralcrime = $db -> Execute("SELECT `astralcrime` FROM `players` WHERE `id`=".$player -> id);
    if (!$objFreeze -> fields['freeze'] && $objAstralcrime -> fields['astralcrime'] == 'Y')
    {
        $smarty -> assign("Crime2", "<li><a href=\"view.php?view=".$view -> id."&amp;steal_astral=".$view -> id."\">".A_STEAL2."</a></li>");
        $blnCrime = true;
    }
        else
    {
        $smarty -> assign("Crime2", '');
    }
    $objAstralcrime -> Close();
}

if ($player -> rank == 'Admin' || $player -> rank == 'Staff') 
{
    $smarty -> assign ("IP", PLAYER_IP.$view -> ip);
}

$objViewtime = $db->Execute("SELECT `lpv` FROM `players` WHERE `id`=".$view->id);
$intLastseen = intval(($ctime - $objViewtime->fields['lpv']) / 86400);
$objViewtime->Close();
switch ($intLastseen)
  {
  case 0:
    $strSeen = "Dzisiaj";
    break;
  case 1:
    $strSeen = "Wczoraj";
    break;
  default:
    $strSeen = $intLastseen." dni temu.";
    break;
  }
$smarty->assign(array("Seen" => "Ostatnio aktywny",
		      "Lastseen" => $strSeen));

/**
* Pickpocket
*/
if (isset ($_GET['steal'])) 
{
    checkvalue($_GET['steal']);
    if ($player -> clas != 'Złodziej')
    {
        error(ERROR." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($_GET['steal'] != $_GET['view']) 
    {
        error (NO_PLAYER2." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($player -> crime <= 0) 
    {
        error (NO_CRIME." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($player -> location != $view -> location) 
    {
        error (BAD_LOCATION." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($view -> hp <= 0) 
    {
        error (IS_DEAD." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($player -> hp <= 0) 
    {
        error (YOU_DEAD." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($player -> immunited == 'Y') 
    {
        error(YOU_IMMU." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($view -> immunited == 'Y') 
    {
        error(HE_IMMU." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($objFreeze -> fields['freeze'])
    {
        error(ACCOUNT_FREEZED." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($view -> tribe > 0 && $view -> tribe == $player -> tribe)
    {
        error(SAME_CLAN." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($view -> age < 4)
    {
        error(TOO_YOUNG." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    $roll = rand (1, $view -> level);
    /**
     * Add bonus from bless
     */
    $strBless = FALSE;
    $objBless = $db -> Execute("SELECT `bless`, `blessval` FROM `players` WHERE `id`=".$player -> id);
    if ($objBless -> fields['bless'] == 'inteli')
    {
        $player -> inteli = $player -> inteli + $objBless -> fields['blessval'];
        $strBless = 'inteli';
    }
    if ($objBless -> fields['bless'] == 'agility')
    {
        $player -> agility = $player -> agility + $objBless -> fields['blessval'];
        $strBless = 'agility';
    }
    $objBless -> Close();
    if ($strBless)
    {
        $db -> Execute("UPDATE `players` SET `bless`='', `blessval`=0 WHERE `id`=".$player -> id);
    }
    $objBless = $db -> Execute("SELECT `bless`, `blessval` FROM `players` WHERE `id`=".$view -> id);
    if ($objBless -> fields['bless'] == 'inteli')
    {
        $view -> inteli = $view -> inteli + $objBless -> fields['blessval'];
    }
    if ($objBless -> fields['bless'] == 'agility')
    {
        $view -> agility = $view -> agility + $objBless -> fields['blessval'];
    }
    $objBless -> Close();

    /**
     * Add bonus from rings
     */
    $arrEquip = $player -> equipment();
    $arrRings = array(R_AGI, R_INT);
    $arrStat = array('agility', 'inteli');
    if ($arrEquip[9][0])
    {
        $arrRingtype = explode(" ", $arrEquip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        if ($intKey != NULL)
        {
            $strStat = $arrStat[$intKey];
            $player -> $strStat = $player -> $strStat + $arrEquip[9][2];
        }
    }
    if ($arrEquip[10][0])
    {
        $arrRingtype = explode(" ", $arrEquip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        if ($intKey != NULL)
        {
            $strStat = $arrStat[$intKey];
            $player -> $strStat = $player -> $strStat + $arrEquip[10][2];
        }
    }

    $chance = ($player -> agility + $player -> inteli) - ($view -> agility + $view -> inteli + $roll);
    $strDate = $db -> DBDate($newdate);
    if ($chance < 1) 
    {
        $cost = 1000 * $player -> level;
        $expgain = ceil($view -> level / 10);
        checkexp($player -> exp,$expgain,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'',0);
        if ($player -> location != 'Lochy') 
        {
            $db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `crime`=`crime`-1 WHERE `id`=".$player -> id);
            $db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.",'".VERDICT."',7,".$cost.",'".$data."')");                
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$player -> id.",'".YOU_IN_JAIL.$cost.".', ".$strDate.")");
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$view -> id.",'".YOU_CATCH."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.YOU_CATCH2."', ".$strDate.")");
            error ("<br />".YOU_JAILED." (<b><a href=\"view.php?view=".$_GET['view']."\">".BACK."</a></b>)");
        } 
            else 
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$view -> id.",'".WHEN_YOU."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.".', ".$strDate.")");         
            $db -> Execute("UPDATE `players` SET `crime`=`crime`-1 WHERE `id`=".$player-> id);
            error ("<br />".YOU_TRY_IN." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");          
        }
    }
    if ($chance > 0 && $chance < 50) 
    {
        $db -> Execute("UPDATE `players` SET `crime`=`crime`-1 WHERE `id`=".$player -> id);
        if ( $view -> credits > 0) 
        {
            $lost = ceil($view -> credits / 10);
            $expgain = $view -> level;
            checkexp($player -> exp,$expgain,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'',0);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$lost." WHERE `id`=".$view -> id);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$lost." WHERE `id`=".$player -> id);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$view -> id.",'".YOU_CATCH."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.NOT_CATCH.$lost.GOLD_COINS."', ".$strDate.")");
            error ("<br />".WHEN_YOU2.$lost.WHEN_YOU21." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        } 
            else 
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$view -> id.",'".YOU_CATCH."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.EMPTY_BAG."', ".$strDate.")");
            error ("<br />".EMPTY_BAG2." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        }
    }
    if ($chance > 49) 
    {
        $db -> Execute("UPDATE `players` SET `crime`=`crime`-1 WHERE `id`=".$player -> id);
        if ( $view -> credits > 0) 
        {
            $lost = ceil($view -> credits / 10);
            $expgain = ceil($view -> level * 10);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$lost." WHERE `id`=".$view -> id);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$lost." WHERE `id`=".$player -> id);
            checkexp($player -> exp,$expgain,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'',0);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$view -> id.",'".YOU_CRIME.$lost.YOU_CRIME2."', ".$strDate.")");
            error ("<br />".SUCCESS.$lost.GOLD_COINS2." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        } 
            else 
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$view -> id.",'".EMPTY_BAG3."', ".$strDate.")");
            error ("<br />".EMPTY_BAG4." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        }
    }
}

/**
 * Steal astral components
 */
if (isset($_GET['steal_astral']))
{
    checkvalue($_GET['steal_astral']);
    if ($player -> clas != 'Złodziej' || $player -> location == 'Lochy')
    {
        error(ERROR." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($_GET['steal_astral'] != $_GET['view']) 
    {
        error (NO_PLAYER2." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if (!$blnCrime) 
    {
        error (NO_CRIME." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($player -> hp <= 0) 
    {
        error (YOU_DEAD." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($view -> tribe > 0 && $view -> tribe == $player -> tribe)
    {
        error(SAME_CLAN." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }

    require_once('includes/astralsteal.php');
    astralsteal($_GET['view'], 'V');
}

if ($objFreeze -> fields['freeze'])
{
    $objFreeze -> Close();
}

/**
 * Display page
 */
$smarty -> display ('view.tpl');
require_once("includes/foot.php");
?>
