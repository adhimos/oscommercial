<?php
/**
 * Created by PhpStorm.
 * User: Diluk
 * Date: 2/19/14
 * Time: 1:44 AM
 */

$customers_columns = "customers_id,customers_gender,customers_firstname,customers_lastname,customers_dob,customers_email_address,customers_telephone,customers_fax,customers_password,customers_newsletter,customers_authentication,customers_guid,customers_verified";

$address_book_columns = "address_book_id,entry_gender,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_suburb,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";

function escape_csv_value($value) {
    $value = str_replace('"', '""', $value); // First off escape all " and make them ""
    if(preg_match('/,/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value)) { // Check if I have any commas or new lines
        return '"'.$value.'"'; // If I have new lines or commas escape them
    } else {
        return $value; // If no new lines or commas just return the value
    }
}

function exportCustomers($exportCtype)
{
    global  $address_book_columns, $customers_columns;
    $customers_columns_array = explode(',', $customers_columns);
    $address_columns_array = explode(',', $address_book_columns);
    $export_customer_table_query = tep_db_query("SELECT ".$customers_columns." FROM " . TABLE_CUSTOMERS );
    $output = "";
    if($exportCtype == 'csv'){
        $output = $customers_columns.",".$address_book_columns."\n";
        $export_customer_table_query = tep_db_query("SELECT ".TABLE_CUSTOMERS.".".$customers_columns.",".$address_book_columns." FROM " . TABLE_CUSTOMERS." INNER JOIN ".TABLE_ADDRESS_BOOK." ON address_book_id = customers_default_address_id");
        while ($customer = tep_db_fetch_array($export_customer_table_query)) {
            for ($i = 0; $i < count($customers_columns_array); $i++) {
                $output .=escape_csv_value($customer[$customers_columns_array[$i]]).',';
            }
            for ($i = 0; $i < count($address_columns_array); $i++) {
                $output .=escape_csv_value($customer[$address_columns_array[$i]]).',';
            }
            $output .="\n";
        }

        // Download the file

        $filename = "customers.csv";
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
    }
    else if($exportCtype == 'xml') {

        // Start XML file, create parent node
        // Set the content type to be XML, so that the browser will   recognise it as XML.
        header( "content-type: application/xml; charset=ISO-8859-15" );

        // "Create" the document.
        $doc = new DOMDocument( "1.0", "ISO-8859-15" );

        $node = $doc->createElement("customers");
        $parnode = $doc->appendChild($node);

        // Iterate through the rows, adding XML nodes for each
        while ($customer = tep_db_fetch_array($export_customer_table_query)){
            // ADD TO XML DOCUMENT NODE

            $node = $doc->createElement("customer");
            $newnode = $parnode->appendChild($node);
            for ($i = 0; $i < count($customers_columns_array); $i++) {

                $newnode->setAttribute($customers_columns_array[$i], $customer[$customers_columns_array[$i]]);
            }

            $export_address_table_query = tep_db_query("SELECT ".$address_book_columns." FROM " . TABLE_ADDRESS_BOOK . " WHERE customers_id=".$customer['customers_id']);
            $node = $doc->createElement("addresses");
            $addressnode = $newnode->appendChild($node);
            // Iterate through the rows, adding XML nodes for each
            while ($address = tep_db_fetch_array($export_address_table_query)){
                $node = $doc->createElement("address");
                $newnode = $addressnode->appendChild($node);
                for ($i = 0; $i < count($address_columns_array); $i++) {
                    $newnode->setAttribute($address_columns_array[$i], $address[$address_columns_array[$i]]);
                }
            }

        }

        $output = $doc->saveXML();


        $filename = "customers.xml";

        header('Content-Disposition: attachment; filename='.$filename);
    }

    echo $output;
    exit;

}

function HandleXmlError($errno, $errstr, $errfile, $errline)
{
    if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0))
    {
        throw new DOMException($errstr);
    }
    else
        return false;
}

function import_customers_from_XML($content){
    global $customers_columns, $address_book_columns;
    // remove id columns
    $customers_columns = str_replace("customers_id,", "", $customers_columns);
    $address_book_columns = str_replace("address_book_id,", "", $address_book_columns);
    $customers_columns_array = explode(',', $customers_columns);
    $address_columns_array = explode(',', $address_book_columns);

    set_error_handler('HandleXmlError');
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc -> load('customers.xml');
        if (!$xmlDoc->schemaValidate('customers.xsd')) {
            print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
            libxml_display_errors();
        }
    restore_error_handler();

        $x = $xmlDoc->documentElement;


        $sql_data_array = array();
        echo count($customers_columns_array);

        $record = 1;
        foreach ($x->childNodes AS $item)
        {
            if($item->nodeName == '#text')
                continue;
            echo "Reading record #".$record."<br>";


            for ($i = 0; $i < count($customers_columns_array); $i++) {
                $sql_data_array[$customers_columns_array[$i]] =  tep_db_prepare_input($item->getAttribute($customers_columns_array[$i]));
                //echo  $customers_columns_array[$i]."=>". $sql_data_array[$customers_columns_array[$i]]. "<br>";
            }


            $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $sql_data_array["customers_email_address"] . "'");
            $check_email = tep_db_fetch_array($check_email_query);
            if ($check_email['total'] > 0) {
                $error = true;
                echo  $sql_data_array["customers_email_address"]." email address already exists. record #".$record." import failed.<br>";
            }
            else {


                tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
                $customer_id = tep_db_insert_id();

                $sql_data_address = array();

                $addressesNode = $item -> childNodes->item(0);

                foreach ($addressesNode->childNodes AS $address)
                {

                    for ($j = 0; $j < count($address_columns_array); $j++) {
                        $sql_data_address[$address_columns_array[$j]]=  tep_db_prepare_input($address->getAttribute($address_columns_array[$j]));
                        //print  $address_columns_array[$j]."=>". $sql_data_address[$address_columns_array[$j]]. "<br>";
                    }
                }

                $sql_data_address["customers_id"]= $customer_id;
                tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_address);

                $address_id = tep_db_insert_id();
                tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "'    where customers_id = '" . (int)$customer_id . "'");


                $customer_info_data = array('customers_info_id'=> $customer_id, 'customers_info_number_of_logons' => '0', 'customers_info_date_account_created' => 'now()',
                    'valid_address'=> '1', 'personal_details_valid' => '1');
                tep_db_perform(TABLE_CUSTOMERS_INFO, $customer_info_data);
                 echo "Successfull imported record #".$record;
            }
            echo "<br>";
            $record++;
        }

}

    function import_customers_by_csv($handle) {
        global  $address_book_columns, $customers_columns;
        // remove id columns
        $customers_columns = str_replace("customers_id,", "", $customers_columns);
        $address_book_columns = str_replace("address_book_id,", "", $address_book_columns);
        $customers_columns_array = explode(',', $customers_columns);

        $address_columns_array = explode(',', $address_book_columns);

                // get headers

                $columns = fgetcsv($handle,100000,",","'");
                $column_list = implode(",",$columns);
                $record = 1;

                //loop through the csv file and insert into database
                while ($data = fgetcsv($handle,100000,",","'")) {

                        for ($i=0;$i<count($columns);$i++){
                            $data[$i]=mysql_real_escape_string($data[$i]);

                        }
                        $sql_data_array = array();
                        for ($i=0;$i<count($customers_columns_array);$i++){
                            $index = array_search($customers_columns_array[$i],$columns );
                            $sql_data_array[$customers_columns_array[$i]] =  $data[$index];
                        }
                        $sql_data_address = array();
                        for ($i=0;$i<count($address_columns_array);$i++){
                            $index = array_search($address_columns_array[$i],$columns );
                            $sql_data_address[$address_columns_array[$i]] =  $data[$index];
                        }

                        //$values = implode(",",$data);


                        $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $sql_data_array["customers_email_address"] . "'");
                        $check_email = tep_db_fetch_array($check_email_query);
                        if ($check_email['total'] > 0) {
                            $error = true;
                            echo  $sql_data_array["customers_email_address"]." email address already exists. record #".$record." import failed.<br>";
                        }
                        else {

                            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
                            $customer_id = tep_db_insert_id();
                            $sql_data_address["customers_id"]= $customer_id;


                            tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_address);
                            $address_id = tep_db_insert_id();
                            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "'    where customers_id = '" . (int)$customer_id . "'");
                            $customer_info_data = array('customers_info_id'=> $customer_id, 'customers_info_number_of_logons' => '0', 'customers_info_date_account_created' => 'now()',
                                'valid_address'=> '1', 'personal_details_valid' => '1');
                            tep_db_perform(TABLE_CUSTOMERS_INFO, $customer_info_data);
                            echo "Successfull imported record #".$record;

                        }
                        echo "<br>";


                        $record +=1;

                }

    }

?>
