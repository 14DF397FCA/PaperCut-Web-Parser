<!DOCTYPE html>
<html>
    <head>
	<meta charset="UTF-8">
	<title>PaperCut Web Parser</title>
    </head>
    <body>
	<div align="left">Enter month for report:</div>
	<?php
	$MONTH = htmlspecialchars($_POST["MONTH"]);
	if (empty($MONTH))
	    {
	    $MONTH = date("Y-m");
	    }
	$date = date_create_from_format('Y-m', $MONTH);
	$TMPDIR = getcwd() . "/tmp";
	if (!file_exists($TMPDIR))
		{
		mkdir($TMPDIR);
		}
	
	$ExportCSV = $TMPDIR . "/" . $MONTH . ".csv";
	if (file_exists($ExportCSV))
		{
		unlink($ExportCSV);
		}
	?>
	<form action="index.php" method="POST" name="SELECTMONTH">
	    <input name="MONTH" type="text" value="<?php echo $MONTH; ?>">
	    <input type="submit" value="Build report">
	</form>
	<?php
	echo "<a href=\"/tmp/" . $MONTH . ".csv\">Download report as CSV-file</a>";
	echo "<br>";
	echo "Report for a month: " . date_format($date, 'F Y');
	$CONFIGFILE = getcwd() . "/config.php";
	require_once($CONFIGFILE);
	$dbconnect = mysql_connect($DBHOST, $DBUSER, $DBPASS);
	if ($dbconnect)
	    {
	    if (mysql_select_db($DBNAME))
		{
		$QueryPrinterName = "SELECT DISTINCT Printer FROM `data` WHERE DateTime BETWEEN \"" . $MONTH . "-01 00:00:00\" AND \"" . date("Y-m-t", strtotime($MONTH)) . " 23:59:59\" ORDER BY Printer ASC; ";
		$ResultPrinterName = mysql_query($QueryPrinterName);
		$PrinterName = "";
		$TotalPrint = 0;
		echo "<table border=1>";
		echo "<tr><td><b>Printer name</b></td><td><b>Print</b></td></tr>";
		file_put_contents($ExportCSV, "Printer name;Print".PHP_EOL, FILE_APPEND | LOCK_EX);
		while ($PrinterName = mysql_fetch_array($ResultPrinterName))
		    {
		    $QueryPrint = "SELECT Pages, Copies FROM data WHERE Printer=\"" . $PrinterName[0] . "\" AND DateTime BETWEEN \"" . $MONTH . "-01 00:00:00\" AND \"" . date("Y-m-t", strtotime($MONTH)) . " 23:59:59\";";
		    $ResultPrint = mysql_query($QueryPrint);
		    $Pages = 0;
		    $Copies = 0;
		    $Summ = 0;
		    $PrintArray = "";
		    while ($PrintArray = mysql_fetch_array($ResultPrint))
				{
				$Pages = $PrintArray[0];
				$Copies = $PrintArray[1];
				$Print = $Pages * $Copies;
				$Summ += $Print;
				}
		    $TotalPrint += $Summ;
		    echo "<tr><td><a href=\"month.php?SelectMonth=" . $MONTH . "&SelectPrinter=" . $PrinterName[0] . "\">" . $PrinterName[0] . "</a></td><td>" . $Summ . "</td></tr>";
			$Txt = $PrinterName[0] . ";" . $Summ;
			file_put_contents($ExportCSV, $Txt.PHP_EOL, FILE_APPEND | LOCK_EX);
		    //echo $PrinterName[0] . " " . $Pages . " * " . $Copies . " = " . $Summ . "<br>";
		    set_time_limit(30);
		    }
		echo "<tr><td><b>Total print</b></td><td><b>" . $TotalPrint . "</b></td></tr>";   
		echo "</table>";
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
	?>
    </body>
</html>
