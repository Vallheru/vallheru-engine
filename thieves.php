<?php
/**
 *   File functions:
 *   Thieves den, items, monuments and missions for thieves
 *
 *   @name                 : thieves.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 28.12.2011
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

$title = "Złodziejska Spelunka";
require_once("includes/head.php");

if($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

if ($player->clas != 'Złodziej')
  {
    error("Drogę zastępują tobie podejrzanie wyglądający osobnicy. <i>Dokąd to! Nic tutaj ciekawego nie znajdziesz, zwiewaj!</i><a href=");
  }

if ($player->gender == 'M')
  {
    $strSuffix = 'eś';
  }
 else
   {
     $strSuffix = 'aś';
   }

/**
 * Main menu
 */
if (!isset($_GET['step']))
  {
    $smarty->assign(array('Thiefinfo' => 'Wchodzisz do niewielkiego, drewnianego budynku. Już od drzwi uderza w Ciebie zapach potu, palonego fajkowego ziela oraz kiepskiej jakości alkoholu. Na moment wszystkie rozmowy cichną, kiedy bywalcy tego miejsca uważnie przyglądają się Tobie. Pokazujesz sekretny znak i po chwili wszystko wraca do normy. Podchodzisz do lady i mówisz do barmana',
			  'Amonuments' => 'Potrzebuję nieco informacji o mieszkańcach '.$gamename.'.',
			  'Aitems' => 'Potrzebuję narzędzi.',
			  'Amissions' => ''));
    $_GET['step'] = '';
  }
else
  {
    /**
     * Monuments
     */
    if ($_GET['step'] == 'monuments')
      {
	/**
	 * Function to get data from database and return array of top 10 players' names, ID's and skill values. 
	 */
	function topplayers($strDbfield)
	{
	  global $db;
	  global $arrTags;
	  
	  if ($strDbfield != 'mpoints')
	    {
	      $objTop = $db -> SelectLimit('SELECT `id`, `user`, `'.$strDbfield.'`, `tribe` FROM `players` ORDER BY `'.$strDbfield.'` DESC', 10);
	    }
	  else
	    {
	      $objTop = $db -> SelectLimit("SELECT `id`, `user`, `".$strDbfield."`, `tribe` FROM `players` WHERE `klasa`='Złodziej' ORDER BY `".$strDbfield."` DESC", 10) or die($db->ErrorMsg());
	    }

	  while (!$objTop -> EOF) 
	    {
	      $objTop->fields['user'] = $arrTags[$objTop->fields['tribe']][0].' '.$objTop->fields['user'].' '.$arrTags[$objTop->fields['tribe']][1];
	      $arrTop[] = array('user' => $objTop -> fields['user'],
				'id' => $objTop -> fields['id'],
				'value' => $objTop -> fields[$strDbfield]);
	      $objTop -> MoveNext();
	    }
	  $objTop -> Close();
	  return $arrTop;
	}

	$arrayMonumentTitles = array("Najwięcej złota (w sakiewce)", "Najwięcej złota (w banku)", "Najwyższa Spostrzegawczość", "Najwyższe Złodziejstwo", 'Wykonanych Zadań');

	$arrayMonumentDescriptions = array("Złoto (w sakiewce)", "Złoto (w banku)", "Spostrzegawczość", "Złodziejstwo", 'Zadań');
        
	$arrayMonuments = array(topplayers('credits'), topplayers('bank'), topplayers('perception'), topplayers('thievery'), topplayers('mpoints'));
	$smarty->assign(array('Titles' => $arrayMonumentTitles,
			      'Descriptions' => $arrayMonumentDescriptions,
			      'Monuments' => $arrayMonuments,
			      'Mname' => "Imię (ID)",
			      "Minfo" => 'Oto najświeższe dane wywiadowcze zebrane przez członków naszego bractwa'));
      }
    /**
     * Thieves tools shop
     */
    elseif ($_GET['step'] == 'shop')
      {
	$smarty->assign(array('Sinfo' => 'Barman wskazuje Tobie głową drogę na zaplecze. Tam w niewielkim pokoiku siedzi pokryty bliznami gnom. Kiedy się zbliżasz do niego, podnosi na ciebie wzrok i pyta: <i>Chcesz kupić narzędzia do pracy? 200 sztuk złota jedno.</i>',
			      'Abuy' => 'Kupię',
			      'Tamount' => 'sztuk narzędzi.'));
	if (isset($_GET['buy']))
	  {
	    checkvalue($_POST['amount']);
	    $intGold = 200 * $_POST['amount'];
	    if ($player->credits < $intGold)
	      {
		message('error', 'Nie masz tylu sztuk złota przy sobie');
	      }
	    else
	      {
		$db->Execute("UPDATE `players` SET `credits`=`credits`-".$intGold." WHERE `id`=".$player->id);
		$objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='Wytrychy z miedzi' AND `power`=10 AND `cost`=20 AND `wt`=10 AND `maxwt`=10 AND `type`='E'");
		if (!$objTest->fields['id'])
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", 'Wytrychy z miedzi', 10, 'E', 20, 0, 10, 1, 10, ".$_POST['amount'].", 'N', 0, 0, 'N', 20)") or die($db->ErrorMsg());
		  }
		else
		  {
		    $db->Execute("UPDATE `equipment` SET `amount`=`amount`+".$_POST['amount']." WHERE `id`=".$objTest->fields['id']);
		  }
		$objTest->Close();
		message('success', 'Zakupił'.$strSuffix.' '.$_POST['amount'].' sztuk(i) wytrychów za '.$intGold.' sztuk złota.');
	      }
	  }
      }
  }

/**
 * Assign variables to template and display page
 */
$smarty->assign(array('Step' => $_GET['step'],
		      'Aback' => 'Wróć'));
$smarty->display('thieves.tpl');

require_once("includes/foot.php");
?>
