<?php
/**
 *   File functions:
 *   Proposals in game
 *
 *   @name                 : proposals.php                            
 *   @copyright            : (C) 2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
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

$title = "Propozycje";
require_once("includes/head.php");

if (!isset($_GET['type']))
  {
    error('Zapomnij o tym.');
  }

/**
 * Add proposal about location description
 */
if ($_GET['type'] == 'D')
  {
    $arrLocations = $arrTitle;
    $arrLocations[13] = $city1;
    $arrRemove = array(0, 1, 2, 5, 6, 7, 12, 15, 16, 19, 21, 28, 39, 43, 45, 46, 47, 49, 56, 59, 63, 64, 82);
    foreach ($arrRemove as $intRemove)
      {
	unset($arrLocations[$intRemove]);
      }
    $arrLocations = array_values($arrLocations);
    $arrLocations[] = $city2;
    $arrLocations[] = 'Avan Tirith';
    $arrLocations[] = 'Polana drwali';
    $arrLocations[] = 'Brzoza przeznaczenia';
    $arrLocations[] = 'Leśny skład';
    $arrLocations[] = 'Korzeń przeznaczenia';
    $smarty->assign(array("Tselect" => "Wybierz lokację, której opis chcesz zaproponować",
			  "Loptions" => $arrLocations,
			  "Tdesc" => "Opis:",
			  "Hdesc" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i>[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul> Jeżeli chcesz użyć jakiś własnych znaczników np [imię gracza], [opis dla mężczyzn] itd, wyjaśnij dokładnie co masz na myśli w dodatkowych informacjach.",
			  "Tinfo" => "Dodatkowe informacje:",
			  "Hinfo" => "Tutaj możesz dołączyć wszelkie dodatkowe informacje na temat opisu. Np kiedy opis ma się pojawić (wieczorem, co drugie logowanie, tylko dla kobiet, itd), czy ma to być dodatkowy opis czy też zastępujący istniejący. Jeżeli użyłeś jakiś własnych znaczników w opisie, koniecznie dodaj ich wytłumaczenie tutaj. Im więcej dodatkowych informacji na temat opisu zamieścisz tutaj, tym większa szansa, że zostanie on dodany. Nie używaj tutaj jakichkolwiek znaczników, ponieważ zostaną one wykasowane.",
			  "Asend" => "Wyślij",
			  "Pdesc" => '',
			  "Desc" => '',
			  "Info" => '',
			  "Apreview" => "Podgląd"));
    if (isset($_GET['send']))
      {
	if (empty($_POST['desc']) || empty($_POST['info']))
	  {
	    error('Wypełnij wszystkie pola.');
	  }
	$intLoc = intval($_POST['loc']);
	if ($intLoc < 0 || $intLoc > count($arrLocations))
	  {
	    error("Zapomnij o tym.");
	  }
	require_once('includes/bbcode.php');
	$strDesc = $_POST['desc'];
	$_POST['desc'] = bbcodetohtml($_POST['desc']);
	$_POST['info'] = str_replace("'", "", strip_tags($_POST['info']));
	if ($_POST['sdesc'] == 'Wyślij')
	  {
	    $db->Execute("INSERT INTO `proposals` (`pid`, `type`, `name`, `data`, `info`) VALUES (".$player->id.", 'D', '".$arrLocations[$intLoc]."', '".$_POST['desc']."', '".$_POST['info']."')");
	    $objStaff = $db -> Execute("SELECT `id` FROM `players` WHERE `rank`='Admin'");
	    $strDate = $db -> DBDate($newdate);
	    while (!$objStaff->EOF) 
	      {
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objStaff->fields['id'].", 'Zgłoszono opis lokacji.', ".$strDate.", 'A')") or die($db->ErrorMsg());
		$objStaff->MoveNext();
	      }
	    $objStaff->Close();
	    error("Zgłosiłeś swoją propozycję opisu lokacji. <a href=account.php>Wróć do opcji konta</a>");
	  }
	else
	  {
	    $smarty->assign(array("Pdesc" => $_POST['desc'].'<br /><br />',
				  "Desc" => $strDesc,
				  "Info" => $_POST['info']));
	  }
      }
  }
/**
 * Add proposal about new item
 */
elseif ($_GET['type'] == 'I')
{
  $arrTypes = array('W' => 'Broń jednoręczna',
		    'W2' => 'Broń dwuręczna',
		    'B' => 'Łuk',
		    'R' => 'Strzały',
		    'H' => 'Hełm',
		    'A' => 'Zbroja',
		    'L' => 'Nagolenniki',
		    'S' => 'Tarcza');
  $smarty->assign(array("Tname" => "Nazwa przedmiotu:",
			"Ninfo" => "Nazwa przedmiotu nie może być taka sama jak już istniejący przedmiot.",
			"Ttype" => "Typ:",
			"Tinfo" => "Broń dwuręczna zadaje nieco większe obrażenia niż broń jednoręczna.",
			"Tlevel" => "Poziom:",
			"Linfo" => "Od poziomu przedmiotu będzie zależała jego podstawowa premia.",
			"Toptions" => $arrTypes,
			"Asend" => "Wyślij"));
  if (isset($_GET['send']))
    {
      if (empty($_POST['iname']) || empty($_POST['level']))
	{
	  error("Wypełnij wszystkie pola.");
	}
      if (!array_key_exists($_POST['itype'], $arrTypes))
	{
	  error("Zapomnij o tym.");
	}
      checkvalue($_POST['level']);
      $_POST['iname'] = str_replace("'", "", strip_tags($_POST['iname']));
      $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=0 AND `name`='".$_POST['iname']."'");
      if ($objTest->fields['id'])
	{
	  error("Już istnieje przedmiot o takiej nazwie.");
	}
      $objTest->Close();
      $objTest = $db->Execute("SELECT `id` FROM `proposals` WHERE `name`='".$_POST['iname']."' AND `type`='I'");
      if ($objTest->fields['id'])
	{
	  error("Ktoś już zgłosił przedmiot o takiej nazwie.");
	}
      $objTest->Close();
      $db->Execute("INSERT INTO `proposals` (`pid`, `type`, `name`, `data`, `info`) VALUES (".$player->id.", 'I', '".$_POST['iname']."', '".$_POST['itype']."', '".$_POST['level']."')");
	$objStaff = $db -> Execute("SELECT `id` FROM `players` WHERE `rank`='Admin'");
	$strDate = $db -> DBDate($newdate);
	while (!$objStaff->EOF) 
	  {
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objStaff->fields['id'].", 'Zgłoszono nowy przedmiot.', ".$strDate.", 'A')") or die($db->ErrorMsg());
	    $objStaff->MoveNext();
	  }
	$objStaff->Close();
	error("Zgłosiłeś swoją propozycję nowego przedmiotu. <a href=account.php>Wróć do opcji konta</a>");
    }
}
/**
 * Add proposal about new monster
 */
elseif ($_GET['type'] == 'M')
{
  $arrValues = array('', '', '', '', '', 1, 1, 1, 1, 0, 0);
  $smarty->assign(array("Pinfo" => "Punkty służą do zmiany statystyk oraz wysokości zdobyczy (złota i doświadczenia) danego potwora. Osłabienie statystyk czy zwiększenie zdobyczy z niego wymaga posiadania punktów. Podniesienie statystyki czy zmniejszenie zdobyczy dodaje punkty. Aby móc zgłosić propozycję, liczba punktów musi być większa lub równa zero.",
			"Tpoints" => "Punkty:",
			"Points" => 0,
			"Tname" => "Nazwa:",
			"Tstr" => "Siła:",
			"Tagi" => "Zręczność:",
			"Tcon" => "Wytrzymałość:",
			"Tspeed" => "Szybkość:",
			"Soptions" => array("Wysoka (+1 punkt)", "Normalna (0 punktów)", "Niska (-1 punkt)"),
			"Loptions" => array("Dużo (-2 punkty)", "Normalnie (0 punktów)", "Mało (+2 punkty)"),
			"Asend" => "Wyślij",
			"Acheck" => "Sprawdź",
			"Tloot1" => "Nazwa 1 łupu:",
			"Tloot2" => "Nazwa 2 łupu:",
			"Tloot3" => "Nazwa 3 łupu:",
			"Tloot4" => "Nazwa 4 łupu:",
			"Tresistance" => "Odporność na żywioł:",
			"Tdmgtype" => "Typ obrażeń:",
			"Roptions" => array("Brak (0 punktów)", "Ogień (słaba) (0 punktów)", "Ogień (normalna) (-1 punkt)", "Ogień (silna) (-2 punkty)", "Woda (słaba) (0 punktów)", "Woda (normalna) (-1 punkt)", "Woda (silna) (-2 punkty)", "Powietrze (słaba) (0 punktów)", "Powietrze (normalna) (-1 punkt)", "Powietrze (silna) (-2 punkty)", "Ziemia (słaba) (0 punktów)", "Ziemia (normalna) (-1 punkt)", "Ziemia (silna) (-2 punkty)"),
			"Doptions" => array("Brak (0 punktów)", "Ogień (-1 punkt)", "Woda (-1 punkt)", "Powietrze (-1 punkt)", "Ziemia (-1 punkt)"),
			"Linfo" => "Łupy z potworów wykorzystywane są do produkcji elitarnego ekwipunku. Nazwa łupu powinna składać się z dwóch części: część ciała potwora oraz nazwa potwora. Na przykład: Palec Goblina, Kość Lisza, Odnóże Gigantycznego Pająka.",
			"Values" => $arrValues,
			"Tloc" => "Lokacja:",
			"Coptions" => array($city1, $city2)));
  if (isset($_GET['send']))
    {
      $_POST['loc'] = intval($_POST['loc']);
      if ($_POST['loc'] != 0 && $_POST['loc'] != 1)
	{
	  error("Zapomnij o tym.");
	}
      $arrText = array('mname', 'loot1', 'loot2', 'loot3', 'loot4');
      $arrStats = array('mstr', 'magi', 'mspeed', 'mcon');
      $arrValues = array();
      foreach ($arrText as $strText)
	{
	  $_POST[$strText] = str_replace("'", "", strip_tags($_POST[$strText]));
	  if (empty($_POST[$strText]))
	    {
	      error("Wypełnij wszystkie pola.");
	    }
	  $arrValues[] = $_POST[$strText];
	}
      $blnExists = FALSE;
      $objTest = $db->Execute("SELECT `id` FROM `monsters` WHERE `name`='".$_POST['mname']."'");
      if ($objTest->fields['id'])
	{
	  $blnExists = TRUE;
	}
      $objTest->Close();
      $objTest = $db->Execute("SELECT `id` FROM `proposals` WHERE `name`='".$_POST['mname']."' AND `type`='M'");
      if ($objTest->fields['id'])
	{
	  $blnExists = TRUE;
	}
      $objTest->Close();
      $intPoints = 0;
      foreach ($arrStats as $strOption)
	{
	  $_POST[$strOption] = intval($_POST[$strOption]);
	  switch ($_POST[$strOption])
	    {
	    case 0:
	      $intPoints ++;
	      break;
	    case 1:
	      break;
	    case 2:
	      $intPoints --;
	      break;
	    default:
	      error("Zapomnij o tym.");
	      break;
	    }
	  $arrValues[] = $_POST[$strOption];
	}
      $_POST['mres'] = intval($_POST['mres']);
      switch ($_POST['mres'])
	{
	case 0:
	case 1:
	case 4:
	case 7:
	case 10:
	  break;
	case 2:
	case 5:
	case 8:
	case 11:
	  $intPoints --;
	  break;
	case 3:
	case 6:
	case 9:
	case 12:
	  $intPoints -= 2;
	  break;
	default:
	    error("Zapomnij o tym.");
	  break;
	}
      $arrValues[] = $_POST['mres'];
      $_POST['mdmg'] = intval($_POST['mdmg']);
      if ($_POST['mdmg'] >= 0 && $_POST['mdmg'] < 5)
	{
	  if ($_POST['mdmg'] > 0)
	    {
	      $intPoints --;
	    }
	  $arrValues[] = $_POST['mdmg'];
	}
      else
	{
	  error('Zapomnij o tym.');
	}
      if ($_POST['smon'] == 'Sprawdź')
	{
	  if ($blnExists)
	    {
	      $arrValues[0] = 'Potwór istnieje bądź został zgłoszony.';
	    }
	  $smarty->assign(array("Values" => $arrValues,
				"Points" => $intPoints));
	}
      else
	{
	  if ($intPoints < 0)
	    {
	      error("Liczba punktów musi być większa lub równa zero.");
	    }
	  if ($blnExists)
	    {
	      error("Istnieje już potwór o tej nazwie bądź ktoś zgłosił potwora o tej samej nazwie.");
	    }
	  array_shift($arrText);
	  $arrOptions = array_merge($arrStats, $arrLoot, $arrText, array('mres', 'mdmg'));
	  $strData = '';
	  foreach ($arrOptions as $strOption)
	    {
	      $strData .= $_POST[$strOption].";";
	    }
	  $db->Execute("INSERT INTO `proposals` (`pid`, `type`, `name`, `data`, `info`) VALUES (".$player->id.", 'M', '".$_POST['mname']."', '".$strData."', '".$_POST['loc']."')");
	  $objStaff = $db -> Execute("SELECT `id` FROM `players` WHERE `rank`='Admin'");
	  $strDate = $db -> DBDate($newdate);
	  while (!$objStaff->EOF) 
	    {
	      $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objStaff->fields['id'].", 'Zgłoszono nowego potwora.', ".$strDate.", 'A')") or die($db->ErrorMsg());
	      $objStaff->MoveNext();
	    }
	  $objStaff->Close();
	  error("Zgłosiłeś swoją propozycję nowego potwora. <a href=account.php>Wróć do opcji konta</a>");
	}
    }
}
/**
 * Add proposal for new question on bridge of death
 */
elseif($_GET['type'] == 'B')
{
  $smarty->assign(array("Tquestion" => "Pytanie:",
			"Tanswer" => "Odpowiedź:",
			"Tinfo" => "Nie używaj jakichkolwiek znaczników HTML czy BBCode w tekście, ponieważ zostaną one i tak usunięte.",
			"Asend" => "Wyślij"));
  if (isset($_GET['send']))
    {
      if (empty($_POST['question']) || empty($_POST['answer']))
	{
	  error("Wypełnij wszystkie pola.");
	}
      $_POST['question'] = str_replace("'", "", strip_tags($_POST['question']));
      $_POST['answer'] = str_replace("'", "", strip_tags($_POST['answer']));
      $db->Execute("INSERT INTO `proposals` (`pid`, `type`, `name`, `data`, `info`) VALUES (".$player->id.", 'B', 'Most', '".$_POST['question']."', '".$_POST['answer']."')");
      $objStaff = $db -> Execute("SELECT `id` FROM `players` WHERE `rank`='Admin'");
      $strDate = $db -> DBDate($newdate);
      while (!$objStaff->EOF) 
	{
	  $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objStaff->fields['id'].", 'Zgłoszono nowe pytanie na Moście Śmierci.', ".$strDate.", 'A')") or die($db->ErrorMsg());
	  $objStaff->MoveNext();
	}
      $objStaff->Close();
      error("Zgłosiłeś swoją propozycję pytania. <a href=account.php>Wróć do opcji konta</a>");
    }
}
/**
 * Add proposal about monster description
 */
elseif ($_GET['type'] == 'E')
  {
    $objMonsters = $db->Execute("SELECT `id`, `name` FROM `monsters`");
    $arrMonsters = array();
    while (!$objMonsters->EOF)
      {
	$arrMonsters[$objMonsters->fields['id']] = $objMonsters->fields['name'];
	$objMonsters->MoveNext();
      }
    $objMonsters->Close();
    $smarty->assign(array("Tselect" => "Wybierz potwora, którego opis chcesz zaproponować",
			  "Moptions" => $arrMonsters,
			  "Tdesc" => "Opis:",
			  "Hdesc" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i>[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul>",
			  "Asend" => "Wyślij",
			  "Pdesc" => '',
			  "Desc" => '',
			  "Apreview" => "Podgląd"));
    if (isset($_GET['send']))
      {
	if (empty($_POST['desc']))
	  {
	    error('Wypełnij wszystkie pola.');
	  }
	checkvalue($_POST['loc']);
	require_once('includes/bbcode.php');
	$strDesc = $_POST['desc'];
	$_POST['desc'] = bbcodetohtml($_POST['desc']);
	if ($_POST['sdesc'] == 'Wyślij')
	  {
	    $db->Execute("INSERT INTO `proposals` (`pid`, `type`, `name`, `data`, `info`) VALUES (".$player->id.", 'E', '".$arrMonsters[$_POST['loc']]."', '".$_POST['desc']."', '".$_POST['loc']."')");
	    $objStaff = $db -> Execute("SELECT `id` FROM `players` WHERE `rank`='Admin'");
	    $strDate = $db -> DBDate($newdate);
	    while (!$objStaff->EOF) 
	      {
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objStaff->fields['id'].", 'Zgłoszono opis potwora.', ".$strDate.", 'A')") or die($db->ErrorMsg());
		$objStaff->MoveNext();
	      }
	    $objStaff->Close();
	    error("Zgłosiłeś swoją propozycję opisu potwora. <a href=account.php>Wróć do opcji konta</a>");
	  }
	else
	  {
	    $smarty->assign(array("Pdesc" => $_POST['desc'].'<br /><br />',
				  "Desc" => $strDesc));
	  }
      }
  }
else
  {
    error("Zapomnij o tym.");
  }

$smarty->assign("Type", $_GET['type']);
$smarty->display('proposals.tpl');
require_once("includes/foot.php");
?>