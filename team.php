<?php
/**
 *   File functions:
 *   Team management
 *
 *   @name                 : team.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 27.11.2012
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

$title = "Drużyna";
require_once("includes/head.php");

error('Czasowo niedostępne.');

//Create new team
if (isset($_GET['create']))
  {
    if ($player->team)
      {
	message('error', 'Nie możesz stworzyć nowej drużyny, ponieważ już należysz do jakiejś.');
      }
    else
      {
	$db->Execute("INSERT INTO `teams` (`leader`, `slot1`) VALUES(".$player->id.", ".$player->id.")");
	$objTeam = $db->Execute("SELECT `id` FROM `teams` WHERE `leader`=".$player->id);
	$player->team = $objTeam->fields['id'];
	$db->Execute("UPDATE `players` SET `team`=".$player->team." WHERE `id`=".$player->id);
	$objTeam->Close();
	message('success', 'Utworzyłeś nową drużynę. Możesz teraz do niej zaprosić maksymalnie 4 osoby.');
      }
  }

//Left team
if (isset($_GET['left']))
  {
    if ($player->team == 0)
      {
	message('error', 'Nie należysz do jakiejkolwiek drużyny.');
      }
    else
      {
	$objTeam = $db->Execute("SELECT * FROM `teams` WHERE `id`=".$player->team);
	$arrSlots = array('slot1', 'slot2', 'slot3', 'slot4', 'slot5');
	$strDate = $db -> DBDate($newdate);
	if ($objTeam->fields['leader'] == $player->id)
	  {
	    $arrIds = array();
	    $strSql = "INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES";
	    foreach ($arrSlots as $strSlot)
	      {
		if ($objTeam->fields[$strSlot] && $objTeam->fields[$strSlot] != $player->id)
		  {
		    
		    $arrIds[] = $objTeam->fields[$strSlot];
		    $strSql .= "(".$objTeam->fields[$strSlot].", '<a href=view.php?view=".$player->id.">".$player->user."</a> rozwiązał waszą drużynę.', ".$strDate.", 'D'),";
		  }
	      }
	    $strSql = rtrim($strSql, ",").';';
	    $db->Execute($strSql);
	    $db->Execute("UPDATE `players` SET `team`=0 WHERE `id` IN (".implode(', ', $arrIds).")");
	    $db->Execute("DELETE FROM `teams` WHERE `id`=".$player->team);
	    message('success', 'Rozwiązałeś swoją drużynę.');
	  }
	else
	  {
	    //Find player slot
	    foreach ($arrSlots as $strSlot)
	      {
		if ($objTeam->fields[$strSlot] == $player->id)
		  {
		    $db->Execute("UPDATE `teams` SET `".$strSlot."`=0 WHERE `id`=".$player->team);
		    break;
		  }
	      }
	    $strDate = $db -> DBDate($newdate);
	    $objLeader = $db->Execute("SELECT `leader` FROM `teams` WHERE `id`=".$player->team);
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objLeader->fields['leader'].", '<a href=view.php?view=".$player->id.">".$player->user."</a> opuścił twoją drużynę.', ".$strDate.", 'D')");
	    $objLeader->Close();
	    $player->team = 0;
	    $db->Execute("UPDATE `players` SET `team`=0 WHERE `id`=".$player->id);
	    message('success', 'Opuściłeś swoją drużynę.');
	  }
	$player->team = 0;
      }
  }

//Kick member from team
if (isset($_GET['kick']))
  {
    checkvalue($_GET['kick']);
    $blnValid = TRUE;
    if ($player->team == 0)
      {
	message('error', 'Nie posiadasz drużyny aby móc wyrzucać graczy.');
	$blnValid = FALSE;
      }
    if ($_GET['kick'] == $player->id)
      {
	message('error', 'Nie możesz wyrzucić sam siebie.');
	$blnValid = FALSE;
      }
    $objMember = $db->Execute("SELECT `team` FROM `players` WHERE `id`=".$_GET['kick']);
    if ($objMember->fields['team'] != 0 && $objMember->fields['team'] != $player->team)
      {
	message('error', 'Ten gracz nie należy do twojej drużyny');
	$blnValid = FALSE;
      }
    $objMember->Close();
    $objTeam = $db->Execute("SELECT * FROM `teams` WHERE `id`=".$player->team);
    if ($objTeam->fields['leader'] != $player->id)
      {
	message('error', 'Tylko przywódca drużyny może wyrzucać jej członków.');
	$blnValid = FALSE;
      }
    if ($blnValid)
      {
	$db->Execute("UPDATE `players` SET `team`=0 WHERE `id`=".$_GET['kick']);
	foreach (array('slot1', 'slot2', 'slot3', 'slot4', 'slot5') as $strSlot)
	  {
	    if ($objTeam->fields[$strSlot] == $_GET['kick'])
	      {
		$db->Execute("UPDATE `teams` SET `".$strSlot."`=0 WHERE `id`=".$player->team);
		break;
	      }
	  }
	$strDate = $db -> DBDate($newdate);
	$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_GET['kick'].", '<a href=view.php?view=".$player->id.">".$player->user."</a> wyrzucił ciebie z drużyny.', ".$strDate.", 'D')");
	message('success', 'Wyrzuciłeś gracza z drużyny.');
      }
    $objTeam->Close();
  }

if ($player->team == 0)
  {
    $smarty->assign(array('Noteam' => 'Nie posiadasz jeszcze drużyny. Możesz',
			  'Acreate' => 'stworzyć',
			  'Noteam2' => 'nową bądź przyłączyć się do istniejącej.'));
  }
 else
   {
     $objTeam = $db->Execute("SELECT * FROM `teams` WHERE `id`=".$player->team);
     $arrSlots = array('slot1', 'slot2', 'slot3', 'slot4', 'slot5');
     $arrIds = array();
     foreach ($arrSlots as $strSlot)
       {
	 if ($objTeam->fields[$strSlot])
	   {
	     $arrIds[] = $objTeam->fields[$strSlot];
	   }
       }
     $arrMembers = $db->GetAll("SELECT `id`, `user`, `energy`, `hp`, `max_hp`, `miejsce` FROM `players` WHERE `id` IN (".implode(', ', $arrIds).")");
     foreach ($arrMembers as &$arrMember)
       {
	 if ($arrMember['id'] == $objTeam->fields['leader'])
	   {
	     $strLeader = $arrMember['user'].' ID:'.$arrMember['id'];
	   }
	 $arrMember['user'] = $arrMember['user'].' ID:'.$arrMember['id'];
	 if ($arrMember['energy'] < 1)
	   {
	     $arrMember['status'] = 'Zmęczony';
	   }
	 elseif ($arrMember['hp'] < $arrMember['max_hp'])
	   {
	     $arrMember['status'] = 'Ranny';
	   }
	 elseif ($arrMember['hp'] == 0)
	   {
	     $arrMember['status'] = 'Martwy';
	   }
	 elseif ($arrMember['miejsce'] != $player->location)
	   {
	     $arrMember['status'] = 'Odłączył się';
	   }
	 else
	   {
	     $arrMember['status'] = 'Zdrowy';
	   }
	 if ($player->id == $objTeam->fields['leader'] && $arrMember['id'] != $player->id)
	   {
	     $arrMember['action'] = 'Wyrzuć';
	   }
	 else
	   {
	     $arrMember['action'] = '';
	   }
       }
     $smarty->assign(array('Aleft' => 'Opuść drużynę',
			   'Tleader' => 'Przywódca:',
			   'Tmembers' => 'Członkowie',
			   'Tstatus' => 'Status',
			   'Tactions' => 'Akcje',
			   'Tleader2' => $strLeader,
			   'Leaderid' => $objTeam->fields['leader'],
			   'Tmembers2' => $arrMembers,
			   "Tinfo" => 'Tutaj możesz sprawdzić status swojej drużyny, bądź, jeżeli jesteś jej przywódcą, zarządzać członkami drużyny. Członkowie martwi, zmęczeni bądź ci, którzy się odłączyli, nie są doliczani do statystyk drużyny. Uwaga: jeżeli opuszczasz drużynę bądź usuwasz z niej członka, nie będziesz pytany o potwierdzenie.'));
   }

$smarty->assign('Pteam', $player->team);
$smarty->display('team.tpl');
require_once("includes/foot.php");
?>
