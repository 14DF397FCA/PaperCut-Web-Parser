<html>
    <head><title>PaperCut Web Parser installer</title></head>
    <body>
        <h1><div align="center">Enter credentials for access to database</div></h1>
        <form action="installer.php" method="POST" name="DBAccess">
            <table WIDTH="30%" align="center">
                <tr><td align="right">Hostname:</td><td><input type="text" name="hostname" value="localhost"></td></tr>
                <tr><td align="right">User</td><td><input type="text" name="user"></td></tr>
                <tr><td align="right">Password</td><td><input type="text" name="password"></td></tr>
                <tr><td align="right">Database name</td><td><input type="text" name="dbname"></td></tr>
		<tr><td align="right">Search directory</td><td><input type="text" name="searchdir"></td></tr>
                <tr><td></td><td><button type="Clear" value="Clear">Clear</button><input type="submit" value="Submit"></td></tr>
            </table>
        </form>
        <?php
        $Query = "CREATE TABLE data ("
                            . "ID int NOT NULL AUTO_INCREMENT, "
                            . "DateTime DATETIME, "
                            . "User varchar(32), "
                            . "Pages int, "
                            . "Copies int, "
                            . "Printer varchar(32), "
                            . "DocumentName varchar(255), "
                            . "Client varchar(128), "
                            . "PaperSize varchar(32), "
                            . "Language varchar(16), "
                            . "Height int, "
                            . "Width int, "
                            . "Duplex varchar(16), "
                            . "Grayscale varchar(16), "
                            . "Size varchar(16), "
                            . "PRIMARY KEY (ID));";
        
        $DBHOST = htmlspecialchars($_POST['hostname']);
        $DBUSER = htmlspecialchars($_POST['user']);
        $DBPASS = htmlspecialchars($_POST['password']);
        $DBNAME = htmlspecialchars($_POST['dbname']);
	$SEARCHDIR = htmlspecialchars($_POST['searchdir']);
        if ( (empty($DBHOST) == false) && (empty($DBUSER) == FALSE) && (empty($DBPASS) == FALSE) && (empty($DBNAME) == FALSE) )
            {
            $fp = fopen("config.php", "w");
            fwrite($fp, "<?php\n");
            fwrite($fp, "\$DBHOST = \"" . $DBHOST . "\";\n");
            fwrite($fp, "\$DBUSER = \"" . $DBUSER . "\";\n");
            fwrite($fp, "\$DBPASS = \"" . $DBPASS . "\";\n");
            fwrite($fp, "\$DBNAME = \"" . $DBNAME . "\";\n");
            fwrite($ft, "\$SEARCHDIR = \"" . $SEARCHDIR . "\";\n");
            fwrite($fp, "?>\n");
            fclose($fp);
            $dbconnect = mysql_connect($DBHOST, $DBUSER, $DBPASS);
            $err = mysql_errno();
            echo $err;
            if ($dbconnect)
                {
                if (mysql_select_db($DBNAME))
                    {
                    $res = mysql_query($Query);                    
                    mysql_close();
                    }
                else
                    {
                    echo "<div align=\"center\"><h2>Cannot select mysql db! Please create database, and try again</h2></div>";
                    }
                }
             else
                {
                echo "<div align=\"center\"><h2>Cannot connect with mysql!</h2></div>";
                }
            }
        else
        {
            echo "<h3><div align=\"center\">Some field is empty!</div></h3>";
        }
        ?>
    </body>
</html>