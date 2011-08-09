<?php
/**
 *   File functions:
 *   Convert HTML to BBcode or BBcode to HTML
 *
 *   @name                 : bbcode.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 26.09.2006
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
function bbcodetohtml($text) 
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
    $text = strip_tags($text);

    /**
     * Replace bbcode tags
     */
    $arrBBon = array('[b]', '[i]', '[u]', '[center]', '[quote]');
    $arrBBoff = array('[/b]', '[/i]', '[/u]', '[/center]', '[/quote]');
    $arrHtmlon = array("<b>", "<i>", "<u>", "<center>", "<br />Cytat:<br /><i>");
    $arrHtmloff = array("</b>", "</i>", "</u>", "</center>", "&nbsp;</i>");
    for ($i = 0; $i < 5; $i++)
      {
	$text = str_replace($arrBBon[$i], $arrHtmlon[$i], $text);
	$text = str_replace($arrBBoff[$i], $arrHtmloff[$i], $text);
	if (strrpos($text, $arrHtmloff[$i]) < strrpos($text, $arrHtmlon[$i]))
	  {
	    $text = substr_replace($text, ' ', strrpos($text, $arrHtmlon[$i]), strrpos($text, $arrHtmlon[$i]) + strlen($arrHtmlon[$i]));
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
    * Replace smiles
    */
    $text = str_replace("<img src=\"images/smile.gif\" title=\":) - uśmiech\" />",":)", $text);
    $text = str_replace("<img src=\"images/bigsmile.gif\" title=\":D - śmiech\" />",":D", $text);
    $text = str_replace("<img src=\"images/frown.gif\" title=\":( - smutny\" />",":(", $text);
    $text = str_replace("<img src=\"images/suprised.gif\" title=\":o - zdziwiony\" />",":o", $text);
    $text = str_replace("<img src=\"images/cry.gif\" title=\";( - płacze\" />",";(", $text);
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
