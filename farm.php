<?php
/**
 *   File functions:
 *   Players farms - herbs
 *
 *   @name                 : farm.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 14.11.2012
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

$title = "Farma";
require_once("includes/head.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error("Nie znajdujesz się w mieście.");
}

/**
 * Main menu
 */
if (!isset($_GET['step']))
  {
    $smarty -> assign(array("Farminfo" => "Witaj na farmach. Możesz tutaj hodować zioła z których później wyrabia się mikstury.",
                            "Aplantation" => "Plantacja",
                            "Ahouse" => "Chatka ogrodnika",
                            "Aencyclopedia" => "Encyklopedia roślin"));
    $_GET['step'] = '';
    $_GET['action'] = '';
  }
else
  {
    /**
     * Herbs info
     */
    if ($_GET['step'] == 'herbsinfo')
      {
	$smarty -> assign(array("Herbsinfo" => "Encyklopedia roślin",
				"Ilaniinfo" => "Illani jest roslina zielną, rosnącą czesto dziko na polanach w różnych miejscach ".$gamename.". W zalezności od miejsca przyjmuje różne rozmiary i formy, od ledwie widocznych wśród traw liści w górach, po dobrze wykształcone rosliny w niższych częściach krainy. Kwitnie na żołto, a po zapyleniu produkuje drobne nasiona w pekających owocach.<br />Łatwa i tania w uprawie, gdyż nie potrzebuje specjalnych zabiegow pielęgnacyjnych, ani dodatkowego sprzętu w hodowli. ",
				"Illaniasinfo" => "Illanias jest rośliną zielną, ciepłolubna. Rośnie w ciepłych lasach, w miejscach takich jak dobrze nasłonecznione polany. Jest dwupienna, co oznacza, że do otrzymania nasion potrzebne są osobniki żeńskie i męskie. Oba osobniki wyglądają podobnie - około 20 centymetrowe, podłużne liście wyrastające bezpośrednio z ziemi. W okresie kwitnienia wypuszczają łodygi na których rozwijają się kwiaty. Żeńskie kwiaty są niedużymi szyszkami, męskie to zawieszone na nitkowatych tworach skupiska pylników.<br />Do uprawy w klimacie ".$gamename." niezbędna jest szklarnia, co podwyższa nieco koszty uprawy. Dziko rośnie słabo i nie kwitnie.",
				"Nutariinfo" => "Nutari jest tropikalnym mchem bagiennym. Rośnie w miejscach bardzo wilgotnych i nie nasłonecznionych. Roślina ma około 10 centymetrów wysokości i jest ulistnioną łodyżką. Rośnie w dużych skupiskach. Do uprawy w warunkach ".$gamename." niezbędna jest szklarnia i system nawadniający.",
				"Dynallcainfo" => "Dynallca to pnącze rosnące w lasach tropikalnych przypominające trochę bluszcz. W warunkach naturalnych oplata się wokół drzew. Najczęściej rośnie w miejscach dobrze nawodnionych, często zapuszcza korzenie na podmokłych terenach. Wykształciła system korzeni oddechowych, dzięki którym może w takich warunkach egzystować. Kwitnie na biało, wypuszczając małe kwiatki o średnicy nie większej, niż 0,5 centymetra. Na ".$gamename." hodowana jest w szklarniach, na specjalnych konstrukcjach nośnych. Wymaga także systemu nawadniającego."));
	$_GET['action'] = '';
      }
    /**
     * House of gardener
     */
    if ($_GET['step'] == 'house')
      {
	$objHerbs = $db -> Execute("SELECT `illani`, `illanias`, `nutari`, `dynallca` FROM `herbs` WHERE `gracz`=".$player -> id);
	if (!$objHerbs -> fields['illani'] && !$objHerbs -> fields['illanias'] && !$objHerbs -> fields['nutari'] && !$objHerbs -> fields['dynallca'])
	  {
	    error("Nie masz ziół do suszenia!");
	  }
	$arrHerbs = array('illani', 'illanias', 'nutari', 'dynallca');
	$arrHerbsname = array("Illani", "Illanias", "Nutari", "Dynallca");
	$arrHerbsaviable = array();
	$arrHerbsoption = array();
	$arrHerbsamount = array();
	$j = 0;
	for ($i = 0; $i < 4; $i++)
	  {
	    $strName = $arrHerbs[$i];
	    if ($objHerbs -> fields[$strName])
	      {
		$arrHerbsaviable[$j] = $arrHerbsname[$i];
		$arrHerbsamount[$j] = $objHerbs -> fields[$strName];
		$arrHerbsoption[$j] = $strName;
		$j++;
	      }
	  }
	if (!isset($_GET['action']))
	  {
	    $_GET['action'] = '';
	  }

	/**
	 * Dryings herbs
	 */
	if (isset($_GET['action']) && $_GET['action'] == 'dry')
	  {
	    if (!isset($_POST['amount']))
	      {
		error("Podaj ile ziół chcesz wysuszyć.");
	      }
	    checkvalue($_POST['amount']);
	    if (!in_array($_POST['herb'], $arrHerbs))
	      {
		error("Zapomnij o tym.");
	      }
	    $intAmountherbs = 10 * $_POST['amount'];
	    if ($intAmountherbs > $objHerbs -> fields[$_POST['herb']])
	      {
		error("Nie masz takiej ilości ziół!");
	      }
	    $intAmountenergy = 0.5 * $_POST['amount'];
	    if ($intAmountenergy > $player -> energy)
	      {
		error("Nie masz tyle energii!");
	      }
	    if ($player -> hp < 1)
	      {
		error("Nie możesz suszyć ziół ponieważ jesteś martwy!");
	      }
	    $intKey = array_search($_POST['herb'], $arrHerbs);
	    $intAmountseeds = 0;
	    for ($i = 0; $i < $_POST['amount']; $i++)
	      {
		$intRoll = rand(1, 100);
		if ($intRoll > 5)
		  {
		    $intAmountseeds++;
		  }
	      }
	    $intExp = $intAmountseeds;
	    if ($player->clas == 'Rzemieślnik') 
	      {
		$intExp = $intExp * 2;
	      }
	    $arrSeeds = array('ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	    $db -> Execute("UPDATE `herbs` SET `".$arrHerbs[$intKey]."`=`".$arrHerbs[$intKey]."`-".$intAmountherbs.", `".$arrSeeds[$intKey]."`=`".$arrSeeds[$intKey]."`+".$intAmountseeds." WHERE `gracz`=".$player -> id);
	    $player->checkexp(array('herbalism' => ($intExp / 2)), $player->id, 'skills');
	    $player->checkexp(array('agility' => ($intExp / 2)), $player->id, 'stats');
	    $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intAmountenergy." WHERE `id`=".$player -> id);
	    if ($player->gender == 'F')
	      {
		$strLast = "aś";
	      }
	    else
	      {
		$strLast = "eś";
	      }
	    message("success", "Wysuszyłeś <b>".$intAmountherbs."</b> sztuk ziół i otrzymaleś w zamian <b>".$intAmountseeds."</b> paczek nasion. Zdobył".$strLast." <b>".$intExp."</b> PD.");
	  }
	$smarty -> assign(array("Houseinfo" => "Witaj w chatce ogrodnika. Tutaj możesz suszyć zioła, aby otrzymać z nich nasiona potrzebne do zasiania plantacji. Za każde 10 ziół danego rodzaju otrzymujesz 1 paczkę nasion. Koszt suszenia ziół to 0.5 enegii za każdą paczkę ziół.",
				"Adry" => "Wysusz",
				"Tdry" => "aby otrzymać",
				"Tpack" => "paczek nasion",
				"Tamount" => "ilość:",
				"Herbsname" => $arrHerbsaviable,
				"Herbsamount" => $arrHerbsamount,
				"Herbsoption" => $arrHerbsoption,
				"Action" => $_GET['action']));
	$objHerbs -> Close();
      }

    /**
     * Plantation
     */
    if ($_GET['step'] == 'plantation')
      {
	$objPlantation = $db -> Execute("SELECT * FROM `farms` WHERE `owner`=".$player -> id." AND `location`='".$player->location."'");
	if ($objPlantation->fields['lands'])
	  {
	    $intLandsamount = $objPlantation -> fields['lands'];
	  }
	else
	  {
	    $intLandsamount = '';
	  }
	$smarty -> assign("Farminfo", "Witaj na plantacji. Tutaj możesz hodować różne zioła.");
	if (!isset($_GET['action']))
	  {
	    $_GET['action'] = '';
	  }

	/**
	 * Upgrade plantation
	 */
	if (isset($_GET['action']) && $_GET['action'] == 'upgrade')
	  {
	    if (!$objPlantation -> fields['lands'])
	      {
		$intMithcost = 20;
		$_POST['lamount'] = 1;
	      }
	    else
	      {
		$intMithcost = 0;
		if (isset($_POST['lamount']))
		  {
		    checkvalue($_POST['lamount']);
		    $intNewamount = $objPlantation->fields['lands'];
		    for ($i = 0; $i < $_POST['lamount']; $i++)
		      {
			if ($intNewamount < 11)
			  {
			$intPrice = 2;
			  }
			elseif ($intNewamount > 10 && $intNewamount < 21)
			  {
			    $intPrice = 5;
			  }
			elseif ($intNewamount > 20 && $intNewamount < 31)
			  {
			    $intPrice = 10;
		      }
			elseif ($intNewamount > 30)
			  {
			    $intPrice = 15;
			  }
			$intMithcost += $intPrice * $intNewamount;
			$intNewamount ++;
		      }
		  }
	      }
	    $smarty -> assign(array("Tmith" => "sztuk mithrilu.",
				    "Buyland" => "Zakup obszar ziemi za",
				    "Buylandcost" => $intMithcost));
	    
	    /**
	     * Buy land, glasshouse, irrigation etc
	     */
	    if (isset($_GET['buy']))
	      {
		$arrBuy = array('L', 'G', 'I', 'C');
		if (!in_array($_GET['buy'], $arrBuy))
		  {
		    error('Zapomnij o tym.');
		  }
		$strBuyitem = '';
		if ($_GET['buy'] == 'L')
		  {
		    if ($player -> platinum < $intMithcost)
		      {
			message('error', "Nie masz takiej ilości mithrilu!");
		      }
		    elseif (!isset($_POST['lamount']))
		      {
			message('error', 'Podaj ile ziemi chcesz dokupić do plantacji.');
		      }
		    elseif ($intLandsamount + $_POST['lamount'] > 100)
		      {
			message('error', "Nie możesz dokupić większej ilości ziemi do farmy.");
		      }
		    else
		      {
			if (!$objPlantation -> fields['lands'])
			  {
			    $db -> Execute("INSERT INTO `farms` (`owner`, `lands`, `location`) VALUES(".$player -> id.", 1, '".$player->location."')");
			    $objPlantation = $db -> Execute("SELECT * FROM `farms` WHERE `owner`=".$player -> id." AND `location`='".$player->location."'");
			    $intLandsamount = $objPlantation -> fields['lands'];
			    $strBuyitem = ' nieco ziemi pod uprawę. Kosztowało to 20 sztuk mithrilu.';
			  }
			else
			  {
			    $db -> Execute("UPDATE `farms` SET `lands`=`lands`+".$_POST['lamount']." WHERE `id`=".$objPlantation -> fields['id']);
			    $objPlantation->fields['lands'] += $_POST['lamount'];
			    $intLandsamount += $_POST['lamount'];
			    $strBuyitem = ' '.$_POST['lamount'].' obszar(ów) ziemi do plantacji. Kosztowało to '.$intMithcost.' sztuk mithrilu.';
			  }
			$db -> Execute("UPDATE players SET platinum=platinum-".$intMithcost." WHERE id=".$player -> id);
		      }
		  }
		else
		  {
		    checkvalue($_POST[strtolower($_GET['buy']).'amount']);
		    $intGoldcost = $_POST[strtolower($_GET['buy']).'amount'] * 1000;
		    $arrItems = array('glasshouse', 'irrigation', 'creeper');
		    $intKey = array_search($_GET['buy'], $arrBuy) - 1;
		    if ($player -> credits < $intGoldcost)
		      {
			message('error', "Nie masz tylu pieniędzy przy sobie aby dokonać zakupu.");
		      }
		    elseif ($objPlantation -> fields['lands'] < ($objPlantation -> fields[$arrItems[$intKey]] + $_POST[strtolower($_GET['buy']).'amount']))
		      {
			message('error', "Nie masz miejsca aby dokupić kolejne ulepszenia do farmy.");
		      }
		    else
		      {
			$db -> Execute("UPDATE farms SET ".$arrItems[$intKey]."=".$arrItems[$intKey]."+".$_POST[strtolower($_GET['buy']).'amount']." WHERE owner=".$player -> id." AND `location`='".$player->location."'") or die($db -> ErrorMsg());
			$db -> Execute("UPDATE players SET credits=credits-".$intGoldcost." WHERE id=".$player -> id);
			$arrText = array('szklarnię(i)', 'system(y) nawiadniający(e)', 'konstrukcję(i) na pnącza');
			$strBuyitem = ' '.$_POST[strtolower($_GET['buy']).'amount'].' '.$arrText[$intKey].'. Kosztowało to '.$intGoldcost.' sztuk złota.';
			$objPlantation->fields[$arrItems[$intKey]] += $_POST[strtolower($_GET['buy']).'amount'];
		      }
		  }
		if ($strBuyitem != '')
		  {
		    message("success", "Dokupiłeś".$strBuyitem);
		  }
		$_GET['action'] = '';
	      }
	  }
	
	/**
	 * Sow herbs
	 */
	if (isset($_GET['action']) && $_GET['action'] == 'sow')
	  {
	    if (!$objPlantation -> fields['lands'])
	      {
		error("Nie masz farmy aby siać zioła");
	      }
	    $objUsedlands = $db -> Execute("SELECT SUM(`amount`) FROM `farm` WHERE `farmid`=".$objPlantation->fields['id']);
	    if (!$objUsedlands->fields['SUM(`amount`)'])
	      {
		$intUsedlands = 0;
	      }
	    else
	      {
		$intUsedlands = $objUsedlands->fields['SUM(`amount`)'];
	      }
	    $objUsedlands -> Close();
	    $intFreelands = $objPlantation->fields['lands'] - $intUsedlands;
	    if ($objPlantation -> fields['lands'] == $intUsedlands)
	      {
		error("Nie masz wolnych obszarów na farmie!");
	      }
	    $objSeeds = $db -> Execute("SELECT ilani_seeds, illanias_seeds, nutari_seeds, dynallca_seeds FROM herbs WHERE gracz=".$player -> id);
	    if (!$objSeeds -> fields['ilani_seeds'] && !$objSeeds -> fields['illanias_seeds'] && !$objSeeds -> fields['nutari_seeds'] && !$objSeeds -> fields['dynallca_seeds'])
	      {
		error("Nie masz nasion aby hodować zioła!");
	      }
	    $arrSeeds = array('ilani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
	    $arrSeedsname = array("Illani", "Illanias", "Nutari", "Dynallca");
	    $arrSeedsaviable = array();
	    $arrSeedsoption = array();
	    $arrSeedsamount = array();
	    $j = 0;
	    for ($i = 0; $i < 4; $i++)
	      {
		$strName = $arrSeeds[$i];
		if ($objSeeds -> fields[$strName])
		  {
		    $arrSeedsaviable[$j] = $arrSeedsname[$i];
		    $arrSeedsamount[$j] = $objSeeds -> fields[$strName];
		    $arrSeedsoption[$j] = $strName;
		    $j++;
		  }
	      }
	    $smarty -> assign(array("Farminfo" => "Tutaj możesz zasiewać swoją farmę. Jedna paczka nasion starcza na zasianie 1 obszaru. Aby móc hodować odpowiednie zioła, musisz również posiadać odpowiednie wyposażenie. Listę wymaganych rzeczy możesz znaleźć w Encyklopedii roślin w poszczególnych opisach. Zasianie jednego obszaru ziemii kosztuje 0.2 energii, w zamian dostajesz 0.01 do umiejętności Zielarstwo.",
				    "Asaw" => "Zasiej",
				    "Tlands" => "obszarów farmy",
				    "Tamount" => "ilość:",
				    "Seedsname" => $arrSeedsaviable,
				    "Seedsamount" => $arrSeedsamount,
				    "Seedsoption" => $arrSeedsoption));
	    
	    /**
	     * Start sow herbs
	     */
	    if (isset($_GET['step2']) && $_GET['step2'] == 'next')
	      {
		if (!in_array($_POST['seeds'], $arrSeeds))
		  {
		    error('Zapomnij o tym.');
		  }
		checkvalue($_POST['amount']);
		if ($_POST['amount'] > $intFreelands)
		  {
		    error("Nie masz tyle wolnych obszarów!");
		  }
		if ($player -> hp < 1)
		  {
		    error("Nie możesz zasiać ziół ponieważ jesteś martwy!");
		  }
		$intEnergy = $_POST['amount'] * 0.2;
		if ($intEnergy > $player -> energy)
		  {
		    error("Nie masz tyle energii.");
		  }
		$intKey = array_search($_POST['seeds'], $arrSeeds);
		if ($_POST['amount'] > $objSeeds -> fields[$arrSeeds[$intKey]])
		  {
		    error("Nie masz nasion aby hodować zioła!");
		  }
		$arrHerbsname = array('illani', 'illanias', 'nutari', 'dynallca');
		if ($intKey)
		  {
		    $arrItems = array(0, $objPlantation -> fields['glasshouse'], $objPlantation -> fields['irrigation'], $objPlantation -> fields['creeper']);
		    $objFarm = $db -> Execute("SELECT amount, name FROM farm WHERE `farmid`=".$objPlantation->fields['id']);
		    $arrNeeditems = array(0, 0, 0, 0);
		    while (!$objFarm -> EOF)
		      {
			$intKey2 = array_search($objFarm -> fields['name'], $arrHerbsname);
			if ($intKey2 == 1)
			  {
			    $arrNeeditems[1] = $arrNeeditems[1] + $objFarm -> fields['amount'];
			  }
                        elseif ($intKey2 == 2)
			  {
			    $arrNeeditems[1] = $arrNeeditems[1] + $objFarm -> fields['amount'];
			    $arrNeeditems[2] = $arrNeeditems[2] + $objFarm -> fields['amount'];
			  }
                        elseif ($intKey2 == 3)
			  {
			    $arrNeeditems[1] = $arrNeeditems[1] + $objFarm -> fields['amount'];
			    $arrNeeditems[2] = $arrNeeditems[2] + $objFarm -> fields['amount'];
			    $arrNeeditems[3] = $arrNeeditems[3] + $objFarm -> fields['amount'];
			  }
			$objFarm -> MoveNext();
		      }
		    $objFarm -> Close();
		    for ($i = 1; $i <= $intKey; $i++)
		      {
			$arrNeeditems[$i] = $_POST['amount'] + $arrNeeditems[$i];
			if (!$arrItems[$i] || $arrItems[$i] < $arrNeeditems[$i])
			  {
			    error("Nie masz odpowiedniego wyposażenia na farmie aby hodować ten typ ziół!");
			  }
		      }
		  }
		$intExp = $_POST['amount'] * 5;
		if ($player->clas == 'Rzemieślnik') 
		  {
		    $intExp = $intExp * 2;
		  }
		$db -> Execute("UPDATE herbs SET ".$arrSeeds[$intKey]."=".$arrSeeds[$intKey]."-".$_POST['amount']." WHERE gracz=".$player -> id);
		$db -> Execute("INSERT INTO `farm` (`farmid`, `owner`, `amount`, `name`, `age`) VALUES(".$objPlantation->fields['id'].", ".$player->id.", ".$_POST['amount'].", '".$arrHerbsname[$intKey]."', 0)") or die($db->ErrorMsg());
		$player->checkexp(array('herbalism' => ($intExp / 2)), $player->id, 'skills');
		$player->checkexp(array('agility' => ($intExp / 2)), $player->id, 'stats');
		$db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
		message("success", "Zasiałeś <b>".$_POST['amount']."</b> obszarów farmy ziołem ".$arrHerbsname[$intKey].". Zdobyłeś <b>".$intExp."</b> PD.");
	      }
	  }
	
	/**
	 * Gather herbs
	 */
	if (isset($_GET['action']) && $_GET['action'] == 'chop')
	  {
	    if (!$objPlantation -> fields['lands'])
	      {
		error("Nie masz farmy aby zbierać zioła");
	      }
	    $objHerbs = $db -> Execute("SELECT `id`, `amount`, `name`, `age` FROM `farm` WHERE `farmid`=".$objPlantation->fields['id']);
	    if (!$objHerbs -> fields['id'])
	      {
		error("Nie hodujesz jakichkolwiek ziół!");
	      }
	    $objHerbs -> Close();
	    $smarty -> assign("Farminfo", "Tutaj możesz zbierać zioła które wcześniej zasiałeś na swojej farmie. Zebranie ziół z jednego pola kosztuje 1.5 energii. W zamian otrzymujesz 0.001 do umiejętności Zielarstwo. Zioła możesz zbierać już po jednym resecie od zasiania. Im dłużej będziesz je hodował tym więcej możesz ich zebrać. Jednak jeżeli zbyt długo będą hodowane, po prostu zwiędną. Poniżej znajduje się lista obecnie hodowanych na farmie ziół.");
        define("NO_HERBS", "Nie hodujesz jakichkolwiek ziół!");
	    /**
	     * Start gather
	     */
	    if (isset($_GET['id']))
	      {
		checkvalue($_GET['id']);
		$objHerb = $db -> Execute("SELECT `farmid`, `amount`, `name`, `age` FROM `farm` WHERE `id`=".$_GET['id']);
		if ($objHerb -> fields['farmid'] != $objPlantation->fields['id'])
		  {
		    error("Nie możesz zbierać cudzych ziół!");
		  }
		$smarty -> assign(array("Herbid" => $_GET['id'],
					"Herbname" => $objHerb -> fields['name'],
					"Agather" => "Zbieraj",
					"Froma" => 'z',
					"Tlands3" => "obszarów farmy."));
		/**
		 * Gather some herbs
		 */
		if (isset($_GET['step2']) && $_GET['step2'] == 'next')
		  {
		    if (!isset($_POST['amount']))
		      {
			error('Zapomnij o tym.');
		      }
		    checkvalue($_POST['amount']);
		    if (!$objHerb -> fields['age'])
		      {
			error("Te zioła jeszcze nie wyrosły!");
		      }
		    if ($objHerb -> fields['amount'] < $_POST['amount'])
		      {
			$_POST['amount'] = $objHerb->fields['amount'];
		      }
		    if ($player -> hp < 1)
		      {
			error("Nie możesz zbierać ziół ponieważ jesteś martwy!");
		      }
		    $intEnergy = $_POST['amount'] * 1.5;
		    if ($intEnergy > $player -> energy)
		      {
			error("Nie masz wystarczającej ilości energii.");
		      }
		    $arrAge = array(0, 1, 2, 2, 3, 3, 4, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1);
		    $arrHerbname = array('illani', 'illanias', 'nutari', 'dynallca');
		    $arrHerbmodif = array(1, 1.5, 2, 2.5);
		    $intKey = array_search($objHerb -> fields['name'], $arrHerbname);
		    $intRoll = rand(-15, 15) / 100;
		    $intKey2 = $objHerb -> fields['age'] - 1;
		    
		    /**
		     * Add bonuses to ability
		     */
		    $player->curskills(array('herbalism'), TRUE, TRUE);
		    $player->skills['herbalism'][1] += $player->checkbonus('herbalism');
		    $player->skills['herbalism'][1] += $player->checkbonus($objHerb->fields['name']);
		    
		    $intFactor = ceil(($player->stats['agility'][2] + $player->skills['herbalism'][1]) / 10);
		    $intAmount = floor((($arrAge[$intKey2] * $_POST['amount']) / $arrHerbmodif[$intKey]) * $intFactor);
		    $intAmount = floor($intAmount + ($intAmount * $intRoll));
		    if ($intAmount < 0)
		      {
			$intAmount = 0;
		      }
		    if ($objHerb -> fields['age'] > 3)
		      {
			$intExp = $intAmount * 2;
		      }
		    else
		      {
			$intExp = 0;
		      }
		    if ($player->clas == 'Rzemieślnik') 
		      {
			$intExp = $intExp * 2;
		      }
		    if ($_POST['amount'] < $objHerb -> fields['amount'])
		      {
			$db -> Execute("UPDATE `farm` SET `amount`=`amount`-".$_POST['amount']." WHERE `id`=".$_GET['id']);
		      }
                    else
		      {
			$db -> Execute("DELETE FROM `farm` WHERE `id`=".$_GET['id']);
		      }
		    $db -> Execute("UPDATE `herbs` SET `".$arrHerbname[$intKey]."`=`".$arrHerbname[$intKey]."`+".$intAmount." WHERE `gracz`=".$player -> id);
		    $player->clearbless(array('agility'));
		    $player->checkexp(array('herbalism' => ($intExp / 2)), $player->id, 'skills');
		    $player->checkexp(array('agility' => ($intExp / 2)), $player->id, 'stats');
		    $db -> Execute("UPDATE `players` SET `energy`=`energy`-".$intEnergy." WHERE `id`=".$player -> id);
		    message("success", "Zebrałeś <b>".$intAmount."</b> sztuk ".$arrHerbname[$intKey]." z farmy. W zamian zdobyłeś <b>".$intExp."</b> PD");
		  }
		$objHerb -> Close();
	      }
	  }
	
	$smarty -> assign("Lands", $intLandsamount);
	if (!$objPlantation -> fields['lands'])
	  {
	    $smarty -> assign("Aupgrade", "Nie masz jeszcze plantacji - kup ziemię pod nią za 20 sztuk mithrilu");
	  }
	else
	  {
	    $smarty -> assign("Asow", "Idź zasiać zioła");
	  }
	if ($objPlantation -> fields['id'])
	  {
	    $objUsedlands = $db -> Execute("SELECT SUM(`amount`) FROM `farm` WHERE `farmid`=".$objPlantation->fields['id']);
	    if (!$objUsedlands->fields['SUM(`amount`)'])
	      {
		$intUsedlands = 0;
	      }
	    else
	      {
		$intUsedlands = $objUsedlands->fields['SUM(`amount`)'];
	      }
	    $objUsedlands -> Close();
	    $intFreelands = $objPlantation->fields['lands'] - $intUsedlands;
	    $arrHerbs = $db->GetAll("SELECT * FROM `farm` WHERE `farmid`=".$objPlantation->fields['id']);
	    foreach ($arrHerbs as &$arrHerb)
	      {
		$arrHerb['stage'] = 'test';
		$arrAge = array(0, 1, 2, 2, 3, 3, 4, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1);
		if ($arrHerb['age'] < 2)
		  {
		    $arrHerb['stage'] = '(zasiana)';
		  }
		elseif ($arrHerb['age'] > 1 && $arrHerb['age'] < 7)
		  {
		    $arrHerb['stage'] = '(sadzonka)';
		  }
		elseif ($arrHerb['age'] > 6 && $arrHerb['age'] < 11)
		  {
		    $arrHerb['stage'] = '(młoda roślina)';
		  }
		elseif ($arrHerb['age'] > 10 && $arrHerb['age'] < 14)
		  {
		    $arrHerb['stage'] = '(rozkwita)';
		  }
		elseif ($arrHerb['age'] > 13 && $arrHerb['age'] < 18)
		  {
		    $arrHerb['stage'] = '(gotowa do zebrania)';
		  }
		elseif ($arrHerb['age'] > 17 && $arrHerb['age'] < 23)
		  {
		    $arrHerb['stage'] = '(przekwita)';
		  }
		elseif ($arrHerb['age'] > 22)
		  {
		    $arrHerb['stage'] = '(zwiędła)';
		  }
	      }
	    if (count($arrHerbs))
	      {
		$strHerbs = 'Lista hodowanych ziół:';
	      }
	    else
	      {
		$strHerbs = 'Obecnie nic nie hodujesz na farmie.';
	      }
	    if ($intLandsamount >= 100)
	      {
		$strBuylands = '';
	      }
	    else
	      {
		$strBuylands = 'obszar(ów) ziemi za';
	      }
	    if ($objPlantation->fields['glasshouse'] == $intLandsamount)
	      {
		$strBuyglass = '';
	      }
	    else
	      {
		$strBuyglass = 'szklarnię(e) za';
	      }
	    if ($objPlantation->fields['irrigation'] == $intLandsamount)
	      {
		$strBuyirrigation = '';
	      }
	    else
	      {
		$strBuyirrigation = 'system(y) nawadniający(e) za';
	      }
	    if ($objPlantation->fields['creeper'] == $intLandsamount)
	      {
		$strBuycreeper = '';
	      }
	    else
	      {
		$strBuycreeper = 'konstrukcję(e) na pnącza za';
	      }
	    $smarty->assign(array("Ilands" => "Obszarów farmy:",
				  "Iglass" => "Szklarni:",
				  "Iirrigation" => "Systemów nawadniających:",
				  "Icreeper" => "Konstrukcji na pnącza:",
				  "Ifreelands" => "Wolnych obszarów:",
				  "Tamount" => "ilość:",
				  "Tage" => "wiek:",
				  "Abuy" => "Dokup",
				  "Tmithcost" => 'sztuk mithrilu.',
				  "Tgoldcost" => 'sztuk złota.',
				  "Aland" => $strBuylands,
				  "Aglass" => $strBuyglass,
				  "Airrigation" => $strBuyirrigation,
				  "Acreeper" => $strBuycreeper,
				  "Therbs" => $strHerbs,
				  "Freelands" => $intFreelands,
				  "Herbs" => $arrHerbs,
				  "Glasshouse" => $objPlantation -> fields['glasshouse'],
				  "Irrigation" => $objPlantation -> fields['irrigation'],
				  "Creeper" => $objPlantation -> fields['creeper']));
	    $objPlantation -> Close();
	  }
      }
  }
/**
* Assign variable to template and display page
*/
$smarty -> assign(array("Step" => $_GET['step'],
			"Action" => $_GET['action'],
                        "Aback" => "Wróć"));
$smarty -> display ('farm.tpl');

require_once("includes/foot.php");
