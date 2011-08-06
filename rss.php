<?php
/**
 *   File functions:
 *   RSS Channel
 *
 *   @name                 : rss.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.1
 *   @since                : 23.06.2006
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
// 

header("Content-Type: text/xml; charset=utf-8");

require_once('includes/config.php');

$objUpdate = $db -> SelectLimit("SELECT * FROM updates WHERE lang='pl' ORDER BY id DESC", 5);
$strNews = '';
while (!$objUpdate -> EOF) 
{
    $arrTime = explode("-", $objUpdate -> fields['time']);
    $strTime = mktime(0, 0, 0, $arrTime[1], $arrTime[2], $arrTime[0]);
    $strText = htmlspecialchars($objUpdate -> fields['updates']);
    $strNews = $strNews."<item>\n<title>".$objUpdate -> fields['title']."</title>\n<dc:creator>".$objUpdate -> fields['starter']."</dc:creator>\n"."<description>".$strText."</description>\n<pubDate>".date("r", $strTime)."</pubDate>\n</item>\n";
    $objUpdate -> MoveNext();        
}
$objUpdate -> Close();

// start the RSS output 
print "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n\n";
print "<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">";
print "<channel>\n";
print "<title>".$gamename."</title>\n";
print "<link>".$gameadress."</link>\n";
print "<description>Najnowsze wieści z ".$gamename."</description>\n";
print "<language>pl</language>\n";
print "<webMaster>".$adminmail."</webMaster>\n";
print "<copyright>2004,2005,2006 Vallheru Team. Released under GNU/GPL license</copyright>\n";
print "<pubDate>".date("r")."</pubDate>";
print $strNews;
print "</channel>\n";
print "</rss>\n";

?>
