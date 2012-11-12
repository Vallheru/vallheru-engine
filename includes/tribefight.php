<?php
/**
 *   File functions:
 *   Tribes fight
 *
 *   @name                 : tribefight.php                            
 *   @copyright            : (C) 2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 12.11.2012
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

if ($player -> id != $mytribe -> fields['owner'] && !$perm -> fields['attack']) 
{
    error(NO_PERM2);
}
if ($mytribe -> fields['atak'] == 'Y') 
{
    error(ONLY_ONE);
}
$klan1 = $db -> Execute("SELECT `id`, `name` FROM `tribes` WHERE `id`!=".$mytribe -> fields['id']." AND `level`=5");
$arrlink = array();
while (!$klan1 -> EOF) 
{
    $arrlink[] = "<a href=\"tribeadmin.php?step2=walka&amp;atak=".$klan1 -> fields['id']."\">".A_ATTACK.$klan1 -> fields['name']."<br /></a>";
    $klan1 -> MoveNext();
}
$klan1 -> Close();
$smarty -> assign(array("Link" => $arrlink, 
                        "Attack" => '',
                        "Selecttribe" => SELECT_TRIBE));
/**
 * Confirmation of attack
 */
if (!isset($_GET['step3']) && isset($_GET['atak']))
{
    checkvalue($_GET['atak']);
    $objEntribe = $db -> Execute("SELECT `id`, `name`, `level` FROM `tribes` WHERE `id`=".$_GET['atak']);
    if (!$objEntribe -> fields['id'])
    {
        error(ERROR);
    }
    if ($objEntribe->fields['level'] != 5)
      {
	error('Można atakować tylko klany typu Zamek.');
      }
    $smarty -> assign(array("Youwant" => YOU_WANT,
                            "Entribe" => $objEntribe -> fields['name'],
                            "Ayes" => YES,
                            "Attack" => $_GET['atak']));
    $objEntribe -> Close();
}
if (isset($_GET['atak']) && (isset($_GET['step3']) && $_GET['step3'] == 'confirm')) 
{
    checkvalue($_GET['atak']);
    $objTest = $db -> Execute("SELECT `id`, `level` FROM `tribes` WHERE `id`=".$_GET['atak']);
    if (!$objTest -> fields['id'])
    {
        error(ERROR);
    }
    if ($objTest->fields['level'] != 5)
      {
	error('Można atakować tylko klany typu Zamek.');
      }
    $objTest -> Close();
    $matak = 0;
    $eobrona = 0;
    $db -> Execute("UPDATE `tribes` SET `atak`='Y' WHERE `id`=".$mytribe -> fields['id']);
    $objMembers = $db -> Execute("SELECT `id` FROM `players` WHERE `tribe`=".$mytribe -> fields['id']);
    while (!$objMembers -> EOF) 
      {
	$objMember = new Player($objMembers->fields['id']);
	if ($objMember->clas != 'Mag')
	  {
	    $matak += $objMember->stats['strength'][2];
	  }
	else
	  {
	    $matak += $objMember->stats['inteli'][2];
	  }
        $objMembers -> MoveNext();
      }
    $objMembers -> Close();
    $matak += $mytribe -> fields['zolnierze'];
    $objMembers = $db -> Execute("SELECT `id` FROM `players` WHERE `tribe`=".$_GET['atak']);
    while (!$objMembers -> EOF) 
      {
	$objMember = new Player($objMembers->fields['id']);
        $eobrona += $objMember->stats['agility'][2] + $objMember->stats['wisdom'][2];
        $objMembers -> MoveNext();
    }
    $objMembers -> Close();
    $klan = $db -> Execute("SELECT `id`, `name`, `owner`, `forty`, `credits`, `platinum` FROM `tribes` WHERE `id`=".$_GET['atak']);
    $eobrona += $klan -> fields['forty'];
    $matak += rand(1, 1000);
    $eobrona += rand(1, 1000);
    $smarty -> assign("Victory", '');
    
    /**
     * If attacker win
     */
    if ($matak >= $eobrona) 
    {
        if ($klan -> fields['credits'] > 0) 
        {
            $gzloto = ceil($klan -> fields['credits'] / 10);
        } 
            else 
        {
            $gzloto = 0;
        }
        if ($klan -> fields['platinum'] > 0) 
        {
            $gmith = ceil($klan -> fields['platinum'] / 10);
        } 
            else 
        {
            $gmith = 0;
        }

        /**
         * Get astral components
         */
        $intRoll2 = rand(1, 100);
        $strComponent = '';
        $strComponent2 = '';
        if ($intRoll2 < 6)
        {
            $objAmount = $db -> Execute("SELECT count(*) FROM `astral` WHERE `owner`=".$klan -> fields['id']." AND `location`='C'");
            if ($objAmount -> fields['count(*)'])
            {
                $intRoll3 = rand(1, $objAmount -> fields['count(*)']) - 1;
                $objAstral = $db -> SelectLimit("SELECT `type`, `number`, `amount` FROM `astral` WHERE `owner`=".$klan -> fields['id']." AND `location`='C'", 1, $intRoll3);
                $arrCompnames = array(array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7),
                                      array(PLAN1, PLAN2, PLAN3, PLAN4, PLAN5),
                                      array(RECIPE1, RECIPE2, RECIPE3, RECIPE4, RECIPE5),
                                      array(COMP1, COMP2, COMP3, COMP4, COMP5, COMP6, COMP7), 
                                      array(CONST1, CONST2, CONST3, CONST4, CONST5), 
                                      array(POTION1, POTION2, POTION3, POTION4, POTION5));
                $arrNames = array('M', 'P', 'R', 'C', 'O', 'T');
                $strName = ereg_replace("[0-9]", "", $objAstral -> fields['type']);
                $intKey = array_search($strName, $arrNames);
                $intKey2 = (int)ereg_replace($arrNames[$intKey], "", $objAstral -> fields['type']);
                $strCompname = $arrCompnames[$intKey][$intKey2];
                if ($intKey < 3)
                {
                    $strType = PIECE;
                }
                else
                {
                    $strType = COMPONENT;
                }
                $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$mytribe -> fields['id']." AND `type`='".$objAstral -> fields['type']."' AND `number`=".$objAstral -> fields['number']." AND `location`='C'") or die("Błąd!");
                if (!$objTest -> fields['amount'])
                {
                    $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$mytribe -> fields['id'].", '".$objAstral -> fields['type']."', ".$objAstral -> fields['number'].", 1, 'C')");
                }
                    else
                {
                    $db -> Execute("UPDATE `astral` SET `amount`=`amount`+1 WHERE `owner`=".$mytribe -> fields['id']." AND `type`='".$objAstral -> fields['type']."' AND `number`=".$objAstral -> fields['number']." AND `location`='C'");
                }
                $objTest -> Close();
                if ($objAstral -> fields['amount'] == 1)
                {
                    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$klan -> fields['id']." AND `type`='".$objAstral -> fields['type']."' AND `number`=".$objAstral -> fields['number']." AND `location`='C'");
                }
                    else
                {
                    $db -> Execute("UPDATE `astral` SET `amount`=`amount`-1 WHERE `owner`=".$klan -> fields['id']." AND `type`='".$objAstral -> fields['type']."' AND `number`=".$objAstral -> fields['number']." AND `location`='C'");
                }
                $objAstral -> Close();
                $strComponent = WIN_COMPONENT.$strType.$strCompname."</b>.";
                $strComponent2 = WIN_COMPONENT2.$strType.$strCompname."</b>.";
                require_once('includes/checkastral.php');
                checkastral($mytribe -> fields['id']);
                checkastral($klan -> fields['id']);
            }
            $objAmount -> Close();
        }

        $smarty -> assign(array("Myname" => $mytribe -> fields['name'], 
                                "Ename" => $klan -> fields['name'], 
                                "Gold" => $gzloto, 
                                "Mithril" => $gmith, 
                                "Victory" => "My",
                                "Youwin" => YOU_WIN,
                                "Youwin2" => YOU_WIN2,
                                "Youwin3" => YOU_WIN3,
                                "Goldcoins" => GOLD_COINS,
                                "Mithrilcoins" => MITHRIL_COINS,
                                "Astral" => $strComponent));
        $db -> Execute("UPDATE `tribes` SET `credits`=`credits`+".$gzloto.", `platinum`=`platinum`+".$gmith.", `wygr`=`wygr`+1 WHERE `id`=".$mytribe -> fields['id']);
        $db -> Execute("UPDATE `tribes` SET `credits`=`credits`-".$gzloto.", `platinum`=`platinum`-".$gmith.", `przeg`=`przeg`+1 WHERE `id`=".$klan -> fields['id']);
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$klan -> fields['owner'].", '".L_TRIBE.'<b><a href="tribes.php?view=view&amp;id='.$mytribe -> fields['id'].'">'.$mytribe -> fields['name'].'</a></b>'.ATTACK_YOU.$gzloto.GOLD_COINS.$gmith.MITHRIL_COINS.$strComponent2."', ".$strDate.", 'C')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$klan -> fields['id']." AND `attack`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".L_TRIBE.'<a href="tribes.php?view=view&amp;id='.$mytribe -> fields['id'].'">'.$mytribe -> fields['name'].'</a>'.ATTACK_YOU.$gzloto.GOLD_COINS.$gmith.MITHRIL_COINS.$strComponent2."', ".$strDate.", 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
    }

    /**
     * If defender win
     */
        else 
    {
        if ($mytribe -> fields['credits'] > 0) 
        {
            $gzloto = ceil($mytribe -> fields['credits'] / 10);
        } 
            else 
        {
            $gzloto = 0;
        }
        if ($mytribe -> fields['platinum'] > 0) 
        {
            $gmith = ceil($mytribe -> fields['platinum'] / 10);
        } 
            else 
        {
            $gmith = 0;
        }

        /**
         * Get astral components
         */
        $intRoll2 = rand(1, 100);
        $strComponent = '';
        $strComponent2 = '';
        if ($intRoll2 < 6)
        {
            $objAmount = $db -> Execute("SELECT count(*) FROM `astral` WHERE `owner`=".$mytribe -> fields['id']." AND `location`='C'");
            if ($objAmount -> fields['count(*)'])
            {
                $intRoll3 = rand(1, $objAmount -> fields['count(*)']) - 1;
                $objAstral = $db -> SelectLimit("SELECT `type`, `number`, `amount` FROM `astral` WHERE `owner`=".$mytribe -> fields['id']." AND `location`='C'", 1, $intRoll3);
                $arrCompnames = array(array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7),
                                      array(PLAN1, PLAN2, PLAN3, PLAN4, PLAN5),
                                      array(RECIPE1, RECIPE2, RECIPE3, RECIPE4, RECIPE5),
                                      array(COMP1, COMP2, COMP3, COMP4, COMP5, COMP6, COMP7), 
                                      array(CONST1, CONST2, CONST3, CONST4, CONST5), 
                                      array(POTION1, POTION2, POTION3, POTION4, POTION5));
                $arrNames = array('M', 'P', 'R', 'C', 'O', 'T');
                $strName = ereg_replace("[0-9]", "", $objAstral -> fields['type']);
                $intKey = array_search($strName, $arrNames);
                $intKey2 = (int)ereg_replace($arrNames[$intKey], "", $objAstral -> fields['type']);
                $strCompname = $arrCompnames[$intKey][$intKey2];
                if ($intKey < 3)
                {
                    $strType = PIECE;
                }
                else
                {
                    $strType = COMPONENT;
                }
                $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$klan -> fields['id']." AND `type`='".$objAstral -> fields['type']."' AND `number`=".$objAstral -> fields['number']." AND `location`='C'");
                if (!$objTest -> fields['amount'])
                {
                    $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$klan -> fields['id'].", '".$objAstral -> fields['type']."', ".$objAstral -> fields['number'].", 1, 'C')");
                }
                    else
                {
                    $db -> Execute("UPDATE `astral` SET `amount`=`amount`+1 WHERE `owner`=".$klan -> fields['id']." AND `type`='".$objAstral -> fields['type']."' AND `number`=".$objAstral -> fields['number']." AND `location`='C'");
                }
                $objTest -> Close();
                if ($objAstral -> fields['amount'] == 1)
                {
                    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$mytribe -> fields['id']." AND `type`='".$objAstral -> fields['type']."' AND `number`=".$objAstral -> fields['number']." AND `location`='C'");
                }
                    else
                {
                    $db -> Execute("UPDATE `astral` SET `amount`=`amount`-1 WHERE `owner`=".$mytribe -> fields['id']." AND `type`='".$objAstral -> fields['type']."' AND `number`=".$objAstral -> fields['number']." AND `location`='C'");
                }
                $objAstral -> Close();
                $strComponent2 = WIN_COMPONENT2.$strType.$strCompname."</b>.";
                $strComponent = WIN_COMPONENT.$strType.$strCompname."</b>.";
                require_once('includes/checkastral.php');
                checkastral($mytribe -> fields['id']);
                checkastral($klan -> fields['id']);
            }
            $objAmount -> Close();
        }

        $smarty -> assign(array("Ename" => $klan -> fields['name'], 
                                "Gold" => $gzloto, 
                                "Mithril" => $gmith, 
                                "Victory" => "Enemy",
                                "Ewin" => YOU_WIN,
                                "Ewin2" => ENEMY_WIN2,
                                "Ewin3" => ENEMY_WIN3,
                                "Goldcoins" => GOLD_COINS,
                                "Mithrilcoins" => MITHRIL_COINS,
                                "Astral" => $strComponent2));
        $strDate = $db -> DBDate($newdate);
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$klan -> fields['owner'].", '".L_TRIBE.'<b><a href="tribes.php?view=view&amp;id='.$mytribe -> fields['id'].'">'.$mytribe -> fields['name'].'</a></b>'.ATTACK_YOU2.$gzloto.GOLD_COINS.$gmith.MITHRIL_COINS.$strComponent."', ".$strDate.", 'C')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$klan -> fields['id']." AND `attack`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".L_TRIBE.'<b><a href="tribes.php?view=view&amp;id='.$mytribe -> fields['id'].'">'.$mytribe -> fields['name'].'</a></b>'.ATTACK_YOU2.$gzloto.GOLD_COINS.$gmith.MITHRIL_COINS.$strComponent."', ".$strDate.", 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $db -> Execute("UPDATE `tribes` SET `credits`=`credits`-".$gzloto.", `platinum`=`platinum`-".$gmith.", `przeg`=`przeg`+1 WHERE `id`=".$mytribe -> fields['id']);
        $db -> Execute("UPDATE `tribes` SET `credits`=`credits`+".$gzloto.", `platinum`=`platinum`+".$gmith.", `wygr`=`wygr`+1 WHERE `id`=".$klan -> fields['id']);
    }
}
?>
