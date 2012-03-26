<?php
/**
 *   File functions:
 *   Core market
 *
 *   @name                 : cmarket.php                            
 *   @copyright            : (C) 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 26.03.2012
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

$title = "Rynek chowańców";
require_once("includes/head.php");

if ($player->location != 'Altara' && $player->location != 'Ardulith') 
  {
    error('Nie znajdujesz się w mieście.');
  }

if (!isset($_GET['view']))
  {
    $smarty -> assign(array("Marketinfo" => "Witaj na rynku z Chowańcami. Tutaj możesz kupić Chowańca od innych graczy. Co chcesz zrobić?",
			    "Ashow" => 'Zobacz oferty',
			    "Aadd" => 'Dodaj ofertę',
			    "Adelete" => 'Skasuj wszystkie swoje oferty',
			    "Aback2" => 'Wróć na rynek'));
    $_GET['view'] = '';
  }

if (isset ($_GET['view']) && $_GET['view'] == 'market') 
  {
    $market = $db -> Execute("SELECT * FROM core_market ORDER BY id DESC");
    $arrlink = array ();
    $i = 0;
    while (!$market -> EOF) 
      {
	if ($market -> fields['gender'] == 'M')
	  {
	    $strGender = 'Samiec';
	  }
	else
	  {
	    $strGender = 'Samica';
	  }
	if ($market -> fields['seller'] == $player -> id) 
	  {
	    $arrlink[$i] = "<tr><td>".$market -> fields['name']." (".$strGender.")</td><td>".$market -> fields['power']."</td><td>".$market -> fields['defense']."</td><td>Mój</td><td>".$market -> fields['cost']." sz</td><td><a href=cmarket.php?view=market&amp;remove=".$market -> fields['id'].">Usuń</a></td></tr>";
	  } 
	else 
	  {
	    $arrlink[$i] = "<tr><td>".$market -> fields['name']." (".$strGender.")</td><td>".$market -> fields['power']."</td><td>".$market -> fields['defense']."</td><td><a href=view.php?view=".$market -> fields['seller'].">".$market -> fields['seller']."</a></td><td>".$market -> fields['cost']." sz</td><td><a href=cmarket.php?view=market&amp;buy=".$market -> fields['id'].">Kup</a></td></tr>";
	  }
	$market -> MoveNext();
	$i = $i + 1;
      }
    $market -> Close();
    $smarty -> assign(array("Link" => $arrlink,
			    "Listinfo" => "Tutaj są oferty sprzedaży Chowańców przez innych graczy.",
			    "Liname" => "Nazwa chowańca",
			    "Liid" => "ID Sprzedającego",
			    "Licost" => "Cena",
			    "Lipower" => "Siła",
			    "Lidefense" => "Obrona",
			    "Coptions" => "Opcje"));
    if (isset($_GET['remove'])) 
      {
	$rem = $db -> Execute("SELECT * FROM core_market WHERE id=".$_GET['remove']);
	if ($rem -> fields['seller'] != $player -> id) 
	  {
	    error ("To nie jest twoja oferta!");
	  } 
	else 
	  {
	    $db -> Execute("INSERT INTO core (owner,name,type,power,defense, gender, ref_id, wins, losses) VALUES(".$player -> id.",'".$rem -> fields['name']."','".$rem -> fields['type']."',".$rem -> fields['power'].",".$rem -> fields['defense'].", '".$rem -> fields['gender']."', ".$rem -> fields['ref_id'].", ".$rem -> fields['wins'].", ".$rem -> fields['losses'].")") or error("Could not get back.");
	    $db -> Execute("DELETE FROM core_market WHERE id=".$rem -> fields['id']);
	    error ("Usunąłeś ofertę. Twój Chowaniec <b>".$rem -> fields['name']."</b> wrócił do ciebie.");
	  }
      }
    if (isset($_GET['buy'])) 
      {
	checkvalue($_GET['buy']);
	$buy = $db -> Execute("SELECT * FROM core_market WHERE id=".$_GET['buy']);
	if ($buy -> fields['seller'] == $player -> id) 
	  {
	    error ("Nie możesz kupić swojego Chowańca.");
	  } 
	else 
	  {
	    if ($player -> credits < $buy -> fields['cost']) 
	      {
		error ("Nie masz tylu sztuk złota przy sobie.");
	      } 
	    else 
	      {
		$db -> Execute("INSERT INTO core (owner,name,type,power,defense, gender, ref_id, wins, losses) VALUES(".$player -> id.",'".$buy -> fields['name']."','".$buy -> fields['type']."',".$buy -> fields['power'].",".$buy -> fields['defense'].", '".$buy -> fields['gender']."', ".$buy -> fields['ref_id'].", ".$buy -> fields['wins'].", ".$buy -> fields['losses'].")") or error($db -> ErrorMsg());
		$db -> Execute("UPDATE players SET credits=credits-".$buy -> fields['cost']." WHERE id=".$player -> id);
		$db -> Execute("UPDATE players SET bank=bank+".$buy -> fields['cost']." WHERE id=".$buy -> fields['seller']);
		$db -> Execute("DELETE FROM core_market WHERE id=".$buy -> fields['id']);
		$strDate = $db -> DBDate($newdate);
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$buy -> fields['seller'].",'<b><a href=view.php?view=".$player -> id.">".$player -> user.'</a></b>, ID <b>'.$player -> id."</b> kupił Twojego Chowańca ".$buy -> fields['name']." za ".$buy -> fields['cost']." sztuk złota.', ".$strDate.", 'R')");
		error ("Kupiłeś <b>Chowańca ".$buy -> fields['name']."</b> za <b>".$buy -> fields['cost']."</b> sztuk złota.");
	      }
	  }
      }
  }

/**
 * Add core to market
 */
if (isset ($_GET['view']) && $_GET['view'] == 'add') 
  {
    $arrcoreid = array();
    $arrname = array();
    $i = 0;
    $mc = $db -> Execute("SELECT * FROM core WHERE owner=".$player -> id);
    while (!$mc -> EOF) 
      {
	$arrcoreid[$i] = $mc -> fields['id'];
	if (!empty($mc -> fields['corename']))
	  {
	    $arrname[$i] = $mc -> fields['corename']." (".$mc -> fields['name'].")";
	  }
	else
	  {
	    $arrname[$i] = $mc -> fields['name'];
	  }
	if ($mc->fields['gender'] == 'F')
	  {
	    $arrname[$i] .= ' (Samica';
	  }
	else
	  {
	    $arrname[$i] .= ' (Samiec';
	  }
	$arrname[$i] .= ' Siła: '.$mc->fields['power'].' Obrona: '.$mc->fields['defense'].')';
	$mc -> MoveNext();
	$i = $i + 1;
      }
    $mc -> Close();
    $smarty -> assign(array("Coreid1" => $arrcoreid, 
			    "Corename" => $arrname,
			    "Addinfo" => "Tutaj dodasz swoją ofertę sprzedaży chowańca.",
			    "Addmy" => "Dodaj mojego",
			    "Addcore" => "Chowańca za",
			    "Coins" => "sztuk złota.",
			    "Asell" => "Dodaj"));
    if (isset ($_GET['action']) && $_GET['action'] == 'add') 
      {
	checkvalue($_POST['cost']);
	$query = $db -> Execute("SELECT count(`id`) FROM `core_market` WHERE `seller`=".$player -> id);
	$numon = $query -> fields['count(`id`)'];
	$query -> Close();
	if ($numon >= 5) 
	  {
	    error ("Możesz maksymalnie wystawić 5 ofert na raz!");
	  } 
	else 
	  {
	    checkvalue($_POST['add_core']);
	    $sc = $db -> Execute("SELECT * FROM core WHERE id=".$_POST['add_core']);
	    if ($sc -> fields['owner'] != $player -> id) 
	      {
		error ("Nie możesz sprzedać cudzego chowańca!");
	      }
	    if ($sc -> fields['status'] != 'Alive')
	      {
		error("Nie możesz wystawić chowańca na rynku, ponieważ jest martwy!");
	      }
	    $db -> Execute("INSERT INTO core_market (seller, cost, name, type, power, defense, gender, ref_id, wins, losses) VALUES(".$player -> id.",".$_POST['cost'].",'".$sc -> fields['name']."','".$sc -> fields['type']."',".$sc -> fields['power'].",".$sc -> fields['defense'].", '".$sc -> fields['gender']."', ".$sc -> fields['ref_id'].", ".$sc -> fields['wins'].", ".$sc -> fields['losses'].")");
	    $db -> Execute("DELETE FROM core WHERE id=".$_POST['add_core']);
	    error ("Dodałeś swojego <b>".$sc -> fields['name']." Chowańca</b> na rynku za <b>".$_POST['cost']."</b> sztuk złota.");
	  }
      }
  }

/**
 * Delete all oferts from market
 */
if (isset($_GET['view']) && $_GET['view'] == 'del')
  {
    require_once('includes/marketdelall.php');
    deleteallcores($player->id);
    error('Wycofałeś wszystkie swoje oferty chowańców z rynku.');
  }

$smarty->assign(array("View" => $_GET['view'],
		      "Aback" => "Wróć"));
$smarty -> display ('cmarket.tpl');

require_once("includes/foot.php");
?>