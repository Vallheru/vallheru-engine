<?php
/**
 *   File functions:
 *   Player's equip - wear and drop items, repair, sell and more
 *
 *   @name                 : equip.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 17.12.2012
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

$title = "Ekwipunek";
require_once("includes/head.php");
require_once("includes/functions.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/equip.php");

/**
* Function show items in backpack
*/
function backpack($type, $nameitems, $type2, $smartyname, $strSelltype) 
{
    global $db;
    global $player;
    global $arrBackpack;
    global $smarty;

    if (!empty ($type2)) 
    {
        $arm = $db -> Execute("SELECT * FROM `equipment` WHERE `owner`=".$player->id." AND `type`='".$type."' AND status='U' ORDER BY `minlev` ASC, `name` ASC") or die($db->ErrorMsg());
        $arm1 = $db -> Execute("SELECT * FROM `equipment` WHERE `owner`=".$player->id." AND `type`='".$type2."' AND `status`='U' ORDER BY `minlev` ASC, `name` ASC") or die($db->ErrorMsg());
    } 
        else 
    {
        $arm = $db -> Execute("SELECT * FROM `equipment` WHERE `owner`=".$player->id." AND `type`='".$type."' AND `status`='U' ORDER BY `minlev` ASC, `name` ASC") or die($db->ErrorMsg());
    }
    $arrshow = array(array());
    $arrMenu = array();
    if (!isset($arm1)) 
    {
        $secarm = 0;
    } 
        else 
    {
        $secarm = 1;
    }
    if ($arm -> fields['id'] || $secarm) 
      {
	if ($type != 'O' && $type != 'Q')
	  {
	    $arrMenu[0] = IN_BACKPACK." ".$nameitems;
	  }
	else
	  {
	    $arrMenu[0] = $nameitems;
	  }
      }
    $j = 1;
    while (!$arm -> EOF) 
      {
	switch ($arm->fields['ptype'])
	  {
	  case 'D':
	    $arm->fields['name'] .= ' (Dynallca +'.$arm->fields['poison'].')';
	    break;
	  case 'N':
	    $arm->fields['name'] .= ' (Nutari +'.$arm->fields['poison'].')';
	    break;
	  case 'I':
	    $arm->fields['name'] .= ' (Illani +'.$arm->fields['poison'].')';
	    break;
	  default:
	    break;
	  }
	switch ($arm->fields['magic'])
	  {
	  case 'E':
	    $arm->fields['name'] .= ' (Żywioł: Ziemia)';
	    break;
	  case 'W':
	    $arm->fields['name'] .= ' (Żywioł: Woda)';
	    break;
	  case 'F':
	    $arm->fields['name'] .= ' (Żywioł: Ogień)';
	    break;
	  case 'A':
	    $arm->fields['name'] .= ' (Żywioł: Powietrze)';
	    break;
	  default:
	    break;
	  }
        if ($arm -> fields['zr'] < 0) 
        {
            $arm -> fields['zr'] = str_replace("-","",$arm -> fields['zr']);
            $agility = "(+".$arm -> fields['zr']." ".EQUIP_AGI.")";
        } 
            elseif ($arm -> fields['zr'] > 0) 
        {
            $agility = "(-".$arm -> fields['zr']." ".EQUIP_AGI.")";
        }
            elseif ($arm -> fields['zr'] == 0)
        {
            $agility = '';
        }
        if ($arm -> fields['szyb'] > 0 && $arm -> fields['type'] != 'A') 
        {
            $speed = "(+".$arm -> fields['szyb']." ".EQUIP_SPEED.")";
        } 
            else 
        {
            $speed = '';
        }
	if ($arm->fields['wt'] < $arm->fields['maxwt'])
	  {
	    $ckoszt = ceil($arm->fields['repair'] * (1 - $arm->fields['wt'] / $arm->fields['maxwt']));
	  }
	else
	  {
	    $ckoszt = 0;
	  }
        if ($arm -> fields['maxwt'] == $arm -> fields['wt']) 
        {
            $ckoszt = 0;
        }
	if ($arm->fields['type'] != 'R' && $arm->fields['type'] != 'I')
	  {
	    $strRepair = "| <a href=\"equip.php?napraw=".$arm -> fields['id']."\">".A_REPAIR."</a> ".FOR_A." ".$ckoszt." ".GOLD_COINS;
	  }
	else
	  {
	    $strRepair = '';
	  }
	if ($arm->fields['type'] != 'R')
	  {
	    $intCost = $arm->fields['cost'];
	  }
	else
	  {
	    $costone = $arm->fields['cost'] / 100;
	    $intCost = ceil($costone * $arm->fields['wt']);
	  }
	switch ($arm->fields['type'])
	  {
	  case 'O':
	  case 'I':
	  case 'Q':
	  case 'P':
	    $strDur = '';
	    break;
	  case 'R':
	    $strDur = " (".$arm -> fields['wt']." strzał)";
	    break;
	  default:
	    $strDur = " (".$arm -> fields['wt']."/".$arm -> fields['maxwt']." ".DURABILITY.")";
	    break;
	  }
	switch ($arm->fields['type'])
	  {
	  case 'C':
            $arrshow[$arm->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." (+".$arm -> fields['power']." % ".EQUIP_MANA.") [ <a href=\"equip.php?equip=".$arm -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm -> fields['cost']." ".GOLD_COINS." ]<br />";
	    break;
	  case 'T':
            $arrshow[$arm->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." (".SPELL_POWER.") [ <a href=\"equip.php?equip=".$arm -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm -> fields['cost']." ".GOLD_COINS." ]<br />";
	    break;
	  case 'Q':
	  case 'O':
	    $arrshow[$arm->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." [ <a href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm->fields['cost']." ".GOLD_COINS." ]<br />";
	    break;
	  case 'P':
	    if ($player->clas == 'Rzemieślnik')
	      {
		$strLearn = '<a href="equip.php?learn='.$arm->fields['id'].'">studiuj</a> | ';
	      }
	    else
	      {
		$strLearn = '';
	      }
	    $arrshow[$arm->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." [ ".$strLearn."<a href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm->fields['cost']." ".GOLD_COINS." ]<br />";
	    break;
	  case 'E':
	    $arrName = explode(' ', $arm->fields['name']);
	    $arrTools = array('Wytrychy' => ' do akcji złodziejskich',
			      'Miechy' => ' do Hutnictwa',
			      'Piła' => ' do Drwalnictwa',
			      'Kilof' => ' do Górnictwa',
			      'Uprząż' => ' do Hodowli',
			      'Nożyk' => ' do Jubilerstwa',
			      'Sierp' => ' do Zielarstwa',
			      'Moździerz' => ' do Alchemii',
			      'Ciesak' => ' do Stolarstwa',
			      'Młot' => ' do Kowalstwa');
	    if (count($arrName) == 3)
	      {
		$strName = $arrName[0];
	      }
	    else
	      {
		$strName = $arrName[1];
	      }
	    $arrshow[$arm->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." (+".$arm -> fields['power']." %".$arrTools[$strName].") ".$agility."".$speed.$strDur." [ <a href=\"equip.php?equip=".$arm -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$intCost." ".GOLD_COINS." ".$strRepair."]<br />";
	    break;
	  default:
	    $arrshow[$arm->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." (+".$arm -> fields['power'].") ".$agility."".$speed.$strDur." [ <a href=\"equip.php?equip=".$arm -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$intCost." ".GOLD_COINS." ".$strRepair."]<br />";
	  break;
        }
        $arm -> MoveNext();
        $j = $j + 1;
        $menu = 'Y';
    }
    if ($secarm) 
    {
        while (!$arm1 -> EOF) 
        {
	    switch ($arm1->fields['magic'])
	      {
	      case 'E':
		$arm1->fields['name'] .= ' (Żywioł: Ziemia)';
		break;
	      case 'W':
		$arm1->fields['name'] .= ' (Żywioł: Woda)';
		break;
	      case 'F':
		$arm1->fields['name'] .= ' (Żywioł: Ogień)';
		break;
	      case 'A':
		$arm1->fields['name'] .= ' (Żywioł: Powietrze)';
		break;
	      default:
		break;
	      }
            if ($arm1 -> fields['zr'] < 0) 
            {
                $arm1 -> fields['zr'] = str_replace("-","",$arm1 -> fields['zr']);
                $agility = "(+".$arm1 -> fields['zr']." ".EQUIP_AGI.")";
            } 
                elseif ($arm1 -> fields['zr'] > 0) 
            {
                $agility = "(-".$arm1 -> fields['zr']." ".EQUIP_AGI.")";
            } 
                else 
            {
                $agility = '';
            }
            if ($arm1 -> fields['szyb'] > 0) 
            {
                $speed = "(+".$arm1 -> fields['szyb']." ".EQUIP_SPEED.")";
            } 
                else 
            {
                $speed = '';
            }
	    if ($arm1->fields['wt'] < $arm1->fields['maxwt'])
	      {
		$ckoszt = ceil($arm1->fields['repair'] * (1 - $arm1->fields['wt'] / $arm1->fields['maxwt']));
	      }
	    else
	      {
		$ckoszt = 0;
	      }
            if ($arm1 -> fields['maxwt'] == $arm1 -> fields['wt']) 
            {
                $ckoszt = 0;
            }  
            if ($arm1 -> fields['type'] == 'C') 
            {
                $arrshow[$arm1->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm1->fields['id']."\" /><b>(".AMOUNT.": ".$arm1 -> fields['amount']." )</b> ".$arm1 -> fields['name']." (+".$arm1 -> fields['power']." % ".EQUIP_MANA.") [ <a href=\"equip.php?equip=".$arm1 -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm1 -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm1 -> fields['cost']." ".GOLD_COINS." ]<br />";
            } 
                elseif ($arm -> fields['type'] == 'T') 
            {
                $arrshow[$arm1->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm1->fields['id']."\" /><b>(".AMOUNT.": ".$arm1 -> fields['amount']." )</b> ".$arm1 -> fields['name']." (".SPELL_POWER.") [ <a href=\"equip.php?equip=".$arm1 -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm1 -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm1 -> fields['cost']." ".GOLD_COINS." ]<br />";
            } 
                else 
            {
                $arrshow[$arm1->fields['minlev']][$j] = "<input type=\"checkbox\" id=\"list\" name=\"".$arm1->fields['id']."\" /><b>(".AMOUNT.": ".$arm1 -> fields['amount']." )</b> ".$arm1 -> fields['name']." (+".$arm1 -> fields['power'].") ".$agility."".$speed." (".$arm1 -> fields['wt']."/".$arm1 -> fields['maxwt']." ".DURABILITY.") [ <a href=\"equip.php?equip=".$arm1 -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm1 -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm1 -> fields['cost']." ".GOLD_COINS." | <a href=\"equip.php?napraw=".$arm1 -> fields['id']."\">".A_REPAIR."</a> ".FOR_A." ".$ckoszt." ".GOLD_COINS." ]<br />";
            }
            $arm1 -> MoveNext();
            $j = $j + 1;
            $menu = 'Y';
        }
    }
    if (isset($menu) && $menu == 'Y') 
      {
	if ($type != 'O' && $type != 'Q')
	  {
	    $arrMenu[1] = "Sprzedaj wszystkie zapasowe ".$nameitems;
	  }
	else
	  {
	    $arrMenu[1] = "Sprzedaj wszystkie ".$nameitems;
	  }
      }
    $arm -> Close();
    if ($secarm) 
    {
        $arm1 -> Close();
    }
    $arrBackpack[] = array('items' => $arrshow,
			   'sell' => "Sprzedaj wybrane ".$nameitems,
			   'type' => $type,
			   'amount' => $j,
			   'menu' => $arrMenu,
			   'name' => $smartyname,
			   'selltype' => $strSelltype);
}

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
  {
    $strChecked = "";
  }
else
  {
    $strChecked = "checked=checked";
  }

/**
* Assign variables to template
*/
$smarty -> assign(array("Arrowhead" => '', 
                        "Action" => '', 
                        "Potions1" => 0,
			"Pets1" => 0,
                        "Repairequip" => '', 
                        "Arrows1" => '',
			"Ilevel" => 'Poziom',
                        "Equipped" => EQUIPPED,
                        "Tarrows" => ARROWS,
                        "Asell" => A_SELL,
			"Refresh" => REFRESH,
			"Checked" => $strChecked,
			"Aback" => "Wróć na górę",
			"Checkall" => "Zaznacz wszystko",
			"Uncheckall" => "Odznacz wszystko",
			"Changeselected" => "Odwróć zaznaczenie"));

function checkmagic($strName, $strMagic)
{
  switch ($strMagic)
    {
    case 'E':
      $strName .= ' (Żywioł: Ziemia)';
      break;
    case 'W':
      $strName .= ' (Żywioł: Woda)';
      break;
    case 'F':
      $strName .= ' (Żywioł: Ogień)';
      break;
    case 'A':
      $strName .= ' (Żywioł: Powietrze)';
      break;
    default:
      break;
    }
  return $strName;
}

/**
* Wear equipment
*/
if (isset($_GET['equip'])) 
{
    equip($_GET['equip']);
    $player->equip = $player->equipment();
}

/**
 * Take off equipment
 */
if (isset($_GET['schowaj'])) 
{
    checkvalue($_GET['schowaj']);
    $bron = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['schowaj']);
    if (!$bron -> fields['id']) 
    {
        error (NO_ITEM);
    }
    if ($player -> id != $bron -> fields['owner']) 
    {
        error (NOT_YOUR);
    }
    if ($bron -> fields['status'] != 'E') 
    {
        error (ERROR);
    }
    if (isset($player->equip[6][0])) 
    {
      $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$player->equip[6][1]."' AND status='U' AND owner=".$player -> id." AND power=".$player->equip[6][2]." AND poison=".$player->equip[6][8]." AND `ptype`='".$player->equip[6][3]."' AND `magic`='".$player->equip[6][10]."'");
    }
    if ($bron -> fields['type'] == 'B') 
    {
        if (!isset($test -> fields['id'])) 
        {
            $db -> Execute("UPDATE `equipment` SET `status`='U' WHERE `type`='R' AND `owner`=".$player -> id." AND `status`='E'");
        } 
            else 
        {
            $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$player->equip[6][6]." WHERE `id`=".$test -> fields['id']);
            $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[6][0]);
        }
    }
    if ($bron -> fields['type'] == 'R') 
    {
        if (!isset($test -> fields['id'])) 
        {
            $db -> Execute("UPDATE `equipment` SET `status`='U' WHERE `type`='R' AND `owner`=".$player -> id." AND `status`='E'");
        } 
            else 
        {
            $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$player->equip[6][6]." WHERE `id`=".$test -> fields['id']);
            $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[6][0]);
        }
    } 
        else 
    {
        $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$bron -> fields['name']."' AND wt=".$bron -> fields['wt']." AND type='".$bron -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$bron -> fields['power']." AND zr=".$bron -> fields['zr']." AND szyb=".$bron -> fields['szyb']." AND maxwt=".$bron -> fields['maxwt']." AND poison=".$bron -> fields['poison']." AND ptype='".$bron -> fields['ptype']."' AND cost=".$bron -> fields['cost']." AND magic='".$bron->fields['magic']."'");
        if ($test -> fields['id']) 
        {
            $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
        } 
            else 
        {
            $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, ptype, repair) VALUES(".$player -> id.",'".$bron -> fields['name']."',".$bron -> fields['power'].",'".$bron -> fields['type']."',".$bron -> fields['cost'].",".$bron -> fields['zr'].",".$bron -> fields['wt'].",".$bron -> fields['minlev'].",".$bron -> fields['maxwt'].",1,'".$bron -> fields['magic']."',".$bron -> fields['poison'].",".$bron -> fields['szyb'].",'".$bron -> fields['twohand']."', '".$bron -> fields['ptype']."', ".$bron -> fields['repair'].")") or error(E_DB);
        }
        $test -> Close();
        $db -> Execute("DELETE FROM equipment WHERE id=".$bron -> fields['id']);
    }
    $player->equip = $player->equipment();
    message("success", "Zdjąłeś ".$bron->fields['name']);
    $bron -> Close();
}

/**
 * Repair used items
 */
if (isset($_GET['napraw_uzywane'])) 
  {
    $text = '';
    $arrTypes = array(6, 7, 8, 9);
    $blnNomoneys = FALSE;
    for ($i = 0; $i < count($player->equip); $i++)
      {
	if (in_array($i, $arrTypes))
	  {
	    continue;
	  }
	if ($player->equip[$i][9] != $player->equip[$i][6])
	  {
	    $intCost = ceil($player->equip[$i][11] * (1 - $player->equip[$i][6] / $player->equip[$i][9]));
            if ($intCost > $player->credits) 
	      {
		$blnNomoneys = TRUE;
		break;
	      }
            $player->credits -= $intCost;
            $db -> Execute("UPDATE `equipment` SET `wt`=".$player->equip[$i][9]." WHERE `id`=".$player->equip[$i][0]);
	    $player->equip[$i][6] = $player->equip[$i][9];
            $text = $text.YOU_REPAIR." ".$player->equip[$i][1]." ".AND_COST." ".$intCost." ".GOLD_COINS.".<br />";
	  }
      }
    $db -> Execute("UPDATE `players` SET `credits`=".$player->credits." WHERE `id`=".$player -> id);
    if ($text != '')
      {
	message("success", $text);
      }
    if ($blnNomoneys)
      {
	message('error', "Nie stać Cię na naprawę ekwipunku.");
      }
}

/**
 * Repair selected equipment
 */
if (isset($_GET['repair']))
  {
    $text = '';
    $arrTypes = array(6, 7, 8, 9);
    $blnNomoneys = FALSE;
    for ($i = 0; $i < count($player->equip); $i++)
      {
	if (in_array($i, $arrTypes))
	  {
	    continue;
	  }
	if (isset($_POST[$player->equip[$i][0]]))
	  {
	    if ($player->equip[$i][9] != $player->equip[$i][6]) 
	      {
		$intCost = ceil($player->equip[$i][11] * (1 - $player->equip[$i][6] / $player->equip[$i][9]));
		if ($intCost > $player->credits) 
		  {
		    $blnNomoneys = TRUE;
		    break;
		  }
		$player->credits -= $intCost;
		$db->Execute("UPDATE `equipment` SET `wt`=".$player->equip[$i][9]." WHERE `id`=".$player->equip[$i][0]);
		$player->equip[$i][6] = $player->equip[$i][9];
		$text = $text."Naprawiłeś ".$player->equip[$i][1].", kosztowało Ciebie to ".$intCost." ".GOLD_COINS.".<br />";
	      }
	  }
      }
    $db -> Execute("UPDATE `players` SET `credits`=".$player->credits." WHERE `id`=".$player -> id);
    if ($text != '')
      {
	message("success", $text);
      }
    if ($blnNomoneys)
      {
	message('error', "Nie stać Cię na naprawę ekwipunku.");
      }
  }

/**
* Fill equiped arrows
*/
if (isset($_GET['fill']))
  {
    if (!$player->equip[6][0])
      {
	error('Nie masz strzał w kołczanie.');
      }
    if ($player->equip[6][6] == 25)
    {
        error(NOT_NEED);
    }
    $objArrows2 = $db -> Execute("SELECT `wt`, `id` FROM `equipment` WHERE name='".$player->equip[6][1]."' AND `power`=".$player->equip[6][2]." AND `status`='U' AND `owner`=".$player -> id." AND `ptype`='".$player->equip[6][3]."' AND `poison`=".$player->equip[6][8]." AND `magic`='".$player->equip[6][10]."'");
    if (!$objArrows2 -> fields['id'])
    {
        error(NO_ARROWS);
    }
    $intAmount = 25 - $player->equip[6][6];
    if ($objArrows2 -> fields['wt'] <= $intAmount)
    {
        $db -> Execute("DELETE FROM equipment WHERE id=".$objArrows2 -> fields['id']);
        $intFill = $objArrows2 -> fields['wt'];
    }
        else
    {
        $db -> Execute("UPDATE equipment SET wt=wt-".$intAmount." WHERE id=".$objArrows2 -> fields['id']);
        $intFill = $intAmount;
    }
    $objArrows2 -> Close();
    $db -> Execute("UPDATE equipment SET wt=wt+".$intFill." WHERE id=".$player->equip[6][0]);
    $player->equip[6][6] = 25;
    message("success", "Uzupełniłeś kołczan");
}

//Weapon
if (!$player->equip[0][0]) 
  {
    $player->equip[0] = $player->equip[1];
  }
if ($player->equip[0][0]) 
  {
    switch ($player->equip[0][3])
      {
      case 'D':
	$player->equip[0][1] .= ' (Dynallca +'.$player->equip[0][8].')';
	break;
      case 'N':
	$player->equip[0][1] .= ' (Nutari +'.$player->equip[0][8].')';
	break;
      case 'I':
	$player->equip[0][1] .= ' (Illani +'.$player->equip[0][8].')';
	break;
      default:
	break;
      }
    $player->equip[0][1] = checkmagic($player->equip[0][1], $player->equip[0][10]);
    if ($player->equip[0][5] != 0)
      {
	$strAgi = ' (+'.($player->equip[0][5] * -1).' zr)';
      }
    else
      {
	$strAgi = '';
      }
    $smarty -> assign ("Weapon", "<input type=\"checkbox\" name=\"".$player->equip[0][0]."\" /><b>".WEAPON.':</b> <span title="Poziom: '.$player->equip[0][4].'">'.$player->equip[0][1]." (+".$player->equip[0][2].") (+".$player->equip[0][7]." ".EQUIP_SPEED.")".$strAgi." (".$player->equip[0][6]."/".$player->equip[0][9]." ".DURABILITY.")</span> [<a href=\"equip.php?schowaj=".$player->equip[0][0]."\">".HIDE_WEP."</a>]<br />\n");
  }
elseif ($player->equip[7][0])
{
  $smarty -> assign ("Weapon", "<b>".STAFF.':</b> <span title="Poziom: '.$player->equip[7][4].'">'.$player->equip[7][1]." (".SPELL_POWER.")</span> [<a href=\"equip.php?schowaj=".$player->equip[7][0]."\">".HIDE."</a>]<br />\n");
}
 else 
   {
     $smarty -> assign ("Weapon", "<b>".WEAPON.":</b> ".EMPTY_SLOT."<br />\n");
   }

//Second weapon
if ($player->equip[11][0]) 
  {
    switch ($player->equip[11][3])
      {
      case 'D':
	$player->equip[11][1] .= ' (Dynallca +'.$player->equip[11][8].')';
	break;
      case 'N':
	$player->equip[11][1] .= ' (Nutari +'.$player->equip[11][8].')';
	break;
      case 'I':
	$player->equip[11][1] .= ' (Illani +'.$player->equip[11][8].')';
	break;
      default:
	break;
      }
    $player->equip[11][1] = checkmagic($player->equip[11][1], $player->equip[11][10]);
    if ($player->equip[11][5] != 0)
      {
	$strAgi = ' (+'.($player->equip[11][5] * -1).' zr)';
      }
    else
      {
	$strAgi = '';
      }
    $smarty -> assign ("Weapon2", "<input type=\"checkbox\" name=\"".$player->equip[11][0].'" /><b>Druga broń:</b> <span title="Poziom: '.$player->equip[11][4].'">'.$player->equip[11][1]." (+".$player->equip[11][2].") (+".$player->equip[11][7]." ".EQUIP_SPEED.")".$strAgi." (".$player->equip[11][6]."/".$player->equip[11][9]." ".DURABILITY.")</span> [<a href=\"equip.php?schowaj=".$player->equip[11][0]."\">".HIDE_WEP."</a>]<br />\n");
  }
 else 
   {
     $smarty -> assign ("Weapon2", "");
   }
 
/**
 * Equipped arrows
 */
if ($player->equip[1][0]) 
{
    if ($player->equip[6][0]) 
    {
      switch ($player->equip[6][3])
      {
      case 'D':
	$player->equip[6][1] .= ' (Dynallca +'.$player->equip[6][8].')';
	break;
      case 'N':
	$player->equip[6][1] .= ' (Nutari +'.$player->equip[6][8].')';
	break;
      case 'I':
	$player->equip[6][1] .= ' (Illani +'.$player->equip[6][8].')';
	break;
      default:
	break;
      }
      $player->equip[6][1] = checkmagic($player->equip[6][1], $player->equip[6][10]);
      $smarty -> assign ("Arrows", "<b>".QUIVER.':</b> <span title="Poziom: '.$player->equip[6][4].'">'.$player->equip[6][1]." (+".$player->equip[6][2].") (".$player->equip[6][6]." ".ARROWS.")</span> [<a href=\"equip.php?schowaj=".$player->equip[6][0]."\">".HIDE_ARR."</a>] [<a href=\"equip.php?fill\">".A_FILL."</a>]<br />\n");
    } 
        else 
    {
        $smarty -> assign ("Arrows", "<b>".QUIVER.":</b> ".EMPTY_SLOT."<br />\n");
    }
} 
    else 
{
    $smarty -> assign ("Arrows", "");
}

//Helmet
if ($player->equip[2][0]) 
  {
    $agility = '';
    if ($player->equip[2][5] < 0) 
      {
	$player->equip[2][5] = str_replace("-","",$player->equip[2][5]);
	$agility = "(+".$player->equip[2][5]." ".EQUIP_AGI.")";
      } 
    elseif ($player->equip[2][5] > 0) 
      {
	$agility = "(-".$player->equip[2][5]." ".EQUIP_AGI.")";
      }
    $player->equip[2][1] = checkmagic($player->equip[2][1], $player->equip[2][10]);
    $smarty -> assign ("Helmet", "<input type=\"checkbox\" name=\"".$player->equip[2][0]."\" /><b>".HELMET.':</b> <span title="Poziom: '.$player->equip[2][4].'">'.$player->equip[2][1]." (+".$player->equip[2][2].") ".$agility." (".$player->equip[2][6]."/".$player->equip[2][9]." ".DURABILITY.")</span> [<a href=\"equip.php?schowaj=".$player->equip[2][0]."\">".WEAR_OFF."</a>]<br />\n");
  } 
 else 
   {
     $smarty -> assign ("Helmet", "<b>".HELMET.":</b> ".EMPTY_SLOT."<br />\n");
   }
//Cape
if ($player->equip[3][0]) 
  {
    $agility = '';
    if ($player->equip[3][5] < 0) 
      {
	$player->equip[3][5] = str_replace("-","",$player->equip[3][5]);
	$agility = "(+".$player->equip[3][5]." ".EQUIP_AGI.")";
      } 
    elseif ($player->equip[3][5] > 0) 
      {
	$agility = "(-".$player->equip[3][5]." ".EQUIP_AGI.")";
      }
    $player->equip[3][1] = checkmagic($player->equip[3][1], $player->equip[3][10]);
    $smarty -> assign ("Armor", "<input type=\"checkbox\" name=\"".$player->equip[3][0]."\" /><b>".ARMOR.':</b> <span title="Poziom: '.$player->equip[3][4].'">'.$player->equip[3][1]." (+".$player->equip[3][2].") ".$agility." (".$player->equip[3][6]."/".$player->equip[3][9]." ".DURABILITY.")</span> [<a href=\"equip.php?schowaj=".$player->equip[3][0]."\">".WEAR_OFF."</a>]<br />\n");
  }
elseif ($player->equip[8][0])
{
  $smarty -> assign ("Armor", "<b>".CAPE.':</b> <span title="Poziom: '.$player->equip[7][4].'">'.$player->equip[8][1]." (+".$player->equip[8][2]." % ".EQUIP_MANA.")</span> [<a href=\"equip.php?schowaj=".$player->equip[8][0]."\">".WEAR_OFF."</a>]<br />\n");
}
 else 
   {
     $smarty -> assign ("Armor", "<b>".ARMOR.":</b> ".EMPTY_SLOT."<br />\n");
   }
//Shield
if ($player->equip[5][0]) 
{
    if ($player->equip[5][5] < 0) 
    {
        $player->equip[5][5] = str_replace("-","",$player->equip[5][5]);
        $agility1 = "(+".$player->equip[5][5]." ".EQUIP_AGI.")";
    } 
        elseif ($player->equip[5][5] > 0) 
    {
        $agility1 = "(-".$player->equip[5][5]." ".EQUIP_AGI.")";
    } 
        else 
    {
        $agility1 = '';
    }
    $player->equip[5][1] = checkmagic($player->equip[5][1], $player->equip[5][10]);
    $smarty -> assign ("Shield", "<input type=\"checkbox\" name=\"".$player->equip[5][0]."\" /><b>".SHIELD.':</b> <span title="Poziom: '.$player->equip[5][4].'">'.$player->equip[5][1]." (+".$player->equip[5][2].") ".$agility1." (".$player->equip[5][6]."/".$player->equip[5][9]." ".DURABILITY.")</span> [<a href=\"equip.php?schowaj=".$player->equip[5][0]."\">".WEAR_OFF."</a>]<br />\n");
} 
    else 
{
    $smarty -> assign ("Shield", "<b>".SHIELD.":</b> ".EMPTY_SLOT."<br />\n");
}
//Legs
if ($player->equip[4][0]) 
{
    if ($player->equip[4][5] < 0) 
    {
        $player->equip[4][5] = str_replace("-","",$player->equip[4][5]);
        $agility2 = "(+".$player->equip[4][5]." ".EQUIP_AGI.")";
    } 
        elseif ($player->equip[4][5] > 0) 
    {
        $agility2 = "(-".$player->equip[4][5]." ".EQUIP_AGI.")";
    }  
        else 
    {
        $agility2 = '';
    }
    $player->equip[4][1] = checkmagic($player->equip[4][1], $player->equip[4][10]);
    $smarty -> assign ("Legs", "<input type=\"checkbox\" name=\"".$player->equip[4][0]."\" /><b>".LEGS.':</b> <span title="Poziom: '.$player->equip[4][4].'">'.$player->equip[4][1]." (+".$player->equip[4][2].") ".$agility2." (".$player->equip[4][6]."/".$player->equip[4][9]." ".DURABILITY.")</span> [<a href=\"equip.php?schowaj=".$player->equip[4][0]."\">".WEAR_OFF."</a>]<br />\n");
} 
    else 
{
    $smarty -> assign ("Legs", "<b>".LEGS.":</b> ".EMPTY_SLOT."<br />\n");
}

/**
 * Weared rings
 */
if ($player->equip[9][0])
{
  $smarty -> assign("Ring1", "<b>".RING.':</b> <span title="Poziom: '.$player->equip[9][4].'">'.$player->equip[9][1]." (+".$player->equip[9][2].")</span> [<a href=\"equip.php?schowaj=".$player->equip[9][0]."\">".WEAR_OFF."</a>]<br />");
}
    else
{
    $smarty -> assign("Ring1", "<b>".RING.":</b> ".EMPTY_SLOT."<br />");
}
if ($player->equip[10][0])
{
  $smarty -> assign("Ring2", "<b>".RING.':</b> <span title="Poziom: '.$player->equip[10][4].'">'.$player->equip[10][1]." (+".$player->equip[10][2].")</span> [<a href=\"equip.php?schowaj=".$player->equip[10][0]."\">".WEAR_OFF."</a>]<br />");
}
    else
{
    $smarty -> assign("Ring2", "<b>".RING.":</b> ".EMPTY_SLOT."<br />");
}

/**
 * Weared tool
 */
if ($player->equip[12][0])
  {
    $arrName = explode(' ', $player->equip[12][1]);
    $arrTools = array('Wytrychy' => ' do akcji złodziejskich',
		      'Miechy' => ' do Hutnictwa',
		      'Piła' => ' do Drwalnictwa',
		      'Kilof' => ' do Górnictwa',
		      'Uprząż' => ' do Hodowli',
		      'Nożyk' => ' do Jubilerstwa',
		      'Sierp' => ' do Zielarstwa',
		      'Moździerz' => ' do Alchemii',
		      'Ciesak' => ' do Stolarstwa',
		      'Młot' => ' do Kowalstwa');
    if (count($arrName) == 3)
      {
	$strName = $arrName[0];
      }
    else
      {
	$strName = $arrName[1];
      }
    $smarty->assign("Tool", "<input type=\"checkbox\" name=\"".$player->equip[12][0].'" /><b>Narzędzie:</b> <span title="Poziom: '.$player->equip[12][4].'">'.$player->equip[12][1]." (+".$player->equip[12][2]." % ".$arrTools[$strName].") (".$player->equip[12][6]."/".$player->equip[12][9]." ".DURABILITY.") </span>[<a href=\"equip.php?schowaj=".$player->equip[12][0]."\">".WEAR_OFF."</a>]<br />");
  }
else
  {
    $smarty->assign("Tool", "<b>Narzędzie:</b> brak<br />");
  }

/**
 * Use pet in battles
 */
if (isset($_GET['usep']))
  {
    checkvalue($_GET['usep']);
    $objPet = $db->Execute("SELECT `id`, `status`, `active`, `power`, `defense` FROM `core` WHERE `owner`=".$player->id." AND `id`=".$_GET['usep']);
    if (!$objPet->fields['id'])
      {
	error('Nie ma takiego chowańca.');
      }
    $blnValid = TRUE;
    if ($objPet->fields['status'] == 'Dead')
      {
	message('error', 'Nie możesz użyć tego chowańca w walce, ponieważ jest martwy.');
	$blnValid = FALSE;
      }
    if ($objPet->fields['active'] == 'Y')
      {
	message('error', 'Nie możesz użyć tego chowańca w walce, ponieważ walczy on na arenie chowańców.');
	$blnValid = FALSE;
      }
    if ($blnValid)
      {
	if ($player->pet[0])
	  {
	    $db->Execute("UPDATE `core` SET `active`='N' WHERE `id`=".$player->pet[0]);
	  }
	$db->Execute("UPDATE `core` SET `active`='B' WHERE `id`=".$_GET['usep']);
	$player->pet = array($_GET['usep'], $objPet->fields['power'], $objPet->fields['defense']);
	message('success', 'Wybrałeś chowańca do walki.');
      }
    $objPet->Close();
  }

/**
 * Remove pet from battles
 */
if (isset($_GET['release2']))
  {
    checkvalue($_GET['release2']);
    $objPet = $db->Execute("SELECT `id` FROM `core` WHERE `owner`=".$player->id." AND `id`=".$_GET['release2']." AND `active`='B'");
    if (!$objPet->fields['id'])
      {
	error('Nie ma takiego chowańca.');
      }
    $db->Execute("UPDATE `core` SET `active`='N' WHERE `id`=".$_GET['release2']);
    $player->pet = array(0, 0, 0);
    message('success', 'Zwolniłeś chowańca z walk.');
  }

/**
 * Used pet
 */
if ($player->pet[0])
  {
    $objPet = $db->Execute("SELECT `name`, `corename` FROM `core` WHERE `id`=".$player->pet[0]);
    if ($objPet->fields['corename'] != '')
      {
	$strName = $objPet->fields['corename'];
      }
    else
      {
	$strName = $objPet->fields['name'];
      }
    $smarty->assign("Pet", "<b>Chowaniec:</b> ".$strName." (Siła: ".$player->pet[1].") (Obrona: ".$player->pet[2].") [<a href=\"equip.php?release2=".$player->pet[0]."\">zwolnij</a>]<br />");
  }
 else
   {
     $smarty->assign("Pet", "<b>Chowaniec:</b> brak<br />");
   }

if ($player->equip[3][0] || $player->equip[0][0] || $player->equip[4][0] || $player->equip[2][0] || $player->equip[5][0] || $player->equip[12][0]) 
{
    $smarty -> assign ("Repairequip", "[<a href=\"equip.php?napraw_uzywane\">".A_REPAIR2."</a>] <br /><input type=\"submit\" value=\"Napraw wybrane\" /><br />\n");
}

/**
 * Change name for pet
 */
if (isset($_GET['name']))
  {
    checkvalue($_GET['name']);
    $objPet = $db->Execute("SELECT `id` FROM `core` WHERE `owner`=".$player->id." AND `id`=".$_GET['name']);
    if (!$objPet->fields['id'])
      {
	error('Nie ma takiego chowańca.');
      }
    $objPet->Close();
    if (!isset($_GET['step']))
      {
	$smarty->assign(array("Achange" => 'Zmień',
			      "Tpname" => 'imię chowańcowi na'));
      }
    else
      {
	$_POST['cname'] = htmlspecialchars($_POST['cname'], ENT_QUOTES);
	$strName = $db -> qstr($_POST['cname'], get_magic_quotes_gpc());
	$db -> Execute("UPDATE `core` SET `corename`=".$strName." WHERE `id`=".$_GET['name']);
	message("success", "Zmieniłeś imię chowańca.");
      }
  }

/**
 * Release selected pets
 **/
if (isset($_GET['releasefew']))
  {
    $objPets = $db->Execute("SELECT `id` FROM `core` WHERE `owner`=".$player->id);
    $arrIds = array();
    while (!$objPets->EOF)
      {
	if (isset($_POST[$objPets->fields['id']]))
	  {
	    $arrIds[] = $objPets->fields['id'];
	  }
	$objPets->MoveNext();
      }
    $objPets->Close();
    if (count($arrIds))
      {
	$db->Execute("DELETE FROM `core` WHERE `id` IN (".implode(', ', $arrIds).")");
	message('success', 'Uwolniłeś wybrane chowańce');
      }
  }

/**
 * Release pet
 */
if (isset($_GET['release']))
  {
    checkvalue($_GET['release']);
    $objPet = $db->Execute("SELECT `id` FROM `core` WHERE `owner`=".$player->id." AND `id`=".$_GET['release']);
    if (!$objPet->fields['id'])
      {
	error('Nie ma takiego chowańca.');
      }
    $objPet->Close();
    $db->Execute("DELETE FROM `core` WHERE `id`=".$_GET['release']);
    message("success", "Uwolniłeś chowańca.");
  }

/**
 * Heal pet
 */
if (isset($_GET['heal']))
  {
    checkvalue($_GET['heal']);
    $objPet = $db->Execute("SELECT `id`, `status`, `power`, `defense` FROM `core` WHERE `owner`=".$player->id." AND `id`=".$_GET['heal']);
    if (!$objPet->fields['id'])
      {
	error('Nie ma takiego chowańca.');
      }
    $blnValid = TRUE;
    if ($objPet->fields['status'] != 'Dead')
      {
	message('error', 'Ten chowaniec nie potrzebuje leczenia');
	$blnValid = FALSE;
      }
    $intCost = ($objPet->fields['power'] + $objPet->fields['defense']) * 5;
    $intMith = floor($intCost / 200);
    if ($player->credits < $intCost)
      {
	message('error', 'Nie masz tylu sztuk złota aby uleczyć tego chowańca.');
	$blnValid = FALSE;
      }
    if ($player->platinum < $intMith)
      {
	message('error', 'Nie masz tylu sztuk mithrilu aby uleczyć tego chowańca.');
	$blnValid = FALSE;
      }
    if ($blnValid)
      {
	$player->credits -= $intCost;
	$player->platinum -= $intMith;
	$db->Execute("UPDATE `players` SET `credits`=`credits`-".$intCost.", `platinum`=`platinum`-".$intMith." WHERE `id`=".$player->id);
	$db->Execute("UPDATE `core` SET `status`='Alive' WHERE `id`=".$_GET['heal']);
	message('success', 'Uleczyłeś wybranego chowańca.');
      }
    $objPet->Close();
  }

/**
 * Learn new plan (craftsmen only)
 */
if (isset($_GET['learn']))
  {
    if ($player->clas != 'Rzemieślnik')
      {
	error('Tylko rzemieślnik może uczyć się planów.');
      }
    checkvalue($_GET['learn']);
    $objPlan = $db->Execute("SELECT `id`, `name`, `minlev`, `amount` FROM `equipment` WHERE `id`=".$_GET['learn']." AND `owner`=".$player->id." AND `status`='U'");
    if (!$objPlan->fields['id'])
      {
	error('Nie ma takiego planu.');
      }
    if ($objPlan->fields['minlev'] > $player->skills['smith'][1])
      {
	error('Nie możesz jeszcze nauczyć się tego planu.');
      }
    $objPlan2 = $db->Execute("SELECT `amount`, `type` FROM `plans` WHERE `name`='".$objPlan->fields['name']."' AND `level`=".$objPlan->fields['minlev']);
    $objTest = $db->Execute("SELECT `id` FROM `smith` WHERE `owner`=".$player->id." AND `name`='".$objPlan->fields['name']."' AND `level`=".$objPlan->fields['minlev']);
    if ($objTest->fields['id'])
      {
	error('Znasz już ten plan.');
      }
    $objTest->Close();

    $db -> Execute("INSERT INTO `smith` (`owner`, `name`, `type`, `cost`, `amount`, `level`, `twohand`, `elite`, `elitetype`) VALUES(".$player->id.", '".$objPlan->fields['name']."', 'E', 1, ".$objPlan2->fields['amount'].", ".$objPlan->fields['minlev'].", 'N', 0, 'S')");
    $objPlan2->Close();
    if ($objPlan->fields['amount'] == 1)
      {
	$db->Execute("DELETE FROM `equipment` WHERE `id`=".$_GET['learn']);
      }
    else
      {
	$db->Execute("UPDATE `equipment` SET `amount`=`amount`-1 WHERE `id`=".$_GET['learn']);
      }
    if ($player->gender == 'M')
      {
	$strSuffix = 'eś';
      }
    else
      {
	$strSuffix = 'aś';
      }
    message('success', 'Nauczył'.$strSuffix.' się wykonywać przedmiot: '.$objPlan->fields['name'].' (poziom: '.$objPlan->fields['minlev'].')');
    $objPlan->Close();
  }

/**
* Sell one item
*/
if (isset($_GET['sell'])) 
{
    checkvalue($_GET['sell']);
    $sell = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['sell']." AND `status`='U'");
    if (!$sell -> fields['id']) 
    {
        error (NO_ITEM);
    }
    if ($sell -> fields['wt'] < $sell -> fields['maxwt'] && $sell -> fields['type'] != 'R') 
    {
        error (BROKEN_ITEM);
    }
    if ($player -> id != $sell -> fields['owner']) 
    {
        error (NOT_YOUR);
    }
    if ($sell->fields['name'] == 'Solidna sakiewka' && $sell->fields['type'] == 'Q')
      {
	$intTime = rand(2, 9);
	$db->Execute("UPDATE `revent` SET `state`=4, `qtime`=".$intTime." WHERE `pid`=".$player->id);
      }
    if ($sell -> fields['type'] == 'R') 
    {
        $costone = ($sell -> fields['cost'] / 100);
        $sell -> fields['cost'] = ceil($costone * $sell -> fields['wt']);
        $db -> Execute("DELETE FROM equipment WHERE id=".$sell -> fields['id']);
    }
    $db -> Execute("UPDATE players SET credits=credits+".$sell -> fields['cost']." WHERE id=".$player -> id);
    $amount = $sell -> fields['amount'] - 1;
    if ($amount > 0) 
    {
        $db -> Execute("UPDATE equipment SET amount=amount-1 WHERE id=".$sell -> fields['id']);
    } 
        else 
    {
        $db -> Execute("DELETE FROM equipment WHERE id=".$sell -> fields['id']);
    }
    message("success", YOU_SELL." ".$sell -> fields['name']." ".FOR_A." ".$sell -> fields['cost']." ".GOLD_COINS.".");
    $sell -> Close();
}

/**
 * Sell one potion
 */
if (isset($_GET['sellpotion']))
  {
    checkvalue($_GET['sellpotion']);
    $sell = $db -> Execute("SELECT * FROM `potions` WHERE `id`=".$_GET['sellpotion']." AND `status`='K'");
    if (!$sell->fields['id']) 
      {
        error(NO_ITEM);
      }
    if ($player->id != $sell->fields['owner']) 
      {
        error(NOT_YOUR);
      }
    $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$sell->fields['cost']." WHERE `id`=".$player -> id);
    $amount = $sell->fields['amount'] - 1;
    if ($amount > 0) 
    {
        $db->Execute("UPDATE `potions` SET `amount`=`amount`-1 WHERE `id`=".$sell->fields['id']);
    } 
        else 
    {
        $db -> Execute("DELETE FROM `potions` WHERE `id`=".$sell->fields['id']);
    }
    if ($player->gender == 'F')
      {
	$strLast = "aś";
      }
    else
      {
	$strLast = "eś";
      }
    message("Action", "Sprzedał".$strLast." ".$sell->fields['name']." ".FOR_A." ".$sell->fields['cost']." ".GOLD_COINS.".");
    $sell->Close();
  }

/**
 * Sell all potions
 */
if (isset($_GET['sellpotions']))
  {
    $objPotion = $db->Execute("SELECT `id`, `cost`, `amount` FROM `potions` WHERE `owner`=".$player -> id." AND `status`='K'");
    if (!isset($objPotion->fields['id']))
      {
        error(NO_ITEMS2);
      }
    $zysk = 0;
    while (!$objPotion->EOF) 
    {
      $zysk += ($objPotion->fields['cost'] * $objPotion->fields['amount']);
      $db->Execute("DELETE FROM `potions` WHERE `id`=".$objPotion->fields['id']);
      $objPotion->MoveNext();
    }
    $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$zysk." WHERE `id`=".$player -> id);
    if ($player->gender == 'F')
      {
	$strLast = "aś";
      }
    else
      {
	$strLast = "eś";
      }
    message("success", "Sprzedał".$strLast." swoje mikstury ".FOR_A." ".$zysk." ".GOLD_COINS.".");
  }

/**
 * Sell selected items
 */
if (isset($_GET['sellchecked']))
  {
    $arrTypes = array('E', 'A', 'P');
    if (!in_array($_GET['sellchecked'], $arrTypes))
      {
	error(ERROR);
      }
    $intMoney = 0;
    //Sell items
    if (($_GET['sellchecked'] == 'E') || ($_GET['sellchecked'] == 'A'))
      {
	$objItems = $db->Execute("SELECT `id`, `name`, `type`, `cost`, `wt`, `maxwt`, `amount` FROM `equipment` WHERE `owner`=".$player->id." AND `status`='U'");
	while (!$objItems->EOF)
	  {
	    if (isset($_POST[$objItems->fields['id']]))
	      {
		if ($objItems->fields['type'] == 'R')
		  {
		    $costone = ($objItems->fields['cost'] / 100);
		    $objItems->fields['cost'] = ceil($costone * $objItems->fields['wt']);
		    $intMoney += $objItems->fields['cost'];
		    $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objItems->fields['id']);
		  }
		elseif ($objItems->fields['type'] == 'Q' && $objItems->fields['name'] == 'Solidna sakiewka')
		  {
		    $intTime = rand(2, 9);
		    $db->Execute("UPDATE `revent` SET `state`=4, `qtime`=".$intTime." WHERE `pid`=".$player->id);
		    $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objItems->fields['id']);
		  }
		elseif ($objItems->fields['maxwt'] == $objItems->fields['wt']) 
		  {
		    $intMoney += ($objItems->fields['cost'] * $objItems->fields['amount']);
		    $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$objItems->fields['id']);
		  }
	      }
	    $objItems->MoveNext();
	  }
	$objItems->Close();
      }
    //Sell potions
    elseif ($_GET['sellchecked'] == 'P')
      {
	$objPotion = $db->Execute("SELECT `id`, `cost`, `amount` FROM `potions` WHERE `owner`=".$player -> id." AND `status`='K'");
	while (!$objPotion->EOF)
	  {
	    if (isset($_POST[$objPotion->fields['id']]))
	      {
		$intMoney += ($objPotion->fields['cost'] * $objPotion->fields['amount']);
		$db->Execute("DELETE FROM `potions` WHERE `id`=".$objPotion->fields['id']);
	      }
	    $objPotion->MoveNext();
	  }
      }
    if ($intMoney > 0)
      {
	$db -> Execute("UPDATE `players` SET `credits`=`credits`+".$intMoney." WHERE `id`=".$player -> id);
	if ($player->gender == 'F')
	  {
	    $strLast = "aś";
	  }
	else
	  {
	    $strLast = "eś";
	  }
	message("success", "Sprzedał".$strLast." wybrane przedmioty ".FOR_A." ".$intMoney." ".GOLD_COINS.".");
      }
  }

/**
 * Sell all items
 */
if (isset($_GET['sprzedaj'])) 
  {
    $arrSell = array('A', 'W', 'H', 'L', 'R', 'C', 'T', 'S', 'I', 'O', 'Q', 'E', 'P');
    if (!in_array($_GET['sprzedaj'], $arrSell))
      {
	error(ERROR);
      }
    if ($_GET['sprzedaj'] != 'W') 
    {
        $zysk1 = $db -> Execute("SELECT * FROM `equipment` WHERE `type`='".$_GET['sprzedaj']."' AND `status`='U' AND `owner`=".$player -> id);
    } 
    else 
    {
        $zysk1 = $db -> Execute("SELECT * FROM `equipment` WHERE `type`='W' AND `status`='U' AND `owner`=".$player -> id);
        $zysk2 = $db -> Execute("SELECT * FROM equipment WHERE type='B' AND status='U' AND owner=".$player -> id);
    }
    if (!isset($zysk1 -> fields['id']) && !isset($zysk2 -> fields['id'])) 
    {
        error (NO_ITEMS2);
    }
    $arrType = array(ARMORS, WEAPONS, HELMETS, LEGS2, ARROWS2, CAPES, STAFFS, SHIELDS, RINGS, 'łupy', 'przedmioty do zadań', 'narzędzia', 'plany');
    $intKey = array_search($_GET['sprzedaj'], $arrSell);
    $typ = $arrType[$intKey];
    $zysk = 0;
    while (!$zysk1 -> EOF) 
    {
        if ($_GET['sprzedaj'] == 'R') 
        {
            $costone = ($zysk1 -> fields['cost'] / 100);
            $zysk1 -> fields['cost'] = ceil($costone * $zysk1 -> fields['wt']);
            $zysk = $zysk + $zysk1 -> fields['cost'];
            $db -> Execute("DELETE FROM equipment WHERE id=".$zysk1 -> fields['id']);
        }
	elseif ($_GET['sprzedaj'] == 'Q' && $zysk1->fields['name'] == 'Solidna sakiewka')
	  {
	    $intTime = rand(2, 9);
	    $db->Execute("UPDATE `revent` SET `state`=4, `qtime`=".$intTime." WHERE `pid`=".$player->id);
	    $db -> Execute("DELETE FROM equipment WHERE id=".$zysk1 -> fields['id']);
	  }
        elseif ($zysk1 -> fields['maxwt'] == $zysk1 -> fields['wt']) 
        {
            $zysk= $zysk + ($zysk1 -> fields['cost'] * $zysk1 -> fields['amount']);
            $db -> Execute("DELETE FROM equipment WHERE id=".$zysk1 -> fields['id']);
        }
        $zysk1 -> MoveNext();
    }
    $zysk1 -> Close();
    if (isset($zysk2 -> fields['id'])) 
    {
        while (!$zysk2 -> EOF) 
        {
            if ($zysk2 -> fields['maxwt'] == $zysk2 -> fields['wt']) 
            {
                $zysk= $zysk + ($zysk2 -> fields['cost'] * $zysk2 -> fields['amount']);
                if ($_GET['sprzedaj'] == 'W') 
                {
                    $typ = 'bronie';
                }
                $db -> Execute("DELETE FROM equipment WHERE id=".$zysk2 -> fields['id']);
            }
            $zysk2 -> MoveNext();
        }
        $zysk2 -> Close();
    }
    $db -> Execute("UPDATE players SET credits=credits+".$zysk." WHERE id=".$player -> id);
    message("success", YOU_SELL." ".$typ." ".FOR_A." ".$zysk." ".GOLD_COINS.".");
}

/**
 * Repair items in backpack
 */
if (isset($_GET['napraw'])) 
{
    checkvalue($_GET['napraw']);
    $rzecz = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['napraw']." AND `type` NOT IN ('R', 'T', 'C', 'I', 'O')");
    if (!$rzecz -> fields['id']) 
    {
        error (NO_ITEM);
    }
    if ($rzecz -> fields['wt'] == $rzecz -> fields['maxwt']) 
    {
        error (NO_REPAIR);
    }
    $intCost = ceil($rzecz->fields['repair'] * (1 - $rzecz->fields['wt'] / $rzecz->fields['maxwt']));
    if ($player->credits < $intCost) 
    {
        error (NO_MONEY);
    }
    if ($player->id != $rzecz->fields['owner']) 
    {
        error (NOT_YOUR);
    }
    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$rzecz -> fields['name']."' AND `wt`=".$rzecz -> fields['maxwt']." AND `type`='".$rzecz -> fields['type']."' AND `status`='U' AND `owner`=".$player -> id." AND `power`=".$rzecz -> fields['power']." AND `zr`=".$rzecz -> fields['zr']." AND `szyb`=".$rzecz -> fields['szyb']." AND `maxwt`=".$rzecz -> fields['maxwt']." AND `poison`=".$rzecz -> fields['poison']." AND `ptype`='".$rzecz -> fields['ptype']."' AND `cost`=".$rzecz -> fields['cost']." AND `repair`=".$rzecz -> fields['repair']) or die($db -> ErrorMsg());
    if (!$test -> fields['id']) 
    {
        $db -> Execute("INSERT INTO equipment (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `ptype`, `repair`, `twohand`) VALUES(".$player -> id.",'".$rzecz -> fields['name']."',".$rzecz -> fields['power'].",'".$rzecz -> fields['type']."',".$rzecz -> fields['cost'].",".$rzecz -> fields['zr'].",".$rzecz -> fields['maxwt'].",".$rzecz -> fields['minlev'].",".$rzecz -> fields['maxwt'].",1,'".$rzecz -> fields['magic']."',".$rzecz -> fields['poison'].",".$rzecz -> fields['szyb'].", '".$rzecz -> fields['ptype']."', ".$rzecz -> fields['repair'].", '".$rzecz -> fields['twohand']."')") or error($db -> ErrorMsg());
    } 
        else 
    {
        $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$test -> fields['id']);
    }
    $test -> Close();
    if ($rzecz -> fields['amount'] > 1)
    {
        $db -> Execute("UPDATE `equipment` SET `amount`=`amount`-1 WHERE `id`=".$_GET['napraw']);
    }
        else
    {
      $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$_GET['napraw']);
    }
    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$intCost." WHERE `id`=".$player -> id);
    message("success", YOU_REPAIR." <b>".$rzecz -> fields['name']."</b> ".FOR_A." <b>".$intCost."</b> ".GOLD_COINS.".");
    $rzecz -> Close();
}

/**
* Poison weapon
*/
if (isset ($_GET['poison'])) 
{
    checkvalue($_GET['poison']);
    $poison = $db -> Execute("SELECT id, power, amount, name FROM potions WHERE id=".$_GET['poison']." AND type='P' AND owner=".$player -> id);
    if (!$poison -> fields['id']) 
    {
        error (NO_POTION);
    }
    $wep = $db -> Execute("SELECT `id`, `name`, `amount`, `power`, `type`, `wt` FROM `equipment` WHERE `owner`=".$player -> id." AND `type` IN ('W', 'R') AND `status`='U' AND `poison`=0");
    $arrname = array();
    $arrid = array();
    $arramount = array();
    $arrPower = array();
    while (!$wep -> EOF) 
    {
        $arrname[] = $wep -> fields['name'];
        $arrid[] = $wep -> fields['id'];
	if ($wep->fields['type'] == 'W')
	  {
	    $arramount[] = $wep -> fields['amount'];
	  }
	else
	  {
	    $arramount[] = $wep -> fields['wt'];
	  }
	$arrPower[] = $wep->fields['power'];
        $wep -> MoveNext();
    }
    $wep -> Close();
    $smarty -> assign ( array("Poisonitem" => $arrname, 
			      "Poisonid" => $arrid, 
			      "Poisonamount" => $arramount,
			      "Poisonpower" => $arrPower,
			      "Poisonit" => POISON_IT,
			      "Tamount" => AMOUNT));
    if (isset($_GET['step']) && $_GET['step'] == 'poison') 
      {
	if (!isset($_POST['weapon']))
        {
            error (ERROR);
        }
	checkvalue($_POST['weapon']);
        $item = $db -> Execute("SELECT * FROM equipment WHERE id=".$_POST['weapon']);
        if (!$item -> fields['id']) 
        {
            error (NO_ITEMS);
        }
        if (($item -> fields['type'] != 'W') && ($item->fields['type'] != 'R'))
        {
            error (NO_WEAPON);
        }
        if ($item -> fields['owner'] != $player -> id) 
        {
            error (NOT_YOUR);
        }
        if ($item -> fields['status'] != 'U') 
        {
            error (NOT_IN);
        }
        if (strpos($poison -> fields['name'], '(K)') !== FALSE)
        {
            $intRoll = rand(0,100);
            if ($intRoll == 1)
	      {
		if ($item->fields['type'] == 'W')
		  {
		    $amount = $item -> fields['amount'] - 1;
		    if ($amount < 1) 
		      {
			$db -> Execute("DELETE FROM equipment WHERE id=".$item -> fields['id']);
		      }
                    else 
		      {
			$db -> Execute("UPDATE equipment SET amount=".$amount." WHERE id=".$item -> fields['id']);
		      }
		  }
		else
		  {
		    $amount = $item->fields['wt'] - 25;
		    if ($amount < 1)
		      {
			$db -> Execute("DELETE FROM `equipment` WHERE `id`=".$item -> fields['id']);
		      }
                    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=".$amount." WHERE `id`=".$item -> fields['id']);
		      }
		  }
		$amount = $poison -> fields['amount'] - 1;
		if ($amount < 1) 
		  {
		    $db -> Execute("DELETE FROM potions WHERE id=".$poison -> fields['id']);
		  }
		else 
		  {
		    $db -> Execute("UPDATE potions SET amount=".$amount." WHERE id=".$poison -> fields['id']);
		  }
                error(YOU_DESTROY);
            }
        }
            else
        {
            $intRoll = 51;
        }
        if ($intRoll > 50)
        {
            $intPower = $poison -> fields['power'];
	    if (strpos($poison->fields['name'], "Dynallca") !== FALSE)
            {
                $strPtype = 'D';
            }
	    elseif (strpos($poison->fields['name'], "Nutari") !== FALSE)
            {
                $strPtype = 'N';
            }
	    elseif (strpos($poison->fields['name'], "Illani") !== FALSE)
            {
	        $strPtype = 'I';
            }
	    if ($item->fields['type'] == 'W')
	      {
		$name = POISONED." ".$item -> fields['name'];
		$test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND wt=".$item -> fields['wt']." AND type='W' AND status='U' AND owner=".$player -> id." AND power=".$item -> fields['power']." AND zr=".$item -> fields['zr']." AND szyb=".$item -> fields['szyb']." AND maxwt=".$item -> fields['maxwt']." AND poison=".$intPower." AND ptype='".$strPtype."' AND `wt`=".$item->fields['wt']);
		if (!$test -> fields['id']) 
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `ptype`, `repair`, `twohand`) VALUES(".$player -> id.",'".$name."',".$item -> fields['power'].",'".$item->fields['type']."',".$item -> fields['cost'].",".$item -> fields['zr'].",".$item -> fields['wt'].",".$item -> fields['minlev'].",".$item -> fields['maxwt'].",1,'N',".$intPower.",".$item -> fields['szyb'].", '".$strPtype."', ".$item -> fields['repair'].", '".$item -> fields['repair']."')") or error($db -> ErrorMsg());
		      
		  } 
		else 
		  {
		    $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
		  }
	      }
	    else
	      {
		$name = "Zatrute"." ".$item -> fields['name'];
		$test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND type='R' AND status='U' AND owner=".$player -> id." AND power=".$item -> fields['power']." AND zr=".$item -> fields['zr']." AND szyb=".$item -> fields['szyb']." AND poison=".$intPower." AND ptype='".$strPtype."'");
		if (!$test -> fields['id']) 
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `ptype`, `repair`, `twohand`) VALUES(".$player -> id.",'".$name."',".$item -> fields['power'].",'".$item->fields['type']."',".$item -> fields['cost'].",".$item -> fields['zr'].", 25,".$item -> fields['minlev'].", 25, 1, 'N',".$intPower.",".$item -> fields['szyb'].", '".$strPtype."', ".$item -> fields['repair'].", '".$item -> fields['repair']."')") or error($db -> ErrorMsg());
		  }
		else
		  {
		    if ($item->fields['wt'] <= 25)
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$item->fields['wt']." WHERE `id`=".$test -> fields['id']);
		      }
		    else
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+25 WHERE `id`=".$test -> fields['id']);
		      }
		  }
	      }
            $test -> Close();
	    if ($item->fields['type'] == 'W')
	      {
		$iamount = $item -> fields['amount'] - 1;
		if ($iamount > 0) 
		  {
		    $db -> Execute("UPDATE equipment SET amount=amount-1 WHERE id=".$item -> fields['id']);
		  }  
                else 
		  {
		    $db -> Execute("DELETE FROM equipment WHERE id=".$item -> fields['id']);
		  }
	      }
	    else
	      {
		$iamount = $item->fields['wt'] - 25;
		if ($iamount > 0)
		  {
		    $db->Execute("UPDATE `equipment` SET `wt`=`wt`-25 WHERE `id`=".$item->fields['id']);
		  }
		else
		  {
		    $db -> Execute("DELETE FROM equipment WHERE id=".$item -> fields['id']);
		  }
	      }
            message("success", YOU_POISON." ".$item -> fields['name'].".");
        }
            else
        {
            message("error", YOU_POISON2." ".$item -> fields['name']." ".BUT_NOT.".");
        }
        $amount = $poison -> fields['amount'] - 1;
        if ($amount < 1) 
        {
            $db -> Execute("DELETE FROM potions WHERE id=".$poison -> fields['id']);
        } 
            else 
        {
            $db -> Execute("UPDATE potions SET amount=".$amount." WHERE id=".$poison -> fields['id']);
        }
        $item -> Close();
    }   
    $poison -> Close();
}

/**
 * Drink few potions
 */
if (isset($_GET['drinkfew']))
  {
    checkvalue($_GET['drinkfew']);
    $objPotion = $db->Execute("SELECT `id`, `name`, `type`, `amount` FROM `potions` WHERE `id`=".$_GET['drinkfew']." AND `owner`=".$player->id." AND `status`='K'");
    if (!$objPotion->fields['id'])
      {
	error(ERROR);
      }
    if ($objPotion->fields['type'] != 'M' && $objPotion->fields['type'] != 'H')
      {
	error("Możesz kilka razy pić tylko mikstury regenerujące.");
      }
    $smarty->assign(array("Adrink" => "Wypij",
			  "Tamount" => "razy",
			  "Pamount" => $objPotion->fields['amount'],
			  "Poname" => $objPotion->fields['name']));
    if (isset($_GET['step']) && $_GET['step'] == 'drink')
      {
	if (!isset($_POST['amount']))
	  {
	    error("Podaj ile napojów chcesz wypić.");
	  }
	checkvalue($_POST['amount']);
	if ($_POST['amount'] > $objPotion->fields['amount'])
	  {
	    error("Nie masz tylu mikstur.");
	  }
	drinkfew($objPotion->fields['id'], $_POST['amount']);
      }
    $objPotion->Close();
  }

global $arrBackpack;
$arrBackpack = array();

backpack('W', WEAPONS,'B','weapons', 'E');
backpack('T', STAFFS,'','staffs', 'E');
backpack('R', 'strzały', '', 'arrows', 'A');
backpack('H', HELMETS,'','helmets', 'E');
backpack('A', ARMORS,'','armors', 'E');
backpack('S', SHIELDS,'','shields', 'E');
backpack('C', CAPES,'','capes', 'E');
backpack('L', LEGS2,'','legs', 'E');
backpack('I', 'pierścienie', '', 'rings', 'E');
backpack('E', 'narzędzia', '', 'tools', 'E');
backpack('P', 'plany', '', 'plans', 'E');
backpack('O', 'Łupy', '', 'loots', 'E');
backpack('Q', 'Przedmioty do zadań', '', 'quests', 'E');

$smarty->assign('Backpack', $arrBackpack);

/**
 * Show potions
 */
$mik = $db -> Execute("SELECT * FROM potions WHERE owner=".$player -> id." AND status='K' ORDER BY `power` ASC, `name` ASC");
if ($mik -> fields['id']) 
{
    $arrname = array();
    $arramount = array();
    $arreffect = array();
    $arrpower = array();
    $arraction = array();
    $arrid = array();
    $i = 0;
    while (!$mik -> EOF) 
    {
        $arrname[$i] = $mik -> fields['name'];
        $arramount[$i] = $mik -> fields['amount'];
        $arreffect[$i] = $mik -> fields['efect'];
        $arraction[$i] = "[ <a href=\"equip.php?wypij=".$mik -> fields['id']."\">".A_DRINK."</a> |";
	$arrpower[$i] = "(".POWER.": ".$mik -> fields['power'].")";
	if ($mik -> fields['type'] == 'P')
	  {
	    $arraction[$i] = "[ <a href=\"equip.php?poison=".$mik -> fields['id']."\">".A_POISON."</a> |";
	  }
	elseif ($mik->fields['type'] == 'H' || $mik->fields['type'] == 'M')
	  {
	    $arraction[$i] .= " <a href=\"equip.php?drinkfew=".$mik->fields['id']."\">wypij kilka</a> |";
	  }
	$arraction[$i] .= " <a href=\"equip.php?sellpotion=".$mik->fields['id']."\">".A_SELL."</a> ".FOR_A." ".$mik->fields['cost']." ".GOLD_COINS." ]";
	$arrid[$i] = $mik->fields['id'];
        $mik -> MoveNext();
        $i = $i + 1;
    }
    if ($i > 0) 
    {
        $strSellAll = "Sprzedaj wszystkie mikstury";
    }
    else
      {
	$strSellAll = '';
      }
    $smarty -> assign ( array("Pname1" => $arrname, 
                              "Pamount1" => $arramount, 
                              "Peffect1" => $arreffect, 
                              "Potionid1" => $arrid, 
                              "Paction1" => $arraction, 
                              "Ppower1" => $arrpower, 
                              "Potions1" => $i,
			      "Sellallp" => $strSellAll,
			      "Potionssell" => "Sprzedaj wybrane mikstury",
                              "Potions2" => POTIONS,
                              "Amount" => AMOUNT));
}
$mik -> Close();

/**
 * Show pets
 */
$arrPets = $db->GetAll("SELECT `id`, `name`, `power`, `defense`, `gender`, `corename`, `status` FROM `core` WHERE `owner`=".$player->id." AND `active`='N'");
if ($arrPets)
  {
    foreach ($arrPets as &$arrPet)
      {
	if ($arrPet['gender'] == 'F')
	  {
	    $arrPet['gender'] = 'Samica';
	  }
	else
	  {
	    $arrPet['gender'] = 'Samiec';
	  }
	if ($arrPet['corename'] != '')
	  {
	    $arrPet['name'] = $arrPet['corename'].' ('.$arrPet['name'].')';
	  }
	if ($arrPet['status'] != 'Alive')
	  {
	    $intGold = ($arrPet['power'] + $arrPet['defense']) * 5;
	    $intMith = floor($intGold / 200);
	    $arrPet['heal'] = 'za '.$intGold.' złota i '.$intMith.' mithrilu';
	  }
	else
	  {
	    $arrPet['heal'] = '';
	  }
      }
    $smarty->assign(array("Pets1" => 1,
			  "Pets" => $arrPets,
			  "Tpets" => 'Posiadane chowańce',
			  "Pheal" => 'ulecz',
			  "Puse" => 'użyj',
			  "Pname" => 'Imię:',
			  "Pgender" => 'Płeć:',
			  "Ppower" => 'Siła:',
			  "Pdefense" => 'Obrona:',
			  "Pchname" => 'zmień imię',
			  "Petrelease" => "Uwolnij wybrane chowańce",
			  "Prelease" => 'uwolnij'));
  }

$smarty -> assign(array("Arramount" => ARR_AMOUNT,
                        "Goldcoins" => GOLD_COINS,
                        "Fora" => FOR_A));

/**
* Initialization of variables
*/
if (!isset($_GET['poison'])) 
{
    $_GET['poison'] = '';
}
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (!isset($_GET['drinkfew']))
  {
    $_GET['drinkfew'] = 0;
  }
if (!isset($_GET['name']))
  {
    $_GET['name'] = 0;
  }

/**
* Drink potion
*/
if (isset($_GET['wypij'])) 
{
    drink($_GET['wypij']);
}

/**
* Assign variables and display page
*/
$smarty -> assign(array("Poison" => $_GET['poison'], 
                        "Step" => $_GET['step'],
			"Drinkfew" => $_GET['drinkfew'],
			"Petname" => $_GET['name']));
$smarty -> display ('equip.tpl');

require_once("includes/foot.php");
?>
