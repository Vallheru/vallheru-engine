<?php
/**
 *   File functions:
 *   Players list
 *
 *   @name                 : memberlist.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 08.11.2012
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

$title = "Lista mieszkańców"; 
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/memberlist.php");

/**
* Assign variables to template
*/
$smarty -> assign(array("Previous" => '', "Next" => ''));

if (!isset($_POST['id'])) 
{
    $_POST['id'] = 0;
}

if (isset($_GET['szukany']))
{
    $_POST['szukany'] = $_GET['szukany'];
}

$strSearch = '';
if (isset($_POST['szukany']))
{
    $_POST['szukany'] = strip_tags($_POST['szukany']);
    $_POST['szukany'] = str_replace("*","%", $_POST['szukany']);
    $strSearch = $db -> qstr($_POST['szukany'], get_magic_quotes_gpc());
    if ($strSearch == "''")
    {
        $strSearch = '';
    }
}

if (empty($strSearch) && $_POST['id'] == 0) 
  {
    $msel = $db -> Execute("SELECT count(`id`) FROM `players`");
  } 
 else 
   {
     $_POST['id'] = intval($_POST['id']);
     if ($_POST['id'] < 0) 
       {
	 error (ERROR);
       }
     if (!empty($strSearch) && $_POST['id'] == 0) 
       {
	 $msel = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `user` LIKE ".$strSearch);
       } 
     elseif (!empty($strSearch) && $_POST['id'] > 0) 
       {
	 $msel = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `id`=".$_POST['id']." AND `user` LIKE ".$strSearch);
       } 
     elseif (empty($strSearch) && $_POST['id'] > 0) 
       {
	 $msel = $db -> Execute("SELECT count(`id`) FROM `players` WHERE `id`=".$_POST['id']);
       }
   }

$graczy = $msel -> fields['count(`id`)'];
$msel -> Close();
if ($graczy == 0 && isset($_POST['szukany'])) 
{
    $strMessage = NO_PLAYER.$_POST['szukany'];
}
if ($graczy == 0 && isset($_POST['id']) && $_POST['id'] > 0) 
{
    $strMessage = NO_PLAYER2.$_POST['id'];
}
$pages = ceil($graczy / 30);
if (isset($_GET['page']))
  {
    checkvalue($_GET['page']);
    $page = $_GET['page'];
  }
 else
   {
     $page = 1;
   }
if ($page > $pages)
  {
    $page = $pages;
  }
if (!isset ($_GET['lista'])) 
{
    $_GET['lista'] = 'id';
}
if (!in_array($_GET['lista'], array('id', 'user', 'rank', 'rasa', 'miejsce', 'shortrpg'))) 
  {
    error(ERROR);
  }
if (isset($_GET['ip']))
  {
    $_POST['ip'] = $_GET['ip'];
  }
if (!isset($_GET['order']))
  {
    $_GET['order'] = 'ASC';
    $strOrder = 'ASC';
  }
else
  {
    if ($_GET['order'] == 'ASC')
      {
	$strOrder = 'DESC';
      }
    elseif ($_GET['order'] == 'DESC')
      {
	$strOrder = 'ASC';
      }
    else
      {
	error('Zapomnij o tym.');
      }
  }
if (empty($_POST['szukany']) && $_POST['id'] == 0 && empty($_POST['ip'])) 
  {
    $mem = $db->GetAll("SELECT `id`, `user`, `rank`, `rasa`, `gender`, `shortrpg`, `tribe`, `miejsce` FROM `players` ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".(30 * ($page - 1)).", 30");
  } 
elseif  (!empty($_POST['szukany']) && $_POST['id'] == 0) 
{
  $mem = $db->GetAll("SELECT `id`, `user`, `rank`, `rasa`, `gender`, `shortrpg`, `tribe`, `miejsce` FROM `players` WHERE `user` LIKE ".$strSearch." ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".(30 * ($page - 1)).", 30");
} 
elseif (!empty($_POST['szukany']) && $_POST['id'] > 0) 
{
  $mem = $db->GetAll("SELECT `id`, `user`, `rank`, `rasa`, `gender`, `shortrpg`, `tribe`, `miejsce` FROM `players` WHERE `id`=".$_POST['id']." AND `user` LIKE ".$strSearch." ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".(30 * ($page - 1)).", 30");
} 
elseif (empty($_POST['szukany']) && $_POST['id'] > 0) 
{
  $mem = $db->GetAll("SELECT `id`, `user`, `rank`, `rasa`, `gender`, `shortrpg`, `tribe`, `miejsce` FROM `players` WHERE `id`=".$_POST['id']." ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".(30 * ($page - 1)).", 30");
}
elseif(!empty($_POST['ip']))
{
  if ($player -> rank != 'Admin' && $player -> rank != 'Staff')
    {
      error(NO_PERM);
    }
  $_POST['ip'] = str_replace("*","%", $_POST['ip']);
  $mem = $db->GetAll("SELECT `id`, `user`, `rank`, `rasa`, `gender`, `shortrpg`, `tribe`, `miejsce` FROM `players` WHERE `ip` LIKE '".$_POST['ip']."' ORDER BY `".$_GET['lista']."` ".$_GET['order']." LIMIT ".(30 * ($page - 1)).", 30");
}

require_once('includes/ranks.php');
if (is_array($mem))
  {
    foreach ($mem as &$arrMember)
      {
	$arrMember['rank'] = selectrank($arrMember['rank'], $arrMember['gender']);
	if (strlen($arrMember['shortrpg']) > 0)
	  {
	    $arrMember['shortrpg'] = '<a href="roleplay.php?view='.$arrMember['id'].'">'.$arrMember['shortrpg'].'</a>';
	  }
      }
  }

/**
* Initialization of variable
*/
if (isset($strMessage))
  {
    message('error', $strMessage);
  }

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Plid" => PL_ID,
			"Plname" => PL_NAME,
			"Plrank" => PL_RANK,
			"Plrace" => PL_RACE,
			"Plroleplay" => "Profil fabularny",
			"Pllocation" => "Lokacja",
			"Search" => SEARCH,
			"Search2" => SEARCH2,
			"Splayer" => S_PLAYER,
			"Asearch" => A_SEARCH,
			"Searchip" => SEARCH_IP,
			"Searchinfo" => SEARCH_INFO,
			"Rank2" => $player -> rank,
			"Playerslist" => $mem,
			"Tpages" => $pages,
			"Tpage" => $page,
			"Fpage" => "Idź do strony:",
			"Mlist" => $_GET['lista'],
			"Torder" => $strOrder,
			"Torder2" => $_GET['order']));
$smarty -> display ('memberlist.tpl');

require_once("includes/foot.php");
?>
