<?php
/**
 *   Funkcje pliku:
 *   Spell book - activation and deactivafion of spells and echance items
 *
 *   @name                 : czary.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 30.10.2006
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

$title = "Księga czarów"; 
require_once("includes/head.php");
require_once("includes/checkexp.php");

/**
* Get the localization for game
*/
require_once("languages/".$player -> lang."/czary.php");

/**
* Assign variable to template
*/
$smarty -> assign("Message", '');

$czarb = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND status='E' AND typ='B'");
if ($czarb -> fields['id']) 
{
    $smarty -> assign ("Battle", B_SPELL.$czarb -> fields['nazwa']." (+".$czarb -> fields['obr']." x ".B_DAMAGE.") | <a href=czary.php?deakt=".$czarb -> fields['id'].">".S_DEACTIV."</a><br />");
} 
    else 
{
    $smarty -> assign ("Battle", B_SPELL.S_NONE."<br />");
}
$czarb -> Close();

$czaro = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND status='E' AND typ='O'");
if ($czaro -> fields['id']) 
{
    $smarty -> assign ("Defence", D_SPELL.$czaro -> fields['nazwa']." (+".$czaro -> fields['obr']." x ".D_DEFENSE.") | <a href=czary.php?deakt=".$czaro -> fields['id'].">".S_DEACTIV."</a><br />");
} 
    else 
{
    $smarty -> assign ("Defence", D_SPELL.S_NONE."<br />");
}
$czaro -> Close();

if (isset($_GET['deakt'])) 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['deakt'])) 
    {
        error (ERROR);
    }
    $czary1 = $db -> Execute("SELECT * FROM czary WHERE id=".$_GET['deakt']);
    if (!$czary1 -> fields['id']) 
    {
        error (NO_SPELL);
    }

    if ($player -> id != $czary1 -> fields['gracz']) 
    {
        error (NOT_YOUR);
    }
    $db -> Execute("UPDATE czary SET status='U' WHERE id=".$czary1 -> fields['id']);
    $czary1 -> Close();
}

$arrname1 = array();
$arrpower1 = array();
$arrid1 = array();
$i = 0;
$czary = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND status='U' AND typ='B'");
while (!$czary -> EOF) 
{
    $arrname1[$i] = $czary -> fields['nazwa'];
    $arrpower1[$i] = $czary -> fields['obr'];
    $arrid1[$i] = $czary -> fields['id'];
    $czary -> MoveNext();
    $i = $i + 1;
}
$czary -> Close();

$arrname2 = array();
$arrpower2 = array();
$arrid2 = array();
$i = 0;
$czaryo = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND status='U' AND typ='O'");
while (!$czaryo -> EOF) 
{
    $arrname2[$i] = $czaryo -> fields['nazwa'];
    $arrpower2[$i] = $czaryo -> fields['obr'];
    $arrid2[$i] = $czaryo -> fields['id'];
    $czaryo -> MoveNext();
    $i = $i + 1;
}
$czaryo -> Close();


$arrname3 = array();
$arrefect = array();
$arrid3 = array();
$i = 0;
$czaryu = $db -> Execute("SELECT * FROM czary WHERE gracz=".$player -> id." AND typ='U'");
while (!$czaryu -> EOF) 
{
    if ($czaryu -> fields['nazwa'] == E_SPELL1) 
    {
        $arrefect[$i] = S_EFECT1;
    }
    if ($czaryu -> fields['nazwa'] == E_SPELL2) 
    {
        $arrefect[$i] = S_EFECT2;
    }
    if ($czaryu -> fields['nazwa'] == E_SPELL3) 
    {
        $arrefect[$i] = S_EFECT3;
    }
    $arrname3[$i] = $czaryu -> fields['nazwa'];
    $arrid3[$i] = $czaryu -> fields['id'];
    $czaryu -> MoveNext();
    $i = $i + 1;
}
$czaryu -> Close();

/**
* Enchace items
*/
if (isset($_GET['cast'])) 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['cast'])) 
    {
        error (ERROR);
    }
    $czary = $db -> Execute("SELECT * FROM czary WHERE id=".$_GET['cast']);
    if (!$czary -> fields['id']) 
    {
        error (NO_SPELL);
    }
    if ($player -> id != $czary -> fields['gracz']) 
    {
        error (NOT_YOUR);
    }
    if ($player -> level < $czary -> fields['poziom']) 
    {
        error (TO_LOW_L);
    }
    if ($player -> mana < $czary -> fields['poziom']) 
    {
        error (NO_MANA);
    }
    if ($player -> clas == 'Barbarzyńca') 
    {
        error (YOU_BARBARIAN);
    }

    $arriname = array();
    $arriamount = array();
    $arriid = array();
    $i = 0;
    $arritem = $db -> Execute("SELECT `name`, `id`, `amount` FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='U' AND `magic`='N' AND `type`!='I'");
    while (!$arritem -> EOF) 
    {
        $arriname[$i] = $arritem -> fields['name'];
        $arriamount[$i] = $arritem -> fields['amount'];
        $arriid[$i] = $arritem -> fields['id'];
        $arritem -> MoveNext();
        $i = $i + 1;
    }
    $arritem -> Close();
    if (isset($_GET['step']) && $_GET['step'] == 'items') 
    {
        if (!isset($_POST['item']) || !ereg("^[1-9][0-9]*$", $_POST['item'])) 
        {
            error (ERROR);
        }
        $arritem = $db -> Execute("SELECT * FROM `equipment` WHERE id=".$_POST['item']);
        $arrPrefixname = array(I_DRAGON, I_DRAGON2, I_DRAGON3, I_ELVES, I_ELVES2, I_ELVES3, I_DWARVES, I_DWARVES2, I_DWARVES3);
        $arrSurfixname = array(I_COPPER, I_BRONZE, I_BRASS, I_IRON, I_STEEL, I_HAZEL, I_YEW, I_ELM, I_HARDER, I_COMPOSITE);
        $strName = $arritem -> fields['name'];
        foreach ($arrPrefixname as $strPrefixname)
        {
            $strName = str_replace($strPrefixname, "", $strName);
        }
        foreach ($arrSurfixname as $strSurfixname)
        {
            $strName = str_replace($strSurfixname, "", $strName);
        }
        if ($arritem -> fields['type'] != 'R' && $arritem -> fields['type'] != 'B')
        {
            $strName = $strName.I_COPPER;
            $objBonus = $db -> Execute("SELECT `power`, `maxwt`, `szyb`, `zr` FROM `equipment` WHERE `owner`=0 AND `name`='".$strName."'");
        }
            else
        {
            if ($arritem -> fields['type'] == 'B')
            {
                $strName = $strName.I_HAZEL;
            }
            $objBonus = $db -> Execute("SELECT `power`, `maxwt`, `szyb`, `zr` FROM `bows` WHERE `name`='".$strName."'");
        }
        if (!$arritem -> fields['id']) 
        {
            error (NO_ITEM);
        }
        if ($arritem -> fields['owner'] != $player -> id) 
        {
            error (NOT_YOUR);
        }
        if ($arritem -> fields['magic'] == 'Y') 
        {
            error (IS_MAGIC);
        }
        $arrspell = $db -> Execute("SELECT nazwa, poziom FROM czary WHERE nazwa='".$_POST['spell']."' AND gracz=".$player -> id);
        if ($player -> energy < $arrspell -> fields['poziom']) 
        {
            error (NO_ENERGY);
        }
        $arrType1 = array('W', 'H', 'B');
        $arrType2 = array('A', 'S');
        $arrType3 = array('L', 'R');
        if (in_array($arritem -> fields['type'], $arrType1)) 
        {
            $name = "Magiczny ".$arritem -> fields['name'];
        } 
            elseif (in_array($arritem -> fields['type'], $arrType2)) 
        {
            $name = "Magiczna ".$arritem -> fields['name'];
        } 
            elseif (in_array($arritem -> fields['type'], $arrType3)) 
        {
            $name = "Magiczne ".$arritem -> fields['name'];
        } 
            else 
        {
            error (NO_ENCHANCE);
        }

        /**
         * Add bless
         */
        $objBless = $db -> Execute("SELECT `bless`, `blessval` FROM `players` WHERE `id`=".$player -> id);
        if ($objBless -> fields['bless'] == 'cast')
        {
            $player -> magic = $player -> magic + $objBless -> fields['blessval'];
            $db -> Execute("UPDATE `players` SET `bless`='', `blessval`=0 WHERE `id`=".$player -> id);
        }
        $objBless -> Close();

        $chance = ($player -> magic - $arritem -> fields['minlev'] - $arrspell -> fields['poziom'] + rand(1,100));
        $bonus = ceil($player -> magic / $arrspell -> fields['poziom']);

        $magic = ($arrspell -> fields['poziom'] / 100);
        if ($arrspell -> fields['nazwa'] == E_SPELL1) 
        {
            if ($arritem -> fields['type'] == 'B') 
            {
                error (NOT_ABLE1);
            }
            if ($chance > 100) 
            {
                $maxbonus = ($objBonus -> fields['power'] * 5);
                if ($bonus > $maxbonus) 
                {
                    $bonus = $maxbonus;
                }
                $power = $arritem -> fields['power'] + $bonus;
                $smarty -> assign ("Message", YOU_RISE.$arritem -> fields['name'].FOR_A.$bonus.NOW_IS.$bonus.S_EXP.$magic.S_CAST);
                $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND wt=".$arritem -> fields['wt']." AND type='".$arritem -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$power." AND zr=".$arritem -> fields['zr']." AND szyb=".$arritem -> fields['szyb']." AND maxwt=".$arritem -> fields['maxwt']." AND poison=".$arritem -> fields['poison']." AND ptype='".$arritem -> fields['ptype']."' AND magic='Y' AND repair=".$arritem -> fields['repair']);
                if (!$test -> fields['id']) 
                {
                    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, ptype, twohand, repair) VALUES(".$player -> id.",'".$name."',".$power.",'".$arritem -> fields['type']."',".$arritem -> fields['cost'].",".$arritem -> fields['zr'].",".$arritem -> fields['wt'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",1,'Y',".$arritem -> fields['poison'].",".$arritem -> fields['szyb'].",'".$arritem -> fields['ptype']."', '".$arritem -> fields['twohand']."', ".$arritem -> fields['repair'].")") or error(E_DB);
                } 
                    else 
                {
                    if ($arritem -> fields['type'] != 'R')
                    {
                        $db -> Execute("UPDATE `equipment` SET `amount`=`amount`+1 WHERE `id`=".$test -> fields['id']);
                    }
                        else
                    {
                        $db -> Execute("UPDATE `equipment` SET `wt`=`wt`+".$arritem -> fields['wt']." WHERE `id`=".$test -> fields['id']);
                    }
                }
                $test -> Close();
                checkexp($player -> exp,$bonus,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'magia',$magic);
            } 
                else 
            {
                $db -> Execute("UPDATE players SET magia=magia+0.01 WHERE id=".$player -> id);
                $smarty -> assign ("Message", YOU_TRY.$arritem -> fields['name'].BUT_FAIL);
            }
        }
        if ($arrspell -> fields['nazwa'] == E_SPELL2) 
        {
            if ($arritem -> fields['type'] == 'R') 
            {
                error (NOT_ABLE2);
            }
            if ($chance > 100) 
            {
                $maxbonus = ($objBonus -> fields['maxwt'] * 5);
                if ($bonus > $maxbonus) 
                {
                    $bonus = $maxbonus;
                }       
                $smarty -> assign ("Message", YOU_RISE2.$arritem -> fields['name'].FOR_A.$bonus.NOW_IS.$bonus.S_EXP.$magic.S_CAST);
                $maxdur = $arritem -> fields['maxwt'] + $bonus;
                $dur = $arritem -> fields['wt'] + $bonus;
                $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND wt=".$dur." AND type='".$arritem -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$arritem -> fields['power']." AND zr=".$arritem -> fields['zr']." AND szyb=".$arritem -> fields['szyb']." AND maxwt=".$maxdur." AND poison=".$arritem -> fields['poison']." AND magic='Y' AND ptype='".$arritem -> fields['ptype']."' AND repair=".$arritem -> fields['repair']);
                if (!$test -> fields['id']) 
                {
                    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, ptype, twohand, repair) VALUES(".$player -> id.",'".$name."',".$arritem -> fields['power'].",'".$arritem -> fields['type']."',".$arritem -> fields['cost'].",".$arritem -> fields['zr'].",".$dur.",".$arritem -> fields['minlev'].",".$maxdur.",1,'Y',".$arritem -> fields['poison'].",".$arritem -> fields['szyb'].",'".$arritem -> fields['ptype']."', '".$arritem -> fields['twohand']."', ".$arritem -> fields['repair'].")") or error(E_DB);
                } 
                    else 
                {
                    $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
                }
                $test -> Close();
                checkexp($player -> exp,$bonus,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'magia',$magic);
            } 
                else 
            {
                $db -> Execute("UPDATE players SET magia=magia+0.01 WHERE id=".$player -> id);
                $smarty -> assign ("Message", YOU_TRY.$arritem -> fields['name'].BUT_FAIL);
            }
        }
        if ($arrspell -> fields['nazwa'] == E_SPELL3) 
        {
            if ($chance > 100) 
            {
                if ($arritem -> fields['type'] == 'W' || $arritem -> fields['type'] == 'B') 
                {
                    $maxbonus = ($objBonus -> fields['szyb'] * 5);
                    if ($bonus > $maxbonus) 
                    {
                        $bonus = $maxbonus;
                    }       
                    $speed = $arritem -> fields['szyb'] + $bonus;
                    $agi = $arritem -> fields['zr'];
                    $text = YOU_RISE3;
                } 
                    elseif ($arritem -> fields['type'] == 'A' || $arritem -> fields['type'] == 'L') 
                {
                    if ($objBonus -> fields['zr'] < 0)
                    {
                        $intBonus = ($objBonus -> fields['zr'] * -1);
                    }
                        else
                    {
                        $intBonus = $objBonus -> fiedlds['zr'];
                    }
                    $maxbonus = ($intBonus * 5);
                    if ($bonus > $maxbonus) 
                    {
                        $bonus = $maxbonus;
                    }
                    if ($arritem -> fields['zr'] < 0)
                    {
                        $agi = $arritem -> fields['zr'] - $bonus;
                    }
                        else
                    {
                        $agi = $arritem -> fields['zr'] + $bonus;
                    }
                    $speed = $arritem -> fields['szyb'];
                    $text = YOU_RISE4;
                } 
                    else 
                {
                    error (NOT_ABLE3);
                }
                $smarty -> assign ("Message", $text." ".$arritem -> fields['name']." o ".$bonus.NOW_IS.$bonus.S_EXP.$magic.S_CAST);
                $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND wt=".$arritem -> fields['wt']." AND type='".$arritem -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$arritem -> fields['power']." AND zr=".$agi." AND szyb=".$speed." AND maxwt=".$arritem -> fields['maxwt']." AND poison=".$arritem -> fields['poison']." AND magic='Y' AND ptype='".$arritem -> fields['ptype']."' AND repair=".$arritem -> fields['repair']);
                if (!$test -> fields['id']) 
                {
                    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, repair) VALUES(".$player -> id.",'".$name."',".$arritem -> fields['power'].",'".$arritem -> fields['type']."',".$arritem -> fields['cost'].",".$agi.",".$arritem -> fields['wt'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",1,'Y',".$arritem -> fields['poison'].",".$speed.", '".$arritem -> fields['twohand']."', ".$arritem -> fields['repair'].")") or error(E_DB);
                } 
                    else 
                {
                    $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
                }
                checkexp($player -> exp,$bonus,$player -> level,$player -> race,$player -> user,$player -> id,0,0,$player -> id,'magia',$magic);
            } 
                else 
            {
                $db -> Execute("UPDATE players SET magia=magia+0.01 WHERE id=".$player -> id);
                $smarty -> assign ("Message", YOU_TRY.$arritem -> fields['name'].BUT_FAIL);
            }
        }
        $db -> Execute("UPDATE `players` SET `pm`=`pm`-".$arrspell -> fields['poziom'].", `energy`=`energy`-".$arrspell -> fields['poziom']." WHERE id=".$player -> id);
        $arrspell -> Close();
        $amount = $arritem -> fields['amount'] - 1;
        if ($amount > 0) 
        {
            $db -> Execute("UPDATE equipment SET amount=amount-1 WHERE id=".$arritem -> fields['id']);
        } 
            else 
        {
            $db -> Execute("DELETE FROM equipment WHERE id=".$arritem -> fields['id']);
        }
        $objBonus -> Close();
        $arritem -> Close();
    }
    $smarty -> assign(array("Spellid" => $czary -> fields['id'], 
        "Spellname" => $czary -> fields['nazwa'], 
        "Itemname" => $arriname, 
        "Itemamount" => $arriamount, 
        "Itemid" => $arriid,
        "Cast2" => CAST,
        "Spell" => SPELL,
        "Ona" => ON_A,
        "Iamount" => I_AMOUNT));
    $czary -> Close();
}

/**
* Activate battle and defense spells
*/
if (isset($_GET['naucz'])) 
{
    if (!ereg("^[1-9][0-9]*$", $_GET['naucz'])) 
    {
        error (ERROR);
    }
    $czary = $db -> Execute("SELECT * FROM czary WHERE id=".$_GET['naucz']);
    if (!$czary -> fields['id']) 
    {
        error (NO_SPELL);
    }
    if ($player -> id != $czary -> fields['gracz']) 
    {
        error (NOT_YOUR);
    }
    if ($player -> level < $czary -> fields['poziom']) 
    {
        error (TOO_LOW_L);
    }
    if ($player -> clas != 'Mag') 
    {
        error (ONLY_MAGE);
    }
    $db -> Execute("UPDATE czary SET status='U' WHERE gracz=".$player -> id." AND typ='".$czary -> fields['typ']."' AND status='E'");
    $db -> Execute("UPDATE czary SET status='E' WHERE id=".$czary -> fields['id']." AND gracz=".$player -> id);
    $smarty -> assign (array("Name" => $czary -> fields['nazwa'],
        "Youuse" => YOU_USE));
    $czary -> Close();
}

/**
* Initialization of variables
*/
if (!isset($_GET['deakt'])) 
{
    $_GET['deakt'] = '';
}
if (!isset($_GET['naucz'])) 
{
    $_GET['naucz'] = '';
}
if (!isset($_GET['cast'])) 
{
    $_GET['cast'] = '';
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Deaktiv" => $_GET['deakt'], 
    "Learn" => $_GET['naucz'], 
    "Bname" => $arrname1, 
    "Bpower" => $arrpower1,
    "Bid" => $arrid1, 
    "Dname" => $arrname2, 
    "Dpower" => $arrpower2, 
    "Did" => $arrid2, 
    "Uname" => $arrname3, 
    "Ueffect" => $arrefect,
    "Uid" => $arrid3, 
    "Cast" => $_GET['cast'],
    "Arefresh" => S_REFRESH,
    "Usedspells" => USED_SPELLS,
    "Spellbook" => SPELL_BOOK,
    "Bspells" => B_SPELLS,
    "Dspells" => D_SPELLS,
    "Espells" => E_SPELLS,
    "Usethis" => USE_THIS,
    "Bdamage" => B_DAMAGE,
    "Ddefense" => D_DEFENSE,
    "Castthis" => CAST_THIS));
$smarty -> display ('czary.tpl');

require_once("includes/foot.php");
?>
