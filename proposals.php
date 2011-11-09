<?php
/**
 *   File functions:
 *   Proposals in game
 *
 *   @name                 : proposals.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 09.11.2011
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
			  "Hdesc" => "Linki automatycznie zamieniane są na klikalne. Możesz używać następujących znaczników BBCode:<br /><ul><li>[b]<b>Pogrubienie</b>[/b]</li><li>[i]<i>Kursywa</i><[/i]</li><li>[u]<u>Podkreślenie</u>[/u]</li><li>[color (angielska nazwa koloru (red, yellow, itp) lub kod HTML (#FFFF00, itp)]pokolorowanie tekstu[/color]</li><li>[center]wycentrowanie tekstu[/center]</li><li>[quote]cytat[/quote]</ul> Jeżeli chcesz użyć jakiś własnych znaczników np [imię gracza], [opis dla mężczyzn] itd, wyjaśnij dokładnie co masz na myśli w dodatkowych informacjach.",
			  "Tinfo" => "Dodatkowe informacje:",
			  "Hinfo" => "Tutaj możesz dołączyć wszelkie dodatkowe informacje na temat opisu. Np kiedy opis ma się pojawić (wieczorem, co drugie logowanie, tylko dla kobiet, itd), czy ma to być dodatkowy opis czy też zastępujący istniejący. Jeżeli użyłeś jakiś własnych znaczników w opisie, koniecznie dodaj ich wytłumaczenie tutaj. Im więcej dodatkowych informacji na temat opisu zamieścisz tutaj, tym większa szansa, że zostanie on dodany. Nie używaj tutaj jakichkolwiek znaczników, ponieważ zostaną one wykasowane.",
			  "Asend" => "Wyślij"));
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
	$_POST['desc'] = bbcodetohtml($_POST['desc']);
	$_POST['info'] = str_replace("'", "", strip_tags($_POST['info']));
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

$smarty->assign("Type", $_GET['type']);
$smarty->display('proposals.tpl');
require_once("includes/foot.php");
?>