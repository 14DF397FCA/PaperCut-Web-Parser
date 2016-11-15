<?php

require_once 'config.php';

$MONTH = htmlspecialchars($_GET["SelectMonth"]);
$PRINTER = htmlspecialchars($_GET["SelectPrinter"]);
if ((!empty($MONTH)) && (!empty($PRINTER)))
	{
	$PrintQuery = "SELECT DateTime, User, Pages, Copies, DocumentName, Client FROM data "
			. "WHERE Printer=\"" . $PRINTER . "\" AND "
			. "DateTime BETWEEN \"" . $MONTH . "-01 00:00:00\" AND \"" . date("Y-m-t", strtotime($MONTH)) . " 23:59:59\";";
	
	$date = date_create_from_format('Y-m', $MONTH);
	echo "Printer \"". $PRINTER . "\" at " . date_format($date, 'F Y');
	$dbconnect = mysql_connect($DBHOST, $DBUSER, $DBPASS);
	if ($dbconnect)
	    {
	    if (mysql_select_db($DBNAME))
			{
			echo "<table border=1>";
			echo "<tr><td><b>Date, time</b></td><td><b>User</b></td><td><b>Pages</b></td><td><b>Copies</b></td><td><b>Document</b></td><td><b>Client</b></td></tr>";
			$PrintResult = mysql_query($PrintQuery);
			$PrintArray;
			//echo $PrintQuery;
			while ($PrintArray = mysql_fetch_array($PrintResult))
				{
				echo "<tr><td>" . $PrintArray[0] . "</td><td>" . $PrintArray[1] . "</td><td>" . $PrintArray[2] . "</td><td>" . $PrintArray[3] . "</td><td>" . $PrintArray[4] . "</td><td>" . $PrintArray[5] . "</td></tr>";
				}
			
			echo "</table>";			
			mysql_close();
			}
		}
	}

?>