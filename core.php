<?php
/**
 *   File functions:
 *   Core arena
 *
 *   @name                 : core.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.7
 *   @since                : 23.11.2012
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

$title = "Polana Chowańców";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/core.php");

if ($player -> location != 'Altara' && $player -> location != 'Ardulith') 
{
    error (ERROR);
}

/**
* Assign variables to template
*/
$smarty -> assign(array("Gains" => '', 
			"Info" => '', 
			"Link" => ''));

if ($player -> corepass != 'Y') 
{
    $smarty -> assign(array("Corepassinfo" => COREPASS_INFO,
			    "Ayes" => A_YES,
			    "Ano" => A_NO,
			    "Havemoney" => HAVE_MONEY));
    if ($player -> credits < 500) 
    {
        error (NO_LICENSE." (<a href=\"city.php\">".BACK."</a>)");
    } 
        else 
    {
        if (isset ($_GET['answer']) && $_GET['answer'] == 'yes') 
        {
            if ($player -> credits < 500) 
            {
                error (NO_MONEY);
            }  
                else 
            {
                $db -> Execute("UPDATE players SET credits=credits-500, corepass='Y' WHERE id=".$player -> id);
                error (YES_LICENSE);
            }
        }
    }
}

if (isset($_GET['view']))
  {
    /**
     * Cores monuments
     */
    if ($_GET['view'] == 'best')
      {
	$arrTypes = array("Plant", "Aqua", "Material", "Element", "Alien", "Ancient");
	$arrTop = array();
	for ($i = 0; $i < count($arrTypes); $i++)
	  {
	    $objTop = $db -> SelectLimit("SELECT `id`, `name`, `corename`, `wins` FROM `core` WHERE `type`='".$arrTypes[$i]."' ORDER BY `wins` DESC", 5);
	    $arrTop[$i] = array();
	    while (!$objTop->EOF)
	      {
		if (!empty($objTop->fields['corename']))
		  {
		    $strName = $objTop->fields['corename'];
		  }
		else
		  {
		    $strName = $objTop->fields['name'];
		  }
		$arrTop[$i][] = "<tr><td align=\"center\">".$strName." (".$objTop->fields['id'].")</td><td align=\"center\">".$objTop->fields['wins']."</td></tr>";
		$objTop->MoveNext();
	      }
	    $objTop->Close();
	  }
	$arrArenaname = array(ARENA1, ARENA2, ARENA3, ARENA4, ARENA5, ARENA6);
	$smarty -> assign(array("Top1" => $arrTop[0],
				"Top2" => $arrTop[1],
				"Top3" => $arrTop[2],
				"Top4" => $arrTop[3],
				"Top5" => $arrTop[4],
				"Top6" => $arrTop[5],
				"Tarens" => $arrArenaname,
				"Cname" => T_CNAME,
				"Twins" => T_WINS));
      }

    /**
     * Core breeding
     */
    elseif ($_GET['view'] == 'breed')
      {
	$objCoreMale = $db -> Execute("SELECT * FROM `core` WHERE `owner`=".$player -> id." AND `gender`='M' AND `status`='Alive'");
	$arrCoremale = array();
	$arrCoremaleid = array();
	$i = 0;
	while (!$objCoreMale -> EOF)
	  {
	    if (!$objCoreMale -> fields['corename'])
	      {
		$arrCoremale[$i] = $objCoreMale -> fields['name'];
	      }
            else
	      {
		$arrCoremale[$i] = $objCoreMale -> fields['corename']." (".$objCoreMale -> fields['name'].")";
	      }
	    $arrCoremale[$i] .= ' Siła: '.$objCoreMale->fields['power'].' Obrona: '.$objCoreMale->fields['defense'];
	    $arrCoremaleid[$i] = $objCoreMale -> fields['id'];
	    $i ++;
	    $objCoreMale -> MoveNext();
	  }
	$objCoreMale -> Close();
	$objCoreFemale = $db -> Execute("SELECT * FROM `core` WHERE `owner`=".$player -> id." AND `gender`='F' AND `status`='Alive'");
	$arrCorefemale = array();
	$arrCorefemaleid = array();
	$i = 0;
	while (!$objCoreFemale -> EOF)
	  {
	    if (!$objCoreFemale -> fields['corename'])
	      {
		$arrCorefemale[$i] = $objCoreFemale -> fields['name'];
	      }
            else
	      {
		$arrCorefemale[$i] = $objCoreFemale -> fields['corename']." (".$objCoreFemale -> fields['name'].")";
	      }
	    $arrCorefemale[$i] .= ' Siła: '.$objCoreFemale->fields['power'].' Obrona: '.$objCoreFemale->fields['defense'];
	    $arrCorefemaleid[$i] = $objCoreFemale -> fields['id'];
	    $i ++;
	    $objCoreFemale -> MoveNext();
	  }
	$objCoreFemale -> Close();
	$smarty -> assign(array("Breedinfo" => BREED_INFO,
				"Coremale" => $arrCoremale,
				"Coremaleid" => $arrCoremaleid,
				"Corefemale" => $arrCorefemale,
				"Corefemaleid" => $arrCorefemaleid,
				"Trainpts" => TRAIN_PTS,
				"Trains" => $player -> trains,
				"Abreed" => A_BREED,
				"Tcores" => T_CORES,
				"Tand" => T_AND));
    
	/**
	 * Breeed cores and breeding cost
	 */
	if (isset($_GET['step']) && $_GET['step'] == 'breed')
	  {
	    if (!isset($_POST['coremale']) || !isset($_POST['corefemale']))
	      {
		error(ERROR);
	      }
	    checkvalue($_POST['coremale']);
	    checkvalue($_POST['corefemale']);
	    $objCoremale = $db -> Execute("SELECT `power`, `defense`, `name`, `owner`, `type` FROM `core` WHERE `id`=".$_POST['coremale']);
	    $objCorefemale = $db -> Execute("SELECT `power`, `defense`, `name`, `owner` FROM `core` WHERE `id`=".$_POST['corefemale']);
	    if ($objCoremale -> fields['owner'] != $player -> id || $objCorefemale -> fields['owner'] != $player -> id)
	      {
		error(NOT_YOUR);
	      }
	    if ($objCoremale -> fields['name'] != $objCorefemale -> fields['name'])
	      {
		error(WRONG_TYPE);
	      }
	    $intCost = ceil(($objCoremale -> fields['power'] + $objCoremale -> fields['defense'] + $objCoreFemale -> fields['power'] + $objCoreFemale -> fields['defense']) / 4);
	    $smarty -> assign(array("Cost" => $intCost,
				    "Maleid" => $_POST['coremale'],
				    "Femaleid" => $_POST['corefemale'],
				    "Thiscost" => THIS_COST,
				    "Mithcoins" => MITH_COINS,
				    "Doyou" => DO_YOU,
				    "Ayes" => YES,
				    "Ano" => NO));
	    
	    /**
	     * Start breeding
	     */
	    if (isset($_GET['next']) && $_GET['next'] == 'breed')
	      {
		if ($intCost > $player -> platinum)
		  {
		    error(NO_MITH2);
		  }
		if ($player -> trains < 15)
		  {
		    error(NO_TRAIN_P);
		  }
		$objChance = $db -> Execute("SELECT `ref_id` FROM `core` WHERE `id`=".$_POST['coremale']);
		if (!$objChance -> fields['ref_id'])
		  {
		    $intChance = 30;
		    $intExp = 50;
		    $intAbility = 0.01;
		  }
                else
		  {
		    $intChance = $objChance -> fields['ref_id'];
		    $intExp = $intChance * 10;
		    $intAbility = $intChance / 100;
		  }
		$objChance -> Close();

		/**
		 * Add bonuses to ability
		 */
		$player->curskills(array('breeding'), TRUE, TRUE);
		$player->skills['breeding'][1] += $player->checkbonus('breeding');
		
		$fltRoll = rand(1,100) / 100;
		$fltResult = $player -> skills['breeding'][1] + $fltRoll;
		if ($fltResult >= $intChance)
		  {
		    if ($objCoremale -> fields['power'] < $objCorefemale -> fields['power'])
		      {
			$fltPower = $objCoremale -> fields['power'];
			$fltMaxpower = $objCorefemale -> fields['power'];
		      }
                    else
		      {
			$fltPower = $objCorefemale -> fields['power'];
			$fltMaxpower = $objCoremale -> fields['power'];
		      }
		    if ($objCoremale -> fields['defense'] < $objCorefemale -> fields['defense'])
		      {
			$fltDefense = $objCoremale -> fields['defense'];
			$fltMaxdefense = $objCorefemale -> fields['defense'];
		      }
                    else
		      {
			$fltDefense = $objCorefemale -> fields['defense'];
			$fltMaxdefense = $objCoremale -> fields['defense'];
		      }
		    $fltPower = $fltPower + $player -> skills['breeding'][1];
		    if ($fltPower > $fltMaxpower)
		      {
			$fltPower = $fltMaxpower;
		      }
		    $fltDefense = $fltDefense + $player -> skills['breeding'][1];
		    if ($fltDefense > $fltMaxdefense)
		      {
			$fltDefense = $fltMaxdefense;
		      }
		    $intGender = rand(1,2);
		    if ($intGender == 1)
		      {
			$strGender = C_MALE;
			$strGen = 'M';
		      }
                    else
		      {
			$strGender = C_FEMALE;
			$strGen = 'F';
		      }
		    $db -> Execute("INSERT INTO core (`owner`, `name`, `type`, `ref_id`, `power`, `defense`, `gender`) VALUES(".$player -> id.", '".$objCoremale -> fields['name']."', '".$objCoremale -> fields['type']."',".$intChance.", ".$fltPower.", ".$fltDefense.", '".$strGen."')") or error("Could not add Core.");
		    $smarty -> assign("Message", YOU_SUCC.$objCoremale -> fields['name'].T_CORE2.$strGender.YOU_GAIN3.$intExp.AND_GAIN);
		  }
                else
		  {
		    $smarty -> assign("Message", YOU_FAIL.' Otrzymujesz 1 punkt doświadczenia.');
		    $intExp = 1;
		  }
		$player->checkexp(array('breeding' => $intExp), $player->id, 'skills');
		$db -> Execute("UPDATE `players` SET `platinum`=`platinum`-".$intCost.", `trains`=`trains`-15 WHERE `id`=".$player -> id);
	      }
	  }
      }

    /**
     * List owned cores
     */
    elseif ($_GET['view'] == 'mycores') 
      {
	if (!isset($_GET['id'])) 
	  {
	    $smarty -> assign(array("Mycoresinfo" => MY_CORES_INFO,
				    "Tcore" => T_CORE));
	    $core = $db -> Execute("SELECT `id`, `name`, `active`, `corename`, `gender` FROM `core` WHERE `owner`=".$player -> id);
	    $arrid = array();
	    $arrname = array();
	    $arractiv = array();
	    $arrCorename = array();
	    $arrGender = array();
	    $i = 0;
	    while (!$core -> EOF) 
	      {
		$arrid[$i] = $core -> fields['id'];
		$arrname[$i] = $core -> fields['name'];
		if ($core -> fields['gender'] == 'M')
		  {
		    $arrGender[$i] = C_MALE;
		  }
                else
		  {
		    $arrGender[$i] = C_FEMALE;
		  }
		if ($core -> fields['corename'] == '')
		  {
		    $arrCorename[$i] = '';
		  }
                else
		  {
		    $arrCorename[$i] = "<b>".$core -> fields['corename']."</b> ";
		  }
		if ($core -> fields['active'] == 'T')
		  {
		    $arractiv[$i] = C_ACTIVE;
		  } 
                else 
		  {
		    $arractiv[$i] = '';
		  }
		$i = $i + 1;
		$core -> MoveNext();
	      }
	    $core -> Close();
	    $smarty -> assign ( array("Name" => $arrname, 
				      "Coreid1" => $arrid, 
				      "Activ" => $arractiv,
				      "Corename" => $arrCorename,
				      "Tgender2" => $arrGender));
	  } 
        else 
	  {
	    checkvalue($_GET['id']);
	    $smarty -> assign(array("Showcore" => SHOW_CORE,
				    "Mainstats" => MAIN_STATS,
				    "Cid" => C_ID,
				    "Cname" => C_NAME,
				    "Ctype" => C_TYPE,
				    "Cstatus" => C_STATUS,
				    "Cpower" => C_POWER,
				    "Cdefense" => C_DEFENSE,
				    "Attributes" => ATTRIBUTES,
				    "Showdesc" => SHOW_DESC,
				    "Coptions" => C_OPTIONS,
				    "Freec" => FREE_C,
				    "Sendcore" => SEND_CORE,
				    "Tcname" => T_CNAME,
				    "Acname" => A_CNAME,
				    "Tgender" => T_GENDER,
				    "Twins" => T_WINS,
				    "Tlosses" => T_LOSSES));
	    $coreinfo = $db -> Execute("SELECT * FROM core WHERE id=".$_GET['id']);
	    if ($coreinfo -> fields['type'] == 'Plant') 
	      {
		$typ = C_TYPE1;
	      }
	    if ($coreinfo -> fields['type'] == 'Aqua') 
	      {
		$typ = C_TYPE2;
	      }
	    if ($coreinfo -> fields['type'] == 'Material') 
	      {
		$typ = C_TYPE3;
	      }
	    if ($coreinfo -> fields['type'] == 'Element') 
	      {
		$typ = C_TYPE4;
	      }
	    if ($coreinfo -> fields['type'] == 'Alien') 
	      {
		$typ = C_TYPE5;
	      }
	    if ($coreinfo -> fields['type'] == 'Ancient') 
	      {
		$typ = C_TYPE6;
	      }
	    if ($coreinfo -> fields['status'] == 'Alive') 
	      {
		$status = C_ALIVE;
	      }
	    if ($coreinfo -> fields['status'] == 'Dead') 
	      {
		$status = C_DEAD;
	      }
	    if (!$coreinfo -> fields['id']) 
	      {
		error (NO_CORE);
	      } 
            else 
	      {
		if ($coreinfo -> fields['owner'] != $player -> id) 
		  {
		    error (NOT_YOUR);
		  } 
                else 
		  {
		    $smarty -> assign (array("Id" => $coreinfo -> fields['id'], 
					     "Name" => $coreinfo -> fields['name'], 
					     "Type" => $typ, 
					     "Stat" => $status, 
					     "Power" => $coreinfo -> fields['power'], 
					     "Defense" => $coreinfo -> fields['defense'],
					     "Library" => $coreinfo -> fields['ref_id'],
					     "Wins" => $coreinfo -> fields['wins'],
					     "Losses" => $coreinfo -> fields['losses']));
		    if ($coreinfo -> fields['active'] == 'N') 
		      {
			$smarty -> assign ("Link", "(<a href=core.php?view=mycores&amp;activate=".$coreinfo -> fields['id'].">".A_ACTIV."</a>)");
		      }
		    if ($coreinfo -> fields['active'] == 'T') 
		      {
			$smarty -> assign ("Link", "(<a href=core.php?view=mycores&amp;dezaktywuj=".$coreinfo -> fields['id'].">".A_DEACTIV."</a>)");
		      }
		    if ($coreinfo -> fields['corename'] == '')
		      {
			$smarty -> assign("Corename", NO_NAME);
		      }
                    else
		      {
			$smarty -> assign("Corename", $coreinfo -> fields['corename']);
		      }
		    if ($coreinfo -> fields['gender'] == 'M')
		      {
			$smarty -> assign("Cgender", C_MALE);
		      }
                    else
		      {
			$smarty -> assign("Cgender", C_FEMALE);
		      }
		  }
	      }
	    $coreinfo -> Close();
	  }
	if (isset($_GET['activate'])) 
	  {
	    checkvalue($_GET['activate']);
	    $active = $db -> Execute("SELECT `id`, `owner`, `name`, `corename` FROM `core` WHERE `id`=".$_GET['activate']);
	    if ($active -> fields['owner'] != $player -> id) 
	      {
		error (NOT_YOUR);
	      } 
            else 
	      {
		if ($active -> fields['corename'] == '')
		  {
		    $strCorename = $active -> fields['name'];
		  }
                else
		  {
		    $strCorename = $active -> fields['corename'];
		  }
		$db -> Execute("UPDATE `core` SET `active`='N' WHERE `owner`=".$player -> id." AND `active`='Y'");
		$db -> Execute("UPDATE `core` SET `active`='T' WHERE `id`=".$_GET['activate']);
		if ($_GET['activate'] == $player->pet[0])
		  {
		    $player->pet = array(0, 0, 0);
		  }
		error (YOU_ACTIV." <b>".$strCorename." </b> (<a href=\"core.php?view=mycores\">".A_REFRESH."</a>).");
	      }
	  }
	if (isset($_GET['dezaktywuj'])) 
	  {
	    checkvalue($_GET['dezaktywuj']);
	    $dez = $db -> Execute("SELECT `id`, `owner`, `name`, `corename` FROM `core` WHERE `id`=".$_GET['dezaktywuj']);
	    if ($dez -> fields['owner'] != $player -> id) 
	      {
		error (NOT_YOUR);
	      } 
            else 
	      {
		if ($dez -> fields['corename'] == '')
		  {
		    $strCorename = $dez -> fields['name'];
		  }
                else
		  {
		    $strCorename = $dez -> fields['corename'];
		  }
		$db -> Execute("UPDATE `core` SET `active`='N' WHERE `id`=".$dez -> fields['id']);
		error (YOU_DEACT." <b>".$strCorename." </b> (<a href=\"core.php?view=mycores\">".A_REFRESH."</a>).");
	      }
	  }
	
	/**
	 * Release core
	 */
	if (isset($_GET['release'])) 
	  {
	    checkvalue($_GET['release']);
	    $rel = $db -> Execute("SELECT id, owner, name, corename FROM core WHERE id=".$_GET['release']);
	    if ($rel -> fields['owner'] != $player -> id) 
	      {
		error (NOT_YOUR);
	      } 
	    if ($rel -> fields['corename'] == '')
	      {
		$strCorename = $rel -> fields['name'];
	      }
            else
	      {
		$strCorename = $rel -> fields['corename'];
	      }
	    $smarty -> assign(array("Corename" => $strCorename,
				    "Release" => $_GET['release'],
				    "Doyou" => DO_YOU,
				    "Ayes" => YES,
				    "Ano" => NO));
	    if (isset($_GET['next']) && $_GET['next'] == 'yes')
	      {
		$db -> Execute("DELETE FROM core WHERE id=".$rel -> fields['id']);
		error (YOU_FREE." <b>".$strCorename." </b> (<a href=\"core.php?view=mycores\">".A_REFRESH."</a>).");
	      }
	  }
        else
	  {
	    $smarty -> assign("Release", '');
	  }
    
	/**
	 * Change core name
	 */
	if (isset($_GET['name']))
	  {
	    $_GET['name'] = htmlspecialchars($_GET['name']);
	    $rel = $db -> Execute("SELECT id, owner FROM core WHERE id=".$_GET['name']);
	    if ($rel -> fields['owner'] != $player -> id) 
	      {
		error (NOT_YOUR);
	      } 
            else 
	      {
		$smarty -> assign(array("Achange" => A_CHANGE,
					"Tchange" => T_CHANGE,
					"Id" => $_GET['name']));
		if (!isset($_GET['step'])) 
		  {
		    $_GET['step'] = '';
		  }
		if ($_GET['step'] == 'name') 
		  {
		    $_POST['cname'] = htmlspecialchars($_POST['cname'], ENT_QUOTES);
		    $strName = $db -> qstr($_POST['cname'], get_magic_quotes_gpc());
		    $db -> Execute("UPDATE core SET corename=".$strName." WHERE id=".$rel -> fields['id']) or error ("bĹÄd przy zapisie!");
		    error (YOU_CHANGE.$strName.". (<a href=core.php?view=mycores>".A_REFRESH."</a>).");
		  }
	      }
	  }
	/**
	 * Send core to another player
	 */
	if (isset($_GET['give'])) 
	  {
	    $_GET['give'] = intval($_GET['give']);
	    $smarty -> assign(array("Aadd" => A_ADD,
				    "Tplayer" => T_PLAYER));
	    $rel = $db -> Execute("SELECT `id`, `owner`, `name`, `corename` FROM `core` WHERE `id`=".$_GET['give']);
	    if ($rel -> fields['owner'] != $player -> id) 
	      {
		error (NOT_YOUR);
	      } 
            else 
	      {
		if ($rel -> fields['corename'] == '')
		  {
		    $strCorename = $rel -> fields['name'];
		  }
                else
		  {
		    $strCorename = $rel -> fields['corename'];
		  }
		$smarty -> assign(array("Id" => $_GET['give'], 
					"CoreName2" => $strCorename));
		if (!isset($_GET['step'])) 
		  {
		    $_GET['step'] = '';
		  }
		if ($_GET['step'] == 'give') 
		  {
		    checkvalue($_POST['gid']);
		    if ($_POST['gid'] == $player -> id) 
		      {
			error (BAD_PLAYER);
		      }
		    $dotowany = $db -> Execute("SELECT `id`, `user` FROM `players` WHERE `id`=".$_POST['gid']);
		    $strReceiversName = $dotowany -> fields['user'];
		    if (!$dotowany -> fields['id']) 
		      {
			error (NO_PLAYER);
		      }
		    $dotowany -> Close();
		    
		    $db -> Execute("UPDATE `core` SET `owner`=".$_POST['gid']." WHERE `id`=".$rel -> fields['id']) or error ("blad przy zapisie!");
		    $strDate = $db -> DBDate($newdate);
		    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES('".$_POST['gid']."','".L_PLAYER." <b><a href=view.php?view=".$player -> id.">".$player -> user."</a></b>".L_ID.'<b>'.$player -> id."</b>, ".SEND_YOU." ".$strCorename.".', ".$strDate.", 'R')");
		    $db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES('".$player -> id."', '".YOU_SEND." <b>".$strCorename." ".SEND2." <b><a href=\"view.php?view=".$_POST['gid']."\">".$strReceiversName."</a></b>".L_ID.'<b>'.$_POST['gid']."</b>', ".$strDate.", 'R')") or die("Blad!");
		    error (YOU_SEND." <b>".$strCorename." ".SEND2." <b><a href=\"view.php?view=".$_POST['gid']."\">".$strReceiversName."</a></b>".L_ID.'<b>'.$_POST['gid']."</b>. (<a href=core.php?view=mycores>".A_REFRESH."</a>).");
		  }
	      }
	  }
      }

    elseif ($_GET['view'] == 'library') 
      {
	if (!isset($_GET['id'])) 
	  {
	    $query = $db -> Execute("SELECT count(`id`) FROM core WHERE owner=".$player -> id." AND type='Secret'");
	    $numys = $query -> fields['count(`id`)'];
	    $query -> Close();
	    $query = $db -> Execute("SELECT count(`id`) FROM core WHERE owner=".$player -> id." AND type='Hybrid'");
	    $numyh = $query -> fields['count(`id`)'];
	    $query -> Close();
	    $query = $db -> Execute("SELECT count(`id`) FROM cores WHERE type!='Secret' AND type!='Hybrid'");
	    $numcores = $query -> fields['count(`id`)'];
	    $query -> Close();
	    $tnumc = ($numcores + $numys + $numyh);
	    $query = $db -> Execute("SELECT count(`id`) FROM core WHERE owner=".$player -> id);
	    $yourc = $query -> fields['count(`id`)'];
	    $query -> Close();
	    $cr = $db -> Execute("SELECT * FROM cores WHERE type!='Hybrid' AND type!='Secret'");
	    $arrlink = array();
	    $i = 0;
	    while (!$cr -> EOF) 
	      {
		$query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id." AND name='".$cr -> fields['name']."'");
		$yh = $query -> RecordCount();
		$query -> Close();
		if ($cr -> fields['type'] == 'Plant') 
		  {
		    $typ = C_TYPE1;
		  }
		if ($cr -> fields['type'] == 'Aqua') 
		  {
		    $typ = C_TYPE2;
		  }
		if ($cr -> fields['type'] == 'Material') 
		  {
		    $typ = C_TYPE3;
		  }
		if ($cr -> fields['type'] == 'Element') 
		  {
		    $typ = C_TYPE4;
		  }
		if ($cr -> fields['type'] == 'Alien') 
		  {
		    $typ = C_TYPE5;
		  }
		if ($cr -> fields['type'] == 'Ancient') 
		  {
		    $typ = C_TYPE6;
		  }
		if ($yh > 0) 
		  {
		    $arrlink[$i] = "<li><a href=core.php?view=library&amp;id=".$cr -> fields['id'].">".$cr -> fields['name']."</a> (".$typ.") (posiadane: ".$yh.")";
		  } 
                else 
		  {
		    $arrlink[$i] = "<li>? (?)";
		  }
		$i = $i + 1;
		$cr -> MoveNext();
	      }
	    $cr -> Close();
	    $query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id." AND type='Hybrid'");
	    $yhc = $query -> RecordCount();
	    $query -> Close();
	    if ($yhc > 0) 
	      {
		$arrlink1 = array();
		$i = 0;
		$cr = $db -> Execute("SELECT * FROM cores WHERE type='Hybrid'");
		while (!$cr -> EOF) 
		  {
		    $query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id." AND name='".$cr -> fields['name']."'");
		    $yh = $query -> RecordCount();
		    $query -> Close();
		    if ($yh > 0) 
		      {
			$arrlink1[$i] = "<li><a href=core.php?view=library&amp;id=".$cr -> fields['id'].">".$cr -> fields['name']."</a> (".$cr -> fields['type'].") (".OWNED.": ".$yh.")</li>";
		      } 
                    else 
		      {
			$arrlink1[$i] = "<li>? (?)</li>";
		      }
		    $i = $i + 1;
		    $cr -> MoveNext();
		  }
		$cr -> Close();
	      }
	    $query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id." AND type='Secret'");
	    $ysc = $query -> RecordCount();
	    $query -> Close();
	    if ($ysc > 0) 
	      {
		$arrlink2 = array();
		$i = 0;
		$cr = $db -> Execute("SELECT * FROM cores WHERE type='Secret'");
		while (!$cr -> EOF) 
		  {
		    $query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id." AND name='".$cr -> fields['name']."'");
		    $yh = $query -> RecordCount();
		    $query -> Close();
		    if ($yh > 0) 
		      {
			$arrlink2[$i] = "<li><a href=core.php?view=library&amp;id=".$cr -> fields['id'].">".$cr -> fields['name']."</a> (".$cr -> fields['type'].") (".OWNED.": ".$yh.")</li>";
		      } 
                    else 
		      {
			$arrlink2[$i] = "<li>? (?)</li>";
		      }
		    $i = $i + 1;
		    $cr -> MoveNext();
		  }
		$cr -> Close();
	      }
	    if (isset($arrlink1)) 
	      {
		$smarty -> assign ("Hybridcore1", $arrlink1);
	      }
	    if (isset($arrlink2)) 
	      {
		$smarty -> assign ("Specialcore1", $arrlink2);
	      }
	    $smarty -> assign (array("Name" => $player -> user, 
				     "Plcores" => $yourc, 
				     "Allcores" => $tnumc, 
				     "Normalcore" => $arrlink,
				     "Libinfo1" => LIB_INFO1,
				     "Libinfo2" => LIB_INFO2,
				     "Libinfo3" => LIB_INFO3,
				     "Libinfo4" => LIB_INFO4,
				     "Ncore" => N_CORE,
				     "Score" => S_CORE,
				     "Hcore" => H_CORE));
	  } 
        else 
	  {
	    checkvalue($_GET['id']);
	    $coreinfo = $db -> Execute("SELECT * FROM cores WHERE id=".$_GET['id']);
	    if ($coreinfo -> fields['type'] == 'Plant') 
	      {
		$typ = C_TYPE1;
	      }
	    if ($coreinfo -> fields['type'] == 'Aqua') 
	      {
		$typ = C_TYPE2;
	      }
	    if ($coreinfo -> fields['type'] == 'Material') 
	      {
		$typ = C_TYPE3;
	      }
	    if ($coreinfo -> fields['type'] == 'Element') 
	      {
		$typ = C_TYPE4;
	      }
	    if ($coreinfo -> fields['type'] == 'Alien') 
	      {
		$typ = C_TYPE5;
	      }
	    if ($coreinfo -> fields['type'] == 'Ancient') 
	      {
		$typ = C_TYPE6;
	      }
	    $query = $db -> Execute("SELECT id FROM core WHERE name='".$coreinfo -> fields['name']."' AND owner=".$player -> id);
	    $ycore = $query -> RecordCount();
	    $query -> Close();
	    if ($ycore > 0) 
	      {
		$query = $db -> Execute("SELECT id FROM core WHERE name='".$coreinfo -> fields['name']."'");
		$caught = $query -> RecordCount();
		$query -> Close();
		$smarty -> assign (array("Id" => $coreinfo -> fields['id'], 
					 "Name" => $coreinfo -> fields['name'], 
					 "Type" => $typ, 
					 "Rarity" => $coreinfo -> fields['rarity'], 
					 "Caught" => $caught,
					 "Showcore" => SHOW_CORE,
					 "Maininfo" => MAIN_INFO,
					 "Standid" => STAND_ID,
					 "Ltype" => L_TYPE,
					 "Lrar" => L_RAR,
					 "Lcat" => L_CAT,
					 "Lname" => L_NAME));
		if (!empty ($coreinfo -> fields['desc'])) 
		  {
		    $smarty -> assign ("Description", "+ <b>".DESC."</b><br /><br /><ul><li>".$coreinfo -> fields['desc']."</li></ul>");
		  } 
                else 
		  {
		    $smarty -> assign("Description", '');
		  }
	      } 
            else 
	      {
		error (NO_CORE2);
	      }
	    $coreinfo -> Close();
	  }
      }
    
    /**
     * Core arena
     */
    if ($_GET['view'] == 'arena') 
      {
	if (!isset ($_GET['step']) && !isset($_GET['attack'])) 
	  {
	    $chowaniec = $db -> Execute("SELECT type FROM core WHERE status='Alive' AND active='T' AND owner=".$player -> id);
	    $smarty -> assign (array("Forest" => "<li>".ARENA1, 
				     "Sea" => "<li>".ARENA2, 
				     "Mountains" => "<li>".ARENA3, 
				     "Plant" => "<li>".ARENA4, 
				     "Desert" => "<li>".ARENA5, 
				     "Magic" => "<li>".ARENA6."<br /><br />",
				     "Arenainfo" => ARENA_INFO,
				     "Aheal" => A_HEAL));
	    if ($chowaniec -> fields['type'] == 'Plant') 
	      {
		$smarty -> assign ("Forest", "<li><a href=core.php?view=arena&amp;step=battles&amp;typ=1>".ARENA1."</a></li>");
	      }
	    if ($chowaniec -> fields['type'] == 'Aqua') 
	      {
		$smarty -> assign ("Sea", "<li><a href=core.php?view=arena&amp;step=battles&amp;typ=2>".ARENA2."</a></li>");
	      }
	    if ($chowaniec -> fields['type'] == 'Material') 
	      {
		$smarty -> assign ("Mountains", "<li><a href=core.php?view=arena&amp;step=battles&amp;typ=3>".ARENA3."</a></li>");
	      }
	    if ($chowaniec -> fields['type'] == 'Element') 
	      {
		$smarty -> assign ("Plant", "<li><a href=core.php?view=arena&amp;step=battles&amp;typ=4>".ARENA4."</a></li>");
	      }
	    if ($chowaniec -> fields['type'] == 'Alien') 
	      {
		$smarty -> assign ("Desert", "<li><a href=core.php?view=arena&amp;step=battles&amp;typ=5>".ARENA5."</a></li>");
	      }
	    if ($chowaniec -> fields['type'] == 'Ancient') 
	      {
		$smarty -> assign ("Magic", "<li><a href=core.php?view=arena&amp;step=battles&amp;typ=6>".ARENA6."</a><br /><br /></li>");
	      }
	    $chowaniec -> Close();
	  }
	if (isset ($_GET['step']) && $_GET['step'] == 'battles') 
	  {
	    $chowaniec = $db -> Execute("SELECT type, name FROM core WHERE status='Alive' AND active='T' AND owner=".$player -> id);
	    $test = '';
	    if ($_GET['typ'] == '1') 
	      {
		$test = 'Plant';
	      }
	    if ($_GET['typ'] == '2') 
	      {
		$test = 'Aqua';
	      }
	    if ($_GET['typ'] == '3') 
	      {
		$test = 'Material';
	      }
	    if ($_GET['typ'] == '4') 
	      {
		$test = 'Element';
	      }
	    if ($_GET['typ'] == '5') 
	      {
		$test = 'Alien';
	      }
	    if ($_GET['typ'] == '6') 
	      {
		$test = 'Ancient';
	      }
	    if ($chowaniec -> fields['type'] != $test) 
	      {
		error (ERROR);
	      }
	    $arrlibrary = array();
	    $arrname = array();
	    $arrowner = array();
	    $arrcoreid = array();
	    $i = 0;
	    $clist = $db -> Execute("SELECT * FROM core WHERE status='Alive' AND active='T' AND owner!=".$player -> id." AND type='".$test."'");
	    while (!$clist -> EOF) 
	      {
		$arrlibrary[$i] = $clist -> fields['ref_id'];
		if ($clist -> fields['corename'] == '')
		  {
		    $arrname[$i] = $clist -> fields['name'];
		  }
                else
		  {
		    $arrname[$i] = $clist -> fields['corename']." (".$clist -> fields['name'].")";
		  }
		$arrowner[$i] = $clist -> fields['owner'];
		$arrcoreid[$i] = $clist -> fields['id'];
		$clist -> MoveNext();
		$i = $i + 1;
	      }
	    $clist -> Close();
	    $smarty -> assign(array("Library" => $arrlibrary, 
				    "Corename" => $arrname, 
				    "Owner" => $arrowner, 
				    "Attackid" => $arrcoreid,
				    "Tcore" => T_CORE,
				    "TOwner" => OWNER,
				    "Coptions" => C_OPTIONS));
	  }
	if (isset($_GET['attack'])) 
	  {
	    checkvalue($_GET['attack']);
	    if ($player -> energy < 0.2) 
	      {
		error ("Nie masz tyle energii!");
	      } 
            else 
	      {
		$mycore = $db -> Execute("SELECT * FROM core WHERE active='T' AND owner=".$player -> id);
		if (!$mycore -> fields['id']) 
		  {
		    error (NO_ENERGY);
		  } 
                else 
		  {
		    if ($mycore -> fields['status'] == 'Dead') 
		      {
			error (CORE_DEAD);
		      } 
                    else 
		      {
			$enemy = $db -> Execute("SELECT * FROM core WHERE id=".$_GET['attack']);
			if (!$enemy -> fields['id']) 
			  {
			    error (NO_CORE);
			  }
			$query = $db -> Execute("SELECT * FROM core WHERE owner=".$player -> id." AND id=".$enemy -> fields['id']);
			$numy = $query -> RecordCount();
			$query -> Close();
			if ($numy > 0) 
			  {
			    error (ITS_YOUR);
			  } 
                        else 
			  {
			    if ($enemy -> fields['status'] == 'Dead') 
			      {
				error (CORE_DEAD2);
			      } 
                            else 
			      {
				if ($mycore -> fields['type'] != $enemy -> fields['type']) 
				  {
				    error (YOU_NOT_FIGHT.$enemy -> fields['name'].BAD_TYPE);
				  }
				if ($enemy -> fields['active'] != 'T') 
				  {
				    error (YOU_NOT_FIGHT.$enemy -> fields['name'].NOT_ACTIVE);
				  }
				$yattack = ($mycore -> fields['power'] - $enemy -> fields['defense']);
				if ($yattack <= 0) 
				  {
				    $yattack = 0;
				  }
				$eattack = ($enemy -> fields['power'] - $mycore -> fields['defense']);
				if ($eattack <= 0) 
				  {
				    $eattack = 0;
				  }
				if ($mycore -> fields['corename'] == '')
				  {
				    $strMycorename = $mycore -> fields['name'];
				  }
                                else
				  {
				    $strMycorename = $mycore -> fields['corename'];
				  }
				if ($enemy -> fields['corename'] == '')
				  {
				    $strEnemycorename = $enemy -> fields['name'];
				  }
                                else
				  {
				    $strEnemycorename = $enemy -> fields['corename'];
				  }
				$smarty -> assign(array("Ycorename" => $strMycorename, 
							"Ecoreowner" => $enemy -> fields['owner'], 
							"Ecorename" => $strEnemycorename, 
							"Ecoreattack" => $eattack, 
							"Ycoreattack" => $yattack,
							"Coreb" => CORE_B,
							"Ycore1" => Y_CORE1,
							"Ycore2" => Y_CORE2,
							"Ycore3" => Y_CORE3,
							"Ecore1" => E_CORE1,
							"Ecore2" => E_CORE2,
							"Ecore3" => E_CORE3));
				if ($eattack == $yattack) 
				  {
				    $smarty -> assign ("Result", RESULT1);
				    $strLogentry = LOG_ENTRY4;
				    $intLogenemyid = $player -> id;
				    $strLogenemyname = $player -> id;
				    $intLogid = $enemy -> fields['owner'];
				  } 
                                else 
				  {
				    if ($eattack > $yattack) 
				      {
					$victor = $db -> Execute("SELECT user, id FROM players WHERE id=".$enemy -> fields['owner']);
					$loser = $db -> Execute("SELECT user, id FROM players WHERE id=".$player -> id);
					$strLogentry = LOG_ENTRY2;
					$intLogenemyid = $loser -> fields['id'];
					$strLogenemyname = $loser -> fields['user'];
					$intLogid = $victor -> fields['id'];
				      }  
                                    else 
				      {
					$victor = $db -> Execute("SELECT user, id FROM players WHERE id=".$player -> id);
					$loser = $db -> Execute("SELECT user, id FROM players WHERE id=".$enemy -> fields['owner']);
					$strLogentry = LOG_ENTRY3;
					$intLogenemyid = $victor -> fields['id'];
					$strLogenemyname = $victor -> fields['user'];
					$intLogid = $loser -> fields['id'];
				      }
				    $smarty -> assign ("Result", $victor -> fields['user'].RESULT2.$loser -> fields['user'].RESULT3);
				    if ($victor -> fields['user'] == $player -> user) 
				      {
					$smarty -> assign ("Info", RESULT4.$strMycorename.RESULT5.$loser -> fields['user']." <b>".$strEnemycorename.RESULT6);
					$db -> Execute("UPDATE core SET status='Dead', losses=losses+1 WHERE id=".$enemy -> fields['id']);
					$db -> Execute("UPDATE core SET wins=wins+1 WHERE id=".$mycore -> fields['id']);
					$gain = ceil(($enemy -> fields['power'] + $enemy -> fields['defense']) * 10);
				      } 
                                    else 
				      {
					$smarty -> assign ("Info", RESULT4.$strMycorename.HAS_BEEN.$victor -> fields['user']." <b>".$strEnemycorename.RESULT6);
					$db -> Execute("UPDATE core SET status='Dead', losses=losses+1 WHERE id=".$mycore -> fields['id']);
					$db -> Execute("UPDATE core SET wins=wins+1 WHERE id=".$enemy -> fields['id']);
					$gain = ceil(($mycore -> fields['power'] + $mycore -> fields['defense']) * 10);
				      }
				    $crgain = rand(0,$gain);
				    $mith = ceil($gain / 200);
				    $plgain = rand(0,$mith);
				    $smarty -> assign ("Gains", $victor -> fields['user'].V_GAIN.$crgain.GOLD_COINS.$plgain.C_MITH);
				    $db -> Execute("UPDATE players SET platinum=platinum+".$plgain.", credits=credits+".$crgain." WHERE id=".$victor -> fields['id']);
				    $db -> Execute("UPDATE players SET energy=energy-0.2 WHERE id=".$player -> id);
				    $victor -> Close();
				    $loser -> Close();
				  }
				$strDate = $db -> DBDate($newdate);
				$db -> Execute("INSERT INTO `log` (`owner`, `log`, `czas`, `type`) VALUES('".$intLogid."','".LOG_ENTRY.$intLogenemyid."\">".$strLogenemyname.'</a></b>'.L_ID.'<b>'.$intLogenemyid.'</b>'.$strLogentry."', ".$strDate.", 'R')");
			      }
			  }
			$enemy -> Close();
		      }
		  }
		$mycore -> Close();
	      }
	  }
	if (isset ($_GET['step']) && $_GET['step'] == 'heal') 
	  {
	    $deadcore = $db -> Execute("SELECT `power`, `defense` FROM `core` WHERE `owner`=".$player -> id." AND `status`='Dead'");
	    $numdead = $deadcore -> RecordCount();
	    $cost = 0;
	    $intMithcost = 0;
	    while (!$deadcore -> EOF) 
	      {
		$cost = $cost + (($deadcore -> fields['power'] + $deadcore -> fields['defense']) * 5);
		$deadcore -> MoveNext();
	      }
	    $cost = floor($cost);
	    $intMithcost = floor($cost / 200);
	    $deadcore -> Close();
	    $smarty -> assign(array("Cost" => $cost, 
				    "Number" => $numdead,
				    "Mithcost" => $intMithcost,
				    "Itcost" => IT_COST,
				    "Gold2" => GOLD2,
				    "Gold3" => GOLD3,
				    "Dcores" => D_CORES,
				    "Ayes2" => A_YES2,
				    "Ano2" => A_NO2));
	    if (isset ($_GET['answer']) && $_GET['answer'] == 'yes') 
	      {
		 if ($player->hp == 0)
		   {
		     message('error', 'Nie możesz leczyć swoich chowańców ponieważ jesteś martwy.');
		   }
		elseif ($player->credits < $cost || $player->platinum < $intMithcost) 
		  {
		    message('error', NO_MONEY);
		  } 
                else 
		  {
		    $db -> Execute("UPDATE `core` SET `status`='Alive' WHERE `owner`=".$player -> id." AND `status`='Dead'");
		    $db -> Execute("UPDATE `players` SET `credits`=`credits`-".$cost.", `platinum`=`platinum`-".$intMithcost." WHERE `id`=".$player -> id);
		    message('success', ALL_HEALED);
		  }
	      }
	  }
      }
    
    elseif ($_GET['view'] == 'train') 
      {
	$smarty -> assign ("Trains", $player -> trains);
	$arrname = array();
	$arrcoreid = array();
	$myc = $db -> Execute("SELECT * FROM `core` WHERE `owner`=".$player -> id);
	$i = 0;
	while (!$myc -> EOF) 
	  {
	    if (!empty($myc -> fields['corename']))
	      {
		$arrname[$i] = $myc -> fields['corename']." (".$myc -> fields['name'].")";
	      }
	    else
	      {
		$arrname[$i] = $myc -> fields['name'];
	      }
	    if ($myc->fields['gender'] == 'F')
	      {
		$arrname[$i] .= ' (Samica';
	      }
	    else
	      {
		$arrname[$i] .= ' (Samiec';
	      }
	    $arrname[$i] .= ' Siła: '.$myc->fields['power'].' Obrona: '.$myc->fields['defense'].')';
	    $arrcoreid[$i] = $myc -> fields['id'];
	    $myc -> MoveNext();
	    $i++;
	  }
	$myc -> Close();
	$smarty -> assign(array("Corename" => $arrname, 
				"Coreid1" => $arrcoreid,
				"Traininfo" => TRAIN_INFO,
				"Traininfo2" => TRAIN_INFO2,
				"Trcore" => TR_CORE,
				"Tamount2" => T_AMOUNT2,
				"Tpower2" => T_POWER2,
				"Tdefense2" => T_DEFENSE2,
				"Atrain" => A_TRAIN,
				"Trainmy" => TRAIN_MY));
	if (isset ($_GET['step']) && $_GET['step'] == 'train') 
	  {
	    if (!isset($_POST['train_core']))
	      {
		error(ERROR);
	      }
	    checkvalue($_POST['train_core']);
	    checkvalue($_POST['reps']);
	    if ($_POST['reps'] <= 0) 
	      {
		error (HOW_MANY);
	      } 
	    if ($_POST['technique'] != 'power' && $_POST['technique'] != 'defense') 
	      {
		error (ERROR);
	      }
	    if ($player -> hp == 0) 
	      {
		error (YOU_DEAD);
	      }
	    $objCore = $db->Execute("SELECT `id`, `status`, `owner` FROM `core` WHERE `id`=".$_POST['train_core']) or die($db->ErrorMsg());
	    if (!$objCore->fields['id'])
	      {
		error('Nie ma takiego chowańca.');
	      }
	    if ($objCore->fields['owner'] != $player->id)
	      {
		error('Ten chowaniec nie należy do Ciebie!');
	      }
	    if ($objCore->fields['status'] != 'Alive')
	      {
		error('Nie możesz trenować martwego chowańca.');
	      }
	    $objCore->Close();
	    if ($_POST['reps'] > $player -> trains) 
	      {
		error (NO_TRAIN_P);
	      }  
	    $gain = ($_POST['reps'] * .125);
	    $db -> Execute("UPDATE core SET ".$_POST['technique']."=".$_POST['technique']."+".$gain." WHERE id=".$_POST['train_core']) or error ("stage 1 failed<br />");
	    $db -> Execute("UPDATE players SET trains=trains-".$_POST['reps']." WHERE id=".$player -> id) or error ("stage 2 failed<br />");
	    if ($_POST['technique'] == 'power') 
	      {
		$cecha = T_POWER;
	      }
	    if ($_POST['technique'] == 'defense') 
	      {
		$cecha = T_DEFENSE;
	      }
	    error (YOU_TRAIN.$_POST['reps'].T_AMOUNT.$_POST['reps'].T_TRAIN.$gain." ".$cecha."</b>.");
	  }
      } 

    /**
     * Search for core
     */
    elseif ($_GET['view'] == 'explore') 
      {
	$smarty -> assign(array("Exploreinfo" => EXPLORE_INFO,
				"Asearch" => A_SEARCH,
				"Eamount" => E_AMOUNT,
				"Mith2" => MITH2,
				"Region1" => REGION1,
				"Region2" => REGION2,
				"Region3" => REGION3,
				"Region4" => REGION4,
				"Region5" => REGION5,
				"Region6" => REGION6));
	if (isset ($_GET['next']) && $_GET['next'] == 'yes') 
	  {
	    checkvalue($_POST['repeat']);
	    $rep = ($_POST['repeat'] * 0.1);
	    if (round($player->energy, 1) < round($rep, 1)) 
	      {
		error (NO_ENERGY2);
	      }
	    if ($player->hp == 0) 
	      {
		error (YOU_DEAD2);
	      }
	    if ($_POST['explore'] == 'Forest') 
	      {
		$req = 0;
		$type = 'Plant';
		$common[1] = 1;
		$common[2] = 2;
		$common[3] = 3;
		$uncommon = 4;
		$rare1 = 5;
		$obszar = REGION1;
	      } 
            elseif ($_POST['explore'] == 'Ocean') 
	      {
		$req = 50;
		$type = 'Aqua';
		$common[1] = 6;
		$common[2] = 7;
		$common[3] = 8;
		$uncommon = 9;
		$rare1 = 10;
		$obszar = REGION2;
	      } 
            elseif ($_POST['explore'] == 'Mountains') 
	      {
		$req = 100;
		$type = 'Material';
		$common[1] = 11;
		$common[2] = 12;
		$common[3] = 13;
		$uncommon = 14;
		$rare1 = 15;
		$obszar = REGION3;
	      } 
            elseif ($_POST['explore'] == 'Plains') 
	      {
		$req = 150;
		$type = 'Element';
		$common[1] = 16;
		$common[2] =17;
		$common[3] = 18;
		$uncommon =19;
		$rare1 = 20;
		$obszar = REGION4;
	      } 
            elseif ($_POST['explore'] == 'Desert') 
	      {
		$req = 200;
		$type = 'Alien';
		$common[1] = 21;
		$common[2] = 22;
		$common[3] = 23;
		$uncommon = 24;
		$rare1 = 25;
		$obszar = REGION5;
	      } 
            elseif ($_POST['explore'] == 'Magic') 
	      {
		$req = 250;
		$type = 'Ancient';
		$common[1] = 26;
		$common[2] = 27;
		$common[3] = 28;
		$uncommon = 29;
		$rare1 = 30;
		$obszar = REGION6;
	      } 
            else 
	      {
		error (NO_REGION);
	      }
	    if ($player -> platinum < $req) 
	      {
		error (NO_MITH);
	      }
	    $arrfind1 = array();
	    $arrfind2 = array();
	    $arrfind3 = array();
	    $j = 0;
	    for ($i=0;$i<=$_POST['repeat'];$i++) 
	      {
		$rare = rand(1,3);
		if ($rare == 1) 
		  {
		    $odds = rand(1,50);
		    $chance = rand(1,50);
		    if ($chance == $odds) 
		      {
			$core = rand(1,3);
			$core = $common[$core];
			$coreinfo = $db -> Execute("SELECT * FROM cores WHERE id=".$core);
			if ($coreinfo -> fields['type'] == 'Plant') 
			  {
			    $typ = C_TYPE1;
			    $mith = 0;
			  }
			if ($coreinfo -> fields['type'] == 'Aqua') 
			  {
			    $typ = C_TYPE2;
			    $mith = 50;
			  }
			if ($coreinfo -> fields['type'] == 'Material') 
			  {
			    $typ = C_TYPE3;
			    $mith = 100;
			  }
			if ($coreinfo -> fields['type'] == 'Element') 
			  {
			    $typ = C_TYPE4;
			    $mith = 150;
			  }
			if ($coreinfo -> fields['type'] == 'Alien') 
			  {
			    $typ = C_TYPE5;
			    $mith = 200;
			  }
			if ($coreinfo -> fields['type'] == 'Ancient') 
			  {
			    $typ = C_TYPE6;
			    $mith = 250;
			  }
			$intGender = rand(1,2);
			if ($intGender == 1)
			  {
			    $strGender = C_MALE;
			    $strGen = 'M';
			  }
                        else
			  {
			    $strGender = C_FEMALE;
			    $strGen = 'F';
			  }
			$player -> platinum = ($player -> platinum - $mith);
			$arrfind1[$j] = YOU_FIND.$coreinfo -> fields['name']." ( ".$strGender." )".FIND2.$typ."</b>.";
			if ($coreinfo -> fields['rarity'] == 1) 
			  {
			    $arrfind2[$j] = RARITY1;
			  }
			if ($coreinfo -> fields['rarity'] == 2) 
			  {
			    $arrfind2[$j] = RARITY2;
			  }
			if ($coreinfo -> fields['rarity'] == 3) 
			  {
			    $arrfind2[$j] = RARITY3;
			  }
			$query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id." AND name='".$coreinfo -> fields['name']."'");
			$corenum = $query -> RecordCount();
			$query -> Close();
			if ($corenum <= 0) 
			  {
			    $arrfind3[$j] = ITS_FIRST;
			  } 
                        else 
			  {
			    $arrfind3[$j] = YOU_HAVE;
			  }
			$j = $j + 1;
			$db -> Execute("UPDATE players SET platinum=platinum-".$mith." WHERE id=".$player -> id);
			$db -> Execute("INSERT INTO core (owner, name, type, ref_id, power, defense, gender) VALUES(".$player -> id.",'".$coreinfo -> fields['name']."','".$coreinfo -> fields['type']."',".$core.",".$coreinfo -> fields['power'].",".$coreinfo -> fields['defense'].", '".$strGen."')") or error("Could not add Core.");
			$coreinfo -> Close();
		      }
		  }
		if ($rare == 2) 
		  {
		    $odds = rand(1,250);
		    $chance = rand(1,250);
		    if ($chance == $odds) 
		      {
			$core = $uncommon;
			$coreinfo = $db -> Execute("SELECT * FROM cores WHERE id=".$core);
			if ($coreinfo -> fields['type'] == 'Plant') 
			  {
			    $typ = C_TYPE1;
			    $mith = 0;
			  }
			if ($coreinfo -> fields['type'] == 'Aqua') 
			  {
			    $typ = C_TYPE2;
			    $mith = 50;
			  }
			if ($coreinfo -> fields['type'] == 'Material') 
			  {
			    $typ = C_TYPE3;
			    $mith = 100;
			  }
			if ($coreinfo -> fields['type'] == 'Element') 
			  {
			    $typ = C_TYPE4;
			    $mith = 150;
			  }
			if ($coreinfo -> fields['type'] == 'Alien') 
			  {
			    $typ = C_TYPE5;
			    $mith = 200;
			  }
			if ($coreinfo -> fields['type'] == 'Ancient') 
			  {
			    $typ = C_TYPE6;
			    $mith = 250;
			  }
			$intGender = rand(1,2);
			if ($intGender == 1)
			  {
			    $strGender = C_MALE;
			    $strGen = 'M';
			  }
                        else
			  {
			    $strGender = C_FEMALE;
			    $strGen = 'F';
			  }
			$player -> platinum = ($player -> platinum - $mith);
			$arrfind1[$j] = YOU_FIND.$coreinfo -> fields['name']." ( ".$strGender." )".FIND2.$typ."</b>.";
			if ($coreinfo -> fields['rarity'] == 1) 
			  {
			    $arrfind2[$j] = RARITY1;
			  }
			if ($coreinfo -> fields['rarity'] == 2) 
			  {
			    $arrfind2[$j] = RARITY2;
			  }
			if ($coreinfo -> fields['rarity'] == 3) 
			  {
			    $arrfind2[$j] = RARITY3;
			  }
			$query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id." AND name='".$coreinfo -> fields['name']."'");
			$corenum = $query -> RecordCount();
			$query -> Close();
			if ($corenum <= 0) 
			  {
			    $arrfind3[$j] = ITS_FIRST;
			  } 
                        else 
			  {
			    $arrfind3[$j] = YOU_HAVE;
			  }
			$j = $j + 1;
			$db -> Execute("UPDATE players SET platinum=platinum-".$mith." WHERE id=".$player -> id);
			$db -> Execute("INSERT INTO core (owner, name, type, ref_id, power, defense, gender) VALUES(".$player -> id.",'".$coreinfo -> fields['name']."','".$coreinfo -> fields['type']."',".$core.",".$coreinfo -> fields['power'].",".$coreinfo -> fields['defense'].", '".$strGen."')") or error("Could not add Core.");
			$coreinfo -> Close();
		      }
		  }
		if ($rare == 3) 
		  {
		    $odds = rand(1,500);
		    $chance = rand(1,500);
		    if ($chance == $odds) 
		      {
			$core = $rare1;
			$coreinfo = $db -> Execute("SELECT * FROM cores WHERE id=".$core);
			if ($coreinfo -> fields['type'] == 'Plant') 
			  {
			    $typ = C_TYPE1;
			    $mith = 0;
			  }
			if ($coreinfo -> fields['type'] == 'Aqua') 
			  {
			    $typ = C_TYPE2;
			    $mith = 50;
			  }
			if ($coreinfo -> fields['type'] == 'Material') 
			  {
			    $typ = C_TYPE3;
			    $mith = 100;
			  }
			if ($coreinfo -> fields['type'] == 'Element') 
			  {
			    $typ = C_TYPE4;
			    $mith = 150;
			  }
			if ($coreinfo -> fields['type'] == 'Alien') 
			  {
			    $typ = C_TYPE5;
			    $mith = 200;
			  }
			if ($coreinfo -> fields['type'] == 'Ancient') 
			  {
			    $typ = C_TYPE6;
			    $mith = 250;
			  }
			$intGender = rand(1,2);
			if ($intGender == 1)
			  {
			    $strGender = C_MALE;
			    $strGen = 'M';
			  }
                        else
			  {
			    $strGender = C_FEMALE;
			    $strGen = 'F';
			  }
			$player -> platinum = ($player -> platinum - $mith);
			$arrfind1[$j] = YOU_FIND.$coreinfo -> fields['name']." ( ".$strGender." )".FIND2.$typ."</b>.";
			if ($coreinfo -> fields['rarity'] == 1) 
			  {
			    $arrfind2[$j] = RARITY1;
			  }
			if ($coreinfo -> fields['rarity'] == 2) 
			  {
			    $arrfind2[$j] = RARITY2;
			  }
			if ($coreinfo -> fields['rarity'] == 3) 
			  {
			    $arrfind2[$j] = RARITY3;
			  }
			$query = $db -> Execute("SELECT id FROM core WHERE owner=".$player -> id." AND name='".$coreinfo -> fields['name']."'");
			$corenum = $query -> RecordCount();
			$query -> Close();
			if ($corenum <= 0) 
			  {
			    $arrfind3[$j] = ITS_FIRST;
			  } 
                        else 
			  {
			    $arrfind3[$j] = YOU_HAVE;
			  }
			$j = $j + 1;
			$db -> Execute("UPDATE players SET platinum=platinum-".$mith." WHERE id=".$player -> id);
			$db -> Execute("INSERT INTO core (owner, name, type, ref_id, power, defense, gender) VALUES(".$player -> id.",'".$coreinfo -> fields['name']."','".$coreinfo -> fields['type']."',".$core.",".$coreinfo -> fields['power'].",".$coreinfo -> fields['defense'].", '".$strGen."')") or error("Could not add Core.");
			$coreinfo -> Close();
		      }
		  }
		if ($player -> platinum < $req) 
		  {
		    $smarty -> assign ("Message", NO_MITH);
		    $smarty -> display ('error1.tpl');
		    break;
		  }
	      }
	    $repeat = ($i - 1);
	    $lostenergy = ($repeat * 0.1);
	    $db -> Execute("UPDATE players SET energy=energy-".$lostenergy." WHERE id=".$player -> id);
	    $smarty -> assign(array("Area" => $obszar, 
				    "Find1" => $arrfind1, 
				    "Find2" => $arrfind2, 
				    "Find3" => $arrfind3, 
				    "Repeat" => $repeat,
				    "Youstart" => YOU_START,
				    "Yousearch" => YOU_SEARCH,
				    "Again" => AGAIN,
				    "Ayes" => YES,
				    "Ano" => NO));
	  }
      }
    $smarty -> assign("Asector", A_SECTOR);
  }
else
  {
    $_GET['view'] = '';
    $smarty -> assign(array("Coremain" => CORE_MAIN,
			    "Amycore" => A_MY_CORE,
			    "Alibrary" => A_LIBRARY,
			    "Aarena" => A_ARENA,
			    "Atrain" => A_TRAIN,
			    "Asearch" => A_SEARCH,
			    "Abreed" => A_BREED,
			    "Amonuments" => A_MONUMENTS));
  }

/**
* Initialization of variables
*/
if (!isset($_GET['id'])) 
{
    $_GET['id'] = '';
}
if (!isset($yhc)) 
{
    $yhc = '';
}
if (!isset($ysc)) 
{
    $ysc = '';
}
if (!isset($ycore)) 
{
    $ycore = '';
}
if (!isset($_GET['step'])) 
{
    $_GET['step'] = '';
}
if (!isset($_GET['attack'])) 
{
    $_GET['attack'] = '';
}
if (!isset($_GET['give'])) 
{
    $_GET['give'] = '';
}
if (!isset($_GET['next'])) 
{
    $_GET['next'] = '';
}
if (!isset($_GET['name']))
{
    $_GET['name'] = '';
}

/**
* Assign variables and display page
*/
$smarty -> assign(array("Corepass" => $player -> corepass, 
                        "View" => $_GET['view'], 
                        "Coreid" => $_GET['id'], 
                        "Hybridcore" => $yhc,
                        "Specialcore" => $ysc, 
                        "Yourcore" => $ycore, 
                        "Step" => $_GET['step'], 
                        "Attack" => $_GET['attack'], 
                        "Give" => $_GET['give'],
                        "Next" => $_GET['next'],
                        "Name2" => $_GET['name']));
$smarty -> display ('core.tpl');

require_once("includes/foot.php");
?>
