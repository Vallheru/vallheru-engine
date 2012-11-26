<?php
/**
 *   File functions:
 *   Game time
 *
 *   @name                 : team.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 26.11.2012
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
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$view->id.", ''<a href=view.php?view=".$player->id.">".$player->user."</a> opuścił twoją drużynę.', ".$strDate.", 'D')");
	    $player->team = 0;
	    $db->Execute("UPDATE `players` SET `team`=0 WHERE `id`=".$player->id);
	    message('success', 'Opuściłeś swoją drużynę.');
	  }
	$player->team = 0;
      }
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
     $arrMembers = $db->GetAll("SELECT `id`, `user`, `energy`, `hp`, `max_hp` FROM `players` WHERE `id` IN (".implode(', ', $arrIds).")");
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
	 else
	   {
	     $arrMember['status'] = 'Zdrowy';
	   }
	 if ($player->id == $objTeam->fields['leader'])
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
			   'Tmembers2' => $arrMembers));
   }

$smarty->assign('Pteam', $player->team);
$smarty->display('team.tpl');
require_once("includes/foot.php");
?>
