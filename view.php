<?php
/**
 *   File functions:
 *   View other players, steal money, astral components from other players
 *
 *   @name                 : view.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 01.05.2012
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
require_once("includes/checkexp.php");

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
                         "Tlevel" => T_LEVEL,
                         "Tstatus" => T_STATUS,
                         "Tmaxhp" => T_MAX_HP,
                         "Tfights" => T_FIGHTS,
                         "Tlastkill" => T_LAST_KILL,
                         "Tlastkilled" => 'Ostatnio zabit'.$strSuffix.' przez',
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
    $smarty -> assign ("Avatar", "<center><img src=\"".$plik."\" width=\"".$arrImage[0]."\" height=\"".$arrImage[1]."\" /></center>");
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
                        "Level" => $view -> level, 
                        "Maxhp" => $view -> max_hp, 
                        "Wins" => $view -> wins, 
                        "Losses" => $view -> losses, 
                        "Lastkilled" => $view -> lastkilled, 
                        "Lastkilledby" => $view -> lastkilledby, 
                        "Profile" => $view -> profile,
			"Refs" => $view->vallars));

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
if ($view -> hp > 0) 
{
    $smarty -> assign ("Status", "<b>".S_LIVE."</b><br />");
} 
    else 
{
    $smarty -> assign ("Status", "<b>".S_DEAD."</b><br />");
}

$objFreeze = $db -> Execute("SELECT `freeze`, `shortrpg` FROM `players` WHERE `id`=".$_GET['view']);

if ($player -> location == $view -> location && $view -> immunited == 'N' && $player -> immunited == 'N' && $player -> id != $view -> id) 
{
    if (!$objFreeze -> fields['freeze'])
    {
        $smarty -> assign ("Attack", "<li><a href=battle.php?battle=".$view -> id.">".A_ATTACK."</a></li>");
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
	if ($view->room == 0 && $view->rinvites == 'Y')
	  {
	    $strLink .= '<li><a href="view.php?view='.$view->id.'&amp;room=add">Zaproś do pokoju w karczmie</a></li>';
	  }
	elseif($view->room == $player->room && $objRowner->fields['owner'] == $player->id)
	  {
	    $strLink .= '<li><a href="view.php?view='.$view->id.'&amp;room=remove">Wyrzuć z pokoju w karczmie</a></li>';
	  }
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

if ($player -> clas == 'Złodziej' && $player -> crime > 0 && $player -> location == $view -> location && $player -> id != $view -> id) 
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
    if (!$objFreeze -> fields['freeze'] && $objAstralcrime -> fields['astralcrime'] == 'Y')
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
    $intLastseen = -1;
  }
else
  {
    $intLastseen = intval(($ctime - $objViewtime->fields['lpv']) / 86400);
  }
$objViewtime->Close();
switch ($intLastseen)
  {
  case -1:
    $strSeen = "Nigdy";
    break;
  case 0:
    $strSeen = "Dzisiaj";
    break;
  case 1:
    $strSeen = "Wczoraj";
    break;
  default:
    $strSeen = $intLastseen." dni temu.";
    break;
  }
$smarty->assign(array("Seen" => "Ostatnio aktywn".$strSuffix,
		      "Lastseen" => $strSeen));

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
	$objTest = $db->Execute("SELECT `id` FROM `ignored` WHERE `owner`=".$view->id." AND `pid`=".$player->id." AND `inn`='Y'");
	if ($objTest->fields['id'])
	  {
	    message('error', 'Ta osoba ignoruje twoje zaproszenia do pokoju w karczmie.');
	    $blnValid = FALSE;
	  }
	$objTest->Close();
	if ($view->rinvites == 'N')
	  {
	    message('error', 'Ta osoba ma wyłączone zaproszenia do pokojów w karczmie.');
	    $blnValid = FALSE;
	  }
	if ($blnValid)
	  {
	    $db->Execute("UPDATE `players` SET `room`=".$player->room." WHERE `id`=".$view->id);
	    $strDate = $db -> DBDate($newdate);
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view->id.", '".$player->user." zaprosił(a) Ciebie do swojego pokoju w karczmie.', ".$strDate.", 'E')");
	    message('success', 'Zaprosiłeś(aś) '.$view->user.' do swojego pokoju w karczmie.', '(<a href="view.php?view='.$view->id.'">Odśwież</a>)');
	  }
      }
    //Remove from room
    if ($_GET['room'] == 'remove')
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
    if ($player -> crime <= 0) 
      {
        error (NO_CRIME." (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
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
    $arrEquip = $player -> equipment();
    $player->curstats($arrEquip);
    $player->curskills(array('thievery'));
    $view->curstats();
    $view->curskills(array('perception'));
    $strDate = $db -> DBDate($newdate);
  }

/**
 * Spying
 */
if (isset($_GET['spy']))
  {
    $db -> Execute("UPDATE `players` SET `crime`=`crime`-1 WHERE `id`=".$player->id);
    $chance = ($player->agility + $player->inteli + $player->thievery) - ($view->inteli + $view->perception);
    if ($chance < 1)
      {
	$expgain = ceil($view->level / 100);
	checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', 0.01);
	$fltPerception = ($player->level / 100);
	$db->Execute("UPDATE `players` SET `perception`=`perception`+".$fltPerception." WHERE `id`=".$view->id);
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'Nagle poczułeś, że ktoś się tobie bacznie przygląda. Rozglądając się wokoło zauważyłeś jak <b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id."</b> gwałtownie ucieka od Ciebie.', ".$strDate.", 'T')");
	error("<br />Próbowałeś dowiedzieć się co posiada ".$view->user." przy sobie, niestety zauważył on Ciebie. Szybko umknąłeś w cień. (<a href=\"view.php?view=".$_GET['view']."\">Wróć</a>)");
      }
    elseif ($chance > 0 && $chance < 50)
      {
	$fltPerception = round($player->level / 200.0, 2);
	$fltThief = round($view->level / 400.0, 2);
	$expgain = ceil($view->level / 10);
	checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
	$db -> Execute("UPDATE `players` SET `perception`=`perception`+".$fltPerception." WHERE `id`=".$view->id);
	$objEquipment = $db->Execute("SELECT `name` FROM `equipment` WHERE `owner`=".$view->id." AND `status`='E'");
	$strEquipment = 'Założony ekwipunek:<ul>';
	while (!$objEquipment->EOF)
	  {
	    $strEquipment .= '<li>'.$objEquipment->fields['name'].'</li>';
	    $objEquipment->MoveNext();
	  }
	if ($strEquipment == 'Założony ekwipunek:<ul>')
	  {
	    $strEquipment = 'Nic nie nosi na sobie.<br />';
	  }
	else
	  {
	    $strEquipment .= '</ul>';
	  }
	$objEquipment->Close();
	$objGold = $db->Execute("SELECT `credits` FROM `players` WHERE `id`=".$view->id);
	$strEquipment .= 'Złota w sakiewce: '.$objGold->fields['credits'];
	$objGold->Close();
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'Nagle poczułeś, że ktoś się tobie bacznie przygląda. Rozglądając się wokoło zauważyłeś jak <b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id."</b> gwałtownie ucieka od Ciebie. Ciekawe jak długo obserwował Ciebie.', ".$strDate.", 'T')");
	error("<br />Przyglądałeś się przez chwilę ".$view->user." niestety, w pewnym momencie zauważył Ciebie. Szybko uciekłeś w cień. Zdobyte informacje:<br />".$strEquipment." (<a href=view.php?view=".$view->id.">Wróć</a>)");
      }
    else
      {
	$fltThief = round($view->level / 200.0, 2);
	$expgain = ceil($view->level / 5);
	checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
	$db -> Execute("UPDATE `players` SET `perception`=`perception`+0.01 WHERE `id`=".$view->id);
	$objEquipment = $db->Execute("SELECT `name`, `power`, `zr`, `szyb` FROM `equipment` WHERE `owner`=".$view->id." AND `status`='E'");
	$strEquipment = 'Założony ekwipunek:<ul>';
	while (!$objEquipment->EOF)
	  {
	    $strEquipment .= '<li>'.$objEquipment->fields['name'].' (+'.$objEquipment->fields['power'].') ';
	    if ($objEquipment->fields['zr'] != 0)
	      {
		$strEquipment .= '('.($objEquipment->fields['zr'] * -1).' zr) ';
	      }
	    if ($objEquipment->fields['szyb'] != 0)
	      {
		$strEquipment .= '('.$objEquipment->fields['szyb'].' szyb)';
	      }
	    $strEquipment .= '</li>';
	    $objEquipment->MoveNext();
	  }
	if ($strEquipment == 'Założony ekwipunek:<ul>')
	  {
	    $strEquipment = 'Nic nie nosi na sobie';
	  }
	else
	  {
	    $strEquipment .= '</ul>';
	  }
	$objEquipment->Close();
	$objGold = $db->Execute("SELECT `credits` FROM `players` WHERE `id`=".$view->id);
	$strEquipment .= 'Złota w sakiewce: '.$objGold->fields['credits'].'<br />';
	$objGold->Close();
	$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'W pewnym momencie odniosłeś nieprzyjemne wrażenie, że ktoś przygląda się Tobie. Rozglądając się na wszystkie strony, niestety nie zauważyłeś źródła niepokoju.', ".$strDate.", 'T')");
	error("<br />Przyglądałeś się przez dłużą chwilę ".$view->user.". Na szczęście nie zauważył twojej obecności. Zdobyte informacje:<br />".$strEquipment." (<a href=view.php?view=".$view->id.">Wróć</a>)");
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

    $chance = ($player->agility + $player->inteli + $player->thievery) - ($view->agility + $view->inteli + $view->perception);
    if ($chance < 1) 
    {
        $cost = 1000 * $player -> level;
        $expgain = ceil($view -> level / 10);
        checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', 0.01);
	$fltPerception = ($player->level / 100);
	$db->Execute("UPDATE `players` SET `perception`=`perception`+".$fltPerception." WHERE `id`=".$view->id);
        if ($player -> location != 'Lochy') 
        {
            $db -> Execute("UPDATE `players` SET `miejsce`='Lochy', `crime`=`crime`-1 WHERE `id`=".$player -> id);
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
    }
    if ($chance > 0 && $chance < 50) 
    {
        $db -> Execute("UPDATE `players` SET `crime`=`crime`-1 WHERE `id`=".$player -> id);
	$fltPerception = ($player->level / 200.0);
	$fltThief = ($view->level / 200.0);
        if ( $view -> credits > 0) 
        {
            $lost = ceil($view -> credits / 10);
            $expgain = $view -> level;
            checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$lost.", `perception`=`perception`+".$fltPerception." WHERE `id`=".$view->id);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$lost." WHERE `id`=".$player -> id);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".YOU_CATCH."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.NOT_CATCH.$lost.GOLD_COINS."', ".$strDate.", 'T')");
            error ("<br />".WHEN_YOU2.$lost.WHEN_YOU21." Zdobyłeś ".$fltThief." w umiejętności Złodziejstwo. (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        } 
	else 
        {
	    $db->Execute("UPDATE `players` SET `perception`=`perception`+".$fltPerception." WHERE `id`=".$view->id);
	    $expgain = ceil($view->level / 5);
	    checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".YOU_CATCH."<b><a href=view.php?view=".$player -> id.">".$player -> user.L_ID.$player -> id.EMPTY_BAG."', ".$strDate.", 'T')");
            error ("<br />".EMPTY_BAG2." Zdobyłeś ".$fltThief." w umiejętności Złodziejstwo. (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        }
    }
    if ($chance > 49) 
    {
        $db -> Execute("UPDATE `players` SET `crime`=`crime`-1 WHERE `id`=".$player -> id);
	$fltThief = ($view->level / 100.0);
        if ( $view -> credits > 0) 
        {
            $lost = ceil($view -> credits / 10);
            $expgain = ceil($view -> level * 10);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$lost.", `perception`=`perception`+0.01 WHERE `id`=".$view -> id);
            $db -> Execute("UPDATE `players` SET `credits`=`credits`+".$lost." WHERE `id`=".$player -> id);
            checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".YOU_CRIME.$lost.YOU_CRIME2."', ".$strDate.", 'T')");
            error ("<br />".SUCCESS.$lost.GOLD_COINS2." Zdobyłeś ".$fltThief." w umiejętności Złodziejstwo. (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
        } 
	else 
        {
	    $expgain = ceil($view->level / 5);
	    checkexp($player -> exp, $expgain, $player -> level, $player -> race, $player -> user, $player -> id, 0, 0, $player -> id, 'thievery', $fltThief);
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view -> id.",'".EMPTY_BAG3."', ".$strDate.", 'T')");
            error ("<br />".EMPTY_BAG4." Zdobyłeś ".$fltThief." w umiejętności Złodziejstwo. (<a href=\"view.php?view=".$_GET['view']."\">".BACK."</a>)");
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
