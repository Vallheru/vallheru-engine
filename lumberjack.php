<?php
/**
 *   File functions:
 *   Chop trees
 *
 *   @name                 : lumberjack.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 18.02.2013
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

if ($player -> location != 'Las') 
{
    error ('Nie znajdujesz się w lesie.');
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
      error('Zapomnij o tym');
    }
    checkvalue($_POST['amount']);
    if ($player-> energy < $_POST['amount']) 
    {
        error('Nie masz tyle energii.');
    }
    if ($player -> hp <= 0) 
    {
        error("Nie możesz wyrąbywać drewna, ponieważ jesteś martwy!");
    }
    if (!array_key_exists($_POST['type'], $arrOptions))
      {
	error('Zapomnij o tym.');
      }
    /**
     * Count bonus to ability
     */
    $player->curskills(array('lumberjack'), TRUE, TRUE);
    $player->skills['lumberjack'][1] += $player->checkbonus('lumberjack');
    
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
	    $intBonus = 1 + (($player->skills['lumberjack'][1] + $player->stats['strength'][2]) / 20);
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
	    $intExp += ($arrKey[$intKey] * $intAmount);
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
                $strInfo = "Podczas wyrębu przewróciło się na ciebie drzewo. Niestety nie udało Ci się go uniknąć. Spadając zadało Tobie ".$intLosthp." obrażeń.";
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
                $strInfo = "Podczas wyrębu przewróciło się na ciebie drzewo. Na szczęście zdążyłeś uniknąć przygniecenia, jednak przerwało to na moment twoją pracę.";
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
    $strMessage = "Przeznaczyłeś na wyrąb lasu ".$i." energii.<br />";
    $intTest = array_sum($arrAmount) + $intAmountgold + $intAmountability;
    if (!$intTest)
    {
        $strMessage = $strMessage."Niestety nic nie znalazłeś.<br />";
    }
        else
    {
        $strMessage = $strMessage."Zdobyłeś:<br />";
    }
    if ($arrAmount[0])
    {
        $strMessage = $strMessage.$arrAmount[0]." sztuk drewna sosnowego<br />";
    }
    if ($arrAmount[1])
    {
        $strMessage = $strMessage.$arrAmount[1]." sztuk drewna z leszczyny<br />";
    }
    if ($arrAmount[2])
    {
        $strMessage = $strMessage.$arrAmount[2]." sztuk drewna cisowego<br />";
    }
    if ($arrAmount[3])
    {
        $strMessage = $strMessage.$arrAmount[3]." sztuk drewna z wiązu<br />";
    }
    if ($intAmountgold)
    {
        $strMessage = $strMessage.$intAmountgold." sztuk złota<br />";
    }
    if ($intAmountability)
      {
	if ($player->clas == 'Rzemieślnik')
	  {
	    $intExp = $intExp * 2;
	  }
        $strMessage = $strMessage.$intExp." PD.<br />";
      }
    $strMessage = $strMessage.$strInfo;
    $smarty -> assign(array("Message2" => $strMessage,
			    "Lselected" => $_POST['type']));
    $player->checkexp(array('lumberjack' => ($intExp / 2)), $player->id, 'skills');
    $player->checkexp(array('strength' => ($intExp / 2)), $player->id, 'stats');
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
    $smarty -> assign("Youwant", "Czy chcesz wyruszyć na poszukiwanie drewna?");
}

/**
* Assign variables to template and display page
*/
$smarty -> assign (array("Action" => $_GET['action'],
                         "Aback" => "Wróć",
                         "Health" => $player -> hp,
			 "Achop" => "Przeznacz",
			 "Onchop" => "na wyrąb drewna",
			 "Curen" => floor($player->energy),
			 "Loptions" => $arrOptions,
			 "Tenergy" => "energii."));
$smarty -> display ('lumberjack.tpl');

require_once("includes/foot.php"); 
?>
