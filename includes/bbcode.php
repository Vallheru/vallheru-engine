<?php
/**
 *   File functions:
 *   Convert HTML to BBcode or BBcode to HTML
 *
 *   @name                 : bbcode.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 30.09.2011
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
* Function convert bbcode tags [b] on HTML <b>, add smiles, new lines, quotes
*/
function bbcodetohtml($text, $isChat = FALSE) 
{
    global $db;

    /**
    * Replace bad words
    */
    $objBadwords = $db -> Execute("SELECT * FROM bad_words");
    $arrText = explode(" ", $text);
    while (!$objBadwords -> EOF)
      {
	foreach ($arrText as &$word)
	  {
	    if (stripos($word, $objBadwords->fields['bword']) === 0)
	      {
		$word = '[kwiatek]';
	      }
	  }
	$objBadwords->MoveNext();
      }
    $objBadwords -> Close();
    $text = implode(" ", $arrText);

    /**
    * Delete HTML tags from text
    */
    $text = str_replace("<", "&lt;", $text);
    $text = strip_tags($text);

    /**
     * Make links clickable
     */
    $text = preg_replace('#(www\.|https?:\/\/){1}[a-zA-Z0-9]{2,}\.[a-zA-Z0-9]{2,}(\S*)#i', "<a href=\"$0\" target=\"_blank\">$0</a>", $text);

    /**
     * Replace bbcode tags
     */
    $arrBBon = array('[b]', '[i]', '[u]', '[center]', '[quote]');
    $arrBBoff = array('[/b]', '[/i]', '[/u]', '[/center]', '[/quote]');
    $arrHtmlon = array("<b>", "<i>", "<u>", "<center>", "<br />Cytat:<br /><i>");
    $arrHtmloff = array("</b>", "</i>", "</u>", "</center>", "</i>");
    for ($i = 0; $i < 5; $i++)
      {
	if (($isChat) && ($i > 2))
	  {
	    $text = str_replace($arrBBon[$i], "", $text);
	    $text = str_replace($arrBBoff[$i], "", $text);
	    continue;
	  }
	$text = str_replace($arrBBon[$i], $arrHtmlon[$i], $text);
	$text = str_replace($arrBBoff[$i], $arrHtmloff[$i], $text);
	$intStart = substr_count($text, $arrHtmlon[$i]);
	$intEnd = substr_count($text, $arrHtmloff[$i]);
	if ($intStart > $intEnd)
	  {
	    for ($j = 0; $j < ($intStart - $intEnd); $j++)
	      {
		$text .= $arrHtmloff[$i];
	      }
	  }
      }

    /**
     * Replace colors
     */
    if (!$isChat)
      {
	$intStart = 0;
	while (TRUE)
	  {
	    $intStart = strpos($text, "[color", $intStart);
	    if ($intStart === FALSE)
	      {
		break;
	      }
	    $intScolor = strpos($text, " ", $intStart) + 1;
	    if ($intScolor === FALSE)
	      {
		$intStart += 3;
		continue;
	      }
	    $intEnd = strpos($text, "]", $intScolor);
	    if ($intEnd === FALSE)
	      {
		break;
	      }
	    $strColor = substr($text, $intScolor, $intEnd - $intScolor);
	    $text = substr_replace($text, '<span style="color: '.$strColor.';">', $intStart, $intEnd - $intStart + 1);
	    $intStart = 0;
	  }
	$text = str_replace("[/color]", "</span>", $text);
	$intStart = substr_count($text, '<span');
	$intEnd = substr_count($text, "</span>");
	if ($intStart > $intEnd)
	  {
	    for ($j = 0; $j < ($intStart - $intEnd); $j++)
	      {
	    $text .= "</span>";
	      }
	  }
      }
  
    /**
    * Change \n on <br />
    */
    $text = nl2br($text);

    /**
    * Add smiles
    */
    $text = str_replace(":)","<img src=\"images/smile.gif\" title=\":) - uśmiech\" />", $text);
    $text = str_replace(":D","<img src=\"images/bigsmile.gif\" title=\":D - śmiech\" />", $text);
    $text = str_replace(":(","<img src=\"images/frown.gif\" title=\":( - smutny\" />", $text);
    $text = str_replace(":o","<img src=\"images/suprised.gif\" title=\":o - zdziwiony\" />", $text);
    $text = str_replace(";(","<img src=\"images/cry.gif\" title=\";( - płacze\" />", $text);
    $text = str_replace(":]", "<img src=\"images/cheesy.png\" title=\":] - wesoły\" />", $text);
    $text = str_replace(":P", "<img src=\"images/tongue.gif\" title=\":P - pokazuje język\" />", $text);
    $text = str_replace(":~", "<img src=\"images/sliniak-1.gif\" title=\":~ - ślini się\" />", $text);
   
    /**
    * Return converted text
    */
    return $text;
}

/**
* Function convert HTML tags <b> etc on BBcode [b] and replace smiles and quotes
*/
function htmltobbcode($text) 
{
    /**
    * Bold font
    */
    $text = str_replace("<b>","[b]",$text);
    $text = str_replace("</b>","[/b]",$text);
    /**
    * Italic font
    */
    $text = str_replace("<i>","[i]",$text);
    $text = str_replace("</i>","[/i]",$text);
    /**
    * Underline
    */
    $text = str_replace("<u>","[u]",$text);
    $text = str_replace("</u>","[/u]",$text);
    /**
     * Replace links
     */
    $text = preg_replace('/<a href=.>/', '', $text);
    $text = str_replace("</a>", "", $text);
    /**
     * Colors
     */
    $intStart = 0;
    while (TRUE)
      {
	$intStart = strpos($text, '<span style="color: ', $intStart);
	if ($intStart === FALSE)
	  {
	    break;
	  }
	$intScolor = strpos($text, " ", $intStart + 8) + 1;
	if ($intScolor === FALSE)
	  {
	    $intStart += 3;
	    continue;
	  }
	$intEnd = strpos($text, ";", $intScolor);
	if ($intEnd === FALSE)
	  {
	    break;
	  }
	$strColor = substr($text, $intScolor, $intEnd - $intScolor);
	$text = substr_replace($text, '[color '.$strColor.']', $intStart, $intEnd - $intStart + 3);
	//Remove </span>
	$intStart = strpos($text, "</span>", $intStart);
	if ($intStart !== FALSE)
	  {
	    $text = substr_replace($text, "[/color]", $intStart, 7);
	  }
	$intStart = 0;
      }
    /**
    * Replace smiles
    */
    $text = str_replace("<img src=\"images/smile.gif\" title=\":) - uśmiech\" />",":)", $text);
    $text = str_replace("<img src=\"images/bigsmile.gif\" title=\":D - śmiech\" />",":D", $text);
    $text = str_replace("<img src=\"images/frown.gif\" title=\":( - smutny\" />",":(", $text);
    $text = str_replace("<img src=\"images/suprised.gif\" title=\":o - zdziwiony\" />",":o", $text);
    $text = str_replace("<img src=\"images/cry.gif\" title=\";( - płacze\" />",";(", $text);
    $text = str_replace("<img src=\"images/cheesy.png\" title=\":] - wesoły\" />",":]", $text);
    $text = str_replace("<img src=\"images/tongue.gif\" title=\":P - pokazuje język\" />",":P", $text);
    $text = str_replace("<img src=\"images/sliniak-1.gif\" title=\":~ - ślini się\" />",":~", $text);
    /**
     * Center text
     */
    $text = str_replace("<center>", "[center]", $text);
    $text = str_replace("</center>", "[/center]", $text);
    /**
     * Quote text
     */
    $text = str_replace("<br />Cytat:<br /><i>", "[quote]", $text);
    $text = str_replace("&nbsp;</i>", "[/quote]", $text);
    /**
    * Delete HTML tags
    */
    $text = strip_tags($text);
    /**
    * Return converted text
    */
    return $text;
}
?>
