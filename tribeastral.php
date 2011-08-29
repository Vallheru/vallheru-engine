<?php
/**
 *   File functions:
 *   Tribe astral vault - plans, maps, recipes
 *
 *   @name                 : tribeastral.php                            
 *   @copyright            : (C) 2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 29.08.2011
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
require_once("languages/".$player -> lang."/tribeastral.php");

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
$objOwner = $db -> Execute("SELECT `owner` FROM `tribes` WHERE id=".$player -> tribe);

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
                            "Message" => ''));
    /**
     * Add component to clan
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'piece' || $_GET['step'] == 'component'))
    {
        integercheck($_POST['amount']);
        if (!ereg("^[0-9]*$", $_POST['name']))
        {
            error(ERROR);
        }
	checkvalue($_POST['amount']);
	checkvalue($_POST['number']);
        $intCompname = $_POST['name'];
        if ($_GET['step'] == 'piece')
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
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$objOwner -> fields['owner'].", '".T_PLAYER1.'<b><a href="view.php?view="'.$player -> id.'>'.$player -> user.'</a></b>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`) VALUES(".$objPerm -> fields['player'].", '".T_PLAYER1.'<a href="view.php?view="'.$player -> id.'>'.$player -> user.'</a>'.T_PLAYER2.'<b>'.$player -> id."</b> ".$strMessage."','".$newdate."')");
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
    $smarty -> assign(array("Addto" => ADD_TO,
                            "Piecenumber" => PIECE_NUMBER,
                            "Astralamount" => ASTRAL_AMOUNT,
                            "Agive" => A_GIVE,
                            "Inames" => $arrNames,
                            "Inames2" => $arrNames2,
                            "Addto2" => ADD_TO2,
                            "Addto3" => ADD_TO3,
                            "Message" => ''));
    /**
     * Give piece of component to player
     */
    if (isset($_GET['step']) && ($_GET['step'] == 'piece' || $_GET['step'] == 'component'))
    {
        integercheck($_POST['amount']);
        if (!ereg("^[0-9]*$", $_POST['name']) || !ereg("^[0-9]*$", $_POST['pid']))
        {
            error(ERROR);
        }
	checkvalue($_POST['amount']);
	checkvalue($_POST['number']);
        $objDonated = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_POST['pid']);
        if (empty ($objDonated -> fields['id'])) 
        {
            error(NO_PLAYER);
        }
        $objDonated -> Close();
        $intCompname = $_POST['name'];
        if ($_GET['step'] == 'piece')
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
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$objOwner -> fields['owner'].", '".$strMessage."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`) VALUES(".$objPerm -> fields['player'].", '".$strMessage."','".$newdate."')");
            $objPerm -> MoveNext();
        }
        $objPerm -> Close();
        $smarty -> assign("Message", $strMessage);
        require_once('includes/checkastral.php');
        checkastral($player -> tribe);
    }
    /**
     * Give component to player
     */
    if (isset($_GET['step']) && $_GET['step'] == 'all')
    {
        integercheck($_POST['amount']);
        if (!ereg("^[0-9]*$", $_POST['name']) || !ereg("^[0-9]*$", $_POST['pid']))
        {
            error(ERROR);
        }
	checkvalue($_POST['amount']);
        $objDonated = $db -> Execute("SELECT `id` FROM `players` WHERE `id`=".$_POST['pid']);
        if (empty ($objDonated -> fields['id'])) 
        {
            error(NO_PLAYER);
        }
        $objDonated -> Close();
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
        $intCompname = $_POST['name'];
        $strType = COMPONENT;
        $strCompname = $arrNames[$intCompname];
        $arrNumber = array(1, 2, 3, 4, 5, 6, 7, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5);
        $strPiecename = $strName.$arrNumber[$_POST['name']];
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
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$_POST['pid'].", '".$strMessage2."','".$newdate."')");
        $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`) VALUES(".$objOwner -> fields['owner'].", '".$strMessage."','".$newdate."')");
        $objPerm = $db -> Execute("SELECT `player` FROM `tribe_perm` WHERE `tribe`=".$player -> tribe." AND `astralvault`=1");
        while (!$objPerm -> EOF)
        {
            $db -> Execute("INSERT INTO log (`owner`, `log`, `czas`) VALUES(".$objPerm -> fields['player'].", '".$strMessage."','".$newdate."')");
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
        $objMinerals = $db -> Execute("SELECT `crystal`, `adamantium`, `meteor` FROM `tribes` WHERE `id`=".$player -> tribe);
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
$smarty -> assign(array("Action" =>$_GET['action'], 
                        "Amain" => A_MAIN,
                        "Adonate" => A_DONATE,
                        "Amembers" => A_MEMBERS,
                        "Apotions" => A_POTIONS,
                        "Aminerals" => A_MINERALS,
                        "Aherbs" => A_HERBS,
                        "Aleft" => A_LEFT,
                        "Aleader" => A_LEADER,
                        "Aforums" => A_FORUMS,
                        "Aarmor" => A_ARMOR,
                        "Aastral" => A_ASTRAL));
$smarty -> display('tribeastral.tpl');

require_once("includes/foot.php");
?>
