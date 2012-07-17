<?php
/**
 *   Funkcje pliku:
 *   School - train stats
 *
 *   @name                 : train.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 17.07.2012
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
require_once("languages/".$lang."/train.php");

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

$intStrcost = ceil($player->oldstats[1] / $fltStat);
$intAgicost = ceil($player->oldstats[0] / $fltStat);
$intConcost = ceil($player->oldstats[5] / $fltStat);
$intSpecost = ceil($player->oldstats[4] / $fltStat);

if ($player -> location == 'Altara')
{
    $intIntcost = ceil($player->oldstats[2] / $fltStat);
    $intWiscost = ceil($player->oldstats[3] / $fltStat);
    $intLess = 0;
}
    else
{
    $intIntcost = ceil(($player->oldstats[2] / $fltStat) - (($player->oldstats[2] / $fltStat) / 10));
    $intWiscost = ceil(($player->oldstats[3] / $fltStat) - (($player->oldstats[3] / $fltStat) / 10));
    $intLess = 1;
}

switch ($player->race)
  {
  case 'Człowiek':
    $smarty -> assign ("Train", T_TRAIN.$intStrcost.T_TRAIN_2.$intAgicost.T_TRAIN_3.$intSpecost.T_TRAIN_4.$intConcost.T_TRAIN_5);
    break;
  case 'Elf':
    $smarty -> assign ("Train", T_TRAIN2.$intStrcost.T_TRAIN2_2.$intConcost.T_TRAIN2_3.$intAgicost.T_TRAIN2_4.$intSpecost.T_TRAIN2_5);
    break;
  case 'Krasnolud':
    $smarty -> assign ("Train", T_TRAIN3.$intStrcost.T_TRAIN3_2.$intConcost.T_TRAIN3_3.$intAgicost.T_TRAIN3_4.$intSpecost.T_TRAIN3_5);
    break;
  case 'Hobbit':
    $smarty -> assign ("Train", T_TRAIN4.$intStrcost.T_TRAIN4_2.$intSpecost.T_TRAIN4_3.$intAgicost.T_TRAIN4_4.$intConcost.T_TRAIN4_5);
    break;
  case 'Jaszczuroczłek':
    $smarty -> assign ("Train", T_TRAIN5.$intAgicost.T_TRAIN5_2.$intConcost.T_TRAIN5_3.$intStrcost.T_TRAIN5_4.$intSpecost.T_TRAIN5_5);
    break;
  case 'Gnom':
    $smarty -> assign ("Train", T_TRAIN4.$intStrcost.T_TRAIN4_2.$intSpecost.T_TRAIN9.$intAgicost.T_TRAIN4_4.$intConcost.T_TRAIN4_5);
    break;
  default:
    break;
}

switch ($player->clas)
  {
  case 'Wojownik':
  case 'Barbarzyńca':
    $smarty -> assign ("Train2", T_TRAIN6.$intWiscost.T_TRAIN6_2.$intIntcost.T_TRAIN6_3);
    break;
  case 'Mag':
    $smarty -> assign ("Train2", T_TRAIN7.$intWiscost.T_TRAIN7_2.$intIntcost.T_TRAIN7_3);
    break;
  case 'Rzemieślnik':
  case 'Złodziej':
    $smarty -> assign ("Train2", T_TRAIN8.$intWiscost.T_TRAIN8_2.$intIntcost.T_TRAIN8_3);
    break;
  default:
    break;
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
    if ($player -> race == '') 
    {
        error (NO_RACE);
    }
    if ($player -> clas == '') 
    {
        error (NO_CLASS);
    }
    if ($player -> race == 'Człowiek') 
    {
        $repeat = ($_POST["rep"] * .3);
    }
    elseif ($player -> race == 'Gnom' && ($_POST["train"] == 'agility' || $_POST['train'] == 'wytrz')) 
    {
        $repeat = ($_POST["rep"] * .3);
    }
    elseif ($player->race == 'Elf')
      {
	if ($_POST['train'] == 'strength' || $_POST['train'] == 'wytrz')
	  {
	    $repeat = ($_POST["rep"] * .4);
	  }
	elseif ($_POST['train'] == 'agility' || $_POST['train'] == 'szyb')
	  {
	    $repeat = ($_POST["rep"] * .2);
	  }
      }
    elseif ($player->race == 'Krasnolud')
      {
	if ($_POST['train'] == 'strength' || $_POST['train'] == 'wytrz')
	  {
	    $repeat = ($_POST["rep"] * .2);
	  }
	elseif ($_POST['train'] == 'agility' || $_POST['train'] == 'szyb')
	  {
	    $repeat = ($_POST["rep"] * .4);
	  }
    }
    elseif ($player->race == 'Jaszczuroczłek')
      {
	if ($_POST['train'] == 'szyb' || $_POST['train'] == 'strength')
	  {
	    $repeat = ($_POST["rep"] * .2);
	  }
	elseif ($_POST['train'] == 'wytrz' || $_POST['train'] == 'agility')
	  {
	    $repeat = ($_POST["rep"] * .4);
	  }
      }
    elseif ($player->race == 'Hobbit' && ($_POST['train'] == 'wytrz' || $_POST['train'] == 'agility'))
      {
	$repeat = ($_POST["rep"] * .2);
      }
    elseif (($player -> race == 'Hobbit' || $player -> race == 'Gnom') && ($_POST["train"] == 'szyb' || $_POST['train'] == 'strength')) 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> clas == 'Wojownik' && ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom')) 
    {
      $repeat = ($_POST["rep"] * .4);
    }
    elseif ($player -> clas == 'Mag' && ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom')) 
    {
      $repeat = ($_POST["rep"] * .2);
    }
    elseif (($player -> clas == 'Rzemieślnik' || $player -> clas == 'Barbarzyńca' || $player -> clas == 'Złodziej') && ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom')) 
    {
      $repeat = ($_POST["rep"] * .3);
    }
    if ($player -> race == 'Gnom' && $_POST["train"]) 
    {
        if ($_POST['train'] == 'wisdom') 
        {
            $repeat = ($_POST["rep"] * .4);
        }
    }
    $gain = ($_POST["rep"] * .060);
    $repeat = round($repeat, 1);
    $blnLess = FALSE;
    switch ($_POST['train'])
      {
      case 'strength':
	$cecha = T_STR;
	$fltStat2 = $player->oldstats[1];
	break;
      case 'agility':
	$cecha = T_AGI;
	$fltStat2 = $player->oldstats[0];
	break;
      case 'inteli':
	$cecha = T_INT;
	$fltStat2 = $player->oldstats[2];
	if ($player->location == 'Ardulith')
	  {
	    $blnLess = TRUE;
	  }
	break;
      case 'szyb':
	$cecha = T_SPEED;
	$fltStat2 = $player->oldstats[4];
	break;
      case 'wytrz':
	$cecha = T_CON;
	$fltStat2 = $player->oldstats[5];
	break;
      case 'wisdom':
	$cecha = T_WIS;
	$fltStat2 = $player->oldstats[3];
	if ($player->location == 'Ardulith')
	  {
	    $blnLess = TRUE;
	  }
	break;
      default:
	break;
    }
    $intCost2 = 0;
    for ($i = 0; $i < $_POST['rep']; $i++)
      {
	if ($blnLess)
	  {
	    $intCost2 += round(($fltStat2 / $fltStat) - (($fltStat2 / $fltStat) / 10), 0);
	  }
	else
	  {
	    $intCost2 += round($fltStat2 / $fltStat, 0);
	  }
	$fltStat2 += 0.06;
      }
    if ($repeat > $player -> energy) 
      {
        message('error', NO_ENERGY);
      }
    elseif ($player -> credits < $intCost2) 
      {
        message('error', NO_MONEY.$intCost2.GOLD_COINS);
      }
    else
      {
	if ($_POST['train'] == 'wytrz') 
	  {
	    $intCondition = floor($player->oldstats[5] + $gain);
	    if ($intCondition > $player->oldstats[5])
	      {
		$intGain = $intCondition - floor($player->oldstats[5]);
		$db -> Execute("UPDATE `players` SET `max_hp`=`max_hp`+".$intGain." WHERE `id`=".$player -> id);
	      } 
	  }
	$db -> Execute("UPDATE `players` SET `energy`=`energy`-".$repeat.", ".$_POST['train']."=".$_POST['train']."+".$gain.", `credits`=`credits`-".$intCost2." WHERE `id`=".$player -> id);
	message('success', YOU_GAIN.$gain." ".$cecha.YOU_PAY.$intCost2.GOLD_COINS);
      }
    $_GET['action'] = '';
}

/**
* Initialization ov variables
*/
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}
if (!isset($_POST['rep']))
  {
    $_POST['rep'] = 0;
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
			"Tinfo" => 'Podaj ile razy chcesz trenować daną cechę.',
			"Plrace" => $player->race,
			"Plclass" => $player->clas,
			"Tcosts" => $player->oldstats[1].', '.$player->oldstats[0].', '.$player->oldstats[2].', '.$player->oldstats[4].', '.$player->oldstats[5].', '.$player->oldstats[3].', '.$fltStat.', '.$intLess,
                        "Action" => $_GET['action'],
			"Rep" => $_POST['rep']));
$smarty -> display ('train.tpl');

require_once("includes/foot.php");
?>
