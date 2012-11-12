<?php
/**
 *   File functions:
 *   Random event in cities
 *
 *   @name                 : revent.php                            
 *   @copyright            : (C) 2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 12.11.2012
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
    $intType = rand(0, 2);
    switch ($intType)
      {
      case 0:
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
	$db->Execute("INSERT INTO `revent` (`pid`, `state`, `qtime`, `location`) VALUES(".$player->id.", 1, 0, '')") or die($db->ErrorMsg());
	$strMessage = 'W pewnym momencie podchodzi do ciebie staruszek.<br /><i>Witaj '.$strGender.', zrobił'.$strSuffix.' coś dla mnie? Mam do przekazania pewną rzecz w '.$strTarget.'. Niestety, sam nie mogę się tam udać. Zrobisz to w moim imieniu?</i><br /><br /><form method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="submit" name="revent" value="Tak" /><br /><br /><input type="submit" name="revent" value="Nie" /></form>';
	break;
      case 1:
	$db->Execute("INSERT INTO `revent` (`pid`, `state`, `qtime`, `location`) VALUES(".$player->id.", 6, 0, '')") or die($db->ErrorMsg());
	$strMessage = 'Drogę zastępuje ci niewielka, przygarbiona postać. To Staruszek! Z pewnym niepokojem oczekujesz czego też będzie on od ciebie chciał. Może dostaniesz wypchaną sakiewkę albo prośbę o dostarczenie przesyłki, a może napadną cię bandyci?';
	$intRoll = rand(1, 100);
	if ($intRoll < 51)
	  {
	    $strMessage .= '<br /><br />Tymczasem Staruszek uważnie cie obserwuje i po chwili odchodzi mrucząc pod nosem coś o dzisiejszej młodzieży. Najwyraźniej twój wygląd nie wzbudził jego zaufania. Może już czas na kąpiel?<br /><br /><a href="'.$_SERVER['PHP_SELF'].'">Wróć</a>';
	    $intTime = rand(18, 36);
	    $db->Execute("UPDATE `revent` SET `state`=5, `qtime`=".$intTime." WHERE `pid`=".$player->id);
	  }
	else
	  {
	    $strMessage .= '<br /><br />Tymczasem Staruszek zadziera głowę tak, by móc ci spojrzeć prosto w oczy.<br />- Wspomóż biednego inwalidę wojennego! - krzyczy.<form method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="submit" name="revent" value="Wspomóż żebraka" /><br /><br /><input type="submit" name="revent" value="Precz żebraku!" /></form>';

	  }
	break;
      case 2:
	if ($player->race == 'Elf')
	  {
	    if ($player->gender == 'M')
	      {
		$strRace = 'elfie';
	      }
	    else
	      {
		$strRace = 'elfico';
	      }
	  }
	elseif ($player->race == 'Krasnolud')
	  {
	    if ($player->gender == 'M')
	      {
		$strRace = 'krasnoludzie';
	      }
	    else
	      {
		$strRace = 'krasnoludko';
	      }
	  }
	elseif ($player->race == 'Gnom')
	  {
	    if ($player->gender == 'M')
	      {
		$strRace = 'gnomie';
	      }
	    else
	      {
		$strRace = 'gnomico';
	      }
	  }
	elseif ($player->race == 'Hobbit')
	  {
	    $strRace = 'niziołku';
	  }
	elseif ($player->race == 'Jaszczuroczłek')
	  {
	    $strRace = 'jaszczuro';
	  }
	else
	  {
	    if ($player->gender == 'M')
	      {
		$strRace = 'człowieku';
	      }
	    else
	      {
		$strRace = 'kobieto';
	      }
	  }
	if ($player->gender == 'M')
	  {
	    $strSuffix = 'y';
	  }
	else
	  {
	    $strSuffix = 'a';
	  }
	$strMessage = 'Twoją uwagę przykuł widok małej, skulonej postaci przemykającej zaułkami miasta. Czy to może być osławiony Staruszek? Bez trudu doganiasz tę postać i zastępujesz jej drogę. <br />- Czego chcessz '.$strRace.'? - pyta.';
	if ($player->skills['perception'][1] > rand(10, 300))
	  {
	    $strMessage .= 'Dzięki swojej wysokiej spostrzegawczości dostrzegasz końcówkę ogona, która na ułamek sekundy wysunęła się spod płaszcza.<br />- Czy ty masz ogon? - pytasz zdziwiony.<br />Postać odrzuca płaszcz i twoim oczom ukazuje się szczurołak!<br />- Zginiessz '.$strRace.'! - wysykuje. Rozpoczyna się walka.';
	    $player->checkexp(array('perception' => 10), $player->id, 'skills');
	    $db->Execute("UPDATE `players` SET `fight`=4 WHERE `id`=".$player->id);
	    $player->fight = 4;
	    $player->revent = 8;
	    $intTime = rand(18, 36);
	    $db->Execute("INSERT INTO `revent` (`pid`, `state`, `qtime`, `location`) VALUES(".$player->id.", 8, ".$intTime.", '')") or die($db->ErrorMsg());
	  }
	else
	  {
	    $strMessage .= '- Może w czymś pomóc? - pytasz.<br />Czujesz na sobie nieprzychylny wzrok istoty.<br />- Komnaty królewsskie. Którędy?<br />Nieco zdziwion'.$strSuffix.' pytaniem wskazujesz kierunek. Indywiduum czym prędzej znika w mroku, a tobie pozostaje tylko poczucie nie do końca dobrze spełnionego obowiązku.<br /><br /><a href="'.$_SERVER['PHP_SELF'].'">Wróć</a>';
	    $player->checkexp(array('perception' => 1), $player->id, 'skills');
	    $intTime = rand(18, 36);
	    $db->Execute("INSERT INTO `revent` (`pid`, `state`, `qtime`, `location`) VALUES(".$player->id.", 5, ".$intTime.", '')") or die($db->ErrorMsg());
	  }
	break;
      default:
	break;
      }
  }
/**
 * Accept/refuse event
 */
elseif ($player->revent == 1 || $player->revent == 6)
{
  if (!isset($_POST['revent']))
    {
      $db->Execute("DELETE FROM `revent` WHERE `pid`=".$player->id);
    }
  else
    {
      if ($_POST['revent'] == 'Wspomóż żebraka' && $player->revent == 6)
	{
	  $intMoneys = rand(10, 100);
	  if ($player->credits < $intMoneys)
	    {
	      $strMessage = 'Orientujesz się, że nie masz przy sobie zbyt wielu pieniędzy aby dać coś żebrakowi. Machasz na wszystko ręką i idziesz dalej.<br /><br /><a href="'.$_SERVER['PHP_SELF'].'">Wróć</a>';
	      $intTime = rand(18, 36);
	      $db->Execute("UPDATE `revent` SET `state`=5, `qtime`=".$intTime." WHERE `pid`=".$player->id);
	    }
	  else
	    {
	      $strMessage = 'Wspomagasz inwalidę wojennego '.$intMoneys.' sztukami złota. Zadowolony weteran dziękuje i przysięga dozgonną wdzięczność.<br /><br /><a href="'.$_SERVER['PHP_SELF'].'">Wróć</a>';
	      $db->Execute("UPDATE `players` SET `credits`=`credits`-".$intMoneys." WHERE `id`=".$player->id);
	      $intTime = rand(3, 9);
	      $db->Execute("UPDATE `revent` SET `state`=7, `qtime`=".$intTime." WHERE `pid`=".$player->id);
	    }
	}
      elseif ($_POST['revent'] == 'Tak' && $player->revent == 1)
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
	  $intKey = array_rand($arrLocs);
	  $strMessage = 'Wyraźnie ucieszony, staruszek wręcza tobie sporych rozmiarów sakiewkę.<i>Dziękuję dziecko, proszę dostarcz to jak najszybciej do mojego przyjaciela w '.$strTarget.'. Najczęściej przebywa on w lokacji: '.$arrLocs[$intKey].'</i><br />Nucąc coś pod nosem, staruszek odchodzi.<br /><br /><a href="'.$_SERVER['PHP_SELF'].'">Wróć</a>';
	  $intTime = rand(2, 5);
	  $db->Execute("UPDATE `revent` SET `state`=2, `qtime`=".$intTime.", `location`='".$strCity.";".$arrLocs[$intKey]."' WHERE `pid`=".$player->id);
	  $db->Execute("INSERT INTO `equipment` (`owner`, `name`, `type`, `cost`) VALUES (".$player->id.", 'Solidna sakiewka', 'Q', 10000)") or die($db->ErrorMsg());
	}
      else
	{
	  $intTime = rand(18, 36);
	  $db->Execute("UPDATE `revent` SET `state`=5, `qtime`=".$intTime." WHERE `pid`=".$player->id);
	  if ($player->revent == 6)
	    {
	      $strMessage = '(przeganiasz Staruszka, który, wyraźnie smutny, odchodzi)<br /><br /><a href="'.$_SERVER['PHP_SELF'].'">Wróć</a>';
	    }
	  else
	    {
	      $strMessage = 'Mrucząc coś pod nosem o niewychowanej dzisiejszej młodzieży staruszek odchodzi.<br /><br /><a href="'.$_SERVER['PHP_SELF'].'">Wróć</a>';
	    }
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
  $strMessage = 'Widzisz niemal identycznie wyglądającego staruszka, jak ten, który podarował tobie sakiewkę. Kiedy do niego podchodzisz, uśmiecha się wesoło do Ciebie.<br /><i>Witaj, cieszę się, że już tutaj dotarł'.$strSuffix.'. Ach, to moja sakiewka!</i> Szybko bierze od ciebie przedmiot. <i>Dziękuję bardzo, oto nieco złota za twoją fatygę.</i> Daje tobie parę sztuk złota i szybko odchodzi.<br /><br /><a href="'.$_SERVER['PHP_SELF'].'">Wróć</a>';
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
    if ($player->revent != 8)
      {
	require_once("includes/foot.php");
	exit;
      }
  }
/**
 * Random event - fight
 */
if ($player->revent == 8)
{
  require_once("includes/turnfight.php");
  require_once("includes/funkcje.php");
  global $enemy;
  global $arrehp;
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
  $enemy1 = $db -> Execute("SELECT * FROM monsters WHERE id=".$player->fight);
  $intElevel = $enemy1->fields['strength'] + $enemy1->fields['agility'] + $enemy1->fields['speed'] + $enemy1->fields['endurance'] + $enemy1->fields['level'] + $enemy1->fields['hp'];
  $span = ($intElevel / $intPlevel);
  if ($span > 2) 
    {
      $span = 2;
    }
  $expgain = ceil($intElevel * $span);
  $goldgain = ceil($intElevel * $span);
  if (!isset($_SESSION['enemy']))
    {
      $enemy = array("strength" => $enemy1 -> fields['strength'], 
		     "agility" => $enemy1 -> fields['agility'], 
		     "speed" => $enemy1 -> fields['speed'], 
		     "endurance" => $enemy1 -> fields['endurance'], 
		     "hp" => $enemy1 -> fields['hp'], 
		     "name" => $enemy1 -> fields['name'], 
		     "level" => $enemy1 -> fields['level'],
		     "lootnames" => explode(";", $enemy1->fields['lootnames']),
		     "lootchances" => explode(";", $enemy1->fields['lootchances']),
		     "dmgtype" => $enemy1->fields['dmgtype'],
		     "resistance" => explode(";", $enemy1->fields['resistance']));
    }
  else
    {
      $enemy = $_SESSION['enemy'];
    }
  if (!isset ($_POST['action'])) 
    {
      turnfight($expgain, $goldgain, '', $_SERVER['PHP_SELF']);
    } 
  else 
    {
      turnfight($expgain, $goldgain, $_POST['action'], $_SERVER['PHP_SELF']);
    }
  $myhp = $db -> Execute("SELECT `hp`, `fight` FROM `players` WHERE `id`=".$player -> id);
  if ($myhp->fields['fight'] == 0)
    {
      unset($_SESSION['enemy']);
      $player->energy--;
      if ($player->energy < 0)
	{
	  $player->energy = 0;
	}
      $db -> Execute("UPDATE `players` SET `energy`=".$player->energy." WHERE `id`=".$player -> id);
      $intTime = rand(18, 36);
      $db->Execute("UPDATE `revent` SET `state`=5, `qtime`=".$intTime." WHERE `pid`=".$player->id);
      if ($myhp -> fields['hp'] == 0) 
	{
	  error("W zwolnionym tempie obserwujesz z niezwykłym spokojem, jak ostatni cios przeciwnika spada na ciebie. Przed twoimi oczami eksploduje najjaśniejsza gwiazda, a po chwili wszystko zapada w ciemność.");
	}
      else
	{
	  error("Ostatnim ciosem dobijasz konającego przeciwnika. Nie będzie się poczwara włóczyć po twoim mieście!");
	}
    }
  $myhp->Close();
  $enemy1 -> Close();
  require_once("includes/foot.php");
  exit;
}
?>