<?php
/**
 *   File functions:
 *   Polish language for chat
 *
 *   @name                 : chat.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @author               : eyescream <tduda@users.sourceforge.net>
 *   @version              : 1.3
 *   @since                : 23.11.2006
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
// $Id: chat.php 840 2006-11-24 16:41:26Z thindil $

define("NO_PERM", "Nie możesz pisać wiadomości na czacie");
define("INNKEEPER", "Karczmarzu");
define("INNKEEPER2", "Karczmarz");
define("FOR_A", "dla");
define("BEER", "piwo");
define("HONEY", "miód");
define("WINE", "wino");
define("MUSTAK", "mustak");
define("JUICE", "soczek");
define("CUCUMBERS", "ogórki");
define("TEA", "herbatka");
define("MEAT", "szaszłyk");
define("MILK", "mleko");
define("FOOD", "pierogi");
define("MEAT2", "pieczeń");
define("EGGS", "jajecznica");
define("FOOD2", "racuchy");
define("ICE", "lody");
define("MEAT3", "mielonka");
define("MEAT4", "schabowy");
define("FOOD3", "pasztecik");
define("CHICKEN", "kurczak");
define("EGG", "jajko na twardo");
define("EGG2", "jajko na miękko");
define("FLAPJACK", "naleśniki");
define("COFFE", "kawa");
define("ALL_P", "wszystkich!");
define("WARN", "Uwaga!");
define("PLEASE", "Proszę");
define("HERE_IS", "oto");
define("FROM_A", "od");
define("A_REFRESH", "odśwież");
define("A_SEND", "Wyślij");
define("INN", "Karczma");
define("INN_IS", "Jest");
define("INN_TEXTS", "wypowiedzi");
define("A_BAN", "Zablokuj");
define("A_UNBAN", "Odblokuj");
define("CHAT_ID", "pisanie wiadomości w karczmie graczowi o ID");
define("A_GIVE", "Daj");
define("WITH_COMM", "z opcjonalnym komentarzem");
define("ERROR", "Zapomnij o tym!");
define("A_PRUNE", "Wyczyść karczmę");
define("FOR_ALL", " stawia wszystkim ");
define("FOR_ALL2", "Uwaga! Oto ");
define("FOR_ALL3", " dla wszystkich ");
define("INNKEEPER_GONE", "Karczmarza nie ma, teraz ");
define("RULES", " tu rządzi!");
define("ON_A", "na");
define("T_DAYS", " dni z przyczyny: ");
define("YOU_BLOCK2", "Masz zakaz wchodzenia do karczmy na ");
define("BLOCK_BY", ". Zablokował Cię ");

if (isset($_GET['step']) && $_GET['step'] == 'ban')
{
    define("YOU_BLOCK", "Zablokowałeś wysyłanie wiadomości na czacie przez gracza");
    define("YOU_UNBLOCK", "Odblokowałeś wysyłanie wiadomości na czacie przez gracza");
}

if (isset ($_GET['step']) && $_GET['step'] == 'clearc') 
{
    define("CHAT_PRUNE", "Wyczyściłeś karczmę.");
}
?>
