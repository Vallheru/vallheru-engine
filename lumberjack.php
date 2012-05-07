<?php
/**
 *   File functions:
 *   Chop trees
 *
 *   @name                 : lumberjack.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 07.05.2012
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

$title = "Wyrąb";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/lumberjack.php");

if ($player -> location != 'Las') 
{
    error (ERROR);
}

$objLumberjack = $db -> Execute("SELECT `level` FROM `lumberjack` WHERE `owner`=".$player -> id);
if (!$objLumberjack -> fields['level'])
  {
    error("Nie posiadasz licencji na wyrąb lasu. Możesz ją zakupić w Tartaku w ".$city2.". (<a href=las.php>Wróć do lasu</a>)");
  }
$arrOptions = array("losowego", "sosnowego", "z leszczyny", "cisowego", "z wiązu");
$arrOptions = array_slice($arrOptions, 0, $objLumberjack->fields['level'] + 1);

/**
 * Chop down trees
 */
if (isset ($_GET['action']) && $_GET['action'] == 'chop') 
{
    if (!isset($_POST['amount']))
    {
        error(ERROR);
    }
    checkvalue($_POST['amount']);
    if ($player-> energy < $_POST['amount']) 
    {
        error(NO_ENERGY);
    }
    if ($player -> hp <= 0) 
    {
        error(YOU_DEAD);
    }
    if (!array_key_exists($_POST['type'], $arrOptions))
      {
	error(ERROR);
      }
    /**
     * Count bonus to ability
     */
    $player->curstats(array(), TRUE);
    $player->curskills(array('lumberjack'), TRUE, TRUE);
    
    $intAmountgold = 0;
    $arrKey = array(1, 2, 3, 4);
    $arrAmount = array(0, 0, 0, 0);
    $intLosthp = 0;
    $strMessage = '';
    $intAmountability = 0;
    $strInfo = '';
    $intExp = 0;
    for ($i = 1; $i <= $_POST['amount']; $i++)
    {
        $intRoll = rand(1,8);
	switch ($intRoll)
	  {
	  case 5:
	  case 6:
	    if ($_POST['type'] == 0)
	      {
		$intKey = (rand(1, $objLumberjack -> fields['level']) - 1);
	      }
	    else
	      {
		$intKey = $_POST['type'] - 1;
	      }
	    $intBonus = 1 + ($player->lumberjack / 20);
	    if ($intBonus > 30)
	      {
		$intBonus = 30;
	      }
            $intAmount = ceil(((rand(1, 20) * 1 / $arrKey[$intKey]) * $intBonus) - $arrKey[$intKey]);
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrAmount[$intKey] = $arrAmount[$intKey] + $intAmount;
            $intAmountability = $intAmountability + 0.1;
	    $intExp += ($arrKey[$intKey] * 2) * $intAmount;
	    break;
	  case 7:
            $intAmount = rand(1,100);
            $intAmountgold = $intAmountgold + $intAmount;
	    break;
	  case 8:
	    $intRoll2 = rand(1,100);
	    if ($intRoll2 <= 50) 
	      {
		$intLosthp = rand(1,100);
		if ($intLosthp > $player -> hp) 
		  {
                    $intLosthp = $player -> hp;
		  }
                $strInfo = TREE_STOMP.YOU_UNLUCK.$intLosthp.T_HITS;
                $player -> hp = $player -> hp - $intLosthp;
		if ($player->hp == 0 && $player->antidote == 'R')
		  {
		    $intLosthp--;
		    $db->Execute("UPDATE `players` SET `antidote`='' WHERE `id`=".$player->id);
		    $strInfo .= ' Tym razem jednak udało ci się uniknąć śmierci.';
		    $player->hp = 1;
		  }
	      }
	    else
	      {
                $strInfo = TREE_STOMP.YOU_LUCK;
	      }
	    break;
	  default:
	    break;
	  }
	if ($intRoll == 8)
	  {
            break;
	  }
    }
    $objLumberjack -> Close();
    $i --;
    $strMessage = YOU_GO.$i.T_ENERGY2;
    $intTest = array_sum($arrAmount) + $intAmountgold + $intAmountability;
    if (!$intTest)
    {
        $strMessage = $strMessage.NOTHING;
    }
        else
    {
        $strMessage = $strMessage.YOU_FIND;
    }
    if ($arrAmount[0])
    {
        $strMessage = $strMessage.$arrAmount[0].T_PINE;
    }
    if ($arrAmount[1])
    {
        $strMessage = $strMessage.$arrAmount[1].T_HAZEL;
    }
    if ($arrAmount[2])
    {
        $strMessage = $strMessage.$arrAmount[2].T_YEW;
    }
    if ($arrAmount[3])
    {
        $strMessage = $strMessage.$arrAmount[3].T_ELM;
    }
    if ($intAmountgold)
    {
        $strMessage = $strMessage.$intAmountgold.T_GOLD;
    }
    if ($intAmountability)
      {
	if ($player->clas == 'Rzemieślnik')
	  {
	    $intExp = $intExp * 2;
	  }
        $strMessage = $strMessage.$intAmountability.T_ABILITY." oraz ".$intExp." PD.<br />";
      }
    $strMessage = $strMessage.$strInfo;
    $smarty -> assign(array("Message" => $strMessage,
			    "Lselected" => $_POST['type']));
    require_once('includes/checkexp.php');
    checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, 'lumberjack', $intAmountability);
    $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$i.", `credits`=`credits`+".$intAmountgold.", `hp`=`hp`-".$intLosthp." WHERE `id`=".$player -> id);
    $objLumber = $db -> Execute("SELECT `owner` FROM `minerals` WHERE `owner`=".$player -> id);
    if (!$objLumber -> fields['owner'])
    {
        $db -> Execute("INSERT INTO `minerals` (`owner`, `pine`, `hazel`, `yew`, `elm`) VALUES(".$player -> id.", ".$arrAmount[0].", ".$arrAmount[1].", ".$arrAmount[2].", ".$arrAmount[3].")") or die($db -> ErrorMsg());
    }
        else
    {
      $db -> Execute("UPDATE `minerals` SET `pine`=`pine`+".$arrAmount[0].", `hazel`=`hazel`+".$arrAmount[1].", `yew`=`yew`+".$arrAmount[2].", `elm`=`elm`+".$arrAmount[3]." WHERE `owner`=".$player -> id);
    }
    $objLumber -> Close();
    $player->energy -= $i;
}

/**
* Initialization of variable
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
    $smarty -> assign("Youwant", YOU_WANT);
}

/**
* Assign variables to template and display page
*/
$smarty -> assign (array("Action" => $_GET['action'],
                         "Aback" => A_BACK,
                         "Health" => $player -> hp,
			 "Achop" => A_CHOP,
			 "Onchop" => ON_CHOP,
			 "Curen" => $player->energy,
			 "Loptions" => $arrOptions,
			 "Tenergy" => T_ENERGY));
$smarty -> display ('lumberjack.tpl');

require_once("includes/foot.php"); 
?>
