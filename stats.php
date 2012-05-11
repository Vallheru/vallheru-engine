<?php
/**
 *   File functions:
 *   Player statistics and general informations about account
 *
 *   @name                 : stats.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : mori <ziniquel@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 11.05.2012
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
* Get the localization for game
*/
require_once("languages/".$player -> lang."/stats.php");

/**
* Assign variables to template
*/
$smarty -> assign(array("Avatar" => '', 
                        "Crime" => '',
			"Thievery" => ''));

$plik = 'avatars/'.$player -> avatar;
if (is_file($plik)) 
{
    require_once('includes/avatars.php');
    $arrImage = scaleavatar($plik);
    $smarty -> assign("Avatar", "<center><img src=\"".$plik."\" width=\"".$arrImage[0]."\" height=\"".$arrImage[1]."\" alt=\"".$player -> user."\" title=\"".$player -> user."\" /></center>");
}

if ($player -> ap > 0) 
{
    $smarty -> assign ("Ap", $player -> ap." (<a href=\"ap.php\">".A_USE."</a>)<br />");
} 
    else 
{
    $smarty -> assign ("Ap", $player -> ap."<br />");
}
if ($player -> race == '') 
{
    $smarty -> assign ("Race", "(<a href=\"rasa.php\">".A_SELECT."</a>)<br />");
} 
    else 
{
    $smarty -> assign ("Race", $player -> race."<br />");
}
if ($player -> clas == '') 
{
    $smarty -> assign ("Clas", "(<a href=\"klasa.php\">".A_SELECT."</a>)<br />");
} 
    else 
{
    $smarty -> assign ("Clas", $player -> clas."<br />");
}
if ($player -> gender == '') 
{
    $smarty -> assign ("Gender", "(<a href=\"stats.php?action=gender\">".A_SELECT."</a>)<br />");
} 
    else 
{
    if ($player -> gender == 'M') 
    {
        $gender = GENDER_M;
    } 
        else 
    {
        $gender = GENDER_F;
    }
    $smarty -> assign ("Gender", $gender."<br />");
}
if ($player -> deity == '') 
{
    $smarty -> assign ("Deity", "(<a href=\"deity.php\">".A_SELECT."</a>)<br />");
} 
    else 
{
    $smarty -> assign ("Deity", $player -> deity." (<a href=\"deity.php?step=change\">".A_CHANGE."</a>)<br />");
}

$rt = ($player -> wins + $player -> losses);

/**
 * Select player rank
 */
require_once('includes/ranks.php');
$strRank = selectrank($player -> rank, $player -> gender);

/**
 * Bonuses from equipment to stats
 */
$player->curstats(array());
$arrCurstats = array($player->agility, $player->strength, $player->inteli, $player->wisdom, $player->speed, $player->cond);

/**
 * Bonus from bless
 */
$objBless = $db -> Execute("SELECT `bless`, `blessval`, `mpoints` FROM `players` WHERE `id`=".$player -> id);
if (!empty($objBless -> fields['bless']))
{
    $arrBless = array('agility', 'strength', 'inteli', 'wisdom', 'speed', 'cond', 'smith', 'alchemy', 'fletcher', 'weapon', 'shoot', 'dodge', 'cast', 'breeding', 'mining', 'lumberjack', 'herbalist', 'jeweller', 'perception', 'thievery', 'metallurgy');
    $intKey = array_search($objBless -> fields['bless'], $arrBless);
    $arrPrays = array(AGI, STR, INTELI, WIS, SPE, CON, SMI, ALC, FLE, WEA, SHO, DOD, CAS, BRE, MINI, LUMBER, HERBS, JEWEL, "Spostrzegawczości", "Złodziejstwa", 'Hutnictwa');
    $smarty -> assign(array("Blessfor" => BLESS_FOR,
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

$arrCurstats2 = array();
$i = 0;
foreach ($arrCurstats as $fltStats)
{
    if ($fltStats != $player->oldstats[$i])
    {
        $arrCurstats2[$i] = "(".$fltStats.")<br />";
    }
        else
    {
        $arrCurstats2[$i] = "<br />";
    }
    $i++;
}
$arrStatstext = array(T_AGI, T_STR, T_INT, T_WIS, T_SPEED, T_CON);

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

$cape = $db -> Execute("SELECT `power` FROM `equipment` WHERE `owner`=".$player -> id." AND `type`='C' AND `status`='E'");
$maxmana = ($arrCurstats[2] + $arrCurstats[3]);
$maxmana = (int)($maxmana + (($cape -> fields['power'] / 100) * $maxmana));
$cape -> Close();
if ($player->mana < $maxmana) 
{
    $smarty -> assign ("Rest", "[<a href=\"rest.php\">".A_REST."</a>]<br />");
} 
    else 
{
    $smarty -> assign ("Rest", "<br />");
}
   
$smarty -> assign(array("Stats" => $player->oldstats,
                        "Curstats" => $arrCurstats2,
                        "Tstats2" => $arrStatstext,
                        "Mana" =>  $player -> mana."/".$maxmana, 
                        "Location" => $strLocation."<br />", 
                        "Age" => $player -> age."<br />", 
                        "Logins" => $player -> logins."<br />", 
                        "Ip" => $player -> ip."<br />", 
                        "Email" => $_SESSION['email']."<br />", 
                        "Smith" => $player -> smith."<br />", 
                        "Alchemy" => $player -> alchemy."<br />", 
                        "Fletcher" => $player -> fletcher."<br />", 
                        "Attack" => $player -> attack."<br />", 
                        "Shoot" => $player -> shoot."<br />", 
                        "Miss" => $player -> miss."<br />", 
                        "Magic" => $player -> magic."<br />",
                        "PW" => $player -> pw."<br />",
			"Energy" => $player->energy."/".$player->max_energy."/".($player->max_energy * 21)."<br />",
                        "Total" => $player -> wins."/".$player -> losses."/".$rt."<br />", 
                        "Lastkilled" => $player -> lastkilled."<br />", 
                        "Lastkilledby" => $player -> lastkilledby,
                        "Leadership" => $player -> leadership."<br />",
                        "Rank" => $strRank."<br />",
                        "Breeding" => $player -> breeding."<br />",
                        "Mining" => $player -> mining."<br />",
                        "Lumberjack" => $player -> lumberjack."<br />",
                        "Herbalist" => $player -> herbalist."<br />",
                        "Jeweller" => $player -> jeweller."<br />",
			"Perception" => $player->perception."<br />",
			"Metallurgy" => $player->metallurgy."<br />",
			"Tnewbie" => "Ochrona młodego gracza",
			"Tdays" => "dni",
			"Tday" => "dzień",
			"Newbie" => $player->newbie,
			"Adisable" => "Wyłącz ochronę",
			"Tmissions" => 'Wykonanych zadań',
                        "Statsinfo" => STATS_INFO,
                        "Tstats" => T_STATS,
                        "Tinfo" => T_INFO,
                        "Trank" => T_RANK,
                        "Tloc" => T_LOC,
                        "Tlogins" => T_LOGINS,
                        "Tage" => T_AGE,
                        "Tip" => T_IP,
                        "Temail" => T_EMAIL,
                        "Tclan" => T_CLAN,
                        "Tability" => T_ABILITY,
                        "Tsmith" => T_SMITH,
                        "Talchemy" => T_ALCHEMY,
                        "Tlumber" => T_LUMBER,
                        "Tfight" => T_FIGHT,
                        "Tshoot" => T_SHOOT,
                        "Tdodge" => T_DODGE,
                        "Tcast" => T_CAST,
                        "Tleader" => T_LEADER,
                        "Tap" => T_AP,
                        "Trace" => T_RACE,
                        "Tclass" => T_CLASS2,
                        "Tdeity" => T_DEITY,
                        "Tgender" => T_GENDER,
                        "Tmana" => T_MANA,
                        "Tpw" => T_PW,
                        "Tfights" => T_FIGHTS,
                        "Tlast" => T_LAST,
                        "Tlast2" => T_LAST2,
                        "Tbreeding" => T_BREEDING,
                        "Tmining" => T_MINING,
                        "Tlumberjack" => T_LUMBERJACK,
                        "Therbalist" => T_HERBALIST,
                        "Tjeweller" => T_JEWELLER,
			"Tenergy" => "Energia",
			"Tperception" => "Spostrzegawczość",
			"Tmetallurgy" => "Hutnictwo"));

if ($player->clas == "Złodziej") 
  {
    $smarty->assign(array("Crime" => "<b>".CRIME_T."</b> ".$player->crime."<br />",
			  "Thievery" => "<b>Złodziejstwo:</b> ".$player->thievery."<br />"));
  }

if (!empty($player-> gg)) 
{
    $smarty -> assign ("GG", "<b>".GG_NUM."</b> ".$player -> gg."<br />");
} 
    else 
{
    $smarty -> assign ("GG", "");
}
$tribe = $db -> Execute("SELECT `name` FROM `tribes` WHERE id=".$player -> tribe);
if ($tribe -> fields['name']) 
{
    $smarty -> assign(array("Tribe" => "<a href=\"tribes.php?view=my\">".$tribe -> fields['name']."</a><br />",
                            "Triberank" => "<b>".TRIBE_RANK."</b> ".$player -> tribe_rank."<br />"));
} 
    else 
{
    $smarty -> assign(array("Tribe" => NOTHING."<br />", 
                            "Triberank" => ""));
}
$tribe -> Close();

/**
* Select gender
*/
if (isset ($_GET['action']) && $_GET['action'] == 'gender') 
{
    $smarty -> assign(array("Genderm" => GENDER_M,
                            "Genderf" => GENDER_F,
                            "Aselect" => A_SELECT));
    if ($player -> gender) 
    {
        error (YOU_HAVE);
    }
    if (isset ($_GET['step']) && $_GET['step'] == 'gender') 
      {
	if ((!isset($_POST['gender']))  || ($_POST['gender'] != 'M') && ($_POST['gender'] != 'F'))
	  {
            error(NO_GENDER);
	  }
        $db -> Execute("UPDATE `players` SET `gender`='".$_POST['gender']."' WHERE `id`=".$player -> id);
        error (YOU_SELECT);
      }
}

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
			  "Ayes" => YES,
			  "Ano" => NO));
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
