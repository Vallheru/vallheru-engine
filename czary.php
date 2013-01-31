<?php
/**
 *   Funkcje pliku:
 *   Spell book - activation and deactivafion of spells and echance items
 *
 *   @name                 : czary.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 31.01.2013
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

/**
* Get the localization for game
*/
require_once("languages/".$lang."/czary.php");

if (isset($_GET['deakt'])) 
{
    checkvalue($_GET['deakt']);
    $czary1 = $db -> Execute("SELECT * FROM `czary` WHERE `id`=".$_GET['deakt']);
    if (!$czary1 -> fields['id']) 
      {
	message('error', NO_SPELL);
      }
    elseif ($player -> id != $czary1 -> fields['gracz']) 
      {
        message('error', NOT_YOUR);
      }
    else
      {
	$db -> Execute("UPDATE `czary` SET `status`='U' WHERE `id`=".$czary1 -> fields['id']);
	$czary1 -> Close();
      }
}

/**
* Enchance items
*/
if (isset($_GET['cast'])) 
{
    checkvalue($_GET['cast']);
    $arrspell = $db -> Execute("SELECT * FROM czary WHERE id=".$_GET['cast']);
    $blnValid = TRUE;
    if (!$arrspell -> fields['id']) 
      {
	message('error', NO_SPELL);
	$blnValid = FALSE;
      }
    if ($player -> id != $arrspell -> fields['gracz']) 
      {
        message('error', NOT_YOUR);
	$blnValid = FALSE;
      }
    if ($player -> mana < $arrspell -> fields['poziom']) 
      {
        message('error', NO_MANA);
	$blnValid = FALSE;
      }
    if ($player -> clas == 'Barbarzyńca') 
      {
        message('error', YOU_BARBARIAN);
	$blnValid = FALSE;
      }
    $arrItems = array();
    if ($blnValid)
      {
	switch ($arrspell->fields['nazwa'])
	  {
	  case E_SPELL1:
	    $strTypes = "'I', 'Q', 'P', 'E', 'O', 'B', 'T', 'C'";
	    break;
	  case E_SPELL2:
	    $strTypes = "'I', 'Q', 'P', 'E', 'O', 'R', 'T', 'C'";
	    break;
	  case E_SPELL3:
	    $strTypes = "'I', 'Q', 'P', 'E', 'O', 'R', 'H', 'S', 'T', 'C'";
	    break;
	  default:
	    error('Zapomnij o tym.');
	    break;
	  }
	$arrItems = $db->GetAll("SELECT `id`, `name`, `amount`, `power`, `szyb`, `zr`, `wt`, `maxwt`, `type` FROM `equipment` WHERE `owner`=".$player -> id." AND `status`='U' AND `magic`='N' AND `type`NOT IN (".$strTypes.")");
	foreach ($arrItems as &$arrItem)
	  {
	    if ($arrItem['szyb'] != 0)
	      {
		$arrItem['szyb'] = ' (+'.$arrItem['szyb'].' szyb) ';
	      }
	    else
	      {
		$arrItem['szyb'] = '';
	      }
	    if ($arrItem['zr'] != 0)
	      {
		$arrItem['zr'] = ' ('.($arrItem['zr'] * -1).' zr) ';
	      }
	    else
	      {
		$arrItem['zr'] = '';
	      }
	    if ($arrItem['type'] == 'R')
	      {
		$arrItem['amount'] = $arrItem['wt'];
		$arrItem['wt'] = '';
	      }
	    else
	      {
		$arrItem['wt'] = ' ('.$arrItem['wt'].'/'.$arrItem['maxwt'].' wt) ';
	      }
	    if ($arrItem['power'] != 0)
	      {
		$arrItem['power'] = ' (+'.$arrItem['power'].') ';
	      }
	    else
	      {
		$arrItem['power'] = '';
	      }
	  }
      }
    if ((isset($_GET['step']) && $_GET['step'] == 'items') && $blnValid)
    {
        if (!isset($_POST['item'])) 
        {
            error (ERROR);
        }
	checkvalue($_POST['item']);
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
            message('error', NO_ITEM);
	    $blnValid = FALSE;
	  }
        if ($arritem -> fields['owner'] != $player -> id) 
	  {
            message('error', NOT_YOUR);
	    $blnValid = FALSE;
	  }
        if ($arritem -> fields['magic'] != 'N') 
	  {
            message('error', IS_MAGIC);
	    $blnValid = FALSE;
	  }
        if ($player -> energy < $arrspell -> fields['poziom']) 
	  {
	    message('error', NO_ENERGY);
	    $blnValid = FALSE;
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
	    message('error', NO_ENCHANCE);
	    $blnValid = FALSE;
        }

	if ($blnValid)
	  {
	    /**
	     * Add bonuses to ability
	     */
	    $player->curskills(array('magic'));
	    $player->skills['magic'][1] += $player->checkbonus('magic');
	    
	    $chance = (($player->skills['magic'][1] + $player->stats['inteli'][2]) - $arritem -> fields['minlev'] - $arrspell -> fields['poziom'] + rand(1,100));
	    $bonus = $player->skills['magic'][1] * rand(1, 5);
	    
	    $arrElement = array('earth' => 'E', 'water' => 'W', 'fire' => 'F', 'wind' => 'A');
	    $blnValid2 = TRUE;
	    if ($arrspell -> fields['nazwa'] == E_SPELL1) 
	      {
		if ($arritem -> fields['type'] == 'B') 
		  {
		    message('error', NOT_ABLE1);
		    $blnValid2 = FALSE;
		    $blnValid = FALSE;
		  }
		elseif ($chance > 100) 
		  {
		    $maxbonus = ($objBonus -> fields['power'] * 5);
		    if ($bonus > $maxbonus) 
		      {
			$bonus = $maxbonus;
		      }
		    $power = $arritem -> fields['power'] + $bonus;
		    message('success', YOU_RISE.$arritem -> fields['name'].FOR_A.$bonus.NOW_IS.$bonus.S_EXP);
		    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND wt=".$arritem -> fields['wt']." AND type='".$arritem -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$power." AND zr=".$arritem -> fields['zr']." AND szyb=".$arritem -> fields['szyb']." AND maxwt=".$arritem -> fields['maxwt']." AND poison=".$arritem -> fields['poison']." AND ptype='".$arritem -> fields['ptype']."' AND magic='".$arrElement[$arrspell->fields['element']]."' AND repair=".$arritem -> fields['repair']);
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, ptype, twohand, repair) VALUES(".$player -> id.",'".$name."',".$power.",'".$arritem -> fields['type']."',".$arritem -> fields['cost'].",".$arritem -> fields['zr'].",".$arritem -> fields['wt'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",1,'".$arrElement[$arrspell->fields['element']]."',".$arritem -> fields['poison'].",".$arritem -> fields['szyb'].",'".$arritem -> fields['ptype']."', '".$arritem -> fields['twohand']."', ".$arritem -> fields['repair'].")") or error(E_DB);
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
		    $player->checkexp(array('inteli' => ($bonus / 2)), $player->id, 'stats');
		    $player->checkexp(array('magic' => ($bonus / 2)), $player->id, 'skills');
		  } 
                else 
		  {
		    $player->checkexp(array('inteli' => 1), $player->id, 'stats');
		    $player->checkexp(array('magic' => 1), $player->id, 'skills');
		    message('error', YOU_TRY.$arritem -> fields['name'].BUT_FAIL);
		    $blnValid = FALSE;
		  }
	      }
	    if ($arrspell -> fields['nazwa'] == E_SPELL2) 
	      {
		if ($arritem -> fields['type'] == 'R') 
		  {
		    message('error', NOT_ABLE2);
		    $blnValid2 = FALSE;
		    $blnValid = FALSE;
		  }
		elseif ($chance > 100) 
		  {
		    $maxbonus = ($objBonus -> fields['maxwt'] * 5);
		    if ($bonus > $maxbonus) 
		      {
			$bonus = $maxbonus;
		      }       
		    message('success', YOU_RISE2.$arritem -> fields['name'].FOR_A.$bonus.NOW_IS.$bonus.S_EXP);
		    $maxdur = $arritem -> fields['maxwt'] + $bonus;
		    $dur = $arritem -> fields['wt'] + $bonus;
		    $test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND wt=".$dur." AND type='".$arritem -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$arritem -> fields['power']." AND zr=".$arritem -> fields['zr']." AND szyb=".$arritem -> fields['szyb']." AND maxwt=".$maxdur." AND poison=".$arritem -> fields['poison']." AND magic='".$arrElement[$arrspell->fields['element']]."' AND ptype='".$arritem -> fields['ptype']."' AND repair=".$arritem -> fields['repair']);
		    if (!$test -> fields['id']) 
		      {
			$db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, ptype, twohand, repair) VALUES(".$player -> id.",'".$name."',".$arritem -> fields['power'].",'".$arritem -> fields['type']."',".$arritem -> fields['cost'].",".$arritem -> fields['zr'].",".$dur.",".$arritem -> fields['minlev'].",".$maxdur.",1,'".$arrElement[$arrspell->fields['element']]."',".$arritem -> fields['poison'].",".$arritem -> fields['szyb'].",'".$arritem -> fields['ptype']."', '".$arritem -> fields['twohand']."', ".$arritem -> fields['repair'].")") or error(E_DB);
		      } 
                    else 
		      {
			$db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
		      }
		    $test -> Close();
		    $player->checkexp(array('inteli' => ($bonus / 2)), $player->id, 'stats');
		    $player->checkexp(array('magic' => ($bonus / 2)), $player->id, 'skills');
		  } 
                else 
		  {
		    $player->checkexp(array('inteli' => 1), $player->id, 'stats');
		    $player->checkexp(array('magic' => 1), $player->id, 'skills');
		    message('error', YOU_TRY.$arritem -> fields['name'].BUT_FAIL);
		    $blnValid = FALSE;
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
			    $intBonus = $objBonus -> fields['zr'];
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
			message('error', NOT_ABLE3);
			$blnValid2 = FALSE;
			$blnValid = FALSE;
		      }
		    if ($blnValid)
		      {
			message("success", $text." ".$arritem -> fields['name']." o ".$bonus.NOW_IS.$bonus.S_EXP);
			$test = $db -> Execute("SELECT id FROM equipment WHERE name='".$name."' AND wt=".$arritem -> fields['wt']." AND type='".$arritem -> fields['type']."' AND status='U' AND owner=".$player -> id." AND power=".$arritem -> fields['power']." AND zr=".$agi." AND szyb=".$speed." AND maxwt=".$arritem -> fields['maxwt']." AND poison=".$arritem -> fields['poison']." AND magic='".$arrElement[$arrspell->fields['element']]."' AND ptype='".$arritem -> fields['ptype']."' AND repair=".$arritem -> fields['repair']);
			if (!$test -> fields['id']) 
			  {
			    $db -> Execute("INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, amount, magic, poison, szyb, twohand, repair) VALUES(".$player -> id.",'".$name."',".$arritem -> fields['power'].",'".$arritem -> fields['type']."',".$arritem -> fields['cost'].",".$agi.",".$arritem -> fields['wt'].",".$arritem -> fields['minlev'].",".$arritem -> fields['maxwt'].",1,'".$arrElement[$arrspell->fields['element']]."',".$arritem -> fields['poison'].",".$speed.", '".$arritem -> fields['twohand']."', ".$arritem -> fields['repair'].")") or error(E_DB);
			  } 
			else 
			  {
			    $db -> Execute("UPDATE equipment SET amount=amount+1 WHERE id=".$test -> fields['id']);
			  }
			$player->checkexp(array('inteli' => ($bonus / 2)), $player->id, 'stats');
			$player->checkexp(array('magic' => ($bonus / 2)), $player->id, 'skills');
		      } 
		  } 
                else 
		  {
		    $blnValid = FALSE;
		    $player->checkexp(array('inteli' => 1), $player->id, 'stats');
		    $player->checkexp(array('magic' => 1), $player->id, 'skills');
		    message("error", YOU_TRY.$arritem -> fields['name'].BUT_FAIL);
		  }
	      }
	    if ($blnValid)
	      {
		$db -> Execute("UPDATE `players` SET `pm`=`pm`-".$arrspell -> fields['poziom'].", `energy`=`energy`-".$arrspell -> fields['poziom']." WHERE id=".$player -> id);
	      }
	    if ($blnValid2)
	      {
		$amount = $arritem -> fields['amount'] - 1;
		if ($amount > 0) 
		  {
		    $db -> Execute("UPDATE equipment SET amount=amount-1 WHERE id=".$arritem -> fields['id']);
		  } 
		else 
		  {
		    $db -> Execute("DELETE FROM equipment WHERE id=".$arritem -> fields['id']);
		  }
	      }
	    $objBonus -> Close();
	    $arritem -> Close();	    
	  }
    }
    $smarty -> assign(array("Spellid" => $arrspell -> fields['id'], 
        "Spellname" => $arrspell -> fields['nazwa'], 
        "Items" => $arrItems, 
        "Cast2" => CAST,
        "Spell23" => SPELL,
        "Ona" => ON_A,
        "Iamount" => I_AMOUNT));
    $arrspell -> Close();
}
else
  {
    $_GET['cast'] = '';
  }

/**
* Activate battle and defense spells
*/
if (isset($_GET['naucz'])) 
{
    checkvalue($_GET['naucz']);
    $czary = $db -> Execute("SELECT * FROM `czary` WHERE `id`=".$_GET['naucz']);
    if (!$czary -> fields['id']) 
      {
	message('error', NO_SPELL);
      }
    elseif ($player -> id != $czary -> fields['gracz']) 
      {
        message('error', NOT_YOUR);
      }
    elseif ($player->skills['magic'][1] < $czary -> fields['poziom']) 
      {
        message('error', 'Nie możesz jeszcze używać tego czaru.');
      }
    elseif ($player -> clas != 'Mag') 
      {
        message('error', ONLY_MAGE);
      }
    else
      {
	$db -> Execute("UPDATE czary SET status='U' WHERE gracz=".$player -> id." AND typ='".$czary -> fields['typ']."' AND status='E'");
	$db -> Execute("UPDATE czary SET status='E' WHERE id=".$czary -> fields['id']." AND gracz=".$player -> id);
	message('success', 'Używasz '.$czary->fields['nazwa']);
      }
    $czary -> Close();
}

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

$czary = $db -> Execute("SELECT * FROM `czary` WHERE `gracz`=".$player -> id." AND `status`='U' AND `typ`='B' ORDER BY `poziom` DESC");
$arrBspells = array("Ziemia" => array(), "Woda" => array(), "Powietrze" => array(), "Ogień" => array());
$arrElements = array('earth' => 'Ziemia', 'water' => 'Woda', 'wind' => 'Powietrze', 'fire' => 'Ogień');
while (!$czary -> EOF) 
  {
    $strKey = $arrElements[$czary->fields['element']];
    $arrBspells[$strKey][] = array("id" => $czary->fields['id'],
				   "name" => $czary->fields['nazwa'],
				   "dmg" => $czary->fields['obr']);
    $czary -> MoveNext();
}
$czary -> Close();
foreach ($arrBspells as $key => $value)
{
  if (count($arrBspells[$key]) == 0)
    {
      unset($arrBspells[$key]);
    }
}

$czaryo = $db -> Execute("SELECT * FROM `czary` WHERE `gracz`=".$player -> id." AND `status`='U' AND `typ`='O' ORDER BY `poziom` DESC");
$arrDspells = array("Ziemia" => array(), "Woda" => array(), "Powietrze" => array(), "Ogień" => array());
while (!$czaryo -> EOF) 
{
    $strKey = $arrElements[$czaryo->fields['element']];
    $arrDspells[$strKey][] = array("id" => $czaryo->fields['id'],
				   "name" => $czaryo->fields['nazwa'],
				   "def" => $czaryo->fields['obr']);
    $czaryo -> MoveNext();
}
$czaryo -> Close();
foreach ($arrDspells as $key => $value)
{
  if (count($arrDspells[$key]) == 0)
    {
      unset($arrDspells[$key]);
    }
}


$arrname3 = array();
$arrefect = array();
$arrid3 = array();
$i = 0;
$czaryu = $db -> Execute("SELECT * FROM `czary` WHERE `gracz`=".$player -> id." AND `typ`='U' ORDER BY `poziom` DESC");
$arrEspells = array("Ziemia" => array(), "Woda" => array(), "Powietrze" => array(), "Ogień" => array());
while (!$czaryu -> EOF) 
{
    $strKey = $arrElements[$czaryu->fields['element']];
    if ($czaryu -> fields['nazwa'] == E_SPELL1) 
    {
        $strEffect = S_EFECT1;
    }
    if ($czaryu -> fields['nazwa'] == E_SPELL2) 
    {
        $strEffect = S_EFECT2;
    }
    if ($czaryu -> fields['nazwa'] == E_SPELL3) 
    {
        $strEffect = S_EFECT3;
    }
    $arrEspells[$strKey][] = array("id" => $czaryu->fields['id'],
				   "name" => $czaryu->fields['nazwa'],
				   "effect" => $strEffect);
    $czaryu -> MoveNext();
}
$czaryu -> Close();
foreach ($arrEspells as $key => $value)
{
  if (count($arrEspells[$key]) == 0)
    {
      unset($arrEspells[$key]);
    }
}

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
  {
    $strChecked = "";
  }
else
  {
    $strChecked = "checked=checkded";
  }

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Bspells2" => $arrBspells,
			"Bamount" => count($arrBspells),
			"Telement" => "Żywioł:",
			"Dspells2" => $arrDspells,
			"Damount" => count($arrDspells),
			"Eamount" => count($arrEspells),
			"Nospells" => "Obecnie nie posiadasz jakichkolwiek czarów w księdze. Możesz zakupić nowe czary w Magicznej Wieży w miastach.",
			"Checked" => $strChecked,
			"Espells2" => $arrEspells,
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
