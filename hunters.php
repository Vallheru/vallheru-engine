<?php
/**
 *   File functions:
 *   Hunters guild - bestiary
 *
 *   @name                 : hunters.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 14.11.2011
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

$title = "Gildia Łowców";
require_once("includes/head.php");

if($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

if (!isset($_GET['step']))
  {
    $smarty -> assign(array("Desc" => 'Niewielki, drewniany budynek stoi wsród drzew. Wokół niego kręcą się postacie okryte ciemnozielonymi płaszczami. Nad drzwiami budynku widzać symbol łowców królestwa.',
			    "Abestiary" => 'Bestiariusz znanych ziem'));
    $_GET['step'] = '';
  }
elseif ($_GET['step'] == 'bestiary')
  {
    $arrMonsters = $db->GetAll("SELECT `id`, `name` FROM `monsters` WHERE `desc`!='' AND `location`='".$player->location."'");
    $smarty->assign(array("Bestiary" => 'Tutaj znajdują się opisy wszystkich potworów jakie można spotkać na terenie królestwa. Opisy te zbierane są przez najodważniejszych poszukiwaczy przygód naszej krainy. Jeżeli chcesz zawsze możesz <a href="proposals.php?type=E">zgłosić swój opis potwora</a>',
			  "Monsters" => $arrMonsters,
			  "Amount" => count($arrMonsters),
			  "Nodesc" => "Nie ma jeszcze opisów bestii w księdze."));
  }
else
  {
    checkvalue($_GET['step']);
    $objMonster = $db->Execute("SELECT `name`, `desc` FROM `monsters` WHERE `id`=".$_GET['step']." AND `location`='".$player->location."'");
    if ($objMonster->fields['desc'] == '')
      {
	error("Nie ma opisu tego potwora.");
      }
    $smarty->assign(array("Mname" => $objMonster->fields['name'],
			  "Mdesc" => $objMonster->fields['desc'],
			  "Aback" => 'Wróć do spisu potworów'));
  }

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Step" => $_GET['step']));
$smarty -> display ('hunters.tpl');

require_once("includes/foot.php");
?>
