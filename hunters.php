<?php
/**
 *   File functions:
 *   Hunters guild - bestiary
 *
 *   @name                 : hunters.php                            
 *   @copyright            : (C) 2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 04.07.2012
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
//Main menu
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
    $smarty -> assign(array("Desc" => 'Niewielki, drewniany budynek stoi wsród drzew. Wokół niego kręcą się postacie okryte ciemnozielonymi płaszczami. Nad drzwiami budynku widać symbol łowców królestwa.',
			    "Abestiary" => 'Bestiariusz znanych ziem',
			    "Aquest" => $strQuest));
    $_GET['step'] = '';
  }
//Bestiary
elseif ($_GET['step'] == 'bestiary')
  {
    $objMonsters = $db->Execute("SELECT `id`, `name`, `location` FROM `monsters` WHERE `desc`!='' ORDER BY `level`");
    $arrMonsters = array();
    $arrMonsters2 = array();
    while (!$objMonsters->EOF)
      {
	if ($objMonsters->fields['location'] == 'Altara')
	  {
	    $arrMonsters[] = array("id" => $objMonsters->fields['id'],
				   "name" => $objMonsters->fields['name']);
	  }
	else
	  {
	    $arrMonsters2[] = array("id" => $objMonsters->fields['id'],
				   "name" => $objMonsters->fields['name']);
	  }
	$objMonsters->MoveNext();
      }
    $objMonsters->Close();
    if (count($arrMonsters) > count($arrMonsters2))
      {
	$intDiff = count($arrMonsters) - count($arrMonsters2);
	for ($i = 0; $i < $intDiff; $i++)
	  {
	    $arrMonsters2[] = array("id" => 0,
				    "name" => '');
	  }
      }
    elseif (count($arrMonsters) < count($arrMonsters2))
      {
	$intDiff = count($arrMonsters2) - count($arrMonsters);
	for ($i = 0; $i < $intDiff; $i++)
	  {
	    $arrMonsters[] = array("id" => 0,
				    "name" => '');
	  }
      }
    
    $strMessage = '<br /><br /><a href="hunters.php">Wróć do gildii</a>';
    $smarty->assign(array("Bestiary" => 'Tutaj znajdują się opisy wszystkich potworów jakie można spotkać na terenie królestwa. Opisy te zbierane są przez najodważniejszych poszukiwaczy przygód naszej krainy. Jeżeli chcesz zawsze możesz <a href="proposals.php?type=E">zgłosić swój opis potwora</a>',
			  "Monsters" => $arrMonsters,
			  "Monsters2" => $arrMonsters2,
			  "Tmonsters" => "W okolicach ".$city1b,
			  "Tmonsters2" => "W okolicach ".$city2,
			  "Amount" => count($arrMonsters),
			  "Nodesc" => "Nie ma jeszcze opisów bestii w księdze."));
  }
//Random quests
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
    case 'B':
    case 'P':
      if ($arrQuest[0] == 'I')
	{
	  $objItem = $db->Execute("SELECT `name`, `cost` FROM `equipment` WHERE `id`=".$arrQuest[1]);
	  $arrMaterial = array('z miedzi', 'z brązu', 'z mosiądzu', 'z żelaza', 'ze stali');
	  $objItem->fields['name'] = str_replace('z miedzi', $arrMaterial[$arrQuest[3]], $objItem->fields['name']);
	  $arrBonus = array(1, 1.05, 1.1, 1.15, 1.2);
	  $fltBonus = $arrBonus[$arrQuest[3]];
	}
      elseif ($arrQuest[0] == 'B')
	{
	  $objItem = $db->Execute("SELECT `name`, `cost` FROM `bows` WHERE `id`=".$arrQuest[1]);
	  $arrMaterial = array('z leszczyny', 'z cisu', 'z wiązu', 'wzmocniony', 'kompozytowy');
	  $objItem->fields['name'] = str_replace('z leszczyny', $arrMaterial[$arrQuest[3]], $objItem->fields['name']);
	  $arrBonus = array(1, 1.05, 1.1, 1.15, 1.2);
	  $fltBonus = $arrBonus[$arrQuest[3]];
	}
      else
	{
	  $objItem = $db->Execute("SELECT `name`, `power` FROM `potions` WHERE `id`=".$arrQuest[1]);
	  $objItem->fields['cost'] = $objItem->fields['power'] * 3;
	  $fltBonus = 1;
	}
      $strQuest .= '<i>Gildia Łowców poszukuje kogoś, kto dostarczy do gildii zapasy. Szczegóły zlecenia:<br />Przedmiot: '.$objItem->fields['name'].'<br />Ilość: '.$arrQuest[2].'<br />Nagroda: '.(ceil($objItem->fields['cost'] * $fltBonus * $arrQuest[2])).' sztuk złota</i>';
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
//Doing quest
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
		     "lootchances" => explode(";", $enemy1->fields['lootchances']),
		     "resistance" => explode(";", $enemy1->fields['resistance']),
		     "dmgtype" => $enemy1->fields['dmgtype']);
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
	  if ($fight->fields['hp'] <= 0 || isset($_SESSION['ressurect']))
	    {
	      unset($_SESSION['ressurect']);
	      error("<br /><br />Niestety nie udało Ci się wykonać zadania.");
	    }
	  if (!isset($_SESSION['razy']))
	    {
	      $intGold = ($enemy['level'] * 10 * $arrQuest[2]);
	      $strMessage = '<br /><br />Dziękujemy za oczyszczenie okolicy z potworów.';
	    }
	  else
	    {
	      unset($_SESSION['razy']);
	      $intGold = -1;
	      $strMessage = 'Niestety, nie udało Ci się wykonać zadania. (<a href="hunters.php">Wróć</a>)';
	    }
	}
      else
	{
	  $intGold = 0;
	}
      break;
    case 'I':
    case 'B':
    case 'P':
      if ($arrQuest[0] == 'I')
	{
	  $objItem = $db->Execute("SELECT `name`, `cost` FROM `equipment` WHERE `id`=".$arrQuest[1]);
	  $arrMaterial = array('z miedzi', 'z brązu', 'z mosiądzu', 'z żelaza', 'ze stali');
	  $objItem->fields['name'] = str_replace('z miedzi', $arrMaterial[$arrQuest[3]], $objItem->fields['name']);
	  $arrBonus = array(1, 1.05, 1.1, 1.15, 1.2);
	  $fltBonus = $arrBonus[$arrQuest[3]];
	}
      elseif ($arrQuest[0] == 'B')
	{
	  $objItem = $db->Execute("SELECT `name`, `cost` FROM `bows` WHERE `id`=".$arrQuest[1]);
	  $arrMaterial = array('z leszczyny', 'z cisu', 'z wiązu', 'wzmocniony', 'kompozytowy');
	  $objItem->fields['name'] = str_replace('z leszczyny', $arrMaterial[$arrQuest[3]], $objItem->fields['name']);
	  $arrBonus = array(1, 1.05, 1.1, 1.15, 1.2);
	  $fltBonus = $arrBonus[$arrQuest[3]];
	}
      else
	{
	  $objItem = $db->Execute("SELECT `name`, `power` FROM `potions` WHERE `id`=".$arrQuest[1]);
	  $objItem->fields['cost'] = $objItem->fields['power'] * 3;
	  $fltBonus = 1;
	}
      if ($arrQuest[0] != 'P')
	{
	  $objItem2 = $db->Execute("SELECT `id`, `amount` FROM `equipment` WHERE `owner`=".$player->id." AND `status`='U' AND `name`='".$objItem->fields['name']."'");
	}
      else
	{
	  $objItem2 = $db->Execute("SELECT `id`, `amount` FROM `potions` WHERE `owner`=".$player->id." AND `status`='K' AND `name`='".$objItem->fields['name']."'");
	}
      if (!$objItem2->fields['id'])
	{
	  error("Nie posiadasz takiego przedmiotu w plecaku.");
	}
      if ($objItem2->fields['amount'] < $arrQuest[2])
	{
	  error("Nie posiadasz aż tyle takiego przedmiotu w plecaku.");
	}
      if ($arrQuest[0] != 'P')
	{
	  if ($objItem2->fields['amount'] == $arrQuest[2])
	    {
	      $db->Execute("DELETE FROM `equipment` WHERE `id`=".$objItem2->fields['id']);
	    }
	  else
	    {
	      $db->Execute("UPDATE `equipment` SET `amount`=`amount`-".$arrQuest[2]." WHERE `id`=".$objItem2->fields['id']);
	    }
	}
      else
	{
	  if ($objItem2->fields['amount'] == $arrQuest[2])
	    {
	      $db->Execute("DELETE FROM `potions` WHERE `id`=".$objItem2->fields['id']);
	    }
	  else
	    {
	      $db->Execute("UPDATE `potions` SET `amount`=`amount`-".$arrQuest[2]." WHERE `id`=".$objItem2->fields['id']);
	    }
	}
      $intGold = ceil($objItem->fields['cost'] * $fltBonus * $arrQuest[2]);
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
	  $strCity = 'altara';
	  $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player->user." wykonał zadanie w Gildi Łowców w ".$city1a.".')");
	}
      else
	{
	  $strCity = 'ardulith';
	  $db -> Execute("INSERT INTO `events` (`text`) VALUES('Gracz ".$player->user." wykonał zadanie w Gildi Łowców w ".$city2.".')");
	}
      $strMessage .= ' Dostajesz '.$intGold.' sztuk złota do ręki.  (<a href="hunters.php">Wróć</a>)';
      $objAmount = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='hunter".$strCity."amount'");
      $objAmount->fields['value'] --;
      //Generate new random mission
      if ($objAmount->fields['value'] > 0)
	{
	  $arrQtypes = array('F', 'I', 'L', 'B', 'P');
	  $strType = $arrQtypes[rand(0, 4)];
	  switch ($strType)
	    {
	    case 'F':
	      $objMonsters = $db->Execute("SELECT `id` FROM `monsters` WHERE `location`='".$player->location."'");
	      $arrMonsters = array();
	      while (!$objMonsters->EOF)
		{
		  $arrMonsters[] = $objMonsters->fields['id'];
		  $objMonsters->MoveNext();
		}
	      $intKey = array_rand($arrMonsters);
	      $intId = $arrMonsters[$intKey];
	      $objMonsters->Close();
	      $intAmount = rand(1, 10);
	      $strQuest = 'F;'.$intId.';'.$intAmount;
	      break;
	    case 'I':
	    case 'B':
	    case 'P':
	      if ($strType == 'I')
		{
		  $objItems = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=0");
		  $intKey2 = rand(0, 4);
		}
	      elseif ($strType == 'B')
		{
		  $objItems = $db->Execute("SELECT `id` FROM `bows` WHERE `type`='B'");
		  $intKey2 = rand(0, 4);
		}
	      else
		{
		  $objItems = $db->Execute("SELECT `id` FROM `potions` WHERE `owner`=0");
		  $intKey2 = 0;
		}
	      $arrItems = array();
	      while (!$objItems->EOF)
		{
		  $arrItems[] = $objItems->fields['id'];
		  $objItems->MoveNext();
		}
	      $objItems->Close();
	      $intKey = array_rand($arrItems);
	      $intId = $arrItems[$intKey];
	      $intAmount = rand(1, 10);
	      $strQuest = $strType.';'.$intId.';'.$intAmount.';'.$intKey2;
	      break;
	    case 'L':
	      $objMonsters = $db->Execute("SELECT `id` FROM `monsters` WHERE `location`='".$player->location."' AND `lootnames`!= ''");
	      $arrMonsters = array();
	      while (!$objMonsters->EOF)
		{
		  $arrMonsters[] = $objMonsters->fields['id'];
		  $objMonsters->MoveNext();
		}
	      $objMonsters->Close();
	      if (count($arrMonsters) > 0)
		{
		  $intKey = array_rand($arrMonsters);
		  $intId = $arrMonsters[$intKey];
		  $intPart = rand(0, 3);
		  $strQuest = 'L;'.$intId.';'.$intPart;
		}
	      else
		{
		  $strQuest = '';
		}
	      break;
	    default:
	      break;
	    }
	  $db->Execute("UPDATE `settings` SET `value`='".$strQuest."' WHERE `setting`='hunter".$strCity."'");
	}
      else
	{
	  $db->Execute("UPDATE `settings` SET `value`='' WHERE `setting`='hunter".$strCity."'");
	}
      $db->Execute("UPDATE `settings` SET `value`='".$objAmount->fields['value']."' WHERE `setting`='hunter".$strCity."amont'");
    }
}
//Monster description
else
  {
    checkvalue($_GET['step']);
    $objMonster = $db->Execute("SELECT `name`, `desc` FROM `monsters` WHERE `id`=".$_GET['step']);
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
