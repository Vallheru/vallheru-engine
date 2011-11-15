<?php
/**
 *   File functions:
 *   Hunters guild - bestiary
 *
 *   @name                 : hunters.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 15.11.2011
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

$title = "Gildia Łowców";
require_once("includes/head.php");

if($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

$strMessage = '';
if (!isset($_GET['step']))
  {
    if ($player->location == 'Altara')
      {
	$objQuest = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='hunteraltara'");
      }
    else
      {
	$objQuest = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='hunterardulith'");
      }
    if ($objQuest->fields['value'])
      {
	$strQuest = 'Tablica ogłoszeń';
      }
    else
      {
	$strQuest = '';
      }
    $objQuest->Close();
    $smarty -> assign(array("Desc" => 'Niewielki, drewniany budynek stoi wsród drzew. Wokół niego kręcą się postacie okryte ciemnozielonymi płaszczami. Nad drzwiami budynku widzać symbol łowców królestwa.',
			    "Abestiary" => 'Bestiariusz znanych ziem',
			    "Aquest" => $strQuest));
    $_GET['step'] = '';
  }
elseif ($_GET['step'] == 'bestiary')
  {
    $arrMonsters = $db->GetAll("SELECT `id`, `name` FROM `monsters` WHERE `desc`!='' AND `location`='".$player->location."'");
    $smarty->assign(array("Bestiary" => 'Tutaj znajdują się opisy wszystkich potworów jakie można spotkać na terenie królestwa. Opisy te zbierane są przez najodważniejszych poszukiwaczy przygód naszej krainy. Jeżeli chcesz zawsze możesz <a href="proposals.php?type=E">zgłosić swój opis potwora</a>',
			  "Monsters" => $arrMonsters,
			  "Amount" => count($arrMonsters),
			  "Nodesc" => "Nie ma jeszcze opisów bestii w księdze."));
  }
elseif ($_GET['step'] == 'table')
{
  if ($player->location == 'Altara')
    {
      $objQuest = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='hunteraltara'");
    }
  else
    {
      $objQuest = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='hunterardulith'");
    }
  if (!$objQuest->fields['value'])
    {
      error("Nie ma zleceń w gildii.");
    }
  $strQuest = 'Przeglądając różnego rodzaju ogłoszenia związane z gildią, natykasz się na interesującą notatkę:<br />';
  $arrQuest = explode(';', $objQuest->fields['value']);
  switch ($arrQuest[0])
    {
    case 'F':
      $objMonster = $db->Execute("SELECT `name`, `level` FROM `monsters` WHERE `id`=".$arrQuest[1]);
      $strQuest .= '<i>Gildia Łowców poszukuje śmiałka, który zająłby się panoszącymi się w okolicy potworami. Szczegóły zlecenia:<br />Nazwa potwora: '.$objMonster->fields['name'].'<br />Ilość do zabicia: '.$arrQuest[2].'<br />Nagroda: '.($objMonster->fields['level'] * 10 * $arrQuest[2]).' sztuk złota</i>';
      $objMonster->Close();
      break;
    case 'I':
      $objItem = $db->Execute("SELECT `name`, `cost` FROM `equipment` WHERE `id`=".$arrQuest[1]);
      $strQuest .= '<i>Gildia Łowców poszukuje kogoś, kto dostarczy do gildii zapasy. Szczegóły zlecenia:<br />Przedmiot: '.$objItem->fields['name'].'<br />Ilość: '.$arrQuest[2].'<br />Nagroda: '.($objItem->fields['cost'] * 1.25 * $arrQuest[2]).' sztuk złota</i>';
      $objItem->Close();
      break;
    case 'L':
      $objMonster = $db->Execute("SELECT `name`, `level`, `lootnames` FROM `monsters` WHERE `id`=".$arrQuest[1]);
      $arrLoot = explode(';', $objMonster->fields['lootnames']);
      $strQuest .= '<i>Gildia Łowców poszukuje odważnej osoby, która dostarczy do gildii materiałów do badań na niebezpiecznymi potworami. Szczegóły zlecenia:<br />Nazwa potwora: '.$objMonster->fields['name'].'<br />Nazwa łupu: '.$arrLoot[$arrQuest[2]].'<br />Nagroda: '.($objMonster->fields['level'] * 100).' sztuk złota.</i>';
      $objMonster->Close();
      break;
    default:
      break;
    }
  $smarty->assign(array("Quest" => $strQuest,
			"Aback" => "Wróć",
			"Amade" => "Wykonaj zlecenie"));
}
elseif ($_GET['step'] == 'quest')
{
  if ($player->location == 'Altara')
    {
      $objQuest = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='hunteraltara'");
    }
  else
    {
      $objQuest = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='hunterardulith'");
    }
  if (!$objQuest->fields['value'])
    {
      error("Nie ma zleceń w gildii.");
    }
  if ($player->energy < 1)
    {
      error("Nie masz energii aby móc przyjąć to zadanie.");
    }
  if ($player->hp <= 0)
    {
      error("Nie możesz wykonywać zadań, kiedy jesteś martwy.");
    }
  $strQuest = 'Przeglądając różnego rodzaju ogłoszenia związane z gildią, natykasz się na interesującą notatkę:<br />';
  $arrQuest = explode(';', $objQuest->fields['value']);
  switch ($arrQuest[0])
    {
    case 'F':
      if ($player->fight == 0)
	{
	  $player->fight = $arrQuest[1];
	  $db->Execute("UPDATE `players` SET `fight`=".$player->fight." WHERE `id`=".$player->id);
	  $_SESSION['razy'] = $arrQuest[2];
	}
      $enemy1 = $db->Execute("SELECT * FROM `monsters` WHERE `id`=".$arrQuest[1]);
      if ($enemy1->fields['id'] != $player->fight)
	{
	  error("Zapomnij o tym.");
	}
      $span = ($enemy1 -> fields['level'] / $player -> level);
      if ($span > 2) 
	{
	  $span = 2;
	}
      $expgain1 = ceil(rand($enemy1 -> fields['exp1'],$enemy1 -> fields['exp2'])  * $span);
      $expgain = $expgain1;
      if ($_SESSION['razy'] > 1)
	{
	  for ($k = 2; $k <= $_SESSION['razy']; $k++)
	    {
	      $expgain = $expgain + ceil($expgain1 / 5 * (sqrt($k) + 4.5));
	    }
	}
      $goldgain = ceil((rand($enemy1 -> fields['credits1'],$enemy1 -> fields['credits2']) * $_SESSION['razy']) * $span);
      $enemy = array("strength" => $enemy1 -> fields['strength'], 
		     "agility" => $enemy1 -> fields['agility'], 
		     "speed" => $enemy1 -> fields['speed'], 
		     "endurance" => $enemy1 -> fields['endurance'], 
		     "hp" => $enemy1 -> fields['hp'], 
		     "name" => $enemy1 -> fields['name'], 
		     "id" => $enemy1 -> fields['id'], 
		     "exp1" => $enemy1 -> fields['exp1'], 
		     "exp2" => $enemy1 -> fields['exp2'], 
		     "level" => $enemy1 -> fields['level'],
		     "lootnames" => explode(";", $enemy1->fields['lootnames']),
		     "lootchances" => explode(";", $enemy1->fields['lootchances']));
      $arrehp = array();
      require_once("includes/funkcje.php");
      require_once("includes/turnfight.php");
      if (!isset ($_POST['action'])) 
        {
            turnfight ($expgain, $goldgain, '', "hunters.php?step=quest");
        } 
      else 
        {
            turnfight ($expgain, $goldgain, $_POST['action'], "hunters.php?step=quest");
        }
      $enemy1->Close();
      $fight = $db -> Execute("SELECT `fight`, `hp` FROM `players` WHERE `id`=".$player -> id);
      if ($fight -> fields['fight'] == 0) 
	{
	  if ($fight->fields['hp'] <= 0)
	    {
	      error("<br /><br />Niestety nie udało Ci się wykonać zadania.");
	    }
	  if (!isset($_SESSION['razy']))
	    {
	      $intGold = ($enemy['level'] * 10 * $arrQuest[2]);
	      $strMessage = '<br /><br />Dziękujemy za oczyszczenie okolicy z potworów.';
	    }
	  else
	    {
	      $intGold = -1;
	      $strMessage = 'Niestety, nie udało Ci się wykonać zadania.';
	    }
	}
      else
	{
	  $intGold = 0;
	}
      break;
    case 'I':
      $objItem = $db->Execute("SELECT `name`, `cost` FROM `equipment` WHERE `id`=".$arrQuest[1]);
      $objItem2 = $db->Execute("SELECT `id`, `amount` FROM `equipment` WHERE `owner`=".$player->id." AND `status`='U' AND `name`='".$objItem->fields['name']."'");
      if (!$objItem2->fields['id'])
	{
	  error("Nie posiadasz takiego przedmiotu w plecaku.");
	}
      if ($objItem2->fields['amount'] < $arrQuest[2])
	{
	  error("Nie posiadasz aż tyle takiego przedmiotu w plecaku.");
	}
      if ($objItem2->fields['amount'] == $arrQuest[2])
	{
	  $db->Execute("DELETE FROM `equipment` WHERE `id`=".$objItem2->fields['id']);
	}
      else
	{
	  $db->Execute("UPDATE `equipment` SET `amount`=`amount`-".$arrQuest[2]." WHERE `id`=".$objItem2->fields['id']);
	}
      $intGold = ($objItem->fields['cost'] * 1.25 * $arrQuest[2]);
      $objItem->Close();
      $objItem2->Close();
      $strMessage = 'Dziękujemy za dostarczenie zapasów do Gildii.';
      break;
    case 'L':
      $objMonster = $db->Execute("SELECT `level`, `lootnames` FROM `monsters` WHERE `id`=".$arrQuest[1]);
      $arrLoot = explode(';', $objMonster->fields['lootnames']);
      $objItem = $db->Execute("SELECT `id`, `amount` FROM `equipment` WHERE `owner`=".$player->id." AND `status`='U' AND `name`='".$arrLoot[$arrQuest[2]]."'");
      if (!$objItem->fields['id'])
	{
	  error("Nie posiadasz takiego przedmiotu w plecaku.");
	}
      $intGold = ($objMonster->fields['level'] * 100);
      if ($objItem->fields['amount'] == 1)
	{
	  $db->Execute("DELETE FROM `equipment` WHERE `id`=".$objItem->fields['id']);
	}
      else
	{
	  $db->Execute("UPDATE `equipment` SET `amount`=`amount`-1 WHERE `id`=".$objItem->fields['id']);
	}
      $objItem->Close();
      $strMessage = 'Dziękujemy za dostarczenie potrzebnych rzeczy do badań.';
      break;
    default:
      break;
    }
  if ($intGold == -1)
    {
      $db->Execute("UPDATE `players` SET `energy`=`energy`-1 WHERE `id`=".$player->id);
    }
  if ($intGold > 0)
    {
      $db->Execute("UPDATE `players` SET `energy`=`energy`-1, `credits`=`credits`+".$intGold." WHERE `id`=".$player->id);
      if ($player->location == 'Altara')
	{
	  $db->Execute("UPDATE `settings` SET `value`='' WHERE `setting`='hunteraltara'");
	  $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player->user." wykonał zadanie w Gildi Łowców w ".$city1a.".')");
	}
      else
	{
	  $db->Execute("UPDATE `settings` SET `value`='' WHERE `setting`='hunterardulith'");
	  $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player->user." wykonał zadanie w Gildi Łowców w ".$city2.".')");
	}
      $strMessage .= ' Dostajesz '.$intGold.' sztuk złota do ręki.';
    }
  $strMessage .= ' (<a href="hunters.php">Wróć</a>)';
}
else
  {
    checkvalue($_GET['step']);
    $objMonster = $db->Execute("SELECT `name`, `desc` FROM `monsters` WHERE `id`=".$_GET['step']." AND `location`='".$player->location."'");
    if ($objMonster->fields['desc'] == '')
      {
	error("Nie ma opisu tego potwora.");
      }
    $smarty->assign(array("Mname" => $objMonster->fields['name'],
			  "Mdesc" => $objMonster->fields['desc'],
			  "Aback" => 'Wróć do spisu potworów'));
  }

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Step" => $_GET['step'],
			"Message" => $strMessage));
$smarty -> display ('hunters.tpl');

require_once("includes/foot.php");
?>
