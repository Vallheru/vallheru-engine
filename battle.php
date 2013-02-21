<?php
/**
 *   File functions:
 *   Battle Arena - figth between players and player vs monsters
 *
 *   @name                 : battle.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 21.02.2013
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
                            "Ashowalive" => "Chcę walczyć z innymi graczami...",
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
    global $strUsername;

    checkvalue($_GET['battle']);
    $player->curskills(array('attack', 'shoot', 'dodge', 'magic'), FALSE);
    $player->stats['wisdom'][2] += $player->checkbonus('will');
    $player->stats['wisdom'][2] += $player->checkbonus('antimagic');
    $player->skills['attack'][1] += $player->checkbonus('weaponmaster');
    $player->skills['shoot'][1] += $player->checkbonus('weaponmaster');
    $player->stats['speed'][2] += $player->checkbonus('tactic');
    $player->user = $strUsername;
    $enemy = new Player($_GET['battle']);
    $enemy->user = $arrTags[$enemy->tribe][0].' '.$enemy->user.' '.$arrTags[$enemy->tribe][1];
    if (!$enemy->id)
      {
	error (NO_PLAYER);
      }
    $enemy->curskills(array('attack', 'shoot', 'dodge', 'magic'), FALSE);
    $enemy->stats['wisdom'][2] += $enemy->checkbonus('will');
    $enemy->stats['wisdom'][2] += $enemy->checkbonus('antimagic');
    $enemy->skills['attack'][1] += $enemy->checkbonus('weaponmaster');
    $enemy->skills['shoot'][1] += $enemy->checkbonus('weaponmaster');
    $enemy->stats['speed'][2] += $enemy->checkbonus('tactic');
    $myczar = $db -> Execute("SELECT * FROM `czary` WHERE `gracz`=".$player -> id." AND `status`='E' AND `typ`='B'");
    $eczar = $db -> Execute("SELECT * FROM `czary` WHERE `gracz`=".$enemy->id." AND `status`='E' AND `typ`='B'");
    $myczaro = $db -> Execute("SELECT * FROM `czary` WHERE `gracz`=".$player -> id." AND `status`='E' AND `typ`='O'");
    $eczaro = $db -> Execute("SELECT * FROM `czary` WHERE `gracz`=".$enemy->id." AND `status`='E' AND `typ`='O'");
    //Count spell damage
    if ($myczar->fields['id'])
      {
	$myczar->fields['dmg'] = $myczar->fields['obr'] * $player->stats['inteli'][2];
	$intBonus = $player->checkbonus('bspell');
	$myczar->fields['dmg'] += ($myczar->fields['dmg'] * $intBonus);
	$intBonus = $player->checkbonus($myczar->fields['element']);
	$myczar->fields['dmg'] += ($myczar->fields['dmg'] * $intBonus);
      }
    if ($eczar->fields['id'])
      {
	$eczar->fields['dmg'] = $eczar->fields['obr'] * $enemy->stats['inteli'][2];
	$intBonus = $enemy->checkbonus('bspell');
	$eczar->fields['dmg'] += ($eczar->fields['dmg'] * $intBonus);
	$intBonus = $enemy->checkbonus($eczar->fields['element']);
	$eczar->fields['dmg'] += ($eczar->fields['dmg'] * $intBonus);
      }
    if ($myczaro->fields['id'])
      {
	$myczaro->fields['def'] = $myczaro->fields['obr'] * $player->stats['wisdom'][2];
	$intBonus = $player->checkbonus('dspell');
	$myczaro->fields['def'] += ($myczaro->fields['def'] * $intBonus);
	$intBonus = $player->checkbonus($myczaro->fields['element']);
	$myczaro->fields['def'] += ($myczaro->fields['def'] * $intBonus);
      }
    if ($eczaro->fields['id'])
      {
	$eczaro->fields['def'] = $eczaro->fields['obr'] * $enemy->stats['wisdom'][2];
	$intBonus = $enemy->checkbonus('bspell');
	$eczaro->fields['def'] += ($eczaro->fields['def'] * $intBonus);
	$intBonus = $enemy->checkbonus($eczaro->fields['element']);
	$eczaro->fields['def'] += ($eczaro->fields['def'] * $intBonus);
      }
    $arrElements = array('water' => 'fire',
			 'fire' => 'wind',
			 'wind' => 'earth',
			 'earth' => 'water');
    $arrElements2 = array('W' => 'fire',
			  'F' => 'wind',
			  'A' => 'earth',
			  'E' => 'water');
    $arrElements3 = array('water' => 'W',
			  'fire' => 'F',
			  'wind' => 'A',
			  'earth' => 'E');
    $arrElements4 = array('water' => 'F',
			    'fire' => 'A',
			    'wind' => 'E',
			    'earth' => 'W');
    $arrElements5 = array('W' => 'F',
			  'F' => 'A',
			  'A' => 'E',
			  'E' => 'W');

    for ($i = 2; $i < 6; $i++)
      {
	if ($enemy->equip[$i][10] != 'N')
	  {
	    if ($myczar->fields['id'])
	      {
		if ($arrElements3[$myczar->fields['element']] == $enemy->equip[$i][10])
		  {
		    $enemy->equip[$i][2] = $enemy->equip[$i][2] * 2;
		  }
		elseif ($arrElements4[$myczar->fields['element']] == $enemy->equip[$i][10])
		  {
		    $enemy->equip[$i][2] = ceil($enemy->equip[$i][2] / 2);
		  }
	      }
	    elseif ($player->equip[0][10] != 'N')
	      {
		if ($player->equip[0][10] == $enemy->equip[$i][10])
		  {
		    $enemy->equip[$i][2] = $enemy->equip[$i][2] * 2;
		  }
		elseif ($arrElements5[$player->equip[0][10]] == $enemy->equip[$i][10])
		  {
		    $enemy->equip[$i][2] = ceil($enemy->equip[$i][2] / 2);
		  }
	      }
	    elseif ($player->equip[6][10] != 'N')
	      {
		if ($player->equip[6][10] == $enemy->equip[$i][10])
		  {
		    $enemy->equip[$i][2] = $enemy->equip[$i][2] * 2;
		  }
		elseif ($arrElements5[$player->equip[6][10]] == $enemy->equip[$i][10])
		  {
		    $enemy->equip[$i][2] = ceil($enemy->equip[$i][2] / 2);
		  }
	      }
	  }
	elseif ($enemy->equip[$i][10] == 'N')
	  {
	    if ($myczar->fields['id'])
	      {
		$enemy->equip[$i][2] = 0;
	      }
	    if ($player->equip[0][10] != 'N' || $player->equip[6][10] != 'N')
	      {
		$enemy->equip[$i][2] = ceil($enemy->equip[$i][2] / 2);
	      }
	  }
	if ($player->equip[$i][10] != 'N')
	  {
	    if ($eczar->fields['id'])
	      {
		if ($arrElements3[$eczar->fields['element']] == $player->equip[$i][10])
		  {
		    $player->equip[$i][2] = $player->equip[$i][2] * 2;
		  }
		elseif ($arrElements4[$eczar->fields['element']] == $player->equip[$i][10])
		  {
		    $player->equip[$i][2] = ceil($player->equip[$i][2] / 2);
		  }
	      }
	    elseif ($enemy->equip[0][10] != 'N')
	      {
		if ($enemy->equip[0][10] == $player->equip[$i][10])
		  {
		    $player->equip[$i][2] = $player->equip[$i][2] * 2;
		  }
		elseif ($arrElements5[$enemy->equip[0][10]] == $player->equip[$i][10])
		  {
		    $player->equip[$i][2] = ceil($player->equip[$i][2] / 2);
		  }
	      }
	    elseif ($enemy->equip[6][10] != 'N')
	      {
		if ($enemy->equip[6][10] == $player->equip[$i][10])
		  {
		    $player->equip[$i][2] = $player->equip[$i][2] * 2;
		  }
		elseif ($arrElements5[$enemy->equip[6][10]] == $player->equip[$i][10])
		  {
		    $player->equip[$i][2] = ceil($player->equip[$i][2] / 2);
		  }
	      }
	  }
	elseif ($player->equip[$i][10] == 'N')
	  {
	    if ($eczar->fields['id'])
	      {
		$player->equip[$i][2] = 0;
	      }
	    if ($enemy->equip[0][10] != 'N' || $enemy->equip[6][10] != 'N')
	      {
		$player->equip[$i][2] = ceil($player->equip[$i][2] / 2);
	      }
	  }
      }
    if ($eczaro->fields['id'])
      {
	if ($myczar->fields['id'])
	  {
	    if ($myczar->fields['element'] == $eczaro->fields['element'])
	      {
		$eczaro->fields['def'] = ($eczaro->fields['obr'] * $enemy->stats['wisdom'][2]) * 2;
	      }
	    elseif ($eczaro->fields['element'] == $arrElements[$myczar->fields['element']])
	      {
		$eczaro->fields['def'] = ($eczaro->fields['obr'] * $enemy->stats['wisdom'][2]) / 2;
	      }
	  }
	elseif ($player->equip[0][10] != 'N')
	  {
	    if ($player->equip[0][10] == $arrElements3[$eczaro->fields['element']])
	      {
		$eczaro->fields['def'] = ($eczaro->fields['obr'] * $enemy->stats['wisdom'][2]) * 2;
	      }
	    elseif ($eczaro->fields['element'] == $arrElements2[$player->equip[0][10]])
	      {
		$eczaro->fields['def'] = ($eczaro->fields['obr'] * $enemy->stats['wisdom'][2]) / 2;
	      }
	  }
	elseif ($player->equip[6][10] != 'N')
	  {
	    if ($player->equip[6][10] == $arrElements3[$eczaro->fields['element']])
	      {
		$eczaro->fields['def'] = ($eczaro->fields['obr'] * $enemy->stats['wisdom'][2]) * 2;
	      }
	    elseif ($eczaro->fields['element'] == $arrElements2[$player->equip[6][10]])
	      {
		$eczaro->fields['def'] = ($eczaro->fields['obr'] * $enemy->stats['wisdom'][2]) / 2;
	      }
	  }
      }
    if ($myczaro->fields['id'] && $eczar->fields['id'])
      {
	if ($myczar->fields['element'] == $eczaro->fields['element'])
	  {
	    $myczaro->fields['def'] = ($myczaro->fields['obr'] * $player->stats['wisdom'][2]) * 2;
	  }
	elseif ($myczaro->fields['element'] == $arrElements[$eczar->fields['element']])
	  {
	    $myczaro->fields['def'] = ($myczaro->fields['obr'] * $player->stats['wisdom'][2]) * 2;
	  }
	elseif ($enemy->equip[0][10] != 'N')
	  {
	    if ($enemy->equip[0][10] == $arrElements3[$myczaro->fields['element']])
	      {
		$myczaro->fields['def'] = ($myczaro->fields['obr'] * $player->stats['wisdom'][2]) * 2;
	      }
	    elseif ($myczaro->fields['element'] == $arrElements2[$enemy->equip[0][10]])
	      {
		$myczaro->fields['def'] = ($myczaro->fields['obr'] * $player->stats['wisdom'][2]) / 2;
	      }
	  }
	elseif ($enemy->equip[6][10] != 'N')
	  {
	    if ($enemy->equip[6][10] == $arrElements3[$myczaro->fields['element']])
	      {
		$myczaro->fields['def'] = ($myczaro->fields['obr'] * $player->stats['wisdom'][2]) * 2;
	      }
	    elseif ($myczaro->fields['element'] == $arrElements2[$enemy->equip[6][10]])
	      {
		$myczaro->fields['def'] = ($myczaro->fields['obr'] * $player->stats['wisdom'][2]) / 2;
	      }
	  }
      }

    $objFreezed = $db->Execute("SELECT `freeze` FROM `players` WHERE `id`=".$enemy->id);
    if ($objFreezed -> fields['freeze'])
    {
        error(ACCOUNT_FREEZED);
    }
    $objFreezed->Close();
    $runda = 0;
    if ($player->equip[0][3] != $enemy->antidote && $player->equip[0][3] == 'D') 
      {
	$player->equip[0][2] = $player->equip[0][2] + $player->equip[0][8];
      }
    if ($enemy->equip[0][3] != $player -> antidote && $enemy->equip[0][3] == 'D') 
    {
      $enemy->equip[0][2] = $enemy->equip[0][2] + $enemy->equip[0][8];
    }
    if ($enemy->id == $player -> id) 
    {
        error (SELF_ATTACK);
    }
    if ($enemy->hp <= 0) 
    {
        error ($enemy->user." ".IS_DEAD);
    }
    if ($player -> hp <= 0) 
    {
        error (YOU_DEAD);
    }
    if ($player -> energy < 1) 
    {
        error (NO_ENERGY);
    }
    if ($enemy->tribe == $player -> tribe && $enemy->tribe > 0) 
    {
        error (YOUR_CLAN);
    }
    if ($player->newbie > 0)
    {
        error (TOO_YOUNG);
    }
    if ($enemy->newbie > 0)
    {
        error (TOO_YOUNG2);
    }
    if ($player->clas == '') 
    {
        error (NO_CLASS);
    }
    if ($enemy->clas == '') 
    {
        error (NO_CLASS2);
    }
    if (($player->equip[0][0] && $myczar -> fields['id']) || ($player->equip[1][0] && $myczar -> fields['id']) || ($player->equip[0][0] && $player->equip[1][0])) 
    {
        error (SELECT_WEP);
    }
    if (!$player->equip[0][0] && !$myczar -> fields['id'] && !$player->equip[1][0]) 
    {
        error (NO_WEAPON);
    }
    if ($player->equip[1][0] && !$player->equip[6][0]) 
    {
        error (NO_ARROWS);
    }
    if ($player->clas != 'Mag' && $myczar -> fields['id']) 
    {
        error (BAD_CLASS);
    }
    if ($player->immunited == 'Y') 
    {
        error (IMMUNITED);
    }
    if ($enemy->immunited == 'Y') 
    {
        error (IMMUNITED2);
    }
    if ($player->clas == 'Mag' && $player -> mana == 0 && $myczar -> fields['id']) 
    {
        error (NO_MANA);
    }
    if ($player -> location != $enemy->location) 
    {
        error (BAD_LOCATION);
    }
    if ($enemy->rest == 'Y') 
    {
        error (PLAYER_R);
    }
    if ($enemy->fight != 0) 
    {
        error (PLAYER_F);
    }
    $objAttack = $db->Execute("SELECT `id` FROM `attacks` WHERE `attacker`=".$player->id." AND `attacked`=".$enemy->id);
    if ($objAttack->fields['id'])
      {
	error('Nie możesz atakować tego gracza więcej niż raz na reset.');
      }
    $smarty -> assign (array("Enemyname" => $enemy->user,
                             "Versus" => VERSUS));
    $db -> Execute("UPDATE `players` SET `energy`=`energy`-1 WHERE `id`=".$player -> id);
    $strMessage = '';
    require_once('includes/battle.php');
    $db->Execute("INSERT INTO `attacks` (`attacker`, `attacked`) VALUES(".$player->id.", ".$enemy->id.")");
    if ($player->stats['speed'][2] >= $enemy->stats['speed'][2]) 
    {
        attack1($player, $enemy, $myczar, $eczar, $myczaro, $eczaro, 0, 0, 0, 0, 0, 0, 0, 0, $player->id, $strMessage);
    } 
        else 
    {
        attack1($enemy, $player, $eczar, $myczar, $eczaro, $myczaro, 0, 0, 0, 0, 0, 0, 0, 0, $player->id, $strMessage);
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
	$elist = $db -> SelectLimit("SELECT `id`, `user`, `rank`, `tribe`, `reputation` FROM `players` WHERE `hp`>0 AND `miejsce`='".$player -> location."' AND `id`!=".$player -> id." AND `immu`='N' AND `rasa`!='' AND `klasa`!='' AND `rest`='N' AND `freeze`=0 AND `reputation`>=".($player->reputation - 10)." ORDER BY RAND()", 50);
	$arrid = array();
	$arrname = array();
	$arrrank = array();
	$arrtribe = array();
	$arrRep = array();
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
	    $arrRep[] = $elist->fields['reputation'];
	    $elist -> MoveNext();
	  }
	$elist -> Close();
	$arrTribes = array_unique($arrtribe);
	if (in_array('0', $arrTribes))
	  {
	    unset($arrTribes[array_search('0', $arrTribes)]);
	  }
	if (count($arrTribes))
	  {
	    $objTribes = $db->Execute("SELECT `id`, `name` FROM `tribes` WHERE `id` IN (".implode(',', $arrTribes).")");
	    while (!$objTribes->EOF)
	      {
		foreach ($arrtribe as &$strTribe)
		  {
		    if ($objTribes->fields['id'] == $strTribe)
		      {
			$strTribe = $objTribes->fields['name'];
		      }
		  }
		$objTribes->MoveNext();
	      }
	    $objTribes->Close();
	  }
	foreach ($arrtribe as &$strTribe)
	  {
	    if ($strTribe == '0')
	      {
		$strTribe = 'Brak';
	      }
	  }
	$smarty -> assign ( array("Enemyid" => $arrid, 
				  "Enemyname" => $arrname, 
				  "Enemytribe" => $arrtribe, 
				  "Enemyrank" => $arrrank,
				  "Enemyrep" => $arrRep,
				  "Lid" => L_ID,
				  "Showinfo" => "Oto dostępni przeciwnicy w twojej okolicy.",
				  "Lname" => L_NAME,
				  "Lrank" => L_RANK,
				  "Lclan" => L_CLAN,
				  "Lrep" => "Reputacja",
				  "Loption" => L_OPTION,
				  "Aattack" => A_ATTACK,
				  "Orback" => "Możesz również",
				  "Bback" => B_BACK));
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
	$intPlevel = $player->stats['condition'][2] + $player->stats['speed'][2] + $player->stats['agility'][2] + $player->skills['dodge'][1] + $player->hp;
	if ($player->equip[0][0] || $player->equip[11][0] || $player->equip[1][0])
	  {
	    $intPlevel += $player->stats['strength'][2];
	    if ($player->equip[0][0] || $player->equip[11][0])
	      {
		$intPlevel += $player->skills['attack'][1];
	      }
	    else
	      {
		$intPlevel += $player->skills['shoot'][1];
	      }
	  }
	else
	  {
	    $intPlevel += $player->stats['wisdom'][2] + $player->stats['inteli'][2] + $player->skills['magic'][1];
	  }
	if (!isset($_POST['fight']) && !isset($_POST['fight1']) && !isset($_GET['fight1'])) 
	  {
	    $arrMonsters = $db->GetAll("SELECT * FROM `monsters` WHERE `location`='".$player -> location."' ORDER BY `level` ASC");
	    foreach ($arrMonsters as &$arrMonster)
	      {
		$arrMonster['energy'] = floor(1 + ($arrMonster['level'] / 20));
		$intElevel = $arrMonster['strength'] + $arrMonster['agility'] + $arrMonster['speed'] + $arrMonster['endurance'] + $arrMonster['level'] + $arrMonster['hp'];
		$intDiv = $intElevel / $intPlevel;
		if ($intDiv <= 0.1)
		  {
		    $arrMonster['elevel'] = 'Brak zagrożenia';
		  }
		elseif ($intDiv > 0.1 && $intDiv <= 0.5)
		  {
		    $arrMonster['elevel'] = 'Niegroźny';
		  }
		elseif ($intDiv > 0.5 && $intDiv <= 0.9)
		  {
		    $arrMonster['elevel'] = 'Prawie wyzwanie';
		  }
		elseif ($intDiv > 0.9 && $intDiv <= 1.2)
		  {
		    $arrMonster['elevel'] = 'Adekwatny';
		  }
		elseif ($intDiv > 1.2 && $intDiv <= 1.3)
		  {
		    $arrMonster['elevel'] = 'Nieco niebezpieczny';
		  }
		elseif ($intDiv > 1.3 && $intDiv <= 1.5)
		  {
		    $arrMonster['elevel'] = 'Groźny';
		  }
		elseif ($intDiv > 1.5 && $intDiv <= 1.7)
		  {
		    $arrMonster['elevel'] = 'Niebezpieczny';
		  }
		elseif ($intDiv > 1.7)
		  {
		    $arrMonster['elevel'] = 'Samobójstwo';
		  }
	      }
	    $smarty -> assign (array("Monsters" => $arrMonsters,
				     "Monsterinfo" => MONSTER_INFO,
				     "Mname" => M_NAME,
				     "Mlevel" => "Poziom trudności",
				     "Mhealth" => M_HEALTH,
				     "Mfast" => M_FAST,
				     "Mturn" => M_TURN,
				     "Abattle" => A_BATTLE,
				     "Orback2" => OR_BACK2,
				     "Mtimes" => "walk (szybkich)",
				     "Mamount" => "Ilość",
				     "Mmonsters" => "potworów",
				     "Menergy" => "Koszt energii",
				     "Menergy2" => "Szybka/Turowa",
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
	    $enemy1 = $db -> Execute("SELECT * FROM `monsters` WHERE `id`=".$_GET['fight1']);
	    if (!$enemy1 -> fields['id']) 
	      {
		error (NO_MONSTER);
	      }
	    if (!isset($_POST['action']))
	      {
		if ($_POST['razy'] > 20)
		  {
		    error(TOO_MUCH_MONSTERS);
		  }
		$intEnergy = floor(1 + ($enemy1->fields['level'] / 20));
		if ($player->energy < ($_POST['razy'] * $intEnergy))
		  {
		    error ("Nie masz wystarczającej ilości energii.");
		  }
		$_SESSION['razy'] = $_POST['razy'];
		$_SESSION['energy'] = $intEnergy;
	      }
	    require_once("includes/turnfight.php");
	    if ($player -> clas == '') 
	      {
		error (NO_CLASS3);
	      }
	    if ($player->fight > 0 && $player->fight != $_GET['fight1'])
	      {
		error("Już z kimś walczysz!");
	      }
	    $intElevel = $enemy1->fields['strength'] + $enemy1->fields['agility'] + $enemy1->fields['speed'] + $enemy1->fields['endurance'] + $enemy1->fields['level'] + $enemy1->fields['hp'];
	    $span = ($intElevel / $intPlevel);
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
	    $expgain1 = ceil($intElevel * $span);
	    if (!isset($_SESSION['razy']))
	      {
		$_SESSION['razy'] = 1;
	      }
	    $expgain = $expgain1 * $_SESSION['razy'];
	    $goldgain = ceil(($intElevel * $_SESSION['razy']) * $span);
	    $expgain = $expgain * $_SESSION['energy'];
	    $goldgain = $goldgain * $_SESSION['energy'];
	    $enemy = array("strength" => $enemy1 -> fields['strength'], 
			   "agility" => $enemy1 -> fields['agility'], 
			   "speed" => $enemy1 -> fields['speed'], 
			   "endurance" => $enemy1 -> fields['endurance'], 
			   "hp" => $enemy1 -> fields['hp'], 
			   "name" => $enemy1 -> fields['name'], 
			   "level" => $enemy1 -> fields['level'],
			   "lootnames" => explode(";", $enemy1->fields['lootnames']),
			   "lootchances" => explode(";", $enemy1->fields['lootchances']),
			   "resistance" => explode(";", $enemy1->fields['resistance']),
			   "dmgtype" => $enemy1->fields['dmgtype']);
	    $arrehp = array ();
	    if (!isset ($_POST['action'])) 
	      {
		unset($_SESSION['miss']);
		$player->energy -= ($_POST['razy'] * $intEnergy);
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
	    $enemy1 = $db -> Execute("SELECT * FROM `monsters` WHERE `id`=".$_GET['fight']);
	    if (!$enemy1 -> fields['id']) 
	      {
		error (NO_MONSTER);
	      }
	    $lostenergy = ($_POST['razy'] * $_POST['times'] * floor(1 + ($enemy1->fields['level'] / 20)));
	    if ($player->energy < $lostenergy) 
	      {
		error ("Nie masz wystarczającej ilości energii.");
	      }
	    if ($player -> clas == '') 
	      {
		error (NO_CLASS3);
	      }
	    $myhp = $player->hp;
	    if ($player->fight > 0 && $player->fight != $_GET['fight'])
	      {
		error("Już z kimś walczysz!");
	      }
	    $intElevel = $enemy1->fields['strength'] + $enemy1->fields['agility'] + $enemy1->fields['speed'] + $enemy1->fields['endurance'] + $enemy1->fields['level'] + $enemy1->fields['hp'];
	    $enemy1 -> fields['hp'] = ($enemy1 -> fields['hp'] * $_POST['razy']);
	    $enemy = array("strength" => $enemy1 -> fields['strength'], 
			   "agility" => $enemy1 -> fields['agility'], 
			   "speed" => $enemy1 -> fields['speed'], 
			   "endurance" => $enemy1 -> fields['endurance'], 
			   "hp" => $enemy1 -> fields['hp'], 
			   "name" => $enemy1 -> fields['name'], 
			   "level" => $enemy1 -> fields['level'],
			   "lootnames" => explode(";", $enemy1->fields['lootnames']),
			   "lootchances" => explode(";", $enemy1->fields['lootchances']),
			   "resistance" => explode(";", $enemy1->fields['resistance']),
			   "dmgtype" => $enemy1->fields['dmgtype']);
	    $intAmount = 0;
	    for ($j=1; $j<=$_POST['times']; $j++) 
	      {
		$intPlevel = $player->stats['condition'][2] + $player->stats['speed'][2] + $player->stats['agility'][2] + $player->skills['dodge'][1] + $player->hp;
		if ($player->equip[0][0] || $player->equip[11][0] || $player->equip[1][0])
		  {
		    $intPlevel += $player->stats['strength'][2];
		    if ($player->equip[0][0] || $player->equip[11][0])
		      {
			$intPlevel += $player->skills['attack'][1];
		      }
		    else
		      {
			$intPlevel += $player->skills['shoot'][1];
		      }
		  }
		else
		  {
		    $intPlevel += $player->stats['wisdom'][2] + $player->stats['inteli'][2] + $player->skills['magic'][1];
		  }
		$span = ($intElevel / $intPlevel);
		if ($span > 2) 
		  {
		    $span = 2;
		  }
		/**
		 * Count gained experience
		 */
		$expgain1 = ceil($intElevel * $span);
		$expgain = $expgain1 * $_POST['razy'];
		$goldgain = ceil(($intElevel * $_POST['razy']) * $span);
		$expgain = $expgain * floor(1 + ($enemy1->fields['level'] / 20));
		$goldgain = $goldgain * floor(1 + ($enemy1->fields['level'] / 20));
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
