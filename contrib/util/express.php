<?php
/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  bradymiller <bradymiller>
 * @author  bradymiller <bradymiller@users.sourceforge.net>
 * @author  bradymiller <bradymiller@users.sourceforge.net>
 * @author  Robert Down <robertdown@live.com>
 * @author  cornfeed <jdough823@gmail.com>
 * @copyright Copyright (c) 2010 bradymiller <bradymiller>
 * @copyright Copyright (c) 2011 bradymiller <bradymiller@users.sourceforge.net>
 * @copyright Copyright (c) 2011 bradymiller <bradymiller@users.sourceforge.net>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2011 cornfeed <jdough823@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
<html>
<head>
<title>
</title>
</head>
<body>
<?php

// This exit is to avoid malicious use of this script.
//  (before running this script, comment out the exit command)
//  (when you are done running the script, remember to uncomment the exit command)
exit;

if (!$_POST['submit']) {
    ?>
<form method=post>
<p>
This script will take the name that you give and create an OpenEMR database with this as the database name, username, password, group name.  It will also rename the directory this OpenEMR installation is under to the new name.  THIS ONLY WORKS WITH XAMPP AND HAS VERY LIMITED TESTING.
</p>
<p>
Make sure that you close Text Editors, Windows Explorer, etc... as these things may stop directory rename that occurs in this script.
</p>
<p>
Enter the name you wish to use for this OpenEMR installation.
</p>
<input type=text name=newname>
<input type=submit name=submit value=submit>
</form>
    <?php
    exit(0);
}

if ($_POST['submit']) {
    $newname = $_POST['newname'];
    //handle the database stuff
    $dumpfile = "sql/database.sql";
    $icd9 = "sql/icd9.sql";
    $conffile = "library/sqlconf.php";
    $server = "localhost";
    $port = "3306";
    $dbname = $newname;
    $root = "root";
    $rootpass = "";
    $login = $newname;
    $pass = $newname;
    $loginhost = "localhost";

    //setup of database

    echo "Connecting to MySQL Server...\n";
    flush();
    if ($server == "localhost") {
        $dbh = mysql_connect("$server", "$root", "$rootpass");
    } else {
        $dbh = mysql_connect("$server:$port", "$root", "$rootpass");
    }

    if ($dbh == false) {
        echo "ERROR.  Check your login credentials.\n";
        echo "<p>".mysql_error()." (#".mysql_errno().")\n";
        exit;
    } else {
        echo "OK.<br>\n";
    }

    echo "Creating database...\n";
    flush();
    if (mysql_query("create database $dbname", $dbh) == false) {
        echo "ERROR.  Check your login credentials.\n";
        echo "<p>".mysql_error()." (#".mysql_errno().")\n";
        exit;
    } else {
        echo "OK.<br>\n";
    }

    echo "Creating user with permissions for database...\n";
    flush();
    if (mysql_query("GRANT ALL PRIVILEGES ON $dbname.* TO '$login'@'$loginhost' IDENTIFIED BY '$pass'", $dbh) == false) {
        echo "ERROR when granting privileges to the specified user.\n";
        echo "<p>".mysql_error()." (#".mysql_errno().")\n";
        echo "ERROR.\n";
        exit;
    } else {
        echo "OK.<br>\n";
    }

    echo "Reconnecting as new user...\n";
    mysql_close($dbh);
} else {
    echo "Connecting to MySQL Server...\n";
}

if ($server == "localhost") {
    $dbh = mysql_connect("$server", "$login", "$pass");
} else {
    $dbh = mysql_connect("$server:$port", "$login", "$pass");
}

if ($dbh == false) {
    echo "ERROR.  Check your login credentials.\n";
    echo "<p>".mysql_error()." (#".mysql_errno().")\n";
    exit;
} else {
    echo "OK.<br>\n";
}

echo "Opening database...";
flush();
if (mysql_select_db("$dbname", $dbh) == false) {
    echo "ERROR.  Check your login credentials.\n";
    echo "<p>".mysql_error()." (#".mysql_errno().")\n";
    exit;
} else {
    echo "OK.<br>\n";
}

    flush();
if ($upgrade != 1) {
    echo "Creating initial tables...\n";
    mysql_query("USE $dbname", $dbh);
    flush();
    $fd = fopen($dumpfile, 'r');
    if ($fd == false) {
        echo "ERROR.  Could not open dumpfile '$dumpfile'.\n";
        flush();
        exit;
    }

    $query = "";
    $line = "";
    while (!feof($fd)) {
        $line = fgets($fd, 1024);
        $line = rtrim($line);
        if (substr($line, 0, 2) == "--") { // Kill comments
            continue;
        }

        if (substr($line, 0, 1) == "#") { // Kill comments
            continue;
        }

        if ($line == "") {
            continue;
        }

        $query = $query.$line;      // Check for full query
        $chr = substr($query, strlen($query)-1, 1);
        if ($chr == ";") { // valid query, execute
            $query = rtrim($query, ";");
            mysql_query("$query", $dbh);
            $query = "";
        }
    }

    echo "OK<br>\n";
    fclose($fd);
    flush();
    echo "Adding Initial User...\n";
    flush();
    $iuser = "admin";
    $iuname = "admin";
    $igroup = $newname;
    //echo "INSERT INTO `groups` VALUES (1,'$igroup','$iuser')<br>\n";
    if (mysql_query("INSERT INTO `groups` (id, name, user) VALUES (1,'$igroup','$iuser')") == false) {
        echo "ERROR.  Could not run queries.\n";
        echo "<p>".mysql_error()." (#".mysql_errno().")\n";
        flush();
        exit;
    }

    //// ViCareplus : As per NIST standard, SHA1 hash/digest of 'pass' is used
    if (mysql_query("INSERT INTO users (id, username, password, authorized, lname,fname) VALUES (1,'$iuser','9d4e1e23bd5b727046a9e3b4b7db57bd8d6ee684',1,'$iuname','')") == false) {
        echo "ERROR.  Could not run queries.\n";
        echo "<p>".mysql_error()." (#".mysql_errno().")\n";
        flush();
        exit;
    }

    echo "OK<br>\n";
    flush();

    //Now write sqlconf file
    echo "Writing SQL Configuration to disk...\n";
    @touch($conffile); // php bug
    $fd = @fopen($conffile, 'w');
    if ($fd == false) {
        echo "ERROR.  Could not open config file '$conffile' for writing.\n";
        flush();
        exit;
    }

    $string = "<?php\n\n//  OpenEMR\n//  MySQL Config\n//  Referenced from sql.inc\n\n";

    $it_died = 0;   //fmg: variable keeps running track of any errors

    fwrite($fd, $string) or $it_died++;
    fwrite($fd, "\$host\t= '$host';\n") or $it_died++;
    fwrite($fd, "\$port\t= '$port';\n") or $it_died++;
    fwrite($fd, "\$login\t= '$login';\n") or $it_died++;
    fwrite($fd, "\$pass\t= '$pass';\n") or $it_died++;
    fwrite($fd, "\$dbase\t= '$dbname';\n") or $it_died++;


    $string = '\n\n' .
        '$sqlconf = array();\n' .
        '$sqlconf["host"]= $host;\n' .
        '$sqlconf["port"] = $port;\n' .
        '$sqlconf["login"] = $login;\n' .
        '$sqlconf["pass"] = $pass;\n' .
        '$sqlconf["dbase"] = $dbase;\n' .
        '//////////////////////////\n' .
        '//////////////////////////\n' .
        '//////////////////////////\n' .
        '//////DO NOT TOUCH THIS///\n' .
        '$config = 1; /////////////\n' .
        '//////////////////////////\n' .
        '//////////////////////////\n' .
        '//////////////////////////\n' .
        '?>';
    ?>
    <?php // done just for coloring

    fwrite($fd, $string) or $it_died++;

    //it's rather irresponsible to not report errors when writing this file.
    if ($it_died != 0) {
        echo "ERROR. Couldn't write $it_died lines to config file '$conffile'.\n";
        flush();
        exit;
    }

    fclose($fd);

    //Now, use new name and fix globals.php and rename directory!!!
    $d = getcwd();
    $dn = dirname($d);
    $contents = file_get_contents($d.'/interface/globals.php');
    $contents = preg_replace(
        '/\$webserver_root\s+=\s+[\"\'].*?[\"\'];/',
        "\$webserver_root = '".$dn."/".$newname."';",
        $contents
    );
    $contents = preg_replace(
        '/\$web_root\s+=\s+[\"\'].*?[\"\'];/',
        "\$web_root = '/".$newname."';",
        $contents
    );
    file_put_contents($d.'/interface/globals.php', $contents);
    if (rename($d, $dn.'/'.$newname)) {
        echo "<br/><a href='http://localhost/".$newname."'>click here</a>";
    }
}
?>
</body>
</html>
