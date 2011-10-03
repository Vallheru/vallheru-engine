<?php
/**
 *   File functions:
 *   Polish language for alchemik.php
 *
 *   @name                 : alchemik.php                            
 *   @copyright            : (C) 2004,2005,2006,2011 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@tuxfamily.org>
 *   @version              : 1.4
 *   @since                : 03.10.2011
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

if (!isset($_GET['alchemik']))
{
    define("WELCOME", "Witaj w pracowni alchemika. Tutaj możesz wyrabiać różne mikstury. Aby móc je wykonywać musisz najpierw posiadać przepis na odpowiednią miksturę oraz odpowiednią ilość ziół.");
    define("A_RECIPES", "Kup przepis na miksturę");
    define("A_MAKE", "Idź do pracowni");
    define("A_ASTRAL", "Wykonaj astralną miksturę");
}

if (isset ($_GET['alchemik']) && $_GET['alchemik'] == 'przepisy') 
{
    define("RECIPES_INFO", "Witaj w sklepie dla alchemików. Tutaj możesz kupić przepisy mikstur, które chcesz wykonywać. Aby kupić dany przepis, musisz mieć przy sobie odpowiednią ilość sztuk złota. Oto lista dostępnych przepisów:");
    define("R_NAME", "Nazwa");
    define("R_COST", "Cena");
    define("R_LEVEL", "Poziom");
    define("R_OPTION", "Opcje");
    define("A_BUY", "Kup");
    if (isset($_GET['buy'])) 
    {
        define("P_YOU_HAVE", "Masz już taki przepis!");
        define("NO_RECIPE", "Nie ma takiego przepisu. Wróć do <a href=\"alchemik.php?alchemik=przepisy\">sklepu</a>.");
        define("BAD_TYPE", "Tutaj tego nie sprzedasz");
        define("NO_MONEY", "Nie stać cię!");
        define("E_DB", "Nie mogę dodać do bazy danych!");
        define("YOU_PAY", "Zapłaciłeś");
        define("AND_BUY", "sztuk złota, i kupiłeś za to nowy przepis na");
    }
}

if (isset ($_GET['alchemik']) && $_GET['alchemik'] == 'pracownia') 
{
    if (!isset($_GET['rob']))
    {
        define("ALCHEMIST_INFO", "Tutaj możesz wykonywać mikstury co do których masz przepisy. Aby wykonać miksturę, musisz posiadać również odpowiednią ilość ziół. Każda próba kosztuje ciebie 1 punkt energii. Nawet za nieudaną próbę dostajesz 0,01 do umiejętności.<br /> Oto lista mikstur, które możesz wykonywać:");
        define("R_NAME", "Nazwa");
        define("R_LEVEL", "Poziom");
        define("R_ILLANI", "Illani");
        define("R_ILLANIAS", "Illanias");
        define("R_NUTARI", "Nutari");
        define("R_DYNALLCA", "Dynallca");
    }
    if (isset($_GET['dalej'])) 
    {
        define("DEAD_PLAYER", "Nie możesz wykonywać mikstur ponieważ jesteś martwy!");
        define("P_START", "Spróbuj");
        define("P_AMOUNT", "razy");
        define("A_MAKE", "wykonać");
    }
    if (isset($_GET['rob'])) 
    {
        define("NO_HERBS", "Nie masz tylu ziół!");
        define("NO_ENERGY", "Nie masz tyle energii!");
        define("NO_RECIPE", "Nie masz takiego przepisu");
        define("E_DB", "Nie mogę odczytać z bazy danych!");
        define("E_DB2", "Nie mogę dodać mikstury! ");
        define("YOU_MAKE", "Wykonałeś");
        define("P_GAIN", "razy. Zdobywasz");
        define("EXP_AND", "PD oraz");
        define("ALCHEMY_LEVEL", "poziomu w umiejętności Alchemia.");
    }
}

if (isset($_GET['alchemik']) && $_GET['alchemik'] == 'astral')
{
    define("HERB1", "Illani");
    define("HERB2", "Nutari");
    define("HERB3", "Illanias");
    define("HERB4", "Dynalca");
    define("POTION1", "Magiczna esensja");
    define("POTION2", "Gwiezdna maść");
    define("POTION3", "Eliksir Illuminati");
    define("POTION4", "Astralne medium");
    define("POTION5", "Magiczny absynt");
    define("WELCOME", "Tutaj możesz wykonywać różne astralne mikstury. W danym momencie możesz wykonywać tylko te, których przepisy posiadasz.");
    define("NO_PLAN", "Nie masz takiego przepisu");
    define("A_BUILD", "Wykonuj");
    define("T_NAME", "Nazwa");
    define("NO_AMOUNT", "Nie masz takiej ilości ");
    define("NO_ENERGY", "Nie masz takiej ilości energii");
    define("YOU_MAKE", "Wykonałeś ");
    define("YOU_GAIN11", "Zdobywasz ");
    define("YOU_GAIN12", " punktów doświadczenia oraz ");
    define("YOU_GAIN13", " poziom(y) w umiejętności alchemia. ");
    define("YOU_FAIL", "Próbowałeś wykonać ");
    define("YOU_FAIL2",", niestety nie udało się.");
    define("YOU_USE", "Zużyłeś na to:<br />");
}
?>
