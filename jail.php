<?php
/**
 *   File functions:
 *   Jail
 *
 *   @name                 : jail.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 21.04.2012
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

$title = "Lochy";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/jail.php");

if ($player -> location != 'Altara' && $player -> location != 'Lochy' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

$smarty -> assign("Prisoner", '');

/**
 * Escape from prison
 */
if (isset($_GET['escape']))
  {
    if ($player->location != 'Lochy')
      {
	error('Nie znajdujesz się w lochach.');
      }
    if ($player->clas != 'Złodziej')
      {
	error('Tylko złodziej może próbować uciekać z więzienia.');
      }
    if ($player->crime < 1)
      {
	error('Nie masz wystarczającej ilości punktów kradzieży.');
      }
    $prisoner = $db->Execute("SELECT * FROM `jail` WHERE `prisoner`=".$player->id);
    if (!$prisoner->fields['prisoner'])
      {
	error('Zapomnij o tym.');
      }
    if (!$prisoner->fields['cost'])
      {
	error('Nie możesz próbować ucieczki, ponieważ została nałożona na ciebie kara administracyjna.');
      }
    require_once("includes/checkexp.php");
    $intMax = ceil($prisoner->fields['cost'] / 50);
    $prisoner->Close();
    $roll = rand (1, $intMax);
    if ($roll == 1)
      {
	$chance = 0;
      }
    elseif ($roll == $intMax)
      {
	$chance = 1000000;
      }
    else
      {
	/**
	 * Add bonus from rings
	 */
	$arrEquip = $player -> equipment();
	$player->curstats($arrEquip);
	$player->curskills(array('thievery'));
	
	$intStats = ($player->agility + $player->inteli + $player->thievery + $player->speed);
	/**
	 * Add bonus from tools
	 */
	if ($arrEquip[12][0])
	  {
	    $intStats += (($arrEquip[12][2] / 100) * $intStats);
	  }
	
	$chance = $intStats - $roll;
      }
    if ($player->gender == 'M')
      {
	$strSuffix = 'eś';
      }
    else
      {
	$strSuffix = 'aś';
      }
    if ($chance < 1) 
      {
	$cost = 1000 * $player -> level;
	$expgain = ceil($player -> level / 10);
	checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', 0.01);
	$db -> Execute("UPDATE `players` SET `crime`=`crime`-1 WHERE `id`=".$player -> id);
	$strDate = $db -> DBDate($newdate);
	$db->Execute("UPDATE `jail` SET `duration`=`duration`+7, `cost`=`cost`+".$cost." WHERE `prisoner`=".$player->id);
	if ($arrEquip[12][0])
	  {
	    $db->Execute("DELETE FROM `equipment` WHERE `id`=".$arrEquip[12][0]);
	  }
	$objTool = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
	if ($objTool->fields['id'])
	  {
	    $intRoll = rand(1, 100);
	    if ($intRoll < 50)
	      {
		$db->Execute("DELETE FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
	      }
	  }
	$objTool->Close();
	message('error', 'Próbował'.$strSuffix.' wydostać się z celi. Z początku wszystko szło zgodnie z planem, jednak w pewnym momencie straż zauważyła i pojmała ciebie. Wylądował'.$strSuffix.' w innej celi, tym razem znacznie lepiej strzeżonej. Na dodatek podniesiono kaucję za ciebie oraz przedłużono lochy.');
      }
    else 
      { 
	$expgain = ($player->level * 10);
	$fltThief = ($player->level / 100.0);
	if ($chance == 1000000)
	  {
	    $expgain = 2 * $expgain;
	    $fltThief = 2 * $fltThief;
	  }
	$db->Execute("DELETE FROM `jail` WHERE `prisoner`=".$player->id);
	$db -> Execute("UPDATE `players` SET `crime`=`crime`-1, `miejsce`='Altara' WHERE `id`=".$player->id);
	checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
	if ($arrEquip[12][0])
	  {
	    $arrEquip[12][6] --;
	    if ($arrEquip == 0)
	      {
		$db->Execute("DELETE FROM `equipment` WHERE `id`=".$arrEquip[12][0]);
	      }
	    else
	      {
		$db->Execute("UPDATE `equipment` SET `wt`=`wt`-1 WHERE `id`=".$arrEquip[12][0]);
	      }
	  }
	$player->location = 'Altara';
	message('success', "Wykorzystując nieuwagę straży, otworzył".$strSuffix." drzwi celi i niepostrzeżenie wydostał".$strSuffix." się z lochów do miasta. Zdobył".$strSuffix." ".$fltThief." w umiejętności Złodziejstwo.");
      }
  }

/**
* If player is in city - show prisoners
*/
if ($player -> location == 'Altara' || $player -> location == 'Ardulith') 
{
    $smarty -> display ('jail.tpl');
    $jail = $db -> Execute("SELECT * FROM `jail` ORDER BY `id` ASC");
    $number = $jail -> RecordCount();
    $smarty -> assign(array("Number" => $number,
			    "Jailinfo" => "Tutaj znajdują się lochy ".$gamename.", do których wtrącani są wszyscy obywatele łamiący miejscowe prawo. Panuje w nich chroniczny chłód, który w połączeniu z wilgocią i zapachem pleśni, powoduje u zwiedzających parszywe odczucia. Wrażenie pogłębiają jęki oraz wycie torturowanych więźniów. Legenda głosi, że kiedyś ktoś uśmiechnął się w tym miejscu.<br /><br />Wyrok zapada na określony czas, z możliwością wpłacenia kaucji. Aby wpłacić kaucję za daną osobę wystarczy po prostu kliknąć na kwotę kaucji. Oto lista osób skazanych wraz z opisem przewinienia:"));
    if ($number > 0) 
    {
        $arrid = array();
        $arrname = array();
        $arrdate = array();
        $arrverdict = array();
        $arrduration = array();
        $arrjailid = array();
        $arrcost = array();
        $arrDurationr = array();
        $i = 0;
        while (!$jail -> EOF) 
        {
            $pname = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$jail -> fields['prisoner']);
            $arrid[$i] = $jail -> fields['prisoner'];
            $arrname[$i] = $pname -> fields['user'];
            $arrdate[$i] = $jail -> fields['data'];
            $arrverdict[$i] = $jail -> fields['verdict'];
            $arrduration[$i] = ceil($jail -> fields['duration'] / 7);
            $arrDurationr[$i] = $jail -> fields['duration'];
            $arrjailid[$i] = $jail -> fields['id'];
            $arrcost[$i] = $jail -> fields['cost'];
            $jail -> MoveNext();
            $i = $i + 1;
        }
        $smarty -> assign(array("Id" => $arrid, 
                                "Name" => $arrname, 
                                "Date" => $arrdate, 
                                "Verdict" => $arrverdict, 
                                "Duration" => $arrduration, 
                                "Jailid" => $arrjailid, 
                                "Cost" => $arrcost,
                                "Duration2" => $arrDurationr,
                                "Pname" => P_NAME,
                                "Pid" => P_ID,
                                "Pdate" => P_DATE,
                                "Preason" => P_REASON,
                                "Pduration" => P_DURATION,
                                "Pduration2" => P_DURATION_R,
                                "Pcost" => P_COST,
				"Nocost" => "Brak możliwości wpłacenia kaucji",
                                "Goldcoins" => GOLD_COINS));
    }
    else
      {
	$smarty -> assign("Noprisoners", NO_PRISONERS);
      }
    $jail -> Close();
}

/**
* If player is in jail - show info about it
*/
if ($player -> location == 'Lochy') 
{
    $prisoner = $db -> Execute("SELECT * FROM `jail` WHERE `prisoner`=".$player -> id);
    $intTime = ceil($prisoner -> fields['duration'] / 7);
    if ($prisoner->fields['cost'] > 0)
      {
	$strEscape = 'Spróbuj ucieczki (1 punkt kradzieży)';
      }
    else
      {
	$strEscape = '';
      }
    $smarty -> assign(array("Date" => $prisoner -> fields['data'], 
                            "Verdict" => $prisoner -> fields['verdict'], 
                            "Duration" => $intTime, 
                            "Cost" => $prisoner -> fields['cost'],
                            "Duration2" => $prisoner -> fields['duration'],
			    "Escape" => $strEscape,
                            "Youare" => YOU_ARE,
                            "Pdate" => P_DATE,
                            "Pduration" => P_DURATION,
                            "Pduration2" => P_DURATION_R,
                            "Preason" => P_REASON,
			    "Nocost" => "Brak możliwości wpłacenia kaucji",
                            "Pcost" => P_COST));
    $prisoner -> Close();
}

/**
* Pay for free prisoner
*/
if (isset($_GET['prisoner'])) 
{
    checkvalue($_GET['prisoner']);
    $prisoner = $db -> Execute("SELECT * FROM `jail` WHERE `id`=".$_GET['prisoner']);
    if (!$prisoner -> fields['id']) 
    {
        error (NO_PRISONER);
    }
    if ($prisoner->fields['cost'] == 0)
      {
	error("Nie możesz wpłacić kaucji za tego więźnia.");
      }
    if ($prisoner -> fields['cost'] > $player -> credits) 
    {
        error (NO_MONEY);
    }
    if ($player -> id == $prisoner -> fields['prisoner']) 
    {
        error (NO_PERM);
    }
    $pname = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$prisoner -> fields['prisoner']);
    if (!isset($_GET['step']))
    {
        $_GET['step'] = '';
        $smarty -> assign(array("Youwant" => YOU_WANT,
                                "Ayes" => YES,
                                "Prisonername" => $pname -> fields['user'],
                                "Aback" => A_BACK));
    }
    if (isset($_GET['step']) && $_GET['step'] == 'confirm')
    {
        $db -> Execute("UPDATE `players` SET `miejsce`='Altara' WHERE `id`=".$prisoner -> fields['prisoner']);
        $db -> Execute("DELETE FROM `jail` WHERE `id`=".$prisoner -> fields['id']);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$prisoner -> fields['cost']." WHERE `id`=".$player -> id);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$prisoner -> fields['prisoner'].",'".L_PLAYER.'<a href="view.php?view='.$player -> id.'">'.$player -> user.'</a>'.L_ID.'<b>'.$player -> id.'</b>'.PAY_FOR."', ".$strDate.", 'J')");
        error (YOU_PAY.$prisoner -> fields['cost'].GOLD_FOR.$pname -> fields['user'].L_ID.$prisoner -> fields['prisoner']);
    }
    $smarty -> assign("Step", $_GET['step']);
}
    else
{
    $_GET['prisoner'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Location" => $player -> location,
                        "Prisoner" => $_GET['prisoner']));
$smarty -> display ('jail.tpl');

require_once("includes/foot.php");
?>
