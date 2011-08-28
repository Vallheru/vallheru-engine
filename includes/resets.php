<?php
/**
 *   File functions:
 *   Resets in game - (mainreset) main reset and (smallreset) other resets
 *
 *   @name                 : resets.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 28.08.2011
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
 * Check avaible languages
 */    
$arrLanguage = scandir('languages/', 1);
$arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));
 
/**
* Main reset of game
**/
function mainreset() 
{
    global $db;
    global $arrLanguage;

    $db -> Execute("UPDATE `settings` SET `value`='N' WHERE `setting`='open'");
    $db -> Execute("UPDATE `settings` SET `value`='Wykonywanie resetu' WHERE `setting`='close_reason'");
    $db -> Execute("TRUNCATE TABLE `events`");
    $db -> Execute("UPDATE `farm` SET `age`=`age`+1");
    $db -> Execute("DELETE FROM `farm` WHERE `age`>26");
    $db -> Execute("UPDATE `players` SET `age`=`age`+1, `hp`=`max_hp`, `bridge`='N', `houserest`='N'");
    /**
     * Add bonus to mana from items and check for duration of antidote
     */
    $arrRings = array();
    $intLangs = 0;
    foreach ($arrLanguage as $strLanguage)
    {
         require_once("languages/".$strLanguage."/resets.php");
         $arrRings[] = R_INT;
         $arrRings[] = R_WIS;
         $intLangs ++;
    }
    $arrStat = array('inteli', 'wisdom');
    $objStats = $db -> Execute("SELECT `id`, `inteli`, `wisdom`, `antidote` FROM `players`");
    while (!$objStats -> EOF)
    {
        $objRings = $db -> Execute("SELECT `power`, `name` FROM `equipment` WHERE `type`='I' AND `status`='E' AND `owner`=".$objStats -> fields['id']);
        while (!$objRings -> EOF)
        {
            $arrRingtype = explode(" ", $objRings -> fields['name']);
            $intAmount = count($arrRingtype) - 1;
            $intKey = array_search($arrRingtype[$intAmount], $arrRings);
            if ($intKey !== NULL)
            {
                $intKey = $intKey / $intLangs;
                $strStat = $arrStat[$intKey];
                $objStats -> fields[$strStat] = $objStats -> fields[$strStat] + $objRings -> fields['power'];
            }
            $objRings -> MoveNext();
        }
        $objRings -> Close();
        $objCape = $db -> Execute("SELECT `power` FROM `equipment` WHERE `type`='C' AND `status`='E' AND `owner`=".$objStats -> fields['id']);
        $intMaxmana = $objStats -> fields['inteli'] + $objStats -> fields['wisdom'];
        $intMaxmana = $intMaxmana + (($objCape -> fields['power'] / 100) * $intMaxmana);
        $objCape -> Close();
        if (!empty($objStats -> fields['antidote']))
        {
            $intAntidote = (int)$objStats -> fields['antidote']{1} + 1;
            if ($intAntidote == 10)
            {
                $strAntidote = '';
            }
                else
            {
                $strAntidote = $objStats -> fields['antidote']{0}.$intAntidote;
            }
        }
            else
        {
            $strAntidote = '';
        }
        $db -> Execute("UPDATE `players` SET `pm`=".$intMaxmana.", `antidote`='".$strAntidote."' WHERE `id`=".$objStats -> fields['id']);
        $objStats -> MoveNext();
        
    }
    $objStats -> Close();
    $db -> Execute("UPDATE players SET energy=energy+max_energy WHERE miejsce!='Lochy' AND freeze=0 AND rasa!='' AND klasa!=''");
    $db -> Execute("UPDATE `players` SET `crime`=`crime`+1, `astralcrime`='Y' WHERE `klasa`='Złodziej' AND `freeze`=0");
    $intCtime = (time() - 200);
    $db -> Execute("UPDATE players SET freeze=freeze-1, lpv=".$intCtime." WHERE freeze>0");
    $db -> Execute("UPDATE outposts SET turns=turns+2, fatigue=100, attacks=0");
    $objItemname = $db -> Execute("SELECT name, id FROM equipment WHERE poison>0");
    while (!$objItemname -> EOF)
    {
        $strName = str_replace("Zatruty ", "", $objItemname -> fields['name']);
        $db -> Execute("UPDATE equipment SET name='".$strName."' WHERE id=".$objItemname -> fields['id']);
        $objItemname -> MoveNext();
    }
    $objItemname -> Close();
    $db -> Execute("UPDATE equipment SET poison=0, ptype='' WHERE poison>0");
    /**
     * Outposts taxes
     */
    $intOutSize = 1;
    $outcost = $db -> Execute("SELECT `id`, `warriors`, `archers`, `catapults`, `gold`, `bcost`, `size` FROM `outposts`");
    while (!$outcost -> EOF) 
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
    $db -> Execute("UPDATE houses SET points=points+2");
    $intMithcost = rand(100, 300);
    /**
    * Count aviable army in outposts
    */
    $intMaxtroops = $intOutSize * 20;
    $intMintroops = $intOutSize * 15;
    $intMaxspecials = $intOutSize * 10;
    $intMinspecials = $intOutSize * 7;
    $intWarriors = rand($intMintroops, $intMaxtroops);
    $intArchers = rand($intMintroops, $intMaxtroops);
    $intCatapults = rand($intMinspecials, $intMaxspecials);
    $intBarricades = rand($intMinspecials, $intMaxspecials);
    $itemid = $db -> Execute("SELECT `id` FROM `potions` WHERE `owner`=0");
    while (!$itemid -> EOF) 
    {
        $amount = rand(1, 50);
        $db -> Execute("UPDATE `potions` SET `amount`=".$amount." WHERE `id`=".$itemid -> fields['id']);
        $itemid -> MoveNext();
    }
    $itemid -> Close();
    $db -> Execute("UPDATE settings SET value='20' WHERE setting='maps'");
    $db -> Execute("UPDATE settings SET value='".$intWarriors."' WHERE setting='warriors'");
    $db -> Execute("UPDATE settings SET value='".$intArchers."' WHERE setting='archers'");
    $db -> Execute("UPDATE settings SET value='".$intCatapults."' WHERE setting='catapults'");
    $db -> Execute("UPDATE settings SET value='".$intBarricades."' WHERE setting='barricades'");
    $db -> Execute("UPDATE players SET trains=trains+15 WHERE corepass='Y' AND freeze=0");
    $db -> Execute("UPDATE tribes SET atak='N'");
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
        }
            else
        {
            $db -> Execute("UPDATE mines_search SET days=days-1 WHERE player=".$objSearchmin -> fields['player']);
        }
        $objSearchmin -> MoveNext();
    }
    $objSearchmin -> Close();
    /**
     * Show new news
     */
    $db -> Execute("UPDATE news SET `show`='Y' WHERE `show`='N' AND `added`='Y' ORDER BY `id` ASC LIMIT 1");
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
                $objName = $db -> Execute("SELECT `name` FROM `tribes` WHERE `id`=".$objAstral -> fields['owner']);
                $time = date("H:i:s");
                $data = date("y-m-d");
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
                foreach ($arrLanguage as $strLanguage)
                {
                    require_once("languages/".$strLanguage."/resets.php");
                    $data = date("y-m-d");
                    $strDate = $db -> DBDate($data);
                    $db -> Execute("INSERT INTO `updates` (`starter`, `title`, `updates`, `lang`, `time`) VALUES('(Herold)','".U_TITLE."','".U_TEXT.$gamename.U_TEXT2.$gamename.U_TEXT3.$newdate.U_TEXT4.$objName -> fields['name'].U_TEXT5."','".$strLanguage."', ".$strDate.")");
                }
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
    * Reopen game
    */
    $db -> Execute("UPDATE settings SET value='Y' WHERE setting='open'");
    $db -> Execute("UPDATE settings SET value='' WHERE setting='close_reason'");
}

/**
* Other reset in this same day
*/
function smallreset() 
{
    global $db;
    global $arrLanguage;
    $db -> Execute("UPDATE settings SET value='N' WHERE setting='open'");
    $db -> Execute("UPDATE settings SET value='Wykonywanie resetu' WHERE setting='close_reason'");
    $db -> Execute("TRUNCATE TABLE events");
    /**
     * Grow herbs
     */
    $db -> Execute("UPDATE farm SET age=age+1");
    $db -> Execute("DELETE FROM farm WHERE age>26");
    $itemid = $db -> Execute("SELECT `id` FROM `potions` WHERE `owner`=0");
    while (!$itemid -> EOF) 
    {
        $amount = rand(1, 50);
        $db -> Execute("UPDATE `potions` SET `amount`=".$amount." WHERE `id`=".$itemid -> fields['id']);
        $itemid -> MoveNext();
    }
    $itemid -> Close();
    /**
    * Count aviable army in outposts
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
    $db -> Execute("UPDATE `players` SET `hp`=`max_hp`, `bridge`='N', `houserest`='N'");
    /**
     * Add bonus to mana from items and check duration of antidote
     */
    $arrRings = array();
    $intLangs = 0;
    foreach ($arrLanguage as $strLanguage)
    {
         require_once("languages/".$strLanguage."/resets.php");
         $arrRings[] = R_INT;
         $arrRings[] = R_WIS;
         $intLangs ++;
    }
    $arrStat = array('inteli', 'wisdom');
    $objStats = $db -> Execute("SELECT `id`, `inteli`, `wisdom`, `antidote` FROM `players`");
    while (!$objStats -> EOF)
    {
        $objRings = $db -> Execute("SELECT `power`, `name` FROM `equipment` WHERE `type`='I' AND `status`='E' AND `owner`=".$objStats -> fields['id']);
        while (!$objRings -> EOF)
        {
            $arrRingtype = explode(" ", $objRings -> fields['name']);
            $intAmount = count($arrRingtype) - 1;
            $intKey = array_search($arrRingtype[$intAmount], $arrRings);
            if ($intKey !== NULL)
            {
                $intKey = $intKey / $intLangs;
                $strStat = $arrStat[$intKey];
                $objStats -> fields[$strStat] = $objStats -> fields[$strStat] + $objRings -> fields['power'];
            }
            $objRings -> MoveNext();
        }
        $objRings -> Close();
        $objCape = $db -> Execute("SELECT `power` FROM `equipment` WHERE `type`='C' AND `status`='E' AND `owner`=".$objStats -> fields['id']);
        $intMaxmana = $objStats -> fields['inteli'] + $objStats -> fields['wisdom'];
        $intMaxmana = $intMaxmana + (($objCape -> fields['power'] / 100) * $intMaxmana);
        $objCape -> Close();
        if (!empty($objStats -> fields['antidote']))
        {
            $intAntidote = (int)$objStats -> fields['antidote']{1} + 1;
            if ($intAntidote == 10)
            {
                $strAntidote = '';
            }
                else
            {
                $strAntidote = $objStats -> fields['antidote']{0}.$intAntidote;
            }
        }
            else
        {
            $strAntidote = '';
        }
        $db -> Execute("UPDATE `players` SET `pm`=".$intMaxmana.", `antidote`='".$strAntidote."' WHERE `id`=".$objStats -> fields['id']);
        $objStats -> MoveNext();
        
    }
    $objStats -> Close();
    $db -> Execute("UPDATE `players` SET `crime`=`crime`+1, `astralcrime`='Y' WHERE `klasa`='Złodziej' AND `freeze`=0");  
    $db -> Execute("UPDATE players SET energy=energy+max_energy WHERE miejsce!='Lochy' AND freeze=0 AND rasa!='' AND klasa!=''");
    $db -> Execute("UPDATE outposts SET turns=turns+2, fatigue=100, attacks=0");
    $db -> Execute("UPDATE tribes SET atak='N'");
    $db -> Execute("UPDATE houses SET points=points+2");
    $objItemname = $db -> Execute("SELECT name, id FROM equipment WHERE poison>0");
    while (!$objItemname -> EOF)
    {
        $strName = str_replace("Zatruty ", "", $objItemname -> fields['name']);
        $db -> Execute("UPDATE equipment SET name='".$strName."' WHERE id=".$objItemname -> fields['id']);
        $objItemname -> MoveNext();
    }
    $objItemname -> Close();
    $db -> Execute("UPDATE equipment SET poison=0, ptype='' WHERE poison>0");
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
     * Reopen game
     */
    $db -> Execute("UPDATE settings SET value='Y' WHERE setting='open'");
    $db -> Execute("UPDATE settings SET value='' WHERE setting='close_reason'");
}
 
?>
