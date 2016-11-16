# PaperCut Web Parser
Simple PHP program for parsing logs of PaperCut Print Logger (http://www.papercut.com/products/free-software/print-logger/) and adding these into database (MySQL/MariaDB) and building reports.

# Insruction
1. Install PaperCut Print Logger on your Windows server with Print Server role, share directory with logs of this tool.
Instructions about installation Print Server role on Windows Server you can find in the internet.
Information about installation PaperCut Print Logger you can find http://www.papercut.com.
2. Prepare LAMP (Linux, Apache, MySQL/MariaDB, PHP) server. See documentation for your Linux distributive about installation Apache, PHP and MySQL/MariaDB.
3. Mount shared folder with daily logs of PaperCut Print Logger on your Windows Server (see step 1) on your LAMP server (step 2) with read only permissions.
4. Create MySQL/MariaDB database and user. Run next commands for create MySQL database, user and grant permissions.
CREATE USER 'printloguser'@'localhost' IDENTIFIED BY 'passwordforprintloguser';
CREATE DATABASE printlog;
GRANT ALL ON printlog.* TO 'printloguser'@'localhost';
5. Create configuration file (config.php) for in directory with other files of this program
5.1. Add into this file next lines
<?php
$DBHOST = "localhost"; //
$DBUSER = "printloguser";
$DBPASS = "passwordforprintloguser";
$DBNAME = "printlog";
$SEARCHDIR = "/path/to/folder/with/log-files/see/step_3_for_details/";
?>
5.2 Or open file installer.php in your browser, fill the fields on form and press button submit.
6. For import already exist PaperCut log files into the database, run file parser.php with parametr import-all, ie /same/folder/parser.php import-all
7. Add record into your cron, for example 
10 2 * * * /same/folder/parser.php
8. Open file index.php in your browser for viewing reports.