<?php
/**
 *   File functions:
 *   Player statistics and general informations about account
 *
 *   @name                 : stats.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : mori <ziniquel@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 05.02.2013
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

$title = "Statystyki";
require_once("includes/head.php");

/**
* Select gender
*/
if (isset ($_GET['action']) && $_GET['action'] == 'gender') 
{
    if ($player -> gender) 
      {
	error("Już masz wybraną płeć");
      }
    if ((!isset($_POST['gender']))  || ($_POST['gender'] != 'M') && ($_POST['gender'] != 'F'))
      {
        message('error', "Wybierz płeć.");
      }
    else
      {
	$db -> Execute("UPDATE `players` SET `gender`='".$_POST['gender']."' WHERE `id`=".$player -> id);
	message('success', "Ustawiłeś płeć postaci.");
	$player->gender = $_POST['gender'];
      }
}

/**
* Assign variables to template
*/
$smarty -> assign("Avatar", '');

$plik = 'avatars/'.$player -> avatar;
if (is_file($plik)) 
{
    require_once('includes/avatars.php');
    $arrImage = scaleavatar($plik);
    $smarty->assign(array('Avatar' => $plik,
			  'Awidth' => $arrImage[0],
			  'Aheight' => $arrImage[1]));
}

if ($player -> ap > 0) 
{
    $smarty -> assign ("Ap", $player -> ap." (<a href=\"ap.php\">Użyj</a>)<br />");
} 
    else 
{
    $smarty -> assign ("Ap", $player -> ap."<br />");
}
if ($player -> race == '') 
{
    $smarty -> assign ("Race", "(<a href=\"rasa.php\">Wybierz</a>)<br />");
} 
    else 
{
    $smarty -> assign ("Race", $player -> race."<br />");
}
if ($player -> clas == '') 
{
    $smarty -> assign ("Clas", "(<a href=\"klasa.php\">Wybierz</a>)<br />");
} 
    else 
{
    $smarty -> assign ("Clas", $player -> clas."<br />");
}
if ($player -> gender == '') 
{
    $gender = '';
} 
    else 
{
    if ($player -> gender == 'M') 
    {
        $gender = "Mężczyzna";
    } 
        else 
    {
        $gender = "Kobieta";
    }
}
$smarty -> assign ("Gender", $gender);
if ($player -> deity == '') 
{
    $smarty -> assign ("Deity", "(<a href=\"deity.php\">Wybierz</a>)<br />");
} 
    else 
{
    $smarty -> assign ("Deity", $player -> deity." (<a href=\"deity.php?step=change\">Zmień</a>)<br />");
}

$rt = ($player -> wins + $player -> losses);

/**
 * Select player rank
 */
require_once('includes/ranks.php');
$strRank = selectrank($player -> rank, $player -> gender);

/**
 * Bonus from bless
 */
$objBless = $db -> Execute("SELECT `bless`, `blessval`, `mpoints` FROM `players` WHERE `id`=".$player -> id);
if (!empty($objBless -> fields['bless']))
  {
    $arrBless = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'condition', 'smith', 'alchemy', 'carpentry', 'attack', 'shoot', 'dodge', 'magic', 'breeding', 'mining', 'lumberjack', 'herbalism', 'jewellry', 'perception', 'thievery', 'smelting');
    $intKey = array_search($objBless -> fields['bless'], $arrBless);
    $arrPrays = array("Zręczności", "Siły", "Inteligencji", "Siły Woli", "Szybkości", "Kondycji", "Kowalstwa", "Alchemii", "Stolarstwa", "Walki Bronią", "Strzelectwa", "Uników", "Rzucania Czarów", "Hodowli", "Górnictwa", "Drwalnictwa", "Zielarstwa", "Jubilerstwa", "Spostrzegawczości", "Złodziejstwa", 'Hutnictwa');
    $smarty -> assign(array("Blessfor" => "Błogosławieństwo do ",
                            "Pray" => "<br />".$arrPrays[$intKey],
                            "Blessval" => "(".$objBless -> fields['blessval'].")<br />"));
  }
 else
   {
     $smarty -> assign(array("Blessfor" => "",
			     "Pray" => "",
			     "Blessval" => ""));
   }
$objBless -> Close();
/**
 * Antidote info
 */
$strEffect = 'Antidotum:';
switch ($player->antidote)
  {
  case 'I':
    $strAntidote = 'na truciznę z Illani';
    break;
  case 'D':
    $strAntidote = 'na truciznę z Dynallca';
    break;
  case 'N':
    $strAntidote = 'na truciznę z Nutari';
    break;
  case 'R':
    $strEffect = 'Wpływ:';
    $strAntidote = 'Wypita mikstura Oszukania śmierci';
    break;
  default:
    $strAntidote = '';
    $strEffect = '';
    break;
  }
$smarty->assign(array('Mpoints' => $objBless->fields['mpoints'],
		      'Antidote' => $strAntidote,
		      'Effect' => $strEffect));

//Stats info
$arrCurstats2 = array();
$i = 0;
$arrSnames = array('strength', 'agility', 'condition', 'speed', 'inteli', 'wisdom');
foreach ($player->stats as $arrStat)
{
  if ($player->oldstats[$arrSnames[$i]][2] < $player->oldstats[$arrSnames[$i]][1])
  {
    $strNeedexp = ' ('.round(($player->oldstats[$arrSnames[$i]][3] / ($player->oldstats[$arrSnames[$i]][2] * 500) * 100), 3).'% dośw)';
  }
  else
    {
      $strNeedexp = '';
    }
  if ($arrStat[2] > $player->oldstats[$arrSnames[$i]][2])
    {
      $arrCurstats2[] = '<b>'.$player->oldstats[$arrSnames[$i]][0].':</b> '.$arrStat[2].' <span style="color: green;">(+'.($arrStat[2] - $player->oldstats[$arrSnames[$i]][2]).')</span>'.$strNeedexp;
    }
  elseif ($arrStat[2] < $player->oldstats[$arrSnames[$i]][2])
    {
      $arrCurstats2[] = '<b>'.$player->oldstats[$arrSnames[$i]][0].':</b> '.$arrStat[2].' <span style="color: red;">('.($arrStat[2] - $player->oldstats[$arrSnames[$i]][2]).')</span>'.$strNeedexp;
    }
  else
    {
      $arrCurstats2[] = '<b>'.$player->oldstats[$arrSnames[$i]][0].':</b> '.$arrStat[2].$strNeedexp;
    }
    $i++;
}

/**
 * Name of player location
 */
$strLocation = $player -> location;
if ($player -> location == 'Altara')
{
    $strLocation = $city1;
}
if ($player -> location == 'Ardulith')
{
    $strLocation = $city2;
}

$maxmana = floor($player->stats['inteli'][2] + $player->stats['wisdom'][2]);
if ($player->clas == 'Mag')
  {
    $maxmana = $maxmana * 2;
  }
$maxmana += floor((($player->equip[8][2] / 100) * $maxmana));
if ($player->mana < $maxmana) 
{
    $smarty -> assign ("Rest", "[<a href=\"rest.php\">Odpocznij</a>]<br />");
} 
    else 
{
    $smarty -> assign ("Rest", "<br />");
}

//Skills info
$arrBskills = $player->skills;
$arrSkills = array("smith", "shoot", "alchemy", "dodge", "carpentry", "magic", "attack", "leadership", "breeding", "mining", "lumberjack", "herbalism", "jewellry", "smelting", "thievery", "perception");
$player->curskills(array("smith", "alchemy", "carpentry", "breeding", "mining", "lumberjack", "herbalism", "jewellry", "smelting"), FALSE, TRUE);
$player->curskills(array("shoot", "dodge", "magic", "attack", "leadership", "perception"), FALSE, FALSE);
$arrStable = array();
$i = -1;
foreach ($arrBskills as $arrSkill)
{
  $i ++;
  if ($arrSkill[0] == "Złodziejstwo" && $player->clas != "Złodziej")
    {
      continue;
    }
  if ($arrSkill[0] == '')
    {
      continue;
    }
  if ($arrSkill[1] < 100 && $arrSkill[1] > 0)
    {
      $strNeedexp = ' ('.round(($arrSkill[2] / ($arrSkill[1] * 100) * 100), 3).'% dośw)';
    }
  else
    {
      $strNeedexp = '';
    }
  if ($player->skills[$arrSkills[$i]][1] > $arrSkill[1])
    {
      $arrStable[] = '<b>'.$arrSkill[0].':</b> '.$player->skills[$arrSkills[$i]][1].' <span style="color: green;">(+'.($player->skills[$arrSkills[$i]][1] - $arrSkill[1]).')</span>'.$strNeedexp;
    }
  elseif ($player->skills[$arrSkills[$i]][1] < $arrSkill[1])
    {
      $arrStable[] = '<b>'.$arrSkill[0].':</b> '.$player->skills[$arrSkills[$i]][1].' <span style="color: red;">(-'.($player->skills[$arrSkills[$i]][1] - $arrSkill[1]).')</span>'.$strNeedexp;
    }
  else
    {
      $arrStable[] = '<b>'.$arrSkill[0].':</b> '.$arrSkill[1].$strNeedexp;
    }
}

//Bonuses info
$arrBid = array();
foreach ($player->bonuses as $arrBonus)
{
  $arrBid[] = $arrBonus[0];
}
if (count($arrBid) > 1)
  {
    $arrBonuses = $db->GetAll("SELECT `id`, `name` FROM `bonuses` WHERE `id` IN (".implode(', ', $arrBid).")");
  }
elseif (count($arrBid) == 1)
   {
     $arrBonuses = $db->GetAll("SELECT `id`, `name` FROM `bonuses` WHERE `id`=".$arrBid[0]);
   }
 else
   {
     $arrBonuses = array();
   }
$arrBtable = array();
foreach ($arrBonuses as $arrBonus)
{
  foreach ($player->bonuses as $arrBonus2)
    {
      if ($arrBonus2[0] == $arrBonus['id'])
	{
	  $arrBtable[] = '<b>'.$arrBonus['name'].':</b> '.($arrBonus2[1] * $arrBonus2[3]).'%';
	  break;
	}
    }
}
   
$smarty -> assign(array("Curstats" => $arrCurstats2,
			"Stable" => $arrStable,
			"Btable" => $arrBtable,
                        "Mana" =>  $player -> mana."/".$maxmana, 
                        "Location" => $strLocation."<br />", 
                        "Age" => $player -> age."<br />", 
                        "Logins" => $player -> logins."<br />", 
                        "Ip" => $player -> ip."<br />", 
                        "Email" => $_SESSION['email']."<br />", 
                        "PW" => $player -> pw."<br />",
			"Energy" => $player->energy."/".$player->max_energy."/".($player->max_energy * 21)."<br />",
                        "Total" => $player -> wins."/".$player -> losses."/".$rt."<br />", 
                        "Lastkilled" => $player -> lastkilled."<br />", 
                        "Lastkilledby" => $player -> lastkilledby,
			"Reputation" => $player->reputation,
                        "Rank" => $strRank."<br />",
			"Tnewbie" => "Ochrona młodego gracza",
			"Tdays" => "dni",
			"Tday" => "dzień",
			"Newbie" => $player->newbie,
			"Adisable" => "Wyłącz ochronę",
			"Tmissions" => 'Wykonanych zadań',
                        "Statsinfo" => "Witaj w swoich statystykach. Możesz tutaj zobaczyć informacje na temat swojej postaci w grze.",
                        "Tstats" => "Statystyki w grze",
                        "Tinfo" => "Informacje",
                        "Trank" => "Ranga",
                        "Tloc" => "Lokacja",
                        "Tlogins" => "Logowań",
                        "Tage" => "Wiek",
                        "Tip" => "IP",
                        "Temail" => "Email",
                        "Tclan" => "Klan",
                        "Tability" => "Umiejętności",
			"Tbonuses" => "Premie",
                        "Tap" => "AP",
                        "Trace" => "Rasa",
                        "Tclass" => "Klasa",
                        "Tdeity" => "Wyznanie",
                        "Tgender" => "Płeć",
                        "Tmana" => "Punkty Magii",
                        "Tpw" => "Punkty Wiary",
                        "Tfights" => "Wyniki walk",
                        "Tlast" => "Ostatnio zabity",
                        "Tlast2" => "Ostatnio zabity przez",
			"Treputation" => "Reputacja",
			"Tenergy" => "Energia",
			"Genderm" => "Mężczyzna",
			"Genderf" => "Kobieta",
			"Aselect" => "Wybierz"));

if (!empty($player-> gg)) 
{
    $smarty -> assign ("GG", "<b>Komunikator </b> ".$player -> gg."<br />");
} 
    else 
{
    $smarty -> assign ("GG", "");
}
$tribe = $db -> Execute("SELECT `name` FROM `tribes` WHERE id=".$player -> tribe);
if ($tribe -> fields['name']) 
{
    $smarty -> assign(array("Tribe" => "<a href=\"tribes.php?view=my\">".$tribe -> fields['name']."</a><br />",
                            "Triberank" => "<b>Ranga w klanie:</b> ".$player -> tribe_rank."<br />"));
} 
    else 
{
    $smarty -> assign(array("Tribe" => "brak<br />", 
                            "Triberank" => ""));
}
$tribe -> Close();

/**
 * Disable newbie protection
 */
if (isset($_GET['action']) && $_GET['action'] == 'newbie')
  {
    if ($player->newbie == 0)
      {
	error("Nie masz na sobie ochrony dla nowych graczy!");
      }
    $smarty->assign(array("Newbieinfo" => "Naprawdę chcesz wyłączyć ochronę? Możesz wtedy zostać zaatakowany przez innych graczy.",
			  "Ayes" => "Tak",
			  "Ano" => "Nie"));
    if (isset($_GET['disable']))
      {
	$db->Execute("UPDATE `players` SET `newbie`=0 WHERE `id`=".$player->id);
	error("Wyłączyłeś(aś) ochronę nowych graczy.");
      }
  }

/**
* Initialization of variable
*/
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
}

/**
* Assign variable and display page
*/
$smarty -> assign ("Action", $_GET['action']);
$smarty -> display ('stats.tpl');

require_once("includes/foot.php");
?>
