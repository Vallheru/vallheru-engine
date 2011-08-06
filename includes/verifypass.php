<?php
/**
 *   File functions:
 *   Password verification - lenght and etc
 *
 *   @name                 : verifypass.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 24.09.2006
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
// $Id: verifypass.php 616 2006-09-24 07:43:22Z thindil $

/**
* Get the localization for game
*/
if (!isset($player -> lang))
{
    /**
     * Check avaible languages
     */    
    $path = 'languages/';
    $dir = opendir($path);
    $arrTranslations = array();
    $i = 0;
    while ($file = readdir($dir))
    {
        if (!ereg(".htm*$", $file))
        {
            if (!ereg("\.$", $file))
            {
                $arrTranslations[$i] = $file;
                $i ++;
            }
        }
    }
    closedir($dir);

    $strLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    foreach ($arrTranslations as $strTrans)
    {
        $strSearch = "^".$strTrans;
        if (eregi($strSearch, $strLanguage))
        {
            $strTranslation = $strTrans;
            break;
        }
    }
    if (!isset($strTranslation))
    {
        $strTranslation = 'pl';
    }
}
    else
{
    $strTranslation = $player -> lang;
}

require_once("languages/".$strTranslation."/verifypass.php");

function verifypass($string,$action) 
{
    global $smarty;

	/**
	 * Check password length
	 */
    $test = strlen($string);
    if ($test < 5) 
	{
        if ($action == 'register') 
		{
            $smarty -> assign ("Error", TOO_SHORT);
            $smarty -> display ('error.tpl');
            exit;
        } 
		    else 
		{
            error(TOO_SHORT);
        }
    }

	/**
	 * Check for letters and numbers
	 */
    $test = eregi(".[a-z]", $string);
    $test1 = ereg(".[0-9]", $string);
    if (empty($test) || empty($test1)) 
	{
        if ($action == 'register') 
		{
            $smarty -> assign ("Error", NO_NUMBER);
            $smarty -> display ('error.tpl');
            exit;
        } 
		    else 
		{
            error(NO_NUMBER);
        }
    }

	/**
	 * Check for upper letters
	 */
    if (!ereg("[[:upper:]]", $string)) 
	{
        if ($action == 'register') 
		{
            $smarty -> assign ("Error", NO_BIG);
            $smarty -> display ('error.tpl');
            exit;
        } 
		    else 
		{
            error(NO_BIG);
        }
    }
}                   
