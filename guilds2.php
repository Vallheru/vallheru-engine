<?php
/**
 *   File functions:
 *   Guilds2 - the best players in various fight skills.
 *
 *   @name                 : guilds2.php                            
 *   @copyright            : (C) 2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 13.03.2012
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

$title = 'Aula Gladiatorów';
require_once('includes/head.php');

/**
 * Function to get data from database and return array of top 10 players' names, ID's and skill values. 
 */
function topplayers($strDbfield)
{
    global $db;
    global $arrTags;
    
    if ($strDbfield == 'mpoints')
      {
	$objTop = $db -> SelectLimit("SELECT `id`, `user`, `".$strDbfield."`, `tribe` FROM `players` WHERE `klasa` IN ('Wojownik', 'Mag', 'Barbarzyńca') ORDER BY `".$strDbfield."` DESC", 10) or die($db->ErrorMsg());
      }
    else
      {
	$objTop = $db -> SelectLimit('SELECT `id`, `user`, `'.$strDbfield.'`, `tribe` FROM `players` ORDER BY `'.$strDbfield.'` DESC', 10);
      }

    $arrTop = array();
    while (!$objTop -> EOF) 
      {
	$objTop->fields['user'] = $arrTags[$objTop->fields['tribe']][0].' '.$objTop->fields['user'].' '.$arrTags[$objTop->fields['tribe']][1];
        $arrTop[] = array('user' => $objTop -> fields['user'],
			  'id' => $objTop -> fields['id'],
			  'value' => $objTop -> fields[$strDbfield]);
        $objTop -> MoveNext();
    }
    $objTop -> Close();
    return $arrTop;
}

/**
 * Show list of defeated monsters
 */
function topplayers2()
{
  global $db;
  global $arrTags;
  
  $objTop = $db->Execute("SELECT * FROM `brecords` ORDER BY `mlevel` DESC, `id` ASC") or die($db->ErrorMsg());
  $arrMonsters = array();
  
  while (!$objTop->EOF)
    {
      if (!in_array($objTop->fields['mname'], $arrMonsters))
	{
	  $objTribe = $db->Execute("SELECT `id`, `user`, `tribe` FROM `players` WHERE `id`=".$objTop->fields['pid']);
	  if ($objTribe->fields['id'])
	    {
	      $objTribe->fields['user'] = '<a href="view.php?view='.$objTop->fields['pid'].'">'.$arrTags[$objTribe->fields['tribe']][0].' '.$objTribe->fields['user'].' '.$arrTags[$objTribe->fields['tribe']][1].'</a>';
	    }
	  else
	    {
	      $objTribe->fields['user'] = 'ktoś';
	    }
	  $arrTop[] = $objTop->fields['mdate'].' dnia obecnej ery <b>'.$objTribe->fields['user'].'</b> pokonał(a) jako pierwszy(a) potwora: <b>'.$objTop->fields['mname'].'</b>';
	  $objTribe->Close();
	  $arrMonsters[] = $objTop->fields['mname'];
	}
      $objTop->MoveNext();
      if (count($arrMonsters) == 10)
	{
	  break;
	}
    }
  $objTop->Close();
  return $arrTop;
}

$arrayMonumentTitles = array("Najwyższa Walka bronią białą", "Najwyższe Strzelectwo", "Najwyższe Rzucanie czarów", "Najwięcej Uników", "Najwyższe Dowodzenie", 'Najwięcej zwycięstw', 'Wykonanych Zadań');

$arrayMonumentDescriptions = array("Walka bronią białą", "Strzelectwo", "Rzucanie czarów", "Uniki", "Dowodzenie", 'Zwycięstw', 'Zadań');
        
$arrayMonuments = array(topplayers('atak'), topplayers('shoot'), topplayers('magia'), topplayers('unik'), topplayers('leadership'), topplayers('wins'), topplayers('mpoints'));
    
$smarty -> assign(array('Titles' => $arrayMonumentTitles,
                        'Descriptions' => $arrayMonumentDescriptions,
                        'Monuments' => $arrayMonuments,
                        'Mname' => "Imię (ID)",
			"Guildinfo" => "Znajdujesz się w olbrzymim, okrągłym, wykonanym z białego kamienia budynku bez dachu. W jego centrum widzisz posągi przedstawiające postacie wojowników oraz magów. Kiedy podchodzisz bliżej nich, dostrzegasz pod każdym posągiem niewielką tabliczkę z imieniem oraz informacją na temat osiągnięć danej osoby.",
			'Guildinfo2' => 'W samym środku budynku widzisz potężną kamienną płytę, na której zapisano imiona istot, które wsławiły się na Arenie Walk.',
			'Monsters' => topplayers2())
                  );   
$smarty -> display ('guilds2.tpl');
require_once('includes/foot.php');
?>
