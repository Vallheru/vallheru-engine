<?php
/**
 *   File functions:
 *   Warehouse - sell minerals and herbs
 *
 *   @name                 : warehouse.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 18.07.2012
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

$title = "Magazyn Królewski";
require_once("includes/head.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error ('Nie znajdujesz się w mieście.');
}

$arrItems = array('copperore', 'zincore', 'tinore', 'ironore', 'copper', 'bronze', 'brass', 'iron', 'steel', 'coal', 'adamantium', 'meteor', 'crystal', 'pine', 'hazel', 'yew', 'elm', 'mithril', 'illani', 'illanias', 'nutari', 'dynallca', 'illani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
$objGold = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='gold'");

/**
* Sell or buy herbs and minerals
*/
if (isset($_GET['action']) && ($_GET['action'] == 'sell' || $_GET['action'] == 'buy'))
  {
    $_GET['item'] = intval($_GET['item']);
    if ($_GET['item'] < 0 || $_GET['item'] > 25)
    {
        error('Zapomnij o tym.');
    }
    $intItem = $_GET['item'];
    $arrItemname = array("rudy miedzi", "rudy cynku", "rudy cyny", "rudy żelaza", "sztabek miedzi", "sztabek brązu", "sztabek mosiądzu", "sztabek żelaza", "sztabek stali", "brył węgla", "brył adamantium", "kawałków meteorytu", "kryształów", "drewna sosnowego", "drewna z leszczyny", "drewna cisowego", "drewna z wiązu", "mithrilu", "illani", "illanias", "nutari", "dynallca", "nasion illani", "nasion illanias", "nasion nutari", "nasion dynallca");
    if ($_GET['action'] == 'sell')
    {
        if ($intItem == 17)
        {
            $intAmount = $player -> platinum;
        }
        if ($intItem < 17)
        {
            $objTest = $db -> Execute("SELECT `".$arrItems[$intItem]."` FROM `minerals` WHERE `owner`=".$player -> id);
            $intAmount = $objTest -> fields[$arrItems[$intItem]];
        }
        if ($intItem > 17)
        {
            if ($arrItems[$intItem] == 'illani_seeds')
            {
                $strHerb = 'ilani_seeds';
            }
            else
            {
                $strHerb = $arrItems[$intItem];
            }
            $objTest = $db -> Execute("SELECT `".$strHerb."` FROM `herbs` WHERE `gracz`=".$player -> id);
            $intAmount = $objTest -> fields[$strHerb];
        }
        $smarty -> assign(array("Asell" => "Sprzedaj",
				"Youhave" => " spośród posiadanych ",
				"Tgold" => 'Złota w królestwie: '.$objGold->fields['value']));
    }
        else
    {
        $objAmount = $db -> Execute("SELECT amount FROM warehouse WHERE reset=1 AND mineral='".$arrItems[$intItem]."'");
        if ($objAmount -> fields['amount'])
        {
            $intAmount = $objAmount -> fields['amount'];
        }
            else
        {
            $intAmount = 0;
        }
        $objAmount -> Close();
        $smarty -> assign(array("Abuy" => "Kup",
				"Wamount" => " w magazynie znajduje się "));
    }
    $objPrice = $db -> Execute("SELECT value FROM settings WHERE setting='".$arrItems[$intItem]."'");
    if ($_GET['action'] == 'sell')
      {
	$intPrice = $objPrice->fields['value'];
      }
    else
      {
	$intPrice = $objPrice->fields['value'] * 2;
      }
    $smarty -> assign(array("Itemname" => $arrItemname[$intItem],
			    "Item" => $_GET['item'],
			    "Aback" => "Wróć",
			    "Tamount" => " sztuk ",
			    "Iamount" => $intAmount,
			    "Price" => $intPrice));
    if (isset($_GET['action2']) && ($_GET['action2'] == 'sell' || $_GET['action2'] == 'buy'))
    {
        if (!isset($_POST['amount']))
        {
            error("Zapomnij o tym.");
        }
	checkvalue($_POST['amount']);
	$blnValid = TRUE;
        if ($_POST['amount'] > $intAmount)
        {
	    if ($_GET['action'] == 'sell')
	      {
		$strAmount = "Nie masz tyle sztuk ";
	      }
	    else
	      {
		$strAmount = "Nie ma tyle sztuk ";
	      }
            message('error', $strAmount.$arrItemname[$intItem]);
	    $blnValid = FALSE;
        }
	if ($blnValid)
	  {
	    $objReset = $db -> Execute("SELECT reset FROM warehouse WHERE reset=1 AND mineral='".$arrItems[$intItem]."'");
	    if ($_GET['action2'] == 'sell')
	      {
		$intGold = ($objPrice -> fields['value'] * $_POST['amount']);
		if ($intGold > $objGold->fields['value'])
		  {
		    message('error', 'Nie posiadamy aż tyle złota aby móc kupić tyle surowców.');
		    $blnValid = FALSE;
		  }
		else
		  {
		    $db -> Execute("UPDATE players SET credits=credits+".$intGold." WHERE id=".$player -> id);
		    $objGold->fields['value'] -= $intGold;
		    $db->Execute("UPDATE `settings` SET `value`='".$objGold->fields['value']."' WHERE `setting`='gold'");
		    if ($objReset -> fields['reset'])
		      {
			$db -> Execute("UPDATE warehouse SET sell=sell+".$_POST['amount'].", amount=amount+".$_POST['amount']." WHERE reset=1 AND mineral='".$arrItems[$intItem]."'");
		      }
		  }
	      }
            else
	      {
		$intGold = ($objPrice -> fields['value'] * $_POST['amount']) * 2;
		if ($intGold > $player -> credits)
		  {
		    message('error', "Nie masz tylu sztuk złota przy sobie.");
		    $blnValid = FALSE;
		  }
		else
		  {
		    $db -> Execute("UPDATE players SET credits=credits-".$intGold." WHERE id=".$player -> id);
		    $objGold->fields['value'] += $intGold;
		    $db->Execute("UPDATE `settings` SET `value`='".$objGold->fields['value']."' WHERE `setting`='gold'");
		    if ($objReset -> fields['reset'])
		      {
			$db -> Execute("UPDATE warehouse SET buy=buy+".$_POST['amount'].", amount=amount-".$_POST['amount']." WHERE reset=1 AND mineral='".$arrItems[$intItem]."'");
		      }
		  }
	      }
	    $objReset -> Close();
	  }
	if ($blnValid)
	  {
	    if ($intItem == 17)
	      {
		if ($_GET['action2'] == 'sell')
		  {
		    $db -> Execute("UPDATE players SET platinum=platinum-".$_POST['amount']." WHERE id=".$player -> id);
		  }
                else
		  {
		    $db -> Execute("UPDATE players SET platinum=platinum+".$_POST['amount']." WHERE id=".$player -> id);
		  }
	      }
	    elseif ($intItem < 17)
	      {
		if ($_GET['action2'] == 'sell')
		  {
		    $db -> Execute("UPDATE minerals SET ".$arrItems[$intItem]."=".$arrItems[$intItem]."-".$_POST['amount']." WHERE owner=".$player -> id);
		  }
                else
		  {
		    $objTest = $db -> Execute("SELECT owner FROM minerals WHERE owner=".$player -> id);
		    if (!$objTest -> fields['owner']) 
		      {
			$db -> Execute("INSERT INTO minerals (owner, ".$arrItems[$intItem].") VALUES(".$player -> id.",".$_POST['amount'].")");
		      } 
                    else 
		      {
			$db -> Execute("UPDATE minerals SET ".$arrItems[$intItem]."=".$arrItems[$intItem]."+".$_POST['amount']." WHERE owner=".$player -> id);
		      }
		    $objTest -> Close();
		  }
	      }
	    elseif ($intItem > 17)
	      {
		if ($arrItems[$intItem] == 'illani_seeds')
		  {
		    $strHerb = 'ilani_seeds';
		  }
                else
		  {
		    $strHerb = $arrItems[$intItem];
		  }
		if ($_GET['action2'] == 'sell')
		  {
		    $db -> Execute("UPDATE herbs SET ".$strHerb."=".$strHerb."-".$_POST['amount']." WHERE gracz=".$player -> id);
		  }
                else
		  {
		    $objTest = $db -> Execute("SELECT gracz FROM herbs WHERE gracz=".$player -> id);
		    if (!$objTest -> fields['gracz']) 
		      {
			$db -> Execute("INSERT INTO herbs (gracz, ".$strHerb.") VALUES(".$player -> id.",".$_POST['amount'].")");
		      } 
                    else 
		      {
			$db -> Execute("UPDATE herbs SET ".$strHerb."=".$strHerb."+".$_POST['amount']." WHERE gracz=".$player -> id);
		      }
		    $objTest -> Close();
		  }
	      }
	    if ($_GET['action2'] == 'sell')
	      {
		message('success', "Sprzedałeś ".$_POST['amount']." sztuk ".$arrItemname[$intItem]." za ".$intGold." sztuk złota.");
		unset($_GET['action']);
	      }
	    else
	      {
		message('success', "Kupiłeś ".$_POST['amount']." sztuk ".$arrItemname[$intItem]." za ".$intGold." sztuk złota.");
		unset($_GET['action']);
	      }
	  }
    }
    $objPrice->Close();
}

/**
* Main menu
*/
if (!isset($_GET['action']))
{
    $arrMinname = array("Ruda miedzi", "Ruda cynku", "Ruda cyny", "Ruda żelaza", "Sztabki miedzi", "Sztabki brązu", "Sztabki mosiądzu", "Sztabki żelaza", "Sztabki stali", "Bryły węgla", "Bryły adamantium", "Kawałki meteorytu", "Kryształów", "Drewno sosnowe", "Drewno z leszczyny", "Drewno cisowe", "Drewno z wiązu", "Mithril");
    $arrHerbname = array("Illani", "Illanias", "Nutari", "Dynallca", "Nasiona Illani", "Nasiona Illanias", "Nasiona Nutari", "Nasiona Dynallca");
    $arrHerb = array(18, 19, 20, 21, 22, 23, 24, 25);
    $arrCostmin = array();
    $arrCostherb = array();
    $arrSellmin = array();
    $arrSellherb = array();
    $arrAmountmin = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    $arrAmountherb = array(0, 0, 0, 0, 0, 0, 0, 0);
    $arrItems2 = array("'copperore'", "'zincore'", "'tinore'", "'ironore'", "'copper'", "'bronze'", "'brass'", "'iron'", "'steel'", "'coal'", "'adamantium'", "'meteor'", "'crystal'", "'pine'", "'hazel'", "'yew'", "'elm'", "'mithril'", "'illani'", "'illanias'", "'nutari'", "'dynallca'", "'illani_seeds'", "'illanias_seeds'", "'nutari_seeds'", "'dynallca_seeds'");
    $objValue = $db->Execute("SELECT `value`, `setting` FROM `settings` WHERE `setting` IN (".implode(',', $arrItems2).")") or die($db->ErrorMsg());
    while(!$objValue->EOF)
      {
	$intKey = array_search($objValue->fields['setting'], $arrItems);
	if ($intKey < 18)
	  {
	    $arrCostmin[$intKey] = $objValue->fields['value'];
	    $arrSellmin[$intKey] = ceil($arrCostmin[$intKey] * 2);
	  }
	else
	  {
	    $arrCostherb[$intKey - 18] = $objValue->fields['value'];
	    $arrSellherb[$intKey - 18] = ceil($arrCostherb[$intKey - 18] * 2);
	  }
	$objValue->MoveNext();
      }
    $objValue->Close();
    $objAmount = $db->Execute("SELECT `amount`, `mineral` FROM `warehouse` WHERE `reset`=1 AND `mineral` IN (".implode(',', $arrItems2).")");
    while (!$objAmount->EOF)
      {
	$intKey = array_search($objAmount->fields['mineral'], $arrItems);
	if ($intKey < 18)
	  {
	    $arrAmountmin[$intKey] = $objAmount -> fields['amount'];
	  }
	else
	  {
	    $arrAmountherb[$intKey - 18] = $objAmount -> fields['amount'];
	  }
	$objAmount->MoveNext();
      }
    $objAmount->Close();
    /**
     * Info about caravan
     */
    $objCaravan = $db -> Execute("SELECT value FROM settings WHERE setting='caravan'");
    if ($objCaravan -> fields['value'] == 'Y')
    {
        $strCaravan = "<br />Dzisiaj odwiedziła magazyn karawana z dalekich krain wykupując co nieco minerałów.<br /><br />";
    }
        else
    {
        $strCaravan = "<br /><br />";
    }
    $smarty -> assign(array("Minname" => $arrMinname,
			    "Herbname" => $arrHerbname,
			    "Costmin" => $arrCostmin,
			    "Costherb" => $arrCostherb,
			    "Herb" => $arrHerb,
			    "Sellmin" => $arrSellmin,
			    "Sellherb" => $arrSellherb,
			    "Amountmin" => $arrAmountmin,
			    "Amountherb" => $arrAmountherb,
			    "Caravaninfo" => $strCaravan,
			    "Tmin" => "Minerał",
			    "Therb" => "Zioło",
			    "Tcost" => "Skup / Sprzedaż",
			    "Taction" => "Akcje",
			    "Asell" => "Sprzedaj",
			    "Abuy" => "Kup",
			    "Tamount" => "Ilość"));
    $_GET['action'] = '';
}

if ($player->location != 'Ardulith')
  {
    $strWarehouseinfo = "Wchodzisz do Magazynu Królewskiego. Jesteś pod wrażeniem widząc wnętrze tej ogromnej budowli. Z podziwu wyrywają Cię krzyki. To jakiś krasnolud kłóci się z magazynierem, ostro gestykulując...<br /><br /><i>Przecież surowce z mojej, podkreślam - mojej kopalni,  są więcej warte niż te psie grosze, które mi oferujesz!!! Toż to wyzysk!!! Kiedyś ktoś się wkurzy i puści cały ten wasz magazyn z dymem!!</i><br /><br />Bierze sakwy leżące na stole i wychodzi energicznym krokiem mrucząc pod nosem jakieś przekleństwa. Widzisz jak natychmiast pojawiają się pracownicy i zabierają towar kupiony od krasnoluda. Zauważasz zbliżającego się do Ciebie magazyniera . Na chwilę przystaje. Spogląda na odchodzącego krasnoluda i ze zrezygnowaniem kiwa głową. Po chwili zwraca się do Ciebie.<br /><br /><i>Witam w Królewskim Magazynie. Skupujemy wszelkie surowce, zioła, wszystkich
rodzajów. Oto ceny obowiązujące dzisiaj.<br />Obecnie dysponujemy ".$objGold->fields['value']." sztukami złota.</i>";
  }
else
  {
    $strWarehouseinfo = "Schodzisz krętymi schodami w dół. Czujesz lekki swąd palących się świec, a oczy zaczynają ci łzawić. Schody prowadzą do jakiegoś pomieszczenia... Po chwili twoje oczy przyzwyczajają się do półmroku. Widzisz ogromną podziemną halę wypełnioną stertami wszelkiego rodzaju rud, liczne regały z miksturami oraz wielkie wiklinowe kosze wypełnione po brzegi ziołami. Wszystko najlepszej jakości. Zewsząd otacza cię gwar sprzeczek i targów sprzedawców i użerających się z nimi klientów.<br />Nieco onieśmielony podchodzisz do pierwszego stoiska i widzisz uśmiechającego się gnoma ubranego w czerwoną, zdobną szatę.<br /><br />- Czym mogę służyć, Panie? - dochodzi do ciebie miły, służalczy głos...<br />Obecnie dysponujemy ".$objGold->fields['value']." sztukami złota.";
  }
$objGold->Close();

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'],
			"Warehouseinfo" => $strWarehouseinfo,
			"Warehouseinfo2" => "Co możesz nam zaoferować?",
			"Warehouseinfo3" => "Co chcesz kupić?"));
$smarty -> display('warehouse.tpl');

require_once("includes/foot.php");
?>
