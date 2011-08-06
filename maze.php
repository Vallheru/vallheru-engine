<?php
/**
 *   File functions:
 *   Labyrynth in forrest city
 *
 *   @name                 : maze.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 31.10.2006
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
// $Id: maze.php 804 2006-10-31 14:12:31Z thindil $

$title = "Labirynt";
require_once("includes/head.php");
require_once("includes/funkcje.php");
require_once("includes/turnfight.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/maze.php");

if ($player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Function to fight with monsters
*/
function battle($type,$adress) 
{
    global $player;
    global $smarty;
    global $enemy;
    global $arrehp;
    global $db;
    if ($player -> hp <= 0) 
    {
        error (NO_LIFE);
    }
    $enemy1 = $db -> Execute("SELECT * FROM monsters WHERE id=".$player -> fight);
    $span = ($enemy1 -> fields['level'] / $player -> level);
    if ($span > 2) 
    {
        $span = 2;
    }
    $expgain = ceil(rand($enemy1 -> fields['exp1'],$enemy1 -> fields['exp2'])  * $span);
    $goldgain = ceil(rand($enemy1 -> fields['credits1'],$enemy1 -> fields['credits2']) * $span);
    $enemy = array("strength" => $enemy1 -> fields['strength'], 
                   "agility" => $enemy1 -> fields['agility'], 
                   "speed" => $enemy1 -> fields['speed'], 
                   "endurance" => $enemy1 -> fields['endurance'], 
                   "hp" => $enemy1 -> fields['hp'], 
                   "name" => $enemy1 -> fields['name'], 
                   "id" => $enemy1 -> fields['id'], 
                   "exp1" => $enemy1 -> fields['exp1'], 
                   "exp2" => $enemy1 -> fields['exp2'],  
                   "level" => $enemy1 -> fields['level']);
    if ($type == 'T') 
    {
        if (!isset ($_POST['action'])) 
        {
            turnfight ($expgain,$goldgain,'',$adress);
        } 
            else 
        {
            turnfight ($expgain,$goldgain,$_POST['action'],$adress);
        }
    } 
        else 
    {
        fightmonster ($enemy,$expgain,$goldgain,1);
    }
    $fight = $db -> Execute("SELECT fight FROM players WHERE id=".$player -> id);
    if ($fight -> fields['fight'] == 0) 
    {
        $player -> energy = $player -> energy - 1;
        if ($player -> energy < 0) 
        {
            $player -> energy = 0;
        }
        $db -> Execute("UPDATE players SET energy=".$player -> energy." WHERE id=".$player -> id);
        $smarty -> assign ("Link", "<br /><br /><a href=\"maze.php?action=explore\">".A_EXPLORE."</a><br />");
    }
        else
    {
        $smarty -> assign("Link", '');
    }
    $fight -> Close();
    $enemy1 -> Close();
}

/**
* If player not escape - start fight
*/
if (isset($_GET['step']) && $_GET['step'] == 'battle') 
{
    if (!isset ($_GET['type'])) 
    {
        $type = 'T';
    } 
        else 
    {
        $type = $_GET['type'];
    }
    battle($type,'maze.php?step=battle');
}

/**
* If player escape
*/
if (isset($_GET['step']) && $_GET['step'] == 'run') 
{
    $enemy = $db -> Execute("SELECT speed, name, exp1, exp2, id FROM monsters WHERE id=".$player -> fight);
    /**
     * Add bonus from rings
     */
    $arrEquip = $player -> equipment();
    if ($arrEquip[9][2])
    {
        $arrRingtype = explode(" ", $arrEquip[9][1]);
        $intAmount = count($arrRingtype) - 1;
        if ($arrRingtype[$intAmount] == R_SPE5)
        {
            $player -> speed = $player -> speed + $arrEquip[9][2];
        }
    } 
    if ($arrEquip[10][2])
    {
        $arrRingtype = explode(" ", $arrEquip[10][1]);
        $intAmount = count($arrRingtype) - 1;
        if ($arrRingtype[$intAmount] == R_SPE5)
        {
            $player -> speed = $player -> speed + $arrEquip[10][2];
        }
    } 
    $chance = (rand(1, $player -> level * 100) + $player -> speed - $enemy -> fields['speed']);
    $smarty -> assign ("Chance", $chance);
    if ($chance > 0) 
    {
        $expgain = rand($enemy -> fields['exp1'],$enemy -> fields['exp2']);
        $expgain = ceil($expgain / 100);
        $smarty -> assign(array("Ename" => $enemy -> fields['name'], 
                                "Expgain" => $expgain,
                                "Escapesucc" => ESCAPE_SUCC,
                                "Escapesucc2" => ESCAPE_SUCC2,
                                "Escapesucc3" => ESCAPE_SUCC3));
        checkexp($player -> exp,$expgain,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'',0);
        $db -> Execute("UPDATE players SET fight=0 WHERE id=".$player -> id);
    } 
        else 
    {
        $strMessage = ESCAPE_FAIL." ".$enemy -> fields['name']." ".ESCAPE_FAIL2.".<br />";
        $smarty -> assign ("Message", $strMessage);
        $smarty -> display ('error1.tpl');
        battle('T','maze.php?step=battle');
    }
    $hp = $db -> Execute("SELECT hp FROM players WHERE id=".$player -> id);
    $smarty -> assign ("Health", $hp -> fields['hp']);
    if ($hp -> fields['hp'] > 0) 
    {
        $smarty -> assign("Link", "<a href=\"maze.php?action=explore\">".A_EXPLORE."</a>");
    }
    $hp -> Close();
}

if (!isset($_GET['action']))
{
    $smarty -> assign(array("Mazeinfo" => MAZE_INFO,
        "Ayes" => YES));
    $_GET['action'] = '';
}

if (isset($_GET['action']) && $_GET['action'] == 'explore')
{
    if ($player -> energy < 0.3)
    {
        error(NO_ENERGY);
    }
    if ($player -> hp <= 0)
    {
        error(YOU_DEAD);
    }
    if (!empty($player -> fight)) 
    {
        $enemy = $db -> Execute("SELECT name FROM monsters WHERE id=".$player -> fight);
        error (FIGHT1.$enemy -> fields['name'].FIGHT2."<br />
                   <a href=maze.php?step=battle&type=T>".Y_TURN_F."</a><br />
                   <a href=maze.php?step=battle&type=N>".Y_NORM_F."</a><br />
               <a href=maze.php?step=run>".NO."</a><br />");
        $enemy -> Close();
    }
    $db -> Execute("UPDATE players SET energy=energy-.3 WHERE id=".$player -> id);
    $intRoll = rand(1, 100);
    $smarty -> assign(array("Link" => "<a href=\"maze.php?action=explore\">".A_EXPLORE."</a>",
        "Roll" => $intRoll));
    if ($intRoll < 49)
    {
        $smarty -> assign("Message", EMPTY_1);
    }
    if ($intRoll > 48 && $intRoll < 64)
    {
        $arrmonsters = array(58, 59, 63, 69, 73, 76, 82, 84, 93, 95, 98, 101, 103);
        $rzut2 = rand(0,12);
        $enemy = $db -> Execute("SELECT name, id FROM monsters WHERE id=".$arrmonsters[$rzut2]);
        $db -> Execute("UPDATE players SET fight=".$enemy -> fields['id']." WHERE id=".$player -> id);
        $smarty -> assign (array("Name" => $enemy -> fields['name'],
                                 "Youmeet" => YOU_MEET,
                                 "Fight2" => FIGHT2,
                                 "Yturnf" => Y_TURN_F,
                                 "Ynormf" => Y_NORM_F,
                                 "Ano" => NO));
    }
    if ($intRoll > 63 && $intRoll < 70)
    {
        $objHerb = $db -> Execute("SELECT gracz FROM herbs WHERE gracz=".$player -> id);
        $arrHerbs = array('illani', 'illanias', 'nutari', 'dynallca');
        $intRoll2 = rand(0, 3);
        $intAmount = rand(1, 10);
        if (!$objHerb -> fields['gracz']) 
        {
            $db -> Execute("INSERT INTO herbs (gracz, ".$arrHerbs[$intRoll2].") VALUES(".$player -> id.",".$intAmount.")");
        } 
            else 
        {
            $db -> Execute("UPDATE herbs SET ".$arrHerbs[$intRoll2]."=".$arrHerbs[$intRoll2]."+".$intAmount." WHERE gracz=".$player -> id);
        }
        $objHerb -> Close();
        $smarty -> assign("Message", F_HERBS.$intAmount.I_AMOUNT.$arrHerbs[$intRoll2]);
    }
    if ($intRoll > 69 && $intRoll < 76)
    {
        $objLumber = $db -> Execute("SELECT owner FROM minerals WHERE owner=".$player -> id);
        $intAmount = rand(1, 10);
        $arrLumber = array('pine', 'hazel', 'yew', 'elm');
        $arrType = array(T_PINE, T_HAZEL, T_YEW, T_ELM);
        $intRoll2 = rand(0, 3);
        if (!$objLumber -> fields['owner']) 
        {
            $db -> Execute("INSERT INTO minerals (owner, ".$arrLumber[$intRoll2].") VALUES(".$player -> id.",".$intAmount.")");
        } 
            else 
        {
            $db -> Execute("UPDATE minerals SET ".$arrLumber[$intRoll2]."=".$arrLumber[$intRoll2]."+".$intAmount." WHERE owner=".$player -> id);
        }
        $objLumber -> Close();
        $smarty -> assign("Message", F_LUMBER.$intAmount.I_AMOUNT.$arrType[$intRoll2]);
    }
    if ($intRoll > 75 && $intRoll < 81)
    {
        $intRoll2 = rand(1,5);
        $db -> Execute("UPDATE players SET platinum=platinum+".$intRoll2." WHERE id=".$player -> id);
        $smarty -> assign("Message", F_MITHRIL.$intRoll2.M_AMOUNT);
    }
    if ($intRoll > 80 && $intRoll < 86)
    {
        $smarty -> assign("Message", F_ENERGY);
        $db -> Execute("UPDATE players SET energy=energy+1 WHERE id=".$player -> id);
    }
    if ($intRoll > 85 && $intRoll < 89)
    {
        $smarty -> assign("Message", F_ENERGY);
        $db -> Execute("UPDATE players SET energy=energy+1 WHERE id=".$player -> id);
    }
    if ($intRoll > 88 && $intRoll < 91)
    {
        $intRoll2 = rand(1, 10);
        if ($intRoll2 < 6)
        {
            $intRoll3 = rand(1,3);
            $strSymbol = '<';
            if ($intRoll3 == 1)
            {
                $strType = 'B';
            }
            if ($intRoll3 == 2)
            {
                $strType = 'O';
            }
            if ($intRoll3 == 3)
            {
                $strType = 'U';
            }
        }
        if ($intRoll2 > 5 && $intRoll2 < 9)
        {
            $intRoll3 = rand(1,3);
            $strSymbol = '=';
            if ($intRoll3 == 1)
            {
                $strType = 'B';
            }
            if ($intRoll3 == 2)
            {
                $strType = 'O';
            }
            if ($intRoll3 == 3)
            {
                $strType = 'U';
            }
        }
        if ($intRoll2 < 9)
        {
            $objQuery = $db -> Execute("SELECT id FROM czary WHERE gracz=0 AND poziom".$strSymbol."".$player -> level." AND typ='".$strType."' AND lang='".$player -> lang."'");
            $intAmount = $objQuery -> RecordCount();
            $objQuery -> Close();
            if ($intAmount > 0)
            {
                $intRoll4 = rand(0, ($intAmount-1));
                $objSpell = $db -> SelectLimit("SELECT * FROM czary WHERE gracz=0 AND poziom".$strSymbol."".$player -> level." AND typ='".$strType."' AND lang='".$player -> lang."'",1,$intRoll4);
                $objTest = $db -> Execute("SELECT id FROM czary WHERE nazwa='".$objSpell -> fields['nazwa']."' AND gracz=".$player -> id);
                if (!$objTest -> fields['id']) 
                {
                    $db -> Execute("INSERT INTO czary (gracz, nazwa, cena, poziom, typ, obr, status) VALUES(".$player -> id.", '".$objSpell -> fields['nazwa']."', ".$objSpell -> fields['cena'].", ".$objSpell -> fields['poziom'].", '".$objSpell -> fields['typ']."', ".$objSpell -> fields['obr'].", 'U')");
                    $smarty -> assign("Message", F_SPELL.$objSpell -> fields['nazwa'].F_SPELL2);
                }
                    else
                {
                    $smarty -> assign("Message", EMPTY_2);
                }
                $objTest -> Close();
                $objSpell -> Close();
            }           
                else
            {
                $smarty -> assign("Message", EMPTY_3);
            }
        }
        if ($intRoll2 > 8)
        {
            $smarty -> assign("Message", EMPTY_4);
        }
    }
    if ($intRoll == 91)
    {
        $intRoll2 = rand(1, 10);
        if ($intRoll2 < 6)
        {
            $strSymbol = '<';
        }
        if ($intRoll2 > 5 || $intRoll2 < 9)
        {
            $strSymbol = '=';
        }
        if ($intRoll2 < 9)
        {
            $objQuery = $db -> Execute("SELECT id FROM mill WHERE owner=0 AND level".$strSymbol."".$player -> level." AND type='B' AND lang='".$player -> lang."'");
            $intAmount = $objQuery -> RecordCount();
            $objQuery -> Close();
            if ($intAmount > 0)
            {
                $intRoll4 = rand(0, ($intAmount-1));
                $objPlan = $db -> SelectLimit("SELECT * FROM mill WHERE owner=0 AND level".$strSymbol."".$player -> level." AND type='B' AND lang='".$player -> lang."'",1,$intRoll4);
                $objTest = $db -> Execute("SELECT id FROM mill WHERE name='".$objPlan -> fields['name']."' AND owner=".$player -> id);
                if (!$objTest -> fields['id']) 
                {
                    $db -> Execute("INSERT INTO mill (owner, name, type, cost, amount, level, twohand) VALUES(".$player -> id.",'".$objPlan -> fields['name']."', '".$objPlan -> fields['type']."' ,".$objPlan -> fields['cost'].", ".$objPlan -> fields['amount'].", ".$objPlan -> fields['level'].", '".$objPlan -> fields['twohand']."')");
                    $smarty -> assign("Message", F_PLANBOW.$objPlan -> fields['name']);
                }
                    else
                {
                    $smarty -> assign("Message", EMPTY_5);
                }
                $objTest -> Close();
                $objPlan -> Close();
            }
                else
            {
                $smarty -> assign("Message", EMPTY_6);
            }
        }
        if ($intRoll2 > 8)
        {
            $smarty -> assign("Message", EMPTY_1);
        }
    }
    if ($intRoll == 92)
    {
        $intRoll2 = rand(1, 10);
        if ($intRoll2 < 6)
        {
            $strSymbol = '<';
        }
        if ($intRoll2 == 6 || $intRoll2 == 7)
        {
            $strSymbol = '=';
        }
        if ($intRoll2 == 8)
        {
            $strSymbol = '>';
        }
        if ($intRoll2 < 9)
        {
            $objQuery = $db -> Execute("SELECT id FROM alchemy_mill WHERE owner=0 AND level".$strSymbol."".$player -> level." AND lang='".$player -> lang."'");
            $intAmount = $objQuery -> RecordCount();
            $objQuery -> Close();
            if ($intAmount > 0)
            {
                $intRoll4 = rand(0, ($intAmount-1));
                $objPlan = $db -> SelectLimit("SELECT * FROM alchemy_mill WHERE owner=0 AND level".$strSymbol."".$player -> level." AND lang='".$player -> lang."'",1,$intRoll4);
                $objTest = $db -> Execute("SELECT id FROM alchemy_mill WHERE name='".$objPlan -> fields['name']."' AND owner=".$player -> id);
                if (!$objTest -> fields['id']) 
                {
                    $db -> Execute("INSERT INTO alchemy_mill (owner, name, cost, status, level, illani, illanias, nutari, dynallca) VALUES(".$player -> id.",'".$objPlan -> fields['name']."',".$objPlan -> fields['cost'].",'N',".$objPlan -> fields['level'].",".$objPlan -> fields['illani'].",".$objPlan -> fields['illanias'].",".$objPlan -> fields['nutari'].",".$objPlan -> fields['dynallca'].")");
                    $smarty -> assign("Message", F_PLANPOTION.$objPlan -> fields['name']);
                }
                    else
                {
                    $smarty -> assign("Message", EMPTY_2);
                }
                $objTest -> Close();
                $objPlan -> Close();
            }
                else
            {
                $smarty -> assign("Message", EMPTY_3);
            }
        }
        if ($intRoll2 > 8)
        {
            $smarty -> assign("Message", EMPTY_4);
        }
    }
    if ($intRoll == 93)
    {
        $intRoll2 = rand(1, 10);
        if ($intRoll2 < 6)
        {
            $strSymbol = '<';
        }
        if ($intRoll2 == 6 || $intRoll2 == 7)
        {
            $strSymbol = '=';
        }
        if ($intRoll2 == 8)
        {
            $strSymbol = '>';
        }
        if ($intRoll2 < 9)
        {
            $objQuery = $db -> Execute("SELECT id FROM mage_items WHERE minlev".$strSymbol."".$player -> level." AND lang='".$player -> lang."' AND type='T'");
            $intAmount = $objQuery -> RecordCount();
            $objQuery -> Close();
            if ($intAmount > 0)
            {
                $intRoll4 = rand(0, ($intAmount-1));
                $objStaff = $db -> SelectLimit("SELECT * FROM mage_items WHERE minlev".$strSymbol."".$player -> level." AND lang='".$player -> lang."' AND type='T'",1,$intRoll4);
                $objTest = $db -> Execute("SELECT id FROM equipment WHERE name='".$objStaff -> fields['name']."' AND owner=".$player -> id);
                if (!$objTest -> fields['id']) 
                {
                    $intNewcost = $objStaff -> fields['minlev'] * 100;
                    $db -> Execute("INSERT INTO equipment (owner, name, cost, minlev, type, power, status) VALUES(".$player -> id.",'".$objStaff -> fields['name']."',".$intNewcost.",".$objStaff -> fields['minlev'].",'".$objStaff -> fields['type']."',".$objStaff -> fields['power'].",'U')");
                    $smarty -> assign("Message", F_STAFF.$objStaff -> fields['name'].F_STAFF2);
                }
                    else
                {
                    $smarty -> assign("Message", EMPTY_5);
                }
                $objTest -> Close();
                $objStaff -> Close();
            }
                else
            {
                $smarty -> assign("Message", EMPTY_6);
            }
        }
        if ($intRoll2 > 8)
        {
            $smarty -> assign("Message", EMPTY_1);
        }
    }
    if ($intRoll == 94)
    {
        $intRoll2 = rand(1, 10);
        if ($intRoll2 < 6)
        {
            $strSymbol = '<';
        }
        if ($intRoll2 == 6 || $intRoll2 == 7)
        {
            $strSymbol = '=';
        }
        if ($intRoll2 == 8)
        {
            $strSymbol = '>';
        }
        if ($intRoll2 < 9)
        {
            $objQuery = $db -> Execute("SELECT id FROM mage_items WHERE minlev".$strSymbol."".$player -> level." AND lang='".$player -> lang."' AND type='C'");
            $intAmount = $objQuery -> RecordCount();
            $objQuery -> Close();
            if ($intAmount > 0)
            {
                $intRoll4 = rand(0, ($intAmount-1));
                $objCape = $db -> SelectLimit("SELECT * FROM mage_items WHERE minlev".$strSymbol."".$player -> level." AND lang='".$player -> lang."' AND type='C'",1,$intRoll4);
                $objTest = $db -> Execute("SELECT id FROM equipment WHERE name='".$objCape -> fields['name']."' AND owner=".$player -> id);
                if (!$objTest -> fields['id']) 
                {
                    $intNewcost = $objCape -> fields['minlev'] * 100;
                    $db -> Execute("INSERT INTO equipment (owner, name, cost, minlev, type, power, status) VALUES(".$player -> id.",'".$objCape -> fields['name']."',".$intNewcost.",".$objCape -> fields['minlev'].",'".$objCape -> fields['type']."',".$objCape -> fields['power'].",'U')");
                    $smarty -> assign("Message", F_CAPE.$objCape -> fields['name'].F_CAPE2);
                }
                    else
                {
                    $smarty -> assign("Message", EMPTY_2);
                }
                $objTest -> Close();
                $objCape -> Close();
            }
                else
            {
                $smarty -> assign("Message", EMPTY_3);
            }
        }
        if ($intRoll2 > 8)
        {
            $smarty -> assign("Message", EMPTY_4);
        }
    }
    if ($intRoll == 95)
    {
        $intRoll2 = rand(1, 10);
        if ($intRoll2 < 6)
        {
            $strSymbol = '<';
        }
        if ($intRoll2 == 6 || $intRoll2 == 7)
        {
            $strSymbol = '=';
        }
        if ($intRoll2 == 8)
        {
            $strSymbol = '>';
        }
        if ($intRoll2 < 9)
        {
            $objQuery = $db -> Execute("SELECT id FROM bows WHERE minlev".$strSymbol."".$player -> level." AND lang='".$player -> lang."' AND type='B'");
            $intAmount = $objQuery -> RecordCount();
            $objQuery -> Close();
            if ($intAmount > 0)
            {
                $intRoll4 = rand(0, ($intAmount-1));
                $objBow = $db -> SelectLimit("SELECT * FROM bows WHERE minlev".$strSymbol."".$player -> level." AND lang='".$player -> lang."' AND type='B'",1,$intRoll4);
                $objTest = $db -> Execute("SELECT id FROM equipment WHERE name='".$objBow -> fields['name']."' AND owner=".$player -> id);
                if (!$objTest -> fields['id']) 
                {
                    $intNewcost = $objBow -> fields['minlev'] * 100;
                    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, szyb, twohand) VALUES(".$player -> id.",'".$objBow -> fields['name']."',".$objBow -> fields['power'].",'B',".$intNewcost.",".$objBow -> fields['zr'].",".$objBow -> fields['maxwt'].",".$objBow -> fields['minlev'].",".$objBow -> fields['maxwt'].",1,".$objBow -> fields['szyb'].",'Y')");
                    $smarty -> assign("Message", F_BOW.$objBow -> fields['name']);
                }
                    else
                {
                    $smarty -> assign("Message", EMPTY_5);
                }
                $objTest -> Close();
                $objBow -> Close();
            }
                else
            {
                $smarty -> assign("Message", EMPTY_6);
            }
        }
        if ($intRoll2 > 8)
        {
            $smarty -> assign("Message", EMPTY_1);
        }
    }
    /**
     * Find astral components
     */
    if ($intRoll == 96 || $intRoll == 97)
    {
        require_once('includes/findastral.php');
        $strResult = findastral(1);
        if ($strResult != false)
        {
            $smarty -> assign("Message", F_ASTRAL.$strResult);
        }
            else
        {
            $smarty -> assign("Message", EMPTY_1);
        }
    }
    if ($intRoll > 97)
    {
        $aviable = $db -> Execute("SELECT qid FROM quests WHERE location='maze.php' AND name='start'");
        $number = $aviable -> RecordCount();
        if ($number > 0) 
        {
            $arramount = array();
            $i = 0;
            while (!$aviable -> EOF) 
            {
                $query = $db -> Execute("SELECT id FROM questaction WHERE quest=".$aviable -> fields['qid']." AND player=".$player -> id);
                if (empty($query -> fields['id'])) 
                {
                    $arramount[$i] = $aviable -> fields['qid'];
                    $i = $i + 1;
                }
                $query -> Close();
                $aviable -> MoveNext();
            }
            $i = $i - 1;
            if ($i >= 0) 
            {
                $roll = rand(0,$i);
                $name = "quest".$arramount[$roll].".php";
                require_once("quests/".$name);
            } 
                else 
            {
                $smarty -> assign("Message", EMPTY_2);
            }
        }
            else
        {
            $smarty -> assign("Message", EMPTY_3);
        }
        $aviable -> Close();
    }
}

if (isset($_GET['step']) && $_GET['step'] == 'quest') 
{
    $query = $db -> Execute("SELECT quest FROM questaction WHERE player=".$player -> id." AND action!='end'");
    $name = "quest".$query -> fields['quest'].".php";
    if (!empty($query -> fields['quest'])) 
    {   
        require_once("quests/".$name);
    }
    $query -> Close();    
}

/**
* Initialization of variable
*/
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'],
                        "Step" => $_GET['step']));
$smarty -> display ('maze.tpl');

require_once("includes/foot.php");
?>
