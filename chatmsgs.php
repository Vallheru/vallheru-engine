<?php
/**
 *   File functions:
 *   Show text in chat
 *
 *   @name                 : chatmsg.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.0
 *   @since                : 03.02.2006
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
// $Id: chatmsgs.php 566 2006-09-13 09:31:08Z thindil $
require_once('includes/config.php');
require_once('includes/sessions.php');
require_once('libs/Smarty.class.php');

$smarty = new Smarty;
$smarty -> compile_check = true;

$strPass = MD5($_SESSION['pass']);
$stat = $db -> Execute("SELECT id, rank, lang, seclang, style, graphic FROM players WHERE email='".$_SESSION['email']."' AND pass='".$strPass."'");

/**
* Get the localization for game
*/
require_once("languages/".$stat -> fields['lang']."/chatmsg.php");

/**
* Select style for chat
*/
if ($stat -> fields['graphic']) 
{
    $smarty -> template_dir = "./templates/".$player -> graphic;
    $smarty -> compile_dir = "./templates_c/".$player -> graphic;
    $strCss = '';
}   
    else
{
    $smarty -> template_dir = './templates';
    $smarty -> compile_dir = './templates_c';
    $strCss = $stat -> fields['style'];
}

$chat = $db -> SelectLimit("SELECT * FROM chat WHERE lang='".$stat -> fields['lang']."' OR lang='".$stat -> fields['seclang']."' AND ownerid=0 OR ownerid=".$stat -> fields['id']." OR senderid=".$stat -> fields['id']." ORDER BY id DESC", 25);
$pl = $db -> Execute("SELECT rank, id, lpv, user FROM players WHERE page='Chat'");
$arrtext = array();
$arrauthor = array();
$arrsenderid = array();
$i = 0;
if ($stat -> fields['rank'] == 'Admin' || $stat -> fields['rank'] == 'Staff' || $stat -> fields['rank'] == 'Karczmarka') 
{
    $smarty -> assign ("Showid", 1);
}
while (!$chat -> EOF) 
{
    $text = wordwrap($chat -> fields['chat'],30,"\n",1);
    $arrtext[$i] = $text;
    $arrauthor[$i] = $chat -> fields['user'];
    $arrsenderid[$i] = $chat -> fields['senderid'];
    $chat -> MoveNext();
    $i = $i + 1;
}
$chat -> Close();


$ctime = time();
$on = '';
$numon = 0;
while (!$pl -> EOF) 
{
    $span = ($ctime - $pl -> fields['lpv']);
    if ($span <= 180) 
    {
        $on = $on." [<A href=view.php?view=".$pl -> fields['id']." target=_parent>".$pl -> fields['user']."</a> (".$pl -> fields['id'].")] ";
        $numon = ($numon + 1);
    }
    $pl -> MoveNext();
}
$pl -> Close();
$query = $db -> Execute("SELECT id FROM chat");
$numchat = $query -> RecordCount();
$query -> Close();

$smarty -> assign ( array("Player" => $on, 
    "Text1" => $numchat, 
    "Online" => $numon, 
    "Author" => $arrauthor, 
    "Text" => $arrtext, 
    "Senderid" => $arrsenderid,
    "Charset" => CHARSET,
    "Thereis" => THERE_IS,
    "Texts" => TEXTS,
    "Cplayers" => C_PLAYERS,
    "Cid" => C_ID,
    "CSS" => $strCss));
$smarty -> display ('chatmsgs.tpl');

?>
