<?php
/**
 *   File functions:
 *   Activation account
 *
 *   @name                 : aktywacja.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.4
 *   @since                : 05.09.2011
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

require 'libs/Smarty.class.php';
require_once ('includes/config.php');

$smarty = new Smarty;

$smarty->compile_check = true;

/**
* Check avaible languages
*/    
$arrLanguage = scandir('languages/', 1);
$arrLanguage = array_diff($arrLanguage, array(".", "..", "index.htm"));

/**
* Get the localization for game
*/
$strLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
foreach ($arrLanguage as $strTrans)
{
    if (strpos($strLanguage, $strTrans) === 0)
      {
	$strTranlsation = $strTrans;
	break;
      }
}
if (!isset($strTranslation))
{
    $strTranslation = 'pl';
}
require_once("languages/".$strTranslation."/aktywacja.php");

$smarty -> assign(array("Gamename" => $gamename, 
                        "Meta" => '',
                        "Charset" => CHARSET));

if (isset ($_GET['kod'])) 
  {
    if (intval($_GET['kod']) < 1) 
    {
        $smarty -> assign ("Error", ERROR);
        $smarty -> display ('error.tpl');
        exit;
    }
    $aktiv = $db -> Execute("SELECT * FROM aktywacja WHERE aktyw=".$_GET['kod']);
    if (!isset($aktiv -> fields['lang']))
    {
        require_once("languages/".$strTranslation."/aktywacja.php");
    }
        else
    {
        require_once("languages/".$aktiv -> fields['lang']."/aktywacja.php");
    }
    while (!$aktiv -> EOF) 
    {
        if ($_GET['kod'] == $aktiv -> fields['aktyw']) 
        {
            $db -> Execute("INSERT INTO `players` (`user`, `email`, `pass`, `refs`, `lang`, `ip`) VALUES('".$aktiv -> fields['user']."','".$aktiv -> fields['email']."','".$aktiv -> fields['pass']."',".$aktiv -> fields['refs'].",'".$aktiv -> fields['lang']."', '".$aktiv -> fields['ip']."')");
            $db -> Execute("DELETE FROM `aktywacja` WHERE `aktyw`=".$_GET['kod']);
            
            $time = date("H:i:s");
            $hour = explode(":", $time);
            $newhour = $hour[0] + 0;
            if ($newhour > 23) 
            {
                $newhour = $newhour - 24;
            }
            $arrtime = array($newhour, $hour[1], $hour[2]);
            $newtime = implode(":",$arrtime);

            $query = $db -> Execute("SELECT count(*) FROM `players`");
            $nump = $query -> fields['count(*)'];
            $query -> Close();
    
            $span = (time() - 180);
            $objQuery = $db -> Execute("SELECT count(*) FROM `players` WHERE `lpv`>=".$span);
            $intNumo = $objQuery -> fields['count(*)'];
            $objQuery -> Close();

            $smarty->assign( array ("Time" => $newtime, 
                                    "Players" => $nump, 
                                    "Online" => $intNumo, 
                                    "Email" => EMAIL,
                                    "Password" => PASSWORD,
                                    "Login" => LOGIN,
                                    "Lostpasswd" => LOST_PASSWORD,
                                    "Ctime" => CURRENT_TIME,
                                    "Whave" => WE_HAVE,
                                    "Registered" => REGISTERED,
                                    "Ingame" => IN_GAME,
                                    "Youraccount" => YOUR_ACCOUNT,
                                    "Here" => HERE,
                                    "Tologin" => TO_LOGIN,
                                    "Meta" => '',
                                    "Welcome" => WELCOME,
                                    "Register" => REGISTER,
                                    "Rules" => RULES,
                                    "Links" => LINKS,
                                    "Forums" => FORUMS));
            $smarty -> display ('activ.tpl');
            break;
        }
        $aktiv -> MoveNext();
    }
    $aktiv -> Close();
  }

$db -> Close();
?>
