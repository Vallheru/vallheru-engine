<?php
/**
 *   File functions:
 *   Crafts guild - random missions for craftsmen
 *
 *   @name                 : crafts.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 14.12.2011
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
		$intLevel = $objSmelter->fields['level'] - 1;
	      }
	    $objSmelter->Close();
	    $arrBillets = array(1, 2, 3, 5, 8);
	    $_SESSION['craftenergy'] = $arrBillets[$intLevel];
	    $_SESSION['craftindex'] = $intLevel;
	    $strEnergy = '(koszt: '.$arrBillets[$intLevel].' energii)';
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
		$intLevel = $objLumber->fields['level'] - 1;
	      }
	    $objLumber->Close();
	    $arrBillets = array(1, 2, 3, 5, 8);
	    $_SESSION['craftenergy'] = $arrBillets[$intLevel];
	    $_SESSION['craftindex'] = $intLevel;
	    $strEnergy = '(koszt: '.$arrBillets[$intLevel].' energii)';
	    $strInfo = 'drwala, który zdobędzie dla nas nieco drewna '.$arrOptions[$intLevel];
	    //mining
	  case 2:
	    $arrOptions = array('rudy miedzi', 'cynku', 'cyny', 'rudy żelaza', 'brył węgla');
	    $intLevel = rand(0, 4);
	    $arrBillets = array(1, 2, 3, 5, 8);
	    $_SESSION['craftenergy'] = $arrBillets[$intLevel];
	    $_SESSION['craftindex'] = $intLevel;
	    $strEnergy = '(koszt: '.$arrBillets[$intLevel].' energii)';
	    $strInfo = 'górnika, który zdobędzie dla nas nieco '.$arrOptions[$intLevel];
	    break;
	    //breeding
	  case 3:
	    $_SESSION['craftenergy'] = 10;
	    $_SESSION['craftindex'] = rand(0, 5);
	    $strEnergy = '(koszt: 10 energii)';
	    $strInfo = 'hodowcy, który zaopiekuje się naszymi chowańcami';
	    break;
	    //jeweller
	  case 4:
	    break;
	    //herbalist
	  case 5:
	    break;
	    //alchemy
	  case 6:
	    break;
	    //fletcher
	  case 7:
	    break;
	    //smith
	  case 8:
	    break;
	  default:
	    break;
	  }
	$strInfo .= '. Chcesz się podjąć tego zadania?</i>';
	$smarty->assign(array('Jobinfo' => 'Przez dłuższą chwilę opowiadasz niziołkowi o swoich umiejętnościach. Ten co chwila przerywa, zadając jakieś pytania a następnie sprawdzając coś w papierach. W końcu, znajduje odpowiedni papier i mówi do ciebie:<i>-Zdaje się, że mam coś dla ciebie. Akurat potrzebujemy '.$strInfo,
			      "Ayes" => "Przyjmuję zlecenie ".$strEnergy,
			      "Ano" => "Nie, dziękuję",
			      'Jobinfo2' => 'Pamiętaj, oferta jest ważna tylko w tym momencie, jeżeli ją odrzucisz, następna szansa dopiero po kolejnym resecie.'));
      }
    /**
     * Finish task
     */
    else
      {
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