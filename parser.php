<?php
require("config.php");
if ($argv[1] == "import-all")
	{
	echo "all file in dir\n";
	CreateFileList($SEARCHDIR);
	}
if (empty($argv[1]))
	{
	$dt = date('Y-m-d',strtotime("-1 days"));
	$SourceFile = $SEARCHDIR . "papercut-print-log-" . $dt . ".csv";
	echo $SourceFile;
	ImportCSVFile(ImportCSVFile($SourceFile));
	}

function CreateFileList($SEARCHDIR)
	{
	$list = glob($SEARCHDIR . "*.csv"); 
	foreach ($list as $l)
		{
		ImportCSVFile($l);
		} 
	}

function ImportCSVFile($SourceFile)
{
require("config.php");
//$SourceFile = "/var/www/shop-vl.ru/public_html/papercut/papercut-print-log-2016-11-04.csv";
echo $SourceFile . "\n";
$ResultFile = tempnam(sys_get_temp_dir(), 'papercut-parser');
$file = file($SourceFile);

array_shift($file);
array_shift($file);

$fp = fopen($ResultFile, "a+");

for ($x=0; $x < sizeof($file); $x++)
	{
    fputs($fp, $file[$x]);
    }
fclose($fp);

//$row = 1;

if (($handle = fopen($ResultFile, "r")) != FALSE)
    {
    $dbconnect = mysql_connect($DBHOST, $DBUSER, $DBPASS);
    if ($dbconnect)
        {
        if (mysql_select_db($DBNAME))
            {
            while (($data = fgetcsv($handle, 0, ",")) != FALSE)
                {
                $printDate = $data[0];
                $printUser = $data[1];
                $printPages = $data[2];
                $printCopies = $data[3];
                $printPrinter = $data[4];
                $printDocument = $data[5];
                $printClient = $data[6];
                $printPaperSize = $data[7];
                $printLanguage = $data[8];
				if ($data[9] != "")
					{
					$printHeight = $data[9];
					}
				else
					{
					$printHeight = "0";
					}
				if ($data[10] != "")
					{
					$printWidth = $data[10];
					}
				else
					{
					$printWidth = "0";
					}
				$printWidth = "0";
                $printDuplex = $data[11];
                $printGrayscale = $data[12];
                $printSize = $data[13];

                $Query = "INSERT INTO data "
					. "(DateTime, User, Pages, Copies, Printer, DocumentName, Client, PaperSize, Language, Height, Width, Duplex, Grayscale, Size) "
					. "VALUES ("
					. "\"" . $printDate . "\", "
					. "\"" . $printUser . "\", "
					. "\"" . $printPages . "\", "
					. "\"" . $printCopies . "\", "
					. "\"" . $printPrinter . "\", "
					. "\"" . $printDocument . "\", "
					. "\"" . $printClient . "\", "
					. "\"" . $printPaperSize . "\", "
					. "\"" . $printLanguage . "\", "
					. "\"" . $printHeight . "\", "
					. "\"" . $printWidth . "\", "
					. "\"" . $printDuplex . "\", "
					. "\"" . $printGrayscale . "\", "
					. "\"" . $printSize . "\""
					. ");";
				echo $Query . "\n";
                mysql_query($Query);
                }
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
}
?>
