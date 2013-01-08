<?php
/**
 *   Funkcje pliku:
 *   School - train stats
 *
 *   @name                 : train.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 08.01.2013
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

$title = "Szkolenie";
require_once("includes/head.php");

if ($player->location != 'Altara' && $player->location != 'Ardulith') 
{
    error ("Nie znajdujesz się w mieście.");
}

if ($player->hp == 0) 
{
    error ("Nie możesz się szkolić kiedy jesteś martwy. <a href=\"city.php\">Wróć</a>.");
}

if (!$player->race)
{
    error("Nie wybrałeś jeszcze rasy. <a href=\"city.php\">Wróć</a>.");
}

if (!$player->clas) 
{
    error("Nie wybrałeś jeszcze klasy. <a href=\"city.php\">Wróć</a>.");
}

$intStrcost = ceil($player->oldstats['strength'][2] * 20);
$intAgicost = ceil($player->oldstats['agility'][2] * 20);
$intConcost = ceil($player->oldstats['condition'][2] * 20);
$intSpecost = ceil($player->oldstats['speed'][2] * 20);

if ($player -> location == 'Altara')
{
    $intIntcost = ceil($player->oldstats['inteli'][2] * 20);
    $intWiscost = ceil($player->oldstats['wisdom'][2] * 20);
    $intLess = 0;
}
    else
{
    $intIntcost = ceil(($player->oldstats['inteli'][2] * 20) - (($player->oldstats['inteli'][2] * 20) / 10));
    $intWiscost = ceil(($player->oldstats['wisdom'][2] * 20) - (($player->oldstats['wisdom'][2] * 20) / 10));
    $intLess = 1;
}

switch ($player->race)
  {
  case 'Człowiek':
    $smarty -> assign ("Train", "0,3 energii za trening Siły (".$intStrcost." sztuk złota)<br /> Zręczności (".$intAgicost." sztuk złota)<br /> Szybkości (".$intSpecost." sztuk złota)<br /> Kondycji (".$intConcost." sztuk złota)<br />");
    break;
  case 'Elf':
    $smarty -> assign ("Train", "0,4 energii za trening Siły (".$intStrcost." sztuk złota)<br />lub Kondycji (".$intConcost." sztuk złota)<br /> 0,2 energii za trening Zręczności (".$intAgicost." sztuk złota)<br />lub Szybkości (".$intSpecost." sztuk złota)<br />");
    break;
  case 'Krasnolud':
    $smarty -> assign ("Train", "0,2 energii za trening Siły (".$intStrcost." sztuk złota)<br /> lub Kondycji (".$intConcost." sztuk złota)<br />0,4 energii za trening Zręczności (".$intAgicost." sztuk złota)<br /> lub Szybkości (".$intSpecost." sztuk złota)<br />");
    break;
  case 'Hobbit':
    $smarty -> assign ("Train", "0,4 energii za trening Siły (".$intStrcost." sztuk złota)<br />lub Szybkość (".$intSpecost." sztuk złota)<br /> 0,2 energii za trening Zręczności (".$intAgicost." sztuk złota)<br />lub Kondycji (".$intConcost." sztuk złota)<br />");
    break;
  case 'Jaszczuroczłek':
    $smarty -> assign ("Train", "0,4 energii za trening Zręczność (".$intAgicost." sztuk złota)<br />lub Kondycji (".$intConcost." sztuk złota)<br />0,2 energii za trening Siły (".$intStrcost." sztuk złota)<br />lub Szybkości (".$intSpecost." sztuk złota)<br />");
    break;
  case 'Gnom':
    $smarty -> assign ("Train", "0,4 energii za trening Siły (".$intStrcost." sztuk złota)<br />lub Szybkość (".$intSpecost." sztuk złota)<br />0,3 energii za trening Zręczności (".$intAgicost." sztuk złota)<br />lub Kondycji (".$intConcost." sztuk złota)<br />");
    break;
  default:
    break;
}

switch ($player->clas)
  {
  case 'Wojownik':
  case 'Barbarzyńca':
    $smarty -> assign ("Train2", " oraz 0,06 energii za trening Siły Woli (".$intWiscost." sztuk złota)<br />lub Inteligencji (".$intIntcost." sztuk złota)");
    break;
  case 'Mag':
    $smarty -> assign ("Train2", " oraz 0,2 energii za trening Siły Woli (".$intWiscost." sztuk złota)<br />lub Inteligencji (".$intIntcost." sztuk złota)");
    break;
  case 'Rzemieślnik':
  case 'Złodziej':
    $smarty -> assign ("Train2", " oraz 0,3 energii za trening Siły Woli (".$intWiscost." sztuk złota)<br />lub Inteligencji (".$intIntcost." sztuk złota)");
    break;
  default:
    break;
}

if ($player -> race == 'Gnom') 
{
    $smarty -> assign ("Train2", " oraz 0,4 energii za trening Siły Woli (".$intWiscost." sztuk złota)<br />lub 0,3 energii za trening Inteligencji (".$intIntcost." sztuk złota)");
}

if (isset ($_GET['action']) && $_GET['action'] == 'train') 
{
    if (!isset($_POST['rep']))
    {
        error("Podaj ile razy chcesz ćwiczyć!");
    }
    checkvalue($_POST['rep']);
    $arrStats = array('strength', 'agility', 'inteli', 'speed', 'condition', 'wisdom');
    if (!in_array($_POST['train'], $arrStats)) 
    {
        error ('Zapomnij o tym');
    }
    if ($player->stats[$_POST['train']][2] == $player->stats[$_POST['train']][1])
      {
	error('Nie możesz ćwiczyć tej cechy, ponieważ osiągnęła już ona maksymalny poziom.');
      }
    if ($player -> race == 'Człowiek') 
    {
        $repeat = ($_POST["rep"] * .3);
    }
    elseif ($player -> race == 'Gnom' && ($_POST["train"] == 'agility' || $_POST['train'] == 'condition')) 
    {
        $repeat = ($_POST["rep"] * .3);
    }
    elseif ($player->race == 'Elf')
      {
	if ($_POST['train'] == 'strength' || $_POST['train'] == 'condition')
	  {
	    $repeat = ($_POST["rep"] * .4);
	  }
	elseif ($_POST['train'] == 'agility' || $_POST['train'] == 'speed')
	  {
	    $repeat = ($_POST["rep"] * .2);
	  }
      }
    elseif ($player->race == 'Krasnolud')
      {
	if ($_POST['train'] == 'strength' || $_POST['train'] == 'condition')
	  {
	    $repeat = ($_POST["rep"] * .2);
	  }
	elseif ($_POST['train'] == 'agility' || $_POST['train'] == 'speed')
	  {
	    $repeat = ($_POST["rep"] * .4);
	  }
    }
    elseif ($player->race == 'Jaszczuroczłek')
      {
	if ($_POST['train'] == 'speed' || $_POST['train'] == 'strength')
	  {
	    $repeat = ($_POST["rep"] * .2);
	  }
	elseif ($_POST['train'] == 'condition' || $_POST['train'] == 'agility')
	  {
	    $repeat = ($_POST["rep"] * .4);
	  }
      }
    elseif ($player->race == 'Hobbit' && ($_POST['train'] == 'condition' || $_POST['train'] == 'agility'))
      {
	$repeat = ($_POST["rep"] * .2);
      }
    elseif (($player -> race == 'Hobbit' || $player -> race == 'Gnom') && ($_POST["train"] == 'speed' || $_POST['train'] == 'strength')) 
    {
        $repeat = ($_POST["rep"] * .4);
    }
    if ($player -> clas == 'Wojownik' && ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom')) 
    {
      $repeat = ($_POST["rep"] * .4);
    }
    elseif ($player -> clas == 'Mag' && ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom')) 
    {
      $repeat = ($_POST["rep"] * .2);
    }
    elseif (($player -> clas == 'Rzemieślnik' || $player -> clas == 'Barbarzyńca' || $player -> clas == 'Złodziej') && ($_POST['train'] == 'inteli' || $_POST['train'] == 'wisdom')) 
    {
      $repeat = ($_POST["rep"] * .3);
    }
    if ($player -> race == 'Gnom' && $_POST["train"]) 
    {
        if ($_POST['train'] == 'wisdom') 
        {
            $repeat = ($_POST["rep"] * .4);
        }
    }
    $repeat = round($repeat, 1);
    $blnLess = FALSE;
    $fltStat2 = $player->oldstats[$_POST['train']][2];
    switch ($_POST['train'])
      {
      case 'strength':
	$cecha = "Siły";
	break;
      case 'agility':
	$cecha = "Zręczności";
	break;
      case 'inteli':
	$cecha = "Inteligencji";
	if ($player->location == 'Ardulith')
	  {
	    $blnLess = TRUE;
	  }
	break;
      case 'speed':
	$cecha = "Szybkości";
	break;
      case 'condition':
	$cecha = "Kondycji";
	break;
      case 'wisdom':
	$cecha = "Siły Woli";
	if ($player->location == 'Ardulith')
	  {
	    $blnLess = TRUE;
	  }
	break;
      default:
	break;
    }
    $intCost2 = 0;
    if ($blnLess)
      {
	$intCost2 += (round(($fltStat2 * 20) - (($fltStat2 * 20) / 10), 0)) * $_POST['rep'];
      }
    else
      {
	$intCost2 += ($fltStat2 * 20) * $_POST['rep'];
      }
    if ($repeat > $player -> energy) 
      {
        message('error', "Nie masz wystarczającej ilości energii.");
      }
    elseif ($player -> credits < $intCost2) 
      {
        message('error', "Nie możesz tyle ćwiczyć, ponieważ nie stać ciebie na to! <br />Potrzebujesz ".$intCost2." sztuk złota.");
      }
    else
      {
	$player->checkexp(array($_POST['train'] => ($_POST['rep'] * 5)), $player->id, 'stats');
	$db -> Execute("UPDATE `players` SET `energy`=`energy`-".$repeat.", `credits`=`credits`-".$intCost2." WHERE `id`=".$player -> id);
	message('success', "Zyskujesz <b>".($_POST['rep'] * 5)." doświadczenia do ".$cecha."</b>. Zapłaciłeś(aś) za to ".$intCost2." sztuk złota.");
      }
    $_GET['action'] = '';
}

/**
* Initialization ov variables
*/
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
}
if (!isset($_POST['rep']))
  {
    $_POST['rep'] = 0;
  }

if ($player->location == 'Altara')
  {
    $strDesc =  "Ogromne filary podtrzymujące sklepienie budynku pamiętają wszystko, co działo się w Szkole Vallheryjskiej. Świadczą o tym ich odrapane wykończenia. Podchodzisz bliżej i widzisz pomieszczenie do złudzenia przypominające Arenę Walk. Na środku sali dwóch krasnoludów walczy ze sobą na ogromne topory obosieczne, które zdają się być większe od nich samych. Nieco dalej elficka para uczy się strzelać do ruchomego celu, którym jest maleńki gnom biegający po podwyższeniu stojącym w odległości 20 metrów od pary. W pewnym momencie słyszysz ruch za swoimi plecami, odwracasz się szybko a w ręku Twym już widać broń. Stoi przed Tobą starzec, który chrypliwym głosem mówi:<br /><br />- Witaj w Szkole Umięjętności w ".$city1a.". Jeżeli chciałbyś trenować swoją krzepę, szybkość czy nawet zgłębiać tajniki magii możesz to zrobić u nas. Za odpowiednią opłatą oczywiście. Starzec podniósł dłoń pokazując tablicę z Twoim imieniem. Zdziwiony skąd oni wiedzą jak się nazywasz podchodzisz blizej i czytasz ceny odpowiednie do Twojej postaci.<br /> ";
  }
else
  {
    $strDesc = "Uniwersytet imienia ".$city2."a ... Stoisz przed wierzbą, na konarach której mieści się uniwersytet założony przez ".$city2."a, a po jego śmierci nazwany tym imieniem. Możesz tu poświęcić swój czas w celu zdobycia większych umiejętności.";
  }

$arrToptions = array();
foreach ($player->stats as $name => $arrStats)
{
  if ($arrStats[2] < $arrStats[1])
    {
      $arrToptions[$name] = $arrStats[0];
    }
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Traininfo" => $strDesc,
			"Toptions" => $arrToptions,
                        "Iwant" => "Chcę trenować moją cechę",
                        "Tamount" => "razy",
                        "Atrain" => "Trenuj",
			"Tinfo" => 'Podaj ile razy chcesz trenować daną cechę.',
			"Plrace" => $player->race,
			"Plclass" => $player->clas,
			"Tcosts" => $player->oldstats['strength'][2].', '.$player->oldstats['agility'][2].', '.$player->oldstats['inteli'][2].', '.$player->oldstats['speed'][2].', '.$player->oldstats['condition'][2].', '.$player->oldstats['wisdom'][2].', '.$intLess,
                        "Action" => $_GET['action'],
			"Rep" => $_POST['rep']));
$smarty -> display ('train.tpl');

require_once("includes/foot.php");
?>
