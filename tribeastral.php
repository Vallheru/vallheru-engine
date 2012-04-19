<?php
/**
 *   File functions:
 *   Tribe astral vault - plans, maps, recipes
 *
 *   @name                 : tribeastral.php                            
 *   @copyright            : (C) 2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.5
 *   @since                : 19.04.2012
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

$title = "Astralny skarbiec";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/tribeastral.php");

/**
* Check if player is in clan
*/
if (!$player -> tribe) 
{
    error (NO_CLAN);
}

/**
* Check if player is in city
*/
if ($player -> location != 'Altara' && $player -> location != 'Ardulith')  
{
    error (ERROR);
}

/**
* Assign variable to template
*/
$smarty -> assign("Message",'');

/**
* Check who have permission to give potions
*/
$objPerm = $db -> Execute("SELECT `astralvault` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `player`=".$player -> id);
$objOwner = $db -> Execute("SELECT `owner`, `level` FROM `tribes` WHERE id=".$player -> tribe);
if ($objOwner->fields['level'] < 3)
  {
    error('Najpierw musisz rozbudować klan, aby mieć dostęp do tego miejsca. (<a href="tribes.php?view=my">Wróć</a>)');
  }

/**
* Main menu
*/
if (!isset($_GET['action'])) 
{
    if ($player -> id == $objOwner -> fields['owner'] || $objPerm -> fields['astralvault']) 
    {
        $strAgive = A_GIVE;

        /**
         * Buy safe box
         */
        $objSafebox = $db -> Execute("SELECT `level` FROM `astral_bank` WHERE `owner`=".$player -> tribe." AND `location`='C'");
        if ($objSafebox -> fields['level'] != 3)
        {
            if (!$objSafebox -> fields['level'])
            {
                $objSafebox -> fields['level'] = 0;
            }
            $arrSafeneed = array(array(200000, 200, 100, 0, 0),
                                 array(400000, 400, 200, 100, 0),
                                 array(800000, 800, 400, 200, 100));
            $intKey = $objSafebox -> fields['level'];
            $intUpgrade = $intKey + 1;
            $strSafebox = "<a href=\"tribeastral.php?action=safe\">".BUY_SAFE.$intUpgrade.BUY_SAFE2.$arrSafeneed[$intKey][0].ASTRAL_GOLD.", ".$arrSafeneed[$intKey][1].ASTRAL_MITH.", ".$arrSafeneed[$intKey][2]." ".ADAMANTIUM.", ".$arrSafeneed[$intKey][3]." ".CRYSTAL.", ".$arrSafeneed[$intKey][4]." ".METEOR.".</a>";
        }
            else
        {
            $strSafebox = '';
        }
        $objSafebox -> Close();
    }
        else
    {
        $strAgive = '';
        $strSafebox = '';
    }
    $smarty -> assign(array("Wareinfo" => WARE_INFO,
                            "Aadd" => A_ADD,
                            "Ashow" => A_SHOW,
                            "Ashow2" => A_SHOW2,
                            "Safebox" => $strSafebox,
                            "Agive" => $strAgive));

    $_GET['action'] = '';
}

$arrMaps = array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7);
$arrMaps2 = array(MAP1, MAP2, MAP3, MAP4, MAP5, MAP6, MAP7);
$arrPlans = array(PLAN1, PLAN2, PLAN3, PLAN4, PLAN5);
$arrPlans2 = array(PLAN1, PLAN2, PLAN3, PLAN4, PLAN5);
$arrRecipes = array(RECIPE1, RECIPE2, RECIPE3, RECIPE4, RECIPE5);
$arrRecipes2 = array(RECIPE1, RECIPE2, RECIPE3, RECIPE4, RECIPE5);
$arrCompnames = array(array(COMP1, COMP2, COMP3, COMP4, COMP5, COMP6, COMP7), 
                      array(CONST1, CONST2, CONST3, CONST4, CONST5), 
                      array(POTION1, POTION2, POTION3, POTION4, POTION5));

/**
 * View astral components
 */
if (isset($_GET['action']) && $_GET['action'] == 'view')
{
    if (!isset($_GET['type']))
    {
        error(ERROR);
    }

    $smarty -> assign("Type", $_GET['type']);

    /**
     * List of maps, plans, recipes
     */
    if (isset($_GET['type']) && $_GET['type'] == 'p')
    {
        require_once('includes/astralvault.php');

        $strMessage = '';

        /**
         * Merge plans, maps, recipes
         */
        if (isset($_GET['step']))
        {
            if ($player -> id == $objOwner -> fields['owner'] || $objPerm -> fields['astralvault']) 
            {
                showastral('C', '', $player -> tribe);
                mergeplans('C', $player -> tribe);
                $strMessage = YOU_MERGE;
            }
        }

        showastral('C', 'tribeastral.php?action=view&amp;type=p', $player -> tribe);
    
        $smarty -> assign(array("Tname" => T_NAME,
                                "Tmaps" => T_MAPS,
                                "Tplans" => T_PLANS,
                                "Trecipes" => T_RECIPES,
                                "Tmaps2" => T_MAPS2,
                                "Tplans2" => T_PLANS2,
                                "Trecipes2" => T_RECIPES2,
                                "Mapsname" => $arrMaps,
                                "Plansname" => $arrPlans,
                                "Recipesname" => $arrRecipes,
                                "Mapsname2" => $arrMaps2,
                                "Plansname2" => $arrPlans2,
                                "Recipesname2" => $arrRecipes2,
                                "Mapsamount" => $arrMapsamount,
                                "Plansamount" => $arrPlansamount,
                                "Recipesamount" => $arrRecipesamount,
                                "Mapsamount2" => $arrCmapsamount,
                                "Plansamount2" => $arrCplansamount,
                                "Recipesamount2" => $arrCrecipesamount,
                                "Message" => $strMessage));
    }

    /**
     * List of components, constructions, potions
     */
    if (isset($_GET['type']) && $_GET['type'] == 'c')
    {
        $arrCompnames2 = array(MAGICCOMP, MAGICCONST, MAGICPOTIONS);
        
        require_once('includes/astralvault.php');

        $arrComponents = showcomponents('C', $player -> tribe);
    
        $smarty -> assign(array("Tname" => T_NAME,
                                "Tmagic" => $arrCompnames2,
                                "Tcomp" => $arrCompnames,
                                "Components" => $arrComponents));
    }
}

/**
 * Give astral components to clan
 */
if (isset($_GET['action']) && $_GET['action'] == 'add')
{
    $arrNames = array_merge($arrMaps, $arrPlans, $arrRecipes);
    $arrNames2 = array_merge($arrCompnames[0], $arrCompnames[1], $arrCompnames[2]);
    $smarty -> assign(array("Addto" => ADD_TO,
                            "Piecenumber" => PIECE_NUMBER,
                            "Astralamount" => ASTRAL_AMOUNT,
                            "Aadd" => A_ADD,
                            "Addto2" => ADD_TO2,
                            "Inames" => $arrNames,
                            "Inames2" => $arrNames2,
			    "Asend" => "Wyślij",
			    "Tall" => "Wyślij wszystkie",
			    "Taplan" => "wszystkie kawałki",
                            "Message" => ''));
    /**
     * Add component to clan
     */
    if (isset($_GET['step']) && (in_array($_GET['step'], array('piece', 'component', 'plan'))))
      {
	$_POST['name'] = intval($_POST['name']);
        if ($_POST['name'] < 0)
	  {
            error(ERROR);
	  }
        $intCompname = $_POST['name'];
	if ($_GET['step'] != 'component')
	  {
            if ($_POST['name'] < 7)
	      {
                $strName = 'M';
	      }
            if ($_POST['name'] > 6 && $_POST['name'] < 12)
	      {
                $strName = 'P';
	      }
            if ($_POST['name'] > 11)
	      {
                $strName = 'R';
	      }
	    $strType = PIECE;
	    $strCompname = $arrNames[$intCompname];
	  }
	else
	  {
	    if ($_POST['name'] < 7)
            {
                $strName = 'C';
            }
            if ($_POST['name'] > 6 && $_POST['name'] < 12)
            {
                $strName = 'O';
            }
            if ($_POST['name'] > 11)
            {
                $strName = 'T';
            }
            $strType = COMPONENT;
            $strCompname = $arrNames2[$intCompname];
	  }
	$arrNumber = array(0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 0, 1, 2, 3, 4);
	$strPiecename = $strName.$arrNumber[$_POST['name']];
      }

    /**
     * Give all selected plan parts to tribe
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'plan'))
      {
	$objAmount = $db -> Execute("SELECT `amount`, `number` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `location`='V'") or die($db -> ErrorMsg());
        if (!$objAmount -> fields['amount'])
	  {
            error(NO_AMOUNT);
	  }
	while(!$objAmount->EOF)
	  {
	    $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player->tribe." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='C'");
	    if (!$objTest -> fields['amount'])
	      {
		$db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$player->tribe.", '".$strPiecename."', ".$objAmount->fields['number'].", ".$objAmount->fields['amount'].", 'C')");
	      }
            else
	      {
		$db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$objAmount->fields['amount']." WHERE `owner`=".$player->tribe." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='C'");
	      }
	    $objTest -> Close();
	    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	    $objAmount->MoveNext();
	  }
	$objAmount->Close();

	$strMessage = HE_ADD."</b> wszystkie posiadane części ".$strCompname.".";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objOwner -> fields['owner'].", '".T_PLAYER1.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user.'</a></b>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."', 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$objOwner -> fields['owner'].", '".T_PLAYER1.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user.'</a></b>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".T_PLAYER1.'<a href="view.php?view='.$player -> id.'">'.$player -> user.'</a>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."', 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $strMessage2 = YOU_ADD."</b> wszystkie posiadane częsci ".$strCompname.".";
        $smarty -> assign("Message", $strMessage2);
        require_once('includes/checkastral.php');
        checkastral($player -> tribe);
      }

    /**
     * Give everything to tribe
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'all'))
      {
	$objAmount = $db -> Execute("SELECT `amount`, `number`, `type` FROM `astral` WHERE `owner`=".$player -> id." AND (`type` LIKE 'M%' OR `type` LIKE 'P%' OR `type` LIKE 'R%') AND `location`='V'") or die($db -> ErrorMsg());
	while (!$objAmount->EOF)
	  {
	    $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player->tribe." AND `type`='".$objAmount->fields['type']."' AND `number`=".$objAmount->fields['number']." AND `location`='C'");
	    if (!$objTest -> fields['amount'])
	      {
		$db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$player->tribe.", '".$objAmount->fields['type']."', ".$objAmount->fields['number'].", ".$objAmount->fields['amount'].", 'C')");
	      }
            else
	      {
		$db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$objAmount->fields['amount']." WHERE `owner`=".$player->tribe." AND `type`='".$objAmount->fields['type']."' AND `number`=".$objAmount->fields['number']." AND `location`='C'");
	      }
	    $objTest -> Close();
	    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$objAmount->fields['type']."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	    $objAmount->MoveNext();
	  }
	$objAmount->Close();

	$strMessage = HE_ADD."</b> wszystkie posiadane części astralne.";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objOwner -> fields['owner'].", '".T_PLAYER1.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user.'</a></b>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."', 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$objOwner -> fields['owner'].", '".T_PLAYER1.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user.'</a></b>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `tribe`) VALUES(".$objPerm -> fields['player'].", '".T_PLAYER1.'<a href="view.php?view='.$player -> id.'">'.$player -> user.'</a>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."', 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $strMessage2 = YOU_ADD." wszystkie posiadane części astralne.";
        $smarty -> assign("Message", $strMessage2);
        require_once('includes/checkastral.php');
        checkastral($player -> tribe);
      }
    
    /**
     * Give component to clan
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'piece' || $_GET['step'] == 'component'))
    {
        integercheck($_POST['amount']);
	checkvalue($_POST['amount']);
	checkvalue($_POST['number']);
        $intNumber = $_POST['number'] - 1;
        $objAmount = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'") or die($db -> ErrorMsg());
        if (!$objAmount -> fields['amount'])
        {
            error(NO_AMOUNT);
        }
        if ($objAmount -> fields['amount'] < $_POST['amount'])
        {
            error(NO_AMOUNT);
        }
        $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> tribe." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='C'");
        if (!$objTest -> fields['amount'])
        {
            $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$player -> tribe.", '".$strPiecename."', ".$intNumber.", ".$_POST['amount'].", 'C')");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$_POST['amount']." WHERE `owner`=".$player -> tribe." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='C'");
        }
        $objTest -> Close();
        if ($objAmount -> fields['amount'] == $_POST['amount'])
        {
            $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`-".$_POST['amount']." WHERE `owner`=".$player -> id." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
        }
        $objAmount -> Close();

        $strMessage = HE_ADD.$strType.$strCompname.M_AMOUNT.$_POST['amount'].".";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objOwner -> fields['owner'].", '".T_PLAYER1.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user.'</a></b>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."', 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$objOwner -> fields['owner'].", '".T_PLAYER1.'<b><a href="view.php?view='.$player -> id.'">'.$player -> user.'</a></b>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".T_PLAYER1.'<a href="view.php?view='.$player -> id.'">'.$player -> user.'</a>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."', 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $strMessage2 = YOU_ADD.$strType.$strCompname.M_AMOUNT.$_POST['amount'].".";
        $smarty -> assign("Message", $strMessage2);
        require_once('includes/checkastral.php');
        checkastral($player -> tribe);
    }
}

/**
 * Give astral components to player
 */
if (isset($_GET['action']) && $_GET['action'] == 'give')
{
    if ($player -> id != $objOwner -> fields['owner'] && !$objPerm -> fields['astralvault']) 
    {
        error(NO_PERM);
    }
    $arrNames = array_merge($arrMaps, $arrPlans, $arrRecipes);
    $arrNames2 = array_merge($arrCompnames[0], $arrCompnames[1], $arrCompnames[2]);
    $objMembers = $db->Execute("SELECT `id`, `user` FROM `players` WHERE `tribe`=".$player->tribe);
    $arrMembers = array();
    while (!$objMembers->EOF)
      {
	$arrMembers[$objMembers->fields['id']] = $objMembers->fields['user'].' ID:'.$objMembers->fields['id'];
	$objMembers->MoveNext();
      }
    $objMembers->Close();
    $smarty -> assign(array("Addto" => ADD_TO,
                            "Piecenumber" => PIECE_NUMBER,
                            "Astralamount" => ASTRAL_AMOUNT,
                            "Agive" => A_GIVE,
                            "Inames" => $arrNames,
                            "Inames2" => $arrNames2,
                            "Addto2" => ADD_TO2,
                            "Addto3" => ADD_TO3,
			    "Taplan" => "wszystkie kawałki (mapy, planu, przepisu) graczowi:",
			    "Members" => $arrMembers,
                            "Message" => ''));

    if (isset($_GET['step']) && in_array($_GET['step'], array('piece', 'component', 'all', 'plan')))
      {
	$_POST['name'] = intval($_POST['name']);
	if ($_POST['name'] < 0)
	  {
	    error(ERROR);
	  }
	if ($_GET['step'] != 'plan')
	  {
	    checkvalue($_POST['amount']);
	  }
	checkvalue($_POST['pid']);
	$objDonated = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_POST['pid']);
        if (empty ($objDonated -> fields['id'])) 
        {
            error(NO_PLAYER);
        }
	$intCompname = $_POST['name'];
	if ($_GET['step'] != 'component')
	  {
	    if ($_POST['name'] < 7)
            {
                $strName = 'M';
            }
            if ($_POST['name'] > 6 && $_POST['name'] < 12)
            {
                $strName = 'P';
            }
            if ($_POST['name'] > 11)
            {
                $strName = 'R';
            }
            $strType = PIECE;
            $strCompname = $arrNames[$intCompname];
	  }
	else
	  {
	    if ($_POST['name'] < 7)
            {
                $strName = 'C';
            }
            if ($_POST['name'] > 6 && $_POST['name'] < 12)
            {
                $strName = 'O';
            }
            if ($_POST['name'] > 11)
            {
                $strName = 'T';
            }
            $strType = COMPONENT;
            $strCompname = $arrNames2[$intCompname];
	  }
	$arrNumber = array(0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 0, 1, 2, 3, 4);
	if ($_GET['step'] == 'all')
	  {
	    $intNumber = $arrNumber[$_POST['name']] + 1;
	  }
	else
	  {
	    $intNumber = $arrNumber[$_POST['name']];
	  }
        $strPiecename = $strName.$intNumber;
      }
    
    /**
     * Give piece of component to player
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'piece' || $_GET['step'] == 'component'))
    {
	checkvalue($_POST['number']);
	$intNumber = $_POST['number'] - 1;
        $objAmount = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player -> tribe." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='C'") or die($db -> ErrorMsg());
        if (!$objAmount -> fields['amount'])
        {
            error(NO_AMOUNT);
        }
        if ($objAmount -> fields['amount'] < $_POST['amount'])
        {
            error(NO_AMOUNT);
        }
        $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$_POST['pid']." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
        if (!$objTest -> fields['amount'])
        {
            $db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$_POST['pid'].", '".$strPiecename."', ".$intNumber.", ".$_POST['amount'].", 'V')");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$_POST['amount']." WHERE `owner`=".$_POST['pid']." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='V'");
        }
        $objTest -> Close();
        if ($objAmount -> fields['amount'] == $_POST['amount'])
        {
            $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player -> tribe." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='C'");
        }
            else
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`-".$_POST['amount']." WHERE `owner`=".$player -> tribe." AND `type`='".$strPiecename."' AND `number`=".$intNumber." AND `location`='C'");
        }
        $objAmount -> Close();
        // Get name of the person which receives astral component.
        $objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['pid'].';');
        $strReceiversName = $objGetName -> fields['user'];
        $objGetName -> Close();
        unset( $objGetName );
        // Get name of the person which receives astral component.
        
        $strMessage = YOU_GIVE.$strType.$strCompname.M_AMOUNT.$_POST['amount'].TO_PLAYER1.'<b><a href="view.php?view='.$_POST['pid'].'">'.$strReceiversName.'</a></b>'.TO_PLAYER2.'<b>'.$_POST['pid']."</b>.";
        $strMessage2 = YOU_GET.$strType.$strCompname.M_AMOUNT.$_POST['amount'].".";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'C')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objOwner -> fields['owner'].", '".$strMessage."','".$newdate."', 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".$strMessage."','".$newdate."', 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $smarty -> assign("Message", $strMessage);
        require_once('includes/checkastral.php');
        checkastral($player -> tribe);
    }

    /**
     * Give all selected plan parts to player
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'plan'))
      {
	$objAmount = $db -> Execute("SELECT `amount`, `number` FROM `astral` WHERE `owner`=".$player->tribe." AND `type`='".$strPiecename."' AND `location`='C'") or die($db -> ErrorMsg());
        if (!$objAmount -> fields['amount'])
	  {
            error(NO_AMOUNT);
	  }
	while(!$objAmount->EOF)
	  {
	    $objTest = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$player->id." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	    if (!$objTest -> fields['amount'])
	      {
		$db -> Execute("INSERT INTO `astral` (`owner`, `type`, `number`, `amount`, `location`) VALUES(".$_POST['pid'].", '".$strPiecename."', ".$objAmount->fields['number'].", ".$objAmount->fields['amount'].", 'V')");
	      }
            else
	      {
		$db -> Execute("UPDATE `astral` SET `amount`=`amount`+".$objAmount->fields['amount']." WHERE `owner`=".$_POST['pid']." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='V'");
	      }
	    $objTest -> Close();
	    $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$player->tribe." AND `type`='".$strPiecename."' AND `number`=".$objAmount->fields['number']." AND `location`='C'");
	    $objAmount->MoveNext();
	  }
	$objAmount->Close();

	// Get name of the person which receives astral component.
        $objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['pid'].';');
        $strReceiversName = $objGetName -> fields['user'];
        $objGetName -> Close();
	$strMessage = YOU_GIVE." wszystkie posiadane częsci mapy/planu ".$strCompname.TO_PLAYER1.'<b><a href="view.php?view='.$_POST['pid'].'">'.$strReceiversName.'</a></b>'.TO_PLAYER2.'<b>'.$_POST['pid']."</b>.";
        $strMessage2 = YOU_GET." wszystkie posiadane części mapy/planu ".$strCompname.".";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'C')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objOwner -> fields['owner'].", '".$strMessage."','".$newdate."', 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".$strMessage."','".$newdate."', 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $smarty -> assign("Message", $strMessage);
        require_once('includes/checkastral.php');
        checkastral($player->tribe);
      }

    /**
     * Give component to player
     */
    if (isset($_GET['step']) && $_GET['step'] == 'all')
    {
        $strType = COMPONENT;
	$objAmount = $db -> Execute("SELECT `amount` FROM `astral_plans` WHERE `owner`=".$player -> tribe." AND `name`='".$strPiecename."' AND `location`='C'") or die($db -> ErrorMsg());
        if (!$objAmount -> fields['amount'])
        {
            error(NO_AMOUNT);
        }
        if ($objAmount -> fields['amount'] < $_POST['amount'])
        {
            error(NO_AMOUNT);
        }
        $objTest = $db -> Execute("SELECT `amount` FROM `astral_plans` WHERE `owner`=".$_POST['pid']." AND `name`='".$strPiecename."' AND `location`='V'");
        if (!$objTest -> fields['amount'])
        {
            $db -> Execute("INSERT INTO `astral_plans` (`owner`, `name`, `amount`, `location`) VALUES(".$_POST['pid'].", '".$strPiecename."', ".$_POST['amount'].", 'V')");
        }
            else
        {
            $db -> Execute("UPDATE `astral_plans` SET `amount`=`amount`+".$_POST['amount']." WHERE `owner`=".$_POST['pid']." AND `name`='".$strPiecename."' AND `location`='V'");
        }
        $objTest -> Close();
        if ($objAmount -> fields['amount'] == $_POST['amount'])
        {
            $db -> Execute("DELETE FROM `astral_plans` WHERE `owner`=".$player -> tribe." AND `name`='".$strPiecename."' AND `location`='C'");
        }
            else
        {
            $db -> Execute("UPDATE `astral_plans` SET `amount`=`amount`-".$_POST['amount']." WHERE `owner`=".$player -> tribe." AND `name`='".$strPiecename."' AND `location`='C'");
        }
        $objAmount -> Close();
        // Get name of the person which receives astral component.
        $objGetName = $db -> Execute("SELECT `user` FROM `players` WHERE `id`=".$_POST['pid'].';');
        $strReceiversName = $objGetName -> fields['user'];
        $objGetName -> Close();
        unset( $objGetName );
        
        $strMessage = YOU_GIVE.$strType.$strCompname.M_AMOUNT.$_POST['amount'].TO_PLAYER1.'<b><a href="view.php?view='.$_POST['pid'].'">'.$strReceiversName.'</a></b>'.TO_PLAYER2.'<b>'.$_POST['pid']."</b>.";
        $strMessage2 = YOU_GET.$strType.$strCompname.M_AMOUNT.$_POST['amount'].".";
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."', 'C')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objOwner -> fields['owner'].", '".$strMessage."','".$newdate."', 'C')");
	$db -> Execute("INSERT INTO `logs` (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`, `type`) VALUES(".$objPerm -> fields['player'].", '".$strMessage."','".$newdate."', 'C')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $smarty -> assign("Message", $strMessage);
    }
}

/**
 * Buy safe box for astral components
 */
if (isset($_GET['action']) && $_GET['action'] == 'safe')
{
    if ($player -> id != $objOwner -> fields['owner'] && !$objPerm -> fields['astralvault']) 
    {
        error(NO_PERM);
    }
    $objSafebox = $db -> Execute("SELECT `level` FROM `astral_bank` WHERE `owner`=".$player -> tribe." AND `location`='C'");
    if ($objSafebox -> fields['level'] != 3)
    {
        if (!$objSafebox -> fields['level'])
        {
            $objSafebox -> fields['level'] = 0;
        }
        $arrSafeneed = array(array(200000, 200, 100, 0, 0),
                                 array(400000, 400, 200, 100, 0),
                                 array(800000, 800, 400, 200, 100));
        $intKey = $objSafebox -> fields['level'];
        $objTribe = $db -> Execute("SELECT `credits`, `platinum` FROM `tribes` WHERE `id`=".$player -> tribe);
        if ($objTribe -> fields['credits'] < $arrSafeneed[$intKey][0])
        {
            error(NO_MONEY);
        }
        if ($objTribe -> fields['platinum'] < $arrSafeneed[$intKey][1])
        {
            error(NO_MITHRIL);
        }
        $objTribe -> Close();
        $objMinerals = $db -> Execute("SELECT `crystal`, `adamantium`, `meteor` FROM `tribe_minerals` WHERE `id`=".$player -> tribe);
        if ($objMinerals -> fields['adamantium'] < $arrSafeneed[$intKey][2])
        {
            error(NO_MINERAL.ADAMANTIUM."!");
        }
        if ($objMinerals -> fields['crystal'] < $arrSafeneed[$intKey][3])
        {
            error(NO_MINERAL.CRYSTAL."!");
        }
        if ($objMinerals -> fields['meteor'] < $arrSafeneed[$intKey][4])
        {
            error(NO_MINERAL.METEOR."!");
        }
        $objMinerals -> Close();
        if (!$objSafebox -> fields['level'])
        {
            $db -> Execute("INSERT INTO `astral_bank` (`owner`, `level`, `location`) VALUES(".$player -> tribe.", 1, 'C')");
        }
            else
        {
            $db -> Execute("UPDATE `astral_bank` SET `level`=`level`+1 WHERE `owner`=".$player -> tribe." AND `location`='C'");
        }
        $db -> Execute("UPDATE `tribes` SET `credits`=`credits`-".$arrSafeneed[$intKey][0].", `platinum`=`platinum`-".$arrSafeneed[$intKey][1].", `adamantium`=`adamantium`-".$arrSafeneed[$intKey][2].", `crystal`=`crystal`-".$arrSafeneed[$intKey][3].", `meteor`=`meteor`-".$arrSafeneed[$intKey][4]." WHERE `id`=".$player -> tribe);
        error(YOU_UPGRADE);
    }
        else
    {
        error(SAFE_ENOUGH);
    }
    $objSafebox -> Close();
}

/**
* Assign variables to template and display page
*/
require_once('includes/tribemenu.php');
$smarty -> assign("Action", $_GET['action']);
$smarty -> display('tribeastral.tpl');

require_once("includes/foot.php");
?>
