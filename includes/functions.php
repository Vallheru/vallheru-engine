<?php
/**
 *   File functions:
 *   Functions drink - drink potions and equip - wear equipment
 *
 *   @name                 : functions.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 27.03.2012
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
require_once("languages/".$player -> lang."/functions.php");

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
                error(YOU_POISONED);
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
    if ($player -> id != $miks -> fields['owner']) 
    {
        error (NOT_OWNER);
    }
    if (empty ($miks -> fields['id'])) 
    {
        error (EMPTY_ID);
    }
    if ($strType == 'M' && $intRoll > 50 && !isset($message)) 
    {
        $cape = $db -> Execute("SELECT power FROM equipment WHERE owner=".$player -> id." AND type='C' AND status='E'");        
        $maxmana = ($player -> inteli + $player -> wisdom);
        $maxmana = $maxmana + (($cape -> fields['power'] / 100) * $maxmana);
        $cape -> Close();
        if ($player -> mana == round($maxmana,0)) 
        {
            if ($title == 'Ekwipunek')
            {
                error(NOT_NEED_MANA);
            }
                else
            {
                $message = NOT_NEED_MANA;
            }
        }
        if (!isset($message))
        {
            $pm = $miks -> fields['power'];
            $pm1 = ($pm + $player -> mana);
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
	  if ($miks->fields['power'] >= $player->level)
	    {
	      $efekt = 'czasowo zabezpieczyłeś się przed śmiercią';
	      $db -> Execute("UPDATE `players` SET `antidote`='R' WHERE `id`=".$player -> id);
	    }
	  else
	    {
	      $message = 'Ta mikstura jest za słaba dla ciebie.';
	    }
	}
    }
    if ($strType == 'H' && $intRoll > 50 && !isset($message)) 
      {
	if ($player->hp == $player->max_hp)
	  {
	    if ($title == 'Ekwipunek')
	      {
                error("Jesteś kompletnie zdrowy. Nie musisz regenerować życia.<a href");
	      }
	    else
	      {
                $message = "Jesteś kompletnie zdrowy. Nie musisz regenerować życia.";
	      }
	  }
        elseif ($player -> hp > 0) 
	  {
            $intRhp = $player -> hp + $miks -> fields['power'];
            if ($intRhp > $player -> max_hp)
	      {
                $intRhp = $player -> max_hp;
                $efekt = RESTORE_ALL_HP;
	      }
            $db -> Execute("UPDATE players SET hp=".$intRhp." WHERE id=".$player -> id);
            if (!isset($efekt))
	      {
                $efekt = RESTORE." ".$miks -> fields['power']." ".SOME_HP;
	      }
            $player -> hp = $intRhp;
	  } 
	else 
	  {
            error (YOU_NEED_H);
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
		$smarty -> assign("Action", DRINK." ".$strPotionname." ".ANDT." ".$efekt.".");
	      }
            else
	      {
		$smarty -> assign("Action", DRINK." ".$strPotionname." ".AND_FAIL);
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
	    $smarty->assign("Action", $message);
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
    $equip = $db -> Execute("SELECT * FROM equipment WHERE id=".$id." AND status='U'");
    if (empty ($equip -> fields['id'])) 
    {
        error (EMPTY_ID);
    }
    if ($player -> id != $equip -> fields['owner']) 
    {
        error (NOT_OWNER);
    }
    if ($player -> level < $equip -> fields['minlev']) 
    {
        error (LEVEL_TOO_LOW);
    }
    if ($player -> clas == 'Barbarzyńca' && ($equip -> fields['magic'] == 'Y' || ($equip -> fields['type'] == 'I' && $equip -> fields['power']))) 
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
    if ($equip -> fields['twohand'] == 'Y') 
    {
        $test = $db -> Execute("SELECT id FROM equipment WHERE status='E' AND type='S' AND owner=".$player -> id);
        if (!empty ($test -> fields['id'])) 
        {
            error (TWO_HAND_NOT_ALLOWED);
        }
        $test -> Close();
	if ($player->clas == 'Barbarzyńca')
	  {
	    $test = $db->Execute("SELECT `id` FROM `equipment` WHERE `status`='E' AND `type`='W' AND `owner`=".$player->id);
	    if (!empty($test->fields['id']))
	      {
		error("Nie możesz wziąć broni dwuręcznej, ponieważ masz już w użyciu jakąś broń!");
	      }
	  }
    }
    if ($type == 'R') 
    {
        $test = $db -> Execute("SELECT id FROM equipment WHERE type='B' AND status='E' AND owner=".$player -> id);
        if (empty($test -> fields['id'])) 
        {
            error (DONT_HAVE_BOW);
        }
        $test -> Close();
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
	    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arrows->fields['name']."' AND status='U' AND owner=".$player->id." AND power=".$arrows->fields['power']." AND poison=".$arrows->fields['poison']." AND `ptype`='".$arrows->fields['ptype']."'");
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
	$db -> Execute("INSERT INTO `equipment` (`name`, `wt`, `power`, `status`, `type`, `owner`, `ptype`, `poison`, `minlev`) VALUES('".$equip -> fields['name']."',".$wt.",".$equip -> fields['power'].",'E','R',".$player -> id.", '".$equip->fields['ptype']."', ".$equip->fields['poison'].", ".$equip->fields['minlev'].")") or error($db->ErrorMsg());
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
	    $arrows = $db -> Execute("SELECT id, name, wt FROM equipment WHERE type='R' AND owner=".$player -> id." AND status='E'");
            if (isset($arrows -> fields['id'])) 
            {
                $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arrows -> fields['name']."' AND status='U' AND owner=".$player -> id);
            }
            if (!isset($test -> fields['id'])) 
            {
                $db -> Execute("UPDATE equipment SET status='U' WHERE type='R' AND owner=".$player -> id." AND status='E'");
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET wt=wt+".$arrows -> fields['wt']." WHERE id=".$test -> fields['id']);
                $db -> Execute("DELETE FROM equipment WHERE id=".$arrows -> fields['id']);
                $test -> Close();
            }
	    $arrows->Close();
        }
        $test = $db -> Execute("SELECT id FROM equipment WHERE status='E' AND type='".$type."' AND owner=".$player -> id);
        if (empty($test -> fields['id'])) 
        {
            if ($type == 'W') 
            {
                $type = 'B';
                $test1 = $db -> Execute("SELECT id FROM equipment WHERE status='E' AND type='".$type."' AND owner=".$player -> id);
                if (empty ($test1 -> fields['id'])) 
                {
                    $type = 'T';
                }
                $test1 -> Close();
            } 
                elseif ($type == 'B') 
            {
                $type = 'W';
                $test1 = $db -> Execute("SELECT id FROM equipment WHERE status='E' AND type='".$type."' AND owner=".$player -> id);
                if (empty ($test1 -> fields['id'])) 
                {
                    $type = 'T';
                }
                $test1 -> Close();
            } 
                elseif ($type == 'T') 
            {
                $type = 'W';
                $test1 = $db -> Execute("SELECT id FROM equipment WHERE status='E' AND type='".$type."' AND owner=".$player -> id);
                if (empty ($test1 -> fields['id'])) 
                {
                    $type = 'B';
                }
                $test1 -> Close();
            }
        }
        $test -> Close();
    }
    if ($type == 'C' || $type == 'A') 
    {
        $test = $db -> Execute("SELECT id, power FROM equipment WHERE status='E' AND type='".$type."' AND owner=".$player -> id);
        if (empty ($test -> fields['id'])) 
        {
            if ($type == 'C') 
            {
                $type = 'A';
            } 
                else 
            {
                $type = 'C';
            }
        }
        $test -> Close();
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
            $objAmount = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='E' AND `owner`=".$player -> id." AND `type`='I'");
            if ($objAmount -> fields['count(`id`)'] == 1)
            {
                $blnTake = false;
            }
        }
	if ($equip->fields['type'] == 'W' && $player->clas == 'Barbarzyńca')
	  {
	    $objAmount = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `status`='E' AND `owner`=".$player -> id." AND `type`='W'");
            if ($objAmount -> fields['count(`id`)'] == 1)
	      {
                $blnTake = false;
	      }
	  }
        if ($blnTake)
        {
            $test2 = $db -> Execute("SELECT * FROM equipment WHERE status='E' AND owner=".$player -> id." AND type='".$type."'");
            if (!empty($test2 -> fields['id'])) 
            {
                $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$test2 -> fields['name']."' AND wt=".$test2 -> fields['wt']." AND status='U' AND owner=".$player -> id." AND power=".$test2 -> fields['power']." AND zr=".$test2 -> fields['zr']." AND szyb=".$test2 -> fields['szyb']." AND maxwt=".$test2 -> fields['maxwt']." AND poison=".$test2 -> fields['poison']." AND ptype='".$test2 -> fields['ptype']."' AND cost=".$test2 -> fields['cost']);
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
	$smarty -> assign ("Action", WEAR." ".$equip -> fields['name'].".");
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
      $cape = $db -> Execute("SELECT power FROM equipment WHERE owner=".$player -> id." AND type='C' AND status='E'");        
      $maxmana = ($player -> inteli + $player -> wisdom);
      $maxmana = $maxmana + (($cape -> fields['power'] / 100) * $maxmana);
      $cape -> Close();
      if ($player->mana == round($maxmana, 0)) 
	{
	  if ($intId != 0)
	    {
	      error("Nie musisz regenerować punktów magii!");
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
	      error("Potrzebujesz wskrzeszenia.");
	    }
	  return;
	}
      elseif ($player->hp == $player->max_hp)
	{
	  if ($intId != 0)
	    {
	      error("Nie potrzebujesz leczenia.");
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
	  $player->mana += $objPotion->fields['power'];
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
	  $player->hp += $objPotion->fields['power'];
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
      $smarty->assign("Effect", $strEffect);
    }
  else
    {
      print "<br />".$strEffect."<br />";
    }
  $objPotion->Close();
}