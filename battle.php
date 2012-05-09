<?php
/**
 *   File functions:
 *   Battle Arena - figth between players and player vs monsters
 *
 *   @name                 : battle.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 09.05.2012
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
require_once("languages/".$lang."/battle.php");

global $runda;
global $number;
global $newdate;
global $smarty;
global $db;

if ($player->location == 'Lochy')
  {
    error('Nie możesz zwiedzać areny ponieważ znajdujesz się w lochach.');
  }

if (!isset($_GET['action']) && !isset($_GET['battle']))
{
    $smarty -> assign(array("Battleinfo" => BATTLE_INFO,
                            "Ashowalive" => A_SHOW_ALIVE,
                            "Ashowlevel" => A_SHOW_LEVEL,
                            "Ashowmonster" => A_SHOW_MONSTER));
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

    checkvalue($_GET['battle']);
    $arrmenu = array('age','inteli','clas','immunited','strength','agility','attack','miss','magic','speed','cond','race','wisdom','shoot','id','user','level','exp','hp','credits','mana','maps', 'antidote', 'battlelog', 'newbie', 'oldstats');
    $arrMyequip = $player->equipment();
    $player->curstats($arrMyequip);
    $player->curskills(array('weapon', 'shoot', 'dodge', 'cast'), FALSE);
    $arrattacker = $player -> stats($arrmenu);
    $arrattacker['user'] = $arrTags[$player->tribe][0].' '.$player->user.' '.$arrTags[$player->tribe][1];
    $enemy = new Player($_GET['battle']);
    $arrplayer = array('id','user','level','tribe','credits','location','hp','mana','exp','age','inteli','clas','immunited','strength','agility','attack','miss','magic','speed','cond','race','wisdom','shoot','maps','rest','fight', 'antidote', 'battlelog', 'newbie', 'oldstats');
    $arrEnequip = $enemy -> equipment();
    $enemy->curstats($arrEnequip);
    $enemy->curskills(array('weapon', 'shoot', 'dodge', 'cast'), FALSE);
    $arrdefender = $enemy -> stats($arrplayer);
    $arrdefender['user'] = $arrTags[$arrdefender['tribe']][0].' '.$arrdefender['user'].' '.$arrTags[$arrdefender['tribe']][1];
    $myczar = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND status='E' AND typ='B'");
    $eczar = $db -> Execute("SELECT * FROM czary WHERE gracz=".$arrdefender['id']." AND status='E' AND typ='B'");
    $myczaro = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND status='E' AND typ='O'");
    $eczaro = $db -> Execute("SELECT * FROM czary WHERE gracz=".$arrdefender['id']." AND status='E' AND typ='O'");

    $objFreezed = $db->Execute("SELECT `freeze` FROM `players` WHERE `id`=".$enemy->id);
    if ($objFreezed -> fields['freeze'])
    {
        error(ACCOUNT_FREEZED);
    }
    $objFreezed->Close();
    $gmywt = array(0,0,0,0);
    $gewt = array(0,0,0,0);
    $runda = 0;
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
    if ($arrattacker['newbie'] > 0)
    {
        error (TOO_YOUNG);
    }
    if ($arrdefender['newbie'] > 0)
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
else
  {
    $_GET['battle'] = '';
  }

if (isset($_GET['action']))
  {
    /**
     * Show players on this same level which have a player
     */
    if ($_GET['action'] == 'showalive') 
      {
	$elist = $db -> SelectLimit("SELECT id, user, rank, tribe FROM players WHERE level=".$player -> level." AND hp>0 AND miejsce='".$player -> location."' AND id!=".$player -> id." AND immu='N' AND rasa!='' AND klasa!='' AND rest='N' AND freeze=0", 50);
	$arrid = array();
	$arrname = array();
	$arrrank = array();
	$arrtribe = array();
	while (!$elist -> EOF) 
	  {
	    switch ($elist->fields['rank'])
	      {
	      case 'Admin':
		$arrrank[] = R_ADMIN;
		break;
	      case 'Staff':
		$arrrank[] = R_STAFF;
		break;
	      case 'Member':
		$arrrank[] = R_MEMBER;
		break;
	      default:
		$arrrank[] = $elist -> fields['rank'];
		break;
	      }
	    $arrid[] = $elist -> fields['id'];
	    $arrname[] = $elist -> fields['user'];
	    $arrtribe[] = $elist -> fields['tribe'];
	    $elist -> MoveNext();
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

    if ($_GET['action'] == 'levellist') 
      {
	$smarty -> assign(array("Showall" => SHOW_ALL,
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
	    checkvalue($_POST['slevel']);
	    checkvalue($_POST['elevel']);
	    $elist = $db -> SelectLimit("SELECT `players`.`id`, `user`, `rank`, `tribes`.`name` FROM `players`, `tribes` WHERE `players`.`level`>=".$_POST['slevel']." AND `players`.`level`<=".$_POST['elevel']." AND `players`.`hp`>0 AND `players`.`miejsce`='".$player->location."' AND `players`.`id`!=".$player -> id." AND `immu`='N' AND `rasa`!='' AND `klasa`!='' AND `rest`='N' AND `freeze`=0 AND `tribes`.`id`=`players`.`tribe`", 50) or die($db->ErrorMsg());
	    $arrid = array();
	    $arrname = array();
	    $arrrank = array();
	    $arrtribe = array();
	    while (!$elist -> EOF) 
	      {
		switch ($elist->fields['rank'])
		  {
		  case 'Admin':
		    $arrrank[] = R_ADMIN;
		    break;
		  case 'Staff':
		    $arrrank[] = R_STAFF;
		    break;
		  case 'Member':
		    $arrrank[] = R_MEMBER;
		    break;
		  default:
		    $arrrank[] = $elist -> fields['rank'];
		    break;
		  }
		$arrid[] = $elist -> fields['id'];
		$arrname[] = $elist -> fields['user'];
		if (!$elist->fields['name'])
		  {
		    $arrtribe[] = 'Brak';
		  }
		else
		  {
		    $arrtribe[] = $elist -> fields['name'];
		  }
		$elist -> MoveNext();
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
     * Figth with monsters
     */
    if ($_GET['action'] == 'monster') 
      {
	if ($player -> location == 'Lochy')
	  {
	    error(ERROR);
	  }
	if (!isset($_POST['fight']) && !isset($_POST['fight1']) && !isset($_GET['fight1'])) 
	  {
	    $monster = $db -> Execute("SELECT `id`, `name`, `level`, `hp` FROM `monsters` WHERE `location`='".$player -> location."' ORDER BY `level` ASC");
	    $arrid = array();
	    $arrname = array();
	    $arrlevel = array();
	    $arrhp = array();
	    while (!$monster -> EOF) 
	      {
		$arrid[] = $monster -> fields['id'];
		$arrname[] = $monster -> fields['name'];
		$arrlevel[] = $monster -> fields['level'];
		$arrhp[] = $monster -> fields['hp'];
		$monster -> MoveNext();
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
				     "Mtimes" => "walk (szybkich)",
				     "Mamount" => "Ilość",
				     "Mmonsters" => "potworów",
				     "Bback2" => B_BACK2));
	  }
	/**
	 * Turn fight with monsters
	 */
	if (isset($_POST['fight1']) || isset($_GET['fight1'])) 
	  {
	    global $arrehp;
	    global $newdate;
	    if (!isset($_GET['fight1']))
	      {
		checkvalue($_POST['mid']);
		$_GET['fight1'] = $_POST['mid'];
	      }
	    if (!isset($_POST['razy']) && !isset ($_POST['action']))
	      {
		error(ERROR);
	      }
	    if (!intval($_GET['fight1']) || !isset ($_POST['action']) && !intval($_POST['razy']))
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
	    if ($player->fight > 0 && $player->fight != $_GET['fight1'])
	      {
		error("Już z kimś walczysz!");
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
	    if (!isset($_SESSION['razy']))
	      {
		$_SESSION['razy'] = 1;
	      }
	    if ($_SESSION['razy'] > 1)
	      {
		for ($k = 2; $k <= $_SESSION['razy']; $k++)
		  {
		    $expgain = $expgain + ceil($expgain1 / 5 * (sqrt($k) + 4.5));
		  }
	      }
	    $goldgain = ceil((rand($enemy1 -> fields['credits1'],$enemy1 -> fields['credits2']) * $_SESSION['razy']) * $span); 
	    $enemy = array("strength" => $enemy1 -> fields['strength'], 
			   "agility" => $enemy1 -> fields['agility'], 
			   "speed" => $enemy1 -> fields['speed'], 
			   "endurance" => $enemy1 -> fields['endurance'], 
			   "hp" => $enemy1 -> fields['hp'], 
			   "name" => $enemy1 -> fields['name'], 
			   "exp1" => $enemy1 -> fields['exp1'], 
			   "exp2" => $enemy1 -> fields['exp2'], 
			   "level" => $enemy1 -> fields['level'],
			   "lootnames" => explode(";", $enemy1->fields['lootnames']),
			   "lootchances" => explode(";", $enemy1->fields['lootchances']));
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
	if (isset($_POST['fight'])) 
	  {
	    global $newdate;
	    
	    $_GET['fight'] = $_POST['mid'];
	    checkvalue($_GET['fight']);
	    if (!isset($_POST['razy'])) 
	      {
		$_POST['razy'] = 1;
	      }
	    checkvalue($_POST['razy']);
	    if (!isset($_POST['times']))
	      {
		error(ERROR);
	      }
	    checkvalue($_POST['times']);
	    if ($player->hp <= 0) 
	      {
		error (NO_HP);
	      }
	    if ($_POST['razy'] > 20)
	      {
		error(TOO_MUCH_MONSTERS);
	      }
	    if ($_POST['times'] > 20)
	      {
		error("Zbyt wiele walk na raz!");
	      }
	    $lostenergy = $_POST['razy'] * $_POST['times'];
	    if ($player->energy < $lostenergy) 
	      {
		error (NO_ENERGY2);
	      }
	    if ($player -> clas == '') 
	      {
		error (NO_CLASS3);
	      }
	    $myhp = $player->hp;
	    $enemy1 = $db -> Execute("SELECT * FROM `monsters` WHERE `id`=".$_GET['fight']);
	    if (!$enemy1 -> fields['id']) 
	      {
		error (NO_MONSTER);
	      }
	    if ($player->fight > 0 && $player->fight != $_GET['fight'])
	      {
		error("Już z kimś walczysz!");
	      }
	    $enemy1 -> fields['hp'] = ($enemy1 -> fields['hp'] * $_POST['razy']);
	    $enemy = array("strength" => $enemy1 -> fields['strength'], 
			   "agility" => $enemy1 -> fields['agility'], 
			   "speed" => $enemy1 -> fields['speed'], 
			   "endurance" => $enemy1 -> fields['endurance'], 
			   "hp" => $enemy1 -> fields['hp'], 
			   "name" => $enemy1 -> fields['name'], 
			   "exp1" => $enemy1 -> fields['exp1'], 
			   "exp2" => $enemy1 -> fields['exp2'], 
			   "level" => $enemy1 -> fields['level'],
			   "lootnames" => explode(";", $enemy1->fields['lootnames']),
			   "lootchances" => explode(";", $enemy1->fields['lootchances']));
	    $intAmount = 0;
	    for ($j=1; $j<=$_POST['times']; $j++) 
	      {
		$myexp = $db  -> Execute("SELECT `exp`, `level` FROM `players` WHERE `id`=".$player -> id);
		$player -> exp = $myexp -> fields['exp'];
		$player -> level = $myexp -> fields['level'];
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
		if ($player->antidote == 'R')
		  {
		    $blnRessurect = TRUE;
		  }
		else
		  {
		    $blnRessurect = FALSE;
		  }
		fightmonster ($enemy, $expgain, $goldgain, $_POST['times']);
		$intAmount++;
		if (($player -> hp <= 0) || ($player->antidote == '' && $blnRessurect))
		  {
		    break;
		  }
	      }
	    $enemy1->Close();
	  }
      }
  }
else
  {
    $_GET['action'] = '';
  }

/**
* Initialization of variables
*/
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
