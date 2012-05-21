<?php
/**
 *   File functions:
 *   Install or update game
 *
 *   @name                 : install.php                            
 *   @copyright            : (C) 2004,2005,2006,2007,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 21.05.2012
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

/**
* Function read .sql file and convert on queries to database (based on phpMyAdmin)
*/
function splitschema(&$ret, $sql)
{
    $sql          = rtrim($sql, "\n\r");
    $sql_len      = strlen($sql);
    $char         = '';
    $string_start = '';
    $in_string    = FALSE;
    $nothing      = TRUE;
    $time0        = time();

    for ($i = 0; $i < $sql_len; ++$i) 
    {
        $char = $sql[$i];
        if ($in_string) 
        {
            for (;;) 
            {
                $i = strpos($sql, $string_start, $i);
                if (!$i) 
                {
                    $ret[] = $sql;
                    return TRUE;
                }
                    elseif ($string_start == '`' || $sql[$i-1] != '\\') 
                {
                    $string_start = '';
                    $in_string = FALSE;
                    break;
                }
                    else 
                {
                    $j = 2;
                    $escaped_backslash = FALSE;
                    while ($i-$j > 0 && $sql[$i-$j] == '\\') 
                    {
                        $escaped_backslash = !$escaped_backslash;
                        $j++;
                    }
                    if ($escaped_backslash) 
                    {
                        $string_start  = '';
                        $in_string     = FALSE;
                        break;
                    }
                        else 
                    {
                        $i++;
                    }
                }
            }
        } 
            elseif (($char == '-' && $sql_len > $i + 2 && $sql[$i + 1] == '-' && $sql[$i + 2] <= ' ') || $char == '#' || ($char == '/' && $sql_len > $i + 1 && $sql[$i + 1] == '*')) 
        {
            $i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
            if ($i === FALSE) 
            {
                break;
            }
            if ($char == '/') 
            {
                $i++;
            }
        }
            elseif ($char == ';') 
        {
            $ret[]      = array('query' => substr($sql, 0, $i), 'empty' => $nothing);
            $nothing    = TRUE;
            $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
            $sql_len    = strlen($sql);
            if ($sql_len) 
            {
                $i      = -1;
            } 
                else 
            {
                return TRUE;
            }
        } 
            elseif (($char == '"') || ($char == '\'') || ($char == '`')) 
        {
            $in_string    = TRUE;
            $nothing      = FALSE;
            $string_start = $char;
        } 
            elseif ($nothing) 
        {
            $nothing = FALSE;
        }
        $time1     = time();
        if ($time1 >= $time0 + 30) 
        {
            $time0 = $time1;
            header('X-pmaPing: Pong');
        } 
    } 
    if (!empty($sql) && preg_match('@[^[:space:]]+@', $sql)) 
    {
        $ret[] = array('query' => $sql, 'empty' => $nothing);
    }
    return TRUE;
}
?>
<html>
<head>
<title>Vallheru - instalacja</title>
<link rel="Stylesheet" href="../css/light.css">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
<META HTTP-EQUIV="Content-Language" CONTENT="pl">
</head>
<body>
<center>
<?php
if (!isset($_GET['step'])) 
{
?>
Wybierz opcję:<br />
<ul>
    <li><a href="install.php?step=install1">Instalacja gry</a></li>
    <li><a href="install.php?step=update1">Uaktualnienie gry</a></li>
</ul>
<?php
}
if (isset($_GET['step']) && $_GET['step'] == 'install1') 
{
?>
Test uprawnień plików oraz katalogów<br />
Zanim zaczniesz instalację upewnij się, że skrypt posiada uprawnienia do modyfikacji odpowiednich plików<br />
<?php
    $result = array();
    @$configfile = fopen('../includes/config.php', 'w');
    if (!$configfile) 
    {
        $result[0] = "Brak uprawnień!<br />";
    } 
        else 
    {
        $result[0] = "W porządku<br />";
    }
    print "Plik includes/config.php...".$result[0];
    @$templatescat = fopen('../templates_c/test.php', 'w');
    if (!$templatescat) 
    {
        $result[1] = "Brak uprawnień!<br />";
    } 
        else 
    {
        $result[1] = "W porządku<br />";
        unlink('../templates_c/test.php');
    }
    print "Katalog templates_c...".$result[1];
    @$avatarscat = fopen('../avatars/test.php', 'w');
    if (!$avatarscat) 
    {
        $result[2] = "Brak uprawnień!<br />";
    } 
        else 
    {
        $result[2] = "W porządku<br />";
        unlink('../avatars/test.php');
    }
    print "Katalog avatars...".$result[2];
    @$tribelogoscat = fopen('../images/tribes/test.php', 'w');
    if (!$tribelogoscat) 
    {
        $result[3] = "Brak uprawnień!<br />";
    } 
        else 
    {
        $result[3] = "W porządku<br />";
        unlink('../images/tribes/test.php');
    }
    print "Katalog images/tribes...".$result[3];
    @$layout1cat = fopen('../templates_c/layout1/test.php', 'w');
    if (!$layout1cat) 
    {
        $result[4] = "Brak uprawnień!<br />";
    } 
        else 
    {
        $result[4] = "W porządku<br />";
        unlink('../templates_c/layout1/test.php');
    }
    print "Katalog templates_c/layout1...".$result[4];
    @$cachecat = fopen('../cache/test.php', 'w');
    if (!$cachecat)
    {
        $result[5] = "Brak uprawnień!<br />";
    }
        else
    {
        $result[5] = "W porządku<br />";
        unlink('../cache/test.php');
    }
    print "Katalog cache...".$result[5];
    @$mailerconf = fopen('../mailer/mailerconfig.php', 'w');
    if (!$mailerconf)
    {
        $result[6] = "Brak uprawnień!<br />";
    }
        else
    {
        $result[6] = "W porządku<br />";
    }
    print "Plik mailer/mailerconfig.php...".$result[6];
    $test = 0;
    for ($i = 0; $i < 7; $i ++) 
    {
        if ($result[$i] == "Brak uprawnień!<br />") 
        {
            print "Zanim rozpoczniesz instalację, nadaj odpowiednie uprawnienia a następnie ponownie odśwież stronę<br />
            <form method=\"post\" action=\"install.php?step=install1\">
            <input type=\"submit\" value=\"Odśwież\"></form>";
            $test = 1;
            exit;
        }
    }
    if (!$test) 
    {
        print "<form method=\"post\" action=\"install.php?step=install2\">
            <input type=\"submit\" value=\"Dalej >>\"></form>";
    }
}
if (isset($_GET['step']) && ($_GET['step'] == 'install2' || $_GET['step'] == 'update1')) 
{
    if ($_GET['step'] == 'install2') 
    {
?>
Instalacja Vallheru<br />
Skrypt ten pomoże zainstalować ci grę Vallheru na twoim serwerze. Przed przystąpieniem do instalacji powinieneś przeczytać plik install.txt <br />
<form method="post" action="install.php?step=install3">
<?php
    } 
        else 
    {
?>
Aktualizjacja Vallheru<br />
<form method="post" action="install.php?step=update2">
<?php
    }
?>
Wypełnij wszystkie pola!<br />
<table width="75%">
    <tr>
        <td>Rodzaj bazy danych:</td>
    <td>
        MySQL
    </td>
    </tr>
    <tr>
        <td>Host bazy danych:</td>
    <td><input type="text" name="dbhost" value="localhost"></td>
    </tr>
    <tr>
        <td>Użytkownik bazy danych:</td>
    <td><input type="text" name="dbuser" value="dbuser"><td>
    </tr>
    <tr>
        <td>Hasło do bazy danych:</td>
    <td><input type="password" name="dbpass"></td>
    </tr>
    <tr>
        <td>Nazwa bazy danych:</td>
    <td><input type="text" name="dbname" value="dbname"></td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td>Nazwa gry:</td>
    <td><input type="text" name="gamename" value="Gra"><td>
    </tr>
    <tr>
        <td>Email gry:</td>
    <td><input type="text" name="gamemail" value="noreply@vallheru.net"></td>
    </tr>
    <tr>
        <td>Adres gry (bez http://):</td>
    <td><input type="text" name="gameadress" value="www.vallheru.net"></td>
    </tr>
    <tr>
        <td>Nazwa pierwszego miasta (mianownik - kto? co?:</td>
        <td><input type="text" name="city1" value="Miasto"></td>
    </tr>
    <tr>
        <td>Nazwa pierwszego miasta (miejscownik - o kim? o czym?):</td>
        <td><input type="text" name="city1a" value="Mieście"></td>
    </tr>
    <tr>
        <td>Nazwa pierwszego miasta (dopełniacz - kogo? czego?):</td>
        <td><input type="text" name="city1b" value="Miasta"></td>
    </tr>
    <tr>
        <td>Nazwa drugiego miasta (nieodmienna):</td>
        <td><input type="text" name="city2" value="Wioska"></td>
    </tr>
    <tr>
        <td>Limit graczy w grze:</td>
        <td><input type="text" name="pllimit" value="50"></td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td>Nick Admina:</td>
    <td><input type="text" name="adminnick"></td>
    </tr>
    <tr>
        <td>Email Admina:</td>
    <td><input type="text" name="adminmail"></td>
    </tr>
    <tr>
        <td>Hasło admina:</td>
    <td><input type="password" name="adminpass"></td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
<?php
    if ($_GET['step'] == 'install2') 
    {
?>
        <td colspan="2" align="center"><input type="submit" value="Instaluj"></td>
<?php
    } 
        else 
    {
?>
    <td colspan="2" align="center"><input type="submit" value="Aktualizuj"></td>
<?php
    }
?>
    </tr>
</table>
<?php
}
if (isset($_GET['step']) && $_GET['step'] == 'install3') 
{
/**
* Check for empty fields in form
*/
    $arrOptions = array($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname'], $_POST['gamename'], $_POST['gamemail'], $_POST['gameadress'], $_POST['adminnick'], $_POST['adminmail'], $_POST['adminpass'], $_POST['city1'], $_POST['city1a'], $_POST['city1b'], $_POST['city2'], $_POST['pllimit']);
    foreach ($arrOptions as $strOption)
    {
        if (empty($strOption))
        {
            die("Wypełnij wszystkie pola! <a href=\"install.php\">Wróć</a>");
        }
    }
/**
* Create file config.php
*/
    print "Tworzenie pliku config.php...";
    $configtext = "<?php
require_once('adodb/adodb.inc.php');
\$ADODB_CACHE_DIR = './cache/';
\$db = NewADOConnection('mysqli');
\$db -> Connect(\"".$_POST['dbhost']."\", \"".$_POST['dbuser']."\", \"".$_POST['dbpass']."\", \"".$_POST['dbname']."\");
\$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
\$gamename= \"".$_POST['gamename']."\";
\$gamemail = \"".$_POST['gamemail']."\";
\$gameadress = \"http://".$_POST['gameadress']."\";
\$adminname = \"".$_POST['adminnick']."\";
\$adminmail = \"".$_POST['adminmail']."\";
\$city1 = \"".$_POST['city1']."\";
\$city1a = \"".$_POST['city1a']."\";
\$city1b = \"".$_POST['city1b']."\";
\$city2 = \"".$_POST['city2']."\";
\$pllimit = ".$_POST['pllimit'].";
\$lang = \"pl\";
?>";
    $configfile = fopen("../includes/config.php", "w");
    fwrite($configfile, $configtext);
    fclose($configfile);
    print "Zakończone<br />";
/**
* Create database
*/
    print "Tworzenie bazy danych...";
    $strFile = 'db/mysql.sql';
    $file = fopen($strFile, "rb");
    $sql_query = fread($file, filesize($strFile));
    fclose($file);
    $pieces = array();
    splitschema($pieces, $sql_query);
    $amount = count($pieces);
    require_once('../adodb/adodb.inc.php');
    $db = NewADOConnection('mysqli');
    $db -> Connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname']) or die("Błąd przy połączeniu z bazą danych!");
    for ($i = 0; $i < $amount; $i ++) 
    {
        $db -> Execute($pieces[$i]['query']) or die("Błąd przy instalacji bazy danych! ".$db -> ErrorMsg());
    }
    print "Zakończone<br />";
/**
* Create admin account
*/
    print "Tworzenie konta admina...";
    $strPass = MD5($_POST['adminpass']);
    $db -> Execute("INSERT INTO players (`user`, `email`, `pass`, `rank`) VALUES('".$_POST['adminnick']."', '".$_POST['adminmail']."', '".$strPass."', 'Admin')") or die("Nie mogę utworzyć konta administratora!");
    $db -> Close();
    print "Zakończone<br />";
    print "<form method=\"post\" action=\"install.php?step=install4\">
            <input type=\"submit\" value=\"Dalej >>\"></form>";
}

if (isset($_GET['step']) && $_GET['step'] == 'install4') 
{
?>
Wybór systemu wysyłania maili.<br />
Poniżej możesz wybrać sposób w jaki będą wysyłane maile:<br />
1. Poprzez funkcję mail(); w PHP<br />
Opcja ta wymaga aby dana funkcja była włączona na serwerze. Jeżeli jesteś pewien że działa, wybierz tę opcję. Jeżeli wybierzesz tę opcję nie musisz wypełniać poniższego formularza<br />
2. Poprzez zewnętrzny serwer SMTP<br />
Wybierz tę opcję w przypadku kiedy opcja nr 1 nie działa. Jednak dodatkowo będziesz musiał podać dane do istniejącego konta pocztowego<br />
<form method="post" action="install.php?step=install5">
<input type="radio" name="mailer" value="sendmail" />Wysyłanie poczty przez mail();<br />
<input type="radio" name="mailer" value="smtp" />Wysyłanie poczty poprzez zewnętrzny serwer SMTP<br />
Nazwa serwera: <input type="text" name="mailserv" /><br />
Nazwa użytkownika: <input type="text" name="user" /><br />
Hasło do konta: <input type="text" name="pass" /><br />
<input type="submit" value="Dalej >>">
</form>
<?php
}

if (isset($_GET['step']) && $_GET['step'] == 'install5') 
{
    /**
     * Set mailer type
     */
    print "Tworzenie pliku mailerconfig.php...";
    if (isset($_POST['mailer']) && $_POST['mailer'] == 'sendmail')
    {
        $strConfigtext = "<?php
require_once('class.phpmailer.php');
\$mail = new PHPMailer();
\$mail -> PluginDir = \"./mailer/\";
\$mail -> SetLanguage(\"pl\", \"./mailer/language/\");
\$mail -> CharSet = \"utf-8\";
\$mail -> Sender = \$gamemail;
\$mail -> IsMail();
\$mail -> From = \$gamemail;
\$mail -> FromName = \$gamename;
\$mail -> AddAddress(\$adress);
\$mail -> WordWrap = 50;
\$mail -> Subject = \$subject;
\$mail -> Body = \$message;

?>";
    }
    else
      {
	if ($_POST['mailserv'] == 'smtp.gmail.com')
	  {
	    $strGmail = "\$mail -> SMTPSecure = true;
\$mail -> Port = 465;";
	  }
	else
	  {
	    $strGmail = '';
	  }
        $strConfigtext = "<?php
require_once('class.phpmailer.php');
\$mail = new PHPMailer();
\$mail -> PluginDir = \"./mailer/\";
\$mail -> SetLanguage(\"pl\", \"./mailer/language/\");
\$mail -> CharSet = \"utf-8\";
\$mail -> Sender = \$gamemail;
\$mail -> IsSMTP();
".$strGmail."
\$mail -> Host = \"".$_POST['mailserv']."\"; 
\$mail -> SMTPAuth = true; 
\$mail -> Username = \"".$_POST['user']."\";
\$mail -> Password = \"".$_POST['pass']."\";
\$mail -> From = \$gamemail;
\$mail -> FromName = \$gamename;
\$mail -> AddAddress(\$adress);
\$mail -> WordWrap = 50;
\$mail -> Subject = \$subject;
\$mail -> Body = \$message;

?>
";
    }
    $configfile = fopen("../mailer/mailerconfig.php", "w");
    fwrite($configfile, $strConfigtext);
    fclose($configfile);
    print "Zakończone<br />";
    print "Instalacja gry została zakończona. Wykasuj plik install.php a najlepiej cały katalog install.<br />Dziękujemy za korzystanie z Vallheru Engine <br />&copy; 2004,2005,2006,2007,2011 Vallheru Team";
}
if (isset($_GET['step']) && $_GET['step'] == 'update2') 
{
    /**
     * Modify file config.php
     */
    print "Modyfikacja pliku config.php...";
    $configtext = "<?php
require_once('adodb/adodb.inc.php');
\$ADODB_CACHE_DIR = './cache/';
\$db = NewADOConnection('mysqli');
\$db -> Connect(\"".$_POST['dbhost']."\", \"".$_POST['dbuser']."\", \"".$_POST['dbpass']."\", \"".$_POST['dbname']."\");
\$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
\$gamename= \"".$_POST['gamename']."\";
\$gamemail = \"".$_POST['gamemail']."\";
\$gameadress = \"http://".$_POST['gameadress']."\";
\$adminname = \"".$_POST['adminnick']."\";
\$adminmail = \"".$_POST['adminmail']."\";
\$city1 = \"".$_POST['city1']."\";
\$city1a = \"".$_POST['city1a']."\";
\$city1b = \"".$_POST['city1b']."\";
\$city2 = \"".$_POST['city2']."\";
\$pllimit = ".$_POST['pllimit'].";
\$lang = \"pl\";
?>";
    $configfile = fopen("../includes/config.php", "w");
    fwrite($configfile, $configtext);
    fclose($configfile);
    print "Zakończone<br />";
    
    /**
    * Modify database
    */
    print "Modyfikacja bazy danych...";
    $file = fopen("db/update.sql", "rb");
    $sql_query = fread($file, filesize('db/update.sql'));
    fclose($file);
    $pieces = array();
    splitschema($pieces, $sql_query);
    require_once('../adodb/adodb.inc.php');
    $db = NewADOConnection('mysqli');
    $db -> Connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname']) or die("Nie mogę połączyć się z bazą danych!");
    $amount = count($pieces);
    for ($i = 0; $i < $amount; $i ++) 
    {
        $db -> Execute($pieces[$i]['query']) or die("Błąd przy uaktualnianiu bazy danych! ".$db -> ErrorMsg());
    }
    print "Zakończone<br />Aktualizacja przebiegła pomyślnie. Wykasuj plik install.php a następnie przegraj nowe pliki do obecnej wersji (jeżeli do tej pory jeszcze tego nie zrobiłeś)<br />Dziękujemy za korzystanie z Vallheru Engine<br />&copy; 2004,2005,2006,2007,2011 Vallheru Team";
}
?> 
</body>
</html>
