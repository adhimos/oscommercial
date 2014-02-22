<?php	

if(isset($_POST['plugin_data'])) {
    	$data =  json_decode($_POST['plugin_data'], true);		
	$pluginName = $data['plugin'];	
	$key = $data['key'];
	$newData = $data['data'];

	require('includes/application_top.php');
	$customer_id = (int) tep_get_from_session('customer_id');
	if(isset($pluginName)){		
		$_SESSION['plugins'][$pluginName]['plugin_data'][$key] = $newData;		
		tep_save_plugin_data_db($customer_id, $pluginName, json_encode($_SESSION['plugins'][$pluginName]['plugin_data']));
	} 
	
  } 
?>
