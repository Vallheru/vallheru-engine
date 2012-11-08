<?php
/**
 *   File functions:
 *   Travel to other locations and magic portal
 *
 *   @name                 : travel.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 08.11.2012
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

$title="Stajnie";
require_once("includes/head.php");
require_once ("includes/funkcje.php");
require_once("includes/turnfight.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/travel.php");

if ($player -> location == 'Lochy') 
{
    error(ERROR);
}

/**
* Assign variables to template
*/
$smarty -> assign(array("Portal" => '', 
    "Maps" => ''));

if (!isset ($_GET['akcja']) && $player -> location == 'Altara' && !isset($_GET['action'])) 
{
    if ($player->maps >= 20  && $player->immunited == 'N') 
    {
        $smarty -> assign(array("Maps" => 0,
                                "Portal1" => PORTAL1,
                                "Ayes" => YES,
                                "Ano" => NO));
    }
    /**
     * Portals to astral plans
     */
    $arrPlans = array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7);
    $objPlans = $db -> Execute("SELECT `name` FROM `astral_plans` WHERE `owner`=".$player -> id." AND `name` LIKE 'M%' AND `location`='V'");
    $arrName = array('');
    $arrLink = array();
    $i = 0;
    while (!$objPlans -> EOF)
    {
        $intNumber = (int)str_replace("M", "", $objPlans -> fields['name']);
        $intNumber = $intNumber - 1;
        $arrName[$i] = $arrPlans[$intNumber];
        $arrLink[$i] = $intNumber;
        $i++;
        $objPlans -> MoveNext();
    }
    $objPlans -> Close();

    $smarty -> assign(array("Altarainfo" => ALTARA_INFO,
                            "Amountains" => A_MOUNTAINS,
                            "Aforest" => A_FOREST,
                            "Tportals" => $arrName,
                            "Tporlink" => $arrLink,
                            "Tporinfo" => T_PORTALS,
                            "Aelfcity" => A_ELFCITY));
}

if (!isset ($_GET['akcja']) && $player -> location == 'Ardulith' && !isset($_GET['action'])) 
{
    $smarty -> assign(array("Altarainfo" => ALTARA_INFO,
                            "Amountains" => A_MOUNTAINS,
                            "Aaltara" => A_ALTARA));
}

if (!isset ($_GET['akcja']) && $player -> location == 'Las' && !isset($_GET['action'])) 
{
    $smarty -> assign(array("Outside" => OUTSIDE,
                            "Aaltara" => A_ALTARA));
}

if (!isset ($_GET['akcja']) && $player -> location == 'Góry' && !isset($_GET['action'])) 
{
    $smarty -> assign(array("Outside" => OUTSIDE,
                            "Aforest" => A_FOREST,
                            "Aaltara" => A_ALTARA));
}

if (isset($_GET['action']))
{
    $smarty -> assign(array("Acaravan" => A_CARAVAN,
                            "Awalk" => A_WALK,
			    "Aportal" => "Użyj magicznego portalu (4000 sztuk złota)",
                            "Aback" => A_BACK));
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'tak' && $player->location == 'Altara' && $player->maps >= 20 && $player->immunited == 'N') 
{
  error('Ta opcja jest na razie wyłączona.');
    $db -> Execute("UPDATE players SET miejsce='Portal' WHERE id=".$player -> id);
    $smarty -> assign(array("Portal" => "Y",
                            "Portal2" => PORTAL2));
}

if (isset ($_GET['akcja']) && $_GET['akcja'] == 'nie' && $player->location == 'Altara' && $player->maps >= 20 && $player->immunited == 'N') 
{
    $smarty -> assign(array("Portal" => "N",
                            "Portal3" => PORTAL3));
}

/**
 * Fight with bandits
 */
function battle($strAddress)
{
  global $db;
  global $player;
  global $enemy;
  global $smarty;

  if (!isset($_SESSION['enemy']))
    {
      require_once('includes/monsters.php');
      $_SESSION['enemy'] = randommonster('Bandyta');
      $enemy = $_SESSION['enemy'];
    }
  else
    {
      $enemy = $_SESSION['enemy'];
    }

  if ($player->energy == 0)
    {
      $db->Execute("UPDATE `players` SET `hp`=0, `miejsce`='Altara', `fight`=0 WHERE `id`=".$player->id);
      error('Zmęczony ruszyłeś zbyt wolno w kierunku napastnika. Ten bez problemu uniknął twojego anemicznego ciosu i wyprowadził swój, wprost na twoją szyję. Była to ostatnia rzecz jaką zapamiętałeś.');
    }

  $db -> Execute("UPDATE `players` SET `fight`=99999 WHERE `id`=".$player->id);
  $arrehp = array ();
  if (!isset ($_POST['action'])) 
    {
      turnfight ($enemy['exp1'], $enemy['gold'], '', $strAddress);
    } 
  else 
    {
      turnfight ($enemy['exp1'], $enemy['gold'], $_POST['action'], $strAddress);
    }
  $myhp = $db -> Execute("SELECT `hp`, `fight` FROM `players` WHERE `id`=".$player -> id);
  if ($myhp -> fields['hp'] == 0 && $myhp -> fields['fight'] == 0) 
    {
      unset($_SESSION['enemy']);
      $player->energy--;
      if ($player->energy < 0)
	{
	  $player->energy = 0;
	}
      $db -> Execute("UPDATE `players` SET `energy`=".$player->energy.", `miejsce`='Altara' WHERE `id`=".$player -> id);
      error (MESSAGE3);
    }
  if ($myhp -> fields['fight'] == 0 && $myhp -> fields['hp'] > 0) 
    {
      unset($_SESSION['enemy']);
      $player->energy--;
      if ($player->energy < 0)
	{
	  $player->energy = 0;
	}
      $db -> Execute("UPDATE `players` SET `energy`=".$player->energy." WHERE `id`=".$player -> id);
      $smarty -> assign ("Message", MESSAGE4);
      $smarty -> display ('error1.tpl');
    }
  $myhp -> Close();
}

/**
 * Travel
 */
if (isset($_GET['akcja']) && in_array($_GET['akcja'], array('gory', 'las', 'city2', 'powrot')))
  {
    if (!isset($_GET['step']))
      {
	error(ERROR);
      }
    if (!in_array($_GET['step'], array('caravan', 'walk', 'magic')))
      {
        error(ERROR);
      }
    switch ($_GET['akcja'])
      {
      case 'gory':
	$arrLocation = array('Altara', 'Ardulith', 'Podróż');
	switch ($_GET['step'])
	  {
	  case 'caravan':
	    if ($player->location != 'Ardulith')
	      {
		$intGoldneed = 1000;
	      }
	    else
	      {
		$intGoldneed = 1200;
	      }
	    break;
	  case 'walk':
	    if ($player->location != 'Ardulith')
	      {
		$intGoldneed = 5;
	      }
	    else
	      {
		$intGoldneed = 6;
	      }
	    break;
	  case 'magic':
	    $intGoldneed = 4000;
	    break;
	  default:
	    error(ERROR);
	    break;
	  }
	$strLocation = 'Góry';
	break;
      case 'las':
	$arrLocation = array('Altara', 'Góry', 'Podróż');
	switch ($_GET['step'])
	  {
	  case 'caravan':
	    if ($player->location != 'Góry')
	      {
		$intGoldneed = 1000;
	      }
	    else
	      {
		$intGoldneed = 1200;
	      }
	    break;
	  case 'walk':
	    if ($player->location != 'Góry')
	      {
		$intGoldneed = 5;
	      }
	    else
	      {
		$intGoldneed = 6;
	      }
	    break;
	  case 'magic':
	    $intGoldneed = 4000;
	    break;
	  default:
	    error(ERROR);
	    break;
	  }
	$strLocation = 'Las';
	break;
      case 'city2':
	$arrLocation = array('Altara', 'Podróż');
	switch ($_GET['step'])
	  {
	  case 'caravan':
	    $intGoldneed = 1000;
	    break;
	  case 'walk':
	    $intGoldneed = 5;
	    break;
	  case 'magic':
	    $intGoldneed  = 4000;
	    break;
	  default:
	    error(ERROR);
	    break;
	  }
	$strLocation = 'Ardulith';
	break;
      case 'powrot':
	$arrLocation = array('Ardulith', 'Góry', 'Las', 'Podróż');
	switch ($_GET['step'])
	  {
	  case 'caravan':
	    $intGoldneed = 1000;
	    break;
	  case 'walk':
	    $intGoldneed = 5;
	    break;
	  case 'magic':
	    $intGoldneed = 4000;
	    break;
	  default:
	    break;
	  }
	$strLocation = 'Altara';
	break;
      default:
	error(ERROR);
	break;
      }
    //We fighting in travel
    $objFight = $db->Execute("SELECT `fight` FROM `players` WHERE `id`=".$player->id);
    if ($objFight->fields['fight'])
      {
	if ($objFight->fields['fight'] != 99999)
	  {
	    error(ERROR);
	  }
	if (!isset($_GET['step2']) || isset($_SESSION['enemy']))
	  {
	    battle("travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']);
	  }
	else
	  {
	    switch ($_GET['step2'])
	      {
	      case 'fight':
		battle("travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']);
		break;
	      case 'pay':
		if ($_GET['step'] == 'caravan')
		  {
		    $intCost = $intGoldneed;
		  }
		else
		  {
		    $intCost = 0;
		  }
		$intRoll = rand(1, 100);
		$intLevel = $player->stats['strength'][2] + $player->stats['agility'][2] + $player->stats['speed'][2] + $player->stats['wisdom'][2] + $player->stats['inteli'][2] + $player->stats['condition'][2];
		if ($intRoll < 6)
		  {
		    $intCost += 5 * $intLevel;
		  }
		elseif ($intRoll > 5 && $intRoll < 26)
		  {
		    $intCost += 15 * $intLevel;
		  }
		elseif ($intRoll > 25 && $intRoll < 76)
		  {
		    $intCost += 25 * $intLevel;
		  }
		elseif ($intRoll > 75 && $intRoll < 96)
		  {
		    $intCost += 50 * $intLevel;
		  }
		else
		  {
		    $intCost = 0;
		  }
		if ($intCost > $player->credits || $intCost == 0)
		  {
		    $smarty -> assign ("Message", "Nie udało Ci się przekonać bandytów złotem. Rozpoczyna się walka!<br />");
		    $smarty -> display ('error1.tpl');
		    battle("travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']);
		  }
		else
		  {
		    $intCost -= $intGoldneed;
		    $db->Execute("UPDATE `players` SET `credits`=`credits`-".$intCost.", `fight`=0 WHERE `id`=".$player->id);
		    $smarty -> assign ("Message", "Płacisz bandytom ".$intCost." sztuk złota i puszczają Ciebie wolno. Dalsza droga przebiega bez niespodzianek<br />");
		    $smarty -> display ('error1.tpl');
		  }
		break;
	      case 'escape':
		$arrbandit = array ();
		for ($i = 0; $i < 4; $i++) 
		  {
		    $roll2 = rand (1,75);
		    $arrbandit[$i] = $roll2;
		  }
		$chance = (rand(1, 100) + $player->stats['speed'][2] + $player->skills['perception'][1]) - ($arrbandit[0] + rand(1, 100));
		$player->clearbless(array('speed'));
		if ($chance > 0) 
		  {
		    $expgain = ceil(($arrbandit[0] + $arrbandit[1] + $arrbandit[2] + $arrbandit[3]) / 100);
		    $smarty -> assign ("Message", "Udało Ci się uciec przed bandytami. Zdobywasz ".$expgain." doświadczenia. Dalsza droga przebiega bez niespodzaniek.<br />");
		    $smarty -> display ('error1.tpl');
		    $player->checkexp(array('speed' => ($expgain / 2)), $player->id, 'stats');
		    $player->checkexp(array('perception' => ($expgain / 2)), $player->id, 'skills');
		    $db -> Execute("UPDATE `players` SET `fight`=0 WHERE `id`=".$player -> id);
		  } 
		else 
		  {
		    $player->checkexp(array('speed' => 1), $player->id, 'stats');
		    $player->checkexp(array('perception' => 1), $player->id, 'skills');
		    $smarty -> assign ("Message", "Nie udało Ci się uciec przed bandytami. Rozpoczyna się walka!<br />");
		    $smarty -> display ('error1.tpl');
		    battle("travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']);
	      }
		break;
	      default:
		battle("travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']);
		break;
	      }
	  }
      }
    $objFight->Close();
    if ($player->hp == 0)
      {
        error(YOU_DEAD);
      }     
    if (!in_array($player->location, $arrLocation))
      {
	error(ERROR);
      }
    if ($_GET['step'] == 'caravan' || $_GET['step'] == 'magic')
      {
	if ($player->credits < $intGoldneed)
	  {
	    error(NO_MONEY);
	  }
	$strCost = 'credits';
      }
    else
      {
	if ($player->energy < $intGoldneed)
	  {
	    error(NO_ENERGY3);
	  }
	$strCost = 'energy';
      }
    if ($player->location != 'Podróż' && $_GET['step'] != 'magic')
      {
	$_SESSION['travel'] = $_GET['akcja'];
	$_SESSION['travel2'] = $_GET['step'];
	$roll = rand(1, 100);
	switch ($_GET['step'])
	  {
	  case 'caravan':
	    $intChance = 20;
	    break;
	  case 'walk':
	    $intChance = 30;
	    break;
	  default:
	    error(ERROR);
	    break;
	  }
	if ($roll > $intChance) 
	  {
	    $smarty -> assign ("Message", MESSAGE1);
	    $smarty -> display ('error1.tpl');
	  }
	else 
	  {
	    $smarty -> assign("Message", "Podróżując do celu wraz z karawaną, nagle zobaczyłeś jak z pobocza drogi wyskakują na Was bandyci. Masz do wyboru:<br /><a href=travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']."&amp;step2=fight>Walczyć</a><br /><a href=travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']."&amp;step2=pay>Zapłacić okup</a><br /><a href=travel.php?akcja=".$_GET['akcja']."&amp;step=".$_GET['step']."&amp;step2=escape>Uciekać</a>");
	    $smarty -> display('error1.tpl');
	    $db -> Execute("UPDATE `players` SET `fight`=99999, `miejsce`='Podróż' WHERE `id`=".$player->id);
	    require_once("includes/foot.php");
	    return;
	  }
      }
    $objFight = $db->Execute("SELECT `fight` FROM `players` WHERE `id`=".$player->id);
    if (!$objFight->fields['fight'])
      {
	$db -> Execute("UPDATE `players` SET `miejsce`='".$strLocation."', ".$strCost."=".$strCost."-".$intGoldneed." WHERE `id`=".$player -> id);
	unset($_SESSION['travel'], $_SESSION['travel2']);
	error (YOU_REACH);
      }
    $objFight->Close();
  }

/**
* Initialization of variable
*/
if (!isset($_GET['akcja'])) 
{
    $_GET['akcja'] = '';
}
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign ( array("Action" => $_GET['akcja'], 
                          "Location" => $player -> location,
                          "Action2" => $_GET['action']));
$smarty -> display('travel.tpl');

require_once("includes/foot.php"); 
?>
