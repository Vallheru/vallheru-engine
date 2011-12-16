<?php
/**
 *   File functions:
 *   Crafts guild - random missions for craftsmen
 *
 *   @name                 : crafts.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 16.12.2011
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
	if ($objJob->fields['craftmission'] == 'Y')
	  {
	    error("Niestety, na chwilę obecną, nie mamy dla ciebie jakiegokolwiek zadania. Proszę wróć za jakiś czas. (<a href=city.php>Powrót do miasta</a>)");
	  }
	$arrSkills = array($player->metallurgy, $player->lumberjack, $player->mining, $player->breeding, $player->jeweller, $player->herbalist, $player->alchemy, $player->fletcher, $player->smith);
	$intIndex = array_search(max($arrSkills), $arrSkills);
	$_SESSION['craft'] = $intIndex;
	switch ($intIndex)
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
	    $_SESSION['craftenergy'] = $arrBillets[$intLevel] * 2;
	    $_SESSION['craftindex'] = $intLevel;
	    $strInfo = 'hutnika, który wytopi nam nieco sztabek '.$arrOptions[$intLevel];
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
	    $_SESSION['craftenergy'] = $arrBillets[$intLevel] * 2;
	    $_SESSION['craftindex'] = $intLevel;
	    $strInfo = 'drwala, który zdobędzie dla nas nieco drewna '.$arrOptions[$intLevel];
	    break;
	    //mining
	  case 2:
	    $arrOptions = array('rudy miedzi', 'cynku', 'cyny', 'rudy żelaza', 'brył węgla', 'adamantium', 'kryształów', 'meteorytu');
	    $intLevel = rand(0, 7);
	    $arrBillets = array(1, 2, 3, 5, 8, 10, 12, 14);
	    $_SESSION['craftenergy'] = $arrBillets[$intLevel] * 2;
	    $_SESSION['craftindex'] = $intLevel;
	    $strInfo = 'górnika, który zdobędzie dla nas nieco '.$arrOptions[$intLevel];
	    break;
	    //breeding
	  case 3:
	    $_SESSION['craftenergy'] = 20;
	    $_SESSION['craftindex'] = rand(1, 6);
	    $strInfo = 'hodowcy, który zaopiekuje się naszymi chowańcami';
	    break;
	    //jeweller
	  case 4:
	    $objRing = $db->Execute("SELECT `id`, `name`, `level` FROM `jeweller` WHERE `owner`=".$player->id." AND `name`!='pierścień' AND `level`<=".$player->level." ORDER BY RAND() LIMIT 1");
	    if (!$objRing->fields['id'])
	      {
		$intLevel = 1;
		$_SESSION['craftindex'] = 1;
	      }
	    else
	      {
		$intLevel = $objRing->fields['level'];
		$_SESSION['craftindex'] = $objRing->fields['id'];
	      }
	    $_SESSION['craftenergy'] = 5 * $intLevel;
	    $strInfo = 'jubilera, który wykona dla nas parę pierścieni typu: '.$objRing->fields['name'];
	    $objRing->Close();
	    break;
	    //herbalist
	  case 5:
	    $arrOptions = array('Illani', 'Illanias', 'Nutari', 'Dynallca', 'Nasiona Illani', 'Nasiona Illanias', 'Nasiona Nutari', 'Nasiona Dynallca');
	    $intLevel = rand(0, 7);
	    $_SESSION['craftenergy'] = 20;
	    $_SESSION['craftindex'] = $intLevel;
	    $strInfo = 'zielarza, który ';
	    if ($intLevel < 4)
	      {
		$strInfo .= 'będzie przez pewien czas doglądał naszych ziół, ';
	      }
	    else
	      {
		$strInfo .= 'ususzy nam nieco ziół, ';
	      }
	    $strInfo .= 'konkretnie '.$arrOptions[$intLevel];
	    break;
	    //alchemy
	  case 6:
	    $objPotion = $db->Execute("SELECT `id`, `name`, `level` FROM `alchemy_mill` WHERE `owner`=".$player->id." AND `level`<=".$player->level." ORDER BY RAND() LIMIT 1");
	    if (!$objPotion->fields['id'])
	      {
		$intLevel = 1;
		$_SESSION['craftindex'] = 5;
	      }
	    else
	      {
		$intLevel = $objPotion->fields['level'];
		$_SESSION['craftindex'] = $objPotion->fields['id'];
	      }
	    $_SESSION['craftenergy'] = 5 * $intLevel;
	    $strInfo = 'alchemika, który wykona dla nas parę mikstur typu: '.$objPotion->fields['name'];
	    $objPotion->Close();
	    break;
	    //fletcher
	  case 7:
	    $objBows = $db->Execute("SELECT `id`, `name`, `level`, `type` FROM `mill` WHERE `owner`=".$player->id." AND `elite`=0 AND `level`<=".$player->level." ORDER BY RAND() LIMIT 1");
	    if (!$objBows->fields['id'])
	      {
		$intLevel = 1;
		$_SESSION['craftindex'] = 1;
	      }
	    else
	      {
		$intLevel = $objBows->fields['level'];
		$_SESSION['craftindex'] = $objBows->fields['id'];
	      }
	    $_SESSION['craftenergy'] = 5 * $intLevel;
	    $strInfo = 'stolarza, który wykona dla nas parę ';
	    if ($objBows->fields['type'] == 'B')
	      {
		$strInfo .= 'łuków ';
	      }
	    else
	      {
		$strInfo .= 'strzał ';
	      }
	    $strInfo .= 'typu: '.$objBows->fields['name'];
	    $objBows->Close();
	    break;
	    //smith
	  case 8:
	    $objSmith = $db->Execute("SELECT `id`, `name`, `level`, `type` FROM `smith` WHERE `owner`=".$player->id." AND `elite`=0 AND `level`<=".$player->level." ORDER BY RAND() LIMIT 1");
	    $arrTypes = array('A', 'S', 'H', 'L', 'W');
	    $arrNames = array('zbrój', 'tarcz', 'hełmów', 'nagolenników', 'broni');
	    if (!$objSmith->fields['id'])
	      {
		$intLevel = 1;
		$_SESSION['craftindex'] = 18;
		$intName = 1;
	      }
	    else
	      {
		$intLevel = $objSmith->fields['level'];
		$_SESSION['craftindex'] = $objSmith->fields['id'];
		$intName = array_search($objSmith->fields['type'], $arrTypes);
	      }
	    $_SESSION['craftenergy'] = 5 * $intLevel;
	    $strInfo = 'kowala, który wykona dla nas parę '.$arrNames[$intName];
	    $strInfo .= ' typu: '.$objSmith->fields['name'];
	    $objSmith->Close();
	    break;
	  default:
	    break;
	  }
	$strInfo .= '. Chcesz się podjąć tego zadania?</i>';
	$smarty->assign(array('Jobinfo' => 'Przez dłuższą chwilę opowiadasz niziołkowi o swoich umiejętnościach. Ten co chwila przerywa, zadając jakieś pytania a następnie sprawdzając coś w papierach. W końcu, znajduje odpowiedni papier i mówi do ciebie:<i>-Zdaje się, że mam coś dla ciebie. Akurat potrzebujemy '.$strInfo,
			      "Ayes" => "Przyjmuję zlecenie (koszt: ".$_SESSION['craftenergy']." energii)",
			      "Ano" => "Nie, dziękuję",
			      'Jobinfo2' => 'Pamiętaj, oferta jest ważna tylko w tym momencie, jeżeli ją odrzucisz, następna szansa dopiero po kolejnym resecie.'));
	$db->Execute("UPDATE `players` SET `craftmission`='Y' WHERE `id`=".$player->id);
      }
    /**
     * Finish task
     */
    else
      {
	if (!isset($_SESSION['craft']))
	  {
	    error('Zapomnij o tym.');
	  }
	if ($player->energy < $_SESSION['craftenergy'])
	  {
	    unset($_SESSION['craft'], $_SESSION['craftenergy'], $_SESSION['craftindex']);
	    error("Nie masz tyle energii.");
	  }
	$intGold = $_SESSION['craftenergy'] * $player->level * 10;
	$db->Execute("UPDATE `players` SET `energy`=`energy`-".$_SESSION['craftenergy'].", `credits`=`credits`+".$intGold." WHERE `id`=".$player->id);
	if ($player->gender == 'M')
	  {
	    $strSuffix = 'eś';
	  }
	else
	  {
	    $strSuffix = 'aś';
	  }
	$strInfo2 = 'Pracował'.$strSuffix.' przez pewien czas przy ';
	switch ($_SESSION['craft'])
	  {
	    //smelting
	  case 0:
	    $arrOptions = array('miedzi', 'brązu', 'mosiądzu', 'żelaza', 'stali');
	    $arrBillets = array('copper', 'bronze', 'brass', 'iron', 'steel');
	    $intAmount = rand(1, 50);
	    $fltSkill = ((float)rand(1, 10) / 10) * ($_SESSION['craftindex'] + 1);
	    $intExp = $fltSkill * 20;
	    $strSkill = 'metallurgy';
	    $strSkill2 = 'hutnictwo';
	    $strInfo2 .= 'wytapianiu sztabek '.$arrOptions[$_SESSION['craftindex']].'. W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$arrOptions[$_SESSION['craftindex']];
	    $objTest = $db->Execute("SELECT `owner`, `".$arrBillets[$_SESSION['craftindex']]."` FROM `minerals` WHERE `owner`=".$player->id);
	    if (!$objTest->fields['owner'])
	      {
		$db->Execute("INSERT INTO `minerals` (`owner`, `".$arrBillets[$_SESSION['craftindex']]."`) VALUES (".$player->id.", ".$intAmount.")");
	      }
	    else
	      {
		$db->Execute("UPDATE `minerals` SET `".$arrBillets[$_SESSION['craftindex']]."`=`".$arrBillets[$_SESSION['craftindex']]."` WHERE `owner`=".$player->id);
	      }
	    $objTest->Close();
	    $intWarehouse = $intAmount * 10;
	    $db->Execute("UPDATE `warehouse` SET `amount`=`amount`+'".$intWarehouse."' WHERE `mineral`='".$arrBillets[$_SESSION['craftindex']]."'");
	    break;
	    //lumberjack
	  case 1:
	    $arrOptions = array("sosnowego", "z leszczyny", "cisowego", "z wiązu");
	    $arrBillets = array('pine', 'hazel', 'yew', 'elm');
	    $intAmount = rand(1, 50);
	    $strInfo2 .= 'ścinaniu drewna '.$arrOptions[$_SESSION['craftindex']].'. W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$arrOptions[$_SESSION['craftindex']];
	    $fltSkill = ((float)rand(1, 10) / 10) * ($_SESSION['craftindex'] + 1);
	    $intExp = $fltSkill * 20;
	    $strSkill = 'lumberjack';
	    $strSkill2 = 'drwalnictwo';
	    $objTest = $db->Execute("SELECT `owner`, `".$arrBillets[$_SESSION['craftindex']]."` FROM `minerals` WHERE `owner`=".$player->id);
	    if (!$objTest->fields['owner'])
	      {
		$db->Execute("INSERT INTO `minerals` (`owner`, `".$arrBillets[$_SESSION['craftindex']]."`) VALUES (".$player->id.", ".$intAmount.")");
	      }
	    else
	      {
		$db->Execute("UPDATE `minerals` SET `".$arrBillets[$_SESSION['craftindex']]."`=`".$arrBillets[$_SESSION['craftindex']]."` WHERE `owner`=".$player->id);
	      }
	    $objTest->Close();
	    $intWarehouse = $intAmount * 10;
	    $db->Execute("UPDATE `warehouse` SET `amount`=`amount`+'".$intWarehouse."' WHERE `mineral`='".$arrBillets[$_SESSION['craftindex']]."'");
	    break;
	    //mining
	  case 2:
	    $arrOptions = array('rudy miedzi', 'cynku', 'cyny', 'rudy żelaza', 'brył węgla', 'adamantium', 'kryształów', 'meteorytu');
	    $arrBillets = array('copperore', 'zincore', 'tinore', 'ironore', 'coal', 'adamantium', 'crystal', 'meteor');
	    $intAmount = rand(1, 50);
	    $strInfo2 .= 'wydobywaniu '.$arrOptions[$_SESSION['craftindex']].'. W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$arrOptions[$_SESSION['craftindex']];
	    $fltSkill = ((float)rand(1, 10) / 10) * ($_SESSION['craftindex'] + 1);
	    $intExp = $fltSkill * 20;
	    $strSkill = 'mining';
	    $strSkill2 = 'górnictwo';
	    $objTest = $db->Execute("SELECT `owner`, `".$arrBillets[$_SESSION['craftindex']]."` FROM `minerals` WHERE `owner`=".$player->id);
	    if (!$objTest->fields['owner'])
	      {
		$db->Execute("INSERT INTO `minerals` (`owner`, `".$arrBillets[$_SESSION['craftindex']]."`) VALUES (".$player->id.", ".$intAmount.")");
	      }
	    else
	      {
		$db->Execute("UPDATE `minerals` SET `".$arrBillets[$_SESSION['craftindex']]."`=`".$arrBillets[$_SESSION['craftindex']]."` WHERE `owner`=".$player->id);
	      }
	    $objTest->Close();
	    $intWarehouse = $intAmount * 10;
	    $db->Execute("UPDATE `warehouse` SET `amount`=`amount`+'".$intWarehouse."' WHERE `mineral`='".$arrBillets[$_SESSION['craftindex']]."'");
	    break;
	    //breeding
	  case 3:
	    $fltSkill = ((float)rand(1, 10) / 10) * $_SESSION['craftindex'];
	    $intExp = $fltSkill * 20;
	    $strSkill = 'breeding';
	    $strSkill2 = 'hodowla';
	    $strGen = array_rand(array('M', 'F'));
	    $objCore = $db->Execute("SELECT * FROM `cores` WHERE `id`=".$_SESSION['craftindex']);
	    $db -> Execute("INSERT INTO `core` (`owner`, `name`, `type`, `ref_id`, `power`, `defense`, `gender`) VALUES(".$player -> id.",'".$objCore->fields['name']."','".$objCore->fields['type']."',".$_SESSION['craftindex'].",".$objCore->fields['power'].",".$objCore->fields['defense'].", '".$strGen."')");
	    $strInfo2 .= 'chowańcach. W nagrodę otrzymał'.$strSuffix.' nowego chowańca: '.$objCore->fields['name'];
	    $objCore->Close();
	    break;
	    //jeweller
	  case 4:
	    $objRing = $db->Execute("SELECT * FROM `jeweller` WHERE `id`=".$_SESSION['craftindex']);
	    if ($objRing->fields['id'] == 1)
	      {
		$intAmount = rand(1, 20);
		$intCost = 3;
		$intPower = 0;
	      }
	    else
	      {
		$arrStats = array(' siły', ' zręczności', ' inteligencji', ' szybkości', ' wytrzymałości', ' siły woli');
		$intAmount = 1;
		$intStat = rand(0, 5);
		$objRing->fields['name'] .= $arrStats[$intStat];
		$intCost = $objRing->fields['cost'] * 20;
		$intPower = ceil($objRing->fields['bonus'] / 10);
	      }
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
	    $fltSkill = ((float)rand(1, 10) / 10) * $objRing->fields['level'];
	    $intExp = $fltSkill * 50;
	    $strSkill = 'jeweller';
	    $strSkill2 = 'jubilerstwo';
	    $strInfo2 .= 'wykonywaniu pierścieni. W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$objiRing->fields['name'];
	    $objRing->Close();
	    break;
	    //herbalist
	  case 5:
	    $arrOptions = array('Illani', 'Illanias', 'Nutari', 'Dynallca', 'Nasiona Illani', 'Nasiona Illanias', 'Nasiona Nutari', 'Nasiona Dynallca');
	    $arrBillets = array('illani', 'illanias', 'nutari', 'dynallca', 'ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	    $arrBillets2 = array('illani', 'illanias', 'nutari', 'dynallca', 'illani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	    if ($_SESSION['craftindex'] < 4)
	      {
		$strInfo2 .= 'hodowaniu ziół: ';
		$intAmount = rand(1, 40);
		$fltSkill = ((float)rand(1, 10) / 10) * ($_SESSION['craftindex'] + 1);
	      }
	    else
	      {
		$strInfo2 .= 'zdobywaniu nasion: ';
		$intAmount = rand(1, 20);
		$fltSkill = ((float)rand(1, 5) / 10) * ($_SESSION['craftindex'] + 1);
	      }
	    $strInfo2 .= $arrOptions[$_SESSION['craftindex']].'. W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$arrOptions[$_SESSION['craftindex']];
	    $intExp = $fltSkill * 20;
	    $strSkill = 'herbalist';
	    $strSkill2 = 'zielarstwo';
	    $objTest = $db->Execute("SELECT `gracz`, `".$arrBillets[$_SESSION['craftindex']]."` FROM `herbs` WHERE `gracz`=".$player->id) or die($db->ErrorMsg());
	    if (!$objTest->fields['owner'])
	      {
		$db->Execute("INSERT INTO `herbs` (`gracz`, `".$arrBillets[$_SESSION['craftindex']]."`) VALUES (".$player->id.", ".$intAmount.")");
	      }
	    else
	      {
		$db->Execute("UPDATE `herbs` SET `".$arrBillets[$_SESSION['craftindex']]."`=`".$arrBillets[$_SESSION['craftindex']]."` WHERE `gracz`=".$player->id);
	      }
	    $objTest->Close();
	    $intWarehouse = $intAmount * 10;
	    $db->Execute("UPDATE `warehouse` SET `amount`=`amount`+'".$intWarehouse."' WHERE `mineral`='".$arrBillets2[$_SESSION['craftindex']]."'");
	    break;
	    //alchemy
	  case 6:
	    $objPotion = $db->Execute("SELECT * FROM `alchemy_mill` WHERE `id`=".$_SESSION['craftindex']);
	    $intAmount = rand(1, 20);
	    $fltSkill = ((float)rand(1, 10) / 10) * ceil($objPotion->fields['level'] / 5);
	    $intExp = $fltSkill * 40;
	    $strSkill = 'alchemia';
	    $strSkill2 = 'alchemia';
	    $intCost = ceil($objPotion->fields['cost'] / 200);
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
	    $strInfo2 .= 'warzeniu mikstur. W nagrodę otrzymał'.$strSuffix.' '.$intAmount.' sztuk '.$objPotion->fields['name'];
	    $objPotion->Close();
	    break;
	    //fletcher
	  case 7:
	    $objBow = $db->Execute("SELECT * FROM `mill` WHERE `id`=".$_SESSION['craftindex']);
	    $strInfo2 .= 'wykonywaniu ';
	    $fltSkill = ((float)rand(1, 10) / 10) * $objBow->fields['level'];
	    $intExp = $fltSkill * 50;
	    $strSkill = 'fletcher';
	    $strSkill2 = 'stolarstwo';
	    if ($objBow->fields['type'] == 'B')
	      {
		$intCost = $objBow->fields['cost'] / 20;
		$objBow->fields['name'] .= ' z leszczyny';
		$intRepaircost = $objBow->fields['level'] * 2;
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
		$stInfo2 .= 'łuków. W nagrodę dostał'.$strSuffix.' '.$objBow->fields['name'];
	      }
	    else
	      {
		$intCost = $objBow->fields['level'] * 5;
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
		$strInfo2 .= 'strzał. W nagrodę dostał'.$strSuffix.' 100 strzał typu: '.$objBow->fields['name'];
	      }
	    $objBow->Close();
	    break;
	    //smith
	  case 8:
	    $objItem = $db->Execute("SELECT * FROM `smith` WHERE `id`=".$_SESSION['craftindex']);
	    $arrTypes = array('A', 'S', 'H', 'L', 'W');
	    $arrNames = array('zbrój', 'tarcz', 'hełmów', 'nagolenników', 'broni');
	    $objItem->fields['name'] .= ' z miedzi';
	    $fltSkill = ((float)rand(1, 10) / 10) * $objItem->fields['level'];
	    $intExp = $fltSkill * 50;
	    $strSkill = 'ability';
	    $strSkill2 = 'kowalstwo';
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
	    $test = $db -> Execute("SELECT `id` FROM `equipment` WHERE `name`='".$objItem->fields['name']."' AND `wt`=".$intWt." AND `type`='".$objItem->fields['type']."' AND `status`='U' AND `owner`=".$player->id." AND `power`=".$objItem->fields['level']." AND `zr`=".$intAgi." AND `szyb`=0 AND `maxwt`=".$intWt." AND `poison`=0 AND `cost`=".$intCost);
	    if (!$test -> fields['id']) 
	      {
		$db -> Execute("INSERT INTO `equipment` (`owner`, `name`, `power`, `type`, `cost`, `zr`, `wt`, `minlev`, `maxwt`, `amount`, `magic`, `poison`, `szyb`, `twohand`, `repair`) VALUES(".$player->id.", '".$objItem->fields['name']."', ".$objItem->fields['level'].", '".$objItem->fields['type']."', ".$intCost.", ".$intAgi.", ".$intWt.", ".$objItem->fields['level'].", ".$intWt.", 1, 'N', 0, 0, '".$objItem->fields['twohand']."', ".$intRepair.")");
	      } 
	    else 
	      {
		$db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$test -> fields['id']);
	      }
	    $intName = array_search($objItem->fields['type'], $arrTypes);
	    $strInfo2 .= 'wykonywaniu '.$arrNames[$intName].'. W nagrodę otrzymał'.$strSuffix.' '.$objItem->fields['name'];
	    $test -> Close();
	    $objItem->Close();
	    break;
	  default:
	    break;
	  }
	$strInfo2 .= ', '.$intExp.' punktów doświadczenia, '.$fltSkill.' w umiejętności '.$strSkill2.' oraz '.$intGold.' sztuk złota.';
	require_once("includes/checkexp.php");
	checkexp($player->exp, $intExp, $player->level, $player->race, $player->user, $player->id, 0, 0, $player->id, $strSkill, $fltSkill);
	$smarty->assign("Result", $strInfo2);
	unset($_SESSION['craft'], $_SESSION['craftindex'], $_SESSION['craftenergy']);
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
		      "Ajob" => 'Szukam jakiejś pracy do wykonania.',
		      "Step" => $_GET['step']));
$smarty->display('crafts.tpl');

require_once("includes/foot.php");
?>