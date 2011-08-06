<?php
/**
 *   File functions:
 *   Polish language for labyrynt
 *
 *   @name                 : grid.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 27.07.2006
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
define("R_AGI", "zręczności");
define("R_STR", "siły");
define("R_INT", "inteligencji");
define("R_WIS", "woli");
define("R_SPE", "szybkości");
define("R_CON", "wytrzymałości");

if (isset ($_GET['action']) && $_GET['action'] == 'explore') 
{
    define("NO_ENERGY", "Nie masz wystarczająco energii aby zwiedzać labirynt.");
    define("YOU_DEAD", "Nie możesz zwiedzać labiryntu ponieważ jesteś martwy!");
    define("CHEST", "Wędrując korytarzami w pewnym momencie w oddali dostrzegasz niewielką, okutą żelazem skrzynię. Podchodzisz do niej i");
    define("CHEST2", " rozwalasz ją przy pomocy własnej broni.");
    define("CHEST3", " rozwalasz ją przy użyciu zaklęcia.");
    define("CHEST4", " przyglądając się jej uważnie, dostrzegasz na boku niewielki przycisk. Kiedy go naciskasz, wieko skrzyni unosi się.");
    define("CHEST5", "Wewnątrz dostrzegasz zwój starego pergaminu. Ostrożnie wyciągasz go i uważnie przyglądasz się. To kawałek mapy skarbów. Delikatnie chowasz ją w plecaku.");
    define("ACTION1", "Idziesz przez dłuższy czas rozglądając się wkoło lecz nic tu nie ma.");
    define("ACTION2", "Nic nie znajdujesz.");
    define("ACTION3", "Znalazłeś mieszek! Znajduje się w nim");
    define("ACTION3_1", "sztuk złota.");
    define("ACTION4", "Idziesz i idziesz, lecz nic nie znajdujesz.");
    define("ACTION5", "Co to jest?");
    define("ACTION6", "Znalazłeś mithril! Zdobyłeś");
    define("ACTION6_1", "sztuk mithrilu");
    define("ACTION7_1", "Zobaczyłeś źródło! Podbiegasz i odzyskujesz <b>1</b> punkt energi!");
    define("ACTION8", "Nie ma tutaj nic wartościowego");
    define("ACTION9", "Wędrowałeś jakiś czas po korytarzach, ale niestety nie znalazłeś nic ciekawego.");
    define("ACTION11", "Idziesz po labiryncie i napotykasz na stary wytarty kawałek papieru. Okazuje się że jest to ");
    define("A_EXP", "Zwiedzaj");
    define("T_NEXT", "dalej. (zostało ci");
    define("EN_PTS", "punktów energii");
}

if (!isset($_GET['action']) && !isset($_GET['step']))
{
    define("LAB_INFO", "Idziesz sobie starą drogą gdzie po bokach stoją stare kamieniczki całe z kamienia już prawie zniszczonego. Nagle w dali zauważasz stary, potężny labirynt jest on wykonany z marmuru a jego mury liczą około pięć metrów wysokości. Wejście jest zablokowane kamieniami, chociaż nikt tam nie wchodził od setek lat! Nagle podchodzi do ciebie stary mędrzec i mówi <i>Patrzysz na stary labirynt młodzieńcze. Nikt tam nie wchodził od wielu lat. Wielu mężnych wojowników wchodziło tam zabić potwora. W końcu go zgładzili, lecz gdy chcieli wychodzić zostali zasypani głazami. Szczury na pewno poroznosiły ich skarby, będziesz mógł znaleźć tam interesujące rzeczy a teraz żegnaj</i>. Podchodzisz do wejścia i robisz sobie przejście. Pod gruzami leży wielu martwych rycerzy.<br /> Czy mimo tego chcesz tam wejść?");
}
?>
