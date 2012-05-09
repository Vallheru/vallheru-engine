<?php
/**
 *   File functions:
 *   Explore forest and mountains
 *
 *   @name                 : explore.php                            
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

$title = "Poszukiwania";
require_once("includes/head.php");
require_once("includes/funkcje.php");
require_once("includes/turnfight.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/explore.php");

if ($player -> location == 'Altara') 
{
    error (ERROR);
}

/**
* Assign variables to template
*/
$smarty -> assign(array("Link" => '', 
                        "Menu" => '',
                        "Youwant" => YOU_WANT,
                        "Ayes" => YES,
                        "Ano" => NO));

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
    $enemy1 = $db -> Execute("SELECT * FROM `monsters` WHERE `id`=".$player -> fight);
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
                   "level" => $enemy1 -> fields['level'],
		   "lootnames" => explode(";", $enemy1->fields['lootnames']),
		   "lootchances" => explode(";", $enemy1->fields['lootchances']));
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
    $fight = $db -> Execute("SELECT `fight`, `hp` FROM `players` WHERE `id`=".$player -> id);
    if ($fight -> fields['fight'] == 0) 
    {
        if ($type == 'T')
	  {
	    $player->energy --;
	    if ($player -> energy < 0) 
	      {
		$player -> energy = 0;
	      }
	    $db -> Execute("UPDATE `players` SET `energy`=".$player->energy." WHERE `id`=".$player->id);
	  }
        if ($player -> location == 'Góry') 
        {
            if ($fight -> fields['hp'] > 0)
            {
                $smarty -> assign ("Link", "<br /><br /><a href=\"explore.php?akcja=gory\">".A_REFRESH."</a><br />");
            }
                else
            {
                $smarty -> assign ("Link", "<br /><br /><a href=\"gory.php\">".A_REFRESH."</a><br />");
            }
        }
        if ($player -> location == 'Las') 
        {
            if ($fight -> fields['hp'] > 0)
            {
                $smarty -> assign ("Link", "<br /><br /><a href=\"explore.php\">".A_REFRESH."</a><br />");
            }
                else
            {
                $smarty -> assign ("Link", "<br /><br /><a href=\"las.php\">".A_REFRESH."</a><br />");
            }
        }
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
    battle($type,'explore.php?step=battle');
}

/**
* If player escape
*/
if (isset($_GET['step']) && $_GET['step'] == 'run') 
{
    $enemy = $db -> Execute("SELECT `level`, `speed`, `name`, `exp1`, `exp2`, `id` FROM monsters WHERE id=".$player -> fight);
    if (!$enemy->fields['id'])
      {
	error('Nie masz przed kim uciekać!');
      }
    /**
     * Add bonus to stats and skills
     */
    $arrEquip = $player -> equipment();
    $player->curstats($arrEquip);
    $player->curskills(array('perception'));
    $chance = (rand(1, $player -> level * 100) + ($player->speed + $player->perception) - $enemy -> fields['speed']);
    $smarty -> assign ("Chance", $chance);
    if ($chance > 0) 
    {
        $expgain = rand($enemy -> fields['exp1'],$enemy -> fields['exp2']);
        $expgain = ceil($expgain / 100);
	$fltPerception = ($enemy->fields['level'] / 100);
        $smarty -> assign(array("Ename" => $enemy -> fields['name'], 
                                "Expgain" => $expgain,
                                "Escapesucc" => ESCAPE_SUCC,
                                "Escapesucc2" => ESCAPE_SUCC2,
                                "Escapesucc3" => ESCAPE_SUCC3." oraz ".$fltPerception." do umiejętności Spostrzegawczość."));
        checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'perception', $fltPerception);
        $db -> Execute("UPDATE `players` SET `fight`=0 WHERE `id`=".$player -> id);
    } 
        else 
    {
        $strMessage = ESCAPE_FAIL." ".$enemy -> fields['name']." ".ESCAPE_FAIL2.".<br />";
	$db->Execute("UPDATE `players` SET `perception`=`perception`+0.01 WHERE `id`=".$player->id);
        $smarty -> assign ("Message", $strMessage);
        $smarty -> display ('error1.tpl');
        battle('T','explore.php?step=battle');
    }
    $hp = $db -> Execute("SELECT `hp` FROM `players` WHERE `id`=".$player -> id);
    $smarty -> assign ("Health", $hp -> fields['hp']);
    if ($player -> location == 'Góry' && $hp -> fields['hp'] > 0) 
    {
        $smarty -> assign (array("Yes" => "explore.php?akcja=gory", 
                                 "No" => "gory.php"));
    }
    if ($player -> location == 'Las' && $hp -> fields['hp'] > 0) 
    {
        $smarty -> assign(array("Yes" => "explore.php", 
                                "No" => "las.php"));
    }
    $hp -> Close();
}

/**
 * Explore moutains - main menu
 */
if ($player -> hp > 0 && !isset ($_GET['action']) && $player -> location == 'Góry' && !isset($_GET['step'])) 
{
    if (!empty($player -> fight)) 
    {
        $enemy = $db -> Execute("SELECT `name` FROM `monsters` WHERE `id`=".$player -> fight);
        error (FIGHT1.$enemy -> fields['name'].FIGHT2."<br />
           <a href=\"explore.php?step=battle\">".YES."</a><br />
           <a href=\"explore.php?step=run\">".NO."</a><br />");
        $enemy -> Close();
    }
        else
    {
        $smarty -> assign(array("Minfo" => M_INFO,
                                "Howmuch" => HOW_MUCH,
                                "Tenergy" => T_ENERGY,
                                "Awalk" => T_WALK));
    }
}

/**
* Explore mountains - random encouter
*/
if (isset($_GET['action']) && $_GET['action'] == 'moutains' && $player -> location == 'Góry' && !isset($_GET['step'])) 
{
    if (!isset($_POST['amount'])) 
    {
        error(ERROR);
    }
    $_POST['amount'] = floatval($_POST['amount']);
    if ($_POST['amount'] < 0.5)
      {
	error(ERROR);
      }
    if ($_POST['amount'] > $player -> energy)
    {
        error(TIRED2);
    }
    if ($player -> hp <= 0) 
    {
        error(YOU_DEAD2);
    }
    if (!empty($player -> fight)) 
    {
        $enemy = $db -> Execute("SELECT `name` FROM `monsters` WHERE `id`=".$player -> fight);
        error (FIGHT3.$enemy -> fields['name'].FIGHT2."<br />
               <a href=\"explore.php?step=battle\">".YES."</a><br />
               <a href=\"explore.php?step=run\">".NO."</a><br />");
    }
    $objMaps = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='maps'");
    $intAmount2 = floor($_POST['amount'] * 2);
    $arrGold = array(0, 0);
    $arrHerbs = array(0, 0, 0, 0);
    $intMeteor = 0;
    $intAstral = 0;
    $strEnemy = '';
    $strBridge = '';
    for ($i = 1; $i < $intAmount2; $i++)
    {
        $intRoll = rand(1, 20);
        if ($intRoll == 9) 
        {
            $intAmount = rand(1,1000);
            $arrGold[0] = $arrGold[0] + $intAmount;
        }
        elseif ($intRoll == 10) 
        {
            $intAmount = rand(1,20);
            $intMeteor = $intMeteor + $intAmount;
        }
        elseif ($intRoll >= 11 && $intRoll <= 13) 
        {
            $intAmount = rand(1,10);
            $arrHerbs[0] = $arrHerbs[0] + $intAmount;
        }
        elseif ($intRoll >= 14 && $intRoll <= 15) 
        {
            $intAmount = rand(1,10);
            $arrHerbs[1] = $arrHerbs[1] + $intAmount;
        }
        elseif ($intRoll == 16) 
        {
            $intAmount = rand(1,10);
            $arrHerbs[2] = $arrHerbs[2] + $intAmount;
        }
        elseif ($intRoll == 18) 
        {
            $intAmount = rand(1,10);
            $arrHerbs[3] = $arrHerbs[3] + $intAmount;
        }
        elseif ($intRoll == 19) 
        {
            $intRoll2 = rand(1, 50);
            if ($intRoll2 == 50 && $objMaps -> fields['value'] > 0 && $player -> maps < 20 && $player -> rank != 'Bohater') 
            {
                $objMaps -> fields['value'] --;
                $player -> maps ++;
                $arrGold[1] ++;
            } 
        }
        elseif ($intRoll == 20)
        {
            require_once('includes/findastral.php');
            $strResult = findastral(2);
            if ($strResult != false)
            {
                $intAstral ++;
            }
        }
        elseif ($intRoll > 5 && $intRoll < 9) 
	  {
	    $intRoll2 = rand(1, 100);
	    if ($intRoll2 < 25)
	      {
		$enemy = $db->SelectLimit("SELECT `name`, `id` FROM `monsters` WHERE `level`<=".$player->level." AND `location`='Altara' ORDER BY RAND()", 1);
	      }
	    elseif ($intRoll2 > 24 && $intRoll2 < 90)
	      {
		$enemy = $db->SelectLimit("SELECT `name`, `id` FROM `monsters` WHERE `level`<=".$player->level." AND `location`='Altara' ORDER BY `level` DESC", 1);
	      }
	    else
	      {
		$enemy = $db->SelectLimit("SELECT `name`, `id` FROM `monsters` WHERE `level`>=".$player->level." AND `location`='Altara' ORDER BY RAND()", 1);
	      }
            $db -> Execute("UPDATE `players` SET `fight`=".$enemy -> fields['id']." WHERE `id`=".$player -> id);
            $strEnemy = YOU_MEET." ".$enemy -> fields['name'].FIGHT2."<br />
               <a href=\"explore.php?step=battle\">".YES."</a><br />
               <a href=\"explore.php?step=run\">".NO."</a><br />";
            $player -> fight = $enemy -> fields['id'];
            $enemy -> Close();
            break;
	  }
        elseif ($intRoll == 17)
        {
            $objBridge = $db -> Execute("SELECT `bridge` FROM `players` WHERE `id`=".$player -> id);
            if ($objBridge -> fields['bridge'] == 'N')
            {
                $strBridge = ACTION8;
                break;
            }
            $objBridge -> Close();
        }
    }

    $intHerbsum = array_sum($arrHerbs);
    $intGoldsum = array_sum($arrGold);
    if ($intHerbsum)
    {
        $objHerbs = $db -> Execute("SELECT `gracz` FROM `herbs` WHERE `gracz`=".$player -> id);
        if ($objHerbs -> fields['gracz'])
        {
            $db -> Execute("UPDATE `herbs` SET `illani`=`illani`+".$arrHerbs[0].", `illanias`=`illanias`+".$arrHerbs[1].", `nutari`=`nutari`+".$arrHerbs[2].", `dynallca`=`dynallca`+".$arrHerbs[3]." WHERE `gracz`=".$player -> id);
        }
            else
        {
            $db -> Execute("INSERT INTO `herbs` (`gracz`, `illani`, `illanias`, `nutari`, `dynallca`) VALUES(".$player -> id.", ".$arrHerbs[0].", ".$arrHerbs[1].", ".$arrHerbs[2].", ".$arrHerbs[3].")");
        }
        $objHerbs -> Close();
    }
    if ($intMeteor)
    {
        $objMinerals = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$player -> id);
        if ($objMinerals -> fields['owner'])
        {
            $db -> Execute("UPDATE `minerals` SET `meteor`=`meteor`+".$intMeteor." WHERE `owner`=".$player -> id);
        }
            else
        {
            $db -> Execute("INSERT INTO `minerals` (`owner`, `meteor`) VALUES(".$player -> id.", ".$intMeteor.")");
        }
        $objMinerals -> Close();
    }
    $fltAmount = $i / 2;
    $strFind = YOU_GO.$fltAmount.T_AMOUNT2;
    if ($intHerbsum || $intGoldsum || $intAstral || $intMeteor)
    {
        $strFind = $strFind.YOU_FIND;
        $arrAmounts = array($intMeteor, $arrHerbs[0], $arrHerbs[1], $arrHerbs[2], $arrHerbs[3], $intAstral, $arrGold[0], $arrGold[1]);
        $arrText = array(T_METEOR, HERB1, HERB2, HERB3, HERB4, T_ASTRALS, T_GOLD, T_MAPS);
        $i = 0;
        foreach ($arrAmounts as $intAmount)
        {
            if ($intAmount)
            {
                $strFind = $strFind.$intAmount.$arrText[$i];
            }
            $i ++;
        }
        $strFind = $strFind."<br />";
    }
    if (!$intHerbsum && !$intGoldsum && !$intAstral && $strEnemy == '' && $strBridge == '' && !$intMeteor)
    {
        $strFind = $strFind.FIND_NOTHING;
    }
    $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$arrGold[0].", `energy`=`energy`-".$fltAmount.", `maps`=".$player -> maps." WHERE `id`=".$player -> id);
    $player->energy -= $fltAmount;
    $db -> Execute("UPDATE `settings` SET `value`=".$objMaps -> fields['value']." WHERE `setting`='maps'");
    $objMaps -> Close();
    $smarty -> assign(array("Youfind" => $strFind,
                            "Howmuch" => HOW_MUCH,
                            "Tenergy" => T_ENERGY,
                            "Awalk" => T_WALK,
                            "Enemy" => $strEnemy,
                            "Bridge" => $strBridge));
}

/**
 * Bridge of death in moutains
 */
if (isset($_GET['action']) && $_GET['action'] == 'moutains' && $player -> location == 'Góry') 
{
    if (isset($_GET['step']) && $_GET['step'] == 'first') 
    {
        if (!isset($_POST['check'])) 
        {
            error(ERROR);
        }
        $smarty -> assign(array("Fquestion" => F_QUESTION,
                                "Anext" => A_NEXT));
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'second') 
    {
        $answer = htmlspecialchars($_POST['fanswer'], ENT_QUOTES);
        if ($answer == $player -> id) 
        {
            $smarty -> assign (array("Answer" => "true",
                                     "Squestion" => S_QUESTION,
                                     "Anext" => A_NEXT));
        } 
            else 
        {
            $db -> Execute("UPDATE `players` SET `hp`=0 WHERE `id`=".$player -> id);
            $smarty -> assign (array("Answer" => "false",
                                     "Qfail" => Q_FAIL));
        }
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'third') 
    {
        if (!isset($_POST['sanswer'])) 
        {
            $_POST['sanswer'] = '';
        }
        $answer = htmlspecialchars($_POST['sanswer'], ENT_QUOTES);
        $answer = strtolower($answer);
        $gamename = strtolower($gamename);
        if ($answer == $gamename) 
        {
            $query = $db -> Execute("SELECT count(`id`) FROM `bridge`");
            $amount = $query->fields['count(`id`)'];
            $query -> Close();
            $number = rand(1, $amount);
            $test = $db -> Execute("SELECT `temp` FROM `players` WHERE `id`=".$player -> id);
            if ($test -> fields['temp'] != 0) 
            {
                $number = $test -> fields['temp'];
            }
            $test -> Close();
            $question = $db -> Execute("SELECT `question` FROM `bridge` WHERE `id`=".$number);
            $db -> Execute("UPDATE `players` SET `temp`=".$number." WHERE `id`=".$player -> id);
            $smarty -> assign(array("Question" => $question -> fields['question'], 
                                    "Answer" => "true",
                                    "Tquestion" => T_QUESTION,
                                    "Anext" => A_NEXT));
            $question -> Close();
        } 
            else 
        {
            $db -> Execute("UPDATE `players` SET `hp`=0 WHERE `id`=".$player -> id);
            $smarty -> assign(array("Answer" => "false",
                                    "Qfail" => Q_FAIL));
        }
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'forth') 
    {
        if (!isset($_POST['tanswer'])) 
        {
            $_POST['tanswer'] = '';
        }
        $test = $db -> Execute("SELECT `bridge`, `temp` FROM `players` WHERE `id`=".$player -> id);
	if ($test->fields['temp'] == 0)
	  {
	    error(ERROR);
	  }
	$answer = $db -> Execute("SELECT `answer` FROM `bridge` WHERE `id`=".$test->fields['temp']);
        if ($test -> fields['bridge'] == 'Y') 
        {
            error(ONLY_ONCE);
        }
        $test -> Close();
        $db -> Execute("UPDATE `players` SET `temp`=0 WHERE `id`=".$player -> id);
        $panswer = htmlspecialchars($_POST['tanswer'], ENT_QUOTES);
        if ($panswer == $answer -> fields['answer']) 
        {
            $query = $db -> Execute("SELECT count(`id`) FROM `equipment` WHERE `owner`=0 AND `minlev`<=".$player -> level);
            $amount = $query -> fields['count(`id`)'];
            $query -> Close();
            $roll = rand (0, ($amount-1));
            $arritem = $db -> SelectLimit("SELECT * FROM `equipment` WHERE `owner`=0", 1, $roll);
            $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$arritem -> fields['name']."' AND `wt`=".$arritem -> fields['maxwt']." AND `type`='".$arritem -> fields['type']."' AND `status`='U' AND `owner`=".$player -> id." AND `power`=".$arritem -> fields['power']." AND `zr`=".$arritem -> fields['zr']." AND `szyb`=".$arritem -> fields['szyb']." AND `maxwt`=".$arritem -> fields['maxwt']." AND `cost`=1 AND `poison`=0");
            if (!$test -> fields['id']) 
            {
                $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `szyb`, `twohand`) VALUES(".$player -> id.",'".$arritem -> fields['name']."',".$arritem -> fields['power'].",'".$arritem -> fields['type']."',1,".$arritem -> fields['zr'].",".$arritem -> fields['maxwt'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",1,".$arritem -> fields['szyb'].",'".$arritem -> fields['twohand']."')");
            } 
                else 
            {
                $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$test -> fields['id']);
            }
            $test -> Close();
            $db -> Execute("UPDATE `players` SET `bridge`='Y' WHERE `id`=".$player -> id);
            $smarty -> assign(array("Answer" => "true", 
                                    "Item" => $arritem -> fields['name'],
                                    "Qsucc" => Q_SUCC,
                                    "Qsucc2" => Q_SUCC2,
                                    "Arefresh" => A_REFRESH));
        } 
	else 
	  {
	    $strInfo = Q_FAIL;
	    if ($player->antidote == 'R')
	      {
		$db->Execute("UPDATE `players` SET `hp`=1, `antidote`='' WHERE `id`=".$player->id);
		$strInfo .= '<br />Tym razem jednak udało ci się oszukać śmierć.';
	      }
	    else
	      {
		$db -> Execute("UPDATE `players` SET `hp`=0 WHERE `id`=".$player -> id);
	      }
            $smarty -> assign(array("Answer" => "false",
                                    "Qfail" => Q_FAIL));
	  }
    }
}

/**
 * Explore forest - main menu
 */
if ($player -> hp > 0 && !isset ($_GET['action']) && $player -> location == 'Las' && !isset($_GET['step'])) 
{
    if (!empty($player -> fight)) 
    {
        $enemy = $db -> Execute("SELECT `name` FROM `monsters` WHERE `id`=".$player -> fight);
        error (FIGHT3.$enemy -> fields['name'].FIGHT2."<br />
           <a href=explore.php?step=battle&type=T>".Y_TURN_F."</a><br />
           <a href=explore.php?step=battle&type=N>".Y_NORM_F."</a><br />
           <a href=explore.php?step=run>".NO."</a><br />");
    }
        else
    {
        $smarty -> assign(array("Finfo" => F_INFO,
                                "Howmuch" => HOW_MUCH,
                                "Tenergy" => T_ENERGY,
                                "Awalk" => T_WALK));
    }
}

/**
* Explore forest - random encouter
*/
if (isset($_GET['action']) && $_GET['action'] == 'forest' && $player -> location == 'Las')
{
    if (!isset($_POST['amount'])) 
    {
        error(ERROR);
    }
    $_POST['amount'] = floatval($_POST['amount']);
    if ($_POST['amount'] < 0.5)
      {
	error(ERROR);
      }
    if ($_POST['amount'] > $player -> energy)
    {
        error(TIRED2);
    }
    if ($player -> hp <= 0) 
    {
        error(YOU_DEAD2);
    }
    if (!empty($player -> fight)) 
    {
        $enemy = $db -> Execute("SELECT `name` FROM `monsters` WHERE `id`=".$player -> fight);
        error (FIGHT3.$enemy -> fields['name'].FIGHT2."<br />
               <a href=\"explore.php?step=battle\">".YES."</a><br />
               <a href=\"explore.php?step=run\">".NO."</a><br />");
    }
    $objMaps = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='maps'");
    $intAmount = floor($_POST['amount'] * 2);
    $arrGold = array(0, 0, 0);
    $arrHerbs = array(0, 0, 0, 0);
    $intAstral = 0;
    $strEnemy = '';
    for ($i = 1; $i < $intAmount; $i++)
    {
        $intRoll = rand(1, 19);
        if ($intRoll == 9) 
        {
            $intGold = rand(1,1000);
            $arrGold[0] = $arrGold[0] + $intGold;
        }
        elseif ($intRoll == 10) 
        {
            $intEnergy = rand(1, 2);
            $arrGold[1] = $arrGold[1] + $intEnergy;
        }
        elseif ($intRoll >= 11 && $intRoll <= 13) 
        {
            $intHerb = rand(1,10);
            $arrHerbs[0] = $arrHerbs[0] + $intHerb;
        }
        elseif ($intRoll >= 14 && $intRoll <= 15) 
        {
            $intHerb = rand(1,10);
            $arrHerbs[1] = $arrHerbs[1] + $intHerb;
        }
        elseif ($intRoll == 16) 
        {
            $intHerb = rand(1,10);
            $arrHerbs[2] = $arrHerbs[2] + $intHerb;
        }
        elseif ($intRoll == 17) 
        {
            $intHerb = rand(1,10);
            $arrHerbs[3] = $arrHerbs[3] + $intHerb;
        }
        elseif ($intRoll == 18) 
        {
            $intRoll2 = rand(1, 50);
            if ($intRoll2 == 50 && $objMaps -> fields['value'] > 0 && $player -> maps < 20 && $player -> rank != 'Bohater') 
            {
                $objMaps -> fields['value'] --;
                $player -> maps ++;
                $arrGold[2] ++;
            } 
        }
        elseif ($intRoll == 19)
        {
            require_once('includes/findastral.php');
            $strResult = findastral(2);
            if ($strResult != false)
            {
                $intAstral ++;
            }
        }
        elseif ($intRoll > 5 && $intRoll < 9) 
        {
	    $intRoll2 = rand(1, 100);
	    if ($intRoll2 < 25)
	      {
		$enemy = $db->SelectLimit("SELECT `name`, `id` FROM `monsters` WHERE `level`<=".$player->level." AND `location`='Ardulith' ORDER BY RAND()", 1);
	      }
	    elseif ($intRoll2 > 24 && $intRoll2 < 90)
	      {
		$enemy = $db->SelectLimit("SELECT `name`, `id` FROM `monsters` WHERE `level`<=".$player->level." AND `location`='Ardulith' ORDER BY `level` DESC", 1);
	      }
	    else
	      {
		$enemy = $db->SelectLimit("SELECT `name`, `id` FROM `monsters` WHERE `level`>=".$player->level." AND `location`='Ardulith' ORDER BY RAND()", 1);
	      }
            $db -> Execute("UPDATE `players` SET `fight`=".$enemy -> fields['id']." WHERE `id`=".$player -> id);
            $strEnemy = YOU_MEET." ".$enemy -> fields['name'].FIGHT2."<br />
               <a href=\"explore.php?step=battle\">".YES."</a><br />
               <a href=\"explore.php?step=run\">".NO."</a><br />";
            $player -> fight = $enemy -> fields['id'];
            $enemy -> Close();
            break;
        }
    }

    $intHerbsum = array_sum($arrHerbs);
    $intGoldsum = array_sum($arrGold);
    if ($intHerbsum)
    {
        $objHerbs = $db -> Execute("SELECT `gracz` FROM `herbs` WHERE `gracz`=".$player -> id);
        if ($objHerbs -> fields['gracz'])
        {
            $db -> Execute("UPDATE `herbs` SET `illani`=`illani`+".$arrHerbs[0].", `illanias`=`illanias`+".$arrHerbs[1].", `nutari`=`nutari`+".$arrHerbs[2].", `dynallca`=`dynallca`+".$arrHerbs[3]." WHERE `gracz`=".$player -> id) or die("Błąd");
        }
            else
        {
            $db -> Execute("INSERT INTO `herbs` (`gracz`, `illani`, `illanias`, `nutari`, `dynallca`) VALUES(".$player -> id.", ".$arrHerbs[0].", ".$arrHerbs[1].", ".$arrHerbs[2].", ".$arrHerbs[3].")");
        }
        $objHerbs -> Close();
    }
    $fltAmount = $i / 2;
    $strFind = YOU_GO.$fltAmount.T_AMOUNT2;
    if ($intHerbsum || $intGoldsum || $intAstral)
    {
        $strFind = $strFind.YOU_FIND;
        $arrAmounts = array($arrHerbs[0], $arrHerbs[1], $arrHerbs[2], $arrHerbs[3], $intAstral, $arrGold[0], $arrGold[1], $arrGold[2]);
        $arrText = array(HERB1, HERB2, HERB3, HERB4, T_ASTRALS, T_GOLD, T_ENERGY2, T_MAPS);
        $i = 0;
        foreach ($arrAmounts as $intAmount)
        {
            if ($intAmount)
            {
                $strFind = $strFind.$intAmount.$arrText[$i];
            }
            $i ++;
        }
        $strFind = $strFind."<br />";
    }
    if (!$intHerbsum && !$intGoldsum && !$intAstral && $strEnemy == '')
    {
        $strFind = $strFind.FIND_NOTHING;
    }
    $fltEnergy = $fltAmount - $arrGold[1];
    $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$arrGold[0].", `energy`=`energy`-".$fltEnergy.", `maps`=".$player -> maps." WHERE `id`=".$player -> id);
    $player->energy -= $fltEnergy;
    $db -> Execute("UPDATE `settings` SET `value`=".$objMaps -> fields['value']." WHERE `setting`='maps'");
    $objMaps -> Close();
    $smarty -> assign(array("Youfind" => $strFind,
                            "Howmuch" => HOW_MUCH,
                            "Tenergy" => T_ENERGY,
                            "Awalk" => T_WALK,
                            "Enemy" => $strEnemy));
}

/**
* Initialization of variables
*/
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}
if (!isset($rzut)) 
{
    $rzut = '';
}

$smarty -> assign(array("Step" => $_GET['step'], 
                        "Fight" => $player -> fight, 
                        "Action" => $_GET['action'],
                        "Location" => $player -> location, 
                        "Roll" => $rzut,
			"Curen" => $player->energy,
                        "Health" => $player -> hp));
$smarty -> display('explore.tpl');

require_once("includes/foot.php");
?>
