<?php
/**
 *   File functions:
 *   Battle Arena - figth between players and player vs monsters
 *
 *   @name                 : battle.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 07.08.2011
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

$title = "Arena Walk";
require_once("includes/head.php");
require_once("includes/funkcje.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/battle.php");

global $runda;
global $number;
global $newdate;
global $smarty;
global $db;

if (!isset($_GET['action']) && !isset($_GET['battle']))
{
    $smarty -> assign(array("Battleinfo" => BATTLE_INFO,
                            "Ashowalive" => A_SHOW_ALIVE,
                            "Ashowlevel" => A_SHOW_LEVEL,
                            "Ashowmonster" => A_SHOW_MONSTER));
}

/**
* Show players on this same level which have a player
*/
if (isset ($_GET['action']) && $_GET['action'] == 'showalive') 
{
    $elist = $db -> SelectLimit("SELECT id, user, rank, tribe FROM players WHERE level=".$player -> level." AND hp>0 AND miejsce='".$player -> location."' AND id!=".$player -> id." AND immu='N' AND rasa!='' AND klasa!='' AND rest='N' AND freeze=0", 50);
    $arrid = array();
    $arrname = array();
    $arrrank = array();
    $arrtribe = array();
    $i = 0;
    while (!$elist -> EOF) 
    {
        if ($elist -> fields['rank'] == 'Admin') 
        {
            $arrrank[$i] = R_ADMIN;
        } 
            elseif ($elist -> fields['rank'] == 'Staff') 
        {
            $arrrank[$i] = R_STAFF;
        } 
            elseif ($elist -> fields['rank'] == 'Member') 
        {
            $arrrank[$i] = R_MEMBER;
        } 
            else 
        {
            $arrrank[$i] = $elist -> fields['rank'];
        }
        $arrid[$i] = $elist -> fields['id'];
        $arrname[$i] = $elist -> fields['user'];
        $arrtribe[$i] = $elist -> fields['tribe'];
        $elist -> MoveNext();
        $i = $i + 1;
    }
    $elist -> Close();
    $smarty -> assign ( array("Level" => $player -> level, 
                              "Enemyid" => $arrid, 
                              "Enemyname" => $arrname, 
                              "Enemytribe" => $arrtribe, 
                              "Enemyrank" => $arrrank,
                              "Lid" => L_ID,
                              "Showinfo" => SHOW_INFO,
                              "Lname" => L_NAME,
                              "Lrank" => L_RANK,
                              "Lclan" => L_CLAN,
                              "Loption" => L_OPTION,
                              "Aattack" => A_ATTACK,
                              "Orback" => OR_BACK,
                              "Bback" => B_BACK));
}

if (isset ($_GET['action']) && $_GET['action'] == 'levellist') 
{
    $smarty -> assign(array(
                            "Showall" => SHOW_ALL,
                            "Tolevel" => TO_LEVEL,
                            "Ago" => A_GO));
    if (isset($_GET['step']) && $_GET['step'] == 'go') 
    {
        if (!isset($_POST['slevel'])) 
        {
            error(S_LEVEL);
        }
        if (!isset($_POST['elevel'])) 
        {
            error(E_LEVEL);
        }
        if (!ereg("^[1-9][0-9]*$", $_POST['slevel']) || !ereg("^[1-9][0-9]*$", $_POST['elevel'])) 
        {
            error (ERROR);
        }
        $elist = $db -> SelectLimit("SELECT id, user, rank, tribe FROM players WHERE level>=".$_POST['slevel']." AND level<=".$_POST['elevel']." AND hp>0 AND miejsce='".$player -> location."' AND id!=".$player -> id." AND immu='N' AND rasa!='' AND klasa!='' AND rest='N' AND freeze=0", 50);
        $arrid = array();
        $arrname = array();
        $arrrank = array();
        $arrtribe = array();
        $i = 0;
        while (!$elist -> EOF) 
        {
            if ($elist -> fields['rank'] == 'Admin') 
            {
                $arrrank[$i] = R_ADMIN;
            } 
                elseif ($elist -> fields['rank'] == 'Staff') 
            {
                $arrrank[$i] = R_STAFF;
            } 
                elseif ($elist -> fields['rank'] == 'Member') 
            {
                $arrrank[$i] = R_MEMBER;
            } 
                else 
            {
                $arrrank[$i] = $elist -> fields['rank'];
            }
            $arrid[$i] = $elist -> fields['id'];
            $arrname[$i] = $elist -> fields['user'];
            $arrtribe[$i] = $elist -> fields['tribe'];
            $elist -> MoveNext();
            $i = $i + 1;
        }
        $elist -> Close();
        $smarty -> assign (array("Enemyid" => $arrid, 
                                 "Enemyname" => $arrname, 
                                 "Enemytribe" => $arrtribe, 
                                 "Enemyrank" => $arrrank,
                                 "Lid" => L_ID,
                                 "Lname" => L_NAME,
                                 "Lrank" => L_RANK,
                                 "Lclan" => L_CLAN,
                                 "Loption" => L_OPTION,
                                 "Aattack" => A_ATTACK));
    }
}

/**
* Start battle
*/
if (isset($_GET['battle'])) 
{
    global $runda;
    global $number;
    global $newdate;
    global $smarty;
    global $db;

    $arrmenu = array('age','inteli','clas','immunited','strength','agility','attack','miss','magic','speed','cond','race','wisdom','shoot','id','user','level','exp','hp','credits','mana','maps', 'antidote', 'battlelog');
    $arrattacker = $player -> stats($arrmenu);
    $enemy = new Player($_GET['battle']);
    $arrplayer = array('id','user','level','tribe','credits','location','hp','mana','exp','age','inteli','clas','immunited','strength','agility','attack','miss','magic','speed','cond','race','wisdom','shoot','maps','rest','fight', 'antidote', 'battlelog');
    $arrdefender = $enemy -> stats($arrplayer);
    $arrMyequip = $player -> equipment();
    $arrEnequip = $enemy -> equipment();
    $myczar = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND status='E' AND typ='B'");
    $eczar = $db -> Execute("SELECT * FROM czary WHERE gracz=".$arrdefender['id']." AND status='E' AND typ='B'");
    $myczaro = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND status='E' AND typ='O'");
    $eczaro = $db -> Execute("SELECT * FROM czary WHERE gracz=".$arrdefender['id']." AND status='E' AND typ='O'");

    $arrStat = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'cond', 'attack', 'shoot', 'miss', 'magic');
    $arrRings = array(R_AGI2, R_STR2, R_INT2, R_WIS2, R_SPE2, R_CON2);

    /**
    * Add bless to stats
    */
    $objMybless = $db -> Execute("SELECT bless, blessval FROM players WHERE id=".$player -> id);
    if (!empty($objMybless -> fields['bless']))
    {
        $arrBless = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition', 'weapon', 'shoot', 'dodge', 'cast');
        $intKey = array_search($objMybless -> fields['bless'], $arrBless);
        $strStat = $arrStat[$intKey];
        $arrattacker[$strStat] = ($arrattacker[$strStat] + $objMybless -> fields['blessval']);
    }
    $objMybless -> Close();
    $objEnemybless = $db -> Execute("SELECT bless, blessval, freeze FROM players WHERE id=".$arrdefender['id']);
    if (!empty($objEnemybless -> fields['bless']))
    {
        $arrBless = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition', 'weapon', 'shoot', 'dodge', 'cast');
        $intKey = array_search($objMybless -> fields['bless'], $arrBless);
        $strStat = $arrStat[$intKey];
        $arrdefender[$strStat] = ($arrdefender[$strStat] + $objEnemybless -> fields['blessval']);
    }

    /**
     * Add bonus to stats from rings
     */
    if ($arrMyequip[9][2])
    {
        $arrRingtype = explode(" ", $arrMyequip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $strStat = $arrStat[$intKey];
        $arrattacker[$strStat] = $arrattacker[$strStat] + $arrMyequip[9][2];
    }
    if ($arrMyequip[10][2])
    {
        $arrRingtype = explode(" ", $arrMyequip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $strStat = $arrStat[$intKey];
        $arrattacker[$strStat] = $arrattacker[$strStat] + $arrMyequip[10][2];
    }
    if ($arrEnequip[9][2])
    {
        $arrRingtype = explode(" ", $arrEnequip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $strStat = $arrStat[$intKey];
        $arrdefender[$strStat] = $arrdefender[$strStat] + $arrEnequip[9][2];
    }
    if ($arrEnequip[10][2])
    {
        $arrRingtype = explode(" ", $arrEnequip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        $intKey = array_search($arrRingtype[$intAmount], $arrRings);
        $strStat = $arrStat[$intKey];
        $arrdefender[$strStat] = $arrdefender[$strStat] + $arrEnequip[10][2];
    }
    if ($objEnemybless -> fields['freeze'])
    {
        error(ACCOUNT_FREEZED);
    }
    $objEnemybless -> Close();
    $gmywt = array(0,0,0,0);
    $gewt = array(0,0,0,0);
    $runda = 0;
    /**
    * Count players agility and speed
    */
    $arrattacker['agility'] = checkagility($arrattacker['agility'], $arrMyequip[3][5], $arrMyequip[4][5], $arrMyequip[5][5]);
    $arrdefender['agility'] = checkagility($arrdefender['agility'], $arrEnequip[3][5], $arrEnequip[4][5], $arrEnequip[5][5]);
    $arrattacker['speed'] = checkspeed($arrattacker['speed'], $arrMyequip[0][7], $arrMyequip[1][7]);
    $arrdefender['speed'] = checkspeed($arrdefender['speed'], $arrEnequip[0][7], $arrEnequip[1][7]);
    if ($arrMyequip[0][3] != $arrdefender['antidote']) 
    {
        if ($arrMyequip[0][3] == 'D')
        {
            $arrMyequip[0][2] = $arrMyequip[0][2] + $arrMyequip[0][8];
        }
    }
    if ($arrEnequip[0][3] != $player -> antidote) 
    {
        if ($arrEnequip[0][3] == 'D')
        {
            $arrEnequip[0][2] = $arrEnequip[0][2] + $arrEnequip[0][8];
        }
    }
    if (!$arrdefender['id']) 
    {
        error (NO_PLAYER);
    }
    if ($arrdefender['id'] == $player -> id) 
    {
        error (SELF_ATTACK);
    }
    if ($arrdefender['hp'] <= 0) 
    {
        error ($arrdefender['user']." ".IS_DEAD);
    }
    if ($player -> hp <= 0) 
    {
        error (YOU_DEAD);
    }
    if ($player -> energy < 1) 
    {
        error (NO_ENERGY);
    }
    if ($arrdefender['tribe'] == $player -> tribe && $arrdefender['tribe'] > 0) 
    {
        error (YOUR_CLAN);
    }
    if ($arrattacker['age'] < 3) 
    {
        error (TOO_YOUNG);
    }
    if ($arrdefender['age'] < 3) 
    {
        error (TOO_YOUNG2);
    }
    if ($arrattacker['clas'] == '') 
    {
        error (NO_CLASS);
    }
    if ($arrdefender['clas'] == '') 
    {
        error (NO_CLASS2);
    }
    if (($arrMyequip[0][0] && $myczar -> fields['id']) || ($arrMyequip[1][0] && $myczar -> fields['id']) || ($arrMyequip[0][0] && $arrMyequip[1][0])) 
    {
        error (SELECT_WEP);
    }
    if (!$arrMyequip[0][0] && !$myczar -> fields['id'] && !$arrMyequip[1][0]) 
    {
        error (NO_WEAPON);
    }
    if ($arrMyequip[1][0] && !$arrMyequip[6][0]) 
    {
        error (NO_ARROWS);
    }
    if (($arrattacker['clas'] == 'Wojownik' || $arrattacker['clas'] == 'Rzemieślnik' || $arrattacker['clas'] == 'Barbarzyńca' || $arrattacker['clas'] == 'Złodziej') && $myczar -> fields['id']) 
    {
        error (BAD_CLASS);
    }
    $span =  ($player -> level - $arrdefender['level']);
    if ($span > 0) 
    {
        error (TOO_LOW);
    }
    if ($arrattacker['immunited'] == 'Y') 
    {
        error (IMMUNITED);
    }
    if ($arrdefender['immunited'] == 'Y') 
    {
        error (IMMUNITED2);
    }
    if ($arrattacker['clas'] == 'Mag' && $player -> mana == 0 && $myczar -> fields['id']) 
    {
        error (NO_MANA);
    }
    if ($player -> location != $arrdefender['location']) 
    {
        error (BAD_LOCATION);
    }
    if ($arrdefender['rest'] == 'Y') 
    {
        error (PLAYER_R);
    }
    if ($arrdefender['fight'] != 0) 
    {
        error (PLAYER_F);
    }
    $smarty -> assign (array("Enemyname" => $arrdefender['user'],
                             "Versus" => VERSUS));
    $db -> Execute("UPDATE `players` SET `energy`=`energy`-1 WHERE `id`=".$player -> id);
    $strMessage = '';
    require_once('includes/battle.php');
    if ($arrattacker['speed'] >= $arrdefender['speed']) 
    {
        attack1($arrattacker, $arrdefender, $arrMyequip, $arrEnequip, $myczar, $eczar, $myczaro, $eczaro,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, $gmywt, $gewt, $arrattacker['id'], $strMessage);
    } 
        else 
    {
        attack1($arrdefender, $arrattacker, $arrEnequip, $arrMyequip, $eczar, $myczar, $eczaro, $myczaro, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, $gmywt, $gewt, $arrattacker['id'], $strMessage);
    }
}

/**
* Figth with monsters
*/
if (isset ($_GET['action']) && $_GET['action'] == 'monster') 
{
    if ($player -> location == 'Lochy')
    {
        error(ERROR);
    }
    if (!isset($_GET['fight']) && !isset($_GET['fight1'])) 
    {
        $monster = $db -> Execute("SELECT `id`, `name`, `level`, `hp` FROM `monsters` WHERE `location`='".$player -> location."' ORDER BY `level` ASC");
        $arrid = array();
        $arrname = array();
        $arrlevel = array();
        $arrhp = array();
        $i = 0;
        while (!$monster -> EOF) 
        {
            $arrid[$i] = $monster -> fields['id'];
            $arrname[$i] = $monster -> fields['name'];
            $arrlevel[$i] = $monster -> fields['level'];
            $arrhp[$i] = $monster -> fields['hp'];
            $monster -> MoveNext();
            $i = $i + 1;
        }
        $monster -> Close();
        $smarty -> assign (array("Enemyid" => $arrid, 
                                 "Enemyname" => $arrname, 
                                 "Enemylevel" => $arrlevel, 
                                 "Enemyhp" => $arrhp,
                                 "Monsterinfo" => MONSTER_INFO,
                                 "Mname" => M_NAME,
                                 "Mlevel" => M_LEVEL,
                                 "Mhealth" => M_HEALTH,
                                 "Mfast" => M_FAST,
                                 "Mturn" => M_TURN,
                                 "Abattle" => A_BATTLE,
                                 "Orback2" => OR_BACK2,
                                 "Bback2" => B_BACK2));
    }
    if (isset($_GET['dalej']) || isset($_GET['next'])) 
    {
        $smarty -> assign(array("Abattle2" => A_BATTLE2,
                                "Witha" => WITH_A,
                                "Nend" => N_END));
    }
    if (isset($_GET['dalej'])) 
    {
        
        if (!ereg("^[1-9][0-9]*$", $_GET['dalej'])) 
        {
            error (ERROR);
        }
        $en = $db -> Execute("SELECT id, name, location FROM monsters WHERE id=".$_GET['dalej']);
        if ($en -> fields['location'] != $player -> location)
        {
            error(ERROR);
        }
        $smarty -> assign ( array("Id" => $en -> fields['id'], 
                                  "Name" => $en -> fields['name'],
                                  "Mtimes" => M_TIMES));
        $en -> Close();
    }
    if (isset ($_GET['next'])) 
    {
        if (!ereg("^[1-9][0-9]*$", $_GET['next'])) 
        { 
            error (ERROR);
        }
        $en = $db -> Execute("SELECT id, name, location FROM monsters WHERE id=".$_GET['next']);
        if ($en -> fields['location'] != $player -> location)
        {
            error(ERROR);
        }
        $smarty -> assign ( array("Id" => $en -> fields['id'], 
                                  "Name" => $en -> fields['name']));
    }
    /**
    * Turn fight with monsters
    */
    if (isset($_GET['fight1'])) 
    {
        global $arrehp;
        global $newdate;
	if (!isset($_POST['razy']) && !isset ($_POST['action']))
        {
            error(ERROR);
        }
        if (!ereg("^[1-9][0-9]*$", $_GET['fight1']) || !isset ($_POST['action']) && !ereg("^[1-9][0-9]*$", $_POST['razy']))
        {
            error (NO_ID);
        } 
        if ($player -> hp <= 0) 
        {
            error (NO_HP);
        }
	if (!isset ($_POST['action']) && $_POST['razy'] > 20)
        {
            error(TOO_MUCH_MONSTERS);
        }
        if (isset($_POST['razy']) && !isset ($_POST['action']))
        {
            $_SESSION['razy'] = $_POST['razy'];
        }
	if (!isset($_POST['action']) && $player -> energy < $_POST['razy'])
        {
            error (NO_ENERGY2);
        }
        require_once("includes/turnfight.php");
        $enemy1 = $db -> Execute("SELECT * FROM monsters WHERE id=".$_GET['fight1']);
        if (!$enemy1 -> fields['id']) 
        {
            error (NO_MONSTER);
        }
        if ($player -> clas == '') 
        {
            error (NO_CLASS3);
        }
        $span = ($enemy1 -> fields['level'] / $player -> level);
        if ($span > 2) 
        {
            $span = 2;
        }
        if (isset ($_POST['write']) && $_POST['write'] == 'Y') 
        {
            $db -> Execute("UPDATE players SET fight=".$enemy1 -> fields['id']." WHERE id=".$player -> id);
            $_POST['write'] = 'N';
            if (isset($_SESSION['amount']))
            {
                for ($k = 0; $k < $_SESSION['amount']; $k ++) 
                {
                    $strIndex = "mon".$k;
                    unset($_SESSION[$strIndex]);
                }
                unset($_SESSION['exhaust'], $_SESSION['round'], $_SESSION['points'], $_SESSION['amount']);
            }
        }
        /**
        * Count gained experience
        */
        $expgain1 = ceil(rand($enemy1 -> fields['exp1'],$enemy1 -> fields['exp2']) * $span);
        $expgain = $expgain1;
	if ($_SESSION['razy'] > 1)
        {
            for ($k = 2; $k <= $_SESSION['razy']; $k++)
            {
                $expgain = $expgain + ceil($expgain1 / 5 * (sqrt($k) + 4.5));
            }
        }
        $goldgain = ceil((rand($enemy1 -> fields['credits1'],$enemy1 -> fields['credits2']) * $_SESSION['razy']) * $span); 
        $enemy = array("strength" => $enemy1 -> fields['strength'], "agility" => $enemy1 -> fields['agility'], "speed" => $enemy1 -> fields['speed'], "endurance" => $enemy1 -> fields['endurance'], "hp" => $enemy1 -> fields['hp'], "name" => $enemy1 -> fields['name'], "exp1" => $enemy1 -> fields['exp1'], "exp2" => $enemy1 -> fields['exp2'], "level" => $enemy1 -> fields['level']);
        $arrehp = array ();
        if (!isset ($_POST['action'])) 
	  {
	    unset($_SESSION['miss']);
            $player -> energy = $player -> energy - $_POST['razy'];
            if ($player -> energy < 0) 
            {
                $player -> energy = 0;
            }
            $db -> Execute("UPDATE players SET energy=".$player -> energy." WHERE id=".$player -> id);
            turnfight ($expgain,$goldgain,'',"battle.php?action=monster&fight1=".$enemy1 -> fields['id']);
	  } 
	else 
	  {
            turnfight ($expgain,$goldgain,$_POST['action'],"battle.php?action=monster&fight1=".$enemy1 -> fields['id']);
	  }
        $enemy1 -> Close();
    }
    /**
    * Fast fight with monsters
    */
    if (isset($_GET['fight'])) 
    {
        global $newdate;

        if (!ereg("^[1-9][0-9]*$", $_GET['fight'])) 
        {
            error (ERROR);
        }
        if (!isset($_POST['razy'])) 
        {
            $_POST['razy'] = 1;
        }
        if (!ereg("^[1-9][0-9]*$", $_POST['razy'])) 
        {
            error (ERROR);
        }
        if (!isset($_POST['times']))
        {
            error(ERROR);
        }
        if (!ereg("^[1-9][0-9]*$", $_POST['times'])) 
        {
            error (ERROR);
        }
        if ($player -> hp <= 0) 
        {
            error (NO_HP);
        }
        if ($_POST['razy'] > 20)
        {
            error(TOO_MUCH_MONSTERS);
        }
        $lostenergy = $_POST['razy'] * $_POST['times'];
        if ($player -> energy < $lostenergy) 
        {
            error (NO_ENERGY2);
        }
        $myhp = $player -> hp;
        for ($j=1; $j<=$_POST['times']; $j++) 
        {
            $enemy1 = $db -> Execute("SELECT * FROM monsters WHERE id=".$_GET['fight']);
            $myexp = $db  -> Execute("SELECT exp, level FROM players WHERE id=".$player -> id);
            $enemy1 -> fields['hp'] = ($enemy1 -> fields['hp'] * $_POST['razy']);
            $player -> exp = $myexp -> fields['exp'];
            $player -> level = $myexp -> fields['level'];
            if (!$enemy1 -> fields['id']) 
            {
                error (NO_MONSTER);
            }
            if ($player -> clas == '') 
            {
                error (NO_CLASS3);
            }
            $span = ($enemy1 -> fields['level'] / $player -> level);
            if ($span > 2) 
            {
                $span = 2;
            }
            /**
            * Count gained experience
            */
            $expgain1 = ceil(rand($enemy1 -> fields['exp1'],$enemy1 -> fields['exp2']) * $span);
            $expgain = $expgain1;
            if ($_POST['razy'] > 1)
            {
                for ($k = 2; $k <= $_POST['razy']; $k++)
                {
                    $expgain = $expgain + ceil($expgain1 / 5 * (sqrt($k) + 4.5));
                }
            }
            $goldgain = ceil((rand($enemy1 -> fields['credits1'],$enemy1 -> fields['credits2']) * $_POST['razy']) * $span);
            $enemy = array("strength" => $enemy1 -> fields['strength'], 
                           "agility" => $enemy1 -> fields['agility'], 
                           "speed" => $enemy1 -> fields['speed'], 
                           "endurance" => $enemy1 -> fields['endurance'], 
                           "hp" => $enemy1 -> fields['hp'], 
                           "name" => $enemy1 -> fields['name'], 
                           "exp1" => $enemy1 -> fields['exp1'], 
                           "exp2" => $enemy1 -> fields['exp2'], 
                           "level" => $enemy1 -> fields['level']);
            $enemy1 -> Close();
            fightmonster ($enemy, $expgain, $goldgain, $_POST['times']);
            $db -> Execute("UPDATE players SET energy=energy-".$_POST['razy']." WHERE id=".$player -> id);
            if ($player -> hp <= 0) 
            {
                break;
            }
        }
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['battle'])) 
{
    $_GET['battle'] = '';
}

if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}

if (!isset($_GET['fight'])) 
{
    $_GET['fight'] = '';
}

if (!isset($_GET['fight1'])) 
{
    $_GET['fight1'] = '';
}

if (!isset($_GET['dalej'])) 
{
    $_GET['dalej'] = '';
}

if (!isset($_GET['next'])) 
{
    $_GET['next'] = '';
}

if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
}

/**
* Assign variables and display page
*/
$smarty -> assign (array("Action" => $_GET['action'], 
                         "Battle" => $_GET['battle'], 
                         "Step" => $_GET['step'], 
                         "Fight" => $_GET['fight'], 
                         "Fight1" => $_GET['fight1'], 
                         "Dalej" => $_GET['dalej'], 
                         "Next" => $_GET['next']));
$smarty -> display ('battle.tpl');

require_once("includes/foot.php");
?>
