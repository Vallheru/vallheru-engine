<?php
/**
 *   File functions:
 *   Functions drink - drink potions and equip - wear equipment
 *
 *   @name                 : functions.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 28.12.2012
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
* Get the localization for game
*/
require_once("languages/".$lang."/functions.php");

/**
* Function to drink potions - argument $id is id drinked potion
*/
function drink($id) 
 {
    global $player;
    global $smarty;
    global $db;
    global $title;

    checkvalue($id);
    $miks = $db -> Execute("SELECT * FROM potions WHERE status='K' AND id=".$id);
    if ($player -> id != $miks -> fields['owner']) 
      {
        error (NOT_OWNER);
      }
    if (empty ($miks -> fields['id'])) 
      {
        error (EMPTY_ID);
      }
    $strType = $miks -> fields['type'];
    if (strpos($miks -> fields['name'], "(K)") !== FALSE)
    {
        $intRoll = rand(0,100);
        if ($intRoll == 1)
        {
            $amount = $miks -> fields['amount'] - 1;
            if ($amount < 1) 
            {
                $db -> Execute("DELETE FROM potions WHERE id=".$miks -> fields['id']);
            }
                else 
            {
                $db -> Execute("UPDATE potions SET amount=".$amount." WHERE id=".$miks -> fields['id']);
            }
            $db -> Execute("UPDATE players SET hp=0 WHERE id=".$player -> id);
            $player -> hp = 0;
            if ($title == 'Ekwipunek')
	      {
		message('error', YOU_POISONED);
		return;
	      }
                else
            {
                $message = YOU_POISONED;
            }
        }
    }
        else
    {
        $intRoll = 51;
    }
    if ($strType == 'M' && $intRoll > 50 && !isset($message)) 
    {
        $maxmana = floor($player->stats['inteli'][2] + $player->stats['wisdom'][2]);
	if ($player->clas == 'Mag')
	  {
	    $maxmana = $maxmana * 2;
	  }
	$maxmana += floor(($player->equip[8][2] / 100) * $maxmana);
        if ($player -> mana == round($maxmana,0)) 
        {
            if ($title == 'Ekwipunek')
	      {
		message('error', NOT_NEED_MANA);
		return;
	      }
                else
            {
                $message = NOT_NEED_MANA;
            }
        }
        if (!isset($message))
        {
	    $pm = ceil(($miks->fields['power'] / 100) * $maxmana);
            $pm1 = $player -> mana + $pm;
            if ($pm1 > $maxmana) 
            {
                $pm1 = $maxmana;
                $efekt = RESTORE_ALL_MANA;
            }
            $db -> Execute("UPDATE players SET pm=".$pm1." WHERE id=".$player -> id);
            if (!isset($efekt))
            {
                $efekt = RESTORE." ".$pm." punktów magii";
            }
            $player -> mana = $pm1;
        }
    }
    if ($strType == 'A' && $intRoll > 50 && !isset($message)) 
    {
      if (strpos($miks -> fields['name'], "Dynallca") !== FALSE)
        {
            $strAtype = 'D';
            $strType2 = 'Dynallca';
        }
      elseif (strpos($miks -> fields['name'], "Nutari") !== FALSE)
        {
            $strAtype = 'N';
            $strType2 = 'Nutari';
        }
      elseif (strpos($miks -> fields['name'], "Illani") !== FALSE)
        {
            $strType2 = 'Illani';
            $strAtype = 'I';
        }
      if (stripos($miks->fields['name'], 'oszukanie śmierci') === FALSE)
	{
	  $efekt =  GAIN_ANTI." ".$strType2;
	  $db -> Execute("UPDATE `players` SET `antidote`='".$strAtype."' WHERE `id`=".$player -> id);
	}
      else
	{
	  $efekt = 'czasowo zabezpieczyłeś się przed śmiercią';
	  $db -> Execute("UPDATE `players` SET `antidote`='R".$miks->fields['power']."' WHERE `id`=".$player -> id);
	}
    }
    if ($strType == 'H' && $intRoll > 50 && !isset($message)) 
      {
	if ($player->hp == $player->max_hp)
	  {
	    if ($title == 'Ekwipunek')
	      {
                message('error', "Jesteś kompletnie zdrowy. Nie musisz regenerować życia.");
		return;
	      }
	    else
	      {
                $message = "Jesteś kompletnie zdrowy. Nie musisz regenerować życia.";
	      }
	  }
        elseif ($player -> hp > 0) 
	  {
            $intRhp = $player -> hp + ceil(($miks -> fields['power'] / 100) * $player->max_hp);
            if ($intRhp > $player -> max_hp)
	      {
                $intRhp = $player -> max_hp;
                $efekt = RESTORE_ALL_HP;
	      }
            $db -> Execute("UPDATE players SET hp=".$intRhp." WHERE id=".$player -> id);
            if (!isset($efekt))
	      {
                $efekt = RESTORE." ".ceil(($miks -> fields['power'] / 100) * $player->max_hp)." ".SOME_HP;
	      }
            $player -> hp = $intRhp;
	  } 
	else 
	  {
            message('error', YOU_NEED_H);
	    return;
	  }
    }
    if (!isset($message))
    {
        $amount = $miks -> fields['amount'] - 1;
        if ($amount < 1) 
        {
            $db -> Execute("DELETE FROM potions WHERE id=".$miks -> fields['id']);
        } 
            else 
        {
            $db -> Execute("UPDATE potions SET amount=".$amount." WHERE id=".$miks -> fields['id']);
        }
        $strPotionname = $miks -> fields['name'];
	if ($player->page != 'Ekwipunek')
	  {
	    if ($intRoll > 50)
	      {
		$smarty -> assign("Message", DRINK." ".$strPotionname." ".ANDT." ".$efekt.".");
	      }
            else
	      {
		$smarty -> assign("Message", DRINK." ".$strPotionname." ".AND_FAIL);
	      }
	  }
	else
	  {
	    if ($intRoll > 50)
	      {
		message("success", DRINK." ".$strPotionname." ".ANDT." ".$efekt.".");
	      }
            else
	      {
		message("error", DRINK." ".$strPotionname." ".AND_FAIL);
	      }
	  }
    }
    else
      {
	if ($player->page != 'Ekwipunek')
	  {
	    $smarty -> assign("Message", $message);
	  }
	else
	  {
	    message("success", $message);
	  }
    }
    if ($player->page != 'Ekwipunek')
      {
	$smarty -> display ('error1.tpl');
      }
    $miks -> Close();   
}

/**
* Fuction with wear equipment
*/
function equip ($id) 
{
    global $player;
    global $smarty;
    global $db;

    checkvalue($id);
    $equip = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$id." AND `status`='U' AND `owner`=".$player->id);
    if (empty ($equip -> fields['id'])) 
    {
        error ('Nie ma takiego przedmiotu.');
    }
    if ($player -> id != $equip -> fields['owner']) 
    {
        error (NOT_OWNER);
    }
    $strSkill = '';
    switch ($equip->fields['type'])
      {
      case 'W':
	$strSkill = 'attack';
	break;
      case 'B':
      case 'R':
	$strSkill = 'shoot';
	break;
      case 'C':
      case 'T':
	$strSkill = 'magic';
	break;
      case 'H':
      case 'A':
      case 'S':
      case 'L':
	$arrSkills = array($player->skills['attack'][1], $player->skills['shoot'][1], $player->skills['magic'][1]);
	if ($arrSkills[0] > $arrSkills[1] && $arrSkills[0] > $arrSkills[2])
	  {
	    $strSkill = 'attack';
	  }
	elseif ($arrSkills[1] > $arrSkills[0] && $arrSkills[1] > $arrSkills[2])
	  {
	    $strSkill = 'shoot';
	  }
	else
	  {
	    $strSkill = 'magic';
	  }
	break;
      case 'E':
	$arrTools = array('smelting' => 'miechy',
			  'lumberjack' => 'piła',
			  'mining' => 'kilof',
			  'breeding' => 'uprząż',
			  'jewellry' => 'nożyk',
			  'herbalism' => 'sierp',
			  'alchemy' => 'moździerz',
			  'carpentry' => 'ciesak',
			  'smith' => 'młot');
	foreach ($arrTools as $Skill => $strName)
	  {
	    if (stripos($equip->fields['name'], $strName) !== FALSE)
	      {
		$strSkill = $Skill;
		break;
	      }
	  }
	break;
      default:
	break;
      }
    if ($strSkill != '')
      {
	if ($player->skills[$strSkill][1] < $equip->fields['minlev'])
	  {
	    message('error', 'Nie możesz jeszcze założyć tego przedmiotu.');
	    return;
	  }
      }
    if ($player -> clas == 'Barbarzyńca' && ($equip -> fields['magic'] != 'N' || ($equip -> fields['type'] == 'I' && $equip -> fields['power']))) 
    {
        error (YOU_ARE_BARBARIAN);
    }
    if ($player -> clas != 'Mag' && ($equip -> fields['type'] == 'T' || $equip -> fields['type'] == 'C')) 
    {
        error (YOU_ARE_NOT_MAGE);
    }
    $arrArtifact = array(AR_SWORD, AR_ARMOR, AR_I_STAFF, AR_CAPE);
    foreach ($arrArtifact as $strArtifact)
    {
        if ($equip -> fields['name'] == $strArtifact && $player -> rank != R_HERO)
        {
            error(YOU_ARE_NOT_HERO);
        }
    }
    $type = $equip -> fields['type'];
    if ($type == 'S') 
    {
        $test = $db -> Execute("SELECT id FROM equipment WHERE status='E' AND twohand='Y' AND owner=".$player -> id);
        if (!empty ($test -> fields['id'])) 
        {
            error (SHIELD_NOT_ALLOWED);
        }
        $test -> Close();
	if ($player->clas == 'Barbarzyńca')
	  {
	    $test = $db->Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='E' AND `type`='W' AND `owner`=".$player->id);
	    if ($test->fields['count(`id`)'] == 2)
	      {
		error("Nie możesz założyć tarczy, ponieważ używasz już dwóch broni.");
	      }
	  }
    }
    if ($type == 'W' && $player->clas == 'Barbarzyńca' && $player->equip[5][0])
      {
	error('Nie możesz założyć drugiej broni, ponieważ używasz obecnie tarczy.');
      }
    if ($equip -> fields['twohand'] == 'Y') 
    {
        if ($player->equip[5][0]) 
        {
            error (TWO_HAND_NOT_ALLOWED);
        }
	if ($player->clas == 'Barbarzyńca')
	  {
	    if ($player->equip[0][0] || $player->equip[11][0])
	      {
		error("Nie możesz wziąć broni dwuręcznej, ponieważ masz już w użyciu jakąś broń!");
	      }
	  }
    }
    if ($type == 'R') 
    {
        if (!$player->equip[1][0]) 
        {
            error (DONT_HAVE_BOW);
        }
        if ($equip -> fields['wt'] > 25) 
        {
            $wt = 25;
        } 
            else 
        {
            $wt = $equip -> fields['wt'];
        }
        $arrows = $db -> Execute("SELECT * FROM equipment WHERE type='R' AND owner=".$player -> id." AND status='E'");
	//Take off old arrows
	if ($arrows->fields['id'])
	  {
	    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arrows->fields['name']."' AND status='U' AND owner=".$player->id." AND power=".$arrows->fields['power']." AND poison=".$arrows->fields['poison']." AND `ptype`='".$arrows->fields['ptype']."' AND `cost`=".$arrows->fields['cost']." AND `magic`='".$arrows->fields['magic']."'") or die($db->ErrorMsg());
	    if (!isset($test -> fields['id'])) 
	      {
		$db -> Execute("UPDATE `equipment` SET `status`='U' WHERE `type`='R' AND `owner`=".$player -> id." AND `status`='E'");
	      } 
            else 
	      {
		$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$arrows->fields['wt']." WHERE `id`=".$test -> fields['id']);
		$db -> Execute("DELETE FROM `equipment` WHERE `id`=".$arrows->fields['id']);
	      }
	    $test->Close();
	  }
	$db -> Execute("INSERT INTO `equipment` (`name`, `wt`, `power`, `status`, `type`, `owner`, `ptype`, `poison`, `minlev`, `magic`) VALUES('".$equip -> fields['name']."',".$wt.",".$equip -> fields['power'].",'E','R',".$player -> id.", '".$equip->fields['ptype']."', ".$equip->fields['poison'].", ".$equip->fields['minlev'].", '".$equip->fields['magic']."')") or error($db->ErrorMsg());
	$testwt = ($equip -> fields['wt'] - $wt);
	if ($testwt < 1) 
	  {
	    $db -> Execute("DELETE FROM equipment WHERE id=".$equip -> fields['id']);
	  } 
	else 
	  {
	    $db -> Execute("UPDATE equipment SET wt=".$testwt." WHERE id=".$equip -> fields['id']);
	  }
	$arrows->Close();
    }
    if ($type == 'W' || $type == 'B' || $type == 'T') 
    {
        if ($type == 'W' || $type == 'T') 
        {
            if ($player->equip[6][0]) 
            {
                $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$player->equip[6][1]."' AND status='U' AND owner=".$player -> id);
            }
            if (!isset($test -> fields['id'])) 
            {
                $db -> Execute("UPDATE equipment SET status='U' WHERE type='R' AND owner=".$player -> id." AND status='E'");
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET wt=wt+".$player->equip[6][6]." WHERE id=".$test -> fields['id']);
                $db -> Execute("DELETE FROM equipment WHERE id=".$player->equip[6][0]);
                $test -> Close();
            }
        }
	switch ($type)
	  {
	  case 'W':
	    if (!$player->equip[0][0] && !$player->equip[11][0])
	      {
		if ($player->equip[1][0])
		  {
		    $type = 'B';
		  }
		else
		  {
		    $type = 'T';
		  }
	      }
	    break;
	  case 'B':
	    if (!$player->equip[1][0])
	      {
		if ($player->equip[0][0] || $player->equip[11][0])
		  {
		    $type = 'W';
		  }
		else
		  {
		    $type = 'T';
		  }
	      }
	    break;
	  case 'T':
	    if (!$player->equip[7][0])
	      {
		if ($player->equip[0][0] || $player->equip[11][0])
		  {
		    $type = 'W';
		  }
		else
		  {
		    $type = 'B';
		  }
	      }
	    break;
	  default:
	    break;
	  }
    }
    if ($type == 'C' || $type == 'A') 
    {
	if ($player->equip[3][0])
	  {
	    $type = 'A';
	  }
	else
	  {
	    $type = 'C';
	  }
    }
    if ($equip -> fields['type'] != 'R') 
    {
        $amount = $equip -> fields['amount'] - 1;
        if ($amount > 0) 
        {
            $db -> Execute("UPDATE equipment SET amount=amount-1 WHERE id=".$equip -> fields['id']);
        } 
            else 
        {
            $db -> Execute("DELETE FROM equipment WHERE id=".$equip -> fields['id']);
        }
        $blnTake = true;
        if ($equip -> fields['type'] == 'I')
        {
	    if (!$player->equip[9][0] || !$player->equip[10][0])
            {
                $blnTake = false;
            }
        }
	if ($equip->fields['type'] == 'W' && $player->clas == 'Barbarzyńca')
	  {
	    if (!$player->equip[0][0] || !$player->equip[11][0])
	      {
                $blnTake = false;
	      }
	  }
        if ($blnTake)
        {
            $test2 = $db -> Execute("SELECT * FROM equipment WHERE status='E' AND owner=".$player -> id." AND type='".$type."'");
            if (!empty($test2 -> fields['id'])) 
            {
                $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$test2 -> fields['name']."' AND wt=".$test2 -> fields['wt']." AND status='U' AND owner=".$player -> id." AND power=".$test2 -> fields['power']." AND zr=".$test2 -> fields['zr']." AND szyb=".$test2 -> fields['szyb']." AND maxwt=".$test2 -> fields['maxwt']." AND poison=".$test2 -> fields['poison']." AND ptype='".$test2 -> fields['ptype']."' AND cost=".$test2 -> fields['cost']." AND `magic`='".$test2->fields['magic']."'");
                if (!empty($test -> fields['id'])) 
                {
                    $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
                } 
                    else 
                {
                    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$test2 -> fields['name']."',".$test2 -> fields['power'].",'".$test2 -> fields['type']."',".$test2 -> fields['cost'].",".$test2 -> fields['zr'].",".$test2 -> fields['wt'].",".$test2 -> fields['minlev'].",".$test2 -> fields['maxwt'].",1,'".$test2 -> fields['magic']."',".$test2 -> fields['poison'].",".$test2 -> fields['szyb'].",'".$test2 -> fields['twohand']."', '".$test2 -> fields['ptype']."', ".$test2 -> fields['repair'].")") or error(E_DROP);
                }
                $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$test2 -> fields['id']);
            }
            $test2 -> Close();
        }
        $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, status, twohand, ptype, repair) VALUES(".$player -> id.",'".$equip -> fields['name']."',".$equip -> fields['power'].",'".$equip -> fields['type']."',".$equip -> fields['cost'].",".$equip -> fields['zr'].",".$equip -> fields['wt'].",".$equip -> fields['minlev'].",".$equip -> fields['maxwt'].",0,'".$equip -> fields['magic']."',".$equip -> fields['poison'].",".$equip -> fields['szyb'].",'E','".$equip -> fields['twohand']."','".$equip -> fields['ptype']."', ".$equip -> fields['repair'].")") or error(E_WEAR);
    }
    if ($player->page != 'Ekwipunek')
      {
	$smarty -> assign ("Message", WEAR." ".$equip -> fields['name'].".");
	$smarty -> display ('error1.tpl');
      }
    else
      {
	message("success", WEAR." ".$equip -> fields['name'].".");
      }
    $equip -> Close();
}

/**
 * Function allow drink few potions simultaneously
 */
function drinkfew($intId, $intAmount, $strType = '')
{
  global $player;
  global $smarty;
  global $db;
  global $lang;
  
  if ($intId != 0)
    {
      $objPotion = $db->Execute("SELECT * FROM `potions` WHERE `id`=".$intId);
    }
  else
    {
      $objPotion = $db->SelectLimit("SELECT * FROM `potions` WHERE `owner`=".$player->id." AND `type`='".$strType."' AND `status`='K'", 1);
      if (!$objPotion->fields['id'])
	{
	  return;
	}
      $intAmount = $objPotion->fields['amount'];
    }
  $intUsed = 0;
  $intOldhp = $player->hp;
  $intOldmana = $player->mana;
  if ($objPotion->fields['type'] == 'M')
    {
      $maxmana = floor($player->stats['inteli'][2] + $player->stats['wisdom'][2]);
      if ($player->clas == 'Mag')
	{
	  $maxmana = $maxmana * 2;
	}
      $maxmana += floor(($player->equip[8][2] / 100) * $maxmana);
      if ($player->mana == round($maxmana, 0)) 
	{
	  if ($intId != 0)
	    {
	      message('error', "Nie musisz regenerować punktów magii!");
	    }
	  return;
	}
    }
  else
    {
      if ($player->hp <= 0)
	{
	  if ($intId != 0)
	    {
	      message('error', "Potrzebujesz wskrzeszenia.");
	    }
	  return;
	}
      elseif ($player->hp == $player->max_hp)
	{
	  if ($intId != 0)
	    {
	      message('error', "Nie potrzebujesz leczenia.");
	    }
	  return;
	}
    }
  for ($i = 1; $i <= $intAmount; $i++)
    {
      if (strpos($objPotion->fields['name'], "(K)") !== FALSE)
	{
	  $intRoll = rand(0, 100);
	  if ($intRoll == 1)
	    {
	      $intUsed = $i;
	      $player->hp = 0;
	      $strEffect = "Kiedy wypiłeś miksturę, świat zawirował ci przed oczami. Napój okazał się trucizną. Przez krótką chwilę próbowałeś walczyć z ogarniającą ciebie ciemnością, lecz niestety twój organizm nie wytrzymał takiej walki. Martwy, padasz na ziemię.";
	      break;
	    }
	  elseif ($intRoll < 51)
	    {
	      continue;
	    }
	}
      if ($objPotion->fields['type'] == 'M')
	{
	  $player->mana += ceil(($objPotion->fields['power'] / 100) * $maxmana);
	  if ($player->mana >= $maxmana)
	    {
	      $player->mana = $maxmana;
	      $intUsed = $i;
	      $strEffect = "Wypiłeś ".$intUsed." razy ".$objPotion->fields['name']." i zregenerowałeś wszystkie punkty magii.";
	      break;
	    }
	}
      else
	{
	  $player->hp += ceil(($objPotion->fields['power'] / 100) * $player->max_hp);
	  if ($player->hp >= $player->max_hp)
	    {
	      $player->hp = $player->max_hp;
	      $intUsed = $i;
	      $strEffect = "Wypiłeś ".$intUsed." razy ".$objPotion->fields['name']." i odzyskałeś wszystkie punkty życia.";
	      break;
	    }
	}
    }
  if ($intUsed == 0)
    {
      $intUsed = $intAmount;
      if ($objPotion->fields['type'] == 'M')
	{
	  $intGained = $player->mana - $intOldmana;
	  $strEffect = "Wypiłeś ".$intUsed." razy ".$objPotion->fields['name']." i zregenerowałeś ".$intGained." punktów magii.";
	}
      else
	{
	  $intGained = $player->hp - $intOldhp;
	  $strEffect = "Wypiłeś ".$intUsed." razy ".$objPotion->fields['name']." i odzyskałeś ".$intGained." punktów życia.";
	}
    }
  elseif (($objPotion->fields['type'] == 'M') && ($player->mana != $maxmana))
    {
      $intGained = $player->mana - $intOldmana;
      $strEffect = "Wypiłeś ".$intUsed." razy ".$objPotion->fields['name']." i zregenerowałeś ".$intGained." punktów magii. ".$strEffect;
    }
  $db->Execute("UPDATE `players` SET `hp`=".$player->hp.", `pm`=".$player->mana." WHERE `id`=".$player->id);
  if ($intUsed == $objPotion->fields['amount'])
    {
      $db->Execute("DELETE FROM `potions` WHERE `id`=".$objPotion->fields['id']);
    }
  else
    {
      $db->Execute("UPDATE `potions` SET `amount`=`amount`-".$intUsed." WHERE `id`=".$objPotion->fields['id']);
    }
  if ($intId != 0)
    {
      message("success", $strEffect);
    }
  else
    {
      print "<br />".$strEffect."<br />";
    }
  $objPotion->Close();
}