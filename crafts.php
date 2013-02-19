<?php
/**
 *   File functions:
 *   Crafts guild - random missions for craftsmen
 *
 *   @name                 : crafts.php                            
 *   @copyright            : (C) 2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 19.02.2013
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

$title = 'Cześnik';
require_once("includes/head.php");

if($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

if ($player->clas != 'Rzemieślnik')
  {
    error("Drogę zastępuje tobie straż. <i>Tylko członkowie cechów rzemieślniczych mają wstęp do tego budynku!</i><a href=");
  }

/**
 * Get job
 */
$objJob = $db->Execute("SELECT `craftmission` FROM `players` WHERE `id`=".$player->id);
if (isset($_GET['step']))
  {
    /**
     * Generate random task
     */
    if ($_GET['step'] == 'first')
      {
	if ($objJob->fields['craftmission'] <= 0)
	  {
	    error("Niestety, na chwilę obecną, nie mamy dla ciebie jakiegokolwiek zadania. Proszę wróć za jakiś czas. (<a href=city.php>Powrót do miasta</a>)");
	  }
	if ($player->hp <= 0)
	  {
	    error("Nie możesz wykonywać zadań, ponieważ jesteś martwy.");
	  }
	$objCraftskill = $db->Execute("SELECT `craftskill` FROM `players` WHERE `id`=".$player->id);
	if ($objCraftskill->fields['craftskill'] == '')
	  {
	    $arrSkills = array($player->skills['smelting'][1], $player->skills['lumberjack'][1], $player->skills['mining'], $player->skills['breeding'][1], $player->skills['jewellry'][1], $player->skills['herbalism'][1], $player->skills['alchemy'][1], $player->skills['carpentry'][1], $player->skills['smith'][1]);
	    $intIndex = array_search(max($arrSkills), $arrSkills);
	  }
	else
	  {
	    $arrSkills = array('smelting', 'lumberjack', 'mining', 'breeding', 'jewellry', 'herbalism', 'alchemy', 'carpentry', 'smith');
	    $intIndex = array_search($objCraftskill->fields['craftskill'], $arrSkills);
	  }
	$intRand = 3;
	while ($intRand == 3 || $intRand == 4)
	  {
	    $intRand = rand(0, 8);
	  }
	switch ($intIndex)
	  {
	  case 0:
	    $_SESSION['craft'] = array(0, 2, $intRand);
	    break;
	  case 1:
	    $_SESSION['craft'] = array(1, 2, $intRand);
	    break;
	  case 2:
	    $_SESSION['craft'] = array(2, 1, $intRand);
	    break;
	  case 3:
	    $_SESSION['craft'] = array(3, 5, $intRand);
	    break;
	  case 4:
	    $_SESSION['craft'] = array(4, 2, $intRand);
	    break;
	  case 5:
	    $_SESSION['craft'] = array(5, 6, $intRand);
	    break;
	  case 6:
	    $_SESSION['craft'] = array(6, 5, $intRand);
	    break;
	  case 7:
	    $_SESSION['craft'] = array(7, 2, $intRand);
	    break;
	  case 8:
	    $_SESSION['craft'] = array(8, 0, $intRand);
	    break;
	  default:
	    $_SESSION['craft'] = array(0, 1, $intRand);
	    break;
	  }
	$_SESSION['craftenergy'] = array();
	$_SESSION['craftindex'] = array();
	$arrInfo = array();
	for ($i = 0; $i < 3; $i++)
	  {
	    switch ($_SESSION['craft'][$i])
	      {
		//smelting
	      case 0:
		$arrOptions = array('miedzi', 'brązu', 'mosiądzu', 'żelaza', 'stali');
		$objSmelter = $db->Execute("SELECT `level` FROM `smelter` WHERE `owner`=".$player->id);
		if (!$objSmelter->fields['level'])
		  {
		    $intLevel = 0;
		  }
		else
		  {
		    $intLevel = rand(0, $objSmelter->fields['level'] - 1);
		  }
		$objSmelter->Close();
		$arrBillets = array(1, 2, 3, 5, 8);
		$_SESSION['craftenergy'][$i] = $arrBillets[$intLevel];
		$_SESSION['craftindex'][$i] = $intLevel;
		$arrInfo[$i] = 'Potrzebujemy hutnika, który wytopi nam nieco sztabek '.$arrOptions[$intLevel];
		break;
		//lumberjack
	      case 1:
		$arrOptions = array("sosnowego", "z leszczyny", "cisowego", "z wiązu");
		$objLumber = $db->Execute("SELECT `level` FROM `lumberjack` WHERE `owner`=".$player->id);
		if (!$objLumber->fields['level'])
		  {
		    $intLevel = 0;
		  }
		else
		  {
		    $intLevel = rand(0, $objLumber->fields['level'] - 1);
		  }
		$objLumber->Close();
		$arrBillets = array(1, 2, 3, 5, 8);
		$_SESSION['craftenergy'][$i] = $arrBillets[$intLevel];
		$_SESSION['craftindex'][$i] = $intLevel;
		$arrInfo[$i] = 'Potrzebujemy drwala, który zdobędzie dla nas nieco drewna '.$arrOptions[$intLevel];
		break;
		//mining
	      case 2:
		$arrOptions = array('rudy miedzi', 'cynku', 'cyny', 'rudy żelaza', 'brył węgla', 'adamantium', 'kryształów', 'meteorytu');
		$intLevel = rand(0, 7);
		$arrBillets = array(1, 2, 3, 5, 8, 10, 12, 14);
		$_SESSION['craftenergy'][$i] = $arrBillets[$intLevel];
		$_SESSION['craftindex'][$i] = $intLevel;
		$arrInfo[$i] = 'Potrzebujemy górnika, który zdobędzie dla nas nieco '.$arrOptions[$intLevel];
		break;
		//breeding
	      case 3:
		$_SESSION['craftenergy'][$i] = 5;
		$_SESSION['craftindex'][$i] = rand(1, 6);
		$strInfo = 'Potrzebujemy hodowcy, który zaopiekuje się naszymi chowańcami';
		break;
		//jeweller
	      case 4:
		$objRing = $db->Execute("SELECT `id`, `name`, `level` FROM `jeweller` WHERE `owner`=".$player->id." AND `name`!='pierścień' AND `level`<=".$player->skills['jewellry'][1]." ORDER BY RAND() LIMIT 1");
		if (!$objRing->fields['id'])
		  {
		    $objRing->Close();
		    $objRing = $db->Execute("SELECT `id`, `name`, `level` FROM `jeweller` WHERE `id`=1");
		  }
		$intLevel = $objRing->fields['level'];
		$_SESSION['craftindex'][$i] = $objRing->fields['id'];
		$_SESSION['craftenergy'][$i] = 2.5 * $intLevel;
		$arrInfo[$i] = 'Potrzebujemy jubilera, który wykona dla nas parę pierścieni typu: '.$objRing->fields['name'];
		$objRing->Close();
		break;
		//herbalist
	      case 5:
		$arrOptions = array('Illani', 'Illanias', 'Nutari', 'Dynallca', 'Nasiona Illani', 'Nasiona Illanias', 'Nasiona Nutari', 'Nasiona Dynallca');
		$intLevel = rand(0, 7);
		$_SESSION['craftenergy'][$i] = 5;
		$_SESSION['craftindex'][$i] = $intLevel;
		$arrInfo[$i] = 'Potrzebujemy zielarza, który ';
		if ($intLevel < 4)
		  {
		    $arrInfo[$i] .= 'będzie przez pewien czas doglądał naszych ziół, ';
		  }
		else
		  {
		    $arrInfo[$i] .= 'ususzy nam nieco ziół, ';
		  }
		$arrInfo[$i] .= 'konkretnie '.$arrOptions[$intLevel];
		break;
		//alchemy
	      case 6:
		$objPotion = $db->Execute("SELECT `id`, `name`, `level` FROM `alchemy_mill` WHERE `owner`=".$player->id." AND `level`<=".$player->skills['alchemy'][1]." ORDER BY RAND() LIMIT 1");
		if (!$objPotion->fields['id'])
		  {
		    $objPotion->Close();
		    $objPotion = $db->Execute("SELECT `id`, `name`, `level` FROM `alchemy_mill` WHERE `id`=5");
		  }
		$intLevel = $objPotion->fields['level'];
		$_SESSION['craftindex'][$i] = $objPotion->fields['id'];
		$_SESSION['craftenergy'][$i] = 2.5 * $intLevel;
		$arrInfo[$i] = 'Potrzebujemy alchemika, który wykona dla nas parę mikstur typu: '.$objPotion->fields['name'];
		$objPotion->Close();
		break;
		//fletcher
	      case 7:
		$objBows = $db->Execute("SELECT `id`, `name`, `level`, `type` FROM `mill` WHERE `owner`=".$player->id." AND `elite`=0 AND `level`<=".$player->skills['carpentry'][1]." ORDER BY RAND() LIMIT 1");
		if (!$objBows->fields['id'])
		  {
		    $objBows->Close();
		    $objBows = $db->Execute("SELECT `id`, `name`, `level`, `type` FROM `mill` WHERE `id`=1");
		  }
		$intLevel = $objBows->fields['level'];
		$_SESSION['craftindex'][$i] = $objBows->fields['id'];
		$_SESSION['craftenergy'][$i] = 2.5 * $intLevel;
		$arrInfo[$i] = 'Potrzebujemy stolarza, który wykona dla nas parę ';
		if ($objBows->fields['type'] == 'B')
		  {
		    $arrInfo[$i] .= 'łuków ';
		  }
		else
		  {
		    $arrInfo[$i] .= 'strzał ';
		  }
		$arrInfo[$i] .= 'typu: '.$objBows->fields['name'];
		$objBows->Close();
		break;
		//smith
	      case 8:
		$objSmith = $db->Execute("SELECT `id`, `name`, `level`, `type` FROM `smith` WHERE `owner`=".$player->id." AND `elite`=0 AND `level`<=".$player->skills['smith'][1]." ORDER BY RAND() LIMIT 1");
		$arrTypes = array('A', 'S', 'H', 'L', 'W', 'E');
		$arrNames = array('zbrój', 'tarcz', 'hełmów', 'nagolenników', 'broni', 'narzędzi');
		if (!$objSmith->fields['id'])
		  {
		    $objSmith->Close();
		    $objSmith = $db->Execute("SELECT `id`, `name`, `level`, `type` FROM `smith` WHERE `id`=18");
		  }
		$intLevel = $objSmith->fields['level'];
		$_SESSION['craftindex'][$i] = $objSmith->fields['id'];
		$intName = array_search($objSmith->fields['type'], $arrTypes);
		$_SESSION['craftenergy'][$i] = 2.5 * $intLevel;
		$arrInfo[$i] = 'Potrzebujemy kowala, który wykona dla nas parę '.$arrNames[$intName];
		$arrInfo[$i] .= ' typu: '.$objSmith->fields['name'];
		$objSmith->Close();
		break;
	      default:
		break;
	      }
	  }
	$objJob->fields['craftmission'] --;
	if ($objJob->fields['craftmission'] > 0)
	  {
	    $strRefresh = 'Odśwież listę (możesz odświeżyć jeszcze '.$objJob->fields['craftmission'].' razy).';
	  }
	else
	  {
	    $strRefresh = '';
	  }
	$smarty->assign(array('Jobinfo' => 'Przez dłuższą chwilę opowiadasz niziołkowi o swoich umiejętnościach. Ten co chwila przerywa, zadając jakieś pytania a następnie sprawdzając coś w papierach. W końcu, znajduje odpowiedni papier i mówi do ciebie:<br /><i>-Zdaje się, że mam coś dla ciebie</i>',
			      "Ayes" => "Przyjmuję zlecenie (koszt: ",
			      "Ayes2" => " energii)",
			      "Jobs" => $arrInfo,
			      "Jenergy" => $_SESSION['craftenergy'],
			      "Ano" => "Nie, dziękuję",
			      'Jobinfo2' => $strRefresh));
	$db->Execute("UPDATE `players` SET `craftmission`=`craftmission`-1 WHERE `id`=".$player->id);
      }
    /**
     * Finish task
     */
    elseif ($_GET['step'] != 'first' && $_GET['step'] != 'register')
      {
	if (!isset($_SESSION['craft']))
	  {
	    error('Zapomnij o tym.');
	  }
	if ($player->hp <= 0)
	  {
	    error("Nie możesz wykonywać zadań, ponieważ jesteś martwy.");
	  }
	$intIndex = intval($_GET['step']);
	if ($intIndex < 0 || $intIndex > 2)
	  {
	    error('Zapomnij o tym');
	  }
	if ($player->energy < $_SESSION['craftenergy'][$intIndex])
	  {
	    unset($_SESSION['craft'], $_SESSION['craftenergy'], $_SESSION['craftindex']);
	    error("Nie masz tyle energii.");
	  }
	$db->Execute("UPDATE `players` SET `energy`=`energy`-".$_SESSION['craftenergy'][$intIndex]."  WHERE `id`=".$player->id);
	if ($player->gender == 'M')
	  {
	    $strSuffix = 'eś';
	  }
	else
	  {
	    $strSuffix = 'aś';
	  }
	$strInfo2 = 'Pracował'.$strSuffix.' przez pewien czas przy ';
	$intRoll = rand(1, 100);
	$blnCase = FALSE;
	if ($intRoll < 6)
	  {
	    $blnCase = TRUE;
	    $intDamage = ceil(($player->max_hp / 100) * rand(1, 25));
	    $player->hp -= $intDamage;
	    if ($player->hp < 0)
	      {
		$player->hp = 0;
	      }
	    $db->Execute("UPDATE `players` SET `hp`=".$player->hp." WHERE `id`=".$player->id);
	  }
	switch ($_SESSION['craft'][$intIndex])
	  {
	    //smelting
	  case 0:
	    $arrTool = array('nowe miechy', 'lepsze miechy', 'miechów', 'lepszych miechów', 'S');
	    $arrOptions = array('miedzi', 'brązu', 'mosiądzu', 'żelaza', 'stali');
	    $arrBillets = array('copper', 'bronze', 'brass', 'iron', 'steel');
	    $intAmount = rand(1, 100);
	    $intExp = (($_SESSION['craftindex'][$intIndex] + 1) * 10);
	    $strSkill = 'smelting';
	    $strSkill2 = 'hutnictwo';
	    $strStat = 'condition';
	    $strStat2 = 'Kondycja';
	    $strInfo2 .= 'wytapianiu sztabek '.$arrOptions[$_SESSION['craftindex'][$intIndex]].'. ';
	    if (!$blnCase)
	      {
		$strInfo2 .= 'W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$arrOptions[$_SESSION['craftindex'][$intIndex]];
		$objTest = $db->Execute("SELECT `owner`, `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."` FROM `minerals` WHERE `owner`=".$player->id);
		if (!$objTest->fields['owner'])
		  {
		    $db->Execute("INSERT INTO `minerals` (`owner`, `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`) VALUES (".$player->id.", ".$intAmount.")");
		  }
		else
		  {
		    $db->Execute("UPDATE `minerals` SET `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`=`".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`+".$intAmount." WHERE `owner`=".$player->id);
		  }
		$objTest->Close();
	      }
	    else
	      {
		$strInfo2 .= 'W pewnym momencie, nie przypilnował'.$strSuffix.' temperatury pieca, który nagle wybuchł raniąc wszystkich dookoła.';
	      }
	    $intWarehouse = $intAmount;
	    $db->Execute("UPDATE `warehouse` SET `amount`=`amount`+'".$intWarehouse."' WHERE `mineral`='".$arrBillets[$_SESSION['craftindex'][$intIndex]]."'");
	    break;
	    //lumberjack
	  case 1:
	    $arrTool = array('nową piłę', 'lepszą piłę', 'piły', 'lepszej piły', 'L');
	    $arrOptions = array("sosnowego", "z leszczyny", "cisowego", "z wiązu");
	    $arrBillets = array('pine', 'hazel', 'yew', 'elm');
	    $intAmount = rand(1, 100);
	    $strInfo2 .= 'ścinaniu drewna '.$arrOptions[$_SESSION['craftindex'][$intIndex]].'. ';
	    if (!$blnCase)
	      {
		$strInfo2 .= 'W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$arrOptions[$_SESSION['craftindex'][$intIndex]];
		$objTest = $db->Execute("SELECT `owner`, `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."` FROM `minerals` WHERE `owner`=".$player->id);
		if (!$objTest->fields['owner'])
		  {
		    $db->Execute("INSERT INTO `minerals` (`owner`, `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`) VALUES (".$player->id.", ".$intAmount.")");
		  }
		else
		  {
		    $db->Execute("UPDATE `minerals` SET `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`=`".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`+".$intAmount." WHERE `owner`=".$player->id);
		  }
	    $objTest->Close();
	      }
	    else
	      {
		$strInfo2 .= 'Niestety przy jednym z drzew ustawił'.$strSuffix.' się ze złej strony. Spadło ono prosto na Ciebie.';
	      }
	    $intExp = (($_SESSION['craftindex'][$intIndex] + 1) * 10);
	    $strSkill = 'lumberjack';
	    $strSkill2 = 'drwalnictwo';
	    $strStat = 'strength';
	    $strStat2 = 'Siła';
	    $intWarehouse = $intAmount;
	    $db->Execute("UPDATE `warehouse` SET `amount`=`amount`+'".$intWarehouse."' WHERE `mineral`='".$arrBillets[$_SESSION['craftindex'][$intIndex]]."'");
	    break;
	    //mining
	  case 2:
	    $arrTool = array('nowy kilof', 'lepszy kilof', 'kilofu', 'lepszego kilofu', 'M');
	    $arrOptions = array('rudy miedzi', 'cynku', 'cyny', 'rudy żelaza', 'brył węgla', 'adamantium', 'kryształów', 'meteorytu');
	    $arrBillets = array('copperore', 'zincore', 'tinore', 'ironore', 'coal', 'adamantium', 'crystal', 'meteor');
	    $intAmount = rand(1, 100);
	    $strInfo2 .= 'wydobywaniu '.$arrOptions[$_SESSION['craftindex'][$intIndex]].'. ';
	    if (!$blnCase)
	      {
		$strInfo2 .= 'W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$arrOptions[$_SESSION['craftindex'][$intIndex]];
		$objTest = $db->Execute("SELECT `owner`, `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."` FROM `minerals` WHERE `owner`=".$player->id);
		if (!$objTest->fields['owner'])
		  {
		    $db->Execute("INSERT INTO `minerals` (`owner`, `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`) VALUES (".$player->id.", ".$intAmount.")");
		  }
		else
		  {
		    $db->Execute("UPDATE `minerals` SET `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`=`".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`+".$intAmount." WHERE `owner`=".$player->id);
		  }
		$objTest->Close();
	      }
	    else
	      {
		$strInfo2 .= 'Niezbyt poprawnie podstęplował'.$strSuffix.' tunel w skutek czego, zawalił się na wszystkich w okolicy.';
	      }
	    $intExp = (($_SESSION['craftindex'][$intIndex] + 1) * 10);
	    $strSkill = 'mining';
	    $strSkill2 = 'górnictwo';
	    $strStat = 'strength';
	    $strStat2 = 'Siła';
	    $intWarehouse = $intAmount;
	    $db->Execute("UPDATE `warehouse` SET `amount`=`amount`+'".$intWarehouse."' WHERE `mineral`='".$arrBillets[$_SESSION['craftindex'][$intIndex]]."'");
	    break;
	    //breeding
	  case 3:
	    $arrTool = array('nową uprząż', 'lepszą uprząż', 'uprzęży', 'lepszej uprzęży', 'B');
	    $intExp = 20 * rand(1, 5);
	    $strSkill = 'breeding';
	    $strSkill2 = 'hodowla';
	    $strStat = 'condition';
	    $strStat2 = 'Kondycja';
	    $intGen = rand(0, 1);
	    if ($intGen)
	      {
		$strGen = 'F';
	      }
	    else
	      {
		$strGen = 'M';
	      }
	    $strInfo2 .= 'chowańcach. ';
	    if (!$blnCase)
	      {
		$objCore = $db->Execute("SELECT * FROM `cores` WHERE `id`=".$_SESSION['craftindex'][$intIndex]);
		$strInfo2 .= 'W nagrodę otrzymał'.$strSuffix.' nowego chowańca: '.$objCore->fields['name'];
		$db -> Execute("INSERT INTO `core` (`owner`, `name`, `type`, `ref_id`, `power`, `defense`, `gender`) VALUES(".$player -> id.",'".$objCore->fields['name']."','".$objCore->fields['type']."',".$_SESSION['craftindex'][$intIndex].",".$objCore->fields['power'].",".$objCore->fields['defense'].", '".$strGen."')");
	      }
	    else
	      {
		$strInfo2 .= 'Jeden z chowańców wpadł w szał i stratował Ciebie.';
	      }
	    $objCore->Close();
	    break;
	    //jeweller
	  case 4:
	    $arrTool = array('nowy nożyk', 'lepszy nożyk', 'nożyka', 'lepszego nożyka', 'J');
	    $objRing = $db->Execute("SELECT * FROM `jeweller` WHERE `id`=".$_SESSION['craftindex'][$intIndex]);
	    if ($objRing->fields['id'] == 1)
	      {
		$intAmount = rand(1, 20);
		$intCost = 3;
		$intPower = 0;
		$strStat = 'agility';
		$strStat2 = 'Zręczność';
	      }
	    else
	      {
		$arrStats = array(' siły', ' zręczności', ' inteligencji', ' szybkości', ' kondycji', ' siły woli');
		$intAmount = 1;
		$intStat = rand(0, 5);
		$objRing->fields['name'] .= $arrStats[$intStat];
		$intCost = $objRing->fields['cost'] / 20;
		$intPower = ceil($objRing->fields['bonus'] / 10);
		$arrStats2 = array('strength', 'agility', 'inteli', 'speed', 'condition', 'wisdom');
		$arrStats3 = array('Siła', 'Zręczność', 'Inteligencja', 'Szybkość', 'Kondycja', 'Siła Woli');
		$strStat = $arrStats2[$intStat];
		$strStat2 = $arrStats3[$intStat];
	      }
	    $intExp = ($objRing->fields['level'] * 20);
	    $strSkill = 'jewellry';
	    $strSkill2 = 'jubilerstwo';
	    $strInfo2 .= 'wykonywaniu pierścieni. ';
	    if (!$blnCase)
	      {
		$strInfo2 .= 'W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$objRing->fields['name'];
		$objTest = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player -> id." AND status='U' AND `name`='".$objRing->fields['name']."' AND `power`=".$intPower." AND `cost`=".$intCost);
		if (!$objTest -> fields['id'])
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `amount`) VALUES(".$player -> id.", '".$objRing->fields['name']."', '".$intPower."', 'U', '".$objRing->fields['type']."', '".$intCost."', '".$objRing->fields['level']."', '".$intAmount."')");
		  }
		else
		  {
		    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+".$intAmount." WHERE `id`=".$objTest -> fields['id']);
		  }
		$objTest -> Close();
	      }
	    else
	      {
		$strInfo2 .= 'W pewnym momencie pękła forma do wytwarzania pierścieni, wylewając surówkę prosto na twoją rękę.';
	      }
	    $objRing->Close();
	    break;
	    //herbalist
	  case 5:
	    $arrTool = array('nowy sierp', 'lepszy sierp', 'sierpa', 'lepszego sierpa', 'H');
	    $arrOptions = array('Illani', 'Illanias', 'Nutari', 'Dynallca', 'Nasiona Illani', 'Nasiona Illanias', 'Nasiona Nutari', 'Nasiona Dynallca');
	    $arrBillets = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	    $arrBillets2 = array('illani', 'illanias', 'nutari', 'dynallca', 'illani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	    if ($_SESSION['craftindex'][$intIndex] < 4)
	      {
		$strInfo2 .= 'hodowaniu ziół: ';
		$intAmount = rand(1, 60);
	      }
	    else
	      {
		$strInfo2 .= 'zdobywaniu nasion: ';
		$intAmount = rand(1, 30);
	      }
	    $strInfo2 .= $arrOptions[$_SESSION['craftindex'][$intIndex]].'. ';
	    if (!$blnCase)
	      {
		$strInfo2 .= 'W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$arrOptions[$_SESSION['craftindex'][$intIndex]];
		$objTest = $db->Execute("SELECT `gracz`, `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."` FROM `herbs` WHERE `gracz`=".$player->id) or die($db->ErrorMsg());
		if (!$objTest->fields['gracz'])
		  {
		    $db->Execute("INSERT INTO `herbs` (`gracz`, `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`) VALUES (".$player->id.", ".$intAmount.")");
		  }
		else
		  {
		    $db->Execute("UPDATE `herbs` SET `".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`=`".$arrBillets[$_SESSION['craftindex'][$intIndex]]."`+".$intAmount." WHERE `gracz`=".$player->id);
		  }
	    $objTest->Close();
	      }
	    else
	      {
		$strInfo2 .= 'Kiedy montował'.$strSuffix.' rusztowanie, to nagle pękło, przygniatając Ciebie.';
	      }
	    $intExp = (($_SESSION['craftindex'][$intIndex] + 1) * 10);
	    $strSkill = 'herbalism';
	    $strSkill2 = 'zielarstwo';
	    $strStat = 'agility';
	    $strStat2 = 'Zręczność';
	    $intWarehouse = $intAmount;
	    $db->Execute("UPDATE `warehouse` SET `amount`=`amount`+'".$intWarehouse."' WHERE `mineral`='".$arrBillets2[$_SESSION['craftindex'][$intIndex]]."'");
	    break;
	    //alchemy
	  case 6:
	    $arrTool = array('nowy moździerz', 'lepszy moździerz', 'moździerza', 'lepszego moździerza', 'A');
	    $objPotion = $db->Execute("SELECT * FROM `alchemy_mill` WHERE `id`=".$_SESSION['craftindex'][$intIndex]);
	    $intAmount = rand(1, 20);
	    $intExp = ($objPotion->fields['level'] * 12);
	    $strSkill = 'alchemy';
	    $strSkill2 = 'alchemia';
	    $strStat = 'wisdom';
	    $strStat2 = 'Siła Woli';
	    $intCost = ceil($objPotion->fields['cost'] / 200);
	    $strInfo2 .= 'warzeniu mikstur. ';
	    if (!$blnCase)
	      {
		$strInfo2 .= 'W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$objPotion->fields['name'];
		$objTest = $db -> Execute("SELECT `id` FROM `potions` WHERE `name`='".$objPotion->fields['name']."' AND `owner`=".$player->id." AND `status`='K' AND `power`=".$objPotion->fields['level']);
		if (!$objTest -> fields['id']) 
		  {
		    $objPotion2 = $db->Execute("SELECT `type`, `efect` FROM `potions` WHERE `name`='".$objPotion->fields['name']."' AND `owner`=0");
		    $db -> Execute("INSERT INTO potions (`owner`, `name`, `efect`, `power`, `amount`, `status`, `type`, `cost`) VALUES(".$player -> id.", '".$objPotion->fields['name']."', '".$objPotion2->fields['efect']."', ".$objPotion->fields['level'].", ".$intAmount.", 'K', '".$objPotion2->fields['type']."', ".$intCost.")");
		    $objPotion2->Close();
		  } 
		else 
		  {
		    $db -> Execute("UPDATE `potions` SET `amount`=`amount`+".$intAmount." WHERE `id`=".$objTest->fields['id']);
		  }
		$objTest -> Close();
	      }
	    else
	      {
		$strInfo2 .= 'Niestety pomyliły Ci się składniki i w pewnym momencie cały kociołek eksplodował, raniąc wszystkich w okolicy.';
	      }
	    $objPotion->Close();
	    break;
	    //fletcher
	  case 7:
	    $arrTool = array('nowy ciesak', 'lepszy ciesak', 'ciesaka', 'lepszego ciesaka', 'F');
	    $objBow = $db->Execute("SELECT * FROM `mill` WHERE `id`=".$_SESSION['craftindex'][$intIndex]);
	    $strInfo2 .= 'wykonywaniu ';
	    $intExp = ($objBow->fields['level'] * 10);
	    $strSkill = 'carpentry';
	    $strSkill2 = 'stolarstwo';
	    $strStat = 'agility';
	    $strStat2 = 'Zręczność';
	    if ($objBow->fields['type'] == 'B')
	      {
		$intCost = $objBow->fields['cost'] / 20;
		$objBow->fields['name'] .= ' z leszczyny';
		$intRepaircost = $objBow->fields['level'] * 2;
		$strInfo2 .= 'łuków. ';
		if (!$blnCase)
		  {
		    $strInfo2 .= 'W nagrodę dostał'.$strSuffix.' '.$objBow->fields['name'];
		    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$objBow->fields['name']."' AND `wt`=40 AND `type`='B' AND `status`='U' AND `owner`=".$player->id." AND `power`=0 AND `zr`=0 AND `szyb`=".$objBow->fields['level']." AND `maxwt`=40 AND `poison`=0 AND `cost`=".$intCost);
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$objBow->fields['name']."', 0, 'B', ".$intCost.", 0, 40, ".$objBow->fields['level'].", 40, 1, 'N', 0, ".$objBow->fields['level'].",'Y', ".$intRepaircost.")");
		      } 
		    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$test -> fields['id']);
		      }
		    $test -> Close();
		  }
		else
		  {
		    $strInfo2 .= 'Tnąc drewno na odpowiednie kawałki, w pewnym momencie straciłeś kontrolę nad piłą, która wgryzła się w Twoją nogę.';
		  }
	      }
	    else
	      {
		$intCost = $objBow->fields['level'] * 5;
		$strInfo2 .= 'strzał. ';
		if (!$blnCase)
		  {
		    $strInfo2 .= 'W nagrodę dostał'.$strSuffix.' 100 strzał typu: '.$objBow->fields['name'];
		    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$objBow->fields['name']."' AND `power`=".$objBow->fields['level']." AND `status`='U' AND `cost`=".$intCost." AND `poison`=0");
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `status`, `minlev`, `wt`) VALUES(".$player->id.", '".$objBow->fields['name']."', ".$objBow->fields['level'].", 'R', ".$intCost.", 'U', ".$objBow->fields['level'].", 100)") or die($db->ErrorMsg());
		      } 
		    else 
		      {
			$db -> Execute("UPDATE `equipment` SET `wt`=`wt`+100 WHERE `id`=".$test -> fields['id']);
		      }
		    $test -> Close();
		  }
		else
		  {
		    $strInfo2 .= 'Tnąc drewno na odpowiednie kawałki, w pewnym momencie stracił'.$strSuffix.' kontrolę nad piłą, która wgryzła się w Twoją nogę.';
		  }
	      }
	    $objBow->Close();
	    break;
	    //smith
	  case 8:
	    $arrTool = array('nowy młot', 'lepszy młot', 'młota', 'lepszego młota', 'I');
	    $objItem = $db->Execute("SELECT * FROM `smith` WHERE `id`=".$_SESSION['craftindex'][$intIndex]);
	    $arrTypes = array('A', 'S', 'H', 'L', 'W', 'E');
	    $arrNames = array('zbrój', 'tarcz', 'hełmów', 'nagolenników', 'broni', 'narzędzi');
	    $objItem->fields['name'] .= ' z miedzi';
	    $intExp = ($objItem->fields['level'] * 10);
	    $strSkill = 'smith';
	    $strSkill2 = 'kowalstwo';
	    $strStat = 'strength';
	    $strStat2 = 'Siła';
	    $intWt = 20;
	    $intRepair = $objItem->fields['level'];
	    if ($objItem->fields['type'] == 'A' || $objItem->fields['type'] == 'W')
	      {
		$intWt = 40;
		$intRepair = $intRepair * 2;
	      }
	    $intAgi = 0;
	    if ($objItem->fields['type'] == 'A')
	      {
		$intAgi = floor($objItem->fields['level'] / 2);
	      }
	    elseif ($objItem->fields['type'] == 'L')
	      {
		$intAgi = floor($objItem->fields['level'] / 3);
	      }
	    $intCost = ceil($objItem -> fields['cost'] / 20);
	    $intName = array_search($objItem->fields['type'], $arrTypes);
	    $strInfo2 .= 'wykonywaniu '.$arrNames[$intName].'. ';
	    if (!$blnCase)
	      {
		$strInfo2 .= 'W nagrodę otrzymał'.$strSuffix.' '.$objItem->fields['name'];
		$test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$objItem->fields['name']."' AND `wt`=".$intWt." AND `type`='".$objItem->fields['type']."' AND `status`='U' AND `owner`=".$player->id." AND `power`=".$objItem->fields['level']." AND `zr`=".$intAgi." AND `szyb`=0 AND `maxwt`=".$intWt." AND `poison`=0 AND `cost`=".$intCost);
		if (!$test -> fields['id']) 
		  {
		    $db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$objItem->fields['name']."', ".$objItem->fields['level'].", '".$objItem->fields['type']."', ".$intCost.", ".$intAgi.", ".$intWt.", ".$objItem->fields['level'].", ".$intWt.", 1, 'N', 0, 0, '".$objItem->fields['twohand']."', ".$intRepair.")");
		  } 
		else 
		  {
		    $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$test -> fields['id']);
		  }
		$test -> Close();
	      }
	    else
	      {
		$strInfo2 .= 'Zbyt mocno uderzał'.$strSuffix.' w kawałek metalu. Ten nagle pękł rozsiewając rozgrzane do czerwoności rykoszety wokoło.';
	      }
	    $objItem->Close();
	    break;
	  default:
	    break;
	  }
	if (!$blnCase)
	  {
	    $intGold = $_SESSION['craftenergy'][$intIndex] * $player->skills[$strSkill][1] * 10;
	    $strInfo2 .= ', '.$intExp.' punktów doświadczenia w umiejętności '.$strSkill2.' oraz do cechy '.$strStat2.'. Dodatkowo dostał'.$strSuffix.' '.$intGold.' sztuk złota';
	    $intRoll = rand(1, 1000);
	    switch ($intRoll)
	      {
	      case 995:
	      case 996:
		$strInfo2 .= ' oraz '.$arrTool[0];
		$arrLoot = array('tools', '=1', $arrTool[4]);
		break;
	      case 997:
		$strInfo2 .= ' oraz '.$arrTool[1];
		$arrLoot = array('tools','>1', $arrTool[4]);
		break;
	      case 998:
	      case 999:
		$strInfo2 .= ' oraz plan '.$arrTool[2];
		$arrLoot = array('plans','=1', $arrTool[4]);
		break;
	      case 1000:
		$strInfo2 .= ' oraz plan '.$arrTool[3];
		$arrLoot = array('plans', '>1', $arrTool[4]);
		break;
	      default:
		break;
	      }
	    if ($intRoll > 994)
	      {
		$objLoot = $db->Execute("SELECT * FROM `".$arrLoot[0]."` WHERE `level`".$arrLoot[1]." AND `type`='".$arrLoot[2]."' ORDER BY RAND() LIMIT 1") or die($db->ErrorMsg());
		if ($arrLoot[0] == 'tools')
		  {
		    $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$objLoot->fields['name']." z miedzi' AND `power`=".$objLoot->fields['power']." AND `cost`=1 AND `wt`=".$objLoot->fields['dur']." AND `maxwt`=".$objLoot->fields['dur']." AND `type`='E' AND `minlev`=".$objLoot->fields['level']." AND repair=".$objLoot->fields['repair']);
		  }
		else
		  {
		    $objTest = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=".$player->id." AND `name`='".$objLoot->fields['name']."' AND `minlev`=".$objLoot->fields['level']." AND `cost`=1 AND `type`='P'");
		  }
		if (!$objTest->fields['id'])
		  {
		    if ($arrLoot[0] == 'tools')
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$objLoot->fields['name']." z miedzi', ".$objLoot->fields['power'].", 'E', 1, 0, ".$objLoot->fields['dur'].", ".$objLoot->fields['level'].", ".$objLoot->fields['dur'].", 1, 'N', 0, 0, 'N', ".$objLoot->fields['repair'].")") or die($db->ErrorMsg());
		      }
		    else
		      {
			$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$objLoot->fields['name']."', 0, 'P', 1, 0, 1, ".$objLoot->fields['level'].", 1, 1, 'N', 0, 0, 'N', 1)") or die($db->ErrorMsg());
		      }
		  }
		else
		  {
		    $db->Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$objTest->fields['id']);
		  }
		$objTest->Close();
		$objLoot->Close();
	      }
	    $strInfo2 .= '.';
	    $player->checkexp(array($strSkill => $intExp / 2), $player->id, "skills");
	    $player->checkexp(array($strStat => $intExp / 2), $player->id, "stats");
	    $db->Execute("UPDATE `players` SET `credits`=`credits`+".$intGold.", `mpoints`=`mpoints`+1 WHERE `id`=".$player->id);
	  }
	else
	  {
	    $strInfo2 .= ' W wyniku wypadku stracił'.$strSuffix.' '.$intDamage.' punktów życia, na dodatek nie dostał'.$strSuffix.' wypłaty.';
	  }
	if ($objJob->fields['craftmission'] > 0)
	  {
	    $strInfo2 .= '<br /><br />(<a href="crafts.php?step=first">Wróć do listy zadań</a>)';
	  }
	else
	  {
	    $strInfo2 .= '<br /><br />(<a href="city.php">Wróć do miasta</a>)';
	  }
	$smarty->assign("Result", $strInfo2);
	unset($_SESSION['craft'], $_SESSION['craftindex'], $_SESSION['craftenergy']);
      }
    elseif ($_GET['step'] == 'register')
      {
	$arrSkills = array("smelting" => "Hutnika", "lumberjack" => "Drwala", "mining" => "Górnika", "breeding" => "Hodowcę", "jewellry" => "Jubilera", "herbalism" => "Zielarza", "alchemy" => "Alchemika", "carpentry" => "Stolarza", "smith" => "Kowala");
	foreach ($arrSkills as $key => &$strSkill)
	  {
	    $intCost = $player->skills[$key][1] * 1000;
	    $strSkill .= ' ('.$intCost.' sztuk złota)';
	  }
	$arrSkills2 = array("smelting" => "Hutnik", "lumberjack" => "Drwal", "mining" => "Górnik", "breeding" => "Hodowca", "jewellry" => "Jubiler", "herbalism" => "Zielarz", "alchemy" => "Alchemik", "carpentry" => "Stolarz", "smith" => "Kowal");
	$objCraftskill = $db->Execute("SELECT `craftskill` FROM `players` WHERE `id`=".$player->id);
	if ($objCraftskill->fields['craftskill'] != "")
	  {
	    $strReginfo = 'Obecnie jesteś zarejestrowany jako '.$arrSkills2[$objCraftskill->fields['craftskill']].'.';
	  }
	else
	  {
	    $strReginfo = '';
	  }
	$smarty->assign(array("Oskills" => $arrSkills,
			      "Reginfo" => "<i>- W porządku.</i> - odpowiada hobbit. - <i>Koszt zarejestrowania się u nas w wybranym zawodzie zależy od poziomu umiejętności sztuk złota. Jeżeli zmienisz później zdanie, zawsze możesz zarejestrować się ponownie w innym zawodzie. To jak, chcesz się zarejestrować?</i>",
			      "Reginfo2" => $strReginfo,
			      "Aregister2" => "Zarejestruj",
			      "Tregister" => 'mnie jako',
			      "Ano" => "Nie, dziękuję"));
	if (isset($_POST['skill']))
	  {
	    $blnValid = TRUE;
	    if (!in_array($_POST['skill'], array_keys($arrSkills)))
	      {
		message('error', 'Zapomnij o tym.');
		$blnValid = FALSE;
	      }
	    $intCost = $player->skills[$_POST['skill']][1] * 1000;
	    if ($player->credits < $intCost)
	      {
		message('error', 'Nie masz tylu sztuk złota przy sobie.');
		$blnValid = FALSE;
	      }
	    if ($blnValid)
	      {
		$db->Execute("UPDATE `players` SET `craftskill`='".$_POST['skill']."', `credits`=`credits`-".$intCost." WHERE `id`=".$player->id);
		$player->credits -= $intCost;
		message('success', 'Zarejestrowałeś się u Cześnika jako '.$arrSkills2[$_POST['skill']].'.');
	      }
	  }
      }
  }
else
  {
    $_GET['step'] = '';
  }

$objJob->Close();
/**
* Assign variables to template and display page
*/
$smarty->assign(array("Craftinfo" => 'Znajdujesz się w niewielkiej, drewnianej faktorii. Wokół kręcą się różne istoty: elfy, krasnoludowie, ludzie. Część z nich biega w tę i z powrotem z pakunkami. Tuż przy drzwiach widzisz stolik z napisem "RECEPCJA". Przy stoliku siedzi znudzony hobbit. Kiedy podchodzisz bliżej, podnosi na ciebie wzrok i bezbarwnym głosem pyta:<br /><i>- W czym mogę pomóc?</i>',
		      "Ajob" => 'Szukam jakiejś pracy do wykonania',
		      "Aregister" => 'Chcę się zarejestrować',
		      "Step" => $_GET['step']));
$smarty->display('crafts.tpl');

require_once("includes/foot.php");
?>