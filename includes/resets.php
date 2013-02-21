<?php
/**
 *   File functions:
 *   Resets in game - (mainreset) main reset and (smallreset) other resets
 *
 *   @name                 : resets.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 21.02.2013
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
 * Add energy every 20 minutes
 */
function energyreset()
{
  global $db;
  $db->Execute("UPDATE `players` SET `energy`=`energy`+(`max_energy`/72) WHERE `miejsce`!='Lochy' AND `freeze`=0 AND `rasa`!='' AND `klasa`!='' AND `energy`<(21 * `max_energy`)");
}

/**
* Other reset in this same day
*/
function smallreset($blnSmall = FALSE) 
{
    global $db;
    global $city1a;
    global $city2;

    if ($blnSmall)
      {
	$db -> Execute("UPDATE settings SET value='N' WHERE setting='open'");
	$db -> Execute("UPDATE settings SET value='Wykonywanie resetu' WHERE setting='close_reason'");
	$db -> Execute("UPDATE `players` SET `hp`=`max_hp`, `bridge`='N'");
      }
    $data = date("y-m-d");
    $strDate = $db -> DBDate($data);
    $db -> Execute("TRUNCATE TABLE events");
    $db->Execute("TRUNCATE TABLE `attacks`");
    /**
     * Grow herbs
     */
    $db -> Execute("UPDATE farm SET age=age+1");
    $db -> Execute("DELETE FROM farm WHERE age>26");
    $arrPlants = $db->GetAll("SELECT `farms`.`owner`, `farm`.`name`, `farms`.`location` FROM `farm`, `farms` WHERE `farm`.`age`=14 AND `farms`.`id`=`farm`.`farmid`");
    $strSql = "INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES";
    $time = date("Y-m-d H:i:s");
    foreach ($arrPlants as $arrPlant)
      {
	if ($arrPlant['location'] == 'Altara')
	  {
	    $strCity = $city1a;
	  }
	else
	  {
	    $strCity = $city2;
	  }
	$strSql .= "(".$arrPlant['owner'].", 'Twoja uprawa ".$arrPlant['name']." na farmie w ".$strCity." jest już gotowa do zebrania.', '".$time."', 'F'),";
      }
    $strSql = rtrim($strSql, ',').';';
    $db->Execute($strSql);
    $itemid = $db -> Execute("SELECT `id` FROM `potions` WHERE `owner`=0");
    while (!$itemid -> EOF) 
    {
        $amount = rand(1, 50);
        $db -> Execute("UPDATE `potions` SET `amount`=".$amount." WHERE `id`=".$itemid -> fields['id']);
        $itemid -> MoveNext();
    }
    $itemid -> Close();
    /**
    * Count available army in outposts
    */
    $intOutSize = 1;
    $objOutpost = $db -> Execute("SELECT size FROM outposts");
    while (!$objOutpost -> EOF)
    {
        $intOutSize = $intOutSize + $objOutpost -> fields['size'];
        $objOutpost -> MoveNext();
    }
    $objOutpost -> Close();
    $intMaxtroops = $intOutSize * 20;
    $intMintroops = $intOutSize * 15;
    $intMaxspecials = $intOutSize * 10;
    $intMinspecials = $intOutSize * 7;
    $intWarriors = rand($intMintroops, $intMaxtroops);
    $intArchers = rand($intMintroops, $intMaxtroops);
    $intCatapults = rand($intMinspecials, $intMaxspecials);
    $intBarricades = rand($intMinspecials, $intMaxspecials);
    $db -> Execute("UPDATE settings SET value='20' WHERE setting='maps'");
    $objWarriors = $db -> Execute("SELECT value FROM settings WHERE setting='warriors'");
    $objArchers = $db -> Execute("SELECT value FROM settings WHERE setting='archers'");
    $objCatapults = $db -> Execute("SELECT value FROM settings WHERE setting='catapults'");
    $objBarricades = $db -> Execute("SELECT value FROM settings WHERE setting='barricades'");
    $intWarriors = $intWarriors + $objWarriors -> fields['value'];
    $intArchers = $intArchers + $objArchers -> fields['value'];
    $intCatapults = $intCatapults + $objCatapults -> fields['value'];
    $intBarricades = $intBarricades + $objBarricades -> fields['value'];
    $objWarriors -> Close();
    $objArchers -> Close();
    $objCatapults -> Close();
    $objBarricades -> Close();
    $db -> Execute("UPDATE settings SET value='".$intWarriors."' WHERE setting='warriors'");
    $db -> Execute("UPDATE settings SET value='".$intArchers."' WHERE setting='archers'");
    $db -> Execute("UPDATE settings SET value='".$intCatapults."' WHERE setting='catapults'");
    $db -> Execute("UPDATE settings SET value='".$intBarricades."' WHERE setting='barricades'");
    /**
     * Add bonus to mana from items and check duration of antidote
     */
    $intLangs = 0;
    $arrRings = array("inteligencji", "woli");
    $arrStat = array('inteli', 'wisdom');
    $objStats = $db -> Execute("SELECT `id`, `stats`, `klasa` FROM `players`");
    while (!$objStats -> EOF)
    {
        $objRings = $db -> Execute("SELECT `power`, `name` FROM `equipment` WHERE `type`='I' AND `status`='E' AND `owner`=".$objStats -> fields['id']);
	$arrTmp = explode(';', $objStats->fields['stats']);
	$arrValues = array();
	foreach ($arrTmp as $strField)
	  {
	    $arrTmp2 = explode(':', $strField);
	    if ($arrTmp2[0] == '')
	      {
		continue;
	      }
	      $arrValues[$arrTmp2[0]] = explode(',', $arrTmp2[1]);
	}
        while (!$objRings -> EOF)
        {
            $arrRingtype = explode(" ", $objRings -> fields['name']);
            $intAmount = count($arrRingtype) - 1;
            $intKey = array_search($arrRingtype[$intAmount], $arrRings);
            if ($intKey !== FALSE)
            {
                $strStat = $arrStat[$intKey];
                $arrValues[$strStat][2] += $objRings -> fields['power'];
            }
            $objRings -> MoveNext();
        }
        $objRings -> Close();
        $objCape = $db -> Execute("SELECT `power` FROM `equipment` WHERE `type`='C' AND `status`='E' AND `owner`=".$objStats -> fields['id']);
        $intMaxmana = floor($arrValues['inteli'][2] + $arrValues['wisdom'][2]);
	if ($objStats->fields['klasa'] == 'Mag')
	  {
	    $intMaxmana = $intMaxmana * 2;
	  }
        $intMaxmana += floor(($objCape -> fields['power'] / 100) * $intMaxmana);
        $objCape -> Close();
        $db -> Execute("UPDATE `players` SET `pm`=".$intMaxmana.", `antidote`='' WHERE `id`=".$objStats -> fields['id']);
        $objStats -> MoveNext();
        
    }
    $objStats -> Close();
    $db -> Execute("UPDATE `players` SET `crime`=`crime`+1, `astralcrime`='Y' WHERE `klasa`='Złodziej' AND `freeze`=0");  
    energyreset();
    $db -> Execute("UPDATE outposts SET turns=turns+2, fatigue=100, attacks=0");
    $db -> Execute("UPDATE tribes SET atak='N'");
    $db -> Execute("UPDATE houses SET points=points+2");
    $objItemname = $db -> Execute("SELECT `name`, `id` FROM `equipment` WHERE `poison`>0 AND `type`!='R'");
    while (!$objItemname -> EOF)
    {
        $strName = str_replace("Zatruty ", "", $objItemname -> fields['name']);
        $db -> Execute("UPDATE `equipment` SET `name`='".$strName."', `poison`=0, `ptype`='' WHERE `id`=".$objItemname -> fields['id']);
        $objItemname -> MoveNext();
    }
    $objItemname -> Close();
    /**
     * Jail - count duration and free prisoners
     */
    $db -> Execute("UPDATE `jail` SET `duration`=`duration`-1");
    $jail = $db -> Execute("SELECT `id`, `duration`, `prisoner` FROM `jail`");
    while (!$jail -> EOF) 
    {
        if ($jail -> fields['duration'] == 0) 
        {
            $db -> Execute("UPDATE `players` SET `miejsce`='Altara' WHERE `id`=".$jail -> fields['prisoner']);
            $db -> Execute("DELETE FROM `jail` WHERE `id`=".$jail -> fields['id']);
        }
        $jail -> MoveNext();
    }
    $jail -> Close();
    /**
     * Ban on chat
     */
    $db -> Execute("UPDATE `chat_config` SET `resets`=`resets`-1");
    $db -> Execute("DELETE FROM `chat_config` WHERE `resets`=0");
    /**
     * Ban on forums
     */
    $db -> Execute("UPDATE `ban_forum` SET `resets`=`resets`-1");
    $db -> Execute("DELETE FROM `ban_forum` WHERE `resets`=0");
    /**
     * Add rings in shop
     */
    $objRings = $db -> Execute("SELECT `id`, `amount` FROM `rings` WHERE `amount`<28");
    $arrRings = array();
    $arrAmount = array();
    $i = 0;
    while (!$objRings -> EOF)
    {
        $arrRings[$i] = $objRings -> fields['id'];
        $arrAmount[$i] = $objRings -> fields['amount'];
        $i ++;
        $objRings -> MoveNext();
    }
    $objRings -> Close();
    $i = 0;
    foreach ($arrRings as $intRing)
    {
        $intAmount = rand(1, 4) + $arrAmount[$i];
        if ($intAmount > 28)
        {
            $intAmount = 28;
        }
        $db -> Execute("UPDATE `rings` SET `amount`=".$intAmount." WHERE `id`=".$intRing);
        $i ++;
    }
    /**
     * Add hunters quest
     */
    $arrQtypes = array('F', 'I', 'L', 'B', 'P');
    foreach (array('altara', 'ardulith') as $strCity)
      {
	//$strType = $arrQtypes[rand(0, 4)];
	$strType = 'I';
	switch ($strType)
	  {
	  case 'F':
	    $objMonsters = $db->Execute("SELECT `id` FROM `monsters` WHERE `location`='".ucfirst($strCity)."'");
	    $arrMonsters = array();
	    while (!$objMonsters->EOF)
	      {
		$arrMonsters[] = $objMonsters->fields['id'];
		$objMonsters->MoveNext();
	      }
	    $intKey = array_rand($arrMonsters);
	    $intId = $arrMonsters[$intKey];
	    $objMonsters->Close();
	    $intAmount = rand(1, 10);
	    $strQuest = 'F;'.$intId.';'.$intAmount;
	    break;
	  case 'I':
	  case 'B':
	  case 'P':
	    if ($strType == 'I')
	      {
		$objItems = $db->Execute("SELECT `id` FROM `equipment` WHERE `owner`=0");
		$intKey2 = rand(0, 4);
	      }
	    elseif ($strType == 'B')
	      {
		$objItems = $db->Execute("SELECT `id` FROM `bows` WHERE `type`='B'");
		$intKey2 = rand(0, 4);
	      }
	    else
	      {
		$objItems = $db->Execute("SELECT `id` FROM `potions` WHERE `owner`=0");
		$intKey2 = 0;
	      }
	    $arrItems = array();
	    while (!$objItems->EOF)
	      {
		$arrItems[] = $objItems->fields['id'];
		$objItems->MoveNext();
	      }
	    $objItems->Close();
	    $intKey = array_rand($arrItems);
	    $intId = $arrItems[$intKey];
	    $intAmount = rand(1, 10);
	    $strQuest = $strType.';'.$intId.';'.$intAmount.';'.$intKey2;
	    break;
	  case 'L':
	    $objMonsters = $db->Execute("SELECT `id` FROM `monsters` WHERE `location`='".ucfirst($strCity)."' AND `lootnames`!= ''");
	    $arrMonsters = array();
	    while (!$objMonsters->EOF)
	      {
		$arrMonsters[] = $objMonsters->fields['id'];
		$objMonsters->MoveNext();
	      }
	    $objMonsters->Close();
	    if (count($arrMonsters) > 0)
	      {
		$intKey = array_rand($arrMonsters);
		$intId = $arrMonsters[$intKey];
		$intPart = rand(0, 3);
		$strQuest = 'L;'.$intId.';'.$intPart;
	      }
	    else
	      {
		$strQuest = '';
	      }
	    break;
	  default:
	    break;
	  }
	$db->Execute("UPDATE `settings` SET `value`='".$strQuest."' WHERE `setting`='hunter".$strCity."'");
	$intAmount = rand(1, 20);
	$db->Execute("UPDATE `settings` SET `value`='".$intAmount."' WHERE `setting`='hunter".$strCity."amount'");
      }
    /**
     * Check for random event
     */
    $objRevent = $db->Execute("SELECT `pid`, `state`, `qtime` FROM `revent`");
    while (!$objRevent->EOF)
      {
	//Count down
	if ($objRevent->fields['qtime'] > 1)
	  {
	    $db->Execute("UPDATE `revent` SET `qtime`=`qtime`-1 WHERE `pid`=".$objRevent->fields['pid']);
	  }
	//Finish random event
	else
	  {
	    $time = date("Y-m-d H:i:s");
	    //Unfinished
	    if ($objRevent->fields['state'] == 2)
	      {
		$db->Execute("DELETE FROM `equipment` WHERE `name`='Solidna sakiewka' AND `type`='Q' AND `owner`=".$objRevent->fields['pid']);
	      }
	    //Finished - give item to target
	    elseif ($objRevent->fields['state'] == 3)
	      {
		$intGold = rand(1000, 8000);
		$db->Execute("UPDATE `players` SET `bank`=`bank`+".$intGold." WHERE `id`=".$objRevent->fields['pid']);
		$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objRevent->fields['pid'].", 'Dostałeś przelew ".$intGold." sztuk złota do banku, tytułem: Dziękuję za pomoc.', '".$time."', 'N')"); 
	      }
	    //Finished - item sold
	    elseif ($objRevent->fields['state'] == 4)
	      {
		$intRoll = rand(0, 1);
		//Go to prison
		if ($intRoll == 0)
		  {
		    $objPlayer = $db->Execute("SELECT `miejsce` FROM `players` WHERE `id`=".$objRevent->fields['pid']);
		    $intGold = rand(1, 100) * 10000;
		    if ($objPlayer->fields['miejsce'] != 'Lochy')
		      {
			$db -> Execute("INSERT INTO `jail` (`prisoner`, `verdict`, `duration`, `cost`, `data`) VALUES(".$objRevent->fields['pid'].",'Okradzenie poborcy podatkowego.', 18, ".$intGold.", ".$strDate.")");
			$db->Execute("UPDATE `players` SET `miejsce`='Lochy' WHERE `id`=".$objRevent->fields['pid']);
			$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objRevent->fields['pid'].", 'Nagle wyskoczył na ciebie patrol gwardzistów królewskich. Szybko i sprawnie zakuli ciebie w kajdany i odtransportowali do miasta. Tam przed sądem zostałeś oskarżony o kradzież pieniędzy podatników. I w ten oto sposób znalazłeś się w lochach.', '".$time."', 'T')"); 
		      }
		    else
		      {
			$db->Execute("UPDATE `jail` SET `duration`=`duration`+18 WHERE `prisoner`=".$objRevent->fields['pid']);
			$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objRevent->fields['pid'].", 'Zostałeś wywleczony z celi i postawiony przed sądem pod zarzutem okradzenia poborcy podatkowego. Po krótkiej rozprawie, sędzia wydłużył twój obecny wyrok.', '".$time."', 'T')");
		      }
		    $objPlayer->Close();
		  }
		//Bandits
		elseif ($intRoll == 1)
		{
		  $objPlayer = $db->Execute("SELECT `bank` FROM `players` WHERE `id`=".$objRevent->fields['pid']);
		  $intBank = floor($objPlayer->fields['bank'] / 2);
		  $objPlayer->Close();
		  $db->Execute("UPDATE `players` SET `hp`=0, `credits`=0, `bank`=".$intBank.", `lastkilledby`='własną chciwość' WHERE `id`=".$objRevent->fields['pid']);
		  $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objRevent->fields['pid'].", 'Nagle wokół ciebie pojawiło się kilka zakapturzonych postaci. Ostatnią rzeczą którą usłyszałeś było: ODDAWAJ NASZE PIENIĄDZE! Kiedy się obudziłeś, okazało się, że zniknęło nie tylko to złoto, które miałeś przy sobie ale również część złota z banku!', '".$time."', 'T')"); 
		}
	      }
	    //Finished - gave gold to old man
	    elseif ($objRevent->fields['state'] == 7)
	      {
		$objOutpost = $db->Execute("SELECT `id`, `barracks` FROM `outposts` WHERE `owner`=".$objRevent->fields['pid']);
		if ($objOutpost->fields['barracks'])
		  {
		    $objVeterans = $db->Execute("SELECT count(`id`) FROM `outpost_veterans` WHERE `outpost`=".$objOutpost->fields['id']);
		    if ($objVeterans->fields['count(`id`)'] < $objOutpost->fields['barracks'])
		      {
			$db->Execute("INSERT INTO outpost_veterans (`outpost`, `name`) VALUES(".$objOutpost->fields['id'].", 'Weteran nr ".$objVeterans->fields['count(`id`)']."')");
			$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$objRevent->fields['pid'].", 'Do Twojej strażnicy zgłosił się na służbę doświadczony weteran!', '".$time."', 'O')"); 
		      }
		    $objVeterans->Close();
		  }
		$objOutpost->Close();
	      }
	    $db->Execute("DELETE FROM `revent` WHERE `pid`=".$objRevent->fields['pid']);
	  }
	$objRevent->MoveNext();
      }
    $objRevent->Close();
    /**
     * Reopen game
     */
    $db -> Execute("UPDATE settings SET value='Y' WHERE setting='open'");
    $db -> Execute("UPDATE settings SET value='' WHERE setting='close_reason'");
}
 
/**
* Main reset of game
**/
function mainreset() 
{
    global $db;
    global $lang;
    global $gamename;

    $db -> Execute("UPDATE `settings` SET `value`='N' WHERE `setting`='open'");
    $db -> Execute("UPDATE `settings` SET `value`='Wykonywanie resetu' WHERE `setting`='close_reason'");
    $db -> Execute("UPDATE `players` SET `age`=`age`+1, `hp`=`max_hp`, `bridge`='N', `houserest`='N', `craftmission`=7");
    $db->Execute("UPDATE `players` SET `newbie`=`newbie`-1 WHERE `newbie`>0");
    $db->Execute("UPDATE `players` SET `trains`=`trains`+15 WHERE `corepass`='Y' AND `freeze`=0");
    $intCtime = (time() - 200);
    $db -> Execute("UPDATE players SET freeze=freeze-1, lpv=".$intCtime." WHERE freeze>0");
    $time = date("Y-m-d H:i:s");
    /**
     * Manage rooms
     */
    $db->Execute("UPDATE `rooms` SET `days`=`days`-1");
    $objRooms = $db->Execute("SELECT `id`, `days`, `owner`, `owners` FROM `rooms` WHERE `days`<2");
    while (!$objRooms->EOF)
      {
	if ($objRooms->fields['days'] == 0)
	  {
	    $db->Execute("UPDATE `players` SET `room`=0 WHERE `room`=".$objRooms->fields['id']);
	    $db->Execute("DELETE FROM `chatrooms` WHERE `room`=".$objRooms->fields['id']);
	  }
	else
	  {
	    $arrOwners = explode(';', $objRooms->fields['owners']);
	    if ($arrOwners[0] == '')
	      {
		$arrOwners[0] = $objRooms->fields['owner'];
	      }
	    else
	      {
		$arrOwners[] = $objRooms->fields['owner'];
	      }
	    foreach ($arrOwners as $intOwner)
	      {
		$db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$intOwner.", 'Niedługo zakończy się okres wynajmu Twojego pokoju.', '".$time."', 'E')") or die ($db->ErrorMsg());
	      }
	  }
	$objRooms->MoveNext();
      }
    $objRooms->Close();
    $db->Execute("DELETE FROM `rooms` WHERE `days`=0");
    $objRooms = $db->Execute("SELECT `id` FROM `rooms`");
    while (!$objRooms->EOF)
      {
	$objMsgs = $db->Execute("SELECT count(`id`) FROM `chatrooms` WHERE `room`=".$objRooms->fields['id']);
	if ($objMsgs->fields['count(`id`)'] > 1000)
	  {
	    $db->Execute("DELETE FROM `chatrooms` WHERE `room`=".$objRooms->fields['id']." ORDER BY `id` LIMIT ".($objMsgs->fields['count(`id`)'] - 1000)) or die($db->ErrorMsg());
	  }
	$objMsgs->Close();
	$objRooms->MoveNext();
      }
    $objRooms->Close();
    /**
     * Outposts taxes
     */
    $data = date("y-m-d");
    $strDate = $db -> DBDate($data);
    $intOutSize = 1;
    $outcost = $db -> Execute("SELECT `id`, `warriors`, `archers`, `catapults`, `gold`, `bcost`, `size`, `owner` FROM `outposts`");
    while (!$outcost->EOF) 
    {
        $cost = ($outcost -> fields['warriors'] * 7) + ($outcost -> fields['archers'] * 7) + ($outcost -> fields['catapults'] * 14);
        $query = $db -> Execute("SELECT count(*) FROM `outpost_monsters` WHERE `outpost`=".$outcost -> fields['id']);
        $nummonsters = $query -> fields['count(*)'];
        $query -> Close();
        $cost = $cost + ($nummonsters * 70);
        $query = $db -> Execute("SELECT count(*) FROM `outpost_veterans` WHERE `outpost`=".$outcost -> fields['id']);
        $numveterans = $query -> fields['count(*)'];
        $query -> Close();
        $cost = $cost + ($numveterans * 70);
        $bonus = ($cost * ($outcost -> fields['bcost'] / 100));
        $bonus = round($bonus, "0");
        $cost = $cost - $bonus;
	if ($outcost->fields['gold'] < ($cost * 2))
	  {
	    $time = date("Y-m-d H:i:s");
	    $db->Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES(".$outcost->fields['owner'].", 'Żołnierze w strażnicy niepokoją się. Może zabraknąć pieniędzy na kolejną wypłatę.', '".$time."', 'O')") or die ($db->ErrorMsg());
	  }
        if ($outcost -> fields['gold'] >= $cost) 
        {
            $tax = $outcost -> fields['gold'] - $cost;
        } 
            else 
        {
            $tax = 0;
            if ($outcost -> fields['gold']) 
            {
                $lost = (100 * ($cost - $outcost -> fields['gold'])) / $cost;
            } 
                else 
            {
                $lost = 1;
            }
            if (!$outcost -> fields['gold']) 
            {
                $arrlost[0] = $outcost -> fields['warriors'];
            } 
                else 
            {
                if ($outcost -> fields['warriors']) 
                {
                    $arrlost[0] = ceil($outcost -> fields['warriors'] * ($lost / 100));
                } 
                    else 
                {
                    $arrlost[0] = 0;
                }
            }
            if (!$outcost -> fields['gold']) 
            {
                $arrlost[1] = $outcost -> fields['archers'];
            } 
                else 
            {
                if ($outcost -> fields['archers']) 
                {
                    $arrlost[1] = ceil($outcost -> fields['archers'] * ($lost / 100));
                } 
                    else 
                {
                    $arrlost[1] = 0;
                }
            }
            if (!$outcost -> fields['gold']) 
            {
                $arrlost[2] = $outcost -> fields['catapults'];
            } 
                else 
            {
                if ($outcost -> fields['catapults']) 
                {
                    $arrlost[2] = ceil($outcost -> fields['catapults'] * ($lost / 100));
                } 
                    else 
                {
                    $arrlost[2] = 0;
                }
            }
            $arrtype = array('warriors','archers','catapults');
            for ($i = 0; $i < 3; $i++) 
            {
                $field = $arrtype[$i];
                $maxlost = $outcost -> fields[$field];
                if ($arrlost[$i] > $maxlost) 
                {
                    $arrlost[$i] = $maxlost;
                }
                $db -> Execute("UPDATE outposts SET ".$arrtype[$i]."=".$arrtype[$i]."-".$arrlost[$i]." WHERE id=".$outcost -> fields['id']);
            }
        }
        $db -> Execute("UPDATE outposts SET gold=".$tax." WHERE id=".$outcost -> fields['id']);
        if (!$outcost -> fields['warriors'] && !$outcost -> fields['archers'])
        {
            if ($outcost -> fields['size'] > 1)
            {
                $db -> Execute("UPDATE outposts SET size=size-1 WHERE id=".$outcost -> fields['id']);
            }
                else
            {
                $db -> Execute("DELETE FROM outposts WHERE id=".$outcost -> fields['id']);
                $db -> Execute("DELETE FROM outpost_monster WHERE outpost=".$outcost -> fields['id']);
                $db -> Execute("DELETE FROM outpost_veterans WHERE outpost=".$outcost -> fields['id']);
            }
        }
        $intOutSize = $intOutSize + $outcost -> fields['size'];
        $outcost -> MoveNext();
    }
    $outcost -> Close(); 
    $intMithcost = rand(100, 300);
    /**
    * Lenght of poll
    */
    $objPolls = $db -> Execute("SELECT id, days FROM polls WHERE votes=-1");
    while (!$objPolls -> EOF)
    {
        if ($objPolls -> fields['days'])
        {
            $intDays = $objPolls -> fields['days'] - 1;
            if ($intDays == 0)
            {
                $objQuery = $db -> Execute("SELECT id FROM players");
                $intMembers = $objQuery -> RecordCount();
                $objQuery -> Close();
                $db -> Execute("UPDATE polls SET members=".$intMembers." WHERE id=".$objPolls -> fields['id']);
                $db -> Execute("UPDATE settings SET value='N' WHERE setting='poll'");
            }
            $db -> Execute("UPDATE polls SET days=days-1 WHERE id=".$objPolls -> fields['id']);
        }
        $objPolls -> MoveNext();
    }
    $objPolls -> Close();
    /** 
     * Warehouse actions
     */
    $arrItems = array('copper', 'iron', 'coal', 'mithril', 'adamantium', 'meteor', 'crystal', 'illani', 'illanias', 'nutari', 'dynallca', 'copperore', 'zincore', 'tinore', 'ironore', 'bronze', 'brass', 'steel', 'pine', 'hazel', 'yew', 'elm', 'illani_seeds', 'illanias_seeds', 'nutari_seeds', 'dynallca_seeds');
     $objTest = $db -> Execute("SELECT reset FROM warehouse WHERE reset=10");
    /**
     * Check for caravans
     */
    if ($objTest -> fields['reset'])
    { 
        $objCaravanday = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='caravanday'");
        $intCaravanday = (int)$objCaravanday -> fields['value'];
        if ($intCaravanday == 10)
        {
            $intRoll = rand(1, 10);
            if ($intRoll == 10)
            {
                foreach ($arrItems as $strItem)
                {
                    $objAmount = $db -> Execute("SELECT `amount` FROM `warehouse` WHERE `mineral`='".$strItem."' AND `reset`=1") or die($db -> ErrorMsg());
                    $intPercent = rand(1, 100) / 100;
                    $intBuy = $objAmount -> fields['amount'] * $intPercent;
                    $intAmount = $objAmount -> fields['amount'] - $intBuy;
                    $objAmount -> Close();
                    $db -> Execute("UPDATE `warehouse` SET amount=".$intAmount.", `buy`=`buy`+".$intBuy." WHERE `mineral`='".$strItem."' AND `reset`=1");
                }
                $db -> Execute("UPDATE `settings` SET `value`='Y' WHERE `setting`='caravan'");
                $db -> Execute("UPDATE `settings` SET `value`='0' WHERE `setting`='caravanday'");
            }
                else
            {
                $db -> Execute("UPDATE `settings` SET `value`='N' WHERE `setting`='caravan'");
            }
        }
            else
        {
            $intCaravanday++;
            $db -> Execute("UPDATE `settings` SET `value`='".$intCaravanday."' WHERE `setting`='caravanday'");
            $db -> Execute("UPDATE `settings` SET `value`='N' WHERE `setting`='caravan'");
        }
    }
    /**
    * Count prices in warehouse
    */
    if (!$objTest -> fields['reset'])
    {
        $arrPricesmin = array(5, 15, 1, 15, 15, 300, 25, 25, 10, 25, 25, 2, 4, 6, 10, 14, 20, 4, 7, 10, 12, 10, 25, 10, 25, 25);
        $arrPricesmax = array(7, 25, 3, 25, 25, 400, 35, 35, 20, 35, 35, 4, 6, 8, 12, 18, 30, 6, 10, 15, 18, 20, 35, 20, 35, 35);
        for ($i = 0; $i < 26; $i++)
        {
            $intPrice = rand($arrPricesmin[$i], $arrPricesmax[$i]);
            $objReset = $db -> Execute("SELECT reset FROM warehouse WHERE mineral='".$arrItems[$i]."' AND reset=1");
            if ($objReset -> fields['reset'])
            {
                $objAmount = $db -> Execute("SELECT amount FROM warehouse WHERE mineral='".$arrItems[$i]."' AND reset=1");
                $intAmount = $objAmount -> fields['amount'];
                $objAmount -> Close();
                $db -> Execute("UPDATE warehouse SET reset=reset+1 WHERE mineral='".$arrItems[$i]."'");
            }
                else
            {
                $intAmount = 0;
            }
            $objReset -> Close();
            $db -> Execute("INSERT INTO warehouse (reset, mineral, cost, amount) VALUES (1, '".$arrItems[$i]."', ".$intPrice.", ".$intAmount.")");
            $db -> Execute("UPDATE settings SET value='".$intPrice."' WHERE setting='".$arrItems[$i]."'") or die($db -> ErrorMsg());
        }
    }
        else
    {
        $arrPrice = array(0.14, 0.13, 0.12, 0.11, 0.1, 0.1, 0.09, 0.08, 0.07, 0.06);
        $arrCost = array();
        $arrSell = array();
        $arrBuy = array();
        $arrSell2 = array();
        $arrBuy2 = array();
        /**
        * Count price for each mineral, herb
        */
        for ($i = 0; $i < 26; $i++)
        {
            $objMineral = $db -> Execute("SELECT cost, sell, buy FROM warehouse WHERE mineral='".$arrItems[$i]."' ORDER BY reset ASC");
            $j = 0;
            while (!$objMineral -> EOF)
            {
                $arrCost[$j] = $objMineral -> fields['cost'];
                $arrSell[$j] = $objMineral -> fields['sell'];
                $arrBuy[$j] = $objMineral -> fields['buy'];
                $j++ ;
                $objMineral -> MoveNext();
            }
            $objMineral -> Close();
            $intSummarysell = 0;
            $intSummarybuy = 0;
            for ($j = 0; $j < 10; $j++)
            {
                $intSummarysell = $intSummarysell + $arrSell[$j];
                $intSummarybuy = $intSummarybuy + $arrBuy[$j];
            }
            for ($j = 0; $j < 10; $j++)
            {
                if ($intSummarysell || $intSummarybuy)
                {
                    $arrSell2[$j] = $arrSell[$j] / ($intSummarysell + $intSummarybuy);
                    $arrBuy2[$j] = $arrBuy[$j] / ($intSummarysell + $intSummarybuy);
                }
                    else
                {
                    $arrBuy2[$j] = $arrBuy[$j];
                    $arrSell2[$j] = $arrSell[$j];
                }
            }
            $intPrice = 0;
            for ($j = 0; $j < 10; $j++)
            {
                $intPrice = $intPrice + (($arrBuy2[$j] - $arrSell2[$j] + 1) * $arrCost[$j] * $arrPrice[$j]);
            }
            $objAmount = $db -> Execute("SELECT amount FROM warehouse WHERE mineral='".$arrItems[$i]."' AND reset=1");
            $intAmount = $objAmount -> fields['amount'];
            $objAmount -> Close();
            if ($intAmount < 1)
            {
                $objMineral2 = $db -> Execute("SELECT cost FROM warehouse WHERE mineral='".$arrItems[$i]."' AND reset=1");
                $intPrice = $objMineral2 -> fields['cost'] + 1;
                $objMineral2 -> Close();
            }
            $db -> Execute("DELETE FROM warehouse WHERE mineral='".$arrItems[$i]."' AND reset=10");
            $db -> Execute("UPDATE warehouse SET reset=reset+1 WHERE mineral='".$arrItems[$i]."'");
            $db -> Execute("INSERT INTO warehouse (reset, mineral, cost, amount) VALUES (1, '".$arrItems[$i]."', ".$intPrice.", ".$intAmount.")");
            $intPrice = ceil($intPrice);
            $db -> Execute("UPDATE settings SET value='".$intPrice."' WHERE setting='".$arrItems[$i]."'") or die($db -> ErrorMsg());
        }
    }
    $objTest -> Close();
    /**
    * Add game age
    */
    $objDay = $db -> Execute("SELECT value FROM settings WHERE setting='day'");
    $intDay = $objDay -> fields['value'] + 1;
    $objDay -> Close();
    $db -> Execute("UPDATE settings SET value='".$intDay."' WHERE setting='day'");
    /**
    * Count amount minerals in mines
    */
    $objSearchmin = $db -> Execute("SELECT `player`, `days`, `mineral`, `searchdays` FROM `mines_search`");
    $arrMinerals = array('coal' => 0.75,
                         'copper' => 1, 
                         'zinc' => 2, 
                         'tin' => 3, 
                         'iron' => 4);
    $arrMineso = array();
    $arrMinesc = array();
    $strSql = "INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES";
    $time = date("Y-m-d H:i:s");
    $blnBack = FALSE;
    while (!$objSearchmin -> EOF)
    {
        $intDays = $objSearchmin -> fields['days'] - 1;
        if (!$intDays)
        {
            $strMinname = $objSearchmin -> fields['mineral'];
            if ($objSearchmin -> fields['searchdays'] == 3)
            {
                $intAmount2 = 3000;
            }
            if ($objSearchmin -> fields['searchdays'] == 2)
            {
                $intAmount2 = 1500;
            }
            if ($objSearchmin -> fields['searchdays'] == 1)
            {
                $intAmount2 = 500;
            }
            $intAmount = ceil(rand(1, 10) * ($intAmount2 / $arrMinerals[$strMinname]));
            $objTest = $db -> Execute("SELECT owner FROM mines WHERE owner=".$objSearchmin -> fields['player']);
            if ($objTest -> fields['owner'])
            {
                $db -> Execute("UPDATE mines SET ".$strMinname."=".$strMinname."+".$intAmount." WHERE owner=".$objSearchmin -> fields['player']);
            }
                else
            {
              $db -> Execute("INSERT INTO mines (owner, ".$strMinname.") VALUES(".$objSearchmin -> fields['player'].", ".$intAmount.")");
            }
            $objTest -> Close();
            $db -> Execute("DELETE FROM mines_search WHERE player=".$objSearchmin -> fields['player']);
	    $strSql .= "(".$objSearchmin->fields['player'].", 'Twój geolog wrócił już z poszukiwania minerałów.', '".$time."', 'I'),";
	    $blnBack = TRUE;
        }
	else
	  {
	    $arrMinesc[] = $objSearchmin->fields['player'];
	  }
        $objSearchmin -> MoveNext();
    }
    $objSearchmin -> Close();
    if ($blnBack)
      {
	$strSql = rtrim($strSql, ',').';';
	$db->Execute($strSql) or die($db->ErrorMsg());
      }
    if (count($arrMinesc) > 0)
      {
	$db->Execute("UPDATE `mines_search` SET `days`=`days`-1 WHERE `player` IN (".implode(',', $arrMinesc).")");
      }
    /**
     * Show new news
     */
    $db -> Execute("UPDATE news SET `show`='Y', `pdate`=".$strDate." WHERE `show`='N' AND `added`='Y' ORDER BY `id` ASC LIMIT 1");
    /**
     * Astral machine
     */
    $objTest = $db -> Execute("SELECT `value` FROM `settings` WHERE `setting`='tribe'");
    if (!$objTest -> fields['value'])
    {
        $objAstral = $db -> Execute("SELECT `owner`, `used`, `directed` FROM `astral_machine` WHERE `aviable`='Y'");
        while (!$objAstral -> EOF)
        {
            $intAstralenergy = $objAstral -> fields['used'] + $objAstral -> fields['directed'];
            if ($intAstralenergy >= 10000)
            {
                $objName = $db -> Execute("SELECT `id`, `name`, `owner` FROM `tribes` WHERE `id`=".$objAstral -> fields['owner']);
		$objOwner = $db->Execute("SELECT `user` FROM `players` WHERE `id`=".$objName->fields['owner']);
                $time = date("H:i:s");
                $hour = explode(":", $time);
                $day = explode("-",$data);
                $newhour = $hour[0];
                if ($newhour > 23) 
                {
                    $newhour = $newhour - 24;
                    $day[2] = $day[2]+1;
                }
                $arrtime = array($newhour, $hour[1], $hour[2]);
                $arrdate = array($day[0], $day[1], $day[2]);
                $newtime = implode(":",$arrtime);
                $newdata = implode("-",$arrdate);
                $arrtemp = array($newdata, $newtime);
                $newdate = implode(" ",$arrtemp);
		$db -> Execute("INSERT INTO `updates` (`starter`, `title`, `updates`, `lang`, `time`) VALUES('(Herold)','Astralna machina','I tak oto na ".$gamename." pojawiła się nowa konstrukcja - Astralna Machina.<br />Władze ".$gamename." pragną poinformować swoich poddanych iż dziś (".$newdate.") ową konstrukcję dla własnej chwały wybudował klan <b>".$objName -> fields['name']."</b>.<br /> Sława bohaterom!','".$lang."', ".$strDate.")");
		$objEra = $db->Execute("SELECT `value` FROM `settings` WHERE `setting`='age'");
		$db->Execute("INSERT INTO `halloffame2` (`tribe`, `leader`, `bdate`) VALUES('".$objName->fields['name']." ID:".$objName->fields['id']."', '".$objOwner->fields['user']." ID:".$objName->fields['owner']."', '".$intDay.", ".$objEra->fields['value']."')") or die($db->ErrorMsg());
		$objEra->Close();
                $db -> Execute("UPDATE `settings` SET `value`='".$objAstral -> fields['owner']."' WHERE `setting`='tribe'");
                $arrComponents = array('C', 'O', 'T');
                $arrAmount = array(array(8, 8, 6, 6, 4, 4, 2),
                                   array(10, 8, 6, 4, 2),
                                   array(10, 8, 6, 4, 2));
                for ($i = 0; $i < 3; $i++)
                {
                    $j = 0;
                    foreach ($arrAmount[$i] as $intAmount)
                    {
                        $strName = $arrComponents[$i].$j;
                        $objAmount = $db -> Execute("SELECT `amount` FROM `astral` WHERE `owner`=".$objAstral -> fields['owner']." AND `type`='".$strName."' AND `number`=0 AND `location`='C'");
                        if ($objAmount -> fields['amount'] == $intAmount)
                        {
                            $db -> Execute("DELETE FROM `astral` WHERE `owner`=".$objAstral -> fields['owner']." AND `type`='".$strName."' AND `number`=0 AND `location`='C'");
                        }
                            else
                        {
                            $db -> Execute("UPDATE `astral` SET `amount`=`amount`-".$intAmount." WHERE `owner`=".$objAstral -> fields['owner']." AND `type`='".$strName."' AND `number`=0 AND `location`='C'");
                        }
                        $j ++;
                    }
                }
                $db -> Execute("UPDATE `astral_machine` SET `used`=".$intAstralenergy.", `directed`=0 WHERE `owner`=".$objAstral -> fields['owner']);
                break;
            }
            $db -> Execute("UPDATE `astral_machine` SET `used`=".$intAstralenergy.", `directed`=0 WHERE `owner`=".$objAstral -> fields['owner']);
            $objAstral -> MoveNext();
        }
        $objAstral -> Close();
    }
    $objTest -> Close();
    /**
     * Agents payment
     */
    $objTribes = $db->Execute("SELECT `id`, `credits`, `agents` FROM `tribes` WHERE `agents`>0");
    while (!$objTribes->EOF)
      {
	$intPayment = $objTribes->fields['agents'] * 1000;
	//Everything ok, just pay to agents
	if ($objTribes->fields['credits'] >= $intPayment)
	  {
	    $db->Execute("UPDATE `tribes` SET `credits`=`credits`-".$intPayment." WHERE `id`=".$objTribes->fields['id']);
	  }
	//Not enough moneys for payment, some agents retreats
	else
	  {
	    $intPercent = $objTribes->fields['credits'] / $intPayment;
	    $intAgents = floor($objTribes->fields['agents'] * $intPercent);
	    $db->Execute("UPDATE `tribes` SET `credits`=0, `agents`=".$intAgents." WHERE `id`=".$objTribes->fields['id']);
	  }
	$objTribes->MoveNext();
      }
    $objTribes->Close();
    smallreset();
}
 
?>
