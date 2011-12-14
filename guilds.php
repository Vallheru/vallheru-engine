<?php
/**
 *   File functions:
 *   Guilds - the best players in various crafts.
 *
 *   @name                 : guilds.php                            
 *   @copyright            : (C) 2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
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

$title = 'Gildia Rzemieślników';
require_once('includes/head.php');

/**
 * Function to get data from database and return array of top 10 players' names, ID's and skill values. 
 */
function topplayers($strDbfield)
{
    global $db;
    global $arrTags;
    
    $objTop = $db -> SelectLimit('SELECT `id`, `user`, `'.$strDbfield.'`, `tribe` FROM `players` ORDER BY `'.$strDbfield.'` DESC', 10);

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

$arrayMonumentTitles = array("Najwyższe Kowalstwo", "Najwyższe Stolarstwo", "Najwyższa Alchemia", "Najwyższe Zielarstwo", "Najwyższe Jubilerstwo", "Najwyższa Hodowla", "Najwyższe Górnictwo", "Najwyższe Drwalnictwo", 'Najwyższe Hutnictwo');

$arrayMonumentDescriptions = array("Kowalstwo", "Stolarstwo", "Alchemia", "Zielarstwo", "Jubilerstwo", "Hodowla", "Górnictwo", "Drwalnictwo", 'Hutnictwo');
        
$arrayMonuments = array(topplayers('ability'), topplayers('fletcher'), topplayers('alchemia'), topplayers('herbalist'), topplayers('jeweller'), topplayers('breeding'), topplayers('mining'), topplayers('lumberjack'), topplayers('metallurgy'));
    
$smarty -> assign(array('Titles' => $arrayMonumentTitles,
                        'Descriptions' => $arrayMonumentDescriptions,
                        'Monuments' => $arrayMonuments,
                        'Mname' => "Imię (ID)",
			"Guildinfo" => "Wchodzisz do dużego, wykonanego z kamienia budynku. W wielkim holu, na bogato zdobionych ścianach, widzisz długą galerię portretów, przedstawiającą różnych mieszkańców ".$gamename.". To najznamienitsi rzemieślnicy w całym królestwie.")
                  );   
$smarty -> display ('guilds.tpl');
require_once('includes/foot.php');
?>
