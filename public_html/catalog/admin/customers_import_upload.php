<?php
/**
 * Created by PhpStorm.
 * User: Diluk
 * Date: 2/20/14
 * Time: 3:51 AM
 */
require('includes/application_top.php');
$allowedExts = array("xml", "csv");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

if ((($_FILES["file"]["type"] == "text/csv")
        || ($_FILES["file"]["type"] == "text/xml")
        || ($_FILES["file"]["type"] == "application/vnd.ms-excel")
        )
    && ($_FILES["file"]["size"] < 200000000)
    && in_array($extension, $allowedExts))
{
    if ($_FILES["file"]["error"] > 0)
    {
        echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
    }
    else
    {
        //echo "Upload: " . $_FILES["file"]["name"] . "<br>";
        //echo "Type: " . $_FILES["file"]["type"] . "<br>";
        //echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
        //echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";
        $file = fopen( $_FILES["file"]["tmp_name"], 'r', true);
        try{
            if($extension == 'xml' && $_FILES["file"]["type"] == "text/xml") {
                $content ="";
                while (!feof($file))
                {
                    $content = $content.fgets($file);
                }
                import_customers_from_XML($content);
            }
            else if($extension == 'csv' && ($_FILES["file"]["type"] == "application/vnd.ms-excel") || ($_FILES["file"]["type"] == "text/csv")) {
                import_customers_by_csv($file);


            }
        }
        catch(Exception $e)
        {
            echo "An unexpected error has occured during import.<br>";
        }

        fclose($file);

    }
}
else
{
    echo "Invalid file";
}

require('includes/application_bottom.php');
?>