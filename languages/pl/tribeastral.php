<?php
/**
 *   File functions:
 *   Polish language for tribe astral vault
 *
 *   @name                 : tribeastral.php                            
 *   @copyright            : (C) 2006,2007,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 30.07.2012
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

define("ERROR", "Zapomnij o tym!");
define("NO_CLAN", "Nie jesteś w klanie.");
define("A_MAIN", "Główna");
define("A_DONATE", "Dotuj");
define("A_MEMBERS", "Członkowie");
define("A_ARMOR", "Zbrojownia");
define("A_POTIONS", "Magazyn");
define("A_MINERALS", "Skarbiec");
define("A_HERBS", "Zielnik");
define("A_LEFT", "Opuść klan");
define("A_LEADER", "Opcje przywódcy");
define("A_FORUMS", "Forum klanu");
define("A_ASTRAL", "Astralny skarbiec");
define("MAP1", "Plan demoniczny");
define("MAP2", "Plan ognisty");
define("MAP3", "Plan piekielny");
define("MAP4", "Plan pustynny");
define("MAP5", "Plan wodny");
define("MAP6", "Plan niebiański");
define("MAP7", "Plan śmiertelny");
define("PLAN1", "Astralny komponent");
define("PLAN2", "Gwiezdny portal");
define("PLAN3", "Świetlisty obelisk");
define("PLAN4", "Płomienny znicz");
define("PLAN5", "Srebrzysta fontanna");
define("RECIPE1", "Magiczna esensja");
define("RECIPE2", "Gwiezdna maść");
define("RECIPE3", "Eliksir Illuminati");
define("RECIPE4", "Astralne medium");
define("RECIPE5", "Magiczny absynt");
define("COMP1", "Ząb Glabrezu");
define("COMP2", "Ognisty pył");
define("COMP3", "Pazur Zgłębiczarta");
define("COMP4", "Łuska Skorpendry");
define("COMP5", "Macka Krakena");
define("COMP6", "Piorun Tytana");
define("COMP7", "Żebro Licha");
define("CONST1", "Astralny komponent");
define("CONST2", "Gwiezdny portal");
define("CONST3", "Świetlny obelisk");
define("CONST4", "Płomienny znicz");
define("CONST5", "Srebrzysta fontanna");
define("POTION1", "Magiczna esensja");
define("POTION2", "Gwiezdna maść");
define("POTION3", "Eliksir Illuminati");
define("POTION4", "Astralne medium");
define("POTION5", "Magiczny absynt");
define("MAGICCOMP", "Magiczne składniki");
define("MAGICCONST", "Magiczne konstrukcje");
define("MAGICPOTIONS", "Magiczne mikstury");
define("ADAMANTIUM", "brył adamantium");
define("CRYSTAL", "kryształów");
define("METEOR", "kawałków meteorytu");
define("NO_MITHRIL", "Klan nie ma tyle mithrilu!");
define("NO_MINERAL", "Klan nie ma takiej ilości ");
define("PIECE", " kawałek mapy/planu <b>");
define("COMPONENT", " kompletny komponent <b>");
define("M_AMOUNT", "</b> sztuk: ");

if (!isset($_GET['action'])) 
{
    define("WARE_INFO", "Witaj w astralnym skarbcu klanu. Tutaj są składowane mapy, plany oraz przepisy astralne należące do klanu niezbędne do stworzenia astralnej machiny. Każdy członek klanu może ofiarować klanowi jakiś komponent astralny ale tylko przywódca lub osoba upoważniona przez niego może darować dany komponent członkom swojego klanu. Co chcesz zrobić?");
    define("A_ADD", "Dać przedmiot do klanu");
    define("A_SHOW", "Zobaczyć listę przedmiotów w astralnym skarbcu klanu (mapy, plany, przepisy)");
    define("A_SHOW2", "Zobaczyć listę przedmiotów w astralnym skarbcu klanu (komponenty, konstrukcje, mikstury)");
    define("A_GIVE", "Daj przedmiot graczowi");
    define("BUY_SAFE", "Rozbuduj skrytkę na astralne komponenty (rozbudowa na ");
    define("BUY_SAFE2", " poziom). Koszt: ");
    define("ASTRAL_GOLD", " sztuk złota ");
    define("ASTRAL_MITH", " sztuk mithrilu ");
}

if (isset($_GET['action']) && $_GET['action'] == 'view')
{
    define("T_NAME", "Nazwa");
    define("T_MAPS", "Kawałki map");
    define("T_PLANS", "Kawałki planów");
    define("T_RECIPES", "Kawałki przepisów");
    define("YOU_MERGE", "Skompletowałeś plan");
    define("T_MAPS2", "Kompletne mapy");
    define("T_PLANS2", "Kompletne plany");
    define("T_RECIPES2", "Kompletne przepisy");
}

if (isset($_GET['action']) && $_GET['action'] == 'add')
{
    define("ADD_TO", "Dodaj do astralnego skarbca kawałek komponentu (mapy, planu, przepisu)");
    define("PIECE_NUMBER", "numer");
    define("ASTRAL_AMOUNT", "ilość");
    define("A_ADD", "Dodaj");
    define("NO_AMOUNT", "Nie masz tylu sztuk tego przedmiotu!");
    define("YOU_ADD", "Dodałeś do astralnego skarbca klanu");
    define("T_PLAYER1", "Gracz ");
    define("T_PLAYER2", ", ID ");
    define("HE_ADD", "</b> dodał do astralnego skarbca klanu");
    define("ADD_TO2", "Dodaj do astralnego skarbca przedmiot (komponent, konstrukcję, miksturę)");
}

if (isset($_GET['action']) && $_GET['action'] == 'give')
{
    define("ADD_TO", "Daj kawałek komponentu (mapy, planu, przepisu) graczowi:");
    define("PIECE_NUMBER", "numer");
    define("ASTRAL_AMOUNT", "ilość");
    define("A_GIVE", "Daj");
    define("NO_AMOUNT", "Nie ma tylu sztuk tego przedmiotu w astralnym skarbcu!");
    define("NO_PERM", "Nie masz uprawnień aby tutaj przebywać!");
    define("YOU_GIVE", "Przekazano z astralnego skarbca klanu");
    define("YOU_GET", "Dostałeś od klanu");
    define("ADD_TO2", "Daj kompletny komponent (mapa, plan, przepis) graczowi:");
    define("ADD_TO3", "Daj przedmiot (komponent, konstrukcję, miksturę) graczowi:");
    define("TO_PLAYER1", " członkowi klanu ");
    define("TO_PLAYER2", " , ID ");
    define("NO_PLAYER", "Nie ma takiego gracza!");
}

if (isset($_GET['action']) && $_GET['action'] == 'safe')
{
    define("SAFE_ENOUGH", "Nie możesz rozbudować sejfu ponieważ już osiągnąłeś maksymalny jego poziom!");
    define("NO_MONEY", "Klan nie ma tylu sztuk złota!");
    define("YOU_UPGRADE", "Rozbudowałeś klanowy sejf na astralne komponenty.");
}
?>
