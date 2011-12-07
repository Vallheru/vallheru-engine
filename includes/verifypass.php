<?php
/**
 *   File functions:
 *   Password verification - lenght and etc
 *
 *   @name                 : verifypass.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.5
 *   @since                : 07.12.2011
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

require_once("languages/".$lang."/verifypass.php");

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
    $test = preg_match("/[a-zA-Z]+/", $string);
    $test1 = preg_match("/[0-9]+/", $string);
    if ($test == 0 || $test1 == 0) 
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
    if (!preg_match("/[A-Z]+/", $string)) 
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
