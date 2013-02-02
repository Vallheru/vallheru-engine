<?php
/**
 *   File functions:
 *   Mines in moutains
 *
 *   @name                 : kopalnia.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 03.02.2013
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

$title = "Kopalnia";
require_once("includes/head.php");

if ($player -> location != 'Góry') 
{
    error("Nie znajdujesz się w górach.");
}

/**
 * Dig for minerals
 */
if (isset($_GET['action']) && $_GET['action'] == 'dig')
{
    if (!isset($_POST['amount'])) 
    {
        error("Zapomnij o tym.");
    }
    checkvalue($_POST['amount']);
    if ($player -> hp <= 0) 
    {
        error("Nie możesz pracować w kopalni, ponieważ jesteś martwy! (<a href=\"gory.php\">Wróć</a>)");
    }
    if ($player -> energy < $_POST['amount']) 
    {
        error("Nie masz tyle energii! (<a href=\"gory.php\">Wróć</a>)");
    }

    /**
     * Count bonus to ability
     */
    $player->curskills(array('mining'), TRUE, TRUE);

    $fltGainability = 0;
    $arrMinerals = array(0, 0);
    $arrGold = array(0, 0);
    $strInfo = '';
    $intExp = 0;

    for ($i = 1; $i <= $_POST['amount']; $i++)
    {
        $intRoll = rand(1, 10);
	if ($intRoll > 4 && $intRoll < 10)
	  {
	    $intBonus = 1 + (($player->skills['mining'][1] + $player->stats['strength'][2]) / 20);
	    $intBonus += $player->checkbonus('mining');
	  }
	switch ($intRoll)
	  {
	  case 5:
	    $intAmount = ceil((rand(1,20) * 1/8) * ($intBonus + $player->checkbonus('crystal')));
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrMinerals[0] = $arrMinerals[0] + $intAmount;
	    $intExp += (3 * $intAmount);
	    break;
	  case 6:
	  case 7:
	    $intAmount = ceil((rand(1,20) * 1/5) * ($intBonus + $player->checkbonus('adamantium')));
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrMinerals[1] = $arrMinerals[1] + $intAmount;
	    $intExp += (4 * $intAmount);
	    break;
	  case 8:
	    $intAmount = ceil((rand(1,20) * 1/3) * $intBonus);
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrGold[1] = $arrGold[1] + $intAmount;
	    break;
	  case 9:
	    $intAmount = ceil(rand(50,200) * $intBonus);
            if ($intAmount < 1)
            {
                $intAmount = 1;
            }
            $arrGold[0] = $arrGold[0] + $intAmount;
	    break;
	  default:
	    break;
	  }
	if ($intRoll == 10)
	  {
            $intRoll2 = rand(1, 100);
            if ($intRoll2 > $player->stats['speed'][2]) 
            {
                $strInfo = "<br /><br />Nagle poczułeś, jak całe wyrobisko powoli zaczyna się rozpadać. Najszybciej jak potrafisz uciekasz w kierunku wyjścia. Niestety, tym razem żywioł okazał się szybszy od ciebie. Potężna lawina kamieni spadła na ciebie,";
		$player->dying();
		if ($player->hp == 1)
		  {
		    $strInfo .= ' na szczęście w tym wypadku, udało ci się oszukać przeznaczenie.';
		  }
		else
		  {
		    $strInfo .= ' zabijając na miejscu.';
		  }
            } 
                else
            {
                $strInfo = "<br /><br />Nagle poczułeś, jak całe wyrobisko powoli zaczyna się rozpadać. Najszybciej jak potrafisz uciekasz w kierunku wyjścia. W ostatnim momencie udało ci się wybiec z rejonu zagrożenia, poczułeś jedynie na plecach podmuch walących się ton skał.";
            }
	    $player->clearbless(array('speed'));
            break;
        }
    }
    $player->clearbless(array('strength'));

    $intMinsum = array_sum($arrMinerals);
    $intGoldsum = array_sum($arrGold);
    $i --;
    if (!$i)
    {
        $i = 1;
    }
    
    if ($intMinsum)
    {
        $objMinerals = $db -> Execute("SELECT `adamantium`, `crystal`, `owner` FROM `minerals` WHERE `owner`=".$player -> id);
        if ($objMinerals -> fields['owner'])
        {
            $db -> Execute("UPDATE `minerals` SET `crystal`=`crystal`+".$arrMinerals[0].", `adamantium`=`adamantium`+".$arrMinerals[1]." WHERE `owner`=".$player -> id);
        }
        else
        {
            $db -> Execute("INSERT INTO `minerals` (`owner`, `crystal`, `adamantium`) VALUES(".$player -> id.", ".$arrMinerals[0].", ".$arrMinerals[1].")");
        }
        $objMinerals -> Close();
    }
    $strFind = "Wybrałeś się na poszukiwanie minerałów ".$i." razy.";
    if ($intGoldsum || $intMinsum)
    {
        $strFind = $strFind."<br /><br />Zdobyłeś:<br /><br />";
        if ($arrMinerals[0])
        {
            $strFind = $strFind.$arrMinerals[0]." kryształów<br />";
        }
        if ($arrMinerals[1])
        {
            $strFind = $strFind.$arrMinerals[1]." brył adamantium<br />";
        }
        if ($arrGold[1])
        {
            $strFind = $strFind.$arrGold[1]." sztuk mithrilu<br />";
        }
        if ($arrGold[0])
        {
            $strFind = $strFind."nieco diamentów wartych ".$arrGold[0]." sztuk złota<br />";
        }
	if ($player->clas = 'Rzemieślnik')
	  {
	    $intExp = $intExp * 2;
	  }
        $strFind = $strFind." oraz ".$intExp." punktów doświadczenia.<br />";
    }
    if (!$intGoldsum && !$intMinsum && $strInfo == '')
    {
        $strFind = $strFind."<br /><br />Niestety nic nie znalazłeś.";
    }
    $strFind = $strFind.$strInfo;
    $player->checkexp(array('strength' => ($intExp / 3),
			    'speed' => ($intExp / 3)), $player->id, 'stats');
    $player->checkexp(array('mining' => ($intExp / 3)), $player->id, 'skills');
    $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$arrGold[0].", `platinum`=`platinum`+".$arrGold[1].", `hp`=".$player -> hp.", `energy`=`energy`-".$i."  WHERE `id`=".$player -> id);
    $smarty -> assign("Youfind", $strFind);
    $player->energy -= $i;
    if ($player->hp <= 0)
      {
	$smarty -> assign(array("Youdead" => "Jesteś martwy",
				"Backto" => "Powrót do ".$city1b,
				"Stayhere" => "Pozostań na miejscu"));
      }
    $strMinesinfo = "Czy chcesz wyruszyć na poszukiwanie minerałów ponownie?";
}

/**
* Initialization of variables
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
    $strMinesinfo = "Witaj w kopalniach w górach Kazad-nar, czy chcesz wyruszyć na poszukiwanie minerałów? Każde poszukiwanie zabiera 1 punkt energii.";
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'],
                        "Ano" => "Nie",
                        "Minesinfo" => $strMinesinfo,
                        "Asearch" => "Szukaj",
                        "Tminerals" => "minerałów",
                        "Tamount" => "razy.",
			"Curen" => $player->energy,
                        "Health" => $player -> hp));
$smarty -> display ('kopalnia.tpl');

require_once("includes/foot.php"); 
?>
