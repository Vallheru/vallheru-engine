<?php
/**
 *   File functions:
 *   Page source
 *
 *   @name                 : source.php                            
 *   @copyright            : (C) 2004-2005 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.0 rc1
 *   @since                : 27.11.2005
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

/**
* Some security tests
*/
if ($_GET['file'] == 'includes/config.php' || $_GET['file'] == 'includes/sessions.php')
{
    exit;
}

$arrTest = explode('/', $_GET['file']);
$intAmount = count($arrTest);
if ($intAmount > 2)
{
    exit;
}
    elseif ($intAmount == 2)
{
    if ($arrTest[0] != 'includes' && $arrTest[0] != 'class')
    {
        exit;
    }
}
$arrTest2 = explode('.', $_GET['file']);
$intAmount = (count($arrTest2) - 1);
if ($arrTest2[$intAmount] != 'php')
{
    exit;
}

$do_gzip_compress = FALSE;
$compress = FALSE;
$phpver = phpversion();
$useragent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;
if ( $phpver >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) ) 
{
    if ( extension_loaded('zlib') ) 
    {
        $compress = TRUE;
        ob_start('ob_gzhandler');
    }
} 
    elseif ( $phpver > '4.0' ) 
{
    if ( strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip') ) 
    {
        if ( extension_loaded('zlib') ) 
        {
            $do_gzip_compress = TRUE;
            $compress = TRUE;
            ob_start();
            ob_implicit_flush(0);
            header('Content-Encoding: gzip');
        }
    }
}

/**
* Display source
*/

require_once 'libs/Smarty.class.php';
$smarty = new Smarty;

$smarty -> compile_check = false;
$smarty -> caching = true;


if ($_GET['file'] == 'includes/head.php')
{
    $strSource = show_source('includes/head.php', true);
}
    elseif ($_GET['file'] == 'includes/foot.php')
{
    $strSource = show_source('includes/foot.php', true);
}
    else
{
    $strSource2 = "<a href=\"source.php?file=includes/head.php\" target=\"_blank\">head.php</a><br /><br />";
    $strSource = show_source($_GET['file'], true);
    $strSource3 = "<br /><a href=\"source.php?file=includes/foot.php\" target=\"_blank\">foot.php</a><br />";
    $strSource = $strSource2.$strSource.$strSource3;
}

$strCacheid = $_GET['file'];

$smarty -> assign(array("Source" => $strSource,
	"File" => $_GET['file']));

$smarty -> display('source.tpl', $strCacheid);
exit;

if (!isset($do_gzip_compress)) 
{
    $do_gzip_compress = $compress;
}

if ( $do_gzip_compress )
{
    //
    // Borrowed from php.net!
    //
    $gzip_contents = ob_get_contents();
    ob_end_clean();

    $gzip_size = strlen($gzip_contents);
    $gzip_crc = crc32($gzip_contents);

    $gzip_contents = gzcompress($gzip_contents, 9);
    $gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

    echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
    echo $gzip_contents;
    echo pack('V', $gzip_crc);
    echo pack('V', $gzip_size);
}
?>
