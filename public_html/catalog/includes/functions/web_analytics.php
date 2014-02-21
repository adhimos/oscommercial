<?php


if(!session_is_registered("wa_is_new_session"))
{
    tep_session_register("wa_is_new_session");
    insert_wa_session();
}

$wa_prod_id = $_GET["products_id"];


if(!empty($wa_prod_id) && !empty($_SESSION["customer_id"])) {
    $sql_data_array = array('product_id' => $wa_prod_id,
        'customer_id' => $_SESSION["customer_id"],
        'view_datetime' => date('Y-m-d H:i:s'),
        'url' =>$_SERVER["REQUEST_URI"]
        );
    tep_db_perform(TABLE_WA_VIEWS, $sql_data_array);
}
else if(!empty($wa_prod_id))
{
    $sql_data_array = array('product_id' => $wa_prod_id,

        'view_datetime' => date('Y-m-d H:i:s'),
        'url' =>$_SERVER["REQUEST_URI"]
    );
    tep_db_perform(TABLE_WA_VIEWS, $sql_data_array);
}
else if(!empty($_SESSION["customer_id"]))
{

    $sql_data_array = array(
        'customer_id' => $_SESSION["customer_id"],
        'view_datetime' => date('Y-m-d H:i:s'),
        'url' =>$_SERVER["REQUEST_URI"]
    );
    tep_db_perform(TABLE_WA_VIEWS, $sql_data_array);
}
else
{
    $sql_data_array = array(
        'view_datetime' => date('Y-m-d H:i:s'),
        'url' =>$_SERVER["REQUEST_URI"]
    );
    tep_db_perform(TABLE_WA_VIEWS, $sql_data_array);
}

//echo $is_new_session;
function insert_wa_session()
{
	$ip_address = $_SERVER['REMOTE_ADDR'];
	//for testing purposes added sample Ips
    $ip_address_array = array("125.214.169.95", "137.132.21.27", "203.127.23.18", "83.98.28.10", "213.174.127.10",
    "164.100.109.240", "62.50.43.44", "203.35.229.223", "220.181.111.85", "205.193.117.158" );
    $random_num = rand(0,9);
    $ip_address = $ip_address_array[$random_num];
	//$ip_address = '50.23.115.104';
	//test

	$country = file_get_contents('http://api.hostip.info/country.php?ip='.$ip_address);
    $customerId =$_SESSION["customer_id"];
	if(empty($customer_id))
		$customerId = -1;
	

	$sql_data_array = array('session_key' => tep_session_id(),
                              'customer_id' => $customerId,
                              'session_starttime' => date('Y-m-d H:i:s'),
                              'session_year' => date("Y"),
							  'session_month' => date("m"),
                              'session_month_string' => date("M"),
                              'session_day' => date("d"),
                              'session_dayofweek' =>  date("D"),
							  
							   'session_hour' => date("H"),
							  'session_min' => date("i"),
                              'session_week' => date("W"),
                              'is_repeat_visitor' => '0',
                              'is_new_visitor' =>  '1',
							  
							 
                              'first_page_url' => $_SERVER["REQUEST_URI"],
                              'user_agent' => '0',
                              'ip_address' =>  $ip_address,
							  
							  'host' => $_SERVER['REMOTE_HOST'],
							  'country' => $country,
                              'browser' =>$_SERVER['HTTP_USER_AGENT'],
                              'is_browser' => '1');

 tep_db_perform(TABLE_WA_SESSIONS, $sql_data_array);
}

?>