<?php
/**
 *   File functions:
 *   Polish language for bugtrack
 *
 *   @name                 : bugtrack.php                            
 *   @copyright            : (C) 2004-2005 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 0.8 beta
 *   @since                : 07.03.2005
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
// 

define("NO_PERM", "Nie masz prawa przebywać tutaj");
define("BUG_INFO", "Oto lista błędów zgłoszonych przez PHP");
define("A_DELETE", "Usuń");
define("B_ID", "ID");
define("B_TYPE", "Typ");
define("B_FILE", "Plik");
define("B_REF", "Odwołujący");
define("B_LINE", "Linia");
define("B_INFO", "Informacja");
define("B_AMOUNT", "Ilość");

if (isset($_GET['action']) && $_GET['action'] == 'delete') 
{
    define("DELETED", "Błędy usunięte! <a href=\"bugtrack.php\">Wróć</a>");
}
?>
