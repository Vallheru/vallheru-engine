<?php
/**
 *   File functions:
 *   Guilds - the best players in various crafts.
 *
 *   @name                 : guilds.php                            
 *   @copyright            : (C) 2011, 2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 25.10.2012
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

$arrMembers = $db->GetAll("SELECT `id`, `user`, `tribe`, `skills` FROM `players`");
foreach ($arrMembers as &$arrMember)
{
  $arrMember['user'] = $arrTags[$arrMember['tribe']][0].' '.$arrMember['user'].' '.$arrTags[$arrMember['tribe']][1];
  $arrTmp = explode(';', $arrMember['skills']);
  $arrValues = array();
  foreach ($arrTmp as $strField)
    {
      $arrTmp2 = explode(':', $strField);
      if ($arrTmp2[0] == '')
	{
	  continue;
	}
      $arrValues[$arrTmp2[0]] = explode(',', $arrTmp2[1]);
    }
  foreach ($arrValues as $strKey => $arrValues)
    {
      $arrMember[$strKey] = $arrValues[1];
    }
}

function topplayers($strSkill)
{
  global $arrMembers;

  $arrSkills = array();
  foreach ($arrMembers as $key => $value)
    {
      $arrSkills[$key] = $value[$strSkill];
    }
  array_multisort($arrSkills, SORT_DESC, $arrMembers);
  if (count($arrMembers) < 10)
    {
      $intMax = count($arrMembers);
    }
  else
    {
      $intMax = 10;
    }
  $arrTop = array();
  for ($i = 0; $i < $intMax; $i++)
    {
      $arrTop[] = array('user' => $arrMembers[$i]['user'],
			'id' => $arrMembers[$i]['id'],
			'value' => $arrMembers[$i][$strSkill]);
    }
  return $arrTop;
}

/**
 * Function to get data from database and return array of top 10 players' names, ID's and skill values. 
 */
function topplayers2($strDbfield)
{
    global $db;
    global $arrTags;
    
    $objTop = $db -> SelectLimit("SELECT `id`, `user`, `mpoints`, `tribe` FROM `players` WHERE `klasa`='Rzemieślnik' ORDER BY `".$strDbfield."` DESC", 10) or die($db->ErrorMsg());

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

$arrayMonumentTitles = array("Najwyższe Kowalstwo", "Najwyższe Stolarstwo", "Najwyższa Alchemia", "Najwyższe Zielarstwo", "Najwyższe Jubilerstwo", "Najwyższa Hodowla", "Najwyższe Górnictwo", "Najwyższe Drwalnictwo", 'Najwyższe Hutnictwo', 'Wykonanych Zadań');

$arrayMonumentDescriptions = array("Kowalstwo", "Stolarstwo", "Alchemia", "Zielarstwo", "Jubilerstwo", "Hodowla", "Górnictwo", "Drwalnictwo", 'Hutnictwo', 'Zadań');
        
$arrayMonuments = array(topplayers('smith'), topplayers('carpentry'), topplayers('alchemy'), topplayers('herbalism'), topplayers('jewellry'), topplayers('breeding'), topplayers('mining'), topplayers('lumberjack'), topplayers('smelting'), topplayers2('mpoints'));
    
$smarty -> assign(array('Titles' => $arrayMonumentTitles,
                        'Descriptions' => $arrayMonumentDescriptions,
                        'Monuments' => $arrayMonuments,
                        'Mname' => "Imię (ID)",
			"Guildinfo" => "Wchodzisz do dużego budynku wykonanego z ciężkich, kamiennych bloków. W wielkim holu, na bogato zdobionych ścianach dostrzegasz długą galerię portretów w solidnych ramach. Przyglądasz się uważniej i udaje ci się rozpoznać kilka z przedstawionych postaci. To najznamienitsi rzemieślnicy w całym królestwie.")
                  );   
$smarty -> display ('guilds.tpl');
require_once('includes/foot.php');
?>
