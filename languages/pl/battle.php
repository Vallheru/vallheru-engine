<?php
/**
 *   File functions:
 *   Polish language for battle arena
 *
 *   @name                 : battle.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.6
 *   @since                : 10.08.2012
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

define("ERROR", "Zapomij o tym!");
define("T_SENDER", "Herold");
define("T_SUBJECT", "Przebieg walki z ");
define("T_SUB_ID", " ID: ");

if (isset ($_GET['action']) && ($_GET['action'] == 'showalive' || $_GET['action'] == 'levellist')) 
{
    define("R_ADMIN", "Władca");
    define("R_STAFF", "Książę");
    define("R_MEMBER", "Mieszkaniec");
    define("L_ID", "ID");
    define("L_NAME", "Imię");
    define("L_RANK", "Ranga");
    define("L_CLAN", "Klan");
    define("L_OPTION", "Opcje");
    define("A_ATTACK", "Atakuj");
    define("OR_BACK", "Lub możesz zawsze...");
    define("B_BACK", "zawrócić");
}

if (isset ($_GET['action']) && $_GET['action'] == 'showalive')
{
    define("SHOW_INFO", "Pokaż wszystkich żywych na poziomie");
}

if (isset ($_GET['action']) && $_GET['action'] == 'levellist') 
{
    define("S_LEVEL", "Podaj początkowy poziom");
    define("E_LEVEL", "Podaj końcowy poziom");
    define("SHOW_ALL", "Pokaż żywych na poziomach od");
    define("TO_LEVEL", "do");
    define("A_GO", "Idź");
}

if (isset($_GET['battle'])) 
{
    define("ACCOUNT_FREEZED", "Nie możesz atakować tego gracza, ponieważ jego konto jest zablokowane");
    define("NO_PLAYER", "Nie ma takiego gracza!");
    define("SELF_ATTACK", "Nie możesz atakować samego siebie!");
    define("IS_DEAD", "jest obecnie martwy.");
    define("NO_ENERGY", "Nie masz wystarczająco dużo energii.");
    define("YOU_DEAD", "Jesteś martwy.");
    define("YOUR_CLAN", "Nie atakuj członków swojego klanu!");
    define("TOO_YOUNG", "Nie możesz atakować innych, tak samo jak inni nie mogą atakować ciebie, ponieważ jesteś młodym graczem");
    define("TOO_YOUNG2", "Nie możesz atakować młodych graczy!");
    define("NO_CLASS", "Nie możesz atakować innych graczy, dopóki nie wybierzesz profesji!");
    define("NO_CLASS2", "Nie możesz atakować gracza, który nie wybrał jeszcze profesji!");
    define("SELECT_WEP", "Nie możesz jednocześnie walczyć bronią i czarem. Wybierz jeden rodzaj walki!");
    define("NO_WEAPON", "Wybierz jakiś rodzaj walki(magia lub broń)!");
    define("NO_ARROWS", "Nie możesz walczyć łukiem ponieważ nie masz strzał w kołczanie!");
    define("BAD_CLASS", "Tylko mag może walczyć używając czarów!");
    define("TOO_LOW", "Nie możesz zaatakować gracza na niższym poziomie.");
    define("IMMUNITED", "Nie możesz walczyć, ponieważ masz immunitet!");
    define("IMMUNITED2", "Nie możesz zaatakować gracza z immunitetem!");
    define("NO_MANA", "Nie możesz atakować przy pomocy czaru, ponieważ nie masz punktów magii!");
    define("BAD_LOCATION", "Nie możesz zaatakować gracza, ponieważ nie przebywa on w tej samej lokacji co ty!");
    define("PLAYER_R", "Nie możesz atakować gracza, ponieważ obecnie odpoczywa!");
    define("PLAYER_F", "Nie możesz zaatakować tego gracza, ponieważ obecnie walczy z potworem");
    define("P_DODGE", "uniknął ataku");
    define("P_ATTACK", "trafia");
    define("AND_KILL", "jednym potężnym ciosem i zabija go!");
    define("HP_LEFT", "zostało");
    define("AND_KILL2", "jednym potężnym zaklęciem i zabija go!");
    define("B_DAMAGE", "i zadaje");
    define("B_BAR", "lecz ten odpiera atak");
    define("B_NO_WIN", "Walka nierozstrzygnięta!");
    define("YOU_ATTACK_BUT", "Zaatakowałeś ale");
    define("YOU_ATTACKED_BUT", "Zostałeś zaatakowany ale");
    define("L_ATTACK", "zaatakował");
    define("L_BATTLE", "walka została nierozstrzygnięta");
    define("L_BATTLE2", "walka została nierozstrzygnięta z");
    define("L_ID", ", ID ");
    define("B_WIN", "zwycięża");
    define("AND_MAP", "oraz 1 kawałek mapy!");
    define("HE_GET", "otrzymuje");
    define("GOLD_COINS", "sztuk złota");
    define("YOU_ATTACK_AND", "Zaatakowałeś i");
    define("YOU_ATTACKED_AND", "Zostałeś zaatakowany i");
    define("L_PLAYER", "Gracz");
    define("BATTLE_WIN", "Walkę wygrał");
    define("YOU_DEFEAT", "pokonałeś");
    define("YOU_REWARD", "Zdobyłeś");
    define("R_AGI2", "zręczności");
    define("R_STR2", "siły");
    define("R_INT2", "inteligencji");
    define("R_WIS2", "woli");
    define("R_SPE2", "szybkości");
    define("R_CON2", "wytrzymałości");
}

if (isset ($_GET['action']) && $_GET['action'] == 'monster') 
{
    define("NO_ID", "Podaj numer");
    define("NO_HP", "Nie możesz walczyć ponieważ jesteś martwy!");
    define("NO_MONSTER", "Tu nie ma potwora.");
    define("NO_ENERGY2", "Nie masz wystarczająco dużo energii.");
    define("NO_CLASS3", "Nie możesz atakować potwora, dopóki nie wybierzesz profesji!");
    define("MONSTER_INFO", "Są tutaj potwory z którymi możesz walczyć. Ale uważaj... nie chcesz przecież atakować kogoś znacznie silniejszego od siebie, prawda?");
    define("M_LEVEL", "Poziom");
    define("M_NAME", "Nazwa");
    define("M_HEALTH", "Zdrowie");
    define("M_FAST", "Szybka");
    define("M_TURN", "Turowa");
    define("A_BATTLE", "Walka");
    define("OR_BACK2", "Lub możesz zawsze...");
    define("B_BACK2", "zawrócić");
    define("TOO_MUCH_MONSTERS", "Nie możesz walczyć z tak dużą ilością potworów!");
    if (isset($_GET['dalej']) || isset($_GET['next'])) 
    {
        define("A_BATTLE2", "Walcz");
        define("WITH_A", "jednocześnie z ");
        define("N_END", "(ami)");
    }
    if (isset($_GET['dalej'])) 
    {
        define("M_TIMES", "razy");
    }
}

if (!isset($_GET['action']) && !isset($_GET['battle']))
{
    define("BATTLE_INFO", "Idziesz sobie alejką pod górę i nagle widzisz tłumy magów, wojowników, a nawet obywateli podążających do pewnego budynku. Gdy zbliżyłeś się do niego, zauważyłeś, że jest on wykonany z mocnego kamienia w kolorze jasnoszarym, w niektórych miejscach pozłacanym. Na górze owego budynku znajdują się trzy wieże. Każda z nich jest pokryta innym materiałem: pierwsza brązem, druga srebrem, i w końcu trzecia złotem. Przy wejściu do budynku jest wyryty w ścianie napis Arena Walk. Wchodzisz do środka gdzie widzisz tłumy wojowników. Ściany są zrobione z twardego kamienia, w częściach pozłacanego. Ciekawy wbiegasz do największej z sal gdzie wszystko jest pokryte kamieniem i gdzie znajdują się drzwi. Podekscytowany chciałeś owe drzwi otworzyć, ale w tym momencie złapał Cię za dłoń silny wojownik i powiedział: <i>Witaj, czego tutaj szukasz?</i> Ty odpowiadasz:");
    define("A_SHOW_ALIVE", "Chcę walczyć z osobami na tym samym poziomie, co ja...");
    define("A_SHOW_LEVEL", "Pokaż mi listę osób na danych poziomach.");
    define("A_SHOW_MONSTER", "Chcę trenować z potworami.");
}
?>
