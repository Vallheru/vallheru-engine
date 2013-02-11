<?php
/**
 *   File functions:
 *   Additional file - make new era in game. Before start this script - copy table players to table zapas and table notatnik to table zapas1
 *
 *   @name                 : resetall.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 11.02.2013
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

require_once('config.php');

/**
* Clear tables
*/

$db -> Execute("TRUNCATE TABLE `aktywacja`");
$db -> Execute("TRUNCATE TABLE `ban`");
$db -> Execute("TRUNCATE TABLE `ban_mail`");
$db -> Execute("TRUNCATE TABLE `bugreport`");
$db -> Execute("TRUNCATE TABLE `bugtrack`");
$db -> Execute("TRUNCATE TABLE chat");
$db -> Execute("TRUNCATE TABLE chat_config");
$db -> Execute("TRUNCATE TABLE core");
$db -> Execute("TRUNCATE TABLE core_market");
$db -> Execute("TRUNCATE TABLE farm");
$db -> Execute("TRUNCATE TABLE farms");
$db -> Execute("TRUNCATE TABLE herbs");
$db -> Execute("TRUNCATE TABLE hmarket");
$db -> Execute("TRUNCATE TABLE houses");
$db -> Execute("TRUNCATE TABLE jail");
$db -> Execute("TRUNCATE TABLE log");
$db -> Execute("TRUNCATE TABLE lumberjack");
$db -> Execute("TRUNCATE TABLE mail");
$db -> Execute("TRUNCATE TABLE mill_work");
$db -> Execute("TRUNCATE TABLE minerals");
$db -> Execute("TRUNCATE TABLE mines");
$db -> Execute("TRUNCATE TABLE mines_search");
$db -> Execute("TRUNCATE TABLE outposts");
$db -> Execute("TRUNCATE TABLE outpost_monsters");
$db -> Execute("TRUNCATE TABLE outpost_veterans");
$db -> Execute("TRUNCATE TABLE pmarket");
$db -> Execute("TRUNCATE TABLE reset");
$db -> Execute("TRUNCATE TABLE smelter");
$db -> Execute("TRUNCATE TABLE smith_work");
$db -> Execute("TRUNCATE TABLE tribe_mag");
$db -> Execute("TRUNCATE TABLE tribe_oczek");
$db -> Execute("TRUNCATE TABLE tribe_perm");
$db -> Execute("TRUNCATE TABLE tribe_rank");
$db -> Execute("TRUNCATE TABLE tribe_replies");
$db -> Execute("TRUNCATE TABLE tribe_topics");
$db -> Execute("TRUNCATE TABLE tribe_zbroj");
$db -> Execute("TRUNCATE TABLE tribe_herbs");
$db -> Execute("TRUNCATE TABLE tribe_minerals");
$db -> Execute("TRUNCATE TABLE tribe_reserv");
$db -> Execute("TRUNCATE TABLE tribes");
$db -> Execute("TRUNCATE TABLE events");
$db -> Execute("TRUNCATE TABLE questaction");
$db -> Execute("TRUNCATE TABLE warehouse");
$db -> Execute("TRUNCATE TABLE `jeweller_work`");
$db->Execute("TRUNCATE TABLE `players`");
$db->Execute("TRUNCATE TABLE `notatnik`");
$db -> Execute("DELETE FROM alchemy_mill WHERE owner>0");
$db -> Execute("DELETE FROM czary WHERE gracz>0");
$db -> Execute("DELETE FROM equipment WHERE owner>0");
$db -> Execute("DELETE FROM potions WHERE owner>0");
$db -> Execute("DELETE FROM mill WHERE owner>0");
$db -> Execute("DELETE FROM smith WHERE owner>0");
$db -> Execute("DELETE FROM `jeweller` WHERE `owner`>0");
$db -> Execute("UPDATE settings SET value='100000' WHERE setting='monsterhp'");
$db -> Execute("UPDATE settings SET value='' WHERE setting='item'");
$db -> Execute("UPDATE settings SET value='' WHERE setting='player'");
$db -> Execute("UPDATE `settings` SET `value`='' WHERE `setting`='tribe'");
$db->Execute("UPDATE `settings` SET `value`=1 WHERE `setting`='day'");
$db -> Execute("TRUNCATE TABLE `lost_pass`");
$db -> Execute("TRUNCATE TABLE `astral_machine`");
$db -> Execute("TRUNCATE TABLE `astral_bank`");
$db -> Execute("TRUNCATE TABLE `amarket`");
$db -> Execute("TRUNCATE TABLE `astral_plans`");
$db -> Execute("TRUNCATE TABLE `astral`");
$db->Execute("TRUNCATE TABLE `revent`");
$db->Execute("TRUNCATE TABLE `brecords`");
$db->Execute("TRUNCATE TABLE `battlelogs`");
print "Tables cleared<br />";

/**
* Copy players
*/
$player = $db -> Execute("SELECT * FROM zapas ORDER BY `id` ASC");
while (!$player -> EOF) 
{
    $player -> fields['profile'] = addslashes($player -> fields['profile']);
    $player -> fields['roleplay'] = addslashes($player -> fields['roleplay']);
    $player -> fields['shortrpg'] = addslashes($player -> fields['shortrpg']);
    $player -> fields['ooc'] = addslashes($player -> fields['ooc']);
    $db -> Execute("INSERT INTO `players` (`user`, `email`, `pass`, `rank`, `age`, `logins`, `profile`, `avatar`, `vallars`, `roleplay`, `shortrpg`, `ooc`, `settings`) VALUES('".$player -> fields['user']."','".$player -> fields['email']."','".$player -> fields['pass']."','".$player -> fields['rank']."',".$player -> fields['age'].",".$player -> fields['logins'].",'".$player -> fields['profile']."','".$player -> fields['avatar']."', ".$player->fields['vallars'].", '".$player->fields['roleplay']."', '".$player->fields['shortrpg']."', '".$player->fields['ooc']."', '".$player->fields['settings']."')") or die($db -> ErrorMsg());
    $player -> MoveNext();
}
$player -> Close();
print "Players copied<br />";

/**
* New id for authors in library
*/
$objAuthor = $db -> Execute("SELECT author FROM library GROUP BY author");
while (!$objAuthor -> EOF)
{
    $arrAuthorId = explode(": ", $objAuthor -> fields['author']);
    $intPid = $arrAuthorId[1];
    $objOldid = $db -> Execute("SELECT user FROM zapas WHERE id=".$intPid);
    $objNewid = $db -> Execute("SELECT id FROM players WHERE user='".$objOldid -> fields['user']."'");
    if ($objNewid -> fields['id'])
    {
        $arrAuthorId[1] = $objNewid -> fields['id'];
    }
        else
    {
        $arrAuthorId[1] = rand(10000,100000);
    }
    $strNewauthor = implode(": ", $arrAuthorId);
    $db -> Execute("UPDATE library SET author='".$strNewauthor."' WHERE author='".$objAuthor -> fields['author']."'");
    $objOldid -> Close();
    $objAuthor -> MoveNext();
}
$objAuthor -> Close();
print "Library ok<br />";

/**
 * Copy notes
 */
$objAuthor = $db->Execute("SELECT `gracz` FROM `zapas1` GROUP BY `gracz`");
while (!$objAuthor->EOF)
  {
    $objOldid = $db->Execute("SELECT `user` FROM `zapas` WHERE `id`=".$objAuthor->fields['gracz']);
    $objNewid = $db->Execute("SELECT `id` FROM `players` WHERE `user`='".$objOldid->fields['user']."'");
    $objNotes = $db->Execute("SELECT * FROM `zapas1` WHERE `gracz`=".$objAuthor->fields['gracz']);
    while (!$objNotes->EOF)
      {
	$db->Execute("INSERT INTO `notatnik` (`gracz`, `tekst`, `czas`, `title`) VALUES(".$objNewid->fields['id'].", '".$objNotes->fields['tekst']."', '".$objNotes->fields['czas']."', '".$objNotes->fields['title']."')");
	$objNotes->MoveNext();
      }
    $objNotes->Close();
    $objAuthor->MoveNext();
  }
$objAuthor->Close();
print "Notes copied<br />";

/**
 * Update contacts
 */
$objContact = $db->Execute("SELECT `id`, `owner`, `pid` FROM `contacts`");
while (!$objContact->EOF)
  {
    $objOwner = $db->Execute("SELECT `user` FROM `zapas` WHERE `id`=".$objContact->fields['owner']);
    $objNewowner = $db->Execute("SELECT `id` FROM `players` WHERE `user`='".$objOwner->fields['user']."'");
    $objOwner->Close();
    $objPid = $db->Execute("SELECT `user` FROM `zapas` WHERE `id`=".$objContact->fields['pid']);
    $objNewpid = $db->Execute("SELECT `id` FROM `players` WHERE `user`='".$objPid->fields['user']."'");
    $objPid->Close();
    $db->Execute("UPDATE `contacts` SET `owner`=".$objNewowner->fields['id'].", `pid`=".$objNewpid->fields['id']." WHERE `id`=".$objContact->fields['id']);
    $objContact->MoveNext();
  }
$objContact->Close();
print "Contacts OK<br />";

/**
 * Update vallars informations
 */
$objVallars = $db->Execute("SELECT `id`, `owner` FROM `vallars`");
while (!$objVallars->EOF)
  {
    $objOwner = $db->Execute("SELECT `user` FROM `zapas` WHERE `id`=".$objVallars->fields['owner']);
    $objNewowner = $db->Execute("SELECT `id` FROM `players` WHERE `user`='".$objOwner->fields['user']."'");
    $objOwner->Close();
    $db->Execute("UPDATE `vallars` SET `owner`=".$objNewowner->fields['id']." WHERE `id`=".$objVallars->fields['id']);
    $objVallars->MoveNext();
  }
$objVallars->Close();
print "Vallars info OK<br />";

/**
* Copy referrals
*/
$ref = $db -> Execute("SELECT refs, user FROM zapas WHERE refs>0");
while (!$ref -> EOF) 
{
    $refuser = $db -> Execute("SELECT user FROM zapas WHERE id=".$ref -> fields['refs']);
    $newrefuser = $db -> Execute("SELECT id FROM players WHERE user='".$refuser -> fields['user']."'");
    $refuser -> Close();
    $db -> Execute("UPDATE players SET refs=".$newrefuser -> fields['id']." WHERE user='".$ref -> fields['user']."'");
    $newrefuser -> Close();
    $ref -> MoveNext();
}
$ref -> Close();
print "Vallars copied<br />End reset database.";

?>
