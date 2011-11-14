<?php
/**
 *   File functions:
 *   Monuments - the best players in various fields.
 *
 *   @name                 : monuments.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
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

$title = 'Posągi';
require_once('includes/head.php');

/**
* Get the localization for game
*/
require_once('languages/'.$player -> lang.'/monuments.php');

/**
 * Function to get data from database and return array of top 5 players' names, ID's and skill values. 
 */
function topplayers($strDbfield, $blnHidden = FALSE)
{
    global $db;
    global $arrTags;
    
    $objTop = $db -> SelectLimit('SELECT `id`, `user`, `'.$strDbfield.'`, `tribe` FROM `players` ORDER BY `'.$strDbfield.'` DESC', 5);

    while (!$objTop -> EOF) 
      {
	if ($blnHidden)
	  {
	    $objTop->fields['user'] = 'XXXXX';
	    $objTop->fields['id'] = 'X';
	  }
	else
	  {
	    $objTop->fields['user'] = $arrTags[$objTop->fields['tribe']][0].' '.$objTop->fields['user'].' '.$arrTags[$objTop->fields['tribe']][1];
	  }
        $arrTop[] = array('user' => $objTop -> fields['user'],
			  'id' => $objTop -> fields['id'],
			  'value' => $objTop -> fields[$strDbfield]);
        $objTop -> MoveNext();
    }
    $objTop -> Close();
    return $arrTop;
}

// Set monument groups.
$arrayMonumentGroups = array(PLAYER_RANKING, STATS, COMBAT_SKILLS, ARTISAN_SKILLS, "Posągi różnych umiejętności" );
// For each group decide which monuments should go there...
$arrayMonumentTitles = array(array(HIGHEST_LEVEL, HIGHEST_WINS, HIGHEST_GOLD_IN_MONEYBAG, HIGHEST_GOLD_ON_ACCOUNT, "Najwięcej Vallarów"),
                             array(HIGHEST_STRENGTH, HIGHEST_ENDURANCE, HIGHEST_INTELLIGENCE, HIGHEST_WISDOM, HIGHEST_SPEED, HIGHEST_AGILITY),
                             array(HIGHEST_SIDEARMS_SKILL, HIGHEST_GAME_SHOOTING, HIGHEST_SPELL_CASTING, HIGHEST_DODGING, HIGHEST_LEADERSHIP),
                             array(HIGHEST_SMITHING, HIGHEST_CARPENTERING, HIGHEST_ALCHEMY, HIGHEST_HERBALISM, HIGHEST_JEWELLERS_CRAFT, HIGHEST_BREEDING, HIGHEST_MINING, HIGHEST_WOODCUTTING, 'Najwyższe Hutnictwo'),
			     array("Najwyższa Spostrzegawczość", "Najwyższe Złodziejstwo")
                             );
// ...and add description of stat/skill/achievement.
$arrayMonumentDescriptions = array(array(LEVEL, WINS, GOLD_IN_MONEYBAG, GOLD_ON_ACCOUNT, "Vallary"),
                                   array(STRENGTH, ENDURANCE, INTELLIGENCE, WISDOM, SPEED, AGILITY),
                                   array(SIDEARMS_SKILL, GAME_SHOOTING, SPELL_CASTING, DODGING, LEADERSHIP),
                                   array(SMITHING, CARPENTERING, ALCHEMY, HERBALISM, JEWELLERS_CRAFT, BREEDING, MINING, WOODCUTTING, 'Hutnictwo'),
				   array("Spostrzegawczość", "Złodziejstwo")
                                   );
        
$arrayMonuments = array(array(topplayers('level'), topplayers('wins'), topplayers('credits'),topplayers('bank'), topplayers('vallars')),
                        array(topplayers('strength'), topplayers('wytrz'), topplayers('inteli'), topplayers('wisdom'), topplayers('szyb'), topplayers('agility')),
                        array(topplayers('atak'), topplayers('shoot'), topplayers('magia'), topplayers('unik'), topplayers('leadership')),
                        array(topplayers('ability'), topplayers('fletcher'), topplayers('alchemia'), topplayers('herbalist'), topplayers('jeweller'), topplayers('breeding'), topplayers('mining'), topplayers('lumberjack'), topplayers('metallurgy')),
			array(topplayers('perception'), topplayers('thievery', TRUE))
                        );
    
$smarty -> assign(array('Groups' => $arrayMonumentGroups, 
                        'Titles' => $arrayMonumentTitles,
                        'Descriptions' => $arrayMonumentDescriptions,
                        'Monuments' => $arrayMonuments,
                        'Mname' => M_NAME)
                  );   
$smarty -> display ('monuments.tpl');
require_once('includes/foot.php');
?>
