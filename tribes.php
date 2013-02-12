<?php
/**
 *   File functions:
 *   Clans - info, manage, herbs, minerals and battles
 *
 *   @name                 : tribes.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : mori <ziniquel@users.sourceforge.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 12.02.2013
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

$title = "Klany";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/tribes.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.<a href=");
}

$smarty -> assign(array("Logo" => '',
                        "WWW" => '',
                        "Message" => '',
                        "Message2" => '',
                        "Pubmessage" => '',
                        "Privmessage" => '',
                        "Hospass1" => '',
                        "Message1" => '',
                        "Step4" => '',
                        "New" => '',
                        "Perm" => '',
                        "Victory" => '',
                        "Link" => '',
                        "Menu" => '',
                        "Empty" => '',
                        "Change" => '',
                        "Itemid" => '',
                        "Perm1" => 0,
                        "Perm2" => 0,
                        "Perm3" => 0,
                        "Perm4" => 0,
                        "Perm5" => 0,
                        "Perm6" => 0,
                        "Perm7" => 0,
                        "Perm8" => 0,
                        "Perm9" => 0,
                        "Perm10" => 0,
                        "Perm11" => 0,
                        "Rank1" => 0,
                        "Rank2" => 0,
                        "Rank3" => 0,
                        "Rank4" => 0,
                        "Rank5" => 0,
                        "Rank6" => 0,
                        "Rank7" => 0,
                        "Rank8" => 0,
                        "Rank9" => 0,
                        "Rank10" => 0));

/**
* Main menu
*/
if (!isset ($_GET['view']) && !isset($_GET['join'])) 
  {
    $smarty -> assign(array("Claninfo" => "<b>Witaj w Domu Klanów. </b><br /><br />Klany to dobrowolne zrzeszenia mieszkańców wszystkich miast ".$gamename.". Posiadają odrębne zasady (lub brak zasad), cele, strukturę i organizację. Mogą prowadzić ze sobą wojny lub zabrać się za budową Astralnej Machiny. Dysponują również własnym forum. Każdy gracz może należeć do maksymalnie jednego klanu.<br /><br />Ze względu na organizację wewnątrz-klanową, nazywane bywają \"państwami w państwie\". Postępują według własnych reguł, nierzadko stawiając je ponad vallheryjskim prawem. W większości są to zamożne instytucje, posiadające warowne siedziby obsadzone wojskiem. Spotykane są również niewielkie kryjówki zrzeszonych złodziejaszków czy też eleganckie dwory miejscowej arystokracji. <br /><br /><b>Tutaj możesz zobaczyć, dołączyć lub nawet jeśli posiadasz odpowiednią ilość złota, stworzyć swój własny klan. </b><br />",
			    "Ashow" => A_SHOW));
    if ($player -> tribe) 
      {
        $mytribe = $db -> Execute("SELECT `name` FROM `tribes` WHERE `id`=".$player -> tribe);
        $smarty -> assign(array("Mytribe" => "<li><a href=\"tribes.php?view=my\">".MY_TRIBE."</a> (".$mytribe -> fields['name'].")</li>",
				"Make" => ""));
      } 
    else 
      {
	$smarty -> assign(array("Mytribe" => "",
				"Make" => '<li><a href="tribes.php?view=make">Stwórz nowy klan</a></li>'));
      }
  }

/**
* List of clans
*/
if (isset ($_GET['view']) && $_GET['view'] == 'all') 
  {
    $query = $db -> Execute("SELECT count(`id`) FROM `tribes`");
    $numt = $query -> fields['count(`id`)'];
    $query -> Close();
    if ($numt <= 0) 
      {
        $smarty -> assign(array("Text" => NO_CLANS,
				"Showinfo" => ''));
      } 
    else 
      {
        $smarty -> assign ("Text", "<ul>");
        $arrTribes = $db->GetAll("SELECT `id`, `name`, `owner`, `level` FROM `tribes` ORDER BY `id` ASC, `level` DESC");
	$arrOwners = array();
	$arrLevels = array('Kryjówka', 'Kamienica', 'Dworek', 'Dwór', 'Zamek');
	foreach ($arrTribes as &$arrTribe)
	  {
	    $arrOwners[] = $arrTribe['owner'];
	    $arrTribe['ownername'] = 'Nieobecny(a)';
	    $arrTribe['level'] = $arrLevels[($arrTribe['level'] - 1)];
	  }
	$objOwners = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `id` IN (".implode(',', $arrOwners).")");
	while (!$objOwners->EOF)
	  {
	    $intKey = array_search($objOwners->fields['id'], $arrOwners);
	    $arrTribes[$intKey]['ownername'] = $objOwners->fields['user'];
	    $objOwners->MoveNext();
	  }
	$objOwners->Close();
        $smarty -> assign(array("Tribes" => $arrTribes, 
				"Showinfo" => SHOW_INFO,
				"Leaderid" => "Przywódca: ",
				"Ttype" => "Typ: "));
      }
  }

/**
* Clan info
*/
if (isset ($_GET['view']) && $_GET['view'] == 'view') 
  {
    if (!isset($_GET['step']))
      {
	$_GET['step'] = '';
      }
    if ($_GET['step'] == '') 
    {
	checkvalue($_GET['id']);
        $tribe = $db -> Execute("SELECT `id`, `name`, `owner`, `wygr`, `przeg`, `public_msg`, `www`, `logo`, `level`, `traps`, `agents`, `dagents` FROM `tribes` WHERE `id`=".$_GET['id']);
        if (!$tribe -> fields['id']) 
        {
            error (NO_CLAN);
        }
        $plik = 'images/tribes/'.$tribe -> fields['logo'];
        $query = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `tribe`=".$tribe -> fields['id']);
        $memnum = $query -> fields['count(`id`)'];
        $query -> Close();
        if (is_file($plik)) 
        {
            $arrImageparams = getimagesize($plik);
            if ($arrImageparams[0] > 200)
            {
                $arrImageparams[0] = 200;
            }
            if ($arrImageparams[1] > 100)
            {
                $arrImageparams[1] = 100;
            }
            $smarty -> assign ("Logo", "<center><img src=\"".$plik."\" width=\"".$arrImageparams[0]."\" height=\"".$arrImageparams[1]."\" /></center><br />");
        }

        $objAstral = $db -> Execute("SELECT `used`, `directed` FROM `astral_machine` WHERE `owner`=".$tribe -> fields['id']);
	$objTest = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='tribe'");
	if (($objAstral->fields['used'] || $objAstral->fields['directed']) && !$objTest->fields['value'] && $player->energy >= 5)
	  {
	    $strSabotage = 'Sabotuj astralną machinę';
	  }
	else
	  {
	    $strSabotage = '';
	  }
	$objTest->Close();
        if ($objAstral -> fields['used'])
        {
            $intPercent = round($objAstral -> fields['used'] / 100, 2);
            $arrPercent = array(20.99, 40.99, 70.99, 85.99, 99.99, 100);
            $arrAstral = array(ASTRAL1, ASTRAL2, ASTRAL3, ASTRAL4, ASTRAL5, ASTRAL6);
            for ($i = 0; $i < 5; $i++)
            {
                if ($intPercent <= $arrPercent[$i])
                {
                    break;
                }
            }
            $strAstral = ASTRAL.$arrAstral[$i]."<br />";
        }
	else
	  {
	    if ($tribe->fields['level'] == 5)
	      {
		$strAstral = ASTRAL."Nie rozpoczęto jeszcze prac.<br />";
	      }
	    else
	      {
		$strAstral = '';
	      }
	  }
        $objAstral -> Close();

	//Tribe level
	$arrLevels = array('Kryjówka', 'Kamienica', 'Dworek', 'Dwór', 'Zamek');
	if ($tribe->fields['level'] == 5)
	  {
	    $strWins = WIN_AMOUNT.": ".$tribe->fields['wygr']."<br />";
	    $strLoses = LOST_AMOUNT.": ".$tribe->fields['przeg']."<br />";
	  }
	else
	  {
	    $strWins = '';
	    $strLoses = '';
	  }

	$objOwner = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$tribe->fields['owner']);
	if (!$objOwner->fields['id'])
	  {
	    $strOwner = 'Nieobecny';
	  }
	else
	  {
	    $strOwner = $objOwner->fields['user'];
	  }
	$objOwner->Close();
        if ($tribe -> fields['www']) 
        {
            $smarty -> assign ("WWW", CLAN_PAGE.': <a href="http://'.$tribe -> fields['www'].'" target="_blank">'.$tribe -> fields['www'].'</a><br />');
        }
	$strTrapsinfo = '';
        if ($player -> clas == 'Złodziej' && $player -> tribe != $tribe -> fields['id']) 
	  {
	    $arrTactions = array();
	    if ($tribe->fields['traps'] > 0 || ($tribe->fields['agents'] - $tribe->fields['dagents']) > 0)
	      {
		$strTrapsinfo = 'Wprawnym okiem dostrzegasz ';
		if ($tribe->fields['traps'] > 0)
		  {
		    $strTrapsinfo .= 'podejrzane miejsca na ziemi w około siedziby';
		    $arrTactions['traps'] = 'Rozbrój pułapki (koszt: '.$tribe->fields['traps'].' energii)';
		  }
		if ($tribe->fields['traps'] > 0 && ($tribe->fields['agents'] - $tribe->fields['dagents']) > 0)
		  {
		    $strTrapsinfo .= ' oraz ';
		  }
		if (($tribe->fields['agents'] - $tribe->fields['dagents']) > 0)
		  {
		    $strTrapsinfo .= 'strażników patrolujących okolicę';
		    $arrTactions['agents'] = 'Przekup strażników (koszt: '.(($tribe->fields['agents'] - $tribe->fields['dagents']) * 5000).' sztuk złota)';
		  }
		$strTrapsinfo .= '.';
	      }
	    $smarty->assign('Tactions', $arrTactions);
            $objAstralcrime = $db -> Execute("SELECT `astralcrime` FROM `players` WHERE `id`=".$player -> id);
            if ($objAstralcrime -> fields['astralcrime'] == 'Y')
	      {
		$smarty->assign(array("Asteal" => A_STEAL,
				      "Aespionage" => "Szpieguj klan",
				      "Asabotage" => $strSabotage));
	      }
	    else
	      {
                $smarty -> assign("Asteal", '');
	      }
            $objAstralcrime -> Close();
	  }
	else
	  {
            $smarty -> assign("Asteal", '');
	  }
	$smarty -> assign(array("Name" => $tribe -> fields['name'], 
                                "Owner" => $tribe -> fields['owner'], 
				"Ownername" => $strOwner,
                                "Members" => $memnum, 
                                "Tribeid" => $tribe -> fields['id'], 
                                "Wins"=> $strWins, 
                                "Lost" => $strLoses, 
                                "Pubmessage" => $tribe -> fields['public_msg'],
                                "Astral" => $strAstral,
				"Tlevel2" => $arrLevels[($tribe->fields['level'] - 1)],
                                "Yousee" => YOU_SEE,
                                "Leader2" => "Przywódca:",
                                "Memamount" => MEM_AMOUNT,
                                "Amembers" => A_MEMBERS,
				"Tlevel" => "Rodzaj klanu:",
				"Ptribe" => $player->tribe,
				"Aback" => "Wróć do spisu klanów",
                                "Jointo" => JOIN_TO,
                                "Ajoin" => A_JOIN,
				"Trapsinfo" => $strTrapsinfo));
        $tribe -> Close();
    }
   else
      {

	//Thief actions
	if (in_array($_GET['step'], array('steal', 'sabotage', 'espionage', 'traps', 'agents')))
	  {
	    checkvalue($_GET['id']);
	    $objTribe = $db -> Execute("SELECT `id`, `owner`, `level`, `traps`, `agents`, `dagents` FROM `tribes` WHERE `id`=".$_GET['id']);
	    if (!$objTribe -> fields['id']) 
	      {
		error(NO_CLAN." (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">Wróć</a>)");
	      }
	    if ($player->clas != 'Złodziej' || $player->location == 'Lochy')
	      {
		error(ERROR." (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">Wróć</a>)");
	      }
	    if ($player -> hp <= 0) 
	      {
		error ("Nie możesz podejmować jakiejkolwiek akcji przeciwko klanom, ponieważ jesteś martwy (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">".BACK."</a>)");
	      }
	    if ($player -> tribe == $_GET['id'])
	      {
		error(SAME_CLAN." (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">Wróć</a>)");
	      }
	    if ($_GET['step'] != 'traps' && $_GET['step'] != 'agents')
	      {
		$objAstralcrime = $db -> Execute("SELECT `astralcrime` FROM `players` WHERE `id`=".$player -> id);
		if ($objAstralcrime -> fields['astralcrime'] == 'N')
		  {
		    error ("Możesz szpiegować klany tylko raz na reset. (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">Wróć</a>)");
		  }
		$objAstralcrime -> Close();
		if ($player->energy < 5)
		  {
		    error('Nie masz wystarczającej ilości energii.');
		  }
	      }
	    $player->curskills(array('thievery'));
	    $intStats = ($player->stats['agility'][2] + $player->stats['inteli'][2] + $player->skills['thievery'][1] + $player->checkbonus('spy'));
	    $player->clearbless(array('inteli', 'agility'));
	    /**
	     * Add bonus from tools
	     */
	    if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
	      {
		$intStats += (($player->equip[12][2] / 100) * $intStats);
	      }
	    $blnAction = TRUE;
	    $strDate = $db -> DBDate($newdate);
	    if ($_GET['step'] == 'traps')
	      {
		if ($objTribe->fields['traps'] == 0)
		  {
		    error("Nie ma pułapek do rozbrojenia. (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">Wróć</a>)");
		  }
		if ($objTribe->fields['traps'] > $player->energy)
		  {
		    error("Nie masz wystarczającej ilości energii. (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">Wróć</a>)");
		  }
		$db->Execute("UPDATE `players` SET `energy`=`energy`-".$objTribe->fields['traps']."  WHERE `id`=".$player->id);
		$intGainexp = $objTribe->fields['traps'] * 3;
		if (rand(1, 100) <= 5 || $intStats < $objTribe->fields['traps'])
		  {
		    $db->Execute("UPDATE `players` SET `miejsce`='Lochy' WHERE `id`=".$player->id);
		    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objTribe->fields['owner'].",'Dowódca straży klanowej melduje, że przyłapano <b><a href=view.php?view=".$player->id.">".$player->user."</a></b> ID:".$player->id." jak próbował rozbroić pułapki w około siedziby klanu i natychmiast przekazano go strażnikom miejskim.', ".$strDate.", 'T')");
		    $blnAction = FALSE;
		    $intGainexp = $objTribe->fields['traps'];
		  }
	      }
	    if ($_GET['step'] == 'agents')
	      {
		$intAmount = ($objTribe->fields['agents'] - $objTribe->fields['dagents']);
		if ($intAmount == 0)
		  {
		    error("Nie ma strażników do przekupienia. (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">Wróć</a>)");
		  }
		$intCost = ($intAmount * 5000);
		if ($player->credits < $intCost)
		  {
		    error("Nie masz tylu sztuk złota przy sobie. (<a href=\"tribes.php?view=view&id=".$_GET['id']."\">Wróć</a>)");
		  }
		$intGainexp = 0;
		$db->Execute("UPDATE `players` SET `credits`=`credits`-".$intCost." WHERE `id`=".$player->id);
		if (rand(1, 100) <= 5 || $intStats < $intAmount)
		  {
		    $db->Execute("UPDATE `players` SET `miejsce`='Lochy' WHERE `id`=".$player->id);
		    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objTribe->fields['owner'].",'Dowódca straży klanowej melduje, że przyłapano <b><a href=view.php?view=".$player->id.">".$player->user."</a></b> ID:".$player->id." jak próbował przekupić strażników i natychmiast przekazano go strażnikom miejskim.', ".$strDate.", 'T')");
		    $blnAction = FALSE;
		  }
	      }
	    if ($_GET['step'] == 'traps' || $_GET['step'] == 'agents')
	      {
		if ($blnAction)
		  {
		    if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
		      {
			$player->equip[12][6] -= $intAmount;
			if ($player->equip[12][6] <= 0)
			  {
			    $db->Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[12][0]);
			  }
			else
			  {
			    $db->Execute("UPDATE `equipment` SET `wt`=`wt`-1 WHERE `id`=".$player->equip[12][0]);
			  }
		      }
		  }
		else
		  {
		    if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
		      {
			$db->Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[12][0]);
		      }
		    $objTool = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U' AND `name` LIKE 'Wytrychy%'");
		    if ($objTool->fields['id'])
		      {
			$intRoll = rand(1, 100);
			if ($intRoll < 50)
			  {
			    $db->Execute("DELETE FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
			  }
		      }
		    $objTool->Close();
		  }
		if ($intGainexp > 0)
		  {
		    $player->checkexp(array('agility' => ceil($intGainexp / 3),
					    'inteli' => ceil($intGainexp / 3)), $player->id, 'stats');
		    $player->checkexp(array('thievery' => ceil($intGainexp / 3)), $player->id, 'skills');
		  }
	      }
	    if ($_GET['step'] == 'espionage' || $_GET['step'] == 'sabotage')
	      {
		if ($_GET['step'] == 'sabotage')
		  {
		    $objTest = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='tribe'");
		    if ($objTest->fields['value'])
		      {
			error('Nie możesz już sabotować machiny, została wybudowana.  (<a href="tribes.php?view=view&id='.$_GET['id'].'">Wróć</a>)');
		      }
		    $objTest->Close();
		  }
		$db->Execute("UPDATE `players` SET `astralcrime`='N', `energy`=`energy`-5 WHERE `id`=".$player->id);
		$objMembers = $db->Execute("SELECT `id` FROM `players` WHERE `tribe`=".$_GET['id']);
		$intGainexp = 0;
		$intAmount = 0;
		while(!$objMembers->EOF)
		  {
		    $objMember = new Player($objMembers->fields['id']);
		    $objMember->curskills(array('perception'));
		    $intChance = $intStats - ($objMember->stats['agility'][2] + $objMember->stats['inteli'][2] + $objMember->skills['perception'][1]);
		    if ($intChance < 1)
		      {
			$objMember->checkexp(array('agility' => ($intStats / 3),
						   'inteli' => ($intStats / 3)), $player->id, 'stats');
			$objMember->checkexp(array('perception' => ($intStats / 3)), $player->id, 'skills');
			$objMember->save();
			$db->Execute("UPDATE `players` SET `miejsce`='Lochy' WHERE `id`=".$player->id);
			$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objMembers->fields['id'].",'Przyłapałeś <b><a href=view.php?view=".$player->id.">".$player->user."</a></b> ID:".$player->id." jak myszkował po siedzibie twojego klanu i natychmiast przekazałeś go strażnikom.', ".$strDate.", 'T')");
			$blnAction = FALSE;
			break;
		      }
		    $intGainexp += ($objMember->stats['agility'][2] + $objMember->stats['inteli'][2] + $objMembers->skills['perception'][1]);
		    $intAmount ++;
		    $objMembers->MoveNext();
		  }
		$objMembers->Close();
		//Check agents
		if ($blnAction && ($objTribe->fields['agents'] - $objTribe->fields['dagents']) > 0)
		  {
		    $arrMax = array(0, 5, 10, 20, 40, 50);
		    $intChance = (($objTribe->fields['agents'] - $objTribe->fields['dagents']) / $arrMax[$objTribe->fields['level']]) * 90;
		    if ($intChance < rand(1, 100))
		      {
			$db->Execute("UPDATE `players` SET `miejsce`='Lochy' WHERE `id`=".$player->id);
			$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objTribe->fields['owner'].",'Dowódca straży klanowej melduje, że przyłapano <b><a href=view.php?view=".$player->id.">".$player->user."</a></b> ID:".$player->id." jak myszkował po siedzibie twojego klanu i natychmiast przekazano go strażnikom miejskim.', ".$strDate.", 'T')");
			$blnAction = FALSE;
		      }
		  }
		//Check traps
		if ($blnAction && $objTribe->fields['traps'])
		  {
		    $arrMax = array(0, 5, 10, 20, 40, 50);
		    $intChance = ($objTribe->fields['traps'] / $arrMax[$objTribe->fields['level']]) * 90;
		    if ($intChance < rand(1, 100))
		      {
			$db->Execute("UPDATE `players` SET `miejsce`='Lochy' WHERE `id`=".$player->id);
			$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objTribe->fields['owner'].",'Dowódca straży klanowej melduje, że <b><a href=view.php?view=".$player->id.">".$player->user."</a></b> ID:".$player->id." dał się złapać w jedną z pułapek zabezpieczających siedzibę twojego klanu i natychmiast przekazano go strażnikom miejskim.', ".$strDate.", 'T')");
			$blnAction = FALSE;
		      }
		  }
		if ($blnAction)
		  {
		    $intGainexp = $intGainexp / 2;
		    if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
		      {
			$player->equip[12][6] -= $intAmount;
			if ($player->equip[12][6] <= 0)
			  {
			    $db->Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[12][0]);
			  }
			else
			  {
			    $db->Execute("UPDATE `equipment` SET `wt`=`wt`-1 WHERE `id`=".$player->equip[12][0]);
			  }
		      }
		  }
		else
		  {
		    $intGainexp = $intAmount;
		    if (stripos($player->equip[12][1], 'wytrychy') !== FALSE)
		      {
			$db->Execute("DELETE FROM `equipment` WHERE `id`=".$player->equip[12][0]);
		      }
		    $objTool = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U' AND `name` LIKE 'Wytrychy%'");
		    if ($objTool->fields['id'])
		      {
			$intRoll = rand(1, 100);
			if ($intRoll < 50)
			  {
			    $db->Execute("DELETE FROM `equipment` WHERE `owner`=".$player->id." AND `type`='E' AND `status`='U'");
			  }
		      }
		    $objTool->Close();
		  }
		if ($intGainexp < 3)
		  {
		    $intGainexp = 3;
		  }
		$player->checkexp(array('agility' => ceil($intGainexp / 3),
					'inteli' => ceil($intGainexp / 3)), $player->id, 'stats');
		$player->checkexp(array('thievery' => ceil($intGainexp / 3)), $player->id, 'skills');
		$db->Execute("UPDATE `tribes` SET `dagents`=0 WHERE `id`=".$objTribe->fields['id']);
	      }
	  }

	/**
	 * Steal astral components
	 */
	if ($_GET['step'] == 'steal')
	  {
	    require_once('includes/astralsteal.php');
	    astralsteal($_GET['id'], 'C', $objTribe -> fields['owner']);
	    $db->Execute("UPDATE `players` SET `energy`=`energy`-5 WHERE `id`=".$player->id);
	    
	    if ($objTribe->fields['level'] == 5)
	      {
		require_once('includes/checkastral.php');
		checkastral($objTribe -> fields['id']);
	      }
	    
	    $objTribe -> Close();
	  }

	//Removing traps
	elseif ($_GET['step'] == 'traps')
	  {
	    if ($blnAction)
	      {
		$db->Execute("UPDATE `tribes` SET `traps`=0 WHERE `id`=".$objTribe->fields['id']);
		$strMessage = 'Po cichu, nie wzbudzając niczyich podejrzeń, rozbroiłeś pułapki wokół siedziby klanu. Teraz powinno być nieco łatwiej dostać się do środka.<br />Zdobyłeś '.$intGainexp.' punktów doświadczenia.';
	      }
	    else
	      {
		$intCost = $player->skills['thievery'][1] * 1000;
		$db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.",'Próba rozbrojenia pułapek klanu', 7, ".$intCost.", '".$data."')");   
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'Zostałeś wtrącony do więzienia na 1 dzień za próbę rozbrojenia pułapek klanu. Kaucja wynosi: ".$intCost.".', ".$strDate.", 'T')");
		$strMessage = "Próbowałeś rozbroić pułapki, niestety jedna z nich zadziałała! I tak oto znalazłeś się w lochach.";
	      }
	    $objTribe -> Close();
	    error($strMessage);
	  }

	//Bribe agents
	elseif ($_GET['step'] == 'agents')
	  {
	    if ($blnAction)
	      {
		$db->Execute("UPDATE `tribes` SET `dagents`=".$objTribe->fields['agents']." WHERE `id`=".$objTribe->fields['id']);
		$strMessage = 'Krążąc wśród strażników, przekonywałeś ich przez jakiś czas, przy pomocy złota, że nie powinni zwracać uwagi na podejrzane hałasy w najbliższej przyszłości. Wydaje się, że całkowicie zgodzili się z tobą.';
	      }
	    else
	      {
		$intCost = $player->skills['thievery'][1] * 1000;
		$db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.",'Próba przekupienia strażników klanu', 7, ".$intCost.", '".$data."')");   
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'Zostałeś wtrącony do więzienia na 1 dzień za próbę przekupienia strażników klanu. Kaucja wynosi: ".$intCost.".', ".$strDate.", 'T')");
		$strMessage = "Próbowałeś przekupić strażników, niestety jeden z nich okazał się odporny na twoje argumenty! I tak oto znalazłeś się w lochach.";
	      }
	    $objTribe -> Close();
	    error($strMessage);
	  }

	//Espionage of clan
	elseif ($_GET['step'] == 'espionage')
	  {
	    if ($blnAction)
	      {
		$objResult = $db->Execute("SELECT `credits`, `platinum`, `zolnierze`, `forty` FROM `tribes` WHERE `id`=".$_GET['id']);
		$strMessage = 'Oto wiadomości jakie udało ci się zebrać o klanie:<br />Złoto: '.$objResult->fields['credits'].'<br />Mithril: '.$objResult->fields['platinum'].'<br />Żołnierzy: '.$objResult->fields['zolnierze'].'<br />Fortyfikacji: '.$objResult->fields['forty']."<br />Zdobyłeś ".$intGainexp." punktów doświadczenia.";
		$objResult->Close();
	      }
	    else
	      {
		$intCost = $player->skills['thievery'][1] * 1000;
		$db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.",'Próba szpiegowania klanu', 7, ".$intCost.", '".$data."')");   
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'Zostałeś wtrącony do więzienia na 1 dzień za próbę szpiegowania klanu. Kaucja wynosi: ".$intCost.".', ".$strDate.", 'T')");
		$strMessage = "Próbowałeś dowiedzieć się czegoś o klanie, niestety zostałeś złapany! I tak oto znalazłeś się w lochach.";
	      }
	    $objTribe -> Close();
	    error($strMessage);
	  }

	//Sabotage of astral machine
	elseif ($_GET['step'] == 'sabotage')
	  {
	    if ($blnAction)
	      {
		$objAstral = $db -> Execute("SELECT `used`, `directed` FROM `astral_machine` WHERE `owner`=".$objTribe->fields['id']);
		$intAmount = $objAstral->fields['used'] - ceil($objAstral->fields['used'] / rand(10, 50));
		if ($intAmount < 0)
		  {
		    $intAmount = 0;
		  }
		$intAmount2 = $objAstral->fields['directed'] - ceil($objAstral->fields['directed'] / rand(10, 50));
		if ($intAmount2 < 0)
		  {
		    $intAmount2 = 0;
		  }
		$objAstral->Close();
		$db->Execute("UPDATE `astral_machine` SET `used`=".$intAmount.", `directed`=".$intAmount2." WHERE `owner`=".$objTribe->fields['id']);
		$strMessage = "Udało ci się uszkodzić Astralną Machinę! Niezauważony szybko uciekasz w bezpieczne miejsce. Zdobyłeś ".$intGainexp." punktów doświadczenia.";
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objTribe->fields['owner'].",'Olbrzymi pióropusz ognia zakwitł nad Astralną Machiną twojego klanu. Ktoś dokonał sabotażu!', ".$strDate.", 'T')");
	      }
	    else
	      {
		$intCost = $player->skills['thievery'][1] * 5000;
		$db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player->id.",'Próba zniszczenia Astralnej Machiny', 7, ".$intCost.", '".$data."')");   
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player->id.",'Zostałeś wtrącony do więzienia na 1 dzień za próbę zniszczenia Astralnej Machiny. Kaucja wynosi: ".$intCost.".', ".$strDate.", 'T')");
		$strMessage = "Próbowałeś uszkodzić Astralną Machinę, niestety zostałeś złapany! I tak oto znalazłeś się w lochach.";
	      }
	    $objTribe -> Close();
	    error($strMessage);
	  }
	
	//Show list of members
	elseif ($_GET['step'] == 'members') 
	  {
	    checkvalue($_GET['tid']);
	    $tribename = $db -> Execute("SELECT `name`, `owner` FROM `tribes` WHERE `id`=".$_GET['tid']);
	    $mem = $db -> Execute("SELECT `id`, `user`, `tribe_rank` FROM `players` WHERE `tribe`=".$_GET['tid']." ORDER BY `id` ASC");
	    $arrlink = array();
	    while (!$mem -> EOF) 
	      {
		if ($mem -> fields['id'] == $tribename -> fields['owner']) 
		  {
		    $arrlink[] = "- <a href=\"view.php?view=".$mem -> fields['id']."\">".$mem -> fields['user']."</a> (".$mem -> fields['id'].") (".$mem -> fields['tribe_rank'].") (".LEADER.")<br />";
		  } 
                else 
		  {
		    $arrlink[] = "- <a href=\"view.php?view=".$mem -> fields['id']."\">".$mem -> fields['user']."</a> (".$mem -> fields['id'].") (".$mem -> fields['tribe_rank'].")<br />";
		  }
		$mem -> MoveNext();
	      }
	    $mem -> Close();
	    $smarty -> assign(array("Name" => $tribename -> fields['name'], 
				    "Link" => $arrlink,
				    "Memberlist" => MEMBER_LIST));
	    $tribename -> Close();
	  }
      }
}

/**
* Join to clan
*/
if (isset($_GET['join'])) 
  {
    checkvalue($_GET['join']);
    $tribe = $db -> Execute("SELECT * FROM `tribes` WHERE `id`=".$_GET['join']);
    $test = $db -> Execute("SELECT `gracz` FROM `tribe_oczek` WHERE `gracz`=".$player -> id);
    if (!isset ($_GET['change'])) 
      {
        if ($player -> tribe) 
	  {
            error (YOU_IN_CLAN);
	  }
        if ($test -> fields['gracz']) 
	  {
            $smarty -> assign(array("Tribeid" => $_GET['join'], 
				    "Playerid" => $test -> fields['gracz'], 
				    "Check" => 1,
				    "Youwait" => YOU_WAIT,
				    "Ayes" => YES,
				    "Ano" => NO));
	  } 
	else 
	  {
            $strDate = $db -> DBDate($newdate);
            $db -> Execute("INSERT INTO `tribe_oczek` (`gracz`, `klan`) VALUES(".$player -> id.",".$tribe -> fields['id'].")");
            $db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$tribe -> fields['owner'].",'".L_PLAYER."<b><a href=\"view.php?view=".$player -> id."\">".$player -> user.L_ID.'<b>'.$player -> id.'</b>'.HE_WANT."', ".$strDate.", 'C')");
	    $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$tribe -> fields['id']." AND `wait`=1");
	    while (!$objPerm -> EOF)
	      {
		$db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].",'".L_PLAYER." <b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.'<b>'.$player -> id.'</b>'.HE_WANT."', ".$strDate.", 'C')");
		$objPerm -> MoveNext();
	      }
	    $objPerm -> Close();
            error (YOU_SEND.$tribe -> fields['name'].".");
	  }
      } 
    else 
      {
        if ($player -> tribe) 
	  {
            error (YOU_IN_CLAN);
	  }
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$tribe -> fields['owner'].",'".L_PLAYER." <b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.'<b>'.$player -> id.'</b>'.HE_WANT."', ".$strDate.", 'C')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$tribe -> fields['id']." AND `wait`=1");
        while (!$objPerm -> EOF)
	  {
            $db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].",'".L_PLAYER." <b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.'<b>'.$player -> id.'</b>'.HE_WANT."', ".$strDate.", 'C')");
            $objPerm -> MoveNext();
	  }
        $objPerm -> Close();
        $db -> Execute("UPDATE `tribe_oczek` SET `klan`=".$tribe -> fields['id']." WHERE `gracz`=".$player -> id);
        error (YOU_SEND.$tribe -> fields['name'].".");
      }
}

/**
* Make clan
*/
if (isset ($_GET['view']) && $_GET['view'] == 'make') 
  {
    $arrOptions = array(1 => 'Kryjówka (koszt: 500 000 sztuk złota, maksymalnie 5 osób w klanie)',
			2 => 'Kamienica (koszt: 1 000 000 sztuk złota, dodaje zbrojownię oraz magazyn klanowy, maksymalnie 10 osób w klanie)',
			3 => 'Dworek (koszt: 1 500 000 sztuk złota, dodaje skarbiec, astralny skarbiec oraz zielnik klanowy, maksymalnie 20 osób w klanie)',
			4 => 'Dwór (koszt: 2 000 000 sztuk złota, liczba osób w klanie bez ograniczeń) <br /><b>albo</b>',
			5 => 'Zamek (koszt: 2 500 000 sztuk złota, liczba osób w klanie bez ograniczeń, pozwala budować Astralną Machinę oraz uczestniczyć w walkach klanowych)');
    $smarty -> assign(array("Clanname" => CLAN_NAME,
			    "Amake" => A_MAKE,
			    "Coptions" => $arrOptions));
    if ($player -> tribe) 
    {
        error (YOU_IN_CLAN);
    }
    if ($player -> hp < 1)
    {
        error(YOU_DEAD);
    }
    $objTest = $db->Execute("SELECT `gracz` FROM `tribe_oczek` WHERE `gracz`=".$player->id);
    if ($objTest->fields['gracz'])
      {
	error('Nie możesz założyć klanu, ponieważ oczekujesz na przyjęcie do innego.');
      }
    $objTest->Close();
    if (isset($_GET['step']) && $_GET['step'] == 'make') 
    {
        if (!$_POST['name']) 
        {
            error (NO_NAME);
        }
	if (!isset($_POST['ctype']))
	  {
	    error("Podaj, jaki typ klanu chcesz założyć");
	  }
	checkvalue($_POST['ctype']);
	if ($_POST['ctype'] > 5)
	  {
	    error('Zapomnij o tym.');
	  }
	$intCost = $_POST['ctype'] * 500000;
	if ($player->credits < $intCost)
	  {
	    error('Nie masz tyle sztuk złota przy sobie.');
	  }
	$_POST['name'] = htmlspecialchars($_POST['name'], ENT_QUOTES);
        $db -> Execute("INSERT INTO `tribes` (`name`, `owner`, `level`) VALUES('".$_POST['name']."', ".$player -> id.", ".$_POST['ctype'].")");
	$newt = $db -> Execute("SELECT id FROM tribes WHERE owner=".$player -> id);
        $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$intCost.", `tribe`=".$newt -> fields['id']." WHERE `id`=".$player -> id);
        $newt -> Close();
        error (YOU_MAKE.$_POST['name']."</i>.<br />");
    }
}

/**
* My clan menu
*/
if (isset ($_GET['view']) && $_GET['view'] == 'my') 
{
    if (!$player -> tribe) 
    {
        error (NOT_IN);
    }
    $mytribe = $db -> Execute("SELECT * FROM tribes WHERE id=".$player -> tribe);
    $perm = $db -> Execute("SELECT * FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']." AND player=".$player -> id);
    require_once('includes/tribemenu.php');
    $smarty -> assign (array("Name" => $mytribe -> fields['name'],
                             "Myclan" => MY_CLAN));
    if (!isset ($_GET['step'])) 
    {
        $plik = 'images/tribes/'.$mytribe -> fields['logo'];
        if (is_file($plik)) 
        {
            $smarty -> assign ("Logo", "<center><img src=\"".$plik."\" height=\"100\"></center><br>");
        }
        $query = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `tribe`=".$mytribe -> fields['id']);
        $memnum = $query -> fields['count(`id`)'];
        $query -> Close();
        $owner = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$mytribe -> fields['owner']);
        if ($player->id == $mytribe->fields['owner'] || !$perm -> fields['info'])
        {
            $smarty -> assign(array("Gold" => $mytribe -> fields['credits'], 
                                    "Mithril" => $mytribe -> fields['platinum'],
                                    "Soldiers" => $mytribe -> fields['zolnierze'], 
                                    "Forts" => $mytribe -> fields['forty']));
        }
            else
        {
            $smarty -> assign(array("Gold" => UNKNOWN,
                                    "Mithril" => UNKNOWN,
                                    "Soldiers" => UNKNOWN,
                                    "Forts" => UNKNOWN));
        }
        $objAstral = $db -> Execute("SELECT `aviable`, `used` FROM `astral_machine` WHERE `owner`=".$mytribe -> fields['id']);
	$objTest = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='tribe'");
        if ($objAstral -> fields['aviable'] == 'Y' && !$objTest->fields['value'])
        {
            $strAstral = "<a href=\"tribes.php?view=my&amp;step=astral\">".A_MACHINE."</a>";
        }
            else
        {
            $strAstral = A_MACHINE;
        }
	$objTest->Close();
        if ($objAstral -> fields['used'])
        {
            $intPercent = round($objAstral -> fields['used'] / 100, 2);
        }
            else
        {
            $intPercent = 0;
        }
        $objAstral -> Close();
        $smarty -> assign(array("Members" => $memnum, 
				"Tlevel" => $mytribe->fields['level'],
                                "Owner" => $owner -> fields['user'], 
                                "Ownerid" => $owner -> fields['id'],  
                                "Wins" => $mytribe -> fields['wygr'], 
                                "Lost" => $mytribe -> fields['przeg'],  
                                "Privmessage" => $mytribe -> fields['private_msg'],
                                "Amachine" => $strAstral,
                                "Percent" => $intPercent,
                                "Apercent" => A_PERCENT,
                                "Clanname" => CLAN_NAME,
                                "Welcome" => WELCOME,
                                "Memamount" => MEM_AMOUNT,
                                "Leader" => LEADER,
                                "Goldcoins" => GOLD_COINS,
                                "Mithcoins" => MITH_COINS,
                                "Winamount" => WIN_AMOUNT,
                                "Lostamount" => LOST_AMOUNT,
                                "Tsoldiers" => T_SOLDIERS,
                                "Tforts" => T_FORTS));
        if ($mytribe -> fields['www']) 
        {
            $smarty -> assign ("WWW", "<li>".CLAN_PAGE.": <a href=\"http://".$mytribe -> fields['www']."\" target=\"_blank\">".$mytribe -> fields['www']."</a></li>");
        }
    }

    /**
     * Build astral machine
     */
    if (isset($_GET['step']) && $_GET['step'] == 'astral')
    {
        $objAstral = $db -> Execute("SELECT `used`, `directed`, `aviable` FROM `astral_machine` WHERE `owner`=".$mytribe -> fields['id']);
        if ($objAstral -> fields['aviable'] != 'Y')
        {
            error(NO_COMPONENTS);
        }
        $objTest = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='tribe'");
        if ($objTest -> fields['value'])
        {
            error(ASTRAL_BUILD);
        }
        $smarty -> assign(array("Aused" => A_USED,
                                "Aenergy" => A_ENERGY,
                                "Atoday" => A_TODAY,
                                "Amax" => A_MAX,
                                "Adirect" => A_DIRECT,
                                "Aform" => A_FORM,
                                "Message" => '',
                                "Eused" => $objAstral -> fields['used'],
                                "Edirected" => $objAstral -> fields['directed']));
        
        /**
         * Add energy to astral machine
         */
        if (isset($_GET['step2']) && $_GET['step2'] == 'add')
        {
	    checkvalue($_POST['amount']);
            if ($_POST['amount'] > $player -> energy)
            {
                error(NO_ENERGY);
            }
            $intDirected = $objAstral -> fields['directed'] + $_POST['amount'];
            if ($intDirected > 2000)
            {
                error(TOO_MUCH);
            }
            $intUsed = $objAstral -> fields['used'] + $intDirected;
            if ($intUsed > 10000)
            {
                error(TOO_MUCH2);
            }
            $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$_POST['amount']." WHERE `id`=".$player -> id);
            $db -> Execute("UPDATE `astral_machine` SET `directed`=".$intDirected." WHERE `owner`=".$mytribe -> fields['id']);
            $smarty -> assign("Message", YOU_ADD.$_POST['amount']." ".A_ENERGY." <a href=\"tribes.php?view=my&amp;step=astral\">".A_REFRESH."</a>");
        }

        $objAstral -> Close();
    }

    /**
    * Members list
    */
    if (isset ($_GET['step']) && $_GET['step'] == 'members') 
    {
        $mem = $db -> Execute("SELECT `id`, `user`, `tribe_rank` FROM `players` WHERE `tribe`=".$mytribe -> fields['id']." ORDER BY `id` ASC");
        $arrlink = array();
        $i = 0;
        while (!$mem -> EOF) 
        {
            if ($mem -> fields['id'] == $mytribe -> fields['owner']) 
            {
                $arrlink[$i] = "- <a href=\"view.php?view=".$mem -> fields['id']."\">".$mytribe->fields['prefix'].' '.$mem -> fields['user'].' '.$mytribe->fields['suffix']."</a> (".$mem -> fields['id'].") (".$mem -> fields['tribe_rank'].") (".LEADER.")<br />";
            } 
                else 
            {
                $arrlink[$i] = "- <a href=\"view.php?view=".$mem -> fields['id']."\">".$mytribe->fields['prefix'].' '.$mem -> fields['user'].' '.$mytribe->fields['suffix']."</a> (".$mem -> fields['id'].") (".$mem -> fields['tribe_rank'].")<br />";
            }
            $mem -> MoveNext();
            $i = $i + 1;
        }
        $mem -> Close();
        $smarty -> assign("Link", $arrlink);
    }

    /**
    * Leave clan
    */
    if (isset ($_GET['step']) && $_GET['step'] == 'quit') 
    {
        $smarty -> assign(array("Ayes" => YES,
            "Ano" => NO));
	$strDate = $db -> DBDate($newdate);
        if ($mytribe -> fields['owner'] == $player -> id) 
        {
            $smarty -> assign(array("Owner" => 1,
                "Qleader" => Q_LEADER));
            if (isset ($_GET['dalej'])) 
	      {
		$objMembers = $db->Execute("SELECT `id` FROM `players` WHERE `tribe`=".$mytribe->fields['id']);
		while(!$objMembers->EOF)
		  {
		    $db->Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$objMembers->fields['id'].", 'Twój klan ".$mytribe->fields['name']." został rozwiązany przez przywódcę <b><a href=\"view.php?view=".$player->id."\">".$player->user.L_ID.'<b>'.$player->id."'</b>.', ".$strDate.", 'C')");
		    $objMembers->MoveNext();
		  }
		$objMembers->Close();
                $db -> Execute("UPDATE players SET tribe=0 WHERE tribe=".$mytribe -> fields['id']);
                $db -> Execute("DELETE FROM tribes WHERE id=".$mytribe -> fields['id']);
                $db -> Execute("DELETE FROM tribe_zbroj WHERE klan=".$mytribe -> fields['id']);
                $db -> Execute("DELETE FROM tribe_mag WHERE klan=".$mytribe -> fields['id']);
                $db -> Execute("DELETE FROM tribe_oczek WHERE klan=".$mytribe -> fields['id']);
                $db -> Execute("DELETE FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']);
                $db -> Execute("UPDATE players SET tribe_rank='' WHERE id=".$player -> id);
                error (L_LEADER);
            }
        }  
            else 
        {
            $smarty -> assign(array("Owner" => 0,
                "Qmember" => Q_MEMBER));
            if (isset ($_GET['dalej'])) 
            {
                $db -> Execute("DELETE FROM tribe_perm WHERE tribe=".$mytribe -> fields['id']." AND player=".$player -> id);
                $strDate = $db -> DBDate($newdate);
                $db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$mytribe -> fields['owner'].", '".L_PLAYER." <b><a href=\"view.php?view=".$player -> id."\">".$player -> user.L_ID.'<b>'.$player -> id.'</b>'.M_LEAVE."', ".$strDate.", 'C')");
		$objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player->tribe." AND (`wait`=1 OR `kick`=1)");
		while (!$objPerm -> EOF)
		  {
		    $db -> Execute("INSERT INTO `log` (`owner`,`log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].",'".L_PLAYER." <b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.'<b>'.$player -> id.'</b>'.M_LEAVE."', ".$strDate.", 'C')");
		    $objPerm -> MoveNext();
		  }
		$objPerm -> Close();
		$db -> Execute("UPDATE players SET tribe=0, tribe_rank='' WHERE id=".$player -> id);
                error (L_MEMBER);
            }
        }
    }
}

/**
* Initialization of variables
*/
if (!isset($_GET['join'])) 
{
    $_GET['join'] = '';
}
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (!isset($_GET['step2'])) 
{
    $_GET['step2'] = '';
}
if (!isset($_GET['step3'])) 
{
    $_GET['step3'] = '';
}
if (!isset($_GET['daj'])) 
{
    $_GET['daj'] = '';
}
if (!isset($_GET['action'])) 
{
    $_GET['action'] = '';
}
if (!isset($_GET['view'])) 
{
    $_GET['view'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("View" => $_GET['view'], 
    "Join" => $_GET['join'], 
    "Step" => $_GET['step'], 
    "Step2" => $_GET['step2'], 
    "Step3" => $_GET['step3'], 
    "Give" => $_GET['daj'], 
    "Action" => $_GET['action']));
$smarty -> display('tribes.tpl');

require_once("includes/foot.php");
?>
