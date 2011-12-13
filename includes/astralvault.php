<?php
/**
 *   File functions:
 *   Astral vault function (show, merge components)
 *
 *   @name                 : astralvault.php                            
 *   @copyright            : (C) 2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 13.12.2011
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

/**
 * Function assign maps, plans, recipes to arrays
 */
function showastral($strLocation, $strFile, $intOwner)
{
    global $db;
    global $arrMaps;
    global $arrPlans;
    global $arrRecipes;
    global $arrMapsamount;
    global $arrPlansamount;
    global $arrRecipesamount;
    global $arrCmapsamount;
    global $arrCplansamount;
    global $arrCrecipesamount;

    /**
     * Count maps amount
     */
    $objMaps = $db -> Execute("SELECT `type`, `number`, `amount` FROM `astral` WHERE owner=".$intOwner." AND `type` LIKE 'M%' AND location='".$strLocation."'");
    $arrMapsamount = array(array(0, 0, 0, 0, 0), 
                           array(0, 0, 0, 0, 0), 
                           array(0, 0, 0, 0, 0), 
                           array(0, 0, 0, 0, 0), 
                           array(0, 0, 0, 0, 0), 
                           array(0, 0, 0, 0, 0), 
                           array(0, 0, 0, 0, 0));
    while (!$objMaps -> EOF)
    {
        $intPlannumber = (int)str_replace("M", "", $objMaps -> fields['type']);
        $intMapnumber = $objMaps -> fields['number'];
        $arrMapsamount[$intPlannumber][$intMapnumber] = $objMaps -> fields['amount'];
        $objMaps -> MoveNext();
    }
    $objMaps -> Close();

    /**
     * Check for aviable merging maps
     */
    if ($strFile != '')
    {
        $blnTest = false;
        $strMaplink = "<a href=\"".$strFile."&amp;step=M&amp;number=";
        for ($i = 0; $i < 7; $i++)
        {
            foreach ($arrMapsamount[$i] as $intAmount)
            {
                if ($intAmount)
                {
                    $blnTest = true;
                }
                    else
                {
                    $blnTest = false;
                    break;
                }
            }
            if ($blnTest)
            {
                $arrMaps[$i] = $strMaplink.$i."\">".$arrMaps[$i]."</a>";
                $blnTest = false;
            }
        }
    }

    /**
     * Count plans amount
     */
    $objPlans = $db -> Execute("SELECT `type`, `number`, `amount` FROM `astral` WHERE owner=".$intOwner." AND `type` LIKE 'P%' AND location='".$strLocation."'");
    $arrPlansamount = array(array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 
                            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 
                            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 
                            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 
                            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
    while (!$objPlans -> EOF)
    {
        $intItemnumber = (int)str_replace("P", "", $objPlans -> fields['type']);
        $intPlannumber = $objPlans -> fields['number'];
        $arrPlansamount[$intItemnumber][$intPlannumber] = $objPlans -> fields['amount'];
        $objPlans -> MoveNext();
    }
    $objPlans -> Close();

    /**
     * Check for aviable merging plans
     */
    if ($strFile != '')
    {
        $blnTest = false;
        $strPlanlink = "<a href=\"".$strFile."&amp;step=P&amp;number=";
        for ($i = 0; $i < 5; $i++)
        {
            foreach ($arrPlansamount[$i] as $intAmount)
            {
                if ($intAmount)
                {
                    $blnTest = true;
                }
                    else
                {
                    $blnTest = false;
                    break;
                }
            }
            if ($blnTest)
            {
                $arrPlans[$i] = $strPlanlink.$i."\">".$arrPlans[$i]."</a>";
                $blnTest = false;
            }
        }
    }

    /**
     * Count recipes amount
     */
    $objRecipes = $db -> Execute("SELECT `type`, `number`, `amount` FROM `astral` WHERE owner=".$intOwner." AND `type` LIKE 'R%' AND location='".$strLocation."'");
    $arrRecipesamount = array(array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 
                            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 
                            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 
                            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 
                            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
    while (!$objRecipes -> EOF)
    {
        $intPotionnumber = (int)str_replace("R", "", $objRecipes -> fields['type']);
        $intRecipenumber = $objRecipes -> fields['number'];
        $arrRecipesamount[$intPotionnumber][$intRecipenumber] = $objRecipes -> fields['amount'];
        $objRecipes -> MoveNext();
    }
    $objRecipes -> Close();

    /**
     * Check for aviable merging recipes
     */
    if ($strFile != '')
    {
        $blnTest = false;
        $strRecipelink = "<a href=\"".$strFile."&amp;step=R&amp;number=";
        for ($i = 0; $i < 5; $i++)
        {
            foreach ($arrRecipesamount[$i] as $intAmount)
            {
                if ($intAmount)
                {
                    $blnTest = true;
                }
                    else
                {
                    $blnTest = false;
                    break;
                }
            }
            if ($blnTest)
            {
                $arrRecipes[$i] = $strRecipelink.$i."\">".$arrRecipes[$i]."</a>";
                $blnTest = false;
            }
        }
    }

    /**
     * Count completed maps
     */
    $objCmaps = $db -> Execute("SELECT `amount`, `name` FROM `astral_plans` WHERE `owner`=".$intOwner." AND `name` LIKE 'M%' AND `location`='".$strLocation."'") or die($db -> ErrorMsg());
    $arrCmapsamount = array(0, 0, 0, 0, 0, 0, 0);
    while (!$objCmaps -> EOF)
    {
        $intPlannumber = (int)str_replace("M", "", $objCmaps -> fields['name']);
        $intPlannumber = $intPlannumber - 1;
        $arrCmapsamount[$intPlannumber] = $objCmaps -> fields['amount'];
        $objCmaps -> MoveNext();
    }
    $objCmaps -> Close();

    /**
     * Count completed plans
     */
    $objCplans = $db -> Execute("SELECT `amount`, `name` FROM `astral_plans` WHERE `owner`=".$intOwner." AND `name` LIKE 'P%' AND `location`='".$strLocation."'") or die($db -> ErrorMsg());
    $arrCplansamount = array(0, 0, 0, 0, 0, 0, 0);
    while (!$objCplans -> EOF)
    {
        $intItemnumber = (int)str_replace("P", "", $objCplans -> fields['name']);
        $intItemnumber = $intItemnumber - 1;
        $arrCplansamount[$intItemnumber] = $objCplans -> fields['amount'];
        $objCplans -> MoveNext();
    }
    $objCplans -> Close();

    /**
     * Count completed recipes
     */
    $objCrecipes = $db -> Execute("SELECT `amount`, `name` FROM `astral_plans` WHERE `owner`=".$intOwner." AND `name` LIKE 'R%' AND `location`='".$strLocation."'") or die($db -> ErrorMsg());
    $arrCrecipesamount = array(0, 0, 0, 0, 0, 0, 0);
    while (!$objCrecipes -> EOF)
    {
        $intPotionnumber = (int)str_replace("R", "", $objCrecipes -> fields['name']);
        $intPotionnumber = $intPotionnumber - 1;
        $arrCrecipesamount[$intPotionnumber] = $objCrecipes -> fields['amount'];
        $objCrecipes -> MoveNext();
    }
    $objCrecipes -> Close();
}

/**
 * Fuction merge maps, plans, recipes
 */
function mergeplans($strLocation, $intOwner)
{
    global $db;
    global $arrMapsamount;
    global $arrPlansamount;
    global $arrRecipesamount;

    $arrStep = array('M', 'P', 'R');
    if (!in_array($_GET['step'], $arrStep))
    {
        error(ERROR);
    }
    $_GET['number'] = intval($_GET['number']);
    if ($_GET['number'] < 0) 
    {
        error (ERROR);
    }
    $intKey = $_GET['number'];
    $blnTest = false;
    if ($_GET['step'] == 'M')
    {
        $arrTest = $arrMapsamount;
    }
    if ($_GET['step'] == 'P')
    {
        $arrTest = $arrPlansamount;
    }
    if ($_GET['step'] == 'R')
    {
        $arrTest = $arrRecipesamount;
    }
    foreach ($arrTest[$intKey] as $intAmount)
    {
        if ($intAmount)
        {
            $blnTest = true;
        }
            else
        {
            error(ERROR);
        }
    }
    $intNumber2 = $intKey + 1;
    $strName = $_GET['step'].$intNumber2;
    $objTest = $db -> Execute("SELECT `amount` FROM `astral_plans` WHERE `owner`=".$intOwner." AND `name`='".$strName."' AND `location`='".$strLocation."'") or die($db -> ErrorMsg());
    if ($objTest -> fields['amount'])
    {
        $db -> Execute("UPDATE `astral_plans` SET `amount`=`amount`+1 WHERE `owner`=".$intOwner." AND `name`='".$strName."' AND `location`='".$strLocation."'") or die($db -> ErrorMsg());
    }
        else
    {
        $db -> Execute("INSERT INTO `astral_plans` (`owner`, `name`, `amount`, `location`) VALUES(".$intOwner.", '".$strName."', 1, '".$strLocation."')");
    }
    $objTest -> Close();
    $strName2 = $_GET['step'].$intKey;
    $i = 0;
    foreach ($arrTest[$intKey] as $intAmount)
    {
        if ($intAmount > 1)
        {
            $db -> Execute("UPDATE `astral` SET `amount`=`amount`-1 WHERE `owner`=".$intOwner." AND `type`='".$strName2."' AND `number`=".$i." AND `location`='".$strLocation."'") or die($db -> ErrorMsg());
        }
            else
        {
            $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$intOwner." AND `type`='".$strName2."' AND `number`=".$i." AND `location`='".$strLocation."'") or die($db -> ErrorMsg());
        }
        $i ++;
    }
}

/**
 * Function assign components to arrays
 */
function showcomponents($strLocation, $intOwner)
{
    global $db;

    /**
     * Count components
     */
    $arrComponents = array(array(0, 0, 0, 0, 0, 0, 0), array(0, 0, 0, 0, 0), array(0, 0, 0, 0, 0));
    $arrType = array('C', 'O', 'T');
    for ($i = 0; $i < 3; $i++)
    {
        $strType = $arrType[$i]."%";
        $objComponent = $db -> Execute("SELECT `amount`, `type` FROM `astral` WHERE `owner`=".$intOwner." AND `type` LIKE '".$strType."' AND `location`='".$strLocation."'");
        while (!$objComponent -> EOF)
        {
            $intNumber = (int)str_replace($arrType[$i], "", $objComponent -> fields['type']);
            $arrComponents[$i][$intNumber] = $objComponent -> fields['amount'];
            $objComponent -> MoveNext();
        }
        $objComponent -> Close();
    }
    return $arrComponents;
}

?>
