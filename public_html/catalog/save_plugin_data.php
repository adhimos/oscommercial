<?php

if(isset($_POST['plugin_data'])) {
    	$data =  json_decode($_POST['plugin_data'], true);		
	require('includes/application_top.php');
	
	if (!tep_session_is_registered('customer_id')) {
    		$navigation->set_snapshot();
    		tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  	}

	$pluginName = $data['plugin'];	
	$newData = $data['data'];
	if(isset($pluginName)){		
		$customer_id = (int) tep_get_from_session('customer_id');
		$pluginData = $_SESSION['plugins'][$pluginName]['plugin_data'];
		if(isset($pluginData)){
			array_push($pluginData, $newData);
			$pluginData = array_unique($pluginData);
		} else { 
			$pluginData = array($newData);
		}
		$_SESSION['plugins'][$pluginName]['plugin_data'] = $pluginData;	
		tep_save_plugin_data_db($customer_id, $pluginName, json_encode($pluginData));		
	} 


	
  } 
?>
