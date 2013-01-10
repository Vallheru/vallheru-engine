<?php
/**
 *   File functions:
 *   View other players, steal money, astral components from other players
 *
 *   @name                 : view.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 10.01.2013
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
// $Id$

$title = "Zobacz"; 
require_once("includes/head.php");

if (!isset($_GET['view'])) 
{
    error("<a href=\"\"></a>");
}

checkvalue($_GET['view']);

$view = new Player($_GET['view']);

/**
* Get the localization for game
*/
require_once("languages/".$lang."/view.php");

if (empty ($view -> id)) 
{
    error (NO_PLAYER);
}

$view->user = $arrTags[$view->tribe][0].' '.$view->user.' '.$arrTags[$view->tribe][1];
if ($view->tribe)
  {
    $tribe = $db -> Execute("SELECT `name` FROM `tribes` WHERE `id`=".$view -> tribe);
    $smarty -> assign ("Clan", T_CLAN.": <a href=tribes.php?view=view&amp;id=".$view -> tribe.">".$tribe -> fields['name']."</a><br />".T_CLAN_RANK.": ".$view -> tribe_rank."<br />");
  } 
 else 
   {
     $smarty -> assign ("Clan", T_CLAN.": ".NOTHING."<br />");
   }

$objPrev = $db->Execute("SELECT `id` FROM `players` WHERE `id`<".$view->id." ORDER BY `id` DESC LIMIT 1");
$intPrevious = $objPrev->fields['id'];
$objPrev->Close();
$objNext = $db->Execute("SELECT `id` FROM `players` WHERE `id`>".$view->id." ORDER BY `id` ASC LIMIT 1");
$intNext = $objNext->fields['id'];
$objNext->Close();
$objMMid = $db->Execute("SELECT MAX(`id`), MIN(`id`) FROM `players`");
if ($view->id == $objMMid->fields['MAX(`id`)'])
  {
    $intNext = $objMMid->fields['MIN(`id`)'];
  }
elseif ($view->id == $objMMid->fields['MIN(`id`)'])
{
  $intPrevious = $objMMid->fields['MAX(`id`)'];
}
$objMMid->Close();

if ($view->gender == 'M')
  {
    $strSuffix = 'y';
  }
else
  {
    $strSuffix = 'a';
  }

$smarty -> assign (array("User" => $view -> user, 
                         "Id" => $view -> id, 
                         "Avatar" => '', 
                         "GG" => '', 
                         "Immu" => '', 
                         "Attack" => '', 
                         "Mail" => '', 
                         "Crime" => '', 
                         "Crime2" => '',
                         "Gender" => '', 
                         "Deity" => '', 
                         "IP" => '',
                         "Trank" => T_RANK,
                         "Tlocation" => T_LOCATION,
                         "Tlastseen" => 'Ostatnio widzian'.$strSuffix,
                         "Tage" => T_AGE,
                         "Tclass2" => T_CLASS2,
                         "Trace" => T_RACE,
                         "Tstatus" => T_STATUS,
                         "Tmaxhp" => T_MAX_HP,
                         "Tfights" => T_FIGHTS,
                         "Tlastkill" => T_LAST_KILL,
                         "Tlastkilled" => 'Ostatnio zabit'.$strSuffix.' przez',
			 "Treputation" => "Reputacja",
                         "Trefs" => T_REFS,
			 "Anext" => 'Następny profil',
			 "Aprevious" => 'Poprzedni profil',
			 "Previous" => $intPrevious,
			 "Next" => $intNext,
                         "Tprofile" => T_PROFILE,
                         "Toptions" => T_OPTIONS));
$plik = 'avatars/'.$view -> avatar;
if (is_file($plik)) 
{
    require_once('includes/avatars.php');
    $arrImage = scaleavatar($plik);
    $smarty->assign(array('Avatar' => $plik,
			  'Awidth' => $arrImage[0],
			  'Aheight' => $arrImage[1]));
}
if (!empty($view -> gg)) 
{
    $smarty -> assign ("GG", GG_NUMBER.$view -> gg."<br />");
}

/**
 * Select player rank
 */
require_once('includes/ranks.php');
$strRank = selectrank($view -> rank, $view -> gender);

if ($view -> immunited == 'Y') 
{
    $smarty -> assign ("Immu", HAVE_IMMU."<br />");
}

/**
 * Name of player location
 */
$strLocation = $view -> location;
if ($view -> location == 'Altara')
{
    $strLocation = $city1;
}
if ($view -> location == 'Ardulith')
{
    $strLocation = $city2;
}

$smarty -> assign(array("Page" => $strViewpage, 
                        "Age" => $view -> age, 
                        "Race" => $view -> race, 
                        "Clas" => $view -> clas, 
                        "Rank" => $strRank, 
                        "Location" => $strLocation, 
                        "Maxhp" => $view -> max_hp, 
                        "Wins" => $view -> wins, 
                        "Losses" => $view -> losses, 
                        "Lastkilled" => $view -> lastkilled, 
                        "Lastkilledby" => $view -> lastkilledby, 
                        "Profile" => $view -> profile,
			"Refs" => $view->vallars,
			"Reputation" => $view->reputation));

if ($view -> wins || $view -> losses)
{
    $intAllfight = $view -> wins + $view -> losses;
    $fltRatio = round(($view -> wins / $intAllfight) * 100, 3);
    $smarty -> assign("Fratio", "(".$fltRatio." %)");
}
    else
{
    $smarty -> assign("Fratio", '');
}

if ($view -> gender) 
{
    if ($view -> gender == 'M') 
    {
        $gender = G_MALE;
    } 
        else 
    {
        $gender = G_FEMALE;
    }
    $smarty -> assign ("Gender", T_GENDER.": ".$gender."<br />");
}
if (!empty ($view -> deity)) 
{
    $smarty -> assign ("Deity", T_DEITY.": ".$view -> deity."<br />");
}
if ($view->gender == 'M')
  {
    $strAlive = 'Żywy';
    $strDead = 'Martwy';
  }
else
  {
    $strAlive = 'Żywa';
    $strDead = 'Martwa';
  }
if ($view -> hp > 0) 
{
    $smarty -> assign ("Status", "<b>".$strAlive."</b><br />");
} 
    else 
{
    $smarty -> assign ("Status", "<b>".$strDead."</b><br />");
}

$objFreeze = $db -> Execute("SELECT `freeze`, `shortrpg` FROM `players` WHERE `id`=".$_GET['view']);

if ($player -> location == $view -> location && $view -> immunited == 'N' && $player -> immunited == 'N' && $player -> id != $view -> id) 
{
    if (!$objFreeze -> fields['freeze'])
    {
        $smarty -> assign ("Attack", "<li><a href=battle.php?battle=".$view -> id.">".A_ATTACK."</a></li><li><a href=view.php?view=".$view->id."&amp;consider>Oceń przeciwnika</a></li>");
    }
        else
    {
        $smarty -> assign("Attack", '');
    }
}

if ($player -> id != $view -> id) 
  {
    $strLink = "<li><a href=mail.php?view=write&amp;to=".$view -> id.">".A_WRITE_PM."</a></li>";
    $objTest = $db->Execute("SELECT `id` FROM `contacts` WHERE `owner`=".$player->id." AND `pid`=".$view->id);
    if ($objTest->fields['id'])
      {
	$strLink .= "<li><a href=account.php?view=contacts&amp;edit=".$objTest->fields['id']."&amp;delete>Usuń z listy kontaktów</a></li>";
      }
    else
      {
	$strLink .= "<li><a href=account.php?view=contacts&amp;add&amp;pid=".$view->id.">Dodaj do listy kontaktów</a></li>";
      }
    $objTest->Close();
    $objTest = $db->Execute("SELECT `id` FROM `ignored` WHERE `owner`=".$player->id." AND `pid`=".$view->id);
    if ($objTest->fields['id'])
      {
	$strLink .= "<li><a href=account.php?view=ignored&amp;edit=".$objTest->fields['id']."&amp;delete>Usuń z listy ignorowanych</a></li>";
      }
    else
      {
	$strLink .= "<li><a href=account.php?view=ignored&amp;add&amp;pid=".$view->id.">Dodaj do listy ignorowanych</a></li>";
      }
    $objTest->Close();
    if ($player->room != 0)
      {
	$objRowner = $db->Execute("SELECT `owner` FROM `rooms` WHERE `id`=".$player->room);
	if ($view->room == 0 && $view->settings['rinvites'] == 'Y' && $view->rinvite == 0)
	  {
	    $strLink .= '<li><a href="view.php?view='.$view->id.'&amp;room=add">Zaproś do pokoju w karczmie</a></li>';
	  }
	elseif($view->room == $player->room && $objRowner->fields['owner'] == $player->id)
	  {
	    $strLink .= '<li><a href="view.php?view='.$view->id.'&amp;room=remove">Wyrzuć z pokoju w karczmie</a></li>';
	  }
      }
    if ($player->team != 0)
      {
	$objTeam = $db->Execute("SELECT * FROM `teams` WHERE `id`=".$player->team);
	$blnFull = TRUE;
	foreach (array('slot1', 'slot2', 'slot3', 'slot4', 'slot5') as $strSlot)
	  {
	    if ($objTeam->fields[$strSlot] == 0)
	      {
		$blnFull = FALSE;
		break;
	      }
	  }
	if ($view->team == 0 && $view->tinvite == 0 && !$blnFull && $objTeam->fields['leader'] == $player->id)
	  {
	    //$strLink .= '<li><a href="view.php?view='.$view->id.'&amp;team=add">Zaproś do drużyny</a></li>';
	  }
	elseif ($view->team == $player->team && $objTeam->fields['leader'] == $player->id)
	  {
	    //$strLink .= '<li><a href="view.php?view='.$view->id.'&amp;team=remove">Wyrzuć z drużyny</a></li>';
	  }
	$objTeam->Close();
      }
    $smarty -> assign ("Mail", $strLink);
  }

if ($objFreeze -> fields['freeze'])
{
    $smarty -> assign("Tfreezed", T_FREEZED."<br />");
}
    else
{
    $smarty -> assign("Tfreezed", '');
}

if ($objFreeze->fields['shortrpg'] != '')
  {
    $smarty->assign("Rprofile", '<a href="roleplay.php?view='.$view->id.'">Profil fabularny</a>');
  }
else
  {
    $smarty->assign("Rprofile", '');
  }

if ($player -> clas == 'Złodziej' && $player -> energy > 2 && $player -> location == $view -> location && $player -> id != $view -> id) 
{
    if (!$objFreeze -> fields['freeze'])
    {
        $smarty -> assign("Crime", "<li><a href=view.php?view=".$view -> id."&amp;steal>".A_STEAL."</a></li><li><a href=view.php?view=".$view->id."&amp;spy>Szpieguj</a></li>");
    }
        else
    {
        $smarty -> assign("Crime", '');
    }
}

$blnCrime = false;
if ($player -> clas == 'Złodziej' && $player -> id != $view -> id) 
{
    $objAstralcrime = $db -> Execute("SELECT `astralcrime` FROM `players` WHERE `id`=".$player -> id);
    if (!$objFreeze -> fields['freeze'] && $objAstralcrime -> fields['astralcrime'] == 'Y' && $player->energy < 5)
    {
        $smarty -> assign("Crime2", "<li><a href=\"view.php?view=".$view -> id."&amp;steal_astral=".$view -> id."\">".A_STEAL2."</a></li>");
        $blnCrime = true;
    }
        else
    {
        $smarty -> assign("Crime2", '');
    }
    $objAstralcrime -> Close();
}

if ($player -> rank == 'Admin' || $player -> rank == 'Staff') 
{
    $smarty -> assign ("IP", "<a href=\"memberlist.php?limit=0&amp;lista=user&amp;ip=".$view->ip."\">".PLAYER_IP.$view -> ip."</a>");
}

$objViewtime = $db->Execute("SELECT `lpv` FROM `players` WHERE `id`=".$view->id);
if ($objViewtime->fields['lpv'] == 0)
  {
    $strSeen = 'Nigdy';
  }
else
  {
    $intLastseen = intval(($ctime - $objViewtime->fields['lpv']) / 86400);
    if ($intLastseen == 0)
      {
	$intLastseen = intval(($ctime - $objViewtime->fields['lpv']) / 3600);
	if ($intLastseen == 0)
	  {
	    $intLastseen = intval(($ctime - $objViewtime->fields['lpv']) / 60);
	    if ($intLastseen == 0)
	      {
		$strSeen = 'Teraz';
	      }
	    else
	      {
		$strSeen = 'około '.$intLastseen." minut temu.";
	      }
	  }
	else
	  {
	    $strSeen = 'około '.$intLastseen." godzin temu.";
	  }
      }
    elseif ($intLastseen == 1)
      {
	$strSeen = 'Wczoraj';
      }
    else
      {
	$strSeen = 'około '.$intLastseen." dni temu.";
      }
  }
$objViewtime->Close();
$smarty->assign(array("Seen" => "Ostatnio aktywn".$strSuffix,
		      "Lastseen" => $strSeen));

/**
 * Consider enemy
 */
if (isset($_GET['consider']))
  {
    if ($view->immunited == 'Y')
      {
	message('error', 'Nie możesz oceniać tej postaci.');
      }
    elseif ($player->energy < 1)
      {
	message('error', 'Nie masz energii aby oceniać tę postać.');
      }
    elseif ($player->id == $view->id)
      {
	message('error', 'Nie możesz oceniać samego siebie.');
      }
    else
      {
	$player->energy --;
	$db->Execute("UPDATE `players` SET `energy`=`energy`-1 WHERE `id`=".$player->id);
	//Player level
	$intPlevel = $player->stats['condition'][2] + $player->stats['speed'][2] + $player->stats['agility'][2] + $player->skills['dodge'][1] + $player->hp;
	if ($player->equip[0][0] || $player->equip[11][0] || $player->equip[1][0])
	  {
	    $intPlevel += $player->stats['strength'][2];
	    if ($player->equip[0][0] || $player->equip[11][0])
	      {
		$intPlevel += $player->skills['attack'][1];
	      }
	    else
	      {
		$intPlevel += $player->skills['shoot'][1];
	      }
	  }
	else
	  {
	    $intPlevel += $player->stats['wisdom'][2] + $player->stats['inteli'][2] + $player->skills['magic'][1];
	  }
	//Enemy level
	$intElevel = $view->stats['condition'][2] + $view->stats['speed'][2] + $view->stats['agility'][2] + $view->skills['dodge'][1] + $player->hp;
	if ($view->equip[0][0] || $view->equip[11][0] || $view->equip[1][0])
	  {
	    $intElevel += $view->stats['strength'][2];
	    if ($view->equip[0][0] || $view->equip[11][0])
	      {
		$intElevel += $view->skills['attack'][1];
	      }
	    else
	      {
		$intElevel += $view->skills['shoot'][1];
	      }
	  }
	else
	  {
	    $intElevel += $view->stats['wisdom'][2] + $view->stats['inteli'][2] + $view->skills['magic'][1];
	  }
	$intDiv = $intElevel / $intPlevel;
	if ($intDiv <= 0.1)
	  {
	    $strInfo = 'nie stanowi zagrożenia dla ciebie.';
	  }
	elseif ($intDiv > 0.1 && $intDiv <= 0.5)
	  {
	    $strInfo = 'jest niegroźny dla ciebie.';
	  }
	elseif ($intDiv > 0.5 && $intDiv <= 0.9)
	  {
	    $strInfo = 'stanowi prawie wyzwanie dla ciebie.';
	  }
	elseif ($intDiv > 0.9 && $intDiv <= 1.2)
	  {
	    $strInfo = 'jest tak samo potężny jak ty.';
	  }
	elseif ($intDiv > 1.2 && $intDiv <= 1.3)
	  {
	    $strInfo = 'jest nieco niebezpieczny dla ciebie.';
	  }
	elseif ($intDiv > 1.3 && $intDiv <= 1.5)
	  {
	    $strInfo = 'jest groźny dla ciebie.';
	  }
	elseif ($intDiv > 1.5 && $intDiv <= 1.7)
	  {
	    $strInfo = 'jest niebezpieczny dla ciebie.';
	  }
	elseif ($intDiv > 1.7)
	  {
	    $strInfo = 'zabije ciebie jednym ciosem.';
	  }
	message('success', $view->user.' '.$strInfo);
	$player->checkexp(array('perception' => ceil($intElevel / 10)), $player->id, 'skills');
      }
  }

/**
 * Battle logs
 */
if (isset($_GET['logs']))
  {
    //Pagination
    $objAmount = $db->Execute("SELECT count(`id`) FROM `battlelogs` WHERE `pid`=".$view->id);
    $intPages = ceil($objAmount->fields['count(`id`)'] / 30);
    $objAmount->Close();
    if (!isset($_GET['page']))
      {
	$intPage = 1;
      }
    else
      {
	$intPage = $_GET['page'];
      }
    $arrBattles = $db->GetAll("SELECT `did`, `wid`, `bdate` FROM `battlelogs` WHERE `pid`=".$view->id." ORDER BY `id` DESC LIMIT ".(30 * ($intPage - 1)).", 30");
    $arrIds = array();
    if ($view->gender == 'M')
      {
	$strSuffix = '';
      }
    else
      {
	$strSuffix = 'a';
      }
    foreach ($arrBattles as &$arrBattle)
      {
	if ($arrBattle['wid'] == $view->id)
	  {
	    $arrBattle['result'] = 'zwyciężył';
	  }
	elseif ($arrBattle['wid'] == 0)
	  {
	    $arrBattle['result'] = 'zremisował';
	  }
	else
	  {
	    $arrBattle['result'] = 'przegrał';
	  }
	if (!in_array($arrBattle['did'], $arrIds))
	  {
	    $arrIds[] = $arrBattle['did'];
	  }
	$arrBattle['result'] .= $strSuffix;
	$arrBattle['ename'] = 'Nieobecny(a)';
      }
    if (count($arrBattles) > 0)
      {
	$objNames = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `id` IN (".implode(', ', $arrIds).")") or die ($db->ErrorMsg());
	while (!$objNames->EOF)
	  {
	    foreach ($arrBattles as &$arrBattle)
	      {
		if ($arrBattle['did'] == $objNames->fields['id'])
		  {
		    $arrBattle['ename'] = $objNames->fields['user'];
		  }
	      }
	    $objNames->MoveNext();
	  }
	$objNames->Close();
      }

    $smarty->assign(array("Tpages" => $intPages,
			  "Tpage" => $intPage,
			  "Fpage" => "Idź do strony:",
			  "Blogs" => $arrBattles,
			  "Logs" => 'Y',
			  "Tage" => "dnia obecnej ery walczył".$strSuffix." i ",
			  "Twith" => "z",
			  "Aback" => "Wróć",
			  "Lamount" => count($arrBattles),
			  "Nologs" => "Nie ma informacji na temat walk tego gracza."));
  }
else
  {
    $smarty->assign("Logs", 'N');
  }

/**
 * Team actions
 */
if (isset($_GET['team']))
  {
    //Invitation
    if ($_GET['team'] == 'add')
      {
	$blnValid = TRUE;
	if ($player->team == 0)
	  {
	    message('error', 'Nie masz drużyny, do której mógłbyś zaprosić tego gracza.');
	    $blnValid = FALSE;
	  }
	if ($view->team > 0)
	  {
	    message('error', 'Ten gracz należy już do jakiejś drużyny.');
	    $blnValid = FALSE;
	  }
	if ($view->tinvite > 0)
	  {
	    message('error', 'Ten gracz jest już zaproszony do jakiejś drużyny.');
	    $blnValid = FALSE;
	  }
	$objTest = $db->Execute("SELECT `id` FROM `ignored` WHERE `owner`=".$view->id." AND `pid`=".$player->id." AND `inn`='Y'");
	if ($objTest->fields['id'])
	  {
	    message('error', 'Ta osoba ignoruje twoje zaproszenia do drużyny.');
	    $blnValid = FALSE;
	  }
	$objTest->Close();
	if ($blnValid)
	  {
	    $objTeam = $db->Execute("SELECT * FROM `teams` WHERE `id`=".$player->team);
	    $blnFull = TRUE;
	    foreach (array('slot1', 'slot2', 'slot3', 'slot4', 'slot5') as $strSlot)
	      {
		if ($objTeam->fields[$strSlot] == 0)
		  {
		    $blnFull = FALSE;
		    break;
		  }
	      }
	    $objTeam->Close();
	    if ($blnFull)
	      {
		message('error', 'Nie możesz zaprosić innych graczy do drużyny, ponieważ jest ona już pełna.');
		$blnValid = FALSE;
	      }
	  }
	if ($blnValid)
	  {
	    $db->Execute("UPDATE `players` SET `tinvite`=".$player->team." WHERE `id`=".$view->id);
	    $strDate = $db -> DBDate($newdate);
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view->id.", '".$player->user." zaprosił(a) Ciebie do swojej drużyny. [<a href=log.php?accept=T>Akceptuj</a>] [<a href=log.php?refuse=T>Odrzuć</a>]', ".$strDate.", 'E')");
	    message('success', 'Zaprosiłeś(aś) '.$view->user.' do swojej drużyny.', '(<a href="view.php?view='.$view->id.'">Odśwież</a>)');
	  }
      }
    //Remove from team
    elseif ($_GET['team'] == 'remove')
      {
	$blnValid = TRUE;
	if ($player->team == 0)
	  {
	    message('error', 'Nie masz drużyny, z której mógłbyś wyrzucić tego gracza.');
	    $blnValid = FALSE;
	  }
	if ($player->team != $view->team)
	  {
	    message('error', 'Ten gracz nie należy do twojej drużyny.');
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $objLeader = $db->Execute("SELECT `leader` FROM `teams` WHERE `id`=".$player->team);
	    if ($objLeader->fields['leader'] != $player->id)
	      {
		message('error', 'Tylko przywódca drużyny może wyrzucać jej członków.');
		$blnValid = FALSE;
	      }
	    $objLeader->Close();
	  }
	if ($blnValid)
	  {
	    $strDate = $db -> DBDate($newdate);
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view->id.", '".$player->user." wyrzucił(a) Ciebie z drużyny.', ".$strDate.", 'D')");
	    $db->Execute("UPDATE `players` SET `team`=0 WHERE `id`=".$view->id);
	    message('success', 'Wyrzuciłeś(aś) '.$view->user.' z drużyny.', '(<a href="view.php?view='.$view->id.'">Odśwież</a>)');
	  }
      }
  }

/**
 * Room actions
 */
if (isset($_GET['room']))
  {
    //Invitation
    if ($_GET['room'] == 'add')
      {
	$blnValid = TRUE;
	if ($player->room == 0)
	  {
	    message('error', 'Nie posiadasz pokoju, do którego mógłbyś zaprosić tego gracza.');
	    $blnValid = FALSE;
	  }
	if ($view->room > 0)
	  {
	    message('error', 'Ten gracz jest już w jakimś pokoju w karczmie.');
	    $blnValid = FALSE;
	  }
	if ($view->rinvite > 0)
	  {
	    message('error', 'Ten gracz jest już zaproszony do jakiegoś pokoju w karczmie.');
	    $blnValid = FALSE;
	  }
	$objTest = $db->Execute("SELECT `id` FROM `ignored` WHERE `owner`=".$view->id." AND `pid`=".$player->id." AND `inn`='Y'");
	if ($objTest->fields['id'])
	  {
	    message('error', 'Ta osoba ignoruje twoje zaproszenia do pokoju w karczmie.');
	    $blnValid = FALSE;
	  }
	$objTest->Close();
	if ($view->settings['rinvites'] == 'N')
	  {
	    message('error', 'Ta osoba ma wyłączone zaproszenia do pokojów w karczmie.');
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $db->Execute("UPDATE `players` SET `rinvite`=".$player->room." WHERE `id`=".$view->id);
	    $strDate = $db -> DBDate($newdate);
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view->id.", '".$player->user." zaprosił(a) Ciebie do swojego pokoju w karczmie. [<a href=log.php?accept=R>Akceptuj</a>] [<a href=log.php?refuse=R>Odrzuć</a>]', ".$strDate.", 'E')");
	    message('success', 'Zaprosiłeś(aś) '.$view->user.' do swojego pokoju w karczmie.', '(<a href="view.php?view='.$view->id.'">Odśwież</a>)');
	  }
      }
    //Remove from room
    elseif ($_GET['room'] == 'remove')
      {
	$blnValid = TRUE;
	$objRowner = $db->Execute("SELECT `owner` FROM `rooms` WHERE `id`=".$player->room);
	if ($player->room == 0 || $player->id != $objRowner->fields['owner'])
	  {
	    message('error', 'Nie posiadasz własnego pokoju w karczmie.');
	    $blnValid = FALSE;
	  }
	$objRowner->Close();
	if ($player->room != $view->room)
	  {
	    message('error', 'Ten gracz nie przebywa w Twoim pokoju w karczmie.');
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $strDate = $db -> DBDate($newdate);
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view->id.", '".$player->user." wyrzucił(a) Ciebie z pokoju w karczmie.', ".$strDate.", 'E')");
	    $db->Execute("UPDATE `players` SET `room`=0 WHERE `id`=".$view->id);
	    message('success', 'Wyrzuciłeś(aś) '.$view->user.' z pokoju w karczmie.', '(<a href="view.php?view='.$view->id.'">Odśwież</a>)');
	  }
      }
  }

if (isset($_GET['spy']) || isset($_GET['steal']))
  {
    if ($player -> clas != 'Złodziej')
      {
        error(ERROR." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
      }
    if ($player -> energy < 2) 
      {
        error ("Nie masz tyle energii. (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
      }
    if ($player -> location != $view -> location) 
      {
        error (BAD_LOCATION." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
      }
    if ($player -> hp <= 0) 
      {
        error (YOU_DEAD." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
      }
    if ($view->newbie > 0)
      {
        error(TOO_YOUNG." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
      }
    if ($view->id == $player->id)
      {
	error(ERROR." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
      }
    if ($objFreeze -> fields['freeze'])
      {
        error(ACCOUNT_FREEZED." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
      }
    if ($view -> tribe > 0 && $view -> tribe == $player -> tribe)
      {
        error(SAME_CLAN." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
      }
    $player->curskills(array('thievery'));
    $player->clearbless(array('inteli', 'agility'));
    $view->curskills(array('perception'));
    $strDate = $db -> DBDate($newdate);
  }

/**
 * Spying
 */
if (isset($_GET['spy']))
  {
    $db -> Execute("UPDATE `players` SET `energy`=`energy`-2 WHERE `id`=".$player->id);
    $intDefense =  ($view->stats['inteli'][2] + $view->skills['perception'][1]);
    $intAttack = ($player->stats['agility'][2] + $player->stats['inteli'][2] + $player->skills['thievery'][1]);
    $chance = $intAttack - $intDefense;
    if ($chance < 1)
      {
	$player->checkexp(array('inteli' => 1,
				'agility' => 1), $player->id, 'stats');
	$player->checkexp(array('thievery' => 1), $player->id, 'skills');
	$view->checkexp(array('inteli' => ($intAttack / 2)), $player->id, 'stats');
	$view->checkexp(array('perception' => ($intAttack / 2)), $player->id, 'skills');
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'Nagle poczułeś, że ktoś się tobie bacznie przygląda. Rozglądając się wokoło zauważyłeś jak <b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id."</b> gwałtownie ucieka od Ciebie.', ".$strDate.", 'T')");
	$view->save();
	error("<br />Próbowałeś dowiedzieć się co posiada ".$view->user." przy sobie, niestety zauważył on Ciebie. Szybko umknąłeś w cień. (<a href=\"view.php?view=".$_GET['view']."\">Wróć</a>)");
      }
    elseif ($chance > 0 && $chance < 50)
      {
	$player->checkexp(array('inteli' => ($intDefense / 6),
				'agility' => ($intDefense / 6)), $player->id, 'stats');
	$player->checkexp(array('thievery' => ($intDefense / 6)), $player->id, 'skills');
	$view->checkexp(array('inteli' => ($intAttack / 4)), $player->id, 'stats');
	$view->checkexp(array('perception' => ($intAttack / 4)), $player->id, 'skills');
	$view->save();
	$strEquipment = 'Założony ekwipunek:<ul>';
	foreach ($view->equip as $arrEquip)
	  {
	    if ($arrEquip[0])
	      {
		$strEquipment .= '<li>'.$arrEquip[1].'</li>';
	      }
	  }
	if ($strEquipment == 'Założony ekwipunek:<ul>')
	  {
	    $strEquipment = 'Nic nie nosi na sobie.<br />';
	  }
	else
	  {
	    $strEquipment .= '</ul>';
	  }
	$strEquipment .= 'Złota w sakiewce: '.$view->credits;
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'Nagle poczułeś, że ktoś się tobie bacznie przygląda. Rozglądając się wokoło zauważyłeś jak <b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id."</b> gwałtownie ucieka od Ciebie. Ciekawe jak długo obserwował Ciebie.', ".$strDate.", 'T')");
	error("<br />Przyglądałeś się przez chwilę ".$view->user." niestety, w pewnym momencie zauważył Ciebie. Szybko uciekłeś w cień. Zdobyte informacje:<br />".$strEquipment." (<a href=view.php?view=".$view->id.">Wróć</a>)");
      }
    else
      {
	$player->checkexp(array('inteli' => ($intDefense / 3),
				'agility' => ($intDefense / 3)), $player->id, 'stats');
	$player->checkexp(array('thievery' => ($intDefense / 3)), $player->id, 'skills');
	$view->checkexp(array('inteli' => 1), $player->id, 'stats');
	$view->checkexp(array('perception' => 1), $player->id, 'skills');
	$view->save();
	$strEquipment = 'Założony ekwipunek:<ul>';
	foreach ($view->equip as $arrEquip)
	  {
	    if ($arrEquip[0])
	      {
		$strEquipment .= '<li>'.$arrEquip[1].' (+'.$arrEquip[2].') ';
		if ($arrEquip[5] != 0)
		  {
		    $strEquipment .= '('.($arrEquip[5] * -1).' zr) ';
		  }
		if ($arrEquip[7] != 0)
		  {
		    $strEquipment .= '('.$arrEquip[7].' szyb)';
		  }
		$strEquipment .= '</li>';
	      }
	  }
	if ($strEquipment == 'Założony ekwipunek:<ul>')
	  {
	    $strEquipment = 'Nic nie nosi na sobie.<br />';
	  }
	else
	  {
	    $strEquipment .= '</ul>';
	  }
	$strEquipment .= 'Złota w sakiewce: '.$view->credits.'<br />';
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'W pewnym momencie odniosłeś nieprzyjemne wrażenie, że ktoś przygląda się Tobie. Rozglądając się na wszystkie strony, niestety nie zauważyłeś źródła niepokoju.', ".$strDate.", 'T')");
	error("<br />Przyglądałeś się przez dłuższą chwilę ".$view->user.". Na szczęście nie zauważył twojej obecności. Zdobyte informacje:<br />".$strEquipment." (<a href=view.php?view=".$view->id.">Wróć</a>)");
      }
  }

/**
* Pickpocket
*/
if (isset ($_GET['steal'])) 
{
    if ($player -> immunited == 'Y') 
    {
        error(YOU_IMMU." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($view -> immunited == 'Y') 
    {
        error(HE_IMMU." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }

    $intAttack = ($player->stats['agility'][2] + $player->stats['inteli'][2] + $player->skills['thievery'][2] + $player->checkbonus('pickpocket'));
    $intDefense = ($player->stats['agility'][2] + $player->stats['inteli'][2] + $player->skills['perception'][2]);
    $chance = $intAttack - $intDefense;
    if ($chance < 1) 
    {
        $cost = 1000 * $player->skills['thievery'][1];
	$player->checkexp(array('inteli' => 1,
				'agility' => 1), $player->id, 'stats');
	$player->checkexp(array('thievery' => 1), $player->id, 'skills');
	$view->checkexp(array('inteli' => ($intAttack / 3),
			      'agility' => ($intAttack / 3)), $player->id, 'stats');
	$view->checkexp(array('perception' => ($intAttack / 3)), $player->id, 'skills');
	$view->save();
        if ($player -> location != 'Lochy') 
        {
            $db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `energy`=`energy`-2 WHERE `id`=".$player -> id);
            $db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$player -> id.",'".VERDICT."',7,".$cost.",'".$data."')");                
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$player -> id.",'".YOU_IN_JAIL.$cost.".', ".$strDate.", 'T')");
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".YOU_CATCH."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.YOU_CATCH2."', ".$strDate.", 'T')");
            error ("<br />".YOU_JAILED." (<b><a href=\"view.php?view=".$_GET['view']."\">".BACK."</a></b>)");
        } 
            else 
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".WHEN_YOU."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.".', ".$strDate.", 'T')");         
            $db -> Execute("UPDATE `players` SET `crime`=`crime`-1 WHERE `id`=".$player-> id);
            error ("<br />".YOU_TRY_IN." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");          
        }
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
    if ($chance > 0 && $chance < 50) 
    {
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-2 WHERE `id`=".$player -> id);
	$player->checkexp(array('inteli' => ($intDefense / 6),
				'agility' => ($intDefense / 6)), $player->id, 'stats');
	$player->checkexp(array('thievery' => ($intDefense / 6)), $player->id, 'skills');
	$view->checkexp(array('inteli' => ($intAttack / 6),
			      'agility' => ($intAttack / 6)), $player->id, 'stats');
	$view->checkexp(array('perception' => ($intAttack / 6)), $player->id, 'skills');
	$view->save();
        if ( $view -> credits > 0) 
        {
	    $lost = ceil($view -> credits / 10);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$lost." WHERE `id`=".$view->id);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$lost." WHERE `id`=".$player -> id);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".YOU_CATCH."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.NOT_CATCH.$lost.GOLD_COINS."', ".$strDate.", 'T')");
            error ("<br />".WHEN_YOU2.$lost.WHEN_YOU21." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        } 
	else 
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".YOU_CATCH."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.EMPTY_BAG."', ".$strDate.", 'T')");
            error ("<br />".EMPTY_BAG2." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        }
    }
    if ($chance > 49) 
    {
        $db -> Execute("UPDATE `players` SET `energy`=`energy`-2 WHERE `id`=".$player -> id);
	$player->checkexp(array('inteli' => ($intDefense / 3),
				'agility' => ($intDefense / 3)), $player->id, 'stats');
	$player->checkexp(array('thievery' => ($intDefense / 3)), $player->id, 'skills');
	$view->checkexp(array('inteli' => 1,
			      'agility' => 1), $player->id, 'stats');
	$view->checkexp(array('perception' => 1), $player->id, 'skills');
	$view->save();
        if ( $view -> credits > 0) 
        {
            $lost = ceil($view -> credits / 10);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$lost." WHERE `id`=".$view -> id);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$lost." WHERE `id`=".$player -> id);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".YOU_CRIME.$lost.YOU_CRIME2."', ".$strDate.", 'T')");
            error ("<br />".SUCCESS.$lost.GOLD_COINS2." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        } 
	else 
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".EMPTY_BAG3."', ".$strDate.", 'T')");
            error ("<br />".EMPTY_BAG4." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        }
    }
}

/**
 * Steal astral components
 */
if (isset($_GET['steal_astral']))
{
    checkvalue($_GET['steal_astral']);
    if ($player -> clas != 'Złodziej' || $player -> location == 'Lochy')
    {
        error(ERROR." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($_GET['steal_astral'] != $_GET['view']) 
    {
        error (NO_PLAYER2." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if (!$blnCrime) 
    {
        error (NO_CRIME." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($player -> hp <= 0) 
    {
        error (YOU_DEAD." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }
    if ($view -> tribe > 0 && $view -> tribe == $player -> tribe)
    {
        error(SAME_CLAN." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
    }

    require_once('includes/astralsteal.php');
    astralsteal($_GET['view'], 'V');
}

if ($objFreeze -> fields['freeze'])
{
    $objFreeze -> Close();
}

/**
 * Display page
 */
$smarty -> display ('view.tpl');
require_once("includes/foot.php");
?>
