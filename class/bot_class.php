<?php
/**
 *   File functions:
 *   Bot class - based on ELIZA algorithm
 *
 *   @name                 : bot_class.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 28.05.2012
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

class Bot 
{
    /**
     * Text which write player to bot
     * @var string
     */
    var $strPlayertext;
    /**
     * Name of bot
     * @var string
     */
    var $strBotname;
    /**
     * Array of player texts on which bot create answer
     * @var array
     */
    var $arrPatsreg;
    /**
     * Array of arrays of possibly answers on player text
     * @var array
     */
    var $arrPatsansw;
    /**
     * Array of personal pronouns to convert
     * @var array
     */
    var $arrReflorg;
    /**
     * Array of converted personal pronouns
     * @var array
     */
    var $arrReflans;
    /**
     * Class constructor - get text and bot name
     * @param string $strText Player text
     * @param string $strName Bot name
     */
    function Bot($strText, $strName) 
    {
        global $lang;
        $this -> strPlayertext = $strText;
        $this -> strBotname = $strName;
        require_once("languages/".$lang."/bot_class.php");
        $this -> arrReflorg = $arrReflectionsorg;
        $this -> arrReflans = $arrReflectionsans;
        $this -> arrPatsreg = $arrPatsreglang;
        $this -> arrPatsans = $arrPatsanslang;
    }
    /**
     * Check - text go to bot?
     * @return boolean <b>true</b> when text is direct to bot otherwise <b>false</b>
     */
    function Checkbot()
    {
	if (strpos($this->strPlayertext, $this->strBotname) === 0)
        {
            return true;
        }
	else
        {
            return false;
        }
    }
    /**
     * Create answer from bot
     * @return string Answer of bot
     */
    function Botanswer()
    {
        global $player;
        global $db;
        $intKey = 0;
        foreach ($this->arrPatsreg as $strPatsreg)
	  {
	    if (preg_match("/".$strPatsreg."/", $this->strPlayertext) == 1)
            {
                break;
            }
            $intKey++;
        }
        switch ($intKey)
        {
            case 0 :
                $arrText = explode(" ", $this -> strPlayertext);
                $strAnswer = $this -> arrPatsans[0][0];
                $objUser = $db -> Execute("SELECT user FROM players WHERE id=".end($arrText));
                if (empty($objUser -> fields['user']))
                {
                    $objUser -> fields['user'] = $player -> user;
                }
                $strAnswer = str_replace("%3", $objUser -> fields['user'], $strAnswer);
                $strAnswer = str_replace("%2", $player -> user, $strAnswer);
                $intValues = count($arrText) - 2;
                $strItem = ' ';
                for ($i = 1; $i < $intValues; $i++)
                {
                    $strItem = $strItem.$arrText[$i]." ";
                }
                $strAnswer = str_replace("%1", $strItem, $strAnswer);
                $objUser -> Close();
                break;
            case 1 :
                $arrText = explode(" ", $this -> strPlayertext);
                $strAnswer = $this -> arrPatsans[1][0];
                $strAnswer = str_replace("%2", $player -> user, $strAnswer);
                $intValues = count($arrText) - 2;
                $strItem = ' ';
                for ($i = 1; $i < $intValues; $i++)
                {
                    $strItem = $strItem.$arrText[$i]." ";
                }
                $strAnswer = str_replace("%1", $strItem, $strAnswer);
                break;
            case 2 :
                $objQuery = $db -> Execute("SELECT id FROM events");
                $intNumber = $objQuery -> RecordCount();
                $objQuery -> Close();
                if ($intNumber > 0) 
                {
                    $intRoll = rand (1, $intNumber);
                    $objEvent = $db -> Execute("SELECT text FROM events WHERE id=".$intRoll);
                    $strAnswer = $this -> arrPatsans[2][0];
                    $strAnswer = str_replace("%1", $objEvent -> fields['text'], $strAnswer);
                    $objEvent -> Close();
                } 
                    else 
                {
                    $strAnswer = $this -> arrPatsans[2][1];
                }
                break;
        }
        $intValues = count($this -> arrPatsreg);
        if ($intKey > 2 && $intKey < $intValues)
        {
            $strReg = $this -> arrPatsreg[$intKey];
            $strReg = str_replace('.', '', $strReg);
            $strReg = str_replace('*', '', $strReg);
            $strReg = strtolower($strReg);
	    $this->strPlayertext = str_replace($this->strBotname, '', $this->strPlayertext);
            $this -> strPlayertext = strtolower($this -> strPlayertext);
            $strItem = str_replace($strReg, '', $this -> strPlayertext);
            if (in_array($strItem, $this -> arrReflorg))
            {
                $intKey2 = array_search($strItem, $this -> arrReflorg);
                $strItem = $this -> arrReflans[$intKey2];
            }
            $intAnswers = count($this -> arrPatsans[$intKey]) - 1;
            $intElement = rand(0, $intAnswers);
            $strAnswer = str_replace("%1", $strItem, $this -> arrPatsans[$intKey][$intElement]);
        }
            elseif ($intKey > 2)
        {
            $arrText = end($this -> arrPatsans);
            $intElements = count($arrText) - 1;
            $intAnswer = rand (0, $intElements);
            $strAnswer = $arrText[$intAnswer];
        }
        return $strAnswer;
    }
}
