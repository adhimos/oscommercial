<?php
/**
 * Created by PhpStorm.
 * User: Diluk
 * Date: 2/20/14
 * Time: 3:51 AM
 */
require('includes/application_top.php');
require('includes/template_top.php');
?>

    <br>
    <a href="customers.php" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-secondary" role="button" aria-disabled="false">
        <span class="ui-button-text">Back to Customers List</span>
    </a>
    <br>

<?php
$allowedExts = array("xml", "csv","vcf");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

if ((($_FILES["file"]["type"] == "text/csv")
        || ($_FILES["file"]["type"] == "text/xml")
        || ($_FILES["file"]["type"] == "application/vnd.ms-excel")
		|| ($_FILES["file"]["type"] == "text/x-vcard")
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
			else if($extension == 'vcf' ){
				import_customers_by_vcard($_FILES["file"]["tmp_name"]);
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
?>
<br>
    <a href="customers.php" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-secondary" role="button" aria-disabled="false">
         <span class="ui-button-text">Back to Customers List</span>
    </a>

<?php
require('includes/application_bottom.php');

require('includes/template_bottom.php');
?>