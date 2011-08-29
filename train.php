<?php
/**
 *   Funkcje pliku:
 *   School - train stats
 *
 *   @name                 : train.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 29.08.2011
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

$title = "Szkolenie";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/train.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

if ($player -> hp == 0) 
{
    error (YOU_DEAD." <a href=\"city.php\">".BACK."</a>.");
}

if (!$player -> race)
{
    error(NO_RACE." <a href=\"city.php\">".BACK."</a>.");
}

if (!$player -> clas) 
{
    error(NO_CLASS." <a href=\"city.php\">".BACK."</a>.");
}

$fltStat = (log10($player -> level) + 1);

$intStrcost = ceil($player -> strength / $fltStat);
$intAgicost = ceil($player -> agility / $fltStat);
$intConcost = ceil($player -> cond / $fltStat);
$intSpecost = ceil($player -> speed / $fltStat);

if ($player -> location == 'Altara')
{
    $intIntcost = ceil($player -> inteli / $fltStat);
    $intWiscost = ceil($player -> wisdom / $fltStat);
}
    else
{
    $intIntcost = ceil(($player -> inteli / $fltStat) - (($player -> inteli / $fltStat) / 10));
    $intWiscost = ceil(($player -> wisdom / $fltStat) - (($player -> wisdom / $fltStat) / 10));
}

if ($player -> race == 'Człowiek') 
{
    $smarty -> assign ("Train", T_TRAIN.$intStrcost.T_TRAIN_2.$intAgicost.T_TRAIN_3.$intSpecost.T_TRAIN_4.$intConcost.T_TRAIN_5);
}

if ($player -> race == 'Elf') 
{
    $smarty -> assign ("Train", T_TRAIN2.$intStrcost.T_TRAIN2_2.$intConcost.T_TRAIN2_3.$intAgicost.T_TRAIN2_4.$intSpecost.T_TRAIN2_5);
}

if ($player -> race == 'Krasnolud') 
{
    $smarty -> assign ("Train", T_TRAIN3.$intStrcost.T_TRAIN3_2.$intConcost.T_TRAIN3_3.$intAgicost.T_TRAIN3_4.$intSpecost.T_TRAIN3_5);
}

if ($player -> race == 'Hobbit') 
{
    $smarty -> assign ("Train", T_TRAIN4.$intStrcost.T_TRAIN4_2.$intSpecost.T_TRAIN4_3.$intAgicost.T_TRAIN4_4.$intConcost.T_TRAIN4_5);
}

if ($player -> race == 'Jaszczuroczłek') 
{
    $smarty -> assign ("Train", T_TRAIN5.$intAgicost.T_TRAIN5_2.$intConcost.T_TRAIN5_3.$intStrcost.T_TRAIN5_4.$intSpecost.T_TRAIN5_5);
}

if ($player -> race == 'Gnom') 
{
    $smarty -> assign ("Train", T_TRAIN4.$intStrcost.T_TRAIN4_2.$intSpecost.T_TRAIN9.$intAgicost.T_TRAIN4_4.$intConcost.T_TRAIN4_5);
}

if ($player -> clas == 'Wojownik' || $player -> clas == 'Barbarzyńca') 
{
    $smarty -> assign ("Train2", T_TRAIN6.$intWiscost.T_TRAIN6_2.$intIntcost.T_TRAIN6_3);
}

if ($player -> clas == 'Mag') 
{
    $smarty -> assign ("Train2", T_TRAIN7.$intWiscost.T_TRAIN7_2.$intIntcost.T_TRAIN7_3);
}

if ($player -> clas == 'Rzemieślnik' || $player -> clas == 'Złodziej') 
{
    $smarty -> assign ("Train2", T_TRAIN8.$intWiscost.T_TRAIN8_2.$intIntcost.T_TRAIN8_3);
}

if ($player -> race == 'Gnom') 
{
    $smarty -> assign ("Train2", T_TRAIN9_1.$intWiscost.T_TRAIN9_2.$intIntcost.T_TRAIN9_3);
}

if (isset ($_GET['action']) && $_GET['action'] == 'train') 
{
    if (!isset($_POST['rep']))
    {
        error(HOW_MANY);
    }
    checkvalue($_POST['rep']);
    $arrStats = array('strength', 'agility', 'inteli', 'szyb', 'wytrz', 'wisdom');
    if (!in_array($_POST['train'], $arrStats)) 
    {
        error (ERROR);
    }
    if ($player -> race == 'Człowiek') 
    {
        $repeat = ($_POST["rep"] * .3);
    }
    if ($player -> race == 'Gnom' && ($_POST["train"] == 'agility' || $_POST['train'] == 'wytrz')) 
    {
        $repeat = ($_POST["rep"] * .3);
    }
    if ($player -> race == 'Elf' && $_POST["train"] == 'strength') 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> race == 'Elf' && $_POST["train"] == 'wytrz') 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> race == 'Elf' && $_POST["train"] == 'agility') 
    {
        $repeat = ($_POST["rep"] * .2);
    }
    if ($player -> race == 'Elf' && $_POST["train"] == 'szyb') 
    {
        $repeat = ($_POST["rep"] * .2);
    }
    if ($player -> race == 'Krasnolud' && $_POST["train"] == 'strength') 
    {
        $repeat = ($_POST["rep"] * .2);
    }
    if ($player -> race == 'Krasnolud' && $_POST["train"] == 'wytrz') 
    {
        $repeat = ($_POST["rep"] * .2);
    }
    if ($player -> race == 'Krasnolud' && $_POST["train"] == 'agility') 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> race == 'Krasnolud' && $_POST["train"] == 'szyb') 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> race == 'Jaszczuroczłek' && $_POST["train"] == 'szyb') 
    {
        $repeat = ($_POST["rep"] * .2);
    }
    if ($player -> race == 'Jaszczuroczłek' && $_POST["train"] == 'wytrz') 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> race == 'Jaszczuroczłek' && $_POST["train"] == 'agility') 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> race == 'Jaszczuroczłek' && $_POST["train"] == 'strength') 
    {
        $repeat = ($_POST["rep"] * .2);
    }
    if (($player -> race == 'Hobbit' || $player -> race == 'Gnom') && $_POST["train"] == 'szyb') 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> race == 'Hobbit' && $_POST["train"] == 'wytrz') 
    {
        $repeat = ($_POST["rep"] * .2);
    }
    if ($player -> race == 'Hobbit' && $_POST["train"] == 'agility') 
    {
        $repeat = ($_POST["rep"] * .2);
    }
    if (($player -> race == 'Hobbit' || $player -> race == 'Gnom') && $_POST["train"] == 'strength') 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> race == '') 
    {
        error (NO_RACE);
    }
    if ($player -> clas == 'Wojownik' && $_POST["train"]) 
    {
        if ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom') 
        {
            $repeat = ($_POST["rep"] * .4);
        }
    }
    if ($player -> clas == 'Mag' && $_POST["train"]) 
    {
        if ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom') 
        {
            $repeat = ($_POST["rep"] * .2);
        }
    }
    if ($player -> clas == 'Rzemieślnik' && $_POST["train"]) 
    {
        if ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom') 
        {
            $repeat = ($_POST["rep"] * .3);
        }
    }
    if ($player -> clas == 'Barbarzyńca' && $_POST["train"]) 
    {
        if ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom') 
        {
            $repeat = ($_POST["rep"] * .3);
        }
    }
    if ($player -> clas == 'Złodziej' && $_POST["train"]) 
    {
        if ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom') 
        {
            $repeat = ($_POST["rep"] * .3);
        }
    }
    if ($player -> race == 'Gnom' && $_POST["train"]) 
    {
        if ($_POST['train'] == 'wisdom') 
        {
            $repeat = ($_POST["rep"] * .4);
        }
    }
    if ($player -> clas == '') 
    {
        error (NO_CLASS);
    }
    $gain = ($_POST["rep"] * .060);
    $repeat = round($repeat, 1);
    if ($repeat > $player -> energy) 
    {
        error (NO_ENERGY);
    }
    if ($_POST['train'] == 'strength') 
    {
        $cecha = T_STR;
        $intCost = $intStrcost;
    }
    if ($_POST['train'] == 'agility') 
    {
        $cecha = T_AGI;
        $intCost = $intAgicost;
    }
    if ($_POST['train'] == 'inteli') 
    {
        $cecha = T_INT;
        $intCost = $intIntcost;
    }
    if ($_POST['train'] == 'szyb') 
    {
        $cecha = T_SPEED;
        $intCost = $intSpecost;
    }
    if ($_POST['train'] == 'wytrz') 
    {
        $cecha = T_CON;
        $intCost = $intConcost;
    }
    if ($_POST['train'] == 'wisdom') 
    {
        $cecha = T_WIS;
        $intCost = $intWiscost;
    }
    $intCost = ceil($intCost + (($_POST['rep'] - 1) * 0.03));
    $intCost2 = ($intCost * $_POST['rep']);
    if ($player -> credits < $intCost2) 
    {
        error (NO_MONEY.$intCost2.GOLD_COINS);
    }
    $smarty -> assign(array("Allcost" => ALL_COST,
                            "Maxcost" => $intCost2,
                            "Train" => $_POST['train'],
                            "Rep" => $_POST['rep'],
                            "Goldcoins" => GOLD_COINS));
    if (isset($_GET['step']) && $_GET['step'] == 'next')
    {
        if ($_POST['train'] == 'wytrz') 
        {
            $intCondition = floor($player -> cond + $gain);
            if ($intCondition > $player -> cond)
            {
                $intGain = $intCondition - floor($player -> cond);
                $db -> Execute("UPDATE `players` SET `max_hp`=`max_hp`+".$intGain." WHERE `id`=".$player -> id);
            } 
        }
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$repeat.", ".$_POST['train']."=".$_POST['train']."+".$gain.", `credits`=`credits`-".$intCost2." WHERE `id`=".$player -> id);
        error (YOU_GAIN.$gain." ".$cecha.YOU_PAY.$intCost2.GOLD_COINS);
    }
}

/**
* Initialization ov variables
*/
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}
if (!isset($_GET['step']))
{
    $_GET['step'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Traininfo" => TRAIN_INFO,
                        "Trstr" => TR_STR,
                        "Tragi" => TR_AGI,
                        "Trspeed" => TR_SPEED,
                        "Trcon" => TR_CON,
                        "Trint" => TR_INT,
                        "Trwis" => TR_WIS,
                        "Iwant" => I_WANT,
                        "Tamount" => T_AMOUNT,
                        "Atrain" => A_TRAIN,
                        "Action" => $_GET['action'],
                        "Step" => $_GET['step']));
$smarty -> display ('train.tpl');

require_once("includes/foot.php");
?>
