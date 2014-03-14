<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  function tep_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $$link;

    if (USE_PCONNECT == 'true') {
      $$link = mysql_pconnect($server, $username, $password);
    } else {
      $$link = mysql_connect($server, $username, $password);
    }

    if ($$link) mysql_select_db($database);

    return $$link;
  }

  function tep_db_close($link = 'db_link') {
    global $$link;

    return mysql_close($$link);
  }

  function tep_db_error($query, $errno, $error) { 
    die('<font color="#000000"><strong>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br /><small><font color="#ff0000">[TEP STOP]</font></small><br /><br /></strong></font>');
  }

  function tep_db_query($query, $link = 'db_link') {
    global $$link;

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
      error_log('QUERY ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }

    $result = mysql_query($query, $$link) or tep_db_error($query, mysql_errno(), mysql_error());

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
       $result_error = mysql_error();
       error_log('RESULT ' . $result . ' ' . $result_error . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }

    return $result;
  }

  function tep_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

    return tep_db_query($query, $link);
  }

  function tep_db_fetch_array($db_query) {
    return mysql_fetch_array($db_query, MYSQL_ASSOC);
  }

  function tep_db_num_rows($db_query) {
    return mysql_num_rows($db_query);
  }

  function tep_db_data_seek($db_query, $row_number) {
    return mysql_data_seek($db_query, $row_number);
  }

  function tep_db_insert_id($link = 'db_link') {
    global $$link;

    return mysql_insert_id($$link);
  }

  function tep_db_free_result($db_query) {
    return mysql_free_result($db_query);
  }

  function tep_db_fetch_fields($db_query) {
    return mysql_fetch_field($db_query);
  }

  function tep_db_output($string) {
    return htmlspecialchars($string);
  }

  function tep_db_input($string, $link = 'db_link') {
    global $$link;

    if (function_exists('mysql_real_escape_string')) {
      return mysql_real_escape_string($string, $$link);
    } elseif (function_exists('mysql_escape_string')) {
      return mysql_escape_string($string);
    }

    return addslashes($string);
  }

  function tep_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(tep_sanitize_string(stripslashes($string)));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = tep_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

function tep_save_plugin_data_db($customer_id, $plugin_name, $data){

$db_link = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);

$result_customer_id = tep_check_plugin_data_available($db_link, $customer_id, $plugin_name);

if(!isset($result_customer_id)){
	$query = "insert into plugin_data (customer_id, plugin_name, data) values (?, ?, ?);";
} else {
	$query = "update plugin_data set data = ? where customer_id = ? and plugin_name = ?;";

}

$stmt = mysqli_stmt_init($db_link);

if (mysqli_stmt_prepare($stmt, $query)) {

	
    /* Lecture des marqueurs */
    
    if(!isset($result_customer_id)){
	mysqli_stmt_bind_param($stmt, "iss", $customer_id, $plugin_name, $data);
    } else {
	mysqli_stmt_bind_param($stmt, "sis", $data, $customer_id, $plugin_name);
    }

    /* Exécution de la requête */
    mysqli_stmt_execute($stmt);    
}
mysqli_stmt_close($stmt);
mysqli_close($db_link);
}


function tep_check_plugin_data_available($db_link, $customer_id, $plugin_name){	
	$select_query = "select customer_id from plugin_data where customer_id = ? and plugin_name = ?";
	$stmt = mysqli_stmt_init($db_link);

	if (mysqli_stmt_prepare($stmt, $select_query)) {
	    	/* Lecture des marqueurs */
		mysqli_stmt_bind_param($stmt, "is", $customer_id, $plugin_name);

	    	/* Exécution de la requête */
	    	mysqli_stmt_execute($stmt);
		
		mysqli_stmt_bind_result($stmt, $result_customer);

		mysqli_stmt_fetch($stmt);
	}
	mysqli_stmt_close($stmt);
	return $result_customer;	
}

function tep_load_plugin_database($customer_id, $plugin_name){
	$db_link = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);

	$select_query = "select data from plugin_data where customer_id = ? and plugin_name = ?";
	$stmt = mysqli_stmt_init($db_link);
	
	if (mysqli_stmt_prepare($stmt, $select_query)) {		
	    	/* Lecture des marqueurs */
		mysqli_stmt_bind_param($stmt, "is", $customer_id, $plugin_name);

	    	/* Exécution de la requête */
	    	mysqli_stmt_execute($stmt);
		
		mysqli_stmt_bind_result($stmt, $result_data);

		mysqli_stmt_fetch($stmt);

	
	}
	mysqli_stmt_close($stmt);
	mysqli_close($db_link);
	return $result_data;	
}


function tep_uninstall_plugin_database($customer_id, $plugin_name){
	$db_link = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);

	$delete_query = "delete from plugin_data where customer_id = ? and plugin_name = ?";
	$stmt = mysqli_stmt_init($db_link);
	
	if (mysqli_stmt_prepare($stmt, $delete_query)) {		
	    	/* Lecture des marqueurs */
		mysqli_stmt_bind_param($stmt, "is", $customer_id, $plugin_name);

	    	/* Exécution de la requête */
	    	mysqli_stmt_execute($stmt);
		
		
	
	}
	mysqli_stmt_close($stmt);
	mysqli_close($db_link);
}


?>
