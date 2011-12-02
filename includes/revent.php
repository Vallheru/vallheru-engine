<?php
/**
 *   File functions:
 *   Random event in cities
 *
 *   @name                 : revent.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 02.12.2011
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

/**
 * Start event
 */
if ($player->revent == 0)
  {
    $db->Execute("INSERT INTO `revent` (`pid`, `state`, `qtime`, `location`) VALUES(".$player->id.", 1, 0, '')") or die($db->ErrorMsg());
    if ($player->gender == 'M')
      {
	$strGender = 'chłopcze';
	$strSuffix = 'byś';
      }
    else
      {
	$strGender = 'dziewczynko';
	$strSuffix = 'abyś';
      }
    if ($player->location == 'Altara')
      {
	$strTarget = $city2;
      }
    else
      {
	$strTarget = $city1a;
      }
    $strMessage = 'W pewnym momencie podchodzi do ciebie staruszek.<br /><i>Witaj '.$strGender.', zrobił'.$strSuffix.' coś dla mnie? Mam do przekazania pewną rzecz w '.$strTarget.'. Niestety, sam nie mogę się tam udać. Zrobisz to w moim imieniu?</i><br /><br /><form method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="submit" name="revent" value="Tak" /><br /><br /><input type="submit" name="revent" value="Nie" /></form>';
  }
/**
 * Accept/refuse event
 */
elseif ($player->revent == 1)
{
  if (!isset($_POST['revent']))
    {
      $db->Execute("DELETE FROM `revent` WHERE `pid`=".$player->id);
    }
  else
    {
      $arrLocs = array('Rynek', 'Arena Walk', 'Miejskie Plotki', 'Magiczna wieża', 'Biblioteka', 'Farma', 'Świątynia');
      if ($player->location == 'Altara')
	{
	  $strTarget = $city2;
	  $strCity = $city2;
	}
      else
	{
	  $strTarget = $city1a;
	  $strCity = $city1;
	}
      if ($_POST['revent'] == 'Tak')
	{
	  $intKey = array_rand($arrLocs);
	  $strMessage = 'Wyraźnie ucieszony, staruszek wręcza tobie sporych rozmiarów sakiewkę.<i>Dziękuję dziecko, proszę dostarcz to jak najszybciej do mojego przyjaciela w '.$strTarget.'. Najczęściej przebywa on w lokacji: '.$arrLocs[$intKey].'</i><br />Nucąc coś pod nosem, staruszek odchodzi.';
	  $intTime = rand(2, 5);
	  $db->Execute("UPDATE `revent` SET `state`=2, `qtime`=".$intTime.", `location`='".$strCity.";".$arrLocs[$intKey]."' WHERE `pid`=".$player->id);
	  $db->Execute("INSERT INTO `equipment` (`owner`, `name`, `type`, `cost`) VALUES (".$player->id.", 'Solidna sakiewka', 'Q', 10000)") or die($db->ErrorMsg());
	}
      else
	{
	  $intTime = rand(7, 18);
	  $db->Execute("UPDATE `revent` SET `state`=5, `qtime`=".$intTime." WHERE `pid`=".$player->id);
	  $strMessage = 'Mrucząc coś pod nosem o niewychowanej dzisiejszej młodzieży staruszek odchodzi.';
	}
    }
}
/**
 * Finish event
 */
elseif ($player->revent == 2)
{
  if ($player->gender == 'M')
      {
	$strSuffix = 'eś';
      }
    else
      {
	$strSuffix = 'aś';
      }
  $strMessage = 'Widzisz niemal identycznie wyglądającego staruszka, jak ten, który podarował tobie sakiewkę. Kiedy do niego podchodzisz, uśmiecha się wesoło do Ciebie.<br /><i>Witaj, cieszę się, że już tutaj dotarł'.$strSuffix.'. Ach, to moja sakiewka!</i>Szybko bierze od ciebie przedmiot.<i>Dziękuję bardzo, oto nieco złota za twoją fatygę.</i>Daje tobie parę sztuk złota i szybko odchodzi.';
  $db->Execute("UPDATE `players` SET `credits`=`credits`+1000 WHERE `id`=".$player->id);
  $db->Execute("DELETE FROM `equipment` WHERE `name`='Solidna sakiewka' AND `type`='Q' AND `owner`=".$player->id);
  $intTime = rand(3, 9);
  $db->Execute("UPDATE `revent` SET `state`=3, `qtime`=".$intTime." WHERE `pid`=".$player->id);
}

if (isset($strMessage))
  {
    $smarty -> assign(array("Message" => $strMessage, 
			    "Gamename" => $gamename, 
			    "Meta" => ''));
    $smarty -> display ('error1.tpl');
    require_once("includes/foot.php");
    exit;
  }

?>