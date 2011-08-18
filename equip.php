<?php
/**
 *   File functions:
 *   Player's equip - wear and drop items, repair, sell and more
 *
 *   @name                 : equip.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 12.08.2011
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
require_once("languages/".$player -> lang."/equip.php");

/**
* Function show items in backpack
*/
function backpack($type,$playerid,$nameitems,$type2,$smartyname) 
{
    global $smarty;
    global $db;
    if (!empty ($type2)) 
    {
        $arm = $db -> Execute("SELECT * FROM equipment WHERE owner=".$playerid." AND type='".$type."' AND status='U'");
        $arm1 = $db -> Execute("SELECT * FROM equipment WHERE owner=".$playerid." AND type='".$type2."' AND status='U'");
    } 
        else 
    {
        $arm = $db -> Execute("SELECT * FROM equipment WHERE owner=".$playerid." AND type='".$type."' AND status='U'");
    }
    $arrshow = array();
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
        $arrshow[0] = "<br /><u>".IN_BACKPACK." ".$nameitems."</u>:<br />\n";
    }
    $j = 1;
    while (!$arm -> EOF) 
    {
        if ($arm -> fields['zr'] < 0) 
        {
            $arm -> fields['zr'] = str_replace("-","",$arm -> fields['zr']);
            $agility = "(+".$arm -> fields['zr']." ".EQUIP_AGI.")";
        } 
            elseif ($arm -> fields['zr'] > 0) 
        {
            $agility = "(-".$arm -> fields['zr']."% ".EQUIP_AGI.")";
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
        if ($arm -> fields['type'] == 'W' && $arm -> fields['poison'] > 0) 
        {
            $arm -> fields['power'] = $arm -> fields['power'] + $arm -> fields['poison'];
        }
        $ckoszt = $arm -> fields['repair'];
        if ($arm -> fields['maxwt'] == $arm -> fields['wt']) 
        {
            $ckoszt = 0;
        }   
        if ($arm -> fields['type'] == 'C') 
        {
            $arrshow[$j] = "<input type=\"checkbox\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." (+".$arm -> fields['power']." % ".EQUIP_MANA.") [ <a href=\"equip.php?equip=".$arm -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm -> fields['cost']." ".GOLD_COINS." ]<br />";
        } 
            elseif ($arm -> fields['type'] == 'T') 
        {
            $arrshow[$j] = "<input type=\"checkbox\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." (".SPELL_POWER.") [ <a href=\"equip.php?equip=".$arm -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm -> fields['cost']." ".GOLD_COINS." ]<br />";
        } 
            else 
        {
            $arrshow[$j] = "<input type=\"checkbox\" name=\"".$arm->fields['id']."\" /><b>(".AMOUNT.": ".$arm -> fields['amount']." )</b> ".$arm -> fields['name']." (+".$arm -> fields['power'].") ".$agility."".$speed." (".$arm -> fields['wt']."/".$arm -> fields['maxwt']." ".DURABILITY.") [ <a href=\"equip.php?equip=".$arm -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm -> fields['cost']." ".GOLD_COINS." | <a href=\"equip.php?napraw=".$arm -> fields['id']."\">".A_REPAIR."</a> ".FOR_A." ".$ckoszt." ".GOLD_COINS." ]<br />";
        }
        $arm -> MoveNext();
        $j = $j + 1;
        $menu = 'Y';
    }
    if ($secarm) 
    {
        while (!$arm1 -> EOF) 
        {
            if ($arm1 -> fields['zr'] < 0) 
            {
                $arm1 -> fields['zr'] = str_replace("-","",$arm1 -> fields['zr']);
                $agility = "(+".$arm1 -> fields['zr']." ".EQUIP_AGI.")";
            } 
                elseif ($arm1 -> fields['zr'] > 0) 
            {
                $agility = "(-".$arm1 -> fields['zr']."% ".EQUIP_AGI.")";
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
            $ckoszt = $arm1 -> fields['repair'];  
            if ($arm1 -> fields['maxwt'] == $arm1 -> fields['wt']) 
            {
                $ckoszt = 0;
            }  
            if ($arm1 -> fields['type'] == 'C') 
            {
                $arrshow[$j] = "<input type=\"checkbox\" name=\"".$arm1->fields['id']."\" /><b>(".AMOUNT.": ".$arm1 -> fields['amount']." )</b> ".$arm1 -> fields['name']." (+".$arm1 -> fields['power']." % ".EQUIP_MANA.") [ <a href=\"equip.php?equip=".$arm1 -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm1 -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm1 -> fields['cost']." ".GOLD_COINS." ]<br />";
            } 
                elseif ($arm -> fields['type'] == 'T') 
            {
                $arrshow[$j] = "<input type=\"checkbox\" name=\"".$arm1->fields['id']."\" /><b>(".AMOUNT.": ".$arm1 -> fields['amount']." )</b> ".$arm1 -> fields['name']." (".SPELL_POWER.") [ <a href=\"equip.php?equip=".$arm1 -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm1 -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm1 -> fields['cost']." ".GOLD_COINS." ]<br />";
            } 
                else 
            {
                $arrshow[$j] = "<input type=\"checkbox\" name=\"".$arm1->fields['id']."\" /><b>(".AMOUNT.": ".$arm1 -> fields['amount']." )</b> ".$arm1 -> fields['name']." (+".$arm1 -> fields['power'].") ".$agility."".$speed." (".$arm1 -> fields['wt']."/".$arm1 -> fields['maxwt']." ".DURABILITY.") [ <a href=\"equip.php?equip=".$arm1 -> fields['id']."\">".A_WEAR."</a> | <A href=\"equip.php?sell=".$arm1 -> fields['id']."\">".A_SELL."</a> ".FOR_A." ".$arm1 -> fields['cost']." ".GOLD_COINS." | <a href=\"equip.php?napraw=".$arm1 -> fields['id']."\">".A_REPAIR."</a> ".FOR_A." ".$ckoszt." ".GOLD_COINS." ]<br />";
            }
            $arm1 -> MoveNext();
            $j = $j + 1;
            $menu = 'Y';
        }
    }
    if (isset($menu) && $menu == 'Y') 
    {
        $arrshow[$j] = "(<a href=\"equip.php?sprzedaj=".$type."\">".A_SELL_ALL." ".$nameitems."</a>)<br />\n";
    }
    $smarty -> assign(array($smartyname => $arrshow,
			    $smartyname."sell" => "Sprzedaj wybrane ".$nameitems,
			    $smartyname."type" => $type,
			    $smartyname."amount" => $j));
    $arm -> Close();
    if ($secarm) 
    {
        $arm1 -> Close();
    }
}

/**
* Assign variables to template
*/
$smarty -> assign(array("Arrowhead" => '', 
                        "Action" => '', 
                        "Potions" => '', 
                        "Hide" => '', 
                        "Repairequip" => '', 
                        "Arrows1" => '',
                        "Equipped" => EQUIPPED,
                        "Tarrows" => ARROWS,
                        "Asell" => A_SELL));

$arrEquip = $player -> equipment();
if ($player -> clas != 'Mag') 
{
    if (!$arrEquip[0][0]) 
    {
        $arrEquip[0] = $arrEquip[1];
    }
    if ($arrEquip[0][0]) 
    {
        if ($arrEquip[0][8] > 0) 
        {
            $arrEquip[0][2] = $arrEquip[0][2] + $arrEquip[0][8];
        }
        $smarty -> assign ("Weapon", "<b>".WEAPON.":</b> ".$arrEquip[0][1]." (+".$arrEquip[0][2].") (+".$arrEquip[0][7]." ".EQUIP_SPEED.") (".$arrEquip[0][6]."/".$arrEquip[0][9]." ".DURABILITY.") [<a href=\"equip.php?schowaj=".$arrEquip[0][0]."\">".HIDE_WEP."</a>]<br />\n");
    } 
        else 
    {
        $smarty -> assign ("Weapon", "<b>".WEAPON.":</b> ".EMPTY_SLOT."<br />\n");
    }
} 
    else 
{
    if (!$arrEquip[0][0]) 
    {
        $arrEquip[0] = $arrEquip[1];
    }
    if ($arrEquip[0][0]) 
    {
        $smarty -> assign ("Weapon", "<b>".WEAPON.":</b> ".$arrEquip[0][1]." (+".$arrEquip[0][2].") (+".$arrEquip[0][7]." ".EQUIP_SPEED.") (".$arrEquip[0][6]."/".$arrEquip[0][9]." ".DURABILITY.") [<a href=\"equip.php?schowaj=".$arrEquip[0][0]."\">".HIDE_WEP."</a>]<br />\n");
    } 
        else 
    {
        if ($arrEquip[7][0]) 
        {
            $smarty -> assign ("Weapon", "<b>".STAFF.":</b> ".$arrEquip[7][1]." (".SPELL_POWER.") [<a href=\"equip.php?schowaj=".$arrEquip[7][0]."\">".HIDE."</a>]<br />\n");
        } 
            else 
        {
            $smarty -> assign ("Weapon", "<b>".WEAPON.":</b> ".EMPTY_SLOT."<br />\n");
        }
    }
}

if ($arrEquip[1][0]) 
{
    if ($arrEquip[6][0]) 
    {
        $smarty -> assign ("Arrows", "<b>".QUIVER.":</b> ".$arrEquip[6][1]." (+".$arrEquip[6][2].") (".$arrEquip[6][6]." ".ARROWS.") [<a href=\"equip.php?schowaj=".$arrEquip[6][0]."\">".HIDE_ARR."</a>] [<a href=\"equip.php?fill=".$arrEquip[6][0]."\">".A_FILL."</a>]<br />\n");
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

if ($arrEquip[2][0]) 
{
    $smarty -> assign ("Helmet", "<b>".HELMET.":</b> ".$arrEquip[2][1]." (+".$arrEquip[2][2].") (".$arrEquip[2][6]."/".$arrEquip[2][9]." ".DURABILITY.") [<a href=\"equip.php?schowaj=".$arrEquip[2][0]."\">".WEAR_OFF."</a>]<br />\n");
} 
    else 
{
    $smarty -> assign ("Helmet", "<b>".HELMET.":</b> ".EMPTY_SLOT."<br />\n");
}

if ($player -> clas != 'Mag') 
{
    if ($arrEquip[3][0]) 
    {
        $agility = '';
        if ($arrEquip[3][5] < 0) 
        {
            $arrEquip[3][5] = str_replace("-","",$arrEquip[3][5]);
            $agility = "(+".$arrEquip[3][5]." ".EQUIP_AGI.")";
        } 
            elseif ($arrEquip[3][5] > 0) 
        {
            $agility = "(-".$arrEquip[3][5]." ".EQUIP_AGI.")";
        }    
        $smarty -> assign ("Armor", "<b>".ARMOR.":</b> ".$arrEquip[3][1]." (+".$arrEquip[3][2].") ".$agility." (".$arrEquip[3][6]."/".$arrEquip[3][9]." ".DURABILITY.") [<a href=\"equip.php?schowaj=".$arrEquip[3][0]."\">".WEAR_OFF."</a>]<br />\n");
    } 
        else 
    {
        $smarty -> assign ("Armor", "<b>".ARMOR.":</b> ".EMPTY_SLOT."<br />\n");
    }
} 
    else 
{
    if ($arrEquip[3][0]) 
    {
        $agility = '';
        if ($arrEquip[3][5] < 0) 
        {
            $arrEquip[3][5] = str_replace("-","",$arrEquip[3][5]);
            $agility = "(+".$arrEquip[3][5]." ".EQUIP_AGI.")";
        } 
            elseif ($arrEquip[3][5] > 0) 
        {
            $agility = "(-".$arrEquip[3][5]." ".EQUIP_AGI.")";
        }        
        $smarty -> assign ("Armor", "<b>".ARMOR.":</b> ".$arrEquip[3][1]." (+".$arrEquip[3][2].") ".$agility." (".$arrEquip[3][6]."/".$arrEquip[3][9]." ".DURABILITY.") [<a href=\"equip.php?schowaj=".$arrEquip[3][0]."\">".WEAR_OFF."</a>]<br />\n");
    } 
        else 
    {
        if ($arrEquip[8][0]) 
        {
            $smarty -> assign ("Armor", "<b>".CAPE.":</b> ".$arrEquip[8][1]." (+".$arrEquip[8][2]." % ".EQUIP_MANA.") [<a href=\"equip.php?schowaj=".$arrEquip[8][0]."\">".WEAR_OFF."</a>]<br />\n");
        } 
            else 
        {
            $smarty -> assign ("Armor", "<b>".ARMOR.":</b> ".EMPTY_SLOT."<br />\n");
        }
    }
}

if ($arrEquip[5][0]) 
{
    if ($arrEquip[5][5] < 0) 
    {
        $arrEquip[5][5] = str_replace("-","",$arrEquip[5][5]);
        $agility1 = "(+".$arrEquip[5][5]." ".EQUIP_AGI.")";
    } 
        elseif ($arrEquip[5][5] > 0) 
    {
        $agility1 = "(-".$arrEquip[5][5]." ".EQUIP_AGI.")";
    } 
        else 
    {
        $agility1 = '';
    } 
    $smarty -> assign ("Shield", "<b>".SHIELD.":</b> ".$arrEquip[5][1]." (+".$arrEquip[5][2].") ".$agility1." (".$arrEquip[5][6]."/".$arrEquip[5][9]." ".DURABILITY.") [<a href=\"equip.php?schowaj=".$arrEquip[5][0]."\">".WEAR_OFF."</a>]<br />\n");
} 
    else 
{
    $smarty -> assign ("Shield", "<b>".SHIELD.":</b> ".EMPTY_SLOT."<br />\n");
}

if ($arrEquip[4][0]) 
{
    if ($arrEquip[4][5] < 0) 
    {
        $arrEquip[4][5] = str_replace("-","",$arrEquip[4][5]);
        $agility2 = "(+".$arrEquip[4][5]." ".EQUIP_AGI.")";
    } 
        elseif ($arrEquip[4][5] > 0) 
    {
        $agility2 = "(-".$arrEquip[4][5]." ".EQUIP_AGI.")";
    }  
        else 
    {
        $agility2 = '';
    }
    $smarty -> assign ("Legs", "<b>".LEGS.":</b> ".$arrEquip[4][1]." (+".$arrEquip[4][2].") ".$agility2." (".$arrEquip[4][6]."/".$arrEquip[4][9]." ".DURABILITY.") [<a href=\"equip.php?schowaj=".$arrEquip[4][0]."\">".WEAR_OFF."</a>]<br />\n");
} 
    else 
{
    $smarty -> assign ("Legs", "<b>".LEGS.":</b> ".EMPTY_SLOT."<br />\n");
}

/**
 * Weared rings
 */
if ($arrEquip[9][0])
{
  $smarty -> assign("Ring1", "<b>".RING.":</b> ".$arrEquip[9][1]." (+".$arrEquip[9][2].") [<a href=\"equip.php?schowaj=".$arrEquip[9][0]."\">".WEAR_OFF."</a>]<br />\n");
}
    else
{
    $smarty -> assign("Ring1", "<b>".RING.":</b> ".EMPTY_SLOT."<br />\n");
}
if ($arrEquip[10][0])
{
  $smarty -> assign("Ring2", "<b>".RING.":</b> ".$arrEquip[10][1]." (+".$arrEquip[10][2].") [<a href=\"equip.php?schowaj=".$arrEquip[10][0]."\">".WEAR_OFF."</a>]<br />\n");
}
    else
{
    $smarty -> assign("Ring2", "<b>".RING.":</b> ".EMPTY_SLOT."<br />\n");
}

if (isset($_GET['schowaj'])) 
{
    checkvalue($_GET['schowaj']);
    $bron = $db -> Execute("SELECT * FROM equipment WHERE id=".$_GET['schowaj']);
    if (!$bron -> fields['id']) 
    {
        error (NO_ITEM);
    }
    if ($player -> id != $bron -> fields['owner']) 
    {
        error (NOT_YOUR);
    }
    if ($bron -> fields['status'] == 'U') 
    {
        error (ERROR);
    }
    if (isset($arrEquip[6][0])) 
    {
        $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$arrEquip[6][1]."' AND status='U' AND owner=".$player -> id." AND power=".$arrEquip[6][2]);
    }
    if ($bron -> fields['type'] == 'B') 
    {
        if (!isset($test -> fields['id'])) 
        {
            $db -> Execute("UPDATE `equipment` SET `status`='U' WHERE `type`='R' AND `owner`=".$player -> id." AND `status`='E'");
        } 
            else 
        {
            $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$arrEquip[6][6]." WHERE `id`=".$test -> fields['id']);
            $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$arrEquip[6][0]);
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
            $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$arrEquip[6][6]." WHERE `id`=".$test -> fields['id']);
            $db -> Execute("DELETE FROM `equipment` WHERE `id`=".$arrEquip[6][0]);
        }
    } 
        else 
    {
        $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$bron -> fields['name']."' AND wt=".$bron -> fields['wt']." AND type='".$bron -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$bron -> fields['power']." AND zr=".$bron -> fields['zr']." AND szyb=".$bron -> fields['szyb']." AND maxwt=".$bron -> fields['maxwt']." AND poison=".$bron -> fields['poison']." AND ptype='".$bron -> fields['ptype']."' AND cost=".$bron -> fields['cost']);
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
    $bron -> Close();
    $smarty -> assign ("Hide", "<br />(<a href=\"equip.php\">".REFRESH."</a>)<br />\n");
}

if ($arrEquip[3][0] || $arrEquip[0][0] || $arrEquip[4][0] || $arrEquip[2][0] || $arrEquip[5][0]) 
{
    $smarty -> assign ("Repairequip", "[<a href=\"equip.php?napraw_uzywane\">".A_REPAIR2."</a>]<br />\n");
}

backpack('W',$player -> id,WEAPONS,'B','Bweapons');
backpack('T',$player -> id,STAFFS,'','Bstaffs');

$arr = $db -> Execute("SELECT * FROM equipment WHERE owner=".$player -> id." AND type='R' AND status='U'");
$posiada = false;
$arrname = array();
$arrpower = array();
$arrdur = array();
$arrid = array();
$arrcost = array();
$i = 0;
while (!$arr -> EOF) 
{
    if(!$posiada) 
    {
        $smarty -> assign (array("Arrows1" => "<br /><u>".BACK_QUIVER."</u>:<br />\n",
                                 "Awear" => A_WEAR,
                                 "Fora" => FOR_A,
                                 "Goldcoins" => GOLD_COINS,
                                 "Sellall" => SELL_ALL_ARR));
    }
    $posiada = true;
    $costone = $arr -> fields['cost'] / 100;
    $arrname[$i] = $arr -> fields['name'];
    $arrpower[$i] = $arr -> fields['power'];
    $arrdur[$i] = $arr -> fields['wt'] * $arr -> fields['amount'];
    $arrid[$i] = $arr -> fields['id'];
    $arrcost[$i] = ceil($costone * $arr -> fields['wt']);
    $arr -> MoveNext();
    $i++;
}
$arr -> Close();
$smarty -> assign(array("Barrows" => $arrname, 
                        "Barrpower" => $arrpower, 
                        "Barramount" => $arrdur, 
                        "Barrid" => $arrid, 
                        "Barrcost" => $arrcost,
                        "Potions1" => '',
                        "Rings1" => '',
			"Barrowssell" => "Sprzedaj wybrane strzały"));

backpack('H',$player -> id,HELMETS,'','Bhelmets');
backpack('A',$player -> id,ARMORS,'','Barmors');
backpack('S',$player -> id,SHIELDS,'','Bshields');
backpack('C',$player -> id,CAPES,'','Bcapes');
backpack('L',$player -> id,LEGS2,'','Blegs');

/**
 * Unused rings
 */
$arrRings = $db -> Execute("SELECT `id`, `name`, `power`, `cost`, `amount` FROM `equipment` WHERE `owner`=".$player -> id." AND `type`='I' AND `status`='U'");
$posiada = false;
$arrName = array();
$arrPower = array();
$arrId = array();
$arrCost = array();
$arrAmount = array();
$i = 0;
while (!$arrRings -> EOF) 
{
    if(!$posiada) 
    {
        $smarty -> assign (array("Rings1" => "<br /><u>".BACK_RINGS."</u>:<br />\n",
                                 "Awear" => A_WEAR,
                                 "Fora" => FOR_A,
                                 "Goldcoins" => GOLD_COINS,
                                 "Sellallrings" => SELL_ALL_RINGS,
                                 "Amount" => AMOUNT));
    }
    $posiada = true;
    $arrName[$i] = $arrRings -> fields['name'];
    $arrPower[$i] = $arrRings -> fields['power'];
    $arrId[$i] = $arrRings -> fields['id'];
    $arrCost[$i] = $arrRings -> fields['cost'];
    $arrAmount[$i] = $arrRings -> fields['amount'];
    $arrRings -> MoveNext();
    $i++;
}
$arrRings -> Close();
$smarty -> assign(array("Brings" => $arrName, 
                        "Bringpower" => $arrPower, 
                        "Bringid" => $arrId, 
                        "Bringcost" => $arrCost,
                        "Bringamount" => $arrAmount,
			"Ringssell" => "Sprzedaj wybrane pierścienie"));

$mik = $db -> Execute("SELECT * FROM potions WHERE owner=".$player -> id." AND status='K'");
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
        if ($mik -> fields['type'] != 'A') 
        {
            $arrpower[$i] = "(".POWER.": ".$mik -> fields['power'].")";
            if ($mik -> fields['type'] == 'P')
            {
                $arraction[$i] = "[ <a href=\"equip.php?poison=".$mik -> fields['id']."\">".A_POISON."</a> |";
            }
        } 
            else
        {
            $arrpower[$i] = '';
        }
	$arraction[$i] .= " <a href=\"equip.php?sellpotion=".$mik->fields['id']."\">".A_SELL."</a> ".FOR_A." ".$mik->fields['cost']." ".GOLD_COINS." ]";
	$arrid[$i] = $mik->fields['id'];
        $mik -> MoveNext();
        $i = $i + 1;
    }
    if ($i > 0) 
    {
        $strSellAll = "(<a href=\"equip.php?sellpotions\">sprzedaj wszystkie mikstury</a>)<br />\n";
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

$smarty -> assign(array("Arramount" => ARR_AMOUNT,
                        "Goldcoins" => GOLD_COINS,
                        "Fora" => FOR_A));

/**
* Sell one item
*/
if (isset($_GET['sell'])) 
{
    checkvalue($_GET['sell']);
    $sell = $db -> Execute("SELECT * FROM equipment WHERE id=".$_GET['sell']);
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
    $smarty -> assign ("Action", YOU_SELL." ".$sell -> fields['name']." ".FOR_A." ".$sell -> fields['cost']." ".GOLD_COINS.". (<A href=\"equip.php\">".REFRESH."</a>)");
    $sell -> Close();
}

/**
 * Sell one potion
 */
if (isset($_GET['sellpotion']))
  {
    checkvalue($_GET['sellpotion']);
    $sell = $db -> Execute("SELECT * FROM `potions` WHERE `id`=".$_GET['sellpotion']);
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
    $smarty -> assign ("Action", "Sprzedał".$strLast." ".$sell->fields['name']." ".FOR_A." ".$sell->fields['cost']." ".GOLD_COINS.". (<A href=\"equip.php\">".REFRESH."</a>)");
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
    $smarty -> assign ("Action", "<br />Sprzedał".$strLast." swoje mikstury ".FOR_A." ".$zysk." ".GOLD_COINS.".<br />\n(<a href=\"equip.php\">".REFRESH."</a>)<br />\n");
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
	$objItems = $db->Execute("SELECT `id`, `type`, `cost`, `wt`, `maxwt`, `amount` FROM `equipment` WHERE `owner`=".$player->id." AND `status`='U'");
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
	$smarty -> assign ("Action", "<br />Sprzedał".$strLast." wybrane przedmioty ".FOR_A." ".$intMoney." ".GOLD_COINS.".<br />\n(<a href=\"equip.php\">".REFRESH."</a>)<br />\n");
      }
  }

/**
 * Sell all items
 */
if (isset($_GET['sprzedaj'])) 
  {
    $arrSell = array('A', 'W', 'H', 'L', 'R', 'C', 'T', 'S', 'I');
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
    $arrSell = array('A', 'W', 'H', 'L', 'R', 'C', 'T', 'S', 'I');
    $arrType = array(ARMORS, WEAPONS, HELMETS, LEGS2, ARROWS2, CAPES, STAFFS, SHIELDS, RINGS);
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
        if ($zysk1 -> fields['maxwt'] == $zysk1 -> fields['wt'] && $zysk1 -> fields['type'] != 'R') 
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
    $smarty -> assign ("Action", "<br />".YOU_SELL." ".$typ." ".FOR_A." ".$zysk." ".GOLD_COINS.".<br />\n(<a href=\"equip.php\">".REFRESH."</a>)<br />\n");
}

/**
 * Repair used items
 */
if (isset($_GET['napraw_uzywane'])) 
{
    $rzecz_wiersz = $db -> Execute("SELECT * FROM equipment WHERE owner = ".$player -> id." AND status = 'E' AND type != 'R' AND type != 'T' AND type != 'C' AND `type`!='I'");
    $text = '';
    while(!$rzecz_wiersz -> EOF) 
    {
        if ($rzecz_wiersz -> fields['maxwt'] != $rzecz_wiersz -> fields['wt']) 
        {
            if ($rzecz_wiersz -> fields['repair'] > $player -> credits) 
            {
                error (NO_MONEY);
            }
            $player -> credits = $player -> credits - $rzecz_wiersz -> fields['repair'];
            $db -> Execute("UPDATE equipment SET wt=".$rzecz_wiersz -> fields['maxwt']." WHERE id=".$rzecz_wiersz -> fields['id']);
            $db -> Execute("UPDATE players SET credits=credits-".$rzecz_wiersz -> fields['repair']." WHERE id=".$player -> id);
            $text = $text."<br />".YOU_REPAIR." ".$rzecz_wiersz -> fields['name']." ".AND_COST." ".$rzecz_wiersz -> fields['repair']." ".GOLD_COINS.".<br />";
        }
        $rzecz_wiersz -> MoveNext();
    }
    $rzecz_wiersz -> Close();
    $smarty -> assign ("Action", $text);
}

/**
 * Repair items in backpack
 */
if (isset($_GET['napraw'])) 
{
    checkvalue($_GET['napraw']);
    $rzecz = $db -> Execute("SELECT * FROM `equipment` WHERE `id`=".$_GET['napraw']);
    if ($rzecz -> fields['wt'] == $rzecz -> fields['maxwt']) 
    {
        error (NO_REPAIR);
    }
    if (!$rzecz -> fields['id']) 
    {
        error (NO_ITEM);
    }
    if ($player -> credits < $rzecz -> fields['repair']) 
    {
        error (NO_MONEY);
    }
    if ($player -> id != $rzecz -> fields['owner']) 
    {
        error (NOT_YOUR);
    }
    if ($rzecz -> fields['type'] == 'R' || $rzecz -> fields['type'] == 'I') 
    {
        error(E_REPAIR);
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
    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$rzecz -> fields['repair']." WHERE `id`=".$player -> id);
    $smarty -> assign ("Action", "<br />".YOU_REPAIR." <b>".$rzecz -> fields['name']."</b> ".FOR_A." <b>".$rzecz -> fields['repair']."</b> ".GOLD_COINS.".<br />\n(<a href=\"equip.php\">".REFRESH."</a>)<br />\n");
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
    $wep = $db -> Execute("SELECT id, name, amount FROM equipment WHERE owner=".$player -> id." AND type='W' AND status='U' AND poison=0");
    $arrname = array();
    $arrid = array();
    $arramount = array();
    $i = 0;
    while (!$wep -> EOF) 
    {
        $arrname[$i] = $wep -> fields['name'];
        $arrid[$i] = $wep -> fields['id'];
        $arramount[$i] = $wep -> fields['amount'];
        $i = $i + 1;
        $wep -> MoveNext();
    }
    $wep -> Close();
    $smarty -> assign ( array("Poisonitem" => $arrname, 
        "Poisonid" => $arrid, 
        "Poisonamount" => $arramount,
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
        if ($item -> fields['type'] != 'W') 
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
        if (ereg("(K)", $poison -> fields['name']))
        {
            $intRoll = rand(0,100);
            if ($intRoll == 1)
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
            $name = POISONED." ".$item -> fields['name'];
            $intPower = $poison -> fields['power'];
            if (ereg("Dynallca",$poison -> fields['name']))
            {
                $strPtype = 'D';
            }
                elseif (ereg("Nutari",$poison -> fields['name']))
            {
                $strPtype = 'N';
            }
                elseif (ereg("Illani",$poison -> fields['name']))
            {
                $strPtype = 'I';
            }
            $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND wt=".$item -> fields['wt']." AND type='W' AND status='U' AND owner=".$player -> id." AND power=".$item -> fields['power']." AND zr=".$item -> fields['zr']." AND szyb=".$item -> fields['szyb']." AND maxwt=".$item -> fields['maxwt']." AND poison=".$intPower." AND ptype='".$strPtype."'");
            if (!$test -> fields['id']) 
            {
                $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `ptype`, `repair`, `twohand`) VALUES(".$player -> id.",'".$name."',".$item -> fields['power'].",'W',".$item -> fields['cost'].",".$item -> fields['zr'].",".$item -> fields['wt'].",".$item -> fields['minlev'].",".$item -> fields['maxwt'].",1,'N',".$intPower.",".$item -> fields['szyb'].", '".$strPtype."', ".$item -> fields['repair'].", '".$item -> fields['repair']."')") or error($db -> ErrorMsg());
            } 
                else 
            {
                $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
            }
            $test -> Close();
            $iamount = $item -> fields['amount'] - 1;
            if ($iamount > 0) 
            {
                $db -> Execute("UPDATE equipment SET amount=amount-1 WHERE id=".$item -> fields['id']);
            }  
                else 
            {
                $db -> Execute("DELETE FROM equipment WHERE id=".$item -> fields['id']);
            }
            $smarty -> assign ("Item", YOU_POISON." ".$item -> fields['name'].". <a href=\"equip.php\">".REFRESH."</a>");
        }
            else
        {
            $smarty -> assign ("Item", YOU_POISON2." ".$item -> fields['name']." ".BUT_NOT.". <a href=\"equip.php\">".REFRESH."</a>");
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
* Fill equiped arrows
*/
if (isset($_GET['fill']))
{
    checkvalue($_GET['fill']);
    $objArrows = $db -> Execute("SELECT wt, name, power, id FROM equipment WHERE id=".$_GET['fill']);
    if (!$objArrows -> fields['id'])
    {
        error(NO_ITEMS);
    }
    if ($objArrows -> fields['wt'] == 20)
    {
        error(NOT_NEED);
    }
    $objArrows2 = $db -> Execute("SELECT wt, id FROM equipment WHERE name='".$objArrows -> fields['name']."' AND power=".$objArrows -> fields['power']." AND status='U' AND owner=".$player -> id);
    if (!$objArrows2 -> fields['id'])
    {
        error(NO_ARROWS);
    }
    $intAmount = 20 - $objArrows -> fields['wt'];
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
    $db -> Execute("UPDATE equipment SET wt=wt+".$intFill." WHERE id=".$objArrows -> fields['id']);
    $objArrows -> Close();
    $smarty -> assign("Hide", "Uzupełniłeś kołczan");
}

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

/**
* Assign variables and display page
*/
$smarty -> assign(array("Poison" => $_GET['poison'], 
                        "Step" => $_GET['step']));
$smarty -> display ('equip.tpl');

/**
* Wear equipment
*/
if (isset($_GET['equip'])) 
{
    equip($_GET['equip']);
}

/**
* Drink potion
*/
if (isset($_GET['wypij'])) 
{
    drink($_GET['wypij']);
}

require_once("includes/foot.php");
?>
